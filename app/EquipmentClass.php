<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EquipmentClass extends Model
{
    protected $fillable= [
        'billing_rate', 'equipment_class_name'
    ];
}
