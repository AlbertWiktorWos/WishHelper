<template>
  <form @submit.prevent="submit" :class="{ 'is-readonly': readonly }">
    <div class="mb-3">
      <label>Nick name</label>
      <input
          type="text"
          class="form-control"
          v-model="form.nickName"
          :readonly="readonly"
      >
    </div>

    <SearchComponent
        :disabled="readonly"
        type="country"
        v-model="form.country"
        label="Country"
        id="country"
    />

    <div class="mb-3 mt-3">
      <label>Max Price</label>
      <input
          type="number"
          min="0.00"
          step="0.01"
          v-model="form.maxPrice"
          class="form-control"
          :readonly="readonly"
      />
    </div>

    <div v-if="!readonly" class="d-grid mt-4">
      <button
          type="submit"
          class="btn btn-accent"
          :disabled="saving"
      >
        {{ saving ? 'Saving...' : 'Save changes' }}
      </button>
    </div>

    <div v-if="error && !readonly" class="alert alert-danger mt-3">
      {{ error }}
    </div>
  </form>
</template>

<script setup>
import { reactive, watch } from 'vue'
import SearchComponent from '@js/components/search/SearchComponent.vue'

const props = defineProps({
  user: { type: Object, required: true },
  saving: Boolean,
  error: null,
  readonly: Boolean // Nowy prop sterujący trybem
})

const emit = defineEmits(['save'])

const form = reactive({
  nickName: '',
  country: '',
  maxPrice: 0,
})

// Synchronizacja danych przy zmianie usera lub wejściu w edycję
watch(() => props.user, (val) => {
  if (!val) return;
  form.nickName = val.nickName;
  form.country = val.country;
  form.maxPrice = val.maxPrice;
}, { immediate: true, deep: true })

const submit = () => {
  let countryId = form.country;
  if(form.country && typeof form.country === 'object' && form.country['@id']) {
    countryId = form.country['@id'];
  }

  emit('save', {
    nickName: form.nickName,
    country: countryId,
    maxPrice: form.maxPrice
  })
}
</script>

<style scoped>

</style>