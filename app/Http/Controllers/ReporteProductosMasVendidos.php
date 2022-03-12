<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Jenssegers\Date\Date;
use Helpers;
use DataTables;
use App\Configuracion_Tabla;
use App\Factura;
use App\FacturaDetalle;
use App\NotaCliente;
use App\NotaClienteDetalle;
use App\Cliente;
use App\Agente;
use App\Serie;
use App\Producto;
use App\TipoOrdenCompra;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesFacturasVentasClientesExport;
use DB;

class ReporteProductosMasVendidos extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_productos_mas_vendidos(Request $request){
        $urlgenerarformatoexcel = route('reporte_facturas_ventas_cliente_generar_formato_excel');
        return view('reportes.facturas.reporteproductosmasvendidos', compact('urlgenerarformatoexcel'));
    }
}
