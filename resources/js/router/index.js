import Login from '../components/Login.vue';
import Logout from '../components/Logout.vue';
import ForgotPassword from '../components/ForgotPassword.vue';
import TempPassword from '../components/TempPassword.vue';
import NewPassword from '../components/NewPassword.vue';
import AllCars from '../components/AllCars.vue';
import Bids from '../components/Bids.vue';
import Schedulings from '../components/Schedulings.vue';
import Payments from '../components/Payments.vue';

export const routes = [
    {
        name: 'login',
        path: '/login',
        component: Login,
        meta: {
            requiresAuth: false
        }
    },
    {
        name: 'forgotPassword',
        path: '/forgot-password',
        component: ForgotPassword,
        meta: {
            requiresAuth: false
        }
    },
    {
        name: 'tempPassword',
        path: '/temp-password',
        component: TempPassword,
        meta: {
            requiresAuth: false
        }
    },
    {
        name: 'newPassword',
        path: '/new-password',
        component: NewPassword,
        meta: {
            requiresAuth: false
        }
    },
    {
        name: 'home',
        path: '/',
        component: AllCars,
        meta: {
            requiresAuth: true
        }
    },
    {
        name: 'logout',
        path: '/logout',
        component: Logout,
        meta: {
            requiresAuth: true
        }
    },
    {
        name: 'bids',
        path: '/bids',
        component: Bids,
        meta: {
            requiresAuth: true
        }
    },
    {
        name: 'schedulings',
        path: '/schedulings',
        component: Schedulings,
        meta: {
            requiresAuth: true
        }
    },
    {
        name: 'payments',
        path: '/payments',
        component: Payments,
        meta: {
            requiresAuth: true
        }
    }
];