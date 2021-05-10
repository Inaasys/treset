'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  $.get(agentes_obtener_ultimo_numero, function(numero){
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
  tabla=$('#tbllistado').DataTable({
    "lengthMenu": [ 10, 50, 100, 250, 500 ],
    "pageLength": 250,
    "sScrollX": "110%",
    "sScrollY": "350px",
    "bScrollCollapse": true,  
    processing: true,
    'language': {
      'loadingRecords': '&nbsp;',
      'processing': '<div class="spinner"></div>'
    },
    serverSide: true,
    ajax: agentes_obtener,
    columns: [
        { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
        { data: 'Numero', name: 'Numero' },
        { data: 'Nombre', name: 'Nombre' },
        { data: 'Rfc', name: 'Rfc', orderable: false, searchable: false  },
        { data: 'Status', name: 'Status', orderable: false, searchable: false  }
    ],
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
function listaralmacenes(){
  ocultarformulario();
  var tablaalmacenes =  '<div class="modal-header bg-red">'+
                          '<h4 class="modal-title">Almacenes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                          '<div class="row">'+
                              '<div class="col-md-12">'+
                                  '<div class="table-responsive">'+
                                      '<table id="tbllistadoalmacen" class="tbllistadoalmacen table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                          '<thead class="customercolor">'+
                                              '<tr>'+
                                                  '<th>Operaciones</th>'+
                                                  '<th>Número</th>'+
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
  $('#tbllistadoalmacen').DataTable({
      "sScrollX": "110%",
      "sScrollY": "300px",
      "bScrollCollapse": true,  
      processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
      serverSide: true,
      ajax: agentes_obtener_almacenes,
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
      "iDisplayLength": 8,
  });
}
function seleccionaralmacen(Numero, Nombre){
  $("#almacen").val(Numero);
  $("#nombrealmacen").val(Nombre);
  mostrarformulario();
}
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Agente');
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
                          '<label>Dirección</label>'+
                          '<input type="text" class="form-control" name="direccion" id="direccion"  data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Colonia</label>'+
                          '<input type="text" class="form-control" name="colonia" id="colonia"  data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Ciudad</label>'+
                          '<input type="text" class="form-control" name="ciudad" id="ciudad"  data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-4">'+
                          '<label>Codigo Postal</label>'+
                          '<input type="text" class="form-control" name="codigopostal" id="codigopostal"  data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>RFC</label>'+
                          '<input type="text" class="form-control" name="rfc" id="rfc" required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Contacto</label>'+
                          '<input type="text" class="form-control" name="contacto" id="contacto" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+    
                      '<div class="col-md-4">'+
                          '<label>Teléfonos</label>'+
                          '<input type="text" class="form-control" name="telefonos" id="telefonos" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Email</label>'+
                          '<input type="text" class="form-control" name="email" id="email" data-parsley-regexemail="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/" data-parsley-length="[1, 60]">'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Cuenta</label>'+
                          '<input type="text" class="form-control" name="cuenta" id="cuenta" data-parsley-length="[1, 25]" onkeyup="tipoLetra(this);">'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+ 
                      '<div class="col-md-4">'+
                        '<label>Almacén</label>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<span class="input-group-btn">'+
                                    '<div id="buscaralmacenes" class="btn bg-blue waves-effect" onclick="listaralmacenes()">Seleccionar</div>'+
                                '</span>'+
                            '</div>'+  
                            '<div class="col-md-8">'+  
                                '<div class="form-line">'+
                                    '<input type="hidden" class="form-control" name="almacen" id="almacen" value="1" required readonly>'+
                                    '<input type="text" class="form-control" name="nombrealmacen" id="nombrealmacen" value="REFACCIONES" required readonly onkeyup="tipoLetra(this);">'+
                                '</div>'+
                            '</div>'+     
                        '</div>'+
                      '</div>'+
                      '<div class="col-md-4">'+
                          '<label>Comisión  %</label>'+
                          '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="comision" id="comision" value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" onchange="formatocorrectoinputcantidades(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" >'+
                      '</div>'+
                      '<div class="col-md-4">'+
                            '<label>Anotaciones</label>'+
                            '<textarea class="form-control" name="anotaciones" id="anotaciones" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
                      '</div>'+
                    '</div>'+   
                '</div>'+
              '</div>';
  $("#tabsform").html(tabs);
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
      url:agentes_guardar,
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
function desactivar(numeroagente){
  $("#numeroagente").val(numeroagente);
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
      url:agentes_alta_o_baja,
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
function obtenerdatos(numeroagente){
  $("#titulomodal").html('Modificación Agente');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(agentes_obtener_agente,{numeroagente:numeroagente },function(data){
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
                            '<label>Dirección</label>'+
                            '<input type="text" class="form-control" name="direccion" id="direccion" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Colonia</label>'+
                            '<input type="text" class="form-control" name="colonia" id="colonia" data-parsley-length="[1, 60]"  onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Ciudad</label>'+
                            '<input type="text" class="form-control" name="ciudad" id="ciudad" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '</div>'+
                        '<div class="row">'+
                        '<div class="col-md-4">'+
                            '<label>Codigo Postal</label>'+
                            '<input type="text" class="form-control" name="codigopostal" id="codigopostal" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>RFC</label>'+
                            '<input type="text" class="form-control" name="rfc" id="rfc" required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Contacto</label>'+
                            '<input type="text" class="form-control" name="contacto" id="contacto" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '</div>'+
                        '<div class="row">'+    
                        '<div class="col-md-4">'+
                            '<label>Teléfonos</label>'+
                            '<input type="text" class="form-control" name="telefonos" id="telefonos" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Email</label>'+
                            '<input type="text" class="form-control" name="email" id="email" data-parsley-regexemail="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/" data-parsley-length="[1, 60]">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<label>Cuenta</label>'+
                            '<input type="text" class="form-control" name="cuenta" id="cuenta" data-parsley-length="[1, 25]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '</div>'+
                        '<div class="row">'+ 
                          '<div class="col-md-4">'+
                            '<label>Almacén</label>'+
                            '<div class="row">'+
                                '<div class="col-md-4">'+
                                    '<span class="input-group-btn">'+
                                        '<div id="buscaralmacenes" class="btn bg-blue waves-effect" onclick="listaralmacenes()">Seleccionar</div>'+
                                    '</span>'+
                                '</div>'+  
                                '<div class="col-md-8">'+  
                                    '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="almacen" id="almacen" value="1" required readonly>'+
                                        '<input type="text" class="form-control" name="nombrealmacen" id="nombrealmacen" value="REFACCIONES" required readonly onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                '</div>'+     
                            '</div>'+
                          '</div>'+
                          '<div class="col-md-4">'+
                              '<label>Comisión  %</label>'+
                              '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="comision" id="comision" value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" onchange="formatocorrectoinputcantidades(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" >'+
                          '</div>'+
                          '<div class="col-md-4">'+
                                  '<label>Anotaciones</label>'+
                                  '<textarea class="form-control" name="anotaciones" id="anotaciones" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
                          '</div>'+
                        '</div>'+   
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //boton formulario 
    $(".select2").select2();
    $("#numero").val(numeroagente);
    $("#nombre").val(data.agente.Nombre);
    $("#direccion").val(data.agente.Direccion);
    $("#colonia").val(data.agente.Colonia);
    $("#ciudad").val(data.agente.Ciudad);
    $("#codigopostal").val(data.agente.Cp);
    $("#rfc").val(data.agente.Rfc);
    $("#contacto").val(data.agente.Contacto);
    $("#telefonos").val(data.agente.Telefonos);
    $("#email").val(data.agente.Email);
    $("#cuenta").val(data.agente.Cuenta);
    $("#comision").val(data.comision);
    $("#anotaciones").val(data.agente.Anotaciones);
    $("#almacen").val(data.almacen.Numero); 
    $("#nombrealmacen").val(data.almacen.Nombre);
    $("#nombrealmacen").keyup(); 
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
      url:agentes_guardar_modificacion,
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
init();