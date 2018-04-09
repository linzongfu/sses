<?php

namespace App\Http\Controllers\Admin;

use App\Models\Appoint;
use App\Models\Authview;
use App\Models\FFunction;
use App\Models\Operate;
use App\Models\Permit;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * @api {get} /admin/rolelist 角色列表
     *
     * @apiName role_list
     * @apiGroup RoleManage
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
     * @apiSampleRequest /admin/rolelist
     */
    public function index(Request $request){
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
        $role=Role::whereNotNull("id");
        $result["count"]=$role->count();

        $result["role"]=$role->skip($start)->take($limit)->orderBy("id",$sort)->get();
        return response()->json($result);
    }

    /**
     * @api {delete} /admin/rolelist/delete/:id 删除角色
     *
     * @apiName role_delete
     * @apiGroup RoleManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/rolelist/delete/:id
     */
    public function delete($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $role=Role::find($id);
        if(!$role) return  response()->json(["code"=>403,"msg"=>"无此角色"]);
        $name=$role->name;
        try{
            $role->delete();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"delete","删除角色".$name,1);
            return response()->json(["code"=>200,"msg"=>"删除成功"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
    }


    /**
     * @api {post} /admin/rolelist/create 添加角色
     *
     * @apiName role_create
     * @apiGroup RoleManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{string}  name 名称
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/rolelist/create
     */
    public function create(Request $request){
        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);
        try{
            $input=$request->only(['name']);
            $validator = \Validator::make($input,[
                'name'=>'required|unique:roles',
            ]);

            if ($validator->fails()) return response()->json(['code'=>400,'msg'=>$input['name'].'存在']);
            $role =new Role();
            $role->name=$input['name'];
            $role->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"create","添加角色".$input['name'],1);
            return response()->json(['code'=>200,'msg'=>'添加成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
        }

    }


    /**
     * @api {put} /admin/rolelist/edit/:id  编辑角色
     *
     * @apiName role_update
     * @apiGroup  RoleManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{string}  name 名称
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/rolelist/edit/:id
     */
    public function edit($id,Request $request){

        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);

        try{
            $input=$request->only(['name']);
            $validator = \Validator::make($input,[
                'name'=>'required|unique:roles',
            ]);
            if ($validator->fails()) return response()->json(['code'=>400,'msg'=>$input['name'].'存在']);
            $role =Role::find($id);
            $role->name=$input['name'];
            $role->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"update","修改角色".$input['name'],1);
            return response()->json(['code'=>200,'msg'=>'修改成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
        }

    }

    /**
     * @api {get} /admin/rolelist/appoint/:role_id 角色功能
     *
     * @apiName role_appoint_index
     * @apiGroup RoleManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/rolelist/appoint/:role_id
     */
    public function show($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $role=Role::find($id);
        if(!$role) return  response()->json(["code"=>403,"msg"=>"无此角色"]);
        $result["role"]=$role;
        $funs=getArraybystr(Operate::where("role_id",$id)->get(),"func_id");
        $funs=FFunction::wherein("id",$funs)->get();
         $result["fun"]=$funs;
         return response()->json($result);
    }


    /**
     * @api {delete} /admin/rolelist/appoint/:role_id/delete/:func_id  删除角色功能
     *
     * @apiName role_appoint_delete
     * @apiGroup RoleManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/rolelist/appoint/:role_id/delete/:func_id
     */
    public function operate_delete($roleid,Request $request,$func_id){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $operate=Operate::where(["role_id"=>$roleid,"func_id"=>$func_id])->first();
        if(!$operate) return  response()->json(["code"=>403,"msg"=>"无此角色"]);
        try{
            $operate->delete();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"delete","删除角色".$roleid."的任职".$func_id,1);
            return response()->json(["code"=>200,"msg"=>"删除成功"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
    }

    /**
     * @api {post} /admin/rolelist/appoint/:role_id/create/:func_id 添加角色功能
     *
     * @apiName role_appoint_create
     * @apiGroup RoleManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{string}  name 名称
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/rolelist/appoint/:role_id/create/:func_id
     */
    public function operate_create($roleid,Request $request,$func_id){
        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);

        $operate=Operate::where(["role_id"=>$roleid,"func_id"=>$func_id])->first();
        if($operate) return  response()->json(["code"=>403,"msg"=>"此角色功能存在"]);
        try{
            $operate=new Operate();
            $operate->role_id=$roleid;
            $operate->func_id=$func_id;
            $operate->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"create","添加角色".$roleid."的任职".$func_id,1);
            return response()->json(["code"=>200,"msg"=>"添加成功"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }

    }

    /**
     * @api {get} /admin/rolelist/authview/:role_id 角色视图
     *
     * @apiName role_authview_index
     * @apiGroup RoleManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/rolelist/authview/:role_id
     */
    public function authview_show($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $role=Role::find($id);
        if(!$role) return  response()->json(["code"=>403,"msg"=>"无此角色"]);
        $result["role"]=$role;
        $views=Authview::where("role_id",$id)->first();
        if(!$views) return response()->json(["code"=>403,"msg"=>"无此角色视图"]);
        $permit=Permit::wherein("id",explode(',', $views->permits))->get();
        $result["authview"]=$permit;
        return response()->json($result);
    }

    /**
     * @api {put} /admin/rolelist/authview/:role_id/edit 编辑角色视图
     *
     * @apiName role_authview_edit
     * @apiGroup RoleManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *  @apiParam{array}  permits id
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/rolelist/authview/:role_id/edit
     */
    public function authview_edit($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $role=Role::find($id);
        if(!$role) return  response()->json(["code"=>403,"msg"=>"无此角色"]);
        $result["role"]=$role;
        $views=Authview::where("role_id",$id)->first();
        if(!$views) return response()->json(["code"=>403,"msg"=>"无此角色视图"]);

        $permits=$request->get("permits");
        if(!$permits) return response()->json(["code"=>403,"msg"=>"请输入permits"]);
        $per=implode(",",$permits);
       // return $per;
        try{
            $views->permits=$per;
            $views->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"update","编辑角色".$role->name."的视图",1);
            return response()->json(["code"=>200,"msg"=>"编辑成功"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }


    }
}
