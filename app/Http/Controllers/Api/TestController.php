<?php

namespace App\Http\Controllers\Api;

use App\Models\Enmajortest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Qustype;


class TestController extends Controller
{
    public function index(){
       $e=Enmajortest::find(16);
       return json_decode($e->choreply,true);
    }
    public function EnTest($pid){
        $a= Qustype::where('pid',$pid)->get();
        if(!$a){ return 'dd';}
       // $result=$a[rand(0,$a->count()-1)];
        $result=$a[0];
        $result->question=Question::where("qustype_id",$result->id)->get();
        return $result;
    }
}
