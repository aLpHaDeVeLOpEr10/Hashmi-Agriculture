// Swap paginated tables in place instead of reloading the page.
//
// Any link inside a [data-pager] block, and any form marked [data-pager-form], points at a URL whose
// #fragment names the card to update (e.g. /machinery/machines/4?page=2#bills). We fetch that URL,
// take the element with the same id from the response and replace the current one. The address bar is
// updated so refresh, Back/Forward and shared links keep working. On any failure we fall back to a
// normal page load, so the tables never get stuck.

const parser = new DOMParser();
let controller = null;

/** Ids of every card on the page that holds a pager or a pager-driven filter, plus anything those filters also update. */
function pagedCardIds() {
    const ids = [...document.querySelectorAll('[data-pager], [data-pager-form]')].map((el) => el.closest('[id]')?.id);
    document.querySelectorAll('[data-pager-targets]').forEach((el) => ids.push(...el.dataset.pagerTargets.split(/\s+/)));

    return ids.filter((id, i, all) => id && all.indexOf(id) === i);
}

/** Which cards a link or form refreshes: an explicit list, every paged card, or just the one named in its URL #fragment. */
function idsFor(el) {
    if (el.dataset.pagerTargets) {
        return { ids: el.dataset.pagerTargets.split(/\s+/).filter(Boolean) };
    }

    return el.hasAttribute('data-pager-all') ? { ids: pagedCardIds() } : {};
}

async function load(href, { ids, push = true } = {}) {
    const url = new URL(href, window.location.href);
    const targets = (ids ?? [decodeURIComponent(url.hash.slice(1))])
        .map((id) => id && document.getElementById(id))
        .filter(Boolean);

    if (targets.length === 0 || url.origin !== window.location.origin) {
        window.location.assign(url.href);
        return;
    }

    controller?.abort();
    controller = new AbortController();
    targets.forEach((el) => el.setAttribute('aria-busy', 'true'));

    try {
        const fetchUrl = new URL(url.href);
        fetchUrl.hash = '';
        const response = await fetch(fetchUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
            credentials: 'same-origin',
            signal: controller.signal,
        });

        // Session expired or something unexpected: let the browser handle it normally.
        if (!response.ok || response.redirected) {
            window.location.assign(url.href);
            return;
        }

        const doc = parser.parseFromString(await response.text(), 'text/html');
        const pairs = targets.map((el) => [el, doc.getElementById(el.id)]);
        if (pairs.some(([, fresh]) => !fresh)) {
            window.location.assign(url.href);
            return;
        }

        const replaced = pairs.map(([el, fresh]) => {
            const node = document.importNode(fresh, true);
            el.replaceWith(node);
            return node;
        });

        if (push) {
            if (!window.history.state?.pager) {
                window.history.replaceState({ ...window.history.state, pager: true }, '');
            }
            window.history.pushState({ pager: true }, '', url.href);

            // Bring the top of the table into view if the user had scrolled past it.
            const focus = replaced.find((node) => '#' + node.id === url.hash) ?? replaced[0];
            if (focus.getBoundingClientRect().top < 0) {
                focus.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    } catch (error) {
        if (error.name !== 'AbortError') {
            window.location.assign(url.href);
        }
    }
}

export function registerPager() {
    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-pager] a[href], a[href][data-pager-link]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return; // let new-tab / modified clicks behave normally
        }
        event.preventDefault();
        load(link.href, idsFor(link));
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-pager-form]');
        if (!form || (form.getAttribute('method') || 'get').toLowerCase() !== 'get') {
            return;
        }
        event.preventDefault();

        const url = new URL(form.getAttribute('action') || window.location.href, window.location.href);
        url.search = '';
        for (const [name, value] of new FormData(form)) {
            if (value !== '') {
                url.searchParams.append(name, value);
            }
        }
        load(url.href, idsFor(form));
    });

    // Back / Forward between pages: refresh every paged table from that history entry's URL.
    window.addEventListener('popstate', (event) => {
        if (!event.state?.pager) {
            return; // not one of ours (e.g. an in-page #anchor)
        }
        const ids = pagedCardIds();
        if (ids.length) {
            load(window.location.href, { ids, push: false });
        } else {
            window.location.reload();
        }
    });
}
