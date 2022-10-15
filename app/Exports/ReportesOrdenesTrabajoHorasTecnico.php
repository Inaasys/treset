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

                $dataaux = Factura::where('Status','<>','BAJA')
                ->whereBetween(DB::raw("FORMAT (Fecha, 'd', 'fr-CA')"),[$fechainicio,$fechaterminacion])
                ->where('Depto','SERVICIO')
                ->orderBy('Fecha')
                ->get();
                foreach ($dataaux as $factura) {
                    if (isset($factura->detalles[0]->Orden)) {
                        $ordenTrabajo = OrdenTrabajo::where('Orden',$factura->detalles[0]->Orden)->first();
                        switch ($tipoorden) {
                            case 'TODOS':
                                foreach ($ordenTrabajo->detalles as $detalle) {
                                    if ($todoslostecnicos) {
                                        if(isset($detalle->tecnico1)){
                                            $arreglo->push([
                                                "Tecnico"=>$detalle->Tecnico1,
                                                "Nombre"=>$detalle->tecnico1->Nombre,
                                                "Orden"=>$detalle->Orden,
                                                "Tipo"=>$ordenTrabajo->Tipo,
                                                "Factura"=>$factura->Factura,
                                                "Fecha"=>$factura->Fecha,
                                                "Codigo"=>$detalle->Codigo,
                                                "Descripcion"=>$detalle->Descripcion,
                                                "Horas"=>Helpers::convertirvalorcorrecto($detalle->Horas1),
                                                "Precio"=>Helpers::convertirvalorcorrecto($detalle->Precio),
                                                "Total"=>Helpers::convertirvalorcorrecto($detalle->Horas1*$detalle->Precio),
                                            ]);
                                        }
                                        if(isset($detalle->tecnico2)){
                                            $arreglo->push([
                                                "Tecnico"=>$detalle->Tecnico2,
                                                "Nombre"=>$detalle->tecnico2->Nombre,
                                                "Orden"=>$detalle->Orden,
                                                "Tipo"=>$ordenTrabajo->Tipo,
                                                "Factura"=>$factura->Factura,
                                                "Fecha"=>$factura->Fecha,
                                                "Codigo"=>$detalle->Codigo,
                                                "Descripcion"=>$detalle->Descripcion,
                                                "Horas"=>Helpers::convertirvalorcorrecto($detalle->Horas2),
                                                "Precio"=>Helpers::convertirvalorcorrecto($detalle->Precio),
                                                "Total"=>Helpers::convertirvalorcorrecto($detalle->Horas2*$detalle->Precio),
                                            ]);
                                        }

                                    }else{
                                        $arraytecnicosseleccionados = explode(",", $string_tecnicos_seleccionados);
                                        if (isset($detalle->tecnico1)) {
                                            if (in_array($detalle->Tecnico1, $arraytecnicosseleccionados)) {
                                                $arreglo->push([
                                                    "Tecnico"=>$detalle->Tecnico1,
                                                    "Nombre"=>$detalle->tecnico1->Nombre,
                                                    "Orden"=>$detalle->Orden,
                                                    "Tipo"=>$ordenTrabajo->Tipo,
                                                    "Factura"=>$factura->Factura,
                                                    "Fecha"=>$factura->Fecha,
                                                    "Codigo"=>$detalle->Codigo,
                                                    "Descripcion"=>$detalle->Descripcion,
                                                    "Horas"=>Helpers::convertirvalorcorrecto($detalle->Horas1),
                                                    "Precio"=>Helpers::convertirvalorcorrecto($detalle->Precio),
                                                    "Total"=>Helpers::convertirvalorcorrecto($detalle->Horas1*$detalle->Precio),
                                                ]);
                                            }
                                        }
                                        if (isset($detalle->tecnico2)) {
                                            if (in_array($detalle->Tecnico2, $arraytecnicosseleccionados)) {
                                                $arreglo->push([
                                                    "Tecnico"=>$detalle->Tecnico2,
                                                    "Nombre"=>$detalle->tecnico2->Nombre,
                                                    "Orden"=>$detalle->Orden,
                                                    "Tipo"=>$ordenTrabajo->Tipo,
                                                    "Factura"=>$factura->Factura,
                                                    "Fecha"=>$factura->Fecha,
                                                    "Codigo"=>$detalle->Codigo,
                                                    "Descripcion"=>$detalle->Descripcion,
                                                    "Horas"=>Helpers::convertirvalorcorrecto($detalle->Horas2),
                                                    "Precio"=>Helpers::convertirvalorcorrecto($detalle->Precio),
                                                    "Total"=>Helpers::convertirvalorcorrecto($detalle->Horas2*$detalle->Precio),
                                                ]);
                                            }
                                        }
                                        if (isset($detalle->tecnico3)) {
                                            if (in_array($detalle->Tecnico3, $arraytecnicosseleccionados)) {
                                                $arreglo->push([
                                                    "Tecnico"=>$detalle->Tecnico3,
                                                    "Nombre"=>$detalle->tecnico3->Nombre,
                                                    "Orden"=>$detalle->Orden,
                                                    "Tipo"=>$ordenTrabajo->Tipo,
                                                    "Factura"=>$factura->Factura,
                                                    "Fecha"=>$factura->Fecha,
                                                    "Codigo"=>$detalle->Codigo,
                                                    "Descripcion"=>$detalle->Descripcion,
                                                    "Horas"=>Helpers::convertirvalorcorrecto($detalle->Horas3),
                                                    "Precio"=>Helpers::convertirvalorcorrecto($detalle->Precio),
                                                    "Total"=>Helpers::convertirvalorcorrecto($detalle->Horas3*$detalle->Precio),
                                                ]);
                                            }
                                        }
                                        if (isset($detalle->tecnico4)) {
                                            if (in_array($detalle->Tecnico4, $arraytecnicosseleccionados)) {
                                                $arreglo->push([
                                                    "Tecnico"=>$detalle->Tecnico4,
                                                    "Nombre"=>$detalle->tecnico4->Nombre,
                                                    "Orden"=>$detalle->Orden,
                                                    "Tipo"=>$ordenTrabajo->Tipo,
                                                    "Factura"=>$factura->Factura,
                                                    "Fecha"=>$factura->Fecha,
                                                    "Codigo"=>$detalle->Codigo,
                                                    "Descripcion"=>$detalle->Descripcion,
                                                    "Horas"=>Helpers::convertirvalorcorrecto($detalle->Horas3),
                                                    "Precio"=>Helpers::convertirvalorcorrecto($detalle->Precio),
                                                    "Total"=>Helpers::convertirvalorcorrecto($detalle->Horas3*$detalle->Precio),
                                                ]);
                                            }
                                        }
                                    }

                                }
                                break;
                            default:
                                if ($ordenTrabajo->Tipo == $tipoorden) {
                                    foreach ($ordenTrabajo->detalles as $detalle) {
                                        if (isset($detalle->tecnico1)) {
                                            $arreglo->push([
                                                "Tecnico"=>$detalle->Tecnico1,
                                                "Nombre"=>$detalle->tecnico1->Nombre,
                                                "Orden"=>$detalle->Orden,
                                                "Tipo"=>$ordenTrabajo->Tipo,
                                                "Factura"=>$factura->Factura,
                                                "Fecha"=>$factura->Fecha,
                                                "Codigo"=>$detalle->Codigo,
                                                "Descripcion"=>$detalle->Descripcion,
                                                "Horas"=>Helpers::convertirvalorcorrecto($detalle->Horas1),
                                                "Precio"=>Helpers::convertirvalorcorrecto($detalle->Precio),
                                                "Total"=>Helpers::convertirvalorcorrecto($detalle->Horas1*$detalle->Precio),
                                            ]);
                                        }
                                        if(isset($detalles->tecnico2)) {
                                            $arreglo->push([
                                                "Tecnico"=>$detalle->Tecnico2,
                                                "Nombre"=>$detalle->tecnico2->Nombre,
                                                "Orden"=>$detalle->Orden,
                                                "Tipo"=>$ordenTrabajo->Tipo,
                                                "Factura"=>$factura->Factura,
                                                "Fecha"=>$factura->Fecha,
                                                "Codigo"=>$detalle->Codigo,
                                                "Descripcion"=>$detalle->Descripcion,
                                                "Horas"=>Helpers::convertirvalorcorrecto($detalle->Horas2),
                                                "Precio"=>Helpers::convertirvalorcorrecto($detalle->Precio),
                                                "Total"=>Helpers::convertirvalorcorrecto($detalle->Horas2*$detalle->Precio),
                                            ]);

                                        }
                                        if(isset($detalles->tecnico3)) {
                                            $arreglo->push([
                                                "Tecnico"=>$detalle->Tecnico3,
                                                "Nombre"=>$detalle->tecnico3->Nombre,
                                                "Orden"=>$detalle->Orden,
                                                "Tipo"=>$ordenTrabajo->Tipo,
                                                "Factura"=>$factura->Factura,
                                                "Fecha"=>$factura->Fecha,
                                                "Codigo"=>$detalle->Codigo,
                                                "Descripcion"=>$detalle->Descripcion,
                                                "Horas"=>Helpers::convertirvalorcorrecto($detalle->Horas3),
                                                "Precio"=>Helpers::convertirvalorcorrecto($detalle->Precio),
                                                "Total"=>Helpers::convertirvalorcorrecto($detalle->Horas3*$detalle->Precio),
                                            ]);

                                        }
                                        if (isset($detalles->tecnico4)) {
                                            $arreglo->push([
                                                "Tecnico"=>$detalle->Tecnico4,
                                                "Nombre"=>$detalle->tecnico4->Nombre,
                                                "Orden"=>$detalle->Orden,
                                                "Tipo"=>$ordenTrabajo->Tipo,
                                                "Factura"=>$factura->Factura,
                                                "Fecha"=>$factura->Fecha,
                                                "Codigo"=>$detalle->Codigo,
                                                "Descripcion"=>$detalle->Descripcion,
                                                "Horas"=>Helpers::convertirvalorcorrecto($detalle->Horas4),
                                                "Precio"=>Helpers::convertirvalorcorrecto($detalle->Precio),
                                                "Total"=>Helpers::convertirvalorcorrecto($detalle->Horas4*$detalle->Precio),
                                            ]);
                                        }

                                    }
                                }
                                break;
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
