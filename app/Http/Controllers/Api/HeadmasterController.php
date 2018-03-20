<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HeadmasterController extends Controller
{
    public function index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(9,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


        $time=Carbon::now();
        $class=Cllass::where("headmaster_id",$opuser)->where("end_at",'>',$time)->get();
        if(!$class)  return response()->json(["code"=>401,"msg"=>"Unable to verify your identity"]);

        $class=Cllass::where("headmaster_id",$opuser)->where("end_at",'>',$time)->get();
        if(!$class) {
            $class=Cllass::where("assistant_id",$opuser)->where("end_at",'>',$time)->get();
        }



    }
}
