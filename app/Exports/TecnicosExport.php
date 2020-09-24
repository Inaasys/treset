<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TecnicosExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array{
        return [
            'Numero',
            'Nombre',
            'Objetivo',
            'Planeacion',
            'Status'
        ];
    }
    public function collection(){
        $tecnicos = DB::table('vistaTecnicos')
                    ->select('Numero',
                            'Nombre',
                            'Objetivo',
                            'Planeacion',
                            'Status')
                    ->orderBy('Numero','DESC')
                    ->get();
         return $tecnicos;
        
    }
}
