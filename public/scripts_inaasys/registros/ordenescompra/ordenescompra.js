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
    $('#fecha').val(hoy);
    $('input[type=datetime-local]').val(new Date().toJSON().slice(0,19));
    */
  $.get(ordenes_compra_obtener_fecha_actual_datetimelocal, function(fechadatetimelocal){
    $("#fecha").val(fechadatetimelocal);
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
//obtener tipos ordenes de compra
function obtenertiposordenescompra(tipoalta){
  $.get(ordenes_compra_obtener_tipos_ordenes_compra, {tipoalta:tipoalta}, function(select_tipos_ordenes_compra){
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
  var t = $('#tbllistadoseriedocumento').DataTable({
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
      $('#tbllistadoalmacen').DataTable({
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
              $buscar.unbind();
              $buscar.bind('keyup change', function(e) {
                  if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoalmacen').DataTable().search( this.value ).draw();
                  }
              });
          },
          
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
    $('#tbllistadorden').DataTable({
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
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadorden').DataTable().search( this.value ).draw();
                }
            });
        },
        
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
        console.log(data);
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
  $('#tbllistadoproducto').DataTable({
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
      if(multiplicaciondescuentoporcentajepartida.d[0] > parseInt(0)){
        var descuentoporcentajepartida = new Decimal(multiplicaciondescuentoporcentajepartida/importepartida);
        $('.descuentoporcentajepartida', this).val(number_format(round(descuentoporcentajepartida, numerodecimales), numerodecimales, '.', ''));
        calculartotalesfilasordencompra(fila);
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
        calculartotalesfilasordencompra(fila);
      }
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
  $("#importe").val(number_format(round(importe, numerodecimales), numerodecimales, '.', ''));
  $("#descuento").val(number_format(round(descuento, numerodecimales), numerodecimales, '.', ''));
  $("#subtotal").val(number_format(round(subtotal, numerodecimales), numerodecimales, '.', ''));
  $("#iva").val(number_format(round(iva, numerodecimales), numerodecimales, '.', ''));
  $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
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
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]">'+Codigo+'</td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxl nombreproductopartida" name="nombreproductopartida[]" id="nombreproductopartida[]" value="'+Producto+'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'+Unidad+'" readonly data-parsley-length="[1, 5]">'+Unidad+'</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm porsurtirpartida" name="porsurtirpartida[]" id="porsurtirpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" id="cantidadpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('+contadorfilas+');cambiodecantidadopreciopartida('+contadorfilas+',\''+tipo +'\');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" id="preciopartida[]" value="'+Costo+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('+contadorfilas+');cambiodecantidadopreciopartida('+contadorfilas+',\''+tipo +'\');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" id="importepartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" id="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" id="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" id="subtotalpartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" id="ivaporcentajepartida[]" value="'+Impuesto+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('+contadorfilas+');"></td>'+
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
                  '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                  '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" data-parsley-length="[0, 10]" required readonly>'+
                  '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" value="alta" readonly>'+
                  '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" value="0" readonly>'+
                '</div>'+  
                '<div class="col-md-2">'+
                  '<label>Plazo Días (proveedor)</label>'+
                  '<input type="text" class="form-control" name="plazo" id="plazo"  required>'+
                '</div>'+
                '<div class="col-md-2">'+
                  '<label>Referencia</label>'+
                  '<input type="text" class="form-control" name="referencia" id="referencia" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+
                '</div>'+
                '<div class="col-md-2">'+
                  '<label>Tipo</label>'+
                  '<select name="tipo" id="tipo" class="form-control select2" style="width:100% !important;" required>'+ 
                  '</select>'+
                '</div>'+
                '<div class="col-xs-12 col-sm-12 col-md-3">'+
                  '<label>Fecha</label>'+
                  '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();" style="min-width:95%;">'+
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
                          '<input type="text" class="form-control" name="numeroproveedor" id="numeroproveedor" required data-parsley-type="integer">'+
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
                          '<input type="text" class="form-control" name="numeroalmacen" id="numeroalmacen" required data-parsley-type="integer">'+
                          '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required data-parsley-type="integer">'+
                          '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly onkeyup="tipoLetra(this)">'+
                        '</div>'+
                      '</td>'+
                    '</tr>'+    
                  '</table>'+
                '</div>'+
                '<div class="col-md-4" id="divbuscarcodigoproducto">'+
                  '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                  '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">'+
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
                          '<input type="text" class="form-control" name="ordentrabajo" id="ordentrabajo" onkeyup="tipoLetra(this);">'+
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
                            '<th class="customercolortheadth">Código</th>'+
                            '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                            '<th class="customercolortheadth">Unidad</th>'+
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
                    '<div class="col-md-6">'+   
                      '<label>Observaciones</label>'+
                      '<textarea class="form-control" name="observaciones" id="observaciones" rows="5" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" required></textarea>'+
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
  obtenultimonumero();
  obtenertiposordenescompra(tipoalta);
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
      listarproductos();
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
      $("#ordentrabajo").attr('required', 'required');
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
  $("#ModalAlta").modal('show');
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    var numerofilas = $("#numerofilas").val();
    if(parseInt(numerofilas) > 0){
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
  $("#ordenautorizar").val(ordenautorizar);
  $("#textomodalautorizar").html("Estas seguro de autorizar la orden de compra? No."+ ordenautorizar);
  $('#autorizarorden').modal('show');
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
    $("#titulomodal").html('Modificación Orden Compra ' + data.ordencompra.Tipo);
    //formulario modificacion
    var tabs ='<div class="col-md-12">'+
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Orden <b style="color:#F44336 !important;" id="serietexto"> Serie:</b></label>'+
                    '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                    '<input type="hidden" class="form-control" name="serie" id="serie"  data-parsley-length="[0, 10]" required readonly>'+
                    '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion"  readonly>'+
                    '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                  '</div>'+  
                  '<div class="col-md-2">'+
                    '<label>Plazo Días (proveedor)</label>'+
                    '<input type="text" class="form-control" name="plazo" id="plazo"  required>'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Referencia</label>'+
                    '<input type="text" class="form-control" name="referencia" id="referencia" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Tipo</label>'+
                    '<select name="tipo" id="tipo" class="form-control select2" style="width:100% !important;" required>'+ 
                    '</select>'+
                  '</div>'+
                  '<div class="col-xs-12 col-sm-12 col-md-3">'+
                    '<label>Fecha</label>'+
                    '<input type="datetime-local" class="form-control" name="fecha" id="fecha" required onchange="validasolomesactual();" style="min-width:95%;">'+
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
                            '<input type="text" class="form-control" name="numeroproveedor" id="numeroproveedor"  required data-parsley-type="integer">'+
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
                            '<input type="text" class="form-control" name="numeroalmacen" id="numeroalmacen" required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly onkeyup="tipoLetra(this)">'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+    
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-4" id="divbuscarcodigoproducto">'+
                    '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                    '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">'+
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
                            '<input type="text" class="form-control" name="ordentrabajo" id="ordentrabajo" onkeyup="tipoLetra(this);">'+
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
                        '<table id="tablaproductodordencompra" class="table table-bordered tablaproductodordencompra">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="customercolortheadth">Código</th>'+
                              '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                              '<th class="customercolortheadth">Unidad</th>'+
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
                      '<div class="col-md-6">'+   
                        '<label>Observaciones</label>'+
                        '<textarea class="form-control" name="observaciones" id="observaciones" rows="5" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" required></textarea>'+
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
    $("#fecha").val(data.fecha);
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
      case 'TOT':
        //colocar required a campo orden
        $("#ordentrabajo").val(data.ordencompra.OrdenTrabajo).attr('required', 'required');
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
        listarproductos();
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
    obtenertiposordenescompra(data.ordencompra.Tipo);
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
    if(parseInt(numerofilas) > 0){
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
    $("#emailasunto").val("ORDEN DE COMPRA NO. " + documento +" DE USADOS TRACTOCAMIONES Y PARTES REFACCIONARIAS SA DE CV");
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
                                          '<th>OrdenCompra</th>'+
                                          '<th>Proveedor</th>'+
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
          url: ordenes_compra_buscar_folio_string_like,
          data: function (d) {
              d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Orden', name: 'Orden' },
          { data: 'Proveedor', name: 'Proveedor', orderable: false, searchable: false },
          { data: 'Total', name: 'Total', orderable: false, searchable: false  },
          { data: 'Status', name: 'Status', orderable: false, searchable: false  },
      ],
  });
}
//configurar tabla
function configurar_tabla(){
  //formulario modificacion
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
                                  '<label>DATOS ORDEN COMPRA</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Orden" id="idOrden" class="filled-in datotabla" value="Orden" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idOrden">Orden</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Status" id="idStatus" class="filled-in datotabla" value="Status" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idStatus">Status</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Proveedor" id="idProveedor" class="filled-in datotabla" value="Proveedor" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idProveedor">Proveedor</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Fecha" id="idFecha" class="filled-in datotabla" value="Fecha" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFecha">Fecha</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="AutorizadoPor" id="idAutorizadoPor" class="filled-in datotabla" value="AutorizadoPor" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idAutorizadoPor">AutorizadoPor</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="AutorizadoFecha" id="idAutorizadoFecha" class="filled-in datotabla" value="AutorizadoFecha" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idAutorizadoFecha">AutorizadoFecha</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Tipo" id="idTipo" class="filled-in datotabla" value="Tipo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTipo">Tipo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Almacen" id="idAlmacen" class="filled-in datotabla" value="Almacen" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idAlmacen">Almacen</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="SubTotal" id="idSubTotal" class="filled-in datotabla" value="SubTotal" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idSubTotal">SubTotal</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+    
                                  '<input type="checkbox" name="Iva" id="idIva" class="filled-in datotabla" value="Iva" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idIva">Iva</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Total" id="idTotal" class="filled-in datotabla" value="Total" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTotal">Total</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Equipo" id="idEquipo" class="filled-in datotabla" value="Equipo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEquipo">Equipo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Usuario" id="idUsuario" class="filled-in datotabla" value="Usuario" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idUsuario">Usuario</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Periodo" id="idPeriodo" class="filled-in datotabla" value="Periodo" onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
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
                                  '<input type="checkbox" name="Plazo" id="idPlazo" class="filled-in datotabla" value="Plazo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPlazo">Plazo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Referencia" id="idReferencia" class="filled-in datotabla" value="Referencia" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idReferencia">Referencia</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Importe" id="idImporte" class="filled-in datotabla" value="Importe" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idImporte">Importe</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Descuento" id="idDescuento" class="filled-in datotabla" value="Descuento" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idDescuento">Descuento</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Obs" id="idObs" class="filled-in datotabla" value="Obs" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idObs">Obs</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="MotivoBaja" id="idMotivoBaja" class="filled-in datotabla" value="MotivoBaja" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idMotivoBaja">MotivoBaja</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="OrdenTrabajo" id="idOrdenTrabajo" class="filled-in datotabla" value="OrdenTrabajo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idOrdenTrabajo">OrdenTrabajo</label>'+
                              '</div>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                          '</div>'+
                          '<div class="col-md-6">'+
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
                              '<div class="col-md-12 form-check">'+
                                  '<label>DATOS ALMACEN</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="NumeroAlmacen" id="idNumeroAlmacen" class="filled-in datotabla" value="NumeroAlmacen"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idNumeroAlmacen">NumeroAlmacen</label>'+  
                              '</div>'+
                              '<div class="col-md-4 form-check">'+  
                                  '<input type="checkbox" name="NombreAlmacen" id="idNombreAlmacen" class="filled-in datotabla" value="NombreAlmacen"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idNombreAlmacen">NombreAlmacen</label>'+ 
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