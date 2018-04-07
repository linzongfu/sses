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

Route::group(['namespace'=>'Admin',
    'prefix'=>'admin',
    'middleware'=>'cors'
    ],function ($app){
    $app->get('/index', 'ManageController@index');

    $app->get('/userlist', 'UserController@user_list');
    $app->delete('/userlist/delete/{Noid}', 'UserController@user_delete');
    $app->post('/userlist/create', 'UserController@user_create');
    $app->get('/userlist/create', 'UserController@add');




   $app->get('/questype', 'QuestypeController@index');
   $app->get('/test', 'QuestypeController@test');
   $app->post('/questype/create', 'QuestypeController@addquestype');
   $app->post('/questype/edit', 'QuestypeController@editquestype');
   $app->delete('/questype/del', 'QuestypeController@delquestype');
   $app->get('/questype/{id}', 'QuestypeController@show');
   $app->get('/aa', function () {
        echo "ddddd";
    });
});


