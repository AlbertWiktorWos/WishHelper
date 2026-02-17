import 'bootstrap';
import '../scss/main.scss';
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import LandingPage from "@js/pages/LandingPage.vue";
import RegisterPage from "@js/pages/RegisterPage.vue";
import LoginPage from "@js/pages/LoginPage.vue";

const pages = {
    landing: LandingPage,
    login: LoginPage,
    register: RegisterPage,
}

const appElement = document.getElementById('app')
const props = appElement.dataset.props
    ? JSON.parse(appElement.dataset.props)
    : {}

if (appElement) {
    const pageName = appElement.dataset.page

    if (pageName && pages[pageName]) {
        const app = createApp(pages[pageName], props)
        app.use(createPinia())
        app.mount(appElement)
    }
}