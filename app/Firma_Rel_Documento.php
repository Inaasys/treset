<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class Firma_Rel_Documento extends Model
{
    protected $table = 'firmas_rel_documentos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'TipoDocumento', 
        'Documento',
        'IdUsuario',
        'Fecha',
        'ReferenciaPosicion',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo'
    ];

  
}
