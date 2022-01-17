<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class PlantillasActualizarListaPreciosCumminsExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;

    public function __construct(){
        $this->campos_consulta = array('PART NO', 'DESCRIPTION', 'Future  Fleet Price','Future Public Price');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 15,
            'D' => 15         
        ];
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'plantillaactualizarlistaprecioscummins';
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    
    public function collection(){
        $plantilla = \App\VistaListaPrecioCummins::where('Numero', '>', 3000000)->get();
        return $plantilla;
    }
}
