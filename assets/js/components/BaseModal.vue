<template>
  <div ref="modalRef" class="modal fade" tabindex="-1">
    <div :class="['modal-dialog', sizeClass]">
      <div class="modal-content">

        <div v-if="$slots.header" class="modal-header">
          <slot name="header" />
          <button
              v-if="closable"
              type="button"
              class="btn-close ms-auto"
              @click="hide"
          />
        </div>

        <div class="modal-body p-4">
          <slot />
        </div>

        <div v-if="$slots.footer" class="modal-footer">
          <slot name="footer" />
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { Modal } from 'bootstrap'

const props = defineProps({
  size: { type: String, default: null }, // 'lg', 'sm', 'xl'
  centered: { type: Boolean, default: false },
  closable: { type: Boolean, default: true }
})

const modalRef = ref(null)
let instance = null

const sizeClass = computed(() => {
  return [
    props.size ? `modal-${props.size}` : '',
    props.centered ? 'modal-dialog-centered' : ''
  ]
})

onMounted(() => {
  instance = new Modal(modalRef.value)
})

onBeforeUnmount(() => {
  instance?.dispose()
})

function show() {
  instance?.show();
}
function hide() {
  instance?.hide();
}

// we exposing this methods that parent could manipulate showing and closing of modal
defineExpose({ show, hide })
</script>