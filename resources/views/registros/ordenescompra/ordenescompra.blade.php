@extends('plantilla_maestra')
@section('titulo')
  Ordenes de Compra
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
                                    <h5>&nbsp;&nbsp;&nbsp;ORDENES DE COMPRA&nbsp;&nbsp;&nbsp;</h5>
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
                                                    <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" href="{{route('ordenes_compra_exportar_excel')}}" target="_blank">
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
                            <div class="col-md-3">
                                <label>Orden <b style="color:#F44336 !important;" id="serietexto"> Serie: {{$serieusuario}}</b></label>
                                <input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">
                                <input type="hidden" class="form-control" name="serie" id="serie" value="{{$serieusuario}}" data-parsley-length="[0, 10]" required readonly>
                                <input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>
                                <input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>
                            </div>   
                            <div class="col-md-3">
                                <label>Plazo Días (proveedor)</label>
                                <input type="text" class="form-control" name="plazo" id="plazo"  required>
                            </div>
                            <div class="col-md-3">
                                <label>Referencia</label>
                                <input type="text" class="form-control" name="referencia" id="referencia" required data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">
                            </div>   
                            <div class="col-md-3">
                                <label>Fecha</label>
                                <input type="date" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();">
                                <input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="{{$periodohoy}}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label>Proveedor</label>
                                <table class="col-md-12">
                                    <tr>
                                        <td>
                                            <div class="btn bg-blue waves-effect" onclick="obtenerproveedores()">Seleccionar</div>
                                        </td>
                                        <td>
                                            <div class="form-line">
                                                <input type="hidden" class="form-control" name="numeroproveedor" id="numeroproveedor" required readonly >
                                                <input type="text" class="form-control" name="proveedor" id="proveedor" required readonly onkeyup="tipoLetra(this)">
                                            </div>
                                        </td>
                                    </tr>    
                                </table>
                            </div>
                            <div class="col-md-4">
                                <label>Almacen</label>
                                <table class="col-md-12">
                                    <tr>
                                        <td>
                                            <div class="btn bg-blue waves-effect" onclick="obteneralmacenes()">Seleccionar</div>
                                        </td>
                                        <td>    
                                            <div class="form-line">
                                                <input type="hidden" class="form-control" name="numeroalmacen" id="numeroalmacen" required readonly >
                                                <input type="text" class="form-control" name="almacen" id="almacen" required readonly onkeyup="tipoLetra(this)">
                                            </div>
                                        </td>    
                                    </tr>    
                                </table>
                            </div>
                            <div class="col-md-4">
                                <label>Tipo</label>
                                <select name="tipo" id="tipo" class="form-control select2" style="width:100% !important;" required>
                                    
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" id="divbuscarcodigoproducto">
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
<!-- Modal Autorizar Orden de Compra-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="autorizarorden" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header bg-red">
        		<h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formautorizar" action="#">
		        	Estas seguro de autorizar la orden de compra?
		        	<input type="hidden" id="ordenautorizar" name="ordenautorizar">
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
                    <input type="hidden" id="ordendesactivar" name="ordendesactivar">
                    <div id="divmotivobaja">
                        <label>Motivo Baja</label>
                        <textarea class="form-control" name="motivobaja" id="motivobaja" rows=2 data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" required></textarea>
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
        var ordenes_compra_obtener = '{!!URL::to('ordenes_compra_obtener')!!}';
        var ordenes_compra_obtener_ultimo_folio = '{!!URL::to('ordenes_compra_obtener_ultimo_folio')!!}';
        var ordenes_compra_obtener_tipos_ordenes_compra = '{!!URL::to('ordenes_compra_obtener_tipos_ordenes_compra')!!}';
        var ordenes_compra_obtener_proveedores = '{!!URL::to('ordenes_compra_obtener_proveedores')!!}';
        var ordenes_compra_obtener_almacenes = '{!!URL::to('ordenes_compra_obtener_almacenes')!!}';
        var ordenes_compra_obtener_productos = '{!!URL::to('ordenes_compra_obtener_productos')!!}';
        var ordenes_compra_guardar = '{!!URL::to('ordenes_compra_guardar')!!}';
        var ordenes_compra_autorizar = '{!!URL::to('ordenes_compra_autorizar')!!}'; 
        var ordenes_compra_verificar_uso_en_modulos = '{!!URL::to('ordenes_compra_verificar_uso_en_modulos')!!}'; 
        var ordenes_compra_alta_o_baja = '{!!URL::to('ordenes_compra_alta_o_baja')!!}'; 
        var ordenes_compra_obtener_orden_compra = '{!!URL::to('ordenes_compra_obtener_orden_compra')!!}'; 
        var ordenes_compra_guardar_modificacion = '{!!URL::to('ordenes_compra_guardar_modificacion')!!}';
        var ordenes_compra_buscar_folio_string_like = '{!!URL::to('ordenes_compra_buscar_folio_string_like')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/ordenescompra/ordenescompra.js"></script>
@endsection



