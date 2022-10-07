<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartaPorteDocumentos extends Model
{
    protected $table = 'Carta Porte Documentos';

    protected $fillable = [
        'CartaPorte',
        'Factura',
        'UUID'
    ];

    /**
     * Get the CartaPorte that owns the CartaPorteDocumentos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function CartaPorte()
    {
        return $this->belongsTo(CartaPorte::class, 'CartaPorte', 'CartaPorte');
    }
}
