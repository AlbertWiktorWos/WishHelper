<template>

  <div>
    <BaseModal
        ref="confirmModalRef"
        size="sm"
        centered="centered"
    >
      <template #header>
        <h5 class="modal-title text-primary">Copy confirmation</h5>
      </template>

      <p>
        Are you sure you want to copy this wish to yourself?
        <strong>{{ itemToCopy?.title }}</strong>?
      </p>

      <template #footer>
        <button class="btn btn-accent" @click="confirmCopy">
          Copy
        </button>
      </template>

    </BaseModal>

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
import BaseModal from "@js/components/BaseModal.vue";

const confirmModalRef = ref(null) // confirm modal

const wishItemStore = useWishItemStore()
const profileStore = useProfileStore()

const itemToCopy = ref(null)
const filters = ref({})

onMounted(async () => {
  wishItemStore.isLoading = true

  await profileStore.fetchMe();

  filters.value = {
    not_owner: true,
    shared: true
  }

  await wishItemStore.fetch(filters.value);
})

const handleCopy = async (item) => {
  itemToCopy.value = item;
  confirmModalRef.value.show();
}

const confirmCopy = async () => {
  if (!itemToCopy.value) {
    return;
  }
  let clone = Object.assign({}, itemToCopy.value);

  if (clone.category?.['@id']) {
    clone.category = clone.category['@id']
  }

  if (clone.currency?.['@id']) {
    clone.currency = clone.currency['@id']
  }
  delete(clone['@id']);
  await wishItemStore.add(clone);
  confirmModalRef.value.hide();
  itemToCopy.value = null;

  if(!wishItemStore.error){
    window.$toast('Success!', 'The wish was successfully copied', 'success')
  }
  await wishItemStore.fetch(filters.value);
}

</script>
