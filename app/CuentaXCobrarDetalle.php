<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CuentaXCobrarDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'CxC Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Pago', 
        'Fecha',
        'Cliente',
        'Factura',
        'Abono',
        'idDocumento',
        'Serie',
        'Folio',
        'MonedaDR',
        'TipoCambioDR',
        'MetodoDePagoDR',
        'NumParcialidad',
        'ImpSaldoAnt',
        'ImpPagado',
        'ImpSaldoInsoluto',
        'Item'
    ]; 
}
