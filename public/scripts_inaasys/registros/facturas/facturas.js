'use strict'
var tabla;
var form;
var contadorfilasfacturas = 0;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 1500));
}
function asignarfechaactual(){
    var fechahoy = new Date();
    var dia = ("0" + fechahoy.getDate()).slice(-2);
    var mes = ("0" + (fechahoy.getMonth() + 1)).slice(-2);
    var hoy = fechahoy.getFullYear()+"-"+(mes)+"-"+(dia) ;
    $('#fecha').val(hoy);
    $('input[type=datetime-local]').val(new Date().toJSON().slice(0,19));
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  var serie = $("#serie").val();
  $.get(facturas_obtener_ultimo_folio, {serie:serie}, function(folio){
    $("#folio").val(folio);
  })  
}
//obtener tipos ordenes de compra
function obtenertiposordenescompra(){
  $.get(facturas_obtener_tipos, function(select_tipos){
    $("#tipo").html(select_tipos);
  })  
}
//obtener tipos de unidades
function obtenertiposunidades(){
  $.get(facturas_obtener_tipos_unidades, function(select_tipos_unidades){
    $("#tipounidad").html(select_tipos_unidades);
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
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
    cambiarurlexportarexcel();
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
    "bScrollCollapse": true,  
    processing: true,
    'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
    },
    serverSide: true,
    ajax: {
        url: facturas_obtener,
        data: function (d) {
            d.periodo = $("#periodo").val();
        }
    },
    "createdRow": function( row, data, dataIndex){
        if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
        else if( data.Status ==  `POR COBRAR`){ $(row).addClass('bg-red');}
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
//obtener registros de proveedores
function obtenerclientes(){
  ocultarformulario();
  var tablaclientes = '<div class="modal-header bg-red">'+
                      '<h4 class="modal-title">Clientes</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                  '<table id="tbllistadocliente" class="tbllistadocliente table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                      '<thead class="customercolor">'+
                                          '<tr>'+
                                              '<th>Operaciones</th>'+
                                              '<th>Numero</th>'+
                                              '<th>Nombre</th>'+
                                              '<th>R.F.C.</th>'+
                                              '<th>Municipio</th>'+
                                              '<th>Agente</th>'+
                                              '<th>Tipo</th>'+
                                              '<th>Saldo $</th>'+
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
    $("#contenidomodaltablas").html(tablaclientes);
    $('#tbllistadocliente').DataTable({
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
            url: facturas_obtener_clientes,
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
                $('#tbllistadocliente').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    }); 
} 
//seleccionar cliente
function seleccionarcliente(Numero, Nombre, Plazo, Rfc, claveformapago, formapago, clavemetodopago, metodopago, claveusocfdi, usocfdi, claveresidenciafiscal, residenciafiscal,NumeroAgente,CreditoCliente,SaldoCliente){
  var numerofilas = $("#numerofilas").val()
  if(parseInt(numerofilas) > 0){
    var confirmacion = confirm("Esta seguro de cambiar el cliente, esto eliminara las partidas agregadas (Remisiones ó Servicios)?"); 
  }else{
    var confirmacion = true;
  }
  if (confirmacion == true) { 
    $("#tabladetallesfactura tbody").html("");
    $("#numerocliente").val(Numero);
    $("#cliente").val(Nombre);
    $("#rfccliente").val(Rfc);
    $("#plazo").val(Plazo);
    //credito y saldo
    $("#credito").val(number_format(round(CreditoCliente, numerodecimales), numerodecimales, '.', ''));
    $("#saldo").val(number_format(round(SaldoCliente, numerodecimales), numerodecimales, '.', ''));
    //datos pestaña receptor o cliente
    $("#receptorrfc").val(Rfc);
    $("#receptornombre").val(Nombre);
    $("#claveformapago").val(claveformapago);
    $("#formapago").val(formapago);
    $("#clavemetodopago").val(clavemetodopago);
    $("#metodopago").val(metodopago);
    $("#claveusocfdi").val(claveusocfdi);
    $("#usocfdi").val(usocfdi);
    $("#claveresidenciafiscal").val(claveresidenciafiscal);
    $("#residenciafiscal").val(residenciafiscal);
    //datos agente
    $.get(facturas_obtener_datos_agente, {NumeroAgente:NumeroAgente}, function(Agente){
      $("#numeroagente").val(Agente.Numero);
      $("#rfcagente").val(Agente.Rfc);
      $("#agente").val(Agente.Nombre);
    }) 
    //comprobar si mostrar botones
    var Depto = $("#depto").val();
    comprobartiposerie(Depto);
    //comprobar numero de filas en la tabla
    comprobarfilas();
    //calcular totales compras nota proveedor
    calculartotal();
    //colocar strings vacios
    $("#stringremisionesseleccionadas").val("");
    $("#stringordenesseleccionadas").val("");
    contadorproductos = 0;
    contadorfilas = 0;
    partida = 1;
    mostrarformulario();
  }
}
//obtener registros de almacenes
function obteneragentes(){
    ocultarformulario();
    var tablaagentes= '<div class="modal-header bg-red">'+
                            '<h4 class="modal-title">Agentes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoagente" class="tbllistadoagente table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="customercolor">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Nombre</th>'+
                                                    '<th>Rfc</th>'+
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
      $('#tbllistadoagente').DataTable({
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
              url: facturas_obtener_agentes,
          },
          columns: [
              { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
              { data: 'Numero', name: 'Numero' },
              { data: 'Nombre', name: 'Nombre' },
              { data: 'Rfc', name: 'Rfc' }
          ],
          "initComplete": function() {
              var $buscar = $('div.dataTables_filter input');
              $buscar.unbind();
              $buscar.bind('keyup change', function(e) {
                  if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoagente').DataTable().search( this.value ).draw();
                  }
              });
          },
          "iDisplayLength": 8,
      }); 
} 
//seleccionar almacen
function seleccionaragente(Numero, Nombre, Rfc){
    $("#numeroagente").val(Numero);
    $("#rfcagente").val(Rfc);
    $("#agente").val(Nombre);
    mostrarformulario();
}
//obtener lugares expedicion
function obtenerlugaresexpedicion(){
  ocultarformulario();
  var tablacodigospostales =  '<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Códigos Postales</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadocodigopostal" class="tbllistadocodigopostal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="customercolor">'+
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
  $('#tbllistadocodigopostal').DataTable({
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
        url: facturas_obtener_codigos_postales,
        data: function (d) {
            //d.numeroestado = $("#estado").val();
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
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadocodigopostal').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  });
} 
//seleccionar lugar expedicion
function seleccionarlugarexpedicion(Clave){
$("#lugarexpedicion").val(Clave);
mostrarformulario();
}
//obtener regimenes fiscales
function obtenerregimenesfiscales(){
  ocultarformulario();
  var tablaregimenesfiscales ='<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Regimenes Fiscales</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoregimenfiscal" class="tbllistadoregimenfiscal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="customercolor">'+
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
  $("#contenidomodaltablas").html(tablaregimenesfiscales);
  $('#tbllistadoregimenfiscal').DataTable({
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
        url: facturas_obtener_regimenes_fiscales
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
          { data: 'Fisica', name: 'Fisica', orderable: false, searchable: false},
          { data: 'Moral', name: 'Moral', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoregimenfiscal').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  });
} 
//seleccionar lugar expedicion
function seleccionarregimenfiscal(Clave, Nombre){
  $("#claveregimenfiscal").val(Clave);
  $("#regimenfiscal").val(Nombre);
  mostrarformulario();
}
//obtener tipos relacion
function obtenertiposrelaciones(){
  ocultarformulario();
  var tablatiposrelaciones ='<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Tipos Relación</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadotiporelacion" class="tbllistadotiporelacion table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="customercolor">'+
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
  $("#contenidomodaltablas").html(tablatiposrelaciones);
  $('#tbllistadotiporelacion').DataTable({
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
        url: facturas_obtener_tipos_relacion
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadotiporelacion').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  });
} 
//seleccionar lugar expedicion
function seleccionartiporelacion(Clave, Nombre){
  $("#clavetiporelacion").val(Clave);
  $("#tiporelacion").val(Nombre);
  mostrarformulario();
}
//obtener formas de pago
function obtenerformaspago(){
  ocultarformulario();
  var tablaformaspago ='<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Formas Pago</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoformapago" class="tbllistadoformapago table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="customercolor">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>Descripción</th>'+
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
  $('#tbllistadoformapago').DataTable({
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
        url: facturas_obtener_formas_pago
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
          { data: 'Descripcion', name: 'Descripcion', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoformapago').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  });
} 
//seleccionar forma pago
function seleccionarformapago(Clave, Nombre){
  $("#claveformapago").val(Clave);
  $("#formapago").val(Nombre);
  mostrarformulario();
}
//obtener metodos de pago
function obtenermetodospago(){
  ocultarformulario();
  var tablametodospago='<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Métodos Pago</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadometodopago" class="tbllistadometodopago table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="customercolor">'+
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
  $("#contenidomodaltablas").html(tablametodospago);
  $('#tbllistadometodopago').DataTable({
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
        url: facturas_obtener_metodos_pago
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadometodopago').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  });
} 
//seleccionar metodo pago
function seleccionarmetodopago(Clave, Nombre){
  $("#clavemetodopago").val(Clave);
  $("#metodopago").val(Nombre);
  mostrarformulario();
}
//obtener usos cfdi
function obtenerusoscfdi(){
  ocultarformulario();
  var tablausoscfdi='<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Usos CFDI</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadousocfdi" class="tbllistadousocfdi table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="customercolor">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>Fisica</th>'+
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
  $("#contenidomodaltablas").html(tablausoscfdi);
  $('#tbllistadousocfdi').DataTable({
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
        url: facturas_obtener_usos_cfdi
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
          { data: 'Fisica', name: 'Fisica', orderable: false, searchable: false},
          { data: 'Moral', name: 'Moral', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadousocfdi').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  });
} 
//seleccionar uso cfdi
function seleccionarusocfdi(Clave, Nombre){
  $("#claveusocfdi").val(Clave);
  $("#usocfdi").val(Nombre);
  mostrarformulario();
}
//obtener residencias fiscales
function obtenerresidenciasfiscales(){
  ocultarformulario();
  var tablaresidenciasfiscales='<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Residencias Fiscales</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoresidencialfiscal" class="tbllistadoresidencialfiscal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="customercolor">'+
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
  $("#contenidomodaltablas").html(tablaresidenciasfiscales);
  $('#tbllistadoresidencialfiscal').DataTable({
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
        url: facturas_obtener_residencias_fiscales
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoresidencialfiscal').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  });
} 
//seleccionar residencia fiscal
function seleccionarresidenciafiscal(Clave, Nombre){
  $("#claveresidenciafiscal").val(Clave);
  $("#residenciafiscal").val(Nombre);
  mostrarformulario();
}
//obtener series facturas
function obtenerfoliosfacturas(){
  ocultarformulario();
  var tablafoliosfiscales='<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Folios Fiscales</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadofoliofiscal" class="tbllistadofoliofiscal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="customercolor">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Serie</th>'+
                                                        '<th>Esquema</th>'+
                                                        '<th>Depto</th>'+
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
  $("#contenidomodaltablas").html(tablafoliosfiscales);
  $('#tbllistadofoliofiscal').DataTable({
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
        url: facturas_obtener_folios_fiscales
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Serie', name: 'Serie' },
          { data: 'Esquema', name: 'Esquema', orderable: false, searchable: false},
          { data: 'Depto', name: 'Depto', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadofoliofiscal').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  });  
}
function seleccionarfoliofiscal(Serie, Esquema, Depto){
  var numerofilas = $("#numerofilas").val()
  if(parseInt(numerofilas) > 0){
    var confirmacion = confirm("Esta seguro de cambiar el folio fiscal, esto eliminara las partidas agregadas (Remisiones ó Servicios)?"); 
  }else{
    var confirmacion = true;
  }
  if (confirmacion == true) { 
    $.get(facturas_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie,Esquema:Esquema,Depto:Depto}, function(folio){
      $("#tabladetallesfactura tbody").html("");
      $("#folio").val(folio);
      $("#serie").val(Serie);
      $("#esquema").val(Esquema);
      $("#depto").val(Depto);
      $("#serietexto").html("Serie: "+Serie);
      $("#esquematexto").html("Esquema: "+Esquema);
      comprobartiposerie(Depto);
      //comprobar numero de filas en la tabla
      comprobarfilas();
      //calcular totales compras nota proveedor
      calculartotal();
      //colocar strings vacios
      $("#stringremisionesseleccionadas").val("");
      $("#stringordenesseleccionadas").val("");
      contadorproductos = 0;
      contadorfilas = 0;
      partida = 1;
      mostrarformulario();
    }) 
  }
}
//comprobar el tipo de serie para la factura
function comprobartiposerie(Depto){
  var cliente = $("#cliente").val();
  if(cliente != ""){
    if(Depto == 'PRODUCTOS'){
      $("#divlistarremisiones").show();
      $("#divlistarservicios").hide();
      $("#divbuscarcodigos").hide();
    }else if(Depto == 'SERVICIO'){
      $("#divlistarremisiones").hide();
      $("#divlistarservicios").show();
      $("#divbuscarcodigos").hide();
    }else{
      $("#divlistarremisiones").hide();
      $("#divlistarservicios").hide();
      $("#divbuscarcodigos").show();
    }
  }
}
//listar todas las facturas
function listarremisiones(){
  ocultarformulario();
  var tablaremisiones ='<div class="modal-header bg-red">'+
                          '<h4 class="modal-title">Facturar Remisiones</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                        '<div class="row">'+
                          '<div class="col-md-12">'+
                            '<div class="table-responsive">'+
                              '<table id="tbllistadoremision" class="tbllistadoremision table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                '<thead class="customercolor">'+
                                  '<tr>'+
                                    '<th>Remisión</th>'+
                                    '<th>Fecha</th>'+
                                    '<th>Cliente</th>'+
                                    '<th>Nombre</th>'+
                                    '<th>Facturar $</th>'+
                                    '<th>Plazo</th>'+
                                    '<th>Pedido</th>'+
                                    '<th>Selecciona</th>'+
                                  '</tr>'+
                                '</thead>'+
                                '<tbody></tbody>'+
                              '</table>'+
                            '</div>'+
                          '</div>'+   
                        '</div>'+
                        '<div class="row">'+
                          '<div class="col-md-6 col-md-offset-4">'+
                          '</div>'+ 
                          '<div class="col-md-2">'+
                            '<label>Total $</label>'+
                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalafacturar" id="totalafacturar" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                          '</div>'+   
                        '</div>'+
                      '</div>'+
                      '<div class="modal-footer">'+
                        '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                      '</div>';
    $("#contenidomodaltablas").html(tablaremisiones);
    $('#tbllistadoremision').DataTable({
        "searching": false,
        "paging":   false,
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
          url: facturas_obtener_remisiones,
          data: function (d) {
              d.numerocliente = $("#numerocliente").val();
              d.stringremisionesseleccionadas = $("#stringremisionesseleccionadas").val();
          }
        },
        columns: [
            { data: 'Remision', name: 'Remision' },
            { data: 'Fecha', name: 'Fecha' },
            { data: 'Cliente', name: 'Cliente', orderable: false, searchable: false },
            { data: 'NombreCliente', name: 'NombreCliente', orderable: false, searchable: false },
            { data: 'Facturar', name: 'Facturar', orderable: false, searchable: false },
            { data: 'Plazo', name: 'Plazo', orderable: false, searchable: false },
            { data: 'Pedido', name: 'Pedido', orderable: false, searchable: false },
            { data: 'Selecciona', name: 'Selecciona', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoremision').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 1000,
        "order": [[ 0, "desc" ]]
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
  $("#stringremisionesseleccionadas").val(arrayremisionesseleccionadas);
}
//obtener todos los datos de la orden de compra seleccionada
function seleccionarremision(Remision){
    if( $('#idremisionesseleccionadas'+Remision).prop('checked') ) {
      var tipooperacion = $("#tipooperacion").val();
      $.get(facturas_obtener_remision, {Remision:Remision, contadorfilas:contadorfilas, partida:partida, tipooperacion:tipooperacion}, function(data){
        $("#tabladetallesfactura tbody").append(data.filasremisiones);
        //array de remisiones seleccionadas
        construirarrayremisionesseleccionadas();
        //comprobar numero de filas en la tabla
        comprobarfilas();
        //calcular totales compras nota proveedor
        calculartotal();
        contadorfilas = data.contadorfilas;
        partida = data.partida;
        remisionagregadacorrectamente();
      })
    }else{
      var confirmacion = confirm("Esta seguro de eliminar las partidas de la remisión?"); 
      if (confirmacion == true) { 
        eliminarfilasremisiondeseleccionada(Remision);
      }
    }
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
//listar todas las facturas
function listarordenes(){
  ocultarformulario();
  var tablaordenes ='<div class="modal-header bg-red">'+
                          '<h4 class="modal-title">Facturar Servicios</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                        '<div class="row">'+
                          '<div class="col-md-12">'+
                            '<div class="table-responsive">'+
                              '<table id="tbllistadoorden" class="tbllistadoorden table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                '<thead class="customercolor">'+
                                  '<tr>'+
                                    '<th>Orden</th>'+
                                    '<th>Fecha</th>'+
                                    '<th>Cliente</th>'+
                                    '<th>Nombre</th>'+
                                    '<th>Por Facturar $</th>'+
                                    '<th>Total Orden</th>'+
                                    '<th>Tipo</th>'+
                                    '<th>Selecciona</th>'+
                                  '</tr>'+
                                '</thead>'+
                                '<tbody></tbody>'+
                              '</table>'+
                            '</div>'+
                          '</div>'+   
                        '</div>'+
                        '<div class="row">'+
                          '<div class="col-md-6 col-md-offset-4">'+
                          '</div>'+ 
                          '<div class="col-md-2">'+
                            '<label>Total $</label>'+
                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalafacturar" id="totalafacturar" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                          '</div>'+   
                        '</div>'+
                      '</div>'+
                      '<div class="modal-footer">'+
                        '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                      '</div>';
    $("#contenidomodaltablas").html(tablaordenes);
    $('#tbllistadoorden').DataTable({
        "searching": false,
        "paging":   false,
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
          url: facturas_obtener_ordenes,
          data: function (d) {
              d.numerocliente = $("#numerocliente").val();
              d.stringordenesseleccionadas = $("#stringordenesseleccionadas").val();
          }
        },
        columns: [
            { data: 'Orden', name: 'Orden' },
            { data: 'Fecha', name: 'Fecha' },
            { data: 'Cliente', name: 'Cliente', orderable: false, searchable: false },
            { data: 'NombreCliente', name: 'NombreCliente', orderable: false, searchable: false },
            { data: 'Facturar', name: 'Facturar', orderable: false, searchable: false },
            { data: 'Total', name: 'Total', orderable: false, searchable: false },
            { data: 'Tipo', name: 'Tipo', orderable: false, searchable: false },
            { data: 'Selecciona', name: 'Selecciona', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoorden').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 1000,
        "order": [[ 0, "desc" ]]
    });
} 
function construirarrayordenesseleccionadas(){
  var arrayordenesseleccionadas = [];
  var lista = document.getElementsByClassName("ordenesseleccionadas");
  for (var i = 0; i < lista.length; i++) {
    if(lista[i].checked){
      arrayordenesseleccionadas.push(lista[i].value);
    }
  }
  $("#stringordenesseleccionadas").val(arrayordenesseleccionadas);
}
//obtener todos los datos de la orden de compra seleccionada
function seleccionarorden(Orden){
    if( $('#idordenesseleccionadas'+Orden).prop('checked') ) {
      var tipooperacion = $("#tipooperacion").val();
      $.get(facturas_obtener_orden, {Orden:Orden, contadorfilas:contadorfilas, partida:partida, tipooperacion:tipooperacion}, function(data){
        $("#tabladetallesfactura tbody").append(data.filasordenes);
        //array de ordenes seleccionadas
        construirarrayordenesseleccionadas();
        //comprobar numero de filas en la tabla
        comprobarfilas();
        //calcular totales compras nota proveedor
        calculartotal();
        contadorfilas = data.contadorfilas;
        partida = data.partida;
        ordenagregadacorrectamente();
      })
    }else{
      var confirmacion = confirm("Esta seguro de eliminar las partidas de la orden?"); 
      if (confirmacion == true) { 
        eliminarfilasordendeseleccionada(Orden);
      }
    }
}
//eliminar todas las filas de la orden que se deselecciono
function eliminarfilasordendeseleccionada(Orden){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
    // obtener los datos de la fila
    var ordenpartida = $(".ordenpartida", this).val();
    if(ordenpartida == Orden){
      eliminarfila(cuentaFilas).then(fila=>{  
      })      
    }
    cuentaFilas++;
  });  
  msjsuccesseliminacionpartidasorden();
}
async function msjsuccesseliminacionpartidasorden(){
  await retraso();
  ordeneliminadacorrectamente();
  construirarrayordenesseleccionadas();
  renumerarfilas();//importante para todos los calculo en el modulo
  comprobarfilas();
  calculartotal();
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
//listar productos para tab consumos
function listarproductos(){
  ocultarformulario();
  var tablaproductos ='<div class="modal-header bg-red">'+
                        '<h4 class="modal-title">Productos</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                        '<div class="row">'+
                          '<div class="col-md-12">'+
                            '<div class="table-responsive">'+
                              '<table id="tbllistadoproducto" class="tbllistadoproducto table table-bordered table-striped table-hover" style="width:100% !important">'+
                                '<thead class="customercolor">'+
                                  '<tr>'+
                                    '<th>Operaciones</th>'+
                                    '<th>Código</th>'+
                                    '<th>Marca</th>'+
                                    '<th>Producto</th>'+
                                    '<th>Ubicación</th>'+
                                    '<th>Existencias Totales</th>'+
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
      url: facturas_obtener_productos,
      data: function (d) {
        d.codigoabuscar = $("#codigoabuscar").val();
        d.tipooperacion = $("#tipooperacion").val();
      }
    },
    columns: [
      { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false  },
      { data: 'Codigo', name: 'Codigo' },
      { data: 'Marca', name: 'Marca', orderable: false, searchable: false  },
      { data: 'Producto', name: 'Producto', orderable: false, searchable: false  },
      { data: 'Ubicacion', name: 'Ubicacion', orderable: false, searchable: false  },
      { data: 'Existencias', name: 'Existencias', orderable: false, searchable: false  },
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
    "iDisplayLength": 8,
  });
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
//agregar una fila en la tabla de precios productos codigo ó dppp
var contadorproductos=0;
var contadorfilas = 0;
var partida = 1;
function agregarfilaproducto(Codigo, Producto, Unidad, Costo, Impuesto, SubTotal, tipooperacion, Insumo, ClaveProducto, ClaveUnidad, NombreClaveProducto, NombreClaveUnidad, CostoDeLista){
    $('.page-loader-wrapper').css('display', 'block');
    var result = evaluarproductoexistente(Codigo);
    if(result == false){
        var multiplicacioncostoimpuesto =  new Decimal(SubTotal).times(Impuesto);      
        var ivapesos = new Decimal(multiplicacioncostoimpuesto/100);
        var total = new Decimal(SubTotal).plus(ivapesos);
        var preciopartida = SubTotal;
        var utilidad = new Decimal(SubTotal).minus(Costo);
        var tipo = "alta";
        var fila= '<tr class="filasproductos" id="filaproducto'+contadorfilas+'">'+
                    '<td class="tdmod"><div class="numeropartida">'+partida+'</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                    '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]">'+Codigo+'</td>'+
                    '<td class="tdmod"><input type="hidden" class="form-control descripcionpartida" name="descripcionpartida[]" value="'+Producto+'" readonly data-parsley-length="[1, 255]">'+Producto+'</td>'+
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="'+Unidad+'" readonly data-parsley-length="[1, 5]">'+Unidad+'</td>'+
                    '<td class="tdmod">'+
                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly>'+
                        '<input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="1.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'+
                        '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'+
                        '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'+
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'+
                    '</td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'+preciopartida+'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'+Impuesto+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'+number_format(round(ivapesos, numerodecimales), numerodecimales, '.', '')+'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'+number_format(round(total, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'+number_format(round(utilidad, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm remisionpartida" name="remisionpartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cartaportepartida" name="cartaportepartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm departamentopartida" name="departamentopartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cargopartida" name="cargopartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partida" name="partida[]" value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm tiendapartida" name="tiendapartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm pedidopartida" name="pedidopartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm almacenpartida" name="almacenpartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm datosunidadpartida" name="datosunidadpartida[]"  value="" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm utilidadaritmeticapartida" name="utilidadaritmeticapartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm utilidadfinancieriapartida" name="utilidadfinancieriapartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'+CostoDeLista+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'+ClaveProducto+'" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'+NombreClaveProducto+'" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'+ClaveUnidad+'" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'+NombreClaveUnidad+'" readonly></td>'+
                  '</tr>';
        contadorproductos++;
        contadorfilas++;
        $("#tabladetallesfactura").append(fila);
        mostrarformulario();      
        comprobarfilas();
        calculartotal();
        $('.page-loader-wrapper').css('display', 'none');
    }else{
        msj_errorproductoyaagregado();
        $('.page-loader-wrapper').css('display', 'none');
    }
}
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Factura');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs =        '<div class="row">'+
                      '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#facturatab" data-toggle="tab">Factura</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#emisortab" data-toggle="tab">Emisor</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#receptortab" data-toggle="tab">Receptor ó Cliente</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#otrostab" data-toggle="tab">Otros</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                          '<div role="tabpanel" class="tab-pane fade in active" id="facturatab">'+
                            '<div class="row">'+
                              '<div class="col-md-3">'+
                                '<label>Factura <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b> &nbsp;&nbsp;<b style="color:#F44336 !important;" id="esquematexto"> Esquema: '+esquema+'</b>  <div class="btn btn-xs bg-red waves-effect" id="btnobtenerfoliosfacturas" onclick="obtenerfoliosfacturas()">Cambiar</div></label>'+
                                '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                '<input type="hidden" class="form-control" name="stringremisionesseleccionadas" id="stringremisionesseleccionadas" readonly required>'+
                                '<input type="hidden" class="form-control" name="stringordenesseleccionadas" id="stringordenesseleccionadas" readonly required>'+
                                '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                                '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                                '<input type="hidden" class="form-control" name="esquema" id="esquema" value="'+esquema+'" readonly data-parsley-length="[1, 10]">'+
                                '<input type="hidden" class="form-control" name="depto" id="depto" value="'+depto+'" readonly data-parsley-length="[1, 20]">'+
                              '</div>'+  
                              '<div class="col-md-3">'+
                                '<label>Cliente</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" id="btnobtenerclientes" onclick="obtenerclientes()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="numerocliente" id="numerocliente" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="text" class="form-control" name="cliente" id="cliente" required readonly>'+
                                        '<input type="hidden" class="form-control" name="rfccliente" id="rfccliente" required readonly>'+
                                      '</div>'+
                                    '</td>'+    
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+ 
                              '<div class="col-md-3">'+
                                '<label>Agente</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" id="btnobteneragentes" onclick="obteneragentes()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="numeroagente" id="numeroagente" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="hidden" class="form-control" name="rfcagente" id="rfcagente" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="text" class="form-control" name="agente" id="agente" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-1">'+
                                '<label>Plazo días</label>'+
                                '<input type="text" class="form-control" name="plazo" id="plazo" value="5" required >'+
                              '</div>'+  
                              '<div class="col-md-2">'+
                                '<label>Fecha</label>'+
                                '<input type="date" class="form-control" name="fecha" id="fecha" required onchange="validasolomesactual();">'+
                                '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                                '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                              '</div>'+   
                            '</div>'+
                            '<div class="row">'+
                              '<div class="col-md-2">'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<label>Moneda</label>'+
                                      '<select name="moneda" id="moneda" class="form-control select2" style="width:100% !important;" required>'+
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
                              '<div class="col-md-2">'+
                                '<label>Pedido</label>'+
                                '<input type="text" class="form-control" name="pedido" id="pedido" data-parsley-length="[1, 50]">'+
                              '</div>'+  
                              '<div class="col-md-2">'+
                                '<label>Tipo</label>'+
                                '<select id="tipo" name="tipo" class="form-control select2" style="width:100%">'+
                                '</select>'+
                              '</div>'+  
                              '<div class="col-md-2">'+
                                '<label>Unidad</label>'+
                                '<select id="tipounidad" name="tipounidad" class="form-control select2" style="width:100%">'+
                                '</select>'+
                              '</div>'+   
                              '<div class="col-md-4" id="divbuscarcodigos"  style="display:none">'+
                                '<label>Cargar Código</label>'+
                                '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">'+
                              '</div>'+  
                              '<div class="col-md-4" id="divlistarremisiones"  style="display:none">'+
                                '<label>Remisiones</label>'+
                                '<div class="btn btn-block bg-blue waves-effect" id="btnlistarremisiones" onclick="listarremisiones()">Agregar Remisiones</div>'+
                              '</div>'+ 
                              '<div class="col-md-4" id="divlistarservicios"  style="display:none">'+
                                '<label>Servicios</label>'+
                                '<div class="btn btn-block bg-blue waves-effect" id="btnlistarservicios" onclick="listarordenes()">Agregar Servicios</div>'+
                              '</div>'+ 
                            '</div>'+
                          '</div>'+   
                          '<div role="tabpanel" class="tab-pane fade" id="emisortab">'+
                            '<div class="row">'+
                              '<div class="col-md-4">'+
                                '<label>R.F.C.</label>'+
                                '<input type="text" class="form-control" name="emisorrfc" id="emisorrfc" value="'+rfcempresa+'"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                              '</div>'+
                              '<div class="col-md-4">'+
                                '<label>Emisor Nombre</label>'+
                                '<input type="text" class="form-control" name="emisornombre" id="emisornombre" value="'+nombreempresa+'" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                              '</div>'+
                              '<div class="col-md-4">'+
                                '<label>Confirmación</label>'+
                                '<input type="text" class="form-control" name="confirmacion" id="confirmacion" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                              '</div>'+
                            '</div>'+
                            '<div class="row">'+
                              '<div class="col-md-4">'+
                                '<label>Lugar Expedición</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenerlugaresexpedicion()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="text" class="form-control" name="lugarexpedicion" id="lugarexpedicion" value="'+lugarexpedicion+'" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-4">'+
                                '<label>Régimen Fiscal</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscales()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="claveregimenfiscal" id="claveregimenfiscal" value="'+claveregimenfiscal+'" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="text" class="form-control" name="regimenfiscal" id="regimenfiscal" value="'+regimenfiscal+'" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-4">'+
                                '<label>Tipo Relación</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenertiposrelaciones()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="clavetiporelacion" id="clavetiporelacion" value="'+clavetiporelacion+'" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="text" class="form-control" name="tiporelacion" id="tiporelacion" value="'+tiporelacion+'" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+
                            '</div>'+
                            '<div class="row">'+
                              '<div class="col-md-6">'+
                                '<div class="row">'+
                                  '<div class="col-md-12 table-responsive cabecerafija" style="height: 125px;overflow-y: scroll;padding: 0px 0px;">'+
                                    '<input type="hidden" class="form-control" name="numerofilasuuid" id="numerofilasuuid" value="0" readonly>'+
                                    '<table id="tablauuidrelacionados" class="table table-bordered tablauuidrelacionados">'+
                                      '<thead class="customercolor">'+
                                        '<tr>'+
                                          '<th class="customercolortheadth">Comprobante o UUID Relacionado</th>'+
                                          '<th class="customercolortheadth">'+
                                              '<div class="col-md-12">'+
                                                '<label for="xml" class="btn btn-success">Selecciona el UUID relacionado</label>'+
                                                '<input type="file" class="form-control" name="xml" id="xml" onchange="cambiodexml(this)" style="visibility:hidden;display:none;" onclick="this.value=null;" form="formxml">'+
                                              '</div>'+
                                          '</th>'+
                                        '</tr>'+
                                      '</thead>'+
                                      '<tbody>'+           
                                      '</tbody>'+
                                    '</table>'+
                                  '</div>'+
                                '</div>'+
                              '</div>'+
                            '</div>'+
                          '</div>'+ 
                          '<div role="tabpanel" class="tab-pane fade" id="receptortab">'+
                            '<div class="row">'+
                              '<div class="col-md-3">'+
                                '<label>R.F.C.</label>'+
                                '<input type="text" class="form-control" name="receptorrfc" id="receptorrfc"   required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Nombre</label>'+
                                '<input type="text" class="form-control" name="receptornombre" id="receptornombre"  required readonly onkeyup="tipoLetra(this);" data-parsley-length="[1, 150]">'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Forma de Pago</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenerformaspago()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="claveformapago" id="claveformapago" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="text" class="form-control" name="formapago" id="formapago" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Método de Pago</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenermetodospago()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="clavemetodopago" id="clavemetodopago" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="text" class="form-control" name="metodopago" id="metodopago" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Condiciones de Pago</label>'+
                                '<input type="text" class="form-control" name="condicionesdepago" id="condicionesdepago" value="CREDITO" required readonly data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);">'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Uso CFDI</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenerusoscfdi()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="claveusocfdi" id="claveusocfdi" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="text" class="form-control" name="usocfdi" id="usocfdi" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Residencial Fiscal</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenerresidenciasfiscales()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="claveresidenciafiscal" id="claveresidenciafiscal" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="text" class="form-control" name="residenciafiscal" id="residenciafiscal" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Num Reg Id Trib</label>'+
                                '<input type="text" class="form-control" name="numeroregidtrib" id="numeroregidtrib" data-parsley-length="[1, 40]" onkeyup="tipoLetra(this);">'+
                              '</div>'+
                            '</div>'+
                          '</div>'+ 
                          '<div role="tabpanel" class="tab-pane fade" id="otrostab">'+
                            '<div class="row">'+
                              '<div class="col-md-4">'+
                                '<label>Tipo PA</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenertipospa()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="numerotipopa" id="numerotipopa"  readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="text" class="form-control" name="tipopa" id="tipopa"  readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+    
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-4">'+
                                '<label>Refactura</label>'+
                                '<input type="text" class="form-control" name="refactura" id="refactura"  readonly data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+
                              '</div>'+
                              '<div class="col-md-4">'+
                                '<label>Descripción</label>'+
                                '<textarea class="form-control" name="descripcion" id="descripcion" rows="3" onkeyup="tipoLetra(this);" data-parsley-length="[1, 255]"></textarea>'+
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
                            '<a href="#productostab" data-toggle="tab">Partidas</a>'+
                          '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                          '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                            '<div class="row">'+
                              '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                                '<table id="tabladetallesfactura" class="table table-bordered tabladetallesfactura">'+
                                  '<thead class="customercolor">'+
                                    '<tr>'+
                                      '<th class="customercolor">#</th>'+
                                      '<th class="customercolortheadth">Código</th>'+
                                      '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                                      '<th class="customercolortheadth">Uda</th>'+
                                      '<th class="customercolortheadth">Cantidad</th>'+
                                      '<th class="customercolortheadth">Precio $</th>'+
                                      '<th class="customercolor">Importe $</th>'+
                                      '<th class="customercolortheadth">Dcto %</th>'+
                                      '<th class="customercolortheadth">Dcto $</th>'+
                                      '<th class="customercolor">Importe Descuento $</th>'+
                                      '<th class="customercolor">SubTotal $</th>'+
                                      '<th class="customercolortheadth">Iva %</th>'+
                                      '<th class="customercolor">Traslado Iva $</th>'+
                                      '<th class="customercolor">Total $</th>'+
                                      '<th class="customercolor">Costo $</th>'+
                                      '<th class="customercolor">Costo Total $</th>'+
                                      '<th class="customercolortheadth">Comisión %</th>'+
                                      '<th class="customercolor">Comisión $</th>'+
                                      '<th class="bg-amber">Utilidad $</th>'+
                                      '<th class="customercolor">Remisión</th>'+
                                      '<th class="customercolor">Carta Porte</th>'+
                                      '<th class="customercolor">Orden</th>'+
                                      '<th class="customercolor">Departamento</th>'+
                                      '<th class="customercolor">Cargo</th>'+
                                      '<th class="customercolor">Partida</th>'+
                                      '<th class="customercolortheadth">Tienda</th>'+
                                      '<th class="customercolortheadth">Pedido</th>'+
                                      '<th class="customercolor">Almacén</th>'+
                                      '<th class="customercolortheadth">Datos de Unidad</th>'+
                                      '<th class="customercolor" hidden>Utilidad Aritmetica %</th>'+
                                      '<th class="customercolor" hidden>Utilidad Financiera %</th>'+
                                      '<th class="customercolor">Moneda</th>'+
                                      '<th class="customercolor">Costo de Lista</th>'+
                                      '<th class="customercolor">Tipo De Cambio</th>'+
                                      '<th class="customercolor">ClaveProducto</th>'+
                                      '<th class="customercolor">Nombre ClaveProducto</th>'+
                                      '<th class="customercolor">ClaveUnidad</th>'+
                                      '<th class="customercolor">Nombre ClaveUnidad</th>'+
                                      '<th class="customercolor" hidden>Traslado Ieps</th>'+
                                      '<th class="customercolor" hidden>Traslado Iva</th>'+
                                      '<th class="customercolor" hidden>Retención Iva</th>'+
                                      '<th class="customercolor" hidden>Retención Isr</th>'+
                                      '<th class="customercolor" hidden>Retención Ieps</th>'+
                                      '<th class="customercolor" hidden>Meses</th>'+
                                      '<th class="customercolor" hidden>Tasa Interes</th>'+
                                      '<th class="customercolor" hidden>Monto Interes</th>'+
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
                                '<td class="tdmod">Crédito</td>'+
                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="credito" id="credito" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                              '</tr>'+
                              '<tr>'+
                                '<td class="tdmod">Saldo</td>'+
                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="saldo" id="saldo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                              '</tr>'+
                              '<tr>'+
                                '<td class="tdmod">Utilidad</td>'+
                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="utilidad" id="utilidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                              '</tr>'+
                              '<tr>'+
                                '<td class="tdmod">Costo</td>'+
                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                              '</tr>'+
                              '<tr hidden>'+
                                '<td class="tdmod">Comisión</td>'+
                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="comision" id="comision" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                              '</tr>'+
                            '</table>'+
                          '</div>'+
                          '<div class="col-md-3">'+
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
                        '<div class="row">'+
                          '<div class="col-md-12">'+   
                            '<h5 id="mensajecalculoscompra"></h5>'+  
                          '</div>'+
                        '</div>'+
                      '</div>'+
                    '</div>';
  $("#tabsform").html(tabs);
  obtenultimonumero();
  obtenertiposordenescompra();
  obtenertiposunidades();
  asignarfechaactual();
  var Depto = $("#depto").val();
  comprobartiposerie(Depto);
  //asignar el tipo de operacion que se realizara
  $("#tipooperacion").val("alta");
  //se debe motrar el input para buscar los productos
  $("#divbuscarcodigoproducto").show();
  //activar los input select
  $("#moneda").select2();
  $("#tipo").select2();
  $("#tipounidad").select2();
  //reiniciar contadores
  contadorproductos=0;
  contadorfilas = 0;
  partida = 1;
  //activar busqueda de codigos
  $("#codigoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      listarproductos();
    }
  });
  $("#ModalAlta").modal('show');
}
//Cada que se elija un archivo
function cambiodexml(e) {  
  var tipooperacion = $("#tipooperacion").val();
  var xml = $('#xml')[0].files[0];
  var form_data = new FormData();
  form_data.append('xml', xml);  
  form_data.append('tipooperacion', tipooperacion);  
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:facturas_cargar_xml_uuid_relacionado,
    data: form_data,
    type: 'POST',
    contentType: false,
    processData: false,
    success: function (data) {
      var result = evaluaruuidexistente(data.uuid[0]);
      if(result == false){
        $("#tablauuidrelacionados tbody").append(data.uuidrelacionado);  
        renumerarfilasuuid();
        comprobarfilasuuid();
      }
    },
    error: function (data) {
      console.log(data);
    }
  });  
}
//comprobar numero filas de la tabla
function comprobarfilasuuid(){
  var numerofilasuuid = $("#tablauuidrelacionados tbody tr").length;
  $("#numerofilasuuid").val(numerofilasuuid);
}
//eliminar una fila en la tabla
function eliminarfilauuid(fila){
  var confirmacion = confirm("Esta seguro de eliminar la fila?"); 
  if (confirmacion == true) { 
    $("#filauuid"+fila).remove();
    renumerarfilasuuid();
    comprobarfilasuuid();
  }
}
//renumerar las filas de la orden de compra
function renumerarfilasuuid(){
  var lista;
  //renumerar filas tr
  lista = document.getElementsByClassName("filasuuid");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("id", "filauuid"+i);
  }
  //renumerar btn eliminar fila
  lista = document.getElementsByClassName("btneliminaruuid");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onclick", "eliminarfilauuid("+i+')');
  }
} 
function evaluaruuidexistente(uuid){
  var sumaiguales=0;
  var sumadiferentes=0;
  var sumatotal=0;
  $("tr.filasuuid").each(function () {
      var uuidrelacionado = $('.uuidrelacionado', this).val();
      if(uuid === uuidrelacionado){
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
      var subtotalpartida = $(".subtotalpartida", this).val();
      var ivaporcentajepartida = $('.ivaporcentajepartida', this).val();
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
      //subtotal partida
      subtotalpartida = new Decimal(importedescuentopesospartida).plus(trasladoiepspesospartida);
      $(".subtotalpartida", this).val(number_format(round(subtotalpartida, numerodecimales), numerodecimales, '.', ''));
      //iva en pesos de la partida
      var multiplicacionivapesospartida = new Decimal(subtotalpartida).times(ivaporcentajepartida);
      trasladoivapesospartida = new Decimal(multiplicacionivapesospartida/100);
      $('.trasladoivapesospartida', this).val(number_format(round(trasladoivapesospartida, numerodecimales), numerodecimales, '.', ''));
      //total en pesos de la partida
      var totalpesospartida = new Decimal(subtotalpartida).plus(trasladoivapesospartida);
      $('.totalpesospartida', this).val(truncar(totalpesospartida, numerodecimales).toFixed(parseInt(numerodecimales)));
      calculartotal();
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
  var utilidad = 0;
  var costo = 0;
  var comision = 0;
  $("tr.filasproductos").each(function(){
    importe = new Decimal(importe).plus($(".importepartida", this).val());
    descuento = new Decimal(descuento).plus($(".descuentopesospartida", this).val());
    subtotal = new Decimal(subtotal).plus($(".subtotalpartida", this).val());
    iva = new Decimal(iva).plus($(".trasladoivapesospartida", this).val());
    total = new Decimal(total).plus($(".totalpesospartida", this).val());
    utilidad = new Decimal(utilidad).plus($(".utilidadpartida", this).val());
    costo = new Decimal(costo).plus($(".costototalpartida", this).val());
    comision = new Decimal(comision).plus($(".comisionpesospartida", this).val());
  }); 
  $("#importe").val(number_format(round(importe, numerodecimales), numerodecimales, '.', ''));
  $("#descuento").val(number_format(round(descuento, numerodecimales), numerodecimales, '.', ''));
  $("#subtotal").val(number_format(round(subtotal, numerodecimales), numerodecimales, '.', ''));
  $("#iva").val(number_format(round(iva, numerodecimales), numerodecimales, '.', ''));
  $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
  $("#totalafacturar").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
  $("#utilidad").val(number_format(round(utilidad, numerodecimales), numerodecimales, '.', ''));
  $("#costo").val(number_format(round(costo, numerodecimales), numerodecimales, '.', ''));
  $("#comision").val(number_format(round(comision, numerodecimales), numerodecimales, '.', ''));
}
//eliminar fila
function eliminarfila(fila){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $("#filaproducto"+fila).remove();
      contadorfilas--; //importante para todos los calculo en el modulo e debe restar al contadorfilas la fila que se acaba de eliminar
      partida--;
      return ejecuta(fila);
    },1500);
  })
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
  var numerofilas = $("#tabladetallesfactura tbody tr").length;
  $("#numerofilas").val(numerofilas);
}
//renumerar las filas de la orden de compra
function renumerarfilas(){
  var lista;
  //renumerar numero prtida
  lista = document.getElementsByClassName("numeropartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].innerHTML = parseInt(i)+1;
  }
  //renumerar filas tr
  lista = document.getElementsByClassName("filasproductos");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("id", "filaproducto"+i);
  }
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("cantidadpartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
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
  //renumerar el iva en porcentaje de la partida
  lista = document.getElementsByClassName("ivaporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
}  
//guardar el registro
$("#btnGuardar").on('click', function (e) {
  e.preventDefault();
  var numerofilas = $("#numerofilas").val();
  if(parseInt(numerofilas) > 0){
      var formData = new FormData($("#formparsley")[0]);
      var form = $("#formparsley");
      if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          url:facturas_guardar,
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
        form.parsley().validate();
      }
  }else{
    msj_erroralmenosunapartidaagregada();
  }
});
//modificacion
function obtenerdatos(facturamodificar){
  $("#titulomodal").html('Modificación Factura');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(facturas_obtener_factura,{facturamodificar:facturamodificar },function(data){
    //formulario modificacion
    var tabs ='<div class="row">'+
                '<div class="col-md-12">'+
                  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                      '<li role="presentation" class="active">'+
                          '<a href="#facturatab" data-toggle="tab">Factura</a>'+
                      '</li>'+
                      '<li role="presentation">'+
                          '<a href="#emisortab" data-toggle="tab">Emisor</a>'+
                      '</li>'+
                      '<li role="presentation">'+
                          '<a href="#receptortab" data-toggle="tab">Receptor ó Cliente</a>'+
                      '</li>'+
                      '<li role="presentation">'+
                          '<a href="#otrostab" data-toggle="tab">Otros</a>'+
                      '</li>'+
                  '</ul>'+
                  '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="facturatab">'+
                      '<div class="row">'+
                        '<div class="col-md-3">'+
                          '<label>Factura <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp; <b style="color:#F44336 !important;" id="esquematexto"> Esquema: '+esquema+'</b>  <div class="btn btn-xs bg-red waves-effect" id="btnobtenerfoliosfacturas" onclick="obtenerfoliosfacturas()">Cambiar</div></label>'+
                          '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                          '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                          '<input type="hidden" class="form-control" name="stringremisionesseleccionadas" id="stringremisionesseleccionadas" readonly required>'+
                          '<input type="hidden" class="form-control" name="stringordenesseleccionadas" id="stringordenesseleccionadas" readonly required>'+
                          '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                          '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                          '<input type="hidden" class="form-control" name="esquema" id="esquema" value="'+esquema+'" readonly data-parsley-length="[1, 10]">'+
                          '<input type="hidden" class="form-control" name="depto" id="depto" value="'+depto+'" readonly >'+
                          '<input type="hidden" class="form-control" name="facturabd" id="facturabd" value="'+facturamodificar+'" readonly data-parsley-length="[1, 20]">'+
                        '</div>'+  
                        '<div class="col-md-3">'+
                          '<label>Cliente</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" id="btnobtenerclientes" onclick="obtenerclientes()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="hidden" class="form-control" name="numerocliente" id="numerocliente" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="text" class="form-control" name="cliente" id="cliente" required readonly>'+
                                  '<input type="hidden" class="form-control" name="rfccliente" id="rfccliente" required readonly>'+
                                '</div>'+
                              '</td>'+    
                            '</tr>'+    
                          '</table>'+
                        '</div>'+ 
                        '<div class="col-md-3">'+
                          '<label>Agente</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" id="btnobteneragentes" onclick="obteneragentes()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="hidden" class="form-control" name="numeroagente" id="numeroagente" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="hidden" class="form-control" name="rfcagente" id="rfcagente" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="text" class="form-control" name="agente" id="agente" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+    
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-1">'+
                          '<label>Plazo días</label>'+
                          '<input type="text" class="form-control" name="plazo" id="plazo" value="5" required >'+
                        '</div>'+  
                        '<div class="col-md-2">'+
                          '<label>Fecha</label>'+
                          '<input type="date" class="form-control" name="fecha" id="fecha" required onchange="validasolomesactual();">'+
                          '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                          '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                        '</div>'+   
                      '</div>'+
                      '<div class="row">'+
                        '<div class="col-md-2">'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<label>Moneda</label>'+
                                '<select name="moneda" id="moneda" class="form-control select2" style="width:100% !important;" required>'+
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
                        '<div class="col-md-2">'+
                          '<label>Pedido</label>'+
                          '<input type="text" class="form-control" name="pedido" id="pedido" data-parsley-length="[1, 50]">'+
                        '</div>'+  
                        '<div class="col-md-2">'+
                          '<label>Tipo</label>'+
                          '<select id="tipo" name="tipo" class="form-control select2" style="width:100%">'+
                          '</select>'+
                        '</div>'+  
                        '<div class="col-md-2">'+
                          '<label>Unidad</label>'+
                          '<select id="tipounidad" name="tipounidad" class="form-control select2" style="width:100%">'+
                          '</select>'+
                        '</div>'+   
                        '<div class="col-md-4" id="divbuscarcodigos"  style="display:none">'+
                          '<label>Cargar Código</label>'+
                          '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">'+
                        '</div>'+  
                        '<div class="col-md-4" id="divlistarremisiones"  style="display:none">'+
                          '<label>Remisiones</label>'+
                          '<div class="btn btn-block bg-blue waves-effect" id="btnlistarremisiones" onclick="listarremisiones()">Agregar Remisiones</div>'+
                        '</div>'+ 
                        '<div class="col-md-4" id="divlistarservicios"  style="display:none">'+
                          '<label>Servicios</label>'+
                          '<div class="btn btn-block bg-blue waves-effect" id="btnlistarservicios" onclick="listarordenes()">Agregar Servicios</div>'+
                        '</div>'+ 
                      '</div>'+
                    '</div>'+   
                    '<div role="tabpanel" class="tab-pane fade" id="emisortab">'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<label>R.F.C.</label>'+
                          '<input type="text" class="form-control" name="emisorrfc" id="emisorrfc" value="'+rfcempresa+'"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Emisor Nombre</label>'+
                          '<input type="text" class="form-control" name="emisornombre" id="emisornombre" value="'+nombreempresa+'" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Confirmación</label>'+
                          '<input type="text" class="form-control" name="confirmacion" id="confirmacion" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                        '</div>'+
                      '</div>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<label>Lugar Expedición</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenerlugaresexpedicion()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="text" class="form-control" name="lugarexpedicion" id="lugarexpedicion" value="'+lugarexpedicion+'" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+    
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Régimen Fiscal</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscales()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="hidden" class="form-control" name="claveregimenfiscal" id="claveregimenfiscal" value="'+claveregimenfiscal+'" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="text" class="form-control" name="regimenfiscal" id="regimenfiscal" value="'+regimenfiscal+'" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+    
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Tipo Relación</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenertiposrelaciones()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="hidden" class="form-control" name="clavetiporelacion" id="clavetiporelacion" value="'+clavetiporelacion+'" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="text" class="form-control" name="tiporelacion" id="tiporelacion" value="'+tiporelacion+'" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+    
                          '</table>'+
                        '</div>'+
                      '</div>'+
                      '<div class="row">'+
                        '<div class="col-md-6">'+
                          '<div class="row">'+
                            '<div class="col-md-12 table-responsive cabecerafija" style="height: 125px;overflow-y: scroll;padding: 0px 0px;">'+
                              '<input type="hidden" class="form-control" name="numerofilasuuid" id="numerofilasuuid" value="0" readonly>'+
                              '<table id="tablauuidrelacionados" class="table table-bordered tablauuidrelacionados">'+
                                '<thead class="customercolor">'+
                                  '<tr>'+
                                    '<th class="customercolortheadth">Comprobante o UUID Relacionado</th>'+
                                    '<th class="customercolortheadth">'+
                                        '<div class="col-md-12">'+
                                          '<label for="xml" class="btn btn-success">Selecciona el UUID relacionado</label>'+
                                          '<input type="file" class="form-control" name="xml" id="xml" onchange="cambiodexml(this)" style="visibility:hidden;display:none;" onclick="this.value=null;" form="formxml">'+
                                        '</div>'+
                                    '</th>'+
                                  '</tr>'+
                                '</thead>'+
                                '<tbody>'+           
                                '</tbody>'+
                              '</table>'+
                            '</div>'+
                          '</div>'+
                        '</div>'+
                      '</div>'+
                    '</div>'+ 
                    '<div role="tabpanel" class="tab-pane fade" id="receptortab">'+
                      '<div class="row">'+
                        '<div class="col-md-3">'+
                          '<label>R.F.C.</label>'+
                          '<input type="text" class="form-control" name="receptorrfc" id="receptorrfc"   required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Nombre</label>'+
                          '<input type="text" class="form-control" name="receptornombre" id="receptornombre"  required readonly onkeyup="tipoLetra(this);" data-parsley-length="[1, 150]">'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Forma de Pago</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenerformaspago()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="hidden" class="form-control" name="claveformapago" id="claveformapago" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="text" class="form-control" name="formapago" id="formapago" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+    
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Método de Pago</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenermetodospago()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="hidden" class="form-control" name="clavemetodopago" id="clavemetodopago" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="text" class="form-control" name="metodopago" id="metodopago" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+    
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Condiciones de Pago</label>'+
                          '<input type="text" class="form-control" name="condicionesdepago" id="condicionesdepago" value="CREDITO" required data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Uso CFDI</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenerusoscfdi()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="hidden" class="form-control" name="claveusocfdi" id="claveusocfdi" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="text" class="form-control" name="usocfdi" id="usocfdi" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+    
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Residencial Fiscal</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenerresidenciasfiscales()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="hidden" class="form-control" name="claveresidenciafiscal" id="claveresidenciafiscal" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="text" class="form-control" name="residenciafiscal" id="residenciafiscal" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+    
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Num Reg Id Trib</label>'+
                          '<input type="text" class="form-control" name="numeroregidtrib" id="numeroregidtrib"  data-parsley-length="[1, 40]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                      '</div>'+
                    '</div>'+ 
                    '<div role="tabpanel" class="tab-pane fade" id="otrostab">'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<label>Tipo PA</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenertipospa()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="hidden" class="form-control" name="numerotipopa" id="numerotipopa"  readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="text" class="form-control" name="tipopa" id="tipopa"  readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+    
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Refactura</label>'+
                          '<input type="text" class="form-control" name="refactura" id="refactura"  readonly data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Descripción</label>'+
                          '<textarea class="form-control" name="descripcion" id="descripcion" rows="3" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
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
                      '<a href="#productostab" data-toggle="tab">Partidas</a>'+
                    '</li>'+
                  '</ul>'+
                  '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                      '<div class="row">'+
                        '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                          '<table id="tabladetallesfactura" class="table table-bordered tabladetallesfactura">'+
                            '<thead class="customercolor">'+
                              '<tr>'+
                                '<th class="customercolor">#</th>'+
                                '<th class="customercolortheadth">Código</th>'+
                                '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                                '<th class="customercolortheadth">Uda</th>'+
                                '<th class="customercolortheadth">Cantidad</th>'+
                                '<th class="customercolortheadth">Precio $</th>'+
                                '<th class="customercolor">Importe $</th>'+
                                '<th class="customercolortheadth">Dcto %</th>'+
                                '<th class="customercolortheadth">Dcto $</th>'+
                                '<th class="customercolor">Importe Descuento $</th>'+
                                '<th class="customercolor">SubTotal $</th>'+
                                '<th class="customercolortheadth">Iva %</th>'+
                                '<th class="customercolor">Traslado Iva $</th>'+
                                '<th class="customercolor">Total $</th>'+
                                '<th class="customercolor">Costo $</th>'+
                                '<th class="customercolor">Costo Total $</th>'+
                                '<th class="customercolortheadth">Comisión %</th>'+
                                '<th class="customercolor">Comisión $</th>'+
                                '<th class="bg-amber">Utilidad $</th>'+
                                '<th class="customercolor">Remisión</th>'+
                                '<th class="customercolor">Carta Porte</th>'+
                                '<th class="customercolor">Orden</th>'+
                                '<th class="customercolor">Departamento</th>'+
                                '<th class="customercolor">Cargo</th>'+
                                '<th class="customercolor">Partida</th>'+
                                '<th class="customercolortheadth">Tienda</th>'+
                                '<th class="customercolortheadth">Pedido</th>'+
                                '<th class="customercolor">Almacén</th>'+
                                '<th class="customercolortheadth">Datos de Unidad</th>'+
                                '<th class="customercolor" hidden>Utilidad Aritmetica %</th>'+
                                '<th class="customercolor" hidden>Utilidad Financiera %</th>'+
                                '<th class="customercolor">Moneda</th>'+
                                '<th class="customercolor">Costo de Lista</th>'+
                                '<th class="customercolor">Tipo De Cambio</th>'+
                                '<th class="customercolor">ClaveProducto</th>'+
                                '<th class="customercolor">Nombre ClaveProducto</th>'+
                                '<th class="customercolor">ClaveUnidad</th>'+
                                '<th class="customercolor">Nombre ClaveUnidad</th>'+
                                '<th class="customercolor" hidden>Traslado Ieps</th>'+
                                '<th class="customercolor" hidden>Traslado Iva</th>'+
                                '<th class="customercolor" hidden>Retención Iva</th>'+
                                '<th class="customercolor" hidden>Retención Isr</th>'+
                                '<th class="customercolor" hidden>Retención Ieps</th>'+
                                '<th class="customercolor" hidden>Meses</th>'+
                                '<th class="customercolor" hidden>Tasa Interes</th>'+
                                '<th class="customercolor" hidden>Monto Interes</th>'+
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
                          '<td class="tdmod">Crédito</td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="credito" id="credito" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '</tr>'+
                        '<tr>'+
                          '<td class="tdmod">Saldo</td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="saldo" id="saldo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '</tr>'+
                        '<tr>'+
                          '<td class="tdmod">Utilidad</td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="utilidad" id="utilidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '</tr>'+
                        '<tr>'+
                          '<td class="tdmod">Costo</td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '</tr>'+
                        '<tr hidden>'+
                          '<td class="tdmod">Comisión</td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="comision" id="comision" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '</tr>'+
                      '</table>'+
                    '</div>'+
                    '<div class="col-md-3">'+
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
                  '<div class="row">'+
                    '<div class="col-md-12">'+   
                      '<h5 id="mensajecalculoscompra"></h5>'+  
                    '</div>'+
                  '</div>'+
                '</div>'+
              '</div>';
    $("#tabsform").html(tabs);
    obtenertiposordenescompra();
    obtenertiposunidades();
    //esconder el div del boton
    $("#btnobtenerclientes").hide();
    $("#btnobtenerfoliosfacturas").hide();
    $("#folio").val(data.factura.Folio);
    $("#serie").val(data.factura.Serie);
    $("#serietexto").html("Serie: "+data.factura.Serie);
    $("#esquematexto").html("Esquema: "+data.factura.Esquema);
    $("#esquema").val(data.factura.Esquema);
    $("#stringremisionesseleccionadas").val(data.arrayremisiones);
    $("#stringordenesseleccionadas").val(data.arrayordenes);
    $("#numerofilas").val(data.numerodetallesfactura);
    $("#depto").val(data.factura.Depto);
    $("#numerofilasuuid").val(data.numerodocumentosfactura);
    $("#fecha").val(data.fecha);
    $("#cliente").val(data.cliente.Nombre)
    $("#numerocliente").val(data.cliente.Numero);
    $("#rfccliente").val(data.cliente.Rfc);
    $("#agente").val(data.agente.Nombre);
    $("#numeroagente").val(data.agente.Numero);
    $("#rfcagente").val(data.agente.Rfc);
    $("#plazo").val(data.factura.Plazo);
    $("#moneda").val(data.factura.Moneda).change();
    $("#pesosmoneda").val(data.tipocambio);
    $("#pedido").val(data.factura.Pedido);
    $("#tipo").val(data.factura.Tipo).change();
    $("#tipounidad").val(data.factura.Unidad).change();
    $("#observaciones").val(data.factura.Obs);
    $("#emisorrfc").val(data.factura.EmisorRfc);
    $("#emisornombre").val(data.factura.EmisorNombre);
    $("#confirmacion").val(data.factura.Confirmacion);
    $("#lugarexpedicion").val(data.factura.LugarExpedicion);
    $("#regimenfiscal").val(data.regimenfiscal.Nombre);
    $("#claveregimenfiscal").val(data.regimenfiscal.Clave);
    $("#tiporelacion").val(data.nombretiporelacion);
    $("#clavetiporelacion").val(data.clavetiporelacion);
    $("#receptorrfc").val(data.factura.ReceptorRfc);
    $("#receptornombre").val(data.factura.ReceptorNombre);
    $("#formapago").val(data.formapago.Nombre);
    $("#claveformapago").val(data.formapago.Clave);
    $("#metodopago").val(data.metodopago.Nombre);
    $("#clavemetodopago").val(data.metodopago.Clave);
    $("#condicionesdepago").val(data.factura.CondicionesDePago);
    $("#usocfdi").val(data.usocfdi.Nombre);
    $("#claveusocfdi").val(data.usocfdi.Clave);
    $("#residenciafiscal").val(data.residenciafiscal.Nombre);
    $("#claveresidenciafiscal").val(data.residenciafiscal.Clave);
    $("#numeroregidtrib").val(data.factura.NumRegIdTrib);
    $("#descripcion").val(data.factura.Descripcion);
    //cargar todos los detalles
    $("#tabladetallesfactura tbody").html(data.filasdetallesfactura);
    //totales
    $("#importe").val(data.importe);
    $("#descuento").val(data.descuento);
    $("#subtotal").val(data.subtotal);
    $("#iva").val(data.iva);
    $("#total").val(data.total);  
    //cargar nota proveedor documentos
    $("#tablauuidrelacionados tbody").html(data.filasdocumentosfactura);
    //totales
    $("#credito").val(data.credito);
    $("#saldo").val(data.saldo);
    $("#utilidad").val(data.utilidad);
    $("#costo").val(data.costo)
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //activar los input select
    $(".select2").select2();
    //reiniciar contadores
    contadorproductos=data.contadorproductos;
    contadorfilas = data.contadorfilas;
    partida = data.partida;
    //activar busqueda de codigos
    $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        listarproductos();
      }
    });
    renumerarfilasuuid();
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
  var numerofilas = $("#numerofilas").val();
  if(parseInt(numerofilas) > 0){
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:facturas_guardar_modificacion,
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
      form.parsley().validate();
    }
  }else{
    msj_erroralmenosunapartidaagregada();
  }
});
//verificar si se puede dar de baja
function desactivar(facturadesactivar){
  $.get(facturas_verificar_si_continua_baja,{facturadesactivar:facturadesactivar}, function(data){
    if(data.Status == 'BAJA'){
      $("#facturadesactivar").val(0);
      $("#textomodaldesactivar").html('Error, esta Factura ya fue dado de baja');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{ 
      if(data.resultadofechas != ''){
        $("#facturadesactivar").val(0);
        $("#textomodaldesactivar").html('Error solo se pueden dar de baja las facturas del mes actual, fecha de la factura: ' + data.resultadofechas);
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        if(data.numerocuentasporcobrar > 0){
          $("#facturadesactivar").val(0);
          $("#textomodaldesactivar").html('Error esta factura tiene registros de cuentas por cobrar con el pago: ' + data.numerocuentaxcobrar);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else{
          $("#facturadesactivar").val(facturadesactivar);
          $("#textomodaldesactivar").html('Estas seguro de cambiar el estado el registro?');
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
      url:facturas_alta_o_baja,
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
//hacer busqueda de folio para exportacion en pdf
function relistarbuscarstringlike(){
  var tabla = $('#tablafoliosencontrados').DataTable();
  tabla.ajax.reload();
}
function buscarstringlike(){
  var columnastablafoliosencontrados =    '<tr>'+
                                                '<th><div style="width:80px !important;">Generar Documento en PDF</div></th>'+
                                                '<th>Factura</th>'+
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
          url: facturas_buscar_folio_string_like,
          data: function (d) {
            d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Factura', name: 'Factura' },
          { data: 'Cliente', name: 'Cliente', orderable: false, searchable: false },
          { data: 'Total', name: 'Total', orderable: false, searchable: false  },
          { data: 'Status', name: 'Status', orderable: false, searchable: false  },
      ],
  });
}
//configurar tabla
function configurar_tabla(){
  //formulario configuracion tabla
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
                                    '<label>DATOS FACTURA</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Factura" id="idFactura" class="filled-in datotabla" value="Factura" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                    '<label for="idFactura">Factura</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Serie" id="idSerie" class="filled-in datotabla" value="Serie" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idSerie">Serie</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Folio" id="idFolio" class="filled-in datotabla" value="Folio" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFolio">Folio</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Fecha" id="idFecha" class="filled-in datotabla" value="Fecha" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFecha">Fecha</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Status" id="idStatus" class="filled-in datotabla" value="Status" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                    '<label for="idStatus">Status</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="UUID" id="idUUID" class="filled-in datotabla" value="UUID" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idUUID">UUID</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Esquema" id="idEsquema" class="filled-in datotabla" value="Esquema" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idEsquema">Esquema</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Depto" id="idDepto" class="filled-in datotabla" value="Depto" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idDepto">Depto</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Cliente" id="idCliente" class="filled-in datotabla" value="Cliente" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idCliente">Cliente</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Remision" id="idRemision" class="filled-in datotabla" value="Remision" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idRemision">Remision</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="OrdenTrabajo" id="idOrdenTrabajo" class="filled-in datotabla" value="OrdenTrabajo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idOrdenTrabajo">OrdenTrabajo</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="EconomicoOrdenTrabajo" id="idEconomicoOrdenTrabajo" class="filled-in datotabla" value="EconomicoOrdenTrabajo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idEconomicoOrdenTrabajo">EconomicoOrdenTrabajo</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Pedido" id="idPedido" class="filled-in datotabla" value="Pedido" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idPedido">Pedido</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Agente" id="idAgente" class="filled-in datotabla" value="Agente" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idAgente">Agente</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Tipo" id="idTipo" class="filled-in datotabla" value="Tipo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idTipo">Tipo</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Plazo" id="idPlazo" class="filled-in datotabla" value="Plazo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idPlazo">Plazo</label>'+
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
                                    '<input type="checkbox" name="Abonos" id="idAbonos" class="filled-in datotabla" value="Abonos" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idAbonos">Abonos</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+    
                                    '<input type="checkbox" name="Descuentos" id="idDescuentos" class="filled-in datotabla" value="Descuentos" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idDescuentos">Descuentos</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+    
                                    '<input type="checkbox" name="Saldo" id="idSaldo" class="filled-in datotabla" value="Saldo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idSaldo">Saldo</label>'+
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
                                    '<input type="checkbox" name="MotivoBaja" id="idMotivoBaja" class="filled-in datotabla" value="MotivoBaja" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idMotivoBaja">MotivoBaja</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Periodo" id="idPeriodo" class="filled-in datotabla" value="Periodo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idPeriodo">Periodo</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Orden" id="idOrden" class="filled-in datotabla" value="Orden" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idOrden">Orden</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Unidad" id="idUnidad" class="filled-in datotabla" value="Unidad" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idUnidad">Unidad</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Lpa" id="idLpa" class="filled-in datotabla" value="Lpa" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idLpa">Lpa</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="BloquearObsoleto" id="idBloquearObsoleto" class="filled-in datotabla" value="BloquearObsoleto" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idBloquearObsoleto">BloquearObsoleto</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Incobrable" id="idIncobrable" class="filled-in datotabla" value="Incobrable" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idIncobrable">Incobrable</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="TipoPA" id="idTipoPA" class="filled-in datotabla" value="TipoPA" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idTipoPA">TipoPA</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Refactura" id="idRefactura" class="filled-in datotabla" value="Refactura" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idRefactura">Refactura</label>'+
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
                                    '<input type="checkbox" name="Ieps" id="idIeps" class="filled-in datotabla" value="Ieps" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idIeps">Ieps</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="IvaRetencion" id="idIvaRetencion" class="filled-in datotabla" value="IvaRetencion" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idIvaRetencion">IvaRetencion</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="IsrRetencion" id="idIsrRetencion" class="filled-in datotabla" value="IsrRetencion" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idIsrRetencion">IsrRetencion</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="IepsRetencion" id="idIepsRetencion" class="filled-in datotabla" value="IepsRetencion" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idIepsRetencion">IepsRetencion</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="ImpLocRetenciones" id="idImpLocRetenciones" class="filled-in datotabla" value="ImpLocRetenciones" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idImpLocRetenciones">ImpLocRetenciones</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="ImpLocTraslados" id="idImpLocTraslados" class="filled-in datotabla" value="ImpLocTraslados" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idImpLocTraslados">ImpLocTraslados</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Costo" id="idCosto" class="filled-in datotabla" value="Costo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idCosto">Costo</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Comision" id="idComision" class="filled-in datotabla" value="Comision" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idComision">Comision</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Utilidad" id="idUtilidad" class="filled-in datotabla" value="Utilidad" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idUtilidad">Utilidad</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Moneda" id="idMoneda" class="filled-in datotabla" value="Moneda" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idMoneda">Moneda</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="TipoCambio" id="idTipoCambio" class="filled-in datotabla" value="TipoCambio" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idTipoCambio">TipoCambio</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Descripcion" id="idDescripcion" class="filled-in datotabla" value="Descripcion" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idDescripcion">Descripcion</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Obs" id="idObs" class="filled-in datotabla" value="Obs" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idObs">Obs</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="CondicionesDePago" id="idCondicionesDePago" class="filled-in datotabla" value="CondicionesDePago" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idCondicionesDePago">CondicionesDePago</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="LugarExpedicion" id="idLugarExpedicion" class="filled-in datotabla" value="LugarExpedicion" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idLugarExpedicion">LugarExpedicion</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="RegimenFiscal" id="idRegimenFiscal" class="filled-in datotabla" value="RegimenFiscal" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idRegimenFiscal">RegimenFiscal</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Confirmacion" id="idConfirmacion" class="filled-in datotabla" value="Confirmacion" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idConfirmacion">Confirmacion</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="FormaPago" id="idFormaPago" class="filled-in datotabla" value="FormaPago" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFormaPago">FormaPago</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="MetodoPago" id="idMetodoPago" class="filled-in datotabla" value="MetodoPago" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idMetodoPago">MetodoPago</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="UsoCfdi" id="idUsoCfdi" class="filled-in datotabla" value="UsoCfdi" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idUsoCfdi">UsoCfdi</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="ResidenciaFiscal" id="idResidenciaFiscal" class="filled-in datotabla" value="ResidenciaFiscal" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idResidenciaFiscal">ResidenciaFiscal</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="TipoRelacion" id="idTipoRelacion" class="filled-in datotabla" value="TipoRelacion" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idTipoRelacion">TipoRelacion</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="NumRegIdTrib" id="idNumRegIdTrib" class="filled-in datotabla" value="NumRegIdTrib" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idNumRegIdTrib">NumRegIdTrib</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="EmisorRfc" id="idEmisorRfc" class="filled-in datotabla" value="EmisorRfc" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idEmisorRfc">EmisorRfc</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="EmisorNombre" id="idEmisorNombre" class="filled-in datotabla" value="EmisorNombre" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idEmisorNombre">EmisorNombre</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="ReceptorRfc" id="idReceptorRfc" class="filled-in datotabla" value="ReceptorRfc" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idReceptorRfc">ReceptorRfc</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="ReceptorNombre" id="idReceptorNombre" class="filled-in datotabla" value="ReceptorNombre" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idReceptorNombre">ReceptorNombre</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="FechaTimbrado" id="idFechaTimbrado" class="filled-in datotabla" value="FechaTimbrado" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFechaTimbrado">FechaTimbrado</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Hora" id="idHora" class="filled-in datotabla" value="Hora" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idHora">Hora</label>'+
                                '</div>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                          '</div>'+
                          '<div class="col-md-6">'+
                              '<div class="col-md-12 form-check">'+
                                  '<label>DATOS CLIENTE</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+  
                                  '<input type="checkbox" name="NombreCliente" id="idNombreCliente" class="filled-in datotabla" value="NombreCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idNombreCliente">NombreCliente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="RfcCliente" id="idRfcCliente" class="filled-in datotabla" value="RfcCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idRfcCliente">RfcCliente</label>'+ 
                              '</div>'+
                              '<div class="col-md-12 form-check">'+
                                  '<label>DATOS AGENTE</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+  
                                  '<input type="checkbox" name="NombreAgente" id="idNombreAgente" class="filled-in datotabla" value="NombreAgente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idNombreAgente">NombreAgente</label>'+ 
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