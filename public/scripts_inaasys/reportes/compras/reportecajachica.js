'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
  asignarfechaactual(); 
  listar();
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
//actualizar reporte
function generar_reporte(){
    var form = $("#formcajachica");
    if (form.parsley().isValid()){
        var result = validafechas();
        if(result == 'fechafinalmayorahoy'){
            msjfechafinalmayorahoy();
            $('#fechafinalreporte').val("");
        }else if(result == 'fechainicialmayorafechafinal'){
            msjfechainicialmayorafechafinal();
            $('#fechainicialreporte').val("");
        }else if(result == 'ok'){
            var tabla = $('.tbllistado').DataTable();
            tabla.ajax.reload();
        }
        //funcion asincrona
        construirarraycheckscompras().then(e=>{}) 
    }else{
        form.parsley().validate();
    }
}
//realizar en reporte en excel
function generar_formato_excel(){
    var form = $("#formcajachica");
    if (form.parsley().isValid()){
        var fechainicialreporte = $("#fechainicialreporte").val();
        var fechafinalreporte = $("#fechafinalreporte").val();
        var statuscompra = $("#statuscompra").val();
        var string_compras = $("#string_compras").val();
        $("#btnGenerarFormatoExcelCajaChica").attr("href", urlgenerarformatoexcelcajachica+'?fechainicialreporte='+fechainicialreporte+'&fechafinalreporte='+fechafinalreporte+'&statuscompra='+statuscompra+'&string_compras='+string_compras);
        $("#btnGenerarFormatoExcelCajaChica").click();
    }else{
        form.parsley().validate();
    }
}
//activar busquedas
$(document).ready(function() {
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
//listar tabla reporte
function listar(){
    tabla=$('#tbllistado').DataTable({
        keys: true,
        "sScrollX": "110%",
        "sScrollY": "390px",
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
            url: generar_reporte_caja_chica,
            data: function (d) {
                d.fechainicialreporte = $("#fechainicialreporte").val();
                d.fechafinalreporte = $("#fechafinalreporte").val();
                d.statuscompra = $("#statuscompra").val();
            }
        },
        columns: [
        { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
        { data: 'fechacompra', name: 'fechacompra', orderable: false, searchable: false },
        { data: 'movimientocompra', name: 'movimientocompra' },
        { data: 'proveedor', name: 'proveedor' },
        { data: 'UUID', name: 'UUID' },
        { data: 'Factura', name: 'Factura' },
        { data: 'conceptopago', name: 'conceptopago' },
        { data: 'observacionescompra', name: 'observacionescompra' },
        { data: 'subtotal', name: 'subtotal'},
        { data: 'iva', name: 'iva'},
        { data: 'ivaretencion', name: 'ivaretencion'},
        { data: 'imphospedaje', name: 'imphospedaje'},
        { data: 'total', name: 'total'},
        { data: 'depto', name: 'depto'}
        ],
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
function construirarraycheckscompras(){
    return new Promise((ejecuta)=>{
        setTimeout(function(){ 
            var string_compras = [];
            var numcomprasseleccionadas = 0;
            var lista = document.getElementsByClassName("checkcompra");
            for (var i = 0; i < lista.length; i++) {
                if(lista[i].checked){
                    string_compras.push(lista[i].value);
                    numcomprasseleccionadas++;
                }
            }
            if(numcomprasseleccionadas > 0){
                $("#string_compras").val(string_compras);
                $("#btnGenerarFormatoExcelCajaChica").show();
            }else{
                $("#string_compras").val(numcomprasseleccionadas);
                $("#btnGenerarFormatoExcelCajaChica").hide();
                msjseleccionaunacompra();
            }
            return ejecuta();
        },500);
    })
}
init();