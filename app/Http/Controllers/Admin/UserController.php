<?php

namespace App\Http\Controllers\Admin;

use App\Models\Appoint;
use App\Models\Log;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Psy\Test\Exception\RuntimeExceptionTest;

class UserController extends Controller
{
    /**
     * @api {get} /admin/userlist 用户列表
     *
     * @apiName user_list
     * @apiGroup UserManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {int}   role_id  角色 检索可选
     * @apiParam {string}   Noid  编号 检索可选
     * @apiParam {string}   sort   时间排序 可选 asc|desc
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/userlist
     */
    public function user_list(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $result=null;
       // $result["role"]=Role::all();
        $user=User::whereNotNull("Noid");
        $role_id=$request->get("role_id");
        $Noid=$request->get("Noid");
        $sort=$request->get("sort");
        if($Noid){
            $user=$user->where("Noid",$Noid)->get();
            if(count($user)==0)return response()->json(["code"=>403,"msg"=>"无此用户"]);
            $result["user"]=$user;
            return  response()->json($result);
        }

       if($role_id){
           $userids=getArraybystr(Appoint::where("role_id",$role_id)->get(),"Noid");
           $user=$user->whereIn("Noid",$userids);
       }
       if(!$sort) $sort='desc';
        $user=$user->orderBy("created_at",$sort)->get();
        return response()->json($user);


    }

    /**
     * @api {delete} /admin/userlist/delete/:Noid 删除用户
     *
     * @apiName user_delete
     * @apiGroup UserManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/userlist/delete/:Noid
     */
    public function user_delete($Noid,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $user=User::where("Noid",$Noid)->first();
        if(!$user) return  response()->json(["code"=>403,"msg"=>"无此用户"]);
        try{
            $user->delete();
            $log=new Log();
            $log->Noid=$opuser;
            $log->url=$request->getRequestUri();
            $log->ip=$request->getClientIp();
            $log->catalog="delete";
            $log->info="删除用户".$Noid;
            $log->type=1;
            $log->save();
            return response()->json(["code"=>200,"msg"=>"删除成功"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }

    }


    /**
     * @api {put} /admin/userlist/create 添加用户
     *
     * @apiName user_create
     * @apiGroup UserManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/userlist/create
     */
    public function user_create(Request $request){
        $opuser=$request->header("opuser");
        return $opuser;
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);



    }
}
