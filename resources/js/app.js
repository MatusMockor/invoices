import './bootstrap';
import {createApp} from 'vue';
import CreateInvoiceForm from './Components/Invoice/CreateInvoiceForm.vue';

// Wait for DOM to be ready before mounting Vue
const app = createApp({});

// Register Vue components
app.component('create-invoice-form', CreateInvoiceForm);

// Mount Vue app when the DOM is fully loaded
app.mount('#app');
