'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
  campos_a_filtrar_en_busquedas();
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
    "pageLength": 1000,
    "sScrollX": "110%",
    "sScrollY": "350px", 
    processing: true,
    'language': {
      'loadingRecords': '&nbsp;',
      'processing': '<div class="spinner"></div>'
    },
    serverSide: true,
    ajax: clientes_obtener,
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
            $(".inputbusquedageneral").val(""); 
          }
      });
    }
  });
  //modificacion al dar doble click
  $('#tbllistado tbody').on('dblclick', 'tr', function () {
    var data = tabla.row( this ).data();
    obtenerdatos(data.Numero);
  });
}
//obtener registros de paises
function obtenerpaises(){
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
  var tpai = $('#tbllistadopais').DataTable({
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
        url: clientes_obtener_paises,
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
              $('#tbllistadopais').DataTable().search( this.value ).draw();
            }
        });
      }
  }); 
  //seleccionar registro al dar doble click
  $('#tbllistadopais tbody').on('dblclick', 'tr', function () {
      var data = tpai.row( this ).data();
      seleccionarpais(data.Nombre, data.Clave);
  }); 
} 
function seleccionarpais(Nombre, Clave){
  var clavepaisanterior = $("#clavepaisanterior").val();
  var clavepais = Clave;
  if(clavepaisanterior != clavepais){
      $("#clavepais").val(Clave);
      $("#clavepaisanterior").val(Clave);
      if(Nombre != null){
      $("#textonombrepais").html(Nombre.substring(0, 40));
      }
      mostrarformulario();
  }
}
//obtener por clave
function obtenerclavepaisporclave(){
  var clavepaisanterior = $("#clavepaisanterior").val();
  var clavepais = $("#clavepais").val();
  if(clavepaisanterior != clavepais){
      if($("#clavepais").parsley().isValid()){
          $.get(clientes_obtener_clave_pais_por_clave, {clavepais:clavepais}, function(data){
              $("#clavepais").val(data.clave);
              $("#clavepaisanterior").val(data.clave);
              if(data.nombre != null){
                  $("#textonombrepais").html(data.nombre.substring(0, 40));
              }
          }) 
      }
  }
}
//regresar clave
function regresarclavepais(){
  var clavepaisanterior = $("#clavepaisanterior").val();
  $("#clavepais").val(clavepaisanterior);
}
//obtener registros de estados
function obtenerestados() {
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
  var test = $('#tbllistadoestado').DataTable({
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
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoestado').DataTable().search( this.value ).draw();
            }
        });
      }
  }); 
  //seleccionar registro al dar doble click
  $('#tbllistadoestado tbody').on('dblclick', 'tr', function () {
      var data = test.row( this ).data();
      seleccionarestado(data.Clave, data.Nombre);
  }); 
}
function seleccionarestado(Clave, Nombre){
  var estadoanterior = $("#estadoanterior").val();
  var estado = Clave;
  if(estadoanterior != estado){
      $("#estado").val(Clave).keyup();
      $("#estadoanterior").val(Clave);
      if(Nombre != null){
      $("#textonombreestado").html(Nombre.substring(0, 40));
      }
      mostrarformulario();
  }
}
//obtener registros de codigos postales
function obtenercodigospostales() {
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
  var tcp = $('#tbllistadocodigopostal').DataTable({
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
        url: clientes_obtener_codigos_postales,
        data: function (d) {
            d.claveestado = $("#estado").val();
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
      }
  });  
  //seleccionar registro al dar doble click
  $('#tbllistadocodigopostal tbody').on('dblclick', 'tr', function () {
      var data = tcp.row( this ).data();
      seleccionarcodigopostal(data.Clave);
  });
}
function seleccionarcodigopostal(Clave){
  var codigopostalanterior = $("#codigopostalanterior").val();
  var codigopostal = Clave;
  if(codigopostalanterior != codigopostal){
      $("#codigopostal").val(Clave);
      $("#codigopostalanterior").val(Clave);
      if(Clave != null){
      $("#textonombrecp").html(Clave.substring(0, 40));
      }
      mostrarformulario();
  }
}
//obtener registros de municipios
function obtenermunicipios() {
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
  var tmun = $('#tbllistadomunicipio').DataTable({
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
        url: clientes_obtener_municipios,
        data: function (d) {
            d.claveestado = $("#estado").val();
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
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadomunicipio').DataTable().search( this.value ).draw();
            }
        });
      }
  });    
  //seleccionar registro al dar doble click
  $('#tbllistadomunicipio tbody').on('dblclick', 'tr', function () {
      var data = tmun.row( this ).data();
      seleccionarmunicipio(data.Nombre);
  });
}
function seleccionarmunicipio(Nombre){
  var municipioanterior = $("#municipioanterior").val();
  var municipio = Nombre;
  if(municipioanterior != municipio){
      $("#municipio").val(Nombre).keyup();
      $("#municipioanterior").val(Nombre);
      if(Nombre != null){
      $("#textonombremunicipio").html(Nombre.substring(0, 40));
      }
      mostrarformulario();
  }
}
//obtener registros de agentes
function obteneragentes(){
  ocultarformulario();
  var tablaagentes =  '<div class="modal-header '+background_forms_and_modals+'">'+
                        '<h4 class="modal-title">Agentes</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<div class="table-responsive">'+
                                    '<table id="tbllistadoagente" class="tbllistadoagente table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                        '<thead class="'+background_tables+'">'+
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
  var tage = $('#tbllistadoagente').DataTable({
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
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoagente').DataTable().search( this.value ).draw();
            }
        });
      }
  });   
  //seleccionar registro al dar doble click
  $('#tbllistadoagente tbody').on('dblclick', 'tr', function () {
      var data = tage.row( this ).data();
      seleccionaragente(data.Numero, data.Nombre);
  });
} 
function seleccionaragente(Numero, Nombre){
  var agenteanterior = $("#agenteanterior").val();
  var agente = Numero;
  if(agenteanterior != agente){
      $("#agente").val(Numero);
      $("#agenteanterior").val(Numero);
      if(Nombre != null){
      $("#textonombreagente").html(Nombre.substring(0, 40));
      }
      mostrarformulario();
  }
}
//obtener por clave
function obteneragentepornumero(){
  var agenteanterior = $("#agenteanterior").val();
  var agente = $("#agente").val();
  if(agenteanterior != agente){
      if($("#agente").parsley().isValid()){
          $.get(clientes_obtener_agente_por_numero, {agente:agente}, function(data){
              $("#agente").val(data.numero);
              $("#agenteanterior").val(data.numero);
              if(data.nombre != null){
                  $("#textonombreagente").html(data.nombre.substring(0, 40));
              }
          }) 
      }
  }
}
//regresar clave
function regresarnumeroagente(){
  var agenteanterior = $("#agenteanterior").val();
  $("#agente").val(agenteanterior);
}
//obtener registros de formas de pago
function obtenerformaspago(){
  ocultarformulario();
  var tablaformaspago = '<div class="modal-header '+background_forms_and_modals+'">'+
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
        url: clientes_obtener_formas_pago,
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false }
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
      }
  });  
  //seleccionar registro al dar doble click
  $('#tbllistadoformapago tbody').on('dblclick', 'tr', function () {
      var data = tforpag.row( this ).data();
      seleccionarformapago(data.Clave, data.Nombre);
  });
} 
function seleccionarformapago(Clave, Nombre){
  var claveformapagoanterior = $("#claveformapagoanterior").val();
  var claveformapago = Clave;
  if(claveformapagoanterior != claveformapago){
      $("#claveformapago").val(Clave);
      $("#claveformapagoanterior").val(Clave);
      if(Nombre != null){
        $("#textonombreformapago").html(Nombre.substring(0, 40));
      }
      mostrarformulario();
  }
}
//obtener por clave
function obtenerformapagoporclave(){
  var claveformapagoanterior = $("#claveformapagoanterior").val();
  var claveformapago = $("#claveformapago").val();
  if(claveformapagoanterior != claveformapago){
      if($("#claveformapago").parsley().isValid()){
          $.get(clientes_obtener_formapago_por_clave, {claveformapago:claveformapago}, function(data){
              $("#claveformapago").val(data.clave);
              $("#claveformapagoanterior").val(data.clave);
              if(data.nombre != null){
                  $("#textonombreformapago").html(data.nombre.substring(0, 40));
              }
          }) 
      }
  }
}
//regresar clave
function regresarclaveformapago(){
  var claveformapagoanterior = $("#claveformapagoanterior").val();
  $("#claveformapago").val(claveformapagoanterior);
}
//obtener registros de formas de pago
function obtenermetodospago(){
  ocultarformulario();
  var tablasmetodospago = '<div class="modal-header '+background_forms_and_modals+'">'+
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
  $("#contenidomodaltablas").html(tablasmetodospago);   
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
        url: clientes_obtener_metodos_pago,
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false }
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
      }
  });  
  //seleccionar registro al dar doble click
  $('#tbllistadometodopago tbody').on('dblclick', 'tr', function () {
      var data = tmetpag.row( this ).data();
      seleccionarmetodopago(data.Clave, data.Nombre);
  });
} 
function seleccionarmetodopago(Clave, Nombre){
  var clavemetodopagoanterior = $("#clavemetodopagoanterior").val();
  var clavemetodopago = Clave;
  if(clavemetodopagoanterior != clavemetodopago){
      $("#clavemetodopago").val(Clave);
      $("#clavemetodopagoanterior").val(Clave);
      if(Nombre != null){
        $("#textonombremetodopago").html(Nombre.substring(0, 40));
      }
      mostrarformulario();
  }
}
//obtener por clave
function obtenermetodopagoporclave(){
  var clavemetodopagoanterior = $("#clavemetodopagoanterior").val();
  var clavemetodopago= $("#clavemetodopago").val();
  if(clavemetodopagoanterior != clavemetodopago){
      if($("#clavemetodopago").parsley().isValid()){
          $.get(clientes_obtener_metodopago_por_clave, {clavemetodopago:clavemetodopago}, function(data){
              $("#clavemetodopago").val(data.clave);
              $("#clavemetodopagoanterior").val(data.clave);
              if(data.nombre != null){
                  $("#textonombremetodopago").html(data.nombre.substring(0, 40));
              }
          }) 
      }
  }
}
//regresar clave
function regresarclavemetodopago(){
  var clavemetodopagoanterior = $("#clavemetodopagoanterior").val();
  $("#clavemetodopago").val(clavemetodopagoanterior);
}
//obtener registros de formas de pago
function obtenerusoscfdi(){
  ocultarformulario();
  var tablasusoscfdi =  '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Métodos Pago</h4>'+
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
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadousocfdi').DataTable().search( this.value ).draw();
            }
        });
      }
  });   
  //seleccionar registro al dar doble click
  $('#tbllistadousocfdi tbody').on('dblclick', 'tr', function () {
      var data = tusocfdi.row( this ).data();
      seleccionarusocfdi(data.Clave, data.Nombre);
  });
} 
function seleccionarusocfdi(Clave, Nombre){
  var claveusocfdianterior = $("#claveusocfdianterior").val();
  var claveusocfdi = Clave;
  if(claveusocfdianterior != claveusocfdi){
      $("#claveusocfdi").val(Clave);
      $("#claveusocfdianterior").val(Clave);
      if(Nombre != null){
        $("#textonombreusocfdi").html(Nombre.substring(0, 40));
      }
      mostrarformulario();
  }
}
//obtener por clave
function obtenerusocfdiporclave(){
  var claveusocfdianterior = $("#claveusocfdianterior").val();
  var claveusocfdi= $("#claveusocfdi").val();
  if(claveusocfdianterior != claveusocfdi){
      if($("#claveusocfdi").parsley().isValid()){
          $.get(clientes_obtener_usocfdi_por_clave, {claveusocfdi:claveusocfdi}, function(data){
              $("#claveusocfdi").val(data.clave);
              $("#claveusocfdianterior").val(data.clave);
              if(data.nombre != null){
                  $("#textonombreusocfdi").html(data.nombre.substring(0, 40));
              }
          }) 
      }
  }
}
//regresar clave
function regresarclaveusocfdi(){
  var claveusocfdianterior = $("#claveusocfdianterior").val();
  $("#claveusocfdi").val(claveusocfdianterior);
}
//obtener registros
function obtenerregimenesfiscales(){
  ocultarformulario();
  var tablaregimenesfiscales = '<div class="modal-header '+background_forms_and_modals+'">'+
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
        url: clientes_obtener_regimenes_fiscales,
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
              $('#tbllistadoregimenfiscal').DataTable().search( this.value ).draw();
            }
        });
      }
  });  
  //seleccionar registro al dar doble click
  $('#tbllistadoregimenfiscal tbody').on('dblclick', 'tr', function () {
      var data = tregfis.row( this ).data();
      seleccionarregimenfiscal(data.Clave, data.Nombre);
  });
} 
function seleccionarregimenfiscal(Clave, Nombre){
  var claveregimenfiscalanterior = $("#claveregimenfiscalanterior").val();
  var claveregimenfiscal = Clave;
  if(claveregimenfiscalanterior != claveregimenfiscal){
      $("#claveregimenfiscal").val(Clave);
      $("#claveregimenfiscalanterior").val(Clave);
      if(Nombre != null){
        $("#textonombreregimenfiscal").html(Nombre.substring(0, 40));
      }
      mostrarformulario();
  }
}
//obtener por clave
function obtenerregimenfiscalporclave(){
  var claveregimenfiscalanterior = $("#claveregimenfiscalanterior").val();
  var claveregimenfiscal= $("#claveregimenfiscal").val();
  if(claveregimenfiscalanterior != claveregimenfiscal){
      if($("#claveregimenfiscal").parsley().isValid()){
          $.get(clientes_obtener_regimenfiscal_por_clave, {claveregimenfiscal:claveregimenfiscal}, function(data){
              $("#claveregimenfiscal").val(data.clave);
              $("#claveregimenfiscalanterior").val(data.clave);
              if(data.nombre != null){
                  $("#textonombreregimenfiscal").html(data.nombre.substring(0, 40));
              }
          }) 
      }
  }
}
//regresar clave
function regresarclaveregimenfiscal(){
  var claveregimenfiscalanterior = $("#claveregimenfiscalanterior").val();
  $("#claveregimenfiscal").val(claveregimenfiscalanterior);
}
//buscar si el rfc escrito ya esta en catalogo
function buscarrfcencatalogo(){
  var rfc = $("#rfc").val();
  $.get(clientes_buscar_rfc_en_tabla,{rfc:rfc },function(existerfc){
      if(existerfc > 0){
        msj_errorrfcexistente();
      }
  });
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
                          '<input type="text" class="form-control inputnext" name="rfc" id="rfc" required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]"  onchange="buscarrfcencatalogo();" onkeyup="tipoLetra(this);mayusculas(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Calle <b style="color:#F44336 !important;">*</b></label>'+
                          '<input type="text" class="form-control inputnext" name="calle" id="calle" required data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-2">'+
                          '<label>No. Exterior <b style="color:#F44336 !important;">*</b></label>'+
                          '<input type="text" class="form-control inputnext" name="noexterior" id="noexterior" required data-parsley-length="[1, 10]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-2">'+
                          '<label>No. Interior</label>'+
                          '<input type="text" class="form-control inputnext" name="nointerior" id="nointerior" data-parsley-length="[1, 10]" onkeyup="tipoLetra(this);">'+
                      '</div>'+ 
                  '</div>'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                          '<label>Colonia<b style="color:#F44336 !important;">*</b></label>'+
                          '<input type="text" class="form-control inputnext" name="colonia" id="colonia" required data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Localidad<b style="color:#F44336 !important;">*</b></label>'+
                          '<input type="text" class="form-control inputnext" name="localidad" id="localidad" required data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Referencia</label>'+
                          '<input type="text" class="form-control inputnext" name="referencia" id="referencia" data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                  '</div>'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                        '<label>País<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombrepais"></span></label>'+
                        '<div class="row">'+
                          '<div class="col-md-4">'+
                            '<span class="input-group-btn">'+
                              '<div id="buscarpaises" class="btn bg-blue waves-effect" onclick="obtenerpaises()">Seleccionar</div>'+
                            '</span>'+
                          '</div>'+  
                          '<div class="col-md-8">'+  
                            '<div class="form-line">'+
                              '<input type="text" class="form-control inputnext" name="clavepais" id="clavepais" value="MEX" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                              '<input type="hidden" class="form-control" name="clavepaisanterior" id="clavepaisanterior" readonly data-parsley-length="[1, 5]">'+
                              '<input type="hidden" class="form-control" name="pais" id="pais" value="151" readonly>'+
                            '</div>'+
                          '</div>'+     
                        '</div>'+
                      '</div>'+
                      '<div class="col-md-4">'+
                        '<label>Estado<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreestado"></span></label>'+
                        '<div class="row">'+
                          '<div class="col-md-4">'+
                            '<span class="input-group-btn">'+
                              '<div id="buscarestados" class="btn bg-blue waves-effect" onclick="obtenerestados()">Seleccionar</div>'+
                            '</span>'+
                          '</div>'+  
                          '<div class="col-md-8">'+  
                            '<div class="form-line">'+
                              '<input type="text" class="form-control inputnext" name="estado" id="estado" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                              '<input type="hidden" class="form-control" name="nombreestado" id="nombreestado">'+
                              '<input type="hidden" class="form-control" name="estadoanterior" id="estadoanterior" readonly data-parsley-length="[1, 5]" >'+
                            '</div>'+
                          '</div>'+     
                        '</div>'+
                      '</div>'+
                    '<div class="col-md-4">'+
                      '<label>Municipio<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombremunicipio"></span></label>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<span class="input-group-btn">'+
                            '<div id="buscarmunicipios" class="btn bg-blue waves-effect" onclick="obtenermunicipios()">Seleccionar</div>'+
                          '</span>'+
                        '</div>'+  
                        '<div class="col-md-8">'+  
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="municipio" id="municipio" required data-parsley-length="[1, 100]" onkeyup="tipoLetra(this)">'+
                            '<input type="hidden" class="form-control" name="municipioanterior" id="municipioanterior" readonly data-parsley-length="[1, 100]">'+
                          '</div>'+
                        '</div>'+     
                      '</div>'+
                    '</div>'+
                  '</div>'+
                  '<div class="row">'+
                    '<div class="col-md-4">'+
                      '<label>Código Postal<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombrecp"></span></label>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<span class="input-group-btn">'+
                            '<div id="buscarcodigospostales" class="btn bg-blue waves-effect" onclick="obtenercodigospostales()">Seleccionar</div>'+
                          '</span>'+
                        '</div>'+  
                        '<div class="col-md-8">'+  
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="codigopostal" id="codigopostal" required data-parsley-codigopostal="^[0-9]{5}$" data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                            '<input type="hidden" class="form-control" name="codigopostalanterior" id="codigopostalanterior" data-parsley-codigopostal="^[0-9]{5}$" data-parsley-length="[1, 5]">'+
                          '</div>'+
                        '</div>'+     
                      '</div>'+
                    '</div>'+
                    '<div class="col-md-4">'+
                      '<label>Plazo (días)</label>'+
                      '<input type="text" class="form-control inputnext" name="plazo" id="plazo" value="1" onkeyup="tipoLetra(this);">'+
                    '</div>'+
                    '<div class="col-md-4">'+
                      '<label>Agente<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreagente"></span></label>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<span class="input-group-btn">'+
                            '<div id="buscaragentes" class="btn bg-blue waves-effect" onclick="obteneragentes()">Seleccionar</div>'+
                          '</span>'+
                        '</div>'+  
                        '<div class="col-md-8">'+  
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="agente" id="agente" required onkeyup="tipoLetra(this)">'+
                            '<input type="hidden" class="form-control" name="nombreagente" id="nombreagente"  readonly>'+
                            '<input type="hidden" class="form-control" name="agenteanterior" id="agenteanterior" readonly>'+
                          '</div>'+
                        '</div>'+     
                      '</div>'+
                    '</div>'+
                  '</div>'+
                  '<div class="row">'+
                    '<div class="col-md-4">'+
                      '<label>Forma de Pago<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreformapago"></span></label>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<span class="input-group-btn">'+
                            '<div id="buscarformaspago" class="btn bg-blue waves-effect" onclick="obtenerformaspago()">Seleccionar</div>'+
                          '</span>'+
                        '</div>'+  
                        '<div class="col-md-8">'+  
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="claveformapago" id="claveformapago"  required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                            '<input type="hidden" class="form-control" name="formapago" id="formapago" readonly>'+
                            '<input type="hidden" class="form-control" name="claveformapagoanterior" id="claveformapagoanterior" readonly data-parsley-length="[1, 5]">'+
                          '</div>'+
                        '</div>'+     
                      '</div>'+
                    '</div>'+
                    '<div class="col-md-4">'+
                      '<label>Método de Pago<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombremetodopago"></span></label>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<span class="input-group-btn">'+
                            '<div id="buscarmetodospago" class="btn bg-blue waves-effect" onclick="obtenermetodospago()">Seleccionar</div>'+
                          '</span>'+
                        '</div>'+  
                        '<div class="col-md-8">'+  
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="clavemetodopago" id="clavemetodopago" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                            '<input type="hidden" class="form-control" name="metodopago" id="metodopago" readonly >'+
                            '<input type="hidden" class="form-control" name="clavemetodopagoanterior" id="clavemetodopagoanterior" readonly data-parsley-length="[1, 5]">'+
                          '</div>'+
                        '</div>'+     
                      '</div>'+
                    '</div>'+
                    '<div class="col-md-4">'+
                        '<label>Uso Cfdi<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreusocfdi"></span></label>'+
                        '<div class="row">'+
                          '<div class="col-md-4">'+
                            '<span class="input-group-btn">'+
                              '<div id="buscarusoscfdi" class="btn bg-blue waves-effect" onclick="obtenerusoscfdi()">Seleccionar</div>'+
                            '</span>'+
                          '</div>'+  
                          '<div class="col-md-8">'+  
                            '<div class="form-line">'+
                              '<input type="text" class="form-control inputnext" name="claveusocfdi" id="claveusocfdi" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                              '<input type="hidden" class="form-control" name="usocfdi" id="usocfdi" readonly>'+
                              '<input type="hidden" class="form-control" name="claveusocfdianterior" id="claveusocfdianterior" readonly data-parsley-length="[1, 5]">'+
                            '</div>'+
                          '</div>'+     
                        '</div>'+
                    '</div>'+
                  '</div>'+
                  '<div class="row">'+
                    '<div class="col-md-4">'+
                      '<label>Regimen Fiscal<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreregimenfiscal"></span></label>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<span class="input-group-btn">'+
                            '<div id="buscarregimenesfiscales" class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscales()">Seleccionar</div>'+
                          '</span>'+
                        '</div>'+  
                        '<div class="col-md-8">'+  
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="claveregimenfiscal" id="claveregimenfiscal" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                            '<input type="hidden" class="form-control" name="regimenfiscal" id="regimenfiscal" readonly>'+
                            '<input type="hidden" class="form-control" name="claveregimenfiscalanterior" id="claveregimenfiscalanterior" readonly data-parsley-length="[1, 5]">'+
                          '</div>'+
                        '</div>'+     
                      '</div>'+
                    '</div>'+
                    '<div class="col-md-4">'+
                          '<label>Tipo</label>'+
                          '<input type="text" class="form-control inputnext" name="tipo" id="tipo" onkeyup="tipoLetra(this);">'+
                    '</div>'+
                    '<div class="col-md-4">'+
                          '<label>Crédito Máximo</label>'+
                          '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="creditomaximo" id="creditomaximo" value="10000.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                    '</div>'+
                  '</div>'+   
                '</div>'+
                '<div role="tabpanel" class="tab-pane fade" id="telefonostabs">'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                          '<label>Contacto</label>'+
                          '<input type="text" class="form-control inputnext" name="contacto" id="contacto" data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Teléfonos</label>'+
                          '<input type="text" class="form-control inputnext" name="telefonos" id="telefonos" data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Celular</label>'+
                          '<input type="text" class="form-control inputnext" name="celular" id="celular" data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                  '</div>'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                          '<label>E-mail 1</label>'+
                          '<input type="text" class="form-control inputnext" name="email1" id="email1"  data-parsley-type="email" data-parsley-length="[1, 100]">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>E-mail 2</label>'+
                          '<input type="text" class="form-control inputnext" name="email2" id="email2"  data-parsley-type="email" data-parsley-length="[1, 100]">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>E-mail 3</label>'+
                          '<input type="text" class="form-control inputnext" name="email3" id="email3"  data-parsley-type="email" data-parsley-length="[1, 100]">'+
                      '</div>'+
                  '</div>'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                          '<label>Cuenta Ref</label>'+
                          '<input type="text" class="form-control inputnext" name="cuentaref" id="cuentaref" data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Cuenta Ser</label>'+
                          '<input type="text" class="form-control inputnext" name="cuentaser" id="cuentaser" data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Anotaciones</label>'+
                          '<textarea class="form-control inputnext" name="anotaciones" id="anotaciones"  data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
                      '</div>'+
                  '</div>'+
                '</div>'+  
              '</div>';
  $("#tabsform").html(tabs);
  obtenultimonumero();
  $("#ModalAlta").modal('show');
  setTimeout(function(){$("#nombre").focus();},500);
  obtenerclavepaisporclave();
  //activar busqueda para clave producto
  $('#clavepais').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
          obtenerclavepaisporclave();
      }
  });
  //regresar clave
  $('#clavepais').on('change', function(e) {
      regresarclavepais();
  });
  //activar busqueda
  $('#agente').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
          obteneragentepornumero();
      }
  });
  //regresar clave
  $('#agente').on('change', function(e) {
      regresarnumeroagente();
  });
  //activar busqueda
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
  //activar busqueda
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
  //activar busqueda
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
  //activar busqueda
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
  //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
  $(".inputnext").keyup(function (e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    var index = $(this).index(".inputnext");          
    switch(code){
      case 13:
        $(".inputnext").eq(index + 1).focus().select(); 
        break;
      case 39:
        $(".inputnext").eq(index + 1).focus().select(); 
        break;
      case 37:
        $(".inputnext").eq(index - 1).focus().select(); 
        break;
    }
  });
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
        buscardatosfilaproducto();
      }
  });
}  
//listar productos para tab consumos
function listarproductos(){
  ocultarformulario();
  var tablaproductos = '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Productos</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                          '<div class="row">'+
                              '<div class="col-md-12">'+
                                  '<div class="table-responsive">'+
                                      '<table id="tbllistadoproducto" class="tbllistadoproducto table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                          '<thead class="'+background_tables+'">'+
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
      keys: true,
      "pageLength": 250,
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
        $buscar.focus();
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
function buscardatosfilaproducto(){
  var codigoabuscar = $("#codigoabuscar").val();
  $.get(clientes_obtener_datos_producto_agregar_fila,{codigoabuscar:codigoabuscar}, function(data){
      if(data.existeproducto > 0){
        agregarfilaproducto(data.codigo, data.nombreproducto);
      }
      $("#codigoabuscar").val("");
  })

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
                            '<input type="text" class="form-control inputnext" name="rfc" id="rfc" required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Calle <b style="color:#F44336 !important;">*</b></label>'+
                            '<input type="text" class="form-control inputnext" name="calle" id="calle" required data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<label>No. Exterior <b style="color:#F44336 !important;">*</b></label>'+
                            '<input type="text" class="form-control inputnext" name="noexterior" id="noexterior" required data-parsley-length="[1, 10]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<label>No. Interior</label>'+
                            '<input type="text" class="form-control inputnext" name="nointerior" id="nointerior" data-parsley-length="[1, 10]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                      '</div>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                            '<label>Colonia<b style="color:#F44336 !important;">*</b></label>'+
                            '<input type="text" class="form-control inputnext" name="colonia" id="colonia" required data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Localidad<b style="color:#F44336 !important;">*</b></label>'+
                            '<input type="text" class="form-control inputnext" name="localidad" id="localidad" required data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Referencia</label>'+
                            '<input type="text" class="form-control inputnext" name="referencia" id="referencia" data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                      '</div>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<label>País<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombrepais"></span></label>'+
                          '<div class="row">'+
                            '<div class="col-md-4">'+
                              '<span class="input-group-btn">'+
                                '<div id="buscarpaises" class="btn bg-blue waves-effect" onclick="obtenerpaises()">Seleccionar</div>'+
                              '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                              '<div class="form-line">'+
                                '<input type="text" class="form-control inputnext" name="clavepais" id="clavepais" value="MEX" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                                '<input type="hidden" class="form-control" name="clavepaisanterior" id="clavepaisanterior" readonly data-parsley-length="[1, 5]">'+
                                '<input type="hidden" class="form-control" name="pais" id="pais" value="151" readonly>'+
                              '</div>'+
                            '</div>'+     
                          '</div>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Estado<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreestado"></span></label>'+
                          '<div class="row">'+
                            '<div class="col-md-4">'+
                              '<span class="input-group-btn">'+
                                '<div id="buscarestados" class="btn bg-blue waves-effect" onclick="obtenerestados()">Seleccionar</div>'+
                              '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                              '<div class="form-line">'+
                                '<input type="text" class="form-control inputnext" name="estado" id="estado" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                                '<input type="hidden" class="form-control" name="nombreestado" id="nombreestado">'+
                                '<input type="hidden" class="form-control" name="estadoanterior" id="estadoanterior" readonly data-parsley-length="[1, 5]" >'+
                              '</div>'+
                            '</div>'+     
                          '</div>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Municipio<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombremunicipio"></span></label>'+
                          '<div class="row">'+
                            '<div class="col-md-4">'+
                              '<span class="input-group-btn">'+
                                '<div id="buscarmunicipios" class="btn bg-blue waves-effect" onclick="obtenermunicipios()">Seleccionar</div>'+
                              '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                              '<div class="form-line">'+
                                '<input type="text" class="form-control inputnext" name="municipio" id="municipio" required data-parsley-length="[1, 100]" onkeyup="tipoLetra(this)">'+
                                '<input type="hidden" class="form-control" name="municipioanterior" id="municipioanterior" readonly data-parsley-length="[1, 100]">'+
                              '</div>'+
                            '</div>'+     
                          '</div>'+
                        '</div>'+
                      '</div>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<label>Código Postal<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombrecp"></span></label>'+
                          '<div class="row">'+
                            '<div class="col-md-4">'+
                              '<span class="input-group-btn">'+
                                '<div id="buscarcodigospostales" class="btn bg-blue waves-effect" onclick="obtenercodigospostales()">Seleccionar</div>'+
                              '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                              '<div class="form-line">'+
                                '<input type="text" class="form-control inputnext" name="codigopostal" id="codigopostal" required data-parsley-codigopostal="^[0-9]{5}$" data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                                '<input type="hidden" class="form-control" name="codigopostalanterior" id="codigopostalanterior" data-parsley-codigopostal="^[0-9]{5}$" data-parsley-length="[1, 5]">'+
                              '</div>'+
                            '</div>'+     
                          '</div>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Plazo (días)</label>'+
                          '<input type="text" class="form-control inputnext" name="plazo" id="plazo" value="1" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Agente<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreagente"></span></label>'+
                          '<div class="row">'+
                            '<div class="col-md-4">'+
                              '<span class="input-group-btn">'+
                                '<div id="buscaragentes" class="btn bg-blue waves-effect" onclick="obteneragentes()">Seleccionar</div>'+
                              '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                              '<div class="form-line">'+
                                '<input type="text" class="form-control inputnext" name="agente" id="agente" required onkeyup="tipoLetra(this)">'+
                                '<input type="hidden" class="form-control" name="nombreagente" id="nombreagente"  readonly>'+
                                '<input type="hidden" class="form-control" name="agenteanterior" id="agenteanterior" readonly>'+
                              '</div>'+
                            '</div>'+     
                          '</div>'+
                        '</div>'+
                      '</div>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<label>Forma de Pago<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreformapago"></span></label>'+
                          '<div class="row">'+
                            '<div class="col-md-4">'+
                              '<span class="input-group-btn">'+
                                '<div id="buscarformaspago" class="btn bg-blue waves-effect" onclick="obtenerformaspago()">Seleccionar</div>'+
                              '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                              '<div class="form-line">'+
                                '<input type="text" class="form-control inputnext" name="claveformapago" id="claveformapago"  required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                                '<input type="hidden" class="form-control" name="formapago" id="formapago" readonly>'+
                                '<input type="hidden" class="form-control" name="claveformapagoanterior" id="claveformapagoanterior" readonly data-parsley-length="[1, 5]">'+
                              '</div>'+
                            '</div>'+     
                          '</div>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Método de Pago<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombremetodopago"></span></label>'+
                          '<div class="row">'+
                            '<div class="col-md-4">'+
                              '<span class="input-group-btn">'+
                                '<div id="buscarmetodospago" class="btn bg-blue waves-effect" onclick="obtenermetodospago()">Seleccionar</div>'+
                              '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                              '<div class="form-line">'+
                                '<input type="text" class="form-control inputnext" name="clavemetodopago" id="clavemetodopago" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                                '<input type="hidden" class="form-control" name="metodopago" id="metodopago" readonly >'+
                                '<input type="hidden" class="form-control" name="clavemetodopagoanterior" id="clavemetodopagoanterior" readonly data-parsley-length="[1, 5]">'+
                              '</div>'+
                            '</div>'+     
                          '</div>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Uso Cfdi<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreusocfdi"></span></label>'+
                          '<div class="row">'+
                            '<div class="col-md-4">'+
                              '<span class="input-group-btn">'+
                                '<div id="buscarusoscfdi" class="btn bg-blue waves-effect" onclick="obtenerusoscfdi()">Seleccionar</div>'+
                              '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                              '<div class="form-line">'+
                                '<input type="text" class="form-control inputnext" name="claveusocfdi" id="claveusocfdi" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                                '<input type="hidden" class="form-control" name="usocfdi" id="usocfdi" readonly>'+
                                '<input type="hidden" class="form-control" name="claveusocfdianterior" id="claveusocfdianterior" readonly data-parsley-length="[1, 5]">'+
                              '</div>'+
                            '</div>'+     
                          '</div>'+
                        '</div>'+
                      '</div>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<label>Regimen Fiscal<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreregimenfiscal"></span></label>'+
                          '<div class="row">'+
                            '<div class="col-md-4">'+
                              '<span class="input-group-btn">'+
                                '<div id="buscarregimenesfiscales" class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscales()">Seleccionar</div>'+
                              '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                              '<div class="form-line">'+
                                '<input type="text" class="form-control inputnext" name="claveregimenfiscal" id="claveregimenfiscal" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+
                                '<input type="hidden" class="form-control" name="regimenfiscal" id="regimenfiscal" readonly>'+
                                '<input type="hidden" class="form-control" name="claveregimenfiscalanterior" id="claveregimenfiscalanterior" readonly data-parsley-length="[1, 5]">'+
                              '</div>'+
                            '</div>'+     
                          '</div>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Tipo</label>'+
                            '<input type="text" class="form-control inputnext" name="tipo" id="tipo" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Crédito Máximo</label>'+
                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="creditomaximo" id="creditomaximo" value="10000.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                        '</div>'+
                      '</div>'+   
                  '</div>'+
                  '<div role="tabpanel" class="tab-pane fade" id="telefonostabs">'+
                    '<div class="row">'+
                        '<div class="col-md-4">'+
                            '<label>Contacto</label>'+
                            '<input type="text" class="form-control inputnext" name="contacto" id="contacto" data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Teléfonos</label>'+
                            '<input type="text" class="form-control inputnext" name="telefonos" id="telefonos" data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Celular</label>'+
                            '<input type="text" class="form-control inputnext" name="celular" id="celular" data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                    '</div>'+
                    '<div class="row">'+
                        '<div class="col-md-4">'+
                            '<label>E-mail 1</label>'+
                            '<input type="text" class="form-control inputnext" name="email1" id="email1"  data-parsley-regexemail="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/" data-parsley-length="[1, 100]">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>E-mail 2</label>'+
                            '<input type="text" class="form-control inputnext" name="email2" id="email2"  data-parsley-regexemail="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/" data-parsley-length="[1, 100]">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>E-mail 3</label>'+
                            '<input type="text" class="form-control inputnext" name="email3" id="email3"  data-parsley-regexemail="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/" data-parsley-length="[1, 100]">'+
                        '</div>'+
                    '</div>'+
                    '<div class="row">'+
                        '<div class="col-md-4">'+
                            '<label>Cuenta Ref</label>'+
                            '<input type="text" class="form-control inputnext" name="cuentaref" id="cuentaref" data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Cuenta Ser</label>'+
                            '<input type="text" class="form-control inputnext" name="cuentaser" id="cuentaser" data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Anotaciones</label>'+
                            '<textarea class="form-control inputnext" name="anotaciones" id="anotaciones"  data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
                        '</div>'+
                    '</div>'+
                  '</div>'+  
                  '<div role="tabpanel" class="tab-pane fade" id="utilidadestab">'+
                    '<div class="row">'+
                      '<div class="col-md-12">'+
                        '<h5>UTILIDADES POR MARCAS PARA ESTE CLIENTE&nbsp;&nbsp;&nbsp;</h5>'+
                        '<div class="table-container">'+
                          '<table id="tablautilidadesmarcas" class="scroll tablautilidadesmarcas">'+
                              '<thead class="'+background_tables+'">'+
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
                          '<div class="col-md-4">'+
                            '<h5>PRECIOS DE PRODUCTOS PARA ESTE CLIENTE&nbsp;&nbsp;&nbsp;</h5>'+
                          '</div>'+
                          '<div class="col-md-2">'+
                              '<div id="botonbuscarproductos" class="btn btn-block bg-blue waves-effect" onclick="listarproductos()">Ver Productos</div>'+
                          '</div>'+
                          '<div class="col-md-6">'+
                            '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto y presiona enter" onkeyup="tipoLetra(this)">'+
                            '<input type="hidden" class="form-control" name="numerofilaspreciosproducto" id="numerofilaspreciosproducto">'+
                          '</div>'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-12">'+  
                          '<div class="table-container">'+
                            '<table id="tablapreciosproductos" class="scroll tablapreciosproductos">'+
                                '<thead class="'+background_tables+'">'+
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
    $("#email2").val(data.cliente.Email2);
    $("#email3").val(data.cliente.Email3);
    $("#cuentaref").val(data.cliente.Cuenta);
    $("#cuentaser").val(data.cliente.CuentaServicio);
    $("#anotaciones").val(data.cliente.Anotaciones);
    if(data.pais != null){
      $("#clavepais").val(data.pais.Clave);
      $("#clavepaisanterior").val(data.pais.Clave);
      $("#pais").val(data.pais.Numero);
      $("#textonombreagente").html(data.pais.Numero);
    }
    if(data.estado != null){
      $("#nombreestado").val(data.estado.Nombre);
      $("#estado").val(data.estado.Clave).keyup();
      $("#estadoanterior").val(data.estado.Clave);
      $("#textonombreestado").html(data.estado.Nombre);
    }
    if(data.municipio != null){
      $("#municipio").val(data.municipio.Nombre).keyup();
      $("#municipioanterior").val(data.municipio.Nombre);
      $("#codigopostal").val(data.codigopostal.Clave);
      $("#codigopostalanterior").val(data.codigopostal.Clave);
      $("#textonombremunicipio").html(data.municipio.Nombre);
      $("#textonombrecp").html(data.codigopostal.Clave);
    }
    if(data.formadepago != null){
      $("#formapago").val(data.formadepago.Nombre);
      $("#claveformapago").val(data.formadepago.Clave);
      $("#claveformapagoanterior").val(data.formadepago.Clave);
      $("#textonombreformapago").html(data.formadepago.Nombre);
    }
    if(data.metododepago != null){
      $("#metodopago").val(data.metododepago.Nombre);
      $("#clavemetodopago").val(data.metododepago.Clave);
      $("#clavemetodopagoanterior").val(data.metododepago.Clave);
      $("#textonombremetodopago").html(data.metododepago.Nombre);
    }
    if(data.usocfdi != null){
      $("#usocfdi").val(data.usocfdi.Nombre);
      $("#claveusocfdi").val(data.usocfdi.Clave);
      $("#claveusocfdianterior").val(data.usocfdi.Clave);
      $("#textonombreusocfdi").html(data.usocfdi.Nombre);
    }
    if(data.regimenfiscal != null){
      $("#regimenfiscal").val(data.regimenfiscal.Nombre);
      $("#claveregimenfiscal").val(data.regimenfiscal.Clave);
      $("#claveregimenfiscalanterior").val(data.regimenfiscal.Clave);
      $("#textonombreregimenfiscal").html(data.regimenfiscal.Nombre);
    }
    if(data.agente != null){
      $("#agente").val(data.agente.Numero);
      $("#agenteanterior").val(data.agente.Numero);
      $("#nombreagente").val(data.agente.Nombre);
      $("#textonombreagente").html(data.agente.Nombre);
    }
    //tabs utilidades
    $("#tablautilidadesmarcas").append(data.filasutilidadesmarcas);
    //tabs precios productos
    $("#tablapreciosproductos").append(data.filaspreciosproductos);
    $("#numerofilaspreciosproducto").val(data.numerofilaspreciosproductos);
    mostrarmodalformulario('MODIFICACION');
    $('.page-loader-wrapper').css('display', 'none');
    activarbusquedaproducto();//importante activa la busqueda de productos por su codigo
    setTimeout(function(){$("#nombre").focus();},500);
    //activar busqueda para clave producto
    $('#clavepais').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclavepaisporclave();
        }
    });
    //regresar clave
    $('#clavepais').on('change', function(e) {
        regresarclavepais();
    });
    //activar busqueda
    $('#agente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obteneragentepornumero();
        }
    });
    //regresar clave
    $('#agente').on('change', function(e) {
        regresarnumeroagente();
    });
    //activar busqueda
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
    //activar busqueda
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
    //activar busqueda
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
    //activar busqueda
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
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnext").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnext");          
      switch(code){
        case 13:
          $(".inputnext").eq(index + 1).focus().select(); 
          break;
        case 39:
          $(".inputnext").eq(index + 1).focus().select(); 
          break;
        case 37:
          $(".inputnext").eq(index - 1).focus().select(); 
          break;
      }
    });
  }).fail( function() {
    msj_errorajax();
    $('.page-loader-wrapper').css('display', 'none');
  })
}
//modificar el registro
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
  var checkboxscolumnas = '';
  var optionsselectbusquedas = '';
  var campos = campos_activados.split(",");
  for (var i = 0; i < campos.length; i++) {
    var returncheckboxfalse = '';
    if(campos[i] == 'Numero' || campos[i] == 'Status'){
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