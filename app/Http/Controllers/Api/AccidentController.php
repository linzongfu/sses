<?php

namespace App\Http\Controllers\Api;

use App\Models\Accident;
use App\Models\Cllass;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccidentController extends Controller
{
    /**
     * @api {get}  /api/accident/:id  某学生的事件详情
     *
     * @apiName  index
     * @apiGroup Accident
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/accident/:id
     */
    public function show($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(9,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $class=Cllass::where("headmaster_id",$opuser)->first();
        if(!$class)$class=Cllass::where("assistant_id",$opuser)->first();
        if (!$class) return response()->json(["code"=>403,'msg'=>"You are not the headteacher or assistant can't continue to operate"]);
        $user=User::where("Noid",$id)->first();
        if($user->class_id!=$class->id) return response()->json(["code"=>403,'msg'=>"You are not the headteacher or assistant can't continue to operate"]);

        $accident=Accident::where("accidents.student_id",$id)
            //->leftJoin("user")

        return $user;
    }
}
