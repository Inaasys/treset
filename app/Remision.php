<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class Remision extends Model
{
    public $timestamps = false;
    protected $table = 'Remisiones';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Remision',
        'Serie',
        'Folio',
        'Cliente',
        'Agente',
        'Fecha',
        'Plazo',
        'Tipo',
        'Unidad',
        'Pedido',
        'Solicita',
        'Referencia',
        'Destino',
        'Almacen',
        'TeleMarketing',
        'Os',
        'Eq',
        'Rq',
        'Importe',
        'Descuento',
        'SubTotal',
        'Iva',
        'Total',
        'Costo',
        'Comision',
        'Utilidad',
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
        'Personas',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo',
        'FormaPago',
        'SerieRq'
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
    /**
     * Get all of the detalles for the Remision
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalles()
    {
        return $this->hasMany(RemisionDetalle::class, 'Remision', 'Remision');
    }
}
