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
use App\OrdenCompra;
use App\OrdenCompraDetalle;
use App\Proveedor;
use App\Almacen;
use App\TipoOrdenCompra;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesRelacionOrdenCompraExport;
use DB;

class ReportesOrdenesCompraController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_relacion_ordenes_compra(Request $request){
        $urlgenerarformatoexcel = route('reporte_relacion_ordenes_compra_generar_formato_excel');
        return view('reportes.ordenescompra.reporterelacionordenescompra', compact('urlgenerarformatoexcel'));
    }
    //obtener tipos ordenes de compra
    public function reporte_relacion_ordenes_compra_obtener_tipos_ordenes_compra(Request $request){
        $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->get();
        $select_tipos_ordenes_compra = "<option value='TODOS' selected>TODOS</option>";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }
    //obtener proveedores
    public function reporte_relacion_ordenes_compra_obtener_proveedores(Request $request){
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
    public function reporte_relacion_ordenes_compra_obtener_almacenes(Request $request){
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
    public function reporte_relacion_ordenes_compra_obtener_proveedor_por_numero(Request $request){
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
    public function reporte_relacion_ordenes_compra_obtener_almacen_por_numero(Request $request){
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
    //generar reporte
    public function reporte_relacion_ordenes_compra_generar_reporte(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $reporte = $request->reporte;
        if($reporte == "RELACION"){
            $data = DB::table('Ordenes de Compra as oc')
            ->leftjoin('Proveedores as p', 'oc.Proveedor', '=', 'p.Numero')
            ->select('oc.Orden', 'oc.Proveedor', 'p.Nombre', 'oc.Fecha', 'oc.Plazo', 'oc.Almacen', 'oc.Tipo', 'oc.Referencia', 'oc.Importe', 'oc.Descuento', 'oc.SubTotal', 'oc.Iva', 'oc.Total', 'oc.Obs', 'oc.Status', 'oc.MotivoBaja', 'oc.Usuario')
            ->whereBetween('oc.Fecha', [$fechainicio, $fechaterminacion])
            ->orderby('oc.Serie', 'ASC')
            ->orderby('oc.Folio', 'ASC')
            ->get();
            if($request->numeroproveedor != ""){
                $data = $data->where('Proveedor', $request->numeroproveedor);
            }
            if($request->numeroalmacen != ""){
                $data = $data->where('Almacen', $request->numeroalmacen);
            }
            if($request->tipo != 'TODOS'){
                $data = $data->where('Tipo', $request->tipo);
            }
            if($request->status != 'TODOS'){
                $data = $data->where('Status', $request->status);
            }
            return DataTables::of($data)
            ->addColumn('Nombre', function($data){ return substr($data->Nombre, 0, 30); })
            ->addColumn('Obs', function($data){ return substr($data->Obs, 0, 30); })
            ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 30); })
            ->make(true);
        }else{
            $data = DB::table('Ordenes de Compra as oc')
            ->leftjoin('Proveedores as p', 'oc.Proveedor', '=', 'p.Numero')
            ->leftjoin('Ordenes de Compra Detalles as ocd', 'oc.Orden', '=', 'ocd.Orden')
            ->select('oc.Orden', 'oc.Proveedor', 'p.Nombre', 'oc.Fecha', 'oc.Plazo', 'oc.Almacen', 'oc.Tipo', 'oc.Referencia', 'ocd.Codigo', 'ocd.Descripcion', 'ocd.Unidad', 'ocd.Surtir as Por Surtir', 'ocd.Cantidad', 'ocd.Precio', 'ocd.Importe', 'ocd.Descuento', 'ocd.SubTotal', 'ocd.Iva', 'ocd.Total', 'oc.Obs', 'oc.Status', 'oc.MotivoBaja', 'oc.Usuario')
            ->whereBetween('oc.Fecha', [$fechainicio, $fechaterminacion])
            ->orderby('oc.Serie', 'ASC')
            ->orderby('oc.Folio', 'ASC')
            ->get();
            if($request->numeroproveedor != ""){
                $data = $data->where('Proveedor', $request->numeroproveedor);
            }
            if($request->numeroalmacen != ""){
                $data = $data->where('Almacen', $request->numeroalmacen);
            }
            if($request->tipo != 'TODOS'){
                $data = $data->where('Tipo', $request->tipo);
            }
            if($request->status != 'TODOS'){
                $data = $data->where('Status', $request->status);
            }
            return DataTables::of($data)
            ->addColumn('Nombre', function($data){ return substr($data->Nombre, 0, 30); })
            ->addColumn('Descripcion', function($data){ return substr($data->Descripcion, 0, 30); })
            ->addColumn('Obs', function($data){ return substr($data->Obs, 0, 30); })
            ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 30); })
            ->make(true);
        }
    }
    //generar excel reporte relacion ordenes compra
    public function reporte_relacion_ordenes_compra_generar_formato_excel(Request $request){
        return Excel::download(new ReportesRelacionOrdenCompraExport($request->fechainicialreporte, $request->fechafinalreporte, $request->numeroproveedor, $request->numeroalmacen, $request->tipo, $request->status, $request->reporte, $this->numerodecimales, $this->empresa), "formatorelacionordenescompra-".$request->reporte.".xlsx"); 
    }

}
