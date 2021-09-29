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
use App\Exports\TraspasosExport;
use App\Traspaso;
use App\TraspasoDetalle;
use App\Serie;
use App\Almacen;
use App\Existencia;
use App\BitacoraDocumento;
use App\Producto;
use App\Marca;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\CotizacionServicio;
use App\CotizacionServicioDetalle;
use App\Requisicion;
use App\RequisicionDetalle;
use App\Cliente;
use App\Configuracion_Tabla;
use App\VistaTraspaso;
use App\VistaObtenerExistenciaProducto;
use Config;
use Mail;
use LynX39\LaraPdfMerger\Facades\PdfMerger;

class TraspasoController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'Traspasos')->first();
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

    public function traspasos(){
        $serieusuario = 'A';
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('traspasos_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('traspasos_exportar_excel');
        $rutacreardocumento = route('traspasos_generar_pdfs');
        $almacendedefault = Almacen::where('Numero', 1)->first();
        return view('registros.traspasos.traspasos', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento','almacendedefault'));
    }

    public function traspasos_obtener(Request $request){
        if($request->ajax()){
            $periodo = $request->periodo;
            //$data = VistaTraspaso::select($this->campos_consulta)->where('periodo', $periodo)->orderBy('Fecha', 'DESC')->orderBy('Serie', 'ASC')->orderBy('Folio', 'DESC')->get();
            $data = VistaTraspaso::select($this->campos_consulta)->where('periodo', $periodo);
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
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                    '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                        'OPERACIONES <span class="caret"></span>'.
                                    '</button>'.
                                    '<ul class="dropdown-menu">'.
                                        '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Traspaso .'\')">Cambios</a></li>'.
                                        '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Traspaso .'\')">Bajas</a></li>'.
                                        '<li><a href="'.route('traspasos_generar_pdfs_indiv',$data->Traspaso).'" target="_blank">Ver Documento PDF</a></li>'.
                                        '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Traspaso .'\')">Enviar Documento por Correo</a></li>'.
                                    '</ul>'.
                                '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                    ->addColumn('subtotal', function($data){ return $data->SubTotal; })
                    ->addColumn('iva', function($data){ return $data->Iva; })
                    ->addColumn('total', function($data){ return $data->Total; })
                    ->addColumn('importe', function($data){ return $data->Importe; })
                    ->addColumn('descuento', function($data){ return $data->Descuento; })
                    ->addColumn('costo', function($data){ return $data->Costo; })
                    ->addColumn('comision', function($data){ return $data->Comision; })
                    ->addColumn('utilidad', function($data){ return $data->Utilidad; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener series documento
    public function traspasos_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'Traspasos')->where('Usuario', Auth::user()->user)->get();
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
    public function traspasos_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Traspaso',$request->Serie);
        return response()->json($folio);
    }
    //obtener ultimo folio
    public function traspasos_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Traspaso',$request->serie);
        return response()->json($folio);
    }

    //obtener almacenes
    public function traspasos_obtener_almacenes(Request $request){
        if($request->ajax()){
            $data = Almacen::where('Status', 'ALTA')->where('Numero', '<>', $request->numeroalmacena)->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaralmacen('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtenr alamcen de porn umero
    public function  traspasos_obtener_almacen_de_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existealmacen = Almacen::where('Numero', $request->numeroalmacende)->where('Numero', '<>', $request->numeroalmacena)->where('Status', 'ALTA')->count();
        if($existealmacen > 0){
            $almacen = Almacen::where('Numero', $request->numeroalmacende)->where('Numero', '<>', $request->numeroalmacena)->where('Status', 'ALTA')->first();
            $numero = $almacen->Numero;
            $nombre = $almacen->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }

    //obtener alamcenes foraneos
    public function traspasos_obtener_almacenes_foraneos(Request $request){
        if($request->ajax()){
            $data = Almacen::where('Status', 'ALTA')->where('Numero', '<>', $request->numeroalmacende)->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaralmacenforaneo('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener almacen a por numero
    public function traspasos_obtener_almacen_a_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existealmacen = Almacen::where('Numero', $request->numeroalmacena)->where('Numero', '<>', $request->numeroalmacende)->where('Status', 'ALTA')->count();
        if($existealmacen > 0){
            $almacen = Almacen::where('Numero', $request->numeroalmacena)->where('Numero', '<>', $request->numeroalmacende)->where('Status', 'ALTA')->first();
            $numero = $almacen->Numero;
            $nombre = $almacen->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }

    //obtener ordenes de trabajo
    public function traspasos_obtener_ordenes_trabajo(Request $request){
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
    public function traspasos_obtener_orden_trabajo_por_folio(Request $request){
        $orden = '';
        $fecha = '';
        $cliente = '';
        $tipo = '';
        $unidad = '';
        $statusorden = '';
        $existeorden = DB::table('Ordenes de Trabajo as ot')
                            ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                            ->select('ot.Orden as Orden', 'ot.Fecha as Fecha', 'c.Nombre as Cliente', 'ot.Tipo as Tipo', 'ot.Unidad as Unidad', 'ot.Status AS StatusOrden')
                            ->where('ot.Status', 'ABIERTA')
                            ->where('ot.Orden', $request->orden)
                            ->count();
        if($existeorden > 0){
            $ot = DB::table('Ordenes de Trabajo as ot')
                        ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                        ->select('ot.Orden as Orden', 'ot.Fecha as Fecha', 'c.Nombre as Cliente', 'ot.Tipo as Tipo', 'ot.Unidad as Unidad', 'ot.Status AS StatusOrden')
                        ->where('ot.Status', 'ABIERTA')
                        ->where('ot.Orden', $request->orden)
                        ->get();
            $orden = $ot[0]->Orden;
            $fecha = $ot[0]->Fecha;
            $cliente = $ot[0]->Cliente;
            $tipo = $ot[0]->Tipo;
            $unidad = $ot[0]->Unidad;
            $statusorden = $ot[0]->StatusOrden;
        }
        $data = array(
            'orden' => $orden,
            'fecha' => $fecha,
            'cliente' => $cliente,
            'tipo' => $tipo,
            'unidad' => $unidad,
            'statusorden' => $statusorden
        );
        return response()->json($data); 
    }

    //obtener cotizaciones 
    public function traspasos_obtener_cotizaciones(Request $request){
        if($request->ajax()){
            $mesactual = date("m");
            $data = DB::table('Cotizaciones Servicio as cots')
                        ->leftJoin('Clientes as c', 'c.Numero', '=', 'cots.Cliente')
                        ->select('cots.Cotizacion', 'cots.Folio', 'cots.Fecha', 'cots.Cliente', 'c.Nombre as Nombre', 'cots.Unidad', 'cots.Plazo as Dias', 'cots.Total')
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
    public function traspasos_obtener_cotizacion(Request $request){
        $cotizacion = CotizacionServicio::where('Cotizacion', $request->Cotizacion)->first();
        $numeroalmacende = $request->numeroalmacende;
        //detalles cotizacion
        $detallescotizacion = CotizacionServicioDetalle::where('Cotizacion', $request->Cotizacion)->where('Departamento', 'REFACCIONES')->orderby('Item', 'ASC')->get();
        $numerodetallescotizacion = CotizacionServicioDetalle::where('Cotizacion', $request->Cotizacion)->where('Departamento', 'REFACCIONES')->count();
        $filasdetallescotizacion = '';
        $contadorproductos = 0;
        $contadorfilas = 0;
        $tipo = "alta";
        if($numerodetallescotizacion > 0){
            foreach($detallescotizacion as $dc){
                $ObtenerExistencia = VistaObtenerExistenciaProducto::select('Existencias')->where('Codigo', $dc->Codigo)->where('Almacen', $numeroalmacende)->first();
                $Existencias = $ObtenerExistencia->Existencias;
                $tipo = "alta";
                $filasdetallescotizacion = $filasdetallescotizacion.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipo.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dc->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dc->Codigo.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.htmlspecialchars($dc->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dc->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dc->Unidad.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-existencias="'.$Existencias.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Utilidad).'" data-parsley-utilidad="0.'.$this->numerocerosconfiguradosinputnumberstep.'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl observacionespartida" name="observacionespartida[]" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm requisicionpartida" name="requisicionpartida[]" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cotizacionpartida" name="cotizacionpartida[]" value="'.$request->Cotizacion.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly data-parsley-length="[1, 3]"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'.Helpers::convertirvalorcorrecto($dc->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipodecambiopartida" name="tipodecambiopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->TipoDeCambio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallescotizacion = '';
        }        
        $data = array(
            "cotizacion" => $cotizacion,
            "filasdetallescotizacion" => $filasdetallescotizacion,
            "numerodetallescotizacion" => $numerodetallescotizacion,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdatetime($cotizacion->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($cotizacion->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($cotizacion->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($cotizacion->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($cotizacion->Iva),
            "total" => Helpers::convertirvalorcorrecto($cotizacion->Total)
        );
        return response()->json($data);
    }
    
    //obtener requisiciones 
    public function traspasos_obtener_requisiciones(Request $request){
        if($request->ajax()){
            $mesactual = date("m");
            $data = DB::table('Requisiciones as r')
                        ->select('r.Requisicion', 'r.Folio', 'r.Fecha', 'r.Orden', 'r.Obs')
                        ->where('r.Status', 'POR SURTIR')
                        ->orWhere('r.Status', 'BACKORDER')
                        ->whereMonth('r.Fecha', '=', $mesactual)
                        ->orderBy("r.Folio", "DESC")
                        ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarrequisicion('.$data->Folio.',\''.$data->Requisicion .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){ return Helpers::fecha_espanol($data->Fecha);  })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener datos de la requiscion seleccionada
    public function traspasos_obtener_requisicion(Request $request){
        $requisicion = Requisicion::where('Requisicion', $request->Requisicion)->first();
        $numeroalmacende = $request->numeroalmacende;
        //detalles requisicion
        $detallesrequisicion = RequisicionDetalle::where('Requisicion', $request->Requisicion)->where('Surtir', '>', 0)->get();
        $numerodetallesrequisicion = RequisicionDetalle::where('Requisicion', $request->Requisicion)->where('Surtir', '>', 0)->count();
        $filasdetallesrequisicion = '';
        $contadorproductos = 0;
        $contadorfilas = 0;
        $tipo = "alta";
        if($numerodetallesrequisicion > 0){
            foreach($detallesrequisicion as $dr){
                $ObtenerExistencia = VistaObtenerExistenciaProducto::select('Existencias')->where('Codigo', 'like', '%' . $dr->Codigo . '%')->where('Almacen', $numeroalmacende)->first();
                $Existencias = $ObtenerExistencia->Existencias;
                $tipo = "alta";
                $filasdetallesrequisicion = $filasdetallesrequisicion.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipo.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dr->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dr->Codigo.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.htmlspecialchars($dr->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dr->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dr->Unidad.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Surtir).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-existencias="'.$Existencias.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Utilidad).'" data-parsley-utilidad="0.'.$this->numerocerosconfiguradosinputnumberstep.'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl observacionespartida" name="observacionespartida[]" value="'.$dr->Obs.'" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm requisicionpartida" name="requisicionpartida[]" value="'.$request->Requisicion.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cotizacionpartida" name="cotizacionpartida[]"  readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$dr->Moneda.'" readonly data-parsley-length="[1, 3]"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'.Helpers::convertirvalorcorrecto($dr->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipodecambiopartida" name="tipodecambiopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->TipoDeCambio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallescotizacion = '';
        }        
        $data = array(
            "requisicion" => $requisicion,
            "filasdetallesrequisicion" => $filasdetallesrequisicion,
            "numerodetallesrequisicion" => $numerodetallesrequisicion,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdatetime($requisicion->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($requisicion->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($requisicion->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($requisicion->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($requisicion->Iva),
            "total" => Helpers::convertirvalorcorrecto($requisicion->Total)
        );
        return response()->json($data);
    }

    //obtener productos
    public function traspasos_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $numeroalmacende = $request->numeroalmacende;
            $tipooperacion = $request->tipooperacion;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')
                                                    ->where(function ($query) use ($numeroalmacende){
                                                        $query->where('Almacen', $numeroalmacende);
                                                    });
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($numeroalmacende, $tipooperacion){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\',\''.$tipooperacion.'\')">Seleccionar</div>';
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
    public function traspasos_obtener_producto_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        $numeroalmacende = $request->numeroalmacende;
        $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)
                                                            ->where(function ($query) use ($numeroalmacende){
                                                                $query->where('Almacen', $numeroalmacende);
                                                            })->count();
        if($contarproductos > 0){
            $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)
                                                        ->where(function ($query) use ($numeroalmacende){
                                                            $query->where('Almacen', $numeroalmacende);
                                                        })->first();
            $data = array(
                'Codigo' => $producto->Codigo,
                'Producto' => htmlspecialchars($producto->Producto, ENT_QUOTES),
                'Unidad' => $producto->Unidad,
                'Costo' => Helpers::convertirvalorcorrecto($producto->Costo),
                'Impuesto' => Helpers::convertirvalorcorrecto($producto->Impuesto),
                'SubTotal' => Helpers::convertirvalorcorrecto($producto->SubTotal),
                'Existencias' => Helpers::convertirvalorcorrecto($producto->Existencias),
                'contarproductos' => $contarproductos
            );
        }else{
            $data = array(
                'Codigo' => '',
                'Producto' => '',
                'Unidad' => '',
                'Costo' => '',
                'Impuesto' => '',
                'SubTotal' => '',
                'Existencias' => '',
                'contarproductos' => $contarproductos
            );
        }
        return response()->json($data);        
    }

    //obtener existencias
    public function traspasos_obtener_existencias_partida(Request $request){
        $ContarExistencia = Existencia::where('Codigo', $request->codigo)->where('Almacen', $request->numeroalmacende)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::where('Codigo', $request->codigo)->where('Almacen',$request->numeroalmacende)->first();
            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $Existencias = Helpers::convertirvalorcorrecto(0);
        }
        return response()->json($Existencias);
    }

    //obtener exstencias almacen foraneo
    public function traspasos_obtener_existencias_almacen_foraneo(Request $request){
        $ContarExistencia = Existencia::where('Codigo', $request->codigo)->where('Almacen', $request->numeroalmacena)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::where('Codigo', $request->codigo)->where('Almacen',$request->numeroalmacena)->first();
            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $Existencias = Helpers::convertirvalorcorrecto(0);
        }
        return response()->json($Existencias);
    }

    //guardar
    public function traspasos_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo folio de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\Traspaso',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $traspaso = $folio.'-'.$request->serie;
        $Traspaso = new Traspaso;
        $Traspaso->Traspaso=$traspaso;
        $Traspaso->Serie=$request->serie;
        $Traspaso->Folio=$folio;
        $Traspaso->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $Traspaso->De=$request->numeroalmacende;
        $Traspaso->A=$request->numeroalmacena;
        $Traspaso->Referencia=$request->referencia;
        $Traspaso->Orden=$request->orden;
        $Traspaso->Importe=$request->importe;
        $Traspaso->Descuento=$request->descuento;
        $Traspaso->SubTotal=$request->subtotal;
        $Traspaso->Iva=$request->iva;
        $Traspaso->Total=$request->total;
        $Traspaso->Costo=$request->costo;
        $Traspaso->Utilidad=$request->utilidad;
        $Traspaso->Obs=$request->observaciones;
        $Traspaso->Status="APLICADO";
        $Traspaso->Usuario=Auth::user()->user;
        $Traspaso->Periodo=$this->periodohoy;
        $Traspaso->save();
        //modificar totales orden trabajo
        if($request->orden != ""){
            $OrdenTrabajoAnterior = OrdenTrabajo::where('Orden', $request->orden)->first();
            OrdenTrabajo::where('Orden', $request->orden)
                        ->update([
                            'Importe' => $OrdenTrabajoAnterior->Importe + $request->importe,
                            'Descuento' => $OrdenTrabajoAnterior->Descuento + $request->descuento,
                            'SubTotal' => $OrdenTrabajoAnterior->SubTotal + $request->subtotal,
                            'Iva' => $OrdenTrabajoAnterior->Iva + $request->iva,
                            'Total' => $OrdenTrabajoAnterior->Total + $request->total,
                            'Costo' => $OrdenTrabajoAnterior->Costo + $request->costo,
                            'Utilidad' => $OrdenTrabajoAnterior->Utilidad + $request->utilidad
                        ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "TRASPASOS";
        $BitacoraDocumento->Movimiento = $traspaso;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "APLICADO";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
            $TraspasoDetalle=new TraspasoDetalle;
            $TraspasoDetalle->Traspaso = $traspaso;
            $TraspasoDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $TraspasoDetalle->Codigo = $codigoproductopartida;
            $TraspasoDetalle->Descripcion = $request->descripcionproductopartida [$key];
            $TraspasoDetalle->Unidad = $request->unidadproductopartida [$key];
            $TraspasoDetalle->Cantidad =  $request->cantidadpartida [$key];
            $TraspasoDetalle->Precio =  $request->preciopartida [$key];
            $TraspasoDetalle->Importe =  $request->importepartida [$key];
            $TraspasoDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
            $TraspasoDetalle->Descuento =  $request->descuentopesospartida  [$key];
            $TraspasoDetalle->SubTotal =  $request->subtotalpartida [$key];
            $TraspasoDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
            $TraspasoDetalle->Iva =  $request->ivapesospartida [$key];
            $TraspasoDetalle->Total =  $request->totalpesospartida [$key];
            $TraspasoDetalle->Costo =  $request->costopartida [$key];
            $TraspasoDetalle->CostoTotal =  $request->costototalpartida [$key];
            $TraspasoDetalle->Utilidad =  $request->utilidadpartida [$key];
            $TraspasoDetalle->Moneda =  $request->monedapartida [$key];
            $TraspasoDetalle->Obs =  $request->observacionespartida [$key];
            $TraspasoDetalle->Requisicion =  $request->requisicionpartida [$key];
            $TraspasoDetalle->Cotizacion =  $request->cotizacionpartida [$key];
            $TraspasoDetalle->CostoDeLista =  $request->costodelistapartida [$key];
            $TraspasoDetalle->TipoDeCambio =  $request->tipodecambiopartida [$key];
            $TraspasoDetalle->Item = $item;
            $TraspasoDetalle->save();
            if($request->requisicionpartida [$key] != ""){
                //modificar faltante por surtir detalle requisicion
                $RequisicionDetalle = RequisicionDetalle::where('Requisicion', $request->requisicionpartida [$key])->where('Codigo', $codigoproductopartida)->first();
                $Surtir = $RequisicionDetalle->Surtir-$request->cantidadpartida  [$key];
                RequisicionDetalle::where('Requisicion', $request->requisicionpartida [$key])
                                    ->where('Codigo', $codigoproductopartida)
                                    ->update([
                                        'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                    ]);
            }
            //restar existencias del almacen que se traspaso
            $ContarExistenciaAlmacenDe = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacende)->count();
            if($ContarExistenciaAlmacenDe > 0){
                $ExistenciaAlmacenDe = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacende)->first();
                $ExistenciaNuevaAlmacenDe = $ExistenciaAlmacenDe->Existencias - $request->cantidadpartida [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->numeroalmacende)
                            ->update([
                                'Existencias' => $ExistenciaNuevaAlmacenDe
                            ]);
            }
            //si el traspaso sera a otro almacen
            if($request->numeroalmacena > 0){
                //agregar existencias al almacen al que se traspaso
                $ContarExistenciaAlmacenA = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacena)->count();
                if($ContarExistenciaAlmacenA > 0){
                    $ExistenciaAlmacenA = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacena)->first();
                    $ExistenciaNuevaAlmacenA = $ExistenciaAlmacenA->Existencias + $request->cantidadpartida [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacena)
                                ->update([
                                    'Existencias' => $ExistenciaNuevaAlmacenA
                                ]);
                }else{
                    $ExistenciaAlmacenA = new Existencia;
                    $ExistenciaAlmacenA->Codigo = $codigoproductopartida;
                    $ExistenciaAlmacenA->Almacen = $request->numeroalmacena;
                    $ExistenciaAlmacenA->Existencias = $request->cantidadpartida [$key];
                    $ExistenciaAlmacenA->save();
                }
            }
            //si el traspaso sera para una orden de trabajo
            if($request->orden != ""){
                $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->orden)->first();
                $contardetallesordentrabajo = OrdenTrabajoDetalle::where('Orden', $request->orden)->count();
                if($contardetallesordentrabajo > 0){
                    $UltimaPartidaOrdenTrabajoDetalle = OrdenTrabajoDetalle::select('Partida')->where('Orden', $request->orden)->orderBy('Partida', 'DESC')->take(1)->get();
                    $UltimaPartida = $UltimaPartidaOrdenTrabajoDetalle[0]->Partida+1;
                }else{
                    $UltimaPartida = 1;
                }
                $OrdenTrabajoDetalle = new OrdenTrabajoDetalle;
                $OrdenTrabajoDetalle->Orden=$request->orden;
                $OrdenTrabajoDetalle->Cliente=$OrdenTrabajo->Cliente;
                $OrdenTrabajoDetalle->Agente=$OrdenTrabajo->Agente;
                $OrdenTrabajoDetalle->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
                $OrdenTrabajoDetalle->Codigo=$codigoproductopartida;
                $OrdenTrabajoDetalle->Descripcion=$request->descripcionproductopartida [$key];
                $OrdenTrabajoDetalle->Unidad=$request->unidadproductopartida [$key];
                $OrdenTrabajoDetalle->Cantidad=$request->cantidadpartida [$key];
                $OrdenTrabajoDetalle->Precio=$request->preciopartida [$key];
                $OrdenTrabajoDetalle->Importe=$request->importepartida [$key];
                $OrdenTrabajoDetalle->Dcto=$request->descuentoporcentajepartida [$key];
                $OrdenTrabajoDetalle->Descuento= $request->descuentopesospartida  [$key];
                $OrdenTrabajoDetalle->SubTotal=$request->subtotalpartida [$key];
                $OrdenTrabajoDetalle->Impuesto=$request->ivaporcentajepartida [$key];
                $OrdenTrabajoDetalle->Iva=$request->ivapesospartida [$key];
                $OrdenTrabajoDetalle->Total=$request->totalpesospartida [$key];
                $OrdenTrabajoDetalle->Costo=$request->costopartida [$key];
                $OrdenTrabajoDetalle->CostoTotal=$request->costototalpartida [$key];
                $OrdenTrabajoDetalle->Utilidad=$request->utilidadpartida [$key];
                $OrdenTrabajoDetalle->Departamento="REFACCIONES";
                $OrdenTrabajoDetalle->Cargo="REFACCIONES";
                $OrdenTrabajoDetalle->Traspaso=$traspaso;
                $OrdenTrabajoDetalle->Item=$item;
                $OrdenTrabajoDetalle->Usuario=Auth::user()->user;
                //$OrdenTrabajoDetalle->Facturar="S";
                $OrdenTrabajoDetalle->Almacen=$request->numeroalmacende;
                $OrdenTrabajoDetalle->Partida=$UltimaPartida;
                $OrdenTrabajoDetalle->save();
                $UltimaPartida++;
            }
            $item++;
        }
        if($request->requisicion != ""){
            //modificar el status de la requisicion a SURTIDO o BACKORDER
            $detallesrequisicionporsurtir = RequisicionDetalle::where('Requisicion', $request->requisicion)->where('Surtir', '>', 0)->count();
            if($detallesrequisicionporsurtir > 0){
                Requisicion::where('Requisicion', $request->requisicion)
                                    ->update([
                                        'Status' => "BACKORDER"
                                    ]);
            }else{
                Requisicion::where('Requisicion', $request->requisicion)
                                    ->update([
                                        'Status' => "SURTIDO"
                                    ]);
            }
        }
        return response()->json($Traspaso);
    }

    //verificar baja
    public function traspasos_verificar_baja(Request $request){
        $Traspaso = Traspaso::where('Traspaso', $request->traspasodesactivar)->first();
        $errores = '';
        if($Traspaso->A > 0){
            $detalles = TraspasoDetalle::where('Traspaso', $request->traspasodesactivar)->get();
            foreach($detalles as $detalle){
                $ContarExistenciaAlmacen = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Traspaso->A)->count();
                if($ContarExistenciaAlmacen > 0){
                    $Existencia = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Traspaso->A)->first();
                    $existencias = $Existencia->Existencias;
                }else{
                    $existencias = 0;
                }
                if($detalle->Cantidad > $existencias){
                    $errores = $errores.'Error el traspaso no se puede cancelar, no hay existencias suficientes en el almacen:'.$Traspaso->A.' para el código:'.$detalle->Codigo.'<br>';
                }
            }
        }else if($Traspaso->Orden != ""){
            $OrdenTrabajo = OrdenTrabajo::where('Orden', $Traspaso->Orden)->first();
            if($OrdenTrabajo->Status != "ABIERTA"){
                $errores = $errores.'Error el traspaso no se puede cancelar, porque la Orden de Trabajo:'.$Traspaso->Orden.' se encuentra en Status TERMINADA o FACTURADA<br>';
            }
        }
        $resultadofechas = Helpers::compararanoymesfechas($Traspaso->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'errores' => $errores,
            'Status' => $Traspaso->Status
        );
        return response()->json($data);
    }

    //bajas
    public function traspasos_alta_o_baja(Request $request){
        $Traspaso = Traspaso::where('Traspaso', $request->traspasodesactivar)->first();
        //modificar totales orden trabajo
        if($Traspaso->Orden != ""){
            $TraspasoAnterior = Traspaso::where('Traspaso', $request->traspasodesactivar)->first();
            $OrdenTrabajoAnterior = OrdenTrabajo::where('Orden', $TraspasoAnterior->Orden)->first();
            OrdenTrabajo::where('Orden', $TraspasoAnterior->Orden)
                        ->update([
                            'Importe' => $OrdenTrabajoAnterior->Importe - $TraspasoAnterior->Importe,
                            'Descuento' => $OrdenTrabajoAnterior->Descuento - $TraspasoAnterior->Descuento,
                            'SubTotal' => $OrdenTrabajoAnterior->SubTotal - $TraspasoAnterior->SubTotal,
                            'Iva' => $OrdenTrabajoAnterior->Iva - $TraspasoAnterior->Iva,
                            'Total' => $OrdenTrabajoAnterior->Total - $TraspasoAnterior->Total,
                            'Costo' => $OrdenTrabajoAnterior->Costo - $TraspasoAnterior->Costo,
                            'Utilidad' => $OrdenTrabajoAnterior->Utilidad - $TraspasoAnterior->Utilidad
                        ]);
        }
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        Traspaso::where('Traspaso', $request->traspasodesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Importe' => '0.000000',
                    'Descuento' => '0.000000',
                    'SubTotal' => '0.000000',
                    'Iva' => '0.000000',
                    'Total' => '0.000000',
                    'Costo' => '0.000000',
                    'Utilidad' => '0.000000'
                ]);
        $detalles = TraspasoDetalle::where('Traspaso', $request->traspasodesactivar)->get();
        foreach($detalles as $detalle){
            //sumar existencias al almacen que realizo el traspaso
            $ExistenciaAlmacenDe = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Traspaso->De)->first();
            $ExistenciaNuevaAlmacenDe = $ExistenciaAlmacenDe->Existencias+$detalle->Cantidad;
            Existencia::where('Codigo', $detalle->Codigo)
                        ->where('Almacen', $Traspaso->De)
                        ->update([
                            'Existencias' => $ExistenciaNuevaAlmacenDe
                        ]);
            if($Traspaso->A > 0){
                //restar existencias al almacen que solicito el traspaso
                $ExistenciaAlmacenA = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Traspaso->A)->first();
                $ExistenciaNuevaAlmacenA = $ExistenciaAlmacenA->Existencias-$detalle->Cantidad;
                Existencia::where('Codigo', $detalle->Codigo)
                            ->where('Almacen', $Traspaso->A)
                            ->update([
                                'Existencias' => $ExistenciaNuevaAlmacenA
                            ]);
            }
            if($Traspaso->Orden != ""){
                $eliminarrefacciones = OrdenTrabajoDetalle::where('Traspaso', $request->traspasodesactivar)->where('Codigo', $detalle->Codigo)->forceDelete();
                $detallestraspaso = TraspasoDetalle::where('Traspaso', $request->traspasodesactivar)->get();
                foreach ($detallestraspaso as $dt){ 
                    if($dt->Requisicion != ""){
                        $RequisicionDetalle = RequisicionDetalle::where('Requisicion', $dt->Requisicion)->where('Codigo', $dt->Codigo)->first();
                        $Surtir = $RequisicionDetalle->Surtir+$dt->Cantidad;
                        RequisicionDetalle::where('Requisicion', $dt->Requisicion)
                                            ->where('Codigo', $dt->Codigo)
                                            ->update([
                                                'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                            ]);
                        //modificar el status de la requisicion a SURTIDO o BACKORDER
                        $detallesrequisicionporsurtir = RequisicionDetalle::where('Requisicion', $dt->Requisicion)->where('Surtir', '>', 0)->count();
                        if($detallesrequisicionporsurtir > 0){
                            Requisicion::where('Requisicion', $dt->Requisicion)
                                                ->update([
                                                    'Status' => "BACKORDER"
                                                ]);
                        }else{
                            Requisicion::where('Requisicion', $dt->Requisicion)
                                                ->update([
                                                    'Status' => "SURTIDO"
                                                ]);
                        }
                    }
                }
            }
            //colocar en ceros cantidades
            TraspasoDetalle::where('Traspaso', $request->traspasodesactivar)
                            ->where('Item', $detalle->Item)
                            ->update([
                                'Cantidad' => '0.000000',
                                'Importe' => '0.000000',
                                'Dcto' => '0.000000',
                                'Descuento' => '0.000000',
                                'SubTotal' => '0.000000',
                                'Iva' => '0.000000',
                                'Total' => '0.000000',
                                'CostoTotal' => '0.000000',
                                'Utilidad' => '0.000000',
                                'Requisicion' => '',
                                'Cotizacion' => ''
                            ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "TRASPASOS";
        $BitacoraDocumento->Movimiento = $request->traspasodesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Traspaso->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($Traspaso);
    }

    //obtener registro
    public function traspasos_obtener_traspaso(Request $request){
        $traspaso = Traspaso::where('Traspaso', $request->traspasomodificar)->first();
        $almacende = Almacen::where('Numero', $traspaso->De)->first();
        $almacena="";
        $ordentrabajo="";
        $cliente="";
        $fechaorden="";
        if($traspaso->A > 0){
            $almacena = Almacen::where('Numero', $traspaso->A)->first();
        }else if($traspaso->Orden != ""){
            $ordentrabajo = OrdenTrabajo::where('Orden', $traspaso->Orden)->first();
            $cliente = Cliente::where('Numero', $ordentrabajo->Cliente)->first();
            $fechaorden = Helpers::formatoinputdatetime($ordentrabajo->Fecha);
        }
        //detalles
        $detallestraspaso= TraspasoDetalle::where('Traspaso', $request->traspasomodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallestraspaso = TraspasoDetalle::where('Traspaso', $request->traspasomodificar)->count();
        if($numerodetallestraspaso > 0){
            $filasdetallestraspaso = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo="modificacion";
            foreach($detallestraspaso as $dt){
                $producto = Producto::where('Codigo', $dt->Codigo)->first();
                $Existencia = Existencia::where('Codigo', $dt->Codigo)->where('Almacen', $traspaso->De)->first();
                $parsleymax = $Existencia->Existencias+$dt->Cantidad;
                $filasdetallestraspaso= $filasdetallestraspaso.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dt->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dt->Codigo.'" readonly>'.$dt->Codigo.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.htmlspecialchars($dt->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dt->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dt->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dt->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-existencias="'.$parsleymax.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');">'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dt->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dt->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Utilidad).'" data-parsley-utilidad="0.'.$this->numerocerosconfiguradosinputnumberstep.'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl observacionespartida" name="observacionespartida[]" value="'.$dt->Obs.'" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm requisicionpartida" name="requisicionpartida[]" value="'.$dt->Requisicion.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cotizacionpartida" name="cotizacionpartida[]" value="'.$dt->Cotizacion.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$dt->Moneda.'" readonly data-parsley-length="[1, 3]"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'.Helpers::convertirvalorcorrecto($dt->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipodecambiopartida" name="tipodecambiopartida[]" value="'.Helpers::convertirvalorcorrecto($dt->TipoDeCambio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallestraspaso = '';
        } 
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($traspaso->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($traspaso->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($traspaso->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }        
        $data = array(
            "traspaso" => $traspaso,
            "almacende" => $almacende,
            "almacena" => $almacena,
            "ordentrabajo" => $ordentrabajo,
            "cliente" => $cliente,
            "filasdetallestraspaso" => $filasdetallestraspaso,
            "numerodetallestraspaso" => $numerodetallestraspaso,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdatetime($traspaso->Fecha),
            "fechaorden" => $fechaorden,
            "importe" => Helpers::convertirvalorcorrecto($traspaso->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($traspaso->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($traspaso->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($traspaso->Iva),
            "total" => Helpers::convertirvalorcorrecto($traspaso->Total),
            "costo" => Helpers::convertirvalorcorrecto($traspaso->Costo),
            "utilidad" => Helpers::convertirvalorcorrecto($traspaso->Utilidad),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }

    //guardar modificacion 
    public function traspasos_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $traspaso = $request->folio.'-'.$request->serie;
        $Traspaso = Traspaso::where('Traspaso', $traspaso)->first();
        //modificar totales orden trabajo IMPORTANTE QUE ESTE AQUI NO MOVER ESTE CODIGO
        if($Traspaso->Orden != ""){
            $TraspasoAnterior = Traspaso::where('Traspaso', $traspaso)->first();
            $OrdenTrabajoAnterior = OrdenTrabajo::where('Orden', $TraspasoAnterior->Orden)->first();
            OrdenTrabajo::where('Orden', $TraspasoAnterior->Orden)
                        ->update([
                            'Importe' => $OrdenTrabajoAnterior->Importe - $TraspasoAnterior->Importe + $request->importe,
                            'Descuento' => $OrdenTrabajoAnterior->Descuento - $TraspasoAnterior->Descuento + $request->descuento,
                            'SubTotal' => $OrdenTrabajoAnterior->SubTotal - $TraspasoAnterior->SubTotal + $request->subtotal,
                            'Iva' => $OrdenTrabajoAnterior->Iva - $TraspasoAnterior->Iva + $request->iva,
                            'Total' => $OrdenTrabajoAnterior->Total - $TraspasoAnterior->Total + $request->total,
                            'Costo' => $OrdenTrabajoAnterior->Costo - $TraspasoAnterior->Costo + $request->costo,
                            'Utilidad' => $OrdenTrabajoAnterior->Utilidad - $TraspasoAnterior->Utilidad + $request->utilidad
                        ]);            
        }
        //validar si las partidas en las modiifcacion son las mismas que los detalles de los traspasos
        // si no son las mismas comparar y eliminar las partidas que corresponden en la tabla detalles de OrdenesTrabajo y Traspasos
        //array partidas antes de modificacion
        $ArrayDetallesTraspasoAnterior = Array();
        $DetallesTraspasoAnterior = TraspasoDetalle::where('Traspaso', $traspaso)->get();
        foreach($DetallesTraspasoAnterior as $detalle){
            array_push($ArrayDetallesTraspasoAnterior, $detalle->Traspaso.'#'.$detalle->Codigo.'#'.$detalle->Item);
        }
        //array partida despues de modificacion
        $ArrayDetallesTraspasoNuevo = Array();
        foreach ($request->codigoproductopartida as $key => $nuevocodigo){
            if($request->agregadoen [$key] == 'NA'){
                array_push($ArrayDetallesTraspasoNuevo, $traspaso.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
            } 
        }  
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesTraspasoAnterior, $ArrayDetallesTraspasoNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                $detalletraspaso = TraspasoDetalle::where('Traspaso', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->first();
                //sumar existencias a almacen principal
                $SumarExistenciaAlmacenDe = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacende)->first();
                $SumarExistenciaNuevaAlmacenDe = $SumarExistenciaAlmacenDe->Existencias + $detalletraspaso->Cantidad;
                Existencia::where('Codigo', $explode_d[1])
                            ->where('Almacen', $request->numeroalmacende)
                            ->update([
                                'Existencias' => $SumarExistenciaNuevaAlmacenDe
                            ]);
                //si el traspaso se realizo a otro almacen
                if($Traspaso->A > 0){
                    //restar existencias a almacen foraneo
                    $RestarExistenciasAlmacenA = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacena)->first();
                    $RestarExistenciaNuevaAlmacenA = $RestarExistenciasAlmacenA->Existencias - $detalletraspaso->Cantidad;
                    Existencia::where('Codigo', $explode_d[1])
                                ->where('Almacen', $request->numeroalmacena)
                                ->update([
                                    'Existencias' => $RestarExistenciaNuevaAlmacenA
                                ]);
                }
                if($Traspaso->Orden != ""){
                    $OrdenTrabajoDetalle = OrdenTrabajoDetalle::where('Traspaso', $explode_d[0])->where('Codigo', $explode_d[1])->forceDelete();
                    if($detalletraspaso->Requisicion != ""){
                        //sumar surtir detalle requisicion
                        $RequisicionDetalle = RequisicionDetalle::where('Requisicion', $detalletraspaso->Requisicion)->where('Codigo', $detalletraspaso->Codigo)->first();
                        $Surtir = $RequisicionDetalle->Surtir+$detalletraspaso->Cantidad;
                        RequisicionDetalle::where('Requisicion', $detalletraspaso->Requisicion)
                                            ->where('Codigo', $detalletraspaso->Codigo)
                                            ->update([
                                                'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                            ]);
                        //modificar el status de la requisicion a SURTIDO o BACKORDER
                        $detallesrequisicionporsurtir = RequisicionDetalle::where('Requisicion', $detalletraspaso->Requisicion)->where('Surtir', '>', 0)->count();
                        if($detallesrequisicionporsurtir > 0){
                            Requisicion::where('Requisicion', $detalletraspaso->Requisicion)
                                                ->update([
                                                    'Status' => "BACKORDER"
                                                ]);
                        }else{
                            Requisicion::where('Requisicion', $detalletraspaso->Requisicion)
                                                ->update([
                                                    'Status' => "SURTIDO"
                                                ]);
                        }
                    }
                }
                //eliminar detalle del traspaso eliminado
                $eliminardetalletraspaso = TraspasoDetalle::where('Traspaso', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
            }
        }
        //modificar traspaso
        Traspaso::where('Traspaso', $traspaso)
        ->update([
            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Referencia' => $request->referencia,
            'Obs' => $request->observaciones,
            'Importe' => $request->importe,
            'Descuento' => $request->descuento,
            'SubTotal' => $request->subtotal,
            'Iva' => $request->iva,
            'Total' => $request->total,
            'Costo' => $request->costo,
            'Utilidad' => $request->utilidad
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "TRASPASOS";
        $BitacoraDocumento->Movimiento = $traspaso;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Traspaso->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){    
            //if la partida se agrego en la modificacion se agrega en los detalles de traspaso y de orden de trabajo si asi lo requiere
            if($request->agregadoen [$key] == 'modificacion'){     
                $contaritems = TraspasoDetalle::select('Item')->where('Traspaso', $traspaso)->count();
                if($contaritems > 0){
                    $item = TraspasoDetalle::select('Item')->where('Traspaso', $traspaso)->orderBy('Item', 'DESC')->take(1)->get();
                    $ultimoitem = $item[0]->Item+1;
                }else{
                    $ultimoitem = 1;
                }
                $TraspasoDetalle=new TraspasoDetalle;
                $TraspasoDetalle->Traspaso = $traspaso;
                $TraspasoDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $TraspasoDetalle->Codigo = $codigoproductopartida;
                $TraspasoDetalle->Descripcion = $request->descripcionproductopartida [$key];
                $TraspasoDetalle->Unidad = $request->unidadproductopartida [$key];
                $TraspasoDetalle->Cantidad =  $request->cantidadpartida [$key];
                $TraspasoDetalle->Precio =  $request->preciopartida [$key];
                $TraspasoDetalle->Importe =  $request->importepartida [$key];
                $TraspasoDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
                $TraspasoDetalle->Descuento =  $request->descuentopesospartida  [$key];
                $TraspasoDetalle->SubTotal =  $request->subtotalpartida [$key];
                $TraspasoDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
                $TraspasoDetalle->Iva =  $request->ivapesospartida [$key];
                $TraspasoDetalle->Total =  $request->totalpesospartida [$key];
                $TraspasoDetalle->Costo =  $request->costopartida [$key];
                $TraspasoDetalle->CostoTotal =  $request->costototalpartida [$key];
                $TraspasoDetalle->Utilidad =  $request->utilidadpartida [$key];
                $TraspasoDetalle->Moneda =  $request->monedapartida [$key];
                $TraspasoDetalle->Item = $ultimoitem;
                $TraspasoDetalle->save();
                //restar existencias del almacen que se traspaso
                $ContarExistenciaAlmacenDe = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacende)->count();
                if($ContarExistenciaAlmacenDe > 0){
                    $ExistenciaAlmacenDe = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacende)->first();
                    $ExistenciaNuevaAlmacenDe = $ExistenciaAlmacenDe->Existencias - $request->cantidadpartida [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacende)
                                ->update([
                                    'Existencias' => $ExistenciaNuevaAlmacenDe
                                ]);
                }
                //si el traspaso sera a otro almacen
                if($request->numeroalmacena > 0){
                    //agregar existencias al almacen al que se traspaso
                    $ContarExistenciaAlmacenA = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacena)->count();
                    if($ContarExistenciaAlmacenA > 0){
                        $ExistenciaAlmacenA = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacena)->first();
                        $ExistenciaNuevaAlmacenA = $ExistenciaAlmacenA->Existencias + $request->cantidadpartida [$key];
                        Existencia::where('Codigo', $codigoproductopartida)
                                    ->where('Almacen', $request->numeroalmacena)
                                    ->update([
                                        'Existencias' => $ExistenciaNuevaAlmacenA
                                    ]);
                    }else{
                        $ExistenciaAlmacenA = new Existencia;
                        $ExistenciaAlmacenA->Codigo = $codigoproductopartida;
                        $ExistenciaAlmacenA->Almacen = $request->numeroalmacena;
                        $ExistenciaAlmacenA->Existencias = $request->cantidadpartida [$key];
                        $ExistenciaAlmacenA->save();
                    }
                }
                //si el traspaso sera para una orden de trabajo
                if($request->orden != ""){
                    $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->orden)->first();
                    $UltimaPartidaOrdenTrabajoDetalle = OrdenTrabajoDetalle::select('Partida')->where('Orden', $request->orden)->orderBy('Partida', 'DESC')->take(1)->get();
                    $UltimaPartida = $UltimaPartidaOrdenTrabajoDetalle[0]->Partida+1;
                    $OrdenTrabajoDetalle = new OrdenTrabajoDetalle;
                    $OrdenTrabajoDetalle->Orden=$request->orden;
                    $OrdenTrabajoDetalle->Cliente=$OrdenTrabajo->Cliente;
                    $OrdenTrabajoDetalle->Agente=$OrdenTrabajo->Agente;
                    $OrdenTrabajoDetalle->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
                    $OrdenTrabajoDetalle->Codigo=$codigoproductopartida;
                    $OrdenTrabajoDetalle->Descripcion=$request->descripcionproductopartida [$key];
                    $OrdenTrabajoDetalle->Unidad=$request->unidadproductopartida [$key];
                    $OrdenTrabajoDetalle->Cantidad=$request->cantidadpartida [$key];
                    $OrdenTrabajoDetalle->Precio=$request->preciopartida [$key];
                    $OrdenTrabajoDetalle->Importe=$request->importepartida [$key];
                    $OrdenTrabajoDetalle->Dcto=$request->descuentoporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Descuento= $request->descuentopesospartida  [$key];
                    $OrdenTrabajoDetalle->SubTotal=$request->subtotalpartida [$key];
                    $OrdenTrabajoDetalle->Impuesto=$request->ivaporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Iva=$request->ivapesospartida [$key];
                    $OrdenTrabajoDetalle->Total=$request->totalpesospartida [$key];
                    $OrdenTrabajoDetalle->Costo=$request->costopartida [$key];
                    $OrdenTrabajoDetalle->CostoTotal=$request->costototalpartida [$key];
                    $OrdenTrabajoDetalle->Utilidad=$request->utilidadpartida [$key];
                    $OrdenTrabajoDetalle->Departamento="REFACCIONES";
                    $OrdenTrabajoDetalle->Cargo="REFACCIONES";
                    $OrdenTrabajoDetalle->Traspaso=$traspaso;
                    $OrdenTrabajoDetalle->Item=$ultimoitem;
                    $OrdenTrabajoDetalle->Usuario=Auth::user()->user;
                    //$OrdenTrabajoDetalle->Facturar="S";
                    $OrdenTrabajoDetalle->Almacen=$request->numeroalmacende;
                    $OrdenTrabajoDetalle->Partida=$UltimaPartida;
                    $OrdenTrabajoDetalle->save();
                    $UltimaPartida++;
                }
                $ultimoitem++;
            }else{
                //si la partida no se agrego en la modificacion solo se modifican los datos
                //modificar detalle
                TraspasoDetalle::where('Traspaso', $traspaso)
                ->where('Item', $request->itempartida [$key])
                ->update([
                    'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                    'Cantidad' =>  $request->cantidadpartida [$key],
                    'Precio' =>  $request->preciopartida [$key],
                    'Importe' =>  $request->importepartida [$key],
                    'Dcto' =>  $request->descuentoporcentajepartida [$key],
                    'Descuento' =>  $request->descuentopesospartida  [$key],
                    'SubTotal' =>  $request->subtotalpartida [$key],
                    'Impuesto' =>  $request->ivaporcentajepartida [$key],
                    'Iva' =>  $request->ivapesospartida [$key],
                    'Total' =>  $request->totalpesospartida [$key],
                    'Costo' =>  $request->costopartida [$key],
                    'CostoTotal' =>  $request->costototalpartida [$key],
                    'Utilidad' =>  $request->utilidadpartida [$key],
                    'Moneda' =>  $request->monedapartida [$key]
                ]);
                //sumar existencias a almacen principal
                $SumarExistenciaAlmacenDe = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacende)->first();
                $SumarExistenciaNuevaAlmacenDe = $SumarExistenciaAlmacenDe->Existencias + $request->cantidadpartidadb [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->numeroalmacende)
                            ->update([
                                'Existencias' => $SumarExistenciaNuevaAlmacenDe
                            ]);
                //restar existencias a almacen principal
                $RestarExistenciaAlmacenDe = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacende)->first();
                $RestarExistenciaNuevaAlmacenDe = $RestarExistenciaAlmacenDe->Existencias - $request->cantidadpartida [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->numeroalmacende)
                            ->update([
                                'Existencias' => $RestarExistenciaNuevaAlmacenDe
                            ]);
                //si el traspaso se realizo a otro almacen
                if($Traspaso->A > 0){
                    //restar existencias a almacen foraneo
                    $RestarExistenciasAlmacenA = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacena)->first();
                    $RestarExistenciaNuevaAlmacenA = $RestarExistenciasAlmacenA->Existencias - $request->cantidadpartidadb [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacena)
                                ->update([
                                    'Existencias' => $RestarExistenciaNuevaAlmacenA
                                ]);
                    //sumar existencias a almacen foraneo
                    $SumarExistenciaAlmacenA = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacena)->first();
                    $SumarExistenciaNuevaAlmacenA = $SumarExistenciaAlmacenA->Existencias + $request->cantidadpartida [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacena)
                                ->update([
                                    'Existencias' => $SumarExistenciaNuevaAlmacenA
                                ]);
                }
                //si el traspaso se realizo a una orden de trabajo se modifican los detalles de la orden de trabajo
                if($Traspaso->Orden != ""){
                    $PrimerPartidaTraspasoOrdenTrabajoDetalle = OrdenTrabajoDetalle::select('Partida')->where('Orden', $Traspaso->Orden)->where('Traspaso', $traspaso)->orderBy('Partida', 'ASC')->take(1)->get();
                    $Partida = $PrimerPartidaTraspasoOrdenTrabajoDetalle[0]->Partida;
                    OrdenTrabajoDetalle::where('Traspaso', $traspaso)
                    ->where('Item', $request->itempartida [$key])
                    ->update([
                        'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                        'Cantidad' => $request->cantidadpartida [$key],
                        'Precio' => $request->preciopartida [$key],
                        'Importe' => $request->importepartida [$key],
                        'Dcto' => $request->descuentoporcentajepartida [$key],
                        'Descuento' => $request->descuentopesospartida  [$key],
                        'SubTotal' => $request->subtotalpartida [$key],
                        'Impuesto' => $request->ivaporcentajepartida [$key],
                        'Iva' => $request->ivapesospartida [$key],
                        'Total' => $request->totalpesospartida [$key],
                        'Costo' => $request->costopartida [$key],
                        'CostoTotal' => $request->costototalpartida [$key],
                        'Utilidad' => $request->utilidadpartida [$key]
                    ]);
                    $Partida++;
                    $detalletraspaso = TraspasoDetalle::where('Traspaso', $traspaso)->where('Item', $request->itempartida [$key])->first();
                    if($detalletraspaso->Requisicion != ""){
                        //sumar surtir detalle requisicion
                        $RequisicionDetalle = RequisicionDetalle::where('Requisicion', $detalletraspaso->Requisicion)->where('Codigo', $detalletraspaso->Codigo)->first();
                        $Surtir = $RequisicionDetalle->Surtir+$detalletraspaso->Cantidad;
                        RequisicionDetalle::where('Requisicion', $detalletraspaso->Requisicion)
                                            ->where('Codigo', $detalletraspaso->Codigo)
                                            ->update([
                                                'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                            ]);

                        //restar surtir detalle requisicion
                        $RequisicionDetalle = RequisicionDetalle::where('Requisicion', $detalletraspaso->Requisicion)->where('Codigo', $detalletraspaso->Codigo)->first();
                        $Surtir = $RequisicionDetalle->Surtir-$request->cantidadpartida [$key];
                        RequisicionDetalle::where('Requisicion', $detalletraspaso->Requisicion)
                                            ->where('Codigo', $detalletraspaso->Codigo)
                                            ->update([
                                                'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                            ]);
                        //modificar el status de la requisicion a SURTIDO o BACKORDER
                        $detallesrequisicionporsurtir = RequisicionDetalle::where('Requisicion', $detalletraspaso->Requisicion)->where('Surtir', '>', 0)->count();
                        if($detallesrequisicionporsurtir > 0){
                            Requisicion::where('Requisicion', $detalletraspaso->Requisicion)
                                                ->update([
                                                    'Status' => "BACKORDER"
                                                ]);
                        }else{
                            Requisicion::where('Requisicion', $detalletraspaso->Requisicion)
                                                ->update([
                                                    'Status' => "SURTIDO"
                                                ]);
                        }
                    }
                }
            }
        }
        return response()->json($Traspaso);
    }

    //buscar folio
    public function traspasos_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = Traspaso::where('Traspaso', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Traspaso .'\')"><i class="material-icons">done</i></div> ';
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
    
    //generar documento pdf
    public function traspasos_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $traspasos = Traspaso::whereIn('Traspaso', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $traspasos = Traspaso::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        foreach ($traspasos as $t){
            $data=array();
            $traspasodetalle = TraspasoDetalle::where('Traspaso', $t->Traspaso)->get();
            $datadetalle=array();
            foreach($traspasodetalle as $td){
                $producto = Producto::where('Codigo', $td->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($td->Cantidad),
                    "codigodetalle"=>$td->Codigo,
                    "descripciondetalle"=>$td->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($td->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($td->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($td->SubTotal)
                );
            } 
            $almacende = Almacen::where('Numero', $t->De)->first();
            $almacena = '';
            $orden = '';
            if($t->A > 0){
                $almacenforaneo = Almacen::where('Numero', $t->A)->first();
                $almacena = $almacenforaneo->Nombre.' ('.$almacenforaneo->Numero.')';
            }
            $data[]=array(
                      "traspaso"=>$t,
                      "descuentotraspaso"=>Helpers::convertirvalorcorrecto($t->Descuento),
                      "subtotaltraspaso"=>Helpers::convertirvalorcorrecto($t->SubTotal),
                      "ivatraspaso"=>Helpers::convertirvalorcorrecto($t->Iva),
                      "totaltraspaso"=>Helpers::convertirvalorcorrecto($t->Total),
                      "almacende" => $almacende,
                      "almacena" => $almacena,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            //dd($data);
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.traspasos.formato_pdf_traspasos', compact('data'))
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$t->Traspaso.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($traspasos as $tra){
            $ArchivoPDF = "PDF".$tra->Traspaso.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
        }
        $pdfMerger->merge(); //unirlos
        $pdfMerger->save("Traspasos.pdf", "browser");//mostrarlos en el navegador
    }

    //generacion de formato en PDF
    public function traspasos_generar_pdfs_indiv($documento){
        $traspasos = Traspaso::where('Traspaso', $documento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($traspasos as $t){
            $traspasodetalle = TraspasoDetalle::where('Traspaso', $t->Traspaso)->get();
            $datadetalle=array();
            foreach($traspasodetalle as $td){
                $producto = Producto::where('Codigo', $td->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($td->Cantidad),
                    "codigodetalle"=>$td->Codigo,
                    "descripciondetalle"=>$td->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($td->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($td->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($td->SubTotal)
                );
            } 
            $almacende = Almacen::where('Numero', $t->De)->first();
            $almacena = '';
            $orden = '';
            if($t->A > 0){
                $almacenforaneo = Almacen::where('Numero', $t->A)->first();
                $almacena = $almacenforaneo->Nombre.' ('.$almacenforaneo->Numero.')';
            }
            $data[]=array(
                      "traspaso"=>$t,
                      "descuentotraspaso"=>Helpers::convertirvalorcorrecto($t->Descuento),
                      "subtotaltraspaso"=>Helpers::convertirvalorcorrecto($t->SubTotal),
                      "ivatraspaso"=>Helpers::convertirvalorcorrecto($t->Iva),
                      "totaltraspaso"=>Helpers::convertirvalorcorrecto($t->Total),
                      "almacende" => $almacende,
                      "almacena" => $almacena,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.traspasos.formato_pdf_traspasos', compact('data'))
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
    public function traspasos_obtener_datos_envio_email(Request $request){
        $traspaso = Traspaso::where('Traspaso', $request->documento)->first();
        $data = array(
            'traspaso' => $traspaso,
            'emailde' => Config::get('mail.from.address'),
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function traspasos_enviar_pdfs_email(Request $request){
        $traspasos = Traspaso::where('Traspaso', $request->emaildocumento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($traspasos as $t){
            $traspasodetalle = TraspasoDetalle::where('Traspaso', $t->Traspaso)->get();
            $datadetalle=array();
            foreach($traspasodetalle as $td){
                $producto = Producto::where('Codigo', $td->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($td->Cantidad),
                    "codigodetalle"=>$td->Codigo,
                    "descripciondetalle"=>$td->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($td->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($td->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($td->SubTotal)
                );
            } 
            $almacende = Almacen::where('Numero', $t->De)->first();
            $almacena = '';
            $orden = '';
            if($t->A > 0){
                $almacenforaneo = Almacen::where('Numero', $t->A)->first();
                $almacena = $almacenforaneo->Nombre.' ('.$almacenforaneo->Numero.')';
            }
            $data[]=array(
                      "traspaso"=>$t,
                      "descuentotraspaso"=>Helpers::convertirvalorcorrecto($t->Descuento),
                      "subtotaltraspaso"=>Helpers::convertirvalorcorrecto($t->SubTotal),
                      "ivatraspaso"=>Helpers::convertirvalorcorrecto($t->Iva),
                      "totaltraspaso"=>Helpers::convertirvalorcorrecto($t->Total),
                      "almacende" => $almacende,
                      "almacena" => $almacena,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.traspasos.formato_pdf_traspasos', compact('data'))
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            //enviar correo electrónico	
            $nombre = 'Receptor envio de correos';
            $receptor = $request->emailpara;
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailasunto;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $correos, $asunto, $pdf, $emaildocumento) {
                $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                        ->cc($correos)
                        ->subject($asunto)
                        ->attachData($pdf->output(), "TraspasoNo".$emaildocumento.".pdf");
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

    //exportar excel
    public function traspasos_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new TraspasosExport($this->campos_consulta,$request->periodo), "traspasos-".$request->periodo.".xlsx");   
  
    }

    //guardar configuracion tabla
    public function traspasos_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        Configuracion_Tabla::where('tabla', 'Traspasos')
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
        return redirect()->route('traspasos');
    }
    
}
