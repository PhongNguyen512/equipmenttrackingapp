<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable= [
        'site_name', 'location_detail'
    ];
    public function EquipmentList(){
        return $this->hasMany(Equipment::class);
    }
}
