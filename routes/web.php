<?php

use Illuminate\Support\Facades\Route;

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
/*
Route::get('/', function () {
    return view('welcome');
});
*/
Route::get('test','TestController@index');
Route::get('/','SigninController@index');
Route::get('login','SigninController@index');
Route::post('login','SigninController@logincheck');
Route::post('readycheck','TestController@readycheck');
Route::post('tumo','TestController@tumo');
Route::post('pon','TestController@pon');
Route::post('reach','TestController@reach');
Route::post('sutehai','TestController@sutehai');
Route::post('gamecheck','TestController@gamecheck');
Route::post('tumoagari','TestController@tumoagari');
Route::get('start','TestController@start');