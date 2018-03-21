<?php

namespace App\Http\Controllers\Api;

use App\Models\Accident;
use App\Models\Cllass;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Claims\Claim;

class AccidentController extends Controller
{
    /**
     * @api {get}  /api/accident/:Noid  某学生的事件详情
     *
     * @apiName  index
     * @apiGroup Accident
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}  page 页码
     * @apiParam {string}  limit 显示条数
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/accident/:Noid
     */
    public function show($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(10,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $class=Cllass::where("headmaster_id",$opuser)->first();
        if(!$class)$class=Cllass::where("assistant_id",$opuser)->first();
        if (!$class) return response()->json(["code"=>403,'msg'=>"You are not the headteacher or assistant can't continue to operate"]);
        $user=User::where("Noid",$id)->first();
        if(!$user) return response()->json(["code"=>403,"msg"=>"this student not exist"]);
        if($user->class_id!=$class->id) return response()->json(["code"=>403,'msg'=>"You are not the headteacher or assistant can't continue to operate"]);

        $page=$request->get('page');
        $limit=$request->get('limit');
        if(!$limit) $limit=10;
        $page=$page?$page-1:0;

        $start=$page*$limit;

        $resutl["User_Name"]=$user->name;
        $resutl["Accidents"]=Accident::where("accidents.student_id",$id)
            ->leftJoin("users","accidents.editor_id","users.Noid")
            ->where("accidents.status",1)
            ->select("accidents.reason",'accidents.score','accidents.created_at','users.name as editor')
            ->skip($start)->take($limit)
            ->get();
        return response()->json($resutl);
    }


    /**
     * @api {post}  /api/accident/:Noid/add  某学生的事况添加
     *
     * @apiName  Add
     * @apiGroup Accident
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {float}  score 分数
     * @apiParam {string}  reason
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/accident/:Noid/add
     */
    public function add($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(10,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $class=Cllass::where("headmaster_id",$opuser)->first();
        if(!$class)$class=Cllass::where("assistant_id",$opuser)->first();
        if (!$class) return response()->json(["code"=>403,'msg'=>"You are not the headteacher or assistant can't continue to operate"]);
        $user=User::where("Noid",$id)->first();
        if(!$user) return response()->json(["code"=>403,"msg"=>"this student not exist"]);
        if($user->class_id!=$class->id) return response()->json(["code"=>403,'msg'=>"You are not the headteacher or assistant can't continue to operate"]);


        $score=$request->get("score");
        $reason=$request->get("reason");
        if(!$score||!$reason) return response()->json(["code"=>403,"msg"=>"missing score and reason"]);

        try{
            $accident=new Accident();
            $accident->student_id=$id;
            $accident->editor_id=$opuser;
            $accident->score=$score;
            $accident->reason=$reason;

            $accident->save();
            return response()->json(["code"=>200,"msg"=>"add accident success"]);

        }
        catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
        return response()->json($resutl);
    }


    /**
     * @api {get}  /api/accident/del/:id  误操作等情况删除事况
     *
     * @apiName  Del
     * @apiGroup Accident
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/accident/del/:id
     */
    public function del($id,Request $request){

        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(10,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

       $accident=Accident::find($id);
       if(!$accident) return response()->json(["code"=>403,"msg"=>"this accident not exist"]);
       $student=User::where("Noid",$accident->student_id)->leftJoin("classs","users.class_id","classs.id")->first();
       if(!$student) return response()->json(["code"=>403,"msg"=>"this accident are wrroy"]);

       if(!($student->headmaster_id==$opuser||$student->assistant_id==$opuser)) return  response()->json(["code"=>403,"msg"=>"forbid access"]);

       try{
           $accident->status=0;
           $accident->save();
           return response()->json(["code"=>200,"msg"=>"del success"]);
       }catch (\Exception $e){
           return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
       }
    }
}
