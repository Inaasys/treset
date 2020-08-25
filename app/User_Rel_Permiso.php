<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_Rel_Permiso extends Model
{
    protected $table = 'user_rel_permisos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id', 
        'permiso_id',
        'status'
    ];
}
