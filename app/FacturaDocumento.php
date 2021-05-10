<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FacturaDocumento extends Model
{
    public $timestamps = false;
    protected $table = 'Facturas Documentos';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Factura', 
        'UUID',
    ];
}
