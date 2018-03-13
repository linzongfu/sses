<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use App\Models\Course;
use App\Models\Teach;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    public function  index(Request $request){
        $opuser=$request->header("opuser");
        accessControl($opuser,5);
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
