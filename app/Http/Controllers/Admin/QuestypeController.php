<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Qustype;
use App\Models\Question;

class QuestypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request){
        $page=$request->get("page",1)-1;
        $limit=$request->get("limit",10);
        $start=$page*$limit;
        $sort=explode(',',$request->get('sort','id'));
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
