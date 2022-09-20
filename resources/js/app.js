require('./bootstrap');
window.Vue = require('vue');

import App from './App.vue';
import VueRouter from 'vue-router';
import VueAxios from 'vue-axios';
import axios from 'axios';
import {routes} from './router';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import VCalendar from 'v-calendar';
// import VueTimepicker from 'vue2-timepicker';
import InputMask from 'vue-input-mask';
import VueTheMask from 'vue-the-mask';
import CommonService from './services/CommonService';
import 'vue2-timepicker/dist/VueTimepicker.css'

Vue.component('input-mask', InputMask)

Vue.use(VueRouter);
Vue.use(VueAxios, axios);
Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(Loading);
Vue.use(VCalendar, { });
Vue.use(VueTheMask);

const router = new VueRouter({
    mode: 'history',
    routes: routes
});

router.beforeEach((to, from, next) => {
    const commonService = new CommonService();

    if(to.matched.some(record => record.meta.requiresAuth)) {
        if (commonService.is_authenticated()) {
            next()
        } else {
            next({
                path: '/login',
                params: { nextUrl: to.fullPath }
            })
        }
    } else {
        if(commonService.is_authenticated()){
            next({
                path: '/',
                params: { nextUrl: to.fullPath }
            })
        }
        else{
            next();
        }
    }
});


const app = new Vue({
    el: '#app',
    router: router,
    render: h => h(App),
});
