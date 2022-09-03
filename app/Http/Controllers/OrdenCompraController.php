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
use App\Exports\PlantillasOrdenesCompraExport;
use App\Imports\OrdenesCompraImport;
use App\Exports\OrdenesDeCompraExport;
use App\Exports\OrdenCompraDetallesExport;
use App\OrdenCompra;
use App\OrdenCompraDetalle;
use App\TipoOrdenCompra;
use App\Serie;
use App\Proveedor;
use App\Almacen;
use App\BitacoraDocumento;
use App\Compra;
use App\CompraDetalle;
use App\Producto;
use App\Marca;
use App\Configuracion_Tabla;
use App\VistaOrdenCompra;
use App\VistaObtenerExistenciaProducto;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Linea;
use App\Firma_Rel_Documento;
use App\User_Rel_Almacen;
use Config;
use Mail;
use App\Existencia;
use Storage;
use ZipArchive;
use File;


class OrdenCompraController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function ordenes_compra(){
        $serieusuario = 'A';
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('OrdenesDeCompra', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('ordenes_compra_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('ordenes_compra_exportar_excel');
        $urlgenerarformatoexceldetalles = route('orden_compra_detalles_exportar_excel');
        $rutacreardocumento = route('ordenes_compra_generar_pdfs');
        $urlgenerarplantilla = route('ordenes_compra_generar_plantilla');
        return view('registros.ordenescompra.ordenescompra', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','urlgenerarformatoexceldetalles','rutacreardocumento','urlgenerarplantilla'));
    }
    //obtener todos los registros
    public function ordenes_compra_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('OrdenesDeCompra', Auth::user()->id);
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaOrdenCompra::select($configuraciones_tabla['campos_consulta'])->where('Periodo', $periodo);
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
                    ->addColumn('operaciones', function($data) use ($tipousuariologueado){
                        /*$operaciones =  '<a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Orden .'\')" >'.
                                            '<i class="material-icons" data-toggle="tooltip" data-placement="top" data-original-title="CAMBIOS">mode_edit</i>'.
                                        '</a>'.
                                        '<a href="javascript:void(0);" onclick="desactivar(\''.$data->Orden .'\')" >'.
                                            '<i class="material-icons" data-toggle="tooltip" data-placement="top" data-original-title="BAJAS">cancel</i>'.
                                        '</a>'.
                                        '<a href="javascript:void(0);" onclick="autorizarordencompra(\''.$data->Orden .'\')" >'.
                                            '<i class="material-icons" data-toggle="tooltip" data-placement="top" data-original-title="AUTORIZAR ORDEN">assignment_turned_in</i>'.
                                        '</a>'.
                                        '<a href="javascript:void(0);" onclick="quitarautorizacionordencompra(\''.$data->Orden .'\')" >'.
                                            '<i class="material-icons" data-toggle="tooltip" data-placement="top" data-original-title="QUITAR AUTORIZACION">assignment_returned</i>'.
                                        '</a>'.
                                        '<a href="'.route('ordenes_compra_generar_pdfs_indiv',$data->Orden).'" target="_blank">'.
                                            '<i class="material-icons" data-toggle="tooltip" data-placement="top" data-original-title="VER DOCUMENTO PDF">picture_as_pdf</i>'.
                                        '</a>'.
                                        '<a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Orden .'\')">'.
                                            '<i class="material-icons" data-toggle="tooltip" data-placement="top" data-original-title="ENVIAR DOCUMENTO POR CORREO">mail_outline</i>'.
                                        '</a>'.
                                        '<a href="javascript:void(0);" onclick="generardocumentoeniframe(\''.$data->Orden .'\')">'.
                                            '<i class="material-icons" data-toggle="tooltip" data-placement="top" data-original-title="IMPRIMIR DOCUMENTO PDF">print</i>'.
                                        '</a>';*/
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Orden .'\')">Cambios</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="autorizarordencompra(\''.$data->Orden .'\')">Autorizar</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="quitarautorizacionordencompra(\''.$data->Orden .'\')">Quitar Autorización</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar(\''.$data->Orden .'\')">Bajas</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="'.route('ordenes_compra_generar_pdfs_indiv',$data->Orden).'" target="_blank">Ver Documento PDF</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Orden .'\')">Enviar Documento por Correo</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="generardocumentoeniframe(\''.$data->Orden .'\')">Imprimir Documento PDF</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                    //->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    //->addColumn('Iva', function($data){ return $data->Iva; })
                    //->addColumn('Total', function($data){ return $data->Total; })
                    //->addColumn('Descuento', function($data){ return $data->Descuento; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //descargar plantilla
    public function ordenes_compra_generar_plantilla(){
        return Excel::download(new PlantillasOrdenesCompraExport(), "plantillaordenescompra.xlsx");
    }
    //cargar partidas excel
    public function ordenes_compra_cargar_partidas_excel(Request $request){
        $arrayexcel =  Excel::toArray(new OrdenesCompraImport, request()->file('partidasexcel'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $filasdetallesordencompra = '';
        $contadorproductos = $request->contadorproductos;
        $contadorfilas = $request->contadorfilas;
        $tipooperacion = 'alta';
        $arraycodigosyaagregados = $porciones = explode(",", $request->arraycodigospartidas);
        foreach($partidasexcel as $partida){
            if($rowexcel > 0){
                if (in_array(strtoupper($partida[0]), $arraycodigosyaagregados)) {

                }else{
                    $codigoabuscar = $partida[0];
                    $cantidadpartida = $partida[1];
                    switch ($request->tipoalta) {
                        case "GASTOS":
                            $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', ''.$codigoabuscar.'')->where('TipoProd', 'GASTOS')->count();
                            break;
                        case "TOT":
                            $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', ''.$codigoabuscar.'')->where('TipoProd', 'TOT')->count();
                            break;
                        default:
                            $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', ''.$codigoabuscar.'')
                            ->where(
                                function($query) {
                                return $query
                                        ->where('TipoProd', 'REFACCION')
                                        ->orWhereNull('TipoProd');
                            })->count();
                    }
                    if($contarproductos > 0){
                        switch ($request->tipoalta) {
                            case "GASTOS":
                                $producto = VistaObtenerExistenciaProducto::where('Codigo', ''.$codigoabuscar.'')->where('TipoProd', 'GASTOS')->first();
                                break;
                            case "TOT":
                                $producto = VistaObtenerExistenciaProducto::where('Codigo', ''.$codigoabuscar.'')->where('TipoProd', 'TOT')->first();
                                break;
                            default:
                                $producto = VistaObtenerExistenciaProducto::where('Codigo', ''.$codigoabuscar.'')
                                ->where(
                                    function($query) {
                                    return $query
                                            ->where('TipoProd', 'REFACCION')
                                            ->orWhereNull('TipoProd');
                                })->first();
                        }
                        if(Helpers::convertirvalorcorrecto($cantidadpartida) == 0){
                            $cantidad = 1;
                        }else{
                            $cantidad = $cantidadpartida;
                        }
                        $preciopartida = $producto->Costo;
                        //importe de la partida
                        $importepartida =  $cantidad*$preciopartida;
                        //subtotal de la partida
                        $subtotalpartida =  $importepartida-0;
                        //iva en pesos de la partida
                        $multiplicacionivapesospartida = $subtotalpartida*$producto->Impuesto;
                        $ivapesospartida = $multiplicacionivapesospartida/100;
                        //total en pesos de la partida
                        $totalpesospartida = $subtotalpartida+$ivapesospartida;
                        $tipo = "alta";
                        $filasdetallesordencompra= $filasdetallesordencompra.
                        '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                            '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosproductos('.$contadorproductos.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'.$producto->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$producto->Codigo.'</b></td>'.
                            '<td class="tdmod"><textarea rows="1" class="form-control inputnextdet nombreproductopartida" name="nombreproductopartida[]" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off" style="font-size:10px;">'.htmlspecialchars($producto->Producto, ENT_QUOTES).'</textarea></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'.$producto->Unidad.'" readonly data-parsley-length="[1, 5]">'.$producto->Unidad.'</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida" name="porsurtirpartida[]" id="porsurtirpartida[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" id="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" id="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($preciopartida).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" id="importepartida[]" value="'.Helpers::convertirvalorcorrecto($importepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" id="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" id="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" id="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" id="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" id="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($ivapesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" id="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($totalpesospartida).'"data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '</tr>';
                        array_push($arraycodigosyaagregados, $producto->Codigo);
                        $contadorproductos++;
                        $contadorfilas++;
                    }
                }
            }
            $rowexcel++;
        }
        $data = array(
            "filasdetallesordencompra" => $filasdetallesordencompra,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
        );
        return response()->json($data);
    }
    //obtener series documento
    public function ordenes_compra_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'OrdenesDeCompra')->where('Usuario', Auth::user()->user)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarseriedocumento(\''.$data->Serie.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener series disponibles del documentos enviado
    public function generar_documentos_obtener_series_disponibles_documentos(Request $request){
        switch ($request->documento) {
            case 'OrdenCompra':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\OrdenCompra');
                break;
            case 'Factura':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\Factura');
                break;
            case 'Compra':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\Compra');
                break;
            case 'ContraRecibo':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\ContraRecibo');
                break;
            case 'CotizacionProducto':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\CotizacionProducto');
                break;
            case 'CotizacionServicio':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\CotizacionServicio');
                break;
            case 'Remision':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\Remision');
                break;
            case 'Produccion':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\Produccion');
                break;
            case 'Requisicion':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\Requisicion');
                break;
            case 'Traspaso':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\Traspaso');
                break;
            case 'OrdenTrabajo':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\OrdenTrabajo');
                break;
            case 'CuentaXPagar':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\CuentaXPagar');
                break;
            case 'CuentaXCobrar':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\CuentaXCobrar');
                break;
            case 'NotaCliente':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\NotaCliente');
                break;
            case 'NotaProveedor':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\NotaProveedor');
                break;
            case 'CartaPorte':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\CartaPorte');
                break;
            case 'Asignacion_Herramienta':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\Asignacion_Herramienta');
                break;
            case 'AjusteInventario':
                $select_series_disponibles = Helpers::armarselectseriesdisponibles('App\AjusteInventario');
                break;
        }
        $fechahoyinputdate = Helpers::fechaexactaaccioninputdate();
        $data = array(
            'fechahoyinputdate' => $fechahoyinputdate,
            'select_series_disponibles' => $select_series_disponibles
        );
        return response()->json($data);
    }

    //obtener ultimo folio de la serie seleccionada
    public function ordenes_compra_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\OrdenCompra',$request->Serie);
        return response()->json($folio);
    }
    //obtener el ultimo folio de la tabla
    public function ordenes_compra_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\OrdenCompra',$request->serie);
        return response()->json($folio);
    }
    //obtener fecha date time actual
    public function ordenes_compra_obtener_fecha_actual_datetimelocal(Request $request){
        $fechas = Helpers::fecha_exacta_accion_datetimelocal();
        return response()->json($fechas);
    }
    //obtener tipos ordenes de compra
    public function ordenes_compra_obtener_tipos_ordenes_compra(Request $request){
        $almacen = $request->almacen;
        switch ($request->tipoalta) {
            case "GASTOS":
                $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', 'GASTOS')->orWhere('Nombre', 'CAJA CHICA')->get();
                break;
            case "CAJA CHICA":
                if($almacen > 0){
                    $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', '<>', 'GASTOS')->Where('Nombre', '<>', 'TOT')->get();
                }else{
                    $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', 'GASTOS')->orWhere('Nombre', 'CAJA CHICA')->get();
                }
                break;
            case "TOT":
                $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', 'TOT')->get();
                break;
            default:
                $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', '<>', 'GASTOS')->Where('Nombre', '<>', 'TOT')->get();

        }
        $select_tipos_ordenes_compra = "<option disabled hidden>Selecciona...</option>";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }
    //obtener proveedores
    public function ordenes_compra_obtener_proveedores(Request $request){
        if($request->ajax()){
            $data = Proveedor::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarproveedor('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener almacenes
    public function ordenes_compra_obtener_almacenes(Request $request){
        if($request->ajax()){
            $contaralmacenesasignadosausuario = User_Rel_Almacen::where('user_id', Auth::user()->id)->count();
            if($contaralmacenesasignadosausuario > 0){
                $data = DB::table('user_rel_almacenes as ura')
                ->join('Almacenes as a', 'ura.almacen_id', '=', 'a.Numero')
                ->select('ura.id', 'a.Numero', 'a.Nombre')
                ->where('a.Status', 'ALTA')
                ->where('ura.user_id', Auth::user()->id)
                ->orderby('a.Numero', 'ASC')
                ->get();
            }else{
                $data = Almacen::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
            }
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaralmacen('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener ordenes trabajo
    public function ordenes_compra_obtener_ordenes_trabajo(Request $request){
        if($request->ajax()){
            $data = DB::table('Ordenes de Trabajo as ot')
                        ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                        ->select('ot.Orden as Orden', 'ot.Fecha as Fecha', 'c.Nombre as Cliente', 'ot.Tipo as Tipo', 'ot.Unidad as Unidad', 'ot.Status AS StatusOrden')
                        ->where('ot.Status', 'ABIERTA')
                        ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarordentrabajo(\''.$data->Orden.'\',\''.Helpers::formatoinputdatetime($data->Fecha).'\',\''.$data->Cliente.'\',\''.$data->Tipo.'\',\''.$data->Unidad.'\',\''.$data->StatusOrden.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener orden trabajo por folio
    public function ordenes_compra_obtener_orden_trabajo_por_folio(Request $request){
        $orden = '';
        $existeorden = DB::table('Ordenes de Trabajo as ot')
                            ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                            ->select('ot.Orden as Orden', 'ot.Fecha as Fecha', 'c.Nombre as Cliente', 'ot.Tipo as Tipo', 'ot.Unidad as Unidad', 'ot.Status AS StatusOrden')
                            ->where('ot.Status', 'ABIERTA')
                            ->where('ot.Orden', $request->ordentrabajo)
                            ->count();
        if($existeorden > 0){
            $orden = DB::table('Ordenes de Trabajo as ot')
                        ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                        ->select('ot.Orden as Orden', 'ot.Fecha as Fecha', 'c.Nombre as Cliente', 'ot.Tipo as Tipo', 'ot.Unidad as Unidad', 'ot.Status AS StatusOrden')
                        ->where('ot.Status', 'ABIERTA')
                        ->where('ot.Orden', $request->ordentrabajo)
                        ->get();
                        //dd($orden[0]);
            $orden = $orden[0]->Orden;
        }
        $data = array(
            'orden' => $orden
        );
        return response()->json($data);
    }
    //obtener productos
    public function ordenes_compra_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $tipooperacion = $request->tipooperacion;
            $numeroalmacen = $request->numeroalmacen;
            switch ($request->tipoalta) {
                case "GASTOS":
                    $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')->where('TipoProd', 'GASTOS');
                    break;
                case "TOT":
                    $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')->where('TipoProd', 'TOT');
                    break;
                default:
                    $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')
                    ->where(
                        function($query) {
                        return $query
                                ->where('TipoProd', 'REFACCION')
                                ->orWhereNull('TipoProd');
                    });
                    //->where('TipoProd', '<>', 'GASTOS')->Where('TipoProd', '<>', 'TOT');
            }
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion, $numeroalmacen){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.$tipooperacion.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Existencias', function($data){
                        return Helpers::convertirvalorcorrecto($data->Existencias);
                    })
                    ->addColumn('Costo', function($data){
                        return Helpers::convertirvalorcorrecto($data->Costo);
                    })
                    ->addColumn('SubTotal', function($data){
                        return Helpers::convertirvalorcorrecto($data->SubTotal);
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener producto por codigo
    public function ordenes_compra_obtener_producto_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        switch ($request->tipoalta) {
            case "GASTOS":
                $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', 'GASTOS')->count();
                break;
            case "TOT":
                $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', 'TOT')->count();
                break;
            default:
                $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)
                ->where(
                    function($query) {
                    return $query
                            ->where('TipoProd', 'REFACCION')
                            ->orWhereNull('TipoProd');
                })->count();
        }
        if($contarproductos > 0){
            switch ($request->tipoalta) {
                case "GASTOS":
                    $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', 'GASTOS')->first();
                    break;
                case "TOT":
                    $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', 'TOT')->first();
                    break;
                default:
                    $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)
                    ->where(
                        function($query) {
                        return $query
                                ->where('TipoProd', 'REFACCION')
                                ->orWhereNull('TipoProd');
                    })->first();
            }

            $data = array(
                'Codigo' => $producto->Codigo,
                'Producto' => htmlspecialchars($producto->Producto, ENT_QUOTES),
                'Unidad' => $producto->Unidad,
                'Costo' => Helpers::convertirvalorcorrecto($producto->Costo),
                'Impuesto' => Helpers::convertirvalorcorrecto($producto->Impuesto),
                'contarproductos' => $contarproductos
            );
        }else{
            $data = array(
                'Codigo' => '',
                'Producto' => '',
                'Unidad' => '',
                'Costo' => '',
                'Impuesto' => '',
                'contarproductos' => $contarproductos
            );
        }
        return response()->json($data);
    }
    //obtener proveedor por numero
    public function ordenes_compra_obtener_proveedor_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $plazo = '';
        $existeproveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->count();
        if($existeproveedor > 0){
            $proveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->first();
            $numero = $proveedor->Numero;
            $nombre = $proveedor->Nombre;
            $plazo = $proveedor->Plazo;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'plazo' => $plazo
        );
        return response()->json($data);
    }
    //obtener almacen por numero
    public function ordenes_compra_obtener_almacen_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $plazo = '';
        $contaralmacenesasignadosausuario = User_Rel_Almacen::where('user_id', Auth::user()->id)->count();
        if($contaralmacenesasignadosausuario > 0){
            $existealmacen = DB::table('user_rel_almacenes as ura')
            ->join('Almacenes as a', 'ura.almacen_id', '=', 'a.Numero')
            ->select('ura.id', 'a.Numero', 'a.Nombre')
            ->where('a.Numero', $request->numeroalmacen)
            ->where('a.Status', 'ALTA')
            ->where('ura.user_id', Auth::user()->id)
            ->count();
            if($existealmacen > 0){
                $almacen = DB::table('user_rel_almacenes as ura')
                ->join('Almacenes as a', 'ura.almacen_id', '=', 'a.Numero')
                ->select('ura.id', 'a.Numero', 'a.Nombre')
                ->where('a.Numero', $request->numeroalmacen)
                ->where('a.Status', 'ALTA')
                ->where('ura.user_id', Auth::user()->id)
                ->orderby('a.Numero', 'DESC')
                ->first();
                $numero = $almacen->Numero;
                $nombre = $almacen->Nombre;
            }
        }else{
            $existealmacen = Almacen::where('Numero', $request->numeroalmacen)->where('Status', 'ALTA')->count();
            if($existealmacen > 0){
                $almacen = Almacen::where('Numero', $request->numeroalmacen)->where('Status', 'ALTA')->first();
                $numero = $almacen->Numero;
                $nombre = $almacen->Nombre;
            }
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data);
    }
    //guardar en el módulo
    public function ordenes_compra_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\OrdenCompra',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $orden = $folio.'-'.$request->serie;
		$OrdenCompra = new OrdenCompra;
		$OrdenCompra->Orden=$orden;
		$OrdenCompra->Serie=$request->serie;
		$OrdenCompra->Folio=$folio;
		$OrdenCompra->Proveedor=$request->numeroproveedor;
        $OrdenCompra->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
		$OrdenCompra->Plazo=$request->plazo;
		$OrdenCompra->Almacen=$request->numeroalmacen;
		$OrdenCompra->Referencia=$request->referencia;
        $OrdenCompra->Tipo=$request->tipo;
        $OrdenCompra->Importe=$request->importe;
        $OrdenCompra->Descuento=$request->descuento;
        $OrdenCompra->SubTotal=$request->subtotal;
        $OrdenCompra->Iva=$request->iva;
        $OrdenCompra->Total=$request->total;
        $OrdenCompra->Obs=$request->observaciones;
        $OrdenCompra->Status="POR SURTIR";
        $OrdenCompra->Usuario=Auth::user()->user;
        $OrdenCompra->Periodo=$this->periodohoy;
        $OrdenCompra->OrdenTrabajo=$request->ordentrabajo;
        $OrdenCompra->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE COMPRA";
        $BitacoraDocumento->Movimiento = $orden;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "POR SURTIR";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){
            $OrdenCompraDetalle=new OrdenCompraDetalle;
            $OrdenCompraDetalle->Orden = $orden;
            $OrdenCompraDetalle->Proveedor = $request->numeroproveedor;
            $OrdenCompraDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $OrdenCompraDetalle->Codigo = $codigoproductopartida;
            $OrdenCompraDetalle->Descripcion = $request->nombreproductopartida [$key];
            $OrdenCompraDetalle->Unidad = $request->unidadproductopartida [$key];
            $OrdenCompraDetalle->Cantidad =  $request->cantidadpartida  [$key];
            $OrdenCompraDetalle->Precio =  $request->preciopartida [$key];
            $OrdenCompraDetalle->Importe = $request->importepartida [$key];
            //$OrdenCompraDetalle->Costo = $request->total [$key];
            $OrdenCompraDetalle->Dcto = $request->descuentoporcentajepartida [$key];
            $OrdenCompraDetalle->Descuento = $request->descuentopesospartida [$key];
            $OrdenCompraDetalle->SubTotal = $request->subtotalpartida [$key];
            $OrdenCompraDetalle->Impuesto = $request->ivaporcentajepartida [$key];
            $OrdenCompraDetalle->Iva = $request->ivapesospartida [$key];
            $OrdenCompraDetalle->Total = $request->totalpesospartida [$key];
            $OrdenCompraDetalle->Surtir = $request->cantidadpartida  [$key];
            $OrdenCompraDetalle->Registro = 0;
            $OrdenCompraDetalle->Item = $item;
            $OrdenCompraDetalle->save();
            $item++;
        }
    	return response()->json($OrdenCompra);
    }
    //verificar autorizacion
    public function ordenes_compra_verificar_autorizacion(Request $request){
        $OrdenCompra = OrdenCompra::where('Orden', $request->ordenautorizar)->first();
        $Detalles = OrdenCompraDetalle::select('Codigo')->where('Orden', $request->ordenautorizar)->get();
        $msjsurtimiento = '';
        foreach($Detalles as $d){
            $DetallesConSurtimientoPendiente = OrdenCompraDetalle::where('Codigo', $d->Codigo)->where('Orden', '<>', $request->ordenautorizar)->where('Surtir', '>', 0)->get();
            foreach($DetallesConSurtimientoPendiente as $dcsp){
                $msjsurtimiento = $msjsurtimiento.'<br> Codigo :'.$dcsp->Codigo.' Pendiente por surtir:'.$dcsp->Surtir.' En orden:'.$dcsp->Orden;
            }
        }
        $data = array(
            'OrdenCompra' => $OrdenCompra,
            'msjsurtimiento' => $msjsurtimiento
        );
        return response()->json($data);
    }
    //autorizar una orden de compra
    public function ordenes_compra_autorizar(Request $request){
        $OrdenCompra = OrdenCompra::where('Orden', $request->ordenautorizar)->first();
        OrdenCompra::where('Orden', $request->ordenautorizar)
            ->update([
                'AutorizadoPor' => Auth::user()->user,
                'AutorizadoFecha' => Helpers::fecha_exacta_accion_datetimestring()
            ]);
        /*
        $OrdenCompra->AutorizadoPor = Auth::user()->user;
        $OrdenCompra->AutorizadoFecha = Helpers::fecha_exacta_accion_datetimestring();
        $OrdenCompra->save();
        */
        return response()->json($OrdenCompra);
    }
    //verificar si se puede quitar auotizacion a orden compra
    public function ordenes_compra_verificar_quitar_autorizacion(Request $request){
        $OrdenCompra = OrdenCompra::where('Orden', $request->orden)->first();
        $resultado = CompraDetalle::where('Orden', $request->orden)->count();
        $numerocompra = 0;
        if($resultado > 0){
            $detallescompras = CompraDetalle::where('Orden', $request->orden)->get();
            foreach($detallescompras as $dc){
                $compra = Compra::where('Compra', $dc->Compra)->first();
                if($compra->Status != 'BAJA'){
                    $numerocompra++;
                }
            }
        }
        $data = array(
            'OrdenCompra' => $OrdenCompra,
            'numerocompra' => $numerocompra,
        );
        return response()->json($data);
    }
    //quitar autorizacion a orden de compra
    public function ordenes_compra_quitar_autorizacion(Request $request){
        $OrdenCompra = OrdenCompra::where('Orden', $request->ordenquitarautorizacion)->first();
        OrdenCompra::where('Orden', $request->ordenquitarautorizacion)
        ->update([
            'AutorizadoPor' => '',
            'AutorizadoFecha' => null
        ]);
        return response()->json($OrdenCompra);
    }
    //verificar si la orden de compra ya fue utilizada en una compra
    public function ordenes_compra_verificar_uso_en_modulos(Request $request){
        $OrdenCompra = OrdenCompra::where('Orden', $request->ordendesactivar)->first();
        $resultado = CompraDetalle::where('Orden', $request->ordendesactivar)->count();
        $numerocompra = 0;
        if($resultado > 0){
            $detallecompra = CompraDetalle::where('Orden', $request->ordendesactivar)->first();
            $numerocompra = $detallecompra->Compra;
        }
        $resultadofechas = Helpers::compararanoymesfechas($OrdenCompra->Fecha);
        $data = array (
            'resultadofechas' => $resultadofechas,
            'resultado' => $resultado,
            'numerocompra' => $numerocompra,
            'Status' => $OrdenCompra->Status
        );
        return response()->json($data);
    }
    //dar de baja orden de compra
    public function ordenes_compra_alta_o_baja(Request $request){
        $OrdenCompra = OrdenCompra::where('Orden', $request->ordendesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        OrdenCompra::where('Orden', $request->ordendesactivar)
        ->update([
            'MotivoBaja' => $MotivoBaja,
            'Status' => 'BAJA',
            'Importe' => '0.000000',
            'Descuento' => '0.000000',
            'SubTotal' => '0.000000',
            'Iva' => '0.000000',
            'Total' => '0.000000'
        ]);
        $detalles = OrdenCompraDetalle::where('Orden', $request->ordendesactivar)->get();
        foreach($detalles as $detalle){
            //colocar en ceros cantidades
            OrdenCompraDetalle::where('Orden', $request->ordendesactivar)
                            ->where('Item', $detalle->Item)
                            ->update([
                                'Cantidad' => '0.000000',
                                'Importe' => '0.000000',
                                'Dcto' => '0.000000',
                                'Descuento' => '0.000000',
                                'SubTotal' => '0.000000',
                                'Iva' => '0.000000',
                                'Total' => '0.000000',
                                'Surtir' => '0.000000'
                            ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE COMPRA";
        $BitacoraDocumento->Movimiento = $request->ordendesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $OrdenCompra->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($OrdenCompra);
    }
    //obtener datos de orden de compra
    public function ordenes_compra_obtener_orden_compra(Request $request){
        $ordencompra = OrdenCompra::where('Orden', $request->ordenmodificar)->first();
        $proveedor = Proveedor::where('Numero', $ordencompra->Proveedor)->first();
        $almacen = Almacen::where('Numero', $ordencompra->Almacen)->first();
        //saber si la modificacion es permitida
        $sumatotalcompras = 0;
        if($ordencompra->Status == 'POR SURTIR' || $ordencompra->Status == 'BACKORDER' && $ordencompra->AutorizadoPor == ''){
            $modificacionpermitida = 1;
        }else if($ordencompra->Status == 'BACKORDER'){
            $modificacionpermitida = 1;
            //traer los totales de las compras en las que la orden ya fue utilizada
            $compras = Compra::where('Orden',$request->ordenmodificar)->get();
            foreach($compras as $compra){
                $sumatotalcompras = $sumatotalcompras + $compra->Total;
            }
        }else{
            $modificacionpermitida = 0;
        }
        //detalles orden compra
        $detallesordencompra = OrdenCompraDetalle::where('Orden', $request->ordenmodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallesordencompra = OrdenCompraDetalle::where('Orden', $request->ordenmodificar)->count();
        if($numerodetallesordencompra > 0){
            $filasdetallesordencompra = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo = "modificacion";
            foreach($detallesordencompra as $doc){
                $cantidadyasurtidapartida = $doc->Cantidad - $doc->Surtir;
                //importante para copiar tabla
                $encabezadostablaacopiar = '#,Codigo,Descripcion,Unidad,Por Surtir,Cantidad,Precio $,Importe $,Dcto %,Dcto $,Subtotal $,Iva %, Iva $, Total $';
                $clasecolumnaobtenervalor = '.numerodetalle,.codigoproductopartida,.nombreproductopartida,.unidadproductopartida,.porsurtirpartida,.cantidadpartida,.preciopartida,.importepartida,.descuentoporcentajepartida,.descuentopesospartida,.subtotalpartida,.ivaporcentajepartida,.ivapesospartida,.totalpesospartida';
                $filasdetallesordencompra= $filasdetallesordencompra.
                '<tr class="filasproductos filaproducto'.$contadorproductos.'" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosproductos('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$doc->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod" ondblclick="construirtabladinamicaporfila('.$contadorfilas.',\'tr.filasproductos\',\''.$encabezadostablaacopiar.'\',\''.$clasecolumnaobtenervalor.'\')"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$doc->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$doc->Codigo.'</b></td>'.
                    '<td class="tdmod"><textarea rows="1" class="form-control inputnextdet nombreproductopartida" name="nombreproductopartida[]" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off" style="font-size:10px;">'.htmlspecialchars($doc->Descripcion, ENT_QUOTES).'</textarea></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$doc->Unidad.'" readonly data-parsley-length="[1, 5]">'.$doc->Unidad.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida"  name="porsurtirpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Surtir).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida"  name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Cantidad).'" data-parsley-min="'.Helpers::convertirvalorcorrecto($cantidadyasurtidapartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="calculartotalesfilasordencompra('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo .'\');formatocorrectoinputcantidades(this);" ></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadyasurtidapartida"  name="cantidadyasurtidapartida[]" value="'.Helpers::convertirvalorcorrecto($cantidadyasurtidapartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" ></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida"  name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="calculartotalesfilasordencompra('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo .'\');formatocorrectoinputcantidades(this);"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida"  name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida"  name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="calculardescuentopesospartida('.$contadorfilas.');formatocorrectoinputcantidades(this);" ></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida"  name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="calculardescuentoporcentajepartida('.$contadorfilas.');formatocorrectoinputcantidades(this);" ></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida"  name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida"  name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="calculartotalesfilasordencompra('.$contadorfilas.');formatocorrectoinputcantidades(this);" ></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida"  name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida"  name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesordencompra = '';
        }
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($ordencompra->Status == 'SURTIDO' || $ordencompra->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($ordencompra->Status == 'SURTIDO' || $ordencompra->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                /*$resultadofechas = Helpers::compararanoymesfechas($ordencompra->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{*/
                    if($ordencompra->AutorizadoPor == ''){
                        $modificacionpermitida = 1;
                    }else{
                        $modificacionpermitida = 0;
                    }
                //}
            }
        }
        $data = array(
            "ordencompra" => $ordencompra,
            "filasdetallesordencompra" => $filasdetallesordencompra,
            "numerodetallesordencompra" => $numerodetallesordencompra,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "modificacionpermitida" => $modificacionpermitida,
            "proveedor" => $proveedor,
            "almacen" => $almacen,
            "fecha" => Helpers::formatoinputdatetime($ordencompra->Fecha),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($ordencompra->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($ordencompra->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($ordencompra->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($ordencompra->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($ordencompra->Iva),
            "total" => Helpers::convertirvalorcorrecto($ordencompra->Total),
            "autorizadopor" => $ordencompra->AutorizadoPor,
            "sumatotalcompras" => Helpers::convertirvalorcorrecto($sumatotalcompras),
            "statusordencompra" => $ordencompra->Status
        );
        return response()->json($data);
    }
    //modificar datos orden de compra
    public function ordenes_compra_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $orden = $request->folio.'-'.$request->serie;
		$OrdenCompra = OrdenCompra::where('Orden', $orden)->first();
        //validar si las partidas en las modiifcacion son las mismas que los detalles de los traspasos
        // si no son las mismas comparar y eliminar las partidas que corresponden en la tabla detalles de OrdenesTrabajo y Traspasos
        //array partidas antes de modificacion
        $ArrayDetallesOrdenCompraAnterior = Array();
        $DetallesOrdenCompraAnterior = OrdenCompraDetalle::where('Orden', $orden)->get();
        foreach($DetallesOrdenCompraAnterior as $detalle){
            //array_push($ArrayDetallesOrdenCompraAnterior, $detalle->Codigo);
            array_push($ArrayDetallesOrdenCompraAnterior, $detalle->Orden.'#'.$detalle->Codigo.'#'.$detalle->Item);
        }
        //array partida despues de modificacion
        $ArrayDetallesOrdenCompraNuevo = Array();
        foreach ($request->codigoproductopartida as $key => $nuevocodigo){
            //array_push($ArrayDetallesOrdenCompraNuevo, $nuevocodigo);
            if($request->agregadoen [$key] == 'NA'){
                array_push($ArrayDetallesOrdenCompraNuevo, $orden.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
            }
        }
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesOrdenCompraAnterior, $ArrayDetallesOrdenCompraNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                //eliminar detalle de la remision eliminado
                $eliminardetalleordencompra = OrdenCompraDetalle::where('Orden', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
            }
        }
        //modificar orden compra
        OrdenCompra::where('Orden', $orden)
        ->update([
            'Proveedor'=>$request->numeroproveedor,
            'Fecha'=>Carbon::parse($request->fecha)->toDateTimeString(),
            'Plazo'=>$request->plazo,
            'Almacen'=>$request->numeroalmacen,
            'Referencia'=>$request->referencia,
            'Tipo'=>$request->tipo,
            'Importe'=>$request->importe,
            'Descuento'=>$request->descuento,
            'SubTotal'=>$request->subtotal,
            'Iva'=>$request->iva,
            'Total'=>$request->total,
            'Obs'=>$request->observaciones,
            'OrdenTrabajo'=>$request->ordentrabajo
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE COMPRA";
        $BitacoraDocumento->Movimiento = $orden;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $OrdenCompra->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $detallesporsurtir = 0;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){
            //if la partida se agrego en la modificacion se agrega en los detalles
            if($request->agregadoen [$key] == 'modificacion'){
                $contaritems = OrdenCompraDetalle::select('Item')->where('Orden', $orden)->count();
                if($contaritems > 0){
                    $item = OrdenCompraDetalle::select('Item')->where('Orden', $orden)->orderBy('Item', 'DESC')->take(1)->get();
                    $ultimoitem = $item[0]->Item+1;
                }else{
                    $ultimoitem = 1;
                }
                $OrdenCompraDetalle=new OrdenCompraDetalle;
                $OrdenCompraDetalle->Orden = $orden;
                $OrdenCompraDetalle->Proveedor = $request->numeroproveedor;
                $OrdenCompraDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $OrdenCompraDetalle->Codigo = $codigoproductopartida;
                $OrdenCompraDetalle->Descripcion = $request->nombreproductopartida [$key];
                $OrdenCompraDetalle->Unidad = $request->unidadproductopartida [$key];
                $OrdenCompraDetalle->Cantidad =  $request->cantidadpartida  [$key];
                $OrdenCompraDetalle->Precio =  $request->preciopartida [$key];
                $OrdenCompraDetalle->Importe = $request->importepartida [$key];
                $OrdenCompraDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                $OrdenCompraDetalle->Descuento = $request->descuentopesospartida [$key];
                $OrdenCompraDetalle->SubTotal = $request->subtotalpartida [$key];
                $OrdenCompraDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                $OrdenCompraDetalle->Iva = $request->ivapesospartida [$key];
                $OrdenCompraDetalle->Total = $request->totalpesospartida [$key];
                $OrdenCompraDetalle->Surtir = $request->cantidadpartida  [$key];
                $OrdenCompraDetalle->Registro = 0;
                $OrdenCompraDetalle->Item = $ultimoitem;
                $OrdenCompraDetalle->save();
                $ultimoitem++;
            }else{
                //si la partida no se agrego en la modificacion solo se modifican los datos
                //modificar detalle
                OrdenCompraDetalle::where('Orden', $orden)
                ->where('Item', $request->itempartida [$key])
                ->update([
                    'Proveedor' => $request->numeroproveedor,
                    'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                    'Codigo' => $codigoproductopartida,
                    'Descripcion' => $request->nombreproductopartida [$key],
                    'Unidad' => $request->unidadproductopartida [$key],
                    'Cantidad' =>  $request->cantidadpartida  [$key],
                    'Precio' =>  $request->preciopartida [$key],
                    'Importe' => $request->importepartida [$key],
                    'Dcto' => $request->descuentoporcentajepartida [$key],
                    'Descuento' => $request->descuentopesospartida [$key],
                    'SubTotal' => $request->subtotalpartida [$key],
              ,      'Impuesto' => $request->ivaporcentajepartida [$key],
                    'Iva' => $request->ivapesospartida [$key],
                    'Total' => $request->totalpesospartida [$key],
                    'Surtir' => $request->porsurtirpartida [$key]
                ]);
            }
            //verificar si la partida ya esta surtida
            if($request->porsurtirpartida [$key] > 0){
                $detallesporsurtir++;//aun no se termina de surtir
            }
        }
        //Cerrar la orden de compra si todas sus partidas tienen cero en por surtir
        if($detallesporsurtir == 0){
            $OrdenCompra = OrdenCompra::where('Orden', $orden)->first();
            OrdenCompra::where('Orden', $orden)
            ->update([
                'Status' => 'SURTIDO'
            ]);
            /*
            $OrdenCompra->Status='SURTIDO';
            $OrdenCompra->save();
            */
        }
    	return response()->json($OrdenCompra);
    }
    //buscar folio on key up
    public function ordenes_compra_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = VistaOrdenCompra::orderBy('Folio', 'ASC')->get();
            return DataTables::of($data)
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->make(true);
        }
    }
    //generacion de formato en PDF
    public function ordenes_compra_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        //primero eliminar todos los archivos zip
        Helpers::eliminararchivoszipgenerados();
        if($request->imprimirdirectamente == 1){
            $ordenescompra = OrdenCompra::where('Orden', $request->arraypdf)->get();
        }else{
            $tipogeneracionpdf = $request->tipogeneracionpdf;
            if($tipogeneracionpdf == 0){
                $ordenescompra = OrdenCompra::whereIn('Orden', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get();
            }else{
                $fechainiciopdf = date($request->fechainiciopdf);
                $fechaterminacionpdf = date($request->fechaterminacionpdf);
                if ($request->has("seriesdisponiblesdocumento")){
                    $ordenescompra = OrdenCompra::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->whereIn('Serie', $request->seriesdisponiblesdocumento)->orderBy('Folio', 'ASC')->take(1500)->get();
                }else{
                    $ordenescompra = OrdenCompra::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
                }
            }
        }
        if($ordenescompra->count() < 1){
            echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $arrayfilespdf = array();
        foreach ($ordenescompra as $oc){
            $data=array();
            $ordencompradetalle = OrdenCompraDetalle::where('Orden', $oc->Orden)->get();
            $datadetalle=array();
            foreach($ordencompradetalle as $ocd){
                $producto = Producto::where('Codigo', $ocd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($ocd->Cantidad),
                    "codigodetalle"=>$ocd->Codigo,
                    "descripciondetalle"=>$ocd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($ocd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($ocd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($ocd->SubTotal)
                );
            }
            $proveedor = Proveedor::where('Numero', $oc->Proveedor)->first();
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'OrdenesDeCompra')->where('Documento', $oc->Orden)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'OrdenesDeCompra')
            ->where('frd.Documento', $oc->Orden)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "ordencompra"=>$oc,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "descuentoordencompra"=>Helpers::convertirvalorcorrecto($oc->Descuento),
                      "subtotalordencompra"=>Helpers::convertirvalorcorrecto($oc->SubTotal),
                      "ivaordencompra"=>Helpers::convertirvalorcorrecto($oc->Iva),
                      "totalordencompra"=>Helpers::convertirvalorcorrecto($oc->Total),
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            //dd($data);
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.ordenescompra.formato_pdf_ordenescompra', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$oc->Orden.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($ordenescompra as $orc){
            $ArchivoPDF = "PDF".$orc->Orden.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
            array_push($arrayfilespdf,$ArchivoPDF);
        }
        $pdfMerger->merge(); //unirlos
        if($request->imprimirdirectamente == 1){
            $archivoacopiar = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $carpetacopias = public_path('xml_descargados/'.$ArchivoPDF);
            File::copy($archivoacopiar, $carpetacopias);
            return response()->json($ArchivoPDF);
        }else{
            if($request->descargar_xml == 0){
                $pdfMerger->save("OrdenesCompra.pdf", "browser");//mostrarlos en el navegador
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
    }

    //generacion de formato en PDF
    public function ordenes_compra_generar_pdfs_indiv($documento){
        $ordenescompra = OrdenCompra::where('Orden', $documento)->orderBy('Folio', 'ASC')->get();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($ordenescompra as $oc){
            $ordencompradetalle = OrdenCompraDetalle::where('Orden', $oc->Orden)->get();
            $datadetalle=array();
            foreach($ordencompradetalle as $ocd){
                $producto = Producto::where('Codigo', $ocd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($ocd->Cantidad),
                    "codigodetalle"=>$ocd->Codigo,
                    "descripciondetalle"=>$ocd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($ocd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($ocd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($ocd->SubTotal)
                );
            }
            $proveedor = Proveedor::where('Numero', $oc->Proveedor)->first();
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'OrdenesDeCompra')->where('Documento', $oc->Orden)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'OrdenesDeCompra')
            ->where('frd.Documento', $oc->Orden)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "ordencompra"=>$oc,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "descuentoordencompra"=>Helpers::convertirvalorcorrecto($oc->Descuento),
                      "subtotalordencompra"=>Helpers::convertirvalorcorrecto($oc->SubTotal),
                      "ivaordencompra"=>Helpers::convertirvalorcorrecto($oc->Iva),
                      "totalordencompra"=>Helpers::convertirvalorcorrecto($oc->Total),
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ordenescompra.formato_pdf_ordenescompra', compact('data'))
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
    public function ordenes_compra_obtener_datos_envio_email(Request $request){
        $ordencompra = OrdenCompra::where('Orden', $request->documento)->first();
        $proveedor = Proveedor::where('Numero',$ordencompra->Proveedor)->first();
        $email2cc = '';
        $email3cc = '';
        if($proveedor->Email2 != '' || $proveedor->Email2 != null){
            $email2cc = $proveedor->Email2;
        }
        if($proveedor->Email3 != '' || $proveedor->Email3 != null){
            $email3cc = $proveedor->Email3;
        }
        $data = array(
            'ordencompra' => $ordencompra,
            'proveedor' => $proveedor,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $proveedor->Email1,
            'email2cc' => $email2cc,
            'email3cc' => $email3cc,
            'correodefault1enviodocumentos' => $this->correodefault1enviodocumentos,
            'correodefault2enviodocumentos' => $this->correodefault2enviodocumentos
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function ordenes_compra_enviar_pdfs_email(Request $request){
        $ordenescompra = OrdenCompra::where('Orden', $request->emaildocumento)->orderBy('Folio', 'ASC')->get();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($ordenescompra as $oc){
            $ordencompradetalle = OrdenCompraDetalle::where('Orden', $oc->Orden)->get();
            $datadetalle=array();
            foreach($ordencompradetalle as $ocd){
                $producto = Producto::where('Codigo', $ocd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($ocd->Cantidad),
                    "codigodetalle"=>$ocd->Codigo,
                    "descripciondetalle"=>$ocd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($ocd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($ocd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($ocd->SubTotal)
                );
            }
            $proveedor = Proveedor::where('Numero', $oc->Proveedor)->first();
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'OrdenesDeCompra')->where('Documento', $oc->Orden)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'OrdenesDeCompra')
            ->where('frd.Documento', $oc->Orden)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "ordencompra"=>$oc,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "descuentoordencompra"=>Helpers::convertirvalorcorrecto($oc->Descuento),
                      "subtotalordencompra"=>Helpers::convertirvalorcorrecto($oc->SubTotal),
                      "ivaordencompra"=>Helpers::convertirvalorcorrecto($oc->Iva),
                      "totalordencompra"=>Helpers::convertirvalorcorrecto($oc->Total),
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ordenescompra.formato_pdf_ordenescompra', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            //enviar correo electrónico
            $datosdocumento = OrdenCompra::where('Orden', $request->emaildocumento)->first();
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
            //subir archivo arjunto 1 en public/archivos_adjuntos para poder adjuntarlo en el correo
            if($request->archivoadjunto != null) {
                //archivos adjuntos
                $mover_a_carpeta="archivos_adjuntos";
                $archivoadjunto = $request->archivoadjunto;
                $nombre_original_archivo_adjunto = $archivoadjunto->getClientOriginalName();
                $nuevo_nombre_archivo_adjunto = time().$nombre_original_archivo_adjunto;
                //guardar archivos en public/archivos_adjuntos
                $archivoadjunto->move($mover_a_carpeta, $nuevo_nombre_archivo_adjunto);
                $urlarchivoadjunto = public_path('archivos_adjuntos\\'.$nuevo_nombre_archivo_adjunto);
            }
            //subir archivo arjunto 2 en public/archivos_adjuntos para poder adjuntarlo en el correo
            if($request->archivoadjunto2 != null) {
                //archivos adjuntos
                $mover_a_carpeta="archivos_adjuntos";
                $archivoadjunto2 = $request->archivoadjunto2;
                $nombre_original_archivo_adjunto2 = $archivoadjunto2->getClientOriginalName();
                $nuevo_nombre_archivo_adjunto2 = time().$nombre_original_archivo_adjunto2;
                //guardar archivos en public/archivos_adjuntos
                $archivoadjunto2->move($mover_a_carpeta, $nuevo_nombre_archivo_adjunto2);
                $urlarchivoadjunto2 = public_path('archivos_adjuntos\\'.$nuevo_nombre_archivo_adjunto2);
            }
            $correos = [$request->emailpara,$request->email2cc,$request->email3cc];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailmensaje;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            if($request->archivoadjunto != null && $request->archivoadjunto2 != null) {
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $urlarchivoadjunto2, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "OrdenCompraNo".$emaildocumento.".pdf")
                            ->attach($urlarchivoadjunto)
                            ->attach($urlarchivoadjunto2);
                });
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto)) {
                    unlink($urlarchivoadjunto);
                }
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto2)) {
                    unlink($urlarchivoadjunto2);
                }
            }else if($request->archivoadjunto != null && $request->archivoadjunto2 == null){
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "OrdenCompraNo".$emaildocumento.".pdf")
                            ->attach($urlarchivoadjunto);
                });
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto)) {
                    unlink($urlarchivoadjunto);
                }
            }else if($request->archivoadjunto == null && $request->archivoadjunto2 != null){
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto2, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "OrdenCompraNo".$emaildocumento.".pdf")
                            ->attach($urlarchivoadjunto2);
                });
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto2)) {
                    unlink($urlarchivoadjunto2);
                }
            }else{
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "OrdenCompraNo".$emaildocumento.".pdf");
                });
            }
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

    //exportar ordenes de compra en excel
    public function ordenes_compra_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('OrdenesDeCompra', Auth::user()->id);
        return Excel::download(new OrdenesDeCompraExport($configuraciones_tabla['campos_consulta'],$request->periodo), "ordenesdecompra-".$request->periodo.".xlsx");
    }
    public function orden_compra_detalles_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $campos_consulta = [];
        array_push($campos_consulta, 'Orden');
        array_push($campos_consulta, 'Fecha');
        array_push($campos_consulta, 'Codigo');
        array_push($campos_consulta, 'Descripcion');
        array_push($campos_consulta, 'Unidad');
        array_push($campos_consulta, 'Cantidad');
        array_push($campos_consulta, 'Precio');
        array_push($campos_consulta, 'Importe');
        array_push($campos_consulta, 'SubTotal');
        array_push($campos_consulta, 'Iva');
        array_push($campos_consulta, 'Total');
        array_push($campos_consulta, 'Costo');
        return Excel::download(new OrdenCompraDetallesExport($campos_consulta,$request->orden), "ordenesdecompra-".$request->orden.".xlsx");
        }
    //configurar tabla
    public function ordenes_compra_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('OrdenesDeCompra', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'OrdenesDeCompra')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='OrdenesDeCompra';
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
        return redirect()->route('ordenes_compra');
    }

    //obtener claves productos
    public function ordenes_compra_obtener_claves_productos(Request $request){
        if($request->ajax()){
            $data = ClaveProdServ::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveproducto(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener claves unidades
    public function ordenes_compra_obtener_claves_unidades(Request $request){
        if($request->ajax()){
            $data = ClaveUnidad::where('Clave', 'H87')->orwhere('Clave', 'HUR');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveunidad(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener marcas
    public function ordenes_compra_obtener_marcas(Request $request){
        if($request->ajax()){
            $data = Marca::where('Status', 'ALTA')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmarca('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Utilidad1', function($data){
                        $utilidad1 = Helpers::convertirvalorcorrecto($data->Utilidad1);
                        return $utilidad1;
                    })
                    ->addColumn('Utilidad2', function($data){
                        $utilidad2 = Helpers::convertirvalorcorrecto($data->Utilidad2);
                        return $utilidad2;
                    })
                    ->addColumn('Utilidad3', function($data){
                        $utilidad3 = Helpers::convertirvalorcorrecto($data->Utilidad3);
                        return $utilidad3;
                    })
                    ->addColumn('Utilidad4', function($data){
                        $utilidad4 = Helpers::convertirvalorcorrecto($data->Utilidad4);
                        return $utilidad4;
                    })
                    ->addColumn('Utilidad5', function($data){
                        $utilidad5 = Helpers::convertirvalorcorrecto($data->Utilidad5);
                        return $utilidad5;
                    })
                    ->rawColumns(['operaciones','Utilidad1','Utilidad2','Utilidad3','Utilidad4','Utilidad5'])
                    ->make(true);
        }
    }
    //obtener lineas
    public function ordenes_compra_obtener_lineas(Request $request){
        if($request->ajax()){
            $data = Linea::where('Status', 'ALTA')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarlinea('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //guardar en catalogo
    public function ordenes_compra_guardar_producto(Request $request){
        $codigo=$request->codigo;
	    $ExisteProducto = Producto::where('Codigo', $codigo )->first();
	    if($ExisteProducto == true){
	        $Producto = 1;
	    }else{
            $marca = Marca::where('Numero', $request->marca)->first();
            $utilidadrestante = Helpers::convertirvalorcorrecto(100) - $marca->Utilidad1;
            $subtotalpesos = $request->costo / ($utilidadrestante/100);
            $utilidadpesos = $subtotalpesos - $request->costo;
            $ivapesos = $subtotalpesos * ($request->impuesto/100);
            $totalpesos = $subtotalpesos + $ivapesos;
            $Producto = new Producto;
            $Producto->Codigo=$request->codigo;
            $Producto->ClaveProducto=$request->claveproducto;
            $Producto->ClaveUnidad=$request->claveunidad;
            $Producto->Producto=$request->producto;
            $Producto->Unidad=$request->unidad;
            $Producto->Marca=$request->marca;
            $Producto->Linea=$request->linea;
            $Producto->Impuesto=$request->impuesto;
            $Producto->Costo=$request->costo;
            $Producto->Precio=$request->precio;
            $Producto->Utilidad=$marca->Utilidad1;
            $Producto->SubTotal=$subtotalpesos;
            $Producto->Iva=$ivapesos;
            $Producto->Total=$totalpesos;
            $Producto->Ubicacion=$request->ubicacion;
            $Producto->TipoProd = $request->tipoproducto;
            $Producto->Status='ALTA';
            $Producto->CostoDeLista=$request->costo;
            $Producto->Moneda='MXN';
            $Producto->CostoDeVenta=$request->costo;
            $Producto->Precio1=$request->precio;
            Log::channel('producto')->info('Se registro un nuevo producto: '.$Producto.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
            $Producto->save();
        }
        return response()->json($Producto);
    }

    public function ordenes_compra_validar_existencias(Request $request){
        ini_set('max_execution_time', -1); // 5 minutos
        ini_set('memory_limit', '-1');
        $arrayExistencias = array();
        $codigos = $request->codigoproductopartida;
        foreach ($codigos as $codigo) {
            $Existencias = Existencia::where('Codigo',$codigo)->where('Almacen',$request->numeroalmacen)->get();
            if ($Existencias->count() > 0) {
                if ($Existencias[0]->Existencias > 0) {
                    array_push($arrayExistencias, array(
                        "Codigo" => $codigo,
                        "Existencias"=> Helpers::convertirvalorcorrecto($Existencias[0]->Existencias)
                    ));
                }
            }
        }
        return response()->json($arrayExistencias);
    }

}
