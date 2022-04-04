<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_Rel_Almacen extends Model
{
    protected $table = 'user_rel_almacenes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id', 
        'almacen_id',
    ];
}
