<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use App\Models\Intest;
use App\Models\Qustype;
use App\Models\Testrule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IntestController extends Controller
{
    /**
     * @api {get} /api/intest/generate  随机生成试卷
     *
     * @apiName  test
     * @apiGroup StageTest
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/intest/generate
     */
  public  function  Generate(Request $request){
      $opuser=$request->header("opuser");
      if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

      if(!in_array(13,getfuncby($opuser)))
          return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

      $now=Carbon::now();
      $class=Cllass::where("headmaster_id",$opuser)->where("end_at",'>',$now)
          ->leftJoin('patterns','classs.pattern_id','patterns.id')
          ->select("classs.*",'patterns.name','patterns.time')
          ->first();
      if(!$class) return response()->json(["code"=>403,"msg"=>"only open to head master"]);
      $create=Carbon::parse($class->created_at);
      $stage=floor($now->diffInMonths($create)/$class->time)+1;
      $intests=Intest::where(["stage_id"=>$stage,"class_id"=>$class->id])->first();
      if(!$intests){
          $rule=Testrule::find(2);

          $mojor_id=$class->major_id;
          $intest["choice"]=Qustype::find($mojor_id)->questions()->where(["type"=>0,"level"=>1])->orderBy(\DB::raw('RAND()'))->take($rule->choice_count)->get();
          $intest["judgment"]=Qustype::find($mojor_id)->questions()->where(["type"=>2,"level"=>1])->orderBy(\DB::raw('RAND()'))->take($rule->judge_count)->get();
          $result["question"]=$intest;

          $Intests=new Intest();
          $Intests->class_id=$class->id;
          $Intests->stage_id=$stage;
          $Intests->choiceid= implode(",", getArraybystr( $intest["choice"],"id"));
          $Intests->judgmentid= implode(",", getArraybystr( $intest["judgment"],"id"));
          $Intests->save();
      }else{
          $intest["choice"]=Question::select("*")->whereIn("id",explode(",",$intests->choiceid))->get();
          $intest["judgment"]=Question::select("*")->whereIn("id",explode(",",$intests->judgmentid))->get();
          $result["question"]=$intest;
      }
      return $result;
  }
}
