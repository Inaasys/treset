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
use App\Empresa;
use App\Pais;
use App\Estado;
use App\Municipio;
use App\c_RegimenFiscal;
use App\CodigoPostal;
use App\Moneda;
use Facturapi\Facturapi;

class EmpresaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //API FACTURAPI 
        $this->facturapi = new Facturapi( config('app.keygeneralfacturapi') ); //
    }
    public function utilerias_empresa_guardar_modificacion(Request $request){
        $mover_a_carpeta="logotipo_empresa";
        $logotipo = $request->logotipo;
        $nombre_original = $logotipo->getClientOriginalName();
        $nuevo_nombre_archivo = time().$nombre_original;
        //guardar xml en public/xml_cargados
        $logotipo->move($mover_a_carpeta, $nuevo_nombre_archivo);
        $Empresa = Empresa::where('Numero', 1)->first();
        //eliminar logotipo anterior
        //$eliminar_logotipo_anterior = public_path().'/logotipo_empresa/'.$Empresa->Logo;
        //unlink($eliminar_logotipo_anterior);
        $Empresa->Logo = $nuevo_nombre_archivo;
        $Empresa->save();
        return response()->json($logotipo);
    }
    public function empresa(){
        return view('empresa.empresa');
    }
    //obtener paises
    public function empresa_obtener_paises(Request $request){
        if($request->ajax()){
            $data = Pais::orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarpais('.$data->Numero.',\''.$data->Clave .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }  
    }
    //obtener estados
    public function empresa_obtener_estados(Request $request){
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
    //obtener municipios
    public function empresa_obtener_municipios(Request $request){
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



    //obtener lugares expedicion
    public function empresa_obtener_lugares_expedicion(Request $request){
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
    public function empresa_obtener_regimenes_fiscales(Request $request){
        if($request->ajax()){
            $data = c_RegimenFiscal::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarregimenfiscal(\''.$data->Clave .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }  
    } 
    //obtener monedas
    public function empresa_obtener_monedas(Request $request){
        if($request->ajax()){
            $data = Moneda::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmoneda(\''.$data->Clave .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }  
    } 

    //guardar cambios en domicilio fiscal
    public function empresa_guardar_modificacion_domicilio_fiscal(Request $request){
        $Empresa = Empresa::where('Numero', 1)->first();
        Empresa::where('Numero', 1)
        ->update([
            'Nombre' => $request->nombrecomercialempresa,
            'Empresa' => $request->razonsocialempresa,
            'Calle' => $request->calleempresa,
            'NoExterior' => $request->noexteriorempresa,
            'NoInterior' => $request->nointeriorempresa,
            'Colonia' => $request->coloniaempresa,
            'Localidad' => $request->localidadempresa,
            'Referencia' => $request->referenciaempresa,
            'Pais' => $request->empresanombrepais,
            'Estado' => $request->empresanombreestado,
            'Municipio' => $request->empresanombremunicipio,
            'Telefonos' => $request->telefonosempresa,
            'Email' => $request->emailempresa
        ]);
        return response()->json($request->all());
    }
    //guardar cambios lugar expedicion
    public function empresa_guardar_modificacion_lugar_expedicion(Request $request){
        $Empresa = Empresa::where('Numero', 1)->first();
        Empresa::where('Numero', 1)
        ->update([
            'LugarExpedicion' => $request->empresalugarexpedicion,
            'RegimenFiscal' => $request->empresaregimenfiscal,
            'Moneda' => $request->empresamoneda
        ]);
        return response()->json($request->all());
    }
    //guar cambiar configurar
    public function empresa_guardar_modificacion_configurar(Request $request){
        $Empresa = Empresa::where('Numero', 1)->first();
        Empresa::where('Numero', 1)
        ->update([
            'Numero_Decimales' => $request->numerodecimalessistema,
            'Numero_Decimales_En_Documentos' => $request->numerodecilamesdocumentospdfsistema,
            'Mayusculas_Sistema' => $request->utilizarmayusculasistema
        ]);
        return response()->json($request->all());
    }
    //guar cambios en temas y logo
    public function empresa_guardar_modificacion_logo_y_tema(Request $request){
        if($request->logotipo != "undefined") {
            $mover_a_carpeta="logotipo_empresa";
            $logotipo = $request->logotipo;
            $nombre_original = $logotipo->getClientOriginalName();
            $nuevo_nombre_archivo = time().$nombre_original;
            //guardar xml en public/xml_cargados
            $logotipo->move($mover_a_carpeta, $nuevo_nombre_archivo);
            $Empresa = Empresa::where('Numero', 1)->first();
            //eliminar logotipo anterior
            $eliminar_logotipo_anterior = public_path().'/logotipo_empresa/'.$Empresa->Logo;
            if (file_exists($eliminar_logotipo_anterior)) {
                unlink($eliminar_logotipo_anterior);
            }
        }else{
            $Empresa = Empresa::where('Numero', 1)->first();
            $nuevo_nombre_archivo = $Empresa->Logo;
        }
        Empresa::where('Numero', 1)
        ->update([
            'Logo' => $nuevo_nombre_archivo,
            'background_navbar' => $request->selectcolornavbar,
            'background_forms_and_modals' => $request->selectcolorformsandmodals,
            'background_tables' => $request->selectcolortables
        ]);
        return response()->json($nuevo_nombre_archivo);
    }
    //guardar registro de empresa en facturapi
    public function empresa_guardar_registro_empresa_facturapi(Request $request){
        $registro_empresa_facturapi = $this->facturapi->Organizations->create(array(
            "name" => $this->empresa->Empresa
        ));
        //colocar id de facturapi
        Empresa::where('Numero', 1)
        ->update([
            'IdFacturapi' => $registro_empresa_facturapi->id
        ]);
        //actualizar organizacion facturapi
        $actualizar_empresa = $this->facturapi->Organizations->updateLegal(
            $registro_empresa_facturapi->id, array(
              "name" => $this->empresa->Empresa,
              "legal_name" => $this->empresa->Empresa,
              "tax_system" => config('app.regimenfiscal'),
              "phone" => config('app.telefonosempresa'),
              "address" => array(
                "exterior" => config('app.noexteriorempresa'),
                "interior" => config('app.nointeriorempresa'),
                "zip" => config('app.lugarexpedicion'),
                "neighborhood" => config('app.calleempresa'),
                "city" => config('app.localidadempresa'),
                "municipality" => config('app.municipioempresa'),
                "state" => config('app.estadoempresa'),
                "country" => config('app.paisempresa')
              )
            )
          );

        return response()->json($registro_empresa_facturapi);
    }
}
