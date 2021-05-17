@extends('plantilla_maestra')
@section('titulo')
  Traspasos
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
                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                    <h5>&nbsp;&nbsp;&nbsp;TRASPASOS&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 button-demo">
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
                                                    <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" href="{{route('traspasos_exportar_excel')}}" target="_blank">
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
                                            <th><div style="width:150px !important;">Operaciones</div></th>
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
                                <label>Traspaso <b style="color:#F44336 !important;" id="serietexto"> Serie: {{$serieusuario}}</b></label>
                                <input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">
                                <input type="hidden" class="form-control" name="serie" id="serie" value="{{$serieusuario}}" required readonly data-parsley-length="[1, 10]">
                                <input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>
                                <input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>
                            </div>  
                            <div class="col-md-3">
                                <label>De Almacén</label>
                                <table class="col-md-12">
                                    <tr>
                                        <td>
                                            <div class="btn bg-blue waves-effect" id="botonobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>
                                        </td>
                                        <td>
                                            <div class="form-line">
                                                <input type="hidden" class="form-control" name="numeroalmacende" id="numeroalmacende" required readonly onkeyup="tipoLetra(this)">
                                                <input type="text" class="form-control" name="almacende" id="almacende" required readonly>
                                            </div>
                                        </td>
                                    </tr>    
                                </table>
                            </div>
                            <div class="col-md-3">
                                <label>A Foráneo</label>
                                <table class="col-md-12">
                                    <tr>
                                        <td>
                                            <div class="btn bg-blue waves-effect" id="botonobteneralmacenesforaneos" onclick="obteneralmacenesforaneos()">Seleccionar</div>
                                        </td>
                                        <td>
                                            <div class="form-line">
                                                <input type="hidden" class="form-control" name="numeroalmacena" id="numeroalmacena"  readonly onkeyup="tipoLetra(this)">
                                                <input type="text" class="form-control" name="almacena" id="almacena"  readonly>
                                            </div>
                                        </td>
                                    </tr>    
                                </table>
                            </div>
                            <div class="col-md-3">
                                <label>Fecha </label>
                                <input type="date" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();" >
                                <input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="{{$periodohoy}}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <label>Orden Trabajo</label>
                                <table class="col-md-12">
                                    <tr>
                                        <td>
                                            <div class="btn bg-blue waves-effect" id="botonobtenerordenestrabajo" onclick="obtenerordenestrabajo()">Seleccionar</div>
                                        </td>
                                        <td>
                                            <div class="form-line">
                                                <input type="text" class="form-control" name="orden" id="orden"  readonly>
                                                <input type="hidden" class="form-control" name="fechaorden" id="fechaorden"  readonly>
                                                <input type="hidden" class="form-control" name="tipo" id="tipo"  readonly>
                                                <input type="hidden" class="form-control" name="unidad" id="unidad"  readonly>
                                            </div>
                                        </td>
                                    </tr>    
                                </table>
                            </div>
                            <div class="col-md-2">
                                <label>Status Orden</label>
                                <input type="text" class="form-control" name="statusorden" id="statusorden"  required readonly onkeyup="tipoLetra(this);">
                            </div>
                            <div class="col-md-2">
                                <label>Cliente </label>
                                <input type="text" class="form-control" name="clienteorden" id="clienteorden"  required readonly onkeyup="tipoLetra(this);">
                            </div>
                            <div class="col-md-2">
                                <label>Referencia </label>
                                <input type="text" class="form-control" name="referencia" id="referencia" data-parsley-length="[1, 200]" onkeyup="tipoLetra(this);">
                            </div>
                            <div class="col-md-3" id="divbuscarcodigoproducto" hidden>
                                <label>Buscar producto por código</label>
                                <input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">
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
		        	<h5 id="textomodaldesactivar"> </h5>
                    <input type="hidden" id="traspasodesactivar" name="traspasodesactivar">
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
        var meshoy = '{{$meshoy}}';
        var periodohoy = '{{$periodohoy}}';
        var campos_activados = '{{$configuracion_tabla->campos_activados}}';
        var campos_desactivados = '{{$configuracion_tabla->campos_desactivados}}';
        var columnas_ordenadas = '{{$configuracion_tabla->columnas_ordenadas}}';
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var traspasos_obtener = '{!!URL::to('traspasos_obtener')!!}';
        var traspasos_obtener_ultimo_folio = '{!!URL::to('traspasos_obtener_ultimo_folio')!!}';
        var traspasos_obtener_almacenes = '{!!URL::to('traspasos_obtener_almacenes')!!}';
        var traspasos_obtener_almacenes_foraneos = '{!!URL::to('traspasos_obtener_almacenes_foraneos')!!}';
        var traspasos_obtener_ordenes_trabajo = '{!!URL::to('traspasos_obtener_ordenes_trabajo')!!}';
        var traspasos_obtener_productos = '{!!URL::to('traspasos_obtener_productos')!!}';
        var traspasos_obtener_existencias_partida = '{!!URL::to('traspasos_obtener_existencias_partida')!!}';
        var traspasos_obtener_existencias_almacen_foraneo = '{!!URL::to('traspasos_obtener_existencias_almacen_foraneo')!!}';
        var traspasos_obtener_existencias_nuevo_almacen_seleccionado = '{!!URL::to('traspasos_obtener_existencias_nuevo_almacen_seleccionado')!!}';
        var traspasos_guardar = '{!!URL::to('traspasos_guardar')!!}';
        var traspasos_verificar_baja = '{!!URL::to('traspasos_verificar_baja')!!}';
        var traspasos_alta_o_baja = '{!!URL::to('traspasos_alta_o_baja')!!}'; 
        var traspasos_obtener_traspaso = '{!!URL::to('traspasos_obtener_traspaso')!!}'; 
        var traspasos_guardar_modificacion = '{!!URL::to('traspasos_guardar_modificacion')!!}';
        var traspasos_obtener_datos_envio_email = '{!!URL::to('traspasos_obtener_datos_envio_email')!!}';
        var traspasos_enviar_pdfs_email = '{!!URL::to('traspasos_enviar_pdfs_email')!!}';
        var traspasos_buscar_folio_string_like = '{!!URL::to('traspasos_buscar_folio_string_like')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/traspasos/traspasos.js"></script>
@endsection



