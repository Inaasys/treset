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
//obtener registros de clientes
function listarclientesfacturaa(){
  ocultarformulario();
  var tablaclientesfacturaa = '<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Factura a</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoclientesfacturaa" class="tbllistadoclientesfacturaa table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                '<thead class="customercolor">'+
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
        "iDisplayLength": 8,
    }); 
} 
function seleccionarclientefacturaa(Numero, Nombre, Plazo, NumeroAgente, Agente){
    $("#numeroclientefacturaa").val(Numero);
    $("#clientefacturaa").val(Nombre);
    $("#plazodias").val(Plazo);
    //datos agente
    $("#numeroagente").val(NumeroAgente);
    $("#agente").val(Agente);
    mostrarformulario();
}
//obtener registros de clientes
function listarclientesdelcliente(){
  ocultarformulario();
  var tablaclientesdelcliente = '<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Del cliente</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoclientesdelcliente" class="tbllistadoclientesdelcliente table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                '<thead class="customercolor">'+
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
        "iDisplayLength": 8,
    }); 
} 
function seleccionarclientedelcliente(Numero, Nombre, Plazo){
    $("#numeroclientedelcliente").val(Numero);
    $("#clientedelcliente").val(Nombre);
    mostrarformulario();
}
//obtener registros de agentes
function listaragentes(){
  ocultarformulario();
  var tablaagentes = '<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Agentes</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive" >'+
                                            '<table id="tbllistadoagentes" class="tbllistadoagentes table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                '<thead class="customercolor">'+
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
        "iDisplayLength": 8,
    }); 
} 
function seleccionaragente(Numero, Nombre){
    $("#numeroagente").val(Numero);
    $("#agente").val(Nombre);
    mostrarformulario();
}
//obtener técnicos
function listartecnicos(){
  if(parseInt(contadortecnicos) < 5){  
    asignaciontecnicosocultarformulario();
    var tablatecnicos = '<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Técnicos</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadotecnicos" class="tbllistadotecnicos table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                '<thead class="customercolor">'+
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
        "iDisplayLength": 8,
    }); 
  }else{
    msjsolo4tecnicospermitidos();
  }
} 
//obtener registros de vines
function listarvines(){
  ocultarformulario();
  var tablavines = '<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Vines</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadovines" class="tbllistadovines table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                '<thead class="customercolor">'+
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
        "iDisplayLength": 8,
    }); 
} 
function seleccionarvin(Cliente, Economico, Vin, Placas, Motor, Marca, Modelo, Año, Color){
    $("#vin").val(Vin);
    $("#motor").val(Motor);
    $("#marca").val(Marca);
    $("#modelo").val(Modelo);
    $("#ano").val(Año);
    $("#placas").val(Placas);
    $("#economico").val(Economico);
    $("#color").val(Color);
    mostrarformulario();
}
//detectar cuando en el input de buscar por codigo de servicio el usuario presione la tecla enter, si es asi se realizara la busqueda con el codigo escrito
$(document).ready(function(){
  $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        listarservicios();
      }
  });
});
//listar productos para tab consumos
function listarservicios(){
  ocultarformulario();
  var tablaservicios = '<div class="modal-header bg-red">'+
                          '<h4 class="modal-title">Servicios</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                          '<div class="row">'+
                            '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                '<table id="tbllistadoservicio" class="tbllistadoservicio table table-bordered table-striped table-hover" style="width:100% !important">'+
                                  '<thead class="customercolor">'+
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
    "iDisplayLength": 8,
  });
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
    var fechahoy = new Date();
    var dia = ("0" + fechahoy.getDate()).slice(-2);
    var mes = ("0" + (fechahoy.getMonth() + 1)).slice(-2);
    var hoy = fechahoy.getFullYear()+"-"+(mes)+"-"+(dia) ;
    var fila=   '<tr class="filasservicios" id="filaservicio'+contadorservicios+'">'+
                        '<td class="tdmod"><div class="divorinputmodmd">'+
                          '<div class="btn bg-red btn-xs" data-toggle="tooltip" title="Eliminar Fila" onclick="eliminarfila('+contadorservicios+')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly> '+
                          '<div class="btn bg-blue btn-xs" data-toggle="tooltip" title="Asignar Técnicos" onclick="asignaciontecnicos('+contadorservicios+')">Asignar técnicos</div>'+
                        '</div></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control tipofila" name="tipofila[]" value="agregado" readonly><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]">'+Codigo+'</td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'+Servicio+'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'+
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
                        '<td class="tdmod"><input type="date" class="form-control divorinputmodmd fechapartida" name="fechapartida[]" value="'+hoy+'" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs traspasopartida" name="traspasopartida[]" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs comprapartida" name="comprapartida[]" readonly data-parsley-length="[1, 20]"></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxs usuariopartida" name="usuariopartida[]" value="'+usuario+'" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxl anotacionespartida" name="anotacionespartida[]" data-parsley-length="[1, 255]"></td>'+
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
  var formasignaciontecnicos =  '<div class="modal-header bg-blue">'+
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
                                              '<textarea class="form-control" name="asignaciontecnicosanotaciones" id="asignaciontecnicosanotaciones" placeholder="Escribe las anotaciones" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rowspan="1">'+anotaciones+'</textarea>'+
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
  var tabs =    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#serviciostab" data-toggle="tab">Servicios</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="serviciostab">'+
                        '<div class="row">'+
                            '<div class="col-md-12 table-responsive cabecerafija" style="height: 150px;overflow-y: scroll;padding: 0px 0px;">'+
                                '<table id="tablaserviciosordentrabajo" class="table table-bordered tablaserviciosordentrabajo">'+
                                    '<thead class="customercolor">'+
                                        '<tr>'+
                                          '<th class="customercolor">#</th>'+
                                          '<th class="customercolor">Código</th>'+
                                          '<th class="customercolor"><div style="width:200px !important;">Descripción</div></th>'+
                                          '<th class="customercolor">Unidad</th>'+
                                          '<th class="customercolortheadth">Cantidad</th>'+
                                          '<th class="customercolortheadth">Precio $</th>'+
                                          '<th class="customercolor">Importe $</th>'+
                                          '<th class="customercolortheadth">Dcto %</th>'+
                                          '<th class="customercolortheadth">Dcto $</th>'+
                                          '<th class="customercolor">SubTotal $</th>'+
                                          '<th class="customercolortheadth">Iva %</th>'+
                                          '<th class="customercolor">Iva $</th>'+
                                          '<th class="customercolor">Total $</th>'+
                                          '<th class="customercolor">Costo $</th>'+
                                          '<th class="customercolor">Costo Total $</th>'+
                                          '<th class="customercolortheadth">Comisión %</th>'+
                                          '<th class="customercolor">Comisión $</th>'+
                                          '<th class="bg-amber">Utilidad $</th>'+
                                          '<th class="customercolor">Departamento</th>'+
                                          '<th class="customercolor">Cargo</th>'+
                                          '<th class="customercolor">Fecha</th>'+
                                          '<th class="customercolor">Traspaso</th>'+
                                          '<th class="customercolor">Compra</th>'+
                                          '<th class="customercolor">Usuario</th>'+
                                          '<th class="customercolortheadth">Anotaciones</th>'+
                                          '<th class="customercolor">Status</th>'+
                                          '<th class="customercolor">Item</th>'+
                                          '<th class="customercolor">Técnico 1</th>'+
                                          '<th class="customercolor">Técnico 2</th>'+
                                          '<th class="customercolor">Técnico 3</th>'+
                                          '<th class="customercolor">Técnico 4</th>'+
                                          '<th class="customercolor">Horas 1</th>'+
                                          '<th class="customercolor">Horas 2</th>'+
                                          '<th class="customercolor">Horas 3</th>'+
                                          '<th class="customercolor">Horas 4</th>'+
                                          '<th class="customercolortheadth">Promoción</th>'+
                                          '<th class="customercolor">Partida</th>'+
                                          '<th class="customercolor">Almacén</th>'+
                                          '<th class="customercolor">Cotización</th>'+
                                        '</tr>'+
                                    '</thead>'+
                                    '<tbody>'+           
                                    '</tbody>'+
                                '</table>'+
                            '</div>'+
                        '</div>'+ 
                        '<div class="row">'+
                          '<div class="col-md-10">'+
                            '<div class="row">'+ 
                              '<div class="col-md-3">'+
                                '<label>Falla</label>'+
                                '<textarea class="form-control" name="falla" id="falla"  required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"  rowspan="3"></textarea>'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Observaciones</label>'+
                                '<textarea class="form-control" name="observaciones" id="observaciones"  required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rowspan="3"></textarea>'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Causa</label>'+
                                '<textarea class="form-control" name="causa" id="causa" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rowspan="3"></textarea>'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Corrección</label>'+
                                '<textarea class="form-control" name="correccion" id="correccion"  data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rowspan="3"></textarea>'+
                              '</div>'+
                            '</div>'+  
                          '</div>'+ 
                          '<div class="col-md-2">'+
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
                '</div>';
  $("#tabsform").html(tabs);
  $("#serie").val(serieusuario);
  $("#serietexto").html("Serie: "+serieusuario);
  $("#numerofilastablaservicios").val(0);
  $("#numerofilas").val(0);
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
    form.parsley().validate();
    msjfaltandatosporcapturar();
  }
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
  $("#titulomodal").html('Modificación Orden Trabajo');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(ordenes_trabajo_obtener_orden_trabajo,{ordenmodificar:ordenmodificar },function(data){
    //formulario modificacion
    var tabs ='<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                '<li role="presentation" class="active">'+
                    '<a href="#serviciostab" data-toggle="tab">Servicios</a>'+
                '</li>'+
              '</ul>'+
              '<div class="tab-content">'+
                  '<div role="tabpanel" class="tab-pane fade in active" id="serviciostab">'+
                      '<div class="row">'+
                          '<div class="col-md-12 table-responsive cabecerafija" style="height: 150px;overflow-y: scroll;padding: 0px 0px;">'+
                              '<table id="tablaserviciosordentrabajo" class="table table-bordered tablaserviciosordentrabajo">'+
                                  '<thead class="customercolor">'+
                                      '<tr>'+
                                        '<th class="customercolor">#</th>'+
                                        '<th class="customercolor">Código</th>'+
                                        '<th class="customercolor"><div style="width:200px !important;">Descripción</div></th>'+
                                        '<th class="customercolor">Unidad</th>'+
                                        '<th class="customercolortheadth">Cantidad</th>'+
                                        '<th class="customercolortheadth">Precio $</th>'+
                                        '<th class="customercolor">Importe $</th>'+
                                        '<th class="customercolortheadth">Dcto %</th>'+
                                        '<th class="customercolortheadth">Dcto $</th>'+
                                        '<th class="customercolor">SubTotal $</th>'+
                                        '<th class="customercolortheadth">Iva %</th>'+
                                        '<th class="customercolor">Iva $</th>'+
                                        '<th class="customercolor">Total $</th>'+
                                        '<th class="customercolor">Costo $</th>'+
                                        '<th class="customercolor">Costo Total $</th>'+
                                        '<th class="customercolortheadth">Comisión %</th>'+
                                        '<th class="customercolor">Comisión $</th>'+
                                        '<th class="bg-amber">Utilidad $</th>'+
                                        '<th class="customercolor">Departamento</th>'+
                                        '<th class="customercolor">Cargo</th>'+
                                        '<th class="customercolor">Fecha</th>'+
                                        '<th class="customercolor">Traspaso</th>'+
                                        '<th class="customercolor">Compra</th>'+
                                        '<th class="customercolor">Usuario</th>'+
                                        '<th class="customercolortheadth">Anotaciones</th>'+
                                        '<th class="customercolor">Status</th>'+
                                        '<th class="customercolor">Item</th>'+
                                        '<th class="customercolor">Técnico 1</th>'+
                                        '<th class="customercolor">Técnico 2</th>'+
                                        '<th class="customercolor">Técnico 3</th>'+
                                        '<th class="customercolor">Técnico 4</th>'+
                                        '<th class="customercolor">Horas 1</th>'+
                                        '<th class="customercolor">Horas 2</th>'+
                                        '<th class="customercolor">Horas 3</th>'+
                                        '<th class="customercolor">Horas 4</th>'+
                                        '<th class="customercolortheadth">Promoción</th>'+
                                        '<th class="customercolor">Partida</th>'+
                                        '<th class="customercolor">Almacén</th>'+
                                        '<th class="customercolor">Cotización</th>'+
                                      '</tr>'+
                                  '</thead>'+
                                  '<tbody>'+           
                                  '</tbody>'+
                              '</table>'+
                          '</div>'+
                      '</div>'+ 
                      '<div class="row">'+
                        '<div class="col-md-10">'+  
                          '<div class="row">'+ 
                            '<div class="col-md-3">'+
                              '<label>Falla</label>'+
                              '<textarea class="form-control" name="falla" id="falla"  required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rowspan="3"></textarea>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                              '<label>Observaciones</label>'+
                              '<textarea class="form-control" name="observaciones" id="observaciones"  required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rowspan="3"></textarea>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                              '<label>Causa</label>'+
                              '<textarea class="form-control" name="causa" id="causa" data-parsley-length="[1, 255]"  onkeyup="tipoLetra(this);" rowspan="3"></textarea>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                              '<label>Corrección</label>'+
                              '<textarea class="form-control" name="correccion" id="correccion"  data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" rowspan="3"></textarea>'+
                            '</div>'+
                          '</div>'+
                        '</div>'+ 
                        '<div class="col-md-2 ">'+
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
              '</div>';
    $("#tabsform").html(tabs);
    $("#folio").val(data.ordentrabajo.Folio);
    $("#serie").val(data.ordentrabajo.Serie);
    $("#serietexto").html("Serie: "+data.ordentrabajo.Serie);
    $("#numerofilastablaservicios").val(data.numerodetallesordentrabajo);
    $("#numerofilas").val(data.numerodetallesordentrabajo);
    $("#fecha").val(data.fecha);
    $("#fechaentregapromesa").val(data.fechaentrega);
    $("#numeroclientefacturaa").val(data.cliente.Numero);
    $("#clientefacturaa").val(data.cliente.Nombre);
    $("#numeroclientedelcliente").val(data.delcliente.Numero);
    $("#clientedelcliente").val(data.delcliente.Nombre);
    $("#numeroagente").val(data.agente.Numero);
    $("#agente").val(data.agente.Nombre);
    $("#caso").val(data.ordentrabajo.Caso);
    $("#vin").val(data.ordentrabajo.Vin);
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
    $("#horasreales").val(data.ordentrabajo.HorasReales);
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
        console.log(data);
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
    msjfaltandatosporcapturar();
  }
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
                                  '<label>DATOS ORDEN TRABAJO</label>'+
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
                                  '<input type="checkbox" name="Caso" id="idCaso" class="filled-in datotabla" value="Caso" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCaso">Caso</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Fecha" id="idFecha" class="filled-in datotabla" value="Fecha" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFecha">Fecha</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Tipo" id="idTipo" class="filled-in datotabla" value="Tipo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTipo">Tipo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Unidad" id="idUnidad" class="filled-in datotabla" value="Unidad" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idUnidad">Unidad</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Cliente" id="idCliente" class="filled-in datotabla" value="Cliente" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCliente">Cliente</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Total" id="idTotal" class="filled-in datotabla" value="Total" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTotal">Total</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Vin" id="idVin" class="filled-in datotabla" value="Vin" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idVin">Vin</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+    
                                  '<input type="checkbox" name="Pedido" id="idPedido" class="filled-in datotabla" value="Pedido" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPedido">Pedido</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Marca" id="idMarca" class="filled-in datotabla" value="Marca" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idMarca">Marca</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Economico" id="idEconomico" class="filled-in datotabla" value="Economico" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEconomico">Economico</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Placas" id="idPlacas" class="filled-in datotabla" value="Placas" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPlacas">Placas</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Año" id="idAño" class="filled-in datotabla" value="Año" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idAño">Año</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Kilometros" id="idKilometros" class="filled-in datotabla" value="Kilometros" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idKilometros">Kilometros</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Reclamo" id="idReclamo" class="filled-in datotabla" value="Reclamo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idReclamo">Reclamo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Motor" id="idMotor" class="filled-in datotabla" value="Motor" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idMotor">Motor</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="MotivoBaja" id="idMotivoBaja" class="filled-in datotabla" value="MotivoBaja" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idMotivoBaja">MotivoBaja</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Usuario" id="idUsuario" class="filled-in datotabla" value="Usuario" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idUsuario">Usuario</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Equipo" id="idEquipo" class="filled-in datotabla" value="Equipo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEquipo">Equipo</label>'+
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
                                  '<input type="checkbox" name="DelCliente" id="idDelCliente" class="filled-in datotabla" value="DelCliente" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idDelCliente">DelCliente</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Agente" id="idAgente" class="filled-in datotabla" value="Agente" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idAgente">Agente</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Plazo" id="idPlazo" class="filled-in datotabla" value="Plazo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPlazo">Plazo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Entrega" id="idEntrega" class="filled-in datotabla" value="Entrega" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEntrega">Entrega</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Laminado" id="idLaminado" class="filled-in datotabla" value="Laminado" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idLaminado">Laminado</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ServicioEnAgencia" id="idServicioEnAgencia" class="filled-in datotabla" value="ServicioEnAgencia" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idServicioEnAgencia">ServicioEnAgencia</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="RetrabajoOrden" id="idRetrabajoOrden" class="filled-in datotabla" value="RetrabajoOrden" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idRetrabajoOrden">RetrabajoOrden</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Impuesto" id="idImpuesto" class="filled-in datotabla" value="Impuesto" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idImpuesto">Impuesto</label>'+
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
                                  '<input type="checkbox" name="SubTotal" id="idSubTotal" class="filled-in datotabla" value="SubTotal" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idSubTotal">SubTotal</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Iva" id="idIva" class="filled-in datotabla" value="Iva" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idIva">Iva</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Facturado" id="idFacturado" class="filled-in datotabla" value="Facturado" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFacturado">Facturado</label>'+
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
                                  '<input type="checkbox" name="Operador" id="idOperador" class="filled-in datotabla" value="Operador" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idOperador">Operador</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="OperadorCelular" id="idOperadorCelular" class="filled-in datotabla" value="OperadorCelular" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idOperadorCelular">OperadorCelular</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Modelo" id="idModelo" class="filled-in datotabla" value="Modelo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idModelo">Modelo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Color" id="idColor" class="filled-in datotabla" value="Color" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idColor">Color</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Combustible" id="idCombustible" class="filled-in datotabla" value="Combustible" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCombustible">Combustible</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Bahia" id="idBahia" class="filled-in datotabla" value="Bahia" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idBahia">Bahia</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Forma" id="idForma" class="filled-in datotabla" value="Forma" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idForma">Forma</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ObsOrden" id="idObsOrden" class="filled-in datotabla" value="ObsOrden" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idObsOrden">ObsOrden</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ObsUnidad" id="idObsUnidad" class="filled-in datotabla" value="ObsUnidad" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idObsUnidad">ObsUnidad</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Campaña" id="idCampaña" class="filled-in datotabla" value="Campaña" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCampaña">Campaña</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Falla" id="idFalla" class="filled-in datotabla" value="Falla" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFalla">Falla</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Causa" id="idCausa" class="filled-in datotabla" value="Causa" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCausa">Causa</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Correccion" id="idCorreccion" class="filled-in datotabla" value="Correccion" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCorreccion">Correccion</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Rodar" id="idRodar" class="filled-in datotabla" value="Rodar" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idRodar">Rodar</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Terminada" id="idTerminada" class="filled-in datotabla" value="Terminada" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTerminada">Terminada</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Facturada" id="idFacturada" class="filled-in datotabla" value="Facturada" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFacturada">Facturada</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="HoraEntrada" id="idHoraEntrada" class="filled-in datotabla" value="HoraEntrada" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idHoraEntrada">HoraEntrada</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="HoraEntrega" id="idHoraEntrega" class="filled-in datotabla" value="HoraEntrega" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idHoraEntrega">HoraEntrega</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="HorasReales" id="idHorasReales" class="filled-in datotabla" value="HorasReales" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idHorasReales">HorasReales</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Promocion" id="idPromocion" class="filled-in datotabla" value="Promocion" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPromocion">Promocion</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="TipoServicio" id="idTipoServicio" class="filled-in datotabla" value="TipoServicio" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTipoServicio">TipoServicio</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="KmProximoServicio" id="idKmProximoServicio" class="filled-in datotabla" value="KmProximoServicio" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idKmProximoServicio">KmProximoServicio</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FechaRecordatorio" id="idFechaRecordatorio" class="filled-in datotabla" value="FechaRecordatorio" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFechaRecordatorio">FechaRecordatorio</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FechaIngresoUnidad" id="idFechaIngresoUnidad" class="filled-in datotabla" value="FechaIngresoUnidad" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFechaIngresoUnidad">FechaIngresoUnidad</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FechaAsignacionUnidad" id="idFechaAsignacionUnidad" class="filled-in datotabla" value="FechaAsignacionUnidad" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFechaAsignacionUnidad">FechaAsignacionUnidad</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FechaTerminoUnidad" id="idFechaTerminoUnidad" class="filled-in datotabla" value="FechaTerminoUnidad" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFechaTerminoUnidad">FechaTerminoUnidad</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="EstadoServicio" id="idEstadoServicio" class="filled-in datotabla" value="EstadoServicio" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEstadoServicio">EstadoServicio</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Refactura" id="idRefactura" class="filled-in datotabla" value="Refactura" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idRefactura">Refactura</label>'+
                              '</div>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                          '</div>'+
                          '<div class="col-md-6">'+
                              '<div class="col-md-12 form-check">'+
                                  '<label>DATOS CLIENTE</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="NumeroCliente" id="idNumeroCliente" class="filled-in datotabla" value="NumeroCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idNumeroCliente">NumeroCliente</label>'+  
                              '</div>'+
                              '<div class="col-md-4 form-check">'+  
                                  '<input type="checkbox" name="NombreCliente" id="idNombreCliente" class="filled-in datotabla" value="NombreCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idNombreCliente">NombreCliente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="RfcCliente" id="idRfcCliente" class="filled-in datotabla" value="RfcCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idRfcCliente">RfcCliente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="CalleCliente" id="idCalleCliente" class="filled-in datotabla" value="CalleCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idCalleCliente">CalleCliente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="noExteriorCliente" id="idnoExteriorCliente" class="filled-in datotabla" value="noExteriorCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idnoExteriorCliente">noExteriorCliente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ColoniaCliente" id="idColoniaCliente" class="filled-in datotabla" value="ColoniaCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idColoniaCliente">ColoniaCliente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="LocalidadCliente" id="idLocalidadCliente" class="filled-in datotabla" value="LocalidadCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idLocalidadCliente">LocalidadCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="MunicipioCliente" id="idMunicipioCliente" class="filled-in datotabla" value="MunicipioCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idMunicipioCliente">MunicipioCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="EstadoCliente" id="idEstadoCliente" class="filled-in datotabla" value="EstadoCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idEstadoCliente">EstadoCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="PaisCliente" id="idPaisCliente" class="filled-in datotabla" value="PaisCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idPaisCliente">PaisCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="CodigoPostalCliente" id="idCodigoPostalCliente" class="filled-in datotabla" value="CodigoPostalCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idCodigoPostalCliente">CodigoPostalCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ReferenciaCliente" id="idReferenciaCliente" class="filled-in datotabla" value="ReferenciaCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idReferenciaCliente">ReferenciaCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="TelefonosCliente" id="idTelefonosCliente" class="filled-in datotabla" value="TelefonosCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idTelefonosCliente">TelefonosCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Email1Cliente" id="idEmail1Cliente" class="filled-in datotabla" value="Email1Cliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idEmail1Cliente">Email1Cliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="AnotacionesCliente" id="idAnotacionesCliente" class="filled-in datotabla" value="AnotacionesCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idAnotacionesCliente">AnotacionesCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FormaPagoCliente" id="idFormaPagoCliente" class="filled-in datotabla" value="FormaPagoCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idFormaPagoCliente">FormaPagoCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="MetodoPagoCliente" id="idMetodoPagoCliente" class="filled-in datotabla" value="MetodoPagoCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idMetodoPagoCliente">MetodoPagoCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="UsoCfdiCliente" id="idUsoCfdiCliente" class="filled-in datotabla" value="UsoCfdiCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idUsoCfdiCliente">UsoCfdiCliente</label>'+                                     
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