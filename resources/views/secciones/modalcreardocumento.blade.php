<!-- Modal Generar PDF-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalgenerarpdf" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header {{$empresa->background_forms_and_modals}}">
        		<h5 class="modal-title" id="exampleModalLabel">Generación de Documentos en PDF</h5>
      		</div>
      		<div class="modal-body">
                  <form id="formgenerarpdf" action='{{$rutacreardocumento}}' method="POST" data-parsley-validate="" target="_blank" onkeydown="return event.key != 'Enter';">
                    @csrf
		        	<h5 id="textomodalgenerarpdf"> </h5>
                    <div class="row">
                        <div class="col-md-4">
                            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Generar Documento en PDF por:</label>
                            <div class="col-md-12 form-check">
                                <input type="radio" name="tipogeneracionpdf" id="tipogeneracionpdf" value="0" onchange="mostrartipogeneracionpdf()" required>
                                <label for="tipogeneracionpdf">Selección de Folios</label>
                                <input type="radio" name="tipogeneracionpdf" id="tipogeneracionpdf1" value="1" onchange="mostrartipogeneracionpdf()" required>
                                <label for="tipogeneracionpdf1">Filtrado de Fechas</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label > Número Decimales en Documento:</label>
                            <select name="numerodecimalesdocumento" id="numerodecimalesdocumento" class="form-control select2" required>
                                <option selected disabled hidden>Selecciona...</option>
                                <option value="1">1</option>
                                <option value="2" selected>2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="divseleccionartipoformatocxc" hidden>
                            <label > Tipo de formato que desea generar:</label>
                            <select name="tipoformatocxc" id="tipoformatocxc" class="form-control select2">
                                <option value="0">Normal</option>
                                <option value="1">Poliza de Ingreso</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div id="tiposeleccionfolios" hidden>
                            <div class="col-md-12">
                                <small><b>Doble click para seleccionar</b></small>
                            </div>
                            <div class="col-md-12" hidden>
                                <input type="text" name="buscarfolio" id="buscarfolio" class="form-control" placeholder="Teclea el folio..." autocomplete="off" onkeyup="relistarbuscarstringlike()">
                            </div>    
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="tablafoliosencontrados" class="tablafoliosencontrados table table-bordered table-striped table-hover" style="width:100% !important;"> 
                                            <thead class="customercolor" id="columnastablafoliosencontrados">
                                            <tr>
                                                <th>campo1</th><!-- IMPORTANTE NO QUITAR -->
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot id="columnasfootertablafoliosencontrados">
                                            </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <select  name="arraypdf[]" id="arraypdf" class="form-control select2" multiple="multiple" style="width:100% !important;" >
                                </select>
                            </div>
                        </div>
                        <div id="tipofiltracionfechas" hidden>
                            <div class="col-md-12">
                                <label >Fecha Inicio</label>
                                <input type="date"  name="fechainiciopdf" id="fechainiciopdf" onchange="validarrangofechascreaciondocumentos();" class="form-control"  style="min-width:95%;">
                            </div>
                            <div class="col-md-12">
                            <label >Fecha Terminación</label>
                                <input type="date"  name="fechaterminacionpdf" id="fechaterminacionpdf" onchange="validarrangofechascreaciondocumentos();" class="form-control"  style="min-width:95%;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
	        	        <div class="btn btn-danger btn-sm" data-dismiss="modal" onclick="destruirtablafoliosexportacion()">Salir</div>
	        	        <button type="submit" class="btn btn-success btn-sm" id="btngenerardocumentospdf">Generar Documento en PDF</button>
	      	        </div>
		        </form>	
      		</div>
    	</div>
  	</div>
</div> 