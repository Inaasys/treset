<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
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

        return $kardex;
    }
}
