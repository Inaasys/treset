@extends('plantilla_maestra')
@section('titulo')
  Compras
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
                                <div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">
                                    <h5>&nbsp;&nbsp;&nbsp;COMPRAS&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-10 col-md-10 col-sm-10 col-xs-12 button-demo">
                                    <div class="table-responsive navbar-right">
                                        <table>
                                            <tr>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta('PRODUCTOS')">
                                                        Altas Prod
                                                    </div>
                                                </td>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta('GASTOS')">
                                                        Altas Gastos
                                                    </div>
                                                </td>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta('TOT')">
                                                        Altas Tot
                                                    </div>
                                                </td>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="mostrarmodalgenerarpdf(0,1,'Compra')">
                                                        Generar Documento
                                                    </div>
                                                </td>
                                                <td >
                                                    <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" href="{{route('compras_exportar_excel')}}" target="_blank">
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
                                            <select class="form-control select2" name="periodo" id="periodo" onchange="relistar()" style="width:100% !important;">
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
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover display nowrap" >
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
                                                    <th>Sum Abonos: <b id="sumaabonosfiltrado"></b></th>
                                                    <th>Sum Descuentos: <b id="sumadescuentosfiltrado"></b></th>
                                                    <th>Sum Saldo: <b id="sumasaldofiltrado"></b></th>
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
                <div class="modal-body">
                    <form id="formxml" action="#" enctype="multipart/form-data">
                        <div class="col-md-12">
                            <table class="col-md-12">
                                <tr>
                                    <td>
                                        <div class="col-md-6">
                                            <input type="text" hidden name="altamodificacion" id="altamodificacion">
                                            <label>Selecciona el xml</label>
                                            <input type="file" class="form-control" name="xml" id="xml" onchange="cambiodexml(this)" onclick="this.value=null;">
                                            <button type="button" class="btn btn-success btn-sm" id="btnenviarxml" style="display:none">Enviar XML</button>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Folio Fiscal</label>
                                            <input type="text" class="form-control" name="uuidxml" id="uuidxml" readonly required>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </form>
                    <form id="formparsley" action="#">
                        <div class="col-md-12" id="tabsform">
                            <!-- aqui van los formularios de alta o modificacion y se agregan automaticamente con jquery -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="col-md-4">
                        <h5 style="color:#F44336 !important;"  id="totalfacturaproveedor"></h5>
                    </div>
                    <div class="col-md-4">
                        <h5 style="color:#F44336 !important;" id="diferenciafacturaproveedor"></h5>
                    </div>
                    <div class="col-md-4">
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
                    <input type="hidden" id="compradesactivar" name="compradesactivar">
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
<!-- Modal Movimientos-->
<div class="modal fade" data-keyboard="false" id="modalmovimientoscompra" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header {{$empresa->background_forms_and_modals}}">
        		<h5 class="modal-title" id="exampleModalLabel">Movimientos</h5>
      		</div>
      		<div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="{{$empresa->background_tables}}">
                                <tr>
                                    <td >Movimiento</td>
                                    <td >Número</td>
                                    <td >Fecha</td>
                                    <td >Abono $</td>
                                    <td >Status</td>
                                </tr>
                            </thead>
                            <tbody id="filasmovimientos"></tbody>
                        </table>
                    </div>
                </div>
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
        var periodohoy = '{{$periodohoy}}';
        var meshoy = '{{$meshoy}}';
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
        var rfcreceptor = '{{$empresa->Rfc}}';
        var nombrereceptor = '{{$empresa->Nombre}}';
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';
        var ligarOTaCompra = '{{$empresa->ligarOTaCompra}}'
        var rol_usuario_logueado = '{{Auth::user()->role_id}}';
        var urlpdfsimpresionesrapidas = '{{asset("xml_descargados/")}}/';
        var compras_obtener = '{!!URL::to('compras_obtener')!!}';
        var compras_obtener_series_documento = '{!!URL::to('compras_obtener_series_documento')!!}';
        var compras_obtener_ultimo_folio_serie_seleccionada = '{!!URL::to('compras_obtener_ultimo_folio_serie_seleccionada')!!}';
        var compras_obtener_ultimo_folio = '{!!URL::to('compras_obtener_ultimo_folio')!!}';
        var ordenes_compra_obtener_fecha_actual_datetimelocal = '{!!URL::to('ordenes_compra_obtener_fecha_actual_datetimelocal')!!}';
        var compras_obtener_tipos_ordenes_compra = '{!!URL::to('compras_obtener_tipos_ordenes_compra')!!}';
        var compras_cargar_xml_alta = '{!!URL::to('compras_cargar_xml_alta')!!}';
        var compras_obtener_proveedores = '{!!URL::to('compras_obtener_proveedores')!!}';
        var compras_obtener_almacenes = '{!!URL::to('compras_obtener_almacenes')!!}';
        var compras_obtener_almacen_por_numero = '{!!URL::to('compras_obtener_almacen_por_numero')!!}';
        var compras_obtener_productos = '{!!URL::to('compras_obtener_productos')!!}';
        var compras_obtener_producto_por_codigo = '{!!URL::to('compras_obtener_producto_por_codigo')!!}';
        var compras_obtener_proveedor_por_numero = '{!!URL::to('compras_obtener_proveedor_por_numero')!!}';
        var compras_obtener_ordenes_compra = '{!!URL::to('compras_obtener_ordenes_compra')!!}';
        var compras_obtener_orden_compra = '{!!URL::to('compras_obtener_orden_compra')!!}';
        var compras_obtener_departamentos = '{!!URL::to('compras_obtener_departamentos')!!}';
        var compras_obtener_claves_productos = '{!!URL::to('compras_obtener_claves_productos')!!}';
        var compras_obtener_claves_unidades = '{!!URL::to('compras_obtener_claves_unidades')!!}';
        var compras_guardar = '{!!URL::to('compras_guardar')!!}';
        var compras_obtener_movimientos_compra = '{!!URL::to('compras_obtener_movimientos_compra')!!}';
        var compras_obtener_compra = '{!!URL::to('compras_obtener_compra')!!}';
        var compras_obtener_existencias_partida = '{!!URL::to('compras_obtener_existencias_partida')!!}';
        var compras_obtener_existencias_almacen = '{!!URL::to('compras_obtener_existencias_almacen')!!}';
        var compras_obtener_valor_modificacionpermitida = '{!!URL::to('compras_obtener_valor_modificacionpermitida')!!}';
        var compras_guardar_modificacion = '{!!URL::to('compras_guardar_modificacion')!!}';
        var compras_verificar_uso_en_modulos =  '{!!URL::to('compras_verificar_uso_en_modulos')!!}';
        var compras_alta_o_baja = '{!!URL::to('compras_alta_o_baja')!!}';
        var compras_obtener_datos_envio_email = '{!!URL::to('compras_obtener_datos_envio_email')!!}';
        var compras_enviar_pdfs_email = '{!!URL::to('compras_enviar_pdfs_email')!!}';
        var compras_generar_pdfs = '{!!URL::to('compras_generar_pdfs')!!}';
        var compras_buscar_folio_string_like = '{!!URL::to('compras_buscar_folio_string_like')!!}';
        var urlgenerarformatoexceldetalles = '{{$urlgenerarformatoexceldetalles}}';
        let orden_de_trabajo_exportar_excel = '{!! URL::to('orden_de_trabajo_exportar_excel')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="{{ asset('scripts_inaasys/registros/compras/compras.js') }}"></script>
@endsection
