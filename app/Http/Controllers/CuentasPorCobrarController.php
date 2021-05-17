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
use App\Exports\CuentasPorCobrarExport;
use App\CuentaXCobrar;
use App\CuentaXCobrarDetalle;
use App\Banco;
use App\Cliente;
use App\Factura;
use App\FacturaDetalle;
use App\Pais;
use App\Estado;
use App\Municipio;
use App\CodigoPostal;
use App\FormaPago;
use App\UsoCFDI;
use App\NotaCliente;
use App\NotaClienteDetalle;
use App\BitacoraDocumento;
use Luecano\NumeroALetras\NumeroALetras;
use App\Configuracion_Tabla;
use App\VistaCuentaPorCobrar;
use App\MetodoPago;
use App\c_RegimenFiscal;
use App\c_TipoRelacion;
use App\FolioComprobantePago;
use App\Comprobante;
use Config;
use Mail;

class CuentasPorCobrarController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'CuentasPorCobrar')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function cuentas_por_cobrar(){
        $contarserieusuario = FolioComprobantePago::where('Predeterminar', '+')->count();
        if($contarserieusuario == 0){
            $FolioComprobantePago = FolioComprobantePago::orderBy('Numero','DESC')->take(1)->get();
            $serieusuario = $FolioComprobantePago[0]->Serie;
            $esquema = $FolioComprobantePago[0]->Esquema;
        }else{
            $FolioComprobantePago = FolioComprobantePago::where('Predeterminar', '+')->first();
            $serieusuario = $FolioComprobantePago->Serie;
            $esquema = $FolioComprobantePago->Esquema;
        }
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('cuentas_por_cobrar_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('cuentas_por_cobrar_exportar_excel');
        $rutacreardocumento = route('cuentas_por_cobrar_generar_pdfs');
        $lugarexpedicion = $this->lugarexpedicion;
        $claveregimenfiscal = '';
        $regimenfiscal = '';
        if($this->regimenfiscal != ''){
            $c_RegimenFiscal = c_RegimenFiscal::where('Clave', $this->regimenfiscal)->first();
            $claveregimenfiscal = $c_RegimenFiscal->Clave;
            $regimenfiscal = $c_RegimenFiscal->Nombre;            
        }
        return view('registros.cuentasporcobrar.cuentasporcobrar', compact('serieusuario','esquema','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento','lugarexpedicion','claveregimenfiscal','regimenfiscal'));
    }

