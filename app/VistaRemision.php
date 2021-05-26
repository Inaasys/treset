<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaRemision extends Model
{
    protected $table = 'VistaRemisiones';
    protected $fillable = [
        'Remision',
        'Serie',
        'Folio',
        'Fecha',
        'Status',
        'Cliente',
        'NombreCliente',
        'Pedido',
        'Os',
        'Eq',
        'Rq',
        'Agente',
        'NombreAgente',
        'Tipo',
        'Almacen',
        'NombreAlmacen',
        'SubTotal',
        'Iva',
        'Total',
        'Equipo',
        'Usuario',
        'MotivoBaja',
        'Periodo',
        'Plazo',
        'Unidad',
        'Solicita',
        'Referencia',
        'Destino',
        'TeleMarketing',
        'Importe',
        'Descuento',
        'Costo',
        'Comision',
        'Utilidad',
        'FormaPago',
        'Obs',
        'TipoCambio',
        'Hora',
        'Facturada',
        'Corte',
        'SuPago',
        'EnEfectivo',
        'EnTarjetas',
        'EnVales',
        'EnCheque',
        'Lugar',
        'Personas'
    ];
    protected $casts = [
        'Fecha' => 'datetime:Y-m-d',
    ];
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
    //importe
    public function getImporteAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //descuento
    public function getDescuentoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //costo
    public function getCostoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //comision
    public function getComisionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //utilidad
    public function getUtilidadAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //tipocambio
    public function getTipoCambioAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //supago
    public function getSuPagoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //enefectivo
    public function getEnEfectivoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //entarjetas
    public function getEnTarjetasAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //envales
    public function getEnValesAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //encheque
    public function getEnChequeAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
