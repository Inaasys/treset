@extends('plantilla_maestra')
@section('titulo')
  Ordenes de Compra
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
                        <div class="header {{$empresa->background_forms_and_modals}}" >
                            <div class="row clearfix">
                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                    <h5>&nbsp;&nbsp;&nbsp;ORDENES DE COMPRA&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12 button-demo">
                                    <div class="table-responsive  navbar-right">
                                        <table>
                                            <tr>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta('')">
                                                        Altas Prod
                                                    </div>
                                                </td>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta('GASTOS')">
                                                        Altas Gastos
                                                    </div>
                                                </td>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta('TOT')">
                                                        Altas Tot
                                                    </div>
                                                </td>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="mostrarmodalgenerarpdf(0,1,'OrdenCompra')">
                                                        Generar Documento
                                                    </div>
                                                </td>
                                                <td >
                                                    <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" href="{{route('ordenes_compra_exportar_excel')}}" target="_blank">
                                                        Excel
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
                                <div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select class="select2 form-control" name="periodo" id="periodo" onchange="relistar()" style="width:100% !important;">
                                                @for ($i = $periodoinicial; $i < $periodohoy; $i++)
                                                    <option value="{{$i}}">{{$i}}</option>
                                                @endfor
                                                    <option value="{{$periodohoy}}" selected>{{$periodohoy}}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="body">
                            <div class="row" hidden>
                                <div class="col-md-12">
                                    <iframe  id="pdfiframe" name="pdfiframe" src="#"></iframe>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover display nowrap">
                                    <thead class="{{$empresa->background_tables}}">
                                        <tr>
                                            <th><div style="width:100px !important;">Operaciones</div></th>
                    						@foreach(explode(',', $configuracion_tabla->columnas_ordenadas) as $co)
                                                    <th id="th{{$co}}">{{$co}}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody ></tbody>
                                    <tfoot>
                                        <tr>
                                            <th><div style="width:100px !important;">Operaciones</div></th>
                    						@foreach(explode(',', $configuracion_tabla->columnas_ordenadas) as $co)
                                                <th id="th{{$co}}">{{$co}}</th>
                                            @endforeach
                                        </tr>
                                    </tfoot>
                                </table>
                                @if($mostrartotalesdecolumnasendocumentos == 'S')
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped table-hover display nowrap" style="font-size:10px;">
                                                <tr class="{{$empresa->background_forms_and_modals}}">
                                                    <th>Sum Importe: <b id="sumaimportefiltrado"></b></th>
                                                    <th>Sum Descuento: <b id="sumadescuentofiltrado"></b></th>
                                                    <th>Sum SubTotal: <b id="sumasubtotalfiltrado"></b></th>
                                                    <th>Sum Iva: <b id="sumaivafiltrado"></b></th>
                                                    <th>Sum Total: <b id="sumatotalfiltrado"></b></th>
                                                </tr>
                                            </table>
                                        </div>
                                @endif
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
                    <form id="formplantilla" action="#" enctype="multipart/form-data" hidden>
                        <div class="col-md-12">
                            <table class="col-md-12">
                                <tr>
                                    <td>
                                        <div class="col-md-6">
                                            <label>Selecciona el archivo excel</label>
                                            <input type="file" class="form-control" name="partidasexcel" id="partidasexcel" onchange="cargarpartidasexcel(this)" onclick="this.value=null;">
                                            <button type="button" class="btn btn-success btn-sm" id="btnenviarpartidasexcel" style="display:none">Enviar Excel</button>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </form>
                    <form id="formparsley" action="#">
                        <div class="modal-body">
                            <div class="col-md-12" id="tabsform">
                                <!-- aqui van los formularios de alta o modificacion y se agregan automaticamente con jquery -->
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
    <!-- Modal Alta/Codigo Productos-->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="ModalFormularioProducto" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div id="formularioproducto">
                    <div class="modal-header {{$empresa->background_forms_and_modals}}">
                        <h4 class="modal-title" >Alta Producto</h4>
                    </div>
                    <form id="formparsleyproducto" action="#">
                        <div class="modal-body">
                            <div class="col-md-12" id="tabsformproducto">

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" onclick="mostrarformularioorden();">Salir</button>
                            <button type="button" class="btn btn-success btn-sm" id="btnGuardarProducto">Guardar Producto</button>
                        </div>
                    </form>
                </div>
                <div id="contenidomodaltablasproducto">
                    <!-- aqui van las tablas de seleccion y se agregan automaticamente con jquery -->
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Autorizar Orden de Compra-->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="autorizarorden" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
                    <h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
                </div>
                <div class="modal-body">
                    <div id="divformautorizar">
                        <form id="formautorizar" action="#">
                            <h5 id="textomodalautorizar">Estas seguro de autorizar la orden de compra?</h5>
                            <input type="hidden" id="ordenautorizar" name="ordenautorizar">
                            <input type="hidden" id="ordenquitarautorizacion" name="ordenquitarautorizacion">
                        </form>
                    </div>
                    <div id="divmsjsurtimiento">
                        <h5 id="textomsjsurtimiento"></h5>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
                                <button type="button" class="btn btn-success btn-sm" id="btncontinuarautorizacion" onclick="continuarconautorizacion();">Contirnuar con Autorización</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="footermodalautorizacion">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
                    <button type="button" class="btn btn-success btn-sm" id="btnautorizar">Confirmar Autorización</button>
                    <button type="button" class="btn btn-success btn-sm" id="btnquitarautorizacion">Quitar Autorización</button>
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
                        <h5 id="textomodaldesactivar"> </h5>
                        <input type="hidden" id="ordendesactivar" name="ordendesactivar">
                        <div id="divmotivobaja">
                            <label>Motivo Baja</label>
                            <textarea class="form-control" name="motivobaja" id="motivobaja" rows=2 data-parsley-length="[1, 200]" onkeyup="tipoLetra(this)" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
                    <button type="button" class="btn btn-success btn-sm" id="btnbaja">Confirmar Baja</button>
                </div>
            </div>
        </div>
    </div>
    <!-- modal para crear documento en PDF-->
    @include('secciones.modalcreardocumento')
    <!-- fin modal para crear documento en PDF-->
    <!-- modal para enviar por email documento en PDF-->
    @include('secciones.modalenviardocumentoemail')
    <!-- fin modal para enviar por email documento en PDF-->
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
        var serieusuario = '{{$serieusuario}}';
        var meshoy = '{{$meshoy}}';
        var periodohoy = '{{$periodohoy}}';
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
        var nombreempresa = '{{$empresa->Nombre}}';
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';
        var agregarreferenciaenasuntocorreo = '{{$empresa->AgregarReferenciaOrdenCompraEnAsuntoCorreo}}';
        var urlgenerarplantilla = '{{$urlgenerarplantilla}}';
        var rol_usuario_logueado = '{{Auth::user()->role_id}}';
        var urlpdfsimpresionesrapidas = '{{asset("xml_descargados/")}}/';
        var ordenes_compra_obtener = '{!!URL::to('ordenes_compra_obtener')!!}';
        var ordenes_compra_descargar_plantilla = '{!!URL::to('ordenes_compra_descargar_plantilla')!!}';
        var ordenes_compra_cargar_partidas_excel = '{!!URL::to('ordenes_compra_cargar_partidas_excel')!!}';
        var ordenes_compra_obtener_series_documento = '{!!URL::to('ordenes_compra_obtener_series_documento')!!}';
        var ordenes_compra_obtener_ultimo_folio_serie_seleccionada = '{!!URL::to('ordenes_compra_obtener_ultimo_folio_serie_seleccionada')!!}';
        var ordenes_compra_obtener_ultimo_folio = '{!!URL::to('ordenes_compra_obtener_ultimo_folio')!!}';
        var ordenes_compra_obtener_fecha_actual_datetimelocal = '{!!URL::to('ordenes_compra_obtener_fecha_actual_datetimelocal')!!}';
        var ordenes_compra_obtener_tipos_ordenes_compra = '{!!URL::to('ordenes_compra_obtener_tipos_ordenes_compra')!!}';
        var ordenes_compra_obtener_proveedores = '{!!URL::to('ordenes_compra_obtener_proveedores')!!}';
        var ordenes_compra_obtener_almacenes = '{!!URL::to('ordenes_compra_obtener_almacenes')!!}';
        var ordenes_compra_obtener_ordenes_trabajo = '{!!URL::to('ordenes_compra_obtener_ordenes_trabajo')!!}';
        var ordenes_compra_obtener_orden_trabajo_por_folio = '{!!URL::to('ordenes_compra_obtener_orden_trabajo_por_folio')!!}';
        var ordenes_compra_obtener_productos = '{!!URL::to('ordenes_compra_obtener_productos')!!}';
        var ordenes_compra_obtener_producto_por_codigo = '{!!URL::to('ordenes_compra_obtener_producto_por_codigo')!!}';
        var ordenes_compra_obtener_proveedor_por_numero = '{!!URL::to('ordenes_compra_obtener_proveedor_por_numero')!!}';
        var ordenes_compra_obtener_almacen_por_numero = '{!!URL::to('ordenes_compra_obtener_almacen_por_numero')!!}';
        var ordenes_compra_guardar = '{!!URL::to('ordenes_compra_guardar')!!}';
        var ordenes_compra_verificar_autorizacion = '{!!URL::to('ordenes_compra_verificar_autorizacion')!!}';
        var ordenes_compra_autorizar = '{!!URL::to('ordenes_compra_autorizar')!!}';
        var ordenes_compra_verificar_quitar_autorizacion  = '{!!URL::to('ordenes_compra_verificar_quitar_autorizacion')!!}';
        var ordenes_compra_quitar_autorizacion  = '{!!URL::to('ordenes_compra_quitar_autorizacion')!!}';
        var ordenes_compra_verificar_uso_en_modulos = '{!!URL::to('ordenes_compra_verificar_uso_en_modulos')!!}';
        var ordenes_compra_alta_o_baja = '{!!URL::to('ordenes_compra_alta_o_baja')!!}';
        var ordenes_compra_obtener_orden_compra = '{!!URL::to('ordenes_compra_obtener_orden_compra')!!}';
        var ordenes_compra_guardar_modificacion = '{!!URL::to('ordenes_compra_guardar_modificacion')!!}';
        var ordenes_compra_obtener_datos_envio_email = '{!!URL::to('ordenes_compra_obtener_datos_envio_email')!!}';
        var ordenes_compra_enviar_pdfs_email = '{!!URL::to('ordenes_compra_enviar_pdfs_email')!!}';
        var ordenes_compra_generar_pdfs = '{!!URL::to('ordenes_compra_generar_pdfs')!!}';
        var ordenes_compra_buscar_folio_string_like = '{!!URL::to('ordenes_compra_buscar_folio_string_like')!!}';
        //alta PROD
        var ordenes_compra_obtener_claves_productos = '{!!URL::to('ordenes_compra_obtener_claves_productos')!!}';
        var ordenes_compra_obtener_claves_unidades = '{!!URL::to('ordenes_compra_obtener_claves_unidades')!!}';
        var ordenes_compra_obtener_marcas = '{!!URL::to('ordenes_compra_obtener_marcas')!!}';
        var ordenes_compra_obtener_lineas = '{!!URL::to('ordenes_compra_obtener_lineas')!!}';
        var ordenes_compra_guardar_producto = '{!!URL::to('ordenes_compra_guardar_producto')!!}';
        var productos_obtener_clave_producto_por_clave = '{!!URL::to('productos_obtener_clave_producto_por_clave')!!}';
        var productos_obtener_clave_unidad_por_clave = '{!!URL::to('productos_obtener_clave_unidad_por_clave')!!}';
        var productos_obtener_marca_por_numero = '{!!URL::to('productos_obtener_marca_por_numero')!!}';
        var productos_obtener_linea_por_numero = '{!!URL::to('productos_obtener_linea_por_numero')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="{{ asset('scripts_inaasys/registros/ordenescompra/ordenescompra.js') }}"></script>
@endsection



