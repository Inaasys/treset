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
    $('#claveserie').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            e.preventDefault();
            obtenerserieporclave();
        }
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
    setTimeout(function(){$("#claveserie").focus();},500);
});
//obtener registros de almacenes
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
            url: reporte_relacion_timbres_utilizados_obtener_series,
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
function obtenerserieporclave(){
    var claveserieanterior = $("#claveserieanterior").val();
    var claveserie = $("#claveserie").val();
    if(claveserieanterior != claveserie){
        if($("#claveserie").parsley().isValid()){
            $.get(reporte_relacion_timbres_utilizados_obtener_serie_por_clave, {claveserie:claveserie}, function(data){
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
        var claveserie = $("#claveserie").val();
        var fechainicialreporte = $("#fechainicialreporte").val();
        var fechafinalreporte = $("#fechafinalreporte").val();
        var tipocomprobante = $("#tipocomprobante").val();
        var reporte = $("#reporte").val();
        $("#btnGenerarFormatoReporteExcel").attr("href", urlgenerarformatoexcel+'?fechainicialreporte='+fechainicialreporte+'&fechafinalreporte='+fechafinalreporte+'&claveserie='+claveserie+'&tipocomprobante='+tipocomprobante+'&reporte='+reporte);
        $("#btnGenerarFormatoReporteExcel").click();
    }else{
        form.parsley().validate();
    }
}
//listar tabla reporte
function listar(){
    let comprobantesArray = []
    var reporte = $("#reporte").val();
    if(reporte == 'GENERAL'){
        var columnas = new Array("Comprobante", "Tipo", "Serie", "Folio", 'TotalSistema', 'TotalCFDI' ,"UUID", "EmisorRfc", "ReceptorRfc", "FormaPago", "MetodoPago", "UsoCfdi");
    }else{
        var columnas = new Array("Total");
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
            url: reporte_relacion_timbres_utilizados_generar_reporte,
            method: 'POST',
            data: function (d) {
                d.claveserie = $("#claveserie").val();
                d.fechainicialreporte = $("#fechainicialreporte").val();
                d.fechafinalreporte = $("#fechafinalreporte").val();
                d.tipocomprobante = $("#tipocomprobante").val();
                d.reporte = $("#reporte").val();
            }
        },
        "createdRow": function( row, data, dataIndex){
            if (data.Tipo == "I") {
                if( (data.TotalSistema <  data.TotalCFDI) || (data.TotalSistema >  data.TotalCFDI) ){
                    if ( data.Status != "BAJA" ) {
                        $(row).addClass('bg-red');
                    }
                }
            }
            comprobantesArray.push(data)
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
            for (let i = 0; i < comprobantesArray.length; i++) {
                if(i < comprobantesArray.length -1){
                    if((comprobantesArray[i].Serie == comprobantesArray[i+1].Serie) && comprobantesArray[i].Folio == comprobantesArray[i+1].Folio){
                        toastr.error( "Aviso, El siguiente serie y folio se encuentra duplicado "
                        + comprobantesArray[i+1].Folio+'-'+comprobantesArray[i+1].Serie, "Mensaje", {
                            "timeOut": "6000",
                            "progressBar": true,
                            "extendedTImeout": "6000"
                        });
                    }
                }
            }
        }
    });

}

init();
