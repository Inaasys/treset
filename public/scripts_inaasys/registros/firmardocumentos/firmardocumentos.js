'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
  campos_a_filtrar_en_busquedas();
  listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 1000));
}
function asignarfechaactual(){
  $.get(ordenes_compra_obtener_fecha_actual_datetimelocal, function(fechas){
    $("#fecha").val(fechas.fecha).attr('min', fechas.fechamin).attr('max', fechas.fechamax);
  }) 
}
//cerrar modales
function limpiarmodales(){
  $("#tabsform").empty();
}
//limpiar todos los inputs del formulario alta
function limpiar(){
  $("#formparsley")[0].reset();
  //Resetear las validaciones del formulario alta
  form = $("#formparsley");
  form.parsley().reset();
  //volver a aplicar configuracion a datatable principal para que realize la busqueda con la tecla enter
  regresarbusquedadatatableprincipal();
}
//mostrar modal formulario
function mostrarmodalformulario(tipo, modificacionpermitida){
    $("#ModalFormulario").modal('show');
    if(tipo == 'ALTA'){
        $("#btnGuardar").show();
        $("#btnGuardarModificacion").hide();
    }else if(tipo == 'MODIFICACION'){
        if(modificacionpermitida == 0){
          $("#btnGuardar").hide();
          $("#btnGuardarModificacion").hide();
        }else{
          $("#btnGuardar").hide();
          $("#btnGuardarModificacion").show();
        }
    }   
}
//ocultar modal formulario
function ocultarmodalformulario(){
  $("#ModalFormulario").modal('hide');
}
//mostrar formulario en modal y ocultar tabla de seleccion
function mostrarformulario(){
  $("#formulario").show();
  $("#contenidomodaltablas").hide();
}
//mostrar tabla de seleccion y ocultar formulario en modal
function ocultarformulario(){
  $("#formulario").hide();
  $("#contenidomodaltablas").show();
}
//cambiar url para exportar excel
function cambiarurlexportarexcel(){
  //colocar el periodo seleccionado como parametro para exportar a excel
  var periodo = $("#periodo").val();
  $("#btnGenerarFormatoExcel").attr("href", urlgenerarformatoexcel+'?periodo='+periodo);
}
function relistar(){
    cambiarurlexportarexcel();
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
}
//listar todos los registros de la tabla
function listar(){
    cambiarurlexportarexcel();
    //Campos ordenados a mostras
    var campos = columnas_ordenadas.split(",");
    var campos_busqueda = campos_busquedas.split(",");
    //agregar inputs de busqueda por columna
    $('#tbllistado tfoot th').each( function () {
        var titulocolumnatfoot = $(this).text();
        var valor_encontrado_en_array = campos_busqueda.indexOf(titulocolumnatfoot); 
        if(valor_encontrado_en_array >= 0){
            $(this).html( '<input type="text" placeholder="Buscar en columna '+titulocolumnatfoot+'" />' );
        }
    });
    // armar columas para datatable se arma desde funcionesglobales.js
    var campos_tabla = armar_columas_datatable(campos,campos_busqueda);
    tabla=$('#tbllistado').DataTable({
        "lengthMenu": [ 100, 250, 500, 1000 ],
        "pageLength": 100,
        "sScrollX": "110%",
        "sScrollY": "350px",
        processing: true,
            'language': {
                'loadingRecords': '&nbsp;',
                'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
            url: firmardocumentos_obtener,
            data: function (d) {
                d.periodo = $("#periodo").val();
            }
        },
        "createdRow": function( row, data, dataIndex){
            if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
        },
        columns: campos_tabla,
        initComplete: function () {
            // Aplicar busquedas por columna
            this.api().columns().every( function () {
                var that = this;
                $('input',this.footer()).on( 'change', function(){
                    if(that.search() !== this.value){
                        that.search(this.value).draw();
                    }
                });
            });
            //Aplicar busqueda general
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                    $('#tbllistado').DataTable().search( this.value ).draw();
                }
            });
        }
    });
}
//obtener tipos ordenes de compra
function obtenertiposdocumentos(){
    $.get(firmardocumentos_obtener_tipos_documentos, function(select_tipos_documentos){
        $("#tipo").html(select_tipos_documentos);
    })  
}
//obtener series documento
function obtenerfoliosdocumento(){
    $("#stringdocumentosseleccionados").val("");
    ocultarformulario();
    var tablaseriesdocumento=   '<div class="modal-header '+background_forms_and_modals+'">'+
                                    '<h4 class="modal-title">Series Documento </h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row">'+
                                        '<div class="col-md-12">'+
                                            '<div class="table-responsive">'+
                                                '<table id="tbllistadoseriedocumento" class="tbllistadoseriedocumento table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                    '<thead class="'+background_tables+'">'+
                                                        '<tr>'+
                                                            '<th>Firmar</th>'+
                                                            '<th>TipoDocumento</th>'+
                                                            '<th>Serie</th>'+
                                                            '<th>Folio</th>'+
                                                            '<th>Documento</th>'+
                                                            '<th>Fecha</th>'+
                                                        '</tr>'+
                                                    '</thead>'+
                                                    '<tbody></tbody>'+
                                                '</table>'+
                                            '</div>'+
                                        '</div>'+   
                                    '</div>'+
                                '</div>'+
                                '<div class="modal-footer">'+
                                    '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                                    '<button type="button" class="btn btn-success btn-sm" onclick="cargardocumentosseleccionados();">Cargar documentos para firmarlos</button>'+
                                '</div>';  
    $("#contenidomodaltablas").html(tablaseriesdocumento);
    var tserdoc = $('#tbllistadoseriedocumento').DataTable({
        "lengthMenu": [ 500, 1000, 1500, 2000, 2500 ],
        "pageLength": 2000,
        "sScrollX": "110%",
        "sScrollY": "370px",
        "bScrollCollapse": true,  
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
            url: firmardocumentos_obtener_folios_documento,
            data: function (d) {
                d.tipo = $("#tipo").val();
                d.stringdocumentosseleccionados = $("#stringdocumentosseleccionados").val();
            }
        },
        columns: [
            { data: 'firmar', name: 'firmar', orderable: false, searchable: false },
            { data: 'TipoDocumento', name: 'TipoDocumento' },
            { data: 'Serie', name: 'Serie' },
            { data: 'Folio', name: 'Folio' },
            { data: 'Documento', name: 'Documento' },
            { data: 'Fecha', name: 'Fecha' }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoseriedocumento').DataTable().search( this.value ).draw();
                }
            });
        },
    });
}
//sleeccionar numero documento
var arraydocumentosseleccionados = [];
//obtener todos los datos de la orden de compra seleccionada
function seleccionardocumento(Documento){
    construirarraydocumentosseleccionados();
}
function construirarraydocumentosseleccionados(){
    $("#stringdocumentosseleccionados").val("");
    var arraydocumentosseleccionados = [];
    var lista = document.getElementsByClassName("documentosseleccionados");
    for (var i = 0; i < lista.length; i++) {
      if(lista[i].checked){
        arraydocumentosseleccionados.push(lista[i].value);
      }
    }
    $("#stringdocumentosseleccionados").val(arraydocumentosseleccionados.sort());
}
var contadorproductos=0;
var contadorfilas = 0;
var partida = 1;
function cargardocumentosseleccionados(){
    contadorfilas = 0;
    partida = 1;
    $("#tabladetallefirmas tbody").html("");
    var stringdocumentosseleccionados = $("#stringdocumentosseleccionados").val();
    var tipo = $("#tipo").val();
    $.get(firmardocumentos_obtener_documentos_a_firmar, {stringdocumentosseleccionados:stringdocumentosseleccionados, tipo:tipo, contadorfilas:contadorfilas, partida:partida}, function(data){
        $("#tabladetallefirmas tbody").append(data.filasfirmas);
        $(".titulofirmapartida").select2();
        //array de remisiones seleccionadas
        construirarraydocumentosseleccionados();
        //comprobar numero de filas en la tabla
        comprobarfilas();
        contadorfilas = data.contadorfilas;
        partida = data.partida;
        mostrarformulario();
        //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
        $(".inputnextdet").keypress(function (e) {
            //recomentable para mayor compatibilidad entre navegadores.
            var code = (e.keyCode ? e.keyCode : e.which);
            if(code==13){
                var index = $(this).index(".inputnextdet");          
                $(".inputnextdet").eq(index + 1).focus().select(); 
            }
        });
    })
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
    var numerofilas = $("#tabladetallefirmas tbody tr").length;
    $("#numerofilas").val(numerofilas);
}
//renumerar las filas de la orden de compra
function renumerarfilasordencompra(){
  var lista;
  var tipo = "alta";
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("cantidadpartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilasordencompra("+i+');cambiodecantidadopreciopartida('+i+',\''+tipo +'\')');
  }
  //renumerar descuento en pesos
  lista = document.getElementsByClassName("descuentoporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculardescuentopesospartida("+i+')');
  }
}  
//alta
function alta(tipoalta){
  $("#titulomodal").html('Alta Firma Documentos' + tipoalta);
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs ='<div class="col-md-12">'+
                '<div class="row">'+
                    '<div class="col-md-2">'+
                        '<label>TipoDocumento</label>'+
                        '<select name="tipo" id="tipo" class="form-control select2" onchange="obtenerfoliosdocumento();" style="width:100% !important;" required>'+ 
                        '</select>'+
                    '</div>'+
                    '<div class="col-xs-12 col-sm-12 col-md-3">'+
                        '<label>Fecha</label>'+
                        '<input type="datetime-local" class="form-control inputnext" name="fecha" id="fecha"  required  style="min-width:95%;" data-parsley-excluded="true" onkeydown="return false">'+
                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                        '<input type="hidden" class="form-control" name="stringdocumentosseleccionados" id="stringdocumentosseleccionados" value="">'+     
                        '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+                   
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-md-12">'+
              '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                '<li role="presentation" class="active">'+
                  '<a href="#productostab" data-toggle="tab">Documentos</a>'+
                '</li>'+
              '</ul>'+
              '<div class="tab-content">'+
                '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                  '<div class="row">'+
                    '<div class="col-md-12 table-responsive cabecerafija" style="height: 375px;overflow-y: scroll;padding: 0px 0px;">'+
                      '<table id="tabladetallefirmas" class="table table-bordered tabladetallefirmas">'+
                        '<thead class="'+background_tables+'">'+
                          '<tr>'+
                            '<th class="'+background_tables+'">#</th>'+
                            '<th class="customercolortheadth">TipoDocumento</th>'+
                            '<th class="customercolortheadth">Documento</th>'+
                            '<th class="customercolortheadth">Usuario</th>'+
                            '<th class="'+background_tables+'">Colocar firma en</th>'+
                          '</tr>'+
                        '</thead>'+
                        '<tbody>'+           
                        '</tbody>'+
                      '</table>'+
                    '</div>'+
                  '</div>'+   
                '</div>'+ 
              '</div>'+
            '</div>';
  $("#tabsform").html(tabs);
  obtenertiposdocumentos();
  asignarfechaactual();
  //reiniciar los contadores
  contadorproductos=0;
  contadorfilas = 0;
  //activar select2
  $("#tipo").select2();
  //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
  $(".inputnext").keypress(function (e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      var index = $(this).index(".inputnext");          
      $(".inputnext").eq(index + 1).focus().select(); 
    }
  });
  $("#ModalAlta").modal('show');
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    var numerofilas = $("#numerofilas").val();
    if(parseInt(numerofilas) > 0 && parseInt(numerofilas) < 500){
        var tipo = $("#tipo").val();
        switch(tipo){
            case "OrdenesDeCompra":
                var urlalta = firmardocumentosoc_guardar;
                break;
            case "Compras":
                var urlalta = firmardocumentoscom_guardar;
                break;
            case "ContraRecibos":
                var urlalta = firmardocumentosconrec_guardar;
                break;
            case "Remisiones":
                var urlalta = firmardocumentosrem_guardar;
                break;
            case "cotizaciones_t":
                break;
            case "Traspasos":
                var urlalta = firmardocumentostras_guardar;
                break;
            case "Ordenes de Trabajo":
                break;
            case "CuentasPorPagar":
                break;
            case "NotasCreditoProveedor":
                var urlalta = firmardocumentosnp_guardar;
                break;
            case "asignacion_herramientas":
                var urlalta = firmardocumentosah_guardar
                break;
            case "prestamo_herramientas":
                break;
            case "AjustesInventario":
                var urlalta = firmardocumentosaji_guardar;
                break;
            case "CotizacionesProductos":
                var urlalta = firmardocumentoscp_guardar;
                break;
            case "CotizacionesServicios":
                var urlalta = firmardocumentoscs_guardar;
                break;
            case "Produccion":
                var urlalta = firmardocumentospro_guardar;
                break;
            case "Requisiciones":
                var urlalta = firmardocumentosreq_guardar;
                break;
        }
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:urlalta,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                if(data > 0){
                    msj_firmasyaexistentes();
                }else{
                    msj_datosguardadoscorrectamente();
                }
                limpiar();
                ocultarmodalformulario();
                limpiarmodales();
                $('.page-loader-wrapper').css('display', 'none');
            },
            error:function(data){
                if(data.status == 403){
                    msj_errorenpermisos();
                }else{
                    msj_errorajax();
                }
                $('.page-loader-wrapper').css('display', 'none');
            }
        })
    }else{
      msj_erroralmenosunaentrada();
    }
  }else{
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(firmadesactivar){
    $.get(firmardocumentos_verificar_uso_en_modulos,{firmadesactivar:firmadesactivar}, function(data){
        if(data.Status == 'BAJA'){
            $("#firmadesactivar").val(0);
            $("#textomodaldesactivar").html('Error, esta firma ya fue dada de baja ');
            $("#divmotivobaja").hide();
            $("#btnbaja").hide();
            $('#estatusregistro').modal('show');
        }else{
            $("#firmadesactivar").val(firmadesactivar);
            $("#textomodaldesactivar").html('Estas seguro de dar de baja la firma? TipoDocumento: '+ data.TipoDocumento + ', Número de Documento: ' + data.Documento + ', Firma colocada en: ' + data.ReferenciaPosicion);
            $("#motivobaja").val("");
            $("#divmotivobaja").show();
            $("#btnbaja").show();
            $('#estatusregistro').modal('show');
        }
    }) 
}
$("#btnbaja").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formdesactivar")[0]);
  var form = $("#formdesactivar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:firmardocumentos_bajas,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#estatusregistro').modal('hide');
        msj_datosguardadoscorrectamente();
        $("#motivobaja").val("");
        $('.page-loader-wrapper').css('display', 'none');
      },
      error:function(data){
        if(data.status == 403){
          msj_errorenpermisos();
        }else{
          msj_errorajax();
        }
        $('#estatusregistro').modal('hide');
        $('.page-loader-wrapper').css('display', 'none');
      }
    })
  }else{
    form.parsley().validate();
  }
});
//configurar tabla
function configurar_tabla(){
  var checkboxscolumnas = '';
  var optionsselectbusquedas = '';
  var campos = campos_activados.split(",");
  for (var i = 0; i < campos.length; i++) {
    var returncheckboxfalse = '';
    if(campos[i] == 'Orden' || campos[i] == 'Status'){
      returncheckboxfalse = 'onclick="javascript: return false;"';
    }
    checkboxscolumnas = checkboxscolumnas + '<div class="col-md-2 form-check">'+
                                              '<input type="checkbox" name="'+campos[i]+'" id="id'+campos[i]+'" class="filled-in datotabla" value="'+campos[i]+'" readonly onchange="construirarraydatostabla(this);" '+returncheckboxfalse+'/>'+
                                              '<label for="id'+campos[i]+'">'+campos[i]+'</label>'+
                                            '</div>';
    optionsselectbusquedas = optionsselectbusquedas + '<option value="'+campos[i]+'">'+campos[i]+'</option>';
  }
  var campos = campos_desactivados.split(",");
  for (var i = 0; i < campos.length; i++) {
    checkboxscolumnas = checkboxscolumnas + '<div class="col-md-2 form-check">'+
                                              '<input type="checkbox" name="'+campos[i]+'" id="id'+campos[i]+'" class="filled-in datotabla" value="'+campos[i]+'" readonly onchange="construirarraydatostabla(this);"/>'+
                                              '<label for="id'+campos[i]+'">'+campos[i]+'</label>'+
                                            '</div>';
    optionsselectbusquedas = optionsselectbusquedas + '<option value="'+campos[i]+'">'+campos[i]+'</option>';
  }
  //formulario configuracion tablas se arma desde funcionesglobales.js
  var tabs = armar_formulario_configuracion_tabla(checkboxscolumnas,optionsselectbusquedas);
  $("#tabsconfigurartabla").html(tabs);
  if(rol_usuario_logueado == 1){
    $("#divorderbystabla").show();
  }
  $("#string_datos_ordenamiento_columnas").val(columnas_ordenadas);
  $("#string_datos_tabla_true").val(campos_activados);
  $("#string_datos_tabla_false").val(campos_desactivados);
  $("#modalconfigurartabla").modal('show');
  $("#titulomodalconfiguraciontabla").html("Configuración de la tabla");
  $('.dd').nestable();
  $("#selectorderby1").val(primerordenamiento).select2();
  $("#deorderby1").val(formaprimerordenamiento).select2();
  $("#selectorderby2").val(segundoordenamiento).select2();
  $("#deorderby2").val(formasegundoordenamiento).select2();
  $("#selectorderby3").val(tercerordenamiento).select2();
  $("#deorderby3").val(formatercerordenamiento).select2();
  $.each(campos_busquedas.split(","), function(i,e){
    $("#selectfiltrosbusquedas option[value='" + e + "']").prop("selected", true);
  });
  $("#selectfiltrosbusquedas").select2();
  //colocar checked a campos activados
  var campos = campos_activados.split(",");
  for (var i = 0; i < campos.length; i++) {
      $("input[name='"+campos[i]+"']").prop('checked', true);
  }
  //crear lista para ordenar columnas
  var campos_ordenados = columnas_ordenadas.split(",");
  $("#columnasnestable").empty();
  for (var i = 0; i < campos_ordenados.length; i++) {
      var columna =   '<li class="dd-item nestable'+campos_ordenados[i]+'">'+
                          '<div class="dd-handle">'+campos_ordenados[i]+'</div>'+
                          '<input type="hidden" class="inputnestable" value="'+campos_ordenados[i]+'">'+
                      '</li>';
      $("#columnasnestable").append(columna);
  }
}
init();