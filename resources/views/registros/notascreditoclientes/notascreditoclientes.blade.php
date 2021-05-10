@extends('plantilla_maestra')
@section('titulo')
  Notas Crédito Clientes
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
                                <div class="col-lg-10 col-md-10 col-sm-10 col-xs-12 table-responsive button-demo">
                                    <table>
                                        <tr>
                                            <td>
                                                <h5>&nbsp;&nbsp;&nbsp;&nbsp;Notas Crédito Clientes&nbsp;&nbsp;&nbsp;</h5>
                                            </td>
                                            <td >
                                                <div class="btn bg-blue btn-xs waves-effect" onclick="alta('PROD')">
                                                    Altas
                                                </div>
                                            </td>
                                            <td >
                                                <div class="btn bg-blue btn-xs waves-effect" onclick="mostrarmodalgenerarpdf()">
                                                    Generar Documento
                                                </div>
                                            </td>
                                            <td >
                                                <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" href="{{route('notas_credito_clientes_exportar_excel')}}" target="_blank">
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
                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                    <div class="row">
                                        <div class="col-md-7 col-md-offset-4">
                                            <select class="form-control select2" name="periodo" id="periodo" onchange="relistar()" style="width75% !important;">
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
                                            <th><div style="width:80px !important;">Operaciones</div></th>
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
                    <div class="col-md-12">
                        <button type="button" class="btn btn-danger btn-sm" onclick="limpiar();limpiarmodales();" data-dismiss="modal">Salir</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardar">Guardar</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardarModificacion">Guardar</button>
                    </div> 
                </div>
            </div>
            <div id="contenidomodaltablas">
                <!-- aqui van las tablas de seleccion y se agregan automaticamente con jquery -->
            </div> 
        </div>
    </div>
</div>
<!-- Modal Autorizar Orden de Compra-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="autorizarorden" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header bg-red">
        		<h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formautorizar" action="#">
		        	Estas seguro de autorizar la orden de compra?
		        	<input type="hidden" id="ordenautorizar" name="ordenautorizar">
		        </form>	
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="btnautorizar">Guardar</button>
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
                    <input type="hidden" id="notadesactivar" name="notadesactivar">
                    <div id="divmotivobaja">
                        <label>Motivo Baja</label>
                        <textarea class="form-control" name="motivobaja" id="motivobaja" rows=2 required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></textarea>
                    </div>
		        </form>	
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="btnbaja">Guardar</button>
	      	</div>
    	</div>
  	</div>
</div>
<!-- modal para crear documento en PDF-->
@include('secciones.modalcreardocumento')
<!-- fin modal para crear documento en PDF-->
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
        var lugarexpedicion = '{{$empresa->LugarExpedicion}}';
        var claveregimenfiscal = '{{$claveregimenfiscal}}';
        var regimenfiscal = '{{$regimenfiscal}}';
        var clavetiporelacion = '{{$tiporelacion->Clave}}';
        var tiporelacion = '{{$tiporelacion->Nombre}}';
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var notas_credito_clientes_obtener = '{!!URL::to('notas_credito_clientes_obtener')!!}';
        var notas_credito_clientes_obtener_ultimo_folio = '{!!URL::to('notas_credito_clientes_obtener_ultimo_folio')!!}';
        var notas_credito_clientes_obtener_clientes = '{!!URL::to('notas_credito_clientes_obtener_clientes')!!}';
        var notas_credito_clientes_obtener_almacenes = '{!!URL::to('notas_credito_clientes_obtener_almacenes')!!}';
        var notas_credito_clientes_obtener_codigos_postales = '{!!URL::to('notas_credito_clientes_obtener_codigos_postales')!!}';
        var notas_credito_clientes_obtener_regimenes_fiscales = '{!!URL::to('notas_credito_clientes_obtener_regimenes_fiscales')!!}';
        var notas_credito_clientes_obtener_tipos_relacion = '{!!URL::to('notas_credito_clientes_obtener_tipos_relacion')!!}';
        var notas_credito_clientes_obtener_formas_pago = '{!!URL::to('notas_credito_clientes_obtener_formas_pago')!!}';
        var notas_credito_clientes_obtener_metodos_pago = '{!!URL::to('notas_credito_clientes_obtener_metodos_pago')!!}';
        var notas_credito_clientes_obtener_usos_cfdi = '{!!URL::to('notas_credito_clientes_obtener_usos_cfdi')!!}';
        var notas_credito_clientes_obtener_residencias_fiscales = '{!!URL::to('notas_credito_clientes_obtener_residencias_fiscales')!!}';
        var notas_credito_clientes_obtener_facturas = '{!!URL::to('notas_credito_clientes_obtener_facturas')!!}'; 
        var notas_credito_clientes_obtener_factura = '{!!URL::to('notas_credito_clientes_obtener_factura')!!}';
        var notas_credito_clientes_obtener_productos = '{!!URL::to('notas_credito_clientes_obtener_productos')!!}';
        var notas_credito_clientes_obtener_datos_almacen  = '{!!URL::to('notas_credito_clientes_obtener_datos_almacen')!!}';
        var notas_credito_cliente_comprobar_cantidad_nota_vs_cantidad_factura = '{!!URL::to('notas_credito_cliente_comprobar_cantidad_nota_vs_cantidad_factura')!!}';
        var notas_credito_clientes_obtener_folios_fiscales = '{!!URL::to('notas_credito_clientes_obtener_folios_fiscales')!!}';
        var notas_credito_clientes_obtener_ultimo_folio_serie_seleccionada = '{!!URL::to('notas_credito_clientes_obtener_ultimo_folio_serie_seleccionada')!!}';
        var notas_credito_clientes_guardar = '{!!URL::to('notas_credito_clientes_guardar')!!}';
        var notas_credito_clientes_verificar_si_continua_baja = '{!!URL::to('notas_credito_clientes_verificar_si_continua_baja')!!}';
        var notas_credito_clientes_alta_o_baja = '{!!URL::to('notas_credito_clientes_alta_o_baja')!!}'; 
        var notas_credito_clientes_obtener_nota_cliente = '{!!URL::to('notas_credito_clientes_obtener_nota_cliente')!!}';
        var notas_credito_clientes_guardar_modificacion = '{!!URL::to('notas_credito_clientes_guardar_modificacion')!!}';
        var notas_credito_clientes_buscar_folio_string_like = '{!!URL::to('notas_credito_clientes_buscar_folio_string_like')!!}'; 
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/notascreditoclientes/notascreditoclientes.js"></script>
    
@endsection