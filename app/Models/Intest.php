<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intest extends Model
{
    public function  intesting(){
        return $this->hasMany("App\Models\Intesting","intest_id","id");
    }
}
