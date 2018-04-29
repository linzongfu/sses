<?php

namespace App\Http\Controllers\Admin;

use App\Models\EnReport;
use App\Models\Label;
use App\Models\Major;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    /**
     * @api {get} /admin/report/entrance/index 入学报告列表
     *
     * @apiName entrance_index
     * @apiGroup report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {int}  page 页码 默认第一页
     * @apiParam {int}  limit 显示条数 默认10
     * @apiParam {int}  major_id 专业id
     * @apiParam {int}  characterlabel_id 性格标签
     * @apiParam {int}  majorlabel_id 专业标签
     * @apiParam {int}  branchlabel_id 文理标签
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/report/entrance/index
     */
    public function enindex(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $input=$request->only(['major_id','characterlabel_id','majorlabel_id','branchlabel_id']);

        $enreport=EnReport::whereNotNull("id");
        if($input["major_id"])$enreport=$enreport->where("major_id",$input["major_id"]);
        if($input["characterlabel_id"])$enreport=$enreport->where("characterlabel_id",$input["characterlabel_id"]);
        if($input["majorlabel_id"])$enreport=$enreport->where("majorlabel_id",$input["majorlabel_id"]);
        if($input["branchlabel_id"])$enreport=$enreport->where("branchlabel_id",$input["branchlabel_id"]);

        $page=$request->get('page');
        $limit=$request->get('limit');
        if(!$limit) $limit=10;
        $page=$page?$page-1:0;

        $result["count"]=$enreport->count();
        $start=$page*$limit;
        $result["enreport"]= $enreport->skip($start)->take($limit)->orderBy("created_at",'desc')->get();
        return response()->json($result);
    }

    /**
     * @api {get} /admin/report/entrance/where 入学报告列表选择
     *
     * @apiName entrance_index_where
     * @apiGroup report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/report/entrance/where
     */
    public function enwhere(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $result["major"]=Major::all();
        $result["characterlabel"]=Label::where("type",1)->get();
        $result["majorlabel"]=Label::where("type",3)->get();
        $result["branchlabel"]=Label::where("type",2)->get();
        return response()->json($result);
    }
}
