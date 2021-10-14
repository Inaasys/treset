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
use App\TipoOrdenCompra;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesRelacionCompraExport;
use DB;

class ReportesComprasController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_relacion_compras(Request $request){
        $urlgenerarformatoexcel = route('reporte_relacion_compras_generar_formato_excel');
        return view('reportes.compras.reporterelacioncompras', compact('urlgenerarformatoexcel'));
    }
    //obtener tipos ordenes de compra
    public function reporte_relacion_compras_obtener_tipos_ordenes_compra(Request $request){
        $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->get();
        $select_tipos_ordenes_compra = "<option value='TODOS' selected>TODOS</option>";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }
    //obtener proveedores
    public function reporte_relacion_compras_obtener_proveedores(Request $request){
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
    public function reporte_relacion_compras_obtener_almacenes(Request $request){
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
    public function reporte_relacion_compras_obtener_proveedor_por_numero(Request $request){
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
    public function reporte_relacion_compras_obtener_almacen_por_numero(Request $request){
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
    public function reporte_relacion_compras_generar_reporte(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $reporte = $request->reporte;
        if($reporte == "RELACION"){
            $data = DB::table('Compras as c')
            ->leftjoin('Proveedores as p', 'c.Proveedor', '=', 'p.Numero')
            ->select('c.Compra', 'c.Proveedor', 'p.Nombre', 'c.Fecha', 'c.Plazo', DB::raw("c.Fecha+c.Plazo as Vence"), 'c.Remision', 'c.Factura', 'c.Movimiento', 'c.Almacen', 'c.Tipo', 'c.Importe', 'c.Descuento', 'c.SubTotal', 'c.Iva', 'c.Total', 'c.Abonos', 'c.Descuentos', 'c.Saldo', 'c.Obs', 'c.Status', 'c.MotivoBaja', 'c.Usuario', 'p.Rfc', 'p.Calle', 'p.NoExterior', 'p.Colonia', 'p.Municipio', 'p.Estado', 'p.CodigoPostal', 'p.Contacto', 'p.Telefonos', 'p.Email1')
            ->whereBetween('c.Fecha', [$fechainicio, $fechaterminacion])
            ->orderby('c.Serie', 'ASC')
            ->orderby('c.Folio', 'ASC')
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
            if($request->movimiento != 'TODOS'){
                $data = $data->where('Movimiento', $request->movimiento);
            }
            if($request->status != 'TODOS'){
                $data = $data->where('Status', $request->status);
            }
            return DataTables::of($data)
            ->addColumn('Nombre', function($data){ return substr($data->Nombre, 0, 30); })
            ->addColumn('Obs', function($data){ return substr($data->Obs, 0, 30); })
            ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 30); })
            ->addColumn('Calle', function($data){ return substr($data->Calle, 0, 30); })
            ->addColumn('Colonia', function($data){ return substr($data->Colonia, 0, 30); })
            ->addColumn('Contacto', function($data){ return substr($data->Contacto, 0, 30); })
            ->addColumn('Telefonos', function($data){ return substr($data->Telefonos, 0, 30); })
            ->addColumn('Email1', function($data){ return substr($data->Email1, 0, 30); })
            ->make(true);
        }else if($reporte == "DETALLES"){
            $data = DB::table('Compras as c')
            ->leftjoin('Proveedores as p', 'c.Proveedor', '=', 'p.Numero')
            ->leftjoin('Compras Detalles as cd', 'c.Compra', '=', 'cd.Compra')
            ->select('c.Compra', 'c.Proveedor', 'p.Nombre', 'c.Fecha', 'c.Plazo', DB::raw("c.Fecha+c.Plazo as Vence"), 'c.Remision', 'c.Factura', 'c.Movimiento', 'c.Almacen', 'c.Tipo', 'cd.Codigo', 'cd.Descripcion', 'cd.Unidad', 'cd.Cantidad', 'cd.Precio', 'cd.Importe', 'cd.Descuento', 'cd.SubTotal', 'cd.Iva', 'cd.Total', 'c.Obs AS ObsCompra', 'cd.Obs As ObsDetalle', 'c.Status', 'c.MotivoBaja', 'c.Usuario', 'p.Rfc', 'p.Calle', 'p.NoExterior', 'p.Colonia', 'p.Municipio', 'p.Estado', 'p.CodigoPostal', 'p.Contacto', 'p.Telefonos', 'p.Email1')
            ->whereBetween('c.Fecha', [$fechainicio, $fechaterminacion])
            ->orderby('c.Serie', 'ASC')
            ->orderby('c.Folio', 'ASC')
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
            if($request->movimiento != 'TODOS'){
                $data = $data->where('Movimiento', $request->movimiento);
            }
            if($request->status != 'TODOS'){
                $data = $data->where('Status', $request->status);
            }
            return DataTables::of($data)
            ->addColumn('Nombre', function($data){ return substr($data->Nombre, 0, 30); })
            ->addColumn('Descripcion', function($data){ return substr($data->Descripcion, 0, 30); })
            ->addColumn('ObsCompra', function($data){ return substr($data->ObsCompra, 0, 30); })
            ->addColumn('ObsDetalle', function($data){ return substr($data->ObsDetalle, 0, 30); })
            ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 30); })
            ->addColumn('Calle', function($data){ return substr($data->Calle, 0, 30); })
            ->addColumn('Colonia', function($data){ return substr($data->Colonia, 0, 30); })
            ->addColumn('Contacto', function($data){ return substr($data->Contacto, 0, 30); })
            ->addColumn('Telefonos', function($data){ return substr($data->Telefonos, 0, 30); })
            ->addColumn('Email1', function($data){ return substr($data->Email1, 0, 30); })
            ->make(true);
        }else{
            $data = DB::table('Compras as c')
            ->leftjoin('Proveedores as p', 'c.Proveedor', '=', 'p.Numero')
            ->select('p.Numero', 'p.Nombre', DB::raw("SUM(c.Total) as Totalc"), 'p.Rfc', 'p.Calle', 'p.NoExterior', 'p.Colonia', 'p.Municipio', 'p.Estado', 'p.CodigoPostal', 'p.Contacto', 'p.Telefonos', 'p.Email1')
            ->whereBetween('c.Fecha', [$fechainicio, $fechaterminacion])
            ->where('c.Status', '<>', 'BAJA')
            ->groupby('p.Numero', 'p.Nombre', 'p.Rfc', 'p.Calle', 'p.NoExterior', 'p.Colonia', 'p.Municipio', 'p.Estado', 'p.CodigoPostal', 'p.Contacto', 'p.Telefonos', 'p.Email1')
            ->orderby(DB::raw("SUM(c.Total)"), 'DESC')
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
            if($request->movimiento != 'TODOS'){
                $data = $data->where('Movimiento', $request->movimiento);
            }
            if($request->status != 'TODOS'){
                $data = $data->where('Status', $request->status);
            }
            return DataTables::of($data)
            ->addColumn('Nombre', function($data){ return substr($data->Nombre, 0, 30); })
            ->addColumn('Calle', function($data){ return substr($data->Calle, 0, 30); })
            ->addColumn('Colonia', function($data){ return substr($data->Colonia, 0, 30); })
            ->addColumn('Contacto', function($data){ return substr($data->Contacto, 0, 30); })
            ->addColumn('Telefonos', function($data){ return substr($data->Telefonos, 0, 30); })
            ->addColumn('Email1', function($data){ return substr($data->Email1, 0, 30); })
            ->addColumn('Totalc', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Totalc), $this->numerodecimales); })
            ->make(true);
        }
    }
    //generar excel reporte relacion ordenes compra
    public function reporte_relacion_compras_generar_formato_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new ReportesRelacionCompraExport($request->fechainicialreporte, $request->fechafinalreporte, $request->numeroproveedor, $request->numeroalmacen, $request->tipo, $request->movimiento, $request->status, $request->reporte, $this->numerodecimales, $this->empresa), "formatorelacioncompras-".$request->reporte.".xlsx"); 
    }
}
