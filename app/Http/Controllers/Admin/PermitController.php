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
     * @apiParam {string}   sort   时间排序 可选 asc|desc
     * @apiParam {string}  page 页码 默认第一页
     * @apiParam {string}  limit 显示条数 默认10
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
        $page=$request->get('page');
        $limit=$request->get('limit');
        if(!$limit) $limit=10;
        $page=$page?$page-1:0;
        $start=$page*$limit;
        $fun=Permit::where("status",1);
        $result["count"]=$fun->count();

        $result["per"]=$fun->skip($start)->take($limit)->orderBy("id",$sort)->get();
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
     * @api {post} /admin/permitlist/create
     *
     * @apiName permit_create
     * @apiGroup PermitManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{string} name 名称
     * @apiParam{string}  remarks 备注
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
     * @api {put} /admin/permitlist/edit/:id
     *
     * @apiName permit_update
     * @apiGroup  PermitManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{string} name 名称
     * @apiParam{string} remarks 备注
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
