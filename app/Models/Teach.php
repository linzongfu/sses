<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teach extends Model
{
    public  function calendars(){
        return $this->hasMany('App\Models\Calendar','teach_id','id');
    }
}
