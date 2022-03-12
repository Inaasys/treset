<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class c_ConfiguracionAutoTransporte extends Model
{
    
    public $timestamps = false;
    protected $table = 'c_ConfigAutotransporte';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Clave',
        'Descripcion',
        'NumeroEjes',
        'NumeroLlantas',
        'Remolque',
        'FechaDeInicioDeVigencia',
        'FechaDeFinDeVigencia'
    ];
}
