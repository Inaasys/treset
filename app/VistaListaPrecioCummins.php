<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VistaListaPrecioCummins extends Model
{
    protected $table = 'VistaListaPreciosCummins';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'NumeroParte', 
        'Descripcion', 
        'PrecioPublico', 
        'Diferencia',
        'PrecioDeFlota'
    ];
}
