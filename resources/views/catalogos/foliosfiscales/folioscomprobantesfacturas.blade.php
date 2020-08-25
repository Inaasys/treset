@extends('plantilla_maestra')
@section('titulo')
  Folios Comprobantes Facturas
@endsection
@section('additionals_css')
    <link href="css/parsley/parsley.css" rel="stylesheet">
    <link href="css/toastr/toastr.min.css" rel="stylesheet">
    <!-- Wait Me Css -->
    <link href="plugins/waitme/waitMe.css" rel="stylesheet" />
    <!-- JQuery DataTable Css -->
    <link href="plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css" rel="stylesheet">
    <!--Select 2-->
    <link href="js/select2/css/select2.min.css" rel="stylesheet" /> 
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
                        	<table>
                        		<tr>
                        			<td>
                        				<h5>&nbsp;&nbsp;&nbsp;&nbsp;Folios Comprobantes Facturas&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                        			<td >
                            			<div class="btn bg-blue btn-xs waves-effect" onclick="alta()">
                                    		Altas
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
                                            <th>Operaciones</th>
                                            <th>Serie</th>
                    						<th>Esquema</th>
                                            <th>FolioInicial</th>
                                            <th>Titulo</th>
                                            <th>Depto</th>
                                            <th>ValidoDesde</th>
                                            <th>ValidoHasta</th>
                                            <th>Empresa</th>
                                            <th>Predeterminar</th>
                                            <th>Status</th>
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
                            <div class="col-md-12">
                                <label>Código<b style="color:#F44336 !important;">*</b></label>
                                <input type="text" class="form-control" name="codigo" id="codigo" required onkeyup="tipoLetra(this);">
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
		        	Esta seguro de dar de baja este registro?
		        	<input type="hidden" id="codigoservicio" name="codigoservicio">
		        </form>	
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="aceptar">Guardar</button>
	      	</div>
    	</div>
  	</div>
</div> 
@endsection
@section('additionals_js')
    <script>
        /*urls y variables renderizadas con blade*/
        var mayusculas_sistema = '{{$mayusculas_sistema}}';
        var folios_comprobantes_facturas_obtener = '{!!URL::to('folios_comprobantes_facturas_obtener')!!}';
        var servicios_obtener_familias = '{!!URL::to('servicios_obtener_familias')!!}';
        var servicios_obtener_claves_productos = '{!!URL::to('servicios_obtener_claves_productos')!!}';
        var servicios_obtener_claves_unidades = '{!!URL::to('servicios_obtener_claves_unidades')!!}';
        var servicios_guardar = '{!!URL::to('servicios_guardar')!!}';
        var servicios_alta_o_baja = '{!!URL::to('servicios_alta_o_baja')!!}'; 
        var servicios_obtener_servicio = '{!!URL::to('servicios_obtener_servicio')!!}'; 
        var servicios_guardar_modificacion = '{!!URL::to('servicios_guardar_modificacion')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/catalogos/foliosfiscales/folioscomprobantesfacturas.js"></script>
@endsection