<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Jenssegers\Date\Date;
use Helpers;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\Tecnico;

class ReportesOrdenesTrabajoHorasTecnico implements FromView,WithTitle
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
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->tiporeporte = $tiporeporte;
        $this->tipoorden = $tipoorden;
        $this->statusorden = $statusorden;
        $this->string_tecnicos_seleccionados = $string_tecnicos_seleccionados;
        $this->numerodecimales = $numerodecimales;
        $this->empresa = $empresa;
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Horas Técnico';
    }

    public function view(): View{
        if($this->tiporeporte == 'Porsucursal'){
            $fechahoy = Carbon::parse($this->fechafinalreporte);//fecha de la que se realizar el reporte
            //$tipoorden = $request->tipoorden;
            //$statusorden = $request->statusorden;
            if($this->tipoorden == 'TODAS' && $this->statusorden == 'TODAS'){
                $ordenes = OrdenTrabajo::whereBetween('Fecha', [$this->fechainicialreporte, $this->fechafinalreporte])->get();
            }else if($tipoorden == 'TODAS' && $statusorden != 'TODAS'){
                $ordenes = OrdenTrabajo::whereBetween('Fecha', [$this->fechainicialreporte, $this->fechafinalreporte])->where('Status', $this->statusorden)->get();
            }else if($statusorden == 'TODAS' && $tipoorden != 'TODAS'){
                $ordenes = OrdenTrabajo::whereBetween('Fecha', [$this->fechainicialreporte, $this->fechafinalreporte])->where('Tipo', $this->tipoorden)->get();
            }else{
                $ordenes = OrdenTrabajo::whereBetween('Fecha', [$this->fechainicialreporte, $this->fechafinalreporte])->where('Tipo', $this->tipoorden)->where('Status', $this->statusorden)->get();
            }
            $data=array();
            $totalhorasdetalles = 0;
            $totalpesoshorasdetalle = 0;
            foreach ($ordenes as $o){
                $detalles = OrdenTrabajoDetalle::where('Orden', $o->Orden)->get();
                foreach($detalles as $d){
                    $totalhorasdetalles = $totalhorasdetalles + $d->Horas1 + $d->Horas2 + $d->Horas3 + $d->Horas4;
                    $totalpesoshorasdetalle = $totalpesoshorasdetalle + $d->SubTotal;
                }
            }
            $empresa = $this->empresa;
            $data[]=array(
                "tecnico" => "N/A",
                "nombre" => $empresa->Nombre,
                "horas" => Helpers::convertirvalorcorrecto($totalhorasdetalles),
                "total" => Helpers::convertirvalorcorrecto($totalpesoshorasdetalle)
            );
            return view('reportes.ordenestrabajo.formato_excel_horas_tecnico', compact('data'));
        }else{
            $fechahoy = Carbon::parse($this->fechafinalreporte);//fecha de la que se realizar el reporte
            //$tipoorden = $request->tipoorden;
            //$statusorden = $request->statusorden;
            if($this->tipoorden == 'TODAS' && $this->statusorden == 'TODAS'){
                $ordenes = OrdenTrabajo::whereBetween('Fecha', [$this->fechainicialreporte, $this->fechafinalreporte])->get();
            }else if($this->tipoorden == 'TODAS' && $this->statusorden != 'TODAS'){
                $ordenes = OrdenTrabajo::whereBetween('Fecha', [$this->fechainicialreporte, $this->fechafinalreporte])->where('Status', $this->statusorden)->get();
            }else if($this->statusorden == 'TODAS' && $this->tipoorden != 'TODAS'){
                $ordenes = OrdenTrabajo::whereBetween('Fecha', [$this->fechainicialreporte, $this->fechafinalreporte])->where('Tipo', $this->tipoorden)->get();
            }else{
                $ordenes = OrdenTrabajo::whereBetween('Fecha', [$this->fechainicialreporte, $this->fechafinalreporte])->where('Tipo', $this->tipoorden)->where('Status', $this->statusorden)->get();
            }
            $data=array();
            $totalhorasdetalles = 0;
            $totalpesoshorasdetalle = 0;
            foreach(explode(",", $this->string_tecnicos_seleccionados) as $tecnico){
                $totalhorasdetalles = 0;
                $totalpesoshorasdetalle = 0;
                $tecnico = Tecnico::where('Numero', $tecnico)->first();
                foreach ($ordenes as $o){
                    $detalles = OrdenTrabajoDetalle::where('Orden', $o->Orden)->get();
                    foreach($detalles as $d){
                        if($d->Tecnico1 == $tecnico->Numero){
                            $totalhorasdetalles = $totalhorasdetalles + $d->Horas1;
                            $totalpesoshorasdetalle = $totalpesoshorasdetalle + $d->SubTotal;
                        }
                        if($d->Tecnico2 == $tecnico->Numero){
                            $totalhorasdetalles = $totalhorasdetalles + $d->Horas2;
                            $totalpesoshorasdetalle = $totalpesoshorasdetalle + $d->SubTotal;
                        }
                        if($d->Tecnico3 == $tecnico->Numero){
                            $totalhorasdetalles = $totalhorasdetalles + $d->Horas3;
                            $totalpesoshorasdetalle = $totalpesoshorasdetalle + $d->SubTotal;
                        }
                        if($d->Tecnico4 == $tecnico->Numero){
                            $totalhorasdetalles = $totalhorasdetalles + $d->Horas4;
                            $totalpesoshorasdetalle = $totalpesoshorasdetalle + $d->SubTotal;
                        }
                    }
                }
                $empresa = $this->empresa;
                $data[]=array(
                    "tecnico" => $tecnico->Numero,
                    "nombre" => $tecnico->Nombre,
                    "horas" => Helpers::convertirvalorcorrecto($totalhorasdetalles),
                    "total" => Helpers::convertirvalorcorrecto($totalpesoshorasdetalle)
                );
            }
            return view('reportes.ordenestrabajo.formato_excel_horas_tecnico', compact('data'));
        }
    }
}
