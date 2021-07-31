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
use App\Exports\AsignacionHerramientaExport;
use App\Configuracion_Tabla;
use App\Asignacion_Herramienta;
use App\Asignacion_Herramienta_Detalle;
use App\Prestamo_Herramienta_Detalle;
use App\Personal;
use App\BitacoraDocumento;
use App\VistaAsignacionHerramienta;
use App\VistaObtenerExistenciaProducto;
use App\Producto;
use App\Existencia;
use App\Almacen;
use App\Serie;

class AsignacionHerramientaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'asignacion_herramientas')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //campos vista
        $this->camposvista = [];
        foreach (explode(",", $this->configuracion_tabla->campos_activados) as $campo){
            array_push($this->camposvista, $campo);
        }
        foreach (explode(",", $this->configuracion_tabla->campos_desactivados) as $campo){
            array_push($this->camposvista, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }
    public function asignacionherramienta(){
        $serieusuario = 'A';
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('asignacion_herramienta_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('asignacion_herramienta_exportar_excel');
        $rutacreardocumento = route('asignacion_herramienta_generar_pdfs');
        return view('registros.asignacionherramienta.asignacionherramienta', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }
    //obtener las asignaciones de herramienta
    public function asignacion_herramienta_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            //$data = VistaAsignacionHerramienta::select($this->campos_consulta)->where('periodo', $periodo)->orderBy('fecha', 'DESC')->orderBy('serie', 'ASC')->orderBy('folio', 'DESC')->get();
            $data = VistaAsignacionHerramienta::select($this->campos_consulta)->where('periodo', $periodo);
            return DataTables::of($data)
                    ->order(function ($query){
                        if($this->configuracion_tabla->primerordenamiento != 'omitir'){
                            $query->orderBy($this->configuracion_tabla->primerordenamiento, '' . $this->configuracion_tabla->formaprimerordenamiento . '');
                        }
                        if($this->configuracion_tabla->segundoordenamiento != 'omitir'){
                            $query->orderBy($this->configuracion_tabla->segundoordenamiento, '' . $this->configuracion_tabla->formasegundoordenamiento . '');
                        }
                        if($this->configuracion_tabla->tercerordenamiento != 'omitir'){
                            $query->orderBy($this->configuracion_tabla->tercerordenamiento, '' . $this->configuracion_tabla->formatercerordenamiento . '');
                        }
                    })
                    ->addColumn('operaciones', function($data) use ($tipousuariologueado){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->asignacion .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->asignacion .'\')">Bajas</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('fecha', function($data){ return Carbon::parse($data->fecha)->toDateTimeString(); })
                    ->addColumn('total', function($data){ return $data->total; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener series documento
    public function asignacion_herramienta_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'asignacion_herramientas')->where('Usuario', Auth::user()->user)->get();
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
    public function asignacion_herramienta_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserieregistrotabla('App\Asignacion_Herramienta',$request->serie);
        return response()->json($folio);
    }
    public function asignacion_herramienta_obtener_ultimo_id(Request $request){
        $folio = Helpers::ultimofolioserieregistrotabla('App\Asignacion_Herramienta',$request->serie);
        return response()->json($folio);
    }
    //obtener personal que recibe herramienta
    public function asignacion_herramienta_obtener_personal_recibe(Request $request){
        if($request->ajax()){
            $data = Personal::where('status', 'ALTA')->where('id', '<>', $request->numeropersonalentrega)->orderBy("id", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarpersonalrecibe('.$data->id.',\''.$data->nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener persona recibe por numero
    public function  asignacion_herramienta_obtener_personal_recibe_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existepersonal = Personal::where('id', $request->numeropersonalrecibe)->where('id', '<>', $request->numeropersonalentrega)->where('Status', 'ALTA')->count();
        if($existepersonal > 0){
            $personal = Personal::where('id', $request->numeropersonalrecibe)->where('id', '<>', $request->numeropersonalentrega)->where('Status', 'ALTA')->first();
            $numero = $personal->id;
            $nombre = $personal->nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data); 
    }

    //obtener personal que entrega herramienta
    public function asignacion_herramienta_obtener_personal_entrega(Request $request){
        if($request->ajax()){
            $data = Personal::where('status', 'ALTA')->where('id', '<>', $request->numeropersonalrecibe)->orderBy("id", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarpersonalentrega('.$data->id.',\''.$data->nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener personal entrega por numero
    public function asignacion_herramienta_obtener_personal_entrega_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existepersonal = Personal::where('id', $request->numeropersonalentrega)->where('id', '<>', $request->numeropersonalrecibe)->where('Status', 'ALTA')->count();
        if($existepersonal > 0){
            $personal = Personal::where('id', $request->numeropersonalentrega)->where('id', '<>', $request->numeropersonalrecibe)->where('Status', 'ALTA')->first();
            $numero = $personal->id;
            $nombre = $personal->nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data); 
    }

    //obtener herramienta
    public function asignacion_herramienta_obtener_herramienta(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $tipooperacion = $request->tipooperacion;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%');
            $almacenes = Almacen::where('status', 'ALTA')->get();
            $selectalmacenes = "<option selected disabled hidden>Selecciona el almacén</option>";
            foreach($almacenes as $a){
                    $selectalmacenes = $selectalmacenes.'<option value='.$a->Numero.'>'.$a->Nombre;
            }
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($selectalmacenes, $tipooperacion){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaherramienta(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.$selectalmacenes.'\',\''.$tipooperacion.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Costo', function($data){ 
                        return Helpers::convertirvalorcorrecto($data->Costo);
                    })
                    ->addColumn('Existencias', function($data){ 
                        return Helpers::convertirvalorcorrecto($data->Existencias);
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener herramienta por codigo
    public function asignacion_herramienta_obtener_herramienta_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->count();
        if($contarproductos > 0){
            $almacenes = Almacen::where('status', 'ALTA')->get();
            $selectalmacenes = "<option selected disabled hidden>Selecciona el almacén</option>";
            foreach($almacenes as $a){
                    $selectalmacenes = $selectalmacenes.'<option value='.$a->Numero.'>'.$a->Nombre;
            }
            $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->first();
            $data = array(
                'Codigo' => $producto->Codigo,
                'Producto' => htmlspecialchars($producto->Producto, ENT_QUOTES),
                'Unidad' => $producto->Unidad,
                'Costo' => Helpers::convertirvalorcorrecto($producto->Costo),
                'Existencias' => Helpers::convertirvalorcorrecto($producto->Existencias),
                'contarproductos' => $contarproductos,
                'selectalmacenes' => $selectalmacenes
            );
        }else{
            $data = array(
                'Codigo' => '',
                'Producto' => '',
                'Unidad' => '',
                'Costo' => '',
                'Existencias' => '',
                'contarproductos' => $contarproductos,
                'selectalmacenes' => ''
            );
        }
        return response()->json($data);  
    }
    //obtener existencia en almacen
    public function asignacion_herramienta_obtener_existencias_almacen(Request $request){
        $Existencia = Existencia::where('Codigo', $request->codigoproductopartida)->where('Almacen', $request->almacenpartida)->first();
        $existenciasactuales = 0;
        if($Existencia != NULL){
            $existenciasactuales = $Existencia->Existencias;
        }
        return response()->json(Helpers::convertirvalorcorrecto($existenciasactuales));
    }
    //guardar regustro
    public function asignacion_herramienta_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo id de la tabla
        DB::unprepared('SET IDENTITY_INSERT asignacion_herramientas ON');
        $id = Helpers::ultimoidregistrotabla('App\Asignacion_Herramienta');
        $folio = Helpers::ultimofolioserieregistrotabla('App\Asignacion_Herramienta',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $asignacion = $folio.'-'.$request->serie;
		$Asignacion_Herramienta = new Asignacion_Herramienta;
		$Asignacion_Herramienta->id=$id;
        $Asignacion_Herramienta->folio=$folio;
        $Asignacion_Herramienta->asignacion=$asignacion;
		$Asignacion_Herramienta->serie=$request->serie;
        $Asignacion_Herramienta->fecha=Carbon::parse($request->fecha)->toDateTimeString();
		$Asignacion_Herramienta->recibe_herramienta=$request->numeropersonalrecibe;
		$Asignacion_Herramienta->entrega_herramienta=$request->numeropersonalentrega;
		$Asignacion_Herramienta->total=$request->total;
        $Asignacion_Herramienta->observaciones=$request->observaciones;
        $Asignacion_Herramienta->status="ALTA";
        $Asignacion_Herramienta->usuario=Auth::user()->user;
        $Asignacion_Herramienta->periodo=$this->periodohoy;
        $Asignacion_Herramienta->save();
        DB::unprepared('SET IDENTITY_INSERT asignacion_herramientas OFF');
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ASIGNACION DE HERRAMIENTA";
        $BitacoraDocumento->Movimiento = $asignacion;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){   
            DB::unprepared('SET IDENTITY_INSERT asignacion_herramientas_detalles ON');
            $iddetalle = Helpers::ultimoidregistrotabla('App\Asignacion_Herramienta_Detalle');          
            $Asignacion_Herramienta_Detalle=new Asignacion_Herramienta_Detalle;
            $Asignacion_Herramienta_Detalle->id = $iddetalle;
            $Asignacion_Herramienta_Detalle->id_asignacion_herramienta = $id;
            $Asignacion_Herramienta_Detalle->asignacion = $asignacion;
            $Asignacion_Herramienta_Detalle->fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $Asignacion_Herramienta_Detalle->herramienta = $codigoproductopartida;
            $Asignacion_Herramienta_Detalle->descripcion = $request->nombreproductopartida [$key];
            $Asignacion_Herramienta_Detalle->unidad = $request->unidadproductopartida [$key];
            $Asignacion_Herramienta_Detalle->cantidad =  $request->cantidadpartida  [$key];
            $Asignacion_Herramienta_Detalle->precio =  $request->preciopartida [$key];
            $Asignacion_Herramienta_Detalle->total = $request->totalpesospartida [$key];
            $Asignacion_Herramienta_Detalle->estado_herramienta = $request->estadopartida  [$key];
            $Asignacion_Herramienta_Detalle->item = $item;
            $Asignacion_Herramienta_Detalle->id_almacen = $request->almacenpartida [$key];
            $Asignacion_Herramienta_Detalle->save();
            $item++;
            DB::unprepared('SET IDENTITY_INSERT asignacion_herramientas_detalles OFF');
            //restar existencias a almacen principal
            $RestarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->almacenpartida [$key])->first();
            $RestarExistenciaNuevaAlmacen = $RestarExistenciaAlmacen->Existencias - $request->cantidadpartida  [$key];
            Existencia::where('Codigo', $codigoproductopartida)
                        ->where('Almacen', $request->almacenpartida [$key])
                        ->update([
                            'Existencias' => Helpers::convertirvalorcorrecto($RestarExistenciaNuevaAlmacen)
                        ]);

        }
    	return response()->json($Asignacion_Herramienta);
    }
    //buscar string like id
    public function asignacion_herramienta_buscar_id_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = VistaAsignacionHerramienta::where('asignacion', 'like', '%' . $string . '%')->orderBy('id', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->asignacion .'\')"><i class="material-icons">done</i></div> ';
                    return $boton;
                })
                ->addColumn('Total', function($data){
                    return Helpers::convertirvalorcorrecto($data->total);
                })
                ->rawColumns(['operaciones'])
                ->make(true);
        } 
    }
    //generar documento
    public function asignacion_herramienta_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $asignacionesherramientas = VistaAsignacionHerramienta::whereIn('asignacion', $request->arraypdf)->orderBy('id', 'ASC')->take(500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $asignacionesherramientas = VistaAsignacionHerramienta::whereBetween('fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('id', 'ASC')->take(500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($asignacionesherramientas as $ah){
            $asignacionherramientadetalle = Asignacion_Herramienta_Detalle::where('asignacion', $ah->asignacion)->get();
            $datadetalle=array();
            foreach($asignacionherramientadetalle as $ahd){
                $producto = Producto::where('Codigo', $ahd->herramienta)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($ahd->cantidad),
                    "herramientadetalle"=>$ahd->herramienta,
                    "descripciondetalle"=>$ahd->descripcion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($ahd->precio),
                    "totaldetalle" => Helpers::convertirvalorcorrecto($ahd->total),
                    "estadodetalle" => $ahd->estado_herramienta
                );
            } 
            $data[]=array(
                      "asignacion"=>$ah,
                      "totalasignacion"=>Helpers::convertirvalorcorrecto($ah->total),
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.asignacionherramienta.formato_pdf_asignacion_herramienta', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 5)
        ->setOption('margin-right', 5)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }
    //guardar configuracion tabla
    public function asignacion_herramienta_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        Configuracion_Tabla::where('tabla', 'asignacion_herramientas')
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
        return redirect()->route('asignacionherramienta');
    }
    //exportar excel
    public function asignacion_herramienta_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new AsignacionHerramientaExport($this->campos_consulta,$request->periodo), "asignacionherramienta-".$request->periodo.".xlsx");   

    }
    //obtener asignacion a autorizar
    public function asignacion_herramienta_obtener_asignacion_herramienta_a_autorizar(Request $request){
        $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $request->asignacion)->get();
        $filasdetallesasignacion='';
        foreach($Asignacion_Herramienta_Detalle as $ahd){
            $filasdetallesasignacion= $filasdetallesasignacion.
            '<tr>'.
                '<td class="tdmod">'.$ahd->herramienta.'</td>'.
                '<td class="tdmod">'.$ahd->cantidad.'</td>'.
                '<td class="tdmod">'.$ahd->id_almacen.'</td>'.
            '</tr>';
        }
        return response()->json($filasdetallesasignacion);
    }
    //autorizar asignacion herramienta
    public function asignacion_herramienta_autorizar(Request $request){
        $Asignacion_Herramienta = Asignacion_Herramienta::where('asignacion', $request->asignacionautorizar)->first();
        $Asignacion_Herramienta->autorizado_por = Auth::user()->user; 
        $Asignacion_Herramienta->fecha_autorizacion = Helpers::fecha_exacta_accion_datetimestring();
        $Asignacion_Herramienta->save();
        //restar la cantidad asignada al codigo en la tabla de existencias
        $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $request->asignacionautorizar)->get();
        $existencias = array();
        foreach($Asignacion_Herramienta_Detalle as $detalle){
            $Existencia = Existencia::where('Codigo', $detalle->herramienta)->where('Almacen', $detalle->id_almacen)->first();
            $existenciasactuales = $Existencia->Existencias - $detalle->cantidad;
            Existencia::where('Almacen', $detalle->id_almacen)->where('Codigo', $detalle->herramienta)->update(['Existencias' => $existenciasactuales]);
        }
        return response()->json($Asignacion_Herramienta);
    }
    //obtener datos asignacion herramienta seleccionada
    public function asignacion_herramienta_obtener_asignacion_herramienta(Request $request){
        $Asignacion_Herramienta = Asignacion_Herramienta::where('asignacion', $request->asignacionmodificar)->first();
        $personalrecibe = Personal::where('id', $Asignacion_Herramienta->recibe_herramienta)->first();
        $personalentrega = Personal::where('id', $Asignacion_Herramienta->entrega_herramienta)->first();
        //detalles orden compra
        $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $request->asignacionmodificar)->get();
        $Numero_Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $request->asignacionmodificar)->count();
        if($Numero_Asignacion_Herramienta_Detalle > 0){
            $filasdetallesasignacion = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            foreach($Asignacion_Herramienta_Detalle as $ahd){
                if($ahd->estado_herramienta == 'Nuevo'){
                    $opciones = '<option value="Nuevo" selected>Nuevo</option>'.
                                '<option value="Usado">Usado</option>';
                }else{
                    $opciones = '<option value="Nuevo">Nuevo</option>'.
                    '<option value="Usado" selected>Usado</option>';
                }
                //almacen seleccionado 
                $almacenes = Almacen::where('status', 'ALTA')->get();
                $selectalmacenes = "<option selected disabled hidden>Selecciona el almacén</option>";
                foreach($almacenes as $a){
                    if($a->Numero == $ahd->id_almacen){
                        $selectalmacenes = $selectalmacenes.'<option selected value='.$a->Numero.'>'.$a->Nombre.'</option>';
                    }else{
                        //$selectalmacenes = $selectalmacenes.'<option value='.$a->Numero.'>'.$a->Nombre.'</option>';
                    }    
                }
                //existencias del almacen seleccionado
                $Existencia = Existencia::where('Codigo', $ahd->herramienta)->where('Almacen', $ahd->id_almacen)->first();
                $existenciasactuales = 0;
                if($Existencia != NULL){
                    $existenciasactuales = $Existencia->Existencias;
                }
                $filasdetallesasignacion= $filasdetallesasignacion.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosproductos('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$ahd->item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'.$ahd->herramienta.'" readonly data-parsley-length="[1, 20]">'.$ahd->herramienta.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl nombreproductopartida" name="nombreproductopartida[]" id="nombreproductopartida[]" value="'.$ahd->descripcion.'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'.$ahd->unidad.'" readonly data-parsley-length="[1, 5]">'.$ahd->unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<select name="almacenpartida[]" class="form-control divorinputmodxl almacenpartida" style="width:100% !important;height: 28px !important;" onchange="obtenerexistenciasalmacen('.$contadorproductos.')" required>'.
                        $selectalmacenes.
                        '</select>'.
                    '</td>'. 
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm existenciasalmacenpartida" name="existenciasalmacenpartida[]" id="existenciasalmacenpartida[]" value="'.Helpers::convertirvalorcorrecto($existenciasactuales).'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" id="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($ahd->cantidad).'" ><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" id="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->cantidad).'" data-parsley-min="0.1" data-parsley-max="'.Helpers::convertirvalorcorrecto($existenciasactuales+$ahd->cantidad).'" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" id="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->precio).'" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" id="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->total).'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod">'.
                    '<select name="estadopartida[]" class="form-control" style="width:100% !important;height: 28px !important;" required>'.
                        '<option selected disabled hidden>Selecciona</option>'.
                        $opciones.
                    '</select>'.
                    '</td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesasignacion = '';
        }        
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($Asignacion_Herramienta->status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($Asignacion_Herramienta->status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($Asignacion_Herramienta->fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    if($Asignacion_Herramienta->autorizado_por == ''){
                        $modificacionpermitida = 1;
                    }else{
                        $modificacionpermitida = 0;
                    }
                }
            }
        }  
        $data = array(
            "Asignacion_Herramienta" => $Asignacion_Herramienta,
            "filasdetallesasignacion" => $filasdetallesasignacion,
            "Numero_Asignacion_Herramienta_Detalle" => $Numero_Asignacion_Herramienta_Detalle,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "modificacionpermitida" => $modificacionpermitida,
            "fecha" => Helpers::formatoinputdatetime($Asignacion_Herramienta->fecha),
            "total" => Helpers::convertirvalorcorrecto($Asignacion_Herramienta->total),
            "personalrecibe" => $personalrecibe,
            "personalentrega" => $personalentrega
        );
        return response()->json($data);
    }
    //guardar cambios de la asignacion
    public function asignacion_herramienta_guardar_modificacion(Request $request){
        //INGRESAR DATOS A TABLA
        $asignacion = $request->folio.'-'.$request->serie;
        $Asignacion_Herramienta = Asignacion_Herramienta::where('asignacion', $asignacion)->first();
        //array detalles antes de modificacion
        $ArrayDetallesAsignacionAnterior = Array();
        $DetallesAsignacionAnterior = Asignacion_Herramienta_Detalle::where('asignacion', $asignacion)->get();
        foreach($DetallesAsignacionAnterior as $detalle){
            array_push($ArrayDetallesAsignacionAnterior, $detalle->asignacion.'#'.$detalle->herramienta.'#'.$detalle->item.'#'.$detalle->id_almacen);
        }
        //array detalles despues de modificacion
        $ArrayDetallesAsignacionNuevo = Array();
        foreach ($request->codigoproductopartida as $key => $nuevocodigo){
            if($request->agregadoen [$key] == 'NA'){
                array_push($ArrayDetallesAsignacionNuevo, $asignacion.'#'.$nuevocodigo.'#'.$request->itempartida [$key].'#'.$request->almacenpartida [$key]);
            } 
        }  
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesAsignacionAnterior, $ArrayDetallesAsignacionNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                $detalleasignacion = Asignacion_Herramienta_Detalle::where('asignacion', $explode_d[0])->where('herramienta', $explode_d[1])->where('item', $explode_d[2])->first();
                //sumar existencias a almacen principal
                $SumarExistenciaAlmacen = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $explode_d[3])->first();
                $SumarExistenciaNuevaAlmacen = $SumarExistenciaAlmacen->Existencias + $detalleasignacion->cantidad;
                Existencia::where('Codigo', $explode_d[1])
                            ->where('Almacen', $explode_d[3])
                            ->update([
                                'Existencias' => Helpers::convertirvalorcorrecto($SumarExistenciaNuevaAlmacen)
                            ]);
                //eliminar detalle
                $eliminardetalle= Asignacion_Herramienta_Detalle::where('asignacion', $explode_d[0])->where('herramienta', $explode_d[1])->where('item', $explode_d[2])->forceDelete();
            }
        }
        //modificar tabla general
        Asignacion_Herramienta::where('asignacion', $asignacion)
                                ->update([
                                    'fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                                    'recibe_herramienta' => $request->numeropersonalrecibe,
                                    'entrega_herramienta' => $request->numeropersonalentrega,
                                    'total' => $request->total,
                                    'observaciones' => $request->observaciones
                                ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ASIGNACION DE HERRAMIENTA";
        $BitacoraDocumento->Movimiento = $asignacion;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Asignacion_Herramienta->status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA  DETALLES
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){  
            //if la partida se agrego en la modificacion se realiza un insert
            if($request->agregadoen [$key] == 'modificacion'){ 
                DB::unprepared('SET IDENTITY_INSERT asignacion_herramientas_detalles ON');
                $iddetalle = Helpers::ultimoidregistrotabla('App\Asignacion_Herramienta_Detalle'); 
                $contardetalles = Asignacion_Herramienta_Detalle::where('asignacion', $asignacion)->count();
                if($contardetalles > 0){
                    $item = Asignacion_Herramienta_Detalle::select('item')->where('asignacion', $asignacion)->orderBy('item', 'DESC')->take(1)->get();
                    $ultimoitem = $item[0]->item+1; 
                }else{
                    $ultimoitem = 1;
                }        
                $Asignacion_Herramienta_Detalle=new Asignacion_Herramienta_Detalle;
                $Asignacion_Herramienta_Detalle->id = $iddetalle;
                $Asignacion_Herramienta_Detalle->id_asignacion_herramienta = $Asignacion_Herramienta->id;
                $Asignacion_Herramienta_Detalle->asignacion = $asignacion;
                $Asignacion_Herramienta_Detalle->fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Asignacion_Herramienta_Detalle->herramienta = $codigoproductopartida;
                $Asignacion_Herramienta_Detalle->descripcion = $request->nombreproductopartida [$key];
                $Asignacion_Herramienta_Detalle->unidad = $request->unidadproductopartida [$key];
                $Asignacion_Herramienta_Detalle->cantidad =  $request->cantidadpartida  [$key];
                $Asignacion_Herramienta_Detalle->precio =  $request->preciopartida [$key];
                $Asignacion_Herramienta_Detalle->total = $request->totalpesospartida [$key];
                $Asignacion_Herramienta_Detalle->estado_herramienta = $request->estadopartida  [$key];
                $Asignacion_Herramienta_Detalle->item = $ultimoitem;
                $Asignacion_Herramienta_Detalle->id_almacen = $request->almacenpartida [$key];
                $Asignacion_Herramienta_Detalle->save();
                DB::unprepared('SET IDENTITY_INSERT asignacion_herramientas_detalles OFF');
                //restar existencias a almacen principal
                $RestarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->almacenpartida [$key])->first();
                $RestarExistenciaNuevaAlmacen = $RestarExistenciaAlmacen->Existencias - $request->cantidadpartida  [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->almacenpartida [$key])
                            ->update([
                                'Existencias' => Helpers::convertirvalorcorrecto($RestarExistenciaNuevaAlmacen)
                            ]);

            }else{
                $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('id_asignacion_herramienta', $Asignacion_Herramienta->id)->where('herramienta', $codigoproductopartida)->where('item', $request->itempartida [$key])->first();
                Asignacion_Herramienta_Detalle::where('id_asignacion_herramienta', $Asignacion_Herramienta->id)->where('herramienta', $codigoproductopartida)->where('item', $request->itempartida [$key])
                                                ->update([
                                                    'descripcion' => $request->nombreproductopartida [$key],
                                                    'cantidad' =>  $request->cantidadpartida  [$key],
                                                    'precio' =>  $request->preciopartida [$key],
                                                    'total' => $request->totalpesospartida [$key],
                                                    'estado_herramienta' => $request->estadopartida  [$key]
                                                ]);
                //sumar existencias a almacen principal
                $SumarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->almacenpartida [$key])->first();
                $SumarExistenciaNuevaAlmacen = $SumarExistenciaAlmacen->Existencias + $request->cantidadpartidadb  [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->almacenpartida [$key])
                            ->update([
                                'Existencias' => Helpers::convertirvalorcorrecto($SumarExistenciaNuevaAlmacen)
                            ]);
                //restar existencias a almacen principal
                $RestarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->almacenpartida [$key])->first();
                $RestarExistenciaNuevaAlmacen = $RestarExistenciaAlmacen->Existencias - $request->cantidadpartida  [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->almacenpartida [$key])
                            ->update([
                                'Existencias' => Helpers::convertirvalorcorrecto($RestarExistenciaNuevaAlmacen)
                            ]);                                
            }
        }
    	return response()->json($Asignacion_Herramienta);
    }
    //verificar si la asignacion no se esta usando el algun modulo (Prestamos Herramientas)
    public function asignacion_herramienta_verificar_uso_en_modulos(Request $request){
        $Asignacion_Herramienta = Asignacion_Herramienta::where('asignacion', $request->asignaciondesactivar)->first();
        $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $request->asignaciondesactivar)->get();
        $numero_prestamos = 0;
        foreach($Asignacion_Herramienta_Detalle as $ahd){
            $Prestamo_Herramienta_Detalle = Prestamo_Herramienta_Detalle::where('id_detalle_asignacion_herramienta', $ahd->id)->where('status_prestamo', 'PRESTADO')->count();
            if($Prestamo_Herramienta_Detalle > 0){
                $numero_prestamos = $numero_prestamos + $Prestamo_Herramienta_Detalle;
            }
        }
        $data = array (
            'numero_prestamos' => $numero_prestamos,
            'status' => $Asignacion_Herramienta->status
        );
        return response()->json($data);
    }
    //dar de baja asignacion de herramienta
    public function asignacion_herramienta_alta_o_baja(Request $request){
        $Asignacion_Herramienta = Asignacion_Herramienta::where('asignacion', $request->asignaciondesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        Asignacion_Herramienta::where('asignacion', $request->asignaciondesactivar)
                                ->update([
                                    'motivo_baja' => $MotivoBaja,
                                    'status' => 'BAJA',
                                    'total' => '0.000000'
                                ]);
        //regresar cantidad asignada al codigo en la tabla existencias
            $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $request->asignaciondesactivar)->get();
            foreach($Asignacion_Herramienta_Detalle as $detalle){
                //sumar existencias a almacen principal
                $SumarExistenciaAlmacen = Existencia::where('Codigo', $detalle->herramienta)->where('Almacen', $detalle->id_almacen)->first();
                $SumarExistenciaNuevaAlmacen = $SumarExistenciaAlmacen->Existencias + $detalle->cantidad;
                Existencia::where('Codigo', $detalle->herramienta)
                            ->where('Almacen', $detalle->id_almacen)
                            ->update([
                                'Existencias' => Helpers::convertirvalorcorrecto($SumarExistenciaNuevaAlmacen)
                            ]);
                //colocar en ceros cantidades detalles
                Asignacion_Herramienta_Detalle::where('id', $detalle->id)
                ->update([
                    'cantidad' => '0.000000',
                    'total' => '0.000000',
                    'estado_auditoria' => 'BAJA',
                    'cantidad_auditoria' => '0.000000'
                ]);
            }
        return response()->json($Asignacion_Herramienta);
    }
    //obtener personal para crear excel
    public function asignacion_herramienta_generar_excel_obtener_personal(){
        $personal = Personal::where('status', 'ALTA')->get();
        return response()->json($personal);
    }
    //obtener toda la herramienta asignada al personal seleccionado
    public function asignacion_herramienta_obtener_herramienta_personal(Request $request){
        //$Asignacion_Herramienta = Asignacion_Herramienta::where('recibe_herramienta', $request->idpersonal)->where('status', 'ALTA')->where('autorizado_por', '<>', '')->get();
        $Asignacion_Herramienta = Asignacion_Herramienta::where('recibe_herramienta', $request->idpersonal)->where('status', 'ALTA')->get();
        $filasdetallesasignacion = '';
        $contadorfilas = 0;
        $contadorasignacionessinautorizar = 0;
        foreach($Asignacion_Herramienta as $ah){
            if($ah->autorizado_por != ''){
                $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $ah->asignacion)->get();
                foreach($Asignacion_Herramienta_Detalle as $ahd){    
                    if($ahd->estado_auditoria == 'FALTANTE'){
                        $opciones = '<option value="FALTANTE" selected>FALTANTE</option>'.
                                    '<option value="OK">OK</option>';
                        $readonlycantidadauditoria = '';
                    }elseif($ahd->estado_auditoria == 'OK'){
                        $opciones = '<option value="FALTANTE">FALTANTE</option>'.
                                    '<option value="OK" selected>OK</option>';
                        $readonlycantidadauditoria = 'readonly="readonly"';
                    }else{
                        $opciones = '<option value="FALTANTE">FALTANTE</option>'.
                                    '<option value="OK">OK</option>';
                        $readonlycantidadauditoria = '';
                    }
                    $filasdetallesasignacion= $filasdetallesasignacion.
                    '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                        '<td class="tdmod"><input type="hidden" class="form-control asignacionpartida" name="asignacionpartida[]" id="asignacsionpartida[]" value="'.$ahd->id.'" readonly>'.$ahd->asignacion.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'.$ahd->herramienta.'" readonly>'.$ahd->herramienta.'</td>'.
                        '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control nombreproductopartida" name="nombreproductopartida[]" id="nombreproductopartida[]" value="'.$ahd->descripcion.'" readonly>'.$ahd->descripcion.'</div></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'.$ahd->unidad.'" readonly>'.$ahd->unidad.'</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" id="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->cantidad).'" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" id="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->precio).'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" id="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->total).'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod">'.
                            '<select name="estadoauditoria[]" class="form-control estadoauditoria" style="width:100% !important;height: 28px !important;" onchange="compararestadoauditoria('.$contadorfilas.');" required>'.
                                '<option selected disabled hidden>Selecciona</option>'.
                                $opciones.
                            '</select>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadauditoriapartida" name="cantidadauditoriapartida[]" id="cantidadauditoriapartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->cantidad_auditoria).'" data-parsley-max="'.Helpers::convertirvalorcorrecto($ahd->cantidad).'" onchange="formatocorrectoinputcantidades(this)" required '.$readonlycantidadauditoria.'></td>'.
                    '</tr>';
                    $contadorfilas++;
                }
            }else{
                $contadorasignacionessinautorizar++;
            }
        }
        //url para generar reporte de auditoria
        $urlgenerarreporteauditoria  = route('asignacion_herramienta_generar_reporte_auditoria', $request->idpersonal);
        //url para generar reporte general personal
        $urlgenerarreportegeneral  = route('asignacion_herramienta_generar_reporte_general', $request->idpersonal);
        $data = array(
            "filasdetallesasignacion" => $filasdetallesasignacion,
            "contadorfilas" => $contadorfilas,
            "urlgenerarreporteauditoria" => $urlgenerarreporteauditoria,
            "urlgenerarreportegeneral" => $urlgenerarreportegeneral,
            "contadorasignacionessinautorizar" => $contadorasignacionessinautorizar
        );
        return response()->json($data);
    }
    //guardar auditoria
    public function asignacion_herramienta_guardar_auditoria(Request $request){
        foreach ($request->asignacionpartida as $key => $iddetalleasignacion){   
            $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('id', $iddetalleasignacion)->first();
            $Asignacion_Herramienta_Detalle->estado_auditoria =  $request->estadoauditoria  [$key];
            $Asignacion_Herramienta_Detalle->cantidad_auditoria =  $request->cantidadauditoriapartida [$key];
            $Asignacion_Herramienta_Detalle->save();
        }
    	return response()->json($Asignacion_Herramienta_Detalle);
    }
    //generar reporte de auditoria
    public function asignacion_herramienta_generar_reporte_auditoria($id){
        $Asignacion_Herramienta = Asignacion_Herramienta::where('recibe_herramienta', $id)->where('status', 'ALTA')->where('autorizado_por', '<>', '')->get();
        $Personal_Recibe_Herramienta = Personal::where('id', $id)->first();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $totalasignacion = 0;
        $data=array();
        $datadetalle=array();

        foreach ($Asignacion_Herramienta as $ah){
            $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $ah->asignacion)->get();
            foreach($Asignacion_Herramienta_Detalle as $ahd){
                if($ahd->estado_auditoria == 'FALTANTE'){
                    $producto = Producto::where('Codigo', $ahd->herramienta)->first();
                    $totaldetalle = $ahd->cantidad_auditoria * $ahd->precio;
                    $datadetalle[]=array(
                        "cantidadauditoriadetalle"=> Helpers::convertirvalorcorrecto($ahd->cantidad_auditoria),
                        "herramientadetalle"=>$ahd->herramienta,
                        "descripciondetalle"=>$ahd->descripcion,
                        "preciodetalle" => Helpers::convertirvalorcorrecto($ahd->precio),
                        "totaldetalle" => Helpers::convertirvalorcorrecto($totaldetalle),
                        "estadoauditoriadetalle" => $ahd->estado_auditoria,
                        "asignaciondetalle" => $ahd->asignacion
                    );
                    $totalasignacion = $totalasignacion + $totaldetalle;
                }
            } 

        }
        $data[]=array(
            "asignacion"=>$ah,
            "totalasignacion"=>Helpers::convertirvalorcorrecto($totalasignacion),
            "fechaformato"=> $fechaformato,
            "datadetalle" => $datadetalle,
            "Personal_Recibe_Herramienta" => $Personal_Recibe_Herramienta
        );
        $pdf = PDF::loadView('registros.asignacionherramienta.formato_pdf_asignacion_herramienta_auditoria', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        //return $pdf->download('contrarecibos.pdf');
        return $pdf->stream();
    }
    //generar reporte general
    public function asignacion_herramienta_generar_reporte_general($id){
        $Asignacion_Herramienta = Asignacion_Herramienta::where('recibe_herramienta', $id)->where('status', 'ALTA')->where('autorizado_por', '<>', '')->get();
        $Personal_Recibe_Herramienta = Personal::where('id', $id)->first();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $totalasignacion = 0;
        $totalfaltante = 0;
        $data=array();
        $datadetalle=array();
        $datadetallefaltante=array();
        foreach ($Asignacion_Herramienta as $ah){
            $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $ah->asignacion)->get();
            foreach($Asignacion_Herramienta_Detalle as $ahd){
                //herramienta asignada
                $producto = Producto::where('Codigo', $ahd->herramienta)->first();
                $totaldetalle = $ahd->cantidad * $ahd->precio;
                $datadetalle[]=array(
                    "cantidadinicialasignacion"=> Helpers::convertirvalorcorrecto($ahd->cantidad),
                    "cantidadauditoriadetalle"=> Helpers::convertirvalorcorrecto($ahd->cantidad_auditoria),
                    "herramientadetalle"=>$ahd->herramienta,
                    "descripciondetalle"=>$ahd->descripcion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($ahd->precio),
                    "totaldetalle" => Helpers::convertirvalorcorrecto($totaldetalle),
                    "estadoauditoriadetalle" => $ahd->estado_auditoria,
                    "asignaciondetalle" => $ahd->asignacion
                );
                $totalasignacion = $totalasignacion + $totaldetalle;
                //herramienta faltante
                if($ahd->estado_auditoria == 'FALTANTE'){
                    $producto = Producto::where('Codigo', $ahd->herramienta)->first();
                    $totaldetalle = $ahd->cantidad_auditoria * $ahd->precio;
                    $datadetallefaltante[]=array(
                        "cantidadauditoriadetalle"=> Helpers::convertirvalorcorrecto($ahd->cantidad_auditoria),
                        "herramientadetalle"=>$ahd->herramienta,
                        "descripciondetalle"=>$ahd->descripcion,
                        "preciodetalle" => Helpers::convertirvalorcorrecto($ahd->precio),
                        "totaldetalle" => Helpers::convertirvalorcorrecto($totaldetalle),
                        "estadoauditoriadetalle" => $ahd->estado_auditoria,
                        "asignaciondetalle" => $ahd->asignacion
                    );
                    $totalfaltante = $totalfaltante + $totaldetalle;
                }
            } 
        }
        $data[]=array(
            "asignacion"=>$ah,
            "totalasignacion"=>Helpers::convertirvalorcorrecto($totalasignacion),
            "totalfaltante"=>Helpers::convertirvalorcorrecto($totalfaltante),
            "fechaformato"=> $fechaformato,
            "datadetalle" => $datadetalle,
            "datadetallefaltante" => $datadetallefaltante,
            "Personal_Recibe_Herramienta" => $Personal_Recibe_Herramienta,
        );
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.asignacionherramienta.formato_pdf_reporte_general_herramienta_asignada', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }
}
