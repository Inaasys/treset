@extends('plantilla_maestra')
@section('titulo')
  Comparativa OT
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
                        <div class="header {{$empresa->background_forms_and_modals}} table-responsive button-demo">
                        	<table>
                        		<tr>
                        			<td >
                        				<h5>&nbsp;&nbsp;&nbsp;Comparativa OT&nbsp;&nbsp;&nbsp;</h5>
                        			</td>
                        		</tr>
                        	</table>
                        </div>
                        <div class="body">
                            <form action="{{ route('reporte_ordenes_de_trabajo_comparativa') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-3">
                                        <label>Seleccionar listado de OT (EXCEL) <span class="label label-danger" id="textonombreclientefacturara"></span></label>
                                        <table class="col-md-12">
                                            <tr>
                                                <td>
                                                    <div class="bg-blue waves-effect"><button class="btn btn-info">Generar</button></div>
                                                </td>
                                                <td>
                                                    <div class="form-line">
                                                        <input type="file" class="form-control inputnextdet" name="listado" id="numeroclientefacturara">
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table id="tbllistado" class="tbllistado table table-bordered table-striped table-hover display nowrap" style="width:100% !important;">
                                    <thead class="customercolor">
                                        <tr id="cabecerastablareporte">
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
            </div>
            <div id="contenidomodaltablas">
                <!-- aqui van las tablas de seleccion y se agregan automaticamente con jquery -->
            </div>
        </div>
    </div>
</div>
@endsection
@section('additionals_js')
    <script>
        $(document).ready(function(){
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    @php
                        echo "alert('".$error."')";
                    @endphp
                @endforeach
            @endif
        })
    </script>
@endsection
