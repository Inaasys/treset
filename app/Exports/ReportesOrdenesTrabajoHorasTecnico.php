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
    private $todoslostecnicos;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $tiporeporte, $tipoorden, $statusorden, $string_tecnicos_seleccionados, $todoslostecnicos, $numerodecimales, $empresa){
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->tiporeporte = $tiporeporte;
        $this->tipoorden = $tipoorden;
        $this->statusorden = $statusorden;
        $this->string_tecnicos_seleccionados = $string_tecnicos_seleccionados;
        $this->todoslostecnicos = $todoslostecnicos;
        $this->numerodecimales = $numerodecimales;
        $this->empresa = $empresa;
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Horas Técnico';
    }

    public function view(): View{
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $reporte = $this->tiporeporte;
        $tipoorden = $this->tipoorden;
        $statusorden = $this->statusorden;
        switch($reporte){
            case "Porsucursal":
                $ordenes = OrdenTrabajo::
                    whereDate('Fecha', '>=', $fechainicio)->whereDate('Fecha', '<=', $fechaterminacion)
                    ->where(function($q) use ($tipoorden) {
                        if($tipoorden != 'TODOS'){
                            $q->where('Tipo', $tipoorden);
                        }
                    })
                    ->where(function($q) use ($statusorden) {
                        if($statusorden != 'TODOS'){
                            if($statusorden == 'FACTURADAS'){
                                $q->where('Status', 'like', '%-%');
                            }else{
                                $q->where('Status', $statusorden);
                            }
                        }
                    })
                    ->get();
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
                break;
            case "Portecnico":
                $ordenes = OrdenTrabajo::
                whereDate('Fecha', '>=', $fechainicio)->whereDate('Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($tipoorden) {
                    if($tipoorden != 'TODOS'){
                        $q->where('Tipo', $tipoorden);
                    }
                })
                ->where(function($q) use ($statusorden) {
                    if($statusorden != 'TODOS'){
                        if($statusorden == 'FACTURADAS'){
                            $q->where('Status', 'like', '%-%');
                        }else{
                            $q->where('Status', $statusorden);
                        }
                    }
                })
                ->get();
                $data=array();
                $totalhorasdetalles = 0;
                $totalpesoshorasdetalle = 0;

                if($this->todoslostecnicos == 1){
                    $tecnicos = Tecnico::where('Status', 'ALTA')->get();
                    $string_tecnicos = '';
                    foreach($tecnicos as $tec){
                        $string_tecnicos = $string_tecnicos.",".$tec->Numero;
                    }
                    $string_tecnicos_seleccionados = substr($string_tecnicos, 1);
                }else{
                    $string_tecnicos_seleccionados = $this->string_tecnicos_seleccionados;
                }
                foreach(explode(",", $string_tecnicos_seleccionados) as $tecnico){
                    $totalhorasdetalles = 0;
                    $totalpesoshorasdetalle = 0;
                    $tecnico = Tecnico::where('Numero', $tecnico)->first();
                    foreach ($ordenes as $o){
                        $detalles = OrdenTrabajoDetalle::where('Orden', $o->Orden)->get();
                        foreach($detalles as $d){
                            if($d->Tecnico1 == $tecnico->Numero){
                                $totalhorasdetalles = $totalhorasdetalles + $d->Horas1;
                                $subtotaltecnico = $d->Precio * $d->Horas1;
                                $totalpesoshorasdetalle = $totalpesoshorasdetalle + $subtotaltecnico;
                            }
                            if($d->Tecnico2 == $tecnico->Numero){
                                $totalhorasdetalles = $totalhorasdetalles + $d->Horas2;
                                $subtotaltecnico = $d->Precio * $d->Horas2;
                                $totalpesoshorasdetalle = $totalpesoshorasdetalle + $subtotaltecnico;
                            }
                            if($d->Tecnico3 == $tecnico->Numero){
                                $totalhorasdetalles = $totalhorasdetalles + $d->Horas3;
                                $subtotaltecnico = $d->Precio * $d->Horas3;
                                $totalpesoshorasdetalle = $totalpesoshorasdetalle + $subtotaltecnico;
                            }
                            if($d->Tecnico4 == $tecnico->Numero){
                                $totalhorasdetalles = $totalhorasdetalles + $d->Horas4;
                                $subtotaltecnico = $d->Precio * $d->Horas4;
                                $totalpesoshorasdetalle = $totalpesoshorasdetalle + $subtotaltecnico;
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
                break;
        }
    }
}
