<template>
  <div>
    <div v-if="errors.length"
         class="mb-6 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded relative"
         role="alert">
      <strong class="font-bold">Chyba!</strong>
      <span class="block sm:inline">Prosím opravte nasledujúce chyby:</span>
      <ul class="mt-3 list-disc list-inside text-sm">
        <li v-for="(error) in errors">{{ error }}</li>
      </ul>
    </div>

    <form :action="submitUrl" method="POST" @submit="onSubmit" id="invoice-form">
      <input type="hidden" name="_token" :value="csrfToken">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Company Information -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Údaje o spoločnosti</h3>
          <div class="mb-4">
            <label for="ico" class="block text-sm font-medium text-gray-700 dark:text-gray-300">IČO</label>
            <div class="mt-1 flex rounded-md shadow-sm">
              <input type="text" v-model="form.ico" name="ico" id="ico"
                     class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm flex-1 block w-full sm:text-sm"
                     placeholder="Zadajte IČO">
              <button type="button" @click="fetchCompanyData"
                      class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                Načítať údaje
              </button>
            </div>
            <div :class="companyMessageClass" class="text-sm mt-1" v-if="companyMessage">{{ companyMessage }}</div>
          </div>

          <div id="company-details" class="space-y-4">
            <div>
              <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Názov
                spoločnosti</label>
              <input type="text" v-model="form.company_name" id="company_name" readonly
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm bg-gray-50">
            </div>

            <div>
              <label for="company_address"
                     class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adresa</label>
              <input type="text" v-model="form.company_address" id="company_address" readonly
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm bg-gray-50">
            </div>

            <div>
              <label for="company_city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mesto</label>
              <input type="text" v-model="form.company_city" id="company_city" readonly
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm bg-gray-50">
            </div>

            <div>
              <label for="company_postal_code"
                     class="block text-sm font-medium text-gray-700 dark:text-gray-300">PSČ</label>
              <input type="text" v-model="form.company_postal_code" id="company_postal_code" readonly
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm bg-gray-50">
            </div>

            <div>
              <label for="company_dic" class="block text-sm font-medium text-gray-700 dark:text-gray-300">DIČ</label>
              <input type="text" v-model="form.company_dic" id="company_dic" readonly
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm bg-gray-50">
            </div>

            <div>
              <label for="company_ic_dph" class="block text-sm font-medium text-gray-700 dark:text-gray-300">IČ
                DPH</label>
              <input type="text" v-model="form.company_ic_dph" id="company_ic_dph" readonly
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm bg-gray-50">
            </div>
          </div>
        </div>

        <!-- Invoice Information -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Údaje o faktúre</h3>

          <div class="grid grid-cols-1 gap-4">
            <div>
              <label for="invoice_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Číslo
                faktúry</label>
              <input type="text" v-model="form.invoice_number" name="invoice_number" id="invoice_number"
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm">
            </div>

            <div>
              <label for="issue_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dátum
                vystavenia</label>
              <input type="date" v-model="form.issue_date" name="issue_date" id="issue_date"
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm">
            </div>

            <div>
              <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dátum
                splatnosti</label>
              <input type="date" v-model="form.due_date" name="due_date" id="due_date"
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm">
            </div>

            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stav
                faktúry</label>
              <select v-model="form.status" name="status" id="status"
                      class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                <option value="draft">Koncept</option>
                <option value="sent">Odoslaná</option>
                <option value="paid">Zaplatená</option>
                <option value="cancelled">Zrušená</option>
              </select>
            </div>

            <div>
              <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mena</label>
              <select v-model="form.currency" name="currency" id="currency" @change="updateCurrency"
                      class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                <option value="EUR">EUR</option>
                <option value="USD">USD</option>
                <option value="CZK">CZK</option>
              </select>
            </div>

            <div>
              <label for="constant_symbol" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konštantný symbol</label>
              <input type="text" v-model="form.constant_symbol" name="constant_symbol" id="constant_symbol"
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm">
            </div>

            <div>
              <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Poznámka</label>
              <textarea v-model="form.note" name="note" id="note" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm"></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Invoice Items -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Položky faktúry</h3>

        <div class="flex flex-col">
          <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
              <div class="shadow overflow-hidden border-b border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="items-table">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Popis
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Množstvo
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Jednotková cena
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Spolu
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Akcie
                    </th>
                  </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="(item, index) in form.items" :key="index" class="item-row">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <input type="text" v-model="item.description" :name="'items['+index+'][description]'"
                             class="item-description border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm"
                             placeholder="Zadajte popis položky" required>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <input type="number" v-model="item.quantity" @input="updateTotal"
                             :name="'items['+index+'][quantity]'"
                             class="item-quantity border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm w-20"
                             min="1" required>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <input type="number" v-model="item.unit_price" @input="updateTotal"
                             :name="'items['+index+'][unit_price]'"
                             class="item-price border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm w-32"
                             min="0" step="0.01" placeholder="0.00" required>
                      <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cena s DPH</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap item-total dark:text-gray-300">
                      {{ getItemTotal(index).toFixed(2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button type="button"
                              class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 remove-item"
                              @click="removeItem(index)">Odstrániť
                      </button>
                    </td>
                  </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="mt-4">
            <button type="button" @click="addItem"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900 hover:bg-indigo-200 dark:hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
              <svg class="-ml-1 mr-2 h-5 w-5 text-indigo-500 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg"
                   viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                      d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                      clip-rule="evenodd"/>
              </svg>
              Pridať položku
            </button>
          </div>

          <div class="mt-6 text-right">
            <div class="text-sm text-gray-500 dark:text-gray-400">Cena bez DPH: <span>{{ subtotal.toFixed(2) }}</span>
              <span>{{ form.currency }}</span></div>
            <div class="text-sm text-gray-500 dark:text-gray-400">DPH (20%): <span>{{ vat.toFixed(2) }}</span>
              <span>{{ form.currency }}</span></div>
            <div class="text-lg font-bold text-gray-900 dark:text-gray-100">Spolu s DPH: <span>{{
                grandTotal.toFixed(2)
              }}</span> <span>{{ form.currency }}</span></div>
            <input type="hidden" v-model="form.total_amount" name="total_amount">
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
          Vytvoriť faktúru
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    fetchCompanyUrl: {
      type: String,
      required: true
    },
    submitUrl: {
      type: String,
      required: true
    },
    csrfToken: {
      type: String,
      required: true
    },
  },
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
        invoice_number: new Date().toISOString().substr(0, 10).replace(/-/g, '') + '-' + Math.floor(1000 + Math.random() * 9000),
        issue_date: new Date().toISOString().substr(0, 10),
        due_date: new Date(new Date().setDate(new Date().getDate() + 14)).toISOString().substr(0, 10),
        status: 'draft',
        supplier_company_id: this.currentCompanyId || '',
        currency: 'EUR',
        constant_symbol: '',
        note: '',
        items: [
          {
            description: '',
            quantity: 1,
            unit_price: 0
          }
        ],
        total_amount: 0
      },
      errors: [],
      companyMessage: '',
      companyMessageClass: '',
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
        this.companyMessage = 'Prosím zadajte IČO';
        this.companyMessageClass = 'text-red-600 dark:text-red-400';
        return;
      }

      this.companyMessage = 'Načítavam údaje...';
      this.companyMessageClass = 'text-gray-600 dark:text-gray-400';

      axios.get(this.fetchCompanyUrl + '?ico=' + this.form.ico)
          .then(response => {
            if (response.data.success) {
              const data = response.data.data;
              this.form.company_name = data.name;
              this.form.company_address = data.street;
              this.form.company_city = data.city;
              this.form.company_postal_code = data.postal_code;
              this.form.company_dic = data.dic || '';
              this.form.company_ic_dph = data.ic_dph || '';

              this.companyMessage = 'Údaje úspešne načítané';
              this.companyMessageClass = 'text-green-600 dark:text-green-400';
            } else {
              this.companyMessage = response.data.message || 'Nepodarilo sa načítať údaje';
              this.companyMessageClass = 'text-red-600 dark:text-red-400';
            }
          })
          .catch(error => {
            this.companyMessage = 'Chyba pri načítaní údajov';
            this.companyMessageClass = 'text-red-600 dark:text-red-400';
            console.error(error);
          });
    },
    getItemTotal(index) {
      const item = this.form.items[index];
      return item.quantity * item.unit_price;
    },
    updateTotal() {
      this.grandTotal = this.form.items.reduce((total, item) => {
        return total + (item.quantity * item.unit_price);
      }, 0);

      // Calculate subtotal and VAT from the grand total (which now includes VAT)
      this.subtotal = this.grandTotal / 1.2;
      this.vat = this.grandTotal - this.subtotal;
      this.form.total_amount = this.grandTotal;
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

      if (!this.form.ico) {
        this.errors.push('IČO je povinné');
      }

      if (!this.form.company_name) {
        this.errors.push('Názov spoločnosti je povinný');
      }

      if (!this.form.company_address) {
        this.errors.push('Adresa spoločnosti je povinná');
      }

      if (!this.form.company_city) {
        this.errors.push('Mesto je povinné');
      }

      if (!this.form.company_postal_code) {
        this.errors.push('PSČ je povinné');
      }

      if (!this.form.invoice_number) {
        this.errors.push('Číslo faktúry je povinné');
      }

      if (!this.form.issue_date) {
        this.errors.push('Dátum vystavenia je povinný');
      }

      if (!this.form.due_date) {
        this.errors.push('Dátum splatnosti je povinný');
      }

      // Validate items
      let hasEmptyItems = false;
      this.form.items.forEach((item) => {
        if (!item.description) {
          hasEmptyItems = true;
        }
      });

      if (hasEmptyItems) {
        this.errors.push('Všetky položky musia mať vyplnený popis');
      }

      return this.errors.length === 0;
    },
    onSubmit(event) {
      if (this.validateForm()) {
        // Form is valid, allow the traditional form submission to proceed
        return true;
      }
      // Form is invalid, prevent submission
      event.preventDefault();
    }
  }
};
</script>
