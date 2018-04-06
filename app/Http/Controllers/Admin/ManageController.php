<?php

namespace App\Http\Controllers\Admin;

use App\Models\Manage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ManageController extends Controller
{
    /**
     * @api {get} /admin/index 管理员主页
     *
     * @apiName manage_index
     * @apiGroup Manage
     * @apiVersion 1.0.0
     *
     * @apiHeader (opuser) {String} opuser
     *
     * @apiSuccess {array} data
     * @apiSampleRequest /admin/index
     */
    public function index(Request $request){
        $opuser=$request->header("opuser");
        if(!$opuser) return response()->json(["code"=>401,"msg"=>"pleace logged in"]);
        if(!in_array(17,getfuncby($opuser))) return   response()->json(["code"=>403,"msg"=>"Prohibition of access"]);
        $meun=Manage::all()->toArray();
        $meun=$this->getTree($meun,0);
        return response()->json($meun);
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
}
