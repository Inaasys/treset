<!-- Modal Generar PDF-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalenviarpdfemail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header {{$empresa->background_forms_and_modals}}">
        		<h5 class="modal-title" id="exampleModalLabel">Envió documento PDF por email</h5>
      		</div>
      		<div class="modal-body">
                  <form id="formenviarpdfemail" >
                    @csrf
		        	<h5 id="textomodalenviarpdfemail"> </h5>
                    <div class="row">
                        <div class="col-md-12" hidden>
                            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;De:</label>
                            <div class="col-md-12 form-check">
                                <input type="text" class="form-control" name="emailde" id="emailde" data-parsley-type="email" required>
                                <input type="hidden" class="form-control" name="emaildocumento" id="emaildocumento" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Para:</label>
                            <div class="col-md-12 form-check">
                                <input type="text" class="form-control" name="emailpara" id="emailpara" data-parsley-type="email" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Asunto:</label>
                            <div class="col-md-12 form-check">
                                <textarea class="form-control" name="emailasunto" id="emailasunto"  required rows="4" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>
                            </div>
                        </div>
                        <div class="col-md-12" id="divincluirxml" hidden>
                            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Incluir XML:</label><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="hidden" name="incluir_xml" class="filled-in" value="0"/>
                            <input type="checkbox" name="incluir_xml" id="incluir_xml" class="filled-in" value="1"/>
                            <label for="incluir_xml"></label>
                        </div>
                    </div>
                    <div class="modal-footer">
	        	        <div class="btn btn-danger btn-sm" data-dismiss="modal">Salir</div>
	        	        <button type="submit" class="btn btn-success btn-sm" id="btnenviarpdfemail">Enviar email</button>
	      	        </div>
		        </form>	
      		</div>
    	</div>
  	</div>
</div> 