<template>
    <div id="PaymentsPage" class="page-content-detail">
        <div class="page-content-block-wrapper">
            <div class="page-header">
                <span>Your Payments</span>
                <p class="header-summary">Make payments for the vehicles you've purchased and picked-up.</p>
            </div>
        </div>

        <div class="page-content-block-wrapper">
            <div class="page-filter">
                <span class="filter-label">Filters:</span>
                <div class="filter-content" v-if="filter_string != ''">
                    <a href="javascript:;" class="mif-cancel text-danger" v-on:click="resetFilter()"></a>
                    {{filter_string}}
                </div>
            </div>
        </div>

        <div class="mobile-total-header">
            {{total}} RESULTS
        </div>

        <div class="page-content-block-wrapper">
            <div class="car-content">
                <div class="car-left-content col-md-8">
                    <div class="title-header">
                        <div class="title">
                            <input type="checkbox" v-model="checked_all" v-on:change="checkAll(true)">&nbsp;Status
                        </div>
                        <div class="title">Ref#</div>
                        <div class="title">Dispatched Date</div>
                        <div class="title">Year</div>
                        <div class="title">Make</div>
                        <div class="title">Model</div>
                        <div class="title">Amount Due</div>
                    </div>
                    <div class="car-body">
                        <div class="car-item" v-for="car in cars" :key="car.id" v-bind:class="{'selected': car.is_checked == true}">
                            <div class="item-data">
                                <input type="checkbox" style="margin-top: 4px;" v-model="car.is_checked" v-on:change="checkAll()" :disabled="car.Stage == 'Paid'">&nbsp;
                                <div v-if="car.Stage=='Paid'" class="status-fail"> PAID </div>
                                <div v-if="car.Stage=='Scheduled For Pick Up'" class="status-won"> UNPAID </div>
                                <div v-if="car.Stage=='Picked Up'" class="status-active"> OVERDUE </div>
                            </div>
                            <div class="item-data">{{ car.Reference_Number }}</div>
                            <div class="item-data">{{ car.Closing_Date | changeDateFormat }}</div>
                            <div class="item-data">{{ car.Year }}</div>
                            <div class="item-data">{{ car.Make }}</div>
                            <div class="item-data">{{ car.Model }}</div>
                            <div class="item-data text-center">{{ toCurrency(car.Profit) }}</div>

                            <div class="mobile-item item-data"  v-on:click="showDetail(car)">
                                <div class="item-content">
                                    <div class="font-weight-bold">{{car.Reference_Number}} &nbsp;&nbsp;{{car.Year}} {{car.Make}} {{car.Model}}</div>
                                    <div>{{car.City}} &nbsp;&nbsp; {{car.Zip_Code}}</div>
                                    <div style="display:flex;justify-content:space-between;">
                                        <div class="text-blue">{{car.Profit}}</div>
                                        <div v-if="car.Stage=='Paid'" class="status-fail"> Paid </div>
                                        <div v-if="car.Stage=='Scheduled For Pick Up'" class="status-won"> Unpaid </div>
                                        <div v-if="car.Stage=='Picked Up'" class="status-active"> OVERDUE </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="car-right-content col-md-4" v-if="!is_mobile_view || (is_mobile_view && sel_car)">
                    <div class="title-header">
                        <a href="javascript:;" class="btn-close-car-detail">
                            <span class="mif-cancel"></span> Clear
                        </a>
                    </div>

                    <a href="javascript:;" class="btn-close-car-detail" v-on:click="sel_car=null">
                        <span class="mif-cross-light"></span>
                    </a>
                    <div class="car-detail">
                        <div class="empty-content" v-if="!count">
                            Click a list item to view details
                        </div>
                        <div class="submit-content" v-if="count && submit_payment">
                            <div class="title font-weight-bold text-center">
                                <div class="">
                                    YOUR PAYMENT OF <span class="text-blue">{{calculateTotal}}</span>
                                </div>
                                <div class="text-center">
                                    WAS SUBMITTED
                                </div>

                            </div>
                            <div class="bid-image"><img src="/img/payment-success.png" alt=""></div>
                            <div class="action-bar text-right">
                                <button class="btn btn-primary action-button" v-on:click="actionAfterPay">DONE</button>
                            </div>
                        </div>
                        <div class="selcar-content" v-if="count && !submit_payment">
                            <div class="title text-blue">Make Payment</div>
                            <div class="selcar-detail row">
                                <div class="col-md-12 field-item">
                                    <div class="item-label">Card Number</div>
                                    <div id="example2-card-number" class="input empty stripe-elements-div"></div>
                                    <div role="alert" class="stripe-elements-error-message-div">{{stripe_card_errors}}</div>
                                </div>
                                <div class="col-md-12 field-item">
                                    <div class="item-label">Cardholder Name</div>
                                    <input type="text" class="item-value" v-model="pay_info.card_name" placeholder="Your name">
                                </div>
                                <div class="col-md-6 field-item">
                                    <div class="item-label">Card Expiry</div>
                                    <div id="example2-card-expiry" class="input empty stripe-elements-div"></div>
                                    <div role="alert" class="stripe-elements-error-message-div">{{stripe_expiry_errors}}</div>
                                </div>
                                <div class="col-md-6 field-item">
                                    <div class="item-label">CVC</div>
                                    <div id="example2-card-cvc" class="input empty stripe-elements-div"></div>
                                    <div role="alert" class="stripe-elements-error-message-div">{{stripe_cvc_errors}}</div>
                                </div>
                                <div class="col-md-12 field-item">
                                    <div class="item-label">Amount</div>
                                    <input type="text" class="item-value txt-amount" v-model="calculateTotal" placeholder="" readOnly>
                                </div>
                            </div>
                            <div class="action-bar">
                                <div class="action-button background-white">
                                    <img src="/img/payment-card.png" alt="">
                                </div>
                                <button class="btn btn-primary action-button" v-on:click="submitPaymentTest" :disabled='preventPayButton'>SUBMIT PAYMENT</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content-block-wrapper padding-0">
            <div class="row footer-wrapper">
                <div class="pagination col-md-8">
                    <div class="page-label">
                        Showing <span> {{(page-1) * records_per_page + 1 }} </span> to <span> {{ (page-1) * records_per_page + cars.length }} </span> of {{total}} Available Cars
                    </div>
                    <div class="pages-action">
                        <!-- <select @change="changeItemCount" v-model="records_per_page">
                            <option v-for="item in countPerPageArray" :value="item" :key="item">{{item}} items</option>
                        </select>
                        &nbsp;&nbsp;Per Page: -->
                        {{records_per_page}} items Per Page:
                        <template v-for="one of valid_pages">
                            <a :key="one"  class="btn-page" v-bind:class="{active: one == page}" href="javascript:;" v-on:click="refreshPage(one)">{{one}}</a>
                        </template>
                        <a class="btn-page"  href="javascript:;" v-on:click="refreshPage(page-1)">&lt; Prev</a>
                        <a class="btn-page"  href="javascript:;" v-on:click="refreshPage(page+1)">Next &gt;</a>
                    </div>
                </div>
                <div class="footer-right col-md-4">
                    <div class="page-label">
                        <span>{{ count }} </span> Vehicles Selected for Payment
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import EventBus from '../event-bus';
import CommonService from '../services/CommonService';
var commonService = new CommonService();

    export default {
        data() {
            return {
                cars: [],
                page: 1,
                records_per_page: 10,
                total: '-',
                valid_pages: [],
                filter_param: {},
                filter_like: false,
                filter_string: '',
                sel_car: null,
                bid_price: '',
                pay_info : {},
                checked_all: false,
                is_mobile_view: window.innerWidth <= 992,
                count: 0,
                calculateTotal: "",
                submit_payment: false,
                stripe: '',
                Card: '',
                submit_payment1: false,
                stripe_card_errors: '',
                stripe_expiry_errors: '',
                stripe_cvc_errors: '',
                preventPayButton: true,
                initPreventPay: true,
                initialized: false,
                stripeApiKey: 'pk_live_ALwf4Vu327m7ddpZqB4Rt7bD',
                countPerPageArray: [8, 9, 10],
            }
        },
        filters: {
            changeDateFormat: function(closing_date) {
                if(closing_date) {
                    var val=closing_date.split('-');
                    return val[1] + "/" + val[2] + "/" + val[0];
                }
                return "";
            },
        },
        computed: {

        },
        created() {
            const thiz = this;
            // this.records_per_page = this.countPerPageArray[0];
            EventBus.$on('update-payments-filter', function(filter_param) {
                thiz.filter_param = filter_param;
                thiz.filter_string = thiz.filter_param['filter_string'];
                delete thiz.filter_param['filter_string'];
                thiz.refreshPage(1);
            });

        },
        beforeDestroy () {
            EventBus.$off('update-bid-filter');
        },
        mounted() {
            this.refreshPage(1);
            const stripeApiKey = this.stripeApiKey;
            let stripeScript = document.createElement('script');
            stripeScript.setAttribute('src', 'https://js.stripe.com/v3/');
            stripeScript.onload = () => {
                this.stripe = Stripe(stripeApiKey);
            };

            document.head.appendChild(stripeScript);
            console.log(this.cars);

        },
        methods: {
            changeItemCount() {
                this.refreshPage();
            },
            preventPay: function() {
                if (this.stripe_card_errors || this.stripe_cvc_errors || this.stripe_expiry_errors) {
                    this.preventPayButton = true;
                } else {
                    this.preventPayButton = false;
                }
            },
            initStripe: function() {
                let elements = this.stripe.elements();
                let Style = {
                        base: {
                        color: '#32325d',
                         '::placeholder': {
                            color: '#269A8E',
                        },
                    }
                };

                var elementStyles = {
                    base: {
                        fontSize: '14px',
                        border:"none",
                        borderBottom: "1px solid #269A8E",
                        fontWeight: 500,
                        padding: "5px 0",
                        outline: "none",
                        width:"100%",
                        font: "normal normal normal 14px/17px Lato",
                        color: '#B9B9B9',
                        fontFamily: 'Source Code Pro, Consolas, Menlo, monospace',
                        fontSize: '16px',
                        fontSmoothing: 'antialiased',
                        '::placeholder': {
                            color: '#269A8E',
                        },
                        ':-webkit-autofill': {
                            color: '#e39f48',
                        },
                    },
                    invalid: {
                        color: '#E25950',
                        '::placeholder': {
                            color: '#FF1744',
                        },
                    },
                };

                var elementClasses = {
                    focus: 'focused',
                    empty: 'empty',
                    invalid: 'invalid',
                };
                let that = this;
                this.Card = elements.create('cardNumber', {
                    style: elementStyles,
                    classes: elementClasses,
                });
                this.Card.mount('#example2-card-number');
                this.Card.on('change', ({error}) => {
                    if (error) {
                        that.stripe_card_errors = error.message;
                    } else {
                        that.stripe_card_errors = '';
                    }
                    that.preventPay();
                });
                var cardExpiry = elements.create('cardExpiry', {
                    style: elementStyles,
                    classes: elementClasses,
                });
                cardExpiry.mount('#example2-card-expiry');
                cardExpiry.on('change', ({error}) => {
                    if (error) {
                        that.stripe_expiry_errors = error.message;
                    } else {
                        that.stripe_expiry_errors = '';
                    }
                    that.preventPay();
                });
                var cardCvc = elements.create('cardCvc', {
                    style: elementStyles,
                    classes: elementClasses,
                });
                cardCvc.mount('#example2-card-cvc');
                cardCvc.on('change', ({error}) => {
                    if (error) {
                        that.stripe_cvc_errors = error.message;
                    } else {
                        that.stripe_cvc_errors = '';
                    }
                    that.preventPay();
                });
            },
            toCurrency: function(value) {
                if(!value) return "";
                var formatter = new Intl.NumberFormat("en-US", {
                    style: 'currency',
                    currency: "USD"
                });
                return formatter.format(value).replace("$", "$ ");
            },
            setCount() {
                this.count = this.cars.filter(one => one.is_checked).length;
            },
            checkAll(force = false) {
                if (force) {
                    this.cars.forEach(one => {if(one.Stage != "Paid") one.is_checked = this.checked_all})
                } else {
                    this.checked_all = this.cars.length == this.cars.filter(one => one.is_checked).length;
                    if(this.cars.filter(one => one.is_checked).length) this.showDetail();
                }
                this.setCount();
                var amount = 0;
                this.cars.map(car => {
                    if(car.is_checked) amount += parseFloat(car.Profit || 0);
                })
                this.calculateTotal = this.toCurrency(amount);
                this.submit_payment = false;
            },
            refreshPage(page) {
                if (!page) page = this.page;
                if (page < 1 || page > parseInt(this.total/this.records_per_page) + 1) return;
                this.page = page;

                this.cars = [];
                for (let index = 0; index < this.records_per_page; index++) {
                    this.cars.push({index})
                }

                let url = '/api/cars?page_type=payments&page=' + this.page+'&records_per_page='+this.records_per_page;
                for (const key in this.filter_param) {
                    if (this.filter_param[key]) {
                        url += '&' + key + '=' + this.filter_param[key];
                    }
                }
                if (this.filter_like) {
                    url += '&type=like';
                }

                let loader = this.$loading.show();

                this.axios
                .get(url, commonService.get_api_header())
                .then(response => {
                    loader.hide();
                    var res_data = response.data;
                    this.total = res_data.total;
                    var data = res_data.data;
                    if(!this.initialized && this.$route.query.id) {
                        data = res_data.data.map(car => {
                            if(car.id == this.$route.query.id && car.Stage != "Paid") car.is_checked = true;
                            else car.is_checked = false;
                        });
                    }
                    this.cars = res_data.data;
                    var start_page = Math.max(1, this.page - 2);
                    var end_page = Math.min(start_page + 4, parseInt(this.total/this.records_per_page) + 1 );
                    this.valid_pages = [];
                    for (let index = start_page; index <= end_page; index++) {
                        this.valid_pages.push(index);
                    }
                    this.checkAll();
                }).catch((error) => {
                    loader.hide();
                    var status = error.response.status;
                    if (status == 401) {
                        commonService.logout();
                        this.$router.push('login');
                    } else {
                        alert('Api request error');
                    }
                });

                this.sel_car = null;
                this.initialize();
            },
            resetFilter() {
                EventBus.$emit('reset-payment-filter');
            },
            showDetail(car) {
                this.sel_car = null;
                setTimeout(() => {
                    this.sel_car = car;
                    this.submit_payment = false;
                    this.initialize();
                    this.initStripe();
                }, 200);
            },
            initialize() {
                this.pay_info = {card_name: "", card_no: "", cvc: "", exp: ""};
            },

            submitPaymentTest() {
                this.submit_payment1 = true;
                let that = this;

                if(!this.pay_info.card_name) {
                    alert('card name is empty');
                    return;
                }
                let loader = this.$loading.show();
                const amount = parseFloat(this.calculateTotal.replace('$', ''));
                this.axios.post('/api/payments/stripe/intent', {
                    'amount': amount *100,
                    'currency': 'USD'
                },commonService.get_api_header())
                .then(function (response) {
                    loader.hide();
                    that.confirmCardPayment(response.data.Intent.Secret);
                }).catch(function (error) {
                    console.log(error);
                    alert(error);
                    loader.hide();
                });
            },
            confirmCardPayment(clientSecret) {
                let loader = this.$loading.show();
                let that = this;
                this.stripe.confirmCardPayment(clientSecret, {
                    payment_method: {
                        card: this.Card,
                    }
                    }).then(function(result) {
                    that.submit_payment1= false;
                    loader.hide();
                    if (result.error) {
                        // console.log('payment error' + result.error.message);
                        alert("Card Information isn't correct!");
                    } else {
                        if (result.paymentIntent.status === 'succeeded') {
                            console.log('payment success');
                            console.log(result.paymentIntent);
                            that.submitPayment();
                            // pass id from paymentIntent to backend when making next request for payment verification
                            // continue making create invoice request here
                            // we're done, if you have api keys you can test
                            //
                        } else {
                            console.log('payment error');
                        }
                    }
                });
            },
            submitPayment() {


                // if (this.pay_info.card_no.length < 16) return alert('Please check the card number');
                // if (!this.pay_info.card_name) return alert('Please input the cardholder name');
                // if (this.pay_info.exp < 4) return alert('Please check the card expiry');
                // if (this.pay_info.cvc < 3) return alert('Please check card cvc number');

                var arr = [];
                this.cars.map(car => {
                    if(car.is_checked) arr.push(car);
                })
                console.log(this.pay_info);
                const thiz = this;
                let loader = this.$loading.show();

                this.axios
                    .post(`/api/car/pay`, {pay_info: this.pay_info, cars: arr}, commonService.get_api_header())
                    .then(response => {
                        console.log(response)
                        loader.hide();
                        this.submit_payment = true;
                        this.cars.map(car => {
                            if(car.is_checked) {
                                car.Stage = "Paid";
                                car.is_checked = false;
                            }
                        })
                }).catch((error) => {
                    loader.hide();
                    var status = error.response.status;
                    if (status == 401) {
                        commonService.logout();
                        this.$router.push('login');
                    } else {
                        alert('Api request error');
                    }
                });

            },
            actionAfterPay() {
                console.log('dd');
                this.cars.map(car =>
                    car = { ...car, is_checked: false }
                );
                this.checked_all = false;
                this.submit_payment = false;
                this.count = 0;
                // this.refreshPage(1);
            }
        }
    }
</script>


<style lang="stylus" scoped>
#PaymentsPage {
    .status-won, .status-active, .status-fail {
        margin-left: 0;
    }
}
</style>
