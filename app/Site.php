<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable= [
        'site_name', 'location_detail'
    ];
    public function EquipmentClassList(){
        return $this->hasMany(EquipmentClass::class);
    }
}
