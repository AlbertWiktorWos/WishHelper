<template>
  <div>


    <div class="row">

      <!-- Search -->
      <div class="mb-4">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h5>Search for Wishes</h5>
              <!--  active filters -->
              <span v-for="(filter, index) in displayedFilters" :key="index" class="badge bg-primary me-1">
              {{ filter }}
            </span>
            </div>

            <button class="btn btn-md btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
              Filters
            </button>
          </div>

          <div class="collapse" id="collapseFilters">
            <div class="card-body">

              <div class="row">

                <div class="col">
                  <label>Price From</label>
                  <input
                      type="number"
                      min="0.00"
                      step="0.01"
                      v-model="searchForm.priceFrom"
                      class="form-control"
                  />
                  <div v-if="v$.priceFrom.$error" class="invalid-feedback d-block">
                    <div v-for="err in v$.priceFrom.$errors" :key="err.$uid">
                      {{ err.$message }}
                    </div>
                  </div>
                </div>
                <div class="col">

                  <label>Price To</label>
                  <input
                      type="number"
                      min="0.00"
                      step="0.01"
                      v-model="searchForm.priceTo"
                      class="form-control"
                  />
                  <div v-if="v$.priceTo.$error" class="invalid-feedback d-block">
                    <div v-for="err in v$.priceTo.$errors" :key="err.$uid">
                      {{ err.$message }}
                    </div>
                  </div>
                </div>
                <div class="col">
                  <!-- we add ref to get selected label -->
                  <SearchComponent
                      ref="currencySearch"
                      type="currency"
                      v-model="searchForm.currency"
                      label="Currency"
                      id="currency"
                  />
                </div>
                <div class="col">
                  <!-- we add ref to get selected label -->
                  <SearchComponent
                      ref="categorySearch"
                      type="category"
                      v-model="searchForm.category"
                      label="Category"
                      id="category"
                  />
                </div>
                <div class="mt-2">
                <TagInput
                    v-model="searchForm.tags"
                    placeholder="Add tag to search"
                />
                <div v-if="v$.tags.$error" class="invalid-feedback d-block">
                  <div v-for="err in v$.tags.$errors" :key="err.$uid">
                    {{ err.$message }}
                  </div>
                </div>
              </div>
              </div>
              <div class="d-flex justify-content-end mt-4 ">
                <button class="btn btn-outline-primary me-0" @click="changeFilters">
                  Search
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Loading -->
      <div v-if="wishItemStore.loading" class="text-center py-3">
        <div class="spinner-border"></div>
      </div>

      <div v-else>
        <!-- Wishes list -->
        <div v-if="wishItemStore.items.length === 0" class="text-center py-3">
          <p class="text-muted">You have no wishes yet. Start by adding your first wish!</p>
        </div>

        <div v-else>
          <div
              v-for="item in wishItemStore.items"
              :key="item.id"
              class="col-md-12 mb-3 position-relative"
          >
            <WishItemCard
                :key="item['@id']"
                :item="item"
                :mode="mode"
                @edit="$emit('edit', item)"
                @delete="$emit('delete', item)"
                @copy="$emit('copy', item)"
            />

            <div v-if="mode==='owner'" class="position-absolute top-0 end-0 m-2 text-end">
              <BaseSwitch
                  :model-value="item.shared"
                  @update:modelValue="value => toggleShare(item, value)"
              />
              <span class="text-muted"> {{item.shared ? 'Shared' : 'Private'}} </span>
            </div>
            <div v-if="mode!=='owner' && item.matchPercentage >= 50" class="position-absolute top-0 end-0 m-2 text-end">
              <span class="text-muted"> {{'Match: ' + item.matchPercentage + '%!'}} </span>
              <i class="bi bi-award-fill"></i>
            </div>
            <div>

            </div>
          </div>

        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center align-items-center mt-4">
          <button
              class="btn btn-outline-secondary btn-sm"
              :disabled="currentPage === 1"
              @click="prevPage"
          >
            Previous
          </button>

          <span class="mx-3">
          Page {{ currentPage }} / {{ totalPages }}
        </span>

          <button
              class="btn btn-outline-secondary btn-sm"
              :disabled="currentPage === totalPages"
              @click="nextPage"
          >
            Next
          </button>

        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import {computed, reactive, ref} from 'vue'
