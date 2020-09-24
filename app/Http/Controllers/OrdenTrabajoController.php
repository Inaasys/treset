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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdenesDeTrabajoExport;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\Cliente;
use App\Servicio;
use App\Configuracion_Tabla;
use App\VistaOrdenTrabajo;

class OrdenTrabajoController extends ConfiguracionSistemaController
{
    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'OrdenesDeTrabajo')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function ordenes_trabajo(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->email, 'Ordenes de Trabajo');
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('ordenes_trabajo_guardar_configuracion_tabla');
        $rutacreardocumento = route('ordenes_trabajo_generar_pdfs');
        return view('registros.ordenestrabajo.ordenestrabajo', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','rutacreardocumento'));

    }
    //obtener todos los registros del modulo
    public function ordenes_trabajo_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaOrdenTrabajo::select($this->campos_consulta)->orderBy('Folio', 'DESC')->where('Periodo', $periodo)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Orden .'\')"><i class="material-icons">mode_edit</i></div> '. 
                                '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Orden .'\')"><i class="material-icons">cancel</i></div>  '.
                                '<div class="btn bg-green btn-xs waves-effect" data-toggle="tooltip" title="Terminar" onclick="autorizarordencompra(\''.$data->Orden .'\')"><i class="material-icons">playlist_add_check</i></div> ';

                    return $boton;
                })
                ->addColumn('Total', function($data){ return $data->Total; })
                ->addColumn('Kilometros', function($data){ return $data->Kilometros; })
                ->addColumn('Impuesto', function($data){ return $data->Impuesto; })
                ->addColumn('Importe', function($data){ return $data->Importe; })
                ->addColumn('Descuento', function($data){ return $data->Descuento; })
                ->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                ->addColumn('Iva', function($data){ return $data->Iva; })
                ->addColumn('Facturado', function($data){ return $data->Facturado; })
                ->addColumn('Costo', function($data){ return $data->Costo; })
                ->addColumn('Comision', function($data){ return $data->Comision; })
                ->addColumn('Utilidad', function($data){ return $data->Utilidad; })
                ->addColumn('HorasReales', function($data){ return $data->HorasReales; })
                ->addColumn('KmProximoServicio', function($data){ return $data->KmProximoServicio; })
                ->rawColumns(['operaciones'])
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
    //funcion exportar excel
    public function ordenes_trabajo_exportar_excel(){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new OrdenesDeTrabajoExport($this->campos_consulta), "ordenesdetrabajo.xlsx");    
    }  
    //configuracion tabla  
    public function ordenes_trabajo_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'OrdenesDeTrabajo')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('ordenes_trabajo');
    }

}
