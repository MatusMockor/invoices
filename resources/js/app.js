import './bootstrap';
import {createApp} from 'vue';
import InvoiceForm from './components/Invoice/InvoiceForm.vue';
import BusinessEntityForm from './components/BusinessEntityForm.vue';
import CompanyForm from './components/CompanyForm.vue';
import SettingsDropdown from './components/SettingsDropdown.vue';
import CompanySwitcher from "./components/CompanySwitcher.vue";
import TripForm from './components/TripForm.vue';
import AnalyticsComponent from './components/Analytics/AnalyticsComponent.vue';

// Import Flowbite for initialization
import 'flowbite';

// Add dark mode detection - use color-theme for consistency with Flowbite
if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}

// Listen for theme changes
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    if (!('color-theme' in localStorage)) {
        if (e.matches) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
});

// Flowbite dark mode toggle and sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get the theme toggle button
    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
    const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

    // Change the icons inside the button based on previous settings
    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        themeToggleLightIcon.classList.remove('hidden');
    } else {
        themeToggleDarkIcon.classList.remove('hidden');
    }

    // Add event listener to the theme toggle button
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
            // Toggle icons
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            // If set via local storage previously
            if (localStorage.getItem('color-theme')) {
                if (localStorage.getItem('color-theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                }
            } else {
                // If NOT set via local storage previously
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                }
            }
        });
    }

    // Toggle sidebar on mobile
    const toggleSidebarMobileBtn = document.getElementById('toggleSidebarMobile');
    const sidebar = document.getElementById('sidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const toggleSidebarMobileHamburger = document.getElementById('toggleSidebarMobileHamburger');
    const toggleSidebarMobileClose = document.getElementById('toggleSidebarMobileClose');

    if (toggleSidebarMobileBtn) {
        toggleSidebarMobileBtn.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
            sidebarBackdrop.classList.toggle('hidden');
            toggleSidebarMobileHamburger.classList.toggle('hidden');
            toggleSidebarMobileClose.classList.toggle('hidden');
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', () => {
            sidebar.classList.add('hidden');
            sidebarBackdrop.classList.add('hidden');
            toggleSidebarMobileHamburger.classList.remove('hidden');
            toggleSidebarMobileClose.classList.add('hidden');
        });
    }

    // Toggle sidebar visibility for desktop - persistent preference
    const toggleSidebarDesktopBtn = document.getElementById('toggleSidebarDesktop');
    const mainContent = document.getElementById('main-content');

    // Check if sidebar was previously hidden by user
    if (localStorage.getItem('sidebar-hidden') === 'true') {
        sidebar.classList.add('lg:hidden');
        mainContent.classList.remove('lg:ml-64');
    }

    if (toggleSidebarDesktopBtn) {
        toggleSidebarDesktopBtn.addEventListener('click', () => {
            sidebar.classList.toggle('lg:hidden');
            mainContent.classList.toggle('lg:ml-64');

            // Save preference to localStorage
            if (sidebar.classList.contains('lg:hidden')) {
                localStorage.setItem('sidebar-hidden', 'true');
            } else {
                localStorage.setItem('sidebar-hidden', 'false');
            }
        });
    }
});

// Wait for DOM to be ready before mounting Vue
const app = createApp({});
app.component('settings-dropdown', SettingsDropdown);

// Register Vue components
app.component('invoice-form', InvoiceForm);
app.component('company-switcher', CompanySwitcher);
app.component('business-entity-form', BusinessEntityForm);
app.component('company-form', CompanyForm);
app.component('trip-form', TripForm);
app.component('analytics-component', AnalyticsComponent);

// Mount Vue app when the DOM is fully loaded
app.mount('#app');
