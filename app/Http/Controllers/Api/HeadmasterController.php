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
     * @api {get} /api/headmaster/index  课表选择界面
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


        return $class->id;
        $user=User::where("users.class_id",$class->id)
           // ->leftJoin("accidents","users.Noid","accidents.student_id")
            //->groupBy("accidents.student_id",'users.name')
            //->select('accidents.student_id','users.name',\DB::raw('SUM(accidents.score) as score'))
            ->get();
        return $user;


    }
}
