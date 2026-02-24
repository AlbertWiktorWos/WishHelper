<template>
  <div>
    <form @submit.prevent="submit">

      <div class="row">
        <div class="col">
          <label class="form-label">Title</label>
          <input
              v-model="form.title"
              class="form-control"
              :class="{ 'is-invalid': v$.title.$error }"
          />
          <div class="invalid-feedback" v-for="err in v$.title.$errors" :key="err.$uid">
            {{ err.$message }}
          </div>
        </div>
        <div class="col">
          <SearchComponent
              type="category"
              v-model="form.category"
              label="Category"
              id="category"
          />
          <div v-if="v$.category.$error" class="invalid-feedback d-block">
            <div v-for="err in v$.category.$errors" :key="err.$uid">
              {{ err.$message }}
            </div>
          </div>
        </div>

      </div>

      <div class="mb-3">
        <label>Description</label>
        <textarea v-model="form.description" class="form-control" />
      </div>

      <div class="row">
        <div class="col">
          <label>Price</label>
          <input
              type="number"
              min="0.00"
              step="0.01"
              v-model="form.price"
              class="form-control"
              :class="{ 'is-invalid': v$.price.$error }"
          />
          <div class="invalid-feedback" v-for="err in v$.price.$errors" :key="err.$uid">
            {{ err.$message }}
          </div>

        </div>
        <div class="col">
          <SearchComponent
              type="currency"
              v-model="form.currency"
              label="Currency"
              id="currency"
          />
          <div v-if="v$.currency.$error" class="invalid-feedback d-block">
            <div v-for="err in v$.currency.$errors" :key="err.$uid">
              {{ err.$message }}
            </div>
          </div>

        </div>

      </div>

      <div class="mb-3 mt-3">
        <label>Link</label>
        <input
            v-model="form.link"
            class="form-control"
            :class="{ 'is-invalid': v$.link.$error }"
        />
        <div class="invalid-feedback" v-for="err in v$.link.$errors" :key="err.$uid">
          {{ err.$message }}
        </div>
      </div>

      <div class="mb-3 mt-3">
        <TagInput
            v-model="form.tags"
        />
        <div v-if="v$.tags.$error" class="invalid-feedback d-block">
          <div v-for="err in v$.tags.$errors" :key="err.$uid">
            {{ err.$message }}
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary" @click="$emit('cancel')">
          Cancel
        </button>
        <button class="btn btn-accent" :disabled="saving">
          {{ saving ? 'Saving...' : 'Save' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import {computed, reactive, watch} from 'vue'
import TagInput from "@js/components/TagInput.vue";
import SearchComponent from "@js/components/search/SearchComponent.vue";
import useVuelidate from '@vuelidate/core'
import {helpers, maxLength, minLength, required, url} from "@vuelidate/validators";

const props = defineProps({
  item: Object,
  saving: Boolean
})

const emit = defineEmits(['save', 'cancel'])

const form = reactive({
  title: '',
  description: '',
  price: null,
  currency: null,
  category: '',
  link: ''
})

watch(
    () => props.item,
    (val) => {
      if (val) {
        Object.assign(form, val)
      }
    },
    { immediate: true }
)


// computed(rules) allows to react to the dynamic currency <-> >price relationship
const rules = computed(() => ({
  title: {
    required: helpers.withMessage('Title is required', required),
    minLength: helpers.withMessage('Title must have at least 3 characters', minLength(3)),
    maxLength: helpers.withMessage('Title must have 100 characters max', maxLength(100)),
  },
  description: {
    maxLength: helpers.withMessage('Title must have 1000 characters max', maxLength(1000)),
  },

  category: {
    required: helpers.withMessage('Category is required', required),
  },

  price: {
    minValue: helpers.withMessage(
        'Price must be greater or equal to 0', value => {
          if(!value){
            return true;
          }
          value = parseFloat(value);
          return value >= 0;
        }
    ),
  },

  currency: {
    required: helpers.withMessage(
        'Currency is required when price is set',
        value => {
          if (!form.price){
            return true;
          }
          return !!value;
        }
    ),
  },

  link: {
    url: helpers.withMessage(
        'Link must be a valid URL',
        url
    ),
  },

  tags: {
    duplicated: helpers.withMessage(
        'Duplicated tags are not allowed',
        value => {
          if (!value || value.length === 0) {
            return true;
        }
          for(let i = 0; i < value.length; i++){
            for(let j = i + 1; j < value.length; j++){
              if (value[i].name.toLowerCase() === value[j].name.toLowerCase()){
                return false;
              }
            }
          }

          return true;
        }
    ),
  }
}))

const v$ = useVuelidate(rules, form)
v$.value.$validate(); // validate on load to show errors for pre-filled form (edit mode)

const submit = async () => {
  const isValid = await v$.value.$validate()

  if (!isValid) {
    return
  }

  emit('save', {...form})
}
</script>
