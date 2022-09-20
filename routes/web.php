<?php

use Illuminate\Support\Facades\Route;
// use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
// use zcrmsdk\crm\crud\ZCRMModule;
// use zcrmsdk\oauth\ZohoOAuth;

use App\Helpers\ZohoSerivce;


use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\TokenType;
use com\zoho\api\authenticator\store\DBStore;
use com\zoho\api\authenticator\store\FileStore;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\UserSignature;
use com\zoho\crm\api\SDKConfigBuilder;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\crm\api\logger\Logger;
use com\zoho\crm\api\logger\Levels;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\GetRecordsHeader;
use com\zoho\crm\api\record\GetRecordsParam;
use com\zoho\crm\api\record\ResponseWrapper;
use com\zoho\crm\api\record\Deals;
use com\zoho\crm\api\record\SearchRecordsParam;
use com\zoho\crm\api\Param;
use com\zoho\crm\api\query\QueryOperations;
use com\zoho\crm\api\query\BodyWrapper;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Models\Car;

Route::get('zoho_oauth2callback', function () {
    return 1;
});
Route::get('test','CarController@test');
Route::post('sync','CarController@sync');

Route::get('/refreshCarData', 'CarController@refreshCarData');
Route::get('/refreshCarLocation', 'CarController@refreshCarLocation');

Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');
