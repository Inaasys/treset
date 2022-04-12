'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
    asignarfechaactual(); 
    listar();
}
//mostrar modal formulario
function mostrarmodalformulario(){
    $("#ModalFormulario").modal('show');  
  }
  //ocultar modal formulario
  function ocultarmodalformulario(){
    $("#ModalFormulario").modal('hide');
  }
  //mostrar formulario
  function mostrarformulario(){
      $("#ModalFormulario").modal('hide');
      $("#contenidomodaltablas").hide();
      $("#formulario").hide();
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
//obtener registro
function obtenerclientesfacturara(){
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablaclientesfacturaa = '<div class="modal-header '+background_forms_and_modals+'">'+
                              '<h4 class="modal-title">Clientes</h4>'+
                          '</div>'+
                          '<div class="modal-body">'+
                              '<div class="row">'+
                                  '<div class="col-md-12">'+
                                      '<div class="table-responsive">'+
                                          '<table id="tbllistadoclientefacturaa" class="tbllistadoclientefacturaa table table-bordered table-striped table-hover" style="width:100% !important">'+
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
        $("#contenidomodaltablas").html(tablaclientesfacturaa);
        var tclifaca = $('#tbllistadoclientefacturaa').DataTable({
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
                url: reporte_unidades_servicio_obtener_clientes_facturaa
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
                    $('#tbllistadoclientefacturaa').DataTable().search( this.value ).draw();
                    }
                });
            },
        }); 
        //seleccionar registro al dar doble click
        $('#tbllistadoclientefacturaa tbody').on('dblclick', 'tr', function () {
            var data = tclifaca.row( this ).data();
            seleccionarclientefacturaa(data.Numero, data.Nombre);
        }); 
} 
//obtener registros
function obtenerclientesdelcliente(){ 
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablaclientesdelcliente ='<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Clientes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive ">'+
                                        '<table id="tbllistadoclientedelcliente" class="tbllistadoclientedelcliente table table-bordered table-striped table-hover" style="width:100% !important">'+
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
    $("#contenidomodaltablas").html(tablaclientesdelcliente);
    var tclidelcli = $('#tbllistadoclientedelcliente').DataTable({
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
            url: reporte_unidades_servicio_obtener_clientes_delcliente,
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
                    $('#tbllistadoclientedelcliente').DataTable().search( this.value ).draw();
                }
            });
        },    
    }); 
    //seleccionar registro al dar doble click
    $('#tbllistadoclientedelcliente tbody').on('dblclick', 'tr', function () {
        var data = tclidelcli.row( this ).data();
        seleccionarclientedelcliente(data.Numero, data.Nombre);
    }); 
} 
//obtener registros de series
function obtenervines(){ 
    $("#ModalFormulario").modal('show');
    $("#contenidomodaltablas").show();
    $("#formulario").hide();
    var tablavines = '<div class="modal-header '+background_forms_and_modals+'">'+
                                  '<h4 class="modal-title">Vines</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                  '<div class="row">'+
                                      '<div class="col-md-12">'+
                                          '<div class="table-responsive">'+
                                              '<table id="tbllistadovines" class="tbllistadovines table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                  '<thead class="'+background_tables+'">'+
                                                      '<tr>'+
                                                          '<th>Operaciones</th>'+
                                                          '<th>Economico</th>'+
                                                          '<th>Vin</th>'+
                                                          '<th>Placas</th>'+
                                                          '<th>Motor</th>'+
                                                          '<th>Marca</th>'+
                                                          '<th>Modelo</th>'+
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
      $("#contenidomodaltablas").html(tablavines);
      var tvins = $('#tbllistadovines').DataTable({
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
            url: reporte_unidades_servicio_obtener_vines
          },
          columns: [
              { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
              { data: 'Economico', name: 'Economico' },
              { data: 'Vin', name: 'Vin' },
              { data: 'Placas', name: 'Placas', orderable: false, searchable: false },
              { data: 'Motor', name: 'Motor', orderable: false, searchable: false },
              { data: 'Marca', name: 'Marca', orderable: false, searchable: false },
              { data: 'Modelo', name: 'Modelo', orderable: false, searchable: false }
          ],
          "initComplete": function() {
              var $buscar = $('div.dataTables_filter input');
              $buscar.focus();
              $buscar.unbind();
              $buscar.bind('keyup change', function(e) {
                  if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadovines').DataTable().search( this.value ).draw();
                  }
              });
          },
      }); 
      //seleccionar registro al dar doble click
      $('#tbllistadovines tbody').on('dblclick', 'tr', function () {
          var data = tvins.row( this ).data();
          seleccionarvin(data.Economico, data.Vin);
      }); 
} 
function seleccionarclientefacturaa(Numero, Nombre){
    var numeroclienteanteriorfacturara = $("#numeroclienteanteriorfacturara").val();
    var numeroclientefacturara = Numero;
    if(numeroclienteanteriorfacturara != numeroclientefacturara){
        $("#numeroclientefacturara").val(Numero);
        $("#numeroclienteanteriorfacturara").val(Numero);
        $("#clientefacturara").val(Nombre);
        if(Nombre != null){
            $("#textonombreclientefacturara").attr('style', 'font-size:8px').html(Nombre.substring(0, 25));
        }
        $("#numeroclientedelcliente").val("");
        $("#numeroclienteanteriordelcliente").val("");
        $("#clientedelcliente").val("");
        $("#textonombreclientedelcliente").html("");
        generar_reporte();
        $("#ModalFormulario").modal('hide');
    }
}
function seleccionarclientedelcliente(Numero, Nombre){
    var numeroclienteanteriordelcliente = $("#numeroclienteanteriordelcliente").val();
    var numeroclientedelcliente = Numero;
    if(numeroclienteanteriordelcliente != numeroclientedelcliente){
        $("#numeroclientedelcliente").val(Numero);
        $("#numeroclienteanteriordelcliente").val(Numero);
        $("#clientedelcliente").val(Nombre);
        if(Nombre != null){
            $("#textonombreclientedelcliente").attr('style', 'font-size:8px').html(Nombre.substring(0, 25));
        }
        $("#numeroclientefacturara").val("");
        $("#numeroclienteanteriorfacturara").val("");
        $("#clientefacturara").val("");
        $("#textonombreclientefacturara").html("");
        generar_reporte();
        $("#ModalFormulario").modal('hide');
    }
}
function seleccionarvin(Economico, Vin){ 
  var numerovinanterior = $("#numerovinanterior").val();
  var numerovin = Vin;
  if(numerovinanterior != numerovin){
    $("#numerovin").val(Vin);
    $("#numerovinanterior").val(Vin);
    $("#textonombrevin").html(Vin);
    generar_reporte();
    $("#ModalFormulario").modal('hide');
  }
}
//obtener por numero
function obtenerclientefacturaapornumero(){
    var numeroclienteanteriorfacturara = $("#numeroclienteanteriorfacturara").val();
    var numeroclientefacturara = $("#numeroclientefacturara").val();
    if(numeroclienteanteriorfacturara != numeroclientefacturara){
        if($("#numeroclientefacturara").parsley().isValid()){
            $.get(reporte_unidades_servicio_obtener_cliente_facturaa_por_numero, {numeroclientefacturara:numeroclientefacturara}, function(data){
                $("#numeroclientefacturara").val(data.numero);
                $("#numeroclienteanteriorfacturara").val(data.numero);
                $("#clientefacturara").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombreclientefacturara").attr('style', 'font-size:8px').html(data.nombre.substring(0, 25));
                }
                $("#numeroclientedelcliente").val("");
                $("#numeroclienteanteriordelcliente").val("");
                $("#clientedelcliente").val("");
                $("#textonombreclientedelcliente").html("");
                generar_reporte();
            }) 
        }
    }
}
//regresar numero
function regresarnumeroclientefacturaa(){
    var numeroclienteanteriorfacturara = $("#numeroclienteanteriorfacturara").val();
    $("#numeroclientefacturara").val(numeroclienteanteriorfacturara);
}
//obtener por numero
function obtenerclientedelclientepornumero(){
    var numeroclienteanteriordelcliente = $("#numeroclienteanteriordelcliente").val();
    var numeroclientedelcliente = $("#numeroclientedelcliente").val();
    if(numeroclienteanteriordelcliente != numeroclientedelcliente){
        if($("#numeroclientedelcliente").parsley().isValid()){
            $.get(reporte_unidades_servicio_obtener_cliente_delcliente_por_numero, {numeroclientedelcliente:numeroclientedelcliente}, function(data){
                $("#numeroclientedelcliente").val(data.numero);
                $("#numeroclienteanteriordelcliente").val(data.numero);
                $("#clientedelcliente").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombreclientedelcliente").attr('style', 'font-size:8px').html(data.nombre.substring(0, 25));
                }
                $("#numeroclientefacturara").val("");
                $("#numeroclienteanteriorfacturara").val("");
                $("#clientefacturara").val("");
                $("#textonombreclientefacturara").html("");
                generar_reporte();
            }) 
        }
    }
}
//regresar numero
function regresarnumeroclientedelcliente(){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    $("#numerocliente").val(numeroclienteanterior);
}
//obtener por numero
function obtenervinpornumero(){
    var numerovinanterior = $("#numerovinanterior").val();
    var numerovin = $("#numerovin").val();
    if(numerovinanterior != numerovin){
      if($("#numerovin").parsley().isValid()){
        var vin = $("#numerovin").val();
        $.get(reporte_unidades_servicio_obtener_vin_por_clave, {numerovin:numerovin}, function(data){
            $("#numerovin").val(data.vin);
            $("#numerovinanterior").val(data.vin);
            $("#textonombrevin").html(data.vin);
            generar_reporte();
        }) 
      }
    }
}
//regresar numero
function regresarnumerovin(){
    var numerovinanterior = $("#numerovinanterior").val();
    $("#numerovin").val(numerovinanterior);
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
        var numeroclientefacturara = $("#numeroclientefacturara").val();
        var numeroclientedelcliente = $("#numeroclientedelcliente").val();
        var numerovin = $("#numerovin").val();
        var tipoorden = $("#tipoorden").val();
        var tipounidad = $("#tipounidad").val();
        var status = $("#status").val();
        var reporte = $("#reporte").val();
        $("#btnGenerarFormatoExcel").attr("href", urlgenerarformatoexcel+'?fechainicialreporte='+fechainicialreporte+'&fechafinalreporte='+fechafinalreporte+'&numeroclientefacturara='+numeroclientefacturara+'&numeroclientedelcliente='+numeroclientedelcliente+'&numerovin='+numerovin+'&tipoorden='+tipoorden+'&tipounidad='+tipounidad+'&status='+status+'&reporte='+reporte);
        $("#btnGenerarFormatoExcel").click();
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
//activar busquedas
$(document).ready(function() {
    //activar busqueda
    $('#numeroclientefacturara').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclientefacturaapornumero();
            e.preventDefault();
        }
    });
    //activar busqueda
    $('#numeroclientedelcliente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclientedelclientepornumero();
            e.preventDefault();
        }
    });
    //activar busqueda
    $('#numerovin').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenervinpornumero();
            e.preventDefault();
        }
    });
    //regresar numero
    $('#numeroclientefacturara').on('change', function(e) {
        regresarnumeroclientefacturaa();
    });
    //regresar numero
    $('#numeroclientedelcliente').on('change', function(e) {
        regresarnumeroclientedelcliente();
    });
    //regresar numero
    $('#numerovin').on('change', function(e) {
        regresarnumerovin();
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
//listar tabla reporte
function listar(){    
    var reporte = $("#reporte").val();
    switch(reporte){
        case "NORMAL":
            var columnas = new Array('Orden', 'Cliente', 'NombreCliente', 'Fecha', 'Entrega', 'Facturada', 'Status', 'Vin', 'Codigo', 'Descripcion', 'Cantidad', 'Precio', 'Dcto', 'Descuento', 'SubTotal', 'Iva', 'Total', 'Costo', 'Utilidad', 'Cargo', 'Compra', 'Traspaso', 'Kilometros', 'Economico', 'Año', 'Modelo', 'Marca', 'Tecnico1', 'NombreTecnico1', 'Horas1', 'Tecnico2', 'NombreTecnico2', 'Horas2', 'Tecnico3', 'NombreTecnico3', 'Horas3', 'Tecnico4', 'NombreTecnico4', 'Horas4', 'Falla', 'Causa', 'Correccion');
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
        "iDisplayLength": 500,
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: reporte_unidades_servicio_generar_reporte,
            method: 'POST',
            data: function (d) {
                d.fechainicialreporte = $("#fechainicialreporte").val();
                d.fechafinalreporte = $("#fechafinalreporte").val();
                d.numeroclientefacturara = $("#numeroclientefacturara").val();
                d.numeroclientedelcliente = $("#numeroclientedelcliente").val();
                d.numerovin = $("#numerovin").val();
                d.tipoorden = $("#tipoorden").val();
                d.tipounidad = $("#tipounidad").val();
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