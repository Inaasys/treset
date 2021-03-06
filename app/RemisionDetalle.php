<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RemisionDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Remisiones Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Remision', 
        'Cliente',
        'Fecha',
        'Codigo',
        'Descripcion',
        'Unidad',
        'Cantidad',
        'Precio',
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
        'Pedido',
        'Cotizacion',
        'Insumo',
        'Item',
        'InteresMeses',
        'InteresTasa',
        'InteresMonto',
        'PrecioNeto'
    ];
}
