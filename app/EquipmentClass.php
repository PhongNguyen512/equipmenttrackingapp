<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EquipmentClass extends Model
{
    protected $fillable= [
        'billing_rate', 'equipment_class_name'
    ];
    public function EquipmentList(){
        return $this->hasMany(Equipment::class);
    }
    public function SiteList()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
}
