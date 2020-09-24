<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaOrdenTrabajo extends Model
{
    protected $table = 'VistaOrdenesDeTrabajo';
    protected $fillable = [
        'Orden',
        'Caso',
        'Fecha',
        'Status',
        'Tipo',
        'Unidad',
        'Cliente',
        'NombreCliente',
        'Total',
        'Vin',
        'Pedido',
        'Marca',
        'Economico',
        'Placas',
        'Año',
        'Kilometros',
        'Reclamo',
        'Motor',
        'MotivoBaja',
        'Usuario',
        'Equipo',
        'Periodo',
        'Folio',
        'DelCliente',
        'Agente',
        'Plazo',
        'Entrega',
        'Laminado',
        'ServicioEnAgencia',
        'RetrabajoOrden',
        'Impuesto',
        'Importe',
        'Descuento',
        'SubTotal',
        'Iva',
        'Facturado',
        'Costo',
        'Comision',
        'Utilidad',
        'Operador',
        'OperadorCelular',
        'Modelo',
        'Color',
        'Combustible',
        'Bahia',
        'Forma',
        'ObsOrden',
        'ObsUnidad',
        'Campaña',
        'Falla',
        'Causa',
        'Correccion',
        'Rodar',
        'Terminada',
        'Facturada',
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
        'NumeroCliente',
        'RfcCliente',
        'CalleCliente',
        'noExteriorCliente',
        'ColoniaCliente',
        'LocalidadCliente',
        'MunicipioCliente',
        'EstadoCliente',
        'PaisCliente',
        'CodigoPostalCliente',
        'ReferenciaCliente',
        'TelefonosCliente',
        'Email1Cliente',
        'AnotacionesCliente',
        'FormaPagoCliente',
        'MetodoPagoCliente',
        'UsoCfdiCliente'
    ];
    //Total
    public function getTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Kilometros
    public function getKilometrosAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Impuesto
    public function getImpuestoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Importe
    public function getImporteAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Descuento
    public function getDescuentoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //SubTotal
    public function getSubTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Iva
    public function getIvaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Facturado
    public function getFacturadoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Costo
    public function getCostoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Comision
    public function getComisionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Utilidad
    public function getUtilidadAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //HorasReales
    public function getHorasRealesAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //KmProximoServicio
    public function getKmProximoServicioAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
