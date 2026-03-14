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
        <i v-if="'alert'===type" class="bi bi-exclamation-diamond me-2"></i>


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

      <div v-if="shouldShowButton" class="mt-2 pt-2 border-top text-end p-2">
        <a :href="redirectLink" class="pointer-event">
          See details
          <i class="bi bi-arrow-right-circle"></i>
        </a>
      </div>

    </div>
  </div>
</template>
<script setup>
import {computed, ref} from 'vue'

const visible = ref(false)
const title = ref('')
const message = ref('')
const type = ref('info')
const redirectLink = ref(null)
const alertTimeout = ref(null)

function show(header, msg, alertType = 'info', timeout = 5000, redirect) {

  // we delete the previous timeout
  if (alertTimeout.value){
    clearTimeout(alertTimeout.value);
  }
  if (redirect){
    redirectLink.value = getRoute(redirect);
  }

  title.value = header;
  message.value = msg;
  type.value = alertType;
  visible.value = true;

  if (timeout) {
    alertTimeout.value = setTimeout(() => {
      visible.value = false
    }, timeout)
  }
}

function getRoute(routeName) {
  return window.APP_ROUTES[routeName];
}

// Logic for checking whether to show a button
const shouldShowButton = computed(() => {
  if (!redirectLink.value) return false;

  try {
    const currentPath = window.location.pathname;

    // Create a URL object from the link to extract only the pathname
    // If the link is relative (e.g., /wish/show/10), we need to provide the database
    const targetUrl = new URL(redirectLink.value, window.location.origin);

// We compare paths (ignore domain and query parameters if necessary)
    return currentPath !== targetUrl.pathname;
  } catch (e) {
    return false;
  }
})

// we expose show function so it can be use from outside
defineExpose({ show })
</script>

<style>
.toast-wrapper {
  position: fixed;
  bottom: 100px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1080; /* higher than the navbar */
}
</style>