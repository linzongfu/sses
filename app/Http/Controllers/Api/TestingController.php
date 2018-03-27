<?php

namespace App\Http\Controllers\Api;

use App\Models\Intest;
use App\Models\Intesting;
use App\Models\Question;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestingController extends Controller
{
    /**
     * @api {get} /api/intesting/index  学生阶段测试首页
     *
     * @apiName  Tesing_index
     * @apiGroup StageTest
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/intesting/index
     */
    public function index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(16,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


        $std=User::where("Noid",$opuser)->first();
        if(!$std)return response()->json(["code"=>403,"msg"=>"pleace login again"]);

        $time=Carbon::now();
        $result["unopend"]=Intest::select('id','stage_id','starttime_at','endtime_at')->where("status",1) ->where("class_id",$std->class_id)->where("starttime_at",'>',$time)->get();
        $result["opening"]=Intest::select('id','stage_id','starttime_at','endtime_at')->where("status",1)->where("class_id",$std->class_id)->where("starttime_at",'<',$time)->where("endtime_at",'>',$time)->get();
        $result["opened"]=Intest::select('id','stage_id','starttime_at','endtime_at')->where("status",1)->where("class_id",$std->class_id)->where("endtime_at",'<',$time)->get();
        return response()->json($result);
    }


    /**
     * @api {get} /api/intesting/show/:id  学生查看阶段测试
     *
     * @apiName  Tesing_show
     * @apiGroup StageTest
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {json} test 试卷信息
     * @apiSuccess {array} choice 选择题
     * @apiSuccess {array} judgment 判断题
     * @apiSuccess {bool} is_reply 是否做题
     * @apiSampleRequest /api/intesting/show/:id
     */
    public function show($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(16,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


        $std=User::where("Noid",$opuser)->first();
        if(!$std)return response()->json(["code"=>403,"msg"=>"pleace login again"]);

        $time=Carbon::now();
        $intest=Intest::find($id);

        if($std->class_id!=$intest->class_id) return response()->json(["code"=>403,"msg"=>"forbid access"]);
        if($time<$intest->starttime_at)return response()->json(["code"=>403,"msg"=>"not open! pleace wait"]);

        if($time<$intest->endtime_at&&$time>$intest->starttime_at){
            $result["test"]=$intest;
            $result["choice"]=Question::select("*")->whereIn("id",explode(",",$intest->choiceid))->get();
            $result["judgment"]=Question::select("*")->whereIn("id",explode(",",$intest->judgmentid))->get();

            $testing=Intesting::where(["intest_id"=>$intest->id,"user_id"=>$opuser])->get();
            if($testing->count()==0)$result["is_reply"]=false;
            else {
                $result["is_reply"]=true;
                $result["intesting"]=$testing;
            }
            return response()->json($result);
        }
       if ($time>$intest->endtime_at){
           $result["test"]=$intest;
           $result["choice"]=Question::select("*")->whereIn("id",explode(",",$intest->choiceid))->get();
           $result["judgment"]=Question::select("*")->whereIn("id",explode(",",$intest->judgmentid))->get();

           $testing=Intesting::where(["intest_id"=>$intest->id,"user_id"=>$opuser])->get();
           if($testing->count()==0)$result["is_reply"]=false;
           else {
               $result["is_reply"]=true;
               $result["intesting"]=$testing;
           }
           return response()->json($result);
       }
        return response()->json(["code"=>403,"msg"=>"stage test Id was error"]);
    }



    /**
     * @api {post} /api/intesting/submit/:id  学生查看阶段测试
     *
     * @apiName  Tesing_show
     * @apiGroup StageTest
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}  Cho_Reply 选择题回答
     * @apiParam {string}  judg_Reply 判断题回答
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/intesting/submit/:id
     */
    public function submit($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(16,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


        $std=User::where("Noid",$opuser)->first();
        if(!$std)return response()->json(["code"=>403,"msg"=>"pleace login again"]);

        $time=Carbon::now();
        $intest=Intest::find($id);

        if($std->class_id!=$intest->class_id) return response()->json(["code"=>403,"msg"=>"forbid access"]);
        if($time<$intest->starttime_at)return response()->json(["code"=>403,"msg"=>"not open! pleace wait"]);

        if($time<$intest->endtime_at&&$time>$intest->starttime_at){
            $result["test"]=$intest;
            $result["choice"]=Question::select("*")->whereIn("id",explode(",",$intest->choiceid))->get();
            $result["judgment"]=Question::select("*")->whereIn("id",explode(",",$intest->judgmentid))->get();

            $testing=Intesting::where(["intest_id"=>$intest->id,"user_id"=>$opuser])->get();
            if($testing->count()==0)$result["is_reply"]=false;
            else {
                $result["is_reply"]=true;
                $result["intesting"]=$testing;
            }
            return response()->json($result);
        }
        if ($time>$intest->endtime_at){
            $result["test"]=$intest;
            $result["choice"]=Question::select("*")->whereIn("id",explode(",",$intest->choiceid))->get();
            $result["judgment"]=Question::select("*")->whereIn("id",explode(",",$intest->judgmentid))->get();

            $testing=Intesting::where(["intest_id"=>$intest->id,"user_id"=>$opuser])->get();
            if($testing->count()==0)$result["is_reply"]=false;
            else {
                $result["is_reply"]=true;
                $result["intesting"]=$testing;
            }
            return response()->json($result);
        }
        return response()->json(["code"=>403,"msg"=>"stage test Id was error"]);
    }

}
