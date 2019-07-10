<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable= [
        'unit', 'description', 'ltd_smu', 'last_entry_ltd_smu', 'owning_status', 'equipment_status', 'mechanical_status'
    ];
}
