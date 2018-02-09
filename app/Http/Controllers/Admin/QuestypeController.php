<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Qustype;
use App\Models\Question;

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
     * @apiParam {Number} page 当前页 默认为1
     * @apiParam {Number} limit 当前页条数 默认为10
     *@apiParam {Number} sort 排序 默认为num
     *@apiParam {Number} keyword 排序 默认为无
     *
     * @apiSuccess {String} data
     * @apiSampleRequest /admin/questype/:id?page=1&limit=10&sort=num&keyword=计算机
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
