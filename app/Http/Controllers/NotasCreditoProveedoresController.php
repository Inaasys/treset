<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use DB;
use PDF;
use App\NotaProveedor;
use App\NotaProveedorDetalle;
use App\Compra;
use App\Proveedor;
use App\Almacen;

class NotasCreditoProveedoresController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }
    
    public function notas_credito_proveedores(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->email, 'ContraRecibos');
        return view('registros.notascreditoproveedores.notascreditoproveedores', compact('serieusuario'));
    }
    //obtener registros tabla
    public function notas_credito_proveedores_obtener(Request $request){
        if($request->ajax()){
            $fechahoy = Carbon::now()->toDateString();
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = DB::table('Notas Proveedor AS np')
            ->Join('Proveedores AS p', 'np.Proveedor', '=', 'p.Numero')
            ->select('np.Nota AS Nota', 'np.Serie AS Serie', 'np.Folio AS Folio', 'np.Proveedor AS Proveedor', 'p.Nombre AS Nombre', 'np.Fecha AS Fecha', 'np.NotaProveedor AS NotaProveedor', 'np.Almacen AS Almacen', 'np.UUID AS UUID', 'np.SubTotal AS SubTotal', 'np.Iva AS Iva', 'np.Total AS Total', 'np.Obs AS Obs', 'np.Status AS Status', 'np.MotivoBaja AS MotivoBaja', 'np.Equipo AS Equipo', 'np.Usuario AS Usuario', 'np.Periodo AS Periodo')
            ->where('np.Periodo', $periodo)
            ->orderBy('np.Folio', 'DESC')
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        if($data->Status != 'BAJA'){
                            $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Nota .'\')"><i class="material-icons">mode_edit</i></div> '. 
                            '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Nota .'\')"><i class="material-icons">cancel</i></div>  ';
                        }else{
                            $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Nota .'\')"><i class="material-icons">mode_edit</i></div> ';
                        }
                        return $boton;
                    })
                    ->addColumn('SubTotal', function($data){
                        $subtotal = Helpers::convertirvalorcorrecto($data->SubTotal);
                        return $subtotal;
                    })
                    ->addColumn('Iva', function($data){
                        $iva = Helpers::convertirvalorcorrecto($data->Iva);
                        return $iva;
                    })
                    ->addColumn('Total', function($data){
                        $total = Helpers::convertirvalorcorrecto($data->Total);
                        return $total;
                    })
                    ->rawColumns(['operaciones','SubTotal','Iva','Total'])
                    ->make(true);
        } 
    }
    //obtener ultimo folio
    public function notas_credito_proveedores_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofoliotablamodulos('App\NotaProveedor');
        return response()->json($folio);
    }
    //obtener proveedor
    public function notas_credito_proveedores_obtener_proveedores(Request $request){
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
    //obtener almacenes
    public function notas_credito_proveedores_obtener_almacenes(Request $request){
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
    //obtener compras
    public function notas_credito_proveedores_obtener_compras(Request $request){
        if($request->ajax()){
            $data = Compra::where('Proveedor', $request->numeroproveedor)
                                ->where('AutorizadoPor', '<>', '')
                                ->where(function ($query) {
                                    $query->where('Status', 'POR SURTIR')
                                        ->orWhere('Status', 'BACKORDER');
                                })
                                ->orderBy('Folio', 'DESC')
                                ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarordencompra('.$data->Folio.',\''.$data->Orden .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){
                        return Helpers::fecha_espanol($data->Fecha);
                    })
                    ->rawColumns(['operaciones','Fecha'])
                    ->make(true);
        }
    }



    

    //buscar folio on key up
    public function notas_credito_proveedores_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = NotaProveedor::where('Nota', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Nota.'\')"><i class="material-icons">done</i></div> ';
                    return $boton;
                })
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->rawColumns(['operaciones','Total'])
                ->make(true);
        } 
    }
    //generacion de formato en PDF
    public function notas_credito_proveedores_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $notascreditoproveedor = NotaProveedor::whereIn('Nota', $request->arraypdf)->orderBy('Folio', 'ASC')->take(500)->get(); 
        }else{
            //$contrarecibos = ContraRecibo::where('Fecha', $request->anopdf)->get(); 
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $notascreditoproveedor = NotaProveedor::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($notascreditoproveedor as $ncp){
            $notascreditoproveedordetalle = NotaProveedorDetalle::where('Nota', $ncp->Nota)->get();
            $datadetalle=array();
            foreach($notascreditoproveedordetalle as $ncpd){
                $contarcompradetalle = Compra::where('Compra', $ncpd->Compra)->count();
                $compradetalle = Compra::where('Compra', $ncpd->Compra)->first();
                if($contarcompradetalle == 0){
                    $remisiondetalle = "";
                    $facturadetalle = "";
                }else{
                    $remisiondetalle = $compradetalle->Remision;
                    $facturadetalle = $compradetalle->Factura;
                }
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($ncpd->Cantidad),
                    "codigodetalle"=>$ncpd->Codigo,
                    "descripciondetalle"=>$ncpd->Descripcion,
                    "compradetalle"=>$ncpd->Compra,
                    "remisiondetalle"=>$remisiondetalle,
                    "facturadetalle"=>$facturadetalle,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($ncpd->Precio),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($ncpd->SubTotal)
                );
            } 
            $proveedor = Proveedor::where('Numero', $ncp->Proveedor)->first();
            $data[]=array(
                "notacreditoproveedor"=>$ncp,
                "descuentonotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Descuento),
                "subtotalnotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->SubTotal),
                "ivanotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Iva),
                "totalnotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Total),
                "proveedor" => $proveedor,
                "datadetalle" => $datadetalle
            );
        }
        //$footerHtml = view()->make('seccionespdf.footer', compact('fechaformato'))->render();
        $pdf = PDF::loadView('registros.notascreditoproveedores.formato_pdf_notascreditoproveedores', compact('data'))
        //->setOption('footer-html', $footerHtml, 'Página [page]')
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-bottom', 10);
        //return $pdf->download('contrarecibos.pdf');
        return $pdf->stream();
    }

}
