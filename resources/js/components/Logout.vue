<template>
</template>

<script>
import CommonService from '../services/CommonService'
const commonService = new CommonService();

    export default {
        data() {
            return {
                username: '',
                password: '',
                error: '',
            }
        },
        created() {
            
        },
        beforeDestroy () {
           
        },
        mounted() {
           commonService.logout();
           this.$router.push('login');
        }
    }
</script>

<style lang="stylus" scoped>
#Login {
    padding: 50px 0;
    .login-form {
        margin: 0 auto;
        width: 490px;
        padding: 50px;
        background white;
        box-shadow: 0px 10px 10px #00000026;
        border-radius: 4px;
        img.logo {
            height: 150px;
            margin: auto;
            display: block;
        }
        .login-label {
            color: #1A173B;
            font-weight: bold;
            font-size: 21px;
            margin: 40px 0;
            text-align : center;
        }
        .control-label {
            color: #6D7A87;
            font-size: 14px;
        }
        .form-control {
            font-size: 18px;
            color: #1A173B;
            padding: 10px 0;
            border:none;
            border-bottom: 1px solid #269A8E;
            outline: none;
            box-shadow: none;
            border-radius: 0;
        }
        .btn-signin {
            height: 60px;
            color: #B2FF9E;
            font-size: 14px;
            margin: 30px 0;
            width: 100%;
        }
        a.singin-other {
            color: #0077FF;
            font-size: 14px;
        }
    }
    .login-contact {
        max-width: 600px;
        text-align: center;
        margin: 30px auto;
        color: #707070;
        font-size: 17px;
    }
    .bottom-terms {
        font-size: 12px;
        color: #9FA9BA;
        display: flex;
        a {
            color: #9FA9BA;
            flex:1;
        }
    }
}
@media only screen and (max-width: 992px) {
    
#Login {
    .login-form {
        margin: 10px;
        width: unset;
    }
}
}

</style>