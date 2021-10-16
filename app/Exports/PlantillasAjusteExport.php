<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class PlantillasAjusteExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;

    public function __construct(){
        $this->campos_consulta = array('codigo', 'entradas', 'salidas');
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
        return 'ajustes';
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    
    public function collection(){
        $plantilla = \App\VistaAjusteInventario::where('Periodo', 3000)->get();
        return $plantilla;
    }
}
