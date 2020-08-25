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


class CuentasPorPagarController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function cuentas_por_pagar(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->email, 'Ordenes de Trabajo');
        return view('registros.cuentasporpagar.cuentasporpagar', compact('serieusuario'));
    }
    //obtener registro tabla
    public function cuentas_por_pagar_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = DB::table('CxP as cp')
            ->Join('Proveedores as p', 'cp.Proveedor', '=', 'p.Numero')
            ->Join('Bancos as b', 'cp.Banco', '=', 'b.Numero')
            ->select('cp.Pago AS Pago', 'cp.Serie AS Serie', 'cp.Folio AS Folio', 'cp.Fecha AS Fecha', 'cp.Proveedor AS Proveedor', 'p.Nombre AS Nombre', 'b.nombre AS Banco', 'cp.Transferencia AS Transferencia', 'cp.Abono AS Abono', 'cp.Status AS Status', 'cp.MotivoBaja AS MotivoBaja', 'cp.Periodo AS Periodo')
            ->where('cp.Periodo', $periodo)
            ->orderBy('cp.Folio', 'DESC')
            ->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    if($data->Status != 'BAJA'){
                        $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Pago .'\')"><i class="material-icons">mode_edit</i></div> '. 
                        '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Pago .'\')"><i class="material-icons">cancel</i></div>  ';
                    }else{
                        $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Pago .'\')"><i class="material-icons">mode_edit</i></div> ';
                    }
                    return $boton;
                })
                ->addColumn('Abono', function($data){
                    $abono = Helpers::convertirvalorcorrecto($data->Abono);
                    return $abono;
                })
                ->rawColumns(['operaciones','Abono'])
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
        $compras = Compra::where('Proveedor', $request->Numero)->where('Status', 'POR PAGAR')->get();
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
                            '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm abonocompra"  name="abonocompra[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="calcularnuevosaldo('.$contadorfilas.');formatocorrectoinputcantidades(this);" >'.
                        '</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm saldocomprainicial" name="saldocomprainicial[]" value="'.Helpers::convertirvalorcorrecto($c->Saldo).'" readonly><input type="text" class="form-control divorinputmodsm saldocompra" name="saldocompra[]" value="'.Helpers::convertirvalorcorrecto($c->Saldo).'" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control contrarecibocompra" name="contrarecibocompra[]" readonly></td>'.

                    '</tr>';
                    $contadorfilas++;
            }
        }       
        $data = array(
            "filascompras" => $filascompras
        );
        return response()->json($data);
    }
    //guardar cuenta por pagar
    public function cuentas_por_pagar_guardar(Request $request){
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
            $Compra->Abonos = $Compra->Abonos + $request->abonocompra [$key];
            $Compra->Saldo = $request->saldocompra [$key];
            if($Compra->Saldo == 0){
                $Compra->Status = "LIQUIDADA";
            }
            $Compra->save();
        }
    	return response()->json($CuentaXPagar); 
    }
    //bajas cuentas por pagar
    public function cuentas_por_pagar_baja(Request $request){
        //tabla cuenta x pagar
        $CuentaXPagar = CuentaXPagar::where('Pago', $request->cxpdesactivar)->first();
        $CuentaXPagar->Abono = '0';
        $CuentaXPagar->MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        $CuentaXPagar->Status = 'BAJA';
        $CuentaXPagar->save();
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
        //obtener detalle cuenta por pagar
        $CuentaXPagarDetalle = CuentaXPagarDetalle::where('Pago', $request->cxpdesactivar)->get();
        foreach($CuentaXPagarDetalle as $cxpd){
            //tabla compras
            $Compra = Compra::where('Compra', $cxpd->Compra)->first();
            $Compra->Abonos = $Compra->Abonos - $cxpd->Abono;
            $Compra->Saldo = $Compra->Saldo + $cxpd->Abono;
            $Compra->Status = "POR PAGAR";
            $Compra->save();
            //tabla cuenta x pgar detalle
            $CuentaXPagarDetalleBaja = CuentaXPagarDetalle::where('Pago', $cxpd->Pago)->where('Compra', $cxpd->Compra)->first();
            $CuentaXPagarDetalleBaja->Abono = '0';
            $CuentaXPagarDetalleBaja->save();
        }
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
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm abonocompra"  name="abonocompra[]" value="'.Helpers::convertirvalorcorrecto($cxpd->Abono).'" readonly>'.Helpers::convertirvalorcorrecto($cxpd->Abono).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm saldocomprainicial" name="saldocomprainicial[]" value="'.Helpers::convertirvalorcorrecto($c->Saldo).'" readonly><input type="hidden" class="form-control divorinputmodsm saldocompra" name="saldocompra[]" value="'.Helpers::convertirvalorcorrecto($c->Saldo).'" readonly>'.Helpers::convertirvalorcorrecto($c->Saldo).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control contrarecibocompra" name="contrarecibocompra[]" readonly></td>'.
                '</tr>';
                $contadorfilas++;
        }
        $data = array(
            'CuentaXPagar' => $CuentaXPagar,
            'CuentaXPagarDetalle' => $CuentaXPagarDetalle,
            'fecha' => Helpers::formatoinputdate($CuentaXPagar->Fecha),
            'proveedor' => $Proveedor,
            'banco' => $Banco,
            'filasdetallecuentasporpagar' => $filasdetallecuentasporpagar,
            'abonototal' => $CuentaXPagar->Abono
        );
        return response()->json($data);
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
            $cuentasporpagar = CuentaXPagar::whereIn('Pago', $request->arraypdf)->orderBy('Folio', 'ASC')->take(500)->get(); 
        }else{
            //$contrarecibos = ContraRecibo::where('Fecha', $request->anopdf)->get(); 
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $cuentasporpagar = CuentaXPagar::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(500)->get();
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
                      "datadetalle" => $datadetalle
            );
        }
        //$footerHtml = view()->make('seccionespdf.footer', compact('fechaformato'))->render();
        $pdf = PDF::loadView('registros.cuentasporpagar.formato_pdf_cuentasporpagar', compact('data'))
        //->setOption('footer-html', $footerHtml, 'Página [page]')
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-bottom', 10);
        //return $pdf->download('contrarecibos.pdf');
        return $pdf->stream();
    }

}
