'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
  asignarfechaactual(); 
  obtenertiposordenescompra();
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
  $('#fechafinalreporte').val(hoy);
  $('#fechainicialreporte').val(hoy);
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
    $('#numeroagente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obteneragentepornumero();
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
    //activar busqueda
    $('#numeroalmacen').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obteneralmacenpornumero();
            e.preventDefault();
        }
    });
    //activar busqueda
    $('#numeromarca').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenermarcapornumero();
            e.preventDefault();
        }
    });
    //activar busqueda
    $('#numerolinea').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerlineapornumero();
            e.preventDefault();
        }
    });
    //activar busqueda
    $('#codigo').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerproductoporcodigo();
            e.preventDefault();
        }
    });
    //activar busqueda
    $('#buscarenajuste').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            generar_reporte();
            e.preventDefault();
        }
    });
    //regresar numero
    $('#numerocliente').on('change', function(e) {
        regresarnumerocliente();
    });
    //regresar numero
    $('#numeroagente').on('change', function(e) {
        regresarnumeroagente();
    });
    //regresar numero
    $('#claveserie').on('change', function(e) {
        regresarclaveserie();
    });
    //regresar numero
    $('#numeroalmacen').on('change', function(e) {
        regresarnumeroalmacen();
    });
    //regresar numero
    $('#numeromarca').on('change', function(e) {
        regresarnumeromarca();
    });
    //regresar numero
    $('#numerolinea').on('change', function(e) {
        regresarnumerolinea();
    });
    //regresar numero
    $('#codigo').on('change', function(e) {
        regresarcodigoproducto();
    });
    //cargar reporte al dar enter en las fechas
    //activar busqueda
    $('#fechainicialreporte').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            generar_reporte();
            e.preventDefault();
        }
    });
    //activar busqueda
    $('#fechafinalreporte').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            generar_reporte();
            e.preventDefault();
        }
    });
});
//obtener tipos ordenes de compra
function obtenertiposordenescompra(){
    $.get(reporte_productos_mas_vendidos_obtener_tipos_ordenes_compra, function(select_tipos_ordenes_compra){
      $("#tipo").html(select_tipos_ordenes_compra);
    })  
}
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
                url: reporte_productos_mas_vendidos_obtener_clientes
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
//obtener registros de agentes
function obteneragentes(){ 
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablaagentes ='<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Agentes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive ">'+
                                        '<table id="tbllistadoagente" class="tbllistadoagente table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Agente</th>'+
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
            url: reporte_productos_mas_vendidos_obtener_agentes,
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
                    $('#tbllistadoagente').DataTable().search( this.value ).draw();
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
            url: reporte_productos_mas_vendidos_obtener_series,
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
function seleccionaragente(Numero, Nombre){
    var numeroagenteanterior = $("#numeroagenteanterior").val();
    var numeroagente = Numero;
    if(numeroagenteanterior != numeroagente){
        $("#numeroagente").val(Numero);
        $("#numeroagenteanterior").val(Numero);
        $("#agente").val(Nombre);
        if(Nombre != null){
            $("#textonombreagente").attr('style', 'font-size:8px').html(Nombre.substring(0, 25));
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
            $.get(reporte_productos_mas_vendidos_obtener_cliente_por_numero, {numerocliente:numerocliente}, function(data){
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
function obteneragentepornumero(){
    var numeroagenteanterior = $("#numeroagenteanterior").val();
    var numeroagente = $("#numeroagente").val();
    if(numeroagenteanterior != numeroagente){
        if($("#numeroagente").parsley().isValid()){
            $.get(reporte_productos_mas_vendidos_obtener_agente_por_numero, {numeroagente:numeroagente}, function(data){
                $("#numeroagente").val(data.numero);
                $("#numeroagenteanterior").val(data.numero);
                $("#almacen").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombreagente").attr('style', 'font-size:8px').html(data.nombre.substring(0, 25));
                }
                generar_reporte();
            })  
        }
    }
}
//regresar numero
function regresarnumeroagente(){
    var numeroagenteanterior = $("#numeroagenteanterior").val();
    $("#numeroagente").val(numeroagenteanterior);
}
//obtener por numero
function obtenerserieporclave(){
    var claveserieanterior = $("#claveserieanterior").val();
    var claveserie = $("#claveserie").val();
    if(claveserieanterior != claveserie){
        if($("#claveserie").parsley().isValid()){
            $.get(reporte_productos_mas_vendidos_obtener_serie_por_clave, {claveserie:claveserie}, function(data){
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
//obtener registros de almacenes
function obteneralmacenes(){ 
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablaalmacenes ='<div class="modal-header '+background_forms_and_modals+'">'+
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
    var talm = $('#tbllistadoalmacen').DataTable({
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
            url: reporte_productos_mas_vendidos_obtener_almacenes,
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
    //seleccion al dar doble click
    $('#tbllistadoalmacen tbody').on('dblclick', 'tr', function () {
      var data = talm.row( this ).data();
      seleccionaralmacen(data.Numero, data.Nombre);
    });
} 
function seleccionaralmacen(Numero, Nombre){
    var numeroalmacenanterior = $("#numeroalmacenanterior").val();
    var numeroalmacen = Numero;
    if(numeroalmacenanterior != numeroalmacen){
        $("#numeroalmacen").val(Numero);
        $("#numeroalmacenanterior").val(Numero);
        $("#almacen").val(Nombre);
        if(Nombre != null){
            $("#textonombrealmacen").attr('style', 'font-size:8px').html(Nombre.substring(0, 45));
        }
        generar_reporte();
        $("#ModalFormulario").modal('hide');
    }
}
//obtener por numero
function obteneralmacenpornumero(){
    var numeroalmacenanterior = $("#numeroalmacenanterior").val();
    var numeroalmacen = $("#numeroalmacen").val();
    if(numeroalmacenanterior != numeroalmacen){
        if($("#numeroalmacen").parsley().isValid()){
            $.get(reporte_productos_mas_vendidos_obtener_almacen_por_numero, {numeroalmacen:numeroalmacen}, function(data){
                $("#numeroalmacen").val(data.numero);
                $("#numeroalmacenanterior").val(data.numero);
                $("#almacen").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombrealmacen").attr('style', 'font-size:8px').html(data.nombre.substring(0, 45));
                }
                generar_reporte();
            })  
        }
    }
}
//regresar numero
function regresarnumeroalmacen(){
    var numeroalmacenanterior = $("#numeroalmacenanterior").val();
    $("#numeroalmacen").val(numeroalmacenanterior);
}
//obtener registros de marcas
function obtenermarcas(){
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablamarcas = '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Marcas</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadomarca" class="tbllistadomarca table table-bordered table-striped table-hover" style="width:100% !important">'+
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
    $("#contenidomodaltablas").html(tablamarcas);
    var tmar = $('#tbllistadomarca').DataTable({
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
            url: reporte_productos_mas_vendidos_obtener_marcas,
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' },
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadomarca').DataTable().search( this.value ).draw();
                }
            });
        },  
    }); 
    //seleccion al dar doble click
    $('#tbllistadomarca tbody').on('dblclick', 'tr', function () {
      var data = tmar.row( this ).data();
      seleccionarmarca(data.Numero, data.Nombre);
    });
} 
//obtener registros de lineas
function obtenerlineas(){ 
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablalineas ='<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Lineas</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive ">'+
                                        '<table id="tbllistadolinea" class="tbllistadolinea table table-bordered table-striped table-hover" style="width:100% !important">'+
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
    $("#contenidomodaltablas").html(tablalineas);
    var tlin = $('#tbllistadolinea').DataTable({
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
            url: reporte_productos_mas_vendidos_obtener_lineas,
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
                    $('#tbllistadolinea').DataTable().search( this.value ).draw();
                }
            });
        },    
    }); 
    //seleccion al dar doble click
    $('#tbllistadolinea tbody').on('dblclick', 'tr', function () {
      var data = tlin.row( this ).data();
      seleccionarlinea(data.Numero, data.Nombre);
    });
} 
function seleccionarmarca(Numero, Nombre){
    var numeromarcaanterior = $("#numeromarcaanterior").val();
    var numeromarca = Numero;
    if(numeromarcaanterior != numeromarca){
        $("#numeromarca").val(Numero);
        $("#numeromarcaanterior").val(Numero);
        $("#marca").val(Nombre);
        if(Nombre != null){
            $("#textonombremarca").attr('style', 'font-size:8px').html(Nombre.substring(0, 45));
        }
        generar_reporte();
        $("#ModalFormulario").modal('hide');
    }
}
function seleccionarlinea(Numero, Nombre){
    var numerolineaanterior = $("#numerolineaanterior").val();
    var numerolinea = Numero;
    if(numerolineaanterior != numerolinea){
        $("#numerolinea").val(Numero);
        $("#numerolineaanterior").val(Numero);
        $("#linea").val(Nombre);
        if(Nombre != null){
            $("#textonombrelinea").attr('style', 'font-size:8px').html(Nombre.substring(0, 45));
        }
        generar_reporte();
        $("#ModalFormulario").modal('hide');
    }
}
//obtener por numero
function obtenermarcapornumero(){
    var numeromarcaanterior = $("#numeromarcaanterior").val();
    var numeromarca = $("#numeromarca").val();
    if(numeromarcaanterior != numeromarca){
        if($("#numeromarca").parsley().isValid()){
            $.get(reporte_productos_mas_vendidos_obtener_marca_por_numero, {numeromarca:numeromarca}, function(data){
                $("#numeromarca").val(data.numero);
                $("#numeromarcaanterior").val(data.numero);
                $("#marca").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombremarca").attr('style', 'font-size:8px').html(data.nombre.substring(0, 45));
                }
                generar_reporte();
            }) 
        }
    }
}
//regresar numero
function regresarnumeromarca(){
    var numeromarcaanterior = $("#numeromarcaanterior").val();
    $("#numeromarca").val(numeromarcaanterior);
}
//obtener por numero
function obtenerlineapornumero(){
    var numerolineaanterior = $("#numerolineaanterior").val();
    var numerolinea = $("#numerolinea").val();
    if(numerolineaanterior != numerolinea){
        if($("#numerolinea").parsley().isValid()){
            $.get(reporte_productos_mas_vendidos_obtener_linea_por_numero, {numerolinea:numerolinea}, function(data){
                $("#numerolinea").val(data.numero);
                $("#numerolineaanterior").val(data.numero);
                $("#linea").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombrelinea").attr('style', 'font-size:8px').html(data.nombre.substring(0, 45));
                }
                generar_reporte();
            })  
        }
    }
}
//regresar numero
function regresarnumerolinea(){
    var numerolineaanterior = $("#numerolineaanterior").val();
    $("#numerolinea").val(numerolineaanterior);
}
//obtener registros de lineas
function obtenerproductos(){ 
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablaproductos ='<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Productos</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive ">'+
                                        '<table id="tbllistadoproducto" class="tbllistadoproducto table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Codigo</th>'+
                                                    '<th>Producto</th>'+
                                                    '<th>Marca</th>'+
                                                    '<th>Ubicacion</th>'+
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
    var tproduc = $('#tbllistadoproducto').DataTable({
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
            url: reporte_productos_mas_vendidos_obtener_productos,
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Codigo', name: 'Codigo' },
            { data: 'Producto', name: 'Producto' },
            { data: 'Marca', name: 'Marca' },
            { data: 'Ubicacion', name: 'Ubicacion' }
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
    //seleccion al dar doble click
    $('#tbllistadoproducto tbody').on('dblclick', 'tr', function () {
      var data = tproduc.row( this ).data();
      seleccionarproducto(data.Codigo, data.Producto);
    });
} 
function seleccionarproducto(Codigo, Producto){
    var codigoanterior = $("#codigoanterior").val();
    var codigo = Codigo;
    if(codigoanterior != codigo){
        $("#codigo").val(Codigo);
        $("#codigoanterior").val(Codigo);
        $("#producto").val(Producto);
        if(Producto != null){
            $("#textonombreproducto").attr('style', 'font-size:8px').html(Producto.substring(0, 45));
        }
        generar_reporte();
        $("#ModalFormulario").modal('hide');
    }
}
//obtener por numero
function obtenerproductoporcodigo(){
    var codigoanterior = $("#codigoanterior").val();
    var codigo = $("#codigo").val();
    if(codigoanterior != codigo){
        if($("#codigo").parsley().isValid()){
            $.get(reporte_productos_mas_vendidos_obtener_producto_por_codigo, {codigo:codigo}, function(data){
                console.log(data);
                $("#codigo").val(data.codigo);
                $("#codigoanterior").val(data.codigo);
                $("#producto").val(data.producto);
                if(data.producto != null){
                    $("#textonombreproducto").attr('style', 'font-size:8px').html(data.producto.substring(0, 45));
                }
                generar_reporte();
            })  
        }
    }
}
//regresar numero
function regresarcodigoproducto(){
    var codigoanterior = $("#codigoanterior").val();
    $("#codigo").val(codigoanterior);
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
        var fechainicialreporte = $("#fechainicialreporte").val();
        var fechafinalreporte = $("#fechafinalreporte").val();
        var numerocliente = $("#numerocliente").val();
        var numeroagente = $("#numeroagente").val();
        var numeromarca = $("#numeromarca").val();
        var numerolinea = $("#numerolinea").val();
        var numeroalmacen = $("#numeroalmacen").val();
        var codigo = $("#codigo").val();
        var claveserie = $("#claveserie").val();
        var tipo = $("#tipo").val();
        var departamento = $("#departamento").val();
        var documentos = $("#documentos").val();
        var status = $("#status").val();
        var reporte = $("#reporte").val();
        var buscarenajuste = $("#buscarenajuste").val();
        if( $('#idordenarportotal').prop('checked') ) {
            var ordenarportotal = "Total";
        }else{
            var ordenarportotal = "Cantidad";
        }
        if( $('#idresumen').prop('checked') ) {
            var resumen = 1;
        }else{
            var resumen = 0;
        }
        $("#btnGenerarFormatoReporteExcel").attr("href", urlgenerarformatoexcel+'?fechainicialreporte='+fechainicialreporte+'&fechafinalreporte='+fechafinalreporte+'&numerocliente='+numerocliente+'&numeroagente='+numeroagente+'&numeromarca='+numeromarca+'&numerolinea='+numerolinea+'&numeroalmacen='+numeroalmacen+'&codigo='+codigo+'&claveserie='+claveserie+'&tipo='+tipo+'&departamento='+departamento+'&documentos='+documentos+'&status='+status+'&reporte='+reporte+'&ordenarportotal='+ordenarportotal+'&resumen='+resumen+'&buscarenajuste='+buscarenajuste);
        $("#btnGenerarFormatoReporteExcel").click();
    }else{
        form.parsley().validate();
    }
}
//listar tabla reporte
function listar(){
    var reporte = $("#reporte").val();
    switch(reporte){
        case "PORMARCAS":
            var columnas = new Array('Cliente', 'Nombre', 'NombreAgente', 'Codigo', 'Producto', 'Unidad', 'Ubicacion', 'Marca', 'NombreMarca', 'Linea', 'NombreLinea', 'Utilidad', 'Cantidad', 'SubTotal', 'Iva', 'Total', 'Almacen', 'FechaUltimaCompra', 'Dias', 'Costo', 'FechaUltimaVenta');
            $("#divbuscarenajuste").hide();
            $("#buscarenajuste").val("");
            break;
        case "PORLINEAS":
            var columnas = new Array('Cliente', 'Nombre', 'NombreAgente', 'Codigo', 'Producto', 'Unidad', 'Ubicacion', 'Marca', 'NombreMarca', 'Linea', 'NombreLinea', 'Utilidad', 'Cantidad', 'SubTotal', 'Iva', 'Total', 'Almacen', 'FechaUltimaCompra', 'Dias', 'Costo', 'FechaUltimaVenta');
            $("#divbuscarenajuste").hide();
            $("#buscarenajuste").val("");
            break;
        case "PORCLIENTES":
            var columnas = new Array('Cliente', 'Nombre', 'NombreAgente', 'Codigo', 'Producto', 'Unidad', 'Ubicacion', 'Marca', 'NombreMarca', 'Linea', 'NombreLinea', 'Utilidad', 'Cantidad', 'SubTotal', 'Iva', 'Total', 'Almacen', 'FechaUltimaCompra', 'Dias', 'Costo', 'FechaUltimaVenta');
            $("#divbuscarenajuste").hide();
            $("#buscarenajuste").val("");
            break;
        case "PORCODIGOS":
            var columnas = new Array('Codigo', 'Producto', 'Unidad', 'Marca', 'Linea', 'Ubicacion', 'Cantidad', 'Costo', 'Venta', 'Almacen', 'Existencias');
            $("#divbuscarenajuste").hide();
            $("#buscarenajuste").val("");
            break;
        case "CRUCEAJUSTE":
            var columnas = new Array('Codigo', 'Producto', 'Marca', 'Linea', 'Almacen', 'CantidadDelAjuste', 'MasVendidas');
            $("#divbuscarenajuste").show();
            break;
    }
    if( $('#idresumen').prop('checked') ) {
        var resumen = 1;
    }else{
        var resumen = 0;
    }
    if( $('#idordenarportotal').prop('checked') ) {
        var ordenarportotal = "Total";
    }else{
        var ordenarportotal = "Cantidad";
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
            url: reporte_productos_mas_vendidos_generar_reporte,
            method: 'POST',
            data: function (d) {
                d.numerocliente = $("#numerocliente").val();
                d.numeroagente = $("#numeroagente").val();
                d.numeromarca = $("#numeromarca").val();
                d.numerolinea = $("#numerolinea").val();
                d.numeroalmacen = $("#numeroalmacen").val();
                d.codigo = $("#codigo").val();
                d.claveserie = $("#claveserie").val();
                d.fechainicialreporte = $("#fechainicialreporte").val();
                d.fechafinalreporte = $("#fechafinalreporte").val();
                d.tipo = $("#tipo").val();
                d.departamento = $("#departamento").val();
                d.documentos = $("#documentos").val();
                d.status = $("#status").val();
                d.reporte = $("#reporte").val();
                d.buscarenajuste = $("#buscarenajuste").val();
                d.ordenarportotal = ordenarportotal;
                d.resumen = resumen;
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