@extends('plantilla_maestra')
@section('titulo')
  Productos
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
                        <div class="header {{$empresa->background_forms_and_modals}}">
                            <div class="row clearfix">
                                <div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">
                                    <h5>&nbsp;&nbsp;&nbsp;PRODUCTOS&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-11 col-md-11 col-sm-11 col-xs-12 button-demo">
                                    <div class="table-responsive navbar-right">
                                        <table>
                                            <tr>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta()">
                                                        Altas
                                                    </div>
                                                </td>
                                                <td >
                                                    <a class="btn bg-blue btn-xs waves-effect" href="{{route('productos_exportar_excel')}}" target="_blank">
                                                        Excel
                                                    </a>
                                                </td>
                                                <td>
                                                    <a class="btn bg-blue btn-xs waves-effect" onclick="generarcodigosbarras();">
                                                        Generar Códigos de Barras
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="configurar_tabla()">
                                                        Configurar Tabla
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover display nowrap" style="width:100% !important;">
                                    <thead class="{{$empresa->background_tables}}">
                                        <tr>
                                            <th><div style="width:100px !important;">Operaciones</div></th>
                                            @foreach(explode(',', $configuracion_tabla->columnas_ordenadas) as $co) 
                                                <th id="th{{$co}}">{{$co}}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th><div style="width:100px !important;">Operaciones</div></th>
                    						@foreach(explode(',', $configuracion_tabla->columnas_ordenadas) as $co) 
                                                <th id="th{{$co}}">{{$co}}</th>
                                            @endforeach
                                        </tr>
                                    </tfoot>
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
                    <div class="modal-header {{$empresa->background_forms_and_modals}}">
                        <h4 class="modal-title" id="titulomodal"></h4>
                    </div>
                    <form id="formparsley" action="#">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Código<b style="color:#F44336 !important;">*</b></label>
                                    <div class="row">
                                        <div class="col-md-12"> 
                                            <div class="form-line">
                                                <input type="text" class="form-control inputnext" name="codigo" id="codigo" required data-parsley-length="[1, 20]" onchange="buscarcodigoencatalogo();" onkeyup="tipoLetra(this);">
                                            </div>
                                        </div>  
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>Clave Producto<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreclaveproducto"></span></label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <span class="input-group-btn">
                                                <div id="buscarclavesproductos" class="btn bg-blue waves-effect" onclick="listarclavesproductos()">Seleccionar</div>
                                            </span>
                                        </div>  
                                        <div class="col-md-8"> 
                                            <div class="form-line">
                                                <input type="text" class="form-control inputnext" name="claveproducto" id="claveproducto" required data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">
                                                <input type="hidden" class="form-control" name="claveproductoanterior" id="claveproductoanterior" required readonly data-parsley-length="[1, 20]">
                                            </div>
                                        </div>    
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>Clave Unidad<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreclaveunidad"></span></label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <span class="input-group-btn">
                                                <div id="buscarclavesunidades" class="btn bg-blue waves-effect" onclick="listarclavesunidades()">Seleccionar</div>
                                            </span>
                                        </div>  
                                        <div class="col-md-8"> 
                                            <div class="form-line">
                                                <input type="text" class="form-control inputnext" name="claveunidad" id="claveunidad" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this);">
                                                <input type="hidden" class="form-control" name="claveunidadanterior" id="claveunidadanterior" required readonly data-parsley-length="[1, 5]">
                                            </div>
                                        </div>    
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <label>Producto<b style="color:#F44336 !important;">*</b></label>
                                    <input type="text" class="form-control inputnext" name="producto" id="producto" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);">
                                </div>
                                <div class="col-md-4">
                                    <label>Unidad<b style="color:#F44336 !important;">*</b></label>
                                    <input type="text" class="form-control inputnext" name="unidad" id="unidad" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this);">
                                </div>
                            </div>
                            <div class="col-md-12" id="tabsform">
                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" onclick="limpiar();limpiarmodales();" data-dismiss="modal">Salir</button>
                            <button type="button" class="btn btn-success btn-sm" id="btnGuardar">Guardar</button>
                            <button type="button" class="btn btn-success btn-sm" id="btnGuardarModificacion">Confirmar Cambios</button>
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
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
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
                    <button type="button" class="btn btn-success btn-sm" id="aceptar">Confirmar Baja</button>
                </div>
            </div>
        </div>
    </div> 
    <!-- Modal Movimientos-->
    <div class="modal fade" data-keyboard="false" id="modalmovimientos" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
                    <h5 class="modal-title" >
                        <div class="row">
                            <div class="col-xs-10 col-sm-10 col-md-11">
                                <label id="titulomodalmovimientos"></label>
                            </div>
                            <div class="col-xs-2 col-sm-2 col-md-1">
                                <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="row ">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <label >Codigo</label>
                                    <input type="text" name="codigokardex" id="codigokardex" class="form-control divorinputmodmd" autocomplete="off" onkeyup="tipoLetra(this);">
                                </div>
                                <div class="col-md-6">
                                    <label>Almacén</label>
                                    <select name="almacenkardex" class="form-control divorinputmodmd select2" id="almacenkardex" style="width:100% !important;"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive cabecerafija" style="height: 450px;overflow-y: scroll;padding: 0px 0px;">
                                <table class="table table-bordered table-striped table-hover" id="tablakardexproducto">
                                    <thead>
                                        <tr>
                                            <td class="{{$empresa->background_tables}}">#</td>
                                            <td class="{{$empresa->background_tables}}">Documento</td>
                                            <td class="{{$empresa->background_tables}}">Movimiento</td>
                                            <td class="{{$empresa->background_tables}}">Fecha</td>
                                            <td class="{{$empresa->background_tables}}">Almacen</td>
                                            <td class="{{$empresa->background_tables}}">Entradas</td>
                                            <td class="{{$empresa->background_tables}}">Salidas</td>
                                            <td class="{{$empresa->background_tables}}">Existencias</td>
                                            <td class="{{$empresa->background_tables}}">Costo</td>
                                            <td class="{{$empresa->background_tables}}">Status</td>
                                        </tr>
                                    </thead>
                                    <tbody id="filasmovimientos"></tbody>
                                </table> 
                            </div>
                        </div>
                    </div>    	
                </div>
                <div class="modal-footer">
                    <h5 id="infomovimientos"></h5>
                </div>
            </div>
        </div>
    </div> 
    <!-- Modal Códigos Barras-->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="ModalCodigosBarras" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div id="contenidomodalcodigosbarras">
                    <div class="modal-header {{$empresa->background_forms_and_modals}}">
                        <h4 class="modal-title" >
                            <div class="row">
                                <div class="col-xs-10 col-sm-10 col-md-11">
                                    <label >Generar Códigos de Barras</label>
                                </div>
                                <div class="col-xs-2 col-sm-2 col-md-1">
                                    <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">                        
                            <div class="col-md-12">
                                <ul class="nav nav-tabs tab-col-blue-grey" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#porcodigotab" data-toggle="tab">Seleccionar Códigos</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#porcatalogotab" data-toggle="tab">Todo el Catálogo Productos</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane fade in active" id="porcodigotab">
                                        <form action='{{$rutacrearpdfcodigosdebarrasarray}}' method="POST" data-parsley-validate="" target="_blank">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control" name="valorgenerarcodigobarras" id="valorgenerarcodigobarras" placeholder="Escribe el código y presiona enter" >
                                                    <input type="text" class="form-control" id="arraycodigosparacodigosdebarras" name="arraycodigosparacodigosdebarras" style="display:none;" required>
                                                </div>
                                                <div class="col-md-6" >
                                                    <button type="submit" class="btn btn-success btn-sm">Imprimir Códigos de Barras 1</button>
                                                </div>
                                            </div><br>
                                            <div class="row overflow-scroll" id="divcodigosbarras" style="height:400px;overflow-y: scroll;"></div>
                                        </form>
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade" id="porcatalogotab">
                                        <form action='{{$rutacrearpdfcodigosdebarrascatalogo}}' method="POST" data-parsley-validate="" target="_blank">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label >Tipo</label>
                                                    <select class="form-control select2" id="tipoprodcodigosbarras" name="tipoprodcodigosbarras" style="width:100%" required>
                                                        <option value="TODOS" selected>TODOS</option>    
                                                        <option value="REFACCION">REFACCION</option>
                                                        <option value="GASTOS">GASTOS</option>
                                                        <option value="TOT">TOT</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label >Status</label>
                                                    <select class="form-control select2" id="statuscodigosbarras" name="statuscodigosbarras" style="width:100%" required>
                                                        <option value="TODOS" selected>TODOS</option> 
                                                        <option value="ALTA">ALTA</option>
                                                        <option value="BAJA">BAJA</option>
                                                    </select>
                                                </div>
                                            </div><br>
                                            <div class="row">
                                                <div class="col-md-12 text-right" >                                            
                                                    <button type="submit" class="btn btn-success btn-sm">Imprimir Códigos de Barras</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" id="modal-footer">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Info Documentos-->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="ModalFormulario1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div id="formulario1">
                    <div class="modal-header {{$empresa->background_forms_and_modals}}">
                        <h4 class="modal-title" id="titulomodal1"></h4>
                    </div>
                    <div class="modal-body">
                        <form id="formxml1" action="#" enctype="multipart/form-data">
                        </form>
                        <form id="formparsley1" action="#">
                            <div class="col-md-12" id="tabsform1">
                                <!-- aqui van los formularios de alta o modificacion y se agregan automaticamente con jquery -->
                            </div>
                        </form> 
                    </div>
                    <div class="modal-footer" id="modal-footer">
                    </div>
                </div>
                <div id="contenidomodaltablas1">
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
        var campos_activados = '{{$configuracion_tabla->campos_activados}}';
        var campos_desactivados = '{{$configuracion_tabla->campos_desactivados}}';
        var columnas_ordenadas = '{{$configuracion_tabla->columnas_ordenadas}}';
        var primerordenamiento = '{{$configuracion_tabla->primerordenamiento}}';
        var formaprimerordenamiento = '{{$configuracion_tabla->formaprimerordenamiento}}';
        var segundoordenamiento = '{{$configuracion_tabla->segundoordenamiento}}';
        var formasegundoordenamiento= '{{$configuracion_tabla->formasegundoordenamiento}}';
        var tercerordenamiento = '{{$configuracion_tabla->tercerordenamiento}}';
        var formatercerordenamiento = '{{$configuracion_tabla->formatercerordenamiento}}';
        var campos_busquedas = '{{$configuracion_tabla->campos_busquedas}}';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';       
        var rol_usuario_logueado = '{{Auth::user()->role_id}}';
        var productos_obtener = '{!!URL::to('productos_obtener')!!}';
        var productos_buscar_codigo_en_tabla = '{!!URL::to('productos_buscar_codigo_en_tabla')!!}';
        var productos_obtener_claves_productos = '{!!URL::to('productos_obtener_claves_productos')!!}';
        var productos_obtener_clave_producto_por_clave = '{!!URL::to('productos_obtener_clave_producto_por_clave')!!}';
        var productos_obtener_claves_unidades = '{!!URL::to('productos_obtener_claves_unidades')!!}';
        var productos_obtener_clave_unidad_por_clave = '{!!URL::to('productos_obtener_clave_unidad_por_clave')!!}';
        var productos_obtener_marcas = '{!!URL::to('productos_obtener_marcas')!!}';
        var productos_obtener_marca_por_numero = '{!!URL::to('productos_obtener_marca_por_numero')!!}';
        var productos_obtener_lineas = '{!!URL::to('productos_obtener_lineas')!!}';
        var productos_obtener_linea_por_numero = '{!!URL::to('productos_obtener_linea_por_numero')!!}';
        var productos_obtener_monedas = '{!!URL::to('productos_obtener_monedas')!!}';
        var productos_obtener_moneda_por_clave = '{!!URL::to('productos_obtener_moneda_por_clave')!!}';
        var productos_obtener_utilidades = '{!!URL::to('productos_obtener_utilidades')!!}';
        var productos_obtener_existencias_almacenes = '{!!URL::to('productos_obtener_existencias_almacenes')!!}';
        var productos_obtener_clientes = '{!!URL::to('productos_obtener_clientes')!!}';
        var productos_obtener_datos_cliente_agregar_fila = '{!!URL::to('productos_obtener_datos_cliente_agregar_fila')!!}';
        var productos_obtener_productos_consumos = '{!!URL::to('productos_obtener_productos_consumos')!!}';
        var productos_obtener_datos_producto_agregar_fila = '{!!URL::to('productos_obtener_datos_producto_agregar_fila')!!}';
        var productos_obtener_tipos_prod = '{!!URL::to('productos_obtener_tipos_prod')!!}';
        var productos_obtener_kardex = '{!!URL::to('productos_obtener_kardex')!!}';
        var productos_guardar = '{!!URL::to('productos_guardar')!!}';
        var productos_alta_o_baja = '{!!URL::to('productos_alta_o_baja')!!}'; 
        var productos_obtener_producto = '{!!URL::to('productos_obtener_producto')!!}'; 
        var productos_validar_si_existe_codigo = '{!!URL::to('productos_validar_si_existe_codigo')!!}'; 
        var productos_generar_codigos_barras_catalogo = '{!!URL::to('productos_generar_codigos_barras_catalogo')!!}'; 
        var productos_generar_pdf_codigo_barras = '{!!URL::to('productos_generar_pdf_codigo_barras')!!}'; 
        var productos_guardar_modificacion = '{!!URL::to('productos_guardar_modificacion')!!}';
        //compras
        var compras_obtener_tipos_ordenes_compra = '{!!URL::to('compras_obtener_tipos_ordenes_compra')!!}';
        var compras_cargar_xml_alta = '{!!URL::to('compras_cargar_xml_alta')!!}';
        var compras_obtener_proveedores = '{!!URL::to('compras_obtener_proveedores')!!}';
        var compras_obtener_almacenes = '{!!URL::to('compras_obtener_almacenes')!!}';
        var compras_obtener_almacen_por_numero = '{!!URL::to('compras_obtener_almacen_por_numero')!!}';
        var compras_obtener_productos = '{!!URL::to('compras_obtener_productos')!!}';
        var compras_obtener_producto_por_codigo = '{!!URL::to('compras_obtener_producto_por_codigo')!!}';
        var compras_obtener_proveedor_por_numero = '{!!URL::to('compras_obtener_proveedor_por_numero')!!}';
        var compras_obtener_ordenes_compra = '{!!URL::to('compras_obtener_ordenes_compra')!!}';
        var compras_obtener_orden_compra = '{!!URL::to('compras_obtener_orden_compra')!!}'; 
        var compras_obtener_departamentos = '{!!URL::to('compras_obtener_departamentos')!!}'; 
        var compras_obtener_claves_productos = '{!!URL::to('compras_obtener_claves_productos')!!}'; 
        var compras_obtener_claves_unidades = '{!!URL::to('compras_obtener_claves_unidades')!!}'; 
        var compras_obtener_compra = '{!!URL::to('compras_obtener_compra')!!}';
        var compras_obtener_existencias_partida = '{!!URL::to('compras_obtener_existencias_partida')!!}';
        var compras_obtener_existencias_almacen = '{!!URL::to('compras_obtener_existencias_almacen')!!}';
        var compras_obtener_valor_modificacionpermitida = '{!!URL::to('compras_obtener_valor_modificacionpermitida')!!}';
        var compras_guardar_modificacion = '{!!URL::to('compras_guardar_modificacion')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/catalogos/productos/productos.js"></script>
    <!--<script src="scripts_inaasys/catalogos/productos/infodocumentos.js"></script>-->
@endsection