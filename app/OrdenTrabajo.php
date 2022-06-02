<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrdenTrabajo extends Model
{
    public $timestamps = false;
    protected $table = 'Ordenes de Trabajo';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Orden',
        'Serie',
        'Folio',
        'Caso',
        'Tipo',
        'Unidad',
        'Cliente',
        'DelCliente',
        'Agente',
        'Plazo',
        'Pedido',
        'Fecha',
        'Entrega',
        'Laminado',
        'ServicioEnAgencia',
        'RetrabajoOrden',
        'Impuesto',
        'Importe',
        'Descuento',
        'SubTotal',
        'Iva',
        'Total',
        'Facturado',
        'Costo',
        'Comision',
        'Utilidad',
        'Operador',
        'OperadorCelular',
        'Vin',
        'Motor',
        'Marca',
        'Modelo',
        'Año',
        'Kilometros',
        'Placas',
        'Economico',
        'Color',
        'Detalles',
        'Combustible',
        'Reclamo',
        'Bahia',
        'Forma',
        'ObsOrden',
        'ObsUnidad',
        'Campaña',
        'Falla',
        'Causa',
        'Correccion',
        'Rodar',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Terminada',
        'Facturada',
        'Usuario',
        'HoraEntrada',
        'HoraEntrega',
        'HorasReales',
        'Promocion',
        'TipoServicio',
        'KmProximoServicio',
        'FechaRecordatorio',
        'FechaIngresoUnidad',
        'FechaAsignacionUnidad',
        'FechaTerminoUnidad',
        'EstadoServicio',
        'Refactura',
        'Periodo'
    ];

    /**
     * Obtiene todos los detalle de la orden de trabajo
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalles()
    {
        return $this->hasMany(OrdenTrabajoDetalle::class, 'Orden', 'Orden');
    }
}
