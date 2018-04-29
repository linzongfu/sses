<?php

namespace App\Http\Controllers\Admin;

use App\Models\Grareport;
use App\Models\Grarule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GraduateController extends Controller
{

    /**
     * @api {get} /admin/report/graduate/index 结业报告列表
     *
     * @apiName graduate_index
     * @apiGroup Graduate_Report
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
     * @apiSampleRequest /admin/report/graduate/index
     */
    public function index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $input=$request->only(['type']);

        $stagereport=Grareport::where("status",1);
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
     * @api {get} /admin/report/graduate/where 结业报告列表选择
     *
     * @apiName graduate_index_where
     * @apiGroup Graduate_Report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/report/graduate/where
     */
    public function where(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $result=[1=>"道德" , 2=>"素质",3=>"学习能力" , 4=>"团队交际",5=>"体育" ,6=>"专业集中成绩",7=>"平时成绩"];
        return response()->json($result);
    }


    /**
     * @api {delete} /admin/report/graduate/delete/:id 删除结业报告
     *
     * @apiName graduate_delete
     * @apiGroup Graduate_Report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/report/graduate/delete/:id
     */
    public function delete($id,Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);

        $report=Grareport::find($id);
        if(!$report) return  response()->json(["code"=>403,"msg"=>"不存在的结业报告"]);
        try{
            $report->status=0;
            $report->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"delete","删除结业报告",1);
            return response()->json(["code"=>200,"msg"=>"删除成功"]);
        }catch (\Exception $e){
            return response()->json(["code"=>403,"msg"=>$e->getMessage()]);
        }
    }


    /**
     * @api {post} /admin/report/graduate/create 添加结业报告
     *
     * @apiName graduate_create
     * @apiGroup Graduate_Report
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
     * @apiSampleRequest  /admin/report/graduate/create
     */
    public function create(Request $request){
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
            $report=Grareport::where(["type"=>$input["type"],"minvalue"=>$input["minvalue"],"maxvalue"=>$input["maxvalue"]])->first();
            if($report)return response()->json(["code"=>403,"msg"=>"已经存在"]);

            $report =new Grareport();
            $report->type=$input["type"];
            $report->minvalue=$input['minvalue'];
            $report->maxvalue=$input['maxvalue'];
            $report->content=$input['content'];
            $report->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"create","添加结业报告",1);
            return response()->json(['code'=>200,'msg'=>'添加成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
        }

    }


    /**
     * @api {put} /admin/report/graduate/edit/:id  编辑结业报告
     *
     * @apiName graduate_update
     * @apiGroup  Graduate_Report
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiParam {string}  content 报告内容
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/report/graduate/edit/:d
     */
    public function edit($id,Request $request){

        $opuser= $request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"未登录"]);
        if(!in_array(17,getfuncby($opuser)))
            return   response()->json(["code"=>403,"msg"=>"禁止访问"]);
        $con=$request->get("content");
        if(!$con)return response()->json(["code"=>403,"msg"=>"请输入content"]);
        try{
            $report =Grareport::find($id);
            if(!$report)return response()->json(["code"=>403]);
            $report->content=$con;
            $report->save();
            log_add($opuser,$request->getRequestUri(),$request->getClientIp(),"update","修改结业报告",1);
            return response()->json(['code'=>200,'msg'=>'修改成功']);
        }catch(\Exception $e){
            return response()->json(['code'=>400,"msg"=>$e->getMessage()]);
        }
    }
}
