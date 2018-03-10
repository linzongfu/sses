<?php

namespace App\Http\Controllers\Api;

use App\Models\Entesting;
use App\Models\Qustype;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EntestController extends Controller
{


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
     *      opuser: Noid
     * }
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
        if (!$id) response()->json(["code"=>400,"msg"=>"pleace enter quetype"]);

        $result=[];
        $est=Entesting::where(["entest_id"=>$id,"user_id"=>$opuser])->first();
        if(!$est){
            if ($id==1){
                $qt=Qustype::select("id")->where("pid",$id)->get();
                $randmax=$qt->count()-1;
                $qt=getArraybystr($qt,"id");
                $quetype=$qt[rand(0,$randmax)];
                $result["questype"]=Qustype::find($quetype);
                $entest=Qustype::find($quetype)->questions() ->get();
                $result["question"]=$entest;

            }else if($id==2){
                $qt=Qustype::select("id")->where("pid",$id)->get();
                $randmax=$qt->count()-1;
                $qt=getArraybystr($qt,"id");
                $quetype=$qt[rand(0,$randmax)];
                $result["questype"]=Qustype::find($quetype);
                $entest=Qustype::find($quetype)->questions()->orderBy(\DB::raw('RAND()'))->take(30)->orderBy("type")->get();
                $result["question"]=$entest;
            }else if($id==3){
                $qt=Qustype::select("id")->where("pid",$id)->get();
                $randmax=$qt->count()-1;
                $qt=getArraybystr($qt,"id");
                $quetype=$qt[rand(0,$randmax)];
                $result["questype"]=Qustype::find($quetype);
                $entest=Qustype::find($quetype)->questions()->orderBy(\DB::raw('RAND()'))->take(30)->get();
                $result["question"]=$entest;
            }
            $entesting=new Entesting();
            $entesting->user_id=$opuser;
            $entesting->entest_id=$id;
            $entesting->questype_id=$quetype;
            $entesting->questions=$entest;
            $entesting->save();
        }else{
            if($est->useranswers){
                return response()->json(["code"=>200,"msg"=>"you are already tested"]);
            }else{
                $result["questype"]=Qustype::find($est->questype_id);

                $result["question"]=$est->questions;
            }
        }



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
        //
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
