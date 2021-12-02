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
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                        <th scope="col">Usuario</th>
                                        <th scope="col">Dispositivo</th>
                                        <th scope="col">IP</th>
                                        <th scope="col">Ultima Conexión</th>
                                        <th scope="col">Eliminar Sesion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sessions as $session)
                                        <tr>
                                            <td>{{ $session->usuario }}</td>    
                                            <td>{{ $session->dispositivo }}</td>
                                            <td>{{ $session->ip_dispositivo }}</td>
                                            <td>{{ \Carbon\Carbon::createFromTimeStamp($session->ultima_conexion)->diffForhumans() }}</td>
                                            <td class="text-center">
                                                <button type="button" name="button" class="btn btn-danger delete-session" data-id="{{ $session->id_session }}">X</button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Basic Examples -->
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
            var eliminar_session = '{!!URL::to('eliminar_session')!!}';
        </script>
        <script type="text/javascript">
            $(".delete-session").click(function(){
                var id = $(this).data("id");
                var token = $("meta[name='csrf-token']").attr("content");
                $.ajax({
                    url: eliminar_session,
                    type: 'POST',
                    data: {
                        "id": id,
                        "_token": token,
                    },
                    success: function (){
                        location.reload();
                    }
                });
            });
        </script>

    @include('secciones.libreriasregistrosycatalogos')
@endsection

