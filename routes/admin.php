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
    $app->get('/userlist/create', 'UserController@add');
    $app->post('/userlist/create', 'UserController@user_create');
    $app->put('/userlist/edit/{Noid}', 'UserController@user_edit');
    $app->delete('/userlist/delete/{Noid}', 'UserController@user_delete');

    $app->get('/functionlist', 'FunctionController@function_list');
    $app->get('/functionlist/create', 'FunctionController@add');
    $app->post('/functionlist/create', 'FunctionController@function_create');
    $app->put('/functionlist/edit/{id}', 'FunctionController@function_edit');
    $app->delete('/functionlist/delete/{id}', 'FunctionController@function_delete');

    $app->get('/permitlist', 'PermitController@permit_list');
    $app->get('/permitlist/create', 'PermitController@add');
    $app->post('/permitlist/create', 'PermitController@permit_create');
    $app->put('/permitlist/edit/{id}', 'PermitController@permit_edit');
    $app->delete('/permitlist/delete/{id}', 'PermitController@permit_delete');

    $app->get('/rolelist', 'RoleController@index');
    $app->post('/rolelist/create', 'RoleController@create');
    $app->put('/rolelist/edit/{id}', 'RoleController@edit');
    $app->delete('/rolelist/delete/{id}', 'RoleController@delete');


    $app->get('/rolelist/appoint/{id}', 'RoleController@show');
    $app->delete('rolelist/appoint/{role_id}/delete/{func_id}', 'RoleController@operate_delete');




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


