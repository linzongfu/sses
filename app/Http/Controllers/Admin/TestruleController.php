<?php

namespace App\Http\Controllers\Admin;

use App\Models\Entesting;
use App\Models\Grarule;
use App\Models\Log;
use App\Models\Testrule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestruleController extends Controller
{
    /**
     * @api {get} /admin/testrule 测试规则列表
     *
     * @apiName testrule_list
     * @apiGroup systemManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/testrule
     */
    public function index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $role=Testrule::all();
        return response()->json($role);
    }


    /**
     * @api {put} /admin/testrule/edit/:id  编辑测试规则
     *
     * @apiName testrule_update
     * @apiGroup  systemManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{int} choice_count 选择题数量 可选(0-99)
     * @apiParam{int}  choice_rate 选择题权重 可选(0-99)%
     * @apiParam{int} judge_count 判断题数量 可选(0-99)
     * @apiParam{int}  judge_rate 判断题权重 可选(0-99)%
      * @apiParam{int} completion_count 填空题数量 可选(0-99)
     * @apiParam{int}  completion_rate 填空题权重 可选(0-99)%
     * @apiParam{int} answer_count 问答题数量 可选(0-99)
     * @apiParam{int} answer_rate 问答题数量 可选(0-99)%
     * @apiParam{int}  project_rate 选择题权重 可选(0-99)%
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/testrule/edit/:id
     */
    public function rule_edit($id,Request $request)
    {

        $opuser = $request->header("opuser");
        if (!$opuser) return response()->json(["code" => 401, "msg" => "未登录"]);
        if (!in_array(17, getfuncby($opuser)))
            return response()->json(["code" => 403, "msg" => "禁止访问"]);

        try {
            $input = $request->only(['choice_count','judge_count','completion_count','answer_count', 'choice_rate' , 'judge_rate' , 'completion_rate' , 'answer_rate' , 'project_rate' ,]);
            $validator = \Validator::make($input, [
                'choice_count' => 'nullable|integer|min:0|max:100',
                'judge_count' => 'nullable|integer|min:0|max:100',
                'completion_count' => 'nullable|integer|min:0|max:100',
                'answer_count' => 'nullable|integer|min:0|max:100',
                'choice_rate' => 'nullable|integer|min:0|max:100',
                'judge_rate' => 'nullable|integer|min:0|max:100',
                'completion_rate' => 'nullable|integer|min:0|max:100',
                'answer_rate' => 'nullable|integer|min:0|max:100',
                'project_rate' => 'nullable|integer|min:0|max:100',
            ]);

            if ($validator->fails()) return response()->json(['code' => 400, 'msg' => $validator->errors()]);
            $rule = Testrule::find($id);
            if (!$rule) return response()->json(["code" => 403, "msg" => "没有这个测试规则"]);
            if ($input['choice_count']) $rule->choice_count=$input['choice_count'];
            if ($input['judge_count']) $rule->judge_count=$input['judge_count'];
            if ($input['completion_count']) $rule->completion_count=$input['completion_count'];
            if ($input['answer_count']) $rule->answer_count=$input['answer_count'];

            if ($input['choice_rate']) $rule->choice_rate=$input['choice_rate']/100;
            if ($input['judge_rate']) $rule->judge_rate=$input['judge_rate']/100;
            if ($input['completion_rate']) $rule->completion_rate=$input['completion_rate']/100;
            if ($input['answer_rate']) $rule->answer_rate=$input['answer_rate']/100;
            if ($input['project_rate']) $rule->project_rate=$input['project_rate']/100;
            $rule->save();
                log_add($opuser, $request->getRequestUri(), $request->getClientIp(), "update", "修改".$rule->name."规则", 1);
                return response()->json(['code' => 200, 'msg' => '修改成功']);
            } catch (\Exception $e) {
                return response()->json(['code' => 400, "msg" => $e->getMessage()]);
            }

        }


    /**
     * @api {get} /admin/gratulaterule 结业报告规则
     *
     * @apiName gratulate_rule
     * @apiGroup systemManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/gratulaterule
     */
    public function gra_index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $role=Grarule::all();
        return response()->json($role);
    }


    /**
     * @api {put} /admin/gratulaterule/edit/:id  结业报告规则修改
     *
     * @apiName gratulate_update
     * @apiGroup  systemManage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam{int} daily 日常方面 1使用2禁止
     * @apiParam{int} personnel 人事经理方面 1使用2禁止
     * @apiParam{int} technology 技术经理方面 1使用2禁止
     * @apiParam{int} headmaster 班主任方面 1使用2禁止

     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/gratulaterule/edit/:id
     */
    public function grarule_edit($id,Request $request)
    {

        $opuser = $request->header("opuser");
        if (!$opuser) return response()->json(["code" => 401, "msg" => "未登录"]);
        if (!in_array(17, getfuncby($opuser)))
            return response()->json(["code" => 403, "msg" => "禁止访问"]);

        $input = $request->only(['daily','personnel','technology','headmaster']);
        $validator = \Validator::make($input, [
            'daily' => 'nullable|integer|min:0|max:3',
            'personnel' => 'nullable|integer|min:0|max:3',
            'technology' => 'nullable|integer|min:0|max:3',
            'headmaster' => 'nullable|integer|min:0|max:3'
        ]);

        if ($validator->fails()) return response()->json(['code' => 400, 'msg' => $validator->errors()]);
        try {

            $rule = Grarule::find($id);
            if (!$rule) return response()->json(["code" => 403, "msg" => "没有这个结业报告规则"]);
            if ($input['daily']) $rule->daily=$input['daily'];
            if ($input['personnel']) $rule->personnel=$input['personnel'];
            if ($input['technology']) $rule->technology=$input['technology'];
            if ($input['headmaster']) $rule->headmaster=$input['headmaster'];
            $rule->save();
            log_add($opuser, $request->getRequestUri(), $request->getClientIp(), "update", "修改结业报告规则", 1);
            return response()->json(['code' => 200, 'msg' => '修改成功']);
        } catch (\Exception $e) {
            return response()->json(['code' => 400, "msg" => $e->getMessage()]);
        }

    }

}
