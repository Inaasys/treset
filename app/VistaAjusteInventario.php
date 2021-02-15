<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaAjusteInventario extends Model
{
    protected $table = 'VistaAjustesInventario';
    protected $fillable = [
        'Ajuste',
        'Serie',
        'Folio',
        'Fecha',
        'Obs',
        'Almacen',
        'Total',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo',
        'NumeroAlmacen',
        'NombreAlmacen'
    ];
    //total
    public function getTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
