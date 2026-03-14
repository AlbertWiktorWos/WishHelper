<template>
  <div class="container py-5">
    <!-- Loading -->
    <Loader v-if="store.loading || !store.data" text="Loading profile..." />

    <div v-else class="row g-4">

      <!-- LEFT COLUMN -->
      <div class="col-sm-12 col-md-4 col-xl-3">

        <div class="row">
          <div class="col-sm-6 col-md-12">
            <!-- Avatar + nickname -->
            <h3>{{ store.data.nickName ?? '-' }}</h3>
            <div class="d-flex align-items-center mb-4">
              <div class="position-relative">
                <img
                    :src="store.data.avatarUrl ?? require('@images/avatar.png')"
                    class="rounded-circle"
                    style="max-width: 200px; max-height: 200px; object-fit: cover;"
                />
                <button
                    class="btn btn-sm btn-secondary position-absolute bottom-0 end-0"
                    @click="uploadAvatar"
                    title="Change avatar"
                >
                  <i class="bi bi-camera"></i>
                </button>
              </div>
            </div>
            <div class="ms-3">
              <p class="text-muted">Joined: {{ formattedDate(store.data.createdAt) ?? '-' }}</p>
              <p class="text-muted w-100 text-break-all">{{ store.data.email ?? '-' }}</p>
            </div>
          </div>

          <!-- User data -->
          <div class="col-sm-6 col-md-12">
            <div class="card p-3 mb-4">
              <div class="text-end">
                <button class="btn btn-outline-primary btn-sm w-100 mb-3" @click="editMode = !editMode">
                  {{ editMode ? 'Cancel' : 'Edit' }}
                </button>
              </div>

              <ProfileForm
                  :user="store.data"
                  :saving="store.saving"
                  :error="store.error"
                  @save="handleSave"
                  :readonly="!editMode"
              />
            </div>
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


          <strong class="pe-2">Observed Categories</strong>

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
      <div class="col-sm-12 col-md-8 col-xl-9">

        <!-- Followed tags -->
        <div class="card p-4 shadow-sm mb-4">

          <div class="position-absolute top-0 end-0 m-3 text-muted">
            <Tooltip text="The tags you follow will affect the wishes notifications you receive." position="top">
              <i class="bi bi-info-circle"
              ></i>
            </Tooltip>
          </div>

          <div class="d-flex align-items-center mb-3">
            <i class="bi bi-flag me-2 fs-4 text-primary"></i>
            <h5 class="mb-0">Observed tags</h5>
          </div>

          <TagInput
              v-model="tags"
          />
          <p v-if="tagError" class="align-self-center text-danger">
            {{ tagError }}
          </p>

        </div>
        <!-- Changing Notifications -->
        <div class="card p-4 shadow-sm mb-4">
          <div class=" d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <i class="bi bi-envelope-at me-2 fs-4 text-primary"></i>
              <h5 class="mb-0">Ask AI for Gift Ideas</h5>
            </div>

            <BaseSwitch
                :model-value="store.data.notify ?? false"
                @update:modelValue="value => toggleNotifications(value)"
            />

          </div>

          <p class="mb-0 text-muted">Receive updates about new content and recommendations.</p>

        </div>

        <AIWishProposition
            @copy="handleCopy(recommendation)"
        />

        <!-- Notifications -->
        <div class="card p-4 shadow-sm mb-4">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-3">
              <i class="bi bi-bell me-2 fs-4 text-primary"></i>
              <h5 class="mb-0">These wishes may interest you</h5>
            </div>
          </div>
          <Loader v-if="wishItemRecommendationStore.loading" text="Loading recommendations..." />
          <div v-else>
            <div v-if="wishItemRecommendationStore.items.length===0">
              <p class="mb-0 text-muted"> There is no recommendation yet! </p>
            </div>
            <div v-else>
              <div v-for="recommendation in wishItemRecommendationStore.items" :key="recommendation.id">

                <div class="alert alert-success" role="alert">
                  <div class="d-flex justify-content-between align-items-center">
                    <h5 v-if="recommendation.type==='ai_recommendation'" class="alert-heading mb-0">Your AI-driven recommendation!</h5>
                    <h5 v-if="recommendation.type==='shared_wish'" class="alert-heading mb-0">New Wish Recommendation!</h5>

                    <div class="d-flex align-items-center text-nowrap">
                      <span class="text-muted small me-2"> {{ getFormattedDate(recommendation.createdAt) }} </span>
                      <button
                          type="button"
                          @click="handleNotificationSeen(recommendation)"
                          class="btn btn-link text-success p-0 fs-3 bi bi-check2-circle"
                          data-bs-dismiss="alert"
                          aria-label="Seen"
                      ></button>
                    </div>
                  </div>
                  <span>Title of the wish you may be interested: </span><strong>{{ recommendation.wishItemTitle }}</strong>

                  <div v-if="recommendation.wishItem" class="mt-2">

                    <a
                        class="btn btn-primary"
                        data-bs-toggle="collapse"
                        :href="'#collapseDetails-' + recommendation.id"
                        role="button"
                        aria-expanded="false"
                        :aria-controls="'collapseDetails-' + recommendation.id"
                    >
                      Click for details!
                    </a>

                    <div class="collapse mt-2" :id="'collapseDetails-' + recommendation.id">
                      <WishItemCard
                          :key="recommendation.wishItem['@id']"
                          :item="recommendation.wishItem"
                          :mode="readonly"
                          @copy="handleCopy(recommendation)"
                      />
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>


      </div>
    </div>
  </div>
