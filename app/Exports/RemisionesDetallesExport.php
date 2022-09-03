<?php

namespace App\Exports;

use App\RemisionDetalle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;

class RemisionesDetallesExport implements FromCollection,WithHeadings,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $remision;

    public function __construct($campos_consulta,$remision){
        $this->campos_consulta = $campos_consulta;
        $this->remision = $remision;
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'remision_Detalles-'.$this->remision;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    public function collection(){
        $remisiondetalles = \App\RemisionDetalle::select($this->campos_consulta)->where('remision', $this->remision)->get();
        return $remisiondetalles;
        
    }
}



