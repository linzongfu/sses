<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
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


        $user=User::where("users.class_id",$class->id)->where("accidents.status",1)
           ->leftJoin("accidents","users.Noid","accidents.student_id")
            ->groupBy("users.Noid",'users.name')
            ->select('users.Noid','users.name',\DB::raw('SUM(accidents.score) as score'))
            ->take($start)->limit($limit)
            ->get();
        return $user;
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
