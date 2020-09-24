<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AgentesExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array{
        return [
            'Numero',
            'Nombre',
            'Rfc',
            'Status'
        ];
    }
    public function collection(){
        $agentes = DB::table('vistaAgentes')
                    ->select('Numero',
                            'Nombre',
                            'Rfc',
                            'Status')
                    ->orderBy('Numero','DESC')
                    ->get();
         return $agentes;
        
    }
}
