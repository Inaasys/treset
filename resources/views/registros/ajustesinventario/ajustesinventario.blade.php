@extends('plantilla_maestra')
@section('titulo')
  Ajustes de Inventario
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
                                                <h5>&nbsp;&nbsp;&nbsp;Ajustes de Inventario&nbsp;&nbsp;&nbsp;</h5>
                                            </td>
                                            <td >
                                                <div class="btn bg-blue btn-xs waves-effect" onclick="alta()">
                                                    Altas
                                                </div>
                                            </td>
                                            <td >
                                                <a class="btn bg-blue btn-xs waves-effect" href="{{route('ajustesinventario_exportar_excel')}}" target="_blank">
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
                        <div class="row">
                            <div class="col-md-2">
                                <label>Ajuste <b style="color:#F44336 !important;" id="serietexto"> Serie: {{$serieusuario}}</b></label>
                                <input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">
                                <input type="hidden" class="form-control" name="serie" id="serie" value="{{$serieusuario}}" required readonly>
                                <input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>
                            </div>  
                            <div class="col-md-3">
                                <label>Almacén</label>
                                <table class="col-md-12">
                                    <tr>
                                        <td>
                                            <div class="btn bg-blue waves-effect" onclick="obteneralmacenes()">Seleccionar</div>
                                        </td>
                                        <td>
                                            <div class="form-line">
                                                <input type="hidden" class="form-control" name="numeroalmacen" id="numeroalmacen" required readonly onkeyup="tipoLetra(this)">
                                                <input type="text" class="form-control" name="almacen" id="almacen" required readonly>
                                            </div>
                                        </td>
                                    </tr>    
                                </table>
                            </div>
                            <div class="col-md-3">
                                <label>Fecha </label>
                                <input type="date" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();" onkeyup="tipoLetra(this);">
                                <input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="{{$periodohoy}}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" id="divbuscarcodigoproducto" hidden>
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
                    <input type="hidden" id="ajustedesactivar" name="ajustedesactivar">
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
        var campos_activados = '{{$configuracion_tabla->campos_activados}}';
        var campos_desactivados = '{{$configuracion_tabla->campos_desactivados}}';
        var columnas_ordenadas = '{{$configuracion_tabla->columnas_ordenadas}}';
        var ajustesinventario_obtener = '{!!URL::to('ajustesinventario_obtener')!!}';
        var ajustesinventario_obtener_ultimo_id = '{!!URL::to('ajustesinventario_obtener_ultimo_id')!!}';
        var ajustesinventario_obtener_almacenes = '{!!URL::to('ajustesinventario_obtener_almacenes')!!}';
        var ajustesinventario_obtener_productos = '{!!URL::to('ajustesinventario_obtener_productos')!!}';
        var ajustesinventario_obtener_existencias_por_codigo_y_almacen = '{!!URL::to('ajustesinventario_obtener_existencias_por_codigo_y_almacen')!!}';
        var ajustesinventario_guardar = '{!!URL::to('ajustesinventario_guardar')!!}';
        var ajustesinventario_verificar_baja = '{!!URL::to('ajustesinventario_verificar_baja')!!}';
        var ajustesinventario_alta_o_baja = '{!!URL::to('ajustesinventario_alta_o_baja')!!}'; 
        var ajustesinventario_obtener_ajuste = '{!!URL::to('ajustesinventario_obtener_ajuste')!!}'; 
        var ajustesinventario_guardar_modificacion = '{!!URL::to('ajustesinventario_guardar_modificacion')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/ajustesinventario/ajustesinventario.js"></script>
@endsection



