import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import { initCharts } from './charts';
import { registerOffline } from './offline';

const storage = {
    get(key) {
        try {
            return window.localStorage.getItem(key);
        } catch {
            return null;
        }
    },
    set(key, value) {
        try {
            window.localStorage.setItem(key, value);
        } catch {}
    },
};

Alpine.plugin(focus);

Alpine.store('sidebar', {
    collapsed: storage.get('sidebar-collapsed') === '1',
    mobileOpen: false,
    toggle() {
        if (window.matchMedia('(min-width: 1024px)').matches) {
            this.collapsed = !this.collapsed;
            storage.set('sidebar-collapsed', this.collapsed ? '1' : '0');
            document.documentElement.classList.toggle('sidebar-collapsed', this.collapsed);
        } else {
            this.mobileOpen = !this.mobileOpen;
        }
    },
});

Alpine.store('toast', {
    items: [],
    nextId: 1,
    show(message, type = 'success') {
        const id = this.nextId++;
        this.items.push({ id, message, type });
        setTimeout(() => this.dismiss(id), 3800);
    },
    dismiss(id) {
        this.items = this.items.filter((item) => item.id !== id);
    },
});

Alpine.data('filterable', (initial = {}) => {
    let rows = [];

    return {
        q: '',
        filters: { ...initial },
        init() {
            rows = [...this.$el.querySelectorAll('[data-row]')];
        },
        match(el) {
            const term = this.q.trim().toLowerCase();
            if (term && !(el.dataset.search ?? el.textContent).toLowerCase().includes(term)) {
                return false;
            }
            return Object.entries(this.filters).every(([key, value]) => !value || el.dataset[key] === value);
        },
        get visible() {
            return rows.filter((row) => this.match(row)).length;
        },
        get total() {
            return rows.length;
        },
        get active() {
            return this.q !== '' || Object.values(this.filters).some(Boolean);
        },
        reset() {
            this.q = '';
            Object.keys(this.filters).forEach((key) => (this.filters[key] = ''));
        },
    };
});

Alpine.data('demoForm', (message = 'Changes saved', close = true) => ({
    submit(event) {
        const form = event.target;
        form.classList.add('was-validated');

        if (!form.checkValidity()) {
            form.querySelector(':invalid')?.focus();
            return;
        }

        Alpine.store('toast').show(`${message} — demo only, nothing was stored.`);
        form.classList.remove('was-validated');

        if (close) {
            form.reset();
            this.$dispatch('close-modal');
        }
    },
}));

Alpine.data('validatedForm', () => ({
    busy: false,
    submit(event) {
        const form = event.target;
        if (!form.checkValidity()) {
            event.preventDefault();
            form.classList.add('was-validated');
            form.querySelector(':invalid')?.focus();
            return;
        }
        this.busy = true;
    },
}));

Alpine.data('paymentAllocator', () => ({
    vendorId: '',
    vendorName: '',
    bills: [],
    amount: '',
    load(detail) {
        this.vendorId = detail.vendor_id ?? '';
        this.vendorName = detail.name;
        this.bills = detail.bills.map((bill) => ({ ...bill, alloc: '' }));
        this.amount = '';
    },
    autoAllocate() {
        let left = parseFloat(this.amount) || 0;
        for (const bill of this.bills) {
            const take = Math.min(left, bill.balance);
            bill.alloc = take > 0 ? Math.round(take * 100) / 100 : '';
            left = Math.round((left - take) * 100) / 100;
        }
    },
    toggle(bill) {
        bill.alloc = bill.alloc ? '' : bill.balance;
        this.amount = this.allocated || '';
    },
    get allocated() {
        return Math.round(this.bills.reduce((sum, bill) => sum + (parseFloat(bill.alloc) || 0), 0) * 100) / 100;
    },
    get difference() {
        return Math.round(((parseFloat(this.amount) || 0) - this.allocated) * 100) / 100;
    },
}));

Alpine.data('globalSearch', (items) => ({
    q: '',
    open: false,
    items,
    get results() {
        const term = this.q.trim().toLowerCase();
        if (!term) return [];
        return this.items.filter((item) => `${item.label} ${item.group}`.toLowerCase().includes(term)).slice(0, 7);
    },
    go() {
        if (this.results.length) window.location.href = this.results[0].url;
    },
}));

registerOffline(Alpine);

window.Alpine = Alpine;
Alpine.start();

initCharts();
