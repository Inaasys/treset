<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    public $timestamps = false;
    protected $table = 'Almacenes';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Nombre',
        'Status'
    ];
}
