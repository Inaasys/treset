<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartaPorteDetalles extends Model
{
    public $timestamps = false;
    protected $table = 'CartaPorte Detalles';
    protected $primaryKey = 'CartaPorte';

    protected $fillable = [
        'CartaPorte',
        'Codigo',
        'Descripcion',
        'Unidad',
        'Cantidad',
        'ClaveUnidad',
        'ClaveProducto',
        'MaterialPeligroso',
        'PesoEnKilogramos'
    ];
}
