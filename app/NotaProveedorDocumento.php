<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotaProveedorDocumento extends Model
{
    public $timestamps = false;
    protected $table = 'Notas Proveedor Documentos';
    protected $fillable = [
        'Nota',
        'Compra',
        'UUID',
        'Total',
        'Descuento',
        'Item'
    ];
}
