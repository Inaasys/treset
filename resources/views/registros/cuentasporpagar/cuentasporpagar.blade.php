@extends('plantilla_maestra')
@section('titulo')
  Cuentas por Pagar
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
                                    <h5>&nbsp;&nbsp;&nbsp;CUENTAS POR PAGAR&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 table-responsive button-demo">
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
                                                <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" href="{{route('cuentas_por_pagar_exportar_excel')}}" target="_blank">
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
                            <div class="col-md-2">
                                <label>Pago <b style="color:#F44336 !important;" id="serietexto"> Serie: {{$serieusuario}}</b></label>
                                <input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">
                                <input type="hidden" class="form-control" name="serie" id="serie" value="{{$serieusuario}}" required readonly data-parsley-length="[1, 10]">
                            </div>   
                            <div class="col-md-4">
                                <label>Proveedor</label>
                                <table class="col-md-12">
                                    <tr>
                                        <td>
                                            <div class="btn bg-blue waves-effect" id="btnobtenerproveedores" onclick="obtenerproveedores()">Seleccionar</div>
                                        </td>
                                        <td>
                                            <div class="form-line">
                                                <input type="hidden" class="form-control" name="numeroproveedor" id="numeroproveedor" required readonly onkeyup="tipoLetra(this)">
                                                <input type="text" class="form-control" name="proveedor" id="proveedor" required readonly>
                                            </div>
                                        </td>
                                    </tr>    
                                </table>
                            </div>
                            <div class="col-md-4">
                                <label>Banco</label>
                                <table class="col-md-12">
                                    <tr>
                                        <td>
                                            <div class="btn bg-blue waves-effect" onclick="obtenerbancos()">Seleccionar</div>
                                        </td>
                                        <td>    
                                            <div class="form-line">
                                                <input type="hidden" class="form-control" name="numerobanco" id="numerobanco" required readonly onkeyup="tipoLetra(this)">
                                                <input type="text" class="form-control" name="banco" id="banco" required readonly>
                                            </div>
                                        </td>    
                                    </tr>    
                                </table>
                            </div>   
                            <div class="col-md-2">
                                <label>Fecha</label>
                                <input type="date" class="form-control" name="fecha" id="fecha" onchange="validasolomesactual();" required>
                                <input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="{{$periodohoy}}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label>Transferencia</label>
                                <input type="text" class="form-control" name="transferencia" id="transferencia" value="0" required onkeyup="tipoLetra(this);">
                            </div>
                            <div class="col-md-4">
                                <label>Cheque</label>
                                <input type="text" class="form-control" name="cheque" id="cheque" value="0" required onkeyup="tipoLetra(this);">
                            </div>
                            <div class="col-md-4">
                                <label>Beneficiario</label>
                                <input type="text" class="form-control" name="beneficiario" id="beneficiario"  required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Cuenta a la que se Depositó</label>
                                <input type="text" class="form-control" name="cuentadeposito" id="cuentadeposito" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">
                            </div>
                            <div class="col-md-6">
                                <label>Anotación</label>
                                <textarea class="form-control" name="anotacion" id="anotacion" rows="2" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>
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
                    <input type="hidden" id="cxpdesactivar" name="cxpdesactivar">
                    <div id="divmotivobaja">
                        <label>Motivo Baja</label>
                        <textarea class="form-control" name="motivobaja" id="motivobaja" rows=2 required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></textarea>
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
        var cuentas_por_pagar_obtener = '{!!URL::to('cuentas_por_pagar_obtener')!!}';
        var cuentas_por_pagar_obtener_ultimo_folio = '{!!URL::to('cuentas_por_pagar_obtener_ultimo_folio')!!}';
        var cuentas_por_pagar_obtener_proveedores = '{!!URL::to('cuentas_por_pagar_obtener_proveedores')!!}';
        var cuentas_por_pagar_obtener_bancos = '{!!URL::to('cuentas_por_pagar_obtener_bancos')!!}';
        var cuentas_por_pagar_obtener_compras_proveedor = '{!!URL::to('cuentas_por_pagar_obtener_compras_proveedor')!!}';
        var cuentas_por_pagar_guardar = '{!!URL::to('cuentas_por_pagar_guardar')!!}';
        var cuentas_por_pagar_comprobar_baja = '{!!URL::to('cuentas_por_pagar_comprobar_baja')!!}';
        var cuentas_por_pagar_baja  = '{!!URL::to('cuentas_por_pagar_baja')!!}';
        var cuentas_por_pagar_obtener_cuenta_por_pagar =  '{!!URL::to('cuentas_por_pagar_obtener_cuenta_por_pagar')!!}';
        var cuentas_por_pagar_buscar_folio_string_like =  '{!!URL::to('cuentas_por_pagar_buscar_folio_string_like')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/cuentasporpagar/cuentasporpagar.js"></script>
@endsection



