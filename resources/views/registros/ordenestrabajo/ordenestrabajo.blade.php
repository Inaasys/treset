@extends('plantilla_maestra')
@section('titulo')
  Ordenes de Trabajo
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
                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                    <h5>&nbsp;&nbsp;&nbsp;ORDENES DE TRABAJO&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12 button-demo">
                                    <div class="table-responsive navbar-right">
                                        <table>
                                            <tr>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta()">
                                                        Altas
                                                    </div>
                                                </td>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="mostrarmodalgenerarpdf(0,1,'OrdenTrabajo')">
                                                        Generar Documento
                                                    </div>
                                                </td>
                                                <td >
                                                    <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" href="{{route('ordenes_trabajo_exportar_excel')}}" target="_blank">
                                                        Excel
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="configurar_tabla()">
                                                        Configurar Tabla
                                                    </div>
                                                </td>
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
                            <div class="row" hidden>
                                <div class="col-md-12">
                                    <iframe  id="pdfiframe" name="pdfiframe" src="#"></iframe>
                                </div>
                            </div>
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
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th><div style="width:100px !important;">Operaciones</div></th>
                    						@foreach(explode(',', $configuracion_tabla->columnas_ordenadas) as $co)
                                                <th id="th{{$co}}">{{$co}}</th>
                                            @endforeach
                                        </tr>
                                    </tfoot>
                                </table>
                                @if($mostrartotalesdecolumnasendocumentos == 'S')
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped table-hover display nowrap" style="font-size:10px;">
                                                <tr class="{{$empresa->background_forms_and_modals}}">
                                                    <th>Sum Importe: <b id="sumaimportefiltrado"></b></th>
                                                    <th>Sum Descuento: <b id="sumadescuentofiltrado"></b></th>
                                                    <th>Sum SubTotal: <b id="sumasubtotalfiltrado"></b></th>
                                                    <th>Sum Iva: <b id="sumaivafiltrado"></b></th>
                                                    <th>Sum Total: <b id="sumatotalfiltrado"></b></th>
                                                    <th>Sum Costo: <b id="sumacostofiltrado"></b></th>
                                                    <th>Sum Comisión: <b id="sumacomisionfiltrado"></b></th>
                                                    <th>Sum Utilidad: <b id="sumautilidadfiltrado"></b></th>
                                                </tr>
                                            </table>
                                        </div>
                                @endif
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
<!-- Modal Seleccion Técnico(s) que realizo/realizaron el servicio y asignación de tiempos-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalasignaciontecnicos" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
            <div id="asignaciontecnicosformulario">
                <div id="tablatecnicos">
                </div>
            </div>
            <div id="asignaciontecnicoscontenidomodaltablas">
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
                    <input type="hidden" id="ordendesactivar" name="ordendesactivar">
                    <div id="divmotivobaja">
                        <label>Motivo Baja</label>
                        <textarea class="form-control" name="motivobaja" id="motivobaja" rows=2 onkeyup="tipoLetra(this)" required data-parsley-length="[1, 200]"></textarea>
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
<!-- Modal Terminar Orden de Trabajo-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalterminarorden" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header {{$empresa->background_forms_and_modals}}">
        		<h5 class="modal-title" id="exampleModalLabel">Ordenes de Trabajo Terminar</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formterminar" action="#">
                      <div class="row">
                          <div class="col-md-2">
                              <label >Orden</label>
                            <input type="text" class="form-control" id="ordenterminar" name="ordenterminar" readonly>
                          </div>
                          <div class="col-md-5">
                              <label >Cliente</label>
                            <input type="text" class="form-control" id="clienteordenterminar" name="clienteordenterminar" readonly>
                          </div>
                          <div class="col-md-5">
                              <label >Fecha</label>
                            <input type="datetime-local" class="form-control" id="fechaordenterminar" name="fechaordenterminar" readonly>
                          </div>
                      </div>
		        </form>
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="btnterminar">Terminar Orden Trabajo</button>
	      	</div>
    	</div>
  	</div>
</div>
<!-- Modal ABRIR de nuevo Orden de Trabajo-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalabrirorden" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header {{$empresa->background_forms_and_modals}}">
        		<h5 class="modal-title" id="exampleModalLabel">Ordenes de Trabajo Abrir Nuevamente</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formabrirnuevamente" action="#">
                      <div class="row">
                          <div class="col-md-12">
                              <h4 id="tituloabrirnuevamenteorden"></h4>
                          </div>
                          <div class="col-md-2" hidden>
                              <label >Orden</label>
                            <input type="text" class="form-control" id="ordenabrir" name="ordenabrir" readonly>
                          </div>
                      </div>
		        </form>
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="btnabrirnuevamente">Abrir Nuevamente Orden Trabajo</button>
	      	</div>
    	</div>
  	</div>
