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

Route::group([
    'namespace'=>'Api'
],function ($app){
   $app->get('/Test', 'TestController@index');
    $app->get('/Test/{id}', 'Api\TestController@EnTest');
    $app->get('/aa','HomeController@index');
    $app->middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });
});

