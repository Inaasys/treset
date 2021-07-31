<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CotizacionProductoDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Cotizaciones Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Cotizacion', 
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
        'Costo Total',
        'Com',
        'Comision',
        'Utilidad',
        'Moneda',
        'CostoDeLista',
        'TipoDeCambio',
        'Existencias',
        'Item',
        'InteresMeses',
        'InteresTasa',
        'InteresMonto'
    ];
}
