<template>

  <form @submit.prevent="submit">

    <div class="mb-3">
      <label>Nick name</label>
      <input
          type="text"
          class="form-control"
          v-model="form.nickName"
      >
    </div>

    <SearchComponent
        type="country"
        v-model="form.country"
        label="Country"
        id="country"
    />

    <div class="d-grid mt-4">
      <button
          type="submit"
          class="btn btn-accent"
          :disabled="saving"
      >
        {{ saving ? 'Saving...' : 'Save changes' }}
      </button>
    </div>

    <div v-if="error" class="alert alert-danger mt-3">
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
})

const emit = defineEmits(['save'])

const form = reactive({
  nickName: '',
  country: '',
})

watch(() => props.user, (val) => {
  if (!val) return

  form.nickName = val.nickName;
  form.country = val.country;
}, { immediate: true })

const submit = () => {

  if(form.country
      && typeof form.country === 'object'
      && form.country['@id']){
    form.country = form.country['@id'];
  }

  emit('save', {
    nickName: form.nickName,
    country: form.country
  })
}
</script>
