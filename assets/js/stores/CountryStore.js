import { defineStore } from 'pinia'
import CountryService from '@js/services/CountryService'

/**
 * @typedef {Object} Country
 * @property {number|string} id
 * @property {string} label
 */

export const useCountryStore = defineStore('country', {
    state: () => ({
        /** @type {Country[]} */
        data: [],
        loading: false,
        error: null,
    }),
    actions: {

        /** Maps API result to {.., id, label} */
        mapCountries(apiResult) {
            let result= [];
            if(Array.isArray(apiResult)){
                result = apiResult;
            }else{
                result = apiResult.member || apiResult.data;
                if(!Array.isArray(result)){
                    result = [result];
                }
            }

            return result.map(item => ({
                ...item,// preserves all original fields: continent, currency, etc.
                label: item.name,
                id: item['@id'],
                iconUrl: item.flag,
                value: item['@id'].split('/').pop(),
            }))
        },

        /**
         * Searches for countries by query and maps to the {id, label} structure
         * @param {string} query
         */
        async search(query = '') {
            this.loading = true
            this.error = null
            try {
                if(query.trim() === '') {
                    // If query is empty, get all countries
                    const res = await CountryService.fetch();
                    this.data = this.mapCountries(res);
                    return;
                }

                const res = await CountryService.search(query);
                // API can return {data: [...]}, we map to our structure
                this.data = this.mapCountries(res);
            } catch (err) {
                this.error = err.message || 'Error fetching countries'
            } finally {
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
                const res = await CountryService.fetch(params)
                this.data = this.mapCountries(res);
            } catch (err) {
                this.error = err.message || 'Error fetching countries'
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
                const res = await CountryService.find(url)
                debugger;
                this.data = this.mapCountries(res);
                debugger;
            } catch (err) {
                this.error = err.message || 'Error fetching countries'
            } finally {
                this.loading = false
            }
            return this.data.length > 0 ? this.data[0] : null; // return the first country or null
        },
    },
})