</template>

<script setup>
import {ref, onMounted, computed, readonly} from 'vue';
import { useProfileStore } from '@js/stores/ProfileStore';
import { useCategoryStore } from '@js/stores/CategoryStore';
import { useWishItemRecommendationStore } from '@js/stores/WishItemRecommendationStore';
import ProfileForm from '@js/components/profile/ProfileForm.vue';
import TagInput from "@js/components/TagInput.vue";
import Tooltip from "@js/components/Tooltip.vue";
import BaseSwitch from "@js/components/BaseSwitch.vue";
import WishItemCard from "@js/components/wishitem/WishItemCard.vue";
import WishItemService from "@js/services/WishItemService";
import Loader from "@js/components/Loader.vue";
import ApiService from "@js/services/ApiService";
import AIWishProposition from "@js/components/profile/AIWishProposition.vue";

const store = useProfileStore();
const categoriesStore = useCategoryStore();
const wishItemRecommendationStore = useWishItemRecommendationStore();
const editMode = ref(false);
const newTag = ref('');
let tagError = ref(null);

function getFormattedDate(date) {
  return (new Date(date)).toLocaleString()
}

// Fetch user profile + categories on mount
onMounted(async () => {
  if (!store.isLoaded){
    await store.fetchMe();
  }
  if (categoriesStore.data.length === 0){
    await categoriesStore.fetch();
  }
  if (wishItemRecommendationStore.items.length === 0){
    await wishItemRecommendationStore.fetch({
      isSeen: false
    });
  }
})

// checkbox categories computed
const handleNotificationSeen = async (recommendation) => {
  await wishItemRecommendationStore.seen(recommendation['@id']);
}

const handleCopy = async (recommendation) => {
  const itemToCopy = recommendation.wishItem;
  if (!itemToCopy) {
    return;
  }
  let clone = Object.assign({}, itemToCopy);

  if (clone.category?.['@id']) {
    clone.category = clone.category['@id']
  }

  if (clone.currency?.['@id']) {
    clone.currency = clone.currency['@id']
  }
  delete(clone['@id']);

  try {
    WishItemService.setErrorMassage('Error occurred during copying, please try again later');
    await WishItemService.post(clone);
  } catch (err) {
    throw err
  }

  await handleNotificationSeen(recommendation);
  window.$toast('Success!', 'The wish was successfully copied', 'success')
}

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

    if(!store.error){
      window.$toast('Success!', 'Tags were updated successfully', 'success')
    }
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

  if(!store.error){
    window.$toast('Success!', 'Profile was updated successfully', 'success')
  }
}

const uploadAvatar = () => {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';

  input.onchange = async (e) => {
    const file = e.target.files[0]
    const formData = new FormData()
    formData.append('file', file)

    const response = await (new ApiService()).upload('/profile/avatar', formData)
    store.data.avatarUrl = response.data.avatarUrl;
    window.$toast('Success!', 'Avatar was updated successfully', 'success')

  }

  input.click();
}


const toggleNotifications = async (value) => {
  store.data.notify = value;
  await store.update({notify: value});
  if(!store.error){
    window.$toast('Success!', 'Notification setting was updated successfully', 'success')
  }
}

const updateCategories = async () => {
  await store.update({categories: store.data.categories});
  if(!store.error){
    window.$toast('Success!', 'Categories were updated successfully', 'success')
  }
}

</script>

<style lang="scss" scoped>
.text-break-all {
  overflow-wrap: break-word; /* Nowoczesny standard */
  word-break: break-all;    /* Agresywne łamanie (dobre dla bardzo długich maili/linków) */
  hyphens: auto;            /* Opcjonalnie dodaje myślniki */
}
</style>
