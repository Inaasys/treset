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
use App\Exports\AjustesInventarioExport;
use App\AjusteInventario;
use App\AjusteInventarioDetalle;
use App\Serie;
use App\Almacen;
use App\Existencia;
use App\BitacoraDocumento;
use App\Producto;
use App\Configuracion_Tabla;
use App\VistaAjusteInventario;

class AjusteInventarioController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'AjustesInventario')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function ajustesinventario(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->email, 'Ajustes de Inventario');
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('ajustesinventario_guardar_configuracion_tabla');
        return view('registros.ajustesinventario.ajustesinventario', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla'));
    }
    
    //obtener las asignaciones de herramienta
    public function ajustesinventario_obtener(Request $request){
        if($request->ajax()){
            $periodo = $request->periodo;
            $data = VistaAjusteInventario::select($this->campos_consulta)->orderBy('Folio', 'DESC')->where('periodo', $periodo)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $botoncambios =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Ajuste .'\')"><i class="material-icons">mode_edit</i></div> '; 
                        $botonbajas =      '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Ajuste .'\')"><i class="material-icons">cancel</i></div> ';
                        if($data->Status == 'BAJA'){
                            $operaciones = '';
                        }else{
                            $operaciones =  $botoncambios.$botonbajas;
                        }
                        return $operaciones;
                    })
                    ->addColumn('total', function($data){ return $data->Total; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }

    //obtener ultimi registro
    public function ajustesinventario_obtener_ultimo_id(){
        $folio = Helpers::ultimofoliotablamodulos('App\AjusteInventario');
        return response()->json($folio);
    }

    //obtener almacenes
    public function ajustesinventario_obtener_almacenes(Request $request){
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

    //obtener productos
    public function ajustesinventario_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $data = DB::table('Productos as t')
            ->leftJoin('Marcas as m', 'm.Numero', '=', 't.Marca')
            ->leftJoin(DB::raw("(select codigo, sum(existencias) as existencias from Existencias group by codigo) as e"),
                function($join){
                    $join->on("e.codigo","=","t.codigo");
                })
            ->select('t.Codigo as Codigo', 't.Producto as Producto', 't.Ubicacion as Ubicacion', 'e.Existencias as Existencias', 't.Costo as Costo', 't.SubTotal as SubTotal', 't.Marca as Marca', 't.Status as Status', 't.Unidad AS Unidad', 't.Impuesto AS Impuesto')
            ->where('t.Codigo', 'like', '%' . $codigoabuscar . '%')
            ->get();
            /*
            $data = DB::table('Productos as t')
            ->leftJoin('Marcas as m', 'm.Numero', '=', 't.Marca')
            ->join('Existencias as e', 'e.Codigo', '=', 't.Codigo')
            ->leftJoin(DB::raw("(
                                    select t.Codigo, t.Almacen, t.Ubicacion
                                    From
                                    (
                                        select 1 as Almacen, Codigo, Ubicacion from productos
                                        Union All
                                        select Almacen, Codigo, Ubicacion from [Productos Ubicaciones] where almacen > 1
                                    ) as t
                                ) as u "),
            function($join){
                $join->on("u.Codigo","=","e.Codigo")->on("u.Almacen","=","e.Almacen");
            })
            ->select('t.Codigo as Codigo', 't.Producto as Producto', 'e.Almacen as Almacen', 'u.Ubicacion as Ubicacion', 'e.Existencias as Existencias', 't.Costo as Costo', 't.SubTotal as SubTotal', 't.Marca as Marca', 't.Status as Status', 't.Unidad AS Unidad', 't.Impuesto AS Impuesto')
            ->where('t.Codigo', 'like', '%' . $codigoabuscar . '%')
            ->get();*/
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\')">Seleccionar</div>';
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
                    ->rawColumns(['operaciones','Costo','Existencias','SubTotal'])
                    ->make(true);
        } 
    }

    //obtener existencia actuales del codigo seleccionada en el almacen seleccionado
    public function ajustesinventario_obtener_existencias_por_codigo_y_almacen(Request $request){
        $ContarExistencia = Existencia::where('Codigo', $request->Codigo)->where('Almacen', $request->numeroalmacen)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::where('Codigo', $request->Codigo)->where('Almacen',$request->numeroalmacen)->first();
            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $Existencias = Helpers::convertirvalorcorrecto(0);
        }
        return response()->json($Existencias);
    }

    //guardar
    public function ajustesinventario_guardar(Request $request){
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofoliotablamodulos('App\AjusteInventario');
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $ajuste = $folio.'-'.$request->serie;
        $AjusteInventario = new AjusteInventario;
        $AjusteInventario->Ajuste=$ajuste;
        $AjusteInventario->Serie=$request->serie;
        $AjusteInventario->Folio=$folio;
        $AjusteInventario->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $AjusteInventario->Obs=$request->observaciones;
        $AjusteInventario->Almacen=$request->numeroalmacen;
        $AjusteInventario->Total=$request->total;
        $AjusteInventario->Status="ALTA";
        $AjusteInventario->Usuario=Auth::user()->user;
        $AjusteInventario->Periodo=$request->periodohoy;
        $AjusteInventario->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "AJUSTES DE INVENTARIO";
        $BitacoraDocumento->Movimiento = $ajuste;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
            $AjusteInventarioDetalle=new AjusteInventarioDetalle;
            $AjusteInventarioDetalle->Ajuste = $ajuste;
            $AjusteInventarioDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $AjusteInventarioDetalle->Codigo = $codigoproductopartida;
            $AjusteInventarioDetalle->Descripcion = $request->nombreproductopartida [$key];
            $AjusteInventarioDetalle->Unidad = $request->unidadproductopartida [$key];
            $AjusteInventarioDetalle->Existencias =  $request->existenciaactualpartida   [$key];
            $AjusteInventarioDetalle->Entradas =  $request->entradaspartida [$key];
            $AjusteInventarioDetalle->Salidas = $request->salidaspartida [$key];
            $AjusteInventarioDetalle->Real = $request->existencianuevapartida  [$key];
            $AjusteInventarioDetalle->Costo = $request->costopartida  [$key];
            $AjusteInventarioDetalle->Item = $item;
            $AjusteInventarioDetalle->save();
            $item++;
            //modificar las existencias del código en la tabla de existencias
            $ContarExistencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
            if($ContarExistencia > 0){
                $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
            }else{
                $Existencia = new Existencia;
            }
            $Existencia->Codigo = $codigoproductopartida;
            $Existencia->Almacen = $request->numeroalmacen;
            $Existencia->Existencias = $request->existencianuevapartida [$key];
            $Existencia->save();
        }
        return response()->json($AjusteInventario);
    }

    //verificar si se puede dar de baja
    public function ajustesinventario_verificar_baja(Request $request){
        $AjusteInventario = AjusteInventario::where('Ajuste', $request->ajustedesactivar)->first();
        $detalles = AjusteInventarioDetalle::where('Ajuste', $request->ajustedesactivar)->get();
        $errores = '';
        foreach($detalles as $detalle){
            if($detalle->Entradas > 0){
                $Existencia = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $AjusteInventario->Almacen)->first();
                if($detalle->Entradas > $Existencia->Existencias){
                    $errores = $errores.'Error el ajuste no se puede cancelar, no hay existencias suficientes en el almacen:'.$AjusteInventario->Almacen.' para el código:'.$detalle->Codigo.'<br>';
                }

            }
        }
        return response()->json($errores);
    }

    //bajas
    public function ajustesinventario_alta_o_baja(Request $request){
        $AjusteInventario = AjusteInventario::where('Ajuste', $request->ajustedesactivar)->first();
        $AjusteInventario->MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        $AjusteInventario->Status = 'BAJA';
        $AjusteInventario->save();
        $detalles = AjusteInventarioDetalle::where('Ajuste', $request->ajustedesactivar)->get();
        foreach($detalles as $detalle){
            //entradas
            if($detalle->Entradas > 0){
                //restar las entradas
                $Existencia = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $AjusteInventario->Almacen)->first();
                $Existencia->Existencias = $Existencia->Existencias-$detalle->Entradas;
                $Existencia->save();
            }
            //salidas
            if($detalle->Salidas > 0){
                //sumar las salidas
                $Existencia = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $AjusteInventario->Almacen)->first();
                $Existencia->Existencias = $Existencia->Existencias+$detalle->Salidas;
                $Existencia->save();
            }
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "AJUSTES DE INVENTARIO";
        $BitacoraDocumento->Movimiento = $request->ajustedesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $AjusteInventario->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($AjusteInventario);
    }

    //obtener ajuste
    public function ajustesinventario_obtener_ajuste(Request $request){
        $ajuste = AjusteInventario::where('Ajuste', $request->ajustemodificar)->first();
        $almacen = Almacen::where('Numero', $ajuste->Almacen)->first();
        //detalles
        $detallesajuste= AjusteInventarioDetalle::where('Ajuste', $request->ajustemodificar)->get();
        $numerodetallesajuste = AjusteInventarioDetalle::where('Ajuste', $request->ajustemodificar)->count();
        if($numerodetallesajuste > 0){
            $filasdetallesajuste = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            foreach($detallesajuste as $da){
                $producto = Producto::where('Codigo', $da->Codigo)->first();
                $Existencia = Existencia::where('Codigo', $da->Codigo)->where('Almacen', $ajuste->Almacen)->first();
                $subtotalentradaspartida = $da->Entradas*$da->Costo;
                $subtotalsalidaspartida = $da->Salidas*$da->Costo;
                $parsleymax = $Existencia->Existencias+$da->Salidas;
                $filasdetallesajuste= $filasdetallesajuste.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$da->Item.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$da->Codigo.'" readonly>'.$da->Codigo.'</td>'.
                    '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control nombreproductopartida" name="nombreproductopartida[]" value="'.$da->Descripcion.'" readonly>'.$da->Descripcion.'</div></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$da->Unidad.'" readonly>'.$da->Unidad.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm existenciaactualpartida" name="existenciaactualpartida[]" value="'.Helpers::convertirvalorcorrecto($Existencia->Existencias).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm entradaspartidadb" name="entradaspartidadb[]" value="'.Helpers::convertirvalorcorrecto($da->Entradas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm entradaspartida" name="entradaspartida[]" value="'.Helpers::convertirvalorcorrecto($da->Entradas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularsubtotalentradas('.$contadorfilas.');calcularexistencianueva('.$contadorfilas.');">'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm salidaspartidadb" name="salidaspartidadb[]" value="'.Helpers::convertirvalorcorrecto($da->Salidas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/"  readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm salidaspartida" name="salidaspartida[]" value="'.Helpers::convertirvalorcorrecto($da->Salidas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" data-parsley-max="'.$parsleymax.'" onchange="formatocorrectoinputcantidades(this);calcularsubtotalsalidas('.$contadorfilas.');calcularexistencianueva('.$contadorfilas.');">'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm existencianuevapartida" name="existencianuevapartida[]" value="'.Helpers::convertirvalorcorrecto($da->Real).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($da->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalentradaspartida" name="subtotalentradaspartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalentradaspartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalsalidaspartida" name="subtotalsalidaspartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalsalidaspartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesajuste = '';
        }        
        $data = array(
            "ajuste" => $ajuste,
            "almacen" => $almacen,
            "filasdetallesajuste" => $filasdetallesajuste,
            "numerodetallesajuste" => $numerodetallesajuste,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdate($ajuste->Fecha),
            "total" => Helpers::convertirvalorcorrecto($ajuste->Total),
            "modificacionpermitida" => 1
        );
        return response()->json($data); 
    }

    //modificar
    public function ajustesinventario_guardar_modificacion(Request $request){
        $ajuste = $request->folio.'-'.$request->serie;
        $AjusteInventario = AjusteInventario::where('Ajuste', $ajuste)->first();
        $AjusteInventario->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $AjusteInventario->Obs=$request->observaciones;
        $AjusteInventario->Almacen=$request->numeroalmacen;
        $AjusteInventario->Total=$request->total;
        $AjusteInventario->Usuario=Auth::user()->user;
        $AjusteInventario->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "AJUSTES DE INVENTARIO";
        $BitacoraDocumento->Movimiento = $ajuste;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $AjusteInventario->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){    
            //modificar las existencias del código en la tabla de existencias
            $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
            $Existencia->Existencias = $request->existencianuevapartida [$key];
            $Existencia->save();
            //existencias actuales
            $existenciaactualpartida = $request->existenciaactualpartida [$key] + $request->salidaspartidadb [$key] - $request->entradaspartidadb [$key];
            AjusteInventarioDetalle::where('Ajuste', $ajuste)
            ->where('Item', $request->itempartida [$key])
            ->update([
                'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                'Existencias' => $existenciaactualpartida,
                'Entradas' => $request->entradaspartida [$key],
                'Salidas' => $request->salidaspartida  [$key],
                'Real' => $request->existencianuevapartida [$key],
                'Costo' => $request->costopartida [$key]
            ]);
        }
        return response()->json($AjusteInventario);
    }

    public function ajustesinventario_exportar_excel(){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new AjustesInventarioExport($this->campos_consulta), "ajustesinventario.xlsx");   
    }

    //guardar configuracion tabla
    public function ajustesinventario_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'AjustesInventario')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('ajustesinventario');
    }
}
