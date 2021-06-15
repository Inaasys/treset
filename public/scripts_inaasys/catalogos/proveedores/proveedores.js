'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  $.get(proveedores_obtener_ultimo_numero, function(numero){
    $("#numero").val(numero);
  })  
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
  var campos_tabla  = [];
  campos_tabla.push({ 'data':'operaciones', 'name':'operaciones', 'orderable':false, 'searchable':false});
  for (var i = 0; i < campos.length; i++) {
      campos_tabla.push({ 
          'data'    : campos[i],
          'name'  : campos[i],
          'orderable': true,
          'searchable': true
      });
  }
    tabla=$('#tbllistado').DataTable({
        "lengthMenu": [ 10, 50, 100, 250, 500 ],
        "pageLength": 250,
        "sScrollX": "110%",
        "sScrollY": "350px", 
        processing: true,
        'language': {
          'loadingRecords': '&nbsp;',
          'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: proveedores_obtener,
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
function listarcodigospostales(){
  ocultarformulario();
  var tablacodigospostales =  '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Códigos Postales</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadocodigopostal" class="tbllistadocodigopostal table table-bordered table-striped table-hover" style="width:100% !important; ">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
                                                        '<th>Estado</th>'+
                                                        '<th>Municipio</th>'+
                                                    '</tr>'+
                                                '</thead>'+
                                                '<tbody></tbody>'+
                                            '</table>'+
                                        '</div>'+
                                    '</div>'+   
                                '</div>'+
                              '</div>'+
                              '<div class="modal-footer">'+
                                '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Salir</button>'+
                              '</div>';
    $("#contenidomodaltablas").html(tablacodigospostales);
    $('#tbllistadocodigopostal').DataTable({
        "sScrollX": "110%",
        "sScrollY": "300px",
        "bScrollCollapse": true,  
        processing: true,
        'language': {
          'loadingRecords': '&nbsp;',
          'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: proveedores_obtener_codigos_postales,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Clave', name: 'Clave' },
            { data: 'Estado', name: 'Estado', orderable: false, searchable: false },
            { data: 'Municipio', name: 'Municipio', orderable: false, searchable: false }
        ],
        "initComplete": function() {
          var $buscar = $('div.dataTables_filter input');
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadocodigopostal').DataTable().search( this.value ).draw();
              }
          });
        },
        "iDisplayLength": 8,
    });
}
function seleccionarcodigopostal(Clave){
    $("#codigopostal").val(Clave);
    mostrarformulario();
}
//alta
function alta(){
  $("#titulomodal").html('Alta Proveedor');
  mostrarmodalformulario('ALTA');
  mostrarformulario();
  //formulario alta
  var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                '<li role="presentation" class="active">'+
                  '<a href="#datosgenerales" data-toggle="tab">Datos Generales</a>'+
                '</li>'+
              '</ul>'+
              '<div class="tab-content">'+
                '<div role="tabpanel" class="tab-pane fade in active" id="datosgenerales">'+
                  '<div class="row">'+
                      '<div class="col-md-4">'+
                          '<label>RFC</label>'+
                          '<input type="text" class="form-control" name="rfc" id="rfc" required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Código Postal</label>'+
                            '<div class="row">'+
                                '<div class="col-md-4">'+
                                    '<span class="input-group-btn">'+
                                        '<div id="buscarcodigospostales" class="btn bg-blue waves-effect" onclick="listarcodigospostales()">Seleccionar</div>'+
                                    '</span>'+
                                '</div>'+  
                                '<div class="col-md-8">'+  
                                    '<div class="form-line">'+
                                        '<input type="text" class="form-control" name="codigopostal" id="codigopostal" required data-parsley-codigopostal="^[0-9]{5}$">'+
                                    '</div>'+
                                '</div>'+     
                            '</div>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Teléfonos</label>'+
                            '<input type="text" class="form-control" name="telefonos" id="telefonos" data-parsley-length="[1, 100]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>E-mail 1</label>'+
                            '<input type="text" class="form-control" name="email1" id="email1"  data-parsley-regexemail="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/" data-parsley-length="[1, 100]">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Plazo</label>'+
                            '<input type="number" class="form-control" name="plazo" id="plazo" required>'+
                        '</div>'+
                    '</div>'+   
                '</div>'+
              '</div>';
  $("#tabsform").html(tabs);
  //inicializar los select con la libreria select2
  obtenultimonumero();
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:proveedores_guardar,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        if(data == 1){
          msj_errorrfcexistente();
        }else{
          msj_datosguardadoscorrectamente();
          limpiar();
          ocultarmodalformulario();
          limpiarmodales();
        }
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
  }
});
//dar de baja o alta registro
function desactivar(numeroproveedor){
  $("#numeroproveedor").val(numeroproveedor);
  $('#estatusregistro').modal('show');
}
$("#aceptar").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formdesactivar")[0]);
  var form = $("#formdesactivar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:proveedores_alta_o_baja,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#estatusregistro').modal('hide');
        msj_statuscambiado();
        $('.page-loader-wrapper').css('display', 'none');
      },
      error:function(data){
        if(data.status == 403){
          msj_errorenpermisos();
        }else{
          msj_errorajax();
        }
        $('#estatusregistro').modal('hide');
        $('.page-loader-wrapper').css('display', 'none');
      }
    })
  }else{
    form.parsley().validate();
  }
});
function obtenerdatos(numeroproveedor){
  $("#titulomodal").html('Modificación Proveedor');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(proveedores_obtener_proveedor,{numeroproveedor:numeroproveedor },function(data){
    //formulario modificacion
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#datosgenerales" data-toggle="tab">Datos Generales</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="datosgenerales">'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>RFC</label>'+
                                '<input type="text" class="form-control" name="rfc" id="rfc" required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Código Postal</label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarcodigospostales" class="btn bg-blue waves-effect" onclick="listarcodigospostales()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-8">'+  
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control" name="codigopostal" id="codigopostal" required data-parsley-codigopostal="^[0-9]{5}$">'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Teléfonos</label>'+
                                '<input type="text" class="form-control" name="telefonos" id="telefonos" onkeyup="tipoLetra(this);" data-parsley-length="[1, 100]">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>E-mail 1</label>'+
                                '<input type="text" class="form-control" name="email1" id="email1"  data-parsley-regexemail="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/" data-parsley-length="[1, 100]">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                              '<label>Plazo</label>'+
                              '<input type="number" class="form-control" name="plazo" id="plazo" required>'+
                            '</div>'+
                        '</div>'+   
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //boton formulario 
    $("#numero").val(numeroproveedor);
    $("#nombre").val(data.proveedor.Nombre);
    $("#rfc").val(data.proveedor.Rfc);
    $("#codigopostal").val(data.proveedor.CodigoPostal);
    $("#email1").val(data.proveedor.Email1);
    $("#plazo").val(data.proveedor.Plazo);
    $("#telefonos").val(data.proveedor.Telefonos);
    mostrarmodalformulario('MODIFICACION');
    $('.page-loader-wrapper').css('display', 'none');
  }).fail( function() {
    msj_errorajax();
    $('.page-loader-wrapper').css('display', 'none');
  })
}
//guardar el registro
$("#btnGuardarModificacion").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:proveedores_guardar_modificacion,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        if(data == 1){
          msj_errorrfcexistente();
        }else{
          msj_datosguardadoscorrectamente();
          limpiar();
          ocultarmodalformulario();
          limpiarmodales();
        }
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
  }
});
//configurar tabla
function configurar_tabla(){
  //formulario modificacion
  var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                  '<li role="presentation" class="active">'+
                      '<a href="#tabcamposamostrar" data-toggle="tab">Campos a mostrar</a>'+
                  '</li>'+
                  '<li role="presentation">'+
                      '<a href="#tabordenarcolumnas" data-toggle="tab">Ordenar Columnas</a>'+
                  '</li>'+
              '</ul>'+
              '<div class="tab-content">'+
                  '<div role="tabpanel" class="tab-pane fade in active" id="tabcamposamostrar">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="col-md-12 form-check">'+
                                  '<label>DATOS PROVEEDOR</label>'+
                              '</div>'+
                              '<div class="col-md-3 form-check">'+
                                  '<input type="checkbox" name="Numero" id="idNumero" class="filled-in datotabla" value="Numero" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idNumero">Numero</label>'+
                              '</div>'+
                              '<div class="col-md-3 form-check">'+
                                  '<input type="checkbox" name="Status" id="idStatus" class="filled-in datotabla" value="Status" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idStatus">Status</label>'+
                              '</div>'+
                              '<div class="col-md-3 form-check">'+
                                  '<input type="checkbox" name="Rfc" id="idRfc" class="filled-in datotabla" value="Rfc" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idRfc">Rfc</label>'+
                              '</div>'+
                              '<div class="col-md-3 form-check">'+
                                  '<input type="checkbox" name="Nombre" id="idNombre" class="filled-in datotabla" value="Nombre" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idNombre">Nombre</label>'+
                              '</div>'+
                              '<div class="col-md-3 form-check">'+
                                  '<input type="checkbox" name="CodigoPostal" id="idCodigoPostal" class="filled-in datotabla" value="CodigoPostal" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCodigoPostal">CodigoPostal</label>'+
                              '</div>'+
                              '<div class="col-md-3 form-check">'+
                                  '<input type="checkbox" name="Email1" id="idEmail1" class="filled-in datotabla" value="Email1" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEmail1">Email1</label>'+
                              '</div>'+
                              '<div class="col-md-3 form-check">'+
                                  '<input type="checkbox" name="Plazo" id="idPlazo" class="filled-in datotabla" value="Plazo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPlazo">Plazo</label>'+
                              '</div>'+
                              '<div class="col-md-3 form-check">'+
                                  '<input type="checkbox" name="Telefonos" id="idTelefonos" class="filled-in datotabla" value="Telefonos" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTelefonos">Telefonos</label>'+
                              '</div>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                          '</div>'+
                      '</div>'+
                  '</div>'+ 
                  '<div role="tabpanel" class="tab-pane fade" id="tabordenarcolumnas">'+
                      '<div class="row">'+
                          '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">'+
                              '<div class="card">'+
                                  '<div class="header">'+
                                      '<h2>'+
                                          'Ordenar Columnas'+
                                          '<small>Ordena las columnas arrastrándolas hacia arriba o hacia abajo. </small>'+
                                      '</h2>'+
                                  '</div>'+
                                  '<div class="body">'+
                                      '<div class="clearfix m-b-20">'+
                                          '<div class="dd" onchange="ordenarcolumnas()">'+
                                              '<ol class="dd-list" id="columnasnestable">'+
                                              '</ol>'+
                                          '</div>'+
                                      '</div>'+
                                      '<input type="hidden" id="string_datos_ordenamiento_columnas" name="string_datos_ordenamiento_columnas" class="form-control" required>'+
                                  '</div>'+
                              '</div>'+
                          '</div>'+
                      '</div>'+      
                  '</div>'+
              '</div>';
  $("#tabsconfigurartabla").html(tabs);
  $("#string_datos_ordenamiento_columnas").val(columnas_ordenadas);
  $("#string_datos_tabla_true").val(campos_activados);
  $("#string_datos_tabla_false").val(campos_desactivados);
  $("#modalconfigurartabla").modal('show');
  $("#titulomodalconfiguraciontabla").html("Configuración de la tabla");
  $('.dd').nestable();
  //campos activados
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