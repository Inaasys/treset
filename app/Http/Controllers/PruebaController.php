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
use App\Proveedor;
use App\Producto;
use App\Marca;
use App\Existencia;
use App\Almacen;
use Mail;
use App\Configuracion_Tabla;
use App\Imports\CatalogoSATc_ClaveProdServCPImport;
use App\Imports\CatalogosSATImport;
use App\Exports\ProductosActivosMigrarNuevaBaseExport;
use App\Exports\ProductosActivosMigrarExistenciasNuevaBaseExport;
use Maatwebsite\Excel\Facades\Excel;

class PruebaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function pruebaswebscraping(){

        
        /*
        /*OBTENER INSUMOS CaTALOGOS PRODUCTOS*/
        /*$insumosproductos = Producto::select('Insumo', 'Codigo')->where('Insumo', '<>', '')->get();
        //dd($insumosproductos);
        $modificados=1;
        foreach($insumosproductos as $ip){
            $porciones = str_split($ip->Insumo);
            if($porciones[0] == "0"){
                //dd("no se agrega");
            }else{
                Producto::where('Codigo', $ip->Codigo)
                ->update([
                    'Insumo'=>"0".$ip->Insumo,
                ]);
                $modificados++;
            }

            /*
            if($ip->Insumo == null){
                dd($ip);
            }
            */
        //}
        

        /*

        /*OBTENER INSUMOS Remisiones  detalles*/
        $insumosproductos = RemisionDetalle::select('Insumo', 'Codigo', 'Remision', 'Item')->where('Insumo', '<>', '')->get();
        //dd($insumosproductos);
        $modificados=1;
        foreach($insumosproductos as $ip){
            $porciones = str_split($ip->Insumo);
            if($porciones[0] == "0"){
                //dd("no se agrega");
            }else{
                RemisionDetalle::where('Remision', $ip->Remision)->where('Codigo', $ip->Codigo)->where('Item', $ip->Item)
                ->update([
                    'Insumo'=>"0".$ip->Insumo,
                ]);
                $modificados++;
            }

            /*
            if($ip->Insumo == null){
                dd($ip);
            }
            */
        }

        /*


        /*OBTENER EXISTENCIAS CORRECTA POR PRODUCTOS SUMANDO Y RESTANDO SU KARDEX*/        
        /*
        $numerodecimalesconfigurados = config('app.numerodedecimales');
        $data = array();
        $productos = Producto::all();
        foreach($productos as $p){
            $kardex = DB::select('exec ObtenerKardex ?,?', array($p->Codigo,1));
            //dd($kardex);
            $nummovimiento = 1;
            $entradas = 0;
            $salidas = 0;
            $existencias = 0;
            foreach(array_reverse($kardex) as $k){
                $entradas = $entradas + $k->Entradas;
                $salidas = $salidas + $k->Salidas;
                $existencias = $existencias + $k->Entradas - $k->Salidas;
                $nummovimiento++;
            }
            $data[]=array(
                "codigo"=>$p->Codigo,
                "almacen" => Helpers::convertirvalorcorrecto(1),
                "entradas"=> Helpers::convertirvalorcorrecto($entradas),
                "salidas" => Helpers::convertirvalorcorrecto($salidas),
                "existencias"=> round($existencias, $numerodecimalesconfigurados),
            );
        }
        $filasmovimientos = '';
        foreach($data as $d){
            $filasmovimientos= $filasmovimientos.
            '<tr>'.
                    '<td><b>'.$d['codigo'].'</b></td>'.
                    '<td>'.$d['almacen'].'</td>'.
                    '<td>'.$d['entradas'].'</td>'.
                    '<td>'.$d['salidas'].'</td>'.
                    '<td>'.$d['existencias'].'</td>'.
            '</tr>';
        }
        echo '<table border="2"><tr><td>Codigo</td><td>Almacen</td><td>Entradas</td><td>Salidas</td><td>Existencias</td></tr>'.$filasmovimientos.'</table>';
        */
        //return response()->json($data);
        /*FIN OBTENER EXISTENCIAS CORRECTA POR PRODUCTOS SUMANDO Y RESTANDO SU KARDEX*/        


