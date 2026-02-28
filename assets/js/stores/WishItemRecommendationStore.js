import { defineStore } from 'pinia'
import WishItemRecommendationsService from '@js/services/WishItemRecommendationsService'

/**
 * @typedef {Object} WishItemRecommendation
 * @property {null|number} score
 * @property {null|string} wishItemTitle
 * @property {null|boolean} isSeen
 * @property {null|string} createdAt
 * @property {null|string} wishItem
 */
export const useWishItemRecommendationStore = defineStore('wishItemRecommendation', {
    state: () => ({
        /** @type {WishItemRecommendation[]} */
        items: [],
        loading: false,
        saving: false,
        error: null,

        pagination: {
            page: 1,
            perPage: 10,
            totalItems: 0
        },

        lastQuery: {
            filters: {},
            page: 1,
            perPage: 10
        }
    }),

    getters: {
        totalPages: (state) => {
            return Math.max(
                1,
                Math.ceil(state.pagination.totalItems / state.pagination.perPage)
            )
        }
    },

    actions: {

        removeById(id) {
            const index = this.items.findIndex(i => i['@id'] === id)
            if (index !== -1) {
                this.items.splice(index, 1)
            }
        },

        async fetch(filters = {}, page = 1, perPage = 5) {
            this.loading = true
            this.error = null

            try {
                const response = await WishItemRecommendationsService.fetch(filters, page, perPage)
                debugger;
                this.items = response.member
                this.pagination.totalItems = response.totalItems
                this.pagination.page = page
                this.pagination.perPage = perPage
            } catch (err) {
                this.error = err.response?.data || err.message
                throw err
            } finally {
                this.loading = false
            }
        },

        async seen(url) {
            this.saving = true
            this.error = null
            debugger;
            try {
                await WishItemRecommendationsService.patch(url, {isSeen: true})
                this.removeById(url)
            } catch (err) {
                this.error = err.response?.data || err.message
                throw err
            } finally {
                this.saving = false
            }
        }
    }
})