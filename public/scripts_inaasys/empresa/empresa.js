'use strict'
//funcion que se ejecuta al inicio
function init(){
    setvaluesselects();
 }
//colocar color al menu
function setcolornavbar(){
    var selectcolornavbar = $("#selectcolornavbar").val();
    $("#colornavbar").removeAttr('class');
    $("#colornavbar").attr('class', 'navbar '+selectcolornavbar);
}
//colocar color a encabezados de modalas y formularios
function setcolorformsandmodals(){
    //taba cambiar logo
    var selectcolorformsandmodals = $("#selectcolorformsandmodals").val();
    $("#infoprofile").removeAttr('class');
    $("#infoprofile").attr('class', 'profile-header '+selectcolorformsandmodals);
    $("#infoprofile1").removeAttr('class');
    $("#infoprofile1").attr('class', 'header text-center '+selectcolorformsandmodals);
    $("#infoprofile2").removeAttr('class');
    $("#infoprofile2").attr('class', 'header text-center '+selectcolorformsandmodals);
}
//asignar valor a los selects
function setvaluesselects(){
    //tab cmabiar logo
    $("#selectcolornavbar option[value='"+colornavbar+"']").attr("selected", true);
    $("#selectcolorformsandmodals option[value='"+colormodalsandforms+"']").attr("selected", true);
    $("#selectcolortables option[value='"+colortables+"']").attr("selected", true);
    //tab configurar
    $("#numerodecimalessistema").val(numerodecimales).trigger("change");
    $("#numerodecilamesdocumentospdfsistema").val(numerodecimalesendocumentos).trigger("change");
    $("input[name=utilizarmayusculasistema][value='"+mayusculas_sistema+"']").prop("checked",true);    
}

//ocultar modal formulario
function ocultarmodaltablaseleccion(){
  $("#ModalTablas").modal('show');
}
//mostrar formulario en modal y ocultar tabla de seleccion
function mostrarmodaltablaseleccion(){
  $("#ModalTablas").modal('hide');
}
//cuando el usuario cambia el estado se deben vaciar el municipio y CP
function vaciarmunicipioycodigopostal(){
  $("#empresanombremunicipio").val("");
}
//cuando el usuario cambia de pais vaciar el estado
function vaciarestado(){
  $("#empresanombreestado").val("");
  $("#empresanumeroestado").val("");
}

//obtener registros de paises
function obtenerpaises(){
  ocultarmodaltablaseleccion();
  var tablapaises = '<div class="modal-header '+background_forms_and_modals+'">'+
                      '<h4 class="modal-title">Paises</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                  '<table id="tbllistadopais" class="tbllistadopais table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                      '<thead class="'+background_tables+'">'+
                                          '<tr>'+
                                              '<th>Operaciones</th>'+
                                              '<th>Clave</th>'+
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
                      '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarmodaltablaseleccion();">Regresar</button>'+
                    '</div>';
  $("#contenidomodaltablas").html(tablapaises);
  $('#tbllistadopais').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: empresa_obtener_paises,
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre' }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadopais').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  }); 
} 
//obtener registros de estados
function obtenerestados() {
  ocultarmodaltablaseleccion();
  var tablaestados = '<div class="modal-header '+background_forms_and_modals+'">'+
                      '<h4 class="modal-title">Estados</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                  '<table id="tbllistadoestado" class="tbllistadoestado table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                      '<thead class="'+background_tables+'">'+
                                          '<tr>'+
                                              '<th>Operaciones</th>'+
                                              '<th>Número</th>'+
                                              '<th>Clave</th>'+
                                              '<th>País</th>'+
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
                      '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarmodaltablaseleccion();">Regresar</button>'+
                    '</div>';
  $("#contenidomodaltablas").html(tablaestados);
  $('#tbllistadoestado').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: empresa_obtener_estados,
        data: function (d) {
            d.numeropais = $("#empresanumeropais").val();
        }
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Numero', name: 'Numero' },
          { data: 'Clave', name: 'Clave' },
          { data: 'Pais', name: 'Pais', orderable: false, searchable: false},
          { data: 'Nombre', name: 'Nombre' }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoestado').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  }); 
}
//obtener registros de municipios
function obtenermunicipios() {
  ocultarmodaltablaseleccion();
  var tablamunicipios = '<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Municipios</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                          '<div class="row">'+
                              '<div class="col-md-12">'+
                                  '<div class="table-responsive">'+
                                      '<table id="tbllistadomunicipio" class="tbllistadomunicipio table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                          '<thead class="'+background_tables+'">'+
                                              '<tr>'+
                                                  '<th>Operaciones</th>'+
                                                  '<th>Clave</th>'+
                                                  '<th>Estado</th>'+
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
                          '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarmodaltablaseleccion();">Regresar</button>'+
                        '</div>';
  $("#contenidomodaltablas").html(tablamunicipios);
  $('#tbllistadomunicipio').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: empresa_obtener_municipios,
        data: function (d) {
            d.claveestado = $("#empresanumeroestado").val();
        }
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Estado', name: 'Estado', orderable: false, searchable: false},
          { data: 'Nombre', name: 'Nombre' }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadomunicipio').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  });  
}



