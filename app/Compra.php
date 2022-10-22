<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    public $timestamps = false;
    protected $table = 'Compras';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Compra',
        'Serie',
        'Folio',
        'Proveedor',
        'Movimiento',
        'Remision',
        'Factura',
        'UUID',
        'Tipo',
        'Plazo',
        'Fecha',
        'Almacen',
        'Orden',
        'Departamento',
        'BloquearObsoleto',
        'Importe',
        'Descuento',
        'leps',
        'SubTotal',
        'Iva',
        'IvaRetencion',
        'IsrRetencion',
        'IepsRetencion',
        'ImpLocRetenciones',
        'ImpLocTraslados',
        'Total',
        'Abonos',
        'Descuentos',
        'Saldo',
        'Obs',
        'Moneda',
        'TipoCambio',
        'FechaEmitida',
        'FechaTimbrado',
        'EmisorRfc',
        'EmisorNombre',
        'ReceptorRfc',
        'ReceptorNombre',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo',
        'OrdenTrabajo'
    ];
    /**
     * Get all of the detalles for the Compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class, 'Compra', 'Compra');
    }
}
