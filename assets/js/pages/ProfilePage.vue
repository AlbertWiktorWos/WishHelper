<template>
  <div class="container py-5">
    <!-- Loading -->
    <div v-if="store.loading || !store.data" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading profile...</p>
    </div>

    <div v-else class="row g-4">

      <!-- LEFT COLUMN -->
      <div class="col-md-3">

        <!-- Avatar + nickname -->
        <div class="d-flex align-items-center mb-4">
          <div class="position-relative">
            <img
                :src="store.data.avatarUrl ?? require('@images/avatar.png')"
                class="rounded-circle"
                style="width: 120px; height: 120px; object-fit: cover;"
            />
            <button
                class="btn btn-sm btn-secondary position-absolute bottom-0 end-0"
                @click="uploadAvatar"
                title="Change avatar"
            >
              <i class="bi bi-camera"></i>
            </button>
          </div>
          <div class="ms-3">
            <h3>{{ store.data.nickName ?? '-' }}</h3>
            <p class="text-muted">Joined: {{ formattedDate(store.data.createdAt) ?? '-' }}</p>
          </div>
        </div>
        <p class="text-muted w-100 text-center">{{ store.data.email ?? '-' }}</p>

        <!-- User data -->
        <div class="card p-3 mb-4">
          <div class="text-end">
            <button class="btn btn-outline-primary btn-sm w-50 float-right" @click="editMode = !editMode">
              {{ editMode ? 'Cancel' : 'Edit' }}
            </button>
          </div>


          <div v-if="!editMode">
            <p><strong>Nick:</strong> {{ store.data.nickName ?? '-' }}</p>
            <p>
              <strong>Country:</strong> {{ store.data.country?.name ?? '-' }}
              <img v-if="store.data.country?.flag" :src="store.data.country.flag" alt="" class="me-2" style="width: 20px; height: 14px; object-fit: cover;">
            </p>
          </div>
          <div v-if="editMode">
            <ProfileForm
                v-if="editMode"
                :user="store.data"
                :saving="store.saving"
                :error="store.error"
                @save="handleSave"
            />
          </div>
        </div>



        <!-- Categories -->
        <div class="card p-3 mb-4">

          <div class="position-absolute top-0 end-0 m-3 text-muted">
            <Tooltip text="You will only receive notifications about wishes that fall into a given category." position="top">
              <i class="bi bi-info-circle"
              ></i>
            </Tooltip>
          </div>


          <strong>Observed Categories</strong>

          <div class="mt-2">
            <div v-for="cat in categoriesStore.data" :key="cat.id">

              <i v-if="cat.icon" :class="['bi', cat.icon]"></i>

              <label class="ms-2 form-check-label" :for="'cat-'+cat.id">
                {{ cat.label }}
              </label>

              <input
                  class="form-check-input float-end"
                  type="checkbox"
                  :id="'cat-'+cat.id"
                  :checked="store.data.categories?.some(c => c === cat['@id']) ?? false"
                  @change="handleCategoryChange($event, cat)"
              />
            </div>
          </div>
        </div>

      </div>

      <!-- RIGHT COLUMN -->
      <div class="col-md-9">

        <!-- Followed tags -->
        <div class="card p-3 mb-4">

          <div class="position-absolute top-0 end-0 m-3 text-muted">
            <Tooltip text="The tags you follow will affect the wishes notifications you receive." position="top">
              <i class="bi bi-info-circle"
              ></i>
            </Tooltip>
          </div>

          <strong>Observed tags</strong>

          <TagInput
              v-model="tags"
          />
          <p v-if="tagError" class="align-self-center text-danger">
            {{ tagError }}
          </p>

        </div>
        <!-- Changing Notifications -->
        <div class="card p-3 mb-4">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <strong>Email notifications</strong>
              <p class="mb-0 text-muted">Receive updates about new content and recommendations.</p>
            </div>

            <BaseSwitch
                :model-value="store.data.notify ?? false"
                @update:modelValue="value => toggleNotifications(value)"
            />
          </div>
        </div>

        <!-- Notifications -->
        <div class="card p-3 mb-4">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <strong>Notifications</strong>
              <p class="mb-0 text-muted"> These wishes may interest you </p>
            </div>
            ...
          </div>



        </div>


      </div>
    </div>
  </div>
</template>

<script setup>
import {ref, onMounted, computed, nextTick} from 'vue';
import { useProfileStore } from '@js/stores/ProfileStore';
import { useCategoryStore } from '@js/stores/CategoryStore';
import ProfileForm from '@js/components/profile/ProfileForm.vue';
import TagInput from "@js/components/TagInput.vue";
import Tooltip from "@js/components/Tooltip.vue";
import BaseSwitch from "@js/components/BaseSwitch.vue";

const store = useProfileStore();
const categoriesStore = useCategoryStore();
const editMode = ref(false);
const newTag = ref('');
let tagError = ref(null);

// Fetch user profile + categories on mount
onMounted(async () => {
  if (!store.isLoaded){
    await store.fetchMe();
  }
  if (categoriesStore.data.length === 0){
    await categoriesStore.fetch();
  }
})

// checkbox categories computed
const handleCategoryChange = async (event, cat) => {
  if (!store.data.categories) {
    store.data.categories = [];
  }

  if (event.target.checked) {
    if (!store.data.categories.find(c => c === cat['@id'])) {
      store.data.categories.push(cat['@id']);
    }
  } else {
    store.data.categories =
        store.data.categories.filter(c => c !== cat['@id']);
  }

  await updateCategories();
}


const tags = computed({
  get() {
    return store.data?.tags ?? [];
  },
  set(value) {
    if (!store.data) return;
    store.data.tags = value;
    updateTags(value);
  }
})

const updateTags = async (tags) => {
  try {
    tagError.value = null;
    await store.update({ tags });
  } catch (err) {
    store.data.tags.pop();
    tagError.value = 'Failed to update tags.';
  }

}

const formattedDate = (date) => date ? new Date(date).toLocaleDateString() : '-'

// CRUD functions
const handleSave = async (payload) => {
  await store.update(payload)
  editMode.value = false
}

const uploadAvatar = () => {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';

  input.onchange = async (e) => {
    const file = e.target.files[0]
    const formData = new FormData()
    formData.append('file', file)
    const response = await fetch('/api/profile/avatar', {
      method: 'POST',
      body: formData,
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });

    const data = await response.json();
    store.data.avatarUrl = data.avatarUrl;
  }

  input.click();
}


const toggleNotifications = async (value) => {
  store.data.notify = value;
  await store.update({notify: value});
}

const updateCategories = async () => {
  await store.update({categories: store.data.categories});
}

</script>

<style lang="scss" scoped>

</style>
