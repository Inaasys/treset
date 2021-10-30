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
use App\Exports\ReportesAntiguedadSaldosExport;
use DB;

class ReporteAntiguedadSaldosController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_antiguedad_saldos(Request $request){
        $urlgenerarformatoexcel = route('reporte_antiguedad_saldos_generar_formato_excel');
        return view('reportes.facturas.reporteantiguedadsaldos', compact('urlgenerarformatoexcel'));
    }
    //obtener clientes
    public function reporte_antiguedad_saldos_obtener_clientes(Request $request){
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
    public function reporte_antiguedad_saldos_obtener_cliente_por_numero(Request $request){
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
    public function reporte_antiguedad_saldos_obtener_series(Request $request){
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
    public function reporte_antiguedad_saldos_obtener_serie_por_clave(Request $request){
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
    
    //generar reporte
    public function reporte_antiguedad_saldos_generar_reporte(Request $request){
        $fechacorte = date($request->fechacorte);
        $numerocliente=$request->numerocliente;
        $claveserie=$request->claveserie;
        $departamento=$request->departamento;
        $saldomayor=$request->saldomayor;
        $reporte = $request->reporte;
        switch($reporte){
            case "GENERAL":
                if($fechacorte == date('Y-m-d')){
                    $data = DB::table('Facturas as f')
                    ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                    ->select("f.Cliente AS Cliente", "c.Nombre AS NombreCliente", DB::raw("SUM(f.Total) AS Facturado"), 
                                DB::raw("isnull((select sum(abono) from [cxc detalles] where cliente = f.cliente group by cliente ),0) as AbonosCXC"),
                                DB::raw("isnull((select sum(total) from [notas cliente detalles] where cliente = f.cliente group by cliente ),0) as DescuentosNotasCredito"),
                                DB::raw("isnull((select sum(abono) from [cxc detalles] where cliente = f.cliente group by cliente ),0) + isnull((select sum(total) from [notas cliente detalles] where cliente = f.cliente group by cliente ),0) as TotalPagos"),
                                DB::raw("sum(f.total) - (isnull((select sum(abono) from [cxc detalles] where cliente = f.cliente group by cliente ),0) + isnull((select sum(total) from [notas cliente detalles] where cliente = f.cliente group by cliente ),0)) as SaldoFacturado "))
                    ->where(function($q) use ($numerocliente) {
                        if($numerocliente != ""){
                            $q->where('f.Cliente', $numerocliente);
                        }
                    })
                    ->where(function($q) use ($saldomayor) {
                        if($saldomayor > 0){
                            $q->where('c.Saldo', '>', 0);
                        }
                    })
                    ->orderby('f.Cliente')
                    ->groupby('f.Cliente', 'c.Nombre')
                    ->get();
                }else{
                    $data = DB::table('Facturas as f')
                    ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                    ->select("f.Cliente AS Cliente", "c.Nombre AS NombreCliente", DB::raw("SUM(f.Total) AS Facturado"), 
                                DB::raw("isnull((select sum(abono) from [cxc detalles] where cliente = f.cliente and fecha <= '".$fechacorte."' group by cliente ),0) as AbonosCXC"),
                                DB::raw("isnull((select sum(total) from [notas cliente detalles] where cliente = f.cliente and fecha <= '".$fechacorte."' group by cliente ),0) as DescuentosNotasCredito"),
                                DB::raw("isnull((select sum(abono) from [cxc detalles] where cliente = f.cliente and fecha <= '".$fechacorte."' group by cliente ),0) + isnull((select sum(total) from [notas cliente detalles] where cliente = f.cliente and fecha <= '".$fechacorte."' group by cliente ),0) as TotalPagos"),
                                DB::raw("sum(f.total) - (isnull((select sum(abono) from [cxc detalles] where cliente = f.cliente and fecha <= '".$fechacorte."' group by cliente ),0) + isnull((select sum(total) from [notas cliente detalles] where cliente = f.cliente and fecha <= '".$fechacorte."' group by cliente ),0)) as SaldoFacturado "))
                    ->where('f.Fecha', '<=', $fechacorte)
                    ->where(function($q) use ($numerocliente) {
                        if($numerocliente != ""){
                            $q->where('f.Cliente', $numerocliente);
                        }
                    })
                    ->where(function($q) use ($saldomayor) {
                        if($saldomayor > 0){
                            $q->where('c.Saldo', '>', 0);
                        }
                    })
                    ->where(function($q) use ($departamento) {
                        if($departamento != 'TODOS'){
                            $q->where('f.depto', $departamento);  
                        }
                    })
                    ->orderby('f.Cliente')
                    ->groupby('f.Cliente', 'c.Nombre')
                    ->get();
                }
                return DataTables::of($data)
                ->addColumn('NombreCliente', function($data){ return substr($data->NombreCliente, 0, 30); })
                ->addColumn('Facturado', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Facturado), $this->numerodecimales); })
                ->addColumn('AbonosCXC', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->AbonosCXC), $this->numerodecimales); })
                ->addColumn('DescuentosNotasCredito', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->DescuentosNotasCredito), $this->numerodecimales); })
                ->addColumn('TotalPagos', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->TotalPagos), $this->numerodecimales); })
                ->addColumn('SaldoFacturado', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SaldoFacturado), $this->numerodecimales); })
                ->make(true);
                break;
            case "DETALLES":
                $data = DB::table('Facturas as f')
                ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                ->select("f.Factura", "f.Fecha", "f.Plazo", "f.Cliente AS Cliente", "c.Nombre AS NombreCliente", DB::raw("SUM(f.Total) AS TotalFactura"), 
                            DB::raw("isnull((select sum(abono) from [cxc detalles] where factura = f.factura and fecha <= '".$fechacorte."' ),0) as AbonosCXC"),
                            DB::raw("isnull((select sum(total) from [notas cliente detalles] where factura = f.factura and fecha <= '".$fechacorte."' ),0) as DescuentosNotasCredito"),
                            DB::raw("isnull((select sum(abono) from [cxc detalles] where factura = f.factura and fecha <= '".$fechacorte."' ),0) + isnull((select sum(total) from [notas cliente detalles] where factura = f.factura and fecha <= '".$fechacorte."' ),0) as TotalPagos"),
                            DB::raw("sum(f.total) - (isnull((select sum(abono) from [cxc detalles] where factura = f.factura and fecha <= '".$fechacorte."' ),0) + isnull((select sum(total) from [notas cliente detalles] where factura = f.factura and fecha <= '".$fechacorte."' ),0)) as SaldoFacturado "))
                ->where('f.Fecha', '<=', $fechacorte)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('f.Cliente', $numerocliente);
                    }
                })
                ->where(function($q) use ($saldomayor) {
                    if($saldomayor > 0){
                        $q->where('f.Saldo', '>', 0);
                    }
                })
                ->where(function($q) use ($departamento) {
                    if($departamento != 'TODOS'){
                        $q->where('f.depto', $departamento);  
                    }
                })
                ->orderby('f.Fecha', 'DESC')
                ->groupby('f.Factura', 'f.Fecha', 'f.Plazo', 'f.Cliente', 'c.Nombre')
                ->get();
                return DataTables::of($data)
                ->addColumn('NombreCliente', function($data){ return substr($data->NombreCliente, 0, 30); })
                ->addColumn('TotalFactura', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->TotalFactura), $this->numerodecimales); })
                ->addColumn('AbonosCXC', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->AbonosCXC), $this->numerodecimales); })
                ->addColumn('DescuentosNotasCredito', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->DescuentosNotasCredito), $this->numerodecimales); })
                ->addColumn('TotalPagos', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->TotalPagos), $this->numerodecimales); })
                ->addColumn('SaldoFacturado', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SaldoFacturado), $this->numerodecimales); })
                ->make(true);
                break;
        }
    }
    //generar reporte en excel
    public function reporte_antiguedad_saldos_generar_formato_excel(Request $request){
        return Excel::download(new ReportesAntiguedadSaldosExport($request->fechacorte, $request->numerocliente, $request->claveserie, $request->departamento, $request->saldomayor, $request->reporte, $this->numerodecimales, $this->empresa), "formatoantiguedadsaldos-".$request->reporte.".xlsx"); 
    }


}
