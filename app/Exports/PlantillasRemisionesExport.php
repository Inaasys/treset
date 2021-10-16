<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class PlantillasRemisionesExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;

    public function __construct(){
        $this->campos_consulta = array('codigo', 'cantidad');
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
        return 'remisiones';
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    
    public function collection(){
        $plantilla = \App\VistaRemision::where('Periodo', 3000)->get();
        return $plantilla;
    }
}
