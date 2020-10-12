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

class EmpresaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
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
}
