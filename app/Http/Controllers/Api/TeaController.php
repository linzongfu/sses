<?php

namespace App\Http\Controllers\Api;

use App\Models\Attend;
use App\Models\Cllass;
use App\Models\Course;
use App\Models\Teach;
use App\Models\User;
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

    /**
     * @api {get} /api/teacher/showteach  查看教学记录
     *
     * @apiName showteach
     * @apiGroup Teacher
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiParam {int}  classid 任课班级id
     * @apiParam {int}  courseid 课程id
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/teacher/showteach
     */
    public function show(Request $request){
        $opuser=$request->header("opuser");
       // dd($opuser);
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(4,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $class=$request->get("classid");
        $course=$request->get("courseid");
        if(!$class||!$course) return response()->json(["code"=>403,"msg"=>"information is wrong"]);
       $tea=Teach::where(["teach_id"=>$opuser,"course_id"=>$course,"class_id"=>$class])->get();
       if($tea->count()==0) return response()->json(["code"=>403,"msg"=>"无上课信息"]);
       $result=$tea[0]->calendars()->orderBy("created_at")->get();
       // $result=Teach::find(1)->get();
        return response()->json($result);
    }

    /**
     * @api {get} /api/teacher/showteach/:id   查看学生出勤
     *
     * @apiName LookAttend
     * @apiGroup Teacher
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiParam {int}  classid 任课班级id
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/teacher/showteach/:id
     */
    public function showattend($id,Request $request){
        $opuser=$request->header("opuser");
        // dd($opuser);
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(4,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $class=$request->get("classid");
        if(!$class) return response()->json(["code"=>403,"msg"=>"classid is missing"]);
        $late=getArraybystr((Attend::select("student_id")->where(["calendar_id"=>$id,"signin"=>0,"signout"=>1])->get()),"student_id");
        $attendance=getArraybystr((Attend::select("student_id")->where(["calendar_id"=>$id])->get()),"student_id");
        $leaveearly=getArraybystr((Attend::select("student_id")->where(["calendar_id"=>$id,"signin"=>1,"signout"=>0])->get()),"student_id");
//return response()->json($leaveearly);

        $result["late"]=User::select('id','Noid','name')->where("class_id",$class)->whereIn('Noid',$late)->get();
        $result["attendance"]=User::select('id','Noid','name')->where("class_id",$class)->whereIn('Noid',$attendance)->get();
        $result["leaveearly"]=User::select('id','Noid','name')->where("class_id",$class)->whereIn('Noid',$leaveearly)->get();
        $result["absence"]=User::select('id','Noid','name')->where("class_id",$class)->whereNotIn('Noid',$attendance)->get();

        //  $result["attendance"]=
       // result[];

        return response()->json($result);
    }


    public function schedule(Request $request){
        $opuser=$request->header("opuser");
        // dd($opuser);
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(4,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);



    }
}
