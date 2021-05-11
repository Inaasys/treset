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
use App\BitacoraDocumento;
use Luecano\NumeroALetras\NumeroALetras;
use App\Configuracion_Tabla;
use App\VistaCuentaPorPagar;
use App\ContraReciboDetalle;

class CuentasPorPagarController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'CuentasPorPagar')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function cuentas_por_pagar(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->user, 'CuentasPorPagar');
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
            $data = VistaCuentaPorPagar::select($this->campos_consulta)->where('Periodo', $periodo)->orderBy('Folio', 'DESC')->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $botoncambios   =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Pago .'\')"><i class="material-icons">mode_edit</i></div> '; 
                    $botonbajas     =    '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Pago .'\')"><i class="material-icons">cancel</i></div>  ';
                    $operaciones =  $botoncambios.$botonbajas;
                    return $operaciones;
                })
                ->addColumn('Abono', function($data){ return $data->Abono; })
                ->rawColumns(['operaciones'])
                ->make(true);
        } 
    }
    //obtener ultimo folio de cuentas por pagar
    public function cuentas_por_pagar_obtener_ultimo_folio(){
        $folio = Helpers::ultimofoliotablamodulos('App\CuentaXPagar');
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
    //obtener almacenes
    public function cuentas_por_pagar_obtener_bancos(Request $request){
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
                $contarnotacreditoproveedor = NotaProveedorDetalle::where('Proveedor', $request->Numero)->where('Compra', $c->Compra)->count();
                if($contarnotacreditoproveedor > 0){
                    $detallenotacreditoproveedor = NotaProveedorDetalle::where('Proveedor', $request->Numero)->where('Compra', $c->Compra)->first();
                    $notacredito = NotaProveedor::where('Nota', $detallenotacreditoproveedor->Nota)->where('STATUS', 'ALTA')->count();
                    if($notacredito > 0){
                        $detallenotacredito = $detallenotacreditoproveedor->Total;
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
                            '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm abonocompra"  name="abonocompra[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularnuevosaldo('.$contadorfilas.');" >'.
                        '</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm saldocomprainicial" name="saldocomprainicial[]" value="'.Helpers::convertirvalorcorrecto($c->Saldo).'" readonly><input type="text" class="form-control divorinputmodsm saldocompra" name="saldocompra[]" value="'.Helpers::convertirvalorcorrecto($c->Saldo).'" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control contrarecibocompra" name="contrarecibocompra[]" value="'.$contrarecibo.'" readonly>'.$contrarecibo.'</td>'.
                    '</tr>';
                    $contadorfilas++;
            }
        }       
        $data = array(
            "filascompras" => $filascompras,
            "numerocontrarecibos" => $numerocontrarecibos
        );
        return response()->json($data);
    }
    //guardar cuenta por pagar
    public function cuentas_por_pagar_guardar(Request $request){
        ini_set('max_input_vars','10000' );
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofoliotablamodulos('App\CuentaXPagar');
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
        $CuentaXPagar->Periodo=$request->periodohoy;
        $CuentaXPagar->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CXP";
        $BitacoraDocumento->Movimiento = $pago;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
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
            $contarnotacreditoproveedor = NotaProveedorDetalle::where('Proveedor', $CuentaXPagar->Proveedor)->where('Compra', $cxpd->Compra)->count();
            if($contarnotacreditoproveedor > 0){
                $detallenotacreditoproveedor = NotaProveedorDetalle::where('Proveedor', $CuentaXPagar->Proveedor)->where('Compra', $cxpd->Compra)->first();
                $notacredito = NotaProveedor::where('Nota', $detallenotacreditoproveedor->Nota)->where('STATUS', 'ALTA')->count();
                if($notacredito > 0){
                    $detallenotacredito = $detallenotacreditoproveedor->Total;
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
            'fecha' => Helpers::formatoinputdate($CuentaXPagar->Fecha),
            'proveedor' => $Proveedor,
            'banco' => $Banco,
            'filasdetallecuentasporpagar' => $filasdetallecuentasporpagar,
            'abonototal' => Helpers::convertirvalorcorrecto($CuentaXPagar->Abono),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($datas);
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
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'CuentasPorPagar')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('cuentas_por_pagar');
    }
}
