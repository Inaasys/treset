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
use App\CuentaXCobrar;
use App\CuentaXCobrarDetalle;
use App\Cliente;
use App\Agente;
use App\Serie;
use App\Producto;
use App\TipoOrdenCompra;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesFacturasVencidasExport;
use DB;
use PDF;
use Illuminate\Support\Collection;

class ReporteFacturasVencidasController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_facturas_vencidas(Request $request){
        $urlgenerarformatoexcel = route('reporte_facturas_vencidas_generar_formato_excel');
        $urlgenerarformatopdf = route('reporte_facturas_vencidas_generar_formato_pdf');
        return view('reportes.facturas.reportefacturasvencidas', compact('urlgenerarformatoexcel','urlgenerarformatopdf'));
    }
    
    //obtener clientes
    public function reporte_facturas_vencidas_obtener_clientes(Request $request){
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
    public function reporte_facturas_vencidas_obtener_cliente_por_numero(Request $request){
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
    //obtener series
    public function reporte_facturas_vencidas_obtener_series(Request $request){
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
    public function reporte_facturas_vencidas_obtener_serie_por_clave(Request $request){
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

    
    //generar reporte en excel
    public function reporte_facturas_vencidas_generar_formato_excel(Request $request){
        return Excel::download(new ReportesFacturasVencidasExport($request->fechacorte, $request->numerocliente, $request->claveserie, $request->departamento, $request->reporte, $this->numerodecimales, $this->empresa), "formatofacturasvencidas-".$request->reporte.".xlsx"); 
    }

    //generar reporte
    public function reporte_facturas_vencidas_generar_reporte(Request $request){
        $fechacorte = date($request->fechacorte);
        $numerocliente=$request->numerocliente;
        $claveserie=$request->claveserie;
        $departamento=$request->departamento;
        $reporte = $request->reporte;
        switch($reporte){
            case "GENERAL":
                $data = DB::table('Facturas as f')
                    ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                    ->select('f.Factura', 'f.Serie', 'f.Folio', 'f.Esquema', 'f.Cliente', 'f.Status', 'c.Nombre as NombreCliente', 'f.Fecha', 'f.Depto', 'f.FormaPago', 'f.SubTotal', 'f.Iva', 'f.Total', 'f.Abonos', 'f.Descuentos', 'f.Saldo')
                    ->where('f.Esquema', 'CFDI')
                    ->where('f.FormaPago', '99')
                    ->where('f.Status', 'POR COBRAR')
                    ->where('f.Fecha', '<=', $fechacorte)
                    ->where(function($q) use ($numerocliente) {
                        if($numerocliente != ""){
                            $q->where('f.Cliente', $numerocliente);
                        }
                    })
                    ->where(function($q) use ($claveserie) {
                        if($claveserie != ""){
                            $q->where('f.Serie', $claveserie);
                        }
                    })
                    ->where(function($q) use ($departamento) {
                        if($departamento != 'TODOS'){
                            $q->where('f.depto', $departamento);  
                        }
                    })
                    ->orderby('Folio')
                    ->orderby('Serie')
                    ->get();
                $arreglo = new Collection;
                foreach($data as $d){
                    $fechafactura = Carbon::parse($d->Fecha);
                    $fechahoy = Carbon::now();
                    if($fechafactura->year != $fechahoy->year || $fechafactura->month != $fechahoy->month){
                        $arreglo->push([
                            "Factura"=>$d->Factura,
                            "Serie"=>$d->Serie,
                            "Folio"=>$d->Folio,
                            "Esquema"=>$d->Esquema,
                            "Status"=>$d->Status,
                            "Cliente"=>$d->Cliente,
                            "Status"=>$d->Status,
                            "NombreCliente"=>$d->NombreCliente,
                            "Fecha"=>$d->Fecha,
                            "Depto"=>$d->Depto,
                            "FormaPago"=>$d->FormaPago,
                            "SubTotal"=>$d->SubTotal,
                            "Iva"=>$d->Iva,
                            "Total"=>$d->Total,
                            "Abonos"=>$d->Abonos,
                            "Descuentos"=>$d->Descuentos,
                            "Saldo"=>$d->Saldo,
                        ]);
                    }
                }
                return Datatables::of($arreglo)->make(true);                    
                break;
            case "DETALLES":

                break;
        }
    }

    //generar pdf
    public function reporte_facturas_vencidas_generar_formato_pdf(Request $request){
        $fechacorte = date($request->fechacorte);
        $numerocliente=$request->numerocliente;
        $claveserie=$request->claveserie;
        $departamento=$request->departamento;
        $reporte = $request->reporte;
        switch($reporte){
            case "GENERAL":
                $data = DB::table('Facturas as f')
                    ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                    ->select('f.Factura', 'f.Serie', 'f.Folio', 'f.Esquema', 'f.Cliente', 'f.Status', 'c.Nombre as NombreCliente', 'f.Fecha', 'f.Depto', 'f.FormaPago', 'f.SubTotal', 'f.Iva', 'f.Total', 'f.Abonos', 'f.Descuentos', 'f.Saldo')
                    ->where('f.Esquema', 'CFDI')
                    ->where('f.FormaPago', '99')
                    ->where('f.Status', 'POR COBRAR')
                    ->where('f.Fecha', '<=', $fechacorte)
                    ->where(function($q) use ($numerocliente) {
                        if($numerocliente != ""){
                            $q->where('f.Cliente', $numerocliente);
                        }
                    })
                    ->where(function($q) use ($claveserie) {
                        if($claveserie != ""){
                            $q->where('f.Serie', $claveserie);
                        }
                    })
                    ->where(function($q) use ($departamento) {
                        if($departamento != 'TODOS'){
                            $q->where('f.depto', $departamento);  
                        }
                    })
                    ->orderby('Folio')
                    ->orderby('Serie')
                    ->get();
                $consultarep = new Collection;
                foreach($data as $d){
                    $fechafactura = Carbon::parse($d->Fecha);
                    $fechahoy = Carbon::now();
                    if($fechafactura->year != $fechahoy->year || $fechafactura->month != $fechahoy->month){
                        $consultarep->push([
                            "Factura"=>$d->Factura,
                            "Serie"=>$d->Serie,
                            "Folio"=>$d->Folio,
                            "Esquema"=>$d->Esquema,
                            "Status"=>$d->Status,
                            "Cliente"=>$d->Cliente,
                            "Status"=>$d->Status,
                            "NombreCliente"=>$d->NombreCliente,
                            "Fecha"=>$d->Fecha,
                            "Depto"=>$d->Depto,
                            "FormaPago"=>$d->FormaPago,
                            "SubTotal"=>$d->SubTotal,
                            "Iva"=>$d->Iva,
                            "Total"=>$d->Total,
                            "Abonos"=>$d->Abonos,
                            "Descuentos"=>$d->Descuentos,
                            "Saldo"=>$d->Saldo,
                        ]);
                    }
                }                  
                break;
            case "DETALLES":

                break;
        }
        $data = array(
            'fechacorte' => $fechacorte,
            'numerocliente' => $numerocliente,
            'claveserie' => $claveserie,
            'departamento' => $departamento,
            'reporte' => $reporte,
            'numerodecimales' => $this->numerodecimales, 
            'empresa' => $this->empresa,
            'consultarep' => $consultarep
        );
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('reportes.facturas.formato_pdf_reportefacturasvencidas', compact('data'))
        ->setPaper('Letter')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();

    }

}
