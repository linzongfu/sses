<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Auth\ResetPasswordController;
use App\Models\Enmajortest;
use App\Models\Entesting;
use App\Models\Question;
use App\Models\Qustype;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /api/ChoiceTest
     */
    public function  index(Request $request){
        $opuser=$request->header("opuser");
        accessControl($opuser,6);
        
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
     * @apiHeaderExample {json} Header-Example:
     * {
     *      opuser
     * }
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/EnTest/:id
     */
    public function Entest($id,Request $request)
    {
        $opuser=$request->header("opuser");
        accessControl($opuser,6);

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

                $entest["choice"]=Qustype::find($mojor_id)->questions()->get();
                $entest["judgment"]=Qustype::find($mojor_id)->questions()->where("type",2)->orderBy(\DB::raw('RAND()'))->take(10)->get();;
                $entest["completion"]=Qustype::find($mojor_id)->questions()->where("type",3)->orderBy(\DB::raw('RAND()'))->take(5)->get();;
                $entest["answer"]=Qustype::find($mojor_id)->questions()->where("type",4)->orderBy(\DB::raw('RAND()'))->take(5)->get();;
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
                    return response()->json(["code"=>200,"msg"=>"you are already tested"]);
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(6,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
