<template>
    <div id="SchedulingPage" class="page-content-detail">
        <div class="page-content-block-wrapper">
            <div class="page-header">
                <span>Scheduling Pick-Ups</span>
                <p class="header-summary">Manage the scheduling and pick-up of vehicles you've purchased.</p>
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
                            <input type="checkbox" v-model="checked_all" v-on:change="checkAll(true)" v-show="existingAnycar">&nbsp;Status
                        </div>
                        <div class="title">Ref#</div>
                        <div class="title">Year</div>
                        <div class="title">Make</div>
                        <div class="title">Model</div>
                        <div class="title">Pay Seller</div>
                    </div>
                    <div class="car-body">
                        <div class="car-item" v-for="car in cars" :key="car.id" v-bind:class="{'selected': sel_car && car.id == sel_car.id}" @click="showDetail(car)">
                            <div class="item-data">
                                <input type="checkbox" style="margin-top: 4px;" v-model="car.is_checked" v-on:change="checkAll()" :disabled="car.Stage == 'Picked Up'">&nbsp;
                                <div v-if="car.Stage == 'Dispatched'" class="status-active uppercase">Unscheduled </div>
                                <div v-if="car.Stage == scheduled_string" class="status-won uppercase">Scheduled </div>
                                <div v-if="car.Stage == pickedup_string" class="status-fail uppercase">Picked-Up </div>
                            </div>
                            <div class="item-data">{{ car.Reference_Number }}</div>
                            <div class="item-data">{{ car.Year }}</div>
                            <div class="item-data">{{ car.Make }}</div>
                            <div class="item-data">{{ car.Model }}</div>
                            <div class="item-data">{{ toCurrency(car.CUSTOMERS_QUOTE) }}</div>
                            <!-- <a href="javascript:;" class="text-center action-go" v-on:click="showDetail(car)">
                                <span class="mif-arrow-right"></span>
                            </a> -->
                            <div class="mobile-item item-data"  v-on:click="showDetail(car)">
                                <div class="item-content">
                                    <div class="font-weight-bold">{{car.Reference_Number}} &nbsp;&nbsp;{{car.Year}} {{car.Make}} {{car.Model}}</div>
                                    <div>{{car.City}} &nbsp;&nbsp; {{car.Zip_Code}}</div>
                                    <div style="display:flex;justify-content:space-between;">
                                        <div class="text-blue">{{car.CUSTOMERS_QUOTE}}</div>
                                        <div v-if="car.Stage == unscheduled_string" class="status-active">Unscheduled </div>
                                        <div v-if="car.Stage == scheduled_string" class="status-won">Scheduled </div>
                                        <div v-if="car.Stage == pickedup_string" class="status-fail">Picked-Up </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="car-right-content col-md-4" v-if="!is_mobile_view || (is_mobile_view && sel_car)">
                    <div class="title-header">
                        <a href="javascript:;" class="btn-close-car-detail" :class="{opacityTo1: (sel_car&&sel_car.Stage!=pickedup_string&&!reschedulable&&!pickable&&(!cancellable&&!reschedulable)&&!pickup_count)}" v-on:click="cancellable=!cancellable">
                            <span class="mif-cancel"></span> Cancel Pickup
                        </a>
                    </div>
                    <div class="car-detail">

                        <div class="selcar-content" v-if="pickup_count">
                            <div class="title padding-0 margin-top-5 go-back" v-on:click="statusInitialise()">
                                <span class="mif-arrow-left"></span>
                                <div class="narrate-label">No thanks, take me back.</div>
                            </div>
                            <div class="submit-content">
                                <div class="cancel-header">You have selected for pickup:</div>
                                <div class="cancel-content">
                                    {{pickup_count}} Vehicles
                                </div>
                            </div>
                            <div class="action-bar">
                                <button class="btn btn-primary action-button float-right" @click="massPickup()">PICKED UP</button>
                            </div>
                        </div>

                        <div class="empty-content" v-if="!sel_car">
                            Click a list item to view details
                        </div>
                        <div class="submit-content" v-if="sel_car && submit_pickup">
                            <div class="title font-weight-bold text-center">
                                <div class="text-blue">Your Pickup has been scheduled for:</div>
                                <div class="">{{pickup_date.date.toLocaleDateString()}}</div>
                            </div>
                            <div class="bid-image"><img src="/img/scheduling_success.png" alt=""></div>
                            <div class="action-bar text-right">
                                <button class="btn btn-primary action-button" v-on:click="sel_car=null">DONE</button>
                            </div>
                        </div>

                        <div class="selcar-content" v-if="sel_car && cancellable">
                            <div class="title padding-0 margin-top-5 go-back" v-on:click="cancellable=!cancellable">
                                <span class="mif-arrow-left"></span>
                                <div class="narrate-label">No thanks, take me back.</div>
                            </div>
                            <div class="submit-content">
                                <div class="cancel-header">Are you sure you'd like to cancel?</div>
                                <div class="cancel-content">This will remove the vehicle from your purchases. You will need to contact dispatch@junkcarboys.com for any further correspondence.</div>
                            </div>
                            <div class="action-bar">
                                <button class="btn btn-primary action-button float-right" @click="submitCancel()">YES, CANCEL</button>
                            </div>
                        </div>



                        <div class="selcar-content" v-if="sel_car && reschedulable">
                            <div class="title padding-0 margin-top-5 go-back" v-on:click="reschedulable=!reschedulable">
                                <span class="mif-arrow-left"></span>
                                <div class="narrate-label">No thanks, take me back.</div>
                            </div>
                            <div class="submit-content">
                                <div class="cancel-header">You are about to reschedule for:</div>
                                <div class="cancel-content">
                                    Date: {{date}}<br/>Time: {{time}}
                                </div>
                            </div>
                            <div class="action-bar">
                                <button class="btn btn-primary action-button float-right" @click="submitSchedule()">CONFIRM</button>
                            </div>
                        </div>

                        <div class="selcar-content" v-if="sel_car && pickable">
                            <div class="title padding-0 margin-top-5 go-back" v-on:click="pickable=!pickable">
                                <span class="mif-arrow-left"></span>
                                <div class="narrate-label">No thanks, take me back.</div>
                            </div>
                            <div class="submit-content">
                                <div class="cancel-header">Are you confirming that you've picked up this vehicle?</div>
                            </div>
                            <div class="action-bar">
                                <button class="btn btn-primary action-button float-right" @click="submitPickedUp()">CONFIRM</button>
                            </div>
                        </div>

                        <div class="selcar-content" :class="{'background-grey': !editable}" v-if="sel_car && !submit_pickup && !pickup_count">
                            <div class="title padding-0 margin-top-5">{{sel_car.Year}}&nbsp;&nbsp;{{sel_car.Make}}&nbsp;&nbsp;{{sel_car.Model}}</div>
                            <hr class="margin-side-minus-30"/>
                            <div class="selcar-detail row">
                                <div class="col-md-6 field-item margin-0">
                                    <div class="item-label">Pick-up Location</div>
                                    <div class="item-value car-value" v-if="sel_car.Street">{{sel_car.Street}}</div>
                                    <div class="item-value car-value">{{sel_car.City}} {{sel_car.State}}</div>
                                    <div class="item-value car-value">{{sel_car.Zip_Code}}</div>
                                </div>
                                <div class="col-md-6 field-item margin-0">
                                    <div class="item-label">Vehicle Owner</div>
                                    <div class="item-value car-value">{{sel_car.Deal_Name}}</div>
                                    <div class="item-value car-value">{{sel_car.Phone | phoneNumberFormat}}</div>
                                    <div class="item-value car-value">{{sel_car.Alt_Phone | phoneNumberFormat}}</div>
                                </div>
                            </div>
                            <hr class="margin-side-minus-30"/>
                            <div class="row">
                                <div class="col-md-6 field-item margin-0">
                                    <div class="item-label">Pick-Up Date:</div>
                                    <input type="date" v-model="date" name="trip-start"
                                        class="item-value car-input-value" ref="datepicker" placeholder="Click to select a date"
                                        min="2018-01-01" :disabled="!editable">
                                </div>
                                <div class="col-md-6 field-item margin-0">
                                    <div class="item-label">Pick-Up Time:</div>
                                    <input type="time" class="item-value car-input-value" v-model="time"  min="12:00" max="18:00"  placeholder="Click to select a time" :disabled="!editable">
                                </div>
                                <div class="col-md-12 field-item margin-top-20">
                                    <div class="item-label">Notes</div>
                                    <input type="text" class="item-value car-input-value" placeholder="Click to enter notes" v-model="sel_car.Scheduled_Note" :disabled="!editable">
                                </div>

                            </div>
                            <div class="action-bar" v-if="sel_car.Stage== unscheduled_string">
                                <button class="action-button none-styled-button float-left" v-on:click="pickable=true">I picked-up already.</button>
                                <button class="btn btn-primary action-button float-right" @click="submitSchedule()">SCHEDULE</button>
                            </div>
                            <div class="action-bar" v-if="sel_car.Stage== scheduled_string && !editable">
                                <button class="action-button none-styled-button float-left background-grey" @click="enableEdit">I need to reschedule.</button>
                                <button class="btn btn-primary action-button float-right" @click="pickable=true">PICKED UP</button>
                            </div>
                            <div class="action-bar" v-if="sel_car.Stage== scheduled_string && editable">
                                <button class="action-button none-styled-button float-left background-grey" @click="disableEdit">Cancel my edits</button>
                                <button class="btn btn-primary action-button float-right" @click="reSchedule">SAVE</button>
                            </div>
                            <div class="action-bar" v-if="sel_car.Stage== pickedup_string">
                                <button class="btn btn-primary action-button float-right" @click="gotoPay()">PAY</button>
                            </div>
                        </div>

                        <!-- <div class="selcar-content" :class="{'background-grey': sel_car.Stage == scheduled_string || sel_car.Stage == pickedup_string}" v-if="sel_car && !submit_pickup">
                            <div class="title padding-0">{{sel_car.Year}}&nbsp;&nbsp;{{sel_car.Make}}&nbsp;&nbsp;{{sel_car.Model}}</div>
                            <div class="selcar-detail row">
                                <div class="calendar-wrapper">
                                    <div class="car-calendar" style="margin:auto;">
                                        <v-calendar :available-dates='date_range' :attributes="attributes" mode="dateTime" @dayclick="onDayClick" disabled is-expanded/>

                                    </div>
                                    <div class="car-timepicker" style="margin:auto;">
                                        <VueTimepicker :minute-interval="10" v-model="time" :disabled="!editable"/>
                                    </div>

                                </div>
                                <div class="w-100 schedule-content" v-if="sel_car.Stage == unscheduled_string">
                                    <div class="col-md-6 field-item">
                                        <div class="item-label">Scheduled Pick-up Date</div>
                                        <div class="item-value">{{pickup_date ? pickup_date.date.toLocaleDateString() + " ( " + time + " )" : 'MM / DD / YYYY  HH : mm'}}&nbsp;</div>
                                    </div>
                                    <div class="col-md-6 field-item">
                                        <div class="item-label">Notes</div>
                                        <input type="text" class="item-value" placeholder="Add notes here" v-model="schedule_note">
                                    </div>
                                </div>
                                <div class="w-100 schedule-content" v-else>
                                    <div class="col-md-6 field-item">
                                        <div class="item-label">Scheduled Pick-up Date</div>
                                        <div class="item-value">{{pickup_date ? pickup_date.date.toLocaleDateString() + " ( " + time + " )" : 'MM / DD / YYYY'}}&nbsp;</div>
                                    </div>
                                    <div class="col-md-12 field-item">

                                    </div>
                                </div>
                            </div>
                            <div class="action-bar" v-if="sel_car.Stage== unscheduled_string">
                                <button class="btn btn-primary action-button float-left" v-on:click="submitSchedule()">SCHEDULE</button>
                                <button class="btn action-button btn-danger" v-on:click="submitCancel()">CANCEL</button>
                                <button class="btn btn-primary action-button float-right" @click="submitPickedUp()">PICKED UP</button>
                            </div>
                            <div class="action-bar" v-if="sel_car.Stage== scheduled_string">
                                <button class="btn btn-primary action-button float-left background-grey" v-show="!editable" @click="enableEdit()">Reschedule</button>
                                <button class="btn action-button btn-danger" v-on:click="submitCancel()">CANCEL</button>
                                <button class="btn btn-primary action-button float-right" @click="submitPickedUp()">PICKED UP</button>
                            </div>
                            <div class="action-bar" v-if="sel_car.Stage== scheduled_string">
                                <button class="btn btn-primary action-button float-left background-grey" v-show="!editable" @click="enableEdit()">Reschedule</button>
                                <button class="btn action-button btn-danger" v-on:click="submitCancel()">CANCEL</button>
                                <button class="btn btn-primary action-button float-right" @click="submitPickedUp()">PICKED UP</button>
                            </div>
                            <div class="action-bar" v-if="sel_car.Stage== pickedup_string">
                                <button class="btn btn-primary action-button float-right" @click="gotoPay()">PAY</button>
                            </div>
                        </div> -->
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
                        <span>{{ cars | unscheduledAmount }} </span> Unscheduled Cars
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
        components:{
            VueTimepicker: () => import ('vue2-timepicker')
        },
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
                pickup_date: null,
                schedule_note: '',
                is_mobile_view: window.innerWidth <= 992,
                editable: false,
                cancellable: false,
                reschedulable: false,
                pickup_count: 0,
                pickable: false,
                date_range: null,
                calendar_disabled_data: { start: null, end: new Date(0, 0, 0) },
                calendar_enabled_data: { start: new Date(0, 0, 0), end: null },
                unscheduled_string: "Dispatched",
                scheduled_string: "Scheduled For Pick Up",
                pickedup_string: "Picked Up",
                defaultTimezoneString: ":00-08:00",
                countPerPageArray: [8, 9, 10],
                date: "",
                time: '09:00',
                temp_date: "",
                temp_time: "",
                checked_all: false,
            }
        },
        computed: {
            attributes() {
                if (!this.pickup_date) return [];
                return [{
                        // highlight: true,
                        key: 'today',
                        dot: true,
                        dates: this.pickup_date.date,
                }]
            },
            existingAnycar() {
                var pickup_count = 0;
                this.cars.map(car => {
                    if(car.Stage != "Picked Up") pickup_count++;
                })
                if(pickup_count) return true;
                else return false;
            }
        },
        filters: {
            unscheduledAmount: function(arr) {
                var unscheduled_string = "Dispatched";
                var un_arr = arr.filter(car=> car.Stage== unscheduled_string)
                return un_arr.length;
            },
            phoneNumberFormat: function(phone) {
                if(!phone || phone == "") return null;
                var phoneNumber = String(phone);
                phoneNumber = [phoneNumber.slice(0, 3), '-', phoneNumber.slice(3)].join('');
                phoneNumber = [phoneNumber.slice(0, 7), '-', phoneNumber.slice(7)].join('');
                return phoneNumber;
            }
        },
        created() {
            const thiz = this;
            // this.records_per_page = this.countPerPageArray[0];
            EventBus.$on('update-schedulings-filter', function(filter_param) {
                thiz.filter_param = filter_param;
                thiz.filter_string = thiz.filter_param['filter_string'];
                delete thiz.filter_param['filter_string'];
                thiz.refreshPage(1);
            });
        },
        beforeDestroy () {
            EventBus.$off('update-schedulings-filter');
        },
        mounted() {
            this.refreshPage(1);
        },
        methods: {
            changeItemCount() {
                this.refreshPage();
            },
            statusInitialise() {
                this.cars.forEach(one => {if(one.Stage != "Picked Up") one.is_checked = false});
                this.pickup_count = 0;
                this.sel_car = null;
                this.cancellable = false;
                this.editable = false;
                this.reschedulable = false;
                this.pickable = false;
                this.checked_all = false;
            },
            refreshPage(page) {
                if (!page) page = this.page;
                if (page < 1 || page > parseInt(this.total/this.records_per_page) + 1) return;
                this.page = page;

                this.cars = [];
                for (let index = 0; index < this.records_per_page; index++) {
                    this.cars.push({index})
                }

                let url = '/api/cars?page_type=schedulings&page=' + this.page+'&records_per_page='+this.records_per_page;
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
                    this.cars = res_data.data;
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

                this.sel_car = null;
                this.pickable = false;
                this.cancellable = false;
                this.reschedulable = false;
            },
            resetFilter() {
                EventBus.$emit('reset-scheduling-filter');
            },
            checkAll(force = false) {
                this.sel_car = null;
                if (force) {
                    this.cars.forEach(one => {if(one.Stage != "Picked Up") one.is_checked = this.checked_all})
                } else {
                    this.checked_all = this.cars.filter(one => one.Stage != "Picked Up").length == this.cars.filter(one => one.is_checked).length;

                }
                var pickup_count = 0;
                this.cars.map(car => {
                    if(car.is_checked) pickup_count++;
                })
                this.pickup_count = pickup_count;
                console.log(pickup_count);
            },
            showDetail(car) {
                setTimeout(() => {
                    var pickup_count = 0;
                    this.cars.map(car => {
                        if(car.is_checked) pickup_count++;
                    })

                    this.sel_car = null;
                    if(pickup_count) {
                        this.pickup_count = pickup_count;
                        return;
                    }
                }, 200);
                setTimeout(() => {
                    this.sel_car = car;
                    this.submit_pickup = false;
                    this.pickup_date = null;
                    this.editable = false;
                    this.cancellable = false;
                    this.reschedulable = false;
                    this.pickable = false;
                    this.date_range = this.calendar_enabled_data;
                    if (this.sel_car.Stage == this.scheduled_string || this.sel_car.Stage == this.pickedup_string) {
                        // let date = new Date(this.sel_car.Scheduled_Time);
                        // this.pickup_date = {
                        //     id: this.sel_car.Scheduled_Time,
                        //     date,
                        // }
                        var dateTime = car.Scheduled_Time;
                        var dTArr = dateTime.split('T');
                        this.date = dTArr[0];
                        var time = dTArr[1];
                        console.log(dTArr);
                        if(time && time != "") {
                            var time_arr = time.split(':');
                            this.time = time_arr[0] + ":" + time_arr[1];
                        }

                        // this.date_range = this.calendar_disabled_data;
                    }
                    else {
                        this.editable = true;
                        this.date = "";
                        this.time = "";
                        this.sel_car.Scheduled_Note = "";
                    }
                    this.schedule_note = this.sel_car.Scheduled_Note;
                }, 500);
            },
            toCurrency: function(value) {
                if(!value) return "";
                var formatter = new Intl.NumberFormat("en-US", {
                    style: 'currency',
                    currency: "USD"
                });
                return formatter.format(value).replace("$", "$ ");
            },
            reSchedule() {
                if(this.date == "" || this.time == "") {
                    alert('Please choose date and time!');
                    return;
                }

                this.reschedulable = true;
            },
            submitSchedule() {
                // if (!this.pickup_date) return alert('Please select a schedule date');
                if (this.date == "" || this.time == "") return alert('Please choose date and time!');
                let loader = this.$loading.show();
                var self = this;
                this.axios
                    .post(`/api/car/schedules/` + this.sel_car.id, {Scheduled_Time: this.date+"T"+this.time+this.defaultTimezoneString, Scheduled_Note: this.sel_car.Scheduled_Note}, commonService.get_api_header())
                    .then(response => {
                        console.log(response)
                        loader.hide();
                        this.submit_pickup = true;
                        this.sel_car.Scheduled_Time = this.date+"T"+this.time+this.defaultTimezoneString;
                        this.sel_car.Scheduled_Note = this.schedule_note;
                        this.sel_car.Stage = this.scheduled_string;
                        this.sel_car = {...this.sel_car};
                        this.reschedulable = false;
                        this.editable = false;
                        this.refreshPage();

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
            submitPickedUp() {
                let loader = this.$loading.show();
                var scheduled_time = "";
                if(this.pickup_date && this.pickup_date.id) scheduled_time = this.date+"T"+this.time+this.defaultTimezoneString;
                this.axios
                    .post(`/api/car/pick/` + this.sel_car.id, {Scheduled_Time: scheduled_time, Scheduled_Note: this.sel_car.Scheduled_Note}, commonService.get_api_header())
                    .then(response => {
                        console.log(response)
                        loader.hide();

                        if(this.pickup_date) {
                            this.submit_pickup = true;
                            this.sel_car.Scheduled_Time = this.date+"T"+this.time+this.defaultTimezoneString;
                            this.sel_car.Scheduled_Note = this.schedule_note;
                        }

                        this.sel_car.Stage = this.pickedup_string;
                        this.sel_car = {...this.sel_car};
                        this.editable = false;
                        this.refreshPage();
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
            massPickup() {
                let loader = this.$loading.show();
                var arr = [];
                this.cars.map(car => {
                    if(car.is_checked) arr.push(car);
                });

                this.axios
                    .post(`/api/car/pickupMass`, { cars: arr }, commonService.get_api_header())
                    .then(response => {
                        loader.hide();
                        this.statusInitialise();
                        this.refreshPage();
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

            submitCancel() {
                let loader = this.$loading.show();
                this.axios
                    .get(`/api/car/cancel/` + this.sel_car.id, commonService.get_api_header())
                    .then(response => {
                        loader.hide();
                        this.refreshPage();
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
            onDayClick(day) {
                if(this.editable)
                    this.pickup_date = day;
            },
            enableEdit() {
                this.editable = true;
                this.temp_date = this.date;
                this.temp_time = this.time;
                this.date = "";
                this.time = "";
                // this.date_range = this.calendar_enabled_data;
            },
            disableEdit() {
                this.editable = false;
                this.date = this.temp_date;
                this.time = this.temp_time;
                // this.date_range = this.calendar_enabled_data;
            },
            gotoPay() {
                this.$router.push('/payments?id='+this.sel_car.id);
            }
        }
    }
</script>
<style lang="stylus" scoped>

.btn-danger {
    background-color: #e3342f;
}
input[type="date"]::-webkit-calendar-picker-indicator, input[type="time"]::-webkit-calendar-picker-indicator {
    background: transparent;
    bottom: 0;
    color: transparent;
    cursor: pointer;
    height: auto;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: auto;
}
#SchedulingPage {
    .status-won, .status-active, .status-fail {
        margin-left: 0;
    }
}

</style>

