<template>
<div id="SiteMenubar"  v-bind:class="{'open-popup': openPopup }">
    <div class="site-menubar-body">
        <ul class="site-menu" v-if="$route.name=='home'">
            <li class="site-menu-item">
                <a href="javascript:;" v-on:click="openCarsFilter()"><span class="mif-filter" v-bind:class="{'text-selected': open_cars_filter}"></span></a>
            </li>
            <li class="site-menu-item">
                <a href="javascript:;" v-on:click="applyLikeFilter()"> <span class="mif-heart"  v-bind:class="{'text-selected': filter_like}"></span> </a>
            </li>
            <li class="site-menu-item">
                <a href="javascript:;" v-on:click="openSavedFilter()" > <span class="mif-floppy-disk" v-bind:class="{'text-selected': open_saved_filter}"></span> </a>
            </li>
        </ul>
        <ul class="site-menu" v-if="$route.name=='bids'">
            <li class="site-menu-item">
                <a href="javascript:;" v-on:click="openBidsFilter()"><span class="mif-filter" v-bind:class="{'text-selected': open_bids_filter}"></span></a>
            </li>
        </ul>
        <ul class="site-menu" v-if="$route.name=='schedulings'">
            <li class="site-menu-item">
                <a href="javascript:;" v-on:click="openSchedulingsFilter()"><span class="mif-filter" v-bind:class="{'text-selected': open_schedulings_filter}"></span></a>
            </li>
        </ul>
        <ul class="site-menu" v-if="$route.name=='payments'">
            <li class="site-menu-item">
                <a href="javascript:;" v-on:click="openPaymentsFilter()"><span class="mif-filter" v-bind:class="{'text-selected': open_payments_filter}"></span></a>
            </li>
        </ul>
    </div>
    <div class="site-menubar-filter">
        <a href="javascript:;" class="btn-close-filter" v-on:click="open_cars_filter = open_saved_filter = open_bids_filter = open_schedulings_filter = open_payments_filter = false">
            <span class="mif-cross-light"></span>
        </a>
        <div class="all-cars-filter" v-if="open_cars_filter && !open_filter_save_step">
            <div class="filter-item miles-input">
                <label for="" class="filter-label">{{filter_labels.distance}}</label>
                <input type="number" class="" placeholder="Type number of miles" v-model="car_filter.distance">
            </div>
            <div class="filter-item miles-input">
                <label for="" class="filter-label">{{filter_labels.Miles}}</label>
                <input type="number" class="" placeholder="Type number of maximum miles"  v-model="car_filter.Miles">
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Does_the_Vehicle_Run_and_Drive}}</label>
                <div class="btn-action-bar">
                    <button class="btn btn-action btn-action-off" v-bind:class="{'btn-primary': car_filter.Does_the_Vehicle_Run_and_Drive =='', 'btn-light': car_filter.Does_the_Vehicle_Run_and_Drive !=''}"  v-on:click="car_filter.Does_the_Vehicle_Run_and_Drive=''">OFF</button>
                    <button class="btn btn-action btn-action-runs" v-bind:class="{'btn-primary': car_filter.Does_the_Vehicle_Run_and_Drive =='Only Starts', 'btn-light': car_filter.Does_the_Vehicle_Run_and_Drive !='Only Starts'}" v-on:click="car_filter.Does_the_Vehicle_Run_and_Drive='Only Starts'">RUNS</button>
                    <button class="btn btn-action btn-action-drives" v-bind:class="{'btn-primary': car_filter.Does_the_Vehicle_Run_and_Drive =='Runs and Drives', 'btn-light': car_filter.Does_the_Vehicle_Run_and_Drive !='Runs and Drives'}" v-on:click="car_filter.Does_the_Vehicle_Run_and_Drive='Runs and Drives'">DRIVERS</button>
                </div>
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.buyers_quote}}</label>
                <input type="number" placeholder="Type in the maximum you're willing to pay" v-model="car_filter.buyers_quote">
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Any_Missing_Body_Panels_Interior_or_Engine_Parts}}</label>
                <select name="" id="" v-model="car_filter.Any_Missing_Body_Panels_Interior_or_Engine_Parts">
                    <option value="">Select options you would accept</option>
                    <option value="No">No</option>
                    <option value="Refer To What's Wrong With It?">Refer To What's Wrong With It?</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Do_they_have_a_Title}}</label>
                <div class="btn-action-bar">
                    <button class="btn btn-action" v-bind:class="{'btn-primary': car_filter.Do_they_have_a_Title =='', 'btn-light': car_filter.Do_they_have_a_Title !=''}"  v-on:click="car_filter.Do_they_have_a_Title=''">OFF</button>
                    <button class="btn btn-action" v-bind:class="{'btn-primary': car_filter.Do_they_have_a_Title =='Yes', 'btn-light': car_filter.Do_they_have_a_Title !='Yes'}" v-on:click="car_filter.Do_they_have_a_Title='Yes'">YES</button>
                    <button class="btn btn-action" v-bind:class="{'btn-primary': car_filter.Do_they_have_a_Title =='No', 'btn-light': car_filter.Do_they_have_a_Title !='No'}" v-on:click="car_filter.Do_they_have_a_Title='No'">NO</button>
                </div>

            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Fire_or_Flood_Damage}}</label>
                <select name="" id="" v-model="car_filter.Fire_or_Flood_Damage">
                    <option value="">Select options you would accept</option>
                    <option v-for="(type, i) in damage_types" :key="i" :value="type">{{type}}</option>
                </select>
            </div>

            <div class="filter-buttons">
                <button class="btn btn-save" v-on:click="openSaveStep()">SAVE SEARCH</button>
                <button class="btn btn-apply" v-on:click="applyCarsFilter()">APPLY</button>
            </div>
        </div>
        <div class="filter-save-step" v-if="open_cars_filter && open_filter_save_step">
            <div class="filter-title-group">
                <div class="ask-label">Are you sure you want to save this search?</div>
                <input type="text" placeholder="Enter a name for this search" v-model="save_filter_title">
            </div>
            <div class="buttons-group">
                <button class="btn btn-light btn-save" v-on:click="open_filter_save_step = false">CANCEL</button>
                <button class="btn btn-primary btn-apply" v-on:click="saveSearch()">SAVE</button>
            </div>
        </div>
        <div class="saved-filters" v-if="open_saved_filter">
            <div class="filter-item" v-for="filter in saved_filters" :key="filter.id">
                <div class="content">
                    <div class="title">{{filter.title}}</div>
                    <div class="action">
                        <a href="javascript:;" v-on:click="editFilter(filter)"> <span class="mif-pencil"></span> </a>
                        <a href="javascript:;" v-on:click="deleteFilter(filter)"> <span class="mif-cancel text-danger"></span> </a>
                    </div>
                </div>
                <div class="filter-detail">{{getFilterString(filter.filter)}}</div>
                <div class="text-right">
                    <button class="btn btn-primary btn-sm" v-on:click="applySavedFilter(filter)" >APPLY</button>
                </div>
            </div>
        </div>
        <div class="bids-filter" v-if="open_bids_filter">
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.status}}</label>
                <select name="" id="" v-model="bid_filter.status">
                    <option value="">All</option>
                    <option value="Won">Won</option>
                    <option value="Active">Active</option>
                </select>
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Reference_Number}}</label>
                <input type="number" :placeholder="'Type ' + filter_labels.Reference_Number" v-model="bid_filter.Reference_Number">
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Year}}</label>
                <input type="number" :placeholder="'Type ' + filter_labels.Year" v-model="bid_filter.Year">
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Make}}</label>
                <input type="text" :placeholder="'Type ' + filter_labels.Make" v-model="bid_filter.Make">
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Model}}</label>
                <input type="text" :placeholder="'Type ' + filter_labels.Model" v-model="bid_filter.Model">
            </div>

            <div class="filter-buttons">
                <button class="btn btn-light btn-save" v-on:click="open_bids_filter=false">CANCEL</button>
                <button class="btn btn-primary btn-apply" v-on:click="applyBidsFilter()">APPLY</button>
            </div>
        </div>
        <div class="schedulings-filter" v-if="open_schedulings_filter">
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.status}}</label>
                <select name="" id="" v-model="schedulings_filter.status">
                    <option value="">All</option>
                    <option value="Unscheduled">Unscheduled</option>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Picked-Up">Picked-Up</option>
                </select>
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Reference_Number}}</label>
                <input type="number" :placeholder="'Type ' + filter_labels.Reference_Number" v-model="schedulings_filter.Reference_Number">
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Year}}</label>
                <input type="number" :placeholder="'Type ' + filter_labels.Year" v-model="schedulings_filter.Year">
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Make}}</label>
                <input type="text" :placeholder="'Type ' + filter_labels.Make" v-model="schedulings_filter.Make">
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Model}}</label>
                <input type="text" :placeholder="'Type ' + filter_labels.Model" v-model="schedulings_filter.Model">
            </div>

            <div class="filter-buttons">
                <button class="btn btn-light btn-save" v-on:click="open_schedulings_filter=false">CANCEL</button>
                <button class="btn btn-primary btn-apply" v-on:click="applySchedulingsFilter()">APPLY</button>
            </div>
        </div>
        <div class="payments-filter" v-if="open_payments_filter">
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.status}}</label>
                <select name="" id="" v-model="payment_filter.status">
                    <option value="">All</option>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Overdue">Overdue</option>
                </select>
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Reference_Number}}</label>
                <input type="number" :placeholder="'Type ' + filter_labels.Reference_Number" v-model="payment_filter.Reference_Number">
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Year}}</label>
                <input type="number" :placeholder="'Type ' + filter_labels.Year" v-model="payment_filter.Year">
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Make}}</label>
                <input type="text" :placeholder="'Type ' + filter_labels.Make" v-model="payment_filter.Make">
            </div>
            <div class="filter-item">
                <label for="" class="filter-label">{{filter_labels.Model}}</label>
                <input type="text" :placeholder="'Type ' + filter_labels.Model" v-model="payment_filter.Model">
            </div>

            <div class="filter-buttons">
                <button class="btn btn-light btn-save" v-on:click="open_payments_filter=false">CANCEL</button>
                <button class="btn btn-primary btn-apply" v-on:click="applyPaymentsFilter()">APPLY</button>
            </div>
        </div>
    </div>
    <div class="popup-wrapper">

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
                open_cars_filter: false,
                open_filter_save_step:false,
                open_saved_filter:false,
                open_bids_filter:false,
                open_schedulings_filter:false,
                open_payments_filter:false,

                save_filter_title:'',
                saved_filters:[],
                filter_like: false,

                car_filter: {
                    distance: '',
                    Miles: '',
                    Does_the_Vehicle_Run_and_Drive: '',
                    buyers_quote: '',
                    Any_Missing_Body_Panels_Interior_or_Engine_Parts: '',
                    Do_they_have_a_Title: '',
                    Fire_or_Flood_Damage: '',
                },
                bid_filter: {
                    status: '',
                    Reference_Number: '',
                    Year: '',
                    Make: '',
                    Model: ''
                },
                schedulings_filter: {
                    status: '',
                    Reference_Number: '',
                    Year: '',
                    Make: '',
                    Model: ''
                },
                payment_filter: {
                    status: '',
                    Reference_Number: '',
                    Year: '',
                    Make: '',
                    Model: ''
                },
                filter_labels: {
                    distance: 'Distance from My Location',
                    Miles: 'Mileage Maximum',
                    Does_the_Vehicle_Run_and_Drive: 'Runs / Drivers',
                    buyers_quote: 'Maximum Offer',
                    Any_Missing_Body_Panels_Interior_or_Engine_Parts: 'Body Damage',
                    Do_they_have_a_Title: 'Title',
                    Fire_or_Flood_Damage: 'Fire / Flood / Hail',

                    //  bids
                    status: 'Status',
                    Reference_Number: 'Ref #',
                    Year: 'Year',
                    Make: 'Make',
                    Model: 'Model',
                },
                damage_types: [
                    "Engine Damage", "Exterior Burn", "Flood", "Fresh Water", "Front & Rear", "Front End", "Hail", "Interior Burn", "Left & Right Side", "Left Front", "Left Rear", "Left Side", "Mechanical", "None", "Rear", "Repossession", "Right Front", "Right Rear", "Right Side", "Rollover", "Roof", "Saltwater", "Storm Damage", "Stripped", "Suspension", "Theft", "Total Burn", "Transmission Damage", "Undercarriage", "Unknown"
                ]
            }
        },
        watch: {
            $route (to_route, from_route){
                this.open_cars_filter = false;
                this.open_filter_save_step = false;
                this.open_saved_filter = false;
                this.open_bids_filter = false;
                this.open_schedulings_filter = false;
                this.open_payments_filter = false;
                this.resetFitlerParams();
            }
        },
        created() {
            const thiz = this;
            EventBus.$on('reset-car-filter', function() {
                thiz.resetFitlerParams();
                thiz.applyCarsFilter();
            })
            EventBus.$on('reset-bid-filter', function() {
                thiz.resetFitlerParams();
                thiz.applyBidsFilter();
            })
            EventBus.$on('reset-scheduling-filter', function() {
                thiz.resetFitlerParams();
                thiz.applySchedulingsFilter();
            })
            EventBus.$on('reset-payment-filter', function() {
                thiz.resetFitlerParams();
                thiz.applyPaymentsFilter();
            })
        },
        computed: {
            openPopup() {
                return this.open_cars_filter || this.open_saved_filter || this.open_bids_filter || this.open_schedulings_filter || this.open_payments_filter;
            }
        },
        methods: {
            resetFitlerParams() {
                this.car_filter =  {
                    distance: '',
                    Miles: '',
                    Does_the_Vehicle_Run_and_Drive: '',
                    buyers_quote: '',
                    Any_Missing_Body_Panels_Interior_or_Engine_Parts: '',
                    Do_they_have_a_Title: '',
                    Fire_or_Flood_Damage: '',
                };
                this.bid_filter =  {
                    status: '',
                    Reference_Number: '',
                    Year: '',
                    Make: '',
                    Model: ''
                };
                this.schedulings_filter = {
                    status: '',
                    Reference_Number: '',
                    Year: '',
                    Make: '',
                    Model: ''
                };
                this.payment_filter = {
                    status: '',
                    Reference_Number: '',
                    Year: '',
                    Make: '',
                    Model: ''
                };
            },
            get_filter_param(filter) {
                const params = {};
                for (const key in filter) {
                    filter[key] && (params[key] = filter[key]);
                }
                return params;
            },
            getFilterString(filter) {
                let res = [];
                for (const key in filter) {
                    if (filter[key]) {
                        res.push(this.filter_labels[key] + ': ' + filter[key]);
                    }
                }
                return res.join(', ');
            },
            openCarsFilter() {
                this.open_cars_filter = !this.open_cars_filter;

                if (this.open_cars_filter) {
                    this.open_filter_save_step = false;
                    this.open_saved_filter = false;
                }
            },
            checkPopupOpen() {
                this.$emit('checkPopupOpen', this.openPopup);
            },
            openSaveStep() {
                const params = this.get_filter_param(this.car_filter);
                if (Object.keys(params).length == 0) return alert('Search parameters are empty.');
                this.open_filter_save_step = true;
                this.save_filter_title = '';

            },
            saveSearch() {
                var title = this.save_filter_title;
                if (!title) return alert('Please input the search title');

                let loader = this.$loading.show();
                this.axios
                    .post(`/api/filters`, {title, filter: JSON.stringify(this.get_filter_param(this.car_filter))}, commonService.get_api_header())
                    .then(response => {
                        this.open_filter_save_step = false;
                        loader.hide();
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
            openSavedFilter() {
                this.open_saved_filter = !this.open_saved_filter;
                if (this.open_saved_filter) {
                    this.open_cars_filter = false;
                    let loader = this.$loading.show();
                    this.saved_filters = [];
                    this.axios
                        .get(`/api/filters`, commonService.get_api_header())
                        .then(response => {
                            loader.hide();
                            if (!response.data.success) return alert('get saved search error');
                            var filters = response.data.filters;
                            filters.map(filter=>filter.filter = JSON.parse(filter.filter));
                            this.saved_filters = filters;

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

            },
            editFilter(filter) {
                this.car_filter = filter.filter;
                this.openCarsFilter();

            },
            deleteFilter(filter) {
                if (!confirm("Are you sure to delete the search?")) return;
                let loader = this.$loading.show();
                const delete_id = filter.id;
                this.axios
                    .delete(`/api/filters/` + delete_id, commonService.get_api_header())
                    .then(response => {
                        loader.hide();
                        if (!response.data.success) return alert('delete search error');
                        this.saved_filters = this.saved_filters.filter(one => delete_id != one.id );
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
            applySavedFilter(filter) {
                this.car_filter = filter.filter;
                this.open_saved_filter = false;
                this.applyCarsFilter();

            },
            applyCarsFilter() {
                const params = this.get_filter_param(this.car_filter);
                params['filter_string'] = this.getFilterString(params);
                params['filter_like'] = this.filter_like;

                EventBus.$emit('update-car-filter', params);
                this.open_cars_filter = false;

            },
            applyLikeFilter() {
                this.filter_like = !this.filter_like;
                this.open_cars_filter = false;
                this.open_saved_filter = false;
                const params = this.get_filter_param(this.car_filter);
                params['filter_string'] = this.getFilterString(params);
                params['filter_like'] = this.filter_like;
                EventBus.$emit('update-car-filter', params);

            },
            // bids
            openBidsFilter() {
                this.open_bids_filter = !this.open_bids_filter;

            },
            applyBidsFilter() {
                this.open_bids_filter = false;
                const params = this.get_filter_param(this.bid_filter);
                params['filter_string'] = this.getFilterString(params);
                EventBus.$emit('update-bid-filter', params);

            },
            // scheduling
            openSchedulingsFilter() {
                this.open_schedulings_filter = !this.open_schedulings_filter;

            },
            applySchedulingsFilter() {
                this.open_schedulings_filter = false;
                const params = this.get_filter_param(this.schedulings_filter);
                params['filter_string'] = this.getFilterString(params);
                EventBus.$emit('update-schedulings-filter', params);

            },
            // payments
            openPaymentsFilter() {
                this.open_payments_filter = !this.open_payments_filter;

            },
            applyPaymentsFilter() {
                this.open_payments_filter = false;
                const params = this.get_filter_param(this.payment_filter);
                params['filter_string'] = this.getFilterString(params);
                EventBus.$emit('update-payments-filter', params);

            },
        }
    }
</script>
