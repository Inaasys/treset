@extends('plantilla_maestra')
@section('titulo')
  Firmar Documentos
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
                                    <h5>&nbsp;&nbsp;&nbsp;FIRMAR DOCUMENTOS&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12 button-demo">
                                    <div class="table-responsive  navbar-right">
                                        <table>
                                            <tr>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta('')">
                                                        Firmar Documento
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
                        <input type="hidden" id="firmadesactivar" name="firmadesactivar">
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
        var firmardocumentos_obtener = '{!!URL::to('firmardocumentos_obtener')!!}';
        var firmardocumentos_obtener_tipos_documentos = '{!!URL::to('firmardocumentos_obtener_tipos_documentos')!!}';
        var firmardocumentos_obtener_folios_documento = '{!!URL::to('firmardocumentos_obtener_folios_documento')!!}';
        var ordenes_compra_obtener_fecha_actual_datetimelocal = '{!!URL::to('ordenes_compra_obtener_fecha_actual_datetimelocal')!!}';
        var firmardocumentos_obtener_documentos_a_firmar = '{!!URL::to('firmardocumentos_obtener_documentos_a_firmar')!!}';
        var firmardocumentosoc_guardar = '{!!URL::to('firmardocumentosoc_guardar')!!}';
        var firmardocumentoscom_guardar = '{!!URL::to('firmardocumentoscom_guardar')!!}';
        var firmardocumentosconrec_guardar = '{!!URL::to('firmardocumentosconrec_guardar')!!}';
        var firmardocumentosrem_guardar = '{!!URL::to('firmardocumentosrem_guardar')!!}';
        var firmardocumentostras_guardar = '{!!URL::to('firmardocumentostras_guardar')!!}';
        var firmardocumentosnp_guardar = '{!!URL::to('firmardocumentosnp_guardar')!!}';
        var firmardocumentosah_guardar = '{!!URL::to('firmardocumentosah_guardar')!!}';
        var firmardocumentosaji_guardar = '{!!URL::to('firmardocumentosaji_guardar')!!}';
        var firmardocumentoscp_guardar = '{!!URL::to('firmardocumentoscp_guardar')!!}';
        var firmardocumentoscs_guardar = '{!!URL::to('firmardocumentoscs_guardar')!!}';
        var firmardocumentospro_guardar = '{!!URL::to('firmardocumentospro_guardar')!!}';
        var firmardocumentosreq_guardar = '{!!URL::to('firmardocumentosreq_guardar')!!}';
        var firmardocumentos_verificar_uso_en_modulos = '{!!URL::to('firmardocumentos_verificar_uso_en_modulos')!!}'; 
        var firmardocumentos_bajas = '{!!URL::to('firmardocumentos_bajas')!!}'; 
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/firmardocumentos/firmardocumentos.js"></script>
@endsection



