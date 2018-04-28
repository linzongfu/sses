<?php

namespace App\Http\Controllers\Api;

use App\Models\Cllass;
use App\Models\Evaluation;
use App\Models\Teach;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EvaluController extends Controller
{
    /**
     * @api {get} /api/evaluation/front  教师评教获取参数
     *
     * @apiName  front
     * @apiGroup Evaluation
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/evaluation/front
     */
    public function front(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        if(!in_array(5,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $now=Carbon::now();

        $class=Teach::where("teaches.starttime",'<',$now)->where("teaches.endtime",'>',$now)->where("teaches.teach_id",$opuser)
            ->leftJoin("classs","teaches.class_id","classs.id")
            ->groupBy("classs.id","classs.name")
            ->select("classs.id","classs.name")->get();
        return $class;
    }

    /**
     * @api {post} /api/evaluation/choice  学生选择
     *
     * @apiName  choice
     * @apiGroup Evaluation
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}   class_id  班级id  讲师必选
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/evaluation/choice
     */
    public function choice(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        $user=User::where("Noid",$opuser)->first();
        if(!$user) return response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $classid=$request->get("class_id");
       if (!$classid)$classid=$user->class_id;
       if (!$classid){
           $time=Carbon::now();
           $class=Cllass::where("headmaster_id")->where("end_at",'>',$time)->first();
           if($class) $classid=$class->id;
       }
        if (!$classid){
            $time=Carbon::now();
            $class=Cllass::where("assistant_id")->where("end_at",'>',$time)->first();
            if($class) $classid=$class->id;
        }
        if(!$classid) return response()->json(["code"=>403,"msg"=>"不允许操作"]);

        $stud=User::where("class_id",$classid)->select("Noid","name")->get();
        return response()->json($stud);
    }

    /**
     * @api {post} /api/evaluation/evaluating  测评提交
     *
     * @apiName  evaluating
     * @apiGroup Evaluation
     * @apiVersion 1.0.0
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {int}   role_id  角色id  自评为0
     * @apiParam {int}   morality  道德评分  1-10
     * @apiParam {int}   citizen  公民素质 1-10
     * @apiParam {int}   study  学习能力 1-10
     * @apiParam {int}   cooperation  团队交际  1-10
     * @apiParam {int}   sport  体育  1-10
     * @apiParam {int}   Noid  对方学号
     * @apiParam {string}   summary  总结
     * @apiParam {string}   autograph 签名
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /api/evaluation/evaluating
     */
    public function add(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        $input = $request->only(['role_id','morality','citizen','study', 'cooperation' , 'sport' , 'Noid' , 'summary','autograph']);
        $validator = \Validator::make($input, [
            'role_id' => 'required|integer|min:0|max:6',
            'morality' => 'required|integer|min:0|max:10',
            'citizen' => 'required|integer|min:0|max:10',
            'study' => 'required|integer|min:0|max:10',
            'cooperation' => 'required|integer|min:0|max:10',
            'sport' => 'required|integer|min:0|max:10',
            'Noid' => 'required',
            'summary' => 'required',
            'autograph'=> 'required'
        ]);
        if ($validator->fails()) return response()->json(['code' => 400, 'msg' => $validator->errors()]);

       try{
            $stud=User::where("Noid",$input['Noid'])->first();
            $class=Cllass::where("classs.id",$stud->class_id)
                ->leftJoin('patterns','classs.pattern_id','patterns.id')
                ->select("classs.*",'patterns.name','patterns.time')
                ->first();

            $create=Carbon::parse($class->created_at);
            $now=Carbon::now();
            $stage=floor($now->diffInMonths($create)/$class->time)+1;
            $evaluation=Evaluation::where([
                [ "user_id",$opuser],
                ["stud_id",$input['Noid']],
                ["role",$input["role_id"]],
                ["stage",$stage]
            ])->first();
            if($evaluation)return response()->json(["code"=>403,"msg"=>"you are alread evaluated"]);
            $evaluation =new Evaluation();
            $evaluation->stud_id=$input['Noid'];
            $evaluation->user_id=$opuser;
            $evaluation->role=$input["role_id"];
            $evaluation->stage=$stage;
            $evaluation->morality=$input['morality'];
            $evaluation->study=$input['study'];
            $evaluation->citizen=$input['citizen'];
            $evaluation->cooperation=$input['cooperation'];
            $evaluation->sport=$input['sport'];
            $evaluation->summary=$input['summary'];
            $evaluation->autograph=$input['autograph'];
            $evaluation->sumscore=2*($input['morality']+$input['study']+$input['citizen']+$input['cooperation']+$input['sport']);
            $evaluation->status=1;
            $evaluation->save();

            return response()->json(["code"=>200,"msg"=>"evaluation sucess"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,$e->getMessage()]);
       }

        return response()->json($stud);
    }
    public function jieye_evaluation(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);

        $input = $request->only(['role_id','morality','citizen','study', 'cooperation' , 'sport' , 'Noid' , 'summary','autograph']);
        $validator = \Validator::make($input, [
            'role_id' => 'required|integer|min:0|max:6',
            'morality' => 'required|integer|min:0|max:10',
            'citizen' => 'required|integer|min:0|max:10',
            'study' => 'required|integer|min:0|max:10',
            'cooperation' => 'required|integer|min:0|max:10',
            'sport' => 'required|integer|min:0|max:10',
            'Noid' => 'required',
            'summary' => 'required',
            'autograph'=> 'required'
        ]);
        if ($validator->fails()) return response()->json(['code' => 400, 'msg' => $validator->errors()]);

        try{
            $stud=User::where("Noid",$input['Noid'])->first();
            $class=Cllass::where("classs.id",$stud->class_id)
                ->leftJoin('patterns','classs.pattern_id','patterns.id')
                ->select("classs.*",'patterns.name','patterns.time')
                ->first();

            $create=Carbon::parse($class->created_at);
            $now=Carbon::now();
            $stage=floor($now->diffInMonths($create)/$class->time)+1;
            $evaluation=Evaluation::where([
                [ "user_id",$opuser],
                ["stud_id",$input['Noid']],
                ["role",$input["role_id"]],
                ["stage",$stage]
            ])->first();
            if($evaluation)return response()->json(["code"=>403,"msg"=>"you are alread evaluated"]);
            $evaluation =new Evaluation();
            $evaluation->stud_id=$input['Noid'];
            $evaluation->user_id=$opuser;
            $evaluation->role=$input["role_id"];
            $evaluation->stage=$stage;
            $evaluation->morality=$input['morality'];
            $evaluation->study=$input['study'];
            $evaluation->citizen=$input['citizen'];
            $evaluation->cooperation=$input['cooperation'];
            $evaluation->sport=$input['sport'];
            $evaluation->summary=$input['summary'];
            $evaluation->autograph=$input['autograph'];
            $evaluation->sumscore=2*($input['morality']+$input['study']+$input['citizen']+$input['cooperation']+$input['sport']);
            $evaluation->status=1;
            $evaluation->save();

            return response()->json(["code"=>200,"msg"=>"evaluation sucess"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,$e->getMessage()]);
        }

        return response()->json($stud);
    }


}
