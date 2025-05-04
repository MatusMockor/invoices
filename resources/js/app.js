import './bootstrap';
import {createApp} from 'vue';
import CreateInvoiceForm from './components/Invoice/CreateInvoiceForm.vue';
import './bootstrap';
import SettingsDropdown from './components/SettingsDropdown.vue';



// Add dark mode detection
if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}

// Listen for theme changes
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    if (!('theme' in localStorage)) {
        if (e.matches) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
});

// Wait for DOM to be ready before mounting Vue
const app = createApp({});
app.component('settings-dropdown', SettingsDropdown);

// Register Vue components
app.component('create-invoice-form', CreateInvoiceForm);

// Mount Vue app when the DOM is fully loaded
app.mount('#app');
