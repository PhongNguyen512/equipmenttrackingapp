<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $fillable= [
        'role'
    ];
    public function UserList(){
        return $this->hasMany(User::class);
    }
}
