<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    currentCompany: {
        type: Object,
        default: null
    },
    companies: {
        type: Array,
        required: true
    },
    switchUrl: {
        type: String,
        required: true
    },
    createUrl: {
        type: String, 
        required: true
    },
    csrfToken: {
        type: String,
        required: true
    }
});

const isOpen = ref(false);
const dropdown = ref(null);

const toggleOpen = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = (event) => {
    if (dropdown.value && !dropdown.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});

const currentCompanyName = computed(() => {
    return props.currentCompany ? props.currentCompany.name : 'Select Company';
});
</script>

<template>
    <div class="ml-3 relative" ref="dropdown">
        <div>
            <button @click.stop="toggleOpen" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                <div>{{ currentCompanyName }}</div>
                <div class="ml-1">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
            </button>
        </div>

        <div v-show="isOpen" class="absolute z-50 mt-2 w-48 rounded-md shadow-lg origin-top-right right-0">
            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-gray-700">
                <div v-for="company in companies" :key="company.id">
                    <form :action="switchUrl.replace('__id__', company.id)" method="POST">
                        <input type="hidden" name="_token" :value="csrfToken">
                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                            <div class="flex items-center">
                                {{ company.name }}
                                <svg v-if="currentCompany && currentCompany.id === company.id" class="ml-2 h-4 w-4 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </form>
                </div>
                
                <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                
                <a :href="createUrl" class="block px-4 py-2 text-left text-sm leading-5 text-blue-500 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                    <div class="flex items-center">
                        <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add New Company
                    </div>
                </a>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add any needed styles here */
</style>

<style scoped>
/* Add any needed styles here */
</style>
