<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    protected $table = 'personal';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nombre', 
        'fecha_ingreso',
        'tipo_personal',
        'status'
    ];

}
