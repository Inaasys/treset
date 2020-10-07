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
use App\Cliente;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesDiariosVentasExport;

class ReporteFacturaController extends ConfiguracionSistemaController{

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
    public function reporte_diario_ventas(){
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('proveedores_guardar_configuracion_tabla');
        return view('reportes.facturas.reportediarioventas', compact('configuracion_tabla','rutaconfiguraciontabla'));
    }
    //obtener clientes
    public function reporte_ventas_diarias_obtener_clientes(Request $request){
        if($request->ajax()){
            $numeroabuscar = $request->numeroabuscar;
            $data = Cliente::where('Status', 'ALTA')->where('Numero', 'like', '%' . $numeroabuscar . '%')->orderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .',1\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }        
    } 
    public function generar_reporte_diario_ventas(Request $request){
        $fechahoy = Carbon::parse($request->fechafinalreporte);//fecha de la que se realizar el reporte
        $diahoy = $fechahoy->toDateString();//dar formato de fecha
        $totalesperadofacturadomes = Helpers::convertirvalorcorrecto($request->objetivofinalpesos);//valor total acumulado esperado del mes sin iva
        $totaldiasmes = $fechahoy->daysInMonth;//saber el numero de dias que tiene el mes
        $primerdiamesactual = $fechahoy->firstOfMonth()->toDateString();//obetener el primer dia del mes
        $ultimodiamesactual = $fechahoy->lastOfMonth()->toDateString();//obtener el ultimo dia del mes actual
        $fechashastadiaactual = CarbonPeriod::create($primerdiamesactual, $diahoy);//obtener un array con todas las fechas entre el primer dia del mes y el dia del cual se quiere realizar el reporte
        //configurar la libreria DATE para fechas en español
        Date::setLocale(config('app.locale'));
        Date::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $arrayfacturado = array();
        $acumuladomessiniva = 0;//declaracion de variable acumulado del mes sin iva
        $acumuladoesperadomes = 0;//declaracion de variable acumulado del mes esperado sin iva
        //iteracion para saber el numero de domingos del mes
        $todaslasfechasmes = CarbonPeriod::create($primerdiamesactual, $ultimodiamesactual);//obtener un array con todas las fechas del mes actual
        $numerodomingos = 0;
        foreach ($todaslasfechasmes as $fecha) {
            if(Date::parse($fecha)->format('l') == 'domingo'){
                $numerodomingos++;
            }
        }
        $totaldiaslaborales = $totaldiasmes - $numerodomingos;
        $importeesperadofacturadopordia = $totalesperadofacturadomes/$totaldiaslaborales;//saber el importe esperado diario sin iva
        //iteracion de todas las fechas obtenidas de CarbonPeriod
        foreach ($fechashastadiaactual as $fecha) {
            $fechafacturas = $fecha->toDateString();//dar formato de fecha a la fecha iterada actualmente
            $dia = Date::parse($fecha)->format('l');//obtener el nombre del dia de la fecha iterada actualmente
            if($dia != "domingo"){
                if($request->numerocliente == 0){
                    $facturas = Factura::whereDate('Fecha', $fechafacturas)->where('Status', '<>', 'BAJA')->orderBy('Folio', 'ASC')->get();//obtener todas las facturas de la fecha iterada actualmente
                }else{
                    $facturas = Factura::whereDate('Fecha', $fechafacturas)->where('Cliente', $request->numerocliente)->where('Status', '<>', 'BAJA')->orderBy('Folio', 'ASC')->get();//obtener todas las facturas de la fecha iterada actualmente
                }
                $acumuladoesperadomes = $acumuladoesperadomes + $importeesperadofacturadopordia;//sumar acumulado esperado en la fecha iterada actualmente
                $importediatotalsiniva = 0;//declaracion  de variable importe total del dia sin iva
                //si se encontraron facturas en la fecha iterada actualmente
                if(sizeof($facturas) >= 1){
                    foreach($facturas as $factura){
                        $importediatotalsiniva = $importediatotalsiniva + $factura->SubTotal;//sumar el importe total del dia sin iva
                        $acumuladomessiniva = $acumuladomessiniva + $factura->SubTotal;//sumar al acumulado total sin iva
                    }
                    //calculo faltante o sobrante de importe segun sea el caso
                    if($importediatotalsiniva >= $importeesperadofacturadopordia){
                        $faltantesobranteimporteobjetivo = $importediatotalsiniva - $importeesperadofacturadopordia;
                    }else{
                        $faltantesobranteimporteobjetivo = $importeesperadofacturadopordia - $importediatotalsiniva;
                    }
                    //calculo faltante o sobrante del acumulado mes segun sea el caso
                    if($acumuladomessiniva >= $acumuladoesperadomes){
                        $faltantesobranteacumuladoobjetivo = $acumuladomessiniva - $acumuladoesperadomes;
                    }else{
                        $faltantesobranteacumuladoobjetivo = $acumuladoesperadomes - $acumuladomessiniva;
                    }
                    //calculo porcentaje objetivo final
                    $porcentajeobjetivofinal = $acumuladomessiniva*100/$totalesperadofacturadomes;
                    $arrayfacturado[]=array(
                        "fechafacturas"=>$fechafacturas,
                        "dia"=>$dia,
                        "importediatotalsiniva"=>Helpers::convertirvalorcorrecto($importediatotalsiniva),
                        "importeesperadofacturadopordia"=>Helpers::convertirvalorcorrecto($importeesperadofacturadopordia),
                        "faltantesobranteimporteobjetivo"=>Helpers::convertirvalorcorrecto($faltantesobranteimporteobjetivo),
                        "acumuladomessiniva"=>Helpers::convertirvalorcorrecto($acumuladomessiniva),
                        "acumuladoesperadomes"=>Helpers::convertirvalorcorrecto($acumuladoesperadomes),
                        "faltantesobranteacumuladoobjetivo"=>Helpers::convertirvalorcorrecto($faltantesobranteacumuladoobjetivo),
                        "porcentajeobjetivofinal"=>Helpers::convertirvalorcorrecto($porcentajeobjetivofinal),
                        "importediatotalsinivaconformato"=>number_format(Helpers::convertirvalorcorrecto($importediatotalsiniva), $this->numerodecimales),
                        "importeesperadofacturadopordiaconformato"=>number_format(Helpers::convertirvalorcorrecto($importeesperadofacturadopordia), $this->numerodecimales),
                        "faltantesobranteimporteobjetivoconformato"=>number_format(Helpers::convertirvalorcorrecto($faltantesobranteimporteobjetivo), $this->numerodecimales),
                        "acumuladomessinivaconformato"=>number_format(Helpers::convertirvalorcorrecto($acumuladomessiniva), $this->numerodecimales),
                        "acumuladoesperadomesconformato"=>number_format(Helpers::convertirvalorcorrecto($acumuladoesperadomes), $this->numerodecimales),
                        "faltantesobranteacumuladoobjetivoconformato"=>number_format(Helpers::convertirvalorcorrecto($faltantesobranteacumuladoobjetivo), $this->numerodecimales)

                    );
                }else{
                    //calculo faltante o sobrante de importe segun sea el caso
                    if($importediatotalsiniva >= $importeesperadofacturadopordia){
                        $faltantesobranteimporteobjetivo = $importediatotalsiniva - $importeesperadofacturadopordia;
                    }else{
                        $faltantesobranteimporteobjetivo = $importeesperadofacturadopordia - $importediatotalsiniva;
                    }

                    //calculo faltante o sobrante del acumulado mes segun sea el caso
                    if($acumuladomessiniva >= $acumuladoesperadomes){
                        $faltantesobranteacumuladoobjetivo = $acumuladomessiniva - $acumuladoesperadomes;
                    }else{
                        $faltantesobranteacumuladoobjetivo = $acumuladoesperadomes - $acumuladomessiniva;
                    }
                    //calculo porcentaje objetivo final
                    $porcentajeobjetivofinal = $acumuladomessiniva*100/$totalesperadofacturadomes;
                    $arrayfacturado[]=array(
                        "fechafacturas"=>$fechafacturas,
                        "dia"=>$dia,
                        "importediatotalsiniva"=>Helpers::convertirvalorcorrecto($importediatotalsiniva),
                        "importeesperadofacturadopordia"=>Helpers::convertirvalorcorrecto($importeesperadofacturadopordia),
                        "faltantesobranteimporteobjetivo"=>Helpers::convertirvalorcorrecto($faltantesobranteimporteobjetivo),
                        "acumuladomessiniva"=>Helpers::convertirvalorcorrecto($acumuladomessiniva),
                        "acumuladoesperadomes"=>Helpers::convertirvalorcorrecto($acumuladoesperadomes),
                        "faltantesobranteacumuladoobjetivo"=>Helpers::convertirvalorcorrecto($faltantesobranteacumuladoobjetivo),
                        "porcentajeobjetivofinal"=>Helpers::convertirvalorcorrecto($porcentajeobjetivofinal),
                        "importediatotalsinivaconformato"=>number_format(Helpers::convertirvalorcorrecto($importediatotalsiniva), $this->numerodecimales),
                        "importeesperadofacturadopordiaconformato"=>number_format(Helpers::convertirvalorcorrecto($importeesperadofacturadopordia), $this->numerodecimales),
                        "faltantesobranteimporteobjetivoconformato"=>number_format(Helpers::convertirvalorcorrecto($faltantesobranteimporteobjetivo), $this->numerodecimales),
                        "acumuladomessinivaconformato"=>number_format(Helpers::convertirvalorcorrecto($acumuladomessiniva), $this->numerodecimales),
                        "acumuladoesperadomesconformato"=>number_format(Helpers::convertirvalorcorrecto($acumuladoesperadomes), $this->numerodecimales),
                        "faltantesobranteacumuladoobjetivoconformato"=>number_format(Helpers::convertirvalorcorrecto($faltantesobranteacumuladoobjetivo), $this->numerodecimales)
                    );
                }
            }
        }
        return DataTables::of($arrayfacturado)
        ->make(true);
    }
    public function generar_excel_reporte_diario_ventas(Request $request){
        return Excel::download(new ReportesDiariosVentasExport($request->fechafinalreporte, $request->objetivofinalpesos, $request->numerocliente, $request->cliente ,$this->numerodecimales), "reportediarioventas.xlsx"); 
    }
}
