'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 1000));
}
function asignarfechaactual(){
    /*
    var fechahoy = new Date();
    var dia = ("0" + fechahoy.getDate()).slice(-2);
    var mes = ("0" + (fechahoy.getMonth() + 1)).slice(-2);
    var hoy = fechahoy.getFullYear()+"-"+(mes)+"-"+(dia) ;
    $('#fecha').val(hoy).css('font-size', '12px');
    */
    $.get(ordenes_compra_obtener_fecha_actual_datetimelocal, function(fechadatetimelocal){
        $("#fecha").val(fechadatetimelocal);
    }) 
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
    var serie = $("#serie").val();
    $.get(cuentas_por_pagar_obtener_ultimo_folio,{serie:serie}, function(folio){
        $("#folio").val(folio);
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
    var campos_tabla  = [];
    campos_tabla.push({ 'data':'operaciones', 'name':'operaciones', 'orderable':false, 'searchable':false});
    for (var i = 0; i < campos.length; i++) {
        campos_tabla.push({ 
            'data'    : campos[i],
            'name'  : campos[i],
            'orderable': true,
            'searchable': true
        });
    }
    tabla=$('#tbllistado').DataTable({
        "lengthMenu": [ 10, 50, 100, 250, 500 ],
        "pageLength": 250,
        "sScrollX": "110%",
        "sScrollY": "350px",
        "bScrollCollapse": true,
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
            url: cuentas_por_pagar_obtener,
            data: function (d) {
                d.periodo = $("#periodo").val();
            },
        },
        "createdRow": function( row, data, dataIndex){
            if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
            else{ $(row).addClass(''); }
        },
        columns: campos_tabla,
        "initComplete": function() {
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
//obtener series documento
function obtenerseriesdocumento(){
    ocultarformulario();
    var seriedefault = 'A';
    var tablaseriesdocumento=   '<div class="modal-header bg-red">'+
                                    '<h4 class="modal-title">Series Documento &nbsp;&nbsp; <div class="btn bg-green btn-xs waves-effect" onclick="seleccionarseriedocumento(\''+seriedefault+'\')">Asignar Serie Default (A)</div></h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                        '<table id="tbllistadoseriedocumento" class="tbllistadoseriedocumento table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="customercolor">'+
                                            '<tr>'+
                                                '<th>Operaciones</th>'+
                                                '<th>Serie</th>'+
                                                '<th>Documento</th>'+
                                                '<th>Nombre</th>'+
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
                                '</div>';  
    $("#contenidomodaltablas").html(tablaseriesdocumento);
    $('#tbllistadoseriedocumento').DataTable({
        "lengthMenu": [ 10, 50, 100, 250, 500 ],
        "pageLength": 250,
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
          url: cuentas_por_pagar_obtener_series_documento
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Serie', name: 'Serie' },
            { data: 'Documento', name: 'Documento' },
            { data: 'Nombre', name: 'Nombre' }
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
function seleccionarseriedocumento(Serie){
    $.get(cuentas_por_pagar_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie}, function(folio){
        $("#folio").val(folio);
        $("#serie").val(Serie);
        $("#serietexto").html("Serie: "+Serie);
        mostrarformulario();
    }) 
}
//obtener registros de proveedores
function obtenerproveedores(){
  ocultarformulario();
  var tablaproveedores = '<div class="modal-header bg-red">'+
                      '<h4 class="modal-title">Proveedores</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive" >'+
                                  '<table id="tbllistadoproveedor" class="tbllistadoproveedor table table-bordered table-striped table-hover" style="width:100% !important">'+
                                      '<thead class="customercolor">'+
                                          '<tr>'+
                                              '<th>Operaciones</th>'+
                                              '<th>Numero</th>'+
                                              '<th>Nombre</th>'+
                                              '<th>R.F.C.</th>'+
                                              '<th>Código Postal</th>'+
                                              '<th>Teléfonos</th>'+
                                              '<th>Email</th>'+
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
                    '</div>';
    $("#contenidomodaltablas").html(tablaproveedores);
    $('#tbllistadoproveedor').DataTable({
        "lengthMenu": [ 10, 50, 100, 250, 500 ],
        "pageLength": 250,
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
            url: cuentas_por_pagar_obtener_proveedores,
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Rfc', name: 'Rfc', orderable: false, searchable: false },
            { data: 'CodigoPostal', name: 'CodigoPostal', orderable: false, searchable: false },
            { data: 'Telefonos', name: 'Telefonos', orderable: false, searchable: false },
            { data: 'Email1', name: 'Email1', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoproveedor').DataTable().search( this.value ).draw();
                }
            });
        },
        
    }); 
} 
//obtener registros de almacenes
function obtenerbancos(){
    ocultarformulario();
    var tablabancos = '<div class="modal-header bg-red">'+
                            '<h4 class="modal-title">Bancos</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadobanco" class="tbllistadobanco table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="customercolor">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Banco</th>'+
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
                        '</div>';
      $("#contenidomodaltablas").html(tablabancos);
      $('#tbllistadobanco').DataTable({
            "lengthMenu": [ 10, 50, 100, 250, 500 ],
            "pageLength": 250,
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
                url: cuentas_por_pagar_obtener_bancos,
            },
            columns: [
                { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
                { data: 'Numero', name: 'Numero' },
                { data: 'Nombre', name: 'Nombre' }
            ],
            "initComplete": function() {
                var $buscar = $('div.dataTables_filter input');
                $buscar.unbind();
                $buscar.bind('keyup change', function(e) {
                    if(e.keyCode == 13 || this.value == "") {
                    $('#tbllistadoalmacen').DataTable().search( this.value ).draw();
                    }
                });
            },
          
      }); 
} 
function seleccionarproveedor(Numero, Nombre){
    $('.page-loader-wrapper').css('display', 'block');
    var numeroproveedoranterior = $("#numeroproveedoranterior").val();
    var numeroproveedor = Numero;
    if(numeroproveedoranterior != numeroproveedor){
        $("#numeroproveedor").val(Numero);
        $("#numeroproveedoranterior").val(Numero);
        $("#proveedor").val(Nombre);
        if(Nombre != null){
            $("#textonombreproveedor").html(Nombre.substring(0, 40));
        }
        $("#beneficiario").val(Nombre);
        $.get(cuentas_por_pagar_obtener_compras_proveedor, {Numero:Numero}, function(data){
            $("#tabladetallecuentasporpagar").html(data.filascompras);
            $('.page-loader-wrapper').css('display', 'none');
        });
        mostrarformulario();
    }
}
function seleccionarbanco(Numero, Nombre, ultimatransferencia){
    var numerobancoanterior = $("#numerobancoanterior").val();
    var numerobanco = Numero;
    if(numerobancoanterior != numerobanco){
        $("#numerobanco").val(Numero);
        $("#numerobancoanterior").val(Numero);
        $("#banco").val(Nombre);
        if(Nombre != null){
            $("#textonombrebanco").html(Nombre.substring(0, 40));
        }
        $("#transferencia").val(parseInt(ultimatransferencia)+parseInt(1));
        mostrarformulario();
    }
}
//obtener por numero
function obtenerproveedorpornumero(){
    $('.page-loader-wrapper').css('display', 'block');
    var numeroproveedoranterior = $("#numeroproveedoranterior").val();
    var numeroproveedor = $("#numeroproveedor").val();
    if(numeroproveedoranterior != numeroproveedor){
        if($("#numeroproveedor").parsley().isValid()){
            $.get(cuentas_por_pagar_obtener_proveedor_por_numero, {numeroproveedor:numeroproveedor}, function(data){
                $("#numeroproveedor").val(data.numero);
                $("#numeroproveedoranterior").val(data.numero);
                $("#proveedor").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombreproveedor").html(data.nombre.substring(0, 40));
                }
                $("#beneficiario").val(data.nombre);
                var Numero = data.numero;
                $.get(cuentas_por_pagar_obtener_compras_proveedor, {Numero:Numero}, function(data){
                    $("#tabladetallecuentasporpagar").html(data.filascompras);
                    $('.page-loader-wrapper').css('display', 'none');
                });
                mostrarformulario();
            }) 
        }
    }
}
//regresar numero
function regresarnumeroproveedor(){
    var numeroproveedoranterior = $("#numeroproveedoranterior").val();
    $("#numeroproveedor").val(numeroproveedoranterior);
}
//obtener por numero
function obtenerbancopornumero(){
    var numerobancoanterior = $("#numerobancoanterior").val();
    var numerobanco = $("#numerobanco").val();
    if(numerobancoanterior != numerobanco){
        if($("#numerobanco").parsley().isValid()){
            $.get(cuentas_por_pagar_obtener_banco_por_numero, {numerobanco:numerobanco}, function(data){
                $("#numerobanco").val(data.numero);
                $("#numerobancoanterior").val(data.numero);
                $("#banco").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombrebanco").html(data.nombre.substring(0, 40));
                }
                $("#transferencia").val(parseInt(data.transferencia)+parseInt(1));
                mostrarformulario();
            }) 
        }
    }
}
//regresar numero
function regresarnumerobanco(){
    var numerobancoanterior = $("#numerobancoanterior").val();
    $("#numerobanco").val(numerobancoanterior);
}
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Cuentas por Pagar');
  mostrarmodalformulario('ALTA');
  mostrarformulario();
  //formulario alta
  var tabs ='<div class="col-md-12">'+    
                '<div class="row">'+ 
                    '<div class="col-md-2">'+ 
                        '<label>Pago <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+ 
                        '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+ 
                        '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+ 
                    '</div>'+   
                    '<div class="col-md-4">'+ 
                        '<label>Proveedor <span class="label label-danger" id="textonombreproveedor"></span></label>'+ 
                        '<table class="col-md-12">'+ 
                            '<tr>'+ 
                                '<td>'+ 
                                    '<div class="btn bg-blue waves-effect" id="btnobtenerproveedores" onclick="obtenerproveedores()">Seleccionar</div>'+ 
                                '</td>'+ 
                                '<td>'+ 
                                    '<div class="form-line">'+ 
                                        '<input type="text" class="form-control" name="numeroproveedor" id="numeroproveedor" required data-parsley-type="integer">'+ 
                                        '<input type="hidden" class="form-control" name="numeroproveedoranterior" id="numeroproveedoranterior" required data-parsley-type="integer">'+ 
                                        '<input type="hidden" class="form-control" name="proveedor" id="proveedor" required readonly>'+ 
                                    '</div>'+ 
                                '</td>'+ 
                            '</tr>'+     
                        '</table>'+ 
                    '</div>'+ 
                    '<div class="col-md-4">'+ 
                        '<label>Banco <span class="label label-danger" id="textonombrebanco"></span></label>'+ 
                        '<table class="col-md-12">'+ 
                            '<tr>'+ 
                                '<td>'+ 
                                    '<div class="btn bg-blue waves-effect" onclick="obtenerbancos()">Seleccionar</div>'+ 
                                '</td>'+ 
                                '<td>'+    
                                    '<div class="form-line">'+ 
                                        '<input type="text" class="form-control" name="numerobanco" id="numerobanco" required data-parsley-type="integer">'+ 
                                        '<input type="hidden" class="form-control" name="numerobancoanterior" id="numerobancoanterior" required data-parsley-type="integer">'+ 
                                        '<input type="hidden" class="form-control" name="banco" id="banco" required readonly>'+ 
                                    '</div>'+ 
                                '</td>'+     
                            '</tr>'+   
                        '</table>'+ 
                    '</div>'+    
                    '<div class="col-md-2">'+ 
                        '<label>Fecha</label>'+
                        '<input type="datetime-local" class="form-control" name="fecha" id="fecha" onchange="validasolomesactual();" required>'+ 
                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+ 
                    '</div>'+ 
                    '</div>'+ 
                    '<div class="row">'+ 
                    '<div class="col-md-4">'+ 
                        '<label>Transferencia</label>'+ 
                        '<input type="text" class="form-control" name="transferencia" id="transferencia" value="0" required data-parsley-type="integer">'+ 
                    '</div>'+ 
                    '<div class="col-md-4">'+ 
                        '<label>Cheque</label>'+ 
                        '<input type="text" class="form-control" name="cheque" id="cheque" value="0" required data-parsley-type="integer">'+ 
                    '</div>'+ 
                    '<div class="col-md-4">'+ 
                        '<label>Beneficiario</label>'+ 
                        '<input type="text" class="form-control" name="beneficiario" id="beneficiario"  required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+ 
                    '</div>'+ 
                    '</div>'+ 
                    '<div class="row">'+ 
                    '<div class="col-md-6">'+ 
                        '<label>Cuenta a la que se Depositó</label>'+ 
                        '<input type="text" class="form-control" name="cuentadeposito" id="cuentadeposito" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+ 
                    '</div>'+ 
                    '<div class="col-md-6">'+ 
                        '<label>Anotación</label>'+ 
                        '<textarea class="form-control" name="anotacion" id="anotacion" rows="2" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+ 
                    '</div>'+ 
                '</div>'+ 
            '</div>'+ 
            '<div class="col-md-12">'+
                '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#productostab" data-toggle="tab">Productos</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                        '<div class="row">'+
                            '<div class="col-md-12 table-responsive cabecerafija" style="height: 225px; overflow-y: scroll;padding: 0px 0px;">'+
                                '<table class="table  table-bordered">'+
                                    '<thead class="customercolor">'+
                                        '<tr>'+
                                          '<th class="customercolor">#</th>'+
                                          '<th class="customercolor">Compra</th>'+
                                          '<th class="customercolor">Factura</th>'+
                                          '<th class="customercolor">Fecha</th>'+
                                          '<th class="customercolor">Plazo</th>'+
                                          '<th class="customercolor">Vence</th>'+
                                          '<th class="customercolor">Total $</th>'+
                                          '<th class="customercolor">Abonos $</th>'+
                                          '<th class="customercolor">Notas Crédito $</th>'+
                                          '<th class="customercolortheadth">Abono</th>'+
                                          '<th class="customercolor">Saldo $</th>'+
                                          '<th class="customercolor">Contrarecibo</th>'+
                                        '</tr>'+
                                    '</thead>'+
                                    '<tbody id="tabladetallecuentasporpagar">'+           
                                    '</tbody>'+
                                '</table>'+
                            '</div>'+
                        '</div>'+ 
                        '<div class="row">'+
                          '<div class="col-md-6">'+   
                            '<label>Contrarecibo</label>'+
                            '<input type="text" class="form-control" name="contrarecibo" id="contrarecibo" onkeyup="tipoLetra(this);" >'+
                          '</div>'+ 
                          '<div class="col-md-3 col-md-offset-3">'+
                                '<table class="table table-striped table-hover">'+
                                    '<tr>'+
                                        '<td class="tdmod">Total</td>'+
                                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                '</table>'+
                            '</div>'+
                        '</div>'+   
                    '</div>'+ 
                '</div>'+ 
            '</div>';
    $("#tabsform").html(tabs);
    obtenultimonumero();
    asignarfechaactual();
    $("#serie").val(serieusuario);
    $("#serietexto").html("Serie: "+serieusuario);
    //busquedas seleccion
    //activar busqueda para proveedores
    $('#numeroproveedor').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerproveedorpornumero();
        }
    });
    //regresar numero proveedor
    $('#numeroproveedor').on('change', function(e) {
        regresarnumeroproveedor();
    });
    //activar busqueda para bancos
    $('#numerobanco').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerbancopornumero();
        }
    });
    //regresar numero banco
    $('#numerobanco').on('change', function(e) {
        regresarnumerobanco();
    });
}
function calcularnuevosaldo(fila){
    var cuentaFilas = 0;
    $("tr.filascompras").each(function () {
        if(fila === cuentaFilas){   
            //descuento en pesos de la partida
            var saldocomprainicial = $('.saldocomprainicial', this).val();
            var abonocompra = $('.abonocompra', this).val();
            if(parseFloat(abonocompra) > parseFloat(saldocomprainicial)){
                $('.abonocompra', this).val(number_format(round(0, numerodecimales), numerodecimales, '.', ''))
                calcularnuevosaldo(fila);
            }else{
                var nuevosaldo  =  new Decimal(saldocomprainicial).minus(abonocompra);
                $('.saldocompra', this).val(number_format(round(nuevosaldo, numerodecimales), numerodecimales, '.', ''));
                calculartotalcuentaporpagar();
            }
        }  
        cuentaFilas++;
    });
}
function calculartotalcuentaporpagar(){
    var total = 0;
    $("tr.filascompras").each(function () {
        total = new Decimal(total).plus($('.abonocompra', this).val());
    }); 
    $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
    e.preventDefault();
    var form = $("#formparsley");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        enviarfilasutilizadas().then(resultado=>{
            var formData = new FormData($("#formparsley")[0]);
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url:cuentas_por_pagar_guardar,
                type: "post",
                dataType: "html",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success:function(data){
                    msj_datosguardadoscorrectamente();
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
        })
    }else{
        msjfaltandatosporcapturar();
    }
    //validar formulario
    form.parsley().validate();
});
//funcion asincrona para buscar existencias de la partida
function enviarfilasutilizadas(){
    return new Promise((ejecuta)=>{
        setTimeout(function(){ 
            var cuentaFilas = 0;
            $("tr.filascompras").each(function () {
                var abonocompra = $(".abonocompra", this).val();
                if(parseFloat(abonocompra) == parseFloat(0) || parseFloat(abonocompra) < parseFloat(0)){
                    $("#filacompra"+cuentaFilas).remove();
                }
                cuentaFilas++;
            }); 
            var resultado = true;
            return ejecuta(resultado);
        },1000);
    })
}
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(cxpdesactivar){
    $.get(cuentas_por_pagar_comprobar_baja,{cxpdesactivar:cxpdesactivar}, function(data){
        console.log(data);
        if(data.Status == 'BAJA'){
            $("#cxpdesactivar").val(0);
            $("#textomodaldesactivar").html('Error, esta cuenta por pagar ya fue dado de baja');
            $("#divmotivobaja").hide();
            $("#btnbaja").hide();
            $('#estatusregistro').modal('show');
        }else{ 
            if(data.resultadofechas != ''){
                $("#cxpdesactivar").val(0);
                $("#textomodaldesactivar").html('Error solo se pueden dar de baja las cuentas por pagar del mes actual, fecha de la cuenta por pagar: ' + data.resultadofechas);
                $("#divmotivobaja").hide();
                $("#btnbaja").hide();
                $('#estatusregistro').modal('show');
            }else{
                $("#cxpdesactivar").val(cxpdesactivar);
                $("#divmotivobaja").show();
                $("#btnbaja").show();
                $("#textomodaldesactivar").html('Estas seguro de dar de baja la cuenta por pagar? No'+cxpdesactivar);
                $('#estatusregistro').modal('show');
            }
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
      url:cuentas_por_pagar_baja,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#estatusregistro').modal('hide');
        msj_datosguardadoscorrectamente();
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
function obtenerdatos(cxpmodificar){
  $("#titulomodal").html('Modificación Cuenta por Pagar');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(cuentas_por_pagar_obtener_cuenta_por_pagar,{cxpmodificar:cxpmodificar },function(data){
    //formulario modificacion
    var tabs =  '<div class="col-md-12">'+    
                    '<div class="row">'+ 
                        '<div class="col-md-2">'+ 
                            '<label>Pago <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b></label>'+ 
                            '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+ 
                            '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+ 
                        '</div>'+   
                        '<div class="col-md-4">'+ 
                            '<label>Proveedor <span class="label label-danger" id="textonombreproveedor"></span></label>'+ 
                            '<table class="col-md-12">'+ 
                                '<tr>'+ 
                                    '<td hidden>'+ 
                                        '<div class="btn bg-blue waves-effect" id="btnobtenerproveedores" onclick="obtenerproveedores()">Seleccionar</div>'+ 
                                    '</td>'+ 
                                    '<td>'+ 
                                        '<div class="form-line">'+ 
                                            '<input type="text" class="form-control" name="numeroproveedor" id="numeroproveedor" required data-parsley-type="integer">'+ 
                                            '<input type="hidden" class="form-control" name="numeroproveedoranterior" id="numeroproveedoranterior" required data-parsley-type="integer">'+ 
                                            '<input type="hidden" class="form-control" name="proveedor" id="proveedor" required readonly>'+ 
                                        '</div>'+ 
                                    '</td>'+ 
                                '</tr>'+     
                            '</table>'+ 
                        '</div>'+ 
                        '<div class="col-md-4">'+ 
                            '<label>Banco <span class="label label-danger" id="textonombrebanco"></span></label>'+ 
                            '<table class="col-md-12">'+ 
                                '<tr>'+ 
                                    '<td>'+ 
                                        '<div class="btn bg-blue waves-effect" onclick="obtenerbancos()">Seleccionar</div>'+ 
                                    '</td>'+ 
                                    '<td>'+    
                                        '<div class="form-line">'+ 
                                            '<input type="text" class="form-control" name="numerobanco" id="numerobanco" required data-parsley-type="integer">'+ 
                                            '<input type="hidden" class="form-control" name="numerobancoanterior" id="numerobancoanterior" required data-parsley-type="integer">'+ 
                                            '<input type="hidden" class="form-control" name="banco" id="banco" required readonly>'+ 
                                        '</div>'+ 
                                    '</td>'+     
                                '</tr>'+   
                            '</table>'+ 
                        '</div>'+    
                        '<div class="col-md-2">'+ 
                            '<label>Fecha</label>'+
                            '<input type="datetime-local" class="form-control" name="fecha" id="fecha" onchange="validasolomesactual();" required>'+ 
                            '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+ 
                        '</div>'+ 
                        '</div>'+ 
                        '<div class="row">'+ 
                        '<div class="col-md-4">'+ 
                            '<label>Transferencia</label>'+ 
                            '<input type="text" class="form-control" name="transferencia" id="transferencia" value="0" required data-parsley-type="integer">'+ 
                        '</div>'+ 
                        '<div class="col-md-4">'+ 
                            '<label>Cheque</label>'+ 
                            '<input type="text" class="form-control" name="cheque" id="cheque" value="0" required data-parsley-type="integer">'+ 
                        '</div>'+ 
                        '<div class="col-md-4">'+ 
                            '<label>Beneficiario</label>'+ 
                            '<input type="text" class="form-control" name="beneficiario" id="beneficiario"  required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+ 
                        '</div>'+ 
                        '</div>'+ 
                        '<div class="row">'+ 
                        '<div class="col-md-6">'+ 
                            '<label>Cuenta a la que se Depositó</label>'+ 
                            '<input type="text" class="form-control" name="cuentadeposito" id="cuentadeposito" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+ 
                        '</div>'+ 
                        '<div class="col-md-6">'+ 
                            '<label>Anotación</label>'+ 
                            '<textarea class="form-control" name="anotacion" id="anotacion" rows="2" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+ 
                        '</div>'+ 
                    '</div>'+ 
                '</div>'+ 
                '<div class="col-md-12">'+
                    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                        '<li role="presentation" class="active">'+
                            '<a href="#productostab" data-toggle="tab">Productos</a>'+
                        '</li>'+
                    '</ul>'+
                    '<div class="tab-content">'+
                        '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                            '<div class="row">'+
                                '<div class="col-md-12 table-responsive cabecerafija" style="height: 225px;overflow-y: scroll;padding: 0px 0px;">'+
                                    '<table class="table table-bordered">'+
                                        '<thead class="customercolor">'+
                                            '<tr>'+
                                            '<th class="customercolor">#</th>'+
                                            '<th class="customercolor">Compra</th>'+
                                            '<th class="customercolor">Factura</th>'+
                                            '<th class="customercolor">Fecha</th>'+
                                            '<th class="customercolor">Plazo</th>'+
                                            '<th class="customercolor">Vence</th>'+
                                            '<th class="customercolor">Total $</th>'+
                                            '<th class="customercolor">Abonos $</th>'+
                                            '<th class="customercolor">Notas Crédito $</th>'+
                                            '<th class="customercolortheadth">Abono</th>'+
                                            '<th class="customercolor">Saldo $</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody id="tabladetallecuentasporpagar">'+           
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+ 
                            '<div class="row">'+
                            '<div class="col-md-6">'+   
                                '<label>Contrarecibo</label>'+
                                '<input type="text" class="form-control" name="contrarecibo" id="contrarecibo" onkeyup="tipoLetra(this);" >'+
                            '</div>'+ 
                            '<div class="col-md-3 col-md-offset-3">'+
                                    '<table class="table table-striped table-hover">'+
                                        '<tr>'+
                                            '<td class="tdmod">Total</td>'+
                                            '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                        '</tr>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+   
                        '</div>'+ 
                    '</div>'+ 
                '</div>';
    $("#tabsform").html(tabs);        
    $("#periodohoy").val(data.CuentaXPagar.Periodo);
    $("#folio").val(data.CuentaXPagar.Folio);
    $("#serie").val(data.CuentaXPagar.Serie);
    $("#serietexto").html("Serie: "+data.CuentaXPagar.Serie);
    $("#numeroproveedor").val(data.proveedor.Numero);
    $("#numeroproveedoranterior").val(data.proveedor.Numero);
    $("#proveedor").val(data.proveedor.Nombre);
    if(data.proveedor.Nombre != null){
        $("#textonombreproveedor").html(data.proveedor.Nombre.substring(0,40));
    }
    $("#numerobanco").val(data.banco.Numero);
    $("#numerobancoanterior").val(data.banco.Numero);
    $("#banco").val(data.banco.Nombre);
    if(data.banco.Nombre != null){
        $("#textonombrebanco").html(data.banco.Nombre.substring(0, 40));
    }
    $("#fecha").val(data.fecha);
    $("#transferencia").val(data.CuentaXPagar.Transferencia)
    $("#cheque").val(data.CuentaXPagar.Cheque);
    $("#beneficiario").val(data.CuentaXPagar.Beneficiario);
    $("#cuentadeposito").val(data.CuentaXPagar.CuentaDeposito);
    $("#anotacion").val(data.CuentaXPagar.Anotacion);
    $("#total").val(data.abonototal);
    //tabs precios productos
    $("#tabladetallecuentasporpagar").html(data.filasdetallecuentasporpagar);
    //busquedas seleccion
    //regresar numero proveedor
    $('#numeroproveedor').on('change', function(e) {
        regresarnumeroproveedor();
    });
    //activar busqueda para bancos
    $('#numerobanco').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerbancopornumero();
        }
    });
    //regresar numero banco
    $('#numerobanco').on('change', function(e) {
        regresarnumerobanco();
    });
    mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
    $('.page-loader-wrapper').css('display', 'none');
  }).fail( function() {
    msj_errorajax();    
    $('.page-loader-wrapper').css('display', 'none');
  })
}
//guardar modificación
$("#btnGuardarModificacion").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:cuentas_por_pagar_guardar_modificacion,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                msj_datosguardadoscorrectamente();
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
        msjfaltandatosporcapturar();
    }
    //validar formulario
    form.parsley().validate();
});
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento){
    $.get(cuentas_por_pagar_obtener_datos_envio_email,{documento:documento}, function(data){
      $("#textomodalenviarpdfemail").html("Enviar email Cuenta Por Pagar No." + documento);
      $("#emaildocumento").val(documento);
      $("#emailde").val(data.emailde);
      $("#emailpara").val(data.emailpara);
      $("#emailasunto").val("CUENTA POR PAGAR NO. " + documento +" DE USADOS TRACTOCAMIONES Y PARTES REFACCIONARIAS SA DE CV");
      $("#modalenviarpdfemail").modal('show');
    })   
}
//enviar documento pdf por email
$("#btnenviarpdfemail").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formenviarpdfemail")[0]);
    var form = $("#formenviarpdfemail");
    if (form.parsley().isValid()){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:cuentas_por_pagar_enviar_pdfs_email,
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data){
          msj_documentoenviadoporemailcorrectamente();
          $("#modalenviarpdfemail").modal('hide');
          $('.page-loader-wrapper').css('display', 'none');
        },
        error:function(data){
          if(data.status == 403){
            msj_errorenpermisos();
          }else{
            msj_errorajax();
          }
          $("#modalenviarpdfemail").modal('hide');
          $('.page-loader-wrapper').css('display', 'none');
        }
      })
    }else{
      form.parsley().validate();
    }
});
//hacer busqueda de folio para exportacion en pdf
function relistarbuscarstringlike(){
    var tabla = $('#tablafoliosencontrados').DataTable();
    tabla.ajax.reload();
}
function buscarstringlike(){
    var columnastablafoliosencontrados =    '<tr>'+
                                                '<th><div style="width:80px !important;">Generar Documento en PDF</div></th>'+
                                                '<th>Pago</th>'+
                                                '<th>Proveedor</th>'+
                                                '<th>Abono</th>'+
                                                '<th>Status</th>'+
                                            '</tr>';
    $("#columnastablafoliosencontrados").html(columnastablafoliosencontrados);
    tabla=$('#tablafoliosencontrados').DataTable({
        "paging":   false,
        "ordering": false,
        "info":     false,
        "searching": false,
        processing: true,
        serverSide: true,
        ajax: {
            url: cuentas_por_pagar_buscar_folio_string_like,
            data: function (d) {
                d.string = $("#buscarfolio").val();
            },
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Pago', name: 'Pago' },
            { data: 'Proveedor', name: 'Proveedor', orderable: false, searchable: false },
            { data: 'Abono', name: 'Abono', orderable: false, searchable: false  },
            { data: 'Status', name: 'Status', orderable: false, searchable: false  },
        ],
    });
  }
