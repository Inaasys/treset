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
  $.get(asignacion_herramienta_obtener_ultimo_id,{serie:serie}, function(folio){
    $("#folio").val(folio);
  })  
}
//cerrar modales
function limpiarmodales(){
  $("#tabsform").empty();
  $("#tabsformauditarherramientas").empty();
}
//limpiar todos los inputs del formulario alta
function limpiar(){
  $("#formparsley")[0].reset();
  $("#formauditarherramienta")[0].reset();
  //Resetear las validaciones del formulario alta
  $("#formparsley").parsley().reset();
  $("#formauditarherramienta").parsley().reset();
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
        url: asignacion_herramienta_obtener,
        data: function (d) {
            d.periodo = $("#periodo").val();
        }
    },
    "createdRow": function( row, data, dataIndex){
        if( data.status ==  `BAJA`){ $(row).addClass('bg-orange');}
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
    obtenerdatos(data.asignacion);
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
  var form_data = new FormData();
  form_data.append('partidasexcel', partidasexcel);  
  form_data.append('contadorproductos', contadorproductos);
  form_data.append('contadorfilas', contadorfilas);
  form_data.append('arraycodigospartidas', arraycodigospartidas);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:asignacion_herramienta_cargar_partidas_excel,
    data: form_data,
    type: 'POST',
    contentType: false,
    processData: false,
    success: function (data) {
      contadorfilas = data.contadorfilas;
      contadorproductos = data.contadorproductos;
      $("#tablaherramientasasignadas tbody").append(data.filasdetallesasignacion);
      comprobarfilas();
      calculartotalordencompra();
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
        url: asignacion_herramienta_obtener_series_documento
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
function seleccionarseriedocumento(serie){
  $.get(asignacion_herramienta_obtener_ultimo_folio_serie_seleccionada, {serie:serie}, function(folio){
      $("#folio").val(folio);
      $("#serie").val(serie);
      $("#serietexto").html("Serie: "+serie);
      mostrarformulario();
  }) 
}
//obtener registros de proveedores
function obtenerpersonalrecibe(){
  ocultarformulario();
  var tablapersonalrecibe = '<div class="modal-header '+background_forms_and_modals+'">'+
                      '<h4 class="modal-title">Personal que recibe</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                  '<table id="tbllistadopersonalrecibe" class="tbllistadopersonalrecibe table table-bordered table-striped table-hover" style="width:100% !important">'+
                                      '<thead class="'+background_tables+'">'+
                                          '<tr>'+
                                              '<th>Operaciones</th>'+
                                              '<th>Numero</th>'+
                                              '<th>Nombre</th>'+
                                              '<th>Fecha Ingreso</th>'+
                                              '<th>Tipo Personal</th>'+
                                              '<th>Status</th>'+
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
    $("#contenidomodaltablas").html(tablapersonalrecibe);
    var tperreb = $('#tbllistadopersonalrecibe').DataTable({
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
            url: asignacion_herramienta_obtener_personal_recibe,
            data: function (d) {
              d.numeropersonalentrega = $("#numeropersonalentrega").val();
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'nombre', name: 'nombre' },
            { data: 'fecha_ingreso', name: 'fecha_ingreso' },
            { data: 'tipo_personal', name: 'tipo_personal' },
            { data: 'status', name: 'status', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadopersonalrecibe').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
    //seleccionar registro al dar doble click
    $('#tbllistadopersonalrecibe tbody').on('dblclick', 'tr', function () {
      var data = tperreb.row( this ).data();
      seleccionarpersonalrecibe(data.id, data.nombre);
    });
} 
//obtener registros de almacenes
function obtenerpersonalentrega(){
    ocultarformulario();
    var tablapersonalentrega = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Personal que entrega</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive ">'+
                                        '<table id="tbllistadopersonalentrega" class="tbllistadopersonalentrega table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Nombre</th>'+
                                                    '<th>Fecha Ingreso</th>'+
                                                    '<th>Tipo Personal</th>'+
                                                    '<th>Status</th>'+
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
      $("#contenidomodaltablas").html(tablapersonalentrega);
      var tperent = $('#tbllistadopersonalentrega').DataTable({
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
            url: asignacion_herramienta_obtener_personal_entrega,
            data: function (d) {
              d.numeropersonalrecibe = $("#numeropersonalrecibe").val();
            }
          },
          columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'nombre', name: 'nombre' },
            { data: 'fecha_ingreso', name: 'fecha_ingreso' },
            { data: 'tipo_personal', name: 'tipo_personal' },
            { data: 'status', name: 'status', orderable: false, searchable: false }
          ],
          "initComplete": function() {
              var $buscar = $('div.dataTables_filter input');
              $buscar.unbind();
              $buscar.bind('keyup change', function(e) {
                  if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadopersonalentrega').DataTable().search( this.value ).draw();
                  }
              });
          }, 
      }); 
      //seleccionar registro al dar doble click
      $('#tbllistadopersonalentrega tbody').on('dblclick', 'tr', function () {
        var data = tperent.row( this ).data();
        seleccionarpersonalentrega(data.id, data.nombre);
      });
  } 
function seleccionarpersonalrecibe(id, nombre){
  var numeropersonalrecibeanterior = $("#numeropersonalrecibeanterior").val();
  var numeropersonalrecibe = id;
  if(numeropersonalrecibeanterior != numeropersonalrecibe){
    $("#numeropersonalrecibe").val(id);
    $("#numeropersonalrecibeanterior").val(id);
    $("#personalrecibe").val(nombre);
    if(nombre != null){
      $("#textonombrepersonalrecibe").html(nombre.substring(0, 40));
    }
    $("#btnbuscarpersonalqueentrega").show();
    mostrarformulario();
  }
}
function seleccionarpersonalentrega(id, nombre){
  var numeropersonalentregaanterior = $("#numeropersonalentregaanterior").val();
  var numeropersonalentrega = id;
  if(numeropersonalentregaanterior != numeropersonalentrega){
    $("#numeropersonalentrega").val(id);
    $("#numeropersonalentregaanterior").val(id);
    $("#personalentrega").val(nombre);
    if(nombre != null){
      $("#textonombrepersonalentrega").html(nombre.substring(0, 40));
    }
    mostrarformulario();
  }
}
//obtener por numero
function obtenerpersonalrecibepornumero(){
  var numeropersonalrecibeanterior = $("#numeropersonalrecibeanterior").val();
  var numeropersonalrecibe = $("#numeropersonalrecibe").val();
  if(numeropersonalrecibeanterior != numeropersonalrecibe){
    if($("#numeropersonalrecibe").parsley().isValid()){
      var numeropersonalentrega = $("#numeropersonalentrega").val();
      $.get(asignacion_herramienta_obtener_personal_recibe_por_numero, {numeropersonalrecibe:numeropersonalrecibe,numeropersonalentrega:numeropersonalentrega}, function(data){
        $("#numeropersonalrecibe").val(data.numero);
        $("#numeropersonalrecibeanterior").val(data.numero);
        $("#personalrecibe").val(data.nombre);
        if(data.nombre != null){
          $("#textonombrepersonalrecibe").html(data.nombre.substring(0, 40));
        }
        $("#btnbuscarpersonalqueentrega").show();
        mostrarformulario();
      }) 
    }
  }
}
//regresar numero
function regresarnumeropersonalrecibe(){
  var numeropersonalrecibeanterior = $("#numeropersonalrecibeanterior").val();
  $("#numeropersonalrecibe").val(numeropersonalrecibeanterior);
}
//obtener por numero
function obtenerpersonalentregapornumero(){
  var numeropersonalentregaanterior = $("#numeropersonalentregaanterior").val();
  var numeropersonalentrega = $("#numeropersonalentrega").val();
  if(numeropersonalentregaanterior != numeropersonalentrega){
    if($("#numeropersonalentrega").parsley().isValid()){
      var numeropersonalrecibe = $("#numeropersonalrecibe").val();
      $.get(asignacion_herramienta_obtener_personal_entrega_por_numero, {numeropersonalentrega:numeropersonalentrega,numeropersonalrecibe:numeropersonalrecibe}, function(data){
        $("#numeropersonalentrega").val(data.numero);
        $("#numeropersonalentregaanterior").val(data.numero);
        $("#personalentrega").val(data.nombre);
        if(data.nombre != null){
          $("#textonombrepersonalentrega").html(data.nombre.substring(0, 40));
        }
        mostrarformulario();
      }) 
    }
  }
}
//regresar numero
function regresarnumeropersonalentrega(){
  var numeropersonalentregaanterior = $("#numeropersonalentregaanterior").val();
  $("#numeropersonalentrega").val(numeropersonalentregaanterior);
}
//listar productos para tab consumos
function listarherramientas(){
  ocultarformulario();
  var tablaherramientas = '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Productos</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                          '<div class="row">'+
                            '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                '<table id="tbllistadoherramienta" class="tbllistadoherramienta table table-bordered table-striped table-hover" style="width:100% !important">'+
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
  $("#contenidomodaltablas").html(tablaherramientas);
  var therr = $('#tbllistadoherramienta').DataTable({
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
      url: asignacion_herramienta_obtener_herramienta,
      data: function (d) {
        d.codigoabuscar = $("#codigoabuscar").val();
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
          $('#tbllistadoherramienta').DataTable().search( this.value ).draw();
        }
      });
    },
  });  

  //seleccionar registro al dar doble click
  $('#tbllistadoherramienta tbody').on('dblclick', 'tr', function () {
    var data = therr.row( this ).data();
    $.get(asignacion_herramienta_obtener_selectalmacenes, function(selectalmacenes){
        //seleccionarbanco(data.Numero, data.Nombre, ultimatransferencia[0].Transferencia);
        agregarfilaherramienta(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Existencias, selectalmacenes, tipooperacion);
    });
  });
}
function obtenerproductoporcodigo(){
  var codigoabuscar = $("#codigoabuscar").val();
  var tipooperacion = $("#tipooperacion").val();
  $.get(asignacion_herramienta_obtener_herramienta_por_codigo,{codigoabuscar:codigoabuscar}, function(data){
    if(parseInt(data.contarproductos) > 0){
      agregarfilaherramienta(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Existencias, data.selectalmacenes, tipooperacion);
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
//calcular total de la orden de compra
function calculartotalesfilas(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
    if(fila === cuentaFilas){
      // obtener los datos de la fila:
      var cantidadpartida = $(".cantidadpartida", this).val();
      var preciopartida = $('.preciopartida', this).val();
      var totalpesospartida = $('.totalpesospartida', this).val(); 
      //total de la partida
      totalpesospartida =  new Decimal(cantidadpartida).times(preciopartida);
      $('.totalpesospartida', this).val(number_format(round(totalpesospartida, numerodecimales), numerodecimales, '.', ''));
      calculartotalordencompra();
    }  
    cuentaFilas++;
  });
} 
//calcular totales de orden de compra
function calculartotalordencompra(){
  var total = 0;
  $("tr.filasproductos").each(function(){
    total = new Decimal(total).plus($(".totalpesospartida", this).val());
  }); 
  $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
}
//agregar una fila en la tabla de precios productos
var contadorproductos=0;
var contadorfilas = 0;
function agregarfilaherramienta(Codigo, Producto, Unidad, Costo, Existencias, selectalmacenes, tipooperacion){
  var result = evaluarproductoexistente(Codigo);
  if(result == false){
    var tipo = "alta";
    var fila=   '<tr class="filasproductos" id="filaproducto'+contadorproductos+'">'+
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosproductos('+contadorproductos+')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]">'+Codigo+'</td>'+
                        '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl nombreproductopartida" name="nombreproductopartida[]" id="nombreproductopartida[]" value="'+Producto+'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'+Unidad+'" readonly data-parsley-length="[1, 5]">'+Unidad+'</td>'+
                        '<td class="tdmod">'+
                          '<select name="almacenpartida[]" class="form-control inputnextdet divorinputmodxl almacenpartida" style="width:100% !important;height: 28px !important;" onchange="obtenerexistenciasalmacen('+contadorproductos+')" required>'+
                            selectalmacenes+
                          '</select>'+
                        '</td>'+ 
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm existenciasalmacenpartida" name="existenciasalmacenpartida[]" id="existenciasalmacenpartida[]" value="'+Existencias+'"  onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" id="cantidadpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-min="0.1" data-parsley-existencias="'+Existencias+'" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" required></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" id="preciopartida[]" value="'+Costo+'" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" id="totalpesospartida[]" value="'+Costo+'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                        '<td class="tdmod">'+
                          '<select name="estadopartida[]" class="form-control divorinputmodmd" style="width:100% !important;height: 28px !important;" required>'+
                              '<option selected disabled hidden>Selecciona</option>'+
                              '<option value="Nuevo">Nuevo</option>'+
                              '<option value="Usado">Usado</option>'+
                          '</select>'+
                        '</td>'+    
                '</tr>';
    contadorproductos++;
    contadorfilas++;
    $("#tablaherramientasasignadas").append(fila);
    comprobarfilas();
    mostrarformulario();
    calculartotalordencompra();
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
  }else{
    msj_errorproductoyaagregado();
  }  
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
  var numerofilas = $("#tablaherramientasasignadas tbody tr").length;
  $("#numerofilas").val(numerofilas);
}
//obtener las existencias actuales del almacen seleccionado
function obtenerexistenciasalmacen(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
    if(fila === cuentaFilas){
      // obtener los datos de la fila:
      var almacenpartida = $(".almacenpartida", this).val();
      var codigoproductopartida = $(".codigoproductopartida", this).val();
      var cantidadpartida = $(".cantidadpartida", this).val();
      comprobarexistenciasalmacenpartida(almacenpartida, codigoproductopartida).then(existencias=>{
        if(parseFloat(existencias) > 0){
          var nuevaexistencia = new Decimal(existencias).plus(cantidadpartida);
          $(".existenciasalmacenpartida", this).val(number_format(round(existencias, numerodecimales), numerodecimales, '.', ''));
          $(".cantidadpartida", this).removeAttr('readonly');
          $(".cantidadpartida", this).attr('data-parsley-existencias', number_format(round(nuevaexistencia, numerodecimales), numerodecimales, '.', ''));
        }else{
          var nuevaexistencia = new Decimal(0).plus(cantidadpartida);
          $(".existenciasalmacenpartida", this).val(number_format(round(existencias, numerodecimales), numerodecimales, '.', ''));
          $(".cantidadpartida", this).val("0."+numerocerosconfigurados);
          $(".cantidadpartida", this).attr('readonly', 'readonly');
          $(".cantidadpartida", this).attr('data-parsley-existencias', number_format(round(nuevaexistencia, numerodecimales), numerodecimales, '.', ''));
        }
      }) 
    }  
    cuentaFilas++;
  });
}
//funcion asincrona para buscar existencias de la partida
function comprobarexistenciasalmacenpartida(almacenpartida, codigoproductopartida){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $.get(asignacion_herramienta_obtener_existencias_almacen,{'almacenpartida':almacenpartida,'codigoproductopartida':codigoproductopartida},existencias=>{
        return ejecuta(existencias);
      })
    },500);
  })
}
//eliminar una fila en la tabla de precios clientes
function eliminarfilapreciosproductos(numerofila){
  var confirmacion = confirm("Esta seguro de eliminar la herramienta?"); 
  if (confirmacion == true) { 
    $("#filaproducto"+numerofila).remove();
    contadorfilas--; //importante para todos los calculo en el modulo de orden de compra se debe restar al contadorfilas la fila que se acaba de eliminar
    contadorproductos--;
    renumerarfilasordencompra();//importante para todos los calculo en el modulo de orden de compra 
    calculartotalordencompra();
  }
}
//renumerar las filas de la orden de compra
function renumerarfilasordencompra(){
  var lista;
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("cantidadpartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar la precio de la partida
  lista = document.getElementsByClassName("preciopartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
}  
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Asignación Herramienta');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs ='<div class="col-md-12">'+
              '<div class="row">'+
                '<div class="col-md-3">'+
                  '<label>Asignación <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                  '<input type="text" class="form-control inputnext" name="folio" id="folio" required readonly>'+
                  '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                  '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                  '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                '</div>'+ 
                '<div class="col-md-3">'+
                  '<label>Personal que recibe <span class="label label-danger" id="textonombrepersonalrecibe"></span></label>'+
                  '<table class="col-md-12">'+
                    '<tr>'+
                      '<td>'+
                        '<div class="btn bg-blue waves-effect" onclick="obtenerpersonalrecibe()">Seleccionar</div>'+
                      '</td>'+
                      '<td>'+
                        '<div class="form-line">'+
                          '<input type="text" class="form-control inputnext" name="numeropersonalrecibe" id="numeropersonalrecibe" required data-parsley-type="integer" autocomplete="off">'+
                          '<input type="hidden" class="form-control" name="numeropersonalrecibeanterior" id="numeropersonalrecibeanterior" required data-parsley-type="integer">'+
                          '<input type="hidden" class="form-control" name="personalrecibe" id="personalrecibe" required readonly>'+
                        '</div>'+
                      '</td>'+
                    '</tr>'+    
                  '</table>'+
                '</div>'+
                '<div class="col-md-3">'+
                  '<label>Personal que entrega <span class="label label-danger" id="textonombrepersonalentrega"></span></label>'+
                  '<table class="col-md-12">'+
                    '<tr>'+
                      '<td>'+
                        '<div class="btn bg-blue waves-effect" onclick="obtenerpersonalentrega()" id="btnbuscarpersonalqueentrega">Seleccionar</div>'+
                      '</td>'+
                      '<td>'+    
                        '<div class="form-line">'+
                          '<input type="text" class="form-control inputnext" name="numeropersonalentrega" id="numeropersonalentrega" required data-parsley-type="integer" autocomplete="off">'+
                          '<input type="hidden" class="form-control" name="numeropersonalentregaanterior" id="numeropersonalentregaanterior" required data-parsley-type="integer">'+
                          '<input type="hidden" class="form-control" name="personalentrega" id="personalentrega" required readonly>'+
                        '</div>'+
                      '</td>'+    
                    '</tr>'+    
                  '</table>'+
                '</div>'+   
                '<div class="col-md-3">'+
                  '<label>Fecha</label>'+
                  '<input type="datetime-local" class="form-control inputnext" name="fecha" id="fecha"  required data-parsley-excluded="true" onkeydown="return false">'+
                  '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                '</div>'+
              '</div>'+
              '<div class="row">'+ 
                '<div class="col-md-4" id="divbuscarcodigoproducto">'+
                  '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                  '<table class="col-md-12">'+
                    '<tr>'+
                      '<td>'+
                        '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarherramientas()">Ver Herramientas</div>'+
                      '</td>'+
                      '<td>'+ 
                        '<div class="form-line">'+
                          '<input type="text" class="form-control inputnext" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código de la herramienta" autocomplete="off">'+
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
                  '<a href="#herramientastab" data-toggle="tab">Herramientas</a>'+
                '</li>'+
              '</ul>'+
              '<div class="tab-content">'+
                '<div role="tabpanel" class="tab-pane fade in active" id="herramientastab">'+
                  '<div class="row">'+
                    '<div class="col-md-12 table-responsive cabecerafija" style="height: 300px;overflow-y: scroll;padding: 0px 0px;">'+
                      '<table id="tablaherramientasasignadas" class="table table-bordered tablaherramientasasignadas">'+
                        '<thead class="'+background_tables+'">'+
                          '<tr>'+
                            '<th class="'+background_tables+'">#</th>'+
                            '<th class="'+background_tables+'">Herramienta</th>'+
                            '<th class="'+background_tables+'"><div style="width:200px !important;">Descripción</div></th>'+
                            '<th class="customercolortheadth">Unidad</th>'+
                            '<th class="customercolortheadth">Almacén</th>'+
                            '<th class="customercolortheadth">Existencias Almacén</th>'+
                            '<th class="customercolortheadth">Cantidad</th>'+
                            '<th class="customercolortheadth">Precio $</th>'+
                            '<th class="'+background_tables+'">Total $</th>'+
                            '<th class="'+background_tables+'">Estado Herramienta</th>'+
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
                      '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" rows="2" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
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
  //mostrar mensaje de bajar plantilla
  $('[data-toggle="tooltip"]').tooltip({
    container: 'body'
  });
  $("#serie").val(serieusuario);
  $("#serietexto").html("Serie: "+serieusuario);
  obtenultimonumero();
  asignarfechaactual();
  //asignar el tipo de operacion que se realizara
  $("#tipooperacion").val("alta");
  //reiniciar contadores  
  contadorproductos=0;
  contadorfilas = 0;
  $("#numerofilas").val("0");
  //se debe motrar el input para buscar los productos
  $("#divbuscarcodigoproducto").show();
  //busquedas seleccion
  $("#codigoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerproductoporcodigo();
    }
  });
  //activar busqueda
  $('#numeropersonalrecibe').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerpersonalrecibepornumero();
    }
  });
  //regresar numero
  $('#numeropersonalrecibe').on('change', function(e) {
    regresarnumeropersonalrecibe();
  });
  //activar busqueda
  $('#numeropersonalentrega').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerpersonalentregapornumero();
    }
  });
  //regresar numero
  $('#numeropersonalentrega').on('change', function(e) {
    regresarnumeropersonalentrega();
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
    if(parseInt(numerofilas) > 0 && parseInt(numerofilas) < 500){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:asignacion_herramienta_guardar,
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
function autorizarasignacion(asignacion){
  $.get(asignacion_herramienta_obtener_asignacion_herramienta_a_autorizar, {asignacion:asignacion}, function(filasdetallesasignacion){
    $("#asignacionautorizar").val(asignacion);
    $("#tablaautorizacionasignacionherramienta tbody").html(filasdetallesasignacion)
    $('#autorizarasignacion').modal('show');
  })  

}
$("#btnautorizar").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formautorizar")[0]);
  var form = $("#formautorizar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:asignacion_herramienta_autorizar,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#autorizarasignacion').modal('hide');
        msj_datosguardadoscorrectamente();
        $('.page-loader-wrapper').css('display', 'none');
      },
      error:function(data){
        if(data.status == 403){
          msj_errorenpermisos();
        }else{
          msj_errorajax();
        }
        $('#autorizarasignacion').modal('hide');
        $('.page-loader-wrapper').css('display', 'none');
      }
    })
  }else{
    form.parsley().validate();
  }
});
//verificar si la asignacion se esta utilizando en algun prestamo de herramienta
function desactivar(asignaciondesactivar){
  $.get(asignacion_herramienta_verificar_uso_en_modulos,{asignaciondesactivar:asignaciondesactivar}, function(data){
    if(data.status == 'BAJA'){
      $("#motivobaja").val("");
      $("#asignaciondesactivar").val(0);
      $("#textomodaldesactivar").html('Error, esta asignación ya fue dado de baja');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{ 
      if(data.numero_prestamos > 0){
        $("#motivobaja").val("");
        $("#asignaciondesactivar").val(0);
        $("#textomodaldesactivar").html('Error esta asignación tiene herramienta prestada');
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        $("#motivobaja").val("");
        $("#asignaciondesactivar").val(asignaciondesactivar);
        $("#textomodaldesactivar").html('Estas seguro de dar de baja la asignación de herramienta? No'+asignaciondesactivar);
        $("#motivobaja").val("");
        $("#divmotivobaja").show();
        $("#btnbaja").show();
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
      url:asignacion_herramienta_alta_o_baja,
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
function obtenerdatos(asignacionmodificar){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(asignacion_herramienta_obtener_asignacion_herramienta,{asignacionmodificar:asignacionmodificar },function(data){
    $("#titulomodal").html('Modificación Asignación Herramienta --- STATUS : ' + data.Asignacion_Herramienta.status);
    //formulario modificacion
    var tabs ='<div class="col-md-12">'+
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Asignación <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b></label>'+
                    '<input type="text" class="form-control inputnext" name="folio" id="folio" required readonly>'+
                    '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                    '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                    '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                  '</div>'+ 
                  '<div class="col-md-3">'+
                    '<label>Personal que recibe <span class="label label-danger" id="textonombrepersonalrecibe"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td hidden>'+
                          '<div class="btn bg-blue waves-effect" onclick="obtenerpersonalrecibe()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="numeropersonalrecibe" id="numeropersonalrecibe" required data-parsley-type="integer" autocomplete="off">'+
                            '<input type="hidden" class="form-control" name="numeropersonalrecibeanterior" id="numeropersonalrecibeanterior" required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="personalrecibe" id="personalrecibe" required readonly>'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+    
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>Personal que entrega <span class="label label-danger" id="textonombrepersonalentrega"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td hidden>'+
                          '<div class="btn bg-blue waves-effect" onclick="obtenerpersonalentrega()" id="btnbuscarpersonalqueentrega">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+    
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="numeropersonalentrega" id="numeropersonalentrega" required data-parsley-type="integer" autocomplete="off">'+
                            '<input type="hidden" class="form-control" name="numeropersonalentregaanterior" id="numeropersonalentregaanterior" required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="personalentrega" id="personalentrega" required readonly>'+
                          '</div>'+
                        '</td>'+    
                      '</tr>'+    
                    '</table>'+
                  '</div>'+   
                  '<div class="col-md-3">'+
                    '<label>Fecha</label>'+
                    '<input type="datetime-local" class="form-control inputnext" name="fecha" id="fecha"  required data-parsley-excluded="true" onkeydown="return false">'+
                    '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                  '</div>'+
                '</div>'+
                '<div class="row">'+
                  '<div class="col-md-4" id="divbuscarcodigoproducto">'+
                    '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarherramientas()">Ver Herramientas</div>'+
                        '</td>'+
                        '<td>'+ 
                          '<div class="form-line">'+
                            '<input type="text" class="form-control inputnext" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código de la herramienta" autocomplete="off">'+
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
                    '<a href="#herramientastab" data-toggle="tab">Herramientas</a>'+
                  '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                  '<div role="tabpanel" class="tab-pane fade in active" id="herramientastab">'+
                    '<div class="row">'+
                      '<div class="col-md-12 table-responsive cabecerafija" style="height: 300px;overflow-y: scroll;padding: 0px 0px;">'+
                        '<table id="tablaherramientasasignadas" class="table table-bordered tablaherramientasasignadas">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="'+background_tables+'">Herramienta</th>'+
                              '<th class="'+background_tables+'"><div style="width:200px !important;">Descripción</div></th>'+
                              '<th class="customercolortheadth">Unidad</th>'+
                              '<th class="customercolortheadth">Almacén</th>'+
                              '<th class="customercolortheadth">Existencias Almacén</th>'+
                              '<th class="customercolortheadth">Cantidad</th>'+
                              '<th class="customercolortheadth">Precio $</th>'+
                              '<th class="'+background_tables+'">Total $</th>'+
                              '<th class="'+background_tables+'">Estado Herramienta</th>'+
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
                        '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" rows="2" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
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
    $("#periodohoy").val(data.Asignacion_Herramienta.periodo);
    $("#folio").val(data.Asignacion_Herramienta.folio);
    $("#serie").val(data.Asignacion_Herramienta.serie);
    $("#serietexto").html("Serie: "+data.Asignacion_Herramienta.serie);
    $("#numeropersonalrecibe").val(data.personalrecibe.id);
    $("#numeropersonalrecibeanterior").val(data.personalrecibe.id);
    $("#personalrecibe").val(data.personalrecibe.nombre);
    if(data.personalrecibe.nombre != null){
      $("#textonombrepersonalrecibe").html(data.personalrecibe.nombre.substring(0, 40));
    }
    $("#numeropersonalentrega").val(data.personalentrega.id);
    $("#numeropersonalentregaanterior").val(data.personalentrega.id);
    $("#personalentrega").val(data.personalentrega.nombre);
    if(data.personalentrega.nombre != null){
      $("#textonombrepersonalentrega").html(data.personalentrega.nombre.substring(0, 40));
    }
    $("#fecha").val(data.fecha).attr('min', data.fechasdisponiblesenmodificacion.fechamin).attr('max', data.fechasdisponiblesenmodificacion.fechamax);
    $("#observaciones").val(data.Asignacion_Herramienta.observaciones);
    $("#total").val(data.total);
    //tabs precios productos
    $("#tablaherramientasasignadas").append(data.filasdetallesasignacion);
    $("#numerofilas").val(data.Numero_Asignacion_Herramienta_Detalle);
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //se deben asignar los valores a los contadores para que las sumas resulten correctas
    contadorproductos = data.contadorproductos;
    contadorfilas = data.contadorfilas;
    //busquedas seleccion
    $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenerproductoporcodigo();
      }
    });
    //regresar numero
    $('#numeropersonalrecibe').on('change', function(e) {
      regresarnumeropersonalrecibe();
    });
    //regresar numero
    $('#numeropersonalentrega').on('change', function(e) {
      regresarnumeropersonalentrega();
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
    if(parseInt(numerofilas) > 0 && parseInt(numerofilas) < 500){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:asignacion_herramienta_guardar_modificacion,
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
//hacer busqueda de folio para exportacion en pdf
function relistarbuscarstringlike(){
  var tabla = $('#tablafoliosencontrados').DataTable();
  tabla.ajax.reload();
}
function buscarstringlike(){
  var columnastablafoliosencontrados =  '<tr>'+
                                          '<th><div style="width:80px !important;">Generar Documento en PDF</div></th>'+
                                          '<th>Asignación</th>'+
                                          '<th>Recibe</th>'+
                                          '<th>Entrega</th>'+
                                          '<th>Total</th>'+
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
          url: asignacion_herramienta_buscar_id_string_like,
          data: function (d) {
              d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'asignacion', name: 'Orden' },
          { data: 'nombre_recibe_herramienta', name: 'nombre_recibe_herramienta', orderable: false, searchable: false },
          { data: 'nombre_entrega_herramienta', name: 'nombre_entrega_herramienta', orderable: false, searchable: false  },
          { data: 'total', name: 'total', orderable: false, searchable: false  },
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
    if(campos[i] == 'id' || campos[i] == 'asignacion' || campos[i] == 'status' || campos[i] == 'periodo'){
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
//generar excel personal para auditoria
function mostrarmodalgenerarexcelpersonal(){
  $.get(asignacion_herramienta_generar_excel_obtener_personal, function(data){
    $("#personalexcel").empty();
    $("#personalexcel").append("<option selected disabled hidden>Selecciona el personal</option>");
    $.each(data,function(key, registro) {
      $("#personalexcel").append('<option value='+registro.id+'>'+registro.nombre+' - '+registro.tipo_personal+'</option>');
    });
    //formulario auditar herramienta a personal
    var tabs =    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                      '<li role="presentation" class="active">'+
                          '<a href="#herramientasasignadastab" data-toggle="tab">Herramientas Asignadas</a>'+
                      '</li>'+
                  '</ul>'+
                  '<div class="tab-content">'+
                      '<div role="tabpanel" class="tab-pane fade in active" id="herramientasasignadastab">'+
                          '<div class="row">'+
                              '<div class="col-md-12 table-responsive">'+
                                  '<table id="tablaherramientasasignadas" class="table table-bordered tablaherramientasasignadas">'+
                                      '<thead class="'+background_tables+'">'+
                                          '<tr>'+
                                              '<th class="'+background_tables+'">Asignación</th>'+
                                              '<th class="'+background_tables+'">Herramienta</th>'+
                                              '<th class="'+background_tables+'"><div style="width:200px !important;">Descripción</div></th>'+
                                              '<th class="'+background_tables+'" >Unidad</th>'+
                                              '<th class="'+background_tables+'" >Cantidad Asignada</th>'+
                                              '<th class="'+background_tables+'" >Precio $</th>'+
                                              '<th class="'+background_tables+'">Total $</th>'+
                                              '<th class="customercolortheadth">Estado Auditoria</th>'+
                                              '<th class="customercolortheadth">Cantidad Actual Auditoria</th>'+
                                          '</tr>'+
                                      '</thead>'+
                                      '<tbody>'+           
                                      '</tbody>'+
                                  '</table>'+
                              '</div>'+
                              '<div class="col-md-12">'+
                                '<input type="hidden" name="numerofilasherramientaasignada" id="numerofilasherramientaasignada" class="form-control" required>'+
                              '</div>'+
                          '</div>'+   
                      '</div>'+ 
                  '</div>';
    $("#tabsformauditarherramientas").html(tabs);
    $("#modalgenerarexcelpersonal").modal('show');
  }) 
}
//cargar toda la herramienta asignada al personal
function herramientaasignadapersonal(){
  var idpersonal = $("#personalexcel").val();
  $.get(asignacion_herramienta_obtener_herramienta_personal, {idpersonal:idpersonal}, function(data){
    if(parseInt(data.contadorfilas) > 0){
      //tabs asignacion herramientas
      $("#tablaherramientasasignadas tbody").html(data.filasdetallesasignacion);
      //se deben asignar los valores a los contadores para que las sumas resulten correctas
      contadorfilas = data.contadorfilas;
      $("#numerofilasherramientaasignada").val(data.contadorfilas);
      $("#btnGuardarAuditoria").show();
      $("#btnGenerarReporteAuditoria").show();
      $("#btnGenerarReporteAuditoria").attr("href", data.urlgenerarreporteauditoria);
      $("#btnGenerarReporteGeneral").show();
      $("#btnGenerarReporteGeneral").attr("href", data.urlgenerarreportegeneral);
      if(parseInt(data.contadorasignacionessinautorizar) > 0){
        msj_infopersonalconasignacionesporautorizar(); 
        $("#btnGuardarAuditoria").hide();
        $("#btnGenerarReporteAuditoria").hide();
        $("#btnGenerarReporteAuditoria").attr("href", "");   
        $("#btnGenerarReporteGeneral").hide();
        $("#btnGenerarReporteGeneral").attr("href", "");   
      }
    }else if(parseInt(data.contadorasignacionessinautorizar) > 0){
      msj_infopersonalconasignacionesporautorizar();
    }else{
      msj_errorpersonalsinherramientaasignada();
      $("#tablaherramientasasignadas tbody").html("");
      $("#btnGuardarAuditoria").hide();
      $("#btnGenerarReporteAuditoria").hide();
      $("#btnGenerarReporteAuditoria").attr("href", "");
      $("#btnGenerarReporteGeneral").hide();
      $("#btnGenerarReporteGeneral").attr("href", "");
    }
  })  
}
//compara el estado de la auditoria
function compararestadoauditoria(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
     if(fila === cuentaFilas){
       // obtener los datos de la fila:
       var estadoauditoria = $(".estadoauditoria", this).val();
       var cantidadpartida = $(".cantidadpartida", this).val();
       if(estadoauditoria == "FALTANTE"){
          $(".cantidadauditoriapartida", this).val("0."+numerocerosconfigurados);
          $(".cantidadauditoriapartida",this).removeAttr('readonly');
          $(".cantidadauditoriapartida",this).attr('data-parsley-min', "0."+numerocerosconfiguradosinputnumberstep);
       }else{
          $(".cantidadauditoriapartida",this).attr('readonly', 'readonly');
          $(".cantidadauditoriapartida", this).val(cantidadpartida);
          $(".cantidadauditoriapartida",this).removeAttr('data-parsley-min');
       }
     }  
     cuentaFilas++;
  });
}
//guardar la auditoria del personal
$("#btnGuardarAuditoria").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formauditarherramienta")[0]);
  var form = $("#formauditarherramienta");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:asignacion_herramienta_guardar_auditoria,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        msj_datosguardadoscorrectamente();
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

init();