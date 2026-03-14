<template>
  <div class="card p-4 shadow-sm mb-4">
    <div class="position-absolute top-0 end-0 m-3 text-muted">
      <Tooltip text="AI will try to prepare wish recommendation based on prompt and your country, max price setting, observed categories and tags." position="top">
        <i class="bi bi-info-circle"
        ></i>
      </Tooltip>
    </div>

    <div class="d-flex align-items-center mb-3">
      <i class="bi bi-robot me-2 fs-4 text-primary"></i>
      <h5 class="mb-0">Ask AI for Gift Ideas</h5>
    </div>

    <div class="input-group mb-2">
      <input
          v-model="prompt"
          type="text"
          class="form-control"
          placeholder="e.g. Something for a programmer who loves coffee..."
          :disabled="loading"
          @keyup.enter="sendPrompt"
      />
      <button
          class="btn btn-primary px-4"
          :disabled="loading || !prompt.trim()"
          @click="sendPrompt"
      >
        <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
        <span v-else><i class="bi bi-send-fill me-1"></i> Send</span>
      </button>
    </div>

    <div v-if="successMessage" class="alert alert-success py-2 mt-2 d-flex align-items-center">
      <i class="bi bi-check-circle-fill me-2"></i>
      {{ successMessage }}
    </div>

    <div v-if="error" class="alert alert-danger py-2 mt-2">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      {{ error }}
    </div>

    <div v-if="recentPrompts.length > 0" class="mt-4">
      <h6 class="text-muted border-bottom pb-2 small fw-bold text-uppercase">Recent Requests</h6>
      <ul class="list-group list-group-flush shadow-sm rounded">
        <li
            v-for="(item, index) in recentPrompts"
            :key="index"
            class="list-group-item d-flex justify-content-between align-items-center bg-light-subtle py-2"
        >
          <div class="text-truncate me-2" :title="item.text">
            <span class="small text-muted me-2">{{ item.time }}</span>
            <span class="fw-medium text-dark">{{ item.text }}</span>
          </div>
          <span v-if="item.status === 'sent'" class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
            <i class="bi bi-cpu me-1"></i> Queued
          </span>
          <span v-else class="badge rounded-pill bg-danger-subtle text-danger">
            Failed
          </span>
        </li>
      </ul>
      <p class="small text-muted mt-2 ps-1 italic">
        <i class="bi bi-info-circle me-1"></i> AI is processing. New recommendations will appear in the notifications when ready.
      </p>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import ApiService from '@js/services/ApiService'
import Tooltip from "@js/components/Tooltip.vue";

const prompt = ref('')
const loading = ref(false)
const error = ref(null)
const successMessage = ref(null)
const recentPrompts = ref([]) // Zwykła tablica z historią wysyłek

async function sendPrompt() {
  if (!prompt.value.trim()) return

  loading.value = true
  error.value = null
  successMessage.value = null

  const currentPrompt = prompt.value.trim()

  try {
    const apiService = new ApiService('/ai/wish_proposition')
    apiService.setErrorMassage('Preparing AI response failed')

    await apiService.post({ prompt: currentPrompt })

    // Sukces - dodajemy do lokalnej historii
    recentPrompts.value.unshift({
      text: currentPrompt,
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      status: 'sent'
    });

    successMessage.value = "Request sent to AI! It's being processed in the background."
    prompt.value = '' // Czyścimy input

    // Ukryj wiadomość sukcesu po 5 sekundach
    setTimeout(() => { successMessage.value = null }, 5000)

  } catch (err) {
    error.value = err.response?.data?.error ?? 'AI request failed'

    // Dodajemy do historii nawet jeśli błąd (opcjonalnie)
    recentPrompts.value.unshift({
      text: currentPrompt,
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      status: 'error'
    });
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.bg-light-subtle {
  background-color: #f8f9fa;
}
.italic {
  font-style: italic;
}
</style>