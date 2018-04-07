<?php

namespace App\Http\Controllers\Admin;

use App\Models\Appoint;
use App\Models\Cllass;
use App\Models\Log;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Psy\Test\Exception\RuntimeExceptionTest;
use Tymon\JWTAuth\Claims\Claim;

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
     * @apiParam {string}  page 页码 默认第一页
     * @apiParam {string}  limit 显示条数 默认10
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
        $page=$request->get('page');
        $limit=$request->get('limit');
        if(!$limit) $limit=10;
        $page=$page?$page-1:0;

        $start=$page*$limit;
        $result["role"]=Role::all();
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
        $user=$user->skip($start)->take($limit)->orderBy("created_at",$sort)->get();
        $result["user"]=$user;
        return response()->json($result);


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
     * @api {post} /admin/userlist/create 添加用户
     *
     * @apiName user_create
     * @apiGroup UserManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{string} noid 编号
     * @apiParam{string} name 姓名
     * @apiParam{string} password 密码
     * @apiParam{string} branch 1文2理 可选
     * @apiParam{string} class_id 班级 可选
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/userlist/create
     */
    public function user_create(Request $request){
            $opuser= $request->header("opuser");
            if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
            if(!in_array(17,getfuncby($opuser)))
                return   response()->json(["code"=>403,"msg"=>"禁止访问"]);

            try{
                $input=$request->only(['noid','name','password','branch','class_id','major_id']);
                $validator = \Validator::make($input,[
                    'noid'=>'required|unique:users',
                    'password'=>'required|max:16|min:6',
                    'name'=>'required',
                    'branch'=>'nullable|alpha_num',
                    'class_id'=>'nullable|alpha_num|max:10|min:1',
                    'major_id'=>'nullable|alpha_num|max:10|min:1',
                ]);
                if ($validator->fails()) return response()->json(['code'=>400,'msg'=>'参数错误']);

                $user =new User();
                $user->Noid=$input['noid'];
                $user->name=$input['name'];
                $user->password=md5(md5($input['password']).$input['password']);
                if($input['class_id']){
                    $class=Cllass::where("id",$input['class_id'])->first();
                    if(!$class) return response()->json(["code"=>403,"msg"=>"class not found"]);
                    $user->class_id=$class->id;
                    $user->major_id=$class->major_id;
                }

                $user->branch=$input['branch'];
                $user->save();
                return response()->json(['code'=>200,'msg'=>'添加成功']);
            }catch(\Exception $e){
                return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
            }

        }


    /**
     * @api {get} /admin/userlist/create 添加用户前置
     *
     * @apiName user_add
     * @apiGroup UserManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/userlist/create
     */
    public function add(Request $request){
        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);

        $result=Cllass::all();
      return response()->json($result);
    }
}
