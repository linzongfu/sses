<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qustype extends Model
{
     public  function questions(){
         return $this->hasMany('App\Models\Question','qustype_id','id');
     }
}
