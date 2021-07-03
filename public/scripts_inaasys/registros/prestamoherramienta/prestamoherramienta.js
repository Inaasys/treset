'use strict'
var tabla;
var form;
var contadorproductos=0;
var contadorfilas = 0;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 1000));
}
function asignarfechaactual(){
  $.get(prestamo_herramienta_obtener_fecha_datetimelocal, function(data){
    /*var fechahoy = new Date();
    var dia = ("0" + fechahoy.getDate()).slice(-2);
    var mes = ("0" + (fechahoy.getMonth() + 1)).slice(-2);
    var hoy = fechahoy.getFullYear()+"-"+(mes)+"-"+(dia) ;*/
    $('#fecha').val(data);
    $("#inicioprestamo").val(data);
    $("#terminoprestamo").val(data);
  });
}
//comparar si la fecha de termino del prestamo es menor a la fecha de inicio
function compararterminoprestamo(){
  var inicioprestamo = $("#inicioprestamo").val();
  var terminoprestamo = $("#terminoprestamo").val();
  if(inicioprestamo > terminoprestamo){
    msjterminoprestamomenor();
    $("#terminoprestamo").val("");
  }
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  var serie = $("#serie").val();
  $.get(prestamo_herramienta_obtener_ultimo_id,{serie:serie}, function(folio){
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
  //Resetear las validaciones del formulario alta
  $("#formparsley").parsley().reset();
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
    var searchable = false;
    if(campos[i] == 'prestamo' || campos[i] == 'status' || campos[i] == 'nombre_recibe_herramienta' || campos[i] == 'nombre_entrega_herramienta'){
        searchable = true;
    }
      campos_tabla.push({ 
          'data'    : campos[i],
          'name'  : campos[i],
          'orderable': false,
          'searchable': searchable
      });
  }
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
        url: prestamo_herramienta_obtener,
        data: function (d) {
            d.periodo = $("#periodo").val();
        }
    },
    "createdRow": function( row, data, dataIndex){
        if( data.status ==  `BAJA`){$(row).addClass('bg-orange');}
        else if( data.status ==  `ENTREGADO`){$(row).addClass('bg-green');}
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
        url: prestamo_herramienta_obtener_series_documento
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
function seleccionarseriedocumento(serie){
  $.get(prestamo_herramienta_obtener_ultimo_folio_serie_seleccionada, {serie:serie}, function(folio){
      $("#folio").val(folio);
      $("#serie").val(serie);
      $("#serietexto").html("Serie: "+serie);
      mostrarformulario();
  }) 
}
//obtener registros
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
    $('#tbllistadopersonalrecibe').DataTable({
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
            url: prestamo_herramienta_obtener_personal_recibe,
            data: function (d) {
              d.personalherramientacomun = $("#personalherramientacomun").val();
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
//obtener por numero
function obtenerpersonalrecibepornumero(){
  var numeropersonalrecibeanterior = $("#numeropersonalrecibeanterior").val();
  var numeropersonalrecibe = $("#numeropersonalrecibe").val();
  if(numeropersonalrecibeanterior != numeropersonalrecibe){
    if($("#numeropersonalrecibe").parsley().isValid()){
      var personalherramientacomun = $("#personalherramientacomun").val();
      $.get(prestamo_herramienta_obtener_personal_recibe_por_numero, {numeropersonalrecibe:numeropersonalrecibe,personalherramientacomun:personalherramientacomun}, function(data){
        $("#numeropersonalrecibe").val(data.numero);
        $("#numeropersonalrecibeanterior").val(data.numero);
        $("#personalrecibe").val(data.nombre);
        if(data.nombre != null){
          $("#textonombrepersonalrecibe").html(data.nombre.substring(0, 40));
        }
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
      var totalpesospartida = $('.totalpartida', this).val(); 
      //total de la partida
      totalpesospartida =  new Decimal(cantidadpartida).times(preciopartida);
      $('.totalpartida', this).val(number_format(round(totalpesospartida, numerodecimales), numerodecimales, '.', ''));
      calculartotal();
    }  
    cuentaFilas++;
  });
} 
//calcular totales de orden de compra
function calculartotal(){
  var total = 0;
  $("tr.filasproductos").each(function(){
    total = new Decimal(total).plus($(".totalpartida", this).val());
  }); 
  $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
}
//obtener personal
function obtenerpersonal(){
  $.get(prestamo_herramienta_obtener_personal, function(data){
      $("#personalherramientacomun").empty();
      $("#personalherramientacomun").append("<option selected disabled hidden>Selecciona el personal</option>");
      $.each(data,function(key, registro) {
        $("#personalherramientacomun").append('<option value='+registro.id+'>'+registro.nombre+' - '+registro.tipo_personal+'</option>');
      });
  });
}
//cargar toda la herramienta asignada al personal
function herramientaasignadapersonal(){
  var personalherramientacomun = $("#personalherramientacomun").val();
  var numeropersonalrecibe = $("#numeropersonalrecibe").val();
  if(numeropersonalrecibe != personalherramientacomun){
    ocultarformulario();
    $("#tablaherramientasprestadas tbody").html("");
    contadorproductos=0;
    contadorfilas = 0;
    var tablaherramientasasignadaspersonalseleccionado =    '<div class="modal-header '+background_forms_and_modals+'">'+
                                                              '<h4 class="modal-title">Herramienta Asignada a Personal Seleccionado</h4>'+
                                                          '</div>'+
                                                          '<div class="modal-body">'+
                                                              '<div class="row">'+
                                                                  '<div class="col-md-12">'+
                                                                      '<div class="table-responsive ">'+
                                                                          '<table id="tablaherramientasasignadaspersonalseleccionado" class="table table-bordered tablaherramientasasignadaspersonalseleccionado" style="width:100% !important;">'+
                                                                              '<thead class="'+background_tables+'">'+
                                                                                  '<tr>'+
                                                                                      '<th>Operaciones</th>'+    
                                                                                      '<th>Asignación</th>'+
                                                                                      '<th>Herramienta</th>'+
                                                                                      '<th><div style="width:200px !important;">Descripción</div></th>'+
                                                                                      '<th >Unidad</th>'+
                                                                                      '<th >Cantidad</th>'+
                                                                                      '<th >Precio $</th>'+
                                                                                      '<th>Total $</th>'+
                                                                                  '</tr>'+
                                                                              '</thead>'+
                                                                              '<tbody>'+           
                                                                              '</tbody>'+
                                                                          '</table>'+
                                                                      '</div>'+
                                                                  '</div>'+   
                                                              '</div>'+
                                                          '</div>'+
                                                          '<div class="modal-footer">'+
                                                              '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                                                          '</div>';
    $("#contenidomodaltablas").html(tablaherramientasasignadaspersonalseleccionado);
    $('#tablaherramientasasignadaspersonalseleccionado').DataTable({
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
          url: prestamo_herramienta_obtener_herramienta_personal,
          data: function (d) {
            d.personalherramientacomun = $("#personalherramientacomun").val();
          }
        },
        columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'asignacion', name: 'asignacion' },
          { data: 'herramienta', name: 'herramienta' },
          { data: 'descripcion', name: 'descripcion', orderable: false, searchable: false },
          { data: 'unidad', name: 'unidad', orderable: false, searchable: false },
          { data: 'cantidad', name: 'cantidad', orderable: false, searchable: false },
          { data: 'precio', name: 'precio', orderable: false, searchable: false },
          { data: 'total', name: 'total', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tablaherramientasasignadaspersonalseleccionado').DataTable().search( this.value ).draw();
                }
            });
        },
        
    });
  }   
}
//evaluar nunmero de filas
function evaluarnumerofilas(){
  if(contadorfilas > 0){
    $("#btnobtenerpersonalrecibe").show();
  }else{
    $("#btnobtenerpersonalrecibe").hide();
    $("#numeropersonalrecibe").val("");
    $("#personalrecibe").val("");
  }
}
//agregar una fila en la tabla de prestamos
function seleccionarherramientaasignada(iddetalleasignacionherramienta){
  $.get(prestamo_herramienta_obtener_detalle_asignacion_seleccionada, {iddetalleasignacionherramienta:iddetalleasignacionherramienta}, function(data){
    var result = evaluarproductoexistente(data.herramienta);
    if(result == false){
      if(parseFloat(data.cantidad) > 0){
        var fila=   '<tr class="filasproductos" id="filaproducto'+contadorproductos+'">'+
                            '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosproductos('+contadorproductos+')">X</div></td>'+
                            '<td class="tdmod"><input type="hidden" class="form-control iddetalleasignacionherramienta" name="iddetalleasignacionherramienta[]" value="'+iddetalleasignacionherramienta+'" readonly><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'+data.herramienta+'" readonly>'+data.herramienta+'</td>'+
                            '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control descripcionpartida" name="descripcionpartida[]" value="'+data.descripcion+'" readonly>'+data.descripcion+'</div></td>'+
                            '<td class="tdmod"><input type="hidden" class="form-control unidadpartida" name="unidadpartida[]" value="'+data.unidad+'" readonly>'+data.unidad+'</td>'+
                            '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-min="0.1" data-parsley-max="'+data.cantidad+'" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('+contadorfilas+');" required></td>'+
                            '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'+data.precio+'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                            '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpartida" name="totalpartida[]" value="'+data.precio+'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                            '<td class="tdmod">'+
                              '<select name="estadopartida[]" class="form-control" style="width:100% !important;height: 28px !important;" required readonly>'+
                                  data.estado_herramienta+
                              '</select>'+
                            '</td>'+    
                    '</tr>';
        contadorproductos++;
        contadorfilas++;
        $("#tablaherramientasprestadas").append(fila);
        calculartotal();
        evaluarnumerofilas();
        msj_herramientagregadocorrectamente();
      }else{
        msj_errorherramientasinexistenciasparaprestamo();
      }
    }else{
      msj_errorherramientayaagregado();
    } 
  });
}
//eliminar una fila en la tabla de precios clientes
function eliminarfilapreciosproductos(numerofila){
  var confirmacion = confirm("Esta seguro de eliminar la herramienta?"); 
  if (confirmacion == true) { 
    $("#filaproducto"+numerofila).remove();
    contadorfilas--; //importante para todos los calculo en el modulo de orden de compra se debe restar al contadorfilas la fila que se acaba de eliminar
    renumerarfilasordencompra();//importante para todos los calculo en el modulo de orden de compra 
    calculartotal();
    evaluarnumerofilas();
  }
}
//renumerar las filas de la orden de compra
function renumerarfilasordencompra(){
  var lista;
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("cantidadpartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "calculartotalesfilasordencompra("+i+')');
  }
}  
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Prestamo Herramienta');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs ='<div class="col-md-12">'+
              '<div class="row">'+
                '<div class="col-md-3">'+
                  '<label>Asignación <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                  '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                  '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly>'+
                  '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion"  readonly>'+
                  '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                '</div>'+   
                '<div class="col-md-3">'+
                  '<label>Selecciona el personal que entrega:</label>'+
                  '<div class="col-md-12">'+
                    '<select name="personalherramientacomun" id="personalherramientacomun" class="form-control select2" onchange="herramientaasignadapersonal()" style="width:100% !important;" required>'+
                    '</select>'+
                    '<input type="hidden" class="form-control" name="numeropersonalentrega" id="numeropersonalentrega" required readonly onkeyup="tipoLetra(this)">'+
                    '<input type="hidden" class="form-control" name="personalentrega" id="personalentrega" required readonly>'+
                  '</div>'+
                '</div>'+  
                '<div class="col-md-3">'+
                  '<label>Personal que recibe <span class="label label-danger" id="textonombrepersonalrecibe"></span></label>'+
                  '<table class="col-md-12">'+
                    '<tr>'+
                      '<td>'+
                        '<div class="btn bg-blue waves-effect" onclick="obtenerpersonalrecibe()" id="btnobtenerpersonalrecibe">Seleccionar</div>'+
                      '</td>'+
                      '<td>'+
                        '<div class="form-line">'+
                          '<input type="text" class="form-control" name="numeropersonalrecibe" id="numeropersonalrecibe" required data-parsley-type="integer">'+
                          '<input type="hidden" class="form-control" name="numeropersonalrecibeanterior" id="numeropersonalrecibeanterior" required data-parsley-type="integer">'+
                          '<input type="hidden" class="form-control" name="personalrecibe" id="personalrecibe" required readonly>'+
                        '</div>'+
                      '</td>'+
                    '</tr>'+    
                  '</table>'+
                '</div>'+
                '<div class="col-md-3">'+
                  '<label>Fecha</label>'+
                  '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();" onkeyup="tipoLetra(this);">'+
                  '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                '</div>'+
              '</div>'+
              '<div class="row">'+
                '<div class="col-md-3">'+
                  '<label>Inicio Prestamo</label>'+
                  '<input type="datetime-local" class="form-control inicioprestamo" name="inicioprestamo" id="inicioprestamo" onchange="compararterminoprestamo()" required>'+
                '</div>'+
                '<div class="col-md-3">'+
                  '<label>Termino Prestamo</label>'+
                  '<input type="datetime-local" class="form-control terminoprestamo" name="terminoprestamo" id="terminoprestamo" onchange="compararterminoprestamo()" required >'+
                '</div>'+
                '<div class="col-md-4">'+
                  '<label >correo notificaciones</label>'+
                  '<input type="email" class="form-control" name="correo" id="correo" data-parsley-type="email" required>'+
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
                      '<table id="tablaherramientasprestadas" class="table table-bordered tablaherramientasprestadas">'+
                        '<thead class="'+background_tables+'">'+
                          '<tr>'+
                            '<th class="'+background_tables+'">#</th>'+
                            '<th class="'+background_tables+'">Herramienta</th>'+
                            '<th class="'+background_tables+'"><div style="width:200px !important;">Descripción</div></th>'+
                            '<th class="customercolortheadth">Unidad</th>'+
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
                      '<textarea class="form-control" name="observaciones" id="observaciones" rows="2" onkeyup="tipoLetra(this);" required></textarea>'+
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
  obtenultimonumero();
  asignarfechaactual();
  obtenerpersonal();
  //reiniciar los contadores
  contadorproductos=0;
  contadorfilas = 0;
  //activar select2
  $("#personalherramientacomun").select2();
  //asignar el tipo de operacion que se realizara
  $("#tipooperacion").val("alta");
  //busquedas seleccion
  //activar busqueda
  $("#codigoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      listarherramientas();
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
  //se debe motrar el input para buscar los productos
  $("#divbuscarcodigoproducto").show();
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
      url:prestamo_herramienta_guardar,
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
//desactivar prestamo
function desactivar(prestamodesactivar){
      $("#motivobaja").val("");
      $("#prestamodesactivar").val(prestamodesactivar);
      $("#textomodaldesactivar").html('Estas seguro de dar de baja el prestamo de herramienta? No'+prestamodesactivar);
      $("#divmotivobaja").show();
      $("#btnbaja").show();
      $('#estatusregistro').modal('show');
}
$("#btnbaja").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formdesactivar")[0]);
  var form = $("#formdesactivar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:prestamo_herramienta_alta_o_baja,
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
//terminar prestamo
function terminarprestamo(prestamo){
  $("#prestamoterminarprestamo").val(prestamo);
  $("#textomodalterminarprestamo").html('Estas seguro de terminar el prestamo de herramienta? No'+prestamo);
  $("#btnterminarprestamo").show();
  $('#modalterminarprestamo').modal('show');
}
$("#btnterminarprestamo").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formterminarprestamo")[0]);
  var form = $("#formterminarprestamo");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:prestamo_herramienta_terminar_prestamo,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#modalterminarprestamo').modal('hide');
        msj_datosguardadoscorrectamente();
        $('.page-loader-wrapper').css('display', 'none');
      },
      error:function(data){
        if(data.status == 403){
          msj_errorenpermisos();
        }else{
          msj_errorajax();
        }
        $('#modalterminarprestamo').modal('hide');
        $('.page-loader-wrapper').css('display', 'none');
      }
    })
  }else{
    form.parsley().validate();
  }
});
//obtener datos para modificacion
function obtenerdatos(prestamomodificar){
  $("#titulomodal").html('Modificación Prestamo Herramienta');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(prestamo_herramienta_obtener_prestamo_herramienta,{prestamomodificar:prestamomodificar },function(data){
    //formulario alta
    var tabs ='<div class="col-md-12">'+
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Asignación <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b></label>'+
                    '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                    '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly>'+
                    '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion"  readonly>'+
                    '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                  '</div>'+   
                  '<div class="col-md-3">'+
                    '<label>Selecciona el personal que entrega:</label>'+
                    '<div class="col-md-12">'+
                      '<select name="personalherramientacomun" id="personalherramientacomun" class="form-control select2" onchange="herramientaasignadapersonal()" style="width:100% !important;" required>'+
                      '</select>'+
                      '<input type="hidden" class="form-control" name="numeropersonalentrega" id="numeropersonalentrega" required readonly onkeyup="tipoLetra(this)">'+
                      '<input type="hidden" class="form-control" name="personalentrega" id="personalentrega" required readonly>'+
                    '</div>'+
                  '</div>'+  
                  '<div class="col-md-3">'+
                    '<label>Personal que recibe <span class="label label-danger" id="textonombrepersonalrecibe"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td hidden>'+
                          '<div class="btn bg-blue waves-effect" onclick="obtenerpersonalrecibe()" id="btnobtenerpersonalrecibe">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control" name="numeropersonalrecibe" id="numeropersonalrecibe" required required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="numeropersonalrecibeanterior" id="numeropersonalrecibeanterior" required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="personalrecibe" id="personalrecibe" required readonly>'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+    
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>Fecha</label>'+
                    '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();" onkeyup="tipoLetra(this);">'+
                    '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                  '</div>'+
                '</div>'+
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Inicio Prestamo</label>'+
                    '<input type="datetime-local" class="form-control inicioprestamo" name="inicioprestamo" id="inicioprestamo" onchange="compararterminoprestamo()" required>'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>Termino Prestamo</label>'+
                    '<input type="datetime-local" class="form-control terminoprestamo" name="terminoprestamo" id="terminoprestamo" onchange="compararterminoprestamo()" required >'+
                  '</div>'+
                  '<div class="col-md-4">'+
                    '<label >correo notificaciones</label>'+
                    '<input type="email" class="form-control" name="correo" id="correo" data-parsley-type="email" required>'+
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
                        '<table id="tablaherramientasprestadas" class="table table-bordered tablaherramientasprestadas">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="'+background_tables+'">Herramienta</th>'+
                              '<th class="'+background_tables+'"><div style="width:200px !important;">Descripción</div></th>'+
                              '<th class="customercolortheadth">Unidad</th>'+
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
                        '<textarea class="form-control" name="observaciones" id="observaciones" rows="2" onkeyup="tipoLetra(this);" required></textarea>'+
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
    $("#periodohoy").val(data.Prestamo_Herramienta.periodo);
    $("#folio").val(data.Prestamo_Herramienta.folio);
    $("#serie").val(data.Prestamo_Herramienta.serie);
    $("#serietexto").html("Serie: "+data.Prestamo_Herramienta.serie);
    $("#numeropersonalrecibe").val(data.personalrecibe.id);
    $("#numeropersonalrecibeanterior").val(data.personalrecibe.id);
    $("#personalrecibe").val(data.personalrecibe.nombre);
    if(data.personalrecibe.nombre != null){
      $("#textonombrepersonalrecibe").html(data.personalrecibe.nombre.substring(0, 40));
    }
    $("#fecha").val(data.fecha);
    $("#fecha").attr('readonly', 'readonly');
    $("#inicioprestamo").val(data.Prestamo_Herramienta.inicio_prestamo);
    $("#inicioprestamo").attr('readonly', 'readonly');
    $("#terminoprestamo").val(data.Prestamo_Herramienta.termino_prestamo);
    $("#correo").val(data.Prestamo_Herramienta.correo);
    $("#observaciones").val(data.Prestamo_Herramienta.observaciones);
    $("#total").val(data.total);
    //tabs herramientas
    $("#tablaherramientasprestadas").append(data.filasdetallesprestamo);
    //se deben asignar los valores a los contadores para que las sumas resulten correctas
    contadorproductos = data.contadorproductos;
    contadorfilas = data.contadorfilas;
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //busquedas seleccion
    //activar busqueda
    $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        listarherramientas();
      }
    });
    //regresar numero
    $('#numeropersonalrecibe').on('change', function(e) {
      regresarnumeropersonalrecibe();
    });
    //se debe esconder el input para buscar los productos porque en la modificacion no se permiten agregar productos
    $("#divbuscarcodigoproducto").hide();
    seleccionarpersonalentrega(data);
  }).fail( function() {
    msj_errorajax();
    $('.page-loader-wrapper').css('display', 'none');
  })
}
async function seleccionarpersonalentrega(data){
  await retraso();
  $("#personalherramientacomun").html(data.selectpersonal);
  $("#personalherramientacomun").attr('disabled', 'disabled');
  //activar select2
  $("#personalherramientacomun").select2();
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
      url:prestamo_herramienta_guardar_modificacion,
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
                                  '<label>DATOS ASIGNACIÓN HERRAMIENTA</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="id" id="idid" class="filled-in datotabla" value="id" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idid">id</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="prestamo" id="idprestamo" class="filled-in datotabla" value="prestamo" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idprestamo">prestamo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="serie" id="idserie" class="filled-in datotabla" value="serie" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idserie">serie</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="fecha" id="idfecha" class="filled-in datotabla" value="fecha" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idfecha">fecha</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="recibe_herramienta" id="idrecibe_herramienta" class="filled-in datotabla" value="recibe_herramienta" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idrecibe_herramienta">recibe_herramienta</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="entrega_herramienta" id="identrega_herramienta" class="filled-in datotabla" value="entrega_herramienta" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="identrega_herramienta">entrega_herramienta</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="total" id="idtotal" class="filled-in datotabla" value="total" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idtotal">total</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="observaciones" id="idobservaciones" class="filled-in datotabla" value="observaciones" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idobservaciones">observaciones</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="correo" id="idcorreo" class="filled-in datotabla" value="correo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idcorreo">correo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+    
                                  '<input type="checkbox" name="correo_enviado" id="idcorreo_enviado" class="filled-in datotabla" value="correo_enviado" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idcorreo_enviado">correo_enviado</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="status" id="idstatus" class="filled-in datotabla" value="status" onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idstatus">status</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                '<input type="checkbox" name="inicio_prestamo" id="idinicio_prestamo" class="filled-in datotabla" value="inicio_prestamo" onchange="construirarraydatostabla(this);"/>'+
                                '<label for="idinicio_prestamo">inicio_prestamo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="termino_prestamo" id="idtermino_prestamo" class="filled-in datotabla" value="termino_prestamo" onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idtermino_prestamo">termino_prestamo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="motivo_baja" id="idmotivo_baja" class="filled-in datotabla" value="motivo_baja" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idmotivo_baja">motivo_baja</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="equipo" id="idequipo" class="filled-in datotabla" value="equipo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idequipo">equipo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="usuario" id="idusuario" class="filled-in datotabla" value="usuario" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idusuario">usuario</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="periodo" id="idperiodo" class="filled-in datotabla" value="periodo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idperiodo">periodo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="folio" id="idfolio" class="filled-in datotabla" value="folio" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idfolio">folio</label>'+
                              '</div>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                          '</div>'+
                          '<div class="col-md-6">'+
                              '<div class="col-md-12 form-check">'+
                                  '<label>DATOS PERSONAL RECIBE</label>'+
                              '</div>'+
                              '<div class="col-md-6 form-check">'+
                                  '<input type="checkbox" name="nombre_recibe_herramienta" id="idnombre_recibe_herramienta" class="filled-in datotabla" value="nombre_recibe_herramienta"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idnombre_recibe_herramienta">nombre_recibe_herramienta</label>'+  
                              '</div>'+
                              '<div class="col-md-6 form-check">'+  
                                  '<input type="checkbox" name="tipo_recibe_herramienta" id="idtipo_recibe_herramienta" class="filled-in datotabla" value="tipo_recibe_herramienta"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idtipo_recibe_herramienta">tipo_recibe_herramienta</label>'+ 
                              '</div>'+
                              '<div class="col-md-12 form-check">'+
                                  '<label>DATOS PERSONAL ENTREGA</label>'+
                              '</div>'+
                              '<div class="col-md-6 form-check">'+
                                  '<input type="checkbox" name="nombre_entrega_herramienta" id="idnombre_entrega_herramienta" class="filled-in datotabla" value="nombre_entrega_herramienta"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idnombre_entrega_herramienta">nombre_entrega_herramienta</label>'+ 
                              '</div>'+
                              '<div class="col-md-6 form-check">'+
                                  '<input type="checkbox" name="tipo_entrega_herramienta" id="idtipo_entrega_herramienta" class="filled-in datotabla" value="tipo_entrega_herramienta"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idtipo_entrega_herramienta">tipo_entrega_herramienta</label>'+ 
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