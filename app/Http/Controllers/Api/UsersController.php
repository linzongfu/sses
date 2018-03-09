<?php

namespace App\Http\Controllers\Api;

use App\Models\Appoint;
use App\Models\FFunction;
use App\Models\Operate;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Validator;



class UsersController extends Controller
{
    /**
     * @api {post} /api/login 用户登录
     *
     * @apiName Userlogin
     * @apiGroup User
     * @apiVersion 1.0.0
     *
     * @apiParam {String}  Noid 学号/职工号
     * @apiParam {String}  Password 密码
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /api/login
     */
    public function login(Request $request){
        $Noid=$request->get('Noid');
        $password=$request->get('password');
        if(!$Noid||!$password) return response()->json(['code'=>400,'msg'=>'参数错误']);
       $user= User::where(['Noid'=>$Noid,'password'=>md5(md5($password).$password)])->first();
        if(!$user)return response()->json(['code'=>400,'msg'=>'用户名密码错误']);
        getfuncby($Noid);
        return response()->json($user);
    }
    public function add(Request $request){
        $opuser=$request->get("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(6,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);

        try{
            $input=$request->only(['Noid','Name','Password']);
            $validator = Validator::make($input,[
                'Noid'=>'required|unique:users',
                'Password'=>'required|max:16|min:6',
                'Name'=>'required',
            ]);
            if ($validator->fails()) return response()->json(['code'=>400,'msg'=>'参数错误']);

            $user =new User();
            $user->Noid=$input['Noid'];
            $user->name=$input['Name'];
            $user->password=md5(md5($input['Password']).$input['Password']);
            $user->save();
            return response()->json(['code'=>200,'msg'=>'添加成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
        }

    }
}
