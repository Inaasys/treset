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
    public function folios_comprobantes_facturas_obtener_ultimo_numero(Request $request){
        $id = Helpers::ultimoidtabla('App\FolioComprobanteFactura');
        return response()->json($id);
    }

    //predeterminar folio
    public function folios_comprobantes_facturas_predeterminar(Request $request){
        //predeterminar folio
        FolioComprobanteFactura::where('Numero', $request->numerofolio)
        ->update([
            'Predeterminar' => '+'
        ]);
        //vaciar predeterminar de folio anterior
        FolioComprobanteFactura::where('Numero', '<>', $request->numerofolio)
        ->update([
            'Predeterminar' => ''
        ]);        
    }

    //altas
    public function folios_comprobantes_facturas_guardar(Request $request){

    }

    //bajas
    public function folios_comprobantes_facturas_alta_o_baja(Requesr $request){
        $numerofolio=$request->numerofolio;
	    $FolioComprobanteFactura = FolioComprobanteFactura::where('Numero', $numerofolio )->first();
	    if($FolioComprobanteFactura->Status == 'ALTA'){
            FolioComprobanteFactura::where('Numero', $numerofolio)
            ->update([
                'Status' => 'BAJA'
            ]);  
	    }else{
            FolioComprobanteFactura::where('Numero', $numerofolio)
            ->update([
                'Status' => 'ALTA'
            ]);
        }
	    return response()->json($FolioComprobanteFactura);
    }

    //obtener folio
    public function folios_comprobantes_facturas_obtener_folio(Request $request){
        $FolioComprobanteFactura = FolioComprobanteFactura::where('Numero', $request->numerofolio)->first();
        $data = array(
            'FolioComprobanteFactura' => $FolioComprobanteFactura
        );
        return response()->json($data);
    }

    //cambios
    public function folios_comprobantes_facturas_guardar_modificacion(Request $request){

    }

}
