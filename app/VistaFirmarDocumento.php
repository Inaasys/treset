<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaFirmarDocumento extends Model
{
    
    protected $table = 'VistaFirmasDocumentos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id', 
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
