<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VinesExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array{
        return [
            'Cliente',
            'Economico',
            'Vin',
            'Placas',
            'Motor',
            'Marca',
            'Modelo',
            'Año',
            'Color',
            'Status'
        ];
    }
    public function collection(){
        $vines = DB::table('vistaVines')
                    ->select('Cliente',
                            'Economico',
                            'Vin',
                            'Placas',
                            'Motor',
                            'Marca',
                            'Modelo',
                            'Año',
                            'Color',
                            'Status')
                    ->get();
         return $vines;
        
    }
}
