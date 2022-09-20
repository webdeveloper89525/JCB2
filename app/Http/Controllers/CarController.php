<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ZohoSerivce;

use Auth;
use DB;
use App\Models\User;
use App\Models\Car;
use App\Models\LikeBind;
use App\Models\Filter;
use App\Models\Location;
use App\Models\Company;
use GuzzleHttp\Client;
use LikeBinds;
use Psr\Cache;




class CarController extends Controller
{
    private $ZohoBooksAccessToken;
    function __construct() {
        // if (!isset($this->ZohoBooksAccessToken)) {
            $AccessToken = $this->getZohoBooksAccessToken();
            if ($AccessToken !== false) $this->ZohoBooksAccessToken = $AccessToken;
        // }
    }

    private function getZohoBooksAccessToken()
    {
        $client_id = '1000.W5L72VDAFQIN6OB9GK08U2CISBX6AB';
        $client_secret = '4a98ff291c7b55b4996f30cae938133f844f0f60a4';
        $refresh_token = '1000.9e815af4aaba1e4d00463a783dbc808f.c684294e58bba87acba6ca1ea394d8f0';
        try {
            $Request = (new Client())->post('https://accounts.zoho.com/oauth/v2/token', [
                'form_params' => [
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refresh_token
                ]
            ]);
            $JSON = $Request->getBody()->getContents();
            $Response = json_decode($JSON, true);
            return sprintf('Zoho-oauthtoken %s', $Response['access_token']) ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function test() {
        $zohoService = new ZohoSerivce();
        $res = $zohoService->queryTest("Deals")[0]->getKeyValue('id');
        return $res;
    }

    public function sync(Request $request) {
        $id = $request->id;
        $price = $request->price;
        $tow_company = $request->tow_company;
        $stage = $request->stage;
        $car = Car::where('Reference_Number', $id)->first();
        if(!$car) return null;
        $car->Buyers_Quote = $price;
        $car->save();
        return $car;
    }

    public function index(Request $request)
    {

        $page = ($request->page ?: 1) - 1;
        $records_per_page = $request->records_per_page ?: 8;
        $select = [
            'id as index',
            'Year',
            'Make',
            'Model',
            'City',
            'Does_the_Vehicle_Run_and_Drive',
            'Miles',
            'Buyers_Quote',
            'Zip_Code',
            'Reference_Number',
            'Closing_Date',
            'Stage',
        ];
        $where = array();
        $query_zip_arr1 = array();
        $query_zip_arr2 = array();
        $query_zip_arr3 = array();
        $query_zip_arr4 = array();
        // $quey = null;
        if ($request->type == 'like') {
            $likes = LikeBind::get();
            $likesArray = array();
            foreach($likes as $like) {
                $likesArray[] = $like->car_index;
            }
            $select[] = 'Any_Missing_Body_Panels_Interior_or_Engine_Parts';
        }
        if (!$request->page_type) $request->page_type = 'cars';

        if ($request->page_type == 'cars') {
            $stage = 'Given Quote';
            $duration = -3;
            $select[] = 'Do_they_have_a_Title';
            $select[] = 'Airbags_Deployed';
            $select[] = 'Is_There_Any_Body_Damage_Broken_Glass_2';
            $select[] = 'Is_there_any_Body_Damage_Broken_Glass';
            $select[] = 'Is_There_Any_Broken_Glass_Windows_etc';
            $select[] = 'Are_all_the_tires_mounted';
            $select[] = 'Which_tires_are_missing';
            $select[] = 'Are_All_the_Tires_Inflated';
            $select[] = 'Which_ones_are_flat';
            $select[] = 'Fire_or_Flood_Damage';
            $select[] = 'What_Kind_of_Mechanical_Issues_Are_There';
            $select[] = 'Scheduled_Time';
            $select[] = 'Any_Missing_Body_Panels_Interior_or_Engine_Parts';
            // $select[] = 'Scheduled_Notes';

            $query = Car::where('Stage', $stage)
                        ->where('Tow_Company_id', '<>', Auth::user()->zoho_index)
                        ->whereNotNull('Buyers_Quote')
                        ->where('Closing_Date', '>=', date('Y-m-d', strtotime($duration . ' days')));


            $records_per_page = 200;
            if ($request->type == 'like')
                $query = $query->whereIn('id', $likesArray);

            else {
                $cities_query = "select Zip_Code from Deals where (Stage = 'Given Quote') and Closing_Date >= '". date('Y-m-d', strtotime('-3 days')) ."' group by Zip_Code order by id desc limit 200 offset 0";
                $zoho_cars_zip_array = $this->getQueryResult($cities_query)['data'];
                // return json_encode(['res'=>$zoho_cars_zip_array]);
                $distance = 250;
                if ($request->distance) {
                    $distance = $request->distance;
                }
                foreach($zoho_cars_zip_array as $zip) {
                    $zip_code = Location::where('zip_code', $zip['Zip_Code'])->first();
                    if($zip_code) {
                        $distance_zip = intval($this->haversineGreatCircleDistance(Auth::user()->lat, Auth::user()->lng, $zip_code->lat, $zip_code->lng));
                        if($distance_zip <= $distance) {
                            if(count($query_zip_arr1) < 50) {
                                $query_zip_arr1[] = $zip_code->zip_code;
                            }
                            else if(count($query_zip_arr2) < 50) {
                                $query_zip_arr2[] = $zip_code->zip_code;
                            }
                            else if(count($query_zip_arr3) < 50) {
                                $query_zip_arr3[] = $zip_code->zip_code;
                            }
                            else if(count($query_zip_arr4) < 50) {
                                $query_zip_arr4[] = $zip_code->zip_code;
                            }
                        }
                    }
                }
            }

        } elseif ($request->page_type == 'bids') {
            $query = Car::where(function($sub_query) {
                $sub_query->where('Stage', 'Given Quote')->
                            orwhere('Stage', 'Deal Made');
            });

            $query = $query->where('Tow_Company_id', Auth::user()->zoho_index);

            if ($request->status && $request->status == 'Won') {
                $query = $query->where('Stage', 'Deal Made');
            } elseif ($request->status && $request->status == 'Active') {
                $query = $query->where('Stage', 'Given Quote');
            }
        } elseif ($request->page_type == 'schedulings') {
            $select[] = 'Scheduled_Time';
            $select[] = 'Scheduled_Note';
            $select[] = 'CUSTOMERS_QUOTE';
            $select[] = 'Deal_Name';
            $select[] = 'State';
            $select[] = 'Street';
            $select[] = 'Phone';
            $select[] = 'Alt_Phone';

            $query = Car::where(function($sub_query) {
                            $sub_query->where('Stage', 'Picked Up');
                            $sub_query->orwhere('Stage', 'Scheduled For Pick Up');
                            $sub_query->orwhere('Stage', 'Dispatched');
                            return $sub_query;
                        });

            $query = $query->where('Tow_Company_id', Auth::user()->zoho_index)
                            ->whereNotNull('Buyers_Quote');

            if ($request->status == 'Unscheduled') {
                $query = $query->where('Stage', 'Dispatched');
            } elseif ($request->status == 'Scheduled') {
                $query = $query->where('Stage', 'Scheduled For Pick Up');
            } elseif ($request->status == 'Picked-Up') {
                $query = $query->where('Stage', 'Picked Up');
            }

        } elseif ($request->page_type == 'payments') {
            $select[] = 'Profit';
            $query = Car::where(function($sub_query) {
                $sub_query->where('Stage', 'Paid');
                // $sub_query->orwhere('Stage', 'Scheduled For Pick Up');
                $sub_query->orwhere('Stage', 'Picked Up');
                return $sub_query;
            });

            $query = $query->where('Tow_Company_id', Auth::user()->zoho_index)
                            ->whereNotNull('Buyers_Quote');

            if ($request->status == 'Paid') {
                $query = $query->where('Stage', 'Paid');
            } elseif ($request->status == 'Unpaid') {
                $query = $query->where(function($sub_query) {
                    $sub_query->where('Stage', 'Dispatched');
                    $sub_query->orwhere('Stage', 'Scheduled For Pick Up');
                    return $sub_query;
                });
            } elseif ($request->status == 'Overdue') {
                $query = $query->where('Stage', 'Picked Up');
            }
        }


        // $query_4_total = $query->select('count(*) as count')->orderby('id', 'desc');

        // // $total_query = $this->getQueryResult($this->convertQuery($query_4_total));
        // return json_encode(array('res'=>$this->convertQuery($query_4_total)));


        // if ($request->Miles) $query = $query->where('Miles', '<', $request->Miles);
        if ($request->Does_the_Vehicle_Run_and_Drive) $query = $query->where('Does_the_Vehicle_Run_and_Drive', $request->Does_the_Vehicle_Run_and_Drive);
        // if ($request->buyers_quote) $query = $query->where('buyers_quote', '<', $request->buyers_quote);
        // if ($request->Any_Missing_Body_Panels_Interior_or_Engine_Parts) $query = $query->where('Any_Missing_Body_Panels_Interior_or_Engine_Parts', $request->Any_Missing_Body_Panels_Interior_or_Engine_Parts);
        if ($request->Do_they_have_a_Title) $query = $query->where('Do_they_have_a_Title', $request->Do_they_have_a_Title);
        if ($request->Fire_or_Flood_Damage) $query = $query->where('Fire_or_Flood_Damage', $request->Fire_or_Flood_Damage);
        if ($request->Reference_Number) $query = $query->where('Reference_Number', $request->Reference_Number);
        if ($request->Year) $query = $query->where('Year', $request->Year);
        if ($request->Make) $query = $query->where('Make', $request->Make);
        if ($request->Model) $query = $query->where('Model', $request->Make);

        // $query = $query->whereIn('Zip_Code', $this->getCities(Auth::user(), 250));
        // $total = $query->count();
        // return json_encode(['res'=>$query_zip_arr1]);
        // if(count($query_zip_arr1)) {
        //     $query = $query->whereIn('Zip_Code', $query_zip_arr1);
        // }
        $query = $query->select($select)
                        ->skip($page * $records_per_page)
                        ->take($records_per_page)
                        ->orderby('id', 'desc');
        $cars = array();
        if($request->page_type == 'cars') {

            if(count($query_zip_arr1)) {

                // $query1 = $query->whereIn('Zip_Code', $query_zip_arr1);
                $builder = $this->convertQuery($query);
                $builder = str_replace(" order by", ') and Zip_Code in ('.$this->array2string($query_zip_arr1).') order by',  strval($builder));
                $builder = str_replace("where ", 'where (',  strval($builder));
                // return $builder;
                $cars1 = $this->getQueryResult($builder)['data'];
                // return json_encode(['res'=>$cars1]);
                if($cars1) $cars = array_merge($cars, $cars1);
                if(count($query_zip_arr2)) {
                    $builder = $this->convertQuery($query);
                    $builder = str_replace(" order by", ') and Zip_Code in ('.$this->array2string($query_zip_arr2).') order by',  strval($builder));
                    $builder = str_replace("where ", 'where (',  strval($builder));
                    // return $builder;
                    $cars2 = $this->getQueryResult($builder)['data'];
                    // return json_encode(['res'=>$cars2]);
                    if($cars2) $cars = array_merge($cars, $cars2);
                    if(count($query_zip_arr3)) {
                        $builder = $this->convertQuery($query);
                        $builder = str_replace(" order by", ') and Zip_Code in ('.$this->array2string($query_zip_arr3).') order by',  strval($builder));
                        $builder = str_replace("where ", 'where (',  strval($builder));
                        $cars3 = $this->getQueryResult($builder)['data'];
                        if($cars3) $cars = array_merge($cars, $cars3);
                        // return json_encode(['res'=>$cars3]);
                        if(count($query_zip_arr4)) {
                            $builder = $this->convertQuery($query);
                            $builder = str_replace(" order by", ') and Zip_Code in ('.$this->array2string($query_zip_arr4).') order by',  strval($builder));
                            $builder = str_replace("where ", 'where (',  strval($builder));
                            $cars4 = $this->getQueryResult($builder)['data'];
                            if($cars4) $cars = array_merge($cars, $cars4);
                            // return json_encode(['res'=>$cars4]);
                        }
                    }
                }
            }
        }

        else {
            $builder = $this->convertQuery($query);
            $builder = str_replace(" order by", ') order by',  strval($builder));
            $cars = $this->getQueryResult($builder)['data'];
        }
        $total = '';

        $car_arr = array();

        if($cars)
            foreach($cars as $car) {
                $like = LikeBind::where('user_id', Auth::user()->id)->where('car_index', $car['id'])->first();
                $location = Location::where('Zip_Code', $car['Zip_Code'])->first();
                if($location) {
                    $car['Distance'] = intval($this->haversineGreatCircleDistance(Auth::user()->lat, Auth::user()->lng, $location->lat, $location->lng));
                }
                if($request->page_type == 'cars') {
                    if($like) $car['is_liked'] = true;
                    else $car['is_liked'] = false;

                }
                $flag = 0;
                if ($request->Miles) {
                    $mile = $request->Miles;
                    if(!$car['Miles']) $flag++;
                    else {
                        $car_mile = $car['Miles'];
                        if(str_contains($car_mile, "k")) $car_mile = explode("k", $car_mile)[0] * 1000;
                        if($car_mile <= $mile) $flag++;
                    }
                }
                else $flag++;

                if ($request->buyers_quote) {
                    if($car['Buyers_Quote'] <= $request->buyers_quote) $flag++;
                }
                else $flag++;

                if ($request->Any_Missing_Body_Panels_Interior_or_Engine_Parts)
                {
                    if($request->Any_Missing_Body_Panels_Interior_or_Engine_Parts == $car['Any_Missing_Body_Panels_Interior_or_Engine_Parts']) $flag++;
                }
                else $flag++;
                if($flag == 3) $car_arr[] = $car;

            }
        else $car_arr = array();

        return ['total' => count($car_arr),  'data' => $car_arr, 'user'=> Auth::user()];
    }

    private function getCities($user, $distance) {

        $zip_code_arr = Location::inCircle($user->lat, $user->lng, $distance)->get();
        $res = [];
        foreach($zip_code_arr as $zip) {
            $res[] = $zip->zip_code;
        }
        return $res;
    }

    private function array2string($array) {

        $str = implode(', ', $array);
        return $str;
    }

    private function convertQuery($builder) {
        $query = str_replace(array('?'), array('\'%s\''), $builder->toSql());
        $query = vsprintf($query, $builder->getBindings());
        // $query = str_replace(array("\t"), array(' '),  $query);

        $laravel_query_strings = array('`', '<>', " and ", 'cars', 'Tow_Company_id', " or ");
        $raw_sql_strings = array('', '!=', ") and ", 'Deals', 'Tow_Company.id', ") or ");

        $query = str_replace($laravel_query_strings, $raw_sql_strings,  $query);
        $bracket_count = substr_count($query, ")");
        $query = str_replace(array('where '), array('where '.str_repeat('(', $bracket_count)), $query);
        return $query;
    }

    public function getQueryResult($query) {
        $Request = (new Client())->post('https://www.zohoapis.com/crm/v2/coql', [
            'json' => array("select_query" => $query),
            'headers' => [
                'Authorization' => $this->ZohoBooksAccessToken
            ]
        ]);
        if ($Request->getStatusCode() != 200 && $Request->getStatusCode() != 201 ) return false;
        $JSON = $Request->getBody()->getContents();
        $Response = json_decode($JSON, true);
        return $Response ?? false;
    }


    public function like(Request $request, $id) {
        // $car = Car::where('index',$id)->first();
        // if(!$car) return 'invalid car';

        $link =  LikeBind::where('car_index', $id)->first();

        if ($request->like) {
            if (!$link) {
                $link = new LikeBind();
                $link->user_id = Auth::user()->id;
                $link->car_index = $id;
                $link->car_id = 0;
                $link->save();

            } else {
                if ($link) {
                    $link->delete();
                }
            }

        }
        // return $deals[0]->getKeyValue('Closing_Date');
        // $zohoService->createInvoices('1061914000238437019', "first Create Test", Auth::user());
        return 'success';
    }


    public function bid(Request $request, $id) {
        // $car = Car::where('index', $id)->first();
        // if(!$car) return 'invalid car';

        $price = $request->price;
        $user_id = Auth::user()->zoho_index;
        $user_name = Auth::user()->name;
        $user_email = Auth::user()->email;
        $now = new \DateTime();

        // update local DB
        // $car->Buyers_Quote = $price;
        // $car->Modified_By_id = $user_id;
        // $car->Modified_By_name = $user_name;
        // $car->Modified_By_email = $user_email;
        // $car->Tow_Company_id = $user_id;
        // $car->Tow_Company_name = $user_name;
        // $car->Modified_Time = $now->format('Y-m-d H:i:s');
        // $car->save();

        $zohoService = new ZohoSerivce();
        $deal = $zohoService->updateDealInfo($id, $price, $user_id, $user_name, $user_email, $now);

        return 'success';

    }

    public function setSchedule(Request $request, $id) {

        $zohoService = new ZohoSerivce();
        $now = new \DateTime($request->Scheduled_Time);
        $note = $request->Scheduled_Note;
        $schedule = $zohoService->scheduleTime($id, $now, $note);

        return json_encode(array('res' => 'success'));
    }

    public function pick(Request $request, $id) {

        $zohoService = new ZohoSerivce();
        $now = new \DateTime($request->Scheduled_Time);
        $note = $request->Scheduled_Note;
        $pick = $zohoService->scheduleTime($id, $now, $note, true);

        return json_encode(array('res' => 'success'));
    }

    public function cancel($id) {

        $zohoService = new ZohoSerivce();
        $pick = $zohoService->cancel($id);

        return json_encode(array('res' => 'success'));
    }

    public function pickupMass(Request $request) {
        $arr = $request->cars;
        $zohoService = new ZohoSerivce();

        $now = new \DateTime();


        // change in crm deals
        $deals = $zohoService->pickupMass($arr, $now);

        return json_encode(array('res' => "success"));
    }

    public function pay(Request $request) {
        $arr = $request->cars;
        $zohoService = new ZohoSerivce();

        $now = new \DateTime($request->Scheduled_Time);


        // change in crm deals
        $deals = $zohoService->pay4Car($arr);

        // create books invoice

        $InvoiceItems = [];
        $InvoiceTotal = 0;
        foreach ($request->cars as $car) {
            $Car = $this->getItem($car['id']);
            if (!$Car) {
                $Car = $this->createItem([
                    'name' => '#'.implode(" ", array($car['Reference_Number'], $car['Year'], $car['Make'], $car['Model'])),
                    'rate' => $car['Profit'],
                    'custom_fields' => [
                        [
                            'label' => 'CRM Car ID',
                            'value' => $car['id']
                        ],
                        [
                            'label' => 'Car Ref#',
                            'value' => $car['Reference_Number']
                        ]
                    ]
                ]);
                if (!$Car) {
                    echo 'Error creating car';
                    exit;
                }
            }
            $InvoiceItems[] = [
                'item_id' => $Car['item_id']
            ];
            $InvoiceTotal += $Car['rate'];
        }
        $User = Auth::user();
        $ContactId = $User->zoho_index;
        $BooksCustomer = $this->getZohoBooksCustomer($ContactId);
        if (!$BooksCustomer) {
            $BooksCustomer = $this->createZohoBooksCustomer($User->name, $User->email, $User->zoho_index);
            if (!$BooksCustomer) {
                echo 'Error creating customer';
                exit;
            }
        }
        $Invoice = $this->createInvoice([
            'customer_id' => $BooksCustomer['contact_id'],
            'line_items' => $InvoiceItems,
            'custom_fields' => [
                [
                    'label' => 'Amount Owed to Junk Car Boys:',
                    'value' => $InvoiceTotal
                ]
            ],
            'payment_options' => [
               'payment_gateways' => [
                   [
                        'gateway_name' => 'stripe'
                   ]
               ]
            ]
        ]);

        // var_dump($BooksCustomer);
        // var_dump($Invoice);


        return json_encode(array('res' => "success"));
    }


    public function saveFilter(Request $request) {
        $params = $request->input();
        $params['user_id'] = Auth::user()->id;
        $filter = Filter::create($params);
        return ['success' => true, 'filter' => $filter];
    }

    public function getFilters() {
        $filters = Filter::where('user_id', Auth::user()->id)->get();
        return ['success' => true, 'filters' => $filters];
    }

    public function deleteFilter($id) {
        $filter = Filter::where('user_id', Auth::user()->id)->find($id);
        if (!$filter) return ['error' => 'Invalid filter'];
        $filter->delete();
        return ['success' => true];
    }

    public function refreshCarData(Request $request) {
        $bulk_insert_mode = true;
        echo 'MODE : ' . ($bulk_insert_mode ? 'Bluk' : 'No Bulk') . '<br/><br/>';

        $zohoService = new ZohoSerivce();
        $page = intval($request->start_page);
        $end_page = intval($request->end_page);
        $length = 200;
        while (true) {
            $zip_codes = [];

            echo time().'---------'.$page . '<br/>';

            $records = $zohoService->getRecords('Deals', $page++, $length);


            if (!is_array($records)) {
                echo "end: resulte is invalid: not array";
                break;
            }

            echo time().'--------- get '.count($records).' records from zoho <br/>';

            $save_array = [];
            $break_time = false;

            $before_day = $request->from_days;
            if (!$before_day) $before_day = 365;

            $bulk_cars = [];
            foreach ($records as $record) {
                $car = $zohoService->saveCarToMysql($record);
                if ($car && $car->Created_Time && strtotime($car->Created_Time) < strtotime("-".$before_day." day", time())) {
                    $break_time = true;
                    echo 'end: date is before a year' . $car->Created_Time;
                    break;
                }
                if ($car) {
                    if($car->Zip_Code) {
                        $zip_codes[] = $car->Zip_Code;
                    }

                    if ($bulk_insert_mode) {
                        $bulk_cars[] = $car->toArray();
                    } else {
                        $check_count =  Car::select('id')->where('index', $car->index)->count();
                        if ($check_count == 0) $car->save();
                    }
                }
            }

            $zohoService->refreshCarLocationFromDB();

            //save bulk cars
            if ($bulk_insert_mode) {
                // check bulk existing index on db;
                $bulk_indexs = [];
                foreach ($bulk_cars as $one) {
                    $bulk_indexs[] = $one['index'];
                }
                $bulk_check_res = Car::select('index')->whereIn('index', $bulk_indexs)->get();
                $existing_indexes = [];
                foreach ($bulk_check_res as $one) {
                    $existing_indexes[] = $one->index;
                }

                $bulk_valid_cars = [];
                foreach ($bulk_cars as $car) {
                    if ( !in_array($car['index'], $existing_indexes) ) {
                        $bulk_valid_cars[] = $car;
                    }
                }
                Car::insert($bulk_valid_cars);
            }

            if ($break_time) break;
            echo time().'--------- save records to db <br/>';

            if ($records < $length) {
                echo "end: count less than 200";
                break;
            }

            if ($page > $end_page) break;
        }

    }

    public function refreshCarLocation() {
        $zohoService = new ZohoSerivce();
        $payload['id'] = "1061914000292793765";
        $payload['price'] = "250";
        $response =  $zohoService->Placebids($payload);
       // echo "<pre>"; print_r($response); echo "</pre>";
        return 'Bid placed successfully';
    }

    function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 3958.7657)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    private function createInvoice($Data)
    {
        try {
            $Request = (new Client())->post(env('ZOHO_BOOKS_API_URI').'invoices?organization_id='.env('ZOHO_ORGANIZATION_ID'), [
                'json' => $Data,
                'headers' => [
                    'Authorization' => $this->ZohoBooksAccessToken
                ]
            ]);
            $JSON = $Request->getBody()->getContents();
            return json_decode($JSON, true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $er = $e->getResponse()->getBody()->getContents();
            print_r(json_decode($er));
            return false;
        }
    }

    private function getItem($Id)
    {
        try {
            $Request = (new Client())->get(env('ZOHO_BOOKS_API_URI').'items', [
                'query' => [
                    'cf_crm_car_id' => $Id,
                    'per_page' => 1,
                    'organization_id' => env('ZOHO_ORGANIZATION_ID')
                ],
                'headers' => [
                    'Authorization' => $this->ZohoBooksAccessToken
                ]
            ]);
            $JSON = $Request->getBody()->getContents();
            $Response = json_decode($JSON, true);
            $Item = $Response['items'][0] ?? false;
            if (!$Item) return false;
            if (!isset($Item['cf_crm_car_id'])) return false;
            if ($Item['cf_crm_car_id'] != $Id) return false;
            return $Item;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return false;
        }
    }
    private function createItem($Data)
    {
        try {
            $Request = (new Client())->post(env('ZOHO_BOOKS_API_URI').'items', [
                'json' => $Data,
                'headers' => [
                    'Authorization' => $this->ZohoBooksAccessToken
                ]
            ]);
            if ($Request->getStatusCode() != 201 && $Request->getStatusCode() != 200) return false;
            $JSON = $Request->getBody()->getContents();
            $Response = json_decode($JSON, true);
            return $Response['item'] ?? false;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            print_r($e->getResponse()->getBody()->getContents());
            return false;
        }
    }



    private function getZohoBooksCustomer($Id)
    {
        try {
            $Request = (new Client())->get(env('ZOHO_BOOKS_API_URI').'contacts', [
                'query' => [
                    'cf_zoho_crm_id' => $Id,
                    'per_page' => 1,
                    'organization_id' => env('ZOHO_ORGANIZATION_ID')
                ],
                'headers' => [
                    'Authorization' => $this->ZohoBooksAccessToken
                ]
            ]);
            $JSON = $Request->getBody()->getContents();
            $Response = json_decode($JSON, true);
            $Customer = $Response['contacts'][0] ?? false;
            if (!$Customer) return false;
            if ($Customer['cf_zoho_crm_id'] != $Id) return false;
            return $Customer;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return false;
        }
    }
    private function createZohoBooksCustomer($Name, $Email, $Id)
    {
        try {
            $Request = (new Client())->post(env('ZOHO_BOOKS_API_URI').'contacts?organization_id='.env('ZOHO_ORGANIZATION_ID'), [
                'json' => [
                    'contact_name' => $Name,
                    'email' => $Email,
                    'custom_fields' => [
                        [
                            'label' => 'Zoho CRM ID',
                            'value' => $Id
                        ]
                    ]
                ],
                'headers' => [
                    'Authorization' => $this->ZohoBooksAccessToken
                ]
            ]);
            if ($Request->getStatusCode() != 201 && $Request->getStatusCode() != 200) return false;
            $JSON = $Request->getBody()->getContents();
            $Response = json_decode($JSON, true);
            return $Response['contact'] ?? false;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            print_r($e->getResponse()->getBody()->getContents());

            return false;
        }
    }

    function getDistance($addressFrom, $addressTo, $unit = ''){
        // Google API key
        $apiKey = env('Google_API_KEY');

        // Change address format
        $formattedAddrFrom    = str_replace(' ', '+', $addressFrom);
        $formattedAddrTo     = str_replace(' ', '+', $addressTo);

        // Geocoding API request with start address
        $geocodeFrom = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrFrom.'&sensor=false&key='.$apiKey);
        $outputFrom = json_decode($geocodeFrom);
        if(!empty($outputFrom->error_message)){
            return $outputFrom->error_message;
        }

        // Geocoding API request with end address
        $geocodeTo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrTo.'&sensor=false&key='.$apiKey);
        $outputTo = json_decode($geocodeTo);
        if(!empty($outputTo->error_message)){
            return $outputTo->error_message;
        }

        // Get latitude and longitude from the geodata
        $latitudeFrom    = $outputFrom->results[0]->geometry->location->lat;
        $longitudeFrom    = $outputFrom->results[0]->geometry->location->lng;
        $latitudeTo        = $outputTo->results[0]->geometry->location->lat;
        $longitudeTo    = $outputTo->results[0]->geometry->location->lng;

        // Calculate distance between latitude and longitude
        $theta    = $longitudeFrom - $longitudeTo;
        $dist    = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
        $dist    = acos($dist);
        $dist    = rad2deg($dist);
        $miles    = $dist * 60 * 1.1515;

        // Convert unit and return distance
        $unit = strtoupper($unit);
        if($unit == "K"){
            return round($miles * 1.609344, 2).' km';
        }elseif($unit == "M"){
            return round($miles * 1609.344, 2).' meters';
        }else{
            return round($miles, 2).' miles';
        }
    }


}
