<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use App\Models\Intest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use function PHPSTORM_META\type;

class MessageController extends Controller
{

    /**
     * @api {get} /api/message/headmaster/get  班主任消息
     *
     * @apiName  Headmaster_message
     * @apiGroup Message
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/message/headmaster/get
     */
    public function Message_Of_Headmaster(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(14,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $now=Carbon::now();
        $class=Cllass::where("headmaster_id",$opuser)->where("end_at",'>',$now)
            ->leftJoin('patterns','classs.pattern_id','patterns.id')
            ->select("classs.*",'patterns.name','patterns.time')
            ->first();
        if(!$class) return response()->json(["code"=>403,"msg"=>"only open to head master"]);
        $create=Carbon::parse($class->created_at);
        $stage=floor($now->diffInMonths($create)/$class->time)+1;

       $create->addMonth($stage*$class->time);
        $time=$create->diffInDays($now,false);
        $message=null;
        if ($time<0&&$time>=-10) {
            $intests=Intest::where(["stage_id"=>$stage,"class_id"=>$class->id,"status"=>1])->first();
            if(!$intests) {
                $message[0]["title"]="请布置第".$stage."阶段测试的试题";
                $message[0]["datetime"]=$now->toDateString();
                $message[0]["type"]="通知";
                return response()->json($message);
            }
        }
        return response()->json($message);


    }

    /**
     * @api {get} /api/message/student/get  学生消息
     *
     * @apiName  Student_message
     * @apiGroup Message
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/message/student/get
     */
    public function Message_Of_Student(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(15,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);



        $time=Carbon::now();
        $stud=User::where("Noid",$opuser)->first();
        if(!$stud) return response()->json(["code"=>403,"msg"=>"pleace login again"]);
        $intest=Intest::where("intests.class_id",$stud->class_id)->where("intests.starttime_at",'<',$time)->where("intests.endtime_at",'>',$time)
            ->leftJoin("intestings",'intests.id',"intestings.intest_id")
            ->get();

       $t=0;
        $message=null;
       if ($intest->count()!=0&&!$intest[0]->choise_reply){
           $message[$t]["title"]="你有待完成的阶段测试";
           $message[$t]["datetime"]=$time->toDateString();
           $message[$t]["type"]="通知";
           $t++;
       }

       if(!$stud->characterlabel_id||!$stud->branchlabel_id||!$stud->majorlabel_id){
           $message[$t]["title"]="请进行入学测试";
           $message[$t]["datetime"]=$time->toDateString();
           $message[$t]["type"]="通知";
           $t++;
       }
        return response()->json($message);
    }
}
