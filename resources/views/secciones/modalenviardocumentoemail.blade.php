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
                            <div class="col-md-12 form-check">
                                <label>De:</label>
                                <input type="text" class="form-control" name="emailde" id="emailde" data-parsley-type="email" required>
                                <input type="hidden" class="form-control" name="emaildocumento" id="emaildocumento" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="col-md-12 form-check">
                                <label>Para:</label>
                                <input type="text" class="form-control" name="emailpara" id="emailpara" data-parsley-type="email" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="col-md-12 form-check">
                                <label>Con Copia a: (escribe o pega el correo y presiona la tecla enter)</label> <br>
                                <select  name="correosconcopia[]" id="correosconcopia" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-12 form-check">
                                <label>Primer correo configurado con copia siempre:</label>
                                <input type="text" class="form-control" name="email2cc" id="email2cc" value="{{$correodefault1enviodocumentos}}" autocomplete="off"  data-parsley-type="email" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-12 form-check">
                                <label>Segundo correo configurado con copia siempre:</label>
                                <input type="text" class="form-control" name="email3cc" id="email3cc" value="{{$correodefault2enviodocumentos}}" autocomplete="off"  data-parsley-type="email" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="col-md-12 form-check">
                                <label>Asunto:</label>
                                <textarea class="form-control" name="emailasunto" id="emailasunto"  required rows="2" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="col-md-12 form-check">
                                <label>Mensaje:</label>
                                <textarea class="form-control" name="emailmensaje" id="emailmensaje"  required rows="2" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>
                            </div>
                        </div>
                        <div class="col-md-12" id="divadjuntararchivo">
                            <div class="col-md-6 form-check">
                                <label>Adjuntar Archivo 1</label>
                                <input type="file" name="archivoadjunto" id="archivoadjunto"  class="dropify" data-max-file-size="2M" data-allowed-file-extensions="pdf xlsx xls csv xml docx zip"  data-height="100"/>
                            </div>
                            <div class="col-md-6 form-check">
                                <label>Adjuntar Archivo 2</label>
                                <input type="file" name="archivoadjunto2" id="archivoadjunto2"  class="dropify" data-max-file-size="2M" data-allowed-file-extensions="pdf xlsx xls csv xml docx zip"  data-height="100"/>
                            </div>
                        </div>
                        <div class="col-md-12" id="divincluirxml" hidden>
                            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Incluir XML:</label><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="hidden" name="incluir_xml" class="filled-in" value="0"/>
                            <input type="checkbox" name="incluir_xml" id="incluir_xml" class="filled-in" value="1"/>
                            <label for="incluir_xml"></label>
                        </div>
                        <div class="col-md-12">
                            <input type="hidden" name="tipoformato" id="tipoformato" value="N/A">
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