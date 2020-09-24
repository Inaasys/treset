<!-- Modal Generar PDF-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalgenerarpdf" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header bg-red">
        		<h5 class="modal-title" id="exampleModalLabel">Generación de Documentos en PDF</h5>
      		</div>
      		<div class="modal-body">
                  <form id="formgenerarpdf" action='{{$rutacreardocumento}}' method="POST" data-parsley-validate="" target="_blank">
                    @csrf
		        	<h5 id="textomodalgenerarpdf"> </h5>
                    <div class="row">
                        <label>Generar Documento en PDF por:</label>
                        <div class="col-md-12 form-check">
                            <input type="radio" name="tipogeneracionpdf" id="tipogeneracionpdf" value="0" onchange="mostrartipogeneracionpdf()" required>
                            <label for="tipogeneracionpdf">Selección de Folios</label>
                            <input type="radio" name="tipogeneracionpdf" id="tipogeneracionpdf1" value="1" onchange="mostrartipogeneracionpdf()" required>
                            <label for="tipogeneracionpdf1">Filtrado de Fechas</label>
                        </div>
                    </div>
                    <div class="row">
                        <div id="tiposeleccionfolios" hidden>
                            <div class="col-md-12">
                                <input type="text" name="buscarfolio" id="buscarfolio" class="form-control" placeholder="Teclea el folio..." autocomplete="off" onkeyup="relistarbuscarstringlike()">
                            </div>    
                            <div class="col-md-12 table-responsive">
                                <table id="tablafoliosencontrados" class="tablafoliosencontrados table table-bordered table-striped table-hover" style="width:100% !important;"> 
                                        <thead class="customercolor" id="columnastablafoliosencontrados">
                                        <tr>
                                            <th>campo1</th><!--IMPORTANTE NO QUITAR-->
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                </table>
                            </div>
                            <div class="col-md-12">
                                <select  name="arraypdf[]" id="arraypdf" class="form-control select2" multiple="multiple" style="width:100% !important;" >
                                </select>
                            </div>
                        </div>
                        <div id="tipofiltracionfechas" hidden>

                            <div class="col-md-12">
                                <label >Fecha Inicio</label>
                                <input type="date"  name="fechainiciopdf" id="fechainiciopdf" class="form-control"  >
                            </div>
                            <div class="col-md-12">
                            <label >Fecha Terminación</label>
                                <input type="date"  name="fechaterminacionpdf" id="fechaterminacionpdf" class="form-control"  >
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
	        	        <div class="btn btn-danger btn-sm" data-dismiss="modal" onclick="destruirtablafoliosexportacion()">Salir</div>
	        	        <button type="submit" class="btn btn-success btn-sm" >Generar Documento en PDF</button>
	      	        </div>
		        </form>	
      		</div>
    	</div>
  	</div>
</div> 