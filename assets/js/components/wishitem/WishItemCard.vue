<template>
  <div class="card h-100 w-100 mb-3">
    <div class="card-body d-flex flex-column position-relative">

      <!-- TITLE -->
      <h5 class="card-title mb-3">{{ item.title || '-' }}</h5>

      <!-- PRICE SECTION -->
      <div class="mb-2">
        <strong>Price:</strong>
        <span class="text-muted">{{ item.price ?? '-' }} {{ item.currency ? item.currency.code : '' }}</span>
      </div>

      <!-- DESCRIPTION -->
      <p class="card-text small flex-grow-1">
        {{ item.description ?? '-' }}
      </p>

      <!-- LINK SECTION -->
      <div v-if="item.link" class="input-group mb-3 position-relative">
        <input
            type="text"
            class="form-control form-control-sm"
            :value="item.link"
            readonly
        />
        <button class="btn btn-outline-secondary btn-sm" @click="copyLink">
          Copy
        </button>

        <!-- Flash message -->
        <div
            v-if="copied"
            class="toast-notification position-absolute end-0 p-2 bg-success text-white rounded"
        >
          Copied!
        </div>
      </div>

      <!-- CATEGORY + TAGS + ACTIONS IN ONE ROW -->
      <div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">

        <!-- LEFT SIDE: category + tags -->
        <div class="d-flex align-items-center flex-wrap gap-2">

          <!-- CATEGORY -->
          <div v-if="item.category" class="d-flex align-items-center">
            <i v-if="item.category.icon" :class="['bi', item.category.icon]"></i>
            <span class="ms-2">{{ item.category.name }}</span>
          </div>

          <!-- TAGS -->
          <span
              v-for="(tag, index) in item.tags"
              :key="tag.id ?? tag.name + index"
              class="badge bg-primary"
          >
      {{ tag.name }}
    </span>

        </div>

        <!-- RIGHT SIDE: buttons -->
        <div class="d-flex">
          <button class="btn btn-outline-primary me-2" @click="$emit('edit')">
            Edit
          </button>
          <button class="btn btn-outline-danger" @click="$emit('delete')">
            Delete
          </button>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['edit', 'delete'])

const copied = ref(false)

const copyLink = () => {
  if (!props.item.link) return
  navigator.clipboard.writeText(props.item.link)
      .then(() => {
        copied.value = true
        setTimeout(() => copied.value = false, 1500)
      })
      .catch(err => console.error('Failed to copy link', err))
}
</script>

<style scoped>
.card-title {
  border-bottom: 1px solid #eaeaea;
  padding-bottom: 0.3rem;
  margin-bottom: 0.8rem;
}

.input-group input[readonly] {
  background-color: #f8f9fa;
  cursor: pointer;
}

.toast-notification {
  font-size: 0.8rem;
  z-index: 10;
  top: -50px;
}

/* zapewnia trochę przestrzeni w prawym górnym rogu dla switcha */
.card-body {
  padding-top: 1rem;
  padding-right: 3rem;
}
</style>
