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
//validar fechas incio y final de reporte
function validafechas(){
    var fechainicialreporte = $('#fechainicialreporte').val();
    var fechafinalreporte = $('#fechafinalreporte').val();
    var fechahoy = new Date();
    var dia = ("0" + fechahoy.getDate()).slice(-2);
    var mes = ("0" + (fechahoy.getMonth() + 1)).slice(-2);
    var hoy = fechahoy.getFullYear()+"-"+(mes)+"-"+(dia);
    if(fechafinalreporte > hoy){
        var msj = 'fechafinalmayorahoy';
    }else if(fechainicialreporte > fechafinalreporte){
        var msj ='fechainicialmayorafechafinal';
    }else{
        var msj ='ok';
    }
    return msj;
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
    setTimeout(function(){$("#numerocliente").focus();},500);
});
//obtener tipos ordenes de compra
function obtenertiposordenescompra(){
    $.get(reporte_facturas_ventas_cliente_obtener_tipos_ordenes_compra, function(select_tipos_ordenes_compra){
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
                url: reporte_facturas_ventas_cliente_obtener_clientes
            },
            columns: [
                { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
                { data: 'Numero', name: 'Numero' },
                { data: 'Nombre', name: 'Nombre' }
            ],
            "initComplete": function() {
                var $buscar = $('div.dataTables_filter input');
                $buscar.focus();
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
            url: reporte_facturas_ventas_cliente_obtener_agentes,
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
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
            url: reporte_facturas_ventas_cliente_obtener_series,
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Serie', name: 'Serie' },
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
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
            $.get(reporte_facturas_ventas_cliente_obtener_cliente_por_numero, {numerocliente:numerocliente}, function(data){
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
            $.get(reporte_facturas_ventas_cliente_obtener_agente_por_numero, {numeroagente:numeroagente}, function(data){
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
            $.get(reporte_facturas_ventas_cliente_obtener_serie_por_clave, {claveserie:claveserie}, function(data){
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
        var result = validafechas();
        if(result == 'fechafinalmayorahoy'){
            msjfechafinalmayorahoy();
            $('#fechafinalreporte').val("");
        }else if(result == 'fechainicialmayorafechafinal'){
            msjfechainicialmayorafechafinal();
            $('#fechainicialreporte').val("");
        }else if(result == 'ok'){
            $('#tbllistado').DataTable().clear().destroy();
            listar();
            //var tabla = $('.tbllistado').DataTable();
            //tabla.ajax.reload();
        }
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
        var claveserie = $("#claveserie").val();
        var tipo = $("#tipo").val();
        var departamento = $("#departamento").val();
        var documentos = $("#documentos").val();
        var status = $("#status").val();
        var reporte = $("#reporte").val();
        $("#btnGenerarFormatoReporteExcel").attr("href", urlgenerarformatoexcel+'?fechainicialreporte='+fechainicialreporte+'&fechafinalreporte='+fechafinalreporte+'&numerocliente='+numerocliente+'&numeroagente='+numeroagente+'&claveserie='+claveserie+'&tipo='+tipo+'&departamento='+departamento+'&documentos='+documentos+'&status='+status+'&reporte='+reporte);
        $("#btnGenerarFormatoReporteExcel").click();
    }else{
        form.parsley().validate();
    }
}
//listar tabla reporte
function listar(){
    var reporte = $("#reporte").val();
    switch(reporte){
        case "UTILIDAD":
            break;
        case "GENERAL":
            var columnas = new Array("Factura", "UUID", "Serie", "Folio", "Depto", "Tipo", "Cliente", "NombreCliente", "Agente", "NombreAgente", "Fecha", "Plazo", "Pedido", "Importe", "Descuento", "SubTotal", "Iva", "Total", "Abonos", "Descuentos", "Saldo", "Costo", "Utilidad", "Moneda", "TipoCambio", "Obs", "Status", "MotivoBaja", "Usuario");
            break;
        case "PRODUCTOS":
            var columnas = new Array("Factura", "Fecha", "Cliente", "NombreCliente", "Agente", "Tipo", "NombreAgente", "Plazo", "Codigo", "Descripcion", "Unidad", "Cantidad", "Precio", "Importe", "Dcto", "Descuento", "SubTotal", "Impuesto", "Iva", "Total", "Costo", "CostoTotal", "Utilidad", "Facturar", "Remision", "Orden", "Departamento", "Cargo", "Almacen", "Partida", "Item");
            break;
        case "VENTAS":
            break;
        case "PAGOS":
            break;
        case "FACTURAS":
            break;
        case "RESUMEN":
            var columnas = new Array("Cliente", "Nombre", "Importe", "Descuento", 'SubTotal', 'Iva', 'Total', 'Costo', 'Utilidad', 'PorcentajeUtilidad');
            break;
        case "MENSUAL":
            var columnas = new Array("Cliente", "NombreCliente", "SubTotal", "Utilidad");
            break;
        case "POTENCIALES":
            var columnas = new Array("Numero", "Nombre", "Plazo", "Credito", "Bloquear", "Saldo", "TotalFacturas");
            break;
        case "COMPARATIVO MENSUAL":
            var columnas = new Array("Cliente", "NombreCliente");
            var todaslasfechas = new Array();
            var fechaInicio = new Date($("#fechainicialreporte").val());
            var fechaFin    = new Date($("#fechafinalreporte").val()); 
            while(fechaFin.getTime() >= fechaInicio.getTime()){
                fechaInicio.setDate(fechaInicio.getDate() + 1);
                var fecha = fechaInicio.getFullYear() + '-' + ('0' + (fechaInicio.getMonth()+1)).slice(-2);
                if(todaslasfechas.indexOf(fecha) == -1){
                    todaslasfechas.push(fecha);
                    columnas.push("SubTotal"+fecha);
                    columnas.push("Utilidad"+fecha);
                    columnas.push("PorcentajeUtilidad"+fecha);
                }
            }
            break;
        case "COMPARATIVO ANUAL":
            var columnas = new Array("Cliente", "NombreCliente");
            var todaslasfechas = new Array();
            var fechaInicio = new Date($("#fechainicialreporte").val());
            var fechaFin    = new Date($("#fechafinalreporte").val()); 
            while(fechaFin.getTime() >= fechaInicio.getTime()){
                fechaInicio.setDate(fechaInicio.getDate() + 1);
                var fecha = fechaInicio.getFullYear();
                if(todaslasfechas.indexOf(fecha) == -1){
                    todaslasfechas.push(fecha);
                    columnas.push("Facturado"+fecha);
                }
            }
            break;
        case "NOTAS DE CREDITO":
            var columnas = new Array("Comprobante", "Documento", "Fecha", "SubTotal", "Iva", "Total");
            break;
        case "NO FACTURADOS":
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
        keys: true,
        /*
        "columnDefs": [{
            "targets": [2],
            "render": function(data, type, row) {
                return Number(data).toLocaleString('en-US', {
                    maximumFractionDigits: numerodecimales,
                    style: 'currency',
                    currency: 'USD'
                });
            }
        }],
        */
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
            url: reporte_facturas_ventas_cliente_generar_reporte,
            method: 'POST',
            data: function (d) {
                d.numerocliente = $("#numerocliente").val();
                d.numeroagente = $("#numeroagente").val();
                d.claveserie = $("#claveserie").val();
                d.fechainicialreporte = $("#fechainicialreporte").val();
                d.fechafinalreporte = $("#fechafinalreporte").val();
                d.tipo = $("#tipo").val();
                d.departamento = $("#departamento").val();
                d.documentos = $("#documentos").val();
                d.status = $("#status").val();
                d.reporte = $("#reporte").val();
            }
        },
        columns: campos_tabla,
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
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