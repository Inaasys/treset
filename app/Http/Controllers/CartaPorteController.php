<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use DB;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NotasCreditoClientesExport;
use App\CartaPorteDocumentos;
use App\NotaCliente;
use App\NotaClienteDetalle;
use App\NotaClienteDocumento;
use App\Factura;
use App\FacturaDetalle;
use App\Cliente;
use App\Producto;
use App\Pais;
use App\Estado;
use App\Municipio;
use App\CodigoPostal;
use App\FormaPago;
use App\MetodoPago;
use App\UsoCFDI;
use App\c_TipoRelacion;
use App\c_RegimenFiscal;
use App\BitacoraDocumento;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Configuracion_Tabla;
use App\VistaCartaPorte;
use App\VistaObtenerExistenciaProducto;
use App\FolioComprobanteTraslado;
use App\Comprobante;
use App\c_ConfiguracionAutoTransporte;
use App\c_CveTransporte;
use App\Operador;
use App\Vehiculo;
use App\CartaPorte;
use App\CartaPorteDetalles;
use App\c_MaterialPeligroso;
use App\c_TipoEmbalaje;
use App\c_ClaveProdServCP;
use Config;
use Mail;
use Facturapi\Facturapi;
use Storage;
use LynX39\LaraPdfMerger\Facades\PdfMerger;

