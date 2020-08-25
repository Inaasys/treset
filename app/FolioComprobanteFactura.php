<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FolioComprobanteFactura extends Model
{
    public $timestamps = false;
    protected $table = 'Folios Comprobantes Facturas';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Serie',
        'Esquema',
        'FolioInicial',
        'Titulo',
        'Depto',
        'ArchivoCertificado',
        'ArchivoLlave',
        'Contraseña',
        'NoCertificado',
        'ValidoDesde',
        'ValidoHasta',
        'Empresa',
        'Domicilio',
        'Leyenda1',
        'Leyenda2',
        'Leyenda3',
        'Pagare',
        'UbicarLogotipo',
        'AlinearLogotipo',
        'AlinearEmpresa',
        'ImprimirLogotipo',
        'CertificadoBase64',
        'LlaveBase64',
        'AlgoritmoSelloDigital',
        'Version',
        'Predeterminar',
        'Status'
    ];
}
