<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServiciosExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array{
        return [
            'Codigo',
            'Servicio',
            'Unidad',
            'Familia',
            'NombreFamilia',
            'Costo',
            'Venta',
            'Cantidad',
            'ClaveProducto',
            'ClaveUnidad',
            'Status'
        ];
    }
    public function collection(){
        $servicios = DB::table('vistaServicios')
                    ->select('Codigo',
                            'Servicio',
                            'Unidad',
                            'Familia',
                            'NombreFamilia',
                            'Costo',
                            'Venta',
                            'Cantidad',
                            'ClaveProducto',
                            'ClaveUnidad',
                            'Status')
                    ->get();
         return $servicios;
        
    }
}
