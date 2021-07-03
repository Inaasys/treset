@extends('plantilla_maestra')
@section('titulo')
  Cuentas por Cobrar
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
                                    <h5>&nbsp;&nbsp;&nbsp;CUENTAS POR COBRAR&nbsp;&nbsp;&nbsp;</h5>
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
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="mostrarmodalgenerarpdf()">
                                                        Generar Documento
                                                    </div>
                                                </td>
                                                <td >
                                                    <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" href="{{route('cuentas_por_cobrar_exportar_excel')}}" target="_blank">
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
                            <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover" style="width:100% !important;">
                                    <thead class="{{$empresa->background_tables}}">
                                        <tr>
                                            <th><div style="width:100px !important;">Operaciones</div></th>
                    						@foreach(explode(',', $configuracion_tabla->columnas_ordenadas) as $co) 
                                                @if($co == 'Pago' || $co == 'UUID' || $co == 'Status' || $co == 'NombreCliente' || $co == 'RfcCliente')
                                                    <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">
                                                        {{$co}}
                                                    </th>
                                                @else
                                                    <th>{{$co}}</th>
                                                @endif
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
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
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
                    <div class="col-md-12">
                        <button type="button" class="btn btn-danger btn-sm" onclick="limpiar();limpiarmodales();" data-dismiss="modal">Salir</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardar">Guardar</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardarModificacion">Confirmar Cambios</button>
                    </div> 
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
      		<div class="modal-header {{$empresa->background_forms_and_modals}}">
        		<h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formdesactivar" action="#">
		        	<h5 id="textomodaldesactivar"> </h5>
                    <input type="hidden" id="cxcdesactivar" name="cxcdesactivar">
                    <div id="divmotivobaja">
                        <label>Motivo Baja</label>
                        <textarea class="form-control" name="motivobaja" id="motivobaja" rows=2  required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></textarea>
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
<!-- Modal Timbrado-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modaltimbrado" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header {{$empresa->background_forms_and_modals}}">
        		<h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formtimbrado" action="#">
		        	<h5 id="textomodaltimbrado"> </h5>
                    <input type="hidden" class="form-control" id="pagotimbrado" name="pagotimbrado">
		        </form>	
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="btntimbrarpago">Timbrar Pago</button>
	      	</div>
    	</div>
  	</div>
