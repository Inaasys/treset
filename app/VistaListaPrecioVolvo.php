<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VistaListaPrecioVolvo extends Model
{
    protected $table = 'VistaListaPreciosVolvo';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'NumeroParte', 
        'Descripcion', 
        'PrecioPublico', 
        'Diferencia'
    ];
}
