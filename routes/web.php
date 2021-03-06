<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::group([
    'prefix'    => 'api',
    'namespace' => 'Api'
], function () {
    Route::get('/gps', [
        'as' => 'gps',
        'uses' => 'GPSController@create']);

    Route::get('/getMenu', [
            'as' => 'menu',
            'uses' => 'MenuController@index' ]);
});

