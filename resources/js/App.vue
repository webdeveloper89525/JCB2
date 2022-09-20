<template>
<div id="bg">
    <div id="jcbApp">
        <site-navbar v-if="authenticated"></site-navbar>
        <site-menubar v-if="authenticated" @checkPopupOpen="checkPopupStatus"></site-menubar>
        <div id="PageContent" v-bind:class="{'no-margin' : !authenticated, 'popup-opened': popupStatus}">
            <router-view></router-view>
        </div>
    </div>
</div>

</template>

<script>
    import SiteNavbar from './components/SiteNavbar';
    import SiteMenubar from './components/SiteMenubar';
    import CommonService from './services/CommonService'
    const commonService = new CommonService();

    export default {
        data() {
            return {
                authenticated: false,
                loaded: false,
                popupStatus: false,
                commonService
            };
        },
        watch: {
            $route (to_route, from_route) {
                this.authenticated = commonService.is_authenticated();
            }
        },
        components: {
            SiteNavbar,
            SiteMenubar,
        },
        mounted() {
            this.authenticated = commonService.is_authenticated();
            this.loaded = true;
        },
        created() {
            this.authenticated = commonService.is_authenticated();
            
        },
        methods: {
            checkPopupStatus(flag) {
                this.popupStatus = flag;
            }
        }

    }
</script>