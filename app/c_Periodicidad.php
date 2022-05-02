<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class c_Periodicidad extends Model
{
    public $timestamps = false;
    protected $table = 'c_Periodicidad';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero',
        'Clave', 
        'Descripcion',
        'FechaDeInicioDeVigencia',
        'FechaDeFinDeVigencia'
    ];
}
