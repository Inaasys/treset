<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;

class AjustesInventarioExport implements FromCollection,WithHeadings,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;

    public function __construct($campos_consulta){
        $this->campos_consulta = $campos_consulta;
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Ajustes Inventario';
    }
    
    public function headings(): array{
        return $this->campos_consulta;
    }
    public function collection(){
        $ajustesinventario = \App\VistaAjusteInventario::select($this->campos_consulta)->orderBy('Folio','DESC')->get();
        return $ajustesinventario;
        
    }
}
