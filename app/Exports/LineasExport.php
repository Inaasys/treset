<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LineasExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array{
        return [
            'Numero',
            'Nombre',
            'Status'
        ];
    }
    public function collection(){
        $lineas = DB::table('vistaLineas')
                    ->select('Numero',
                            'Nombre',
                            'Status')
                    ->orderBy('Numero','DESC')
                    ->get();
         return $lineas;
        
    }
}
