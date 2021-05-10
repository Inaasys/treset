@extends('plantilla_maestra')
@section('titulo')
  Clientes
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
                        <div class="header bg-red table-responsive button-demo">
                        	<table>
                        		<tr>
                        			<td>
                        				<h5>&nbsp;&nbsp;&nbsp;Clientes&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                        			<td >
                            			<div class="btn bg-blue btn-xs waves-effect" onclick="alta()">
                                    		Altas
                                		</div>
                        			</td>
                                    <td >
                                        <a class="btn bg-blue btn-xs waves-effect" href="{{route('clientes_exportar_excel')}}" target="_blank">
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
                        <div class="body">
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover" style="width:100% !important;">
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
                <form id="formparsley" action="#">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Número</label>
                                <input type="text" class="form-control" name="numero" id="numero" required readonly onkeyup="tipoLetra(this);">
                            </div>   
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label>Nombre</label>
                                <input type="text" class="form-control" name="nombre" id="nombre" placeholder="1er Apellido, 2do Apellido, Nombre(s)" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">
                            </div>
                        </div>
                        <div class="col-md-12" id="tabsform">
                            <!-- aqui van los formularios de alta o modificacion y se agregan automaticamente con jquery -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-sm" onclick="limpiar();limpiarmodales();" data-dismiss="modal">Salir</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardar">Guardar</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardarModificacion">Guardar</button>
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
      		<div class="modal-header bg-red">
        		<h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formdesactivar" action="#">
		        	<h5>Esta seguro de dar de baja este registro?</h5>
		        	<input type="hidden" id="numerocliente" name="numerocliente">
		        </form>	
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="aceptar">Guardar</button>
	      	</div>
    	</div>
  	</div>
</div> 
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
        var campos_activados = '{{$configuracion_tabla->campos_activados}}';
        var campos_desactivados = '{{$configuracion_tabla->campos_desactivados}}';
        var columnas_ordenadas = '{{$configuracion_tabla->columnas_ordenadas}}';
        var clientes_obtener = '{!!URL::to('clientes_obtener')!!}';
        var clientes_obtener_ultimo_numero = '{!!URL::to('clientes_obtener_ultimo_numero')!!}';
        var clientes_guardar = '{!!URL::to('clientes_guardar')!!}';
        var clientes_obtener_paises = '{!!URL::to('clientes_obtener_paises')!!}';
        var clientes_obtener_codigos_postales = '{!!URL::to('clientes_obtener_codigos_postales')!!}';
        var clientes_obtener_estados = '{!!URL::to('clientes_obtener_estados')!!}';
        var clientes_obtener_municipios = '{!!URL::to('clientes_obtener_municipios')!!}';
        var clientes_obtener_agentes = '{!!URL::to('clientes_obtener_agentes')!!}';
        var clientes_obtener_formas_pago = '{!!URL::to('clientes_obtener_formas_pago')!!}';
        var clientes_obtener_metodos_pago = '{!!URL::to('clientes_obtener_metodos_pago')!!}';
        var clientes_obtener_uso_cfdi = '{!!URL::to('clientes_obtener_uso_cfdi')!!}';
        var clientes_obtener_productos = '{!!URL::to('clientes_obtener_productos')!!}';
        var clientes_alta_o_baja = '{!!URL::to('clientes_alta_o_baja')!!}'; 
        var clientes_obtener_cliente = '{!!URL::to('clientes_obtener_cliente')!!}'; 
        var clientes_guardar_modificacion = '{!!URL::to('clientes_guardar_modificacion')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/catalogos/clientes/clientes.js"></script>
@endsection