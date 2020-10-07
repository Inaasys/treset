@extends('plantilla_maestra')
@section('titulo')
  Reporte Diario Ventas
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
                        				<h5>&nbsp;&nbsp;&nbsp;Reporte Diario Ventas&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                        			<td >
                            			<div class="btn bg-blue btn-xs waves-effect" onclick="realizar_reporte()">
                                    		Actualizar Reporte
                                		</div>
                        			</td>
                                    <td >
                                        <a class="btn bg-blue btn-xs waves-effect" onclick="realizar_excel_reporte()">
                                            Generar Excel
                                        </a>
                                    </td>
                        		</tr>
                        	</table>
                        </div>
                        <div class="body">
                            <form id="formventasdiarias" action="{{url('/generar_excel_reporte_diario_ventas')}}"  method="post" >
                                @csrf
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Fecha Final Reporte</label>
                                        <input type="date" class="form-control" name="fechafinalreporte" id="fechafinalreporte" required>
                                    </div>  
                                    <div class="col-md-2">
                                        <label>Objetivo Mensual en Pesos</label>
                                        <input type="number" step="0.{{$numerocerosconfiguradosinputnumberstep}}" class="form-control" name="objetivofinalpesos" id="objetivofinalpesos" value="3000000.{{$numerocerosconfigurados}}" data-parsley-min="1" onchange="formatocorrectoinputcantidades(this);" required>
                                    </div> 
                                    <div class="col-md-4">
                                        <input type="checkbox" name="porcliente" id="idporcliente" class="filled-in" onchange="filtrocliente();" />
                                        <label for="idporcliente"><b>Por Cliente</b></label>
                                        <div class="row" id="divfiltrocliente" hidden>
                                            <div class="col-md-3">
                                                <span class="input-group-btn">
                                                    <div id="buscarcodigospostales" class="btn bg-blue waves-effect" onclick="listarclientes()">Seleccionar</div>
                                                </span>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="form-line">
                                                    <input type="text" class="form-control" name="cliente" id="cliente" value="0" required readonly>
                                                    <input type="hidden" class="form-control" name="numerocliente" id="numerocliente" value="0" required readonly>
                                                    <input type="hidden" class="form-control" name="aplicarfiltrocliente" id="aplicarfiltrocliente" value="0" required readonly>
                                                </div>
                                            </div> 
                                        </div>
                                    </div>
                                    <div class="col-md-2" hidden>
                                        <button  class="btn btn-success btn-sm" id="btngenerarexcel">Generar Excel</button>
                                    </div>  
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover" style="width:100% !important;">
                                    <thead class="customercolor">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Dia</th>
                                            <th>Importe Día Sin Iva $</th>
                                            <th>Importe Esperado Día $</th>
                                            <th>Faltante/Sobrante $</th>
                                            <th>Acumulado Mes Sin Iva $</th>
                                            <th>Acumulado Esperado Mes $</th>
                                            <th>Faltante/Sobrante Acumulado $</th>
                                            <th>Objetivo Final %</th>
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
        var generar_reporte_diario_ventas = '{!!URL::to('generar_reporte_diario_ventas')!!}';
        var generar_excel_reporte_diario_ventas = '{!!URL::to('generar_excel_reporte_diario_ventas')!!}';
        var reporte_ventas_diarias_obtener_clientes = '{!!URL::to('reporte_ventas_diarias_obtener_clientes')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/reportes/facturas/reportediarioventas.js"></script>
@endsection