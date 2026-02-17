import { defineStore } from 'pinia'
import CountryService from '../services/CountryService'

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

        /** Mapuje wynik API na {id, label} */
        mapCountries(apiResult) {
            return (Array.isArray(apiResult) ? apiResult : apiResult.data).map(item => ({
                ...item, // zachowuje wszystkie oryginalne pola: continent, currency, itp.
                label: item.name,
                icon: item.flag,
                value: item['@id'].split('/').pop(),
            }))
        },

        /**
         * Szuka kraje po zapytaniu i mapuje na strukturę {id, label}
         * @param {string} query
         */
        async search(query = '') {
            this.loading = true
            this.error = null
            try {
                const res = await CountryService.search(query)
                // API może zwracać {data: [...]}, mapujemy na naszą strukturę
                this.data = this.mapCountries(res);
            } catch (err) {
                this.error = err.message || 'Error fetching countries'
            } finally {
                this.loading = false
            }
        },

        /**
         * Pobiera wszystkie kraje (np. przy ładowaniu selecta)
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
    },
})
