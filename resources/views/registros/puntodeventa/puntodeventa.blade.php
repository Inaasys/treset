
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="icon" type="image/png" href="images/iconapp1.png" sizes="16x16">
        <link rel="icon" type="image/png" href="images/iconapp1.png" sizes="32x32">
        <title>Punto de Venta - {{$empresa->Nombre}}</title>
        <!-- Bootstrap Core Css -->
        <link href="plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
        <!-- Waves Effect Css -->
        <link href="plugins/node-waves/waves.css" rel="stylesheet" />
        <!-- Custom Css -->
        <link href="css/style.css" rel="stylesheet">
        <link href="css/parsley/parsley.css" rel="stylesheet">
        <link href="css/toastr/toastr.min.css" rel="stylesheet">
        <!-- Wait Me Css -->
        <link href="plugins/waitme/waitMe.css" rel="stylesheet" />
        <!-- JQuery DataTable Css -->
        <link href="plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css" rel="stylesheet">
        <!-- focus en td table-->
        <link rel="stylesheet" type="text/css" href="plugins/jquery-datatable-keytable/keyTable.bootstrap.min.css">
        <!--Select 2-->
        <link href="js/select2/css/select2.min.css" rel="stylesheet" /> 
    </head>
    <body class="five-zero-zero">
        <nav class="navbar navbar-default {{$empresa->background_navbar}}">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <a class="navbar-brand" href="#"><img id="navbarlogotipoempresa" src="logotipo_empresa/{{$empresa->Logo}}" style="object-fit: contain;" width="125" height="50"></a>
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <form class="navbar-form navbar-right">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control divorinputmodxl inputnextdet" id="buscarcodigo" placeholder="Escribe el código del producto" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control divorinputmodxl inputnextdet" id="numerocliente" placeholder="Escribe el numero del cliente" autocomplete="off">
                                    <input type="hidden" class="form-control divorinputmodxl inputnextdet" id="numeroclienteanterior" >
                                    <input type="hidden" class="form-control divorinputmodxl inputnextdet" id="cliente" >
                                    <label id="textonombrecliente">nombre del cliente</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
        <div class="body">
            <div class="table-responsive cabecerafija" style="height: 350px;overflow-y: scroll;padding: 0px 0px;">
                <table id="tablaproductospuntodeventa" class="table table-bordered tablaproductospuntodeventa">
                    <thead class="{{$empresa->background_tables}}">
                        <tr>
                            <th><div style="width:100px !important;">Operaciones</div></th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Unidad</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Importe</th>
                            <th>Descuento %</th>
                            <th>Descuento $</th>
                            <th>SubTotal $</th>
                            <th>IVA %</th>
                            <th>IVA $</th>
                            <th>Total $</th>
                            <th>Costo</th>
                            <th>ClaveProducto</th>
                            <th>ClaveUnidad</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="alert {{$empresa->background_navbar}}">
                <strong>CONTROLES DE TODO EL MODULO!</strong>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card text-justify">
                        <div class="body {{$empresa->background_forms_and_modals}}">
                            <table>
                                <tr>
                                    <td>NOMBRE: </td>
                                    <td id="nombreclienteseleccionado"></td>
                                </tr>
                                <tr>
                                    <td>RFC: </td>
                                    <td id="rfcclienteseleccionado"></td>
                                </tr>
                                <tr>
                                    <td>DIRECCIÓN: </td>
                                    <td id="direccionclienteseleccionado"></td>
                                </tr>
                                <tr>
                                    <td>REGIMEN FISCAL: </td>
                                    <td id="regimenfiscalclienteseleccionado"></td>
                                </tr>
                                <tr>
                                    <td>AGENTE: </td>
                                    <td id="agenteclienteseleccionado"></td>
                                </tr>
                                <tr>
                                    <td>CRÉDITO: </td>
                                    <td id="creditoclienteseleccionado"></td>
                                </tr>
                                <tr>
                                    <td>SALDO: </td>
                                    <td id="saldoclienteseleccionado"></td>
                                </tr>
                            </table>

                        </div>
                    </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
            </div>
            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                <table class="table table-striped table-hover">
                    <tr>
                        <td style="padding:0px !important;">Importe</td>
                        <td style="padding:0px !important;"><input type="number" class="form-control" style="width:100% !important;height:25px !important;" name="importe" id="importe" value="0.{{$numerocerosconfigurados}}" required readonly></td>
                    </tr>
                    <tr>
                        <td style="padding:0px !important;">Descuento</td>
                        <td style="padding:0px !important;"><input type="number" class="form-control" style="width:100% !important;height:25px !important;" name="descuento" id="descuento" value="0.{{$numerocerosconfigurados}}" required readonly></td>
                    </tr>
                    <tr>
                        <td style="padding:0px !important;">SubTotal</td>
                        <td style="padding:0px !important;"><input type="number" class="form-control" style="width:100% !important;height:25px !important;" name="subtotal" id="subtotal" value="0.{{$numerocerosconfigurados}}" required readonly></td>
                    </tr>
                    <tr>
                        <td style="padding:0px !important;">Iva</td>
                        <td style="padding:0px !important;"><input type="number" class="form-control" style="width:100% !important;height:25px !important;" name="iva" id="iva" value="0.{{$numerocerosconfigurados}}" required readonly></td>
                    </tr>
                    <tr>
                        <td style="padding:0px !important;">Total</td>
                        <td style="padding:0px !important;"><input type="number" class="form-control" style="width:100% !important;height:25px !important;" name="total" id="total" value="0.{{$numerocerosconfigurados}}" required readonly></td>
                    </tr>
                </table>
            </div>
        </div>

        <script>
            /*urls y variables renderizadas con blade*/
            var mayusculas_sistema = '{{$mayusculas_sistema}}';
            var numerodecimales = '{{$numerodecimales}}';
            var numerocerosconfigurados = '{{$numerocerosconfigurados}}';
            var numerocerosconfiguradosinputnumberstep = '{{$numerocerosconfiguradosinputnumberstep}}';
            var meshoy = '{{$meshoy}}';
            var periodohoy = '{{$periodohoy}}';
            var serieusuario = '{{$serieusuario}}';
            var campos_activados = '{{$configuracion_tabla->campos_activados}}';
            var campos_desactivados = '{{$configuracion_tabla->campos_desactivados}}';
            var columnas_ordenadas = '{{$configuracion_tabla->columnas_ordenadas}}';
            var primerordenamiento = '{{$configuracion_tabla->primerordenamiento}}';
            var formaprimerordenamiento = '{{$configuracion_tabla->formaprimerordenamiento}}';
            var segundoordenamiento = '{{$configuracion_tabla->segundoordenamiento}}';
            var formasegundoordenamiento= '{{$configuracion_tabla->formasegundoordenamiento}}';
            var tercerordenamiento = '{{$configuracion_tabla->tercerordenamiento}}';
            var formatercerordenamiento = '{{$configuracion_tabla->formatercerordenamiento}}';
            var campos_busquedas = '{{$configuracion_tabla->campos_busquedas}}';
            var nombreempresa = '{{$empresa->Nombre}}';
            var rfcempresa = '{{$empresa->Rfc}}';
            var urlgenerarformatoexcel = '{{$urlgenerarformatoexcel}}';
            var background_navbar = '{{$empresa->background_navbar}}';
            var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
            var background_tables = '{{$empresa->background_tables}}';
            var urlgenerarplantilla = '{{$urlgenerarplantilla}}';       
            var rol_usuario_logueado = '{{Auth::user()->role_id}}';
            var rol_de_usuario_logueado_en_sistema = '{{Auth::user()->role_id}}';
            var verificarinsumosremisionenot = '{{$empresa->VerificarPartidasRemisionEnOT}}';
            var controlarconsecutivonumrequisicion = '{{$empresa->ControlarConsecutivoNumeroRequisicionEnRemisiones}}';
            var mostrarinsumoporpartidaenremisiones = '{{$empresa->MostrarInsumoPorPartidaEnCapturaDeRemisiones}}';
            var usuariosamodificarinsumos = '{{$usuariosamodificarinsumos}}';
            var usuariologueado = '{{Auth::user()->user}}';
            var modificarconsecutivofolioenremisiones = '{{$modificarconsecutivofolioenremisiones}}';
            var validarutilidadnegativa = '{{$validarutilidadnegativa}}';
            var pedirobligatoriamentereferenciarnremisiones = '{{$empresa->PedirObligatoriamenteReferenciaEnRemisiones}}';
            var pedirobligatoriamenteordenservicioenremisiones = '{{$empresa->PedirObligatoriamenteOrdenServicioEnRemisiones}}';
            var pedirobligatoriamenteequipoenremisiones = '{{$empresa->PedirObligatoriamenteEquipoEnRemisiones}}';
            var generarformatorequisiciontyt = '{{$empresa->GenerarFormatoRequisicionTYT}}';
            var urlpdfsimpresionesrapidas = '{{asset("xml_descargados/")}}/';
            var punto_de_venta_obtener_producto_por_codigo = '{!!URL::to('punto_de_venta_obtener_producto_por_codigo')!!}';
            var punto_de_venta_obtener_datos_agregar_fila_producto = '{!!URL::to('punto_de_venta_obtener_datos_agregar_fila_producto')!!}';
            var punto_de_venta_obtener_nuevo_saldo_cliente = '{!!URL::to('punto_de_venta_obtener_nuevo_saldo_cliente')!!}';
            var punto_de_venta_obtener_cliente_por_numero = '{!!URL::to('punto_de_venta_obtener_cliente_por_numero')!!}';

            
        </script>
        
        <!-- Jquery Core Js -->
        <script src="plugins/jquery/jquery.min.js"></script>
        <!-- Bootstrap Core Js -->
        <script src="plugins/bootstrap/js/bootstrap.js"></script>
        <!-- Waves Effect Plugin Js -->
        <script src="plugins/node-waves/waves.js"></script>     
        <script src="js/toastr/toastr.min.js"></script>
        <!-- scripit init-->
        <script src="js/toastr/toastr.init.js"></script>
        <script src="js/parsley/parsley.min.js"></script>
        <!-- Cambiar idioma de parsley -->
        <script src="js/parsley/i18n/es.js"></script>    
        <!--toltips-->
        <script src="js/pages/ui/tooltips-popovers.js"></script>    
        <!-- Jquery DataTable Plugin Js -->
        <script src="plugins/jquery-datatable/jquery.dataTables.js"></script>
        <script src="plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.js"></script>
        <!-- focus en td table-->
        <script src="plugins/jquery-datatable-keytable/dataTables.keyTable.min.js"></script>
        <!-- Custom Js -->
        <script src="js/pages/ui/modals.js"></script>
        <!--Libreria para operaciones aritmeticas correctas en javascript-->
        <script src="js/decimaljs/decimal.min.js"></script>
        <!--Funciones globales para la aplicacion-->
        <script src="js/select2/js/select2.min.js"></script>
        <script>
            $(".select2").select2();
        </script> 

        <script src="js/barcodelistener/jquery.barcodelistener-1.1-min.js"></script>
        <script src="js/barcodelistener/jquery-barcodeListener.js"></script>
        <script src="scripts_inaasys/registros/puntodeventa/puntodeventa.js"></script>

        
    </body>
</html>