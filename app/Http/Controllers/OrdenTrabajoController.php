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
use App\Exports\OrdenesDeTrabajoExport;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\TipoOrdenTrabajo;
use App\TipoUnidad;
use App\Cliente;
use App\Agente;
use App\Tecnico;
use App\Vine;
use App\Servicio;
use App\Configuracion_Tabla;
use App\VistaOrdenTrabajo;
use App\BitacoraDocumento;

class OrdenTrabajoController extends ConfiguracionSistemaController
{
    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'OrdenesDeTrabajo')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function ordenes_trabajo(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->user, 'Ordenes de Trabajo');
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('ordenes_trabajo_guardar_configuracion_tabla');
        $rutacreardocumento = route('ordenes_trabajo_generar_pdfs');
        $urlgenerarformatoexcel = route('ordenes_trabajo_exportar_excel');
        return view('registros.ordenestrabajo.ordenestrabajo', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','rutacreardocumento','urlgenerarformatoexcel'));
    }
    //obtener todos los registros del modulo
    public function ordenes_trabajo_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaOrdenTrabajo::select($this->campos_consulta)->orderBy('Folio', 'DESC')->where('Periodo', $periodo)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $botoncambios   =   '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Orden .'\')"><i class="material-icons">mode_edit</i></div> '; 
                    $botonbajas     =   '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Orden .'\')"><i class="material-icons">cancel</i></div>  ';
                    $botonterminar  =   '<div class="btn bg-green btn-xs waves-effect" data-toggle="tooltip" title="Terminar" onclick="terminar(\''.$data->Orden .'\')"><i class="material-icons">playlist_add_check</i></div> ';
                    $operaciones = $botoncambios.$botonbajas.$botonterminar;
                    return $operaciones;
                })
                ->addColumn('Total', function($data){ return $data->Total; })
                ->addColumn('Kilometros', function($data){ return $data->Kilometros; })
                ->addColumn('Impuesto', function($data){ return $data->Impuesto; })
                ->addColumn('Importe', function($data){ return $data->Importe; })
                ->addColumn('Descuento', function($data){ return $data->Descuento; })
                ->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                ->addColumn('Iva', function($data){ return $data->Iva; })
                ->addColumn('Facturado', function($data){ return $data->Facturado; })
                ->addColumn('Costo', function($data){ return $data->Costo; })
                ->addColumn('Comision', function($data){ return $data->Comision; })
                ->addColumn('Utilidad', function($data){ return $data->Utilidad; })
                ->addColumn('HorasReales', function($data){ return $data->HorasReales; })
                ->addColumn('KmProximoServicio', function($data){ return $data->KmProximoServicio; })
                ->rawColumns(['operaciones'])
                ->make(true);
        } 
    }
    //obtener ultimo folio de la orden de trabajo
    public function ordenes_trabajo_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\OrdenTrabajo', $request->serie);
        return response()->json($folio);
    }
    //obtener tipos de ordenes de trbaajo
    public function ordenes_trabajo_obtener_tipos_ordenes_trabajo(){
        $tipos_ordenes_trabajo = TipoOrdenTrabajo::where('STATUS', 'ALTA')->get();
        $select_tipos_ordenes_trabajo = "<option disabled hidden>Selecciona...</option>";
        foreach($tipos_ordenes_trabajo as $tipo){
            $select_tipos_ordenes_trabajo = $select_tipos_ordenes_trabajo."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_trabajo);
    }
    //obtener tipos de unidades
    public function ordenes_trabajo_obtener_tipos_unidades(){
        $tipos_unidades= TipoUnidad::where('STATUS', 'ALTA')->get();
        $select_tipos_unidades = "<option disabled hidden>Selecciona...</option>";
        foreach($tipos_unidades as $tipo){
            $select_tipos_unidades = $select_tipos_unidades."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_unidades);
    }
    //obtener fecha actual datetimelocal
    public function ordenes_trabajo_obtener_fecha_actual_datetimelocal(){
        $fechadatetimelocal = Helpers::fecha_exacta_accion_datetimelocal();
        return response()->json($fechadatetimelocal);
    }
    //obtener clientes factura a
    public function ordenes_trabajo_obtener_clientes_facturaa(Request $request){
        if($request->ajax()){
            $data = Cliente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $contaragente = Agente::where('Numero', $data->Agente)->count();
                        $nombreagente = '';
                        $numeroagente = '';
                        if($contaragente > 0){
                            $agente = Agente::where('Numero', $data->Agente)->first();
                            $nombreagente = $agente->Nombre;
                            $numeroagente = $agente->Numero;
                        }
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclientefacturaa('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$numeroagente.'\',\''.$nombreagente.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener clientes para campo Del Cliente
    public function ordenes_trabajo_obtener_clientes_delcliente(Request $request){
        if($request->ajax()){
            $data = Cliente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclientedelcliente('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }  
    }
    //obtener agentes
    public function ordenes_trabajo_obtener_agentes(Request $request){
        if($request->ajax()){
            $data = Agente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaragente('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener vines
    public function ordenes_trabajo_obtener_vines(Request $request){
        if($request->ajax()){
            $data = Vine::where('Cliente', $request->numeroclientefacturaa)->where('Status', 'ALTA')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarvin('.$data->Cliente.',\''.$data->Economico.'\',\''.$data->Vin.'\',\''.$data->Placas.'\',\''.$data->Motor.'\',\''.$data->Marca.'\',\''.$data->Modelo.'\',\''.$data->Año.'\',\''.$data->Color.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener servicios
    public function ordenes_trabajo_obtener_servicios(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $tipooperacion = $request->tipooperacion;
            $data = Servicio::where('Codigo', 'like', '%'.$codigoabuscar.'%')->where('Status', 'ALTA')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaservicio(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Servicio, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Venta).'\',\''.Helpers::convertirvalorcorrecto($data->Cantidad).'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$tipooperacion.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Venta', function($data){ 
                        return Helpers::convertirvalorcorrecto($data->Venta);
                    })
                    ->addColumn('Cantidad', function($data){ 
                        return Helpers::convertirvalorcorrecto($data->Cantidad);
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener tecnicos
    public function ordenes_trabajo_obtener_tecnicos(Request $request){
        if($request->ajax()){
            $data = Tecnico::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) {
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilatecnico('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //alta/guardar
    public function ordenes_trabajo_guardar(Request $request){
        ini_set('max_input_vars','10000' );
        $folio = Helpers::ultimofolioserietablamodulos('App\OrdenTrabajo', $request->serie);
        $orden = $folio.'-'.$request->serie;
        $ExisteOrden = OrdenTrabajo::where('Orden', $orden)->first();
	    if($ExisteOrden == true){
	        $OrdenTrabajo = 1;
	    }else{  
            //INGRESAR DATOS A TABLA ORDEN DE TRABAJO
            $OrdenTrabajo = new OrdenTrabajo;
            $OrdenTrabajo->Orden=$orden;
            $OrdenTrabajo->Serie=$request->serie;
            $OrdenTrabajo->Folio=$request->folio;
            $OrdenTrabajo->Tipo=$request->tipoorden;
            $OrdenTrabajo->Unidad=$request->tipounidad;
            $OrdenTrabajo->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
            $OrdenTrabajo->Entrega=Carbon::parse($request->fechaentregapromesa)->toDateTimeString();
            $OrdenTrabajo->Cliente=$request->numeroclientefacturaa;
            $OrdenTrabajo->DelCliente=$request->numeroclientedelcliente;
            $OrdenTrabajo->Agente=$request->numeroagente;
            $OrdenTrabajo->Caso=$request->caso;
            $OrdenTrabajo->TipoServicio=$request->tiposervicio;
            $OrdenTrabajo->Vin=$request->vin;
            $OrdenTrabajo->Motor=$request->motor;
            $OrdenTrabajo->Marca=$request->marca;
            $OrdenTrabajo->Modelo=$request->modelo;
            $OrdenTrabajo->Año=$request->ano;
            $OrdenTrabajo->Kilometros=$request->kilometros;
            $OrdenTrabajo->Placas=$request->placas;
            $OrdenTrabajo->Economico=$request->economico;
            $OrdenTrabajo->Color=$request->color;
            $OrdenTrabajo->KmProximoServicio=$request->kmproxservicio;
            $OrdenTrabajo->FechaRecordatorio=Carbon::parse($request->fecharecordatoriocliente)->toDateTimeString();
            $OrdenTrabajo->Reclamo=$request->reclamo;
            $OrdenTrabajo->Pedido=$request->ordencliente;
            $OrdenTrabajo->Campaña=$request->campana;
            $OrdenTrabajo->Promocion=$request->promocion;
            $OrdenTrabajo->Bahia=$request->bahia;
            $OrdenTrabajo->HorasReales=$request->horasreales;
            $OrdenTrabajo->Rodar=$request->rodar;
            $OrdenTrabajo->Plazo=$request->plazodias;
            $OrdenTrabajo->Falla=$request->falla;
            $OrdenTrabajo->ObsOrden=$request->observaciones;
            $OrdenTrabajo->Causa=$request->causa;
            $OrdenTrabajo->Correccion=$request->correccion;
            $OrdenTrabajo->Importe=$request->importe;
            $OrdenTrabajo->Descuento=$request->descuento;
            $OrdenTrabajo->SubTotal=$request->subtotal;
            $OrdenTrabajo->Iva=$request->iva;
            $OrdenTrabajo->Total=$request->total;
            $OrdenTrabajo->Utilidad=$request->subtotal;
            $OrdenTrabajo->Status="ABIERTA";
            $OrdenTrabajo->Usuario=Auth::user()->user;
            $OrdenTrabajo->Periodo=$request->periodohoy;
            $OrdenTrabajo->save();
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "ORDENES DE TRABAJO";
            $BitacoraDocumento->Movimiento = $orden;
            $BitacoraDocumento->Aplicacion = "ALTA";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = "ABIERTA";
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $request->periodohoy;
            $BitacoraDocumento->save();
            if($request->numerofilastablaservicios > 0){
                //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
                foreach ($request->codigopartida as $key => $codigopartida){             
                    $OrdenTrabajoDetalle=new OrdenTrabajoDetalle;
                    $OrdenTrabajoDetalle->Orden = $orden;
                    $OrdenTrabajoDetalle->Cliente = $request->numeroclientefacturaa;
                    $OrdenTrabajoDetalle->Agente = $request->numeroagente;
                    $OrdenTrabajoDetalle->Fecha = Carbon::parse($request->fechapartida [$key])->toDateTimeString();
                    $OrdenTrabajoDetalle->Codigo = $codigopartida;
                    $OrdenTrabajoDetalle->Descripcion = $request->descripcionpartida [$key];
                    $OrdenTrabajoDetalle->Anotaciones = $request->anotacionespartida [$key];
                    $OrdenTrabajoDetalle->Unidad = $request->unidadpartidad [$key];
                    $OrdenTrabajoDetalle->Cantidad =  $request->cantidadpartida  [$key];
                    $OrdenTrabajoDetalle->Precio =  $request->preciopartida [$key];
                    $OrdenTrabajoDetalle->Importe = $request->importepartida [$key];
                    $OrdenTrabajoDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Descuento = $request->descuentopesospartida [$key];
                    $OrdenTrabajoDetalle->SubTotal = $request->subtotalpartida [$key];
                    $OrdenTrabajoDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Iva = $request->ivapesospartida [$key];
                    $OrdenTrabajoDetalle->Total = $request->totalpesospartida [$key];
                    $OrdenTrabajoDetalle->Costo = $request->costopartida [$key];
                    $OrdenTrabajoDetalle->CostoTotal = $request->costototalpartida [$key];
                    $OrdenTrabajoDetalle->Com = $request->comisionporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Comision = $request->comisionpesospartida [$key];
                    $OrdenTrabajoDetalle->Utilidad = $request->utilidadpartida [$key];
                    $OrdenTrabajoDetalle->Departamento = $request->departamentopartida [$key];
                    $OrdenTrabajoDetalle->Cargo = $request->cargopartida [$key];
                    $OrdenTrabajoDetalle->Traspaso = $request->traspasopartida [$key];
                    $OrdenTrabajoDetalle->Compra = $request->comprapartida [$key];
                    $OrdenTrabajoDetalle->Item = $request->itempartida [$key];
                    $OrdenTrabajoDetalle->Usuario = $request->usuariopartida [$key];
                    $OrdenTrabajoDetalle->Tecnico1 = $request->numerotecnicopartida1 [$key];
                    $OrdenTrabajoDetalle->Tecnico2 = $request->numerotecnicopartida2 [$key];
                    $OrdenTrabajoDetalle->Tecnico3 = $request->numerotecnicopartida3 [$key];
                    $OrdenTrabajoDetalle->Tecnico4 = $request->numerotecnicopartida4 [$key];
                    $OrdenTrabajoDetalle->Horas1 = $request->horaspartida1 [$key];
                    $OrdenTrabajoDetalle->Horas2 = $request->horaspartida2 [$key];
                    $OrdenTrabajoDetalle->Horas3 = $request->horaspartida3 [$key];
                    $OrdenTrabajoDetalle->Horas4 = $request->horaspartida4 [$key];
                    $OrdenTrabajoDetalle->Promocion = $request->promocionpartida [$key];
                    $OrdenTrabajoDetalle->Status = $request->statuspartida [$key];
                    $OrdenTrabajoDetalle->Almacen = $request->almacenpartida [$key];
                    $OrdenTrabajoDetalle->Cotizacion = $request->cotizacionpartida [$key];
                    $OrdenTrabajoDetalle->Partida = $request->partidapartida [$key];
                    $OrdenTrabajoDetalle->save();
                }
            } 
        }
    	return response()->json($OrdenTrabajo); 
    }
    //obtener orden de trabajo
    public function ordenes_trabajo_obtener_orden_trabajo(Request $request){
        $ordentrabajo = OrdenTrabajo::where('Orden', $request->ordenmodificar)->first();
        $cliente = Cliente::where('Numero', $ordentrabajo->Cliente)->first();
        $delcliente = Cliente::where('Numero', $ordentrabajo->DelCliente)->first();
        $agente = Agente::where('Numero', $ordentrabajo->Agente)->first();
        //tipo orden trabajo seleccionada
        $tipos_ordenes_trabajo = TipoOrdenTrabajo::where('STATUS', 'ALTA')->get();
        $selecttipoordentrabajo = "<option selected disabled hidden>Selecciona...</option>";
        foreach($tipos_ordenes_trabajo as $tot){
            if($tot->Nombre == $ordentrabajo->Tipo){
                $selecttipoordentrabajo = $selecttipoordentrabajo.'<option selected value='.$tot->Nombre.'>'.$tot->Nombre.'</option>';
            }else{
                $selecttipoordentrabajo = $selecttipoordentrabajo.'<option value='.$tot->Nombre.'>'.$tot->Nombre.'</option>';
            }    
        }
        //tipo unidad seleccionada
        $tipos_unidades= TipoUnidad::where('STATUS', 'ALTA')->get();
        $selecttipounidad = "<option selected disabled hidden>Selecciona...</option>";
        foreach($tipos_unidades as $tu){
            if($tu->Nombre == $ordentrabajo->Unidad){
                $selecttipounidad = $selecttipounidad.'<option selected value='.$tu->Nombre.'>'.$tu->Nombre.'</option>';
            }else{
                $selecttipounidad = $selecttipounidad.'<option value='.$tu->Nombre.'>'.$tu->Nombre.'</option>';
            }    
        }
        //modificacion permitida
        if($ordentrabajo->Status == 'ABIERTA'){
            //$readonly = ''; 
        }else{
            //$readonly = 'readonly="readonly"';
        }
        $filasdetallesordentrabajo = '';
        $contadorservicios = 0;
        $contadorfilas = 0;
        $item = 1;
        $tipo = "modificacion";
        //detalles orden trabajo
        $detallesordentrabajo = OrdenTrabajoDetalle::where('Orden', $request->ordenmodificar)->OrderBy('Partida', 'ASC')->get();
        $numerodetallesordentrabajo = OrdenTrabajoDetalle::where('Orden', $request->ordenmodificar)->count();
        if($numerodetallesordentrabajo > 0){
            foreach($detallesordentrabajo as $dot){
                $counttecnico1 = Tecnico::where('Numero', $dot->Tecnico1)->count();
                $counttecnico2 = Tecnico::where('Numero', $dot->Tecnico2)->count();
                $counttecnico3 = Tecnico::where('Numero', $dot->Tecnico3)->count();
                $counttecnico4 = Tecnico::where('Numero', $dot->Tecnico4)->count();
                if($counttecnico1 > 0){
                    $obtenertecnico1 = Tecnico::where('Numero', $dot->Tecnico1)->first();
                    $tecnico1 = $obtenertecnico1->Nombre;
                }else{
                    $tecnico1 = $dot->Tecnico1;
                }
                if($counttecnico2 > 0){
                    $obtenertecnico2 = Tecnico::where('Numero', $dot->Tecnico2)->first();
                    $tecnico2 = $obtenertecnico2->Nombre;
                }else{
                    $tecnico2 = $dot->Tecnico2;
                }
                if($counttecnico3 > 0){
                    $obtenertecnico3 = Tecnico::where('Numero', $dot->Tecnico3)->first();
                    $tecnico3 = $obtenertecnico3->Nombre;
                }else{
                    $tecnico3 = $dot->Tecnico3;
                }
                if($counttecnico4 > 0){
                    $obtenertecnico4 = Tecnico::where('Numero', $dot->Tecnico4)->first();
                    $tecnico4 = $obtenertecnico4->Nombre;
                }else{
                    $tecnico4 = $dot->Tecnico4;
                }
                if($dot->Departamento == 'SERVICIO'){
                    $botonasignartecnicos = '<div class="btn bg-blue btn-xs" data-toggle="tooltip" title="Asignar Técnicos" onclick="asignaciontecnicos('.$contadorservicios.')">Asignar técnicos</div>';
                    $botoneliminarfila = '<div class="btn bg-red btn-xs" data-toggle="tooltip" title="Eliminar Fila" onclick="eliminarfila('.$contadorservicios.')">X</div> ';
                    if($ordentrabajo->Status == 'ABIERTA'){
                        $readonly = '';
                        $readonlyprecio = '';
                    }else{
                        $readonly = 'readonly="readonly"';
                        $readonlyprecio = 'readonly="readonly"';
                    }
                }else{
                    $botonasignartecnicos = '';
                    $botoneliminarfila = '';
                    if($ordentrabajo->Status == 'ABIERTA'){
                        $readonlyprecio = '';
                    }else{
                        $readonlyprecio = 'readonly="readonly"';
                    }
                    $readonly = 'readonly="readonly"';
                }
                $filasdetallesordentrabajo=$filasdetallesordentrabajo. 
                '<tr class="filasservicios" id="filaservicio'.$contadorservicios.'">'.
                    '<td class="tdmod"><div class="divorinputmodmd">'.
                    $botoneliminarfila.
                    $botonasignartecnicos.
                    '<input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dot->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly>'.
                    '</div></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control tipofila" name="tipofila[]" value="consultado" readonly><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dot->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dot->Codigo.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.$dot->Descripcion.'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadpartidad" name="unidadpartidad[]" value="'.$dot->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dot->Unidad.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');" '.$readonlyprecio.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dot->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dot->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dot->Utilidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs departamentopartida" name="departamentopartida[]" value="'.$dot->Departamento.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs cargopartida" name="cargopartida[]" value="'.$dot->Cargo.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="date" class="form-control divorinputmodmd fechapartida" name="fechapartida[]" value="'.Helpers::formatoinputdate($dot->Fecha).'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs traspasopartida" name="traspasopartida[]" value="'.$dot->Traspaso.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs comprapartida" name="comprapartida[]" value="'.$dot->Compra.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs usuariopartida" name="usuariopartida[]" value="'.$dot->Usuario.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl anotacionespartida" name="anotacionespartida[]" value="'.$dot->Anotaciones.'" '.$readonly.'  data-parsley-length="[1, 255]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs statuspartida" name="statuspartida[]" value="'.$dot->Status.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxs itempartidabd" name="itempartidabd[]" value="'.$dot->Item.'" readonly><input type="text" class="form-control divorinputmodxs itempartida" name="itempartida[]" value="'.$dot->Item.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida1" name="numerotecnicopartida1[]" value="'.$dot->Tecnico1.'" readonly><input type="text" class="form-control divorinputmodl tecnicopartida1" name="tecnicopartida1[]" value="'.$tecnico1.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida2" name="numerotecnicopartida2[]" value="'.$dot->Tecnico2.'" readonly><input type="text" class="form-control divorinputmodl tecnicopartida2" name="tecnicopartida2[]" value="'.$tecnico2.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida3" name="numerotecnicopartida3[]" value="'.$dot->Tecnico3.'" readonly><input type="text" class="form-control divorinputmodl tecnicopartida3" name="tecnicopartida3[]" value="'.$tecnico3.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida4" name="numerotecnicopartida4[]" value="'.$dot->Tecnico4.'" readonly><input type="text" class="form-control divorinputmodl tecnicopartida4" name="tecnicopartida4[]" value="'.$tecnico4.'" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida1" name="horaspartida1[]" value="'.Helpers::convertirvalorcorrecto($dot->Horas1).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida2" name="horaspartida2[]" value="'.Helpers::convertirvalorcorrecto($dot->Horas2).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida3" name="horaspartida3[]" value="'.Helpers::convertirvalorcorrecto($dot->Horas3).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm horaspartida4" name="horaspartida4[]" value="'.Helpers::convertirvalorcorrecto($dot->Horas4).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs promocionpartida" name="promocionpartida[]" value="'.$dot->Promocion.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs partidapartida" name="partidapartida[]" value="'.$dot->Partida.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs almacenpartida" name="almacenpartida[]" value="'.$dot->Almacen.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs cotizacionpartida" name="cotizacionpartida[]" value="'.$dot->Cotizacion.'" readonly data-parsley-length="[1, 20]"></td>'.
                '</tr>';
                $contadorservicios++;
                $contadorfilas++;
                $item++;
            }
        }else{
            $filasdetallesordentrabajo = '';
        }  
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($ordentrabajo->Status != 'ABIERTA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($ordentrabajo->Status != 'ABIERTA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($ordentrabajo->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        } 
        $data = array(
            "ordentrabajo" => $ordentrabajo,
            "filasdetallesordentrabajo" => $filasdetallesordentrabajo,
            "numerodetallesordentrabajo" => $numerodetallesordentrabajo,
            "contadorservicios" => $contadorservicios,
            "contadorfilas" => $contadorfilas,
            "item" => $item,
            "modificacionpermitida" => $modificacionpermitida,
            "cliente" => $cliente,
            "delcliente" => $delcliente,
            "agente" => $agente,
            "selecttipoordentrabajo" => $selecttipoordentrabajo,
            "selecttipounidad" => $selecttipounidad,
            "fecha" => Helpers::formatoinputdatetime($ordentrabajo->Fecha),
            "fechaentrega" => Helpers::formatoinputdatetime($ordentrabajo->Entrega),
            "fecharecordatoriocliente" => Helpers::formatoinputdate($ordentrabajo->FechaRecordatorio),
            "kilometros" => Helpers::convertirvalorcorrecto($ordentrabajo->Kilometros),
            "kmproximoservicio" => Helpers::convertirvalorcorrecto($ordentrabajo->KmProximoServicio),
            "importe" => Helpers::convertirvalorcorrecto($ordentrabajo->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($ordentrabajo->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($ordentrabajo->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($ordentrabajo->Iva),
            "total" => Helpers::convertirvalorcorrecto($ordentrabajo->Total)
        );
        return response()->json($data);
    }
    //modificacion
    public function ordenes_trabajo_guardar_modificacion(Request $request){
        ini_set('max_input_vars','10000' );
        $orden = $request->folio.'-'.$request->serie;
        $OrdenTrabajo = OrdenTrabajo::where('Orden', $orden)->first();
        //modificar orden
        OrdenTrabajo::where('Orden', $orden)
        ->update([
            'Tipo'=>$request->tipoorden,
            'Unidad'=>$request->tipounidad,
            'Fecha'=>Carbon::parse($request->fecha)->toDateTimeString(),
            'Entrega'=>Carbon::parse($request->fechaentregapromesa)->toDateTimeString(),
            'Cliente'=>$request->numeroclientefacturaa,
            'DelCliente'=>$request->numeroclientedelcliente,
            'Agente'=>$request->numeroagente,
            'Caso'=>$request->caso,
            'TipoServicio'=>$request->tiposervicio,
            'Vin'=>$request->vin,
            'Motor'=>$request->motor,
            'Marca'=>$request->marca,
            'Modelo'=>$request->modelo,
            'Año'=>$request->ano,
            'Kilometros'=>$request->kilometros,
            'Placas'=>$request->placas,
            'Economico'=>$request->economico,
            'Color'=>$request->color,
            'KmProximoServicio'=>$request->kmproxservicio,
            'FechaRecordatorio'=>Carbon::parse($request->fecharecordatoriocliente)->toDateTimeString(),
            'Reclamo'=>$request->reclamo,
            'Pedido'=>$request->ordencliente,
            'Campaña'=>$request->campana,
            'Promocion'=>$request->promocion,
            'Bahia'=>$request->bahia,
            'HorasReales'=>$request->horasreales,
            'Rodar'=>$request->rodar,
            'Plazo'=>$request->plazodias,
            'Falla'=>$request->falla,
            'ObsOrden'=>$request->observaciones,
            'Causa'=>$request->causa,
            'Correccion'=>$request->correccion,
            'Importe'=>$request->importe,
            'Descuento'=>$request->descuento,
            'SubTotal'=>$request->subtotal,
            'Iva'=>$request->iva,
            'Total'=>$request->total,
            'Utilidad'=>$request->subtotal
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE TRABAJO";
        $BitacoraDocumento->Movimiento = $orden;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $OrdenTrabajo->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        $numerodetallesordentrabajomodificada = OrdenTrabajoDetalle::where('Orden', $orden)->count();
        if($request->numerofilastablaservicios == 0){
            if($numerodetallesordentrabajomodificada > 0){
                OrdenTrabajoDetalle::where('Orden', $orden)->forceDelete();
            }
        }else{
            //eliminar detalles que se quitaron en la modificacion
            $DetallesOrdenTrabajoAntesDeModificacion = OrdenTrabajoDetalle::where('Orden', $orden)->get();
            $array_detalles_antes_de_modificar = [];
            foreach($DetallesOrdenTrabajoAntesDeModificacion as $dotadm){
                array_push($array_detalles_antes_de_modificar, $dotadm->Orden.'#'.$dotadm->Codigo.'#'.$dotadm->Item);
            }
            $array_detalles_despues_de_modificar = [];
            foreach ($request->codigopartida as $key => $codigopartida){   
                if($request->tipofila [$key] == 'consultado'){
                    array_push($array_detalles_despues_de_modificar, $orden.'#'.$codigopartida.'#'.$request->itempartidabd [$key]);
                }  
            }
            $diferencias = array_diff($array_detalles_antes_de_modificar, $array_detalles_despues_de_modificar);
            foreach($diferencias as $d){
                $explode_d = explode("#",$d);
                $diff = $explode_d[0];
                $EliminaDetalle = OrdenTrabajoDetalle::where('Orden', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
            }
            //modificar partidas que no se eliminaron
            foreach ($request->codigopartida as $key => $codigopartida){   
                if($request->tipofila [$key] == 'consultado'){
                    OrdenTrabajoDetalle::where('Orden', $orden)
                    ->where('Item', $request->itempartidabd [$key])
                    ->update([
                        'Orden' => $orden,
                        'Cliente' => $request->numeroclientefacturaa,
                        'Agente' => $request->numeroagente,
                        'Fecha' => Carbon::parse($request->fechapartida [$key])->toDateTimeString(),
                        'Codigo' => $codigopartida,
                        'Descripcion' => $request->descripcionpartida [$key],
                        'Anotaciones' => $request->anotacionespartida [$key],
                        'Unidad' => $request->unidadpartidad [$key],
                        'Cantidad' =>  $request->cantidadpartida  [$key],
                        'Precio' =>  $request->preciopartida [$key],
                        'Importe' => $request->importepartida [$key],
                        'Dcto' => $request->descuentoporcentajepartida [$key],
                        'Descuento' => $request->descuentopesospartida [$key],
                        'SubTotal' => $request->subtotalpartida [$key],
                        'Impuesto' => $request->ivaporcentajepartida [$key],
                        'Iva' => $request->ivapesospartida [$key],
                        'Total' => $request->totalpesospartida [$key],
                        'Costo' => $request->costopartida [$key],
                        'CostoTotal' => $request->costototalpartida [$key],
                        'Com' => $request->comisionporcentajepartida [$key],
                        'Comision' => $request->comisionpesospartida [$key],
                        'Utilidad' => $request->utilidadpartida [$key],
                        'Departamento' => $request->departamentopartida [$key],
                        'Cargo' => $request->cargopartida [$key],
                        'Traspaso' => $request->traspasopartida [$key],
                        'Compra' => $request->comprapartida [$key],
                        'Item' => $request->itempartida [$key],
                        'Usuario' => $request->usuariopartida [$key],
                        'Tecnico1' => $request->numerotecnicopartida1 [$key],
                        'Tecnico2' => $request->numerotecnicopartida2 [$key],
                        'Tecnico3' => $request->numerotecnicopartida3 [$key],
                        'Tecnico4' => $request->numerotecnicopartida4 [$key],
                        'Horas1' => $request->horaspartida1 [$key],
                        'Horas2' => $request->horaspartida2 [$key],
                        'Horas3' => $request->horaspartida3 [$key],
                        'Horas4' => $request->horaspartida4 [$key],
                        'Promocion' => $request->promocionpartida [$key],
                        'Status' => $request->statuspartida [$key],
                        'Almacen' => $request->almacenpartida [$key],
                        'Cotizacion' => $request->cotizacionpartida [$key],
                        'Partida' => $request->partidapartida [$key]
                    ]);
                }elseif($request->tipofila [$key] == 'agregado'){
                    //agregar todas las partidas agregadas en la modificación
                    $OrdenTrabajoDetalle=new OrdenTrabajoDetalle;
                    $OrdenTrabajoDetalle->Orden = $orden;
                    $OrdenTrabajoDetalle->Cliente = $request->numeroclientefacturaa;
                    $OrdenTrabajoDetalle->Agente = $request->numeroagente;
                    $OrdenTrabajoDetalle->Fecha = Carbon::parse($request->fechapartida [$key])->toDateTimeString();
                    $OrdenTrabajoDetalle->Codigo = $codigopartida;
                    $OrdenTrabajoDetalle->Descripcion = $request->descripcionpartida [$key];
                    $OrdenTrabajoDetalle->Anotaciones = $request->anotacionespartida [$key];
                    $OrdenTrabajoDetalle->Unidad = $request->unidadpartidad [$key];
                    $OrdenTrabajoDetalle->Cantidad =  $request->cantidadpartida  [$key];
                    $OrdenTrabajoDetalle->Precio =  $request->preciopartida [$key];
                    $OrdenTrabajoDetalle->Importe = $request->importepartida [$key];
                    $OrdenTrabajoDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Descuento = $request->descuentopesospartida [$key];
                    $OrdenTrabajoDetalle->SubTotal = $request->subtotalpartida [$key];
                    $OrdenTrabajoDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Iva = $request->ivapesospartida [$key];
                    $OrdenTrabajoDetalle->Total = $request->totalpesospartida [$key];
                    $OrdenTrabajoDetalle->Costo = $request->costopartida [$key];
                    $OrdenTrabajoDetalle->CostoTotal = $request->costototalpartida [$key];
                    $OrdenTrabajoDetalle->Com = $request->comisionporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Comision = $request->comisionpesospartida [$key];
                    $OrdenTrabajoDetalle->Utilidad = $request->utilidadpartida [$key];
                    $OrdenTrabajoDetalle->Departamento = $request->departamentopartida [$key];
                    $OrdenTrabajoDetalle->Cargo = $request->cargopartida [$key];
                    $OrdenTrabajoDetalle->Traspaso = $request->traspasopartida [$key];
                    $OrdenTrabajoDetalle->Compra = $request->comprapartida [$key];
                    $OrdenTrabajoDetalle->Item = $request->itempartida [$key];
                    $OrdenTrabajoDetalle->Usuario = $request->usuariopartida [$key];
                    $OrdenTrabajoDetalle->Tecnico1 = $request->numerotecnicopartida1 [$key];
                    $OrdenTrabajoDetalle->Tecnico2 = $request->numerotecnicopartida2 [$key];
                    $OrdenTrabajoDetalle->Tecnico3 = $request->numerotecnicopartida3 [$key];
                    $OrdenTrabajoDetalle->Tecnico4 = $request->numerotecnicopartida4 [$key];
                    $OrdenTrabajoDetalle->Horas1 = $request->horaspartida1 [$key];
                    $OrdenTrabajoDetalle->Horas2 = $request->horaspartida2 [$key];
                    $OrdenTrabajoDetalle->Horas3 = $request->horaspartida3 [$key];
                    $OrdenTrabajoDetalle->Horas4 = $request->horaspartida4 [$key];
                    $OrdenTrabajoDetalle->Promocion = $request->promocionpartida [$key];
                    $OrdenTrabajoDetalle->Status = $request->statuspartida [$key];
                    $OrdenTrabajoDetalle->Almacen = $request->almacenpartida [$key];
                    $OrdenTrabajoDetalle->Cotizacion = $request->cotizacionpartida [$key];
                    $OrdenTrabajoDetalle->Partida = $request->partidapartida [$key];
                    $OrdenTrabajoDetalle->save();                    
                }          
            }
        }
    	return response()->json($OrdenTrabajo);         
    }

    //verificar el registro que se dara de baja
    public function ordenes_trabajo_verificar_uso_en_modulos(Request $request){
        $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->ordendesactivar)->first();
        $resultado = OrdenTrabajoDetalle::where('Orden', $request->ordendesactivar)->count();
        $condetalles = false;
        if($resultado > 0){
            $condetalles = true;
        }
        $resultadofechas = Helpers::compararanoymesfechas($OrdenTrabajo->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'condetalles' => $condetalles,
            'Status' => $OrdenTrabajo->Status
        );
        return response()->json($data);
    }

    //dar de baja registro
    public function ordenes_trabajo_alta_o_baja(Request $request){
        $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->ordendesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        OrdenTrabajo::where('Orden', $request->ordendesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Impuesto' => '0.000000',
                    'Importe' => '0.000000',
                    'Descuento' => '0.000000',
                    'SubTotal' => '0.000000',
                    'Iva' => '0.000000',
                    'Total' => '0.000000',
                    'Facturado' => '0.000000',
                    'Costo' => '0.000000',
                    'Comision' => '0.000000',
                    'Utilidad' => '0.000000',
                    'HorasReales' => '0.000000'
                ]);
        $detalles = OrdenTrabajoDetalle::where('Orden', $request->ordendesactivar)->get();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE TRABAJO";
        $BitacoraDocumento->Movimiento = $request->ordendesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $OrdenTrabajo->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($OrdenTrabajo);
    }

    //verificar status del registro
    public function ordenes_trabajo_verificar_status_orden(Request $request){
        $ordentrabajo = OrdenTrabajo::where('Orden', $request->ordenterminar)->first();
        $cliente = Cliente::where('Numero', $ordentrabajo->Cliente)->first();
        $data = array(
            'ordentrabajo' => $ordentrabajo,
            'cliente' => $cliente,
            "fecha" => Helpers::formatoinputdatetime($ordentrabajo->Fecha)
        );
        return response()->json($data); 
    }

    //terminar orden de trabajo
    public function ordenes_trabajo_terminar_orden(Request $request){
        $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->ordenterminar)->first();
        $OrdenTrabajo->Status = 'CERRADA';
        $OrdenTrabajo->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE TRABAJO";
        $BitacoraDocumento->Movimiento = $request->ordenterminar;
        $BitacoraDocumento->Aplicacion = "CERRAR";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $OrdenTrabajo->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($OrdenTrabajo);  
    }

    //buscar folio on key up
    public function ordenes_trabajo_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = OrdenTrabajo::where('Orden', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Orden .'\')"><i class="material-icons">done</i></div> ';
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
    public function ordenes_trabajo_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $ordenestrabajo = OrdenTrabajo::whereIn('Orden', $request->arraypdf)->orderBy('Folio', 'ASC')->take(500)->get(); 
        }else{
            //$contrarecibos = ContraRecibo::where('Fecha', $request->anopdf)->get(); 
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $ordenestrabajo = OrdenTrabajo::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($ordenestrabajo as $ot){
            $ordentrabajodetalle = OrdenTrabajoDetalle::where('Orden', $ot->Orden)->get();
            $datadetalle=array();
            foreach($ordentrabajodetalle as $otd){
                $serviciodetalle = Servicio::where('Codigo', $otd->Codigo)->first();
                $datadetalle[]=array(
                    "codigodetalle"=>$otd->Codigo,
                    "descripciondetalle"=>$otd->Descripcion,
                    "unidaddetalle"=>$otd->Unidad,
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($otd->Cantidad),
                    "preciodetalle" => Helpers::convertirvalorcorrecto($otd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($otd->Descuento),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($otd->SubTotal),
                    "totaldetalle" => Helpers::convertirvalorcorrecto($otd->Total)
                );
            } 
            $cliente = Cliente::where('Numero', $ot->Cliente)->first();
            $data[]=array(
                    "ordentrabajo"=>$ot,
                    "importeordentrabajo"=>Helpers::convertirvalorcorrecto($ot->Importe),
                    "descuentoordentrabajo"=>Helpers::convertirvalorcorrecto($ot->Descuento),
                    "subtotalordentrabajo"=>Helpers::convertirvalorcorrecto($ot->SubTotal),
                    "ivaordentrabajo"=>Helpers::convertirvalorcorrecto($ot->Iva),
                    "totalordentrabajo"=>Helpers::convertirvalorcorrecto($ot->Total),
                    "cliente" => $cliente,
                    "datadetalle" => $datadetalle,
                    "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ordenestrabajo.formato_pdf_ordenestrabajo', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 5)
        ->setOption('margin-right', 5)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }
    //funcion exportar excel
    public function ordenes_trabajo_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new OrdenesDeTrabajoExport($this->campos_consulta,$request->periodo), "ordenesdetrabajo-".$request->periodo.".xlsx");   
    }  
    //configuracion tabla  
    public function ordenes_trabajo_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'OrdenesDeTrabajo')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('ordenes_trabajo');
    }

}
