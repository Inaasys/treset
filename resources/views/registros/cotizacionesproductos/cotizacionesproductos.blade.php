@extends('plantilla_maestra')
@section('titulo')
    Cotizaciones Productos
@endsection
@section('additionals_css')
    @include('secciones.libreriascss')
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
            @include('secciones.nombreempresa')
            <!-- Basic Examples -->
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card" id="listadoregistros">
                        <div class="header {{$empresa->background_forms_and_modals}}">
                            <div class="row clearfix">
                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                    <h5>&nbsp;&nbsp;&nbsp;COTIZACIONES PRODUCTOS&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 button-demo">
                                    <div class="table-responsive  navbar-right">
                                        <table>
                                            <tr>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta()">
                                                        Altas
                                                    </div>
                                                </td>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="mostrarmodalgenerarpdf()">
                                                        Generar Documento
                                                    </div>
                                                </td>
                                                <td >
                                                    <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" href="{{route('ordenes_compra_exportar_excel')}}" target="_blank">
                                                        Excel
                                                    </a>
                                                </td>
                                                @if(Auth::user()->role_id == 1)
                                                <td>
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="configurar_tabla()">
                                                        Configurar Tabla
                                                    </div>
                                                </td>
                                                @endif
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select class="select2 form-control" name="periodo" id="periodo" onchange="relistar()" style="width:100% !important;">
                                                @for ($i = $periodoinicial; $i < $periodohoy; $i++)
                                                    <option value="{{$i}}">{{$i}}</option>
                                                @endfor
                                                    <option value="{{$periodohoy}}" selected>{{$periodohoy}}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover display nowrap">
                                    <thead class="{{$empresa->background_tables}}">
                                        <tr>
                                            <th><div style="width:100px !important;">Operaciones</div></th>
                    						@foreach(explode(',', $configuracion_tabla->columnas_ordenadas) as $co) 
                                                    <th id="th{{$co}}">{{$co}}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody ></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Basic Examples -->
    </div>
</section>
<!-- Modal Alta/Modificacion-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="ModalFormulario" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div id="formulario">
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
                    <h4 class="modal-title" id="titulomodal"></h4>
                </div>
                <form id="formplantilla" action="#" enctype="multipart/form-data" hidden>
                    <div class="col-md-12">
                        <table class="col-md-12">
                            <tr>
                                <td>
                                    <div class="col-md-6">
                                        <label>Selecciona el archivo excel</label>
                                        <input type="file" class="form-control" name="partidasexcel" id="partidasexcel" onchange="cargarpartidasexcel(this)" onclick="this.value=null;">
                                        <button type="button" class="btn btn-success btn-sm" id="btnenviarpartidasexcel" style="display:none">Enviar Excel</button>
                                    </div>
                                </td>
                            </tr>
                        </table>   
                    </div>
                </form>
                <form id="formparsley" action="#">
                    <div class="modal-body">
                        <div class="col-md-12" id="tabsform">
                            <!-- aqui van los formularios de alta o modificacion y se agregan automaticamente con jquery -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-sm" onclick="limpiar();limpiarmodales();" data-dismiss="modal">Salir</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardar">Guardar</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardarModificacion">Confirmar Cambios</button>
                    </div>
                </form> 
            </div>
            <div id="contenidomodaltablas">
                <!-- aqui van las tablas de seleccion y se agregan automaticamente con jquery -->
            </div> 
        </div>
    </div>
</div>
<!-- Modal Baja o Alta-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="estatusregistro" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header {{$empresa->background_forms_and_modals}}">
        		<h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formdesactivar" action="#">
		        	<h5 id="textomodaldesactivar"> </h5>
                    <input type="hidden" id="cotizaciondesactivar" name="cotizaciondesactivar">
                    <div id="divmotivobaja">
                        <label>Motivo Baja</label>
                        <textarea class="form-control" name="motivobaja" id="motivobaja" rows=2 data-parsley-length="[1, 200]" onkeyup="tipoLetra(this)" required></textarea>
                    </div>
		        </form>	
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="btnbaja">Confirmar Baja</button>
	      	</div>
    	</div>
  	</div>
