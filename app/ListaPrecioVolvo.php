<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ListaPrecioVolvo extends Model
{
    public $timestamps = false;
    protected $table = 'lista_precios_volvo';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'NumeroParte',
        'Descripcion',
        'PrecioPublico',
        'Diferencia'
    ];
}
