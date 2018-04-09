<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testrule extends Model
{
    protected $table = 'test_rules';
    protected $hidden = ['created_at','updated_at'];
}
