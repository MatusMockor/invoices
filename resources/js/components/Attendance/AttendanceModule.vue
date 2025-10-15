<template>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dochádzka</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Spravujte svoju dochádzku a odpracované hodiny
                </p>
            </div>

            <!-- Check In/Out Widget -->
            <CheckInOutWidget
                :api-routes="apiRoutes"
                :csrf-token="csrfToken"
                @checked-in="handleCheckedIn"
                @checked-out="handleCheckedOut"
            />

            <!-- Attendance List -->
            <AttendanceList
                :api-routes="apiRoutes"
                :csrf-token="csrfToken"
                :key="refreshKey"
            />
        </div>
    </div>
</template>

<script>
import CheckInOutWidget from './CheckInOutWidget.vue';
import AttendanceList from './AttendanceList.vue';

export default {
    name: 'AttendanceModule',
    components: {
        CheckInOutWidget,
        AttendanceList,
    },
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
            refreshKey: 0,
        };
    },
    methods: {
        handleCheckedIn() {
            this.refreshKey++;
        },
        handleCheckedOut() {
            this.refreshKey++;
        },
    },
};
</script>
