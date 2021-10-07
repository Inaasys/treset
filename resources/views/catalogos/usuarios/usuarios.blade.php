@extends('plantilla_maestra')
@section('titulo')
  Usuarios
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
                                    <h5>&nbsp;&nbsp;&nbsp;USUARIOS&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-11 col-md-11 col-sm-11 col-xs-12 button-demo">
                                    <div class="table-responsive navbar-right">
                                        <table>
                                            <tr>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta()">
                                                        Altas
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover display nowrap" style="width:100% !important;">
                                    <thead class="{{$empresa->background_tables}}">
                                        <tr>
                                            <th><div style="width:100px !important;">Operaciones</div></th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Número</th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Nombre</th>
                    						<th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Correo</th>
                    						<th>Usuario</th>
                    						<th>Rol</th>
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
                <form id="formparsley" action="#">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Número</label>
                                <input type="text" class="form-control" name="numero" id="numero" required readonly onkeyup="tipoLetra(this);">
                            </div>   
                        </div>
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
<!-- Modal Permisos-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="ModalPermisos" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div id="formulariopermisos">
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
                    <h4 class="modal-title" id="titulomodalpermisos">Permisos</h4>
                </div>
                <form id="formpermisos" action="#">
                    <div class="modal-body">
                        <div class="col-md-12" id="tabsformpermisos">
                            <!-- aqui van los formularios de alta o modificacion y se agregan automaticamente con jquery -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardarPermisos">Confirmar Permisos</button>
                    </div>
                </form> 
            </div>
            <div id="contenidomodaltablas">
                <!-- aqui van las tablas de seleccion y se agregan automaticamente con jquery -->
            </div> 
        </div>
    </div>
</div>
<!-- Modal Series Documentos Por Usuario-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="ModalSeriesDocumentos" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div id="formularioserie">
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
                    <h4 class="modal-title" id="titulomodalserie">Series Documentos</h4>
                </div>
                <form id="formparsleyserie" action="#">
                    <div class="modal-body">
                        <div class="col-md-12" id="tabsformserie">
                            <!-- aqui van los formularios de alta o modificacion y se agregan automaticamente con jquery -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-sm" onclick="mostrarlistadoseriesdocumentos()">Regresar</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardarSerie">Guardar</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardarModificacionSerie">Confirmar Cambios</button>
                    </div>
                </form> 
            </div>
            <div id="tablasmodalserie">
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
		        	<h5>Esta seguro de dar de baja este registro?</h5>
		        	<input type="hidden" id="idusuario" name="idusuario">
		        </form>	
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="aceptar">Confirmar Baja</button>
	      	</div>
    	</div>
  	</div>
</div> 
@endsection
@section('additionals_js')
    <script>
        /*urls y variables renderizadas con blade*/
        var mayusculas_sistema = '{{$mayusculas_sistema}}';
        var numerodecimales = '{{$numerodecimales}}';
        var numerocerosconfigurados = '{{$numerocerosconfigurados}}';
        var numerocerosconfiguradosinputnumberstep = '{{$numerocerosconfiguradosinputnumberstep}}';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';
        var usuarios_obtener = '{!!URL::to('usuarios_obtener')!!}';
        var usuarios_obtener_ultimo_numero = '{!!URL::to('usuarios_obtener_ultimo_numero')!!}';
        var usuarios_obtener_roles = '{!!URL::to('usuarios_obtener_roles')!!}';
        var usuarios_guardar = '{!!URL::to('usuarios_guardar')!!}';
        var usuarios_alta_o_baja = '{!!URL::to('usuarios_alta_o_baja')!!}'; 
        var usuarios_obtener_usuario = '{!!URL::to('usuarios_obtener_usuario')!!}'; 
        var usuarios_guardar_modificacion = '{!!URL::to('usuarios_guardar_modificacion')!!}';
        var usuarios_obtener_permisos = '{!!URL::to('usuarios_obtener_permisos')!!}';
        var usuarios_guardar_permisos = '{!!URL::to('usuarios_guardar_permisos')!!}';
        var usuarios_obtener_series_documentos_usuario = '{!!URL::to('usuarios_obtener_series_documentos_usuario')!!}';
        var usuarios_obtener_tipos_documentos = '{!!URL::to('usuarios_obtener_tipos_documentos')!!}';
        var usuarios_guardar_serie_documento = '{!!URL::to('usuarios_guardar_serie_documento')!!}';
        var usuarios_guardar_modificacion_serie_documento = '{!!URL::to('usuarios_guardar_modificacion_serie_documento')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/catalogos/usuarios/usuarios.js"></script>
@endsection