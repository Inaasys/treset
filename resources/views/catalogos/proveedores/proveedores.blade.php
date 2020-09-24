@extends('plantilla_maestra')
@section('titulo')
  Proveedores
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
                        			<td >
                        				<h5>&nbsp;&nbsp;&nbsp;Proveedores&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                        			<td >
                            			<div class="btn bg-blue btn-xs waves-effect" onclick="alta()">
                                    		Altas
                                		</div>
                        			</td>
                                    <td >
                                        <a class="btn bg-blue btn-xs waves-effect" href="{{route('proveedores_exportar_excel')}}" target="_blank">
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
                                <label>Razon Social</label>
                                <input type="text" class="form-control" name="nombre" id="nombre" required onkeyup="tipoLetra(this);">
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
		        	<input type="hidden" id="numeroproveedor" name="numeroproveedor">
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
        var proveedores_obtener = '{!!URL::to('proveedores_obtener')!!}';
        var proveedores_obtener_ultimo_numero = '{!!URL::to('proveedores_obtener_ultimo_numero')!!}';
        var proveedores_obtener_codigos_postales = '{!!URL::to('proveedores_obtener_codigos_postales')!!}';
        var proveedores_guardar = '{!!URL::to('proveedores_guardar')!!}';
        var proveedores_alta_o_baja = '{!!URL::to('proveedores_alta_o_baja')!!}'; 
        var proveedores_obtener_proveedor = '{!!URL::to('proveedores_obtener_proveedor')!!}'; 
        var proveedores_guardar_modificacion = '{!!URL::to('proveedores_guardar_modificacion')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/catalogos/proveedores/proveedores.js"></script>

@endsection