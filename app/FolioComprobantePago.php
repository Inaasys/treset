<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FolioComprobantePago extends Model
{
    public $timestamps = false;
    protected $table = 'Folios Comprobantes Pagos';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Serie',
        'Esquema',
        'FolioInicial',
        'Titulo',
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
        'Leyenda',
        'UbicarLogo',
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
