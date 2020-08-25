<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotaProveedorDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Notas Proveedor Detalles';
    protected $fillable = [
        'Nota',
        'Proveedor',
        'Fecha',
        'Compra',
        'Factura',
        'UUID',
        'Codigo',
        'Descripcion',
        'Unidad',
        'Cantidad',
        'Precio',
        'Importe',
        'Dcto',
        'Descuento',
        'ImporteDescuento',
        'Ieps',
        'SubTotal',
        'Impuesto',
        'Iva',
        'IvaRetencion',
        'IsrRetencion',
        'IepsRetencion',
        'Total',
        'Partida',
        'PrecioMoneda',
        'DescuentoMoneda',
        'ClaveProducto',
        'ClaveUnidad',
        'Item'
    ];
}
