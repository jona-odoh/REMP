import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

window.L = L;
window.Chart = Chart;
window.Alpine = Alpine;

// Reusable offline-tolerant localStorage autosave helper for long Ministry forms
Alpine.data('formAutosave', (storageKey) => ({
    restored: false,
    init() {
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            try {
                const data = JSON.parse(saved);
                Object.entries(data).forEach(([name, value]) => {
                    const field = this.$el.querySelector(`[name="${name}"]`);
                    if (field && field.type !== 'password' && field.type !== 'hidden' && field.type !== 'file') {
                        if (field.type === 'checkbox') {
                            field.checked = Boolean(value);
                        } else if (!field.value) {
                            field.value = value;
                        }
                    }
                });
                this.restored = true;
            } catch {
                localStorage.removeItem(storageKey);
            }
        }

        this.$el.addEventListener('input', () => this.save());
        this.$el.addEventListener('change', () => this.save());
        this.$el.addEventListener('submit', () => localStorage.removeItem(storageKey));
    },
    save() {
        const formData = new FormData(this.$el);
        const payload = {};
        for (const [key, value] of formData.entries()) {
            if (!key.startsWith('_') && !key.includes('password') && typeof value === 'string') {
                payload[key] = value;
            }
        }
        localStorage.setItem(storageKey, JSON.stringify(payload));
    },
    clearDraft() {
        localStorage.removeItem(storageKey);
        this.restored = false;
    },
}));

Alpine.start();
