<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ListaPrecioCummins extends Model
{
    public $timestamps = false;
    protected $table = 'lista_precios_cummins';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'NumeroParte',
        'Descripcion',
        'PrecioPublico',
        'Diferencia',
        'PrecioDeFlota'
    ];
}
