import axios from 'axios'

class ApiService {

    //global variable to save last request params
    lastFetchParams

    constructor(baseURL = '/api') {

        this.logoutTimer = null
        this.isLoggingOut = false
        this.defautErrorMassage = false

        this.client = axios.create({
            baseURL,
        })

        // by interceptors we can config requests (or responses) before they are handled
        this.client.interceptors.request.use(
            config => {
                if(this.isLoggingOut){
                    return false;
                }

                // we add X-CSRF-TOKEN for potentially unsafe requests
                const token = window.CSRF_TOKEN
                if (token && !['get', 'head', 'options'].includes(config.method)) {
                    config.headers['X-CSRF-TOKEN'] = token
                }
                // if it's not by form we set 'application/json'
                if (!(config.data instanceof FormData) && config.method !== 'patch') {
                    config.headers['Content-Type'] = 'application/json'
                }

                return config
            });

        this.client.interceptors.response.use(
            response => response,
            async error => {
                if (error.response) {
                    const status = error.response?.status

                    if (status === 429) {
                        window.$toast(
                            'Rate limit exceeded',
                            'Too many requests. Please try again later.',
                            'alert'
                        )
                    }else if (status === 401 && !this.isLoggingOut) {

                        this.isLoggingOut = true

                        window.$toast(
                            'Session expired',
                            'You will be logged out in 5 seconds...',
                            'alert'
                        )

                        this.logoutTimer = setTimeout(async () => {
                            try {
                                await this.client.post('/logout')
                            } catch (_) {
                                // we ignore it – the session doesn't work anyway
                            }

                            window.location.href = '/login'
                        }, 5000)
                    }else if(status >= 400){
                        window.$toast('Error!', this.defautErrorMassage ? this.defautErrorMassage : 'Something goes wrong, please try again later', 'error')
                    }

                } else if (error.request) {
                    // brak odpowiedzi z serwera (network error)
                    window.$toast(
                        'Network error',
                        'Server is unreachable.',
                        'error'
                    )
                }

                return Promise.reject(error)
            }
        )
    }

    async get(id, config = {}) {
        try {
            this.setErrorMassage('Getting information failed, please try again later');
            return await this.client.get(id, config)
        } catch (err) {
            console.error(err)
            throw err
        }
    }
    async find(url, config = {}) {
        try {
            this.setErrorMassage('Element was not founded, please try again later');
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

    async post(data, config = {}) {
        try {
            this.setErrorMassage('Adding new element failed, please try again later');
            return await this.client.post('', data, config)
        } catch (err) {
            console.error(err)
            throw err
        }
    }

    async patch(url, data, config = {}) {
        this.setErrorMassage('Updating failed, please try again later');
        config = { ...config, headers: { 'Content-Type': 'application/merge-patch+json', ...config.headers } }
        // if url starts with baseURL, remove it
        const cleanUrl = url.startsWith(this.client.defaults.baseURL)
            ? url.slice(this.client.defaults.baseURL.length)
            : url

        try {
            return await this.client.patch(cleanUrl, data, config)
        } catch (err) {
            console.error(err)
            throw err
        }
    }

    async delete(url, data, config = {}) {
        // if url starts with baseURL, remove it
        this.setErrorMassage('Removing failed, please try again later');
        const cleanUrl = url.startsWith(this.client.defaults.baseURL)
            ? url.slice(this.client.defaults.baseURL.length)
            : url

        try {
            return await this.client.delete(cleanUrl, config)
        } catch (err) {
            console.error(err)
            throw err
        }
    }

    async search(query = '') {
        const params = query ? { name: query } : {}
        this.lastFetchParams = params;
        const response = await this.get('/', { params })
        return response.data;
    }

    async fetch(params = {}, page = 1, itemsPerPage = 10) {
        const requestParams = { ...params, page, itemsPerPage }
        this.lastFetchParams = requestParams;
        const response = await this.get('/', { params: requestParams })
        return response.data;
    }

    async fetchAll(params = {}) {
        this.lastFetchParams = params;
        const response = await this.get('/', { params })
        return response.data;
    }

    async upload(url, formData, config = {}) {
        this.setErrorMassage('An error occurred while uploading the file.');
        const cleanUrl = url.startsWith(this.client.defaults.baseURL)
            ? url.slice(this.client.defaults.baseURL.length)
            : url

        try {
            return await this.client.post(cleanUrl, formData, config)
        }catch (err){
            console.error(err)
            throw err
        }
    }

    async logout() {
        return await this.client.post('/logout')
    }

    setErrorMassage(errorMassage){
        this.defautErrorMassage = errorMassage;
    }
}

export default ApiService
