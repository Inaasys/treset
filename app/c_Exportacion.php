<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class c_Exportacion extends Model
{
    public $timestamps = false;
    protected $table = 'c_Exportacion';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero',
        'Clave', 
        'Descripcion',
        'FechaDeInicioDeVigencia',
        'FechaDeFinDeVigencia'
    ];
}