</div>
<!-- Modal Baja Timbre-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalbajatimbre" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header {{$empresa->background_forms_and_modals}}">
        		<h5 class="modal-title" >Aviso</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formbajatimbre" action="#">
		        	<h5 id="textomodalbajatimbre"> </h5>
                    <input type="hidden" class="form-control" id="iddocumentofacturapi" name="iddocumentofacturapi" readonly>
                    <input type="hidden" class="form-control" id="facturabajatimbre" name="facturabajatimbre" readonly>
		        </form>	
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="btnbajatimbre">Confirmar Baja Timbre</button>
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
        var esquema = '{{$esquema}}';
        var periodohoy = '{{$periodohoy}}';
        var meshoy = '{{$meshoy}}';
        var campos_activados = '{{$configuracion_tabla->campos_activados}}';
        var campos_desactivados = '{{$configuracion_tabla->campos_desactivados}}';
        var columnas_ordenadas = '{{$configuracion_tabla->columnas_ordenadas}}';
        var rfcempresa = '{{$empresa->Rfc}}';
        var nombreempresa = '{{$empresa->Nombre}}';
        var lugarexpedicion = '{{$lugarexpedicion}}';
        var claveregimenfiscal = '{{$claveregimenfiscal}}';
        var regimenfiscal = '{{$regimenfiscal}}';
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';
        var cuentas_por_cobrar_obtener = '{!!URL::to('cuentas_por_cobrar_obtener')!!}';
        var cuentas_por_cobrar_obtener_ultimo_folio = '{!!URL::to('cuentas_por_cobrar_obtener_ultimo_folio')!!}';
        var cuentas_por_cobrar_obtener_fecha_datetime = '{!!URL::to('cuentas_por_cobrar_obtener_fecha_datetime')!!}';
        var cuentas_por_cobrar_obtener_clientes = '{!!URL::to('cuentas_por_cobrar_obtener_clientes')!!}';
        var cuentas_por_cobrar_obtener_cliente_por_numero = '{!!URL::to('cuentas_por_cobrar_obtener_cliente_por_numero')!!}';
        var cuentas_por_cobrar_obtener_facturas_cliente = '{!!URL::to('cuentas_por_cobrar_obtener_facturas_cliente')!!}';
        var cuentas_por_cobrar_obtener_bancos = '{!!URL::to('cuentas_por_cobrar_obtener_bancos')!!}';
        var cuentas_por_cobrar_obtener_banco_por_numero = '{!!URL::to('cuentas_por_cobrar_obtener_banco_por_numero')!!}';
        var cuentas_por_cobrar_obtener_codigos_postales = '{!!URL::to('cuentas_por_cobrar_obtener_codigos_postales')!!}';
        var cuentas_por_cobrar_obtener_lugar_expedicion_por_clave = '{!!URL::to('cuentas_por_cobrar_obtener_lugar_expedicion_por_clave')!!}';
        var cuentas_por_cobrar_obtener_regimenes_fiscales = '{!!URL::to('cuentas_por_cobrar_obtener_regimenes_fiscales')!!}';
        var cuentas_por_cobrar_obtener_regimen_fiscal_por_clave = '{!!URL::to('cuentas_por_cobrar_obtener_regimen_fiscal_por_clave')!!}';
        var cuentas_por_cobrar_obtener_tipos_relacion = '{!!URL::to('cuentas_por_cobrar_obtener_tipos_relacion')!!}';
        var cuentas_por_cobrar_obtener_tipo_relacion_por_clave = '{!!URL::to('cuentas_por_cobrar_obtener_tipo_relacion_por_clave')!!}';
        var cuentas_por_cobrar_obtener_formas_pago = '{!!URL::to('cuentas_por_cobrar_obtener_formas_pago')!!}';
        var cuentas_por_cobrar_obtener_forma_pago_por_clave = '{!!URL::to('cuentas_por_cobrar_obtener_forma_pago_por_clave')!!}';
        var cuentas_por_cobrar_obtener_metodos_pago = '{!!URL::to('cuentas_por_cobrar_obtener_metodos_pago')!!}';
        var cuentas_por_cobrar_obtener_facturas = '{!!URL::to('cuentas_por_cobrar_obtener_facturas')!!}';
        var cuentas_por_cobrar_obtener_factura = '{!!URL::to('cuentas_por_cobrar_obtener_factura')!!}';        
        var cuentas_por_cobrar_obtener_folios_fiscales = '{!!URL::to('cuentas_por_cobrar_obtener_folios_fiscales')!!}';
        var cuentas_por_cobrar_obtener_ultimo_folio_serie_seleccionada = '{!!URL::to('cuentas_por_cobrar_obtener_ultimo_folio_serie_seleccionada')!!}';
        var cuentas_por_cobrar_guardar = '{!!URL::to('cuentas_por_cobrar_guardar')!!}';
        var cuentas_por_cobrar_comprobar_baja = '{!!URL::to('cuentas_por_cobrar_comprobar_baja')!!}';
        var cuentas_por_cobrar_baja  = '{!!URL::to('cuentas_por_cobrar_baja')!!}';
        var cuentas_por_cobrar_obtener_cuenta_por_cobrar =  '{!!URL::to('cuentas_por_cobrar_obtener_cuenta_por_cobrar')!!}';
        var cuentas_por_cobrar_guardar_modificacion = '{!!URL::to('cuentas_por_cobrar_guardar_modificacion')!!}';
        var cuentas_por_cobrar_obtener_datos_envio_email = '{!!URL::to('cuentas_por_cobrar_obtener_datos_envio_email')!!}';
        var cuentas_por_cobrar_enviar_pdfs_email = '{!!URL::to('cuentas_por_cobrar_enviar_pdfs_email')!!}';
        var cuentas_por_cobrar_buscar_folio_string_like =  '{!!URL::to('cuentas_por_cobrar_buscar_folio_string_like')!!}';
        var cuentas_por_cobrar_verificar_si_continua_timbrado=  '{!!URL::to('cuentas_por_cobrar_verificar_si_continua_timbrado')!!}';
        var cuentas_por_cobrar_timbrar_pago=  '{!!URL::to('cuentas_por_cobrar_timbrar_pago')!!}';
        var cuentas_por_cobrar_verificar_si_continua_baja_timbre=  '{!!URL::to('cuentas_por_cobrar_verificar_si_continua_baja_timbre')!!}';
        var cuentas_por_cobrar_baja_timbre=  '{!!URL::to('cuentas_por_cobrar_baja_timbre')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/cuentasporcobrar/cuentasporcobrar.js"></script>
@endsection



