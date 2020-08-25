<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductoConsumo extends Model
{
    public $timestamps = false;
    protected $table = 'Productos Consumos';
    //protected $primaryKey = 'Codigo';
    protected $fillable = [
        'Codigo', 
        'Equivale', 
        'Cantidad',
        'Item'
    ];
}
