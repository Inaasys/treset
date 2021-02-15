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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesDiariosCajaChicaExport;

class ReporteCajaChicaController extends ConfiguracionSistemaController{
    public function __construct(){
        parent::__construct(); //carga las configuraciones en el controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'Proveedores')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }
    public function reporte_caja_chica(){
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('proveedores_guardar_configuracion_tabla');
        $urlgenerarformatoexcelcajachica = route('reporte_caja_chica_generar_formato_excel');
        return view('reportes.compras.reportecajachica', compact('configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcelcajachica'));
        
    }
    //obtener proveedores
    public function reporte_caja_chica_obtener_proveedores(Request $request){
        if($request->ajax()){
            $numeroabuscar = $request->numeroabuscar;
            $data = Proveedor::where('Status', 'ALTA')->where('Numero', 'like', '%' . $numeroabuscar . '%')->orderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarproveedor('.$data->Numero.',\''.$data->Nombre .',1\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }        
    } 
    //generar reporte caja chica
    public function generar_reporte_caja_chica(Request $request){
        $fechahoy = Carbon::parse($request->fechafinalreporte);//fecha de la que se realizar el reporte
        $compras = Compra::whereBetween('Fecha', [$request->fechainicialreporte, $request->fechafinalreporte])->where('Tipo', 'CAJA CHICA')->where('Status', $request->statuscompra)->get();
        $data=array();
        $sumasubtotal = 0;
        $sumaiva = 0;
        $sumaivaretencion = 0;
        $sumatotal = 0;
        foreach ($compras as $c){
            $sumasubtotal = $sumasubtotal + $c->SubTotal;
            $sumaiva = $sumaiva + $c->Iva;
            $sumaivaretencion = $sumaivaretencion + $c->IvaRetencion;
            $sumatotal = $sumatotal + $c->Total;
            $proveedor = Proveedor::where('Numero', $c->Proveedor)->first();
            $data[]=array(
                      "compra"=>$c,
                      "fechacompra"=>Carbon::parse($c->Fecha)->toDateString(),
                      "movimientocompra"=>$c->Compra,
                      "proveedor"=>$c->EmisorNombre,
                      "UUID"=>$c->UUID,
                      "conceptopago"=>"",
                      "observacionescompra"=>$c->Obs,
                      "subtotal"=>number_format(Helpers::convertirvalorcorrecto($c->SubTotal), $this->numerodecimales),
                      "iva"=>number_format(Helpers::convertirvalorcorrecto($c->Iva), $this->numerodecimales),
                      "ivaretencion"=>number_format(Helpers::convertirvalorcorrecto($c->IvaRetencion), $this->numerodecimales),
                      "imphospedaje"=>"",
                      "total"=>number_format(Helpers::convertirvalorcorrecto($c->Total), $this->numerodecimales),
                      "depto"=>"",
                      "sumasubtotal"=>number_format(Helpers::convertirvalorcorrecto($sumasubtotal), $this->numerodecimales),
                      "sumaiva"=>number_format(Helpers::convertirvalorcorrecto($sumaiva), $this->numerodecimales),
                      "sumaivaretencion"=>number_format(Helpers::convertirvalorcorrecto($sumaivaretencion), $this->numerodecimales),
                      "sumatotal"=>number_format(Helpers::convertirvalorcorrecto($sumatotal), $this->numerodecimales)
            );
        }
        $empresa = $this->empresa;
        $fechahoy = Carbon::now()->toDateString();
        return DataTables::of($data)
        ->addColumn('operaciones', function($data){
            $checkbox = '<input type="checkbox" name="checkcompra'.$data['movimientocompra'].'" id="idcheckcompra'.$data['movimientocompra'].'" class="filled-in checkcompra" value="'.$data['movimientocompra'].'" onchange="construirarraycheckscompras()" checked/><label for="idcheckcompra'.$data['movimientocompra'].'">Seleccionar</label>'; 
            return $checkbox;
        })
        ->rawColumns(['operaciones'])
        ->make(true);
    }
    //generar formato en excel de la caja chica
    public function reporte_caja_chica_generar_formato_excel(Request $request){
        return Excel::download(new ReportesDiariosCajaChicaExport($request->fechainicialreporte, $request->fechafinalreporte, $request->statuscompra, $request->string_compras, $this->numerodecimales, $this->empresa), "formatocajachica.xlsx"); 
    }
}
