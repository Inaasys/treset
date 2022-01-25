<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ListaPrecioFleetguard extends Model
{
    public $timestamps = false;
    protected $table = 'lista_precios_fleetguard';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'NumeroParte',
        'Descripcion',
        'PrecioPublico'
    ];
}
