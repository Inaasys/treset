@extends('plantilla_maestra')
@section('titulo')
  Folios Comprobantes Pagos
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
                                    <h5>&nbsp;&nbsp;&nbsp;FOLIOS COMPROBANTES PAGOS&nbsp;&nbsp;&nbsp;</h5>
                                </div>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12 button-demo">
                                    <div class="table-responsive navbar-right">
                                        <table>
                                            <tr>
                                                <td >
                                                    <div class="btn bg-blue btn-xs waves-effect" onclick="alta()">
                                                        Altas
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
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Número</th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Serie</th>
                    						<th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Esquema</th>
                                            <th>FolioInicial</th>
                                            <th>Titulo</th>
                                            <th>ValidoDesde</th>
                                            <th>ValidoHasta</th>
                                            <th>Empresa</th>
                                            <th>Predeterminar</th>
                                            <th class="customercolortheadth" data-toggle="tooltip" data-placement="top" title data-original-title="Búsqueda activada">Status</th>
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
                <div class="modal-header {{$empresa->background_forms_and_modals}}">
                    <h4 class="modal-title" id="titulomodal"></h4>
                </div>
                <form id="formparsley" action="#">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Número</label>
                                <input type="text" class="form-control" name="numero" id="numero" required readonly>
                            </div>
                            <div class="col-md-3">
                                <label>Serie</label>
                                <input type="text" class="form-control" name="serie" id="serie" value="F" required data-parsley-length="[1, 10]" onkeyup="tipoLetra(this);">
                            </div>
                            <div class="col-md-3">
                                <label>Esquema</label>
                                <select name="esquema" id="esquema" class="form-control select2" style="width:100%" required>
                                    <option selected disabled hidden>Selecciona...</option>
                                    <option value="CFDI" selected>CFDI</option>
                                    <option value="PAGO">PAGO</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Titulo</label>
                                <input type="text" class="form-control" name="titulo" id="titulo" value="PAGO" required data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">
                            </div>
                        </div>
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
		        	<h5>Esta seguro de dar de baja este registro?</h5>
		        	<input type="hidden" id="numerofolio" name="numerofolio">
		        </form>	
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="aceptar">Confirmar Baja</button>
	      	</div>
    	</div>
  	</div>
</div> 
<!-- Modal Predeterminar Folio-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalpredeterminarfolio" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header {{$empresa->background_forms_and_modals}}">
        		<h5 class="modal-title" id="exampleModalLabel">Aviso</h5>
      		</div>
      		<div class="modal-body">
		      	<form id="formpredeterminar" action="#">
		        	<h5>Esta seguro de asignar como default este folio?</h5>
		        	<input type="hidden" id="numerofolio" name="numerofolio">
		        </form>	
      		</div>
	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
	        	<button type="button" class="btn btn-success btn-sm" id="btnpredeterminar">Guardar</button>
	      	</div>
    	</div>
  	</div>
</div> 
@endsection
@section('additionals_js')
    <script>
        /*urls y variables renderizadas con blade*/
        var mayusculas_sistema = '{{$mayusculas_sistema}}';
        var numerodecimales = '{{$numerodecimales}}';
        var numerocerosconfigurados = '{{$numerocerosconfigurados}}';
        var numerocerosconfiguradosinputnumberstep = '{{$numerocerosconfiguradosinputnumberstep}}';
        var nombreempresa = '{{$empresa->Nombre}}';
        var calleempresa = '{{$empresa->Calle}}';
        var numeroexteriorempresa = '{{$empresa->NoExterior}}';
        var coloniaempresa = '{{$empresa->Colonia}}';
        var cpempresa = '{{$empresa->LugarExpedicion}}';
        var municipioempresa = '{{$empresa->Municipio}}';
        var estadoempresa = '{{$empresa->Estado}}';
        var rfcempresa = '{{$empresa->Rfc}}';
        var telefonosempresa = '{{$empresa->Telefonos}}';
        var textareadomicilio = calleempresa+' '+numeroexteriorempresa+'\n'+coloniaempresa+' C.P.'+cpempresa+'\n'+municipioempresa+', '+estadoempresa+'\n'+'RFC: '+rfcempresa+'\n'+'TELEFONO(S): '+telefonosempresa;
        var pagaredefaultuno = 'Por el presente pagaré reconozco(emos) deber y me(nos) obligo(amos) a pagar en esta ciudad o en cualquier otra en que se me(nos) requiera de pago a %beneficiario o a su orden el día de su vencimiento %vence, la cantidad de %total ( %letratotal ). Valor recibido a mi(nuestra) entera satisfacción.'+'\n'+' %br La cantidad que ampara este pagaré es parte de la cantidad mayor, por la cual se otorgan otros pagarés con vencimientos posteriores y queda expresamente convenido que si no es pagado este documento precisamente a su vencimiento, se dará por vencidos anticipadamente los demás pagarés a los que se refiere esta cláusula.'+'\n'+'Este pagaré es mercantil y está regido por la ley general de Títulos y Operaciones de Crédito en su artículo 173 parte final y demás artículos correlativos.De no verificarse el pago de la cantidad que este pagaré expresa el día de su vencimiento, abonaré(mos) el rédito de 6% mensual por todo tiempo que esté insoluto, sin prejuicio al cobro más los gastos que por ellos se originen. Así mismo el otorgante se obliga en los términos del presente pagaré, por la persona que los suscriba, basta que quien lo firme, sea trabajador o dependiente laboral y se tendrá como si lo suscribiera el presente legal o dueño de la empresa otorgante.'+'\n'+' %br Otorgante: %nombre'+'\n'+' %br Domicilio: %direccion'+'\n'+' %br Ciudad: %ciudad %estadobeneficiario a %fecha'+'\n'+' %br _________________________'+'\n'+' %br Firma';
        var pagaredefaultdos = 'Por el presente pagaré reconozco(emos) deber y me(nos) obligo(amos) a pagar en esta ciudad o en cualquier otra en que se me(nos) requiera de pago a %beneficiario o a su orden el día de su vencimiento %vence, la cantidad de %total (%totalletra). Valor recibido a mi(nuestra) entera satisfacción.'+'\n'+'En caso de incumplimiento la cantidad consignada generará interes moratorios a razón de un 6% mensual.'+'\n'+'Otorgante: %t%nombre%t%tAl %fecha%t%t%t%t%t%tFirma____________________';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';
        var folios_comprobantes_pagos_obtener = '{!!URL::to('folios_comprobantes_pagos_obtener')!!}';
        var folios_comprobantes_pagos_predeterminar = '{!!URL::to('folios_comprobantes_pagos_predeterminar')!!}';
        var folios_comprobantes_pagos_obtener_ultimo_numero = '{!!URL::to('folios_comprobantes_pagos_obtener_ultimo_numero')!!}';
        var folios_comprobantes_pagos_guardar = '{!!URL::to('folios_comprobantes_pagos_guardar')!!}';
        var folios_comprobantes_pagos_alta_o_baja = '{!!URL::to('folios_comprobantes_pagos_alta_o_baja')!!}'; 
        var folios_comprobantes_pagos_obtener_folio = '{!!URL::to('folios_comprobantes_pagos_obtener_folio')!!}'; 
        var folios_comprobantes_pagos_guardar_modificacion = '{!!URL::to('folios_comprobantes_pagos_guardar_modificacion')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/catalogos/foliosfiscales/folioscomprobantespagos.js"></script>
@endsection