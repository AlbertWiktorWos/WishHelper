import ApiService from './ApiService'

class CategoryService extends ApiService {
    constructor() {
        super('/api/categories') // set baseURL for this service
    }
}

export default new CategoryService()
