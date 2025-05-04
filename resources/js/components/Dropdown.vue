<template>
  <div class="relative">
    <div @click="toggleDropdown">
      <slot name="trigger"></slot>
    </div>

    <div v-show="isOpen" 
         :class="[
            'absolute z-50 mt-2 rounded-md shadow-lg transition-all duration-200 transform',
            width,
            alignmentClasses
         ]"
         @click="isOpen = false">
      <div :class="['rounded-md ring-1 ring-black ring-opacity-5', contentClasses]">
        <slot name="content"></slot>
      </div>
    </div>
  </div>
</template>

<script>
import { onBeforeUnmount, onMounted, ref, computed } from 'vue'

export default {
  props: {
    align: {
      type: String,
      default: 'right'
    },
    width: {
      type: String,
      default: 'w-48'
    },
    contentClasses: {
      type: String,
      default: 'py-1 bg-white dark:bg-gray-700'
    }
  },
  setup() {
    const isOpen = ref(false)
    
    const toggleDropdown = () => {
      isOpen.value = !isOpen.value
    }
    
    const closeDropdown = () => {
      isOpen.value = false
    }
    
    let handleOutsideClick = null
    
    onMounted(() => {
      handleOutsideClick = (e) => {
        const el = document.querySelector('.relative')
        if (el && !el.contains(e.target)) {
          closeDropdown()
        }
      }
      
      document.addEventListener('click', handleOutsideClick)
    })
    
    onBeforeUnmount(() => {
      document.removeEventListener('click', handleOutsideClick)
    })
    
    return {
      isOpen,
      toggleDropdown,
      closeDropdown
    }
  },
  computed: {
    alignmentClasses() {
      switch (this.align) {
        case 'left':
          return 'origin-top-left left-0'
        case 'top':
          return 'origin-top'
        case 'right':
        default:
          return 'origin-top-right right-0'
      }
    }
  }
}
</script>
