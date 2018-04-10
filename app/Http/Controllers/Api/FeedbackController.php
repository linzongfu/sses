<?php

namespace App\Http\Controllers\Api;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FeedbackController extends Controller
{


    /**
     * @api {post} /api/feedback  反馈
     *
     * @apiName  add
     * @apiGroup Feedback
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}   content 内容
     * @apiParam {string}   contact 手机号码  可选
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/feedback
     */
    public function  feedback(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        $user=User::where("Noid",$opuser)->first();
       if(!$user) return response()->json(["code"=>403,"msg"=>"无此用户"]);

       $content=$request->get("content");
       if(!$content)return response()->json(["code"=>403,"msg"=>"pleace enter content"]);
       $contact=$request->get("contact");
       try{
           $feedback=new  Feedback();
           $feedback->user_id=$user->Noid;
           $feedback->name=$user->name;
           $feedback->content=$content;
           if($contact) $feedback->contact=$contact;
           $feedback->save();
           return response()->json(["code"=>200,"msg"=>"add sucess"]);
       }catch (\Exception $e){
           return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
       }
    }
}
