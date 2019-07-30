<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipments';
    protected $fillable= [
        'unit', 'description', 'ltd_smu', 'last_entry_ltd_smu', 
        'owning_status', 'equipment_status', 'mechanical_status',
        'equipment_class_id',
    ];
    public function EquipmentClassList()
    {
        return $this->belongsTo(EquipmentClass::class, 'equipment_class_id');
    }
    public function UpdateLog(){
        return $this->hasOne('App\EquipUpdateLog', 'equipment_id', 'id');
    }
}
