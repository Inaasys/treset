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
use Facturapi\Facturapi;
use Storage;

class FolioComprobanteNotaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //API FACTURAPI 
        $this->facturapi = new Facturapi( config('app.keygeneralfacturapi') ); //
    }
    
    public function folios_comprobantes_notas(){
        return view('catalogos.foliosfiscales.folioscomprobantesnotas');
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
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos('.$data->Numero.')">Cambios</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar('.$data->Numero.')">Bajas</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="predeterminarfolio('.$data->Numero.')">Predeterminar Folio</a></li>'.
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
        FolioComprobanteNota::where('Numero', $request->numerofoliopred)
        ->update([
            'Predeterminar' => '+'
        ]);
        //vaciar predeterminar de folio anterior
        FolioComprobanteNota::where('Numero', '<>', $request->numerofoliopred)
        ->update([
            'Predeterminar' => ''
        ]);      
    }

    //folios fiscales
    public function folios_comprobantes_notas_enviar_archivos_timbrado(Request $request){
        if($request->archivocertificado != "" && $request->archivollaveprivada != "" && $request->contrasenallaveprivada != ""){
            $mover_a_carpeta="archivos_timbrado_empresa";
            $path = public_path('archivos_timbrado_empresa');
            //archivo certificado
            $ArchivoDeCertificado = $request->archivocertificado;
            $nombre_original_certificado = $ArchivoDeCertificado->getClientOriginalName();
            $ArchivoCertificado = $nombre_original_certificado;
            //guardar en public/archivos_timbrado_empresa
            $ArchivoDeCertificado->move($mover_a_carpeta, $ArchivoCertificado);
            $urlcertificado = $path."\\".$ArchivoCertificado;
            //archivo llave privada
            $ArchivoDeLlave = $request->archivollaveprivada;
            $nombre_original_llave = $ArchivoDeLlave->getClientOriginalName();
            $ArchivoLlave = $nombre_original_llave;
            //guardar en public/archivos_timbrado_empresa
            $ArchivoDeLlave->move($mover_a_carpeta, $ArchivoLlave);
            $urlllave = $path."\\".$ArchivoLlave;
            //contraseña archivos
            $Contraseña = $request->contrasenallaveprivada;
            //actualizar archivos de certificado
            $archivos = $this->facturapi->Organizations->uploadCertificate($this->empresa->IdFacturapi, array(
                "cerFile" => $urlcertificado,
                "keyFile" => $urlllave,
                "password" => $Contraseña
            ));
            $result = json_encode($archivos);
            $result2 = json_decode($result, true);
            if(array_key_exists('id', $result2)){
                $data = array(
                    'msj' => 'OK',
                    'updated_at' => $archivos->certificate->updated_at,
                    'expires_at' => $archivos->certificate->expires_at
                );
            }else{
                $data = array(
                    'msj' => 'Archivos o contraseña incorrectos',
                    'updated_at' => "",
                    'expires_at' => ""
                );
            }
        }else{
            $data = array(
                'msj' => 'Para obtener fechas Certificado válido desde y Certificado válido hasta, se requieren Archivo de certificado, Archivo de llave y Contraseña',
                'updated_at' => "",
                'expires_at' => ""
            );
        }
        return response()->json($data);
    }

    //altas
    public function folios_comprobantes_notas_guardar(Request $request){
        $serie=$request->serie;
	    $ExisteSerie = FolioComprobanteNota::where('Serie', $serie )->first();
	    if($ExisteSerie == true){
	        $FolioComprobanteNota = 1;
	    }else{
            $id = Helpers::ultimoidtabla('App\FolioComprobanteNota');
            $ArchivoCertificado = "";
            $ArchivoLlave = "";
            $Contraseña = "";
            $NoCertificado = "";
            $ValidoDesde = "";
            $ValidoHasta = "";
            if($request->esquema == "CFDI"){
                $mover_a_carpeta="archivos_timbrado_empresa";
                $path = public_path('archivos_timbrado_empresa');
                //archivo certificado
                $ArchivoDeCertificado = $request->archivocertificado;
                $nombre_original_certificado = $ArchivoDeCertificado->getClientOriginalName();
                $ArchivoCertificado = $nombre_original_certificado;
                //guardar en public/archivos_timbrado_empresa
                $ArchivoDeCertificado->move($mover_a_carpeta, $ArchivoCertificado);
                $urlcertificado = $path."\\".$ArchivoCertificado;
                //archivo llave privada
                $ArchivoDeLlave = $request->archivollaveprivada;
                $nombre_original_llave = $ArchivoDeLlave->getClientOriginalName();
                $ArchivoLlave = $nombre_original_llave;
                //guardar en public/archivos_timbrado_empresa
                $ArchivoDeLlave->move($mover_a_carpeta, $ArchivoLlave);
                $urlllave = $path."\\".$ArchivoLlave;
                //contraseña archivos
                $Contraseña = $request->contrasenallaveprivada;
                //actualizar archivos de certificado
                $archivos = $this->facturapi->Organizations->uploadCertificate($this->empresa->IdFacturapi, array(
                    "cerFile" => $urlcertificado,
                    "keyFile" => $urlllave,
                    "password" => $Contraseña
                ));
                $ValidoDesde = Carbon::parse($request->certificadovalidodesde)->toDateTimeString();
                $ValidoHasta = Carbon::parse($request->certificadovalidohasta)->toDateTimeString();
            }
            $FolioComprobanteNota = new FolioComprobanteNota;
            $FolioComprobanteNota->Numero=$id;
            $FolioComprobanteNota->Serie=$request->serie;
            $FolioComprobanteNota->Esquema=$request->esquema;
            $FolioComprobanteNota->FolioInicial=$request->folioinicial;
            $FolioComprobanteNota->Titulo=$request->titulo; 
            $FolioComprobanteNota->ArchivoCertificado=$ArchivoCertificado;
            $FolioComprobanteNota->ArchivoLlave=$ArchivoLlave;
            $FolioComprobanteNota->Contraseña=$Contraseña;
            $FolioComprobanteNota->NoCertificado=$NoCertificado;
            $FolioComprobanteNota->ValidoDesde=$ValidoDesde;
            $FolioComprobanteNota->ValidoHasta=$ValidoHasta;
            $FolioComprobanteNota->Empresa=$request->empresa;
            $FolioComprobanteNota->Domicilio=$request->domicilio;
            $FolioComprobanteNota->Leyenda1=$request->leyenda1;
            $FolioComprobanteNota->Leyenda2=$request->leyenda2;
            $FolioComprobanteNota->Leyenda3=$request->leyenda3;
            $FolioComprobanteNota->Leyenda=$request->pagare;
            $FolioComprobanteNota->Version=$request->versioncfdi;
            $FolioComprobanteNota->Status="ALTA";
            $FolioComprobanteNota->save();
        }
        return response()->json($FolioComprobanteNota); 
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
        $path = public_path('archivos_timbrado_empresa');
        $urlcertificado = $path."\\".$FolioComprobanteNota->ArchivoCertificado;
        $urlllave = $path."\\".$FolioComprobanteNota->ArchivoLlave;
        $data = array(
            'FolioComprobanteNota' => $FolioComprobanteNota,
            'urlcertificado' => $urlcertificado,
            'urlllave' => $urlllave
        );
        return response()->json($data);
    }

    //cambios
    public function folios_comprobantes_notas_guardar_modificacion(Request $request){
        $FolioComprobanteNota = FolioComprobanteNota::where('Numero', $request->numero)->first(); 
        $ArchivoCertificado = "";
        $ArchivoLlave = "";
        $Contraseña = "";
        $NoCertificado = "";
        $ValidoDesde = "";
        $ValidoHasta = "";
        if($request->esquema == "CFDI"){
            if($request->actualizarcertificado == 1){
                $mover_a_carpeta="archivos_timbrado_empresa";
                $path = public_path('archivos_timbrado_empresa');
                //archivo certificado
                $ArchivoDeCertificado = $request->archivocertificado;
                $nombre_original_certificado = $ArchivoDeCertificado->getClientOriginalName();
                $ArchivoCertificado = $nombre_original_certificado;
                //guardar en public/archivos_timbrado_empresa
                $ArchivoDeCertificado->move($mover_a_carpeta, $ArchivoCertificado);
                $urlcertificado = $path."\\".$ArchivoCertificado;
                //archivo llave privada
                $ArchivoDeLlave = $request->archivollaveprivada;
                $nombre_original_llave = $ArchivoDeLlave->getClientOriginalName();
                $ArchivoLlave = $nombre_original_llave;
                //guardar en public/archivos_timbrado_empresa
                $ArchivoDeLlave->move($mover_a_carpeta, $ArchivoLlave);
                $urlllave = $path."\\".$ArchivoLlave;
                //contraseña archivos
                $Contraseña = $request->contrasenallaveprivada;
                //actualizar archivos de certificado
                $archivos = $this->facturapi->Organizations->uploadCertificate($this->empresa->IdFacturapi, array(
                    "cerFile" => $urlcertificado,
                    "keyFile" => $urlllave,
                    "password" => $Contraseña
                ));
                $ValidoDesde = Carbon::parse($request->certificadovalidodesde)->toDateTimeString();
                $ValidoHasta = Carbon::parse($request->certificadovalidohasta)->toDateTimeString();
            }
        }
        if($request->actualizarcertificado == 1){
            //modificar con archivos
            FolioComprobanteNota::where('Numero', $request->numero)
            ->update([
                'Titulo' => $request->titulo,
                'ArchivoCertificado' => $ArchivoCertificado,
                'ArchivoLlave' => $ArchivoLlave,
                'Contraseña' => $Contraseña,
                'NoCertificado' => $NoCertificado,
                'ValidoDesde' => $ValidoDesde,
                'ValidoHasta' => $ValidoHasta,
                'Empresa' => $request->empresa,
                'Domicilio' => $request->domicilio,
                'Leyenda1' => $request->leyenda1,
                'Leyenda2' => $request->leyenda2,
                'Leyenda3' => $request->leyenda3,
                'Leyenda' => $request->pagare,
                'Version' => $request->versioncfdi
            ]);
        }else{
            //modificar sin archivos
            FolioComprobanteNota::where('Numero', $request->numero)
            ->update([
                'Titulo' => $request->titulo,
                'Empresa' => $request->empresa,
                'Domicilio' => $request->domicilio,
                'Leyenda1' => $request->leyenda1,
                'Leyenda2' => $request->leyenda2,
                'Leyenda3' => $request->leyenda3,
                'Leyenda' => $request->pagare,
                'Version' => $request->versioncfdi
            ]);
        }
        return response()->json($FolioComprobanteNota); 
    }

}
