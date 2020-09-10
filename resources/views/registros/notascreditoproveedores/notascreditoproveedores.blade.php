@extends('plantilla_maestra')
@section('titulo')
  Notas Crédito Proveedores
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
                            <div class="row clearfix">
                                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 table-responsive">
                                    <table>
                                        <tr>
                                            <td>
                                                <h5>&nbsp;&nbsp;&nbsp;&nbsp;Notas Crédito Proveedores&nbsp;&nbsp;&nbsp;</h5>
                                            </td>
                                            <td >
                                                <div class="btn bg-blue btn-xs waves-effect" onclick="alta('PROD')">
                                                    Altas
                                                </div>&nbsp;&nbsp;&nbsp;
                                            </td>
                                            <td >
                                                <div class="btn bg-blue btn-xs waves-effect" onclick="mostrarmodalgenerarpdf()">
                                                    Generar PDF
                                                </div>&nbsp;&nbsp;&nbsp;
                                            </td>
                        		        </tr>
                        	        </table>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">     
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                    <div class="row">
                                        <div class="col-md-7 col-md-offset-4">
                                            <select class="form-control select2" name="periodo" id="periodo" onchange="relistar()" style="width75% !important;">
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
                    						<th>Nota</th>
                                            <th>Proveedor</th>
                    						<th><div style="width:200px !important;">Nombre</div></th>
                                            <th>Fecha</th>
                                            <th>NotaProveedor</th>
                                            <th>Almacen</th>
                                            <th><div style="width:200px !important;">UUID</div></th>
                                            <th>SubTotal</th>
                                            <th>Iva</th>
                                            <th>Total</th>
                                            <th><div style="width:200px !important;">Obs</div></th>
                                            <th>Status</th>
                                            <th><div style="width:200px !important;">MotivoBaja</div></th>
                                            <th>Equipo</th>
                                            <th>Usuario</th>
                                            <th>Periodo</th>
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
                <div class="modal-body">
                    <form id="formxml" action="#" enctype="multipart/form-data">
                        <div class="col-md-12">
                            
                            <table class="col-md-12">
                                <tr>
                                    <td>
                                        <div class="col-md-6">
                                            <label>Selecciona el xml</label>
                                            <input type="file" class="form-control" name="xml" id="xml" onchange="cambiodexml(this)" onclick="this.value=null;">
                                            <button type="button" class="btn btn-success btn-sm" id="btnenviarxml" style="display:none">Enviar XML</button>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Folio Fiscal</label>
                                            <input type="text" class="form-control" name="uuidxml" id="uuidxml" readonly required>
                                        </div>
                                    </td>
                                </tr>
                            </table>   
                        </div>
                    </form>
                    <form id="formparsley" action="#">
                        <div class="col-md-12" id="tabsform">
                            <!-- aqui van los formularios de alta o modificacion y se agregan automaticamente con jquery -->
                        </div>
                    </form> 
                </div>
                <div class="modal-footer">
                    <div class="col-md-4">
                        <h5 style="color:#F44336 !important;"  id="totalfacturaproveedor"></h5>
                    </div>
                    <div class="col-md-4">
                        <h5 style="color:#F44336 !important;" id="diferenciafacturaproveedor"></h5>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-danger btn-sm" onclick="limpiar();limpiarmodales();" data-dismiss="modal">Salir</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardar">Guardar</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnGuardarModificacion">Guardar</button>
                    </div>
                        
                </div>
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
                    <input type="hidden" id="compradesactivar" name="compradesactivar">
                    <div id="divmotivobaja">
                        <label>Motivo Baja</label>
                        <textarea class="form-control" name="motivobaja" id="motivobaja" rows=2 onkeyup="tipoLetra(this)"></textarea>
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
<!-- Modal Movimientos-->
<div class="modal fade" data-keyboard="false" id="modalmovimientoscompra" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header bg-red">
        		<h5 class="modal-title" id="exampleModalLabel">Movimientos</h5>
      		</div>
      		<div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <td class="customercolortheadth">Movimiento</td>
                                    <td class="customercolortheadth">Número</td>
                                    <td class="customercolortheadth">Fecha</td>
                                    <td class="customercolortheadth">Abono $</td>
                                    <td class="customercolortheadth">Status</td>
                                </tr>
                            </thead>
                            <tbody id="filasmovimientos"></tbody>
                        </table> 
                    </div>
                </div>    	
      		</div>
    	</div>
  	</div>
