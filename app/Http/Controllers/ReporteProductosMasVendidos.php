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
use App\Almacen;
use App\Marca;
use App\Linea;
use App\Serie;
use App\Producto;
use App\TipoOrdenCompra;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesProductosMasVendidosExport;
use DB;

class ReporteProductosMasVendidos extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_productos_mas_vendidos(Request $request){
        $urlgenerarformatoexcel = route('reporte_productos_mas_vendidos_generar_formato_excel');
        return view('reportes.facturas.reporteproductosmasvendidos', compact('urlgenerarformatoexcel'));
    }
    
    //obtener tipos ordenes de compra
    public function reporte_productos_mas_vendidos_obtener_tipos_ordenes_compra(Request $request){
        $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->get();
        $select_tipos_ordenes_compra = "<option value='TODOS' selected>TODOS</option>";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }
    //obtener clientes
    public function reporte_productos_mas_vendidos_obtener_clientes(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_cliente_por_numero(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_agentes(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_agente_por_numero(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_series(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_serie_por_clave(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_almacenes(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_almacen_por_numero(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_marcas(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_marca_por_numero(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_lineas(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_linea_por_numero(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_productos(Request $request){
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
    public function reporte_productos_mas_vendidos_obtener_producto_por_codigo(Request $request){
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
    public function reporte_productos_mas_vendidos_generar_reporte(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $numerocliente=$request->numerocliente;
        $numeroagente=$request->numeroagente;
        $numeromarca=$request->numeromarca;
        $numerolinea=$request->numerolinea;
        $numeroalmacen=$request->numeroalmacen;
        $codigo=$request->codigo;
        $claveserie=$request->claveserie;
        $tipo=$request->tipo;
        $departamento=$request->departamento;
        $documentos=$request->documentos;
        $status=$request->status;
        $reporte = $request->reporte;
        $ordenarportotal=$request->ordenarportotal;
        $resumen=$request->resumen;
        $buscarenajuste=$request->buscarenajuste;
        switch($reporte){
            case "PORMARCAS":
                $data = DB::table('Facturas as f')
                            ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                            ->join('Productos as p', 'p.Codigo', '=', 'fd.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->join('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                            ->join('Agentes as a', 'f.Agente', '=', 'a.Numero')
                            ->select('f.Cliente', 'c.Nombre', 'fd.Codigo', 'p.Producto', 'p.Unidad', 'a.Nombre as NombreAgente', 'p.Ubicacion', 'm.Numero as Marca', 'm.Nombre AS NombreMarca', 'l.Numero as Linea', 'l.Nombre as NombreLinea', DB::raw("SUM(fd.Utilidad) AS Utilidad"), DB::raw("SUM(fd.Cantidad) AS Cantidad"), DB::raw("SUM(fd.SubTotal) AS SubTotal"), DB::raw("SUM(fd.Iva) AS Iva"),  DB::raw("SUM(fd.Total) AS Total"), 'fd.Almacen', DB::raw("p.[Fecha Ultima Compra] as FechaUltimaCompra"), DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate()) as Dias"), 'fd.Costo', DB::raw("p.[Fecha Ultima Venta] as FechaUltimaVenta") )
                            ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numerocliente) {
                                if($numerocliente != ""){
                                    $q->whereIn('f.Cliente', array($numerocliente));
                                }
                            })
                            ->where(function($q) use ($numeroagente) {
                                if($numeroagente != ""){
                                    $q->whereIn('f.Agente', array($numeroagente));
                                }
                            })
                            ->where(function($q) use ($numeromarca) {
                                if($numeromarca != ""){
                                    $q->whereIn('m.Numero', array($numeromarca));
                                }
                            })
                            ->where(function($q) use ($numerolinea) {
                                if($numerolinea != ""){
                                    $q->whereIn('l.Numero', array($numerolinea));
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('fd.Almacen', array($numeroalmacen));
                                }
                            })
                            ->where(function($q) use ($claveserie) {
                                if($claveserie != ""){
                                    $q->whereIn('f.Serie', array($claveserie));
                                }
                            })
                            ->where(function($q) use ($codigo) {
                                if($codigo != ""){
                                    $q->whereIn('fd.Codigo', array($codigo));
                                }
                            })
                            ->where(function($q) use ($departamento) {
                                if($departamento != 'TODOS'){
                                    $q->where('f.Depto', $departamento);
                                }
                            })
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('f.Status', $status);
                                }
                            })
                            ->where(function($q) use ($documentos) {
                                if($documentos != 'TODOS'){
                                    if($documentos == 'FACTURAS'){
                                        $q->where('f.Esquema', '<>', 'INTERNA');
                                    }
                                    if($documentos == 'INTERNOS'){
                                        $q->where('f.Esquema', 'INTERNA');
                                    }
                                }
                            })
                            ->where(function($q) use ($tipo) {
                                if($tipo != 'TODOS'){
                                    $q->where('f.Tipo', $tipo);
                                }
                            })
                            ->groupby('f.Cliente', 'c.Nombre', 'fd.Costo', 'f.Agente', 'fd.Codigo', 'p.Producto', 'f.Unidad', 'm.Numero', 'm.Nombre', 'l.Numero', 'l.Nombre', 'p.Ubicacion', 'p.Unidad', 'a.Nombre', DB::raw("p.[Fecha Ultima Venta]"), DB::raw("p.[Fecha Ultima Compra]"), DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate())"), 'fd.Almacen')
                            ->orderby(DB::raw("SUM(fd.".$ordenarportotal.")"), 'DESC')
                            ->get();
                return DataTables::of($data)
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->addColumn('Cantidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Cantidad), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->make(true);
                break;
            case "PORLINEAS":
                $data = DB::table('Facturas as f')
                            ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                            ->join('Productos as p', 'p.Codigo', '=', 'fd.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->join('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                            ->join('Agentes as a', 'f.Agente', '=', 'a.Numero')
                            ->select('f.Cliente', 'c.Nombre', 'fd.Codigo', 'p.Producto', 'p.Unidad', 'a.Nombre as NombreAgente', 'p.Ubicacion', 'm.Numero as Marca', 'm.Nombre AS NombreMarca', 'l.Numero as Linea', 'l.Nombre as NombreLinea', DB::raw("SUM(fd.Utilidad) AS Utilidad"), DB::raw("SUM(fd.Cantidad) AS Cantidad"), DB::raw("SUM(fd.SubTotal) AS SubTotal"), DB::raw("SUM(fd.Iva) AS Iva"),  DB::raw("SUM(fd.Total) AS Total"), 'fd.Almacen', DB::raw("p.[Fecha Ultima Compra] as FechaUltimaCompra"), DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate()) as Dias"), 'fd.Costo', DB::raw("p.[Fecha Ultima Venta] as FechaUltimaVenta") )
                            ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numerocliente) {
                                if($numerocliente != ""){
                                    $q->whereIn('f.Cliente', array($numerocliente));
                                }
                            })
                            ->where(function($q) use ($numeroagente) {
                                if($numeroagente != ""){
                                    $q->whereIn('f.Agente', array($numeroagente));
                                }
                            })
                            ->where(function($q) use ($numeromarca) {
                                if($numeromarca != ""){
                                    $q->whereIn('m.Numero', array($numeromarca));
                                }
                            })
                            ->where(function($q) use ($numerolinea) {
                                if($numerolinea != ""){
                                    $q->whereIn('l.Numero', array($numerolinea));
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('fd.Almacen', array($numeroalmacen));
                                }
                            })
                            ->where(function($q) use ($claveserie) {
                                if($claveserie != ""){
                                    $q->whereIn('f.Serie', array($claveserie));
                                }
                            })
                            ->where(function($q) use ($codigo) {
                                if($codigo != ""){
                                    $q->whereIn('fd.Codigo', array($codigo));
                                }
                            })
                            ->where(function($q) use ($departamento) {
                                if($departamento != 'TODOS'){
                                    $q->where('f.Depto', $departamento);
                                }
                            })
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('f.Status', $status);
                                }
                            })
                            ->where(function($q) use ($documentos) {
                                if($documentos != 'TODOS'){
                                    if($documentos == 'FACTURAS'){
                                        $q->where('f.Esquema', '<>', 'INTERNA');
                                    }
                                    if($documentos == 'INTERNOS'){
                                        $q->where('f.Esquema', 'INTERNA');
                                    }
                                }
                            })
                            ->where(function($q) use ($tipo) {
                                if($tipo != 'TODOS'){
                                    $q->where('f.Tipo', $tipo);
                                }
                            })
                            ->groupby('f.Cliente', 'c.Nombre', 'fd.Costo', 'f.Agente', 'fd.Codigo', 'p.Producto', 'f.Unidad', 'm.Numero', 'm.Nombre', 'l.Numero', 'l.Nombre', 'p.Ubicacion', 'p.Unidad', 'a.Nombre', DB::raw("p.[Fecha Ultima Venta]"), DB::raw("p.[Fecha Ultima Compra]"), DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate())"), 'fd.Almacen')
                            ->orderby(DB::raw("SUM(fd.".$ordenarportotal.")"), 'DESC')
                            ->get();
                return DataTables::of($data)
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->addColumn('Cantidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Cantidad), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->make(true);
                break;
            case "PORCLIENTES":
                $data = DB::table('Facturas as f')
                            ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                            ->join('Productos as p', 'p.Codigo', '=', 'fd.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->join('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                            ->join('Agentes as a', 'f.Agente', '=', 'a.Numero')
                            ->select('f.Cliente', 'c.Nombre', 'fd.Codigo', 'p.Producto', 'p.Unidad', 'a.Nombre as NombreAgente', 'p.Ubicacion', 'm.Numero as Marca', 'm.Nombre AS NombreMarca', 'l.Numero as Linea', 'l.Nombre as NombreLinea', DB::raw("SUM(fd.Utilidad) AS Utilidad"), DB::raw("SUM(fd.Cantidad) AS Cantidad"), DB::raw("SUM(fd.SubTotal) AS SubTotal"), DB::raw("SUM(fd.Iva) AS Iva"),  DB::raw("SUM(fd.Total) AS Total"), 'fd.Almacen', DB::raw("p.[Fecha Ultima Compra] as FechaUltimaCompra"), DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate()) as Dias"), 'fd.Costo', DB::raw("p.[Fecha Ultima Venta] as FechaUltimaVenta") )
                            ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numerocliente) {
                                if($numerocliente != ""){
                                    $q->whereIn('f.Cliente', array($numerocliente));
                                }
                            })
                            ->where(function($q) use ($numeroagente) {
                                if($numeroagente != ""){
                                    $q->whereIn('f.Agente', array($numeroagente));
                                }
                            })
                            ->where(function($q) use ($numeromarca) {
                                if($numeromarca != ""){
                                    $q->whereIn('m.Numero', array($numeromarca));
                                }
                            })
                            ->where(function($q) use ($numerolinea) {
                                if($numerolinea != ""){
                                    $q->whereIn('l.Numero', array($numerolinea));
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('fd.Almacen', array($numeroalmacen));
                                }
                            })
                            ->where(function($q) use ($claveserie) {
                                if($claveserie != ""){
                                    $q->whereIn('f.Serie', array($claveserie));
                                }
                            })
                            ->where(function($q) use ($codigo) {
                                if($codigo != ""){
                                    $q->whereIn('fd.Codigo', array($codigo));
                                }
                            })
                            ->where(function($q) use ($departamento) {
                                if($departamento != 'TODOS'){
                                    $q->where('f.Depto', $departamento);
                                }
                            })
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('f.Status', $status);
                                }
                            })
                            ->where(function($q) use ($documentos) {
                                if($documentos != 'TODOS'){
                                    if($documentos == 'FACTURAS'){
                                        $q->where('f.Esquema', '<>', 'INTERNA');
                                    }
                                    if($documentos == 'INTERNOS'){
                                        $q->where('f.Esquema', 'INTERNA');
                                    }
                                }
                            })
                            ->where(function($q) use ($tipo) {
                                if($tipo != 'TODOS'){
                                    $q->where('f.Tipo', $tipo);
                                }
                            })
                            ->groupby('f.Cliente', 'c.Nombre', 'fd.Costo', 'f.Agente', 'fd.Codigo', 'p.Producto', 'f.Unidad', 'm.Numero', 'm.Nombre', 'l.Numero', 'l.Nombre', 'p.Ubicacion', 'p.Unidad', 'a.Nombre', DB::raw("p.[Fecha Ultima Venta]"), DB::raw("p.[Fecha Ultima Compra]"), DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate())"), 'fd.Almacen')
                            ->orderby(DB::raw("SUM(fd.".$ordenarportotal.")"), 'DESC')
                            ->get();
                return DataTables::of($data)
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->addColumn('Cantidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Cantidad), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->make(true);
                break;
            case "PORCODIGOS":
                $data = DB::table('Facturas as f')
                            ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                            ->join('Productos as p', 'p.Codigo', '=', 'fd.Codigo')
                            ->join('Existencias as e', 'fd.Codigo', '=', 'e.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->join('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                            ->select('fd.Codigo', 'p.Producto', 'p.Unidad', 'm.Nombre as Marca', 'l.Nombre as Linea', 'p.Ubicacion', DB::raw("SUM(fd.Cantidad) AS Cantidad"), 'p.Costo', 'p.Venta', 'fd.Almacen', 'e.Existencias' )
                            ->whereColumn("fd.Almacen", "e.Almacen")
                            ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numerocliente) {
                                if($numerocliente != ""){
                                    $q->whereIn('f.Cliente', array($numerocliente));
                                }
                            })
                            ->where(function($q) use ($numeroagente) {
                                if($numeroagente != ""){
                                    $q->whereIn('f.Agente', array($numeroagente));
                                }
                            })
                            ->where(function($q) use ($numeromarca) {
                                if($numeromarca != ""){
                                    $q->whereIn('m.Numero', array($numeromarca));
                                }
                            })
                            ->where(function($q) use ($numerolinea) {
                                if($numerolinea != ""){
                                    $q->whereIn('l.Numero', array($numerolinea));
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('fd.Almacen', array($numeroalmacen));
                                }
                            })
                            ->where(function($q) use ($claveserie) {
                                if($claveserie != ""){
                                    $q->whereIn('f.Serie', array($claveserie));
                                }
                            })
                            ->where(function($q) use ($codigo) {
                                if($codigo != ""){
                                    $q->whereIn('fd.Codigo', array($codigo));
                                }
                            })
                            ->where(function($q) use ($departamento) {
                                if($departamento != 'TODOS'){
                                    $q->where('f.Depto', $departamento);
                                }
                            })
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('f.Status', $status);
                                }
                            })
                            ->where(function($q) use ($documentos) {
                                if($documentos != 'TODOS'){
                                    if($documentos == 'FACTURAS'){
                                        $q->where('f.Esquema', '<>', 'INTERNA');
                                    }
                                    if($documentos == 'INTERNOS'){
                                        $q->where('f.Esquema', 'INTERNA');
                                    }
                                }
                            })
                            ->where(function($q) use ($tipo) {
                                if($tipo != 'TODOS'){
                                    $q->where('f.Tipo', $tipo);
                                }
                            })
                            ->groupby('fd.Codigo', 'p.Producto', 'f.Unidad', 'p.Unidad', 'm.Nombre', 'l.Nombre', 'p.Costo', 'p.Venta', 'fd.Almacen', 'e.Existencias', 'p.Ubicacion')
                            ->orderby(DB::raw("SUM(fd.Cantidad)"), 'DESC')
                            ->get();
                return DataTables::of($data)
                ->addColumn('Cantidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Cantidad), $this->numerodecimales); })
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->addColumn('Venta', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Venta), $this->numerodecimales); })
                ->make(true);
                break;
            case "CRUCEAJUSTE":
                //filtros en consulta
                $wheres = "";
                $wheres = $wheres ." and f.fecha >= '" .$fechainicio. "' and f.fecha <= '" .$fechaterminacion. "'";
                if($numerocliente != ""){
                    //$wheres = $wheres . "and f.Cliente = ".$numerocliente." ";
                    $wheres = $wheres . "and f.Cliente in (".$numerocliente.")";
                }
                if($numeroagente != ""){
                    //$wheres = $wheres . "and f.Agente = ".$numeroagente." ";
                    $wheres = $wheres . "and f.Agente in (".$numeroagente.")";
                }
                if($numeromarca != ""){
                    $wheres = $wheres . "and m.Numero in (".$numeromarca.")";
                }
                if($numerolinea != ""){
                    $wheres = $wheres . "and l.Numero in (".$numerolinea.")";
                }
                if($numeroalmacen != ""){
                    $wheres = $wheres . "and d.Almacen in (".$numeroalmacen.")";
                }
                if($claveserie != ""){
                    //$wheres = $wheres . "and f.Serie = '".$claveserie."' ";
                    $wheres = $wheres . "and f.Serie in (".$claveserie.")";
                }
                if($codigo != ""){
                    $wheres = $wheres . "and d.Codigo in ('".$codigo."')";
                }
                if($departamento != 'TODOS'){
                    $wheres = $wheres . "and f.Depto = '".$departamento."' ";
                }
                if($documentos != 'TODOS'){
                    if($documentos == 'FACTURAS'){
                        $wheres = $wheres . "and f.Esquema <> 'INTERNA' ";
                    }
                    if($documentos == 'INTERNOS'){
                        $wheres = $wheres . "and f.Esquema = 'INTERNA' ";
                    }
                }
                if($tipo != 'TODOS'){
                    $wheres = $wheres . "and f.Tipo = '".$tipo."' ";
                }
                if($status != 'TODOS'){
                    $wheres = $wheres . "and f.Status = '".$status."' ";
                }
                $data = DB::table('Ajustes de Inventario Detalles as a')
                            ->select('a.Codigo', 't.Producto', 't.Marca', 't.Linea', 't.Almacen', DB::raw("SUM(a.Entradas - a.Salidas) as CantidadDelAjuste"), 't.Cantidad as MasVendidas')
                    
                            ->join(DB::raw('(select	d.Codigo, p.Producto, m.nombre as Marca, l.nombre as Linea, sum(d.cantidad) as Cantidad, d.Almacen, p.Costo
                                from	facturas f, [facturas detalles] d, productos p, marcas m, lineas l, clientes c
                                where	f.factura = d.factura and d.codigo = p.codigo and f.cliente = c.numero and p.marca = m.numero and p.linea = l.numero 	
                                '.$wheres.'
                                group by d.codigo, p.producto, m.numero, m.nombre, l.nombre, p.Costo, d.Almacen
                            ) t'), 
                            function($join)
                            {
                                $join->on('t.Codigo', '=', 'a.Codigo');
                            })
                            ->where(function($q) use ($buscarenajuste) {
                                if($buscarenajuste != ""){
                                    $q->where('a.Ajuste', $buscarenajuste);
                                }
                            })
                            ->groupby('a.Codigo', 't.Producto', 't.Marca', 't.Linea', 't.Almacen', 't.Cantidad')
                            ->orderBy('t.Cantidad', 'DESC')
                            ->get();
                return DataTables::of($data)->make(true);
                break;
        }
    }
    
    //generar reporte en excel
    public function reporte_productos_mas_vendidos_generar_formato_excel(Request $request){
        return Excel::download(new ReportesProductosMasVendidosExport($request->fechainicialreporte, $request->fechafinalreporte, $request->numerocliente, $request->numeroagente, $request->numeromarca, $request->numerolinea, $request->numeroalmacen, $request->codigo, $request->claveserie, $request->tipo, $request->departamento, $request->documentos, $request->status, $request->reporte, $request->ordenarportotal, $request->resumen, $request->buscarenajuste, $this->numerodecimales, $this->empresa), "formatoproductosmasvendidos-".$request->reporte.".xlsx");    
    }
    
}
