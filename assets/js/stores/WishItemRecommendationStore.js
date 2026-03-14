import { defineStore } from 'pinia'
import WishItemRecommendationsService from '@js/services/WishItemRecommendationsService'

/**
 * @typedef {Object} WishItemRecommendation
 * @property {null|number} score
 * @property {null|string} wishItemTitle
 * @property {null|boolean} isSeen
 * @property {null|string} createdAt
 * @property {null|object} wishItem
 * @property {null|object} wishSnapshot
 * @property {null|string} type
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

                this.items = response.member.map(item => {
                    // We extract data from a snapshot or the original object
                    const rawWish = item.wishSnapshot;

                    if (rawWish) {
                        // Deep copy to avoid mutating the original from response
                        const wishItem = { ...rawWish };

                        // Category mapping: replace "id" with "@id" for consistency with JSON-LD
                        if (wishItem.category && wishItem.category.id) {
                            wishItem.category = {
                                ...wishItem.category,
                                '@id': wishItem.category.id // Ustawiamy IRI pod kluczem @id
                            };
                        }

                        // Currency mapping: replace "id" with "@id"
                        if (wishItem.currency && wishItem.currency.id) {
                            wishItem.currency = {
                                ...wishItem.currency,
                                '@id': wishItem.currency.id // Ustawiamy IRI pod kluczem @id
                            };
                        }

                        if(item.type==='ai_recommendation'){
                            wishItem.owner = 'WishHelperBot'
                        }

                        return {
                            ...item,
                            wishItem: wishItem
                        }
                    }
                    return item
                })

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