class CartaPorteController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //API FACTURAPI
        $this->facturapi = new Facturapi( config('app.keyfacturapi') ); //
    }

    public function carta_porte(){
        $contarserieusuario = FolioComprobanteTraslado::where('Predeterminar', '+')->count();
        if($contarserieusuario == 0){
            $FolioComprobanteTraslado = FolioComprobanteTraslado::orderBy('Numero','DESC')->take(1)->get();

            $serieusuario = (isset($FolioComprobanteTraslado[0]) ? $FolioComprobanteTraslado[0]->Serie : 'CP');
            $esquema = (isset($FolioComprobanteTraslado[0]) ? $FolioComprobanteTraslado[0]->Esquema : 'TRASLADO');
        }else{
            $FolioComprobanteTraslado = FolioComprobanteTraslado::where('Predeterminar', '+')->first();
            $serieusuario = $FolioComprobanteTraslado->Serie;
            $esquema = $FolioComprobanteTraslado->Esquema;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('CartaPorte', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('carta_porte_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('notas_credito_clientes_exportar_excel');
        $rutacreardocumento = route('notas_credito_clientes_generar_pdfs');
        $lugarexpedicion = $this->lugarexpedicion;
        $claveregimenfiscal = '';
        $regimenfiscal = '';
        if($this->regimenfiscal != ''){
            $c_RegimenFiscal = c_RegimenFiscal::where('Clave', $this->regimenfiscal)->first();
            $claveregimenfiscal = $c_RegimenFiscal->Clave;
            $regimenfiscal = $c_RegimenFiscal->Nombre;
        }
        return view('registros.cartasporte.cartasporte', compact('serieusuario','esquema','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento','lugarexpedicion','claveregimenfiscal','regimenfiscal'));
    }

    public function carta_porte_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('CartaPorte', Auth::user()->id);

            $fechahoy = Carbon::now()->toDateString();
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaCartaPorte::select($configuraciones_tabla['campos_consulta'])->where('Periodo', $periodo);
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos(\''.$data->CartaPorte .'\')">Cambios</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar(\''.$data->CartaPorte .'\')">Bajas</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="timbrarcarta(\''. $data->CartaPorte .'\')">Timbrar</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="'.route('carta_porte_generar_pdfs_indiv',$data->CartaPorte).'" target="_blank">Ver Documento</a></li>'.
                                                //'<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="timbrarnota(\''.$data->Nota .'\')">Timbrar Nota</a></li>'.
                                                //'<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="cancelartimbre(\''.$data->Nota .'\')">Cancelar Timbre</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener ultimo folio
    public function carta_porte_obtener_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\CartaPorte', $request->serie);
        return response()->json($folio);
    }

    //obtener folios notas
    public function carta_porte_obtener_folios_fiscales(Request $request){
        if($request->ajax()){
            $data = FolioComprobanteTraslado::where('Status', 'ALTA')->OrderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarfoliofiscal(\''.$data->Serie.'\',\''.$data->Esquema.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener datos folio seleccionado
    public function carta_porte_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\CartaPorte', $request->Serie);
        return response()->json($folio);
    }

    //obtener clientes
    public function carta_porte_obtener_clientes(Request $request){
        if($request->ajax()){
            //$data = Cliente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            $data = DB::table('Clientes as c')
            ->leftJoin('c_FormaPago as fp', 'fp.Clave', '=', 'c.FormaPago')
            ->leftJoin('c_MetodoPago as mp', 'mp.Clave', '=', 'c.MetodoPago')
            ->leftJoin('c_UsoCFDI as uc', 'uc.Clave', '=', 'c.UsoCfdi')
            ->leftJoin('c_Pais as p', 'p.Clave', '=', 'c.Pais')
            ->select('c.Numero', 'c.Nombre', 'c.Plazo', 'c.Rfc', 'c.Agente', 'c.Credito', 'c.Saldo', 'c.Status', 'c.Municipio', 'c.Tipo', 'fp.Clave AS ClaveFormaPago', 'fp.Nombre AS NombreFormaPago', 'mp.Clave AS ClaveMetodoPago', 'mp.Nombre AS NombreMetodoPago', 'uc.Clave AS ClaveUsoCfdi', 'uc.Nombre AS NombreUsoCfdi', 'p.Clave AS ClavePais', 'p.Nombre AS NombrePais', 'c.Calle', 'c.NoExterior', 'c.NoInterior', 'c.Colonia', 'c.Localidad', 'c.Referencia', 'c.Municipio', 'c.Estado', 'c.CodigoPostal')
            ->where('c.Status', 'ALTA')
            ->orderBy("Numero", "DESC")
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$data->Rfc.'\',\''.$data->ClaveFormaPago.'\',\''.$data->NombreFormaPago.'\',\''.$data->ClaveMetodoPago.'\',\''.$data->NombreMetodoPago.'\',\''.$data->ClaveUsoCfdi.'\',\''.$data->NombreUsoCfdi.'\',\''.$data->ClavePais.'\',\''.$data->NombrePais.'\',\''.$data->Calle.'\',\''.$data->NoExterior.'\',\''.$data->NoInterior.'\',\''.$data->Colonia.'\',\''.$data->Localidad.'\',\''.$data->Referencia.'\',\''.$data->Municipio.'\',\''.$data->Estado.'\',\''.$data->CodigoPostal.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener cliente por numero
    public function carta_porte_obtener_cliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $rfc = '';
        $plazo = '';
        $credito = '';
        $saldo = '';
        $claveformapago = '';
        $formapago = '';
        $clavemetodopago = '';
        $metodopago = '';
        $claveusocfdi = '';
        $usocfdi = '';
        $claveresidenciafiscal = '';
        $residenciafiscal = '';
        $calle = '';
        $noexterior = '';
        $nointerior = '';
        $colonia = '';
        $localidad = '';
        $referencia = '';
        $municipio = '';
        $estado = '';
        $codigopostal = '';
        $existecliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->first();
            $datos = DB::table('Clientes as c')
            ->leftJoin('c_FormaPago as fp', 'fp.Clave', '=', 'c.FormaPago')
            ->leftJoin('c_MetodoPago as mp', 'mp.Clave', '=', 'c.MetodoPago')
            ->leftJoin('c_UsoCFDI as uc', 'uc.Clave', '=', 'c.UsoCfdi')
            ->leftJoin('c_Pais as p', 'p.Clave', '=', 'c.Pais')
            ->select('c.Numero', 'c.Status', 'fp.Clave AS claveformapago', 'fp.Nombre AS formapago', 'mp.Clave AS clavemetodopago', 'mp.Nombre AS metodopago', 'uc.Clave AS claveusocfdi', 'uc.Nombre AS usocfdi', 'p.Clave AS claveresidenciafiscal', 'p.Nombre AS residenciafiscal', 'c.Calle', 'c.NoExterior', 'c.NoInterior', 'c.Colonia', 'c.Localidad', 'c.Referencia', 'c.Municipio', 'c.Estado', 'c.CodigoPostal')
            ->where('c.Numero', $request->numerocliente)
            ->where('c.Status', 'ALTA')
            ->get();
            $claveformapago = $datos[0]->claveformapago;
            $formapago = $datos[0]->formapago;
            $clavemetodopago = $datos[0]->clavemetodopago;
            $metodopago = $datos[0]->metodopago;
            $claveusocfdi = $datos[0]->claveusocfdi;
            $usocfdi = $datos[0]->usocfdi;
            $claveresidenciafiscal = $datos[0]->claveresidenciafiscal;
            $residenciafiscal = $datos[0]->residenciafiscal;
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
            $rfc = $cliente->Rfc;
            $plazo = $cliente->Plazo;
            $credito = Helpers::convertirvalorcorrecto($cliente->Credito);
            $saldo = Helpers::convertirvalorcorrecto($cliente->Saldo);
            $calle = $datos[0]->Calle;
            $noexterior = $datos[0]->NoExterior;
            $nointerior = $datos[0]->NoInterior;
            $colonia = $datos[0]->Colonia;
            $localidad = $datos[0]->Localidad;
            $referencia = $datos[0]->Referencia;
            $municipio = $datos[0]->Municipio;
            $estado = $datos[0]->Estado;
            $codigopostal = $datos[0]->CodigoPostal;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'rfc' => $rfc,
            'plazo' => $plazo,
            'credito' => $credito,
            'saldo' => $saldo,
            'claveformapago' => $claveformapago,
            'formapago' => $formapago,
            'clavemetodopago' => $clavemetodopago,
            'metodopago' => $metodopago,
            'claveusocfdi' => $claveusocfdi,
            'usocfdi' => $usocfdi,
            'claveresidenciafiscal' => $claveresidenciafiscal,
            'residenciafiscal' => $residenciafiscal,
            'calle' => $calle,
            'noexterior' => $noexterior,
            'nointerior' => $nointerior,
            'colonia' => $colonia,
            'localidad' => $localidad,
            'referencia' => $referencia,
            'municipio' => $municipio,
            'estado' => $estado,
            'codigopostal' => $codigopostal,
        );
        return response()->json($data);
    }

    //obtener municipios
    public function carta_porte_obtener_municipios(Request $request){
        if($request->ajax()){
            $tipo = $request->tipo;
            $data = Municipio::query();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data) use ($tipo){
                    $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmunicipio('.$data->Numero.',\''.$data->Nombre .'\',\''.$tipo.'\')">Seleccionar</div>';
                    return $boton;
                })
                ->rawColumns(['operaciones'])
                ->make(true);
        }
    }

    //obtener est
    public function carta_porte_obtener_estados(Request $request){
        if($request->ajax()){
            $tipo = $request->tipo;
            $data = Estado::where('Pais','MEX')->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data) use ($tipo){
                    $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarestado('.$data->Numero.',\''.$data->Clave .'\',\''.$tipo.'\')">Seleccionar</div>';
                    return $boton;
                })
                ->rawColumns(['operaciones'])
                ->make(true);
        }
    }

    //obtener pai
    public function carta_porte_obtener_paises(Request $request){
        if($request->ajax()){
            $tipo = $request->tipo;
            $data = Pais::query();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data) use ($tipo){
                    $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarpais('.$data->Numero.',\''.$data->Nombre .'\',\''.$tipo.'\')">Seleccionar</div>';
                    return $boton;
                })
                ->rawColumns(['operaciones'])
                ->make(true);
        }
    }

    //obtener cp
    public function carta_porte_obtener_codigospostales(Request $request){
        if($request->ajax()){
            $tipo = $request->tipo;
            $data = CodigoPostal::query();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data) use ($tipo){
                    $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcp('.$data->Numero.',\''.$data->Clave .'\',\''.$tipo.'\')">Seleccionar</div>';
                    return $boton;
                })
                ->rawColumns(['operaciones'])
                ->make(true);
        }
    }

    //obtener config autotransporte
    public function carta_porte_obtener_coonfiguracionesautotransporte(Request $request){
        if($request->ajax()){
            $tipo = $request->tipo;
            $data = c_ConfiguracionAutoTransporte::query();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data) use ($tipo){
                    $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarconfigautotransporte('.$data->Numero.',\''.$data->Clave .'\',\''.$data->Descripcion.'\')">Seleccionar</div>';
                    return $boton;
                })
                ->rawColumns(['operaciones'])
                ->make(true);
        }
    }

    //obtener por clave
    public function carta_porte_obtener_configuracionautotransporte_por_clave(Request $request){
        $clave = '';
        $descripcion = '';
        $existeconfiguraciontransporte = c_ConfiguracionAutoTransporte::where('Clave', $request->claveconfigautotransporte)->count();
        if($existeconfiguraciontransporte > 0){
            $configuraciontransporte = c_ConfiguracionAutoTransporte::where('Clave', $request->claveconfigautotransporte)->first();
            $clave = $configuraciontransporte->Clave;
            $descripcion = $configuraciontransporte->Descripcion;
        }
        $data = array(
            'clave' => $clave,
            'descripcion' => $descripcion
        );
        return response()->json($data);
    }

    //obtener claves transportes
    public function carta_porte_obtener_clavestransporte(Request $request){
        if($request->ajax()){
            $tipo = $request->tipo;
            $data = c_CveTransporte::query();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data) use ($tipo){
                    $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclavetransporte('.$data->Numero.',\''.$data->Clave .'\',\''.$data->Descripcion.'\')">Seleccionar</div>';
                    return $boton;
                })
                ->rawColumns(['operaciones'])
                ->make(true);
        }

    }

    //obtener por clave
    public function carta_porte_obtener_clavetransporte_por_clave(Request $request){
        $clave = '';
        $descripcion = '';
        $existeclavetransporte = c_CveTransporte::where('Clave', $request->clavetransporte)->count();
        if($existeclavetransporte > 0){
            $clavetransporte = c_CveTransporte::where('Clave', $request->clavetransporte)->first();
            $clave = $clavetransporte->Clave;
            $descripcion = $clavetransporte->Descripcion;
        }
        $data = array(
            'clave' => $clave,
            'descripcion' => $descripcion
        );
        return response()->json($data);
    }


    //obtener productos
    public function carta_porte_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $numeroalmacen = $request->numeroalmacen;
            $tipooperacion = $request->tipooperacion;
            $stringfacturasseleccionadas = $request->stringfacturasseleccionadas;
            $arrayproductosseleccionables = Array();
            foreach(explode(",", $request->stringfacturasseleccionadas) as $factura){
                $detallesfactura = FacturaDetalle::where('Factura', $factura)->get();
                foreach($detallesfactura as $detalle){
                    array_push($arrayproductosseleccionables, $detalle->Codigo);
                }
            }
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($numeroalmacen, $tipooperacion, $stringfacturasseleccionadas){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->MaterialPeligroso.'\',\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$data->NombreClaveProducto.'\',\''.$data->NombreClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
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

    /**
     * @author Jose Alonso Espinares Romero
     * @return claves
     */
    public function carta_porte_obtener_claves_materiales_peligrosos(Request $request){
        if($request->ajax()){
            $fila = $request->fila;
            $data = c_MaterialPeligroso::query();
            return DataTables::of($data)
            ->addColumn('operaciones', function($data) use ($fila){
                $boton = $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclavepeligroso(\''.$data->Clave .'\',\''.htmlspecialchars($data->Descripcion,ENT_QUOTES) .'\','.$fila.')">Seleccionar</div>';
                return $boton;
            })
            ->rawColumns(['operaciones'])
            ->make(true);
        }
    }
    /**
     * @author Jose Alonso Espinares Romero
     * @return embalajes
     */
    public function carta_porte_obtener_claves_tipo_embalajes(Request $request){
        if($request->ajax()){
            $fila = $request->fila;
            $data = c_TipoEmbalaje::query();
            return DataTables::of($data)
            ->addColumn('operaciones', function($data) use ($fila){
                $boton = $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclavembalaje(\''.$data->Clave .'\',\''.htmlspecialchars($data->Descripcion,ENT_QUOTES) .'\','.$fila.')">Seleccionar</div>';
                return $boton;
            })
            ->rawColumns(['operaciones'])
            ->make(true);
        }
    }

    //obtener vehiculos
    public function carta_porte_obtener_vehiculos(Request $request){
        if($request->ajax()){
            $data = Vehiculo::OrderBy('id', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarvehiculo(\''.$data->id.'\',\''.$data->PermisoSCT.'\',\''.$data->NumeroPermisoSCT.'\',\''.$data->NombreAseguradora.'\',\''.$data->NumeroPolizaSeguro.'\',\''.$data->Placa.'\',\''.$data->Año.'\',\''.$data->SubTipoRemolque.'\',\''.$data->PlacaSubTipoRemolque.'\',\''.$data->Marca.'\',\''.$data->Modelo.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener vehiculo por numero
    public function carta_porte_obtener_vehiculo_por_numero(Request $request){
        $id = '';
        $PermisoSCT = '';
        $NumeroPermisoSCT = '';
        $NombreAseguradora = '';
        $NumeroPolizaSeguro = '';
        $Placa = '';
        $Año = '';
        $SubTipoRemolque = '';
        $PlacaSubTipoRemolque = '';
        $Modelo = '';
        $Marca = '';
        $existevehiculo = Vehiculo::where('id', $request->numerovehiculoempresa)->count();
        if($existevehiculo > 0){
            $vehiculo = Vehiculo::where('id', $request->numerovehiculoempresa)->first();
            $id = $vehiculo->id;
            $PermisoSCT = $vehiculo->PermisoSCT;
            $NumeroPermisoSCT = $vehiculo->NumeroPermisoSCT;
            $NombreAseguradora = $vehiculo->NombreAseguradora;
            $NumeroPolizaSeguro = $vehiculo->NumeroPolizaSeguro;
            $Placa = $vehiculo->Placa;
            $Año = $vehiculo->Año;
            $SubTipoRemolque = $vehiculo->SubTipoRemolque;
            $PlacaSubTipoRemolque = $vehiculo->PlacaSubTipoRemolque;
            $Modelo = $vehiculo->Modelo;
            $Marca = $vehiculo->Marca;
        }
        $data = array(
            'id' => $id,
            'PermisoSCT' => $PermisoSCT,
            'NumeroPermisoSCT' => $NumeroPermisoSCT,
            'NombreAseguradora' => $NombreAseguradora,
            'NumeroPolizaSeguro' => $NumeroPolizaSeguro,
            'Placa' => $Placa,
            'Año' => $Año,
            'SubTipoRemolque' => $SubTipoRemolque,
            'PlacaSubTipoRemolque' => $PlacaSubTipoRemolque,
            'Marca' => $Marca,
            'Modelo' => $Modelo,
        );
        return response()->json($data);

    }
    //obtener operadores
    public function carta_porte_obtener_operadores(Request $request){
        if($request->ajax()){
            $data = Operador::OrderBy('id', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaroperador(\''.$data->id.'\',\''.$data->Rfc.'\',\''.$data->Nombre.'\',\''.$data->NumeroLicencia.'\',\''.$data->Calle.'\',\''.$data->NoExterior.'\',\''.$data->NoInterior.'\',\''.$data->Colonia.'\',\''.$data->Localidad.'\',\''.$data->Referencia.'\',\''.$data->Municipio.'\',\''.$data->Estado.'\',\''.$data->Pais.'\',\''.$data->CodigoPostal.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener operador por numero
    public function carta_porte_obtener_operador_por_numero(Request $request){
        $id = '';
        $Rfc = '';
        $Nombre = '';
        $NumeroLicencia = '';
        $Calle = '';
        $NoExterior = '';
        $NoInterior = '';
        $Colonia = '';
        $Localidad = '';
        $Referencia = '';
        $Municipio = '';
        $Estado = '';
        $Pais = '';
        $CodigoPostal = '';
        $existeoperador = Operador::where('id', $request->numerooperador)->count();
        if($existeoperador > 0){
            $operador = Operador::where('id', $request->numerooperador)->first();
            $id = $operador->id;
            $Rfc = $operador->Rfc;
            $Nombre = $operador->Nombre;
            $NumeroLicencia = $operador->NumeroLicencia;
            $Calle = $operador->Calle;
            $NoExterior = $operador->NoExterior;
            $NoInterior = $operador->NoInterior;
            $Colonia = $operador->Colonia;
            $Localidad = $operador->Localidad;
            $Referencia = $operador->Referencia;
            $Municipio = $operador->Municipio;
            $Estado = $operador->Estado;
            $Pais = $operador->Pais;
            $CodigoPostal = $operador->CodigoPostal;
        }
        $data = array(
            'id' => $id,
            'Rfc' => $Rfc,
            'Nombre' => $Nombre,
            'NumeroLicencia' => $NumeroLicencia,
            'Calle' => $Calle,
            'NoExterior' => $NoExterior,
            'NoInterior' => $NoInterior,
            'Colonia' => $Colonia,
            'Localidad' => $Localidad,
            'Referencia' => $Referencia,
            'Municipio' => $Municipio,
            'Estado' => $Estado,
            'Pais' => $Pais,
            'CodigoPostal' => $CodigoPostal,
        );
        return response()->json($data);

    }

    //alta
    public function carta_porte_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\CartaPorte', $request->serie);
        //INGRESAR DATOS A TABLA COMPRAS
        $cartaporte = $folio.'-'.$request->serie;
        $CartaPorte = new CartaPorte;
        $CartaPorte->CartaPorte = $cartaporte;
        $CartaPorte->Serie= $request->serie;
        $CartaPorte->RegimenFiscal = $request->claveregimenfiscal;
        $CartaPorte->Folio= $folio;
        $CartaPorte->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $CartaPorte->Cliente=$request->numerocliente;
        $CartaPorte->Status="ALTA";
        $CartaPorte->Periodo=$this->periodohoy;
        $CartaPorte->Esquema= 'CFDI';
        $CartaPorte->Importe = Helpers::convertirvalorcorrecto($request->importe);
        $CartaPorte->Descuento = Helpers::convertirvalorcorrecto($request->descuento);
        $CartaPorte->SubTotal = Helpers::convertirvalorcorrecto($request->subtotal);
        $CartaPorte->Iva = Helpers::convertirvalorcorrecto($request->iva);
        $CartaPorte->Total = Helpers::convertirvalorcorrecto($request->total);
        $CartaPorte->Moneda = 'MXN';
        $CartaPorte->Obs=$request->observaciones;
        $CartaPorte->Usuario=Auth::user()->user;
        $CartaPorte->LugarExpedicion=$request->lugarexpedicion;
        $CartaPorte->RegimenFiscal=$request->claveregimenfiscal;
        $CartaPorte->UsoCfdi = 'S01' ;
        $CartaPorte->Hora=Carbon::parse($request->fecha)->toDateTimeString();
        $CartaPorte->TransporteInternacional = $request->transporteinternacional;
        $CartaPorte->TotalDistanciaRecorrida = $request->totaldistanciarecorrida;
        $CartaPorte->RfcRemitente=$request->rfcremitente;
        $CartaPorte->NombreRemitente=$request->nombreremitente;
        $CartaPorte->FechaSalida = Carbon::parse($request->fechasalida)->toDateTimeString();
        $CartaPorte->CalleRemitente = $request->calleremitente;
        $CartaPorte->NoExteriorRemitente = $request->numeroextremitente;
        $CartaPorte->NoInteriorRemitente = $request->numerointremitente;
        $CartaPorte->ColoniaRemitente =  $request->coloniaremitente;
        $CartaPorte->LocalidadRemitente = $request->localidadremitente;
        $CartaPorte->ReferenciaRemitente = $request->referenciaremitente;
        $CartaPorte->MunicipioRemitente = $request->municipioremitente;
        $CartaPorte->EstadoRemitente = $request->estadoremitente;
        $CartaPorte->PaisRemitente = $request->paisremitente;
        $CartaPorte->CodigoPostalRemitente = $request->cpremitente;
        $CartaPorte->RfcDestinatario=$request->rfcdestinatario;
        $CartaPorte->NombreDestinatario=$request->nombredestinatario;
        $CartaPorte->FechaLlegada = Carbon::parse($request->fechallegada)->toDateTimeString();
        $CartaPorte->CalleDestinatario = $request->calledestinatario;
        $CartaPorte->NoExteriorDestinatario = $request->numeroextdestinatario;
        $CartaPorte->NoInteriorDestinatario = $request->numerointdestinatario;
        $CartaPorte->ColoniaDestinatario = $request->coloniadestinatario;
        $CartaPorte->LocalidadDestinatario = $request->localidaddestinatario;
        $CartaPorte->ReferenciaDestinatario = $request->referenciadestinatario;
        $CartaPorte->MunicipioDestinatario = $request->municipiodestinatario;
        $CartaPorte->EstadoDestinatario = $request->estadodestinatario;
        $CartaPorte->PaisDestinatario = $request->paisdestinatario;
        $CartaPorte->CodigoPostalDestinatario = $request->cpdestinatario;
        $CartaPorte->ClaveTransporte = $request->clavetransporte;
        $CartaPorte->RfcOperador = $request->rfcoperador;
        $CartaPorte->NombreOperador = $request->nombreoperador;
        $CartaPorte->NumeroLicencia = $request->numerolicenciaoperador;
        $CartaPorte->CalleOperador = $request->calleoperador;
        $CartaPorte->NoExteriorOperador = $request->numeroextoperador;
        $CartaPorte->NoInteriorOperador = $request->numerointoperador;
        $CartaPorte->ColoniaOperador = $request->coloniaoperador;
        $CartaPorte->LocalidadOperador = $request->localidadoperador;
        $CartaPorte->ReferenciaOperador = $request->referenciaoperador;
        $CartaPorte->MunicipioOperador = $request->municipiooperador;
        $CartaPorte->EstadoOperador = $request->estadooperador;
        $CartaPorte->PaisOperador = $request->paisoperador;
        $CartaPorte->CodigoPostalOperador = $request->cpoperador;
        $CartaPorte->PermisoSCT = $request->permisosct;
        $CartaPorte->NumeroPermisoSCT = $request->numeropermisosct;
        $CartaPorte->NombreAsegurado = $request->nombreaseguradora;
        $CartaPorte->NumeroPolizaSeguro = $request->numeropolizaseguro;
        $CartaPorte->ConfiguracionVehicular = $request->claveconfigautotransporte;
        $CartaPorte->PlacaVehiculoMotor = $request->placavehiculo;
        $CartaPorte->AnoModeloVehiculoMotor = $request->anovehiculo;
        $CartaPorte->SubTipoRemolque = $request->subtiporemolque;
        $CartaPorte->PlacaRemolque = $request->placaremolque;
        $CartaPorte->TotalMercancias = $request->numerototalmercancias;
        $CartaPorte->PesoBrutoTotal = $request->pesoTotalBruto;
        $CartaPorte->carreteraFederal = (int)$request->carreteraFederal;
        $CartaPorte->save();

        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO NOTAS PROVEEDOR
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CARTA PORTE";
        $BitacoraDocumento->Movimiento = $cartaporte;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();

        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigopartida as $key => $codigos) {
            $CartaPorteDetalle = new CartaPorteDetalles;
            $CartaPorteDetalle->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $CartaPorteDetalle->CartaPorte = $cartaporte;
            $CartaPorteDetalle->Codigo = $request->codigopartida[$key];
            $CartaPorteDetalle->Descripcion = $request->descripcionpartida[$key];
            $CartaPorteDetalle->Unidad =  $request->nombreclaveunidadpartida[$key];
            $CartaPorteDetalle->Cantidad = $request->cantidadPartida[$key];
            $CartaPorteDetalle->ClaveUnidad = $request->claveunidadpartida[$key];
            $CartaPorteDetalle->ClaveProducto = $request->claveproductopartida[$key];
            $CartaPorteDetalle->MaterialPeligroso = $request->materialpeligrosopartida[$key];
            $CartaPorteDetalle->Moneda = 'MXN';
            if(isset($request->clavematerialpeligrosopartida)){
                $CartaPorteDetalle->CveMaterialPeligroso = $request->clavematerialpeligrosopartida[$key];
            }
            if(isset($request->clavetipoembalajepartida)){
                $CartaPorteDetalle->Embalaje = $request->clavetipoembalajepartida[$key];
            }
            if(isset($request->descripcionembalajepartida)){
                $CartaPorteDetalle->DescripEmbalaje = $request->descripcionembalajepartida[$key];
            }
            $CartaPorteDetalle->PesoEnKilogramos = $request->pesototal[$key];
            $CartaPorteDetalle->Item = $item;
            $CartaPorteDetalle->save();
            $item++;
        }
        if(isset($request->uuidrelacionado)){
            foreach ($request->uuidrelacionado as $key => $uuid) {
                $documento = new CartaPorteDocumentos;
                $documento->CartaPorte = $cartaporte;
                $documento->Factura = $request->factura[$key];
                $documento->UUID = $request->uuidrelacionado[$key];
                $documento->save();
            }
            CartaPorte::where('CartaPorte', $cartaporte)->update([
                "TipoRelacion" => '05'
            ]);
        }
        return response()->json($CartaPorte);
    }

    //verificar si se puede dar de bajar nota cliente
    public function carta_porte_verificar_si_continua_baja(Request $request){
        $errores = '';
        $CartaPorte = CartaPorte::where('CartaPorte', $request->cartadesactivar)->first();
        $resultadofechas = Helpers::compararanoymesfechas($CartaPorte->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'errores' => $errores,
            'Status' => $CartaPorte->Status
        );
        return response()->json($data);
    }

    //bajas
    public function carta_porte_alta_o_baja(Request $request){
        $CartaPorte = CartaPorte::where('CartaPorte', $request->cartadesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        CartaPorte::where('CartaPorte', $request->cartadesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'PesoBrutoTotal' => '0.000000',
                    'TotalDistanciaRecorrida' => '0.000000'
                ]);
        $detalles = CartaPorteDetalles::where('CartaPorte', $request->cartadesactivar)->get();
        //notas proveedor detalles
        foreach($detalles as $detalle){
            //colocar en ceros cantidades nota proveedor detalles
            NotaClienteDetalle::where('Nota', $request->notadesactivar)
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
                                'Total' => '0.000000'
                            ]);
        }
        //carta porte documentos

        foreach($CartaPorte->documentos as $detalledocumento){
            $detalledocumento->forceDelete();
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CARTAPORTE";
        $BitacoraDocumento->Movimiento = $request->cartadesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $CartaPorte->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($CartaPorte);
    }

    //obtener nota cliente
    public function carta_porte_obtener_carta_porte(Request $request){
        $cartaporte = CartaPorte::where('CartaPorte', $request->cartaporte)->first();
        $cliente = Cliente::where('Numero', $cartaporte->Cliente)->first();
        $regimenfiscal = c_RegimenFiscal::where('Clave', $cartaporte->RegimenFiscal)->first();
        //$tiporelacion = c_TipoRelacion::where('Clave', $notacliente->TipoRelacion)->first();
        //$formapago = FormaPago::where('Clave', $notacliente->FormaPago)->first();
        //$metodopago = MetodoPago::where('Clave', $notacliente->MetodoPago)->first();
        //$usocfdi = UsoCFDI::where('Clave', $notacliente->UsoCfdi)->first();
        $residenciafiscal = Pais::where('Clave', $cartaporte->ResidenciaFiscal)->first();
        $vehiculoEmpresa = Vehiculo::where('Placa',$cartaporte->PlacaVehiculoMotor)->first();
        $configuracionVehicular = c_ConfiguracionAutoTransporte::where('Clave', $cartaporte->ConfiguracionVehicular)->first();
        $operador = Operador::where('Rfc', $cartaporte->RfcOperador)->first();
        $clavetransporte = c_CveTransporte::where('Clave',$cartaporte->ClaveTransporte)->first();
        $MaterialPeligrosoHTML = '';
        $Embalaje = '';
        //detalles
        $numerodetallescarta = $cartaporte->detalles->count();
        $pesoUnitario =  0;
        $filasdetallescartaporte = '';
        $filasdocumentosrelacionados = '';
        if($numerodetallescarta > 0){
            $contadorproductos = 0;
            $contadorfilas = 0;

            $tipo="modificacion";
            foreach($cartaporte->detalles as $cpd){
                    $pesoUnitario = ($cpd->PesoEnKilogramos / $cpd->Cantidad);

                    //Inicia Bloque Material Peligro
                    if($cpd->MaterialPeligroso == "0"){
                        $MaterialPeligrosoHTML = '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Material Peligroso"><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm clavematerialpeligrosopartida" name="clavematerialpeligrosopartida[]"  value="" disabled readonly'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="hidden" class="form-control divorinputmodmd descripcionclavepeligrosopartida" name="descripcionclavepeligrosopartida[]" disabled  value="">'.
                            '</div>'.
                        '</div>';
                        $Embalaje = '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Tipos Embalaje"><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm clavetipoembalajepartida" name="clavetipoembalajepartida[]"  value="" disabled readonly'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="hidden" class="form-control divorinputmodmd descripcionclavepeligrosopartida" name="descripcionclavepeligrosopartida[]" disabled  value="">'.
                            '</div>'.
                        '</div>';
                    }else if($cpd->MaterialPeligroso == "1"){
                        $MaterialPeligrosoHTML = '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Material Peligroso" onclick="listarclavespeligrosos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm clavematerialpeligrosopartida" name="clavematerialpeligrosopartida[]"  value="'.$cpd->CvaMaterialPeligroso.'" required readonly'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="hidden" class="form-control divorinputmodmd descripcionclavepeligrosopartida" name="descripcionclavepeligrosopartida[]" required  value="">'.
                            '</div>'.
                        '</div>';
                        $Embalaje = '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Tipos Embalaje" onclick="listarclavespeligrosos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm clavetipoembalajepartida" name="clavetipoembalajepartida[]"  value="'.$cpd->Embalaje.'" required readonly'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="hidden" class="form-control divorinputmodmd descripcionembalajepartida" name="descripcionembalajepartida[]" required  value="'.$cpd->DescripEmbalaje.'" readonly>'.
                            '</div>'.
                        '</div>';
                    }else{
                        $MaterialPeligrosoHTML = '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavespeligrosos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm clavematerialpeligrosopartida" name="clavematerialpeligrosopartida[]"  value="'.$cpd->CveMaterialPeligroso.'" readonly'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="hidden" class="form-control divorinputmodmd descripcionclavepeligrosopartida" name="descripcionclavepeligrosopartida[]"  value="">'.
                            '</div>'.
                        '</div>';
                        $Embalaje = '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Tipos Embalaje" onclick="listartiposembalaje('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm clavetipoembalajepartida" name="clavetipoembalajepartida[]"  value="'.$cpd->Embalaje.'" readonly'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="hidden" class="form-control divorinputmodmd descripcionembalajepartida" name="descripcionembalajepartida[]"  value="'.$cpd->DescripEmbalaje.'" readonly>'.
                            '</div>'.
                        '</div>';
                    }
                    //Termina Bloque Material Peligroso
                    $claveproductopartida = ClaveProdServ::where('Clave', $cpd->ClaveProducto)->first();
                    $claveunidadpartida = ClaveUnidad::where('Clave', $cpd->ClaveUnidad)->first();
                    $claveproducto = $claveproductopartida ? $claveproductopartida->Clave : '';
                    $nombreclaveproducto = $claveproductopartida ? $claveproductopartida->Nombre : '';
                    $claveunidad = $claveunidadpartida ? $claveunidadpartida->Clave : '';
                    $nombreclaveunidad = $claveunidadpartida ? $claveunidadpartida->Nombre : '';
                    if($cpd->Codigo == 'DPPP'){
                        $filasdetallescartaporte .=
                        '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfila" onclick="eliminarfila('.$contadorfilas.')" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$cpd->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$cpd->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$cpd->Codigo.'</b></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($cpd->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$cpd->Unidad.'" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadPartida" name="cantidadPartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->Cantidad).'" data-parsley-min="0.1"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');">'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->Precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->Ieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencioniva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->IvaRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionisr).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->IsrRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->IepsRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]"  value="0" readonly></td>'.
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
                        $filasdetallescartaporte= $filasdetallescartaporte.
                        '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                            '<td class="tdmod"><input type="hidden" class="btn btn-danger btn-xs btneliminarfila"><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$cpd->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]" value="'.$cpd->Item.'"></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$cpd->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$cpd->Codigo.'</b></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($cpd->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadPartida" name="cantidadPartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->Cantidad).'" data-parsley-min="0.000001" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularPesoPartida('.$contadorfilas.')"></td>'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($cpd->Cantidad).'" data-parsley-min="0.000001" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');revisarcantidadnotavscantidadfactura('.$contadorfilas.');">'.
                                '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($cpd->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);">'.
                                '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                                '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'.
                                '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm pesopartida" name="pesobrutopartida[]" value="'.Helpers::convertirvalorcorrecto($pesoUnitario).'" data-parsley-min="0.0000001" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularPesoPartida('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm pesototalpartida" name="pesototal[]" value="'.Helpers::convertirvalorcorrecto($cpd->PesoEnKilogramos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod">'.
                                '<input type="hidden" name="materialpeligrosopartida[]" value="'.$cpd->MaterialPeligroso.'"></input>'.
                                $MaterialPeligrosoHTML
                            .'</td>'.
                            '<td class="tdmod">'.
                                $Embalaje
                            .'</td>'.
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
        $filasdocumentosrelacionados = '';
        if ($cartaporte->documentos->count() > 0) {
            foreach ($cartaporte->documentos as $documento) {
                $filasdocumentosrelacionados .=
                '<tr class="filasuuid" id="filauuid0">'.
                    '<td class="tdmod">'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" class="form-control divorinputmodsm uuidrelacionado" name="uuidrelacionado[]" value="'.$documento->UUID.'" readonly required>'.$documento->UUID.
                        '<input type="text" style="display:none"; class="form-control divorinputmodsm" name="factura[]" value="'.$documento->Factura.'" readonly>'.
                    '</td>'.
                '</tr>';
            //     $filasdocumentosrelacionados.= '<tr class="filasuuid" id="filauuid0">'.
            //     '<td class="tdmod">'.
            //         '<div class="btn btn-danger btn-xs btneliminaruuid">X</div><input type="hidden" class="form-control uuidagregadoen" name="uuidagregadoen[]" value="NA" readonly>'.
            //     '</td>'.
            //     '<td class="tdmod">'.
            //         '<input type="hidden" class="form-control divorinputmodsm uuidrelacionado" name="uuidrelacionado[]" value="'.$documento->UUID.'" readonly required>'.$documento->UUID.
            //         '<input type="text" style="display:none"; class="form-control divorinputmodsm" name="factura[]" value="'.$documento->Factura.'" readonly>'.
            //     '</td>'.
            // '</tr>';
            }
        }
        if(Auth::user()->role_id == 1){
            if($cartaporte->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($cartaporte->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($cartaporte->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }
        $data = array(
            "cartaporte" => $cartaporte,
            "filasdetallescartaporte" => $filasdetallescartaporte,
            "filasdocumentosrelacionados" => $filasdocumentosrelacionados,
            "numerodetallescarta" => $numerodetallescarta,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "tipodetalles" => $tipodetalles,
            "cliente" => $cliente,
            "regimenfiscal" => $regimenfiscal,
            "vehiculoEmpresa" => $vehiculoEmpresa,
            "configuracionVehicular" => $configuracionVehicular,
            'clavetransporte' => $clavetransporte,
            "residenciafiscal" => $residenciafiscal,
            "fecha" => Carbon::parse($cartaporte->Fecha)->toDateTimeString(),
            "Operador" => $operador,
            "modificacionpermitida" => $modificacionpermitida,
        );
        return response()->json($data);
    }

    //cambios
    public function carta_porte_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );

            //dd($request->all());
            $cartaporte = $request->folio.'-'.$request->serie;
            $CartaPorte = CartaPorte::where('CartaPorte', $cartaporte)->first();
            $MaterialPeligroso = null;
            $Embalaje = null;
            $descripcionEmbalaje = null;
            //modificar nota
            CartaPorte::where('CartaPorte', $cartaporte)
            ->update([
                'Cliente' =>$request->numerocliente,
                'Periodo' =>$this->periodohoy,
                'Obs' =>$request->observaciones,
                'Usuario' =>Auth::user()->user,
                'LugarExpedicion' =>$request->lugarexpedicion,
                'RegimenFiscal' =>$request->claveregimenfiscal,
                'Hora' =>Carbon::parse($request->fecha)->toDateTimeString(),
                'TransporteInternacional ' => $request->transporteinternacional,
                'TotalDistanciaRecorrida ' => $request->totaldistanciarecorrida,
                'RfcRemitente' =>$request->rfcremitente,
                'NombreRemitente' =>$request->nombreremitente,
                'FechaSalida ' => Carbon::parse($request->fechasalida)->toDateTimeString(),
                'CalleRemitente ' => $request->calleremitente,
                'NoExteriorRemitente ' => $request->numeroextremitente,
                'NoInteriorRemitente ' => $request->numerointremitente,
                'ColoniaRemitente ' =>  $request->coloniaremitente,
                'LocalidadRemitente ' => $request->localidadremitente,
                'ReferenciaRemitente ' => $request->referenciaremitente,
                'MunicipioRemitente ' => $request->municipioremitente,
                'EstadoRemitente ' => $request->estadoremitente,
                'PaisRemitente ' => $request->paisremitente,
                'CodigoPostalRemitente ' => $request->cpremitente,
                'RfcDestinatario' =>$request->rfcdestinatario,
                'NombreDestinatario' =>$request->nombredestinatario,
                'FechaLlegada ' => Carbon::parse($request->fechallegada)->toDateTimeString(),
                'CalleDestinatario ' => $request->calledestinatario,
                'NoExteriorDestinatario ' => $request->numeroextdestinatario,
                'NoInteriorDestinatario ' => $request->numerointdestinatario,
                'ColoniaDestinatario ' => $request->coloniadestinatario,
                'LocalidadDestinatario ' => $request->localidaddestinatario,
                'ReferenciaDestinatario ' => $request->referenciadestinatario,
                'MunicipioDestinatario ' => $request->municipiodestinatario,
                'EstadoDestinatario ' => $request->estadodestinatario,
                'PaisDestinatario ' => $request->paisdestinatario,
                'CodigoPostalDestinatario ' => $request->cpdestinatario,
                'ClaveTransporte ' => $request->clavetransporte,
                'RfcOperador ' => $request->rfcoperador,
                'NombreOperador ' => $request->nombreoperador,
                'NumeroLicencia ' => $request->numerolicenciaoperador,
                'CalleOperador ' => $request->calleoperador,
                'NoExteriorOperador ' => $request->numeroextoperador,
                'NoInteriorOperador ' => $request->numerointoperador,
                'ColoniaOperador ' => $request->coloniaoperador,
                'LocalidadOperador ' => $request->localidadoperador,
                'ReferenciaOperador ' => $request->referenciaoperador,
                'MunicipioOperador ' => $request->municipiooperador,
                'EstadoOperador ' => $request->estadooperador,
                'PaisOperador ' => $request->paisoperador,
                'CodigoPostalOperador ' => $request->cpoperador,
                'PermisoSCT ' => $request->permisosct,
                'NumeroPermisoSCT ' => $request->numeropermisosct,
                'NombreAsegurado ' => $request->nombreaseguradora,
                'NumeroPolizaSeguro ' => $request->numeropolizaseguro,
                'ConfiguracionVehicular ' => $request->claveconfigautotransporte,
                'PlacaVehiculoMotor ' => $request->placavehiculo,
                'AnoModeloVehiculoMotor ' => $request->anovehiculo,
                'SubTipoRemolque ' => $request->subtiporemolque,
                'PlacaRemolque ' => $request->placaremolque,
                'TotalMercancias ' => $request->numerototalmercancias,
                'PesoBrutoTotal ' => $request->pesoTotalBruto
            ]);
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
            $BitacoraDocumento =  new BitacoraDocumento;
            $BitacoraDocumento->Documento = "CARTA PORTE";
            $BitacoraDocumento->Movimiento = $cartaporte;
            $BitacoraDocumento->Aplicacion = "CAMBIO";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = 'ALTA';
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $this->periodohoy;
            $BitacoraDocumento->save();
            //detalles
            foreach ($request->codigopartida as $key => $codigopartida){
                //if la partida se agrego en la modificacion se realiza un insert

                if(isset($request->clavematerialpeligrosopartida)){
                    $MaterialPeligroso = $request->clavematerialpeligrosopartida[$key];
                }
                if(isset($request->clavetipoembalajepartida)){
                    $Embalaje = $request->clavetipoembalajepartida[$key];
                }
                if(isset($request->descripcionembalajepartida)){
                    $descripcionEmbalaje = $request->descripcionembalajepartida[$key];
                }

                if($request->agregadoen [$key] == 'modificacion'){
                    $contaritems = CartaPorteDetalles::select('Item')->where('CartaPorte', $cartaporte)->count();
                    if($contaritems > 0){
                        $item = CartaPorteDetalles::select('Item')->where('CartaPorte', $cartaporte)->orderBy('Item', 'DESC')->take(1)->get();
                        $ultimoitem = $item[0]->Item+1;
                    }else{
                        $ultimoitem = 1;
                    }
                    $CartaPorteDetalle = new CartaPorteDetalles;
                    $CartaPorteDetalle->Fecha = Helpers::fecha_exacta_accion_datetimestring();
                    $CartaPorteDetalle->CartaPorte = $cartaporte;
                    $CartaPorteDetalle->Codigo = $request->codigopartida[$key];
                    $CartaPorteDetalle->Descripcion = $request->descripcionpartida[$key];
                    $CartaPorteDetalle->Unidad =  $request->nombreclaveunidadpartida[$key];
                    $CartaPorteDetalle->Cantidad = $request->cantidadPartida[$key];
                    $CartaPorteDetalle->ClaveUnidad = $request->claveunidadpartida[$key];
                    $CartaPorteDetalle->ClaveProducto = $request->claveproductopartida[$key];
                    $CartaPorteDetalle->MaterialPeligroso = $request->materialpeligrosopartida[$key];
                    $CartaPorteDetalle->CveMaterialPeligroso = $MaterialPeligroso;
                    $CartaPorteDetalle->Embalaje = $Embalaje;
                    $CartaPorteDetalle->DescripEmbalaje = $descripcionEmbalaje;
                    $CartaPorteDetalle->PesoEnKilogramos = $request->pesototal[$key];
                    $CartaPorteDetalle->Item = $ultimoitem;
                    $CartaPorteDetalle->save();
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar detalle
                    CartaPorteDetalles::where('CartaPorte', $cartaporte)
                    ->where('Item', $request->partidapartida[$key])
                    ->update([
                        'Fecha' => Helpers::fecha_exacta_accion_datetimestring(),
                        'CartaPorte' => $cartaporte,
                        'Codigo' => $request->codigopartida[$key],
                        'Descripcion' => $request->descripcionpartida[$key],
                        'Unidad' =>  $request->nombreclaveunidadpartida[$key],
                        'Cantidad' => $request->cantidadPartida[$key],
                        'ClaveUnidad' => $request->claveunidadpartida[$key],
                        'ClaveProducto' => $request->claveproductopartida[$key],
                        'MaterialPeligroso' => $request->materialpeligrosopartida[$key],
                        'CveMaterialPeligroso' => $MaterialPeligroso,
                        'Embalaje' => $Embalaje,
                        'DescripEmbalaje' => $descripcionEmbalaje,
                        'PesoEnKilogramos' => $request->pesototal[$key],
                    ]);
                }
            }
            return response()->json($CartaPorte);
    }

    //generacion de formato en PDF
    public function notas_credito_clientes_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $notascreditocliente = NotaCliente::whereIn('Nota', $request->arraypdf)->orderBy('Folio', 'ASC')->take(250)->get();
        }else{
            //$contrarecibos = ContraRecibo::where('Fecha', $request->anopdf)->get();
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            if ($request->has("seriesdisponiblesdocumento")){
                $notascreditocliente = NotaCliente::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->whereIn('Serie', $request->seriesdisponiblesdocumento)->orderBy('Folio', 'ASC')->take(250)->get();
            }else{
                $notascreditocliente = NotaCliente::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(250)->get();
            }
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        foreach ($notascreditocliente as $ncc){
            $data=array();
            $notascreditoclientedetalle = NotaClienteDetalle::where('Nota', $ncc->Nota)->get();
            $datadetalle=array();
            foreach($notascreditoclientedetalle as $nccd){
                $claveproducto = ClaveProdServ::where('Clave', $nccd->ClaveProducto)->first();
                $claveunidad = ClaveUnidad::where('Clave', $nccd->ClaveUnidad)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($nccd->Cantidad),
                    "codigodetalle"=>$nccd->Codigo,
                    "descripciondetalle"=>$nccd->Descripcion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($nccd->Precio),
                    "porcentajedescuentodetalle" => Helpers::convertirvalorcorrecto($nccd->Dcto),
                    "pesosdescuentodetalle" => Helpers::convertirvalorcorrecto($nccd->Descuento),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($nccd->SubTotal),
                    "impuestodetalle" => Helpers::convertirvalorcorrecto($nccd->Impuesto),
                    "ivadetalle" => Helpers::convertirvalorcorrecto($nccd->Iva),
                    "claveproducto" => $claveproducto,
                    "claveunidad" => $claveunidad
                );
            }
            $cliente = Cliente::where('Numero', $ncc->Cliente)->first();
            $formapago = FormaPago::where('Clave', $ncc->FormaPago)->first();
            $metodopago = MetodoPago::where('Clave', $ncc->MetodoPago)->first();
            $usocfdi = UsoCFDI::where('Clave', $ncc->UsoCfdi)->first();
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($ncc->Total, 2, 'M.N.');
            $notaclientedocumento = NotaClienteDocumento::where('Nota', $ncc->Nota)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $ncc->RegimenFiscal)->first();
            $data[]=array(
                "notacreditocliente"=>$ncc,
                "notaclientedocumento"=>$notaclientedocumento,
                "comprobante" => $comprobante,
                "comprobantetimbrado" => $comprobantetimbrado,
                "regimenfiscal"=> $regimenfiscal,
                "subtotalnotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->SubTotal),
                "ivanotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->Iva),
                "totalnotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->Total),
                "cliente" => $cliente,
                "formapago" => $formapago,
                "metodopago" => $metodopago,
                "usocfdi" => $usocfdi,
                "datadetalle" => $datadetalle,
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($ncc->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.notascreditoclientes.formato_pdf_notascreditoclientes', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$ncc->Nota.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($notascreditocliente as $notcc){
            $ArchivoPDF = "PDF".$notcc->Nota.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
        }
        $pdfMerger->merge(); //unirlos
        $pdfMerger->save("NotasCreditoCliente.pdf", "browser");//mostrarlos en el navegador
    }

    //generacion de formato en PDF
    public function carta_porte_generar_pdfs_indiv($documento){

        $cartaporte = CartaPorte::where('CartaPorte', $documento)->with(['documentos'])->first();
        $cliente = Cliente::where('Rfc',$cartaporte->RfcDestinatario)->first();
        $regimenfiscal = c_RegimenFiscal::where('Clave',$cliente->RegimenFiscal)->first();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $usocfdi = UsoCFDI::where('Clave',$cartaporte->UsoCfdi)->first();
        $regimenEmisor = c_RegimenFiscal::where('Clave',$cartaporte->RegimenFiscal)->first();
        $numerodecimalesdocumento = $this->numerodecimalesendocumentos;
        $estadoCliente = Estado::where('Clave',$cartaporte->EstadoDestinatario)->first();
        $tipoPermiso = \DB::table('c_TipoPermiso')->where('Clave',$cartaporte->PermisoSCT)->first();
        $configuracionVehicular = c_ConfiguracionAutoTransporte::where('Clave',$cartaporte->ConfiguracionVehicular)->first();
        $data=array();
        $datadetalle =collect([]);
        $MaterialPeligrosoMaterialPeligroso = '';
        $dataDomicilioEmisor = array(
            'pais'=> $this->paisempresa,
            'cp' => $this->cpempresa,
            'estado' =>$this->estadoempresa,
            'municipio'=>$this->municipioempresa,
            'colonia'=>$this->coloniaempresa,
            'calle'=>$this->calleempresa,
            'exterior'=>$this->noexteriorempresa,
            'interior'=>$this->nointeriorempresa,
            'referencia'=>$this->referenciaempresa
        );
        $dataDestino = array(
            'nombre' => $cliente->Nombre,
            'pais'=> 'México',
            'cp' => $cliente->CodigoPostal,
            'estado' => $estadoCliente->Nombre,
            'municipio'=>$cliente->Municipio,
            'colonia'=>$cliente->Colonia,
            'calle'=>$cliente->Calle,
            'exterior'=>$cliente->noExterior,
            'interior'=>$cliente->noInterior,
            'referencia'=>$cliente->Referencia,
            'localidad'=>$cliente->Localidad
        );
        $datosAutoTransporte = array(
            'tipoPermiso' => (isset($tipoPermiso) ? $tipoPermiso->Descripcion : 'NA'),
            'numeroPermiso' => $cartaporte->NumeroPermisoSCT,
            'confVehiculo' => (isset($$configuracionVehicular) ? $tipoPermiso->Descripcion : 'NA'),
        );
        $detalles = CartaPorteDetalles::where('CartaPorte', $cartaporte->CartaPorte)->get();
        foreach($detalles as $detalle){
            $claveproducto = ClaveProdServ::where('Clave', $detalle->ClaveProducto)->first();
            $claveunidad = ClaveUnidad::where('Clave', $detalle->ClaveUnidad)->first();
            $MaterialPeligroso = 'No';
            switch ($detalle->MaterialPeligroso) {
                case '0,1':
                    if (!isset($detalle->CveMaterialPeligroso)) {
                        $MaterialPeligrosoMaterialPeligroso="No";
                        $claveMaterial = '';
                        $embalaje = '';
                    }else{
                        $MaterialPeligrosoMaterialPeligroso="Sí" ;
                        $claveMaterial = $detalle->CveMaterialPeligroso;
                        $embalaje = $detalle->Embalaje;
                    }
                    break;
                case '0':
                    $MaterialPeligroso = 'No';
                    $claveMaterial = '';
                    $embalaje = '';
                    break;
                default:
                    $MaterialPeligrosorialPeligroso = "Sí";
                    $claveMaterial = $detalle->CveMaterialPeligroso;
                    $embalaje = $detalle->Embalaje;
                    break;
            }
            $datadetalle[]=collect([
                "cantidaddetalle"=> Helpers::convertirvalorcorrecto($detalle->Cantidad),
                "codigodetalle"=>$detalle->Codigo,
                "descripciondetalle"=>$detalle->Descripcion,
                "pesounitario" => number_format(($detalle->PesoEnKilogramos/$detalle->Cantidad),$this->numerodecimales,'.',','),
                "pesoBruto" => number_format($detalle->PesoEnKilogramos,$this->numerodecimales,'.',','),
                "precio" => Helpers::convertirvalorcorrecto($detalle->Costo),
                "importe" => Helpers::convertirvalorcorrecto($detalle->Costo),
                "descuento" => Helpers::convertirvalorcorrecto(0),
                "descuentopesos" => Helpers::convertirvalorcorrecto(0),
                "subtotal" => Helpers::convertirvalorcorrecto(0),
                "claveproducto" => $claveproducto,
                "claveunidad" => $claveunidad,
                'materialPeligroso' => $MaterialPeligroso,
                'claveMaterial' => $claveMaterial,
                'embalaje' => $embalaje
                ]);
        }
        $comprobantetimbrado = Comprobante::where('Comprobante', 'CartaPorte')->where('Folio', '' . $cartaporte->Folio . '')->where('Serie', '' . $cartaporte->Serie . '')->count();
        $comprobante = Comprobante::where('Comprobante', 'CartaPorte')->where('Folio', '' . $cartaporte->Folio . '')->where('Serie', '' . $cartaporte->Serie . '')->first();

        ini_set('max_execution_time', 300000); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cartasporte.formato_traslado', compact('cliente','cartaporte','usocfdi','regimenfiscal',
        'datadetalle','comprobantetimbrado','comprobante','regimenEmisor', 'numerodecimalesdocumento','dataDomicilioEmisor'
        ,'dataDestino','datosAutoTransporte'))
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
    public function notas_credito_clientes_obtener_datos_envio_email(Request $request){
        $notacliente = NotaCliente::where('Nota', $request->documento)->first();
        $cliente = Cliente::where('Numero',$notacliente->Cliente)->first();
        $data = array(
            'notacliente' => $notacliente,
            'cliente' => $cliente,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $cliente->Email1,
            'email2cc' => $cliente->Email2,
            'email3cc' => $cliente->Email3
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function notas_credito_clientes_enviar_pdfs_email(Request $request){
        $notascreditocliente = NotaCliente::where('Nota', $request->emaildocumento)->get();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($notascreditocliente as $ncc){
            $notascreditoclientedetalle = NotaClienteDetalle::where('Nota', $ncc->Nota)->get();
            $datadetalle=array();
            foreach($notascreditoclientedetalle as $nccd){
                $claveproducto = ClaveProdServ::where('Clave', $nccd->ClaveProducto)->first();
                $claveunidad = ClaveUnidad::where('Clave', $nccd->ClaveUnidad)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($nccd->Cantidad),
                    "codigodetalle"=>$nccd->Codigo,
                    "descripciondetalle"=>$nccd->Descripcion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($nccd->Precio),
                    "porcentajedescuentodetalle" => Helpers::convertirvalorcorrecto($nccd->Dcto),
                    "pesosdescuentodetalle" => Helpers::convertirvalorcorrecto($nccd->Descuento),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($nccd->SubTotal),
                    "impuestodetalle" => Helpers::convertirvalorcorrecto($nccd->Impuesto),
                    "ivadetalle" => Helpers::convertirvalorcorrecto($nccd->Iva),
                    "claveproducto" => $claveproducto,
                    "claveunidad" => $claveunidad
                );
            }
            $cliente = Cliente::where('Numero', $ncc->Cliente)->first();
            $formapago = FormaPago::where('Clave', $ncc->FormaPago)->first();
            $metodopago = MetodoPago::where('Clave', $ncc->MetodoPago)->first();
            $usocfdi = UsoCFDI::where('Clave', $ncc->UsoCfdi)->first();
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($ncc->Total, 2, 'M.N.');
            $notaclientedocumento = NotaClienteDocumento::where('Nota', $ncc->Nota)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $ncc->RegimenFiscal)->first();
            $data[]=array(
                "notacreditocliente"=>$ncc,
                "notaclientedocumento"=>$notaclientedocumento,
                "comprobante" => $comprobante,
                "comprobantetimbrado" => $comprobantetimbrado,
                "regimenfiscal"=> $regimenfiscal,
                "subtotalnotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->SubTotal),
                "ivanotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->Iva),
                "totalnotacreditocliente"=>Helpers::convertirvalorcorrecto($ncc->Total),
                "cliente" => $cliente,
                "formapago" => $formapago,
                "metodopago" => $metodopago,
                "usocfdi" => $usocfdi,
                "datadetalle" => $datadetalle,
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($ncc->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.notascreditoclientes.formato_pdf_notascreditoclientes', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        //obtener XML
        if($request->incluir_xml == 1){
            $nota = NotaCliente::where('Nota', $request->emaildocumento)->first();
            $comprobante = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $nota->Folio . '')->where('Serie', '' . $nota->Serie . '')->first();
            $descargar_xml = $this->facturapi->Invoices->download_xml($comprobante->IdFacturapi); // stream containing the XML file or
            $nombre_xml = "NotaCreditoClienteNo".$nota->Nota.'##'.$nota->UUID.'.xml';
            Storage::disk('local')->put($nombre_xml, $descargar_xml);
            $url_xml = Storage::disk('local')->getAdapter()->applyPathPrefix($nombre_xml);
        }else{
            $url_xml = "";
        }
        try{
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
            if($this->correodefault1enviodocumentos != ""){
                array_push($arraycc, $this->correodefault1enviodocumentos);
            }
            if($this->correodefault2enviodocumentos != ""){
                array_push($arraycc, $this->correodefault2enviodocumentos);
            }
            //subir archivo arjunto 1 en public/archivos_adjuntos para poder adjuntarlo en el correo
            if($request->archivoadjunto != null) {
                //archivos adjuntos
                $mover_a_carpeta="archivos_adjuntos";
                $archivoadjunto = $request->archivoadjunto;
                $nombre_original_archivo_adjunto = $archivoadjunto->getClientOriginalName();
                $nuevo_nombre_archivo_adjunto = time().$nombre_original_archivo_adjunto;
                //guardar archivos en public/archivos_adjuntos
                $archivoadjunto->move($mover_a_carpeta, $nuevo_nombre_archivo_adjunto);
                $urlarchivoadjunto = public_path('archivos_adjuntos\\'.$nuevo_nombre_archivo_adjunto);
            }
            //subir archivo arjunto 2 en public/archivos_adjuntos para poder adjuntarlo en el correo
            if($request->archivoadjunto2 != null) {
                //archivos adjuntos
                $mover_a_carpeta="archivos_adjuntos";
                $archivoadjunto2 = $request->archivoadjunto2;
                $nombre_original_archivo_adjunto2 = $archivoadjunto2->getClientOriginalName();
                $nuevo_nombre_archivo_adjunto2 = time().$nombre_original_archivo_adjunto2;
                //guardar archivos en public/archivos_adjuntos
                $archivoadjunto2->move($mover_a_carpeta, $nuevo_nombre_archivo_adjunto2);
                $urlarchivoadjunto2 = public_path('archivos_adjuntos\\'.$nuevo_nombre_archivo_adjunto2);
            }
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailmensaje;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            if (file_exists($url_xml) != false) {
                if($request->archivoadjunto != null && $request->archivoadjunto2 != null) {
                    Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $urlarchivoadjunto2, $correos, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "NotaCreditoClienteNo".$emaildocumento.".pdf")
                                ->attach($url_xml)
                                ->attach($urlarchivoadjunto)
                                ->attach($urlarchivoadjunto2);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto)) {
                        unlink($urlarchivoadjunto);
                    }
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto2)) {
                        unlink($urlarchivoadjunto2);
                    }
                }else if($request->archivoadjunto != null && $request->archivoadjunto2 == null){
                    Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $correos, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "NotaCreditoClienteNo".$emaildocumento.".pdf")
                                ->attach($url_xml)
                                ->attach($urlarchivoadjunto);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto)) {
                        unlink($urlarchivoadjunto);
                    }
                }else if($request->archivoadjunto == null && $request->archivoadjunto2 != null){
                    Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $correos, $urlarchivoadjunto2, $arraycc, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "NotaCreditoClienteNo".$emaildocumento.".pdf")
                                ->attach($url_xml)
                                ->attach($urlarchivoadjunto2);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto2)) {
                        unlink($urlarchivoadjunto2);
                    }
                }else{
                    Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $correos, $arraycc, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "NotaCreditoClienteNo".$emaildocumento.".pdf")
                                ->attach($url_xml);
                    });
                }
                //eliminar xml de storage/xml_cargados
                unlink($url_xml);
            }else{
                if($request->archivoadjunto != null && $request->archivoadjunto2 != null) {
                    Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $urlarchivoadjunto2, $correos, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "NotaCreditoClienteNo".$emaildocumento.".pdf")
                                ->attach($urlarchivoadjunto)
                                ->attach($urlarchivoadjunto2);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto)) {
                        unlink($urlarchivoadjunto);
                    }
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto2)) {
                        unlink($urlarchivoadjunto2);
                    }
                }else if($request->archivoadjunto != null && $request->archivoadjunto2 == null){
                    Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $correos, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "NotaCreditoClienteNo".$emaildocumento.".pdf")
                                ->attach($urlarchivoadjunto);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto)) {
                        unlink($urlarchivoadjunto);
                    }
                }else if($request->archivoadjunto == null && $request->archivoadjunto2 != null){
                    Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $correos, $urlarchivoadjunto2, $arraycc, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "NotaCreditoClienteNo".$emaildocumento.".pdf")
                                ->attach($urlarchivoadjunto2);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto2)) {
                        unlink($urlarchivoadjunto2);
                    }
                }else{
                    Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $correos, $arraycc, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "NotaCreditoClienteNo".$emaildocumento.".pdf");
                    });
                }
            }
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
    public function notas_credito_clientes_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new NotasCreditoClientesExport($this->campos_consulta,$request->periodo), "notascreditoclientes-".$request->periodo.".xlsx");
    }
    //configuracion tabla
    public function carta_porte_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        Configuracion_Tabla::where('tabla', 'CartaPorte')->where('IdUsuario',Auth::user()->id)
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
        return redirect()->route('carta_porte');
    }
    //verificar si se puede timbrar
    public function carta_porte_verificar_si_continua_timbrado(Request $request){
        $carta = CartaPorte::where('CartaPorte', $request->carta)->first();
        $data = array(
            'Status' => $carta->Status,
            'UUID' => $carta->UUID
        );
        return response()->json($data);
    }
    //timbrar carta
    public function carta_porte_timbrar_carta(Request $request){

        $carta = CartaPorte::where('CartaPorte', $request->cartatimbrado)->first();
        $cliente = Cliente::where('Numero', $carta->Cliente)->first();
        $arraydet = array();
        $salidaarray = explode(" ",date_create($carta->FechaSalida)->format('Y-m-d H:i:s'));
        $llegadaarray = explode(" ",date_create($carta->FechaLlegada)->format('Y-m-d H:i:s'));
        $bienesTransportados = '';
        $municipioRe = Municipio::where('Nombre','like','%'.$carta->MunicipioRemitente.'%')->select('Clave')->first();
        $municipioDe = Municipio::where('Nombre','like','%'.$carta->MunicipioDestinatario.'%')->select('Clave')->first();
        $vehiculo = Vehiculo::where('Placa',$carta->PlacaVehiculoMotor)->first();
        $confVehicular = c_ConfiguracionAutoTransporte::where('Clave',$carta->ConfiguracionVehicular)->first();
        foreach($carta->detalles as $detalle){
            array_push($arraydet, array(
                    "quantity" => Helpers::convertirvalorcorrecto($detalle->Cantidad),
                    "product" =>
                        array(
                            "description" => $detalle->Descripcion,
                            "product_key" => $detalle->ClaveProducto,
                            "unit_key" => $detalle->ClaveUnidad,
                            "sku" => $detalle->Codigo
                    )
                )
            );

            switch ($detalle->MaterialPeligroso) {
                case '0,1':
                    if (!isset($detalle->CveMaterialPeligroso)) {
                        $bienesTransportados .=
                        '<cartaporte20:Mercancia BienesTransp="'.$detalle->ClaveProducto.'" Descripcion="'.$detalle->Descripcion.'" Cantidad="'.$detalle->Cantidad.'" ClaveUnidad="'.$detalle->ClaveUnidad.'" Unidad="'.$detalle->Unidad.'" PesoEnKg="'.$detalle->PesoEnKilogramos.'" MaterialPeligroso="No" />';
                    }else{
                        $bienesTransportados .=
                        '<cartaporte20:Mercancia BienesTransp="'.$detalle->ClaveProducto.'" Descripcion="'.$detalle->Descripcion.'" Cantidad="'.$detalle->Cantidad.'" ClaveUnidad="'.$detalle->ClaveUnidad.'" Unidad="'.$detalle->Unidad.'" PesoEnKg="'.$detalle->PesoEnKilogramos.'" MaterialPeligroso="Sí" CveMaterialPeligroso="'.$detalle->CveMaterialPeligroso.'" Embalaje="'.$detalle->Embalaje.'" DescripEmbalaje="'.$detalle->DescripEmbalaje.'" />';
                    }
                    break;
                case '0':
                    $bienesTransportados .=
                    '<cartaporte20:Mercancia BienesTransp="'.$detalle->ClaveProducto.'" Descripcion="'.$detalle->Descripcion.'" Cantidad="'.$detalle->Cantidad.'" ClaveUnidad="'.$detalle->ClaveUnidad.'" Unidad="'.$detalle->Unidad.'" PesoEnKg="'.$detalle->PesoEnKilogramos.'" />';
                    break;
                default:
                    $bienesTransportados .=
                    '<cartaporte20:Mercancia BienesTransp="'.$detalle->ClaveProducto.'" Descripcion="'.$detalle->Descripcion.'" Cantidad="'.$detalle->Cantidad.'" ClaveUnidad="'.$detalle->ClaveUnidad.'" Unidad="'.$detalle->Unidad.'" PesoEnKg="'.$detalle->PesoEnKilogramos.'" MaterialPeligroso="Si" CveMaterialPeligroso="'.$detalle->CveMaterialPeligroso.'" Embalaje="'.$detalle->Embalaje.'" DescripEmbalaje="'.$detalle->DescripEmbalaje.'" />';
                    break;
            }
        }
        //Valida si se ingreso referencia para el destinatario
        $arraydoc = array();
        foreach($carta->documentos as $documento){
            array_push($arraydoc, $documento->UUID);
        }
        //Carta Porte
        if(sizeof($arraydoc) > 0){
            switch ((int)$carta->carreteraFederal) {
                case 1:
                    $invoice = array(
                        "type" => \Facturapi\InvoiceType::TRASLADO,
                        "customer" => array(
                            'legal_name' =>$carta->NombreRemitente,
                            "tax_id" => $carta->RfcRemitente,
                            "tax_system" => $carta->RegimenFiscal,
                            "address" => array(
                                "zip" => $carta->CodigoPostalRemitente
                            )
                        ),
                        "related_documents" => array(
                            array(
                                "relationship" => '05',
                                "documents" => $arraydoc
                            )
                        ),

                        "complements" => array(
                            //"type" => "P",
                            array(
                                "type" => "custom",
                                "data" => "<?xml version='1.0' standalone='yes'?>".
                                '<cartaporte20:CartaPorte Version="2.0" TranspInternac="No" TotalDistRec="'.$carta->TotalDistanciaRecorrida.'">'.
                                    '<cartaporte20:Ubicaciones>'.
                                        '<cartaporte20:Ubicacion TipoUbicacion="Origen" IDUbicacion="OR000123" RFCRemitenteDestinatario="'.$carta->RfcRemitente.'" NombreRemitenteDestinatario="'.$this->empresa->Nombre.'" FechaHoraSalidaLlegada="'.$salidaarray[0].'T'.$salidaarray[1].'">'.
                                            '<cartaporte20:Domicilio Municipio="'.$municipioRe->Clave.'" Calle="'.$carta->CalleRemitente.'" Pais="MEX" Estado="'.$carta->EstadoRemitente.'" CodigoPostal="'.$carta->CodigoPostalRemitente.'"/>'.
                                        '</cartaporte20:Ubicacion>'.
                                        '<cartaporte20:Ubicacion TipoUbicacion="Destino" IDUbicacion="DE000456" RFCRemitenteDestinatario="'.$carta->RfcDestinatario.'" NombreRemitenteDestinatario="'.$carta->NombreDestinatario.'" FechaHoraSalidaLlegada="'.$llegadaarray[0].'T'.$llegadaarray[1].'" DistanciaRecorrida="'.$carta->TotalDistanciaRecorrida.'">'.
                                            '<cartaporte20:Domicilio Municipio="'.$municipioDe->Clave.'" Calle="'.$carta->CalleDestinatario.'" Pais="MEX" Estado="'.$carta->EstadoDestinatario.'" CodigoPostal="'.$carta->CodigoPostalDestinatario.'"/>'.
                                        '</cartaporte20:Ubicacion>'.
                                    '</cartaporte20:Ubicaciones>'.
                                    '<cartaporte20:Mercancias PesoBrutoTotal="'.$carta->PesoBrutoTotal.'" UnidadPeso="KGM" NumTotalMercancias="'.$carta->TotalMercancias.'">'.
                                        $bienesTransportados.
                                        '<cartaporte20:Autotransporte PermSCT="TPXX00" NumPermisoSCT="TPXX00">'.
                                            '<cartaporte20:IdentificacionVehicular ConfigVehicular="'.$carta->ConfiguracionVehicular.'" PlacaVM="'.$carta->PlacaVehiculoMotor.'" AnioModeloVM="'.$carta->AnoModeloVehiculoMotor.'"/>'.
                                            '<cartaporte20:Seguros AseguraRespCivil="'.$carta->NombreAsegurado.'" PolizaRespCivil="'.$carta->NumeroPolizaSeguro.'"/>'.
                                        '</cartaporte20:Autotransporte>'.
                                    '</cartaporte20:Mercancias>'.
                                    '<cartaporte20:FiguraTransporte>'.
                                        '<cartaporte20:TiposFigura TipoFigura="'.$carta->ClaveTransporte.'" RFCFigura="'.$carta->RfcOperador.'" NumLicencia="'.$carta->NumeroLicencia.'" NombreFigura="'.$carta->NombreOperador.'">'.
                                        '</cartaporte20:TiposFigura>'.
                                    '</cartaporte20:FiguraTransporte>'.
                                '</cartaporte20:CartaPorte>'
                            )
                        ),
                        "items" => $arraydet,
                        "folio_number" => $carta->Folio,
                        "series" => $carta->Serie,
                        "currency" => 'XXX',
                        "use" => 'S01'
                    );
                    break;
                default:
                    $invoice = array(
                        "type" => \Facturapi\InvoiceType::TRASLADO,
                        "customer" => array(
                            'legal_name' =>$carta->NombreRemitente,
                            "tax_id" => $carta->RfcRemitente,
                            "tax_system" => $carta->RegimenFiscal,
                            "address" => array(
                                "zip" => $carta->CodigoPostalRemitente
                            )
                        ),
                        "related_documents" => array(
                            array(
                                "relationship" => '05',
                                "documents" => $arraydoc
                            )
                        ),

                        // "pdf_custom_section" =>
                        // //Datos extra (Permisos)
                        // '<ul><li>Datos Auto Transporte</li></ul><br>'.
                        // '<table>'.
                        //     '<thead>'.
                        //         '<tr><th>Permiso de la SCT</th><th>Numero de Permiso</th></tr>'.
                        //     '</thead>'.
                        //     '<tbody>'.
                        //         '<tr>'.
                        //             '<td><b>'.$carta->PermisoSCT.'</b></td>'.
                        //             '<td><b>'.$carta->NumeroPermisoSCT.'</b></td>'.
                        //         '</tr>'.
                        //     '</tbody>'.
                        // '</table>'.
                        // //Datos extra (Autotransporte)
                        // '<ul><li>Datos Vehiculares</li></ul><br>'.
                        // '<table>'.
                        //     '<thead>'.
                        //         '<tr><th>Marca</th><th>Modelo</th><th>Año</th><th>Placa</th></tr>'.
                        //     '</thead>'.
                        //     '<tbody>'.
                        //         '<tr>'.
                        //             '<td><b>'.$vehiculo->Marca.'</b></td>'.
                        //             '<td><b>'.$vehiculo->Modelo.'</b></td>'.
                        //             '<td><b>'.$vehiculo->Año.'</b></td>'.
                        //             '<td><b>'.$vehiculo->Placa.'</b></td>'.
                        //         '</tr>'.
                        //     '</tbody>'.
                        //     '<tfoot>'.
                        //         '<tr>'.
                        //             '<th><b>Configuración vehicular </b></th>'.
                        //             '<th>'.$confVehicular->Clave.'(<b>'.$confVehicular->Descripcion.'</b>)</th>'.
                        //         '</tr>'.
                        //     '</tfoot>'.
                        // '</table>',

                        "namespaces" => array(
                            array(
                                "prefix" => 'cartaporte20',
                                "uri" => 'http://www.sat.gob.mx/CartaPorte20',
                                "schema_location" => "http://www.sat.gob.mx/CartaPorte20 http://www.sat.gob.mx/sitio_internet/cfd/CartaPorte/CartaPorte20.xsd"

                            )
                        ),
                        "items" => $arraydet,
                        "folio_number" => $carta->Folio,
                        "series" => $carta->Serie,
                        "currency" => 'XXX',
                        "use" => 'S01'
                    );
                    break;
            }

        }else{
            switch ((int)$carta->carreteraFederal) {
                case 1:
                    $invoice = array(
                        "type" => \Facturapi\InvoiceType::TRASLADO,
                        "customer" => array(
                            'legal_name' =>$carta->NombreRemitente,
                            "tax_id" => $carta->RfcRemitente,
                            "tax_system" => $carta->RegimenFiscal,
                            "address" => array(
                                "zip" => $carta->CodigoPostalRemitente
                            )
                        ),

                        // "pdf_custom_section" =>
                        //     //Datos extra (Permisos)
                        //     '<ul><li>Datos Auto Transporte</li></ul><br>'.
                        //     '<table>'.
                        //         '<thead>'.
                        //             '<tr><th>Permiso de la SCT</th><th>Numero de Permiso</th></tr>'.
                        //         '</thead>'.
                        //         '<tbody>'.
                        //             '<tr>'.
                        //                 '<td><b>'.$carta->PermisoSCT.'</b></td>'.
                        //                 '<td><b>'.$carta->NumeroPermisoSCT.'</b></td>'.
                        //             '</tr>'.
                        //         '</tbody>'.
                        //     '</table> <br>'.
                        //     //Datos extra (Autotransporte)
                        //     '<ul><li>Datos Vehiculares</li></ul><br>'.
                        //     '<table>'.
                        //         '<thead>'.
                        //             '<tr><th>Marca</th><th>Modelo</th><th>Año</th><th>Placa</th></tr>'.
                        //         '</thead>'.
                        //         '<tbody>'.
                        //             '<tr>'.
                        //                 '<td><b>'.$vehiculo->Marca.'</b></td>'.
                        //                 '<td><b>'.$vehiculo->Modelo.'</b></td>'.
                        //                 '<td><b>'.$vehiculo->Año.'</b></td>'.
                        //                 '<td><b>'.$vehiculo->Placa.'</b></td>'.
                        //             '</tr>'.
                        //         '</tbody>'.
                        //         '<tfoot>'.
                        //             '<tr>'.
                        //                 '<th><b></b></th>'.
                        //                 '<th></th>'.
                        //             '</tr>'.
                        //             '<tr>'.
                        //                 '<th><b>Configuración vehicular </b></th>'.
                        //                 '<th>'.$confVehicular->Clave.'; '.$confVehicular->Descripcion.'</th>'.
                        //             '</tr>'.
                        //         '</tfoot>'.
                        //     '</table>',

                        "complements" => array(
                            //"type" => "P",
                            array(
                                "type" => "custom",
                                "data" => "<?xml version='1.0' standalone='yes'?>".
                                '<cartaporte20:CartaPorte Version="2.0" TranspInternac="No" TotalDistRec="'.$carta->TotalDistanciaRecorrida.'">'.
                                    '<cartaporte20:Ubicaciones>'.
                                        '<cartaporte20:Ubicacion TipoUbicacion="Origen" IDUbicacion="OR000123" RFCRemitenteDestinatario="'.$carta->RfcRemitente.'" NombreRemitenteDestinatario="'.$this->empresa->Nombre.'" FechaHoraSalidaLlegada="'.$salidaarray[0].'T'.$salidaarray[1].'">'.
                                            '<cartaporte20:Domicilio Municipio="'.$municipioRe->Clave.'" Calle="'.$carta->CalleRemitente.'" Pais="MEX" Estado="'.$carta->EstadoRemitente.'" CodigoPostal="'.$carta->CodigoPostalRemitente.'"/>'.
                                        '</cartaporte20:Ubicacion>'.
                                        '<cartaporte20:Ubicacion TipoUbicacion="Destino" IDUbicacion="DE000456" RFCRemitenteDestinatario="'.$carta->RfcDestinatario.'" NombreRemitenteDestinatario="'.$carta->NombreDestinatario.'" FechaHoraSalidaLlegada="'.$llegadaarray[0].'T'.$llegadaarray[1].'" DistanciaRecorrida="'.$carta->TotalDistanciaRecorrida.'">'.
                                            '<cartaporte20:Domicilio Municipio="'.$municipioDe->Clave.'" Calle="'.$carta->CalleDestinatario.'" Pais="MEX" Estado="'.$carta->EstadoDestinatario.'" CodigoPostal="'.$carta->CodigoPostalDestinatario.'"/>'.
                                        '</cartaporte20:Ubicacion>'.
                                    '</cartaporte20:Ubicaciones>'.
                                    '<cartaporte20:Mercancias PesoBrutoTotal="'.$carta->PesoBrutoTotal.'" UnidadPeso="KGM" NumTotalMercancias="'.$carta->TotalMercancias.'">'.
                                        $bienesTransportados.
                                        '<cartaporte20:Autotransporte PermSCT="TPXX00" NumPermisoSCT="TPXX00">'.
                                            '<cartaporte20:IdentificacionVehicular ConfigVehicular="'.$carta->ConfiguracionVehicular.'" PlacaVM="'.$carta->PlacaVehiculoMotor.'" AnioModeloVM="'.$carta->AnoModeloVehiculoMotor.'"/>'.
                                            '<cartaporte20:Seguros AseguraRespCivil="'.$carta->NombreAsegurado.'" PolizaRespCivil="'.$carta->NumeroPolizaSeguro.'"/>'.
                                        '</cartaporte20:Autotransporte>'.
                                    '</cartaporte20:Mercancias>'.
                                    '<cartaporte20:FiguraTransporte>'.
                                        '<cartaporte20:TiposFigura TipoFigura="'.$carta->ClaveTransporte.'" RFCFigura="'.$carta->RfcOperador.'" NumLicencia="'.$carta->NumeroLicencia.'" NombreFigura="'.$carta->NombreOperador.'">'.
                                        '</cartaporte20:TiposFigura>'.
                                    '</cartaporte20:FiguraTransporte>'.
                                '</cartaporte20:CartaPorte>'
                            )
                        ),
                        "namespaces" => array(
                            array(
                                "prefix" => 'cartaporte20',
                                "uri" => 'http://www.sat.gob.mx/CartaPorte20',
                                "schema_location" => "http://www.sat.gob.mx/CartaPorte20 http://www.sat.gob.mx/sitio_internet/cfd/CartaPorte/CartaPorte20.xsd"

                            )
                        ),
                        "items" => $arraydet,
                        "folio_number" => $carta->Folio,
                        "series" => $carta->Serie,
                        "currency" => 'XXX',
                        "use" => 'S01'
                    );
                    break;
                default:
                    $invoice = array(
                        "type" => \Facturapi\InvoiceType::TRASLADO,
                        "customer" => array(
                            'legal_name' =>$carta->NombreRemitente,
                            "tax_id" => $carta->RfcRemitente,
                            "tax_system" => $carta->RegimenFiscal,
                            "address" => array(
                                "zip" => $carta->CodigoPostalRemitente
                            )
                        ),

                        // "pdf_custom_section" =>
                        //     //Datos extra (Permisos)
                        //     '<ul><li>Datos Auto Transporte</li></ul><br>'.
                        //     '<table>'.
                        //         '<thead>'.
                        //             '<tr><th>Permiso de la SCT</th><th>Numero de Permiso</th></tr>'.
                        //         '</thead>'.
                        //         '<tbody>'.
                        //             '<tr>'.
                        //                 '<td><b>'.$carta->PermisoSCT.'</b></td>'.
                        //                 '<td><b>'.$carta->NumeroPermisoSCT.'</b></td>'.
                        //             '</tr>'.
                        //         '</tbody>'.
                        //     '</table> <br>'.
                        //     //Datos extra (Autotransporte)
                        //     '<ul><li>Datos Vehiculares</li></ul><br>'.
                        //     '<table>'.
                        //         '<thead>'.
                        //             '<tr><th>Marca</th><th>Modelo</th><th>Año</th><th>Placa</th></tr>'.
                        //         '</thead>'.
                        //         '<tbody>'.
                        //             '<tr>'.
                        //                 '<td><b>'.$vehiculo->Marca.'</b></td>'.
                        //                 '<td><b>'.$vehiculo->Modelo.'</b></td>'.
                        //                 '<td><b>'.$vehiculo->Año.'</b></td>'.
                        //                 '<td><b>'.$vehiculo->Placa.'</b></td>'.
                        //             '</tr>'.
                        //         '</tbody>'.
                        //         '<tfoot>'.
                        //             '<tr>'.
                        //                 '<th><b></b></th>'.
                        //                 '<th></th>'.
                        //             '</tr>'.
                        //             '<tr>'.
                        //                 '<th><b>Configuración vehicular </b></th>'.
                        //                 '<th>'.$confVehicular->Clave.'; '.$confVehicular->Descripcion.'</th>'.
                        //             '</tr>'.
                        //         '</tfoot>'.
                        //     '</table>',
                        "items" => $arraydet,
                        "folio_number" => $carta->Folio,
                        "series" => $carta->Serie,
                        "currency" => 'XXX',
                        "use" => 'S01'
                    );
                    break;
            }

        }
        $new_invoice = $this->facturapi->Invoices->create( $invoice );
        $result = json_encode($new_invoice);
        $result2 = json_decode($result, true);
        if(array_key_exists('ok', $result2) == true){
            $mensaje = $new_invoice->message;
            $tipomensaje = "error";
            $data = array(
                        'mensaje' => "Error, ".$mensaje,
                        'tipomensaje' => $tipomensaje
                    );
            return response()->json($data);
        }else{
            //obtener datos del xml del documento timbrado para guardarlo en la tabla comprobantes
            $descargar_xml = $this->facturapi->Invoices->download_xml($new_invoice->id); // stream containing the XML file or
            $xml = simplexml_load_string($descargar_xml);
            $comprobante = $xml->attributes();
            $CertificadoCFD = $comprobante['NoCertificado'];
            //obtener datos generales del xml nodo Emisor
            $activar_namespaces = $xml->getNameSpaces(true);
            $namespaces = $xml->children($activar_namespaces['cfdi']);
            //obtener UUID del xml timbrado digital
            $activar_namespaces = $namespaces->Complemento->getNameSpaces(true);
            $namespaces_uuid = $namespaces->Complemento->children($activar_namespaces['tfd']);
            $atributos_complemento = $namespaces_uuid->TimbreFiscalDigital->attributes();
            $NoCertificadoSAT = $atributos_complemento['NoCertificadoSAT'];
            $SelloCFD = $atributos_complemento['SelloCFD'];
            $SelloSAT = $atributos_complemento['SelloSAT'];
            $fechatimbrado = $atributos_complemento['FechaTimbrado'];
            $cadenaoriginal = "||".$atributos_complemento['Version']."|".$new_invoice->uuid."|".$atributos_complemento['FechaTimbrado']."|".$atributos_complemento['SelloCFD']."|".$atributos_complemento['NoCertificadoSAT']."||";
            //guardar en tabla comprobante
            $Comprobante = new Comprobante;
            $Comprobante->Comprobante = 'CartaPorte';
            $Comprobante->Tipo = $new_invoice->type;
            $Comprobante->Version = '4.0';
            $Comprobante->Serie = $new_invoice->series;
            $Comprobante->Folio = $new_invoice->folio_number;
            $Comprobante->UUID = $new_invoice->uuid;
            $Comprobante->Fecha = $fechatimbrado;
            $Comprobante->SubTotal = $carta->SubTotal;
            $Comprobante->Descuento = $carta->Descuento;
            $Comprobante->Total = $carta->Total;
            $Comprobante->EmisorRfc = $carta->RfcRemitente;
            $Comprobante->ReceptorRfc = $carta->RfcRemitente;
            $Comprobante->FormaPago = 'NA';
            $Comprobante->MetodoPago = 'NA';
            $Comprobante->UsoCfdi = $carta->UsoCfdi;
            $Comprobante->Moneda = $new_invoice->currency;
            $Comprobante->TipoCambio = Helpers::convertirvalorcorrecto($new_invoice->exchange);
            $Comprobante->CertificadoSAT = $NoCertificadoSAT;
            $Comprobante->CertificadoCFD = $CertificadoCFD;
            $Comprobante->FechaTimbrado = $fechatimbrado;
            $Comprobante->CadenaOriginal = $cadenaoriginal;
            $Comprobante->selloSAT = $SelloSAT;
            $Comprobante->selloCFD = $SelloCFD;
            // //$Comprobante->CfdiTimbrado = $new_invoice->type;
            $Comprobante->Periodo = $this->periodohoy;
            $Comprobante->IdFacturapi = $new_invoice->id;
            $Comprobante->UrlVerificarCfdi = $new_invoice->verification_url;
            $Comprobante->save();
            //Colocar UUID en documento
            CartaPorte::where('CartaPorte', $request->cartatimbrado)
                            ->update([
                                'FechaTimbrado' => $fechatimbrado,
                                'UUID' => $new_invoice->uuid
                            ]);
            // Enviar a más de un correo (máx 10)
            $this->facturapi->Invoices->send_by_email(
                $new_invoice->id,
                array(
                    "alonso.espinares@socasa.com.mx",
                    //"marco.baltazar@utpcamiones.com.mx",
                )
            );
            $mensaje = "Correcto, el documento se timbro correctamente";
            $tipomensaje = "success";
            $data = array(
                        'mensaje' => $mensaje,
                        'tipomensaje' => $tipomensaje
                    );
            return response()->json($data);
        }
    }
    //verificar cancelacion timbre
    public function notas_credito_clientes_verificar_si_continua_baja_timbre(Request $request){
        $obtener_factura = '';
        $comprobante = '';
        $factura = NotaCliente::where('Nota', $request->facturabajatimbre)->first();
        $existe_comprobante = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')->count();
        if($existe_comprobante > 0){
            $comprobante = Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')->first();
            $obtener_factura = $this->facturapi->Invoices->retrieve($comprobante->IdFacturapi); // obtener factura
        }
        $data = array(
            'obtener_factura' => $obtener_factura,
            'factura' => $factura,
            'comprobante' => $comprobante
        );
        return response()->json($data);
    }
    //cancelar timbre
    public function notas_credito_clientes_baja_timbre(Request $request){
        //colocar fecha de cancelacion en tabla comprobante
        $factura = NotaCliente::where('Nota', $request->facturabajatimbre)->first();
        Comprobante::where('Comprobante', 'Nota')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')
        ->update([
            'FechaCancelacion' => Helpers::fecha_exacta_accion_datetimestring()
        ]);
        //cancelar timbre facturapi
        $timbrecancelado = $this->facturapi->Invoices->cancel($request->iddocumentofacturapi);
        return response()->json($timbrecancelado);
    }

    //obtener facturas relaciinadas
    public function carta_porte_obtener_facturas_cliente(Request $request){
        if($request->ajax()){
            $data = Factura::where('Cliente', $request->numerocliente)
            ->where('Status','<>','BAJA')
            ->where('Esquema','CFDI')
            ->where('Depto', '<>','SERVICIO')
            ->where('Periodo',$this->periodohoy)
            ->whereNotIn('Factura', CartaPorteDocumentos::select('Factura')->get()->toArray())
            ->where('UUID', '<>', '')
            ->orderBy('Fecha','Desc')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarfacturarel(\''.$data->UUID .'\',\''.$data->Factura.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Total', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total);
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }

    }
    //Obtiene los codigos de la factura relacionada
    public function carta_porte_obtener_datos_factura(Request $request){
        $detallesfactura = FacturaDetalle::where('Factura',$request->facturacarta)->get();
        $numerodetallesfactura = $detallesfactura->count();

        if ($numerodetallesfactura > 0 ) {
            $filasdetalles = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo = "alta";
            foreach ($detallesfactura as $detalle) {
                $MaterialPeligroso = '';
                $Embalaje = '';
                $functionbtnmaterial = 'onclick="listarclavespeligrosos('.$contadorproductos.')"';
                $fuincionbtnembalaje = 'onclick="listartiposembalaje('.$contadorfilas.');"';
                $peligroso = c_ClaveProdServCP::where('Clave',$detalle->ClaveProducto)->first();
                $valorpeligroso = (isset($peligroso) ? $peligroso->MaterialPeligroso : '0,1') ;
                $descripcionclaveproducto = ClaveProdServ::where('Clave',$detalle->ClaveProducto)->first();
                $descripcionclaveunidad = ClaveUnidad::where('Clave',$detalle->ClaveUnidad)->first();
                switch ($valorpeligroso) {
                    case 0:
                        $MaterialPeligroso = ' readonly ';
                        $Embalaje = ' readonly ';
                        $functionbtnmaterial = '';
                        $fuincionbtnembalaje = '';
                        break;
                    case 1:
                        $MaterialPeligroso = ' required ';
                        $Embalaje = ' required ';
                        $functionbtnmaterial = 'onclick="listarclavespeligrosos('.$contadorproductos.')"';
                        $fuincionbtnembalaje = 'onclick="listartiposembalaje('.$contadorfilas.');"';
                    default:
                        break;
                }
                if ($valorpeligroso == '0,1') {
                    $MaterialPeligroso = '';
                    $Embalaje = '';
                    $functionbtnmaterial = 'onclick="listarclavespeligrosos('.$contadorproductos.')"';
                    $fuincionbtnembalaje = 'onclick="listartiposembalaje('.$contadorfilas.');"';
                }
                $filasdetalles = $filasdetalles.'<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipo.'" readonly></td>'.
                    '<td>'.
                        '<input type="hidden" class="form-control divorinputmodsm partidapartida" name="partidapartida[]" value="'.($contadorproductos+1).'" />'.
                        '<span class="textopartida">'.($contadorproductos+1).'</span>'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$detalle->Codigo.'" readonly data-parsley-length="[1, 20]">'.$detalle->Codigo.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" class="form-control descripcionpartida" name="descripcionpartida[]" value="'.$detalle->Descripcion.'" readonly data-parsley-length="[1, 20]">'.$detalle->Descripcion.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadPartida" name="cantidadPartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularPesoPartida('.$contadorfilas.')" >'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="1.'.$this->numerocerosconfigurados.'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorproductos.');revisarcantidadnotavscantidadfactura('.$contadorfilas.');">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);">'.
                        '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                        '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm pesopartida" name="pesobrutopartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-min="0.'. $this->numerocerosconfigurados .'1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularPesoPartida('.$contadorfilas.')">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm pesototal" name="preciopartida[]" value="'.$detalle->Precio.'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');">'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.$detalle->Precio.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm pesototalpartida" name="pesototal[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-min="0.'. $this->numerocerosconfigurados .'1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" name="materialpeligrosopartida[]" value="'.$valorpeligroso.'"></input>'.
                        '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Material Peligroso" '.$functionbtnmaterial.'><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm clavematerialpeligrosopartida" name="clavematerialpeligrosopartida[]"  value="" '.$MaterialPeligroso.'>'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="hidden" class="form-control divorinputmodmd descripcionclavepeligrosopartida" name="descripcionclavepeligrosopartida[]" readonly  value="">'.
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Tipos Embalaje" '.$fuincionbtnembalaje.'><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm clavetipoembalajepartida" name="clavetipoembalajepartida[]"  value=""'. $Embalaje .'>'.
                            '</div>'.
                            '<div class="col-xs-5 col-sm-5 col-md-5">'.
                                '<input type="hidden" class="form-control divorinputmodmd descripcionembalajepartida" name="descripcionembalajepartida[]"  value="">'.
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod">'.
                      '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorproductos.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'. $detalle->ClaveProducto .'" readonly data-parsley-length="[1, 20]">'.
                            '</div>'.
                      '</div>'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$descripcionclaveproducto->Nombre.'" readonly>'.
                    '</td>'.
                    '<td class="tdmod">'.
                      '<div class="row divorinputmodxl">'.
                        '<div class="col-xs-2 col-sm-2 col-md-2">'.
                          '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesunidades" data-toggle="tooltip" title="Ver Claves Unidades" onclick="listarclavesunidades('.$contadorproductos.');" ><i class="material-icons">remove_red_eye</i></div>'.
                        '</div>'.
                        '<div class="col-xs-10 col-sm-10 col-md-10">'.
                          '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$detalle->ClaveUnidad.'" readonly data-parsley-length="[1, 5]">'.
                        '</div>'.
                      '</div>'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$descripcionclaveunidad->Nombre.'" readonly>'.
                    '</td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }

        }else{
            $filasdetalles = '';
        }
        $data = array(
            "filasdetallesfactura" => $filasdetalles,
            "numerodetallesfactura" => $numerodetallesfactura,
            "contadorproductos" => $contadorproductos,
            'MaterialPeligroso' => $MaterialPeligroso
        );
        return response()->json($data);
    }

}
