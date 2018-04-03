<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use App\Models\Intest;
use App\Models\Message;
use App\Models\Readmge;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;



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
      //  return $class->created_at;
        $stage=floor($now->diffInMonths($create)/$class->time)+1;

       $create->addMonth($stage*$class->time);
        $time=$create->diffInDays($now,false);
        $message=null;
       // return response()->json($time);
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

       $megids=getArraybystr(Message::where("class_id",$stud->class_id)->leftJoin('readmges',"messages.id","readmges.message_id")->where("stu_id",$opuser)->get(),'id');
        //return $megids;
        $message=Message::select('messages.id','messages.title','messages.description','messages.type','users.name')
            ->where("messages.class_id",$stud->class_id)->whereNotIn("messages.id",$megids)
            ->leftJoin("users","messages.sponsor_id","users.Noid")
            ->get()->toArray();
        //return $message;
       $t=count($message);
     //  return $t;
       if ($intest->count()!=0&&!$intest[0]->choise_reply){
           $message[$t]["title"]="你有待完成的阶段测试";
           $message[$t]["datetime"]=$time->toDateString();
           $message[$t]["type"]="待办";
           $t++;
       }

       if(!$stud->characterlabel_id||!$stud->branchlabel_id||!$stud->majorlabel_id){
           $message[$t]["title"]="请进行入学测试";
           $message[$t]["datetime"]=$time->toDateString();
           $message[$t]["type"]="待办";
           $t++;
       }
        return response()->json($message);
    }

    /**
     * @api {get} /api/message/show/:id  学生查看详细消息
     *
     * @apiName  Show_message
     * @apiGroup Message
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/message/show/:id
     */
    public function show($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(15,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $mge=Message::find($id);
        $stu=User::where("Noid",$opuser)->first();
        if(!$mge) return response()->json(["code"=>403,"msg"=>"message not found"]);
        if(!$stu) return response()->json(["code"=>403,"msg"=>"pleace logged in again"]);
        if($mge->class_id!=$stu->class_id) return response()->json(["code"=>403,"msg"=>"you not look this message"]);




       if($mge->sponsor_id)$mge["sponsor"]=getArraybystr(User::where("Noid",$mge->sponsor_id)->get(),'name');
        $redmsg=Readmge::where("message_id",$id)->where("stu_id",$opuser)->first();
        if (!$redmsg){
            $redmsg=new  Readmge();
            $redmsg->message_id=$id;
            $redmsg->stu_id=$opuser;
            $redmsg->is_read=1;
            $redmsg->save();
        }
        return response()->json($mge);
    }


    /**
     * @api {post} /api/add_message  班主任发布消息
     *
     * @apiName  Add_Msssage
     * @apiGroup Message
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}  sponsor_id 学号 可选
     * @apiParam {string}  title 标题
     * @apiParam {string}  description 描述
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/add_message
     */
    public function  Add_message(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(14,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $input=$request->only(['sponsor_id','title','description']);
        if(!$input['sponsor_id']) $input['sponsor_id']=$opuser;;
        $validator =\Validator::make($input,['sponsor_id'=>'required|alpha_num|string', 'title'=>'required|string', 'description'=>'required|string',]);
        if ($validator->fails()) return response()->json(['code'=>400,'msg'=>$validator->errors()]);
        $class=Cllass::where("headmaster_id",$opuser)->where("end_at",">",Carbon::now())->first();
        if (!$class) return response()->json(["code"=>403,"msg"=>"you are not is headmaster"]);
        try{
            $me =new Message();
            $me->class_id=$class->id;
            $me->sponsor_id=$input['sponsor_id'];
            $me->title=$input['title'];
            $me->description=$input['description'];
            $me->datetime=Carbon::now();
            $me->type="通知";
            $me->save();
            return response()->json(["code"=>200,"meg"=>"sucess"]);

       }catch (\Exception $e){
           return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
    }

    /**
     * @api {get} /api/message/recall/:id  班主任撤回消息
     *
     * @apiName  Recall_Msssage
     * @apiGroup Message
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/message/recall/:id
     */
    public function  Recall_message($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(14,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $class=Cllass::where("headmaster_id",$opuser)->where("end_at",">",Carbon::now())->first();
        if (!$class) return response()->json(["code"=>403,"msg"=>"you are not is headmaster"]);
        $me=Message::where(["id"=>$id,"class_id"=>$class->id])->first();
        if(!$me) return response()->json(["code"=>403,"msg"=>"you are not exist this message"]);

        $now=Carbon::now();
       // return $now."   ".$me->datetime;
        $time=$now->diffInMinutes(Carbon::parse($me->datetime),true);
        if($time>=3) return response()->json(["code"=>403,"msg"=>"时间超过三分钟，不允许撤回"]);
        $me->delete();
        return response()->json(["code"=>200,"msg"=>"成功撤回"]);
    }


    /**
     * @api {get} /api/message/index  消息主页
     *
     * @apiName  index
     * @apiGroup Message
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/message/index
     */
    public function  index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(14,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $class_id=Cllass::where("headmaster_id",$opuser)->where("end_at",">",Carbon::now())->first()->id;
        if(!$class_id) $class_id=User::where("Noid",$opuser)->first()->class_id;
        if (!$class_id) return response()->json(["code"=>403,"msg"=>"not found"]);


         $me=Message::where("class_id",$class_id)->get();
         return response()->json($me);
    }


}
