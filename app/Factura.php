<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    public $timestamps = false;
    protected $table = 'Facturas';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Factura', 
        'Serie',
        'Folio',
        'Esquema',
        'Cliente',
        'Agente',
        'Fecha',
        'Plazo',
        'Depto',
        'Orden',
        'Pedido',
        'Tipo',
        'Unidad',
        'Lpa',
        'BloquearObsoleto',
        'Importe',
        'Descuento',
        'Ieps',
        'SubTotal',
        'Iva',
        'IvaRetencion',
        'IsrRetencion',
        'IepsRetencion',
        'ImpLocRetenciones',
        'ImpLocTraslados',
        'Total',
        'Costo',
        'Comision',
        'Utilidad',
        'Abonos',
        'Descuentos',
        'Saldo',
        'Moneda',
        'TipoCambio',
        'Descripcion',
        'Obs',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'CondicionesDePago',
        'LugarExpedicion',
        'RegimenFiscal',
        'Confirmacion',
        'FormaPago',
        'MetodoPago',
        'UsoCfdi',
        'ResidenciaFiscal',
        'TipoRelacion',
        'NumRegIdTrib',
        'EmisorRfc',
        'EmisorNombre',
        'ReceptorRfc',
        'ReceptorNombre',
        'FechaTimbrado',
        'UUID',
        'Hora',
        'Periodo'
    ];
}
