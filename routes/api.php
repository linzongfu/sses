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


    $app->get('/EnTest/index_tea','EntestController@index_tea');
    $app->get('/ChoiceTest', 'EntestController@index');
    $app->get('/EnTest/{id}', 'EntestController@Entest');
    $app->get('/EnTest/Correct/{Noid}', 'EntestController@Correct');
    $app->post('/EnTest/Submit', 'EntestController@store');
    $app->post('/EnTest/show', 'EntestController@show');
    $app->post('/EnTest/Corrected/{Noid}', 'EntestController@Corrected');

    $app->get('/intest/generate', 'IntestController@Generate');
    $app->get('/intest/choice', 'IntestController@choice_index');
    $app->get('/intest/index', 'IntestController@index');
    $app->post('/intest/complete/{stage}', 'IntestController@result_test');
    $app->post('/intest/edit/{id}', 'IntestController@edit');
    $app->post('/intest/defense/{intest_id}', 'IntestController@defense');




    $app->get('/intesting/index', 'TestingController@index');
    $app->get('/intesting/show/{id}', 'TestingController@show');
    $app->get('/intesting/show/{id}', 'TestingController@show');
    $app->post('/intesting/submit', 'TestingController@submit');
    $app->post('/intesting/debate', 'TestingController@debate');

    $app->post('/feedback', 'FeedbackController@feedback');

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

    $app->get('/task/showlist/tasking','TaskController@tasking');
    $app->get('/task/showlist/tasking/{id}','TaskController@taskingshow');
    $app->post('/task/showlist/tasking/{id}/correct','TaskController@correct');

    $app->get('/tasking/index','TaskingController@index');
    $app->get('/tasking/show/{id}','TaskingController@show');
    $app->post('/tasking/add','TaskingController@add');
    $app->get('/tasking/showMyTask','TaskingController@showMyTask');

    $app->get('/selection/index','SelectionController@index');
    $app->get('/selection/index/{id}','SelectionController@show');
    $app->post('/selection/add','SelectionController@add');
    $app->get('/selection/del/{id}','SelectionController@del');


    $app->get("/enreport/{Noid}","ReportController@enReport");


    $app->get('/vote/index','VoteController@index');
    $app->get('/vote/index/{id}','VoteController@show');
    $app->get('/vote/isvote/{id}','VoteController@isvote');
    $app->post('/vote/voting/{id}','VoteController@add');
    $app->get('/vote/list/{id}','VoteController@listing');

    $app->get('/headmaster/index','HeadmasterController@index');
    $app->get('/headmaster/test','HeadmasterController@test');


    $app->get('/message/headmaster/get','MessageController@Message_Of_Headmaster');
    $app->get('/message/show/{id}','MessageController@show');
    $app->get('/message/student/get','MessageController@Message_Of_Student');
    $app->post('/add_message','MessageController@Add_message');
    $app->get('/message/recall/{id} ','MessageController@Recall_message');
    $app->get('/message/index','MessageController@index');


    $app->get('/accident/choice','AccidentController@choice');
    $app->get('/accident/{id}','AccidentController@show');
    $app->post('/accident/{id}/add','AccidentController@add');
    $app->get('/accident/del/{id}','AccidentController@del');



    $app->get('/evalustion/front','EvaluController@front');
    $app->post('/evalustion/choice','EvaluController@choice');
    $app->post('/evalustion/evaluating','EvaluController@add');


    $app->get('/Test/{id}', 'Api\TestController@EnTest');
    $app->get('/aa','HomeController@index');


    Route::post('/upload','FileController@upload');
    $app->middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });
});