import { useWishItemStore } from '@js/stores/WishItemStore'
import WishItemCard from './WishItemCard.vue'
import BaseSwitch from "@js/components/BaseSwitch.vue";
import TagInput from "@js/components/TagInput.vue";
import SearchComponent from "@js/components/search/SearchComponent.vue";
import {helpers, maxLength, minLength, required, url} from "@vuelidate/validators";
import useVuelidate from "@vuelidate/core";

const wishItemStore = useWishItemStore()

const props = defineProps({
  filters: Object,
  mode: {
    type: String,
    default: 'readonly'
  }
})

// search form
const searchForm = reactive({
  priceFrom: null,
  priceTo: null,
  currency: null,
  category: '',
  tags: [],
})

const displayedFilters = ref(['No filters applied..'])
const userFilters = ref({})

// we need refs to get components and their labels
const currencySearch = ref(null)
const categorySearch = ref(null)

const currentPage = computed(() => wishItemStore.pagination.page)
const totalPages = computed(() => wishItemStore.totalPages)

// computed(rules) allows to react to the dynamic currency <-> >price relationship
const rules = computed(() => ({
  priceFrom: {
    minValue: helpers.withMessage(
        'Price from must be greater or equal to 0', value => {
          if(!value){
            return true;
          }
          value = parseFloat(value);
          return value >= 0;
        }
    ),
    maxValueByPriceTo: helpers.withMessage(
        'Price from must be less or equal of Price To', value => {
          if(!value || !searchForm.priceTo){
            return true;
          }

          const valueFrom = parseFloat(value);
          const valueTo = parseFloat(searchForm.priceTo);
          return valueFrom <= valueTo;
        }
    )
  },
  priceTo: {
    minValue: helpers.withMessage(
        'Price from must be greater or equal to 0', value => {
          if(!value){
            return true;
          }
          value = parseFloat(value);
          return value >= 0;
        }
    ),
    minValueByPriceFrom: helpers.withMessage(
        'Price To must be greater or equal of Price From', value => {
          if(!value || !searchForm.priceFrom){
            return true;
          }

          const valueFrom = parseFloat(searchForm.priceFrom);
          const valueTo = parseFloat(value);
          return valueFrom <= valueTo;
        }
    )
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

const v$ = useVuelidate(rules, searchForm)
v$.value.$validate(); // validate on load to show errors for pre-filled form (edit mode)

const changeFilters = async () => {

  const isValid = await v$.value.$validate();
  if (!isValid){
    return;
  }

  const params = {};
  displayedFilters.value = []; // <-- reset tablicy

  if (searchForm.priceFrom !== null && searchForm.priceFrom !== '') {
    params['price[gte]'] = searchForm.priceFrom
    displayedFilters.value.push('Price from: ' + params['price[gte]'])
  }

  if (searchForm.priceTo !== null && searchForm.priceTo !== '') {
    params['price[lte]'] = searchForm.priceTo
    displayedFilters.value.push('Price to: ' + params['price[lte]'])
  }

  if (searchForm.currency) {
    params['currency'] = searchForm.currency
    displayedFilters.value.push(
        'Currency: ' + (currencySearch.value ? currencySearch.value.query : searchForm.currency)
    )
  }
  if (searchForm.category) {
    params['category'] = searchForm.category
    displayedFilters.value.push(
        'Category: ' + (categorySearch.value ? categorySearch.value.query : searchForm.category)
    )
  }

  if (searchForm.tags && searchForm.tags.length > 0) {
    params['tags'] = searchForm.tags.map(tag => tag.name)
    displayedFilters.value.push('Tags: ' + params['tags'].join(', '))
  }

  if (displayedFilters.value.length === 0) {
    displayedFilters.value.push('No filters applied..')
  }

  userFilters.value = params;
  await wishItemStore.fetch({ ...props.filters, ...params });
}

const nextPage = async () => {
  if (currentPage.value < totalPages.value) {
    await wishItemStore.fetch(
        { ...props.filters, ...userFilters.value },
        currentPage.value + 1,
        wishItemStore.pagination.perPage
    )
    scrollToTop()
  }
}

const prevPage = async () => {
  if (currentPage.value > 1) {
    await wishItemStore.fetch(
        { ...props.filters, ...userFilters.value },
        currentPage.value - 1,
        wishItemStore.pagination.perPage
    )
    scrollToTop()
  }
}

// helper
const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth' // smooth scrolling
  })
}

// Actions
const toggleShare = async (item) => {
  await wishItemStore.update(item['@id'], { shared: !item.shared })
}

</script>
