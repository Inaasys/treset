<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;

class ExistenciasSucsExport implements FromCollection,WithHeadings,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $conn;
    private $suc;

    public function __construct($campos_consulta,$conn,$suc){
        $this->campos_consulta = $campos_consulta;
        $this->conn = $conn;
        $this->suc = $suc;
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Existencias'.$this->suc;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    
    public function collection(){ 
        $existencias = \App\VistaExistencia::on($this->conn)->select($this->campos_consulta)->where('Almacen', 1)->get(); // static method
        return $existencias;        
    }
}
