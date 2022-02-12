@extends('plantilla_maestra')
@section('titulo')
  Reporte Horas Técnico
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
                        				<h5>&nbsp;&nbsp;&nbsp;Reporte Horas Técnico&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                                    <td >
                                        <div class="btn bg-blue btn-xs waves-effect" onclick="generar_reporte()">
                                            Ver Reporte
                                        </div>
                                    </td>
                                    <td>
                                        <div class="" >
                                            <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcelHorasTecnico" onclick="generar_formato_excel()">Generar Reporte en Excel</a>
                                        </div>  
                                    </td>
                        		</tr>
                        	</table>
                        </div>
                        <div class="body">
                            <form id="formreporte">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Técnico / Sucursal</label>
                                        <select class="form-control select2" name="tiporeporte" id="tiporeporte" onchange="validartiporeporte()" required>
                                            <option value="Porsucursal" selected>Por Sucursal</option>
                                            <option value="Portecnico">Por Técnico</option>
                                        </select>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Fecha Inicial Reporte</label>
                                        <input type="date" class="form-control" name="fechainicialreporte" id="fechainicialreporte" onchange="generar_reporte()" required>
                                    </div>  
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Fecha Final Reporte</label>
                                        <input type="date" class="form-control" name="fechafinalreporte" id="fechafinalreporte" onchange="generar_reporte()" required>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Tipos Ordenes</label>
                                        <select class="form-control select2" name="tipoorden" id="tipoorden" onchange="generar_reporte()" required>
                                            <option value="TODOS" selected>TODOS</option>
                                            @foreach($tipos_ordenes_trabajo as $tipo)
                                                <option value="{{$tipo->Nombre}}">{{$tipo->Nombre}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Status Ordenes</label>
                                        <select class="form-control select2" name="statusorden" id="statusorden" onchange="generar_reporte()" required>
                                            <option value="TODOS" selected>TODOS</option>
                                            <option value="FACTURADAS">FACTURADAS</option>
                                            <option value="ABIERTAS">ABIERTAS</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row" id="divportecnico" hidden>
                                    <div class="col-md-3">
                                        <label>Selecciona Técnico</label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="listartecnicos()">Seleccionar</div>
                                                </td>
                                                <td>
                                                    <div class="form-line">
                                                        <input type="hidden" class="form-control" name="numerotecnico" id="numerotecnico" onkeyup="tipoLetra(this)">
                                                        <input type="text" class="form-control" name="tecnico" id="tecnico">
                                                    </div>
                                                </td>
                                            </tr>    
                                        </table>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2" >
                                        <label>Todos los técnicos</label>
                                        <div class="col-md-12 form-check">
                                            <input type="hidden" name="todoslostecnicos" value="0"/>
                                            <input type="checkbox" name="todoslostecnicos" id="idtodoslostecnicos" class="filled-in" value="1" onchange="generar_reporte()"  checked/>
                                            <label for="idtodoslostecnicos">Todos los técnicos</label>
                                        </div>
                                    </div> 
                                    <div class="col-md-2" hidden>
                                        <label>String Tecnicos</label>
                                        <input type="text" class="form-control" name="string_tecnicos_seleccionados" id="string_tecnicos_seleccionados" value="0">
                                    </div>
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover" style="width:100% !important;">
                                    <thead class="customercolor">
                                        <tr>
                                            <th>TÉCNICO</th>
                                            <th>NOMBRE</th>
                                            <th>HORAS</th>
                                            <th>TOTAL $</th>
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
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var generar_reporte_horas_tecnico = '{!!URL::to('generar_reporte_horas_tecnico')!!}';
        var reporte_horas_tecnico_obtener_tecnicos = '{!!URL::to('reporte_horas_tecnico_obtener_tecnicos')!!}';
        var generar_excel_reporte_diario_ventas = '{!!URL::to('generar_excel_reporte_diario_ventas')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/reportes/ordenestrabajo/horastecnico.js"></script>
@endsection