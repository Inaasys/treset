@extends('plantilla_maestra')
@section('titulo')
  Servicios
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
                                    <h5>&nbsp;&nbsp;&nbsp;SERVICIOS&nbsp;&nbsp;&nbsp;</h5>
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
                                                <td >
                                                    <a class="btn bg-blue btn-xs waves-effect" href="{{route('servicios_exportar_excel')}}" target="_blank">
                                                        Excel
                                                    </a>
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
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Código</th>
                    						<th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Servicio</th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Unidad</th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">NúmeroFamilia</th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Familia</th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Costo</th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Venta</th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Cantidad</th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">ClaveProducto</th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">ClaveUnidad</th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th><div style="width:100px !important;">Operaciones</div></th>
                    						<th>Código</th>
                                            <th>Servicio</th>
                                            <th>Unidad</th>
                                            <th>NúmeroFamilia</th>
                                            <th>Familia</th>
                                            <th>Costo</th>
                                            <th>Venta</th>
                                            <th>Cantidad</th>
                                            <th>ClaveProducto</th>
                                            <th>ClaveUnidad</th>
                                            <th>Status</th>
                                        </tr>
                                    </tfoot>
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
                            <div class="col-md-12">
                                <label>Código<b style="color:#F44336 !important;">*</b></label>
                                <input type="text" class="form-control inputnext" name="codigo" id="codigo" required data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">
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
		        	<input type="hidden" id="codigoservicio" name="codigoservicio">
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
        var servicios_obtener = '{!!URL::to('servicios_obtener')!!}';
        var servicios_obtener_familias = '{!!URL::to('servicios_obtener_familias')!!}';
        var servicios_obtener_familia_por_numero = '{!!URL::to('servicios_obtener_familia_por_numero')!!}';
        var servicios_obtener_claves_productos = '{!!URL::to('servicios_obtener_claves_productos')!!}';
        var servicios_obtener_clave_producto_por_clave = '{!!URL::to('servicios_obtener_clave_producto_por_clave')!!}';
        var servicios_obtener_claves_unidades = '{!!URL::to('servicios_obtener_claves_unidades')!!}';
        var servicios_obtener_clave_unidad_por_clave = '{!!URL::to('servicios_obtener_clave_unidad_por_clave')!!}';
        var servicios_guardar = '{!!URL::to('servicios_guardar')!!}';
        var servicios_alta_o_baja = '{!!URL::to('servicios_alta_o_baja')!!}'; 
        var servicios_obtener_servicio = '{!!URL::to('servicios_obtener_servicio')!!}'; 
        var servicios_guardar_modificacion = '{!!URL::to('servicios_guardar_modificacion')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/catalogos/servicios/servicios.js"></script>
@endsection