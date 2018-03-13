<?php
use App\Models\Appoint;
use App\Models\Operate;

function getArraybystr($Elo,$str){
    $sid=[];
    $sNum=$Elo->count();
    $Elo=$Elo->toArray();
    for($i=0;$i<$sNum;$i++)
        $sid[$i]=$Elo[$i][$str];
    return $sid;
}
function getfuncby($Noid){
//    if(Cache::has($Noid."function"))
//    $funcid=Cache::get($Noid."function");
//    else{
//        $role=Appoint::select("role_id")->where('Noid',$Noid)->get();
//        $roleid= getArraybystr($role,"role_id");
//        $func=Operate::select("func_id")->whereIn("role_id",$roleid)->distinct()->get();
//        $funcid=getArraybystr($func,"func_id");
//        Cache::add($Noid."function",$funcid,20);
//    }
        $role=Appoint::select("role_id")->where('Noid',$Noid)->get();
        $roleid= getArraybystr($role,"role_id");
        $func=Operate::select("func_id")->whereIn("role_id",$roleid)->distinct()->get();
        $funcid=getArraybystr($func,"func_id");
    return $funcid;
}
function accessControl($opuser,$access_id){

}
?>