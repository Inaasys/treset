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
    $('#numeroproveedor').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerproveedorpornumero();
            e.preventDefault();
        }
    });
    //regresar numero
    $('#numeroproveedor').on('change', function(e) {
        regresarnumeroproveedor();
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
    setTimeout(function(){$("#numeroproveedor").focus();},500);
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
    $('#tbllistadoproveedor').DataTable({
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
            url: reporte_relacion_contrarecibos_obtener_proveedores,
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
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoproveedor').DataTable().search( this.value ).draw();
                }
            });
        },  
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
//obtener por numero
function obtenerproveedorpornumero(){
    var numeroproveedoranterior = $("#numeroproveedoranterior").val();
    var numeroproveedor = $("#numeroproveedor").val();
    if(numeroproveedoranterior != numeroproveedor){
        if($("#numeroproveedor").parsley().isValid()){
            $.get(reporte_relacion_contrarecibos_obtener_proveedor_por_numero, {numeroproveedor:numeroproveedor}, function(data){
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
        var numeroproveedor = $("#numeroproveedor").val();
        var fechainicialreporte = $("#fechainicialreporte").val();
        var fechafinalreporte = $("#fechafinalreporte").val();
        var reporte = $("#reporte").val();
        $("#btnGenerarFormatoReporteExcel").attr("href", urlgenerarformatoexcel+'?fechainicialreporte='+fechainicialreporte+'&fechafinalreporte='+fechafinalreporte+'&numeroproveedor='+numeroproveedor+'&reporte='+reporte);
        $("#btnGenerarFormatoReporteExcel").click();
    }else{
        form.parsley().validate();
    }
}
//realizar en reporte en pdf
function generar_formato_pdf(){
    var form = $("#formreporte");
    if (form.parsley().isValid()){
        var numeroproveedor = $("#numeroproveedor").val();
        var fechainicialreporte = $("#fechainicialreporte").val();
        var fechafinalreporte = $("#fechafinalreporte").val();
        var reporte = $("#reporte").val();
        $("#btnGenerarFormatoReportePdf").attr("href", urlgenerarformatopdf+'?numeroproveedor='+numeroproveedor+'&fechainicialreporte='+fechainicialreporte+'&fechafinalreporte='+fechafinalreporte+'&reporte='+reporte);
        $("#btnGenerarFormatoReportePdf").click();
    }else{
        form.parsley().validate();
    }
}
//listar tabla reporte
function listar(){
    var reporte = $("#reporte").val();
    if(reporte == 'GENERAL'){
        var columnas = new Array("ContraRecibo", "Proveedor", "Nombre", "Fecha", "SubTotal", "Iva", "Total", "Status");
    }else{
        var columnas = new Array("ContraRecibo", "Proveedor", "Nombre", "Compra", "Movimiento", "Fecha", "Remision", "Factura", "FechaAPagar", "SubTotal", "Iva", "Total", "Status");
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
            url: reporte_relacion_contrarecibos_generar_reporte,
            method: 'POST',
            data: function (d) {
                d.numeroproveedor = $("#numeroproveedor").val();
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