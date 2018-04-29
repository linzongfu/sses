<?php

namespace App\Http\Controllers\Api;

use App\Models\Attend;
use App\Models\Calendar;
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


    /**
     * @api {post} /api/teacher/attendmanage/:calendarid   学生出勤考勤
     *
     * @apiName Signin
     * @apiGroup Teacher
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiParam {int}  signin 0：迟到1：正常
     * @apiParam {int}  signout 0：早退1：正常
     * @apiParam {string}  student_id
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/teacher/attendmanage/:calendarid
     */
    public function sign($id,Request $request){
        $opuser=$request->header("opuser");

        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(4,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $std_id=$request->get("student_id");
        $signin=$request->get("signin");
        if(!$signin)$signin=0;
        $signout=$request->get("signout");
        if(!$signout)$signout=0;
        if(!$std_id) return response()->json(["code"=>403,"msg"=>"学号不能为空"]);
        try {
            $attend = Attend::where("student_id", $std_id)->where("calendar_id", $id)->first();
            if (!$attend) {
                $attend = new  Attend();
                $attend->student_id = $std_id;
                $attend->calendar_id = $id;
            }
            $attend->signin = $signin;
            $attend->signout = $signout;
            $attend->save();
            return response()->json(["code"=>200,"msg"=>"success"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
    }

    /**
     * @api {get} /api/teacher/attendfront/:calendarid   学生出勤考勤学生选择
     *
     * @apiName Signinfront
     * @apiGroup Teacher
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/teacher/attendfront/:calendarid
     */
    public function attendfront($calendarid,Request $request){
        $opuser=$request->header("opuser");

        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(4,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


        $calendar=Calendar::where("id",$calendarid)->select("teach_id")->first();
        if(!$calendar) return response()->json(["code"=>403,"msg"=>"calendarid error"]);
        $class=Teach::where("id",$calendar->teach_id)->select("teach_id","class_id")->first();
        if($class->teach_id!=$opuser) return response()->json(["code"=>403,"msg"=>"calendarid error"]);
        if(!$class) return response()->json(["code"=>403,"msg"=>"not this 教学记录"]);

        $exitstu=getArraybystr(Attend::where("calendar_id",$calendarid)->get(),"student_id");

        $stu=User::where("class_id",$class->class_id)->whereNotIn("Noid",$exitstu)->select("Noid","name")->get();
        return response()->json($stu);


    }

}
