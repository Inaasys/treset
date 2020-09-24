<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MarcasExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array{
        return [
            'Numero',
            'Nombre',
            'Utilidad1',
            'Utilidad2',
            'Utilidad3',
            'Utilidad4',
            'Utilidad5',
            'Status'
        ];
    }
    public function collection(){
        $marcas = DB::table('vistaMarcas')
                    ->select('Numero',
                            'Nombre',
                            'Utilidad1',
                            'Utilidad2',
                            'Utilidad3',
                            'Utilidad4',
                            'Utilidad5',
                            'Status')
                    ->orderBy('Numero','DESC')
                    ->get();
         return $marcas;
        
    }
}
