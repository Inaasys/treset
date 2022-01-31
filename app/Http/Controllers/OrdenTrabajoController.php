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
use App\Exports\PlantillasOrdenesTrabajoExport;
use App\Imports\OrdenesTrabajoImport;
use App\Exports\OrdenesDeTrabajoExport;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\CotizacionServicio;
use App\CotizacionServicioDetalle;
use App\TipoOrdenTrabajo;
use App\TipoUnidad;
use App\Cliente;
use App\Agente;
use App\Tecnico;
use App\Vine;
use App\Servicio;
use App\Configuracion_Tabla;
use App\VistaOrdenTrabajo;
use App\BitacoraDocumento;
use Config;
use Mail;
use App\Serie;
use LynX39\LaraPdfMerger\Facades\PdfMerger;
use Storage; 
use ZipArchive;

class OrdenTrabajoController extends ConfiguracionSistemaController
{
    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function ordenes_trabajo(){
        $contarserieusuario = Serie::where('Documento', 'Ordenes de Trabajo')->where('Usuario', Auth::user()->user)->count();
        if($contarserieusuario > 0){
            $serie = Serie::where('Documento', 'Ordenes de Trabajo')->where('Usuario', Auth::user()->user)->first();
            $serieusuario = $serie->Serie;
        }else{
            $serieusuario = 'A';

        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('OrdenesDeTrabajo', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('ordenes_trabajo_guardar_configuracion_tabla');
        $rutacreardocumento = route('ordenes_trabajo_generar_pdfs');
        $urlgenerarformatoexcel = route('ordenes_trabajo_exportar_excel');
        $urlgenerarplantilla = route('ordenes_trabajo_generar_plantilla');
        return view('registros.ordenestrabajo.ordenestrabajo', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','rutacreardocumento','urlgenerarformatoexcel','urlgenerarplantilla'));
    }
    //obtener todos los registros del modulo
    public function ordenes_trabajo_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('OrdenesDeTrabajo', Auth::user()->id);
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaOrdenTrabajo::select($configuraciones_tabla['campos_consulta'])->where('Periodo', $periodo);
            return DataTables::of($data)
                    ->order(function ($query) use($configuraciones_tabla) {
                        if($configuraciones_tabla['configuracion_tabla']->primerordenamiento != 'omitir'){
                            $query->orderBy($configuraciones_tabla['configuracion_tabla']->primerordenamiento, '' . $configuraciones_tabla['configuracion_tabla']->formaprimerordenamiento . '');
                        }
                        if($configuraciones_tabla['configuracion_tabla']->segundoordenamiento != 'omitir'){
                            $query->orderBy($configuraciones_tabla['configuracion_tabla']->segundoordenamiento, '' . $configuraciones_tabla['configuracion_tabla']->formasegundoordenamiento . '');
                        }
                        if($configuraciones_tabla['configuracion_tabla']->tercerordenamiento != 'omitir'){
                            $query->orderBy($configuraciones_tabla['configuracion_tabla']->tercerordenamiento, '' . $configuraciones_tabla['configuracion_tabla']->formatercerordenamiento . '');
                        }
                    })
                    ->withQuery('sumaimporte', function($data) {
                        return $data->sum('Importe');
                    })
                    ->withQuery('sumadescuento', function($data) {
                        return $data->sum('Descuento');
                    })
                    ->withQuery('sumasubtotal', function($data) {
                        return $data->sum('SubTotal');
                    })
                    ->withQuery('sumaiva', function($data) {
                        return $data->sum('Iva');
                    })
                    ->withQuery('sumatotal', function($data) {
                        return $data->sum('Total');
                    })
                    ->withQuery('sumacosto', function($data) {
                        return $data->sum('Costo');
                    })
                    ->withQuery('sumacomision', function($data) {
                        return $data->sum('Comision');
                    })
                    ->withQuery('sumautilidad', function($data) {
                        return $data->sum('Utilidad');
                    })
                    ->addColumn('operaciones', function($data){
                        $operaciones =  '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Orden .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="terminar(\''.$data->Orden .'\')">Terminar OT</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="abrirnuevamente(\''.$data->Orden .'\')">Abrir Nuevamente OT</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Orden .'\')">Bajas</a></li>'.
                                                '<li><a href="'.route('ordenes_trabajo_generar_pdfs_indiv',$data->Orden).'" target="_blank">Generar Documento</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Orden .'\')">Enviar Documento por Correo</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
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
    //descargar plantilla
    public function ordenes_trabajo_generar_plantilla(){
        return Excel::download(new PlantillasOrdenesTrabajoExport(), "plantillaordenestrabajo.xlsx"); 
    }
    //cargar partidas excel
    public function ordenes_trabajo_cargar_partidas_excel(Request $request){
        $arrayexcel =  Excel::toArray(new OrdenesTrabajoImport, request()->file('partidasexcel'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $filasdetallesordentrabajo = '';
        $contadorservicios = $request->contadorservicios;
        $contadorfilas = $request->contadorfilas;
        $item = $request->item;
        $hoy = $request->hoy;
        $tipooperacion = $request->tipooperacion;
        $arraycodigosyaagregados = $porciones = explode(",", $request->arraycodigospartidas);
        foreach($partidasexcel as $partida){
            if($rowexcel > 0){
                //if (in_array(strtoupper($partida[0]), $arraycodigosyaagregados)) {
                    
                //}else{
                    $codigoabuscar = $partida[0];
                    $cantidadpartida = $partida[1];
                    $contarservicios = Servicio::where('Codigo', ''.$codigoabuscar.'')->count();
                    if($contarservicios > 0){
                        $servicio = Servicio::where('Codigo', ''.$codigoabuscar.'')->first();
                        if(Helpers::convertirvalorcorrecto($cantidadpartida) == 0){
                            $cantidad = 1;
                        }else{
                            $cantidad = $cantidadpartida;
                        }
                        //precio de la partidad
                        $preciopartidaservicio = $servicio->Venta;
                        //importe de la partida
                        $importepartidaservicio =  $cantidad*$preciopartidaservicio;
                        //subtotal de la partida
                        $subtotalpartidaservicio =  $importepartidaservicio-0;
                        //iva porcentaje partida 
                        $ivaporcentajepartidaservicio = Helpers::convertirvalorcorrecto(16);
                        //iva en pesos de la partida
                        $multiplicacionivapesospartida = $subtotalpartidaservicio*$ivaporcentajepartidaservicio;
                        $ivapesospartidaservicio = $multiplicacionivapesospartida/100;
                        //total en pesos de la partida
                        $totalpesospartidaservicio = $subtotalpartidaservicio+$ivapesospartidaservicio;
                        //costo partida servicio
                        $costopartidaservicio = Helpers::convertirvalorcorrecto(0);
                        //costo total
                        $costototalpartidaservicio  = $costopartidaservicio*$cantidad;
                        //comision de la partida
                        $comisionporcentajepartidaservicio = $subtotalpartidaservicio*0;
                        $comisionespesospartidaservicio = $comisionporcentajepartidaservicio/100;
                        //utilidad de la partida
                        $utilidadpartidaservicio = $subtotalpartidaservicio-$costototalpartidaservicio-$comisionespesospartidaservicio;
                        $tipo = "alta";
                        $filasdetallesordentrabajo= $filasdetallesordentrabajo.
                        '<tr class="filasservicios" id="filaservicio'.$contadorservicios.'">'.
                            '<td class="tdmod"><div class="divorinputmodmd">'.
                                '<div class="btn bg-red btn-xs" data-toggle="tooltip" title="Eliminar Fila" onclick="eliminarfila('.$contadorservicios.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly> '.
                                '<div class="btn bg-blue btn-xs" data-toggle="tooltip" title="Asignar Técnicos" onclick="asignaciontecnicos('.$contadorservicios.')">Asignar técnicos</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control tipofila" name="tipofila[]" value="agregado" readonly><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$servicio->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$servicio->Codigo.'</b></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($servicio->Servicio, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control unidadpartidad" name="unidadpartidad[]" value="'.$servicio->Unidad.'" readonly data-parsley-length="[1, 5]">'.$servicio->Unidad.'</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($preciopartidaservicio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($importepartidaservicio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalpartidaservicio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($ivaporcentajepartidaservicio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($ivapesospartidaservicio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($totalpesospartidaservicio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($costopartidaservicio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($costototalpartidaservicio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($comisionporcentajepartidaservicio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto($comisionespesospartidaservicio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($utilidadpartidaservicio).'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs departamentopartida" name="departamentopartida[]" value="SERVICIO" readonly data-parsley-length="[1, 20]"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs cargopartida" name="cargopartida[]" value="SERVICIO" readonly data-parsley-length="[1, 20]"></td>'.
                            '<td class="tdmod"><input type="datetime-local" class="form-control divorinputmodxl fechapartida" name="fechapartida[]" value="'.$hoy.'" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs traspasopartida" name="traspasopartida[]" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs comprapartida" name="comprapartida[]" readonly data-parsley-length="[1, 20]"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs usuariopartida" name="usuariopartida[]" value="'.Auth::user()->user.'" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxl anotacionespartida" name="anotacionespartida[]" data-parsley-length="[1, 255]" autocomplete="off"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs statuspartida" name="statuspartida[]" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs itempartida" name="itempartida[]" value="'.$item.'" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida1" name="numerotecnicopartida1[]" value="0" readonly><input type="text" class="form-control divorinputmodl tecnicopartida1" name="tecnicopartida1[]" value="0" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida2" name="numerotecnicopartida2[]" value="0" readonly><input type="text" class="form-control divorinputmodl tecnicopartida2" name="tecnicopartida2[]" value="0" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida3" name="numerotecnicopartida3[]" value="0" readonly><input type="text" class="form-control divorinputmodl tecnicopartida3" name="tecnicopartida3[]" value="0" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida4" name="numerotecnicopartida4[]" value="0" readonly><input type="text" class="form-control divorinputmodl tecnicopartida4" name="tecnicopartida4[]" value="0" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida1" name="horaspartida1[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida2" name="horaspartida2[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida3" name="horaspartida3[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida4" name="horaspartida4[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs promocionpartida" name="promocionpartida[]" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs partidapartida" name="partidapartida[]" value="'.$item.'" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs almacenpartida" name="almacenpartida[]" value="0" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs cotizacionpartida" name="cotizacionpartida[]" readonly data-parsley-length="[1, 20]"></td>'.
                        '</tr>';
                        //array_push($arraycodigosyaagregados, $servicio->Codigo);
                        $contadorservicios++;
                        $contadorfilas++;
                        $item++;
                    }
                //}
            }
            $rowexcel++;
        }
        $data = array(
            "filasdetallesordentrabajo" => $filasdetallesordentrabajo,
            "contadorservicios" => $contadorservicios,
            "contadorfilas" => $contadorfilas,
            "item" => $item
        );
        return response()->json($data); 
    }
    //obtener series documento
    public function ordenes_trabajo_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'Ordenes de Trabajo')->where('Usuario', Auth::user()->user)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarseriedocumento(\''.$data->Serie.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener ultimo folio de la serie seleccionada
    public function ordenes_trabajo_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\OrdenTrabajo',$request->Serie);
        return response()->json($folio);
    }
    //obtener ultimo folio de la orden de trabajo
    public function ordenes_trabajo_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\OrdenTrabajo', $request->serie);
        return response()->json($folio);
    }
    //obtener tipos de ordenes de trbaajo
    public function ordenes_trabajo_obtener_tipos_ordenes_trabajo(){
        $tipos_ordenes_trabajo = TipoOrdenTrabajo::where('STATUS', 'ALTA')->get();
        $select_tipos_ordenes_trabajo = "<option disabled hidden>Selecciona...</option>";
        foreach($tipos_ordenes_trabajo as $tipo){
            $select_tipos_ordenes_trabajo = $select_tipos_ordenes_trabajo."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_trabajo);
    }
    //obtener tipos de unidades
    public function ordenes_trabajo_obtener_tipos_unidades(){
        $tipos_unidades= TipoUnidad::where('STATUS', 'ALTA')->get();
        $select_tipos_unidades = "<option disabled hidden>Selecciona...</option>";
        foreach($tipos_unidades as $tipo){
            $select_tipos_unidades = $select_tipos_unidades."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_unidades);
    }
    //obtener fecha actual datetimelocal
    public function ordenes_trabajo_obtener_fecha_actual_datetimelocal(){
        $fechadatetimelocal = Helpers::fecha_exacta_accion_datetimelocal();
        return response()->json($fechadatetimelocal);
    }
    //obtener clientes factura a
    public function ordenes_trabajo_obtener_clientes_facturaa(Request $request){
        if($request->ajax()){
            $data = DB::table('Clientes as c')
            ->leftJoin('Agentes as a', 'a.Numero', '=', 'c.Agente')
            ->select('c.Numero', 'c.Nombre', 'c.Plazo', 'c.Rfc', 'c.Agente', 'c.Credito', 'c.Saldo', 'c.Status', 'c.Municipio', 'c.Tipo', 'a.Numero AS NumeroAgente', 'a.Nombre AS NombreAgente')
            ->where('c.Status', 'ALTA')
            ->orderBy("Numero", "DESC")
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclientefacturaa('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$data->NumeroAgente.'\',\''.$data->NombreAgente.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener cliente factura a por numero
    public function ordenes_trabajo_obtener_cliente_facturaa_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $plazo = '';
        $numeroagente = '';
        $nombreagente = '';
        $existecliente = Cliente::where('Numero', $request->numeroclientefacturaa)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numeroclientefacturaa)->where('Status', 'ALTA')->first();
            $agente = Agente::where('Numero', $cliente->Agente)->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
            $plazo = $cliente->Plazo;
            $numeroagente = $agente->Numero;
            $nombreagente = $agente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'plazo' => $plazo,
            'numeroagente' => $numeroagente,
            'nombreagente' => $nombreagente,
        );
        return response()->json($data);
    }

    //obtener clientes para campo Del Cliente
    public function ordenes_trabajo_obtener_clientes_delcliente(Request $request){
        if($request->ajax()){
            $data = Cliente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclientedelcliente('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }  
    }

    //obtener cliente del lciente por numero
    public function ordenes_trabajo_obtener_cliente_delcliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $plazo = '';
        $existecliente = Cliente::where('Numero', $request->numeroclientedelcliente)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numeroclientedelcliente)->where('Status', 'ALTA')->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
            $plazo = $cliente->Plazo;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'plazo' => $plazo,
        );
        return response()->json($data);
    }

    //obtener agentes
    public function ordenes_trabajo_obtener_agentes(Request $request){
        if($request->ajax()){
            $data = Agente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaragente('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener agente por numero
    public function ordenes_trabajo_obtener_agente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existeagente = Agente::where('Numero', $request->numeroagente)->where('Status', 'ALTA')->count();
        if($existeagente > 0){
            $agente = Agente::where('Numero', $request->numeroagente)->where('Status', 'ALTA')->first();
            $numero = $agente->Numero;
            $nombre = $agente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data); 
    }

    //obtener vines
    public function ordenes_trabajo_obtener_vines(Request $request){
        if($request->ajax()){
            $data = Vine::where('Cliente', $request->numeroclientefacturaa)->where('Status', 'ALTA')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarvin('.$data->Cliente.',\''.$data->Economico.'\',\''.$data->Vin.'\',\''.$data->Placas.'\',\''.$data->Motor.'\',\''.$data->Marca.'\',\''.$data->Modelo.'\',\''.$data->Año.'\',\''.$data->Color.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener vin por numero
    public function ordenes_trabajo_obtener_vin_por_numero(Request $request){
        $cliente = '';
        $economico = '';
        $vin = '';
        $placas = '';
        $motor = '';
        $marca = '';
        $modelo = '';
        $año = '';
        $color = '';
        $existevin = Vine::where('Vin', $request->vin)->where('Cliente', $request->numeroclientefacturaa)->where('Status', 'ALTA')->count();
        if($existevin > 0){
            $v = DB::table('Vines')->where('Vin', $request->vin)->where('Cliente', $request->numeroclientefacturaa)->where('Status', 'ALTA')->first();
            $cliente = $v->Cliente;
            $economico = $v->Economico;
            $vin = $v->Vin;
            $placas = $v->Placas;
            $motor = $v->Motor;
            $marca = $v->Marca;
            $modelo = $v->Modelo;
            $año = $v->Año;
            $color = $v->Color;
        }
        $data = array(
            'cliente' => $cliente,
            'economico' => $economico,
            'vin' => $vin,
            'placas' => $placas,
            'motor' => $motor,
            'marca' => $marca,
            'modelo' => $modelo,
            'año' => $año,
            'color' => $color
        );
        return response()->json($data); 
    }

    //obtener cotizaciones 
    public function ordenes_trabajo_obtener_cotizaciones(Request $request){
        if($request->ajax()){
            $mesactual = date("m");
            $data = DB::table('Cotizaciones Servicio as cots')
                        ->leftJoin('Clientes as c', 'c.Numero', '=', 'cots.Cliente')
                        ->select('cots.Cotizacion', 'cots.Folio', 'cots.Fecha', 'cots.Cliente', 'c.Nombre as Nombre', 'cots.Unidad', 'cots.Plazo as Dias', 'cots.Total')
                        ->where('cots.Cliente', $request->numerocliente)
                        ->where('cots.Status', 'POR CARGAR')
                        ->whereMonth('cots.Fecha', '=', $mesactual)
                        ->orderBy("Folio", "DESC")
                        ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcotizacion('.$data->Folio.',\''.$data->Cotizacion .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){ return Helpers::fecha_espanol($data->Fecha);  })
                    ->addColumn('Total', function($data){ return Helpers::convertirvalorcorrecto($data->Total);  })

                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener datos de la cotizaciones seleccionada
    public function ordenes_trabajo_obtener_cotizacion(Request $request){
        $cotizacion = CotizacionServicio::where('Cotizacion', $request->Cotizacion)->first();
        //detalles cotizacion
        $detallescotizacion = CotizacionServicioDetalle::where('Cotizacion', $request->Cotizacion)->where('Departamento', 'SERVICIO')->get();
        $numerodetallescotizacion = CotizacionServicioDetalle::where('Cotizacion', $request->Cotizacion)->where('Departamento', 'SERVICIO')->count();
        if($numerodetallescotizacion > 0){
            $filasdetallescotizacion = '';
            $contadorservicios = 0;
            $contadorfilas = 0;
            $tipo = "alta";
            foreach($detallescotizacion as $dc){
                $tipo = "alta";
                $filasdetallescotizacion = $filasdetallescotizacion.
                '<tr class="filasservicios" id="filaservicio'.$contadorservicios.'">'.
                    '<td class="tdmod">'.
                        '<div class="divorinputmodmd">'.
                            '<div class="btn bg-red btn-xs" data-toggle="tooltip" title="Eliminar Fila" onclick="eliminarfila('.$contadorservicios.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipo.'" readonly> '.
                            '<div class="btn bg-blue btn-xs" data-toggle="tooltip" title="Asignar Técnicos" onclick="asignaciontecnicos('.$contadorservicios.')">Asignar técnicos</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control tipofila" name="tipofila[]" value="agregado" readonly><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dc->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$dc->Codigo.'</b></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($dc->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadpartidad" name="unidadpartidad[]" value="'.$dc->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dc->Unidad.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Utilidad).'"data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs departamentopartida" name="departamentopartida[]" value="SERVICIO" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs cargopartida" name="cargopartida[]" value="SERVICIO" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="datetime-local" class="form-control divorinputmodxl fechapartida" name="fechapartida[]" value="'.Helpers::formatoinputdatetime($dc->Fecha).'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs traspasopartida" name="traspasopartida[]" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs comprapartida" name="comprapartida[]" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs usuariopartida" name="usuariopartida[]" value="'.Auth::user()->user.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl anotacionespartida" name="anotacionespartida[]" data-parsley-length="[1, 255]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs statuspartida" name="statuspartida[]" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs itempartida" name="itempartida[]" value="'.$dc->Item.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida1" name="numerotecnicopartida1[]" value="0" readonly><input type="text" class="form-control divorinputmodl tecnicopartida1" name="tecnicopartida1[]" value="0" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida2" name="numerotecnicopartida2[]" value="0" readonly><input type="text" class="form-control divorinputmodl tecnicopartida2" name="tecnicopartida2[]" value="0" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida3" name="numerotecnicopartida3[]" value="0" readonly><input type="text" class="form-control divorinputmodl tecnicopartida3" name="tecnicopartida3[]" value="0" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida4" name="numerotecnicopartida4[]" value="0" readonly><input type="text" class="form-control divorinputmodl tecnicopartida4" name="tecnicopartida4[]" value="0" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida1" name="horaspartida1[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida2" name="horaspartida2[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida3" name="horaspartida3[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida4" name="horaspartida4[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs promocionpartida" name="promocionpartida[]" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs partidapartida" name="partidapartida[]" value="'.$dc->Item.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs almacenpartida" name="almacenpartida[]" value="0" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs cotizacionpartida" name="cotizacionpartida[]" value="'.$dc->Cotizacion.'" readonly data-parsley-length="[1, 20]"></td>'.
                '</tr>';
                $contadorservicios++;
                $contadorfilas++;
            }
        }else{
            $filasdetallescotizacion = '';
        }        
        $data = array(
            "cotizacion" => $cotizacion,
            "filasdetallescotizacion" => $filasdetallescotizacion,
            "numerodetallescotizacion" => $numerodetallescotizacion,
            "contadorservicios" => $contadorservicios,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdatetime($cotizacion->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($cotizacion->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($cotizacion->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($cotizacion->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($cotizacion->Iva),
            "total" => Helpers::convertirvalorcorrecto($cotizacion->Total),
            "kilometros" => Helpers::convertirvalorcorrecto($cotizacion->Kilometros)
        );
        return response()->json($data);
    }

    //obtener servicios
    public function ordenes_trabajo_obtener_servicios(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $tipooperacion = $request->tipooperacion;
            $data = Servicio::where('Codigo', 'like', '%'.$codigoabuscar.'%')->where('Status', 'ALTA');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaservicio(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Servicio, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Venta).'\',\''.Helpers::convertirvalorcorrecto($data->Cantidad).'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$tipooperacion.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Venta', function($data){ 
                        return Helpers::convertirvalorcorrecto($data->Venta);
                    })
                    ->addColumn('Cantidad', function($data){ 
                        return Helpers::convertirvalorcorrecto($data->Cantidad);
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener servicio por codigo
    public function ordenes_trabajo_obtener_servicio_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        $contarservicios = Servicio::where('Codigo', $codigoabuscar)->where('Status', 'ALTA')->count();
        if($contarservicios > 0){
            $servicio = Servicio::where('Codigo', $codigoabuscar)->where('Status', 'ALTA')->first();
            $data = array(
                'Codigo' => $servicio->Codigo,
                'Servicio' => htmlspecialchars($servicio->Servicio, ENT_QUOTES),
                'Unidad' => $servicio->Unidad,
                'Costo' => Helpers::convertirvalorcorrecto($servicio->Costo),
                'Venta' => Helpers::convertirvalorcorrecto($servicio->Venta),
                'Cantidad' => Helpers::convertirvalorcorrecto($servicio->Cantidad),
                'ClaveProducto' => $servicio->ClaveProducto,
                'ClaveUnidad' => $servicio->ClaveUnidad,
                'contarservicios' => $contarservicios
            );
        }else{
            $data = array(
                'Codigo' => '',
                'Servicio' => '',
                'Unidad' => '',
                'Costo' => '',
                'Venta' => '',
                'Cantidad' => '',
                'ClaveProducto' => '',
                'ClaveUnidad' => '',
                'contarservicios' => $contarservicios
            );
        }
        return response()->json($data);
    }
    //obtener tecnicos
    public function ordenes_trabajo_obtener_tecnicos(Request $request){
        if($request->ajax()){
            $data = Tecnico::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) {
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilatecnico('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //alta/guardar
    public function ordenes_trabajo_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //$folio = Helpers::ultimofolioserietablamodulos('App\OrdenTrabajo', $request->serie);
        $folio = $request->folio;
        $orden = $folio.'-'.$request->serie;
        $ExisteOrden = OrdenTrabajo::where('Orden', $orden)->first();
	    if($ExisteOrden == true){
	        $OrdenTrabajo = 1;
	    }else{  
            //INGRESAR DATOS A TABLA ORDEN DE TRABAJO
            $OrdenTrabajo = new OrdenTrabajo;
            $OrdenTrabajo->Orden=$orden;
            $OrdenTrabajo->Serie=$request->serie;
            $OrdenTrabajo->Folio=$folio;
            $OrdenTrabajo->Tipo=$request->tipoorden;
            $OrdenTrabajo->Unidad=$request->tipounidad;
            $OrdenTrabajo->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
            $OrdenTrabajo->Entrega=Carbon::parse($request->fechaentregapromesa)->toDateTimeString();
            $OrdenTrabajo->Cliente=$request->numeroclientefacturaa;
            $OrdenTrabajo->DelCliente=$request->numeroclientedelcliente;
            $OrdenTrabajo->Agente=$request->numeroagente;
            $OrdenTrabajo->Caso=$request->caso;
            $OrdenTrabajo->TipoServicio=$request->tiposervicio;
            $OrdenTrabajo->Vin=$request->vin;
            $OrdenTrabajo->Motor=$request->motor;
            $OrdenTrabajo->Marca=$request->marca;
            $OrdenTrabajo->Modelo=$request->modelo;
            $OrdenTrabajo->Año=$request->ano;
            $OrdenTrabajo->Kilometros=$request->kilometros;
            $OrdenTrabajo->Placas=$request->placas;
            $OrdenTrabajo->Economico=$request->economico;
            $OrdenTrabajo->Color=$request->color;
            $OrdenTrabajo->KmProximoServicio=$request->kmproxservicio;
            $OrdenTrabajo->FechaRecordatorio=Carbon::parse($request->fecharecordatoriocliente)->toDateTimeString();
            $OrdenTrabajo->Reclamo=$request->reclamo;
            $OrdenTrabajo->Pedido=$request->ordencliente;
            $OrdenTrabajo->Campaña=$request->campana;
            $OrdenTrabajo->Promocion=$request->promocion;
            $OrdenTrabajo->Bahia=$request->bahia;
            $OrdenTrabajo->HorasReales=$request->horasreales;
            $OrdenTrabajo->Rodar=$request->rodar;
            $OrdenTrabajo->Plazo=$request->plazodias;
            $OrdenTrabajo->Falla=$request->falla;
            $OrdenTrabajo->ObsOrden=$request->observaciones;
            $OrdenTrabajo->Causa=$request->causa;
            $OrdenTrabajo->Correccion=$request->correccion;
            $OrdenTrabajo->Importe=$request->importe;
            $OrdenTrabajo->Descuento=$request->descuento;
            $OrdenTrabajo->SubTotal=$request->subtotal;
            $OrdenTrabajo->Iva=$request->iva;
            $OrdenTrabajo->Total=$request->total;
            $OrdenTrabajo->Utilidad=$request->subtotal;
            $OrdenTrabajo->Status="ABIERTA";
            $OrdenTrabajo->Usuario=Auth::user()->user;
            $OrdenTrabajo->Periodo=$this->periodohoy;
            $OrdenTrabajo->save();
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "ORDENES DE TRABAJO";
            $BitacoraDocumento->Movimiento = $orden;
            $BitacoraDocumento->Aplicacion = "ALTA";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = "ABIERTA";
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $this->periodohoy;
            $BitacoraDocumento->save();
            if($request->numerofilastablaservicios > 0){
                //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
                foreach ($request->codigopartida as $key => $codigopartida){             
                    $OrdenTrabajoDetalle=new OrdenTrabajoDetalle;
                    $OrdenTrabajoDetalle->Orden = $orden;
                    $OrdenTrabajoDetalle->Cliente = $request->numeroclientefacturaa;
                    $OrdenTrabajoDetalle->Agente = $request->numeroagente;
                    $OrdenTrabajoDetalle->Fecha = Carbon::parse($request->fechapartida [$key])->toDateTimeString();
                    $OrdenTrabajoDetalle->Codigo = $codigopartida;
                    $OrdenTrabajoDetalle->Descripcion = $request->descripcionpartida [$key];
                    $OrdenTrabajoDetalle->Anotaciones = $request->anotacionespartida [$key];
                    $OrdenTrabajoDetalle->Unidad = $request->unidadpartidad [$key];
                    $OrdenTrabajoDetalle->Cantidad =  $request->cantidadpartida  [$key];
                    $OrdenTrabajoDetalle->Precio =  $request->preciopartida [$key];
                    $OrdenTrabajoDetalle->Importe = $request->importepartida [$key];
                    $OrdenTrabajoDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Descuento = $request->descuentopesospartida [$key];
                    $OrdenTrabajoDetalle->SubTotal = $request->subtotalpartida [$key];
                    $OrdenTrabajoDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Iva = $request->ivapesospartida [$key];
                    $OrdenTrabajoDetalle->Total = $request->totalpesospartida [$key];
                    $OrdenTrabajoDetalle->Costo = $request->costopartida [$key];
                    $OrdenTrabajoDetalle->CostoTotal = $request->costototalpartida [$key];
                    $OrdenTrabajoDetalle->Com = $request->comisionporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Comision = $request->comisionpesospartida [$key];
                    $OrdenTrabajoDetalle->Utilidad = $request->utilidadpartida [$key];
                    $OrdenTrabajoDetalle->Departamento = $request->departamentopartida [$key];
                    $OrdenTrabajoDetalle->Cargo = $request->cargopartida [$key];
                    $OrdenTrabajoDetalle->Traspaso = $request->traspasopartida [$key];
                    $OrdenTrabajoDetalle->Compra = $request->comprapartida [$key];
                    $OrdenTrabajoDetalle->Item = $request->itempartida [$key];
                    $OrdenTrabajoDetalle->Usuario = $request->usuariopartida [$key];
                    $OrdenTrabajoDetalle->Tecnico1 = $request->numerotecnicopartida1 [$key];
                    $OrdenTrabajoDetalle->Tecnico2 = $request->numerotecnicopartida2 [$key];
                    $OrdenTrabajoDetalle->Tecnico3 = $request->numerotecnicopartida3 [$key];
                    $OrdenTrabajoDetalle->Tecnico4 = $request->numerotecnicopartida4 [$key];
                    $OrdenTrabajoDetalle->Horas1 = $request->horaspartida1 [$key];
                    $OrdenTrabajoDetalle->Horas2 = $request->horaspartida2 [$key];
                    $OrdenTrabajoDetalle->Horas3 = $request->horaspartida3 [$key];
                    $OrdenTrabajoDetalle->Horas4 = $request->horaspartida4 [$key];
                    $OrdenTrabajoDetalle->Promocion = $request->promocionpartida [$key];
                    $OrdenTrabajoDetalle->Status = $request->statuspartida [$key];
                    $OrdenTrabajoDetalle->Almacen = $request->almacenpartida [$key];
                    $OrdenTrabajoDetalle->Cotizacion = $request->cotizacionpartida [$key];
                    $OrdenTrabajoDetalle->Partida = $request->partidapartida [$key];
                    $OrdenTrabajoDetalle->save();
                }
            } 
        }
    	return response()->json($OrdenTrabajo); 
    }
    //obtener orden de trabajo
    public function ordenes_trabajo_obtener_orden_trabajo(Request $request){
        $ordentrabajo = OrdenTrabajo::where('Orden', $request->ordenmodificar)->first();
        $cliente = Cliente::where('Numero', $ordentrabajo->Cliente)->first();
        $delcliente = Cliente::where('Numero', $ordentrabajo->DelCliente)->first();
        $agente = Agente::where('Numero', $ordentrabajo->Agente)->first();
        //tipo orden trabajo seleccionada
        $tipos_ordenes_trabajo = TipoOrdenTrabajo::where('STATUS', 'ALTA')->get();
        $selecttipoordentrabajo = "<option selected disabled hidden>Selecciona...</option>";
        foreach($tipos_ordenes_trabajo as $tot){
            if($tot->Nombre == $ordentrabajo->Tipo){
                $selecttipoordentrabajo = $selecttipoordentrabajo.'<option selected value='.$tot->Nombre.'>'.$tot->Nombre.'</option>';
            }else{
                $selecttipoordentrabajo = $selecttipoordentrabajo.'<option value='.$tot->Nombre.'>'.$tot->Nombre.'</option>';
            }    
        }
        //tipo unidad seleccionada
        $tipos_unidades= TipoUnidad::where('STATUS', 'ALTA')->get();
        $selecttipounidad = "<option selected disabled hidden>Selecciona...</option>";
        foreach($tipos_unidades as $tu){
            if($tu->Nombre == $ordentrabajo->Unidad){
                $selecttipounidad = $selecttipounidad.'<option selected value='.$tu->Nombre.'>'.$tu->Nombre.'</option>';
            }else{
                $selecttipounidad = $selecttipounidad.'<option value='.$tu->Nombre.'>'.$tu->Nombre.'</option>';
            }    
        }
        //modificacion permitida
        if($ordentrabajo->Status == 'ABIERTA'){
            //$readonly = ''; 
        }else{
            //$readonly = 'readonly="readonly"';
        }
        $filasdetallesordentrabajo = '';
        $contadorservicios = 0;
        $contadorfilas = 0;
        $item = 1;
        $tipo = "modificacion";
        //detalles orden trabajo
        $detallesordentrabajo = OrdenTrabajoDetalle::where('Orden', $request->ordenmodificar)->OrderBy('Partida', 'ASC')->get();
        $numerodetallesordentrabajo = OrdenTrabajoDetalle::where('Orden', $request->ordenmodificar)->count();
        if($numerodetallesordentrabajo > 0){
            foreach($detallesordentrabajo as $dot){
                $counttecnico1 = Tecnico::where('Numero', $dot->Tecnico1)->count();
                $counttecnico2 = Tecnico::where('Numero', $dot->Tecnico2)->count();
                $counttecnico3 = Tecnico::where('Numero', $dot->Tecnico3)->count();
                $counttecnico4 = Tecnico::where('Numero', $dot->Tecnico4)->count();
                if($counttecnico1 > 0){
                    $obtenertecnico1 = Tecnico::where('Numero', $dot->Tecnico1)->first();
                    $tecnico1 = $obtenertecnico1->Nombre;
                }else{
                    $tecnico1 = $dot->Tecnico1;
                }
                if($counttecnico2 > 0){
                    $obtenertecnico2 = Tecnico::where('Numero', $dot->Tecnico2)->first();
                    $tecnico2 = $obtenertecnico2->Nombre;
                }else{
                    $tecnico2 = $dot->Tecnico2;
                }
                if($counttecnico3 > 0){
                    $obtenertecnico3 = Tecnico::where('Numero', $dot->Tecnico3)->first();
                    $tecnico3 = $obtenertecnico3->Nombre;
                }else{
                    $tecnico3 = $dot->Tecnico3;
                }
                if($counttecnico4 > 0){
                    $obtenertecnico4 = Tecnico::where('Numero', $dot->Tecnico4)->first();
                    $tecnico4 = $obtenertecnico4->Nombre;
                }else{
                    $tecnico4 = $dot->Tecnico4;
                }
                if($dot->Departamento == 'SERVICIO' && $dot->Compra == ""){
                    $botonasignartecnicos = '<div class="btn bg-blue btn-xs" data-toggle="tooltip" title="Asignar Técnicos" onclick="asignaciontecnicos('.$contadorservicios.')">Asignar técnicos</div>';
                    $botoneliminarfila = '<div class="btn bg-red btn-xs" data-toggle="tooltip" title="Eliminar Fila" onclick="eliminarfila('.$contadorservicios.')">X</div> ';
                    if($ordentrabajo->Status == 'ABIERTA'){
                        $readonly = '';
                        $readonlyprecio = '';
                    }else{
                        $readonly = 'readonly="readonly"';
                        $readonlyprecio = 'readonly="readonly"';
                    }
                }else{
                    $botonasignartecnicos = '';
                    $botoneliminarfila = '';
                    if($ordentrabajo->Status == 'ABIERTA'){
                        $readonlyprecio = '';
                    }else{
                        $readonlyprecio = 'readonly="readonly"';
                    }
                    $readonly = 'readonly="readonly"';
                }
                $filasdetallesordentrabajo=$filasdetallesordentrabajo. 
                '<tr class="filasservicios" id="filaservicio'.$contadorservicios.'">'.
                    '<td class="tdmod"><div class="divorinputmodmd">'.
                    $botoneliminarfila.
                    $botonasignartecnicos.
                    '<input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly>'.
                    '</div></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control tipofila" name="tipofila[]" value="consultado" readonly><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dot->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$dot->Codigo.'</b></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($dot->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadpartidad" name="unidadpartidad[]" value="'.$dot->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dot->Unidad.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');" '.$readonlyprecio.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dot->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dot->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Utilidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs departamentopartida" name="departamentopartida[]" value="'.$dot->Departamento.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs cargopartida" name="cargopartida[]" value="'.$dot->Cargo.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="datetime-local" class="form-control divorinputmodxl fechapartida" name="fechapartida[]" value="'.Helpers::formatoinputdatetime($dot->Fecha).'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs traspasopartida" name="traspasopartida[]" value="'.$dot->Traspaso.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs comprapartida" name="comprapartida[]" value="'.$dot->Compra.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs usuariopartida" name="usuariopartida[]" value="'.$dot->Usuario.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl anotacionespartida" name="anotacionespartida[]" value="'.$dot->Anotaciones.'" '.$readonly.'  data-parsley-length="[1, 255]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs statuspartida" name="statuspartida[]" value="'.$dot->Status.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxs itempartidabd" name="itempartidabd[]" value="'.$dot->Item.'" readonly><input type="text" class="form-control divorinputmodxs itempartida" name="itempartida[]" value="'.$dot->Item.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida1" name="numerotecnicopartida1[]" value="'.$dot->Tecnico1.'" readonly><input type="text" class="form-control divorinputmodl tecnicopartida1" name="tecnicopartida1[]" value="'.$tecnico1.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida2" name="numerotecnicopartida2[]" value="'.$dot->Tecnico2.'" readonly><input type="text" class="form-control divorinputmodl tecnicopartida2" name="tecnicopartida2[]" value="'.$tecnico2.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida3" name="numerotecnicopartida3[]" value="'.$dot->Tecnico3.'" readonly><input type="text" class="form-control divorinputmodl tecnicopartida3" name="tecnicopartida3[]" value="'.$tecnico3.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida4" name="numerotecnicopartida4[]" value="'.$dot->Tecnico4.'" readonly><input type="text" class="form-control divorinputmodl tecnicopartida4" name="tecnicopartida4[]" value="'.$tecnico4.'" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida1" name="horaspartida1[]" value="'.Helpers::convertirvalorcorrecto($dot->Horas1).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida2" name="horaspartida2[]" value="'.Helpers::convertirvalorcorrecto($dot->Horas2).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida3" name="horaspartida3[]" value="'.Helpers::convertirvalorcorrecto($dot->Horas3).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida4" name="horaspartida4[]" value="'.Helpers::convertirvalorcorrecto($dot->Horas4).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs promocionpartida" name="promocionpartida[]" value="'.$dot->Promocion.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs partidapartida" name="partidapartida[]" value="'.$dot->Partida.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs almacenpartida" name="almacenpartida[]" value="'.$dot->Almacen.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs cotizacionpartida" name="cotizacionpartida[]" value="'.$dot->Cotizacion.'" readonly data-parsley-length="[1, 20]"></td>'.
                '</tr>';
                $contadorservicios++;
                $contadorfilas++;
                $item++;
            }
        }else{
            $filasdetallesordentrabajo = '';
        }  
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($ordentrabajo->Status != 'ABIERTA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($ordentrabajo->Status != 'ABIERTA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        } 
        $data = array(
            "ordentrabajo" => $ordentrabajo,
            "filasdetallesordentrabajo" => $filasdetallesordentrabajo,
            "numerodetallesordentrabajo" => $numerodetallesordentrabajo,
            "contadorservicios" => $contadorservicios,
            "contadorfilas" => $contadorfilas,
            "item" => $item,
            "modificacionpermitida" => $modificacionpermitida,
            "cliente" => $cliente,
            "delcliente" => $delcliente,
            "agente" => $agente,
            "selecttipoordentrabajo" => $selecttipoordentrabajo,
            "selecttipounidad" => $selecttipounidad,
            "fecha" => Helpers::formatoinputdatetime($ordentrabajo->Fecha),
            "fechaentrega" => Helpers::formatoinputdatetime($ordentrabajo->Entrega),
            "fecharecordatoriocliente" => Helpers::formatoinputdate($ordentrabajo->FechaRecordatorio),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($ordentrabajo->Fecha),
            "kilometros" => Helpers::convertirvalorcorrecto($ordentrabajo->Kilometros),
            "horasreales" => Helpers::convertirvalorcorrecto($ordentrabajo->HorasReales),
            "kmproximoservicio" => Helpers::convertirvalorcorrecto($ordentrabajo->KmProximoServicio),
            "importe" => Helpers::convertirvalorcorrecto($ordentrabajo->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($ordentrabajo->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($ordentrabajo->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($ordentrabajo->Iva),
            "total" => Helpers::convertirvalorcorrecto($ordentrabajo->Total)
        );
        return response()->json($data);
    }
    //modificacion
    public function ordenes_trabajo_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $orden = $request->folio.'-'.$request->serie;
        $OrdenTrabajo = OrdenTrabajo::where('Orden', $orden)->first();
        //modificar orden
        OrdenTrabajo::where('Orden', $orden)
        ->update([
            'Tipo'=>$request->tipoorden,
            'Unidad'=>$request->tipounidad,
            'Fecha'=>Carbon::parse($request->fecha)->toDateTimeString(),
            'Entrega'=>Carbon::parse($request->fechaentregapromesa)->toDateTimeString(),
            'Cliente'=>$request->numeroclientefacturaa,
            'DelCliente'=>$request->numeroclientedelcliente,
            'Agente'=>$request->numeroagente,
            'Caso'=>$request->caso,
            'TipoServicio'=>$request->tiposervicio,
            'Vin'=>$request->vin,
            'Motor'=>$request->motor,
            'Marca'=>$request->marca,
            'Modelo'=>$request->modelo,
            'Año'=>$request->ano,
            'Kilometros'=>$request->kilometros,
            'Placas'=>$request->placas,
            'Economico'=>$request->economico,
            'Color'=>$request->color,
            'KmProximoServicio'=>$request->kmproxservicio,
            'FechaRecordatorio'=>Carbon::parse($request->fecharecordatoriocliente)->toDateTimeString(),
            'Reclamo'=>$request->reclamo,
            'Pedido'=>$request->ordencliente,
            'Campaña'=>$request->campana,
            'Promocion'=>$request->promocion,
            'Bahia'=>$request->bahia,
            'HorasReales'=>$request->horasreales,
            'Rodar'=>$request->rodar,
            'Plazo'=>$request->plazodias,
            'Falla'=>$request->falla,
            'ObsOrden'=>$request->observaciones,
            'Causa'=>$request->causa,
            'Correccion'=>$request->correccion,
            'Importe'=>$request->importe,
            'Descuento'=>$request->descuento,
            'SubTotal'=>$request->subtotal,
            'Iva'=>$request->iva,
            'Total'=>$request->total,
            'Utilidad'=>$request->subtotal
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE TRABAJO";
        $BitacoraDocumento->Movimiento = $orden;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $OrdenTrabajo->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        $numerodetallesordentrabajomodificada = OrdenTrabajoDetalle::where('Orden', $orden)->count();
        if($request->numerofilastablaservicios == 0){
            if($numerodetallesordentrabajomodificada > 0){
                OrdenTrabajoDetalle::where('Orden', $orden)->forceDelete();
            }
        }else{
            //eliminar detalles que se quitaron en la modificacion
            $DetallesOrdenTrabajoAntesDeModificacion = OrdenTrabajoDetalle::where('Orden', $orden)->get();
            $array_detalles_antes_de_modificar = [];
            foreach($DetallesOrdenTrabajoAntesDeModificacion as $dotadm){
                array_push($array_detalles_antes_de_modificar, $dotadm->Orden.'#'.$dotadm->Codigo.'#'.$dotadm->Item);
            }
            $array_detalles_despues_de_modificar = [];
            foreach ($request->codigopartida as $key => $codigopartida){   
                if($request->tipofila [$key] == 'consultado'){
                    array_push($array_detalles_despues_de_modificar, $orden.'#'.$codigopartida.'#'.$request->itempartidabd [$key]);
                }  
            }
            $diferencias = array_diff($array_detalles_antes_de_modificar, $array_detalles_despues_de_modificar);
            foreach($diferencias as $d){
                $explode_d = explode("#",$d);
                $diff = $explode_d[0];
                $EliminaDetalle = OrdenTrabajoDetalle::where('Orden', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
            }
            //modificar partidas que no se eliminaron
            foreach ($request->codigopartida as $key => $codigopartida){   
                if($request->tipofila [$key] == 'consultado'){
                    OrdenTrabajoDetalle::where('Orden', $orden)
                    ->where('Item', $request->itempartidabd [$key])
                    ->update([
                        'Orden' => $orden,
                        'Cliente' => $request->numeroclientefacturaa,
                        'Agente' => $request->numeroagente,
                        'Fecha' => Carbon::parse($request->fechapartida [$key])->toDateTimeString(),
                        'Codigo' => $codigopartida,
                        'Descripcion' => $request->descripcionpartida [$key],
                        'Anotaciones' => $request->anotacionespartida [$key],
                        'Unidad' => $request->unidadpartidad [$key],
                        'Cantidad' =>  $request->cantidadpartida  [$key],
                        'Precio' =>  $request->preciopartida [$key],
                        'Importe' => $request->importepartida [$key],
                        'Dcto' => $request->descuentoporcentajepartida [$key],
                        'Descuento' => $request->descuentopesospartida [$key],
                        'SubTotal' => $request->subtotalpartida [$key],
                        'Impuesto' => $request->ivaporcentajepartida [$key],
                        'Iva' => $request->ivapesospartida [$key],
                        'Total' => $request->totalpesospartida [$key],
                        'Costo' => $request->costopartida [$key],
                        'CostoTotal' => $request->costototalpartida [$key],
                        'Com' => $request->comisionporcentajepartida [$key],
                        'Comision' => $request->comisionpesospartida [$key],
                        'Utilidad' => $request->utilidadpartida [$key],
                        'Departamento' => $request->departamentopartida [$key],
                        'Cargo' => $request->cargopartida [$key],
                        'Traspaso' => $request->traspasopartida [$key],
                        'Compra' => $request->comprapartida [$key],
                        'Item' => $request->itempartida [$key],
                        'Usuario' => $request->usuariopartida [$key],
                        'Tecnico1' => $request->numerotecnicopartida1 [$key],
                        'Tecnico2' => $request->numerotecnicopartida2 [$key],
                        'Tecnico3' => $request->numerotecnicopartida3 [$key],
                        'Tecnico4' => $request->numerotecnicopartida4 [$key],
                        'Horas1' => $request->horaspartida1 [$key],
                        'Horas2' => $request->horaspartida2 [$key],
                        'Horas3' => $request->horaspartida3 [$key],
                        'Horas4' => $request->horaspartida4 [$key],
                        'Promocion' => $request->promocionpartida [$key],
                        'Status' => $request->statuspartida [$key],
                        'Almacen' => $request->almacenpartida [$key],
                        'Cotizacion' => $request->cotizacionpartida [$key],
                        'Partida' => $request->partidapartida [$key]
                    ]);
                }elseif($request->tipofila [$key] == 'agregado'){
                    //agregar todas las partidas agregadas en la modificación
                    $OrdenTrabajoDetalle=new OrdenTrabajoDetalle;
                    $OrdenTrabajoDetalle->Orden = $orden;
                    $OrdenTrabajoDetalle->Cliente = $request->numeroclientefacturaa;
                    $OrdenTrabajoDetalle->Agente = $request->numeroagente;
                    $OrdenTrabajoDetalle->Fecha = Carbon::parse($request->fechapartida [$key])->toDateTimeString();
                    $OrdenTrabajoDetalle->Codigo = $codigopartida;
                    $OrdenTrabajoDetalle->Descripcion = $request->descripcionpartida [$key];
                    $OrdenTrabajoDetalle->Anotaciones = $request->anotacionespartida [$key];
                    $OrdenTrabajoDetalle->Unidad = $request->unidadpartidad [$key];
                    $OrdenTrabajoDetalle->Cantidad =  $request->cantidadpartida  [$key];
                    $OrdenTrabajoDetalle->Precio =  $request->preciopartida [$key];
                    $OrdenTrabajoDetalle->Importe = $request->importepartida [$key];
                    $OrdenTrabajoDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Descuento = $request->descuentopesospartida [$key];
                    $OrdenTrabajoDetalle->SubTotal = $request->subtotalpartida [$key];
                    $OrdenTrabajoDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Iva = $request->ivapesospartida [$key];
                    $OrdenTrabajoDetalle->Total = $request->totalpesospartida [$key];
                    $OrdenTrabajoDetalle->Costo = $request->costopartida [$key];
                    $OrdenTrabajoDetalle->CostoTotal = $request->costototalpartida [$key];
                    $OrdenTrabajoDetalle->Com = $request->comisionporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Comision = $request->comisionpesospartida [$key];
                    $OrdenTrabajoDetalle->Utilidad = $request->utilidadpartida [$key];
                    $OrdenTrabajoDetalle->Departamento = $request->departamentopartida [$key];
                    $OrdenTrabajoDetalle->Cargo = $request->cargopartida [$key];
                    $OrdenTrabajoDetalle->Traspaso = $request->traspasopartida [$key];
                    $OrdenTrabajoDetalle->Compra = $request->comprapartida [$key];
                    $OrdenTrabajoDetalle->Item = $request->itempartida [$key];
                    $OrdenTrabajoDetalle->Usuario = $request->usuariopartida [$key];
                    $OrdenTrabajoDetalle->Tecnico1 = $request->numerotecnicopartida1 [$key];
                    $OrdenTrabajoDetalle->Tecnico2 = $request->numerotecnicopartida2 [$key];
                    $OrdenTrabajoDetalle->Tecnico3 = $request->numerotecnicopartida3 [$key];
                    $OrdenTrabajoDetalle->Tecnico4 = $request->numerotecnicopartida4 [$key];
                    $OrdenTrabajoDetalle->Horas1 = $request->horaspartida1 [$key];
                    $OrdenTrabajoDetalle->Horas2 = $request->horaspartida2 [$key];
                    $OrdenTrabajoDetalle->Horas3 = $request->horaspartida3 [$key];
                    $OrdenTrabajoDetalle->Horas4 = $request->horaspartida4 [$key];
                    $OrdenTrabajoDetalle->Promocion = $request->promocionpartida [$key];
                    $OrdenTrabajoDetalle->Status = $request->statuspartida [$key];
                    $OrdenTrabajoDetalle->Almacen = $request->almacenpartida [$key];
                    $OrdenTrabajoDetalle->Cotizacion = $request->cotizacionpartida [$key];
                    $OrdenTrabajoDetalle->Partida = $request->partidapartida [$key];
                    $OrdenTrabajoDetalle->save();                    
                }          
            }
        }
    	return response()->json($OrdenTrabajo);         
    }

    //verificar el registro que se dara de baja
    public function ordenes_trabajo_verificar_uso_en_modulos(Request $request){
        $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->ordendesactivar)->first();
        $resultado = OrdenTrabajoDetalle::where('Orden', $request->ordendesactivar)->count();
        $condetalles = false;
        if($resultado > 0){
            $condetalles = true;
        }
        $resultadofechas = Helpers::compararanoymesfechas($OrdenTrabajo->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'condetalles' => $condetalles,
            'Status' => $OrdenTrabajo->Status
        );
        return response()->json($data);
    }

    //dar de baja registro
    public function ordenes_trabajo_alta_o_baja(Request $request){
        $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->ordendesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        OrdenTrabajo::where('Orden', $request->ordendesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Impuesto' => '0.000000',
                    'Importe' => '0.000000',
                    'Descuento' => '0.000000',
                    'SubTotal' => '0.000000',
                    'Iva' => '0.000000',
                    'Total' => '0.000000',
                    'Facturado' => '0.000000',
                    'Costo' => '0.000000',
                    'Comision' => '0.000000',
                    'Utilidad' => '0.000000',
                    'HorasReales' => '0.000000'
                ]);
        $detalles = OrdenTrabajoDetalle::where('Orden', $request->ordendesactivar)->get();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE TRABAJO";
        $BitacoraDocumento->Movimiento = $request->ordendesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $OrdenTrabajo->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($OrdenTrabajo);
    }

    //verificar status del registro
    public function ordenes_trabajo_verificar_status_orden(Request $request){
        $ordentrabajo = OrdenTrabajo::where('Orden', $request->ordenterminar)->first();
        $cliente = Cliente::where('Numero', $ordentrabajo->Cliente)->first();
        $data = array(
            'ordentrabajo' => $ordentrabajo,
            'cliente' => $cliente,
            "fecha" => Helpers::formatoinputdatetime($ordentrabajo->Fecha)
        );
        return response()->json($data); 
    }

    //terminar orden de trabajo
    public function ordenes_trabajo_terminar_orden(Request $request){
        $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->ordenterminar)->first();
        OrdenTrabajo::where('Orden', $request->ordenterminar)
            ->update([
                'Status' => 'CERRADA'
            ]);
        /*
        $OrdenTrabajo->Status = 'CERRADA';
        $OrdenTrabajo->save();
        */
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE TRABAJO";
        $BitacoraDocumento->Movimiento = $request->ordenterminar;
        $BitacoraDocumento->Aplicacion = "CERRAR";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $OrdenTrabajo->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($OrdenTrabajo);  
    }

    //verificar status del registro
    public function ordenes_trabajo_verificar_abrir_nuevamente_orden(Request $request){
        $ordentrabajo = OrdenTrabajo::where('Orden', $request->ordenabrir)->first();
        return response()->json($ordentrabajo); 
    }

    //abrir nuevamente orden de trabajo
    public function ordenes_trabajo_abrir_nuevamente_orden(Request $request){
        $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->ordenabrir)->first();
        OrdenTrabajo::where('Orden', $request->ordenabrir)
            ->update([
                'Status' => 'ABIERTA'
            ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE TRABAJO";
        $BitacoraDocumento->Movimiento = $request->ordenabrir;
        $BitacoraDocumento->Aplicacion = "ABRIRNUEVAMENTE";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $OrdenTrabajo->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($OrdenTrabajo);  
    }

    //buscar folio on key up
    public function ordenes_trabajo_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = VistaOrdenTrabajo::orderBy('Folio', 'ASC')->get();
            return DataTables::of($data)
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->make(true);
        } 
    }
    //generacion de formato en PDF
    public function ordenes_trabajo_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        //primero eliminar todos los archivos zip
        Helpers::eliminararchivoszipgenerados();
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
        $arrayfilespdf = array();
        foreach ($ordenestrabajo as $ot){
            $data=array();
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
                    "datadetalle" => $datadetalle,
                    "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            //dd($data);
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.ordenestrabajo.formato_pdf_ordenestrabajo', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$ot->Orden.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($ordenestrabajo as $ots){
            $ArchivoPDF = "PDF".$ots->Orden.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
            array_push($arrayfilespdf,$ArchivoPDF);
        }
        $pdfMerger->merge(); //unirlos
        if($request->descargar_xml == 0){
            $pdfMerger->save("OrdenesTrabajo.pdf", "browser");//mostrarlos en el navegador
        }else{
            //carpeta donde se guardara el archivo zip
            $public_dir=public_path();
            // Zip File Name
            $zipFileName = 'DocumentosPDF.zip';
            // Crear Objeto ZipArchive
            $zip = new ZipArchive;
            if ($zip->open($public_dir . '/xml_descargados/' . $zipFileName, ZipArchive::CREATE) === TRUE) {
                // Agregar archivos que se comprimiran
                foreach($arrayfilespdf as $afp) {
                    $zip->addFile(Storage::disk('local3')->getAdapter()->applyPathPrefix($afp),$afp);
                }     
                //terminar proceso   
                $zip->close();
            }
            // Set Encabezados para descargar
            $headers = array(
                'Content-Type' => 'application/octet-stream',
            );
            $filetopath=$public_dir.'/xml_descargados/'.$zipFileName;
            // Create Download Response
            if(file_exists($filetopath)){
                return response()->download($filetopath,$zipFileName,$headers);
            }
        }
    }

    //generacion de formato en PDF
    public function ordenes_trabajo_generar_pdfs_indiv($documento){
        $ordenestrabajo = OrdenTrabajo::where('Orden', $documento)->get(); 
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
                    "datadetalle" => $datadetalle,
                    "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ordenestrabajo.formato_pdf_ordenestrabajo', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //obtener datos para enviar email
    public function ordenes_trabajo_obtener_datos_envio_email(Request $request){
        $ordentrabajo= OrdenTrabajo::where('Orden', $request->documento)->first();
        $cliente = Cliente::where('Numero', $ordentrabajo->Cliente)->first();
        $email2cc = '';
        $email3cc = '';
        if($cliente->Email2 != '' || $cliente->Email2 != null){
            $email2cc = $cliente->Email2;
        }
        if($cliente->Email3 != '' || $cliente->Email3 != null){
            $email3cc = $cliente->Email3;
        }
        $data = array(
            'ordentrabajo' => $ordentrabajo,
            'cliente' => $cliente,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $cliente->Email1,
            'email2cc' => $email2cc,
            'email3cc' => $email3cc,
            'correodefault1enviodocumentos' => $this->correodefault1enviodocumentos,
            'correodefault2enviodocumentos' => $this->correodefault2enviodocumentos
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function ordenes_trabajo_enviar_pdfs_email(Request $request){
        $ordenestrabajo = OrdenTrabajo::where('Orden', $request->emaildocumento)->get(); 
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
                    "datadetalle" => $datadetalle,
                    "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ordenestrabajo.formato_pdf_ordenestrabajo', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            $datosdocumento = OrdenTrabajo::where('Orden', $request->emaildocumento)->first();
            //enviar correo electrónico	
            $nombre = 'Receptor envio de correos';
            $receptor = $request->emailpara;
            $arraycc = array();
            array_push($arraycc, $request->emailpara);
            if($request->email2cc != ""){
                array_push($arraycc, $request->email2cc);
            }
            if($request->email3cc != ""){
                array_push($arraycc, $request->email3cc);
            }
            if($request->correosconcopia != null){
                foreach($request->correosconcopia as $cc){
                    if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                        array_push($arraycc, $cc);
                    }
                }
            }
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailmensaje;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $correos, $asunto, $pdf, $emaildocumento) {
                $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                        ->cc($arraycc)
                        ->subject($asunto)
                        ->attachData($pdf->output(), "OrdenTrabajoNo".$emaildocumento.".pdf");
            });
        } catch(\Exception $e) {
            $receptor = 'osbaldo.anzaldo@utpcamiones.com.mx';
            $correos = ['osbaldo.anzaldo@utpcamiones.com.mx'];
            $msj = 'Error al enviar correo';
            Mail::send('correos.errorenvio.error', compact('e','msj'), function($message) use ($receptor, $correos) {
                $message->to($receptor)
                        ->cc($correos)
                        ->subject('Error al enviar correo nuevo usuario');
            });
        }
    }

    //funcion exportar excel
    public function ordenes_trabajo_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('OrdenesDeTrabajo', Auth::user()->id);
        return Excel::download(new OrdenesDeTrabajoExport($configuraciones_tabla['campos_consulta'],$request->periodo), "ordenesdetrabajo-".$request->periodo.".xlsx");   
    }  
    //configuracion tabla  
    public function ordenes_trabajo_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('OrdenesDeTrabajo', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'OrdenesDeTrabajo')->where('IdUsuario', Auth::user()->id)
            ->update([
                'campos_activados' => $request->string_datos_tabla_true,
                'campos_desactivados' => $string_datos_tabla_false,
                'columnas_ordenadas' => $request->string_datos_ordenamiento_columnas,
                'usuario' => Auth::user()->user,
                'primerordenamiento' => $request->selectorderby1,
                'formaprimerordenamiento' => $request->deorderby1,
                'segundoordenamiento' => $request->selectorderby2,
                'formasegundoordenamiento' => $request->deorderby2,
                'tercerordenamiento' => $request->selectorderby3,
                'formatercerordenamiento' => $request->deorderby3,
                'campos_busquedas' => substr($selectmultiple, 1),
            ]);
        }else{
            $Configuracion_Tabla=new Configuracion_Tabla;
            $Configuracion_Tabla->tabla='OrdenesDeTrabajo';
            $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
            $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
            $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
            $Configuracion_Tabla->ordenar = 0;
            $Configuracion_Tabla->usuario = Auth::user()->user;
            $Configuracion_Tabla->campos_busquedas = substr($selectmultiple, 1);
            $Configuracion_Tabla->primerordenamiento = $request->selectorderby1;
            $Configuracion_Tabla->formaprimerordenamiento = $request->deorderby1;
            $Configuracion_Tabla->segundoordenamiento =  $request->selectorderby2;
            $Configuracion_Tabla->formasegundoordenamiento =  $request->deorderby2;
            $Configuracion_Tabla->tercerordenamiento = $request->selectorderby3;
            $Configuracion_Tabla->formatercerordenamiento = $request->deorderby3;
            $Configuracion_Tabla->IdUsuario = Auth::user()->id;
            $Configuracion_Tabla->save();
        }
        return redirect()->route('ordenes_trabajo');
    }

}
