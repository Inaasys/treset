<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class c_ClaveProdServCP extends Model
{
    public $timestamps = false;
    protected $table = 'c_ClaveProdServCP';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero',
        'Clave',
        'Descripcion',
        'MaterialPeligroso',
        'FechaDeInicioDeVigencia',
        'FechaDeFinDeVigencia'
    ];
}
