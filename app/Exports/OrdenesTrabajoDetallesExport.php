<?php

namespace App\Exports;

use App\OrdenTrabajoDetalle;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrdenesTrabajoDetallesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return OrdenTrabajoDetalle::all();
    }
}
