<template>
  <div class="position-relative">
    <label for="search">{{ label }}</label>
    <input
        id="search"
        type="text"
        class="form-control"
        :placeholder="placeholder"
        v-model="query"
        @input="onInput"
        @emptied="onInput"
        @focus="showDropdown = true"
        @blur="onBlur"
        autocomplete="off"
    />
    <!--  input responsible for sending data  -->
    <input type="hidden" :name="id" :value="props.modelValue" />

    <!-- dropdown -->
    <ul v-if="showDropdown" class="list-group position-absolute w-100" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
      <li v-if="loading" class="list-group-item text-center text-muted">Ładowanie...</li>
      <li v-else-if="!results || results.length === 0" class="list-group-item text-center text-muted">Brak wyników</li>
      <li
          v-for="item in results"
          :key="item.value"
          class="list-group-item list-group-item-action d-flex align-items-center"
          @mousedown.prevent="selectItem(item)"
      >
        <img v-if="item.iconUrl" :src="item.iconUrl" alt="" class="me-2" style="width: 20px; height: 14px; object-fit: cover;">
        <i v-if="item.icon" :class="['bi', item.icon]"></i>
        <span>{{ item.label }}</span>
      </li>
    </ul>
  </div>
</template>

<script setup>
import {onMounted, ref, watch} from 'vue'
import {useCountryStore} from '@js/stores/CountryStore.js'

const props = defineProps({
  modelValue: Object, // the entire value of v-model
  type: {type: String, required: true}, // 'country', 'currency', 'category'
  label: {type: String, default: ''}, // field label
  placeholder: {type: String, default: 'Search...'},
  id: {type: String, default: 'search-input'} // id for input and hidden field
})

const emit = defineEmits(['update:modelValue']);

const query = ref('');
const results = ref([]);
const showDropdown = ref(false);
const loading = ref(false);

// store to API depending on type
let store
switch (props.type) {
  case 'country':
    store = useCountryStore();
    break
  default:
    throw new Error('Nieobsługiwany typ w SearchComponent');
}

const debounceTimeout = ref(null)

/**
 * It retrieves data from the API when the component is mounted. If the data is already in the store, it uses it immediately.
 */
onMounted(async () => {
  debugger;
  if(props.modelValue && props.modelValue['@id']){
    const resultVal = await store.find(props.modelValue['@id']);
    debugger;
    if(resultVal){
      query.value = resultVal.label;
      results.value = [resultVal];
      return;
    }
  }

  await store.fetch();
  try {
    if(store.data.length === 0){
      await store.fetch();
    }
    results.value = store.data;
  } finally {
    loading.value = false;
  }
})

const onInput = async () => {
  if (!query.value) {
    results.value = [];
    return;
  }

  // we delete the previous timeout
  if (debounceTimeout.value){
    clearTimeout(debounceTimeout.value);
  }

  // we set a new timeout
  debounceTimeout.value = setTimeout(async () => {
    loading.value = true;
    debugger;
    try {
      if(query.value) {
        await store.search(query.value || '');
      }else{
          await store.fetch(); // if query is empty, get all
      }

      results.value = store.data;
    } finally {
      loading.value = false;
    }
  }, 300) // 300ms debounce
}


const selectItem = (item) => {
  emit('update:modelValue', item.value);   // we pass only id value
  query.value = item.label; // input shows label for user
  showDropdown.value = false;
}

const onBlur = () => {
  setTimeout(() => showDropdown.value = false, 150) // allows to click on a dropdown item
}

// if v-model is set from outside, show label in input
watch(() => props.modelValue, (val) => {
  const selected = store.data.find(d => d.value === val)
  query.value = selected ? selected.label : ''
})
</script>

<style scoped>
.list-group-item-action:hover {
  cursor: pointer;
}
</style>
