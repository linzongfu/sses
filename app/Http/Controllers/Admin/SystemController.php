<?php

namespace App\Http\Controllers\Admin;

use App\Models\Feedback;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SystemController extends Controller
{
    /**
     * @api {get} /admin/feedback 反馈管理
     *
     * @apiName feedback_list
     * @apiGroup systemManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {int}   noid  ID 检索可选
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/feedback
     */
    public function index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


        $back=Feedback::whereNotNull("id")->orderBy("created_at","desc");
        $Noid=$request->get("noid");
        if($Noid)$back=$back->where("user_id",$Noid);
        $result=$back->get();
        return response()->json($result);
    }

    /**
     * @api {get} /admin/feedback/:id  反馈查看
     *
     * @apiName feedback_show
     * @apiGroup systemManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {int}   noid  ID 检索可选
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/feedback/:id
     */
    public function show($id, Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


        $back=Feedback::where("id",$id)->first();
        if(!$back)return response()->json(["code"=>403,"msg"=>"Not found"]);
        $back->status=2;
        $back->save();
        return response()->json($back);
    }

}
