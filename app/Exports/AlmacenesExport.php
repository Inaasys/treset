<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AlmacenesExport implements FromCollection,WithHeadings
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
        $almacenes = DB::table('vistaAlmacenes')
                    ->select('Numero',
                            'Nombre',
                            'Status')
                    ->orderBy('Numero','DESC')
                    ->get();
         return $almacenes;
        
    }
}
