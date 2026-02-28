import ApiService from './ApiService'

class WishItemRecommendationsService extends ApiService {
    constructor() {
        super('/api/wish_item_recommendations')
    }
}

export default new WishItemRecommendationsService()
