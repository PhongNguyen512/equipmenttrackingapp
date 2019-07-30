<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EquipUpdateLog extends Model
{
    protected $guarded = ['id'];

    public function Equipment(){
        return $this->belongsTo('App\Equipment');
    }

    public function User(){
        return $this->belongsTo('App\User');
    }
}
