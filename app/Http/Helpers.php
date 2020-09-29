<?php
use Carbon\Carbon;
use App\Empresa;
use App\Serie;
use Jenssegers\Date\Date;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

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

    //se obtiene la fecha excacta en la que se realizo una accion con formato DateTimeString
    public static function fecha_exacta_accion_datetimestring(){
        Carbon::setLocale(config('app.locale'));
        Carbon::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $fecha = Carbon::now()->toDateTimeString();
        return $fecha;
    }

    //dar formato correcto a la fecha para input type date en vista
    public static function formatoinputdate($fecha){
        if($fecha == null){
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
        if($valorbd == null){
            $valorcorrecto = '0.'.$decimalesconfigurados;
        }else{
            $encontrar = '.';
            $result = strpos($valorbd, $encontrar);
            if($result == 0){
                $valorcorrecto = '0'.$valorbd;
            }else{
                $valorcorrecto = $valorbd;
            }
        }
        //dar formato correcto a la cantidad con base a los numero de decimales configurados y redondear
        //$truncar = 10**$numerodecimalesconfigurados;
        //return number_format(intval(round($valorcorrecto, $numerodecimalesconfigurados) * $truncar) / $truncar, $numerodecimalesconfigurados, '.', '');
        return number_format(round($valorcorrecto, $numerodecimalesconfigurados), $numerodecimalesconfigurados, '.', '');

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
        $client = new Client();
        $resultado_scraping = $client->request('GET', 'https://www.dof.gob.mx/indicadores_detalle.php?cod_tipo_indicador=158&dfecha='.$dia.'%2F'.$mes.'%2F'.$ano.'&hfecha='.$dia.'%2F'.$mes.'%2F'.$ano);
        $precio_dolar = $resultado_scraping->filter('.Celda .txt')->last()->text();
        return $precio_dolar;
        /*$fecha_explode = explode("-", $fecha);
        $ano = $fecha_explode[0];
        $mes = $fecha_explode[1];
        $dia = $fecha_explode[2];
        $pagina_inicio = file_get_contents('https://www.dof.gob.mx/indicadores_detalle.php?cod_tipo_indicador=158&dfecha='.$dia.'%2F'.$mes.'%2F'.$ano.'&hfecha='.$dia.'%2F'.$mes.'%2F'.$ano);
        $explode_pagina = explode('<td width="52%" align="center" class="txt">', $pagina_inicio);
        $ultimo_explode = explode('</td>', $explode_pagina[1]);
        return $ultimo_explode[0];*/
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