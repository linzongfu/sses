<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    protected $hidden = ['created_at','updated_at','status','teach_id'];
}
