import 'bootstrap';
import '../scss/main.scss';
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import LandingPage from "@js/pages/LandingPage.vue";

const app = createApp(LandingPage)
app.use(createPinia())
app.mount('#app')
