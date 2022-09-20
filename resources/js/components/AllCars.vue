<template>
    <div id="AllCarsPage" class="page-content-detail">
        <template>
            <div class="page-content-block-wrapper">
                <div class="page-header">
                    <span>All Cars</span>
                    <p class="header-summary">View and bid on vehicles available for purchase.</p>
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
                <div class="car-content" v-if="!sel_car">
                    <div class="title-header">
                        <div class="action-heart"></div>
                        <div class="title">Year</div>
                        <div class="title">Make</div>
                        <div class="title">Model</div>
                        <div class="title">City</div>
                        <div class="title">Distance</div>
                        <div class="title">Drives</div>
                        <div class="title">Closing Date</div>
                        <div class="title">Mileage</div>
                        <div class="title">Current Offer</div>
                        <!-- <div class="action-go"></div> -->
                    </div>
                    <div class="car-body">
                        <div class="car-item" v-for="car in cars" :key="car.id" @click="showDetail(car, $event)">
                            <div class="action-heart">
                                <a href="javascript:;" class="mif-heart" v-bind:class="{'text-danger': car.is_liked}"  v-on:click="likeCar(car)"></a>
                            </div>
                            <div class="item-data">{{ car.Year }}</div>
                            <div class="item-data">{{ car.Make }}</div>
                            <div class="item-data">{{ car.Model }}</div>
                            <div class="item-data">{{ car.City }}</div>
                            <div class="item-data lowercase">{{ car.Distance | distanceFormat }}</div>
                            <div class="item-data">{{ car.Does_the_Vehicle_Run_and_Drive }}</div>
                            <div class="item-data">{{ car.Closing_Date | changeDateFormat }}</div>
                            <div class="item-data text-center">{{ car.Miles || "Unable To Verify" }}</div>
                            <div class="item-data text-center">${{ car.Buyers_Quote}}</div>
                            <!-- <div class="text-center action-go">
                                <a href="javascript:;" v-on:click="showDetail(car)">
                                    <span class="mif-arrow-right"></span>
                                </a>
                            </div> -->
                            <div class="mobile-item item-data" v-on:click="showDetail(car)">
                                <div class="item-content">
                                    <div class="font-weight-bold">{{car.Reference_Number}} &nbsp;&nbsp;{{car.Year}} {{car.Make}} {{car.Model}}</div>
                                    <div>{{car.City}} &nbsp;&nbsp; {{car.Zip_Code}}</div>
                                    <div class="text-blue">{{car.Buyers_Quote}}</div>
                                </div>
                                <div class="action-heart">
                                    <a href="javascript:;">
                                        <span class="mif-heart"  v-bind:class="{'text-danger': car.is_liked}"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <template v-if="sel_car">
                    <div class="car-content car-detail-popup">
                        <template v-if="!submit_bid">
                            <div class="detail-bottom">
                                <button class="btn btn-default btn-like" v-bind:class="{'is_liked': sel_car.is_liked}" v-on:click="likeCar(sel_car)" >
                                    <span class="mif-heart"></span>
                                </button>
                                <a href="javascript:;" class="btn-close" v-on:click="sel_car = null"><span class="mif-cross-light"></span></a>
                            </div>
                            <div class="detail-content">
                                <div class="row car-fields">
                                    <div class="car-header col-md-6">
                                        <div class="car-title">
                                            {{sel_car.Year}} {{sel_car.Make}} {{sel_car.Model}}
                                            <br>
                                            <label>{{sel_car.City}} - {{sel_car.Miles | milesValidate}}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                    </div>
                                    <div class="car-header col-md-3 ">
                                        <div class="action-bar margin-ignore">
                                            <div class="header-label">{{bid_submit_button_string.caption}}</div>
                                            <span>$</span>
                                            <input ref="bid_input" type="number" @keyup="changeStatus" @change="changeStatus" placeholder="" v-model="bid_price">
                                            <button class="btn" :class="'status'+bid_status" @click="submitBid()">{{bid_submit_button_string.btn_str}}</button>
                                        </div>
                                    </div>
                                    <div class="field-item col-md-3" v-for="(detail_field, i) in detail_fields" :key="i">
                                        <div class="item-label">{{detail_field.field}}</div>
                                        <div type="text" class="item-value" :class="{'fontColorBlack': replaceIfEmpty(sel_car[detail_field.key]) != '-- None --' }">{{ replaceIfEmpty(sel_car[detail_field.key]) }}</div>
                                    </div>
                                </div>
                            </div>

                        </template>
                        <template v-if="submit_bid">
                            <div class="submit-success">
                                <div class="title">YOUR BID OF <span class="text-blue">{{bid_price | toCurrency}}
                                <br>
                                </span>WAS SUBMITTED!</div>
                                <div class="img-div">
                                    <img src="/img/bid_success.png" alt="">
                                </div>
                                <div class="text-center btn-div">
                                    <button class="btn btn-primary btn-done" v-on:click="refreshPage(page)">DONE</button>
                                </div>
                                <div class="detail-bottom">
                                    <div>
                                        <a href="javascript:;" class="btn-close" v-on:click="refreshPage(page)"><span class="mif-cross-light"></span></a>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            <div class="page-content-block-wrapper">
                <div class="pagination">
                    <div class="page-label">
                        Showing <span> {{ total }} </span> Available Cars
                    </div>
                    <!-- <div class="pages-action">
                        Page:
                        <template v-for="one of valid_pages">
                            <a :key="one"  class="btn-page" v-bind:class="{active: one == page}" href="javascript:;" v-on:click="refreshPage(one)">{{one}}</a>
                        </template>
                        <a class="btn-page"  href="javascript:;" v-on:click="refreshPage(page-1)">&lt; Prev</a>
                        <a class="btn-page"  href="javascript:;" v-on:click="refreshPage(page+1)">Next &gt;</a>
                    </div> -->
                </div>
            </div>
        </template>
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
                records_per_page: 8,
                total: '-',
                valid_pages: [],
                filter_param: {},
                filter_like: false,
                filter_string: '',
                sel_car: null,
                bid_price: '',
                bid_status: 1,
                submit_bid: '',
                sort_field: "id",
                sort_arrow: 1,
                detail_fields: [
                    {field: "Zip", key: "Zip_Code"},
                    {field: "City", key: "City"},
                    {field: "Runs/Drives", key: "Does_the_Vehicle_Run_and_Drive"},
                    {field: "Mileage", key: "Miles"},
                    {field: "Title", key: "Do_they_have_a_Title"},
                    {field: "Airbags Deployed", key: "Airbags_Deployed"},
                    {field: "Damage", key: "Is_There_Any_Body_Damage_Broken_Glass_2"},
                    {field: "Damage Description", key: "Is_there_any_Body_Damage_Broken_Glass"},
                    {field: "Broken Glass", key: "Is_There_Any_Broken_Glass_Windows_etc"},
                    {field: "Wheels Mounted", key: "Are_all_the_tires_mounted"},
                    {field: "Missing Wheels", key: "Which_tires_are_missing"},
                    {field: "Tires Inflated", key: "Are_All_the_Tires_Inflated"},
                    {field: "Flat Tires", key: "Which_ones_are_flat"},
                    {field: "Fire/Flood/Hail", key: "Fire_or_Flood_Damage"},
                    {field: "Missing Parts", key: "Any_Missing_Body_Panels_Interior_or_Engine_Parts"},
                    {field: "Mechanical Issues", key: "What_Kind_of_Mechanical_Issues_Are_There"},
                ],
                bid_submit_button_string: {}
            }
        },
        created() {
            const thiz = this;
            EventBus.$on('update-car-filter', function(filter_param) {
                thiz.filter_param = filter_param;
                thiz.filter_string = thiz.filter_param['filter_string'];
                thiz.filter_like = thiz.filter_param['filter_like'];
                delete thiz.filter_param['filter_string'];
                thiz.refreshPage(1);
            });
            EventBus.$on('update-car-filter-like', function(filter_like) {
                thiz.filter_like = filter_like;
                thiz.filter_param = {};
                thiz.refreshPage(1);
            });

            console.log('created');
        },
        beforeDestroy () {
            EventBus.$off('update-car-filter')
            EventBus.$off('update-car-filter-like')
        },
        mounted() {
            this.refreshPage(1);
        },
        computed: {

        },
        filters: {

            milesValidate: function(value) {
                if(value == null)
                    return "Unable to Verify";
                else
                    return value + " Miles";
            },
            toCurrency: function(value) {
                var formatter = new Intl.NumberFormat("en-US", {
                    style: 'currency',
                    currency: "USD"
                });
                return formatter.format(value);
            },
            distanceFormat: function(distance) {
                if(distance) {
                    if(distance < 100) return distance + '\xa0\xa0\xa0\xa0\xa0mi';
                    if(distance < 1000) return distance + '\xa0\xa0\xa0mi';
                    if(distance < 10000) return distance + '\xa0mi';
                    return distance + "mi";
                }
                return "";
            },
            changeDateFormat: function(closing_date) {
                if(closing_date) {
                    var val=closing_date.split('-');
                    return val[1] + "/" + val[2] + "/" + val[0];
                }
                return "";
            },
        },
        methods: {
            refreshPage(page) {
                if (!page) page = this.page;
                if (page < 1 || page > parseInt(this.total/this.records_per_page) + 1) return;
                this.page = page;
                this.sel_car = null;

                this.cars = [];
                for (let index = 0; index < this.records_per_page; index++) {
                    this.cars.push({index})
                }

                let url = '/api/cars?page_type=cars&page=' + this.page;
                for (const key in this.filter_param) {
                    if (this.filter_param[key]) {
                        url += '&' + key + '=' + this.filter_param[key];
                    }
                }
                if (this.filter_like) {
                    url += '&type=like';
                }

                let loader = this.$loading.show();

                var arrow = this.sort_arrow;
                var sort_field = this.sort_field;

                this.axios
                .get(url, commonService.get_api_header())
                .then(response => {
                    loader.hide();
                    var res_data = response.data;
                    this.cars = res_data.data.sort((a, b) =>  (a[sort_field] - b[sort_field]) * arrow );
                    // this.cars = res_data.data;
                    this.total = res_data.total;

                    var start_page = Math.max(1, this.page - 2);
                    var end_page = Math.min(start_page + 4, parseInt(this.total/this.records_per_page) + 1 );
                    this.valid_pages = [];
                    for (let index = start_page; index <= end_page; index++) {
                        this.valid_pages.push(index);
                    }
                })
                .catch((error) => {
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
            likeCar(car) {
                let loader = this.$loading.show();
                this.axios
                    .post(`/api/car/like/${car.id}`, {like: (car.is_liked ? '' : true)}, commonService.get_api_header())
                    .then(response => {
                        loader.hide();
                        car.is_liked = !car.is_liked;
                        // this.refreshPage();
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
            resetFilter() {
                EventBus.$emit('reset-car-filter');
            },
            replaceIfEmpty(value) {
                // return value;
                var emptyPlaceHolderStr = "-- None --";
                if(value == "" || value == undefined || value == null) return emptyPlaceHolderStr;
                var flag = false;
                if(typeof value === "number") return value;
                if(Array.isArray(value)) return value.join(',');
                if(value.charAt(0) == "[") {
                    var ini_str = "";
                    var arr = JSON.parse(value);
                    arr.map(val=> {
                        if(typeof val === "object"){
                            for(var prop in val) {
                                if (val.hasOwnProperty(prop)) {
                                    ini_str += val.prop;
                                }
                            }
                        }
                        else {
                            ini_str += val;
                        }
                    })
                    if(ini_str == "") return emptyPlaceHolderStr;
                    else return ini_str;
                }
                return value;

            },
            checkFontColor(val) {
                if(val == "-- None --") return false;
                return true;
            },
            showDetail(car, $evt) {
                if($evt.toElement.className.includes('mif-heart')) return;

                this.sel_car = car;
                this.bid_price = (car.Buyers_Quote)? parseFloat(car.Buyers_Quote).toFixed(2) : '0';
                this.submit_bid = false;
                this.changeStatus();
            },
            changeStatus() {
                var caption, btn_str;
                var post_price = this.sel_car.Buyers_Quote? parseFloat(this.sel_car.Buyers_Quote): 0;
                if(parseFloat(this.bid_price) > post_price) {
                    this.bid_status = 2;
                    caption = "Your Offer";
                    btn_str = "SUBMIT";
                }
                else if(parseFloat(this.bid_price) == post_price) {
                    this.bid_status = 1;
                    caption = "Current Offer";
                    btn_str = "BID";
                }
                else {
                    this.bid_status = 0;
                    caption = "Your Offer";
                    btn_str = "TOO LOW";
                }

                this.bid_submit_button_string = {caption: caption, btn_str: btn_str};
            },
            submitBid() {
                if (!this.bid_price) return alert('Please type the bid price');
                if(this.bid_status == 0) return;
                else if(this.bid_status == 1) { this.$refs.bid_input.focus(); return; }

                let loader = this.$loading.show();
                const thiz = this;

                this.axios
                    .post(`/api/car/bid/${this.sel_car.id}`, { price: this.bid_price }, commonService.get_api_header())
                    .then(response => {
                        if(response.data != "success") {
                            alert("No such car");
                            this.sel_car = null;
                        }
                        loader.hide();
                        this.submit_bid = true;
                        // this.sel_car = {...this.sel_car, Buyers_Quote: this.bid_price};
                        // console.log(this.sel_car);
                        // this.cars.map(car=> {
                        //     if(car.index == this.sel_car.index) car = {...car, Buyers_Quote: this.bid_price};
                        // });
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
            }

        }
    }
</script>

<style lang="stylus" scoped>
@media only screen and (max-width: 992px) {
    .car-detail-popup {
        position: fixed !important;
        left: 0;
        top: 70px;
        right: 0;
        bottom: 0;
        z-index: 1111;
        overflow: auto;
        padding: 0;
        .header-label {
            display:none;
        }
        .btn-close {
            top: 10px;
            right: 10px;
        }
        .detail-content {
            padding: 0 !important;
        }
        .car-title {
            width: calc(100% + 50px);
            text-align: center;
            font-size: 25px;
            margin-left: -25px;
            margin-right: -25px;
            margin-top: 30px;
            border-bottom: 2px solid #31AEED;
            padding: 20px 0;
        }
        .detail-bottom {
            position: initial !important;
            .btn-like {
                position: absolute;
                left: 10px;
                top: 10px;
                width: 55px !important;
                height: 55px !important;
            }
            .action-bar {
                width: 100%;
                .btn {
                    padding-left: 15px !important;
                    padding-right: 15px !important;
                }
            }
        }
        .submit-success {
            .title {
                font-size: 25px !important;
            }
            .btn-done {
                margin-right: 0 !important;
            }

        }
    }
}
.fontColorBlack {
    color: #000 !important;
}
</style>
