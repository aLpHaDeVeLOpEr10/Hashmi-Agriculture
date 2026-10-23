let QUEUE_KEY = 'offline-expenses';

function readQueue() {
    try {
        return JSON.parse(window.localStorage.getItem(QUEUE_KEY) ?? '[]');
    } catch {
        return [];
    }
}

function writeQueue(queue) {
    try {
        window.localStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
    } catch {}
}

function uuid() {
    if (window.crypto?.randomUUID) return window.crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        return (c === 'x' ? r : (r & 0x3) | 0x8).toString(16);
    });
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function post(url, body, token) {
    return fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(body),
    });
}

export function registerOffline(Alpine) {
    const meta = document.querySelector('meta[name="offline-sync"]');
    QUEUE_KEY = `offline-expenses:${meta?.dataset.user ?? 'guest'}`;

    Alpine.store('offline', {
        online: navigator.onLine,
        queue: readQueue(),
        syncing: false,
        get pending() {
            return this.queue.filter((entry) => !entry.errors).length;
        },
        get failed() {
            return this.queue.filter((entry) => entry.errors);
        },
        add(data, label) {
            this.queue.push({ uuid: uuid(), data, label, savedAt: new Date().toISOString() });
            writeQueue(this.queue);
        },
        discard(id) {
            this.queue = this.queue.filter((entry) => entry.uuid !== id);
            writeQueue(this.queue);
        },
        async sync() {
            if (!meta || this.syncing || !navigator.onLine || this.queue.length === 0) return;
            this.syncing = true;
            const entries = this.queue.map(({ uuid, data }) => ({ uuid, data }));

            try {
                let response = await post(meta.content, { entries }, csrfToken());
                if (response.status === 419) {
                    const fresh = await fetch(meta.dataset.token, { credentials: 'same-origin', headers: { Accept: 'application/json' } }).then((r) => r.json());
                    document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', fresh.token);
                    response = await post(meta.content, { entries }, fresh.token);
                }
                if (!response.ok) throw new Error(`Sync failed (${response.status})`);

                const { results } = await response.json();
                const byId = Object.fromEntries(results.map((r) => [r.uuid, r]));
                const synced = results.filter((r) => r.ok).length;
                this.queue = this.queue
                    .filter((entry) => !byId[entry.uuid]?.ok)
                    .map((entry) => (byId[entry.uuid] ? { ...entry, errors: byId[entry.uuid].errors } : entry));
                writeQueue(this.queue);

                if (synced > 0) {
                    Alpine.store('toast').show(meta.dataset.syncedMessage.replace(':count', synced));
                    navigator.serviceWorker?.controller?.postMessage('clear-pages');
                }
            } catch {
                Alpine.store('toast').show(meta.dataset.failedMessage, 'info');
            } finally {
                this.syncing = false;
            }
        },
    });

    window.addEventListener('online', () => {
        Alpine.store('offline').online = true;
        Alpine.store('offline').sync();
    });
    window.addEventListener('offline', () => (Alpine.store('offline').online = false));

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form.matches('form[data-offline-form]') || navigator.onLine) return;

        event.preventDefault();
        event.stopImmediatePropagation();
        form.classList.add('was-validated');
        if (!form.checkValidity()) return;

        const data = {};
        for (const [key, value] of new FormData(form).entries()) {
            if (['_token', '_method', 'receipt', 'action'].includes(key) || key.startsWith('payment') || key === 'paid_amount' || value === '') continue;
            data[key] = value;
        }
        Alpine.store('offline').add(data, form.dataset.offlineLabel?.replace(':description', data.description ?? ''));
        Alpine.store('toast').show(form.dataset.offlineMessage, 'info');
        form.reset();
        form.classList.remove('was-validated');
        form.dispatchEvent(new Event('offline-saved'));
    }, true);

    document.addEventListener('submit', (event) => {
        if (event.target.matches('form[data-logout]')) {
            navigator.serviceWorker?.controller?.postMessage('clear-pages');
        }
    });

    if ('serviceWorker' in navigator && meta) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }

    document.addEventListener('alpine:initialized', () => Alpine.store('offline').sync());
}
