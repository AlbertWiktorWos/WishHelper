<template>
  <div>
    <!-- Loading -->
    <div v-if="store.loading" class="text-center py-3">
      <div class="spinner-border"></div>
    </div>

    <div v-else>
      <!-- List of wishes -->
      <div class="row">

        <div v-if="store.data.length===0" class="text-center py-3">
          <p class="text-muted">You have no wishes yet. Start by adding your first wish!</p>
        </div>
        <div v-else>

          <div
              v-for="item in paginatedData"
              :key="item.id"
              class="col-md-12 mb-3"
          >
            <div class="position-relative">
              <WishItemCard
                  :item="item"
                  @delete="$emit('delete', item)"
                  @edit="$emit('edit', item)"
              />

              <div class="position-absolute top-0 end-0 m-2 text-end">
                <BaseSwitch
                    :model-value="item.shared"
                    @update:modelValue="value => toggleShare(item, value)"
                />
                <span class="text-muted"> {{item.shared ? 'Shared' : 'Private'}} </span>
              </div>
            </div>
          </div>


          <!-- Pagination -->
          <div class="d-flex justify-content-between align-items-center mt-3">
            <button
                class="btn btn-sm btn-outline-secondary"
                :disabled="currentPage === 1"
                @click="prevPage"
            >
              Previous
            </button>

            <span>Page {{ currentPage }} / {{ totalPages }}</span>

            <button
                class="btn btn-sm btn-outline-secondary"
                :disabled="currentPage === totalPages"
                @click="nextPage"
            >
              Next
            </button>
          </div>

        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useWishItemStore } from '@js/stores/WishItemStore'
import WishItemCard from './WishItemCard.vue'
import BaseSwitch from "@js/components/BaseSwitch.vue";

const store = useWishItemStore()

// Pagination state
const currentPage = ref(1)
const perPage = ref(5) // ile elementów na stronę

// Computed list of items for current page
const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return store.data.slice(start, end)
})

// Total pages
const totalPages = computed(() => Math.ceil(store.data.length / perPage.value))

// Pagination functions
const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    scrollToTop()
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    scrollToTop()
  }
}

// helper
const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth' // smooth scrolling
  })
}

// Actions
const toggleShare = async (item) => {
  await store.update(item['@id'], { shared: !item.shared })
}

// Reset to page 1 when store data changes (ex. fetch)
watch(() => store.data.length, () => currentPage.value = 1)
</script>
