<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table = 'vehiculos';
    protected $primaryKey = 'id';
    protected $fillable = [ 
      'PermisoSCT',
      'NumeroPermisoSCT',
      'NombreAseguradora',
      'NumeroPolizaSeguro',
      'Placa',
      'Año',
      'SubTipoRemolque',
      'PlacaSubTipoRemolque',
      'Marca',
      'Modelo'
    ];
}
