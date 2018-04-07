<?php

namespace App\Http\Controllers\Api;

use App\Models\Appoint;
use App\Models\Cllass;
use App\Models\Debate;
use App\Models\Defense;
use App\Models\Intest;
use App\Models\Intesting;
use App\Models\Question;
use App\Models\Qustype;
use App\Models\Testrule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IntestController extends Controller
{

    /**
     * @api {get} /api/intest/index  阶段测试列表
     *
     * @apiName  index
     * @apiGroup StageTest
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/intest/index
     */
    public  function  index(Request $request){
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
        $intest=Intest::where(["class_id"=>$class->id,"status"=>1])
            ->get();
        return response()->json($intest);
    }



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
      $result["stage"]=$stage;
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
          $Intests->status= 0;
          $Intests->save();
      }else{
          if($intests->status==1) return response()->json(["code"=>403,"msg"=>"this stage intest exist"]);
          $intest["choice"]=Question::select("*")->whereIn("id",explode(",",$intests->choiceid))->get();
          $intest["judgment"]=Question::select("*")->whereIn("id",explode(",",$intests->judgmentid))->get();
          $result["question"]=$intest;
      }
      return  response()->json($result);
  }


    /**
     * @api {get} /api/intest/choice  哪次测评选择
     *
     * @apiName  intest_choice
     * @apiGroup StageTest
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}   class_id 班级id  可选
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/intest/choice
     */
    public  function  choice_index(Request $request){

        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);


        $class_id=$request->get("class_id");
        $result["class"]=Cllass::all();
        if($class_id)$result["intest"]=Intest::where("class_id",$class_id)->get();
        return  response()->json($result);
    }


    /**
     * @api {post} /api/intest/complete/:stage  最终阶段测试稿
     *
     * @apiName  complete
     * @apiGroup StageTest
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}  Project_Name 项目名称 必须输入
     * @apiParam {string}  Project_Detail 项目要求详情 必须输入
     * @apiParam {string}  startime_at 开始时间 必须输入
     * @apiParam {string}  endtime_at 结束时间 必须输入
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/intest/complete/:stage
     */
    public  function  result_test($stage,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(13,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $name=$request->get("Project_Name");
        $detail=$request->get("Project_Detail");
        $start=$request->get("startime_at");
        $end=$request->get("endtime_at");
        if(!$name||!$detail||!$start||!$end)return response()->json(["code"=>403,"msg"=>"Loss Parameter"]);

        $now=Carbon::now();
        $class=Cllass::where("headmaster_id",$opuser)->where("end_at",'>',$now)
            ->leftJoin('patterns','classs.pattern_id','patterns.id')
            ->select("classs.*",'patterns.name','patterns.time')
            ->first();
        if(!$class) return response()->json(["code"=>403,"msg"=>"only open to head master"]);
        $intests=Intest::where(["stage_id"=>$stage,"class_id"=>$class->id,"status"=>0])->first();

        if(!$intests) return response()->json(["code"=>403,"msg"=>"Information anomaly"]);

        try{
               $intests->project_name=$name;
               $intests->project_detail=$detail;
               $intests->starttime_at=$start;
               $intests->endtime_at=$end;
               $intests->status=1;
               $intests->save();
               return response()->json(["code"=>200,"msg"=>"StageTest Release completion"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
    }



    /**
     * @api {post} /api/intest/edit/:id  修改阶段测试稿
     *
     * @apiName  edit
     * @apiGroup StageTest
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}  Project_Name 项目名称 必须输入
     * @apiParam {string}  Project_Detail 项目要求详情 必须输入
     * @apiParam {string}  startime_at 开始时间 必须输入
     * @apiParam {string}  endtime_at 结束时间 必须输入
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/intest/edit/:id
     */
    public  function  edit($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(13,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $name=$request->get("Project_Name");
        $detail=$request->get("Project_Detail");
        $start=$request->get("startime_at");
        $end=$request->get("endtime_at");
        if(!$name||!$detail||!$start||!$end)return response()->json(["code"=>403,"msg"=>"Loss Parameter"]);

        $now=Carbon::now();
        $class=Cllass::where("headmaster_id",$opuser)->where("end_at",'>',$now)
            ->leftJoin('patterns','classs.pattern_id','patterns.id')
            ->select("classs.*",'patterns.name','patterns.time')
            ->first();
        if(!$class) return response()->json(["code"=>403,"msg"=>"only open to head master"]);
        $intests=Intest::where(["status"=>1,"id"=>$id])->first();


        if(!$intests) return response()->json(["code"=>403,"msg"=>"Information anomaly"]);
        if($intests->class_id !=$class->id)return response()->json(["code"=>403,"msg"=>"forbid access"]);
        try{
            $intests->project_name=$name;
            $intests->project_detail=$detail;
            $intests->starttime_at=$start;
            $intests->endtime_at=$end;
            $intests->save();
            return response()->json(["code"=>200,"msg"=>"StageTest edit completion"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
    }


    /**
     * @api {get} /api/intest/index/:id  某次测试的详情
     *
     * @apiName  index
     * @apiGroup StageTest
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/intest/index/:id
     */
    public  function  showlist($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(13,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $class=Cllass::where("headmaster_id",$opuser)->where("end_at",'>',Carbon::now())->first();
        $intest=Intest::find($id);
        if(!$intest)return response()->json(["code"=>400,"msg"=>"intest id not exist"]);

        if($class->id!=$intest->class_id)return response()->json(["code"=>403,"msg"=>"this intest is not your class intest"]);

        $result["Not_Correct"]= $intestings=Intesting::where("intest_id",$id)->where("status",0)->get();
        $result["Corrected"]= $intestings=Intesting::where("intest_id",$id)->where("status",1)->get();

        return response()->json($intest);
    }


    /**
     * @api {post} /api/intest/defense/:intest_id  项目答辩打分
     *
     * @apiName  defense
     * @apiGroup StageTest
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}  Noid  学生学号
     * @apiParam {string}  role_id  角色_id
     * @apiParam {float}  projectscore 项目分
     * @apiParam {float}  debatescore 答辩分
     * @apiParam {float}  filescore 文档分
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/intest/defense/:intest_id
     */
    public  function  defense($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        $time=Carbon::now();
        $studNoid=$request->get("Noid");
        $role_id=$request->get("role_id");
        $projectscore=$request->get("projectscore");
        $debatescore=$request->get("debatescore");
        $filescore=$request->get("filescore");
        if (!$studNoid||!$role_id||!$projectscore||!$debatescore||!$filescore)return response()->json(["code"=>403,"msg"=>"parameter missing"]);

        $intest=Intest::where("id",$id)->where("starttime_at","<",$time)->where("endtime_at",">",$time)->first();
        if(!$intest) return response()->json(["code"=>403,"not opening id was ".$id." test"]);

        $appoint=Appoint::where(["Noid"=>$opuser,"role_id"=>$role_id])->first();
        if(!$appoint)return response()->json(["code"=>403,"msg"=>"role error"]);

        $debate=Debate::where(["user_id"=>$studNoid,"intest_id"=>$id])->first();
       if(!$debate)return response()->json(["code"=>403,"msg"=>"intest_id or stud_id error"]);

        $defense=Defense::where(["role_id"=>$role_id,"debate_id"=>$debate->id,"user_id"=>$opuser])->first();
         try{
             if (!$defense) {
                 $defense=new  Defense();
                 $defense->role_id=$role_id;
                 $defense->debate_id=$debate->id;
                 $defense->user_id=$opuser;
             }
             $defense->projectscore=$projectscore;
             $defense->debatescore=$debatescore;
             $defense->filescore=$filescore;
             $defense->save();
             return response()->json(["code"=>200,"msg"=>"success"]);

         }catch (\Exception $e){
             return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
         }

        return response()->json($appoint);
    }


}
