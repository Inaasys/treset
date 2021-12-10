<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;

class PlantillaCompraExport implements FromCollection,WithHeadings,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $documento;

    public function __construct($documento){
        $this->documento = $documento;
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Compra-'.$this->documento;
    }

    public function headings(): array{
        return array('Codigo', 'Cantidad', 'Precio');
    }

    public function collection(){
        $compra = \App\CompraDetalle::select('Codigo', 'Cantidad', 'Precio')->where('Compra', $this->documento)->orderBy('Item','ASC')->get();
        return $compra;
        
    }
}
