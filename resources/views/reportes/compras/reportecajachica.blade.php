@extends('plantilla_maestra')
@section('titulo')
  Reporte Caja Chica
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
                        				<h5>&nbsp;&nbsp;&nbsp;Reporte Caja Chica&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                                    <td >
                                        <div class="btn bg-blue btn-xs waves-effect" onclick="generar_reporte()">
                                            Ver Reporte
                                        </div>
                                    </td>
                                    <td>
                                        <div class="" >
                                            <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcelCajaChica" onclick="generar_formato_excel()">Generar Reporte en Excel</a>
                                        </div>  
                                    </td>
                        		</tr>
                        	</table>
                        </div>
                        <div class="body">
                            <form id="formcajachica">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Fecha Inicial Reporte</label>
                                        <input type="date" class="form-control" name="fechainicialreporte" id="fechainicialreporte" onchange="generar_reporte()" required>
                                    </div>  
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Fecha Final Reporte</label>
                                        <input type="date" class="form-control" name="fechafinalreporte" id="fechafinalreporte" onchange="generar_reporte()" required>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Status Compra</label>
                                        <select class="form-control select2" name="statuscompra" id="statuscompra" onchange="generar_reporte()"  required>
                                            <option value="TODOS">TODOS</option>    
                                            <option value="LIQUIDADA">LIQUIDADA</option>
                                            <option value="POR PAGAR">POR PAGAR</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2" hidden>
                                        <label>String Compras</label>
                                        <input type="text" class="form-control" name="string_compras" id="string_compras" value="0" required>
                                    </div> 
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover display nowrap" style="width:100% !important;">
                                    <thead class="customercolor">
                                        <tr>
                                            <th>OPERACIONES</th>
                                            <th>FECHA</th>
                                            <th>MOVIMIENTO COMPRA</th>
                                            <th>PROVEEDOR</th>
                                            <th>UUID</th>
                                            <th>FACTURA</th>
                                            <th>CONCEPTO DE PAGO</th>
                                            <th>OBSERVACIONES</th>
                                            <th>SUBTOTAL</th>
                                            <th>IVA</th>
                                            <th>IVA RETENCIÓN</th>
                                            <th>IMP HOSPEDAJE</th>
                                            <th>TOTAL</th>
                                            <th>DEPTO</th>
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
        var urlgenerarformatoexcelcajachica = '{{$urlgenerarformatoexcelcajachica}}';
        var generar_reporte_caja_chica = '{!!URL::to('generar_reporte_caja_chica')!!}';
        var generar_excel_reporte_diario_ventas = '{!!URL::to('generar_excel_reporte_diario_ventas')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/reportes/compras/reportecajachica.js"></script>
@endsection