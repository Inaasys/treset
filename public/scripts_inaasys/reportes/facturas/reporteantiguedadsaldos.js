'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
  asignarfechaactual(); 
  activarrelistarreporteenterfechacorte();
  listar();
}
//mostrar formulario
function mostrarformulario(){
    $("#ModalFormulario").modal('hide');
    $("#contenidomodaltablas").hide();
    $("#formulario").hide();
}
//listar todos los registros de la tabla
function asignarfechaactual(){
  var fechahoy = new Date();
  var dia = ("0" + fechahoy.getDate()).slice(-2);
  var mes = ("0" + (fechahoy.getMonth() + 1)).slice(-2);
  var hoy = fechahoy.getFullYear()+"-"+(mes)+"-"+(dia) ;
  $('#fechacorte').val(hoy);
}
//detectar cuando en el input de objetivo mensual cambie y se presione enter para actualizar la busqueda
function activarrelistarreporteenterfechacorte(){
    var fechacorte = $('#fechacorte');
    fechacorte.unbind();
    fechacorte.bind('keyup change', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            generar_reporte();
        }
    });
}
//activar busquedas
$(document).ready(function() {
    //activar busqueda
    $('#numerocliente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclientepornumero();
            e.preventDefault();
        }
    });
    //activar busqueda
    $('#claveserie').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerserieporclave();
            e.preventDefault();
        }
    });
    //regresar numero
    $('#numerocliente').on('change', function(e) {
        regresarnumerocliente();
    });
    //regresar numero
    $('#claveserie').on('change', function(e) {
        regresarclaveserie();
    });
});
//obtener clientes
function obtenerclientes(){
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablaclientes = '<div class="modal-header '+background_forms_and_modals+'">'+
                              '<h4 class="modal-title">Clientes</h4>'+
                          '</div>'+
                          '<div class="modal-body">'+
                              '<div class="row">'+
                                  '<div class="col-md-12">'+
                                      '<div class="table-responsive">'+
                                          '<table id="tbllistadocliente" class="tbllistadocliente table table-bordered table-striped table-hover" style="width:100% !important">'+
                                              '<thead class="'+background_tables+'">'+
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
                              '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                          '</div>';
        $("#contenidomodaltablas").html(tablaclientes);
        $('#tbllistadocliente').DataTable({
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
                url: reporte_antiguedad_saldos_obtener_clientes
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
            
        }); 
} 
//obtener registros de series
function obtenerseries(){ 
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablaseries ='<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Series</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive ">'+
                                        '<table id="tbllistadoserie" class="tbllistadoserie table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Serie</th>'+
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
    $("#contenidomodaltablas").html(tablaseries);
    $('#tbllistadoserie').DataTable({
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
            url: reporte_antiguedad_saldos_obtener_series,
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Serie', name: 'Serie' },
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                    $('#tbllistadoserie').DataTable().search( this.value ).draw();
                }
            });
        },    
    }); 
} 
function seleccionarcliente(Numero, Nombre){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    var numerocliente = Numero;
    if(numeroclienteanterior != numerocliente){
        $("#numerocliente").val(Numero);
        $("#numeroclienteanterior").val(Numero);
        $("#cliente").val(Nombre);
        if(Nombre != null){
            $("#textonombrecliente").attr('style', 'font-size:8px').html(Nombre.substring(0, 25));
        }
        generar_reporte();
        $("#ModalFormulario").modal('hide');
    }
}
function seleccionarserie(Serie){
    var claveserieanterior = $("#claveserieanterior").val();
    var claveserie = Serie;
    if(claveserieanterior != claveserie){
        $("#claveserie").val(Serie);
        $("#claveserieanterior").val(Serie);
        $("#serie").val(Serie);
        if(Serie != null){
            $("#textonombreserie").attr('style', 'font-size:8px').html(Serie.substring(0, 45));
        }
        generar_reporte();
        $("#ModalFormulario").modal('hide');
    }
}
//obtener por numero
function obtenerclientepornumero(){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    var numerocliente = $("#numerocliente").val();
    if(numeroclienteanterior != numerocliente){
        if($("#numerocliente").parsley().isValid()){
            $.get(reporte_antiguedad_saldos_obtener_cliente_por_numero, {numerocliente:numerocliente}, function(data){
                $("#numerocliente").val(data.numero);
                $("#numeroclienteanterior").val(data.numero);
                $("#cliente").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombrecliente").attr('style', 'font-size:8px').html(data.nombre.substring(0, 25));
                }
                generar_reporte();
            }) 
        }
    }
}
//regresar numero
function regresarnumerocliente(){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    $("#numerocliente").val(numeroclienteanterior);
}
//obtener por numero
function obtenerserieporclave(){
    var claveserieanterior = $("#claveserieanterior").val();
    var claveserie = $("#claveserie").val();
    if(claveserieanterior != claveserie){
        if($("#claveserie").parsley().isValid()){
            $.get(reporte_antiguedad_saldos_obtener_serie_por_clave, {claveserie:claveserie}, function(data){
                $("#claveserie").val(data.claveserie);
                $("#claveserieanterior").val(data.claveserie);
                $("#serie").val(data.claveserie);
                if(data.claveserie != null){
                    $("#textonombreserie").attr('style', 'font-size:8px').html(data.claveserie.substring(0, 45));
                }
                generar_reporte();
            })  
        }
    }
}
//regresar numero
function regresarclaveserie(){
    var claveserieanterior = $("#claveserieanterior").val();
    $("#claveserie").val(claveserieanterior);
}
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
        var fechacorte = $("#fechacorte").val();
        var numerocliente = $("#numerocliente").val();
        var claveserie = $("#claveserie").val();
        var departamento = $("#departamento").val();
        if( $('#idsaldomayor').prop('checked') ) {
            var saldomayor = 1;
        }else{
            saldomayor = 0;
        }
        var reporte = $("#reporte").val();
        $("#btnGenerarFormatoReporteExcel").attr("href", urlgenerarformatoexcel+'?fechacorte='+fechacorte+'&numerocliente='+numerocliente+'&claveserie='+claveserie+'&departamento='+departamento+'&saldomayor='+saldomayor+'&reporte='+reporte);
        $("#btnGenerarFormatoReporteExcel").click();
    }else{
        form.parsley().validate();
    }
}

