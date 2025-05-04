<template>
  <div class="hidden sm:flex sm:items-center sm:ms-6">
    <Dropdown align="right" width="48">
      <template #trigger>
        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
          <div>{{ userName }}</div>

          <div class="ms-1">
            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </div>
        </button>
      </template>

      <template #content>
        <DropdownLink :href="profileRoute">
          {{ profileText }}
        </DropdownLink>

        <!-- Authentication -->
        <form method="POST" :action="logoutRoute">
          <input type="hidden" name="_token" :value="csrfToken">

          <DropdownLink :href="logoutRoute" @click.prevent="logout">
            {{ logoutText }}
          </DropdownLink>
        </form>
      </template>
    </Dropdown>
  </div>
</template>

<script>
import Dropdown from './Dropdown.vue';
import DropdownLink from './DropdownLink.vue';

export default {
  components: {
    Dropdown,
    DropdownLink
  },
  props: {
    userName: {
      type: String,
      required: true
    },
    profileRoute: {
      type: String,
      required: true
    },
    logoutRoute: {
      type: String,
      required: true
    },
    profileText: {
      type: String,
      default: 'Profile'
    },
    logoutText: {
      type: String,
      default: 'Log Out'
    },
    csrfToken: {
      type: String,
      required: true
    }
  },
  methods: {
    logout(e) {
      e.preventDefault();
      this.$el.querySelector('form').submit();
    }
  }
}
</script>
