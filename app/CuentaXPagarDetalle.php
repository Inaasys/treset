<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CuentaXPagarDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'CxP Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Pago', 
        'Fecha',
        'Proveedor',
        'Compra',
        'Abono',
        'Item'
    ]; 
}
