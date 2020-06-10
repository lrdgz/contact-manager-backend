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

Route::group(['prefix' => 'user', 'namespace' => 'User'], function (){
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');

    Route::post('contacts/add', 'ContactController@addContacts');
    Route::get('contacts/get-all/{token}/{pagination?}', 'ContactController@getPaginatedContacts');
    Route::put('contacts/update/{id}', 'ContactController@editSingleData');
    Route::delete('contacts/delete/{id}', 'ContactController@deleteContacts');
    Route::get('contacts/get-single/{id}', 'ContactController@getSingleData');
    Route::get('contacts/search/{search}/{token}/{pagination?}', 'ContactController@searchData');

});
