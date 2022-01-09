<?php
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Empresa;
use App\Serie;
use Jenssegers\Date\Date;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\HttpClient\HttpClient;


class Helpers{
    
    //saber si el sistema esta configurado para utilizar mayusculas
    public static function mayusculas_sistema(){
        $configuraciones_empresa = Empresa::all();
        $ini = $configuraciones_empresa[0]->Ini;
        $encontrarpalabra = 'Utilizar_Mayusculas=S';
        $mayusculas_sistema = strpos($ini, $encontrarpalabra);
        return $mayusculas_sistema;
    } 

    //convertir el string en mayusculas o minusculas segun sea el caso de la configuracion de la empresa
    public static function convertir_string_mayuscula_o_minuscula($string){
        $mayusculas_sistema = config('app.mayusculas_sistema');
        if($mayusculas_sistema == 'S'){
            $string_modificado = mb_strtoupper($string);
        }
        return $string_modificado;
    }

    //se obtiene la fecha de mexico en español correcta 
    public static function fecha_exacta_accion(){
        /*Carbon::setLocale(config('app.locale'));
        Carbon::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $fecha = Carbon::now()->formatLocalized('%A %d %B %Y %H:%M:%S');*/
        Date::setLocale(config('app.locale'));
        Date::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $fecha = Date::now()->format('l j F Y H:i:s');
        return $fecha;
    }

    //se coloca la fecha enviada en español
    public static function fecha_espanol($fechaenviada){
        /*Carbon::setLocale(config('app.locale'));
        Carbon::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $fecha = Carbon::parse($fechaenviada)->formatLocalized('%A %d %B %Y');*/
        Date::setLocale(config('app.locale'));
        Date::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $fecha = Date::parse($fechaenviada)->format('l j F Y');
        return $fecha;
    }

    //se coloca la fecha enviada en español
    public static function fecha_espanol_datetime($fechaenviada){
        /*Carbon::setLocale(config('app.locale'));
        Carbon::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $fecha = Carbon::parse($fechaenviada)->formatLocalized('%A %d %B %Y');*/
        Date::setLocale(config('app.locale'));
        Date::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $fecha = Date::parse($fechaenviada)->format('l j F Y H:i:s');
        return $fecha;
    }

    //se obtiene la fecha excacta en la que se realizo una accion con formato DateTimeString
    public static function fecha_exacta_accion_datetimestring(){
        Carbon::setLocale(config('app.locale'));
        Carbon::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $fecha = Carbon::now()->toDateTimeString();
        return $fecha;
    }

    //se obtiene la hora exacta y se contatena con la fecha recibida
    public static function fecha_mas_hora_exacta_accion_datetimestring($fechaenviada){
        Carbon::setLocale(config('app.locale'));
        Carbon::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $hora = Carbon::now()->toTimeString();
        $fechaenviada = Carbon::parse($fechaenviada)->toDateString();
        $fecha = Carbon::parse($fechaenviada." ".$hora)->toDateTimeString();
        return $fecha;
    }

    //se obtiene la fecha excacta para input date-timelocal
    public static function fecha_exacta_accion_datetimelocal(){
        Carbon::setLocale(config('app.locale'));
        Carbon::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $fecha = '';
        $fechamax = '';
        $fechamin = '';
        //$fecha = Carbon::now()->format('Y-m-d')."T".Carbon::now()->format('H:i');
        //$date = Carbon::parse('2021-12-02');
        $date = Carbon::now();
        if($date->day <= 3){
            $fecha = Carbon::now()->format('Y-m-d')."T".Carbon::now()->format('H:i');
            $ultimodiamesactual = Carbon::now()->endOfMonth()->toDateString();
            $fechamax = Carbon::parse($ultimodiamesactual)->format('Y-m-d')."T23:59";
            $primerdiamesanterior = new Carbon('first day of last month');
            $fechamin = Carbon::parse($primerdiamesanterior)->format('Y-m-d')."T00:00";
        }else{
            $fecha = Carbon::now()->format('Y-m-d')."T".Carbon::now()->format('H:i');
            $ultimodiamesactual = Carbon::now()->endOfMonth()->toDateString();
            $fechamax = Carbon::parse($ultimodiamesactual)->format('Y-m-d')."T23:59";
            $primerdiamesactual = new Carbon('first day of this month');
            $fechamin = Carbon::parse($primerdiamesactual)->format('Y-m-d')."T00:00";
        }
        $data = array(
            'fecha' => $fecha,
            'fechamax' => $fechamax,
            'fechamin' => $fechamin
        );
        return $data;
    }

