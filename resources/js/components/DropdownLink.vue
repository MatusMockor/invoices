<template>
  <a :href="href" 
     @click="onClick"
     class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
    <slot></slot>
  </a>
</template>

<script>
export default {
  props: {
    href: {
      type: String,
      required: true
    },
    method: {
      type: String,
      default: 'get'
    },
    as: {
      type: String,
      default: 'a'
    }
  },
  emits: ['click'],
  methods: {
    onClick(e) {
      this.$emit('click', e)
    }
  }
}
</script>
