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
use Luecano\NumeroALetras\NumeroALetras;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NotasCreditoClientesExport;
use App\NotaCliente;
use App\NotaClienteDetalle;
use App\NotaClienteDocumento;
use App\Remision;
use App\Factura;
use App\FacturaDetalle;
use App\Cliente;
use App\Almacen;
use App\Producto;
use App\Pais;
use App\Estado;
use App\Municipio;
use App\CodigoPostal;
use App\FormaPago;
use App\MetodoPago;
use App\UsoCFDI;
use App\c_TipoRelacion;
use App\c_RegimenFiscal;
use App\BitacoraDocumento;
Use App\Existencia;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Configuracion_Tabla;
use App\VistaNotaCreditoCliente;
use App\VistaObtenerExistenciaProducto;
use App\FolioComprobanteNota;
use App\Comprobante;
use Config;
use Mail;
use Facturapi\Facturapi;
use Storage;
use LynX39\LaraPdfMerger\Facades\PdfMerger;

class NotasCreditoClientesController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'NotasCreditoCliente')->first();
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
        //API FACTURAPI 
        $this->facturapi = new Facturapi( config('app.keyfacturapi') ); //
    }
    
    public function notas_credito_clientes(){
        $contarserieusuario = FolioComprobanteNota::where('Predeterminar', '+')->count();
        if($contarserieusuario == 0){
            $FolioComprobanteNota = FolioComprobanteNota::orderBy('Numero','DESC')->take(1)->get();
            $serieusuario = $FolioComprobanteNota[0]->Serie;
            $esquema = $FolioComprobanteNota[0]->Esquema;
        }else{
            $FolioComprobanteNota = FolioComprobanteNota::where('Predeterminar', '+')->first();
            $serieusuario = $FolioComprobanteNota->Serie;
            $esquema = $FolioComprobanteNota->Esquema;
        }
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('notas_credito_clientes_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('notas_credito_clientes_exportar_excel');
        $rutacreardocumento = route('notas_credito_clientes_generar_pdfs');
        $lugarexpedicion = $this->lugarexpedicion;
        $claveregimenfiscal = '';
        $regimenfiscal = '';
        if($this->regimenfiscal != ''){
            $c_RegimenFiscal = c_RegimenFiscal::where('Clave', $this->regimenfiscal)->first();
            $claveregimenfiscal = $c_RegimenFiscal->Clave;
            $regimenfiscal = $c_RegimenFiscal->Nombre;            
        }
        return view('registros.notascreditoclientes.notascreditoclientes', compact('serieusuario','esquema','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento','lugarexpedicion','claveregimenfiscal','regimenfiscal'));
    }

    public function notas_credito_clientes_obtener(Request $request){
        if($request->ajax()){
            $fechahoy = Carbon::now()->toDateString();
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            //$data = VistaNotaCreditoCliente::select($this->campos_consulta)->where('Periodo', $periodo)->orderBy('Fecha', 'DESC')->orderBy('Serie', 'ASC')->orderBy('Folio', 'DESC')->get();
            $data = VistaNotaCreditoCliente::select($this->campos_consulta)->where('Periodo', $periodo);
            return DataTables::of($data)
                    ->order(function ($query) {
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
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Nota .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Nota .'\')">Bajas</a></li>'.
                                                '<li><a href="'.route('notas_credito_clientes_generar_pdfs_indiv',$data->Nota).'" target="_blank">Ver Documento PDF</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Nota .'\')">Enviar Documento por Correo</a></li>'.
                                                //'<li><a href="javascript:void(0);" onclick="timbrarnota(\''.$data->Nota .'\')">Timbrar Nota</a></li>'.
                                                //'<li><a href="javascript:void(0);" onclick="cancelartimbre(\''.$data->Nota .'\')">Cancelar Timbre</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                    ->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    ->addColumn('Iva', function($data){ return $data->Iva; })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->addColumn('ImpLocTraslados', function($data){ return $data->ImpLocTraslados; })
                    ->addColumn('ImpLocRetenciones', function($data){ return $data->ImpLocRetenciones; })
                    ->addColumn('IepsRetencion', function($data){ return $data->IepsRetencion; })
                    ->addColumn('IsrRetencion', function($data){ return $data->IsrRetencion; })
                    ->addColumn('IvaRetencion', function($data){ return $data->IvaRetencion; })
                    ->addColumn('Ieps', function($data){ return $data->Ieps; })
                    ->addColumn('Descuento', function($data){ return $data->Descuento; })
                    ->addColumn('Importe', function($data){ return $data->Importe; })
                    ->addColumn('TipoCambio', function($data){ return $data->TipoCambio; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }

    //obtener ultimo folio
    public function notas_credito_clientes_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\NotaCliente', $request->serie);
        return response()->json($folio);
    }

    //obtener clientes
    public function notas_credito_clientes_obtener_clientes(Request $request){
        if($request->ajax()){
            //$data = Cliente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            $data = DB::table('Clientes as c')
            ->leftJoin('c_FormaPago as fp', 'fp.Clave', '=', 'c.FormaPago')
            ->leftJoin('c_MetodoPago as mp', 'mp.Clave', '=', 'c.MetodoPago')
            ->leftJoin('c_UsoCFDI as uc', 'uc.Clave', '=', 'c.UsoCfdi')
            ->leftJoin('c_Pais as p', 'p.Clave', '=', 'c.Pais')
            ->select('c.Numero', 'c.Nombre', 'c.Plazo', 'c.Rfc', 'c.Agente', 'c.Credito', 'c.Saldo', 'c.Status', 'c.Municipio', 'c.Tipo', 'fp.Clave AS ClaveFormaPago', 'fp.Nombre AS NombreFormaPago', 'mp.Clave AS ClaveMetodoPago', 'mp.Nombre AS NombreMetodoPago', 'uc.Clave AS ClaveUsoCfdi', 'uc.Nombre AS NombreUsoCfdi', 'p.Clave AS ClavePais', 'p.Nombre AS NombrePais')
            ->where('c.Status', 'ALTA')
            ->orderBy("Numero", "DESC")
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$data->Rfc.'\',\''.$data->ClaveFormaPago.'\',\''.$data->NombreFormaPago.'\',\''.$data->ClaveMetodoPago.'\',\''.$data->NombreMetodoPago.'\',\''.$data->ClaveUsoCfdi.'\',\''.$data->NombreUsoCfdi.'\',\''.$data->ClavePais.'\',\''.$data->NombrePais.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener cliente por numero
    public function notas_credito_clientes_obtener_cliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $rfc = '';
        $plazo = '';
        $credito = '';
        $saldo = '';
        $claveformapago = '';
        $formapago = '';
        $clavemetodopago = '';
        $metodopago = '';
        $claveusocfdi = '';
        $usocfdi = '';
        $claveresidenciafiscal = '';
        $residenciafiscal = '';
        $existecliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->first();
            $datos = DB::table('Clientes as c')
            ->leftJoin('c_FormaPago as fp', 'fp.Clave', '=', 'c.FormaPago')
            ->leftJoin('c_MetodoPago as mp', 'mp.Clave', '=', 'c.MetodoPago')
            ->leftJoin('c_UsoCFDI as uc', 'uc.Clave', '=', 'c.UsoCfdi')
            ->leftJoin('c_Pais as p', 'p.Clave', '=', 'c.Pais')
            ->select('c.Numero', 'c.Status', 'fp.Clave AS claveformapago', 'fp.Nombre AS formapago', 'mp.Clave AS clavemetodopago', 'mp.Nombre AS metodopago', 'uc.Clave AS claveusocfdi', 'uc.Nombre AS usocfdi', 'p.Clave AS claveresidenciafiscal', 'p.Nombre AS residenciafiscal')
            ->where('c.Numero', $request->numerocliente)
            ->where('c.Status', 'ALTA')
            ->get();
            $claveformapago = $datos[0]->claveformapago;
            $formapago = $datos[0]->formapago;
            $clavemetodopago = $datos[0]->clavemetodopago;
            $metodopago = $datos[0]->metodopago;
            $claveusocfdi = $datos[0]->claveusocfdi;
            $usocfdi = $datos[0]->usocfdi;
            $claveresidenciafiscal = $datos[0]->claveresidenciafiscal;
            $residenciafiscal = $datos[0]->residenciafiscal;
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
            $rfc = $cliente->Rfc;
            $plazo = $cliente->Plazo;
            $credito = Helpers::convertirvalorcorrecto($cliente->Credito);
            $saldo = Helpers::convertirvalorcorrecto($cliente->Saldo);
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'rfc' => $rfc,
            'plazo' => $plazo,
            'credito' => $credito,
            'saldo' => $saldo,
            'claveformapago' => $claveformapago,
            'formapago' => $formapago,
            'clavemetodopago' => $clavemetodopago,
            'metodopago' => $metodopago,
            'claveusocfdi' => $claveusocfdi,
            'usocfdi' => $usocfdi,
            'claveresidenciafiscal' => $claveresidenciafiscal,
            'residenciafiscal' => $residenciafiscal,
        );
        return response()->json($data);
    }

    //obtener almacenes
    public function notas_credito_clientes_obtener_almacenes(Request $request){
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
    public function notas_credito_clientes_obtener_almacen_por_numero(Request $request){
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
            'nombre' => $nombre,
        );
        return response()->json($data); 
    }

    //obtener codifos postales
    public function notas_credito_clientes_obtener_codigos_postales(Request $request){
        if($request->ajax()){
            $data = CodigoPostal::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarlugarexpedicion(\''.$data->Clave .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    
    //obtener lugar expedicion por clave
    public function notas_credito_clientes_obtener_lugar_expedicion_por_clave(Request $request){
        $clave = '';
        $estado = '';
        $existelugarexpedicion = CodigoPostal::where('Clave', $request->lugarexpedicion)->count();
        if($existelugarexpedicion > 0){
            $lugarexpedicion = CodigoPostal::where('Clave', $request->lugarexpedicion)->first();
            $clave = $lugarexpedicion->Clave;
            $estado = $lugarexpedicion->Estado;
        }
        $data = array(
            'clave' => $clave,
            'estado' => $estado
        );
        return response()->json($data); 
    }

    //obtener regimenes fiscales
    public function notas_credito_clientes_obtener_regimenes_fiscales(Request $request){
        if($request->ajax()){
            $data = c_RegimenFiscal::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarregimenfiscal(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener regimen fiscal por clave
    public function notas_credito_clientes_obtener_regimen_fiscal_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeregimenfiscal = c_RegimenFiscal::where('Clave', $request->claveregimenfiscal)->count();
        if($existeregimenfiscal > 0){
            $regimenfiscal = c_RegimenFiscal::where('Clave', $request->claveregimenfiscal)->first();
            $clave = $regimenfiscal->Clave;
            $nombre = $regimenfiscal->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data);  
    }

    //obtener tipos relacion
    public function notas_credito_clientes_obtener_tipos_relacion(Request $request){
        if($request->ajax()){
            $data = c_TipoRelacion::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionartiporelacion(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener tipo relacion por clave
    public function notas_credito_clientes_obtener_tipo_relacion_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeretiporelacion = c_TipoRelacion::where('Clave', $request->clavetiporelacion)->count();
        if($existeretiporelacion > 0){
            $tiporelacion = c_TipoRelacion::where('Clave', $request->clavetiporelacion)->first();
            $clave = $tiporelacion->Clave;
            $nombre = $tiporelacion->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }

    //obtener formas pago
    public function notas_credito_clientes_obtener_formas_pago(Request $request){
        if($request->ajax()){
            $data = FormaPago::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarformapago(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener forma pago por clave
    public function notas_credito_clientes_obtener_forma_pago_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existereformapago = FormaPago::where('Clave', $request->claveformapago)->count();
        if($existereformapago > 0){
            $formapago = FormaPago::where('Clave', $request->claveformapago)->first();
            $clave = $formapago->Clave;
            $nombre = $formapago->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }

    //obtener metodos pago
    public function notas_credito_clientes_obtener_metodos_pago(Request $request){
        if($request->ajax()){
            $data = MetodoPago::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmetodopago(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener metodo pago por clave
    public function notas_credito_clientes_obtener_metodo_pago_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeremetodopago = MetodoPago::where('Clave', $request->clavemetodopago)->count();
        if($existeremetodopago > 0){
            $metodopago = MetodoPago::where('Clave', $request->clavemetodopago)->first();
            $clave = $metodopago->Clave;
            $nombre = $metodopago->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }

    //obtener usos cfdi
    public function notas_credito_clientes_obtener_usos_cfdi(Request $request){
        if($request->ajax()){
            $data = UsoCFDI::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarusocfdi(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener uso cfdi por clave
    public function notas_credito_clientes_obtener_uso_cfdi_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existereusocfdi = UsoCFDI::where('Clave', $request->claveusocfdi)->count();
        if($existereusocfdi > 0){
            $usocfdi = UsoCFDI::where('Clave', $request->claveusocfdi)->first();
            $clave = $usocfdi->Clave;
            $nombre = $usocfdi->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }

    //obtener residencias fiscales
    public function notas_credito_clientes_obtener_residencias_fiscales(Request $request){
        if($request->ajax()){
            $data = Pais::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarresidenciafiscal(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener residencia fiscal por clave
    public function notas_credito_clientes_obtener_residencia_fiscal_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeresidenciafiscal = Pais::where('Clave', $request->claveresidenciafiscal)->count();
        if($existeresidenciafiscal > 0){
            $residencialfiscal = Pais::where('Clave', $request->claveresidenciafiscal)->first();
            $clave = $residencialfiscal->Clave;
            $nombre = $residencialfiscal->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }

    //obtener facturas
    public function notas_credito_clientes_obtener_facturas(Request $request){
        if($request->ajax()){
            $arrayfacturasseleccionadas = Array();
            foreach(explode(",", $request->stringfacturasseleccionadas) as $factura){
                array_push($arrayfacturasseleccionadas, $factura);
            }
            $data = Factura::where('Cliente', $request->numerocliente)
                                ->whereNotIn('Factura', $arrayfacturasseleccionadas)
                                ->where('Status', 'POR COBRAR')
                                ->orderBy('Folio', 'DESC')
                                ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarfactura('.$data->Folio.',\''.$data->Factura .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){
                        return Helpers::fecha_espanol($data->Fecha);
                    })
                    ->addColumn('Items', function($data){
                        return FacturaDetalle::where('Factura', $data->Factura)->count();
                    })
                    ->addColumn('Total', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total);
                    })
                    ->addColumn('Abonos', function($data){
                        return Helpers::convertirvalorcorrecto($data->Abonos);
                    })
                    ->addColumn('Descuentos', function($data){
                        return Helpers::convertirvalorcorrecto($data->Descuentos);
                    })
                    ->addColumn('Saldo', function($data){
                        return Helpers::convertirvalorcorrecto($data->Saldo);
                    })
                    ->rawColumns(['operaciones','Fecha','Total'])
                    ->make(true);
        }
    }

    //obtener factura
    public function notas_credito_clientes_obtener_factura(Request $request){
        $factura = Factura::where('Factura', $request->Factura)->first();
        $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($factura->Iva, $factura->SubTotal);
        $tipooperacion = $request->tipooperacion;
        //detalles factura
        $filafactura = '';
        $filafactura= $filafactura.
        '<tr class="filasfacturas" id="filafactura'.$request->contadorfilasfacturas.'">'.
            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfilafactura" onclick="eliminarfilafacturanotacliente('.$request->contadorfilasfacturas.')" >X</div><input type="hidden" class="form-control facturaagregadoen" name="facturaagregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
            '<td class="tdmod"><input type="hidden" class="form-control facturaaplicarpartida" name="facturaaplicarpartida[]" value="'.$factura->Factura.'" readonly>'.$factura->Factura.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control fechafacturapartida" name="fechafacturapartida[]" value="'.$factura->Fecha.'" readonly>'.$factura->Fecha.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control uuidfacturapartida" name="uuidfacturapartida[]" value="'.$factura->UUID.'" readonly>'.$factura->UUID.'</td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd totalpesosfacturapartida" name="totalpesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonosfacturapartida" name="abonosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Abonos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd notascreditofacturapartida" name="notascreditofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Descuentos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd descuentopesosfacturapartida" name="descuentopesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-max="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilastablafacturas('.$request->contadorfilasfacturas.');" ></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd saldofacturapartida" name="saldofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
        '</tr>';
        $data = array(
            "factura" => $factura,
            "filafactura" => $filafactura,
        );
        return response()->json($data);   
    }

    //obtener datos almacen
    public function notas_credito_clientes_obtener_datos_almacen(Request $request){
        $factura = Factura::where('Factura', $request->factura)->first();
        $remision = Remision::where('Status', $request->factura)->first();
        $almacen = Almacen::where('Numero', $remision->Almacen)->first();
        $data = array(
            'factura' => $factura,
            'almacen' => $almacen
        );
        return response()->json($data);
    }

    //obtener productos
    public function notas_credito_clientes_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $numeroalmacen = $request->numeroalmacen;
            $tipooperacion = $request->tipooperacion;
            $stringfacturasseleccionadas = $request->stringfacturasseleccionadas;
            $arrayproductosseleccionables = Array();
            foreach(explode(",", $request->stringfacturasseleccionadas) as $factura){
                $detallesfactura = FacturaDetalle::where('Factura', $factura)->get();
                foreach($detallesfactura as $detalle){
                    array_push($arrayproductosseleccionables, $detalle->Codigo);
                }
            }
            $data = VistaObtenerExistenciaProducto::whereIn('Codigo', $arrayproductosseleccionables)->where('Codigo', 'like', '%' . $codigoabuscar . '%');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($numeroalmacen, $tipooperacion, $stringfacturasseleccionadas){
                        if($data->Almacen == $numeroalmacen || $data->Almacen == NULL){
                            $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$data->NombreClaveProducto.'\',\''.$data->NombreClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
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
    public function notas_credito_clientes_obtener_producto_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        $numeroalmacen = $request->numeroalmacen;
        $stringfacturasseleccionadas = $request->stringfacturasseleccionadas;
        $arrayproductosseleccionables = Array();
        foreach(explode(",", $request->stringfacturasseleccionadas) as $factura){
            $detallesfactura = FacturaDetalle::where('Factura', $factura)->get();
            foreach($detallesfactura as $detalle){
                array_push($arrayproductosseleccionables, $detalle->Codigo);
            }
        }
        $contarproductos = VistaObtenerExistenciaProducto::whereIn('Codigo', $arrayproductosseleccionables)->where('Codigo', $codigoabuscar)->where('Almacen', $numeroalmacen)->count();
        if($contarproductos > 0){
            $producto = VistaObtenerExistenciaProducto::whereIn('Codigo', $arrayproductosseleccionables)->where('Codigo', $codigoabuscar)->where('Almacen', $numeroalmacen)->first();
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
                'NombreClaveProducto' => $producto->NombreClaveProducto,
                'NombreClaveUnidad' => $producto->NombreClaveUnidad,
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
                'NombreClaveProducto' => '',
                'NombreClaveUnidad' => '',
                'contarproductos' => $contarproductos
            );
        }
        return response()->json($data);  
    }

    //obtener claves productos
    public function notas_credito_clientes_obtener_claves_productos(Request $request){
        if($request->ajax()){
            $fila = $request->fila;
            $data = ClaveProdServ::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fila){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveproducto(\''.$data->Clave .'\',\''.$data->Nombre .'\','.$fila.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }          
    }
    //obtener claves unidades
    public function notas_credito_clientes_obtener_claves_unidades(Request $request){
        if($request->ajax()){
            $fila = $request->fila;
            $data = ClaveUnidad::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fila){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveunidad(\''.$data->Clave .'\',\''.$data->Nombre .'\','.$fila.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }         
    }

    //comprobar cantidad nota vs cantidad factura
    public function notas_credito_cliente_comprobar_cantidad_nota_vs_cantidad_factura(Request $request){
        $notacliente = $request->folio.'-'.$request->serie;
        $facturadetalle = FacturaDetalle::where('Factura', $request->factura)->where('Codigo', $request->codigopartida)->first();
        $cantidadfactura = $facturadetalle->Cantidad;
        $cantidadesenotrasnotas = 0;
        $existencantidadesenotrasnotas = NotaClienteDocumento::where('Nota', '<>', $notacliente)->where('Factura', $request->factura)->count();
        if($existencantidadesenotrasnotas > 0){
            $detallesnotasutilizadas = NotaClienteDocumento::where('Nota', '<>', $notacliente)->where('Factura', $request->factura)->get();
            foreach($detallesnotasutilizadas as $dnu){
                $existedetallenota = NotaClienteDetalle::where('Nota', $dnu->Nota)->where('Codigo', $request->codigopartida)->count();
                if($existedetallenota > 0){
                    $detallenota = NotaClienteDetalle::where('Nota', $dnu->Nota)->where('Codigo', $request->codigopartida)->first();
                    $cantidadesenotrasnotas = $cantidadesenotrasnotas + $detallenota->Cantidad;
                } 
            }
        }
        $cantidadmaximapermitida = $cantidadfactura + $cantidadesenotrasnotas;
        return response()->json(Helpers::convertirvalorcorrecto($cantidadmaximapermitida));
    }

    //obtener folios notas
    public function notas_credito_clientes_obtener_folios_fiscales(Request $request){
        if($request->ajax()){
            $data = FolioComprobanteNota::where('Status', 'ALTA')->OrderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarfoliofiscal(\''.$data->Serie.'\',\''.$data->Esquema.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    
    //obtener datos folio seleccionado
    public function notas_credito_clientes_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\NotaCliente', $request->Serie);
        return response()->json($folio);
    }

    //alta
    public function notas_credito_clientes_guardar(Request $request){ 
        ini_set('max_input_vars','20000' );
            //obtener el ultimo id de la tabla
            $folio = Helpers::ultimofolioserietablamodulos('App\NotaCliente', $request->serie);
            //INGRESAR DATOS A TABLA COMPRAS
            $notacliente = $folio.'-'.$request->serie;
            $NotaCliente = new NotaCliente;
            $NotaCliente->Nota=$notacliente;
            $NotaCliente->Serie=$request->serie;
            $NotaCliente->Folio=$folio;
            $NotaCliente->Esquema=$request->esquema;
            $NotaCliente->Cliente=$request->numerocliente;
            $NotaCliente->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
            $NotaCliente->Almacen=$request->numeroalmacen; 
            $NotaCliente->Importe=$request->importe;
            $NotaCliente->Descuento=$request->descuento;
            $NotaCliente->Ieps=$request->ieps;  
            $NotaCliente->SubTotal=$request->subtotal;
            $NotaCliente->Iva=$request->iva;
            $NotaCliente->IvaRetencion=$request->retencioniva;
            $NotaCliente->IsrRetencion=$request->retencionisr;
            $NotaCliente->IepsRetencion=$request->retencionieps;
            $NotaCliente->Total=$request->total;
            $NotaCliente->Moneda=$request->moneda;
            $NotaCliente->TipoCambio=$request->pesosmoneda;
            $NotaCliente->Obs=$request->observaciones;
            $NotaCliente->Status="ALTA";
            $NotaCliente->Usuario=Auth::user()->user;
            $NotaCliente->CondicionesDePago=$request->condicionesdepago;
            $NotaCliente->LugarExpedicion=$request->lugarexpedicion;
            $NotaCliente->RegimenFiscal=$request->claveregimenfiscal;
            $NotaCliente->TipoRelacion=$request->clavetiporelacion;
            $NotaCliente->Confirmacion=$request->confirmacion;
            $NotaCliente->FormaPago=$request->claveformapago;
            $NotaCliente->MetodoPago=$request->clavemetodopago;
            $NotaCliente->UsoCfdi=$request->claveusocfdi;
            $NotaCliente->ResidenciaFiscal=$request->claveresidenciafiscal;
            $NotaCliente->NumRegIdTrib=$request->numeroregidtrib;
            $NotaCliente->EmisorRfc=$request->emisorrfc;
            $NotaCliente->EmisorNombre=$request->emisornombre;
            $NotaCliente->ReceptorRfc=$request->receptorrfc;
            $NotaCliente->ReceptorNombre=$request->receptornombre;
            $NotaCliente->Hora=Carbon::parse($request->fecha)->toDateTimeString();
            $NotaCliente->Periodo=$this->periodohoy;
            $NotaCliente->save();
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO NOTAS PROVEEDOR
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "NOTAS CLIENTE";
            $BitacoraDocumento->Movimiento = $notacliente;
            $BitacoraDocumento->Aplicacion = "ALTA";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = "ALTA";
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $this->periodohoy;
            $BitacoraDocumento->save();
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO NOTAS PROVEEDOR DOC
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "NOTAS CLIENTE DOC";
            $BitacoraDocumento->Movimiento = $notacliente;
            $BitacoraDocumento->Aplicacion = "ALTA";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = "ALTA";
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $this->periodohoy;
            $BitacoraDocumento->save();
            //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
            $item = 1;
            foreach ($request->codigopartida as $key => $codigopartida){             
                $NotaClienteDetalle=new NotaClienteDetalle;
                $NotaClienteDetalle->Nota = $notacliente;
                $NotaClienteDetalle->Cliente = $request->numerocliente;
                $NotaClienteDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $NotaClienteDetalle->Codigo = $codigopartida;
                $NotaClienteDetalle->Descripcion = $request->descripcionpartida [$key];
                $NotaClienteDetalle->Unidad = $request->unidadpartida [$key];
                $NotaClienteDetalle->Cantidad =  $request->cantidadpartida  [$key];
                $NotaClienteDetalle->Precio =  $request->preciopartida [$key];
                $NotaClienteDetalle->Importe = $request->importepartida [$key];
                $NotaClienteDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                $NotaClienteDetalle->Descuento = $request->descuentopesospartida [$key];
                $NotaClienteDetalle->ImporteDescuento = $request->importedescuentopesospartida [$key];
                $NotaClienteDetalle->Ieps = $request->trasladoiepspesospartida [$key];
                $NotaClienteDetalle->SubTotal = $request->subtotalpartida [$key];
                $NotaClienteDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                $NotaClienteDetalle->Iva = $request->trasladoivapesospartida [$key];
                $NotaClienteDetalle->IvaRetencion = $request->retencionivapesospartida [$key];
                $NotaClienteDetalle->IsrRetencion = $request->retencionisrpesospartida [$key];
                $NotaClienteDetalle->IepsRetencion = $request->retencioniepspesospartida [$key];
                $NotaClienteDetalle->Total = $request->totalpesospartida [$key];
                $NotaClienteDetalle->Costo = $request->costopartida [$key];
                $NotaClienteDetalle->Partida = $request->partidapartida [$key];
                $NotaClienteDetalle->ClaveProducto = $request->claveproductopartida [$key];
                $NotaClienteDetalle->ClaveUnidad = $request->claveunidadpartida [$key];
                $NotaClienteDetalle->Item = $item;
                $NotaClienteDetalle->save();
                if($codigopartida != 'DPPP'){
                    //sumar existencias del almacen 
                    $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->count();
                    if($ContarExistenciaAlmacen > 0){
                        $ExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                        $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias + $request->cantidadpartida [$key];
                        Existencia::where('Codigo', $codigopartida)
                                    ->where('Almacen', $request->numeroalmacen)
                                    ->update([
                                        'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                    ]);
                    }else{
                        $ExistenciaAlmacen = new Existencia;
                        $ExistenciaAlmacen->Codigo = $codigopartida;
                        $ExistenciaAlmacen->Almacen = $request->numeroalmacen;
                        $ExistenciaAlmacen->Existencias = $request->cantidadpartida [$key];
                        $ExistenciaAlmacen->save();
                    }
                }
                $item++;
            }
            //INGRESAR DATOS A TABLA NOTA CLIENTES DOCUMENTOS
            $itemdocumento = 1;
            foreach ($request->facturaaplicarpartida as $key => $facturapartida){             
                $NotaClienteDocumento=new NotaClienteDocumento;
                $NotaClienteDocumento->Nota = $notacliente;
                $NotaClienteDocumento->Factura = $facturapartida;
                $NotaClienteDocumento->UUID = $request->uuidfacturapartida [$key];
                $NotaClienteDocumento->Descuento = $request->descuentopesosfacturapartida [$key];
                $NotaClienteDocumento->Item = $itemdocumento;
                $NotaClienteDocumento->save();
                //Modificar Factura
                Factura::where('Factura', $facturapartida)
                ->update([
                    'Descuentos' => $request->descuentopesosfacturapartida [$key],
                    'Saldo' => $request->saldofacturapartida [$key]
                ]);
                //Si el saldo es igual a 0 liquidar factura
                if($request->saldofacturapartida [$key] == Helpers::convertirvalorcorrecto(0)){
                    Factura::where('Factura', $facturapartida)
                            ->update([
                                'Status' => "LIQUIDADO"
                            ]);
                }
                $itemdocumento++;
            }
            return response()->json($NotaCliente);  
    }

    //verificar si se puede dar de bajar nota cliente
    public function notas_credito_clientes_verificar_si_continua_baja(Request $request){
        $errores = '';
        $NotaCliente = NotaCliente::where('Nota', $request->notadesactivar)->first();
        $DetallesNotaCliente = NotaClienteDetalle::where('Nota', $request->notadesactivar)->get();
        foreach($DetallesNotaCliente as $detalle){
            if($detalle->Codigo != 'DPPP'){
                $existencias = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $NotaCliente->Almacen)->first();
                if($existencias->Existencias < $detalle->Cantidad){
                    $errores = $errores.'Error la nota de cliente no se puede dar de baja porque no hay existencias suficientes en el almacen: '.$NotaCliente->Almacen.' para el código: '.$detalle->Codigo.'<br>';
                }
            }
        }  
        $resultadofechas = Helpers::compararanoymesfechas($NotaCliente->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'errores' => $errores,
            'Status' => $NotaCliente->Status
        );
        return response()->json($data);
    }

    //bajas
    public function notas_credito_clientes_alta_o_baja(Request $request){
        $NotaCliente = NotaCliente::where('Nota', $request->notadesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        NotaCliente::where('Nota', $request->notadesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Importe' => '0.000000',
                    'Descuento' => '0.000000',
                    'Ieps' => '0.000000',
                    'SubTotal' => '0.000000',
                    'Iva' => '0.000000',
                    'IvaRetencion' => '0.000000',
                    'IsrRetencion' => '0.000000',
                    'IepsRetencion' => '0.000000',
                    'ImpLocRetenciones' => '0.000000',
                    'ImpLocTraslados' => '0.000000',
                    'Total' => '0.000000'
                ]);
        $detalles = NotaClienteDetalle::where('Nota', $request->notadesactivar)->get();
        //notas proveedor detalles
        foreach($detalles as $detalle){
            if($detalle->Codigo != 'DPPP'){
                //restar existencias al almacen
                $ExistenciaAlmacen = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $NotaCliente->Almacen)->first();
                $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias-$detalle->Cantidad;
                Existencia::where('Codigo', $detalle->Codigo)
                            ->where('Almacen', $NotaCliente->Almacen)
                            ->update([
                                'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                            ]);
            }
            //colocar en ceros cantidades nota proveedor detalles
            NotaClienteDetalle::where('Nota', $request->notadesactivar)
                            ->where('Item', $detalle->Item)
                            ->update([
                                'Cantidad' => '0.000000',
                                'Importe' => '0.000000',
                                'Dcto' => '0.000000',
                                'Descuento' => '0.000000',
                                'ImporteDescuento' => '0.000000',
                                'Ieps' => '0.000000',
                                'SubTotal' => '0.000000',
                                'Iva' => '0.000000',
                                'IvaRetencion' => '0.000000',
                                'IsrRetencion' => '0.000000',
                                'IepsRetencion' => '0.000000',
                                'Total' => '0.000000'
                            ]);                                    
        }
        //nota cliente documentos
        $detallesdocumentos = NotaClienteDocumento::where('Nota', $request->notadesactivar)->get(); 
        foreach($detallesdocumentos as $detalledocumento){
            $notaclientedocumento = NotaClienteDocumento::where('Nota', $request->notadesactivar)->where('Factura', $detalledocumento->Factura)->first();
            $facturadocumento = Factura::where('Factura', $detalledocumento->Factura)->first();
            //Regresar saldo y descuentos a la factura
            $NuevoDescuentos = $facturadocumento->Descuentos - $notaclientedocumento->Descuento;
            $NuevoSaldo = $facturadocumento->Saldo + $notaclientedocumento->Descuento;
            Factura::where('Factura', $detalledocumento->Factura)
            ->update([
                'Descuentos' => Helpers::convertirvalorcorrecto($NuevoDescuentos),
                'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo)
            ]);
            //Si el saldo es mayor a 0 cambiar status de factura a POR COBRAR
            if($NuevoSaldo > Helpers::convertirvalorcorrecto(0)){
                Factura::where('Factura', $detalledocumento->Factura)
                        ->update([
                            'Status' => "POR COBRAR"
                        ]);
            }
            //colocar en cero cantidades nota proveedor documentos
            NotaClienteDocumento::where('Nota', $request->notadesactivar)
                                    ->where('Factura', $detalledocumento->Factura)
                                    ->update([
                                        'Descuento' => '0.000000',
                                        'Total' => '0.000000'
                                    ]);  
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "NOTAS CLIENTE";
        $BitacoraDocumento->Movimiento = $request->notadesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $NotaCliente->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($NotaCliente);
    }

    //obtener nota cliente
    public function notas_credito_clientes_obtener_nota_cliente(Request $request){
        $notacliente = NotaCliente::where('Nota', $request->notamodificar)->first();
        $almacen = 0;
        if($notacliente->Almacen != 0){
            $almacen = Almacen::where('Numero', $notacliente->Almacen)->first();
        }
        $cliente = Cliente::where('Numero', $notacliente->Cliente)->first();
        $regimenfiscal = c_RegimenFiscal::where('Clave', $notacliente->RegimenFiscal)->first();
        $tiporelacion = c_TipoRelacion::where('Clave', $notacliente->TipoRelacion)->first();
        $formapago = FormaPago::where('Clave', $notacliente->FormaPago)->first();
        $metodopago = MetodoPago::where('Clave', $notacliente->MetodoPago)->first();
        $usocfdi = UsoCFDI::where('Clave', $notacliente->UsoCfdi)->first();
        $residenciafiscal = Pais::where('Clave', $notacliente->ResidenciaFiscal)->first();
        //detalles
        $detallesnotacliente = NotaClienteDetalle::where('Nota', $request->notamodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallesnotacliente = NotaClienteDetalle::where('Nota', $request->notamodificar)->count();
        $filasdetallesnotacliente = '';
        if($numerodetallesnotacliente > 0){
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo="modificacion";
            foreach($detallesnotacliente as $dnc){
                    $producto = "";
                    $Existencia = 0;
                    if($notacliente->Almacen != 0){
                        $Existencia = Existencia::where('Codigo', $dnc->Codigo)->where('Almacen', $notacliente->Almacen)->first();
                        $producto = Producto::where('Codigo', $dnc->Codigo)->first();
                    }
                    $claveproductopartida = ClaveProdServ::where('Clave', $dnc->ClaveProducto)->first();
                    $claveunidadpartida = ClaveUnidad::where('Clave', $dnc->ClaveUnidad)->first();
                    $claveproducto = $claveproductopartida ? $claveproductopartida->Clave : '';
                    $nombreclaveproducto = $claveproductopartida ? $claveproductopartida->Nombre : '';
                    $claveunidad = $claveunidadpartida ? $claveunidadpartida->Clave : '';
                    $nombreclaveunidad = $claveunidadpartida ? $claveunidadpartida->Nombre : '';
                    //importante porque si se quiere hacer una divison con 0 marca ERROR
                    $porcentajeieps = 0;
                    $porcentajeretencioniva = 0;
                    $porcentajeretencionisr = 0;
                    $porcentajeretencionieps = 0;
                    if($dnc->Ieps > 0){
                        $porcentajeieps = ($dnc->Ieps * 100) / $dnc->ImporteDescuento;
                    }
                    if($dnc->IvaRetencion > 0){
                        $porcentajeretencioniva = ($dnc->IvaRetencion * 100) / $dnc->SubTotal;
                    }
                    if($dnc->IsrRetencion > 0){
                        $porcentajeretencionisr = ($dnc->IsrRetencion * 100) / $dnc->SubTotal;
                    }
                    if($dnc->IepsRetencion > 0){
                        $porcentajeretencionieps = ($dnc->IepsRetencion * 100) / $dnc->SubTotal;
                    }
                    if($dnc->Codigo == 'DPPP'){
                        $filasdetallesnotacliente= $filasdetallesnotacliente.
                        '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfila" onclick="eliminarfila('.$contadorfilas.')" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dnc->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dnc->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dnc->Codigo.'</td>'.         
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($dnc->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$dnc->Unidad.'" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Cantidad).'" data-parsley-min="0.1" data-parsley-max="1.0"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');">'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Ieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencioniva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->IvaRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionisr).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->IsrRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->IepsRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]"  value="0" readonly></td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                        '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                    '</div>'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                        '<input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly data-parsley-length="[1, 20]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$nombreclaveproducto.'" readonly></td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                        '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesunidades" data-toggle="tooltip" title="Ver Claves Unidades" onclick="listarclavesunidades('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                    '</div>'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.   
                                        '<input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly data-parsley-length="[1, 5]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly></td>'.
                        '</tr>';
                        $tipodetalles = 'dppp';
                    }else{
                        $filasdetallesnotacliente= $filasdetallesnotacliente.
                        '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfila" onclick="eliminarfila('.$contadorfilas.')" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dnc->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dnc->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dnc->Codigo.'</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($dnc->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$dnc->Unidad.'" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Cantidad).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');revisarcantidadnotavscantidadfactura('.$contadorfilas.');">'.
                                '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dnc->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);">'.
                                '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                                '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'.
                                '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeieps).'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Ieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencioniva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->IvaRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionisr).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->IsrRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->IepsRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]" value="0"></td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                        '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                    '</div>'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                        '<input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly data-parsley-length="[1, 20]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$nombreclaveproducto.'" readonly></td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                        '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesunidades" data-toggle="tooltip" title="Ver Claves Unidades" onclick="listarclavesunidades('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                    '</div>'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.   
                                        '<input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly data-parsley-length="[1, 5]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly></td>'.
                        '</tr>';
                        $tipodetalles = 'codigos';
                    }
                    $contadorproductos++;
                    $contadorfilas++;
            }
        }     
        //nota proveedor documentos
        $documentosnotacliente = NotaClienteDocumento::where('Nota', $request->notamodificar)->orderBy('Item', 'ASC')->get();
        $numerodocumentosnotacliente = NotaClienteDocumento::where('Nota', $request->notamodificar)->count();
        $filasdocumentosnotacliente = '';
        $arrayfacturas = array();
        if($numerodocumentosnotacliente > 0){
            $contadorfilasfacturas = 0;
            $tipo="modificacion";
            $descuentofacturas = 0;
            foreach($documentosnotacliente as $docnc){
                    array_push($arrayfacturas, $docnc->Factura);
                    $descuentofac = 0;
                    $descuentosfactura = NotaClienteDocumento::where('Nota', '<>', $request->notamodificar)->where('Factura', $docnc->Factura)->get();
                    foreach($descuentosfactura as $descuento){
                        $descuentofac = $descuentofac + $descuento->Descuento;
                    }
                    $factura = Factura::where('Factura', $docnc->Factura)->first();
                    $saldo = $factura->Saldo + $factura->Descuentos;
                    $filasdocumentosnotacliente= $filasdocumentosnotacliente.
                    '<tr class="filasfacturas" id="filafactura'.$contadorfilasfacturas.'">'.
                        '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfilafactura" onclick="eliminarfilafacturanotacliente('.$contadorfilasfacturas.')" >X</div><input type="hidden" class="form-control itemfacturapartida" name="itemfacturapartida[]" value="'.$docnc->Item.'" readonly><input type="hidden" class="form-control facturaagregadoen" name="facturaagregadoen[]" value="NA" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control facturaaplicarpartida" name="facturaaplicarpartida[]" value="'.$factura->Factura.'" readonly>'.$factura->Factura.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control fechafacturapartida" name="fechafacturapartida[]" value="'.$factura->Fecha.'" readonly>'.$factura->Fecha.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control uuidfacturapartida" name="uuidfacturapartida[]" value="'.$factura->UUID.'" readonly>'.$factura->UUID.'</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd totalpesosfacturapartida" name="totalpesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonosfacturapartida" name="abonosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Abonos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd notascreditofacturapartida" name="notascreditofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($descuentofac).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd descuentopesosfacturapartida" name="descuentopesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($docnc->Descuento).'" data-parsley-max="'.Helpers::convertirvalorcorrecto($saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilastablafacturas('.$contadorfilasfacturas.');" ></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd saldofacturapartida" name="saldofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '</tr>'; 
                    $descuentofacturas = $descuentofacturas+$docnc->Descuento;
            }
        } 
        $diferencia = $notacliente->Total - $descuentofacturas;
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($notacliente->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($notacliente->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($notacliente->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }
        $data = array(
            "notacliente" => $notacliente,
            "filasdetallesnotacliente" => $filasdetallesnotacliente,
            "numerodetallesnotacliente" => $numerodetallesnotacliente,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "tipodetalles" => $tipodetalles,
            "almacen" => $almacen,
            "cliente" => $cliente,
            "regimenfiscal" => $regimenfiscal,
            "tiporelacion" => $tiporelacion,
            "formapago" => $formapago,
            "metodopago" => $metodopago,
            "usocfdi" => $usocfdi,
            "residenciafiscal" => $residenciafiscal,
            "filasdocumentosnotacliente" => $filasdocumentosnotacliente,
            "numerodocumentosnotacliente" => $numerodocumentosnotacliente,
            "contadorfilasfacturas" => $contadorfilasfacturas,
            "arrayfacturas" => $arrayfacturas,
            "descuentofacturas" => Helpers::convertirvalorcorrecto($descuentofacturas),
            "diferencia" => Helpers::convertirvalorcorrecto($diferencia),
            "fecha" => Helpers::formatoinputdatetime($notacliente->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($notacliente->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($notacliente->Descuento),
            "ieps" => Helpers::convertirvalorcorrecto($notacliente->Ieps),
            "subtotal" => Helpers::convertirvalorcorrecto($notacliente->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($notacliente->Iva),
            "ivaretencion" => Helpers::convertirvalorcorrecto($notacliente->IvaRetencion),
            "isrretencion" => Helpers::convertirvalorcorrecto($notacliente->IsrRetencion),
            "iepsretencion" => Helpers::convertirvalorcorrecto($notacliente->IepsRetencion),
            "total" => Helpers::convertirvalorcorrecto($notacliente->Total),
            "tipocambio" => Helpers::convertirvalorcorrecto($notacliente->TipoCambio),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }

    //cambios
    public function notas_credito_clientes_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
            $notacliente = $request->notaclientebd;
            $NotaCliente = NotaCliente::where('Nota', $notacliente)->first();
            //array detalles antes de modificacion
            $ArrayDetallesNotaAnterior = Array();
            $DetallesNotaAnterior = NotaClienteDetalle::where('Nota', $notacliente)->get();
            foreach($DetallesNotaAnterior as $detalle){
                array_push($ArrayDetallesNotaAnterior, $detalle->Nota.'#'.$detalle->Codigo.'#'.$detalle->Item);
            }
            //array detalles despues de modificacion
            $ArrayDetallesNotaNuevo = Array();
            foreach ($request->codigopartida as $key => $nuevocodigo){
                if($request->agregadoen [$key] == 'NA'){
                    array_push($ArrayDetallesNotaNuevo, $notacliente.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
                } 
            }  
            //diferencias entre arreglos
            $diferencias_arreglos = array_diff($ArrayDetallesNotaAnterior, $ArrayDetallesNotaNuevo);
            //iteramos las diferencias entre arreglos
            if(count($diferencias_arreglos) > 0){
                foreach($diferencias_arreglos as $eliminapartida){
                    $explode_d = explode("#",$eliminapartida);
                    $detallenota = NotaClienteDetalle::where('Nota', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->first();
                    //restar existencias a almacen principal
                    $RestarExistenciaAlmacen = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacen)->first();
                    $RestarExistenciaNuevaAlmacen = $RestarExistenciaAlmacen->Existencias - $detallenota->Cantidad;
                    Existencia::where('Codigo', $explode_d[1])
                                ->where('Almacen', $request->numeroalmacen)
                                ->update([
                                    'Existencias' => Helpers::convertirvalorcorrecto($RestarExistenciaNuevaAlmacen)
                                ]);
                    //eliminar detalle
                    $eliminardetalle= NotaClienteDetalle::where('Nota', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
                }
            }
            //array detalles documentos antes de modificacion
            $ArrayDetallesDocumentosNotaAnterior = Array();
            $DetallesDocumentosNotaAnterior = NotaClienteDocumento::where('Nota', $notacliente)->get();
            foreach($DetallesDocumentosNotaAnterior as $detalledocumento){
                array_push($ArrayDetallesDocumentosNotaAnterior, $detalledocumento->Nota.'#'.$detalledocumento->Factura.'#'.$detalledocumento->Item);
            }
            //array detalles documentos despues de modificacion
            $ArrayDetallesDocumentosNotaNuevo = Array();
            foreach ($request->facturaaplicarpartida as $key => $nuevafactura){
                if($request->facturaagregadoen [$key] == 'NA'){
                    array_push($ArrayDetallesDocumentosNotaNuevo, $notacliente.'#'.$nuevafactura.'#'.$request->itemfacturapartida [$key]);
                } 
            }  
            //diferencias entre arreglos
            $diferencias_arreglos = array_diff($ArrayDetallesDocumentosNotaAnterior, $ArrayDetallesDocumentosNotaNuevo);
            //iteramos las diferencias entre arreglos
            if(count($diferencias_arreglos) > 0){
                foreach($diferencias_arreglos as $eliminapartida){
                    $explode_d = explode("#",$eliminapartida);
                    $detalledocumentonota = NotaClienteDocumento::where('Nota', $explode_d[0])->where('Factura', $explode_d[1])->where('Item', $explode_d[2])->first();
                    //Regresar saldo y descuentos a la factura
                    $notaclientedocumento = NotaClienteDocumento::where('Nota', $explode_d[0])->where('Factura', $explode_d[1])->where('Item', $explode_d[2])->first();
                    $facturadocumento = Factura::where('Factura', $explode_d[1])->first();
                    $NuevoDescuentos = $facturadocumento->Descuentos - $notaclientedocumento->Descuento;
                    $NuevoSaldo = $facturadocumento->Saldo + $notaclientedocumento->Descuento;
                    Factura::where('Factura', $explode_d[1])
                    ->update([
                        'Descuentos' => Helpers::convertirvalorcorrecto($NuevoDescuentos),
                        'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo)
                    ]);
                    //eliminar detalle
                    $eliminardetalledocumento= NotaClienteDocumento::where('Nota', $explode_d[0])->where('Factura', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
                }
            }
            //modificar nota
            NotaCliente::where('Nota', $notacliente)
            ->update([
                'Cliente' => $request->numerocliente,
                'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                'Almacen' => $request->numeroalmacen, 
                'Importe' => $request->importe,
                'Descuento' => $request->descuento,
                'Ieps' => $request->ieps,  
                'SubTotal' => $request->subtotal,
                'Iva' => $request->iva,
                'IvaRetencion' => $request->retencioniva,
                'IsrRetencion' => $request->retencionisr,
                'IepsRetencion' => $request->retencionieps,
                'Total' => $request->total,
                'Moneda' => $request->moneda,
                'TipoCambio' => $request->pesosmoneda,
                'Obs' => $request->observaciones,
                'CondicionesDePago' => $request->condicionesdepago,
                'LugarExpedicion' => $request->lugarexpedicion,
                'RegimenFiscal' => $request->claveregimenfiscal,
                'TipoRelacion' => $request->clavetiporelacion,
                'Confirmacion' => $request->confirmacion,
                'FormaPago' => $request->claveformapago,
                'MetodoPago' => $request->clavemetodopago,
                'UsoCfdi' => $request->claveusocfdi,
                'ResidenciaFiscal' => $request->claveresidenciafiscal,
                'NumRegIdTrib' => $request->numeroregidtrib,
                'EmisorRfc' => $request->emisorrfc,
                'EmisorNombre' => $request->emisornombre,
                'ReceptorRfc' => $request->receptorrfc,
                'ReceptorNombre' => $request->receptornombre
            ]);
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "NOTAS CLIENTE";
            $BitacoraDocumento->Movimiento = $notacliente;
            $BitacoraDocumento->Aplicacion = "CAMBIO";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = $NotaCliente->Status;
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $this->periodohoy;
            $BitacoraDocumento->save();
            //detalles
            foreach ($request->codigopartida as $key => $codigopartida){  
                //if la partida se agrego en la modificacion se realiza un insert
                if($request->agregadoen [$key] == 'modificacion'){        
                    $contaritems = NotaClienteDetalle::select('Item')->where('Nota', $notacliente)->count();
                    if($contaritems > 0){
                        $item = NotaClienteDetalle::select('Item')->where('Nota', $notacliente)->orderBy('Item', 'DESC')->take(1)->get();
                        $ultimoitem = $item[0]->Item+1;
                    }else{
                        $ultimoitem = 1;
                    }
                    $NotaClienteDetalle=new NotaClienteDetalle;
                    $NotaClienteDetalle->Nota = $notacliente;
                    $NotaClienteDetalle->Cliente = $request->numerocliente;
                    $NotaClienteDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                    $NotaClienteDetalle->Codigo = $codigopartida;
                    $NotaClienteDetalle->Descripcion = $request->descripcionpartida [$key];
                    $NotaClienteDetalle->Unidad = $request->unidadpartida [$key];
                    $NotaClienteDetalle->Cantidad =  $request->cantidadpartida  [$key];
                    $NotaClienteDetalle->Precio =  $request->preciopartida [$key];
                    $NotaClienteDetalle->Importe = $request->importepartida [$key];
                    $NotaClienteDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                    $NotaClienteDetalle->Descuento = $request->descuentopesospartida [$key];
                    $NotaClienteDetalle->ImporteDescuento = $request->importedescuentopesospartida [$key];
                    $NotaClienteDetalle->Ieps = $request->trasladoiepspesospartida [$key];
                    $NotaClienteDetalle->SubTotal = $request->subtotalpartida [$key];
                    $NotaClienteDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                    $NotaClienteDetalle->Iva = $request->trasladoivapesospartida [$key];
                    $NotaClienteDetalle->IvaRetencion = $request->retencionivapesospartida [$key];
                    $NotaClienteDetalle->IsrRetencion = $request->retencionisrpesospartida [$key];
                    $NotaClienteDetalle->IepsRetencion = $request->retencioniepspesospartida [$key];
                    $NotaClienteDetalle->Total = $request->totalpesospartida [$key];
                    $NotaClienteDetalle->Costo = $request->costopartida [$key];
                    $NotaClienteDetalle->Partida = $request->partida [$key];
                    $NotaClienteDetalle->ClaveProducto = $request->claveproductopartida [$key];
                    $NotaClienteDetalle->ClaveUnidad = $request->claveunidadpartida [$key];
                    $NotaClienteDetalle->Item = $ultimoitem;
                    $NotaClienteDetalle->save();
                    if($codigopartida != 'DPPP'){
                        //sumar existencias del almacen 
                        $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->count();
                        if($ContarExistenciaAlmacen > 0){
                            $ExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                            $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias + $request->cantidadpartida [$key];
                            Existencia::where('Codigo', $codigopartida)
                                        ->where('Almacen', $request->numeroalmacen)
                                        ->update([
                                            'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                        ]);
                        }else{
                            $ExistenciaAlmacen = new Existencia;
                            $ExistenciaAlmacen->Codigo = $codigopartida;
                            $ExistenciaAlmacen->Almacen = $request->numeroalmacen;
                            $ExistenciaAlmacen->Existencias = $request->cantidadpartida [$key];
                            $ExistenciaAlmacen->save();
                        }
                    }
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar detalle
                    NotaClienteDetalle::where('Nota', $notacliente)
                    ->where('Item', $request->itempartida [$key])
                    ->update([
                        'Cliente' => $request->numerocliente,
                        'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                        'Codigo' => $codigopartida,
                        'Descripcion' => $request->descripcionpartida [$key],
                        'Unidad' => $request->unidadpartida [$key],
                        'Cantidad' =>  $request->cantidadpartida  [$key],
                        'Precio' =>  $request->preciopartida [$key],
                        'Importe' => $request->importepartida [$key],
                        'Dcto' => $request->descuentoporcentajepartida [$key],
                        'Descuento' => $request->descuentopesospartida [$key],
                        'ImporteDescuento' => $request->importedescuentopesospartida [$key],
                        'Ieps' => $request->trasladoiepspesospartida [$key],
                        'SubTotal' => $request->subtotalpartida [$key],
                        'Impuesto' => $request->ivaporcentajepartida [$key],
                        'Iva' => $request->trasladoivapesospartida [$key],
                        'IvaRetencion' => $request->retencionivapesospartida [$key],
                        'IsrRetencion' => $request->retencionisrpesospartida [$key],
                        'IepsRetencion' => $request->retencioniepspesospartida [$key],
                        'Total' => $request->totalpesospartida [$key],
                        'Costo' => $request->costopartida [$key],
                        'Partida' => $request->partidapartida [$key],
                        'ClaveProducto' => $request->claveproductopartida [$key],
                        'ClaveUnidad' => $request->claveunidadpartida [$key]
                    ]);
                    if($codigopartida != 'DPPP'){
                        //restar existencias a almacen principal
                        $ExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                        $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias - $request->cantidadpartidadb [$key];
                        Existencia::where('Codigo', $codigopartida)
                                    ->where('Almacen', $request->numeroalmacen)
                                    ->update([
                                        'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                    ]);
                        //sumar existencias del almacen 
                        $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->count();
                        if($ContarExistenciaAlmacen > 0){
                            $ExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                            $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias + $request->cantidadpartida [$key];
                            Existencia::where('Codigo', $codigopartida)
                                        ->where('Almacen', $request->numeroalmacen)
                                        ->update([
                                            'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                        ]);
                        }else{
                            $ExistenciaAlmacen = new Existencia;
                            $ExistenciaAlmacen->Codigo = $codigopartida;
                            $ExistenciaAlmacen->Almacen = $request->numeroalmacen;
                            $ExistenciaAlmacen->Existencias = $request->cantidadpartida [$key];
                            $ExistenciaAlmacen->save();
                        }
                    }
                }    
            }
            //detalles documentos
            foreach ($request->facturaaplicarpartida as $key => $facturapartida){     
                //if la partida se agrego en la modificacion se realiza un insert
                if($request->facturaagregadoen [$key] == 'modificacion'){ 
                    $itemdocumento = NotaClienteDocumento::select('Item')->where('Nota', $notacliente)->orderBy('Item', 'DESC')->take(1)->get();
                    $ultimoitemdocumento = $itemdocumento[0]->Item+1;
                    $NotaClienteDocumento=new NotaClienteDocumento;
                    $NotaClienteDocumento->Nota = $notacliente;
                    $NotaClienteDocumento->Factura = $facturapartida;
                    $NotaClienteDocumento->UUID = $request->uuidfacturapartida [$key];
                    $NotaClienteDocumento->Descuento = $request->descuentopesosfacturapartida [$key];
                    $NotaClienteDocumento->Item = $ultimoitemdocumento;
                    $NotaClienteDocumento->save();
                    //Modificar Factura
                    Factura::where('Factura', $facturapartida)
                    ->update([
                        'Descuentos' => $request->descuentopesosfacturapartida [$key],
                        'Saldo' => $request->saldofacturapartida [$key]
                    ]);
                    //Si el saldo es igual a 0 liquidar factura
                    if($request->saldofacturapartida [$key] == Helpers::convertirvalorcorrecto(0)){
                        Factura::where('Factura', $facturapartida)
                                ->update([
                                    'Status' => "LIQUIDADO"
                                ]);
                    }
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar detalle
                    NotaClienteDocumento::where('Nota', $notacliente)
                    ->where('Item', $request->itemfacturapartida [$key])
                    ->update([
                        'Descuento' => $request->descuentopesosfacturapartida [$key]
                    ]);
                    //Regresar saldo y descuentos a la factura
                    $notaclientedocumento = NotaClienteDocumento::where('Nota', $notacliente)->where('Factura', $facturapartida)->where('Item', $request->itemfacturapartida [$key])->first();
                    $facturadocumento = Factura::where('Factura', $facturapartida)->first();
                    $NuevoDescuentos = $facturadocumento->Descuentos - $notaclientedocumento->Descuento;
                    $NuevoSaldo = $facturadocumento->Saldo + $notaclientedocumento->Descuento;
                    Factura::where('Factura', $facturapartida)
                    ->update([
                        'Descuentos' => Helpers::convertirvalorcorrecto($NuevoDescuentos),
                        'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo)
                    ]);
                    //Modificar Factura
                    Factura::where('Factura', $facturapartida)
                    ->update([
                        'Descuentos' => $request->descuentopesosfacturapartida [$key],
                        'Saldo' => $request->saldofacturapartida [$key]
                    ]);
                    //Si el saldo es igual a 0 liquidar factura
                    if($request->saldofacturapartida [$key] == Helpers::convertirvalorcorrecto(0)){
                        Factura::where('Factura', $facturapartida)
                                ->update([
                                    'Status' => "LIQUIDADO"
                                ]);
                    }
                } 
            }  
            return response()->json($NotaCliente);
    }

    //buscar folio on key up
    public function notas_credito_clientes_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = NotaCliente::where('Nota', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Nota.'\')"><i class="material-icons">done</i></div> ';
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
    public function notas_credito_clientes_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $notascreditocliente = NotaCliente::whereIn('Nota', $request->arraypdf)->orderBy('Folio', 'ASC')->take(250)->get(); 
        }else{
            //$contrarecibos = ContraRecibo::where('Fecha', $request->anopdf)->get(); 
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $notascreditocliente = NotaCliente::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(250)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        foreach ($notascreditocliente as $ncc){
            $data=array();
            $notascreditoclientedetalle = NotaClienteDetalle::where('Nota', $ncc->Nota)->get();
            $datadetalle=array();
            foreach($notascreditoclientedetalle as $nccd){
                $claveproducto = ClaveProdServ::where('Clave', $nccd->ClaveProducto)->first();
                $claveunidad = ClaveUnidad::where('Clave', $nccd->ClaveUnidad)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($nccd->Cantidad),
                    "codigodetalle"=>$nccd->Codigo,
                    "descripciondetalle"=>$nccd->Descripcion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($nccd->Precio),
                    "porcentajedescuentodetalle" => Helpers::convertirvalorcorrecto($nccd->Dcto),
                    "pesosdescuentodetalle" => Helpers::convertirvalorcorrecto($nccd->Descuento),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($nccd->SubTotal),
                    "impuestodetalle" => Helpers::convertirvalorcorrecto($nccd->Impuesto),
                    "ivadetalle" => Helpers::convertirvalorcorrecto($nccd->Iva),
                    "claveproducto" => $claveproducto,
                    "claveunidad" => $claveunidad
                );
            } 
            $cliente = Cliente::where('Numero', $ncc->Cliente)->first();
            $formapago = FormaPago::where('Clave', $ncc->FormaPago)->first();
            $metodopago = MetodoPago::where('Clave', $ncc->MetodoPago)->first();
            $usocfdi = UsoCFDI::where('Clave', $ncc->UsoCfdi)->first();
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($ncc->Total, 2, 'M.N.');
            $notaclientedocumento = NotaClienteDocumento::where('Nota', $ncc->Nota)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $ncc->RegimenFiscal)->first();
            $data[]=array(
                "notacreditocliente"=>$ncc,
                "notaclientedocumento"=>$notaclientedocumento,
                "comprobante" => $comprobante,
                "comprobantetimbrado" => $comprobantetimbrado,
                "regimenfiscal"=> $regimenfiscal,
                "subtotalnotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->SubTotal),
                "ivanotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->Iva),
                "totalnotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->Total),
                "cliente" => $cliente,
                "formapago" => $formapago,
                "metodopago" => $metodopago,
                "usocfdi" => $usocfdi,
                "datadetalle" => $datadetalle,
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($ncc->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.notascreditoclientes.formato_pdf_notascreditoclientes', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$ncc->Nota.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($notascreditocliente as $notcc){
            $ArchivoPDF = "PDF".$notcc->Nota.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
        }
        $pdfMerger->merge(); //unirlos
        $pdfMerger->save("NotasCreditoCliente.pdf", "browser");//mostrarlos en el navegador
    }

    //generacion de formato en PDF
    public function notas_credito_clientes_generar_pdfs_indiv($documento){
        $notascreditocliente = NotaCliente::where('Nota', $documento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($notascreditocliente as $ncc){
            $notascreditoclientedetalle = NotaClienteDetalle::where('Nota', $ncc->Nota)->get();
            $datadetalle=array();
            foreach($notascreditoclientedetalle as $nccd){
                $claveproducto = ClaveProdServ::where('Clave', $nccd->ClaveProducto)->first();
                $claveunidad = ClaveUnidad::where('Clave', $nccd->ClaveUnidad)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($nccd->Cantidad),
                    "codigodetalle"=>$nccd->Codigo,
                    "descripciondetalle"=>$nccd->Descripcion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($nccd->Precio),
                    "porcentajedescuentodetalle" => Helpers::convertirvalorcorrecto($nccd->Dcto),
                    "pesosdescuentodetalle" => Helpers::convertirvalorcorrecto($nccd->Descuento),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($nccd->SubTotal),
                    "impuestodetalle" => Helpers::convertirvalorcorrecto($nccd->Impuesto),
                    "ivadetalle" => Helpers::convertirvalorcorrecto($nccd->Iva),
                    "claveproducto" => $claveproducto,
                    "claveunidad" => $claveunidad
                );
            } 
            $cliente = Cliente::where('Numero', $ncc->Cliente)->first();
            $formapago = FormaPago::where('Clave', $ncc->FormaPago)->first();
            $metodopago = MetodoPago::where('Clave', $ncc->MetodoPago)->first();
            $usocfdi = UsoCFDI::where('Clave', $ncc->UsoCfdi)->first();
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($ncc->Total, 2, 'M.N.');
            $notaclientedocumento = NotaClienteDocumento::where('Nota', $ncc->Nota)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $ncc->RegimenFiscal)->first();
            $data[]=array(
                "notacreditocliente"=>$ncc,
                "notaclientedocumento"=>$notaclientedocumento,
                "comprobante" => $comprobante,
                "comprobantetimbrado" => $comprobantetimbrado,
                "regimenfiscal"=> $regimenfiscal,
                "subtotalnotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->SubTotal),
                "ivanotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->Iva),
                "totalnotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->Total),
                "cliente" => $cliente,
                "formapago" => $formapago,
                "metodopago" => $metodopago,
                "usocfdi" => $usocfdi,
                "datadetalle" => $datadetalle,
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($ncc->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.notascreditoclientes.formato_pdf_notascreditoclientes', compact('data'))
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
    public function notas_credito_clientes_obtener_datos_envio_email(Request $request){
        $notacliente = NotaCliente::where('Nota', $request->documento)->first();
        $cliente = Cliente::where('Numero',$notacliente->Cliente)->first();
        $data = array(
            'notacliente' => $notacliente,
            'cliente' => $cliente,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $cliente->Email1,
            'email2cc' => $cliente->Email2,
            'email3cc' => $cliente->Email3
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function notas_credito_clientes_enviar_pdfs_email(Request $request){
        $notascreditocliente = NotaCliente::where('Nota', $request->emaildocumento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($notascreditocliente as $ncc){
            $notascreditoclientedetalle = NotaClienteDetalle::where('Nota', $ncc->Nota)->get();
            $datadetalle=array();
            foreach($notascreditoclientedetalle as $nccd){
                $claveproducto = ClaveProdServ::where('Clave', $nccd->ClaveProducto)->first();
                $claveunidad = ClaveUnidad::where('Clave', $nccd->ClaveUnidad)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($nccd->Cantidad),
                    "codigodetalle"=>$nccd->Codigo,
                    "descripciondetalle"=>$nccd->Descripcion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($nccd->Precio),
                    "porcentajedescuentodetalle" => Helpers::convertirvalorcorrecto($nccd->Dcto),
                    "pesosdescuentodetalle" => Helpers::convertirvalorcorrecto($nccd->Descuento),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($nccd->SubTotal),
                    "impuestodetalle" => Helpers::convertirvalorcorrecto($nccd->Impuesto),
                    "ivadetalle" => Helpers::convertirvalorcorrecto($nccd->Iva),
                    "claveproducto" => $claveproducto,
                    "claveunidad" => $claveunidad
                );
            } 
            $cliente = Cliente::where('Numero', $ncc->Cliente)->first();
            $formapago = FormaPago::where('Clave', $ncc->FormaPago)->first();
            $metodopago = MetodoPago::where('Clave', $ncc->MetodoPago)->first();
            $usocfdi = UsoCFDI::where('Clave', $ncc->UsoCfdi)->first();
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($ncc->Total, 2, 'M.N.');
            $notaclientedocumento = NotaClienteDocumento::where('Nota', $ncc->Nota)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $ncc->RegimenFiscal)->first();
            $data[]=array(
                "notacreditocliente"=>$ncc,
                "notaclientedocumento"=>$notaclientedocumento,
                "comprobante" => $comprobante,
                "comprobantetimbrado" => $comprobantetimbrado,
                "regimenfiscal"=> $regimenfiscal,
                "subtotalnotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->SubTotal),
                "ivanotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->Iva),
                "totalnotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->Total),
                "cliente" => $cliente,
                "formapago" => $formapago,
                "metodopago" => $metodopago,
                "usocfdi" => $usocfdi,
                "datadetalle" => $datadetalle,
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($ncc->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.notascreditoclientes.formato_pdf_notascreditoclientes', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        //obtener XML
        if($request->incluir_xml == 1){
            $nota = NotaCliente::where('Nota', $request->emaildocumento)->first(); 
            $comprobante = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $nota->Folio . '')->where('Serie', '' . $nota->Serie . '')->first();
            $descargar_xml = $this->facturapi->Invoices->download_xml($comprobante->IdFacturapi); // stream containing the XML file or
            $nombre_xml = "NotaCreditoClienteNo".$nota->Nota.'##'.$nota->UUID.'.xml';
            Storage::disk('local')->put($nombre_xml, $descargar_xml);
            $url_xml = Storage::disk('local')->getAdapter()->applyPathPrefix($nombre_xml);
        }else{
            $url_xml = "";
        }
        try{
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
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailasunto;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            if (file_exists($url_xml) != false) {
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $arraycc, $correos, $asunto, $pdf, $emaildocumento,$url_xml) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento,$url_xml)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "NotaCreditoClienteNo".$emaildocumento.".pdf")
                            ->attach($url_xml);
                });
                //eliminar xml de storage/xml_cargados
                unlink($url_xml);
            }else{
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $arraycc, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "NotaCreditoClienteNo".$emaildocumento.".pdf");
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

    //exportar a excel
    public function notas_credito_clientes_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new NotasCreditoClientesExport($this->campos_consulta,$request->periodo), "notascreditoclientes-".$request->periodo.".xlsx");   
    }
    //configuracion tabla
    public function notas_credito_clientes_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        Configuracion_Tabla::where('tabla', 'NotasCreditoCliente')
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
        return redirect()->route('notas_credito_clientes');
    }
    //verificar si se puede timbrar la factura
    public function notas_credito_clientes_verificar_si_continua_timbrado(Request $request){
        $nota = NotaCliente::where('Nota', $request->nota)->first();
        $data = array(
            'Esquema' => $nota->Esquema,
            'Status' => $nota->Status,
            'UUID' => $nota->UUID
        );
        return response()->json($data);
    }
    //timbrar factura
    public function notas_credito_clientes_timbrar_nota(Request $request){
        $nota = NotaCliente::where('Nota', $request->notatimbrado)->first();
        $detallesnota = NotaClienteDetalle::where('Nota', $request->notatimbrado)->get();
        $detallesdocumentosnota = NotaClienteDocumento::where('Nota', $request->notatimbrado)->where('Descuento', '>', 0)->get();
        $cliente = Cliente::where('Numero', $nota->Cliente)->first();
        $arraydet = array();
        foreach($detallesnota as $dn){
            array_push($arraydet,   array(
                                        "description" => $dn->Descripcion,
                                        "product_key" => $dn->ClaveProducto,
                                        "price" => Helpers::convertirvalorcorrecto($dn->Precio),
                                        "tax_included" => false,
                                        "sku" => $dn->Codigo
                                    )                    
            );
        }  
        $arraydoc = array();
        foreach($detallesdocumentosnota as $ddn){
            array_push($arraydoc, $ddn->UUID);
        }  
        //NOTAS DE CREDITO PROVEEDOR
        $invoice = array(
            "type" => \Facturapi\InvoiceType::EGRESO,
            "customer" => array(
                "legal_name" => $cliente->Nombre,
                "tax_id" => $cliente->Rfc
            ),
            "payment_form" => $nota->FormaPago,
            "payment_method" => $nota->MetodoPago,
            "relation" => $nota->TipoRelacion,
            "related" => $arraydoc,
            "products" => $arraydet,
            "folio_number" => $nota->Folio,
            "series" => $nota->Serie,
            "currency" => $nota->Moneda,
            "exchange" => Helpers::convertirvalorcorrecto($nota->TipoCambio),
            "conditions" => $nota->CondicionesDePago
        );
        $new_invoice = $this->facturapi->Invoices->create( $invoice );
        $result = json_encode($new_invoice);
        $result2 = json_decode($result, true);
        if(array_key_exists('ok', $result2) == true){
            $mensaje = $new_invoice->message;
            $tipomensaje = "error";
            $data = array(
                        'mensaje' => "Error, ".$mensaje,
                        'tipomensaje' => $tipomensaje 
                    );
            return response()->json($data);
        }else{
            //obtener datos del xml del documento timbrado para guardarlo en la tabla comprobantes
            $descargar_xml = $this->facturapi->Invoices->download_xml($new_invoice->id); // stream containing the XML file or
            $xml = simplexml_load_string($descargar_xml);  
            $comprobante = $xml->attributes(); 
            $CertificadoCFD = $comprobante['NoCertificado'];
            //obtener datos generales del xml nodo Emisor
            $activar_namespaces = $xml->getNameSpaces(true);
            $namespaces = $xml->children($activar_namespaces['cfdi']);
            //obtener UUID del xml timbrado digital
            $activar_namespaces = $namespaces->Complemento->getNameSpaces(true);
            $namespaces_uuid = $namespaces->Complemento->children($activar_namespaces['tfd']);
            $atributos_complemento = $namespaces_uuid->TimbreFiscalDigital->attributes();
            $NoCertificadoSAT = $atributos_complemento['NoCertificadoSAT'];
            $SelloCFD = $atributos_complemento['SelloCFD'];
            $SelloSAT = $atributos_complemento['SelloSAT'];
            $fechatimbrado = $atributos_complemento['FechaTimbrado'];
            $cadenaoriginal = "||".$atributos_complemento['Version']."|".$new_invoice->uuid."|".$atributos_complemento['FechaTimbrado']."|".$atributos_complemento['SelloCFD']."|".$atributos_complemento['NoCertificadoSAT']."||";
            //guardar en tabla comprobante
            $Comprobante = new Comprobante;
            $Comprobante->Comprobante = 'Nota';
            $Comprobante->Tipo = $new_invoice->type;
            $Comprobante->Version = '3.3';
            $Comprobante->Serie = $new_invoice->series;
            $Comprobante->Folio = $new_invoice->folio_number;
            $Comprobante->UUID = $new_invoice->uuid;
            $Comprobante->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $Comprobante->SubTotal = $nota->SubTotal;
            $Comprobante->Descuento = $nota->Descuento;
            $Comprobante->Total = $nota->Total;
            $Comprobante->EmisorRfc = $nota->EmisorRfc;
            $Comprobante->ReceptorRfc = $nota->ReceptorRfc;
            $Comprobante->FormaPago = $new_invoice->payment_form;
            $Comprobante->MetodoPago = $new_invoice->payment_method;
            $Comprobante->UsoCfdi = $nota->UsoCfdi;
            $Comprobante->Moneda = $new_invoice->currency;
            $Comprobante->TipoCambio = Helpers::convertirvalorcorrecto($new_invoice->exchange);
            $Comprobante->CertificadoSAT = $NoCertificadoSAT;
            $Comprobante->CertificadoCFD = $CertificadoCFD;
            $Comprobante->FechaTimbrado = $fechatimbrado;
            $Comprobante->CadenaOriginal = $cadenaoriginal;
            $Comprobante->selloSAT = $SelloSAT;
            $Comprobante->selloCFD = $SelloCFD;
            //$Comprobante->CfdiTimbrado = $new_invoice->type;
            $Comprobante->Periodo = $this->periodohoy;
            $Comprobante->IdFacturapi = $new_invoice->id;
            $Comprobante->UrlVerificarCfdi = $new_invoice->verification_url;
            $Comprobante->save();
            //Colocar UUID en documento
            NotaCliente::where('Nota', $request->notatimbrado)
                            ->update([
                                'FechaTimbrado' => $fechatimbrado,
                                'UUID' => $new_invoice->uuid
                            ]);  
            // Enviar a más de un correo (máx 10)
            $this->facturapi->Invoices->send_by_email(
                $new_invoice->id,
                array(
                    "osbaldo.anzaldo@utpcamiones.com.mx",
                    //"marco.baltazar@utpcamiones.com.mx",
                )
            );
            $mensaje = "Correcto, el documento se timbro correctamente";
            $tipomensaje = "success";
            $data = array(
                        'mensaje' => $mensaje,
                        'tipomensaje' => $tipomensaje 
                    );
            return response()->json($data);
        }
    }
    //verificar cancelacion timbre
    public function notas_credito_clientes_verificar_si_continua_baja_timbre(Request $request){
        $obtener_factura = '';
        $comprobante = '';
        $factura = NotaCliente::where('Nota', $request->facturabajatimbre)->first();
        $existe_comprobante = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')->count();
        if($existe_comprobante > 0){
            $comprobante = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')->first();
            $obtener_factura = $this->facturapi->Invoices->retrieve($comprobante->IdFacturapi); // obtener factura
        }
        $data = array(
            'obtener_factura' => $obtener_factura,
            'factura' => $factura,
            'comprobante' => $comprobante
        );
        return response()->json($data);
    }
    //cancelar timbre
    public function notas_credito_clientes_baja_timbre(Request $request){
        //colocar fecha de cancelacion en tabla comprobante
        $factura = NotaCliente::where('Nota', $request->facturabajatimbre)->first();
        Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')
        ->update([
            'FechaCancelacion' => Helpers::fecha_exacta_accion_datetimestring()
        ]);
        //cancelar timbre facturapi
        $timbrecancelado = $this->facturapi->Invoices->cancel($request->iddocumentofacturapi);
        return response()->json($timbrecancelado);
    }

}