    //se obtienes los intervalos de min y max para el input de fecha en los modulos
    public static function obtenerfechasdisponiblesenmodificacion($fechadocumento){
        Carbon::setLocale(config('app.locale'));
        Carbon::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $fecha = Carbon::parse($fechadocumento);
        $primerdia = $fecha->firstOfMonth()->toDateString();
        $ultimodia = $fecha->endOfMonth()->toDateString();
        $fechamax = Carbon::parse($ultimodia)->format('Y-m-d')."T23:59";
        $fechamin = Carbon::parse($primerdia)->format('Y-m-d')."T00:00";
        $data = array(
            'fechamax' => $fechamax,
            'fechamin' => $fechamin
        );
        return $data;
    }

    //dar formato correcto a la fecha para input type date en vista
    public static function formatoinputdate($fecha){
        if($fecha == null || $fecha == ''){
            $fechacorrecta = null;
        }else{
            $fechacorrecta = Carbon::parse($fecha)->format('Y-m-d');
        }
        return $fechacorrecta;
    }

    //dar formato correcto a la fecha para input date time local html 5
    public static function formatoinputdatetime($fecha){
        if($fecha == null){
            $fechacorrecta = null;
        }else{
            $fechacorrecta = Carbon::parse($fecha)->format('Y-m-d')."T".Carbon::parse($fecha)->format('H:i');
        }
        return $fechacorrecta;
    }

    //comparar año y mes de dos fecha
    public static function compararanoymesfechas($fechadocumento){
        Date::setLocale(config('app.locale'));
        Date::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $fecha = "";
        $fechaalta = Date::parse($fechadocumento);
        $fechaactual = Date::now();
        //$fechaactual = Date::parse('2000-01-31');
        if( Auth::user()->role_id != 1){
            if( ($fechaalta->year != $fechaactual->year) || ($fechaalta->month != $fechaactual->month)){
                $fecha = Date::parse($fechadocumento)->format('l j F Y');
            }
        }
        return $fecha;
    }

    //se obtiene el ultimo folio del modulo que se requiere
    public static function ultimofoliotablamodulos($tabla){
        $ultimoFolioTabla = $tabla::select("Folio")->orderBy("Folio", "DESC")->take(1)->get();
        if(sizeof($ultimoFolioTabla) == 0 || sizeof($ultimoFolioTabla) == "" || sizeof($ultimoFolioTabla) == null){
            $folio = 1;
        }else{
            $folio = $ultimoFolioTabla[0]->Folio+1;   
        }
        return $folio;
    }

    //se obtiene el ultimo folio por serie del modulo que se requiere
    public static function ultimofolioserietablamodulos($tabla, $serie){
        $ultimoFolioTabla = $tabla::select("Folio")->where('Serie', $serie)->orderBy("Folio", "DESC")->take(1)->get();
        if(sizeof($ultimoFolioTabla) == 0 || sizeof($ultimoFolioTabla) == "" || sizeof($ultimoFolioTabla) == null){
            $folio = 1;
        }else{
            $folio = $ultimoFolioTabla[0]->Folio+1;   
        }
        return $folio;
    }

    //se obtiene el ultimo id de la tabla que se requiere
    public static function ultimoidtabla($tabla){
        $ultimoNumeroTabla = $tabla::select("Numero")->orderBy("Numero", "DESC")->take(1)->get();
        if(sizeof($ultimoNumeroTabla) == 0 || sizeof($ultimoNumeroTabla) == "" || sizeof($ultimoNumeroTabla) == null){
            $id = 1;
        }else{
            $id = $ultimoNumeroTabla[0]->Numero+1;   
        }
        return $id;
    }

