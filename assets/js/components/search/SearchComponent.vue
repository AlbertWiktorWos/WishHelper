<template>
  <div class="position-relative">
    <label for="search">{{ label }}</label>
    <div class="position-relative">
      <input
          id="search"
          type="text"
          class="form-control pe-5"
          :placeholder="placeholder"
          v-model="query"
          @input="onInput"
          @focus="showDropdown = true"
          @blur="onBlur"
          autocomplete="off"
      />

      <!-- clear button -->
      <!-- we set @mousedown.prevent so the blur does not close the dropdown before clicking-->
      <button
          v-if="modelValue"
          type="button"
          class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 p-0 border-0 bg-transparent"
          @mousedown.prevent="onClearSelection"
          tabindex="-1"
      >
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <!--  input responsible for sending data  -->
    <input type="hidden" :name="filedName" :value="props.modelValue" />

    <!-- dropdown -->
    <ul v-if="showDropdown" class="list-group position-absolute w-100" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
      <li v-if="loading" class="list-group-item text-center text-muted">Loading...</li>
      <li v-else-if="!results || results.length === 0" class="list-group-item text-center text-muted">No results</li>
      <li
          v-for="item in results"
          :key="item.id"
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
import {useCurrencyStore} from "@js/stores/CurrencyStore";
import {useCategoryStore} from "@js/stores/CategoryStore";

const props = defineProps({
  modelValue: {type: [Object, String], default: ''}, // the entire value of v-model
  type: {type: String, required: true}, // 'country', 'currency', 'category'
  label: {type: String, default: ''}, // field label
  placeholder: {type: String, default: 'Search...'},
  filedName: {type: String, default: 'search-input'} // filedName for input and hidden field
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
  case 'currency':
    store = useCurrencyStore();
    break
  case 'category':
    store = useCategoryStore();
    break
  default:
    throw new Error('Unsupported type in SearchComponent');
}

const debounceTimeout = ref(null)

/**
 * we clear query and
 */
async function onClearSelection(){
  query.value = '';
  emit('update:modelValue', null);   // we pass null as value
  loading.value = true;
  results.value = [];
  showDropdown.value = true;
  try {
    await store.fetch();
    results.value = store.data;
  } finally {
    loading.value = false;
  }
}
/**
 * It retrieves data from the API when the component is mounted. If the data is already in the store, it uses it immediately.
 */
onMounted(async () => {
  debugger;
  if(props.modelValue){
    let resultVal = null;
    if(typeof props.modelValue === 'string'){
      resultVal = await store.find(props.modelValue);
    }else{
      resultVal = await store.find(props.modelValue['@id']);
    }
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
  // we delete the previous timeout
  debugger;
  if (debounceTimeout.value){
    clearTimeout(debounceTimeout.value);
  }

  // we set a new timeout
  debounceTimeout.value = setTimeout(async () => {
    loading.value = true;
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

/**
 * On select item we need to push info about changing form
 * @param item
 */
const selectItem = (item) => {
  emit('update:modelValue', item['@id']);   // we pass only id value
  query.value = item.label; // input shows label for user
  showDropdown.value = false;
}

const onBlur = () => {
  setTimeout(() => showDropdown.value = false, 150) // allows to click on a dropdown item
}

/**
 * if v-model is set from outside, show label in input
 * we allows to pass object or id
 */
watch(() => props.modelValue, (val) => {
  debugger;
  if(store.data.length < 1 || val === null){
    return;
  }
  let selected;
  if(typeof val === 'string'){
    selected = store.data.find(item => (item['@id'] === val))
  }else{
    selected = store.data.find(item => (item['@id'] === val['@id']))
  }
  query.value = selected ? selected.label : ''
})

// we expose query value that parent component can catch that by ref
defineExpose({
  query
})

</script>

<style scoped>
.list-group-item-action:hover {
  cursor: pointer;
}
</style>
