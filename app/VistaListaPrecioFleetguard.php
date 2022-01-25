<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VistaListaPrecioFleetguard extends Model
{
    protected $table = 'VistaListaPreciosFleetguard';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'NumeroParte', 
        'Descripcion', 
        'PrecioPublico'
    ];
}
