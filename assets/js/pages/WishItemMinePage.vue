<template>

  <div>
    <!-- Modal -->
    <div ref="formModalRef" class="modal fade show" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editedItem ? 'Edit Wish' : 'Create Wish' }}</h5>
            <button type="button" class="btn-close" @click="closeForm"></button>
          </div>
          <div class="modal-body">

            <WishItemForm
                :item="editedItem"
                :saving="wishItemStore.saving"
                @save="handleSave"
                @cancel="closeForm"
            />

          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Modal -->
    <div ref="confirmModalRef" class="modal fade" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger">Delete confirmation</h5>
            <button type="button" class="btn-close" @click="confirmInstance.hide()"></button>
          </div>
          <div class="modal-body">
            <p>
              Are you sure you want to delete
              <strong>{{ itemToDelete?.title }}</strong>?
            </p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="confirmInstance.hide()">
              Cancel
            </button>
            <button class="btn btn-danger" @click="confirmDelete">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal backdrop -->
    <div class="modal-backdrop fade show" v-if="showModal"></div>

    <div class="container py-5">

      <div v-if="wishItemStore.loading || !wishItemStore.data" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading wishes...</p>
      </div>

      <div v-else class="row g-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>My Wishes</h2>
            <button class="btn btn-primary" @click="openCreate">
              Add Wish
            </button>
        </div>

        <WishItemMine
            :items="wishItemStore.items"
            :loading="wishItemStore.loading"
            @delete="handleDelete"
            @edit="handleEdit"
        />

      </div>
    </div>
  </div>

</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useWishItemStore } from '@js/stores/WishItemStore'
import WishItemForm from '@js/components/wishitem/WishItemForm.vue'
import WishItemMine from "@js/components/wishitem/WishItemMine.vue";
import { useProfileStore } from "@js/stores/ProfileStore";
import { Modal } from 'bootstrap'

const formModalRef = ref(null) // form modal
let modalInstance = null
const confirmModalRef = ref(null) // confirm modal
let confirmInstance = null

const wishItemStore = useWishItemStore()
const profileStore = useProfileStore()

const editedItem = ref(null)
const itemToDelete = ref(null)

onMounted(async () => {
  wishItemStore.isLoading = true
  modalInstance = new Modal(formModalRef.value)
  confirmInstance = new Modal(confirmModalRef.value)

  debugger;
  await profileStore.fetchMe();
  debugger;
  await wishItemStore.fetch({
    owner: profileStore.data?.id
  });
})

const showModal = ref(false)

const openCreate = () => {
  debugger;
  editedItem.value = null
  modalInstance.show()
}

const handleEdit = (item) => {
  editedItem.value = item
  modalInstance.show()
}

const handleSave = async (payload) => {
  debugger;

  debugger;
  if(payload.category
      && typeof payload.category === 'object'
      && payload.category['@id']){
    payload.category = payload.category['@id'];
  }
  debugger;
  if(payload.currency
      && typeof payload.currency === 'object'
      && payload.currency['@id']){
    payload.currency = payload.currency['@id'];
  }

  if (editedItem.value) {
    debugger;
    await wishItemStore.update(editedItem.value['@id'], payload);
    editedItem.value = null;
  } else {
    await wishItemStore.add(payload);
  }
  closeForm()
}

const handleDelete = async (item) => {
  debugger;
  itemToDelete.value = item;
  confirmInstance.show();
}

const confirmDelete = async () => {
  if (!itemToDelete.value) {
    return;
  }
  await wishItemStore.remove(itemToDelete.value['@id']);
  confirmInstance.hide();
  itemToDelete.value = null;
}

const closeForm = () => {
  modalInstance.hide();
}
</script>
