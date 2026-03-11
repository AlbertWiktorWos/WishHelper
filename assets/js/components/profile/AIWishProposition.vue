<template>
  <div class="card p-3 mb-4">
    <strong class="mb-2">Ask AI</strong>

    <div class="input-group mb-2">
      <input
          v-model="prompt"
          type="text"
          class="form-control"
          placeholder="Try AI to prepare new wish..."
          @keyup.enter="sendPrompt"
      />
      <button
          class="btn btn-primary"
          :disabled="loading || !prompt"
          @click="sendPrompt"
      >
        <span v-if="loading" class="spinner-border spinner-border-sm"></span>
        <span v-else>Send</span>
      </button>
    </div>

    <div v-if="error" class="text-danger">{{ error }}</div>

    <div v-if="wishItem" class="mt-3">
      <WishItemCard
          :item="wishItem"
          mode="readonly"
          @copy="handleCopy"
      />
    </div>
  </div>
</template>

<script setup>
import {ref} from 'vue'
import WishItemCard from '@js/components/wishitem/WishItemCard.vue'
import ApiService from '@js/services/ApiService'
import WishItemService from "@js/services/WishItemService";

const prompt = ref('')
const wishItem = ref(null)
const loading = ref(false)
const error = ref(null)

async function sendPrompt() {
  loading.value = true
  error.value = null
  wishItem.value = null

  try {
    const apiService = new ApiService('/ai/wish_proposition')
    apiService.setErrorMassage('Preparing AI response failed')

    const res = await apiService.post({prompt: prompt.value})

    // map AI response to frontend WishItem format
    wishItem.value = {
      id: null,
      '@id': null,
      title: res.data.title ?? '-',
      description: res.data.description ?? '-',
      price: res.data.price ?? null,
      priceInfoInUserCurrency: null,
      currency: res.data.currency ?? null,
      tags: res.data.tags?.map(tagName => ({name: tagName})) ?? [],
      category: res.data.category ?? null,
      link: res.data.link ?? null,
      shared: false,
      matchPercentage: null,
      createdAt: new Date().toISOString()
    }

  } catch (err) {
    error.value = err.response?.data?.error ?? 'AI request failed'
  } finally {
    loading.value = false
  }
}

async function handleCopy() {

  let itemToCopy = Object.assign({}, wishItem.value);
  if (itemToCopy.category?.['id']) {
    itemToCopy.category = itemToCopy.category['id']
  }

  if (itemToCopy.currency?.['id']) {
    itemToCopy.currency = itemToCopy.currency['id']
  }

  delete (itemToCopy['id']);

  WishItemService.setErrorMassage('Error occurred during copying, please try again later or create new Wish proposition');
  const result = await WishItemService.post(itemToCopy);
  if(result){
    window.$toast('Success!', 'The wish was successfully copied. You can find it at your wish list!', 'success')
    wishItem.value = null;
  }


}
</script>