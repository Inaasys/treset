<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DB;
use DataTables;
use App\FolioComprobanteNota;

class FolioComprobanteNotaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }
    
    public function folios_comprobantes_notas(){
        $mayusculas_sistema = Helpers::mayusculas_sistema();
        return view('catalogos.foliosfiscales.folioscomprobantesnotas', compact('mayusculas_sistema'));
    }
    public function folios_comprobantes_notas_obtener(Request $request){
        if($request->ajax()){
            $data = FolioComprobanteNota::query();
            return DataTables::of($data)
                    ->order(function ($query) {
                        $query->orderBy('Numero', 'DESC');
                    })
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
    public function folios_comprobantes_notas_obtener_ultimo_numero(Request $request){
        $id = Helpers::ultimoidtabla('App\FolioComprobanteNota');
        return response()->json($id);
    }

    //predeterminar folio
    public function folios_comprobantes_notas_predeterminar(Request $request){
        //predeterminar folio
        FolioComprobanteNota::where('Numero', $request->numerofolio)
        ->update([
            'Predeterminar' => '+'
        ]);
        //vaciar predeterminar de folio anterior
        FolioComprobanteNota::where('Numero', '<>', $request->numerofolio)
        ->update([
            'Predeterminar' => ''
        ]);        
    }

    //altas
    public function folios_comprobantes_notas_guardar(Request $request){

    }

    //bajas
    public function folios_comprobantes_notas_alta_o_baja(Request $request){
        $numerofolio=$request->numerofolio;
	    $FolioComprobanteNota = FolioComprobanteNota::where('Numero', $numerofolio )->first();
	    if($FolioComprobanteNota->Status == 'ALTA'){
            FolioComprobanteNota::where('Numero', $numerofolio)
            ->update([
                'Status' => 'BAJA'
            ]);  
	    }else{
            FolioComprobanteNota::where('Numero', $numerofolio)
            ->update([
                'Status' => 'ALTA'
            ]);
        }
	    return response()->json($numerofolio);
    }

    //obtener folio
    public function folios_comprobantes_notas_obtener_folio(Request $request){
        $FolioComprobanteNota = FolioComprobanteNota::where('Numero', $request->numerofolio)->first();
        $data = array(
            'FolioComprobanteNota' => $FolioComprobanteNota
        );
        return response()->json($data);
    }

    //cambios
    public function folios_comprobantes_notas_guardar_modificacion(Request $request){

    }

}
