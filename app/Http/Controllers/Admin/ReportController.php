<?php

namespace App\Http\Controllers\Admin;

use App\Models\EnReport;
use App\Models\Label;
use App\Models\Major;
use App\Models\Stagereport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    /**
     * @api {get} /admin/report/entrance/index 入学报告列表
     *
     * @apiName entrance_index
     * @apiGroup Entrance_Report
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
     * @apiGroup Entrance_Report
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


    /**
     * @api {delete} /admin/report/entrance/delete/:id 删除入学报告
     *
     * @apiName entrance_delete
     * @apiGroup Entrance_Report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/report/entrance/delete/:id
     */
    public function endelete($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $report=EnReport::find($id);
        if(!$report) return  response()->json(["code"=>403,"msg"=>"不存在的入学报告"]);
        try{
            $report->delete();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"delete","删除视图入学报告",1);
            return response()->json(["code"=>200,"msg"=>"删除成功"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
    }


    /**
     * @api {post} /admin/report/entrance/create 添加入学报告
     *
     * @apiName entrance_create
     * @apiGroup Entrance_Report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {int}  major_id 专业id
     * @apiParam {int}  characterlabel_id 性格标签
     * @apiParam {int}  majorlabel_id 专业标签
     * @apiParam {int}  branchlabel_id 文理标签
     * @apiParam {string}  content 报告内容
     *
     * @apiSuccess {array} data
     * @apiSampleRequest  /admin/report/entrance/create
     */
    public function encreate(Request $request){
        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);

        $input=$request->only(['major_id','characterlabel_id','majorlabel_id','content',"branchlabel_id"]);

        $validator = \Validator::make($input,[
            'major_id'=>'required|alpha_num|max:10|min:1',
            'characterlabel_id'=>'required|alpha_num|max:10|min:1',
            'majorlabel_id'=>'required|alpha_num|max:10|min:1',
            'branchlabel_id'=>'required|alpha_num|max:10|min:1',
            'content'=>'required',
        ]);
        if ($validator->fails()) return response()->json(['code'=>400,'msg'=>'参数错误']);
       try{


            $report=EnReport::where(["major_id"=>$input["major_id"],"characterlabel_id"=>$input["characterlabel_id"],"majorlabel_id"=>$input["majorlabel_id"],"branchlabel_id"=>$input["branchlabel_id"]])->first();
            if($report)return response()->json(["code"=>403,"msg"=>"已经存在"]);

            $per =new EnReport();
            $per->major_id=$input["major_id"];
            $per->characterlabel_id=$input['characterlabel_id'];
            $per->majorlabel_id=$input['majorlabel_id'];
            $per->branchlabel_id=$input['branchlabel_id'];
            $per->content=$input['content'];
            $per->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"create","添加入学报告",1);
            return response()->json(['code'=>200,'msg'=>'添加成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
      }

    }


    /**
     * @api {put} /admin/report/entrance/edit/:id  编辑入学报告
     *
     * @apiName entrance_update
     * @apiGroup  Entrance_Report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}  content 报告内容
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/report/entrance/edit/:d
     */
    public function enedit($id,Request $request){

        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);
        $con=$request->get("content");
        if(!$con)return response()->json(["code"=>403,"msg"=>"请输入content"]);
        try{
            $report =EnReport::find($id);
            if(!$report)return response()->json(["code"=>403]);
            $report->content=$con;
            $report->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"update","修改入学报告",1);
            return response()->json(['code'=>200,'msg'=>'修改成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
        }
    }




    /**
     * @api {get} /admin/report/stage/index 阶段报告列表
     *
     * @apiName stage_index
     * @apiGroup Stage_Report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {int}  page 页码 默认第一页
     * @apiParam {int}  limit 显示条数 默认10
     * @apiParam {int}  type 类型
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/report/stage/index
     */
    public function stageindex(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $input=$request->only(['type']);

        $stagereport=Stagereport::where("status",1);
        if($input["type"])$stagereport=$stagereport->where("type",$input["type"]);


        $page=$request->get('page');
        $limit=$request->get('limit');
        if(!$limit) $limit=10;
        $page=$page?$page-1:0;

        $result["count"]=$stagereport->count();
        $start=$page*$limit;
        $result["stage_report"]= $stagereport->skip($start)->take($limit)->get();
        return response()->json($result);
    }
    /**
     * @api {get} /admin/report/stage/where 阶段报告列表选择
     *
     * @apiName stage_index_where
     * @apiGroup Stage_Report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/report/stage/where
     */
    public function stagewhere(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $result=[1=>"道德" , 2=>"素质",3=>"学习能力" , 4=>"团队交际",5=>"体育" ,6=>"专业成绩"];
        return response()->json($result);
    }


    /**
     * @api {delete} /admin/report/stage/delete/:id 删除阶段报告
     *
     * @apiName stage_delete
     * @apiGroup Stage_Report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/report/stage/delete/:id
     */
    public function stagedelete($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $report=Stagereport::find($id);
        if(!$report) return  response()->json(["code"=>403,"msg"=>"不存在的阶段报告"]);
        try{
            $report->status=0;
            $report->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"delete","删除阶段报告",1);
            return response()->json(["code"=>200,"msg"=>"删除成功"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
    }


    /**
     * @api {post} /admin/report/stage/create 添加阶段报告
     *
     * @apiName stage_create
     * @apiGroup Stage_Report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {int}  type 类型
     * @apiParam {int}  minvalue 范围最大值
     * @apiParam {int}  maxvalue 范围最小值
     * @apiParam {int}  content 内容
     *
     * @apiSuccess {array} data
     * @apiSampleRequest  /admin/report/stage/create
     */
    public function stagecreate(Request $request){
        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);

        $input=$request->only(['type','minvalue','maxvalue',"content"]);

        $validator = \Validator::make($input,[
            'type'=>'required|between:0,100',
            'minvalue'=>'required|between:0,100',
            'maxvalue'=>'required|between:0,100',
            'content'=>'required',
        ]);
        if ($validator->fails()) return response()->json(['code'=>400,'msg'=>'参数错误']);
        try{
            $report=Stagereport::where(["type"=>$input["type"],"minvalue"=>$input["minvalue"],"maxvalue"=>$input["maxvalue"]])->first();
            if($report)return response()->json(["code"=>403,"msg"=>"已经存在"]);

            $report =new Stagereport();
            $report->type=$input["type"];
            $report->minvalue=$input['minvalue'];
            $report->maxvalue=$input['maxvalue'];
            $report->content=$input['content'];
            $report->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"create","添加阶段报告",1);
            return response()->json(['code'=>200,'msg'=>'添加成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
        }

    }


    /**
     * @api {put} /admin/report/stage/edit/:id  编辑阶段报告
     *
     * @apiName stage_update
     * @apiGroup  Stage_Report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}  content 报告内容
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/report/stage/edit/:d
     */
    public function stageedit($id,Request $request){

        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);
        $con=$request->get("content");
        if(!$con)return response()->json(["code"=>403,"msg"=>"请输入content"]);
        try{
            $report =Stagereport::find($id);
            if(!$report)return response()->json(["code"=>403]);
            $report->content=$con;
            $report->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"update","修改阶段报告",1);
            return response()->json(['code'=>200,'msg'=>'修改成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
        }
    }
}


