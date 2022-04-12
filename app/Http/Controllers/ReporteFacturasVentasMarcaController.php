<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Jenssegers\Date\Date;
use Helpers;
use DataTables;
use App\Configuracion_Tabla;
use App\Factura;
use App\FacturaDetalle;
use App\NotaCliente;
use App\NotaClienteDetalle;
use App\Cliente;
use App\Agente;
use App\Serie;
use App\Almacen;
use App\Marca;
use App\Linea;
use App\Producto;
use App\TipoOrdenCompra;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesFacturasVentasClientesExport;
use DB;

class ReporteFacturasVentasMarcaController  extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_facturas_ventas_marca(Request $request){
        $urlgenerarformatoexcel = route('reporte_facturas_ventas_marca_generar_formato_excel');
        return view('reportes.facturas.reportefacturasventasmarca', compact('urlgenerarformatoexcel'));
    }

    //obtener tipos ordenes de compra
    public function reporte_facturas_ventas_marca_obtener_tipos_ordenes_compra(Request $request){
        $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->get();
        $select_tipos_ordenes_compra = "<option value='TODOS' selected>TODOS</option>";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }
    //obtener clientes
    public function reporte_facturas_ventas_marca_obtener_clientes(Request $request){
        if($request->ajax()){
            $data = Cliente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener clientes por numero
    public function reporte_facturas_ventas_marca_obtener_cliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existecliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data);
    }
    //obtener agentes
    public function reporte_facturas_ventas_marca_obtener_agentes(Request $request){
        if($request->ajax()){
            $data = Agente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaragente('.$data->Numero.',\''.$data->Nombre.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener agente por numero
    public function reporte_facturas_ventas_marca_obtener_agente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existeagente = Agente::where('Numero', $request->numeroagente)->where('Status', 'ALTA')->count();
        if($existeagente > 0){
            $agente = Agente::where('Numero', $request->numeroagente)->where('Status', 'ALTA')->first();
            $numero = $agente->Numero;
            $nombre = $agente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data);
    }
    //obtener series
    public function reporte_facturas_ventas_marca_obtener_series(Request $request){
        if($request->ajax()){
            $data = Factura::select('Serie')->groupby('Serie')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarserie(\''.$data->Serie .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener serie por clave
    public function reporte_facturas_ventas_marca_obtener_serie_por_clave(Request $request){
        $claveserie = '';
        $existeserie = Factura::select('Serie')->where('Serie', $request->claveserie)->groupby('Serie')->count();
        if($existeserie > 0){
            $serie = Factura::select('Serie')->where('Serie', $request->claveserie)->groupby('Serie')->first();
            $claveserie = $serie->Serie;
        }
        $data = array(
            'claveserie' => $claveserie,
        );
        return response()->json($data);
    }
    
    //obtener alamcenes
    public function reporte_facturas_ventas_marca_obtener_almacenes(Request $request){
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
    //obtener almacen por numero
    public function reporte_facturas_ventas_marca_obtener_almacen_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existealmacen = Almacen::where('Numero', $request->numeroalmacen)->where('Status', 'ALTA')->count();
        if($existealmacen > 0){
            $almacen = Almacen::where('Numero', $request->numeroalmacen)->where('Status', 'ALTA')->first();
            $numero = $almacen->Numero;
            $nombre = $almacen->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data); 
    }
    //obtener marcas
    public function reporte_facturas_ventas_marca_obtener_marcas(Request $request){
        if($request->ajax()){
            $data = Marca::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmarca('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener marca por numero
    public function reporte_facturas_ventas_marca_obtener_marca_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existemarca = Marca::where('Numero', $request->numeromarca)->where('Status', 'ALTA')->count();
        if($existemarca > 0){
            $marca = Marca::where('Numero', $request->numeromarca)->where('Status', 'ALTA')->first();
            $numero = $marca->Numero;
            $nombre = $marca->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data);
        
    }
    //obtener lineas
    public function reporte_facturas_ventas_marca_obtener_lineas(Request $request){
        if($request->ajax()){
            $data = Linea::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarlinea('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
        
    }
    //obtener linea por numero
    public function reporte_facturas_ventas_marca_obtener_linea_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existelinea= Linea::where('Numero', $request->numerolinea)->where('Status', 'ALTA')->count();
        if($existelinea > 0){
            $linea = Linea::where('Numero', $request->numerolinea)->where('Status', 'ALTA')->first();
            $numero = $linea->Numero;
            $nombre = $linea->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data); 
    }
    //obtener productos
    public function reporte_facturas_ventas_marca_obtener_productos(Request $request){
        if($request->ajax()){
            $data = Producto::where('Status', 'ALTA')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarproducto(\''.$data->Codigo .'\',\''.$data->Producto .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
        
    }
    //obtener producto por cod
    public function reporte_facturas_ventas_marca_obtener_producto_por_codigo(Request $request){
        $codigo = '';
        $producto = '';
        $existeproducto= Producto::where('Codigo', $request->codigo)->where('Status', 'ALTA')->count();
        if($existeproducto > 0){
            $producto = Producto::where('Codigo', $request->codigo)->where('Status', 'ALTA')->first();
            $codigo = $producto->Codigo;
            $producto = $producto->Producto;
        }
        $data = array(
            'codigo' => $codigo,
            'producto' => $producto,
        );
        return response()->json($data); 
    }

    //generar reporte
    public function reporte_facturas_ventas_marca_generar_reporte(Request $request){
        
    }
}
