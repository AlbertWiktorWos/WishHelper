// assets/js/services/ApiService.js
import axios from 'axios'

class ApiService {
    constructor(baseURL = '/api') {
        this.client = axios.create({
            baseURL,
            headers: { 'Content-Type': 'application/json' }
        })
    }

    async get(url, config = {}) {
        try {
            return await this.client.get(url, config)
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

    async search(query = '') {
        const params = query ? { search: query } : {}
        const response = await this.get('/', { params }) // używamy this.get
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
