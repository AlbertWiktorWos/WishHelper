import ApiService from './ApiService'

class WishItemService extends ApiService {
    constructor() {
        super('/api/wish_items')
    }
}

export default new WishItemService()
