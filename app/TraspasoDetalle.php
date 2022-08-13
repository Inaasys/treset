<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TraspasoDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Traspasos Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Traspaso',
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
        'Obs',
        'Pedido',
        'Requisicion',
        'Cotizacion',
        'Item',
        'Rollos',
        'Status'
    ];
    /**
     * Get the Traspaso that owns the TraspasoDetalle
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Traspaso(){
        return $this->belongsTo(Traspaso::class, 'Traspaso');
    }
}
