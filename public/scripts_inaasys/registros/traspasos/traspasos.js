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
  $.get(traspasos_obtener_ultimo_folio,{serie:serie}, function(folio){
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
//validar si se debe mostrar o no el buscador de productos
function mostrarbuscadorcodigoproducto(){
  var almacen = $("#almacende").val();
  var almacenforaneo = $("#almacena").val();
  var orden = $("#orden").val();
  if(almacen != "" && (almacenforaneo != "" || orden != "")){
    $("#divbuscarcodigoproducto").show();
    $("#divimportarpartidas").show();
    $("#divlistarrequisiciones").show();
    $("#divlistarcotizaciones").show();
  }else{
    $("#divbuscarcodigoproducto").hide();
    $("#divimportarpartidas").hide();
    $("#divlistarrequisiciones").hide();
    $("#divlistarcotizaciones").hide();
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
        url: traspasos_obtener,
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
    obtenerdatos(data.Traspaso);
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
  var partidasexcel = $('#partidasexcel')[0].files[0];
  var numeroalmacen = $("#numeroalmacende").val();
  var almacende = $("#almacende").val();
  var almacenforaneo = $("#almacena").val();
  var orden = $("#orden").val();
  var form_data = new FormData();
  form_data.append('partidasexcel', partidasexcel);  
  form_data.append('numeroalmacen', numeroalmacen);
  form_data.append('almacende', almacende);
  form_data.append('almacenforaneo', almacenforaneo);
  form_data.append('orden', orden);
  form_data.append('contadorproductos', contadorproductos);
  form_data.append('contadorfilas', contadorfilas);
  form_data.append('arraycodigospartidas', arraycodigospartidas);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:traspasos_cargar_partidas_excel,
    data: form_data,
    type: 'POST',
    contentType: false,
    processData: false,
    success: function (data) {
      contadorfilas = data.contadorfilas;
      contadorproductos = data.contadorproductos;
      $("#tablaproductostraspasos tbody").append(data.filasdetallestraspaso);
      comprobarfilas();
      calculartotal();
      $("#codigoabuscar").val("");
      //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
      $(".inputnextdet").keypress(function (e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          var index = $(this).index(".inputnextdet");          
          $(".inputnextdet").eq(index + 1).focus().select(); 
        }
      });
    },
    error: function (data) {
      console.log(data);
    }
  });                      
});
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
        url: traspasos_obtener_series_documento
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
  $.get(traspasos_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie}, function(folio){
      $("#folio").val(folio);
      $("#serie").val(Serie);
      $("#serietexto").html("Serie: "+Serie);
      mostrarformulario();
  }) 
}
//obtener almacenes
function obteneralmacenes(){
  ocultarformulario();
  var tablaalmacenes = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">De Almacén</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoalmacen" class="tbllistadoalmacen table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Almacen</th>'+
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
            url: traspasos_obtener_almacenes,
            data: function (d) {
                d.numeroalmacena = $("#numeroalmacena").val();
            }
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
    //seleccionar registro al dar doble click
    $('#tbllistadoalmacen tbody').on('dblclick', 'tr', function () {
        var data = talm.row( this ).data();
        seleccionaralmacen(data.Numero, data.Nombre);
    }); 
} 
//obtener datos de remision seleccionada
function seleccionaralmacen(Numero, Nombre){
  var numeroalmacendeanterior = $("#numeroalmacendeanterior").val();
  var numeroalmacende = Numero;
  if(numeroalmacendeanterior != numeroalmacende){
    $("#numeroalmacende").val(Numero);
    $("#numeroalmacendeanterior").val(Numero);
    $("#almacende").val(Nombre);
    if(Nombre != null){
      $("#textonombrealmacende").html(Nombre.substring(0, 40));
    }
    mostrarformulario();
    mostrarbuscadorcodigoproducto();
    //recargar existencias actuales del nuevo almacen seleccionado en data-parsley-existencias de las partidas
    $("tr.filasproductos").each(function () {
      $('.cantidadpartida', this).change();
    });
  }
}
//obtener almacenes foraneos
function obteneralmacenesforaneos(){
    ocultarformulario();
    var tablaalmacenes = '<div class="modal-header '+background_forms_and_modals+'">'+
                              '<h4 class="modal-title">Almacén Foráneo</h4>'+
                          '</div>'+
                          '<div class="modal-body">'+
                              '<div class="row">'+
                                  '<div class="col-md-12">'+
                                      '<div class="table-responsive">'+
                                          '<table id="tbllistadoalmacen" class="tbllistadoalmacen table table-bordered table-striped table-hover" style="width:100% !important">'+
                                              '<thead class="'+background_tables+'">'+
                                                  '<tr>'+
                                                      '<th>Operaciones</th>'+
                                                      '<th>Numero</th>'+
                                                      '<th>Almacen</th>'+
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
      var talmfor = $('#tbllistadoalmacen').DataTable({
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
            url: traspasos_obtener_almacenes_foraneos,
            data: function (d) {
              d.numeroalmacende = $("#numeroalmacende").val();
            }
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
      //seleccionar registro al dar doble click
      $('#tbllistadoalmacen tbody').on('dblclick', 'tr', function () {
          var data = talmfor.row( this ).data();
          seleccionaralmacenforaneo(data.Numero, data.Nombre);
      }); 
  } 
//obtener datos de remision seleccionada
function seleccionaralmacenforaneo(Numero, Nombre){
  var numeroalmacenaanterior = $("#numeroalmacenaanterior").val();
  var numeroalmacena = Numero;
  if(numeroalmacenaanterior != numeroalmacena){
    //colocar requisicion y cotizacion en vacio
    $("#requisicion").val("");
    $("#cotizacion").val("");
    if($("#numeroalmacenaanterior").val() == ""){
      //eliminar filas y regresar contadores a 0 porque los calculos para trapasos a otro almacen son de forma diferente
      $("#tablaproductostraspasos tbody").html("");
      contadorproductos=0;
      contadorfilas = 0;
      $("#numerofilas").val(0);
    }
    //colocar datos y required a los datos del almacen
    $("#numeroalmacena").val(Numero).attr('required', 'required');
    $("#numeroalmacenaanterior").val(Numero).attr('required', 'required');
    $("#almacena").val(Nombre).attr('required', 'required');
    if(Nombre != null){
      $("#textonombrealmacena").html(Nombre.substring(0, 40));
    }
    //colocar en blanco datos orden y quitarles el required
    $("#orden").val("").removeAttr('required');
    $("#ordenanterior").val("").removeAttr('required');
    $("#textonombreorden").html("");
    $("#fechaorden").val("").removeAttr('required');
    $("#clienteorden").val("").removeAttr('required');
    $("#tipo").val("").removeAttr('required');
    $("#unidad").val("").removeAttr('required');
    $("#statusorden").val("").removeAttr('required');
    mostrarformulario();
    //mostrar el buscador de codigos
    mostrarbuscadorcodigoproducto();
  }
}
//obtener almacenes foraneos
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
            url: traspasos_obtener_ordenes_trabajo
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
//obtener datos de orden seleccionada
function seleccionarordentrabajo(Orden, Fecha, Cliente, Tipo, Unidad, StatusOrden){
  var ordenanterior = $("#ordenanterior").val();
  var orden = Orden;
  if(ordenanterior != orden){
    //colocar requisicion y cotizacion en vacio
    $("#requisicion").val("");
    $("#cotizacion").val("");
    if($("#ordenanterior").val() == ""){
      //eliminar filas y regresar contadores a 0 poruque los calculos para traspasos a ordenes de trabajo son de forma diferente
      $("#tablaproductostraspasos tbody").html("");
      contadorproductos=0;
      contadorfilas = 0;
      $("#numerofilas").val(0);
    }
    //colocar datos de orden y required
    $("#orden").val(Orden).attr('required', 'required');
    $("#ordenanterior").val(Orden).attr('required', 'required');
    if(Orden != null){
      $("#textonombreorden").html(Orden.substring(0, 40));
    }
    $("#fechaorden").val(Fecha).attr('required', 'required');
    $("#clienteorden").val(Cliente).attr('required', 'required');
    $("#tipo").val(Tipo).attr('required', 'required');
    $("#unidad").val(Unidad).attr('required', 'required')
    $("#statusorden").val(StatusOrden).attr('required', 'required');
    //colocar en blanco alamcen foraneo y quitarle el required
    $("#numeroalmacena").val("").removeAttr('required');
    $("#numeroalmacenaanterior").val("").removeAttr('required');
    $("#almacena").val("").removeAttr('required');
    $("#textonombrealmacena").html("");
    mostrarformulario();
    //mostrar el buscador de codigos
    mostrarbuscadorcodigoproducto();
  }
}
//obtener por numero
function obteneralmacendepornumero(){
  var numeroalmacendeanterior = $("#numeroalmacendeanterior").val();
  var numeroalmacende = $("#numeroalmacende").val();
  if(numeroalmacendeanterior != numeroalmacende){
    if($("#numeroalmacende").parsley().isValid()){
      var numeroalmacena = $("#numeroalmacena").val();
      $.get(traspasos_obtener_almacen_de_por_numero, {numeroalmacende:numeroalmacende,numeroalmacena:numeroalmacena}, function(data){
          $("#numeroalmacende").val(data.numero);
          $("#numeroalmacendeanterior").val(data.numero);
          $("#almacende").val(data.nombre);
          if(data.nombre != null){
            $("#textonombrealmacende").html(data.nombre.substring(0, 40));
          }
          mostrarformulario();
          mostrarbuscadorcodigoproducto();
          //recargar existencias actuales del nuevo almacen seleccionado en data-parsley-existencias de las partidas
          $("tr.filasproductos").each(function () {
            $('.cantidadpartida', this).change();
          });
      }) 
    }
  }
}
//regresar numero
function regresarnumeroalmacende(){
  var numeroalmacendeanterior = $("#numeroalmacendeanterior").val();
  $("#numeroalmacende").val(numeroalmacendeanterior);
}
//obtener por numero
function obteneralmacenapornumero(){
  var numeroalmacenaanterior = $("#numeroalmacenaanterior").val();
  var numeroalmacena = $("#numeroalmacena").val();
  if(numeroalmacenaanterior != numeroalmacena){
    if($("#numeroalmacena").parsley().isValid()){
      var numeroalmacende = $("#numeroalmacende").val();
      if(numeroalmacena != numeroalmacende){
        $.get(traspasos_obtener_almacen_a_por_numero, {numeroalmacena:numeroalmacena,numeroalmacende:numeroalmacende}, function(data){
          //colocar requisicion y cotizacion en vacio
          $("#requisicion").val("");
          $("#cotizacion").val("");
          if($("#numeroalmacenaanterior").val() == ""){
            //eliminar filas y regresar contadores a 0 porque los calculos para trapasos a otro almacen son de forma diferente
            $("#tablaproductostraspasos tbody").html("");
            contadorproductos=0;
            contadorfilas = 0;
            $("#numerofilas").val(0);
          }
          //colocar datos y required a los datos del almacen
          $("#numeroalmacena").val(data.numero).attr('required', 'required');
          $("#numeroalmacenaanterior").val(data.numero).attr('required', 'required');
          $("#almacena").val(data.nombre).attr('required', 'required');
          if(data.nombre != null){
            $("#textonombrealmacena").html(data.nombre.substring(0, 40));
          }
          //colocar en blanco datos orden y quitarles el required
          $("#orden").val("").removeAttr('required');
          $("#ordenanterior").val("").removeAttr('required');
          $("#textonombreorden").html("");
          $("#fechaorden").val("").removeAttr('required');
          $("#clienteorden").val("").removeAttr('required');
          $("#tipo").val("").removeAttr('required');
          $("#unidad").val("").removeAttr('required');
          $("#statusorden").val("").removeAttr('required');
          mostrarformulario();
          //mostrar el buscador de codigos
          mostrarbuscadorcodigoproducto();
        }) 
      }else{
        regresarnumeroalmacena();
      }
    }
  }
}
//regresar numero
function regresarnumeroalmacena(){
  var numeroalmacenaanterior = $("#numeroalmacenaanterior").val();
  $("#numeroalmacena").val(numeroalmacenaanterior);
}
//obtener por folio
function obtenerordenporfolio(){
  var ordenanterior = $("#ordenanterior").val();
  var orden = $("#orden").val();
  if(ordenanterior != orden){
    if($("#orden").parsley().isValid()){
      var orden = $("#orden").val();
      $.get(traspasos_obtener_orden_trabajo_por_folio, {orden:orden}, function(data){
        //colocar requisicion y cotizacion en vacio
        $("#requisicion").val("");
        $("#cotizacion").val("");
        if($("#ordenanterior").val() == ""){
          //eliminar filas y regresar contadores a 0 poruque los calculos para traspasos a ordenes de trabajo son de forma diferente
          $("#tablaproductostraspasos tbody").html("");
          contadorproductos=0;
          contadorfilas = 0;
          $("#numerofilas").val(0);
        }
        //colocar datos de orden y required
        $("#orden").val(data.orden).attr('required', 'required');
        $("#ordenanterior").val(data.orden).attr('required', 'required');
        if(data.orden != null){
          $("#textonombreorden").html(data.orden.substring(0, 40));
        }
        $("#fechaorden").val(data.fecha).attr('required', 'required');
        $("#clienteorden").val(data.cliente).attr('required', 'required');
        $("#tipo").val(data.tipo).attr('required', 'required');
        $("#unidad").val(data.unidad).attr('required', 'required')
        $("#statusorden").val(data.statusorden).attr('required', 'required');
        //colocar en blanco alamcen foraneo y quitarle el required
        $("#numeroalmacena").val("").removeAttr('required');
        $("#numeroalmacenaanterior").val("").removeAttr('required');
        $("#almacena").val("").removeAttr('required');
        $("#textonombrealmacena").html("");
        mostrarformulario();
        //mostrar el buscador de codigos
        mostrarbuscadorcodigoproducto();
      }) 
    }
  }
}
//regresar folio
function regresarfolioorden(){
  var ordenanterior = $("#ordenanterior").val();
  $("#orden").val(ordenanterior);
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
  var tcots = $('#tbllistadocotizacion').DataTable({
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
          url: traspasos_obtener_cotizaciones,
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
  //seleccionar registro al dar doble click
  $('#tbllistadocotizacion tbody').on('dblclick', 'tr', function () {
      var data = tcots.row( this ).data();
      seleccionarcotizacion(data.Folio, data.Cotizacion);
  });  
} 
//obtener todos los datos de la cotizacion seleccionada
function seleccionarcotizacion(Folio, Cotizacion){
  $('.page-loader-wrapper').css('display', 'block');
  $("#tablaproductostraspasos tbody").html("");
  var numeroalmacende = $("#numeroalmacende").val();
  $.get(traspasos_obtener_cotizacion, {Folio:Folio, Cotizacion:Cotizacion, numeroalmacende:numeroalmacende}, function(data){
      $("#cotizacion").val(Cotizacion);
      $("#requisicion").val("");
      $("#observaciones").val(data.cotizacion.Obs);
      $("#tablaproductostraspasos tbody").html(data.filasdetallescotizacion);
      $("#importe").val(data.importe);
      $("#descuento").val(data.descuento);
      $("#subtotal").val(data.subtotal);
      $("#iva").val(data.iva);
      $("#total").val(data.total);  
      //detalles
      $("#numerofilas").val(data.numerodetallescotizacion);
      //colocar valores a contadores
      contadorproductos = data.contadorproductos;
      contadorfilas = data.contadorfilas;
      totalesfilas();
      mostrarformulario();
      $('.page-loader-wrapper').css('display', 'none');
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
//listar todas las requisiciones
function listarrequisiciones(){
  ocultarformulario();
  var tablarequisiciones ='<div class="modal-header '+background_forms_and_modals+'">'+
                              '<h4 class="modal-title">Requisiciones</h4>'+
                          '</div>'+
                          '<div class="modal-body">'+
                              '<div class="row">'+
                                  '<div class="col-md-12">'+
                                      '<div class="table-responsive">'+
                                          '<table id="tbllistadorequisicion" class="tbllistadorequisicion table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                              '<thead class="'+background_tables+'">'+
                                                  '<tr>'+
                                                      '<th>Operaciones</th>'+
                                                      '<th>Requisicion</th>'+
                                                      '<th>Fecha</th>'+
                                                      '<th>Orden</th>'+
                                                      '<th>Obs</th>'+
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
  $("#contenidomodaltablas").html(tablarequisiciones);
  var treqs = $('#tbllistadorequisicion').DataTable({
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
          url: traspasos_obtener_requisiciones,
          data: function (d) {
              d.numerocliente = $("#numeroclientefacturaa").val();
          }
      },
      columns: [
        { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
        { data: 'Requisicion', name: 'Requisicion' },
        { data: 'Fecha', name: 'Fecha' },
        { data: 'Orden', name: 'Orden', orderable: false, searchable: false },
        { data: 'Obs', name: 'Obs', orderable: false, searchable: false }
      ],
      "initComplete": function() {
          var $buscar = $('div.dataTables_filter input');
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadorequisicion').DataTable().search( this.value ).draw();
              }
          });
      },
  });  
  //seleccionar registro al dar doble click
  $('#tbllistadorequisicion tbody').on('dblclick', 'tr', function () {
      var data = treqs.row( this ).data();
      seleccionarrequisicion(data.Folio, data.Requisicion);
  });   
} 
//obtener todos los datos de la requisicion seleccionada
function seleccionarrequisicion(Folio, Requisicion){
  $('.page-loader-wrapper').css('display', 'block');
  $("#tablaproductostraspasos tbody").html("");
  var numeroalmacende = $("#numeroalmacende").val();
  $.get(traspasos_obtener_requisicion, {Folio:Folio, Requisicion:Requisicion, numeroalmacende:numeroalmacende}, function(data){
      $("#requisicion").val(Requisicion);
      $("#cotizacion").val("");
      $("#observaciones").val(data.requisicion.Obs);
      $("#tablaproductostraspasos tbody").html(data.filasdetallesrequisicion);
      $("#importe").val(data.importe);
      $("#descuento").val(data.descuento);
      $("#subtotal").val(data.subtotal);
      $("#iva").val(data.iva);
      $("#total").val(data.total);  
      //detalles
      $("#numerofilas").val(data.numerodetallesrequisicion);
      //colocar valores a contadores
      contadorproductos = data.contadorproductos;
      contadorfilas = data.contadorfilas;
      totalesfilas();
      mostrarformulario();
      $('.page-loader-wrapper').css('display', 'none');
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
        url: traspasos_obtener_productos,
        data: function (d) {
          d.codigoabuscar = $("#codigoabuscar").val();
          d.numeroalmacende = $("#numeroalmacende").val();
          d.tipooperacion = $("#tipooperacion").val();
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
        agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, number_format(round(data.Impuesto, numerodecimales), numerodecimales, '.', ''), data.SubTotal, data.Existencias, tipooperacion);
    });
}
function obtenerproductoporcodigo(){
  var codigoabuscar = $("#codigoabuscar").val();
  var tipooperacion = $("#tipooperacion").val();
  var numeroalmacende = $("#numeroalmacende").val();
  $.get(traspasos_obtener_producto_por_codigo,{codigoabuscar:codigoabuscar,numeroalmacende:numeroalmacende}, function(data){
    if(parseInt(data.contarproductos) > 0){
      agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Impuesto, data.SubTotal, data.Existencias, tipooperacion);
    }else{
      msjnoseencontroningunproducto();
    }
  }) 
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
function totalesfilas(){
  $("tr.filasproductos").each(function () { 
      // obtener los datos de la fila:
      var cantidadpartida = $(".cantidadpartida", this).val();
      var preciopartida = $('.preciopartida', this).val();
      var importepartida = $('.importepartida', this).val();
      var descuentopesospartida = $('.descuentopesospartida', this).val();
      var subtotalpartida = $('.subtotalpartida', this).val();
      var ivaporcentajepartida = $('.ivaporcentajepartida', this).val();
      var ivapesospartida = $('.ivapesospartida', this).val();
      var totalpesospartida = $('.totalpesospartida', this).val(); 
      var utilidadpartida = $(".utilidadpartida", this).val();
      var costopartida = $(".costopartida", this).val();
      var costototalpartida = $(".costototalpartida ", this).val();
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
      //costo total
      costototalpartida  = new Decimal(costopartida).times(cantidadpartida);
      $('.costototalpartida', this).val(number_format(round(costototalpartida, numerodecimales), numerodecimales, '.', ''));
      //utilidad de la partida
      utilidadpartida = new Decimal(subtotalpartida).minus(costototalpartida);
      $(".utilidadpartida", this).val(number_format(round(utilidadpartida, numerodecimales), numerodecimales, '.', ''));
  });
  calculartotal();
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
      var subtotalpartida = $('.subtotalpartida', this).val();
      var ivaporcentajepartida = $('.ivaporcentajepartida', this).val();
      var ivapesospartida = $('.ivapesospartida', this).val();
      var totalpesospartida = $('.totalpesospartida', this).val(); 
      var utilidadpartida = $(".utilidadpartida", this).val();
      var costopartida = $(".costopartida", this).val();
      var costototalpartida = $(".costototalpartida ", this).val();
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
      //costo total
      costototalpartida  = new Decimal(costopartida).times(cantidadpartida);
      $('.costototalpartida', this).val(number_format(round(costototalpartida, numerodecimales), numerodecimales, '.', ''));
      //utilidad de la partida
      utilidadpartida = new Decimal(subtotalpartida).minus(costototalpartida);
      $(".utilidadpartida", this).val(number_format(round(utilidadpartida, numerodecimales), numerodecimales, '.', ''));
      calculartotal();
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
      calculartotalesfilas(fila);
      //verificar si el almacen principal cuenta con las existencias requeridas
      var numeroalmacende = $("#numeroalmacende").val();
      var codigo = $(".codigoproductopartida", this).val();
      comprobarexistenciasenbd(fila, tipo, numeroalmacende, codigo).then(existencias=>{
        if(tipo == "alta"){
          var dataparsleymax = existencias;
        }else if(tipo == "modificacion"){
          var dataparsleymax = new Decimal(existencias).plus($("#filaproducto"+fila+" .cantidadpartidadb").val());
        }
        $("#filaproducto"+fila+" .cantidadpartida").attr('data-parsley-existencias',dataparsleymax);
        //$("#formparsley").parsley().validate();
        $('.cantidadpartida', this).parsley().validate();
      })
      //verificar si el almacen foraneo cuenta con las existencias requeridad
      var numeroalmacena = $("#numeroalmacena").val();
      if(numeroalmacena != ''){
        var tipooperacion = $("#tipooperacion").val();
        if(tipooperacion == 'modificacion'){
          var cantidadpartidadb = $(".cantidadpartidadb", this).val();
          var cantidadpartida = $(".cantidadpartida", this).val();
          if(cantidadpartida < cantidadpartidadb){
              comprobarexistenciasenbdalmacenforaneo(fila, tipo, numeroalmacena, codigo).then(existencias=>{
                var regresarexistenciasalmacenprincipal = new Decimal(cantidadpartidadb).minus(cantidadpartida);
                if(existencias < regresarexistenciasalmacenprincipal){
                  //mensaje error se require al menos una entrada de una partida
                  toastr.error( "Error, el almacén foráneo no cuenta con existencias suficientes", "Mensaje", {
                            "timeOut": "9500",
                            "progressBar": true,
                            "extendedTImeout": "5000"
                  });
                  $("#btnGuardarModificacion").hide();
                }else{
                  $("#btnGuardarModificacion").show();
                }
              })
          }else{
            $("#btnGuardarModificacion").show();
          }
        }
      }
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
      //if(multiplicaciondescuentoporcentajepartida.d[0] > parseInt(0)){
        var descuentoporcentajepartida = new Decimal(multiplicaciondescuentoporcentajepartida/importepartida);
        $('.descuentoporcentajepartida', this).val(number_format(round(descuentoporcentajepartida, numerodecimales), numerodecimales, '.', ''));
        calculartotalesfilas(fila);
      //}
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
      //if(multiplicaciondescuentopesospartida.d[0] > parseInt(0)){
        var descuentopesospartida = new Decimal(multiplicaciondescuentopesospartida/100);
        $('.descuentopesospartida', this).val(number_format(round(descuentopesospartida, numerodecimales), numerodecimales, '.', ''));
        calculartotalesfilas(fila);
     // }
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
  var costo = 0;
  var utilidad = 0;
  $("tr.filasproductos").each(function(){
    importe= new Decimal(importe).plus($(".importepartida", this).val());
    descuento = new Decimal(descuento).plus($(".descuentopesospartida", this).val());
    subtotal= new Decimal(subtotal).plus($(".subtotalpartida", this).val());
    iva = new Decimal(iva).plus($(".ivapesospartida", this).val());
    total = new Decimal(total).plus($(".totalpesospartida", this).val());
    costo = new Decimal(costo).plus($(".costopartida", this).val());
    utilidad = new Decimal(utilidad).plus($(".utilidadpartida", this).val());
  }); 
  $("#importe").val(number_format(round(importe, numerodecimales), numerodecimales, '.', ''));
  $("#descuento").val(number_format(round(descuento, numerodecimales), numerodecimales, '.', ''));
  $("#subtotal").val(number_format(round(subtotal, numerodecimales), numerodecimales, '.', ''));
  $("#iva").val(number_format(round(iva, numerodecimales), numerodecimales, '.', ''));
  $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
  $("#costo").val(number_format(round(costo, numerodecimales), numerodecimales, '.', ''));
  $("#utilidad").val(number_format(round(utilidad, numerodecimales), numerodecimales, '.', ''));
}
//funcion asincrona para buscar existencias de la partida
function comprobarexistenciasenbd(fila, tipo, numeroalmacende, codigo){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $.get(traspasos_obtener_existencias_partida,{'numeroalmacende':numeroalmacende,'codigo':codigo},existencias=>{
        return ejecuta(existencias);
      })
    },500);
  })
}
//function asincrona para buscar existencias actuales en bd de la partida
function comprobarexistenciasenbdalmacenforaneo(fila, tipo, numeroalmacena, codigo){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $.get(traspasos_obtener_existencias_almacen_foraneo,{'numeroalmacena':numeroalmacena,'codigo':codigo},existencias=>{
        return ejecuta(existencias);
      })
    },500);
  })
}
//agregar una fila en la tabla de precios productos
var contadorproductos=0;
var contadorfilas = 0;
function agregarfilaproducto(Codigo, Producto, Unidad, Costo, Impuesto, SubTotal, Existencias, CostoDeLista, tipooperacion){
  $('.page-loader-wrapper').css('display', 'block');
  var result = evaluarproductoexistente(Codigo);
  if(result == false){
    var almacen = $("#almacende").val();
    var almacenforaneo = $("#almacena").val();
    var orden = $("#orden").val();
      if(almacen != "" &&  orden != ""){
        var multiplicacioncostoimpuesto =  new Decimal(SubTotal).times(Impuesto);      
        var ivapesos = new Decimal(multiplicacioncostoimpuesto/100);
        var total = new Decimal(SubTotal).plus(ivapesos);
        var preciopartida = SubTotal;
        var parsleyutilidad = numerocerosconfiguradosinputnumberstep;
      }else if(almacen != "" &&  almacenforaneo != ""){
        var multiplicacioncostoimpuesto =  new Decimal(Costo).times(Impuesto);      
        var ivapesos = new Decimal(multiplicacioncostoimpuesto/100);
        var total = new Decimal(Costo).plus(ivapesos);
        var preciopartida = Costo;
        var parsleyutilidad = '000000'; 
      }
      var tipo = "alta";
      var fila=   '<tr class="filasproductos" id="filaproducto'+contadorproductos+'">'+
                          '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('+contadorproductos+')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                          '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]">'+Codigo+'</td>'+
                          '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'+Producto+'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'+
                          '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'+Unidad+'" readonly data-parsley-length="[1, 5]">'+Unidad+'</td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-existencias="'+Existencias+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');cambiodecantidadopreciopartida('+contadorfilas+',\''+tipo +'\');"></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');cambiodecantidadopreciopartida('+contadorfilas+',\''+tipo +'\');"></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('+contadorfilas+');"></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('+contadorfilas+');"></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'+Impuesto+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'+number_format(round(ivapesos, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'+number_format(round(total, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-utilidad="0.'+parsleyutilidad+'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                          '<td class="tdmod"><input type="text" class="form-control divorinputmodxl observacionespartida" name="observacionespartida[]" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'+
                          '<td class="tdmod"><input type="text" class="form-control divorinputmodsm requisicionpartida" name="requisicionpartida[]" readonly data-parsley-length="[1, 20]"></td>'+
                          '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cotizacionpartida" name="cotizacionpartida[]" readonly data-parsley-length="[1, 20]"></td>'+
                          '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly data-parsley-length="[1, 3]"></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'+number_format(round(CostoDeLista, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm tipodecambiopartida" name="tipodecambiopartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                  '</tr>';
      contadorproductos++;
      contadorfilas++;
      $("#tablaproductostraspasos").append(fila);
      mostrarformulario();      
      comprobarfilas();
      calculartotal();
      $("#codigoabuscar").val("");
      //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
      $(".inputnextdet").keypress(function (e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          var index = $(this).index(".inputnextdet");          
          $(".inputnextdet").eq(index + 1).focus().select(); 
        }
      });
      $('.page-loader-wrapper').css('display', 'none');
  }else{
    msj_errorproductoyaagregado();
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
  var numerofilas = $("#tablaproductostraspasos tbody tr").length;
  $("#numerofilas").val(numerofilas);
}
//renumerar las filas de la orden de compra
function renumerarfilas(){
  var lista;
  var tipo = "alta";
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("cantidadpartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+');cambiodecantidadopreciopartida('+i+',\''+tipo +'\')');
  }
  //renumero el precio de la partida
  lista = document.getElementsByClassName("preciopartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+');cambiodecantidadopreciopartida('+i+',\''+tipo +'\')');
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
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
}  
//alta
function alta(){
  $("#titulomodal").html('Alta Traspaso');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
    var tabs ='<div class="col-md-12">'+
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Traspaso <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                    '<input type="text" class="form-control inputnext" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                    '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                    '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                    '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>De Almacén <span class="label label-danger" id="textonombrealmacende"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="botonobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="numeroalmacende" id="numeroalmacende" required data-parsley-type="integer" autocomplete="off" >'+
                            '<input type="hidden" class="form-control" name="numeroalmacendeanterior" id="numeroalmacendeanterior" required data-parsley-type="integer" >'+
                            '<input type="hidden" class="form-control" name="almacende" id="almacende" required readonly>'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+    
                     '</table>'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>A Foráneo <span class="label label-danger" id="textonombrealmacena"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="botonobteneralmacenesforaneos" onclick="obteneralmacenesforaneos()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="numeroalmacena" id="numeroalmacena"  required data-parsley-type="integer" autocomplete="off">'+
                            '<input type="hidden" class="form-control" name="numeroalmacenaanterior" id="numeroalmacenaanterior"  required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="almacena" id="almacena"  readonly>'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+    
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>Fecha </label>'+
                    '<input type="datetime-local" class="form-control inputnext" name="fecha" id="fecha"  required data-parsley-excluded="true" onkeydown="return false">'+
                    '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                  '</div>'+
                '</div>'+
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Orden Trabajo <span class="label label-danger" id="textonombreorden"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="botonobtenerordenestrabajo" onclick="obtenerordenestrabajo()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="orden" id="orden" onkeyup="tipoLetra(this);" autocomplete="off" >'+
                            '<input type="hidden" class="form-control" name="ordenanterior" id="ordenanterior" >'+
                            '<input type="hidden" class="form-control" name="fechaorden" id="fechaorden"  readonly>'+
                            '<input type="hidden" class="form-control" name="tipo" id="tipo"  readonly>'+
                            '<input type="hidden" class="form-control" name="unidad" id="unidad"  readonly>'+
                          '</div>'+
                        '</td>'+
                      '</tr> '+   
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Status Orden</label>'+
                    '<input type="text" class="form-control inputnext" name="statusorden" id="statusorden"  required readonly onkeyup="tipoLetra(this);" autocomplete="off">'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Cliente </label>'+
                    '<input type="text" class="form-control inputnext" name="clienteorden" id="clienteorden"  required readonly onkeyup="tipoLetra(this);" autocomplete="off">'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Referencia </label>'+
                    '<input type="text" class="form-control inputnext" name="referencia" id="referencia" data-parsley-length="[1, 200]" onkeyup="tipoLetra(this);" autocomplete="off">'+
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
                            '<input type="text" class="form-control inputnext" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+    
                    '</table>'+
                  '</div>'+
                '</div>'+
                '<div class="row">'+
                  '<div class="col-md-2" id="divlistarrequisiciones" hidden>'+
                    '<label>Requisiciones</label>'+
                    '<div class="btn btn-block bg-blue waves-effect" id="btnlistarrequisiciones" onclick="listarrequisiciones()">Ver Requisiciones</div>'+
                    '<input type="hidden" class="form-control" name="requisicion" id="requisicion" readonly>'+
                  '</div>'+ 
                  '<div class="col-md-2" id="divlistarcotizaciones" hidden>'+
                    '<label>Cotizaciones</label>'+
                    '<div class="btn btn-block bg-blue waves-effect" id="btnlistarcotizaciones" onclick="listarcotizaciones()">Ver Cotizaciones</div>'+
                    '<input type="hidden" class="form-control" name="cotizacion" id="cotizacion" readonly>'+
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
                        '<table id="tablaproductostraspasos" class="table table-bordered tablaproductostraspasos">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="'+background_tables+'">Código</th>'+
                              '<th class="'+background_tables+'"><div style="width:200px !important;">Descripción</div></th>'+
                              '<th class="'+background_tables+'">Unidad</th>'+
                              '<th class="customercolortheadth">Cantidad</th>'+
                              '<th class="customercolortheadth">Precio $</th>'+
                              '<th class="'+background_tables+'">Importe $</th>'+
                              '<th class="customercolortheadth">Descuento %</th>'+
                              '<th class="customercolortheadth">Descuento $</th>'+
                              '<th class="'+background_tables+'">SubTotal $</th>'+
                              '<th class="customercolortheadth">Iva %</th>'+
                              '<th class="'+background_tables+'">Iva $</th>'+
                              '<th class="'+background_tables+'">Total $</th>'+
                              '<th class="'+background_tables+'">Costo $</th>'+
                              '<th class="'+background_tables+'">Costo Total</th>'+
                              '<th class="bg-amber">Utilidad $</th>'+
                              '<th class="customercolortheadth">Observaciones</th>'+
                              '<th class="'+background_tables+'">Requisición</th>'+
                              '<th class="'+background_tables+'">Cotización</th>'+
                              '<th class="'+background_tables+'">Moneda</th>'+
                              '<th class="'+background_tables+'">Costo de Lista</th>'+
                              '<th class="'+background_tables+'">Tipo de Cambio</th>'+
                            '</tr>'+
                          '</thead>'+
                          '<tbody>'+           
                          '</tbody>'+
                        '</table>'+
                      '</div>'+
                    '</div>'+ 
                    '<div class="row" id="divimportarpartidas" hidden>'+
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
                        '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
                      '</div>'+ 
                      '<div class="col-md-3 col-md-offset-3">'+
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
                          '<tr hidden>'+
                            '<td style="padding:0px !important;">Costo</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                          '<tr hidden>'+
                            '<td style="padding:0px !important;">Utilidad</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="utilidad" id="utilidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                        '</table>'+
                      '</div>'+
                    '</div>'+   
                  '</div>'+ 
                '</div>'+ 
              '</div>';
  $("#tabsform").html(tabs);
  //mostrar mensaje de bajar plantilla
  $('[data-toggle="tooltip"]').tooltip({
    container: 'body'
  });
  $("#serie").val(serieusuario);
  $("#serietexto").html("Serie: "+serieusuario);
  obtenultimonumero();
  asignarfechaactual();
  //almacen de
  $("#almacende").val(almacende);
  if(almacende != null){
    $("#textonombrealmacende").html(almacende.substring(0, 40));
  }
  $("#numeroalmacende").val(numeroalmacende);
  $("#numeroalmacendeanterior").val(numeroalmacende);
  //almacen a
  $("#almacena").val("");
  $("#textonombrealmacena").html("");
  $("#numeroalmacena").val("");
  $("#numeroalmacenaanterior").val("");
  //orden
  $("#orden").val("");
  $("#textonombreorden").html("");
  $("#ordenanterior").val("");
  //mostrar botones de seleccion
  $("#botonobteneralmacenes").show();
  $("#botonobteneralmacenesforaneos").show();
  $("#botonobtenerordenestrabajo").show();
  //reiniciar contadores
  contadorproductos=0;
  contadorfilas = 0;
  $("#numerofilas").val("0");
  //ocultar buscador de productos
  mostrarbuscadorcodigoproducto();
  //asignar el tipo de operacion que se realizara
  $("#tipooperacion").val("alta");
  //activar busquedas
  $("#codigoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerproductoporcodigo();
    }
  });
  //activar busqueda para almacen de
  $('#numeroalmacende').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obteneralmacendepornumero();
    }
  });
  //regresar numero almacen de
  $('#numeroalmacende').on('change', function(e) {
    regresarnumeroalmacende();
  });
  //activar busqueda para almacen a
  $('#numeroalmacena').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obteneralmacenapornumero();
    }
  });
  //regresar numero almacen a
  $('#numeroalmacena').on('change', function(e) {
    regresarnumeroalmacena();
  });
  //activar busqueda para ordenes
  $('#orden').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerordenporfolio();
    }
  });
  //regresar folio orden
  $('#orden').on('change', function(e) {
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
  $(".inputnextdet").keypress(function (e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      var index = $(this).index(".inputnextdet");          
      $(".inputnextdet").eq(index + 1).focus().select(); 
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
    if(parseInt(numerofilas) > 0  && parseInt(numerofilas) < 500){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:traspasos_guardar,
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
//bajas
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(traspasodesactivar){
  $.get(traspasos_verificar_baja,{traspasodesactivar:traspasodesactivar}, function(data){
    if(data.Status == 'BAJA'){
      $("#traspasodesactivar").val(0);
      $("#textomodaldesactivar").html('Error, este Traspaso ya fue dado de baja');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{ 
      if(data.resultadofechas != ''){
        $("#traspasodesactivar").val(0);
        $("#textomodaldesactivar").html('Error solo se pueden dar de baja los traspasos del mes actual, fecha del traspaso: ' + data.resultadofechas);
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        if(data.errores != ''){
          $("#traspasodesactivar").val(0);
          $("#textomodaldesactivar").html(data.errores);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else{
          $("#traspasodesactivar").val(traspasodesactivar);
          $("#textomodaldesactivar").html('Estas seguro de dar de baja el traspaso? No'+ traspasodesactivar);
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
      url:traspasos_alta_o_baja,
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
//modificacion
function obtenerdatos(traspasomodificar){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(traspasos_obtener_traspaso,{traspasomodificar:traspasomodificar },function(data){
    $("#titulomodal").html('Modificación Traspaso --- STATUS : ' + data.traspaso.Status);
    //formulario modificacion
    var tabs ='<div class="col-md-12">'+
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Traspaso <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b></label>'+
                    '<input type="text" class="form-control inputnext" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                    '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                    '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                    '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>De Almacén <span class="label label-danger" id="textonombrealmacende"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="botonobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="numeroalmacende" id="numeroalmacende" required data-parsley-type="integer" autocomplete="off" >'+
                            '<input type="hidden" class="form-control" name="numeroalmacendeanterior" id="numeroalmacendeanterior" required data-parsley-type="integer" >'+
                            '<input type="hidden" class="form-control" name="almacende" id="almacende" required readonly>'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+    
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>A Foráneo <span class="label label-danger" id="textonombrealmacena"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="botonobteneralmacenesforaneos" onclick="obteneralmacenesforaneos()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="numeroalmacena" id="numeroalmacena"  required data-parsley-type="integer" autocomplete="off">'+
                            '<input type="hidden" class="form-control" name="numeroalmacenaanterior" id="numeroalmacenaanterior"  required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="almacena" id="almacena"  readonly>'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+    
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>Fecha </label>'+
                    '<input type="datetime-local" class="form-control inputnext" name="fecha" id="fecha"  required data-parsley-excluded="true" onkeydown="return false">'+
                    '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                  '</div>'+
                '</div>'+
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Orden Trabajo <span class="label label-danger" id="textonombreorden"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="botonobtenerordenestrabajo" onclick="obtenerordenestrabajo()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="orden" id="orden" onkeyup="tipoLetra(this);" autocomplete="off" >'+
                            '<input type="hidden" class="form-control" name="ordenanterior" id="ordenanterior" >'+
                            '<input type="hidden" class="form-control" name="fechaorden" id="fechaorden"  readonly>'+
                            '<input type="hidden" class="form-control" name="tipo" id="tipo"  readonly>'+
                            '<input type="hidden" class="form-control" name="unidad" id="unidad"  readonly>'+
                          '</div>'+
                        '</td>'+
                      '</tr> '+   
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Status Orden</label>'+
                    '<input type="text" class="form-control inputnext" name="statusorden" id="statusorden"  required readonly onkeyup="tipoLetra(this);" autocomplete="off">'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Cliente </label>'+
                    '<input type="text" class="form-control inputnext" name="clienteorden" id="clienteorden"  required readonly onkeyup="tipoLetra(this);" autocomplete="off">'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Referencia </label>'+
                    '<input type="text" class="form-control inputnext" name="referencia" id="referencia" data-parsley-length="[1, 200]" onkeyup="tipoLetra(this);" autocomplete="off">'+
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
                            '<input type="text" class="form-control inputnext" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">'+
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
                      '<div class="col-md-12 table-responsive cabecerafija" style="height: 225px;overflow-y: scroll;padding: 0px 0px;">'+
                        '<table id="tablaproductostraspasos" class="table table-bordered tablaproductostraspasos">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="'+background_tables+'">Código</th>'+
                              '<th class="'+background_tables+'"><div style="width:200px !important;">Descripción</div></th>'+
                              '<th class="'+background_tables+'">Unidad</th>'+
                              '<th class="customercolortheadth">Cantidad</th>'+
                              '<th class="customercolortheadth">Precio $</th>'+
                              '<th class="'+background_tables+'">Importe $</th>'+
                              '<th class="customercolortheadth">Descuento %</th>'+
                              '<th class="customercolortheadth">Descuento $</th>'+
                              '<th class="'+background_tables+'">SubTotal $</th>'+
                              '<th class="customercolortheadth">Iva %</th>'+
                              '<th class="'+background_tables+'">Iva $</th>'+
                              '<th class="'+background_tables+'">Total $</th>'+
                              '<th class="'+background_tables+'">Costo $</th>'+
                              '<th class="'+background_tables+'">Costo Total</th>'+
                              '<th class="bg-amber">Utilidad $</th>'+
                              '<th class="customercolortheadth">Observaciones</th>'+
                              '<th class="'+background_tables+'">Requisición</th>'+
                              '<th class="'+background_tables+'">Cotización</th>'+
                              '<th class="'+background_tables+'">Moneda</th>'+
                              '<th class="'+background_tables+'">Costo de Lista</th>'+
                              '<th class="'+background_tables+'">Tipo de Cambio</th>'+
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
                        '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
                      '</div>'+ 
                      '<div class="col-md-3 col-md-offset-3">'+
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
                          '<tr hidden>'+
                            '<td style="padding:0px !important;">Costo</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                          '<tr hidden>'+
                            '<td style="padding:0px !important;">Utilidad</td>'+
                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="utilidad" id="utilidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                          '</tr>'+
                        '</table>'+
                      '</div>'+
                    '</div>'+   
                  '</div>'+ 
                '</div>'+ 
              '</div>';
    $("#tabsform").html(tabs);
    $("#periodohoy").val(data.traspaso.Periodo);
    $("#folio").val(data.traspaso.Folio);
    $("#serie").val(data.traspaso.Serie);
    $("#serietexto").html("Serie: "+data.traspaso.Serie);
    $("#fecha").val(data.fecha).attr('min', data.fechasdisponiblesenmodificacion.fechamin).attr('max', data.fechasdisponiblesenmodificacion.fechamax);
    $("#almacende").val(data.almacende.Nombre);
    if(data.almacende.Nombre != null){
      $("#textonombrealmacende").html(data.almacende.Nombre.substring(0, 40));
    }
    $("#numeroalmacende").val(data.almacende.Numero);
    $("#numeroalmacendeanterior").val(data.almacende.Numero);
    if(data.almacena !=""){
      $("#almacena").val(data.almacena.Nombre).attr('required', 'required');
      $("#textonombrealmacena").html(data.almacena.Nombre.substring(0, 40));
      $("#numeroalmacena").val(data.almacena.Numero).attr('required', 'required');
      $("#numeroalmacenaanterior").val(data.almacena.Numero).attr('required', 'required');
      //colocar en blanco datos orden y quitarles el required
      $("#orden").val("").removeAttr('required');
      $("#ordenanterior").val("").removeAttr('required');
      $("#textonombreorden").html("");
      $("#fechaorden").val("").removeAttr('required');
      $("#clienteorden").val("").removeAttr('required');
      $("#tipo").val("").removeAttr('required');
      $("#unidad").val("").removeAttr('required');
      $("#statusorden").val("").removeAttr('required');
    }
    if(data.ordentrabajo != ""){
      $("#orden").val(data.ordentrabajo.Orden).attr('required', 'required');
      $("#ordenanterior").val(data.ordentrabajo.Orden).attr('required', 'required');
      $("#textonombreorden").html(data.ordentrabajo.Orden.substring(0, 40));
      $("#fechaorden").val(data.fechaorden).attr('required', 'required');
      $("#tipo").val(data.ordentrabajo.Tipo).attr('required', 'required');
      $("#unidad").val(data.ordentrabajo.Unidad).attr('required', 'required');
      $("#statusorden").val(data.ordentrabajo.Status).attr('required', 'required');
      $("#clienteorden").val(data.cliente.Nombre).attr('required', 'required');
      //colocar en blanco alamcen foraneo y quitarle el required
      $("#numeroalmacena").val("").removeAttr('required');
      $("#numeroalmacenaanterior").val("").removeAttr('required');
      $("#almacena").val("").removeAttr('required');
      $("#textonombrealmacena").html("");
    }
    $("#referencia").val(data.traspaso.Referencia);
    $("#observaciones").val(data.traspaso.Obs);
    $("#importe").val(data.importe);
    $("#descuento").val(data.descuento);
    $("#subtotal").val(data.subtotal);
    $("#iva").val(data.iva);
    $("#total").val(data.total);
    $("#costo").val(data.costo);
    $("#utilidad").val(data.utilidad);
    //detalles
    $("#tablaproductostraspasos tbody").html(data.filasdetallestraspaso);
    $("#numerofilas").val(data.numerodetallestraspaso);
    //colocar valores a contadores
    contadorproductos = data.contadorproductos;
    contadorfilas = data.contadorfilas;
    //ocultar botones de seleccion
    $("#botonobteneralmacenes").hide();
    $("#botonobteneralmacenesforaneos").hide();
    $("#botonobtenerordenestrabajo").hide();
    //mostrar el buscador de productos
    mostrarbuscadorcodigoproducto();
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //activar busquedas
    $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenerproductoporcodigo();
      }
    });
    //regresar numero almacen de
    $('#numeroalmacende').on('change', function(e) {
      regresarnumeroalmacende();
    });
    //regresar numero almacen a
    $('#numeroalmacena').on('change', function(e) {
      regresarnumeroalmacena();
    });
    //regresar folio orden
    $('#orden').on('change', function(e) {
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
    $(".inputnextdet").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnextdet");          
        $(".inputnextdet").eq(index + 1).focus().select(); 
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
    if(parseInt(numerofilas) > 0  && parseInt(numerofilas) < 500){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:traspasos_guardar_modificacion,
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
  $.get(traspasos_obtener_datos_envio_email,{documento:documento}, function(data){
    $("#textomodalenviarpdfemail").html("Enviar email Traspaso No." + documento);
    $("#emaildocumento").val(documento);
    $("#emailde").val(data.emailde);
    $("#emailpara").val(data.emailpara);
    $("#email2cc").val(data.email2cc);
    $("#email3cc").val(data.email3cc);
    $("#emailasunto").val("TRASPASO NO. " + documento +" DE "+ nombreempresa);
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
      url:traspasos_enviar_pdfs_email,
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
  var columnastablafoliosencontrados =  '<tr>'+
                                          '<th><div style="width:80px !important;">Generar Documento en PDF</div></th>'+
                                          '<th>Traspaso</th>'+
                                          '<th>Total</th>'+
                                          '<th>Status</th>'+
                                        '</tr>';
  $("#columnastablafoliosencontrados").html(columnastablafoliosencontrados);
  tabla=$('#tablafoliosencontrados').DataTable({
      "paging":   false,
      "ordering": false,
      "info":     false,
      "searching": false,
      order: [1, 'asc'],
      processing: true,
      serverSide: true,
      ajax: {
          url: traspasos_buscar_folio_string_like,
          data: function (d) {
              d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Traspaso', name: 'Traspaso' },
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
    if(campos[i] == 'Traspaso' || campos[i] == 'Status' || campos[i] == 'Periodo'){
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