</div> 
<!-- Modal Generar PDF-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalgenerarpdf" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header bg-red">
        		<h5 class="modal-title" id="exampleModalLabel">Generación de PDF's</h5>
      		</div>
      		<div class="modal-body">
                  <form id="formgenerarpdf" action='{{ url("/notas_credito_proveedores_generar_pdfs") }}' method="POST" data-parsley-validate="" target="_blank">
                    @csrf
		        	<h5 id="textomodalgenerarpdf"> </h5>
                    <div class="row">
                        <label>Generar PDF's por:</label>
                        <div class="col-md-12 form-check">
                            <input type="radio" name="tipogeneracionpdf" id="tipogeneracionpdf" value="0" onchange="mostrartipogeneracionpdf()" required>
                            <label for="tipogeneracionpdf">Selección de Folios</label>
                            <input type="radio" name="tipogeneracionpdf" id="tipogeneracionpdf1" value="1" onchange="mostrartipogeneracionpdf()" required>
                            <label for="tipogeneracionpdf1">Filtrado de Fechas</label>
                        </div>
                    </div>
                    <div class="row">
                        <div id="tiposeleccionfolios" hidden>
                            <div class="col-md-12">
                                <input type="text" name="buscarfolio" id="buscarfolio" class="form-control" placeholder="Teclea el folio..." autocomplete="off" onkeyup="buscarstringlike(this.value)">
                            </div>    
                            <div class="col-md-12 table-responsive">
                                <table id="tablafoliosencontrados" class="tablafoliosencontrados table table-bordered table-striped table-hover" style="width:100% !important;"> 
                                        <thead class="customercolor">
                                            <tr>
                                                <th><div style="width:80px !important;">Generar PDF</div></th>
                                                <th>Nota</th>
                                                <th>Proveedor</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                </table>
                            </div>
                            <div class="col-md-12">
                                <select  name="arraypdf[]" id="arraypdf" class="form-control select2" multiple="multiple" style="width:100% !important;" >
                                </select>
                            </div>
                        </div>
                        <div id="tipofiltracionfechas" hidden>

                            <div class="col-md-12">
                                <label >Fecha Inicio</label>
                                <input type="date"  name="fechainiciopdf" id="fechainiciopdf" class="form-control"  >
                            </div>
                            <div class="col-md-12">
                            <label >Fecha Terminación</label>
                                <input type="date"  name="fechaterminacionpdf" id="fechaterminacionpdf" class="form-control"  >
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
	        	        <div class="btn btn-danger btn-sm" data-dismiss="modal">Salir</div>
	        	        <button type="submit" class="btn btn-success btn-sm" >Generar PDF's</button>
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
        var periodohoy = '{{$periodohoy}}';
        var meshoy = '{{$meshoy}}';
        var notas_credito_proveedores_obtener = '{!!URL::to('notas_credito_proveedores_obtener')!!}';
        var notas_credito_proveedores_obtener_ultimo_folio = '{!!URL::to('notas_credito_proveedores_obtener_ultimo_folio')!!}';
        var notas_credito_proveedores_obtener_proveedores = '{!!URL::to('notas_credito_proveedores_obtener_proveedores')!!}';
        var notas_credito_proveedores_obtener_almacenes = '{!!URL::to('notas_credito_proveedores_obtener_almacenes')!!}';
        var notas_credito_proveedores_obtener_compras = '{!!URL::to('notas_credito_proveedores_obtener_compras')!!}'; 
        var notas_credito_proveedores_obtener_compra = '{!!URL::to('notas_credito_proveedores_obtener_compra')!!}';
        var notas_credito_proveedor_obtener_codigos_compra = '{!!URL::to('notas_credito_proveedor_obtener_codigos_compra')!!}';
        var notas_credito_proveedor_guardar = '{!!URL::to('notas_credito_proveedor_guardar')!!}';
        var notas_credito_proveedor_cargar_xml_alta = '{!!URL::to('notas_credito_proveedor_cargar_xml_alta')!!}';
        var notas_credito_proveedor_obtener_existencias_partida = '{!!URL::to('notas_credito_proveedor_obtener_existencias_partida')!!}';


        var compras_obtener_claves_productos = '{!!URL::to('compras_obtener_claves_productos')!!}'; 
        var compras_obtener_claves_unidades = '{!!URL::to('compras_obtener_claves_unidades')!!}'; 
        var compras_obtener_movimientos_compra = '{!!URL::to('compras_obtener_movimientos_compra')!!}';
        var compras_obtener_compra = '{!!URL::to('compras_obtener_compra')!!}';
        var compras_guardar_modificacion = '{!!URL::to('compras_guardar_modificacion')!!}';
        var compras_verificar_uso_en_modulos =  '{!!URL::to('compras_verificar_uso_en_modulos')!!}';
        var compras_alta_o_baja = '{!!URL::to('compras_alta_o_baja')!!}'; 
        var notas_credito_proveedores_buscar_folio_string_like = '{!!URL::to('notas_credito_proveedores_buscar_folio_string_like')!!}'; 
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/notascreditoproveedores/notascreditoproveedores.js"></script>
    
@endsection