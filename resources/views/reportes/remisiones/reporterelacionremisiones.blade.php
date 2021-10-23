@extends('plantilla_maestra')
@section('titulo')
  Reporte Relación Remisiones
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
                        				<h5>&nbsp;&nbsp;&nbsp;Reporte Relación Remisiones&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                                    <td >
                                        <div class="btn bg-blue btn-xs waves-effect" onclick="generar_reporte()">
                                            Ver Reporte
                                        </div>
                                    </td>
                                    <td>
                                        <div class="">
                                            <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoReporteExcel" onclick="generar_formato_excel()">Generar Reporte en Excel</a>
                                        </div>  
                                    </td>
                        		</tr>
                        	</table>
                        </div>
                        <div class="body">
                            <form id="formreporte">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Cliente <span class="label label-danger" id="textonombrecliente"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="obtenerclientes()">Seleccionar</div>
                                                </td>
                                                <td>
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="numerocliente" id="numerocliente" data-parsley-type="integer" autocomplete="off">
                                                        <input type="hidden" class="form-control" name="numeroclienteanterior" id="numeroclienteanterior" data-parsley-type="integer">
                                                        <input type="hidden" class="form-control" name="cliente" id="cliente" readonly onkeyup="tipoLetra(this)">
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Agente <span class="label label-danger" id="textonombreagente"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="obteneragentes()">Seleccionar</div>
                                                </td>
                                                <td> 
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="numeroagente" id="numeroagente" data-parsley-type="integer" autocomplete="off">
                                                        <input type="hidden" class="form-control" name="numeroagenteanterior" id="numeroagenteanterior" data-parsley-type="integer">
                                                        <input type="hidden" class="form-control" name="agente" id="agente" readonly onkeyup="tipoLetra(this)">
                                                    </div>
                                                </td>
                                            </tr>  
                                        </table>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Serie <span class="label label-danger" id="textonombreserie"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="obtenerseries()">Seleccionar</div>
                                                </td>
                                                <td> 
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="claveserie" id="claveserie" onkeyup="tipoLetra(this)" autocomplete="off">
                                                        <input type="hidden" class="form-control" name="claveserieanterior" id="claveserieanterior" onkeyup="tipoLetra(this)">
                                                        <input type="hidden" class="form-control" name="serie" id="serie" readonly onkeyup="tipoLetra(this)">
                                                    </div>
                                                </td>
                                            </tr>  
                                        </table>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Forma Pago <span class="label label-danger" id="textonombreformapago"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" onclick="obtenerformaspago()">Seleccionar</div>
                                                </td>
                                                <td> 
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="claveformapago" id="claveformapago" onkeyup="tipoLetra(this)" autocomplete="off">
                                                        <input type="hidden" class="form-control" name="claveformapagoanterior" id="claveformapagoanterior" onkeyup="tipoLetra(this)">
                                                        <input type="hidden" class="form-control" name="formapago" id="formapago" readonly onkeyup="tipoLetra(this)">
                                                    </div>
                                                </td>
                                            </tr>  
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Fecha Inicial</label>
                                        <input type="date" class="form-control" name="fechainicialreporte" id="fechainicialreporte" onchange="generar_reporte()" required>
                                    </div>  
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Fecha Final</label>
                                        <input type="date" class="form-control" name="fechafinalreporte" id="fechafinalreporte" onchange="generar_reporte()" required>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Tipo</label>
                                        <select class="form-control select2" name="tipo" id="tipo" onchange="generar_reporte()"  required>
                                        </select>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Status</label>
                                        <select class="form-control select2" name="status" id="status" onchange="generar_reporte()"  required>
                                            <option value="TODOS">TODOS</option>
                                            <option value="POR FACTURAR">POR FACTURAR</option>
                                            <option value="FACTURADOS">FACTURADOS</option>
                                            <option value="BAJA">BAJA</option>
                                        </select>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Reporte</label>
                                        <select class="form-control select2" name="reporte" id="reporte" onchange="generar_reporte()"  required>
                                            <!--<option value="UTILIDAD">UTILIDAD</option>-->
                                            <option value="GENERAL">GENERAL</option>
                                            <option value="PRODUCTOS">PRODUCTOS</option>
                                            <!--<option value="VENTAS">VENTAS</option>
                                            <option value="PAGOS">PAGOS</option>
                                            <option value="REMISIONES">REMISIONES</option>-->
                                            <option value="RESUMEN">RESUMEN</option>
                                            <option value="MENSUAL">MENSUAL</option>
                                            <option value="POTENCIALES">POTENCIALES</option>
                                            <option value="CORTE">CORTE</option>
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
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';
        var reporte_relacion_remisiones_obtener_tipos_ordenes_compra = '{!!URL::to('reporte_relacion_remisiones_obtener_tipos_ordenes_compra')!!}';
        var reporte_relacion_remisiones_obtener_clientes = '{!!URL::to('reporte_relacion_remisiones_obtener_clientes')!!}';
        var reporte_relacion_remisiones_obtener_agentes = '{!!URL::to('reporte_relacion_remisiones_obtener_agentes')!!}';
        var reporte_relacion_remisiones_obtener_series = '{!!URL::to('reporte_relacion_remisiones_obtener_series')!!}';
        var reporte_relacion_remisiones_obtener_formaspago = '{!!URL::to('reporte_relacion_remisiones_obtener_formaspago')!!}';
        var reporte_relacion_remisiones_obtener_cliente_por_numero = '{!!URL::to('reporte_relacion_remisiones_obtener_cliente_por_numero')!!}';
        var reporte_relacion_remisiones_obtener_agente_por_numero = '{!!URL::to('reporte_relacion_remisiones_obtener_agente_por_numero')!!}';
        var reporte_relacion_remisiones_obtener_serie_por_clave = '{!!URL::to('reporte_relacion_remisiones_obtener_serie_por_clave')!!}';
        var reporte_relacion_remisiones_obtener_formapago_por_clave = '{!!URL::to('reporte_relacion_remisiones_obtener_formapago_por_clave')!!}';

        var reporte_relacion_remisiones_generar_reporte = '{!!URL::to('reporte_relacion_remisiones_generar_reporte')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/reportes/remisiones/reporterelacionremisiones.js"></script>
@endsection