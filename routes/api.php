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
    'namespace'=>'Api',
    'middleware'=>'cors'
],function ($app){
   $app->get('/Test', 'TestController@index');

    $app->post('/login', 'UsersController@login');
    $app->post('/users/add', 'UsersController@add');

    $app->get('/ChoiceTest', 'EntestController@index');
    $app->get('/EnTest/{id}', 'EntestController@Entest');
    $app->post('/EnTest/Submit', 'EntestController@store');


    $app->get('/teacher/index', 'TeaController@index');
    $app->get('/teacher/showteach', 'TeaController@show');

    $app->get('/teacher/showteach/{id}', 'TeaController@showattend');

    $app->get('/Schedule/index','ScheduleController@index');
    $app->post('/Schedule/show','ScheduleController@ShowByTeacher');
    $app->post('/Schedule/ShowStudent','ScheduleController@ShowByStudent');

    $app->get('/task/index','TaskController@index');
    $app->post('/task/showlist','TaskController@showlist');
    $app->post('/task/addtask','TaskController@addtask');
    $app->get('/task/deltask/{id}','TaskController@delstask');
    $app->post('/task/edittask/{id}','TaskController@edittask');

    $app->get('/tasking/index','TaskingController@index');
    $app->get('/tasking/show/{id}','TaskingController@show');
    $app->post('/tasking/add','TaskingController@add');
    $app->get('/tasking/showMyTask','TaskingController@showMyTask');

    $app->get('/Test/{id}', 'Api\TestController@EnTest');
    $app->get('/aa','HomeController@index');
    $app->middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });
});

