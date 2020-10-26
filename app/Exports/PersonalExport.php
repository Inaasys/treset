<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PersonalExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array{
        return [
            'id',
            'nombre',
            'fecha_ingreso',
            'tipo_personal',
            'status'
        ];
    }
    public function collection(){
        $personal = DB::table('VistaPersonal')
                    ->select('id',
                            'nombre',
                            'fecha_ingreso',
                            'tipo_personal',
                            'status')
                    ->orderBy('id','DESC')
                    ->get();
         return $personal;
        
    }
}
