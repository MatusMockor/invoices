<template>
    <div class="mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center items-center py-8">
                    <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Active Attendance -->
                <div v-else-if="todayAttendance && todayAttendance.is_active" class="text-center">
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                            <span class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse"></span>
                            Aktívny
                        </span>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Prihlásený od</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ formatTime(todayAttendance.check_in) }}
                        </p>
                    </div>

                    <div class="mb-6">
                        <p class="text-lg text-gray-900 dark:text-white">
                            Odpracované: <span class="font-bold">{{ elapsedTime }}</span>
                        </p>
                    </div>

                    <div class="flex gap-3 justify-center">
                        <button
                            @click="checkOut"
                            :disabled="processing"
                            class="px-6 py-3 bg-red-600 border border-transparent rounded-lg font-semibold text-white hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50"
                        >
                            <span v-if="!processing">Odhlásiť sa</span>
                            <span v-else>Spracováva sa...</span>
                        </button>

                        <button
                            v-if="!activeBreak"
                            @click="showBreakModal = true"
                            class="px-6 py-3 bg-yellow-600 border border-transparent rounded-lg font-semibold text-white hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Začať prestávku
                        </button>

                        <button
                            v-else
                            @click="endBreak"
                            class="px-6 py-3 bg-orange-600 border border-transparent rounded-lg font-semibold text-white hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Ukončiť prestávku
                        </button>
                    </div>

                    <!-- Active Break Info -->
                    <div v-if="activeBreak" class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-sm text-yellow-800 dark:text-yellow-300">
                            Prestávka začatá o {{ formatTime(activeBreak.break_start) }}
                        </p>
                    </div>
                </div>

                <!-- No Active Attendance -->
                <div v-else class="text-center">
                    <div class="mb-4">
                        <p class="text-lg text-gray-600 dark:text-gray-400">
                            Ešte ste sa dnes neprihlásili
                        </p>
                    </div>

                    <button
                        @click="showCheckInModal = true"
                        :disabled="processing"
                        class="px-8 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50"
                    >
                        <span v-if="!processing">Prihlásiť sa</span>
                        <span v-else>Spracováva sa...</span>
                    </button>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm text-red-800 dark:text-red-300">{{ error }}</p>
                </div>
            </div>
        </div>

        <!-- Check In Modal -->
        <div v-if="showCheckInModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
            <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Prihlásiť sa</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Typ práce
                        </label>
                        <select
                            v-model="checkInForm.work_type"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                        >
                            <option value="">Vyberte typ práce</option>
                            <option v-for="(label, value) in workTypes" :key="value" :value="value">{{ label }}</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Poznámka (voliteľná)
                        </label>
                        <textarea
                            v-model="checkInForm.note"
                            rows="3"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                        ></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-end gap-3">
                    <button
                        @click="showCheckInModal = false"
                        class="px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition ease-in-out duration-150"
                    >
                        Zrušiť
                    </button>
                    <button
                        @click="checkIn"
                        :disabled="processing || !checkInForm.work_type"
                        class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition ease-in-out duration-150 disabled:opacity-50"
                    >
                        Prihlásiť sa
                    </button>
                </div>
            </div>
        </div>

        <!-- Break Modal -->
        <div v-if="showBreakModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
            <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Začať prestávku</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Typ prestávky
                        </label>
                        <select
                            v-model="breakForm.break_type"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                        >
                            <option value="lunch">Obed</option>
                            <option value="coffee">Prestávka na kávu</option>
                            <option value="personal">Osobná prestávka</option>
                            <option value="other">Iné</option>
                        </select>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-end gap-3">
                    <button
                        @click="showBreakModal = false"
                        class="px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition ease-in-out duration-150"
                    >
                        Zrušiť
                    </button>
                    <button
                        @click="startBreak"
                        :disabled="processing"
                        class="px-4 py-2 bg-yellow-600 border border-transparent rounded-lg font-semibold text-white hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition ease-in-out duration-150 disabled:opacity-50"
                    >
                        Začať
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CheckInOutWidget',
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
        return {
            loading: true,
            processing: false,
            todayAttendance: null,
            workTypes: {},
            error: null,
            showCheckInModal: false,
            showBreakModal: false,
            checkInForm: {
                work_type: 'office',
                note: '',
            },
            breakForm: {
                break_type: 'lunch',
            },
            elapsedTime: '00:00:00',
            timer: null,
        };
    },
    computed: {
        activeBreak() {
            if (!this.todayAttendance || !this.todayAttendance.breaks) return null;
            return this.todayAttendance.breaks.find(b => b.is_active);
        },
    },
    mounted() {
        this.fetchOptions();
        this.fetchTodayAttendance();
        this.startTimer();
    },
    beforeUnmount() {
        if (this.timer) {
            clearInterval(this.timer);
        }
    },
    methods: {
        async fetchOptions() {
            try {
                const response = await fetch(this.apiRoutes.options);
                const data = await response.json();
                this.workTypes = data.work_types;
            } catch (error) {
                console.error('Error fetching options:', error);
            }
        },
        async fetchTodayAttendance() {
            this.loading = true;
            try {
                const response = await fetch(this.apiRoutes.today);
                const data = await response.json();
                this.todayAttendance = data.attendance;
            } catch (error) {
                this.error = 'Chyba pri načítavaní dnešnej dochádzky';
            } finally {
                this.loading = false;
            }
        },
        async checkIn() {
            this.processing = true;
            this.error = null;

            try {
                const response = await fetch(this.apiRoutes.checkIn, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(this.checkInForm),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Chyba pri prihlasovaní');
                }

                this.todayAttendance = data.attendance;
                this.showCheckInModal = false;
                this.$emit('checked-in');
            } catch (error) {
                this.error = error.message;
            } finally {
                this.processing = false;
            }
        },
        async checkOut() {
            this.processing = true;
            this.error = null;

            try {
                const url = this.apiRoutes.checkOut.replace(':id', this.todayAttendance.id);
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Chyba pri odhlasovaní');
                }

                this.todayAttendance = data.attendance;
                this.$emit('checked-out');
            } catch (error) {
                this.error = error.message;
            } finally {
                this.processing = false;
            }
        },
        async startBreak() {
            this.processing = true;
            this.error = null;

            try {
                const url = this.apiRoutes.startBreak.replace(':id', this.todayAttendance.id);
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(this.breakForm),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Chyba pri začatí prestávky');
                }

                await this.fetchTodayAttendance();
                this.showBreakModal = false;
            } catch (error) {
                this.error = error.message;
            } finally {
                this.processing = false;
            }
        },
        async endBreak() {
            this.processing = true;
            this.error = null;

            try {
                const url = this.apiRoutes.endBreak.replace(':breakId', this.activeBreak.id);
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Chyba pri ukončení prestávky');
                }

                await this.fetchTodayAttendance();
            } catch (error) {
                this.error = error.message;
            } finally {
                this.processing = false;
            }
        },
        formatTime(isoString) {
            const date = new Date(isoString);
            return date.toLocaleTimeString('sk-SK', { hour: '2-digit', minute: '2-digit' });
        },
        startTimer() {
            this.timer = setInterval(() => {
                this.updateElapsedTime();
            }, 1000);
        },
        updateElapsedTime() {
            if (!this.todayAttendance || !this.todayAttendance.is_active) {
                this.elapsedTime = '00:00:00';
                return;
            }

            const start = new Date(this.todayAttendance.check_in);
            const now = new Date();
            const diff = Math.floor((now - start) / 1000); // seconds

            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            this.elapsedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        },
    },
};
</script>
