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
use App\Exports\AjustesInventarioExport;
use App\Exports\PlantillasAjusteExport;
use App\Imports\AjustesImport;
use App\AjusteInventario;
use App\AjusteInventarioDetalle;
use App\Serie;
use App\Almacen;
use App\Existencia;
use App\BitacoraDocumento;
use App\Producto;
use App\Configuracion_Tabla;
use App\VistaAjusteInventario;
use App\VistaObtenerExistenciaProducto;
use App\Firma_Rel_Documento;
use App\User_Rel_Almacen;
use Config;
use Mail;
use LynX39\LaraPdfMerger\Facades\PdfMerger;
use Storage; 
use ZipArchive;

class AjusteInventarioController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function ajustesinventario(){
        $serieusuario = 'A';
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('AjustesInventario', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('ajustesinventario_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('ajustesinventario_exportar_excel');
        $rutacreardocumento = route('ajustesinventario_generar_pdfs');
        $urlgenerarplantilla = route('ajustesinventario_generar_plantilla');
        return view('registros.ajustesinventario.ajustesinventario', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento','urlgenerarplantilla'));
    }
    
    //obtener las asignaciones de herramienta
    public function ajustesinventario_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('AjustesInventario', Auth::user()->id);
            $periodo = $request->periodo;
            $data = VistaAjusteInventario::select($configuraciones_tabla['campos_consulta'])->where('periodo', $periodo);
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
                    ->withQuery('sumatotal', function($data) {
                        return $data->sum('Total');
                    })
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Ajuste .'\')">Cambios</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar(\''.$data->Ajuste .'\')">Bajas</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="'.route('ajustesinventario_generar_pdfs_indiv',$data->Ajuste).'" target="_blank">Ver Documento PDF</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Ajuste .'\')">Enviar Documento por Correo</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                    //->addColumn('Total', function($data){ return $data->Total; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //descargar plantilla
    public function ajustesinventario_generar_plantilla(){
        return Excel::download(new PlantillasAjusteExport(), "plantillaajustes.xlsx"); 
    }
    //cargar partidas excel
    public function ajustesinventario_cargar_partidas_excel(Request $request){
        $arrayexcel =  Excel::toArray(new AjustesImport, request()->file('partidasexcel'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $filasdetallesajuste = '';
        $contadorproductos = $request->contadorproductos;
        $contadorfilas = $request->contadorfilas;
        $tipooperacion = 'alta';
        $arraycodigosyaagregados = $porciones = explode(",", $request->arraycodigospartidas);
        foreach($partidasexcel as $partida){
            if($rowexcel > 0){
                if (in_array(strtoupper($partida[0]), $arraycodigosyaagregados)) {
                    
                }else{
                    $codigoabuscar = $partida[0];
                    $entradas = 0;
                    $salidas = 0;
                    $parsleyminentradas = '0.000000';
                    $parsleyminsalidas = '0.000000';
                    //si entradas y salidas son nulos, ceros o strings
                    if(($partida[1] == null || $partida[1] == 0 || is_string($partida[1]) == true) && ($partida[2] == null || $partida[2] == 0 || is_string($partida[2]) == true)){
                        $parsleyminentradas = '0.'.$this->numerocerosconfiguradosinputnumberstep;
                        $parsleyminsalidas = '0.'.$this->numerocerosconfiguradosinputnumberstep;
                        $entradas = 0;
                        $salidas = 0;
                    }
                    //si entradas y salidas son mayores a 0
                    if($partida[1] > 0 && $partida[2] > 0){
                        $parsleyminentradas = '0.'.$this->numerocerosconfiguradosinputnumberstep;
                        $parsleyminsalidas = '0.000000';
                        $entradas = $partida[1];
                        $salidas = 0;
                    }
                    //si escribio entradas y la salidas son nulas, cero o string
                    if($partida[1] > 0 && ($partida[2] == null || $partida[2] == 0 || is_string($partida[2]) == true) ){
                        $parsleyminentradas = '0.'.$this->numerocerosconfiguradosinputnumberstep;
                        $parsleyminsalidas = '0.000000';
                        $entradas = $partida[1];
                        $salidas = 0;
                    }
                    //si escribio salidas y las entradas son nulas, cero o string
                    if($partida[2] > 0 && ($partida[1] == null || $partida[1] == 0 || is_string($partida[1]) == true) ){
                        $parsleyminsalidas = '0.'.$this->numerocerosconfiguradosinputnumberstep;
                        $parsleyminentradas = '0.000000';
                        $salidas = $partida[2];
                        $entradas = 0;
                    }
                    $numeroalmacen = $request->numeroalmacen;
                    $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', ''.$codigoabuscar.'')->count();
                    if($contarproductos > 0){
                        $producto = VistaObtenerExistenciaProducto::where('Codigo', ''.$codigoabuscar.'')->first();
                        $contarexistencia = Existencia::where('Codigo', ''.$codigoabuscar.'')->where('Almacen', $numeroalmacen)->count();
                        if($contarexistencia > 0){
                            $Existencia = Existencia::where('Codigo', ''.$codigoabuscar.'')->where('Almacen', $numeroalmacen)->first();
                            $parsleymax = $Existencia->Existencias;
                        }else{
                            $parsleymax = 0;
                        }
                        $subtotalentradaspartida = $entradas*$producto->Costo;
                        $subtotalsalidaspartida = $salidas*$producto->Costo;
                        $existencianuevapartida = $parsleymax + $entradas - $salidas;
                        $filasdetallesajuste= $filasdetallesajuste.
                        '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfilas" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'.$producto->Codigo.'" readonly data-parsley-length="[1, 20]"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl nombreproductopartida" name="nombreproductopartida[]" id="nombreproductopartida[]" value="'.htmlspecialchars($producto->Producto, ENT_QUOTES).'" readonly data-parsley-length="[1, 255]"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'.$producto->Unidad.'" readonly data-parsley-length="[1, 5]"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm existenciaactualpartida" name="existenciaactualpartida[]" id="existenciaactualpartida[]" value="'.Helpers::convertirvalorcorrecto($parsleymax).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod">'.
                                '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm entradaspartidadb" name="entradaspartidadb[]" value="'.Helpers::convertirvalorcorrecto($entradas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm entradaspartida" name="entradaspartida[]" id="entradaspartida[]" value="'.Helpers::convertirvalorcorrecto($entradas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" data-parsley-min="'.$parsleyminentradas.'" onchange="formatocorrectoinputcantidades(this);calcularsubtotalentradas('.$contadorfilas.');calcularexistencianueva('.$contadorfilas.');colocardataparsleyminentradas('.$contadorfilas.');">'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm salidaspartidadb" name="salidaspartidadb[]" value="'.Helpers::convertirvalorcorrecto($salidas).'"data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/"  readonly>'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm salidaspartida" name="salidaspartida[]" id="salidaspartida[]" value="'.Helpers::convertirvalorcorrecto($salidas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" data-parsley-min="'.$parsleyminsalidas.'" data-parsley-existencias="'.$parsleymax.'"	 onchange="formatocorrectoinputcantidades(this);calcularsubtotalsalidas('.$contadorfilas.');calcularexistencianueva('.$contadorfilas.');revisarexistenciasalmacen('.$contadorfilas.');colocardataparsleyminsalidas('.$contadorfilas.');">'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm existencianuevapartida" name="existencianuevapartida[]" id="existencianuevapartida[]" value="'.Helpers::convertirvalorcorrecto($existencianuevapartida).'"data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm costopartida" name="costopartida[]" id="costopartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);cambiocosto('.$contadorfilas.');"></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalentradaspartida" name="subtotalentradaspartida[]" id="subtotalentradaspartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalentradaspartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalsalidaspartida" name="subtotalsalidaspartida[]" id="subtotalsalidaspartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalsalidaspartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
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
            "filasdetallesajuste" => $filasdetallesajuste,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
        );
        return response()->json($data); 
    }
    //obtener series documento
    public function ajustesinventario_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'AjustesInventario')->where('Usuario', Auth::user()->user)->get();
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
    public function ajustesinventario_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\AjusteInventario',$request->Serie);
        return response()->json($folio);
    }
    //obtener ultimi registro
    public function ajustesinventario_obtener_ultimo_id(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\AjusteInventario',$request->serie);
        return response()->json($folio);
    }

    //obtener almacenes
    public function ajustesinventario_obtener_almacenes(Request $request){
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

    //obtener almacen por numero
    public function ajustesinventario_obtener_almacen_por_numero(Request $request){
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

    //obtener productos
    public function ajustesinventario_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $tipooperacion = $request->tipooperacion;
            //$numeroalmacen = $request->numeroalmacen;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion){
                        //if($data->Almacen == $numeroalmacen || $data->Almacen == NULL){
                            $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.$tipooperacion.'\')">Seleccionar</div>';
                        //}else{
                            //$boton = '';
                        //}    
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
    public function ajustesinventario_obtener_producto_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        //$numeroalmacen = $request->numeroalmacen;
        $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)
                                                            /*->where(function ($query) use ($numeroalmacen) {
                                                                $query->where('Almacen', $numeroalmacen)
                                                                ->orWhere('Almacen', NULL);
                                                            })*/->count();
        if($contarproductos > 0){
            $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)
                                                        /*->where(function ($query) use ($numeroalmacen) {
                                                            $query->where('Almacen', $numeroalmacen)
                                                            ->orWhere('Almacen', NULL);
                                                        })*/->first();
            $data = array(
                'Codigo' => $producto->Codigo,
                'Producto' => htmlspecialchars($producto->Producto, ENT_QUOTES),
                'Unidad' => $producto->Unidad,
                'Costo' => Helpers::convertirvalorcorrecto($producto->Costo),
                'Impuesto' => Helpers::convertirvalorcorrecto($producto->Impuesto),
                'contarproductos' => $contarproductos,
            );
        }else{
            $data = array(
                'Codigo' => '',
                'Producto' => '',
                'Unidad' => '',
                'Costo' => '',
                'Impuesto' => '',
                'contarproductos' => $contarproductos,
            );
        }
        return response()->json($data); 
    }

    //obtener existencias
    public function ajustesinventario_obtener_existencias_partida(Request $request){
        $ContarExistencia = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->almacen)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->almacen)->first();
            $existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $existencias = Helpers::convertirvalorcorrecto(0);
        }
        $ajuste = $request->folio.'-'.$request->serie;
        $detalleajusteinventario = AjusteInventarioDetalle::where('Ajuste', $ajuste)->where('Codigo', $request->codigopartida)->count();
        $nuevaexistencia = 0;
        if($detalleajusteinventario > 0){
            $detalleajusteinventario = AjusteInventarioDetalle::where('Ajuste', $ajuste)->where('Codigo', $request->codigopartida)->first();
            $nuevaexistencia = $existencias + $detalleajusteinventario->Cantidad;
        }else{
            $nuevaexistencia = $existencias;
        }
        return response()->json(Helpers::convertirvalorcorrecto($nuevaexistencia));
    }

    //guardar
    public function ajustesinventario_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\AjusteInventario',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $ajuste = $folio.'-'.$request->serie;
        $AjusteInventario = new AjusteInventario;
        $AjusteInventario->Ajuste=$ajuste;
        $AjusteInventario->Serie=$request->serie;
        $AjusteInventario->Folio=$folio;
        $AjusteInventario->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $AjusteInventario->Obs=$request->observaciones;
        $AjusteInventario->Almacen=$request->numeroalmacen;
        $AjusteInventario->Total=$request->total;
        $AjusteInventario->Status="ALTA";
        $AjusteInventario->Usuario=Auth::user()->user;
        $AjusteInventario->Periodo=$this->periodohoy;
        $AjusteInventario->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "AJUSTES DE INVENTARIO";
        $BitacoraDocumento->Movimiento = $ajuste;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
            $AjusteInventarioDetalle=new AjusteInventarioDetalle;
            $AjusteInventarioDetalle->Ajuste = $ajuste;
            $AjusteInventarioDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $AjusteInventarioDetalle->Codigo = $codigoproductopartida;
            $AjusteInventarioDetalle->Descripcion = $request->nombreproductopartida [$key];
            $AjusteInventarioDetalle->Unidad = $request->unidadproductopartida [$key];
            $AjusteInventarioDetalle->Existencias =  $request->existenciaactualpartida   [$key];
            $AjusteInventarioDetalle->Entradas =  $request->entradaspartida [$key];
            $AjusteInventarioDetalle->Salidas = $request->salidaspartida [$key];
            $AjusteInventarioDetalle->Real = $request->existencianuevapartida  [$key];
            $AjusteInventarioDetalle->Costo = $request->costopartida  [$key];
            $AjusteInventarioDetalle->Item = $item;
            $AjusteInventarioDetalle->save();
            $item++;
            //modificar ultimocosto
            if($request->entradaspartida [$key] > 0){
                $Producto = Producto::where('Codigo', $codigoproductopartida)->first();
                if($this->tipodeutilidad == 'Financiera'){
                    $restautilidad = Helpers::convertirvalorcorrecto(100) - $Producto->Utilidad;
                    $divisionutilidad = $restautilidad / Helpers::convertirvalorcorrecto(100);
                    $nuevosubtotalproducto = $request->costopartida [$key] / $divisionutilidad;
                }else{
                    $sumautilidad = Helpers::convertirvalorcorrecto(100) + $Producto->Utilidad;
                    $divisionutilidad = $sumautilidad / Helpers::convertirvalorcorrecto(100);
                    $nuevosubtotalproducto = $request->costopartida [$key] * $divisionutilidad;
                }
                $nuevoivaproducto = $nuevosubtotalproducto*($Producto->Impuesto/Helpers::convertirvalorcorrecto(100));
                $nuevototalproducto = $nuevosubtotalproducto + $nuevoivaproducto;
                Producto::where('Codigo', $codigoproductopartida)
                ->update([
                    'Costo' => $request->costopartida [$key],
                    'CostoDeLista' => $request->costopartida [$key],
                    'CostoDeVenta' => $request->costopartida [$key],
                    'Ultimo Costo' => $request->costopartida [$key],
                    'SubTotal' => Helpers::convertirvalorcorrecto($nuevosubtotalproducto),
                    'Iva' => Helpers::convertirvalorcorrecto($nuevoivaproducto),
                    'Total' => Helpers::convertirvalorcorrecto($nuevototalproducto)
                ]);
            }
            //modificar las existencias del código en la tabla de existencias
            $ContarExistencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
            if($ContarExistencia > 0){
                $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                Existencia::where('Codigo', $codigoproductopartida)
                ->where('Almacen', $request->numeroalmacen)
                ->update([
                    'Existencias' => Helpers::convertirvalorcorrecto($request->existencianuevapartida [$key])
                ]);
            }else{
                $Existencia = new Existencia;
                $Existencia->Codigo = $codigoproductopartida;
                $Existencia->Almacen = $request->numeroalmacen;
                $Existencia->Existencias = $request->existencianuevapartida [$key];
                $Existencia->save();
            }
        }
        return response()->json($AjusteInventario);
    }

