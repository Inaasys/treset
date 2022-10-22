<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Compras Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Compra',
        'Proveedor',
        'Fecha',
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
        'Orden',
        'Depto',
        'Obs',
        'PrecioMoneda',
        'DescuentoMoneda',
        'AplicarCosto',
        'ClaveProducto',
        'ClaveUnidad',
        'OtroMontoBase',
        'Item'
    ];
    /**
     * Get the Compra that owns the CompraDetalle
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'Compra', 'Compra');
    }
}
