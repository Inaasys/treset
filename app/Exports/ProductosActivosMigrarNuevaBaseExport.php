<?php

namespace App\Exports;
use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;

class ProductosActivosMigrarNuevaBaseExport implements FromCollection,WithHeadings,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $arraydatos;

    public function __construct($arraydatos){
        $this->arraydatos = $arraydatos;
        $this->campos_consulta = array("insumo","codigo","claveproducto","claveunidad", "descripcion","unidad","marca","linea","impuesto","ubicacion","tipoprod","costo","precio","utilidad","subtotal","iva","total","status","costolista","moneda","costoventa","precio1");
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'productosamigrar';
    }

    public function headings(): array{
        return $this->campos_consulta;

    }
    public function collection(){
        $datosaexportar = $this->arraydatos;
        return collect($datosaexportar);
        
    }
}
