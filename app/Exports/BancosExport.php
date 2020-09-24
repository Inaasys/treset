<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BancosExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array{
        return [
            'Numero',
            'Nombre',
            'Cuenta',
            'Status'
        ];
    }
    public function collection(){
        $bancos = DB::table('vistaBancos')
                    ->select('Numero',
                            'Nombre',
                            'Cuenta',
                            'Status')
                    ->orderBy('Numero','DESC')
                    ->get();
         return $bancos;
        
    }
}
