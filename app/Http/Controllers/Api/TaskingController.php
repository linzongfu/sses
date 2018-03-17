<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Models\Tasking;
use App\Models\Teach;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaskingController extends Controller
{
    /**
     * @api {get} /api/tasking/index  学生作业index
     *
     * @apiName taskingindex
     * @apiGroup Task
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     * @apiParam {int}  Teach_id 任课id
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/tasking/index
     */
    public  function index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(3,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $user=User::select("class_id")->where('Noid',$opuser)->get();
        if($user->count()==0) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        $class=getArraybystr($user,'class_id');

        if(empty($class[0]))   return  response()->json(["code"=>403,"msg"=>"Please land again"]);
        $nowdata=Carbon::now();

        $teach=Teach::where("teaches.class_id",$class[0])->where("starttime","<",$nowdata)->where("endtime",">",$nowdata)
             ->leftJoin("courses","teaches.course_id","=","courses.id")
             ->leftJoin("users","teaches.teach_id","LIKE","users.Noid")
            ->select('teaches.id','users.name as TeacherName','courses.name as CourseName')
            ->get();
        $result["teach"]=$teach;
       // return response()->json(getArraybystr($teach,"id"));
        $teach_id=$request->get("Teach_id");

        if(!$teach_id) {
            $Task["Implement"]=Task::select('id','name')->where("starttime","<",$nowdata)->where("endtime",">",$nowdata)->whereIn("teach_id",getArraybystr($teach,"id"))->get();
            $Task["End"]=Task::select('id','name')->where("starttime","<",$nowdata)->where("endtime","<",$nowdata)->whereIn("teach_id",getArraybystr($teach,"id"))->get();
           //return $teach_id;
        }else{
            $Task["Implement"]=Task::select('id','name')->where("starttime","<",$nowdata)->where("endtime",">",$nowdata)->whereIn("teach_id",[$teach_id])->get();
            $Task["End"]=Task::select('id','name')->where("starttime","<",$nowdata)->where("endtime","<",$nowdata)->whereIn("teach_id",[$teach_id])->get();

        }
        $result["Task"]=$Task;
        return response()->json($result);
    }


    /**
     * @api {get} /api/tasking/show/:id  学生查看当前作业详情
     *
     * @apiName tasking
     * @apiGroup Task
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/tasking/show/:id
     */
    public function show($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(3,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


        $task=Task::find($id);
        $tasking=Tasking::where("task_id",$id)->where("user_id",$opuser)->get()->count();
         $result["task"]=$task;
         $result["status"]=$tasking>0?"1":"0";
        return response()->json($result);
    }

    /**
     * @api {post} /api/tasking/add  学生提交作业
     *
     * @apiName AddTasking
     * @apiGroup Task
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiParam {int}  Task_id  作业id
     * @apiParam {string}  Answer  用户提交的作业
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/tasking/add
     */
    public  function  add(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(3,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $task_id=$request->get("Task_id");
        $answer=$request->get("Answer");
        if(!$task_id||!$answer) return response()->json(["code"=>403,"msg"=>"pleace enter Task_id and Answer"]);

        try{
            $tasking=Tasking::where("task_id",$task_id)->where("user_id",$opuser);
            if($tasking->count()>0){
                $tasking->update(['answer'=>$answer]);
                return response()->json(["code"=>200,"msg"=>"edit succes"]);
            }
            else {
                $taskings=new  Tasking();
                $taskings->task_id=$task_id;
                $taskings->user_id=$opuser;
                $taskings->answer=$answer;
                $taskings->save();
                return response()->json(["code"=>200,"msg"=>"add succes"]);
            }
        }
        catch (\Exception $e){
           return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }

    }

    /**
     * @api {get} /api/tasking/showMyTask  查看自己作业详情
     *
     * @apiName showMyTasking
     * @apiGroup Task
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiParam {int}  Task_id  作业id
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/tasking/showMyTask
     */
    public  function  showMyTask(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(3,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $task_id=$request->get("Task_id");
        if(!$task_id) return response()->json(["code"=>403,"msg"=>"pleace enter Task_id "]);

        try{
            $tasking=Tasking::where("task_id",$task_id)->where("user_id",$opuser);
            //return $tasking->count();
            if($tasking->count()==0){
                return response()->json(["code"=>403,"msg"=>"Not found"]);
            }
            else {
                return response()->json($tasking->get());
            }
        }
        catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }

    }
}
