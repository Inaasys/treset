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
use App\Exports\ContraRecibosExport;
use App\ContraRecibo;
use App\ContraReciboDetalle;
use App\Proveedor;
use App\Compra;
use App\BitacoraDocumento;
use App\Configuracion_Tabla;
use App\VistaContraRecibo;
use Config;
use Mail;

class ContraRecibosController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'ContraRecibos')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function contrarecibos(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->user, 'ContraRecibos');
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('contrarecibos_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('contrarecibos_exportar_excel');
        $rutacreardocumento = route('contrarecibos_generar_pdfs');
        return view('registros.contrarecibos.contrarecibos', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }
    //obtener registro tabla
    public function contrarecibos_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaContraRecibo::select($this->campos_consulta)->orderBy('Folio', 'DESC')->where('Periodo', $periodo)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $botoncambios   = '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->ContraRecibo .'\')"><i class="material-icons">mode_edit</i></div> ';
                    $botonbaja      = '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->ContraRecibo .'\')"><i class="material-icons">cancel</i></div> ';
                    $botondocumentopdf = '<a href="'.route('contrarecibos_generar_pdfs_indiv',$data->ContraRecibo).'" target="_blank"><div class="btn bg-blue-grey btn-xs waves-effect" data-toggle="tooltip" title="Generar Documento"><i class="material-icons">archive</i></div></a> ';
                    $botonenviaremail = '<div class="btn bg-brown btn-xs waves-effect" data-toggle="tooltip" title="Enviar Documento por Correo" onclick="enviardocumentoemail(\''.$data->ContraRecibo .'\')"><i class="material-icons">email</i></div> ';
                    $boton =  $botoncambios.$botonbaja.$botondocumentopdf.$botonenviaremail;
                    return $boton;
                })
                ->addColumn('Total', function($data){ return $data->Total; })
                ->rawColumns(['operaciones','Total'])
                ->make(true);
        } 
    }
    //obtener ultimo folio
    public function contrarecibos_obtener_ultimo_folio(){
        $folio = Helpers::ultimofoliotablamodulos('App\ContraRecibo');
        return response()->json($folio);
    }
    //obtener proveedor
    public function contrarecibos_obtener_proveedores(Request $request){
        if($request->ajax()){
            $data = Proveedor::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $fechahoy = Carbon::now()->toDateTimeString();
                        $fechahoyespanol = Helpers::fecha_espanol(Carbon::now()->toDateTimeString());
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarproveedor('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$fechahoy .'\',\''.$fechahoyespanol .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener compras proveedor
    public function contrarecibos_obtener_compras_proveedor(Request $request){
        $compras = Compra::where('Proveedor', $request->Numero)->where('Status', 'POR PAGAR')->orderBy('Folio', 'ASC')->get();
        $numerocompras = Compra::where('Proveedor', $request->Numero)->where('Status', 'POR PAGAR')->count();
        $filascompras = '';
        $contadorfilas = 0;
        $tipo = "alta";
        if($numerocompras > 0){
            foreach($compras as $c){
                $existedetallecontrarecibo = ContraReciboDetalle::where('Compra', $c->Compra)->count();
                if($existedetallecontrarecibo == 0){
                        $filascompras= $filascompras.
                        '<tr class="filascompras" id="filacompra'.$contadorfilas.'">'.
                            '<td class="tdmod"></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm compra" name="compra[]" value="'.$c->Compra.'" readonly data-parsley-length="[1, 20]">'.$c->Compra.'</td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm facturacompra" name="facturacompra[]" value="'.$c->Factura.'" readonly data-parsley-length="[1, 20]">'.$c->Factura.'</td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm remisioncompra" name="remisioncompra[]" value="'.$c->Remision.'" readonly data-parsley-length="[1, 20]">'.$c->Remision.'</td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm fechafacturacompra" name="fechafacturacompra[]" value="'.$c->Fecha.'" readonly>'.Helpers::fecha_espanol($c->Fecha).'</td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm plazocompra" name="plazocompra[]" value="'.$c->Plazo.'" readonly>'.$c->Plazo.'</td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm fechapagarproveedor" name="fechapagarproveedor[]" value="'.Carbon::parse($c->Fecha)->addDays($c->Plazo)->toDateTimeString().'" readonly><b class="fechaespanoltexto">'.Helpers::fecha_espanol(Carbon::parse($c->Fecha)->addDays($c->Plazo)->toDateTimeString()).'</b></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm totalcompra" name="totalcompra[]" value="'.Helpers::convertirvalorcorrecto($c->Total).'" readonly>'.Helpers::convertirvalorcorrecto($c->Total).'</td>'.
                            '<td class="tdmod text-center">'.
                                '<input type="checkbox" name="contrarecibocompra[]" id="idcontrarecibocompra'.$contadorfilas.'" class="contrarecibocompra filled-in" value="0" onchange="calculartotalcontrarecibo(\''.$tipo .'\');" required>'.
                                '<label for="idcontrarecibocompra'.$contadorfilas.'" ></label>'.
                            '</td>'.
                        '</tr>';
                        $contadorfilas++;
                }
            }
        }       
        $data = array(
            "filascompras" => $filascompras
        );
        return response()->json($data);
    }
    //obtener compras proveedor por numero
    public function contrarecibos_obtener_compras_proveedor_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $filascompras = '';
        $existeproveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->count();
        if($existeproveedor > 0){
            $proveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->first();
            $numero = $proveedor->Numero;
            $nombre = $proveedor->Nombre;
            $compras = Compra::where('Proveedor', $request->numeroproveedor)->where('Status', 'POR PAGAR')->orderBy('Folio', 'ASC')->get();
            $numerocompras = Compra::where('Proveedor', $request->numeroproveedor)->where('Status', 'POR PAGAR')->count();
            $contadorfilas = 0;
            $tipo = "alta";
            if($numerocompras > 0){
                foreach($compras as $c){
                    $existedetallecontrarecibo = ContraReciboDetalle::where('Compra', $c->Compra)->count();
                    if($existedetallecontrarecibo == 0){
                            $filascompras= $filascompras.
                            '<tr class="filascompras" id="filacompra'.$contadorfilas.'">'.
                                '<td class="tdmod"></td>'.
                                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm compra" name="compra[]" value="'.$c->Compra.'" readonly data-parsley-length="[1, 20]">'.$c->Compra.'</td>'.
                                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm facturacompra" name="facturacompra[]" value="'.$c->Factura.'" readonly data-parsley-length="[1, 20]">'.$c->Factura.'</td>'.
                                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm remisioncompra" name="remisioncompra[]" value="'.$c->Remision.'" readonly data-parsley-length="[1, 20]">'.$c->Remision.'</td>'.
                                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm fechafacturacompra" name="fechafacturacompra[]" value="'.$c->Fecha.'" readonly>'.Helpers::fecha_espanol($c->Fecha).'</td>'.
                                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm plazocompra" name="plazocompra[]" value="'.$c->Plazo.'" readonly>'.$c->Plazo.'</td>'.
                                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm fechapagarproveedor" name="fechapagarproveedor[]" value="'.Carbon::parse($c->Fecha)->addDays($c->Plazo)->toDateTimeString().'" readonly><b class="fechaespanoltexto">'.Helpers::fecha_espanol(Carbon::parse($c->Fecha)->addDays($c->Plazo)->toDateTimeString()).'</b></td>'.
                                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm totalcompra" name="totalcompra[]" value="'.Helpers::convertirvalorcorrecto($c->Total).'" readonly>'.Helpers::convertirvalorcorrecto($c->Total).'</td>'.
                                '<td class="tdmod text-center">'.
                                    '<input type="checkbox" name="contrarecibocompra[]" id="idcontrarecibocompra'.$contadorfilas.'" class="contrarecibocompra filled-in" value="0" onchange="calculartotalcontrarecibo(\''.$tipo .'\');" required>'.
                                    '<label for="idcontrarecibocompra'.$contadorfilas.'" ></label>'.
                                '</td>'.
                            '</tr>';
                            $contadorfilas++;
                    }
                }
            }  
        }    
        $fechahoy = Carbon::now()->toDateTimeString();
        $fechahoyespanol = Helpers::fecha_espanol(Carbon::now()->toDateTimeString()); 
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            "filascompras" => $filascompras,
            'fechahoy' => $fechahoy,
            'fechahoyespanol' => $fechahoyespanol
        );
        return response()->json($data);
    }
    //guardar contrarecibo
    public function contrarecibos_guardar(Request $request){
        ini_set('max_input_vars','10000' );
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofoliotablamodulos('App\ContraRecibo');
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $contrarecibo = $folio.'-'.$request->serie;
		$ContraRecibo = new ContraRecibo;
		$ContraRecibo->ContraRecibo = $contrarecibo;
		$ContraRecibo->Serie = $request->serie;
		$ContraRecibo->Folio = $folio;
        $ContraRecibo->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
        $ContraRecibo->Proveedor = $request->numeroproveedor;
		$ContraRecibo->Facturas = $request->numerofacturas;
		$ContraRecibo->Total = $request->totalcontrarecibos;
        $ContraRecibo->Obs = $request->observaciones;
        $ContraRecibo->Status = "ALTA";
        $ContraRecibo->Usuario = Auth::user()->user;
        $ContraRecibo->Periodo = $this->periodohoy;
        $ContraRecibo->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CONTRARECIBOS";
        $BitacoraDocumento->Movimiento = $contrarecibo;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        foreach ($request->compra as $key => $c){  
            $checkbox = isset($request->contrarecibocompra [$key]) ? $request->contrarecibocompra [$key] : 0;
            if($checkbox == 1){
                $ContraReciboDetalle = new ContraReciboDetalle;
                $ContraReciboDetalle->ContraRecibo = $contrarecibo;
                $ContraReciboDetalle->Fecha = $request->fechafacturacompra [$key];
                $ContraReciboDetalle->Proveedor = $request->numeroproveedor;
                $ContraReciboDetalle->Compra = $c;
                $ContraReciboDetalle->Factura = $request->facturacompra [$key];
                $ContraReciboDetalle->Remision = $request->remisioncompra [$key];
                $ContraReciboDetalle->Plazo = $request->plazocompra [$key];
                $ContraReciboDetalle->FechaAPagar = $request->fechapagarproveedor [$key];
                $ContraReciboDetalle->Total = $request->totalcompra [$key];
                $ContraReciboDetalle->save();
            }   
        }
    	return response()->json($ContraRecibo);
    }

    //verificar baja
    public function contrarecibos_verificar_si_continua_baja(Request $request){
        $ContraRecibo = ContraRecibo::where('ContraRecibo', $request->contrarecibodesactivar)->first(); 
        $resultadofechas = Helpers::compararanoymesfechas($ContraRecibo->Fecha);
        $data = array (
            'resultadofechas' => $resultadofechas,
            'Status' => $ContraRecibo->Status
        );
        return response()->json($data);
    }

    //dar de baja contrarecibo
    public function contrarecibos_baja(Request $request){
        $ContraRecibo = ContraRecibo::where('ContraRecibo', $request->contrarecibodesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        ContraRecibo::where('ContraRecibo', $request->contrarecibodesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Facturas' => '0',
                    'Total' => '0.000000'
                ]);
        //obtener detalle cuenta por pagar
        $ContraReciboDetalle = ContraReciboDetalle::where('ContraRecibo', $request->contrarecibodesactivar)->get();
        foreach($ContraReciboDetalle as $crd){
            //colocar en ceros cantidades
            ContraReciboDetalle::where('ContraRecibo', $crd->ContraRecibo)
            ->where('Compra', $crd->Compra)
            ->update([
                'Compra' => 'BAJA',
                'Factura' => '',
                'Total' => '0.000000'
            ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CONTRARECIBOS";
        $BitacoraDocumento->Movimiento = $request->contrarecibodesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = 'BAJA';
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($ContraRecibo);
    }
    //Obtener contrarecibo
    public function contrarecibos_obtener_contrarecibo(Request $request){
        $contrarecibo = ContraRecibo::where('ContraRecibo', $request->contrarecibomodificar)->first();
        $detallescontrarecibo = ContraReciboDetalle::where('ContraRecibo', $request->contrarecibomodificar)->get();
        $proveedor = Proveedor::where('Numero', $contrarecibo->Proveedor)->first();
        $filasdetallescontrarecibo = '';
        $contadorfilas = 0;
        $tipo = "modificacion";
        foreach($detallescontrarecibo as $dcr){
            $filasdetallescontrarecibo= $filasdetallescontrarecibo.
            '<tr class="filascompras" id="filacompra'.$contadorfilas.'">'.
                '<td class="tdmod"><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm compra" name="compra[]" value="'.$dcr->Compra.'" readonly data-parsley-length="[1, 20]">'.$dcr->Compra.'</td>'.
                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm facturacompra" name="facturacompra[]" value="'.$dcr->Factura.'" readonly data-parsley-length="[1, 20]">'.$dcr->Factura.'</td>'.
                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm remisioncompra" name="remisioncompra[]" value="'.$dcr->Remision.'" readonly data-parsley-length="[1, 20]">'.$dcr->Remision.'</td>'.
                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm fechafacturacompra" name="fechafacturacompra[]" value="'.$dcr->Fecha.'" readonly>'.Helpers::fecha_espanol($dcr->Fecha).'</td>'.
                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm plazocompra" name="plazocompra[]" value="'.$dcr->Plazo.'" readonly>'.$dcr->Plazo.'</td>'.
                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm fechapagarproveedor" name="fechapagarproveedor[]" value="'.$dcr->FechaAPagar.'" readonly><b class="fechaespanoltexto">'.Helpers::fecha_espanol($dcr->FechaAPagar).'</b></td>'.
                '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm totalcompra" name="totalcompra[]" value="'.Helpers::convertirvalorcorrecto($dcr->Total).'" readonly>'.Helpers::convertirvalorcorrecto($dcr->Total).'</td>'.
                '<td class="tdmod text-center">'.
                    '<input type="checkbox" name="contrarecibocompra[]" id="idcontrarecibocompra'.$contadorfilas.'" class="contrarecibocompra filled-in" value="1" onchange="calculartotalcontrarecibo(\''.$tipo .'\');" required checked>'.
                    '<label for="idcontrarecibocompra'.$contadorfilas.'" ></label>'.
                '</td>'.
            '</tr>';
            $contadorfilas++;
        }
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($contrarecibo->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($contrarecibo->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($contrarecibo->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        } 
        $data = array(
            "contrarecibo" => $contrarecibo,
            "detallescontrarecibo" => $detallescontrarecibo,
            "proveedor" => $proveedor,
            "fecha" => Helpers::formatoinputdate($contrarecibo->Fecha),
            "total" => Helpers::convertirvalorcorrecto($contrarecibo->Total),
            "filasdetallescontrarecibo" => $filasdetallescontrarecibo,
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }
    //cambios
    public function contrarecibos_guardar_modificacion(Request $request){
        ini_set('max_input_vars','10000' );
        //INGRESAR DATOS A TABLA
        $contrarecibo = $request->folio.'-'.$request->serie;
		$ContraRecibo = ContraRecibo::where('ContraRecibo', $contrarecibo)->first();
        //validar si las partidas en las modiifcacion son las mismas que la base
        // si no son las mismas comparar y eliminar las partidas que corresponden en la tabla detalles
        //array partidas antes de modificacion
        $ArrayDetallesContraReciboAnterior = Array();
        $DetallesContraReciboAnterior = ContraReciboDetalle::where('ContraRecibo', $contrarecibo)->get();
        foreach($DetallesContraReciboAnterior as $detalle){
            array_push($ArrayDetallesContraReciboAnterior, $detalle->ContraRecibo.'#'.$detalle->Compra);
        }
        //array partida despues de modificacion
        $ArrayDetallesContraReciboNuevo = Array();
        foreach ($request->compra as $key => $nuevacompra){
            if($request->agregadoen [$key] == 'NA'){
                array_push($ArrayDetallesContraReciboNuevo, $contrarecibo.'#'.$nuevacompra);
            } 
        }  
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesContraReciboAnterior, $ArrayDetallesContraReciboNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                //eliminar detalle
                $eliminardetallecontrarecibo = ContraReciboDetalle::where('ContraRecibo', $explode_d[0])->where('Compra', $explode_d[1])->forceDelete();
            }
        }
        //modificar orden compra
        ContraRecibo::where('ContraRecibo', $contrarecibo)
        ->update([
            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Facturas' => $request->numerofacturas,
            'Total' => $request->totalcontrarecibos,
            'Obs' => $request->observaciones
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CONTRARECIBOS";
        $BitacoraDocumento->Movimiento = $contrarecibo;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $ContraRecibo->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $detallesporsurtir = 0;
        foreach ($request->compra as $key => $c){    
                //modificar detalle
                ContraReciboDetalle::where('ContraRecibo', $contrarecibo)
                ->where('Compra', $c)
                ->update([
                    'Fecha' => $request->fechafacturacompra [$key],
                    'Compra' => $c,
                    'Factura' => $request->facturacompra [$key],
                    'Remision' => $request->remisioncompra [$key],
                    'Plazo' => $request->plazocompra [$key],
                    'FechaAPagar' => $request->fechapagarproveedor [$key],
                    'Total' => $request->totalcompra [$key]
                ]);
        }
    	return response()->json($ContraRecibo);   
    }
    //buscar folio on key up
    public function contrarecibos_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = ContraRecibo::where('Contrarecibo', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->ContraRecibo .'\')"><i class="material-icons">done</i></div> ';
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
    public function contrarecibos_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $contrarecibos = ContraRecibo::whereIn('ContraRecibo', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $contrarecibos = ContraRecibo::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($contrarecibos as $cr){
            $contrarecibodetalle = ContraReciboDetalle::where('ContraRecibo', $cr->ContraRecibo)->get();
            $datadetalle=array();
            foreach($contrarecibodetalle as $crd){
                $datadetalle[]=array(
                    "fechadetalle"=>$crd->Fecha,
                    "facturadetalle"=>$crd->Factura,
                    "remisiondetalle"=>$crd->Remision,
                    "totaldetalle" => Helpers::convertirvalorcorrecto($crd->Total),
                    "fechaapagardetalle"=> $crd->FechaAPagar,
                    "compradetalle" => $crd->Compra
                );
            } 
            $proveedor = Proveedor::where('Numero', $cr->Proveedor)->first();
            $data[]=array(
                      "contrarecibo"=>$cr,
                      "totalcontrarecibo"=>Helpers::convertirvalorcorrecto($cr->Total),
                      "contrarecibodetalle"=>$contrarecibodetalle,
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.contrarecibos.formato_pdf_contrarecibos', compact('data'))
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
    public function contrarecibos_generar_pdfs_indiv($documento){
        $contrarecibos = ContraRecibo::where('ContraRecibo', $documento)->orderBy('Folio', 'ASC')->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($contrarecibos as $cr){
            $contrarecibodetalle = ContraReciboDetalle::where('ContraRecibo', $cr->ContraRecibo)->get();
            $datadetalle=array();
            foreach($contrarecibodetalle as $crd){
                $datadetalle[]=array(
                    "fechadetalle"=>$crd->Fecha,
                    "facturadetalle"=>$crd->Factura,
                    "remisiondetalle"=>$crd->Remision,
                    "totaldetalle" => Helpers::convertirvalorcorrecto($crd->Total),
                    "fechaapagardetalle"=> $crd->FechaAPagar,
                    "compradetalle" => $crd->Compra
                );
            } 
            $proveedor = Proveedor::where('Numero', $cr->Proveedor)->first();
            $data[]=array(
                      "contrarecibo"=>$cr,
                      "totalcontrarecibo"=>Helpers::convertirvalorcorrecto($cr->Total),
                      "contrarecibodetalle"=>$contrarecibodetalle,
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.contrarecibos.formato_pdf_contrarecibos', compact('data'))
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
    public function contrarecibos_obtener_datos_envio_email(Request $request){
        $contrarecibo = ContraRecibo::where('ContraRecibo', $request->documento)->first();
        $proveedor = Proveedor::where('Numero',$contrarecibo->Proveedor)->first();
        $data = array(
            'contrarecibo' => $contrarecibo,
            'proveedor' => $proveedor,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $proveedor->Email1
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function contrarecibos_enviar_pdfs_email(Request $request){
        $contrarecibos = ContraRecibo::where('ContraRecibo', $request->emaildocumento)->orderBy('Folio', 'ASC')->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($contrarecibos as $cr){
            $contrarecibodetalle = ContraReciboDetalle::where('ContraRecibo', $cr->ContraRecibo)->get();
            $datadetalle=array();
            foreach($contrarecibodetalle as $crd){
                $datadetalle[]=array(
                    "fechadetalle"=>$crd->Fecha,
                    "facturadetalle"=>$crd->Factura,
                    "remisiondetalle"=>$crd->Remision,
                    "totaldetalle" => Helpers::convertirvalorcorrecto($crd->Total),
                    "fechaapagardetalle"=> $crd->FechaAPagar,
                    "compradetalle" => $crd->Compra
                );
            } 
            $proveedor = Proveedor::where('Numero', $cr->Proveedor)->first();
            $data[]=array(
                      "contrarecibo"=>$cr,
                      "totalcontrarecibo"=>Helpers::convertirvalorcorrecto($cr->Total),
                      "contrarecibodetalle"=>$contrarecibodetalle,
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.contrarecibos.formato_pdf_contrarecibos', compact('data'))
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
                        ->attachData($pdf->output(), "ContraReciboNo".$emaildocumento.".pdf");
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
    public function contrarecibos_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new ContraRecibosExport($this->campos_consulta,$request->periodo), "contrarecibos-".$request->periodo.".xlsx");      
    }
    //configuracion tabla
    public function contrarecibos_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'ContraRecibos')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('contrarecibos');
    }
}
