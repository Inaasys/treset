<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use DB;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProduccionExport;
use App\Produccion;
use App\ProduccionDetalle;
use App\TipoOrdenCompra;
use App\Cliente;
use App\Almacen;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Producto;
use App\ProductoConsumo;
use App\BitacoraDocumento;
use App\Existencia;
use App\Marca;
use App\Configuracion_Tabla;
use App\VistaProduccion;
use App\VistaObtenerExistenciaProducto;
use App\Serie;
use App\Firma_Rel_Documento;
use Config;
use Mail;
use LynX39\LaraPdfMerger\Facades\PdfMerger;

class ProduccionController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }
    //vista
    public function produccion(){
        $serieusuario = 'A';
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Produccion', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('produccion_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('produccion_exportar_excel');
        $rutacreardocumento = route('produccion_generar_pdfs');
        return view('registros.produccion.produccion', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }
    //obtener registros tabla
    public function produccion_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Produccion', Auth::user()->id);
            $periodo = $request->periodo;
            $data = VistaProduccion::select($configuraciones_tabla['campos_consulta'])->where('Periodo', $periodo);
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
                    ->addColumn('operaciones', function($data){
                        $operaciones =  '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Produccion .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="producir(\''.$data->Produccion .'\')">Producir</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Produccion .'\')">Bajas</a></li>'.
                                                '<li><a href="'.route('produccion_generar_pdfs_indiv',$data->Produccion).'" target="_blank">Ver Documento PDF</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Produccion .'\')">Enviar Documento por Correo</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Cantidad', function($data){ return $data->Cantidad; })
                    ->addColumn('Costo', function($data){ return $data->Costo; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener series documento
    public function produccion_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'Produccion')->where('Usuario', Auth::user()->user)->get();
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
    public function produccion_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Produccion',$request->Serie);
        return response()->json($folio);
    }
    //obtener ultimo folio
    public function produccion_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Produccion',$request->serie);
        return response()->json($folio);
    }
    //obtener clientes
    public function produccion_obtener_clientes(Request $request){
        if($request->ajax()){
            $data = DB::table('Clientes as c')
            ->leftJoin('Agentes as a', 'a.Numero', '=', 'c.Agente')
            ->select('c.Numero', 'c.Nombre', 'c.Plazo', 'c.Rfc', 'c.Agente', 'c.Credito', 'c.Saldo', 'c.Status', 'c.Municipio', 'c.Tipo', 'a.Numero AS NumeroAgente', 'a.Nombre AS NombreAgente')
            ->where('c.Status', 'ALTA')
            ->orderBy("Numero", "ASC")
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .'\',\''.Helpers::convertirvalorcorrecto($data->Credito).'\',\''.Helpers::convertirvalorcorrecto($data->Saldo).'\',\''.$data->NumeroAgente .'\',\''.$data->NombreAgente .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener cliente por numero
    public function produccion_obtener_cliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $credito = '';
        $saldo = '';
        $numeroagente = '';
        $nombreagente = '';
        $existecliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'credito' => $credito,
            'saldo' => $saldo,
            'numeroagente' => $numeroagente,
            'nombreagente' => $nombreagente
        );
        return response()->json($data);
    }
    //obtener almacenes
    public function produccion_obtener_almacenes(Request $request){
        if($request->ajax()){
            $data = Almacen::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
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
    public function produccion_obtener_almacen_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existealmacen = Almacen::where('Numero', $request->numeroalmacen)->where('Status', 'ALTA')->count();
        if($existealmacen > 0){
            $almacen = Almacen::where('Numero', $request->numeroalmacen)->where('Status', 'ALTA')->first();
            $numero = $almacen->Numero;
            $nombre = $almacen->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }
    //obtener prudoctos
    public function produccion_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $numeroalmacen = $request->numeroalmacen;
            $tipooperacion = $request->tipooperacion;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')->where('Pt', 'S');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion, $numeroalmacen){
                        if($data->Almacen == $numeroalmacen){
                            $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarproducto(\''.$data->Codigo .'\')">Seleccionar</div>';
                        }else{
                            $boton = '';
                        }
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
    public function produccion_obtener_producto_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        $numeroalmacen = $request->numeroalmacen;
        $filasdetallesproduccion = '';
        $contadorproductos = 0;
        $contadorfilas = 0;
        $tipo = $request->tipooperacion;
        $productoterminado = "";
        $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('Almacen', $numeroalmacen)->where('Pt', 'S')->count();
        if($contarproductos > 0){
            $productoterminado = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('Almacen', $numeroalmacen)->where('Pt', 'S')->first();
            $insumosfabricacion =  ProductoConsumo::where('Codigo', $codigoabuscar)->get();
            foreach($insumosfabricacion as $if){
                $producto = Producto::where('Codigo', $if->Equivale)->first();
                $contarexistencia = Existencia::where('Codigo', $if->Equivale)->where('Almacen', $numeroalmacen)->count();
                if($contarexistencia > 0){
                    $Existencia = Existencia::where('Codigo', $if->Equivale)->where('Almacen', $numeroalmacen)->first();
                    $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
                }else{
                    $Existencias = Helpers::convertirvalorcorrecto(0);
                }
                $parsleymax = $Existencias;
                $filasdetallesproduccion= $filasdetallesproduccion.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipo.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$if->Equivale.'" readonly data-parsley-length="[1, 20]">'.$if->Equivale.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.htmlspecialchars($producto->Producto, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm unidadproductopartida" name="unidadproductopartida[]" value="'.$producto->Unidad.'" data-parsley-length="[1, 5]"></td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($if->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($if->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();">'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.                           
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm mermapartida" name="mermapartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm consumopartida" name="consumopartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm costounitariopartida" name="costounitariopartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod" hidden><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]" value="'.$if->Item.'" required></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesproduccion = '';
        }  
        $data = array(
            'productoterminado' => $productoterminado,
            'filasdetallesproduccion' => $filasdetallesproduccion,
            'contadorproductos' => $contadorproductos,
            'contadorfilas' => $contadorfilas,
            'contarproductos' => $contarproductos
        );
        return response()->json($data);
    }
    //obtener existencias
    public function produccion_obtener_existencias_almacen(Request $request){
        $ContarExistencia = Existencia::where('Codigo', $request->codigo)->where('Almacen', $request->numeroalmacen)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::where('Codigo', $request->codigo)->where('Almacen',$request->numeroalmacen)->first();
            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $Existencias = Helpers::convertirvalorcorrecto(0);
        }
        return response()->json($Existencias);
    }
    //obtener insumos para pt
    public function produccion_obtener_productos_insumos_pt(Request $request){
        if($request->ajax()){
            $codigoabuscarinsumo = $request->codigoabuscarinsumo;
            $numeroalmacen = $request->numeroalmacen;
            $tipooperacion = $request->tipooperacion;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscarinsumo . '%');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion, $numeroalmacen){
                        if($data->Almacen == $numeroalmacen){
                            $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
                        }else{
                            $boton = '';
                        }
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
    //obtener insumos para pt por codigo
    public function produccion_obtener_producto_insumo_pt_por_codigo(Request $request){
        $codigoabuscarinsumo = $request->codigoabuscarinsumo;
        $numeroalmacen = $request->numeroalmacen;
        $filainsumo = '';
        $contadorproductos = $request->contadorproductos;
        $contadorfilas = $request->contadorfilas;
        $tipo = "alta";
        $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscarinsumo)->where('Almacen', $numeroalmacen)->count();
        if($contarproductos > 0){
            $insumo = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscarinsumo)->where('Almacen', $numeroalmacen)->first();
            $Existencia = Existencia::where('Codigo', $insumo->Codigo)->where('Almacen', $numeroalmacen)->first();
            $parsleymax = $Existencia->Existencias;
            $filainsumo= $filainsumo.
            '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipo.'" readonly></td>'.
                '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$insumo->Codigo.'" readonly data-parsley-length="[1, 20]">'.$insumo->Codigo.'</td>'.
                '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.htmlspecialchars($insumo->Producto, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm unidadproductopartida" name="unidadproductopartida[]" value="'.$insumo->Unidad.'" data-parsley-length="[1, 5]"></td>'.
                '<td class="tdmod">'.
                    '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                    '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();">'.
                    '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.                           
                '</td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm mermapartida" name="mermapartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();"></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm consumopartida" name="consumopartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm costounitariopartida" name="costounitariopartida[]" value="'.Helpers::convertirvalorcorrecto($insumo->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();"></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '</tr>';
            $contadorproductos++;
            $contadorfilas++;
        }else{
            $filainsumo = '';
        } 
        $data = array(
            'insumo' => $insumo,
            'filainsumo' => $filainsumo,
            'contadorproductos' => $contadorproductos,
            'contadorfilas' => $contadorfilas,
            'contarproductos' => $contarproductos
        );
        return response()->json($data);
    }
    //altas
    public function produccion_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo folio de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\Produccion',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $produccion = $folio.'-'.$request->serie;
        $Produccion = new Produccion;
        $Produccion->Produccion=$produccion;
        $Produccion->Serie=$request->serie;
        $Produccion->Folio=$folio;
        $Produccion->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $Produccion->Cliente=$request->numerocliente;
        $Produccion->Codigo=$request->codigoabuscar;
        $Produccion->Almacen=$request->numeroalmacen;
        $Produccion->Cantidad=$request->cantidad;
        $Produccion->Costo=$request->costo;
        $Produccion->Total=$request->total;
        $Produccion->Obs=$request->observaciones;
        $Produccion->Status="PENDIENTE";
        $Produccion->Usuario=Auth::user()->user;
        $Produccion->Periodo=$this->periodohoy;
        $Produccion->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "PRODUCCION";
        $BitacoraDocumento->Movimiento = $produccion;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "PENDIENTE";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
            $ProduccionDetalle=new ProduccionDetalle;
            $ProduccionDetalle->Produccion = $produccion;
            $ProduccionDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $ProduccionDetalle->Codigo = $codigoproductopartida;
            $ProduccionDetalle->Descripcion = $request->descripcionproductopartida [$key];
            $ProduccionDetalle->Unidad = $request->unidadproductopartida [$key];
            $ProduccionDetalle->Cantidad =  $request->cantidadpartida [$key];
            $ProduccionDetalle->Merma =  $request->mermapartida [$key];
            $ProduccionDetalle->Consumo =  $request->consumopartida [$key];
            $ProduccionDetalle->Costo =  $request->costounitariopartida [$key];
            $ProduccionDetalle->Total =  $request->costototalpartida  [$key];
            $ProduccionDetalle->Partida =  $request->partidapartida [$key];
            $ProduccionDetalle->Item = $item;
            $ProduccionDetalle->Periodo =  $this->periodohoy;
            $ProduccionDetalle->save();
            $item++;
        }
        return response()->json($Produccion);
    }
    //verificar existencias insumos produccion
    public function produccion_verificar_existencias_insumos_produccion(Request $request){
        $produccion = Produccion::where('Produccion', $request->produccionproducir)->first();
        $produccion_detalles = ProduccionDetalle::where('Produccion', $request->produccionproducir)->get();
        $errores = '';
        foreach($produccion_detalles as $pd){
            $cantidad_insumo_utilizar = $produccion->Cantidad * $pd->Cantidad;
            $ContarExistencia = Existencia::where('Codigo', $pd->Codigo)->where('Almacen', $produccion->Almacen)->count();
            if($ContarExistencia > 0){
                $Existencia = Existencia::where('Codigo', $pd->Codigo)->where('Almacen',$produccion->Almacen)->first();
                $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
            }else{
                $Existencias = Helpers::convertirvalorcorrecto(0);
            }
            if($Existencias < Helpers::convertirvalorcorrecto($cantidad_insumo_utilizar)){
                $errores = $errores.'Error la producción no se puede realizar porque no hay existencias suficientes en el almacén:'.$produccion->Almacen.' del Codigo:'.$pd->Codigo.' Existencias a utilizar:'.Helpers::convertirvalorcorrecto($cantidad_insumo_utilizar).' Existencias actuales en almacén:'.$Existencias.'<br><br>';

            }
        }
        $resultadofechas = Helpers::compararanoymesfechas($produccion->Fecha);
        $data = array(
            'produccion' => $produccion,
            'resultadofechas' => $resultadofechas,
            'errores' => $errores
        );
        return response()->json($data);
    }
    //realizar produccion
    public function produccion_realizar_produccion(Request $request){
        $Produccion = Produccion::where('Produccion', $request->produccionproducir)->first();
        //cambiar status
        Produccion::where('Produccion', $request->produccionproducir)
                ->update([
                    'Status' => 'PRODUCIDO',
                    'Producido' => Helpers::fecha_exacta_accion_datetimestring()
                ]);
        $produccion_detalles = ProduccionDetalle::where('Produccion', $request->produccionproducir)->get();
        //restar existencias de insumos en almacen
        foreach($produccion_detalles as $detalle){
            //restar existencias insumos al almacen
            $cantidad_insumo_utilizar = $Produccion->Cantidad * $detalle->Cantidad;
            $ExistenciaAlmacen = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Produccion->Almacen)->first();
            $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias-$cantidad_insumo_utilizar;
            Existencia::where('Codigo', $detalle->Codigo)
                        ->where('Almacen', $Produccion->Almacen)
                        ->update([
                            'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                        ]);
        }
        //sumar existencias de PT en Almacen
        $ExistenciaAlmacen = Existencia::where('Codigo', $Produccion->Codigo)->where('Almacen', $Produccion->Almacen)->first();
        $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias+$Produccion->Cantidad;
        Existencia::where('Codigo', $Produccion->Codigo)
                    ->where('Almacen', $Produccion->Almacen)
                    ->update([
                        'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                    ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "PRODUCCION";
        $BitacoraDocumento->Movimiento = $request->produccionproducir;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "PRODUCIDO";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($Produccion);
    }
    //verificar si continua baja
    public function produccion_verificar_baja(Request $request){
        $produccion = Produccion::where('Produccion', $request->producciondesactivar)->first();
        $produccion_detalles = ProduccionDetalle::where('Produccion', $request->producciondesactivar)->get();
        $errores = '';
        $ContarExistencia = Existencia::where('Codigo', $produccion->Codigo)->where('Almacen', $produccion->Almacen)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::where('Codigo', $produccion->Codigo)->where('Almacen',$produccion->Almacen)->first();
            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $Existencias = Helpers::convertirvalorcorrecto(0);
        }
        if($Existencias < Helpers::convertirvalorcorrecto($produccion->Cantidad)){
            $errores = $errores.'Error la producción no se puede dar de baja porque no hay existencias suficientes del PT en el almacén:'.$produccion->Almacen.' del Codigo:'.$produccion->Codigo.' Existencias a regresar:'.Helpers::convertirvalorcorrecto($produccion->Cantidad).' Existencias actuales en almacén:'.$Existencias.'<br><br>';
        }
        $resultadofechas = Helpers::compararanoymesfechas($produccion->Fecha);
        $data = array(
            'produccion' => $produccion,
            'resultadofechas' => $resultadofechas,
            'errores' => $errores
        );
        return response()->json($data);
    }
    //bajas
    public function produccion_alta_o_baja(Request $request){
        $Produccion = Produccion::where('Produccion', $request->producciondesactivar)->first();
        //restar existencias PT al almacen
        $ExistenciaAlmacen = Existencia::where('Codigo', $Produccion->Codigo)->where('Almacen', $Produccion->Almacen)->first();
        $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias-$Produccion->Cantidad;
        Existencia::where('Codigo', $Produccion->Codigo)
                    ->where('Almacen', $Produccion->Almacen)
                    ->update([
                        'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                    ]);
        //detalles
        $produccion_detalles = ProduccionDetalle::where('Produccion', $request->producciondesactivar)->get();
        foreach($produccion_detalles as $detalle){
            //sumar existencias al almacen
            $cantidad_insumo_utilizar = $Produccion->Cantidad * $detalle->Cantidad;
            $ExistenciaAlmacen = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Produccion->Almacen)->first();
            $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias+$cantidad_insumo_utilizar;
            Existencia::where('Codigo', $detalle->Codigo)
                        ->where('Almacen', $Produccion->Almacen)
                        ->update([
                            'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                        ]);
            //colocar en ceros cantidades
            ProduccionDetalle::where('Produccion', $request->producciondesactivar)
                            ->where('Item', $detalle->Item)
                            ->update([
                                'Cantidad' => '0.000000',
                                'Merma' => '0.000000',
                                'Consumo' => '0.000000',
                                'Total' => '0.000000'
                            ]);
        }
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        Produccion::where('Produccion', $request->producciondesactivar)
                ->update([
                    'MotivoDeBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Cantidad' => '0.000000',
                    'Total' => '0.000000'
                ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "PRODUCCION";
        $BitacoraDocumento->Movimiento = $request->producciondesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Produccion->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($Produccion);
    }
    //obtener datos
    public function produccion_obtener_produccion(Request $request){
        $produccion = Produccion::where('Produccion', $request->produccionmodificar)->first();
        $cliente = Cliente::where('Numero', $produccion->Cliente)->first();
        $almacen = Almacen::where('Numero', $produccion->Almacen)->first();
        //detalles
        $detallesproduccion= ProduccionDetalle::where('Produccion', $request->produccionmodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallesproduccion = ProduccionDetalle::where('Produccion', $request->produccionmodificar)->count();
        if($numerodetallesproduccion > 0){
            $filasdetallesproduccion = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo="modificacion";
            foreach($detallesproduccion as $dp){
                $producto = Producto::where('Codigo', $dp->Codigo)->first();
                $contarexistencia = Existencia::where('Codigo', $dp->Codigo)->where('Almacen', $produccion->Almacen)->count();
                if($contarexistencia > 0){
                    $Existencia = Existencia::where('Codigo', $dp->Codigo)->where('Almacen',$produccion->Almacen)->first();
                    $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
                }else{
                    $Existencias = Helpers::convertirvalorcorrecto(0);
                }
                $parsleymax = $Existencias;
                $filasdetallesproduccion= $filasdetallesproduccion.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dp->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dp->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dp->Codigo.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.htmlspecialchars($dp->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm unidadproductopartida" name="unidadproductopartida[]" value="'.$dp->Unidad.'" data-parsley-length="[1, 5]"></td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dp->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dp->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();">'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.                           
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm mermapartida" name="mermapartida[]" value="'.Helpers::convertirvalorcorrecto($dp->Merma).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm consumopartida" name="consumopartida[]" value="'.Helpers::convertirvalorcorrecto($dp->Consumo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm costounitariopartida" name="costounitariopartida[]" value="'.Helpers::convertirvalorcorrecto($dp->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dp->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod" hidden><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]" value="'.$dp->Partida.'" required></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesproduccion = '';
        }      
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($produccion->Status != 'PENDIENTE'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($produccion->Status != 'PENDIENTE'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($produccion->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }  
        $data = array(
            "produccion" => $produccion,
            "cliente" => $cliente,
            "almacen" => $almacen,
            "filasdetallesproduccion" => $filasdetallesproduccion,
            "numerodetallesproduccion" => $numerodetallesproduccion,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdatetime($produccion->Fecha),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($produccion->Fecha),
            "cantidad" => Helpers::convertirvalorcorrecto($produccion->Cantidad),
            "total" => Helpers::convertirvalorcorrecto($produccion->Total),
            "costo" => Helpers::convertirvalorcorrecto($produccion->Costo),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);

    }
    //cambios
    public function produccion_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $produccion = $request->folio.'-'.$request->serie;
        $Produccion = Produccion::where('Produccion', $produccion)->first();
        //validar si las partidas en las modiifcacion son las mismas que los detalles
        // si no son las mismas comparar y eliminar las partidas que se quitaron en la modificacion
        //array partidas antes de modificacion
        $ArrayDetallesProduccionAnterior = Array();
        $DetallesProduccionAnterior = ProduccionDetalle::where('Produccion', $produccion)->get();
        foreach($DetallesProduccionAnterior as $detalle){
            array_push($ArrayDetallesProduccionAnterior, $detalle->Produccion.'#'.$detalle->Codigo.'#'.$detalle->Item);
        }
        //array partida despues de modificacion
        $ArrayDetallesProduccionNuevo = Array();
        foreach ($request->codigoproductopartida as $key => $nuevocodigo){
            if($request->agregadoen [$key] == 'NA'){
                array_push($ArrayDetallesProduccionNuevo, $produccion.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
            } 
        }  
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesProduccionAnterior, $ArrayDetallesProduccionNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                $detalleproduccion = ProduccionDetalle::where('Produccion', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->first();
                //eliminar detalle de la produccion eliminado
                $eliminardetalleproduccion = ProduccionDetalle::where('Produccion', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
            }
        }
        //modificar produccion
        Produccion::where('Produccion', $produccion)
        ->update([
            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Cliente' => $request->numerocliente,
            'Codigo' => $request->codigoabuscar,
            'Almacen' => $request->numeroalmacen,
            'Cantidad' => $request->cantidad,
            'Costo' => $request->costo,
            'Total' => $request->total,
            'Obs' => $request->observaciones
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "PRODUCCION";
        $BitacoraDocumento->Movimiento = $produccion;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Produccion->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){    
            //if la partida se agrego en la modificacion se agrega en los detalles
            if($request->agregadoen [$key] == 'modificacion'){
                $contaritems = ProduccionDetalle::select('Item')->where('Produccion', $produccion)->count();
                if($contaritems > 0){
                    $item = ProduccionDetalle::select('Item')->where('Produccion', $produccion)->orderBy('Item', 'DESC')->take(1)->get();
                    $ultimoitem = $item[0]->Item+1;
                }else{
                    $ultimoitem = 1;
                }
                $ProduccionDetalle=new ProduccionDetalle;
                $ProduccionDetalle->Produccion = $produccion;
                $ProduccionDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $ProduccionDetalle->Codigo = $codigoproductopartida;
                $ProduccionDetalle->Descripcion = $request->descripcionproductopartida [$key];
                $ProduccionDetalle->Unidad = $request->unidadproductopartida [$key];
                $ProduccionDetalle->Cantidad =  $request->cantidadpartida [$key];
                $ProduccionDetalle->Merma =  $request->mermapartida [$key];
                $ProduccionDetalle->Consumo =  $request->consumopartida [$key];
                $ProduccionDetalle->Costo =  $request->costounitariopartida [$key];
                $ProduccionDetalle->Total =  $request->costototalpartida  [$key];
                $ProduccionDetalle->Partida =  $request->partidapartida [$key];
                $ProduccionDetalle->Item = $ultimoitem;
                $ProduccionDetalle->Periodo =  $this->periodohoy;
                $ProduccionDetalle->save();
                $ultimoitem++;
            }else{
                //si la partida no se agrego en la modificacion solo se modifican los datos
                //modificar detalle
                ProduccionDetalle::where('Produccion', $produccion)
                ->where('Item', $request->itempartida [$key])
                ->update([
                    'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                    'Descripcion' => $request->descripcionproductopartida [$key],
                    'Unidad' => $request->unidadproductopartida [$key],
                    'Cantidad' =>  $request->cantidadpartida [$key],
                    'Merma' =>  $request->mermapartida [$key],
                    'Consumo' =>  $request->consumopartida [$key],
                    'Costo' =>  $request->costounitariopartida [$key],
                    'Total' =>  $request->costototalpartida  [$key]
                ]);
            }
        }
        return response()->json($Produccion);
    }
    //buscar folio por like
    public function produccion_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = VistaProduccion::orderBy('Folio', 'ASC')->get();
            return DataTables::of($data)
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->make(true);
        } 
    }
    //generar pdfs
    public function produccion_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $producciones = Produccion::whereIn('Produccion', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $producciones = Produccion::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        foreach ($producciones as $p){
            $data=array();
            $producciondetalle = ProduccionDetalle::where('Produccion', $p->Produccion)->get();
            $datadetalle=array();
            foreach($producciondetalle as $pd){
                $producto = Producto::where('Codigo', $pd->Codigo)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($pd->Cantidad),
                    "codigodetalle"=>$pd->Codigo,
                    "descripciondetalle"=>$pd->Descripcion,
                    "unidaddetalle"=>$pd->Unidad,
                    "mermadetalle" => Helpers::convertirvalorcorrecto($pd->Merma),
                    "consumodetalle" => Helpers::convertirvalorcorrecto($pd->Consumo),
                    "costodetalle" => Helpers::convertirvalorcorrecto($pd->Costo),
                    "costototaldetalle" => Helpers::convertirvalorcorrecto($pd->Total)
                );
            } 
            $nombrecliente = "";
            $contarcliente = Cliente::where('Numero', $p->Cliente)->count();
            if($contarcliente > 0){
                $cliente = Cliente::where('Numero', $p->Cliente)->first();
                $nombrecliente = $cliente->Nombre;
            }
            $nombrealmacen = "";
            $contaralmacen = Almacen::where('Numero', $p->Almacen)->count();
            if($contaralmacen > 0){
                $almacen = Almacen::where('Numero', $p->Almacen)->first();
                $nombrealmacen = $almacen->Nombre;
            }
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'Produccion')->where('Documento', $p->Produccion)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'Produccion')
            ->where('frd.Documento', $p->Produccion)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "produccion"=>$p,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "cantidadproduccion"=>Helpers::convertirvalorcorrecto($p->Cantidad),
                      "costoproduccion"=>Helpers::convertirvalorcorrecto($p->Costo),
                      "totalproduccion"=>Helpers::convertirvalorcorrecto($p->Total),
                      "nombrecliente" => $nombrecliente,
                      "nombrealmacen" => $nombrealmacen,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.produccion.formato_pdf_producciones', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$p->Produccion.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($producciones as $pro){
            $ArchivoPDF = "PDF".$pro->Produccion.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
        }
        $pdfMerger->merge(); //unirlos
        $pdfMerger->save("Producciones.pdf", "browser");//mostrarlos en el navegador
    }
    //generar pef por folio
    public function produccion_generar_pdfs_indiv($documento){
        $producciones = Produccion::where('Produccion', $documento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($producciones as $p){
            $producciondetalle = ProduccionDetalle::where('Produccion', $p->Produccion)->get();
            $datadetalle=array();
            foreach($producciondetalle as $pd){
                $producto = Producto::where('Codigo', $pd->Codigo)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($pd->Cantidad),
                    "codigodetalle"=>$pd->Codigo,
                    "descripciondetalle"=>$pd->Descripcion,
                    "unidaddetalle"=>$pd->Unidad,
                    "mermadetalle" => Helpers::convertirvalorcorrecto($pd->Merma),
                    "consumodetalle" => Helpers::convertirvalorcorrecto($pd->Consumo),
                    "costodetalle" => Helpers::convertirvalorcorrecto($pd->Costo),
                    "costototaldetalle" => Helpers::convertirvalorcorrecto($pd->Total)
                );
            } 
            $nombrecliente = "";
            $contarcliente = Cliente::where('Numero', $p->Cliente)->count();
            if($contarcliente > 0){
                $cliente = Cliente::where('Numero', $p->Cliente)->first();
                $nombrecliente = $cliente->Nombre;
            }
            $nombrealmacen = "";
            $contaralmacen = Almacen::where('Numero', $p->Almacen)->count();
            if($contaralmacen > 0){
                $almacen = Almacen::where('Numero', $p->Almacen)->first();
                $nombrealmacen = $almacen->Nombre;
            }
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'Produccion')->where('Documento', $p->Produccion)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'Produccion')
            ->where('frd.Documento', $p->Produccion)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "produccion"=>$p,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "cantidadproduccion"=>Helpers::convertirvalorcorrecto($p->Cantidad),
                      "costoproduccion"=>Helpers::convertirvalorcorrecto($p->Costo),
                      "totalproduccion"=>Helpers::convertirvalorcorrecto($p->Total),
                      "nombrecliente" => $nombrecliente,
                      "nombrealmacen" => $nombrealmacen,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.produccion.formato_pdf_producciones', compact('data'))
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
    public function produccion_obtener_datos_envio_email(Request $request){
        $produccion = Produccion::where('Produccion', $request->documento)->first();
        $data = array(
            'produccion' => $produccion,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => "",
            'email2cc' => "",
            'email3cc' => ""
        );
        return response()->json($data);
    }
    //enviar pdf por emial
    public function produccion_enviar_pdfs_email(Request $request){
        $producciones = Produccion::where('Produccion', $request->emaildocumento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($producciones as $p){
            $producciondetalle = ProduccionDetalle::where('Produccion', $p->Produccion)->get();
            $datadetalle=array();
            foreach($producciondetalle as $pd){
                $producto = Producto::where('Codigo', $pd->Codigo)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($pd->Cantidad),
                    "codigodetalle"=>$pd->Codigo,
                    "descripciondetalle"=>$pd->Descripcion,
                    "unidaddetalle"=>$pd->Unidad,
                    "mermadetalle" => Helpers::convertirvalorcorrecto($pd->Merma),
                    "consumodetalle" => Helpers::convertirvalorcorrecto($pd->Consumo),
                    "costodetalle" => Helpers::convertirvalorcorrecto($pd->Costo),
                    "costototaldetalle" => Helpers::convertirvalorcorrecto($pd->Total)
                );
            } 
            $nombrecliente = "";
            $contarcliente = Cliente::where('Numero', $p->Cliente)->count();
            if($contarcliente > 0){
                $cliente = Cliente::where('Numero', $p->Cliente)->first();
                $nombrecliente = $cliente->Nombre;
            }
            $nombrealmacen = "";
            $contaralmacen = Almacen::where('Numero', $p->Almacen)->count();
            if($contaralmacen > 0){
                $almacen = Almacen::where('Numero', $p->Almacen)->first();
                $nombrealmacen = $almacen->Nombre;
            }
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'Produccion')->where('Documento', $p->Produccion)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'Produccion')
            ->where('frd.Documento', $p->Produccion)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "produccion"=>$p,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "cantidadproduccion"=>Helpers::convertirvalorcorrecto($p->Cantidad),
                      "costoproduccion"=>Helpers::convertirvalorcorrecto($p->Costo),
                      "totalproduccion"=>Helpers::convertirvalorcorrecto($p->Total),
                      "nombrecliente" => $nombrecliente,
                      "nombrealmacen" => $nombrealmacen,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.produccion.formato_pdf_producciones', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            $datosdocumento = Produccion::where('Produccion', $request->emaildocumento)->first();
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
            if($this->correodefault1enviodocumentos != ""){
                array_push($arraycc, $this->correodefault1enviodocumentos);
            }
            if($this->correodefault2enviodocumentos != ""){
                array_push($arraycc, $this->correodefault2enviodocumentos);
            }
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailasunto;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $correos, $asunto, $pdf, $emaildocumento) {
                $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                        ->cc($arraycc)
                        ->subject($asunto)
                        ->attachData($pdf->output(), "ProducciónNo".$emaildocumento.".pdf");
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
    //exportar a excel
    public function produccion_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Produccion', Auth::user()->id);
        return Excel::download(new ProduccionExport($configuraciones_tabla['campos_consulta'],$request->periodo), "produccion-".$request->periodo.".xlsx");   

    }
    //configuracion tabla
    public function produccion_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Produccion', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'Produccion')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='Produccion';
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
        return redirect()->route('produccion');
    }
    
}
