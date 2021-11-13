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
use App\CotizacionProducto;
use App\CotizacionProductoDetalle;
use App\Cliente;
use App\TipoOrdenCompra;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesRelacionCotizacionExport;
use DB;

class ReportesCotizacionesController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_relacion_cotizaciones(Request $request){
        $urlgenerarformatoexcel = route('reporte_relacion_cotizaciones_generar_formato_excel');
        return view('reportes.cotizaciones.reporterelacioncotizaciones', compact('urlgenerarformatoexcel'));
    }
    //obtener tipos ordenes de compra
    public function reporte_relacion_cotizaciones_obtener_tipos_ordenes_compra(Request $request){
        $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->get();
        $select_tipos_ordenes_compra = "<option value='TODOS' selected>TODOS</option>";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }
    //obtener proveedores
    public function reporte_relacion_cotizaciones_obtener_clientes(Request $request){
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
    //obtener proveedor por numero
    public function reporte_relacion_cotizaciones_obtener_cliente_por_numero(Request $request){
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
    //generar reporte
    public function reporte_relacion_cotizaciones_generar_reporte(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $reporte = $request->reporte;
        $numerocliente=$request->numerocliente;
        $tipo=$request->tipo;
        $status=$request->status;
        if($reporte == "GENERAL"){
            $data = DB::table('Cotizaciones as co')
            ->leftjoin('Clientes as c', 'co.Cliente', '=', 'c.Numero')
            ->select('co.Cotizacion', 'co.Cliente', 'c.Nombre', 'co.Fecha', 'co.Plazo', 'co.Tipo', 'co.Referencia', 'co.Importe', 'co.Descuento', 'co.SubTotal', 'co.Iva', 'co.Total', 'co.Obs', 'co.Status', 'co.MotivoBaja', 'co.Usuario')
            //->whereBetween('co.Fecha', [$fechainicio, $fechaterminacion])
            ->whereDate('co.Fecha', '>=', $fechainicio)->whereDate('co.Fecha', '<=', $fechaterminacion)
            ->where(function($q) use ($numerocliente) {
                if($numerocliente != ""){
                    $q->where('co.Cliente', $numerocliente);
                }
            })
            ->where(function($q) use ($tipo) {
                if($tipo != 'TODOS'){
                    $q->where('co.Tipo', $tipo);
                }
            })
            ->where(function($q) use ($status) {
                if($status != 'TODOS'){
                    $q->where('co.Status', $status);
                }
            })
            ->orderby('co.Serie', 'ASC')
            ->orderby('co.Folio', 'ASC')
            ->get();
            return DataTables::of($data)
            ->addColumn('Nombre', function($data){ return substr($data->Nombre, 0, 30); })
            ->addColumn('Importe', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Importe), $this->numerodecimales); })
            ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
            ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
            ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
            ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
            ->addColumn('Obs', function($data){ return substr($data->Obs, 0, 30); })
            ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 30); })
            ->make(true);
        }else{
            $data = DB::table('Cotizaciones as co')
            ->leftjoin('Clientes as c', 'co.Cliente', '=', 'c.Numero')
            ->leftjoin('Cotizaciones Detalles as cod', 'co.Cotizacion', '=', 'cod.Cotizacion')
            ->select('co.Cotizacion', 'co.Cliente', 'c.Nombre', 'co.Fecha', 'co.Plazo', 'co.Tipo', 'co.Referencia', 'cod.Codigo', 'cod.Descripcion', 'cod.Unidad', 'cod.Cantidad', 'cod.Precio', 'cod.Importe', 'cod.Descuento', 'cod.SubTotal', 'cod.Iva', 'cod.Total', 'co.Obs', 'co.Status', 'co.MotivoBaja', 'co.Usuario')
            //->whereBetween('co.Fecha', [$fechainicio, $fechaterminacion])
            ->whereDate('co.Fecha', '>=', $fechainicio)->whereDate('co.Fecha', '<=', $fechaterminacion)
            ->where(function($q) use ($numerocliente) {
                if($numerocliente != ""){
                    $q->where('co.Cliente', $numerocliente);
                }
            })
            ->where(function($q) use ($tipo) {
                if($tipo != 'TODOS'){
                    $q->where('co.Tipo', $tipo);
                }
            })
            ->where(function($q) use ($status) {
                if($status != 'TODOS'){
                    $q->where('co.Status', $status);
                }
            })
            ->orderby('co.Serie', 'ASC')
            ->orderby('co.Folio', 'ASC')
            ->get();
            return DataTables::of($data)
            ->addColumn('Nombre', function($data){ return substr($data->Nombre, 0, 30); })
            ->addColumn('Descripcion', function($data){ return substr($data->Descripcion, 0, 30); })
            ->addColumn('Precio', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Precio), $this->numerodecimales); })
            ->addColumn('Importe', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Importe), $this->numerodecimales); })
            ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
            ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
            ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
            ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
            ->addColumn('Obs', function($data){ return substr($data->Obs, 0, 30); })
            ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 30); })
            ->make(true);
        }
    }
    //generar excel reporte relacion ordenes compra
    public function reporte_relacion_cotizaciones_generar_formato_excel(Request $request){
        return Excel::download(new ReportesRelacionCotizacionExport($request->fechainicialreporte, $request->fechafinalreporte, $request->numerocliente, $request->tipo, $request->status, $request->reporte, $this->numerodecimales, $this->empresa), "formatorelacioncotizaciones-".$request->reporte.".xlsx"); 
    }
}
