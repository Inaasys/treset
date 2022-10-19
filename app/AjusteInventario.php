<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AjusteInventario extends Model
{
    public $timestamps = false;
    protected $table = 'Ajustes de Inventario';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Ajuste',
        'Serie',
        'Folio',
        'Fecha',
        'Almacen',
        'Obs',
        'Total',
        'Obs',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo'
    ];

    /**
     * Get all of the detalles for the AjusteInventario
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalles()
    {
        return $this->hasMany(AjusteInventarioDetalle::class, 'Ajuste', 'Ajuste');
    }
}
