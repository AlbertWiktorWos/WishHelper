<template>
  <div>
    <!-- Loading -->
    <div v-if="wishItemStore.loading" class="text-center py-3">
      <div class="spinner-border"></div>
    </div>

    <div v-else>

      <div class="row">
      <div v-if="wishItemStore.items.length === 0" class="text-center py-3">
        <p class="text-muted">You have no wishes yet. Start by adding your first wish!</p>
      </div>

      <div v-else>
        <div
            v-for="item in wishItemStore.items"
            :key="item.id"
            class="col-md-12 mb-3 position-relative"
        >
          <WishItemCard
              :key="item['@id']"
              :item="item"
              :mode="mode"
              @edit="$emit('edit', item)"
              @delete="$emit('delete', item)"
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
      <div class="d-flex justify-content-between align-items-center mt-4">
        <button
            class="btn btn-outline-secondary btn-sm"
            :disabled="currentPage === 1"
            @click="prevPage"
        >
          Previous
        </button>

        <span>
          Page {{ currentPage }} / {{ totalPages }}
        </span>

        <button
            class="btn btn-outline-secondary btn-sm"
            :disabled="currentPage === totalPages"
            @click="nextPage"
        >
          Next
        </button>

      </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useWishItemStore } from '@js/stores/WishItemStore'
import WishItemCard from './WishItemCard.vue'
import BaseSwitch from "@js/components/BaseSwitch.vue";

const wishItemStore = useWishItemStore()

const props = defineProps({
  filters: Object,
  mode: {
    type: String,
    default: 'readonly'
  }
})

const currentPage = computed(() => wishItemStore.pagination.page)
const totalPages = computed(() => wishItemStore.totalPages)

const nextPage = async () => {
  if (currentPage.value < totalPages.value) {
    await wishItemStore.fetch(
        props.filters,
        currentPage.value + 1,
        wishItemStore.pagination.perPage
    )
    scrollToTop()
  }
}

const prevPage = async () => {
  if (currentPage.value > 1) {
    await wishItemStore.fetch(
        props.filters,
        currentPage.value - 1,
        wishItemStore.pagination.perPage
    )
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
  await wishItemStore.update(item['@id'], { shared: !item.shared })
}

</script>
