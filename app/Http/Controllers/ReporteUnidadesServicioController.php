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
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\TipoUnidad;
use App\TipoOrdenTrabajo;
use App\Tecnico;
use App\Cliente;
use App\Vine;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesUnidadesServicioExport;
use DB;
use Illuminate\Support\Collection;
use PDF;

class ReporteUnidadesServicioController extends ConfiguracionSistemaController{
    public function __construct(){
        parent::__construct(); //carga las configuraciones en el controlador ConfiguracionSistemaController
    }

    public function reporte_unidades_servicio(){
        $urlgenerarformatoexcel = route('reporte_unidades_servicio_generar_formato_excel');
        $urlgenerarformatopdf = route('reporte_unidades_servicio_generar_formato_pdf');
        $tipos_ordenes_trabajo = TipoOrdenTrabajo::where('Status', 'ALTA')->get();
        $tipos_unidades = TipoUnidad::where('Status', 'ALTA')->get();
        return view('reportes.ordenestrabajo.unidadesservicio', compact('urlgenerarformatoexcel', 'urlgenerarformatopdf', 'tipos_ordenes_trabajo', 'tipos_unidades'));
    }


    public function reporte_unidades_servicio_obtener_clientes_facturaa(Request $request){
        if($request->ajax()){
            $data = Cliente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclientefacturaa('.$data->Numero.',\''.$data->Nombre.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    public function reporte_unidades_servicio_obtener_clientes_delcliente(Request $request){
        if($request->ajax()){
            $data = Cliente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclientedelcliente('.$data->Numero.',\''.$data->Nombre.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    public function reporte_unidades_servicio_obtener_vines(Request $request){
        if($request->ajax()){
            $data = Vine::where('Status', 'ALTA')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarvin(\''.$data->Economico.'\',\''.$data->Vin.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    public function reporte_unidades_servicio_obtener_cliente_facturaa_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existecliente = Cliente::where('Numero', $request->numeroclientefacturara)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numeroclientefacturara)->where('Status', 'ALTA')->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data);
    }

    public function reporte_unidades_servicio_obtener_cliente_delcliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existecliente = Cliente::where('Numero', $request->numeroclientedelcliente)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numeroclientedelcliente)->where('Status', 'ALTA')->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data);
    }

    public function reporte_unidades_servicio_obtener_vin_por_clave(Request $request){
        $economico = '';
        $vin = '';
        $existevin = Vine::where('Vin', $request->numerovin)->where('Status', 'ALTA')->count();
        if($existevin > 0){
            $v = DB::table('Vines')->where('Vin', $request->numerovin)->where('Status', 'ALTA')->first();
            $economico = $v->Economico;
            $vin = $v->Vin;
        }
        $data = array(
            'economico' => $economico,
            'vin' => $vin
        );
        return response()->json($data); 
    }

    public function reporte_unidades_servicio_generar_reporte(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $numeroclientefacturara=$request->numeroclientefacturara;
        $numeroclientedelcliente=$request->numeroclientedelcliente;
        $numerovin=$request->numerovin;
        $tipoorden=$request->tipoorden;
        $tipounidad=$request->tipounidad;
        $status=$request->status;
        $reporte = $request->reporte;
        switch($reporte){
            case "NORMAL":
                $data = DB::table('Ordenes de Trabajo as ot')
                            ->join('Ordenes de Trabajo Detalles as otd', 'ot.Orden', '=', 'otd.Orden')
                            ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                            ->select('otd.Orden', 'ot.Cliente', 'c.Nombre as NombreCliente', 'ot.Fecha', 'ot.Entrega', 'ot.Facturada', 'ot.Status', 'ot.Vin', 'otd.Codigo', DB::raw("case otd.departamento+otd.cargo when 'SERVICIO'+'SERVICIO' then (select top 1 servicio from servicios where codigo = otd.codigo) else (select top 1 producto from productos where codigo = otd.codigo) end as Descripcion"), 'otd.Cantidad', 'otd.Precio', 'otd.Dcto', 'otd.Descuento', 'otd.SubTotal', 'otd.Iva', 'otd.Total', 'otd.Costo', 'otd.Utilidad', 'otd.Cargo', 'otd.Compra', 'otd.Traspaso', 'ot.Kilometros', 'ot.Economico', 'ot.Año', 'ot.Modelo', 'ot.Marca', 'otd.Tecnico1', DB::raw("(select top 1 nombre from tecnicos where numero = otd.tecnico1) as NombreTecnico1"), 'otd.Horas1', 'otd.Tecnico2', DB::raw("(select top 1 nombre from tecnicos where numero = otd.tecnico2) as NombreTecnico2"), 'otd.Horas2', 'otd.Tecnico3', DB::raw("(select top 1 nombre from tecnicos where numero = otd.tecnico3) as NombreTecnico3"), 'otd.Horas3', 'otd.Tecnico4', DB::raw("(select top 1 nombre from tecnicos where numero = otd.tecnico4) as NombreTecnico4"), 'otd.Horas4', 'ot.Falla', 'ot.Causa', 'ot.Correccion')
                            ->whereDate('ot.Fecha', '>=', $fechainicio)->whereDate('ot.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numeroclientefacturara) {
                                if($numeroclientefacturara != ""){
                                    $q->where('ot.Cliente', $numeroclientefacturara);
                                }
                            })
                            ->where(function($q) use ($numeroclientedelcliente) {
                                if($numeroclientedelcliente != ""){
                                    $q->where('ot.DelCliente', $numeroclientedelcliente);
                                }
                            })
                            ->where(function($q) use ($numerovin) {
                                if($numerovin != 'TODOS'){
                                    $q->where('ot.Vin', 'like', '%' . $numerovin . '%');
                                }
                            })
                            ->where(function($q) use ($tipoorden) {
                                if($tipoorden != 'TODOS'){
                                    $q->where('ot.Tipo', $tipoorden);
                                }
                            })
                            ->where(function($q) use ($tipounidad) {
                                if($tipounidad != 'TODOS'){
                                    $q->where('ot.Unidad', $tipounidad);
                                }
                            })
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    if($status == 'ABIERTAS'){
                                        $q->where('ot.Status', 'ABIERTA');
                                    }elseif($status == 'FACTURADAS'){
                                        $q->where('ot.Status', '<>', 'ABIERTA')->where('ot.Status', '<>', 'BAJA');
                                    }elseif($status == 'BAJA'){
                                        $q->where('ot.Status', 'BAJA');            
                                    }
                                }
                            })
                            ->orderby('ot.Serie', 'ASC')
                            ->orderby('ot.Folio', 'ASC')
                            ->orderby('otd.Item', 'ASC')
                            ->get();
                return DataTables::of($data)
                ->addColumn('Cantidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Cantidad), $this->numerodecimales); })
                ->addColumn('Precio', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Precio), $this->numerodecimales); })
                ->addColumn('Dcto', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Dcto), $this->numerodecimales); })
                ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->addColumn('Horas1', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Horas1), $this->numerodecimales); })
                ->addColumn('Horas2', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Horas2), $this->numerodecimales); })
                ->addColumn('Horas3', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Horas3), $this->numerodecimales); })
                ->addColumn('Horas4', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Horas4), $this->numerodecimales); })
                ->addColumn('Fecha', function($data){ return Helpers::fecha_espanol_datetime($data->Fecha); })
                ->addColumn('Entrega', function($data){ return Helpers::fecha_espanol_datetime($data->Entrega); })
                ->addColumn('Facturada', function($data){ return Helpers::fecha_espanol_datetime($data->Facturada); })
                ->make(true);
                break;
        }
    }

    //Route::get('/reporte_unidades_servicio_generar_formato_pdf', 'ReporteUnidadesServicioController@reporte_unidades_servicio_generar_formato_pdf')->name('reporte_unidades_servicio_generar_formato_pdf')->middleware('revisaraccesomenu:menureporteunidadesservicio');

    //generar reporte en excel
    public function reporte_unidades_servicio_generar_formato_excel(Request $request){
        return Excel::download(new ReportesUnidadesServicioExport($request->fechainicialreporte, $request->fechafinalreporte, $request->numeroclientefacturara, $request->numeroclientedelcliente, $request->numerovin, $request->tipoorden, $request->tipounidad, $request->status, $request->reporte, $this->numerodecimales, $this->empresa), "formatoreporteunidadesservicio-".$request->reporte.".xlsx");    
    }

    //generar formato pdf
    public function reporte_unidades_servicio_generar_formato_pdf(Request $request){
        
    }

}