        /*$fecha = "2020-09-24";

        $client = new Client();

        $crawler = $client->request('GET', 'https://www.dof.gob.mx/indicadores_detalle.php?cod_tipo_indicador=158&dfecha=24%2F09%2F2020&hfecha=24%2F09%2F2020');
        /*$arraydolar = array();
        $crawler->filter('.Celda .txt')->each(function ($node) {

            if(is_numeric($node->text())) {
                array_push($arraydolar, $node->text());
                print $node->text()."<br>";
            } 

        });*/

        //$arraydolar = $crawler->filter('.Celda .txt')->last()->text();
        /*foreach ($arraydolar as $domElement) {
            //var_dump($domElement->nodeName);
            print $domElement->nodeName."<br>";
        }*/
        //dd($arraydolar);*/

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
        $sales = DB::connection('sqlsrv2')->select("Select * from Clientes");
        dd($sales);

    }

    public function importSATClaveProdServCP(){        
        /*
        //CATALOGO c_ClaveProdServCP
        $arrayexcel =  Excel::toArray(new CatalogoSATc_ClaveProdServCPImport, storage_path('c_ClaveProdServCP.xls'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 4){
                $fecha = Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($partida[4]))->toDateTimeString();
                DB::table('c_ClaveProdServCP')->insert(
                    [
                        'Clave' => $partida[0], 
                        'Descripcion' => $partida[1],
                        'PalabrasSimilares' => $partida[2],
                        'MaterialPeligroso' => $partida[3],
                        'FechaDeInicioDeVigencia' => $fecha,
                        'FechaDeFinDeVigencia' => $partida[5],
                        'Numero' => $numerofila,
                    ]
                );
                $numerofila++;
            }
            $rowexcel++;
        }
        */
        /*
        //CATALOGO c_ClaveUnidadPeso
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('c_ClaveUnidadPeso.xls'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 4){
                $fecha = Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($partida[4]))->toDateTimeString();
                DB::table('c_ClaveUnidadPeso')->insert(
                    [
                        'Numero' => $numerofila,
                        'Clave' => $partida[0], 
                        'Nombre' => $partida[1],
                        'Descripcion' => $partida[2],
                        'Nota' => $partida[3],
                        'FechaDeInicioDeVigencia' => $fecha,
                        'FechaDeFinDeVigencia' => $partida[5],
                        'Simbolo' => $partida[6],
                        'Bandera' => $partida[7]
                    ]
                );
                $numerofila++;
            }
            $rowexcel++;
        }
        */
        /*
        //CATALOGO c_ConfigAutotransporte
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('c_ConfigAutotransporte.xls'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 4){
                $fecha = Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($partida[5]))->toDateTimeString();
                DB::table('c_ConfigAutotransporte')->insert(
                    [
                        'Numero' => $numerofila,
                        'Clave' => $partida[0], 
                        'Descripcion' => $partida[1],
                        'NumeroEjes' => $partida[2],
                        'NumeroLlantas' => $partida[3],
                        'Remolque' => $partida[4],
                        'FechaDeInicioDeVigencia' => $fecha,
                        'FechaDeFinDeVigencia' => $partida[6],
                    ]
                );
                $numerofila++;
            }
            $rowexcel++;
        }
        */
        /*
        //CATALOGO c_CveTransporte
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('c_CveTransporte.xls'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 4){
                $fecha = Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($partida[2]))->toDateTimeString();
                DB::table('c_CveTransporte')->insert(
                    [
                        'Numero' => $numerofila,
                        'Clave' => $partida[0], 
                        'Descripcion' => $partida[1],
                        'FechaDeInicioDeVigencia' => $fecha,
                        'FechaDeFinDeVigencia' => $partida[3],
                    ]
                );
                $numerofila++;
            }
            $rowexcel++;
        }
        */
        /*
        //CATALOGO c_Estaciones 
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('c_Estaciones.xls'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 4){
                $fecha = Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($partida[6]))->toDateTimeString();
                DB::table('c_Estaciones')->insert(
                    [
                        'Numero' => $numerofila,
                        'Clave' => $partida[0], 
                        'Descripcion' => $partida[1],
                        'ClaveTransporte' => $partida[2],
                        'Nacionalidad' => $partida[3],
                        'DesignadorIATA' => $partida[4],
                        'LineaFerrea' => $partida[5],
                        'FechaDeInicioDeVigencia' => $fecha,
                        'FechaDeFinDeVigencia' => $partida[7],
                    ]
                );
                $numerofila++;
            }
            $rowexcel++;
        }
        */
        /*
        //CATALOGO c_FiguraTransporte 
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('c_FiguraTransporte.xls'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 4){
                $fecha = Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($partida[2]))->toDateTimeString();
                DB::table('c_FiguraTransporte')->insert(
                    [
                        'Numero' => $numerofila,
                        'Clave' => $partida[0], 
                        'Descripcion' => $partida[1],
                        'FechaDeInicioDeVigencia' => $fecha,
                        'FechaDeFinDeVigencia' => $partida[3],
                    ]
                );
                $numerofila++;
            }
            $rowexcel++;
        }
        */
        /*
        //CATALOGO c_MaterialPeligroso 
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('c_MaterialPeligroso.xls'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 4){
                $fecha = Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($partida[5]))->toDateTimeString();
                DB::table('c_MaterialPeligroso')->insert(
                    [
                        'Numero' => $numerofila,
                        'Clave' => $partida[0], 
                        'Descripcion' => $partida[1],
                        'ClaseODiv' => $partida[2],
                        'PeligroSecundaria' => $partida[3],
                        'NombreTecnico' => $partida[4],
                        'FechaDeInicioDeVigencia' => $fecha,
                        'FechaDeFinDeVigencia' => $partida[6],
                    ]
                );
                $numerofila++;
            }
            $rowexcel++;
        }
        */
        /*
        //CATALOGO c_ParteTransporte 
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('c_ParteTransporte.xls'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 4){
                $fecha = Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($partida[2]))->toDateTimeString();
                DB::table('c_ParteTransporte')->insert(
                    [
                        'Numero' => $numerofila,
                        'Clave' => $partida[0], 
                        'Descripcion' => $partida[1],
                        'FechaDeInicioDeVigencia' => $fecha,
                        'FechaDeFinDeVigencia' => $partida[3],
                    ]
                );
                $numerofila++;
            }
            $rowexcel++;
        }
        */
        /*
        //CATALOGO  c_SubTipoRem 
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('c_SubTipoRem.xls'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 4){
                $fecha = Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($partida[2]))->toDateTimeString();
                DB::table('c_SubTipoRem')->insert(
                    [
                        'Numero' => $numerofila,
                        'Clave' => $partida[0], 
                        'RemolqueOSemiremolque' => $partida[1],
                        'FechaDeInicioDeVigencia' => $fecha,
                        'FechaDeFinDeVigencia' => $partida[3],
                    ]
                );
                $numerofila++;
            }
            $rowexcel++;
        }
        */
        /*
        //CATALOGO  c_TipoEmbalaje 
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('c_TipoEmbalaje.xls'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 4){
                $fecha = Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($partida[2]))->toDateTimeString();
                DB::table('c_TipoEmbalaje')->insert(
                    [
                        'Numero' => $numerofila,
                        'Clave' => $partida[0], 
                        'Descripcion' => $partida[1],
                        'FechaDeInicioDeVigencia' => $fecha,
                        'FechaDeFinDeVigencia' => $partida[3],
                    ]
                );
                $numerofila++;
            }
            $rowexcel++;
        }
        */
        /*
        //CATALOGO  c_TipoEstacion 
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('c_TipoEstacion.xls'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 4){
                $fecha = Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($partida[3]))->toDateTimeString();
                DB::table('c_TipoEstacion')->insert(
                    [
                        'Numero' => $numerofila,
                        'Clave' => $partida[0], 
                        'Descripcion' => $partida[1],
                        'ClaveTransporte' => $partida[2],
                        'FechaDeInicioDeVigencia' => $fecha,
                        'FechaDeFinDeVigencia' => $partida[4],
                    ]
                );
                $numerofila++;
            }
            $rowexcel++;
        }
        */
        /*
        //CATALOGO  c_TipoPermiso 
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('c_TipoPermiso.xls'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 4){
                $fecha = Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($partida[3]))->toDateTimeString();
                DB::table('c_TipoPermiso')->insert(
                    [
                        'Numero' => $numerofila,
                        'Clave' => $partida[0], 
                        'Descripcion' => $partida[1],
                        'ClaveTransporte' => $partida[2],
                        'FechaDeInicioDeVigencia' => $fecha,
                        'FechaDeFinDeVigencia' => $partida[4],
                    ]
                );
                $numerofila++;
            }
            $rowexcel++;
        }
        */

        //return response()->json($rowexcel); 
        
    }

    //funcion para  obtener datos de producto en base
    public function obtener_datos_catalogo_productos_por_codigo(Request $request){
        //CATALOGO  PRODUCTOS 
        //$arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('codigosconmovimientos.xlsx'));
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('codigossinmovimientos.xlsx'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        $arraydatos = array();
        foreach($partidasexcel as $partida){
                $datosproducto = Producto::where('Codigo', ''.$partida[1].'')->count();
                if($datosproducto > 0){
                    $producto = Producto::where('Codigo', ''.$partida[1].'')->first();
                    $arraydatos[] = array(
                        "insumo" => $partida[0],
                        "codigo" => $partida[1], 
                        "claveproducto" => $producto->ClaveProducto,
                        "claveunidad" => $producto->ClaveUnidad, 
                        "descripcion" => $producto->Producto,
                        "unidad" => $producto->Unidad,
                        "marca" => $producto->Marca,
                        "linea" => $producto->Linea,
                        "impuesto" => $producto->Impuesto,
                        "ubicacion" => $producto->Ubicacion,
                        "tipoprod" => $producto->TipoProd,
                        "costo" => $producto->Costo,
                        "precio" => $producto->Precio,
                        "utilidad" => $producto->Utilidad,
                        "subtotal" => $producto->SubTotal,
                        "iva" => $producto->Iva,
                        "total" => $producto->Total,
                        "status" => $producto->Status,
                        "costolista" => $producto->CostoDeLista,
                        "moneda" => $producto->Moneda,
                        "costoventa" => $producto->CostoDeVenta,
                        "precio1" => $producto->Precio1
                    );
                }else{
                    $nuevocodigo = '0'.$partida[1];
                    $datosnuevoproducto = Producto::where('Codigo', ''.$nuevocodigo.'')->count();
                    if($datosnuevoproducto > 0){
                        $producto = Producto::where('Codigo', ''.$nuevocodigo.'')->first();
                        $arraydatos[] = array(
                            "insumo" => $partida[0],
                            "codigo" => $partida[1], 
                            "claveproducto" => $producto->ClaveProducto,
                            "claveunidad" => $producto->ClaveUnidad, 
                            "descripcion" => $producto->Producto,
                            "unidad" => $producto->Unidad,
                            "marca" => $producto->Marca,
                            "linea" => $producto->Linea,
                            "impuesto" => $producto->Impuesto,
                            "ubicacion" => $producto->Ubicacion,
                            "tipoprod" => $producto->TipoProd,
                            "costo" => $producto->Costo,
                            "precio" => $producto->Precio,
                            "utilidad" => $producto->Utilidad,
                            "subtotal" => $producto->SubTotal,
                            "iva" => $producto->Iva,
                            "total" => $producto->Total,
                            "status" => $producto->Status,
                            "costolista" => $producto->CostoDeLista,
                            "moneda" => $producto->Moneda,
                            "costoventa" => $producto->CostoDeVenta,
                            "precio1" => $producto->Precio1
                        );
                    }
                }
            $rowexcel++;
        }
        //return Excel::download(new ProductosActivosMigrarNuevaBaseExport($arraydatos), "productosconmovimientosamigrar.xlsx");   
        return Excel::download(new ProductosActivosMigrarNuevaBaseExport($arraydatos), "productossinmovimientosamigrar.xlsx");   
    }

    //migrar los productos que solo se utilizan a base nueva
    public function migrar_productos_utilizados_base_nueva(Request $request){
        //$arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('productosconmovimientosamigrar.xlsx'));
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('productossinmovimientosamigrar.xlsx'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        foreach($partidasexcel as $partida){
            if($rowexcel > 1){
                $ExisteProducto = Producto::where('Codigo', ''.$partida[1].'' )->count();
                if($ExisteProducto == 0){      
                    $Producto = new Producto;
                    $Producto->Insumo=$partida[0];
                    $Producto->Codigo=$partida[1];
                    $Producto->ClaveProducto=$partida[2];
                    $Producto->ClaveUnidad=$partida[3];
                    $Producto->Producto=$partida[4];
                    $Producto->Unidad=$partida[5];
                    $Producto->Marca=$partida[6];
                    $Producto->Linea=$partida[7];
                    $Producto->Impuesto=$partida[8];;
                    $Producto->Ubicacion=$partida[9];
                    $Producto->TipoProd=$partida[10];
                    $Producto->Costo=$partida[11];
                    $Producto->Precio=$partida[12];
                    $Producto->Utilidad=$partida[13];
                    $Producto->SubTotal=$partida[14];
                    $Producto->Iva=$partida[15];
                    $Producto->Total=$partida[16];
                    $Producto->Status=$partida[17];
                    $Producto->CostoDeLista=$partida[18];
                    $Producto->Moneda=$partida[19];
                    $Producto->CostoDeVenta=$partida[20];
                    $Producto->Precio1=$partida[21];
                    $Producto->save();
                }    
                $numerofila++;
            }
            $rowexcel++;
        }
        return response()->json($rowexcel); 
    }

    //obtener existencias actuales producto y almacen y generar excel para cargar ajuste
    public function obtener_existencias_por_codigo_y_almacen_generar_excel_ajuste(Request $request){
        //CATALOGO  PRODUCTOS 
        //$arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('codigosconmovimientos.xlsx'));
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('codigossinmovimientos.xlsx'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        $arraydatos = array();
        $numeroalmacen = 1;
        foreach($partidasexcel as $partida){
                $existenciasproducto = Existencia::where('Codigo', ''.$partida[1].'')->where('Almacen', $numeroalmacen)->count();
                if($existenciasproducto > 0){
                    $existencias = Existencia::where('Codigo', ''.$partida[1].'')->where('Almacen', $numeroalmacen)->first();
                    $arraydatos[] = array(
                        "codigo" => $partida[1], 
                        "entradas" => $existencias->Existencias,
                        "salidas" => '0.000000', 
                    );
                }else{
                    $nuevocodigo = '0'.$partida[1];
                    $existenciasproducto = Existencia::where('Codigo', ''.$nuevocodigo.'')->where('Almacen', $numeroalmacen)->count();
                    if($existenciasproducto > 0){
                        $existencias = Existencia::where('Codigo', ''.$nuevocodigo.'')->where('Almacen', $numeroalmacen)->first();
                        $arraydatos[] = array(
                            "codigo" => $partida[1], 
                            "entradas" => $existencias->Existencias,
                            "salidas" => '0.000000',
                        );
                    }
                }
            $rowexcel++;
        }
        return Excel::download(new ProductosActivosMigrarExistenciasNuevaBaseExport($arraydatos), "plantillaajustesmisgrarexistenciasbaseproduccion.xlsx");   

    }
    
    public function comparar_existencias_vs_ajuste(Request $request){
        $arrayexcel =  Excel::toArray(new CatalogosSATImport, storage_path('ajusteconmov.xlsx'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $numerofila = 1;
        $arraydatos = array();
        $numeroalmacen = 1;
        $existenciasdiferentes = array();
        foreach($partidasexcel as $partida){
                $existenciasproducto = Existencia::where('Codigo', ''.$partida[0].'')->where('Almacen', $numeroalmacen)->count();
                if($existenciasproducto > 0){
                    $existencias = Existencia::where('Codigo', ''.$partida[0].'')->where('Almacen', $numeroalmacen)->first();
                    if($partida[1] != $existencias->Existencias){
                        array_push($existenciasdiferentes, $existencias->Existencias);
                    }
                }else{
                    $nuevocodigo = '0'.$partida[0];
                    $existenciasproducto = Existencia::where('Codigo', ''.$nuevocodigo.'')->where('Almacen', $numeroalmacen)->count();
                    if($existenciasproducto > 0){
                        $existencias = Existencia::where('Codigo', ''.$nuevocodigo.'')->where('Almacen', $numeroalmacen)->first();
                        if($partida[1] != $existencias->Existencias){
                            array_push($existenciasdiferentes, $existencias->Existencias);
                        }
                    }
                }
            $rowexcel++;
        }
        dd($existenciasdiferentes);
        //return Excel::download(new ProductosActivosMigrarExistenciasNuevaBaseExport($arraydatos), "plantillaajustesmisgrarexistenciasbaseproduccion.xlsx");   

    }

    public function asignar_valores_por_defecto_busquedas_y_ordenamiento(){
        //tabla ordenes compra
        Configuracion_Tabla::where('tabla', 'OrdenesDeCompra')
        ->update([
            'primerordenamiento'=>'Fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'Serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'Folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Orden,Status,NombreProveedor'
        ]);
        //tabla Compras
        Configuracion_Tabla::where('tabla', 'Compras')
        ->update([
            'primerordenamiento'=>'Fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'Serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'Folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Compra,Status,NombreProveedor'
        ]);
        //tabla ContraRecibos
        Configuracion_Tabla::where('tabla', 'ContraRecibos')
        ->update([
            'primerordenamiento'=>'Fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'Serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'Folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'ContraRecibo,Status,NombreProveedor'
        ]);
        //tabla OrdenesDeTrabajo
        Configuracion_Tabla::where('tabla', 'OrdenesDeTrabajo')
        ->update([
            'primerordenamiento'=>'Fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'Serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'Folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Orden,Status,Vin,Pedido,Marca,Economico,Placas'
        ]);
        //tabla CuentasPorPagar
        Configuracion_Tabla::where('tabla', 'CuentasPorPagar')
        ->update([
            'primerordenamiento'=>'Fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'Serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'Folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Pago,Status,NombreProveedor'
        ]);
        //tabla NotasCreditoProveedor
        Configuracion_Tabla::where('tabla', 'NotasCreditoProveedor')
        ->update([
            'primerordenamiento'=>'Fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'Serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'Folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Nota,Status,NombreProveedor,UUID'
        ]);
        //tabla asignacion_herramientas
        Configuracion_Tabla::where('tabla', 'asignacion_herramientas')
        ->update([
            'primerordenamiento'=>'fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'asignacion,status,nombre_recibe_herramienta,nombre_entrega_herramienta'
        ]);
        //tabla prestamo_herramientas
        Configuracion_Tabla::where('tabla', 'prestamo_herramientas')
        ->update([
            'primerordenamiento'=>'fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'prestamo,status,nombre_recibe_herramienta,nombre_entrega_herramienta'
        ]);
        //tabla cotizaciones_t
        Configuracion_Tabla::where('tabla', 'cotizaciones_t')
        ->update([
            'primerordenamiento'=>'fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'cotizacion,status,ot_tecnodiesel,ot_tyt'
        ]);
        //tabla AjustesInventario
        Configuracion_Tabla::where('tabla', 'AjustesInventario')
        ->update([
            'primerordenamiento'=>'Fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'Serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'Folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Ajuste,Status,NombreAlmacen'
        ]);
        //tabla Traspasos
        Configuracion_Tabla::where('tabla', 'Traspasos')
        ->update([
            'primerordenamiento'=>'Fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'Serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'Folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Traspaso,Orden,Nombre'
        ]);
        //tabla Remisiones
        Configuracion_Tabla::where('tabla', 'Remisiones')
        ->update([
            'primerordenamiento'=>'Fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'Serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'Folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Remision,Status,NombreCliente,Os,Eq,Rq'
        ]);
        //tabla NotasCreditoCliente
        Configuracion_Tabla::where('tabla', 'NotasCreditoCliente')
        ->update([
            'primerordenamiento'=>'Fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'Serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'Folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Nota,Status,UUID,NombreCliente,RfcCliente'
        ]);
        //tabla CuentasPorCobrar
        Configuracion_Tabla::where('tabla', 'CuentasPorCobrar')
        ->update([
            'primerordenamiento'=>'Fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'Serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'Folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Pago,UUID,Status,NombreCliente,RfcCliente'
        ]);
        //tabla Facturas
        Configuracion_Tabla::where('tabla', 'Facturas')
        ->update([
            'primerordenamiento'=>'Fecha',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'Serie',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'Folio',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Factura,Status,UUID,NombreCliente,RfcCliente'
        ]);
        //tabla Existencias
        Configuracion_Tabla::where('tabla', 'Existencias')
        ->update([
            'primerordenamiento'=>'omitir',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'omitir',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'omitir',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Codigo,Producto'
        ]);
        //tabla Productos
        Configuracion_Tabla::where('tabla', 'Productos')
        ->update([
            'primerordenamiento'=>'omitir',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'omitir',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'omitir',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Codigo,Producto'
        ]);
        //tabla Clientes
        Configuracion_Tabla::where('tabla', 'Clientes')
        ->update([
            'primerordenamiento'=>'Numero',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'omitir',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'omitir',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Numero,Nombre'
        ]);
        //tabla Proveedores
        Configuracion_Tabla::where('tabla', 'Proveedores')
        ->update([
            'primerordenamiento'=>'Numero',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'omitir',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'omitir',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Numero,Status,Nombre'
        ]);
    }

    public function modificar_valores_en_bd_para_actualizacion_rama20210706correciones(){
        //tabla Cotizaciones Productos
        Configuracion_Tabla::where('tabla', 'CotizacionesProductos')
        ->update([
            'primerordenamiento'=>'Folio',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'omitir',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'omitir',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Cotizacion,Status,NombreCliente'
        ]);
        //tabla Cotizaciones Servicios
        Configuracion_Tabla::where('tabla', 'CotizacionesServicio')
        ->update([
            'primerordenamiento'=>'Folio',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'omitir',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'omitir',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Cotizacion,Status,NombreCliente'
        ]);
    }

    public function modificar_valores_en_bd_para_actualizacion_rama20210731correciones(){
        //tabla Proveedores
        Proveedor::where('Numero', '>', 0)
        ->update([
            'SolicitarXML'=>'1',
        ]);
        //Configuracion Tabla Produccion        
        Configuracion_Tabla::where('tabla', 'Produccion')
        ->update([
            'primerordenamiento'=>'Folio',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'omitir',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'omitir',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Produccion,Status'
        ]);
        //Configuracion columnas tabla produccion       
        Configuracion_Tabla::where('tabla', 'Produccion')
        ->update([
            'campos_activados'=>'Produccion,Serie,Folio,Fecha,Codigo,Cliente,Almacen,Cantidad,Costo,Total,Obs,Status,Producido,MotivoDeBaja,Usuario,Periodo',
            'campos_desactivados'=>'Equipo',
            'columnas_ordenadas'=>'Produccion,Serie,Folio,Fecha,Codigo,Cliente,Almacen,Cantidad,Costo,Total,Obs,Status,Producido,MotivoDeBaja,Usuario,Periodo',
        ]);
    }

    public function modificar_valores_en_bd_para_actualizacion_rama20210814correciones(){
        //Configuracion columnas tabla produccion       
        Configuracion_Tabla::where('tabla', 'Produccion')
        ->update([
            'campos_activados'=>'Produccion,Serie,Folio,Fecha,Codigo,Cliente,Almacen,Cantidad,Costo,Total,Obs,Status,Producido,MotivoDeBaja,Usuario,Periodo',
            'campos_desactivados'=>'Equipo',
            'columnas_ordenadas'=>'Produccion,Serie,Folio,Fecha,Codigo,Cliente,Almacen,Cantidad,Costo,Total,Obs,Status,Producido,MotivoDeBaja,Usuario,Periodo',
        ]);
        
        //Configuracion Tabla Produccion        
        Configuracion_Tabla::where('tabla', 'Requisiciones')
        ->update([
            'primerordenamiento'=>'Folio',
            'formaprimerordenamiento'=>'DESC',
            'segundoordenamiento'=>'omitir',
            'formasegundoordenamiento'=>'ASC',
            'tercerordenamiento'=>'omitir',
            'formatercerordenamiento'=>'DESC',
            'campos_busquedas'=>'Requisicion,Orden,Vin,Economico,Status'
        ]);
    }

    public function modificar_valores_en_bd_para_actualizacion_rama20210924correciones(){
        //Configuracion columnas tabla Existencias       
        Configuracion_Tabla::where('tabla', 'Existencias')
        ->update([
            'campos_activados'=>'Codigo,Status,Producto,Ubicacion,Almacen,Existencias,Costo,Utilidad,SubTotal,Iva,Total,totalCostoInventario,Insumo',
            'campos_desactivados'=>'Unidad,CostoDeLista,Moneda,CostoDeVenta,Marca,Linea,FechaUltimaCompra,FechaUltimaVenta,ClaveProducto,ClaveUnidad,Precio,NombreMarca,NombreLinea',
            'columnas_ordenadas'=>'Codigo,Insumo,Status,Producto,Ubicacion,Almacen,Existencias,Costo,Utilidad,SubTotal,Iva,Total,totalCostoInventario',
        ]);
        
    }

    public function modificar_valores_en_bd_para_actualizacion_rama20211001correciones(){
        //Configuracion columnas tabla CuentasPorPagar       
        Configuracion_Tabla::where('tabla', 'CuentasPorPagar')
        ->update([
            'campos_activados'=>'Pago,Status,Fecha,Proveedor,Transferencia,Abono,Periodo,Banco,NombreProveedor,Compras',
            'campos_desactivados'=>'Equipo,MotivoBaja,Folio,Serie,Cheque,Beneficiario,CuentaDeposito,Anotacion,Usuario,NumeroBanco,NombreBanco,CuentaBanco,NumeroProveedor,RfcProveedor,CodigoPostalProveedor,PlazoProveedor,TelefonosProveedor,Email1Proveedor',
            'columnas_ordenadas'=>'Pago,Status,Fecha,Proveedor,Banco,NombreProveedor,Transferencia,Abono,Compras,Periodo',
        ]);
        
    }

    public function modificar_valores_en_bd_para_actualizacion_rama20211101correciones(){
        //Configuracion columnas tabla CuentasPorPagar       
        Producto::where('Status', 'ALTA')
        ->update([
            'TipoProd'=>'REFACCION',
        ]);
    }

    public function modificar_valores_en_bd_para_actualizacion_rama20211227correciones(){
        //Configuracion columnas tabla Remisiones       
        Configuracion_Tabla::where('tabla', 'Remisiones')
        ->update([
            'campos_activados'=>'Remision,Serie,Folio,Fecha,Status,Cliente,NombreCliente,Pedido,Os,Eq,SerieRq,Rq,Agente,NombreAgente,Tipo,Almacen,NombreAlmacen,SubTotal,Iva,Total,Equipo,Usuario,MotivoBaja,Periodo',
            'campos_desactivados'=>'Referencia,Plazo,Unidad,Solicita,Destino,TeleMarketing,Importe,Descuento,Costo,Comision,Utilidad,FormaPago,Obs,TipoCambio,Hora,Facturada,Corte,SuPago,EnEfectivo,EnTarjetas,EnVales,EnCheque,Lugar,Personas',
            'columnas_ordenadas'=>'Remision,Serie,Folio,Fecha,Status,Cliente,NombreCliente,Pedido,Os,Eq,SerieRq,Rq,Agente,NombreAgente,Tipo,Almacen,NombreAlmacen,SubTotal,Iva,Total,Equipo,Usuario,MotivoBaja,Periodo',
        ]);
    }

    public function modificar_valores_en_bd_para_actualizacion_rama20220109correciones(){
        //Configuracion columnas tabla cotizaciones_t       
        Configuracion_Tabla::where('tabla', 'cotizaciones_t')
        ->update([
            'campos_activados'=>'id,cotizacion,fecha,subtotal,iva,total,status,periodo',
            'campos_desactivados'=>'motivo_baja,equipo,usuario,serie',
            'columnas_ordenadas'=>'id,cotizacion,fecha,subtotal,iva,total,status,periodo',
        ]);
    }

}