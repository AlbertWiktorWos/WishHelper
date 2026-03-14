import { useWishItemRecommendationStore } from "@js/stores/WishItemRecommendationStore";

class MercureService {
    constructor() {
        this.eventSource = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 10;
        this.reconnectDelay = 5000;

        const mercureUrl = window.MERCURE_URL ? window.MERCURE_URL : 'http://localhost:3000/.well-known/mercure'

        // CONFIGURATION: Here we add new topics after comma and what they should do
        this.config = {
            hubUrl: mercureUrl,
            getTopics: (userId) => [
                `user/${userId}/wish-item-recommendations`,
            ]
        };
    }

    /**
     * The main startup function called in app.js
     */
    init(userId, token) {
        if (!userId || !token) return;

        const url = new URL(this.config.hubUrl);
        const topics = this.config.getTopics(userId);

        topics.forEach(topic => url.searchParams.append('topic', topic));
        url.searchParams.append('authorization', token);

        this.eventSource = new EventSource(url);

        this.eventSource.onopen = () => {
            console.log('Mercure connected');
            this.reconnectAttempts = 0;
        };

        this.eventSource.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleMessage(data);
        };

        this.eventSource.onerror = () => {
            console.error('Mercure connection lost');
            this.stop();
            this.reconnect(userId, token);
        };
    }

    /**
     * RESPONSE LOGIC: Here we decide what happens after receiving the data
     */
    handleMessage(data) {
        console.log('New Mercure message:', data);

        // Reaction to AI recommendations
        if (data.type === 'ai_recommendation') {
            window.$toast(
                'New recommendation!',
                `AI has prepared for you new wish recommendation! ${data.title}`,
                'info',
                10000,
                'profile'
            );
            useWishItemRecommendationStore().fetch();
        }
        if (data.type === 'shared_wish') {
            window.$toast(
                'New recommendation!',
                `Some user shared a wish that might interest you! ${data.title}`,
                'info',
                10000,
                'profile'
            );
            useWishItemRecommendationStore().fetch();
        }
    }

    reconnect(userId, token) {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Retry ${this.reconnectAttempts}/${this.maxReconnectAttempts}...`);
            setTimeout(() => this.init(userId, token), this.reconnectDelay);
        }
    }

    stop() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
    }
}

export default new MercureService();