    //se obtiene el ultimo id de la tabla que se requiere
    public static function ultimoidregistrotabla($tabla){
        $ultimoidregistrotabla = $tabla::select("id")->orderBy("id", "DESC")->take(1)->get();
        if(sizeof($ultimoidregistrotabla) == 0 || sizeof($ultimoidregistrotabla) == "" || sizeof($ultimoidregistrotabla) == null){
            $id = 1;
        }else{
            $id = $ultimoidregistrotabla[0]->id+1;   
        }
        return $id;
    }

    //se obtiene el ultimo id de la tabla que se requiere segun la serie
    public static function ultimofolioserieregistrotabla($tabla,$serie){
        $ultimofolioregistrotabla = $tabla::select("folio")->where('serie', $serie)->orderBy("folio", "DESC")->take(1)->get();
        if(sizeof($ultimofolioregistrotabla) == 0 || sizeof($ultimofolioregistrotabla) == "" || sizeof($ultimofolioregistrotabla) == null){
            $folio = 1;
        }else{
            $folio = $ultimofolioregistrotabla[0]->folio+1;   
        }
        return $folio;
    }

    //se obtiene el ultimo id de la tabla que se requiere
    public static function ultimoidycuentacontablebanco($tabla){
        $ultimoNumeroTabla = $tabla::select("Numero")->orderBy("Numero", "DESC")->take(1)->get();
        if(sizeof($ultimoNumeroTabla) == 0 || sizeof($ultimoNumeroTabla) == "" || sizeof($ultimoNumeroTabla) == null){
            $id = 1;
            $cuentacontable = '100-002-000001';
        }else{
            $id = $ultimoNumeroTabla[0]->Numero+1;
            $cuentacontable = '100-002-00000'.$id;   
        }
        $data = array(
            'id' => $id,
            'cuentacontable' => $cuentacontable
        );
        return $data;
    }

    //convertir valor de forma correcta con el numero de decimales establecidos en la confuracion del sistema
    public static function convertirvalorcorrecto($valorbd){
        //obtener numero de decimales configurados
        $numerodecimalesconfigurados = config('app.numerodedecimales');
        $decimalesconfigurados = '';
        //saber cuantos ceros se requiren con base a los decimales configurados ejemplo si se configuraron 3 decimales la cadena obtenida sera '000'
        for($i=0;$i<$numerodecimalesconfigurados;$i++){
            $decimalesconfigurados = $decimalesconfigurados.'0';
        }
        //if($valorbd == null){
            //$valorcorrecto = '0.'.$decimalesconfigurados;
            return number_format(round($valorbd, $numerodecimalesconfigurados), $numerodecimalesconfigurados, '.', '');
        //}else{
            /*if($valorbd<0){//si el numero es negativo
                $numero = abs($valorbd);
                $valorcorrecto = number_format($numero) * -1;
                return number_format(round($valorcorrecto, $numerodecimalesconfigurados), $numerodecimalesconfigurados, '.', '');
            }else {*/
                //$encontrar = '.';
                //$result = strpos($valorbd, $encontrar);
                //if($result == 0){
                  //  $valorcorrecto = '0'.$valorbd;
                //}else{
                  //  $valorcorrecto = $valorbd;
               // }
                //return number_format(round($valorcorrecto, $numerodecimalesconfigurados), $numerodecimalesconfigurados, '.', '');
            //}
        //}
        //dar formato correcto a la cantidad con base a los numero de decimales configurados y redondear
        //return number_format(round($valorcorrecto, $numerodecimalesconfigurados), $numerodecimalesconfigurados, '.', '');
    }

