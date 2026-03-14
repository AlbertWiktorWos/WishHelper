<template>
  <div :class="['card h-100', 'w-100', 'mb-3', item.matchPercentage > 50 ? 'border-accent' : '']">
    <div class="card-body d-flex flex-column position-relative">

      <!-- TITLE WITH OWNER -->
      <div v-if="item.owner==='WishHelperBot'" class="d-flex align-items-center mb-3">
        <img
            :src="require('@images/avatar_ai.png')"
            :alt="WishHelperBot"
            class="rounded-circle border me-2"
            style="width: 32px; height: 32px; object-fit: cover;"
        >
        <span class="small text-muted fw-bold">@WishHelperBot</span>
      </div>
      <div v-else class="d-flex align-items-center mb-3">
        <img
            :src="userAvatarUrl ?? require('@images/avatar.png')"
            :alt="item.owner.nickName"
            class="rounded-circle border me-2"
            style="width: 32px; height: 32px; object-fit: cover;"
        >
        <span class="small text-muted fw-bold">@{{ item.owner.nickName }}</span>
      </div>

      <h5 class="card-title mb-3">{{ item.title || '-' }}</h5>

      <!-- PRICE SECTION -->
      <div class="mb-2">
        <strong>Price:</strong>
        <span>{{ item.price ?? '-' }} {{ item.currency ? item.currency.code : '' }}</span>
        <span class="ms-2 text-muted">{{ item.priceInfoInUserCurrency ?? '' }}</span>
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
      <div class="d-grid d-md-flex align-items-md-center justify-content-md-between mt-3 gap-3">

        <div class="d-flex align-items-center flex-wrap gap-2 flex-grow-1" style="min-width: 0;">

          <div v-if="item.category" class="d-flex align-items-center flex-shrink-0 me-2">
            <i v-if="item.category.icon" :class="['bi', item.category.icon]"></i>
            <span class="ms-1 text-nowrap fw-medium small">{{ item.category.name }}</span>
          </div>

          <div class="d-flex flex-nowrap gap-1 overflow-hidden">
            <span
                v-for="(tag, index) in item.tags"
                :key="tag.id ?? tag.name + index"
                class="badge bg-primary text-truncate"
                style="max-width: 100px; font-size: 0.75rem;"
            >
              {{ tag.name }}
            </span>
          </div>
        </div>

        <div class="flex-shrink-0 ms-md-auto">
          <div v-if="mode === 'owner'" class="d-flex flex-nowrap">
            <button class="btn btn-outline-primary btn me-2" @click="$emit('edit')">
              Edit
            </button>
            <button class="btn btn-outline-danger btn" @click="$emit('delete')">
              Delete
            </button>
          </div>
          <div v-else class="d-flex flex-nowrap">
            <button class="btn btn-outline-primary btn px-3" @click="$emit('copy')">
              Copy
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import {computed, ref} from 'vue'

const props = defineProps({
  mode: 'owner' | 'readonly',
  item: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['edit', 'delete'])

const copied = ref(false)

const userAvatarUrl = computed(() => {
  const avatarFile = props.item.owner?.avatar;

  if (avatarFile) {
    // We only have the file name in the API so we add the URL
    return `${window.location.origin}/uploads/avatars/${avatarFile}`;
  }

  return null;
})

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

/* provides some space in the upper right corner for the switch */
.card-title {
  padding-right: 6rem;
}
.card-body {
  padding-top: 1rem;
}
</style>
