import { defineStore } from 'pinia'
import CategoryService from "@js/services/CategoryService";

/**
 * @typedef {Object} Category
 * @property {number|string} id
 * @property {string} label
 */

export const useCategoryStore = defineStore('category', {
    state: () => ({
        /** @type {Category[]} */
        data: [],
        loading: false,
        error: null,
    }),
    actions: {

        /** Maps API result to {value, label} */
        mapCategories(apiResult) {
            debugger;
            let result= [];
            if(Array.isArray(apiResult)){
                result = apiResult;
            }else{
                result = apiResult.data;
                if(!Array.isArray(result)){
                    result = [result];
                }
            }

            return result.map(item => ({
                ...item, // retains all original fields: continent, currency, etc.
                label: item.name,
                id: item['@id'],
                value: item['@id'].split('/').pop(),
                icon: item.icon,
            }))
        },

        /**
         * Looks for a category by query and maps it to a {value, label} structure
         * @param {string} query
         */
        async search(query = '') {
            debugger;
            this.loading = true
            this.error = null
            try {
                const res = await CategoryService.search(query)
                // API can return {data: [...]}, we map to our structure
                this.data = this.mapCategories(res);
                debugger;
            } catch (err) {
                debugger;
                this.error = err.message || 'Error fetching Categories'
            } finally {
                debugger;
                this.loading = false
            }
        },

        /**
         * Downloads all countries (e.g. when loading select)
         * @param {object} params
         */
        async fetch(params = {}) {
            this.loading = true
            this.error = null
            try {
                const res = await CategoryService.fetch(params)
                this.data = this.mapCategories(res);
            } catch (err) {
                this.error = err.message || 'Error fetching Categories'
            } finally {
                this.loading = false
            }
        },

        /**
         * Gets country from url
         * @param {string} url
         */
        async find(url) {
            this.loading = true
            this.error = null
            try {
                const res = await CategoryService.find(url)
                this.data = this.mapCategories(res);
            } catch (err) {
                this.error = err.message || 'Error fetching Categories'
            } finally {
                this.loading = false
            }
            return this.data.length > 0 ? this.data[0] : null; // return the first category or null
        },
    },
})
