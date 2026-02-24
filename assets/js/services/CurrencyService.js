// assets/js/services/CurrencyService.js
import ApiService from './ApiService'

class CurrencyService extends ApiService {
    constructor() {
        super('/api/currencies') // set baseURL for this service
    }
}

export default new CurrencyService()