    //verificar si se puede dar de baja
    public function ajustesinventario_verificar_baja(Request $request){
        $AjusteInventario = AjusteInventario::where('Ajuste', $request->ajustedesactivar)->first();
        $detalles = AjusteInventarioDetalle::where('Ajuste', $request->ajustedesactivar)->get();
        $errores = '';
        foreach($detalles as $detalle){
            if($detalle->Entradas > 0){
                $Existencia = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $AjusteInventario->Almacen)->first();
                if($detalle->Entradas > $Existencia->Existencias){
                    $errores = $errores.'Error el ajuste no se puede cancelar, no hay existencias suficientes en el almacen:'.$AjusteInventario->Almacen.' para el código:'.$detalle->Codigo.'<br>';
                }

            }
        }
        $resultadofechas = Helpers::compararanoymesfechas($AjusteInventario->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'errores' => $errores,
            'Status' => $AjusteInventario->Status
        );
        return response()->json($data);
    }

    //bajas
    public function ajustesinventario_alta_o_baja(Request $request){
        $AjusteInventario = AjusteInventario::where('Ajuste', $request->ajustedesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        AjusteInventario::where('Ajuste', $request->ajustedesactivar)
        ->update([
            'MotivoBaja' => $MotivoBaja,
            'Status' => 'BAJA',
            'Total' => '0.000000'
        ]);
        $detalles = AjusteInventarioDetalle::where('Ajuste', $request->ajustedesactivar)->get();
        foreach($detalles as $detalle){
            //entradas
            if($detalle->Entradas > 0){
                //restar las entradas
                $Existencia = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $AjusteInventario->Almacen)->first();
                $NuevaExistenciaEntradas = $Existencia->Existencias-$detalle->Entradas;
                Existencia::where('Codigo', $detalle->Codigo)
                ->where('Almacen', $AjusteInventario->Almacen)
                ->update([
                    'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaEntradas)
                ]);
            }
            //salidas
            if($detalle->Salidas > 0){
                //sumar las salidas
                $Existencia = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $AjusteInventario->Almacen)->first();
                $NuevaExistenciaSalidas = $Existencia->Existencias+$detalle->Salidas;
                Existencia::where('Codigo', $detalle->Codigo)
                ->where('Almacen', $AjusteInventario->Almacen)
                ->update([
                    'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaSalidas)
                ]);
            }
            //colocar en ceros cantidades
            AjusteInventarioDetalle::where('Ajuste', $request->ajustedesactivar)
                                    ->where('Item', $detalle->Item)
                                    ->update([
                                        'Existencias' => '0.000000',
                                        'Entradas' => '0.000000',
                                        'Salidas' => '0.000000',
                                        'Real' => '0.000000'
                                    ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "AJUSTES DE INVENTARIO";
        $BitacoraDocumento->Movimiento = $request->ajustedesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $AjusteInventario->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($AjusteInventario);
    }

    //obtener nuevos datos fila
    public function ajustesinventario_obtener_nuevos_datos_fila(Request $request){
        $ContarExistencia = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->numeroalmacen)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->numeroalmacen)->first();
            $existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $existencias = Helpers::convertirvalorcorrecto(0);
        }
        $ajuste = $request->folio.'-'.$request->serie;
        $ajusteinventario = AjusteInventario::where('Ajuste', $ajuste)->first();
        $contardetalleajusteinventario = AjusteInventarioDetalle::where('Ajuste', $ajuste)->where('Codigo', $request->codigopartida)->count();
        $nuevaexistencia = 0;
        $existencianuevapartida = 0;
        $entradaspartida = $request->entradaspartida;
        $salidaspartida = $request->salidaspartida;
        $entradaspartidadb = 0;
        $salidaspartidadb = 0;
        if($contardetalleajusteinventario > 0){
            $detalleajusteinventario = AjusteInventarioDetalle::where('Ajuste', $ajuste)->where('Codigo', $request->codigopartida)->first();
            if($request->numeroalmacen == $ajusteinventario->Almacen){
                $nuevaexistencia = $detalleajusteinventario->Existencias;
            }else{
                $nuevaexistencia = $existencias;
            }
            $existenciaactualpartida = $nuevaexistencia;
            $entradaspartidadb = $detalleajusteinventario->Entradas;
            $salidaspartidadb = $detalleajusteinventario->Salidas;
        }else{
            $nuevaexistencia = $existencias;
            $existenciaactualpartida = $nuevaexistencia;
        }
        $existencianuevapartida = $existenciaactualpartida+$entradaspartida-$salidaspartida;
        $nuevosdatosfila = array(
            'nuevaexistencia' => Helpers::convertirvalorcorrecto($nuevaexistencia),
            'existencianuevapartida' => Helpers::convertirvalorcorrecto($existencianuevapartida)
        );
        return response()->json($nuevosdatosfila);
    }

    //obtener ajuste
    public function ajustesinventario_obtener_ajuste(Request $request){
        $ajuste = AjusteInventario::where('Ajuste', $request->ajustemodificar)->first();
        $almacen = Almacen::where('Numero', $ajuste->Almacen)->first();
        //detalles
        $detallesajuste= AjusteInventarioDetalle::where('Ajuste', $request->ajustemodificar)->get();
        $numerodetallesajuste = AjusteInventarioDetalle::where('Ajuste', $request->ajustemodificar)->count();
        if($numerodetallesajuste > 0){
            $filasdetallesajuste = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            foreach($detallesajuste as $da){
                $producto = Producto::where('Codigo', $da->Codigo)->first();
                $Existencia = Existencia::where('Codigo', $da->Codigo)->where('Almacen', $ajuste->Almacen)->first();
                $subtotalentradaspartida = $da->Entradas*$da->Costo;
                $subtotalsalidaspartida = $da->Salidas*$da->Costo;
                $parsleymax = $Existencia->Existencias+$da->Salidas;
                $filasdetallesajuste= $filasdetallesajuste.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfilas" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$da->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd codigoproductopartida" name="codigoproductopartida[]" value="'.$da->Codigo.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl inputnextdet nombreproductopartida" name="nombreproductopartida[]" value="'.htmlspecialchars($da->Descripcion, ENT_QUOTES).'" readonly data-parsley-length="[1, 255]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm unidadproductopartida" name="unidadproductopartida[]" value="'.$da->Unidad.'" readonly data-parsley-length="[1, 5]"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm existenciaactualpartida" name="existenciaactualpartida[]" value="'.Helpers::convertirvalorcorrecto($da->Existencias).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm entradaspartidadb" name="entradaspartidadb[]" value="'.Helpers::convertirvalorcorrecto($da->Entradas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm entradaspartida" name="entradaspartida[]" value="'.Helpers::convertirvalorcorrecto($da->Entradas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularsubtotalentradas('.$contadorfilas.');calcularexistencianueva('.$contadorfilas.');colocardataparsleyminentradas('.$contadorfilas.');">'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm salidaspartidadb" name="salidaspartidadb[]" value="'.Helpers::convertirvalorcorrecto($da->Salidas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/"  readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm salidaspartida" name="salidaspartida[]" value="'.Helpers::convertirvalorcorrecto($da->Salidas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" data-parsley-existencias="'.$parsleymax.'" onchange="formatocorrectoinputcantidades(this);calcularsubtotalsalidas('.$contadorfilas.');calcularexistencianueva('.$contadorfilas.');revisarexistenciasalmacen('.$contadorfilas.');colocardataparsleyminsalidas('.$contadorfilas.');">'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm existencianuevapartida" name="existencianuevapartida[]" value="'.Helpers::convertirvalorcorrecto($da->Real).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($da->Costo).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);cambiocosto('.$contadorfilas.');"></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalentradaspartida" name="subtotalentradaspartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalentradaspartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalsalidaspartida" name="subtotalsalidaspartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalsalidaspartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesajuste = '';
        }        
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($ajuste->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($ajuste->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($ajuste->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }
        $data = array(
            "ajuste" => $ajuste,
            "almacen" => $almacen,
            "filasdetallesajuste" => $filasdetallesajuste,
            "numerodetallesajuste" => $numerodetallesajuste,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdatetime($ajuste->Fecha),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($ajuste->Fecha),
            "total" => Helpers::convertirvalorcorrecto($ajuste->Total),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data); 
    }

    //modificar
    public function ajustesinventario_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $ajuste = $request->folio.'-'.$request->serie;
        $AjusteInventario = AjusteInventario::where('Ajuste', $ajuste)->first();
        $numeroalmacendb = $request->numeroalmacendb;
        $numeroalmacen = $request->numeroalmacen;
        if($numeroalmacendb == $numeroalmacen){
            //array detalles antes de modificacion
            $ArrayDetallesAjusteAnterior = Array();
            $DetallesAjusteAnterior = AjusteInventarioDetalle::where('Ajuste', $ajuste)->get();
            foreach($DetallesAjusteAnterior as $detalle){
                array_push($ArrayDetallesAjusteAnterior, $detalle->Ajuste.'#'.$detalle->Codigo.'#'.$detalle->Item);
            }
            //array detalles despues de modificacion
            $ArrayDetallesAjusteNuevo = Array();
            foreach ($request->codigoproductopartida as $key => $nuevocodigo){
                if($request->agregadoen [$key] == 'NA'){
                    array_push($ArrayDetallesAjusteNuevo, $ajuste.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
                } 
            }  
            //diferencias entre arreglos
            $diferencias_arreglos = array_diff($ArrayDetallesAjusteAnterior, $ArrayDetallesAjusteNuevo);
            //iteramos las diferencias entre arreglos
            if(count($diferencias_arreglos) > 0){
                foreach($diferencias_arreglos as $eliminapartida){
                    $explode_d = explode("#",$eliminapartida);
                    $detalleajuste = AjusteInventarioDetalle::where('Ajuste', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->first();
                    //entradas
                    if($detalleajuste->Entradas > 0){
                        //restar las entradas
                        $Existencia = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacen)->first();
                        $NuevaExistenciaEntradas = $Existencia->Existencias-$detalleajuste->Entradas;
                        Existencia::where('Codigo', $explode_d[1])
                        ->where('Almacen', $request->numeroalmacen)
                        ->update([
                            'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaEntradas)
                        ]);
                    }
                    //salidas
                    if($detalleajuste->Salidas > 0){
                        //sumar las salidas
                        $Existencia = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacen)->first();
                        $NuevaExistenciaSalidas = $Existencia->Existencias+$detalleajuste->Salidas;
                        Existencia::where('Codigo', $explode_d[1])
                        ->where('Almacen', $request->numeroalmacen)
                        ->update([
                            'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaSalidas)
                        ]);
                    }
                    //eliminar detalle
                    $eliminardetalle= AjusteInventarioDetalle::where('Ajuste', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
                }
            }
        }else{
            $detallesajustes = AjusteInventarioDetalle::where('Ajuste', $ajuste)->get();
            foreach($detallesajustes as $da){
                //entradas
                if($da->Entradas > 0){
                    //restar las entradas
                    $Existencia = Existencia::where('Codigo', $da->Codigo)->where('Almacen', $request->numeroalmacendb)->first();
                    $NuevaExistenciaEntradas = $Existencia->Existencias-$da->Entradas;
                    Existencia::where('Codigo', $da->Codigo)
                    ->where('Almacen', $request->numeroalmacendb)
                    ->update([
                        'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaEntradas)
                    ]);
                }
                //salidas
                if($da->Salidas > 0){
                    //sumar las salidas
                    $Existencia = Existencia::where('Codigo', $da->Codigo)->where('Almacen', $request->numeroalmacendb)->first();
                    $NuevaExistenciaSalidas = $Existencia->Existencias+$da->Salidas;
                    Existencia::where('Codigo', $da->Codigo)
                    ->where('Almacen', $request->numeroalmacendb)
                    ->update([
                        'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaSalidas)
                    ]);
                }
                //eliminar detalle
                $eliminardetalle= AjusteInventarioDetalle::where('Ajuste', $da->Ajuste)->where('Codigo', $da->Codigo)->where('Item', $da->Item)->forceDelete();
            }
        }
        //modificar ajuste
        AjusteInventario::where('Ajuste', $ajuste)
        ->update([
            'Fecha'=>Carbon::parse($request->fecha)->toDateTimeString(),
            'Obs'=>$request->observaciones,
            'Almacen'=>$request->numeroalmacen,
            'Total'=>$request->total
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "AJUSTES DE INVENTARIO";
        $BitacoraDocumento->Movimiento = $ajuste;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $AjusteInventario->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        //if al ajuste no se le modifico el almacen
        if($numeroalmacendb == $numeroalmacen){  
            foreach ($request->codigoproductopartida as $key => $codigoproductopartida){ 
                //if la partida se agrego en la modificacion se realiza un insert
                if($request->agregadoen [$key] == 'modificacion'){      
                    $contaritems = AjusteInventarioDetalle::select('Item')->where('Ajuste', $ajuste)->count();
                    if($contaritems > 0){
                        $item = AjusteInventarioDetalle::select('Item')->where('Ajuste', $ajuste)->orderBy('Item', 'DESC')->take(1)->get();
                        $ultimoitem = $item[0]->Item+1;
                    }else{
                        $ultimoitem = 1;
                    }
                    $AjusteInventarioDetalle=new AjusteInventarioDetalle;
                    $AjusteInventarioDetalle->Ajuste = $ajuste;
                    $AjusteInventarioDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                    $AjusteInventarioDetalle->Codigo = $codigoproductopartida;
                    $AjusteInventarioDetalle->Descripcion = $request->nombreproductopartida [$key];
                    $AjusteInventarioDetalle->Unidad = $request->unidadproductopartida [$key];
                    $AjusteInventarioDetalle->Existencias =  $request->existenciaactualpartida   [$key];
                    $AjusteInventarioDetalle->Entradas =  $request->entradaspartida [$key];
                    $AjusteInventarioDetalle->Salidas = $request->salidaspartida [$key];
                    $AjusteInventarioDetalle->Real = $request->existencianuevapartida  [$key];
                    $AjusteInventarioDetalle->Costo = $request->costopartida  [$key];
                    $AjusteInventarioDetalle->Item = $ultimoitem;
                    $AjusteInventarioDetalle->save();
                    //modificar las existencias del código en la tabla de existencias
                    $ContarExistencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                    if($ContarExistencia > 0){
                        $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                        Existencia::where('Codigo', $codigoproductopartida)
                        ->where('Almacen', $request->numeroalmacen)
                        ->update([
                            'Existencias' => Helpers::convertirvalorcorrecto($request->existencianuevapartida [$key])
                        ]);
                    }else{
                        $Existencia = new Existencia;
                        $Existencia->Codigo = $codigoproductopartida;
                        $Existencia->Almacen = $request->numeroalmacen;
                        $Existencia->Existencias = $request->existencianuevapartida [$key];
                        $Existencia->save();
                    }
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar detalle
                    //ENTRADAS
                    //restar las entradas ajuste antes de modificar
                    $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                    $NuevaExistenciaEntradas = $Existencia->Existencias-$request->entradaspartidadb [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                    ->where('Almacen', $request->numeroalmacen)
                    ->update([
                        'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaEntradas)
                    ]);
                    //sumar las nuevas entradas de ajuste
                    $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                    $NuevaExistenciaEntradas = $Existencia->Existencias+$request->entradaspartida [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                    ->where('Almacen', $request->numeroalmacen)
                    ->update([
                        'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaEntradas)
                    ]);
                    //SALIDAS
                    //sumar las salidas de ajuste antes de modificar
                    $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                    $NuevaExistenciaEntradas = $Existencia->Existencias+$request->salidaspartidadb [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                    ->where('Almacen', $request->numeroalmacen)
                    ->update([
                        'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaEntradas)
                    ]);
                    //restar las nuevas salidas del ajuste
                    $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                    $NuevaExistenciaEntradas = $Existencia->Existencias-$request->salidaspartida [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                    ->where('Almacen', $request->numeroalmacen)
                    ->update([
                        'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaEntradas)
                    ]);
                    //existencias actuales
                    //$existenciaactualpartida = $request->existenciaactualpartida [$key] + $request->salidaspartidadb [$key] - $request->entradaspartidadb [$key];
                    AjusteInventarioDetalle::where('Ajuste', $ajuste)
                    ->where('Item', $request->itempartida [$key])
                    ->update([
                        'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                        //'Existencias' => $request->existenciaactualpartida [$key],
                        'Entradas' => $request->entradaspartida [$key],
                        'Salidas' => $request->salidaspartida  [$key],
                        'Real' => $request->existencianuevapartida [$key],
                        'Costo' => $request->costopartida [$key]
                    ]);
                }  
            }
        }else{
            $item = 1;
            foreach ($request->codigoproductopartida as $key => $codigoproductopartida){ 
                //si no ingresa los detalles con el nuevo almacen
                $AjusteInventarioDetalle=new AjusteInventarioDetalle;
                $AjusteInventarioDetalle->Ajuste = $ajuste;
                $AjusteInventarioDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $AjusteInventarioDetalle->Codigo = $codigoproductopartida;
                $AjusteInventarioDetalle->Descripcion = $request->nombreproductopartida [$key];
                $AjusteInventarioDetalle->Unidad = $request->unidadproductopartida [$key];
                $AjusteInventarioDetalle->Existencias =  $request->existenciaactualpartida   [$key];
                $AjusteInventarioDetalle->Entradas =  $request->entradaspartida [$key];
                $AjusteInventarioDetalle->Salidas = $request->salidaspartida [$key];
                $AjusteInventarioDetalle->Real = $request->existencianuevapartida  [$key];
                $AjusteInventarioDetalle->Costo = $request->costopartida  [$key];
                $AjusteInventarioDetalle->Item = $item;
                $AjusteInventarioDetalle->save();
                $item++;
                //modificar las existencias del código en la tabla de existencias
                $ContarExistencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                if($ContarExistencia > 0){
                    $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                    Existencia::where('Codigo', $codigoproductopartida)
                    ->where('Almacen', $request->numeroalmacen)
                    ->update([
                        'Existencias' => Helpers::convertirvalorcorrecto($request->existencianuevapartida [$key])
                    ]);
                }else{
                    $Existencia = new Existencia;
                    $Existencia->Codigo = $codigoproductopartida;
                    $Existencia->Almacen = $request->numeroalmacen;
                    $Existencia->Existencias = $request->existencianuevapartida [$key];
                    $Existencia->save();
                }
            }
        } 
        return response()->json($AjusteInventario);
    }

    //buscar folio
    public function ajustesinventario_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = VistaAjusteInventario::orderBy('Folio', 'ASC')->get();
            return DataTables::of($data)
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->make(true);
        } 
    }

    //generar documento pdf
    public function ajustesinventario_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        //primero eliminar todos los archivos zip
        Helpers::eliminararchivoszipgenerados();
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $ajustes = AjusteInventario::whereIn('Ajuste', $request->arraypdf)->orderBy('Folio', 'ASC')->take(500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            if ($request->has("seriesdisponiblesdocumento")){
                $ajustes = AjusteInventario::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->whereIn('Serie', $request->seriesdisponiblesdocumento)->orderBy('Folio', 'ASC')->take(500)->get();
            }else{
                $ajustes = AjusteInventario::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(500)->get();
            }
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $arrayfilespdf = array();
        foreach ($ajustes as $a){
            $data=array();
            $ajustedetalle = AjusteInventarioDetalle::where('Ajuste', $a->Ajuste)->get();
            $datadetalle=array();
            foreach($ajustedetalle as $ad){
                $producto = Producto::where('Codigo', $ad->Codigo)->first();
                $totalentradas=$ad->Entradas*$ad->Costo;
                $totalsalidas=$ad->Salidas*$ad->Costo;
                if($totalentradas >= $totalsalidas){
                    $total = $totalentradas - $totalsalidas;
                }else{
                    $total = $totalsalidas - $totalentradas;
                }
                $datadetalle[]=array(
                    "codigodetalle"=>$ad->Codigo,
                    "descripciondetalle"=>$ad->Descripcion,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "existendetalle"=> Helpers::convertirvalorcorrecto($ad->Existencias),
                    "entradasdetalle" => Helpers::convertirvalorcorrecto($ad->Entradas),
                    "salidasdetalle" => Helpers::convertirvalorcorrecto($ad->Salidas),
                    "realdetalle" => Helpers::convertirvalorcorrecto($ad->Real),
                    "costodetalle" => Helpers::convertirvalorcorrecto($ad->Costo),
                    "totaldetalle" => Helpers::convertirvalorcorrecto($total)
                );
            } 
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'AjustesInventario')->where('Documento', $a->Ajuste)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'AjustesInventario')
            ->where('frd.Documento', $a->Ajuste)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "ajuste"=>$a,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "totalajuste"=>Helpers::convertirvalorcorrecto($a->Total),
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            //dd($data);
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.ajustesinventario.formato_pdf_ajustesinventario', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$a->Ajuste.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($ajustes as $aj){
            $ArchivoPDF = "PDF".$aj->Ajuste.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
            array_push($arrayfilespdf,$ArchivoPDF);
        }
        $pdfMerger->merge(); //unirlos
        if($request->descargar_xml == 0){
            $pdfMerger->save("AjustesInventario.pdf", "browser");//mostrarlos en el navegador
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
    public function ajustesinventario_generar_pdfs_indiv($documento){
        $ajustes = AjusteInventario::where('Ajuste', $documento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($ajustes as $a){
            $ajustedetalle = AjusteInventarioDetalle::where('Ajuste', $a->Ajuste)->get();
            $datadetalle=array();
            foreach($ajustedetalle as $ad){
                $producto = Producto::where('Codigo', $ad->Codigo)->first();
                $totalentradas=$ad->Entradas*$ad->Costo;
                $totalsalidas=$ad->Salidas*$ad->Costo;
                if($totalentradas >= $totalsalidas){
                    $total = $totalentradas - $totalsalidas;
                }else{
                    $total = $totalsalidas - $totalentradas;
                }
                $datadetalle[]=array(
                    "codigodetalle"=>$ad->Codigo,
                    "descripciondetalle"=>$ad->Descripcion,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "existendetalle"=> Helpers::convertirvalorcorrecto($ad->Existencias),
                    "entradasdetalle" => Helpers::convertirvalorcorrecto($ad->Entradas),
                    "salidasdetalle" => Helpers::convertirvalorcorrecto($ad->Salidas),
                    "realdetalle" => Helpers::convertirvalorcorrecto($ad->Real),
                    "costodetalle" => Helpers::convertirvalorcorrecto($ad->Costo),
                    "totaldetalle" => Helpers::convertirvalorcorrecto($total)
                );
            } 
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'AjustesInventario')->where('Documento', $a->Ajuste)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'AjustesInventario')
            ->where('frd.Documento', $a->Ajuste)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "ajuste"=>$a,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "totalajuste"=>Helpers::convertirvalorcorrecto($a->Total),
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ajustesinventario.formato_pdf_ajustesinventario', compact('data'))
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
    public function ajustesinventario_obtener_datos_envio_email(Request $request){
        $ajusteinventario = AjusteInventario::where('Ajuste', $request->documento)->first();
        $data = array(
            'ajusteinventario' => $ajusteinventario,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => "",
            'email2cc' => "",
            'email3cc' => "",
            'correodefault1enviodocumentos' => $this->correodefault1enviodocumentos,
            'correodefault2enviodocumentos' => $this->correodefault2enviodocumentos
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function ajustesinventario_enviar_pdfs_email(Request $request){
        $ajustes = AjusteInventario::where('Ajuste', $request->emaildocumento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($ajustes as $a){
            $ajustedetalle = AjusteInventarioDetalle::where('Ajuste', $a->Ajuste)->get();
            $datadetalle=array();
            foreach($ajustedetalle as $ad){
                $producto = Producto::where('Codigo', $ad->Codigo)->first();
                $totalentradas=$ad->Entradas*$ad->Costo;
                $totalsalidas=$ad->Salidas*$ad->Costo;
                if($totalentradas >= $totalsalidas){
                    $total = $totalentradas - $totalsalidas;
                }else{
                    $total = $totalsalidas - $totalentradas;
                }
                $datadetalle[]=array(
                    "codigodetalle"=>$ad->Codigo,
                    "descripciondetalle"=>$ad->Descripcion,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "existendetalle"=> Helpers::convertirvalorcorrecto($ad->Existencias),
                    "entradasdetalle" => Helpers::convertirvalorcorrecto($ad->Entradas),
                    "salidasdetalle" => Helpers::convertirvalorcorrecto($ad->Salidas),
                    "realdetalle" => Helpers::convertirvalorcorrecto($ad->Real),
                    "costodetalle" => Helpers::convertirvalorcorrecto($ad->Costo),
                    "totaldetalle" => Helpers::convertirvalorcorrecto($total)
                );
            } 
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'AjustesInventario')->where('Documento', $a->Ajuste)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'AjustesInventario')
            ->where('frd.Documento', $a->Ajuste)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "ajuste"=>$a,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "totalajuste"=>Helpers::convertirvalorcorrecto($a->Total),
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ajustesinventario.formato_pdf_ajustesinventario', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            $datosdocumento = AjusteInventario::where('Ajuste', $request->emaildocumento)->first();
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
                        ->attachData($pdf->output(), "AjusteInventarioNo".$emaildocumento.".pdf");
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

    public function ajustesinventario_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('AjustesInventario', Auth::user()->id);
        return Excel::download(new AjustesInventarioExport($configuraciones_tabla['campos_consulta'],$request->periodo), "ajustesinventario-".$request->periodo.".xlsx");   
    }

    //guardar configuracion tabla
    public function ajustesinventario_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('AjustesInventario', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'AjustesInventario')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='AjustesInventario';
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
        return redirect()->route('ajustesinventario');
    }
}
