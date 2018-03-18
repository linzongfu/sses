<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use App\Models\Course;
use App\Models\Task;
use App\Models\Tasking;
use App\Models\Teach;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    /**
     * @api {get} /api/task/index  查看任课信息
     *
     * @apiName taskindex
     * @apiGroup Task
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/task/index
     */
    public function  index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(4,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $result["Teach"]=Teach::
            leftJoin("courses","teaches.course_id","=","courses.id")
            ->leftJoin("classs","teaches.class_id","=","classs.id")
            ->where("teach_id",$opuser)
            ->where("starttime",'<',Carbon::now())
            ->where('endtime','>',Carbon::now())
            ->select('teaches.id','courses.name as Course_Name','classs.name as Class_Name')
            ->get();
        return response()->json($result);
    }

    /**
     * @api {post} /api/task/showlist  查看发布的作业列表
     *
     * @apiName ShowTasks
     * @apiGroup Task
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     * @apiParam {int}  Teach_id 任课教学id
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/task/showlist
     */
    public  function  showlist(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(4,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $Teach_id=$request->get("Teach_id");
        if(!$Teach_id) return response()->json(["code"=>403,"msg"=>"missing Teach_id"]);
        $IsAccess=Teach::find($Teach_id)->where(["teach_id"=>$opuser,"id"=>$Teach_id])->get();
        if($IsAccess->count()==0) return response()->json(["code"=>403,"msg"=>"Unlawful access"]);

        $Tasklist=Task::where(["teach_id"=>$Teach_id,"status"=>1])->get();
        return response()->json($Tasklist);
    }


    /**
     * @api {post} /api/task/addtask  发布作业
     *
     * @apiName AddTask
     * @apiGroup Task
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     * @apiParam {int}  Teach_id 任课教学id
     * @apiParam {string}  Task_Name 作业名
     * @apiParam {string}  Task_Content 作业内容
     * @apiParam {string}  Task_starttime 作业最早提交时间
     * @apiParam {string}  Task_Endtime 作业结束提交时间
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/task/addtask
     */
    public  function  addtask(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(4,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $Teach_id=$request->get("Teach_id");
        if(!$Teach_id) return response()->json(["code"=>403,"msg"=>"missing Teach_id"]);
        $IsAccess=Teach::find($Teach_id)->where(["teach_id"=>$opuser,"id"=>$Teach_id])->get();
        if($IsAccess->count()==0) return response()->json(["code"=>403,"msg"=>"Unlawful access"]);

       $Name=$request->get("Task_Name");
       $Content=$request->get("Task_Content");
       $Starttime=$request->get("Task_starttime");
       $Endtime=$request->get("Task_Endtime");
       if(!$Name||!$Content||!$Starttime||!$Endtime) return response()->json(["code"=>403,"msg"=>"Parameter loss"]);
       try{
           $Task=new  Task();
           $Task->teach_id=$Teach_id;
           $Task->name=$Name;
           $Task->starttime=$Starttime;
           $Task->endtime=$Endtime;
           $Task->content=$Content;
           $Task->save();
       }catch (\Exception $e){
           return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
       }
        return response()->json(["code"=>200,"msg"=>" add task sucess"]);
    }


    /**
     * @api {post} /api/task/edittask/:id   修改作业
     *
     * @apiName EditTask
     * @apiGroup Task
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     * @apiParam {string}  Task_Name 新作业名
     * @apiParam {string}  Task_Content 新作业内容
     * @apiParam {string}  Task_starttime 新作业最早提交时间
     * @apiParam {string}  Task_Endtime 新作业结束提交时间
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/task/edittask/:id
     */

    public  function  edittask($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(4,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(4,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


        $Task=Task::find($id);

        $IsAccess=Teach::where(["id"=>$Task->teach_id,"teach_id"=>$opuser])->get();
        if($IsAccess->count()==0) return response()->json(["code"=>403,"msg"=>"Unlawful access"]);

        $Name=$request->get("Task_Name");
        $Content=$request->get("Task_Content");
        $Starttime=$request->get("Task_starttime");
        $Endtime=$request->get("Task_Endtime");
        if(!$Name||!$Content||!$Starttime||!$Endtime) return response()->json(["code"=>403,"msg"=>"Parameter loss"]);
        try{
            $Task=Task::find($id);
            $Task->name=$Name;
            $Task->starttime=$Starttime;
            $Task->endtime=$Endtime;
            $Task->content=$Content;
            $Task->save();
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
        return response()->json(["code"=>200,"msg"=>" edit task sucess"]);
    }

    /**
     * @api {get} /api/task/deltask/:id   删除作业
     *
     * @apiName DelTask
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
     * @apiSampleRequest /api/task/deltask/:id
     */
    public  function  delstask($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(4,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


     $Task=Task::find($id);

      $IsAccess=Teach::where(["id"=>$Task->teach_id,"teach_id"=>$opuser])->get();
      if($IsAccess->count()==0) return response()->json(["code"=>403,"msg"=>"Unlawful access"]);
        try{
            $Task=Task::find($id);
            $Task->status=0;

            $Task->save();
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
        return response()->json(["code"=>200,"msg"=>" delete task sucess"]);
    }


    /**
     * @api {get} /api/task/showlist/tasking   老师检查作业情况
     *
     * @apiName CheckTasking
     * @apiGroup Task
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiParam {int}  Task_id 作业id
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/task/showlist/tasking
     */
    public  function  tasking(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(4,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $Task_id=$request->get("Task_id");
        if(!$Task_id) return response()->json(["code"=>403,"msg"=>"Task_id missing"]);

        $Task=Task::find($Task_id);
        if(!$Task) return response()->json(["code"=>403,"msg"=>"pleace Check Task_id"]);

        $Teach=Teach::find($Task->teach_id);
        if(!$Teach) return response()->json(["code"=>403,"msg"=>"pleace Check Enter"]);

        $result["HasSubmitted"]=Tasking::where("task_id",$Task_id)
            ->leftJoin("users","taskings.user_id",'=','users.Noid')
            //->select("taskings.*",'users.Noid','users.name')
            ->select('taskings.id','taskings.status','taskings.score','users.Noid','users.name')
            ->get();
        $students=getArraybystr($result["HasSubmitted"],"Noid");
        $result["Unsubmitted"]=User::select("Noid",'name')->where("class_id",$Teach->class_id)->whereNotIn('Noid',$students)->get();

        return response()->json($result);
    }

    /**
 * @api {get} /api/task/showlist/tasking/:id   老师检查作业详情
 *
 * @apiName CheckTaskingDetails
 * @apiGroup Task
 * @apiVersion 1.0.0
 *
 * @apiHeader (opuser) {String} opuser
 * @apiHeaderExample {json} Header-Example:
 * {
 *      opuser
 * }
 *
 *
 * @apiSuccess {String} data
 * @apiSampleRequest /api/task/showlist/tasking/:id
 */
    public  function  taskingshow($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(4,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $result=Tasking::where("taskings.id",$id)
            ->leftJoin("users","taskings.user_id",'=','users.Noid')
            ->select("taskings.*",'users.Noid','users.name')
            //->select('taskings.status','users.Noid','users.name')
            ->get();

        return response()->json($result);
    }


    /**
     * @api {post} /api/task/showlist/tasking/:id/correct   老师批改作业
     *
     * @apiName CorrectTasking
     * @apiGroup Task
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiParam {float}  Score 分数
     * @apiParam {string}  Comment 评语
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/task/showlist/tasking/:id/correct
     */
    public  function  correct($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(4,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $score=$request->get('Score');
        $comment=$request->get('Comment');

        if(!$score||!$comment) return response(["code"=>403,"msg"=>"Pleace Enter Score or Comment"]);

        try{
            $tasking=Tasking::find($id);
            $tasking->comment=$comment;
            $tasking->score=$score;
            $tasking->status=2;
            $tasking->save();
            return response()->json(["code"=>200,"msg"=>"Correct sucess"]);
        }
        catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
        return response()->json($result);
    }
}
