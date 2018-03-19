<?php

namespace App\Http\Controllers\Api;

use App\Models\Selection;
use App\Models\User;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VoteController extends Controller
{
    /**
     * @api {get} /api/vote/index  学生投票主页
     *
     * @apiName VoteIndex
     * @apiGroup Selection
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/vote/index
     */
    public function  index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(8,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

       // if(!$class) $class=Cllass::where("assistant_id",$opuser)->where("end_at",">",$date)->first();
             $user=User::where("Noid",$opuser)->first();
             if(!$user) return response()->json(["code"=>401,"msg"=>"Please log in again"]);

             $date=Carbon::now();
             $result["Current"]=Selection::where("selections.class_id",$user->class_id)->where("starttime",'<',$date)->where('endtime','>',$date)->where("status",1)->leftJoin("users","selections.publish_id",'=','users.Noid')
                 ->leftJoin("classs","selections.class_id",'=','classs.id')
                 ->select('selections.id','selections.name as sele_name','classs.name as class_name','users.name as user_name')
                 ->get();
             $result["Future"]=Selection::where("selections.class_id",$user->class_id)->where('starttime','>',$date)->where("status",1)->leftJoin("users","selections.publish_id",'=','users.Noid')
                 ->leftJoin("classs","selections.class_id",'=','classs.id')
                 ->select('selections.id','selections.name as sele_name','classs.name as class_name','users.name as user_name')
                 ->get();
             $result["History"]=Selection::where("selections.class_id",$user->class_id)->where('endtime','<',$date)->where("status",1)->leftJoin("users","selections.publish_id",'=','users.Noid')
                 ->leftJoin("classs","selections.class_id",'=','classs.id')
                 ->select('selections.id','selections.name as sele_name','classs.name as class_name','users.name as user_name')
                 ->get();
             return response()->json($result);
    }



    /**
     * @api {get} /api/vote/index/:id  学生查看投票详情
     *
     * @apiName showVote
     * @apiGroup Selection
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {json} Vote 票型
     * @apiSuccess {int} Is_Voted 大于0则投过票
     * @apiSampleRequest /api/vote/index/:id
     */
    public function  show($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(8,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $selection=Selection::find($id);
        if(!$selection) return response()->json(["code"=>403,"msg"=>"Parameter id is error"]);
        if($selection->status==0) return response()->json(["code"=>403,"msg"=>"Parameter id is error"]);

        $vote=Vote::select("std_id",\DB::raw('count(id) as num'))->where("selection_id",$id)->groupBy('std_id')
            ->orderBy('num','desc')
            ->get();
        $result["Vote"]=$vote;
        $result["Is_Voted"]=Vote::where(["std_id"=>$opuser,"selection_id"=>$id])->count();
        return response()->json($result);
    }


    /**
     * @api {post} /api/vote/voting/:id  学生投票
     *
     * @apiName Voting
     * @apiGroup Selection
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {array}   新作业名
     * @apiParam {string}  Task_Content 新作业内容
     * @apiParam {string}  Task_starttime 新作业最早提交时间
     *
     * @apiSuccess {string} data
     * @apiSampleRequest /api/vote/voting/:id
     */
    public function  add($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(8,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $selection=Selection::find($id);
        if(!$selection) return response()->json(["code"=>403,"msg"=>"Parameter id is error"]);
        if($selection->status==0) return response()->json(["code"=>403,"msg"=>"Parameter id is error"]);

        $vote=Vote::select("std_id",\DB::raw('count(id) as num'))->where("selection_id",$id)->groupBy('std_id')
            ->orderBy('num','desc')
            ->get();
        $result["Vote"]=$vote;
        $result["Is_Voted"]=Vote::where(["std_id"=>$opuser,"selection_id"=>$id])->count();
        return response()->json($result);
    }

}
