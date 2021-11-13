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
use App\ContraRecibo;
use App\ContraReciboDetalle;
use App\Proveedor;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesRelacionContraReciboExport;
use DB;

class ReportesContraRecibosController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_relacion_contrarecibos(Request $request){
        $urlgenerarformatoexcel = route('reporte_relacion_contrarecibos_generar_formato_excel');
        return view('reportes.contrarecibos.reporterelacioncontrarecibos', compact('urlgenerarformatoexcel'));
    }
    //obtener proveedores
    public function reporte_relacion_contrarecibos_obtener_proveedores(Request $request){
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
    //obtener proveedor por numero
    public function reporte_relacion_contrarecibos_obtener_proveedor_por_numero(Request $request){
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
    //generar reporte
    public function reporte_relacion_contrarecibos_generar_reporte(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $reporte = $request->reporte;
        $numeroproveedor=$request->numeroproveedor;
        if($reporte == "GENERAL"){
            $data = DB::table('ContraRecibos as cr')
            ->leftjoin('Proveedores as p', 'cr.Proveedor', '=', 'p.Numero')
            ->leftjoin('ContraRecibos Detalles as crd', 'cr.ContraRecibo', '=', 'crd.ContraRecibo')
            ->leftjoin('Compras as c', 'crd.Compra', '=', 'c.Compra')
            ->select('cr.ContraRecibo', 'cr.Proveedor', 'p.Nombre', 'cr.Fecha', DB::raw("SUM(c.SubTotal) as SubTotal"), DB::raw("SUM(c.Iva) as Iva"), DB::raw("SUM(c.Total) as Total"), 'cr.Status')
            //->whereBetween('cr.Fecha', [$fechainicio, $fechaterminacion])
            ->whereDate('cr.Fecha', '>=', $fechainicio)->whereDate('cr.Fecha', '<=', $fechaterminacion)
            ->where(function($q) use ($numeroproveedor) {
                if($numeroproveedor != ""){
                    $q->where('cr.Proveedor', $numeroproveedor);
                }
            })
            ->groupby('cr.ContraRecibo', 'cr.Fecha', 'cr.Proveedor', 'p.Nombre', 'cr.Status')
            ->orderby('cr.Fecha', 'ASC')
            ->get();
            return DataTables::of($data)
            ->addColumn('Nombre', function($data){ return substr($data->Nombre, 0, 30); })
            ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
            ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
            ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
            ->make(true);
        }else{
            $data = DB::table('ContraRecibos as cr')
            ->leftjoin('Proveedores as p', 'cr.Proveedor', '=', 'p.Numero')
            ->leftjoin('ContraRecibos Detalles as crd', 'cr.ContraRecibo', '=', 'crd.ContraRecibo')
            ->leftjoin('Compras as c', 'crd.Compra', '=', 'c.Compra')
            ->select('cr.ContraRecibo', 'cr.Proveedor', 'p.Nombre', 'crd.Compra', 'c.Movimiento', 'cr.Fecha', 'c.Remision', 'c.Factura', 'crd.FechaAPagar', 'c.SubTotal', 'c.Iva', 'c.Total', 'cr.Status')
            //->whereBetween('cr.Fecha', [$fechainicio, $fechaterminacion])
            ->whereDate('cr.Fecha', '>=', $fechainicio)->whereDate('cr.Fecha', '<=', $fechaterminacion)
            ->where(function($q) use ($numeroproveedor) {
                if($numeroproveedor != ""){
                    $q->where('cr.Proveedor', $numeroproveedor);
                }
            })
            ->orderby('cr.Fecha', 'ASC')
            ->get();
            return DataTables::of($data)
            ->addColumn('Nombre', function($data){ return substr($data->Nombre, 0, 30); })
            ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
            ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
            ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
            ->make(true);
        }
    }
    //generar excel reporte relacion ordenes compra
    public function reporte_relacion_contrarecibos_generar_formato_excel(Request $request){
        return Excel::download(new ReportesRelacionContraReciboExport($request->fechainicialreporte, $request->fechafinalreporte, $request->numeroproveedor, $request->reporte, $this->numerodecimales, $this->empresa), "formatorelacioncontrarecibos-".$request->reporte.".xlsx"); 
    }
}
