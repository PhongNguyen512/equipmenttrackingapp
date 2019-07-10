<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipments';
    protected $fillable= [
        'unit', 'description', 'ltd_smu', 'last_entry_ltd_smu', 'owning_status', 'equipment_status', 'mechanical_status'
    ];
    public function EquipmentClassList()
    {
        return $this->belongsTo(EquipmentClass::class, 'equipment_category_id');
    }
    public function SiteList()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
}
