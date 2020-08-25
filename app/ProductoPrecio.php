<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductoPrecio extends Model
{
    public $timestamps = false;
    protected $table = 'Productos Precios';
    //protected $primaryKey = 'Codigo';
    protected $fillable = [
        'Codigo', 
        'Cliente', 
        'Precio',
        'Item'
    ];
}
