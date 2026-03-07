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

        loadedPage: 1,
        hasMore: false,
    }),
    actions: {

        /** Maps API result to {value, label} */
        mapCurrencies(apiResult) {
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
            this.loading = true
            this.error = null
            try {
                const res = await CurrencyService.search(query)
                // API can return {data: [...]}, we map to our structure
                this.data = this.mapCurrencies(res);
                this.setHasMore(res);
            } catch (err) {
                this.error = err.message || 'Error fetching Currencies'
            } finally {
                this.loading = false
                this.loadedPage = 1;
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
                this.setHasMore(res);
            } catch (err) {
                this.error = err.message || 'Error fetching Currencies'
            } finally {
                this.loadedPage = 1;
                this.loading = false
            }
        },

        /**
         * Fetch more currencies by previous params
         */
        async fetchMore() {
            this.loading = true
            this.error = null
            try {
                this.loadedPage++;
                const res = await CurrencyService.fetch(CurrencyService.lastFetchParams, this.loadedPage)
                this.data = [...this.data, ...this.mapCurrencies(res)]
                this.setHasMore(res);
            } catch (err) {
                this.error = err.message || 'Error fetching countries'
                this.loadedPage--;
                console.log(this.error);
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
                this.loadedPage = 1;
                this.loading = false
            }
            return this.data.length > 0 ? this.data[0] : null; // return the first Currency or null
        },

        setHasMore(res){
            if(!res.view?.next){
                this.hasMore = false
            }else{
                this.hasMore = true
            }
        }
    },
})
