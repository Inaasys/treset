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
            $data = FolioComprobanteNota::orderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $botoncambios =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos('.$data->Numero.')"><i class="material-icons">mode_edit</i></div> '; 
                        $botonbajas =      '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar('.$data->Numero.')"><i class="material-icons">cancel</i></div> ';
                        $botonpredeterminar = '<div class="btn bg-green btn-xs waves-effect" data-toggle="tooltip" title="Predeterminar Folio" onclick="predeterminarfolio('.$data->Numero.')"><i class="material-icons">check</i></div> ';
                        if($data->Status == 'ALTA'){
                            $boton =    $botonpredeterminar.$botoncambios.$botonbajas;
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
