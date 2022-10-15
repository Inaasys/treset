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

    /**
     * Get the user that owns the OrdenTrabajoDetalle
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function OrdenTrabajo(){
        return $this->belongsTo(OrdenTrabajo::class, 'Orden');
    }
    /**
     * Get the tecnico1 associated with the OrdenTrabajoDetalle
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tecnico1()
    {
        return $this->hasOne(Tecnico::class, 'Numero', 'Tecnico1');
    }

    /**
     * Get the tecnico2 associated with the OrdenTrabajoDetalle
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tecnico2()
    {
        return $this->hasOne(Tecnico::class, 'Numero', 'Tecnico2');
    }

    /**
     * Get the tecnico3 associated with the OrdenTrabajoDetalle
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tecnico3()
    {
        return $this->hasOne(Tecnico::class, 'Numero', 'Tecnico3');
    }

    /**
     * Get the tecnico4 associated with the OrdenTrabajoDetalle
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tecnico4()
    {
        return $this->hasOne(Tecnico::class, 'Nombre', 'Tecnico4');
    }
}
