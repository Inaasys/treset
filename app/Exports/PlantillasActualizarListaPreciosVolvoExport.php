<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class PlantillasActualizarListaPreciosVolvoExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;

    public function __construct(){
        $this->campos_consulta = array('item', 'descripcion', 'PL 21 USD');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 15          
        ];
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'plantillaactualizarlistapreciosvolvo';
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    
    public function collection(){
        $plantilla = \App\VistaListaPrecioVolvo::where('Numero', '>', 300000)->get();
        return $plantilla;
    }
}
