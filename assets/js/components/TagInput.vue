<template>
    <div class="d-flex flex-wrap gap-2 mb-2">

      <span
          v-for="(tag, index) in internalTags"
          :key="tag.id ?? tag.name + index"
          class="badge bg-primary d-flex align-items-center"
      >
        {{ tag.name }}

        <button
            type="button"
            class="btn btn-sm btn-link text-white ms-2 p-0"
            @click="removeTag(index)"
        >
          <i class="bi bi-x-lg"></i>
        </button>
      </span>

    </div>

    <div class="d-flex gap-2">
      <input
          type="text"
          class="form-control"
          v-model="newTag"
          @keydown.enter.prevent="addTag"
          placeholder="Add new tag"
      />
      <button type="button" class="btn btn-primary" @click="addTag">
        Add
      </button>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

const internalTags = ref([...props.modelValue])
const newTag = ref('')

watch(
    () => props.modelValue,
    (val) => {
      internalTags.value = [...(val ?? [])]
    },
    { deep: true }
)

const addTag = () => {
  const value = newTag.value.trim()
  if (!value) {
    return;
  }
  for(let i = 0; i < internalTags.value.length; i++){
    if (internalTags.value[i].name.toLowerCase() === value.toLowerCase()){
      return;
    }
  }

  internalTags.value.push({ name: value })
  emit('update:modelValue', internalTags.value)

  newTag.value = ''
}

const removeTag = (index) => {
  internalTags.value.splice(index, 1)
  emit('update:modelValue', internalTags.value)
}
</script>

<style lang="scss" scoped>
@import '~styles/variables';

.bi {
  color: $wh-sage-100 !important;
}
.bi::before {
  font-weight: bold  !important;
}
</style>
