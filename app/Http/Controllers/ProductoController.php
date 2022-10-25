<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DB;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosExport;
use App\Producto;
use App\Agente;
use App\Tabla;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Marca;
use App\Linea;
use App\Moneda;
use App\Cliente;
use App\ProductoPrecio;
use App\ProductoConsumo;
use App\Almacen;
use App\Existencia;
use App\Configuracion_Tabla;
use App\VistaProducto;
use App\RegistrosAcciones;
use App\TipoOrdenCompra;
use App\VistaObtenerExistenciaProducto;
use App\AjusteInventario;
use App\Remision;
use App\Compra;
use App\Traspaso;
use App\Proveedor;
use App\NotaProveedor;
use App\Produccion;
use App\Exports\KardexProductoExport;
use App\OrdenTrabajo;
use DNS1D;
use DNS2D;
use PDF;

class ProductoController extends ConfiguracionSistemaController{

    public function __construct(){

        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function productos(){
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Productos', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('productos_guardar_configuracion_tabla');
        $rutacrearpdfcodigosdebarrascatalogo = route('productos_generar_codigos_barras_catalogo');
        $rutacrearpdfcodigosdebarrasarray = route('productos_generar_codigos_barras_array');
        return view('catalogos.productos.productos', compact('configuracion_tabla','rutaconfiguraciontabla','rutacrearpdfcodigosdebarrascatalogo','rutacrearpdfcodigosdebarrasarray'));
    }
    //obtener todos los registros
    public function productos_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Productos', Auth::user()->id);
            $data = VistaProducto::select($configuraciones_tabla['campos_consulta']);
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
                    ->withQuery('sumacosto', function($data) {
                        return $data->sum('Costo');
                    })
                    ->withQuery('sumaultimocosto', function($data) {
                        return $data->sum('UltimoCosto');
                    })
                    ->withQuery('sumaultimaventa', function($data) {
                        return $data->sum('UltimaVenta');
                    })
                    ->withQuery('sumaprecio', function($data) {
                        return $data->sum('Precio');
                    })
                    ->withQuery('sumaexistencias', function($data) {
                        return $data->sum('Existencias');
                    })
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos(\''. htmlspecialchars($data->Codigo , ENT_QUOTES) .'\')">Cambios</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar(\''. htmlspecialchars($data->Codigo , ENT_QUOTES) .'\')">Bajas</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerkardex(\''. htmlspecialchars($data->Codigo , ENT_QUOTES) .'\','.$data->Almacen.')">Ver Movimientos</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Existencias', function($data){ return Helpers::convertirvalorcorrecto($data->Existencias); })
                    ->addColumn('Costo', function($data){ return $data->Costo; })
                    ->addColumn('CostoDeLista', function($data){ return $data->CostoDeLista; })
                    ->addColumn('CostoDeVenta', function($data){ return $data->CostoDeVenta; })
                    ->addColumn('Utilidad', function($data){ return $data->Utilidad; })
                    ->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    ->addColumn('Iva', function($data){ return $data->Iva; })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->addColumn('Precio', function($data){ return $data->Precio; })
                    ->addColumn('Impuesto', function($data){ return $data->Impuesto; })
                    ->addColumn('Venta', function($data){ return $data->Venta; })
                    ->addColumn('UltimoCosto', function($data){ return $data->UltimoCosto; })
                    ->addColumn('UltimaVenta', function($data){ return $data->UltimaVenta; })
                    ->addColumn('Utilidad1Marca', function($data){ return $data->Utilidad1Marca; })
                    ->addColumn('Utilidad2Marca', function($data){ return $data->Utilidad2Marca; })
                    ->addColumn('Utilidad3Marca', function($data){ return $data->Utilidad3Marca; })
                    ->addColumn('Utilidad4Marca', function($data){ return $data->Utilidad4Marca; })
                    ->addColumn('Utilidad5Marca', function($data){ return $data->Utilidad5Marca; })
                    ->setRowClass(function ($data) { return $data->Status == 'ALTA' ? '' : 'bg-orange'; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener codigo
    public function productos_buscar_codigo_en_tabla(Request $request){
        $existecodigo = Producto::where('Codigo', $request->codigo)->count();
        return response()->json($existecodigo);
    }
    //obtener claves productos
    public function productos_obtener_claves_productos(Request $request){
        if($request->ajax()){
            $data = ClaveProdServ::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveproducto(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener claves unidades
    public function productos_obtener_claves_unidades(Request $request){
        if($request->ajax()){
            $data = ClaveUnidad::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveunidad(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener marcas
    public function productos_obtener_marcas(Request $request){
        if($request->ajax()){
            $data = Marca::where('Status', 'ALTA')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmarca('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Utilidad1', function($data){
                        $utilidad1 = Helpers::convertirvalorcorrecto($data->Utilidad1);
                        return $utilidad1;
                    })
                    ->addColumn('Utilidad2', function($data){
                        $utilidad2 = Helpers::convertirvalorcorrecto($data->Utilidad2);
                        return $utilidad2;
                    })
                    ->addColumn('Utilidad3', function($data){
                        $utilidad3 = Helpers::convertirvalorcorrecto($data->Utilidad3);
                        return $utilidad3;
                    })
                    ->addColumn('Utilidad4', function($data){
                        $utilidad4 = Helpers::convertirvalorcorrecto($data->Utilidad4);
                        return $utilidad4;
                    })
                    ->addColumn('Utilidad5', function($data){
                        $utilidad5 = Helpers::convertirvalorcorrecto($data->Utilidad5);
                        return $utilidad5;
                    })
                    ->rawColumns(['operaciones','Utilidad1','Utilidad2','Utilidad3','Utilidad4','Utilidad5'])
                    ->make(true);
        }
    }
    //obtener lineas
    public function productos_obtener_lineas(Request $request){
        if($request->ajax()){
            $data = Linea::where('Status', 'ALTA')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarlinea('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener monedas
    public function productos_obtener_monedas(Request $request){
        if($request->ajax()){
            $data = Moneda::orderBy('Clave', 'ASC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmoneda(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    public function pruebas(){
        return view ('pruebas.pruebas');

    }
    //obtener claves productos
    public function productos_obtener_utilidades(Request $request){
        if($request->ajax()){
            $costo = Helpers::convertirvalorcorrecto($request->costo);
            $impuesto = Helpers::convertirvalorcorrecto($request->impuesto);
            $marca = Marca::select('Utilidad1', 'Utilidad2', 'Utilidad3', 'Utilidad4', 'Utilidad5')->where('Numero', $request->numeromarca)->get();
            $utilidades = array();
            $utilidades[] = array("utilidad" => Helpers::convertirvalorcorrecto($marca[0]->Utilidad1), "costo" => $costo, "impuesto" => $impuesto);
            $utilidades[] = array("utilidad" => Helpers::convertirvalorcorrecto($marca[0]->Utilidad2), "costo" => $costo, "impuesto" => $impuesto);
            $utilidades[] = array("utilidad" => Helpers::convertirvalorcorrecto($marca[0]->Utilidad3), "costo" => $costo, "impuesto" => $impuesto);
            $utilidades[] = array("utilidad" => Helpers::convertirvalorcorrecto($marca[0]->Utilidad4), "costo" => $costo, "impuesto" => $impuesto);
            $utilidades[] = array("utilidad" => Helpers::convertirvalorcorrecto($marca[0]->Utilidad5), "costo" => $costo, "impuesto" => $impuesto);
            $filasutilidadesproducto = '';
                foreach($utilidades as $utilidad){
                    if($this->tipodeutilidad == 'Financiera'){
                        //$subtotalpesos = $utilidad["costo"]/(((100 - $utilidad["utilidad"]/Helpers::convertirvalorcorrecto(100)) / 100));
                        $restautilidad = Helpers::convertirvalorcorrecto(100) - $utilidad["utilidad"];
                        $divisionutilidad = $restautilidad / Helpers::convertirvalorcorrecto(100);
                        $subtotalpesos = $utilidad["costo"] / $divisionutilidad;
                    }else{
                        //$nuevosubtotalproducto = $request->preciopartida [$key]*(1+($Producto->Utilidad/100));
                        //$multiplicacionsubtotalpesos = $utilidad["costo"]*($utilidad["utilidad"]/Helpers::convertirvalorcorrecto(100));
                        //$subtotalpesos = $utilidad["costo"]+$multiplicacionsubtotalpesos;
                        $sumautilidad = Helpers::convertirvalorcorrecto(100) + $utilidad["utilidad"];
                        $divisionutilidad = $sumautilidad / Helpers::convertirvalorcorrecto(100);
                        $subtotalpesos = $utilidad["costo"] * $divisionutilidad;
                    }
                    $utilidadpesos = $subtotalpesos - $utilidad["costo"];
                    $ivapesos = $subtotalpesos*($utilidad["impuesto"]/Helpers::convertirvalorcorrecto(100));
                    $totalpesos = $subtotalpesos + $ivapesos;
                    /*
                    $utilidadrestante = Helpers::convertirvalorcorrecto(100) - $utilidad["utilidad"];
                    $subtotalpesos = $utilidad["costo"] / ($utilidadrestante/100);
                    $utilidadpesos = $subtotalpesos - $utilidad["costo"];
                    $subtotalpesos = $utilidad["costo"] / ($utilidadrestante/100);
                    $ivapesos = $subtotalpesos * ($utilidad["impuesto"]/100);
                    $totalpesos = $subtotalpesos + $ivapesos;
                    */
                    $filasutilidadesproducto= $filasutilidadesproducto.
                    '<tr>'.
                        '<td>'.Helpers::convertirvalorcorrecto($utilidad["costo"]).'</td>'.
                        '<td>'.Helpers::convertirvalorcorrecto($utilidad["utilidad"]).'</td>'.
                        '<td>'.Helpers::convertirvalorcorrecto($utilidadpesos).'</td>'.
                        '<td>'.Helpers::convertirvalorcorrecto($subtotalpesos).'</td>'.
                        '<td>'.Helpers::convertirvalorcorrecto($ivapesos).'</td>'.
                        '<td>'.Helpers::convertirvalorcorrecto($totalpesos).'</td>'.
                    '</tr>';
                }
            return response()->json($filasutilidadesproducto);
        }
    }
    //obtener existencias en almacenes
    public function productos_obtener_existencias_almacenes(Request $request){
        $codigo = $request->codigo;/*
        $existencias = DB::select("SELECT a.Numero AS Numero, a.Nombre AS Nombre, e.Existencias AS Existencias,
                                    (SELECT TOP 1 Ubicacion FROM [Productos Ubicaciones] WHERE almacen = a.Numero and Codigo = '".$codigo."') AS Ubicacion
                                FROM Almacenes AS a
                                left join Existencias AS e on (e.Almacen = a.Numero and e.Codigo = '".$codigo."')");*/
        $almacenes = Almacen::where('Status', 'ALTA')->get();
        $filasexistenciasalmacen = '';
        foreach($almacenes as $almacen){
            $existenciacontador = Existencia::where('Codigo', $codigo)->where('Almacen', $almacen->Numero)->count();
            $existencia = Existencia::where('Codigo', $codigo)->where('Almacen', $almacen->Numero)->first();
            if($existenciacontador > 0){
                $existenciaproducto = Helpers::convertirvalorcorrecto($existencia->Existencias);
            }else{
                $existenciaproducto = Helpers::convertirvalorcorrecto(0);
            }
            $filasexistenciasalmacen= $filasexistenciasalmacen.
            '<tr>'.
                '<td>'.$almacen->Numero.'</td>'.
                '<td>'.$almacen->Nombre.'</td>'.
                '<td>'.$existenciaproducto.'</td>'.
            '</tr>';
        }
        return response()->json($filasexistenciasalmacen);
    }
    //obtener clientes
    public function productos_obtener_clientes(Request $request){
        if($request->ajax()){
            $numeroabuscar = $request->numeroabuscar;
            $data = Cliente::where('Status', 'ALTA')->where('Numero', 'like', '%' . $numeroabuscar . '%')->orderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilacliente('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener datos de cliente para agregar a la afila
    public function productos_obtener_datos_cliente_agregar_fila(Request $request){
        $numero = '';
        $nombre = '';
        $existecliente = Cliente::where('Numero', $request->numeroabuscar)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numeroabuscar)->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
        }
        $data = array(
            'existecliente' => $existecliente,
            'numero' => $numero,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener clave producto por clave
    public function productos_obtener_clave_producto_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeclaveproducto = ClaveProdServ::where('Clave', $request->claveproducto)->count();
        if($existeclaveproducto > 0){
            $claveproducto = ClaveProdServ::where('Clave', $request->claveproducto)->first();
            $clave = $claveproducto->Clave;
            $nombre = $claveproducto->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener clave unidad por clave
    public function productos_obtener_clave_unidad_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeclaveunidad = ClaveUnidad::where('Clave', $request->claveunidad)->count();
        if($existeclaveunidad > 0){
            $claveunidad = ClaveUnidad::where('Clave', $request->claveunidad)->first();
            $clave = $claveunidad->Clave;
            $nombre = $claveunidad->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener marca por numero
    public function productos_obtener_marca_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existemarca = Marca::where('Numero', $request->marca)->count();
        if($existemarca > 0){
            $marca = Marca::where('Numero', $request->marca)->first();
            $numero = $marca->Numero;
            $nombre = $marca->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener linea por numero
    public function productos_obtener_linea_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existelinea = Linea::where('Numero', $request->linea)->count();
        if($existelinea > 0){
            $linea = Linea::where('Numero', $request->linea)->first();
            $numero = $linea->Numero;
            $nombre = $linea->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener modenas por clave
    public function productos_obtener_moneda_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existemoneda = Moneda::where('Clave', $request->moneda)->count();
        if($existemoneda > 0){
            $moneda = Moneda::where('Clave', $request->moneda)->first();
            $clave = $moneda->Clave;
            $nombre = $moneda->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener tipos prod
    public function productos_obtener_tipos_prod(){
        $tipos_ordenes_compra = TipoOrdenCompra::where('Nombre', 'GASTOS')->orWhere('Nombre', 'TOT')->get();
        $select_tipos_ordenes_compra = "<option value='REFACCION'>REFACCION</option>";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }

    //obtener productos consumos
    public function productos_obtener_productos_consumos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')->where('Pt', '<>', 'S');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaconsumos(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad.'\',\''.$data->Inventariable.'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Venta).'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Existencias', function($data){
                        $existencias = Helpers::convertirvalorcorrecto($data->Existencias);
                        return $existencias;
                    })
                    ->addColumn('Costo', function($data){
                        $costo = Helpers::convertirvalorcorrecto($data->Costo);
                        return $costo;
                    })
                    ->addColumn('SubTotal', function($data){
                        $subtotal = Helpers::convertirvalorcorrecto($data->SubTotal);
                        return $subtotal;
                    })
                    ->rawColumns(['operaciones', 'Existencias', 'Costo', 'SubTotal'])
                    ->make(true);
        }
    }
    //obtener datos de cliente para agregar a la afila
    public function productos_obtener_datos_producto_agregar_fila(Request $request){
        $codigo = '';
        $nombreproducto = '';
        $unidad = '';
        $inventariable = '';
        $costo = '';
        $venta = '';
        $existeproducto = Producto::where('Codigo', $request->codigoabuscar)->where('Status', 'ALTA')->count();
        if($existeproducto > 0){
            $producto = Producto::where('Codigo', $request->codigoabuscar)->where('Status', 'ALTA')->first();
            $codigo = $producto->Codigo;
            $nombreproducto = htmlspecialchars($producto->Producto, ENT_QUOTES);
            $unidad = $producto->Unidad;
            $inventariable = $producto->Inventariable;
            $costo = Helpers::convertirvalorcorrecto($producto->Costo);
            $venta = Helpers::convertirvalorcorrecto($producto->Venta);
        }
        $data = array(
            'existeproducto' => $existeproducto,
            'codigo' => $codigo,
            'nombreproducto' => $nombreproducto,
            'unidad' => $unidad,
            'inventariable' => $inventariable,
            'costo' => $costo,
            'venta' => $venta
        );
        return response()->json($data);
    }

    //guardar en catalogo
    public function productos_guardar(Request $request){
        $codigo=$request->codigo;
	    $ExisteProducto = Producto::where('Codigo', $codigo )->first();
	    if($ExisteProducto == true){
	        $Producto = 1;
	    }else{
            $marca = Marca::where('Numero', $request->marca)->first();
            $utilidadrestante = Helpers::convertirvalorcorrecto(100) - $marca->Utilidad1;
            $subtotalpesos = $request->costo / ($utilidadrestante/100);
            $utilidadpesos = $subtotalpesos - $request->costo;
            $ivapesos = $subtotalpesos * ($request->impuesto/100);
            $totalpesos = $subtotalpesos + $ivapesos;
            $Producto = new Producto;
            $Producto->Codigo=$request->codigo;
            $Producto->ClaveProducto=$request->claveproducto;
            $Producto->ClaveUnidad=$request->claveunidad;
            $Producto->Producto=$request->producto;
            $Producto->Unidad=$request->unidad;
            $Producto->Marca=$request->marca;
            $Producto->Linea=$request->linea;
            $Producto->Impuesto=$request->impuesto;
            $Producto->Costo=$request->costo;
            $Producto->Precio=$request->precio;
            $Producto->Utilidad=$marca->Utilidad1;
            $Producto->SubTotal=$subtotalpesos;
            $Producto->Iva=$ivapesos;
            $Producto->Total=$totalpesos;
            $Producto->Ubicacion=$request->ubicacion;
            $Producto->TipoProd = $request->tipo;
            $Producto->Codigo1=$request->codigo1;
            $Producto->Codigo2=$request->codigo2;
            $Producto->Codigo3=$request->codigo3;
            $Producto->Codigo4=$request->codigo4;
            $Producto->Codigo5=$request->codigo5;
            $Producto->Status='ALTA';
            $Producto->CostoDeLista=$request->costo;
            $Producto->Moneda='MXN';
            $Producto->CostoDeVenta=$request->costo;
            $Producto->Precio1=$request->precio;
            Log::channel('producto')->info('Se registro un nuevo producto: '.$Producto.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
            $Producto->save();
        }
        return response()->json($Producto);
    }
    //dar de baja o alta en catalogo
    public function productos_alta_o_baja(Request $request){
        $codigoproducto=$request->codigoproducto;
	    $Producto = Producto::where('Codigo', $codigoproducto )->first();
	    if($Producto->Status == 'ALTA'){
           Producto::where('Codigo', $codigoproducto)
           ->update([
               'Status' => 'BAJA'
           ]);
           Log::channel('producto')->info('El producto fue dado de baja: '.$Producto.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }else{
            Producto::where('Codigo', $codigoproducto)
                ->update([
                    'Status' => 'ALTA'
                ]);
            Log::channel('producto')->info('El producto fue dado de alta: '.$Producto.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }
	    return response()->json($Producto);
    }
    //obtener datos del catalogo
    public function productos_obtener_producto(Request $request){
        $codigoproducto = htmlspecialchars_decode($request->codigoproducto,ENT_QUOTES);
        $producto = Producto::where('Codigo', $codigoproducto)->first();
        $barcode = DNS1D::getBarcodeSVG($request->codigoproducto, 'C128', 1,55,'black', true);
        //$barcode = DNS2D::getBarcodeSVG($request->codigoproducto, 'QRCODE', 2, 2, true);
        $valores_producto = Producto::where('Codigo', $codigoproducto)->first();
        $marca = Marca::where('Numero', $producto->Marca)->first();
        $linea = Linea::where('Numero', $producto->Linea)->first();
        $precio = Helpers::convertirvalorcorrecto($producto->Precio);
        $costo = Helpers::convertirvalorcorrecto($producto->Costo);
        $impuesto = Helpers::convertirvalorcorrecto($producto->Impuesto);
        $fechaultimaventa = Helpers::formatoinputdate($producto->{'Fecha Ultima Venta'});
        $fechaultimacompra = Helpers::formatoinputdate($producto->{'Fecha Ultima Compra'});
        $ultimaventa = Helpers::convertirvalorcorrecto($producto->{'Ultima Venta'});
        $ultimocosto = Helpers::convertirvalorcorrecto($producto->{'Ultimo Costo'});
        $comision = Helpers::convertirvalorcorrecto($producto->Comision);
        $descuento = Helpers::convertirvalorcorrecto($producto->Descuento);
        $minimos = Helpers::convertirvalorcorrecto($producto->Min);
        $maximos = Helpers::convertirvalorcorrecto($producto->Max);
        $costomaximo = Helpers::convertirvalorcorrecto($producto->CostoMaximo);
        $costodelista = Helpers::convertirvalorcorrecto($producto->CostoDeLista);
        $lpafechacreacion = Helpers::formatoinputdate($producto->Lpa1FechaCreacion);
        $lpafechaultimaventa = Helpers::formatoinputdate($producto->Lpa1FechaUltimaVenta);
        $lpafechaultimacompra = Helpers::formatoinputdate($producto->Lpa1FechaUltimaCompra);
        //precios clientes
        $preciosclientes = ProductoPrecio::where('Codigo', $request->codigoproducto)->get();
        $numerofilasprecioscliente = ProductoPrecio::where('Codigo', $request->codigoproducto)->count();
        if($numerofilasprecioscliente > 0){
            $filaspreciosclientes = '';
            $contadorpreciosclientes = 0;
            foreach($preciosclientes as $pc){
                $c = Cliente::where('Numero', $pc->Cliente)->first();
                $filaspreciosclientes= $filaspreciosclientes.
                '<tr class="filaspreciosclientes" id="filapreciocliente'.$contadorpreciosclientes.'">'.
                    '<td><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosclientes('.$contadorpreciosclientes.')">X</div></td>'.
                    '<td><input type="hidden" name="numerocliente[]"  value="'.$pc->Cliente.'" readonly>'.$pc->Cliente.'</td>'.
                    '<td><input type="hidden" name="nombrecliente[]" value="'.$c->Nombre.'" readonly>'.$c->Nombre.'</td>'.
                    '<td><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" name="precioproductocliente[]" required value="'.Helpers::convertirvalorcorrecto($pc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);"></td>'.
                '</tr>';
                $contadorpreciosclientes++;
            }
        }else{
            $filaspreciosclientes = '';
        }
        //consumos
        $consumos = ProductoConsumo::where('Codigo', $request->codigoproducto)->get();
        $numerofilasconsumos = ProductoConsumo::where('Codigo', $request->codigoproducto)->count();
        if($numerofilasconsumos > 0){
            $filasconsumos = '';
            $contadorconsumos = 0;
            foreach($consumos as $c){
                $producto = Producto::where('Codigo', $c->Equivale)->first();
                $filasconsumos= $filasconsumos.
                '<tr class="filasconsumos" id="filaconsumo'.$contadorconsumos.'">'.
                    '<td><div class="btn btn-danger btn-xs" onclick="eliminarfilaconsumos('.$contadorconsumos.')">X</div></td>'.
                    '<td><input type="hidden" name="codigoproductoconsumos[]" value="'.$c->Equivale.'" readonly>'.$c->Equivale.'</td>'.
                    '<td><input type="hidden" name="productoconsumos[]"  value="'.$producto->Producto.'" readonly>'.$producto->Producto.'</td>'.
                    '<td><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" name="cantidadproductoconsumos[]" required value="'.Helpers::convertirvalorcorrecto($c->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);"></td>'.
                    '<td><input type="hidden" name="unidadconsumos[]"  value="'.$producto->Unidad.'" readonly>'.$producto->Unidad.'</td>'.
                    '<td><input type="hidden" name="inventariableconsumos[]"  value="'.$producto->Inventariable.'" readonly>'.$producto->Inventariable.'</td>'.
                    '<td><input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" name="costoconsumos[]" required value="'.Helpers::convertirvalorcorrecto($producto->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);">'.Helpers::convertirvalorcorrecto($producto->Costo).'</td>'.
                    '<td><input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" name="precionetoconsumos[]" required value="'.Helpers::convertirvalorcorrecto($producto->Venta).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);">'.Helpers::convertirvalorcorrecto($producto->Precio).'</td>'.

                '</tr>';
                $contadorconsumos++;
            }
        }else{
            $filasconsumos = '';
        }
        $data = array(
            "producto" => $producto,
            "barcode" => $barcode,
            "pt" => $valores_producto->Pt,
            "valores_producto" => $valores_producto,
            "marca" => $marca,
            "linea" => $linea,
            "precio" => $precio,
            "costo" => $costo,
            "impuesto" => $impuesto,
            "fechaultimaventa" => $fechaultimaventa,
            "fechaultimacompra" => $fechaultimacompra,
            "ultimaventa" => $ultimaventa,
            "ultimocosto" => $ultimocosto,
            "comision" => $comision,
            "descuento" => $descuento,
            "minimos" => $minimos,
            "maximos" => $maximos,
            "costomaximo" => $costomaximo,
            "costodelista" => $costodelista,
            "lpafechacreacion" => $lpafechacreacion,
            "lpafechaultimaventa" => $lpafechaultimaventa,
            "lpafechaultimacompra" => $lpafechaultimacompra,
            "filaspreciosclientes" => $filaspreciosclientes,
            "numerofilasprecioscliente" => $numerofilasprecioscliente,
            "filasconsumos" => $filasconsumos,
            "numerofilasconsumos" => $numerofilasconsumos
        );
        return response()->json($data);
    }
    //validar si existe codigo
    public function productos_validar_si_existe_codigo(Request $request){
        $resultado = Producto::where('Codigo', $request->valorgenerarcodigobarras)->count();
        $producto = '';
        if($resultado > 0){
            $producto = DNS1D::getBarcodeSVG($request->valorgenerarcodigobarras, 'C128', 1,55,'black', true);
        }
        $data = array(
            'resultado' => $resultado,
            'producto' => $producto
        );
        return response()->json($data);
    }
    //generar codigos de barras de todos el catalogo
    public function productos_generar_codigos_barras_catalogo(Request $request){
        $tamanoetiquetas = $request->tamanoetiquetascatalogocodigosbarras;
        $tipoprod = $request->tipoprodcodigosbarras;
        $status = $request->statuscodigosbarras;
        $ubicaciones = $request->codigobarrasubicacion;
        $codigos = array();
        $productos = Producto::select('Codigo','Producto','Ubicacion')
        ->where(function($q) use ($tipoprod) {
            if($tipoprod != "TODOS"){
                $q->where('TipoProd', $tipoprod);
            }
        })
        ->where(function($q) use ($status) {
            if($status != "TODOS"){
                $q->where('Status', $status);
            }
        })
        ->where(function($q) use ($ubicaciones) {
            if($ubicaciones != null){
                $q->whereIn('Ubicacion', $ubicaciones);
            }
        })
        ->get();
        if($request->generarcodigosdebarrasporexistencias == 1){
            foreach($productos as $p){
                $contarexistencias = Existencia::where('Codigo', $p->Codigo)->where('Almacen', 1)->count();
                if($contarexistencias > 0){
                    $existencias = Existencia::where('Codigo', $p->Codigo)->where('Almacen', 1)->first();
                    $existencia = $existencias->Existencias;
                }else{
                    $existencia = 0;
                }
                $datos = array(
                    'codigo' => $p->Codigo,
                    'producto' => $p->Producto,
                    'ubicacion' => $p->Ubicacion,
                    'existencia' => $existencia
                );
                array_push($codigos, $datos);
            }
        }else{
            foreach($productos as $p){
                $datos = array(
                    'codigo' => $p->Codigo,
                    'producto' => $p->Producto,
                    'ubicacion' => $p->Ubicacion,
                    'existencia' => 1
                );
                array_push($codigos, $datos);
            }
        }
        //dd($codigos);
        $tipo = 1;
        if($tamanoetiquetas == 'chica'){
            $pdf = PDF::loadView('catalogos.productos.formato_pdf_codigos_de_barras', compact('codigos','tipo','tamanoetiquetas'))
            ->setPaper('Letter')
            ->setOption('margin-left', 5)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 9)
            ->setOption('margin-top', 15);
        }else{
            $pdf = PDF::loadView('catalogos.productos.formato_pdf_codigos_de_barras', compact('codigos','tipo','tamanoetiquetas'))
            ->setPaper('Letter')
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 1)
            ->setOption('margin-top', 12);
        }
        return $pdf->stream();
    }
    //generar codigos de barras por array
    public function productos_generar_codigos_barras_array(Request $request){
        $tamanoetiquetas = $request->tamanoetiquetasarraycodigosbarras;
        $stringcodigosparacodigosdebarras = $request->arraycodigosparacodigosdebarras;
        $eliminarcoma = substr($stringcodigosparacodigosdebarras, 1);
        $codigosexplode = explode(",", $eliminarcoma);
        $codigos = array();
        foreach($codigosexplode  as $ce){
            $p = Producto::where('Codigo', $ce)->first();
            $datos = array(
                'codigo' => $p->Codigo,
                'producto' => $p->Producto,
                'ubicacion' => $p->Ubicacion
            );
            array_push($codigos, $datos);
        }
        $tipo = 2;
        if($tamanoetiquetas == 'chica'){
            $pdf = PDF::loadView('catalogos.productos.formato_pdf_codigos_de_barras', compact('codigos','tipo','tamanoetiquetas'))
            ->setPaper('Letter')
            ->setOption('margin-left', 5)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 9)
            ->setOption('margin-top', 15);
        }else{
            $pdf = PDF::loadView('catalogos.productos.formato_pdf_codigos_de_barras', compact('codigos','tipo','tamanoetiquetas'))
            ->setPaper('Letter')
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 1)
            ->setOption('margin-top', 12);
        }
        return $pdf->stream();
    }
    //generar codigo de barras por codigo desde modificiacion
    public function productos_generar_pdf_codigo_barras(Request $request){
        $numimpresiones = $request->numimpresiones;
        $tamanoetiquetas = $request->tamanoetiquetas;
        $codigos = array();
        $datos = array(
            'codigo' => $request->codigo,
            'producto' => $request->producto,
            'ubicacion' => $request->ubicacion
        );
        array_push($codigos, $datos);
        $tipo = 3;
        if($tamanoetiquetas == 'chica'){
            $pdf = PDF::loadView('catalogos.productos.formato_pdf_codigos_de_barras', compact('codigos','tipo','numimpresiones','tamanoetiquetas'))
            ->setPaper('Letter')
            ->setOption('margin-left', 5)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 9)
            ->setOption('margin-top', 15);
        }else{
            $pdf = PDF::loadView('catalogos.productos.formato_pdf_codigos_de_barras', compact('codigos','tipo','numimpresiones','tamanoetiquetas'))
            ->setPaper('Letter')
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 1)
            ->setOption('margin-top', 12);
        }
        return $pdf->stream();
    }
    //modificar en catalogo
    public function productos_guardar_modificacion(Request $request){

        $codigo= $request->codigo;
        $costodeventa = $request->costo;
        $marca = Marca::where('Numero', $request->marca)->first();
        $utilidadrestante = Helpers::convertirvalorcorrecto(100) - $marca->Utilidad1;
        $subtotalpesos = $request->costo / ($utilidadrestante/100);
        $utilidadpesos = $subtotalpesos - $request->costo;
        $ivapesos = $subtotalpesos * ($request->impuesto/100);
        $totalpesos = $subtotalpesos + $ivapesos;
        //imagen
        if($request->hasFile('imagen')){
            $destinationPath="imagenes_productos";
            $file = $request->imagen;
            $extension = $file->getClientOriginalName();
            $fileName = time().$extension;
            $file->move($destinationPath,$fileName);
            $img = $fileName;
            //eliminar imagen anterior
            $ImagenProducto = Producto::where('Codigo', htmlspecialchars_decode($codigo,ENT_QUOTES) )->first();
            if($ImagenProducto->Imagen == NULL){
                $url = public_path().'/imagenes_productos/'.'NULL';
            }else{
                $url = public_path().'/imagenes_productos/'.$ImagenProducto->Imagen;
            }
            if (file_exists($url)) {
                unlink($url);
            }
        }else{
            $ImagenProducto = Producto::where('Codigo', htmlspecialchars_decode($codigo,ENT_QUOTES) )->first();
            $img = $ImagenProducto->Imagen;
        }
        $Producto = Producto::where('Codigo', htmlspecialchars_decode($codigo,ENT_QUOTES) )->first();
        $original = $Producto->toArray();
        Producto::where('Codigo', htmlspecialchars_decode($codigo,ENT_QUOTES))
        ->update([
            //datos producto
            'ClaveProducto' => $request->claveproducto,
            'ClaveUnidad' => $request->claveunidad,
            'Producto' => $request->producto,
            'Unidad' => $request->unidad,
            //tabs producto
            'Marca' => $request->marca,
            'Linea' => $request->linea,
            'Impuesto' => $request->impuesto,
            'Costo' => $request->costo,
            'CostoDeVenta' => $costodeventa,
            'Utilidad' => $marca->Utilidad1,
            'SubTotal' => $subtotalpesos,
            'Iva' => $ivapesos,
            'Total' => $totalpesos,
            'Ubicacion' => $request->ubicacion,
            'TipoProd' => $request->tipo,
            'CostoDeLista' => $request->costodelista,
            'Moneda' => $request->moneda,
            //tabs codigos alternos
            'Codigo1' => $request->codigo1,
            'Codigo2' => $request->codigo2,
            'Codigo3' => $request->codigo3,
            'Codigo4' => $request->codigo4,
            'Codigo5' => $request->codigo5,
            //tabs consumo
            'Pt' => $request->consumosproductoterminado,
            //tabs fechas
            'Comision' => $request->fechascomision,
            'Descuento' => $request->fechasdescuento,
            'Min' => $request->fechasminimos,
            'Max' => $request->fechasmaximos,
            'CostoMaximo' => $request->fechascostomaximo,
            'Zona' => $request->fechaszonadeimpresion,
            'ProductoPeligroso' => $request->fechasproductopeligroso,
            'Supercedido' => $request->fechassupercedido,
            'Descripcion' => $request->fechasdescripcion,
            'Imagen' => $img
        ]);
        //solo si el usuario esta autorizado en modificar el dato insumo
        if (in_array(strtoupper(Auth::user()->user), explode(",",$this->usuariosamodificarinsumos))) {
            Producto::where('Codigo', $codigo)
                    ->update([
                        'Insumo'=>$request->fechasinsumo
                    ]);
        }
        Log::channel('producto')->info('Se modifico el producto: '.$Producto.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        //Tabla Productos Precios
        $eliminarpreciosproductos = ProductoPrecio::where('Codigo', $codigo)->forceDelete();
        if($request->numerofilasprecioscliente > 0){
            $contador = 1;
            foreach ($request->numerocliente as $key => $numerocliente){
        	    //alta tabla detalle productos precios
        	    $ProductoPrecio=new ProductoPrecio;
        	    $ProductoPrecio->Codigo = $codigo;
        	    $ProductoPrecio->Cliente = $numerocliente;
        	    $ProductoPrecio->Precio = $request->precioproductocliente [$key];
        	    $ProductoPrecio->Item = $contador;
                $ProductoPrecio->save();
                $contador++;
            }
        }
        //Tabla Productos Consumos
        $eliminarconsumos = ProductoConsumo::where('Codigo', $codigo)->forceDelete();
        if($request->numerofilasconsumosproductoterminado > 0){
            $contador = 1;
            foreach ($request->codigoproductoconsumos as $key => $codigoproductoconsumos){
        	    //alta tabla detalle productos precios
        	    $ProductoConsumo=new ProductoConsumo;
        	    $ProductoConsumo->Codigo = $codigo;
        	    $ProductoConsumo->Equivale = $codigoproductoconsumos;
        	    $ProductoConsumo->Cantidad = $request->cantidadproductoconsumos [$key];
        	    $ProductoConsumo->Item = $contador;
                $ProductoConsumo->save();
                $contador++;
            }
        }
        $registroProducto = Producto::where('Codigo', htmlspecialchars_decode($codigo,ENT_QUOTES) )->first();
        $descripcion = ["original"=>$original,"cambios"=>$registroProducto->toArray(), "request"=>$request->all()];
        $registro = new RegistrosAcciones;
        $registro->user_id = Auth::user()->id;
        $registro->accion = "Modificación Producto: ".$codigo;
        $registro->controlador = 'ProductoController';
        $registro->metodo = 'productos_guardar_modificacion';
        $registro->descripcion = json_encode($descripcion);
        $registro->save();
    	return response()->json($Producto);
    }

    //obtener kardex
    public function productos_obtener_kardex(Request $request){
        $ip = route('ver_movimiento_kardex');
        $almacenes = Almacen::where('Status', 'ALTA')->get();
        $selectalmacenes = "<option selected disabled hidden>Selecciona el almacén</option>";
        if($request->almacen == null){
            $almacen_kardex = 1;
        }else{
            $almacen_kardex = $request->almacen;
        }
        foreach($almacenes as $a){
            if($a->Numero == $almacen_kardex){
                $selectalmacenes = $selectalmacenes.'<option value='.$a->Numero.' Selected>'.$a->Nombre.'</option>';
            }else{
                $selectalmacenes = $selectalmacenes.'<option value='.$a->Numero.'>'.$a->Nombre.'</option>';
            }
        }
        $kardex = DB::select('exec ObtenerKardex ?,?', array($request->codigo,$almacen_kardex));
        $nummovimiento = 1;
        $entradas = 0;
        $salidas = 0;
        $existencias = 0;
        $numerodecimalesconfigurados = config('app.numerodedecimales');
        $data = array();
        foreach(array_reverse($kardex) as $k){
            $entradas = $entradas + $k->Entradas;
            $salidas = $salidas + $k->Salidas;
            $existencias = $existencias + $k->Entradas - $k->Salidas;
            $colorfila = '';
            if($k->Status == 'BAJA'){
                $colorfila = 'bg-red';
            }
            $data[]=array(
                "colorfila"=>$colorfila,
                "nummovimiento"=>$nummovimiento,
                "documento"=>$k->Documento,
                "movimiento"=>$k->Movimiento,
                "fecha"=>Helpers::fecha_espanol($k->Fecha),
                "almacen" => Helpers::convertirvalorcorrecto($k->Almacen),
                "entradas"=> Helpers::convertirvalorcorrecto($k->Entradas),
                "salidas" => Helpers::convertirvalorcorrecto($k->Salidas),
                "existencias"=> round($existencias, $numerodecimalesconfigurados),
                "costo"=>Helpers::convertirvalorcorrecto($k->Costo),
                "status"=>$k->Status
            );
            $nummovimiento++;
        }
        $filasmovimientos = "";
        $primerfila = 0;
        foreach(array_reverse($data) as $d){
            if($primerfila == 0){
                $colorfilaex = 'bg-amber font-bold col-pink';
            }else{
                $colorfilaex = '';
            }
            $filasmovimientos= $filasmovimientos.
            '<tr class="'.$d['colorfila'].'">'.
                '<td><b>'.$d['nummovimiento'].'</b></td>'.
                '<td>'.$d['documento'].'</td>'.
                //'<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerkardex(\''. htmlspecialchars($data->Codigo , ENT_QUOTES) .'\','.$data->Almacen.')">Ver Movimientos</a></li>'.
                '<td><a class="paddingmenuopciones" href="javascript:void(0);" onclick="mostrarMovimiento(\''.$d['documento'].'\',\''.$d['movimiento'].'\',\''.htmlspecialchars($request->codigo , ENT_QUOTES).'\')">'.$d['movimiento'].'</a></td>'.
                '<td>'.$d['fecha'].'</td>'.
                '<td>'.$d['almacen'].'</td>'.
                '<td>'.$d['entradas'].'</td>'.
                '<td>'.$d['salidas'].'</td>'.
                '<td class="'.$colorfilaex.'">'.$d['existencias'].'</td>'.
                '<td>'.$d['costo'].'</td>'.
                '<td>'.$d['status'].'</td>'.
            '</tr>';
            $primerfila++;
        }
        $data = array(
            'filasmovimientos' => $filasmovimientos,
            'entradas' => Helpers::convertirvalorcorrecto($entradas),
            'salidas' => Helpers::convertirvalorcorrecto($salidas),
            'existencias' => Helpers::convertirvalorcorrecto($existencias),
            'selectalmacenes' => $selectalmacenes,
            'almacen_kardex' => $almacen_kardex
        );
        return response()->json($data);
    }

    //exportar a excel
    public function productos_exportar_excel(){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Productos', Auth::user()->id);
        return Excel::download(new ProductosExport($configuraciones_tabla['campos_consulta']), "productos.xlsx");
    }
    //guardar cambios en confguraxion de la tabla
    public function productos_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Productos', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'Productos')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='Productos';
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
        return redirect()->route('productos');
    }

    public function productos_download_excel_kardex(Request $request){
        return Excel::download(new KardexProductoExport($request->codigo,$request->almacen),'kardex_'.$request->codigo.'.xlsx');
    }
    public function ver_movimiento_kardex(Request $request){
        $documento  = $request->documento;
        $numero = $request->numero;
        $codigo = $request->codigo;
        $provedor = '';
        $almacen = '';
        $almacen2 = '';
        $cliente = '';
        $agente = '';
        $orden = null;
        $ordenStatus = '';
        switch ($documento) {
            case 'Ajustes':
                $movimiento = AjusteInventario::where('Ajuste',$numero)
                ->first();
                $provedor = NULL;
                $almacen = Almacen::where('Numero',$movimiento->Almacen)->select('Nombre')->first();
                $almacen2 = null;
                $cliente = null;
                $agente = null;
                $orden = null;
                $ordenStatus = '';
                break;
            case 'Compras':
                $movimiento = Compra::where('Compra',$numero)
                ->first();
                $provedor = Proveedor::where('Numero',$movimiento->Proveedor)->select('Nombre')->first();
                $almacen = Almacen::where('Numero',$movimiento->Almacen)->select('Nombre')->first();
                $almacen2 = null;
                $cliente = null;
                $agente = null;
                $orden = null;
                $ordenStatus = '';
                break;
            case 'Remisiones':
                $movimiento = Remision::where('Remision', $numero)
                ->first();
                $almacen = Almacen::where('Numero',$movimiento->De)->select('Nombre')->first();
                $almacen2 = null;
                $cliente = Cliente::where('Numero',$movimiento->Cliente)->select('Nombre')->first();
                $provedor = null;
                $agente = Agente::where('Numero',$movimiento->Agente)->select('Nombre')->first();
                $orden = null;
                $ordenStatus = '';
                break;
            case 'Traspasos':
                $movimiento = Traspaso::where('Traspaso', $numero)
                ->first();
                $almacen = Almacen::where('Numero',$movimiento->De)->select('Nombre')->first();
                $almacen2 = Almacen::where('Numero',$movimiento->A)->select('Nombre')->first();
                $provedor = null;
                $agente = null;
                $orden = OrdenTrabajo::where('Orden',$movimiento->Orden)->select('Orden','Status','Cliente')->first();
                $cliente = (isset($orden) ? Cliente::where('Numero',$orden->Cliente)->select('Nombre')->first() : NULL);
                break;
            case 'NC Proveedor':
                $movimiento = NotaProveedor::where('Nota',$numero)->with(['detalles','documentos'])
                ->first();
                $almacen = Almacen::where('Numero',$movimiento->Almacen)->select('Nombre')->first();
                $almacen2 = NULL;
                $provedor = Proveedor::where('Numero',$movimiento->Proveedor)->select('Nombre')->first();
                $cliente = NULL;
                $agente = null;
                $orden = null;
                $ordenStatus = '';
                break;
            default:
                $movimiento = collect([
                    "detalles" => collect([])
                ]);
                $provedor = null;
                $almacen =  null;
                $almacen2 = null;
                $cliente = null;
                $agente = null;
                $orden = null;
                $ordenStatus = '';
                break;
        }
        $referencia = '';
        $filasmovimiento = '';
        $filasDescuentos='';
        switch ($documento) {
            case 'Ajustes':
                foreach ($movimiento->detalles as $detalle) {
                    $filasmovimiento .= '<tr class="filasproductos">'.
                        '<td class="tdmod"><b style="font-size:12px;">'.$detalle->Codigo.'</b></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$detalle->Descripcion.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;" class="codigopartidatexto">'.$detalle->Descripcion.'</b></td>'.
                        '<td class="tdmod">'.$detalle->Unidad.'</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Existencias),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Entradas),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Salidas),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Real),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Real),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm" value="'.$movimiento->Usuario.'"><td>'.
                    '</tr>';
                }
                break;

            case 'Consumo Produccion':
                foreach ($movimiento->detalles as $detalle) {
                    $filasmovimiento .= '<tr class="filasproductos">'.
                        '<td class="tdmod"><b style="font-size:12px;">'.$detalle->Codigo.'</b></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$detalle->Descripcion.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;" class="codigopartidatexto">'.$detalle->Descripcion.'</b></td>'.
                        '<td class="tdmod">'.$detalle->Unidad.'</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Cantidad),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Merma),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Consumo),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Costo),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Total),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm" value="'.$movimiento->Usuario.'"><td>'.
                    '</tr>';
                }
                break;
            case 'NC Proveedor':
                //Codigos
                foreach ($movimiento->detalles as $detalle) {
                    $producto = "";
                    $Existencia = 0;
                    if($movimiento->Almacen != 0){
                        $Existencia = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $movimiento->Almacen)->first();
                        $producto = Producto::where('Codigo', $detalle->Codigo)->first();
                    }
                    //$parsleymax = $detalle->Cantidad;
                    //$cantidadpartidadetalleordencompra = OrdenCompraDetalle::where('Orden', $compra->Orden)->where('Codigo', $detalle->Codigo)->first();
                    $claveproductopartida = ClaveProdServ::where('Clave', $detalle->ClaveProducto)->first();
                    $claveunidadpartida = ClaveUnidad::where('Clave', $detalle->ClaveUnidad)->first();
                    $claveproducto = $claveproductopartida ? $claveproductopartida->Clave : '';
                    $nombreclaveproducto = $claveproductopartida ? $claveproductopartida->Nombre : '';
                    $claveunidad = $claveunidadpartida ? $claveunidadpartida->Clave : '';
                    $nombreclaveunidad = $claveunidadpartida ? $claveunidadpartida->Nombre : '';
                    //importante porque si se quiere hacer una divison con 0 marca ERROR
                    $porcentajeieps = 0;
                    $porcentajeretencioniva = 0;
                    $porcentajeretencionisr = 0;
                    $porcentajeretencionieps = 0;
                    if($detalle->Ieps > 0){
                        $porcentajeieps = ($detalle->Ieps * 100) / $detalle->ImporteDescuento;
                    }
                    if($detalle->IvaRetencion > 0){
                        $porcentajeretencioniva = ($detalle->IvaRetencion * 100) / $detalle->SubTotal;
                    }
                    if($detalle->IsrRetencion > 0){
                        $porcentajeretencionisr = ($detalle->IsrRetencion * 100) / $detalle->SubTotal;
                    }
                    if($detalle->IepsRetencion > 0){
                        $porcentajeretencionieps = ($detalle->IepsRetencion * 100) / $detalle->SubTotal;
                    }
                    if($detalle->Codigo == 'DPPP'){
                        $filasmovimiento= $filasmovimiento.
                        '<tr class="filasproductos">'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$detalle->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$detalle->Codigo.'</b></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($detalle->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$detalle->Unidad.'" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Precio),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Importe),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Dcto).'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->ImporteDescuento),$this->numerodecimales,',','.').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($porcentajeieps),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Ieps),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->SubTotal),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Impuesto),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Iva),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($porcentajeretencioniva),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->IvaRetencion),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($porcentajeretencionisr),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->IsrRetencion),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($porcentajeretencionieps),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->IepsRetencion),$this->numerodecimales).'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Total),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->PrecioMoneda),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->DescuentoMoneda),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                        '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly data-parsley-length="[1, 20]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$nombreclaveproducto.'" readonly></td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                        '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly data-parsley-length="[1, 5]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly>'.
                            '</td>'.
                        '</tr>';
                    }else{
                        $filasmovimiento= $filasmovimiento.
                        '<tr class="filasproductos">'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$detalle->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$detalle->Codigo.'</b></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($detalle->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$detalle->Unidad.'" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Precio),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Importe),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Dcto).'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->ImporteDescuento),$this->numerodecimales,',','.').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($porcentajeieps),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Ieps),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->SubTotal),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Impuesto),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Iva),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($porcentajeretencioniva),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->IvaRetencion),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($porcentajeretencionisr),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->IsrRetencion),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($porcentajeretencionieps),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->IepsRetencion),$this->numerodecimales).'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Total),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->PrecioMoneda),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->DescuentoMoneda),$this->numerodecimales,'.',',').'" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                        '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly data-parsley-length="[1, 20]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$nombreclaveproducto.'" readonly></td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                        '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly data-parsley-length="[1, 5]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly>'.
                            '</td>'.
                        '</tr>';
                    }
                }

                //Descuentos
                foreach ($movimiento->documentos as $descuento) {

                    $filasDescuentos .= '<tr class="filasproductos">'.
                        '<td class="tdmod"><b style="font-size:12px;">'.$descuento->Compra.'</b></td>'.
                        '<td class="tdmod"><b style="font-size:12px;">'.$descuento->compra->Fecha.'</b></td>'.
                        '<td class="tdmod"><b style="font-size:12px;">'.$descuento->compra->Factura.'</b></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($descuento->Total),$this->numerodecimales,'.',',').'" readonly>'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($descuento->compra->Abonos),$this->numerodecimales,'.',',').'" readonly>'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($descuento->Descuento),$this->numerodecimales,'.',',').'" readonly>'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($descuento->compra->Saldo),$this->numerodecimales,'.',',').'" readonly>'.
                        '</td>'.
                    '</tr>';
                }
                break;
            default:
                foreach ($movimiento->detalles as $detalle) {
                    switch ($documento) {
                        case 'Ajustes':
                            $movimiento = AjusteInventario::where('Ajuste',$numero)
                            ->first();
                            break;
                        case 'Compras':
                            $referencia = (isset($detalle->Orden) ? $detalle->Orden : 'NA');
                            break;
                        case 'Remisiones':
                            $referencia = (isset($detalle->Orden) ? $detalle->Orden : 'NA');
                            break;
                        case 'Traspasos':
                            $referencia = (isset($detalle->Requisicion) ? $detalle->Requisicion : 'NA');

                        default:
                            $provedor = '';
                            break;
                    }
                    $filasmovimiento .= '<tr class="filasproductos">'.
                        '<td class="tdmod"><b style="font-size:12px;">'.$detalle->Codigo.'</b></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$detalle->Descripcion.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;" class="codigopartidatexto">'.$detalle->Descripcion.'</b></td>'.
                        '<td class="tdmod">'.$detalle->Unidad.'</td>'.
                        '<td class="tdmod">'.
                            Helpers::convertirvalorcorrecto($detalle->Cantidad).
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Precio),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Importe),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.Helpers::convertirvalorcorrecto($detalle->Dcto).'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Descuento), $this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'. number_format(Helpers::convertirvalorcorrecto($detalle->ImporteDescuento),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->SubTotal),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(($detalle->Impuesto/100),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format($detalle->Iva,$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.number_format(Helpers::convertirvalorcorrecto($detalle->Total),$this->numerodecimales,'.',',').'">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm" value="'.$referencia.'">'.
                        '</td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm" value="'.$movimiento->Usuario.'"><td>'.
                    '</tr>';
                }
                break;
        }
        $data = array(
            'filasmovimiento'=> $filasmovimiento,
            'filasDescuentos'=> $filasDescuentos,
            'movimiento'=> $movimiento,
            'documento'=> $documento,
            'proveedor' =>$provedor,
            'almacen' => (isset($almacen) ? $almacen->Nombre : ''),
            'almacen2' => (isset($almacen2) ? $almacen2->Nombre : ''),
            "cliente" => (isset($cliente) ? $cliente->Nombre : ''),
            'agente' => (isset($agente) ? $agente->Nombre : ''),
            'orden' =>  (isset($orden) ? $orden->Orden : ''),
            'ordenStatus' =>(isset($orden) ? $orden->Status : ''),
        );

        return response()->json($data);

    }
}
