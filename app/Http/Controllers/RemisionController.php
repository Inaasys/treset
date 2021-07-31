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
use App\Exports\RemisionesExport;
use App\Remision;
use App\RemisionDetalle;
use App\CotizacionProducto;
use App\CotizacionProductoDetalle;
use App\Factura;
use App\FacturaDetalle;
use App\Serie;
use App\Almacen;
use App\Cliente;
use App\Agente;
use App\TipoCliente;
use App\TipoOrdenCompra;
use App\TipoUnidad;
use App\Existencia;
use App\BitacoraDocumento;
use App\Producto;
use App\Marca;
use App\Configuracion_Tabla;
use App\VistaRemision;
use App\VistaObtenerExistenciaProducto;
use App\Cotizacion;
use App\CotizacionDetalle;
use Config;
use Mail;

class RemisionController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'Remisiones')->first();
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

    public function remisiones(){
        $serieusuario = 'A';
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('remisiones_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('remisiones_exportar_excel');
        $rutacreardocumento = route('remisiones_generar_pdfs');
        return view('registros.remisiones.remisiones', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }

    //obtener registros tabla
    public function remisiones_obtener(Request $request){
        if($request->ajax()){
            $periodo = $request->periodo;
            //$data = VistaRemision::select($this->campos_consulta)->where('Periodo', $periodo)->orderBy('Fecha', 'DESC')->orderBy('Serie', 'ASC')->orderBy('Folio', 'DESC')->get();
            $data = VistaRemision::select($this->campos_consulta)->where('Periodo', $periodo);
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
                        $operaciones =  '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Remision .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Remision .'\')">Bajas</a></li>'.
                                                '<li><a href="'.route('remisiones_generar_pdfs_indiv',$data->Remision).'" target="_blank">Ver Documento PDF</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Remision .'\')">Enviar Documento por Correo</a></li>'.
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
                    ->addColumn('tipocambio', function($data){ return $data->TipoCambio; })
                    ->addColumn('supago', function($data){ return $data->SuPago; })
                    ->addColumn('enefectivo', function($data){ return $data->EnEfectivo; })
                    ->addColumn('entarjetas', function($data){ return $data->EnTarjetas; })
                    ->addColumn('envales', function($data){ return $data->EnVales; })
                    ->addColumn('encheque', function($data){ return $data->EnCheque; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener series documento
    public function remisiones_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'Remisiones')->where('Usuario', Auth::user()->user)->get();
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
    public function remisiones_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Remision',$request->Serie);
        return response()->json($folio);
    }
    //obtener ultimo folio
    public function remisiones_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Remision',$request->serie);
        return response()->json($folio);
    }

    //obtener clientes
    public function remisiones_obtener_clientes(Request $request){
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
    public function remisiones_obtener_cliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $credito = '';
        $saldo = '';
        $numeroagente = '';
        $nombreagente = '';
        $existecliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->first();
            $agente = Agente::where('Numero', $cliente->Agente)->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
            $credito = Helpers::convertirvalorcorrecto($cliente->Credito);
            $saldo = Helpers::convertirvalorcorrecto($cliente->Saldo);
            $numeroagente = $agente->Numero;
            $nombreagente = $agente->Nombre;
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

    //obtener oagentes
    public function remisiones_obtener_agentes(Request $request){
        if($request->ajax()){
            $data = Agente::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
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
    public function remisiones_obtener_agente_por_numero(Request $request){
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
            'nombre' => $nombre
        );
        return response()->json($data); 
    }

    //obtener almacenes
    public function remisiones_obtener_almacenes(Request $request){
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
    public function remisiones_obtener_almacen_por_numero(Request $request){
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

    //obtener tipos cliente
    public function remisiones_obtener_tipos_cliente(){
        $tipos_cliente = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', '<>', 'GASTOS')->Where('Nombre', '<>', 'TOT')->get();
        $select_tipos_cliente = "<option disabled hidden>Selecciona...</option>";
        foreach($tipos_cliente as $tipo){
            $select_tipos_cliente = $select_tipos_cliente."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_cliente);
    }

    //obtener tipos unidad
    public function remisiones_obtener_tipos_unidad(){
        $tipos_unidad = TipoUnidad::where('STATUS', 'ALTA')->orderBy('Numero', 'ASC')->get();
        $select_tipos_unidad = "<option disabled hidden>Selecciona...</option>";
        foreach($tipos_unidad as $tipo){
            $select_tipos_unidad = $select_tipos_unidad."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_unidad); 
    }

    //obtener cotizaciones 
    public function remisiones_obtener_cotizaciones(Request $request){
        if($request->ajax()){
            $mesactual = date("m");
            $data = DB::table('Cotizaciones as cot')
                        ->join('Clientes as c', 'c.Numero', '=', 'cot.Cliente')
                        ->select('cot.Cotizacion', 'cot.Folio', 'cot.Fecha', 'cot.Cliente', 'c.Nombre as Nombre', 'cot.Tipo', 'cot.Plazo as Dias', 'cot.Total')
                        ->where('cot.Cliente', $request->numerocliente)
                        ->where('cot.Status', 'POR CARGAR')
                        ->whereMonth('cot.Fecha', '=', $mesactual)
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
    public function remisiones_obtener_cotizacion(Request $request){
        $cotizacion = CotizacionProducto::where('Cotizacion', $request->Cotizacion)->first();
        $almacen = $request->numeroalmacen;
        //detalles cotizacion
        $detallescotizacion = CotizacionProductoDetalle::where('Cotizacion', $request->Cotizacion)->get();
        $numerodetallescotizacion = CotizacionProductoDetalle::where('Cotizacion', $request->Cotizacion)->count();
        if($numerodetallescotizacion > 0){
            $filasdetallescotizacion = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo = "alta";
            foreach($detallescotizacion as $dc){
                $producto = Producto::where('Codigo', $dc->Codigo)->first();
                $Existencia = Existencia::where('Codigo', $dc->Codigo)->where('Almacen', $almacen)->first();
                $parsleymax = $Existencia->Existencias;
                $filasdetallescotizacion= $filasdetallescotizacion.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipo.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dc->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dc->Codigo.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.$dc->Descripcion.'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dc->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dc->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-existencias="'.$parsleymax.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadpartida('.$contadorfilas.',\''.$tipo.'\');">'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.                           
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionespesospartida" name="comisionespesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Utilidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$dc->Moneda.'" readonly data-parsley-length="[1, 3]"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costolistapartida" name="costolistapartida[]" value="'.Helpers::convertirvalorcorrecto($dc->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.$dc->TipoDeCambio.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cotizacionpartida" name="cotizacionpartida[]" value="'.$dc->Cotizacion.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm insumopartida" name="insumopartida[]" value="'.$producto->Insumo.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveprodutopartida" name="claveprodutopartida[]" value="'.$producto->ClaveProducto.'" readonly required></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]" value="'.$producto->ClaveUnidad.'" readonly required></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm mesespartida" name="mesespartida[]" value="'.Helpers::convertirvalorcorrecto($dc->InteresMeses).'" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tasainterespartida" name="tasainterespartida[]" value="'.Helpers::convertirvalorcorrecto($dc->InteresTasa).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm montointerespartida" name="montointerespartida[]" value="'.Helpers::convertirvalorcorrecto($dc->InteresMonto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
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
            "almacen" => $almacen,
            "fecha" => Helpers::formatoinputdatetime($cotizacion->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($cotizacion->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($cotizacion->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($cotizacion->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($cotizacion->Iva),
            "total" => Helpers::convertirvalorcorrecto($cotizacion->Total)
        );
        return response()->json($data);
    }

    //obtener nuevo saldo cliente
    public function remisiones_obtener_nuevo_saldo_cliente(Request $request){
        $cliente = Cliente::where('Numero', $request->numerocliente)->first();
        ///$nuevosaldo = $cliente->Saldo + $request->total;
        return response()->json(Helpers::convertirvalorcorrecto($cliente->Saldo));
    }

    //obtener prudoctos
    public function remisiones_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $numeroalmacen = $request->numeroalmacen;
            $tipooperacion = $request->tipooperacion;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%');
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
    //obtener producto por codigo
    public function remisiones_obtener_producto_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        $numeroalmacen = $request->numeroalmacen;
        $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('Almacen', $numeroalmacen)->count();
        if($contarproductos > 0){
            $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('Almacen', $numeroalmacen)->first();
            $data = array(
                'Codigo' => $producto->Codigo,
                'Producto' => htmlspecialchars($producto->Producto, ENT_QUOTES),
                'Unidad' => $producto->Unidad,
                'Costo' => Helpers::convertirvalorcorrecto($producto->Costo),
                'Impuesto' => Helpers::convertirvalorcorrecto($producto->Impuesto),
                'SubTotal' => Helpers::convertirvalorcorrecto($producto->SubTotal),
                'Existencias' => Helpers::convertirvalorcorrecto($producto->Existencias),
                'Insumo' => $producto->Insumo,
                'ClaveProducto' => $producto->ClaveProducto,
                'ClaveUnidad' => $producto->ClaveUnidad,
                'CostoDeLista' => Helpers::convertirvalorcorrecto($producto->CostoDeLista),
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
                'Insumo' => '',
                'ClaveProducto' => '',
                'ClaveUnidad' => '',
                'CostoDeLista' => '',
                'contarproductos' => $contarproductos
            );
        }
        return response()->json($data);
    }

    //obtener existencias
    public function remisiones_obtener_existencias_almacen(Request $request){
        $ContarExistencia = Existencia::where('Codigo', $request->codigo)->where('Almacen', $request->numeroalmacen)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::where('Codigo', $request->codigo)->where('Almacen',$request->numeroalmacen)->first();
            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $Existencias = Helpers::convertirvalorcorrecto(0);
        }
        return response()->json($Existencias);
    }

    //guardar
    public function remisiones_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo folio de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\Remision',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $remision = $folio.'-'.$request->serie;
        $Remision = new Remision;
        $Remision->Remision=$remision;
        $Remision->Serie=$request->serie;
        $Remision->Folio=$folio;
        $Remision->Cliente=$request->numerocliente;
        $Remision->Agente=$request->numeroagente;
        $Remision->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $Remision->Plazo=$request->plazo;
        $Remision->Tipo=$request->tipo;
        $Remision->Unidad=$request->unidad;
        $Remision->Pedido=$request->pedido;
        $Remision->Solicita=$request->solicitadopor;
        $Remision->Referencia=$request->referencia;
        $Remision->Destino=$request->destinodelpedido;
        $Remision->Almacen=$request->numeroalmacen;
        $Remision->Os=$request->ordenservicio;
        $Remision->Eq=$request->equipo;
        $Remision->Rq=$request->requisicion;
        $Remision->Importe=$request->importe;
        $Remision->Descuento=$request->descuento;
        $Remision->SubTotal=$request->subtotal;
        $Remision->Iva=$request->iva;
        $Remision->Total=$request->total;
        $Remision->Costo=$request->costo;
        $Remision->Comision=$request->comision;
        $Remision->Utilidad=$request->utilidad;
        $Remision->Obs=$request->observaciones;
        $Remision->Hora=Helpers::fecha_exacta_accion_datetimestring();
        $Remision->Status="POR FACTURAR";
        $Remision->Usuario=Auth::user()->user;
        $Remision->Periodo=$this->periodohoy;
        $Remision->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "REMISIONES";
        $BitacoraDocumento->Movimiento = $remision;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "POR FACTURAR";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
            $RemisionDetalle=new RemisionDetalle;
            $RemisionDetalle->Remision = $remision;
            $RemisionDetalle->Cliente = $request->numerocliente;
            $RemisionDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $RemisionDetalle->Codigo = $codigoproductopartida;
            $RemisionDetalle->Descripcion = $request->descripcionproductopartida [$key];
            $RemisionDetalle->Unidad = $request->unidadproductopartida [$key];
            $RemisionDetalle->Cantidad =  $request->cantidadpartida [$key];
            $RemisionDetalle->Precio =  $request->preciopartida [$key];
            $RemisionDetalle->Importe =  $request->importepartida [$key];
            $RemisionDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
            $RemisionDetalle->Descuento =  $request->descuentopesospartida  [$key];
            $RemisionDetalle->SubTotal =  $request->subtotalpartida [$key];
            $RemisionDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
            $RemisionDetalle->Iva =  $request->ivapesospartida [$key];
            $RemisionDetalle->Total =  $request->totalpesospartida [$key];
            $RemisionDetalle->Costo =  $request->costopartida [$key];
            $RemisionDetalle->CostoTotal =  $request->costototalpartida [$key];
            $RemisionDetalle->Com =  $request->comisionporcentajepartida [$key];
            $RemisionDetalle->Comision =  $request->comisionespesospartida [$key];
            $RemisionDetalle->Utilidad =  $request->utilidadpartida [$key];
            $RemisionDetalle->Moneda =  $request->monedapartida [$key];
            $RemisionDetalle->CostoDeLista =  $request->costolistapartida [$key];
            $RemisionDetalle->TipoDeCambio =  $request->tipocambiopartida [$key];
            $RemisionDetalle->Cotizacion =  $request->cotizacionpartida [$key];
            $RemisionDetalle->Insumo =  $request->insumopartida [$key];
            $RemisionDetalle->InteresMeses =  $request->mesespartida [$key];
            $RemisionDetalle->InteresTasa =  $request->tasainterespartida  [$key];
            $RemisionDetalle->InteresMonto =  $request->montointerespartida  [$key];
            $RemisionDetalle->Item = $item;
            $RemisionDetalle->save();
            //modificar fechaultimaventa y ultimocosto
            $Producto = Producto::where('Codigo', $codigoproductopartida)->first();
            $Producto->{'Fecha Ultima Venta'} = Carbon::parse($request->fecha)->toDateTimeString();
            $Producto->{'Ultima Venta'} = $request->preciopartida [$key];
            $Producto->save();
            //restar existencias del almacen 
            $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
            if($ContarExistenciaAlmacen > 0){
                $ExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias - $request->cantidadpartida [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->numeroalmacen)
                            ->update([
                                'Existencias' => $ExistenciaNuevaAlmacen
                            ]);
            }
            $item++;
        }
        return response()->json($Remision);
    }

    //verificar baja
    public function remisiones_verificar_baja(Request $request){
        $Remision = Remision::where('Remision', $request->remisiondesactivar)->first();
        $ContarDetallesRemisionFacturados = FacturaDetalle::where('Remision', $request->remisiondesactivar)->count();
        $errores = '';
        if($ContarDetallesRemisionFacturados){
            $DetallesRemisionFacturados = FacturaDetalle::where('Remision', $request->remisiondesactivar)->get();
            foreach($DetallesRemisionFacturados as $detalle){
                $errores = $errores.'Error la remisión no se puede cancelar, porque existen registros de remisiones en la factura No:'.$detalle->Factura.'<br>';
            }  
        }
        $errorescotizacion = '';
        $ContarRemisionCotizados = Cotizacion::where('num_remision', $request->remisiondesactivar)->where('status', 'ALTA')->count();
        if($ContarRemisionCotizados){
            $RemisionCotizados = Cotizacion::where('num_remision', $request->remisiondesactivar)->where('status', 'ALTA')->get();
            foreach($RemisionCotizados as $cot){
                $errorescotizacion = $errorescotizacion.'Error la remisión no se puede cancelar, porque existen registros de remisiones en la cotización No:'.$cot->cotizacion.'<br>';
            }  
        }
        $resultadofechas = Helpers::compararanoymesfechas($Remision->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'errores' => $errores,
            'errorescotizacion' => $errorescotizacion,
            'Status' => $Remision->Status
        );
        return response()->json($data);
    }

    //bajas
    public function remisiones_alta_o_baja(Request $request){
        $Remision = Remision::where('Remision', $request->remisiondesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        Remision::where('Remision', $request->remisiondesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Importe' => '0.000000',
                    'Descuento' => '0.000000',
                    'SubTotal' => '0.000000',
                    'Iva' => '0.000000',
                    'Total' => '0.000000',
                    'Costo' => '0.000000',
                    'Comision' => '0.000000',
                    'Utilidad' => '0.000000'
                ]);
        $detalles = RemisionDetalle::where('Remision', $request->remisiondesactivar)->get();
        foreach($detalles as $detalle){
            //si se utilizo cotizacion regresar a alta el status
            if($detalle->Cotizacion != ''){
                CotizacionProducto::where('Cotizacion', $detalle->Cotizacion)
                        ->update([
                            'Status' => 'ALTA'
                        ]);
            }
            //sumar existencias al almacen
            $ExistenciaAlmacen = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Remision->Almacen)->first();
            $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias+$detalle->Cantidad;
            Existencia::where('Codigo', $detalle->Codigo)
                        ->where('Almacen', $Remision->Almacen)
                        ->update([
                            'Existencias' => $ExistenciaNuevaAlmacen
                        ]);
            //colocar en ceros cantidades
            RemisionDetalle::where('Remision', $request->remisiondesactivar)
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
                                'Com' => '0.000000',
                                'Comision' => '0.000000',
                                'Utilidad' => '0.000000',
                                'Cotizacion' => '',
                            ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "REMISIONES";
        $BitacoraDocumento->Movimiento = $request->remisiondesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Remision->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($Remision);
    }

    //obtener registro
    public function remisiones_obtener_remision(Request $request){
        $remision = Remision::where('Remision', $request->remisionmodificar)->first();
        $cliente = Cliente::where('Numero', $remision->Cliente)->first();
        $agente = Agente::where('Numero', $remision->Agente)->first();
        $almacen = Almacen::where('Numero', $remision->Almacen)->first();
        $tipos_cliente = TipoCliente::where('STATUS', 'ALTA')->orderBy('Numero', 'ASC')->get();
        $select_tipos_cliente = "<option selected disabled hidden>Selecciona...</option>";
        foreach($tipos_cliente as $tipo){
            if($tipo->Nombre == $remision->Tipo){
                $select_tipos_cliente = $select_tipos_cliente."<option value='".$tipo->Nombre."' selected>".$tipo->Nombre."</option>";
            }else{
                $select_tipos_cliente = $select_tipos_cliente."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
            }
        }
        $tipos_unidad = TipoUnidad::where('STATUS', 'ALTA')->orderBy('Numero', 'ASC')->get();
        $select_tipos_unidad = "<option selected disabled hidden>Selecciona...</option>";
        foreach($tipos_unidad as $tipo){
            if($tipo->Nombre == $remision->Unidad){
                $select_tipos_unidad = $select_tipos_unidad."<option value='".$tipo->Nombre."' selected>".$tipo->Nombre."</option>";
            }else{
                $select_tipos_unidad = $select_tipos_unidad."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
            }
        }
        //detalles
        $detallesremision= RemisionDetalle::where('Remision', $request->remisionmodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallesremision = RemisionDetalle::where('Remision', $request->remisionmodificar)->count();
        if($numerodetallesremision > 0){
            $filasdetallesremision = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo="modificacion";
            foreach($detallesremision as $dr){
                $producto = Producto::where('Codigo', $dr->Codigo)->first();
                $Existencia = Existencia::where('Codigo', $dr->Codigo)->where('Almacen', $remision->Almacen)->first();
                $parsleymax = $Existencia->Existencias+$dr->Cantidad;
                $filasdetallesremision= $filasdetallesremision.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dr->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dr->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dr->Codigo.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.$dr->Descripcion.'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dr->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dr->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dr->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-existencias="'.$parsleymax.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadpartida('.$contadorfilas.',\''.$tipo.'\');">'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.                           
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionespesospartida" name="comisionespesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Utilidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$dr->Moneda.'" readonly data-parsley-length="[1, 3]"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costolistapartida" name="costolistapartida[]" value="'.Helpers::convertirvalorcorrecto($dr->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->TipoDeCambio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cotizacionpartida" name="cotizacionpartida[]" value="'.$dr->Cotizacion.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm insumopartida" name="insumopartida[]" value="'.$dr->Insumo.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveprodutopartida" name="claveprodutopartida[]" value="'.$producto->ClaveProducto.'" readonly required></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]" value="'.$producto->ClaveUnidad.'" readonly required></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm mesespartida" name="mesespartida[]" value="'.Helpers::convertirvalorcorrecto($dr->InteresMeses).'" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tasainterespartida" name="tasainterespartida[]" value="'.Helpers::convertirvalorcorrecto($dr->InteresTasa).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm montointerespartida" name="montointerespartida[]" value="'.Helpers::convertirvalorcorrecto($dr->InteresMonto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesremision = '';
        }      
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($remision->Status != 'POR FACTURAR'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($remision->Status != 'POR FACTURAR'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($remision->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }  
        $data = array(
            "remision" => $remision,
            "cliente" => $cliente,
            "agente" => $agente,
            "almacen" => $almacen,
            "select_tipos_cliente" => $select_tipos_cliente,
            "select_tipos_unidad" => $select_tipos_unidad,
            "filasdetallesremision" => $filasdetallesremision,
            "numerodetallesremision" => $numerodetallesremision,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdatetime($remision->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($remision->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($remision->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($remision->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($remision->Iva),
            "total" => Helpers::convertirvalorcorrecto($remision->Total),
            "costo" => Helpers::convertirvalorcorrecto($remision->Costo),
            "utilidad" => Helpers::convertirvalorcorrecto($remision->Utilidad),
            "comision" => Helpers::convertirvalorcorrecto($remision->Comision),
            "credito" => Helpers::convertirvalorcorrecto($cliente->Credito),
            "saldo" => Helpers::convertirvalorcorrecto($cliente->Saldo),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }

    //cambios
    public function remisiones_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $remision = $request->folio.'-'.$request->serie;
        $Remision = Remision::where('Remision', $remision)->first();
        //validar si las partidas en las modiifcacion son las mismas que los detalles de los traspasos
        // si no son las mismas comparar y eliminar las partidas que corresponden en la tabla detalles de OrdenesTrabajo y Traspasos
        //array partidas antes de modificacion
        $ArrayDetallesRemisionAnterior = Array();
        $DetallesRemisionAnterior = RemisionDetalle::where('Remision', $remision)->get();
        foreach($DetallesRemisionAnterior as $detalle){
            //array_push($ArrayDetallesRemisionAnterior, $detalle->Codigo);
            array_push($ArrayDetallesRemisionAnterior, $detalle->Remision.'#'.$detalle->Codigo.'#'.$detalle->Item);
        }
        //array partida despues de modificacion
        $ArrayDetallesRemisionNuevo = Array();
        foreach ($request->codigoproductopartida as $key => $nuevocodigo){
            //array_push($ArrayDetallesRemisionNuevo, $nuevocodigo);
            if($request->agregadoen [$key] == 'NA'){
                array_push($ArrayDetallesRemisionNuevo, $remision.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
            } 
        }  
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesRemisionAnterior, $ArrayDetallesRemisionNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                $detalleremision = RemisionDetalle::where('Remision', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->first();
                //sumar existencias a almacen principal
                $SumarExistenciaAlmacen = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacen)->first();
                $SumarExistenciaNuevaAlmacen = $SumarExistenciaAlmacen->Existencias + $detalleremision->Cantidad;
                Existencia::where('Codigo', $explode_d[1])
                            ->where('Almacen', $request->numeroalmacen)
                            ->update([
                                'Existencias' => $SumarExistenciaNuevaAlmacen
                            ]);

                //eliminar detalle de la remision eliminado
                $eliminardetalleremision = RemisionDetalle::where('Remision', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
            }
        }
        //modificar remision
        Remision::where('Remision', $remision)
        ->update([
            'Cliente' => $request->numerocliente,
            'Agente' => $request->numeroagente,
            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Plazo' => $request->plazo,
            'Tipo' => $request->tipo,
            'Unidad' => $request->unidad,
            'Pedido' => $request->pedido,
            'Solicita' => $request->solicitadopor,
            'Referencia' => $request->referencia,
            'Destino' => $request->destinodelpedido,
            'Os' => $request->ordenservicio,
            'Eq' => $request->equipo,
            'Rq' => $request->requisicion,
            'Obs' => $request->observaciones,
            'Importe' => $request->importe,
            'Descuento' => $request->descuento,
            'SubTotal' => $request->subtotal,
            'Iva' => $request->iva,
            'Total' => $request->total,
            'Costo' => $request->costo,
            'Utilidad' => $request->utilidad,
            'Comision' => $request->comision
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "REMISIONES";
        $BitacoraDocumento->Movimiento = $remision;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Remision->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){    
            //if la partida se agrego en la modificacion se agrega en los detalles de traspaso y de orden de trabajo si asi lo requiere
            if($request->agregadoen [$key] == 'modificacion'){
                $item = RemisionDetalle::select('Item')->where('Remision', $remision)->orderBy('Item', 'DESC')->take(1)->get();
                $ultimoitem = $item[0]->Item+1;
                $RemisionDetalle=new RemisionDetalle;
                $RemisionDetalle->Remision = $remision;
                $RemisionDetalle->Cliente = $request->numerocliente;
                $RemisionDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $RemisionDetalle->Codigo = $codigoproductopartida;
                $RemisionDetalle->Descripcion = $request->descripcionproductopartida [$key];
                $RemisionDetalle->Unidad = $request->unidadproductopartida [$key];
                $RemisionDetalle->Cantidad =  $request->cantidadpartida [$key];
                $RemisionDetalle->Precio =  $request->preciopartida [$key];
                $RemisionDetalle->Importe =  $request->importepartida [$key];
                $RemisionDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
                $RemisionDetalle->Descuento =  $request->descuentopesospartida  [$key];
                $RemisionDetalle->SubTotal =  $request->subtotalpartida [$key];
                $RemisionDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
                $RemisionDetalle->Iva =  $request->ivapesospartida [$key];
                $RemisionDetalle->Total =  $request->totalpesospartida [$key];
                $RemisionDetalle->Costo =  $request->costopartida [$key];
                $RemisionDetalle->CostoTotal =  $request->costototalpartida [$key];
                $RemisionDetalle->Com =  $request->comisionporcentajepartida [$key];
                $RemisionDetalle->Comision =  $request->comisionespesospartida [$key];
                $RemisionDetalle->Utilidad =  $request->utilidadpartida [$key];
                $RemisionDetalle->Moneda =  $request->monedapartida [$key];
                $RemisionDetalle->CostoDeLista =  $request->costolistapartida [$key];
                $RemisionDetalle->Insumo =  $request->insumopartida [$key];
                $RemisionDetalle->InteresMeses =  $request->mesespartida [$key];
                $RemisionDetalle->InteresTasa =  $request->tasainterespartida  [$key];
                $RemisionDetalle->InteresMonto =  $request->montointerespartida  [$key];
                $RemisionDetalle->Item = $ultimoitem;
                $RemisionDetalle->save();
                //restar existencias del almacen 
                $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                if($ContarExistenciaAlmacen > 0){
                    $ExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                    $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias - $request->cantidadpartida [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacen)
                                ->update([
                                    'Existencias' => $ExistenciaNuevaAlmacen
                                ]);
                }
                $ultimoitem++;
            }else{
                //si la partida no se agrego en la modificacion solo se modifican los datos
                //modificar detalle
                RemisionDetalle::where('Remision', $remision)
                ->where('Item', $request->itempartida [$key])
                ->update([
                    'Cliente' => $request->numerocliente,
                    'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                    'Descripcion' => $request->descripcionproductopartida [$key],
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
                    'Com' =>  $request->comisionporcentajepartida [$key],
                    'Comision' =>  $request->comisionespesospartida [$key],
                    'Utilidad' =>  $request->utilidadpartida [$key],
                    'Moneda' =>  $request->monedapartida [$key],
                    'InteresMeses' =>  $request->mesespartida [$key],
                    'InteresTasa' =>  $request->tasainterespartida  [$key],
                    'InteresMonto' =>  $request->montointerespartida  [$key]
                ]);
                //sumar existencias a almacen principal
                $SumarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                $SumarExistenciaNuevaAlmacen = $SumarExistenciaAlmacen->Existencias + $request->cantidadpartidadb [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->numeroalmacen)
                            ->update([
                                'Existencias' => $SumarExistenciaNuevaAlmacen
                            ]);
                //restar existencias del almacen 
                $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                if($ContarExistenciaAlmacen > 0){
                    $ExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                    $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias - $request->cantidadpartida [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacen)
                                ->update([
                                    'Existencias' => $ExistenciaNuevaAlmacen
                                ]);
                }
            }
        }
        return response()->json($Remision);
    }

    //buscar folio
    public function remisiones_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = Remision::where('Remision', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Remision .'\')"><i class="material-icons">done</i></div> ';
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
    public function remisiones_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $remisiones = Remision::whereIn('Remision', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $remisiones = Remision::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($remisiones as $r){
            $remisiondetalle = RemisionDetalle::where('Remision', $r->Remision)->get();
            $datadetalle=array();
            foreach($remisiondetalle as $rd){
                $producto = Producto::where('Codigo', $rd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($rd->Cantidad),
                    "codigodetalle"=>$rd->Codigo,
                    "descripciondetalle"=>$rd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($rd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($rd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($rd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $r->Cliente)->first();
            $agente = Agente::where('Numero', $r->Agente)->first();
            $data[]=array(
                      "remision"=>$r,
                      "descuentoremision"=>Helpers::convertirvalorcorrecto($r->Descuento),
                      "subtotalremision"=>Helpers::convertirvalorcorrecto($r->SubTotal),
                      "ivaremision"=>Helpers::convertirvalorcorrecto($r->Iva),
                      "totalremision"=>Helpers::convertirvalorcorrecto($r->Total),
                      "cliente" => $cliente,
                      "agente" => $agente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.remisiones.formato_pdf_remisiones', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //generacion de formato en PDF
    public function remisiones_generar_pdfs_indiv($documento){
        $remisiones = Remision::where('Remision', $documento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($remisiones as $r){
            $remisiondetalle = RemisionDetalle::where('Remision', $r->Remision)->get();
            $datadetalle=array();
            foreach($remisiondetalle as $rd){
                $producto = Producto::where('Codigo', $rd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($rd->Cantidad),
                    "codigodetalle"=>$rd->Codigo,
                    "descripciondetalle"=>$rd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($rd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($rd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($rd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $r->Cliente)->first();
            $agente = Agente::where('Numero', $r->Agente)->first();
            $data[]=array(
                      "remision"=>$r,
                      "descuentoremision"=>Helpers::convertirvalorcorrecto($r->Descuento),
                      "subtotalremision"=>Helpers::convertirvalorcorrecto($r->SubTotal),
                      "ivaremision"=>Helpers::convertirvalorcorrecto($r->Iva),
                      "totalremision"=>Helpers::convertirvalorcorrecto($r->Total),
                      "cliente" => $cliente,
                      "agente" => $agente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.remisiones.formato_pdf_remisiones', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //obtener datos para enviar email
    public function remisiones_obtener_datos_envio_email(Request $request){
        $remision = Remision::where('Remision', $request->documento)->first();
        $cliente = Cliente::where('Numero',$remision->Cliente)->first();
        $data = array(
            'remision' => $remision,
            'cliente' => $cliente,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $cliente->Email1
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function remisiones_enviar_pdfs_email(Request $request){
        $remisiones = Remision::where('Remision', $request->emaildocumento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($remisiones as $r){
            $remisiondetalle = RemisionDetalle::where('Remision', $r->Remision)->get();
            $datadetalle=array();
            foreach($remisiondetalle as $rd){
                $producto = Producto::where('Codigo', $rd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($rd->Cantidad),
                    "codigodetalle"=>$rd->Codigo,
                    "descripciondetalle"=>$rd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($rd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($rd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($rd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $r->Cliente)->first();
            $agente = Agente::where('Numero', $r->Agente)->first();
            $data[]=array(
                      "remision"=>$r,
                      "descuentoremision"=>Helpers::convertirvalorcorrecto($r->Descuento),
                      "subtotalremision"=>Helpers::convertirvalorcorrecto($r->SubTotal),
                      "ivaremision"=>Helpers::convertirvalorcorrecto($r->Iva),
                      "totalremision"=>Helpers::convertirvalorcorrecto($r->Total),
                      "cliente" => $cliente,
                      "agente" => $agente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.remisiones.formato_pdf_remisiones', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
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
                        ->attachData($pdf->output(), "RemisionNo".$emaildocumento.".pdf");
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
    public function remisiones_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new RemisionesExport($this->campos_consulta,$request->periodo), "remisiones-".$request->periodo.".xlsx");   
    }

    //guardar configuracion tabla
    public function remisiones_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        Configuracion_Tabla::where('tabla', 'Remisiones')
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
        return redirect()->route('remisiones');
    }
    
}
