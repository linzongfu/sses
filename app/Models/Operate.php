<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operate extends Model
{
    public function fun()
    {
        return $this->hasMany('App\Models\FFunction','func_id','id');
    }
}
