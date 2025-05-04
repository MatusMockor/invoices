import './bootstrap';
import { createApp } from 'vue';
import Alpine from 'alpinejs';
import ExampleComponent from './components/ExampleComponent.vue';

window.Alpine = Alpine;
Alpine.start();

// Initialize Vue application
const app = createApp({});

// Register Vue components
app.component('example-component', ExampleComponent);

// Mount Vue app
app.mount('#app');
