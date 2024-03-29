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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NotasCreditoProveedoresExport;
use App\NotaProveedor;
use App\NotaProveedorDetalle;
use App\NotaProveedorDocumento;
use App\TipoOrdenCompra;
use App\Compra;
use App\CompraDetalle;
use App\Proveedor;
use App\Almacen;
use App\Producto;
use App\BitacoraDocumento;
Use App\Existencia;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Configuracion_Tabla;
use App\VistaNotaCreditoProveedor;
use App\VistaObtenerExistenciaProducto;
use App\Firma_Rel_Documento;
use App\User_Rel_Almacen;
use Config;
use Mail;
use App\Serie;
use LynX39\LaraPdfMerger\Facades\PdfMerger;
use Storage;
use ZipArchive;
use File;

class NotasCreditoProveedoresController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'NotasCreditoProveedor')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //campos vista
        $this->camposvista = [];
        foreach (explode(",", $this->configuracion_tabla->campos_activados) as $campo){
            array_push($this->camposvista, $campo);
        }
        foreach (explode(",", $this->configuracion_tabla->campos_desactivados) as $campo){
            array_push($this->camposvista, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function notas_credito_proveedores(){
        $serieusuario = 'A';
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('NotasCreditoProveedor', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('notas_credito_proveedor_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('notas_credito_proveedores_exportar_excel');
        $rutacreardocumento = route('notas_credito_proveedores_generar_pdfs');
        return view('registros.notascreditoproveedores.notascreditoproveedores', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }

    //obtener registros tabla
    public function notas_credito_proveedores_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('NotasCreditoProveedor', Auth::user()->id);
            $fechahoy = Carbon::now()->toDateString();
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaNotaCreditoProveedor::select($configuraciones_tabla['campos_consulta'])->where('Periodo', $periodo);
            return DataTables::of($data)
                    ->order(function ($query) use($configuraciones_tabla) {
                        if($configuraciones_tabla['configuracion_tabla']->primerordenamiento != 'omitir'){
                            $query->orderBy($configuraciones_tabla['configuracion_tabla']->primerordenamiento, '' . $configuraciones_tabla['configuracion_tabla']->formaprimerordenamiento . '');
                        }
                        if($configuraciones_tabla['configuracion_tabla']->segundoordenamiento != 'omitir'){
                            $query->orderBy($configuraciones_tabla['configuracion_tabla']->segundoordenamiento, '' . $configuraciones_tabla['configuracion_tabla']->formasegundoordenamiento . '');
                        }
                        if($configuraciones_tabla['configuracion_tabla']->tercerordenamiento != 'omitir'){
                            $query->orderBy($configuraciones_tabla['configuracion_tabla']->tercerordenamiento, '' . $configuraciones_tabla['configuracion_tabla']->formatercerordenamiento . '');
                        }
                    })
                    ->withQuery('sumaimporte', function($data) {
                        return $data->sum('Importe');
                    })
                    ->withQuery('sumadescuento', function($data) {
                        return $data->sum('Descuento');
                    })
                    ->withQuery('sumasubtotal', function($data) {
                        return $data->sum('SubTotal');
                    })
                    ->withQuery('sumaiva', function($data) {
                        return $data->sum('Iva');
                    })
                    ->withQuery('sumatotal', function($data) {
                        return $data->sum('Total');
                    })
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Nota .'\')">Cambios</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar(\''.$data->Nota .'\')">Bajas</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="'.route('notas_credito_proveedores_generar_pdfs_indiv',$data->Nota).'" target="_blank">Ver Documento PDF</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Nota .'\')">Enviar Documento por Correo</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="generardocumentoeniframe(\''.$data->Nota .'\')">Imprimir Documento PDF</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                    //->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    //->addColumn('Iva', function($data){ return $data->Iva; })
                    //->addColumn('Total', function($data){ return $data->Total; })
                    ->addColumn('ImpLocTraslados', function($data){ return $data->ImpLocTraslados; })
                    ->addColumn('ImpLocRetenciones', function($data){ return $data->ImpLocRetenciones; })
                    ->addColumn('IepsRetencion', function($data){ return $data->IepsRetencion; })
                    ->addColumn('IsrRetencion', function($data){ return $data->IsrRetencion; })
                    ->addColumn('IvaRetencion', function($data){ return $data->IvaRetencion; })
                    ->addColumn('Ieps', function($data){ return $data->Ieps; })
                    //->addColumn('Descuento', function($data){ return $data->Descuento; })
                    //->addColumn('Importe', function($data){ return $data->Importe; })
                    //->addColumn('TipoCambio', function($data){ return $data->TipoCambio; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener series documento
    public function notas_credito_proveedores_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'NotasCreditoProveedor')->where('Usuario', Auth::user()->user)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarseriedocumento(\''.$data->Serie.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener ultimo folio de la serie seleccionada
    public function notas_credito_proveedores_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\NotaProveedor',$request->Serie);
        return response()->json($folio);
    }
    //obtener ultimo folio
    public function notas_credito_proveedores_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\NotaProveedor',$request->serie);
        return response()->json($folio);
    }
    //obtener tipos ordenes de compra
    public function notas_credito_proveedor_obtener_tipos_ordenes_compra(Request $request){
        $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->get();
        $select_tipos_ordenes_compra = "<option selected disabled hidden>Selecciona...</option>";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }
    //obtener proveedor
    public function notas_credito_proveedores_obtener_proveedores(Request $request){
        if($request->ajax()){
            $data = Proveedor::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarproveedor('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$data->Rfc .'\',\''.$data->SolicitarXML .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener proveedor por numero
    public function notas_credito_proveedores_obtener_proveedor_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $plazo = '';
        $rfc = '';
        $SolicitarXML = 0;
        $existeproveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->count();
        if($existeproveedor > 0){
            $proveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->first();
            $numero = $proveedor->Numero;
            $nombre = $proveedor->Nombre;
            $plazo = $proveedor->Plazo;
            $rfc = $proveedor->Rfc;
            $SolicitarXML = $proveedor->SolicitarXML;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'plazo' => $plazo,
            'rfc' => $rfc,
            'SolicitarXML' => $SolicitarXML
        );
        return response()->json($data);
    }

    //obtener almacenes
    public function notas_credito_proveedores_obtener_almacenes(Request $request){
        if($request->ajax()){
            $contaralmacenesasignadosausuario = User_Rel_Almacen::where('user_id', Auth::user()->id)->count();
            if($contaralmacenesasignadosausuario > 0){
                $data = DB::table('user_rel_almacenes as ura')
                ->join('Almacenes as a', 'ura.almacen_id', '=', 'a.Numero')
                ->select('ura.id', 'a.Numero', 'a.Nombre')
                ->where('a.Status', 'ALTA')
                ->where('ura.user_id', Auth::user()->id)
                ->orderby('a.Numero', 'ASC')
                ->get();
            }else{
                $data = Almacen::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
            }
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaralmacen('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener almacen por numero
    public function notas_credito_proveedores_obtener_almacen_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $plazo = '';
        $contaralmacenesasignadosausuario = User_Rel_Almacen::where('user_id', Auth::user()->id)->count();
        if($contaralmacenesasignadosausuario > 0){
            $existealmacen = DB::table('user_rel_almacenes as ura')
            ->join('Almacenes as a', 'ura.almacen_id', '=', 'a.Numero')
            ->select('ura.id', 'a.Numero', 'a.Nombre')
            ->where('a.Numero', $request->numeroalmacen)
            ->where('a.Status', 'ALTA')
            ->where('ura.user_id', Auth::user()->id)
            ->count();
            if($existealmacen > 0){
                $almacen = DB::table('user_rel_almacenes as ura')
                ->join('Almacenes as a', 'ura.almacen_id', '=', 'a.Numero')
                ->select('ura.id', 'a.Numero', 'a.Nombre')
                ->where('a.Numero', $request->numeroalmacen)
                ->where('a.Status', 'ALTA')
                ->where('ura.user_id', Auth::user()->id)
                ->orderby('a.Numero', 'DESC')
                ->first();
                $numero = $almacen->Numero;
                $nombre = $almacen->Nombre;
            }
        }else{
            $existealmacen = Almacen::where('Numero', $request->numeroalmacen)->where('Status', 'ALTA')->count();
            if($existealmacen > 0){
                $almacen = Almacen::where('Numero', $request->numeroalmacen)->where('Status', 'ALTA')->first();
                $numero = $almacen->Numero;
                $nombre = $almacen->Nombre;
            }
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data);
    }

    //obtener compras
    public function notas_credito_proveedores_obtener_compras(Request $request){
        if($request->ajax()){
            $arraycomprasseleccionadas = Array();
            foreach(explode(",", $request->stringcomprasseleccionadas) as $compra){
                array_push($arraycomprasseleccionadas, $compra);
            }
            $data = Compra::where('Proveedor', $request->numeroproveedor)
                                ->whereNotIn('Compra', $arraycomprasseleccionadas)
                                ->where('Status', 'POR PAGAR')
                                ->orderBy('Folio', 'DESC')
                                ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcompra('.$data->Folio.',\''.$data->Compra .'\',\''.$data->Tipo .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){
                        return Helpers::fecha_espanol($data->Fecha);
                    })
                    ->addColumn('Total', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total);
                    })
                    ->rawColumns(['operaciones','Fecha','Total'])
                    ->make(true);
        }
    }

    //obtener productos
    public function notas_credito_proveedores_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $numeroalmacen = $request->numeroalmacen;
            $tipooperacion = $request->tipooperacion;
            $stringcomprasseleccionadas = $request->stringcomprasseleccionadas;
            $arrayproductosseleccionables = Array();
            foreach(explode(",", $request->stringcomprasseleccionadas) as $compra){
                $detallescompra = CompraDetalle::where('Compra', $compra)->get();
                foreach($detallescompra as $detalle){
                    array_push($arrayproductosseleccionables, $detalle->Codigo);
                }
            }
            $data = VistaObtenerExistenciaProducto::whereIn('Codigo', $arrayproductosseleccionables)->where('Codigo', 'like', '%' . $codigoabuscar . '%')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($numeroalmacen, $tipooperacion, $stringcomprasseleccionadas){
                        $compra = Compra::where('Compra', $stringcomprasseleccionadas)->first();
                        switch($compra->Tipo){
                            case "GASTOS":
                                $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$data->NombreClaveProducto.'\',\''.$data->NombreClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
                                break;
                            case "TOT":
                                $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$data->NombreClaveProducto.'\',\''.$data->NombreClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
                                break;
                            default:
                                if($data->Almacen == $numeroalmacen || $data->Almacen == NULL){
                                    $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$data->NombreClaveProducto.'\',\''.$data->NombreClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
                                }else{
                                    $boton = '';
                                }
                        }
                        return $boton;
                    })
                    ->addColumn('Existencias', function($data){
                        return Helpers::convertirvalorcorrecto($data->Existencias);
                    })
                    ->addColumn('Costo', function($data){
                        return Helpers::convertirvalorcorrecto($data->Costo);
                    })
                    ->addColumn('SubTotal', function($data){
                        return Helpers::convertirvalorcorrecto($data->SubTotal);
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener producto por codigo
    public function notas_credito_proveedores_obtener_producto_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        $numeroalmacen = $request->numeroalmacen;
        $stringcomprasseleccionadas = $request->stringcomprasseleccionadas;
        $arrayproductosseleccionables = Array();
        foreach(explode(",", $request->stringcomprasseleccionadas) as $compra){
            $detallescompra = CompraDetalle::where('Compra', $compra)->get();
            foreach($detallescompra as $detalle){
                array_push($arrayproductosseleccionables, $detalle->Codigo);
            }
        }
        $contarproductos = VistaObtenerExistenciaProducto::whereIn('Codigo', $arrayproductosseleccionables)->where('Codigo', $codigoabuscar)->where('Almacen', $numeroalmacen)->count();
        if($contarproductos > 0){
            $producto = VistaObtenerExistenciaProducto::whereIn('Codigo', $arrayproductosseleccionables)->where('Codigo', $codigoabuscar)->where('Almacen', $numeroalmacen)->first();
            $data = array(
                'Codigo' => $producto->Codigo,
                'Producto' => htmlspecialchars($producto->Producto, ENT_QUOTES),
                'Unidad' => $producto->Unidad,
                'Costo' => Helpers::convertirvalorcorrecto($producto->Costo),
                'Impuesto' => Helpers::convertirvalorcorrecto($producto->Impuesto),
                'SubTotal' => Helpers::convertirvalorcorrecto($producto->SubTotal),
                'Existencias' => Helpers::convertirvalorcorrecto($producto->Existencias),
                'Insumo' => $producto->Insumo,
                'ClaveProducto' => $producto->ClaveProducto,
                'ClaveUnidad' => $producto->ClaveUnidad,
                'NombreClaveProducto' => $producto->NombreClaveProducto,
                'NombreClaveUnidad' => $producto->NombreClaveUnidad,
                'contarproductos' => $contarproductos
            );
        }else{
            $data = array(
                'Codigo' => '',
                'Producto' => '',
                'Unidad' => '',
                'Costo' => '',
                'Impuesto' => '',
                'SubTotal' => '',
                'Existencias' => '',
                'Insumo' => '',
                'ClaveProducto' => '',
                'ClaveUnidad' => '',
                'NombreClaveProducto' => '',
                'NombreClaveUnidad' => '',
                'contarproductos' => $contarproductos
            );
        }
        return response()->json($data);
    }

    //obtener claves productos
    public function notas_credito_proveedores_obtener_claves_productos(Request $request){
        if($request->ajax()){
            $fila = $request->fila;
            $data = ClaveProdServ::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fila){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveproducto(\''.$data->Clave .'\',\''.$data->Nombre .'\','.$fila.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener claves unidades
    public function notas_credito_proveedores_obtener_claves_unidades(Request $request){
        if($request->ajax()){
            $fila = $request->fila;
            $data = ClaveUnidad::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fila){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveunidad(\''.$data->Clave .'\',\''.$data->Nombre .'\','.$fila.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener compra seleccionada
    public function notas_credito_proveedores_obtener_compra(Request $request){
        $compra = Compra::where('Compra', $request->Compra)->first();
        $almacen = Almacen::where('Numero', $compra->Almacen)->first();
        $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($compra->Iva, $compra->SubTotal);
        $tipooperacion = $request->tipooperacion;
        //detalles orden compra
        $filacompra = '';
        $filacompra= $filacompra.
        '<tr class="filascompras" id="filacompra'.$request->contadorfilascompras.'">'.
            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfilacompra" onclick="eliminarfilacompranotaproveedor('.$request->contadorfilascompras.')" >X</div><input type="hidden" class="form-control compraagregadoen" name="compraagregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
            '<td class="tdmod"><input type="hidden" class="form-control compraaplicarpartida" name="compraaplicarpartida[]" value="'.$compra->Compra.'" readonly>'.$compra->Compra.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control fechacomprapartida" name="fechacomprapartida[]" value="'.$compra->Fecha.'" readonly>'.$compra->Fecha.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control facturacomprapartida" name="facturacomprapartida[]" value="'.$compra->Factura.'" readonly>'.$compra->Factura.'</td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd totalpesoscomprapartida" name="totalpesoscomprapartida[]" value="'.Helpers::convertirvalorcorrecto($compra->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonoscomprapartida" name="abonoscomprapartida[]" value="'.Helpers::convertirvalorcorrecto($compra->Abonos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd notascreditocomprapartida" name="notascreditocomprapartida[]" value="'.Helpers::convertirvalorcorrecto($compra->Descuentos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdetcom divorinputmodmd descuentopesoscomprapartida" name="descuentopesoscomprapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-max="'.Helpers::convertirvalorcorrecto($compra->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilastablacompras('.$request->contadorfilascompras.');" ></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd saldocomprapartida" name="saldocomprapartida[]" value="'.Helpers::convertirvalorcorrecto($compra->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
        '</tr>';
        $data = array(
            "compra" => $compra,
            "almacen" => $almacen,
            "filacompra" => $filacompra,
        );
        return response()->json($data);
    }

    //obtener los codigos de la compra seleccionada
    public function notas_credito_proveedor_obtener_codigos_compra(Request $request){
        if($request->ajax()){
            $fila = $request->fila;
            $data = DB::table('Compras Detalles AS cd')
                        ->Join('c_ClaveProdServ AS cp', 'cd.ClaveProducto', '=', 'cp.Clave')
                        ->Join('c_ClaveUnidad AS cu', 'cd.ClaveUnidad', '=', 'cu.Clave')
                        ->select('cd.Item AS Item', 'cd.Compra AS Compra', 'cd.Codigo AS Codigo', 'cd.Descripcion AS Descripcion', 'cd.Unidad AS Unidad', 'cd.Cantidad AS Cantidad', 'cd.Precio AS Precio', 'cd.Importe AS Importe', 'cd.Dcto AS Dcto', 'cd.Descuento AS Descuento', 'cd.ImporteDescuento AS ImporteDescuento', 'cd.SubTotal AS SubTotal', 'cd.Impuesto AS Impuesto', 'cd.Iva AS Iva', 'cd.Total AS Total', 'cp.Clave AS ClaveProducto', 'cp.Nombre AS NombreClaveProducto', 'cu.Clave AS ClaveUnidad', 'cu.Nombre AS NombreClaveUnidad')
                        ->where('cd.Compra', $request->compra)
                        ->orderBy('cd.Item', 'ASC')
                        ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fila){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcodigocompra('.$fila.','.$data->Item.',\''.$data->Compra.'\',\''.$data->Codigo.'\',\''.$data->Descripcion.'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Cantidad).'\',\''.Helpers::convertirvalorcorrecto($data->Precio).'\',\''.Helpers::convertirvalorcorrecto($data->Importe).'\',\''.Helpers::convertirvalorcorrecto($data->Dcto).'\',\''.Helpers::convertirvalorcorrecto($data->Descuento).'\',\''.Helpers::convertirvalorcorrecto($data->ImporteDescuento).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->Iva).'\',\''.Helpers::convertirvalorcorrecto($data->Total).'\',\''.$data->ClaveProducto.'\',\''.$data->NombreClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$data->NombreClaveUnidad.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Cantidad', function($data){
                        return Helpers::convertirvalorcorrecto($data->Cantidad);
                    })
                    ->addColumn('Precio', function($data){
                        return Helpers::convertirvalorcorrecto($data->Precio);
                    })
                    ->addColumn('Total', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total);
                    })
                    ->rawColumns(['operaciones','Cantidad','Precio','Total'])
                    ->make(true);
        }
    }

    //obtener datos almacen
    public function notas_credito_proveedores_obtener_datos_almacen(Request $request){
        $compra = Compra::where('Compra', $request->compra)->first();
        $almacen = Almacen::where('Numero', $compra->Almacen)->first();
        $data = array(
            'compra' => $compra,
            'almacen' => $almacen
        );
        return response()->json($data);
    }

    public function notas_credito_proveedor_cargar_xml_alta(Request $request){
        $mover_a_carpeta="xml_cargados";
        $xml = $request->xml;
        $nombre_original = $xml->getClientOriginalName();
        //guardar xml en public/xml_cargados
        $xml->move($mover_a_carpeta,$nombre_original);
        if (file_exists('xml_cargados/'.$nombre_original)) {
            //cargar xml
            libxml_use_internal_errors(true);
            $xml = simplexml_load_file('xml_cargados/'.$nombre_original);
            //obtener datos generales del xml nodo Comprobante
            $comprobante = $xml->attributes();
            $array_comprobante = array(
                "Total" => $comprobante['Total'],
                "Moneda" => $comprobante['Moneda'],
                "TipoDeComprobante" => $comprobante['TipoDeComprobante'],
                "MetodoPago" => $comprobante['MetodoPago'],
                "LugarExpedicion" => $comprobante['LugarExpedicion'],
                "SubTotal" => $comprobante['SubTotal'],
                "Folio" => $comprobante['Folio'],
                "Fecha" => $comprobante['Fecha'],
                "Version" => $comprobante['Version'],
                "Serie" => $comprobante['Serie'],
                "Sello" => $comprobante['Sello'],
                "Certificado" => $comprobante['Certificado'],
                "CondicionesDePago" => $comprobante['CondicionesDePago'],
                "FormaPago" => $comprobante['FormaPago'],
                "NoCertificado" => $comprobante['NoCertificado'],
                "TipoCambio" => $comprobante['TipoCambio'],
                "Descuento" => $comprobante['Descuento']
            );
            //obtener datos generales del xml nodo Emisor
            $activar_namespaces = $xml->getNameSpaces(true);
            $namespaces = $xml->children($activar_namespaces['cfdi']);
            if($namespaces->Emisor){
                $emisor = $namespaces->Emisor->attributes();
                $array_emisor = array(
                    "Rfc" => $emisor['Rfc'],
                    "Nombre" => $emisor['Nombre'],
                    "RegimenFiscal" => $emisor['RegimenFiscal']
                );
            }else{
                $emisor = "";
            }
            //obtener datos generales del xml nodo Receptor
            if($namespaces->Receptor){
                $receptor = $namespaces->Receptor->attributes();
                $array_receptor= array(
                    "Rfc" => $receptor['Rfc'],
                    "Nombre" => $receptor['Nombre'],
                    "UsoCFDI" => $receptor['UsoCFDI']
                );
            }else{
                $receptor = "";
            }
            //obtener datos generales del xml nodo Impuestos
            $impuesto = $namespaces->Impuestos->attributes();
            $TotalImpuestosTrasladados = $impuesto['TotalImpuestosTrasladados'];
            //obtener datos generales del xml nodo hijo traslado del nodo padre Impuestos
            $array_traslados_impuestos = array();
            foreach($namespaces->Impuestos->Traslados->Traslado as $traslado){
                $atributos_traslado = $traslado->attributes();
                $array_traslados_impuestos[] = array(
                    "Impuesto" => $atributos_traslado['Impuesto'],
                    "TipoFactor" => $atributos_traslado['TipoFactor'],
                    "TasaOCuota" => $atributos_traslado['TasaOCuota'],
                    "Importe" => $atributos_traslado['Importe']
                );
            }
            //obtener todas las partidas ó conceptos del xml
            $array_conceptos = array();
            foreach($namespaces->Conceptos->Concepto as $concepto){
                //obtener datos generales del xml nodo hijo traslado del nodo padre Concepto
                $array_traslados = array();
                $atributos_traslado = $concepto->Impuestos->Traslados->Traslado->attributes();
                $array_traslados[] = array(
                    "Base" => $atributos_traslado['Base'],
                    "Impuesto" => $atributos_traslado['Impuesto'],
                    "TipoFactor" => $atributos_traslado['TipoFactor'],
                    "TasaOCuota" => $atributos_traslado['TasaOCuota'],
                    "Importe" => $atributos_traslado['Importe']
                );
                //obtener datos generales del xml nodo Concepto
                $atributos_concepto = $concepto->attributes();
                $array_conceptos[] = array(
                    "ClaveProdServ" => $atributos_concepto['ClaveProdServ'],
                    "Cantidad" => $atributos_concepto['Cantidad'],
                    "ClaveUnidad" => $atributos_concepto['ClaveUnidad'],
                    "Unidad" => $atributos_concepto['Unidad'],
                    "Descripcion" => $atributos_concepto['Descripcion'],
                    "ValorUnitario" => $atributos_concepto['ValorUnitario'],
                    "Importe" => $atributos_concepto['Importe'],
                    "Descuento" => $atributos_concepto['Descuento'],
                    "array_traslados" => $array_traslados
                );
            }
            //obtener UUID del xml timbrado digital
            $activar_namespaces = $namespaces->Complemento->getNameSpaces(true);
            $namespaces_uuid = $namespaces->Complemento->children($activar_namespaces['tfd']);
            $atributos_complemento = $namespaces_uuid->TimbreFiscalDigital->attributes();
            $uuid = $atributos_complemento['UUID'];
            $fechatimbrado = $atributos_complemento['FechaTimbrado'];
            $data = array(
                "nombrexml" => $nombre_original,
                "comprobante" => $comprobante,
                "array_comprobante" => $array_comprobante,
                "array_emisor" => $array_emisor,
                "array_receptor" => $array_receptor,
                "array_conceptos" => $array_conceptos,
                "TotalImpuestosTrasladados" => $TotalImpuestosTrasladados,
                "array_traslados_impuestos" => $array_traslados_impuestos,
                "uuid" => $uuid,
                "fechatimbrado" => $fechatimbrado
            );
            //eliminar xml de public/xml_cargados
            $eliminarxml = public_path().'/xml_cargados/'.$nombre_original;
            unlink($eliminarxml);
        } else {
            exit('Error al abrir xml.');
        }
        return response()->json($data);
    }

    //obtener existencias actuales por codigo y almacen
    public function notas_credito_proveedor_obtener_existencias_partida(Request $request){
        $compra = Compra::where('Compra', $request->stringcomprasseleccionadas)->first();
        switch ($compra->Tipo){
            case "TOT":
                $nuevaexistencia = $request->cantidadpartida;
                break;
            case "GASTOS":
                $nuevaexistencia = $request->cantidadpartida;
                break;
            default:
            $existencias = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->almacen)->first();
            $nota = $request->folio.'-'.$request->serie;
            $detallenotaproveedor = NotaProveedorDetalle::where('Nota', $nota)->where('Codigo', $request->codigopartida)->count();
            $nuevaexistencia = 0;
            if($detallenotaproveedor > 0){
                $detallenotaproveedor = NotaProveedorDetalle::where('Nota', $nota)->where('Codigo', $request->codigopartida)->first();
                $nuevaexistencia = $existencias->Existencias + $detallenotaproveedor->Cantidad;
            }else{
                $nuevaexistencia = $existencias->Existencias;
            }
        }
        return response()->json(Helpers::convertirvalorcorrecto($nuevaexistencia));
    }

    public function notas_credito_proveedor_guardar(Request $request){
        ini_set('max_input_vars','20000' );

        $uuid=$request->uuid;
        $solicitarxml=$request->solicitarxml;
	    $ExisteUUID = NotaProveedor::where('UUID', $uuid )->where('Status', '<>', 'BAJA')->first();
	    if($ExisteUUID == true && $solicitarxml == 1){
	        $NotaProveedor = 1;
	    }else{
            //obtener el ultimo id de la tabla
            $folio = Helpers::ultimofolioserietablamodulos('App\NotaProveedor',$request->serie);
            //INGRESAR DATOS A TABLA COMPRAS
            $notaproveedor = $folio.'-'.$request->serie;
            $NotaProveedor = new NotaProveedor;
            $NotaProveedor->Nota=$notaproveedor;
            $NotaProveedor->Serie=$request->serie;
            $NotaProveedor->Folio=$folio;
            $NotaProveedor->Proveedor=$request->numeroproveedor;
            $NotaProveedor->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
            if($solicitarxml == 1){
                $NotaProveedor->UUID=$request->uuid;
                $NotaProveedor->FechaEmitida=Carbon::parse($request->fechaemitida)->toDateTimeString();
            }else{
                $NotaProveedor->UUID="N/A";
                $NotaProveedor->FechaEmitida=Helpers::fecha_exacta_accion_datetimestring();
            }
            $NotaProveedor->NotaProveedor=$request->notaproveedor;
            $NotaProveedor->Almacen=$request->numeroalmacen;
            $NotaProveedor->Importe=$request->importe;
            $NotaProveedor->Descuento=$request->descuento;
            $NotaProveedor->Ieps=$request->ieps;
            $NotaProveedor->SubTotal=$request->subtotal;
            $NotaProveedor->Iva=$request->iva;
            $NotaProveedor->IvaRetencion=$request->retencioniva;
            $NotaProveedor->IsrRetencion=$request->retencionisr;
            $NotaProveedor->IepsRetencion=$request->retencionieps;
            $NotaProveedor->Total=$request->total;
            $NotaProveedor->Obs=$request->observaciones;
            $NotaProveedor->Moneda=$request->moneda;
            $NotaProveedor->TipoCambio=$request->pesosmoneda;
            $NotaProveedor->EmisorRfc=$request->emisorrfc;
            $NotaProveedor->EmisorNombre=$request->emisornombre;
            $NotaProveedor->ReceptorRfc=$request->receptorrfc;
            $NotaProveedor->ReceptorNombre=$request->receptornombre;
            $NotaProveedor->Status="ALTA";
            $NotaProveedor->Usuario=Auth::user()->user;
            $NotaProveedor->Periodo=$this->periodohoy;
            $NotaProveedor->save();
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO NOTAS PROVEEDOR
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "NOTAS PROVEEDOR";
            $BitacoraDocumento->Movimiento = $notaproveedor;
            $BitacoraDocumento->Aplicacion = "ALTA";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = "ALTA";
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $this->periodohoy;
            $BitacoraDocumento->save();
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO NOTAS PROVEEDOR DOC
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "NOTAS PROVEEDOR DOC";
            $BitacoraDocumento->Movimiento = $notaproveedor;
            $BitacoraDocumento->Aplicacion = "ALTA";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = "ALTA";
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $this->periodohoy;
            $BitacoraDocumento->save();
            //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
            $item = 1;
            foreach ($request->codigopartida as $key => $codigopartida){
                $auxExistencia = 0;
                $NotaProveedorDetalle=new NotaProveedorDetalle;
                $NotaProveedorDetalle->Nota = $notaproveedor;
                $NotaProveedorDetalle->Proveedor = $request->numeroproveedor;
                $NotaProveedorDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $NotaProveedorDetalle->Codigo = $codigopartida;
                $NotaProveedorDetalle->Descripcion = $request->descripcionpartida [$key];
                $NotaProveedorDetalle->Unidad = $request->unidadpartida [$key];
                $NotaProveedorDetalle->Cantidad =  $request->cantidadpartida  [$key];
                $NotaProveedorDetalle->Precio =  $request->preciopartida [$key];
                $NotaProveedorDetalle->Importe = $request->importepartida [$key];
                $NotaProveedorDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                $NotaProveedorDetalle->Descuento = $request->descuentopesospartida [$key];
                $NotaProveedorDetalle->ImporteDescuento = $request->importedescuentopesospartida [$key];
                $NotaProveedorDetalle->Ieps = $request->trasladoiepspesospartida [$key];
                $NotaProveedorDetalle->SubTotal = $request->subtotalpartida [$key];
                $NotaProveedorDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                $NotaProveedorDetalle->Iva = $request->trasladoivapesospartida [$key];
                $NotaProveedorDetalle->IvaRetencion = $request->retencionivapesospartida [$key];
                $NotaProveedorDetalle->IsrRetencion = $request->retencionisrpesospartida [$key];
                $NotaProveedorDetalle->IepsRetencion = $request->retencioniepspesospartida [$key];
                $NotaProveedorDetalle->Total = $request->totalpesospartida [$key];
                $NotaProveedorDetalle->Partida = $request->partidapartida [$key];
                $NotaProveedorDetalle->PrecioMoneda = $request->preciomonedapartida [$key];
                $NotaProveedorDetalle->DescuentoMoneda = $request->descuentopartida [$key];
                $NotaProveedorDetalle->ClaveProducto = $request->claveproductopartida [$key];
                $NotaProveedorDetalle->ClaveUnidad = $request->claveunidadpartida [$key];
                $NotaProveedorDetalle->Item = $item;
                $NotaProveedorDetalle->save();
                $compra = Compra::where('Compra', $request->stringcomprasseleccionadas)->first();
                if($codigopartida != 'DPPP'){
                    switch ($compra->Tipo){
                        case "TOT":
                            break;
                        case "GASTOS":
                            break;
                        default:
                        //restar existencias a almacen principal
                        $RestarExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                        $RestarExistenciaNuevaAlmacen = $RestarExistenciaAlmacen->Existencias - $request->cantidadpartida  [$key];
                        if ($RestarExistenciaNuevaAlmacen < 0 ) {
                            $auxExistencia = 0;
                        } else {
                            $auxExistencia = $RestarExistenciaNuevaAlmacen;
                        }

                        Existencia::where('Codigo', $codigopartida)
                                    ->where('Almacen', $request->numeroalmacen)
                                    ->update([
                                        'Existencias' => Helpers::convertirvalorcorrecto($RestarExistenciaNuevaAlmacen)
                        ]);
                    }
                }
                // Compra::where('Compra', $request->stringcomprasseleccionadas)
                //         ->update([
                //         "Descuentos" => Helpers::convertirvalorcorrecto($compra->Descuentos) +
                //         Helpers::convertirvalorcorrecto($request->descuentopesoscomprapartida [$key])
                // ]);
                $item++;
            }
            //INGRESAR DATOS A TABLA NOTA PROVEEDOR DOCUMENTOS
            $itemdocumento = 1;
            foreach ($request->compraaplicarpartida as $key => $comprapartida){
                $NotaProveedorDocumento=new NotaProveedorDocumento;
                $compraAux = Compra::where('Compra', $comprapartida)->first();
                $descuentoAux = Helpers::convertirvalorcorrecto($compraAux->Descuentos) + Helpers::convertirvalorcorrecto($request->descuentopesoscomprapartida [$key]);
                $NotaProveedorDocumento->Nota = $notaproveedor;
                $NotaProveedorDocumento->Compra = $comprapartida;
                $NotaProveedorDocumento->Descuento = $request->descuentopesoscomprapartida [$key];
                $NotaProveedorDocumento->Item = $itemdocumento;
                $NotaProveedorDocumento->save();
                //Modificar Compra
                Compra::where('Compra', $comprapartida)
                ->update([
                    'Saldo' => $request->saldocomprapartida [$key],
                    'Descuentos' => number_format(round($descuentoAux, ), $this->numerodecimales,'.','')
                ]);
                //Si el saldo es igual a 0 liquidar compra
                if($request->saldocomprapartida [$key] == Helpers::convertirvalorcorrecto(0)){
                    Compra::where('Compra', $comprapartida)
                            ->update([
                                'Status' => "LIQUIDADA"
                            ]);
                }
                $itemdocumento++;
            }
        }
        return response()->json($NotaProveedor);
    }

    //verificar si se puede da de baja la nota
    public function notas_credito_proveedores_verificar_uso_en_modulos(Request $request){
        $notaproveedor = NotaProveedor::where('Nota', $request->notadesactivar)->first();
        $resultadofechas = Helpers::compararanoymesfechas($notaproveedor->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'Status' => $notaproveedor->Status
        );
        return response()->json($data);
    }

    //bajas
    public function notas_credito_proveedores_alta_o_baja(Request $request){
        $NotaProveedor = NotaProveedor::where('Nota', $request->notadesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        NotaProveedor::where('Nota', $request->notadesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Importe' => '0.000000',
                    'Descuento' => '0.000000',
                    'Ieps' => '0.000000',
                    'SubTotal' => '0.000000',
                    'Iva' => '0.000000',
                    'IvaRetencion' => '0.000000',
                    'IsrRetencion' => '0.000000',
                    'IepsRetencion' => '0.000000',
                    'ImpLocRetenciones' => '0.000000',
                    'ImpLocTraslados' => '0.000000',
                    'Total' => '0.000000'
                ]);
        $detalles = NotaProveedorDetalle::where('Nota', $request->notadesactivar)->get();
        //notas proveedor detalles
        foreach($detalles as $detalle){
            if($detalle->Codigo != 'DPPP'){
                //sumar existencias al almacen
                $ExistenciaAlmacen = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $NotaProveedor->Almacen)->first();
                $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias+$detalle->Cantidad;
                Existencia::where('Codigo', $detalle->Codigo)
                            ->where('Almacen', $NotaProveedor->Almacen)
                            ->update([
                                'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                            ]);
            }
            //colocar en ceros cantidades nota proveedor detalles
            NotaProveedorDetalle::where('Nota', $request->notadesactivar)
                            ->where('Item', $detalle->Item)
                            ->update([
                                'Cantidad' => '0.000000',
                                'Importe' => '0.000000',
                                'Dcto' => '0.000000',
                                'Descuento' => '0.000000',
                                'ImporteDescuento' => '0.000000',
                                'Ieps' => '0.000000',
                                'SubTotal' => '0.000000',
                                'Iva' => '0.000000',
                                'IvaRetencion' => '0.000000',
                                'IsrRetencion' => '0.000000',
                                'IepsRetencion' => '0.000000',
                                'Total' => '0.000000',
                                'PrecioMoneda' => '0.000000',
                                'DescuentoMoneda' => '0.000000'
                            ]);
        }
        //notasproveedor documentos
        $detallesdocumentos = NotaProveedorDocumento::where('Nota', $request->notadesactivar)->get();
        foreach($detallesdocumentos as $detalledocumento){
            $notaproveedordocumento = NotaProveedorDocumento::where('Nota', $request->notadesactivar)->where('Compra', $detalledocumento->Compra)->first();
            $compradocumento = Compra::where('Compra', $detalledocumento->Compra)->first();
            //Regresar saldo y descuentos a la compra
            $NuevoDescuentos = $compradocumento->Descuentos - $notaproveedordocumento->Descuento;
            $NuevoSaldo = $compradocumento->Saldo + $notaproveedordocumento->Descuento;
            Compra::where('Compra', $detalledocumento->Compra)
            ->update([
                'Descuentos' => Helpers::convertirvalorcorrecto($NuevoDescuentos),
                'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo)
            ]);
            //Si el saldo es mayor a 0 cambiar status de compra a POR PAGAR
            if($NuevoSaldo > Helpers::convertirvalorcorrecto(0)){
                Compra::where('Compra', $detalledocumento->Compra)
                        ->update([
                            'Status' => "POR PAGAR"
                        ]);
            }
            //colocar en cero cantidades nota proveedor documentos
            NotaProveedorDocumento::where('Nota', $request->notadesactivar)
                                    ->where('Compra', $detalledocumento->Compra)
                                    ->update([
                                        'Descuento' => '0.000000',
                                        'Total' => '0.000000'
                                    ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "NOTAS PROVEEDOR";
        $BitacoraDocumento->Movimiento = $request->notadesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $NotaProveedor->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($NotaProveedor);
    }

    //obtener nota credito proveedor
    public function notas_credito_proveedores_obtener_nota_proveedor(Request $request){
        $notaproveedor = NotaProveedor::where('Nota', $request->notamodificar)->first();
        $almacen = 0;
        if($notaproveedor->Almacen != 0){
            $almacen = Almacen::where('Numero', $notaproveedor->Almacen)->first();
        }
        $proveedor = Proveedor::where('Numero', $notaproveedor->Proveedor)->first();
        //detalles
        $detallesnotaproveedor = NotaProveedorDetalle::where('Nota', $request->notamodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallesnotaproveedor = NotaProveedorDetalle::where('Nota', $request->notamodificar)->count();
        $filasdetallesnotaproveedor = '';
        $descuentocompras = 0;
        $contadorfilascompras = 0;
        if($numerodetallesnotaproveedor > 0){
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo="modificacion";
            foreach($detallesnotaproveedor as $dnp){
                    $producto = "";
                    $Existencia = 0;
                    if($notaproveedor->Almacen != 0){
                        $Existencia = Existencia::where('Codigo', $dnp->Codigo)->where('Almacen', $notaproveedor->Almacen)->first();
                        $producto = Producto::where('Codigo', $dnp->Codigo)->first();
                    }
                    //$parsleymax = $dnp->Cantidad;
                    //$cantidadpartidadetalleordencompra = OrdenCompraDetalle::where('Orden', $compra->Orden)->where('Codigo', $dnp->Codigo)->first();
                    $claveproductopartida = ClaveProdServ::where('Clave', $dnp->ClaveProducto)->first();
                    $claveunidadpartida = ClaveUnidad::where('Clave', $dnp->ClaveUnidad)->first();
                    $claveproducto = $claveproductopartida ? $claveproductopartida->Clave : '';
                    $nombreclaveproducto = $claveproductopartida ? $claveproductopartida->Nombre : '';
                    $claveunidad = $claveunidadpartida ? $claveunidadpartida->Clave : '';
                    $nombreclaveunidad = $claveunidadpartida ? $claveunidadpartida->Nombre : '';
                    //importante porque si se quiere hacer una divison con 0 marca ERROR
                    $porcentajeieps = 0;
                    $porcentajeretencioniva = 0;
                    $porcentajeretencionisr = 0;
                    $porcentajeretencionieps = 0;
                    if($dnp->Ieps > 0){
                        $porcentajeieps = ($dnp->Ieps * 100) / $dnp->ImporteDescuento;
                    }
                    if($dnp->IvaRetencion > 0){
                        $porcentajeretencioniva = ($dnp->IvaRetencion * 100) / $dnp->SubTotal;
                    }
                    if($dnp->IsrRetencion > 0){
                        $porcentajeretencionisr = ($dnp->IsrRetencion * 100) / $dnp->SubTotal;
                    }
                    if($dnp->IepsRetencion > 0){
                        $porcentajeretencionieps = ($dnp->IepsRetencion * 100) / $dnp->SubTotal;
                    }
                    if($dnp->Codigo == 'DPPP'){
                        $filasdetallesnotaproveedor= $filasdetallesnotaproveedor.
                        '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfila" onclick="eliminarfila('.$contadorfilas.')" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dnp->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dnp->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$dnp->Codigo.'</b></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($dnp->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$dnp->Unidad.'" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Cantidad).'" data-parsley-min="0.1" data-parsley-max="1.0"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');">'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Ieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencioniva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->IvaRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionisr).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->IsrRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->IepsRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]"  value="0" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->PrecioMoneda).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->DescuentoMoneda).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                        '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                    '</div>'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                        '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly data-parsley-length="[1, 20]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$nombreclaveproducto.'" readonly></td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                        '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesunidades" data-toggle="tooltip" title="Ver Claves Unidades" onclick="listarclavesunidades('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                    '</div>'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                        '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly data-parsley-length="[1, 5]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly></td>'.
                        '</tr>';
                        $tipodetalles = 'dppp';
                    }else{
                        $filasdetallesnotaproveedor= $filasdetallesnotaproveedor.
                        '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfila" onclick="eliminarfila('.$contadorfilas.')" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dnp->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dnp->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$dnp->Codigo.'</b></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($dnp->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$dnp->Unidad.'" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Cantidad).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');revisarexistenciasalmacen('.$contadorfilas.');">'.
                                '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dnp->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);">'.
                                '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                                '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'.
                                '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Ieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencioniva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->IvaRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionisr).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->IsrRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->IepsRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]"  value="0" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->PrecioMoneda).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="'.Helpers::convertirvalorcorrecto($dnp->DescuentoMoneda).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                        '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                    '</div>'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                        '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly data-parsley-length="[1, 20]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$nombreclaveproducto.'" readonly></td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                        '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesunidades" data-toggle="tooltip" title="Ver Claves Unidades" onclick="listarclavesunidades('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                    '</div>'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                        '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly data-parsley-length="[1, 5]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly></td>'.
                        '</tr>';
                        $tipodetalles = 'codigos';
                    }
                    $contadorproductos++;
                    $contadorfilas++;
            }
        }
        //nota proveedor documentos
        $documentosnotaproveedor = NotaProveedorDocumento::where('Nota', $request->notamodificar)->orderBy('Item', 'ASC')->get();
        $numerodocumentosnotaproveedor = NotaProveedorDocumento::where('Nota', $request->notamodificar)->count();
        $filasdocumentosnotaproveedor = '';
        $arraycompras = array();
        if($numerodocumentosnotaproveedor > 0){
            $contadorfilascompras = 0;
            $tipo="modificacion";
            $descuentocompras = 0;
            foreach($documentosnotaproveedor as $docnp){
                    array_push($arraycompras, $docnp->Compra);
                    $descuentocomp = 0;
                    $descuentoscompra = NotaProveedorDocumento::where('Nota', '<>', $request->notamodificar)->where('Compra', $docnp->Compra)->get();
                    foreach($descuentoscompra as $descuento){
                        $descuentocomp = $descuentocomp + $descuento->Descuento;
                    }
                    $compra = Compra::where('Compra', $docnp->Compra)->first();
                    $saldo = $compra->Saldo + $compra->Descuentos;
                    $filasdocumentosnotaproveedor= $filasdocumentosnotaproveedor.
                    '<tr class="filascompras" id="filacompra'.$contadorfilascompras.'">'.
                        '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfilacompra" onclick="eliminarfilacompranotaproveedor('.$contadorfilascompras.')" >X</div><input type="hidden" class="form-control itemcomprapartida" name="itemcomprapartida[]" value="'.$docnp->Item.'" readonly><input type="hidden" class="form-control compraagregadoen" name="compraagregadoen[]" value="NA" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control compraaplicarpartida" name="compraaplicarpartida[]" value="'.$compra->Compra.'" readonly>'.$compra->Compra.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control fechacomprapartida" name="fechacomprapartida[]" value="'.$compra->Fecha.'" readonly>'.$compra->Fecha.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control facturacomprapartida" name="facturacomprapartida[]" value="'.$compra->Factura.'" readonly>'.$compra->Factura.'</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd totalpesoscomprapartida" name="totalpesoscomprapartida[]" value="'.Helpers::convertirvalorcorrecto($compra->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonoscomprapartida" name="abonoscomprapartida[]" value="'.Helpers::convertirvalorcorrecto($compra->Abonos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd notascreditocomprapartida" name="notascreditocomprapartida[]" value="'.Helpers::convertirvalorcorrecto($descuentocomp).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdetcom divorinputmodmd descuentopesoscomprapartida" name="descuentopesoscomprapartida[]" value="'.Helpers::convertirvalorcorrecto($docnp->Descuento).'" data-parsley-max="'.Helpers::convertirvalorcorrecto($saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilastablacompras('.$contadorfilascompras.');" ></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd saldocomprapartida" name="saldocomprapartida[]" value="'.Helpers::convertirvalorcorrecto($compra->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '</tr>';
                    $contadorproductos++;
                    $contadorfilas++;
                    $descuentocompras = $descuentocompras+$docnp->Descuento;
            }
        }
        $diferencia = $notaproveedor->Total - $descuentocompras;
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($notaproveedor->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($notaproveedor->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($notaproveedor->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }
        $data = array(
            "notaproveedor" => $notaproveedor,
            "filasdetallesnotaproveedor" => $filasdetallesnotaproveedor,
            "numerodetallesnotaproveedor" => $numerodetallesnotaproveedor,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "tipodetalles" => $tipodetalles,
            "almacen" => $almacen,
            "proveedor" => $proveedor,
            "filasdocumentosnotaproveedor" => $filasdocumentosnotaproveedor,
            "numerodocumentosnotaproveedor" => $numerodocumentosnotaproveedor,
            "contadorfilascompras" => $contadorfilascompras,
            "arraycompras" => $arraycompras,
            "descuentocompras" => Helpers::convertirvalorcorrecto($descuentocompras),
            "diferencia" => Helpers::convertirvalorcorrecto($diferencia),
            "fecha" => Helpers::formatoinputdatetime($notaproveedor->Fecha),
            "fechaemitida" => Helpers::formatoinputdatetime($notaproveedor->FechaEmitida),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($notaproveedor->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($notaproveedor->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($notaproveedor->Descuento),
            "ieps" => Helpers::convertirvalorcorrecto($notaproveedor->Ieps),
            "subtotal" => Helpers::convertirvalorcorrecto($notaproveedor->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($notaproveedor->Iva),
            "ivaretencion" => Helpers::convertirvalorcorrecto($notaproveedor->IvaRetencion),
            "isrretencion" => Helpers::convertirvalorcorrecto($notaproveedor->IsrRetencion),
            "iepsretencion" => Helpers::convertirvalorcorrecto($notaproveedor->IepsRetencion),
            "total" => Helpers::convertirvalorcorrecto($notaproveedor->Total),
            "tipocambio" => Helpers::convertirvalorcorrecto($notaproveedor->TipoCambio),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }

    //cambios
    public function notas_credito_proveedores_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $uuid=$request->uuid;
        $notaproveedor = $request->notaproveedorbd;
	    $ExisteUUID = NotaProveedor::where('Nota', '<>', $notaproveedor)->where('UUID', $uuid )->where('Status', '<>', 'BAJA')->first();
	    if($ExisteUUID == true){
	        $NotaProveedor = 1;
	    }else{
            $NotaProveedor = NotaProveedor::where('Nota', $notaproveedor)->first();
            //array detalles antes de modificacion
            $ArrayDetallesNotaAnterior = Array();
            $DetallesNotaAnterior = NotaProveedorDetalle::where('Nota', $notaproveedor)->get();
            foreach($DetallesNotaAnterior as $detalle){
                //array_push($ArrayDetallesNotaAnterior, $detalle->Codigo);
                array_push($ArrayDetallesNotaAnterior, $detalle->Nota.'#'.$detalle->Codigo.'#'.$detalle->Item);
            }
            //array detalles despues de modificacion
            $ArrayDetallesNotaNuevo = Array();
            foreach ($request->codigopartida as $key => $nuevocodigo){
                //array_push($ArrayDetallesNotaNuevo, $nuevocodigo);
                if($request->agregadoen [$key] == 'NA'){
                    array_push($ArrayDetallesNotaNuevo, $notaproveedor.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
                }
            }
            //diferencias entre arreglos
            $diferencias_arreglos = array_diff($ArrayDetallesNotaAnterior, $ArrayDetallesNotaNuevo);
            //iteramos las diferencias entre arreglos
            if(count($diferencias_arreglos) > 0){
                foreach($diferencias_arreglos as $eliminapartida){
                    $explode_d = explode("#",$eliminapartida);
                    $detallenota = NotaProveedorDetalle::where('Nota', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->first();
                    //sumar existencias a almacen principal
                    $SumarExistenciaAlmacen = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacen)->first();
                    $SumarExistenciaNuevaAlmacen = $SumarExistenciaAlmacen->Existencias + $detallenota->Cantidad;
                    Existencia::where('Codigo', $explode_d[1])
                                ->where('Almacen', $request->numeroalmacen)
                                ->update([
                                    'Existencias' => Helpers::convertirvalorcorrecto($SumarExistenciaNuevaAlmacen)
                                ]);
                    //eliminar detalle
                    $eliminardetalle= NotaProveedorDetalle::where('Nota', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
                }
            }
            //array detalles documentos antes de modificacion
            $ArrayDetallesDocumentosNotaAnterior = Array();
            $DetallesDocumentosNotaAnterior = NotaProveedorDocumento::where('Nota', $notaproveedor)->get();
            foreach($DetallesDocumentosNotaAnterior as $detalledocumento){
                //array_push($ArrayDetallesDocumentosNotaAnterior, $detalledocumento->Compra);
                array_push($ArrayDetallesDocumentosNotaAnterior, $detalledocumento->Nota.'#'.$detalledocumento->Compra.'#'.$detalledocumento->Item);
            }
            //array detalles documentos despues de modificacion
            $ArrayDetallesDocumentosNotaNuevo = Array();
            foreach ($request->compraaplicarpartida as $key => $nuevacompra){
                //array_push($ArrayDetallesDocumentosNotaNuevo, $nuevacompra);
                if($request->compraagregadoen [$key] == 'NA'){
                    array_push($ArrayDetallesDocumentosNotaNuevo, $notaproveedor.'#'.$nuevacompra.'#'.$request->itemcomprapartida [$key]);
                }
            }
            //diferencias entre arreglos
            $diferencias_arreglos = array_diff($ArrayDetallesDocumentosNotaAnterior, $ArrayDetallesDocumentosNotaNuevo);
            //iteramos las diferencias entre arreglos
            if(count($diferencias_arreglos) > 0){
                foreach($diferencias_arreglos as $eliminapartida){
                    $explode_d = explode("#",$eliminapartida);
                    $detalledocumentonota = NotaProveedorDocumento::where('Nota', $explode_d[0])->where('Compra', $explode_d[1])->where('Item', $explode_d[2])->first();
                    //Regresar saldo y descuentos a la compra
                    $notaproveedordocumento = NotaProveedorDocumento::where('Nota', $explode_d[0])->where('Compra', $explode_d[1])->where('Item', $explode_d[2])->first();
                    $compradocumento = Compra::where('Compra', $explode_d[1])->first();
                    $NuevoDescuentos = $compradocumento->Descuentos - $notaproveedordocumento->Descuento;
                    $NuevoSaldo = $compradocumento->Saldo + $notaproveedordocumento->Descuento;
                    Compra::where('Compra', $explode_d[1])
                    ->update([
                        'Descuentos' => Helpers::convertirvalorcorrecto($NuevoDescuentos),
                        'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo)
                    ]);
                    //eliminar detalle
                    $eliminardetalledocumento= NotaProveedorDocumento::where('Nota', $explode_d[0])->where('Compra', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
                }
            }
            //modificar nota
            NotaProveedor::where('Nota', $notaproveedor)
            ->update([
                'Proveedor'=>$request->numeroproveedor,
                'Fecha'=>Carbon::parse($request->fecha)->toDateTimeString(),
                'UUID'=>$request->uuid,
                'NotaProveedor'=>$request->notaproveedor,
                'Almacen'=>$request->numeroalmacen,
                'Importe'=>$request->importe,
                'Descuento'=>$request->descuento,
                'Ieps'=>$request->ieps,
                'SubTotal'=>$request->subtotal,
                'Iva'=>$request->iva,
                'IvaRetencion'=>$request->retencioniva,
                'IsrRetencion'=>$request->retencionisr,
                'IepsRetencion'=>$request->retencionieps,
                'Total'=>$request->total,
                'Obs'=>$request->observaciones,
                'Moneda'=>$request->moneda,
                'TipoCambio'=>$request->pesosmoneda,
                'FechaEmitida'=>Carbon::parse($request->fechaemitida)->toDateTimeString(),
                'EmisorRfc'=>$request->emisorrfc,
                'EmisorNombre'=>$request->emisornombre,
                'ReceptorRfc'=>$request->receptorrfc,
                'ReceptorNombre'=>$request->receptornombre
            ]);
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "NOTAS PROVEEDOR";
            $BitacoraDocumento->Movimiento = $notaproveedor;
            $BitacoraDocumento->Aplicacion = "CAMBIO";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = $NotaProveedor->Status;
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $this->periodohoy;
            $BitacoraDocumento->save();
            //detalles
            foreach ($request->codigopartida as $key => $codigopartida){
                //if la partida se agrego en la modificacion se realiza un insert
                if($request->agregadoen [$key] == 'modificacion'){
                    $contaritems = NotaProveedorDetalle::select('Item')->where('Nota', $notaproveedor)->count();
                    if($contaritems > 0){
                        $item = NotaProveedorDetalle::select('Item')->where('Nota', $notaproveedor)->orderBy('Item', 'DESC')->take(1)->get();
                        $ultimoitem = $item[0]->Item+1;
                    }else{
                        $ultimoitem = 1;
                    }
                    $NotaProveedorDetalle=new NotaProveedorDetalle;
                    $NotaProveedorDetalle->Nota = $notaproveedor;
                    $NotaProveedorDetalle->Proveedor = $request->numeroproveedor;
                    $NotaProveedorDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                    $NotaProveedorDetalle->Codigo = $codigopartida;
                    $NotaProveedorDetalle->Descripcion = $request->descripcionpartida [$key];
                    $NotaProveedorDetalle->Unidad = $request->unidadpartida [$key];
                    $NotaProveedorDetalle->Cantidad =  $request->cantidadpartida  [$key];
                    $NotaProveedorDetalle->Precio =  $request->preciopartida [$key];
                    $NotaProveedorDetalle->Importe = $request->importepartida [$key];
                    $NotaProveedorDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                    $NotaProveedorDetalle->Descuento = $request->descuentopesospartida [$key];
                    $NotaProveedorDetalle->ImporteDescuento = $request->importedescuentopesospartida [$key];
                    $NotaProveedorDetalle->Ieps = $request->trasladoiepspesospartida [$key];
                    $NotaProveedorDetalle->SubTotal = $request->subtotalpartida [$key];
                    $NotaProveedorDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                    $NotaProveedorDetalle->Iva = $request->trasladoivapesospartida [$key];
                    $NotaProveedorDetalle->IvaRetencion = $request->retencionivapesospartida [$key];
                    $NotaProveedorDetalle->IsrRetencion = $request->retencionisrpesospartida [$key];
                    $NotaProveedorDetalle->IepsRetencion = $request->retencioniepspesospartida [$key];
                    $NotaProveedorDetalle->Total = $request->totalpesospartida [$key];
                    $NotaProveedorDetalle->Partida = $request->partidapartida [$key];
                    $NotaProveedorDetalle->PrecioMoneda = $request->preciomonedapartida [$key];
                    $NotaProveedorDetalle->DescuentoMoneda = $request->descuentopartida [$key];
                    $NotaProveedorDetalle->ClaveProducto = $request->claveproductopartida [$key];
                    $NotaProveedorDetalle->ClaveUnidad = $request->claveunidadpartida [$key];
                    $NotaProveedorDetalle->Item = $ultimoitem;
                    $NotaProveedorDetalle->save();
                    if($codigopartida != 'DPPP'){
                        //restar existencias a almacen principal
                        $RestarExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                        $RestarExistenciaNuevaAlmacen = $RestarExistenciaAlmacen->Existencias - $request->cantidadpartida  [$key];
                        Existencia::where('Codigo', $codigopartida)
                                    ->where('Almacen', $request->numeroalmacen)
                                    ->update([
                                        'Existencias' => Helpers::convertirvalorcorrecto($RestarExistenciaNuevaAlmacen)
                                    ]);
                    }
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar detalle
                    NotaProveedorDetalle::where('Nota', $notaproveedor)
                    ->where('Item', $request->itempartida [$key])
                    ->update([
                        'Proveedor' => $request->numeroproveedor,
                        'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                        'Codigo' => $codigopartida,
                        'Descripcion' => $request->descripcionpartida [$key],
                        'Unidad' => $request->unidadpartida [$key],
                        'Cantidad' =>  $request->cantidadpartida  [$key],
                        'Precio' =>  $request->preciopartida [$key],
                        'Importe' => $request->importepartida [$key],
                        'Dcto' => $request->descuentoporcentajepartida [$key],
                        'Descuento' => $request->descuentopesospartida [$key],
                        'ImporteDescuento' => $request->importedescuentopesospartida [$key],
                        'Ieps' => $request->trasladoiepspesospartida [$key],
                        'SubTotal' => $request->subtotalpartida [$key],
                        'Impuesto' => $request->ivaporcentajepartida [$key],
                        'Iva' => $request->trasladoivapesospartida [$key],
                        'IvaRetencion' => $request->retencionivapesospartida [$key],
                        'IsrRetencion' => $request->retencionisrpesospartida [$key],
                        'IepsRetencion' => $request->retencioniepspesospartida [$key],
                        'Total' => $request->totalpesospartida [$key],
                        'Partida' => $request->partida [$key],
                        'PrecioMoneda' => $request->preciomonedapartida [$key],
                        'DescuentoMoneda' => $request->descuentopartida [$key],
                        'ClaveProducto' => $request->claveproductopartida [$key],
                        'ClaveUnidad' => $request->claveunidadpartida [$key]
                    ]);
                    if($codigopartida != 'DPPP'){
                        //sumar existencias del almacen
                        $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->count();
                        if($ContarExistenciaAlmacen > 0){
                            $ExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                            $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias + $request->cantidadpartidadb [$key];
                            Existencia::where('Codigo', $codigopartida)
                                        ->where('Almacen', $request->numeroalmacen)
                                        ->update([
                                            'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                        ]);
                        }else{
                            $ExistenciaAlmacen = new Existencia;
                            $ExistenciaAlmacen->Codigo = $codigopartida;
                            $ExistenciaAlmacen->Almacen = $request->numeroalmacen;
                            $ExistenciaAlmacen->Existencias = $request->cantidadpartidadb [$key];
                            $ExistenciaAlmacen->save();
                        }
                        //restar existencias a almacen principal
                        $ExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                        $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias - $request->cantidadpartida [$key];
                        Existencia::where('Codigo', $codigopartida)
                                    ->where('Almacen', $request->numeroalmacen)
                                    ->update([
                                        'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                    ]);
                    }
                }
            }
            //detalles documentos
            foreach ($request->compraaplicarpartida as $key => $comprapartida){
                //if la partida se agrego en la modificacion se realiza un insert
                if($request->compraagregadoen [$key] == 'modificacion'){
                    $itemdocumento = NotaProveedorDocumento::select('Item')->where('Nota', $notaproveedor)->orderBy('Item', 'DESC')->take(1)->get();
                    $ultimoitemdocumento = $itemdocumento[0]->Item+1;
                    $NotaProveedorDocumento=new NotaProveedorDocumento;
                    $NotaProveedorDocumento->Nota = $notaproveedor;
                    $NotaProveedorDocumento->Compra = $comprapartida;
                    $NotaProveedorDocumento->Descuento = $request->descuentopesoscomprapartida [$key];
                    $NotaProveedorDocumento->Item = $ultimoitemdocumento;
                    $NotaProveedorDocumento->save();
                    //Modificar Compra
                    Compra::where('Compra', $comprapartida)
                    ->update([
                        'Descuentos' => $request->descuentopesoscomprapartida [$key],
                        'Saldo' => $request->saldocomprapartida [$key]
                    ]);
                    //Si el saldo es igual a 0 liquidar compra
                    if($request->saldocomprapartida [$key] == Helpers::convertirvalorcorrecto(0)){
                        Compra::where('Compra', $comprapartida)
                                ->update([
                                    'Status' => "LIQUIDADA"
                                ]);
                    }
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar detalle
                    NotaProveedorDocumento::where('Nota', $notaproveedor)
                    ->where('Item', $request->itemcomprapartida [$key])
                    ->update([
                        'Descuento' => $request->descuentopesoscomprapartida [$key]
                    ]);
                    //Regresar saldo y descuentos a la compra
                    $notaproveedordocumento = NotaProveedorDocumento::where('Nota', $notaproveedor)->where('Compra', $comprapartida)->where('Item', $request->itemcomprapartida [$key])->first();
                    $compradocumento = Compra::where('Compra', $comprapartida)->first();
                    $NuevoDescuentos = $compradocumento->Descuentos - $notaproveedordocumento->Descuento;
                    $NuevoSaldo = $compradocumento->Saldo + $notaproveedordocumento->Descuento;
                    Compra::where('Compra', $comprapartida)
                    ->update([
                        'Descuentos' => Helpers::convertirvalorcorrecto($NuevoDescuentos),
                        'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo)
                    ]);
                    //Modificar Compra
                    Compra::where('Compra', $comprapartida)
                    ->update([
                        'Descuentos' => $request->descuentopesoscomprapartida [$key],
                        'Saldo' => $request->saldocomprapartida [$key]
                    ]);
                    //Si el saldo es igual a 0 liquidar compra
                    if($request->saldocomprapartida [$key] == Helpers::convertirvalorcorrecto(0)){
                        Compra::where('Compra', $comprapartida)
                                ->update([
                                    'Status' => "LIQUIDADA"
                                ]);
                    }
                }
            }
        }
        return response()->json($NotaProveedor);
    }

    //buscar folio on key up
    public function notas_credito_proveedores_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = VistaNotaCreditoProveedor::orderBy('Folio', 'ASC')->get();
            return DataTables::of($data)
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->make(true);
        }
    }
    //generacion de formato en PDF
    public function notas_credito_proveedores_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        //primero eliminar todos los archivos zip
        Helpers::eliminararchivoszipgenerados();
        if($request->imprimirdirectamente == 1){
            $notascreditoproveedor = NotaProveedor::where('Nota', $request->arraypdf)->get();
        }else{
            $tipogeneracionpdf = $request->tipogeneracionpdf;
            if($tipogeneracionpdf == 0){
                $notascreditoproveedor = NotaProveedor::whereIn('Nota', $request->arraypdf)->orderBy('Folio', 'ASC')->take(500)->get();
            }else{
                //$contrarecibos = ContraRecibo::where('Fecha', $request->anopdf)->get();
                $fechainiciopdf = date($request->fechainiciopdf);
                $fechaterminacionpdf = date($request->fechaterminacionpdf);
                if ($request->has("seriesdisponiblesdocumento")){
                    $notascreditoproveedor = NotaProveedor::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->whereIn('Serie', $request->seriesdisponiblesdocumento)->orderBy('Folio', 'ASC')->take(500)->get();
                }else{
                    $notascreditoproveedor = NotaProveedor::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(500)->get();
                }
            }
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $arrayfilespdf = array();
        foreach ($notascreditoproveedor as $ncp){
            $data=array();
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
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'NotasCreditoProveedor')->where('Documento', $ncp->Nota)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'NotasCreditoProveedor')
            ->where('frd.Documento', $ncp->Nota)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                "notacreditoproveedor"=>$ncp,
                "numerofirmas"=>$numerofirmas,
                "firmas"=>$firmas,
                "descuentonotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Descuento),
                "subtotalnotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->SubTotal),
                "ivanotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Iva),
                "totalnotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Total),
                "proveedor" => $proveedor,
                "datadetalle" => $datadetalle,
                "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.notascreditoproveedores.formato_pdf_notascreditoproveedores', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$ncp->Nota.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($notascreditoproveedor as $notccp){
            $ArchivoPDF = "PDF".$notccp->Nota.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
            array_push($arrayfilespdf,$ArchivoPDF);
        }
        $pdfMerger->merge(); //unirlos
        if($request->imprimirdirectamente == 1){
            $archivoacopiar = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $carpetacopias = public_path('xml_descargados/'.$ArchivoPDF);
            File::copy($archivoacopiar, $carpetacopias);
            return response()->json($ArchivoPDF);
        }else{
            if($request->descargar_xml == 0){
                $pdfMerger->save("NotasCreditoProveedor.pdf", "browser");//mostrarlos en el navegador
            }else{
                //carpeta donde se guardara el archivo zip
                $public_dir=public_path();
                // Zip File Name
                $zipFileName = 'DocumentosPDF.zip';
                // Crear Objeto ZipArchive
                $zip = new ZipArchive;
                if ($zip->open($public_dir . '/xml_descargados/' . $zipFileName, ZipArchive::CREATE) === TRUE) {
                    // Agregar archivos que se comprimiran
                    foreach($arrayfilespdf as $afp) {
                        $zip->addFile(Storage::disk('local3')->getAdapter()->applyPathPrefix($afp),$afp);
                    }
                    //terminar proceso
                    $zip->close();
                }
                // Set Encabezados para descargar
                $headers = array(
                    'Content-Type' => 'application/octet-stream',
                );
                $filetopath=$public_dir.'/xml_descargados/'.$zipFileName;
                // Create Download Response
                if(file_exists($filetopath)){
                    return response()->download($filetopath,$zipFileName,$headers);
                }
            }
        }
    }

    //generacion de formato en PDF
    public function notas_credito_proveedores_generar_pdfs_indiv($documento){
        $notascreditoproveedor = NotaProveedor::where('Nota', $documento)->get();
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
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'NotasCreditoProveedor')->where('Documento', $ncp->Nota)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'NotasCreditoProveedor')
            ->where('frd.Documento', $ncp->Nota)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                "notacreditoproveedor"=>$ncp,
                "numerofirmas"=>$numerofirmas,
                "firmas"=>$firmas,
                "descuentonotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Descuento),
                "subtotalnotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->SubTotal),
                "ivanotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Iva),
                "totalnotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Total),
                "proveedor" => $proveedor,
                "datadetalle" => $datadetalle,
                "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.notascreditoproveedores.formato_pdf_notascreditoproveedores', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //obtener datos para enviar email
    public function notas_credito_proveedores_obtener_datos_envio_email(Request $request){
        $notaproveedor = NotaProveedor::where('Nota', $request->documento)->first();
        $proveedor = Proveedor::where('Numero',$notaproveedor->Proveedor)->first();
        $email2cc = '';
        $email3cc = '';
        if($proveedor->Email2 != '' || $proveedor->Email2 != null){
            $email2cc = $proveedor->Email2;
        }
        if($proveedor->Email3 != '' || $proveedor->Email3 != null){
            $email3cc = $proveedor->Email3;
        }
        $data = array(
            'notaproveedor' => $notaproveedor,
            'proveedor' => $proveedor,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $proveedor->Email1,
            'email2cc' => $email2cc,
            'email3cc' => $email3cc,
            'correodefault1enviodocumentos' => $this->correodefault1enviodocumentos,
            'correodefault2enviodocumentos' => $this->correodefault2enviodocumentos
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function notas_credito_proveedores_enviar_pdfs_email(Request $request){
        $notascreditoproveedor = NotaProveedor::where('Nota', $request->emaildocumento)->get();
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
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'NotasCreditoProveedor')->where('Documento', $ncp->Nota)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'NotasCreditoProveedor')
            ->where('frd.Documento', $ncp->Nota)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                "notacreditoproveedor"=>$ncp,
                "numerofirmas"=>$numerofirmas,
                "firmas"=>$firmas,
                "descuentonotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Descuento),
                "subtotalnotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->SubTotal),
                "ivanotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Iva),
                "totalnotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Total),
                "proveedor" => $proveedor,
                "datadetalle" => $datadetalle,
                "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.notascreditoproveedores.formato_pdf_notascreditoproveedores', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            $datosdocumento = NotaProveedor::where('Nota', $request->emaildocumento)->first();
            //enviar correo electrónico
            $nombre = 'Receptor envio de correos';
            $receptor = $request->emailpara;
            $arraycc = array();
            array_push($arraycc, $request->emailpara);
            if($request->email2cc != ""){
                array_push($arraycc, $request->email2cc);
            }
            if($request->email3cc != ""){
                array_push($arraycc, $request->email3cc);
            }
            if($request->correosconcopia != null){
                foreach($request->correosconcopia as $cc){
                    if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                        array_push($arraycc, $cc);
                    }
                }
            }
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailmensaje;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $correos, $asunto, $pdf, $emaildocumento) {
                $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                        ->cc($arraycc)
                        ->subject($asunto)
                        ->attachData($pdf->output(), "NotaCreditoProveedorNo".$emaildocumento.".pdf");
            });
        } catch(\Exception $e) {
            $receptor = 'osbaldo.anzaldo@utpcamiones.com.mx';
            $correos = ['osbaldo.anzaldo@utpcamiones.com.mx'];
            $msj = 'Error al enviar correo';
            Mail::send('correos.errorenvio.error', compact('e','msj'), function($message) use ($receptor, $correos) {
                $message->to($receptor)
                        ->cc($correos)
                        ->subject('Error al enviar correo nuevo usuario');
            });
        }
    }

    //exportar a excel
    public function notas_credito_proveedores_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('NotasCreditoProveedor', Auth::user()->id);
        return Excel::download(new NotasCreditoProveedoresExport($configuraciones_tabla['campos_consulta'],$request->periodo), "notascreditoproveedores-".$request->periodo.".xlsx");
    }
    //configuracion tabla
    public function notas_credito_proveedor_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('NotasCreditoProveedor', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'NotasCreditoProveedor')->where('IdUsuario', Auth::user()->id)
            ->update([
                'campos_activados' => $request->string_datos_tabla_true,
                'campos_desactivados' => $string_datos_tabla_false,
                'columnas_ordenadas' => $request->string_datos_ordenamiento_columnas,
                'usuario' => Auth::user()->user,
                'primerordenamiento' => $request->selectorderby1,
                'formaprimerordenamiento' => $request->deorderby1,
                'segundoordenamiento' => $request->selectorderby2,
                'formasegundoordenamiento' => $request->deorderby2,
                'tercerordenamiento' => $request->selectorderby3,
                'formatercerordenamiento' => $request->deorderby3,
                'campos_busquedas' => substr($selectmultiple, 1),
            ]);
        }else{
            $Configuracion_Tabla=new Configuracion_Tabla;
            $Configuracion_Tabla->tabla='NotasCreditoProveedor';
            $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
            $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
            $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
            $Configuracion_Tabla->ordenar = 0;
            $Configuracion_Tabla->usuario = Auth::user()->user;
            $Configuracion_Tabla->campos_busquedas = substr($selectmultiple, 1);
            $Configuracion_Tabla->primerordenamiento = $request->selectorderby1;
            $Configuracion_Tabla->formaprimerordenamiento = $request->deorderby1;
            $Configuracion_Tabla->segundoordenamiento =  $request->selectorderby2;
            $Configuracion_Tabla->formasegundoordenamiento =  $request->deorderby2;
            $Configuracion_Tabla->tercerordenamiento = $request->selectorderby3;
            $Configuracion_Tabla->formatercerordenamiento = $request->deorderby3;
            $Configuracion_Tabla->IdUsuario = Auth::user()->id;
            $Configuracion_Tabla->save();
        }
        return redirect()->route('notas_credito_proveedores');
    }
}
