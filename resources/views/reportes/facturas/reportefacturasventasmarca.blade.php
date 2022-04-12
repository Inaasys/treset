@extends('plantilla_maestra')
@section('titulo')
  Reporte Relación Ventas Marcas
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
                        				<h5>&nbsp;&nbsp;&nbsp;Reporte Relación Ventas Marcas&nbsp;&nbsp;&nbsp;</h5>
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
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
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
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
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
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
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
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
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
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
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
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-2">
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
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Fecha Inicial</label>
                                        <input type="date" class="form-control" name="fechainicialreporte" id="fechainicialreporte" required>
                                    </div>  
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Fecha Final</label>
                                        <input type="date" class="form-control" name="fechafinalreporte" id="fechafinalreporte" required>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Producto <span class="label label-danger" id="textonombreproducto"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td> 
                                                    <div class="form-line">
                                                        <input type="text" class="form-control" name="producto" id="producto" onkeyup="tipoLetra(this)" autocomplete="off">
                                                        <input type="hidden" class="form-control" name="productoanterior" id="productoanterior" onkeyup="tipoLetra(this)">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="col-md-12 form-check">
                                                        <input type="checkbox" name="productoigual" id="idproductoigual" class="filled-in" value="1" />
                                                        <label for="idproductoigual">Igual</label>
                                                    </div>
                                                </td>
                                            </tr>  
                                        </table>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Tipo</label>
                                        <select class="form-control select2" name="tipo" id="tipo" onchange="generar_reporte()"  required>
                                        </select>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Departamento</label>
                                        <select class="form-control select2" name="departamento" id="departamento" onchange="generar_reporte()"  required>
                                            <option value="TODOS">TODOS</option>
                                            <option value="PRODUCTOS">PRODUCTOS</option>
                                            <option value="SERVICIO">SERVICIO</option>
                                            <option value="LIBRE">LIBRE</option>
                                        </select>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Documentos</label>
                                        <select class="form-control select2" name="documentos" id="documentos" onchange="generar_reporte()"  required>
                                            <option value="TODOS">TODOS</option>
                                            <option value="FACTURAS">FACTURAS</option>
                                            <option value="INTERNOS">INTERNOS</option>
                                        </select>
                                    </div> 
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Status</label>
                                        <select class="form-control select2" name="status" id="status" onchange="generar_reporte()"  required>
                                            <option value="TODOS">TODOS</option>
                                            <option value="POR COBRAR">POR COBRAR</option>
                                            <option value="LIQUIDADO">LIQUIDADO</option>
                                            <option value="BAJA">BAJA</option>
                                        </select>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                                        <label>Reporte</label>
                                        <select class="form-control select2" name="reporte" id="reporte" onchange="generar_reporte()"  required>
                                            <option value="RELACIONUTILIDADES">RELACION UTILIDADES</option>
                                            <option value="UTILIDADCAMBIARIA">UTILIDAD CAMBIARIA</option>
                                            <option value="COMPARARUTILIDADCOSTO">COMPARAR UTILIDAD COSTO</option>
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
        var reporte_facturas_ventas_marca_obtener_tipos_ordenes_compra = '{!!URL::to('reporte_facturas_ventas_marca_obtener_tipos_ordenes_compra')!!}';
        var reporte_facturas_ventas_marca_obtener_clientes = '{!!URL::to('reporte_facturas_ventas_marca_obtener_clientes')!!}';
        var reporte_facturas_ventas_marca_obtener_cliente_por_numero = '{!!URL::to('reporte_facturas_ventas_marca_obtener_cliente_por_numero')!!}';
        var reporte_facturas_ventas_marca_obtener_agentes = '{!!URL::to('reporte_facturas_ventas_marca_obtener_agentes')!!}';
        var reporte_facturas_ventas_marca_obtener_agente_por_numero = '{!!URL::to('reporte_facturas_ventas_marca_obtener_agente_por_numero')!!}';
        var reporte_facturas_ventas_marca_obtener_series = '{!!URL::to('reporte_facturas_ventas_marca_obtener_series')!!}';
        var reporte_facturas_ventas_marca_obtener_serie_por_clave = '{!!URL::to('reporte_facturas_ventas_marca_obtener_serie_por_clave')!!}';
        var reporte_facturas_ventas_marca_obtener_almacenes = '{!!URL::to('reporte_facturas_ventas_marca_obtener_almacenes')!!}';
        var reporte_facturas_ventas_marca_obtener_almacen_por_numero = '{!!URL::to('reporte_facturas_ventas_marca_obtener_almacen_por_numero')!!}';
        var reporte_facturas_ventas_marca_obtener_marcas = '{!!URL::to('reporte_facturas_ventas_marca_obtener_marcas')!!}';
        var reporte_facturas_ventas_marca_obtener_marca_por_numero = '{!!URL::to('reporte_facturas_ventas_marca_obtener_marca_por_numero')!!}';
        var reporte_facturas_ventas_marca_obtener_lineas = '{!!URL::to('reporte_facturas_ventas_marca_obtener_lineas')!!}';
        var reporte_facturas_ventas_marca_obtener_linea_por_numero = '{!!URL::to('reporte_facturas_ventas_marca_obtener_linea_por_numero')!!}';
        var reporte_facturas_ventas_marca_obtener_productos = '{!!URL::to('reporte_facturas_ventas_marca_obtener_productos')!!}';
        var reporte_facturas_ventas_marca_obtener_producto_por_codigo = '{!!URL::to('reporte_facturas_ventas_marca_obtener_producto_por_codigo')!!}';
        var reporte_facturas_ventas_marca_generar_reporte = '{!!URL::to('reporte_facturas_ventas_marca_generar_reporte')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/reportes/facturas/reportefacturasventasmarca.js"></script>
@endsection