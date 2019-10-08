<?php

use Illuminate\Http\Request;

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

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('auth/me', 'AuthController@local');
    Route::get('oauth/me', 'AuthController@oauth');
});


// KP

// All Publicly accessible APIs go here
require('api_public.php');

// access using http://localhost:8000/api/v1/tags
// Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:api']], function () {
Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'V1\Admin', 'middleware' => ['auth:api']], function () {
    Route::apiResource('tags', 'TagsApiController');
});
