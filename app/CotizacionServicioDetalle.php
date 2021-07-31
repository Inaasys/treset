<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CotizacionServicioDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Cotizaciones Servicio Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Cotizacion',
        'Fecha',
        'Codigo',
        'Descripcion',
        'Unidad',
        'Cantidad',
        'Precio',
        'PrecioNeto',
        'Importe',
        'Dcto',
        'Descuento',
        'SubTotal',
        'Impuesto',
        'Iva',
        'Total',
        'Costo',
        'CostoTotal',
        'Com',
        'Comision',
        'Utilidad',
        'Moneda',
        'CostoDeLista',
        'TipoDeCambio',
        'Existencias',
        'Departamento',
        'Item',
    ];
}
