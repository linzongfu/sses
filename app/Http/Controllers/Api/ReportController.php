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
}
