<template>

  <div>
    <!-- FORM MODAL -->
    <BaseModal
        ref="formModalRef"
        size="lg"
    >
      <template #header>
        <h5 class="modal-title">
          {{ editedItem ? 'Edit Wish' : 'Create Wish' }}
        </h5>
      </template>

      <WishItemForm
          :item="editedItem"
          :saving="wishItemStore.saving"
          @save="handleSave"
      />
    </BaseModal>


    <!-- Confirm Modal -->

    <BaseModal
        ref="confirmModalRef"
        size="sm"
        centered="centered"
    >
      <template #header>
          <h5 class="modal-title text-danger">Delete confirmation</h5>
      </template>

      <p>
        Are you sure you want to delete
        <strong>{{ itemToDelete?.title }}</strong>?
      </p>

      <template #footer>
        <button class="btn btn-danger" @click="confirmDelete">
          Delete
        </button>
      </template>

    </BaseModal>

    <!-- Modal backdrop -->
    <div class="modal-backdrop fade show" v-if="showModal"></div>

    <div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>My Wishes</h2>
      <button class="btn btn-primary" @click="openCreate">
        Add Wish
      </button>
    </div>

    <WishItemList
        :filters="filters"
        mode="owner"
        @edit="handleEdit"
        @delete="handleDelete"
    />

  </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useWishItemStore } from '@js/stores/WishItemStore'
import WishItemList from '@js/components/wishitem/WishItemList.vue'
import WishItemForm from '@js/components/wishitem/WishItemForm.vue'
import { useProfileStore } from "@js/stores/ProfileStore";
import BaseModal from "@js/components/BaseModal.vue";

const formModalRef = ref(null)
const confirmModalRef = ref(null) // confirm modal

const wishItemStore = useWishItemStore()
const profileStore = useProfileStore()

const editedItem = ref(null)
const itemToDelete = ref(null)
const filters = ref({})

onMounted(async () => {
  wishItemStore.isLoading = true

  await profileStore.fetchMe()

  filters.value = {
    owner: profileStore.data?.id
  }

  await wishItemStore.fetch(filters.value, 1, 10)
})

const openCreate = () => {
  editedItem.value = null
  formModalRef.value.show()
}

const handleEdit = (item) => {
  editedItem.value = item
  formModalRef.value.show()
}

const handleSave = async (payload) => {
  if (payload.category?.['@id']) {
    payload.category = payload.category['@id']
  }

  if (payload.currency?.['@id']) {
    payload.currency = payload.currency['@id']
  }

  if (editedItem.value) {
    await wishItemStore.update(editedItem.value['@id'], payload)
    if(!wishItemStore.error){
      window.$toast('Success!', 'The wish was successfully updated', 'success')
    }
  } else {
    await wishItemStore.add(payload)
    if(!wishItemStore.error){
      window.$toast('Success!', 'The wish was successfully added', 'success')
    }
  }

  formModalRef.value.hide()
}

const handleDelete = async (item) => {
  itemToDelete.value = item;
  confirmModalRef.value.show();
}

const confirmDelete = async () => {
  if (!itemToDelete.value) {
    return;
  }
  await wishItemStore.remove(itemToDelete.value['@id']);
  confirmModalRef.value.hide();
  itemToDelete.value = null;
  if(!wishItemStore.error){
    window.$toast('Success!', 'The wish was successfully deleted', 'success')
  }
}


</script>