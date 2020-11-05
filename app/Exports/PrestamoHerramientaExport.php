<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;

class PrestamoHerramientaExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;

    public function __construct($campos_consulta){
        $this->campos_consulta = $campos_consulta;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    public function collection(){
        $prestacionesherramientas = \App\VistaPrestamoHerramienta::select($this->campos_consulta)->orderBy('id','DESC')->get();
        return $prestacionesherramientas;
        
    }
}
