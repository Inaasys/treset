<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequisicionDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Requisiciones Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Requisicion',
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
        'Surtir',
        'Registro',
        'Obs',
        'Moneda',
        'CostoDeLista',
        'TipoDeCambio',
        'Item'
    ];
}
