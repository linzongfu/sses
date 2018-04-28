<?php

namespace App\Http\Controllers\Admin;

use App\Models\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LogController extends Controller
{
    /**
     * @api {get} /admin/loglist 日志管理
     *
     * @apiName system_list
     * @apiGroup systemManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {int}   noid  管理员ID 检索可选
     * @apiParam {string}   ip  ip地址 检索可选
     * @apiParam {string}  catelog 类别 可选(create|update|delete)
     * @apiParam {string}  type 类别 可选(目前只有1:管理员)
     * @apiParam {string}  page 页码 默认第一页
     * @apiParam {string}  limit 显示条数 默认10
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/loglist
     */
    public function index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $input=$request->only(['noid','ip','catelog','type']);
        $validator = \Validator::make($input,[
            'noid'=>'nullable|alpha_num',
            'ip'=>'nullable|ip',
            'catelog'=>'nullable|alpha',
            'type'=>'nullable',
        ]);
        if ($validator->fails()) return response()->json(['code'=>400,'msg'=>'参数错误']);
        $log=Log::whereNotNull("Noid");
        if($input["noid"])$log=$log->where("Noid",$input["noid"]);
        if($input["ip"])$log=$log->where("ip",$input["ip"]);
        if($input["catelog"])$log=$log->where("catelog",$input["catelog"]);
        $page=$request->get('page');
        $limit=$request->get('limit');
        if(!$limit) $limit=10;
        $page=$page?$page-1:0;
        $result["count"]=$log->count();

        $start=$page*$limit;
        $result["log"]= $log=$log->skip($start)->take($limit)->orderBy("created_at",'desc')->get();
        return response()->json($result);
    }

}
