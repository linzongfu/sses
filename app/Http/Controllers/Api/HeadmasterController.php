<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use App\Models\Enmajortest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HeadmasterController extends Controller
{

    /**
     * @api {get} /api/headmaster/index  班主任界面
     *
     * @apiName  index
     * @apiGroup Headmaster
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/headmaster/index
     */
    public function index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(9,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $time=Carbon::now();
        $class=Cllass::where("headmaster_id",$opuser)->where("end_at",'>',$time)->get();
        if(!$class)  return response()->json(["code"=>401,"msg"=>"Unable to verify your identity"]);

        $class=Cllass::where("headmaster_id",$opuser)->where("end_at",'>',$time)->first();
        if(!$class) {
            $class=Cllass::where("assistant_id",$opuser)->where("end_at",'>',$time)->first();
            if (!$class) return response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        }
        $page=$request->get('page');
        $limit=$request->get('limit');
        if(!$limit) $limit=10;
        $page=$page?$page-1:0;

        $start=$page*$limit;

        $user=User::where("class_id",$class->id);
        $ids=getArraybystr($user->get(),"Noid");
        $result["count"]=$user->count();
        $user =$user->take($start)->limit($limit)
            ->get();
        $result["user"]=$user;
        $result["A"]=Enmajortest::wherein("user_id",$ids)->whereBetween('sumscore',[90,100])->get()->count();
        $result["B"]=Enmajortest::wherein("user_id",$ids)->whereBetween('sumscore',[80,89])->get()->count();
        $result["C"]=Enmajortest::wherein("user_id",$ids)->whereBetween('sumscore',[70,79])->get()->count();
        $result["D"]=Enmajortest::wherein("user_id",$ids)->whereBetween('sumscore',[60,69])->get()->count();
        $result["E"]=Enmajortest::wherein("user_id",$ids)->whereBetween('sumscore',[0,59])->get()->count();
        return $result;
    }







    /**
     * @api {get} /api/headmaster/test  测试
     *
     * @apiName  test
     * @apiGroup Headmaster
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/headmaster/test
     */
    public function  test(Request $request){
        $opuser=$request->header("opuser");
        $aa=Cllass::where("headmaster_id",$opuser)
        ->leftJoin("patterns","classs.pattern_id","patterns.id")
            ->select("classs.*","patterns.name")
        ->first();
       $first=Carbon::parse($aa->created_at);
       $second=Carbon::parse($aa->end_at);
       $r["1"]=$aa->created_at;
       $r["2"]=$aa->end_at;
       $r["3"]=$first->diffInMonths($second,false);
        return $r;
    }



}
