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

    /**
     * Get the NotaProveedor that owns the NotaProveedorDocumento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function NotaProveedor()
    {
        return $this->belongsTo(NotaProveedor::class, 'Nota', 'Nota');
    }

    /**
     * Get the Compra that owns the NotaProveedorDocumento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function compra()
    {
        return $this->belongsTo(Compra::class, 'Compra', 'Compra');
    }
}
