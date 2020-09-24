<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaCliente extends Model
{
    protected $table = 'VistaClientes';
    protected $fillable = [
        'Numero',
        'Nombre',
        'Rfc',
        'Municipio',
        'Bloquear',
        'FacturarAlCosto',
        'Plazo',
        'Credito',
        'Saldo',
        'FormaPago',
        'Email1',
        'Telefonos',
        'Agente',
        'NombreAgente',
        'Status',
        'Calle',
        'noExterior',
        'noInterior ',
        'Colonia',
        'Localidad',
        'Referencia',
        'Estado',
        'Pais',
        'CodigoPostal',
        'MetodoPago',
        'UsoCfdi',
        'NumeroAgente',
        'DireccionAgente',
        'ColoniaAgente',
        'CiudadAgente',
        'CpAgente',
        'RfcAgente',
        'ContactoAgente',
        'TelefonosAgente',
        'EmailAgente',
        'CuentaAgente',
        'ComisionAgente',
        'AnotiacionesAgente'
    ];
    //credito
    public function getCreditoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //saldo
    public function getSaldoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //comision agente
    public function getComisionAgenteAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
