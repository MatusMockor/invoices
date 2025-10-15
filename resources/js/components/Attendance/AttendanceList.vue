<template>
    <div class="mt-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    História dochádzky
                </h3>

                <!-- Filters -->
                <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Od dátumu
                        </label>
                        <input
                            v-model="filters.start_date"
                            type="date"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Do dátumu
                        </label>
                        <input
                            v-model="filters.end_date"
                            type="date"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        />
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="fetchAttendances"
                            class="px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800 transition w-full"
                        >
                            Filtrovať
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center items-center py-8">
                    <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Attendance Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Dátum</th>
                                <th scope="col" class="px-6 py-3">Prihlásenie</th>
                                <th scope="col" class="px-6 py-3">Odhlásenie</th>
                                <th scope="col" class="px-6 py-3">Typ práce</th>
                                <th scope="col" class="px-6 py-3">Odpracované</th>
                                <th scope="col" class="px-6 py-3">Stav</th>
                                <th scope="col" class="px-6 py-3">Akcie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="attendance in attendances"
                                :key="attendance.id"
                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                            >
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    {{ formatDate(attendance.check_in) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ formatTime(attendance.check_in) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ attendance.check_out ? formatTime(attendance.check_out) : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ attendance.work_type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold">
                                    {{ attendance.formatted_working_hours }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="getStatusClass(attendance.status_color)" class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ attendance.status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="viewDetails(attendance)"
                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline"
                                    >
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="attendances.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Žiadne záznamy dochádzky
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.last_page > 1" class="mt-4 flex justify-center">
                    <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button
                            @click="changePage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50"
                        >
                            Predchádzajúca
                        </button>
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ pagination.current_page }} / {{ pagination.last_page }}
                        </span>
                        <button
                            @click="changePage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50"
                        >
                            Ďalšia
                        </button>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div v-if="showDetailModal && selectedAttendance" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
            <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-2xl sm:w-full">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Detail dochádzky</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dátum</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(selectedAttendance.check_in) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Typ práce</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedAttendance.work_type_label }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Prihlásenie</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedAttendance.check_in_formatted }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Odhlásenie</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedAttendance.check_out_formatted || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Odpracované hodiny</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ selectedAttendance.formatted_working_hours }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Stav</dt>
                            <dd class="mt-1">
                                <span :class="getStatusClass(selectedAttendance.status_color)" class="px-2 py-1 text-xs font-medium rounded-full">
                                    {{ selectedAttendance.status_label }}
                                </span>
                            </dd>
                        </div>
                        <div v-if="selectedAttendance.note" class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Poznámka</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedAttendance.note }}</dd>
                        </div>
                        <div v-if="selectedAttendance.breaks && selectedAttendance.breaks.length > 0" class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Prestávky</dt>
                            <dd>
                                <ul class="space-y-2">
                                    <li v-for="breakItem in selectedAttendance.breaks" :key="breakItem.id" class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                        <span class="text-sm text-gray-900 dark:text-white">
                                            {{ breakItem.break_type_label }}
                                        </span>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            {{ breakItem.break_start_formatted }} - {{ breakItem.break_end_formatted || 'Aktívna' }}
                                            <span v-if="breakItem.duration_minutes" class="ml-2 font-semibold">({{ breakItem.formatted_duration }})</span>
                                        </span>
                                    </li>
                                </ul>
                            </dd>
                        </div>
                    </dl>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-end">
                    <button
                        @click="showDetailModal = false"
                        class="px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition ease-in-out duration-150"
                    >
                        Zavrieť
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AttendanceList',
    props: {
        apiRoutes: {
            type: Object,
            required: true,
        },
        csrfToken: {
            type: String,
            required: true,
        },
    },
    data() {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        return {
            loading: false,
            attendances: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                per_page: 15,
            },
            filters: {
                start_date: firstDay.toISOString().split('T')[0],
                end_date: lastDay.toISOString().split('T')[0],
            },
            showDetailModal: false,
            selectedAttendance: null,
        };
    },
    mounted() {
        this.fetchAttendances();
    },
    methods: {
        async fetchAttendances(page = 1) {
            this.loading = true;

            try {
                const params = new URLSearchParams({
                    page,
                    per_page: this.pagination.per_page,
                    ...this.filters,
                });

                const response = await fetch(`${this.apiRoutes.index}?${params}`);
                const data = await response.json();

                this.attendances = data.data;
                this.pagination = {
                    current_page: data.meta.current_page,
                    last_page: data.meta.last_page,
                    per_page: data.meta.per_page,
                };
            } catch (error) {
                console.error('Error fetching attendances:', error);
            } finally {
                this.loading = false;
            }
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.fetchAttendances(page);
            }
        },
        viewDetails(attendance) {
            this.selectedAttendance = attendance;
            this.showDetailModal = true;
        },
        formatDate(isoString) {
            const date = new Date(isoString);
            return date.toLocaleDateString('sk-SK', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },
        formatTime(isoString) {
            const date = new Date(isoString);
            return date.toLocaleTimeString('sk-SK', { hour: '2-digit', minute: '2-digit' });
        },
        getStatusClass(color) {
            const classes = {
                green: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                yellow: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                red: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            };
            return classes[color] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
        },
    },
};
</script>
