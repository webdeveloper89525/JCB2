<template>
    <div id="NewPassword" class="page-content-detail">
        <div class="login-form">
            <img class="logo" src="/img/login-logo.png" alt="">
            <div class="login-label">
                Enter a new password
            </div>
            <div class="" style="font-size:18px;max-width:390px;">
                Fantastic. Please enter a new password for our records. You’ll then be provided access to the portal.
            </div>
            <div class="form-group" style="">
                <label class="control-label">
                    New Password
                </label>
                <input type="password" class="form-control" v-model="password" placeholder="Enter a new password">
            </div>
            <div class="form-group" style="">
                <label class="control-label">
                    Confirm Password
                </label>
                <input type="password" class="form-control" v-model="confirm_password" placeholder="Confirm your entry above">
            </div>
            <button class="btn btn-primary btn-signin" v-on:click="submit()">
                LETS GO!
            </button>
            <div v-if="match_fail" style="font-size:18px;max-width:390px;color:#FF0000;">
                Apologies, the two passwords did not match. Please enter and confirm a new password.
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
                password: '',
                confirm_password: '',
                match_fail: false,
            }
        },
        created() {
            
        },
        beforeDestroy () {
           
        },
        mounted() {
           var username = localStorage.getItem('username');
           var temp_password = localStorage.getItem('temp-password');
           if (!username || !temp_password) {
               return this.$router.replace('forgot-password');
           }
        },
        methods: {
            submit() {
                if (!this.password || this.password != this.confirm_password) {
                    return this.match_fail = true;
                }
                this.match_fail = false;

                if (!this.password) return alert('Please input the password');
                
                let loader = this.$loading.show();

                var username = localStorage.getItem('username');
                var temp_password = localStorage.getItem('temp-password');

                this.axios
                    .post(`/api/reset-password`, {
                        username, temp_password, password: this.password
                    })
                    .then(response => {
                        loader.hide();
                        var res = response.data;
                        if (res.error) return alert(res.error);

                        localStorage.clear();
                        this.$router.push('login');
                });

            }
        }
    }
</script>

<style lang="stylus" scoped>
#NewPassword {
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
    .form-group {
            margin-top: 25px;
    }
}
@media only screen and (max-width: 992px) {
    
#NewPassword {
    .login-form {
        margin: 10px;
        width: unset;
    }
}
}

</style>