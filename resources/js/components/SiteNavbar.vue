<template>
<div id="SiteNavbar">
    <nav class="navbar navbar-expand-lg">
        <div class="collapse navbar-collapse">
            <div class="navbar-userinfo">
                <div class="navbar-notification logo-wrapper">
                    <img v-bind:src="avatar" alt="logo">
                </div>
                <div class="navbar-username">
                    {{username}} <span class="mif-arrow-drop-down drop-down" @click="showDropdown"></span>
                    <div class="dropdown-action-menu" v-if="showActions">
                        <div class="dropDownMenu-header">
                            <div>Menu</div>
                            <div class="close" @click="showDropdown"><span class="mif-cross-light"></span></div>
                        </div>
                        <div class="content">
                            <div class="content-row">
                                <span class="mif-user"></span>
                                &nbsp;&nbsp;Profile
                            </div>
                            <div class="content-row">
                                <span class="mif-credit-card"></span>
                                &nbsp;&nbsp;Payment Method
                            </div>
                        </div>
                        <div class="signout" @click="logout">
                            Sign Out
                        </div>
                    </div>
                </div>

            </div>
            <div class="navbar-nav">
                <div class="nav-wrapper">
                <router-link to="/" class="nav-item nav-link" v-bind:class="{'active': $route.name == 'home'}">All Cars</router-link>
                <router-link to="/bids" class="nav-item nav-link" v-bind:class="{'active': $route.name == 'bids'}">Bids</router-link>
                <router-link to="/schedulings" class="nav-item nav-link" v-bind:class="{'active': $route.name == 'schedulings'}">Scheduling</router-link>
                <router-link to="/payments" class="nav-item nav-link" v-bind:class="{'active': $route.name == 'payments'}">Payment</router-link>
                <router-link to="/reports" class="nav-item nav-link" v-bind:class="{'active': $route.name == 'reports'}">Reports</router-link>
                </div>
            </div>
            <div class="navbar-notification"><span class="mif-bell"></span></div>
        </div>
        <div class="navbar-mobile d-lg-none w-100">
            <div class="navbar-mobile-content">
                <img class="user-avatar" v-bind:src="avatar" alt="logo">

                <div class="nav-mobile-title" v-if="$route.name=='home'">All Cars</div>
                <div class="nav-mobile-title" v-if="$route.name=='bids'">Your Bids</div>
                <div class="nav-mobile-title" v-if="$route.name=='schedulings'">Scheduling</div>
                <div class="nav-mobile-title" v-if="$route.name=='payments'">Your Payments</div>
                <div class="nav-mobile-title" v-if="$route.name=='reports'">Reports</div>

                <b-navbar-toggle target="navbar-toggle-collapse" class="btn-mobile-menu">
                    <span class="mif-menu"></span>
                </b-navbar-toggle>
            </div>


            <b-collapse id="navbar-toggle-collapse" is-nav >
                <b-navbar-nav class="ml-auto">
                    <b-nav-item href="/"  v-bind:class="{'active': $route.name == 'home'}">All Cars</b-nav-item>
                    <b-nav-item href="/bids"  v-bind:class="{'active': $route.name == 'bids'}">Bids</b-nav-item>
                    <b-nav-item href="/schedulings"  v-bind:class="{'active': $route.name == 'schedulings'}">Scheduling</b-nav-item>
                    <b-nav-item href="/payments"  v-bind:class="{'active': $route.name == 'payments'}">Payment</b-nav-item>
                    <b-nav-item href="/reports"  v-bind:class="{'active': $route.name == 'reports'}">Reports</b-nav-item>
                </b-navbar-nav>
            </b-collapse>
        </div>
    </nav>

</div>
</template>

<script>
import EventBus from '../event-bus';
import CommonService from '../services/CommonService';
const commonService = new CommonService();

export default {
    data() {
        return {
            username: '',
            avatar : '/img/avatar.png',
            showActions: false
        };
    },
    mounted() {
        this.username = commonService.get_auth_name();
        this.avatar = commonService.get_auth_avatar();
        this.showActions = false;
    },
    watch: {
        $route (to_route, from_route) {
            this.username = commonService.get_auth_name();
            this.avatar = commonService.get_auth_avatar();
        }
    },
    methods: {
        logout() {
            console.log("logout")
            let loader = this.$loading.show();
            this.axios
                .get(`/api/logout`)
                .then(response => {
                    loader.hide();
                    this.$router.push('/logout');
                });
        },
        showDropdown(flag) {
            this.showActions = !this.showActions;
        }
    }
}
</script>
