<template>
  <div v-if="visible" class="toast-wrapper">
    <div
        class="toast show border-0 shadow"
        role="alert"
    >
      <div
          :class="['toast-header', `toast-${type}`]"
      >

        <i v-if="'info'===type" class="bi bi-bell me-2"></i>
        <i v-if="'error'===type" class="bi bi-dash-circle me-2"></i>
        <i v-if="'success'===type" class="bi bi-check2-circle  me-2"></i>
        <i v-if="'alert'===type" class="bi bi-exclamation-diamond  me-2"></i>


        <strong class="me-auto">{{ title }}</strong>
        <button
            type="button"
            class="btn-close btn-close-white me-2 m-auto"
            @click="visible=false"
        ></button>
      </div>

      <div class="toast-body d-flex">
          {{ message }}
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'

const visible = ref(false)
const title = ref('')
const message = ref('')
const type = ref('info')

function show(header, msg, alertType = 'info', timeout = 3000) {
  title.value = header;
  message.value = msg;
  type.value = alertType;
  visible.value = true;

  if (timeout) {
    setTimeout(() => {
      visible.value = false
    }, timeout)
  }
}
// we expose show function so it can be use from outside
defineExpose({ show })
</script>

<style>
.toast-wrapper {
  position: fixed;
  bottom: 100px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1080; /* wyżej niż navbar */
}
</style>