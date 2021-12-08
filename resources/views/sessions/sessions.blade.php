@extends('plantilla_maestra')
@section('titulo')
  Sesiones Activas
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
                                    <h5>&nbsp;&nbsp;&nbsp;SESIONES ACTIVAS&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-10 col-md-10 col-sm-10 col-xs-12 button-demo">
                                        <div class="table-responsive navbar-right">
                                            <table>
                                                <tr>
                                                    <td >
                                                        <form id="formsesionesactivas" method="POST">
                                                            <div id="btnsesionesactivas" class="btn btn-warning btn-xs" >Eliminar sesiones activas</button>
                                                        </form>
                                                    </td>
                                                    <td >
                                                        <form action="formsinlogin" method="POST">
                                                            <div id="btnsesionessinlogin" class="btn btn-warning btn-xs" >Eliminar sesiones sin log-in</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                </div>
                            </div>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover display nowrap" style=" width:100% !important;">
                                    <thead class="{{$empresa->background_tables}}">
                                        <tr>
                                            <th scope="col">Operaciones</th>
                                            <th>Id Usuario</th>
                                            <th scope="col">Nombre Usuario</th>
                                            <th scope="col">Dispositivo</th>
                                            <th scope="col">Navegador</th>
                                            <th scope="col">Plataforma / Sistema</th>
                                            <th scope="col">IP</th>
                                            <th scope="col">Ultima Conexión</th>
                                            <th scope="col">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Operaciones</th>
                                            <th>Id Usuario</th>
                                            <th>Nombre Usuario</th>
                                            <th>Dispositivo</th>
                                            <th>Navegador</th>
                                            <th>Plataforma / Sistema</th>
                                            <th>IP</th>
                                            <th>Ultima Conexión</th>
                                            <th>Estado</th>
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
                        <input type="hidden" id="sesiondesactivar" name="sesiondesactivar">
                    </form>	
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
                    <button type="button" class="btn btn-success btn-sm" id="btnbaja">Confirmar Baja</button>
                </div>
            </div>
        </div>
    </div> 
</section>
@endsection
@section('additionals_js')
        <script>
            var mayusculas_sistema = '{{$mayusculas_sistema}}';
            var numerodecimales = '{{$numerodecimales}}';
            var numerocerosconfigurados = '{{$numerocerosconfigurados}}';
            var numerocerosconfiguradosinputnumberstep = '{{$numerocerosconfiguradosinputnumberstep}}';
            var background_navbar = '{{$empresa->background_navbar}}';
            var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
            var background_tables = '{{$empresa->background_tables}}';
            var sesiones_obtener = '{!!URL::to('sesiones_obtener')!!}';
            var eliminar_session = '{!!URL::to('eliminar_session')!!}';
            var sesiones_eliminar_sesiones_activas = '{!!URL::to('sesiones_eliminar_sesiones_activas')!!}';
            var sesiones_eliminar_sesiones_sin_login = '{!!URL::to('sesiones_eliminar_sesiones_sin_login')!!}';
        </script>
        @include('secciones.libreriasregistrosycatalogos')
        <script type="text/javascript">
            //listar sesiones
            //agregar inputs de busqueda por columna
            $('#tbllistado tfoot th').each( function () {
                var title = $(this).text();
                if(title != 'Operaciones'){
                    $(this).html( '<input type="text" placeholder="Buscar por '+title+'" style="width:125px !important;"/>' );
                }
            });
            tabla=$('#tbllistado').DataTable({
                "lengthMenu": [ 100, 250, 500, 1000 ],
                "pageLength": 1000,
                "sScrollX": "110%",
                "sScrollY": "350px",
                processing: true,
                'language': {
                    'loadingRecords': '&nbsp;',
                    'processing': '<div class="spinner"></div>'
                },
                serverSide: true,
                ajax: sesiones_obtener,
                columns: [
                    { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
                    { data: 'usuario', name: 'usuario', orderable: false, searchable: true },
                    { data: 'user_name', name: 'user_name', orderable: false, searchable: true },
                    { data: 'device', name: 'device', orderable: false, searchable: true },
                    { data: 'browser', name: 'browser   ', orderable: false, searchable: true },
                    { data: 'platform', name: 'platform', orderable: false, searchable: true },
                    { data: 'ip_dispositivo', name: 'ip_dispositivo', orderable: false, searchable: true },
                    { data: 'ultima_conexion', name: 'ultima_conexion', orderable: false, searchable: true },
                    { data: 'status', name: 'status', orderable: false, searchable: true },
                ],
                initComplete: function () {
                // Aplicar busquedas por columna
                this.api().columns().every( function () {
                    var that = this;
                    $('input',this.footer()).on( 'change', function(){
                        if(that.search() !== this.value){
                            that.search(this.value).draw();
                        }
                    });
                });
                //Aplicar busqueda general
                var $buscar = $('div.dataTables_filter input');
                $buscar.unbind();
                $buscar.bind('keyup change', function(e) {
                    if(e.keyCode == 13 || this.value == "") {
                        $('#tbllistado').DataTable().search( this.value ).draw();
                    }
                });
                }
            });
            //desactivar sesion por id
            function desactivar(sesiondesactivar){
                $("#sesiondesactivar").val(sesiondesactivar);
                $("#btnbaja").show();
                $("#textomodaldesactivar").html('Estas seguro de dar de baja la sesion? '+sesiondesactivar);
                $('#estatusregistro').modal('show');
            }
            $("#btnbaja").on('click', function(e){
                e.preventDefault();
                var formData = new FormData($("#formdesactivar")[0]);
                var form = $("#formdesactivar");
                if (form.parsley().isValid()){
                    $('.page-loader-wrapper').css('display', 'block');
                    $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url:eliminar_session,
                    type: "post",
                    dataType: "html",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success:function(data){
                        $('#estatusregistro').modal('hide');
                        msj_datosguardadoscorrectamente();
                        $('.page-loader-wrapper').css('display', 'none');
                    },
                    error:function(data){
                        msj_errorajax();
                        $('#estatusregistro').modal('hide');
                        $('.page-loader-wrapper').css('display', 'none');
                    }
                    })
                }else{
                    form.parsley().validate();
                }
            });
            //eliminar sesiones activas
            $("#btnsesionesactivas").on('click', function(e){
                e.preventDefault();
                var formData = new FormData($("#formsesionesactivas")[0]);
                $('.page-loader-wrapper').css('display', 'block');
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url:sesiones_eliminar_sesiones_activas,
                    type: "post",
                    dataType: "html",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success:function(data){
                        $('#estatusregistro').modal('hide');
                        msj_datosguardadoscorrectamente();
                        $('.page-loader-wrapper').css('display', 'none');
                    },
                    error:function(data){
                        msj_errorajax();
                        $('#estatusregistro').modal('hide');
                        $('.page-loader-wrapper').css('display', 'none');
                    }
                })
            });
            
            //eliminar sesiones sin log-in
            $("#btnsesionessinlogin").on('click', function(e){
                e.preventDefault();
                var formData = new FormData($("#formsinlogin")[0]);
                $('.page-loader-wrapper').css('display', 'block');
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url:sesiones_eliminar_sesiones_sin_login,
                    type: "post",
                    dataType: "html",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success:function(data){
                        $('#estatusregistro').modal('hide');
                        msj_datosguardadoscorrectamente();
                        $('.page-loader-wrapper').css('display', 'none');
                    },
                    error:function(data){
                        msj_errorajax();
                        $('#estatusregistro').modal('hide');
                        $('.page-loader-wrapper').css('display', 'none');
                    }
                })
            });
        </script>

@endsection

