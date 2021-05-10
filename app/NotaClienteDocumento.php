<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotaClienteDocumento extends Model
{
    public $timestamps = false;
    protected $table = 'Notas Cliente Documentos';
    protected $fillable = [
        'Nota',
        'Factura',
        'UUID',
        'Total',
        'Descuento',
        'Item'
    ];
}
