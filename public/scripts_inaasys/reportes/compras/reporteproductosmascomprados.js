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
    $('#numeroproveedor').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerproveedorpornumero();
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
    //regresar numero
    $('#numeroproveedor').on('change', function(e) {
        regresarnumeroproveedor();
    });
    //regresar numero
    $('#numeroalmacen').on('change', function(e) {
        regresarnumeroalmacen();
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
    //regresar numero
    $('#numeromarca').on('change', function(e) {
        regresarnumeromarca();
    });
    //regresar numero
    $('#numerolinea').on('change', function(e) {
        regresarnumerolinea();
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
//obtener registros de proveedores
function obtenerproveedores(){
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablaproveedores = '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Proveedores</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoproveedor" class="tbllistadoproveedor table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Numero</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>R.F.C.</th>'+
                                                        '<th>Código Postal</th>'+
                                                        '<th>Teléfonos</th>'+
                                                        '<th>Email</th>'+
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
    $("#contenidomodaltablas").html(tablaproveedores);
    var tprov = $('#tbllistadoproveedor').DataTable({
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
            url: reporte_productos_mas_comprados_obtener_proveedores,
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Rfc', name: 'Rfc', orderable: false, searchable: false },
            { data: 'CodigoPostal', name: 'CodigoPostal', orderable: false, searchable: false },
            { data: 'Telefonos', name: 'Telefonos', orderable: false, searchable: false },
            { data: 'Email1', name: 'Email1', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoproveedor').DataTable().search( this.value ).draw();
                }
            });
        },  
    }); 
    //seleccion al dar doble click
    $('#tbllistadoproveedor tbody').on('dblclick', 'tr', function () {
      var data = tprov.row( this ).data();
      seleccionarproveedor(data.Numero, data.Nombre);
    });
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
            url: reporte_productos_mas_comprados_obtener_almacenes,
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
function seleccionarproveedor(Numero, Nombre){
    var numeroproveedoranterior = $("#numeroproveedoranterior").val();
    var numeroproveedor = Numero;
    if(numeroproveedoranterior != numeroproveedor){
        $("#numeroproveedor").val(Numero);
        $("#numeroproveedoranterior").val(Numero);
        $("#proveedor").val(Nombre);
        if(Nombre != null){
            $("#textonombreproveedor").attr('style', 'font-size:8px').html(Nombre.substring(0, 45));
        }
        generar_reporte();
        $("#ModalFormulario").modal('hide');
    }
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
function obtenerproveedorpornumero(){
    var numeroproveedoranterior = $("#numeroproveedoranterior").val();
    var numeroproveedor = $("#numeroproveedor").val();
    if(numeroproveedoranterior != numeroproveedor){
        if($("#numeroproveedor").parsley().isValid()){
            $.get(reporte_productos_mas_comprados_obtener_proveedor_por_numero, {numeroproveedor:numeroproveedor}, function(data){
                $("#numeroproveedor").val(data.numero);
                $("#numeroproveedoranterior").val(data.numero);
                $("#proveedor").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombreproveedor").attr('style', 'font-size:8px').html(data.nombre.substring(0, 45));
                }
                generar_reporte();
            }) 
        }
    }
}
//regresar numero
function regresarnumeroproveedor(){
    var numeroproveedoranterior = $("#numeroproveedoranterior").val();
    $("#numeroproveedor").val(numeroproveedoranterior);
}
//obtener por numero
function obteneralmacenpornumero(){
    var numeroalmacenanterior = $("#numeroalmacenanterior").val();
    var numeroalmacen = $("#numeroalmacen").val();
    if(numeroalmacenanterior != numeroalmacen){
        if($("#numeroalmacen").parsley().isValid()){
            $.get(reporte_productos_mas_comprados_obtener_almacen_por_numero, {numeroalmacen:numeroalmacen}, function(data){
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
            url: reporte_productos_mas_comprados_obtener_marcas,
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
            url: reporte_productos_mas_comprados_obtener_lineas,
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
            $.get(reporte_productos_mas_comprados_obtener_marca_por_numero, {numeromarca:numeromarca}, function(data){
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
            $.get(reporte_productos_mas_comprados_obtener_linea_por_numero, {numerolinea:numerolinea}, function(data){
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
        var numeroproveedor = $("#numeroproveedor").val();
        var numeroalmacen = $("#numeroalmacen").val();
        var numeromarca = $("#numeromarca").val();
        var numerolinea = $("#numerolinea").val();
        var fechainicialreporte = $("#fechainicialreporte").val();
        var fechafinalreporte = $("#fechafinalreporte").val();
        if( $('#idordenarportotal').prop('checked') ) {
            var ordenarportotal = "Total";
        }else{
            var ordenarportotal = "Cantidad";
        }
        var status = $("#status").val();
        var reporte = $("#reporte").val();
        $("#btnGenerarFormatoExcel").attr("href", urlgenerarformatoexcel+'?fechainicialreporte='+fechainicialreporte+'&fechafinalreporte='+fechafinalreporte+'&numeroproveedor='+numeroproveedor+'&numeroalmacen='+numeroalmacen+'&numeromarca='+numeromarca+'&numerolinea='+numerolinea+'&ordenarportotal='+ordenarportotal+'&status='+status+'&reporte='+reporte);
        $("#btnGenerarFormatoExcel").click();
    }else{
        form.parsley().validate();
    }
}
//listar tabla reporte
function listar(){
    var reporte = $("#reporte").val();
    switch(reporte){
        case "PORMARCAS":
            var columnas = new Array('Proveedor', 'Codigo', 'Producto', 'Unidad', 'Fecha', 'Tipo', 'Almacen', 'Factura', 'Remision', 'Marca', 'Linea', 'Cantidad', 'Precio', 'Importe', 'Dcto', 'Descuento', 'SubTotal', 'Iva', 'Total');
            break;
        case "PORLINEAS":
            var columnas = new Array('Proveedor', 'Codigo', 'Producto', 'Unidad', 'Fecha', 'Tipo', 'Almacen', 'Factura', 'Remision', 'Marca', 'Linea', 'Cantidad', 'Precio', 'Importe', 'Dcto', 'Descuento', 'SubTotal', 'Iva', 'Total');
            break;
        case "PORPROVEEDORES":
            var columnas = new Array('Proveedor', 'Codigo', 'Producto', 'Unidad', 'Fecha', 'Tipo', 'Almacen', 'Factura', 'Remision', 'Marca', 'Linea', 'Cantidad', 'Precio', 'Importe', 'Dcto', 'Descuento', 'SubTotal', 'Iva', 'Total');
            break;
        case "PORCODIGOS":
            var columnas = new Array('Codigo', 'Producto', 'Unidad', 'Marca', 'Linea', 'Ubicacion', 'Cantidad', 'Costo', 'Venta', 'Almacen', 'Existencias');
            break;
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
        "iDisplayLength": 500,//paginacion cada 500 registros
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: reporte_productos_mas_comprados_generar_reporte,
            method: 'POST',
            data: function (d) {
                d.numeroproveedor = $("#numeroproveedor").val();
                d.numeroalmacen = $("#numeroalmacen").val();
                d.numeromarca = $("#numeromarca").val();
                d.numerolinea = $("#numerolinea").val();
                d.fechainicialreporte = $("#fechainicialreporte").val();
                d.fechafinalreporte = $("#fechafinalreporte").val();
                d.status = $("#status").val();
                d.reporte = $("#reporte").val();
                d.ordenarportotal = ordenarportotal;
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