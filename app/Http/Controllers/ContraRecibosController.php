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
use App\Firma_Rel_Documento;
use Config;
use Mail;
use App\Serie;
use LynX39\LaraPdfMerger\Facades\PdfMerger;
use Storage; 
use ZipArchive;

class ContraRecibosController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function contrarecibos(){
        $serieusuario = 'A';
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('ContraRecibos', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('contrarecibos_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('contrarecibos_exportar_excel');
        $rutacreardocumento = route('contrarecibos_generar_pdfs');
        return view('registros.contrarecibos.contrarecibos', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }
    //obtener registro tabla
    public function contrarecibos_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('ContraRecibos', Auth::user()->id);
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            //$data = VistaContraRecibo::select($this->campos_consulta)->where('Periodo', $periodo)->orderBy('Fecha', 'DESC')->orderBy('Serie', 'ASC')->orderBy('Folio', 'DESC')->get();
            $data = VistaContraRecibo::select($configuraciones_tabla['campos_consulta'])->where('Periodo', $periodo);
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
                ->withQuery('sumatotal', function($data) {
                    return $data->sum('Total');
                })
                ->addColumn('operaciones', function($data){
                        $operaciones =  '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos(\''.$data->ContraRecibo .'\')">Cambios</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar(\''.$data->ContraRecibo .'\')">Bajas</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="'.route('contrarecibos_generar_pdfs_indiv',$data->ContraRecibo).'" target="_blank">Ver Documento PDF</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->ContraRecibo .'\')">Enviar Documento por Correo</a></li>'.
                                            '</ul>'.
                                        '</div>';
                    return $operaciones;
                })
                ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                ->addColumn('Total', function($data){ return $data->Total; })
                ->rawColumns(['operaciones','Total'])
                ->make(true);
        } 
    }
    //obtener series documento
    public function contrarecibos_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'ContraRecibos')->where('Usuario', Auth::user()->user)->get();
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
    public function contrarecibos_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\ContraRecibo',$request->Serie);
        return response()->json($folio);
    }
    //obtener ultimo folio
    public function contrarecibos_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\ContraRecibo',$request->serie);
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
                                '<label for="idcontrarecibocompra'.$contadorfilas.'" class="inputnext"></label>'.
                            '</td>'.
                        '</tr>';
                        $contadorfilas++;
                }
            }
        }       
        $fechahoy = Carbon::now()->toDateTimeString();
        $fechahoyespanol = Helpers::fecha_espanol(Carbon::now()->toDateTimeString());
        $data = array(
            "filascompras" => $filascompras,
            'fechahoy' => $fechahoy,
            'fechahoyespanol' => $fechahoyespanol
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
                                    '<label for="idcontrarecibocompra'.$contadorfilas.'" class="inputnext"></label>'.
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
        ini_set('max_input_vars','20000' );
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\ContraRecibo',$request->serie);
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
            //$checkbox = isset($request->contrarecibocompra [$key]) ? $request->contrarecibocompra [$key] : 0;
            //if($checkbox == 1){
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
            //}   
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
            "fecha" => Helpers::formatoinputdatetime($contrarecibo->Fecha),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($contrarecibo->Fecha),
            "total" => Helpers::convertirvalorcorrecto($contrarecibo->Total),
            "filasdetallescontrarecibo" => $filasdetallescontrarecibo,
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }
    //cambios
    public function contrarecibos_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
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
            $data = VistaContraRecibo::orderBy('Folio', 'ASC')->get();
            return DataTables::of($data)
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->make(true);
        } 
    }
    //generacion de formato en PDF
    public function contrarecibos_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        //primero eliminar todos los archivos zip
        Helpers::eliminararchivoszipgenerados();
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $contrarecibos = ContraRecibo::whereIn('ContraRecibo', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $contrarecibos = ContraRecibo::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $arrayfilespdf = array();
        foreach ($contrarecibos as $cr){
            $data=array();
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
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'ContraRecibos')->where('Documento', $cr->ContraRecibo)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'ContraRecibos')
            ->where('frd.Documento', $cr->ContraRecibo)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "contrarecibo"=>$cr,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "totalcontrarecibo"=>Helpers::convertirvalorcorrecto($cr->Total),
                      "contrarecibodetalle"=>$contrarecibodetalle,
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            //dd($data);
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.contrarecibos.formato_pdf_contrarecibos', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$cr->ContraRecibo.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF)); 
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($contrarecibos as $contra){
            $ArchivoPDF = "PDF".$contra->ContraRecibo.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
            array_push($arrayfilespdf,$ArchivoPDF);
        }
        $pdfMerger->merge(); //unirlos
        if($request->descargar_xml == 0){
            $pdfMerger->save("ContraRecibos.pdf", "browser");//mostrarlos en el navegador
        }else{
            //carpeta donde se guardara el archivo zip
            $public_dir=public_path();
            // Zip File Name
            $zipFileName = 'DocumentosPDF.zip';
            // Crear Objeto ZipArchive
            $zip = new ZipArchive;
            if ($zip->open($public_dir . '/xml_descargados/' . $zipFileName, ZipArchive::CREATE) === TRUE) {
                // Agregar archivos que se comprimiran
                foreach($arrayfilespdf as $afp) {
                    $zip->addFile(Storage::disk('local3')->getAdapter()->applyPathPrefix($afp),$afp);
                }     
                //terminar proceso   
                $zip->close();
            }
            // Set Encabezados para descargar
            $headers = array(
                'Content-Type' => 'application/octet-stream',
            );
            $filetopath=$public_dir.'/xml_descargados/'.$zipFileName;
            // Create Download Response
            if(file_exists($filetopath)){
                return response()->download($filetopath,$zipFileName,$headers);
            }
        }
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
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'ContraRecibos')->where('Documento', $cr->ContraRecibo)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'ContraRecibos')
            ->where('frd.Documento', $cr->ContraRecibo)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "contrarecibo"=>$cr,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
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
    public function contrarecibos_obtener_datos_envio_email(Request $request){
        $contrarecibo = ContraRecibo::where('ContraRecibo', $request->documento)->first();
        $proveedor = Proveedor::where('Numero',$contrarecibo->Proveedor)->first();
        $email2cc = '';
        $email3cc = '';
        if($proveedor->Email2 != '' || $proveedor->Email2 != null){
            $email2cc = $proveedor->Email2;
        }
        if($proveedor->Email3 != '' || $proveedor->Email3 != null){
            $email3cc = $proveedor->Email3;
        }
        $data = array(
            'contrarecibo' => $contrarecibo,
            'proveedor' => $proveedor,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $proveedor->Email1,
            'email2cc' => $email2cc,
            'email3cc' => $email3cc,
            'correodefault1enviodocumentos' => $this->correodefault1enviodocumentos,
            'correodefault2enviodocumentos' => $this->correodefault2enviodocumentos
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
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'ContraRecibos')->where('Documento', $cr->ContraRecibo)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'ContraRecibos')
            ->where('frd.Documento', $cr->ContraRecibo)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "contrarecibo"=>$cr,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
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
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            $datosdocumento = ContraRecibo::where('ContraRecibo', $request->emaildocumento)->first();
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
            if($request->correosconcopia != null){
                foreach($request->correosconcopia as $cc){
                    if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                        array_push($arraycc, $cc);
                    }
                }
            }
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailmensaje;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $correos, $asunto, $pdf, $emaildocumento) {
                $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                        ->cc($arraycc)
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
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('ContraRecibos', Auth::user()->id);
        return Excel::download(new ContraRecibosExport($configuraciones_tabla['campos_consulta'],$request->periodo), "contrarecibos-".$request->periodo.".xlsx");      
    }
    //configuracion tabla
    public function contrarecibos_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('ContraRecibos', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'ContraRecibos')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='ContraRecibos';
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
        return redirect()->route('contrarecibos');
    }
}
