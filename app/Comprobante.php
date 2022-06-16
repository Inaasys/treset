<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    public $timestamps = false;
    protected $table = 'Comprobantes';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Comprobante',
        'Tipo',
        'Version',
        'Serie',
        'Folio',
        'FechaCancelacion',
        'UUID',
        'Fecha',
        'SubTotal',
        'Descuento',
        'Total',
        'EmisorRfc',
        'ReceptorRfc',
        'FormaPago',
        'MetodoPago',
        'UsoCfdi',
        'Moneda',
        'TipoCambio',
        'CertificadoSAT',
        'CertificadoCFD',
        'FechaTimbrado',
        'CadenaOriginal',
        'selloSAT',
        'selloCFD',
        'CfdiTimbrado',
        'CfdiTimbrado1',
        'Periodo',
        'IdFacturapi',
        'UrlVerificarCfdi'
    ];

    /**
    * Relaciona el comprobante con la Factura
    */

    public function factura(){
        $factura = Factura::where('Factura',$this->Folio.'-'.$this->Serie)->first();
        return $factura;
    }

}
