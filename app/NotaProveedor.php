<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotaProveedor extends Model
{
    public $timestamps = false;
    protected $table = 'Notas Proveedor';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Nota',
        'Serie',
        'Folio',
        'Proveedor',
        'Fecha',
        'UUID',
        'NotaProveedor',
        'Almacen',
        'Importe',
        'Descuento',
        'Ieps',
        'SubTotal',
        'Iva',
        'IvaRetencion',
        'IsrRetencion',
        'IepsRetencion',
        'ImpLocRetenciones',
        'ImpLocTraslados',
        'Total',
        'Obs',
        'Moneda',
        'TipoCambio',
        'FechaEmitida',
        'EmisorRfc',
        'EmisorNombre',
        'ReceptorRfc',
        'ReceptorNombre',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo'
    ];
    /**
     * Get all of the detalles for the NotaProveedorDetalle
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalles()
    {
        return $this->hasMany(NotaProveedorDetalle::class, 'Nota', 'Nota');
    }

    /**
     * Get the Documentos associated with the NotaProveedor
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function documentos()
    {
        return $this->hasMany(NotaProveedorDocumento::class, 'Nota', 'Nota');
    }

}
