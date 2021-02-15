@extends('plantilla_maestra')
@section('titulo')
  Ordenes de Trabajo
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
                                                <h5>Ordenes de Trabajo&nbsp;&nbsp;&nbsp;</h5>
                                            </td>
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
                                                <a class="btn bg-blue btn-xs waves-effect" href="{{route('ordenes_trabajo_exportar_excel')}}" target="_blank">
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
                        <ul class="nav nav-tabs tab-col-blue-grey" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#datosgeneralesordentrabajotab" data-toggle="tab">Datos Generales</a>
                            </li>
                            <li role="presentation">
                                <a href="#datostab" data-toggle="tab">Datos</a>
                            </li>
                            <li role="presentation">
                                <a href="#estadotab" data-toggle="tab">Estado</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade in active" id="datosgeneralesordentrabajotab">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Orden <b style="color:#F44336 !important;" id="serietexto"> Serie: {{$serieusuario}}</b></label>
                                        <input type="text" class="form-control" name="folio" id="folio" required onkeyup="tipoLetra(this);">
                                        <input type="hidden" class="form-control" name="serie" id="serie" value="{{$serieusuario}}" required readonly>
                                        <input type="hidden" class="form-control" name="numerofilastablaservicios" id="numerofilastablaservicios" required readonly>
                                    </div>   
                                    <div class="col-md-2">
                                        <label>Tipo de Orden</label>
                                        <select name="tipoorden" id="tipoorden" class="form-control select2" style="width:100% !important;" required>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Tipo de Unidad</label>
                                        <select name="tipounidad" id="tipounidad" class="form-control select2" style="width:100% !important;" required>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Fecha</label>
                                        <input type="datetime-local" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();" onkeyup="tipoLetra(this);">
                                        <input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="{{$periodohoy}}">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Entrega Promesa</label>
                                        <input type="datetime-local" class="form-control" name="fechaentregapromesa" id="fechaentregapromesa"  required onchange="validasolomesactual();" onkeyup="tipoLetra(this);">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Factura a</label>
                                        <table>
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="listarclientesfacturaa()">Seleccionar</div>
                                                </td>
                                                <td>
                                                    <div class="form-line">
                                                        <input type="hidden" class="form-control" name="numeroclientefacturaa" id="numeroclientefacturaa" required readonly onkeyup="tipoLetra(this)">
                                                        <input type="text" class="form-control" name="clientefacturaa" id="clientefacturaa" required readonly>
                                                    </div>
                                                </td>
                                            </tr>    
                                        </table>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Del cliente</label>
                                        <table>
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="listarclientesdelcliente()">Seleccionar</div>
                                                </td>
                                                <td>    
                                                    <div class="form-line">
                                                        <input type="hidden" class="form-control" name="numeroclientedelcliente" id="numeroclientedelcliente" required readonly onkeyup="tipoLetra(this)">
                                                        <input type="text" class="form-control" name="clientedelcliente" id="clientedelcliente" required readonly>
                                                    </div>
                                                </td>    
                                            </tr>    
                                        </table>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Agente</label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="listaragentes()">Seleccionar</div>
                                                </td>
                                                <td>    
                                                    <div class="form-line">
                                                        <input type="hidden" class="form-control" name="numeroagente" id="numeroagente" required readonly onkeyup="tipoLetra(this)">
                                                        <input type="text" class="form-control" name="agente" id="agente" required readonly>
                                                    </div>
                                                </td>    
                                            </tr>    
                                        </table>
                                    </div>
                                    <div class="col-md-3" id="divcaso">
                                        <label>Caso</label>
                                        <input type="text" class="form-control" name="caso" id="caso"   onkeyup="tipoLetra(this);">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4" id="divbuscarcodigoservicio">
                                        <label>Buscar servicio por código (Carga Mano de Obra)</label>
                                        <input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" autocomplete="off" placeholder="Escribe el código del servicio y da enter">
                                    </div>
                                </div>
                            </div>   
                            <div role="tabpanel" class="tab-pane fade" id="datostab">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Tipo Servicio</label>
                                        <select name="tiposervicio" id="tiposervicio" class="form-control select2" style="width:100% !important;" required>
                                            <option selected disabled hidden>Selecciona</option>
                                            <option value="NORMAL">NORMAL</option>
                                            <option value="CORRECTIVO" selected>CORRECTIVO</option>
                                            <option value="PREVENTIVO">PREVENTIVO</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Vin / Serie</label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="listarvines()">Seleccionar</div>
                                                </td>
                                                <td>    
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="vin" id="vin" required>
                                                    </div>
                                                </td>    
                                            </tr>    
                                        </table>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Motor / Serie</label>
                                        <input type="text" class="form-control" name="motor" id="motor"  required  onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Marca</label>
                                        <input type="text" class="form-control" name="marca" id="marca"  required  onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Modelo</label>
                                        <input type="text" class="form-control" name="modelo" id="modelo"  required  onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-1">
                                        <label>Año</label>
                                        <input type="text" class="form-control" name="ano" id="ano"  data-parsley-max="{{$periodohoy+1}}" data-parsley-type="digits" data-parsley-length="[4,4]" required  onkeyup="tipoLetra(this);">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Kilómetros</label>
                                        <input type="number" step="0.{{$numerocerosconfiguradosinputnumberstep}}" class="form-control" name="kilometros" id="kilometros" value="0.{{$numerocerosconfigurados}}" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{<?php echo $numerodecimales; ?>}$/" onchange="formatocorrectoinputcantidades(this)"  required >
                                    </div>
                                    <div class="col-md-2">
                                        <label>Placas</label>
                                        <input type="text" class="form-control" name="placas" id="placas"  required  onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-2">
                                        <label># Económico</label>
                                        <input type="text" class="form-control" name="economico" id="economico"  required  onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Color</label>
                                        <input type="text" class="form-control" name="color" id="color"  required data-parsley-minlength="3" onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Km Próx Servicio</label>
                                        <input type="number" step="0.{{$numerocerosconfiguradosinputnumberstep}}" class="form-control" name="kmproxservicio" id="kmproxservicio" value="0.{{$numerocerosconfigurados}}" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{<?php echo $numerodecimales; ?>}$/" onchange="formatocorrectoinputcantidades(this)"  >
                                    </div>
                                    <div class="col-md-2">
                                        <label>Fecha Recordatorio Cliente</label>
                                        <input type="date" class="form-control" name="fecharecordatoriocliente" id="fecharecordatoriocliente"   onkeyup="tipoLetra(this);">
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="estadotab">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Reclamo</label>
                                        <input type="text" class="form-control" name="reclamo" id="reclamo"   onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Orden Cliente</label>
                                        <input type="text" class="form-control" name="ordencliente" id="ordencliente"   onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Campaña No</label>
                                        <input type="text" class="form-control" name="campana" id="campana"  onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-2" >
                                        <label>Promoción</label>
                                        <input type="text" class="form-control" name="promocion" id="promocion"   onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Bahia</label>
                                        <input type="text" class="form-control" name="bahia" id="bahia"  required  onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-2" >
                                        <label>Horas Reales</label>
                                        <input type="text" class="form-control" name="horasreales" id="horasreales"   onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-2" >
                                        <label>Rodar</label>
                                        <input type="text" class="form-control" name="rodar" id="rodar"   onkeyup="tipoLetra(this);">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Plazo Días</label>
                                        <input type="text" class="form-control" name="plazodias" id="plazodias"  required  onkeyup="tipoLetra(this);">
                                    </div>
                                </div> 
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
<!-- Modal Seleccion Técnico(s) que realizo/realizaron el servicio y asignación de tiempos-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalasignaciontecnicos" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
            <div id="asignaciontecnicosformulario">
                <div id="tablatecnicos">
                </div>
            </div>
            <div id="asignaciontecnicoscontenidomodaltablas">
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
		        	<h5 id="textomodaldesactivar"> </h5>
                    <input type="hidden" id="ordendesactivar" name="ordendesactivar">
                    <div id="divmotivobaja">
                        <label>Motivo Baja</label>
                        <textarea class="form-control" name="motivobaja" id="motivobaja" rows=2 onkeyup="tipoLetra(this)" required></textarea>
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
<!-- Modal Terminar Orden de Trabajo-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalterminarorden" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header bg-red">
        		<h5 class="modal-title" id="exampleModalLabel">Ordenes de Trabajo Terminar</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formterminar" action="#">
                      <div class="row">
                          <div class="col-md-2">
                              <label >Orden</label>
                            <input type="text" class="form-control" id="ordenterminar" name="ordenterminar" readonly>
                          </div>
                          <div class="col-md-5">
                              <label >Cliente</label>
                            <input type="text" class="form-control" id="clienteordenterminar" name="clienteordenterminar" readonly>
                          </div>
                          <div class="col-md-5">
                              <label >Fecha</label>
                            <input type="datetime-local" class="form-control" id="fechaordenterminar" name="fechaordenterminar" readonly>
                          </div>
                      </div>                    
		        </form>	
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="btnterminar">Terminar</button>
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
        var meshoy = '{{$meshoy}}';
        var periodohoy = '{{$periodohoy}}';
        var serieusuario = '{{$serieusuario}}';
        var campos_activados = '{{$configuracion_tabla->campos_activados}}';
        var campos_desactivados = '{{$configuracion_tabla->campos_desactivados}}';
        var columnas_ordenadas = '{{$configuracion_tabla->columnas_ordenadas}}';
        var usuario = '{!!Auth::user()->user!!}';
        var ordenes_trabajo_obtener = '{!!URL::to('ordenes_trabajo_obtener')!!}';
        var ordenes_trabajo_obtener_ultimo_folio = '{!!URL::to('ordenes_trabajo_obtener_ultimo_folio')!!}';
        var ordenes_trabajo_obtener_tipos_ordenes_trabajo = '{!!URL::to('ordenes_trabajo_obtener_tipos_ordenes_trabajo')!!}';
        var ordenes_trabajo_obtener_tipos_unidades = '{!!URL::to('ordenes_trabajo_obtener_tipos_unidades')!!}';
        var ordenes_trabajo_obtener_fecha_actual_datetimelocal = '{!!URL::to('ordenes_trabajo_obtener_fecha_actual_datetimelocal')!!}';
        var ordenes_trabajo_obtener_clientes_facturaa = '{!!URL::to('ordenes_trabajo_obtener_clientes_facturaa')!!}';
        var ordenes_trabajo_obtener_clientes_delcliente = '{!!URL::to('ordenes_trabajo_obtener_clientes_delcliente')!!}';
        var ordenes_trabajo_obtener_agentes = '{!!URL::to('ordenes_trabajo_obtener_agentes')!!}';
        var ordenes_trabajo_obtener_vines = '{!!URL::to('ordenes_trabajo_obtener_vines')!!}';
        var ordenes_trabajo_obtener_servicios = '{!!URL::to('ordenes_trabajo_obtener_servicios')!!}';
        var ordenes_trabajo_obtener_tecnicos = '{!!URL::to('ordenes_trabajo_obtener_tecnicos')!!}';
        var ordenes_trabajo_guardar = '{!!URL::to('ordenes_trabajo_guardar')!!}';
        var ordenes_trabajo_verificar_uso_en_modulos = '{!!URL::to('ordenes_trabajo_verificar_uso_en_modulos')!!}'; 
        var ordenes_trabajo_alta_o_baja = '{!!URL::to('ordenes_trabajo_alta_o_baja')!!}'; 
        var ordenes_trabajo_verificar_status_orden = '{!!URL::to('ordenes_trabajo_verificar_status_orden')!!}'; 
        var ordenes_trabajo_terminar_orden = '{!!URL::to('ordenes_trabajo_terminar_orden')!!}'; 
        var ordenes_trabajo_obtener_orden_trabajo = '{!!URL::to('ordenes_trabajo_obtener_orden_trabajo')!!}'; 
        var ordenes_trabajo_guardar_modificacion = '{!!URL::to('ordenes_trabajo_guardar_modificacion')!!}';
        var ordenes_trabajo_buscar_folio_string_like = '{!!URL::to('ordenes_trabajo_buscar_folio_string_like')!!}'; 
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/ordenestrabajo/ordenestrabajo.js"></script>
@endsection