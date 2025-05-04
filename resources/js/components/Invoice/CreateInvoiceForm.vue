<template>
    <div>
        <div v-if="errors.length" class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Chyba!</strong>
            <span class="block sm:inline">Prosím opravte nasledujúce chyby:</span>
            <ul class="mt-3 list-disc list-inside text-sm">
                <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
            </ul>
        </div>

        <form @submit.prevent="submitForm" id="invoice-form">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Company Information -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Údaje o spoločnosti</h3>
                    
                    <div class="mb-4">
                        <label for="ico" class="block text-sm font-medium text-gray-700">IČO</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" v-model="form.ico" id="ico" class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300" placeholder="Zadajte IČO">
                            <button type="button" @click="fetchCompanyData" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Načítať údaje
                            </button>
                        </div>
                        <div :class="companyMessageClass" class="text-sm mt-1" v-if="companyMessage">{{ companyMessage }}</div>
                    </div>
                    
                    <div id="company-details" class="space-y-4">
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700">Názov spoločnosti</label>
                            <input type="text" v-model="form.company_name" id="company_name" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                        </div>
                        
                        <div>
                            <label for="company_address" class="block text-sm font-medium text-gray-700">Adresa</label>
                            <input type="text" v-model="form.company_address" id="company_address" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                        </div>
                        
                        <div>
                            <label for="company_city" class="block text-sm font-medium text-gray-700">Mesto</label>
                            <input type="text" v-model="form.company_city" id="company_city" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                        </div>
                        
                        <div>
                            <label for="company_postal_code" class="block text-sm font-medium text-gray-700">PSČ</label>
                            <input type="text" v-model="form.company_postal_code" id="company_postal_code" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                        </div>
                        
                        <div>
                            <label for="company_dic" class="block text-sm font-medium text-gray-700">DIČ</label>
                            <input type="text" v-model="form.company_dic" id="company_dic" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                        </div>
                        
                        <div>
                            <label for="company_ic_dph" class="block text-sm font-medium text-gray-700">IČ DPH</label>
                            <input type="text" v-model="form.company_ic_dph" id="company_ic_dph" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                        </div>
                    </div>
                </div>
                
                <!-- Invoice Information -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Údaje o faktúre</h3>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="invoice_number" class="block text-sm font-medium text-gray-700">Číslo faktúry</label>
                            <input type="text" v-model="form.invoice_number" id="invoice_number" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="issue_date" class="block text-sm font-medium text-gray-700">Dátum vystavenia</label>
                            <input type="date" v-model="form.issue_date" id="issue_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700">Dátum splatnosti</label>
                            <input type="date" v-model="form.due_date" id="due_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Stav faktúry</label>
                            <select v-model="form.status" id="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="draft">Koncept</option>
                                <option value="sent">Odoslaná</option>
                                <option value="paid">Zaplatená</option>
                                <option value="cancelled">Zrušená</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700">Mena</label>
                            <select v-model="form.currency" id="currency" @change="updateCurrency" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="EUR">EUR</option>
                                <option value="USD">USD</option>
                                <option value="CZK">CZK</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="note" class="block text-sm font-medium text-gray-700">Poznámka</label>
                            <textarea v-model="form.note" id="note" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Invoice Items -->
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Položky faktúry</h3>
                
                <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200" id="items-table">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Popis</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Množstvo</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jednotková cena</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spolu</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Akcie</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(item, index) in form.items" :key="index" class="item-row">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="text" v-model="item.description" class="item-description border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="Zadajte popis položky" required>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number" v-model="item.quantity" @input="updateTotal" class="item-quantity border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-20" min="1" required>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number" v-model="item.unit_price" @input="updateTotal" class="item-price border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-32" min="0" step="0.01" placeholder="0.00" required>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap item-total">
                                                {{ getItemTotal(index).toFixed(2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button type="button" class="text-red-600 hover:text-red-900 remove-item" @click="removeItem(index)">Odstrániť</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="button" @click="addItem" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Pridať položku
                        </button>
                    </div>
                    
                    <div class="mt-6 text-right">
                        <div class="text-sm text-gray-500">Medzisúčet: <span>{{ subtotal.toFixed(2) }}</span> <span>{{ form.currency }}</span></div>
                        <div class="text-sm text-gray-500">DPH (20%): <span>{{ vat.toFixed(2) }}</span> <span>{{ form.currency }}</span></div>
                        <div class="text-lg font-bold">Spolu: <span>{{ grandTotal.toFixed(2) }}</span> <span>{{ form.currency }}</span></div>
                        <input type="hidden" v-model="form.total_amount">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Vytvoriť faktúru
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            form: {
                ico: '',
                company_name: '',
                company_address: '',
                company_city: '',
                company_postal_code: '',
                company_dic: '',
                company_ic_dph: '',
                invoice_number: 'INVOICE-' + new Date().toISOString().substr(0, 10).replace(/-/g, '') + '-' + Math.floor(1000 + Math.random() * 9000),
                issue_date: new Date().toISOString().substr(0, 10),
                due_date: new Date(new Date().setDate(new Date().getDate() + 14)).toISOString().substr(0, 10),
                status: 'draft',
                currency: 'EUR',
                note: '',
                total_amount: '0.00',
                items: [
                    {
                        description: '',
                        quantity: 1,
                        unit_price: 0
                    }
                ]
            },
            companyMessage: '',
            companyMessageClass: '',
            errors: [],
            subtotal: 0,
            vat: 0,
            grandTotal: 0
        };
    },
    created() {
        this.updateTotal();
    },
    methods: {
        fetchCompanyData() {
            if (!this.form.ico) {
                this.companyMessage = 'Zadajte IČO';
                this.companyMessageClass = 'text-red-500';
                return;
            }
            
            this.companyMessage = 'Načítavam údaje...';
            this.companyMessageClass = '';
            
            axios.post('/api/companies/fetch-by-ico', {
                ico: this.form.ico
            })
            .then(response => {
                const data = response.data;
                if (data.success) {
                    this.form.company_name = data.data.name;
                    this.form.company_address = data.data.street;
                    this.form.company_city = data.data.city;
                    this.form.company_postal_code = data.data.postal_code;
                    this.form.company_dic = data.data.dic;
                    this.form.company_ic_dph = data.data.ic_dph;
                    
                    this.companyMessage = 'Údaje úspešne načítané';
                    this.companyMessageClass = 'text-green-500';
                } else {
                    this.companyMessage = data.message || 'Nepodarilo sa načítať údaje';
                    this.companyMessageClass = 'text-red-500';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.companyMessage = 'Chyba pri načítaní údajov';
                this.companyMessageClass = 'text-red-500';
            });
        },
        getItemTotal(index) {
            const item = this.form.items[index];
            return (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
        },
        updateTotal() {
            this.subtotal = this.form.items.reduce((total, item, index) => {
                return total + this.getItemTotal(index);
            }, 0);
            
            this.vat = this.subtotal * 0.2; // 20% VAT
            this.grandTotal = this.subtotal + this.vat;
            this.form.total_amount = this.grandTotal.toFixed(2);
        },
        addItem() {
            this.form.items.push({
                description: '',
                quantity: 1,
                unit_price: 0
            });
        },
        removeItem(index) {
            if (this.form.items.length > 1) {
                this.form.items.splice(index, 1);
                this.updateTotal();
            } else {
                alert('Faktúra musí obsahovať aspoň jednu položku');
            }
        },
        updateCurrency() {
            this.updateTotal();
        },
        validateForm() {
            this.errors = [];
            
            // Check if company data is loaded
            if (!this.form.company_name) {
                this.errors.push('Zadajte IČO a načítajte údaje o spoločnosti');
                return false;
            }
            
            // Check if all required fields are filled
            if (!this.form.ico || !this.form.invoice_number || !this.form.issue_date || !this.form.due_date) {
                this.errors.push('Vyplňte všetky povinné polia faktúry');
                return false;
            }
            
            // Check if at least one item has a description and price
            let validItems = false;
            
            for (const item of this.form.items) {
                if (item.description && parseFloat(item.unit_price) > 0) {
                    validItems = true;
                    break;
                }
            }
            
            if (!validItems) {
                this.errors.push('Pridajte aspoň jednu položku s popisom a cenou');
                return false;
            }
            
            return true;
        },
        submitForm() {
            if (!this.validateForm()) {
                return;
            }
            
            axios.post('/invoices', this.form)
                .then(response => {
                    window.location.href = '/invoices';
                })
                .catch(error => {
                    if (error.response && error.response.data && error.response.data.errors) {
                        this.errors = Object.values(error.response.data.errors).flat();
                    } else {
                        this.errors = ['Nastala chyba pri ukladaní faktúry.'];
                    }
                });
        }
    }
};
</script>
