<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Helpers;
use DB;

class KardexProductoExport implements FromArray, WithHeadings
{
    protected $codigo;
    protected $almacen;

    public function __construct($codigo,$almacen) {
        $this->codigo = $codigo;
        $this->almacen = $almacen;
    }

    public function headings(): array{
        return [
            'Documento','Movimiento','Fecha','Almacen','Entradas','Salidas','Existencias','Costo','Status'
        ];
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array():array
    {
        $kardex = DB::select('exec ObtenerKardex ?,?', array($this->codigo,$this->almacen));

        $nummovimiento = 1;
        $entradas = 0;
        $salidas = 0;
        $existencias = 0;
        $numerodecimalesconfigurados = config('app.numerodedecimales');
        $data = array();
        foreach(array_reverse($kardex) as $k){
            $entradas = $entradas + $k->Entradas;
            $salidas = $salidas + $k->Salidas;
            $existencias = $existencias + $k->Entradas - $k->Salidas;
            $colorfila = '';
            if($k->Status == 'BAJA'){
                $colorfila = 'bg-red';
            }
            $data[]=array(
                "documento"=>$k->Documento,
                "movimiento"=>$k->Movimiento,
                "fecha"=>Helpers::fecha_espanol($k->Fecha),
                "almacen" => Helpers::convertirvalorcorrecto($k->Almacen),
                "entradas"=> Helpers::convertirvalorcorrecto($k->Entradas),
                "salidas" => Helpers::convertirvalorcorrecto($k->Salidas),
                "existencias"=> round($existencias, $numerodecimalesconfigurados),
                "costo"=>Helpers::convertirvalorcorrecto($k->Costo),
                "status"=>$k->Status
            );
            $nummovimiento++;
        }
        return $data;
    }
}
