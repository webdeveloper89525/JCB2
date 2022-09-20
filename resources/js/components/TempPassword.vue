<template>
    <div id="ForgotPassword" class="page-content-detail">
        <div class="login-form">
            <img class="logo" src="/img/login-logo.png" alt="">
            <div class="login-label">
                Enter your temporary password
            </div>
            <div class="" style="font-size:18px;max-width:390px;    min-height: 110px;">
                Please enter the temporary password we sent to your email address.
            </div>
            <div class="form-group" style="    margin-top: 60px;margin-bottom: 0;">
                <label class="control-label">
                    Password
                </label>
                <input type="password" class="form-control" v-model="password" placeholder="Enter your password">
            </div>
            <button class="btn btn-primary btn-signin" v-on:click="submit()">
                LOGIN NOW
            </button>
            <!-- <div class="text-center">
                <a href="/login" class="signin-other">Login</a>
            </div> -->
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
                password: '',
            }
        },
        created() {
            
        },
        beforeDestroy () {
           
        },
        mounted() {
           var username = localStorage.getItem('username');
           if (!username) {
               return this.$router.push('forgot-password');
           }
        },
        methods: {
            submit() {
                if (!this.password) return alert('Please input the password');
                
                let loader = this.$loading.show();
                var username = localStorage.getItem('username');
                this.axios
                    .post(`/api/temp-password`, {
                        username, password: this.password
                    })
                    .then(response => {
                        loader.hide();
                        var res = response.data;
                        if (res.error) return alert(res.error);
                        localStorage.setItem('temp-password', this.password);
                        this.$router.push('new-password');
                });
            }
        }
    }
</script>

<style lang="stylus" scoped>
#ForgotPassword {
    padding: 50px 0;
    .login-form {
        margin: 0 auto;
        width: fit-content;
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
            margin: 40px 0 25px;
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
    
#ForgotPassword {
    .login-form {
        margin: 10px;
        width: unset;
    }
}
}

</style>