    //obtener numero de ceros con base a los decimales configurados
    public static function numerocerosconfiguracion(){
        //obtener numero de decimales configurados
        $numerodecimalesconfigurados = config('app.numerodedecimales');
        $numerocerosconfigurados = '';
        //saber cuantos ceros se requiren con base a los decimales configurados ejemplo si se configuraron 3 decimales la cadena obtenida sera '000'
        for($i=0;$i<$numerodecimalesconfigurados;$i++){
            $numerocerosconfigurados = $numerocerosconfigurados.'0';
        }
        return $numerocerosconfigurados;
    }
    //obtener numero de ceros con base a los decimales configurados
    public static function numerocerosconfiguracioninputnumberstep(){
        //obtener numero de decimales configurados
        $numerodecimalesconfigurados = config('app.numerodedecimales')-1;
        $numerocerosconfigurados = '';
        //saber cuantos ceros se requiren con base a los decimales configurados ejemplo si se configuraron 3 decimales la cadena obtenida sera '000'
        for($i=0;$i<$numerodecimalesconfigurados;$i++){
            $numerocerosconfigurados = $numerocerosconfigurados.'0';
        }
        return $numerocerosconfigurados.'1';
    }    

    //obtener serie del modulo que se require del usuario logueado si es que la ocupa
    public static function obtenerserieusuario($usuario, $documento){
        $serie = Serie::where('Documento', $documento)->where('Usuario', $usuario)->first();
        if($serie != null){
            $serieusuario = $serie->Serie;
        }else{
            $serieusuario = 'A';
        }
        return $serieusuario;
    }

    //calcular porcentaje de iva
    public static function calcular_porcentaje_iva_aritmetico($iva, $subtotal){
        $porcentajeiva = ($iva * 100) / $subtotal;
        return $porcentajeiva;
    }

    public static function obtener_valor_dolar_por_fecha_diario_oficial_federacion($fecha){
        $fecha_explode = explode("-", $fecha);
        $ano = $fecha_explode[0];
        $mes = $fecha_explode[1];
        $dia = $fecha_explode[2];
        $precio_dolar = 0;
        $client = new Client(HttpClient::create(['verify_peer' => false, 'verify_host' => false])); 
        try{
            $resultado_scraping = $client->request('GET', 'https://www.dof.gob.mx/indicadores_detalle.php?cod_tipo_indicador=158&dfecha='.$dia.'%2F'.$mes.'%2F'.$ano.'&hfecha='.$dia.'%2F'.$mes.'%2F'.$ano);
            $precio_dolar = $resultado_scraping->filter('.Celda .txt')->last()->text();
        } catch(Exception $e) {
            $precio_dolar = "sinactualizacion";
        }
        return $precio_dolar;
        /*
        $fecha_explode = explode("-", $fecha);
        $ano = $fecha_explode[0];
        $mes = $fecha_explode[1];
        $dia = $fecha_explode[2];
        $pagina_inicio = file_get_contents('https://www.dof.gob.mx/indicadores_detalle.php?cod_tipo_indicador=158&dfecha='.$dia.'%2F'.$mes.'%2F'.$ano.'&hfecha='.$dia.'%2F'.$mes.'%2F'.$ano);
        $explode_pagina = explode('<td width="52%" align="center" class="txt">', $pagina_inicio);
        $ultimo_explode = explode('</td>', $explode_pagina[1]);
        return $ultimo_explode[0];
        */
    }

    public static function comprobar_conexion_internet(){
        $conexion = @fsockopen("www.google.com", 80); 
        if ($conexion){
            $estado_conexion_internet = true; //action when connected
            fclose($conexion);
        }else{
            $estado_conexion_internet = false; //action in connection failure
        }
        return $estado_conexion_internet;
    }

    //eliminar archivos pdfs generados
    public static function eliminararchivospdfsgenerados(){
        //primero eliminar todos los archivos de la carpeta
        $pdfs = glob(storage_path('archivos_pdf_documentos_generados/*')); //obtenemos todos los nombres de los ficheros
        foreach($pdfs as $pd){
            if(is_file($pd))
            unlink($pd); //elimino el fichero
        }
        return $pdfs;
    }
    
    //quitar acentos
    public static function quitaracentos($string){
        $string = trim($string);
        $string = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $string
        );
        $string = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $string
        );
        $string = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $string
        );
        $string = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $string
        );
        $string = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $string
        );   
        return $string;        
    }
}
?>