<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaObtenerExistenciaProducto extends Model
{
    protected $table = 'VistaObtenerExistenciasProductos';
    protected $fillable = [
        'Codigo',
        'Marca',
        'Producto',
        'Almacen',
        'Ubicacion',
        'Existencias',
        'Costo',
        'SubTotal',
        'Unidad',
        'Impuesto',
        'Insumo',
        'ClaveProducto',
        'ClaveUnidad',
        'NombreClaveProducto',
        'NombreClaveUnidad ',
        'CostoDeLista'
    ];
    //Existencias
    public function getExistenciasAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Costo
    public function getCostoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //SubTotal
    public function getSubTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
