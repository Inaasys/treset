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
use Luecano\NumeroALetras\NumeroALetras;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NotasCreditoClientesExport;
use App\NotaCliente;
use App\NotaClienteDetalle;
use App\NotaClienteDocumento;
use App\Remision;
use App\Factura;
use App\FacturaDetalle;
use App\Cliente;
use App\Almacen;
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
Use App\Existencia;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Configuracion_Tabla;
use App\VistaNotaCreditoCliente;
use App\VistaObtenerExistenciaProducto;
use App\FolioComprobanteNota;
use App\Comprobante;
use Config;
use Mail;

class NotasCreditoClientesController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'NotasCreditoCliente')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }
    
    public function notas_credito_clientes(){
        $contarserieusuario = FolioComprobanteNota::where('Predeterminar', '+')->count();
        if($contarserieusuario == 0){
            $FolioComprobanteNota = FolioComprobanteNota::orderBy('Numero','DESC')->take(1)->get();
            $serieusuario = $FolioComprobanteNota[0]->Serie;
            $esquema = $FolioComprobanteNota[0]->Esquema;
        }else{
            $FolioComprobanteNota = FolioComprobanteNota::where('Predeterminar', '+')->first();
            $serieusuario = $FolioComprobanteNota->Serie;
            $esquema = $FolioComprobanteNota->Esquema;
        }
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('notas_credito_clientes_guardar_configuracion_tabla');
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
        return view('registros.notascreditoclientes.notascreditoclientes', compact('serieusuario','esquema','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento','lugarexpedicion','claveregimenfiscal','regimenfiscal'));
    }

    public function notas_credito_clientes_obtener(Request $request){
        if($request->ajax()){
            $fechahoy = Carbon::now()->toDateString();
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaNotaCreditoCliente::select($this->campos_consulta)->where('Periodo', $periodo)->orderBy('Fecha', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $botoncambios  =   '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Nota .'\')"><i class="material-icons">mode_edit</i></div> '; 
                        $botonbajas     =   '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Nota .'\')"><i class="material-icons">cancel</i></div>  ';
                        $botondocumentopdf = '<a href="'.route('notas_credito_clientes_generar_pdfs_indiv',$data->Nota).'" target="_blank"><div class="btn bg-blue-grey btn-xs waves-effect" data-toggle="tooltip" title="Generar Documento"><i class="material-icons">archive</i></div></a> ';
                        $botonenviaremail = '<div class="btn bg-brown btn-xs waves-effect" data-toggle="tooltip" title="Enviar Documento por Correo" onclick="enviardocumentoemail(\''.$data->Nota .'\')"><i class="material-icons">email</i></div> ';
                        $operaciones =      $botoncambios.$botonbajas.$botondocumentopdf.$botonenviaremail;
                        return $operaciones;
                    })
                    ->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    ->addColumn('Iva', function($data){ return $data->Iva; })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->addColumn('ImpLocTraslados', function($data){ return $data->ImpLocTraslados; })
                    ->addColumn('ImpLocRetenciones', function($data){ return $data->ImpLocRetenciones; })
                    ->addColumn('IepsRetencion', function($data){ return $data->IepsRetencion; })
                    ->addColumn('IsrRetencion', function($data){ return $data->IsrRetencion; })
                    ->addColumn('IvaRetencion', function($data){ return $data->IvaRetencion; })
                    ->addColumn('Ieps', function($data){ return $data->Ieps; })
                    ->addColumn('Descuento', function($data){ return $data->Descuento; })
                    ->addColumn('Importe', function($data){ return $data->Importe; })
                    ->addColumn('TipoCambio', function($data){ return $data->TipoCambio; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }

    //obtener ultimo folio
    public function notas_credito_clientes_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\NotaCliente', $request->serie);
        return response()->json($folio);
    }

    //obtener clientes
    public function notas_credito_clientes_obtener_clientes(Request $request){
        if($request->ajax()){
            //$data = Cliente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            $data = DB::table('Clientes as c')
            ->leftJoin('c_FormaPago as fp', 'fp.Clave', '=', 'c.FormaPago')
            ->leftJoin('c_MetodoPago as mp', 'mp.Clave', '=', 'c.MetodoPago')
            ->leftJoin('c_UsoCFDI as uc', 'uc.Clave', '=', 'c.UsoCfdi')
            ->leftJoin('c_Pais as p', 'p.Clave', '=', 'c.Pais')
            ->select('c.Numero', 'c.Nombre', 'c.Plazo', 'c.Rfc', 'c.Agente', 'c.Credito', 'c.Saldo', 'c.Status', 'c.Municipio', 'c.Tipo', 'fp.Clave AS ClaveFormaPago', 'fp.Nombre AS NombreFormaPago', 'mp.Clave AS ClaveMetodoPago', 'mp.Nombre AS NombreMetodoPago', 'uc.Clave AS ClaveUsoCfdi', 'uc.Nombre AS NombreUsoCfdi', 'p.Clave AS ClavePais', 'p.Nombre AS NombrePais')
            ->where('c.Status', 'ALTA')
            ->orderBy("Numero", "DESC")
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$data->Rfc.'\',\''.$data->ClaveFormaPago.'\',\''.$data->NombreFormaPago.'\',\''.$data->ClaveMetodoPago.'\',\''.$data->NombreMetodoPago.'\',\''.$data->ClaveUsoCfdi.'\',\''.$data->NombreUsoCfdi.'\',\''.$data->ClavePais.'\',\''.$data->NombrePais.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener almacenes
    public function notas_credito_clientes_obtener_almacenes(Request $request){
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

    //obtener codifos postales
    public function notas_credito_clientes_obtener_codigos_postales(Request $request){
        if($request->ajax()){
            $data = CodigoPostal::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarlugarexpedicion(\''.$data->Clave .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener regimenes fiscales
    public function notas_credito_clientes_obtener_regimenes_fiscales(Request $request){
        if($request->ajax()){
            $data = c_RegimenFiscal::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarregimenfiscal(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener tipos relacion
    public function notas_credito_clientes_obtener_tipos_relacion(Request $request){
        if($request->ajax()){
            $data = c_TipoRelacion::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionartiporelacion(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener formas pago
    public function notas_credito_clientes_obtener_formas_pago(Request $request){
        if($request->ajax()){
            $data = FormaPago::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarformapago(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener metodos pago
    public function notas_credito_clientes_obtener_metodos_pago(Request $request){
        if($request->ajax()){
            $data = MetodoPago::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmetodopago(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener usos cfdi
    public function notas_credito_clientes_obtener_usos_cfdi(Request $request){
        if($request->ajax()){
            $data = UsoCFDI::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarusocfdi(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener residencias fiscales
    public function notas_credito_clientes_obtener_residencias_fiscales(Request $request){
        if($request->ajax()){
            $data = Pais::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarresidenciafiscal(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener facturas
    public function notas_credito_clientes_obtener_facturas(Request $request){
        if($request->ajax()){
            $arrayfacturasseleccionadas = Array();
            foreach(explode(",", $request->stringfacturasseleccionadas) as $factura){
                array_push($arrayfacturasseleccionadas, $factura);
            }
            $data = Factura::where('Cliente', $request->numerocliente)
                                ->whereNotIn('Factura', $arrayfacturasseleccionadas)
                                ->where('Status', 'POR COBRAR')
                                ->orderBy('Folio', 'DESC')
                                ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarfactura('.$data->Folio.',\''.$data->Factura .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){
                        return Helpers::fecha_espanol($data->Fecha);
                    })
                    ->addColumn('Items', function($data){
                        return FacturaDetalle::where('Factura', $data->Factura)->count();
                    })
                    ->addColumn('Total', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total);
                    })
                    ->addColumn('Abonos', function($data){
                        return Helpers::convertirvalorcorrecto($data->Abonos);
                    })
                    ->addColumn('Descuentos', function($data){
                        return Helpers::convertirvalorcorrecto($data->Descuentos);
                    })
                    ->addColumn('Saldo', function($data){
                        return Helpers::convertirvalorcorrecto($data->Saldo);
                    })
                    ->rawColumns(['operaciones','Fecha','Total'])
                    ->make(true);
        }
    }

    //obtener factura
    public function notas_credito_clientes_obtener_factura(Request $request){
        $factura = Factura::where('Factura', $request->Factura)->first();
        $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($factura->Iva, $factura->SubTotal);
        $tipooperacion = $request->tipooperacion;
        //detalles factura
        $filafactura = '';
        $filafactura= $filafactura.
        '<tr class="filasfacturas" id="filafactura'.$request->contadorfilasfacturas.'">'.
            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfilafactura" onclick="eliminarfilafacturanotacliente('.$request->contadorfilasfacturas.')" >X</div><input type="hidden" class="form-control facturaagregadoen" name="facturaagregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
            '<td class="tdmod"><input type="hidden" class="form-control facturaaplicarpartida" name="facturaaplicarpartida[]" value="'.$factura->Factura.'" readonly>'.$factura->Factura.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control fechafacturapartida" name="fechafacturapartida[]" value="'.$factura->Fecha.'" readonly>'.$factura->Fecha.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control uuidfacturapartida" name="uuidfacturapartida[]" value="'.$factura->UUID.'" readonly>'.$factura->UUID.'</td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd totalpesosfacturapartida" name="totalpesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonosfacturapartida" name="abonosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Abonos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd notascreditofacturapartida" name="notascreditofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Descuentos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd descuentopesosfacturapartida" name="descuentopesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-max="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilastablafacturas('.$request->contadorfilasfacturas.');" ></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd saldofacturapartida" name="saldofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
        '</tr>';
        $data = array(
            "factura" => $factura,
            "filafactura" => $filafactura,
        );
        return response()->json($data);   
    }

    //obtener datos almacen
    public function notas_credito_clientes_obtener_datos_almacen(Request $request){
        $factura = Factura::where('Factura', $request->factura)->first();
        $remision = Remision::where('Status', $request->factura)->first();
        $almacen = Almacen::where('Numero', $remision->Almacen)->first();
        $data = array(
            'factura' => $factura,
            'almacen' => $almacen
        );
        return response()->json($data);
    }

    //obtener productos
    public function notas_credito_clientes_obtener_productos(Request $request){
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
            $data = VistaObtenerExistenciaProducto::whereIn('Codigo', $arrayproductosseleccionables)->where('Codigo', 'like', '%' . $codigoabuscar . '%')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($numeroalmacen, $tipooperacion, $stringfacturasseleccionadas){
                        if($data->Almacen == $numeroalmacen){
                            $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$data->NombreClaveProducto.'\',\''.$data->NombreClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
                        }else{
                            $boton = '';
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

    //comprobar cantidad nota vs cantidad factura
    public function notas_credito_cliente_comprobar_cantidad_nota_vs_cantidad_factura(Request $request){
        $notacliente = $request->folio.'-'.$request->serie;
        $facturadetalle = FacturaDetalle::where('Factura', $request->factura)->where('Codigo', $request->codigopartida)->first();
        $cantidadfactura = $facturadetalle->Cantidad;
        $cantidadesenotrasnotas = 0;
        $existencantidadesenotrasnotas = NotaClienteDocumento::where('Nota', '<>', $notacliente)->where('Factura', $request->factura)->count();
        if($existencantidadesenotrasnotas > 0){
            $detallesnotasutilizadas = NotaClienteDocumento::where('Nota', '<>', $notacliente)->where('Factura', $request->factura)->get();
            foreach($detallesnotasutilizadas as $dnu){
                $existedetallenota = NotaClienteDetalle::where('Nota', $dnu->Nota)->where('Codigo', $request->codigopartida)->count();
                if($existedetallenota > 0){
                    $detallenota = NotaClienteDetalle::where('Nota', $dnu->Nota)->where('Codigo', $request->codigopartida)->first();
                    $cantidadesenotrasnotas = $cantidadesenotrasnotas + $detallenota->Cantidad;
                } 
            }
        }
        $cantidadmaximapermitida = $cantidadfactura + $cantidadesenotrasnotas;
        return response()->json(Helpers::convertirvalorcorrecto($cantidadmaximapermitida));
    }

    //obtener folios notas
    public function notas_credito_clientes_obtener_folios_fiscales(Request $request){
        if($request->ajax()){
            $data = FolioComprobanteNota::where('Status', 'ALTA')->OrderBy('Numero', 'DESC')->get();
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
    public function notas_credito_clientes_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\NotaCliente', $request->Serie);
        return response()->json($folio);
    }

    //alta
    public function notas_credito_clientes_guardar(Request $request){ 
        ini_set('max_input_vars','10000' );
            //obtener el ultimo id de la tabla
            $folio = Helpers::ultimofolioserietablamodulos('App\NotaCliente', $request->serie);
            //INGRESAR DATOS A TABLA COMPRAS
            $notacliente = $folio.'-'.$request->serie;
            $NotaCliente = new NotaCliente;
            $NotaCliente->Nota=$notacliente;
            $NotaCliente->Serie=$request->serie;
            $NotaCliente->Folio=$folio;
            $NotaCliente->Esquema=$request->esquema;
            $NotaCliente->Cliente=$request->numerocliente;
            $NotaCliente->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
            $NotaCliente->Almacen=$request->numeroalmacen; 
            $NotaCliente->Importe=$request->importe;
            $NotaCliente->Descuento=$request->descuento;
            $NotaCliente->Ieps=$request->ieps;  
            $NotaCliente->SubTotal=$request->subtotal;
            $NotaCliente->Iva=$request->iva;
            $NotaCliente->IvaRetencion=$request->retencioniva;
            $NotaCliente->IsrRetencion=$request->retencionisr;
            $NotaCliente->IepsRetencion=$request->retencionieps;
            $NotaCliente->Total=$request->total;
            $NotaCliente->Moneda=$request->moneda;
            $NotaCliente->TipoCambio=$request->pesosmoneda;
            $NotaCliente->Obs=$request->observaciones;
            $NotaCliente->Status="ALTA";
            $NotaCliente->Usuario=Auth::user()->user;
            $NotaCliente->CondicionesDePago=$request->condicionesdepago;
            $NotaCliente->LugarExpedicion=$request->lugarexpedicion;
            $NotaCliente->RegimenFiscal=$request->claveregimenfiscal;
            $NotaCliente->TipoRelacion=$request->clavetiporelacion;
            $NotaCliente->Confirmacion=$request->confirmacion;
            $NotaCliente->FormaPago=$request->claveformapago;
            $NotaCliente->MetodoPago=$request->clavemetodopago;
            $NotaCliente->UsoCfdi=$request->claveusocfdi;
            $NotaCliente->ResidenciaFiscal=$request->claveresidenciafiscal;
            $NotaCliente->NumRegIdTrib=$request->numeroregidtrib;
            $NotaCliente->EmisorRfc=$request->emisorrfc;
            $NotaCliente->EmisorNombre=$request->emisornombre;
            $NotaCliente->ReceptorRfc=$request->receptorrfc;
            $NotaCliente->ReceptorNombre=$request->receptornombre;
            $NotaCliente->Hora=Helpers::fecha_mas_hora_exacta_accion_datetimestring($request->fecha);
            $NotaCliente->Periodo=$request->periodohoy;
            $NotaCliente->save();
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO NOTAS PROVEEDOR
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "NOTAS CLIENTE";
            $BitacoraDocumento->Movimiento = $notacliente;
            $BitacoraDocumento->Aplicacion = "ALTA";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = "ALTA";
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $request->periodohoy;
            $BitacoraDocumento->save();
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO NOTAS PROVEEDOR DOC
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "NOTAS CLIENTE DOC";
            $BitacoraDocumento->Movimiento = $notacliente;
            $BitacoraDocumento->Aplicacion = "ALTA";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = "ALTA";
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $request->periodohoy;
            $BitacoraDocumento->save();
            //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
            $item = 1;
            foreach ($request->codigopartida as $key => $codigopartida){             
                $NotaClienteDetalle=new NotaClienteDetalle;
                $NotaClienteDetalle->Nota = $notacliente;
                $NotaClienteDetalle->Cliente = $request->numerocliente;
                $NotaClienteDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $NotaClienteDetalle->Codigo = $codigopartida;
                $NotaClienteDetalle->Descripcion = $request->descripcionpartida [$key];
                $NotaClienteDetalle->Unidad = $request->unidadpartida [$key];
                $NotaClienteDetalle->Cantidad =  $request->cantidadpartida  [$key];
                $NotaClienteDetalle->Precio =  $request->preciopartida [$key];
                $NotaClienteDetalle->Importe = $request->importepartida [$key];
                $NotaClienteDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                $NotaClienteDetalle->Descuento = $request->descuentopesospartida [$key];
                $NotaClienteDetalle->ImporteDescuento = $request->importedescuentopesospartida [$key];
                $NotaClienteDetalle->Ieps = $request->trasladoiepspesospartida [$key];
                $NotaClienteDetalle->SubTotal = $request->subtotalpartida [$key];
                $NotaClienteDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                $NotaClienteDetalle->Iva = $request->trasladoivapesospartida [$key];
                $NotaClienteDetalle->IvaRetencion = $request->retencionivapesospartida [$key];
                $NotaClienteDetalle->IsrRetencion = $request->retencionisrpesospartida [$key];
                $NotaClienteDetalle->IepsRetencion = $request->retencioniepspesospartida [$key];
                $NotaClienteDetalle->Total = $request->totalpesospartida [$key];
                $NotaClienteDetalle->Costo = $request->costopartida [$key];
                $NotaClienteDetalle->Partida = $request->partidapartida [$key];
                $NotaClienteDetalle->ClaveProducto = $request->claveproductopartida [$key];
                $NotaClienteDetalle->ClaveUnidad = $request->claveunidadpartida [$key];
                $NotaClienteDetalle->Item = $item;
                $NotaClienteDetalle->save();
                if($codigopartida != 'DPPP'){
                    //sumar existencias del almacen 
                    $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->count();
                    if($ContarExistenciaAlmacen > 0){
                        $ExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                        $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias + $request->cantidadpartida [$key];
                        Existencia::where('Codigo', $codigopartida)
                                    ->where('Almacen', $request->numeroalmacen)
                                    ->update([
                                        'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                    ]);
                    }else{
                        $ExistenciaAlmacen = new Existencia;
                        $ExistenciaAlmacen->Codigo = $codigopartida;
                        $ExistenciaAlmacen->Almacen = $request->numeroalmacen;
                        $ExistenciaAlmacen->Existencias = $request->cantidadpartida [$key];
                        $ExistenciaAlmacen->save();
                    }
                }
                $item++;
            }
            //INGRESAR DATOS A TABLA NOTA CLIENTES DOCUMENTOS
            $itemdocumento = 1;
            foreach ($request->facturaaplicarpartida as $key => $facturapartida){             
                $NotaClienteDocumento=new NotaClienteDocumento;
                $NotaClienteDocumento->Nota = $notacliente;
                $NotaClienteDocumento->Factura = $facturapartida;
                $NotaClienteDocumento->UUID = $request->uuidfacturapartida [$key];
                $NotaClienteDocumento->Descuento = $request->descuentopesosfacturapartida [$key];
                $NotaClienteDocumento->Item = $itemdocumento;
                $NotaClienteDocumento->save();
                //Modificar Factura
                Factura::where('Factura', $facturapartida)
                ->update([
                    'Descuentos' => $request->descuentopesosfacturapartida [$key],
                    'Saldo' => $request->saldofacturapartida [$key]
                ]);
                //Si el saldo es igual a 0 liquidar factura
                if($request->saldofacturapartida [$key] == Helpers::convertirvalorcorrecto(0)){
                    Factura::where('Factura', $facturapartida)
                            ->update([
                                'Status' => "LIQUIDADO"
                            ]);
                }
                $itemdocumento++;
            }
            return response()->json($NotaCliente);  
    }

    //verificar si se puede dar de bajar nota cliente
    public function notas_credito_clientes_verificar_si_continua_baja(Request $request){
        $errores = '';
        $NotaCliente = NotaCliente::where('Nota', $request->notadesactivar)->first();
        $DetallesNotaCliente = NotaClienteDetalle::where('Nota', $request->notadesactivar)->get();
        foreach($DetallesNotaCliente as $detalle){
            if($detalle->Codigo != 'DPPP'){
                $existencias = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $NotaCliente->Almacen)->first();
                if($existencias->Existencias < $detalle->Cantidad){
                    $errores = $errores.'Error la nota de cliente no se puede dar de baja porque no hay existencias suficientes en el almacen: '.$NotaCliente->Almacen.' para el código: '.$detalle->Codigo.'<br>';
                }
            }
        }  
        $resultadofechas = Helpers::compararanoymesfechas($NotaCliente->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'errores' => $errores,
            'Status' => $NotaCliente->Status
        );
        return response()->json($data);
    }

    //bajas
    public function notas_credito_clientes_alta_o_baja(Request $request){
        $NotaCliente = NotaCliente::where('Nota', $request->notadesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        NotaCliente::where('Nota', $request->notadesactivar)
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
        $detalles = NotaClienteDetalle::where('Nota', $request->notadesactivar)->get();
        //notas proveedor detalles
        foreach($detalles as $detalle){
            if($detalle->Codigo != 'DPPP'){
                //restar existencias al almacen
                $ExistenciaAlmacen = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $NotaCliente->Almacen)->first();
                $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias-$detalle->Cantidad;
                Existencia::where('Codigo', $detalle->Codigo)
                            ->where('Almacen', $NotaCliente->Almacen)
                            ->update([
                                'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                            ]);
            }
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
        //nota cliente documentos
        $detallesdocumentos = NotaClienteDocumento::where('Nota', $request->notadesactivar)->get(); 
        foreach($detallesdocumentos as $detalledocumento){
            $notaclientedocumento = NotaClienteDocumento::where('Nota', $request->notadesactivar)->where('Factura', $detalledocumento->Factura)->first();
            $facturadocumento = Factura::where('Factura', $detalledocumento->Factura)->first();
            //Regresar saldo y descuentos a la factura
            $NuevoDescuentos = $facturadocumento->Descuentos - $notaclientedocumento->Descuento;
            $NuevoSaldo = $facturadocumento->Saldo + $notaclientedocumento->Descuento;
            Factura::where('Factura', $detalledocumento->Factura)
            ->update([
                'Descuentos' => Helpers::convertirvalorcorrecto($NuevoDescuentos),
                'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo)
            ]);
            //Si el saldo es mayor a 0 cambiar status de factura a POR COBRAR
            if($NuevoSaldo > Helpers::convertirvalorcorrecto(0)){
                Factura::where('Factura', $detalledocumento->Factura)
                        ->update([
                            'Status' => "POR COBRAR"
                        ]);
            }
            //colocar en cero cantidades nota proveedor documentos
            NotaClienteDocumento::where('Nota', $request->notadesactivar)
                                    ->where('Factura', $detalledocumento->Factura)
                                    ->update([
                                        'Descuento' => '0.000000',
                                        'Total' => '0.000000'
                                    ]);  
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "NOTAS CLIENTE";
        $BitacoraDocumento->Movimiento = $request->notadesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $NotaCliente->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($NotaCliente);
    }

    //obtener nota cliente
    public function notas_credito_clientes_obtener_nota_cliente(Request $request){
        $notacliente = NotaCliente::where('Nota', $request->notamodificar)->first();
        $almacen = 0;
        if($notacliente->Almacen != 0){
            $almacen = Almacen::where('Numero', $notacliente->Almacen)->first();
        }
        $cliente = Cliente::where('Numero', $notacliente->Cliente)->first();
        $regimenfiscal = c_RegimenFiscal::where('Clave', $notacliente->RegimenFiscal)->first();
        $tiporelacion = c_TipoRelacion::where('Clave', $notacliente->TipoRelacion)->first();
        $formapago = FormaPago::where('Clave', $notacliente->FormaPago)->first();
        $metodopago = MetodoPago::where('Clave', $notacliente->MetodoPago)->first();
        $usocfdi = UsoCFDI::where('Clave', $notacliente->UsoCfdi)->first();
        $residenciafiscal = Pais::where('Clave', $notacliente->ResidenciaFiscal)->first();
        //detalles
        $detallesnotacliente = NotaClienteDetalle::where('Nota', $request->notamodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallesnotacliente = NotaClienteDetalle::where('Nota', $request->notamodificar)->count();
        $filasdetallesnotacliente = '';
        if($numerodetallesnotacliente > 0){
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo="modificacion";
            foreach($detallesnotacliente as $dnc){
                    $producto = "";
                    $Existencia = 0;
                    if($notacliente->Almacen != 0){
                        $Existencia = Existencia::where('Codigo', $dnc->Codigo)->where('Almacen', $notacliente->Almacen)->first();
                        $producto = Producto::where('Codigo', $dnc->Codigo)->first();
                    }
                    $claveproductopartida = ClaveProdServ::where('Clave', $dnc->ClaveProducto)->first();
                    $claveunidadpartida = ClaveUnidad::where('Clave', $dnc->ClaveUnidad)->first();
                    //importante porque si se quiere hacer una divison con 0 marca ERROR
                    $porcentajeieps = 0;
                    $porcentajeretencioniva = 0;
                    $porcentajeretencionisr = 0;
                    $porcentajeretencionieps = 0;
                    if($dnc->Ieps > 0){
                        $porcentajeieps = ($dnc->Ieps * 100) / $dnc->ImporteDescuento;
                    }
                    if($dnc->IvaRetencion > 0){
                        $porcentajeretencioniva = ($dnc->IvaRetencion * 100) / $dnc->SubTotal;
                    }
                    if($dnc->IsrRetencion > 0){
                        $porcentajeretencionisr = ($dnc->IsrRetencion * 100) / $dnc->SubTotal;
                    }
                    if($dnc->IepsRetencion > 0){
                        $porcentajeretencionieps = ($dnc->IepsRetencion * 100) / $dnc->SubTotal;
                    }
                    if($dnc->Codigo == 'DPPP'){
                        $filasdetallesnotacliente= $filasdetallesnotacliente.
                        '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfila" onclick="eliminarfila('.$contadorfilas.')" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dnc->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dnc->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dnc->Codigo.'</td>'.         
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.$dnc->Descripcion.'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$dnc->Unidad.'" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Cantidad).'" data-parsley-min="0.1" data-parsley-max="1.0"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');">'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Ieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencioniva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->IvaRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionisr).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->IsrRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->IepsRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]"  value="0" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproductopartida->Clave.'" readonly data-parsley-length="[1, 20]"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$claveproductopartida->Nombre.'" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidadpartida->Clave.'" readonly data-parsley-length="[1, 5]"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$claveunidadpartida->Nombre.'" readonly></td>'.
                        '</tr>';
                        $tipodetalles = 'dppp';
                    }else{
                        $filasdetallesnotacliente= $filasdetallesnotacliente.
                        '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfila" onclick="eliminarfila('.$contadorfilas.')" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dnc->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dnc->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dnc->Codigo.'</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.$dnc->Descripcion.'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$dnc->Unidad.'" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Cantidad).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');revisarcantidadnotavscantidadfactura('.$contadorfilas.');">'.
                                '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dnc->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);">'.
                                '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                                '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'.
                                '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeieps).'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Ieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencioniva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->IvaRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionisr).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->IsrRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->IepsRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dnc->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]" value="0"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproductopartida->Clave.'" readonly data-parsley-length="[1, 20]"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$claveproductopartida->Nombre.'" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidadpartida->Clave.'" readonly data-parsley-length="[1, 5]"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$claveunidadpartida->Nombre.'" readonly></td>'.
                        '</tr>';
                        $tipodetalles = 'codigos';
                    }
                    $contadorproductos++;
                    $contadorfilas++;
            }
        }     
        //nota proveedor documentos
        $documentosnotacliente = NotaClienteDocumento::where('Nota', $request->notamodificar)->orderBy('Item', 'ASC')->get();
        $numerodocumentosnotacliente = NotaClienteDocumento::where('Nota', $request->notamodificar)->count();
        $filasdocumentosnotacliente = '';
        $arrayfacturas = array();
        if($numerodocumentosnotacliente > 0){
            $contadorfilasfacturas = 0;
            $tipo="modificacion";
            $descuentofacturas = 0;
            foreach($documentosnotacliente as $docnc){
                    array_push($arrayfacturas, $docnc->Factura);
                    $descuentofac = 0;
                    $descuentosfactura = NotaClienteDocumento::where('Nota', '<>', $request->notamodificar)->where('Factura', $docnc->Factura)->get();
                    foreach($descuentosfactura as $descuento){
                        $descuentofac = $descuentofac + $descuento->Descuento;
                    }
                    $factura = Factura::where('Factura', $docnc->Factura)->first();
                    $saldo = $factura->Saldo + $factura->Descuentos;
                    $filasdocumentosnotacliente= $filasdocumentosnotacliente.
                    '<tr class="filasfacturas" id="filafactura'.$contadorfilasfacturas.'">'.
                        '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfilafactura" onclick="eliminarfilafacturanotacliente('.$contadorfilasfacturas.')" >X</div><input type="hidden" class="form-control itemfacturapartida" name="itemfacturapartida[]" value="'.$docnc->Item.'" readonly><input type="hidden" class="form-control facturaagregadoen" name="facturaagregadoen[]" value="NA" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control facturaaplicarpartida" name="facturaaplicarpartida[]" value="'.$factura->Factura.'" readonly>'.$factura->Factura.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control fechafacturapartida" name="fechafacturapartida[]" value="'.$factura->Fecha.'" readonly>'.$factura->Fecha.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control uuidfacturapartida" name="uuidfacturapartida[]" value="'.$factura->UUID.'" readonly>'.$factura->UUID.'</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd totalpesosfacturapartida" name="totalpesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonosfacturapartida" name="abonosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Abonos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd notascreditofacturapartida" name="notascreditofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($descuentofac).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd descuentopesosfacturapartida" name="descuentopesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($docnc->Descuento).'" data-parsley-max="'.Helpers::convertirvalorcorrecto($saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilastablafacturas('.$contadorfilasfacturas.');" ></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd saldofacturapartida" name="saldofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '</tr>'; 
                    $descuentofacturas = $descuentofacturas+$docnc->Descuento;
            }
        } 
        $diferencia = $notacliente->Total - $descuentofacturas;
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($notacliente->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($notacliente->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($notacliente->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }
        $data = array(
            "notacliente" => $notacliente,
            "filasdetallesnotacliente" => $filasdetallesnotacliente,
            "numerodetallesnotacliente" => $numerodetallesnotacliente,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "tipodetalles" => $tipodetalles,
            "almacen" => $almacen,
            "cliente" => $cliente,
            "regimenfiscal" => $regimenfiscal,
            "tiporelacion" => $tiporelacion,
            "formapago" => $formapago,
            "metodopago" => $metodopago,
            "usocfdi" => $usocfdi,
            "residenciafiscal" => $residenciafiscal,
            "filasdocumentosnotacliente" => $filasdocumentosnotacliente,
            "numerodocumentosnotacliente" => $numerodocumentosnotacliente,
            "contadorfilasfacturas" => $contadorfilasfacturas,
            "arrayfacturas" => $arrayfacturas,
            "descuentofacturas" => Helpers::convertirvalorcorrecto($descuentofacturas),
            "diferencia" => Helpers::convertirvalorcorrecto($diferencia),
            "fecha" => Helpers::formatoinputdate($notacliente->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($notacliente->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($notacliente->Descuento),
            "ieps" => Helpers::convertirvalorcorrecto($notacliente->Ieps),
            "subtotal" => Helpers::convertirvalorcorrecto($notacliente->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($notacliente->Iva),
            "ivaretencion" => Helpers::convertirvalorcorrecto($notacliente->IvaRetencion),
            "isrretencion" => Helpers::convertirvalorcorrecto($notacliente->IsrRetencion),
            "iepsretencion" => Helpers::convertirvalorcorrecto($notacliente->IepsRetencion),
            "total" => Helpers::convertirvalorcorrecto($notacliente->Total),
            "tipocambio" => Helpers::convertirvalorcorrecto($notacliente->TipoCambio),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }

    //cambios
    public function notas_credito_clientes_guardar_modificacion(Request $request){
        ini_set('max_input_vars','10000' );
            $notacliente = $request->notaclientebd;
            $NotaCliente = NotaCliente::where('Nota', $notacliente)->first();
            //array detalles antes de modificacion
            $ArrayDetallesNotaAnterior = Array();
            $DetallesNotaAnterior = NotaClienteDetalle::where('Nota', $notacliente)->get();
            foreach($DetallesNotaAnterior as $detalle){
                array_push($ArrayDetallesNotaAnterior, $detalle->Nota.'#'.$detalle->Codigo.'#'.$detalle->Item);
            }
            //array detalles despues de modificacion
            $ArrayDetallesNotaNuevo = Array();
            foreach ($request->codigopartida as $key => $nuevocodigo){
                if($request->agregadoen [$key] == 'NA'){
                    array_push($ArrayDetallesNotaNuevo, $notacliente.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
                } 
            }  
            //diferencias entre arreglos
            $diferencias_arreglos = array_diff($ArrayDetallesNotaAnterior, $ArrayDetallesNotaNuevo);
            //iteramos las diferencias entre arreglos
            if(count($diferencias_arreglos) > 0){
                foreach($diferencias_arreglos as $eliminapartida){
                    $explode_d = explode("#",$eliminapartida);
                    $detallenota = NotaClienteDetalle::where('Nota', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->first();
                    //restar existencias a almacen principal
                    $RestarExistenciaAlmacen = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacen)->first();
                    $RestarExistenciaNuevaAlmacen = $RestarExistenciaAlmacen->Existencias - $detallenota->Cantidad;
                    Existencia::where('Codigo', $explode_d[1])
                                ->where('Almacen', $request->numeroalmacen)
                                ->update([
                                    'Existencias' => Helpers::convertirvalorcorrecto($RestarExistenciaNuevaAlmacen)
                                ]);
                    //eliminar detalle
                    $eliminardetalle= NotaClienteDetalle::where('Nota', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
                }
            }
            //array detalles documentos antes de modificacion
            $ArrayDetallesDocumentosNotaAnterior = Array();
            $DetallesDocumentosNotaAnterior = NotaClienteDocumento::where('Nota', $notacliente)->get();
            foreach($DetallesDocumentosNotaAnterior as $detalledocumento){
                array_push($ArrayDetallesDocumentosNotaAnterior, $detalledocumento->Nota.'#'.$detalledocumento->Factura.'#'.$detalledocumento->Item);
            }
            //array detalles documentos despues de modificacion
            $ArrayDetallesDocumentosNotaNuevo = Array();
            foreach ($request->facturaaplicarpartida as $key => $nuevafactura){
                if($request->facturaagregadoen [$key] == 'NA'){
                    array_push($ArrayDetallesDocumentosNotaNuevo, $notacliente.'#'.$nuevafactura.'#'.$request->itemfacturapartida [$key]);
                } 
            }  
            //diferencias entre arreglos
            $diferencias_arreglos = array_diff($ArrayDetallesDocumentosNotaAnterior, $ArrayDetallesDocumentosNotaNuevo);
            //iteramos las diferencias entre arreglos
            if(count($diferencias_arreglos) > 0){
                foreach($diferencias_arreglos as $eliminapartida){
                    $explode_d = explode("#",$eliminapartida);
                    $detalledocumentonota = NotaClienteDocumento::where('Nota', $explode_d[0])->where('Factura', $explode_d[1])->where('Item', $explode_d[2])->first();
                    //Regresar saldo y descuentos a la factura
                    $notaclientedocumento = NotaClienteDocumento::where('Nota', $explode_d[0])->where('Factura', $explode_d[1])->where('Item', $explode_d[2])->first();
                    $facturadocumento = Factura::where('Factura', $explode_d[1])->first();
                    $NuevoDescuentos = $facturadocumento->Descuentos - $notaclientedocumento->Descuento;
                    $NuevoSaldo = $facturadocumento->Saldo + $notaclientedocumento->Descuento;
                    Factura::where('Factura', $explode_d[1])
                    ->update([
                        'Descuentos' => Helpers::convertirvalorcorrecto($NuevoDescuentos),
                        'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo)
                    ]);
                    //eliminar detalle
                    $eliminardetalledocumento= NotaClienteDocumento::where('Nota', $explode_d[0])->where('Factura', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
                }
            }
            //modificar nota
            NotaCliente::where('Nota', $notacliente)
            ->update([
                'Cliente' => $request->numerocliente,
                'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                'Almacen' => $request->numeroalmacen, 
                'Importe' => $request->importe,
                'Descuento' => $request->descuento,
                'Ieps' => $request->ieps,  
                'SubTotal' => $request->subtotal,
                'Iva' => $request->iva,
                'IvaRetencion' => $request->retencioniva,
                'IsrRetencion' => $request->retencionisr,
                'IepsRetencion' => $request->retencionieps,
                'Total' => $request->total,
                'Moneda' => $request->moneda,
                'TipoCambio' => $request->pesosmoneda,
                'Obs' => $request->observaciones,
                'CondicionesDePago' => $request->condicionesdepago,
                'LugarExpedicion' => $request->lugarexpedicion,
                'RegimenFiscal' => $request->claveregimenfiscal,
                'TipoRelacion' => $request->clavetiporelacion,
                'Confirmacion' => $request->confirmacion,
                'FormaPago' => $request->claveformapago,
                'MetodoPago' => $request->clavemetodopago,
                'UsoCfdi' => $request->claveusocfdi,
                'ResidenciaFiscal' => $request->claveresidenciafiscal,
                'NumRegIdTrib' => $request->numeroregidtrib,
                'EmisorRfc' => $request->emisorrfc,
                'EmisorNombre' => $request->emisornombre,
                'ReceptorRfc' => $request->receptorrfc,
                'ReceptorNombre' => $request->receptornombre
            ]);
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "NOTAS CLIENTE";
            $BitacoraDocumento->Movimiento = $notacliente;
            $BitacoraDocumento->Aplicacion = "CAMBIO";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = $NotaCliente->Status;
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $request->periodohoy;
            $BitacoraDocumento->save();
            //detalles
            foreach ($request->codigopartida as $key => $codigopartida){  
                //if la partida se agrego en la modificacion se realiza un insert
                if($request->agregadoen [$key] == 'modificacion'){    
                    $contardetalles = NotaClienteDetalle::where('Nota', $notacliente)->count();
                    if($contardetalles > 0){
                        $item = NotaClienteDetalle::select('Item')->where('Nota', $notacliente)->orderBy('Item', 'DESC')->take(1)->get();
                        $ultimoitem = $item[0]->Item+1;
                    }else{
                        $ultimoitem = 1;
                    }
                    $NotaClienteDetalle=new NotaClienteDetalle;
                    $NotaClienteDetalle->Nota = $notacliente;
                    $NotaClienteDetalle->Cliente = $request->numerocliente;
                    $NotaClienteDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                    $NotaClienteDetalle->Codigo = $codigopartida;
                    $NotaClienteDetalle->Descripcion = $request->descripcionpartida [$key];
                    $NotaClienteDetalle->Unidad = $request->unidadpartida [$key];
                    $NotaClienteDetalle->Cantidad =  $request->cantidadpartida  [$key];
                    $NotaClienteDetalle->Precio =  $request->preciopartida [$key];
                    $NotaClienteDetalle->Importe = $request->importepartida [$key];
                    $NotaClienteDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                    $NotaClienteDetalle->Descuento = $request->descuentopesospartida [$key];
                    $NotaClienteDetalle->ImporteDescuento = $request->importedescuentopesospartida [$key];
                    $NotaClienteDetalle->Ieps = $request->trasladoiepspesospartida [$key];
                    $NotaClienteDetalle->SubTotal = $request->subtotalpartida [$key];
                    $NotaClienteDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                    $NotaClienteDetalle->Iva = $request->trasladoivapesospartida [$key];
                    $NotaClienteDetalle->IvaRetencion = $request->retencionivapesospartida [$key];
                    $NotaClienteDetalle->IsrRetencion = $request->retencionisrpesospartida [$key];
                    $NotaClienteDetalle->IepsRetencion = $request->retencioniepspesospartida [$key];
                    $NotaClienteDetalle->Total = $request->totalpesospartida [$key];
                    $NotaClienteDetalle->Costo = $request->costopartida [$key];
                    $NotaClienteDetalle->Partida = $request->partida [$key];
                    $NotaClienteDetalle->ClaveProducto = $request->claveproductopartida [$key];
                    $NotaClienteDetalle->ClaveUnidad = $request->claveunidadpartida [$key];
                    $NotaClienteDetalle->Item = $ultimoitem;
                    $NotaClienteDetalle->save();
                    if($codigopartida != 'DPPP'){
                        //sumar existencias del almacen 
                        $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->count();
                        if($ContarExistenciaAlmacen > 0){
                            $ExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                            $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias + $request->cantidadpartida [$key];
                            Existencia::where('Codigo', $codigopartida)
                                        ->where('Almacen', $request->numeroalmacen)
                                        ->update([
                                            'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                        ]);
                        }else{
                            $ExistenciaAlmacen = new Existencia;
                            $ExistenciaAlmacen->Codigo = $codigopartida;
                            $ExistenciaAlmacen->Almacen = $request->numeroalmacen;
                            $ExistenciaAlmacen->Existencias = $request->cantidadpartida [$key];
                            $ExistenciaAlmacen->save();
                        }
                    }
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar detalle
                    NotaClienteDetalle::where('Nota', $notacliente)
                    ->where('Item', $request->itempartida [$key])
                    ->update([
                        'Cliente' => $request->numerocliente,
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
                        'Costo' => $request->costopartida [$key],
                        'Partida' => $request->partida [$key],
                        'ClaveProducto' => $request->claveproductopartida [$key],
                        'ClaveUnidad' => $request->claveunidadpartida [$key]
                    ]);
                    if($codigopartida != 'DPPP'){
                        //restar existencias a almacen principal
                        $ExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                        $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias - $request->cantidadpartidadb [$key];
                        Existencia::where('Codigo', $codigopartida)
                                    ->where('Almacen', $request->numeroalmacen)
                                    ->update([
                                        'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                    ]);
                        //sumar existencias del almacen 
                        $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->count();
                        if($ContarExistenciaAlmacen > 0){
                            $ExistenciaAlmacen = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                            $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias + $request->cantidadpartida [$key];
                            Existencia::where('Codigo', $codigopartida)
                                        ->where('Almacen', $request->numeroalmacen)
                                        ->update([
                                            'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                        ]);
                        }else{
                            $ExistenciaAlmacen = new Existencia;
                            $ExistenciaAlmacen->Codigo = $codigopartida;
                            $ExistenciaAlmacen->Almacen = $request->numeroalmacen;
                            $ExistenciaAlmacen->Existencias = $request->cantidadpartida [$key];
                            $ExistenciaAlmacen->save();
                        }
                    }
                }    
            }
            //detalles documentos
            foreach ($request->facturaaplicarpartida as $key => $facturapartida){     
                //if la partida se agrego en la modificacion se realiza un insert
                if($request->facturaagregadoen [$key] == 'modificacion'){ 
                    $itemdocumento = NotaClienteDocumento::select('Item')->where('Nota', $notacliente)->orderBy('Item', 'DESC')->take(1)->get();
                    $ultimoitemdocumento = $itemdocumento[0]->Item+1;
                    $NotaClienteDocumento=new NotaClienteDocumento;
                    $NotaClienteDocumento->Nota = $notacliente;
                    $NotaClienteDocumento->Factura = $facturapartida;
                    $NotaClienteDocumento->UUID = $request->uuidfacturapartida [$key];
                    $NotaClienteDocumento->Descuento = $request->descuentopesosfacturapartida [$key];
                    $NotaClienteDocumento->Item = $ultimoitemdocumento;
                    $NotaClienteDocumento->save();
                    //Modificar Factura
                    Factura::where('Factura', $facturapartida)
                    ->update([
                        'Descuentos' => $request->descuentopesosfacturapartida [$key],
                        'Saldo' => $request->saldofacturapartida [$key]
                    ]);
                    //Si el saldo es igual a 0 liquidar factura
                    if($request->saldofacturapartida [$key] == Helpers::convertirvalorcorrecto(0)){
                        Factura::where('Factura', $facturapartida)
                                ->update([
                                    'Status' => "LIQUIDADO"
                                ]);
                    }
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar detalle
                    NotaClienteDocumento::where('Nota', $notacliente)
                    ->where('Item', $request->itemfacturapartida [$key])
                    ->update([
                        'Descuento' => $request->descuentopesosfacturapartida [$key]
                    ]);
                    //Regresar saldo y descuentos a la factura
                    $notaclientedocumento = NotaClienteDocumento::where('Nota', $notacliente)->where('Factura', $facturapartida)->where('Item', $request->itemfacturapartida [$key])->first();
                    $facturadocumento = Factura::where('Factura', $facturapartida)->first();
                    $NuevoDescuentos = $facturadocumento->Descuentos - $notaclientedocumento->Descuento;
                    $NuevoSaldo = $facturadocumento->Saldo + $notaclientedocumento->Descuento;
                    Factura::where('Factura', $facturapartida)
                    ->update([
                        'Descuentos' => Helpers::convertirvalorcorrecto($NuevoDescuentos),
                        'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo)
                    ]);
                    //Modificar Factura
                    Factura::where('Factura', $facturapartida)
                    ->update([
                        'Descuentos' => $request->descuentopesosfacturapartida [$key],
                        'Saldo' => $request->saldofacturapartida [$key]
                    ]);
                    //Si el saldo es igual a 0 liquidar factura
                    if($request->saldofacturapartida [$key] == Helpers::convertirvalorcorrecto(0)){
                        Factura::where('Factura', $facturapartida)
                                ->update([
                                    'Status' => "LIQUIDADO"
                                ]);
                    }
                } 
            }  
            return response()->json($NotaCliente);
    }

    //buscar folio on key up
    public function notas_credito_clientes_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = NotaCliente::where('Nota', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
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
    public function notas_credito_clientes_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $notascreditocliente = NotaCliente::whereIn('Nota', $request->arraypdf)->orderBy('Folio', 'ASC')->take(250)->get(); 
        }else{
            //$contrarecibos = ContraRecibo::where('Fecha', $request->anopdf)->get(); 
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $notascreditocliente = NotaCliente::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(250)->get();
        }
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
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($ncc->Total, 2, 'M.N.');
            $notaclientedocumento = NotaClienteDocumento::where('Nota', $ncc->Nota)->first();
            $comprobantetimbrado = Comprobante::where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->count();
            $comprobante = Comprobante::where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->first();
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
                "datadetalle" => $datadetalle,
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($ncc->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.notascreditoclientes.formato_pdf_notascreditoclientes', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //generacion de formato en PDF
    public function notas_credito_clientes_generar_pdfs_indiv($documento){
        $notascreditocliente = NotaCliente::where('Nota', $documento)->get(); 
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
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($ncc->Total, 2, 'M.N.');
            $notaclientedocumento = NotaClienteDocumento::where('Nota', $ncc->Nota)->first();
            $comprobantetimbrado = Comprobante::where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->count();
            $comprobante = Comprobante::where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->first();
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
                "datadetalle" => $datadetalle,
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($ncc->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.notascreditoclientes.formato_pdf_notascreditoclientes', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
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
            'emailpara' => $cliente->Email1
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
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($ncc->Total, 2, 'M.N.');
            $notaclientedocumento = NotaClienteDocumento::where('Nota', $ncc->Nota)->first();
            $comprobantetimbrado = Comprobante::where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->count();
            $comprobante = Comprobante::where('Folio', '' . $ncc->Folio . '')->where('Serie', '' . $ncc->Serie . '')->first();
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
                "datadetalle" => $datadetalle,
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($ncc->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.notascreditoclientes.formato_pdf_notascreditoclientes', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            //enviar correo electrónico	
            $nombre = 'Receptor envio de correos';
            $receptor = $request->emailpara;
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailasunto;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $correos, $asunto, $pdf, $emaildocumento) {
                $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                        ->cc($correos)
                        ->subject($asunto)
                        ->attachData($pdf->output(), "NotaCreditoClienteNo".$emaildocumento.".pdf");
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
    public function notas_credito_clientes_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new NotasCreditoClientesExport($this->campos_consulta,$request->periodo), "notascreditoclientes-".$request->periodo.".xlsx");   
    }
    //configuracion tabla
    public function notas_credito_clientes_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'NotasCreditoCliente')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('notas_credito_clientes');
    }
}
