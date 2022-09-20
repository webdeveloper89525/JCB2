<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::get('/test', function (Request $request) {
//     // $user = [];
//     // \Mail::send('mail-tmp-password', ['password' => '123123'], function ($m) use ($user) {
//     //     $m->to('al.arshavin@yandex.com', 'Test User')->subject('JCB App temporary password');
//     // });

//     return 'success';
// });

Route::get('test',    'UserController@forgotPassword');

Route::get('sync-user-info',    'UserController@syncUserInfo');
Route::post('login',            'UserController@login');
Route::get('logout',            'UserController@logout');
Route::post('forgot-password',  'UserController@forgotPassword');
Route::post('temp-password',    'UserController@checkTempPassword');
Route::post('reset-password',   'UserController@resetPassword');

Route::group(['middleware' => 'auth:api'], function() {
    Route::get('cars', 'CarController@index');
    Route::group(['prefix' => 'car'], function () {
        Route::post('like/{id}',    'CarController@like');
        Route::post('schedules/{id}', 'CarController@setSchedule');
        Route::post('bid/{id}', 'CarController@bid');
        Route::post('pick/{id}', 'CarController@pick');
        Route::get('cancel/{id}', 'CarController@cancel');
        Route::post('pay', 'CarController@pay');
        Route::post('pickupMass', 'CarController@pickupMass');
    });
    Route::group(['prefix' => 'filters'], function (){
        Route::post('',         'CarController@saveFilter');
        Route::get('',          'CarController@getFilters');
        Route::delete('{id}',   'CarController@deleteFilter');
    });

    Route::get('stripe', 'StripePaymentController@stripe');
    Route::post('/payments/stripe/intent', 'StripePaymentController@createIntent')->name('stripe.post');
});
