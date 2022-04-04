@extends('plantilla_maestra')
@section('titulo')
  Facturas
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
                                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                        <h5>&nbsp;&nbsp;&nbsp;FACTURAS&nbsp;&nbsp;&nbsp;</h5>
                                    </div>
                                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12 button-demo">
                                        <div class="table-responsive navbar-right">
                                            <table>
                                                <tr>
                                                    <td >
                                                        <div class="btn bg-blue btn-xs waves-effect" onclick="alta('PROD')">
                                                            Altas
                                                        </div>
                                                    </td>
                                                    <td >
                                                        <div class="btn bg-blue btn-xs waves-effect" onclick="mostrarmodalgenerarpdf(0,1)">
                                                            Generar Documento
                                                        </div>
                                                    </td>
                                                    <td >
                                                        <a class="btn bg-blue btn-xs waves-effect" id="btnGenerarFormatoExcel" href="{{route('facturas_exportar_excel')}}" target="_blank">
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
                                                <select class="form-control select2" name="periodo" id="periodo" onchange="relistar()" style="width:100% !important;">
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
                                    @if($mostrartotalesdecolumnasendocumentos == 'S')
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped table-hover display nowrap" style="font-size:10px;">
                                                <tr class="{{$empresa->background_forms_and_modals}}">
                                                    <th>Sum Importe: <b id="sumaimportefiltrado"></b></th>
                                                    <th>Sum Descuento: <b id="sumadescuentofiltrado"></b></th>
                                                    <th>Sum SubTotal: <b id="sumasubtotalfiltrado"></b></th>
                                                    <th>Sum Iva: <b id="sumaivafiltrado"></b></th>
                                                    <th>Sum Total: <b id="sumatotalfiltrado"></b></th>
                                                    <th>Sum Abonos: <b id="sumaabonosfiltrado"></b></th>
                                                    <th>Sum Descuentos: <b id="sumadescuentosfiltrado"></b></th>
                                                    <th>Sum Saldo: <b id="sumasaldofiltrado"></b></th>
                                                    <th>Sum Costo: <b id="sumacostofiltrado"></b></th>
                                                    <th>Sum Comisión: <b id="sumacomisionfiltrado"></b></th>
                                                    <th>Sum Utilidad: <b id="sumautilidadfiltrado"></b></th>
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
                    <div class="modal-body">
                        <form id="formxml" action="#" enctype="multipart/form-data">
                        </form>
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
                            <div class="col-md-12" id="tabsform">
                                <!-- aqui van los formularios de alta o modificacion y se agregan automaticamente con jquery -->
                            </div>
                        </form> 
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-danger btn-sm" onclick="limpiar();limpiarmodales();" data-dismiss="modal">Salir</button>
                            <button type="button" class="btn btn-success btn-sm" id="btnGuardar">Guardar</button>
                            <button type="button" class="btn btn-success btn-sm" id="btnGuardarModificacion">Confirmar Cambios</button>
                        </div> 
                    </div>
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
                        <h5 id="textomodaldesactivar"> </h5>
                        <input type="hidden" class="form-control" id="facturadesactivar" name="facturadesactivar" readonly>
                        <div id="divmotivobaja">
                            <label>Motivo Baja</label>
                            <textarea class="form-control" name="motivobaja" id="motivobaja" rows=2 onkeyup="tipoLetra(this)" required data-parsley-length="[1, 200]"></textarea>
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
    <!-- Modal Timbrado-->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="modaltimbrado" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
                    <h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
                </div>
                <div class="modal-body">
                    <form id="formtimbrado" action="#">
                        <h5 id="textomodaltimbrado"> </h5>
                        <input type="hidden" class="form-control" id="facturatimbrado" name="facturatimbrado" readonly>
                    </form>	
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
                    <button type="button" class="btn btn-success btn-sm" id="btntimbrarfactura">Timbrar Factura</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Baja Timbre-->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalbajatimbre" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
                    <h5 class="modal-title" >Aviso</h5>
                </div>
                <div class="modal-body">
                    <form id="formbajatimbre" action="#">
                        <h5 id="textomodalbajatimbre"> </h5>
                        <input type="hidden" class="form-control" id="iddocumentofacturapi" name="iddocumentofacturapi" readonly>
                        <input type="hidden" class="form-control" id="facturabajatimbre" name="facturabajatimbre" readonly>
                        <div class="row">
                            <div class="col-md-12" id="divmotivobajatimbre">
                                <label for="">Motivo:</label>
                                <select name="motivobajatimbre" id="motivobajatimbre" class="form-control select2" style="width:100%;" required></select>
                            </div>
                        </div>
                    </form>	
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
                    <button type="button" class="btn btn-success btn-sm" id="btnbajatimbre">Confirmar Baja Timbre</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Numero Pedido-->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalremisionesporpedido" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
                    <h5 class="modal-title" >Escribe el número de pedido</h5>
                </div>
                <div class="modal-body">
                    <form id="formremisionesporpedido" action="#">
                        <input type="text" class="form-control" id="numeropedidoremisiones" name="numeropedidoremisiones" required>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" onclick="cerrarmodalnumeropedido();">Regresar</button>
                    <button type="button" class="btn btn-success btn-sm" onclick="seleccionarremisionesporpedido();">Seleccionar remisiones con pedido</button>
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
                                    <label >Factura</label>
                                    <input type="text" name="facturakardex" id="facturakardex" class="form-control divorinputmodmd" autocomplete="off" onkeyup="tipoLetra(this);">
                                </div>
                                <div class="col-md-6">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive cabecerafija" style="height: 450px;overflow-y: scroll;padding: 0px 0px;">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <td class="{{$empresa->background_tables}}">#</td>
                                            <td class="{{$empresa->background_tables}}">Movimiento</td>
                                            <td class="{{$empresa->background_tables}}">Número</td>
                                            <td class="{{$empresa->background_tables}}">Fecha</td>
                                            <td class="{{$empresa->background_tables}}">Abono</td>
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
                </div>
            </div>
        </div>
    </div> 
    <!-- Modal modificar datos generales-->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalmodificardatosgeneralesdocumento" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
                    <h5 class="modal-title" id="exampleModalLabel">Facturas Modificar Datos Generales</h5>
                </div>
                <div class="modal-body">
                    <form id="formamodificardatosgenerales" action="#">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 id="titulomodificardatosgenerales"></h4>
                            </div>
                            <div class="col-md-2">
                                    <label >Factura</label>
                                    <input type="text" class="form-control inputnextdatosgenerales" id="facturadatosgenerales" name="facturadatosgenerales" readonly>
                            </div>
                            <div class="col-md-2">
                                    <label>Pedido</label>
                                    <input type="text" class="form-control inputnextdatosgenerales" id="pedidodatosgenerales" name="pedidodatosgenerales" onkeyup="tipoLetra(this)">
                            </div>
                        </div>                    
                    </form>	
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
                    <button type="button" class="btn btn-success btn-sm" id="btnguardardatosgenerales">Confirmar Cambios</button>
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
        var esquema = '{{$esquema}}';
        var depto = '{{$depto}}';
        var periodohoy = '{{$periodohoy}}';
        var meshoy = '{{$meshoy}}';
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
        var rfcempresa = '{{$empresa->Rfc}}';
        var nombreempresa = '{{$empresa->Nombre}}';
        var lugarexpedicion = '{{$lugarexpedicion}}';
        var claveregimenfiscal = '{{$claveregimenfiscal}}';
        var regimenfiscal = '{{$regimenfiscal}}';
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';
        var urlgenerarplantilla = '{{$urlgenerarplantilla}}';       
        var rol_usuario_logueado = '{{Auth::user()->role_id}}';
        var urlpdfsimpresionesrapidas = '{{asset("xml_descargados/")}}/';
        var urliconossistema = '{{asset("images/")}}/';
        var pedirobligatoriamenteobservacionenfactura = '{{$empresa->PedirObligatoriamenteObservacionEnFactura}}';
        var validarutilidadnegativa = '{{$validarutilidadnegativa}}';
        var facturas_obtener = '{!!URL::to('facturas_obtener')!!}';
        var facturas_descargar_plantilla = '{!!URL::to('facturas_descargar_plantilla')!!}';
        var facturas_cargar_partidas_excel = '{!!URL::to('facturas_cargar_partidas_excel')!!}';
        var facturas_obtener_ultimo_folio = '{!!URL::to('facturas_obtener_ultimo_folio')!!}';
        var ordenes_compra_obtener_fecha_actual_datetimelocal = '{!!URL::to('ordenes_compra_obtener_fecha_actual_datetimelocal')!!}';
        var facturas_obtener_tipos = '{!!URL::to('facturas_obtener_tipos')!!}';
        var facturas_obtener_tipos_unidades = '{!!URL::to('facturas_obtener_tipos_unidades')!!}';
        var facturas_obtener_clientes = '{!!URL::to('facturas_obtener_clientes')!!}';
        var facturas_obtener_cliente_por_numero = '{!!URL::to('facturas_obtener_cliente_por_numero')!!}';
        var facturas_obtener_agentes = '{!!URL::to('facturas_obtener_agentes')!!}';
        var facturas_obtener_agente_por_numero = '{!!URL::to('facturas_obtener_agente_por_numero')!!}';
        var facturas_obtener_codigos_postales = '{!!URL::to('facturas_obtener_codigos_postales')!!}';
        var facturas_obtener_lugar_expedicion_por_clave = '{!!URL::to('facturas_obtener_lugar_expedicion_por_clave')!!}';
        var facturas_obtener_regimenes_fiscales = '{!!URL::to('facturas_obtener_regimenes_fiscales')!!}';
        var facturas_obtener_regimen_fiscal_por_clave = '{!!URL::to('facturas_obtener_regimen_fiscal_por_clave')!!}';
        var facturas_obtener_tipos_relacion = '{!!URL::to('facturas_obtener_tipos_relacion')!!}';
        var facturas_obtener_tipo_relacion_por_clave = '{!!URL::to('facturas_obtener_tipo_relacion_por_clave')!!}';
        var facturas_obtener_formas_pago = '{!!URL::to('facturas_obtener_formas_pago')!!}';
        var facturas_obtener_forma_pago_por_clave = '{!!URL::to('facturas_obtener_forma_pago_por_clave')!!}';
        var facturas_obtener_metodos_pago = '{!!URL::to('facturas_obtener_metodos_pago')!!}';
        var facturas_obtener_metodo_pago_por_clave = '{!!URL::to('facturas_obtener_metodo_pago_por_clave')!!}';
        var facturas_obtener_usos_cfdi = '{!!URL::to('facturas_obtener_usos_cfdi')!!}';
        var facturas_obtener_uso_cfdi_por_clave = '{!!URL::to('facturas_obtener_uso_cfdi_por_clave')!!}';
        var facturas_obtener_residencias_fiscales = '{!!URL::to('facturas_obtener_residencias_fiscales')!!}';
        var facturas_obtener_residencia_fiscal_por_clave = '{!!URL::to('facturas_obtener_residencia_fiscal_por_clave')!!}';
        var facturas_obtener_folios_fiscales = '{!!URL::to('facturas_obtener_folios_fiscales')!!}';
        var facturas_obtener_ultimo_folio_serie_seleccionada  = '{!!URL::to('facturas_obtener_ultimo_folio_serie_seleccionada')!!}';
        var facturas_obtener_serie_interna  = '{!!URL::to('facturas_obtener_serie_interna')!!}';
        var facturas_obtener_datos_agente  = '{!!URL::to('facturas_obtener_datos_agente')!!}';
        var facturas_obtener_remisiones = '{!!URL::to('facturas_obtener_remisiones')!!}';
        var facturas_obtener_remisiones_por_pedido  = '{!!URL::to('facturas_obtener_remisiones_por_pedido')!!}';
        var facturas_obtener_remisiones_pedido  = '{!!URL::to('facturas_obtener_remisiones_pedido')!!}'; 
        var facturas_obtener_total_a_facturar  = '{!!URL::to('facturas_obtener_total_a_facturar')!!}'; 
        var facturas_obtener_remision = '{!!URL::to('facturas_obtener_remision')!!}';
        var facturas_obtener_ordenes = '{!!URL::to('facturas_obtener_ordenes')!!}';
        var facturas_obtener_orden = '{!!URL::to('facturas_obtener_orden')!!}';
        var facturas_obtener_productos = '{!!URL::to('facturas_obtener_productos')!!}';
        var facturas_obtener_producto_por_codigo = '{!!URL::to('facturas_obtener_producto_por_codigo')!!}';
        var facturas_obtener_productos_gastos = '{!!URL::to('facturas_obtener_productos_gastos')!!}';
        var facturas_obtener_producto_gasto_por_codigo = '{!!URL::to('facturas_obtener_producto_gasto_por_codigo')!!}';
        var facturas_obtener_claves_productos = '{!!URL::to('facturas_obtener_claves_productos')!!}';
        var facturas_obtener_claves_unidades = '{!!URL::to('facturas_obtener_claves_unidades')!!}';
        var facturas_obtener_facturas_relacionadas = '{!!URL::to('facturas_obtener_facturas_relacionadas')!!}';
        var facturas_cargar_xml_uuid_relacionado = '{!!URL::to('facturas_cargar_xml_uuid_relacionado')!!}';
        var facturas_obtener_nuevo_saldo_cliente = '{!!URL::to('facturas_obtener_nuevo_saldo_cliente')!!}';
        var facturas_guardar = '{!!URL::to('facturas_guardar')!!}';
        var facturas_obtener_factura = '{!!URL::to('facturas_obtener_factura')!!}';
        var facturas_guardar_modificacion = '{!!URL::to('facturas_guardar_modificacion')!!}';
        var facturas_obtener_datos_generales = '{!!URL::to('facturas_obtener_datos_generales')!!}';
        var facturas_guardar_modificacion_datos_generales = '{!!URL::to('facturas_guardar_modificacion_datos_generales')!!}';
        var facturas_obtener_kardex = '{!!URL::to('facturas_obtener_kardex')!!}';
        var facturas_verificar_si_continua_baja = '{!!URL::to('facturas_verificar_si_continua_baja')!!}';
        var facturas_alta_o_baja = '{!!URL::to('facturas_alta_o_baja')!!}'; 
        var facturas_obtener_datos_envio_email = '{!!URL::to('facturas_obtener_datos_envio_email')!!}';
        var facturas_enviar_pdfs_email = '{!!URL::to('facturas_enviar_pdfs_email')!!}';
        var facturas_enviar_pdfs_clientes_email = '{!!URL::to('facturas_enviar_pdfs_clientes_email')!!}';
        var facturas_generar_pdfs = '{!!URL::to('facturas_generar_pdfs')!!}';
        var facturas_buscar_folio_string_like = '{!!URL::to('facturas_buscar_folio_string_like')!!}'; 
        var facturas_verificar_si_continua_timbrado = '{!!URL::to('facturas_verificar_si_continua_timbrado')!!}'; 
        var facturas_timbrar_factura = '{!!URL::to('facturas_timbrar_factura')!!}'; 
        var facturas_verificar_si_continua_baja_timbre = '{!!URL::to('facturas_verificar_si_continua_baja_timbre')!!}'; 
        var facturas_baja_timbre = '{!!URL::to('facturas_baja_timbre')!!}'; 
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/facturas/facturas.js"></script>
    
@endsection