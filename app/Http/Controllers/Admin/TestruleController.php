<?php

namespace App\Http\Controllers\Admin;

use App\Models\Entesting;
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
                'choice_count' => 'nullable|integer|min:0|max:2',
                'judge_count' => 'nullable|integer|min:0|max:2',
                'completion_count' => 'nullable|integer|min:0|max:2',
                'answer_count' => 'nullable|integer|min:0|max:2',
                'choice_rate' => 'nullable|integer|min:0|max:2',
                'judge_rate' => 'nullable|integer|min:0|max:2',
                'completion_rate' => 'nullable|integer|min:0|max:2',
                'answer_rate' => 'nullable|integer|min:0|max:2',
                'project_rate' => 'nullable|integer|min:0|max:2',
            ]);
            if ($validator->fails()) return response()->json(['code' => 400, 'msg' => $validator->errors()]);
            $rule = Testrule::find($id);
            if (!$rule) return response()->json(["code" => 403, "msg" => "没有这个测试规则"]);
            if ($input['choice_count']) $rule->
                log_add($opuser, $request->getRequestUri(), $request->getClientIp(), "update", "修改视图权限" . $input['remark'], 1);
                return response()->json(['code' => 200, 'msg' => '添加成功']);
            } catch (\Exception $e) {
                return response()->json(['code' => 400, "msg" => $e->getMessage()]);
            }

        }

}
