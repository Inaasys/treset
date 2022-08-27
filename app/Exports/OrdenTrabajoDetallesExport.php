<?php

namespace App\Exports;

use App\OrdenTrabajoDetalle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;

class OrdenTrabajoDetallesExport implements FromCollection,WithHeadings,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */

    /*public function collection()
    {
        return OrdenTrabajoDetalle::all();
    }*/
    
    use Exportable;
    private $campos_consulta;
    private $orden;

    public function __construct($campos_consulta,$orden){
        $this->campos_consulta = $campos_consulta;
        $this->orden = $orden;
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'OrdenesTrabajo-'.$this->orden;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    public function collection(){
        $ordenesdetrabajo = \App\OrdenTrabajoDetalle::select($this->campos_consulta)->where('orden', $this->orden)->get();
        return $ordenesdetrabajo;
        
    }
}
