@extends('plantilla_maestra')
@section('titulo')
  Inicio
@endsection
@section('additionals_css')

@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="block-header">
            <h2>
                INICIO - BIENVENIDO
            </h2>
            @if(\Session::has('success'))
                <div class="alert bg-green alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    Contraseña Cambiada Correctamente
                </div>
            @endif
            @if(Auth::user()->first_login == 0)     
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card" id="listadoregistros">
                        <div class="header bg-red">
                        	<table>
                        		<tr>
                        			<td>
                        				<h5>&nbsp;&nbsp;&nbsp;&nbsp;Por seguridad cambia la contraseña que te fue asignada&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                        			<td >
                            			
                        			</td>
                        		</tr>
                        	</table>
                        </div>
                        <div class="body">
                            <form method="POST" action="{{ route('cambiar_contrasena') }}" data-parsley-validate="">
                            @csrf
                                <div class="row">
                                    <div class="col-md-6" >
                                        <label>Nueva Contraseña <b style="color:#F44336 !important;">*</b></label>
                                        <input type="text" class="form-control" name="email" id="email" value="{{Auth::user()->email}}" autocomplete="email" required data-parsley-type="email" style="display: none;">
                                        <input type="password" class="form-control" name="pass" id="pass" required autocomplete="new-password" data-parsley-regexsafepassword="/^(?=.{8,}$)(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$/">
                                    </div>
                                    <div class="col-md-6" >
                                        <label>Confirmar Nueva Contraseña <b style="color:#F44336 !important;">*</b></label>
                                        <input type="password" class="form-control" name="confirmarpass" id="confirmarpass"  required autocomplete="new-password" data-parsley-equalto="#pass">
                                    </div>
                                </div><br>
                                <div class="row">    
                                    <div class="col-md-2 col-md-offset-10">
                                        <button type="submit" class="btn bg-green btn-block waves-effect">Guardar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
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
    </script>
    <script src="js/toastr/toastr.min.js"></script>
    <!-- scripit init-->
    <script src="js/toastr/toastr.init.js"></script>
    <script src="js/parsley/parsley.min.js"></script>
    <!-- Cambiar idioma de parsley -->
    <script src="js/parsley/i18n/es.js"></script>  
@endsection