'use strict'
var tabla;
var form;
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
      $("#fechaemitida").val(fechas.fecha).attr('min', fechas.fechamin).attr('max', fechas.fechamax);
    })
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  var serie = $("#serie").val();
  $.get(compras_obtener_ultimo_folio,{serie:serie}, function(folio){
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
  $("#formxml")[0].reset();
  //Resetear las validaciones del formulario alta
  form = $("#formparsley");
  form.parsley().reset();
  //volver a aplicar configuracion a datatable principal para que realize la busqueda con la tecla enter
  regresarbusquedadatatableprincipal();
}
//validar si se debe mostrar o no el buscador de productos
function mostrarbuscadorcodigoproducto(){
  var orden = $("#orden").val();
  var proveedor = $("#proveedor").val();
  if(orden != "" && proveedor != ""){
    $("#divbuscarcodigoproducto").show();
  }else{
    $("#divbuscarcodigoproducto").hide();
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
        url: compras_obtener,
        data: function (d) {
            d.periodo = $("#periodo").val();
        }
    },
    "createdRow": function( row, data, dataIndex){
        if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
    },
    columns: campos_tabla,
    "drawCallback": function( data ) {
        $("#sumaimportefiltrado").html(number_format(round(data.json.sumaimporte, numerodecimales), numerodecimales, '.', ''));
        $("#sumadescuentofiltrado").html(number_format(round(data.json.sumadescuento, numerodecimales), numerodecimales, '.', ''));
        $("#sumasubtotalfiltrado").html(number_format(round(data.json.sumasubtotal, numerodecimales), numerodecimales, '.', ''));
        $("#sumaivafiltrado").html(number_format(round(data.json.sumaiva, numerodecimales), numerodecimales, '.', ''));
        $("#sumatotalfiltrado").html(number_format(round(data.json.sumatotal, numerodecimales), numerodecimales, '.', ''));
        $("#sumaabonosfiltrado").html(number_format(round(data.json.sumaabonos, numerodecimales), numerodecimales, '.', ''));
        $("#sumadescuentosfiltrado").html(number_format(round(data.json.sumadescuentos, numerodecimales), numerodecimales, '.', ''));
        $("#sumasaldofiltrado").html(number_format(round(data.json.sumasaldo, numerodecimales), numerodecimales, '.', ''));
    },
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
    obtenerdatos(data.Compra);
  });
}
//obtener tipos ordenes de compra
function obtenertiposordenescompra(tipoalta, almacen){
    $.get(compras_obtener_tipos_ordenes_compra, {tipoalta:tipoalta, almacen:almacen}, function(select_tipos_ordenes_compra){
      $("#tipo").html(select_tipos_ordenes_compra);
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
  var tserdoc = $('#tbllistadoseriedocumento').DataTable({
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
        url: compras_obtener_series_documento
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Serie', name: 'Serie' },
          { data: 'Documento', name: 'Documento' },
          { data: 'Nombre', name: 'Nombre' }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
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
    $.get(compras_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie}, function(folio){
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
                              '<div class="table-responsive">'+
                                  '<table id="tbllistadoproveedor" class="tbllistadoproveedor table table-bordered table-striped table-hover" style="width:100% !important;">'+
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
            url: compras_obtener_proveedores,
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
            $buscar.focus();
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
      seleccionarproveedor(data.Numero, data.Nombre, data.Plazo, data.Rfc, data.SolicitarXML);
    });
}
//obtener datos del proveedor
function seleccionarproveedor(Numero, Nombre, Plazo, Rfc, SolicitarXML){
  var numeroproveedoranterior = $("#numeroproveedoranterior").val();
  var numeroproveedor = Numero;
  if(numeroproveedoranterior != numeroproveedor){
    var numerofilas = $("#numerofilas").val()
    if(parseInt(numerofilas) > 0){
      var confirmacion = confirm("Esta seguro de cambiar el proveedor?, esto eliminara las partidas agregadas");
    }else{
      var confirmacion = true;
    }
    if (confirmacion == true) {
      $("#tablaproductoscompras tbody").html("");
      $("#numeroproveedor").val(Numero);
      $("#numeroproveedoranterior").val(Numero);
      $("#proveedor").val(Nombre);
      if(parseInt(SolicitarXML) == 1){
        $("#emisornombredb").val(Nombre);
        $("#emisorrfcdb").val(Rfc);
      }else{
        $("#emisornombredb").val(Nombre);
        $("#emisorrfcdb").val(Rfc);
        $("#emisornombre").val(Nombre);
        $("#emisorrfc").val(Rfc);
      }
      $("#plazo").val(Plazo);
      $("#solicitarxml").val(SolicitarXML);
      if(Nombre != null){
        $("#textonombreproveedor").html(Nombre.substring(0, 40));
      }
      //mostrar boton de buscar ordenes de compra
      $("#btnlistarordenesdecompra").show();
      //comprobar numero de filas en la tabla
      comprobarfilas();
      //calcular totales
      calculartotal();
      //reiniciar contadores
      contadorproductos = 0;
      contadorfilas = 0;
      mostrarformulario();
    }
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
              url: compras_obtener_almacenes,
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
//obtener datos del almacen
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
    mostrarformulario();
  }
}
//listar departamentos
function listardepartamentos(fila){
  ocultarformulario();
  var tabladepartamentos =  '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Departamentos</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadodepartamento" class="tbllistadodepartamento table table-bordered table-striped table-hover" style="width:100% !important;">'+
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
                                '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                            '</div>';
  $("#contenidomodaltablas").html(tabladepartamentos);
  var tdeptos = $('#tbllistadodepartamento').DataTable({
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
          url: compras_obtener_departamentos,
          data: function (d) {
            d.fila = fila;
          }
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
                $('#tbllistadodepartamento').DataTable().search( this.value ).draw();
              }
          });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadodepartamento tbody').on('dblclick', 'tr', function () {
    var data = tdeptos.row( this ).data();
    seleccionardepartamento(data.Numero, data.Nombre, fila);
  });
}
//seleccion de departamento
function seleccionardepartamento(numerodepartamento, departamento, fila){
  $("#filaproducto"+fila+" .numerodepartamentopartida").val(numerodepartamento);
  $("#filaproducto"+fila+" .departamentopartida").val(departamento);
  mostrarformulario();
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
          url: compras_obtener_claves_productos,
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
          url: compras_obtener_claves_unidades,
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
//listar todas las ordenes de compra
function listarordenesdecompra (){
  ocultarformulario();
  var tablaordenescompra =  '<div class="modal-header '+background_forms_and_modals+'">'+
                              '<h4 class="modal-title">Ordenes de Compra</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                              '<div class="row">'+
                                  '<div class="col-md-12">'+
                                      '<div class="table-responsive">'+
                                          '<table id="tbllistadoordencompra" class="tbllistadoordencompra table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                              '<thead class="'+background_tables+'">'+
                                                  '<tr>'+
                                                      '<th>Operaciones</th>'+
                                                      '<th>Orden</th>'+
                                                      '<th>Fecha</th>'+
                                                      '<th>Referencia</th>'+
                                                      '<th>Tipo</th>'+
                                                      '<th>Almacen</th>'+
                                                      '<th>Total</th>'+
                                                      '<th>Autorizado</th>'+
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
    $("#contenidomodaltablas").html(tablaordenescompra);
    var tocs = $('#tbllistadoordencompra').DataTable({
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
          url: compras_obtener_ordenes_compra,
          data: function (d) {
              d.numeroproveedor = $("#numeroproveedor").val();
              d.tipocompra = $("#tipocompra").val();
          }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Orden', name: 'Orden' },
            { data: 'Fecha', name: 'Fecha' },
            { data: 'Referencia', name: 'Referencia', orderable: false, searchable: false },
            { data: 'Tipo', name: 'Tipo', orderable: false, searchable: false },
            { data: 'Almacen', name: 'Almacen', orderable: false, searchable: false },
            { data: 'Total', name: 'Total', orderable: false, searchable: false },
            { data: 'AutorizadoPor', name: 'AutorizadoPor', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoordencompra').DataTable().search( this.value ).draw();
                }
            });
        },
    });
    //seleccionar registro al dar doble click
    $('#tbllistadoordencompra tbody').on('dblclick', 'tr', function () {
      var data = tocs.row( this ).data();
      var tipoalta = $("#tipocompra").val();
      seleccionarordencompra(data.Folio, data.Orden, tipoalta);
    });
}
//obtener todos los datos de la orden de compra seleccionada
function seleccionarordencompra(Folio, Orden, tipoalta){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(compras_obtener_orden_compra, {Folio:Folio, Orden:Orden, tipoalta:tipoalta}, function(data){
    $("#orden").val(Orden);
    switch (tipoalta) {
      case 'GASTOS':
        //desabilitar almacen
        $("#numeroalmacen").val(0).attr('readonly', 'readonly');
        $("#numeroalmacenanterior").val(0).attr('readonly', 'readonly');
        $("#almacen").val(0).attr('readonly', 'readonly');
        break;
      case 'TOT':
        //colocar required a campo orden
        $("#ordentrabajo").val(data.ordencompra.OrdenTrabajo).attr('required', 'required');
        $("#ordentrabajoanterior").val(data.ordencompra.OrdenTrabajo).attr('required', 'required');
        //desabilitar almacen
        $("#numeroalmacen").val(0).attr('readonly', 'readonly');
        $("#numeroalmacenanterior").val(0).attr('readonly', 'readonly');
        $("#almacen").val(0).attr('readonly', 'readonly');
        break;
      default:
        if(data.almacen.Nombre != null){
          $("#textonombrealmacen").html(data.almacen.Nombre.substring(0, 40));
        }
        $("#almacen").val(data.almacen.Nombre);
        $("#numeroalmacen").val(data.almacen.Numero);
        $("#numeroalmacenanterior").val(data.almacen.Numero);
    }
    $("#observaciones").val(data.ordencompra.Obs);
    $("#plazo").val(data.ordencompra.Plazo);
    $("#tablaproductoscompras tbody").html(data.filasdetallesordencompra);
    $("#importe").val(data.ordencompra.Importe);
    $("#descuento").val(data.ordencompra.Descuento);
    $("#subtotal").val(data.ordencompra.Subtotal);
    $("#iva").val(data.ordencompra.Iva);
    $("#total").val(data.ordencompra.Total);
    var solicitarxml = $("#solicitarxml").val();
    if(solicitarxml == 0){
      $("#importexml").val(data.ordencompra.Importe);
      $("#descuentoxml").val(data.ordencompra.Descuento);
      $("#subtotalxml").val(data.ordencompra.Subtotal);
      $("#ivaxml").val(data.ordencompra.Iva);
      $("#totalxml").val(data.ordencompra.Total);
      $("#fechaemitida").val(data.fecha);
    }
    //detalles
    $("#arraycodigosdetallesordencompra").val(data.arraycodigosdetallesordencompra);
    $("#numerofilas").val(data.numerodetallesordencompra);
    //colocar valores a contadores
    contadorproductos = data.contadorproductos;
    contadorfilas = data.contadorfilas;
    seleccionartipoordencompra(data);
  })
}
async function seleccionartipoordencompra(data){
  await retraso();
  $("#tipo").val(data.ordencompra.Tipo).change();
  calculartotal();
  mostrarbuscadorcodigoproducto();
  mostrarformulario();
  $('.page-loader-wrapper').css('display', 'none');
  //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
  $(".inputnextdet").keyup(function (e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    var index = $(this).index(".inputnextdet");
    switch(code){
      case 13:
        //$(".inputnextdet").eq(index + 1).focus().select();
        break;
      case 39:
        $(".inputnextdet").eq(index + 1).focus().select();
        break;
      case 37:
        $(".inputnextdet").eq(index - 1).focus().select();
        break;
    }
  });
}
//Cada que se elija un archivo
function cambiodexml(e) {
  $("#btnenviarxml").click();
}
//Agregar respuesta a la datatable
$("#btnenviarxml").on('click', function(e){
  e.preventDefault();
  var xml = $('#xml')[0].files[0];
  var form_data = new FormData();
  form_data.append('xml', xml);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:compras_cargar_xml_alta,
    data: form_data,
    type: 'POST',
    contentType: false,
    processData: false,
    success: function (data) {
      if(data.array_comprobante.Descuento == null){
        var importexml = data.array_comprobante.SubTotal[0];
        var descuentoxml = '0.'+numerocerosconfigurados;
        var subtotalxml = data.array_comprobante.SubTotal[0];
        var ivaxml = data.TotalImpuestosTrasladados[0];
        var totalxml = data.array_comprobante.Total[0];
      }else{
        var importexml = data.array_comprobante.SubTotal[0];
        var descuentoxml = data.array_comprobante.Descuento[0]
        var subtotalxml = new Decimal(importexml).minus(descuentoxml);
        var ivaxml = data.TotalImpuestosTrasladados[0];
        var totalxml = new Decimal(subtotalxml).plus(ivaxml);
      }
      if(data.uuid != null){
        $("#uuidxml").val(data.uuid[0]);
        $("#uuid").val(data.uuid[0]);
      }
      if(data.fechatimbrado != null){
        $("#fechatimbrado").val(data.fechatimbrado[0]);
      }
      if(data.array_emisor.Rfc != null){
        $("#emisorrfc").val(data.array_emisor.Rfc[0]);
      }
      if(data.array_emisor.Nombre != null){
        $("#emisornombre").val(data.array_emisor.Nombre[0]);
      }
      if(data.array_receptor.Rfc != null){
        $("#receptorrfcxml").val(data.array_receptor.Rfc[0]);
      }
      if(data.array_receptor.Nombre != null){
        $("#receptornombrexml").val(data.array_receptor.Nombre[0]);
      }
      if(data.array_comprobante.Serie != null && data.array_comprobante.Folio != null){
        $("#factura").val(data.array_comprobante.Serie[0]+data.array_comprobante.Folio[0]);
      }
      $("#importexml").val(number_format(round(importexml, numerodecimales), numerodecimales, '.', ''));
      $("#descuentoxml").val(number_format(round(descuentoxml, numerodecimales), numerodecimales, '.', ''));
      $("#subtotalxml").val(number_format(round(subtotalxml, numerodecimales), numerodecimales, '.', ''));
      $("#ivaxml").val(number_format(round(ivaxml, 2), numerodecimales, '.', ''));
      $("#totalxml").val(number_format(round(totalxml, 2), numerodecimales, '.', ''));
      $("#fechaemitida").val(data.array_comprobante.Fecha[0]);
      //mostrar el total de la factura del proveedor
      //$("#totalfacturaproveedor").html("Total factura proveedor : "+ truncar(totalxml.toFixed(parseInt(numerodecimales)), numerodecimales));
      $("#totalfacturaproveedor").html("Total factura proveedor :"+ number_format(round(totalxml, numerodecimales), numerodecimales, '.', ''))
      //detalles xml
      /*$("#tablaproductoscompras tbody").html(data.filasdetallesxml);
      calculartotal();*/
      //validar si la fecha de la compra es igual a la fecha de la factura del proveedor
      validarmescompra();
      //mostrar boton de buscar proveedores
      $("#btnobtenerproveedores").show();
      //vaciar la fecha de la compra
      $("#fecha").val("");
      if($("#total").val() > 0){
        calculartotal();
      }
    },
    error: function (data) {
      console.log(data);
    }
  });
});
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
function obtenerproveedorpornumero(){
  var numeroproveedoranterior = $("#numeroproveedoranterior").val();
  var numeroproveedor = $("#numeroproveedor").val();
  if(numeroproveedoranterior != numeroproveedor){
    if($("#numeroproveedor").parsley().isValid()){
      $.get(compras_obtener_proveedor_por_numero, {numeroproveedor:numeroproveedor}, function(data){
        var numerofilas = $("#numerofilas").val()
        if(parseInt(numerofilas) > 0){
          var confirmacion = confirm("Esta seguro de cambiar el proveedor?, esto eliminara las partidas agregadas");
        }else{
          var confirmacion = true;
        }
        if (confirmacion == true) {
          $("#tablaproductoscompras tbody").html("");
          $("#numeroproveedor").val(data.numero);
          $("#numeroproveedoranterior").val(data.numero);
          $("#proveedor").val(data.nombre);
          if(parseInt(data.SolicitarXML) == 1){
            $("#emisornombredb").val(data.nombre);
            $("#emisorrfcdb").val(data.rfc);
          }else{
            $("#emisornombredb").val(data.nombre);
            $("#emisorrfcdb").val(data.rfc);
            $("#emisornombre").val(data.nombre);
            $("#emisorrfc").val(data.rfc);
          }
          $("#plazo").val(data.plazo);
          $("#solicitarxml").val(data.SolicitarXML);
          if(data.nombre != null){
            $("#textonombreproveedor").html(data.nombre.substring(0, 40));
          }
          //mostrar boton de buscar ordenes de compra
          $("#btnlistarordenesdecompra").show();
          //comprobar numero de filas en la tabla
          comprobarfilas();
          //calcular totales
          calculartotal();
          //reiniciar contadores
          contadorproductos = 0;
          contadorfilas = 0;
          mostrarformulario();
        }else{
          $("#numeroproveedor").val(numeroproveedoranterior);
        }
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
function obteneralmacenpornumero(){
  var numeroalmacenanterior = $("#numeroalmacenanterior").val();
  var numeroalmacen = $("#numeroalmacen").val();
  if(numeroalmacenanterior != numeroalmacen){
    if($("#numeroalmacen").parsley().isValid()){
      $.get(compras_obtener_almacen_por_numero, {numeroalmacen:numeroalmacen}, function(data){
          $("#numeroalmacen").val(data.numero);
          $("#numeroalmacenanterior").val(data.numero);
          $("#almacen").val(data.nombre);
          if(data.nombre != null){
            $("#textonombrealmacen").html(data.nombre.substring(0, 40));
          }
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
      url: compras_obtener_productos,
      data: function (d) {
        d.codigoabuscar = $("#codigoabuscar").val();
        d.numeroalmacen = $("#numeroalmacen").val();
        d.tipooperacion = $("#tipooperacion").val();
        d.tipoalta = $("#tipocompra").val();
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
    agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, number_format(round(data.Impuesto, numerodecimales), numerodecimales, '.', ''), data.SubTotal, data.Existencias, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, data.NombreClaveProducto, data.NombreClaveUnidad, number_format(round(data.CostoDeLista, numerodecimales), numerodecimales, '.', ''));
  });
}
function obtenerproductoporcodigo(){
  var codigoabuscar = $("#codigoabuscar").val();
  var tipooperacion = $("#tipooperacion").val();
  var tipoalta = $("#tipocompra").val();
  $.get(compras_obtener_producto_por_codigo,{codigoabuscar:codigoabuscar,tipoalta:tipoalta}, function(data){
    if(parseInt(data.contarproductos) > 0){
      agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Impuesto, data.SubTotal, data.Existencias, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, data.NombreClaveProducto, data.NombreClaveUnidad, data.CostoDeLista);
    }else{
      msjnoseencontroningunproducto();
    }
  })
}
//funcion que evalua si el codigo agregado existe en la orden de compra
function evaluarcodigoenordencompra(Codigo){
  var arraycodigosdetallesordencompra = $("#arraycodigosdetallesordencompra").val();
  var codigoencontrado = arraycodigosdetallesordencompra.search(Codigo);
  if(codigoencontrado == -1){
    var codigocorrecto = false;
  }else{
    var codigocorrecto = true;
  }
  return codigocorrecto;
}
//función que evalua si la partida que quieren ingresar ya existe o no en el detalle de la orden de compra
function evaluarproductoexistente(Codigo){
  var sumaiguales=0;
  var sumadiferentes=0;
  var sumatotal=0;
  $("tr.filasproductos").each(function () {
      var codigoproducto = $('.codigoproductopartida', this).val();
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
      //$('.descuentopesospartida', this).val('0.'+numerocerosconfigurados);
      //$('.descuentoporcentajepartida',this).val('0.'+numerocerosconfigurados);
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
      var descuentoporcentajepartida = new Decimal(multiplicaciondescuentoporcentajepartida/importepartida);
      $('.descuentoporcentajepartida', this).val(number_format(round(descuentoporcentajepartida, numerodecimales), numerodecimales, '.', ''));
      calculartotalesfilas(fila);
      calculartotal();
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
      var descuentopesospartida = new Decimal(multiplicaciondescuentopesospartida/100);
      $('.descuentopesospartida', this).val(number_format(round(descuentopesospartida, numerodecimales), numerodecimales, '.', ''));
      calculartotalesfilas(fila);
      calculartotal();
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
    ieps = new Decimal(ieps).plus($(".trasladoiepspesospartida", this).val());
    retencioniva = new Decimal(retencioniva).plus($(".retencionivapesospartida", this).val());
    retencionisr = new Decimal(retencionisr).plus($(".retencionisrpesospartida", this).val());
    retencionieps = new Decimal(retencionieps).plus($(".retencioniepspesospartida", this).val());
  });
  //IVA Y TOTAL
  let ivaAux = parseFloat(iva.toFixed(parseInt(numerodecimales)))
  let totalRound = 0
//   if (iva > 0) {
//     ivaAux = subtotal * 0.16;
//   }
  totalRound = parseFloat(subtotal.toFixed(parseInt(numerodecimales))) + ivaAux
  $("#importe").val(number_format(round(importe, numerodecimales), numerodecimales, '.', ''));
  $("#descuento").val(number_format(round(descuento, numerodecimales), numerodecimales, '.', ''));
  $("#subtotal").val(number_format(round(subtotal, numerodecimales), numerodecimales, '.', ''));
  $("#iva").val(number_format(round(ivaAux, numerodecimales), numerodecimales, '.', ''));
  $("#total").val(number_format(round(totalRound, numerodecimales), numerodecimales, '.', ''));
  $("#ieps").val(number_format(round(ieps, numerodecimales), numerodecimales, '.', ''));
  $("#retencioniva").val(number_format(round(retencioniva, numerodecimales), numerodecimales, '.', ''));
  $("#retencionisr").val(number_format(round(retencionisr, numerodecimales), numerodecimales, '.', ''));
  $("#retencionieps").val(number_format(round(retencionieps, numerodecimales), numerodecimales, '.', ''));
  //esconder tr's
  /*if(parseFloat(ieps) == 0){
    $("#trieps").hide();
  }else{
    $("#trieps").show();
  }
  if(parseFloat(retencioniva) == 0){
    $("#trretencioniva").hide();
  }else{
    $("#trretencioniva").show();
  }
  if(parseFloat(retencionisr) == 0){
    $("#trretencionisr").hide();
  }else{
    $("#trretencionisr").show();
  }
  if(parseFloat(retencionieps) == 0){
    $("#trretencionieps").hide();
  }else{
    $("#trretencionieps").show();
  }*/
  //machar totales factura proveedor y orden de compra
  var totalxml = $("#totalxml").val();
  if(parseFloat(totalRound) > parseFloat(totalxml)){
    var diferencia = new Decimal(totalRound).minus(totalxml);
    $("#diferenciafacturaproveedor").html("Diferencia del total por : $ "+number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
  }else if(parseFloat(totalRound) < parseFloat(totalxml)){
    var diferencia = new Decimal(totalxml).minus(totalRound);
    $("#diferenciafacturaproveedor").html("Diferencia del total por : $ -"+number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
  }else if(parseFloat(totalRound) == parseFloat(totalxml)){
    $("#diferenciafacturaproveedor").html("");
  }
  $("#diferenciatotales").val(number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
}
//validar que la fecha de la compra sea la misma que la fecha de emision de la factura del proveedor y validar que la fecha de la compra solo sea del mismo mes y año en curso
function validarmescompra(){
  /*var fechaxml = new Date($("#fechaemitida").val());
  var dia = ("0" + fechaxml.getDate()).slice(-2);
  var mes = ("0" + (fechaxml.getMonth() + 1)).slice(-2);
  var fechafacturasinhoras = fechaxml.getFullYear()+"-"+(mes)+"-"+(dia) ;
  var fechacompra = new Date($("#fecha").val());
  var diacompra = ("0" + fechacompra.getDate()).slice(-2);
  var mescompra = ("0" + (fechacompra.getMonth() + 1)).slice(-2);
  var fechacomprasinhoras = fechacompra.getFullYear()+"-"+(mescompra)+"-"+(diacompra) ;
  if(fechafacturasinhoras != fechacomprasinhoras){
    $("#fecha").val("");
    msj_errorfechaigualafechafactura();
  }*/
}
//agregar una fila en la tabla de precios productos
var contadorproductos=0;
var contadorfilas = 0;
function agregarfilaproducto(Codigo, Producto, Unidad, Costo, Impuesto, SubTotal, Existencias, tipooperacion, Insumo, ClaveProducto, ClaveUnidad, NombreClaveProducto, NombreClaveUnidad, CostoDeLista){
  $('.page-loader-wrapper').css('display', 'block');
  var codigocorrecto = evaluarcodigoenordencompra(Codigo);
  if(codigocorrecto == true){
    var result = evaluarproductoexistente(Codigo);
    if(result == false){
        var multiplicacioncostoimpuesto =  new Decimal(SubTotal).times(Impuesto);
        var ivapesos = new Decimal(multiplicacioncostoimpuesto/100);
        var total = new Decimal(SubTotal).plus(ivapesos);
        var preciopartida = Costo;
        var orden = $("#orden").val();
        var tipo = "alta";
        var fila=
                  '<tr class="filasproductos" id="filaproducto'+contadorproductos+'">'+
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('+contadorproductos+')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'+Codigo+'</b></td>'+
                    '<td class="tdmod"><textarea rows="1" type="text" class="form-control inputnextdet nombreproductopartida" name="nombreproductopartida[]" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"  style="font-size:10px;">'+Producto+'</textarea></td>'+
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'+Unidad+'" readonly data-parsley-length="[1, 5]">'+Unidad+'</td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm porsurtirpartida"  name="porsurtirpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'+preciopartida+'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" ></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'+Impuesto+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'+number_format(round(ivapesos, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'+number_format(round(total, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm ordenpartida" name="ordenpartida[]" value="'+orden+'" readonly data-parsley-length="[1, 20]" autocomplete="off"></td>'+
                    '<td class="tdmod">'+
                        '<div class="row divorinputmodxl">'+
                            '<div class="col-xs-2 col-sm-2 col-md-2">'+
                                '<div class="btn bg-blue btn-xs waves-effect btnlistardepartamentos" data-toggle="tooltip" title="Ver Departamentos" onclick="listardepartamentos('+contadorfilas+');" ><i class="material-icons">remove_red_eye</i></div>'+
                            '</div>'+
                            '<div class="col-xs-10 col-sm-10 col-md-10">'+
                                '<input type="hidden" class="form-control divorinputmodsm numerodepartamentopartida" name="numerodepartamentopartida[]" readonly><input type="text" class="form-control inputnextdet divorinputmodmd departamentopartida" name="departamentopartida[]" readonly>'+
                            '</div>'+
                        '</div>'+
                    '</td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'+
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
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm costocatalogopartida" name="costocatalogopartida[]" value="'+Costo+'"  readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm costoingresadopartida" name="costoingresadopartida[]" readonly></td>'+
                  '</tr>';
        contadorproductos++;
        contadorfilas++;
        $("#tablaproductoscompras").append(fila);
        mostrarformulario();
        comprobarfilas();
        calculartotal();
        $("#codigoabuscar").val("");
        $('.page-loader-wrapper').css('display', 'none');
        //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
        $(".inputnextdet").keyup(function (e) {
          //recomentable para mayor compatibilidad entre navegadores.
          var code = (e.keyCode ? e.keyCode : e.which);
          var index = $(this).index(".inputnextdet");
          switch(code){
            case 13:
              //$(".inputnextdet").eq(index + 1).focus().select();
              break;
            case 39:
              $(".inputnextdet").eq(index + 1).focus().select();
              break;
            case 37:
              $(".inputnextdet").eq(index - 1).focus().select();
              break;
          }
        });
    }else{
        msj_errorproductoyaagregado();
        $('.page-loader-wrapper').css('display', 'none');
    }
  }else{
    msj_errorcodigonoexisteenorden();
    $('.page-loader-wrapper').css('display', 'none');
  }
}
//eliminar una fila en la tabla de precios clientes
function eliminarfila(numerofila){
  var confirmacion = confirm("Esta seguro de eliminar la fila?");
  if (confirmacion == true) {
    $("#filaproducto"+numerofila).remove();
    contadorfilas--;
    contadorproductos--;
    comprobarfilas();
    renumerarfilas();//importante para todos los calculo en el modulo de orden de compra
    calculartotal();
  }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
  var numerofilas = $("#tablaproductoscompras tbody tr").length;
  $("#numerofilas").val(numerofilas);
}
//renumerar las filas de la orden de compra
function renumerarfilas(){
  var lista;
  var tipo = "alta";
  //renumerar cantidadpartida
  lista = document.getElementsByClassName("cantidadpartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar preciopartida
  lista = document.getElementsByClassName("preciopartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar descuentopesospartida
  lista = document.getElementsByClassName("descuentopesospartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar iepsporcentajepartida
  lista = document.getElementsByClassName("iepsporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar ivaporcentajepartida
  lista = document.getElementsByClassName("ivaporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  // renumerar retencionivaporcentajepartida
  lista = document.getElementsByClassName("retencionivaporcentajepartida");
  for (var i = 0; i < lista.length; i++){
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  // renumerar retencionisrporcentajepartida
  lista = document.getElementsByClassName("retencionisrporcentajepartida");
  for (var i = 0; i < lista.length; i++){
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  // renumerar retencioniepsporcentajepartida
  lista = document.getElementsByClassName("retencioniepsporcentajepartida");
  for (var i = 0; i < lista.length; i++){
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  // renumerar btnlistardepartamentos
  lista = document.getElementsByClassName("btnlistardepartamentos");
  for (var i = 0; i < lista.length; i++){
    lista[i].setAttribute("onclick", "listardepartamentos("+i+')');
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
//alta clientes
function alta(tipoalta){
  $("#titulomodal").html('Alta Compra ' + tipoalta);
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();

  //Variables filas xml
  let solicitarxml = $('#solicitarxml').val()
  let importexml = ''
  let descuentoxml = ''
  let subtotalxml = ''
  let ivaxml = ''
  let totalxml = ''
  let headOT = ''

  //Valida si debe Contener OT

  if(tipoalta == 'PRODUCTOS') headOT = '<th class="" style="background-color: #F70707">OT</th>'


  if(solicitarxml){
    importexml = '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importexml" id="importexml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'
    descuentoxml = '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentoxml" id="descuentoxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'
    subtotalxml = '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotalxml" id="subtotalxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'
    ivaxml = '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ivaxml" id="ivaxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'
    totalxml = '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalxml" id="totalxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'
  }else{
    importexml = '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importexml" id="importexml" value="0.'+numerocerosconfigurados+'" required readonly></td>'
    descuentoxml = '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentoxml" id="descuentoxml" value="0.'+numerocerosconfigurados+'" required readonly></td>'
    subtotalxml = '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotalxml" id="subtotalxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'
    ivaxml = '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ivaxml" id="ivaxml" value="0.'+numerocerosconfigurados+'" required readonly></td>'
    totalxml = '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalxml" id="totalxml" value="0.'+numerocerosconfigurados+'" required readonly></td>'
  }

  //formulario alta
  var tabs =    '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#compratab" data-toggle="tab">Compra</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#emisorreceptortab" data-toggle="tab">Emisor, Receptor</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="compratab">'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Compra <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                                        '<input type="text" class="form-control inputnextdet" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'"  required readonly data-parsley-length="[1, 10]">'+
                                        '<input type="hidden" class="form-control" name="uuid" id="uuid" readonly required data-parsley-length="[1, 50]">'+
                                        '<input type="hidden" class="form-control" name="diferenciatotales" id="diferenciatotales" readonly required>'+
                                        '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" value="0" readonly>'+
                                        '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" value="alta" readonly>'+
                                        '<input type="hidden" class="form-control" name="arraycodigosdetallesordencompra" id="arraycodigosdetallesordencompra" readonly>'+
                                        '<input type="hidden" class="form-control" name="solicitarxml" id="solicitarxml" readonly>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Plazo Días (proveedor)</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="plazo" id="plazo"  required onkeyup="tipoLetra(this);" autocomplete="off">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Fecha</label>'+
                                        '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required style="min-width:95%;" data-parsley-excluded="true" onkeydown="return false">'+
                                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                                        '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Emitida</label>'+
                                        '<input type="datetime-local" class="form-control" name="fechaemitida" id="fechaemitida" data-parsley-excluded="true" onkeydown="return false" required readonly>'+
                                        '<input type="hidden" class="form-control" name="fechatimbrado" id="fechatimbrado" >'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Proveedor <span class="label label-danger" id="textonombreproveedor"></span> </label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" id="btnobtenerproveedores" onclick="obtenerproveedores()" >Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnextdet" name="numeroproveedor" id="numeroproveedor" required data-parsley-type="integer" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="numeroproveedoranterior" id="numeroproveedoranterior" required data-parsley-type="integer">'+
                                                        '<input type="hidden" class="form-control" name="proveedor" id="proveedor" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Almacen <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td hidden>'+
                                                    '<div class="btn bg-blue waves-effect" id="btnobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnextdet" name="numeroalmacen" id="numeroalmacen" required readonly data-parsley-type="integer" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required readonly data-parsley-type="integer">'+
                                                        '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Remisión</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="remision" id="remision" onkeyup="tipoLetra(this)" data-parsley-length="[1, 20]" autocomplete="off">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Factura</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="factura" id="factura" required onkeyup="tipoLetra(this)" data-parsley-length="[1, 20]" autocomplete="off">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-2">'+
                                        '<label>Tipo</label>'+
                                        '<select name="tipo" id="tipo" class="form-control select2" style="width:100% !important;" required readonly>'+
                                        '</select>'+
                                        '<input type="hidden" class="form-control" name="tipocompra" id="tipocompra"  >'+
                                    '</div>'+
                                    '<div class="col-md-2">'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<label>Moneda</label>'+
                                                    '<select name="moneda" id="moneda" class="form-control select2" style="width:100% !important;" required data-parsley-length="[1, 5]">'+
                                                        '<option value="MXN" selected>MXN</option>'+
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
                                    '<div class="col-md-2">'+
                                        '<label>Cargar Ordenes de Compra</label>'+
                                        '<div class="btn btn-block bg-blue waves-effect" id="btnlistarordenesdecompra" onclick="listarordenesdecompra()">VER ORDENES DE COMPRA</div>'+
                                        '<input type="hidden" class="form-control" name="orden" id="orden" required readonly>'+
                                    '</div>'+
                                    '<div class="col-md-3" id="busquedaordenestrabajo">'+
                                      '<label>Orden Trabajo <span class="label label-danger" id="textonombreordentrabajo"></span></label>'+
                                      '<table class="col-md-12">'+
                                        '<tr>'+
                                          '<td>'+
                                            '<div class="form-line">'+
                                              '<input type="text" class="form-control" name="ordentrabajo" id="ordentrabajo" readonly onkeyup="tipoLetra(this);" autocomplete="off">'+
                                              '<input type="hidden" class="form-control" name="ordentrabajoanterior" id="ordentrabajoanterior" readonly>'+
                                            '</div>'+
                                          '</td>'+
                                        '</tr>'+
                                      '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3" id="divbuscarcodigoproducto" hidden>'+
                                      '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                                      '<table class="col-md-12">'+
                                        '<tr>'+
                                          '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver Productos</div>'+
                                          '</td>'+
                                          '<td>'+
                                            '<div class="form-line">'+
                                              '<input type="text" class="form-control inputnextdet" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">'+
                                            '</div>'+
                                          '</td>'+
                                        '</tr>'+
                                      '</table>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div role="tabpanel" class="tab-pane fade" id="emisorreceptortab">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Emisor R.F.C.</label>'+
                                        '<input type="text" class="form-control inputnexttabem" name="emisorrfc" id="emisorrfc"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                        '<input type="hidden" class="form-control" name="emisorrfcdb" id="emisorrfcdb"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Emisor Nombre</label>'+
                                        '<input type="text" class="form-control inputnexttabem" name="emisornombre" id="emisornombre" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="emisornombredb" id="emisornombredb" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Receptor R.F.C.</label>'+
                                        '<input type="text" class="form-control inputnexttabem" name="receptorrfc" id="receptorrfc"  value="'+rfcreceptor+'" required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                        '<input type="hidden" class="form-control" name="receptorrfcxml" id="receptorrfcxml" value="'+rfcreceptor+'" required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Receptor Nombre</label>'+
                                        '<input type="text" class="form-control inputnexttabem" name="receptornombre" id="receptornombre" value="'+nombrereceptor+'" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="receptornombrexml" id="receptornombrexml" value="'+nombrereceptor+'" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
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
                                '<a href="#productostab" data-toggle="tab">Productos</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                                '<div class="row">'+
                                    '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                                        '<table id="tablaproductoscompras" class="table table-bordered tablaproductoscompras">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                '<th class="'+background_tables+'">#</th>'+
                                                '<th class="'+background_tables+'"><div style="width:100px !important;">Código</div></th>'+
                                                '<th class="customercolortheadth"><div style="width:400px !important;">Descripción</div></th>'+
                                                '<th class="'+background_tables+'">Unidad</th>'+
                                                '<th class="'+background_tables+'">Por Surtir</th>'+
                                                headOT+
                                                '<th class="customercolortheadth">Cantidad</th>'+
                                                '<th class="customercolortheadth">Precio $</th>'+
                                                '<th class="'+background_tables+'">Importe $</th>'+
                                                '<th class="customercolortheadth">Dcto %</th>'+
                                                '<th class="customercolortheadth">Dcto $</th>'+
                                                '<th class="'+background_tables+'">Importe Descuento $</th>'+
                                                '<th class="customercolortheadth">Ieps %</th>'+
                                                '<th class="'+background_tables+'">Traslado Ieps $</th>'+
                                                '<th class="'+background_tables+'">SubTotal $</th>'+
                                                '<th class="customercolortheadth">Iva %</th>'+
                                                '<th class="'+background_tables+'">Traslado Iva $</th>'+
                                                '<th class="customercolortheadth">Retención Iva %</th>'+
                                                '<th class="'+background_tables+'">Retención Iva $</th>'+
                                                '<th class="customercolortheadth">Retención Isr %</th>'+
                                                '<th class="'+background_tables+'">Retención Isr $</th>'+
                                                '<th class="customercolortheadth">Retención Ieps %</th>'+
                                                '<th class="'+background_tables+'">Retención Ieps $</th>'+
                                                '<th class="'+background_tables+'">Total $</th>'+
                                                '<th class="customercolortheadth">Orden</th>'+
                                                '<th class="customercolortheadth">Depto</th>'+
                                                '<th class="customercolortheadth" hidden>Precio Moneda $</th>'+
                                                '<th class="customercolortheadth" hidden>Descuento $</th>'+
                                                '<th class="customercolortheadth">ClaveProducto</th>'+
                                                '<th class="'+background_tables+'">Nombre ClaveProducto</th>'+
                                                '<th class="customercolortheadth">ClaveUnidad</th>'+
                                                '<th class="'+background_tables+'">Nombre ClaveUnidad</th>'+
                                                '<th class="'+background_tables+'">Costo Catálogo</th>'+
                                                '<th class="'+background_tables+'">Costo Ingresado</th>'+
                                                '</tr>'+
                                            '</thead>'+
                                            '<tbody>'+
                                            '</tbody>'+
                                        '</table>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                  '<div class="col-md-6">'+
                                      '<label>Observaciones</label>'+
                                      '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" rows="5" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
                                  '</div>'+
                                  '<div class="col-md-3 col-md-offset-3">'+
                                        '<table class="table table-striped table-hover">'+
                                            '<tr>'+
                                                '<td class="tdmod">Importe</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                importexml+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">Descuento</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                descuentoxml+
                                            '</tr>'+
                                            '<tr id="trieps" hidden>'+
                                              '<td class="tdmod">Ieps</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ieps" id="ieps" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iepsxml" id="iepsxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">SubTotal</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                subtotalxml+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">Iva</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                ivaxml+
                                            '</tr>'+
                                            '<tr id="trretencioniva" hidden>'+
                                              '<td class="tdmod">Retención Iva</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencioniva" id="retencioniva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionivaxml" id="retencionivaxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trretencionisr" hidden>'+
                                              '<td class="tdmod">Retención Isr</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionisr" id="retencionisr" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionisrxml" id="retencionisrxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trretencionieps" hidden>'+
                                              '<td class="tdmod">Retención Ieps</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionieps" id="retencionieps" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencioniepsxml" id="retencioniepsxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">Total</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                totalxml+
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
                        '</div>'+
                    '</div>'+
                '</div>';
  $("#tabsform").html(tabs);
  //colocar autocomplette off  todo el formulario
  $(".form-control").attr('autocomplete','off');
  obtenultimonumero();
  //verificar que tipo de alta se realizara genera existencias
  switch (tipoalta) {
    case 'GASTOS':
      var generaexistencias = 0;
      break;
    case 'TOT':
      var generaexistencias = 0;
      break;
    default:
      var generaexistencias = 1;
  }
  obtenertiposordenescompra(tipoalta, generaexistencias);
  asignarfechaactual();
  //ocultar buscador de productos
  mostrarbuscadorcodigoproducto();
  //activar los input select
  //$("#tipo").select2({disabled: true});
  $("#tipo").select2();
  $("#moneda").select2();
  //reiniciar contadores
  contadorproductos=0;
  contadorfilas = 0;
  //activar busqueda de codigos
  $("#codigoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerproductoporcodigo();
    }
  });
  //busquedas seleccion
  //activar busqueda
  $('#numeroproveedor').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerproveedorpornumero();
    }
  });
  //regresar numero
  $('#numeroproveedor').on('change', function(e) {
    regresarnumeroproveedor();
  });
  //verificar que tipo de alta se realizara y asignaran configuraciones correspondientes
  $("#tipocompra").val(tipoalta);
  switch (tipoalta) {
    case 'GASTOS':
      //desabilitar almacen
      $("#numeroalmacen").val(0).attr('readonly', 'readonly');
      $("#numeroalmacenanterior").val(0).attr('readonly', 'readonly');
      $("#almacen").val(0).attr('readonly', 'readonly');
      $("#btnobteneralmacenes").hide();
      $("#busquedaordenestrabajo").hide();
      break;
    case 'TOT':
      //colocar required a campo orden
      $("#ordentrabajo").attr('required', 'required').addClass('inputnextdet');
      $("#ordentrabajoanterior").attr('required', 'required');
      //desabilitar almacen
      $("#numeroalmacen").val(0).attr('readonly', 'readonly');
      $("#numeroalmacenanterior").val(0).attr('readonly', 'readonly');
      $("#almacen").val(0).attr('readonly', 'readonly');
      $("#btnobteneralmacenes").hide();
      $("#busquedaordenestrabajo").show();
      break;
    default:
      $("#busquedaordenestrabajo").hide();
  }
  //regresar numero
  $('#numeroalmacen').on('change', function(e) {
    regresarnumeroalmacen();
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
  //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB EMISOR RECEPTOR
  $(".inputnexttabem").keypress(function (e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      var index = $(this).index(".inputnexttabem");
      $(".inputnexttabem").eq(index + 1).focus().select();
    }
  });
  //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
  $(".inputnextdet").keyup(function (e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    var index = $(this).index(".inputnextdet");
    switch(code){
      case 13:
        //$(".inputnextdet").eq(index + 1).focus().select();
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
//guardar el registro
$("#btnGuardar").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    var solicitarxml = $("#solicitarxml").val();
    var diferenciatotales = $("#diferenciatotales").val();
    if(diferenciatotales <= 0.02){
      var emisorrfc = $("#emisorrfc").val();
      var emisorrfcdb = $("#emisorrfcdb").val();
      if(emisorrfc != emisorrfcdb){
        msj_errorrfcdistinto();
      }else{
        if(solicitarxml){
            var receptorrfc = $("#receptorrfc").val();
            var receptorrfcxml = $("#receptorrfcxml").val();
            if(receptorrfc == receptorrfcxml  || solicitarxml == 1){
                $("#tipo").prop("disabled", false);
                $('.page-loader-wrapper').css('display', 'block');
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url:compras_guardar,
                    type: "post",
                    dataType: "html",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success:function(data){
                        if(data == 1){
                        msj_erroruuidexistente();
                        $('.page-loader-wrapper').css('display', 'none');
                        }else{
                        msj_datosguardadoscorrectamente();
                        limpiar();
                        ocultarmodalformulario();
                        limpiarmodales();
                        $('.page-loader-wrapper').css('display', 'none');
                        }
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
            msj_errorrfcreceptordistinto();
            }
        }else{
            $("#tipo").prop("disabled", false);
                $('.page-loader-wrapper').css('display', 'block');
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url:compras_guardar,
                    type: "post",
                    dataType: "html",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success:function(data){
                        if(data == 1){
                        msj_erroruuidexistente();
                        $('.page-loader-wrapper').css('display', 'none');
                        }else{
                        msj_datosguardadoscorrectamente();
                        limpiar();
                        ocultarmodalformulario();
                        limpiarmodales();
                        $('.page-loader-wrapper').css('display', 'none');
                        }
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
        }
      }
    }else{
      msj_errortotalpartidasnocoincide();
    }
  }else{
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});
//modificacion compra
function obtenerdatos(compramodificar){
    let OTHeader = ''
    let inputOT = ''
  $('.page-loader-wrapper').css('display', 'block');
  $.get(compras_obtener_compra,{compramodificar:compramodificar },function(data){
    if(data.movimiento.indexOf('ALMACEN') > -1){
        OTHeader = '<th class="" style="background-color: #F70707">OT</th>'
    }
    $("#titulomodal").html('Modificación Compra --- STATUS : ' + data.compra.Status);
    //formulario modificacion
    var tabs =    '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#compratab" data-toggle="tab">Compra</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#emisorreceptortab" data-toggle="tab">Emisor, Receptor</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="compratab">'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Compra <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b></label>'+
                                        '<input type="text" class="form-control inputnextdet" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                        '<input type="hidden" class="form-control" name="compra" id="compra" required readonly>'+
                                        '<input type="hidden" class="form-control" name="uuid" id="uuid" readonly required data-parsley-length="[1, 50]">'+
                                        '<input type="hidden" class="form-control" name="diferenciatotales" id="diferenciatotales" readonly required>'+
                                        '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                                        '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                                        '<input type="hidden" class="form-control" name="arraycodigosdetallesordencompra" id="arraycodigosdetallesordencompra" readonly>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Plazo Días (proveedor)</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="plazo" id="plazo"  required onkeyup="tipoLetra(this);" autocomplete="off">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Fecha</label>'+
                                        '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required style="min-width:95%;" data-parsley-excluded="true" onkeydown="return false">'+
                                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                                        '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Emitida</label>'+
                                        '<input type="datetime-local" class="form-control" name="fechaemitida" id="fechaemitida" data-parsley-excluded="true" onkeydown="return false" required readonly>'+
                                        '<input type="hidden" class="form-control" name="fechatimbrado" id="fechatimbrado" >'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Proveedor <span class="label label-danger" id="textonombreproveedor"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" id="btnobtenerproveedores" onclick="obtenerproveedores()" style="display:none">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnextdet" name="numeroproveedor" id="numeroproveedor" required data-parsley-type="integer" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="numeroproveedoranterior" id="numeroproveedoranterior" required data-parsley-type="integer">'+
                                                        '<input type="hidden" class="form-control" name="proveedor" id="proveedor" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Almacen <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td hidden>'+
                                                    '<div class="btn bg-blue waves-effect" id="btnobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnextdet" name="numeroalmacen" id="numeroalmacen" required data-parsley-type="integer" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required readonly data-parsley-type="integer">'+
                                                        '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Remisión</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="remision" id="remision" onkeyup="tipoLetra(this)" data-parsley-length="[1, 20]" autocomplete="off">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Factura</label>'+
                                        '<input type="text" class="form-control inputnextdet" name="factura" id="factura" required onkeyup="tipoLetra(this)" data-parsley-length="[1, 20]" autocomplete="off">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-2">'+
                                        '<label>Tipo</label>'+
                                        '<select name="tipo" id="tipo" class="form-control select2" style="width:100% !important;" required readonly>'+
                                        '</select>'+
                                        '<input type="hidden" class="form-control" name="tipocompra" id="tipocompra"  >'+
                                    '</div>'+
                                    '<div class="col-md-2">'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<label>Moneda</label>'+
                                                    '<select name="moneda" id="moneda" class="form-control select2" style="width:100% !important;" required data-parsley-length="[1, 5]">'+
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
                                    '<div class="col-md-2" id="divbtnlistarordenes">'+
                                        '<label>Cargar Ordenes de Compra</label>'+
                                        '<div class="btn bg-blue waves-effect" id="btnlistarordenesdecompra" onclick="listarordenesdecompra()" style="display:none">Ver Ordenes de Compra</div>'+
                                        '<input type="hidden" class="form-control" name="orden" id="orden" required readonly>'+
                                    '</div>'+
                                    '<div class="col-md-3" id="busquedaordenestrabajo">'+
                                      '<label>Orden Trabajo <span class="label label-danger" id="textonombreordentrabajo"></span></label>'+
                                      '<table class="col-md-12">'+
                                        '<tr>'+
                                          '<td>'+
                                            '<div class="form-line">'+
                                              '<input type="text" class="form-control" name="ordentrabajo" id="ordentrabajo" readonly onkeyup="tipoLetra(this);" autocomplete="off">'+
                                              '<input type="hidden" class="form-control" name="ordentrabajoanterior" id="ordentrabajoanterior" readonly>'+
                                            '</div>'+
                                          '</td>'+
                                        '</tr>'+
                                      '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3" id="divbuscarcodigoproducto" hidden>'+
                                      '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                                      '<table class="col-md-12">'+
                                        '<tr>'+
                                          '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver Productos</div>'+
                                          '</td>'+
                                          '<td>'+
                                            '<div class="form-line">'+
                                              '<input type="text" class="form-control inputnextdet" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">'+
                                            '</div>'+
                                          '</td>'+
                                        '</tr>'+
                                      '</table>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div role="tabpanel" class="tab-pane fade" id="emisorreceptortab">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Emisor R.F.C.</label>'+
                                        '<input type="text" class="form-control inputnexttabem" name="emisorrfc" id="emisorrfc"  required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                        '<input type="hidden" class="form-control" name="emisorrfcdb" id="emisorrfcdb"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                      '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Emisor Nombre</label>'+
                                        '<input type="text" class="form-control inputnexttabem" name="emisornombre" id="emisornombre" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="emisornombredb" id="emisornombredb" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Receptor R.F.C.</label>'+
                                        '<input type="text" class="form-control inputnexttabem" name="receptorrfc" id="receptorrfc"  required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                        '<input type="hidden" class="form-control" name="receptorrfcxml" id="receptorrfcxml" required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Receptor Nombre</label>'+
                                        '<input type="text" class="form-control inputnexttabem" name="receptornombre" id="receptornombre" required data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="receptornombrexml" id="receptornombrexml" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
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
                                '<a href="#productostab" data-toggle="tab">Productos</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                                '<div class="row">'+
                                    '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                                        '<table id="tablaproductoscompras" class="table table-bordered tablaproductoscompras">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                '<th class="'+background_tables+'">#</th>'+
                                                '<th class="'+background_tables+'"><div style="width:100px !important;">P</div></th>'+
                                                '<th class="customercolortheadth"><div style="width:400px !important;">Descripción</div></th>'+
                                                '<th class="'+background_tables+'">Unidad</th>'+
                                                '<th class="'+background_tables+'" hidden>Por Surtir</th>'+
                                                OTHeader+
                                                '<th class="customercolortheadth">Cantidad</th>'+
                                                '<th class="customercolortheadth">Precio $</th>'+
                                                '<th class="'+background_tables+'">Importe $</th>'+
                                                '<th class="customercolortheadth">Dcto %</th>'+
                                                '<th class="customercolortheadth">Dcto $</th>'+
                                                '<th class="'+background_tables+'">Importe Descuento $</th>'+
                                                '<th class="customercolortheadth">Ieps %</th>'+
                                                '<th class="'+background_tables+'">Traslado Ieps $</th>'+
                                                '<th class="'+background_tables+'">SubTotal $</th>'+
                                                '<th class="customercolortheadth">Iva %</th>'+
                                                '<th class="'+background_tables+'">Traslado Iva $</th>'+
                                                '<th class="customercolortheadth">Retención Iva %</th>'+
                                                '<th class="'+background_tables+'">Retención Iva $</th>'+
                                                '<th class="customercolortheadth">Retención Isr %</th>'+
                                                '<th class="'+background_tables+'">Retención Isr $</th>'+
                                                '<th class="customercolortheadth">Retención Ieps %</th>'+
                                                '<th class="'+background_tables+'">Retención Ieps $</th>'+
                                                '<th class="'+background_tables+'">Total $</th>'+
                                                '<th class="customercolortheadth">Orden</th>'+
                                                '<th class="customercolortheadth">Depto</th>'+
                                                '<th class="customercolortheadth" hidden>Precio Moneda $</th>'+
                                                '<th class="customercolortheadth" hidden>Descuento $</th>'+
                                                '<th class="customercolortheadth">ClaveProducto</th>'+
                                                '<th class="'+background_tables+'">Nombre ClaveProducto</th>'+
                                                '<th class="customercolortheadth">ClaveUnidad</th>'+
                                                '<th class="'+background_tables+'">Nombre ClaveUnidad</th>'+
                                                '<th class="'+background_tables+'">Costo Catálogo</th>'+
                                                '<th class="'+background_tables+'">Costo Ingresado</th>'+
                                                '</tr>'+
                                            '</thead>'+
                                            '<tbody>'+
                                            '</tbody>'+
                                        '</table>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                  '<div class="col-md-6">'+
                                      '<label>Observaciones</label>'+
                                      '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" rows="5" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
                                  '</div>'+
                                  '<div class="col-md-3 col-md-offset-3">'+
                                        '<table class="table table-striped table-hover">'+
                                            '<tr>'+
                                                '<td class="tdmod">Importe</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importexml" id="importexml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">Descuento</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentoxml" id="descuentoxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trieps" hidden>'+
                                              '<td class="tdmod">Ieps</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ieps" id="ieps" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iepsxml" id="iepsxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">SubTotal</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotalxml" id="subtotalxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">Iva</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ivaxml" id="ivaxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trretencioniva" hidden>'+
                                              '<td class="tdmod">Retención Iva</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencioniva" id="retencioniva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionivaxml" id="retencionivaxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trretencionisr" hidden>'+
                                              '<td class="tdmod">Retención Isr</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionisr" id="retencionisr" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionisrxml" id="retencionisrxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trretencionieps" hidden>'+
                                              '<td class="tdmod">Retención Ieps</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionieps" id="retencionieps" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencioniepsxml" id="retencioniepsxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">Total</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalxml" id="totalxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
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
                        '</div>'+
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //colocar autocomplette off  todo el formulario
    $(".form-control").attr('autocomplete','off');
    //esconder el div del boton listar ordenes
    $("#divbtnlistarordenes").hide();
    $("#periodohoy").val(data.compra.Periodo);
    $("#folio").val(data.compra.Folio);
    $("#serie").val(data.compra.Serie);
    $("#serietexto").html("Serie: "+data.compra.Serie);
    $("#compra").val(data.compra.Compra);
    $("#uuidxml").val(data.compra.UUID);
    $("#uuid").val(data.compra.UUID);
    $("#plazo").val(data.compra.Plazo);
    $("#fecha").val(data.fecha).attr('min', data.fechasdisponiblesenmodificacion.fechamin).attr('max', data.fechasdisponiblesenmodificacion.fechamax);
    $("#fechaemitida").val(data.fechaemitida);
    $("#fechatimbrado").val(data.fechatimbrado);
    $("#ordentrabajo").val(data.compra.OrdenTrabajo);
    $("#ordentrabajoanterior").val(data.compra.OrdenTrabajo);
    $("#proveedor").val(data.proveedor.Nombre);
    if(data.proveedor.Nombre != null){
      $("#textonombreproveedor").html(data.proveedor.Nombre.substring(0, 40));
    }
    $("#numeroproveedor").val(data.proveedor.Numero);
    $("#numeroproveedoranterior").val(data.proveedor.Numero);
    switch (data.compra.Tipo){
      case 'GASTOS':
        //desabilitar almacen
        $("#numeroalmacen").val(0).attr('readonly', 'readonly');
        $("#numeroalmacenanterior").val(0).attr('readonly', 'readonly');
        $("#almacen").val(0).attr('readonly', 'readonly');
        $("#btnobteneralmacenes").hide();
        $("#busquedaordenestrabajo").hide();
        break;
      case 'CAJA CHICA':
        if(data.compra.Almacen > 0){
          $("#almacen").val(data.almacen.Nombre);
          if(data.almacen.Nombre != null){
            $("#textonombrealmacen").html(data.almacen.Nombre.substring(0, 40));
          }
          $("#numeroalmacen").val(data.almacen.Numero);
          $("#numeroalmacenanterior").val(data.almacen.Numero);
        }else{
          //desabilitar almacen
          $("#numeroalmacen").val(0).attr('readonly', 'readonly');
          $("#numeroalmacenanterior").val(0).attr('readonly', 'readonly');
          $("#almacen").val(0).attr('readonly', 'readonly');
          $("#btnobteneralmacenes").hide();
          $("#busquedaordenestrabajo").hide();
        }
        break;
      case 'TOT':
        //colocar required a campo orden
        $("#ordentrabajo").attr('required', 'required').addClass('inputnextdet');
        $("#ordentrabajoanterior").attr('required', 'required');
        //desabilitar almacen
        $("#numeroalmacen").val(0).attr('readonly', 'readonly');
        $("#numeroalmacenanterior").val(0).attr('readonly', 'readonly');
        $("#almacen").val(0).attr('readonly', 'readonly');
        $("#btnobteneralmacenes").hide();
        $("#busquedaordenestrabajo").show();
        break;
      default:
        $("#almacen").val(data.almacen.Nombre);
        if(data.almacen.Nombre != null){
          $("#textonombrealmacen").html(data.almacen.Nombre.substring(0, 40));
        }
        $("#numeroalmacen").val(data.almacen.Numero);
        $("#numeroalmacenanterior").val(data.almacen.Numero);
    }
    $("#remision").val(data.compra.Remision);
    $("#factura").val(data.compra.Factura);
    $("#moneda").val(data.compra.Moneda).change();
    $("#pesosmoneda").val(data.tipocambio);
    $("#observaciones").val(data.compra.Obs);
    $("#emisorrfc").val(data.compra.EmisorRfc);
    $("#emisornombre").val(data.compra.EmisorNombre);
    $("#receptorrfc").val(data.compra.ReceptorRfc);
    $("#receptornombre").val(data.compra.ReceptorNombre);
    $("#emisorrfcdb").val(data.compra.EmisorRfc);
    $("#emisornombredb").val(data.compra.EmisorNombre);
    $("#receptorrfcxml").val(data.compra.ReceptorRfc);
    $("#receptornombrexml").val(data.compra.ReceptorNombre);
    //totales compra
    $("#importe").val(number_format(data.compra.Importe, numerodecimales, '.', ''));
    $("#descuento").val(number_format(data.compra.Descuento, numerodecimales, '.', ''));
    $("#ieps").val(data.ieps);
    $("#subtotal").val(number_format(data.compra.SubTotal, numerodecimales, '.', ''));
    $("#iva").val(number_format(data.compra.Iva, numerodecimales, '.', ''));
    $("#retencioniva").val(data.ivaretencion);
    $("#retencionisr").val(data.isrretencion);
    $("#retencionieps").val(data.iepsretencion);
    $("#total").val(number_format(data.compra.Total, numerodecimales, '.', ''));
    //totales xml
    $("#importexml").val(number_format(data.compra.Importe, numerodecimales, '.', ''));
    $("#descuentoxml").val(number_format(data.compra.Descuento, numerodecimales, '.', ''));
    $("#iepsxml").val(data.ieps);
    $("#subtotalxml").val(number_format(data.compra.SubTotal, numerodecimales, '.', ''));
    $("#ivaxml").val(number_format(data.compra.Iva, numerodecimales, '.', ''));
    $("#retencionivaxml").val(data.ivaretencion);
    $("#retencionisrxml").val(data.isrretencion);
    $("#retencioniepsxml").val(data.iepsretencion);
    $("#totalxml").val(number_format(data.compra.Total, numerodecimales, '.', ''));
    //detalles
    $("#tablaproductoscompras tbody").html(data.filasdetallescompra);
    $("#numerofilas").val(data.numerodetallescompra);
    $("#arraycodigosdetallesordencompra").val(data.arraycodigosdetallesordencompra);
    //colocar valores a contadores
    contadorproductos = data.contadorproductos;
    contadorfilas = data.contadorfilas;
    //mostrar el buscador de productos
    mostrarbuscadorcodigoproducto();
    //busquedas seleccion
    //activar busqueda de codigos
    $("#codigoabuscar").keypress(function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          obtenerproductoporcodigo();
        }
    });
    //regresar numero
    $('#numeroproveedor').on('change', function(e) {
      regresarnumeroproveedor();
    });
    //regresar numero
    $('#numeroalmacen').on('change', function(e) {
      regresarnumeroalmacen();
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
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB EMISOR RECEPTOR
    $(".inputnexttabem").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnexttabem");
        $(".inputnexttabem").eq(index + 1).focus().select();
      }
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnextdet").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnextdet");
      switch(code){
        case 13:
          //$(".inputnextdet").eq(index + 1).focus().select();
          break;
        case 39:
          $(".inputnextdet").eq(index + 1).focus().select();
          break;
        case 37:
          $(".inputnextdet").eq(index - 1).focus().select();
          break;
      }
    });
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //se deben asignar los valores a los contadores para que las sumas resulten correctas
    obtenertiposordenescompra(data.compra.Tipo, data.compra.Almacen);
    seleccionartipocompra(data);
  }).fail( function() {
    msj_errorajax();
    $('.page-loader-wrapper').css('display', 'none');
  })
}
async function seleccionartipocompra(data){
  await retraso();
  $("#tipo").val(data.compra.Tipo).change();
  //calculartotal();
  //$("#tipo").select2({disabled: true});
  $("#tipo").select2();
  $("#moneda").select2();
  setTimeout(function(){$("#folio").focus();},500);
  mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
  $('.page-loader-wrapper').css('display', 'none');
}
//revisar si hay existencias de la partida en el almacen
function revisarexistenciasalmacen(fila){
  var cuentaFilas = 0;
  var cantidadoperacionaritmetica = 0;
  $("tr.filasproductos").each(function () {
    if(fila === cuentaFilas){
      var folio = $("#folio").val();
      var serie = $("#serie").val();
      var cantidadinicialpartida = $(".cantidadinicialpartida", this).val();
      var cantidadpartida = $(".cantidadpartida", this).val();
      if(parseFloat(cantidadpartida) > parseFloat(cantidadinicialpartida)){
        $(".operacionaritmetica", this).val("suma");
        cantidadoperacionaritmetica = new Decimal(cantidadpartida).minus(cantidadinicialpartida);
        $(".cantidadoperacionaritmetica", this).val(number_format(round(cantidadoperacionaritmetica, numerodecimales), numerodecimales, '.', ''));
        //esconder error en existencias
        $(".cantidaderrorexistencias", this).css('display','none');
        $(".cantidaderrorexistencias", this).html("");
        $(".cantidadincorrecta", this).val(0);
      }else if(parseFloat(cantidadpartida) < parseFloat(cantidadinicialpartida)){
        $(".operacionaritmetica", this).val("resta");
        cantidadoperacionaritmetica = new Decimal(cantidadinicialpartida).minus(cantidadpartida);
        $(".cantidadoperacionaritmetica", this).val(number_format(round(cantidadoperacionaritmetica, numerodecimales), numerodecimales, '.', ''));
        var almacen = $("#numeroalmacen").val();
        var codigopartida = $(".codigoproductopartida", this).val();
        var compra = $("#compra").val();
        comprobarexistenciaspartida(almacen, codigopartida, folio, serie, compra, cantidadpartida).then(nuevaexistencia=>{
          if(parseFloat(cantidadoperacionaritmetica) > parseFloat(nuevaexistencia)){
            //mostrar error en existencias
            $(".cantidaderrorexistencias", this).css('display','block');
            $(".cantidaderrorexistencias", this).html("Error el almacen no cuenta con existencias suficientes");
            $(".cantidadincorrecta", this).val(1);
          }else{
            //esconder error en existencias
            $(".cantidaderrorexistencias", this).css('display','none');
            $(".cantidaderrorexistencias", this).html("");
            $(".cantidadincorrecta", this).val(0);
          }
        })
      }else{
        $(".operacionaritmetica", this).val("");
        $(".cantidadoperacionaritmetica", this).val(0);
        //esconder error en existencias
        $(".cantidaderrorexistencias", this).css('display','none');
        $(".cantidaderrorexistencias", this).html("");
        $(".cantidadincorrecta", this).val(0);
      }
    }
    cuentaFilas++;
  });
  //revisar si se mostrara o ocultara el boton de guardar
  var tipooperacion = $("#tipooperacion").val();
  cantidadesinsuficientesalmacen(tipooperacion);
}
//funcion que revisa si se mostrara o ocultara el boton de guardar
async function cantidadesinsuficientesalmacen(tipooperacion){
  await retraso();
  var cantidadincorrecta = 0;
  $("tr.filasproductos").each(function () {
    if($(".cantidadincorrecta", this).val() == 1){
      cantidadincorrecta++;
    }
  });
  var folio = $("#folio").val();
  var serie = $("#serie").val();
  var compra = folio+"-"+serie;
  $.get(compras_obtener_valor_modificacionpermitida, {compra:compra}, function(modificacionpermitida){
    if(modificacionpermitida == 1){
      if(cantidadincorrecta == 0 && tipooperacion == 'alta'){
        $("#btnGuardar").show();
        $("#btnGuardarModificacion").hide();
      }else if(cantidadincorrecta == 0 && tipooperacion == 'modificacion'){
        $("#btnGuardar").hide();
        $("#btnGuardarModificacion").show();
      }else{
        $("#btnGuardar").hide();
        $("#btnGuardarModificacion").hide();
      }
    }
  });
}
//funcion asincrona para buscar existencias de la partida
function comprobarexistenciaspartida(almacen, codigopartida, folio, serie, compra, cantidadpartida){
  return new Promise((ejecuta)=>{
    setTimeout(function(){
      $.get(compras_obtener_existencias_partida,{'almacen':almacen,'codigopartida':codigopartida,'folio':folio,'serie':serie,'compra':compra,'cantidadpartida':cantidadpartida},nuevaexistencia=>{
        return ejecuta(nuevaexistencia);
      })
    },500);
  })
}
//funcion asincrona para buscar existencias de la partida
function comprobarexistenciasenbd(fila, tipo, numeroalmacen, codigo){
  return new Promise((ejecuta)=>{
    setTimeout(function(){
      $.get(compras_obtener_existencias_almacen,{'numeroalmacen':numeroalmacen,'codigo':codigo},existencias=>{
        return ejecuta(existencias);
      })
    },500);
  })
}
//guardar modificación
$("#btnGuardarModificacion").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    var diferenciatotales = $("#diferenciatotales").val();
    if(diferenciatotales <= 0.02){
      var emisorrfc = $("#emisorrfc").val();
      var emisorrfcdb = $("#emisorrfcdb").val();
      if(emisorrfc == emisorrfcdb){
        var receptorrfc = $("#receptorrfc").val();
        var receptorrfcxml = $("#receptorrfcxml").val();
        if(receptorrfc == receptorrfcxml){
          $("#tipo").prop("disabled", false);
            $('.page-loader-wrapper').css('display', 'block');
            $.ajax({
              headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
              url:compras_guardar_modificacion,
              type: "post",
              dataType: "html",
              data: formData,
              cache: false,
              contentType: false,
              processData: false,
              success:function(data){
                if(data == 1){
                  msj_erroruuidexistente();
                  $('.page-loader-wrapper').css('display', 'none');
                }else{
                  msj_datosguardadoscorrectamente();
                  limpiar();
                  ocultarmodalformulario();
                  limpiarmodales();
                  $('.page-loader-wrapper').css('display', 'none');
                }
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
          msj_errorrfcreceptordistinto();
        }
      }else{
        msj_errorrfcdistinto();
      }
    }else{
      msj_errortotalpartidasnocoincide();
    }
  }else{
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(compradesactivar){
  $.get(compras_verificar_uso_en_modulos,{compradesactivar:compradesactivar}, function(data){
    if(data.Status == 'BAJA'){
      $("#compradesactivar").val(0);
      $("#textomodaldesactivar").html('Error, esta compra ya fue dada de baja');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{
      if(data.resultadofechas != ''){
        $("#compradesactivar").val(0);
        $("#textomodaldesactivar").html('Error solo se pueden dar de baja las compras del mes actual, fecha de la compra: ' + data.resultadofechas);
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        if(data.numerocuentasporpagar > 0){
          $("#compradesactivar").val(0);
          $("#textomodaldesactivar").html('Error esta compra tiene registros de cuentas por pagar con el pago: ' + data.numerocuentaxpagar);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else if(data.numerocontrareciboscompra > 0){
          $("#compradesactivar").val(0);
          $("#textomodaldesactivar").html('Error esta compra tiene registros de contrarecibos con el contrarecibo: ' + data.numerocontrarecibo);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else if(data.numeronotasproveedor > 0){
          $("#compradesactivar").val(0);
          $("#textomodaldesactivar").html('Error esta compra tiene registros de notas crédito proveedor con la nota: ' + data.numeronotaproveedor);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else if(data.numerodetallesconexistenciasinsuficientes > 0){
          $("#compradesactivar").val(0);
          $("#textomodaldesactivar").html('Error el Almacen no cuenta con existencias suficientes para cancelar la compra: ' + compradesactivar);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else{
          $("#compradesactivar").val(compradesactivar);
          $("#textomodaldesactivar").html('Estas seguro de dar de baja la compra? No'+ compradesactivar);
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
      url:compras_alta_o_baja,
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
//obtener movimiento compra
function movimientoscompra(compra){
  $.get(compras_obtener_movimientos_compra, {compra:compra}, function(data){
    $("#modalmovimientoscompra").modal('show');
    $("#filasmovimientos").html(data.filasmovimientos);
  });
}
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento){
  $.get(compras_obtener_datos_envio_email,{documento:documento}, function(data){
    $("#textomodalenviarpdfemail").html("Enviar email Compra No." + documento);
    $("#emaildocumento").val(documento);
    $("#emailde").val(data.emailde);
    $("#emailpara").val(data.emailpara);
    $("#email2cc").val(data.correodefault1enviodocumentos);
    $("#email3cc").val(data.correodefault2enviodocumentos);
    if(data.email2cc != ""){
      $("#correosconcopia").append('<option value="'+data.email2cc+'" selected>'+data.email2cc+'</option>');
    }
    if(data.email3cc != ""){
      $("#correosconcopia").append('<option value="'+data.email3cc+'" selected>'+data.email3cc+'</option>');
    }
    $("#emailasunto").val("COMPRA NO. " + documento +" DE "+ nombreempresa);
    $("#emailmensaje").val("COMPRA NO. " + documento + " DE "+ nombreempresa);
    $(".dropify-clear").trigger("click");
    $("#divadjuntararchivo").show();
    $("#modalenviarpdfemail").modal('show');
    $("#correosconcopia").select2({
        dropdownParent: $('#modalenviarpdfemail'),
        tags: true,
        width: '78.00em',
        tokenSeparators: [',', ' ']
    })
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
      url:compras_enviar_pdfs_email,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        msj_documentoenviadoporemailcorrectamente();
        $("#modalenviarpdfemail").modal('hide');
        $("#correosconcopia").html("");
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
  var columnastablafoliosencontrados =  '<tr>'+
                                          '<th>Documento</th>'+
                                          '<th>Proveedor</th>'+
                                          '<th>Total</th>'+
                                          '<th>Status</th>'+
                                        '</tr>';
  $("#columnastablafoliosencontrados").html(columnastablafoliosencontrados);
  $("#columnasfootertablafoliosencontrados").html(columnastablafoliosencontrados);
  //agregar inputs de busqueda por columna
  $('#tablafoliosencontrados tfoot th').each( function () {
    var titulocolumnatfoot = $(this).text();
    $(this).html( '<input type="text" placeholder="Buscar en columna '+titulocolumnatfoot+'" />' );
  });
  var tablafolenc=$('#tablafoliosencontrados').DataTable({
      keys: true,
      "pageLength": 100,
      'sDom': 't',
      "sScrollX": "100%",
      "sScrollY": "250px",
      processing: true,
      serverSide: true,
      processing: true,
      'language': {
          'loadingRecords': '&nbsp;',
          'processing': '<div class="spinner"></div>'
      },
      ajax: {
          url: compras_buscar_folio_string_like,
          data: function (d) {
            d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'Compra', name: 'Compra', orderable: false, searchable: true },
          { data: 'NombreProveedor', name: 'NombreProveedor', orderable: false, searchable: true },
          { data: 'Total', name: 'Total', orderable: false, searchable: true  },
          { data: 'Status', name: 'Status', orderable: false, searchable: true  },
      ],
      initComplete: function () {
        // Aplicar busquedas por columna
        this.api().columns().every( function () {
          var that = this;
          $('input',this.footer()).on('keyup', function(){
            if(that.search() !== this.value){
              that.search(this.value).draw();
            }
          });
        });
        $(".dataTables_filter").css('display', 'none');
      }
  });
  //modificacion al dar doble click
  $('#tablafoliosencontrados tbody').on('dblclick', 'tr', function () {
    tablafolenc = $("#tablafoliosencontrados").DataTable();
    var data = tablafolenc.row( this ).data();
    agregararraypdf(data.Compra);
  });
}
//generar documento en iframe
function generardocumentoeniframe(Compra){
  var arraypdf = new Array();
  var folios = [Compra];
  arraypdf.push(folios);
  var form_data = new FormData();
  form_data.append('arraypdf', arraypdf);
  form_data.append('tipogeneracionpdf', 0);
  form_data.append('numerodecimalesdocumento', 2);
  form_data.append('imprimirdirectamente', 1);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:compras_generar_pdfs,
    data: form_data,
    type: 'POST',
    contentType: false,
    processData: false,
    success: function (data) {
      $('#pdfiframe').attr("src", urlpdfsimpresionesrapidas+data);
      setTimeout(function(){imprimirdirecto();},500);
    },
    error: function (data) {
      console.log(data);
    }
  });
}
//imprimir documento pdf directamente
function imprimirdirecto(){
  var pdfFrame = window.frames["pdfiframe"];
  pdfFrame.focus();
  pdfFrame.print();
}
//configurar tabla
function configurar_tabla(){
  var checkboxscolumnas = '';
  var optionsselectbusquedas = '';
  var campos = campos_activados.split(",");
  for (var i = 0; i < campos.length; i++) {
    var returncheckboxfalse = '';
    if(campos[i] == 'Compra' || campos[i] == 'Status' || campos[i] == 'Periodo'){
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




