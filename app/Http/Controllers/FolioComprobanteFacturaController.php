<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DB;
use DataTables;
use App\FolioComprobanteFactura;

class FolioComprobanteFacturaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }
    
    public function folios_comprobantes_facturas(){
        $mayusculas_sistema = Helpers::mayusculas_sistema();
        return view('catalogos.foliosfiscales.folioscomprobantesfacturas', compact('mayusculas_sistema'));
    }
    public function folios_comprobantes_facturas_obtener(Request $request){
        if($request->ajax()){
            $data = FolioComprobanteFactura::orderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        if($data->Status == 'ALTA'){
                            $boton =    '<div class="btn bg-amber btn-xs waves-effect" onclick="obtenerdatos(\''.$data->Numero .'\')">Cambios</div> '. 
                                        '<div class="btn bg-red btn-xs waves-effect" onclick="desactivar(\''.$data->Numero .'\')">Bajas</div>';
                        }else{
                            $boton = '';
                            //$boton =    '<div class="btn bg-green btn-xs waves-effect" onclick="desactivar(\''.$data->Numero .'\')">Altas</div>';
                        } 
                        return $boton;
                    })
                    ->setRowClass(function ($data) {
                        return $data->Status == 'ALTA' ? '' : 'bg-orange';
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
}