//obtener registros de paises
function obtenerlugaresexpedicion(){
  ocultarmodaltablaseleccion();
  var tablalugaresexpedicion = '<div class="modal-header '+background_forms_and_modals+'">'+
                      '<h4 class="modal-title">Lugares Expedición</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                  '<table id="tbllistadolugarexpedicion" class="tbllistadolugarexpedicion table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                      '<thead class="'+background_tables+'">'+
                                          '<tr>'+
                                              '<th>Operaciones</th>'+
                                              '<th>Clave</th>'+
                                              '<th>Estado</th>'+
                                          '</tr>'+
                                      '</thead>'+
                                      '<tbody></tbody>'+
                                  '</table>'+
                              '</div>'+
                          '</div>'+   
                      '</div>'+
                    '</div>'+
                    '<div class="modal-footer">'+
                      '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarmodaltablaseleccion();">Regresar</button>'+
                    '</div>';
  $("#contenidomodaltablas").html(tablalugaresexpedicion);
  $('#tbllistadolugarexpedicion').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: empresa_obtener_lugares_expedicion,
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Estado', name: 'Estado' }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadolugarexpedicion').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  }); 
} 
//obtener registros de paises
function obtenerregimenesfiscales(){
  ocultarmodaltablaseleccion();
  var tablaregimenesfiscales = '<div class="modal-header '+background_forms_and_modals+'">'+
                      '<h4 class="modal-title">Regimenes Fiscales</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                  '<table id="tbllistadoregimenfiscal" class="tbllistadoregimenfiscal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                      '<thead class="'+background_tables+'">'+
                                          '<tr>'+
                                              '<th>Operaciones</th>'+
                                              '<th>Clave</th>'+
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
                      '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarmodaltablaseleccion();">Regresar</button>'+
                    '</div>';
  $("#contenidomodaltablas").html(tablaregimenesfiscales);
  $('#tbllistadoregimenfiscal').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: empresa_obtener_regimenes_fiscales,
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre' }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoregimenfiscal').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  }); 
} 
//obtener registros de paises
function obtenermonedas(){
  ocultarmodaltablaseleccion();
  var tablamonedas= '<div class="modal-header '+background_forms_and_modals+'">'+
                      '<h4 class="modal-title">Monedas</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                  '<table id="tbllistadomoneda" class="tbllistadomoneda table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                      '<thead class="'+background_tables+'">'+
                                          '<tr>'+
                                              '<th>Operaciones</th>'+
                                              '<th>Clave</th>'+
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
                      '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarmodaltablaseleccion();">Regresar</button>'+
                    '</div>';
  $("#contenidomodaltablas").html(tablamonedas);
  $('#tbllistadomoneda').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: {
        url: empresa_obtener_monedas,
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre' }
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadomoneda').DataTable().search( this.value ).draw();
            }
        });
      },
      "iDisplayLength": 8,
  }); 
} 


function seleccionarpais(Numero, Clave){
  $("#empresanombrepais").val(Clave);
  $("#empresanumeropais").val(Numero);
  mostrarmodaltablaseleccion();
  vaciarestado();
  vaciarmunicipioycodigopostal();
}
function seleccionarestado(Clave, Nombre){
  $("#empresanumeroestado").val(Clave);
  $("#empresanombreestado").val(Nombre);
  mostrarmodaltablaseleccion();
  vaciarmunicipioycodigopostal();
}
function seleccionarmunicipio(Nombre){
  $("#empresanombremunicipio").val(Nombre);
  mostrarmodaltablaseleccion();
}

function seleccionarlugarexpedicion(Clave){
  $("#empresalugarexpedicion").val(Clave);
  mostrarmodaltablaseleccion();
}
function seleccionarregimenfiscal(Clave){
  $("#empresaregimenfiscal").val(Clave);
  mostrarmodaltablaseleccion();
}
function seleccionarmoneda(Clave){
  $("#empresamoneda").val(Clave);
  mostrarmodaltablaseleccion();
}

//guardar modificacion domicilio fiscal
$("#btnguardardomiciliofiscal").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formdomiciliofiscal")[0]);
    var form = $("#formdomiciliofiscal");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          url:empresa_guardar_modificacion_domicilio_fiscal,
          type: "post",
          dataType: "html",
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          success:function(data){
            msj_datosguardadoscorrectamente();
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
      msjfaltandatosporcapturar();
    }
    //validar formulario
    form.parsley().validate();
});


//guardar modificacion domicilio fiscal
$("#btnguardarlugarexpedicion").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formlugarexpedicion")[0]);
  var form = $("#formlugarexpedicion");
  if (form.parsley().isValid()){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:empresa_guardar_modificacion_lugar_expedicion,
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data){
          msj_datosguardadoscorrectamente();
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
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});

//guardar modificacion configurar
$("#btnguardarconfigurar").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formconfigurar")[0]);
  var form = $("#formconfigurar");
  if (form.parsley().isValid()){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:empresa_guardar_modificacion_configurar,
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data){
          msj_datosguardadoscorrectamente();
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
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});

//guardar modificacion contrasena
$("#btnguardarcontrasena").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formcambiarcontrasena")[0]);
  var form = $("#formcambiarcontrasena");
  if (form.parsley().isValid()){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:cambiar_contrasena,
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data){
          msj_datosguardadoscorrectamente();
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
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});
//guardar modificacion logo y temas
$("#btnguardarlogotipo").on('click', function (e) {
    e.preventDefault();
    //var formData = new FormData($("#formlogotipo")[0]);
    var form = $("#formlogotipo");
    if (form.parsley().isValid()){
        var formData = new FormData($("#formlogotipo")[0]);
        var logotipo = $('#logo')[0].files[0];
        formData.append('logotipo', logotipo); 
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          url:empresa_guardar_modificacion_logo_y_tema,
          type: "post",
          dataType: "html",
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          success:function(data){
            var nuevologotipo = data.replace(/['"]+/g, '');
            msj_datosguardadoscorrectamente();
            $('#perfillogotipoempresa').attr('src', urllogotipos + nuevologotipo);
            $('#navbarlogotipoempresa').attr('src', urllogotipos + nuevologotipo);
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
      msjfaltandatosporcapturar();
    }
    //validar formulario
    form.parsley().validate();
});

init();