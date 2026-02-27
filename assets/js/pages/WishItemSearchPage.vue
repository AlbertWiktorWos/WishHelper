<template>

  <div>
    <!-- Confirm Modal -->
    <div ref="confirmModalRef" class="modal fade" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-primary">Copy confirmation</h5>
            <button type="button" class="btn-close" @click="confirmInstance.hide()"></button>
          </div>
          <div class="modal-body">
            <p>
              Are you sure you want to copy this wish to yourself?
              <strong>{{ itemToCopy?.title }}</strong>?
            </p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="confirmInstance.hide()">
              Cancel
            </button>
            <button class="btn btn-accent" @click="confirmCopy">
              Copy
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal backdrop -->
    <div class="modal-backdrop fade show" v-if="showModal"></div>

    <div class="container">

        <WishItemList
            :filters="filters"
            :items="wishItemStore.items"
            :loading="wishItemStore.loading"
            @copy="handleCopy"
            mode="readonly"
        />

    </div>
  </div>

</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useWishItemStore } from '@js/stores/WishItemStore'
import WishItemList from "@js/components/wishitem/WishItemList.vue";
import { useProfileStore } from "@js/stores/ProfileStore";
import { Modal } from 'bootstrap'

const confirmModalRef = ref(null) // confirm modal
let confirmInstance = null

const wishItemStore = useWishItemStore()
const profileStore = useProfileStore()

const itemToCopy = ref(null)
const filters = ref({})

onMounted(async () => {
  debugger;
  wishItemStore.isLoading = true
  confirmInstance = new Modal(confirmModalRef.value)

  await profileStore.fetchMe();
  debugger;

  filters.value = {
    not_owner: true,
    shared: true
  }

  await wishItemStore.fetch(filters.value);
})

const handleCopy = async (item) => {
  debugger;
  itemToCopy.value = item;
  confirmInstance.show();
}

const confirmCopy = async () => {
  if (!itemToCopy.value) {
    return;
  }
debugger;
  let clone = Object.assign({}, itemToCopy.value);

  if (clone.category?.['@id']) {
    clone.category = clone.category['@id']
  }

  if (clone.currency?.['@id']) {
    clone.currency = clone.currency['@id']
  }
  delete(clone['@id']);
  await wishItemStore.add(clone);
  confirmInstance.hide();
  itemToCopy.value = null;
}

</script>
