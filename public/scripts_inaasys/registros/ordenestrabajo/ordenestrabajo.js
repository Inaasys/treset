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
  $.get(ordenes_trabajo_obtener_fecha_actual_datetimelocal, function(fechadatetimelocal){
    $("#fecha").val(fechadatetimelocal);
    $("#fechaentregapromesa").val(fechadatetimelocal);
  }) 
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  var serie = $("#serie").val();
  $.get(ordenes_trabajo_obtener_ultimo_folio, {serie:serie}, function(folio){
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
//mostrar formulario en modal y ocultar tabla de seleccion
function asignaciontecnicosmostrarformulario(){
  $("#asignaciontecnicosformulario").show();
  $("#asignaciontecnicoscontenidomodaltablas").hide();
}
//mostrar tabla de seleccion y ocultar formulario en modal
function asignaciontecnicosocultarformulario(){
  $("#asignaciontecnicosformulario").hide();
  $("#asignaciontecnicoscontenidomodaltablas").show();
}
//mostrar modal asignacion tecnicos
function mostrarmodalasignaciontecnicos(){
  $("#modalasignaciontecnicos").modal('show');
  $("#modalasignaciontecnicos").css('overflow', 'auto');
  $("#ModalFormulario").modal('hide');
}
//ocultar modal asignacion tecnicos
function ocultarmodalasignaciontecnicos(){
  $("#modalasignaciontecnicos").modal('hide');
  $("#ModalFormulario").modal('show');
  $("#ModalFormulario").css('overflow', 'auto');
}
function relistar(){
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
    cambiarurlexportarexcel();
}
//listar todos los registros de la tabla
function listar(){
  cambiarurlexportarexcel();
  //Campos ordenados a mostras
  var campos = columnas_ordenadas.split(",");
  var campos_busqueda = campos_busquedas.split(",");
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
            url: ordenes_trabajo_obtener,
            data: function (d) {
                d.periodo = $("#periodo").val();
            }
        },
        "createdRow": function( row, data, dataIndex){
            if( data.Status ==  `ABIERTA`){ $(row).addClass('bg-red');}
            else if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
            else if( data.Status ==  `CERRADA`){ $(row).addClass('bg-light-green');}
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
    //modificacion al dar doble click
    $('#tbllistado tbody').on('dblclick', 'tr', function () {
      var data = tabla.row( this ).data();
      obtenerdatos(data.Orden);
    });
}
//obtener tipos ordenes de compra
function obtenertiposordenestrabajo(){
    $.get(ordenes_trabajo_obtener_tipos_ordenes_trabajo, function(select_tipos_ordenes_trabajo){
      $("#tipoorden").html(select_tipos_ordenes_trabajo);
    })  
}
//obtener tipos de unidades
function obtenertiposunidades(){
  $.get(ordenes_trabajo_obtener_tipos_unidades, function(select_tipos_unidades){
    $("#tipounidad").html(select_tipos_unidades);
  }) 
}
//obtener series documento
function obtenerseriesdocumento(){
  ocultarformulario();
  var seriedefault = 'A';
  var tablaseriesdocumento= '<div class="modal-header '+background_forms_and_modals+'">'+
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
        url: ordenes_trabajo_obtener_series_documento
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
  $.get(ordenes_trabajo_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie}, function(folio){
      $("#folio").val(folio);
      $("#serie").val(Serie);
      $("#serietexto").html("Serie: "+Serie);
      mostrarformulario();
  }) 
}
//obtener registros de clientes
function listarclientesfacturaa(){
  ocultarformulario();
  var tablaclientesfacturaa = '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Factura a</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoclientesfacturaa" class="tbllistadoclientesfacturaa table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Numero</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>R.F.C.</th>'+
                                                        '<th>Municipio</th>'+
                                                        '<th>Agente</th>'+
                                                        '<th>Tipo</th>'+
                                                        '<th>Saldo</th>'+
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
    $("#contenidomodaltablas").html(tablaclientesfacturaa);
    $('#tbllistadoclientesfacturaa').DataTable({
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
            url: ordenes_trabajo_obtener_clientes_facturaa,
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
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoclientesfacturaa').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
} 
function seleccionarclientefacturaa(Numero, Nombre, Plazo, NumeroAgente, Agente){
  var numeroclientefacturaaanterior = $("#numeroclientefacturaaanterior").val();
  var numeroclientefacturaa = Numero;
  if(numeroclientefacturaaanterior != numeroclientefacturaa){
    $("#numeroclientefacturaa").val(Numero);
    $("#numeroclientefacturaaanterior").val(Numero);
    $("#clientefacturaa").val(Nombre);
    $("#textonombreclientefacturaaa").html(Nombre.substring(0, 40));
    $("#plazodias").val(Plazo);
    //datos agente
    $("#numeroagente").val(NumeroAgente);
    $("#numeroagenteanterior").val(NumeroAgente);
    $("#agente").val(Agente);
    $("#textonombreagente").html(Agente.substring(0, 40));
    mostrarformulario();
  }
}
//obtener registros de clientes
function listarclientesdelcliente(){
  ocultarformulario();
  var tablaclientesdelcliente = '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Del cliente</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoclientesdelcliente" class="tbllistadoclientesdelcliente table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Numero</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>R.F.C.</th>'+
                                                        '<th>Municipio</th>'+
                                                        '<th>Agente</th>'+
                                                        '<th>Tipo</th>'+
                                                        '<th>Saldo</th>'+
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
    $("#contenidomodaltablas").html(tablaclientesdelcliente);
    $('#tbllistadoclientesdelcliente').DataTable({
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
            url: ordenes_trabajo_obtener_clientes_delcliente,
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
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoclientesdelcliente').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
} 
function seleccionarclientedelcliente(Numero, Nombre, Plazo){
  var numeroclientedelclienteanterior = $("#numeroclientedelclienteanterior").val();
  var numeroclientedelcliente = Numero;
  if(numeroclientedelclienteanterior != numeroclientedelcliente){
    $("#numeroclientedelcliente").val(Numero);
    $("#numeroclientedelclienteanterior").val(Numero);
    $("#clientedelcliente").val(Nombre);
    $("#textonombreclientedelcliente").html(Nombre.substring(0, 40));
    mostrarformulario();
  }
}
//obtener registros de agentes
function listaragentes(){
  ocultarformulario();
  var tablaagentes = '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Agentes</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive" >'+
                                            '<table id="tbllistadoagentes" class="tbllistadoagentes table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Numero</th>'+
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
    $('#tbllistadoagentes').DataTable({
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
            url: ordenes_trabajo_obtener_agentes,
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
                $('#tbllistadoagentes').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
} 
function seleccionaragente(Numero, Nombre){
  var numeroagenteanterior = $("#numeroagenteanterior").val();
  var numeroagente = Numero;
  if(numeroagenteanterior != numeroagente){
    $("#numeroagente").val(Numero);
    $("#numeroagenteanterior").val(Numero);
    $("#agente").val(Nombre);
    $("#textonombreagente").html(Nombre.substring(0,40));
    mostrarformulario();
  }
}
//obtener técnicos
function listartecnicos(){
  if(parseInt(contadortecnicos) < 5){  
    asignaciontecnicosocultarformulario();
    var tablatecnicos = '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Técnicos</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadotecnicos" class="tbllistadotecnicos table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Numero</th>'+
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
                                '<button type="button" class="btn btn-danger btn-sm" onclick="asignaciontecnicosmostrarformulario();">Regresar</button>'+
                              '</div>';
    $("#asignaciontecnicoscontenidomodaltablas").html(tablatecnicos);
    $('#tbllistadotecnicos').DataTable({
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
          url: ordenes_trabajo_obtener_tecnicos,
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
                $('#tbllistadotecnicos').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
  }else{
    msjsolo4tecnicospermitidos();
  }
} 
//obtener registros de vines
function listarvines(){
  ocultarformulario();
  var tablavines = '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Vines</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadovines" class="tbllistadovines table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Economico</th>'+
                                                        '<th>Vin</th>'+
                                                        '<th>Placas</th>'+
                                                        '<th>Motor</th>'+
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
    $("#contenidomodaltablas").html(tablavines);
    $('#tbllistadovines').DataTable({ 
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
          url: ordenes_trabajo_obtener_vines,
          data: function (d) {
            d.numeroclientefacturaa = $("#numeroclientefacturaa").val();
          }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Economico', name: 'Economico' },
            { data: 'Vin', name: 'Vin' },
            { data: 'Placas', name: 'Placas', orderable: false, searchable: false },
            { data: 'Motor', name: 'Motor', orderable: false, searchable: false },
            { data: 'Marca', name: 'Marca', orderable: false, searchable: false },
            { data: 'Modelo', name: 'Modelo', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadovines').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
} 
function seleccionarvin(Cliente, Economico, Vin, Placas, Motor, Marca, Modelo, Año, Color){
  var vinanterior = $("#vinanterior").val();
  var vin = Vin;
  if(vinanterior != vin){
    $("#vin").val(Vin);
    $("#vinanterior").val(Vin);
    $("#textonombrevin").html(Vin);
    $("#motor").val(Motor);
    $("#marca").val(Marca);
    $("#modelo").val(Modelo);
    $("#ano").val(Año);
    $("#placas").val(Placas);
    $("#economico").val(Economico);
    $("#color").val(Color);
    mostrarformulario();
  }
}
//obtener por numero
function obtenerclientefacturaapornumero(){
  var numeroclientefacturaaanterior = $("#numeroclientefacturaaanterior").val();
  var numeroclientefacturaa = $("#numeroclientefacturaa").val();
  if(numeroclientefacturaaanterior != numeroclientefacturaa){
    if($("#numeroclientefacturaa").parsley().isValid()){
      var numeroclientefacturaa = $("#numeroclientefacturaa").val();
      $.get(ordenes_trabajo_obtener_cliente_facturaa_por_numero, {numeroclientefacturaa:numeroclientefacturaa}, function(data){
          $("#numeroclientefacturaa").val(data.numero);
          $("#numeroclientefacturaaanterior").val(data.numero);
          $("#clientefacturaa").val(data.nombre);
          $("#textonombreclientefacturaaa").html(data.nombre.substring(0, 40));
          $("#plazodias").val(data.plazo);
          //datos agente
          $("#numeroagente").val(data.numeroagente);
          $("#numeroagenteanterior").val(data.numeroagente);
          $("#agente").val(data.nombreagente);
          $("#textonombreagente").html(data.nombreagente.substring(0, 40));
          mostrarformulario();
      }) 
    }
  }
}
//regresar numero
function regresarnumeroclientefacturaa(){
  var numeroclientefacturaaanterior = $("#numeroclientefacturaaanterior").val();
  $("#numeroclientefacturaa").val(numeroclientefacturaaanterior);
}
//obtener por numero
function obtenerclientedelclientepornumero(){
  var numeroclientedelclienteanterior = $("#numeroclientedelclienteanterior").val();
  var numeroclientedelcliente = $("#numeroclientedelcliente").val();
  if(numeroclientedelclienteanterior != numeroclientedelcliente){
    if($("#numeroclientedelcliente").parsley().isValid()){
      var numeroclientedelcliente = $("#numeroclientedelcliente").val();
      $.get(ordenes_trabajo_obtener_cliente_delcliente_por_numero, {numeroclientedelcliente:numeroclientedelcliente}, function(data){
        $("#numeroclientedelcliente").val(data.numero);
        $("#numeroclientedelclienteanterior").val(data.numero);
        $("#clientedelcliente").val(data.nombre);
        $("#textonombreclientedelcliente").html(data.nombre.substring(0, 40));
        mostrarformulario();
      }) 
    }
  }
}
//regresar numero
function regresarnumeroclientedelcliente(){
  var numeroclientedelclienteanterior = $("#numeroclientedelclienteanterior").val();
  $("#numeroclientedelcliente").val(numeroclientedelclienteanterior);
}
//obtener por numero
function obteneragentepornumero(){
  var numeroagenteanterior = $("#numeroagenteanterior").val();
  var numeroagente = $("#numeroagente").val();
  if(numeroagenteanterior != numeroagente){
    if($("#numeroagente").parsley().isValid()){
      var numeroagente = $("#numeroagente").val();
      $.get(ordenes_trabajo_obtener_agente_por_numero, {numeroagente:numeroagente}, function(data){
          $("#numeroagente").val(data.numero);
          $("#numeroagenteanterior").val(data.numero);
          $("#agente").val(data.nombre);
          $("#textonombreagente").html(data.nombre.substring(0,40));
          mostrarformulario();
      }) 
    }
  }
}
//regresar numero
function regresarnumeroagente(){
  var numeroagenteanterior = $("#numeroagenteanterior").val();
  $("#numeroagente").val(numeroagenteanterior);
}
//obtener por numero
function obtenervinpornumero(){
  var vinanterior = $("#vinanterior").val();
  var vin = $("#vin").val();
  if(vinanterior != vin){
    if($("#vin").parsley().isValid()){
      var vin = $("#vin").val();
      var numeroclientefacturaa = $("#numeroclientefacturaa").val();
      $.get(ordenes_trabajo_obtener_vin_por_numero, {vin:vin,numeroclientefacturaa:numeroclientefacturaa}, function(data){
          $("#vin").val(data.vin);
          $("#vinanterior").val(data.vin);
          $("#textonombrevin").html(data.vin);
          $("#motor").val(data.motor);
          $("#marca").val(data.marca);
          $("#modelo").val(data.modelo);
          $("#ano").val(data.año);
          $("#placas").val(data.placas);
          $("#economico").val(data.economico);
          $("#color").val(data.color);
          mostrarformulario();
      }) 
    }
  }
}
//regresar numero
function regresarnumerovin(){
  var vinanterior = $("#vinanterior").val();
  $("#vin").val(vinanterior);
}
//listar todas las cotizaciones
function listarcotizaciones (){
    ocultarformulario();
    var tablacotizaciones = '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Cotizaciones</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadocotizacion" class="tbllistadocotizacion table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Cotización</th>'+
                                                        '<th>Fecha</th>'+
                                                        '<th>Cliente</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>Unidad</th>'+
                                                        '<th>Total</th>'+
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
    $("#contenidomodaltablas").html(tablacotizaciones);
    $('#tbllistadocotizacion').DataTable({
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
            url: ordenes_trabajo_obtener_cotizaciones,
            data: function (d) {
                d.numerocliente = $("#numeroclientefacturaa").val();
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Cotizacion', name: 'Cotizacion' },
            { data: 'Fecha', name: 'Fecha' },
            { data: 'Cliente', name: 'Cliente', orderable: false, searchable: false },
            { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false },
            { data: 'Unidad', name: 'Unidad', orderable: false, searchable: false },
            { data: 'Total', name: 'Total', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                    $('#tbllistadocotizacion').DataTable().search( this.value ).draw();
                }
            });
        },
    });  
} 
//obtener todos los datos de la cotizacion seleccionada
function seleccionarcotizacion(Folio, Cotizacion){
    $('.page-loader-wrapper').css('display', 'block');
    $("#tablaserviciosordentrabajo tbody").html("");
    $.get(ordenes_trabajo_obtener_cotizacion, {Folio:Folio, Cotizacion:Cotizacion}, function(data){
        $("#observaciones").val(data.cotizacion.Obs);
        $("#plazo").val(data.cotizacion.Plazo);
        $("#tablaserviciosordentrabajo tbody").html(data.filasdetallescotizacion);
        $("#importe").val(data.importe);
        $("#descuento").val(data.descuento);
        $("#subtotal").val(data.subtotal);
        $("#iva").val(data.iva);
        $("#total").val(data.total);  
        $("#vin").val(data.cotizacion.Vin);
        $("#vinanterior").val(data.cotizacion.Vin);
        if(data.cotizacion.Vin != null){
          $("#textonombrevin").html(data.cotizacion.Vin);
        }
        $("#motor").val(data.cotizacion.Motor);
        $("#marca").val(data.cotizacion.Marca);
        $("#modelo").val(data.cotizacion.Modelo);
        $("#ano").val(data.cotizacion.Año);
        $("#kilometros").val(data.kilometros);
        $("#placas").val(data.cotizacion.Placas);
        $("#economico").val(data.cotizacion.Economico);
        $("#color").val(data.cotizacion.Color);
        //detalles
        $("#numerofilas").val(data.numerodetallescotizacion);
        $("#numerofilastablaservicios").val(data.numerodetallescotizacion);
        //colocar valores a contadores
        contadorservicios = data.contadorservicios;
        contadorfilas = data.contadorfilas;
        seleccionarselectscotizacion(data);
    })  
}
async function seleccionarselectscotizacion(data){
    await retraso();
    $("#tipounidad").val(data.cotizacion.Unidad).change();
    $("#tiposervicio").val(data.cotizacion.TipoServicio).change();
    //calculartotal();
    mostrarformulario();
    $('.page-loader-wrapper').css('display', 'none');
} 
//listar productos para tab consumos
function listarservicios(){
  ocultarformulario();
  var tablaservicios = '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Servicios</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                          '<div class="row">'+
                            '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                '<table id="tbllistadoservicio" class="tbllistadoservicio table table-bordered table-striped table-hover" style="width:100% !important">'+
                                  '<thead class="'+background_tables+'">'+
                                    '<tr>'+
                                      '<th>Operaciones</th>'+
                                      '<th>Código</th>'+
                                      '<th>Servicio</th>'+
                                      '<th>Cantidad</th>'+
                                      '<th>SubTotal</th>'+
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
  $("#contenidomodaltablas").html(tablaservicios);
  $('#tbllistadoservicio').DataTable({
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
      url: ordenes_trabajo_obtener_servicios,
      data: function (d) {
        d.codigoabuscar = $("#codigoabuscar").val();
        d.tipooperacion = $("#tipooperacion").val();
      }
    },
    columns: [
      { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false  },
      { data: 'Codigo', name: 'Codigo' },
      { data: 'Servicio', name: 'Servicio'  },
      { data: 'Cantidad', name: 'Cantidad', orderable: false, searchable: false  },
      { data: 'Venta', name: 'Venta', orderable: false, searchable: false  }
    ],
    "initComplete": function() {
      var $buscar = $('div.dataTables_filter input');
      $buscar.unbind();
      $buscar.bind('keyup change', function(e) {
        if(e.keyCode == 13 || this.value == "") {
          $('#tbllistadoservicio').DataTable().search( this.value ).draw();
        }
      });
    },
  });
}
function obtenerservicioporcodigo(){
  var codigoabuscar = $("#codigoabuscar").val();
  var tipooperacion = $("#tipooperacion").val();
  $.get(ordenes_trabajo_obtener_servicio_por_codigo,{codigoabuscar:codigoabuscar}, function(data){
    if(parseInt(data.contarservicios) > 0){
      agregarfilaservicio(data.Codigo, data.Servicio, data.Unidad, data.Costo, data.Venta, data.Cantidad, data.ClaveProducto, data.ClaveUnidad, tipooperacion);
    }else{
      msjnoseencontroningunproducto();
    }
  }) 
}
//calcular total detalles orden de trabajo
function calculartotalesfilasordentrabajo(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasservicios").each(function () { 
    if(fila === cuentaFilas){
      // obtener los datos de la fila:
      var cantidadpartida = $(".cantidadpartida", this).val();
      var preciopartida = $('.preciopartida', this).val();
      var importepartida = $('.importepartida', this).val();
      var descuentopesospartida = $('.descuentopesospartida', this).val();
      var subtotalpartida = $('.subtotalpartida', this).val();
      var ivaporcentajepartida = $('.ivaporcentajepartida', this).val();
      var ivapesospartida = $('.ivapesospartida', this).val();
      var totalpesospartida = $('.totalpesospartida', this).val(); 
      //importe de la partida
      importepartida =  new Decimal(cantidadpartida).times(preciopartida);
      $('.importepartida', this).val(number_format(round(importepartida, numerodecimales), numerodecimales, '.', ''));
      //subtotal de la partida
      subtotalpartida =  new Decimal(importepartida).minus(descuentopesospartida);
      $('.subtotalpartida', this).val(number_format(round(subtotalpartida, numerodecimales), numerodecimales, '.', ''));
      //utilidad de la partida
      $('.utilidadpartida', this).val(number_format(round(subtotalpartida, numerodecimales), numerodecimales, '.', ''));
      //iva en pesos de la partida
      var multiplicacionivapesospartida = new Decimal(subtotalpartida).times(ivaporcentajepartida);
      ivapesospartida = new Decimal(multiplicacionivapesospartida/100);
      $('.ivapesospartida', this).val(number_format(round(ivapesospartida, numerodecimales), numerodecimales, '.', ''));
      //total en pesos de la partida
      totalpesospartida = new Decimal(subtotalpartida).plus(ivapesospartida);
      $('.totalpesospartida', this).val(number_format(round(totalpesospartida, numerodecimales), numerodecimales, '.', ''));
      calculartotalordentrabajo();
    }  
    cuentaFilas++;
  });
}
//dejar en 0 los descuentos cuando el precio de la partida se cambie
function cambiodecantidadopreciopartida(fila,tipo){
  var cuentaFilas = 0;
  $("tr.filasservicios").each(function () {
    if(fila === cuentaFilas){  
      $('.descuentopesospartida', this).val('0.'+numerocerosconfigurados); 
      $('.descuentoporcentajepartida',this).val('0.'+numerocerosconfigurados);
      calculartotalesfilasordentrabajo(fila);
    }  
    cuentaFilas++;
  });  
}
//calcular el porcentaje de descuento cuando el descuento en pesos se modifique
function calculardescuentoporcentajepartida(fila){
  var cuentaFilas = 0;
  $("tr.filasservicios").each(function () {
    if(fila === cuentaFilas){  
      //descuento en porcentaje de la partida
      var importepartida = $('.importepartida', this).val(); 
      var descuentopesospartida = $('.descuentopesospartida', this).val(); 
      var multiplicaciondescuentoporcentajepartida  =  new Decimal(descuentopesospartida).times(100);
      if(multiplicaciondescuentoporcentajepartida.d[0] > parseInt(0)){
        var descuentoporcentajepartida = new Decimal(multiplicaciondescuentoporcentajepartida/importepartida);
        $('.descuentoporcentajepartida', this).val(number_format(round(descuentoporcentajepartida, numerodecimales), numerodecimales, '.', ''));
        calculartotalesfilasordentrabajo(fila);
      }
    }  
    cuentaFilas++;
  });    
}
//calcular el descuento en pesos cuando hay cambios en el porcentaje de descuento
function calculardescuentopesospartida(fila){
  var cuentaFilas = 0;
  $("tr.filasservicios").each(function () {
    if(fila === cuentaFilas){   
      //descuento en pesos de la partida
      var importepartida = $('.importepartida', this).val();
      var descuentoporcentajepartida = $('.descuentoporcentajepartida', this).val();
      var multiplicaciondescuentopesospartida  =  new Decimal(importepartida).times(descuentoporcentajepartida);
      if(multiplicaciondescuentopesospartida.d[0] > parseInt(0)){
        var descuentopesospartida = new Decimal(multiplicaciondescuentopesospartida/100);
        $('.descuentopesospartida', this).val(number_format(round(descuentopesospartida, numerodecimales), numerodecimales, '.', ''));
        calculartotalesfilasordentrabajo(fila);
      }
    }  
    cuentaFilas++;
  }); 
} 
//calcular totales de orden de compra
function calculartotalordentrabajo(){
  var importe = 0;
  var descuento = 0;
  var subtotal= 0;
  var iva = 0;
  var total = 0;
  $("tr.filasservicios").each(function(){
    importe= new Decimal(importe).plus($(".importepartida", this).val());
    descuento = new Decimal(descuento).plus($(".descuentopesospartida", this).val());
    subtotal= new Decimal(subtotal).plus($(".subtotalpartida", this).val());
    iva = new Decimal(iva).plus($(".ivapesospartida", this).val());
    total = new Decimal(total).plus($(".totalpesospartida", this).val());
  }); 
  $("#importe").val(number_format(round(importe, numerodecimales), numerodecimales, '.', ''));
  $("#descuento").val(number_format(round(descuento, numerodecimales), numerodecimales, '.', ''));
  $("#subtotal").val(number_format(round(subtotal, numerodecimales), numerodecimales, '.', ''));
  $("#iva").val(number_format(round(iva, numerodecimales), numerodecimales, '.', ''));
  $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
}
//agregar una fila a la tabla
var contadorservicios=0;
var contadorfilas = 0;
var item = 1;
function agregarfilaservicio(Codigo, Servicio, Unidad, Costo, Venta, Cantidad, ClaveProducto, ClaveUnidad, tipooperacion){
    var impuesto = "16."+numerocerosconfigurados;
    var importepartida = new Decimal(Cantidad).times(Venta);
    var multiplicacioncostoimpuesto =  new Decimal(importepartida).times(impuesto);      
    var ivapesos = new Decimal(multiplicacioncostoimpuesto/100);
    var total = new Decimal(importepartida).plus(ivapesos);
    var tipo = "alta";
    var hoy = $("#fecha").val();
    var fila=   '<tr class="filasservicios" id="filaservicio'+contadorservicios+'">'+
                        '<td class="tdmod"><div class="divorinputmodmd">'+
                          '<div class="btn bg-red btn-xs" data-toggle="tooltip" title="Eliminar Fila" onclick="eliminarfila('+contadorservicios+')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly> '+
                          '<div class="btn bg-blue btn-xs" data-toggle="tooltip" title="Asignar Técnicos" onclick="asignaciontecnicos('+contadorservicios+')">Asignar técnicos</div>'+
                        '</div></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control tipofila" name="tipofila[]" value="agregado" readonly><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]">'+Codigo+'</td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'+Servicio+'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control unidadpartidad" name="unidadpartidad[]" value="'+Unidad+'" readonly data-parsley-length="[1, 5]">'+Unidad+'</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'+Cantidad+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('+contadorfilas+');cambiodecantidadopreciopartida('+contadorfilas+',\''+tipo +'\');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'+Venta+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('+contadorfilas+');cambiodecantidadopreciopartida('+contadorfilas+',\''+tipo +'\');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'+number_format(round(importepartida, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'+number_format(round(importepartida, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'+number_format(round(impuesto, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo('+contadorfilas+');" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'+number_format(round(ivapesos, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'+number_format(round(total, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'+number_format(round(importepartida, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs departamentopartida" name="departamentopartida[]" value="SERVICIO" readonly data-parsley-length="[1, 20]"></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs cargopartida" name="cargopartida[]" value="SERVICIO" readonly data-parsley-length="[1, 20]"></td>'+
                        '<td class="tdmod"><input type="datetime-local" class="form-control divorinputmodxl fechapartida" name="fechapartida[]" value="'+hoy+'" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs traspasopartida" name="traspasopartida[]" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs comprapartida" name="comprapartida[]" readonly data-parsley-length="[1, 20]"></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs usuariopartida" name="usuariopartida[]" value="'+usuario+'" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxl anotacionespartida" name="anotacionespartida[]" data-parsley-length="[1, 255]" autocomplete="off"></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs statuspartida" name="statuspartida[]" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs itempartida" name="itempartida[]" value="'+item+'" readonly></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida1" name="numerotecnicopartida1[]" value="0" readonly><input type="text" class="form-control divorinputmodl tecnicopartida1" name="tecnicopartida1[]" value="0" readonly></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida2" name="numerotecnicopartida2[]" value="0" readonly><input type="text" class="form-control divorinputmodl tecnicopartida2" name="tecnicopartida2[]" value="0" readonly></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida3" name="numerotecnicopartida3[]" value="0" readonly><input type="text" class="form-control divorinputmodl tecnicopartida3" name="tecnicopartida3[]" value="0" readonly></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodl numerotecnicopartida4" name="numerotecnicopartida4[]" value="0" readonly><input type="text" class="form-control divorinputmodl tecnicopartida4" name="tecnicopartida4[]" value="0" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm horaspartida1" name="horaspartida1[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm horaspartida2" name="horaspartida2[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm horaspartida3" name="horaspartida3[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm horaspartida4" name="horaspartida4[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs promocionpartida" name="promocionpartida[]" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs partidapartida" name="partidapartida[]" value="'+item+'" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs almacenpartida" name="almacenpartida[]" value="0" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs cotizacionpartida" name="cotizacionpartida[]" readonly data-parsley-length="[1, 20]"></td>'+
                '</tr>';
    contadorservicios++;
    contadorfilas++;
    item++;
    $("#tablaserviciosordentrabajo").append(fila);
    $("#numerofilastablaservicios").val(contadorservicios);
    $("#numerofilas").val(contadorservicios);
    mostrarformulario();
    calculartotalordentrabajo(); 
    $("#codigoabuscar").val("");
}
//asignacion de tecnicos para el servicio seleccionado
function asignaciontecnicos(fila){
  contadortecnicos = 1;
  var filastecnicosasignados = "";
  var arraytecnicosasignados = new Array();
  var Codigo = $("#filaservicio"+fila+" .codigopartida").val();
  var Servicio = $("#filaservicio"+fila+" .descripcionpartida").val();
  var Cantidad = $("#filaservicio"+fila+" .cantidadpartida").val();
  var Venta = $("#filaservicio"+fila+" .preciopartida").val();
  var importepartida = $("#filaservicio"+fila+" .importepartida").val();
  var impuesto = $("#filaservicio"+fila+" .ivaporcentajepartida").val();
  var total = $("#filaservicio"+fila+" .totalpesospartida").val();
  var anotaciones = $("#filaservicio"+fila+" .anotacionespartida").val();
  //construis array con tecnicos asignados al servicio
  if($("#filaservicio"+fila+" .numerotecnicopartida1").val() > 0){
    var tecnicoasignado = new Array();
    tecnicoasignado["numerotecnico"] = $("#filaservicio"+fila+" .numerotecnicopartida1").val();
    tecnicoasignado["tecnico"] = $("#filaservicio"+fila+" .tecnicopartida1").val();
    tecnicoasignado["horas"] = $("#filaservicio"+fila+" .horaspartida1").val();
    arraytecnicosasignados.push(tecnicoasignado);
  }
  if($("#filaservicio"+fila+" .numerotecnicopartida2").val() > 0){
    var tecnicoasignado = new Array();
    tecnicoasignado["numerotecnico"] = $("#filaservicio"+fila+" .numerotecnicopartida2").val();
    tecnicoasignado["tecnico"] = $("#filaservicio"+fila+" .tecnicopartida2").val();
    tecnicoasignado["horas"] = $("#filaservicio"+fila+" .horaspartida2").val();
    arraytecnicosasignados.push(tecnicoasignado);
  }
  if($("#filaservicio"+fila+" .numerotecnicopartida3").val() > 0){
    var tecnicoasignado = new Array();
    tecnicoasignado["numerotecnico"] = $("#filaservicio"+fila+" .numerotecnicopartida3").val();
    tecnicoasignado["tecnico"] = $("#filaservicio"+fila+" .tecnicopartida3").val();
    tecnicoasignado["horas"] = $("#filaservicio"+fila+" .horaspartida3").val();
    arraytecnicosasignados.push(tecnicoasignado);
  }
  if($("#filaservicio"+fila+" .numerotecnicopartida4").val() > 0){
    var tecnicoasignado = new Array();
    tecnicoasignado["numerotecnico"] = $("#filaservicio"+fila+" .numerotecnicopartida4").val();
    tecnicoasignado["tecnico"] = $("#filaservicio"+fila+" .tecnicopartida4").val();
    tecnicoasignado["horas"] = $("#filaservicio"+fila+" .horaspartida4").val();
    arraytecnicosasignados.push(tecnicoasignado);
  }
  //iterar array de tecnicos asignados para agregar filas a la tabla de tecnicos asignados
  $.each(arraytecnicosasignados,function(key, tecnico) {
    filastecnicosasignados=filastecnicosasignados+'<tr class="filastecnicos" id="filatecnico'+contadortecnicos+'">'+
                                                    '<td class="tdmod"><div class="divorinputmodsm">'+
                                                      '<div class="btn bg-red btn-xs btneliminarfilatecnico" data-toggle="tooltip" title="Eliminar Fila" onclick="eliminarfilatecnico('+contadortecnicos+')">X</div>'+
                                                    '</td>'+
                                                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxl numerotecnicopartida" name="numerotecnicopartida[]" value="'+contadortecnicos+'" readonly><div class="divnumerotecnicopartida">'+contadortecnicos+'</div></td>'+
                                                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxl numerotecnicoencatalogopartida" name="numerotecnicoencatalogopartida[]" value="'+tecnico["numerotecnico"]+'" required readonly><input type="hidden" class="form-control divorinputmodxl tecnicopartida" name="tecnicopartida[]" value="'+tecnico["tecnico"]+'" required readonly>'+tecnico["tecnico"]+'</td>'+
                                                    '<td class="tdmod">'+
                                                      '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodxl horastecnicopartida" name="horastecnicopartida[]" value="'+tecnico["horas"]+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);sumarhorastrabajadastecnicos();">'+
                                                    '</td>'+
                                                  '</tr>';
    contadortecnicos++;
  });
  var formasignaciontecnicos =  '<div class="modal-header '+background_forms_and_modals+'">'+
                                  '<h5 class="modal-title" id="exampleModalLabel">Asignación tiempo(s) tecnico(s)</h5>'+
                                '</div>'+
                                '<form id="formasignaciontecnicos" action="#">'+
                                  '<div class="modal-body">'+
                                      '<div class="row">'+
                                          '<div class="col-md-6">'+
                                              '<label>Código Servicio</label>'+
                                              '<input type="text" class="form-control" name="asignaciontecnicoscodigo" id="asignaciontecnicoscodigo" readonly value="'+Codigo+'" onkeyup="tipoLetra(this);">'+
                                          '</div>'+ 
                                          '<div class="col-md-6">'+
                                              '<label>Servicio</label>'+
                                              '<input type="text" class="form-control" name="asignaciontecnicosservicio" id="asignaciontecnicosservicio" value="'+Servicio+'" readonly onkeyup="tipoLetra(this);">'+
                                          '</div>'+
                                          '<div class="col-md-12">'+
                                              '<label>Precios / Tiempos Servicio</label>'+
                                              '<div class=" table-responsive">'+
                                                '<table class="table table-bordered">'+
                                                    '<thead class="customercolortheadth">'+
                                                        '<tr>'+
                                                            '<th>Cantidad (HRS Totales)</th>'+
                                                            '<th>Precio (Por Hora)</th>'+
                                                            '<th>Sub Total</th>'+
                                                            '<th>Iva (16%)</th>'+
                                                            '<th>Total</th>'+
                                                        '</tr>'+
                                                    '</thead>'+
                                                    '<tbody>'+
                                                        '<tr>'+
                                                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm" name="asignaciontecnicoscantidad" id="asignaciontecnicoscantidad" value="'+Cantidad+'" readonly></td>'+
                                                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm" name="asignaciontecnicosprecio" id="asignaciontecnicosprecio" value="'+Venta+'" readonly></td>'+
                                                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm" name="asignaciontecnicossubtotal" id="asignaciontecnicossubtotal" value="'+importepartida+'" readonly></td>'+
                                                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm" name="asignaciontecnicosiva" id="asignaciontecnicosiva" value="'+impuesto+'" readonly></td>'+
                                                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm" name="asignaciontecnicostotal" id="asignaciontecnicostotal" value="'+total+'" readonly></td>'+
                                                        '</tr>'+
                                                    '</tbody>'+
                                                '</table>'+
                                              '</div>'+
                                          '</div>'+
                                          '<div class="col-md-12">'+
                                              '<label>Anotaciones</label>'+
                                              '<textarea class="form-control" name="asignaciontecnicosanotaciones" id="asignaciontecnicosanotaciones" placeholder="Escribe las anotaciones" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rowspan="1">'+anotaciones+'</textarea>'+
                                          '</div>'+
                                          '<div class="col-md-12">'+
                                            '<label><br>Técnicos</label>&nbsp;&nbsp;'+
                                            '<div class="btn bg-blue btn-xs waves-effect" onclick="listartecnicos()">agregar técnico</div>'+
                                            '<div class=" table-responsive">'+
                                              '<table id="tablatecnicosasignaciontiempos" class="table table-bordered tablatecnicosasignaciontiempos">'+
                                                    '<thead class="customercolortheadth">'+
                                                        '<tr>'+
                                                            '<th>Operaciones</th>'+
                                                            '<th>#</th>'+
                                                            '<th>Técnico</th>'+
                                                            '<th>Horas</th>'+
                                                        '</tr>'+
                                                    '</thead>'+
                                                    '<tbody>'+
                                                      filastecnicosasignados+
                                                    '</tbody>'+
                                                    '<tfoot>'+
                                                      '<tr>'+
                                                          '<td colspan="2">'+
                                                          '<td style="padding:0px !important;" class="text-right font-bold">Total horas facturación:</td>'+
                                                          '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodxl" name="asignaciontecnicostotalhorasfacturacion" id="asignaciontecnicostotalhorasfacturacion" value="'+Cantidad+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'+
                                                      '</tr>'+
                                                      '<tr>'+
                                                          '<td colspan="2">'+
                                                          '<td style="padding:0px !important;" class="text-right font-bold">Total horas de técnicos:</td>'+
                                                          '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodxl" name="asignaciontecnicostotalhorastecnicos" id="asignaciontecnicostotalhorastecnicos" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)" readonly></td>'+
                                                      '</tr>'+
                                                      '<tr>'+
                                                          '<td colspan="2">'+
                                                          '<td style="padding:0px !important;" class="text-right font-bold">Asignar tiempos iguales a cada técnico:</td>'+
                                                          '<td class="tdmod text-center">'+
                                                            '<input type="checkbox" name="asignaciontecnicosdividirtiemposiguales" id="idasignaciontecnicosdividirtiemposiguales" class="asignaciontecnicosdividirtiemposiguales filled-in" value="0" onchange="dividirtiemposiguales()">'+
                                                            '<label for="idasignaciontecnicosdividirtiemposiguales" ></label>'+
                                                          '</td>'+                                                 
                                                      '</tr>'+
                                                    '</tfoot>'+
                                              '</table>'+
                                            '</div>'+
                                          '</div>'+
                                      '</div>'+
                                  '</div>'+
                                  '<div class="modal-footer">'+
                                      '<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" onclick="ocultarmodalasignaciontecnicos()">Salir</button>'+
                                      '<button type="button" class="btn btn-primary btn-sm" id="btnasignaciontecnicos" onclick="asignartecnicos('+fila+')">Asignar Técnico(s)</button>'+
                                  '</div>'+
                                '</form>';
  $("#asignaciontecnicosformulario").html(formasignaciontecnicos);
  mostrarmodalasignaciontecnicos();
  sumarhorastrabajadastecnicos();
}
//función que evalua si el dato que se quiere agregar ya existe en la tabla
function evaluartecnicoexistente(Numero){
  var sumaiguales=0;
  var sumadiferentes=0;
  var sumatotal=0;
  $("tr.filastecnicos").each(function () {
      var numerotecnicoencatalogopartida = $('.numerotecnicoencatalogopartida', this).val();
      if(Numero == numerotecnicoencatalogopartida){
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
//agregar una fila a la tabla
var contadortecnicos= 1;
function agregarfilatecnico(Numero, Nombre){
  var result = evaluartecnicoexistente(Numero);
  if(result == false){
    var fila=   '<tr class="filastecnicos" id="filatecnico'+contadortecnicos+'">'+
                  '<td class="tdmod"><div class="divorinputmodsm">'+
                    '<div class="btn bg-red btn-xs btneliminarfilatecnico" data-toggle="tooltip" title="Eliminar Fila" onclick="eliminarfilatecnico('+contadortecnicos+')">X</div>'+
                  '</td>'+
                  '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxl numerotecnicopartida" name="numerotecnicopartida[]" value="'+contadortecnicos+'" readonly><div class="divnumerotecnicopartida">'+contadortecnicos+'</div></td>'+
                  '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxl numerotecnicoencatalogopartida" name="numerotecnicoencatalogopartida[]" value="'+Numero+'" required readonly><input type="hidden" class="form-control divorinputmodxl tecnicopartida" name="tecnicopartida[]" value="'+Nombre+'" required readonly>'+Nombre+'</td>'+
                  '<td class="tdmod">'+
                    '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodxl horastecnicopartida" name="horastecnicopartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);sumarhorastrabajadastecnicos();">'+
                  '</td>'+
                '</tr>';
    contadortecnicos++;
    $("#tablatecnicosasignaciontiempos tbody").append(fila); 
    asignaciontecnicosmostrarformulario();
  }else{
    msj_errortecnicoyaagregado();
  }
}
//asignar mismo tiempo a cada tecnico
function dividirtiemposiguales(){
  if( $('#idasignaciontecnicosdividirtiemposiguales').prop('checked') ) {
    var asignaciontecnicostotalhorasfacturacion = $("#asignaciontecnicostotalhorasfacturacion").val();
    var horastrabajadasportecnico= new Decimal(asignaciontecnicostotalhorasfacturacion).dividedBy(parseInt(contadortecnicos)-parseInt(1));
    $("tr.filastecnicos").each(function () {
      $('.horastecnicopartida', this).val(number_format(round(horastrabajadasportecnico, numerodecimales), numerodecimales, '.', '')).attr('readonly', 'readonly');
    });
  }else{
    $("tr.filastecnicos").each(function () {
      $('.horastecnicopartida', this).val(number_format(round(horastrabajadasportecnico, numerodecimales), numerodecimales, '.', '')).removeAttr('readonly');
    });
  }
  sumarhorastrabajadastecnicos();
}
//sumar las horas trabajadas por los técnicos
function sumarhorastrabajadastecnicos(){
  var sumahorastrabajadastecnicos = 0;
  $("tr.filastecnicos").each(function () {
    sumahorastrabajadastecnicos= new Decimal(sumahorastrabajadastecnicos).plus($(".horastecnicopartida", this).val());
  }); 
  $("#asignaciontecnicostotalhorastecnicos").val(number_format(round(sumahorastrabajadastecnicos, numerodecimales), numerodecimales, '.', ''));
}
//eliminar fila de la tabla de tecnicos
function eliminarfilatecnico(numerofila){
  var confirmacion = confirm("Esta seguro de eliminar el técnico?"); 
  if (confirmacion == true) { 
    $("#filatecnico"+numerofila).remove();
    contadortecnicos--; //importante restar la fila eliminada para calculos y funciones
    renumerarfilastecnicos();//importante para todos los calculo en el modulo de orden de compra 
    $("#idasignaciontecnicosdividirtiemposiguales").change();//recalculartiempos
    sumarhorastrabajadastecnicos();//calcular suma total de horas trabajadas  por tecnicos
  } 
}
//renumerar filas de la tabla de tecnicos
function renumerarfilastecnicos(){
  var lista;
  //renumerar las filas tecnicos
  lista = document.getElementsByClassName("filastecnicos");
  for (var i = 0; i < lista.length; i++) {
    var numtecnico = parseInt(i)+parseInt(1);
    lista[i].setAttribute("id", "filatecnico"+numtecnico);
  }
  //renumerar btn eliminar fila
  lista = document.getElementsByClassName("btneliminarfilatecnico");
  for (var i = 0; i < lista.length; i++) {
    var numtecnico = parseInt(i)+parseInt(1);
    lista[i].setAttribute("onclick", "eliminarfilatecnico("+numtecnico+')');
  }
  //renumerar numero tecnico
  lista = document.getElementsByClassName("numerotecnicopartida");
  for (var i = 0; i < lista.length; i++) {
    var numtecnico = parseInt(i)+parseInt(1);
    lista[i].setAttribute("value", numtecnico);
  }
  //renumerar div numero tecnico
  lista = document.getElementsByClassName("divnumerotecnicopartida");
  for (var i = 0; i < lista.length; i++) {
    var numtecnico = parseInt(i)+parseInt(1);
    lista[i].innerHTML = numtecnico;
  }
}  
//asignar tecnicos al servicio seleccionado
function asignartecnicos(fila){
  var asignaciontecnicostotalhorasfacturacion = $("#asignaciontecnicostotalhorasfacturacion").val();
  var asignaciontecnicostotalhorastecnicos = $("#asignaciontecnicostotalhorastecnicos").val();
  if(parseFloat(asignaciontecnicostotalhorasfacturacion) != parseFloat(asignaciontecnicostotalhorastecnicos)){
    msjtotalhorasnocorresponden();
  }else{
    var form = $("#formasignaciontecnicos");
    if (form.parsley().isValid()){
      //borrar tecnicos primero
      $("#filaservicio"+fila+" .numerotecnicopartida1").val("0");
      $("#filaservicio"+fila+" .tecnicopartida1").val("0");
      $("#filaservicio"+fila+" .horaspartida1").val("0").change();
      $("#filaservicio"+fila+" .numerotecnicopartida2").val("0");
      $("#filaservicio"+fila+" .tecnicopartida2").val("0");
      $("#filaservicio"+fila+" .horaspartida2").val("0").change();
      $("#filaservicio"+fila+" .numerotecnicopartida3").val("0");
      $("#filaservicio"+fila+" .tecnicopartida3").val("0");
      $("#filaservicio"+fila+" .horaspartida3").val("0").change();
      $("#filaservicio"+fila+" .numerotecnicopartida4").val("0");
      $("#filaservicio"+fila+" .tecnicopartida4").val("0");
      $("#filaservicio"+fila+" .horaspartida4").val("0").change();
      var asignaciontecnicosanotaciones = $("#asignaciontecnicosanotaciones").val();
      $("#filaservicio"+fila+" .anotacionespartida").val(asignaciontecnicosanotaciones);
      $("tr.filastecnicos").each(function () {
        var numerotecnicopartida = $(".numerotecnicopartida", this).val();
        $("#filaservicio"+fila+" .numerotecnicopartida"+numerotecnicopartida).val($(".numerotecnicoencatalogopartida",this).val());
        $("#filaservicio"+fila+" .tecnicopartida"+numerotecnicopartida).val($(".tecnicopartida",this).val());
        $("#filaservicio"+fila+" .horaspartida"+numerotecnicopartida).val($(".horastecnicopartida",this).val());
      });
      ocultarmodalasignaciontecnicos();
    }else{
      form.parsley().validate();
    }
  }
}
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Orden de Trabajo');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs ='<div class="col-md-12">'+
              '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                '<li role="presentation" class="active">'+
                  '<a href="#datosgeneralesordentrabajotab" data-toggle="tab">Datos Generales</a>'+
                '</li>'+
                '<li role="presentation">'+
                  '<a href="#datostab" data-toggle="tab">Datos</a>'+
                '</li>'+
                '<li role="presentation">'+
                  '<a href="#estadotab" data-toggle="tab">Estado</a>'+
                '</li>'+
              '</ul>'+
              '<div class="tab-content">'+
                '<div role="tabpanel" class="tab-pane fade in active" id="datosgeneralesordentrabajotab">'+
                  '<div class="row">'+
                    '<div class="col-md-2">'+
                      '<label>Orden <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                      '<input type="text" class="form-control" name="folio" id="folio" required onkeyup="tipoLetra(this);">'+
                      '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                      '<input type="hidden" class="form-control" name="numerofilastablaservicios" id="numerofilastablaservicios" value="0" required readonly>'+
                      '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" value="0" readonly>'+
                      '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Tipo de Orden</label>'+
                      '<select name="tipoorden" id="tipoorden" class="form-control select2" style="width:100% !important;" required>'+
                      '</select>'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Tipo de Unidad</label>'+
                      '<select name="tipounidad" id="tipounidad" class="form-control select2" style="width:100% !important;" required>'+
                      '</select>'+
                    '</div>'+
                    '<div class="col-md-3">'+
                      '<label>Fecha</label>'+
                      '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();" onkeyup="tipoLetra(this);">'+
                      '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                    '</div>'+
                    '<div class="col-md-3">'+
                      '<label>Entrega Promesa</label>'+
                      '<input type="datetime-local" class="form-control" name="fechaentregapromesa" id="fechaentregapromesa"  required onchange="validasolomesactual();" onkeyup="tipoLetra(this);">'+
                    '</div>'+
                  '</div>'+
                  '<div class="row">'+
                    '<div class="col-md-3">'+
                      '<label>Factura a <span class="label label-danger" id="textonombreclientefacturaaa"></span></label>'+
                      '<table>'+
                        '<tr>'+
                          '<td>'+
                            '<div class="btn bg-blue waves-effect" onclick="listarclientesfacturaa()">Seleccionar</div>'+
                          '</td>'+
                          '<td>'+
                            '<div class="form-line">'+
                              '<input type="text" class="form-control" name="numeroclientefacturaa" id="numeroclientefacturaa" required data-parsley-type="integer" autocomplete="off">'+
                              '<input type="hidden" class="form-control" name="numeroclientefacturaaanterior" id="numeroclientefacturaaanterior" required data-parsley-type="integer">'+
                              '<input type="hidden" class="form-control" name="clientefacturaa" id="clientefacturaa" required readonly>'+
                            '</div>'+
                          '</td>'+
                        '</tr>'+  
                      '</table>'+
                    '</div>'+
                    '<div class="col-md-3">'+
                      '<label>Del cliente <span class="label label-danger" id="textonombreclientedelcliente"></span></label>'+
                      '<table>'+
                        '<tr>'+
                          '<td>'+
                            '<div class="btn bg-blue waves-effect" onclick="listarclientesdelcliente()">Seleccionar</div>'+
                          '</td>'+
                          '<td>'+    
                            '<div class="form-line">'+
                              '<input type="text" class="form-control" name="numeroclientedelcliente" id="numeroclientedelcliente" required data-parsley-type="integer" autocomplete="off">'+
                              '<input type="hidden" class="form-control" name="numeroclientedelclienteanterior" id="numeroclientedelclienteanterior" required data-parsley-type="integer">'+
                              '<input type="hidden" class="form-control" name="clientedelcliente" id="clientedelcliente" required readonly>'+
                            '</div>'+
                          '</td>'+    
                        '</tr> '+   
                      '</table>'+
                    '</div>'+
                    '<div class="col-md-3">'+
                      '<label>Agente <span class="label label-danger" id="textonombreagente"></span></label>'+
                      '<table class="col-md-12">'+
                        '<tr>'+
                          '<td>'+
                            '<div class="btn bg-blue waves-effect" onclick="listaragentes()">Seleccionar</div>'+
                          '</td>'+
                          '<td>'+   
                            '<div class="form-line">'+
                              '<input type="text" class="form-control" name="numeroagente" id="numeroagente" required data-parsley-type="integer" autocomplete="off">'+
                              '<input type="hidden" class="form-control" name="numeroagenteanterior" id="numeroagenteanterior" required data-parsley-type="integer">'+
                              '<input type="hidden" class="form-control" name="agente" id="agente" required readonly>'+
                            '</div>'+
                          '</td>'+   
                        '</tr>'+    
                      '</table>'+
                    '</div>'+
                    '<div class="col-md-3" id="divcaso">'+
                      '<label>Caso</label>'+
                      '<input type="text" class="form-control" name="caso" id="caso"   onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]" autocomplete="off">'+
                    '</div>'+
                  '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-4" id="divbuscarcodigoservicio">'+
                        '<label>Escribe el código a buscar y presiona la tecla ENTER (Carga Mano de Obra)</label>'+
                        '<table class="col-md-12">'+
                          '<tr>'+
                            '<td>'+
                              '<div class="btn bg-blue waves-effect" id="btnobtenerservicios" onclick="listarservicios()">Ver Servicios</div>'+
                            '</td>'+
                            '<td>'+ 
                              '<div class="form-line">'+
                                '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del servicio y da enter" autocomplete="off">'+
                              '</div>'+
                            '</td>'+
                          '</tr>'+    
                        '</table>'+
                      '</div>'+
                      '<div class="col-md-2" id="divlistarcotizaciones">'+
                        '<label>Cotizaciones</label>'+
                        '<div class="btn btn-block bg-blue waves-effect" id="btnlistarcotizaciones" onclick="listarcotizaciones()">Ver Cotizaciones</div>'+
                      '</div>'+ 
                    '</div>'+
                '</div>'+ 
                '<div role="tabpanel" class="tab-pane fade" id="datostab">'+
                  '<div class="row">'+
                    '<div class="col-md-2">'+
                      '<label>Tipo Servicio</label>'+
                      '<select name="tiposervicio" id="tiposervicio" class="form-control select2" style="width:100% !important;" required>'+
                        '<option selected disabled hidden>Selecciona</option>'+
                        '<option value="NORMAL">NORMAL</option>'+
                        '<option value="CORRECTIVO" selected>CORRECTIVO</option>'+
                        '<option value="PREVENTIVO">PREVENTIVO</option>'+
                      '</select>'+
                    '</div>'+
                    '<div class="col-md-3">'+
                      '<label>Vin / Serie <span class="label label-danger" id="textonombrevin"></span></label>'+
                      '<table class="col-md-12">'+
                        '<tr>'+
                          '<td>'+
                            '<div class="btn bg-blue waves-effect" onclick="listarvines()">Seleccionar</div>'+
                          '</td>'+
                          '<td>'+    
                            '<div class="form-line">'+
                              '<input type="text" class="form-control" name="vin" id="vin" required data-parsley-length="[1, 30]" autocomplete="off">'+
                              '<input type="hidden" class="form-control" name="vinanterior" id="vinanterior" required data-parsley-length="[1, 30]">'+
                            '</div>'+
                          '</td>'+    
                        '</tr>'+    
                      '</table>'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Motor / Serie</label>'+
                      '<input type="text" class="form-control" name="motor" id="motor"  required  onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]" autocomplete="off">'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Marca</label>'+
                      '<input type="text" class="form-control" name="marca" id="marca"  required  onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]" autocomplete="off">'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Modelo</label>'+
                      '<input type="text" class="form-control" name="modelo" id="modelo"  required  onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]" autocomplete="off">'+
                    '</div>'+
                    '<div class="col-md-1">'+
                      '<label>Año</label>'+
                      '<input type="text" class="form-control" name="ano" id="ano"  data-parsley-max="'+parseInt(periodohoy)+'" data-parsley-type="digits" data-parsley-length="[4,4]" required  onkeyup="tipoLetra(this);" autocomplete="off">'+
                    '</div>'+
                  '</div>'+
                  '<div class="row">'+
                    '<div class="col-md-2">'+
                      '<label>Kilómetros</label>'+
                      '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="kilometros" id="kilometros" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)"  required >'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Placas</label>'+
                      '<input type="text" class="form-control" name="placas" id="placas"  required data-parsley-length="[1, 10]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label># Económico</label>'+
                      '<input type="text" class="form-control" name="economico" id="economico"  required  data-parsley-length="[1, 30]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Color</label>'+
                      '<input type="text" class="form-control" name="color" id="color"  required data-parsley-length="[3, 30]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Km Próx Servicio</label>'+
                      '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="kmproxservicio" id="kmproxservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)"  >'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Fecha Recordatorio Cliente</label>'+
                      '<input type="date" class="form-control" name="fecharecordatoriocliente" id="fecharecordatoriocliente"   onkeyup="tipoLetra(this);">'+
                    '</div>'+
                  '</div>'+
                '</div>'+
                '<div role="tabpanel" class="tab-pane fade" id="estadotab">'+
                  '<div class="row">'+
                    '<div class="col-md-2">'+
                      '<label>Reclamo</label>'+
                      '<input type="text" class="form-control" name="reclamo" id="reclamo"  data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Orden Cliente</label>'+
                      '<input type="text" class="form-control" name="ordencliente" id="ordencliente"   onkeyup="tipoLetra(this);" autocomplete="off" required>'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Campaña No</label>'+
                      '<input type="text" class="form-control" name="campana" id="campana" data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                    '</div>'+
                    '<div class="col-md-2" >'+
                      '<label>Promoción</label>'+
                      '<input type="text" class="form-control" name="promocion" id="promocion"   onkeyup="tipoLetra(this);" autocomplete="off">'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Bahia</label>'+
                      '<input type="text" class="form-control" name="bahia" id="bahia"  required  onkeyup="tipoLetra(this);" data-parsley-type="number"	autocomplete="off">'+
                    '</div>'+
                    '<div class="col-md-2" >'+
                      '<label>Horas Reales</label>'+
                      '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="horasreales" id="horasreales" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)">'+
                    '</div>'+
                  '</div>'+
                  '<div class="row">'+
                    '<div class="col-md-2" >'+
                      '<label>Rodar</label>'+
                      '<input type="text" class="form-control" name="rodar" id="rodar"  data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                    '</div>'+
                    '<div class="col-md-2">'+
                      '<label>Plazo Días</label>'+
                      '<input type="text" class="form-control" name="plazodias" id="plazodias"  required  onkeyup="tipoLetra(this);" autocomplete="off">'+
                    '</div>'+
                  '</div>'+
                '</div>'+
              '</div>'+
            '</div>'+ 
            '<div class="col-md-12">'+   
              '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                '<li role="presentation" class="active">'+
                  '<a href="#serviciostab" data-toggle="tab">Servicios</a>'+
                '</li>'+
              '</ul>'+
              '<div class="tab-content">'+
                '<div role="tabpanel" class="tab-pane fade in active" id="serviciostab">'+
                  '<div class="row">'+
                    '<div class="col-md-12 table-responsive cabecerafija" style="height: 300px;overflow-y: scroll;padding: 0px 0px;">'+
                      '<table id="tablaserviciosordentrabajo" class="table table-bordered tablaserviciosordentrabajo">'+
                        '<thead class="'+background_tables+'">'+
                          '<tr>'+
                            '<th class="'+background_tables+'">#</th>'+
                            '<th class="'+background_tables+'">Código</th>'+
                            '<th class="'+background_tables+'"><div style="width:200px !important;">Descripción</div></th>'+
                            '<th class="'+background_tables+'">Unidad</th>'+
                            '<th class="customercolortheadth">Cantidad</th>'+
                            '<th class="customercolortheadth">Precio $</th>'+
                            '<th class="'+background_tables+'">Importe $</th>'+
                            '<th class="customercolortheadth">Dcto %</th>'+
                            '<th class="customercolortheadth">Dcto $</th>'+
                            '<th class="'+background_tables+'">SubTotal $</th>'+
                            '<th class="customercolortheadth">Iva %</th>'+
                            '<th class="'+background_tables+'">Iva $</th>'+
                            '<th class="'+background_tables+'">Total $</th>'+
                            '<th class="'+background_tables+'">Costo $</th>'+
                            '<th class="'+background_tables+'">Costo Total $</th>'+
                            '<th class="customercolortheadth">Comisión %</th>'+
                            '<th class="'+background_tables+'">Comisión $</th>'+
                            '<th class="bg-amber">Utilidad $</th>'+
                            '<th class="'+background_tables+'">Departamento</th>'+
                            '<th class="'+background_tables+'">Cargo</th>'+
                            '<th class="'+background_tables+'">Fecha</th>'+
                            '<th class="'+background_tables+'">Traspaso</th>'+
                            '<th class="'+background_tables+'">Compra</th>'+
                            '<th class="'+background_tables+'">Usuario</th>'+
                            '<th class="customercolortheadth">Anotaciones</th>'+
                            '<th class="'+background_tables+'">Status</th>'+
                            '<th class="'+background_tables+'">Item</th>'+
                            '<th class="'+background_tables+'">Técnico 1</th>'+
                            '<th class="'+background_tables+'">Técnico 2</th>'+
                            '<th class="'+background_tables+'">Técnico 3</th>'+
                            '<th class="'+background_tables+'">Técnico 4</th>'+
                            '<th class="'+background_tables+'">Horas 1</th>'+
                            '<th class="'+background_tables+'">Horas 2</th>'+
                            '<th class="'+background_tables+'">Horas 3</th>'+
                            '<th class="'+background_tables+'">Horas 4</th>'+
                            '<th class="customercolortheadth">Promoción</th>'+
                            '<th class="'+background_tables+'">Partida</th>'+
                            '<th class="'+background_tables+'">Almacén</th>'+
                            '<th class="'+background_tables+'">Cotización</th>'+
                          '</tr>'+
                        '</thead>'+
                        '<tbody>'+           
                        '</tbody>'+
                      '</table>'+
                    '</div>'+
                  '</div>'+ 
                  '<div class="row">'+
                    '<div class="col-md-9">'+
                      '<div class="row">'+ 
                        '<div class="col-md-3">'+
                          '<label>Falla</label>'+
                          '<textarea class="form-control" name="falla" id="falla"  required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"  rows="4"></textarea>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Observaciones</label>'+
                          '<textarea class="form-control" name="observaciones" id="observaciones"  required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rows="4"></textarea>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Causa</label>'+
                          '<textarea class="form-control" name="causa" id="causa" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rows="4"></textarea>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Corrección</label>'+
                          '<textarea class="form-control" name="correccion" id="correccion"  data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rows="4"></textarea>'+
                        '</div>'+
                      '</div>'+  
                    '</div>'+ 
                    '<div class="col-md-3">'+
                      '<table class="table table-striped table-hover">'+
                        '<tr>'+
                          '<td style="padding:0px !important;">Importe</td>'+
                          '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '</tr>'+
                        '<tr>'+
                          '<td style="padding:0px !important;">Descuento</td>'+
                          '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '</tr>'+
                        '<tr>'+
                          '<td style="padding:0px !important;">SubTotal</td>'+
                          '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '</tr>'+
                        '<tr>'+
                          '<td style="padding:0px !important;">Iva</td>'+
                          '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '</tr>'+
                        '<tr>'+
                          '<td style="padding:0px !important;">Total</td>'+
                          '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '</tr>'+
                      '</table>'+
                    '</div>'+
                  '</div>'+   
                '</div>'+
              '</div>'+ 
            '</div>';
  $("#tabsform").html(tabs);
  $("#serie").val(serieusuario);
  $("#serietexto").html("Serie: "+serieusuario);
  $("#numerofilastablaservicios").val(0);
  $("#numerofilas").val(0);
  //factura a
  $("#textonombreclientefacturaaa").html("");
  //del cliente
  $("#textonombreclientedelcliente").html("");
  //agente
  $("#textonombreagente").html("");
  //vin
  $("#textonombrevin").html("");
  //asignar el tipo de operacion que se realizara
  $("#tipooperacion").val("alta");
  obtenultimonumero();
  obtenertiposordenestrabajo()
  obtenertiposunidades();
  asignarfechaactual();
  //reiniciar contadores  
  contadorservicios=0;
  contadorfilas = 0;
  item = 1;
  //selects
  $("#tiposervicio").select2();
  $("#tipoorden").select2();
  $("#tipounidad").select2();
  //activar busquedas
  $("#codigoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerservicioporcodigo();
    }
  });
  //activar busqueda para cliente a
  $('#numeroclientefacturaa').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerclientefacturaapornumero();
    }
  });
  //regresar numero cliente a
  $('#numeroclientefacturaa').on('change', function(e) {
    regresarnumeroclientefacturaa();
  });
  //activar busqueda para cliente de
  $('#numeroclientedelcliente').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerclientedelclientepornumero();
    }
  });
  //regresar numero cliente de
  $('#numeroclientedelcliente').on('change', function(e) {
    regresarnumeroclientedelcliente();
  });
  //activar busqueda para agentes
  $('#numeroagente').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obteneragentepornumero();
    }
  });
  //regresar numero agente
  $('#numeroagente').on('change', function(e) {
    regresarnumeroagente();
  });
  //activar busqueda para vines
  $('#vin').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenervinpornumero();
    }
  });
  //regresar vin
  $('#vin').on('change', function(e) {
    regresarnumerovin();
  });
  //se debe motrar el input para buscar los productos
  $("#divbuscarcodigoservicio").show();
  $("#ModalAlta").modal('show');
}
//eliminar una fila en la tabla
function eliminarfila(numerofila){
  var confirmacion = confirm("Esta seguro de eliminar el servicio?"); 
  if (confirmacion == true) { 
    var traspasopartida = $("#filaservicio"+numerofila+" .traspasopartida").val();
    if(traspasopartida != ''){
      msjerrorcancelartraspaso();
    }else{
      //eliminar fila
      $("#filaservicio"+numerofila).remove();
      contadorfilas--; //importante para todos los calculo en el modulo se debe restar al contadorfilas la fila que se acaba de eliminar
      contadorservicios--;
      item--;
      renumerarfilasordentrabajo();//importante para todos los calculo en el modulo
      calculartotalordentrabajo();
      $("#numerofilastablaservicios").val(contadorservicios);
      $("#numerofilas").val(contadorservicios);
    }
  } 
}
//renumerar las filas de la tabla
function renumerarfilasordentrabajo(){
  var lista;
  var tipo = "alta";
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("cantidadpartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo("+i+');cambiodecantidadopreciopartida('+i+',\''+tipo +'\')');
  }
  //renumero el precio de la partida
  lista = document.getElementsByClassName("preciopartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo("+i+');cambiodecantidadopreciopartida('+i+',\''+tipo +'\')');
  }
  //renumerar descuento en pesos
  lista = document.getElementsByClassName("descuentoporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculardescuentopesospartida("+i+')');
  }
  //renumerar porcentaje de descuento
  lista = document.getElementsByClassName("descuentopesospartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida("+i+')');
  }
  //renumerar porcentaje de iva
  lista = document.getElementsByClassName("ivaporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilasordentrabajo("+i+')');
  }
  //renumerar item
  lista = document.getElementsByClassName("itempartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("value", i+parseInt(1));
  }
  //renumerar partida
  lista = document.getElementsByClassName("partidapartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("value", i+parseInt(1));
  }
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
      url:ordenes_trabajo_guardar,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        if(data == 1){
          msj_errorordenexistente();
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
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(ordendesactivar){
  $.get(ordenes_trabajo_verificar_uso_en_modulos,{ordendesactivar:ordendesactivar}, function(data){
    if(data.Status == 'BAJA'){
      $("#ordendesactivar").val(0);
      $("#textomodaldesactivar").html('Error, esta Orden ya fue dado de baja');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{ 
      if(data.resultadofechas != ''){
        $("#ordendesactivar").val(0);
        $("#textomodaldesactivar").html('Error solo se pueden dar de baja las ordenes de trabajo del mes actual, fecha de la orden de trabajo: ' + data.resultadofechas);
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        if(data.condetalles == true){
          $("#ordendesactivar").val(0);
          $("#textomodaldesactivar").html('Error no deben existir partidas en las ordenes de trabajo');
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else{
          $("#ordendesactivar").val(ordendesactivar);
          $("#textomodaldesactivar").html('Estas seguro de dar de baja la orden de trabajo? No'+ ordendesactivar);
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
      url:ordenes_trabajo_alta_o_baja,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#estatusregistro').modal('hide');
        msj_statuscambiado();
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
function obtenerdatos(ordenmodificar){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(ordenes_trabajo_obtener_orden_trabajo,{ordenmodificar:ordenmodificar },function(data){
    $("#titulomodal").html('Modificación Orden Trabajo --- STATUS : ' + data.ordentrabajo.Status);
    //formulario modificacion
    var tabs ='<div class="col-md-12">'+
                '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                  '<li role="presentation" class="active">'+
                    '<a href="#datosgeneralesordentrabajotab" data-toggle="tab">Datos Generales</a>'+
                  '</li>'+
                  '<li role="presentation">'+
                    '<a href="#datostab" data-toggle="tab">Datos</a>'+
                  '</li>'+
                  '<li role="presentation">'+
                    '<a href="#estadotab" data-toggle="tab">Estado</a>'+
                  '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                  '<div role="tabpanel" class="tab-pane fade in active" id="datosgeneralesordentrabajotab">'+
                    '<div class="row">'+
                      '<div class="col-md-2">'+
                        '<label>Orden <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b></label>'+
                        '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                        '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                        '<input type="hidden" class="form-control" name="numerofilastablaservicios" id="numerofilastablaservicios" value="0" required readonly>'+
                        '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" value="0" readonly>'+
                        '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Tipo de Orden</label>'+
                        '<select name="tipoorden" id="tipoorden" class="form-control select2" style="width:100% !important;" required>'+
                        '</select>'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Tipo de Unidad</label>'+
                        '<select name="tipounidad" id="tipounidad" class="form-control select2" style="width:100% !important;" required>'+
                        '</select>'+
                      '</div>'+
                      '<div class="col-md-3">'+
                        '<label>Fecha</label>'+
                        '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();" onkeyup="tipoLetra(this);">'+
                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                      '</div>'+
                      '<div class="col-md-3">'+
                        '<label>Entrega Promesa</label>'+
                        '<input type="datetime-local" class="form-control" name="fechaentregapromesa" id="fechaentregapromesa"  required onchange="validasolomesactual();" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-3">'+
                        '<label>Factura a <span class="label label-danger" id="textonombreclientefacturaaa"></span></label>'+
                        '<table>'+
                          '<tr>'+
                            '<td>'+
                              '<div class="btn bg-blue waves-effect" onclick="listarclientesfacturaa()">Seleccionar</div>'+
                            '</td>'+
                            '<td>'+
                              '<div class="form-line">'+
                                '<input type="text" class="form-control" name="numeroclientefacturaa" id="numeroclientefacturaa" required data-parsley-type="integer" autocomplete="off">'+
                                '<input type="hidden" class="form-control" name="numeroclientefacturaaanterior" id="numeroclientefacturaaanterior" required data-parsley-type="integer">'+
                                '<input type="hidden" class="form-control" name="clientefacturaa" id="clientefacturaa" required readonly>'+
                              '</div>'+
                            '</td>'+
                          '</tr>'+  
                        '</table>'+
                      '</div>'+
                      '<div class="col-md-3">'+
                        '<label>Del cliente <span class="label label-danger" id="textonombreclientedelcliente"></span></label>'+
                        '<table>'+
                          '<tr>'+
                            '<td>'+
                              '<div class="btn bg-blue waves-effect" onclick="listarclientesdelcliente()">Seleccionar</div>'+
                            '</td>'+
                            '<td>'+    
                              '<div class="form-line">'+
                                '<input type="text" class="form-control" name="numeroclientedelcliente" id="numeroclientedelcliente" required data-parsley-type="integer" autocomplete="off">'+
                                '<input type="hidden" class="form-control" name="numeroclientedelclienteanterior" id="numeroclientedelclienteanterior" required data-parsley-type="integer">'+
                                '<input type="hidden" class="form-control" name="clientedelcliente" id="clientedelcliente" required readonly>'+
                              '</div>'+
                            '</td>'+    
                          '</tr> '+   
                        '</table>'+
                      '</div>'+
                      '<div class="col-md-3">'+
                        '<label>Agente <span class="label label-danger" id="textonombreagente"></span></label>'+
                        '<table class="col-md-12">'+
                          '<tr>'+
                            '<td>'+
                              '<div class="btn bg-blue waves-effect" onclick="listaragentes()">Seleccionar</div>'+
                            '</td>'+
                            '<td>'+   
                              '<div class="form-line">'+
                                '<input type="text" class="form-control" name="numeroagente" id="numeroagente" required data-parsley-type="integer" autocomplete="off">'+
                                '<input type="hidden" class="form-control" name="numeroagenteanterior" id="numeroagenteanterior" required data-parsley-type="integer">'+
                                '<input type="hidden" class="form-control" name="agente" id="agente" required readonly>'+
                              '</div>'+
                            '</td>'+   
                          '</tr>'+    
                        '</table>'+
                      '</div>'+
                      '<div class="col-md-3" id="divcaso">'+
                        '<label>Caso</label>'+
                        '<input type="text" class="form-control" name="caso" id="caso"   onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]" autocomplete="off">'+
                      '</div>'+
                    '</div>'+
                      '<div class="row">'+
                        '<div class="col-md-4" id="divbuscarcodigoservicio">'+
                          '<label>Escribe el código a buscar y presiona la tecla ENTER (Carga Mano de Obra)</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" id="btnobtenerservicios" onclick="listarservicios()">Ver Servicios</div>'+
                              '</td>'+
                              '<td>'+ 
                                '<div class="form-line">'+
                                  '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del servicio y da enter" autocomplete="off">'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+    
                          '</table>'+
                        '</div>'+
                      '</div>'+
                  '</div>'+ 
                  '<div role="tabpanel" class="tab-pane fade" id="datostab">'+
                    '<div class="row">'+
                      '<div class="col-md-2">'+
                        '<label>Tipo Servicio</label>'+
                        '<select name="tiposervicio" id="tiposervicio" class="form-control select2" style="width:100% !important;" required>'+
                          '<option selected disabled hidden>Selecciona</option>'+
                          '<option value="NORMAL">NORMAL</option>'+
                          '<option value="CORRECTIVO" selected>CORRECTIVO</option>'+
                          '<option value="PREVENTIVO">PREVENTIVO</option>'+
                        '</select>'+
                      '</div>'+
                      '<div class="col-md-3">'+
                        '<label>Vin / Serie <span class="label label-danger" id="textonombrevin"></span></label>'+
                        '<table class="col-md-12">'+
                          '<tr>'+
                            '<td>'+
                              '<div class="btn bg-blue waves-effect" onclick="listarvines()">Seleccionar</div>'+
                            '</td>'+
                            '<td>'+    
                              '<div class="form-line">'+
                                '<input type="text" class="form-control" name="vin" id="vin" required data-parsley-length="[1, 30]" autocomplete="off">'+
                                '<input type="hidden" class="form-control" name="vinanterior" id="vinanterior" required data-parsley-length="[1, 30]">'+
                              '</div>'+
                            '</td>'+    
                          '</tr>'+    
                        '</table>'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Motor / Serie</label>'+
                        '<input type="text" class="form-control" name="motor" id="motor"  required  onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]" autocomplete="off">'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Marca</label>'+
                        '<input type="text" class="form-control" name="marca" id="marca"  required  onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]" autocomplete="off">'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Modelo</label>'+
                        '<input type="text" class="form-control" name="modelo" id="modelo"  required  onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]" autocomplete="off">'+
                      '</div>'+
                      '<div class="col-md-1">'+
                        '<label>Año</label>'+
                        '<input type="text" class="form-control" name="ano" id="ano"  data-parsley-max="'+parseInt(periodohoy)+'" data-parsley-type="digits" data-parsley-length="[4,4]" required  onkeyup="tipoLetra(this);" autocomplete="off">'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-2">'+
                        '<label>Kilómetros</label>'+
                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="kilometros" id="kilometros" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)"  required >'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Placas</label>'+
                        '<input type="text" class="form-control" name="placas" id="placas"  required data-parsley-length="[1, 10]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label># Económico</label>'+
                        '<input type="text" class="form-control" name="economico" id="economico"  required  data-parsley-length="[1, 30]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Color</label>'+
                        '<input type="text" class="form-control" name="color" id="color"  required data-parsley-length="[3, 30]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Km Próx Servicio</label>'+
                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="kmproxservicio" id="kmproxservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)"  >'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Fecha Recordatorio Cliente</label>'+
                        '<input type="date" class="form-control" name="fecharecordatoriocliente" id="fecharecordatoriocliente"   onkeyup="tipoLetra(this);">'+
                      '</div>'+
                    '</div>'+
                  '</div>'+
                  '<div role="tabpanel" class="tab-pane fade" id="estadotab">'+
                    '<div class="row">'+
                      '<div class="col-md-2">'+
                        '<label>Reclamo</label>'+
                        '<input type="text" class="form-control" name="reclamo" id="reclamo"  data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Orden Cliente</label>'+
                        '<input type="text" class="form-control" name="ordencliente" id="ordencliente"   onkeyup="tipoLetra(this);" autocomplete="off" required>'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Campaña No</label>'+
                        '<input type="text" class="form-control" name="campana" id="campana" data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                      '</div>'+
                      '<div class="col-md-2" >'+
                        '<label>Promoción</label>'+
                        '<input type="text" class="form-control" name="promocion" id="promocion"   onkeyup="tipoLetra(this);" autocomplete="off">'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Bahia</label>'+
                        '<input type="text" class="form-control" name="bahia" id="bahia"  required  onkeyup="tipoLetra(this);" autocomplete="off">'+
                      '</div>'+
                      '<div class="col-md-2" >'+
                        '<label>Horas Reales</label>'+
                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="horasreales" id="horasreales" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)">'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-2" >'+
                        '<label>Rodar</label>'+
                        '<input type="text" class="form-control" name="rodar" id="rodar"  data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                      '</div>'+
                      '<div class="col-md-2">'+
                        '<label>Plazo Días</label>'+
                        '<input type="text" class="form-control" name="plazodias" id="plazodias"  required  onkeyup="tipoLetra(this);" autocomplete="off">'+
                      '</div>'+
                    '</div>'+
                  '</div>'+
                '</div>'+
              '</div>'+ 
              '<div class="col-md-12">'+ 
                '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                  '<li role="presentation" class="active">'+
                    '<a href="#serviciostab" data-toggle="tab">Servicios</a>'+
                  '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                  '<div role="tabpanel" class="tab-pane fade in active" id="serviciostab">'+
                    '<div class="row">'+
                      '<div class="col-md-12 table-responsive cabecerafija" style="height: 300px;overflow-y: scroll;padding: 0px 0px;">'+
                        '<table id="tablaserviciosordentrabajo" class="table table-bordered tablaserviciosordentrabajo">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="'+background_tables+'">Código</th>'+
                              '<th class="'+background_tables+'"><div style="width:200px !important;">Descripción</div></th>'+
                              '<th class="'+background_tables+'">Unidad</th>'+
                              '<th class="customercolortheadth">Cantidad</th>'+
                              '<th class="customercolortheadth">Precio $</th>'+
                              '<th class="'+background_tables+'">Importe $</th>'+
                              '<th class="customercolortheadth">Dcto %</th>'+
                              '<th class="customercolortheadth">Dcto $</th>'+
                              '<th class="'+background_tables+'">SubTotal $</th>'+
                              '<th class="customercolortheadth">Iva %</th>'+
                              '<th class="'+background_tables+'">Iva $</th>'+
                              '<th class="'+background_tables+'">Total $</th>'+
                              '<th class="'+background_tables+'">Costo $</th>'+
                              '<th class="'+background_tables+'">Costo Total $</th>'+
                              '<th class="customercolortheadth">Comisión %</th>'+
                              '<th class="'+background_tables+'">Comisión $</th>'+
                              '<th class="bg-amber">Utilidad $</th>'+
                              '<th class="'+background_tables+'">Departamento</th>'+
                              '<th class="'+background_tables+'">Cargo</th>'+
                              '<th class="'+background_tables+'">Fecha</th>'+
                              '<th class="'+background_tables+'">Traspaso</th>'+
                              '<th class="'+background_tables+'">Compra</th>'+
                              '<th class="'+background_tables+'">Usuario</th>'+
                              '<th class="customercolortheadth">Anotaciones</th>'+
                              '<th class="'+background_tables+'">Status</th>'+
                              '<th class="'+background_tables+'">Item</th>'+
                              '<th class="'+background_tables+'">Técnico 1</th>'+
                              '<th class="'+background_tables+'">Técnico 2</th>'+
                              '<th class="'+background_tables+'">Técnico 3</th>'+
                              '<th class="'+background_tables+'">Técnico 4</th>'+
                              '<th class="'+background_tables+'">Horas 1</th>'+
                              '<th class="'+background_tables+'">Horas 2</th>'+
                              '<th class="'+background_tables+'">Horas 3</th>'+
                              '<th class="'+background_tables+'">Horas 4</th>'+
                              '<th class="customercolortheadth">Promoción</th>'+
                              '<th class="'+background_tables+'">Partida</th>'+
                              '<th class="'+background_tables+'">Almacén</th>'+
                              '<th class="'+background_tables+'">Cotización</th>'+
                            '</tr>'+
                          '</thead>'+
                          '<tbody>'+           
                          '</tbody>'+
                        '</table>'+
                      '</div>'+
                    '</div>'+ 
                    '<div class="row">'+
                      '<div class="col-md-9">'+  
                        '<div class="row">'+ 
                          '<div class="col-md-3">'+
                            '<label>Falla</label>'+
                            '<textarea class="form-control" name="falla" id="falla"  required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rows="4"></textarea>'+
                          '</div>'+
                          '<div class="col-md-3">'+
                            '<label>Observaciones</label>'+
                            '<textarea class="form-control" name="observaciones" id="observaciones"  required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rows="4"></textarea>'+
                          '</div>'+
                          '<div class="col-md-3">'+
                            '<label>Causa</label>'+
                            '<textarea class="form-control" name="causa" id="causa" data-parsley-length="[1, 255]"  onkeyup="tipoLetra(this);" rows="4"></textarea>'+
                          '</div>'+
                          '<div class="col-md-3">'+
                            '<label>Corrección</label>'+
                            '<textarea class="form-control" name="correccion" id="correccion"  data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rows="4"></textarea>'+
                          '</div>'+
                        '</div>'+
                      '</div>'+ 
                      '<div class="col-md-3">'+
                        '<table class="table table-striped table-hover">'+
                          '<tr>'+
                            '<td style="padding:0px !important;">Importe</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                          '<tr>'+
                            '<td style="padding:0px !important;">Descuento</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                          '<tr>'+
                            '<td style="padding:0px !important;">SubTotal</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                          '<tr>'+
                            '<td style="padding:0px !important;">Iva</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                          '<tr>'+
                            '<td style="padding:0px !important;">Total</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                        '</table>'+
                      '</div>'+
                    '</div>'+   
                  '</div>'+ 
                '</div>'+ 
              '</div>';
    $("#tabsform").html(tabs);
    $("#periodohoy").val(data.ordentrabajo.Periodo);
    $("#folio").val(data.ordentrabajo.Folio);
    $("#serie").val(data.ordentrabajo.Serie);
    $("#serietexto").html("Serie: "+data.ordentrabajo.Serie);
    $("#numerofilastablaservicios").val(data.numerodetallesordentrabajo);
    $("#numerofilas").val(data.numerodetallesordentrabajo);
    $("#fecha").val(data.fecha);
    $("#fechaentregapromesa").val(data.fechaentrega);
    $("#numeroclientefacturaa").val(data.cliente.Numero);
    $("#numeroclientefacturaaanterior").val(data.cliente.Numero);
    $("#clientefacturaa").val(data.cliente.Nombre);
    $("#textonombreclientefacturaaa").html(data.cliente.Nombre.substring(0, 40));
    $("#numeroclientedelcliente").val(data.delcliente.Numero);
    $("#numeroclientedelclienteanterior").val(data.delcliente.Numero);
    $("#clientedelcliente").val(data.delcliente.Nombre);
    $("#textonombreclientedelcliente").html(data.delcliente.Nombre.substring(0, 40));
    $("#numeroagente").val(data.agente.Numero);
    $("#numeroagenteanterior").val(data.agente.Numero);
    $("#agente").val(data.agente.Nombre);
    $("#textonombreagente").html(data.agente.Nombre);
    $("#caso").val(data.ordentrabajo.Caso);
    $("#vin").val(data.ordentrabajo.Vin);
    $("#vinanterior").val(data.ordentrabajo.Vin);
    $("#textonombrevin").html(data.ordentrabajo.Vin);
    $("#motor").val(data.ordentrabajo.Motor);
    $("#marca").val(data.ordentrabajo.Marca);
    $("#modelo").val(data.ordentrabajo.Modelo);
    $("#ano").val(data.ordentrabajo.Año);
    $("#kilometros").val(data.kilometros);
    $("#placas").val(data.ordentrabajo.Placas);
    $("#economico").val(data.ordentrabajo.Economico);
    $("#color").val(data.ordentrabajo.Color);
    $("#kmproxservicio").val(data.kmproximoservicio);
    $("#fecharecordatoriocliente").val(data.fecharecordatoriocliente);
    $("#reclamo").val(data.ordentrabajo.Reclamo);
    $("#ordencliente").val(data.ordentrabajo.Pedido);
    $("#campana").val(data.ordentrabajo.Campaña);
    $("#promocion").val(data.ordentrabajo.Promocion);
    $("#bahia").val(data.ordentrabajo.Bahia);
    $("#horasreales").val(data.horasreales);
    $("#rodar").val(data.ordentrabajo.Rodar);    
    $("#plazodias").val(data.ordentrabajo.Plazo);
    $("#falla").val(data.ordentrabajo.Falla);
    $("#observaciones").val(data.ordentrabajo.ObsOrden);
    $("#causa").val(data.ordentrabajo.Causa);
    $("#correccion").val(data.ordentrabajo.Correccion);
    $("#importe").val(data.importe);
    $("#descuento").val(data.descuento);
    $("#subtotal").val(data.subtotal);
    $("#iva").val(data.iva);
    $("#total").val(data.total);
    //filas tabla
    $("#tablaserviciosordentrabajo").append(data.filasdetallesordentrabajo);
    //se deben asignar los valores a los contadores para que las sumas resulten correctas
    contadorservicios = data.contadorservicios;
    contadorfilas = data.contadorfilas;
    item = data.item;
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //activar busquedas
    $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenerservicioporcodigo();
      }
    });
    //activar busqueda para cliente a
    $('#numeroclientefacturaa').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenerclientefacturaapornumero();
      }
    });
    //regresar numero cliente a
    $('#numeroclientefacturaa').on('change', function(e) {
      regresarnumeroclientefacturaa();
    });
    //activar busqueda para cliente de
    $('#numeroclientedelcliente').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenerclientedelclientepornumero();
      }
    });
    //regresar numero cliente de
    $('#numeroclientedelcliente').on('change', function(e) {
      regresarnumeroclientedelcliente();
    });
    //activar busqueda para agentes
    $('#numeroagente').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obteneragentepornumero();
      }
    });
    //regresar numero agente
    $('#numeroagente').on('change', function(e) {
      regresarnumeroagente();
    });
    //activar busqueda para vines
    $('#vin').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenervinpornumero();
      }
    });
    //regresar vin
    $('#vin').on('change', function(e) {
      regresarnumerovin();
    });
    selectsordentrabajo(data);
  }).fail( function() {
    msj_errorajax();
    $('.page-loader-wrapper').css('display', 'none');
  })
}
async function selectsordentrabajo(data){
  await retraso();
  $("#tiposervicio").val(data.ordentrabajo.TipoServicio).change();
  $("#tipoorden").html(data.selecttipoordentrabajo);
  $("#tipounidad").html(data.selecttipounidad);
  $("#tiposervicio").select2();
  $("#tipoorden").select2();
  $("#tipounidad").select2();
  //se debe esconder el input para buscar los productos porque en la modificacion no se permiten agregar productos
  $("#divbuscarcodigoservicio").show();
  mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
  $('.page-loader-wrapper').css('display', 'none');
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
      url:ordenes_trabajo_guardar_modificacion,
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
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function terminar(ordenterminar){
  $.get(ordenes_trabajo_verificar_status_orden,{ordenterminar:ordenterminar}, function(data){
    if(data.ordentrabajo.Status != 'ABIERTA'){
      $("#ordenterminar").val(data.ordentrabajo.Orden);
      $("#clienteordenterminar").val(data.cliente.Nombre);
      $("#fechaordenterminar").val(data.fecha);
      $("#btnterminar").hide();
      $('#modalterminarorden').modal('show');
    }else{
      $("#ordenterminar").val(data.ordentrabajo.Orden);
      $("#clienteordenterminar").val(data.cliente.Nombre);
      $("#fechaordenterminar").val(data.fecha);
      $("#btnterminar").show();
      $('#modalterminarorden').modal('show');
    }
  }) 
}
$("#btnterminar").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formterminar")[0]);
  var form = $("#formterminar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:ordenes_trabajo_terminar_orden,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#modalterminarorden').modal('hide');
        msj_ordenterminada();
        $("#formterminar")[0].reset();
        $('.page-loader-wrapper').css('display', 'none');
      },
      error:function(data){
        if(data.status == 403){
          msj_errorenpermisos();
        }else{
          msj_errorajax();
        }
        $('#modalterminarorden').modal('hide');
        $('.page-loader-wrapper').css('display', 'none');
      }
    })
  }else{
    form.parsley().validate();
  }
});
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento){
  $.get(ordenes_trabajo_obtener_datos_envio_email,{documento:documento}, function(data){
    $("#textomodalenviarpdfemail").html("Enviar email Orden de Trabajo No." + documento);
    $("#emaildocumento").val(documento);
    $("#emailde").val(data.emailde);
    $("#emailasunto").val("ORDEN DE TRABAJO NO. " + documento +" DE "+ nombreempresa);
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
      url:ordenes_trabajo_enviar_pdfs_email,
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
                                                '<th>Orden</th>'+
                                                '<th>Cliente</th>'+
                                                '<th>Total</th>'+
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
          url: ordenes_trabajo_buscar_folio_string_like,
          data: function (d) {
            d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Orden', name: 'Orden' },
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
    if(campos[i] == 'Orden' || campos[i] == 'Status' || campos[i] == 'Periodo'){
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