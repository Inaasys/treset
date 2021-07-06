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
use App\Exports\CuentasPorPagarExport;
use App\CuentaXPagar;
use App\CuentaXPagarDetalle;
use App\Banco;
use App\Proveedor;
use App\Compra;
use App\CompraDetalle;
use App\NotaProveedor;
use App\NotaProveedorDetalle;
use App\NotaProveedorDocumento;
use App\BitacoraDocumento;
use Luecano\NumeroALetras\NumeroALetras;
use App\Configuracion_Tabla;
use App\VistaCuentaPorPagar;
use App\ContraReciboDetalle;
use Config;
use Mail;
use App\Serie;

class CuentasPorPagarController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'CuentasPorPagar')->first();
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

    public function cuentas_por_pagar(){
        $serieusuario = 'A';
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('cuentas_por_pagar_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('cuentas_por_pagar_exportar_excel');
        $rutacreardocumento = route('cuentas_por_pagar_generar_pdfs');
        return view('registros.cuentasporpagar.cuentasporpagar', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }
    //obtener registro tabla
    public function cuentas_por_pagar_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            //$data = VistaCuentaPorPagar::select($this->campos_consulta)->where('Periodo', $periodo)->orderBy('Fecha', 'DESC')->orderBy('Serie', 'ASC')->orderBy('Folio', 'DESC')->get();
            $data = VistaCuentaPorPagar::select($this->campos_consulta)->where('Periodo', $periodo);
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
                                            '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Pago .'\')">Cambios</a></li>'.
                                            '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Pago .'\')">Bajas</a></li>'.
                                            '<li><a href="'.route('cuentas_por_pagar_generar_pdfs_indiv',$data->Pago).'" target="_blank">Ver Documento PDF</a></li>'.
                                            '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Pago .'\')">Enviar Documento por Correo</a></li>'.
                                        '</ul>'.
                                    '</div>';
                    return $operaciones;
                })
                ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                ->addColumn('Abono', function($data){ return $data->Abono; })
                ->rawColumns(['operaciones'])
                ->make(true);
        } 
    }
    //obtener series documento
    public function cuentas_por_pagar_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'CuentasPorPagar')->where('Usuario', Auth::user()->user)->get();
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
    public function cuentas_por_pagar_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\CuentaXPagar',$request->Serie);
        return response()->json($folio);
    }
    //obtener ultimo folio de cuentas por pagar
    public function cuentas_por_pagar_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\CuentaXPagar',$request->serie);
        return response()->json($folio);
    }
    //obtener proveedores
    public function cuentas_por_pagar_obtener_proveedores(Request $request){
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
    //obtener proveedor por numero
    public function cuentas_por_pagar_obtener_proveedor_por_numero(Request $request){
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
    
    //obtener bancos
    public function cuentas_por_pagar_obtener_bancos(Request $request){
        if($request->ajax()){
            $data = Banco::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $ultimatransferencia = VistaCuentaPorPagar::select("Transferencia")->where('Banco', $data->Numero)->orderBy("Transferencia", "DESC")->take(1)->get();
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarbanco('.$data->Numero.',\''.$data->Nombre .'\','.$ultimatransferencia[0]->Transferencia.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener banco por numero
    public function cuentas_por_pagar_obtener_banco_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $transferencia = 0;
        $existebanco = Banco::where('Numero', $request->numerobanco)->where('Status', 'ALTA')->count();
        if($existebanco > 0){
            $banco = Banco::where('Numero', $request->numerobanco)->where('Status', 'ALTA')->first();
            $ultimatransferencia = VistaCuentaPorPagar::select("Transferencia")->where('Banco', $request->numerobanco)->orderBy("Transferencia", "DESC")->take(1)->get();
            $transferencia = $ultimatransferencia[0]->Transferencia;
            $numero = $banco->Numero;
            $nombre = $banco->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'transferencia' => $transferencia
        );
        return response()->json($data); 
    }

    //obtener compras por proveedor
    public function cuentas_por_pagar_obtener_compras_proveedor(Request $request){
        $compras = Compra::where('Proveedor', $request->Numero)->where('Status', 'POR PAGAR')->orderBy('Fecha', 'ASC')->get();
        $numerocompras = Compra::where('Proveedor', $request->Numero)->where('Status', 'POR PAGAR')->count();
        $filascompras = '';
        $contadorfilas = 0;
        if($numerocompras > 0){
            foreach($compras as $c){
                //obtener nota de credito proveedor
                $detallenotacredito = 0;
                $contarnotacreditoproveedor = NotaProveedorDocumento::where('Compra', $c->Compra)->count();
                if($contarnotacreditoproveedor > 0){
                    $detallenotacreditoproveedor = NotaProveedorDocumento::where('Compra', $c->Compra)->get();
                    foreach($detallenotacreditoproveedor as $detalle){
                        $detallenotacredito = $detalle->Descuento;
                    }
                }
                $contrarecibo = '';
                $numerocontrarecibos = ContraReciboDetalle::where('Compra', $c->Compra)->count();
                if($numerocontrarecibos > 0){
                    $contrarecibodetalle = ContraReciboDetalle::where('Compra', $c->Compra)->first();
                    $contrarecibo = $contrarecibodetalle->ContraRecibo;
                }
                $filascompras= $filascompras.
                    '<tr class="filascompras" id="filacompra'.$contadorfilas.'">'.
                        '<td class="tdmod"></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm compra" name="compra[]" value="'.$c->Compra.'" readonly>'.$c->Compra.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm facturacompra" name="facturacompra[]" value="'.$c->Factura.'" readonly>'.$c->Factura.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm fechacompra" name="fechacompra[]" value="'.Helpers::fecha_espanol($c->Fecha).'" readonly>'.Helpers::fecha_espanol($c->Fecha).'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm plazocompra" name="plazocompra[]" value="'.$c->Plazo.'" readonly>'.$c->Plazo.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm vencecompra" name="vencecompra[]" value="'.Helpers::fecha_espanol(Carbon::parse($c->Fecha)->addDays($c->Plazo)->toDateTimeString()).'" readonly>'.Helpers::fecha_espanol(Carbon::parse($c->Fecha)->addDays($c->Plazo)->toDateTimeString()).'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm totalcompra" name="totalcompra[]" value="'.Helpers::convertirvalorcorrecto($c->Total).'" readonly>'.Helpers::convertirvalorcorrecto($c->Total).'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm abonoscompra" name="abonoscompra[]" value="'.Helpers::convertirvalorcorrecto($c->Abonos).'" readonly>'.Helpers::convertirvalorcorrecto($c->Abonos).'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm notascreditocompra" name="notascreditocompra[]" value="'.Helpers::convertirvalorcorrecto($detallenotacredito).'" readonly>'.Helpers::convertirvalorcorrecto($detallenotacredito).'</td>'.
                        '<td class="tdmod">'.
                            '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm abonocompra"  name="abonocompra[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularnuevosaldo('.$contadorfilas.');">'.
                        '</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm saldocomprainicial" name="saldocomprainicial[]" value="'.Helpers::convertirvalorcorrecto($c->Saldo).'" readonly><input type="text" class="form-control divorinputmodsm saldocompra" name="saldocompra[]" value="'.Helpers::convertirvalorcorrecto($c->Saldo).'"  ondblclick="saldarcompra('.$contadorfilas.')" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control contrarecibocompra" name="contrarecibocompra[]" value="'.$contrarecibo.'" readonly>'.$contrarecibo.'</td>'.
                    '</tr>';
                    $contadorfilas++;
            }
        }else{
            $numerocontrarecibos = 0;
        }       
        $data = array(
            "filascompras" => $filascompras,
            "numerocontrarecibos" => $numerocontrarecibos
        );
        return response()->json($data);
    }
    //guardar cuenta por pagar
    public function cuentas_por_pagar_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\CuentaXPagar', $request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $pago = $folio.'-'.$request->serie;
		$CuentaXPagar = new CuentaXPagar;
		$CuentaXPagar->Pago=$pago;
		$CuentaXPagar->Serie=$request->serie;
		$CuentaXPagar->Folio=$folio;
        $CuentaXPagar->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $CuentaXPagar->Proveedor=$request->numeroproveedor;
		$CuentaXPagar->Banco=$request->numerobanco;
		$CuentaXPagar->Cheque=$request->cheque;
		$CuentaXPagar->Transferencia=$request->transferencia;
        $CuentaXPagar->Beneficiario=$request->beneficiario;
        $CuentaXPagar->Abono=$request->total;
        $CuentaXPagar->CuentaDeposito=$request->cuentadeposito;  
        $CuentaXPagar->Anotacion=$request->anotacion;
        $CuentaXPagar->Status="ALTA";
        $CuentaXPagar->Usuario=Auth::user()->user;
        $CuentaXPagar->Periodo=$this->periodohoy;
        $CuentaXPagar->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CXP";
        $BitacoraDocumento->Movimiento = $pago;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->compra as $key => $c){     
            if($request->abonocompra [$key] > Helpers::convertirvalorcorrecto(0)){        
                $CuentaXPagarDetalle=new CuentaXPagarDetalle;
                $CuentaXPagarDetalle->Pago = $pago;
                $CuentaXPagarDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $CuentaXPagarDetalle->Proveedor = $request->numeroproveedor;
                $CuentaXPagarDetalle->Compra = $c;
                $CuentaXPagarDetalle->Abono = $request->abonocompra [$key];
                $CuentaXPagarDetalle->Item = $item;
                $CuentaXPagarDetalle->save();
                $item++;
                //modificar abonos y saldo en compra
                $Compra = Compra::where('Compra', $c)->first();
                $NuevoAbono = $Compra->Abonos + $request->abonocompra [$key];
                $NuevoSaldo = $request->saldocompra [$key];
                if($request->saldocompra [$key] == 0){
                    $Status = "LIQUIDADA";
                }else{
                    $Status = "POR PAGAR"; 
                }
                Compra::where('Compra', $c)
                            ->update([
                                'Abonos' => Helpers::convertirvalorcorrecto($NuevoAbono),
                                'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo),
                                'Status' => $Status
                            ]);
            }
        }
    	return response()->json($CuentaXPagar); 
    }

    //comprobar baja de documento
    public function cuentas_por_pagar_comprobar_baja(Request $request){
        $CuentaXPagar = CuentaXPagar::where('Pago', $request->cxpdesactivar)->first();
        $resultadofechas = Helpers::compararanoymesfechas($CuentaXPagar->Fecha);
        $data = array (
            'resultadofechas' => $resultadofechas,
            'Status' => $CuentaXPagar->Status
        );
        return response()->json($data);
    }

    //bajas cuentas por pagar
    public function cuentas_por_pagar_baja(Request $request){
        $CuentaXPagar = CuentaXPagar::where('Pago', $request->cxpdesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        CuentaXPagar::where('Pago', $request->cxpdesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Abono' => '0.000000'
                ]);
        $detalles = CuentaXPagarDetalle::where('Pago', $request->cxpdesactivar)->get();
        foreach($detalles as $detalle){
            //restar abono de la compra
            $Compra = Compra::where('Compra', $detalle->Compra)->first();
            $NuevoAbono = $Compra->Abonos - $detalle->Abono;
            $NuevoSaldo = $Compra->Saldo + $detalle->Abono;
            Compra::where('Compra', $detalle->Compra)
                        ->update([
                            'Abonos' => Helpers::convertirvalorcorrecto($NuevoAbono),
                            'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo),
                            'Status' => "POR PAGAR"
                        ]);
            //tabla cuenta x pgar detalle
            CuentaXPagarDetalle::where('Pago', $detalle->Pago)
                            ->where('Compra', $detalle->Compra)
                            ->update([
                                'Abono' => '0.000000'
                            ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CXP";
        $BitacoraDocumento->Movimiento = $request->cxpdesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $CuentaXPagar->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($CuentaXPagar);
    }
    //obtener cuentas por pagar
    public function cuentas_por_pagar_obtener_cuenta_por_pagar(Request $request){
        $CuentaXPagar = CuentaXPagar::where('Pago', $request->cxpmodificar)->first();
        $CuentaXPagarDetalle = CuentaXPagarDetalle::where('Pago', $request->cxpmodificar)->get();
        $Proveedor = Proveedor::where('Numero', $CuentaXPagar->Proveedor)->first();
        $Banco = Banco::where('Numero', $CuentaXPagar->Banco)->first();
        $filasdetallecuentasporpagar = '';
        $contadorfilas = 0;
        foreach($CuentaXPagarDetalle as $cxpd){
            //obtener nota de credito proveedor
            $detallenotacredito = 0;
            $contarnotacreditoproveedor = NotaProveedorDocumento::where('Compra', $cxpd->Compra)->count();
            if($contarnotacreditoproveedor > 0){
                $detallenotacreditoproveedor = NotaProveedorDocumento::where('Compra', $cxpd->Compra)->get();
                foreach($detallenotacreditoproveedor as $detalle){
                    $detallenotacredito = $detalle->Descuento;
                }
            }
            $c = Compra::where('Compra', $cxpd->Compra)->first();
            $filasdetallecuentasporpagar= $filasdetallecuentasporpagar.
                '<tr class="filascompras" id="filacompra'.$contadorfilas.'">'.
                    '<td class="tdmod"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm compra" name="compra[]" value="'.$c->Compra.'" readonly>'.$c->Compra.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm facturacompra" name="facturacompra[]" value="'.$c->Factura.'" readonly>'.$c->Factura.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm fechacompra" name="fechacompra[]" value="'.Helpers::fecha_espanol($c->Fecha).'" readonly>'.Helpers::fecha_espanol($c->Fecha).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm plazocompra" name="plazocompra[]" value="'.$c->Plazo.'" readonly>'.$c->Plazo.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm vencecompra" name="vencecompra[]" value="'.Helpers::fecha_espanol(Carbon::parse($c->Fecha)->addDays($c->Plazo)->toDateTimeString()).'" readonly>'.Helpers::fecha_espanol(Carbon::parse($c->Fecha)->addDays($c->Plazo)->toDateTimeString()).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm totalcompra" name="totalcompra[]" value="'.Helpers::convertirvalorcorrecto($c->Total).'" readonly>'.Helpers::convertirvalorcorrecto($c->Total).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm abonoscompra" name="abonoscompra[]" value="'.Helpers::convertirvalorcorrecto($c->Abonos).'" readonly>'.Helpers::convertirvalorcorrecto($c->Abonos).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm notascreditocompra" name="notascreditocompra[]" value="'.Helpers::convertirvalorcorrecto($detallenotacredito).'" readonly>'.Helpers::convertirvalorcorrecto($detallenotacredito).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm abonocompra"  name="abonocompra[]" value="'.Helpers::convertirvalorcorrecto($cxpd->Abono).'" onchange="formatocorrectoinputcantidades(this);" readonly>'.Helpers::convertirvalorcorrecto($cxpd->Abono).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm saldocomprainicial" name="saldocomprainicial[]" value="'.Helpers::convertirvalorcorrecto($c->Saldo).'" readonly><input type="hidden" class="form-control divorinputmodsm saldocompra" name="saldocompra[]" value="'.Helpers::convertirvalorcorrecto($c->Saldo).'" readonly>'.Helpers::convertirvalorcorrecto($c->Saldo).'</td>'.
                '</tr>';
                $contadorfilas++;
        }
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($CuentaXPagar->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($CuentaXPagar->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($CuentaXPagar->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }
        $data = array(
            'CuentaXPagar' => $CuentaXPagar,
            'CuentaXPagarDetalle' => $CuentaXPagarDetalle,
            'fecha' => Helpers::formatoinputdatetime($CuentaXPagar->Fecha),
            'proveedor' => $Proveedor,
            'banco' => $Banco,
            'filasdetallecuentasporpagar' => $filasdetallecuentasporpagar,
            'abonototal' => Helpers::convertirvalorcorrecto($CuentaXPagar->Abono),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }
    //cambios
    public function cuentas_por_pagar_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        //INGRESAR DATOS A TABLA
        $cuentaxpagar = $request->folio.'-'.$request->serie;
		$CuentaXPagar = CuentaXPagar::where('Pago', $cuentaxpagar)->first();
        //modificar
        CuentaXPagar::where('Pago', $cuentaxpagar)
        ->update([
            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Banco' => $request->numerobanco,
            'Cheque' => $request->cheque,
            'Transferencia' => $request->transferencia,
            'Beneficiario' => $request->beneficiario,
            'CuentaDeposito' => $request->cuentadeposito,  
            'Anotacion' => $request->anotacion
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CXP";
        $BitacoraDocumento->Movimiento = $cuentaxpagar;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $CuentaXPagar->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
    	return response()->json($CuentaXPagar);
    }
    //buscar folio on key up
    public function cuentas_por_pagar_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = CuentaXPagar::where('Pago', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Pago .'\')"><i class="material-icons">done</i></div> ';
                    return $boton;
                })
                ->addColumn('Abono', function($data){
                    $abono = Helpers::convertirvalorcorrecto($data->Abono);
                    return $abono;
                })
                ->rawColumns(['operaciones','Total'])
                ->make(true);
        } 
    }
    //generacion de formato en PDF
    public function cuentas_por_pagar_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $cuentasporpagar = CuentaXPagar::whereIn('Pago', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $cuentasporpagar = CuentaXPagar::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cuentasporpagar as $cxp){
            $formatter = new NumeroALetras;
            $abonoletras =  $formatter->toInvoice($cxp->Abono, $this->numerodecimales, 'M.N.');
            $cuentaporpagardetalle = CuentaXPagarDetalle::where('Pago', $cxp->Pago)->get();
            $datadetalle=array();
            foreach($cuentaporpagardetalle as $cxpd){
                $proveedordetalle = Proveedor::where('Numero', $cxpd->Proveedor)->first();
                $contarcompradetalle = Compra::where('Compra', $cxpd->Compra)->count();
                $compradetalle = Compra::where('Compra', $cxpd->Compra)->first();
                if($contarcompradetalle == 0){
                    $remisiondetalle = "";
                    $facturadetalle = "";
                }else{
                    $remisiondetalle = $compradetalle->Remision;
                    $facturadetalle = $compradetalle->Factura;
                }
                $datadetalle[]=array(
                    "proveedordetalle"=> $proveedordetalle,
                    "remisiondetalle"=>$remisiondetalle,
                    "facturadetalle"=>$facturadetalle,
                    "abonodetalle" => Helpers::convertirvalorcorrecto($cxpd->Abono)
                );
            } 
            $banco = Banco::where('Numero', $cxp->Banco)->first();
            $proveedor = Proveedor::where('Numero', $cxp->Proveedor)->first();
            $data[]=array(
                      "cuentaporpagar"=>$cxp,
                      "fechaespanolcuentaporpagar"=>Helpers::fecha_espanol($cxp->Fecha),
                      "abonocuentaporpagar"=>Helpers::convertirvalorcorrecto($cxp->Abono),
                      "abonoletras"=>$abonoletras,
                      "proveedor" => $proveedor,
                      "banco"=> $banco,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cuentasporpagar.formato_pdf_cuentasporpagar', compact('data'))
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
    public function cuentas_por_pagar_generar_pdfs_indiv($documento){
        $cuentasporpagar = CuentaXPagar::where('Pago', $documento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cuentasporpagar as $cxp){
            $formatter = new NumeroALetras;
            $abonoletras =  $formatter->toInvoice($cxp->Abono, $this->numerodecimales, 'M.N.');
            $cuentaporpagardetalle = CuentaXPagarDetalle::where('Pago', $cxp->Pago)->get();
            $datadetalle=array();
            foreach($cuentaporpagardetalle as $cxpd){
                $proveedordetalle = Proveedor::where('Numero', $cxpd->Proveedor)->first();
                $contarcompradetalle = Compra::where('Compra', $cxpd->Compra)->count();
                $compradetalle = Compra::where('Compra', $cxpd->Compra)->first();
                if($contarcompradetalle == 0){
                    $remisiondetalle = "";
                    $facturadetalle = "";
                }else{
                    $remisiondetalle = $compradetalle->Remision;
                    $facturadetalle = $compradetalle->Factura;
                }
                $datadetalle[]=array(
                    "proveedordetalle"=> $proveedordetalle,
                    "remisiondetalle"=>$remisiondetalle,
                    "facturadetalle"=>$facturadetalle,
                    "abonodetalle" => Helpers::convertirvalorcorrecto($cxpd->Abono)
                );
            } 
            $banco = Banco::where('Numero', $cxp->Banco)->first();
            $proveedor = Proveedor::where('Numero', $cxp->Proveedor)->first();
            $data[]=array(
                      "cuentaporpagar"=>$cxp,
                      "fechaespanolcuentaporpagar"=>Helpers::fecha_espanol($cxp->Fecha),
                      "abonocuentaporpagar"=>Helpers::convertirvalorcorrecto($cxp->Abono),
                      "abonoletras"=>$abonoletras,
                      "proveedor" => $proveedor,
                      "banco"=> $banco,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cuentasporpagar.formato_pdf_cuentasporpagar', compact('data'))
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
    public function cuentas_por_pagar_obtener_datos_envio_email(Request $request){
        $cuentaxpagar = CuentaXPagar::where('Pago', $request->documento)->first();
        $proveedor = Proveedor::where('Numero',$cuentaxpagar->Proveedor)->first();
        $data = array(
            'cuentaxpagar' => $cuentaxpagar,
            'proveedor' => $proveedor,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $proveedor->Email1
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function cuentas_por_pagar_enviar_pdfs_email(Request $request){
        $cuentasporpagar = CuentaXPagar::where('Pago', $request->emaildocumento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cuentasporpagar as $cxp){
            $formatter = new NumeroALetras;
            $abonoletras =  $formatter->toInvoice($cxp->Abono, $this->numerodecimales, 'M.N.');
            $cuentaporpagardetalle = CuentaXPagarDetalle::where('Pago', $cxp->Pago)->get();
            $datadetalle=array();
            foreach($cuentaporpagardetalle as $cxpd){
                $proveedordetalle = Proveedor::where('Numero', $cxpd->Proveedor)->first();
                $contarcompradetalle = Compra::where('Compra', $cxpd->Compra)->count();
                $compradetalle = Compra::where('Compra', $cxpd->Compra)->first();
                if($contarcompradetalle == 0){
                    $remisiondetalle = "";
                    $facturadetalle = "";
                }else{
                    $remisiondetalle = $compradetalle->Remision;
                    $facturadetalle = $compradetalle->Factura;
                }
                $datadetalle[]=array(
                    "proveedordetalle"=> $proveedordetalle,
                    "remisiondetalle"=>$remisiondetalle,
                    "facturadetalle"=>$facturadetalle,
                    "abonodetalle" => Helpers::convertirvalorcorrecto($cxpd->Abono)
                );
            } 
            $banco = Banco::where('Numero', $cxp->Banco)->first();
            $proveedor = Proveedor::where('Numero', $cxp->Proveedor)->first();
            $data[]=array(
                      "cuentaporpagar"=>$cxp,
                      "fechaespanolcuentaporpagar"=>Helpers::fecha_espanol($cxp->Fecha),
                      "abonocuentaporpagar"=>Helpers::convertirvalorcorrecto($cxp->Abono),
                      "abonoletras"=>$abonoletras,
                      "proveedor" => $proveedor,
                      "banco"=> $banco,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cuentasporpagar.formato_pdf_cuentasporpagar', compact('data'))
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
                        ->attachData($pdf->output(), "CuentaPorPagarNo".$emaildocumento.".pdf");
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
    public function cuentas_por_pagar_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new CuentasPorPagarExport($this->campos_consulta,$request->periodo), "cuentasporpagar-".$request->periodo.".xlsx");   
    
    }
    //configuracion de la tabla
    public function cuentas_por_pagar_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        Configuracion_Tabla::where('tabla', 'CuentasPorPagar')
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
        return redirect()->route('cuentas_por_pagar');
    }
}
