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
use App\Compra;
use App\CompraDetalle;
use App\Proveedor;
use App\Almacen;
use App\Marca;
use App\Linea;
use App\TipoOrdenCompra;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesProductosMasCompradosExport;
use DB;

class ReporteProductosMasComprados extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_productos_mas_comprados(Request $request){
        $urlgenerarformatoexcel = route('reporte_productos_mas_comprados_generar_formato_excel');
        return view('reportes.compras.reporteproductosmascomprados', compact('urlgenerarformatoexcel'));
    }
    //obtener proveedores
    public function reporte_productos_mas_comprados_obtener_proveedores(Request $request){
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
    //obtener alamcenes
    public function reporte_productos_mas_comprados_obtener_almacenes(Request $request){
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
    //obtener proveedor por numero
    public function reporte_productos_mas_comprados_obtener_proveedor_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existeproveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->count();
        if($existeproveedor > 0){
            $proveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->first();
            $numero = $proveedor->Numero;
            $nombre = $proveedor->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data);
        
    }
    //obtener almacen por numero
    public function reporte_productos_mas_comprados_obtener_almacen_por_numero(Request $request){
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
    public function reporte_productos_mas_comprados_obtener_marcas(Request $request){
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
    //obtener lineas
    public function reporte_productos_mas_comprados_obtener_lineas(Request $request){
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
    //obtener marca por numero
    public function reporte_productos_mas_comprados_obtener_marca_por_numero(Request $request){
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
    //obtener linea por numero
    public function reporte_productos_mas_comprados_obtener_linea_por_numero(Request $request){
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
    
    //generar reporte
    public function reporte_productos_mas_comprados_generar_reporte(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $reporte = $request->reporte;
        $numeroproveedor = $request->numeroproveedor;
        $numeroalmacen = $request->numeroalmacen;
        $numeromarca = $request->numeromarca;
        $numerolinea = $request->numerolinea;
        $status = $request->status;
        $ordenarportotal = $request->ordenarportotal;
        switch($reporte){
            case "PORMARCAS":
                $data = DB::table('Compras as c')
                            ->join('Compras Detalles as cd', 'c.Compra', '=', 'cd.Compra')
                            ->join('Productos as p', 'p.Codigo', '=', 'cd.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->select('c.Proveedor', 'cd.Codigo', 'p.Producto', 'p.Unidad', 'c.Fecha', 'c.Tipo', 'c.Almacen', 'c.Factura', 'c.Remision', 'm.Nombre AS Marca', 'l.Nombre AS Linea', DB::raw("SUM(cd.Cantidad) AS Cantidad"), 'cd.Precio', 'cd.Importe', 'cd.Dcto', 'cd.Descuento',  DB::raw("SUM(cd.SubTotal) AS SubTotal"),  DB::raw("SUM(cd.Iva) AS Iva"),  DB::raw("SUM(cd.Total) AS Total"),  )
                            ->whereDate('c.Fecha', '>=', $fechainicio)->whereDate('c.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numeroproveedor) {
                                if($numeroproveedor != ""){
                                    $q->where('c.Proveedor', $numeroproveedor);
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('c.Almacen', array($numeroalmacen));
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
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('c.Status', $status);
                                }
                            })
                            ->groupby('c.Proveedor', 'cd.Codigo', 'p.Producto', 'p.Unidad', 'c.Fecha', 'c.Factura', 'c.Remision', 'c.Tipo', 'c.Almacen', 'm.Nombre', 'l.Nombre', 'cd.Importe', 'cd.Precio', 'cd.Dcto', 'cd.Descuento')
                            ->orderby(DB::raw("SUM(cd.".$ordenarportotal.")"), 'DESC')
                            ->get();
                return DataTables::of($data)
                ->addColumn('Precio', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Precio), $this->numerodecimales); })
                ->addColumn('Importe', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Importe), $this->numerodecimales); })
                ->addColumn('Dcto', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Dcto), $this->numerodecimales); })
                ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->make(true);
                break;
            case "PORLINEAS":
                $data = DB::table('Compras as c')
                            ->join('Compras Detalles as cd', 'c.Compra', '=', 'cd.Compra')
                            ->join('Productos as p', 'p.Codigo', '=', 'cd.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->select('c.Proveedor', 'cd.Codigo', 'p.Producto', 'p.Unidad', 'c.Fecha', 'c.Tipo', 'c.Almacen', 'c.Factura', 'c.Remision', 'm.Nombre AS Marca', 'l.Nombre AS Linea', DB::raw("SUM(cd.Cantidad) AS Cantidad"), 'cd.Precio', 'cd.Importe', 'cd.Dcto', 'cd.Descuento',  DB::raw("SUM(cd.SubTotal) AS SubTotal"),  DB::raw("SUM(cd.Iva) AS Iva"),  DB::raw("SUM(cd.Total) AS Total"),  )
                            ->whereDate('c.Fecha', '>=', $fechainicio)->whereDate('c.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numeroproveedor) {
                                if($numeroproveedor != ""){
                                    $q->where('c.Proveedor', $numeroproveedor);
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('c.Almacen', array($numeroalmacen));
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
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('c.Status', $status);
                                }
                            })
                            ->groupby('c.Proveedor', 'cd.Codigo', 'p.Producto', 'p.Unidad', 'c.Fecha', 'c.Factura', 'c.Remision', 'c.Tipo', 'c.Almacen', 'm.Nombre', 'l.Nombre', 'cd.Importe', 'cd.Precio', 'cd.Dcto', 'cd.Descuento')
                            ->orderby(DB::raw("SUM(cd.".$ordenarportotal.")"), 'DESC')
                            ->get();
                return DataTables::of($data)
                ->addColumn('Precio', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Precio), $this->numerodecimales); })
                ->addColumn('Importe', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Importe), $this->numerodecimales); })
                ->addColumn('Dcto', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Dcto), $this->numerodecimales); })
                ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->make(true);
                break;
            case "PORPROVEEDORES":
                $data = DB::table('Compras as c')
                            ->join('Compras Detalles as cd', 'c.Compra', '=', 'cd.Compra')
                            ->join('Productos as p', 'p.Codigo', '=', 'cd.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->select('c.Proveedor', 'cd.Codigo', 'p.Producto', 'p.Unidad', 'c.Fecha', 'c.Tipo', 'c.Almacen', 'c.Factura', 'c.Remision', 'm.Nombre AS Marca', 'l.Nombre AS Linea', DB::raw("SUM(cd.Cantidad) AS Cantidad"), 'cd.Precio', 'cd.Importe', 'cd.Dcto', 'cd.Descuento',  DB::raw("SUM(cd.SubTotal) AS SubTotal"),  DB::raw("SUM(cd.Iva) AS Iva"),  DB::raw("SUM(cd.Total) AS Total"),  )
                            ->whereDate('c.Fecha', '>=', $fechainicio)->whereDate('c.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numeroproveedor) {
                                if($numeroproveedor != ""){
                                    $q->where('c.Proveedor', $numeroproveedor);
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('c.Almacen', array($numeroalmacen));
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
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('c.Status', $status);
                                }
                            })
                            ->groupby('c.Proveedor', 'cd.Codigo', 'p.Producto', 'p.Unidad', 'c.Fecha', 'c.Factura', 'c.Remision', 'c.Tipo', 'c.Almacen', 'm.Nombre', 'l.Nombre', 'cd.Importe', 'cd.Precio', 'cd.Dcto', 'cd.Descuento')
                            ->orderby(DB::raw("SUM(cd.".$ordenarportotal.")"), 'DESC')
                            ->get();
                return DataTables::of($data)
                ->addColumn('Precio', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Precio), $this->numerodecimales); })
                ->addColumn('Importe', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Importe), $this->numerodecimales); })
                ->addColumn('Dcto', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Dcto), $this->numerodecimales); })
                ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->make(true);
                break;
            case "PORCODIGOS":
                $data = DB::table('Compras as c')
                            ->join('Compras Detalles as cd', 'c.Compra', '=', 'cd.Compra')
                            ->join('Productos as p', 'p.Codigo', '=', 'cd.Codigo')
                            ->join('Existencias as e', 'cd.Codigo', '=', 'e.Codigo')
                            ->join('Proveedores as prov', 'c.Proveedor', '=', 'prov.Numero')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->select('cd.Codigo', 'p.Producto', 'p.Unidad', 'm.Nombre AS Marca', 'l.Nombre AS Linea', 'p.Ubicacion', DB::raw("SUM(cd.Cantidad) AS Cantidad"), 'p.Costo', 'p.Venta', 'c.Almacen', 'e.Existencias')
                            ->whereColumn("c.Almacen", "e.Almacen")
                            ->whereDate('c.Fecha', '>=', $fechainicio)->whereDate('c.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numeroproveedor) {
                                if($numeroproveedor != ""){
                                    $q->where('c.Proveedor', $numeroproveedor);
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('c.Almacen', array($numeroalmacen));
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
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('c.Status', $status);
                                }
                            })
                            ->groupby('cd.Codigo', 'p.Producto', 'p.Unidad', 'm.Nombre', 'l.Nombre', 'p.Costo', 'p.Venta', 'c.Almacen', 'e.Existencias', 'p.Ubicacion')
                            ->orderby(DB::raw("SUM(cd.Cantidad)"), 'DESC')
                            ->get();
                return DataTables::of($data)
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->addColumn('Venta', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Venta), $this->numerodecimales); })
                ->make(true);
                break;
        }
    }
    //generar excel reporte
    public function reporte_productos_mas_comprados_generar_formato_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new ReportesProductosMasCompradosExport($request->fechainicialreporte, $request->fechafinalreporte, $request->numeroproveedor, $request->numeroalmacen, $request->numeromarca, $request->numerolinea, $request->ordenarportotal, $request->status, $request->reporte, $this->numerodecimales, $this->empresa), "formatoproductosmascomprados-".$request->reporte.".xlsx"); 
    }
}
