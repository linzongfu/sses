<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VoteController extends Controller
{
    /**
     * @api {get} api/selection/index  班主任选举主页
     *
     * @apiName index
     * @apiGroup Selection
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/selection/index
     */
    public function  index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(7,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $date=Carbon::now();
        $result["Current"]=Selection::where("teacher_id",$opuser)->where("starttime",'<',$date)->where('endtime','>',$date)->get();
        $result["Future"]=Selection::where("teacher_id",$opuser)->where('starttime','>',$date)->get();
        $result["History"]=Selection::where("teacher_id",$opuser)->where('endtime','<',$date)->get();
        return response()->json($result);
    }
}
