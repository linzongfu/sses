<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PermitController extends Controller
{
    /**
     * @api {get} /admin/permitlist 视图权限列表
     *
     * @apiName permit_list
     * @apiGroup PermitManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/permitlist
     */
    public function permit_list(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $result=null;
        $sort=$request->get("sort");
        if(!$sort)$sort="asc";



        $fun=Permit::where("status",1);
        $result["count"]=$fun->count();

        $result["per"]=$fun->orderBy("id",$sort)->get();
        return response()->json($result);
    }

    /**
     * @api {delete} /admin/permitlist/delete/:id 删除视图权限
     *
     * @apiName permit_delete
     * @apiGroup PermitManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/permitlist/delete/:Noid
     */
    public function permit_delete($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $permit=Permit::find($id);
        if(!$permit) return  response()->json(["code"=>403,"msg"=>"无此视图权限"]);
        $name=$permit->remark;
        try{
            $permit->status=0;
            $permit->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"delete","删除视图权限".$name,1);
            return response()->json(["code"=>200,"msg"=>"删除成功"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
    }


    /**
     * @api {post} /admin/permitlist/create 添加视图权限
     *
     * @apiName permit_create
     * @apiGroup PermitManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{int} permitPoint 权限点
     * @apiParam{string}  remark 备注
     * @apiParam{string}  type 级别
     * @apiParam{string}  parent_id 父亲权限
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/permitlist/create
     */
    public function permit_create(Request $request){
        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);
        try{
            $input=$request->only(['permitPoint','remark','type','parent_id']);
            $validator = \Validator::make($input,[
                'permitPoint'=>'required',
                'remarks'=>'nullable',
            ]);
            if ($validator->fails()) return response()->json(['code'=>400,'msg'=>'参数错误']);

            $per =new Permit();
            $per->permitPoint=$input['permitPoint'];
            $per->remark=$input['remark'];
            $per->type=$input['type'];
            $per->parent_id=$input['parent_id'];
            $per->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"create","添加视图权限".$input['remark'],1);
            return response()->json(['code'=>200,'msg'=>'添加成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
        }

    }


    /**
     * @api {put} /admin/permitlist/edit/:id  编辑视图权限
     *
     * @apiName permit_update
     * @apiGroup  PermitManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{int} permitPoint 权限点
     * @apiParam{string}  remark 备注
     * @apiParam{string}  type 级别
     * @apiParam{string}  parent_id 父亲权限
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/permitlist/edit/:d
     */
    public function permit_edit($id,Request $request){

        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);

        try{
            $input=$request->only(['permitPoint','remark','type','parent_id']);
            $validator = \Validator::make($input,[
                'permitPoint'=>'required',
                'remarks'=>'nullable',
            ]);
            if ($validator->fails()) return response()->json(['code'=>400,'msg'=>'参数错误']);
            $per =Permit::find($id);
            $per->permitPoint=$input['permitPoint'];
            $per->remark=$input['remark'];
            $per->type=$input['type'];
            $per->parent_id=$input['parent_id'];
            $per->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"update","修改视图权限".$input['remark'],1);
            return response()->json(['code'=>200,'msg'=>'添加成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
        }

    }

}
