import { defineStore } from 'pinia'
import CurrencyService from "@js/services/CurrencyService";

/**
 * @typedef {Object} Currency
 * @property {number|string} id
 * @property {string} label
 */

export const useCurrencyStore = defineStore('Currency', {
    state: () => ({
        /** @type {Currency[]} */
        data: [],
        loading: false,
        error: null,
    }),
    actions: {

        /** Maps API result to {value, label} */
        mapCurrencies(apiResult) {
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
            }))
        },

        /**
         * Looks for a Currency by query and maps it to a {value, label} structure
         * @param {string} query
         */
        async search(query = '') {
            debugger;
            this.loading = true
            this.error = null
            try {
                const res = await CurrencyService.search(query)
                // API can return {data: [...]}, we map to our structure
                this.data = this.mapCurrencies(res);
            } catch (err) {
                this.error = err.message || 'Error fetching Currencies'
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
                const res = await CurrencyService.fetch(params)
                this.data = this.mapCurrencies(res);
            } catch (err) {
                this.error = err.message || 'Error fetching Currencies'
            } finally {
                this.loading = false
            }
        },

        /**
         * Gets currency from url
         * @param {string} url
         */
        async find(url) {
            this.loading = true
            this.error = null
            try {
                const res = await CurrencyService.find(url)
                this.data = this.mapCurrencies(res);
            } catch (err) {
                this.error = err.message || 'Error fetching Currencies'
            } finally {
                this.loading = false
            }
            return this.data.length > 0 ? this.data[0] : null; // return the first Currency or null
        },
    },
})
