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

class FolioComprobanteCartaPorteController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //API FACTURAPI 
        $this->facturapi = new Facturapi( config('app.keygeneralfacturapi') ); //
    }
    
    public function folios_comprobantes_cartasporte(){
        return view('catalogos.foliosfiscales.folioscomprobantescartasporte');
    }
}
