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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RequisicionesExport;
use App\Requisicion;
use App\RequisicionDetalle;
use App\TipoOrdenCompra;
use App\Cliente;
use App\Almacen;
use App\Departamento;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Producto;
use App\BitacoraDocumento;
use App\Existencia;
use App\Marca;
use App\Configuracion_Tabla;
use App\VistaRequisicion;
use App\VistaObtenerExistenciaProducto;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\Serie;
use Config;
use Mail;

class RequisicionController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'Compras')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //campos vista
        $this->camposvista = [];
        foreach (explode(",", $this->configuracion_tabla->campos_activados) as $campo){
            array_push($this->camposvista, $campo);
        }
        foreach (explode(",", $this->configuracion_tabla->campos_desactivados) as $campo){
            array_push($this->camposvista, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function requisiciones(){
        $serieusuario = 'A';
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('compras_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('compras_exportar_excel');
        $rutacreardocumento = route('compras_generar_pdfs');
        return view('registros.compras.compras', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }
    
}
