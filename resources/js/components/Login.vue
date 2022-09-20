<template>
    <div id="Login" class="page-content-detail">
        <div class="login-form">
            <img class="logo" src="/img/login-logo.png" alt="">
            <div class="login-label">
                Sign in to your account
            </div>
            <div class="form-group">
                <label class="control-label">
                    Username
                </label>
                <input type="text" class="form-control" v-model="username">
            </div>
            <div class="form-group" style="margin-top:30px;">
                <label class="control-label">
                    Password
                </label>
                <input type="password" class="form-control" v-model="password">
            </div>
            <button class="btn btn-primary btn-signin" v-on:click="login()">
                SIGN IN
            </button>
            <div v-if="error" style="font-size:18px;max-width:390px;color:#FF0000;">
                {{error}}
            </div>
            <div class="text-center">
                <a href="/forgot-password" class="signin-other">Forgot Password</a>
            </div>
        </div>
        <div class="login-contact">
            If you have any questions, concerns or suggestions on how we can better serve you please contact 
            <a href="mailto:dispatch@junkcarboys.com">dispatch@junkcarboys.com</a>
        </div>
        <div class="bottom-terms text-center">
            <a href="javascript:;" class="text-right">Terms and Conditions</a>
            &nbsp; | &nbsp;
            <a href="javascript:;" class="text-left">Privacy Policy</a>
        </div>
    </div>
</template>

<script>
import EventBus from '../event-bus';
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
           
        },
        methods: {
            login() {
                if (!this.username) return alert('Please input the username');
                if (!this.password) return alert('Please input the password');

                let loader = this.$loading.show();
                this.error = '';

                this.axios
                    .post(`/api/login`, {
                        username: this.username, 
                        password: this.password
                    })
                    .then(response => {
                        loader.hide();
                        var res = response.data;
                        if (res.error) return this.error = "Username or Password is invalid";

                        commonService.save_login(res.data);
                        this.$router.push('/');
                });


                // commonService.login(this.username, this.password);



                // setTimeout(() => {
                //     this.$router.replace('/');
                // }, 300);
            }
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