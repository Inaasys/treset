<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientesExport;
use App\Cliente;
use App\Pais; 
use App\CodigoPostal;
use App\Estado;
use App\Municipio;
use App\Agente;
use App\FormaPago;
use App\MetodoPago;
use App\UsoCFDI;
use App\Empresa;
use App\Marca;
use App\ProductoPrecio;
use App\Producto;
use App\Configuracion_Tabla;
use App\VistaCliente;

class ClienteController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function clientes(){
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Clientes', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('clientes_guardar_configuracion_tabla');
        return view('catalogos.clientes.clientes', compact('configuracion_tabla','rutaconfiguraciontabla'));
    }
    //obtener todos los registros
    public function clientes_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Clientes', Auth::user()->id);
            $data = VistaCliente::select($configuraciones_tabla['campos_consulta']);
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
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos('.$data->Numero.')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar('.$data->Numero.')">Bajas</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Credito', function($data){ return $data->Credito; })
                    ->addColumn('Saldo', function($data){ return $data->Saldo; })
                    ->addColumn('ComisionAgente', function($data){ return $data->ComisionAgente; })
                    ->setRowClass(function ($data) {
                        return $data->Status == 'ALTA' ? '' : 'bg-orange';
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener codigo
    public function clientes_buscar_rfc_en_tabla(Request $request){
        $existerfc = Cliente::where('Rfc', $request->rfc)->count();
        return response()->json($existerfc);
    }
    //obtener ultimo numero de tabla
    public function clientes_obtener_ultimo_numero(){
        $id = Helpers::ultimoidtabla('App\Cliente');
        return response()->json($id);
    }
    //obtener paises
    public function clientes_obtener_paises(Request $request){
        if($request->ajax()){
            $data = Pais::orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarpais(\''.$data->Nombre .'\',\''.$data->Clave.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }  
    }
    //obtener pais por clave
    public function clientes_obtener_clave_pais_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existepais = Pais::where('Clave', $request->clavepais)->count();
        if($existepais > 0){
            $pais = Pais::where('Clave', $request->clavepais)->first();
            $clave = $pais->Clave;
            $nombre = $pais->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }

    //obtener estados
    public function clientes_obtener_estados(Request $request){
        if($request->ajax()){
            if ($request->numeropais != '') {
                $pais = Pais::where('Numero', $request->numeropais)->first();
                $data = Estado::where('Pais', $pais->Clave )->orderBy("Numero", "ASC")->get();
            }else{
                $data = Estado::query();
            }
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarestado(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener codigos postales
    public function clientes_obtener_codigos_postales(Request $request){
        if($request->ajax()){
            if ($request->claveestado != '') {
                $data = CodigoPostal::where('Estado', $request->claveestado )->get();
            }else{
                $data = CodigoPostal::query();
            }
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcodigopostal(\''.$data->Clave .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }  
    }  
    
    //obtener datos direccion
    public function clientes_obtener_datos_direccion(Request $request){
        $cp = CodigoPostal::where('Clave', $request->Clave )->first();
        $mun = Municipio::where('Clave', $cp->Municipio)->first();
        $est = Estado::where('Clave', $mun->Estado)->first();
        $pais = Pais::where('Clave', $est->Pais)->first();
        $datos = array(
            'cp' => $cp,
            'mun' => $mun,
            'est' => $est,
            'pais' => $pais
        );
        return response()->json($datos);
    }

    //obtener municipios
    public function clientes_obtener_municipios(Request $request){
        if($request->ajax()){
            if ($request->claveestado != '') {
                $data = Municipio::where('Estado', $request->claveestado )->orderBy("Numero", "ASC")->get();
            }else{
                $data = Municipio::query();
            }
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmunicipio(\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }  
    }   
    //obtener agentes
    public function clientes_obtener_agentes(Request $request){
        if($request->ajax()){
            $data = Agente::where('Status', 'ALTA')->orderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaragente('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }  
    }
    ///obtener agente por numero
    public function clientes_obtener_agente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existeagente = Agente::where('Numero', $request->agente)->count();
        if($existeagente > 0){
            $agente = Agente::where('Numero', $request->agente)->first();
            $numero = $agente->Numero;
            $nombre = $agente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }
    //obtener formas de pago
    public function clientes_obtener_formas_pago(Request $request){
        if($request->ajax()){
            $data = FormaPago::orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarformapago(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener forma pago por clave
    public function clientes_obtener_formapago_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeformapago = FormaPago::where('Clave', $request->claveformapago)->count();
        if($existeformapago > 0){
            $formapago = FormaPago::where('Clave', $request->claveformapago)->first();
            $clave = $agente->Clave;
            $nombre = $agente->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }
    //obtener metodos de pago
    public function clientes_obtener_metodos_pago(Request $request){
        if($request->ajax()){
            $data = MetodoPago::orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmetodopago(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    } 
    //obtener metodo por clave
    public function clientes_obtener_metodopago_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existemetodopago = MetodoPago::where('Clave', $request->clavemetodopago)->count();
        if($existemetodopago > 0){
            $metodopago = MetodoPago::where('Clave', $request->clavemetodopago)->first();
            $clave = $metodopago->Clave;
            $nombre = $metodopago->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }
    //obtener uso cfdi
    public function clientes_obtener_uso_cfdi(Request $request){
        if($request->ajax()){
            $data = UsoCFDI::orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarusocfdi(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }   
    //obtener uso cfdi por clave
    public function clientes_obtener_usocfdi_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeusocfdi = UsoCFDI::where('Clave', $request->claveusocfdi)->count();
        if($existeusocfdi > 0){
            $usocfdi = UsoCFDI::where('Clave', $request->claveusocfdi)->first();
            $clave = $usocfdi->Clave;
            $nombre = $usocfdi->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }
    
    //obtener productos
    public function clientes_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $data = DB::table('Productos as t')
            ->leftJoin('Marcas as m', 'm.Numero', '=', 't.Marca')
            ->leftJoin(DB::raw("(select codigo, sum(existencias) as existencias from existencias group by codigo) as e"),
            function($join){
                $join->on("e.codigo","=","t.codigo");
            })
            ->select('t.Codigo as Codigo', 't.Producto as Producto', 't.Ubicacion as Ubicacion', 'e.Existencias as Existencias', 't.Costo as Costo', 't.SubTotal as SubTotal', 't.Marca as Marca', 't.Status as Status')
            ->where('t.Codigo', 'like', '%' . $codigoabuscar . '%')
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Existencias', function($data){
                        $existencias = Helpers::convertirvalorcorrecto($data->Existencias);
                        return $existencias;
                    })
                    ->addColumn('Costo', function($data){
                        $costo = Helpers::convertirvalorcorrecto($data->Costo);
                        return $costo;
                    })
                    ->addColumn('SubTotal', function($data){
                        $subtotal = Helpers::convertirvalorcorrecto($data->SubTotal);
                        return $subtotal;
                    })
                    ->rawColumns(['operaciones', 'Existencias', 'Costo', 'SubTotal'])
                    ->make(true);
        } 
    }     
    //obtenr datos de prudcto para agregar fila
    public function clientes_obtener_datos_producto_agregar_fila(Request $request){
        $codigo = '';
        $nombreproducto = '';
        $existeproducto = Producto::where('Codigo', $request->codigoabuscar)->where('Status', 'ALTA')->count();
        if($existeproducto > 0){
            $producto = Producto::where('Codigo', $request->codigoabuscar)->where('Status', 'ALTA')->first();
            $codigo = $producto->Codigo;
            $nombreproducto = htmlspecialchars($producto->Producto, ENT_QUOTES);
        }
        $data = array(
            'existeproducto' => $existeproducto,
            'codigo' => $codigo,
            'nombreproducto' => $nombreproducto,
        );
        return response()->json($data);

    }
    //guardar en catalogo
    public function clientes_guardar(Request $request){
	    $rfc=$request->rfc;
	    /*$ExisteCliente = Cliente::where('Rfc', $rfc )->first();
	    if($ExisteCliente == true){
	        $Cliente = 1;
	    }else{*/
            //obtener el ultimo id de la tabla
            $id = Helpers::ultimoidtabla('App\Cliente');
		    $Cliente = new Cliente;
		    $Cliente->Numero=$id;
		    $Cliente->Nombre=$request->nombre;
		    $Cliente->Rfc=$request->rfc;
		    $Cliente->Calle=$request->calle;
            $Cliente->noExterior=$request->noexterior;
		    $Cliente->noInterior=$request->nointerior;
		    $Cliente->Colonia=$request->colonia;
		    $Cliente->Localidad=$request->localidad;
		    $Cliente->Referencia=$request->referencia;
            $Cliente->Pais=$request->clavepais;
            $Cliente->Estado=$request->estado;
            $Cliente->Municipio=$request->municipio;  
            $Cliente->CodigoPostal=$request->codigopostal;
            $Cliente->Plazo=$request->plazo;
            $Cliente->Agente=$request->agente;
            $Cliente->FormaPago=$request->claveformapago;
            $Cliente->MetodoPago=$request->clavemetodopago;
            $Cliente->UsoCfdi=$request->claveusocfdi;
            $Cliente->Tipo=$request->tipo;
            $Cliente->Credito=$request->creditomaximo;
            $Cliente->Contacto=$request->contacto;
            $Cliente->Telefonos=$request->telefonos;
            $Cliente->Celular=$request->celular;
            $Cliente->Email1=$request->email1;
            $Cliente->Email2=$request->email2;
            $Cliente->Email3=$request->email3;
            $Cliente->Cuenta=$request->cuentaref;
            $Cliente->CuentaServicio=$request->cuentaser;
            $Cliente->Anotaciones=$request->anotaciones;
            $Cliente->Status='ALTA';
            Log::channel('cliente')->info('Se registro un nuevo cliente: '.$Cliente.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
		    $Cliente->save();
      	//}
    	return response()->json($Cliente); 
    }
    //dar de baja o alta en catalogo
    public function clientes_alta_o_baja(Request $request){
        $numerocliente=$request->numerocliente;
	    $Cliente = Cliente::where('Numero', $numerocliente )->first();
	    if($Cliente->Status == 'ALTA'){
           $Cliente->Status = 'BAJA';
           Log::channel('cliente')->info('El cliente fue dado de baja: '.$Cliente.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
	    }else{
	       $Cliente->Status = 'ALTA';
           Log::channel('cliente')->info('El cliente fue dado de alta: '.$Cliente.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }
	    $Cliente->save();
	    return response()->json($Cliente);
    }
    //obtener datos del catalogo
    public function clientes_obtener_cliente(Request $request){
        $cliente = Cliente::where('Numero', $request->numerocliente)->first();
        $clavepais = $cliente->Pais;
        $pais = Pais::where('Clave', $clavepais)->first();
        $nombreestado = $cliente->Estado;
        $estado = Estado::where('Clave', mb_strtolower($nombreestado))->first(); 
        $nombremunicipio = $cliente->Municipio;
        $municipio = Municipio::where('Estado', mb_strtolower($nombreestado))->where('Nombre', 'like', '%' . mb_strtolower($nombremunicipio) . '%')->first(); 
        $clavecodigopostal = $cliente->CodigoPostal;
        $codigopostal = CodigoPostal::where('Clave', $clavecodigopostal)->first();
        $claveformadepago = $cliente->FormaPago;
        $formadepago = FormaPago::where('Clave', $claveformadepago)->first();
        $clavemetododepago = $cliente->MetodoPago;
        $metododepago = MetodoPago::where('Clave', $clavemetododepago)->first();
        $claveusocfdi = $cliente->UsoCfdi;
        $usocfdi = UsoCFDI::where('Clave', $claveusocfdi)->first();
        $claveagente = $cliente->Agente;
        $agente = Agente::where('Numero', $claveagente)->first();
        //tab utilidades
        $utilidadesmarcas = Marca::where('Status', 'ALTA')->get();
        $numerofilasutilidadesmarcas = Marca::where('Status', 'ALTA')->count();
        if($numerofilasutilidadesmarcas > 0){
            $filasutilidadesmarcas  = '';
            $contadorutilidadesmarcas  = 0;
            foreach($utilidadesmarcas as $um){
                $filasutilidadesmarcas= $filasutilidadesmarcas.
                '<tr>'.
                    '<td><input type="hidden" name="numeromarca[]"  value="'.$um->Numero.'" readonly>'.$um->Numero.'</td>'.
                    '<td><input type="hidden" name="nombremarca[]"  value="'.$um->Nombre.'" readonly>'.$um->Nombre.'</td>'.
                    '<td><input type="hidden" name="utilidad1marca[]" value="'.Helpers::convertirvalorcorrecto($um->Utilidad1).'" readonly>'.Helpers::convertirvalorcorrecto($um->Utilidad1).'</td>'.
                    '<td><input type="hidden" name="utilidad1marca[]" value="'.Helpers::convertirvalorcorrecto($um->Utilidad2).'" readonly>'.Helpers::convertirvalorcorrecto($um->Utilidad2).'</td>'.
                    '<td><input type="hidden" name="utilidad1marca[]" value="'.Helpers::convertirvalorcorrecto($um->Utilidad3).'" readonly>'.Helpers::convertirvalorcorrecto($um->Utilidad3).'</td>'.
                    '<td><input type="hidden" name="utilidad1marca[]" value="'.Helpers::convertirvalorcorrecto($um->Utilidad4).'" readonly>'.Helpers::convertirvalorcorrecto($um->Utilidad4).'</td>'.
                    '<td><input type="hidden" name="utilidad1marca[]" value="'.Helpers::convertirvalorcorrecto($um->Utilidad5).'" readonly>'.Helpers::convertirvalorcorrecto($um->Utilidad5).'</td>'.
                    '<td><input type="text" name="utilidadmarcaseleccionada[]" ></td><td><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" name="dctoutilidadmarca[]" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);"></td>'.
                '</tr>';
                $contadorutilidadesmarcas++;
            }
        }else{
            $filasutilidadesmarcas = '';
        }
        //tab precios productos
        $preciosproductos = ProductoPrecio::where('Cliente', $request->numerocliente)->get();
        $numerofilaspreciosproductos = ProductoPrecio::where('Cliente', $request->numerocliente)->count();
        if($numerofilaspreciosproductos > 0){
            $filaspreciosproductos = '';
            $contadorpreciosproductos = 0;
            foreach($preciosproductos as $pp){
                $p = Producto::where('Codigo', $pp->Codigo)->first();
                $filaspreciosproductos= $filaspreciosproductos.
                '<tr class="filaspreciosproductos" id="filaprecioproducto'.$contadorpreciosproductos.'">'.
                    '<td><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosproductos('.$contadorpreciosproductos.')">X</div></td>'.
                    '<td><input type="hidden" name="codigoproducto[]" value="'.$pp->Codigo.'" readonly>'.$pp->Codigo.'</td>'.
                    '<td><input type="hidden" name="nombreproducto[]" value="'.$p->Producto.'" readonly>'.$p->Producto.'</td>'.
                    '<td><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'"" name="subtotalprecioproducto[]" required value="'.Helpers::convertirvalorcorrecto($pp->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);"></td>'.
                '</tr>';
                $contadorpreciosproductos++;
            }
        }else{
            $filaspreciosproductos = '';
        }        
        $data = array(
            "cliente" => $cliente,
            "pais" => $pais,
            "estado" => $estado,
            "municipio" => $municipio,
            "codigopostal" => $codigopostal,
            "formadepago" => $formadepago,
            "metododepago" => $metododepago,
            "usocfdi" => $usocfdi,
            "agente" => $agente,
            "filasutilidadesmarcas" => $filasutilidadesmarcas,
            "filaspreciosproductos" => $filaspreciosproductos,
            "numerofilaspreciosproductos" => $numerofilaspreciosproductos,
            "credito" => Helpers::convertirvalorcorrecto($cliente->Credito)
        );
        return response()->json($data);
    }
    //modificar en catalogo
    public function clientes_guardar_modificacion(Request $request){
        $rfc=$request->rfc;
        $numerocliente = $request->numero;
	    /*$ExisteCliente = Cliente::where('Numero','<>', $numerocliente)->where('Rfc', $rfc )->first();
	    if($ExisteCliente == true){
            $Cliente = 1;
	    }else{*/
            $Cliente = Cliente::where('Numero', $numerocliente )->first();
            Cliente::where('Numero', $numerocliente)
            ->update([
                'Nombre' => $request->nombre,
                'Rfc' => $request->rfc,
                'Calle' => $request->calle,
                'noExterior' => $request->noexterior,
                'noInterior' => $request->nointerior,
                'Colonia' => $request->colonia,
                'Localidad' => $request->localidad,
                'Referencia' => $request->referencia,
                'Pais' => $request->clavepais,
                'Estado' => $request->estado,
                'Municipio' => $request->municipio,  
                'CodigoPostal' => $request->codigopostal,
                'Plazo' => $request->plazo,
                'Agente' => $request->agente,
                'FormaPago' => $request->claveformapago,
                'MetodoPago' => $request->clavemetodopago,
                'UsoCfdi' => $request->claveusocfdi,
                'Tipo' => $request->tipo,
                'Credito' => $request->creditomaximo,
                'Contacto' => $request->contacto,
                'Telefonos' => $request->telefonos,
                'Celular' => $request->celular,
                'Email1' => $request->email1,
                'Email2' => $request->email2,
                'Email3' => $request->email3,
                'Cuenta' => $request->cuentaref,
                'CuentaServicio' => $request->cuentaser,
                'Anotaciones' => $request->anotaciones
            ]);
            /*
            //modificar registro
            $Cliente = Cliente::where('Numero', $numerocliente )->first();
		    $Cliente->Nombre=$request->nombre;
		    $Cliente->Rfc=$request->rfc;
		    $Cliente->Calle=$request->calle;
            $Cliente->noExterior=$request->noexterior;
		    $Cliente->noInterior=$request->nointerior;
		    $Cliente->Colonia=$request->colonia;
		    $Cliente->Localidad=$request->localidad;
		    $Cliente->Referencia=$request->referencia;
            $Cliente->Pais=$request->clavepais;
            $Cliente->Estado=$request->estado;
            $Cliente->Municipio=$request->municipio;  
            $Cliente->CodigoPostal=$request->codigopostal;
            $Cliente->Plazo=$request->plazo;
            $Cliente->Agente=$request->agente;
            $Cliente->FormaPago=$request->claveformapago;
            $Cliente->MetodoPago=$request->clavemetodopago;
            $Cliente->UsoCfdi=$request->claveusocfdi;
            $Cliente->Tipo=$request->tipo;
            $Cliente->Credito=$request->creditomaximo;
            $Cliente->Contacto=$request->contacto;
            $Cliente->Telefonos=$request->telefonos;
            $Cliente->Celular=$request->celular;
            $Cliente->Email1=$request->email1;
            $Cliente->Email2=$request->email2;
            $Cliente->Email3=$request->email3;
            $Cliente->Cuenta=$request->cuentaref;
            $Cliente->CuentaServicio=$request->cuentaser;
            $Cliente->Anotaciones=$request->anotaciones;
            */
            Log::channel('cliente')->info('Se modifico el cliente: '.$Cliente.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
            //$Cliente->save();
            //Tabla Precios Productos
            $eliminarpreciosproductos = ProductoPrecio::where('Cliente', $numerocliente)->forceDelete();
            if($request->numerofilaspreciosproducto > 0){
                $contador = 1;
                foreach ($request->codigoproducto as $key => $codigoproducto){
                    //alta tabla detalle productos precios
                    $ProductoPrecio=new ProductoPrecio;
                    $ProductoPrecio->Codigo = $codigoproducto;
                    $ProductoPrecio->Cliente = $numerocliente;
                    $ProductoPrecio->Precio = $request->subtotalprecioproducto [$key];
                    $ProductoPrecio->Item = $contador;
                    $ProductoPrecio->save();	
                    $contador++;
                }   
            } 
      	//}
    	return response()->json($Cliente); 
    }
    //exportar a excel
    public function clientes_exportar_excel(){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Clientes', Auth::user()->id);
        return Excel::download(new ClientesExport($configuraciones_tabla['campos_consulta']), "clientes.xlsx");        
    }
    //guardar configuracion tabla
    public function clientes_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Clientes', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'Clientes')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='Clientes';
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
        return redirect()->route('clientes');
    }
}
