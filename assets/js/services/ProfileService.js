import ApiService from './ApiService'

class ProfileService extends ApiService {
    constructor() {
        super('/api/users')
    }

    async me() {
        const response = await this.get('/me')
        return response.data
    }

    async update(data) {
        const response = await this.patch('/me', data)
        return response.data
    }
}

export default new ProfileService()
