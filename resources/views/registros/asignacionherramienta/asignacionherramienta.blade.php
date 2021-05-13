@extends('plantilla_maestra')
@section('titulo')
  Asignación de Herramienta
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
                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                    <h5>&nbsp;&nbsp;&nbsp;ASIGNACIÓN DE HERRAMIENTA&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12 button-demo">
                                    <div class="table-responsive">
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
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="mostrarmodalgenerarexcelpersonal()">
                                                        Auditar Herramienta
                                                    </div>
                                                </td>
                                                <td >
                                                    <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" href="{{route('asignacion_herramienta_exportar_excel')}}" target="_blank">
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
                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                    <div class="row">
                                        <div class="col-md-7 col-md-offset-4">
                                            <select class="select2 form-control" name="periodo" id="periodo" onchange="relistar()" style="width75% !important;">
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
                <form id="formparsley" action="#">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Asignación <b style="color:#F44336 !important;" id="serietexto"> Serie: {{$serieusuario}}</b></label>
                                <input type="text" class="form-control" name="id" id="id" required readonly onkeyup="tipoLetra(this);">
                                <input type="hidden" class="form-control" name="serie" id="serie" value="{{$serieusuario}}" required readonly data-parsley-length="[1, 10]">
                                <input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>
                                <input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>
                            </div>   
                            <div class="col-md-4">
                                <label>Personal que recibe</label>
                                <table class="col-md-12">
                                    <tr>
                                        <td>
                                            <div class="btn bg-blue waves-effect" onclick="obtenerpersonalrecibe()">Seleccionar</div>
                                        </td>
                                        <td>
                                            <div class="form-line">
                                                <input type="hidden" class="form-control" name="numeropersonalrecibe" id="numeropersonalrecibe" required readonly onkeyup="tipoLetra(this)">
                                                <input type="text" class="form-control" name="personalrecibe" id="personalrecibe" required readonly>
                                            </div>
                                        </td>
                                    </tr>    
                                </table>
                            </div>
                            <div class="col-md-4">
                                <label>Personal que entrega</label>
                                <table class="col-md-12">
                                    <tr>
                                        <td>
                                            <div class="btn bg-blue waves-effect" onclick="obtenerpersonalentrega()" id="btnbuscarpersonalqueentrega" style="display:none">Seleccionar</div>
                                        </td>
                                        <td>    
                                            <div class="form-line">
                                                <input type="hidden" class="form-control" name="numeropersonalentrega" id="numeropersonalentrega" required readonly onkeyup="tipoLetra(this)">
                                                <input type="text" class="form-control" name="personalentrega" id="personalentrega" required readonly>
                                            </div>
                                        </td>    
                                    </tr>    
                                </table>
                            </div>   
                            <div class="col-md-2">
                                <label>Fecha</label>
                                <input type="date" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();" onkeyup="tipoLetra(this);">
                                <input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="{{$periodohoy}}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" id="divbuscarcodigoproducto">
                                <label>Buscar herramienta por código</label>
                                <input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código de la herramienta">
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
<!-- Modal Autorizar Asignación-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="autorizarasignacion" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header bg-red">
        		<h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formautorizar" action="#">
                    <h5 >Estas seguro de autorizar la asignación de herramienta? </h5>
                    <div class="table-responsive">
                        <table id="tablaautorizacionasignacionherramienta" class="tablaautorizacionasignacionherramienta table table-bordered table-striped table-hover">
                            <thead class="customercolor">
                                <tr>
                                    <th>Código</th>
                                    <th>Cantidad</th>
                                    <th>Almacen</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
		        	<input type="hidden" id="asignacionautorizar" name="asignacionautorizar">
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
                    <input type="hidden" id="asignaciondesactivar" name="asignaciondesactivar">
                    <div id="divmotivobaja">
                        <label>Motivo Baja</label>
                        <textarea class="form-control" name="motivobaja" id="motivobaja" rows=2 onkeyup="tipoLetra(this)" required data-parsley-length="[1, 255]"></textarea>
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
<!-- modal para crear excel personal para auditoria -->
<!-- Modal Generar PDF-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalgenerarexcelpersonal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-lg" role="document">
    	<div class="modal-content">
      		<div class="modal-header bg-red">
        		<h5 class="modal-title" id="exampleModalLabel">Auditoria de Herramienta por Personal</h5>
      		</div>
      		<div class="modal-body">
                  <form id="formauditarherramienta">
                    <div class="row">
                        <label>Selecciona el personal que se auditara:</label>
                        <div class="col-md-12">
                            <select name="personalexcel" id="personalexcel" class="form-control select2" onchange="herramientaasignadapersonal()" style="width:100% !important;" required>
                            </select>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-md-12" id="tabsformauditarherramientas">
                            <!-- aqui van el formulario para auditar herramienta al personal -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-sm" onclick="limpiar();limpiarmodales();" data-dismiss="modal">Salir</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardarAuditoria" style="display:none">Guardar Auditoria</button>
                        <a class="btn btn-primary btn-sm" id="btnGenerarReporteAuditoria" style="display:none" target="_blank">Generar Reporte Auditoría</a>
                        <a class="btn btn-primary btn-sm" id="btnGenerarReporteGeneral" style="display:none" target="_blank">Generar Reporte General</a>
                    </div>
		        </form>	
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
        var serieusuario = '{{$serieusuario}}';
        var meshoy = '{{$meshoy}}';
        var periodohoy = '{{$periodohoy}}';
        var campos_activados = '{{$configuracion_tabla->campos_activados}}';
        var campos_desactivados = '{{$configuracion_tabla->campos_desactivados}}';
        var columnas_ordenadas = '{{$configuracion_tabla->columnas_ordenadas}}';
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var asignacion_herramienta_obtener = '{!!URL::to('asignacion_herramienta_obtener')!!}';
        var asignacion_herramienta_obtener_ultimo_id = '{!!URL::to('asignacion_herramienta_obtener_ultimo_id')!!}';
        var asignacion_herramienta_obtener_personal_recibe = '{!!URL::to('asignacion_herramienta_obtener_personal_recibe')!!}';
        var asignacion_herramienta_obtener_personal_entrega = '{!!URL::to('asignacion_herramienta_obtener_personal_entrega')!!}';
        var asignacion_herramienta_obtener_herramienta = '{!!URL::to('asignacion_herramienta_obtener_herramienta')!!}';
        var asignacion_herramienta_obtener_existencias_almacen = '{!!URL::to('asignacion_herramienta_obtener_existencias_almacen')!!}';
        var asignacion_herramienta_guardar = '{!!URL::to('asignacion_herramienta_guardar')!!}';
        var asignacion_herramienta_obtener_asignacion_herramienta_a_autorizar = '{!!URL::to('asignacion_herramienta_obtener_asignacion_herramienta_a_autorizar')!!}'; 
        var asignacion_herramienta_autorizar = '{!!URL::to('asignacion_herramienta_autorizar')!!}'; 
        var asignacion_herramienta_obtener_asignacion_herramienta = '{!!URL::to('asignacion_herramienta_obtener_asignacion_herramienta')!!}'; 
        var asignacion_herramienta_guardar_modificacion = '{!!URL::to('asignacion_herramienta_guardar_modificacion')!!}';
        var asignacion_herramienta_alta_o_baja = '{!!URL::to('asignacion_herramienta_alta_o_baja')!!}'; 
        var asignacion_herramienta_buscar_id_string_like = '{!!URL::to('asignacion_herramienta_buscar_id_string_like')!!}';
        var asignacion_herramienta_generar_excel_obtener_personal = '{!!URL::to('asignacion_herramienta_generar_excel_obtener_personal')!!}';
        var asignacion_herramienta_obtener_herramienta_personal = '{!!URL::to('asignacion_herramienta_obtener_herramienta_personal')!!}';
        var asignacion_herramienta_guardar_auditoria = '{!!URL::to('asignacion_herramienta_guardar_auditoria')!!}';
        var asignacion_herramienta_verificar_uso_en_modulos = '{!!URL::to('asignacion_herramienta_verificar_uso_en_modulos')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/asignacionherramienta/asignacionherramienta.js"></script>
@endsection



