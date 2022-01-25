'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
    campos_a_filtrar_en_busquedas();
    listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 5000));
}
//cerrar modales
function limpiarmodales(){
  $("#tabsform").empty();
}
//limpiar todos los inputs del formulario alta
function limpiar(){
    $("#formparsley")[0].reset();
    //Resetear las validaciones del formulario alta
    form = $("#formparsley");
    form.parsley().reset();
    //volver a aplicar configuracion a datatable principal para que realize la busqueda con la tecla enter
    regresarbusquedadatatableprincipal();
}
//mostrar modal formulario
function mostrarmodalformulario(tipo){
    $("#ModalFormulario").modal('show');
    if(tipo == 'ALTA'){
        $("#btnGuardar").show();
        $("#btnGuardarModificacion").hide();
    }else if(tipo == 'MODIFICACION'){
        $("#btnGuardar").hide();
        $("#btnGuardarModificacion").show();
    }   
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
function listar(){
    //Campos ordenados a mostras
    var campos = columnas_ordenadas.split(",");
    var campos_busqueda = campos_busquedas.split(",");
    //agregar inputs de busqueda por columna
    $('#tbllistado tfoot th').each( function () {
      var titulocolumnatfoot = $(this).text();
      var valor_encontrado_en_array = campos_busqueda.indexOf(titulocolumnatfoot); 
      if(valor_encontrado_en_array >= 0){
        $(this).html( '<input type="text" placeholder="Buscar en columna '+titulocolumnatfoot+'" />' );
      }
    });
    // armar columas para datatable se arma desde funcionesglobales.js
    var campos_tabla = armar_columas_datatable(campos,campos_busqueda);
    tabla=$('#tbllistado').DataTable({
        "lengthMenu": [ 100, 250, 500, 1000 ],
        "pageLength": 500,
        "sScrollX": "110%",
        "sScrollY": "350px", 
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: lista_precios_fleetguard_obtener,
        columns: campos_tabla,
        initComplete: function () {
          // Aplicar busquedas por columna
          this.api().columns().every( function () {
            var that = this;
            $('input',this.footer()).on( 'change', function(){
              if(that.search() !== this.value){
                that.search(this.value).draw();
              }
            });
          });
          //Aplicar busqueda general
          var $buscar = $('div.dataTables_filter input');
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistado').DataTable().search( this.value ).draw();
              }
          });
        }
    });
    //modificacion al dar doble click
    $('#tbllistado tbody').on('dblclick', 'tr', function () {
      var data = tabla.row( this ).data();
      obtenerdatos(data.Codigo);
    }); 
}
//realizar en reporte en excel
function descargar_plantilla(){
    $("#btnGenerarPlantilla").attr("href", urlgenerarplantilla);
    $("#btnGenerarPlantilla").click();
}
function seleccionarpartidasexcel(){
    $("#partidasexcel").click();
}
//Cada que se elija un archivo
function cargarpartidasexcel(e) {
    $("#btnenviarpartidasexcel").click();
}
//Agregar respuesta a la datatable
$("#btnenviarpartidasexcel").on('click', function(e){
    e.preventDefault();
    $('.page-loader-wrapper').css('display', 'block');
    var partidasexcel = $('#partidasexcel')[0].files[0];
    var form_data = new FormData();
    form_data.append('partidasexcel', partidasexcel); 
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:lista_precios_fleetguard_actualizar_lista_precios_vs_excel,
      data: form_data,
      type: 'POST',
      contentType: false,
      processData: false,
      success: function (data) {
        msj_datosguardadoscorrectamente();
        $('.page-loader-wrapper').css('display', 'none');
      },
      error: function (data) {
        $('.page-loader-wrapper').css('display', 'none');
      }
    });                      
});
//alta
function alta(){
    $("#titulomodal").html('Actualizar Lista Precios Fleetguard');
    mostrarmodalformulario('ALTA');
    mostrarformulario();
    //formulario alta
    var tabs =  '<div class="col-md-12">'+
                    '<div class="row">'+
                        '<div class="col-md-12">'+   
                            '<table>'+
                                '<tr>'+
                                    '<td><div type="button" class="btn btn-success btn-sm" onclick="seleccionarpartidasexcel()">Cargar archivo para actualizar lista precios volvo</div></td>'+
                                    '<td data-toggle="tooltip" data-placement="top" title data-original-title="Ver ejemplo de plantilla para que la actualización se genere de forma correcta"><a class="material-icons" onclick="descargar_plantilla()" id="btnGenerarPlantilla" target="_blank">get_app</a></td>'+
                                '</tr>'+
                            '</table>'+
                        '</div>'+ 
                    '</div>'+  
                '</div>';
    $("#tabsform").html(tabs);
    //mostrar mensaje de bajar plantilla
    $('[data-toggle="tooltip"]').tooltip({
      container: 'body'
    });
}
function actualizartipocambio(){
    $("#modalactualizartipocambio").modal('show');
}
function obtenervalordolarhoydof(){ 
    $('.page-loader-wrapper').css('display', 'block');
    $.get(lista_precios_fleetguard_obtener_valor_dolar_hoy_dof, function(valordolar){
        if(valordolar == 'sinactualizacion'){
            msj_errorajax();
        }else{
            $("#valortipocambio").val(number_format(round(valordolar, numerodecimales), numerodecimales, '.', ''));
        }
        $('.page-loader-wrapper').css('display', 'none');
    })  
}
//guardar el registro
$("#btnguardartipodecambio").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formactualizartipocambio")[0]);
    var form = $("#formactualizartipocambio");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:lista_precios_volvo_guardar_valor_tipo_cambio,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                msj_datosguardadoscorrectamente();
                $('#modalactualizartipocambio').modal('hide');
                $("#formactualizartipocambio")[0].reset();
                //Resetear las validaciones del formulario alta
                form = $("#formactualizartipocambio");
                form.parsley().reset();
                $('.page-loader-wrapper').css('display', 'none');
            },
            error:function(data){
                if(data.status == 403){
                    msj_errorenpermisos();
                  }else{
                    msj_errorajax();
                  }
                $('.page-loader-wrapper').css('display', 'none');
            }
        })
    }else{
        form.parsley().validate();
        msj_verificartodoslosdatos();
    }
});
//configurar tabla
function configurar_tabla(){
    var checkboxscolumnas = '';
    var optionsselectbusquedas = '';
    var campos = campos_activados.split(",");
    for (var i = 0; i < campos.length; i++) {
      var returncheckboxfalse = '';
      if(campos[i] == 'Codigo' || campos[i] == 'Status'){
        returncheckboxfalse = 'onclick="javascript: return false;"';
      }
      checkboxscolumnas = checkboxscolumnas + '<div class="col-md-2 form-check">'+
                                                '<input type="checkbox" name="'+campos[i]+'" id="id'+campos[i]+'" class="filled-in datotabla" value="'+campos[i]+'" readonly onchange="construirarraydatostabla(this);" '+returncheckboxfalse+'/>'+
                                                '<label for="id'+campos[i]+'">'+campos[i]+'</label>'+
                                              '</div>';
      optionsselectbusquedas = optionsselectbusquedas + '<option value="'+campos[i]+'">'+campos[i]+'</option>';
    }
    var campos = campos_desactivados.split(",");
    for (var i = 0; i < campos.length; i++) {
      checkboxscolumnas = checkboxscolumnas + '<div class="col-md-2 form-check">'+
                                                '<input type="checkbox" name="'+campos[i]+'" id="id'+campos[i]+'" class="filled-in datotabla" value="'+campos[i]+'" readonly onchange="construirarraydatostabla(this);"/>'+
                                                '<label for="id'+campos[i]+'">'+campos[i]+'</label>'+
                                              '</div>';
      optionsselectbusquedas = optionsselectbusquedas + '<option value="'+campos[i]+'">'+campos[i]+'</option>';
    }
    //formulario configuracion tablas se arma desde funcionesglobales.js
    var tabs = armar_formulario_configuracion_tabla(checkboxscolumnas,optionsselectbusquedas);
        $("#tabsconfigurartabla").html(tabs);
        if(rol_usuario_logueado == 1){
          $("#divorderbystabla").show();
        }
        $("#string_datos_ordenamiento_columnas").val(columnas_ordenadas);
        $("#string_datos_tabla_true").val(campos_activados);
        $("#string_datos_tabla_false").val(campos_desactivados);
        $("#modalconfigurartabla").modal('show');
        $("#titulomodalconfiguraciontabla").html("Configuración de la tabla");
        $('.dd').nestable();
        $("#selectorderby1").val(primerordenamiento).select2();
        $("#deorderby1").val(formaprimerordenamiento).select2();
        $("#selectorderby2").val(segundoordenamiento).select2();
        $("#deorderby2").val(formasegundoordenamiento).select2();
        $("#selectorderby3").val(tercerordenamiento).select2();
        $("#deorderby3").val(formatercerordenamiento).select2();
        $.each(campos_busquedas.split(","), function(i,e){
          $("#selectfiltrosbusquedas option[value='" + e + "']").prop("selected", true);
        });
        $("#selectfiltrosbusquedas").select2();
        //colocar checked a campos activados
        var campos = campos_activados.split(",");
        for (var i = 0; i < campos.length; i++) {
            $("input[name='"+campos[i]+"']").prop('checked', true);

        }
        //crear lista para ordenar columnas
        var campos_ordenados = columnas_ordenadas.split(",");
        $("#columnasnestable").empty();
        for (var i = 0; i < campos_ordenados.length; i++) {
            var columna =   '<li class="dd-item nestable'+campos_ordenados[i]+'">'+
                                '<div class="dd-handle">'+campos_ordenados[i]+'</div>'+
                                '<input type="hidden" class="inputnestable" value="'+campos_ordenados[i]+'">'+
                            '</li>';
            $("#columnasnestable").append(columna);
        }
}
init();