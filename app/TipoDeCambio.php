<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoDeCambio extends Model
{
    public $timestamps = false;
    protected $table = 'Tipos de Cambio';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Moneda', 
        'Fecha',
        'TipoCambioCompra',
        'TipoCambioVenta',
        'TipoCambioDOF'
    ];
}
