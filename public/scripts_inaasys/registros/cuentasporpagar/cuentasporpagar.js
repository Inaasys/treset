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
    //modificacion al dar doble click
    $('#tbllistado tbody').on('dblclick', 'tr', function () {
      var data = tabla.row( this ).data();
      obtenerdatos(data.Pago);
    });
}
//obtener series documento
function obtenerseriesdocumento(){
    ocultarformulario();
    var seriedefault = 'A';
    var tablaseriesdocumento=   '<div class="modal-header '+background_forms_and_modals+'">'+
                                    '<h4 class="modal-title">Series Documento &nbsp;&nbsp; <div class="btn bg-green btn-xs waves-effect" onclick="seleccionarseriedocumento(\''+seriedefault+'\')">Asignar Serie Default (A)</div></h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                        '<table id="tbllistadoseriedocumento" class="tbllistadoseriedocumento table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
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
    var tserdoc = $('#tbllistadoseriedocumento').DataTable({
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
    //seleccionar registro al dar doble click
    $('#tbllistadoseriedocumento tbody').on('dblclick', 'tr', function () {
      var data = tserdoc.row( this ).data();
      seleccionarseriedocumento(data.Serie);
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
  var tablaproveedores = '<div class="modal-header '+background_forms_and_modals+'">'+
                      '<h4 class="modal-title">Proveedores</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive" >'+
                                  '<table id="tbllistadoproveedor" class="tbllistadoproveedor table table-bordered table-striped table-hover" style="width:100% !important">'+
                                      '<thead class="'+background_tables+'">'+
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
    var tprov = $('#tbllistadoproveedor').DataTable({
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
    //seleccionar registro al dar doble click
    $('#tbllistadoproveedor tbody').on('dblclick', 'tr', function () {
      var data = tprov.row( this ).data();
      seleccionarproveedor(data.Numero, data.Nombre);
    });
} 
//obtener registros de almacenes
function obtenerbancos(){
    ocultarformulario();
    var tablabancos = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Bancos</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadobanco" class="tbllistadobanco table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
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
      var tban = $('#tbllistadobanco').DataTable({
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
                    $('#tbllistadobanco').DataTable().search( this.value ).draw();
                    }
                });
            },
      });  
      //seleccionar registro al dar doble click
      $('#tbllistadobanco tbody').on('dblclick', 'tr', function () {
        var data = tban.row( this ).data();
        $.get(cuentas_por_pagar_obtener_ultima_transferencia, {Numero:data.Numero}, function(ultimatransferencia){
            seleccionarbanco(data.Numero, data.Nombre, ultimatransferencia[0].Transferencia);
        });
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
    var numeroproveedoranterior = $("#numeroproveedoranterior").val();
    var numeroproveedor = $("#numeroproveedor").val();
    if(numeroproveedoranterior != numeroproveedor){
        if($("#numeroproveedor").parsley().isValid()){
            $('.page-loader-wrapper').css('display', 'block');
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

//saldar compra
function saldarcompra(fila){
    var abonocompra = $("#filacompra"+fila+" .abonocompra").val();
    var saldocompra = $("#filacompra"+fila+" .saldocompra").val();
    var nuevosaldo = new Decimal(abonocompra).plus(saldocompra);
    $("#filacompra"+fila+" .abonocompra").val(number_format(round(nuevosaldo, numerodecimales), numerodecimales, '.', ''));
    calcularnuevosaldo(fila);
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
                        '<input type="text" class="form-control inputnext" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+ 
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
                                        '<input type="text" class="form-control inputnext" name="numeroproveedor" id="numeroproveedor" required data-parsley-type="integer" autocomplete="off">'+ 
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
                                        '<input type="text" class="form-control inputnext" name="numerobanco" id="numerobanco" required data-parsley-type="integer" autocomplete="off">'+ 
                                        '<input type="hidden" class="form-control" name="numerobancoanterior" id="numerobancoanterior" required data-parsley-type="integer">'+ 
                                        '<input type="hidden" class="form-control" name="banco" id="banco" required readonly>'+ 
                                    '</div>'+ 
                                '</td>'+     
                            '</tr>'+   
                        '</table>'+ 
                    '</div>'+    
                    '<div class="col-md-2">'+ 
                        '<label>Fecha</label>'+
                        '<input type="datetime-local" class="form-control inputnext" name="fecha" id="fecha" onchange="validasolomesactual();" required>'+ 
                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+ 
                    '</div>'+ 
                    '</div>'+ 
                    '<div class="row">'+ 
                    '<div class="col-md-4">'+ 
                        '<label>Transferencia</label>'+ 
                        '<input type="text" class="form-control inputnext" name="transferencia" id="transferencia" value="0" required data-parsley-type="integer" autocomplete="off">'+ 
                    '</div>'+ 
                    '<div class="col-md-4">'+ 
                        '<label>Cheque</label>'+ 
                        '<input type="text" class="form-control inputnext" name="cheque" id="cheque" value="0" required data-parsley-type="integer" autocomplete="off">'+ 
                    '</div>'+ 
                    '<div class="col-md-4">'+ 
                        '<label>Beneficiario</label>'+ 
                        '<input type="text" class="form-control inputnext" name="beneficiario" id="beneficiario"  required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);" autocomplete="off">'+ 
                    '</div>'+ 
                    '</div>'+ 
                    '<div class="row">'+ 
                    '<div class="col-md-6">'+ 
                        '<label>Cuenta a la que se Depositó</label>'+ 
                        '<input type="text" class="form-control inputnext" name="cuentadeposito" id="cuentadeposito" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" autocomplete="off">'+ 
                    '</div>'+ 
                    '<div class="col-md-6">'+ 
                        '<label>Anotación</label>'+ 
                        '<textarea class="form-control inputnext" name="anotacion" id="anotacion" rows="2" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+ 
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
                                    '<thead class="'+background_tables+'">'+
                                        '<tr>'+
                                          '<th class="'+background_tables+'">#</th>'+
                                          '<th class="'+background_tables+'">Compra</th>'+
                                          '<th class="'+background_tables+'">Factura</th>'+
                                          '<th class="'+background_tables+'">Fecha</th>'+
                                          '<th class="'+background_tables+'">Plazo</th>'+
                                          '<th class="'+background_tables+'">Vence</th>'+
                                          '<th class="'+background_tables+'">Total $</th>'+
                                          '<th class="'+background_tables+'">Abonos $</th>'+
                                          '<th class="'+background_tables+'">Notas Crédito $</th>'+
                                          '<th class="customercolortheadth">Abono</th>'+
                                          '<th class="'+background_tables+'">Saldo $ (DOBLE CLICK)</th>'+
                                          '<th class="'+background_tables+'">Contrarecibo</th>'+
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
                            '<input type="text" class="form-control inputnext" name="contrarecibo" id="contrarecibo" onkeyup="tipoLetra(this);" autocomplete="off" >'+
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
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnext").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnext");          
        $(".inputnext").eq(index + 1).focus().select(); 
      }
    });
    setTimeout(function(){$("#folio").focus();},500);
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
                $("#motivobaja").val("");
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
  $('.page-loader-wrapper').css('display', 'block');
  $.get(cuentas_por_pagar_obtener_cuenta_por_pagar,{cxpmodificar:cxpmodificar },function(data){
    $("#titulomodal").html('Modificación Cuenta por Pagar --- STATUS : ' + data.CuentaXPagar.Status);
    //formulario modificacion
    var tabs =  '<div class="col-md-12">'+    
                    '<div class="row">'+ 
                        '<div class="col-md-2">'+ 
                            '<label>Pago <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b></label>'+ 
                            '<input type="text" class="form-control inputnext" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+ 
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
                                            '<input type="text" class="form-control inputnext" name="numeroproveedor" id="numeroproveedor" required data-parsley-type="integer" autocomplete="off">'+ 
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
                                            '<input type="text" class="form-control inputnext" name="numerobanco" id="numerobanco" required data-parsley-type="integer" autocomplete="off">'+ 
                                            '<input type="hidden" class="form-control" name="numerobancoanterior" id="numerobancoanterior" required data-parsley-type="integer">'+ 
                                            '<input type="hidden" class="form-control" name="banco" id="banco" required readonly>'+ 
                                        '</div>'+ 
                                    '</td>'+     
                                '</tr>'+   
                            '</table>'+ 
                        '</div>'+    
                        '<div class="col-md-2">'+ 
                            '<label>Fecha</label>'+
                            '<input type="datetime-local" class="form-control inputnext" name="fecha" id="fecha" onchange="validasolomesactual();" required>'+ 
                            '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+ 
                        '</div>'+ 
                        '</div>'+ 
                        '<div class="row">'+ 
                        '<div class="col-md-4">'+ 
                            '<label>Transferencia</label>'+ 
                            '<input type="text" class="form-control inputnext" name="transferencia" id="transferencia" value="0" required data-parsley-type="integer" autocomplete="off">'+ 
                        '</div>'+ 
                        '<div class="col-md-4">'+ 
                            '<label>Cheque</label>'+ 
                            '<input type="text" class="form-control inputnext" name="cheque" id="cheque" value="0" required data-parsley-type="integer" autocomplete="off">'+ 
                        '</div>'+ 
                        '<div class="col-md-4">'+ 
                            '<label>Beneficiario</label>'+ 
                            '<input type="text" class="form-control inputnext" name="beneficiario" id="beneficiario"  required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);" autocomplete="off">'+ 
                        '</div>'+ 
                        '</div>'+ 
                        '<div class="row">'+ 
                        '<div class="col-md-6">'+ 
                            '<label>Cuenta a la que se Depositó</label>'+ 
                            '<input type="text" class="form-control inputnext" name="cuentadeposito" id="cuentadeposito" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" autocomplete="off">'+ 
                        '</div>'+ 
                        '<div class="col-md-6">'+ 
                            '<label>Anotación</label>'+ 
                            '<textarea class="form-control inputnext" name="anotacion" id="anotacion" rows="2" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+ 
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
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                            '<th class="'+background_tables+'">#</th>'+
                                            '<th class="'+background_tables+'">Compra</th>'+
                                            '<th class="'+background_tables+'">Factura</th>'+
                                            '<th class="'+background_tables+'">Fecha</th>'+
                                            '<th class="'+background_tables+'">Plazo</th>'+
                                            '<th class="'+background_tables+'">Vence</th>'+
                                            '<th class="'+background_tables+'">Total $</th>'+
                                            '<th class="'+background_tables+'">Abonos $</th>'+
                                            '<th class="'+background_tables+'">Notas Crédito $</th>'+
                                            '<th class="customercolortheadth">Abono</th>'+
                                            '<th class="'+background_tables+'">Saldo $ (DOBLE CLICK)</th>'+
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
                                '<input type="text" class="form-control inputnext" name="contrarecibo" id="contrarecibo" onkeyup="tipoLetra(this);"  autocomplete="off">'+
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
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnext").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnext");          
        $(".inputnext").eq(index + 1).focus().select(); 
      }
    });
    setTimeout(function(){$("#folio").focus();},500);
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
      $("#email2cc").val(data.email2cc);
      $("#email3cc").val(data.email3cc);
      $("#emailasunto").val("CUENTA POR PAGAR NO. " + documento +" DE "+ nombreempresa);
      $(".dropify-clear").trigger("click");
      $("#divadjuntararchivo").hide();
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
    var checkboxscolumnas = '';
    var optionsselectbusquedas = '';
    var campos = campos_activados.split(",");
    for (var i = 0; i < campos.length; i++) {
      var returncheckboxfalse = '';
      if(campos[i] == 'Pago' || campos[i] == 'Status' || campos[i] == 'Periodo'){
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