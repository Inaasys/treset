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
  $.get(cotizaciones_obtener_ultimo_id,{serie:serie}, function(folio){
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
        url: cotizaciones_obtener,
        data: function (d) {
            d.periodo = $("#periodo").val();
        }
    },
    "createdRow": function( row, data, dataIndex){
        if( data.status ==  `BAJA`){ $(row).addClass('bg-orange');}
    },
    columns: campos_tabla,
    "drawCallback": function( data ) {
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
    }
  });
  //modificacion al dar doble click
  $('#tbllistado tbody').on('dblclick', 'tr', function () {
    var data = tabla.row( this ).data();
    obtenerdatos(data.cotizacion);
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
        url: cotizaciones_obtener_series_documento
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
}
function seleccionarseriedocumento(serie){
  $.get(cotizaciones_obtener_ultimo_folio_serie_seleccionada, {serie:serie}, function(folio){
      $("#folio").val(folio);
      $("#serie").val(serie);
      $("#serietexto").html("Serie: "+serie);
      mostrarformulario();
  }) 
}
//obtener registros de remisiones
function obtenerremisiones(){
  ocultarformulario();
  var tablaremisiones = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Remisiones</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoremision" class="tbllistadoremision table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Remisión</th>'+
                                                    '<th>Cliente</th>'+
                                                    '<th>Tipo</th>'+
                                                    '<th>Unidad</th>'+
                                                    '<th>Requisición</th>'+
                                                    '<th>Total</th>'+
                                                    '<th>Status</th>'+
                                                    '<th>Selecciona</th>'+
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
                            '<button type="button" class="btn btn-success btn-sm" onclick="cargarremisionesseleccionadas();">Cargar remisiones</button>'+
                        '</div>';
    $("#contenidomodaltablas").html(tablaremisiones);
    $('#tbllistadoremision').DataTable({
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
          url: cotizaciones_obtener_remisiones,
          data: function (d) {
            d.tipooperacion = $("#tipooperacion").val();
            d.stringremisionesseleccionadas = $("#stringremisionesseleccionadas").val();
          }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Remision', name: 'Remision' },
            { data: 'Cliente', name: 'Cliente' },
            { data: 'Tipo', name: 'Tipo', orderable: false, searchable: false },
            { data: 'Unidad', name: 'Unidad', orderable: false, searchable: false },
            { data: 'Rq', name: 'Rq', orderable: false, searchable: false },
            { data: 'Total', name: 'Total', orderable: false, searchable: false },
            { data: 'Status', name: 'Status', orderable: false, searchable: false },
            { data: 'Selecciona', name: 'Selecciona', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoremision').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
} 
function construirarrayremisionesseleccionadas(){
  var arrayremisionesseleccionadas = [];
  var lista = document.getElementsByClassName("remisionesseleccionadas");
  for (var i = 0; i < lista.length; i++) {
    if(lista[i].checked){
      arrayremisionesseleccionadas.push(lista[i].value);
    }
  }
  $("#stringremisionesseleccionadas").val(arrayremisionesseleccionadas.sort());
}
var arrayclientesremisionesseleccionadas = [];
//obtener todos los datos de la orden de compra seleccionada
function seleccionarremision(Folio,Remision,Cliente){
  if( $('#idremisionesseleccionadas'+Remision).prop('checked') ) {
    var cantidadclientesremisionesseleccionadas = arrayclientesremisionesseleccionadas.length;
    if(cantidadclientesremisionesseleccionadas == 0){
      arrayclientesremisionesseleccionadas.push(Cliente);
    }else{
      var existeclienteenarray = arrayclientesremisionesseleccionadas.indexOf(Cliente);
      if(existeclienteenarray == -1){
        alert("Solo se debe seleccionar un mismo cliente para cotizar");
        $('#idremisionesseleccionadas'+Remision).removeAttr('checked');
      }else{
        arrayclientesremisionesseleccionadas.push(Cliente);
      }
    }
  }else{
    arrayclientesremisionesseleccionadas.pop();
  }
  construirarrayremisionesseleccionadas();
  //calculartotalafacturar("REMISIONES");
}
//cargar remisiones
var contadorproductos=0;
var contadorfilas = 0;
var partida = 1;
function cargarremisionesseleccionadas(){
  contadorfilas = 0;
  partida = 1;
  $("#tablaproductocotizacion tbody").html("");
  var tipooperacion = $("#tipooperacion").val();
  var stringremisionesseleccionadas = $("#stringremisionesseleccionadas").val();
  $.get(cotizaciones_obtener_remision, {stringremisionesseleccionadas:stringremisionesseleccionadas, contadorfilas:contadorfilas, partida:partida, tipooperacion:tipooperacion}, function(data){
    $("#tablaproductocotizacion tbody").append(data.filasremisiones);
    //array de remisiones seleccionadas
    construirarrayremisionesseleccionadas();
    //comprobar numero de filas en la tabla
    comprobarfilas();
    //calcular totales
    calculartotales();
    contadorfilas = data.contadorfilas;
    partida = data.partida;
    remisionagregadacorrectamente();
    mostrarformulario();
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
  })
}

//eliminar todas las filas de la remision que se deselecciono
function eliminarfilasremisiondeseleccionada(Remision){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
    // obtener los datos de la fila
    var remisionpartida = $(".remisionpartida", this).val();
    if(remisionpartida == Remision){
      eliminarfila(cuentaFilas).then(fila=>{  
      })      
    }
    cuentaFilas++;
  });  
  msjsuccesseliminacionpartidasremision();
}
async function msjsuccesseliminacionpartidasremision(){
  await retraso();
  remisioneliminadacorrectamente();
  construirarrayremisionesseleccionadas();
  renumerarfilas();//importante para todos los calculo en el modulo
  comprobarfilas();
  calculartotal();
}
/*
//obtener datos de remision seleccionada
function seleccionarremision(Folio, Remision){
    var tipooperacion = $("#tipooperacion").val();
    $("#numeroremision").val(Folio);
    $("#remision").val(Remision);
    mostrarformulario();
    $('.page-loader-wrapper').css('display', 'block');
    $.get(cotizaciones_obtener_remision, {Folio:Folio, Remision:Remision, tipooperacion:tipooperacion}, function(data){
      //if(data.cotizacionyaexistente == 1){
      //  msjremisionyautilizada();
      //}else{
        $("#tablaproductocotizacion tbody").html(data.filasdetallesremision);
        //$("#ottecnodiesel").val(data.remision.Referencia);
        //$("#ottyt").val(data.remision.Os);
        //$("#equipo").val(data.remision.Eq);
        //$("#requisicion").val(data.remision.Rq);
        $("#subtotal").val(data.subtotal);
        $("#iva").val(data.iva);
        $("#total").val(data.total);
        $("#numerofilas").val(data.numerodetallesremision);
      //}
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
    }) 
}
*/
//calcular total de cada fila
function calculartotalesfilas(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
    if(fila === cuentaFilas){
      // obtener los datos de la fila:
      var cantidadpartida = $(".cantidadpartida", this).val();
      var preciopartida = $('.preciopartida', this).val();
      var importepartida = $('.importepartida', this).val();
      //importe de la partida
      importepartida =  new Decimal(cantidadpartida).times(preciopartida);
      $('.importepartida', this).val(number_format(round(importepartida, numerodecimales), numerodecimales, '.', ''));
      calculartotales();
    }  
    cuentaFilas++;
  });
}
//calcular totales
function calculartotales(){
  var subtotal= 0;
  var iva = 0;
  var total = 0;
  $("tr.filasproductos").each(function(){
    subtotal= new Decimal(subtotal).plus($(".importepartida", this).val());
  }); 
  //subtotal
  $("#subtotal").val(number_format(round(subtotal, numerodecimales), numerodecimales, '.', ''));
  //iva
  var multiplicacioniva= new Decimal(subtotal).times(16);
  iva = new Decimal(multiplicacioniva/100);
  $("#iva").val(number_format(round(iva, numerodecimales), numerodecimales, '.', ''));
  //total
  total = new Decimal(subtotal).plus(iva);
  $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
}
//eliminar una fila en la tabla de precios clientes
function eliminarfila(numerofila){
  var confirmacion = confirm("Esta seguro de eliminar la fila?"); 
  if (confirmacion == true) { 
    $("#filaproducto"+numerofila).remove();
    comprobarfilas();
    renumerarfilas();//importante para todos los calculo en el modulo de orden de compra 
    calculartotales();
  }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
  var numerofilas = $("#tablaproductocotizacion tbody tr").length;
  $("#numerofilas").val(numerofilas);
}
//renumerar las filas de la orden de compra
function renumerarfilas(){
  var lista;
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("cantidadpartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+');');
  }
  //renumero el precio de la partida
  lista = document.getElementsByClassName("preciopartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+');');
  }
}  
//alta
function alta(){
  $("#titulomodal").html('Alta Cotización');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs ='<div class="col-md-12">'+  
              '<div class="row">'+
                '<div class="col-md-3">'+
                  '<label>Cotización <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                  '<input type="text" class="form-control inputnextdet" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                  '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                  '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" value="alta" readonly>'+
                  '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" value="0" readonly>'+
                  '<input type="hidden" class="form-control" name="stringremisionesseleccionadas" id="stringremisionesseleccionadas" readonly required>'+
                '</div>'+ 
                '<div class="col-md-3">'+
                  '<label>Fecha </label>'+
                  '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required  data-parsley-excluded="true" onkeydown="return false">'+
                  '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="{{$periodohoy}}">'+
                '</div>'+
                '<div class="col-md-3">'+
                  '<label>Agregar Remisiones</label>'+
                  '<table class="col-md-12">'+
                    '<tr>'+
                      '<td>'+
                        '<div class="btn bg-blue waves-effect" onclick="obtenerremisiones()">Agregar Remisiones</div>'+
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
                    '<div class="col-md-12 table-responsive cabecerafija" style="height: 275px;overflow-y: scroll;padding: 0px 0px;">'+
                      '<table id="tablaproductocotizacion" class="table table-bordered tablaproductocotizacion">'+
                        '<thead class="'+background_tables+'">'+
                          '<tr>'+
                            '<th class="'+background_tables+'">#</th>'+
                            '<th class="'+background_tables+'">operaciones</th>'+
                            '<th class="'+background_tables+'">num remision</th>'+
                            '<th class="'+background_tables+'">insumo</th>'+
                            '<th class="'+background_tables+'"><div style="width:100px !important;">numero_parte</div></th>'+
                            '<th class="customercolortheadth"><div style="width:200px !important;">descripcion</div></th>'+
                            '<th class="customercolortheadth">cantidad</th>'+
                            '<th class="'+background_tables+'">unidad</th>'+
                            '<th class="'+background_tables+'">num equipo</th>'+
                            '<th class="'+background_tables+'">ot tyt</th>'+
                            '<th class="'+background_tables+'">ot tecnodiesel</th>'+
                            '<th class="customercolortheadth">precio $</th>'+
                            '<th class="'+background_tables+'">importe $</th>'+
                            '<th class="'+background_tables+'">fecha remision</th>'+
                            '<th class="'+background_tables+'">tipo cambio</th>'+
                            '<th class="'+background_tables+'">usd</th>'+
                            '<th class="customercolortheadth">status_refaccion</th>'+
                          '</tr>'+
                        '</thead>'+
                        '<tbody>'+           
                        '</tbody>'+
                      '</table>'+
                    '</div>'+
                  '</div>'+ 
                  '<div class="row">'+
                    '<div class="col-md-6">'+   
                    '</div>'+ 
                    '<div class="col-md-3 col-md-offset-3">'+
                      '<table class="table table-striped table-hover">'+
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
  obtenultimonumero();
  asignarfechaactual();
  $("#ModalAlta").modal('show');
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
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    var numerofilas = $("#numerofilas").val();
    if(numerofilas > 0){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:cotizaciones_guardar,
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
function desactivar(cotizaciondesactivar){
  $.get(cotizaciones_verificar_uso_en_modulos,{cotizaciondesactivar:cotizaciondesactivar}, function(data){
    if(data.status == 'BAJA'){
      $("#cotizaciondesactivar").val(0);
      $("#textomodaldesactivar").html('Error, esta Cotización ya fue dado de baja');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{   
      if(data.resultadofechas != ''){
        $("#cotizaciondesactivar").val(0);
        $("#textomodaldesactivar").html('Error solo se pueden dar de baja las cotizaciones del mes actual, fecha de la cotización: ' + data.resultadofechas);
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        $("#cotizaciondesactivar").val(cotizaciondesactivar);
        $("#textomodaldesactivar").html('Estas seguro de desactivar la cotización? No'+ cotizaciondesactivar);
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
      url:cotizaciones_alta_o_baja,
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
function obtenerdatos(cotizacionmodificar){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(cotizaciones_obtener_cotizacion,{cotizacionmodificar:cotizacionmodificar },function(data){
    $("#titulomodal").html('Modificación Cotizacion --- STATUS : ' + data.cotizacion.status);
    //formulario modificacion
    var tabs ='<div class="col-md-12">'+  
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Cotización <b style="color:#F44336 !important;" id="serietexto"> Serie: </b></label>'+
                    '<input type="text" class="form-control inputnextdet" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                    '<input type="hidden" class="form-control" name="serie" id="serie" required readonly data-parsley-length="[1, 10]">'+
                    '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                    '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" value="0" readonly>'+
                  '</div>'+ 
                  '<div class="col-md-3">'+
                    '<label>OT Tecnodiesel</label>'+
                    '<input type="text" class="form-control inputnext" name="ottecnodiesel" id="ottecnodiesel"  required  onkeyup="tipoLetra(this);" data-parsley-length="[1, 50]" autocomplete="off">'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>OT TyT</label>'+
                    '<input type="text" class="form-control inputnext" name="ottyt" id="ottyt" required data-parsley-length="[0, 50]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                  '</div>'+ 
                  '<div class="col-md-3">'+
                    '<label>Fecha </label>'+
                    '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required  data-parsley-excluded="true" onkeydown="return false">'+
                    '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy">'+
                  '</div>'+
                '</div>'+
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Remisión</label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" onclick="obtenerremisiones()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="hidden" class="form-control" name="numeroremision" id="numeroremision" required readonly onkeyup="tipoLetra(this)" autocomplete="off">'+
                            '<input type="text" class="form-control inputnext" name="remision" id="remision" required readonly>'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+   
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>Equipo</label>'+
                    '<input type="text" class="form-control inputnext" name="equipo" id="equipo" required onkeyup="tipoLetra(this);" data-parsley-length="[1, 50]" autocomplete="off">'+
                  '</div>'+ 
                  '<div class="col-md-3">'+
                    '<label>Requisición</label>'+
                    '<input type="text" class="form-control inputnext" name="requisicion" id="requisicion" required onkeyup="tipoLetra(this);" data-parsley-length="[1, 50]" autocomplete="off">'+
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
                      '<div class="col-md-12 table-responsive cabecerafija" style="height: 275px;overflow-y: scroll;padding: 0px 0px;">'+
                        '<table id="tablaproductocotizacion" class="table table-bordered tablaproductocotizacion">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="customercolortheadth"><div style="width:100px !important;">numero_parte</div></th>'+
                              '<th class="customercolortheadth"><div style="width:200px !important;">descripcion</div></th>'+
                              '<th class="customercolortheadth">unidad</th>'+
                              '<th class="'+background_tables+'">insumo</th>'+
                              '<th class="customercolortheadth">status_refaccion</th>'+
                              '<th class="customercolortheadth">precio $</th>'+
                              '<th class="customercolortheadth">cantidad</th>'+
                              '<th class="'+background_tables+'">importe $</th>'+
                            '</tr>'+
                          '</thead>'+
                          '<tbody>'+           
                          '</tbody>'+
                        '</table>'+
                      '</div>'+
                    '</div>'+ 
                    '<div class="row">'+
                      '<div class="col-md-6">'+   
                      '</div>'+ 
                      '<div class="col-md-3 col-md-offset-3">'+
                        '<table class="table table-striped table-hover">'+
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
    $("#periodohoy").val(data.cotizacion.periodo);
    $("#folio").val(data.cotizacion.folio);
    $("#serie").val(data.cotizacion.serie);
    $("#serietexto").html("Serie: "+data.cotizacion.serie);
    $("#ottecnodiesel").val(data.cotizacion.ot_tecnodiesel);
    $("#ottyt").val(data.cotizacion.ot_tyt);
    $("#fecha").val(data.fecha).attr('min', data.fechasdisponiblesenmodificacion.fechamin).attr('max', data.fechasdisponiblesenmodificacion.fechamax);
    $("#remision").val(data.cotizacion.num_remision);
    $("#equipo").val(data.cotizacion.num_equipo);
    $("#requisicion").val(data.cotizacion.num_remision);
    $("#subtotal").val(data.subtotal);
    $("#iva").val(data.iva);
    $("#total").val(data.total);
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //detalles
    $("#tablaproductocotizacion tbody").html(data.filasdetallescotizacion);
    $("#numerofilas").val(data.numerodetallescotizacion);
    mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
    $('.page-loader-wrapper').css('display', 'none');
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
    if(numerofilas > 0){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:cotizaciones_guardar_modificacion,
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
                                          '<th>OrdenCompra</th>'+
                                          '<th>Proveedor</th>'+
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
  var checkboxscolumnas = '';
  var optionsselectbusquedas = '';
  var campos = campos_activados.split(",");
  for (var i = 0; i < campos.length; i++) {
    var returncheckboxfalse = '';
    if(campos[i] == 'id' || campos[i] == 'cotizacion' || campos[i] == 'status' || campos[i] == 'periodo'){
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