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
use DB;
use Illuminate\Support\Collection;
use PDF;

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
        $urlgenerarformatopdf = route('reporte_horas_tecnico_generar_formato_pdf');
        return view('reportes.ordenestrabajo.horastecnico', compact('configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel', 'urlgenerarformatopdf', 'tipos_ordenes_trabajo'));
        
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
        //$reporte = $request->tiporeporte;
        $reporte = $request->statusorden;
        $tipoorden = $request->tipoorden;
        $statusorden = $request->statusorden;
        $string_tecnicos_seleccionados = $request->string_tecnicos_seleccionados; 
        if($string_tecnicos_seleccionados > 0){
            $todoslostecnicos = 0;
        }else{
            $todoslostecnicos = 1;
        }
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
                return Datatables::of($arreglo)->make(true);                          
                break;
            case "Porsucursal":
                break;
            case "Portecnico":
                break;
        }
    }

    //generar formato en excel
    public function reporte_horas_tecnico_generar_formato_excel(Request $request){
        return Excel::download(new ReportesOrdenesTrabajoHorasTecnico($request->fechainicialreporte, $request->fechafinalreporte, $request->tiporeporte, $request->tipoorden, $request->statusorden, $request->string_tecnicos_seleccionados, $this->numerodecimales, $this->empresa), "formatohorastecnico.xlsx"); 
    }

    //generar formato pdf
    public function reporte_horas_tecnico_generar_formato_pdf(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        //$reporte = $request->tiporeporte;
        $reporte = $request->statusorden;
        $tipoorden = $request->tipoorden;
        $statusorden = $request->statusorden;
        $string_tecnicos_seleccionados = $request->string_tecnicos_seleccionados; 
        if($string_tecnicos_seleccionados > 0){
            $todoslostecnicos = 0;
        }else{
            $todoslostecnicos = 1;
        }
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
                $consultarep = new Collection;
                if($todoslostecnicos == 1){       
                    foreach($data as $d){
                        if($d->Tecnico1 > 0){
                            $tecnico = Tecnico::where('Numero', $d->Tecnico1)->first();
                            $consultarep->push([
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
                            $consultarep->push([
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
                            $consultarep->push([
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
                            $consultarep->push([
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
                                $consultarep->push([
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
                                $consultarep->push([
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
                                $consultarep->push([
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
                                $consultarep->push([
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
        if($todoslostecnicos == 1){ 
            $data = array(
                'fechainicio' => $fechainicio,
                'fechaterminacion' => $fechaterminacion,
                'reporte' => $reporte,
                'tipoorden' => $tipoorden,
                'statusorden' => $statusorden,
                'string_tecnicos_seleccionados' => $string_tecnicos_seleccionados,
                'todoslostecnicos' => $todoslostecnicos,
                'numerodecimales' => $this->numerodecimales, 
                'empresa' => $this->empresa,
                'consultarep' => $consultarep
            );
        }else{
            $data = array(
                'fechainicio' => $fechainicio,
                'fechaterminacion' => $fechaterminacion,
                'reporte' => $reporte,
                'tipoorden' => $tipoorden,
                'statusorden' => $statusorden,
                'string_tecnicos_seleccionados' => $string_tecnicos_seleccionados,
                'todoslostecnicos' => $todoslostecnicos,
                'numerodecimales' => $this->numerodecimales, 
                'empresa' => $this->empresa,
                'consultarep' => $consultarep,
                'arraytecnicosseleccionados' => $arraytecnicosseleccionados

            );

        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('reportes.ordenestrabajo.formato_pdf_reportehorastecnico', compact('data'))
        ->setPaper('Letter')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }
}
