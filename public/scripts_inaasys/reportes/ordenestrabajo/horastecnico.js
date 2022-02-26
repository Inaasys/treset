'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
    asignarfechaactual(); 
    listar();
    activarrelistarreporteenterfechainicial();
    activarrelistarreporteenterfechafinal();
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
  $('#fechainicialreporte').val(hoy);
}
//validar que tipo de reporte se realizara
function validartiporeporte(){
    var tiporeporte = $("#tiporeporte").val();
    if(tiporeporte == 'Porsucursal'){
        generar_reporte();
        $("#divportecnico").hide();
    }else if(tiporeporte == 'Portecnico'){
        $("#string_tecnicos_seleccionados").val("");
        $("#tecnico").val("");
        $("#numerotecnico").val("");
        $("#divportecnico").show();
        generar_reporte();
    }
}
//detectar cuando en el input de objetivo mensual cambie y se presione enter para actualizar la busqueda
function activarrelistarreporteenterfechainicial(){
    var fechainicialreporte = $('#fechainicialreporte');
    fechainicialreporte.unbind();
    fechainicialreporte.bind('keyup change', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            generar_reporte();
        }
    });
}
//detectar cuando en el input de objetivo mensual cambie y se presione enter para actualizar la busqueda
function activarrelistarreporteenterfechafinal(){
    var fechafinalreporte = $('#fechafinalreporte');
    fechafinalreporte.unbind();
    fechafinalreporte.bind('keyup change', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            generar_reporte();
        }
    });
}
/*
//actualizar reporte
function generar_reporte(){
    var form = $("#formreporte");
    if (form.parsley().isValid()){
            var tabla = $('.tbllistado').DataTable();
            tabla.ajax.reload();
    }else{
        form.parsley().validate();
    }
}
*/
//actualizar reporte
function generar_reporte(){
    var form = $("#formreporte");
    if (form.parsley().isValid()){
        $('#tbllistado').DataTable().clear().destroy();
        listar();
    }else{
        form.parsley().validate();
    }
}
//realizar en reporte en excel
function generar_formato_excel(){
    var form = $("#formreporte");
    if (form.parsley().isValid()){
        var fechainicialreporte = $("#fechainicialreporte").val();
        var fechafinalreporte = $("#fechafinalreporte").val();
        var tiporeporte = $("#tiporeporte").val();
        var tipoorden = $("#tipoorden").val();
        var statusorden = $("#statusorden").val();
        var string_tecnicos_seleccionados = $("#string_tecnicos_seleccionados").val(); 
        $("#btnGenerarFormatoExcelHorasTecnico").attr("href", urlgenerarformatoexcel+'?fechainicialreporte='+fechainicialreporte+'&fechafinalreporte='+fechafinalreporte+'&tiporeporte='+tiporeporte+'&tipoorden='+tipoorden+'&statusorden='+statusorden+'&string_tecnicos_seleccionados='+string_tecnicos_seleccionados);
        $("#btnGenerarFormatoExcelHorasTecnico").click();
    }else{
        form.parsley().validate();
    }
}
//realizar en reporte en pdf
function generar_formato_pdf(){
    var form = $("#formreporte");
    if (form.parsley().isValid()){
        var fechainicialreporte = $("#fechainicialreporte").val();
        var fechafinalreporte = $("#fechafinalreporte").val();
        var tiporeporte = $("#tiporeporte").val();
        var tipoorden = $("#tipoorden").val();
        var statusorden = $("#statusorden").val();
        var string_tecnicos_seleccionados = $("#string_tecnicos_seleccionados").val(); 
        $("#btnGenerarFormatoReportePdf").attr("href", urlgenerarformatopdf+'?fechainicialreporte='+fechainicialreporte+'&fechafinalreporte='+fechafinalreporte+'&tiporeporte='+tiporeporte+'&tipoorden='+tipoorden+'&statusorden='+statusorden+'&string_tecnicos_seleccionados='+string_tecnicos_seleccionados);
        $("#btnGenerarFormatoReportePdf").click();
    }else{
        form.parsley().validate();
    }
}
//colocar el mismo valor a string tecnos seleccionados
function colocarvalorstring(){
    $("#string_tecnicos_seleccionados").val($("#tecnico").val());
}
//listar tabla reporte
function listar(){    
    var reporte = $("#statusorden").val();
    switch(reporte){
        case "FACTURADAS":
            var columnas = new Array("Tecnico", "Nombre", "Orden", "Tipo", "Factura", "Fecha", "Codigo", "Descripcion", "Horas", "Precio", "Total");
            break;
        case "DETALLES":
            var columnas = new Array("Factura", "Fecha", "Plazo", "Cliente", "NombreCliente", "TotalFactura", "AbonosCXC", "DescuentosNotasCredito", "TotalPagos", "SaldoFacturado");
            break;
    }
    var campos_tabla  = [];
    var cabecerastablareporte = "";
    for (var i = 0; i < columnas.length; i++) {
        campos_tabla.push({ 
            'data'    : columnas[i],
            'name'  : columnas[i],
            'orderable': false,
            'searchable': false
        });
        cabecerastablareporte = cabecerastablareporte +'<th>'+columnas[i]+'</th>';
    }
    $("#cabecerastablareporte").html(cabecerastablareporte);
    tabla=$('#tbllistado').DataTable({
        "lengthMenu": [ 500, 1000 ],
        "sScrollX": "110%",
        "sScrollY": "300px",
        "bScrollCollapse": true,  
        "lengthChange": false,
        "paging":   true,
        "ordering": false,
        "info":     true,
        "searching": false,
        "iDisplayLength": 500,//paginacion cada 50 registros
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
            url: generar_reporte_horas_tecnico,
            data: function (d) {
                d.fechainicialreporte = $("#fechainicialreporte").val();
                d.fechafinalreporte = $("#fechafinalreporte").val();
                d.tiporeporte = $("#tiporeporte").val();
                d.tipoorden = $("#tipoorden").val();
                d.statusorden = $("#statusorden").val();
                d.string_tecnicos_seleccionados = $("#string_tecnicos_seleccionados").val();
            }
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
//listar tecnicos
function listartecnicos(){
    mostrarmodalformulario();
    ocultarformulario();
    var tablatecnicos = '<div class="modal-header bg-red">'+
                            '<h4 class="modal-title">Selecciona los técnicos de los cuales desea obtener el reporte</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadocliente" class="tbllistadocliente table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="customercolor">'+
                                                '<tr>'+
                                                    '<th>Seleccionar</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Nombre</th>'+
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
                            '<button type="button" class="btn btn-danger btn-sm" onclick="ocultarmodalformulario();generar_reporte();">Ver Reporte</button>'+
                        '</div>';   
    $("#contenidomodaltablas").html(tablatecnicos);
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
            url: reporte_horas_tecnico_obtener_tecnicos,
            data: function (d) {
              d.string_tecnicos_seleccionados = $("#string_tecnicos_seleccionados").val();
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
                  $('#tbllistadocliente').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 250,
    });
}
//construir array de tecnicos seleccionados
function construirarraytecnicosseleccionados(){
    var string_tecnicos_seleccionados = [];
    var lista = document.getElementsByClassName("tecnicosseleccionados");
    for (var i = 0; i < lista.length; i++) {
        if(lista[i].checked){
            string_tecnicos_seleccionados.push(lista[i].value);
        }
    }
    $("#string_tecnicos_seleccionados").val(string_tecnicos_seleccionados);
    $("#tecnico").val(string_tecnicos_seleccionados);
    $("#numerotecnico").val(string_tecnicos_seleccionados);
}
init();