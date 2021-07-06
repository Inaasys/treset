<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use SoapClient;
use Helpers;
use DB;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosExport;
use App\Producto;
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
use App\ContraReciboDetalle;
use GuzzleHttp\Client;
use PDF;
use Mail;

class ProductoController extends ConfiguracionSistemaController{

    public function __construct(){
        
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'Productos')->first();
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

    public function productos(){
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('productos_guardar_configuracion_tabla');
        return view('catalogos.productos.productos', compact('configuracion_tabla','rutaconfiguraciontabla'));
    }
    //obtener todos los registros
    public function productos_obtener(Request $request){
        if($request->ajax()){
            //$data = VistaProducto::select($this->campos_consulta)->get();
            $data = VistaProducto::select($this->campos_consulta);
            return DataTables::of($data)
                    ->order(function ($query) {
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
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Codigo .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Codigo .'\')">Bajas</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerkardex(\''.$data->Codigo .'\','.$data->Almacen.')">Ver Movimientos</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Existencias', function($data){ return $data->Existencias; })
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
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmoneda(\''.$data->Clave .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }        
    }    
    public function pruebas(){


        return view ('pruebas.pruebas');

        

/*
        $endpoint = config('app.endpointapicurrencylayer');
        $access_key = config('app.keyapicurrencylayer');
        define('VT_URL', 'http://api.currencylayer.com/'.$endpoint.'?access_key='.$access_key.'&currencies=MXN');
        //crear cliente Guzzle HTTP
        $cliente = new Client();
        //respuesta de API
        $respuesta = $cliente->request('GET', VT_URL, []);
        $resultado = json_decode($respuesta->getBody());
        //obtener valor del dolar
        $valor_dolar = $resultado->quotes->USDMXN;
        dd($valor_dolar);*/


		/*
		$nombre = 'Receptor envio de correos';
        $receptor = 'osbaldo.anzaldo@utpcamiones.com.mx';
        $correos = ['al221410832@gmail.com','marco.baltazar@utpcamiones.com.mx'];
        $name = "Receptor envio de correos";
        $body = "Nuevo respaldo de la base de datos";
        $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
        //$urlpublic = url("/");
        //$url = explode("public", $urlpublic);
        //$urlinventarioactual = $url[0]."storage/excel/exportaciones/inventarioactual.xlsx";
        //$urlreporteventas = $url[0]."storage/excel/exportaciones/reporteventas.xlsx";

        Mail::send('correos.respaldos.respaldos', compact('name', 'body', 'receptor', 'horaaccion'), function($message) use ($nombre, $receptor, $correos) {
            $message->to($receptor, $nombre)
                    ->cc($correos)
                    ->subject('Respaldo');
                    //->attach($urlinventarioactual)
                    //->attach($urlreporteventas);
        });*/
		

        /*$pdf = \PDF::loadView('pruebas.pruebas');
        $pdf->setOption('enable-javascript', true);
        $pdf->setOption('javascript-delay', 15000);
        $pdf->setOption('enable-smart-shrinking', true);
        $pdf->setOption('no-stop-slow-scripts', true);
        $pdf->setOption('lowquality', false);
        return $pdf->stream('graph.pdf');*/

/*
        $pdf = PDF::loadView('welcome');

        $pdf->setOption('enable-javascript', true);
        $pdf->setOption('javascript-delay', 1000);
        $pdf->setOption('no-stop-slow-scripts', true);
        $pdf->setOption('enable-smart-shrinking', true);
        return $pdf->download();*/

        /*$endpoint = config('app.endpointapicurrencylayer');
        $access_key = config('app.keyapicurrencylayer');
        define('VT_URL', 'http://api.currencylayer.com/'.$endpoint.'?access_key='.$access_key.'&currencies=MXN');
        //crear cliente Guzzle HTTP
        $cliente = new Client();
        //respuesta de API
        $respuesta = $cliente->request('GET', VT_URL, []);
        $resultado = json_decode($respuesta->getBody());
        //obtener valor del dolar
        $valor_dolar = $resultado->quotes->USDMXN;
        dd($valor_dolar);*/

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
                    $utilidadrestante = Helpers::convertirvalorcorrecto(100) - $utilidad["utilidad"];
                    $subtotalpesos = $utilidad["costo"] / ($utilidadrestante/100);
                    $utilidadpesos = $subtotalpesos - $utilidad["costo"];
                    $subtotalpesos = $utilidad["costo"] / ($utilidadrestante/100);
                    $ivapesos = $subtotalpesos * ($utilidad["impuesto"]/100);
                    $totalpesos = $subtotalpesos + $ivapesos;
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
    //obtener productos consumos
    public function productos_obtener_productos_consumos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $data = DB::table('Productos as t')
            ->leftJoin('Marcas as m', 'm.Numero', '=', 't.Marca')
            ->leftJoin(DB::raw("(select codigo, sum(existencias) as existencias from existencias group by codigo) as e"),
            function($join){
                $join->on("e.codigo","=","t.codigo");
            })
            ->select('t.Codigo as Codigo', 't.Producto as Producto', 't.Ubicacion as Ubicacion', 'e.Existencias as Existencias', 't.Costo as Costo', 't.SubTotal as SubTotal', 't.Marca as Marca', 't.Status as Status')
            ->where('t.Codigo', 'like', '%' . $codigoabuscar . '%')
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaconsumos(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\')">Seleccionar</div>';
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
    //guardar en catalogo
    public function productos_guardar(Request $request){
        $codigo=$request->codigo;
	    $ExisteProducto = Producto::where('Codigo', $codigo )->first();
	    if($ExisteProducto == true){
	        $Producto = 1;
	    }else{        
            $costodeventa = $request->costo;
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
            $Producto->CostoDeVenta=$costodeventa;
            $Producto->Utilidad=$marca->Utilidad1;
            $Producto->SubTotal=$subtotalpesos;
            $Producto->Iva=$ivapesos;
            $Producto->Total=$totalpesos;
            $Producto->Ubicacion=$request->ubicacion;
            $Producto->Codigo1=$request->codigo1;
            $Producto->Codigo2=$request->codigo2;
            $Producto->Codigo3=$request->codigo3;
            $Producto->Codigo4=$request->codigo4;
            $Producto->Codigo5=$request->codigo5;
            $Producto->Status='ALTA';
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
	       $Producto->Status = 'BAJA';
           Log::channel('producto')->info('El producto fue dado de baja: '.$Producto.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }else{
	       $Producto->Status = 'ALTA';
           Log::channel('producto')->info('El producto fue dado de alta: '.$Producto.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }
	    $Producto->save();
	    return response()->json($Producto);
    } 
    //obtener datos del catalogo
    public function productos_obtener_producto(Request $request){
        $producto = Producto::where('Codigo', $request->codigoproducto)->first();
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
                '</tr>';
                $contadorconsumos++;
            }
        }else{
            $filasconsumos = '';
        }
        $data = array(
            "producto" => $producto->toArray(),
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
        //modificar registro Tabla Productos
        $Producto = Producto::where('Codigo', $codigo )->first();
        //datos producto
        $Producto->ClaveProducto=$request->claveproducto;
        $Producto->ClaveUnidad=$request->claveunidad;
        $Producto->Producto=$request->producto;
        $Producto->Unidad=$request->unidad;
        //tabs producto
        $Producto->Marca=$request->marca;
        $Producto->Linea=$request->linea;
        $Producto->Impuesto=$request->impuesto;
        $Producto->Costo=$request->costo;
        $Producto->CostoDeVenta=$costodeventa;
        $Producto->Utilidad=$marca->Utilidad1;
        $Producto->SubTotal=$subtotalpesos;
        $Producto->Iva=$ivapesos;
        $Producto->Total=$totalpesos;
        $Producto->Ubicacion=$request->ubicacion;
        $Producto->CostoDeLista=$request->costodelista;
        $Producto->Moneda=$request->moneda;
        //tabs codigos alternos
        $Producto->Codigo1=$request->codigo1;
        $Producto->Codigo2=$request->codigo2;
        $Producto->Codigo3=$request->codigo3;
        $Producto->Codigo4=$request->codigo4;
        $Producto->Codigo5=$request->codigo5;   
        //tabs consumo
        $Producto->Pt=$request->consumosproductoterminado;
        //tabs fechas
        $Producto->Comision=$request->fechascomision;
        $Producto->Descuento=$request->fechasdescuento;
        $Producto->Min=$request->fechasminimos;
        $Producto->Max=$request->fechasmaximos;
        $Producto->CostoMaximo=$request->fechascostomaximo;
        $Producto->Zona=$request->fechaszonadeimpresion;
        $Producto->ProductoPeligroso=$request->fechasproductopeligroso;
        $Producto->Supercedido=$request->fechassupercedido;
        $Producto->Insumo=$request->fechasinsumo;
        $Producto->Descripcion=$request->fechasdescripcion;
        //tabs lpa
        $Producto->Lpa1Subir=$request->lpasubircodigo;
        $Producto->Lpa2Subir=$request->lpasubircodigo;
        $Producto->Lpa1FechaCreacion=$request->lpafechacreacion;
        $Producto->Lpa2FechaCreacion=$request->lpafechacreacion;
        $Producto->Lpa1FechaUltimaVenta=$request->lpafechaultimaventa;
        $Producto->Lpa2FechaUltimaVenta=$request->lpafechaultimaventa;
        $Producto->Lpa1FechaUltimaCompra=$request->lpafechaultimacompra;
        $Producto->Lpa2FechaUltimaCompra=$request->lpafechaultimacompra;
        $Producto->Lpa1Identificacion=$request->lpaidentificacion;
        $Producto->Lpa2Identificacion=$request->lpaidentificacion;
        $Producto->Lpa1Ubicacion=$request->lpaubicacion;
        $Producto->Lpa2Ubicacion=$request->lpaubicacion;
        $Producto->Lpa1CodigoCompra=$request->lpacodigocompra;
        $Producto->Lpa2CodigoCompra=$request->lpacodigocompra;
        Log::channel('producto')->info('Se modifico el producto: '.$Producto.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        $Producto->save();
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
    	return response()->json($Producto); 
    }

    //obtener kardex
    public function productos_obtener_kardex(Request $request){
        $almacenes = Almacen::where('Status', 'ALTA')->get();
        $selectalmacenes = "<option selected disabled hidden>Selecciona el almacén</option>";
        foreach($almacenes as $a){
            if($a->Numero == $request->almacen){
                $selectalmacenes = $selectalmacenes.'<option value='.$a->Numero.' Selected>'.$a->Nombre;
            }else{
                $selectalmacenes = $selectalmacenes.'<option value='.$a->Numero.'>'.$a->Nombre;
            }
        }
        $kardex = DB::select('exec ObtenerKardex ?,?', array($request->codigo,$request->almacen));
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
                '<td>'.$d['movimiento'].'</td>'.
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
        );
        return response()->json($data);
    }

    //exportar a excel
    public function productos_exportar_excel(){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new ProductosExport($this->campos_consulta), "productos.xlsx");
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
        Configuracion_Tabla::where('tabla', 'Productos')
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
        return redirect()->route('productos');
    }
}
