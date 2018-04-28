<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use App\Models\EnReport;
use App\Models\User;
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
     * @api {get} /api/enreport/:Noid  学中报告
     *
     * @apiName  Start_Report
     * @apiGroup report
     * @apiVersion 1.0.0
     *
     *@apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/enreport/:Noid
     */
    public  function StageReport($Noid,Request $request){
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
        $stageReport=[];
        if(!$stageReport->choicescore){
            $choreply=json_decode($stageReport->choreply,true);
            $cho_count=count($choreply);
            $cho_size=0;
            for($i=0;$i<$cho_count;$i++ )if ($choreply[$i]["answer"]==$choreply[$i]["userAnswer"])$cho_size++;
            $choicescore=($cho_size/$cho_count)*100;
            $stageReport->choicescore=$choicescore;
        }else $choicescore=$stageReport->choicescore;

        if(!$stageReport->judgscore){
            $judgreply=json_decode($stageReport->judgreply,true);
            $judg_count=count($judgreply);
            $judg_size=0;
            for($i=0;$i<$judg_count;$i++ )if ($judgreply[$i]["answer"]==$choreply[$i]["userAnswer"])$judg_size++;
            $judgscore=($judg_size/$judg_count)*100;
            $stageReport->judgscore=$judgscore;
        }else $judgscore=$stageReport->judgscore;


        $rule=Testrule::find(1);
        $sumscore=$choicescore*$rule->choice_rate+$judgscore*$rule->judge_rate+$stageReport*$rule->completion_rate+$stageReport*$rule->answer_rate;
        $stageReport->sumscore=$sumscore;
        $stageReport->save();
        return response()->json($stageReport);
    }
}
