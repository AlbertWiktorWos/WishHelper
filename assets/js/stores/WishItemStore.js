import { defineStore } from 'pinia'
import WishItemService from "@js/services/WishItemService";

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
        data: [],
        loading: false,
        saving: false,
        error: null,
    }),

    actions: {

        upsertItem(item) {
            const index = this.data.findIndex(i => i['@id'] === item['@id'])

            if (index === -1) {
                this.data.push(item)
            } else {
                this.data[index] = item
            }
        },

        removeById(id) {
            const index = this.data.findIndex(i => i['@id'] === id)
            if (index !== -1) {
                this.data.splice(index, 1)
            }
        },

        async fetch(params = {}) {
            this.loading = true
            this.error = null

            try {
                const items = await WishItemService.fetch(params)
                this.data = items
                return items
            } catch (err) {
                this.error = err.message || 'Error fetching wishes'
                throw err
            } finally {
                this.loading = false
            }
        },

        async add(payload, config = {}) {
            this.saving = true
            this.error = null
            debugger;
            try {
                const created = await WishItemService.post(payload, config)
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
