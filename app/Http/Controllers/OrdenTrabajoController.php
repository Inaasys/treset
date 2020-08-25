<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use DB;
use PDF;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\Cliente;
use App\Servicio;

class OrdenTrabajoController extends ConfiguracionSistemaController
{
    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function ordenes_trabajo(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->email, 'Ordenes de Trabajo');
        return view('registros.ordenestrabajo.ordenestrabajo', compact('serieusuario'));

    }
    //obtener todos los registros del modulo
    public function ordenes_trabajo_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = DB::table('Ordenes de Trabajo as ot')
            ->Join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
            ->select('ot.Orden AS Orden', 'ot.Serie AS Serie', 'ot.Folio AS Folio', 'ot.Caso AS Caso', 'ot.Fecha AS Fecha', 'ot.Status AS Status', 'ot.Tipo AS Tipo', 'ot.Unidad AS Unidad', 'ot.Cliente AS Cliente', 'c.Nombre AS Nombre', 'ot.Total AS Total', 'ot.Vin AS Vin', 'ot.Pedido AS Pedido', 'ot.Marca AS Marca', 'ot.Economico AS Economico', 'ot.Placas AS Placas', 'ot.Año AS Año', 'ot.Kilometros AS Kilometros', 'ot.Reclamo AS Reclamo', 'ot.Motor AS Motor', 'ot.MotivoBaja AS MotivoBaja', 'ot.Usuario AS Usuario', 'ot.Equipo AS Equipo', 'ot.Periodo AS Periodo')
            ->where('ot.Periodo', $periodo)
            ->orderBy('ot.Folio', 'DESC')
            ->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Orden .'\')"><i class="material-icons">mode_edit</i></div> '. 
                                '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Orden .'\')"><i class="material-icons">cancel</i></div>  '.
                                '<div class="btn bg-green btn-xs waves-effect" data-toggle="tooltip" title="Terminar" onclick="autorizarordencompra(\''.$data->Orden .'\')"><i class="material-icons">playlist_add_check</i></div> ';

                    return $boton;
                })
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->rawColumns(['operaciones','Total'])
                ->make(true);
        } 
    }


        //buscar folio on key up
        public function ordenes_trabajo_buscar_folio_string_like(Request $request){
            if($request->ajax()){
                $string = $request->string;
                $data = OrdenTrabajo::where('Orden', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
                return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Orden .'\')"><i class="material-icons">done</i></div> ';
                        return $boton;
                    })
                    ->addColumn('Total', function($data){
                        $total = Helpers::convertirvalorcorrecto($data->Total);
                        return $total;
                    })
                    ->rawColumns(['operaciones','Total'])
                    ->make(true);
            } 
        }
        //generacion de formato en PDF
        public function ordenes_trabajo_generar_pdfs(Request $request){
            $tipogeneracionpdf = $request->tipogeneracionpdf;
            if($tipogeneracionpdf == 0){
                $ordenestrabajo = OrdenTrabajo::whereIn('Orden', $request->arraypdf)->orderBy('Folio', 'ASC')->take(500)->get(); 
            }else{
                //$contrarecibos = ContraRecibo::where('Fecha', $request->anopdf)->get(); 
                $fechainiciopdf = date($request->fechainiciopdf);
                $fechaterminacionpdf = date($request->fechaterminacionpdf);
                $ordenestrabajo = OrdenTrabajo::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(500)->get();
            }
            $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
            $data=array();
            foreach ($ordenestrabajo as $ot){
                $ordentrabajodetalle = OrdenTrabajoDetalle::where('Orden', $ot->Orden)->get();
                $datadetalle=array();
                foreach($ordentrabajodetalle as $otd){
                    $serviciodetalle = Servicio::where('Codigo', $otd->Codigo)->first();
                    $datadetalle[]=array(
                        "codigodetalle"=>$otd->Codigo,
                        "descripciondetalle"=>$otd->Descripcion,
                        "unidaddetalle"=>$otd->Unidad,
                        "cantidaddetalle"=> Helpers::convertirvalorcorrecto($otd->Cantidad),
                        "preciodetalle" => Helpers::convertirvalorcorrecto($otd->Precio),
                        "descuentodetalle" => Helpers::convertirvalorcorrecto($otd->Descuento),
                        "subtotaldetalle" => Helpers::convertirvalorcorrecto($otd->SubTotal),
                        "totaldetalle" => Helpers::convertirvalorcorrecto($otd->Total)
                    );
                } 
                $cliente = Cliente::where('Numero', $ot->Cliente)->first();
                $data[]=array(
                          "ordentrabajo"=>$ot,
                          "importeordentrabajo"=>Helpers::convertirvalorcorrecto($ot->Importe),
                          "descuentoordentrabajo"=>Helpers::convertirvalorcorrecto($ot->Descuento),
                          "subtotalordentrabajo"=>Helpers::convertirvalorcorrecto($ot->SubTotal),
                          "ivaordentrabajo"=>Helpers::convertirvalorcorrecto($ot->Iva),
                          "totalordentrabajo"=>Helpers::convertirvalorcorrecto($ot->Total),
                          "cliente" => $cliente,
                          "datadetalle" => $datadetalle
                );
            }
            //$footerHtml = view()->make('seccionespdf.footer', compact('fechaformato'))->render();
            $pdf = PDF::loadView('registros.ordenestrabajo.formato_pdf_ordenestrabajo', compact('data'))
            //->setOption('footer-html', $footerHtml, 'Página [page]')
            ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            ->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-bottom', 10);
            //return $pdf->download('contrarecibos.pdf');
            return $pdf->stream();
        }


}
