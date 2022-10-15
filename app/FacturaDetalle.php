<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FacturaDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Facturas Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Factura',
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
        'CostoTotal',
        'Com',
        'Comision',
        'Utilidad',
        'Moneda',
        'CostoDeLista',
        'TipoDeCambio',
        'Remision',
        'Orden',
        'Departamento',
        'Cargo',
        'Partida',
        'Facturar',
        'Ti',
        'Tf',
        'Ent',
        'Med',
        'Tienda',
        'Pedido',
        'Almacen',
        'Anotaciones',
        'DatosUnidad',
        'BloquearLpa',
        'ClaveProducto',
        'ClaveUnidad',
        'Item',
        'CuentaPredial',
        'InteresMeses',
        'InteresTasa',
        'InteresMonto',
        'Lote',
        'Venta',
        'PrecioNeto'
    ];

    /**
     * Get the Factura that owns the FacturaDetalle
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function factura()
    {
        return $this->belongsTo(Factura::class, 'Factura', 'Factura');
    }
}
