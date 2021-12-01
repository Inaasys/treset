<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FolioComprobanteTraslado extends Model
{
    public $timestamps = false;
    protected $table = 'Folios Comprobantes Traslados';
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
        'CertificadoBase64',
        'LlaveBase64',
        'Version',
        'Predeterminar',
        'Status'
    ];
}
