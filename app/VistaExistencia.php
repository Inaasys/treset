<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaExistencia extends Model
{
    protected $table = 'VistaExistencias';
    protected $fillable = [
        'Codigo',
        'Producto',
        'Unidad',
        'Ubicacion',
        'Almacen',
        'Existencias',
        'Costo',
        'totalCostoInventario',
        'CostoDeLista',
        'Moneda',
        'CostoDeVenta',
        'Utilidad',
        'SubTotal',
        'Iva',
        'Total',
        'Marca',
        'Linea',
        'NombreMarca',
        'NombreLinea',
        'FechaUltimaCompra',
        'FechaUltimaVenta',
        'ClaveProducto',
        'ClaveUnidad',
        'Status',
        'Precio'
    ];
    //existencias
    public function getExistenciasAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //costo
    public function getCostoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //totalCostoInventario
    public function gettotalCostoInventarioAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //costo de lista
    public function getCostoDeListaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //costo de venta
    public function getCostoDeVentaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //utilidad
    public function getUtilidadAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //subtotal
    public function getSubTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //iva
    public function getIvaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //total
    public function getTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //precio
    public function getPrecioAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    
}
