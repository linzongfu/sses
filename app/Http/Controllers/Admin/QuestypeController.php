<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Qustype;
use App\Models\Question;
use Illuminate\Support\Facades\Input;

class QuestypeController extends Controller
{
    /**
     * @api {get} /admin/questype 所有入学测试类型
     *
     * @apiName questype
     * @apiGroup EntrTest
     * @apiVersion 1.0.0
     *
     * @apiHeader (Authorization) {String} Authorization Bearer {token}.
     * @apiHeaderExample {json} Header-Example:
     * {
     *      Authorization: Bearer {token}
     * }
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/questype
     */
    public function index()
    {
        $questypes=Qustype::all()->toArray();
        $questypes=$this->getTree($questypes,0);
         return response()->json($questypes);
    }

    public  function  test(Request $request){
        //dd(Input::all());
         return response()->json(["ss"=>"sss"]);
    }
    public function getTree($data, $pId)
    {
        $tree = '';
        foreach($data as $k => $v)
        {

           if($v['pid'] == $pId)
            {         //父亲找到儿子
                $v['pid'] = $this->getTree($data, $v['id']);
                $tree[] = $v;
               //unset($data[$k]);
            }
        }
        return $tree;
    }

    /**
     * @api {get} /admin/questype/addquestype 添加测试类型
     *
     * @apiName questype
     * @apiGroup EntrTest
     * @apiVersion 1.0.0
     *
     * @apiHeader (Authorization) {String} Authorization Bearer {token}.
     * @apiHeaderExample {json} Header-Example:
     * {
     *      Authorization: Bearer {token}
     * }
     * @apiParam {Number}  pid 当前页 默认为0
     * @apiParam {String}  name 类型名
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /admin/questype/addquestype
     */
    public function addquestype(Request $request){
        $pid=$request->get("pid");
        if(!$pid)$pid=0;
        $name=$request->get("name");
        if(!$name) return  response()->json(['message'=>'你的输入有误'],403);
        try{
            $questype=new Qustype();
            $questype->name=$name;
            $questype->pid=$pid;
            $questype->save();
            return response()->json(['message'=>'添加成功']);
        }catch (\Exception $e){
            return response()->json(['message'=>'添加失败','err'=>$e->getMessage()],403);
        }
    }
    public function editquestype(Request $request){
           $id=$request->get("id");
           $pid=$request->get("pid");
           $name=$request->get("name");

           if((!$id)||(!$pid)||(!$name))     return response()->json(['message'=>"你的输入有误"],403);

           try{
               $tquestype=Question::find((int)$id);
               $request->pid=$pid;
               $request->name=$name;
               $request->save();
               return response()->json(['message'=>"修改成功"]);
           }catch (\Exception $e){
               return response()->json(["message"=>'修改失败','err'=>$e->getMessage()],403);
           }
    }


    public  function  delquestype(Request $request){
        $id=$request->get("id");
        if(!$id) return response()->json(["message"=>"请检查参数"]);
      /*  try{
            $questype=Qustype::find((int)$id);
            if($questype) {
                $questype->delete();
               // $questypes=Qustype::select
            }
        }catch (\Exception $e){
            return
        }
      */

    }
    /**
     * @api {get} /admin/questype/:id  指定类型的题
     *
     * @apiName question
     * @apiGroup EntrTest
     * @apiVersion 1.0.0
     *
     * @apiHeader (Authorization) {String} Authorization Bearer {token}.
     * @apiHeaderExample {json} Header-Example:
     * {
     *      Authorization: Bearer {token}
     * }
     * @apiParam {Number}  page 当前页 默认为1
     * @apiParam {Number} limit 当前页条数 默认为10
     * @apiParam {String} keyword 当前页 默认为1
     * @apiParam {String} sort 排序 默认为num
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /admin/questype/:id
     */
    public function show($id,Request $request){
        $page=$request->get("page",1);
        $limit=$request->get("limit",10);
        $page=$page?$page:0;
        $limit=$limit?$limit:10;
        $start=$page*$limit;
        $sort=$request->get('sort');
        if(!$sort) $sort="num";
        $keyword=$request->get('keyword');
        $questions=Question::select('id','num','qustype_id','title','type','info','answer')->where('qustype_id',$id);
        if($keyword)
            $questions->where('title','LIKE','%'.$keyword."%");
        $data['total']=$questions->count();
        $data['list']=$questions->
        skip($start)->
        take($limit)->orderBy($sort)
            ->get();
        return response()->json($data);

    }
    public function create()
    {

    }

    public function store(Request $request)
    {
        //
    }



    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }
}
