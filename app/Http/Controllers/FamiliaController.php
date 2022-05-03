<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; //para envio de formularios
use Illuminate\Support\Facades\Log;//para crear archivos .log
use Illuminate\Support\Facades\Auth;//para obtener datos de usuario logueado
use Carbon\Carbon;//manejo de fechas
use Helpers;//libreria interna con funciones globales
use DataTables;//datatables
use Maatwebsite\Excel\Facades\Excel;//archivos excel    
use App\Exports\LineasExport;//archivo para exportar a excel
use App\Linea;//modelo del controlador

class FamiliaController extends Controller
{
    //
}
