<template>
  <div :class="wrapperClass">
    <div class="spinner-border" :class="spinnerClass" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p v-if="text" class="mt-2 mb-0">{{ text }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  text: { type: String, default: '' },
  centered: { type: Boolean, default: true },
  overlay: { type: Boolean, default: false },
  size: { type: String, default: '' }, // '' | 'spinner-border-sm'
  variant: { type: String, default: 'primary' }
})

const wrapperClass = computed(() => {
  return {
    'text-center py-4': props.centered && !props.overlay,
    'loader-overlay d-flex justify-content-center align-items-center': props.overlay
  }
})

const spinnerClass = computed(() => {
  return [
    `text-${props.variant}`,
    props.size
  ]
})
</script>

<style scoped>
.loader-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0.6);
  z-index: 1050;
}
</style>