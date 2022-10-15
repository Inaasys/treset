<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Jenssegers\Date\Date;
use Helpers;
use App\Factura;
use App\OrdenTrabajo;
use App\FacturaDetalle;
use App\NotaCliente;
use App\NotaClienteDetalle;
use App\CuentaXCobrar;
use App\CuentaXCobrarDetalle;
use App\Cliente;
use App\Agente;
use App\Tecnico;
use DB;
use Illuminate\Support\Collection;

class ReportesOrdenesTrabajoHorasTecnico implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $fechainicialreporte;
    private $fechafinalreporte;
    private $tiporeporte;
    private $tipoorden;
    private $statusorden;
    private $string_tecnicos_seleccionados;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $tiporeporte, $tipoorden, $statusorden, $string_tecnicos_seleccionados, $numerodecimales, $empresa){
        switch($statusorden){
            case "FACTURADAS":
                $this->campos_consulta = array("Tecnico", "Nombre", "Orden", "Tipo", "Factura", "Fecha", "Codigo", "Descripcion", "Horas", "Precio", "Total");
                break;
            case "DETALLES":
                break;
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->tiporeporte = $tiporeporte;
        $this->tipoorden = $tipoorden;
        $this->statusorden = $statusorden;
        $this->string_tecnicos_seleccionados = $string_tecnicos_seleccionados;
        $this->numerodecimales = $numerodecimales;
        $this->empresa = $empresa;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 15,
            'N' => 15,
            'O' => 15,
            'P' => 15,
            'Q' => 15,
            'R' => 15,
            'S' => 15,
            'T' => 15,
            'U' => 15,
            'V' => 15,
            'W' => 15,
            'X' => 15,
            'Y' => 15,
            'Z' => 15,
        ];
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Horas Tecnico'.$this->statusorden;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }

    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        //$reporte = $request->tiporeporte;
        $reporte = $this->statusorden;
        $tipoorden = $this->tipoorden;
        $statusorden = $this->statusorden;
        $string_tecnicos_seleccionados = $this->string_tecnicos_seleccionados;
        if($string_tecnicos_seleccionados > 0){
            $todoslostecnicos = 0;
        }else{
            $todoslostecnicos = 1;
        }
        $arreglo = new Collection;
        switch($reporte){
            case "FACTURADAS":
                $data = DB::table('Facturas as f')
                ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                ->join('Ordenes de Trabajo as ot', 'fd.Orden', '=', 'ot.Orden')
                ->join('Ordenes de Trabajo Detalles as otd', 'ot.Orden', '=', 'otd.Orden')
                ->select("f.Factura", "f.Fecha", "fd.Orden", "f.Unidad", "ot.Marca", "ot.Tipo", "ot.HorasReales", "f.Status", "fd.Codigo", "fd.Descripcion", "fd.Precio", "f.TipoCambio", "fd.Departamento", "fd.Cargo", "otd.Status", "otd.Tecnico1", "otd.Tecnico2", "otd.Tecnico3", "otd.Tecnico4", "otd.Horas1", "otd.Horas2", "otd.Horas3", "otd.Horas4", "otd.Partida", "fd.Partida", "ot.Usuario")
                ->whereColumn("otd.Status", "f.Factura")
                ->whereColumn("fd.Partida", "otd.Partida")
                ->where("ot.Tipo", "<>", "REFACTURA")
                ->where("fd.Cargo", "SERVICIO")
                ->where("f.Status", "<>", "BAJA")
                ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
                ->where(function($q){
                    $q->where('otd.Tecnico1', '>', 0)
                      ->orWhere('otd.Tecnico2', '>',0)
                      ->orWhere('otd.Tecnico3', '>',0)
                      ->orWhere('otd.Tecnico4', '>',0);
                })
                ->where(function($q) use ($tipoorden) {
                    if($tipoorden != 'TODOS'){
                        $q->where('ot.Tipo', $tipoorden);
                    }
                })
                ->orderby('f.Fecha')
                ->get();
                $cont = 0;
                $arreglo = new Collection;
                if($todoslostecnicos == 1){
                    foreach($data as $d){
                        if($d->Tecnico1 > 0){
                            $tecnico = Tecnico::where('Numero', $d->Tecnico1)->first();
                            $arreglo->push([
                                "Tecnico"=>$d->Tecnico1,
                                "Nombre"=>$tecnico->Nombre,
                                "Orden"=>$d->Orden,
                                "Tipo"=>$d->Tipo,
                                "Factura"=>$d->Factura,
                                "Fecha"=>$d->Fecha,
                                "Codigo"=>$d->Codigo,
                                "Descripcion"=>$d->Descripcion,
                                "Horas"=>Helpers::convertirvalorcorrecto($d->Horas1),
                                "Precio"=>Helpers::convertirvalorcorrecto($d->Precio),
                                "Total"=>Helpers::convertirvalorcorrecto($d->Horas1*$d->Precio),
                            ]);
                        }
                        if($d->Tecnico2 > 0){
                            $tecnico = Tecnico::where('Numero', $d->Tecnico2)->first();
                            $arreglo->push([
                                "Tecnico"=>$d->Tecnico2,
                                "Nombre"=>$tecnico->Nombre,
                                "Orden"=>$d->Orden,
                                "Tipo"=>$d->Tipo,
                                "Factura"=>$d->Factura,
                                "Fecha"=>$d->Fecha,
                                "Codigo"=>$d->Codigo,
                                "Descripcion"=>$d->Descripcion,
                                "Horas"=>Helpers::convertirvalorcorrecto($d->Horas2),
                                "Precio"=>Helpers::convertirvalorcorrecto($d->Precio),
                                "Total"=>Helpers::convertirvalorcorrecto($d->Horas2*$d->Precio),
                            ]);
                        }
                        if($d->Tecnico3 > 0){
                            $tecnico = Tecnico::where('Numero', $d->Tecnico3)->first();
                            $arreglo->push([
                                "Tecnico"=>$d->Tecnico3,
                                "Nombre"=>$tecnico->Nombre,
                                "Orden"=>$d->Orden,
                                "Tipo"=>$d->Tipo,
                                "Factura"=>$d->Factura,
                                "Fecha"=>$d->Fecha,
                                "Codigo"=>$d->Codigo,
                                "Descripcion"=>$d->Descripcion,
                                "Horas"=>Helpers::convertirvalorcorrecto($d->Horas3),
                                "Precio"=>Helpers::convertirvalorcorrecto($d->Precio),
                                "Total"=>Helpers::convertirvalorcorrecto($d->Horas3*$d->Precio),
                            ]);
                        }
                        if($d->Tecnico4 > 0){
                            $tecnico = Tecnico::where('Numero', $d->Tecnico4)->first();
                            $arreglo->push([
                                "Tecnico"=>$d->Tecnico4,
                                "Nombre"=>$tecnico->Nombre,
                                "Orden"=>$d->Orden,
                                "Tipo"=>$d->Tipo,
                                "Factura"=>$d->Factura,
                                "Fecha"=>$d->Fecha,
                                "Codigo"=>$d->Codigo,
                                "Descripcion"=>$d->Descripcion,
                                "Horas"=>Helpers::convertirvalorcorrecto($d->Horas4),
                                "Precio"=>Helpers::convertirvalorcorrecto($d->Precio),
                                "Total"=>Helpers::convertirvalorcorrecto($d->Horas4*$d->Precio),
                            ]);
                        }
                    }
                }else{
                    $arraytecnicosseleccionados = explode(",", $string_tecnicos_seleccionados);
                    foreach($data as $d){
                        if($d->Tecnico1 > 0){
                            if (in_array($d->Tecnico1, $arraytecnicosseleccionados)) {
                                $tecnico = Tecnico::where('Numero', $d->Tecnico1)->first();
                                $arreglo->push([
                                    "Tecnico"=>$d->Tecnico1,
                                    "Nombre"=>$tecnico->Nombre,
                                    "Orden"=>$d->Orden,
                                    "Tipo"=>$d->Tipo,
                                    "Factura"=>$d->Factura,
                                    "Fecha"=>$d->Fecha,
                                    "Codigo"=>$d->Codigo,
                                    "Descripcion"=>$d->Descripcion,
                                    "Horas"=>Helpers::convertirvalorcorrecto($d->Horas1),
                                    "Precio"=>Helpers::convertirvalorcorrecto($d->Precio),
                                    "Total"=>Helpers::convertirvalorcorrecto($d->Horas1*$d->Precio),
                                ]);
                            }
                        }
                        if($d->Tecnico2 > 0){
                            if (in_array($d->Tecnico2, $arraytecnicosseleccionados)) {
                                $tecnico = Tecnico::where('Numero', $d->Tecnico2)->first();
                                $arreglo->push([
                                    "Tecnico"=>$d->Tecnico2,
                                    "Nombre"=>$tecnico->Nombre,
                                    "Orden"=>$d->Orden,
                                    "Tipo"=>$d->Tipo,
                                    "Factura"=>$d->Factura,
                                    "Fecha"=>$d->Fecha,
                                    "Codigo"=>$d->Codigo,
                                    "Descripcion"=>$d->Descripcion,
                                    "Horas"=>Helpers::convertirvalorcorrecto($d->Horas2),
                                    "Precio"=>Helpers::convertirvalorcorrecto($d->Precio),
                                    "Total"=>Helpers::convertirvalorcorrecto($d->Horas2*$d->Precio),
                                ]);
                            }
                        }
                        if($d->Tecnico3 > 0){
                            if (in_array($d->Tecnico3, $arraytecnicosseleccionados)) {
                                $tecnico = Tecnico::where('Numero', $d->Tecnico3)->first();
                                $arreglo->push([
                                    "Tecnico"=>$d->Tecnico3,
                                    "Nombre"=>$tecnico->Nombre,
                                    "Orden"=>$d->Orden,
                                    "Tipo"=>$d->Tipo,
                                    "Factura"=>$d->Factura,
                                    "Fecha"=>$d->Fecha,
                                    "Codigo"=>$d->Codigo,
                                    "Descripcion"=>$d->Descripcion,
                                    "Horas"=>Helpers::convertirvalorcorrecto($d->Horas3),
                                    "Precio"=>Helpers::convertirvalorcorrecto($d->Precio),
                                    "Total"=>Helpers::convertirvalorcorrecto($d->Horas3*$d->Precio),
                                ]);
                            }
                        }
                        if($d->Tecnico4 > 0){
                            if (in_array($d->Tecnico4, $arraytecnicosseleccionados)) {
                                $tecnico = Tecnico::where('Numero', $d->Tecnico4)->first();
                                $arreglo->push([
                                    "Tecnico"=>$d->Tecnico4,
                                    "Nombre"=>$tecnico->Nombre,
                                    "Orden"=>$d->Orden,
                                    "Tipo"=>$d->Tipo,
                                    "Factura"=>$d->Factura,
                                    "Fecha"=>$d->Fecha,
                                    "Codigo"=>$d->Codigo,
                                    "Descripcion"=>$d->Descripcion,
                                    "Horas"=>Helpers::convertirvalorcorrecto($d->Horas4),
                                    "Precio"=>Helpers::convertirvalorcorrecto($d->Precio),
                                    "Total"=>Helpers::convertirvalorcorrecto($d->Horas4*$d->Precio),
                                ]);
                            }
                        }
                    }
                }
                break;
            case "Porsucursal":
                break;
            case "Portecnico":
                break;
        }
        return $arreglo;
    }
}
