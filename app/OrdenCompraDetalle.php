<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrdenCompraDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Ordenes de Compra Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Orden', 
        'Proveedor',
        'Fecha',
        'Codigo',
        'Descripcion',
        'Unidad',
        'Cantidad',
        'Precio',
        'Importe',
        'Costo',
        'Dcto',
        'Descuento',
        'SubTotal',
        'Impuesto',
        'Iva',
        'Total',
        'Surtir',
        'Registro',
        'Item'
    ];
}
