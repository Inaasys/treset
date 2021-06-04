<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DB;
use DataTables;
use App\FolioComprobantePago;

class FolioComprobantePagoController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }
    
    public function folios_comprobantes_pagos(){
        $mayusculas_sistema = Helpers::mayusculas_sistema();
        return view('catalogos.foliosfiscales.folioscomprobantespagos', compact('mayusculas_sistema'));
    }
    public function folios_comprobantes_pagos_obtener(Request $request){
        if($request->ajax()){
            $data = FolioComprobantePago::orderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos('.$data->Numero.')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar('.$data->Numero.')">Bajas</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="predeterminarfolio('.$data->Numero.')">Predeterminar Folio</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        /*$botoncambios =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos('.$data->Numero.')"><i class="material-icons">mode_edit</i></div> '; 
                        $botonbajas =      '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar('.$data->Numero.')"><i class="material-icons">cancel</i></div> ';
                        $botonpredeterminar = '<div class="btn bg-green btn-xs waves-effect" data-toggle="tooltip" title="Predeterminar Folio" onclick="predeterminarfolio('.$data->Numero.')"><i class="material-icons">check</i></div> ';
                        if($data->Status == 'ALTA'){
                            $operaciones =    $botonpredeterminar.$botoncambios.$botonbajas;
                        }else{
                            $operaciones = '';
                            //$operaciones =    '<div class="btn bg-green btn-xs waves-effect" onclick="desactivar(\''.$data->Numero .'\')">Altas</div>';
                        } */
                        return $operaciones;
                    })
                    ->setRowClass(function ($data) {
                        return $data->Status == 'ALTA' ? '' : 'bg-orange';
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }

    //obtener ultimo numero
    public function folios_comprobantes_pagos_obtener_ultimo_numero(Request $request){
        $id = Helpers::ultimoidtabla('App\FolioComprobantePago');
        return response()->json($id);
    }

    //predeterminar folio
    public function folios_comprobantes_pagos_predeterminar(Request $request){
        //predeterminar folio
        FolioComprobantePago::where('Numero', $request->numerofolio)
        ->update([
            'Predeterminar' => '+'
        ]);
        //vaciar predeterminar de folio anterior
        FolioComprobantePago::where('Numero', '<>', $request->numerofolio)
        ->update([
            'Predeterminar' => ''
        ]);        
    }

    //altas
    public function folios_comprobantes_pagos_guardar(Request $request){

    }

    //bajas
    public function folios_comprobantes_pagos_alta_o_baja(Requesr $request){
        $numerofolio=$request->numerofolio;
	    $FolioComprobantePago = FolioComprobantePago::where('Numero', $numerofolio )->first();
	    if($FolioComprobantePago->Status == 'ALTA'){
            FolioComprobantePago::where('Numero', $numerofolio)
            ->update([
                'Status' => 'BAJA'
            ]);  
	    }else{
            FolioComprobantePago::where('Numero', $numerofolio)
            ->update([
                'Status' => 'ALTA'
            ]);
        }
	    return response()->json($FolioComprobantePago);
    }

    //obtener folio
    public function folios_comprobantes_pagos_obtener_folio(Request $request){
        $FolioComprobantePago = FolioComprobantePago::where('Numero', $request->numerofolio)->first();
        $data = array(
            'FolioComprobantePago' => $FolioComprobantePago
        );
        return response()->json($data);
    }

    //cambios
    public function folios_comprobantes_pagos_guardar_modificacion(Request $request){

    }

}
