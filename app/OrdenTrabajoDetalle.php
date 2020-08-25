<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrdenTrabajoDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'Ordenes de Trabajo Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Orden', 
        'Cliente',
        'Agente',
        'Fecha',
        'Codigo',
        'Descripcion',
        'Anotaciones',
        'Unidad',
        'Cantidad',
        'Precio',
        'Importe',
        'Dcto',
        'Descuento',
        'SubTotal',
        'Impuesto',
        'Iva',
        'Total',
        'Costo',
        'CostoTotal',
        'Com',
        'Comision',
        'Utilidad',
        'Departamento',
        'Cargo',
        'Traspaso',
        'Compra',
        'Item',
        'Usuario',
        'Tecnico1',
        'Tecnico2',
        'Tecnico3',
        'Tecnico4',
        'Horas1',
        'Horas2',
        'Horas3',
        'Horas4',
        'Facturar',
        'Promocion',
        'Status',
        'Almacen',
        'Cotizacion',
        'Partida'
    ];
}
