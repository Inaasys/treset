'use strict'
var tabla;
var form;
var contadorfilascompras = 0;
//funcion que se ejecuta al inicio
function init(){
  campos_a_filtrar_en_busquedas();
  listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 1500));
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
      $('input[type=datetime-local]').val(fechadatetimelocal);
    })
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  var serie = $("#serie").val();
  $.get(notas_credito_proveedores_obtener_ultimo_folio,{serie:serie}, function(folio){
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
        url: notas_credito_proveedores_obtener,
        data: function (d) {
            d.periodo = $("#periodo").val();
        }
    },
    "createdRow": function( row, data, dataIndex){
        if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
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
    obtenerdatos(data.Nota);
  });
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
        url: notas_credito_proveedores_obtener_series_documento
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
  $.get(notas_credito_proveedores_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie}, function(folio){
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
            url: notas_credito_proveedores_obtener_proveedores,
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
//seleccionar proveedor
function seleccionarproveedor(Numero, Nombre, Plazo, Rfc){
  var numeroproveedoranterior = $("#numeroproveedoranterior").val();
  var numeroproveedor = Numero;
  if(numeroproveedoranterior != numeroproveedor){
    $("#numeroproveedor").val(Numero);
    $("#numeroproveedoranterior").val(Numero);
    $("#proveedor").val(Nombre);
    if(Nombre != null){
      $("#textonombreproveedor").html(Nombre.substring(0, 40));
    }
    $("#rfcproveedor").val(Rfc);
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
              url: notas_credito_proveedores_obtener_almacenes,
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
    mostrarformulario();
  }
}
//listar todas las ordenes de compra
function listarcompras (){
  ocultarformulario();
  var tablacompras =  '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Compras</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                        '<div class="row">'+
                          '<div class="col-md-12">'+
                            '<div class="table-responsive">'+
                              '<table id="tbllistadocompra" class="tbllistadocompra table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                '<thead class="'+background_tables+'">'+
                                  '<tr>'+
                                    '<th>Operaciones</th>'+
                                    '<th>Compra</th>'+
                                    '<th>Fecha</th>'+
                                    '<th>UUID</th>'+
                                    '<th>Proveedor</th>'+
                                    '<th>Tipo</th>'+
                                    '<th>Almacen</th>'+
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
    $("#contenidomodaltablas").html(tablacompras);
    $('#tbllistadocompra').DataTable({
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
          url: notas_credito_proveedores_obtener_compras,
          data: function (d) {
              d.numeroproveedor = $("#numeroproveedor").val();
              d.stringcomprasseleccionadas = $("#stringcomprasseleccionadas").val();
          }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Compra', name: 'Compra' },
            { data: 'Fecha', name: 'Fecha' },
            { data: 'UUID', name: 'UUID', orderable: false, searchable: false },
            { data: 'Proveedor', name: 'Proveedor', orderable: false, searchable: false },
            { data: 'Tipo', name: 'Tipo', orderable: false, searchable: false },
            { data: 'Almacen', name: 'Almacen', orderable: false, searchable: false },
            { data: 'Total', name: 'Total', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadocompra').DataTable().search( this.value ).draw();
                }
            });
        },
        
    });  
} 
//obtener todos los datos de la orden de compra seleccionada
function seleccionarcompra(Folio, Compra, Tipo){
    $('.page-loader-wrapper').css('display', 'block');
    var tipooperacion = $("#tipooperacion").val();
    $.get(notas_credito_proveedores_obtener_compra, {Folio:Folio, Compra:Compra, contadorfilascompras:contadorfilascompras, tipooperacion:tipooperacion}, function(data){
      $("#tabladetallescomprasnotasproveedor tbody").append(data.filacompra);
      switch (Tipo) {
        case 'GASTOS':
          //desabilitar almacen
          $("#numeroalmacen").val(0).attr('readonly', 'readonly');
          $("#numeroalmacenanterior").val(0).attr('readonly', 'readonly');
          $("#almacen").val(0).attr('readonly', 'readonly');
          break;
        case 'TOT':
          //desabilitar almacen
          $("#numeroalmacen").val(0).attr('readonly', 'readonly');
          $("#numeroalmacenanterior").val(0).attr('readonly', 'readonly');
          $("#almacen").val(0).attr('readonly', 'readonly');
          break;
        default:
          $("#almacen").val(data.almacen.Nombre);
          $("#textonombrealmacen").html(data.almacen.Nombre);
          $("#numeroalmacen").val(data.almacen.Numero);
          $("#numeroalmacenanterior").val(data.almacen.Numero);
      }
      //array de compras seleccionar
      construirarraycomprasseleccionadas();
      //activar buscador de codigos
      $("#codigoabuscar").removeAttr('readonly');
      //comprobar numero de filas en la tabla
      comprobarfilascompranotaproveedor();
      //calcular totales compras nota proveedor
      calculartotalcompranotaproveedor();
      mostrarformulario();
      eliminarfilascodigos();
      $('.page-loader-wrapper').css('display', 'none');
      contadorfilascompras++;
    })
}
//crear array de compras seleccionadas
function construirarraycomprasseleccionadas(){
  var arraycomprasseleccionadas = [];
  $("tr.filascompras").each(function () { 
      // obtener los datos de la fila
      var compraaplicarpartida = $(".compraaplicarpartida", this).val();
      arraycomprasseleccionadas.push(compraaplicarpartida);
  });
  $("#stringcomprasseleccionadas").val(arraycomprasseleccionadas);
}
//calcular total por filas de tabla compras de la nota de credito proveedor
function calculartotalesfilastablacompras(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filascompras").each(function () { 
    if(fila === cuentaFilas){
      // obtener los datos de la fila:
      var totalpesoscomprapartida = $(".totalpesoscomprapartida", this).val();
      var abonoscomprapartida = $(".abonoscomprapartida", this).val();
      var notascreditocomprapartida = $(".notascreditocomprapartida", this).val();
      var descuentopesoscomprapartida = $('.descuentopesoscomprapartida', this).val();
      var saldocomprapartida = $('.saldocomprapartida', this).val();
      //saldo de la compra partida
      saldocomprapartida =  new Decimal(totalpesoscomprapartida).minus(abonoscomprapartida).minus(notascreditocomprapartida).minus(descuentopesoscomprapartida);
      $('.saldocomprapartida', this).val(number_format(round(saldocomprapartida, numerodecimales), numerodecimales, '.', ''));     
      calculartotal();
      calculartotalcompranotaproveedor();
    }  
    cuentaFilas++;
  });
}
//calcular totales de la compra de la nota de proveedor
function calculartotalcompranotaproveedor(){
  var descuentocompras = 0;
  var diferencia= 0;
  $("tr.filascompras").each(function(){
    descuentocompras = new Decimal(descuentocompras).plus($(".descuentopesoscomprapartida", this).val());
  }); 
  var totalnota = $("#totalnota").val();
  $("#descuentocompras").val(number_format(round(descuentocompras, numerodecimales), numerodecimales, '.', ''));
  diferencia = new Decimal(totalnota).minus(descuentocompras);
  $("#diferencia").val(number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
}
//eliminar una fila en la tabla de compras
function eliminarfilacompranotaproveedor(fila){
  var confirmacion = confirm("Esta seguro de eliminar la fila?"); 
  if (confirmacion == true) { 
    $("#filacompra"+fila).remove();
    contadorfilascompras--; //importante para todos los calculos se debe restar al contador
    renumerarfilascompranotaproveedor();//importante para todos los calculo en el modulo de orden de compra 
    comprobarfilascompranotaproveedor();
    calculartotalcompranotaproveedor();
    construirarraycomprasseleccionadas();
  }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilascompranotaproveedor(){
  var numerofilascompras = $("#tabladetallescomprasnotasproveedor tbody tr").length;
  $("#numerofilascompras").val(numerofilascompras);
  //quitar el almacen cuando se elijan mas de una compra
  var tipodetalles = $("#tipodetalles").val();
  var numerofilas = $("#numerofilas").val();
  if(parseInt(numerofilascompras) > parseInt(1) ){
    $("#almacen").val(0);
    $("#textonombrealmacen").html("");
    $("#numeroalmacen").val(0);
    $("#numeroalmacenanterior").val(0);
  }else if(parseInt(numerofilascompras) == parseInt(1) && parseInt(numerofilas) == parseInt(0) ){
    var compra = $(".compraaplicarpartida").val();
    $.get(notas_credito_proveedores_obtener_datos_almacen, {compra:compra}, function(data){
      switch (data.compra.Tipo){
        case 'GASTOS':
          $("#almacen").val(0);
          $("#textonombrealmacen").html("");
          $("#numeroalmacen").val(0);
          $("#numeroalmacenanterior").val(0);
          break;
        case 'TOT':
          $("#almacen").val(0);
          $("#textonombrealmacen").html("");
          $("#numeroalmacen").val(0);
          $("#numeroalmacenanterior").val(0);
          break;
        default:
          $("#almacen").val(data.almacen.Nombre);
          $("#textonombrealmacen").html(data.almacen.Nombre);
          $("#numeroalmacen").val(data.almacen.Numero);
          $("#numeroalmacenanterior").val(data.almacen.Numero);
      } 
    })
  }else if(parseInt(numerofilascompras) == parseInt(1) && parseInt(numerofilas) >= parseInt(1) && tipodetalles == 'dppp' ){
    $("#almacen").val(0);
    $("#textonombrealmacen").html("");
    $("#numeroalmacen").val(0);
    $("#numeroalmacenanterior").val(0);
  }else if(parseInt(numerofilascompras) == parseInt(1) && parseInt(numerofilas) >= parseInt(1) && (tipodetalles == '' || tipodetalles == 'codigos') ){
    var compra = $(".compraaplicarpartida").val();
    $.get(notas_credito_proveedores_obtener_datos_almacen, {compra:compra}, function(data){
      switch (data.compra.Tipo){
        case 'GASTOS':
          $("#almacen").val(0);
          $("#textonombrealmacen").html("");
          $("#numeroalmacen").val(0);
          $("#numeroalmacenanterior").val(0);
          break;
        case 'TOT':
          $("#almacen").val(0);
          $("#textonombrealmacen").html("");
          $("#numeroalmacen").val(0);
          $("#numeroalmacenanterior").val(0);
          break;
        default:
          $("#almacen").val(data.almacen.Nombre);
          $("#textonombrealmacen").html(data.almacen.Nombre);
          $("#numeroalmacen").val(data.almacen.Numero);
          $("#numeroalmacenanterior").val(data.almacen.Numero);
      } 
    })
  }
}
//renumerar las filas de la orden de compra
function renumerarfilascompranotaproveedor(){
  var lista;
  //renumerar filas tr
  lista = document.getElementsByClassName("filascompras");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("id", "filacompra"+i);
  }
  //renumerar btneliminarfilacompra
  lista = document.getElementsByClassName("btneliminarfilacompra");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onclick", "eliminarfilacompranotaproveedor("+i+')');
  }
  //renumerar descuentopesoscomprapartida
  lista = document.getElementsByClassName("descuentopesoscomprapartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilastablacompras("+i+')');
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
function obtenerproveedorpornumero(){
  var numeroproveedoranterior = $("#numeroproveedoranterior").val();
  var numeroproveedor = $("#numeroproveedor").val();
  if(numeroproveedoranterior != numeroproveedor){
    if($("#numeroproveedor").parsley().isValid()){
      $.get(notas_credito_proveedores_obtener_proveedor_por_numero, {numeroproveedor:numeroproveedor}, function(data){
        $("#numeroproveedor").val(data.numero);
        $("#numeroproveedoranterior").val(data.numero);
        $("#proveedor").val(data.nombre);
        if(data.nombre != null){
          $("#textonombreproveedor").html(data.nombre.substring(0, 40));
        }
        $("#rfcproveedor").val(data.rfc);
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
function obteneralmacenpornumero(){
  var numeroalmacenanterior = $("#numeroalmacenanterior").val();
  var numeroalmacen = $("#numeroalmacen").val();
  if(numeroalmacenanterior != numeroalmacen){
    if($("#numeroalmacen").parsley().isValid()){
      $.get(notas_credito_proveedores_obtener_almacen_por_numero, {numeroalmacen:numeroalmacen}, function(data){
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
  var numerofilascompras = $("#numerofilascompras").val();
  var codigoabuscar = $("#codigoabuscar").val().toUpperCase();
  if(parseInt(numerofilascompras) > parseInt(1) && codigoabuscar != 'DPPP'){
    msj_errorsolo1compraparadevoluciones();
  }else if(parseInt(numerofilascompras) >= parseInt(1) && codigoabuscar == 'DPPP'){
    agregarfiladppp();
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
          url: notas_credito_proveedores_obtener_productos,
          data: function (d) {
            d.codigoabuscar = $("#codigoabuscar").val();
            d.numeroalmacen = $("#numeroalmacen").val();
            d.tipooperacion = $("#tipooperacion").val();
            d.stringcomprasseleccionadas = $("#stringcomprasseleccionadas").val();
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
}
function obtenerproductoporcodigo(){  
  var numerofilascompras = $("#numerofilascompras").val();
  var codigoabuscar = $("#codigoabuscar").val().toUpperCase();
  if(parseInt(numerofilascompras) > parseInt(1) && codigoabuscar != 'DPPP'){
    msj_errorsolo1compraparadevoluciones();
  }else if(parseInt(numerofilascompras) >= parseInt(1) && codigoabuscar == 'DPPP'){
    agregarfiladppp();
  }else{
    var numeroalmacen = $("#numeroalmacen").val();
    var stringcomprasseleccionadas = $("#stringcomprasseleccionadas").val();
    var tipooperacion = $("#tipooperacion").val();
    $.get(notas_credito_proveedores_obtener_producto_por_codigo,{codigoabuscar:codigoabuscar,numeroalmacen:numeroalmacen,stringcomprasseleccionadas:stringcomprasseleccionadas}, function(data){
      if(parseInt(data.contarproductos) > 0){
        agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Impuesto, data.SubTotal, data.Existencias, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, data.NombreClaveProducto, data.NombreClaveUnidad, data.CostoDeLista);
      }else{
        msjnoseencontroningunproducto();
      }
    }) 
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
  $('#tbllistadoclaveproducto').DataTable({
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
          url: notas_credito_proveedores_obtener_claves_productos,
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
                $('#tbllistadoclaveproducto').DataTable().search( this.value ).draw();
              }
          });
      },
      
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
  $('#tbllistadoclaveunidad').DataTable({
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
          url: notas_credito_proveedores_obtener_claves_unidades,
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
                '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="DESCUENTO POR PRONTO PAGO" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off" ></td>'+
                '<td class="tdmod"><input type="text" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="ACTIV" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'+
                '<td class="tdmod">'+
                    '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-min="0.1" data-parsley-max="1.0"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');">'+
                '</td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" ></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="16.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                '<td class="tdmod" hidden><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]"  value="0" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                '<td class="tdmod">'+
                  '<div class="row divorinputmodxl">'+
                    '<div class="col-xs-2 col-sm-2 col-md-2">'+
                      '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('+contadorfilas+');" ><i class="material-icons">remove_red_eye</i></div>'+
                    '</div>'+
                    '<div class="col-xs-10 col-sm-10 col-md-10">'+    
                      '<input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="84111506" readonly data-parsley-length="[1, 20]">'+
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
                      '<input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="ACT" readonly data-parsley-length="[1, 5]">'+
                    '</div>'+
                  '</div>'+
                '</td>'+
                '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="Actividad" readonly></td>'+
              '</tr>';
    contadorproductos++;
    contadorfilas++;
    $("#tabladetallesnotaproveedor").append(fila);
    mostrarformulario();      
    comprobarfilas();
    calculartotal();
    eliminarfilascodigos();
    //colocar el tipo de detalles
    $("#tipodetalles").val("dppp");
    //colocar almacen 0
    comprobarfilascompranotaproveedor();
    $("#codigoabuscar").val("");
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
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'+Producto+'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off" ></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="'+Unidad+'" data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'+
                    '<td class="tdmod">'+
                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');revisarexistenciasalmacen('+contadorfilas+');">'+
                        '<input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                        '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'+
                        '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'+
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'+
                    '</td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'+preciopartida+'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" ></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'+Impuesto+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'+ivapesos+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'+number_format(round(total, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod" hidden><input type="text" class="form-control divorinputmodsm partidapartida" name="partidapartida[]" value="0" autocomplete="off"></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod">'+
                      '<div class="row divorinputmodxl">'+
                        '<div class="col-xs-2 col-sm-2 col-md-2">'+
                          '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('+contadorfilas+');" ><i class="material-icons">remove_red_eye</i></div>'+
                        '</div>'+
                        '<div class="col-xs-10 col-sm-10 col-md-10">'+    
                          '<input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'+ClaveProducto+'" readonly data-parsley-length="[1, 20]">'+
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
                          '<input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'+ClaveUnidad+'" readonly data-parsley-length="[1, 5]">'+
                        '</div>'+
                      '</div>'+
                    '</td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'+NombreClaveUnidad+'" readonly></td>'+
                  '</tr>';
        contadorproductos++;
        contadorfilas++;
        $("#tabladetallesnotaproveedor").append(fila);
        mostrarformulario();      
        comprobarfilas();
        calculartotal();
        eliminarfilasdppp();
        //colocar el tipo de detalles
        $("#tipodetalles").val("codigos");
        //colocar almacen 0
        comprobarfilascompranotaproveedor();
        $("#codigoabuscar").val("");
        $('.page-loader-wrapper').css('display', 'none');
    }else{
        msj_errorproductoyaagregado();
        $('.page-loader-wrapper').css('display', 'none');
    }
}
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Nota Crédito Proveedor');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
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
                                    '<div class="col-md-2">'+
                                        '<label>Nota <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                                        '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                        '<input type="hidden" class="form-control" name="uuid" id="uuid" readonly required data-parsley-length="[1, 50]">'+
                                        '<input type="hidden" class="form-control" name="stringcomprasseleccionadas" id="stringcomprasseleccionadas" readonly required>'+
                                        '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                                        '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                                        '<input type="hidden" class="form-control" name="numerofilascompras" id="numerofilascompras" readonly>'+
                                        '<input type="hidden" class="form-control" name="tipodetalles" id="tipodetalles" readonly>'+
                                        '<input type="hidden" class="form-control" name="diferenciatotales" id="diferenciatotales" readonly required>'+
                                    '</div>'+  
                                    '<div class="col-md-4">'+
                                        '<label>Proveedor  <span class="label label-danger" id="textonombreproveedor"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" id="btnobtenerproveedores" onclick="obtenerproveedores()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control" name="numeroproveedor" id="numeroproveedor" required data-parsley-type="integer" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="numeroproveedoranterior" id="numeroproveedoranterior" required data-parsley-type="integer">'+
                                                        '<input type="hidden" class="form-control" name="proveedor" id="proveedor" required readonly>'+
                                                        '<input type="hidden" class="form-control" name="rfcproveedor" id="rfcproveedor" required readonly>'+
                                                    '</div>'+
                                                '</td>'+    
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Nota Proveedor</label>'+
                                        '<input type="text" class="form-control" name="notaproveedor" id="notaproveedor"  required data-parsley-length="[1, 20]" onkeyup="tipoLetra(this)" autocomplete="off">'+
                                    '</div>'+   
                                    '<div class="col-md-3">'+
                                        '<label>Fecha</label>'+
                                        '<input type="datetime-local" class="form-control" name="fecha" id="fecha" required onchange="validasolomesactual();validarmescompra();">'+
                                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                                        '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                                    '</div>'+   
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Almacen <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td hidden>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obteneralmacenes()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control" name="numeroalmacen" id="numeroalmacen" required readonly data-parsley-type="integer" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required data-parsley-type="integer">'+
                                                        '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
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
                                                    '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="pesosmoneda" id="pesosmoneda" value="1.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                                '</td>'+
                                            '</tr>'+
                                        '</table>'+
                                    '</div>'+  
                                    '<div class="col-md-3">'+
                                        '<label>Cargar Compras Proveedor</label>'+
                                        '<div class="btn btn-block bg-blue waves-effect" id="btnlistarcompras" onclick="listarcompras()" >Agregar Compra Proveedor</div>'+
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
                                              '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" readonly autocomplete="off">'+
                                            '</div>'+
                                          '</td>'+
                                        '</tr>'+    
                                      '</table>'+
                                    '</div>'+                                   
                                '</div>'+
                            '</div>'+   
                            '<div role="tabpanel" class="tab-pane fade" id="emisorreceptortab">'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Emisor R.F.C.</label>'+
                                        '<input type="text" class="form-control" name="emisorrfc" id="emisorrfc"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Emisor Nombre</label>'+
                                        '<input type="text" class="form-control" name="emisornombre" id="emisornombre" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Receptor R.F.C.</label>'+
                                        '<input type="hidden" class="form-control" name="rfcempresa" id="rfcempresa"  value="'+rfcempresa+'" required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                        '<input type="text" class="form-control" name="receptorrfc" id="receptorrfc"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Receptor Nombre</label>'+
                                        '<input type="text" class="form-control" name="receptornombre" id="receptornombre" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                      '<label>Emitida</label>'+
                                      '<input type="datetime-local" class="form-control" name="fechaemitida" id="fechaemitida"  required readonly>'+
                                      '<input type="hidden" class="form-control" name="fechatimbrado" id="fechatimbrado" >'+
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
                                '<a href="#productostab" data-toggle="tab">DPPP ó Códigos</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#comprastab" data-toggle="tab">Compras</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                                '<div class="row">'+
                                    '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                                        '<table id="tabladetallesnotaproveedor" class="table table-bordered tabladetallesnotaproveedor">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                '<th class="'+background_tables+'">#</th>'+
                                                '<th class="customercolortheadth" hidden>Compra</th>'+
                                                '<th class="'+background_tables+'" hidden><div style="width:250px !important;">UUID</div></th>'+
                                                '<th class="customercolortheadth">Código</th>'+
                                                '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                                                '<th class="customercolortheadth">Uda</th>'+
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
                                                '<th class="customercolortheadth" hidden>Partida</th>'+
                                                '<th class="customercolortheadth">Precio Moneda</th>'+
                                                '<th class="customercolortheadth">Descuento</th>'+
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
                            '<div role="tabpanel" class="tab-pane fade" id="comprastab">'+
                                '<div class="row">'+
                                  '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                                    '<table id="tabladetallescomprasnotasproveedor" class="table table-bordered tabladetallescomprasnotasproveedor">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                            '<th class="'+background_tables+'">#</th>'+
                                            '<th class="customercolortheadth">Compra</th>'+
                                            '<th class="'+background_tables+'">Fecha</th>'+
                                            '<th class="'+background_tables+'">Factura</th>'+
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
                              '<textarea class="form-control" name="observaciones" id="observaciones" rows="5" onkeyup="tipoLetra(this);"  required data-parsley-length="[1, 255]"></textarea>'+
                          '</div>'+ 
                          '<div class="col-md-3">'+
                              '<table class="table table-striped table-hover">'+
                                  '<tr>'+
                                      '<td class="tdmod">Total Nota</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalnota" id="totalnota" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td class="tdmod">Descuentos</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentocompras" id="descuentocompras" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
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
                                      '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importexml" id="importexml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td class="tdmod">Descuento</td>'+
                                      '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                      '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentoxml" id="descuentoxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
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
                '</div>';
  $("#tabsform").html(tabs);
  $("#serie").val(serieusuario);
  $("#serietexto").html("Serie: "+serieusuario);
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
  contadorfilascompras = 0;
  //activar busqueda de codigos
  $("#codigoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerproductoporcodigo();
    }
  });
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
  //regresar numero almacen
  $('#numeroalmacen').on('change', function(e) {
    regresarnumeroalmacen();
  });
  $("#ModalAlta").modal('show');
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
          url:notas_credito_proveedor_cargar_xml_alta,
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
            $("#uuid").val(data.uuid[0]);
            $("#uuidxml").val(data.uuid[0]);
            $("#emisorrfc").val(data.array_emisor.Rfc[0]);
            $("#emisornombre").val(data.array_emisor.Nombre[0]);
            $("#receptorrfc").val(data.array_receptor.Rfc[0]);
            $("#receptornombre").val(data.array_receptor.Nombre[0]);
            $("#notaproveedor").val(data.array_comprobante.Serie[0]+data.array_comprobante.Folio[0]);
            $("#importexml").val(number_format(round(importexml, numerodecimales), numerodecimales, '.', ''));
            $("#descuentoxml").val(number_format(round(descuentoxml, numerodecimales), numerodecimales, '.', ''));
            $("#subtotalxml").val(number_format(round(subtotalxml, numerodecimales), numerodecimales, '.', ''));
            $("#ivaxml").val(number_format(round(ivaxml, numerodecimales), numerodecimales, '.', ''));
            $("#totalxml").val(number_format(round(totalxml, numerodecimales), numerodecimales, '.', ''));
            $("#fechaemitida").val(data.array_comprobante.Fecha[0]);
            //validar si la fecha de la compra es igual a la fecha de la factura del proveedor
            validarmescompra();
            //machar totales factura proveedor y orden de compra
            var totalxml = $("#totalxml").val();
            var total = $("#total").val();
            if(parseFloat(total) > parseFloat(totalxml)){
              var diferencia = new Decimal(total).minus(totalxml);
              $("#diferenciafacturaproveedor").html("Diferencia del total por : $ "+number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
            }else if(parseFloat(total) < parseFloat(totalxml)){
              var diferencia = new Decimal(totalxml).minus(total);
              $("#diferenciafacturaproveedor").html("Diferencia del total por : $ -"+number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
            }else if(parseFloat(total) == parseFloat(totalxml)){
              $("#diferenciafacturaproveedor").html("");
            }
            $("#diferenciatotales").val(number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
            //mostrar el total de la factura del proveedor
            $("#totalfacturaproveedor").html("Total factura proveedor :"+ number_format(round(totalxml, numerodecimales), numerodecimales, '.', ''))
          },
          error: function (data) {
              console.log(data);
          }
      });                      
});
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
      calculartotalcompranotaproveedor();
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
  //machar totales factura proveedor y note de credito
  var totalxml = $("#totalxml").val();
  if(parseFloat(total) > parseFloat(totalxml)){
    var diferencia = new Decimal(total).minus(totalxml);
    $("#diferenciafacturaproveedor").html("Diferencia del total por : $ "+number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
  }else if(parseFloat(total) < parseFloat(totalxml)){
    var diferencia = new Decimal(totalxml).minus(total);
    $("#diferenciafacturaproveedor").html("Diferencia del total por : $ -"+number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
  }else if(parseFloat(total) == parseFloat(totalxml)){
    $("#diferenciafacturaproveedor").html("");
  }
  $("#diferenciatotales").val(number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
}
//validar que la fecha de la compra sea la misma que la fecha de emision de la factura del proveedor y validar que la fecha de la compra solo sea del mismo mes y año en curso
function validarmescompra(){
  var fechaxml = new Date($("#fechaemitida").val());
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
  }
}
//eliminar una fila en la tabla
function eliminarfila(fila){
  var confirmacion = confirm("Esta seguro de eliminar la fila?"); 
  if (confirmacion == true) { 
    $("#filaproducto"+fila).remove();
    contadorfilas--; //importante para todos los calculo en el modulo de orden de compra se debe restar al contadorfilas la fila que se acaba de eliminar
    renumerarfilas();//importante para todos los calculo en el modulo de orden de compra 
    comprobarfilas();
    comprobarfilascompranotaproveedor();
    calculartotal();
  }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
  var numerofilas = $("#tabladetallesnotaproveedor tbody tr").length;
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
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+');revisarexistenciasalmacen('+i+')');
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
//revisar si hay existencias de la partida en el almacen
function revisarexistenciasalmacen(fila){
  var stringcomprasseleccionadas = $("#stringcomprasseleccionadas").val();
  var folio = $("#folio").val();
  var serie = $("#serie").val();
  var almacen = $("#numeroalmacen").val();
  var codigopartida = $("#filaproducto"+fila+" .codigopartida").val();
  var cantidadpartida = $("#filaproducto"+fila+" .cantidadpartida").val();
  var realizarbusquedaexistencias = $("#filaproducto"+fila+" .realizarbusquedaexistencias").val();
  if(realizarbusquedaexistencias === "1"){
    comprobarexistenciaspartida(almacen, codigopartida, folio, serie, stringcomprasseleccionadas, cantidadpartida).then(nuevaexistencia=>{
      $("#filaproducto"+fila+" .cantidadpartida").attr('data-parsley-existencias',nuevaexistencia);
      $("#filaproducto"+fila+" .cantidadpartida").parsley().validate();
    })
  }
}
//funcion asincrona para buscar existencias de la partida
function comprobarexistenciaspartida(almacen, codigopartida, folio, serie, stringcomprasseleccionadas, cantidadpartida){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $.get(notas_credito_proveedor_obtener_existencias_partida,{'almacen':almacen,'codigopartida':codigopartida,'folio':folio,'serie':serie,'stringcomprasseleccionadas':stringcomprasseleccionadas,'cantidadpartida':cantidadpartida},nuevaexistencia=>{
        return ejecuta(nuevaexistencia);
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
    var numerofilascompras = $("#numerofilascompras").val();
    if(parseInt(numerofilas) > 0 && parseInt(numerofilascompras) > 0){
      var diferencia = $("#diferencia").val();
      if(parseFloat(diferencia) <= parseFloat(0.01)){
        var diferenciatotales = $("#diferenciatotales").val();
        if(diferenciatotales <= parseFloat(0.01)){
          var emisorrfc = $("#emisorrfc").val();
          var rfcproveedor = $("#rfcproveedor").val();
          if(emisorrfc == rfcproveedor){
            var receptorrfc = $("#receptorrfc").val();
            var rfcempresa = $("#rfcempresa").val();
            if(receptorrfc == rfcempresa){
              $('.page-loader-wrapper').css('display', 'block');
              $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url:notas_credito_proveedor_guardar,
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
  $.get(notas_credito_proveedores_obtener_nota_proveedor,{notamodificar:notamodificar },function(data){
    $("#titulomodal").html('Modificación Nota Crédito Proveedor --- STATUS : ' + data.notaproveedor.Status);
  //formulario modificacion
  var tabs ='<div class="row">'+
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
                      '<div class="col-md-2">'+
                        '<label>Nota <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b></label>'+
                        '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                        '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                        '<input type="hidden" class="form-control" name="uuid" id="uuid" readonly required data-parsley-length="[1, 50]">'+
                        '<input type="hidden" class="form-control" name="stringcomprasseleccionadas" id="stringcomprasseleccionadas" readonly required>'+
                        '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                        '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                        '<input type="hidden" class="form-control" name="numerofilascompras" id="numerofilascompras" readonly>'+
                        '<input type="hidden" class="form-control" name="tipodetalles" id="tipodetalles" readonly>'+
                        '<input type="hidden" class="form-control" name="diferenciatotales" id="diferenciatotales" readonly required>'+
                        '<input type="hidden" class="form-control" name="notaproveedorbd" id="notaproveedorbd" readonly required>'+
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
                                '<input type="text" class="form-control" name="numeroproveedor" id="numeroproveedor" required data-parsley-type="integer" autocomplete="off">'+
                                '<input type="hidden" class="form-control" name="numeroproveedoranterior" id="numeroproveedoranterior" required data-parsley-type="integer">'+
                                '<input type="hidden" class="form-control" name="proveedor" id="proveedor" required readonly>'+
                                '<input type="hidden" class="form-control" name="rfcproveedor" id="rfcproveedor" required readonly>'+
                              '</div>'+
                            '</td>'+    
                          '</tr>'+    
                        '</table>'+
                      '</div>'+
                      '<div class="col-md-3">'+
                        '<label>Nota Proveedor</label>'+
                        '<input type="text" class="form-control" name="notaproveedor" id="notaproveedor"  required onkeyup="tipoLetra(this)" data-parsley-length="[1, 20]" autocomplete="off">'+
                      '</div>'+   
                      '<div class="col-md-3">'+
                        '<label>Fecha</label>'+
                        '<input type="datetime-local" class="form-control" name="fecha" id="fecha" required onchange="validasolomesactual();validarmescompra();">'+
                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                        '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                      '</div>'+   
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-3">'+
                        '<label>Almacen  <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                        '<table class="col-md-12">'+
                          '<tr>'+
                            '<td hidden>'+
                              '<div class="btn bg-blue waves-effect" onclick="obteneralmacenes()">Seleccionar</div>'+
                            '</td>'+
                            '<td>'+
                              '<div class="form-line">'+
                                '<input type="text" class="form-control" name="numeroalmacen" id="numeroalmacen" required readonly data-parsley-type="integer" autocomplete="off">'+
                                '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required data-parsley-type="integer">'+
                                '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly>'+
                              '</div>'+
                            '</td>'+
                          '</tr>'+    
                        '</table>'+
                      '</div>'+
                      '<div class="col-md-3" id="divbuscarcodigoproducto">'+
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
                              '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="pesosmoneda" id="pesosmoneda" value="1.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</td>'+
                          '</tr>'+
                        '</table>'+
                      '</div>'+  
                      '<div class="col-md-3">'+
                        '<label>Cargar Compras Proveedor</label>'+
                        '<div class="btn btn-block bg-blue waves-effect" id="btnlistarcompras" onclick="listarcompras()" >Agregar Compra Proveedor</div>'+
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
                                '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" readonly autocomplete="off">'+
                              '</div>'+
                            '</td>'+
                          '</tr>'+    
                        '</table>'+
                      '</div>'+                                  
                    '</div>'+
                  '</div>'+   
                  '<div role="tabpanel" class="tab-pane fade" id="emisorreceptortab">'+
                    '<div class="row">'+
                      '<div class="col-md-3">'+
                        '<label>Emisor R.F.C.</label>'+
                        '<input type="text" class="form-control" name="emisorrfc" id="emisorrfc"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                      '</div>'+
                      '<div class="col-md-3">'+
                        '<label>Emisor Nombre</label>'+
                        '<input type="text" class="form-control" name="emisornombre" id="emisornombre" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-3">'+
                        '<label>Receptor R.F.C.</label>'+
                        '<input type="hidden" class="form-control" name="rfcempresa" id="rfcempresa"  value="'+rfcempresa+'" required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                        '<input type="text" class="form-control" name="receptorrfc" id="receptorrfc"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                      '</div>'+
                      '<div class="col-md-3">'+
                        '<label>Receptor Nombre</label>'+
                        '<input type="text" class="form-control" name="receptornombre" id="receptornombre" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-3">'+
                        '<label>Emitida</label>'+
                        '<input type="datetime-local" class="form-control" name="fechaemitida" id="fechaemitida"  required readonly>'+
                        '<input type="hidden" class="form-control" name="fechatimbrado" id="fechatimbrado" >'+
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
                    '<a href="#productostab" data-toggle="tab">DPPP ó Códigos</a>'+
                  '</li>'+
                  '<li role="presentation">'+
                    '<a href="#comprastab" data-toggle="tab">Compras</a>'+
                  '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                  '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                    '<div class="row">'+
                      '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                        '<table id="tabladetallesnotaproveedor" class="table table-bordered tabladetallesnotaproveedor">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="customercolortheadth" hidden>Compra</th>'+
                              '<th class="'+background_tables+'" hidden><div style="width:250px !important;">UUID</div></th>'+
                              '<th class="customercolortheadth">Código</th>'+
                              '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                              '<th class="customercolortheadth">Uda</th>'+
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
                              '<th class="customercolortheadth" hidden>Partida</th>'+
                              '<th class="customercolortheadth">Precio Moneda</th>'+
                              '<th class="customercolortheadth">Descuento</th>'+
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
                  '<div role="tabpanel" class="tab-pane fade" id="comprastab">'+
                    '<div class="row">'+
                      '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                        '<table id="tabladetallescomprasnotasproveedor" class="table table-bordered tabladetallescomprasnotasproveedor">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="customercolortheadth">Compra</th>'+
                              '<th class="'+background_tables+'">Fecha</th>'+
                              '<th class="'+background_tables+'">Factura</th>'+
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
                    '<textarea class="form-control" name="observaciones" id="observaciones" rows="5" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
                  '</div>'+ 
                  '<div class="col-md-3">'+
                    '<table class="table table-striped table-hover">'+
                      '<tr>'+
                        '<td class="tdmod">Total Nota</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalnota" id="totalnota" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                      '</tr>'+
                      '<tr>'+
                        '<td class="tdmod">Descuentos</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentocompras" id="descuentocompras" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
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
                        '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importexml" id="importexml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                      '</tr>'+
                      '<tr>'+
                        '<td class="tdmod">Descuento</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentoxml" id="descuentoxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
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
            '</div>';
    $("#tabsform").html(tabs);  
    //esconder el div del boton listar ordenes
    $("#btnobtenerproveedores").hide();
    $("#btnlistarcompras").hide();
    $("#folio").val(data.notaproveedor.Folio);
    $("#serie").val(data.notaproveedor.Serie);
    $("#serietexto").html("Serie: "+data.notaproveedor.Serie);
    $("#uuidxml").val(data.notaproveedor.UUID);
    $("#uuid").val(data.notaproveedor.UUID);
    $("#notaproveedorbd").val(data.notaproveedor.Nota);
    $("#stringcomprasseleccionadas").val(data.arraycompras);
    $("#numerofilas").val(data.numerodetallesnotaproveedor);
    $("#numerofilascompras").val(data.numerodocumentosnotaproveedor);
    $("#tipodetalles").val(data.tipodetalles);
    $("#fecha").val(data.fecha);
    $("#fechaemitida").val(data.fechaemitida);
    $("#proveedor").val(data.proveedor.Nombre);
    if(data.proveedor.Nombre != null){
      $("#textonombreproveedor").html(data.proveedor.Nombre.substring(0, 40));
    }
    $("#numeroproveedor").val(data.proveedor.Numero);
    $("#numeroproveedoranterior").val(data.proveedor.Numero);
    $("#rfcproveedor").val(data.proveedor.Rfc);
    $("#notaproveedor").val(data.notaproveedor.NotaProveedor);
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
    $("#moneda").val(data.notaproveedor.Moneda).change();
    $("#pesosmoneda").val(data.tipocambio);
    $("#observaciones").val(data.notaproveedor.Obs);
    $("#emisorrfc").val(data.notaproveedor.EmisorRfc);
    $("#emisornombre").val(data.notaproveedor.EmisorNombre);
    $("#receptorrfc").val(data.notaproveedor.ReceptorRfc);
    $("#receptornombre").val(data.notaproveedor.ReceptorNombre);
    //cargar todos los detalles
    $("#tabladetallesnotaproveedor tbody").html(data.filasdetallesnotaproveedor);
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
    //totales xml
    $("#importexml").val(data.importe);
    $("#descuentoxml").val(data.descuento);
    $("#iepsxml").val(data.ieps);
    $("#subtotalxml").val(data.subtotal);
    $("#ivaxml").val(data.iva);
    $("#retencionivaxml").val(data.ivaretencion);
    $("#retencionisrxml").val(data.isrretencion);
    $("#retencioniepsxml").val(data.iepsretencion);
    $("#totalxml").val(data.total);    
    //cargar nota proveedor documentos
    $("#tabladetallescomprasnotasproveedor tbody").html(data.filasdocumentosnotaproveedor);
    //totales descuentos y nota
    $("#totalnota").val(data.total);
    $("#descuentocompras").val(data.descuentocompras);
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
    contadorfilascompras = data.contadorfilascompras;
    //activar busqueda de codigos
    $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenerproductoporcodigo();
      }
    });
    //regresar numero proveedor
    $('#numeroproveedor').on('change', function(e) {
      regresarnumeroproveedor();
    });
    //regresar numero almacen
    $('#numeroalmacen').on('change', function(e) {
      regresarnumeroalmacen();
    });
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
    var diferencia = $("#diferencia").val();
    if(parseFloat(diferencia) <= parseFloat(0.01)){
      var diferenciatotales = $("#diferenciatotales").val();
      if(diferenciatotales <= parseFloat(0.01)){
        var emisorrfc = $("#emisorrfc").val();
        var rfcproveedor = $("#rfcproveedor").val();
        if(emisorrfc == rfcproveedor){
          var receptorrfc = $("#receptorrfc").val();
          var rfcempresa = $("#rfcempresa").val();
          if(receptorrfc == rfcempresa){
            $('.page-loader-wrapper').css('display', 'block');
            $.ajax({
              headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
              url:notas_credito_proveedores_guardar_modificacion,
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
      msj_errorendiferenciatotalnotatotaldescuentos();
    }
  }else{
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(notadesactivar){
  $.get(notas_credito_proveedores_verificar_uso_en_modulos,{notadesactivar:notadesactivar}, function(data){
    if(data.Status == 'BAJA'){
      $("#notadesactivar").val(0);
      $("#textomodaldesactivar").html('Error, esta nota credito proveedor ya fue dado de baja');
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
        $("#notadesactivar").val(notadesactivar);
        $("#textomodaldesactivar").html('Estas seguro de dar de baja la nota crédito proveedor? No'+notadesactivar);
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
      url:notas_credito_proveedores_alta_o_baja,
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
  $.get(notas_credito_proveedores_obtener_datos_envio_email,{documento:documento}, function(data){
    $("#textomodalenviarpdfemail").html("Enviar email Nota de Crédito Proveedor No." + documento);
    $("#emaildocumento").val(documento);
    $("#emailde").val(data.emailde);
    $("#emailpara").val(data.emailpara);
    $("#emailasunto").val("NOTA DE CRÉDITO PROVEEDOR NO. " + documento +" DE "+ nombreempresa);
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
      url:notas_credito_proveedores_enviar_pdfs_email,
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
                                                '<th>Nota</th>'+
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
      processing: true,
      serverSide: true,
      ajax: {
          url: notas_credito_proveedores_buscar_folio_string_like,
          data: function (d) {
            d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Nota', name: 'Nota' },
          { data: 'Proveedor', name: 'Proveedor', orderable: false, searchable: false },
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