<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Auth\ResetPasswordController;
use App\Models\Cllass;
use App\Models\Enmajortest;
use App\Models\Entesting;
use App\Models\Label;
use App\Models\Question;
use App\Models\Qustype;
use App\Models\Testrule;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Psy\Test\CodeCleaner\MagicConstantsPassTest;

class EntestController extends Controller
{
    /**
     * @api {get} /api/ChoiceTest 选择入学测试类型
     *
     * @apiName ChoiceTest
     * @apiGroup EntrTest
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /api/ChoiceTest
     */
    public function  index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(6,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

       $questypes=Qustype::select("*")->where(["status"=>0,"pid"=>"0"])->get();
       return response()->json($questypes);
   }

    /**
     * @api {get} /api/EnTest/:id  进入入学测试
     *
     * @apiName question
     * @apiGroup EntrTest
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/EnTest/:id
     */
    public function Entest($id,Request $request)
    {
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(6,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $result=[];
        if ($id==1){  //性格测试
            $est=Entesting::where(["entest_id"=>$id,"user_id"=>$opuser])->first();
            if(!$est){ //判断是否申请过
                $qt=Qustype::select("id")->where(["pid"=>$id,"status"=>0])->get();   //没有 生成测试题库
                $randmax=$qt->count()-1;
                $qt=getArraybystr($qt,"id");
                $quetype=$qt[rand(0,$randmax)];
                $result["questype"]=Qustype::find($quetype);
                $entest=Qustype::find($quetype)->questions() ->get();
                $result["question"]=$entest;

                $ente=new Entesting();
                $ente->entest_id=$id;
                $ente->user_id=$opuser;
                $ente->questype_id=$quetype;
                $ente->questions=implode(",",getArraybystr($entest,'id'));
                $ente->save();

            }else   //有  更具用户是否回答过再进行判断
            {
                if($est->useranswers){
                    return response()->json(["code"=>400,"msg"=>"you are already tested"]);
                }else{
                    $result["questype"]=Qustype::find($est->questype_id);
                    $result["question"]=Question::select("*")->whereIn("id",explode(",",$est->questions))->get();
                }
            }
        }
        else if($id==2)
        {
            $est=Enmajortest::where("user_id",$opuser)->first();
            if(!$est){
                $mojor_id=  getArraybystr(User::select("major_id")->where("Noid",$opuser)->get(),"major_id")[0];
                $result["questype"]=Qustype::select("*")->where("belongto",$mojor_id)->get()[0];
                if (!$result["questype"]) return response()->json(["code"=>400,"msg"=>"请联系管理员反馈个人信息"]);

                $rule=Testrule::find(1);

                $entest["choice"]=Qustype::find($mojor_id)->questions()->where(["type"=>0,"level"=>0])->orderBy(\DB::raw('RAND()'))->take($rule->choice_count)->get();
                $entest["judgment"]=Qustype::find($mojor_id)->questions()->where(["type"=>2,"level"=>0])->orderBy(\DB::raw('RAND()'))->take($rule->judge_count)->get();
                $entest["completion"]=Qustype::find($mojor_id)->questions()->where(["type"=>3,"level"=>0])->orderBy(\DB::raw('RAND()'))->take($rule->completion_count)->get();
                $entest["answer"]=Qustype::find($mojor_id)->questions()->where(["type"=>4,"level"=>0])->orderBy(\DB::raw('RAND()'))->take($rule->answer_count)->get();
                $result["question"]=$entest;

                $enmajortest=new Enmajortest();
                $enmajortest->questype_id=$mojor_id;
                $enmajortest->user_id=$opuser;
                $enmajortest->choiceid= implode(",", getArraybystr( $entest["choice"],"id"));
                $enmajortest->judgmentid= implode(",", getArraybystr( $entest["judgment"],"id"));
                $enmajortest->completionid= implode(",", getArraybystr( $entest["completion"],"id"));
                $enmajortest->answerid= implode(",", getArraybystr( $entest["answer"],"id"));
                $enmajortest->save();
            }else {
                if(!$est->choreply||!$est->judgreply||!$est->comrelpy||!$est->ansreply){
                    $result["questype"]=Qustype::find($est->questype_id);

                    $entest["choice"]=Question::select("*")->whereIn("id",explode(",",$est->choiceid))->get();
                    $entest["judgment"]=Question::select("*")->whereIn("id",explode(",",$est->judgmentid))->get();
                    $entest["completion"]=Question::select("*")->whereIn("id",explode(",",$est->completionid))->get();
                    $entest["answer"]=Question::select("*")->whereIn("id",explode(",",$est->answerid))->get();
                    $result["question"]=$entest;
                }else{
                    return response()->json(["code"=>400,"msg"=>"you are already tested"]);
                }
            }
        }
        else if($id==3){
                 $est=Entesting::where(["entest_id"=>$id,"user_id"=>$opuser])->first();
                 if(!$est){

                     $branch= getArraybystr(User::select("branch")->where("Noid",$opuser)->get(),"branch")[0];

                     if (!($branch==1||$branch==2)) return response()->json(["code"=>400,"msg"=>"请联系管理员反馈个人信息"]);

                     $result["questype"]=Qustype::select("*")->where("belongto",$branch)->get()[0];


                     $entest=Qustype::find( $result["questype"]->id)->questions() ->get();
                     $result["question"]=$entest;


                     $ente=new Entesting();
                     $ente->entest_id=$id;
                     $ente->user_id=$opuser;
                     $ente->questype_id=$result["questype"]->id;
                     $ente->questions=implode(",",getArraybystr($entest,'id'));
                     $ente->save();
                }
                else {
                    if($est->useranswers){
                        return response()->json(["code"=>400,"msg"=>"you are already tested"]);
                    }else{
                        $result["questype"]=Qustype::find($est->questype_id);
                        $result["question"]=Question::select("*")->whereIn("id",explode(",",$est->questions))->get();
                    }
                }
        }

      $result["count"]=count($result["question"]);
        return response()->json($result);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * @api {post} /api/EnTest/Submit  提交测试结果
     *
     * @apiName submitTest
     * @apiGroup EntrTest
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {int}  result 结果标签id
     * @apiParam {int}  entest_id 测试类型id
     * @apiParam {string}  useranswer 用户回答
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/EnTest/Submit
     */
    public function store(Request $request)
    {
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(6,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $entest_id=$request->get("entest_id");
        if(!$entest_id) return response()->json(['code'=>400,'msg'=>'参数错误']);
        $entest_pid=Qustype::find($entest_id)->pid;

        if ($entest_pid!=2)
        {
            $useranswer=$request->get("useranswer");
            $result=$request->get("result");
            if(!$result||!$useranswer)
                 return response()->json(['code'=>400,'msg'=>'missing result or useranser']);

            $entest=Entesting::select("*")->where(['entest_id'=>$entest_pid,'user_id'=>$opuser])->first();
            if (empty($entest)){
                return response()->json(['code'=>403,'msg'=>'请先进行入学测试']);
            }else{
                if($entest->useranswers)
                    return response()->json(['code'=>403,'msg'=>'你已经提交测试']);
            }

            $entest->useranswers=$useranswer;
            $entest->save();
            if ($entest_pid==1)
                User::where('Noid',$opuser)->update(["characterlabel_id"=>$result]);
            else if($entest_pid==2)
                User::where('Noid',$opuser)->update(["branchlabel_id"=>$result]);
            else  return response()->json(['code'=>400,'msg'=>'参数错误']);

        }else {
            $choice=$request->get("choice");
            $judgment=$request->get("judgment");
            $completion=$request->get("completion");
            $answer=$request->get("answer");
            if(!$choice){
                return response()->json(['code'=>400,'msg'=>'missing choice']);
            }
            if(!$judgment){
                return response()->json(['code'=>400,'msg'=>'missing judgment']);
            }
            if(!$completion){
                return response()->json(['code'=>400,'msg'=>'missing completion']);
            }
            if(!$answer){
                return response()->json(['code'=>400,'msg'=>'missing answer']);
            }
            $entest=Enmajortest::select("*")->where(['questype_id'=>$entest_id,"user_id"=>$opuser])->first();
            if (empty($entest)){
                return response()->json(['code'=>403,'msg'=>'请先进行入学测试']);
            }else{
                if($entest->sumscore)
                    return response()->json(['code'=>403,'msg'=>'你已经提交测试']);
            }
            $entest->choreply=$choice;
            $entest->judgreply=$judgment;
            $entest->comrelpy=$completion;
            $entest->ansreply=$answer;
            $entest->save();
        }
        return response()->json(['code'=>200,'msg'=>"提交成功"]);


    }


    /**
     * @api {post} /api/EnTest/show  查看学生入学测试结果
     *
     * @apiName show
     * @apiGroup EntrTest
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}  Stud_Noid 学生学号  自己班级学生的学号
     * @apiParam {int}  Entest_Id 测试类型  性格文理专业测试
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/EnTest/show
     */
    public function show(Request $request)
    {
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(11,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $stud=$request->get("Stud_Noid");
        $entest_id=$request->get("Entest_Id");
        if(!$stud||!$entest_id) return response()->json(["code"=>403,"msg"=>"missing Stud_Noid or Entest_id"]);

        $student=User::where("Noid",$stud)->first();
        if(!$student) return response()->json(["code"=>403,"msg"=>"student information is error"]);
        $class=Cllass::where("id",$student->class_id)->first();

        if($opuser!=$class->headmaster_id) return response()->json(["code"=>403,"msg"=>"forbid access"]);
        if($entest_id==1){
            $result["Test_Name"]=Qustype::find($entest_id);
            if($student->characterlabel_id) return response()->json(["code"=>403,"msg"=>"this student No character test"]);
            $result["label"]=Label::find($student->characterlabel_id);
        }else if($entest_id==3){
            $result["Test_Name"]=Qustype::find($entest_id);
            if($student->branchlabel_id) return response()->json(["code"=>403,"msg"=>"this student No thinking test"]);
            $result["label"]=Label::find($student->branchlabel_id);
        }else if($entest_id==2){
            $result["Test_Name"]=Qustype::find($entest_id);
            if($student->majorlabel_id) return response()->json(["code"=>403,"msg"=>"this student No major test"]);

            $major=Enmajortest::where("user_id",$student->Noid)->first();
            if(!$major) return response()->json(["code"=>403,"msg"=>"this student No major test"]);

            if(!$major->choicescore||!$major->judgscore||!$major->complescore||!$major->answerscore||!$major->sumscore){
                return response()->json(["code"=>403,"msg"=>"the student's test paper has not been corrected"]);
            }

            $result["major"]=$major->$major;

            $result["label"]=Label::find($student->majorlabel_id);
        }

        return response()->json($result);
    }

    /**
     * @api {get} /api/EnTest/Correct/:Noid  请求批改作业
     *
     * @apiName Correct
     * @apiGroup EntrTest
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/EnTest/Correct/:Noid
     */
    public function Correct($Noid,Request $request)
    {
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(11,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $class=Cllass::where("headmaster_id",$opuser)->first();
       if(!$class)$class=Cllass::where("assistant_id",$opuser)->first();
        if(!$class) return response()->json(["code"=>403,'msg'=>"You are not the headteacher or assistant can't continue to operate"]);
        $user=User::where("Noid",$Noid)->first();
        if(!$user) return response()->json(["code"=>403,"msg"=>"this student not exist"]);
        if($user->class_id!=$class->id) return response()->json(["code"=>403,'msg'=>"You are not the headteacher or assistant can't continue to operate"]);


        $major_test=Enmajortest::where("user_id",$user->Noid)->first();
        $major_test->choreply=json_decode($major_test->choreply,true);
        $major_test->judgreply=json_decode($major_test->judgreply,true);
        $major_test->comrelpy=json_decode($major_test->comrelpy,true);
        $major_test->ansreply=json_decode($major_test->ansreply,true);
        return response()->json($major_test);

    }


    /**
     * @api {post} /api/EnTest/Corrected/:Noid  批改好作业提交
     *
     * @apiName Corrected
     * @apiGroup EntrTest
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {float}  Com_Score 填空题答案
     * @apiParam {float}  Ans_Score 问答题分数
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/EnTest/Corrected/:Noid
     */
    public function Corrected($Noid,Request $request)
    {
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(11,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $class=Cllass::where("headmaster_id",$opuser)->first();
        if(!$class)$class=Cllass::where("assistant_id",$opuser)->first();
        if(!$class) return response()->json(["code"=>403,'msg'=>"You are not the headteacher or assistant can't continue to operate"]);
        $user=User::where("Noid",$Noid)->first();
        if(!$user) return response()->json(["code"=>403,"msg"=>"this student not exist"]);
        if($user->class_id!=$class->id) return response()->json(["code"=>403,'msg'=>"You are not the headteacher or assistant can't continue to operate"]);

        $major_test=Enmajortest::where("user_id",$user->Noid)->first();



        $com_score=$request->get("Com_Score");
        $ans_score=$request->get("Ans_Score");
        if(!$com_score||!$ans_score) return response()->json(["code"=>403,"msg"=>"pleace enter Com_Score and Ans_Score"]);

        try{

            if(!$major_test->choicescore){
                $choreply=json_decode($major_test->choreply,true);
                $cho_count=count($choreply);
                $cho_size=0;
                for($i=0;$i<$cho_count;$i++ )if ($choreply[$i]["answer"]==$choreply[$i]["userAnswer"])$cho_size++;
                $choicescore=($cho_size/$cho_count)*100;
                $major_test->choicescore=$choicescore;
            }else $choicescore=$major_test->choicescore;

            if(!$major_test->judgscore){
                $judgreply=json_decode($major_test->judgreply,true);
                $judg_count=count($judgreply);
                $judg_size=0;
                for($i=0;$i<$judg_count;$i++ )if ($judgreply[$i]["answer"]==$choreply[$i]["userAnswer"])$judg_size++;
                $judgscore=($judg_size/$judg_count)*100;
                $major_test->judgscore=$judgscore;
            }else $judgscore=$major_test->judgscore;

            $major_test->complescore=$com_score;
            $major_test->answerscore=$ans_score;
            $rule=Testrule::find(1);
            $sumscore=$choicescore*$rule->choice_rate+$judgscore*$rule->judge_rate+$com_score*$rule->completion_rate+$ans_score*$rule->answer_rate;
            $major_test->sumscore=$sumscore;
            $major_test->save();
            $user->majorlabel_id=$sumscore>60?21:22;
            $user->save();
            return response()->json(["code"=>200,"msg"=>"Correct success"]);

        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
