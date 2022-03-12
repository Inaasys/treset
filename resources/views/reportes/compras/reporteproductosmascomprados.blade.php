@extends('plantilla_maestra')
@section('titulo')
  Reporte Productos más Comprados
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
                        				<h5>&nbsp;&nbsp;&nbsp;Reporte Productos más Comprados&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                                    <td >
                                        <div class="btn bg-blue btn-xs waves-effect" onclick="generar_reporte()">
                                            Ver Reporte
                                        </div>
                                    </td>
                                    <td>
                                        <div class="">
                                            <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" onclick="generar_formato_excel()">Generar Reporte en Excel</a>
                                        </div>  
                                    </td>
                        		</tr>
                        	</table>
                        </div>
                        <div class="body">
                            <form id="formreporte">
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
                                        <label>Marca <span class="label label-danger" id="textonombremarca"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" id="btnobtenermarcas" onclick="obtenermarcas()">Seleccionar</div>
                                                </td>
                                                <td> 
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="numeromarca" id="numeromarca" data-parsley-type="integer" autocomplete="off">
                                                        <input type="hidden" class="form-control" name="numeromarcaanterior" id="numeromarcaanterior" data-parsley-type="integer">
                                                        <input type="hidden" class="form-control" name="marca" id="marca" readonly onkeyup="tipoLetra(this)">
                                                    </div>
                                                </td>
                                            </tr>  
                                        </table>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Linea <span class="label label-danger" id="textonombrelinea"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="btn bg-blue waves-effect" id="btnobtenerlineas" onclick="obtenerlineas()">Seleccionar</div>
                                                </td>
                                                <td> 
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="numerolinea" id="numerolinea" data-parsley-type="integer" autocomplete="off">
                                                        <input type="hidden" class="form-control" name="numerolineaanterior" id="numerolineaanterior" data-parsley-type="integer">
                                                        <input type="hidden" class="form-control" name="linea" id="linea" readonly onkeyup="tipoLetra(this)">
                                                    </div>
                                                </td>
                                            </tr>  
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Fecha Inicial</label>
                                        <input type="date" class="form-control" name="fechainicialreporte" id="fechainicialreporte" required>
                                    </div>  
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <label>Fecha Final</label>
                                        <input type="date" class="form-control" name="fechafinalreporte" id="fechafinalreporte" required>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-2">
                                        <label>Status</label>
                                        <select class="form-control select2" name="status" id="status" onchange="generar_reporte()"  required>
                                            <option value="TODOS">TODOS</option>
                                            <option value="POR PAGAR">POR PAGAR</option>
                                            <option value="LIQUIDADA">LIQUIDADA</option>
                                            <option value="BAJA">BAJA</option>
                                        </select>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-2">
                                        <label>Reporte</label>
                                        <select class="form-control select2" name="reporte" id="reporte" onchange="generar_reporte()"  required>
                                            <option value="PORMARCAS">POR MARCAS</option>
                                            <option value="PORLINEAS">POR LINEAS</option>
                                            <option value="PORPROVEEDORES">POR PROVEEDORES</option>
                                            <option value="PORCODIGOS">POR CODIGOS</option>
                                        </select>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Ordenar por Total</label>
                                        <div class="col-md-12 form-check">
                                            <input type="checkbox" name="ordenarportotal" id="idordenarportotal" class="filled-in" value="1" onchange="generar_reporte()" />
                                            <label for="idordenarportotal">Ordenar por Total</label>
                                        </div>
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
        var reporte_productos_mas_comprados_obtener_proveedores = '{!!URL::to('reporte_productos_mas_comprados_obtener_proveedores')!!}';
        var reporte_productos_mas_comprados_obtener_almacenes = '{!!URL::to('reporte_productos_mas_comprados_obtener_almacenes')!!}';
        var reporte_productos_mas_comprados_obtener_proveedor_por_numero = '{!!URL::to('reporte_productos_mas_comprados_obtener_proveedor_por_numero')!!}';
        var reporte_productos_mas_comprados_obtener_almacen_por_numero = '{!!URL::to('reporte_productos_mas_comprados_obtener_almacen_por_numero')!!}';
        var reporte_productos_mas_comprados_obtener_marcas = '{!!URL::to('reporte_productos_mas_comprados_obtener_marcas')!!}';
        var reporte_productos_mas_comprados_obtener_lineas = '{!!URL::to('reporte_productos_mas_comprados_obtener_lineas')!!}';
        var reporte_productos_mas_comprados_obtener_marca_por_numero = '{!!URL::to('reporte_productos_mas_comprados_obtener_marca_por_numero')!!}';
        var reporte_productos_mas_comprados_obtener_linea_por_numero = '{!!URL::to('reporte_productos_mas_comprados_obtener_linea_por_numero')!!}';
        var reporte_productos_mas_comprados_generar_reporte = '{!!URL::to('reporte_productos_mas_comprados_generar_reporte')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/reportes/compras/reporteproductosmascomprados.js"></script>
@endsection