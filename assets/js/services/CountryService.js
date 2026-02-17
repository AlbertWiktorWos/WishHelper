// assets/js/services/CountryService.js
import ApiService from './ApiService'

class CountryService extends ApiService {
    constructor() {
        super('/api/countries') // set baseURL for this service
    }
}

export default new CountryService()
