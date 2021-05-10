<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use DB;
use PDF;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use App\Prestamo_Herramienta;
use App\Prestamo_Herramienta_Detalle;
use App\Personal;
use App\CuentaXPagar;
use App\CuentaXPagarDetalle;
use App\OrdenCompraDetalle;
use Mail;

class PruebaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function pruebaswebscraping(){
        $fecha = "2020-09-24";

        $client = new Client();

        $crawler = $client->request('GET', 'https://www.dof.gob.mx/indicadores_detalle.php?cod_tipo_indicador=158&dfecha=24%2F09%2F2020&hfecha=24%2F09%2F2020');
        /*$arraydolar = array();
        $crawler->filter('.Celda .txt')->each(function ($node) {

            if(is_numeric($node->text())) {
                array_push($arraydolar, $node->text());
                print $node->text()."<br>";
            } 

        });*/

        $arraydolar = $crawler->filter('.Celda .txt')->last()->text();
        /*foreach ($arraydolar as $domElement) {
            //var_dump($domElement->nodeName);
            print $domElement->nodeName."<br>";
        }*/
        dd($arraydolar);

    }

    public function enviar_msj_whatsapp(Request $request){
        //dd($request->all());
        $datos = [
            'phone' => $request->numero, // numero telefonico
            'body' => $request->mensaje, // mensaje
        ];
        $json = json_encode($datos); // codificar datos en JSON
        //token asignado para el uso de la API
        $token = 'zgoax25wdbrzjvgx';
        //numero de instancia asignada para el uso de la API
        $numeroInstancia = '169609';
        //url de la API para enviar mensajes
        $url = 'https://eu174.chat-api.com/instance'.$numeroInstancia.'/message?token='.$token;
        // realizar petición a la API
        $opciones = stream_context_create(['http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/json',
                'content' => $json
            ]
        ]);
        // Enviar a peticion a la API
        $enviarmensaje = file_get_contents($url, false, $opciones);
        dd($enviarmensaje);
    }

    public function pruebas_vocales(){
        $ArrayVocales = array('A', 'E', 'I', 'O', 'U');
        $String = "BIENVENIDO";
        $ArrayVocalesEncontradas = array();
        $contadorvocales = 0;
        $vocalesencontradas = "";
        for($i = 0; $i<strlen($String); $i++){
            for($j = 0 ; $j<count($ArrayVocales); $j++){
                $existevocal = in_array($String[$i], $ArrayVocalesEncontradas);
                //if($existevocal == false){
                    if($ArrayVocales[$j] == $String[$i]){
                        array_push($ArrayVocalesEncontradas, $String[$i]);
                        $vocalesencontradas = $vocalesencontradas.",".$String[$i];
                        $contadorvocales++;
                    }
                //}
            }
        }
        echo "Numero de vocales: ".$contadorvocales;
        echo "<br>";
        echo "Vocales encontradas: ";
        print_r($ArrayVocalesEncontradas);
        //dd($ArrayVocalesEncontradas);
    }

    public function prueba_diferencias_arrays(){
        $ArrayDetallesTraspasoAnterior = Array();

        $ArrayDetallesTraspasoNuevo = Array('20725387');

        //$clave = array_search('verde', $array); // $clave = 2;
        //echo $clave;
        $diferencias_arreglos = array_diff($ArrayDetallesTraspasoAnterior, $ArrayDetallesTraspasoNuevo);
        dd($diferencias_arreglos);
/*
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                echo $eliminapartida."<br>";
            }
        }*/

    }

    public function matchar_compras(){
        /*$ArrayCompras = Array('27907-A');
        foreach($ArrayCompras as $compra){
            $cuentasporpagar = CuentaXPagarDetalle::where('Compra', $compra)->get();
            dd($cuentasporpagar);
        }*/
        $fecha='2021-03-08';

        dd(Helpers::fecha_mas_hora_exacta_accion_datetimestring($fecha));

    }

}
