@extends('plantilla_maestra')
@section('titulo')
  Reporte Relación Ordenes Compra
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
                        <div class="header bg-red table-responsive button-demo">
                        	<table>
                        		<tr>
                        			<td >
                        				<h5>&nbsp;&nbsp;&nbsp;Reporte Relación Ordenes Compra&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                                    <td >
                                        <div class="btn bg-blue btn-xs waves-effect" onclick="generar_reporte()">
                                            Ver Reporte
                                        </div>
                                    </td>
                                    <td>
                                        <div class="">
                                            <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoReporteRelacionOrdenesCompra" onclick="generar_formato_excel()">Generar Reporte en Excel</a>
                                        </div>  
                                    </td>
                        		</tr>
                        	</table>
                        </div>
                        <div class="body">
                            <form id="formrelacionordenescompra">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Proveedor <span class="label label-danger" id="textonombreproveedor"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="obtenerproveedores()">Seleccionar</div>
                                                </td>
                                                <td>
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="numeroproveedor" id="numeroproveedor" data-parsley-type="integer" autocomplete="off">
                                                        <input type="hidden" class="form-control" name="numeroproveedoranterior" id="numeroproveedoranterior" data-parsley-type="integer">
                                                        <input type="hidden" class="form-control" name="proveedor" id="proveedor" readonly onkeyup="tipoLetra(this)">
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Almacen <span class="label label-danger" id="textonombrealmacen"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" id="btnobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>
                                                </td>
                                                <td> 
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="numeroalmacen" id="numeroalmacen" data-parsley-type="integer" autocomplete="off">
                                                        <input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" data-parsley-type="integer">
                                                        <input type="hidden" class="form-control" name="almacen" id="almacen" readonly onkeyup="tipoLetra(this)">
                                                    </div>
                                                </td>
                                            </tr>  
                                        </table>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Fecha Inicial</label>
                                        <input type="date" class="form-control" name="fechainicialreporte" id="fechainicialreporte" onchange="generar_reporte()" required>
                                    </div>  
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Fecha Final</label>
                                        <input type="date" class="form-control" name="fechafinalreporte" id="fechafinalreporte" onchange="generar_reporte()" required>
                                    </div> 
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Tipo</label>
                                        <select class="form-control select2" name="tipo" id="tipo" onchange="generar_reporte()"  required>
                                            <option value="LIQUIDADA">LIQUIDADA</option>
                                            <option value="POR PAGAR">POR PAGAR</option>
                                        </select>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Status</label>
                                        <select class="form-control select2" name="status" id="status" onchange="generar_reporte()"  required>
                                            <option value="TODOS">TODOS</option>
                                            <option value="POR SURTIR">POR SURTIR</option>
                                            <option value="SURTIDO">SURTIDO</option>
                                            <option value="BACKORDER">BACKORDER</option>
                                            <option value="BAJA">BAJA</option>
                                        </select>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Reporte</label>
                                        <select class="form-control select2" name="reporte" id="reporte" onchange="generar_reporte()"  required>
                                            <option value="RELACION">RELACION</option>
                                            <option value="DETALLES">DETALLES</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover display nowrap" style="width:100% !important;">
                                    <thead class="{{$empresa->background_tables}}">
                                        <tr id="cabecerastablareporte">
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
                </div>
                <div id="contenidomodaltablas">
                    <!-- aqui van las tablas de seleccion y se agregan automaticamente con jquery -->
                </div> 
            </div>
        </div>
    </div>
    <!-- fin modal para configuraciones de tablas-->
@endsection
@section('additionals_js')
    <script>
        /*urls y variables renderizadas con blade*/
        var mayusculas_sistema = '{{$mayusculas_sistema}}';
        var numerodecimales = '{{$numerodecimales}}';
        var numerocerosconfigurados = '{{$numerocerosconfigurados}}';
        var numerocerosconfiguradosinputnumberstep = '{{$numerocerosconfiguradosinputnumberstep}}';
        var urlgenerarformatoexcelrelacionordenescompra = '{{$urlgenerarformatoexcelrelacionordenescompra}}';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';
        var reporte_relacion_ordenes_compra_obtener_tipos_ordenes_compra = '{!!URL::to('reporte_relacion_ordenes_compra_obtener_tipos_ordenes_compra')!!}';
        var reporte_relacion_ordenes_compra_obtener_proveedores = '{!!URL::to('reporte_relacion_ordenes_compra_obtener_proveedores')!!}';
        var reporte_relacion_ordenes_compra_obtener_almacenes = '{!!URL::to('reporte_relacion_ordenes_compra_obtener_almacenes')!!}';
        var reporte_relacion_ordenes_compra_obtener_proveedor_por_numero = '{!!URL::to('reporte_relacion_ordenes_compra_obtener_proveedor_por_numero')!!}';
        var reporte_relacion_ordenes_compra_obtener_almacen_por_numero = '{!!URL::to('reporte_relacion_ordenes_compra_obtener_almacen_por_numero')!!}';

        var reporte_relacion_ordenes_compra_generar_reporte = '{!!URL::to('reporte_relacion_ordenes_compra_generar_reporte')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/reportes/ordenescompra/reporterelacionordenescompra.js"></script>
@endsection