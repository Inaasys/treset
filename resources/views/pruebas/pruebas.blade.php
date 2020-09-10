@extends('plantilla_maestra')
@section('titulo')
  Pruebas
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
                            <div class="row clearfix">
                                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 table-responsive">
                                    <table>
                                        <tr>
                                            <td>
                                                <h5>&nbsp;&nbsp;&nbsp;&nbsp;Pruebas&nbsp;&nbsp;&nbsp;</h5>
                                            </td>
                        		        </tr>
                        	        </table>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">     
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                </div>
                            </div>
                        </div>
                        <div class="body">
                            <div class="row">
                                
                            <form action="{{ route('enviar_msj_whatsapp') }}" method="post">
                                @csrf
                                <div class="col-md-6">
                                    <label>Número Teléfono</label>
                                    <input type="text" class="form-control" id="numero" name="numero" placeholder="escribe el número teléfonico">
                                </div>
                                <div class="col-md-6">
                                    <label >Mensaje</label>
                                    <textarea class="form-control" id="mensaje" name="mensaje" rows="2" placeholder="escribe el mensaje"></textarea><br>
                                </div>
                                <div class="col-md-12">
                                    <input type="submit" class="btn btn-block btn-primary" name="submit" value="Enviar Mensaje">
                                </div>
                            </form>

                                {{--<div class="col-md-6">
                                    <label>fecha</label>
                                    <input type="date" class="form-control" id="fechadolar" name="fechadolar" onchange="obtener_valor_dolar_por_fecha_diario_oficial_federacion()">
                                </div>
                                <div class="col-md-6">
                                    <label >valor dolar</label>
                                    <input type="text" class="form-control" id="valordolardof" name="valordolardof">
                                </div>--}}
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
        /*urls y variables renderizadas con blade*/
        var mayusculas_sistema = '{{$mayusculas_sistema}}';
        var numerodecimales = '{{$numerodecimales}}';
        var numerocerosconfigurados = '{{$numerocerosconfigurados}}';
        var numerocerosconfiguradosinputnumberstep = '{{$numerocerosconfiguradosinputnumberstep}}';
        var compras_obtener = '{!!URL::to('compras_obtener')!!}';
        var periodohoy = '{{$periodohoy}}';
        var meshoy = '{{$meshoy}}';
        var obtener_valor_dolar_dof = '{!!URL::to('obtener_valor_dolar_dof')!!}';

    </script>
    <script>
        function obtener_valor_dolar_por_fecha_diario_oficial_federacion(){
            var fechadolar = $("#fechadolar").val();
            $.get(obtener_valor_dolar_dof, {fechadolar:fechadolar}, function(data){
                $("#valordolardof").val(data);
            });
        }
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/registros/compras/compras.js"></script>
@endsection