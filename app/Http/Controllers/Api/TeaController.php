<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use App\Models\Course;
use App\Models\Teach;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TeaController extends Controller
{
    /**
     * @api {get} api/teacher/index  教师页面
     *
     * @apiName index
     * @apiGroup Teacher
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/teacher/index
     */
    public function  index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(4,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


        $result["class"]=Teach::select('class_id')->where([
            ['teach_id','=',$opuser],
            ['endtime','>',Carbon::now()],
        ])->get();
        $result["course"]=Teach::select('course_id')->where([
            ['teach_id','=',$opuser],
            ['endtime','>',Carbon::now()],
        ])->get();
        
        $result["class"]=Cllass::select('id','name')->whereIn('id',getArraybystr($result["class"],'class_id'))->get();
        $result["course"]=Course::select('id','name')->whereIn('id',getArraybystr($result["course"],'course_id'))->get();
        return response()->json($result);
    }
}
