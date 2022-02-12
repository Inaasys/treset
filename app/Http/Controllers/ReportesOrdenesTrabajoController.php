<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Jenssegers\Date\Date;
use Helpers;
use DataTables;
use App\Configuracion_Tabla;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\TipoOrdenTrabajo;
use App\Tecnico;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesOrdenesTrabajoHorasTecnico;

class ReportesOrdenesTrabajoController extends ConfiguracionSistemaController{
    public function __construct(){
        parent::__construct(); //carga las configuraciones en el controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'Proveedores')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function reporte_ordenes_trabajo_horas_tecnico(){
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('proveedores_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('reporte_horas_tecnico_generar_formato_excel');
        $tipos_ordenes_trabajo = TipoOrdenTrabajo::where('Status', 'ALTA')->get();
        return view('reportes.ordenestrabajo.horastecnico', compact('configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','tipos_ordenes_trabajo'));
        
    }

    //obtener tecnicos
    public function reporte_horas_tecnico_obtener_tecnicos(Request $request){
        if($request->ajax()){
            $tecnicosseleccionados = array();
            foreach(explode(",", $request->string_tecnicos_seleccionados) as $tecnico){
                array_push($tecnicosseleccionados, $tecnico);
            }
            $data = Tecnico::where('Status', 'ALTA')->orderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
            ->addColumn('operaciones', function($data) use ($tecnicosseleccionados){
                if (in_array($data->Numero, $tecnicosseleccionados)) {
                    $checked = 'checked';
                }else{
                    $checked = '';
                }
                $checkbox = '<input type="checkbox" name="tecnicosseleccionados" class="tecnicosseleccionados" id="tecnico'.$data->Numero.'" class="filled-in" value="'.$data->Numero.'"  onchange="construirarraytecnicosseleccionados();" '.$checked.'/>'.
                            '<label for="tecnico'.$data->Numero.'">selecciona</label>';
                return $checkbox;
            })
            ->rawColumns(['operaciones'])
            ->make(true);
        } 
    }

    //generar reporte
    public function generar_reporte_horas_tecnico(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $reporte = $request->tiporeporte;
        $tipoorden = $request->tipoorden;
        $statusorden = $request->statusorden;
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
                return DataTables::of($data)
                ->rawColumns(['operaciones'])
                ->make(true);
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

                if($request->todoslostecnicos == 1){
                    $tecnicos = Tecnico::where('Status', 'ALTA')->get();
                    $string_tecnicos = '';
                    foreach($tecnicos as $tec){
                        $string_tecnicos = $string_tecnicos.",".$tec->Numero;
                    }
                    $string_tecnicos_seleccionados = substr($string_tecnicos, 1);
                }else{
                    $string_tecnicos_seleccionados = $request->string_tecnicos_seleccionados;
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
                return DataTables::of($data)
                ->rawColumns(['operaciones'])
                ->make(true); 
                break;
        }
    }

    //generar formato en excel
    public function reporte_horas_tecnico_generar_formato_excel(Request $request){
        return Excel::download(new ReportesOrdenesTrabajoHorasTecnico($request->fechainicialreporte, $request->fechafinalreporte, $request->tiporeporte, $request->tipoorden, $request->statusorden, $request->string_tecnicos_seleccionados, $request->todoslostecnicos, $this->numerodecimales, $this->empresa), "formatohorastecnico.xlsx"); 
    }
}