//configurar tabla
function configurar_tabla(){
    //formulario configuracion tabla
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#tabcamposamostrar" data-toggle="tab">Campos a mostrar</a>'+
                    '</li>'+
                    '<li role="presentation">'+
                        '<a href="#tabordenarcolumnas" data-toggle="tab">Ordenar Columnas</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="tabcamposamostrar">'+
                        '<div class="row">'+
                            '<div class="col-md-6">'+
                                '<div class="col-md-12 form-check">'+
                                    '<label>DATOS CUENTA POR PAGAR</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Pago" id="idPago" class="filled-in datotabla" value="Pago" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                    '<label for="idPago">Pago</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Status" id="idStatus" class="filled-in datotabla" value="Status" onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                    '<label for="idStatus">Status</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Fecha" id="idFecha" class="filled-in datotabla" value="Fecha" readonly onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFecha">Fecha</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Proveedor" id="idProveedor" class="filled-in datotabla" value="Proveedor" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idProveedor">Proveedor</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Transferencia" id="idTransferencia" class="filled-in datotabla" value="Transferencia" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idTransferencia">Transferencia</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Abono" id="idAbono" class="filled-in datotabla" value="Abono" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idAbono">Abono</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="MotivoBaja" id="idMotivoBaja" class="filled-in datotabla" value="MotivoBaja" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idMotivoBaja">MotivoBaja</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Periodo" id="idPeriodo" class="filled-in datotabla" value="Periodo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idPeriodo">Periodo</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Folio" id="idFolio" class="filled-in datotabla" value="Folio" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFolio">Folio</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+    
                                    '<input type="checkbox" name="Serie" id="idSerie" class="filled-in datotabla" value="Serie" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idSerie">Serie</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Banco" id="idBanco" class="filled-in datotabla" value="Banco" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idBanco">Banco</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Cheque" id="idCheque" class="filled-in datotabla" value="Cheque" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idCheque">Cheque</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Beneficiario" id="idBeneficiario" class="filled-in datotabla" value="Beneficiario" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idBeneficiario">Beneficiario</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="CuentaDeposito" id="idCuentaDeposito" class="filled-in datotabla" value="CuentaDeposito" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idCuentaDeposito">CuentaDeposito</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Anotacion" id="idAnotacion" class="filled-in datotabla" value="Anotacion" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idAnotacion">Anotacion</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Equipo" id="idEquipo" class="filled-in datotabla" value="Equipo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idEquipo">Equipo</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Usuario" id="idUsuario" class="filled-in datotabla" value="Usuario" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idUsuario">Usuario</label>'+
                                '</div>'+
                                '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                                '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<div class="col-md-12 form-check">'+
                                    '<label>DATOS BANCO</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="NumeroBanco" id="idNumeroBanco" class="filled-in datotabla" value="NumeroBanco"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idNumeroBanco">NumeroBanco</label>'+  
                                '</div>'+
                                '<div class="col-md-4 form-check">'+  
                                    '<input type="checkbox" name="NombreBanco" id="idNombreBanco" class="filled-in datotabla" value="NombreBanco"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idNombreBanco">NombreBanco</label>'+ 
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="CuentaBanco" id="idCuentaBanco" class="filled-in datotabla" value="CuentaBanco"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idCuentaBanco">CuentaBanco</label>'+ 
                                '</div>'+
                                '<div class="col-md-12 form-check">'+
                                    '<label>DATOS PROVEEDOR</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="NumeroProveedor" id="idNumeroProveedor" class="filled-in datotabla" value="NumeroProveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idNumeroProveedor">NumeroProveedor</label>'+  
                                '</div>'+
                                '<div class="col-md-4 form-check">'+  
                                    '<input type="checkbox" name="NombreProveedor" id="idNombreProveedor" class="filled-in datotabla" value="NombreProveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idNombreProveedor">NombreProveedor</label>'+ 
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="RfcProveedor" id="idRfcProveedor" class="filled-in datotabla" value="RfcProveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idRfcProveedor">RfcProveedor</label>'+ 
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="CodigoPostalProveedor" id="idCodigoPostalProveedor" class="filled-in datotabla" value="CodigoPostalProveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idCodigoPostalProveedor">CodigoPostalProveedor</label>'+  
                                '</div>'+
                                '<div class="col-md-4 form-check">'+  
                                    '<input type="checkbox" name="PlazoProveedor" id="idPlazoProveedor" class="filled-in datotabla" value="PlazoProveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idPlazoProveedor">PlazoProveedor</label>'+ 
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="TelefonosProveedor" id="idTelefonosProveedor" class="filled-in datotabla" value="TelefonosProveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idTelefonosProveedor">TelefonosProveedor</label>'+ 
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Email1Proveedor" id="idEmail1Proveedor" class="filled-in datotabla" value="Email1Proveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idEmail1Proveedor">Email1Proveedor</label>'+  
                                '</div>'+                                
                            '</div>'+
                        '</div>'+
                    '</div>'+ 
                    '<div role="tabpanel" class="tab-pane fade" id="tabordenarcolumnas">'+
                        '<div class="row">'+
                            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">'+
                                '<div class="card">'+
                                    '<div class="header">'+
                                        '<h2>'+
                                            'Ordenar Columnas'+
                                            '<small>Ordena las columnas arrastrándolas hacia arriba o hacia abajo. </small>'+
                                        '</h2>'+
                                    '</div>'+
                                    '<div class="body">'+
                                        '<div class="clearfix m-b-20">'+
                                            '<div class="dd" onchange="ordenarcolumnas()">'+
                                                '<ol class="dd-list" id="columnasnestable">'+
                                                '</ol>'+
                                            '</div>'+
                                        '</div>'+
                                        '<input type="hidden" id="string_datos_ordenamiento_columnas" name="string_datos_ordenamiento_columnas" class="form-control" required>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+      
                    '</div>'+
                '</div>';
    $("#tabsconfigurartabla").html(tabs);
    $("#string_datos_ordenamiento_columnas").val(columnas_ordenadas);
    $("#string_datos_tabla_true").val(campos_activados);
    $("#string_datos_tabla_false").val(campos_desactivados);
    $("#modalconfigurartabla").modal('show');
    $("#titulomodalconfiguraciontabla").html("Configuración de la tabla");
    $('.dd').nestable();
    //campos activados
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