</div> 
<!-- modal para crear documento en PDF-->
@include('secciones.modalcreardocumento')
<!-- fin modal para crear documento en PDF-->
<!-- modal para enviar por email documento en PDF-->
@include('secciones.modalenviardocumentoemail')
<!-- fin modal para enviar por email documento en PDF-->
<!-- modal para configuraciones de tablas-->
@include('secciones.modalconfiguraciontablas')
<!-- fin modal para configuraciones de tablas-->
@endsection
@section('additionals_js')
    <script>
        /*urls y variables renderizadas con blade*/
        var mayusculas_sistema = '{{$mayusculas_sistema}}';
        var numerodecimales = '{{$numerodecimales}}';
        var numerocerosconfigurados = '{{$numerocerosconfigurados}}';
        var numerocerosconfiguradosinputnumberstep = '{{$numerocerosconfiguradosinputnumberstep}}';
        var serieusuario = '{{$serieusuario}}';
        var meshoy = '{{$meshoy}}';
        var periodohoy = '{{$periodohoy}}';
        var campos_activados = '{{$configuracion_tabla->campos_activados}}';
        var campos_desactivados = '{{$configuracion_tabla->campos_desactivados}}';
        var columnas_ordenadas = '{{$configuracion_tabla->columnas_ordenadas}}';
        var primerordenamiento = '{{$configuracion_tabla->primerordenamiento}}';
        var formaprimerordenamiento = '{{$configuracion_tabla->formaprimerordenamiento}}';
        var segundoordenamiento = '{{$configuracion_tabla->segundoordenamiento}}';
        var formasegundoordenamiento= '{{$configuracion_tabla->formasegundoordenamiento}}';
        var tercerordenamiento = '{{$configuracion_tabla->tercerordenamiento}}';
        var formatercerordenamiento = '{{$configuracion_tabla->formatercerordenamiento}}';
        var campos_busquedas = '{{$configuracion_tabla->campos_busquedas}}';
        var nombreempresa = '{{$empresa->Nombre}}';
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';
        var urlgenerarplantilla = '{{$urlgenerarplantilla}}';
        var cotizaciones_productos_obtener = '{!!URL::to('cotizaciones_productos_obtener')!!}';
        var cotizaciones_productos_descargar_plantilla = '{!!URL::to('cotizaciones_productos_descargar_plantilla')!!}';
        var cotizaciones_productos_cargar_partidas_excel = '{!!URL::to('cotizaciones_productos_cargar_partidas_excel')!!}';
        var cotizaciones_productos_obtener_series_documento = '{!!URL::to('cotizaciones_productos_obtener_series_documento')!!}';
        var cotizaciones_productos_obtener_ultimo_folio_serie_seleccionada = '{!!URL::to('cotizaciones_productos_obtener_ultimo_folio_serie_seleccionada')!!}';
        var cotizaciones_productos_obtener_ultimo_folio = '{!!URL::to('cotizaciones_productos_obtener_ultimo_folio')!!}';
        var cotizaciones_productos_obtener_fecha_actual_datetimelocal = '{!!URL::to('cotizaciones_productos_obtener_fecha_actual_datetimelocal')!!}';
        var cotizaciones_productos_obtener_tipos_ordenes_compra = '{!!URL::to('cotizaciones_productos_obtener_tipos_ordenes_compra')!!}';
        var cotizaciones_productos_obtener_clientes = '{!!URL::to('cotizaciones_productos_obtener_clientes')!!}';
        var cotizaciones_productos_obtener_agentes = '{!!URL::to('cotizaciones_productos_obtener_agentes')!!}';
        var cotizaciones_productos_obtener_cliente_por_numero = '{!!URL::to('cotizaciones_productos_obtener_cliente_por_numero')!!}';
        var cotizaciones_productos_obtener_agente_por_numero = '{!!URL::to('cotizaciones_productos_obtener_agente_por_numero')!!}';
        var cotizaciones_productos_obtener_productos = '{!!URL::to('cotizaciones_productos_obtener_productos')!!}';
        var cotizaciones_productos_obtener_producto_por_codigo = '{!!URL::to('cotizaciones_productos_obtener_producto_por_codigo')!!}';
        var cotizaciones_productos_obtener_existencias_almacen_uno = '{!!URL::to('cotizaciones_productos_obtener_existencias_almacen_uno')!!}';
        var cotizaciones_productos_obtener_nuevo_saldo_cliente = '{!!URL::to('cotizaciones_productos_obtener_nuevo_saldo_cliente')!!}';
        var cotizaciones_productos_guardar = '{!!URL::to('cotizaciones_productos_guardar')!!}';
        var cotizaciones_productos_verificar_baja = '{!!URL::to('cotizaciones_productos_verificar_baja')!!}'; 
        var cotizaciones_productos_bajas = '{!!URL::to('cotizaciones_productos_bajas')!!}'; 
        var cotizaciones_productos_obtener_cotizacion_producto = '{!!URL::to('cotizaciones_productos_obtener_cotizacion_producto')!!}'; 
        var cotizaciones_productos_guardar_modificacion = '{!!URL::to('cotizaciones_productos_guardar_modificacion')!!}';
        var cotizaciones_productos_obtener_datos_envio_email = '{!!URL::to('cotizaciones_productos_obtener_datos_envio_email')!!}';
        var cotizaciones_productos_enviar_pdfs_email = '{!!URL::to('cotizaciones_productos_enviar_pdfs_email')!!}';
        var cotizaciones_productos_enviar_pdfs_cliente_email = '{!!URL::to('cotizaciones_productos_enviar_pdfs_cliente_email')!!}';
        var cotizaciones_productos_buscar_folio_string_like = '{!!URL::to('cotizaciones_productos_buscar_folio_string_like')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/cotizacionesproductos/cotizacionesproductos.js"></script>
@endsection



