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
use App\Exports\ReportesFacturasVentasMarcasExport;
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
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $numerocliente=$request->numerocliente;
        $numeroagente=$request->numeroagente;
        $numeromarca = $request->numeromarca;
        $numerolinea = $request->numerolinea;
        $claveserie = $request->claveserie;
        $numeroalmacen = $request->numeroalmacen;
        $codigo = $request->codigo;
        $tipo=$request->tipo;
        $departamento=$request->departamento;
        $documentos=$request->documentos;
        $status=$request->status;
        $reporte = $request->reporte;
        switch($reporte){
            case "RELACIONUTILIDADES":
                $data = DB::table('Facturas as f')
                ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                ->join('Productos as p', 'p.Codigo', '=', 'fd.Codigo')
                ->leftjoin('Marcas as m', 'm.Numero', '=', 'p.Marca')
                ->join('Clientes as c', 'c.Numero', '=', 'f.Cliente')
                ->leftjoin('Agentes as a', 'a.Numero', '=', 'f.Agente')
                ->select("f.Factura", "f.Fecha", "fd.Remision", "fd.Orden", "p.Producto", "c.Nombre as NombreCliente", "f.Agente", "a.Nombre as NombreAgente", "fd.Almacen", "fd.Codigo", "p.Marca", "m.Nombre", "fd.Cantidad", "fd.Precio", "fd.Dcto", "fd.Descuento", "fd.SubTotal", "fd.Iva", "fd.Total", DB::raw("case when fd.moneda = 'USD' and round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) > 0 then round(fd.CostoDeLista*fd.TipoDeCambio,2) else fd.Costo end as Costo"),DB::raw("case when fd.moneda = 'USD' and round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) > 0 then round(fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) else fd.CostoTotal end as CostoTotal"),DB::raw("case	when fd.moneda = 'USD' and round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) > 0 then fd.SubTotal-round(fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) else fd.Utilidad end as Utilidad"),DB::raw("p.[Fecha Ultima Compra] as FechaUltimaCompra"),DB::raw("p.[Fecha Ultima Venta] as FechaUltimaVenta"),DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate()) as Dias"))
                ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('f.Cliente', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numeromarca) {
                    if($numeromarca != ""){
                        $q->whereIn('p.Marca', array($numeromarca));
                    }
                })
                ->where(function($q) use ($numerolinea) {
                    if($numerolinea != ""){
                        $q->whereIn('p.Linea', array($numerolinea));
                    }
                })
                ->where(function($q) use ($claveserie) {
                    if($claveserie != ""){
                        $q->whereIn('f.Serie', array($claveserie));
                    }
                })
                ->where(function($q) use ($numeroalmacen) {
                    if($numeroalmacen != ""){
                        $q->whereIn('fd.Almacen', array($numeroalmacen));
                    }
                })
                ->where(function($q) use ($codigo) {
                    if($codigo != ""){
                        $q->where('fd.Codigo', $codigo);
                    }
                })
                ->where(function($q) use ($tipo) {
                    if($tipo != 'TODOS'){
                        $q->where('f.Tipo', $tipo);
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
                ->orderby('f.Serie', 'ASC')
                ->orderby('f.Folio', 'ASC')
                ->orderby('fd.Item', 'ASC')
                ->get();
                return DataTables::of($data)
                ->addColumn('NombreCliente', function($data){ return substr($data->NombreCliente, 0, 30); })
                ->addColumn('NombreAgente', function($data){ return substr($data->NombreAgente, 0, 30); })
                ->addColumn('Precio', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Precio), $this->numerodecimales); })
                ->addColumn('Dcto', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Dcto), $this->numerodecimales); })
                ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->addColumn('CostoTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->CostoTotal), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->make(true);
                break;
            case "UTILIDADCAMBIARIA":
                $data = DB::table('Facturas as f')
                ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                ->join('Productos as p', 'p.Codigo', '=', 'fd.Codigo')
                ->leftjoin('Marcas as m', 'm.Numero', '=', 'p.Marca')
                ->join('Clientes as c', 'c.Numero', '=', 'f.Cliente')
                ->leftjoin('Agentes as a', 'a.Numero', '=', 'f.Agente')
                ->select("f.Factura", "f.Fecha", "fd.Remision", "fd.Orden", "p.Producto", "c.Nombre as NombreCliente", "f.Agente", "a.Nombre as NombreAgente", "fd.Almacen", "fd.Codigo", "p.Marca", "m.Nombre", "fd.Cantidad", "fd.Precio", "fd.Dcto", "fd.Descuento", "fd.SubTotal", "fd.Iva", "fd.Total","fd.Costo", "fd.CostoTotal", "fd.Utilidad", DB::raw("case when fd.subtotal>0 then (fd.Utilidad/fd.SubTotal)*100 else 0 end as PorcentajeUtilidad"),"fd.CostoDeLista", "fd.Moneda", "fd.TipoDeCambio", DB::raw("fd.CostoDeLista*fd.TipoDeCambio as CostoDeListaMXN"),DB::raw("round(fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) as TotalCostoDeListaReal"),DB::raw("round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) as UtilidadPorCostoDeListaActualReal"),DB::raw("round(fd.Utilidad-(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad),2) as UtilidadPorTC"),DB::raw("case when fd.moneda = 'USD' and round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) > 0 then round(fd.CostoDeLista*fd.TipoDeCambio,2) else fd.Costo end as CostoParaComision"),DB::raw("case when fd.moneda = 'USD' and round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) > 0 then round(fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) else fd.CostoTotal end as CostoTotalParaComision"),DB::raw("case when fd.moneda = 'USD' and round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) > 0 then fd.SubTotal-round(fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) else fd.Utilidad end as UtilidadParaComision"))
                ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('f.Cliente', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numeromarca) {
                    if($numeromarca != ""){
                        $q->whereIn('p.Marca', array($numeromarca));
                    }
                })
                ->where(function($q) use ($numerolinea) {
                    if($numerolinea != ""){
                        $q->whereIn('p.Linea', array($numerolinea));
                    }
                })
                ->where(function($q) use ($claveserie) {
                    if($claveserie != ""){
                        $q->whereIn('f.Serie', array($claveserie));
                    }
                })
                ->where(function($q) use ($numeroalmacen) {
                    if($numeroalmacen != ""){
                        $q->whereIn('fd.Almacen', array($numeroalmacen));
                    }
                })
                ->where(function($q) use ($codigo) {
                    if($codigo != ""){
                        $q->where('fd.Codigo', $codigo);
                    }
                })
                ->where(function($q) use ($tipo) {
                    if($tipo != 'TODOS'){
                        $q->where('f.Tipo', $tipo);
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
                ->orderby('f.Serie', 'ASC')
                ->orderby('f.Folio', 'ASC')
                ->orderby('fd.Item', 'ASC')
                ->get();
                return DataTables::of($data)
                ->addColumn('NombreCliente', function($data){ return substr($data->NombreCliente, 0, 30); })
                ->addColumn('NombreAgente', function($data){ return substr($data->NombreAgente, 0, 30); })
                ->addColumn('Precio', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Precio), $this->numerodecimales); })
                ->addColumn('Dcto', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Dcto), $this->numerodecimales); })
                ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->addColumn('CostoTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->CostoTotal), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->addColumn('PorcentajeUtilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->PorcentajeUtilidad), $this->numerodecimales); })
                ->addColumn('CostoDeLista', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->CostoDeLista), $this->numerodecimales); })
                ->addColumn('TipoDeCambio', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->TipoDeCambio), $this->numerodecimales); })
                ->addColumn('CostoDeListaMXN', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->CostoDeListaMXN), $this->numerodecimales); })
                ->addColumn('TotalCostoDeListaReal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->TotalCostoDeListaReal), $this->numerodecimales); })
                ->addColumn('UtilidadPorCostoDeListaActualReal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->UtilidadPorCostoDeListaActualReal), $this->numerodecimales); })
                ->addColumn('UtilidadPorTC', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->UtilidadPorTC), $this->numerodecimales); })
                ->addColumn('CostoParaComision', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->CostoParaComision), $this->numerodecimales); })
                ->addColumn('CostoTotalParaComision', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->CostoTotalParaComision), $this->numerodecimales); })
                ->addColumn('UtilidadParaComision', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->UtilidadParaComision), $this->numerodecimales); })
                ->make(true);
                break;
        }
    }
    //generar reporte en excel
    public function reporte_facturas_ventas_marca_generar_formato_excel(Request $request){
        return Excel::download(new ReportesFacturasVentasMarcasExport($request->fechainicialreporte, $request->fechafinalreporte, $request->numerocliente, $request->numeroagente, $request->numeromarca, $request->numerolinea, $request->numeroalmacen, $request->codigo, $request->claveserie, $request->tipo, $request->departamento, $request->documentos, $request->status, $request->reporte, $this->numerodecimales, $this->empresa), "formatofacturasventaspormarcas-".$request->reporte.".xlsx");    
    }
}
