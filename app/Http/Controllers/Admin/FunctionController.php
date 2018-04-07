<?php

namespace App\Http\Controllers\Admin;

use App\Models\FFunction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FunctionController extends Controller
{
    /**
     * @api {get} /admin/functionlist 功能列表
     *
     * @apiName function_list
     * @apiGroup FunctionManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}   sort   时间排序 可选 asc|desc
     * @apiParam {string}  page 页码 默认第一页
     * @apiParam {string}  limit 显示条数 默认10
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/functionlist
     */
    public function function_list(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $result=null;
        $sort=$request->get("sort");
        if(!$sort)$sort="asc";
        $page=$request->get('page');
        $limit=$request->get('limit');
        if(!$limit) $limit=10;
        $page=$page?$page-1:0;
        $start=$page*$limit;
        $fun=FFunction::where("status",1)->skip($start)->take($limit)->orderBy("created_at",$sort)->get();
        $result=$fun;
        return response()->json($result);
    }

    /**
     * @api {delete} /admin/functionlist/delete/:id 删除功能
     *
     * @apiName function_delete
     * @apiGroup FunctionManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/functionlist/delete/:Noid
     */
    public function function_delete($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $function=FFunction::find($id);
        if(!$function) return  response()->json(["code"=>403,"msg"=>"无此功能"]);
        $name=$function->name;
        try{
            $function->status=0;
            $function->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"delete","删除功能".$name,1);
            return response()->json(["code"=>200,"msg"=>"删除成功"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
    }


    /**
     * @api {post} /admin/functionlist/create 添加功能
     *
     * @apiName function_create
     * @apiGroup FunctionManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{string} name 名称
     * @apiParam{string}  remarks 备注
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/functionlist/create
     */
    public function function_create(Request $request){
        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);

        try{
            $input=$request->only(['name','remarks']);
            $validator = \Validator::make($input,[
                'name'=>'required',
                'remarks'=>'nullable',
            ]);
            if ($validator->fails()) return response()->json(['code'=>400,'msg'=>'参数错误']);

            $fun =new FFunction();
            $fun->name=$input['name'];
            $fun->remarks=$input['remarks'];
            $fun->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"create","添加功能".$input['name'],1);
            return response()->json(['code'=>200,'msg'=>'添加成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
        }

    }


    /**
     * @api {put} /admin/functionlist/edit/:id 修改功能
     *
     * @apiName function_update
     * @apiGroup  FunctionManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{string} name 名称
     * @apiParam{string} remarks 备注
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/functionlist/edit/:d
     */
    public function function_edit($id,Request $request){

        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);

        try{
            $input=$request->only(['name','remarks']);
            $validator = \Validator::make($input,[
                'name'=>'required',
                'remarks'=>'nullable',
            ]);
            if ($validator->fails()) return response()->json(['code'=>400,'msg'=>'参数错误']);
            $fun =FFunction::find($id);
            $fun->name=$input['name'];
            $fun->remarks=$input['remarks'];
            $fun->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"update","修改功能".$input['name'],1);
            return response()->json(['code'=>200,'msg'=>'添加成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
        }

    }

}
