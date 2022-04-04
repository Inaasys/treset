@extends('plantilla_maestra')
@section('titulo')
  Reporte Unidades Servicio
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
                        <div class="header {{$empresa->background_forms_and_modals}} table-responsive button-demo">
                        	<table>
                        		<tr>
                        			<td >
                        				<h5>&nbsp;&nbsp;&nbsp;Reporte Unidades Servicio&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                                    <td >
                                        <div class="btn bg-blue btn-xs waves-effect" onclick="generar_reporte()">
                                            Ver Reporte
                                        </div>
                                    </td>
                                    <td>
                                        <div class="" >
                                            <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" onclick="generar_formato_excel()">Generar Reporte en Excel</a>
                                        </div>  
                                    </td>
                                    <td>
                                        <div class="">
                                            <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoReportePdf" onclick="generar_formato_pdf()" target="__blank">Generar Reporte en PDF</a>
                                        </div>  
                                    </td>
                        		</tr>
                        	</table>
                        </div>
                        <div class="body">
                            <form id="formreporte">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-3">
                                        <label>Facturar a <span class="label label-danger" id="textonombreclientefacturara"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="obtenerclientesfacturara()">Seleccionar</div>
                                                </td>
                                                <td>
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="numeroclientefacturara" id="numeroclientefacturara" data-parsley-type="integer" autocomplete="off">
                                                        <input type="hidden" class="form-control" name="numeroclienteanteriorfacturara" id="numeroclienteanteriorfacturara" data-parsley-type="integer">
                                                        <input type="hidden" class="form-control" name="clientefacturara" id="clientefacturara" readonly onkeyup="tipoLetra(this)">
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-3">
                                        <label>Del Cliente <span class="label label-danger" id="textonombreclientedelcliente"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="obtenerclientesdelcliente()">Seleccionar</div>
                                                </td>
                                                <td>
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="numeroclientedelcliente" id="numeroclientedelcliente" data-parsley-type="integer" autocomplete="off">
                                                        <input type="hidden" class="form-control" name="numeroclienteanteriordelcliente" id="numeroclienteanteriordelcliente" data-parsley-type="integer">
                                                        <input type="hidden" class="form-control" name="clientedelcliente" id="clientedelcliente" readonly onkeyup="tipoLetra(this)">
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-3">
                                        <label>Vin/Serie <span class="label label-danger" id="textonombrevin"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="obtenervines()">Seleccionar</div>
                                                </td>
                                                <td>
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="numerovin" id="numerovin" autocomplete="off" onkeyup="tipoLetra(this)">
                                                        <input type="hidden" class="form-control" name="numerovinanterior" id="numerovinanterior">
                                                        <input type="hidden" class="form-control" name="vin" id="vin" readonly onkeyup="tipoLetra(this)">
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Fecha Inicial Reporte</label>
                                        <input type="date" class="form-control" name="fechainicialreporte" id="fechainicialreporte" required>
                                    </div>  
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Fecha Final Reporte</label>
                                        <input type="date" class="form-control" name="fechafinalreporte" id="fechafinalreporte" required>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Tipo Orden</label>
                                        <select class="form-control select2" name="tipoorden" id="tipoorden" onchange="generar_reporte()" required>
                                            <option value="TODOS" selected>TODOS</option>
                                            @foreach($tipos_ordenes_trabajo as $tipo)
                                                <option value="{{$tipo->Nombre}}">{{$tipo->Nombre}}</option>
                                            @endforeach
                                        </select>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Tipo Unidad</label>
                                        <select class="form-control select2" name="tipounidad" id="tipounidad" onchange="generar_reporte()" required>
                                            <option value="TODOS" selected>TODOS</option>
                                            @foreach($tipos_unidades as $tipo)
                                                <option value="{{$tipo->Nombre}}">{{$tipo->Nombre}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Status</label>
                                        <select class="form-control select2" name="status" id="status" onchange="generar_reporte()" required>
                                            <option value="TODOS" selected>TODOS</option>
                                            <option value="FACTURADAS" >FACTURADAS</option>
                                            <option value="ABIERTAS">ABIERTAS</option>
                                            <option value="BAJA">BAJA</option>
                                        </select>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Reporte</label>
                                        <select class="form-control select2" name="reporte" id="reporte" onchange="generar_reporte()" required>
                                            <option value="NORMAL" >NORMAL</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover display nowrap" style="width:100% !important;">
                                    <thead class="customercolor">
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
@endsection
@section('additionals_js')
    <script>
        /*urls y variables renderizadas con blade*/
        var mayusculas_sistema = '{{$mayusculas_sistema}}';
        var numerodecimales = '{{$numerodecimales}}';
        var numerocerosconfigurados = '{{$numerocerosconfigurados}}';
        var numerocerosconfiguradosinputnumberstep = '{{$numerocerosconfiguradosinputnumberstep}}';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var urlgenerarformatopdf = '{{$urlgenerarformatopdf}}';
        var reporte_unidades_servicio_obtener_clientes_facturaa = '{!!URL::to('reporte_unidades_servicio_obtener_clientes_facturaa')!!}';
        var reporte_unidades_servicio_obtener_clientes_delcliente = '{!!URL::to('reporte_unidades_servicio_obtener_clientes_delcliente')!!}';
        var reporte_unidades_servicio_obtener_vines = '{!!URL::to('reporte_unidades_servicio_obtener_vines')!!}';
        var reporte_unidades_servicio_obtener_cliente_facturaa_por_numero = '{!!URL::to('reporte_unidades_servicio_obtener_cliente_facturaa_por_numero')!!}';
        var reporte_unidades_servicio_obtener_cliente_delcliente_por_numero = '{!!URL::to('reporte_unidades_servicio_obtener_cliente_delcliente_por_numero')!!}';
        var reporte_unidades_servicio_obtener_vin_por_clave = '{!!URL::to('reporte_unidades_servicio_obtener_vin_por_clave')!!}';
        var reporte_unidades_servicio_generar_reporte = '{!!URL::to('reporte_unidades_servicio_generar_reporte')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/reportes/ordenestrabajo/unidadesservicio.js"></script>
@endsection