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
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  var serie = $("#serie").val();
  $.get(ordenes_compra_obtener_ultimo_folio,{serie:serie}, function(folio){
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
        url: ordenes_compra_obtener,
        data: function (d) {
            d.periodo = $("#periodo").val();
        }
    },
    "createdRow": function( row, data, dataIndex){
        if( data.Status ==  `POR SURTIR`){ $(row).addClass('bg-red');}
        else if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
        else if( data.Status ==  `BACKORDER`){ $(row).addClass('bg-yellow');}
    },
    columns: campos_tabla,
    "drawCallback": function( data ) {
        $("#sumaimportefiltrado").html(number_format(round(data.json.sumaimporte, numerodecimales), numerodecimales, '.', ''));
        $("#sumadescuentofiltrado").html(number_format(round(data.json.sumadescuento, numerodecimales), numerodecimales, '.', ''));
        $("#sumasubtotalfiltrado").html(number_format(round(data.json.sumasubtotal, numerodecimales), numerodecimales, '.', ''));
        $("#sumaivafiltrado").html(number_format(round(data.json.sumaiva, numerodecimales), numerodecimales, '.', ''));
        $("#sumatotalfiltrado").html(number_format(round(data.json.sumatotal, numerodecimales), numerodecimales, '.', ''));
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
      //tooltip mensajes
      $('[data-toggle="tooltip"]').tooltip({
        container: 'body'
      });
    }
  });
  //modificacion al dar doble click
  $('#tbllistado tbody').on('dblclick', 'tr', function () {
    var data = tabla.row( this ).data();
    obtenerdatos(data.Orden);
  });
}
//realizar en reporte en excel
function descargar_plantilla(){
  $("#btnGenerarPlantilla").attr("href", urlgenerarplantilla);
  $("#btnGenerarPlantilla").click();
}
function seleccionarpartidasexcel(){
  $("#partidasexcel").click();
}
//Cada que se elija un archivo
function cargarpartidasexcel(e) {
  $("#btnenviarpartidasexcel").click();
}
//Agregar respuesta a la datatable
$("#btnenviarpartidasexcel").on('click', function(e){
  e.preventDefault();
  var arraycodigospartidas = [];
  var lista = document.getElementsByClassName("codigoproductopartida");
  for (var i = 0; i < lista.length; i++) {
    arraycodigospartidas.push(lista[i].value);
  }
  var tipoalta = $("#tipoalta").val();
  var partidasexcel = $('#partidasexcel')[0].files[0];
  var form_data = new FormData();
  form_data.append('partidasexcel', partidasexcel);
  form_data.append('contadorproductos', contadorproductos);
  form_data.append('contadorfilas', contadorfilas);
  form_data.append('arraycodigospartidas', arraycodigospartidas);
  form_data.append('tipoalta', tipoalta);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:ordenes_compra_cargar_partidas_excel,
    data: form_data,
    type: 'POST',
    contentType: false,
    processData: false,
    success: function (data) {
      contadorfilas = data.contadorfilas;
      contadorproductos = data.contadorproductos;
      $("#tablaproductodordencompra tbody").append(data.filasdetallesordencompra);
      comprobarfilaspreciosproductos();
      calculartotalordencompra();
      $("#codigoabuscar").val("");
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
    },
    error: function (data) {
      console.log(data);
    }
  });
});
//obtener tipos ordenes de compra
function obtenertiposordenescompra(tipoalta, almacen){
  $.get(ordenes_compra_obtener_tipos_ordenes_compra, {tipoalta:tipoalta, almacen:almacen}, function(select_tipos_ordenes_compra){
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
        url: ordenes_compra_obtener_series_documento
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
    $.get(ordenes_compra_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie}, function(folio){
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
            url: ordenes_compra_obtener_proveedores,
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
      seleccionarproveedor(data.Numero, data.Nombre, data.Plazo);
    });
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
                                    '<div class="table-responsive ">'+
                                        '<table id="tbllistadoalmacen" class="tbllistadoalmacen table table-bordered table-striped table-hover" style="width:100% !important">'+
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
              url: ordenes_compra_obtener_almacenes,
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
function seleccionarproveedor(Numero, Nombre, Plazo){
  var numeroproveedoranterior = $("#numeroproveedoranterior").val();
  var numeroproveedor = Numero;
  if(numeroproveedoranterior != numeroproveedor){
    $("#numeroproveedor").val(Numero);
    $("#numeroproveedoranterior").val(Numero);
    $("#proveedor").val(Nombre);
    if(Nombre != null){
      $("#textonombreproveedor").html(Nombre.substring(0, 60));
    }
    //colocar el plazo del proveedor
    $("#plazo").val(Plazo);
    mostrarformulario();
  }
}
function seleccionaralmacen(Numero, Nombre){
  var numeroalmacenanterior = $("#numeroalmacenanterior").val();
  var numeroalmacen = Numero;
  if(numeroalmacenanterior != numeroalmacen){
    $("#numeroalmacen").val(Numero);
    $("#numeroalmacenanterior").val(Numero);
    $("#almacen").val(Nombre);
    if(Nombre != null){
      $("#textonombrealmacen").html(Nombre.substring(0, 60));
    }
    mostrarformulario();
  }
}
function obtenerordenestrabajo(){
  ocultarformulario();
  var tablaordenes = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Ordenes de Trabajo</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadorden" class="tbllistadorden table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Orden</th>'+
                                                    '<th>Fecha</th>'+
                                                    '<th>Cliente</th>'+
                                                    '<th>Tipo</th>'+
                                                    '<th>Unidad</th>'+
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
    $("#contenidomodaltablas").html(tablaordenes);
    var tots = $('#tbllistadorden').DataTable({
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
          url: ordenes_compra_obtener_ordenes_trabajo
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Orden', name: 'Orden' },
            { data: 'Fecha', name: 'Fecha' },
            { data: 'Cliente', name: 'Cliente' },
            { data: 'Tipo', name: 'Tipo' },
            { data: 'Unidad', name: 'Unidad' }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadorden').DataTable().search( this.value ).draw();
                }
            });
        },
    });
    //seleccionar registro al dar doble click
    $('#tbllistadorden tbody').on('dblclick', 'tr', function () {
      var data = tots.row( this ).data();
      seleccionarordentrabajo(data.Orden, data.Fecha, data.Cliente, data.Tipo, data.Unidad, data.StatusOrden);
    });
}
//obtener datos de remision seleccionada
function seleccionarordentrabajo(Orden, Fecha, Cliente, Tipo, Unidad, StatusOrden){
  var ordentrabajoanterior = $("#ordentrabajoanterior").val();
  var ordentrabajo = Orden;
  if(ordentrabajoanterior != ordentrabajo){
    //colocar datos de orden y required
    $("#ordentrabajo").val(Orden).attr('required', 'required');
    $("#ordentrabajoanterior").val(Orden).attr('required', 'required');
    if(Orden != null){
      $("#textonombreordentrabajo").html(Orden.substring(0, 40));
    }
    mostrarformulario();
  }
}
//obtener por numero
function obtenerproveedorpornumero(){
  var numeroproveedoranterior = $("#numeroproveedoranterior").val();
  var numeroproveedor = $("#numeroproveedor").val();
  if(numeroproveedoranterior != numeroproveedor){
    if($("#numeroproveedor").parsley().isValid()){
      $.get(ordenes_compra_obtener_proveedor_por_numero, {numeroproveedor:numeroproveedor}, function(data){
        $("#numeroproveedor").val(data.numero);
        $("#numeroproveedoranterior").val(data.numero);
        $("#proveedor").val(data.nombre);
        if(data.nombre != null){
          $("#textonombreproveedor").html(data.nombre.substring(0, 60));
        }
        $("#plazo").val(data.plazo);
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
      $.get(ordenes_compra_obtener_almacen_por_numero, {numeroalmacen:numeroalmacen}, function(data){
        $("#numeroalmacen").val(data.numero);
        $("#numeroalmacenanterior").val(data.numero);
        $("#almacen").val(data.nombre);
        if(data.nombre != null){
          $("#textonombrealmacen").html(data.nombre.substring(0, 60));
        }
      })
    }
  }
}
//regresar numero
function regresarnumeroalmacen(){
  var numeroalmacenanterior = $("#numeroalmacenanterior").val();
  $("#numeroalmacen").val(numeroalmacenanterior);
}
//obtener por folio
function obtenerordenporfolio(){
  var ordentrabajoanterior = $("#ordentrabajoanterior").val();
  var ordentrabajo = $("#ordentrabajo").val();
  if(ordentrabajoanterior != ordentrabajo){
    if($("#ordentrabajo").parsley().isValid()){
      $.get(ordenes_compra_obtener_orden_trabajo_por_folio, {ordentrabajo:ordentrabajo}, function(data){
        //colocar datos de orden y required
        $("#ordentrabajo").val(data.orden).attr('required', 'required');
        $("#ordentrabajoanterior").val(data.orden).attr('required', 'required');
        if(data.orden != null){
          $("#textonombreordentrabajo").html(data.orden.substring(0, 40));
        }
        mostrarformulario();
      })
    }
  }
}
//regresar folio
function regresarfolioorden(){
  var ordentrabajoanterior = $("#ordentrabajoanterior").val();
  $("#ordentrabajo").val(ordentrabajoanterior);
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
      url: ordenes_compra_obtener_productos,
      data: function (d) {
        d.codigoabuscar = $("#codigoabuscar").val();
        d.tipooperacion = $("#tipooperacion").val();
        d.numeroalmacen = $("#numeroalmacen").val();
        d.tipoalta = $("#tipoalta").val();
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
    agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, number_format(round(data.Impuesto, numerodecimales), numerodecimales, '.', ''), tipooperacion);
  });
}
function obtenerproductoporcodigo(){
  var codigoabuscar = $("#codigoabuscar").val();
  var tipooperacion = $("#tipooperacion").val();
  var tipoalta = $("#tipoalta").val();
  if(codigoabuscar != ""){
    $.get(ordenes_compra_obtener_producto_por_codigo,{codigoabuscar:codigoabuscar,tipoalta:tipoalta}, function(data){
      if(parseInt(data.contarproductos) > 0){
        agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Impuesto, tipooperacion);
      }else{
        var confirmacion = confirm("El código: " + codigoabuscar + " no existe, desea agregarlo al catalogo de productos?");
        if (confirmacion == true) {
          //formulario alta
          var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                          '<li role="presentation" class="active">'+
                              '<a href="#productotab" data-toggle="tab">Producto</a>'+
                          '</li>'+
                      '</ul>'+
                      '<div class="tab-content">'+
                          '<div role="tabpanel" class="tab-pane fade in active" id="productotab">'+
                              '<div class="row">'+
                                  '<div class="col-md-4">'+
                                      '<label>Código<b style="color:#F44336 !important;">*</b></label>'+
                                      '<input type="text" class="form-control inputnexttabaddprod" name="codigo" id="codigo" required readonly data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+
                                  '</div>'+
                                  '<div class="col-md-4">'+
                                      '<label>Clave Producto<b style="color:#F44336 !important;">*</b></b><span class="label label-danger" id="textonombreclaveproducto"></span></label>'+
                                      '<div class="row">'+
                                          '<div class="col-md-4">'+
                                              '<span class="input-group-btn">'+
                                                  '<div id="buscarclavesproductos" class="btn bg-blue waves-effect" onclick="listarclavesproductos()">Seleccionar</div>'+
                                              '</span>'+
                                          '</div>'+
                                          '<div class="col-md-8">'+
                                              '<div class="form-line">'+
                                                  '<input type="text" class="form-control inputnexttabaddprod" name="claveproducto" id="claveproducto" required data-parsley-length="[1, 20]"  onkeyup="tipoLetra(this);">'+
                                                  '<input type="hidden" class="form-control" name="claveproductoanterior" id="claveproductoanterior" required readonly data-parsley-length="[1, 20]">'+
                                              '</div>'+
                                          '</div>'+
                                      '</div>'+
                                  '</div>'+
                                  '<div class="col-md-4">'+
                                      '<label>Clave Unidad<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreclaveunidad"></span></label>'+
                                      '<div class="row">'+
                                          '<div class="col-md-4">'+
                                              '<span class="input-group-btn">'+
                                                  '<div id="buscarclavesunidades" class="btn bg-blue waves-effect" onclick="listarclavesunidades()">Seleccionar</div>'+
                                              '</span>'+
                                          '</div>'+
                                          '<div class="col-md-8"> '+
                                              '<div class="form-line">'+
                                                  '<input type="text" class="form-control inputnexttabaddprod" name="claveunidad" id="claveunidad" required data-parsley-length="[1, 5]"  onkeyup="tipoLetra(this);">'+
                                                  '<input type="hidden" class="form-control" name="claveunidadanterior" id="claveunidadanterior" required readonly data-parsley-length="[1, 5]">'+
                                              '</div>'+
                                          '</div>'+
                                      '</div>'+
                                  '</div>'+
                              '</div>'+
                              '<div class="row">'+
                                  '<div class="col-md-8">'+
                                      '<label>Producto<b style="color:#F44336 !important;">*</b></label>'+
                                      '<input type="text" class="form-control inputnexttabaddprod" name="producto" id="producto" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);">'+
                                  '</div>'+
                                  '<div class="col-md-4">'+
                                      '<label>Unidad<b style="color:#F44336 !important;">*</b></label>'+
                                      '<input type="text" class="form-control inputnexttabaddprod" name="unidad" id="unidad" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this);">'+
                                  '</div>'+
                              '</div>'+
                              '<div class="row">'+
                                  '<div class="col-md-4">'+
                                      '<label>Marca<b style="color:#F44336 !important;">*</b></b><span class="label label-danger" id="textonombremarca"></span></label>'+
                                      '<div class="row">'+
                                          '<div class="col-md-4">'+
                                              '<span class="input-group-btn">'+
                                                  '<div id="buscarmarcas" class="btn bg-blue waves-effect" onclick="listarmarcas()">Seleccionar</div>'+
                                              '</span>'+
                                          '</div>'+
                                          '<div class="col-md-8">'+
                                              '<div class="form-line">'+
                                                  '<input type="text" class="form-control inputnexttabaddprod" name="marca" id="marca" required  onkeyup="tipoLetra(this);">'+
                                                  '<input type="hidden" class="form-control" name="marcaanterior" id="marcaanterior" required readonly>'+
                                              '</div>'+
                                          '</div>'+
                                      '</div>'+
                                  '</div>'+
                                  '<div class="col-md-4">'+
                                      '<label>Linea<b style="color:#F44336 !important;">*</b></b><span class="label label-danger" id="textonombrelinea"></span></label>'+
                                      '<div class="row">'+
                                          '<div class="col-md-4">'+
                                              '<span class="input-group-btn">'+
                                                  '<div id="buscarlineas" class="btn bg-blue waves-effect" onclick="listarlineas()">Seleccionar</div>'+
                                              '</span>'+
                                          '</div>'+
                                          '<div class="col-md-8">'+
                                              '<div class="form-line">'+
                                                  '<input type="text" class="form-control inputnexttabaddprod" name="linea" id="linea" required  onkeyup="tipoLetra(this);">'+
                                                  '<input type="hidden" class="form-control" name="lineaanterior" id="lineaanterior" required readonly>'+
                                              '</div>'+
                                          '</div>'+
                                      '</div>'+
                                  '</div>'+
                                  '<div class="col-md-4">'+
                                      '<label>Impuesto % <b style="color:#F44336 !important;">*</b></label>'+
                                      '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnexttabaddprod" name="impuesto" id="impuesto" required value="16.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                  '</div>'+
                              '</div>'+
                              '<div class="row">'+
                                  '<div class="col-md-4">'+
                                      '<label>Costo (De última compra sin impuesto)</label>'+
                                      '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnexttabaddprod" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                  '</div>'+
                                  '<div class="col-md-4">'+
                                      '<label>Precio (Precio neto)</label>'+
                                      '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnexttabaddprod" name="precio" id="precio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                  '</div>'+
                                  '<div class="col-md-2">'+
                                      '<label>Ubicación</label>'+
                                      '<input type="text" class="form-control inputnexttabaddprod" name="ubicacion" id="ubicacion" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this)">'+
                                  '</div>'+
                                  '<div class="col-md-2">'+
                                      '<label>Tipo Producto</label>'+
                                      '<select name="tipoproducto" id="tipoproducto" class="form-control select2" style="width:100% !important;" required>'+
                                      '</select>'+
                                  '</div>'+
                              '</div>'+
                          '</div>'+
                      '</div>';
          $("#tabsformproducto").html(tabs);
          //colocar autocomplette off  todo el formulario
          $(".form-control").attr('autocomplete','off');
          $("#codigo").val(codigoabuscar);
          obtenertipos(tipoalta);
          $("#ModalFormularioProducto").modal('show');
          $("#ModalFormularioProducto").css('overflow', 'auto');
          $("#ModalFormulario").modal('hide');
          setTimeout(function(){$("#codigo").focus();},500);
          //activar busqueda para clave producto
          $('#claveproducto').on('keypress', function(e) {
              //recomentable para mayor compatibilidad entre navegadores.
              var code = (e.keyCode ? e.keyCode : e.which);
              if(code==13){
                  obtenerclaveproductoporclave();
              }
          });
          //regresar clave
          $('#claveproducto').on('change', function(e) {
              regresarclaveproducto();
          });
          //activar busqueda para clave unidad
          $('#claveunidad').on('keypress', function(e) {
              //recomentable para mayor compatibilidad entre navegadores.
              var code = (e.keyCode ? e.keyCode : e.which);
              if(code==13){
                  obtenerclaveunidadporclave();
              }
          });
          //regresar clave
          $('#claveunidad').on('change', function(e) {
              regresarclaveunidad();
          });
          //activar busqueda para marca
          $('#marca').on('keypress', function(e) {
              //recomentable para mayor compatibilidad entre navegadores.
              var code = (e.keyCode ? e.keyCode : e.which);
              if(code==13){
                  obtenermarcapornumero();
              }
          });
          //regresar numero
          $('#marca').on('change', function(e) {
              regresarmarca();
          });
          //activar busqueda para linea
          $('#linea').on('keypress', function(e) {
              //recomentable para mayor compatibilidad entre navegadores.
              var code = (e.keyCode ? e.keyCode : e.which);
              if(code==13){
                  obtenerlineapornumero();
              }
          });
          //regresar numero
          $('#linea').on('change', function(e) {
              regresarlinea();
          });
          //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
          $(".inputnexttabaddprod").keypress(function (e) {
              //recomentable para mayor compatibilidad entre navegadores.
              var code = (e.keyCode ? e.keyCode : e.which);
              if(code==13){
              var index = $(this).index(".inputnexttabaddprod");
                  $(".inputnexttabaddprod").eq(index + 1).focus().select();
              }
          });
        }else{
          $("#codigoabuscar").val("");
        }
      }
    })
  }
}
//obtener tipos prod
function obtenertipos(tipoalta){
  var select_tipos_ordenes_compra = "";
  switch (tipoalta) {
    case "GASTOS":
        select_tipos_ordenes_compra = select_tipos_ordenes_compra + "<option value='GASTOS' selected>GASTOS</option>";
        break;
    case "TOT":
        select_tipos_ordenes_compra = select_tipos_ordenes_compra + "<option value='TOT' selected>TOT</option>";
        break;
    default:
        select_tipos_ordenes_compra = select_tipos_ordenes_compra + "<option value='REFACCION' selected>REFACCION</option>";
  }
  $("#tipoproducto").html(select_tipos_ordenes_compra);
}
//mostrar tabla de seleccion y ocultar formulario en modal
function ocultarformularioproducto(){
  $("#formularioproducto").hide();
  $("#contenidomodaltablasproducto").show();
}
//mostrar formulario de producto
function mostrarformularioproducto(){
  $("#formularioproducto").show();
  $("#contenidomodaltablasproducto").hide();
}
//mostrar modal formulario orden
function mostrarformularioorden(){
  $("#ModalFormulario").modal('show');
  $("#ModalFormulario").css('overflow', 'auto');
  $("#ModalFormularioProducto").modal('hide');
  $("#codigoabuscar").val("");
}
//listar claves productos
function listarclavesproductos(){
  ocultarformularioproducto();
  var tablaclavesproductos =  '<div class="modal-header '+background_forms_and_modals+'">'+
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
                                                          '<th>Usual</th>'+
                                                      '</tr>'+
                                                  '</thead>'+
                                                  '<tbody></tbody>'+
                                              '</table>'+
                                          '</div>'+
                                      '</div>'+
                                  '</div>'+
                              '</div>'+
                              '<div class="modal-footer">'+
                                  '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformularioproducto();">Regresar</button>'+
                              '</div>';
  $("#contenidomodaltablasproducto").html(tablaclavesproductos);
  var tclavprod = $('#tbllistadoclaveproducto').DataTable({
      keys: true,
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,
      processing: true,
      'language': {
          'loadingRecords': '&nbsp;',
          'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: ordenes_compra_obtener_claves_productos,
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre' },
          { data: 'Usual', name: 'Usual', orderable: false, searchable: false  }
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
      "iDisplayLength": 8,
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoclaveproducto tbody').on('dblclick', 'tr', function () {
    var data = tclavprod.row( this ).data();
    seleccionarclaveproducto(data.Clave, data.Nombre);
  });
}
function seleccionarclaveproducto(Clave, Nombre){
    $("#claveproducto").val(Clave);
    $("#producto").val(Nombre);
    $("#producto").keyup();
    mostrarformularioproducto();
}
//obtener por clave
function obtenerclaveproductoporclave(){
    var claveproductoanterior = $("#claveproductoanterior").val();
    var claveproducto = $("#claveproducto").val();
    if(claveproductoanterior != claveproducto){
        if($("#claveproducto").parsley().isValid()){
            $.get(productos_obtener_clave_producto_por_clave, {claveproducto:claveproducto}, function(data){
                $("#claveproducto").val(data.clave);
                $("#claveproductoanterior").val(data.clave);
                if(data.nombre != null){
                    $("#textonombreclaveproducto").html(data.nombre.substring(0, 40));
                }
            })
        }
    }
}
//regresar clave
function regresarclaveproducto(){
    var claveproductoanterior = $("#claveproductoanterior").val();
    $("#claveproducto").val(claveproductoanterior);
}
//listar claves unidades
function listarclavesunidades(){
  ocultarformularioproducto();
  var tablaclavesunidades =   '<div class="modal-header '+background_forms_and_modals+'">'+
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
                                                          '<th>Descripción</th>'+
                                                          '<th>Usual</th>'+
                                                      '</tr>'+
                                                  '</thead>'+
                                                  '<tbody></tbody>'+
                                              '</table>'+
                                          '</div>'+
                                      '</div>'+
                                  '</div>'+
                              '</div>'+
                              '<div class="modal-footer">'+
                                  '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformularioproducto();">Regresar</button>'+
                              '</div>';
  $("#contenidomodaltablasproducto").html(tablaclavesunidades);
  var tclavuni = $('#tbllistadoclaveunidad').DataTable({
      keys: true,
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,
      processing: true,
      'language': {
          'loadingRecords': '&nbsp;',
          'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: ordenes_compra_obtener_claves_unidades,
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre' },
          { data: 'Descripcion', name: 'Descripcion', orderable: false, searchable: false  },
          { data: 'Usual', name: 'Usual', orderable: false, searchable: false  }
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
      "iDisplayLength": 8,
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoclaveunidad tbody').on('dblclick', 'tr', function () {
    var data = tclavuni.row( this ).data();
    seleccionarclaveunidad(data.Clave, data.Nombre);
  });
}
function seleccionarclaveunidad(Clave, Nombre){
    $("#claveunidad").val(Clave);
    $("#unidad").val(Nombre);
    $("#unidad").keyup();
    mostrarformularioproducto();
}
//obtener por clave
function obtenerclaveunidadporclave(){
    var claveunidadanterior = $("#claveunidadanterior").val();
    var claveunidad = $("#claveunidad").val();
    if(claveunidadanterior != claveunidad){
        if($("#claveunidad").parsley().isValid()){
            $.get(productos_obtener_clave_unidad_por_clave, {claveunidad:claveunidad}, function(data){
                $("#claveunidad").val(data.clave);
                $("#claveunidadanterior").val(data.clave);
                if(data.nombre != null){
                    $("#textonombreclaveunidad").html(data.nombre.substring(0, 40));
                }
            })
        }
    }
}
//regresar clave
function regresarclaveunidad(){
    var claveunidadanterior = $("#claveunidadanterior").val();
    $("#claveunidad").val(claveunidadanterior);
}
//listar marcas
function listarmarcas(){
  ocultarformularioproducto();
  var tablamarcas =   '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Marcas</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                          '<div class="row">'+
                              '<div class="col-md-12">'+
                                  '<div class="table-responsive">'+
                                      '<table id="tbllistadomarca" class="tbllistadomarca table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                          '<thead class="'+background_tables+'">'+
                                              '<tr>'+
                                                  '<th>Operaciones</th>'+
                                                  '<th>Número</th>'+
                                                  '<th>Nombre</th>'+
                                                  '<th>Util 1 %</th>'+
                                                  '<th>Util 2 %</th>'+
                                                  '<th>Util 3 %</th>'+
                                                  '<th>Util 4 %</th>'+
                                                  '<th>Util 5 %</th>'+
                                              '</tr>'+
                                          '</thead>'+
                                          '<tbody></tbody>'+
                                      '</table>'+
                                  '</div>'+
                              '</div>'+
                          '</div>'+
                      '</div>'+
                      '<div class="modal-footer">'+
                          '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformularioproducto();">Regresar</button>'+
                      '</div>';
  $("#contenidomodaltablasproducto").html(tablamarcas);
  var tmarc = $('#tbllistadomarca').DataTable({
      keys: true,
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,
      processing: true,
      'language': {
          'loadingRecords': '&nbsp;',
          'processing': '<div class="spinner"></div>'
        },
      serverSide: true,
      ajax: ordenes_compra_obtener_marcas,
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Numero', name: 'Numero' },
          { data: 'Nombre', name: 'Nombre' },
          { data: 'Utilidad1', name: 'Utilidad1', orderable: false, searchable: false  },
          { data: 'Utilidad2', name: 'Utilidad2', orderable: false, searchable: false  },
          { data: 'Utilidad3', name: 'Utilidad3', orderable: false, searchable: false  },
          { data: 'Utilidad4', name: 'Utilidad4', orderable: false, searchable: false  },
          { data: 'Utilidad5', name: 'Utilidad5', orderable: false, searchable: false  }
      ],
      "initComplete": function() {
          var $buscar = $('div.dataTables_filter input');
          $buscar.focus();
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadomarca').DataTable().search( this.value ).draw();
              }
          });
      },
      "iDisplayLength": 8,
  });
  //seleccionar registro al dar doble click
  $('#tbllistadomarca tbody').on('dblclick', 'tr', function () {
    var data = tmarc.row( this ).data();
    seleccionarmarca(data.Numero, data.Nombre);
  });
}
function seleccionarmarca(Numero, Nombre){
    $("#marca").val(Numero);
    $("#nombremarca").val(Nombre);
    mostrarformularioproducto();
}
//obtener por numero
function obtenermarcapornumero(){
    var marcaanterior = $("#marcaanterior").val();
    var marca = $("#marca").val();
    if(marcaanterior != marca){
        if($("#marca").parsley().isValid()){
            $.get(productos_obtener_marca_por_numero, {marca:marca}, function(data){
                $("#marca").val(data.numero);
                $("#marcaanterior").val(data.numero);
                if(data.nombre != null){
                    $("#textonombremarca").html(data.nombre.substring(0, 40));
                }
            })
        }
    }
}
//regresar numero
function regresarmarca(){
    var marcaanterior = $("#marcaanterior").val();
    $("#marca").val(marcaanterior);
}
//listar lineas
function listarlineas(){
  ocultarformularioproducto();
  var tablalineas =   '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Lineas</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                          '<div class="row">'+
                              '<div class="col-md-12">'+
                                  '<div class="table-responsive">'+
                                      '<table id="tbllistadolinea" class="tbllistadolinea table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                          '<thead class="'+background_tables+'">'+
                                              '<tr>'+
                                                  '<th>Operaciones</th>'+
                                                  '<th>Número</th>'+
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
                          '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformularioproducto();">Regresar</button>'+
                      '</div>';
  $("#contenidomodaltablasproducto").html(tablalineas);
  var tlin = $('#tbllistadolinea').DataTable({
      keys: true,
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,
      processing: true,
      'language': {
          'loadingRecords': '&nbsp;',
          'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: ordenes_compra_obtener_lineas,
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
                $('#tbllistadolinea').DataTable().search( this.value ).draw();
              }
          });
      },
      "iDisplayLength": 8,
  });
  //seleccionar registro al dar doble click
  $('#tbllistadolinea tbody').on('dblclick', 'tr', function () {
    var data = tlin.row( this ).data();
    seleccionarlinea(data.Numero, data.Nombre);
  });
}
function seleccionarlinea(Numero, Nombre){
    $("#linea").val(Numero);
    $("#nombrelinea").val(Nombre);
    mostrarformularioproducto();
}
//obtener por numero
function obtenerlineapornumero(){
    var lineaanterior = $("#lineaanterior").val();
    var linea = $("#linea").val();
    if(lineaanterior != linea){
        if($("#linea").parsley().isValid()){
            $.get(productos_obtener_linea_por_numero, {linea:linea}, function(data){
                $("#linea").val(data.numero);
                $("#lineaanterior").val(data.numero);
                if(data.nombre != null){
                    $("#textonombrelinea").html(data.nombre.substring(0, 40));
                }
            })
        }
    }
}
//regresar numero
function regresarlinea(){
    var lineaanterior = $("#lineaanterior").val();
    $("#linea").val(lineaanterior);
}
//guardar el registro
$("#btnGuardarProducto").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsleyproducto")[0]);
  var form = $("#formparsleyproducto");
  if (form.parsley().isValid()){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          url:ordenes_compra_guardar_producto,
          type: "post",
          dataType: "html",
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          success:function(data){
              if(data == 1){
                  msj_errorcodigoexistente();
              }else{
                  toastr.success( "Datos guardados correctamente", "Mensaje", {
                      "timeOut": "6000",
                      "progressBar": true,
                      "extendedTImeout": "6000"
                  });
                  $("#ModalFormulario").modal('show');
                  $("#ModalFormulario").css('overflow', 'auto');
                  $("#ModalFormularioProducto").modal('hide');
                  obtenerproductoporcodigo();
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
function calculartotalesfilasordencompra(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
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
      //iva en pesos de la partida
      var multiplicacionivapesospartida = new Decimal(subtotalpartida).times(ivaporcentajepartida);
      ivapesospartida = new Decimal(multiplicacionivapesospartida/100);
      $('.ivapesospartida', this).val(number_format(round(ivapesospartida, numerodecimales), numerodecimales, '.', ''));
      //total en pesos de la partida
      totalpesospartida = new Decimal(subtotalpartida).plus(ivapesospartida);
      $('.totalpesospartida', this).val(number_format(round(totalpesospartida, numerodecimales), numerodecimales, '.', ''));
      calculartotalordencompra();
    }
    cuentaFilas++;
  });
}
//dejar en 0 los descuentos cuando el precio de la partida se cambie
function cambiodecantidadopreciopartida(fila,tipo){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
    if(fila === cuentaFilas){
      $('.descuentopesospartida', this).val('0.'+numerocerosconfigurados);
      $('.descuentoporcentajepartida',this).val('0.'+numerocerosconfigurados);
      //asignar el valor de cantidad a por surtir
      if(tipo == "alta"){
        $(".porsurtirpartida", this).val($(".cantidadpartida", this).val());
      }else if(tipo == "modificacion"){
        var calculoporsurtir = new Decimal($(".cantidadpartida",this).val()).minus($(".cantidadyasurtidapartida", this).val());
        $('.porsurtirpartida', this).val(number_format(round(calculoporsurtir, numerodecimales), numerodecimales, '.', ''));
      }
      calculartotalesfilasordencompra(fila);
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
      calculartotalesfilasordencompra(fila);
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
      calculartotalesfilasordencompra(fila);
    }
    cuentaFilas++;
  });
}
//calcular totales de orden de compra
function calculartotalordencompra(){
  var importe = 0;
  var descuento = 0;
  var subtotal= 0;
  var iva = 0;
  var total = 0;
  $("tr.filasproductos").each(function(){
    importe= new Decimal(importe).plus($(".importepartida", this).val());
    descuento = new Decimal(descuento).plus($(".descuentopesospartida", this).val());
    subtotal= new Decimal(subtotal).plus($(".subtotalpartida", this).val());
    iva = new Decimal(iva).plus($(".ivapesospartida", this).val());
    total = new Decimal(total).plus($(".totalpesospartida", this).val());
  });
  //IVA Y TOTAL
  let ivaAux = 0
  let totalRound = 0
  if(iva > 0){
    ivaAux = subtotal * 0.16;
  }
  totalRound = parseFloat(subtotal.toFixed(parseInt(numerodecimales))) + ivaAux

  $("#importe").val(number_format(round(importe, numerodecimales), numerodecimales, '.', ''));
  $("#descuento").val(number_format(round(descuento, numerodecimales), numerodecimales, '.', ''));
  $("#subtotal").val(number_format(round(subtotal, numerodecimales), numerodecimales, '.', ''));
  $("#iva").val(number_format(round(ivaAux, numerodecimales), numerodecimales, '.', ''));
  $("#total").val(number_format(round(totalRound, numerodecimales), numerodecimales, '.', ''));
  //if el status de la orden es backorder se debe comparar el total de la orden de compra con el total de la(s) compras en las que se ha utilizado la orden de compra
  if($("#statusordencompra").val() == "BACKORDER"){
    if($("#sumatotalcompras").val() != number_format(round(total, numerodecimales), numerodecimales, '.', '')){
      $("#btnGuardarModificacion").hide();
      msj_errortotalordencompra();
    }else{
      $("#btnGuardarModificacion").show();
    }
  }
}
//agregar una fila en la tabla de precios productos
var contadorproductos=0;
var contadorfilas = 0;
function agregarfilaproducto(Codigo, Producto, Unidad, Costo, Impuesto, tipooperacion){
  var result = evaluarproductoexistente(Codigo);
  if(result == false){
    var multiplicacioncostoimpuesto =  new Decimal(Costo).times(Impuesto);
    var ivapesos = new Decimal(multiplicacioncostoimpuesto/100);
    var total = new Decimal(Costo).plus(ivapesos);
    var tipo = "alta";
    var fila=   '<tr class="filasproductos" id="filaproducto'+contadorproductos+'">'+
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosproductos('+contadorproductos+')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'+Codigo+'</b></td>'+
                        '<td class="tdmod"><textarea rows="1" type="text" class="form-control inputnextdet nombreproductopartida" name="nombreproductopartida[]" id="nombreproductopartida[]" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"  style="font-size:10px;">'+Producto+'</textarea></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'+Unidad+'" readonly data-parsley-length="[1, 5]">'+Unidad+'</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm porsurtirpartida" name="porsurtirpartida[]" id="porsurtirpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" id="cantidadpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('+contadorfilas+');cambiodecantidadopreciopartida('+contadorfilas+',\''+tipo +'\');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" id="preciopartida[]" value="'+Costo+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('+contadorfilas+');cambiodecantidadopreciopartida('+contadorfilas+',\''+tipo +'\');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" id="importepartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" id="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" id="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" id="subtotalpartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" id="ivaporcentajepartida[]" value="'+Impuesto+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" id="ivapesospartida[]" value="'+number_format(round(ivapesos, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" id="totalpesospartida[]" value="'+number_format(round(total, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '</tr>';
    contadorproductos++;
    contadorfilas++;
    $("#tablaproductodordencompra").append(fila);
    mostrarformulario();
    comprobarfilaspreciosproductos();
    calculartotalordencompra();
    $("#codigoabuscar").val("");
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
  }
}
//eliminar una fila en la tabla de precios clientes
function eliminarfilapreciosproductos(numerofila){
  var confirmacion = confirm("Esta seguro de eliminar el producto?");
  if (confirmacion == true) {
    $("#filaproducto"+numerofila).remove();
    contadorfilas--; //importante para todos los calculo en el modulo de orden de compra se debe restar al contadorfilas la fila que se acaba de eliminar
    comprobarfilaspreciosproductos();
    renumerarfilasordencompra();//importante para todos los calculo en el modulo de orden de compra
    calculartotalordencompra();
  }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilaspreciosproductos(){
  var numerofilas = $("#tablaproductodordencompra tbody tr").length;
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
  //renumero el precio de la partida
  lista = document.getElementsByClassName("preciopartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilasordencompra("+i+');cambiodecantidadopreciopartida('+i+',\''+tipo +'\')');
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
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilasordencompra("+i+')');
  }
}
//alta clientes
function alta(tipoalta){
  $("#titulomodal").html('Alta Orden de Compra ' + tipoalta);
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs ='<div class="col-md-12">'+
              '<div class="row">'+
                '<div class="col-md-3">'+
                  '<label>Orden <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b> &nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                  '<input type="text" class="form-control inputnextdet" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                  '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" data-parsley-length="[0, 10]" required readonly>'+
                  '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" value="alta" readonly>'+
                  '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" value="0" readonly>'+
                  '<input type="hidden" class="form-control" name="tipoalta" id="tipoalta" value="'+tipoalta+'" readonly>'+
                '</div>'+
                '<div class="col-md-2">'+
                  '<label>Plazo Días (proveedor)</label>'+
                  '<input type="text" class="form-control inputnextdet" name="plazo" id="plazo" required autocomplete="off">'+
                '</div>'+
                '<div class="col-md-2">'+
                  '<label>Referencia</label>'+
                  '<input type="text" class="form-control inputnextdet" name="referencia" id="referencia" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                '</div>'+
                '<div class="col-md-2">'+
                  '<label>Tipo</label>'+
                  '<select name="tipo" id="tipo" class="form-control select2" style="width:100% !important;" required>'+
                  '</select>'+
                '</div>'+
                '<div class="col-xs-12 col-sm-12 col-md-3">'+
                  '<label>Fecha</label>'+
                  '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required  style="min-width:95%;" data-parsley-excluded="true" onkeydown="return false">'+
                  '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                '</div>'+
              '</div>'+
              '<div class="row">'+
                '<div class="col-md-4">'+
                  '<label>Proveedor <span class="label label-danger" id="textonombreproveedor"></span></label>'+
                  '<table class="col-md-12">'+
                    '<tr>'+
                      '<td>'+
                        '<div class="btn bg-blue waves-effect" onclick="obtenerproveedores()">Seleccionar</div>'+
                      '</td>'+
                      '<td>'+
                        '<div class="form-line">'+
                          '<input type="text" class="form-control inputnextdet" name="numeroproveedor" id="numeroproveedor" required data-parsley-type="integer" autocomplete="off">'+
                          '<input type="hidden" class="form-control" name="numeroproveedoranterior" id="numeroproveedoranterior" required data-parsley-type="integer">'+
                          '<input type="hidden" class="form-control" name="proveedor" id="proveedor" required readonly onkeyup="tipoLetra(this)">'+
                        '</div>'+
                      '</td>'+
                    '</tr>'+
                  '</table>'+
                '</div>'+
                '<div class="col-md-4">'+
                  '<label>Almacen <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                  '<table class="col-md-12">'+
                    '<tr>'+
                      '<td>'+
                        '<div class="btn bg-blue waves-effect" id="btnobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>'+
                      '</td>'+
                      '<td>'+
                        '<div class="form-line">'+
                          '<input type="text" class="form-control inputnextdet" name="numeroalmacen" id="numeroalmacen" required data-parsley-type="integer" autocomplete="off">'+
                          '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required data-parsley-type="integer">'+
                          '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly onkeyup="tipoLetra(this)">'+
                        '</div>'+
                      '</td>'+
                    '</tr>'+
                  '</table>'+
                '</div>'+
                '<div class="col-md-4" id="divbuscarcodigoproducto">'+
                  '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                  '<table class="col-md-12">'+
                    '<tr>'+
                      '<td>'+
                        '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver Productos</div>'+
                      '</td>'+
                      '<td>'+
                        '<div class="form-line">'+
                          '<input type="text" class="form-control inputnextdet" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off" onkeyup="tipoLetra(this)">'+
                        '</div>'+
                      '</td>'+
                    '</tr>'+
                  '</table>'+
                '</div>'+
              '</div>'+
              '<div class="row">'+
                '<div class="col-md-4" id="busquedaordenestrabajo">'+
                  '<label>Orden Trabajo <span class="label label-danger" id="textonombreordentrabajo"></span></label>'+
                  '<table class="col-md-12">'+
                    '<tr>'+
                      '<td>'+
                        '<div class="btn bg-blue waves-effect" id="btnobtenerordenestrabajo" onclick="obtenerordenestrabajo()">Seleccionar</div>'+
                      '</td>'+
                      '<td>'+
                        '<div class="form-line">'+
                          '<input type="text" class="form-control" name="ordentrabajo" id="ordentrabajo" onkeyup="tipoLetra(this);" autocomplete="off">'+
                          '<input type="hidden" class="form-control" name="ordentrabajoanterior" id="ordentrabajoanterior">'+
                        '</div>'+
                      '</td>'+
                    '</tr>'+
                  '</table>'+
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
                    '<div class="col-md-12 table-responsive cabecerafija" style="height: 175px;overflow-y: scroll;padding: 0px 0px;">'+
                      '<table id="tablaproductodordencompra" class="table table-bordered tablaproductodordencompra">'+
                        '<thead class="'+background_tables+'">'+
                          '<tr>'+
                            '<th class="'+background_tables+'">#</th>'+
                            '<th class="'+background_tables+'"><div style="width:100px !important;">Código</div></th>'+
                            '<th class="customercolortheadth"><div style="width:400px !important;">Descripción</div></th>'+
                            '<th class="'+background_tables+'">Unidad</th>'+
                            '<th class="'+background_tables+'">Por Surtir</th>'+
                            '<th class="customercolortheadth">Cantidad</th>'+
                            '<th class="customercolortheadth">Precio $</th>'+
                            '<th class="'+background_tables+'">Importe $</th>'+
                            '<th class="customercolortheadth">Dcto %</th>'+
                            '<th class="customercolortheadth">Dcto $</th>'+
                            '<th class="'+background_tables+'">SubTotal $</th>'+
                            '<th class="customercolortheadth">Iva %</th>'+
                            '<th class="'+background_tables+'">Iva $</th>'+
                            '<th class="'+background_tables+'">Total $</th>'+
                          '</tr>'+
                        '</thead>'+
                        '<tbody>'+
                        '</tbody>'+
                      '</table>'+
                    '</div>'+
                  '</div>'+
                  '<div class="row">'+
                    '<div class="col-md-12">'+
                      '<table>'+
                        '<tr>'+
                          '<td><div type="button" class="btn btn-success btn-sm" onclick="seleccionarpartidasexcel()">Importar partidas en excel</div></td>'+
                          '<td data-toggle="tooltip" data-placement="top" title data-original-title="Bajar plantilla"><a class="material-icons" onclick="descargar_plantilla()" id="btnGenerarPlantilla" target="_blank">get_app</a></td>'+
                        '</tr>'+
                      '</table>'+
                    '</div>'+
                  '</div>'+
                  '<div class="row">'+
                    '<div class="col-md-6">'+
                      '<label>Observaciones</label>'+
                      '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" rows="5" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" required></textarea>'+
                    '</div>'+
                    '<div class="col-md-3 col-md-offset-3">'+
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
                '</div>'+
              '</div>'+
            '</div>';
  $("#tabsform").html(tabs);
  //colocar autocomplette off  todo el formulario
  $(".form-control").attr('autocomplete','off');
  //mostrar mensaje de bajar plantilla
  $('[data-toggle="tooltip"]').tooltip({
    container: 'body'
  });
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
  obtenultimonumero();
  obtenertiposordenescompra(tipoalta, generaexistencias);
  asignarfechaactual();
  //reiniciar los contadores
  contadorproductos=0;
  contadorfilas = 0;
  //activar select2
  $("#tipo").select2();
  //busquedas seleccion
  //activar busqueda de codigos
  $("#codigoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerproductoporcodigo();
    }
  });
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
      //activar busqueda para ordenes
      $('#ordentrabajo').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerordenporfolio();
        }
      });
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
      //activar busqueda
      $('#numeroalmacen').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          obteneralmacenpornumero();
        }
      });
      $("#busquedaordenestrabajo").hide();
  }
  //regresar numero
  $('#numeroalmacen').on('change', function(e) {
    regresarnumeroalmacen();
  });
  //regresar folio orden
  $('#ordentrabajo').on('change', function(e) {
    regresarfolioorden();
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
    var numerofilas = $("#numerofilas").val();
    if(parseInt(numerofilas) > 0 && parseInt(numerofilas) < 500){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:ordenes_compra_guardar,
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
      msj_erroralmenosunaentrada();
    }
  }else{
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});
//autorizar orden de compra
function autorizarordencompra(ordenautorizar){
  $.get(ordenes_compra_verificar_autorizacion,{ordenautorizar:ordenautorizar}, function(data){
    if(data.msjsurtimiento != ''){
      $("#footermodalautorizacion").hide();
      $("#divformautorizar").hide();
      $("#divmsjsurtimiento").show();
      $("#textomsjsurtimiento").html("Esta seguro de autorizar la orden de compra?, ya que algunas de sus partidas ya las tiene cargadas en otras Ordenes de Compra y aun estan pendientes por surtir.<br>"+ data.msjsurtimiento);
    }else{
      $("#footermodalautorizacion").show();
      $("#divformautorizar").show();
      $("#divmsjsurtimiento").hide();
      $("#textomsjsurtimiento").html("");
    }
    if(data.OrdenCompra.AutorizadoPor == ''){
      $("#ordenautorizar").val(ordenautorizar);
      $("#ordenquitarautorizacion").val("");
      $("#textomodalautorizar").html("Estas seguro de autorizar la orden de compra? No."+ ordenautorizar);
      $("#btnautorizar").show();
      $("#btnquitarautorizacion").hide();
      $('#autorizarorden').modal('show');
    }else{
      $("#ordenautorizar").val("");
      $("#ordenquitarautorizacion").val("");
      $("#textomodalautorizar").html("La orden ya fue autorizada");
      $("#btnautorizar").hide();
      $("#btnquitarautorizacion").hide();
      $('#autorizarorden').modal('show');
    }
  });
}
//continuar con autorizacion
function continuarconautorizacion(){
  $("#footermodalautorizacion").show();
  $("#divformautorizar").show();
  $("#divmsjsurtimiento").hide();
  $("#textomsjsurtimiento").html("");
}
$("#btnautorizar").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formautorizar")[0]);
  var form = $("#formautorizar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:ordenes_compra_autorizar,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#autorizarorden').modal('hide');
        msj_datosguardadoscorrectamente();
        $('.page-loader-wrapper').css('display', 'none');
      },
      error:function(data){
        if(data.status == 403){
          msj_errorenpermisos();
        }else{
          msj_errorajax();
        }
        $('#autorizarorden').modal('hide');
        $('.page-loader-wrapper').css('display', 'none');
      }
    })
  }else{
    form.parsley().validate();
  }
});
//quitar autorzacion a orden compra
function quitarautorizacionordencompra(orden){
  $.get(ordenes_compra_verificar_quitar_autorizacion,{orden:orden}, function(data){
    if(data.OrdenCompra.Status == 'POR SURTIR'){
      $("#ordenquitarautorizacion").val(orden);
      $("#ordenautorizar").val("");
      $("#textomodalautorizar").html("Estas seguro de quitar autorizacion a la orden de compra? No."+ orden);
      $("#btnautorizar").hide();
      $("#btnquitarautorizacion").show();
      $("#divmsjsurtimiento").hide();
      $('#autorizarorden').modal('show');
    }else if(data.OrdenCompra.Status == 'BACKORDER'){
      if(data.numerocompra > 0){
        $("#ordenquitarautorizacion").val("");
        $("#ordenautorizar").val("");
        $("#textomodalautorizar").html("No se puede quitar autorización a la Orden de Compra porque esta dada de BAJA o ya fue utilizada en una Compra");
        $("#btnautorizar").hide();
        $("#btnquitarautorizacion").hide();
        $("#divmsjsurtimiento").hide();
        $('#autorizarorden').modal('show');
      }else{
        $("#ordenquitarautorizacion").val(orden);
        $("#ordenautorizar").val("");
        $("#textomodalautorizar").html("Estas seguro de quitar autorizacion a la orden de compra? No."+ orden);
        $("#btnautorizar").hide();
        $("#btnquitarautorizacion").show();
        $("#divmsjsurtimiento").hide();
        $('#autorizarorden').modal('show');
      }
    }else{
      $("#ordenquitarautorizacion").val("");
      $("#ordenautorizar").val("");
      $("#textomodalautorizar").html("No se puede quitar autorización a la Orden de Compra porque esta dada de BAJA o ya fue utilizada en una Compra");
      $("#btnautorizar").hide();
      $("#btnquitarautorizacion").hide();
      $("#divmsjsurtimiento").hide();
      $('#autorizarorden').modal('show');
    }
  });
}
$("#btnquitarautorizacion").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formautorizar")[0]);
  var form = $("#formautorizar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:ordenes_compra_quitar_autorizacion,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#autorizarorden').modal('hide');
        msj_datosguardadoscorrectamente();
        $('.page-loader-wrapper').css('display', 'none');
      },
      error:function(data){
        if(data.status == 403){
          msj_errorenpermisos();
        }else{
          msj_errorajax();
        }
        $('#autorizarorden').modal('hide');
        $('.page-loader-wrapper').css('display', 'none');
      }
    })
  }else{
    form.parsley().validate();
  }
});
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(ordendesactivar){
  $.get(ordenes_compra_verificar_uso_en_modulos,{ordendesactivar:ordendesactivar}, function(data){
    if(data.Status == 'BAJA'){
      $("#ordendesactivar").val(0);
      $("#textomodaldesactivar").html('Error, esta orden de compra ya fue dada de baja ');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{
      if(data.resultadofechas != ''){
        $("#ordendesactivar").val(0);
        $("#textomodaldesactivar").html('Error solo se pueden dar de baja las ordenes de compra del mes actual, fecha de la orden de compra: ' + data.resultadofechas);
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        if(data.resultado > 0){
          $("#ordendesactivar").val(0);
          $("#textomodaldesactivar").html('Error esta orden de compra se esta utilizando en la compra : ' + data.numerocompra);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else{
          $("#ordendesactivar").val(ordendesactivar);
          $("#textomodaldesactivar").html('Estas seguro de dar de baja la orden de compra? No'+ ordendesactivar);
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
      url:ordenes_compra_alta_o_baja,
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
function obtenerdatos(ordenmodificar){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(ordenes_compra_obtener_orden_compra,{ordenmodificar:ordenmodificar },function(data){
    $("#titulomodal").html('Modificación Orden Compra --- STATUS : ' + data.ordencompra.Status);
    //formulario modificacion
    var tabs ='<div class="col-md-12">'+
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Orden <b style="color:#F44336 !important;" id="serietexto"> Serie:</b></label>'+
                    '<input type="text" class="form-control inputnextdet" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                    '<input type="hidden" class="form-control" name="serie" id="serie"  data-parsley-length="[0, 10]" required readonly>'+
                    '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion"  readonly>'+
                    '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                    '<input type="hidden" class="form-control" name="tipoalta" id="tipoalta" value="" readonly>'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Plazo Días (proveedor)</label>'+
                    '<input type="text" class="form-control inputnextdet" name="plazo" id="plazo"  required autocomplete="off">'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Referencia</label>'+
                    '<input type="text" class="form-control inputnextdet" name="referencia" id="referencia" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Tipo</label>'+
                    '<select name="tipo" id="tipo" class="form-control select2" style="width:100% !important;" required>'+
                    '</select>'+
                  '</div>'+
                  '<div class="col-xs-12 col-sm-12 col-md-3">'+
                    '<label>Fecha</label>'+
                    '<input type="datetime-local" class="form-control" name="fecha" id="fecha" required style="min-width:95%;" data-parsley-excluded="true" onkeydown="return false">'+
                    '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy">'+
                  '</div>'+
                '</div>'+
                '<div class="row">'+
                  '<div class="col-md-4">'+
                    '<label>Proveedor <span class="label label-danger" id="textonombreproveedor"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" onclick="obtenerproveedores()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnextdet" name="numeroproveedor" id="numeroproveedor"  required data-parsley-type="integer" autocomplete="off">'+
                            '<input type="hidden" class="form-control" name="numeroproveedoranterior" id="numeroproveedoranterior"  required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="proveedor" id="proveedor"  required readonly onkeyup="tipoLetra(this)">'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-4">'+
                    '<label>Almacen <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="btnobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnextdet" name="numeroalmacen" id="numeroalmacen" required data-parsley-type="integer" autocomplete="off">'+
                            '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly onkeyup="tipoLetra(this)">'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-4" id="divbuscarcodigoproducto">'+
                    '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver Productos</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnextdet" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off" onkeyup="tipoLetra(this)">'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+
                    '</table>'+
                  '</div>'+
                '</div>'+
                '<div class="row">'+
                  '<div class="col-md-4" id="busquedaordenestrabajo">'+
                    '<label>Orden Trabajo <span class="label label-danger" id="textonombreordentrabajo"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="btnobtenerordenestrabajo" onclick="obtenerordenestrabajo()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control" name="ordentrabajo" id="ordentrabajo" onkeyup="tipoLetra(this);" autocomplete="off">'+
                            '<input type="hidden" class="form-control" name="ordentrabajoanterior" id="ordentrabajoanterior">'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+
                    '</table>'+
                  '</div>'+
                '</div>'+
              '</div>'+
              '<div class="col-md-12" id="tabsform">'+
                '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                  '<li role="presentation" class="active">'+
                    '<a href="#productostab" data-toggle="tab">Productos</a>'+
                  '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                  '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                    '<div class="row">'+
                      '<div class="col-md-12 table-responsive cabecerafija" style="height: 175px;overflow-y: scroll;padding: 0px 0px;">'+
                        '<table id="tablaproductodordencompra" class="table table-bordered tablaproductodordencompra tabladetallesmodulo">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="'+background_tables+'" ondblclick="construirtabladinamicaporcolumna(\'codigoproductopartida\',\'Codigo\');"><div style="width:100px !important;">Código</div></th>'+
                              '<th class="customercolortheadth" ondblclick="construirtabladinamicaporcolumna(\'nombreproductopartida\',\'Descripción\');"><div style="width:400px !important;">Descripción</div></th>'+
                              '<th class="'+background_tables+'" ondblclick="construirtabladinamicaporcolumna(\'unidadproductopartida\',\'Unidad\');">Unidad</th>'+
                              '<th class="'+background_tables+'" ondblclick="construirtabladinamicaporcolumna(\'porsurtirpartida\',\'Por Surtir\');">Por Surtir</th>'+
                              '<th class="customercolortheadth" ondblclick="construirtabladinamicaporcolumna(\'cantidadpartida\',\'Cantidad\');">Cantidad</th>'+
                              '<th class="customercolortheadth" ondblclick="construirtabladinamicaporcolumna(\'preciopartida\',\'Precio $\');">Precio $</th>'+
                              '<th class="'+background_tables+'" ondblclick="construirtabladinamicaporcolumna(\'importepartida\',\'Importe $\');">Importe $</th>'+
                              '<th class="customercolortheadth" ondblclick="construirtabladinamicaporcolumna(\'descuentoporcentajepartida\',\'Dcto %\');">Dcto %</th>'+
                              '<th class="customercolortheadth" ondblclick="construirtabladinamicaporcolumna(\'descuentopesospartida\',\'Dcto $\');">Dcto $</th>'+
                              '<th class="'+background_tables+'" ondblclick="construirtabladinamicaporcolumna(\'subtotalpartida\',\'SubTotal $\');">SubTotal $</th>'+
                              '<th class="customercolortheadth" ondblclick="construirtabladinamicaporcolumna(\'ivaporcentajepartida\',\'Iva %\');">Iva %</th>'+
                              '<th class="'+background_tables+'" ondblclick="construirtabladinamicaporcolumna(\'ivapesospartida\',\'Iva $\');">Iva $</th>'+
                              '<th class="'+background_tables+'" ondblclick="construirtabladinamicaporcolumna(\'totalpesospartida\',\'Total $\');">Total $</th>'+
                            '</tr>'+
                          '</thead>'+
                          '<tbody>'+
                          '</tbody>'+
                        '</table>'+
                        '<table class="table table-bordered tabladinamicaacopiar" hidden>'+
                            '<thead class="'+background_tables+'" id="theadtabladinamicaacopiar">'+
                            '</thead>'+
                            '<tbody id="tbodytabladinamicaacopiar">'+
                            '</tbody>'+
                        '</table>'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-6">'+
                        '<label>Observaciones</label>'+
                        '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" rows="5" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" required></textarea>'+
                      '</div>'+
                      '<div class="col-md-3 col-md-offset-3">'+
                        '<table class="table table-striped table-hover">'+
                          '<tr>'+
                            '<td style="padding:0px !important;">Importe</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="importe" id="importe" value="0.'+numerocerosconfigurados+'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                          '<tr>'+
                            '<td style="padding:0px !important;">Descuento</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                          '<tr>'+
                            '<td style="padding:0px !important;">SubTotal</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                          '<tr>'+
                            '<td style="padding:0px !important;">Iva</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="iva" id="iva" value="0.'+numerocerosconfigurados+'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                          '<tr>'+
                            '<td style="padding:0px !important;">Total</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="total" id="total" value="0.'+numerocerosconfigurados+'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                          '<tr hidden>'+
                            '<td style="padding:0px !important;">Total Suma Com</td>'+
                            '<td style="padding:0px !important;" ><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="sumatotalcompras" id="sumatotalcompras" value="'+data.sumatotalcompras+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                            '<td style="padding:0px !important;" hidden><input type="text" class="form-control" style="width:100% !important;height:25px !important;" name="statusordencompra" id="statusordencompra" value="'+data.statusordencompra+'" required readonly></td>'+
                          '</tr>'+
                        '</table>'+
                      '</div>'+
                    '</div>'+
                  '</div>'+
                '</div>'
              '</div>';
    $("#tabsform").html(tabs);
    $("#periodohoy").val(data.ordencompra.Periodo);
    $("#folio").val(data.ordencompra.Folio);
    $("#serie").val(data.ordencompra.Serie);
    $("#serietexto").html("Serie: "+data.ordencompra.Serie);
    $("#plazo").val(data.ordencompra.Plazo);
    $("#referencia").val(data.ordencompra.Referencia);
    $("#fecha").val(data.fecha).attr('min', data.fechasdisponiblesenmodificacion.fechamin).attr('max', data.fechasdisponiblesenmodificacion.fechamax);
    $("#numeroproveedor").val(data.proveedor.Numero);
    $("#numeroproveedoranterior").val(data.proveedor.Numero);
    $("#proveedor").val(data.proveedor.Nombre);
    if(data.proveedor.Nombre != null){
      $("#textonombreproveedor").html(data.proveedor.Nombre.substring(0, 60));
    }
    //verificar que tipo de alta se realizara y asignaran configuraciones correspondientes
    switch (data.ordencompra.Tipo) {
      case 'GASTOS':
        //desabilitar almacen
        $("#numeroalmacen").val(0).attr('readonly', 'readonly');
        $("#numeroalmacenanterior").val(0).attr('readonly', 'readonly');
        $("#almacen").val(0).attr('readonly', 'readonly');
        $("#btnobteneralmacenes").hide();
        $("#busquedaordenestrabajo").hide();
        break;
      case 'CAJA CHICA':
        if(data.ordencompra.Almacen > 0){
          $("#numeroalmacen").val(data.almacen.Numero);
          $("#numeroalmacenanterior").val(data.almacen.Numero);
          $("#almacen").val(data.almacen.Nombre);
          if(data.almacen.Nombre != null){
            $("#textonombrealmacen").html(data.almacen.Nombre.substring(0, 60));
          }
          //activar busqueda
          $('#numeroalmacen').on('keypress', function(e) {
            //recomentable para mayor compatibilidad entre navegadores.
            var code = (e.keyCode ? e.keyCode : e.which);
            if(code==13){
              obteneralmacenpornumero();
            }
          });
          $("#busquedaordenestrabajo").hide();
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
        $("#ordentrabajo").val(data.ordencompra.OrdenTrabajo).attr('required', 'required').addClass('inputnextdet');
        $("#ordentrabajoanterior").val(data.ordencompra.OrdenTrabajo).attr('required', 'required');
        if(data.ordencompra.OrdenTrabajo != null){
          $("#textonombreordentrabajo").html(data.ordencompra.OrdenTrabajo.substring(0, 60));
        }
        //desabilitar almacen
        $("#numeroalmacen").val(0).attr('readonly', 'readonly');
        $("#numeroalmacenanterior").val(0).attr('readonly', 'readonly');
        $("#almacen").val(0).attr('readonly', 'readonly');
        $("#btnobteneralmacenes").hide();
        $("#busquedaordenestrabajo").show();
        //activar busqueda para ordenes
        $('#ordentrabajo').on('keypress', function(e) {
          //recomentable para mayor compatibilidad entre navegadores.
          var code = (e.keyCode ? e.keyCode : e.which);
          if(code==13){
          obtenerordenporfolio();
          }
        });
        break;
      default:
        $("#numeroalmacen").val(data.almacen.Numero);
        $("#numeroalmacenanterior").val(data.almacen.Numero);
        $("#almacen").val(data.almacen.Nombre);
        if(data.almacen.Nombre != null){
          $("#textonombrealmacen").html(data.almacen.Nombre.substring(0, 60));
        }
        //activar busqueda
        $('#numeroalmacen').on('keypress', function(e) {
          //recomentable para mayor compatibilidad entre navegadores.
          var code = (e.keyCode ? e.keyCode : e.which);
          if(code==13){
            obteneralmacenpornumero();
          }
        });
        $("#busquedaordenestrabajo").hide();
    }
    $("#observaciones").val(data.ordencompra.Obs)
    $("#importe").val(data.importe);
    $("#descuento").val(data.descuento);
    $("#subtotal").val(data.subtotal);
    $("#iva").val(data.iva);
    $("#total").val(data.total);
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //tabs precios productos
    $("#tablaproductodordencompra").append(data.filasdetallesordencompra);
    $("#numerofilas").val(data.numerodetallesordencompra);
    //se deben asignar los valores a los contadores para que las sumas resulten correctas
    contadorproductos = data.contadorproductos;
    contadorfilas = data.contadorfilas;
    //busquedas seleccion
    //activar busqueda de codigos
    $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenerproductoporcodigo();
      }
    });
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
    //regresar numero
    $('#numeroalmacen').on('change', function(e) {
      regresarnumeroalmacen();
    });
    //regresar folio orden
    $('#ordentrabajo').on('change', function(e) {
      regresarfolioorden();
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
    //copiar detalles tabla modulo
    const btnCopyTable = document.querySelector('table.tabladinamicaacopiar');
    const elTable = document.querySelector('table.tabladinamicaacopiar');
    const copyEl = (elToBeCopied) => {
       let range, sel;
       // Ensure that range and selection are supported by the browsers
       if (document.createRange && window.getSelection) {
           console.log(elToBeCopied);
           range = document.createRange();
           sel = window.getSelection();
           // unselect any element in the page
           sel.removeAllRanges();
           try {
               range.selectNodeContents(elToBeCopied);
               sel.addRange(range);
           } catch (e) {
               range.selectNode(elToBeCopied);
               sel.addRange(range);
           }
           document.execCommand('copy');
       }
       sel.removeAllRanges();
       msj_tablacopiadacorrectamente();
    };
    //btnCopyText.addEventListener('click', () => copyEl(elText));
    btnCopyTable.addEventListener('dblclick', () => copyEl(elTable));
    //fin copias tabla detalles modulo
    obtenertiposordenescompra(data.ordencompra.Tipo, data.ordencompra.Almacen);
    seleccionartipoordencompra(data);
  }).fail( function() {
    msj_errorajax();
    $('.page-loader-wrapper').css('display', 'none');
  })
}
async function seleccionartipoordencompra(data){
  await retraso();
  $("#tipo").val(data.ordencompra.Tipo).change();
  $("#tipo").select2();
  setTimeout(function(){$("#folio").focus();},500);
  mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
  $('.page-loader-wrapper').css('display', 'none');
}
//guardar modificación
$("#btnGuardarModificacion").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    var numerofilas = $("#numerofilas").val();
    if(parseInt(numerofilas) > 0 && parseInt(numerofilas) < 500){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:ordenes_compra_guardar_modificacion,
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
      msj_erroralmenosunaentrada();
    }
  }else{
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento){
  $.get(ordenes_compra_obtener_datos_envio_email,{documento:documento}, function(data){
    $("#textomodalenviarpdfemail").html("Enviar email Orden de Compra No." + documento);
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
    if(agregarreferenciaenasuntocorreo == 'S'){
      var asunto = "ORDEN DE COMPRA NO. " + documento +" DE "+ nombreempresa + " " + data.ordencompra.Referencia;
    }else{
      var asunto = "ORDEN DE COMPRA NO. " + documento +" DE "+ nombreempresa;
    }
    $("#emailasunto").val(asunto);
    $("#emailmensaje").val(asunto);
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
      url:ordenes_compra_enviar_pdfs_email,
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
          url: ordenes_compra_buscar_folio_string_like,
          data: function (d) {
              d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'Orden', name: 'Orden', orderable: false, searchable: true },
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
  //seleccionar al dar doble click
  $('#tablafoliosencontrados tbody').on('dblclick', 'tr', function () {
    tablafolenc = $("#tablafoliosencontrados").DataTable();
    var data = tablafolenc.row( this ).data();
    agregararraypdf(data.Orden);
  });
}
//generar documento en iframe
function generardocumentoeniframe(Orden){
  var arraypdf = new Array();
  var folios = [Orden];
  arraypdf.push(folios);
  var form_data = new FormData();
  form_data.append('arraypdf', arraypdf);
  form_data.append('tipogeneracionpdf', 0);
  form_data.append('numerodecimalesdocumento', 2);
  form_data.append('imprimirdirectamente', 1);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:ordenes_compra_generar_pdfs,
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
