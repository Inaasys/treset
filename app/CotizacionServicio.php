<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CotizacionServicio extends Model
{
    public $timestamps = false;
    protected $table = 'Cotizaciones Servicio';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Cotizacion',
        'Serie',
        'Folio',
        'Cliente',
        'Agente',
        'Plazo',
        'Fecha',
        'Unidad',
        'Referencia',
        'Importe',
        'Descuento',
        'SubTotal',
        'Iva',
        'Total',
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
        'TipoServicio',
        'Obs',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo'
    ];
}
