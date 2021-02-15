'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  $.get(clientes_obtener_ultimo_numero, function(numero){
    $("#numero").val(numero);
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
function mostrarmodalformulario(tipo){
  $("#ModalFormulario").modal('show');
  if(tipo == 'ALTA'){
      $("#btnGuardar").show();
      $("#btnGuardarModificacion").hide();
  }else if(tipo == 'MODIFICACION'){
      $("#btnGuardar").hide();
      $("#btnGuardarModificacion").show();
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
//listar todos los registros de la tabla
function listar(){
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
    "sScrollX": "110%",
    "sScrollY": "350px",
    "bScrollCollapse": true,  
    processing: true,
    'language': {
      'loadingRecords': '&nbsp;',
      'processing': '<div class="spinner"></div>'
    },
    serverSide: true,
    ajax: clientes_obtener,
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
//obtener registros de paises
function obtenerpaises(){
  ocultarformulario();
  var tablapaises = '<div class="modal-header bg-red">'+
                      '<h4 class="modal-title">Paises</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                  '<table id="tbllistadopais" class="tbllistadopais table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                      '<thead class="customercolor">'+
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
  $("#contenidomodaltablas").html(tablapaises);
  $('#tbllistadopais').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: clientes_obtener_paises,
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
              $('#tbllistadopais').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  }); 
} 
//obtener registros de estados
function obtenerestados() {
  ocultarformulario();
  var tablaestados = '<div class="modal-header bg-red">'+
                      '<h4 class="modal-title">Estados</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                  '<table id="tbllistadoestado" class="tbllistadoestado table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                      '<thead class="customercolor">'+
                                          '<tr>'+
                                              '<th>Operaciones</th>'+
                                              '<th>Número</th>'+
                                              '<th>Clave</th>'+
                                              '<th>País</th>'+
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
  $("#contenidomodaltablas").html(tablaestados);
  $('#tbllistadoestado').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: clientes_obtener_estados,
        data: function (d) {
            d.numeropais = $("#pais").val();
        }
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Numero', name: 'Numero' },
          { data: 'Clave', name: 'Clave' },
          { data: 'Pais', name: 'Pais', orderable: false, searchable: false},
          { data: 'Nombre', name: 'Nombre' }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoestado').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  }); 
}
//obtener registros de codigos postales
function obtenercodigospostales() {
  ocultarformulario();
  var tablacodigospostales =  '<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Códigos Postales</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadocodigopostal" class="tbllistadocodigopostal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="customercolor">'+
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
  $('#tbllistadocodigopostal').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: clientes_obtener_codigos_postales,
        data: function (d) {
            d.numeroestado = $("#estado").val();
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
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadocodigopostal').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  });  
}
//obtener registros de municipios
function obtenermunicipios() {
  ocultarformulario();
  var tablamunicipios = '<div class="modal-header bg-red">'+
                          '<h4 class="modal-title">Municipios</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                          '<div class="row">'+
                              '<div class="col-md-12">'+
                                  '<div class="table-responsive">'+
                                      '<table id="tbllistadomunicipio" class="tbllistadomunicipio table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                          '<thead class="customercolor">'+
                                              '<tr>'+
                                                  '<th>Operaciones</th>'+
                                                  '<th>Clave</th>'+
                                                  '<th>Estado</th>'+
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
  $("#contenidomodaltablas").html(tablamunicipios);
  $('#tbllistadomunicipio').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: clientes_obtener_municipios,
        data: function (d) {
            d.numeroestado = $("#estado").val();
        }
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Estado', name: 'Estado', orderable: false, searchable: false},
          { data: 'Nombre', name: 'Nombre' }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadomunicipio').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  });  
}
//obtener registros de agentes
function obteneragentes(){
  ocultarformulario();
  var tablaagentes =  '<div class="modal-header bg-red">'+
                        '<h4 class="modal-title">Agentes</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<div class="table-responsive">'+
                                    '<table id="tbllistadoagente" class="tbllistadoagente table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                        '<thead class="customercolor">'+
                                            '<tr>'+
                                                '<th>Operaciones</th>'+
                                                '<th>Número</th>'+
                                                '<th>Nombre</th>'+
                                                '<th>R.F.C.</th>'+
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
  $("#contenidomodaltablas").html(tablaagentes);  
  $('#tbllistadoagente').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: clientes_obtener_agentes,
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Numero', name: 'Numero' },
          { data: 'Nombre', name: 'Nombre' },
          { data: 'Rfc', name: 'Rfc', orderable: false, searchable: false }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoagente').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  }); 
} 
//obtener registros de formas de pago
function obtenerformaspago(){
  ocultarformulario();
  var tablaformaspago = '<div class="modal-header bg-red">'+
                          '<h4 class="modal-title">Formas Pago</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                          '<div class="row">'+
                              '<div class="col-md-12">'+
                                  '<div class="table-responsive">'+
                                      '<table id="tbllistadoformapago" class="tbllistadoformapago table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                          '<thead class="customercolor">'+
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
  $("#contenidomodaltablas").html(tablaformaspago);   
  $('#tbllistadoformapago').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: clientes_obtener_formas_pago,
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoformapago').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  }); 
} 
//obtener registros de formas de pago
function obtenermetodospago(){
  ocultarformulario();
  var tablasmetodospago = '<div class="modal-header bg-red">'+
                            '<h4 class="modal-title">Métodos Pago</h4>'+
                          '</div>'+
                          '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadometodopago" class="tbllistadometodopago table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="customercolor">'+
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
  $("#contenidomodaltablas").html(tablasmetodospago);   
  $('#tbllistadometodopago').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: clientes_obtener_metodos_pago,
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadometodopago').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  }); 
} 
//obtener registros de formas de pago
function obtenerusoscfdi(){
  ocultarformulario();
  var tablasusoscfdi =  '<div class="modal-header bg-red">'+
                          '<h4 class="modal-title">Métodos Pago</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                          '<div class="row">'+
                              '<div class="col-md-12">'+
                                  '<div class="table-responsive">'+
                                      '<table id="tbllistadousocfdi" class="tbllistadousocfdi table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                          '<thead class="customercolor">'+
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
  $("#contenidomodaltablas").html(tablasusoscfdi); 
  $('#tbllistadousocfdi').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: clientes_obtener_uso_cfdi,
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre' },
          { data: 'Fisica', name: 'Fisica', orderable: false, searchable: false },
          { data: 'Moral', name: 'Moral', orderable: false, searchable: false }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadousocfdi').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  }); 
} 
function seleccionarpais(Numero, Clave){
  $("#pais").val(Numero);
  $("#clavepais").val(Clave);
  $("#clavepais").keyup();
  mostrarformulario();
  vaciarestado();
  vaciarmunicipioycodigopostal();
}
function seleccionarestado(Numero, Nombre){
  $("#estado").val(Numero);
  $("#nombreestado").val(Nombre);
  $("#nombreestado").keyup();
  mostrarformulario();
  vaciarmunicipioycodigopostal();
}
function seleccionarcodigopostal(Clave){
  $("#codigopostal").val(Clave);
  mostrarformulario();
}
function seleccionarmunicipio(Nombre){
  $("#municipio").val(Nombre);
  $("#municipio").keyup();
  mostrarformulario();
}
function seleccionaragente(Numero, Nombre){
  $("#agente").val(Numero);
  $("#nombreagente").val(Nombre);
  $("#nombreagente").keyup();
  mostrarformulario();
}
function seleccionarformapago(Clave, Nombre){
  $("#claveformapago").val(Clave);
  $("#formapago").val(Nombre);
  $("#formapago").keyup();
  mostrarformulario();
}
function seleccionarmetodopago(Clave, Nombre){
  $("#clavemetodopago").val(Clave);
  $("#metodopago").val(Nombre);
  $("#metodopago").keyup();
  mostrarformulario();
}
function seleccionarusocfdi(Clave, Nombre){
  $("#claveusocfdi").val(Clave);
  $("#usocfdi").val(Nombre);
  $("#usocfdi").keyup();
  mostrarformulario();
}
//cuando el usuario cambia el estado se deben vaciar el municipio y CP
function vaciarmunicipioycodigopostal(){
  $("#municipio").val("");
  $("#codigopostal").val("");
}
//cuando el usuario cambia de pais vaciar el estado
function vaciarestado(){
  $("#estado").val("");
  $("#nombreestado").val("");
}
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Cliente');
  mostrarmodalformulario('ALTA');
  mostrarformulario();
  //formulario alta
  var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                '<li role="presentation" class="active">'+
                  '<a href="#domiciliofiscal" data-toggle="tab">Domicilio Fiscal</a>'+
                '</li>'+
                '<li role="presentation">'+
                  '<a href="#telefonostabs" data-toggle="tab">Teléfonos</a>'+
                '</li>'+
              '</ul>'+
              '<div class="tab-content">'+
                '<div role="tabpanel" class="tab-pane fade in active" id="domiciliofiscal">'+
                  '<div class="row">'+
                      '<div class="col-md-12">'+
                          '<small><b style="color:#F44336 !important;">*</b> Indispensable para el timbrado de facturas</small>'+
                      '</div>'+
                  '</div>'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                          '<label>RFC <b style="color:#F44336 !important;">*</b></label>'+
                          '<input type="text" class="form-control" name="rfc" id="rfc" required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" onkeyup="tipoLetra(this);mayusculas(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Calle <b style="color:#F44336 !important;">*</b></label>'+
                          '<input type="text" class="form-control" name="calle" id="calle" required onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>No. Exterior <b style="color:#F44336 !important;">*</b></label>'+
                          '<input type="text" class="form-control" name="noexterior" id="noexterior" required onkeyup="tipoLetra(this);">'+
                      '</div>'+
                  '</div>'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                          '<label>No. Interior</label>'+
                          '<input type="text" class="form-control" name="nointerior" id="nointerior" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Colonia<b style="color:#F44336 !important;">*</b></label>'+
                          '<input type="text" class="form-control" name="colonia" id="colonia" required onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Localidad<b style="color:#F44336 !important;">*</b></label>'+
                          '<input type="text" class="form-control" name="localidad" id="localidad" required onkeyup="tipoLetra(this);">'+
                      '</div>'+
                  '</div>'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                          '<label>Referencia</label>'+
                          '<input type="text" class="form-control" name="referencia" id="referencia" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                        '<label>País<b style="color:#F44336 !important;">*</b></label>'+
                        '<div class="row">'+
                          '<div class="col-md-4">'+
                            '<span class="input-group-btn">'+
                              '<div id="buscarpaises" class="btn bg-blue waves-effect" onclick="obtenerpaises()">Seleccionar</div>'+
                            '</span>'+
                          '</div>'+  
                          '<div class="col-md-8">'+  
                            '<div class="form-line">'+
                              '<input type="text" class="form-control" name="clavepais" id="clavepais" value="MEX" required readonly onkeyup="tipoLetra(this)">'+
                              '<input type="hidden" class="form-control" name="pais" id="pais" value="151" required readonly>'+
                            '</div>'+
                          '</div>'+     
                        '</div>'+
                      '</div>'+
                      '<div class="col-md-4">'+
                        '<label>Estado<b style="color:#F44336 !important;">*</b></label>'+
                        '<div class="row">'+
                          '<div class="col-md-4">'+
                            '<span class="input-group-btn">'+
                              '<div id="buscarestados" class="btn bg-blue waves-effect" onclick="obtenerestados()">Seleccionar</div>'+
                            '</span>'+
                          '</div>'+  
                          '<div class="col-md-8">'+  
                            '<div class="form-line">'+
                              '<input type="text" class="form-control" name="nombreestado" id="nombreestado" required readonly onkeyup="tipoLetra(this)">'+
                              '<input type="hidden" class="form-control" name="estado" id="estado" required readonly>'+
                            '</div>'+
                          '</div>'+     
                        '</div>'+
                      '</div>'+
                  '</div>'+
                  '<div class="row">'+
                    '<div class="col-md-4">'+
                      '<label>Municipio<b style="color:#F44336 !important;">*</b></label>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<span class="input-group-btn">'+
                            '<div id="buscarmunicipios" class="btn bg-blue waves-effect" onclick="obtenermunicipios()">Seleccionar</div>'+
                          '</span>'+
                        '</div>'+  
                        '<div class="col-md-8">'+  
                          '<div class="form-line">'+
                            '<input type="text" class="form-control" name="municipio" id="municipio" required readonly onkeyup="tipoLetra(this)">'+
                          '</div>'+
                        '</div>'+     
                      '</div>'+
                    '</div>'+
                    '<div class="col-md-4">'+
                      '<label>Código Postal<b style="color:#F44336 !important;">*</b></label>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<span class="input-group-btn">'+
                            '<div id="buscarcodigospostales" class="btn bg-blue waves-effect" onclick="obtenercodigospostales()">Seleccionar</div>'+
                          '</span>'+
                        '</div>'+  
                        '<div class="col-md-8">'+  
                          '<div class="form-line">'+
                            '<input type="text" class="form-control" name="codigopostal" id="codigopostal" required data-parsley-codigopostal="^[0-9]{5}$" onkeyup="tipoLetra(this)">'+
                          '</div>'+
                        '</div>'+     
                      '</div>'+
                    '</div>'+
                    '<div class="col-md-4">'+
                      '<label>Plazo (días)</label>'+
                      '<input type="text" class="form-control" name="plazo" id="plazo" value="1" onkeyup="tipoLetra(this);">'+
                    '</div>'+
                  '</div>'+
                  '<div class="row">'+
                    '<div class="col-md-4">'+
                      '<label>Agente</label>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<span class="input-group-btn">'+
                            '<div id="buscaragentes" class="btn bg-blue waves-effect" onclick="obteneragentes()">Seleccionar</div>'+
                          '</span>'+
                        '</div>'+  
                        '<div class="col-md-8">'+  
                          '<div class="form-line">'+
                            '<input type="text" class="form-control" name="nombreagente" id="nombreagente" readonly onkeyup="tipoLetra(this)">'+
                            '<input type="hidden" class="form-control" name="agente" id="agente" readonly>'+
                          '</div>'+
                        '</div>'+     
                      '</div>'+
                    '</div>'+
                    '<div class="col-md-4">'+
                      '<label>Forma de Pago<b style="color:#F44336 !important;">*</b></label>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<span class="input-group-btn">'+
                            '<div id="buscarformaspago" class="btn bg-blue waves-effect" onclick="obtenerformaspago()">Seleccionar</div>'+
                          '</span>'+
                        '</div>'+  
                        '<div class="col-md-8">'+  
                          '<div class="form-line">'+
                            '<input type="text" class="form-control" name="formapago" id="formapago" required readonly onkeyup="tipoLetra(this)">'+
                            '<input type="hidden" class="form-control" name="claveformapago" id="claveformapago" required readonly>'+
                          '</div>'+
                        '</div>'+     
                      '</div>'+
                    '</div>'+
                    '<div class="col-md-4">'+
                      '<label>Método de Pago<b style="color:#F44336 !important;">*</b></label>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<span class="input-group-btn">'+
                            '<div id="buscarmetodospago" class="btn bg-blue waves-effect" onclick="obtenermetodospago()">Seleccionar</div>'+
                          '</span>'+
                        '</div>'+  
                        '<div class="col-md-8">'+  
                          '<div class="form-line">'+
                            '<input type="text" class="form-control" name="metodopago" id="metodopago" required readonly onkeyup="tipoLetra(this)">'+
                            '<input type="hidden" class="form-control" name="clavemetodopago" id="clavemetodopago" required readonly>'+
                          '</div>'+
                        '</div>'+     
                      '</div>'+
                    '</div>'+
                  '</div>'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                        '<label>Uso Cfdi<b style="color:#F44336 !important;">*</b></label>'+
                        '<div class="row">'+
                          '<div class="col-md-4">'+
                            '<span class="input-group-btn">'+
                              '<div id="buscarusoscfdi" class="btn bg-blue waves-effect" onclick="obtenerusoscfdi()">Seleccionar</div>'+
                            '</span>'+
                          '</div>'+  
                          '<div class="col-md-8">'+  
                            '<div class="form-line">'+
                              '<input type="text" class="form-control" name="usocfdi" id="usocfdi" required readonly onkeyup="tipoLetra(this)">'+
                              '<input type="hidden" class="form-control" name="claveusocfdi" id="claveusocfdi" required readonly>'+
                            '</div>'+
                          '</div>'+     
                        '</div>'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Tipo</label>'+
                          '<input type="text" class="form-control" name="tipo" id="tipo" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Crédito Máximo  (0 = Crédito libre)</label>'+
                          '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="creditomaximo" id="creditomaximo" value="10000.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                      '</div>'+
                  '</div>'+   
                '</div>'+
                '<div role="tabpanel" class="tab-pane fade" id="telefonostabs">'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                          '<label>Contacto</label>'+
                          '<input type="text" class="form-control" name="contacto" id="contacto" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Teléfonos</label>'+
                          '<input type="text" class="form-control" name="telefonos" id="telefonos" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Celular</label>'+
                          '<input type="text" class="form-control" name="celular" id="celular" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                  '</div>'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                          '<label>E-mail 1</label>'+
                          '<input type="text" class="form-control" name="email1" id="email1"  data-parsley-regexemail="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Cuenta Ref</label>'+
                          '<input type="text" class="form-control" name="cuentaref" id="cuentaref" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Cuenta Ser</label>'+
                          '<input type="text" class="form-control" name="cuentaser" id="cuentaser" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                  '</div>'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                          '<label>Anotaciones</label>'+
                          '<textarea class="form-control" name="anotaciones" id="anotaciones"  onkeyup="tipoLetra(this);"></textarea>'+
                      '</div>'+
                  '</div>'+
                '</div>'+  
              '</div>';
  $("#tabsform").html(tabs);
  obtenultimonumero();
  $("#ModalAlta").modal('show');
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:clientes_guardar,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        if(data == 1){
          msj_errorrfcexistente();
        }else{
          msj_datosguardadoscorrectamente();
          limpiar();
          ocultarmodalformulario();
          limpiarmodales();
        }
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
    form.parsley().validate();
  }
});
//dar de baja o alta registro
function desactivar(numerocliente){
  $("#numerocliente").val(numerocliente);
  $('#estatusregistro').modal('show');
}
$("#aceptar").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formdesactivar")[0]);
  var form = $("#formdesactivar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:clientes_alta_o_baja,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
          $('#estatusregistro').modal('hide');
          msj_statuscambiado();
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
//detectar cuando en el input de buscar por codigo de producto el usuario presione la tecla enter, si es asi se realizara la busqueda con el codigo escrito
function activarbusquedaproducto(){
  var buscarcodigoproducto = $('#codigoabuscar');
  buscarcodigoproducto.unbind();
  buscarcodigoproducto.bind('keyup change', function(e) {
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        listarproductos();
      }
  });
}  
//listar productos para tab consumos
function listarproductos(){
  ocultarformulario();
  var tablaproductos = '<div class="modal-header bg-red">'+
                          '<h4 class="modal-title">Productos</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                          '<div class="row">'+
                              '<div class="col-md-12">'+
                                  '<div class="table-responsive">'+
                                      '<table id="tbllistadoproducto" class="tbllistadoproducto table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                          '<thead class="customercolor">'+
                                              '<tr>'+
                                                  '<th>Operaciones</th>'+
                                                  '<th>Código</th>'+
                                                  '<th>Marca</th>'+
                                                  '<th>Producto</th>'+
                                                  '<th>Almacen</th>'+
                                                  '<th>Ubicación</th>'+
                                                  '<th>Existen</th>'+
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
  $('#tbllistadoproducto').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: clientes_obtener_productos,
        data: function (d) {
          d.codigoabuscar = $("#codigoabuscar").val();
        }
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false  },
          { data: 'Codigo', name: 'Codigo' },
          { data: 'Marca', name: 'Marca', orderable: false, searchable: false  },
          { data: 'Producto', name: 'Producto', orderable: false, searchable: false  },
          { data: 'Ubicacion', name: 'Ubicacion', orderable: false, searchable: false  },
          { data: 'Ubicacion', name: 'Ubicacion', orderable: false, searchable: false  },
          { data: 'Existencias', name: 'Existencias', orderable: false, searchable: false  },
          { data: 'Costo', name: 'Costo', orderable: false, searchable: false  },
          { data: 'SubTotal', name: 'SubTotal', orderable: false, searchable: false  } 
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoproducto').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  });
  
}
//agregar una fila en la tabla de precios productos
var contadorpreciosproductos=0;
function agregarfilaproducto(Codigo, Producto){
    var fila=   '<tr class="filaspreciosproductos" id="filaprecioproducto'+contadorpreciosproductos+'">'+
                    '<td><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosproductos('+contadorpreciosproductos+')">X</div></td>'+
                    '<td><input type="hidden" name="codigoproducto[]" id="codigoproducto[]" value="'+Codigo+'" readonly>'+Codigo+'</td>'+
                    '<td><input type="hidden" name="nombreproducto[]" id="nombreproducto[]" value="'+Producto+'" readonly>'+Producto+'</td>'+
                    '<td><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" name="subtotalprecioproducto[]" id="subtotalprecioproducto[]" required value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);"></td>'+
                '</tr>';
    contadorpreciosproductos++;
    $("#tablapreciosproductos").append(fila);
    mostrarformulario();
    comprobarfilaspreciosproductos();
}
//eliminar una fila en la tabla de precios clientes
function eliminarfilapreciosproductos(numerofila){
    $("#filaprecioproducto"+numerofila).remove();
    comprobarfilaspreciosproductos();
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilaspreciosproductos(){
    var numerofilas = $("#tablapreciosproductos tbody tr").length;
    $("#numerofilaspreciosproducto").val(numerofilas);
}
function obtenerdatos(numerocliente){
  $("#titulomodal").html('Modificación Cliente');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(clientes_obtener_cliente,{numerocliente:numerocliente },function(data){
    //formulario modificacion
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                  '<li role="presentation" class="active">'+
                    '<a href="#domiciliofiscal" data-toggle="tab">Domicilio Fiscal</a>'+
                  '</li>'+
                  '<li role="presentation">'+
                    '<a href="#telefonostabs" data-toggle="tab">Teléfonos</a>'+
                  '</li>'+
                  '<li role="presentation" >'+
                    '<a href="#utilidadestab" data-toggle="tab">Utilidades</a>'+
                  '</li>'+
                  '<li role="presentation" >'+
                    '<a href="#precioproductostab" data-toggle="tab">Precio Productos</a>'+
                  '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                  '<div role="tabpanel" class="tab-pane fade in active" id="domiciliofiscal">'+
                    '<div class="row">'+
                        '<div class="col-md-12">'+
                            '<small><b style="color:#F44336 !important;">*</b> Indispensable para el timbrado de facturas</small>'+
                        '</div>'+
                    '</div>'+
                    '<div class="row">'+
                        '<div class="col-md-4">'+
                            '<label>RFC <b style="color:#F44336 !important;">*</b></label>'+
                            '<input type="text" class="form-control" name="rfc" id="rfc" required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" onkeyup="tipoLetra(this);mayusculas(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Calle <b style="color:#F44336 !important;">*</b></label>'+
                            '<input type="text" class="form-control" name="calle" id="calle" required onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>No. Exterior <b style="color:#F44336 !important;">*</b></label>'+
                            '<input type="text" class="form-control" name="noexterior" id="noexterior" required onkeyup="tipoLetra(this);">'+
                        '</div>'+
                    '</div>'+
                    '<div class="row">'+
                        '<div class="col-md-4">'+
                            '<label>No. Interior</label>'+
                            '<input type="text" class="form-control" name="nointerior" id="nointerior" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Colonia<b style="color:#F44336 !important;">*</b></label>'+
                            '<input type="text" class="form-control" name="colonia" id="colonia" required onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Localidad<b style="color:#F44336 !important;">*</b></label>'+
                            '<input type="text" class="form-control" name="localidad" id="localidad" required onkeyup="tipoLetra(this);">'+
                        '</div>'+
                    '</div>'+
                    '<div class="row">'+
                        '<div class="col-md-4">'+
                            '<label>Referencia</label>'+
                            '<input type="text" class="form-control" name="referencia" id="referencia" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>País<b style="color:#F44336 !important;">*</b></label>'+
                          '<div class="row">'+
                            '<div class="col-md-4">'+
                              '<span class="input-group-btn">'+
                                '<div id="buscarpaises" class="btn bg-blue waves-effect" onclick="obtenerpaises()">Seleccionar</div>'+
                              '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                              '<div class="form-line">'+
                                '<input type="text" class="form-control" name="clavepais" id="clavepais" required readonly onkeyup="tipoLetra(this)">'+
                                '<input type="hidden" class="form-control" name="pais" id="pais" required readonly>'+
                              '</div>'+
                            '</div>'+     
                          '</div>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Estado<b style="color:#F44336 !important;">*</b></label>'+
                          '<div class="row">'+
                            '<div class="col-md-4">'+
                              '<span class="input-group-btn">'+
                                '<div id="buscarestados" class="btn bg-blue waves-effect" onclick="obtenerestados()">Seleccionar</div>'+
                              '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                              '<div class="form-line">'+
                                '<input type="text" class="form-control" name="nombreestado" id="nombreestado" required readonly onkeyup="tipoLetra(this)">'+
                                '<input type="hidden" class="form-control" name="estado" id="estado" required readonly>'+
                              '</div>'+
                            '</div>'+     
                          '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-4">'+
                        '<label>Municipio<b style="color:#F44336 !important;">*</b></label>'+
                        '<div class="row">'+
                          '<div class="col-md-4">'+
                            '<span class="input-group-btn">'+
                              '<div id="buscarmunicipios" class="btn bg-blue waves-effect" onclick="obtenermunicipios()">Seleccionar</div>'+
                            '</span>'+
                          '</div>'+  
                          '<div class="col-md-8">'+  
                            '<div class="form-line">'+
                              '<input type="text" class="form-control" name="municipio" id="municipio" required readonly onkeyup="tipoLetra(this)">'+
                            '</div>'+
                          '</div>'+     
                        '</div>'+
                      '</div>'+
                      '<div class="col-md-4">'+
                        '<label>Código Postal<b style="color:#F44336 !important;">*</b></label>'+
                        '<div class="row">'+
                          '<div class="col-md-4">'+
                            '<span class="input-group-btn">'+
                              '<div id="buscarcodigospostales" class="btn bg-blue waves-effect" onclick="obtenercodigospostales()">Seleccionar</div>'+
                            '</span>'+
                          '</div>'+  
                          '<div class="col-md-8">'+  
                            '<div class="form-line">'+
                              '<input type="text" class="form-control" name="codigopostal" id="codigopostal" required data-parsley-codigopostal="^[0-9]{5}$">'+
                            '</div>'+
                          '</div>'+     
                        '</div>'+
                      '</div>'+
                      '<div class="col-md-4">'+
                        '<label>Plazo (días)</label>'+
                        '<input type="text" class="form-control" name="plazo" id="plazo" value="1" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-4">'+
                        '<label>Agente</label>'+
                        '<div class="row">'+
                          '<div class="col-md-4">'+
                            '<span class="input-group-btn">'+
                              '<div id="buscaragentes" class="btn bg-blue waves-effect" onclick="obteneragentes()">Seleccionar</div>'+
                            '</span>'+
                          '</div>'+  
                          '<div class="col-md-8">'+  
                            '<div class="form-line">'+
                              '<input type="text" class="form-control" name="nombreagente" id="nombreagente" readonly onkeyup="tipoLetra(this)">'+
                              '<input type="hidden" class="form-control" name="agente" id="agente" readonly>'+
                            '</div>'+
                          '</div>'+     
                        '</div>'+
                      '</div>'+
                      '<div class="col-md-4">'+
                        '<label>Forma de Pago<b style="color:#F44336 !important;">*</b></label>'+
                        '<div class="row">'+
                          '<div class="col-md-4">'+
                            '<span class="input-group-btn">'+
                              '<div id="buscarformaspago" class="btn bg-blue waves-effect" onclick="obtenerformaspago()">Seleccionar</div>'+
                            '</span>'+
                          '</div>'+  
                          '<div class="col-md-8">'+  
                            '<div class="form-line">'+
                              '<input type="text" class="form-control" name="formapago" id="formapago" required readonly onkeyup="tipoLetra(this)">'+
                              '<input type="hidden" class="form-control" name="claveformapago" id="claveformapago" required readonly>'+
                            '</div>'+
                          '</div>'+     
                        '</div>'+
                      '</div>'+
                      '<div class="col-md-4">'+
                        '<label>Método de Pago<b style="color:#F44336 !important;">*</b></label>'+
                        '<div class="row">'+
                          '<div class="col-md-4">'+
                            '<span class="input-group-btn">'+
                              '<div id="buscarmetodospago" class="btn bg-blue waves-effect" onclick="obtenermetodospago()">Seleccionar</div>'+
                            '</span>'+
                          '</div>'+  
                          '<div class="col-md-8">'+  
                            '<div class="form-line">'+
                              '<input type="text" class="form-control" name="metodopago" id="metodopago" required readonly onkeyup="tipoLetra(this)">'+
                              '<input type="hidden" class="form-control" name="clavemetodopago" id="clavemetodopago" required readonly>'+
                            '</div>'+
                          '</div>'+     
                        '</div>'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<label>Uso Cfdi<b style="color:#F44336 !important;">*</b></label>'+
                          '<div class="row">'+
                            '<div class="col-md-4">'+
                              '<span class="input-group-btn">'+
                                '<div id="buscarusoscfdi" class="btn bg-blue waves-effect" onclick="obtenerusoscfdi()">Seleccionar</div>'+
                              '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                              '<div class="form-line">'+
                                '<input type="text" class="form-control" name="usocfdi" id="usocfdi" required readonly onkeyup="tipoLetra(this)">'+
                                '<input type="hidden" class="form-control" name="claveusocfdi" id="claveusocfdi" required readonly>'+
                              '</div>'+
                            '</div>'+     
                          '</div>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Tipo</label>'+
                            '<input type="text" class="form-control" name="tipo" id="tipo" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Crédito Máximo  (0 = Crédito libre)</label>'+
                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="creditomaximo" id="creditomaximo" value="10000.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                        '</div>'+
                    '</div>'+   
                  '</div>'+
                  '<div role="tabpanel" class="tab-pane fade" id="telefonostabs">'+
                    '<div class="row">'+
                        '<div class="col-md-4">'+
                            '<label>Contacto</label>'+
                            '<input type="text" class="form-control" name="contacto" id="contacto" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Teléfonos</label>'+
                            '<input type="text" class="form-control" name="telefonos" id="telefonos" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Celular</label>'+
                            '<input type="text" class="form-control" name="celular" id="celular" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                    '</div>'+
                    '<div class="row">'+
                        '<div class="col-md-4">'+
                            '<label>E-mail 1</label>'+
                            '<input type="text" class="form-control" name="email1" id="email1"  data-parsley-regexemail="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Cuenta Ref</label>'+
                            '<input type="text" class="form-control" name="cuentaref" id="cuentaref" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Cuenta Ser</label>'+
                            '<input type="text" class="form-control" name="cuentaser" id="cuentaser" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                    '</div>'+
                    '<div class="row">'+
                        '<div class="col-md-4">'+
                            '<label>Anotaciones</label>'+
                            '<textarea class="form-control" name="anotaciones" id="anotaciones"  onkeyup="tipoLetra(this);"></textarea>'+
                        '</div>'+
                    '</div>'+
                  '</div>'+  
                  '<div role="tabpanel" class="tab-pane fade" id="utilidadestab">'+
                    '<div class="row">'+
                      '<div class="col-md-12">'+
                        '<h5>UTILIDADES POR MARCAS PARA ESTE CLIENTE&nbsp;&nbsp;&nbsp;</h5>'+
                        '<div class="table-container">'+
                          '<table id="tablautilidadesmarcas" class="scroll tablautilidadesmarcas">'+
                              '<thead class="customercolor">'+
                                  '<tr>'+
                                    '<th>#</th>'+
                                    '<th>Marca</th>'+
                                    '<th>Utilidad1</th>'+
                                    '<th>Utilidad2</th>'+
                                    '<th>Utilidad3</th>'+
                                    '<th>Utilidad4</th>'+
                                    '<th>Utilidad5</th>'+
                                    '<th>Utilidad #</th>'+
                                    '<th>Dcto %</th>'+
                                  '</tr>'+
                              '</thead>'+
                              '<tbody>'+           
                              '</tbody>'+
                          '</table>'+
                        '</div>'+
                      '</div>'+
                    '</div>'+
                  '</div>'+
                  '<div role="tabpanel" class="tab-pane fade" id="precioproductostab">'+
                    '<div class="row">'+
                      '<div class="col-md-12">'+
                          '<div class="col-md-6">'+
                            '<h5>PRECIOS DE PRODUCTOS PARA ESTE CLIENTE&nbsp;&nbsp;&nbsp;</h5>'+
                          '</div>'+
                          '<div class="col-md-6">'+
                            '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto">'+
                            '<input type="hidden" class="form-control" name="numerofilaspreciosproducto" id="numerofilaspreciosproducto">'+
                          '</div>'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-12">'+  
                          '<div class="table-container">'+
                            '<table id="tablapreciosproductos" class="scroll tablapreciosproductos">'+
                                '<thead class="customercolor">'+
                                    '<tr>'+
                                      '<th>Operaciones</th>'+    
                                      '<th>Código</th>'+
                                      '<th>Descripción</th>'+
                                      '<th>Subtotal $</th>'+
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
    $("#numero").val(numerocliente);
    $("#nombre").val(data.cliente.Nombre);
    $("#rfc").val(data.cliente.Rfc);
    $("#calle").val(data.cliente.Calle);
    $("#noexterior").val(data.cliente.noExterior);
    $("#nointerior").val(data.cliente.noInterior);
    $("#colonia").val(data.cliente.Colonia);
    $("#localidad").val(data.cliente.Localidad);
    $("#referencia").val(data.cliente.Referencia);
    $("#plazo").val(data.cliente.Plazo);
    $("#tipo").val(data.cliente.Tipo);
    $("#creditomaximo").val(data.credito);
    $("#contacto").val(data.cliente.Contacto);
    $("#telefonos").val(data.cliente.Telefonos);
    $("#celular").val(data.cliente.Celular);
    $("#email1").val(data.cliente.Email1);
    $("#cuentaref").val(data.cliente.Cuenta);
    $("#cuentaser").val(data.cliente.CuentaServicio);
    $("#anotaciones").val(data.cliente.Anotaciones);
    $("#clavepais").val(data.pais.Clave);
    $("#pais").val(data.pais.Numero);
    $("#nombreestado").val(data.estado.Nombre);
    $("#nombreestado").keyup();
    $("#estado").val(data.estado.Numero);
    $("#municipio").val(data.municipio.Nombre);
    $("#municipio").keyup();
    $("#codigopostal").val(data.codigopostal.Clave);
    $("#formapago").val(data.formadepago.Nombre);
    $("#claveformapago").val(data.formadepago.Clave);
    $("#formapago").keyup();
    $("#metodopago").val(data.metododepago.Nombre);
    $("#clavemetodopago").val(data.metododepago.Clave);
    $("#metodopago").keyup();
    $("#usocfdi").val(data.usocfdi.Nombre);
    $("#claveusocfdi").val(data.usocfdi.Clave);
    $("#usocfdi").keyup();
    if(data.agente != null){
      $("#agente").val(data.agente.Numero);
      $("#nombreagente").val(data.agente.Nombre);
      $("#nombreagente").keyup();
    }
    //tabs utilidades
    $("#tablautilidadesmarcas").append(data.filasutilidadesmarcas);
    //tabs precios productos
    $("#tablapreciosproductos").append(data.filaspreciosproductos);
    $("#numerofilaspreciosproducto").val(data.numerofilaspreciosproductos);
    mostrarmodalformulario('MODIFICACION');
    $('.page-loader-wrapper').css('display', 'none');
    activarbusquedaproducto();//importante activa la busqueda de productos por su codigo
  })
}


//guardar el registro
$("#btnGuardarModificacion").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:clientes_guardar_modificacion,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        if(data == 1){
          msj_errorrfcexistente();
        }else{
          msj_datosguardadoscorrectamente();
          limpiar();
          ocultarmodalformulario();
          limpiarmodales();
        }
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
    form.parsley().validate();
  }
});
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
                                  '<label>DATOS CLIENTE</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Numero" id="idNumero" class="filled-in datotabla" value="Numero" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idNumero">Numero</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Status" id="idStatus" class="filled-in datotabla" value="Status" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idStatus">Status</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Nombre" id="idNombre" class="filled-in datotabla" value="Nombre" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idNombre">Nombre</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Rfc" id="idRfc" class="filled-in datotabla" value="Rfc" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idRfc">Rfc</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Municipio" id="idMunicipio" class="filled-in datotabla" value="Municipio" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idMunicipio">Municipio</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Bloquear" id="idBloquear" class="filled-in datotabla" value="Bloquear" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idBloquear">Bloquear</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FacturarAlCosto" id="idFacturarAlCosto" class="filled-in datotabla" value="FacturarAlCosto" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFacturarAlCosto">FacturarAlCosto</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Plazo" id="idPlazo" class="filled-in datotabla" value="Plazo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPlazo">Plazo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Credito" id="idCredito" class="filled-in datotabla" value="Credito" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCredito">Credito</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+    
                                  '<input type="checkbox" name="Saldo" id="idSaldo" class="filled-in datotabla" value="Saldo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idSaldo">Saldo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FormaPago" id="idFormaPago" class="filled-in datotabla" value="FormaPago" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFormaPago">FormaPago</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Email1" id="idEmail1" class="filled-in datotabla" value="Email1" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEmail1">Email1</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Telefonos" id="idTelefonos" class="filled-in datotabla" value="Telefonos" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTelefonos">Telefonos</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Agente" id="idAgente" class="filled-in datotabla" value="Agente" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idAgente">Agente</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Calle" id="idCalle" class="filled-in datotabla" value="Calle" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCalle">Calle</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="noExterior" id="idnoExterior" class="filled-in datotabla" value="noExterior" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idnoExterior">noExterior</label>'+
                              '</div>'+

                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="noInterior" id="idnoInterior" class="filled-in datotabla" value="noInterior" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idnoInterior">noInterior</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Colonia" id="idColonia" class="filled-in datotabla" value="Colonia" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idColonia">Colonia</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Localidad" id="idLocalidad" class="filled-in datotabla" value="Localidad" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idLocalidad">Localidad</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Referencia" id="idReferencia" class="filled-in datotabla" value="Referencia" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idReferencia">Referencia</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Estado" id="idEstado" class="filled-in datotabla" value="Estado" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEstado">Estado</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Pais" id="idPais" class="filled-in datotabla" value="Pais" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPais">Pais</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="CodigoPostal" id="idCodigoPostal" class="filled-in datotabla" value="CodigoPostal" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCodigoPostal">CodigoPostal</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="MetodoPago" id="idMetodoPago" class="filled-in datotabla" value="MetodoPago" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idMetodoPago">MetodoPago</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="UsoCfdi" id="idUsoCfdi" class="filled-in datotabla" value="UsoCfdi" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idUsoCfdi">UsoCfdi</label>'+
                              '</div>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                          '</div>'+
                          '<div class="col-md-6">'+
                              '<div class="col-md-12 form-check">'+
                                  '<label>DATOS AGENTE</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="NumeroAgente" id="idNumeroAgente" class="filled-in datotabla" value="NumeroAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idNumeroAgente">NumeroAgente</label>'+  
                              '</div>'+
                              '<div class="col-md-4 form-check">'+  
                                  '<input type="checkbox" name="NombreAgente" id="idNombreAgente" class="filled-in datotabla" value="NombreAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idNombreAgente">NombreAgente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="DireccionAgente" id="idDireccionAgente" class="filled-in datotabla" value="DireccionAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idDireccionAgente">DireccionAgente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ColoniaAgente" id="idColoniaAgente" class="filled-in datotabla" value="ColoniaAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idColoniaAgente">ColoniaAgente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="CiudadAgente" id="idCiudadAgente" class="filled-in datotabla" value="CiudadAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idCiudadAgente">CiudadAgente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="CpAgente" id="idCpAgente" class="filled-in datotabla" value="CpAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idCpAgente">CpAgente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="RfcAgente" id="idRfcAgente" class="filled-in datotabla" value="RfcAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idRfcAgente">RfcAgente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ContactoAgente" id="idContactoAgente" class="filled-in datotabla" value="ContactoAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idContactoAgente">ContactoAgente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="TelefonosAgente" id="idTelefonosAgente" class="filled-in datotabla" value="TelefonosAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idTelefonosAgente">TelefonosAgente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="EmailAgente" id="idEmailAgente" class="filled-in datotabla" value="EmailAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idEmailAgente">EmailAgente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="CuentaAgente" id="idCuentaAgente" class="filled-in datotabla" value="CuentaAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idCuentaAgente">CuentaAgente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ComisionAgente" id="idComisionAgente" class="filled-in datotabla" value="ComisionAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idComisionAgente">ComisionAgente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="AnotiacionesAgente" id="idAnotiacionesAgente" class="filled-in datotabla" value="AnotiacionesAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idAnotiacionesAgente">AnotiacionesAgente</label>'+                                     
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