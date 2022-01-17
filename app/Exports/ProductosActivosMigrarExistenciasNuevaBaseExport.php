<?php

namespace App\Exports;
use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;

class ProductosActivosMigrarExistenciasNuevaBaseExport implements FromCollection,WithHeadings,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $arraydatos;

    public function __construct($arraydatos){
        $this->arraydatos = $arraydatos;
        $this->campos_consulta = array("codigo","entradas","salidas");
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'productosamigrarexistencias';
    }

    public function headings(): array{
        return $this->campos_consulta;

    }
    public function collection(){
        $datosaexportar = $this->arraydatos;
        return collect($datosaexportar);
        
    }
}
