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
                 ->select('selections.id','selections.name as sele_name','classs.name as class_name','users.name as user_name','selections.starttime','selections.endtime')
                 ->get();
             $result["Future"]=Selection::where("selections.class_id",$user->class_id)->where('starttime','>',$date)->where("status",1)->leftJoin("users","selections.publish_id",'=','users.Noid')
                 ->leftJoin("classs","selections.class_id",'=','classs.id')
                 ->select('selections.id','selections.name as sele_name','classs.name as class_name','users.name as user_name','selections.starttime','selections.endtime')
                 ->get();
             $result["History"]=Selection::where("selections.class_id",$user->class_id)->where('endtime','<',$date)->where("status",1)->leftJoin("users","selections.publish_id",'=','users.Noid')
                 ->leftJoin("classs","selections.class_id",'=','classs.id')
                 ->select('selections.id','selections.name as sele_name','classs.name as class_name','users.name as user_name','selections.starttime','selections.endtime')
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

        $vote=Vote::select("std_id",'users.name as name',\DB::raw('count(votes.id) as num'))->where("selection_id",$id)->groupBy('std_id','users.name')
            ->orderBy('num','desc')
            ->leftJoin('users','votes.std_id','users.Noid')
            ->get();
        $result["Vote"]=$vote;
        $result["Is_Voted"]=Vote::where(["forstd_id"=>$opuser,"selection_id"=>$id])->count();
        return response()->json($result);
    }

    /**
     * @api {get} /api/vote/isvote/:id  学生是否投票
     *
     * @apiName IsVote
     * @apiGroup Selection
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {int} Is_Voted 大于0则投过票
     * @apiSampleRequest /api/vote/isvote/:id
     */
    public function  isvote($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(8,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $selection=Selection::find($id);
        if(!$selection) return response()->json(["code"=>403,"msg"=>"Parameter id is error"]);
        if($selection->status==0) return response()->json(["code"=>403,"msg"=>"Parameter id is error"]);
        $result["Is_Voted"]=Vote::where(["forstd_id"=>$opuser,"selection_id"=>$id])->count();
        return response()->json($result);
    }

    /**
     * @api {get} /api/vote/list/:id  学生投票选择
     *
     * @apiName Votelist
     * @apiGroup Selection
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {string} data
     * @apiSampleRequest /api/vote/list/:id
     */
    public function  listing($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(8,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $selection=Selection::find($id);
        if(!$selection) return response()->json(["code"=>403,"msg"=>"Selection id is error"]);
        if($selection->status==0) return response()->json(["code"=>403,"msg"=>"Selection id is error"]);

        $user=User::where('Noid',$opuser)->first();
        if(!$user) return response()->json(["code"=>403,"msg"=>"pleace log in again"]);

        if($user->class_id!=$selection->class_id) return response()->json(["code"=>403,"msg"=>"You can't take part in the vote in other classes"]);

        $users=User::where("class_id",$user->class_id)->select('Noid','name')->get();
        $result["user"]=$users;
        $result["maxvote"]=$selection->maxvote;
       return response()->json($result);

    }

    /**
     * @api {post} /api/vote/voting/:id  学生投票ing
     *
     * @apiName Voting
     * @apiGroup Selection
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}  students 被票的人1,2,3
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
        if(!$selection) return response()->json(["code"=>403,"msg"=>"Selection id is error"]);
        if($selection->status==0) return response()->json(["code"=>403,"msg"=>"Selection id is error"]);

        $user=User::where("Noid",$opuser)->first();
        if(!$user) return response()->json(["code"=>403,"msg"=>"User information id is error"]);
        if($user->class_id!=$selection->class_id)  return response()->json(["code"=>403,"msg"=>"You are not the student of the class"]);

        if (Vote::where(["forstd_id"=>$opuser,"selection_id"=>$id])->count()>0) return response()->json(["code"=>403,"msg"=>"You have already voted"]);

        $stu=$request->get("students");
        if(!$stu) return  response()->json(["code"=>403,"msg"=>"No vote"]);
        $stu=explode(",",$request->get("students"));
        if(count($stu)>$selection->maxvote) return  response()->json(["code"=>403,"msg"=>"You are too many vote"]);

         try{
             $t=0;
            for($i=0;$i<count($stu);$i++){
                if($stu[$i]){
                    $vote=new Vote();
                    $vote->std_id=$stu[$i];
                    $vote->forstd_id=$opuser;
                    $vote->selection_id=$id;
                    $vote->save();
                }
            }
            return response()->json(["code"=>403,"msg"=>"Vote success"]);;
         }
         catch(\Exception $e){
             return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
         }
    }

}
