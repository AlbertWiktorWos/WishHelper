import { defineStore } from 'pinia'
import WishItemService from '@js/services/WishItemService'

/**
 * @typedef {Object} WishItem
 * @property {number|string} id
 * @property {null|string} title
 * @property {null|string} description
 * @property {null|string} price
 * @property {null|string} link
 * @property {null|boolean} shared
 * @property {[]} category
 */
export const useWishItemStore = defineStore('wishItem', {
    state: () => ({
        /** @type {WishItem[]} */
        items: [],
        loading: false,
        saving: false,
        error: null,

        pagination: {
            page: 1,
            perPage: 5,
            totalItems: 0
        },

        lastQuery: {
            filters: {},
            page: 1,
            perPage: 5
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
        upsertItem(item) {
            const index = this.items.findIndex(i => i['@id'] === item['@id'])

            if (index === -1) {
                this.items.push(item)
            } else {
                this.items[index] = item
            }
        },

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
                const response = await WishItemService.fetch(filters, page, perPage)
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

        async add(payload) {
            this.saving = true
            this.error = null

            try {
                const created = await WishItemService.post(payload)
                this.upsertItem(created.data)
                debugger;
                return created
            } catch (err) {
                this.error = err.response?.data || err.message
                throw err
            } finally {
                this.saving = false
            }
        },

        async update(url, payload, config = {}) {
            debugger;
            this.saving = true
            this.error = null

            try {
                const updated = await WishItemService.patch(url, payload, config)
                debugger;
                this.upsertItem(updated.data)
                return updated
            } catch (err) {
                this.error = err.response?.data || err.message
                throw err
            } finally {
                this.saving = false
            }
        },

        async remove(id, config = {}) {
            this.saving = true
            this.error = null
            debugger;
            try {
                await WishItemService.delete(id, config)
                this.removeById(id)
            } catch (err) {
                this.error = err.response?.data || err.message
                throw err
            } finally {
                this.saving = false
            }
        }
    }
})