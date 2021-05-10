<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotaClienteDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Notas Cliente Detalles';
    protected $fillable = [
        'Nota',
        'Cliente',
        'Fecha',
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
        'Costo',
        'Partida',
        'ClaveProducto',
        'ClaveUnidad',
        'Item'
    ];
}
