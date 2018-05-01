<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use App\Models\Debate;
use App\Models\EnReport;
use App\Models\Evaluation;
use App\Models\Intest;
use App\Models\Intesting;
use App\Models\Stage_report_result;
use App\Models\Testrule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    /**
     * @api {get} /api/enreport/:Noid  入学报告
     *
     * @apiName  Entrance_Report
     * @apiGroup report
     * @apiVersion 1.0.0
     *
     *@apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/enreport/:Noid
     */
   public  function enReport($Noid,Request $request){
       $opuser=$request->header("opuser");
       if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
       if(!in_array(12,getfuncby($opuser)))
           return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
       $student=User::where("Noid",$Noid)->first();
       if(!$student) return response()->json(["code"=>403,"msg"=>"student Noid is error"]);
       if($Noid!=$opuser){
           $class=Cllass::where("headmaster_id",$opuser)->first();
           if(!$class) $class=Cllass::where("assistant_id".$opuser)->first();
           if(!$class) return response()->json(["code"=>403,"msg"=>"forbid access"]);
           if($class->id!=$student->class_id) return response()->json(["code"=>403,"msg"=>"forbid access"]);
       }
       if(!$student->major_id||!$student->characterlabel_id||!$student->branchlabel_id||!$student->majorlabel_id)
           return response()->json(["code"=>403,"msg"=>"student ".$Noid." Do not finish the entrance test"]);
        $report=EnReport::select('id','content','created_at')
            ->where(["major_id"=>$student->major_id,"characterlabel_id"=>$student->characterlabel_id,"branchlabel_id"=>$student->branchlabel_id,"majorlabel_id"=>$student->majorlabel_id])->first();
        if(!$report)return response()->json(["code"=>403,"msg"=>"pleace contact the administrator to improve the rules of asmisson EnReport "]);
       $result["name"]=$student->name;
       $result["report"]=$report;
       return response()->json($result);
   }


    /**
     * @api {get} /api/stagereport/:Noid/list  学中报告列表
     *
     * @apiName  Stage_Report_list
     * @apiGroup report
     * @apiVersion 1.0.0
     *
     *@apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/stagereport/:Noid/list
     */
    public  function StageReport_list($Noid,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        $student=User::where("Noid",$Noid)->first();
        if(!$student) return response()->json(["code"=>403,"msg"=>"student Noid is error"]);
        $nowtime=Carbon::now();
        if($Noid!=$opuser){
            $class=Cllass::where("headmaster_id",$opuser)->where("created_at","<",$nowtime)->where("end_at",">",$nowtime)->first();
            if(!$class) $class=Cllass::where("assistant_id".$opuser)->where("created_at","<",$nowtime)->where("end_at",">",$nowtime)->first();
            if(!$class) return response()->json(["code"=>403,"msg"=>"forbid access"]);
            if($class->id!=$student->class_id) return response()->json(["code"=>403,"msg"=>"forbid access"]);
            $class_id=$class->id;
        }else
        {
            $class_id=$student->class_id;
        }

        $now=Carbon::now();
        $class=Cllass::where("classs.id",$class_id)
            ->leftJoin('patterns','classs.pattern_id','patterns.id')
            ->select("classs.*",'patterns.name','patterns.time')
            ->first();
        if(!$class) return response()->json(["code"=>403,"msg"=>"only open to head master"]);
        $create=Carbon::parse($class->created_at);
        $stage=floor($now->diffInMonths($create)/$class->time);
        $result=[];
        for($i=1;$i<=$stage;$i++){
            $result[$i]=["id"=>$i,"value"=>"第".$i."阶段或学期报告"];
        }
        return response()->json($result);
    }



    /**
     * @api {get} /api/stagereport/:Noid/list/:stage  学中报告内容
     *
     * @apiName  Stage_Report_show
     * @apiGroup report
     * @apiVersion 1.0.0
     *
     *@apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/stagereport/:Noid/list/:stage
     */
    public  function StageReport($Noid,$stage,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        $report=Stage_report_result::where(["stud_Noid"=>$Noid,"stage"=>$stage])->first();
        if ($report)return response()->json($report);

        $trule=Testrule::find(2);

        $student=User::where("Noid",$Noid)->first();
        if(!$student) return response()->json(["code"=>403,"msg"=>"student Noid is error"]);
        $nowtime=Carbon::now();
        if($Noid!=$opuser){
            $class=Cllass::where("headmaster_id",$opuser)->where("created_at","<",$nowtime)->where("end_at",">",$nowtime)->first();
            if(!$class) $class=Cllass::where("assistant_id".$opuser)->where("created_at","<",$nowtime)->where("end_at",">",$nowtime)->first();
            if(!$class) return response()->json(["code"=>403,"msg"=>"forbid access"]);
            if($class->id!=$student->class_id) return response()->json(["code"=>403,"msg"=>"forbid access"]);
            $class_id=$class->id;
        }else
        {
            $class_id=$student->class_id;
        }
        $test=Intest::where(["class_id"=>$class_id,"stage_id"=>$stage])->first();
        if(!$test) return response()->json(["code"=>403,"msg"=>"本阶段未进行测试"]);
        $testing= Intesting::where(["intest_id"=>$test->id,"user_id"=>$Noid])->first();
        if(!$testing) return response()->json(["code"=>403,"msg"=>"该生未进行本阶段的测试"]);
        if(!$testing->choise_score||!$testing->judg_score) return response()->json(["code"=>403,"msg"=>"未批改阶段测试 请联系班主任"]);
        $debate= Debate::where(["intest_id"=>$test->id,"user_id"=>$Noid])->first();
        if(!$debate) return response()->json(["code"=>403,"msg"=>"该生未进行本阶段的答辩"]);
        if(!$debate->score) return response()->json(["code"=>403,"msg"=>"未综合答辩结果 请联系班主任"]);

        $type6=$testing->choise_score*$trule->choice_rate+$testing->judg_score*$trule->judge_rate+$debate->score*$trule->project_rate;

        $eva=Evaluation::where(["stud_id"=>$Noid,"stage"=>$stage,"status"=>1])->groupBy("stud_id")
           ->select(\DB::raw('avg(morality) as type1'),\DB::raw('avg(citizen) as type2'),\DB::raw('avg(study) as type3'),\DB::raw('avg(cooperation) as type4'),\DB::raw('avg(sport) as type5'))->get();
        return $eva;

    }
}
