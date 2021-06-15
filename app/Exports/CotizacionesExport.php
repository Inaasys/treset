<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;

class CotizacionesExport implements FromCollection,WithHeadings,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $periodo;

    public function __construct($campos_consulta,$periodo){
        $this->campos_consulta = $campos_consulta;
        $this->periodo = $periodo;
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Cotizaciones-'.$this->periodo;
    }
    
    public function headings(): array{
        return $this->campos_consulta;
    }
    public function collection(){
        $cotizaciones = \App\VistaCotizacion::select($this->campos_consulta)->where('Periodo', $this->periodo)->orderBy('fecha', 'DESC')->orderBy('serie', 'ASC')->orderBy('folio', 'DESC')->get();
        return $cotizaciones;
        
    }
}
