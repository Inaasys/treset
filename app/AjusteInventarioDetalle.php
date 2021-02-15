<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AjusteInventarioDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Ajustes de Inventario Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Ajuste', 
        'Fecha',
        'Codigo',
        'Descripcion',
        'Unidad',
        'Existencias',
        'Entradas',
        'Salidas',
        'Real',
        'Costo',
        'Kilometros',
        'Anotacion',
        'Item'
    ];
}
