import { defineStore } from 'pinia'
import ProfileService from '../services/ProfileService'

export const useProfileStore = defineStore('profile', {
    state: () => ({
        data: null,
        loading: false,
        saving: false,
        error: null,
    }),

    getters: {
        isLoaded: (state) => !!state.data,
    },

    actions: {

        async fetchMe() {
            this.loading = true
            this.error = null
            try {
                this.data = await ProfileService.me()
            } catch (err) {
                this.error = err.response?.data || err.message
            } finally {
                this.loading = false
            }
        },

        async update(payload) {
            this.saving = true
            this.error = null
            try {
                const updated = await ProfileService.update(payload)
                this.data = updated
                return updated
            } catch (err) {
                this.error = err.response?.data || err.message
                throw err
            } finally {
                this.saving = false
            }
        }
    }
})
