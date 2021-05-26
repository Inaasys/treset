<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaProducto extends Model
{
    protected $table = 'VistaProductos';
    protected $fillable = [
        'Codigo',
        'ClaveProducto',
        'ClaveUnidad',
        'Producto',
        'Unidad',
        'Ubicacion',
        'Existencias',
        'Almacen',
        'Costo',
        'CostoDeLista',
        'Moneda',
        'CostoDeVenta',
        'Utilidad',
        'SubTotal',
        'Iva',
        'Total',
        'Marca',
        'Linea',
        'NombreMarca ',
        'NombreLinea',
        'Status',
        'Supercedido',
        'Grupo',
        'Precio',
        'Impuesto',
        'TasaIeps',
        'Venta',
        'Insumo',
        'FechaUltimaCompra',
        'FechaUltimaVenta',
        'UltimoCosto',
        'UltimaVenta'
    ];
    //existencias
    public function getExistenciasAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //costo
    public function getCostoAttribute($value){
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
    //impuesto
    public function getImpuestoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //venta
    public function getVentaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //ultimo costo
    public function getUltimoCostoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //ultima venta
    public function getUltimaVentaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
