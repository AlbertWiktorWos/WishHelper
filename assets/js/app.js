import 'bootstrap';
import '../scss/main.scss';
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import LandingPage from "@js/pages/LandingPage.vue";
import RegisterPage from "@js/pages/RegisterPage.vue";
import LoginPage from "@js/pages/LoginPage.vue";
import EmailVerifiedPage from "@js/pages/EmailVerifiedPage.vue";
import ResendVerificationEmailPage from "@js/pages/ResendVerificationEmailPage.vue";
import ProfilePage from "@js/pages/ProfilePage.vue";
import WishItemMinePage from "@js/pages/WishItemMinePage.vue";
import WishItemSearchPage from "@js/pages/WishItemSearchPage.vue";
import Toast from "@js/components/Toast.vue";

const pages = {
    landing: LandingPage,
    login: LoginPage,
    register: RegisterPage,
    emailVerified: EmailVerifiedPage,
    resendVerificationEmail: ResendVerificationEmailPage,
    profile: ProfilePage,
    wishItemMine: WishItemMinePage,
    wishItemSearch: WishItemSearchPage,
}

const appElement = document.getElementById('app')
const props = appElement.dataset.props
    ? JSON.parse(appElement.dataset.props)
    : {}

if (appElement) {
    const pageName = appElement.dataset.page

    if (pageName && pages[pageName]) {
        const app = createApp(pages[pageName], props)
        app.use(createPinia());
        app.mount(appElement);
    }

    // mount globalnego alertu
    const alertElement = document.getElementById('global-toast')

    let alertAppInstance = null

    if (alertElement) {
        const alertApp = createApp(Toast)
        alertAppInstance = alertApp.mount(alertElement)
    }

// globalna funkcja
    window.$toast = (title, message, type = 'info', timeout = 3000) => {
        if (alertAppInstance) {
            debugger;
            alertAppInstance.show(title, message, type, timeout)
        }
    }
}