<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use App\Models\Selection;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SelectionController extends Controller
{
    /**
     * @api {get} api/selection/index  班主任or助教选举主页
     *
     * @apiName index
     * @apiGroup Selection
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/selection/index
     */
    public function  index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(7,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $class=Cllass::where("headmaster_id",$opuser)->orWhere("assistant_id",$opuser)->first();
        if(!$class) return response()->json(["code"=>403,"msg"=>"you are not is headmaster or assistant"]);
        $date=Carbon::now();
        $result["Current"]=Selection::whereIn("publish_id",[$class->headmaster_id,$class->assistant_id])->where("starttime",'<',$date)->where('endtime','>',$date)->where("status",1)
            ->leftJoin("users","selections.publish_id",'=','users.Noid')
            ->leftJoin("classs","selections.class_id",'=','classs.id')
            ->select('selections.id','selections.level','selections.name as sele_name','classs.name as class_name','users.name as user_name','users.Noid as user_Noid','selections.starttime','selections.endtime')
            ->get();
        $result["Future"]=Selection::whereIn("publish_id",[$class->headmaster_id,$class->assistant_id])->where('starttime','>',$date)->where("status",1)->leftJoin("users","selections.publish_id",'=','users.Noid')
            ->leftJoin("classs","selections.class_id",'=','classs.id')
            ->select('selections.id','selections.level','selections.name as sele_name','classs.name as class_name','users.name as user_name','users.Noid as user_Noid','selections.starttime','selections.endtime')
            ->get();;
        $result["History"]=Selection::whereIn("publish_id",[$class->headmaster_id,$class->assistant_id])->where('endtime','<',$date)->where("status",1)->leftJoin("users","selections.publish_id",'=','users.Noid')
            ->leftJoin("classs","selections.class_id",'=','classs.id')
            ->select('selections.id','selections.level','selections.name as sele_name','classs.name as class_name','users.name as user_name','users.Noid as user_Noid','selections.starttime','selections.endtime')
            ->get();;
        return response()->json($result);
    }


    /**
     * @api {get} /api/selection/index/:id 查看选举结果
     *
     * @apiName show
     * @apiGroup Selection
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/selection/index/:id
     */
    public function  show($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(7,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

       $selection=Selection::find($id);
       if(!$selection) return response()->json(["code"=>403,"msg"=>"Parameter id is error"]);
       if($selection->status==0) return response()->json(["code"=>403,"msg"=>"Parameter id is error"]);

        $vote=Vote::select("std_id",'users.name',\DB::raw('count(votes.id) as num'))->where("selection_id",$id)
            ->leftJoin('users','votes.std_id','users.Noid')
            ->groupBy('std_id','users.name')
            ->orderBy('num','desc')
            ->get();
        return response()->json($vote);
    }

    /**
     * @api {get} /api/selection/del/:id 删除选举结果
     *
     * @apiName Del
     * @apiGroup Selection
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/selection/del/:id
     */
    public function  del($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(7,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


       $selection=Selection::find($id);
       if (!$selection) return response()->json(["code"=>403,"msg"=>"Non-existent Selection"]);
       $class=Cllass::find($selection->class_id);
       if(!$class)  return response()->json(["code"=>403,"msg"=>"lllegal visit"]);
       if($class->headmaster_id!=$opuser){
           if ($class->assistant_id!=$opuser)return response()->json(["code"=>403,"msg"=>"lllegal visit"]);
           if($selection->publish_id!=$opuser)return response()->json(["code"=>403,"msg"=>"lllegal visit"]);
       }
       try{
           $selection->status=0;
           $selection->save();
           return response()->json(["code"=>200,"msg"=>"del the success of the selection"]);
       }
       catch (\Exception $e){
           return response()->json(["code"=>403,"msg"=>$e->getMessage()]);

       }
    }


    /**
     * @api {post} /api/selection/add 班主任发起评选
     *
     * @apiName addSelection
     * @apiGroup Selection
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{int} MaxVote 最大投票数
     * @apiParam{string} Name 选举标题
     * @apiParam{string} StartTime 开始时间
     * @apiParam{string} EndTime 结束时间
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/selection/add
     */
    public function  add(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(7,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


        $level=2;
        $date=Carbon::now();
        $class=Cllass::where("headmaster_id",$opuser)->where("end_at",">",$date)->first();
        if(!$class) {
            $class=Cllass::where("assistant_id",$opuser)->where("end_at",">",$date)->first();
            $level=1;
        }
        if(!$class)return response()->json(["code"=>403,'msg'=>"You are not the head teacher can't continue to operate"]);
        $class_id=$class->id;
        $name=$request->get("Name");
        $maxvote=$request->get("MaxVote");
        $starttime=$request->get("StartTime");
        $endtime=$request->get("EndTime");

        if(!$name||!$maxvote||!$starttime||!$endtime) return response()->json(["code"=>403,"msg"=>"Parameter loss"]);

        try{
            $selection=new  Selection();
            $selection->class_id=$class_id;
            $selection->publish_id=$opuser;
            $selection->level=$level;
            $selection->name=$name;
            $selection->maxvote=$maxvote;
            $selection->starttime=$starttime;
            $selection->endtime=$endtime;
            $selection->save();
            return response()->json(["code"=>200,"msg"=>"Release the success of the selection"]);

        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
    }


}
