<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class c_Meses extends Model
{
    public $timestamps = false;
    protected $table = 'c_Meses';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero',
        'Clave', 
        'Descripcion',
        'FechaDeInicioDeVigencia',
        'FechaDeFinDeVigencia'
    ];
}
