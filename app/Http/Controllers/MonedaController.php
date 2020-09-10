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

class MonedaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function obtener_valor_dolar_dof(Request $request){
        $valor_dolar = Helpers::obtener_valor_dolar_por_fecha_diario_oficial_federacion($request->fechadolar);
        return response()->json($valor_dolar);
    }
}