    //obtener registro tabla
    public function cuentas_por_cobrar_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaCuentaPorCobrar::select($this->campos_consulta)->where('Periodo', $periodo)->orderBy('Folio', 'DESC')->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                        $botoncambios   =   '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Pago .'\')"><i class="material-icons">mode_edit</i></div> '; 
                        $botonbajas     =   '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Pago .'\')"><i class="material-icons">cancel</i></div>  ';
                        $botondocumentopdf = '<a href="'.route('cuentas_por_cobrar_generar_pdfs_indiv',$data->Pago).'" target="_blank"><div class="btn bg-blue-grey btn-xs waves-effect" data-toggle="tooltip" title="Generar Documento"><i class="material-icons">archive</i></div></a> ';
                        $botonenviaremail = '<div class="btn bg-brown btn-xs waves-effect" data-toggle="tooltip" title="Enviar Documento por Correo" onclick="enviardocumentoemail(\''.$data->Pago .'\')"><i class="material-icons">email</i></div> ';
                        $operaciones    = $botoncambios.$botonbajas.$botondocumentopdf.$botonenviaremail;
                    return $operaciones;
                })
                ->addColumn('Abono', function($data){ return $data->Abono; })
                ->addColumn('TipoCambio', function($data){ return $data->TipoCambio; })
                ->rawColumns(['operaciones'])
                ->make(true);
        } 
    }

    //obtener ultimo folio
    public function cuentas_por_cobrar_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\CuentaXCobrar', $request->serie);
        return response()->json($folio);
    }

    //obtener datetime local 
    public function cuentas_por_cobrar_obtener_fecha_datetime(){
        $fecha = Helpers::fecha_exacta_accion_datetimelocal();
        return response()->json($fecha);
    }

    //obtener clientes
    public function cuentas_por_cobrar_obtener_clientes(Request $request){
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
                        /*$claveformapago = '';
                        $formapago = '';
                        $clavemetodopago = '';
                        $metodopago = '';
                        $claveusocfdi = '';
                        $usocfdi = '';
                        $claveresidenciafiscal = '';
                        $residenciafiscal = '';
                        if($data->FormaPago != ''){
                            $FormaPago = FormaPago::where('Clave', $data->FormaPago)->first();
                            $claveformapago = $FormaPago->Clave;
                            $formapago = $FormaPago->Nombre;
                        }
                        if($data->MetodoPago != ''){
                            $MetodoPago = MetodoPago::where('Clave', $data->MetodoPago)->first();
                            $clavemetodopago = $MetodoPago->Clave;
                            $metodopago = $MetodoPago->Nombre;
                        }
                        if($data->UsoCfdi != ''){
                            $UsoCFDI = UsoCFDI::where('Clave', $data->UsoCfdi)->first();
                            $claveusocfdi = $UsoCFDI->Clave;
                            $usocfdi = $UsoCFDI->Nombre;
                        }
                        if($data->Pais != ''){
                            $Pais = Pais::where('Clave', $data->Pais)->first();
                            $claveresidenciafiscal = $Pais->Clave;
                            $residenciafiscal = $Pais->Nombre;
                        }*/
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$data->Rfc.'\',\''.$data->ClaveFormaPago.'\',\''.$data->NombreFormaPago.'\',\''.$data->ClaveMetodoPago.'\',\''.$data->NombreMetodoPago.'\',\''.$data->ClaveUsoCfdi.'\',\''.$data->NombreUsoCfdi.'\',\''.$data->ClavePais.'\',\''.$data->NombrePais.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener bancos
    public function cuentas_por_cobrar_obtener_bancos(Request $request){
        if($request->ajax()){
            $data = Banco::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarbanco('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }


    //obtener codifos postales
    public function cuentas_por_cobrar_obtener_codigos_postales(Request $request){
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

    //obtener regimenes fiscales
    public function cuentas_por_cobrar_obtener_regimenes_fiscales(Request $request){
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

    //obtener tipos relacion
    public function cuentas_por_cobrar_obtener_tipos_relacion(Request $request){
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

    //obtener formas pago
    public function cuentas_por_cobrar_obtener_formas_pago(Request $request){
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

    //obtener metodos pago
    public function cuentas_por_cobrar_obtener_metodos_pago(Request $request){
        if($request->ajax()){
            $fila = $request->fila;
            $data = MetodoPago::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fila){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmetodopago(\''.$data->Clave .'\',\''.$data->Nombre .'\','.$fila.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener facturas 
    public function cuentas_por_cobrar_obtener_facturas(Request $request){
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
    public function cuentas_por_cobrar_obtener_factura(Request $request){
        $factura = Factura::where('Factura', $request->Factura)->first();
        $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($factura->Iva, $factura->SubTotal);
        $tipooperacion = $request->tipooperacion;
        $numeroparcialidades = 1;
        $numparcialidades = CuentaXCobrarDetalle::where('Factura', $request->Factura)->where('Abono', '>', 0)->count();
        if($numparcialidades > 0){
            $numeroparcialidades = $numeroparcialidades + $numparcialidades;
        }
        //detalles factura
        $filafactura = '';
        $filafactura= $filafactura.
        '<tr class="filasfacturas" id="filafactura'.$request->contadorfilas.'">'.
            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfilafactura" onclick="eliminarfilafactura('.$request->contadorfilas.')" >X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
            '<td class="tdmod"><input type="hidden" class="form-control facturaaplicarpartida" name="facturaaplicarpartida[]" value="'.$factura->Factura.'" readonly>'.$factura->Factura.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control fechafacturapartida" name="fechafacturapartida[]" value="'.Helpers::fecha_espanol($factura->Fecha).'" readonly>'.Helpers::fecha_espanol($factura->Fecha).'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control plazofacturapartida" name="plazofacturapartida[]" value="'.$factura->Plazo.'" readonly>'.$factura->Plazo.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control vencefacturapartida" name="vencefacturapartida[]" value="'.Helpers::fecha_espanol(Carbon::parse($factura->Fecha)->addDays($factura->Plazo)->toDateTimeString()).'" readonly>'.Helpers::fecha_espanol(Carbon::parse($factura->Fecha)->addDays($factura->Plazo)->toDateTimeString()).'</td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd totalpesosfacturapartida" name="totalpesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonosfacturapartida" name="abonosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Abonos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd notascreditofacturapartida" name="notascreditofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Descuentos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonopesosfacturapartida" name="abonopesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-min="0.1" data-parsley-max="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularnuevosaldo('.$request->contadorfilas.');calculartotal('.$request->contadorfilas.');" ></td>'.
            '<td class="tdmod" hidden><input type="number" class="form-control divorinputmodmd saldofacturapartidadb" name="saldofacturapartidadb[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'"></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd saldofacturapartida" name="saldofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="hidden" class="form-control uuidfacturapartida" name="uuidfacturapartida[]" value="'.$factura->UUID.'" readonly>'.$factura->UUID.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control seriefacturapartida" name="seriefacturapartida[]" value="'.$factura->Serie.'" readonly>'.$factura->Serie.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control foliofacturapartida" name="foliofacturapartida[]" value="'.$factura->Folio.'" readonly>'.$factura->Folio.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control monedadrfacturapartida" name="monedadrfacturapartida[]" value="'.$factura->Moneda.'" readonly>'.$factura->Moneda.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control tipocambiodrfacturapartida" name="tipocambiodrfacturapartida[]" value="'.$factura->TipoCambio.'" readonly>'.$factura->TipoCambio.'</td>'.
            '<td class="tdmod">'.
                '<div class="row divorinputmodl">'.
                    '<div class="col-md-2">'.
                        '<div class="btn bg-blue btn-xs waves-effect" data-toggle="tooltip" title="Cambiar Método de Pago" onclick="listarmetodospago('.$request->contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                    '</div>'.
                    '<div class="col-md-10">'.    
                        '<input type="text" class="form-control divorinputmodsm metodopagodrfacturapartida" name="metodopagodrfacturapartida[]" value="'.$factura->MetodoPago.'" readonly>'.                 
                    '</div>'.
                '</div>'.
            '</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control numparcialidadfacturapartida" name="numparcialidadfacturapartida[]" value="'.$numeroparcialidades.'" readonly>'.$numeroparcialidades.'</td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd impsaldoantfacturapartida" name="impsaldoantfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd imppagadofacturapartida" name="imppagadofacturapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd impsaldoinsolutofacturapartida" name="impsaldoinsolutofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
        '</tr>';
        $data = array(
            "factura" => $factura,
            "filafactura" => $filafactura,
        );
        return response()->json($data);  
    }

    //obtener folios notas
    public function cuentas_por_cobrar_obtener_folios_fiscales(Request $request){
        if($request->ajax()){
            $data = FolioComprobantePago::where('Status', 'ALTA')->OrderBy('Numero', 'DESC')->get();
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
    public function cuentas_por_cobrar_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\CuentaXCobrar', $request->Serie);
        return response()->json($folio);
    }

    //altas
    public function cuentas_por_cobrar_guardar(Request $request){
        ini_set('max_input_vars','10000' );
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofoliotablamodulos('App\CuentaXCobrar');
        //INGRESAR DATOS A TABLA
        $pago = $folio.'-'.$request->serie;
		$CuentaXCobrar = new CuentaXCobrar;
		$CuentaXCobrar->Pago=$pago;
		$CuentaXCobrar->Serie=$request->serie;
		$CuentaXCobrar->Folio=$folio;
        $CuentaXCobrar->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $CuentaXCobrar->FechaPago=Carbon::parse($request->fechaaplicacionpagos)->toDateTimeString();
        $CuentaXCobrar->Cliente=$request->numerocliente;
		$CuentaXCobrar->Banco=$request->numerobanco;
        $CuentaXCobrar->Esquema=$request->esquema;
        $CuentaXCobrar->Abono=$request->total;
        $CuentaXCobrar->Anotacion=$request->anotacion;
        $CuentaXCobrar->Moneda=$request->moneda;
        $CuentaXCobrar->TipoCambio=$request->pesosmoneda;
        $CuentaXCobrar->EmisorRfc=$request->emisorrfc;
        $CuentaXCobrar->EmisorNombre=$request->emisornombre;
        $CuentaXCobrar->LugarExpedicion=$request->lugarexpedicion;
        $CuentaXCobrar->RegimenFiscal=$request->claveregimenfiscal;
        $CuentaXCobrar->ReceptorRfc=$request->receptorrfc;
        $CuentaXCobrar->ReceptorNombre=$request->receptornombre;
        $CuentaXCobrar->FormaPago=$request->claveformapago;
        $CuentaXCobrar->Hora=Carbon::parse($request->fecha)->toDateTimeString();
        $CuentaXCobrar->Status="ALTA";
        $CuentaXCobrar->Usuario=Auth::user()->user;
        $CuentaXCobrar->Periodo=$request->periodohoy;
        $CuentaXCobrar->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CXC";
        $BitacoraDocumento->Movimiento = $pago;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        $item = 1;
        foreach ($request->facturaaplicarpartida as $key => $factura){     
                $CuentaXCobrarDetalle=new CuentaXCobrarDetalle;
                $CuentaXCobrarDetalle->Pago = $pago;
                $CuentaXCobrarDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $CuentaXCobrarDetalle->Cliente = $request->numerocliente;
                $CuentaXCobrarDetalle->Factura = $factura;
                $CuentaXCobrarDetalle->Abono = $request->abonopesosfacturapartida [$key];
                $CuentaXCobrarDetalle->idDocumento = $request->uuidfacturapartida [$key];
                $CuentaXCobrarDetalle->Serie = $request->seriefacturapartida [$key];
                $CuentaXCobrarDetalle->Folio = $request->foliofacturapartida [$key];
                $CuentaXCobrarDetalle->MonedaDR = $request->monedadrfacturapartida [$key];
                $CuentaXCobrarDetalle->TipoCambioDR = $request->tipocambiodrfacturapartida [$key];
                $CuentaXCobrarDetalle->MetodoDePagoDR = $request->metodopagodrfacturapartida [$key];
                $CuentaXCobrarDetalle->NumParcialidad = $request->numparcialidadfacturapartida [$key];
                $CuentaXCobrarDetalle->ImpSaldoAnt = $request->impsaldoantfacturapartida [$key];
                $CuentaXCobrarDetalle->ImpPagado = $request->imppagadofacturapartida [$key];
                $CuentaXCobrarDetalle->ImpSaldoInsoluto = $request->impsaldoinsolutofacturapartida [$key];
                $CuentaXCobrarDetalle->Item = $item;
                $CuentaXCobrarDetalle->save();
                $item++;
                //modificar abonos y saldo en factura
                $Factura = Factura::where('Factura', $factura)->first();
                $NuevoAbono = $Factura->Abonos + $request->abonopesosfacturapartida [$key];
                $NuevoSaldo = $request->saldofacturapartida [$key];
                if($request->saldofacturapartida [$key] == Helpers::convertirvalorcorrecto(0)){
                    $Status = "LIQUIDADO";
                }else{
                    $Status = "POR COBRAR"; 
                }
                //Modificar Factura
                Factura::where('Factura', $factura)
                ->update([
                    'Abonos' => Helpers::convertirvalorcorrecto($NuevoAbono),
                    'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo),
                    'Status' => $Status
                ]);
        }
    	return response()->json($CuentaXCobrar); 
    }

    //comprobar baja de documento
    public function cuentas_por_cobrar_comprobar_baja(Request $request){
        $CuentaXCobrar = CuentaXCobrar::where('Pago', $request->cxcdesactivar)->first();
        $resultadofechas = Helpers::compararanoymesfechas($CuentaXCobrar->Fecha);
        $data = array (
            'resultadofechas' => $resultadofechas,
            'Status' => $CuentaXCobrar->Status
        );
        return response()->json($data);
    }

    //bajas
    public function cuentas_por_cobrar_baja(Request $request){
        $CuentaXCobrar = CuentaXCobrar::where('Pago', $request->cxcdesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        CuentaXCobrar::where('Pago', $request->cxcdesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Abono' => '0.000000'
                ]);
        $detalles = CuentaXCobrarDetalle::where('Pago', $request->cxcdesactivar)->get();
        foreach($detalles as $detalle){
            //restar abono de la factura
            $Factura = Factura::where('Factura', $detalle->Factura)->first();
            $NuevoAbono = $Factura->Abonos - $detalle->Abono;
            $NuevoSaldo = $Factura->Saldo + $detalle->Abono;
            Factura::where('Factura', $detalle->Factura)
                        ->update([
                            'Abonos' => Helpers::convertirvalorcorrecto($NuevoAbono),
                            'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo),
                            'Status' => "POR COBRAR"
                        ]);
            //tabla cuenta x pgar detalle
            CuentaXCobrarDetalle::where('Pago', $detalle->Pago)
                            ->where('Factura', $detalle->Factura)
                            ->update([
                                'Abono' => '0.000000'
                            ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CXC";
        $BitacoraDocumento->Movimiento = $request->cxcdesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $CuentaXCobrar->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($CuentaXCobrar);
    }

    //obtener registro
    public function cuentas_por_cobrar_obtener_cuenta_por_cobrar(Request $request){
        $cuentaxcobrar = CuentaXCobrar::where('Pago', $request->cxcmodificar)->first();
        $numerocuentaxcobrardetalle = CuentaXCobrarDetalle::where('Pago', $request->cxcmodificar)->count();
        $cuentaxcobrardetalle = CuentaXCobrarDetalle::where('Pago', $request->cxcmodificar)->get();
        $cliente = Cliente::where('Numero', $cuentaxcobrar->Cliente)->first();
        $regimenfiscal = c_RegimenFiscal::where('Clave', $cuentaxcobrar->RegimenFiscal)->first();
        $tiporelacion = c_TipoRelacion::where('Numero', 1)->first();
        $formapago = FormaPago::where('Clave', $cuentaxcobrar->FormaPago)->first();
        $banco = Banco::where('Numero', $cuentaxcobrar->Banco)->first();
        $filasdetallecuentasporcobrar = '';
        $contadorfilas = 0;
        $contadorproductos = 0;
        $arrayfacturas = array();
        if($numerocuentaxcobrardetalle > 0){
            foreach($cuentaxcobrardetalle as $cxcd){
                array_push($arrayfacturas, $cxcd->Factura);
                $factura = Factura::where('Factura', $cxcd->Factura)->first();
                $filasdetallecuentasporcobrar= $filasdetallecuentasporcobrar.
                '<tr class="filasfacturas" id="filafactura'.$contadorfilas.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfilafactura" onclick="eliminarfilafactura('.$contadorfilas.')" >X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control facturaaplicarpartida" name="facturaaplicarpartida[]" value="'.$factura->Factura.'" readonly>'.$factura->Factura.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control fechafacturapartida" name="fechafacturapartida[]" value="'.Helpers::fecha_espanol($factura->Fecha).'" readonly>'.Helpers::fecha_espanol($factura->Fecha).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control plazofacturapartida" name="plazofacturapartida[]" value="'.$factura->Plazo.'" readonly>'.$factura->Plazo.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control vencefacturapartida" name="vencefacturapartida[]" value="'.Helpers::fecha_espanol(Carbon::parse($factura->Fecha)->addDays($factura->Plazo)->toDateTimeString()).'" readonly>'.Helpers::fecha_espanol(Carbon::parse($factura->Fecha)->addDays($factura->Plazo)->toDateTimeString()).'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd totalpesosfacturapartida" name="totalpesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonosfacturapartida" name="abonosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Abonos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd notascreditofacturapartida" name="notascreditofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Descuentos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonopesosfacturapartida" name="abonopesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($cxcd->Abono).'" data-parsley-min="0.1" data-parsley-max="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularnuevosaldo('.$contadorfilas.');calculartotal('.$contadorfilas.');" ></td>'.
                    '<td class="tdmod" hidden><input type="number" class="form-control divorinputmodmd saldofacturapartidadb" name="saldofacturapartidadb[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd saldofacturapartida" name="saldofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control uuidfacturapartida" name="uuidfacturapartida[]" value="'.$cxcd->idDocumento.'" readonly>'.$cxcd->idDocumento.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control seriefacturapartida" name="seriefacturapartida[]" value="'.$cxcd->Serie.'" readonly>'.$cxcd->Serie.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control foliofacturapartida" name="foliofacturapartida[]" value="'.$cxcd->Folio.'" readonly>'.$cxcd->Folio.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control monedadrfacturapartida" name="monedadrfacturapartida[]" value="'.$cxcd->MonedaDR.'" readonly>'.$cxcd->MonedaDR.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control tipocambiodrfacturapartida" name="tipocambiodrfacturapartida[]" value="'.$cxcd->TipoCambioDR.'" readonly>'.$cxcd->TipoCambioDR.'</td>'.
                    '<td class="tdmod">'.
                        '<div class="row divorinputmodl">'.
                            '<div class="col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect" data-toggle="tooltip" title="Cambiar Método de Pago" onclick="listarmetodospago('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-md-10">'.    
                                '<input type="text" class="form-control divorinputmodsm metodopagodrfacturapartida" name="metodopagodrfacturapartida[]" value="'.$cxcd->MetodoDePagoDR.'" readonly>'.                 
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control numparcialidadfacturapartida" name="numparcialidadfacturapartida[]" value="'.$cxcd->NumParcialidad.'" readonly>'.$cxcd->NumParcialidad.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd impsaldoantfacturapartida" name="impsaldoantfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($cxcd->ImpSaldoAnt).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd imppagadofacturapartida" name="imppagadofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($cxcd->ImpPagado).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd impsaldoinsolutofacturapartida" name="impsaldoinsolutofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($cxcd->ImpSaldoInsoluto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '</tr>';
                $contadorfilas++;
                $contadorproductos++;
            }
        }
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($cuentaxcobrar->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($cuentaxcobrar->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($cuentaxcobrar->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }
        $data = array(
            'cuentaxcobrar' => $cuentaxcobrar,
            'cuentaxcobrardetalle' => $cuentaxcobrardetalle,
            'numerocuentaxcobrardetalle' => $numerocuentaxcobrardetalle,
            'contadorproductos' => $contadorproductos,
            'contadorfilas' => $contadorfilas,
            'fecha' => Helpers::formatoinputdatetime($cuentaxcobrar->Fecha),
            'fechapago' => Helpers::formatoinputdatetime($cuentaxcobrar->FechaPago),
            'cliente' => $cliente,
            'regimenfiscal' => $regimenfiscal,
            'tiporelacion' => $tiporelacion,
            'formapago' => $formapago,
            'banco' => $banco,
            'filasdetallecuentasporcobrar' => $filasdetallecuentasporcobrar,
            'arrayfacturas' => $arrayfacturas,
            'abonototal' => Helpers::convertirvalorcorrecto($cuentaxcobrar->Abono),
            'tipocambio' => Helpers::convertirvalorcorrecto($cuentaxcobrar->TipoCambio),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($datas);
    }

    //buscar folio on key up
    public function cuentas_por_cobrar_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = CuentaXCobrar::where('Pago', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Pago .'\')"><i class="material-icons">done</i></div> ';
                    return $boton;
                })
                ->addColumn('Abono', function($data){
                    return Helpers::convertirvalorcorrecto($data->Abono);
                })
                ->rawColumns(['operaciones','Total'])
                ->make(true);
        } 
    }

    //generar documento PDF
    public function cuentas_por_cobrar_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $cuentasporcobrar = CuentaXCobrar::whereIn('Pago', $request->arraypdf)->orderBy('Folio', 'ASC')->take(150)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $cuentasporcobrar = CuentaXCobrar::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(150)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cuentasporcobrar as $cxc){
            $formatter = new NumeroALetras;
            $abonoletras =  $formatter->toInvoice($cxc->Abono, $this->numerodecimales, 'M.N.');
            $cuentaporcobrardetalle = CuentaXCobrarDetalle::where('Pago', $cxc->Pago)->get();
            $datadetalle=array();
            $importepagado = 0;
            $importesaldoinsoluto = 0;
            foreach($cuentaporcobrardetalle as $cxcd){
                $importepagado = $importepagado + $cxcd->ImpPagado;
                $importesaldoinsoluto = $importesaldoinsoluto + $cxcd->ImpSaldoInsoluto;
                $clientedetalle = Cliente::where('Numero', $cxcd->Cliente)->first();
                $facturadetalle = Factura::where('Factura', $cxcd->Factura)->first();
                $metodopagofacturadetalle = MetodoPago::where('Clave', $facturadetalle->MetodoPago)->first();
                $datadetalle[]=array(
                    "clientedetalle"=> $clientedetalle,
                    "iddocumentodetalle" => $cxcd->idDocumento,
                    "facturadetalle" => $cxcd->Factura,
                    "fechadetalle" => Carbon::parse($facturadetalle->Fecha)->toDateString(),
                    "plazodetalle" => $facturadetalle->Plazo,
                    "vencedetalle" => Carbon::parse($facturadetalle->Fecha)->addDays($facturadetalle->Plazo)->toDateString(),
                    "totalfactura" => Helpers::convertirvalorcorrecto($facturadetalle->Total),
                    "numparcialidaddetalle" => $cxcd->NumParcialidad,
                    "impsaldoantdetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpSaldoAnt),
                    "imppagadodetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpPagado),
                    "impsaldoinsolutodetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpSaldoInsoluto),
                    "tipocambiofacturadetalle" => Helpers::convertirvalorcorrecto($facturadetalle->TipoCambio),
                    "clavemetodopagodetalle" => $metodopagofacturadetalle->Clave,
                    "nombremetodopagodetalle" => $metodopagofacturadetalle->Nombre
                );
            } 
            $cliente = Cliente::where('Numero', $cxc->Cliente)->first();
            $estadocliente = Estado::where('Clave', $cliente->Estado)->first();
            $formapago = FormaPago::where('Clave', $cxc->FormaPago)->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $cxc->RegimenFiscal)->first();
            $comprobantetimbrado = Comprobante::where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->count();
            $comprobante = Comprobante::where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->first();
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($cxc->Abono, 2, 'M.N.');
            $data[]=array(
                        "cuentaporcobrar"=>$cxc,
                        "fechaespanolcuentaporcobrar"=>Helpers::fecha_espanol($cxc->Fecha),
                        "abonocuentaporcobrar"=>Helpers::convertirvalorcorrecto($cxc->Abono),
                        "importepagado" =>Helpers::convertirvalorcorrecto($importepagado),
                        "importesaldoinsoluto" =>Helpers::convertirvalorcorrecto($importesaldoinsoluto),
                        "abonoletras"=>$abonoletras,
                        "formapago" => $formapago,
                        "comprobante" => $comprobante,
                        "comprobantetimbrado" => $comprobantetimbrado,
                        "regimenfiscal"=> $regimenfiscal,
                        "cliente" => $cliente,
                        "estadocliente" => $estadocliente,
                        "datadetalle" => $datadetalle,
                        "tipocambiocxc"=>Helpers::convertirvalorcorrecto($cxc->TipoCambio),
                        "totalletras"=>$totalletras,
                        "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cuentasporcobrar.formato_pdf_cuentasporcobrar', compact('data'))
        ->setOption('footer-left', 'Este pago es una representación impresa de un CFDi')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //generacion de formato en PDF
    public function cuentas_por_cobrar_generar_pdfs_indiv($documento){
        $cuentasporcobrar = CuentaXCobrar::where('Pago', $documento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cuentasporcobrar as $cxc){
            $formatter = new NumeroALetras;
            $abonoletras =  $formatter->toInvoice($cxc->Abono, $this->numerodecimales, 'M.N.');
            $cuentaporcobrardetalle = CuentaXCobrarDetalle::where('Pago', $cxc->Pago)->get();
            $datadetalle=array();
            $importepagado = 0;
            $importesaldoinsoluto = 0;
            foreach($cuentaporcobrardetalle as $cxcd){
                $importepagado = $importepagado + $cxcd->ImpPagado;
                $importesaldoinsoluto = $importesaldoinsoluto + $cxcd->ImpSaldoInsoluto;
                $clientedetalle = Cliente::where('Numero', $cxcd->Cliente)->first();
                $facturadetalle = Factura::where('Factura', $cxcd->Factura)->first();
                $metodopagofacturadetalle = MetodoPago::where('Clave', $facturadetalle->MetodoPago)->first();
                $datadetalle[]=array(
                    "clientedetalle"=> $clientedetalle,
                    "iddocumentodetalle" => $cxcd->idDocumento,
                    "facturadetalle" => $cxcd->Factura,
                    "fechadetalle" => Carbon::parse($facturadetalle->Fecha)->toDateString(),
                    "plazodetalle" => $facturadetalle->Plazo,
                    "vencedetalle" => Carbon::parse($facturadetalle->Fecha)->addDays($facturadetalle->Plazo)->toDateString(),
                    "totalfactura" => Helpers::convertirvalorcorrecto($facturadetalle->Total),
                    "numparcialidaddetalle" => $cxcd->NumParcialidad,
                    "impsaldoantdetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpSaldoAnt),
                    "imppagadodetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpPagado),
                    "impsaldoinsolutodetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpSaldoInsoluto),
                    "tipocambiofacturadetalle" => Helpers::convertirvalorcorrecto($facturadetalle->TipoCambio),
                    "clavemetodopagodetalle" => $metodopagofacturadetalle->Clave,
                    "nombremetodopagodetalle" => $metodopagofacturadetalle->Nombre
                );
            } 
            $cliente = Cliente::where('Numero', $cxc->Cliente)->first();
            $estadocliente = Estado::where('Clave', $cliente->Estado)->first();
            $formapago = FormaPago::where('Clave', $cxc->FormaPago)->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $cxc->RegimenFiscal)->first();
            $comprobantetimbrado = Comprobante::where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->count();
            $comprobante = Comprobante::where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->first();
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($cxc->Abono, 2, 'M.N.');
            $data[]=array(
                        "cuentaporcobrar"=>$cxc,
                        "fechaespanolcuentaporcobrar"=>Helpers::fecha_espanol($cxc->Fecha),
                        "abonocuentaporcobrar"=>Helpers::convertirvalorcorrecto($cxc->Abono),
                        "importepagado" =>Helpers::convertirvalorcorrecto($importepagado),
                        "importesaldoinsoluto" =>Helpers::convertirvalorcorrecto($importesaldoinsoluto),
                        "abonoletras"=>$abonoletras,
                        "formapago" => $formapago,
                        "comprobante" => $comprobante,
                        "comprobantetimbrado" => $comprobantetimbrado,
                        "regimenfiscal"=> $regimenfiscal,
                        "cliente" => $cliente,
                        "estadocliente" => $estadocliente,
                        "datadetalle" => $datadetalle,
                        "tipocambiocxc"=>Helpers::convertirvalorcorrecto($cxc->TipoCambio),
                        "totalletras"=>$totalletras,
                        "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cuentasporcobrar.formato_pdf_cuentasporcobrar', compact('data'))
        ->setOption('footer-left', 'Este pago es una representación impresa de un CFDi')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //obtener datos para enviar email
    public function cuentas_por_cobrar_obtener_datos_envio_email(Request $request){
        $cuentaporcobrar = CuentaXCobrar::where('Pago', $request->documento)->first();
        $cliente = Cliente::where('Numero',$cuentaporcobrar->Cliente)->first();
        $data = array(
            'cuentaporcobrar' => $cuentaporcobrar,
            'cliente' => $cliente,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $cliente->Email1
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function cuentas_por_cobrar_enviar_pdfs_email(Request $request){
        $cuentasporcobrar = CuentaXCobrar::where('Pago', $request->emaildocumento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cuentasporcobrar as $cxc){
            $formatter = new NumeroALetras;
            $abonoletras =  $formatter->toInvoice($cxc->Abono, $this->numerodecimales, 'M.N.');
            $cuentaporcobrardetalle = CuentaXCobrarDetalle::where('Pago', $cxc->Pago)->get();
            $datadetalle=array();
            $importepagado = 0;
            $importesaldoinsoluto = 0;
            foreach($cuentaporcobrardetalle as $cxcd){
                $importepagado = $importepagado + $cxcd->ImpPagado;
                $importesaldoinsoluto = $importesaldoinsoluto + $cxcd->ImpSaldoInsoluto;
                $clientedetalle = Cliente::where('Numero', $cxcd->Cliente)->first();
                $facturadetalle = Factura::where('Factura', $cxcd->Factura)->first();
                $metodopagofacturadetalle = MetodoPago::where('Clave', $facturadetalle->MetodoPago)->first();
                $datadetalle[]=array(
                    "clientedetalle"=> $clientedetalle,
                    "iddocumentodetalle" => $cxcd->idDocumento,
                    "facturadetalle" => $cxcd->Factura,
                    "fechadetalle" => Carbon::parse($facturadetalle->Fecha)->toDateString(),
                    "plazodetalle" => $facturadetalle->Plazo,
                    "vencedetalle" => Carbon::parse($facturadetalle->Fecha)->addDays($facturadetalle->Plazo)->toDateString(),
                    "totalfactura" => Helpers::convertirvalorcorrecto($facturadetalle->Total),
                    "numparcialidaddetalle" => $cxcd->NumParcialidad,
                    "impsaldoantdetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpSaldoAnt),
                    "imppagadodetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpPagado),
                    "impsaldoinsolutodetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpSaldoInsoluto),
                    "tipocambiofacturadetalle" => Helpers::convertirvalorcorrecto($facturadetalle->TipoCambio),
                    "clavemetodopagodetalle" => $metodopagofacturadetalle->Clave,
                    "nombremetodopagodetalle" => $metodopagofacturadetalle->Nombre
                );
            } 
            $cliente = Cliente::where('Numero', $cxc->Cliente)->first();
            $estadocliente = Estado::where('Clave', $cliente->Estado)->first();
            $formapago = FormaPago::where('Clave', $cxc->FormaPago)->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $cxc->RegimenFiscal)->first();
            $comprobantetimbrado = Comprobante::where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->count();
            $comprobante = Comprobante::where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->first();
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($cxc->Abono, 2, 'M.N.');
            $data[]=array(
                        "cuentaporcobrar"=>$cxc,
                        "fechaespanolcuentaporcobrar"=>Helpers::fecha_espanol($cxc->Fecha),
                        "abonocuentaporcobrar"=>Helpers::convertirvalorcorrecto($cxc->Abono),
                        "importepagado" =>Helpers::convertirvalorcorrecto($importepagado),
                        "importesaldoinsoluto" =>Helpers::convertirvalorcorrecto($importesaldoinsoluto),
                        "abonoletras"=>$abonoletras,
                        "formapago" => $formapago,
                        "comprobante" => $comprobante,
                        "comprobantetimbrado" => $comprobantetimbrado,
                        "regimenfiscal"=> $regimenfiscal,
                        "cliente" => $cliente,
                        "estadocliente" => $estadocliente,
                        "datadetalle" => $datadetalle,
                        "tipocambiocxc"=>Helpers::convertirvalorcorrecto($cxc->TipoCambio),
                        "totalletras"=>$totalletras,
                        "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cuentasporcobrar.formato_pdf_cuentasporcobrar', compact('data'))
        ->setOption('footer-left', 'Este pago es una representación impresa de un CFDi')
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
                        ->attachData($pdf->output(), "CuentaPorCobrarNo".$emaildocumento.".pdf");
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
    public function cuentas_por_cobrar_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new CuentasPorCobrarExport($this->campos_consulta,$request->periodo), "cuentasporcobrar-".$request->periodo.".xlsx");   
    }

    //guardar configuracion tabla
    public function cuentas_por_cobrar_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'CuentasPorCobrar')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('cuentas_por_cobrar');
    }

}
