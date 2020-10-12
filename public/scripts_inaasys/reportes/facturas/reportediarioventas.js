'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
  asignarfechaactual(); 
  listar();
  activarrelistarreporteenter();
}
//mostrar modal formulario
function mostrarmodalformulario(){
  $("#ModalFormulario").modal('show');  
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
//listar todos los registros de la tabla
function asignarfechaactual(){
  var fechahoy = new Date();
  var dia = ("0" + fechahoy.getDate()).slice(-2);
  var mes = ("0" + (fechahoy.getMonth() + 1)).slice(-2);
  var hoy = fechahoy.getFullYear()+"-"+(mes)+"-"+(dia) ;
  $('#fechafinalreporte').val(hoy);
}
//detectar cuando en el input de objetivo mensual cambie y se presione enter para actualizar la busqueda
function activarrelistarreporteenter(){
  var objetivofinalpesos = $('#objetivofinalpesos');
  objetivofinalpesos.unbind();
  objetivofinalpesos.bind('keyup change', function(e) {
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        realizar_reporte();
      }
  });
}
//actualizar reporte
function realizar_reporte(){
  var form = $("#formventasdiarias");
  if (form.parsley().isValid()){
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
  }else{
    form.parsley().validate();
  }
}
//detectar si se presiono la tecla enter
function pulsar(e) {
  var tecla = (document.all) ? e.keyCode :e.which;
  return (tecla!=13);
}
//realizar en reporte en excel
function realizar_excel_reporte(){
  var form = $("#formventasdiarias");
  if (form.parsley().isValid()){
    $("#btngenerarexcel").click();
  }else{
    form.parsley().validate();
  }
}
//listar tabla reporte
function listar(){
  tabla=$('#tbllistado').DataTable({
    "sScrollX": "110%",
    "sScrollY": "350px",
    "bScrollCollapse": true,  
    "paging":   false,
    "ordering": false,
    "info":     false,
    "searching": false,
    "iDisplayLength": 50,//paginacion cada 50 registros
    processing: true,
    'language': {
      'loadingRecords': '&nbsp;',
      'processing': '<div class="spinner"></div>'
    },
    serverSide: true,
    ajax: {
      url: generar_reporte_diario_ventas,
      data: function (d) {
          d.fechafinalreporte = $("#fechafinalreporte").val();
          d.objetivofinalpesos = $("#objetivofinalpesos").val();
          d.numerocliente = $("#numerocliente").val();
      }
    },
    "createdRow": function ( row, data, index ) {
      if (parseFloat(data.importediatotalsiniva) < parseFloat(data.importeesperadofacturadopordia)) {
        $('td', row).eq(4).addClass('bg-red');
      }else{
        $('td', row).eq(4).addClass('bg-green');
      }
      if (parseFloat(data.acumuladomessiniva) < parseFloat(data.acumuladoesperadomes)) {
        $('td', row).eq(7).addClass('bg-red');
      }else{
        $('td', row).eq(7).addClass('bg-green');
      }
      if (parseFloat(data.porcentajeobjetivofinal) >= 100) {
        $('td', row).eq(8).addClass('bg-green');
      }
    },
    columns: [
      { data: 'fechafacturas', name: 'fechafacturas', orderable: false, searchable: false },
      { data: 'dia', name: 'dia' },
      { data: 'importediatotalsinivaconformato', name: 'importediatotalsinivaconformato' },
      { data: 'importeesperadofacturadopordiaconformato', name: 'importeesperadofacturadopordiaconformato' },
      { data: 'faltantesobranteimporteobjetivoconformato', name: 'faltantesobranteimporteobjetivoconformato' },
      { data: 'acumuladomessinivaconformato', name: 'acumuladomessinivaconformato' },
      { data: 'acumuladoesperadomesconformato', name: 'acumuladoesperadomesconformato'},
      { data: 'faltantesobranteacumuladoobjetivoconformato', name: 'faltantesobranteacumuladoobjetivoconformato'},
      { data: 'porcentajeobjetivofinal', name: 'porcentajeobjetivofinal'}
    ],
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
//identificar si el reporte se hara por cliente o no
function filtrocliente(){
  if($('#idporcliente').prop('checked')){
    seleccionarcliente(1,1,1);
    $("#divfiltrocliente").show();
  }else{
    seleccionarcliente(0,0,0);
    $("#divfiltrocliente").hide();
  }
}
function listarclientes(){
  mostrarmodalformulario();
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
                          '<button type="button" class="btn btn-danger btn-sm" onclick="ocultarmodalformulario();">Salir</button>'+
                      '</div>';   
  $("#contenidomodaltablas").html(tablaclientes);
  $('#tbllistadocliente').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
          'loadingRecords': '&nbsp;',
          'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
          url: reporte_ventas_diarias_obtener_clientes,
          data: function (d) {
            d.numeroabuscar = $("#numeroabuscar").val();
          }
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Numero', name: 'Numero' },
          { data: 'Nombre', name: 'Nombre' },
          { data: 'Rfc', name: 'Rfc', orderable: false, searchable: false },
          { data: 'Municipio', name: 'Municipio', orderable: false, searchable: false } 
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
function seleccionarcliente(Numero, Nombre){
    $("#cliente").val(Nombre);
    $("#numerocliente").val(Numero);
    ocultarmodalformulario();
}
init();