</div>
<!-- Modal modificar datos generales Orden de Trabajo-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalmodificardatosgeneralesorden" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header {{$empresa->background_forms_and_modals}}">
        		<h5 class="modal-title" id="exampleModalLabel">Ordenes de Trabajo Modificar Datos Generales</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formamodificardatosgenerales" action="#">
                      <div class="row">
                          <div class="col-md-12">
                              <h4 id="titulomodificardatosgenerales"></h4>
                          </div>
                          <div class="col-md-2">
                                <label >Orden</label>
                                <input type="text" class="form-control inputnextdet" id="ordenmodificardatosgenerales" name="ordenmodificardatosgenerales" readonly>
                          </div>
                          <div class="col-md-2">
                                <label>Kilometros</label>
                                <input type="text" class="form-control inputnextdet" id="kilometrosdatosgenerales" name="kilometrosdatosgenerales" onkeyup="tipoLetra(this)">
                          </div>
                          <div class="col-md-2">
                                <label>Placas</label>
                                <input type="text" class="form-control inputnextdet" id="placasdatosgenerales" name="placasdatosgenerales" onkeyup="tipoLetra(this)" >
                          </div>
                          <div class="col-md-2">
                                <label>Economico</label>
                                <input type="text" class="form-control inputnextdet" id="economicodatosgenerales" name="economicodatosgenerales" onkeyup="tipoLetra(this)">
                          </div>
                          <div class="col-md-2">
                                <label>Orden Cliente</label>
                                <input type="text" class="form-control inputnextdet" id="ordenclientedatosgenerales" name="ordenclientedatosgenerales" onkeyup="tipoLetra(this)">
                          </div>
                      </div>
		        </form>
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="btnguardardatosgenerales">Confirmar Cambios</button>
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
        var primerordenamiento = '{{$configuracion_tabla->primerordenamiento}}';
        var formaprimerordenamiento = '{{$configuracion_tabla->formaprimerordenamiento}}';
        var segundoordenamiento = '{{$configuracion_tabla->segundoordenamiento}}';
        var formasegundoordenamiento= '{{$configuracion_tabla->formasegundoordenamiento}}';
        var tercerordenamiento = '{{$configuracion_tabla->tercerordenamiento}}';
        var formatercerordenamiento = '{{$configuracion_tabla->formatercerordenamiento}}';
        var campos_busquedas = '{{$configuracion_tabla->campos_busquedas}}';
        var nombreempresa = '{{$empresa->Nombre}}';
        var usuario = '{!!Auth::user()->user!!}';
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';
        var urlgenerarplantilla = '{{$urlgenerarplantilla}}';
        var rol_usuario_logueado = '{{Auth::user()->role_id}}';
        var urlpdfsimpresionesrapidas = '{{asset("xml_descargados/")}}/';
        var ordenes_trabajo_obtener = '{!!URL::to('ordenes_trabajo_obtener')!!}';
        var ordenes_trabajo_descargar_plantilla = '{!!URL::to('ordenes_trabajo_descargar_plantilla')!!}';
        var ordenes_trabajo_cargar_partidas_excel = '{!!URL::to('ordenes_trabajo_cargar_partidas_excel')!!}';
        var ordenes_trabajo_obtener_series_documento = '{!!URL::to('ordenes_trabajo_obtener_series_documento')!!}';
        var ordenes_trabajo_obtener_ultimo_folio_serie_seleccionada = '{!!URL::to('ordenes_trabajo_obtener_ultimo_folio_serie_seleccionada')!!}';
        var ordenes_trabajo_obtener_ultimo_folio = '{!!URL::to('ordenes_trabajo_obtener_ultimo_folio')!!}';
        var ordenes_trabajo_obtener_tipos_ordenes_trabajo = '{!!URL::to('ordenes_trabajo_obtener_tipos_ordenes_trabajo')!!}';
        var ordenes_trabajo_obtener_tipos_unidades = '{!!URL::to('ordenes_trabajo_obtener_tipos_unidades')!!}';
        var ordenes_trabajo_obtener_fecha_actual_datetimelocal = '{!!URL::to('ordenes_trabajo_obtener_fecha_actual_datetimelocal')!!}';
        var ordenes_trabajo_obtener_clientes_facturaa = '{!!URL::to('ordenes_trabajo_obtener_clientes_facturaa')!!}';
        var ordenes_trabajo_obtener_cliente_facturaa_por_numero = '{!!URL::to('ordenes_trabajo_obtener_cliente_facturaa_por_numero')!!}';
        var ordenes_trabajo_obtener_clientes_delcliente = '{!!URL::to('ordenes_trabajo_obtener_clientes_delcliente')!!}';
        var ordenes_trabajo_obtener_cliente_delcliente_por_numero = '{!!URL::to('ordenes_trabajo_obtener_cliente_delcliente_por_numero')!!}';
        var ordenes_trabajo_obtener_agentes = '{!!URL::to('ordenes_trabajo_obtener_agentes')!!}';
        var ordenes_trabajo_obtener_agente_por_numero = '{!!URL::to('ordenes_trabajo_obtener_agente_por_numero')!!}';
        var ordenes_trabajo_obtener_vines = '{!!URL::to('ordenes_trabajo_obtener_vines')!!}';
        var ordenes_trabajo_obtener_vin_por_numero = '{!!URL::to('ordenes_trabajo_obtener_vin_por_numero')!!}';
        var ordenes_trabajo_obtener_cotizaciones = '{!!URL::to('ordenes_trabajo_obtener_cotizaciones')!!}';
        var ordenes_trabajo_obtener_cotizacion = '{!!URL::to('ordenes_trabajo_obtener_cotizacion')!!}';
        var ordenes_trabajo_obtener_servicios = '{!!URL::to('ordenes_trabajo_obtener_servicios')!!}';
        var ordenes_trabajo_obtener_servicio_por_codigo = '{!!URL::to('ordenes_trabajo_obtener_servicio_por_codigo')!!}';
        var ordenes_trabajo_obtener_tecnicos = '{!!URL::to('ordenes_trabajo_obtener_tecnicos')!!}';
        var ordenes_trabajo_guardar = '{!!URL::to('ordenes_trabajo_guardar')!!}';
        var ordenes_trabajo_verificar_uso_en_modulos = '{!!URL::to('ordenes_trabajo_verificar_uso_en_modulos')!!}';
        var ordenes_trabajo_alta_o_baja = '{!!URL::to('ordenes_trabajo_alta_o_baja')!!}';
        var ordenes_trabajo_verificar_status_orden = '{!!URL::to('ordenes_trabajo_verificar_status_orden')!!}';
        var ordenes_trabajo_terminar_orden = '{!!URL::to('ordenes_trabajo_terminar_orden')!!}';
        var ordenes_trabajo_verificar_abrir_nuevamente_orden = '{!!URL::to('ordenes_trabajo_verificar_abrir_nuevamente_orden')!!}';
        var ordenes_trabajo_abrir_nuevamente_orden = '{!!URL::to('ordenes_trabajo_abrir_nuevamente_orden')!!}';
        var ordenes_trabajo_obtener_orden_trabajo = '{!!URL::to('ordenes_trabajo_obtener_orden_trabajo')!!}';
        var ordenes_trabajo_guardar_modificacion = '{!!URL::to('ordenes_trabajo_guardar_modificacion')!!}';
        var ordenes_trabajo_obtener_datos_generales_orden = '{!!URL::to('ordenes_trabajo_obtener_datos_generales_orden')!!}';
        var ordenes_trabajo_guardar_modificacion_datos_generales = '{!!URL::to('ordenes_trabajo_guardar_modificacion_datos_generales')!!}';
        var ordenes_trabajo_obtener_datos_envio_email = '{!!URL::to('ordenes_trabajo_obtener_datos_envio_email')!!}';
        var ordenes_trabajo_enviar_pdfs_email = '{!!URL::to('ordenes_trabajo_enviar_pdfs_email')!!}';
        var ordenes_trabajo_buscar_folio_string_like = '{!!URL::to('ordenes_trabajo_buscar_folio_string_like')!!}';
        var ordenes_trabajo_generar_pdfs = '{!!URL::to('ordenes_trabajo_generar_pdfs')!!}';
        var ordenes_trabajo_bloquear_desbloquear = '{!!URL::to('ordenes_trabajo_bloquear_desbloquear') !!}';
        var ordenes_trabajo_validar_numero_partes = '{!!URL::to('ordenes_trabajo_validar_numero_partes')!!}'
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="{{ asset('scripts_inaasys/registros/ordenestrabajo/ordenestrabajo.js') }}"></script>
@endsection
