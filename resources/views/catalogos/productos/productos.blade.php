@extends('plantilla_maestra')
@section('titulo')
  Productos
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
                        	<table>
                        		<tr>
                        			<td>
                        				<h5>&nbsp;&nbsp;&nbsp;&nbsp;Productos&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                        			<td >
                            			<div class="btn bg-blue btn-xs waves-effect" onclick="alta()">
                                    		Altas
                                		</div>
                        			</td>
                        		</tr>
                        	</table>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover" style="width:100% !important;">
                                    <thead class="customercolor">
                                        <tr>
                                            <th><div style="width:80px !important;">Operaciones</div></th>
                                            <th>Codigo</th>
                                            <th>ClaveProducto</th>
                                            <th>ClaveUnidad</th>
                                            <th>Producto</th>
                                            <th>Unidad</th>
                                            <th>Ubicacion</th>
                                            <th>Existencias</th>
                                            <th>Costo</th>
                                            <th>CostoDeLista</th>
                                            <th>Moneda</th>
                                            <th>CostoDeVenta</th>
                                            <th>Utilidad</th>
                                            <th>SubTotal</th>
                                            <th>Iva</th>
                                            <th>Total</th>
                                            <th>Marca</th>
                                            <th>Linea</th>
                                            <th>NombreMarca</th>
                                            <th>NombreLinea</th>
                                            <th>Status</th>
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
                                <div class="col-md-4">
                                    <label>Código<b style="color:#F44336 !important;">*</b></label>
                                    <input type="text" class="form-control" name="codigo" id="codigo" required onkeyup="tipoLetra(this);">
                                </div>   
                                <div class="col-md-4">
                                    <label>Clave Producto<b style="color:#F44336 !important;">*</b></label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <span class="input-group-btn">
                                                <div id="buscarclavesproductos" class="btn bg-blue waves-effect" onclick="listarclavesproductos()">Seleccionar</div>
                                            </span>
                                        </div>  
                                        <div class="col-md-8"> 
                                            <div class="form-line">
                                                <input type="text" class="form-control" name="claveproducto" id="claveproducto" required readonly>
                                            </div>
                                        </div>    
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>Clave Unidad<b style="color:#F44336 !important;">*</b></label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <span class="input-group-btn">
                                                <div id="buscarclavesunidades" class="btn bg-blue waves-effect" onclick="listarclavesunidades()">Seleccionar</div>
                                            </span>
                                        </div>  
                                        <div class="col-md-8"> 
                                            <div class="form-line">
                                                <input type="text" class="form-control" name="claveunidad" id="claveunidad" required readonly>
                                            </div>
                                        </div>    
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <label>Producto<b style="color:#F44336 !important;">*</b></label>
                                    <input type="text" class="form-control" name="producto" id="producto" required onkeyup="tipoLetra(this);">
                                </div>
                                <div class="col-md-4">
                                    <label>Unidad<b style="color:#F44336 !important;">*</b></label>
                                    <input type="text" class="form-control" name="unidad" id="unidad" required onkeyup="tipoLetra(this);">
                                </div>
                            </div>
                            <div class="col-md-12" id="tabsform">
                                
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
    <!-- Modal Baja o Alta-->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="estatusregistro" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-red">
                    <h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
                </div>
                <div class="modal-body">
                    <form id="formdesactivar" action="#">
                        <h5>Esta seguro de dar de baja este registro?</h5>
                        <input type="hidden" id="codigoproducto" name="codigoproducto">
                    </form>	
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
                    <button type="button" class="btn btn-success btn-sm" id="aceptar">Guardar</button>
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
        var productos_obtener = '{!!URL::to('productos_obtener')!!}';
        var productos_obtener_claves_productos = '{!!URL::to('productos_obtener_claves_productos')!!}';
        var productos_obtener_claves_unidades = '{!!URL::to('productos_obtener_claves_unidades')!!}';
        var productos_obtener_marcas = '{!!URL::to('productos_obtener_marcas')!!}';
        var productos_obtener_lineas = '{!!URL::to('productos_obtener_lineas')!!}';
        var productos_obtener_monedas = '{!!URL::to('productos_obtener_monedas')!!}';
        var productos_obtener_utilidades = '{!!URL::to('productos_obtener_utilidades')!!}';
        var productos_obtener_existencias_almacenes = '{!!URL::to('productos_obtener_existencias_almacenes')!!}';
        var productos_obtener_clientes = '{!!URL::to('productos_obtener_clientes')!!}';
        var productos_obtener_productos_consumos = '{!!URL::to('productos_obtener_productos_consumos')!!}';
        var productos_guardar = '{!!URL::to('productos_guardar')!!}';
        var productos_alta_o_baja = '{!!URL::to('productos_alta_o_baja')!!}'; 
        var productos_obtener_producto = '{!!URL::to('productos_obtener_producto')!!}'; 
        var productos_guardar_modificacion = '{!!URL::to('productos_guardar_modificacion')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/catalogos/productos/productos.js"></script>
@endsection