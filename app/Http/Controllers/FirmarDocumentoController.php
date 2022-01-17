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
use App\Configuracion_Tabla;
use App\VistaFirmarDocumento;
use App\Documento;
use App\TituloFirma;
use App\Firma_Rel_Documento;
use Config;
use Mail;
use Schema;
use LynX39\LaraPdfMerger\Facades\PdfMerger;
use Storage; 

class FirmarDocumentoController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'FirmarDocumentos')->first();
        //consultas ordenadas
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

    public function firmardocumentos(){
        $serieusuario = 'A';
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('FirmarDocumentos', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('firmardocumentos_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('ordenes_compra_exportar_excel');
        $rutacreardocumento = route('ordenes_compra_generar_pdfs');
        $urlgenerarplantilla = route('ordenes_compra_generar_plantilla');
        return view('registros.firmardocumentos.firmardocumentos', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento','urlgenerarplantilla'));
    }
    //obtener todos los registros
    public function firmardocumentos_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('FirmarDocumentos', Auth::user()->id);
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaFirmarDocumento::select($configuraciones_tabla['campos_consulta'])->where('Periodo', $periodo)->where('IdUsuario', Auth::user()->id);
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
                    ->addColumn('operaciones', function($data) use ($tipousuariologueado){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->id .'\')">Bajas</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    } 
    //obtener tipos documentos
    public function firmardocumentos_obtener_tipos_documentos(){
        $tipos_documentos = Documento::where('status', 'ALTA')->get();
        $select_tipos_documentos = "<option selected disabled hidden>Selecciona...</option>";
        foreach($tipos_documentos as $tipo){
            $select_tipos_documentos = $select_tipos_documentos."<option value='".$tipo->documento."'>".$tipo->documento."</option>";
        }
        return response()->json($select_tipos_documentos);
    }
    //obtener folios documentos
    public function firmardocumentos_obtener_folios_documento(Request $request){
        if($request->ajax()){
            $tipo = $request->tipo;
            $arraydocumentosseleccionados = Array();
            foreach(explode(",", $request->stringdocumentosseleccionados) as $documento){
                array_push($arraydocumentosseleccionados, $documento);
            }
            $primerdiamesanterior = new Carbon('first day of last month');
            $ultimodiamesactual = new Carbon('last day of this month');
            switch($request->tipo){
                case "OrdenesDeCompra":
                    $data = DB::table('Ordenes de Compra as oc')
                    ->select("oc.Serie", "oc.Folio", "oc.Orden as Documento", "oc.Fecha")
                    ->leftjoin('firmas_rel_documentos as frd', 'oc.Orden', '=', 'frd.Documento')
                    ->whereDate('oc.Fecha', '>=', $primerdiamesanterior->toDateString())->whereDate('oc.Fecha', '<=', $ultimodiamesactual->toDateString())
                    ->where('oc.AutorizadoPor', '<>', '')
                    ->orderBy('oc.Folio', 'DESC')
                    ->get();
                    break;
                case "Compras":
                    $data = DB::table('Compras as c')
                    ->select("c.Serie", "c.Folio", "c.Compra as Documento", "c.Fecha")
                    ->leftjoin('firmas_rel_documentos as frd', 'c.Compra', '=', 'frd.Documento')
                    ->whereDate('c.Fecha', '>=', $primerdiamesanterior->toDateString())->whereDate('c.Fecha', '<=', $ultimodiamesactual->toDateString())
                    ->where('c.Status', '<>', 'BAJA')
                    ->orderBy('c.Folio', 'DESC')
                    ->get();
                    break;
                case "ContraRecibos":
                    $data = DB::table('ContraRecibos as c')
                    ->select("c.Serie", "c.Folio", "c.ContraRecibo as Documento", "c.Fecha")
                    ->leftjoin('firmas_rel_documentos as frd', 'c.ContraRecibo', '=', 'frd.Documento')
                    ->whereDate('c.Fecha', '>=', $primerdiamesanterior->toDateString())->whereDate('c.Fecha', '<=', $ultimodiamesactual->toDateString())
                    ->where('c.Status', '<>', 'BAJA')
                    ->orderBy('c.Folio', 'DESC')
                    ->get();
                    break;
                case "Remisiones":
                    $data = DB::table('Remisiones as r')
                    ->select("r.Serie", "r.Folio", "r.Remision as Documento", "r.Fecha")
                    ->leftjoin('firmas_rel_documentos as frd', 'r.Remision', '=', 'frd.Documento')
                    ->whereDate('r.Fecha', '>=', $primerdiamesanterior->toDateString())->whereDate('r.Fecha', '<=', $ultimodiamesactual->toDateString())
                    ->where('r.Status', '<>', 'BAJA')
                    ->orderBy('r.Folio', 'DESC')
                    ->get();
                    break;
                case "cotizaciones_t":
                    break;
                case "Traspasos":
                    $data = DB::table('Traspasos as t')
                    ->select("t.Serie", "t.Folio", "t.Traspaso as Documento", "t.Fecha")
                    ->leftjoin('firmas_rel_documentos as frd', 't.Traspaso', '=', 'frd.Documento')
                    ->whereDate('t.Fecha', '>=', $primerdiamesanterior->toDateString())->whereDate('t.Fecha', '<=', $ultimodiamesactual->toDateString())
                    ->where('t.Status', '<>', 'BAJA')
                    ->orderBy('t.Folio', 'DESC')
                    ->get();
                    break;
                case "Ordenes de Trabajo":
                    break;
                case "CuentasPorPagar":
                    break;
                case "NotasCreditoProveedor":
                    $data = DB::table('Notas Proveedor as np')
                    ->select("np.Serie", "np.Folio", "np.Nota as Documento", "np.Fecha")
                    ->leftjoin('firmas_rel_documentos as frd', 'np.Nota', '=', 'frd.Documento')
                    ->whereDate('np.Fecha', '>=', $primerdiamesanterior->toDateString())->whereDate('np.Fecha', '<=', $ultimodiamesactual->toDateString())
                    ->where('np.Status', '<>', 'BAJA')
                    ->orderBy('np.Folio', 'DESC')
                    ->get();
                    break;
                case "asignacion_herramientas":
                    $data = DB::table('asignacion_herramientas as ah')
                    ->select("ah.serie as Serie", "ah.id as Folio", "ah.asignacion as Documento", "ah.fecha as Fecha")
                    ->leftjoin('firmas_rel_documentos as frd', 'ah.asignacion', '=', 'frd.Documento')
                    ->whereDate('ah.fecha', '>=', $primerdiamesanterior->toDateString())->whereDate('ah.fecha', '<=', $ultimodiamesactual->toDateString())
                    ->where('ah.status', '<>', 'BAJA')
                    ->orderBy('ah.id', 'DESC')
                    ->get();
                    break;
                case "prestamo_herramientas":
                    break;
                case "AjustesInventario":
                    $data = DB::table('Ajustes de Inventario as aji')
                    ->select("aji.Serie", "aji.Folio", "aji.Ajuste as Documento", "aji.Fecha")
                    ->leftjoin('firmas_rel_documentos as frd', 'aji.Ajuste', '=', 'frd.Documento')
                    ->whereDate('aji.Fecha', '>=', $primerdiamesanterior->toDateString())->whereDate('aji.Fecha', '<=', $ultimodiamesactual->toDateString())
                    ->where('aji.Status', '<>', 'BAJA')
                    ->orderBy('aji.Folio', 'DESC')
                    ->get();
                    break;
                case "CotizacionesProductos":
                    $data = DB::table('Cotizaciones as c')
                    ->select("c.Serie", "c.Folio", "c.Cotizacion as Documento", "c.Fecha")
                    ->leftjoin('firmas_rel_documentos as frd', 'c.Cotizacion', '=', 'frd.Documento')
                    ->whereDate('c.Fecha', '>=', $primerdiamesanterior->toDateString())->whereDate('c.Fecha', '<=', $ultimodiamesactual->toDateString())
                    ->where('c.Status', '<>', 'BAJA')
                    ->orderBy('c.Folio', 'DESC')
                    ->get();
                    break;
                case "CotizacionesServicios":
                    $data = DB::table('Cotizaciones Servicio as c')
                    ->select("c.Serie", "c.Folio", "c.Cotizacion as Documento", "c.Fecha")
                    ->leftjoin('firmas_rel_documentos as frd', 'c.Cotizacion', '=', 'frd.Documento')
                    ->whereDate('c.Fecha', '>=', $primerdiamesanterior->toDateString())->whereDate('c.Fecha', '<=', $ultimodiamesactual->toDateString())
                    ->where('c.Status', '<>', 'BAJA')
                    ->orderBy('c.Folio', 'DESC')
                    ->get();
                    break;
                case "Produccion":
                    $data = DB::table('Produccion as p')
                    ->select("p.Serie", "p.Folio", "p.Produccion as Documento", "p.Fecha")
                    ->leftjoin('firmas_rel_documentos as frd', 'p.Produccion', '=', 'frd.Documento')
                    ->whereDate('p.Fecha', '>=', $primerdiamesanterior->toDateString())->whereDate('p.Fecha', '<=', $ultimodiamesactual->toDateString())
                    ->where('p.Status', '<>', 'BAJA')
                    ->orderBy('p.Folio', 'DESC')
                    ->get();
                    break;
                case "Requisiciones":
                    $data = DB::table('Requisiciones as r')
                    ->select("r.Serie", "r.Folio", "r.Requisicion as Documento", "r.Fecha")
                    ->leftjoin('firmas_rel_documentos as frd', 'r.Requisicion', '=', 'frd.Documento')
                    ->whereDate('r.Fecha', '>=', $primerdiamesanterior->toDateString())->whereDate('r.Fecha', '<=', $ultimodiamesactual->toDateString())
                    ->where('r.Status', '<>', 'BAJA')
                    ->orderBy('r.Folio', 'DESC')
                    ->get();
                    break;
            }
            return DataTables::of($data)
                    ->addColumn('firmar', function($data) use ($arraydocumentosseleccionados){
                        if(in_array($data->Documento, $arraydocumentosseleccionados) == true){
                            $checkbox = '<input type="checkbox" name="documentosseleccionados[]" id="iddocumentosseleccionados'.$data->Documento.'" class="documentosseleccionados filled-in" value="'.$data->Documento.'" onchange="seleccionardocumento(\''.$data->Documento.'\');" required checked>'.
                            '<label for="iddocumentosseleccionados'.$data->Documento.'" ></label>';
                        }else{
                            $checkbox = '<input type="checkbox" name="documentosseleccionados[]" id="iddocumentosseleccionados'.$data->Documento.'" class="documentosseleccionados filled-in" value="'.$data->Documento.'" onchange="seleccionardocumento(\''.$data->Documento.'\');" required>'.
                            '<label for="iddocumentosseleccionados'.$data->Documento.'" ></label>';
                        }
                        return $checkbox;
                    })
                    
                    ->addColumn('TipoDocumento', function($data) use($tipo){
                        return $tipo;
                    })
                    ->rawColumns(['firmar'])
                    ->make(true);
        }
    }

    //cargar documentos a firmar
    public function firmardocumentos_obtener_documentos_a_firmar(Request $request){
        //detalles
        $filasfirmas = '';
        $contadorfilas = $request->contadorfilas;
        $partida = $request->partida;
        $titulosfirmas = TituloFirma::all();
        $select_titulos_firmas = "";
        foreach($titulosfirmas as $tf){
            $select_titulos_firmas = $select_titulos_firmas."<option value='".$tf->Titulo."'>".$tf->Titulo."</option>";
        }
        foreach(explode(",", $request->stringdocumentosseleccionados) as $d){
            switch($request->tipo){
                case "OrdenesDeCompra":
                    $datos = DB::table('Ordenes de Compra as oc')
                    ->select("oc.Serie", "oc.Folio", "oc.Orden as Documento", "oc.Fecha")
                    ->where('oc.Orden', $d)
                    ->first();
                    break;
                case "Compras":
                    $datos = DB::table('Compras as c')
                    ->select("c.Serie", "c.Folio", "c.Compra as Documento", "c.Fecha")
                    ->where('c.Compra', $d)
                    ->first();
                    break;
                case "ContraRecibos":
                    $datos = DB::table('ContraRecibos as c')
                    ->select("c.Serie", "c.Folio", "c.ContraRecibo as Documento", "c.Fecha")
                    ->where('c.ContraRecibo', $d)
                    ->first();
                    break;
                case "Remisiones":
                    $datos = DB::table('Remisiones as r')
                    ->select("r.Serie", "r.Folio", "r.Remision as Documento", "r.Fecha")
                    ->where('r.Remision', $d)
                    ->first();
                    break;
                case "cotizaciones_t":
                    break;
                case "Traspasos":
                    $datos = DB::table('Traspasos as t')
                    ->select("t.Serie", "t.Folio", "t.Traspaso as Documento", "t.Fecha")
                    ->where('t.Traspaso', $d)
                    ->first();
                    break;
                case "Ordenes de Trabajo":
                    break;
                case "CuentasPorPagar":
                    break;
                case "NotasCreditoProveedor":
                    $datos = DB::table('Notas Proveedor as np')
                    ->select("np.Serie", "np.Folio", "np.Nota as Documento", "np.Fecha")
                    ->where('np.Nota', $d)
                    ->first();
                    break;
                case "asignacion_herramientas":
                    $datos = DB::table('asignacion_herramientas as ah')
                    ->select("ah.serie", "ah.id", "ah.asignacion as Documento", "ah.fecha")
                    ->where('ah.asignacion', $d)
                    ->first();
                    break;
                case "prestamo_herramientas":
                    break;
                case "AjustesInventario":
                    $datos = DB::table('Ajustes de Inventario as aji')
                    ->select("aji.Serie", "aji.Folio", "aji.Ajuste as Documento", "aji.Fecha")
                    ->where('aji.Ajuste', $d)
                    ->first();
                    break;
                case "CotizacionesProductos":
                    $datos = DB::table('Cotizaciones as c')
                    ->select("c.Serie", "c.Folio", "c.Cotizacion as Documento", "c.Fecha")
                    ->where('c.Cotizacion', $d)
                    ->first();
                    break;
                case "CotizacionesServicios":
                    $datos = DB::table('Cotizaciones Servicio as c')
                    ->select("c.Serie", "c.Folio", "c.Cotizacion as Documento", "c.Fecha")
                    ->where('c.Cotizacion', $d)
                    ->first();
                    break;
                case "Produccion":
                    $datos = DB::table('Produccion as p')
                    ->select("p.Serie", "p.Folio", "p.Produccion as Documento", "p.Fecha")
                    ->where('p.Produccion', $d)
                    ->first();
                    break;
                case "Requisiciones":
                    $datos = DB::table('Requisiciones as r')
                    ->select("r.Serie", "r.Folio", "r.Requisicion as Documento", "r.Fecha")
                    ->where('r.Requisicion', $d)
                    ->first();
                    break;
            }
            $filasfirmas= $filasfirmas.
            '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                '<td class="tdmod"><div class="numeropartida">'.$partida.'</div></td>'.
                '<td class="tdmod"><input type="hidden" class="form-control tipodocumentopartida" name="tipodocumentopartida[]" value="'.$request->tipo.'" readonly required data-parsley-length="[1, 255]">'.$request->tipo.'</td>'.
                '<td class="tdmod"><input type="hidden" class="form-control documentopartida" name="documentopartida[]" value="'.$datos->Documento.'" readonly required data-parsley-length="[1, 255]">'.$datos->Documento.'</td>'.
                '<td class="tdmod"><input type="hidden" class="form-control idusuariopartida" name="idusuariopartida[]" value="'.Auth::user()->id.'" readonly required data-parsley-length="[1, 255]"><input type="text" class="form-control divorinputmodxl usuariopartida" name="usuariopartida[]" value="'.Auth::user()->user.'" required readonly data-parsley-length="[1, 255]"></td>'.
                '<td class="tdmod"><select class="form-control titulofirmapartida select2" name="titulofirmapartida[]"  style="width:100%;">'.$select_titulos_firmas.'</select></td>'.    
            '</tr>';
            $contadorfilas++;
            $partida++; 
        }
        $data = array(
            "filasfirmas" => $filasfirmas,
            "contadorfilas" => $contadorfilas,
            "partida" => $partida
        );
        return response()->json($data); 
    }

    //firmas altas en irdenes compras
    public function firmardocumentosoc_guardar(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $existenfirmas = 0; 
        foreach ($request->tipodocumentopartida as $key => $tipodocumentopartida){  
            $existefirmaendocumento = Firma_Rel_Documento::where('TipoDocumento',$tipodocumentopartida)
                                                            ->where('Documento',$request->documentopartida [$key])
                                                            ->where('ReferenciaPosicion',$request->titulofirmapartida [$key])
                                                            ->where('IdUsuario',Auth::user()->id)
                                                            ->where('Status', 'ALTA')
                                                            ->count();
            if($existefirmaendocumento == 0){
                $Firma_Rel_Documento=new Firma_Rel_Documento;
                $Firma_Rel_Documento->TipoDocumento = $tipodocumentopartida;
                $Firma_Rel_Documento->Documento = $request->documentopartida [$key];
                $Firma_Rel_Documento->IdUsuario = Auth::user()->id;
                $Firma_Rel_Documento->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Firma_Rel_Documento->ReferenciaPosicion = $request->titulofirmapartida [$key];
                $Firma_Rel_Documento->Status = "ALTA";
                $Firma_Rel_Documento->Usuario = Auth::user()->user;
                $Firma_Rel_Documento->Periodo =  $this->periodohoy;
                $Firma_Rel_Documento->save();
                $item++;
            }else{
                $existenfirmas++;
            }
        }
    	return response()->json($existenfirmas); 
    }
    //firmas altas en compras
    public function firmardocumentoscom_guardar(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $existenfirmas = 0; 
        foreach ($request->tipodocumentopartida as $key => $tipodocumentopartida){  
            $existefirmaendocumento = Firma_Rel_Documento::where('TipoDocumento',$tipodocumentopartida)
                                                            ->where('Documento',$request->documentopartida [$key])
                                                            ->where('ReferenciaPosicion',$request->titulofirmapartida [$key])
                                                            ->where('IdUsuario',Auth::user()->id)
                                                            ->where('Status', 'ALTA')
                                                            ->count();
            if($existefirmaendocumento == 0){
                $Firma_Rel_Documento=new Firma_Rel_Documento;
                $Firma_Rel_Documento->TipoDocumento = $tipodocumentopartida;
                $Firma_Rel_Documento->Documento = $request->documentopartida [$key];
                $Firma_Rel_Documento->IdUsuario = Auth::user()->id;
                $Firma_Rel_Documento->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Firma_Rel_Documento->ReferenciaPosicion = $request->titulofirmapartida [$key];
                $Firma_Rel_Documento->Status = "ALTA";
                $Firma_Rel_Documento->Usuario = Auth::user()->user;
                $Firma_Rel_Documento->Periodo =  $this->periodohoy;
                $Firma_Rel_Documento->save();
                $item++;
            }else{
                $existenfirmas++;
            }
        }
    	return response()->json($existenfirmas); 
    }
    //firmas altas en contrarecibos
    public function firmardocumentosconrec_guardar(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $existenfirmas = 0; 
        foreach ($request->tipodocumentopartida as $key => $tipodocumentopartida){  
            $existefirmaendocumento = Firma_Rel_Documento::where('TipoDocumento',$tipodocumentopartida)
                                                            ->where('Documento',$request->documentopartida [$key])
                                                            ->where('ReferenciaPosicion',$request->titulofirmapartida [$key])
                                                            ->where('IdUsuario',Auth::user()->id)
                                                            ->where('Status', 'ALTA')
                                                            ->count();
            if($existefirmaendocumento == 0){
                $Firma_Rel_Documento=new Firma_Rel_Documento;
                $Firma_Rel_Documento->TipoDocumento = $tipodocumentopartida;
                $Firma_Rel_Documento->Documento = $request->documentopartida [$key];
                $Firma_Rel_Documento->IdUsuario = Auth::user()->id;
                $Firma_Rel_Documento->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Firma_Rel_Documento->ReferenciaPosicion = $request->titulofirmapartida [$key];
                $Firma_Rel_Documento->Status = "ALTA";
                $Firma_Rel_Documento->Usuario = Auth::user()->user;
                $Firma_Rel_Documento->Periodo =  $this->periodohoy;
                $Firma_Rel_Documento->save();
                $item++;
            }else{
                $existenfirmas++;
            }
        }
    	return response()->json($existenfirmas); 
    }
    //firmas altas en remisiones
    public function firmardocumentosrem_guardar(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $existenfirmas = 0; 
        foreach ($request->tipodocumentopartida as $key => $tipodocumentopartida){  
            $existefirmaendocumento = Firma_Rel_Documento::where('TipoDocumento',$tipodocumentopartida)
                                                            ->where('Documento',$request->documentopartida [$key])
                                                            ->where('ReferenciaPosicion',$request->titulofirmapartida [$key])
                                                            ->where('IdUsuario',Auth::user()->id)
                                                            ->where('Status', 'ALTA')
                                                            ->count();
            if($existefirmaendocumento == 0){
                $Firma_Rel_Documento=new Firma_Rel_Documento;
                $Firma_Rel_Documento->TipoDocumento = $tipodocumentopartida;
                $Firma_Rel_Documento->Documento = $request->documentopartida [$key];
                $Firma_Rel_Documento->IdUsuario = Auth::user()->id;
                $Firma_Rel_Documento->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Firma_Rel_Documento->ReferenciaPosicion = $request->titulofirmapartida [$key];
                $Firma_Rel_Documento->Status = "ALTA";
                $Firma_Rel_Documento->Usuario = Auth::user()->user;
                $Firma_Rel_Documento->Periodo =  $this->periodohoy;
                $Firma_Rel_Documento->save();
                $item++;
            }else{
                $existenfirmas++;
            }
        }
    	return response()->json($existenfirmas); 
    }
    //firmas altas en traspasos
    public function firmardocumentostras_guardar(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $existenfirmas = 0; 
        foreach ($request->tipodocumentopartida as $key => $tipodocumentopartida){  
            $existefirmaendocumento = Firma_Rel_Documento::where('TipoDocumento',$tipodocumentopartida)
                                                            ->where('Documento',$request->documentopartida [$key])
                                                            ->where('ReferenciaPosicion',$request->titulofirmapartida [$key])
                                                            ->where('IdUsuario',Auth::user()->id)
                                                            ->where('Status', 'ALTA')
                                                            ->count();
            if($existefirmaendocumento == 0){
                $Firma_Rel_Documento=new Firma_Rel_Documento;
                $Firma_Rel_Documento->TipoDocumento = $tipodocumentopartida;
                $Firma_Rel_Documento->Documento = $request->documentopartida [$key];
                $Firma_Rel_Documento->IdUsuario = Auth::user()->id;
                $Firma_Rel_Documento->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Firma_Rel_Documento->ReferenciaPosicion = $request->titulofirmapartida [$key];
                $Firma_Rel_Documento->Status = "ALTA";
                $Firma_Rel_Documento->Usuario = Auth::user()->user;
                $Firma_Rel_Documento->Periodo =  $this->periodohoy;
                $Firma_Rel_Documento->save();
                $item++;
            }else{
                $existenfirmas++;
            }
        }
    	return response()->json($existenfirmas); 
    }
    //firmas altas en notas proveedor
    public function firmardocumentosnp_guardar(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $existenfirmas = 0; 
        foreach ($request->tipodocumentopartida as $key => $tipodocumentopartida){  
            $existefirmaendocumento = Firma_Rel_Documento::where('TipoDocumento',$tipodocumentopartida)
                                                            ->where('Documento',$request->documentopartida [$key])
                                                            ->where('ReferenciaPosicion',$request->titulofirmapartida [$key])
                                                            ->where('IdUsuario',Auth::user()->id)
                                                            ->where('Status', 'ALTA')
                                                            ->count();
            if($existefirmaendocumento == 0){
                $Firma_Rel_Documento=new Firma_Rel_Documento;
                $Firma_Rel_Documento->TipoDocumento = $tipodocumentopartida;
                $Firma_Rel_Documento->Documento = $request->documentopartida [$key];
                $Firma_Rel_Documento->IdUsuario = Auth::user()->id;
                $Firma_Rel_Documento->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Firma_Rel_Documento->ReferenciaPosicion = $request->titulofirmapartida [$key];
                $Firma_Rel_Documento->Status = "ALTA";
                $Firma_Rel_Documento->Usuario = Auth::user()->user;
                $Firma_Rel_Documento->Periodo =  $this->periodohoy;
                $Firma_Rel_Documento->save();
                $item++;
            }else{
                $existenfirmas++;
            }
        }
    	return response()->json($existenfirmas); 
    }
    //firmas altas en asignacion herramienta
    public function firmardocumentosah_guardar(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $existenfirmas = 0; 
        foreach ($request->tipodocumentopartida as $key => $tipodocumentopartida){  
            $existefirmaendocumento = Firma_Rel_Documento::where('TipoDocumento',$tipodocumentopartida)
                                                            ->where('Documento',$request->documentopartida [$key])
                                                            ->where('ReferenciaPosicion',$request->titulofirmapartida [$key])
                                                            ->where('IdUsuario',Auth::user()->id)
                                                            ->where('Status', 'ALTA')
                                                            ->count();
            if($existefirmaendocumento == 0){
                $Firma_Rel_Documento=new Firma_Rel_Documento;
                $Firma_Rel_Documento->TipoDocumento = $tipodocumentopartida;
                $Firma_Rel_Documento->Documento = $request->documentopartida [$key];
                $Firma_Rel_Documento->IdUsuario = Auth::user()->id;
                $Firma_Rel_Documento->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Firma_Rel_Documento->ReferenciaPosicion = $request->titulofirmapartida [$key];
                $Firma_Rel_Documento->Status = "ALTA";
                $Firma_Rel_Documento->Usuario = Auth::user()->user;
                $Firma_Rel_Documento->Periodo =  $this->periodohoy;
                $Firma_Rel_Documento->save();
                $item++;
            }else{
                $existenfirmas++;
            }
        }
    	return response()->json($existenfirmas); 
    }
    //firmas altas en ajustes de inventario
    public function firmardocumentosaji_guardar(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $existenfirmas = 0; 
        foreach ($request->tipodocumentopartida as $key => $tipodocumentopartida){  
            $existefirmaendocumento = Firma_Rel_Documento::where('TipoDocumento',$tipodocumentopartida)
                                                            ->where('Documento',$request->documentopartida [$key])
                                                            ->where('ReferenciaPosicion',$request->titulofirmapartida [$key])
                                                            ->where('IdUsuario',Auth::user()->id)
                                                            ->where('Status', 'ALTA')
                                                            ->count();
            if($existefirmaendocumento == 0){
                $Firma_Rel_Documento=new Firma_Rel_Documento;
                $Firma_Rel_Documento->TipoDocumento = $tipodocumentopartida;
                $Firma_Rel_Documento->Documento = $request->documentopartida [$key];
                $Firma_Rel_Documento->IdUsuario = Auth::user()->id;
                $Firma_Rel_Documento->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Firma_Rel_Documento->ReferenciaPosicion = $request->titulofirmapartida [$key];
                $Firma_Rel_Documento->Status = "ALTA";
                $Firma_Rel_Documento->Usuario = Auth::user()->user;
                $Firma_Rel_Documento->Periodo =  $this->periodohoy;
                $Firma_Rel_Documento->save();
                $item++;
            }else{
                $existenfirmas++;
            }
        }
    	return response()->json($existenfirmas); 
    }

    //firmas altas en cotizacione productos
    public function firmardocumentoscp_guardar(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $existenfirmas = 0; 
        foreach ($request->tipodocumentopartida as $key => $tipodocumentopartida){  
            $existefirmaendocumento = Firma_Rel_Documento::where('TipoDocumento',$tipodocumentopartida)
                                                            ->where('Documento',$request->documentopartida [$key])
                                                            ->where('ReferenciaPosicion',$request->titulofirmapartida [$key])
                                                            ->where('IdUsuario',Auth::user()->id)
                                                            ->where('Status', 'ALTA')
                                                            ->count();
            if($existefirmaendocumento == 0){
                $Firma_Rel_Documento=new Firma_Rel_Documento;
                $Firma_Rel_Documento->TipoDocumento = $tipodocumentopartida;
                $Firma_Rel_Documento->Documento = $request->documentopartida [$key];
                $Firma_Rel_Documento->IdUsuario = Auth::user()->id;
                $Firma_Rel_Documento->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Firma_Rel_Documento->ReferenciaPosicion = $request->titulofirmapartida [$key];
                $Firma_Rel_Documento->Status = "ALTA";
                $Firma_Rel_Documento->Usuario = Auth::user()->user;
                $Firma_Rel_Documento->Periodo =  $this->periodohoy;
                $Firma_Rel_Documento->save();
                $item++;
            }else{
                $existenfirmas++;
            }
        }
    	return response()->json($existenfirmas); 
    }
    //firmas altas en cotizacione servicios
    public function firmardocumentoscs_guardar(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $existenfirmas = 0; 
        foreach ($request->tipodocumentopartida as $key => $tipodocumentopartida){  
            $existefirmaendocumento = Firma_Rel_Documento::where('TipoDocumento',$tipodocumentopartida)
                                                            ->where('Documento',$request->documentopartida [$key])
                                                            ->where('ReferenciaPosicion',$request->titulofirmapartida [$key])
                                                            ->where('IdUsuario',Auth::user()->id)
                                                            ->where('Status', 'ALTA')
                                                            ->count();
            if($existefirmaendocumento == 0){
                $Firma_Rel_Documento=new Firma_Rel_Documento;
                $Firma_Rel_Documento->TipoDocumento = $tipodocumentopartida;
                $Firma_Rel_Documento->Documento = $request->documentopartida [$key];
                $Firma_Rel_Documento->IdUsuario = Auth::user()->id;
                $Firma_Rel_Documento->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Firma_Rel_Documento->ReferenciaPosicion = $request->titulofirmapartida [$key];
                $Firma_Rel_Documento->Status = "ALTA";
                $Firma_Rel_Documento->Usuario = Auth::user()->user;
                $Firma_Rel_Documento->Periodo =  $this->periodohoy;
                $Firma_Rel_Documento->save();
                $item++;
            }else{
                $existenfirmas++;
            }
        }
    	return response()->json($existenfirmas); 
    }

    //firmas altas en produccion
    public function firmardocumentospro_guardar(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $existenfirmas = 0; 
        foreach ($request->tipodocumentopartida as $key => $tipodocumentopartida){  
            $existefirmaendocumento = Firma_Rel_Documento::where('TipoDocumento',$tipodocumentopartida)
                                                            ->where('Documento',$request->documentopartida [$key])
                                                            ->where('ReferenciaPosicion',$request->titulofirmapartida [$key])
                                                            ->where('IdUsuario',Auth::user()->id)
                                                            ->where('Status', 'ALTA')
                                                            ->count();
            if($existefirmaendocumento == 0){
                $Firma_Rel_Documento=new Firma_Rel_Documento;
                $Firma_Rel_Documento->TipoDocumento = $tipodocumentopartida;
                $Firma_Rel_Documento->Documento = $request->documentopartida [$key];
                $Firma_Rel_Documento->IdUsuario = Auth::user()->id;
                $Firma_Rel_Documento->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Firma_Rel_Documento->ReferenciaPosicion = $request->titulofirmapartida [$key];
                $Firma_Rel_Documento->Status = "ALTA";
                $Firma_Rel_Documento->Usuario = Auth::user()->user;
                $Firma_Rel_Documento->Periodo =  $this->periodohoy;
                $Firma_Rel_Documento->save();
                $item++;
            }else{
                $existenfirmas++;
            }
        }
    	return response()->json($existenfirmas); 
    }

    //firmas altas en requisiciones
    public function firmardocumentosreq_guardar(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $existenfirmas = 0; 
        foreach ($request->tipodocumentopartida as $key => $tipodocumentopartida){  
            $existefirmaendocumento = Firma_Rel_Documento::where('TipoDocumento',$tipodocumentopartida)
                                                            ->where('Documento',$request->documentopartida [$key])
                                                            ->where('ReferenciaPosicion',$request->titulofirmapartida [$key])
                                                            ->where('IdUsuario',Auth::user()->id)
                                                            ->where('Status', 'ALTA')
                                                            ->count();
            if($existefirmaendocumento == 0){
                $Firma_Rel_Documento=new Firma_Rel_Documento;
                $Firma_Rel_Documento->TipoDocumento = $tipodocumentopartida;
                $Firma_Rel_Documento->Documento = $request->documentopartida [$key];
                $Firma_Rel_Documento->IdUsuario = Auth::user()->id;
                $Firma_Rel_Documento->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $Firma_Rel_Documento->ReferenciaPosicion = $request->titulofirmapartida [$key];
                $Firma_Rel_Documento->Status = "ALTA";
                $Firma_Rel_Documento->Usuario = Auth::user()->user;
                $Firma_Rel_Documento->Periodo =  $this->periodohoy;
                $Firma_Rel_Documento->save();
                $item++;
            }else{
                $existenfirmas++;
            }
        }
    	return response()->json($existenfirmas); 
    }

    //verificar uso en modulo
    public function firmardocumentos_verificar_uso_en_modulos(Request $request){
        $Firma_Rel_Documento= Firma_Rel_Documento::where('id', $request->firmadesactivar)->first();
        return response()->json($Firma_Rel_Documento);
    }

    //bajas
    public function firmardocumentos_bajas(Request $request){
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        Firma_Rel_Documento::where('id', $request->firmadesactivar)
        ->update([
            'MotivoBaja' => $MotivoBaja,
            'Status' => 'BAJA',
        ]);
    }

    //configurar tabla
    public function firmardocumentos_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('FirmarDocumentos', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'FirmarDocumentos')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='FirmarDocumentos';
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
        return redirect()->route('firmardocumentos');
    }
}
