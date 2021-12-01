<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class Producto extends Model
{
    public $timestamps = false;
    protected $table = 'Productos';
    //protected $primaryKey = 'Codigo';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Supercedido', 
        'Producto', 
        'Unidad',
        'Marca',
        'Linea',
        'Grupo',
        'Precio',
        'Impuesto',
        'TasaIeps',
        'Costo',
        'Moneda',
        'CostoDeLista',
        'Actualizado',
        'CostoDeVenta',
        'Utilidad',
        'SubTotal',
        'Iva',
        'Total',
        'Venta',
        'Min',
        'Max',
        'Ubicacion',
        'Comision',
        'Descuento',
        'Descripcion',
        'ClaveProducto',
        'ClaveUnidad',
        'Codigo1',
        'Codigo2',
        'Codigo3',
        'Codigo4',
        'Codigo5',
        'Insumo',
        'Reman',
        '[Fecha Ultima Compra]',
        '[Fecha Ultima Venta]',
        'Utilidad1',
        'Utilidad2',
        'Utilidad3',
        'Utilidad4',
        'Utilidad5',
        'Precio1',
        'Precio2',
        'Precio3',
        'Precio4',
        'Precio5',
        'CostoMaximo',
        'Pt',
        'Bloquear',
        '[Ultimo Costo]',
        '[Ultima Venta]',
        'Proveedor1',
        'Proveedor2',
        'Zona',
        'ProductoPeligroso',
        'Lpa1Subir',
        'Lpa2Subir',
        'Lpa1FechaCreacion',
        'Lpa2FechaCreacion',
        'Lpa1FechaUltimaVenta',
        'Lpa2FechaUltimaVenta',
        'Lpa1FechaUltimaCompra',
        'Lpa2FechaUltimaCompra',
        'Lpa1Identificacion',
        'Lpa2Identificacion',
        'Lpa1Ubicacion',
        'Lpa2Ubicacion',
        'Lpa1CodigoCompra',
        'Lpa2CodigoCompra',
        'Status',
        'Surtir',
        'NoSubir'
    ];
  
}
