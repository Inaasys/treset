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
use App\Comprobante;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteRelacionTimbresUtilizadosExport;
use DB;

class ReporteTimbresUtilizadosController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_relacion_timbres_utilizados(Request $request){
        $urlgenerarformatoexcel = route('reporte_relacion_timbres_utilizados_generar_formato_excel');
        return view('reportes.facturas.reporterelaciontimbresutilizados', compact('urlgenerarformatoexcel'));
    }

    //obtener series
    public function reporte_relacion_timbres_utilizados_obtener_series(Request $request){
        if($request->ajax()){
            $data = Comprobante::select('Serie')->groupby('Serie')->get();
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
    public function reporte_relacion_timbres_utilizados_obtener_serie_por_clave(Request $request){
        $claveserie = '';
        $existeserie = Comprobante::select('Serie')->where('Serie', $request->claveserie)->groupby('Serie')->count();
        if($existeserie > 0){
            $serie = Comprobante::select('Serie')->where('Serie', $request->claveserie)->groupby('Serie')->first();
            $claveserie = $serie->Serie;
        }
        $data = array(
            'claveserie' => $claveserie,
        );
        return response()->json($data);
    }

    //generar reporte en excel
    public function reporte_relacion_timbres_utilizados_generar_formato_excel(Request $request){
        return Excel::download(new ReporteRelacionTimbresUtilizadosExport($request->fechainicialreporte, $request->fechafinalreporte, $request->claveserie, $request->tipocomprobante, $request->reporte, $this->numerodecimales, $this->empresa), "formatorelaciontimbresutilizados-".$request->reporte.".xlsx");
    }

    //generar reporte
    public function reporte_relacion_timbres_utilizados_generar_reporte(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $claveserie=$request->claveserie;
        $reporte = $request->reporte;
        $tipocomprobante=$request->tipocomprobante;
        switch($reporte){
            case "GENERAL":
                $data = DB::table('Comprobantes as c')
                    ->leftjoin('Facturas as f','f.Factura',DB::raw("(c.Folio+'-'+c.Serie)"))
                    ->leftjoin('CxC as cx','cx.Pago',DB::raw("(c.Folio+'-'+c.Serie)"))
                    ->leftjoin('Notas Cliente as nc','nc.Nota',DB::raw("(c.Folio+'-'+c.Serie)"))
                    ->leftjoin('Notas Proveedor as np','np.Nota',DB::raw("(c.Folio+'-'+c.Serie)"))
                    ->select('c.Comprobante', 'c.Tipo', 'c.Serie', 'c.Folio', 'f.Total as TotalSistema','f.Status','c.Total as TotalCFDI','c.UUID', 'c.EmisorRfc', 'c.ReceptorRfc', 'c.FormaPago', 'c.MetodoPago', 'c.UsoCfdi')
                    ->whereDate('c.Fecha', '>=', $fechainicio)->whereDate('c.Fecha', '<=', $fechaterminacion)
                    ->where(function($q) use ($claveserie) {
                        if($claveserie != null){
                            $q->where('c.Serie', $claveserie);
                        }
                    })
                    ->where(function($q) use ($tipocomprobante) {
                        if($tipocomprobante != 'TODOS'){
                            $q->where('c.Comprobante', $tipocomprobante);
                        }
                    })
                    ->orderby('c.Folio')
                    ->orderby('c.Comprobante')
                    ->get();
                return DataTables::of($data)
                ->make(true);
                break;
            case "TOTALES":
                $data = DB::table('Comprobantes')
                    ->select(DB::raw('COUNT(Comprobante) as Total'))
                    ->whereDate('Fecha', '>=', $fechainicio)->whereDate('Fecha', '<=', $fechaterminacion)
                    ->where(function($q) use ($claveserie) {
                        if($claveserie != null){
                            $q->where('Serie', $claveserie);
                        }
                    })
                    ->where(function($q) use ($tipocomprobante) {
                        if($tipocomprobante != 'TODOS'){
                            $q->where('Comprobante', $tipocomprobante);
                        }
                    })
                    ->get();
                return DataTables::of($data)
                ->make(true);
                break;
        }
    }
}
