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
use Facturapi\Facturapi;
use Storage;

class FolioComprobanteFacturaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //API FACTURAPI 
        $this->facturapi = new Facturapi( config('app.keygeneralfacturapi') ); //
    }
    
    public function folios_comprobantes_facturas(){
        return view('catalogos.foliosfiscales.folioscomprobantesfacturas');
    }
    public function folios_comprobantes_facturas_obtener(Request $request){
        if($request->ajax()){
            $data = FolioComprobanteFactura::query();
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
    public function folios_comprobantes_facturas_obtener_ultimo_numero(Request $request){
        $id = Helpers::ultimoidtabla('App\FolioComprobanteFactura');
        return response()->json($id);
    }

    //predeterminar folio
    public function folios_comprobantes_facturas_predeterminar(Request $request){
        //predeterminar folio
        FolioComprobanteFactura::where('Numero', $request->numerofoliopred)
        ->update([
            'Predeterminar' => '+'
        ]);
        //vaciar predeterminar de folio anterior
        FolioComprobanteFactura::where('Numero', '<>', $request->numerofoliopred)
        ->update([
            'Predeterminar' => ''
        ]);        
    }

    //folios fiscales
    public function folios_comprobantes_facturas_enviar_archivos_timbrado(Request $request){
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
    public function folios_comprobantes_facturas_guardar(Request $request){
        $serie=$request->serie;
	    $ExisteSerie = FolioComprobanteFactura::where('Serie', $serie )->first();
	    if($ExisteSerie == true){
	        $FolioComprobanteFactura = 1;
	    }else{
            $id = Helpers::ultimoidtabla('App\FolioComprobanteFactura');
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
                /*
                $mover_a_carpeta="archivos_timbrado_empresa";
                //archivo certificado
                $ArchivoDeCertificado = $request->archivocertificado;
                $nombre_original_certificado = $ArchivoDeCertificado->getClientOriginalName();
                $ArchivoCertificado = $nombre_original_certificado;
                //guardar en public/archivos_timbrado_empresa
                $ArchivoDeCertificado->move($mover_a_carpeta, $ArchivoCertificado);

                //archivo llave privada
                $ArchivoDeLlave = $request->archivollaveprivada;
                $nombre_original_llave = $ArchivoDeLlave->getClientOriginalName();
                $ArchivoLlave = $nombre_original_llave;
                //guardar en public/archivos_timbrado_empresa
                $ArchivoDeLlave->move($mover_a_carpeta, $ArchivoLlave);

                $Contraseña = $request->contrasenallaveprivada;
                */
                $ValidoDesde = Carbon::parse($request->certificadovalidodesde)->toDateTimeString();
                $ValidoHasta = Carbon::parse($request->certificadovalidohasta)->toDateTimeString();
            }
            $FolioComprobanteFactura = new FolioComprobanteFactura;
            $FolioComprobanteFactura->Numero=$id;
            $FolioComprobanteFactura->Serie=$request->serie;
            $FolioComprobanteFactura->Esquema=$request->esquema;
            $FolioComprobanteFactura->FolioInicial=$request->folioinicial;
            $FolioComprobanteFactura->Titulo=$request->titulo; 
            $FolioComprobanteFactura->Depto=$request->departamento;
            $FolioComprobanteFactura->ArchivoCertificado=$ArchivoCertificado;
            $FolioComprobanteFactura->ArchivoLlave=$ArchivoLlave;
            $FolioComprobanteFactura->Contraseña=$Contraseña;
            $FolioComprobanteFactura->NoCertificado=$NoCertificado;
            $FolioComprobanteFactura->ValidoDesde=$ValidoDesde;
            $FolioComprobanteFactura->ValidoHasta=$ValidoHasta;
            $FolioComprobanteFactura->Empresa=$request->empresa;
            $FolioComprobanteFactura->Domicilio=$request->domicilio;
            $FolioComprobanteFactura->Leyenda1=$request->leyenda1;
            $FolioComprobanteFactura->Leyenda2=$request->leyenda2;
            $FolioComprobanteFactura->Leyenda3=$request->leyenda3;
            $FolioComprobanteFactura->Pagare=$request->pagare;
            $FolioComprobanteFactura->Version=$request->versioncfdi;
            $FolioComprobanteFactura->Status="ALTA";
            $FolioComprobanteFactura->save();
        }
        return response()->json($FolioComprobanteFactura); 
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
        $path = public_path('archivos_timbrado_empresa');
        $urlcertificado = $path."\\".$FolioComprobanteFactura->ArchivoCertificado;
        $urlllave = $path."\\".$FolioComprobanteFactura->ArchivoLlave;
        $data = array(
            'FolioComprobanteFactura' => $FolioComprobanteFactura,
            'urlcertificado' => $urlcertificado,
            'urlllave' => $urlllave
        );
        return response()->json($data);
    }

    //cambios
    public function folios_comprobantes_facturas_guardar_modificacion(Request $request){
        $FolioComprobanteFactura = FolioComprobanteFactura::where('Numero', $request->numero)->first(); 
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
            FolioComprobanteFactura::where('Numero', $request->numero)
            ->update([
                'Titulo' => $request->titulo,
                'Depto' => $request->departamento,
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
                'Pagare' => $request->pagare,
                'Version' => $request->versioncfdi
            ]);
        }else{
            //modificar sin archivos
            FolioComprobanteFactura::where('Numero', $request->numero)
            ->update([
                'Titulo' => $request->titulo,
                'Depto' => $request->departamento,
                'Empresa' => $request->empresa,
                'Domicilio' => $request->domicilio,
                'Leyenda1' => $request->leyenda1,
                'Leyenda2' => $request->leyenda2,
                'Leyenda3' => $request->leyenda3,
                'Pagare' => $request->pagare,
                'Version' => $request->versioncfdi
            ]);
        }
        return response()->json($FolioComprobanteFactura); 
    }

}
