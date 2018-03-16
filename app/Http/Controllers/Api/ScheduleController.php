<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use App\Models\Stage;
use App\Models\Teach;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * @api {get} /api/Schedule/index  课表选择界面
     *
     * @apiName  index
     * @apiGroup Schedule
     * @apiVersion 1.0.0
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/Schedule/index
     */
    public function index(){
           $result["Stage"]=Stage::all();
           $result["week"]=["第一周","第二周",
               "第三周","第四周","第五周","第六周",
               "第七周","第八周","第九周","第十周",
               "第十一周","第十二周","第十三周","第十四周",
               "第十五周","第十六周","第十七周","第十八周",
               "第十九周","第二十周",
           ];
           $result["class"]=Cllass::select('id','name')->get();
           return response()->json($result);
    }
    /**
     * @api {post} /api/Schedule/show  查看教师课表
     *
     * @apiName TeacherOfSchedule
     * @apiGroup Schedule
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiParam {int}  stage 学年阶段
     * @apiParam {string}  week 这几周的课表
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/Schedule/show
     */
    public function ShowByTeacher(Request $request){
        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);

        $stage=$request->get("stage");
        $week=$request->get("week");
        if(!$stage||!$week) return response()->json(["code"=>403,"msg"=>"请选择星期和学期"]);
         $result=DB::select('select calendars.day,calendars.Section,calendars.place,courses.name FROM teaches,calendars,courses WHERE teaches.teach_id LIKE ? AND teaches.stage_id=? AND calendars.teach_id=teaches.id AND teaches.course_id=courses.id AND calendars.week LIKE ? order BY calendars.day ,calendars.Section ',[$opuser,$stage,$week]);
         if(count($result)==0) return response()->json(["code"=>403,"msg"=>"无课表信息"]);
         return response()->json($result);
    }

    /**
     * @api {post} /api/Schedule/ShowStudent  查看学生课表
     *
     * @apiName StudentOfSchedule
     * @apiGroup Schedule
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiParam {int}  stage 学年阶段
     * @apiParam {string}  week 这几周的课表
     * @apiParam {string}  classid 班级 班主任查看无需输入
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/Schedule/ShowStudent
     */
    public function ShowByStudent(Request $request){
        $opuser= $request->header("opuser");
        $stage=$request->get("stage");
        $week=$request->get("week");
        $class=$request->get("classid");
        if(!$class){
            if (!$opuser) return response()->json(["code"=>403,"msg"=>"class information loss"]);
            $class=Cllass::select("id")->where([["headmaster_id","=",$opuser], ["end_at",'>',Carbon::now()]])->get();
            if($class->count()==0){

                return response()->json(["code"=>403,"msg"=>"class information loss"]);
            }
            $class = getArraybystr($class,"id")[0];
        }
        if(!$stage||!$week) return response()->json(["code"=>403,"msg"=>"请选择星期和学期"]);
        $result=DB::select('SELECT calendars.day,calendars.Section,calendars.place,courses.name FROM teaches,courses,calendars WHERE calendars.teach_id=teaches.id AND teaches.course_id=courses.id AND teaches.stage_id=? AND class_id=? AND calendars.week LIKE ? ORDER BY calendars.day ,calendars.Section ',[$stage,$class,$week]);
        if(count($result)==0) return response()->json(["code"=>403,"msg"=>"无课表信息"]);
        return response()->json($result);
    }
}
