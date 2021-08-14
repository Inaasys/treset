<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProduccionDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Produccion Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Produccion', 
        'Fecha',
        'Codigo',
        'Descripcion',
        'Unidad',
        'Cantidad',
        'Merma',
        'Consumo',
        'Costo',
        'Total',
        'Partida',
        'Item',
        'Periodo'
    ];
}
