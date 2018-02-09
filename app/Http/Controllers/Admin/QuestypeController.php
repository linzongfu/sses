<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Qustype;
use App\Models\Question;

class QuestypeController extends Controller
{
    /**
     * @api {get} /admin/questype 接口测试
     * @apiDescription 所有入学测试题目分类
     * @apiGroup 入学测试
     *
     * @apiParamExample {string} 请求参数格式:
     * /admin/questype
     * @apiVersion 1.0.0
     */
    public function index()
    {
        $questypes=Qustype::all()->toArray();
        $questypes=$this->getTree($questypes,0);
         return response()->json($questypes);
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
     * @api {get} /admin/questype/:id  接口测试
     * @apiDescription 获得指定类型的所有题目
     * @apiGroup 入学测试
     *
     * @apiParam {Number} [page] 当前页默认1
     * @apiParam {Number} [limit] 每页条数默认10
     * @apiParam {Number} [keyword] 搜索关键词默认无
     * @apiParam {Number} [sort] 排序 默认题号
     *
     * @apiParamExample {string} 请求参数格式:
     *    /admin/questype/:id?page=1&perpage=20
     *
     * @apiVersion 1.0.0
     */
    public function show($id,Request $request){
        $page=$request->get("page",1)-1;
        $limit=$request->get("limit",10);
        $start=$page*$limit;
        $sort=explode(',',$request->get('sort','num'));
        $keyword=$request->get('keyword','');
        $questions=Question::select('id','num','qustype_id','title','type','info','answer')->where('qustype_id',$id);
        if($keyword)
            $questions->where('title','LIKE','%'.$keyword."%");
        $data['total']=$questions->count();
        $data['list']=$questions->
        skip($start)->
        take($limit)->orderBy($sort[0])
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
