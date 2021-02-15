<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class Remision extends Model
{
    public $timestamps = false;
    protected $table = 'Remisiones';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Remision', 
        'Serie',
        'Folio',
        'Cliente',
        'Agente',
        'Fecha',
        'Plazo',
        'Tipo',
        'Unidad',
        'Pedido',
        'Solicita',
        'Referencia',
        'Destino',
        'Almacen',
        'TeleMarketing',
        'Os',
        'Eq',
        'Rq',
        'Importe',
        'Descuento',
        'SubTotal',
        'Iva',
        'Total',
        'Costo',
        'Comision',
        'Utilidad',
        'Obs',
        'TipoCambio',
        'Hora',
        'Facturada',
        'Corte',
        'SuPago',
        'EnEfectivo',
        'EnTarjetas',
        'EnVales',
        'EnCheque',
        'Lugar',
        'Personas',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo',
        'FormaPago'
    ];

    public function getTotalAttribute($value)
    {
        return Helpers::convertirvalorcorrecto($value);
    }
}
