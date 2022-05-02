'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
  asignarfechaactual(); 
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
    $('#numerobanco').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerbancopornumero();
            e.preventDefault();
        }
    });
    //activar busqueda
    $('#claveformapago').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerformapagoporclave();
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
    $('#numerobanco').on('change', function(e) {
        regresarnumerobanco();
    });
    //regresar numero
    $('#claveformapago').on('change', function(e) {
        regresarclaveformapago();
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
                url: reporte_relacion_cuentasporcobrar_obtener_clientes
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
            url: reporte_relacion_cuentasporcobrar_obtener_agentes,
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
//obtener registros de bancos
function obtenerbancos(){
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablabancos = '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Bancos</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadobanco" class="tbllistadobanco table table-bordered table-striped table-hover" style="width:100% !important">'+
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
    $("#contenidomodaltablas").html(tablabancos);
    $('#tbllistadobanco').DataTable({
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
            url: reporte_relacion_cuentasporcobrar_obtener_bancos,
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
                  $('#tbllistadobanco').DataTable().search( this.value ).draw();
                }
            });
        },  
    }); 
} 
//obtener registros de formas pago
function obtenerformaspago(){ 
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablaformaspago ='<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Formas Pago</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive ">'+
                                        '<table id="tbllistadoformapago" class="tbllistadoformapago table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
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
            url: reporte_relacion_cuentasporcobrar_obtener_formaspago,
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Clave', name: 'Clave' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Descripcion', name: 'Descripcion' }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                    $('#tbllistadoformapago').DataTable().search( this.value ).draw();
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
            $("#textonombreagente").attr('style', 'font-size:8px').html(Nombre.substring(0, 45));
        }
        generar_reporte();
        $("#ModalFormulario").modal('hide');
    }
}
function seleccionarbanco(Numero, Nombre){
    var numerobancoanterior = $("#numerobancoanterior").val();
    var numerobanco = Numero;
    if(numerobancoanterior != numerobanco){
        $("#numerobanco").val(Numero);
        $("#numerobancoanterior").val(Numero);
        $("#banco").val(Nombre);
        if(Nombre != null){
            $("#textonombrebanco").attr('style', 'font-size:8px').html(Nombre.substring(0, 45));
        }
        generar_reporte();
        $("#ModalFormulario").modal('hide');
    }
}
function seleccionarformapago(Clave, Nombre){
    var claveformapagoanterior = $("#claveformapagoanterior").val();
    var claveformapago = Clave;
    if(claveformapagoanterior != claveformapago){
        $("#claveformapago").val(Clave);
        $("#claveformapagoanterior").val(Clave);
        $("#formapago").val(Nombre);
        if(Nombre != null){
            $("#textonombreformapago").attr('style', 'font-size:8px').html(Nombre.substring(0, 45));
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
            $.get(reporte_relacion_cuentasporcobrar_obtener_cliente_por_numero, {numerocliente:numerocliente}, function(data){
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
            $.get(reporte_relacion_cuentasporcobrar_obtener_agente_por_numero, {numeroagente:numeroagente}, function(data){
                $("#numeroagente").val(data.numero);
                $("#numeroagenteanterior").val(data.numero);
                $("#almacen").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombreagente").attr('style', 'font-size:8px').html(data.nombre.substring(0, 45));
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
function obtenerbancopornumero(){
    var numerobancoanterior = $("#numerobancoanterior").val();
    var numerobanco = $("#numerobanco").val();
    if(numerobancoanterior != numerobanco){
        if($("#numerobanco").parsley().isValid()){
            $.get(reporte_relacion_cuentasporcobrar_obtener_banco_por_numero, {numerobanco:numerobanco}, function(data){
                $("#numerobanco").val(data.numero);
                $("#numerobancoanterior").val(data.numero);
                $("#banco").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombrebanco").attr('style', 'font-size:8px').html(data.nombre.substring(0, 45));
                }
                generar_reporte();
            }) 
        }
    }
}
//regresar numero
function regresarnumerobanco(){
    var numerobancoanterior = $("#numerobancoanterior").val();
    $("#numerobanco").val(numerobancoanterior);
}
//obtener por numero
function obtenerformapagoporclave(){
    var claveformapagoanterior = $("#claveformapagoanterior").val();
    var claveformapago = $("#claveformapago").val();
    if(claveformapagoanterior != claveformapago){
        if($("#claveformapago").parsley().isValid()){
            $.get(reporte_relacion_cuentasporcobrar_obtener_formapago_por_clave, {claveformapago:claveformapago}, function(data){
                $("#claveformapago").val(data.clave);
                $("#claveformapagoanterior").val(data.clave);
                $("#formapago").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombreformapago").attr('style', 'font-size:8px').html(data.nombre.substring(0, 45));
                }
                generar_reporte();
            })  
        }
    }
}
//regresar numero
function regresarclaveformapago(){
    var claveformapagoanterior = $("#claveformapagoanterior").val();
    $("#claveformapago").val(claveformapagoanterior);
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
        var reporte = $("#reporte").val();
        var numerocliente = $("#numerocliente").val();
        var numeroagente = $("#numeroagente").val();
        var numerobanco = $("#numerobanco").val();
        var claveformapago = $("#claveformapago").val();
        $("#btnGenerarFormatoReporteExcel").attr("href", urlgenerarformatoexcel+'?fechainicialreporte='+fechainicialreporte+'&fechafinalreporte='+fechafinalreporte+'&numerocliente='+numerocliente+'&numeroagente='+numeroagente+'&numerobanco='+numerobanco+'&claveformapago='+claveformapago+'&reporte='+reporte);
        $("#btnGenerarFormatoReporteExcel").click();
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
        var reporte = $("#reporte").val();
        var numerocliente = $("#numerocliente").val();
        var numeroagente = $("#numeroagente").val();
        var numerobanco = $("#numerobanco").val();
        var claveformapago = $("#claveformapago").val();
        $("#btnGenerarFormatoReportePdf").attr("href", urlgenerarformatopdf+'?fechainicialreporte='+fechainicialreporte+'&fechafinalreporte='+fechafinalreporte+'&numerocliente='+numerocliente+'&numeroagente='+numeroagente+'&numerobanco='+numerobanco+'&claveformapago='+claveformapago+'&reporte='+reporte);
        $("#btnGenerarFormatoReportePdf").click();
    }else{
        form.parsley().validate();
    }
}
//listar tabla reporte
function listar(){
    var reporte = $("#reporte").val();
    switch(reporte){
        case "AGRUPARxCLIENTES":
            var columnas = new Array("Pago", "Fecha", "Cliente", "FormaPago", "Banco", "Factura", "Agente", "NombreAgente", "Total", "Abono", "Saldo", "SubTotal", "Utilidad", "Anotacion", "MotivoBaja", "Status");
            break;
        case "AGRUPARxAGENTES":
            var columnas = new Array("Pago", "Fecha", "Cliente", "FormaPago", "Banco", "Factura", "Agente", "NombreAgente", "Total", "Abono", "Saldo", "SubTotal", "Utilidad", "Anotacion", "MotivoBaja", "Status");
            break;
        case "AGRUPARxFORMADEPAGO":
            var columnas = new Array("Pago", "Fecha", "Cliente", "FormaPago", "Banco", "Factura", "Agente", "NombreAgente", "Total", "Abono", "Saldo", "SubTotal", "Utilidad", "Anotacion", "MotivoBaja", "Status");
            break;
        case "AGRUPARxBANCO":
            var columnas = new Array("Pago", "Fecha", "Cliente", "FormaPago", "Banco", "Factura", "Agente", "NombreAgente", "Total", "Abono", "Saldo", "SubTotal", "Utilidad", "Anotacion", "MotivoBaja", "Status");
            break;
        case "RELACIONDEPAGOS":
            var columnas = new Array("Pago", "Fecha", "Cliente", "FormaPago", "Banco", "Abono", "Anotacion", "MotivoBaja", "Status");
            break;
        case "COMISIONAAGENTES":
            var columnas = new Array("Factura", "FechaFactura", "NombreCliente", "MontoFactura", "NombreAgente", "Pago", "FechaPago", "Dias", "Abono", "Comision", "ComisionPesos", "FormaPago");
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
            url: reporte_relacion_cuentasporcobrar_generar_reporte,
            method: 'POST',
            data: function (d) {
                d.numerocliente = $("#numerocliente").val();
                d.numeroagente = $("#numeroagente").val();
                d.numerobanco = $("#numerobanco").val();
                d.claveformapago = $("#claveformapago").val();
                d.fechainicialreporte = $("#fechainicialreporte").val();
                d.fechafinalreporte = $("#fechafinalreporte").val();
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