<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LogController extends Controller
{
    /**
     * @api {get} /admin/loglist 用户列表
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
            'noid'=>'nullable|unique:users',
            'ip'=>'nullable|max:16|min:6',
            'catelog'=>'nullable|alpha_num',
            'type'=>'nullable|alpha_num|max:10|min:1',
        ]);
        return response()->json($result);


    }

}
