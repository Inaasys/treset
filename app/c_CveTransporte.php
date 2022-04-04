<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class c_CveTransporte extends Model
{
    
    public $timestamps = false;
    protected $table = 'c_CveTransporte';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Clave',
        'Descripcion',
        'FechaDeInicioDeVigencia',
        'FechaDeFinDeVigencia'
    ];
}
