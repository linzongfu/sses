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
    $app->post('rolelist/appoint/{role_id}/create/{func_id}', 'RoleController@operate_create');

    $app->get('/rolelist/authview/{id}', 'RoleController@authview_show');
    $app->put('/rolelist/authview/{id}/edit', 'RoleController@authview_edit');


   $app->get('/questype', 'QuestypeController@index');
   $app->get('/test', 'QuestypeController@test');
   $app->post('/questype/create', 'QuestypeController@addquestype');
   $app->post('/questype/edit', 'QuestypeController@editquestype');
   $app->delete('/questype/del', 'QuestypeController@delquestype');
   $app->get('/questype/{id}', 'QuestypeController@show');

   $app->get('/loglist', 'LogController@index');


   $app->get('/report/entrance/where', 'ReportController@enwhere');
   $app->get('/report/entrance/index', 'ReportController@enindex');
   $app->post('/report/entrance/create', 'ReportController@encreate');
   $app->put('/report/entrance/edit/{id}', 'ReportController@enedit');
   $app->delete('/report/entrance/delete/{id}', 'ReportController@endelete');



    $app->get('/report/stage/where', 'ReportController@stagewhere');
    $app->get('/report/stage/index', 'ReportController@stageindex');
    $app->post('/report/stage/create', 'ReportController@stagecreate');
    $app->put('/report/stage/edit/{id}', 'ReportController@stageedit');
    $app->delete('/report/stage/delete/{id}', 'ReportController@stagedelete');

    $app->get('/report/graduate/where', 'GraduateController@where');
    $app->get('/report/graduate/index', 'GraduateController@index');
    $app->post('/report/graduate/create', 'GraduateController@create');
    $app->put('/report/graduate/edit/{id}', 'GraduateController@edit');
    $app->delete('/report/graduate/delete/{id}', 'GraduateController@delete');


   $app->get('/feedback', 'SystemController@index');
   $app->get('/feedback/{id}', 'SystemController@show');


   $app->get('/testrule', 'TestruleController@index');
   $app->put('/testrule/edit/{id}', 'TestruleController@rule_edit');


   $app->get('/gratulaterule', 'TestruleController@gra_index');
   $app->put('/gratulaterule/edit/{id}', 'TestruleController@grarule_edit');
});


