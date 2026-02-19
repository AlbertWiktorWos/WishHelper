// assets/js/services/ApiService.js
import axios from 'axios'

class ApiService {
    constructor(baseURL = '/api') {
        this.client = axios.create({
            baseURL,
            headers: { 'Content-Type': 'application/json' }
        })
    }

    async get(id, config = {}) {
        try {
            debugger;
            return await this.client.get(id, config)
        } catch (err) {
            console.error(err)
            throw err
        }
    }
    async find(url, config = {}) {
        try {
            // if url starts with baseURL, remove it
            const cleanUrl = url.startsWith(this.client.defaults.baseURL)
                ? url.slice(this.client.defaults.baseURL.length)
                : url

            return await this.client.get(cleanUrl, config);
        } catch (err) {
            console.error(err)
            throw err
        }
    }

    async post(url, data, config = {}) {
        try {
            return await this.client.post(url, data, config)
        } catch (err) {
            console.error(err)
            throw err
        }
    }

    async patch(url, data, config = {}) {
        config = { ...config, headers: { 'Content-Type': 'application/merge-patch+json', ...config.headers } }

        try {
            return await this.client.patch(url, data, config)
        } catch (err) {
            console.error(err)
            throw err
        }
    }

    async search(query = '') {
        const params = query ? { name: query } : {}
        const response = await this.get('/', { params })
        return response.data.member;
    }

    async fetch(params = {}, page = 1, itemsPerPage = 10) {
        const requestParams = { ...params, page, itemsPerPage }
        const response = await this.get('/', { params: requestParams })
        return response.data.member;
    }

    async fetchAll(params = {}) {
        const response = await this.get('/', { params })
        return response.data.member;
    }

}

export default ApiService
