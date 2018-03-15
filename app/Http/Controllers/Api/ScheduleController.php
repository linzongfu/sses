<?php

namespace App\Http\Controllers\Api;

use App\Models\Stage;
use App\Models\Teach;
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
           return response()->json($result);
    }
    /**
     * @api {post} /api/Schedule/show  查看教师课表
     *
     * @apiName teacher of Schedule
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
         $result=DB::select('select calendars.day,calendars.Section,calendars.place,courses.name FROM teaches,calendars,courses WHERE teaches.teach_id LIKE ? AND teaches.stage_id=? AND calendars.teach_id=teaches.id AND teaches.course_id=courses.id AND calendars.week LIKE ?',[$opuser,$stage,$week]);
        return response()->json($result);
    }


    /**
     * @api {post} /api/Schedule/show  查看教师课表
     *
     * @apiName teacher of Schedule
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
    public function ShowByStudent(Request $request){
        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);

        $stage=$request->get("stage");
        $week=$request->get("week");
        if(!$stage||!$week) return response()->json(["code"=>403,"msg"=>"请选择星期和学期"]);
        $result=DB::select('select calendars.day,calendars.Section,calendars.place,courses.name FROM teaches,calendars,courses WHERE teaches.teach_id LIKE ? AND teaches.stage_id=? AND calendars.teach_id=teaches.id AND teaches.course_id=courses.id AND calendars.week LIKE ?',[$opuser,$stage,$week]);
        return response()->json($result);
    }
}