//realizar en reporte en pdf
function generar_formato_pdf(){
    var form = $("#formreporte");
    if (form.parsley().isValid()){
        var fechacorte = $("#fechacorte").val();
        var numerocliente = $("#numerocliente").val();
        var claveserie = $("#claveserie").val();
        var departamento = $("#departamento").val();
        if( $('#idsaldomayor').prop('checked') ) {
            var saldomayor = 1;
        }else{
            saldomayor = 0;
        }
        var reporte = $("#reporte").val();
        $("#btnGenerarFormatoReportePdf").attr("href", urlgenerarformatopdf+'?fechacorte='+fechacorte+'&numerocliente='+numerocliente+'&claveserie='+claveserie+'&departamento='+departamento+'&saldomayor='+saldomayor+'&reporte='+reporte);
        $("#btnGenerarFormatoReportePdf").click();
    }else{
        form.parsley().validate();
    }
}

//listar tabla reporte
function listar(){
    var reporte = $("#reporte").val();
    switch(reporte){
        case "GENERAL":
            var columnas = new Array("Cliente", "NombreCliente", "Facturado", "AbonosCXC", "DescuentosNotasCredito", "TotalPagos", "SaldoFacturado");
            break;
        case "DETALLES":
            var columnas = new Array("Factura", "Fecha", "Plazo", "Cliente", "NombreCliente", "TotalFactura", "AbonosCXC", "DescuentosNotasCredito", "TotalPagos", "SaldoFacturado");
            break;
    }
    if( $('#idsaldomayor').prop('checked') ) {
        var saldomayor = 1;
    }else{
        saldomayor = 0;
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
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: reporte_antiguedad_saldos_generar_reporte,
            method: 'POST',
            data: function (d) {
                d.numerocliente = $("#numerocliente").val();
                d.claveserie = $("#claveserie").val();
                d.fechacorte = $("#fechacorte").val();
                d.departamento = $("#departamento").val();
                d.saldomayor = saldomayor;
                d.reporte = $("#reporte").val();
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
init();