'use strict'
var tabla;
var form;
var contadorfilasfacturas = 0;
//funcion que se ejecuta al inicio
function init(){
  campos_a_filtrar_en_busquedas();
  listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 1500));
}
function asignarfechaactual(){
    $.get(ordenes_compra_obtener_fecha_actual_datetimelocal, function(fechas){
      $("#fecha").val(fechas.fecha).attr('min', fechas.fechamin).attr('max', fechas.fechamax);
      $("#fechasalida").val(fechas.fecha).attr('min', fechas.fechamin).attr('max', fechas.fechamax);
      $("#fechallegada").val(fechas.fecha).attr('min', fechas.fechamin).attr('max', fechas.fechamax);
    }) 
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  var serie = $("#serie").val();
  $.get(carta_porte_obtener_obtener_ultimo_folio, {serie:serie}, function(folio){
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
//mostrar boton de mostrar facturas
function mostrarbotonlistarfacturas(){
  var cliente = $("#cliente").val();
  var almacen = $("#almacen").val();
  if(cliente != "" && almacen != ""){
    $("#btnlistarfacturas").show();
  }else{
    $("#btnlistarfacturas").hide();
  } 
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
    keys: true,
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
        url: carta_porte_obtener,
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
  //modificacion al dar doble click
  $('#tbllistado tbody').on('dblclick', 'tr', function () {
    var data = tabla.row( this ).data();
    obtenerdatos(data.Nota);
  });
}
//obtener registros de proveedores
function obtenerclientes(){
  ocultarformulario();
  var tablaclientes = '<div class="modal-header '+background_forms_and_modals+'">'+
                      '<h4 class="modal-title">Clientes</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                  '<table id="tbllistadocliente" class="tbllistadocliente table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                      '<thead class="'+background_tables+'">'+
                                          '<tr>'+
                                              '<th>Operaciones</th>'+
                                              '<th>Numero</th>'+
                                              '<th>Nombre</th>'+
                                              '<th>R.F.C.</th>'+
                                              '<th>Municipio</th>'+
                                              '<th>Agente</th>'+
                                              '<th>Tipo</th>'+
                                              '<th>Saldo $</th>'+
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
    $("#contenidomodaltablas").html(tablaclientes);
    var tcli = $('#tbllistadocliente').DataTable({
        keys: true,
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
            url: carta_porte_obtener_clientes,
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Rfc', name: 'Rfc', orderable: false, searchable: false },
            { data: 'Municipio', name: 'Municipio', orderable: false, searchable: false },
            { data: 'Agente', name: 'Agente', orderable: false, searchable: false },
            { data: 'Tipo', name: 'Tipo', orderable: false, searchable: false },
            { data: 'Saldo', name: 'Saldo', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadocliente').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
    //seleccionar registro al dar doble click
    $('#tbllistadocliente tbody').on('dblclick', 'tr', function () {
        var data = tcli.row( this ).data();
        seleccionarcliente(data.Numero, data.Nombre, data.Plazo, data.Rfc, data.ClaveFormaPago, data.NombreFormaPago, data.ClaveMetodoPago, data.NombreMetodoPago, data.ClaveUsoCfdi, data.NombreUsoCfdi, "", "", data.Calle, data.NoExterior, data.NoInterior, data.Colonia, data.Localidad, data.Referencia, data.Municipio, data.Estado, data.CodigoPostal);
    }); 
} 
//seleccionar proveedor
function seleccionarcliente(Numero, Nombre, Plazo, Rfc, claveformapago, formapago, clavemetodopago, metodopago, claveusocfdi, usocfdi, claveresidenciafiscal, residenciafiscal, calle, noexterior, nointerior, colonia, localidad, referencia, municipio, estado, codigopostal){
  var numeroclienteanterior = $("#numeroclienteanterior").val();
  var numerocliente = Numero;
  if(numeroclienteanterior != numerocliente){ 
    $("#numerocliente").val(Numero);
    $("#numeroclienteanterior").val(Numero);
    $("#cliente").val(Nombre);
    if(Nombre != null){
      $("#textonombrecliente").html(Nombre.substring(0, 40));
    }
    //datos pestaña receptor o cliente
    $("#rfcdestinatario").val(Rfc);
    $("#nombredestinatario").val(Nombre);
    $("#calledestinatario").val(calle);
    $("#numeroextdestinatario").val(noexterior);
    $("#numerointdestinatario").val(nointerior);
    $("#coloniadestinatario").val(colonia);
    $("#localidaddestinatario").val(localidad);
    $("#referenciadestinatario").val(referencia);
    $("#municipiodestinatario").val(municipio);
    $("#estadodestinatario").val(estado);
    $("#paisdestinatario").val(residenciafiscal);
    $("#cpdestinatario").val(codigopostal);
    /*
    $("#claveformapago").val(claveformapago);
    $("#formapago").val(formapago);
    if(formapago != null){
      $("#textonombreformapago").html(formapago.substring(0, 40));
    }
    $("#clavemetodopago").val(clavemetodopago);
    $("#metodopago").val(metodopago);
    if(metodopago != null){
      $("#textonombremetodopago").html(metodopago.substring(0, 40));
    }
    $("#claveusocfdi").val(claveusocfdi);
    $("#usocfdi").val(usocfdi);
    if(usocfdi != null){
      $("#textonombreusocfdi").html(usocfdi.substring(0, 40));
    }
    $("#claveresidenciafiscal").val(claveresidenciafiscal);
    $("#residenciafiscal").val(residenciafiscal);
    if(residenciafiscal != null){
      $("#textonombreresidenciafiscal").html(residenciafiscal.substring(0, 40));
    }
    */
    mostrarformulario();
  }
}
//obtener registros de mun
function obtenermunicipios(tipo){
  ocultarformulario();
  var tablamunicipios = '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Municipios</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadomunicipio" class="tbllistadomunicipio table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Municipio</th>'+
                                                    '<th>Estado</th>'+
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
    $("#contenidomodaltablas").html(tablamunicipios);
    var talmun = $('#tbllistadomunicipio').DataTable({
        keys: true,
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
            url: carta_porte_obtener_municipios,
            data: function (d) {
                d.tipo = tipo;
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Estado', name: 'Estado' }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadomunicipio').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
    //seleccionar registro al dar doble click
    $('#tbllistadomunicipio tbody').on('dblclick', 'tr', function () {
        var data = talmun.row( this ).data();
        seleccionarmunicipio(data.Numero, data.Nombre, tipo);
    }); 
} 
//seleccionar mun
function seleccionarmunicipio(Numero, Nombre, tipo){
  switch(tipo){
    case 'remitente':
        var numeromunicipioremitenteanterior = $("#numeromunicipioremitenteanterior").val();
        var numeromunicipioremitente = Numero;
        if(numeromunicipioremitenteanterior != numeromunicipioremitente){
          $("#numeromunicipioremitente").val(Numero);
          $("#numeromunicipioremitenteanterior").val(Numero);
          $("#municipioremitente").val(Nombre);
          if(Nombre != null){
            $("#textonombremunicipioremitente").html(Nombre.substring(0, 40));
          }
          mostrarformulario();
        }
      break;
    case 'destinatario':
        var numeromunicipiodestinatarioanterior = $("#numeromunicipiodestinatarioanterior").val();
        var numeromunicipiodestinatario = Numero;
        if(numeromunicipiodestinatarioanterior != numeromunicipiodestinatario){
          $("#numeromunicipiodestinatario").val(Numero);
          $("#numeromunicipiodestinatarioanterior").val(Numero);
          $("#municipiodestinatario").val(Nombre);
          if(Nombre != null){
            $("#textonombremunicipiodestinatario").html(Nombre.substring(0, 40));
          }
          mostrarformulario();
        }
      break;
  }
}
//obtener registros de est
function obtenerestados(tipo){
  ocultarformulario();
  var tablaestados = '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Estados</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoestado" class="tbllistadoestado table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Estado</th>'+
                                                    '<th>Pais</th>'+
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
    $("#contenidomodaltablas").html(tablaestados);
    var talest = $('#tbllistadoestado').DataTable({
        keys: true,
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
            url: carta_porte_obtener_estados,
            data: function (d) {
                d.tipo = tipo;
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Pais', name: 'Pais' }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoestado').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
    //seleccionar registro al dar doble click
    $('#tbllistadoestado tbody').on('dblclick', 'tr', function () {
        var data = talest.row( this ).data();
        seleccionarestado(data.Numero, data.Nombre, tipo);
    }); 
} 
//seleccionar est
function seleccionarestado(Numero, Nombre, tipo){
  switch(tipo){
    case 'remitente':
        var numeroestadoremitenteanterior = $("#numeroestadoremitenteanterior").val();
        var numeroestadoremitente = Numero;
        if(numeroestadoremitenteanterior != numeroestadoremitente){
          $("#numeroestadoremitente").val(Numero);
          $("#numeroestadoremitenteanterior").val(Numero);
          $("#estadoremitente").val(Nombre);
          if(Nombre != null){
            $("#textonombreestadoremitente").html(Nombre.substring(0, 40));
          }
          mostrarformulario();
        }
      break;
    case 'destinatario':
        var numeroestadodestinatarioanterior = $("#numeroestadodestinatarioanterior").val();
        var numeroestadodestinatario = Numero;
        if(numeroestadodestinatarioanterior != numeroestadodestinatario){
          $("#numeroestadodestinatario").val(Numero);
          $("#numeroestadodestinatarioanterior").val(Numero);
          $("#estadodestinatario").val(Nombre);
          if(Nombre != null){
            $("#textonombreestadodestinatario").html(Nombre.substring(0, 40));
          }
          mostrarformulario();
        }
      break;
  }
}
//obtener registros de pai
function obtenerpaises(tipo){
  ocultarformulario();
  var tablapaises = '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Paises</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadopais" class="tbllistadopais table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Pais</th>'+
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
    $("#contenidomodaltablas").html(tablapaises);
    var talpai = $('#tbllistadopais').DataTable({
        keys: true,
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
            url: carta_porte_obtener_paises,
            data: function (d) {
                d.tipo = tipo;
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' },
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadopais').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
    //seleccionar registro al dar doble click
    $('#tbllistadopais tbody').on('dblclick', 'tr', function () {
        var data = talpai.row( this ).data();
        seleccionarpais(data.Numero, data.Nombre, tipo);
    }); 
} 
//seleccionar pai
function seleccionarpais(Numero, Nombre, tipo){
  switch(tipo){
    case 'remitente':
        var numeropaisremitenteanterior = $("#numeropaisremitenteanterior").val();
        var numeropaisremitente = Numero;
        if(numeropaisremitenteanterior != numeropaisremitente){
          $("#numeropaisremitente").val(Numero);
          $("#numeropaisremitenteanterior").val(Numero);
          $("#paisremitente").val(Nombre);
          if(Nombre != null){
            $("#textonombrepaisremitente").html(Nombre.substring(0, 40));
          }
          mostrarformulario();
        }
      break;
    case 'destinatario':
        var numeropaisdestinatarioanterior = $("#numeropaisdestinatarioanterior").val();
        var numeropaisdestinatario = Numero;
        if(numeropaisdestinatarioanterior != numeropaisdestinatario){
          $("#numeropaisdestinatario").val(Numero);
          $("#numeropaisdestinatarioanterior").val(Numero);
          $("#paisdestinatario").val(Nombre);
          if(Nombre != null){
            $("#textonombrepaisdestinatario").html(Nombre.substring(0, 40));
          }
          mostrarformulario();
        }
      break;
  }
}
//obtener registros de cp
function obtenercps(tipo){
  ocultarformulario();
  var tablacps = '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Códigos Postales</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadocp" class="tbllistadocp table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Clave</th>'+
                                                    '<th>Estado</th>'+
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
    $("#contenidomodaltablas").html(tablacps);
    var talcps = $('#tbllistadocp').DataTable({
        keys: true,
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
            url: carta_porte_obtener_codigospostales,
            data: function (d) {
                d.tipo = tipo;
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Clave', name: 'Clave' },
            { data: 'Estado', name: 'Estado' },
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadocp').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
    //seleccionar registro al dar doble click
    $('#tbllistadocp tbody').on('dblclick', 'tr', function () {
        var data = talcps.row( this ).data();
        seleccionarcp(data.Numero, data.Clave, tipo);
    }); 
} 
//seleccionar est
function seleccionarcp(Numero, Clave, tipo){
  switch(tipo){
    case 'remitente':
        var numerocpremitenteanterior = $("#numerocpremitenteanterior").val();
        var numerocpremitente = Numero;
        if(numerocpremitenteanterior != numerocpremitente){
          $("#numerocpremitente").val(Numero);
          $("#numerocpremitenteanterior").val(Numero);
          $("#cpremitente").val(Clave);
          if(Clave != null){
            $("#textonombrecpremitente").html(Clave.substring(0, 40));
          }
          mostrarformulario();
        }
      break;
    case 'destinatario':
        var numerocpdestinatarioanterior = $("#numerocpdestinatarioanterior").val();
        var numerocpdestinatario = Numero;
        if(numerocpdestinatarioanterior != numerocpdestinatario){
          $("#numerocpdestinatario").val(Numero);
          $("#numerocpdestinatarioanterior").val(Numero);
          $("#cpdestinatario").val(Clave);
          if(Clave != null){
            $("#textonombrecpdestinatario").html(Clave.substring(0, 40));
          }
          mostrarformulario();
        }
      break;
  }
}
//obtener registros de config auto
function obtenerconfigautotransporte(){
    ocultarformulario();
    var tablaconfigauto = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Configuración Autotransporte</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoconfigautotransporte" class="tbllistadoconfigautotransporte table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Clave</th>'+
                                                    '<th>Descripción</th>'+
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
      $("#contenidomodaltablas").html(tablaconfigauto);
      var tconfauto = $('#tbllistadoconfigautotransporte').DataTable({
          keys: true,
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
              url: carta_porte_obtener_coonfiguracionesautotransporte,
          },
          columns: [
              { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
              { data: 'Numero', name: 'Numero' },
              { data: 'Clave', name: 'Clave' },
              { data: 'Descripcion', name: 'Descripcion' }
          ],
          "initComplete": function() {
              var $buscar = $('div.dataTables_filter input');
              $buscar.focus();
              $buscar.unbind();
              $buscar.bind('keyup change', function(e) {
                  if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoconfigautotransporte').DataTable().search( this.value ).draw();
                  }
              });
          },
      }); 
      //seleccionar registro al dar doble click
      $('#tbllistadoconfigautotransporte tbody').on('dblclick', 'tr', function () {
          var data = tconfauto.row( this ).data();
          seleccionarconfigautotransporte(data.Numero, data.Clave, data.Descripcion);
      }); 
} 
//seleccionar
function seleccionarconfigautotransporte(Numero, Clave, Descripcion){
  var claveconfigautotransporteanterior = $("#claveconfigautotransporteanterior").val();
  var claveconfigautotransporte = Clave;
  if(claveconfigautotransporteanterior != claveconfigautotransporte){
    $("#claveconfigautotransporte").val(Clave);
    $("#claveconfigautotransporteanterior").val(Clave);
    $("#configautotransporte").val(Descripcion);
    if(Descripcion != null){
      $("#textonombreconfigautotransporte").html(Descripcion.substring(0, 30));
    }
    mostrarformulario();
  }
}
//obtener registros clave transporte
function obtenerclavestransporte(){
  ocultarformulario();
  var tablaclavetransporte = '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Claves de transportes</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                          '<div class="row">'+
                              '<div class="col-md-12">'+
                                  '<div class="table-responsive">'+
                                      '<table id="tbllistadoclavetransporte" class="tbllistadoclavetransporte table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                          '<thead class="'+background_tables+'">'+
                                              '<tr>'+
                                                  '<th>Operaciones</th>'+
                                                  '<th>Numero</th>'+
                                                  '<th>Clave</th>'+
                                                  '<th>Descripción</th>'+
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
    $("#contenidomodaltablas").html(tablaclavetransporte);
    var tclavtrans = $('#tbllistadoclavetransporte').DataTable({
        keys: true,
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
            url: carta_porte_obtener_clavestransporte,
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Clave', name: 'Clave' },
            { data: 'Descripcion', name: 'Descripcion' }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoclavetransporte').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
    //seleccionar registro al dar doble click
    $('#tbllistadoclavetransporte tbody').on('dblclick', 'tr', function () {
        var data = tclavtrans.row( this ).data();
        seleccionarclavetransporte(data.Numero, data.Clave, data.Descripcion);
    }); 
} 
//seleccionar
function seleccionarclavetransporte(Numero, Clave, Descripcion){
  var clavetransporteanterior = $("#clavetransporteanterior").val();
  var clavetransporte = Clave;
  if(clavetransporteanterior != clavetransporte){
    $("#clavetransporte").val(Clave);
    $("#clavetransporteanterior").val(Clave);
    $("#nombreclavetransporte").val(Descripcion);
    if(Descripcion != null){
      $("#textonombreclavetransporte").html(Descripcion.substring(0, 40));
    }
    mostrarformulario();
  }
}
//obtener registros de almacenes
function obteneralmacenes(){
    ocultarformulario();
    var tablaalmacenes = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Almacenes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoalmacen" class="tbllistadoalmacen table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Almacén</th>'+
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
      $("#contenidomodaltablas").html(tablaalmacenes);
      var talm = $('#tbllistadoalmacen').DataTable({
          keys: true,
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
              url: notas_credito_clientes_obtener_almacenes,
          },
          columns: [
              { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
              { data: 'Numero', name: 'Numero' },
              { data: 'Nombre', name: 'Nombre' }
          ],
          "initComplete": function() {
              var $buscar = $('div.dataTables_filter input');
              $buscar.focus();
              $buscar.unbind();
              $buscar.bind('keyup change', function(e) {
                  if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoalmacen').DataTable().search( this.value ).draw();
                  }
              });
          },
      }); 
      //seleccionar registro al dar doble click
      $('#tbllistadoalmacen tbody').on('dblclick', 'tr', function () {
          var data = talm.row( this ).data();
          seleccionaralmacen(data.Numero, data.Nombre);
      }); 
} 
//seleccionar almacen
function seleccionaralmacen(Numero, Nombre){
  var numeroalmacenanterior = $("#numeroalmacenanterior").val();
  var numeroalmacen = Numero;
  if(numeroalmacenanterior != numeroalmacen){
    $("#numeroalmacen").val(Numero);
    $("#numeroalmacenanterior").val(Numero);
    $("#almacen").val(Nombre);
    if(Nombre != null){
      $("#textonombrealmacen").html(Nombre.substring(0, 40));
    }
    $("#btnlistarfacturas").show();
    mostrarformulario();
  }
}
//obtener lugares expedicion
function obtenerlugaresexpedicion(){
  ocultarformulario();
  var tablacodigospostales =  '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Códigos Postales</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadocodigopostal" class="tbllistadocodigopostal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
                                                        '<th>Estado</th>'+
                                                        '<th>Municipio</th>'+
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
  $("#contenidomodaltablas").html(tablacodigospostales);
  var tcodpost = $('#tbllistadocodigopostal').DataTable({
      keys: true,
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
        url: notas_credito_clientes_obtener_codigos_postales,
        data: function (d) {
            //d.numeroestado = $("#estado").val();
        }
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Estado', name: 'Estado', orderable: false, searchable: false},
          { data: 'Municipio', name: 'Municipio', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadocodigopostal').DataTable().search( this.value ).draw();
            }
        });
      }, 
  });
  //seleccionar registro al dar doble click
  $('#tbllistadocodigopostal tbody').on('dblclick', 'tr', function () {
      var data = tcodpost.row( this ).data();
      seleccionarlugarexpedicion(data.Clave);
  }); 
} 
//seleccionar lugar expedicion
function seleccionarlugarexpedicion(Clave){
  var lugarexpedicionanterior = $("#lugarexpedicionanterior").val();
  var lugarexpedicion = Clave;
  if(lugarexpedicionanterior != lugarexpedicion){
    $("#lugarexpedicion").val(Clave);
    $("#lugarexpedicionanterior").val(Clave);
    mostrarformulario();
  }
}
//obtener regimenes fiscales
function obtenerregimenesfiscales(){
  ocultarformulario();
  var tablaregimenesfiscales ='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Regimenes Fiscales</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoregimenfiscal" class="tbllistadoregimenfiscal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>Física</th>'+
                                                        '<th>Moral</th>'+
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
  $("#contenidomodaltablas").html(tablaregimenesfiscales);
  var tregfis = $('#tbllistadoregimenfiscal').DataTable({
      keys: true,
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
        url: notas_credito_clientes_obtener_regimenes_fiscales
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
          { data: 'Fisica', name: 'Fisica', orderable: false, searchable: false},
          { data: 'Moral', name: 'Moral', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoregimenfiscal').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoregimenfiscal tbody').on('dblclick', 'tr', function () {
      var data = tregfis.row( this ).data();
      seleccionarregimenfiscal(data.Clave, data.Nombre);
  }); 
} 
//seleccionar lugar expedicion
function seleccionarregimenfiscal(Clave, Nombre){
  var claveregimenfiscalanterior = $("#claveregimenfiscalanterior").val();
  var claveregimenfiscal = Clave;
  if(claveregimenfiscalanterior != claveregimenfiscal){
    $("#claveregimenfiscal").val(Clave);
    $("#claveregimenfiscalanterior").val(Clave);
    $("#regimenfiscal").val(Nombre);
    if(Nombre != null){
      $("#textonombreregimenfiscal").html(Nombre.substring(0, 40));
    }
    mostrarformulario();
  }
}
//obtener tipos relacion
function obtenertiposrelaciones(){
  ocultarformulario();
  var tablatiposrelaciones ='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Tipos Relación</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadotiporelacion" class="tbllistadotiporelacion table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
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
  $("#contenidomodaltablas").html(tablatiposrelaciones);
  var ttiprel = $('#tbllistadotiporelacion').DataTable({
      keys: true,
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
        url: notas_credito_clientes_obtener_tipos_relacion
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadotiporelacion').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadotiporelacion tbody').on('dblclick', 'tr', function () {
      var data = ttiprel.row( this ).data();
      seleccionartiporelacion(data.Clave, data.Nombre);
  }); 
} 
//seleccionar lugar expedicion
function seleccionartiporelacion(Clave, Nombre){
  var clavetiporelacionanterior = $("#clavetiporelacionanterior").val();
  var clavetiporelacion = Clave;
  if(clavetiporelacionanterior != clavetiporelacion){
    $("#clavetiporelacion").val(Clave);
    $("#clavetiporelacionanterior").val(Clave);
    $("#tiporelacion").val(Nombre);
    if(Nombre != null){
      $("#textonombretiporelacion").html(Nombre.substring(0, 40));
    }
    mostrarformulario();
  }
}
//obtener formas de pago
function obtenerformaspago(){
  ocultarformulario();
  var tablaformaspago ='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Formas Pago</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoformapago" class="tbllistadoformapago table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>Descripción</th>'+
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
  $("#contenidomodaltablas").html(tablaformaspago);
  var tforpag = $('#tbllistadoformapago').DataTable({
      keys: true,
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
        url: notas_credito_clientes_obtener_formas_pago
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
          { data: 'Descripcion', name: 'Descripcion', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoformapago').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoformapago tbody').on('dblclick', 'tr', function () {
      var data = tforpag.row( this ).data();
      seleccionarformapago(data.Clave, data.Nombre);
  }); 
} 
//seleccionar forma pago
function seleccionarformapago(Clave, Nombre){
  var claveformapagoanterior = $("#claveformapagoanterior").val();
  var claveformapago = Clave;
  if(claveformapagoanterior != claveformapago){
    $("#claveformapago").val(Clave);
    $("#claveformapagoanterior").val(Clave);
    $("#formapago").val(Nombre);
    if(Nombre != null){
      $("#textonombreformapago").html(Nombre.substring(0, 40));
    }
    mostrarformulario();
  }
}
//obtener metodos de pago
function obtenermetodospago(){
  ocultarformulario();
  var tablametodospago='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Métodos Pago</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadometodopago" class="tbllistadometodopago table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
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
  $("#contenidomodaltablas").html(tablametodospago);
  var tmetpag = $('#tbllistadometodopago').DataTable({
      keys: true,
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
        url: notas_credito_clientes_obtener_metodos_pago
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadometodopago').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadometodopago tbody').on('dblclick', 'tr', function () {
      var data = tmetpag.row( this ).data();
      seleccionarmetodopago(data.Clave, data.Nombre);
  });
} 
//seleccionar metodo pago
function seleccionarmetodopago(Clave, Nombre){
  var clavemetodopagoanterior = $("#clavemetodopagoanterior").val();
  var clavemetodopago = Clave;
  if(clavemetodopagoanterior != clavemetodopago){
    $("#clavemetodopago").val(Clave);
    $("#clavemetodopagoanterior").val(Clave);
    $("#metodopago").val(Nombre);
    if(Nombre != null){
      $("#textonombremetodopago").html(Nombre.substring(0, 40));
    }
    mostrarformulario();
  }
}
//obtener usos cfdi
function obtenerusoscfdi(){
  ocultarformulario();
  var tablausoscfdi='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Usos CFDI</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadousocfdi" class="tbllistadousocfdi table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>Fisica</th>'+
                                                        '<th>Moral</th>'+
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
  $("#contenidomodaltablas").html(tablausoscfdi);
  var tusocfdi = $('#tbllistadousocfdi').DataTable({
      keys: true,
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
        url: notas_credito_clientes_obtener_usos_cfdi
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
          { data: 'Fisica', name: 'Fisica', orderable: false, searchable: false},
          { data: 'Moral', name: 'Moral', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadousocfdi').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadousocfdi tbody').on('dblclick', 'tr', function () {
      var data = tusocfdi.row( this ).data();
      seleccionarusocfdi(data.Clave, data.Nombre);
  }); 
} 
//seleccionar uso cfdi
function seleccionarusocfdi(Clave, Nombre){
  var claveusocfdianterior = $("#claveusocfdianterior").val();
  var claveusocfdi = Clave;
  if(claveusocfdianterior != claveusocfdi){
    $("#claveusocfdi").val(Clave);
    $("#claveusocfdianterior").val(Clave);
    $("#usocfdi").val(Nombre);
    if(Nombre != null){
      $("#textonombreusocfdi").html(Nombre.substring(0, 40));
    }
    mostrarformulario();
  }
}
//obtener residencias fiscales
function obtenerresidenciasfiscales(){
  ocultarformulario();
  var tablaresidenciasfiscales='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Residencias Fiscales</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoresidencialfiscal" class="tbllistadoresidencialfiscal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
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
  $("#contenidomodaltablas").html(tablaresidenciasfiscales);
  var tresfis = $('#tbllistadoresidencialfiscal').DataTable({
      keys: true,
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
        url: notas_credito_clientes_obtener_residencias_fiscales
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoresidencialfiscal').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoresidencialfiscal tbody').on('dblclick', 'tr', function () {
      var data = tresfis.row( this ).data();
      seleccionarresidenciafiscal(data.Clave, data.Nombre);
  }); 
} 
//seleccionar residencia fiscal
function seleccionarresidenciafiscal(Clave, Nombre){
  var claveresidenciafiscalanterior = $("#claveresidenciafiscalanterior").val();
  var claveresidenciafiscal = Clave;
  if(claveresidenciafiscalanterior != claveresidenciafiscal){
    $("#claveresidenciafiscal").val(Clave);
    $("#claveresidenciafiscalanterior").val(Clave);
    $("#residenciafiscal").val(Nombre);
    if(Nombre != null){
      $("#textonombreresidenciafiscal").html(Nombre.substring(0, 40));
    }
    mostrarformulario();
  }
}
//obtener folio serie nota
function obtenerfoliosnotas(){
  ocultarformulario();
  var tablafoliosfiscales='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Folios Fiscales</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadofoliofiscal" class="tbllistadofoliofiscal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Serie</th>'+
                                                        '<th>Esquema</th>'+
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
  $("#contenidomodaltablas").html(tablafoliosfiscales);
  var tfolfis = $('#tbllistadofoliofiscal').DataTable({
      keys: true,
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
        url: carta_porte_obtener_folios_fiscales
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Serie', name: 'Serie' },
          { data: 'Esquema', name: 'Esquema', orderable: false, searchable: false},
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadofoliofiscal').DataTable().search( this.value ).draw();
            }
        });
      },
  });  
  //seleccionar registro al dar doble click
  $('#tbllistadofoliofiscal tbody').on('dblclick', 'tr', function () {
      var data = tfolfis.row( this ).data();
      seleccionarfoliofiscal(data.Serie, data.Esquema);
  }); 
}
function seleccionarfoliofiscal(Serie, Esquema){
  var numerofilas = $("#numerofilas").val()
  if(parseInt(numerofilas) > 0){
    var confirmacion = confirm("Esta seguro de cambiar el folio fiscal?"); 
  }else{
    var confirmacion = true;
  }
  if (confirmacion == true) { 
    $.get(carta_porte_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie,Esquema:Esquema}, function(folio){
      $("#folio").val(folio);
      $("#serie").val(Serie);
      $("#esquema").val(Esquema);
      $("#serietexto").html("Serie: "+Serie);
      $("#esquematexto").html("Esquema: "+Esquema);
      mostrarformulario();
    }) 
  }
}
//obtener vehiculos
function obtenervehiculosempresa (){
  ocultarformulario();
  var tablavehiculos='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Vehiculos</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadovehiculo" class="tbllistadovehiculo table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Numero</th>'+
                                                        '<th>Placa</th>'+
                                                        '<th>Año</th>'+
                                                        '<th>Marca</th>'+
                                                        '<th>Modelo</th>'+
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
  $("#contenidomodaltablas").html(tablavehiculos);
  var tvehi = $('#tbllistadovehiculo').DataTable({
      keys: true,
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
        url: carta_porte_obtener_vehiculos
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'id', name: 'id' },
          { data: 'Placa', name: 'Placa' },
          { data: 'Año', name: 'Año', orderable: false, searchable: false},
          { data: 'Marca', name: 'Marca', orderable: false, searchable: false},
          { data: 'Modelo', name: 'Modelo', orderable: false, searchable: false},
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadovehiculo').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadovehiculo tbody').on('dblclick', 'tr', function () {
      var data = tvehi.row( this ).data();
      seleccionarvehiculo(data.id, data.PermisoSCT, data.NumeroPermisoSCT, data.NombreAseguradora, data.NumeroPolizaSeguro, data.Placa, data.Año, data.SubTipoRemolque, data.PlacaSubTipoRemolque, data.Marca, data.Modelo);
  }); 
} 
//seleccionar residencia fiscal
function seleccionarvehiculo(id, PermisoSCT, NumeroPermisoSCT, NombreAseguradora, NumeroPolizaSeguro, Placa, Año, SubTipoRemolque, PlacaSubTipoRemolque, Marca, Modelo){
  var numerovehiculoempresaanterior = $("#numerovehiculoempresaanterior").val();
  var numerovehiculoempresa = id;
  if(numerovehiculoempresaanterior != numerovehiculoempresa){
    $("#numerovehiculoempresa").val(id);
    $("#numerovehiculoempresaanterior").val(id);
    $("#permisosct").val(PermisoSCT);
    $("#numeropermisosct").val(NumeroPermisoSCT);
    $("#nombreaseguradora").val(NombreAseguradora);
    $("#numeropolizaseguro").val(NumeroPolizaSeguro);
    $("#placavehiculo").val(Placa);
    $("#anovehiculo").val(Año);
    $("#subtiporemolque").val(SubTipoRemolque);
    $("#placaremolque").val(PlacaSubTipoRemolque);
    $("#vehiculoempresa").val(Modelo);
    if(Modelo != null){
      $("#textonombrevehiculoempresa").html(Marca +" - "+Modelo);
    }
    mostrarformulario();
  }
}
//obtener por clave
function obtenervehiculopornumero(){
  var numerovehiculoempresaanterior = $("#numerovehiculoempresaanterior").val();
  var numerovehiculoempresa  = $("#numerovehiculoempresa").val();
  if(numerovehiculoempresaanterior != numerovehiculoempresa){
    if($("#numerovehiculoempresa").parsley().isValid()){
      $.get(carta_porte_obtener_vehiculo_por_numero, {numerovehiculoempresa:numerovehiculoempresa}, function(data){
        $("#numerovehiculoempresa").val(data.id);
        $("#numerovehiculoempresaanterior").val(data.id);
        $("#permisosct").val(data.PermisoSCT);
        $("#numeropermisosct").val(data.NumeroPermisoSCT);
        $("#nombreaseguradora").val(data.NombreAseguradora);
        $("#numeropolizaseguro").val(data.NumeroPolizaSeguro);
        $("#placavehiculo").val(data.Placa);
        $("#anovehiculo").val(data.Año);
        $("#subtiporemolque").val(data.SubTipoRemolque);
        $("#placaremolque").val(data.PlacaSubTipoRemolque);
        $("#vehiculoempresa").val(data.Modelo);
        if(data.Modelo != null){
          $("#textonombrevehiculoempresa").html(data.Marca +" - "+data.Modelo);
        }
        mostrarformulario();
      }) 
    }
  }
}
//regresar clave
function regresarnumerovehiculo(){
  var numerovehiculoempresaanterior = $("#numerovehiculoempresaanterior").val();
  $("#numerovehiculoempresa").val(numerovehiculoempresaanterior);
}







//obtener operadores
function obteneroperadoresempresa (){
  ocultarformulario();
  var tablaoperadores='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Operadores</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadooperador" class="tbllistadooperador table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Numero</th>'+
                                                        '<th>RFC</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>Licencia</th>'+
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
  $("#contenidomodaltablas").html(tablaoperadores);
  var toper = $('#tbllistadooperador').DataTable({
      keys: true,
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
        url: carta_porte_obtener_operadores
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'id', name: 'id' },
          { data: 'Rfc', name: 'Rfc' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
          { data: 'NumeroLicencia', name: 'NumeroLicencia', orderable: false, searchable: false},
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadooperador').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadooperador tbody').on('dblclick', 'tr', function () {
      var data = toper.row( this ).data();
      seleccionaroperador(data.id, data.Rfc, data.Nombre, data.NumeroLicencia, data.Calle, data.NoExterior, data.NoInterior, data.Colonia, data.Localidad, data.Referencia, data.Municipio, data.Estado, data.Pais, data.CodigoPostal);
  }); 
} 
//seleccionar residencia fiscal
function seleccionaroperador(id, Rfc, Nombre, NumeroLicencia, Calle, NoExterior, NoInterior, Colonia, Localidad, Referencia, Municipio, Estado, Pais, CodigoPostal){
  var numerooperadoranterior = $("#numerooperadoranterior").val();
  var numerooperador = id;
  if(numerooperadoranterior != numerooperador){
    $("#numerooperador").val(id);
    $("#numerooperadoranterior").val(id);
    $("#rfcoperador").val(Rfc);
    $("#nombreoperador").val(Nombre);
    $("#numerolicenciaoperador").val(NumeroLicencia);
    $("#calleoperador").val(Calle);
    $("#numeroextoperador").val(NoExterior);
    $("#numerointoperador").val(NoInterior);
    $("#coloniaoperador").val(Colonia);
    $("#localidadoperador").val(Localidad);
    $("#referenciaoperador").val(Referencia);
    $("#municipiooperador").val(Municipio);
    $("#estadooperador").val(Estado);
    $("#paisoperador").val(Pais);
    $("#cpoperador").val(CodigoPostal);
    if(Nombre != null){
      $("#textonombreoperador").html(Nombre);
    }
    mostrarformulario();
  }
}
//obtener por clave
function obteneroperadorpornumero(){
  var numerooperadoranterior = $("#numerooperadoranterior").val();
  var numerooperador  = $("#numerooperador").val();
  if(numerooperadoranterior != numerooperador){
    if($("#numerooperador").parsley().isValid()){
      $.get(carta_porte_obtener_operador_por_numero, {numerooperador:numerooperador}, function(data){
        $("#numerooperador").val(data.id);
        $("#numerooperadoranterior").val(data.id);
        $("#rfcoperador").val(data.Rfc);
        $("#nombreoperador").val(data.Nombre);
        $("#numerolicenciaoperador").val(data.NumeroLicencia);
        $("#calleoperador").val(data.Calle);
        $("#numeroextoperador").val(data.NoExterior);
        $("#numerointoperador").val(data.NoInterior);
        $("#coloniaoperador").val(data.Colonia);
        $("#localidadoperador").val(data.Localidad);
        $("#referenciaoperador").val(data.Referencia);
        $("#municipiooperador").val(data.Municipio);
        $("#estadooperador").val(data.Estado);
        $("#paisoperador").val(data.Pais);
        $("#cpoperador").val(data.CodigoPostal);
        if(data.Nombre != null){
          $("#textonombreoperador").html(data.Nombre);
        }
        mostrarformulario();
      }) 
    }
  }
}
//regresar clave
function regresarnumerooperador(){
  var numerooperadoranterior = $("#numerooperadoranterior").val();
  $("#numerooperador").val(numerooperadoranterior);
}
//listar todas las facturas
function listarfacturas (){
  ocultarformulario();
  var tablafacturas ='<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Facturas</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                        '<div class="row">'+
                          '<div class="col-md-12">'+
                            '<div class="table-responsive">'+
                              '<table id="tbllistadofactura" class="tbllistadofactura table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                '<thead class="'+background_tables+'">'+
                                  '<tr>'+
                                    '<th>Operaciones</th>'+
                                    '<th>Factura</th>'+
                                    '<th>Depto</th>'+
                                    '<th>Fecha</th>'+
                                    '<th>Plazo</th>'+
                                    '<th>Items</th>'+
                                    '<th>Total $</th>'+
                                    '<th>Abonos $</th>'+
                                    '<th>Descuentos $</th>'+
                                    '<th>Saldo $</th>'+
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
    $("#contenidomodaltablas").html(tablafacturas);
    var tfact = $('#tbllistadofactura').DataTable({
        keys: true,
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
          url: notas_credito_clientes_obtener_facturas,
          data: function (d) {
              d.numerocliente = $("#numerocliente").val();
              d.stringfacturasseleccionadas = $("#stringfacturasseleccionadas").val();
          }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Factura', name: 'Factura' },
            { data: 'Depto', name: 'Depto' },
            { data: 'Fecha', name: 'Fecha' },
            { data: 'Plazo', name: 'Plazo', orderable: false, searchable: false },
            { data: 'Items', name: 'Items', orderable: false, searchable: false },
            { data: 'Total', name: 'Total', orderable: false, searchable: false },
            { data: 'Abonos', name: 'Abonos', orderable: false, searchable: false },
            { data: 'Descuentos', name: 'Descuentos', orderable: false, searchable: false },
            { data: 'Saldo', name: 'Saldo', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadofactura').DataTable().search( this.value ).draw();
                }
            });
        },
    });  
    //seleccionar registro al dar doble click
    $('#tbllistadofactura tbody').on('dblclick', 'tr', function () {
      var data = tfact.row( this ).data();
      seleccionarfactura(data.Folio, data.Factura);
    });
} 
//obtener todos los datos de la orden de compra seleccionada
function seleccionarfactura(Folio, Factura){
    $('.page-loader-wrapper').css('display', 'block');
    var tipooperacion = $("#tipooperacion").val();
    $.get(notas_credito_clientes_obtener_factura, {Folio:Folio, Factura:Factura, contadorfilasfacturas:contadorfilasfacturas, tipooperacion:tipooperacion}, function(data){
      $("#tabladetallesfacturasnotascliente tbody").append(data.filafactura);
      //array de compras seleccionar
      construirarrayfacturasseleccionadas();
      //activar buscador de codigos
      $("#codigoabuscar").removeAttr('readonly');
      //comprobar numero de filas en la tabla
      comprobarfilasfacturanotacliente();
      //calcular totales compras nota proveedor
      calculartotalcompranotacliente();
      mostrarformulario();
      eliminarfilascodigos();
      $('.page-loader-wrapper').css('display', 'none');
      //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
      $(".inputnextdet").keyup(function (e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        var index = $(this).index(".inputnextdet");          
        switch(code){
          case 13:
            $(".inputnextdet").eq(index + 1).focus().select(); 
            break;
          case 39:
            $(".inputnextdet").eq(index + 1).focus().select(); 
            break;
          case 37:
            $(".inputnextdet").eq(index - 1).focus().select(); 
            break;
        }
      });
      contadorfilasfacturas++;
    })
}
//crear array de compras seleccionadas
function construirarrayfacturasseleccionadas(){
  var arrayfacturasseleccionadas = [];
  $("tr.filasfacturas").each(function () { 
      // obtener los datos de la fila
      var facturaaplicarpartida = $(".facturaaplicarpartida", this).val();
      arrayfacturasseleccionadas.push(facturaaplicarpartida);
  });
  $("#stringfacturasseleccionadas").val(arrayfacturasseleccionadas);
}
//calcular total por filas de tabla compras de la nota de credito proveedor
function calculartotalesfilastablafacturas(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasfacturas").each(function () { 
    if(fila === cuentaFilas){
      // obtener los datos de la fila:
      var totalpesosfacturapartida = $(".totalpesosfacturapartida", this).val();
      var notascreditofacturapartida = $(".notascreditofacturapartida", this).val();
      var descuentopesosfacturapartida = $('.descuentopesosfacturapartida', this).val();
      var saldofacturapartida = $('.saldofacturapartida', this).val();
      var abonosfacturapartida = $('.abonosfacturapartida', this).val();
      //saldo de la factura partida
      saldofacturapartida =  new Decimal(totalpesosfacturapartida).minus(abonosfacturapartida).minus(notascreditofacturapartida).minus(descuentopesosfacturapartida);
      $('.saldofacturapartida', this).val(number_format(round(saldofacturapartida, numerodecimales), numerodecimales, '.', ''));     
      calculartotal();
      calculartotalcompranotacliente();
    }  
    cuentaFilas++;
  });
}
//calcular totales de la compra de la nota de proveedor
function calculartotalcompranotacliente(){
  var descuentofacturas = 0;
  var diferencia= 0;
  $("tr.filasfacturas").each(function(){
    descuentofacturas = new Decimal(descuentofacturas).plus($(".descuentopesosfacturapartida", this).val());
  }); 
  var totalnota = $("#totalnota").val();
  $("#descuentofacturas").val(number_format(round(descuentofacturas, numerodecimales), numerodecimales, '.', ''));
  diferencia = new Decimal(totalnota).minus(descuentofacturas);
  $("#diferencia").val(number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
}
//eliminar una fila en la tabla de compras
function eliminarfilafacturanotacliente(fila){
  var confirmacion = confirm("Esta seguro de eliminar la fila?"); 
  if (confirmacion == true) { 
    $("#filafactura"+fila).remove();
    contadorfilasfacturas--; //importante para todos los calculos se debe restar al contador
    renumerarfilasfacturanotacliente();//importante para todos los calculo en el modulo de orden de compra 
    comprobarfilasfacturanotacliente();
    calculartotalcompranotacliente();
    construirarrayfacturasseleccionadas();
  }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilasfacturanotacliente(){
  var numerofilasfacturas = $("#tabladetallesfacturasnotascliente tbody tr").length;
  $("#numerofilasfacturas").val(numerofilasfacturas);
  //quitar el almacen cuando se elijan mas de una compra
  var tipodetalles = $("#tipodetalles").val();
  var numerofilas = $("#numerofilas").val();
  if(parseInt(numerofilasfacturas) > parseInt(1) ){
    $("#almacen").val(0);
    $("#textonombrealmacen").html("");
    $("#numeroalmacen").val(0);
    $("#numeroalmacenanterior").val(0);
  }else if(parseInt(numerofilasfacturas) == parseInt(1) && parseInt(numerofilas) == parseInt(0) ){
      $("#almacen").val("");
      $("#textonombrealmacen").html("");
      $("#numeroalmacen").val("");
      $("#numeroalmacenanterior").val("");
  }else if(parseInt(numerofilasfacturas) == parseInt(1) && parseInt(numerofilas) >= parseInt(1) && (tipodetalles == '' || tipodetalles == 'dppp')  ){
    $("#almacen").val(0);
    $("#textonombrealmacen").html("");
    $("#numeroalmacen").val(0);
    $("#numeroalmacenanterior").val(0);
  }
}
//renumerar las filas de la orden de factura
function renumerarfilasfacturanotacliente(){
  var lista;
  //renumerar filas tr
  lista = document.getElementsByClassName("filasfacturas");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("id", "filafactura"+i);
  }
  //renumerar btneliminarfilacompra
  lista = document.getElementsByClassName("btneliminarfilafactura");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onclick", "eliminarfilafacturanotacliente("+i+')');
  }
  //renumerar descuentopesoscomprapartida
  lista = document.getElementsByClassName("descuentopesosfacturapartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilastablafacturas("+i+')');
  }
  //renumerar btnlistarclavesproductos
  lista = document.getElementsByClassName("btnlistarclavesproductos");
  for (var i = 0; i < lista.length; i++){
    lista[i].setAttribute("onclick", "listarclavesproductos("+i+')');
  }
  //renumerar btnlistarclavesunidades
  lista = document.getElementsByClassName("btnlistarclavesunidades");
  for (var i = 0; i < lista.length; i++){
    lista[i].setAttribute("onclick", "listarclavesunidades("+i+')');
  }
}  
//detectar cuando en el input de buscar por codigo de producto el usuario presione la tecla enter, si es asi se realizara la busqueda con el codigo escrito
$(document).ready(function(){
  $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        listarproductos();
      }
  });
});

//obtener por numero
function obtenerclientepornumero(){
  var numeroclienteanterior = $("#numeroclienteanterior").val();
  var numerocliente = $("#numerocliente").val();
  if(numeroclienteanterior != numerocliente){
    if($("#numerocliente").parsley().isValid()){
      $.get(carta_porte_obtener_cliente_por_numero, {numerocliente:numerocliente}, function(data){
        $("#numerocliente").val(data.numero);
        $("#numeroclienteanterior").val(data.numero);
        $("#cliente").val(data.nombre);
        if(data.nombre != null){
          $("#textonombrecliente").html(data.nombre.substring(0, 40));
        }
        //datos pestaña receptor o cliente
        $("#rfcdestinatario").val(data.rfc);
        $("#nombredestinatario").val(data.nombre);
        $("#calledestinatario").val(data.calle);
        $("#numeroextdestinatario").val(data.noexterior);
        $("#numerointdestinatario").val(data.nointerior);
        $("#coloniadestinatario").val(data.colonia);
        $("#localidaddestinatario").val(data.localidad);
        $("#referenciadestinatario").val(data.referencia);
        $("#municipiodestinatario").val(data.municipio);
        $("#estadodestinatario").val(data.estado);
        $("#paisdestinatario").val(data.residenciafiscal);
        $("#cpdestinatario").val(data.codigopostal);
        /*
        $("#claveformapago").val(data.claveformapago);
        $("#claveformapagoanterior").val(data.claveformapago);
        $("#formapago").val(data.formapago);
        if(data.formapago != null){
          $("#textonombreformapago").html(data.formapago.substring(0, 40));
        }
        $("#clavemetodopago").val(data.clavemetodopago);
        $("#clavemetodopagoanterior").val(data.clavemetodopago);
        $("#metodopago").val(data.metodopago);
        if(data.metodopago != null){
          $("#textonombremetodopago").html(data.metodopago.substring(0, 40));
        }
        $("#claveusocfdi").val(data.claveusocfdi);
        $("#claveusocfdianterior").val(data.claveusocfdi);
        $("#usocfdi").val(data.usocfdi);
        if(data.usocfdi != null){
          $("#textonombreusocfdi").html(data.usocfdi.substring(0, 40));
        }
        $("#claveresidenciafiscal").val(data.claveresidenciafiscal);
        $("#claveresidenciafiscalanterior").val(data.claveresidenciafiscal);
        $("#residenciafiscal").val(data.residenciafiscal);
        if(data.residenciafiscal != null){
          $("#textonombreresidenciafiscal").html(data.residenciafiscal.substring(0, 40));
        }
        */
        mostrarformulario();
      }) 
    }
  }
}
//regresar numero
function regresarnumerocliente(){
  var numeroclienteanterior = $("#numeroclienteanterior").val();
  $("#numerocliente").val(numeroclienteanterior);
}
//obtener por numero
function obteneralmacenpornumero(){
  var numeroalmacenanterior = $("#numeroalmacenanterior").val();
  var numeroalmacen = $("#numeroalmacen").val();
  if(numeroalmacenanterior != numeroalmacen){
    if($("#numeroalmacen").parsley().isValid()){
      $.get(notas_credito_clientes_obtener_almacen_por_numero, {numeroalmacen:numeroalmacen}, function(data){
          $("#numeroalmacen").val(data.numero);
          $("#numeroalmacenanterior").val(data.numero);
          $("#almacen").val(data.nombre);
          if(data.nombre != null){
            $("#textonombrealmacen").html(data.nombre.substring(0, 40));
          }
          mostrarbotonlistarfacturas();
          mostrarformulario();
      }) 
    }
  }
}
//regresar numero
function regresarnumeroalmacen(){
  var numeroalmacenanterior = $("#numeroalmacenanterior").val();
  $("#numeroalmacen").val(numeroalmacenanterior);
}
//obtener por clave
function obtenerlugarexpedicionporclave(){
  var lugarexpedicionanterior = $("#lugarexpedicionanterior").val();
  var lugarexpedicion = $("#lugarexpedicion").val();
  if(lugarexpedicionanterior != lugarexpedicion){
    if($("#lugarexpedicion").parsley().isValid()){
      $.get(notas_credito_clientes_obtener_lugar_expedicion_por_clave, {lugarexpedicion:lugarexpedicion}, function(data){
        $("#lugarexpedicion").val(data.clave);
        $("#lugarexpedicionanterior").val(data.clave);
        if(data.estado != null){
          $("#textonombrelugarexpedicion").html(data.estado.substring(0, 40));
        }
        mostrarformulario();
      }) 
    }
  }
}
//regresar clave
function regresarclavelugarexpedicion(){
  var lugarexpedicionanterior = $("#lugarexpedicionanterior").val();
  $("#lugarexpedicion").val(lugarexpedicionanterior);
}
//obtener por clave
function obtenerregimenfiscalporclave(){
  var claveregimenfiscalanterior = $("#claveregimenfiscalanterior").val();
  var claveregimenfiscal = $("#claveregimenfiscal").val();
  if(claveregimenfiscalanterior != claveregimenfiscal){
    if($("#claveregimenfiscal").parsley().isValid()){
      $.get(notas_credito_clientes_obtener_regimen_fiscal_por_clave, {claveregimenfiscal:claveregimenfiscal}, function(data){
        $("#claveregimenfiscal").val(data.clave);
        $("#claveregimenfiscalanterior").val(data.clave);
        $("#regimenfiscal").val(data.nombre);
        if(data.nombre != null){
          $("#textonombreregimenfiscal").html(data.nombre.substring(0, 40));
        }
        mostrarformulario();
      }) 
    }
  }
}
//regresar clave
function regresarclaveregimenfiscal(){
  var claveregimenfiscalanterior = $("#claveregimenfiscalanterior").val();
  $("#claveregimenfiscal").val(claveregimenfiscalanterior);
}
//obtener por clave
function obtenertiporelacionporclave(){
  var clavetiporelacionanterior = $("#clavetiporelacionanterior").val();
  var clavetiporelacion = $("#clavetiporelacion").val();
  if(clavetiporelacionanterior != clavetiporelacion){
    if($("#clavetiporelacion").parsley().isValid()){
      $.get(notas_credito_clientes_obtener_tipo_relacion_por_clave, {clavetiporelacion:clavetiporelacion}, function(data){
        $("#clavetiporelacion").val(data.clave);
        $("#clavetiporelacionanterior").val(data.clave);  
        $("#tiporelacion").val(data.nombre);
        if(data.nombre != null){
          $("#textonombretiporelacion").html(data.nombre.substring(0, 40));
        }
        mostrarformulario();
      }) 
    }
  }
}
//regresar clave
function regresarclavetiporelacion(){
  var clavetiporelacionanterior = $("#clavetiporelacionanterior").val();
  $("#clavetiporelacion").val(clavetiporelacionanterior);
}
//obtener por clave
function obtenerformapagoporclave(){
  var claveformapagoanterior = $("#claveformapagoanterior").val();
  var claveformapago = $("#claveformapago").val();
  if(claveformapagoanterior != claveformapago){
    if($("#claveformapago").parsley().isValid()){
      $.get(notas_credito_clientes_obtener_forma_pago_por_clave, {claveformapago:claveformapago}, function(data){
        $("#claveformapago").val(data.clave);
        $("#claveformapagoanterior").val(data.clave);
        $("#formapago").val(data.nombre);
        if(data.nombre != null){
          $("#textonombreformapago").html(data.nombre.substring(0, 40));
        }
        mostrarformulario();
      }) 
    }
  }
}
//regresar clave
function regresarclaveformapago(){
  var claveformapagoanterior = $("#claveformapagoanterior").val();
  $("#claveformapago").val(claveformapagoanterior);
}
//obtener por clave
function obtenermetodopagoporclave(){
  var clavemetodopagoanterior = $("#clavemetodopagoanterior").val();
  var clavemetodopago = $("#clavemetodopago").val();
  if(clavemetodopagoanterior != clavemetodopago){
    if($("#clavemetodopago").parsley().isValid()){
      $.get(notas_credito_clientes_obtener_metodo_pago_por_clave, {clavemetodopago:clavemetodopago}, function(data){
        $("#clavemetodopago").val(data.clave);
        $("#clavemetodopagoanterior").val(data.clave);
        $("#metodopago").val(data.nombre);
        if(data.nombre != null){
          $("#textonombremetodopago").html(data.nombre.substring(0, 40));
        }
        mostrarformulario();
      }) 
    }
  }
}
//regresar clave
function regresarclavemetodopago(){
  var clavemetodopagoanterior = $("#clavemetodopagoanterior").val();
  $("#clavemetodopago").val(clavemetodopagoanterior);
}
//obtener por clave
function obtenerusocfdiporclave(){
  var claveusocfdianterior = $("#claveusocfdianterior").val();
  var claveusocfdi = $("#claveusocfdi").val();
  if(claveusocfdianterior != claveusocfdi){
    if($("#claveusocfdi").parsley().isValid()){
      $.get(notas_credito_clientes_obtener_uso_cfdi_por_clave, {claveusocfdi:claveusocfdi}, function(data){
        $("#claveusocfdi").val(data.clave);
        $("#claveusocfdianterior").val(data.clave);
        $("#usocfdi").val(data.nombre);
        if(data.nombre != null){
          $("#textonombreusocfdi").html(data.nombre.substring(0, 40));
        }
        mostrarformulario();
      }) 
    }
  }
}
//regresar clave
function regresarclaveusocfdi(){
  var claveusocfdianterior = $("#claveusocfdianterior").val();
  $("#claveusocfdi").val(claveusocfdianterior);
}
//obtener por clave
function obtenerresidenciafiscalporclave(){
  var claveresidenciafiscalanterior = $("#claveresidenciafiscalanterior").val();
  var claveresidenciafiscal = $("#claveresidenciafiscal").val();
  if(claveresidenciafiscalanterior != claveresidenciafiscal){
    if($("#claveresidenciafiscal").parsley().isValid()){
      $.get(notas_credito_clientes_obtener_residencia_fiscal_por_clave, {claveresidenciafiscal:claveresidenciafiscal}, function(data){
        $("#claveresidenciafiscal").val(data.clave);
        $("#claveresidenciafiscalanterior").val(data.clave);
        $("#residenciafiscal").val(data.nombre);
        if(data.nombre != null){
          $("#textonombreresidenciafiscal").html(data.nombre.substring(0, 40));
        }
        mostrarformulario();
      }) 
    }
  }
}
//regresar clave
function regresarclaveresidenciafiscal(){
  var claveresidenciafiscalanterior = $("#claveresidenciafiscalanterior").val();
  $("#claveresidenciafiscal").val(claveresidenciafiscalanterior);
}
//obtener por clave
function obtenerconfigautotransporteporclave(){
  var claveconfigautotransporteanterior = $("#claveconfigautotransporteanterior").val();
  var claveconfigautotransporte  = $("#claveconfigautotransporte").val();
  if(claveconfigautotransporteanterior != claveconfigautotransporte){
    if($("#claveconfigautotransporte").parsley().isValid()){
      $.get(carta_porte_obtener_configuracionautotransporte_por_clave, {claveconfigautotransporte:claveconfigautotransporte}, function(data){
        console.log(data);
          $("#claveconfigautotransporte").val(data.clave);
          $("#claveconfigautotransporteanterior").val(data.clave);
          $("#configautotransporte").val(data.descripcion);
          if(data.descripcion != null){
            $("#textonombreconfigautotransporte").html(data.descripcion.substring(0, 40));
          }
          mostrarformulario();
      }) 
    }
  }
}
//regresar clave
function regresarclaveconfigautotransporte(){
  var claveconfigautotransporteanterior = $("#claveconfigautotransporteanterior").val();
  $("#claveconfigautotransporte").val(claveconfigautotransporteanterior);
}
//obtener por clave
function obtenerclavetransporteporclave(){
  var clavetransporteanterior = $("#clavetransporteanterior").val();
  var clavetransporte  = $("#clavetransporte").val();
  if(clavetransporteanterior != clavetransporte){
    if($("#clavetransporte").parsley().isValid()){
      $.get(carta_porte_obtener_clavetransporte_por_clave, {clavetransporte:clavetransporte}, function(data){
          $("#clavetransporte").val(data.clave);
          $("#clavetransporteanterior").val(data.clave);
          $("#nombreclavetransporte").val(data.descripcion);
          if(data.descripcion != null){
            $("#textonombreclavetransporte").html(data.descripcion.substring(0, 40));
          }
          mostrarformulario();
      }) 
    }
  }
}
//regresar clave
function regresarclavetransporte(){
  var clavetransporteanterior = $("#clavetransporteanterior").val();
  $("#clavetransporte").val(clavetransporteanterior);
}
//listar productos para tab consumos
function listarproductos(){
  var numerofilasfacturas = $("#numerofilasfacturas").val();
  var codigoabuscar = $("#codigoabuscar").val().toUpperCase();
  if(parseInt(numerofilasfacturas) > parseInt(1) && codigoabuscar != 'DPPP'){
    msj_errorsolo1factura();
  }else if(parseInt(numerofilasfacturas) >= parseInt(1) && codigoabuscar == 'DPPP'){
    agregarfiladppp();
  }else{
    var almacen = $("#almacen").val();
    if(almacen == ''){
      msj_erroreligeunalmacen();
    }else{
      ocultarformulario();
      var tablaproductos = '<div class="modal-header '+background_forms_and_modals+'">'+
                              '<h4 class="modal-title">Productos</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                              '<div class="row">'+
                                '<div class="col-md-12">'+
                                  '<div class="table-responsive">'+
                                    '<table id="tbllistadoproducto" class="tbllistadoproducto table table-bordered table-striped table-hover" style="width:100% !important">'+
                                      '<thead class="'+background_tables+'">'+
                                        '<tr>'+
                                          '<th>Operaciones</th>'+
                                          '<th>Código</th>'+
                                          '<th>Marca</th>'+
                                          '<th>Producto</th>'+
                                          '<th>Ubicación</th>'+
                                          '<th>Existencias</th>'+
                                          '<th>Almacen</th>'+
                                          '<th>Costo $</th>'+
                                          '<th>Sub Total $</th>'+
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
      $("#contenidomodaltablas").html(tablaproductos);
      var tprod = $('#tbllistadoproducto').DataTable({
        keys: true,
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
          url: notas_credito_clientes_obtener_productos,
          data: function (d) {
            d.codigoabuscar = $("#codigoabuscar").val();
            d.numeroalmacen = $("#numeroalmacen").val();
            d.tipooperacion = $("#tipooperacion").val();
            d.stringfacturasseleccionadas = $("#stringfacturasseleccionadas").val();
          }
        },
        columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false  },
          { data: 'Codigo', name: 'Codigo' },
          { data: 'Marca', name: 'Marca', orderable: false, searchable: false  },
          { data: 'Producto', name: 'Producto' },
          { data: 'Ubicacion', name: 'Ubicacion', orderable: false, searchable: false  },
          { data: 'Existencias', name: 'Existencias', orderable: false, searchable: false  },
          { data: 'Almacen', name: 'Almacen', orderable: false, searchable: false  },
          { data: 'Costo', name: 'Costo', orderable: false, searchable: false  },
          { data: 'SubTotal', name: 'SubTotal', orderable: false, searchable: false  } 
        ],
        "initComplete": function() {
          var $buscar = $('div.dataTables_filter input');
          $buscar.focus();
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoproducto').DataTable().search( this.value ).draw();
            }
          });
        },
      });
      //seleccionar registro al dar doble click
      $('#tbllistadoproducto tbody').on('dblclick', 'tr', function () {
          var data = tprod.row( this ).data();
          var tipooperacion = $("#tipooperacion").val();
          agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Impuesto, data.SubTotal, data.Existencias, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, data.NombreClaveProducto, data.NombreClaveUnidad, data.CostoDeLista);
      });
    }
  }
}
function obtenerproductoporcodigo(){
  var numerofilasfacturas = $("#numerofilasfacturas").val();
  var codigoabuscar = $("#codigoabuscar").val().toUpperCase();
  if(parseInt(numerofilasfacturas) > parseInt(1) && codigoabuscar != 'DPPP'){
    msj_errorsolo1factura();
  }else if(parseInt(numerofilasfacturas) >= parseInt(1) && codigoabuscar == 'DPPP'){
    agregarfiladppp();
  }else{
    var almacen = $("#almacen").val();
    if(almacen == ''){
      msj_erroreligeunalmacen();
    }else{
      var numeroalmacen = $("#numeroalmacen").val();
      var stringfacturasseleccionadas = $("#stringfacturasseleccionadas").val();
      var tipooperacion = $("#tipooperacion").val();
      $.get(notas_credito_clientes_obtener_producto_por_codigo,{codigoabuscar:codigoabuscar,numeroalmacen:numeroalmacen,stringfacturasseleccionadas:stringfacturasseleccionadas}, function(data){
        if(parseInt(data.contarproductos) > 0){
          agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Impuesto, data.SubTotal, data.Existencias, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, data.NombreClaveProducto, data.NombreClaveUnidad, data.CostoDeLista);
        }else{
          msjnoseencontroningunproducto();
        }
      }) 
    }
  }
}
//listar claves productos
function listarclavesproductos(fila){
  ocultarformulario();
  var tablaclavesproducto = '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Claves Productos</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoclaveproducto" class="tbllistadoclaveproducto table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
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
  $("#contenidomodaltablas").html(tablaclavesproducto);
  var tclavprod = $('#tbllistadoclaveproducto').DataTable({
      keys: true,
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
          url: notas_credito_clientes_obtener_claves_productos,
          data: function (d) {
            d.fila = fila;
          }
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre' }
      ],
      "initComplete": function() {
          var $buscar = $('div.dataTables_filter input');
          $buscar.focus();
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoclaveproducto').DataTable().search( this.value ).draw();
              }
          });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoclaveproducto tbody').on('dblclick', 'tr', function () {
      var data = tclavprod.row( this ).data();
      seleccionarclaveproducto(data.Clave, data.Nombre, fila);
  });
}
//seleccion de clave producto
function seleccionarclaveproducto(clave, nombre, fila){
  $("#filaproducto"+fila+" .claveproductopartida").val(clave);
  $("#filaproducto"+fila+" .nombreclaveproductopartida").val(nombre);
  mostrarformulario();
}
//listar claves unidades
function listarclavesunidades(fila){
  ocultarformulario();
  var tablaclavesunidades = '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Claves Unidades</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoclaveunidad" class="tbllistadoclaveunidad table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
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
  $("#contenidomodaltablas").html(tablaclavesunidades);
  var tclavuni = $('#tbllistadoclaveunidad').DataTable({
      keys: true,
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
          url: notas_credito_clientes_obtener_claves_unidades,
          data: function (d) {
            d.fila = fila;
          }
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre' }
      ],
      "initComplete": function() {
          var $buscar = $('div.dataTables_filter input');
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoclaveunidad').DataTable().search( this.value ).draw();
              }
          });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoclaveunidad tbody').on('dblclick', 'tr', function () {
      var data = tclavuni.row( this ).data();
      seleccionarclaveunidad(data.Clave, data.Nombre, fila);
  });
}
//seleccion de clave unidad
function seleccionarclaveunidad(clave, nombre, fila){
  $("#filaproducto"+fila+" .claveunidadpartida").val(clave);
  $("#filaproducto"+fila+" .nombreclaveunidadpartida").val(nombre);
  mostrarformulario();
}
//función que evalua si la partida que quieren ingresar ya existe o no en el detalle de la orden de compra
function evaluarproductoexistente(Codigo){
  var sumaiguales=0;
  var sumadiferentes=0;
  var sumatotal=0;
  $("tr.filasproductos").each(function () {
      var codigoproducto = $('.codigopartida', this).val();
      if(Codigo === codigoproducto){
          sumaiguales++;
      }else{
          sumadiferentes++;
      }
      sumatotal++;
  });
  var resta = parseInt(sumadiferentes) - parseInt(sumaiguales);
  var total = sumatotal;
  if(resta != total){
      var result = true;
  }else{
      var result = false;
  }
  return result;
} 
//eliminar filas dppp
function eliminarfilasdppp(){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
    var codigoproducto = $('.codigopartida', this).val();
    if(codigoproducto == 'DPPP'){
      $("#filaproducto"+cuentaFilas).remove();
      contadorfilas--; //importante para todos los calculo en el modulo de orden de compra se debe restar al contadorfilas la fila que se acaba de eliminar
    }
    cuentaFilas++;
  });
  renumerarfilas();//importante para todos los calculo en el modulo de orden de compra 
  comprobarfilas();
  calculartotal();
}
//eliminar filas codigos
function eliminarfilascodigos(){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
    var codigoproducto = $('.codigopartida', this).val();
    if(codigoproducto != 'DPPP'){
      $("#filaproducto"+cuentaFilas).remove();
      contadorfilas--; //importante para todos los calculo en el modulo de orden de compra se debe restar al contadorfilas la fila que se acaba de eliminar
    }
    cuentaFilas++;
  });
  renumerarfilas();//importante para todos los calculo en el modulo de orden de compra 
  comprobarfilas();
  calculartotal();
}
//agregar una fila en la tabla de precios productos codigo ó dppp
var contadorproductos=0;
var contadorfilas = 0;
function agregarfiladppp(){
  $('.page-loader-wrapper').css('display', 'block');
  var result = evaluarproductoexistente("DPPP");
  if(result == false){
    var tipooperacion = $("#tipooperacion").val();
    var fila= '<tr class="filasproductos" id="filaproducto'+contadorfilas+'">'+
                '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfila" onclick="eliminarfila('+contadorfilas+')" >X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="DPPP" readonly data-parsley-length="[1, 20]">DPPP</td>'+         
                '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="DESCUENTO POR PRONTO PAGO" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'+
                '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxs unidadpartida" name="unidadpartida[]" value="ACTIV" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'+
                '<td class="tdmod">'+
                    '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-min="0.1" data-parsley-max="1.0"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');">'+
                '</td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" ></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="16.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]"  value="0" readonly></td>'+
                '<td class="tdmod">'+
                  '<div class="row divorinputmodxl">'+
                    '<div class="col-xs-2 col-sm-2 col-md-2">'+
                      '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('+contadorfilas+');" ><i class="material-icons">remove_red_eye</i></div>'+
                    '</div>'+
                    '<div class="col-xs-10 col-sm-10 col-md-10">'+    
                      '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="84111506" readonly data-parsley-length="[1, 20]">'+
                    '</div>'+
                  '</div>'+
                '</td>'+
                '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="Servicios de facturación" readonly></td>'+
                '<td class="tdmod">'+
                  '<div class="row divorinputmodxl">'+
                    '<div class="col-xs-2 col-sm-2 col-md-2">'+
                      '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesunidades" data-toggle="tooltip" title="Ver Claves Unidades" onclick="listarclavesunidades('+contadorfilas+');" ><i class="material-icons">remove_red_eye</i></div>'+
                    '</div>'+
                    '<div class="col-xs-10 col-sm-10 col-md-10">'+    
                      '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="ACT" readonly data-parsley-length="[1, 5]">'+
                    '</div>'+
                  '</div>'+
                '</td>'+
                '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="Actividad" readonly></td>'+
              '</tr>';
    contadorproductos++;
    contadorfilas++;
    $("#tabladetallesnotacliente").append(fila);
    mostrarformulario();      
    comprobarfilas();
    calculartotal();
    eliminarfilascodigos();
    //colocar el tipo de detalles
    $("#tipodetalles").val("dppp");
    //colocar almacen 0
    comprobarfilasfacturanotacliente();
    $("#codigoabuscar").val("");
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnextdet").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnextdet");          
      switch(code){
        case 13:
          $(".inputnextdet").eq(index + 1).focus().select(); 
          break;
        case 39:
          $(".inputnextdet").eq(index + 1).focus().select(); 
          break;
        case 37:
          $(".inputnextdet").eq(index - 1).focus().select(); 
          break;
      }
    });
    $('.page-loader-wrapper').css('display', 'none');
  }else{
    msj_errorproductoyaagregado();
    $('.page-loader-wrapper').css('display', 'none');
  }
}
function agregarfilaproducto(Codigo, Producto, Unidad, Costo, Impuesto, SubTotal, Existencias, tipooperacion, Insumo, ClaveProducto, ClaveUnidad, NombreClaveProducto, NombreClaveUnidad, CostoDeLista){
    $('.page-loader-wrapper').css('display', 'block');
    var result = evaluarproductoexistente(Codigo);
    if(result == false){
        var multiplicacioncostoimpuesto =  new Decimal(Costo).times(Impuesto);      
        var ivapesos = new Decimal(multiplicacioncostoimpuesto/100);
        var total = new Decimal(Costo).plus(ivapesos);
        var preciopartida = Costo;
        var tipo = "alta";
        var fila= '<tr class="filasproductos" id="filaproducto'+contadorfilas+'">'+
                    '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfila" onclick="eliminarfila('+contadorfilas+')" >X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                    '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]">'+Codigo+'</td>'+         
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'+Producto+'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'+
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxs unidadpartida" name="unidadpartida[]" value="'+Unidad+'" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'+
                    '<td class="tdmod">'+
                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');revisarcantidadnotavscantidadfactura('+contadorfilas+');">'+
                        '<input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                        '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'+
                        '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'+
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'+
                    '</td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'+preciopartida+'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" ></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'+Impuesto+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'+number_format(round(ivapesos, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'+number_format(round(total, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]" value="0" autocomplete="off"></td>'+
                    '<td class="tdmod">'+
                      '<div class="row divorinputmodxl">'+
                        '<div class="col-xs-2 col-sm-2 col-md-2">'+
                          '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('+contadorfilas+');" ><i class="material-icons">remove_red_eye</i></div>'+
                        '</div>'+
                        '<div class="col-xs-10 col-sm-10 col-md-10">'+    
                          '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'+ClaveProducto+'" readonly data-parsley-length="[1, 20]">'+
                        '</div>'+
                      '</div>'+
                    '</td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'+NombreClaveProducto+'" readonly></td>'+
                    '<td class="tdmod">'+
                      '<div class="row divorinputmodxl">'+
                        '<div class="col-xs-2 col-sm-2 col-md-2">'+
                          '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesunidades" data-toggle="tooltip" title="Ver Claves Unidades" onclick="listarclavesunidades('+contadorfilas+');" ><i class="material-icons">remove_red_eye</i></div>'+
                        '</div>'+
                        '<div class="col-xs-10 col-sm-10 col-md-10">'+    
                          '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'+ClaveUnidad+'" readonly data-parsley-length="[1, 5]">'+
                        '</div>'+
                      '</div>'+
                    '</td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'+NombreClaveUnidad+'" readonly></td>'+
                  '</tr>';
        contadorproductos++;
        contadorfilas++;
        $("#tabladetallesnotacliente").append(fila);
        mostrarformulario();      
        comprobarfilas();
        calculartotal();
        eliminarfilasdppp();
        //colocar el tipo de detalles
        $("#tipodetalles").val("codigos");
        //colocar almacen 0
        comprobarfilasfacturanotacliente();
        $("#codigoabuscar").val("");
        //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
        $(".inputnextdet").keyup(function (e) {
          //recomentable para mayor compatibilidad entre navegadores.
          var code = (e.keyCode ? e.keyCode : e.which);
          var index = $(this).index(".inputnextdet");          
          switch(code){
            case 13:
              $(".inputnextdet").eq(index + 1).focus().select(); 
              break;
            case 39:
              $(".inputnextdet").eq(index + 1).focus().select(); 
              break;
            case 37:
              $(".inputnextdet").eq(index - 1).focus().select(); 
              break;
          }
        });
        $('.page-loader-wrapper').css('display', 'none');
    }else{
        msj_errorproductoyaagregado();
        $('.page-loader-wrapper').css('display', 'none');
    }
}
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Carta Porte');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs =    '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#cartaportetab" data-toggle="tab">Carta Porte</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#origentab" data-toggle="tab">Datos Remitente</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#destinotab" data-toggle="tab">Datos Destinatario</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#autotransportetab" data-toggle="tab">Datos Auto Transporte</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#operadortab" data-toggle="tab">Datos Operador</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="cartaportetab">'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Nota <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp; <b style="color:#F44336 !important;" id="esquematexto"> Esquema: '+esquema+'</b>  <div class="btn btn-xs bg-red waves-effect" id="btnobtenerfoliosnotas" onclick="obtenerfoliosnotas()">Cambiar</div></label>'+
                                        '<input type="text" class="form-control inputnextdet" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                        '<input type="hidden" class="form-control" name="stringfacturasseleccionadas" id="stringfacturasseleccionadas" readonly required>'+
                                        '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                                        '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                                        '<input type="hidden" class="form-control" name="numerofilasfacturas" id="numerofilasfacturas" readonly>'+
                                        '<input type="hidden" class="form-control" name="tipodetalles" id="tipodetalles" readonly>'+
                                        '<input type="hidden" class="form-control" name="diferenciatotales" id="diferenciatotales" readonly required>'+
                                        '<input type="hidden" class="form-control" name="esquema" id="esquema" value="'+esquema+'" readonly data-parsley-length="[1, 10]">'+
                                    '</div>'+  
                                    '<div class="col-md-3">'+
                                      '<label>Cliente <span class="label label-danger" id="textonombrecliente"></span></label>'+
                                      '<table class="col-md-12">'+
                                        '<tr>'+
                                          '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenerclientes" onclick="obtenerclientes()">Seleccionar</div>'+
                                          '</td>'+
                                          '<td>'+
                                            '<div class="form-line">'+
                                              '<input type="text" class="form-control inputnextdet" name="numerocliente" id="numerocliente" required data-parsley-type="integer" autocomplete="off">'+
                                              '<input type="hidden" class="form-control" name="numeroclienteanterior" id="numeroclienteanterior" required data-parsley-type="integer">'+
                                              '<input type="hidden" class="form-control" name="cliente" id="cliente" required readonly>'+
                                              '<input type="hidden" class="form-control" name="rfccliente" id="rfccliente" required readonly>'+
                                            '</div>'+
                                          '</td>'+    
                                        '</tr>'+    
                                      '</table>'+
                                    '</div>'+ 
                                    '<div class="col-md-3">'+
                                        '<label>Fecha</label>'+
                                        '<input type="datetime-local" class="form-control" name="fecha" id="fecha" required  data-parsley-excluded="true" onkeydown="return false">'+
                                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                                        '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                                    '</div>'+   
                                '</div>'+
                                '<div class="row">'+   
                                    '<div class="col-md-2">'+
                                      '<label>Transporte Internacional</label>'+
                                      '<select name="transporteinternacional" id="transporteinternacional" class="form-control select2" style="width:100% !important;" required>'+ 
                                        '<option value="Si">Si</option>'+
                                        '<option value="No" selected>No</option>'+
                                      '</select>'+
                                    '</div>'+
                                    '<div class="col-md-2">'+
                                        '<label>Total Distancia Recorrida KM</label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet" name="totaldistanciarecorrida" id="totaldistanciarecorrida" value="1.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                    '<div class="col-md-2">'+
                                        '<label>Total mercancias</label>'+
                                        '<input type="number" class="form-control inputnextdet" name="numerototalmercancias" id="numerototalmercancias" value="0" required readonly>'+
                                    '</div>'+
                                    '<div class="col-md-3" id="divbuscarcodigoproducto">'+
                                      '<label>Escribe el código y presiona la tecla ENTER</label>'+
                                      '<table class="col-md-12">'+
                                        '<tr>'+
                                          '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver Productos</div>'+
                                          '</td>'+
                                          '<td>'+ 
                                            '<div class="form-line">'+
                                              '<input type="text" class="form-control inputnextdet" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" readonly autocomplete="off">'+
                                            '</div>'+
                                          '</td>'+
                                        '</tr>'+    
                                      '</table>'+
                                    '</div>'+                                 
                                '</div>'+
                            '</div>'+   
                            '<div role="tabpanel" class="tab-pane fade" id="origentab">'+
                                '<div class="row">'+
                                    '<div class="col-md-2">'+
                                        '<label>R.F.C.</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="rfcremitente" id="rfcremitente" value="'+rfcempresa+'"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Nombre</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="nombreremitente" id="nombreremitente" value="'+nombreempresa+'" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-2">'+
                                        '<label>Fecha y Hora Salida</label>'+
                                        '<input type="datetime-local" class="form-control" name="fechasalida" id="fechasalida" required data-parsley-excluded="true" onkeydown="return false">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Calle</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="calleremitente" id="calleremitente" value="'+calleempresa+'" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-1">'+
                                        '<label>Número Ext</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="numeroextremitente" id="numeroextremitente" value="'+noexteriorempresa+'" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-1">'+
                                        '<label>Número Int</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="numerointremitente" id="numerointremitente" value="'+nointeriorempresa+'" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-2">'+
                                        '<label>Colonia</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="coloniaremitente" id="coloniaremitente" value="'+coloniaempresa+'" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-2">'+
                                        '<label>Localidad</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="localidadremitente" id="localidadremitente" value="'+localidadempresa+'" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Referencia</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="referenciaremitente" id="referenciaremitente" value="'+referenciaempresa+'" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                      '<label>Municipio <span class="label label-danger" id="textonombremunicipioremitente"></span></label>'+
                                      '<table class="col-md-12">'+
                                        '<tr>'+
                                          '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenermunicipiosremitente" onclick="obtenermunicipios('+"'remitente'"+')">Seleccionar</div>'+
                                          '</td>'+
                                          '<td>'+
                                            '<div class="form-line">'+
                                              '<input type="hidden" class="form-control" name="numeromunicipioremitente" id="numeromunicipioremitente" required data-parsley-type="integer" autocomplete="off">'+
                                              '<input type="hidden" class="form-control" name="numeromunicipioremitenteanterior" id="numeromunicipioremitenteanterior" required data-parsley-type="integer">'+
                                              '<input type="text" class="form-control inputnextdet" name="municipioremitente" id="municipioremitente" value="'+municipioempresa+'" required>'+
                                            '</div>'+
                                          '</td>'+    
                                        '</tr>'+    
                                      '</table>'+
                                    '</div>'+ 
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                      '<label>Estado <span class="label label-danger" id="textonombreestadoremitente"></span></label>'+
                                      '<table class="col-md-12">'+
                                        '<tr>'+
                                          '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenerestadosremitente" onclick="obtenerestados('+"'remitente'"+')">Seleccionar</div>'+
                                          '</td>'+
                                          '<td>'+
                                            '<div class="form-line">'+
                                              '<input type="hidden" class="form-control" name="numeroestadoremitente" id="numeroestadoremitente" required data-parsley-type="integer" autocomplete="off">'+
                                              '<input type="hidden" class="form-control" name="numeroestadoremitenteanterior" id="numeroestadoremitenteanterior" required data-parsley-type="integer">'+
                                              '<input type="text" class="form-control inputnextdet" name="estadoremitente" id="estadoremitente" value="'+estadoempresa+'" required>'+
                                            '</div>'+
                                          '</td>'+    
                                        '</tr>'+    
                                      '</table>'+
                                    '</div>'+ 
                                    '<div class="col-md-3">'+
                                      '<label>Pais <span class="label label-danger" id="textonombrepaisremitente"></span></label>'+
                                      '<table class="col-md-12">'+
                                        '<tr>'+
                                          '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenerpaisesremitente" onclick="obtenerpaises('+"'remitente'"+')">Seleccionar</div>'+
                                          '</td>'+
                                          '<td>'+
                                            '<div class="form-line">'+
                                              '<input type="hidden" class="form-control" name="numeropaisremitente" id="numeropaisremitente" required data-parsley-type="integer" autocomplete="off">'+
                                              '<input type="hidden" class="form-control" name="numeropaisremitenteanterior" id="numeropaisremitenteanterior" required data-parsley-type="integer">'+
                                              '<input type="text" class="form-control inputnextdet" name="paisremitente" id="paisremitente" value="'+paisempresa+'" required>'+
                                            '</div>'+
                                          '</td>'+    
                                        '</tr>'+    
                                      '</table>'+
                                    '</div>'+ 
                                    '<div class="col-md-3">'+
                                      '<label>Código Postal <span class="label label-danger" id="textonombrecpremitente"></span></label>'+
                                      '<table class="col-md-12">'+
                                        '<tr>'+
                                          '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenercpsremitente" onclick="obtenercps('+"'remitente'"+')">Seleccionar</div>'+
                                          '</td>'+
                                          '<td>'+
                                            '<div class="form-line">'+
                                              '<input type="hidden" class="form-control" name="numerocpremitente" id="numerocpremitente" required data-parsley-type="integer" autocomplete="off">'+
                                              '<input type="hidden" class="form-control" name="numerocpremitenteanterior" id="numerocpremitenteanterior" required data-parsley-type="integer">'+
                                              '<input type="text" class="form-control inputnextdet" name="cpremitente" id="cpremitente" value="'+cpempresa+'" required>'+
                                            '</div>'+
                                          '</td>'+    
                                        '</tr>'+    
                                      '</table>'+
                                    '</div>'+ 
                                '</div>'+
                            '</div>'+ 
                            '<div role="tabpanel" class="tab-pane fade" id="destinotab">'+
                              '<div class="row">'+
                                  '<div class="col-md-2">'+
                                      '<label>R.F.C.</label>'+
                                      '<input type="text" class="form-control inputnextdet" name="rfcdestinatario" id="rfcdestinatario"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                  '</div>'+
                                  '<div class="col-md-4">'+
                                      '<label>Nombre</label>'+
                                      '<input type="text" class="form-control inputnextdet" name="nombredestinatario" id="nombredestinatario" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                  '</div>'+
                                  '<div class="col-md-2">'+
                                      '<label>Fecha y Hora Llegada</label>'+
                                      '<input type="datetime-local" class="form-control" name="fechallegada" id="fechallegada" required  data-parsley-excluded="true" onkeydown="return false">'+
                                  '</div>'+
                                  '<div class="col-md-4">'+
                                      '<label>Calle</label>'+
                                      '<input type="text" class="form-control inputnextdet" name="calledestinatario" id="calledestinatario" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                  '</div>'+
                              '</div>'+
                              '<div class="row">'+
                                  '<div class="col-md-1">'+
                                      '<label>Número Ext</label>'+
                                      '<input type="text" class="form-control inputnextdet" name="numeroextdestinatario" id="numeroextdestinatario" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                  '</div>'+
                                  '<div class="col-md-1">'+
                                      '<label>Número Int</label>'+
                                      '<input type="text" class="form-control inputnextdet" name="numerointdestinatario" id="numerointdestinatario" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                  '</div>'+
                                  '<div class="col-md-2">'+
                                      '<label>Colonia</label>'+
                                      '<input type="text" class="form-control inputnextdet" name="coloniadestinatario" id="coloniadestinatario" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                  '</div>'+
                                  '<div class="col-md-2">'+
                                      '<label>Localidad</label>'+
                                      '<input type="text" class="form-control inputnextdet" name="localidaddestinatario" id="localidaddestinatario" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                  '</div>'+
                                  '<div class="col-md-3">'+
                                      '<label>Referencia</label>'+
                                      '<input type="text" class="form-control inputnextdet" name="referenciadestinatario" id="referenciadestinatario" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                  '</div>'+
                                  '<div class="col-md-3">'+
                                    '<label>Municipio <span class="label label-danger" id="textonombremunicipiodestinatario"></span></label>'+
                                    '<table class="col-md-12">'+
                                      '<tr>'+
                                        '<td>'+
                                          '<div class="btn bg-blue waves-effect" id="btnobtenermunicipiosdestinatario" onclick="obtenermunicipios('+"'destinatario'"+')">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                          '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="numeromunicipiodestinatario" id="numeromunicipiodestinatario" required data-parsley-type="integer" autocomplete="off">'+
                                            '<input type="hidden" class="form-control" name="numeromunicipiodestinatarioanterior" id="numeromunicipiodestinatarioanterior" required data-parsley-type="integer">'+
                                            '<input type="text" class="form-control inputnextdet" name="municipiodestinatario" id="municipiodestinatario" required>'+
                                          '</div>'+
                                        '</td>'+    
                                      '</tr>'+    
                                    '</table>'+
                                  '</div>'+ 
                              '</div>'+
                              '<div class="row">'+
                                  '<div class="col-md-3">'+
                                    '<label>Estado <span class="label label-danger" id="textonombreestadodestinatario"></span></label>'+
                                    '<table class="col-md-12">'+
                                      '<tr>'+
                                        '<td>'+
                                          '<div class="btn bg-blue waves-effect" id="btnobtenerestadosdestinatario" onclick="obtenerestados('+"'destinatario'"+')">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                          '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="numeroestadodestinatario" id="numeroestadodestinatario" required data-parsley-type="integer" autocomplete="off">'+
                                            '<input type="hidden" class="form-control" name="numeroestadodestinatarioanterior" id="numeroestadodestinatarioanterior" required data-parsley-type="integer">'+
                                            '<input type="text" class="form-control inputnextdet" name="estadodestinatario" id="estadodestinatario" required>'+
                                          '</div>'+
                                        '</td>'+    
                                      '</tr>'+    
                                    '</table>'+
                                  '</div>'+ 
                                  '<div class="col-md-3">'+
                                    '<label>Pais <span class="label label-danger" id="textonombrepaisdestinatario"></span></label>'+
                                    '<table class="col-md-12">'+
                                      '<tr>'+
                                        '<td>'+
                                          '<div class="btn bg-blue waves-effect" id="btnobtenerpaisesdestinatario" onclick="obtenerpaises('+"'destinatario'"+')">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                          '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="numeropaisdestinatario" id="numeropaisdestinatario" required data-parsley-type="integer" autocomplete="off">'+
                                            '<input type="hidden" class="form-control" name="numeropaisdestinatarioanterior" id="numeropaisdestinatarioanterior" required data-parsley-type="integer">'+
                                            '<input type="text" class="form-control inputnextdet" name="paisdestinatario" id="paisdestinatario" required>'+
                                          '</div>'+
                                        '</td>'+    
                                      '</tr>'+    
                                    '</table>'+
                                  '</div>'+ 
                                  '<div class="col-md-3">'+
                                    '<label>Código Postal <span class="label label-danger" id="textonombrecpdestinatario"></span></label>'+
                                    '<table class="col-md-12">'+
                                      '<tr>'+
                                        '<td>'+
                                          '<div class="btn bg-blue waves-effect" id="btnobtenercpsdestinatario" onclick="obtenercps('+"'destinatario'"+')">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                          '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="numerocpdestinatario" id="numerocpdestinatario" required data-parsley-type="integer" autocomplete="off">'+
                                            '<input type="hidden" class="form-control" name="numerocpdestinatarioanterior" id="numerocpdestinatarioanterior" required data-parsley-type="integer">'+
                                            '<input type="text" class="form-control inputnextdet" name="cpdestinatario" id="cpdestinatario" required>'+
                                          '</div>'+
                                        '</td>'+    
                                      '</tr>'+    
                                    '</table>'+
                                  '</div>'+ 
                              '</div>'+
                            '</div>'+
                            '<div role="tabpanel" class="tab-pane fade" id="autotransportetab">'+
                              '<div class="row">'+
                                '<div class="col-md-4">'+
                                  '<label>Vehiculo Empresa <span class="label label-danger" id="textonombrevehiculoempresa"></span></label>'+
                                  '<table class="col-md-12">'+
                                    '<tr>'+
                                      '<td>'+
                                        '<div class="btn bg-blue waves-effect" id="btnobtenervehiculosempresa" onclick="obtenervehiculosempresa()">Seleccionar</div>'+
                                      '</td>'+
                                      '<td>'+
                                        '<div class="form-line">'+
                                          '<input type="text" class="form-control inputnextdet" name="numerovehiculoempresa" id="numerovehiculoempresa" required data-parsley-type="integer" autocomplete="off">'+
                                          '<input type="hidden" class="form-control" name="numerovehiculoempresaanterior" id="numerovehiculoempresaanterior" required data-parsley-type="integer">'+
                                          '<input type="hidden" class="form-control" name="vehiculoempresa" id="vehiculoempresa" required readonly>'+
                                        '</div>'+
                                      '</td>'+    
                                    '</tr>'+    
                                  '</table>'+
                                '</div>'+ 
                                '<div class="col-md-2">'+
                                    '<label>Permiso de la SCT</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="permisosct" id="permisosct" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<label>Número de Permiso de la SCT</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="numeropermisosct" id="numeropermisosct" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<label>Nombre de Aseguradora</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="nombreaseguradora" id="nombreaseguradora" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<label>Número de Póliza de Seguro</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="numeropolizaseguro" id="numeropolizaseguro" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                              '</div>'+
                              '<div class="row">'+
                                '<div class="col-md-3">'+
                                  '<label>Configuración Vehicular <span class="label label-danger" id="textonombreconfigautotransporte"></span></label>'+
                                  '<table class="col-md-12">'+
                                    '<tr>'+
                                      '<td>'+
                                        '<div class="btn bg-blue waves-effect" id="btnobtenerconfigautotransporte" onclick="obtenerconfigautotransporte()">Seleccionar</div>'+
                                      '</td>'+
                                      '<td>'+
                                        '<div class="form-line">'+
                                          '<input type="text" class="form-control inputnextdet" name="claveconfigautotransporte" id="claveconfigautotransporte" required autocomplete="off" onkeyup="tipoLetra(this);">'+
                                          '<input type="hidden" class="form-control" name="claveconfigautotransporteanterior" id="claveconfigautotransporteanterior" required data-parsley-type="integer">'+
                                          '<input type="hidden" class="form-control" name="configautotransporte" id="configautotransporte" required readonly>'+
                                        '</div>'+
                                      '</td>'+    
                                    '</tr>'+    
                                  '</table>'+
                                '</div>'+ 
                                '<div class="col-md-2">'+
                                    '<label>Placa Vehículo Motor</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="placavehiculo" id="placavehiculo" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<label>Año Modelo Vehículo Motor</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="anovehiculo" id="anovehiculo" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<label>SubTipo de Remolque</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="subtiporemolque" id="subtiporemolque" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<label>Placa</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="placaremolque" id="placaremolque" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+  
                              '</div>'+
                            '</div>'+
                            '<div role="tabpanel" class="tab-pane fade" id="operadortab">'+
                              '<div class="row">'+
                                '<div class="col-md-3">'+
                                  '<label>Operador <span class="label label-danger" id="textonombreoperador"></span></label>'+
                                  '<table class="col-md-12">'+
                                    '<tr>'+
                                      '<td>'+
                                        '<div class="btn bg-blue waves-effect" id="btnobteneroperadores" onclick="obteneroperadores()">Seleccionar</div>'+
                                      '</td>'+
                                      '<td>'+
                                        '<div class="form-line">'+
                                          '<input type="text" class="form-control inputnextdet" name="numerooperador" id="numerooperador" required data-parsley-type="integer" autocomplete="off">'+
                                          '<input type="hidden" class="form-control" name="numerooperadoranterior" id="numerooperadoranterior" required data-parsley-type="integer">'+
                                          '<input type="hidden" class="form-control" name="operador" id="operador" required readonly>'+
                                        '</div>'+
                                      '</td>'+    
                                    '</tr>'+    
                                  '</table>'+
                                '</div>'+ 
                                '<div class="col-md-3">'+
                                  '<label>Clave de Transporte <span class="label label-danger" id="textonombreclavetransporte"></span></label>'+
                                  '<table class="col-md-12">'+
                                    '<tr>'+
                                      '<td>'+
                                        '<div class="btn bg-blue waves-effect" id="btnobtenerclavestransporte" onclick="obtenerclavestransporte()">Seleccionar</div>'+
                                      '</td>'+
                                      '<td>'+
                                        '<div class="form-line">'+
                                          '<input type="text" class="form-control inputnextdet" name="clavetransporte" id="clavetransporte" required autocomplete="off">'+
                                          '<input type="hidden" class="form-control" name="clavetransporteanterior" id="clavetransporteanterior" required data-parsley-type="integer">'+
                                          '<input type="hidden" class="form-control" name="nombreclavetransporte" id="nombreclavetransporte" required readonly>'+
                                        '</div>'+
                                      '</td>'+    
                                    '</tr>'+    
                                  '</table>'+
                                '</div>'+ 
                                '<div class="col-md-2">'+
                                    '<label>R.F.C.</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="rfcoperador" id="rfcoperador" required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<label>Nombre</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="nombreoperador" id="nombreoperador" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<label>Número de Licencia</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="numerolicenciaoperador" id="numerolicenciaoperador" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                              '</div>'+
                              '<div class="row">'+
                                '<div class="col-md-4">'+
                                    '<label>Calle</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="calleoperador" id="calleoperador" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-1">'+
                                    '<label>Número Ext</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="numeroextoperador" id="numeroextoperador" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-1">'+
                                    '<label>Número Int</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="numerointoperador" id="numerointoperador" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<label>Colonia</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="coloniaoperador" id="coloniaoperador" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<label>Localidad</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="localidadoperador" id="localidadoperador" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<label>Referencia</label>'+
                                    '<input type="text" class="form-control inputnextdet" name="referenciaoperador" id="referenciaoperador" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                              '</div>'+
                              '<div class="row">'+
                              '<div class="col-md-3">'+
                                '<label>Municipio <span class="label label-danger" id="textonombremunicipiooperador"></span></label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" id="btnobtenermunicipiosoperador" onclick="obtenermunicipios('+"'operador'"+')">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="numeromunicipiooperador" id="numeromunicipiooperador" required data-parsley-type="integer" autocomplete="off">'+
                                        '<input type="hidden" class="form-control" name="numeromunicipiooperadoranterior" id="numeromunicipiooperadoranterior" required data-parsley-type="integer">'+
                                        '<input type="text" class="form-control inputnextdet" name="municipiooperador" id="municipiooperador" required>'+
                                      '</div>'+
                                    '</td>'+    
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+ 
                              '<div class="col-md-3">'+
                                '<label>Estado <span class="label label-danger" id="textonombreestadooperador"></span></label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" id="btnobtenerestadosoperador" onclick="obtenerestados('+"'operador'"+')">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="numeroestadooperador" id="numeroestadooperador" required data-parsley-type="integer" autocomplete="off">'+
                                        '<input type="hidden" class="form-control" name="numeroestadooperadoranterior" id="numeroestadooperadoranterior" required data-parsley-type="integer">'+
                                        '<input type="text" class="form-control inputnextdet" name="estadooperador" id="estadooperador"  required>'+
                                      '</div>'+
                                    '</td>'+    
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+ 
                              '<div class="col-md-3">'+
                                '<label>Pais <span class="label label-danger" id="textonombrepaisoperador"></span></label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" id="btnobtenerpaisesoperador" onclick="obtenerpaises('+"'operador'"+')">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="numeropaisoperador" id="numeropaisoperador" required data-parsley-type="integer" autocomplete="off">'+
                                        '<input type="hidden" class="form-control" name="numeropaisoperadoranterior" id="numeropaisoperadoranterior" required data-parsley-type="integer">'+
                                        '<input type="text" class="form-control inputnextdet" name="paisoperador" id="paisoperador"  required>'+
                                      '</div>'+
                                    '</td>'+    
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+ 
                              '<div class="col-md-3">'+
                                '<label>Código Postal <span class="label label-danger" id="textonombrecpoperador"></span></label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" id="btnobtenercpsoperador" onclick="obtenercps('+"'operador'"+')">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="numerocpoperador" id="numerocpoperador" required data-parsley-type="integer" autocomplete="off">'+
                                        '<input type="hidden" class="form-control" name="numerocpoperadoranterior" id="numerocpoperadoranterior" required data-parsley-type="integer">'+
                                        '<input type="text" class="form-control inputnextdet" name="cpoperador" id="cpoperador" required>'+
                                      '</div>'+
                                    '</td>'+    
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+ 
                            '</div>'+
                          '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#productostab" data-toggle="tab">Códigos</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                                '<div class="row">'+
                                    '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                                        '<table id="tabladetallesnotacliente" class="table table-bordered tabladetallesnotacliente">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                  '<th class="'+background_tables+'">#</th>'+
                                                  '<th class="'+background_tables+'">Código</th>'+
                                                  '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                                                  '<th class="customercolortheadth">Uda</th>'+
                                                  '<th class="customercolortheadth">Cantidad</th>'+
                                                  '<th class="customercolortheadth">Precio $</th>'+
                                                  '<th class="'+background_tables+'">Importe $</th>'+
                                                  '<th class="customercolortheadth" hidden>Dcto %</th>'+
                                                  '<th class="customercolortheadth" hidden>Dcto $</th>'+
                                                  '<th class="'+background_tables+'" hidden>Importe Descuento $</th>'+
                                                  '<th class="customercolortheadth" hidden>Ieps %</th>'+
                                                  '<th class="'+background_tables+'" hidden>Traslado Ieps $</th>'+
                                                  '<th class="'+background_tables+'">SubTotal $</th>'+
                                                  '<th class="customercolortheadth">Iva %</th>'+
                                                  '<th class="'+background_tables+'">Traslado Iva $</th>'+
                                                  '<th class="customercolortheadth" hidden>Retención Iva %</th>'+
                                                  '<th class="'+background_tables+'" hidden>Retención Iva $</th>'+
                                                  '<th class="customercolortheadth" hidden>Retención Isr %</th>'+
                                                  '<th class="'+background_tables+'" hidden>Retención Isr $</th>'+
                                                  '<th class="customercolortheadth" hidden>Retención Ieps %</th>'+
                                                  '<th class="'+background_tables+'" hidden>Retención Ieps $</th>'+
                                                  '<th class="'+background_tables+'">Total $</th>'+
                                                  '<th class="customercolortheadth">Material Peligroso</th>'+
                                                  '<th class="customercolortheadth">Peso en KG</th>'+
                                                  '<th class="customercolortheadth">Moneda</th>'+
                                                  '<th class="customercolortheadth">ClaveProducto</th>'+
                                                  '<th class="'+background_tables+'">Nombre ClaveProducto</th>'+
                                                  '<th class="customercolortheadth">ClaveUnidad</th>'+
                                                  '<th class="'+background_tables+'">Nombre ClaveUnidad</th>'+
                                                '</tr>'+
                                            '</thead>'+
                                            '<tbody>'+           
                                            '</tbody>'+
                                        '</table>'+
                                    '</div>'+
                                '</div>'+ 
                            '</div>'+ 
                        '</div>'+
                        '<div class="row">'+
                          '<div class="col-md-6">'+   
                              '<label>Observaciones</label>'+
                              '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" rows="5" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
                          '</div>'+ 
                          '<div class="col-md-3">'+
                          '</div>'+
                          '<div class="col-md-3">'+
                              '<table class="table table-striped table-hover">'+
                                  '<tr>'+
                                      '<td class="tdmod">Importe</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr hidden>'+
                                      '<td class="tdmod">Descuento</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td class="tdmod">SubTotal</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td class="tdmod">Iva</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td class="tdmod">Total</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                              '</table>'+
                          '</div>'+
                        '</div>'+
                        '<div class="row">'+
                          '<div class="col-md-12">'+   
                            '<h5 id="mensajecalculoscompra"></h5>'+  
                          '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>';
  $("#tabsform").html(tabs);
  //colocar autocomplette off  todo el formulario
  $(".form-control").attr('autocomplete','off');
  obtenultimonumero();
  asignarfechaactual();
  //asignar el tipo de operacion que se realizara
  $("#tipooperacion").val("alta");
  //se debe motrar el input para buscar los productos
  $("#divbuscarcodigoproducto").show();
  //activar los input select
  $("#moneda").select2();
  //reiniciar contadores
  contadorproductos=0;
  contadorfilas = 0;
  contadorfilasfacturas = 0;
  //activar busqueda de codigos
  $("#codigoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerproductoporcodigo();
    }
  });
  //activar busqueda para clientes
  $('#numerocliente').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerclientepornumero();
    }
  });
  //regresar numero cliente
  $('#numerocliente').on('change', function(e) {
    regresarnumerocliente();
  });
  //activar busqueda para almacenes
  $('#numeroalmacen').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obteneralmacenpornumero();
    }
  });
  //regresar numero almacen
  $('#numeroalmacen').on('change', function(e) {
    regresarnumeroalmacen();
  });
  //activar busqueda para lugar expedicion
  $('#lugarexpedicion').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerlugarexpedicionporclave();
    }
  });
  //regresar clave
  $('#lugarexpedicion').on('change', function(e) {
    regresarclavelugarexpedicion();
  });
  //activar busqueda para regimen fiscal
  $('#claveregimenfiscal').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerregimenfiscalporclave();
    }
  });
  //regresar clave
  $('#claveregimenfiscal').on('change', function(e) {
    regresarclaveregimenfiscal();
  });
  //activar busqueda para tipo relacion
  $('#clavetiporelacion').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenertiporelacionporclave();
    }
  });
  //regresar clave
  $('#clavetiporelacion').on('change', function(e) {
    regresarclavetiporelacion();
  });
  //activar busqueda para forma pago
  $('#claveformapago').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerformapagoporclave();
    }
  });
  //regresar clave
  $('#claveformapago').on('change', function(e) {
    regresarclaveformapago();
  });
  //activar busqueda para metodo pago
  $('#clavemetodopago').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenermetodopagoporclave();
    }
  });
  //regresar clave
  $('#clavemetodopago').on('change', function(e) {
    regresarclavemetodopago();
  });
  //activar busqueda para uso cfdi
  $('#claveusocfdi').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerusocfdiporclave();
    }
  });
  //regresar clave
  $('#claveusocfdi').on('change', function(e) {
    regresarclaveusocfdi();
  });
  //activar busqueda para residencia fiscal
  $('#claveresidenciafiscal').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerresidenciafiscalporclave();
    }
  });
  //regresar clave
  $('#claveresidenciafiscal').on('change', function(e) {
    regresarclaveresidenciafiscal();
  });
  //activar busqueda para residencia fiscal
  $('#claveconfigautotransporte').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerconfigautotransporteporclave();
    }
  });
  //regresar clave
  $('#claveconfigautotransporte').on('change', function(e) {
    regresarclaveconfigautotransporte();
  });
  //activar busqueda
  $('#numerovehiculoempresa').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenervehiculopornumero();
    }
  });
  //regresar
  $('#numerovehiculoempresa').on('change', function(e) {
    regresarnumerovehiculo();
  });
  //activar busqueda
  $('#numerooperador').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obteneroperadorpornumero();
    }
  });
  //regresar
  $('#numerooperador').on('change', function(e) {
    regresarnumerooperador();
  });
  //activar busqueda para residencia fiscal
  $('#clavetransporte').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerclavetransporteporclave();
    }
  });
  //regresar clave
  $('#clavetransporte').on('change', function(e) {
    regresarclavetransporte();
  });
  //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
  $(".inputnextdet").keyup(function (e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    var index = $(this).index(".inputnextdet");          
    switch(code){
      case 13:
        $(".inputnextdet").eq(index + 1).focus().select(); 
        break;
      case 39:
        $(".inputnextdet").eq(index + 1).focus().select(); 
        break;
      case 37:
        $(".inputnextdet").eq(index - 1).focus().select(); 
        break;
    }
  });
  setTimeout(function(){$("#folio").focus();},500);
  $("#ModalAlta").modal('show');
}
//calcular total de la orden de compra
function calculartotalesfilas(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
    if(fila === cuentaFilas){
      // obtener los datos de la fila:
      var cantidadpartida = $(".cantidadpartida", this).val();
      var preciopartida = $('.preciopartida', this).val();
      var importepartida = $('.importepartida', this).val(); 
      var descuentopesospartida = $('.descuentopesospartida', this).val();
      var importedescuentopesospartida = $('.importedescuentopesospartida', this).val();
      var iepsporcentajepartida = $(".iepsporcentajepartida", this).val();
      var trasladoiepspesospartida = $(".trasladoiepspesospartida", this).val();
      var subtotalpartida = $(".subtotalpartida", this).val();
      var ivaporcentajepartida = $('.ivaporcentajepartida', this).val();
      var trasladoivapesospartida = $('.trasladoivapesospartida', this).val();
      var retencionivaporcentajepartida = $(".retencionivaporcentajepartida", this).val();
      var retencionivapesospartida = $(".retencionivapesospartida", this).val();
      var retencionisrporcentajepartida = $(".retencionisrporcentajepartida", this).val();
      var retencionisrpesospartida = $(".retencionisrpesospartida", this).val();
      var retencioniepsporcentajepartida = $(".retencioniepsporcentajepartida", this).val();
      var retencioniepspesospartida = $(".retencioniepspesospartida", this).val();
      var totalpesospartida = $('.totalpesospartida', this).val(); 
      //importe de la partida
      importepartida =  new Decimal(cantidadpartida).times(preciopartida);
      $('.importepartida', this).val(number_format(round(importepartida, numerodecimales), numerodecimales, '.', ''));
      //descuento porcentaje
      var multiplicaciondescuentoporcentajepartida  =  new Decimal(descuentopesospartida).times(100);
      if(multiplicaciondescuentoporcentajepartida.d[0] > parseInt(0)){
        var descuentoporcentajepartida = new Decimal(multiplicaciondescuentoporcentajepartida/importepartida);
        $('.descuentoporcentajepartida', this).val(number_format(round(descuentoporcentajepartida, numerodecimales), numerodecimales, '.', ''));
      }      
      //importe menos descuento de la partida
      importedescuentopesospartida =  new Decimal(importepartida).minus(descuentopesospartida);
      $('.importedescuentopesospartida', this).val(number_format(round(importedescuentopesospartida, numerodecimales), numerodecimales, '.', ''));
      //ieps partida
      var multiplicaciontrasladoiepspesospartida = new Decimal(importedescuentopesospartida).times(iepsporcentajepartida);
      trasladoiepspesospartida = new Decimal(multiplicaciontrasladoiepspesospartida/100);
      $('.trasladoiepspesospartida', this).val(number_format(round(trasladoiepspesospartida, numerodecimales), numerodecimales, '.', ''));
      //subtotal partida
      subtotalpartida = new Decimal(importedescuentopesospartida).plus(trasladoiepspesospartida);
      $(".subtotalpartida", this).val(number_format(round(subtotalpartida, numerodecimales), numerodecimales, '.', ''));
      //iva en pesos de la partida
      var multiplicacionivapesospartida = new Decimal(subtotalpartida).times(ivaporcentajepartida);
      trasladoivapesospartida = new Decimal(multiplicacionivapesospartida/100);
      $('.trasladoivapesospartida', this).val(number_format(round(trasladoivapesospartida, numerodecimales), numerodecimales, '.', ''));
      //retencion iva partida
      var multiplicacionretencionivapesospartida = new Decimal(subtotalpartida).times(retencionivaporcentajepartida);
      retencionivapesospartida = new Decimal(multiplicacionretencionivapesospartida/100);
      $('.retencionivapesospartida', this).val(number_format(round(retencionivapesospartida, numerodecimales), numerodecimales, '.', ''));
      //retencion isr partida
      var multiplicacionretencionisrpesospartida = new Decimal(subtotalpartida).times(retencionisrporcentajepartida);
      retencionisrpesospartida = new Decimal(multiplicacionretencionisrpesospartida/100);
      $('.retencionisrpesospartida', this).val(number_format(round(retencionisrpesospartida, numerodecimales), numerodecimales, '.', ''));
      //retencion ieps partida
      var multiplicacionretencioniepspesospartida = new Decimal(subtotalpartida).times(retencioniepsporcentajepartida);
      retencioniepspesospartida = new Decimal(multiplicacionretencioniepspesospartida/100);
      $('.retencioniepspesospartida', this).val(number_format(round(retencioniepspesospartida, numerodecimales), numerodecimales, '.', ''));
      //total en pesos de la partida
      var subtotalmastrasladoivapartida = new Decimal(subtotalpartida).plus(trasladoivapesospartida);
      var menosretencionivapesospartida = new Decimal(subtotalmastrasladoivapartida).minus(retencionivapesospartida);
      var menosretencionisrpesospartida = new Decimal(menosretencionivapesospartida).minus(retencionisrpesospartida);
      var menosretencioniepspesospartida = new Decimal(menosretencionisrpesospartida).minus(retencioniepspesospartida);
      totalpesospartida = new Decimal(menosretencioniepspesospartida);
      $('.totalpesospartida', this).val(truncar(totalpesospartida, numerodecimales).toFixed(parseInt(numerodecimales)));
      calculartotal();
      calculartotalcompranotacliente();
      //asignar el traslado traslado iva partida
      $(".trasladoivapartida", this).val(ivaporcentajepartida+',Tasa');
    }  
    cuentaFilas++;
  });
}
//dejar en 0 los descuentos cuando el precio de la partida se cambie
function cambiodecantidadopreciopartida(fila){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
    if(fila === cuentaFilas){  
      calculardescuentoporcentajepartida(fila);
      calculartotalesfilas(fila);
      calculartotal();
    }  
    cuentaFilas++;
  });  
}
//calcular el porcentaje de descuento cuando el descuento en pesos se modifique
function calculardescuentoporcentajepartida(fila){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
    if(fila === cuentaFilas){  
      //descuento en porcentaje de la partida
      var importepartida = $('.importepartida', this).val(); 
      var descuentopesospartida = $('.descuentopesospartida', this).val(); 
      var multiplicaciondescuentoporcentajepartida  =  new Decimal(descuentopesospartida).times(100);
      if(multiplicaciondescuentoporcentajepartida.d[0] > parseInt(0)){
        var descuentoporcentajepartida = new Decimal(multiplicaciondescuentoporcentajepartida/importepartida);
        $('.descuentoporcentajepartida', this).val(number_format(round(descuentoporcentajepartida, numerodecimales), numerodecimales, '.', ''));
        calculartotalesfilas(fila);
        calculartotal();
      }
    }  
    cuentaFilas++;
  });    
}
//calcular el descuento en pesos cuando hay cambios en el porcentaje de descuento
function calculardescuentopesospartida(fila){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
    if(fila === cuentaFilas){   
      //descuento en pesos de la partida
      var importepartida = $('.importepartida', this).val();
      var descuentoporcentajepartida = $('.descuentoporcentajepartida', this).val();
      var multiplicaciondescuentopesospartida  =  new Decimal(importepartida).times(descuentoporcentajepartida);
      if(multiplicaciondescuentopesospartida.d[0] > parseInt(0)){
        var descuentopesospartida = new Decimal(multiplicaciondescuentopesospartida/100);
        $('.descuentopesospartida', this).val(number_format(round(descuentopesospartida, numerodecimales), numerodecimales, '.', ''));
        calculartotalesfilas(fila);
        calculartotal();
      }
    }  
    cuentaFilas++;
  }); 
}      
//calcular totales de orden de compra
function calculartotal(){
  var importe = 0;
  var descuento = 0;
  var subtotal= 0;
  var iva = 0;
  var total = 0;
  var totalnota = 0;
  var ieps = 0;
  var retencioniva = 0;
  var retencionisr = 0;
  var retencionieps = 0;
  $("tr.filasproductos").each(function(){
    importe = new Decimal(importe).plus($(".importepartida", this).val());
    descuento = new Decimal(descuento).plus($(".descuentopesospartida", this).val());
    subtotal = new Decimal(subtotal).plus($(".subtotalpartida", this).val());
    iva = new Decimal(iva).plus($(".trasladoivapesospartida", this).val());
    total = new Decimal(total).plus($(".totalpesospartida", this).val());
    totalnota = new Decimal(total).plus($(".totalpesospartida", this).val());
    ieps = new Decimal(ieps).plus($(".trasladoiepspesospartida", this).val());
    retencioniva = new Decimal(retencioniva).plus($(".retencionivapesospartida", this).val());
    retencionisr = new Decimal(retencionisr).plus($(".retencionisrpesospartida", this).val());
    retencionieps = new Decimal(retencionieps).plus($(".retencioniepspesospartida", this).val());
  }); 
  $("#importe").val(number_format(round(importe, numerodecimales), numerodecimales, '.', ''));
  $("#descuento").val(number_format(round(descuento, numerodecimales), numerodecimales, '.', ''));
  $("#subtotal").val(number_format(round(subtotal, numerodecimales), numerodecimales, '.', ''));
  $("#iva").val(number_format(round(iva, numerodecimales), numerodecimales, '.', ''));
  $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
  $("#totalnota").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
  $("#ieps").val(number_format(round(ieps, numerodecimales), numerodecimales, '.', ''));
  $("#retencioniva").val(number_format(round(retencioniva, numerodecimales), numerodecimales, '.', ''));
  $("#retencionisr").val(number_format(round(retencionisr, numerodecimales), numerodecimales, '.', ''));
  $("#retencionieps").val(number_format(round(retencionieps, numerodecimales), numerodecimales, '.', ''));
}
//eliminar una fila en la tabla
function eliminarfila(fila){
  var confirmacion = confirm("Esta seguro de eliminar la fila?"); 
  if (confirmacion == true) { 
    $("#filaproducto"+fila).remove();
    contadorfilas--; //importante para todos los calculo en el modulo de orden de compra se debe restar al contadorfilas la fila que se acaba de eliminar
    renumerarfilas();//importante para todos los calculo en el modulo de orden de compra 
    comprobarfilas();
    comprobarfilasfacturanotacliente();
    calculartotal();
  }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
  var numerofilas = $("#tabladetallesnotacliente tbody tr").length;
  $("#numerofilas").val(numerofilas);
}
//renumerar las filas de la orden de compra
function renumerarfilas(){
  var lista;
  //renumerar filas tr
  lista = document.getElementsByClassName("filasproductos");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("id", "filaproducto"+i);
  }
  //renumerar btn eliminar fila
  lista = document.getElementsByClassName("btneliminarfila");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onclick", "eliminarfila("+i+')');
  }
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("cantidadpartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+');revisarcantidadnotavscantidadfactura('+i+')');
  }
  //renumerar el precio de la partida
  lista = document.getElementsByClassName("preciopartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar el decuetno en pesos de la partida
  lista = document.getElementsByClassName("descuentopesospartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar el ieps en porcentaje de la partida
  lista = document.getElementsByClassName("iepsporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar el iva en porcentaje de la partida
  lista = document.getElementsByClassName("ivaporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar el retencion iva en porcentaje de la partida
  lista = document.getElementsByClassName("retencionivaporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar el retencion isr en porcentaje de la partida
  lista = document.getElementsByClassName("retencionisrporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar el retencion ieps en porcentaje de la partida
  lista = document.getElementsByClassName("retencioniepsporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
}  
//revisar si hay existencias de la partida en el almacen
function revisarcantidadnotavscantidadfactura(fila){
  var folio = $("#folio").val();
  var serie = $("#serie").val();
  var factura = $("#stringfacturasseleccionadas").val();
  //var cantidadpartida = $("#filaproducto"+fila+" .cantidadpartida").val();
  var almacen = $("#numeroalmacen").val();
  var codigopartida = $("#filaproducto"+fila+" .codigopartida").val();
  var realizarbusquedaexistencias = $("#filaproducto"+fila+" .realizarbusquedaexistencias").val();
  if(realizarbusquedaexistencias === "1"){
    comprobarcantidadnotavscantidadfactura(almacen, codigopartida, folio, serie, factura).then(cantidadmaximapermitida=>{
      $("#filaproducto"+fila+" .cantidadpartida").attr('data-parsley-max',cantidadmaximapermitida);
      $("#filaproducto"+fila+" .cantidadpartida").parsley().validate();
    })
  }
}
//funcion asincrona para buscar existencias de la partida
function comprobarcantidadnotavscantidadfactura(almacen, codigopartida, folio, serie, factura){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $.get(notas_credito_cliente_comprobar_cantidad_nota_vs_cantidad_factura,{'almacen':almacen,'codigopartida':codigopartida,'folio':folio,'serie':serie,'factura':factura},cantidadmaximapermitida=>{
        return ejecuta(cantidadmaximapermitida);
      })
    },500);
  })
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    var numerofilas = $("#numerofilas").val();
    var numerofilasfacturas = $("#numerofilasfacturas").val();
    if(parseInt(numerofilas) > 0 && parseInt(numerofilasfacturas) > 0){
      var diferencia = $("#diferencia").val();
      if(parseFloat(diferencia) <= parseFloat(0.01)){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          url:notas_credito_clientes_guardar,
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
        msj_errorendiferenciatotalnotatotaldescuentos();
      }
    }else{
      msj_erroralmenosunapartidaagregada();
    }
  }else{
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});
//modificacion compra
function obtenerdatos(notamodificar){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(notas_credito_clientes_obtener_nota_cliente,{notamodificar:notamodificar },function(data){
    $("#titulomodal").html('Modificación Nota Crédito Cliente --- STATUS : ' + data.notacliente.Status);
    //formulario modificacion
    var tabs =    '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#compratab" data-toggle="tab">Nota</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#emisortab" data-toggle="tab">Emisor</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#receptortab" data-toggle="tab">Receptor ó Cliente</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="compratab">'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Nota <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp;<b style="color:#F44336 !important;" id="esquematexto"> Esquema: '+esquema+'</b>  <div class="btn btn-xs bg-red waves-effect" id="btnobtenerfoliosnotas" onclick="obtenerfoliosnotas()">Cambiar</div></label>'+
                                        '<input type="text" class="form-control inputnextdet" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                        '<input type="hidden" class="form-control" name="stringfacturasseleccionadas" id="stringfacturasseleccionadas" readonly required>'+
                                        '<input type="hidden" class="form-control" name="notaclientebd" id="notaclientebd" readonly>'+
                                        '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                                        '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                                        '<input type="hidden" class="form-control" name="numerofilasfacturas" id="numerofilasfacturas" readonly>'+
                                        '<input type="hidden" class="form-control" name="tipodetalles" id="tipodetalles" readonly>'+
                                        '<input type="hidden" class="form-control" name="diferenciatotales" id="diferenciatotales" readonly required>'+
                                        '<input type="hidden" class="form-control" name="esquema" id="esquema" value="'+esquema+'" readonly data-parsley-length="[1, 10]">'+
                                    '</div>'+  
                                    '<div class="col-md-3">'+
                                        '<label>Cliente <span class="label label-danger" id="textonombrecliente"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" id="btnobtenerclientes" onclick="obtenerclientes()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control" name="numerocliente" id="numerocliente" required data-parsley-type="integer" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="numeroclienteanterior" id="numeroclienteanterior" required data-parsley-type="integer">'+
                                                        '<input type="hidden" class="form-control" name="cliente" id="cliente" required readonly>'+
                                                        '<input type="hidden" class="form-control" name="rfccliente" id="rfccliente" required readonly>'+
                                                    '</div>'+
                                                '</td>'+    
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+ 
                                    '<div class="col-md-3">'+
                                        '<label>Almacen <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" id="btnobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnextdet" name="numeroalmacen" id="numeroalmacen" required readonly onkeyup="tipoLetra(this)" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior"  required data-parsley-type="integer">'+
                                                        '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Fecha</label>'+
                                        '<input type="datetime-local" class="form-control" name="fecha" id="fecha" required  onkeydown="return false">'+
                                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                                        '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                                    '</div>'+   
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                      '<table class="col-md-12">'+
                                        '<tr>'+
                                          '<td>'+
                                            '<label>Moneda</label>'+
                                            '<select name="moneda" id="moneda" class="form-control select2" style="width:100% !important;" required>'+
                                              '<option value="MXN">MXN</option>'+
                                              '<option value="USD">USD</option>'+
                                              '<option value="EUR">EUR</option>'+
                                            '</select>'+
                                          '</td>'+
                                          '<td>'+
                                            '<label>Pesos</label>'+
                                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet" name="pesosmoneda" id="pesosmoneda" value="1.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                          '</td>'+
                                        '</tr>'+
                                      '</table>'+
                                    '</div>'+ 
                                    '<div class="col-md-3">'+
                                        '<label>Cargar Facturas</label>'+
                                        '<div class="btn btn-block bg-blue waves-effect" id="btnlistarfacturas" onclick="listarfacturas()" style="display:none">Agregar Factura</div>'+
                                    '</div>'+      
                                    '<div class="col-md-3" id="divbuscarcodigoproducto">'+
                                      '<label>Escribe DPPP ó el Código y presiona la tecla ENTER</label>'+
                                      '<table class="col-md-12">'+
                                        '<tr>'+
                                          '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver Productos</div>'+
                                          '</td>'+
                                          '<td>'+ 
                                            '<div class="form-line">'+
                                              '<input type="text" class="form-control inputnextdet" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" readonly autocomplete="off">'+
                                            '</div>'+
                                          '</td>'+
                                        '</tr>'+    
                                      '</table>'+
                                    '</div>'+                                  
                                '</div>'+
                            '</div>'+   
                            '<div role="tabpanel" class="tab-pane fade" id="emisortab">'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<label>R.F.C.</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="emisorrfc" id="emisorrfc" value="'+rfcempresa+'"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Emisor Nombre</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="emisornombre" id="emisornombre" value="'+nombreempresa+'" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Confirmación</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="confirmacion" id="confirmacion" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                  '<div class="col-md-4">'+
                                      '<label>Lugar Expedición <span class="label label-danger" id="textonombrelugarexpedicion"></span></label>'+
                                      '<table class="col-md-12">'+
                                          '<tr>'+
                                              '<td>'+
                                                  '<div class="btn bg-blue waves-effect" onclick="obtenerlugaresexpedicion()">Seleccionar</div>'+
                                              '</td>'+
                                              '<td>'+
                                                  '<div class="form-line">'+
                                                      '<input type="text" class="form-control inputnextdet" name="lugarexpedicion" id="lugarexpedicion" value="'+lugarexpedicion+'" required autocomplete="off">'+
                                                      '<input type="hidden" class="form-control" name="lugarexpedicionanterior" id="lugarexpedicionanterior" value="'+lugarexpedicion+'" required>'+
                                                  '</div>'+
                                              '</td>'+
                                          '</tr>'+    
                                      '</table>'+
                                  '</div>'+
                                  '<div class="col-md-4">'+
                                      '<label>Régimen Fiscal <span class="label label-danger" id="textonombreregimenfiscal">'+regimenfiscal+'</span></label>'+
                                      '<table class="col-md-12">'+
                                          '<tr>'+
                                              '<td>'+
                                                  '<div class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscales()">Seleccionar</div>'+
                                              '</td>'+
                                              '<td>'+
                                                  '<div class="form-line">'+
                                                      '<input type="text" class="form-control inputnextdet" name="claveregimenfiscal" id="claveregimenfiscal" value="'+claveregimenfiscal+'" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                                      '<input type="hidden" class="form-control" name="claveregimenfiscalanterior" id="claveregimenfiscalanterior" value="'+claveregimenfiscal+'" required readonly onkeyup="tipoLetra(this)">'+
                                                      '<input type="hidden" class="form-control" name="regimenfiscal" id="regimenfiscal" value="'+regimenfiscal+'" required readonly>'+
                                                  '</div>'+
                                              '</td>'+
                                          '</tr>'+    
                                      '</table>'+
                                  '</div>'+
                                  '<div class="col-md-4">'+
                                      '<label>Tipo Relación  <span class="label label-danger" id="textonombretiporelacion"></span></label>'+
                                      '<table class="col-md-12">'+
                                          '<tr>'+
                                              '<td>'+
                                                  '<div class="btn bg-blue waves-effect" onclick="obtenertiposrelaciones()">Seleccionar</div>'+
                                              '</td>'+
                                              '<td>'+
                                                  '<div class="form-line">'+
                                                      '<input type="text" class="form-control inputnextdet" name="clavetiporelacion" id="clavetiporelacion"  onkeyup="tipoLetra(this)" required autocomplete="off">'+
                                                      '<input type="hidden" class="form-control" name="clavetiporelacionanterior" id="clavetiporelacionanterior"  readonly onkeyup="tipoLetra(this)">'+
                                                      '<input type="hidden" class="form-control" name="tiporelacion" id="tiporelacion" readonly>'+
                                                  '</div>'+
                                              '</td>'+
                                          '</tr>'+    
                                      '</table>'+
                                  '</div>'+
                                '</div>'+
                            '</div>'+ 
                            '<div role="tabpanel" class="tab-pane fade" id="receptortab">'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>R.F.C.</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="receptorrfc" id="receptorrfc"   required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Nombre</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="receptornombre" id="receptornombre"  required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Forma de Pago <span class="label label-danger" id="textonombreformapago"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenerformaspago()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnextdet" name="claveformapago" id="claveformapago" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="claveformapagoanterior" id="claveformapagoanterior" required readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="hidden" class="form-control" name="formapago" id="formapago" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Método de Pago <span class="label label-danger" id="textonombremetodopago"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenermetodospago()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnextdet" name="clavemetodopago" id="clavemetodopago" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="clavemetodopagoanterior" id="clavemetodopagoanterior" required readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="hidden" class="form-control" name="metodopago" id="metodopago" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Condiciones de Pago</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="condicionesdepago" id="condicionesdepago" value="CREDITO" required data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Uso CFDI  <span class="label label-danger" id="textonombreusocfdi"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenerusoscfdi()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnextdet" name="claveusocfdi" id="claveusocfdi" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="claveusocfdianterior" id="claveusocfdianterior" required readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="hidden" class="form-control" name="usocfdi" id="usocfdi" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Residencial Fiscal <span class="label label-danger" id="textonombreresidenciafiscal"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenerresidenciasfiscales()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnextdet" name="claveresidenciafiscal" id="claveresidenciafiscal" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="claveresidenciafiscalanterior" id="claveresidenciafiscalanterior" required readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="hidden" class="form-control" name="residenciafiscal" id="residenciafiscal" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Num Reg Id Trib</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="numeroregidtrib" id="numeroregidtrib" data-parsley-length="[1, 40]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                                    '</div>'+
                                '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                  '</div>'+
                  '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#productostab" data-toggle="tab">Códigos ó DPPP</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#facturastab" data-toggle="tab">Facturas</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                                '<div class="row">'+
                                    '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                                        '<table id="tabladetallesnotacliente" class="table table-bordered tabladetallesnotacliente">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                  '<th class="'+background_tables+'">#</th>'+
                                                  '<th class="'+background_tables+'">Código</th>'+
                                                  '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                                                  '<th class="customercolortheadth">Uda</th>'+
                                                  '<th class="customercolortheadth">Cantidad</th>'+
                                                  '<th class="customercolortheadth">Precio $</th>'+
                                                  '<th class="'+background_tables+'">Importe $</th>'+
                                                  '<th class="customercolortheadth">Dcto %</th>'+
                                                  '<th class="customercolortheadth">Dcto $</th>'+
                                                  '<th class="'+background_tables+'">Importe Descuento $</th>'+
                                                  '<th class="customercolortheadth" hidden>Ieps %</th>'+
                                                  '<th class="'+background_tables+'" hidden>Traslado Ieps $</th>'+
                                                  '<th class="'+background_tables+'">SubTotal $</th>'+
                                                  '<th class="customercolortheadth">Iva %</th>'+
                                                  '<th class="'+background_tables+'">Traslado Iva $</th>'+
                                                  '<th class="customercolortheadth" hidden>Retención Iva %</th>'+
                                                  '<th class="'+background_tables+'" hidden>Retención Iva $</th>'+
                                                  '<th class="customercolortheadth" hidden>Retención Isr %</th>'+
                                                  '<th class="'+background_tables+'" hidden>Retención Isr $</th>'+
                                                  '<th class="customercolortheadth" hidden>Retención Ieps %</th>'+
                                                  '<th class="'+background_tables+'" hidden>Retención Ieps $</th>'+
                                                  '<th class="'+background_tables+'">Total $</th>'+
                                                  '<th class="customercolortheadth">Partida</th>'+
                                                  '<th class="customercolortheadth">ClaveProducto</th>'+
                                                  '<th class="'+background_tables+'">Nombre ClaveProducto</th>'+
                                                  '<th class="customercolortheadth">ClaveUnidad</th>'+
                                                  '<th class="'+background_tables+'">Nombre ClaveUnidad</th>'+
                                                '</tr>'+
                                            '</thead>'+
                                            '<tbody>'+           
                                            '</tbody>'+
                                        '</table>'+
                                    '</div>'+
                                '</div>'+ 
                            '</div>'+ 
                            '<div role="tabpanel" class="tab-pane fade" id="facturastab">'+
                                '<div class="row">'+
                                  '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                                    '<table id="tabladetallesfacturasnotascliente" class="table table-bordered tabladetallesfacturasnotascliente">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                            '<th class="'+background_tables+'">#</th>'+
                                            '<th class="customercolortheadth">Factura</th>'+
                                            '<th class="'+background_tables+'">Fecha</th>'+
                                            '<th class="'+background_tables+'">UUID</th>'+
                                            '<th class="'+background_tables+'">Total $</th>'+
                                            '<th class="'+background_tables+'">Abonos $</th>'+
                                            '<th class="'+background_tables+'">Notas Crédito $</th>'+
                                            '<th class="customercolortheadth">Descuento $</th>'+
                                            '<th class="'+background_tables+'">Saldo $</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+           
                                        '</tbody>'+
                                    '</table>'+
                                  '</div>'+
                                '</div>'+
                            '</div>'+ 
                        '</div>'+
                        '<div class="row">'+
                          '<div class="col-md-6">'+   
                              '<label>Observaciones</label>'+
                              '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" rows="5" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
                          '</div>'+ 
                          '<div class="col-md-3">'+
                              '<table class="table table-striped table-hover">'+
                                  '<tr>'+
                                      '<td class="tdmod">Total Nota</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalnota" id="totalnota" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td class="tdmod">Descuentos</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentofacturas" id="descuentofacturas" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td class="tdmod">Diferencia</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="diferencia" id="diferencia" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                              '</table>'+
                          '</div>'+
                          '<div class="col-md-3">'+
                              '<table class="table table-striped table-hover">'+
                                  '<tr>'+
                                      '<td class="tdmod">Importe</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td class="tdmod">Descuento</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td class="tdmod">SubTotal</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td class="tdmod">Iva</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td class="tdmod">Total</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                              '</table>'+
                          '</div>'+
                        '</div>'+
                        '<div class="row">'+
                          '<div class="col-md-12">'+   
                            '<h5 id="mensajecalculoscompra"></h5>'+  
                          '</div>'+
                        '</div>'+
                    '</div>'+
                  '</div>';
    $("#tabsform").html(tabs);
    //colocar autocomplette off  todo el formulario
    $(".form-control").attr('autocomplete','off');
    //esconder el div del boton listar ordenes
    $("#btnobtenerclientes").hide();
    $("#btnlistarfacturas").hide();
    $("#btnobteneralmacenes").hide();
    $("#btnobtenerfoliosnotas").hide();
    $("#periodohoy").val(data.notacliente.Periodo);
    $("#folio").val(data.notacliente.Folio);
    $("#serie").val(data.notacliente.Serie);
    $("#serietexto").html("Serie: "+data.notacliente.Serie);
    $("#esquema").val(data.notacliente.Esquema);
    $("#esquematexto").html("Esquema: "+data.notacliente.Esquema);
    $("#stringfacturasseleccionadas").val(data.arrayfacturas);
    $("#notaclientebd").val(data.notacliente.Nota);
    $("#numerofilas").val(data.numerodetallesnotacliente);
    $("#numerofilasfacturas").val(data.numerodocumentosnotacliente);
    $("#tipodetalles").val(data.tipodetalles);
    $("#fecha").val(data.fecha);
    $("#cliente").val(data.cliente.Nombre);
    if(data.cliente.Nombre != null){
      $("#textonombrecliente").html(data.cliente.Nombre.substring(0, 40));
    }
    $("#numerocliente").val(data.cliente.Numero);
    $("#numeroclienteanterior").val(data.cliente.Numero);
    $("#rfccliente").val(data.cliente.Rfc);
    if(parseInt(data.almacen) == parseInt(0)){
      $("#almacen").val(0);
      $("#textonombrealmacen").html("");
      $("#numeroalmacen").val(0);
      $("#numeroalmacenanterior").val(0);
    }else{
      $("#almacen").val(data.almacen.Nombre);
      if(data.almacen.Nombre != null){
        $("#textonombrealmacen").html(data.almacen.Nombre.substring(0, 40));
      }
      $("#numeroalmacen").val(data.almacen.Numero);
      $("#numeroalmacenanterior").val(data.almacen.Numero);
    }
    $("#moneda").val(data.notacliente.Moneda).change();
    $("#pesosmoneda").val(data.tipocambio);
    $("#observaciones").val(data.notacliente.Obs);
    $("#emisorrfc").val(data.notacliente.EmisorRfc);
    $("#emisornombre").val(data.notacliente.EmisorNombre);
    $("#confirmacion").val(data.notacliente.Confirmacion);
    $("#lugarexpedicion").val(data.notacliente.LugarExpedicion);
    $("#lugarexpedicionanterior").val(data.notacliente.LugarExpedicion);
    $("#regimenfiscal").val(data.regimenfiscal.Nombre);
    if(data.regimenfiscal.Nombre != null){
      $("#textonombreregimenfiscal").html(data.regimenfiscal.Nombre.substring(0, 40));
    }
    $("#claveregimenfiscal").val(data.regimenfiscal.Clave);
    $("#claveregimenfiscalanterior").val(data.regimenfiscal.Clave);
    if(data.tiporelacion != null){
      $("#tiporelacion").val(data.tiporelacion.Nombre);
      if(data.tiporelacion.Nombre != null){
        $("#textonombretiporelacion").html(data.tiporelacion.Nombre.substring(0, 40));
      }
      $("#clavetiporelacion").val(data.tiporelacion.Clave);
      $("#clavetiporelacionanterior").val(data.tiporelacion.Clave);
    }
    $("#receptorrfc").val(data.notacliente.ReceptorRfc);
    $("#receptornombre").val(data.notacliente.ReceptorNombre);
    $("#formapago").val(data.formapago.Nombre);
    if(data.formapago.Nombre != null){
      $("#textonombreformapago").html(data.formapago.Nombre.substring(0, 40));
    }
    $("#claveformapago").val(data.formapago.Clave);
    $("#claveformapagoanterior").val(data.formapago.Clave);
    $("#metodopago").val(data.metodopago.Nombre);
    if(data.metodopago.Nombre != null){
      $("#textonombremetodopago").html(data.metodopago.Nombre.substring(0, 40));
    }
    $("#clavemetodopago").val(data.metodopago.Clave);
    $("#clavemetodopagoanterior").val(data.metodopago.Clave);
    $("#condicionesdepago").val(data.notacliente.CondicionesDePago);
    $("#usocfdi").val(data.usocfdi.Nombre);
    if(data.usocfdi.Nombre != null){
      $("#textonombreusocfdi").html(data.usocfdi.Nombre.substring(0, 40));
    }
    $("#claveusocfdi").val(data.usocfdi.Clave);
    $("#claveusocfdianterior").val(data.usocfdi.Clave);
    $("#residenciafiscal").val(data.residenciafiscal.Nombre);
    if(data.residenciafiscal.Nombre != null){
      $("#textonombreresidenciafiscal").html(data.residenciafiscal.Nombre.substring(0, 40));
    }
    $("#claveresidenciafiscal").val(data.residenciafiscal.Clave);
    $("#claveresidenciafiscalanterior").val(data.residenciafiscal.Clave);
    $("#numeroregidtrib").val(data.notacliente.NumRegIdTrib);
    //cargar todos los detalles
    $("#tabladetallesnotacliente tbody").html(data.filasdetallesnotacliente);
    //totales compra
    $("#importe").val(data.importe);
    $("#descuento").val(data.descuento);
    $("#ieps").val(data.ieps);
    $("#subtotal").val(data.subtotal);
    $("#iva").val(data.iva);
    $("#retencioniva").val(data.ivaretencion);
    $("#retencionisr").val(data.isrretencion);
    $("#retencionieps").val(data.iepsretencion);
    $("#total").val(data.total);  
    //cargar nota proveedor documentos
    $("#tabladetallesfacturasnotascliente tbody").html(data.filasdocumentosnotacliente);
    //totales descuentos y nota
    $("#totalnota").val(data.total);
    $("#descuentofacturas").val(data.descuentofacturas);
    $("#diferencia").val(data.diferencia);
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //se debe motrar el input para buscar los productos
    $("#divbuscarcodigoproducto").show();
    //activar buscador de codigos
    $("#codigoabuscar").removeAttr('readonly');
    //activar los input select
    $("#moneda").select2();
    //reiniciar contadores
    contadorproductos=data.contadorproductos;
    contadorfilas = data.contadorfilas;
    contadorfilasfacturas = data.contadorfilasfacturas;
    //activar busqueda de codigos
    $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenerproductoporcodigo();
      }
    });
    //regresar numero cliente
    $('#numerocliente').on('change', function(e) {
      regresarnumerocliente();
    });
    //regresar numero almacen
    $('#numeroalmacen').on('change', function(e) {
      regresarnumeroalmacen();
    });
    //activar busqueda para lugar expedicion
    $('#lugarexpedicion').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenerlugarexpedicionporclave();
      }
    });
    //regresar clave
    $('#lugarexpedicion').on('change', function(e) {
      regresarclavelugarexpedicion();
    });
    //activar busqueda para regimen fiscal
    $('#claveregimenfiscal').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenerregimenfiscalporclave();
      }
    });
    //regresar clave
    $('#claveregimenfiscal').on('change', function(e) {
      regresarclaveregimenfiscal();
    });
    //activar busqueda para tipo relacion
    $('#clavetiporelacion').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenertiporelacionporclave();
      }
    });
    //regresar clave
    $('#clavetiporelacion').on('change', function(e) {
      regresarclavetiporelacion();
    });
    //activar busqueda para forma pago
    $('#claveformapago').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenerformapagoporclave();
      }
    });
    //regresar clave
    $('#claveformapago').on('change', function(e) {
      regresarclaveformapago();
    });
    //activar busqueda para metodo pago
    $('#clavemetodopago').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenermetodopagoporclave();
      }
    });
    //regresar clave
    $('#clavemetodopago').on('change', function(e) {
      regresarclavemetodopago();
    });
    //activar busqueda para uso cfdi
    $('#claveusocfdi').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenerusocfdiporclave();
      }
    });
    //regresar clave
    $('#claveusocfdi').on('change', function(e) {
      regresarclaveusocfdi();
    });
    //activar busqueda para residencia fiscal
    $('#claveresidenciafiscal').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenerresidenciafiscalporclave();
      }
    });
    //regresar clave
    $('#claveresidenciafiscal').on('change', function(e) {
      regresarclaveresidenciafiscal();
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnextdet").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnextdet");          
      switch(code){
        case 13:
          $(".inputnextdet").eq(index + 1).focus().select(); 
          break;
        case 39:
          $(".inputnextdet").eq(index + 1).focus().select(); 
          break;
        case 37:
          $(".inputnextdet").eq(index - 1).focus().select(); 
          break;
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
    var numerofilas = $("#numerofilas").val();
    var numerofilasfacturas = $("#numerofilasfacturas").val();
    if(parseInt(numerofilas) > 0 && parseInt(numerofilasfacturas) > 0){
      var diferencia = $("#diferencia").val();
      if(parseFloat(diferencia) <= parseFloat(0.01)){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          url:notas_credito_clientes_guardar_modificacion,
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
        msj_errorendiferenciatotalnotatotaldescuentos();
      }
    }else{
      msj_erroralmenosunapartidaagregada();
    }
  }else{
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});
//verificar si hay existencias suficientes en los almacenes para dar de baja nota de credito
function desactivar(notadesactivar){
  $.get(notas_credito_clientes_verificar_si_continua_baja,{notadesactivar:notadesactivar}, function(data){
    if(data.Status == 'BAJA'){
      $("#notadesactivar").val(0);
      $("#textomodaldesactivar").html('Error, esta nota credito cliente ya fue dado de baja');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{ 
      if(data.resultadofechas != ''){
        $("#notadesactivar").val(0);
        $("#textomodaldesactivar").html('Error solo se pueden dar de baja las notas del mes actual, fecha de la nota: ' + data.resultadofechas);
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        if(data.errores != ''){
          $("#notadesactivar").val(0);
          $("#textomodaldesactivar").html(data.errores);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else{
          $("#notadesactivar").val(notadesactivar);
          $("#textomodaldesactivar").html('Estas seguro de dar de baja la nota de crédito cliente? No'+notadesactivar);
          $("#motivobaja").val("");
          $("#divmotivobaja").show();
          $("#btnbaja").show();
          $('#estatusregistro').modal('show');
        }
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
      url:notas_credito_clientes_alta_o_baja,
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
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento){
  $.get(notas_credito_clientes_obtener_datos_envio_email,{documento:documento}, function(data){
    $("#textomodalenviarpdfemail").html("Enviar email Nota de Crédito Cliente No." + documento);
    $("#emaildocumento").val(documento);
    $("#emailde").val(data.emailde);
    $("#emailpara").val(data.emailpara);
    $("#email2cc").val(data.email2cc);
    $("#email3cc").val(data.email3cc);
    $("#emailasunto").val("NOTA DE CRÉDITO CLIENTE NO. " + documento +" DE "+ nombreempresa);
    $("#emailmensaje").val("NOTA DE CRÉDITO CLIENTE NO. " + documento +" DE "+ nombreempresa);
    if(data.notacliente.UUID != ""){
      $("#incluir_xml").removeAttr('onclick');
    }else{
      $("#incluir_xml").attr('onclick','javascript: return false;');
    }
    $("#divincluirxml").show();
    $(".dropify-clear").trigger("click");
    $("#divadjuntararchivo").show();
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
      url:notas_credito_clientes_enviar_pdfs_email,
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
//timbrar pago
function timbrarnota(nota){
  $.get(notas_credito_clientes_verificar_si_continua_timbrado,{nota:nota}, function(data){
      if(data.Esquema == 'INTERNA' || data.Esquema == 'NOTA'){
        $("#notatimbrado").val(0);
        $("#textomodaltimbrado").html('Aviso, las Notas de crédito cliente con Esquema INTERNA o NOTA no se pueden timbrar');
        $('#modaltimbrado').modal('show');
        $("#btntimbrarnota").hide();
      }else if(data.Status == 'BAJA'){
          $("#notatimbrado").val(0);
          $("#textomodaltimbrado").html('Aviso, esta Nota se encuentra dada de baja');
          $('#modaltimbrado').modal('show');
          $("#btntimbrarnota").hide();
      }else{ 
          if(data.UUID != ""){
              $("#notatimbrado").val(0);
              $("#textomodaltimbrado").html('Aviso, esta Nota ya se timbro');
              $('#modaltimbrado').modal('show');
              $("#btntimbrarnota").hide();
          }else{
              $("#modaltimbrado").modal("show");
              $("#textomodaltimbrado").html("Esta seguro de timbrar la Nota? No"+nota);
              $("#notatimbrado").val(nota);
              $("#btntimbrarnota").show();
          }
      }
  }) 
}
$("#btntimbrarnota").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formtimbrado")[0]);
  var form = $("#formtimbrado");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:notas_credito_clientes_timbrar_nota,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#modaltimbrado').modal('hide');
        var results = JSON.parse(data);
        msj_documentotimbradocorrectamente(results.mensaje, results.tipomensaje);
        $('.page-loader-wrapper').css('display', 'none');
      },
      error:function(data){
        if(data.status == 403){
          msj_errorenpermisos();
        }else{
          msj_errorajax();
        }
        $('#modaltimbrado').modal('hide');
        $('.page-loader-wrapper').css('display', 'none');
      }
    })
  }else{
    form.parsley().validate();
  }
});  

//cancelar timbre
function cancelartimbre(facturabajatimbre){
  $.get(notas_credito_clientes_verificar_si_continua_baja_timbre,{facturabajatimbre:facturabajatimbre}, function(data){
    if(data.comprobante != ''){
      if(data.comprobante.IdFacturapi != null){
          if(data.obtener_factura.cancellation_status == "none" || data.obtener_factura.cancellation_status == "pending"){
            $("#facturabajatimbre").val(facturabajatimbre);
            $("#iddocumentofacturapi").val(data.obtener_factura.id);
            $("#textomodalbajatimbre").html('Esta seguro de dar de baja el timbre de la factura No.'+ facturabajatimbre);
            $("#btnbajatimbre").show();
            $('#modalbajatimbre').modal('show');
          }else if(data.obtener_factura.cancellation_status == "accepted"){
            $("#facturabajatimbre").val(0);
            $("#iddocumentofacturapi").val(0);
            $("#textomodalbajatimbre").html('Aviso, el timbre de la factura No.' + facturabajatimbre +' ya esta cancelado');
            $("#btnbajatimbre").hide();
            $('#modalbajatimbre').modal('show');
          }
      }else{
        $("#facturabajatimbre").val(0);
        $("#iddocumentofacturapi").val(0);
        $("#textomodalbajatimbre").html('Aviso, la Nota No.'+ facturabajatimbre +' no esta timbrada en el nuevo sistema');
        $("#btnbajatimbre").hide();
        $('#modalbajatimbre').modal('show');
      }
    }else{ 
      $("#facturabajatimbre").val(0);
      $("#iddocumentofacturapi").val(0);
      $("#textomodalbajatimbre").html('Aviso, la Nota No.'+ facturabajatimbre +' no esta timbrada');
      $("#btnbajatimbre").hide();
      $('#modalbajatimbre').modal('show');
    }
  })   
}
$("#btnbajatimbre").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formbajatimbre")[0]);
  var form = $("#formbajatimbre");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:notas_credito_clientes_baja_timbre,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#modalbajatimbre').modal('hide');
        msj_timbrecanceladocorrectamente();
        $('.page-loader-wrapper').css('display', 'none');
      },
      error:function(data){
        if(data.status == 403){
          msj_errorenpermisos();
        }else{
          msj_errorajax();
        }
        $('#modalbajatimbre').modal('hide');
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
                                                '<th>Nota</th>'+
                                                '<th>Cliente</th>'+
                                                '<th>Total</th>'+
                                                '<th>Status</th>'+
                                            '</tr>';
  $("#columnastablafoliosencontrados").html(columnastablafoliosencontrados);
  tabla=$('#tablafoliosencontrados').DataTable({
      keys: true,
      "paging":   false,
      "ordering": false,
      "info":     false,
      "searching": false,
      processing: true,
      serverSide: true,
      ajax: {
          url: notas_credito_clientes_buscar_folio_string_like,
          data: function (d) {
            d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Nota', name: 'Nota' },
          { data: 'Cliente', name: 'Cliente', orderable: false, searchable: false },
          { data: 'Total', name: 'Total', orderable: false, searchable: false  },
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
    if(campos[i] == 'Nota' || campos[i] == 'Status' || campos[i] == 'Periodo'){
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