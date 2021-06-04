@extends('plantilla_maestra')
@section('titulo')
  Remisiones
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
                        <div class="header bg-red">
                            <div class="row clearfix">
                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                    <h5>&nbsp;&nbsp;&nbsp;REMISIONES&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 button-demo">
                                    <div class="table-responsive">
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
                                                    <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" href="{{route('remisiones_exportar_excel')}}" target="_blank">
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
                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                    <div class="row">
                                        <div class="col-md-7 col-md-offset-4">
                                            <select class="select2 form-control" name="periodo" id="periodo" onchange="relistar()" style="width75% !important;">
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
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover">
                                    <thead class="customercolor">
                                        <tr>
                                            <th><div style="width:100px !important;">Operaciones</div></th>
                    						@foreach(explode(',', $configuracion_tabla->columnas_ordenadas) as $co) 
                                            <th>{{$co}}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
                <div class="modal-header bg-red">
                    <h4 class="modal-title" id="titulomodal"></h4>
                </div>
                <div class="modal-body">
                    <form id="formparsley" action="#">
                        
                        <div class="col-md-12" id="tabsform">
                            <!-- aqui van los formularios de alta o modificacion y se agregan automaticamente con jquery -->
                        </div>
                    </form>
                </div> 
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" onclick="limpiar();limpiarmodales();" data-dismiss="modal">Salir</button>
                    <button type="button" class="btn btn-success btn-sm" id="btnGuardar">Guardar</button>
                    <button type="button" class="btn btn-success btn-sm" id="btnGuardarModificacion">Confirmar Cambios</button>
                </div>
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
      		<div class="modal-header bg-red">
        		<h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formdesactivar" action="#">
		        	<h5 id="textomodaldesactivar"> </h5>
                    <input type="hidden" id="remisiondesactivar" name="remisiondesactivar">
                    <div id="divmotivobaja">
                        <label>Motivo Baja</label>
                        <textarea class="form-control" name="motivobaja" id="motivobaja" rows=2 onkeyup="tipoLetra(this)" required data-parsley-length="[1, 255]"></textarea>
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
        var meshoy = '{{$meshoy}}';
        var periodohoy = '{{$periodohoy}}';
        var serieusuario = '{{$serieusuario}}';
        var campos_activados = '{{$configuracion_tabla->campos_activados}}';
        var campos_desactivados = '{{$configuracion_tabla->campos_desactivados}}';
        var columnas_ordenadas = '{{$configuracion_tabla->columnas_ordenadas}}';
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var remisiones_obtener = '{!!URL::to('remisiones_obtener')!!}';
        var remisiones_obtener_series_documento = '{!!URL::to('remisiones_obtener_series_documento')!!}';
        var remisiones_obtener_ultimo_folio_serie_seleccionada = '{!!URL::to('remisiones_obtener_ultimo_folio_serie_seleccionada')!!}';
        var remisiones_obtener_ultimo_folio = '{!!URL::to('remisiones_obtener_ultimo_folio')!!}';
        var ordenes_compra_obtener_fecha_actual_datetimelocal = '{!!URL::to('ordenes_compra_obtener_fecha_actual_datetimelocal')!!}';
        var remisiones_obtener_clientes = '{!!URL::to('remisiones_obtener_clientes')!!}';
        var remisiones_obtener_cliente_por_numero = '{!!URL::to('remisiones_obtener_cliente_por_numero')!!}';
        var remisiones_obtener_agente_por_numero = '{!!URL::to('remisiones_obtener_agente_por_numero')!!}';
        var remisiones_obtener_almacen_por_numero = '{!!URL::to('remisiones_obtener_almacen_por_numero')!!}';
        var remisiones_obtener_agentes = '{!!URL::to('remisiones_obtener_agentes')!!}';
        var remisiones_obtener_almacenes = '{!!URL::to('remisiones_obtener_almacenes')!!}';
        var remisiones_obtener_tipos_cliente = '{!!URL::to('remisiones_obtener_tipos_cliente')!!}';
        var remisiones_obtener_tipos_unidad = '{!!URL::to('remisiones_obtener_tipos_unidad')!!}';
        var remisiones_obtener_productos = '{!!URL::to('remisiones_obtener_productos')!!}';
        var remisiones_obtener_existencias_almacen = '{!!URL::to('remisiones_obtener_existencias_almacen')!!}';
        var remisiones_guardar = '{!!URL::to('remisiones_guardar')!!}';
        var remisiones_verificar_baja = '{!!URL::to('remisiones_verificar_baja')!!}';
        var remisiones_alta_o_baja = '{!!URL::to('remisiones_alta_o_baja')!!}'; 
        var remisiones_obtener_remision = '{!!URL::to('remisiones_obtener_remision')!!}'; 
        var remisiones_guardar_modificacion = '{!!URL::to('remisiones_guardar_modificacion')!!}';
        var remisiones_obtener_datos_envio_email = '{!!URL::to('remisiones_obtener_datos_envio_email')!!}';
        var remisiones_enviar_pdfs_email = '{!!URL::to('remisiones_enviar_pdfs_email')!!}';
        var remisiones_buscar_folio_string_like = '{!!URL::to('remisiones_buscar_folio_string_like')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/remisiones/remisiones.js"></script>
@endsection



