    <!-- Modal Configurar Tabla-->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="modalconfigurartabla" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div id="formulariopermisos">
                    <div class="modal-header bg-red">
                        <h4 class="modal-title" id="titulomodalconfiguraciontabla"></h4>
                    </div>
                    <form id="formconfigurartabla" action="{{$rutaconfiguraciontabla}}" method="post" data-parsley-validate="">
                        @csrf
                        <div class="modal-body">
                            <div class="col-md-12" id="tabsconfigurartabla">
                                <!-- aqui van los formularios de alta o modificacion y se agregan automaticamente con jquery -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>
                            <button type="submit" class="btn btn-success btn-sm" id="btnguardarconfigurartabla">Guardar</button>
                        </div>
                    </form> 
                </div>
            </div>
        </div>
    </div>