@extends('plantilla_maestra')
@section('titulo')
  Personal
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
                                <div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">
                                    <h5>&nbsp;&nbsp;&nbsp;PERSONAL&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-11 col-md-11 col-sm-11 col-xs-12 button-demo">
                                    <div class="table-responsive">
                                        <table>
                                            <tr>
                                                <td >
                                                    <a class="btn bg-blue btn-xs waves-effect" href="{{route('personal_exportar_excel')}}" target="_blank">
                                                        Excel
                                                    </a>
                                                </td>
                                                @if($numeropersonal == 0)
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="cargarusuariosytecnicos()">
                                                        Cargar usuarios y técnicos al catálogo personal
                                                    </div>
                                                </td>
                                                @endif
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover" style="width:100% !important;">
                                    <thead class="customercolor">
                                        <tr>
                                            <th><div style="width:100px !important;">Operaciones</div></th>
                                            <th>id</th>
                    						<th>nombre</th>
                                            <th>fecha_ingreso</th>
                                            <th>tipo_personal</th>
                                            <th>status</th>
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
<!-- Modal Carga de Usuarios y Tecnicos-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="ModalCargarUsuariosYTecnicos" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div>
                <div class="modal-header bg-red">
                    <h4 class="modal-title" >Técnicos y Usuarios</h4>
                </div>
                <form  action="{{route('personal_guardar_usuarios_y_tecnicos')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="col-md-12" id="tablausuariosytecnicos">
                            <!-- aqui van los formularios de alta o modificacion y se agregan automaticamente con jquery -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-sm"  data-dismiss="modal">Salir</button>
                        <button type="submit" class="btn btn-success btn-sm" >Cargar en Catálogo Personal</button>
                    </div>
                </form> 
            </div>   
        </div>
    </div>
</div>
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
                                <label>id</label>
                                <input type="text" class="form-control" name="id" id="id" required readonly onkeyup="tipoLetra(this);">
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
      		<div class="modal-header bg-red">
        		<h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formdesactivar" action="#">
                  <h5>Esta seguro de dar de baja este registro?</h5>
		        	<input type="hidden" id="numeropersonal" name="numeropersonal">
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
        var personal_obtener = '{!!URL::to('personal_obtener')!!}';
        var personal_obtener_usuarios_y_tecnicos = '{!!URL::to('personal_obtener_usuarios_y_tecnicos')!!}';
        var personal_alta_o_baja = '{!!URL::to('personal_alta_o_baja')!!}'; 
        var personal_obtener_personal = '{!!URL::to('personal_obtener_personal')!!}'; 
        var personal_guardar_modificacion = '{!!URL::to('personal_guardar_modificacion')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/catalogos/personal/personal.js"></script>
@endsection