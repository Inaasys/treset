<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    public $timestamps = false;
    protected $table = 'Proveedores';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Nombre', 
        'Rfc',
        'Calle',
        'noExterior',
        'noInterior',
        'Colonia',
        'Localidad',
        'Referencia',
        'Municipio',
        'Estado',
        'Pais',
        'CodigoPostal',
        'Plazo',
        'Cuenta',
        'Asimilado',
        'Anotaciones',
        'Contacto',
        'Telefonos',
        'Celular',
        'Nextel',
        'Email1',
        'Email2',
        'Email3',
        'Tipo',
        'Clave',
        'CuentaBancaria',
        'DescuentosCascada',
        'Credito',
        'Saldo',
        'Status',
        'SolicitarXML'
    ];
}
