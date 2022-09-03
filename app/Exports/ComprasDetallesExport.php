<?php

namespace App\Exports;

use App\CompraDetalle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;

class ComprasDetallesExport implements FromCollection,WithHeadings,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    /*public function collection()
    {
        return CompraDetalle::all();
    }*/
    use Exportable;
    private $campos_consulta;
    private $compra;

    public function __construct($campos_consulta,$compra){
        $this->campos_consulta = $campos_consulta;
        $this->compra = $compra;
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Compra_Detalles-'.$this->compra;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    public function collection(){
        $compradetalles = \App\CompraDetalle::select($this->campos_consulta)->where('compra', $this->compra)->get();
        return $compradetalles;
        
    }
}


