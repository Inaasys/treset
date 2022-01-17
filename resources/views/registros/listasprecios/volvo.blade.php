@extends('plantilla_maestra')
@section('titulo')
  Lista Precios Volvo
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
                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                    <h5>&nbsp;&nbsp;&nbsp;LISTA PRECIOS VOLVO&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12 button-demo">
                                    <div class="table-responsive navbar-right">
                                        <table>
                                            <tr>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta()">
                                                        Actualizar Lista
                                                    </div>
                                                </td>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="actualizartipocambio()">
                                                        Actualizar Tipo Cambio
                                                    </div>
                                                </td>
                                                <td >
                                                    <a class="btn bg-blue btn-xs waves-effect" href="{{route('lista_precios_volvo_exportar_excel')}}" target="_blank">
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

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" onclick="limpiar();limpiarmodales();" data-dismiss="modal">Salir</button>
                        </div>
                    </form> 
                </div>
                <div id="contenidomodaltablas">
                    <!-- aqui van las tablas de seleccion y se agregan automaticamente con jquery -->
                </div>     
            </div>
        </div>
    </div>
    <!-- Modal Actualizar Tipo Cambio-->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalactualizartipocambio" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
                    <h5 class="modal-title" id="exampleModalLabel">Actualización tipo de cambio volvo</h5>
                </div>
                <div class="modal-body">
                    <form id="formactualizartipocambio" action="#">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Valor tipo cambio volvo: {{$ultimo_valor_tipo_cambio_volvo}}</h4>
                            </div>
                            <div class="col-md-6">
                                <h4>Ultima actualización: {{$ultima_fecha_actualización_tipo_cambio_volvo}}</h4>
                            </div>
                            <div class="col-md-6">
                                <label for="">Actualizar tipo cambio</label>
                                <input type="number" step="0.{{$numerocerosconfiguradosinputnumberstep}}" class="form-control" name="valortipocambio" id="valortipocambio" value="0.{{$numerocerosconfigurados}}" data-parsley-min="0.{{$numerocerosconfiguradosinputnumberstep}}" onchange="formatocorrectoinputcantidades(this);">
                            </div>
                            <div class="col-md-6">
                                <label for="">Obtener valor dolar hoy Diario Oficial de la Federación</label>
                                <div class="btn btn-block btn-success" onclick="obtenervalordolarhoydof();">Obtener ultimo valor dolar Diario Oficial de la Federación</div>
                            </div>
                        </div>
                    </form>	
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
                    <button type="button" class="btn btn-success btn-sm" id="btnguardartipodecambio">Actualizar</button>
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
        var tipodeutilidad = '{{$tipodeutilidad}}';
        var urlgenerarplantilla = '{{$urlgenerarplantilla}}';
        var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
        var lista_precios_volvo_obtener = '{!!URL::to('lista_precios_volvo_obtener')!!}';
        var lista_precios_volvo_obtener_valor_dolar_hoy_dof = '{!!URL::to('lista_precios_volvo_obtener_valor_dolar_hoy_dof')!!}';
        var lista_precios_volvo_guardar_valor_tipo_cambio = '{!!URL::to('lista_precios_volvo_guardar_valor_tipo_cambio')!!}';
        var lista_precios_volvo_actualizar_lista_precios_vs_excel = '{!!URL::to('lista_precios_volvo_actualizar_lista_precios_vs_excel')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/listasprecios/volvo.js"></script>
@endsection