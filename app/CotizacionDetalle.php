<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CotizacionDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'cotizaciones_t_detalles';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_cotizacion',
        'cotizacion', 
        'fecha',
        'numero_parte',
        'descripcion',
        'unidad',
        'status_refaccion',
        'insumo',
        'precio',
        'cantidad',
        'importe',
        'item'
    ];
}
