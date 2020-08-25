'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  $.get(usuarios_obtener_ultimo_numero, function(numero){
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
    "sScrollX": "110%",
    "sScrollY": "350px",
    "bScrollCollapse": true,
    processing: true,
    'language': {
      'loadingRecords': '&nbsp;',
      'processing': '<div class="spinner"></div>'
    },
    serverSide: true,
    ajax: usuarios_obtener,
    columns: [
        { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
        { data: 'id', name: 'id' },
        { data: 'name', name: 'name' },
        { data: 'email', name: 'email', orderable: false  },
        { data: 'user', name: 'user', orderable: false, searchable: false  },
        { data: 'role_id', name: 'role_id', orderable: false, searchable: false  }
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
//obtener roles
function obtenerroles(){
    $.get(usuarios_obtener_roles, function(roles){
        $("#roles").html(roles);
      })      
}
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Usuario');
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
                            '<div class="col-md-12">'+
                                '<small><b style="color:#F44336 !important;">*</b> Datos obligatorios</small>'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Nombre <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control" name="name" id="name" required onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Usuario <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control" name="user" id="user" required onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Correo Electrónico <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control" name="email" id="email" required autocomplete="email" data-parsley-type="email"	>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<label>Contraseña <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="password" class="form-control" name="pass" id="pass" required autocomplete="new-password">'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<label>Confirmar Contraseña <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="password" class="form-control" name="confirmarpass" id="confirmasrpass" required autocomplete="new-password" data-parsley-equalto="#pass">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Rol <b style="color:#F44336 !important;">*</b></label>'+
                                    '<div class="col-md-12 form-check" id="roles">'+
                                        
                                    '</div>'+
                            '</div>'+
                        '</div>'+ 
                    '</div>'+ 
                '</div>';
  $("#tabsform").html(tabs);
  obtenultimonumero();
  obtenerroles();
  $("#ModalAlta").modal('show');
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
            url:usuarios_guardar,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                if(data == 1){
                    msj_errorcorreoexistente();
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
function desactivar(id){
  $("#idusuario").val(id);
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
            url:usuarios_alta_o_baja,
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
                $('#estatusregistro').modal('hide');
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
function obtenerdatos(id){
  $("#titulomodal").html('Modificación Usuario');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(usuarios_obtener_usuario,{id:id },function(data){
    //formulario modificacion
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                    '<a href="#datosgenerales" data-toggle="tab">Datos Generales</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="datosgenerales">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<small><b style="color:#F44336 !important;">*</b> Datos obligatorios</small>'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Nombre <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control" name="name" id="name" required onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Usuario <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control" name="user" id="user" required readonly onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Correo Electrónico <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control" name="email" id="email" autocomplete="email" required data-parsley-type="email">'+
                            '</div>'+
                            '<div class="col-md-6" hidden>'+
                                '<label>Contraseña <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="password" class="form-control" name="pass" id="pass" autocomplete="new-password" data-parsley-regexsafepassword="/^(?=.{8,}$)(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$/">'+
                            '</div>'+
                            '<div class="col-md-6" hidden>'+
                                '<label>Confirmar Contraseña <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="password" class="form-control" name="confirmarpass" id="confirmasrpass" autocomplete="new-password" data-parsley-equalto="#pass">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Rol <b style="color:#F44336 !important;">*</b></label>'+
                                    '<div class="col-md-12 form-check" id="roles">'+
                                        
                                    '</div>'+
                            '</div>'+
                        '</div>'+ 
                    '</div>'+ 
                '</div>';
    $("#tabsform").html(tabs);
    $("#numero").val(id);
    $("#name").val(data.usuario.name);
    $("#user").val(data.usuario.user);
    $("#email").val(data.usuario.email);
    $("#roles").html(data.roles);
    mostrarmodalformulario('MODIFICACION');
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
            url:usuarios_guardar_modificacion,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                if(data == 1){
                    msj_errorcorreoexistente();
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

function permisos(id){
    $('.page-loader-wrapper').css('display', 'block');
    $.get(usuarios_obtener_permisos,{id:id},function(data){
        //formulario modificacion
        var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                        '<li role="presentation" class="active">'+
                            '<a href="#tabregistros" data-toggle="tab">Registros</a>'+
                        '</li>'+
                        '<li role="presentation">'+
                            '<a href="#tabcatalogos" data-toggle="tab">Catálogos</a>'+
                        '</li>'+
                        '<li role="presentation">'+
                            '<a href="#tabliberar" data-toggle="tab">Liberar</a>'+
                        '</li>'+
                        '<li role="presentation">'+
                            '<a href="#tabsat" data-toggle="tab">Sat</a>'+
                        '</li>'+
                        '<li role="presentation">'+
                            '<a href="#tabseries" data-toggle="tab">Series</a>'+
                        '</li>'+
                        '<li role="presentation">'+
                            '<a href="#tabalmacenes" data-toggle="tab">Almacenes</a>'+
                        '</li>'+
                    '</ul>'+
                    '<div class="tab-content">'+
                        '<div role="tabpanel" class="tab-pane fade in active" id="tabregistros">'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Ordenes de Compra</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrosordenescompra" id="idmenuregistrosordenescompra" class="filled-in submenu accesoregistros" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrosordenescompra">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ordenescompra.altas" id="idregistros.ordenescompra.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ordenescompra.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ordenescompra.bajas" id="idregistros.ordenescompra.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ordenescompra.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ordenescompra.cambios" id="idregistros.ordenescompra.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ordenescompra.cambios">Cambios</label>'+

                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ordenescompra.autorizar" id="idregistros.ordenescompra.autorizar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ordenescompra.autorizar">Autorizar</label>'+

                                        '<input type="hidden" name="string_submenus" id="string_submenus">'+
                                        '<input type="hidden" name="string_permisos_crud" id="string_permisos_crud">'+
                                        '<input type="hidden" name="id_usuario_permisos" id="id_usuario_permisos">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Compras</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistroscompras" id="idmenuregistroscompras" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistroscompras">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.compras.altas" id="idregistros.compras.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.compras.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.compras.bajas" id="idregistros.compras.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.compras.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.compras.cambios" id="idregistros.compras.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.compras.cambios">Cambios</label>'+                                        
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>ContraRecibos</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistroscontrarecibos" id="idmenuregistroscontrarecibos" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistroscontrarecibos">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.contrarecibos.altas" id="idregistros.contrarecibos.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.contrarecibos.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.contrarecibos.bajas" id="idregistros.contrarecibos.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.contrarecibos.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.contrarecibos.cambios" id="idregistros.contrarecibos.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.contrarecibos.cambios">Cambios</label>'+  
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Cotizaciones</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistroscotizaciones" id="idmenuregistroscotizaciones" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistroscotizaciones">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.cotizaciones.altas" id="idregistros.cotizaciones.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.cotizaciones.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.cotizaciones.bajas" id="idregistros.cotizaciones.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.cotizaciones.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.cotizaciones.cambios" id="idregistros.cotizaciones.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.cotizaciones.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Pedidos</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrospedidos" id="idmenuregistrospedidos" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrospedidos">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.pedidos.altas" id="idregistros.pedidos.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.pedidos.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.pedidos.bajas" id="idregistros.pedidos.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.pedidos.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.pedidos.cambios" id="idregistros.pedidos.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.pedidos.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Remisiones</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrosremisiones" id="idmenuregistrosremisiones" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrosremisiones">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.remisiones.altas" id="idregistros.remisiones.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.remisiones.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.remisiones.bajas" id="idregistros.remisiones.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.remisiones.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.remisiones.cambios" id="idregistros.remisiones.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.remisiones.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Facturas</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrosfacturas" id="idmenuregistrosfacturas" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrosfacturas">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.facturas.altas" id="idregistros.facturas.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.facturas.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.facturas.bajas" id="idregistros.facturas.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.facturas.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.facturas.cambios" id="idregistros.facturas.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.facturas.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Producción</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrosproduccion" id="idmenuregistrosproduccion" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrosproduccion">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.produccion.altas" id="idregistros.produccion.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.produccion.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.produccion.bajas" id="idregistros.produccion.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.produccion.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.produccion.cambios" id="idregistros.produccion.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.produccion.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Destinar</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrosdestinar" id="idmenuregistrosdestinar" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrosdestinar">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.destinar.altas" id="idregistros.destinar.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.destinar.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.destinar.bajas" id="idregistros.destinar.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.destinar.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.destinar.cambios" id="idregistros.destinar.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.destinar.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Traspasos</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrostraspasos" id="idmenuregistrostraspasos" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrostraspasos">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.traspasos.altas" id="idregistros.traspasos.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.traspasos.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.traspasos.bajas" id="idregistros.traspasos.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.traspasos.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.traspasos.cambios" id="idregistros.traspasos.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.traspasos.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Requisiciones</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrosrequisiciones" id="idmenuregistrosrequisiciones" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrosrequisiciones">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.requisiciones.altas" id="idregistros.requisiciones.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.requisiciones.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.requisiciones.bajas" id="idregistros.requisiciones.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.requisiciones.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.requisiciones.cambios" id="idregistros.requisiciones.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.requisiciones.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Ordenes de Trabajo</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrosordenestrabajo" id="idmenuregistrosordenestrabajo" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrosordenestrabajo">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ordenes.trabajo.altas" id="idregistros.ordenes.trabajo.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ordenes.trabajo.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ordenes.trabajo.bajas" id="idregistros.ordenes.trabajo.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ordenes.trabajo.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ordenes.trabajo.cambios" id="idregistros.ordenes.trabajo.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ordenes.trabajo.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Planeación del Taller</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrosplaneaciontaller" id="idmenuregistrosplaneaciontaller" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrosplaneaciontaller">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.planeacion.taller.altas" id="idregistros.planeacion.taller.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.planeacion.taller.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.planeacion.taller.bajas" id="idregistros.planeacion.taller.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.planeacion.taller.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.planeacion.taller.cambios" id="idregistros.planeacion.taller.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.planeacion.taller.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Cuentas x Cobrar</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistroscuentasxcobrar" id="idmenuregistroscuentasxcobrar" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistroscuentasxcobrar">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.cuentas.x.cobrar.altas" id="idregistros.cuentas.x.cobrar.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.cuentas.x.cobrar.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.cuentas.x.cobrar.bajas" id="idregistros.cuentas.x.cobrar.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.cuentas.x.cobrar.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.cuentas.x.cobrar.cambios" id="idregistros.cuentas.x.cobrar.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.cuentas.x.cobrar.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Cuentas x Pagar</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistroscuentasxpagar" id="idmenuregistroscuentasxpagar" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistroscuentasxpagar">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.cuentas.x.pagar.altas" id="idregistros.cuentas.x.pagar.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.cuentas.x.pagar.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.cuentas.x.pagar.bajas" id="idregistros.cuentas.x.pagar.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.cuentas.x.pagar.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.cuentas.x.pagar.cambios" id="idregistros.cuentas.x.pagar.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.cuentas.x.pagar.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Notas Crédito Clientes</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrosnotascreditoclientes" id="idmenuregistrosnotascreditoclientes" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrosnotascreditoclientes">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.notas.credito.clientes.altas" id="idregistros.notas.credito.clientes.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.notas.credito.clientes.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.notas.credito.clientes.bajas" id="idregistros.notas.credito.clientes.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.notas.credito.clientes.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.notas.credito.clientes.cambios" id="idregistros.notas.credito.clientes.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.notas.credito.clientes.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Notas Crédito Proveedores</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrosnotascreditoproveedores" id="idmenuregistrosnotascreditoproveedores" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrosnotascreditoproveedores">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.notas.credito.proveedores.altas" id="idregistros.notas.credito.proveedores.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.notas.credito.proveedores.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.notas.credito.proveedores.bajas" id="idregistros.notas.credito.proveedores.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.notas.credito.proveedores.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.notas.credito.proveedores.cambios" id="idregistros.notas.credito.proveedores.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.notas.credito.proveedores.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Cíclicos</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrosciclicos" id="idmenuregistrosciclicos" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrosciclicos">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ciclicos.altas" id="idregistros.ciclicos.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ciclicos.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ciclicos.bajas" id="idregistros.ciclicos.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ciclicos.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ciclicos.cambios" id="idregistros.ciclicos.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ciclicos.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Ajustes de Inventario</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menuregistrosajusteinventario" id="idmenuregistrosajusteinventario" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenuregistrosajusteinventario">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ajustes.inventario.altas" id="idregistros.ajustes.inventario.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ajustes.inventario.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ajustes.inventario.bajas" id="idregistros.ajustes.inventario.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ajustes.inventario.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="registros.ajustes.inventario.cambios" id="idregistros.ajustes.inventario.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idregistros.ajustes.inventario.cambios">Cambios</label>'+ 
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Acceso a todos los registros en menu</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="accesotodoslosregistrosenmenu" id="idaccesotodoslosregistrosenmenu" class="filled-in" value="1" onchange="marcaraccesosentodoslosregistros()"/>'+
                                        '<label for="idaccesotodoslosregistrosenmenu">Marcar</label>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Altas,Bajas,Cambios en todos los registros</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="crudentodoslosregistros" id="idcrudentodoslosregistros" class="filled-in" value="1" onchange="marcarcrudentodoslosregistros()"/>'+
                                        '<label for="idcrudentodoslosregistros">Marcar</label>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                        '<div role="tabpanel" class="tab-pane fade" id="tabcatalogos">'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Clientes</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menucatalogoclientes" id="idmenucatalogoclientes" class="filled-in submenu accesocatalogos" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogoclientes">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.clientes.altas" id="idcatalogos.clientes.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idcatalogos.clientes.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.clientes.bajas" id="idcatalogos.clientes.bajas" class="filled-in crudcatalogos permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idcatalogos.clientes.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.clientes.cambios" id="idcatalogos.clientes.cambios" class="filled-in crudcatalogos permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idcatalogos.clientes.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Agentes</label>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<input type="checkbox" name="menucatalogoagentes" id="idmenucatalogoagentes" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogoagentes">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.agentes.altas" id="idcatalogos.agentes.altas" class="filled-in crudcatalogos permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idcatalogos.agentes.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.agentes.bajas" id="idcatalogos.agentes.bajas" class="filled-in crudcatalogos permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idcatalogos.agentes.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.agentes.cambios" id="idcatalogos.agentes.cambios" class="filled-in crudcatalogos permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                        '<label for="idcatalogos.agentes.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+      
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Proveedores</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="menucatalogoproveedores" id="idmenucatalogoproveedores" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogoproveedores">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.proveedores.altas" id="idcatalogos.proveedores.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.proveedores.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.proveedores.bajas" id="idcatalogos.proveedores.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.proveedores.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.proveedores.cambios" id="idcatalogos.proveedores.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.proveedores.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Almacenes </label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="menucatalogoalmacenes" id="idmenucatalogoalmacenes" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogoalmacenes">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.almacenes.altas" id="idcatalogos.almacenes.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.almacenes.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.almacenes.bajas" id="idcatalogos.almacenes.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.almacenes.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.almacenes.cambios" id="idcatalogos.almacenes.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.almacenes.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Marcas</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="menucatalogomarcas" id="idmenucatalogomarcas" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogomarcas">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.marcas.altas" id="idcatalogos.marcas.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.marcas.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.marcas.bajas" id="idcatalogos.marcas.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.marcas.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.marcas.cambios" id="idcatalogos.marcas.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.marcas.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Lineas</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="menucatalogolineas" id="idmenucatalogolineas" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogolineas">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.lineas.altas" id="idcatalogos.lineas.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.lineas.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.lineas.bajas" id="idcatalogos.lineas.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.lineas.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.lineas.cambios" id="idcatalogos.lineas.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.lineas.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Productos</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="menucatalogoproductos" id="idmenucatalogoproductos" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogoproductos">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.productos.altas" id="idcatalogos.productos.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.productos.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.productos.bajas" id="idcatalogos.productos.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.productos.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.productos.cambios" id="idcatalogos.productos.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.productos.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Bancos</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="menucatalogobancos" id="idmenucatalogobancos" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogobancos">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.bancos.altas" id="idcatalogos.bancos.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.bancos.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.bancos.bajas" id="idcatalogos.bancos.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.bancos.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.bancos.cambios" id="idcatalogos.bancos.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.bancos.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Técnicos</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="menucatalogotecnicos" id="idmenucatalogotecnicos" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogotecnicos">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.tecnicos.altas" id="idcatalogos.tecnicos.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.tecnicos.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.tecnicos.bajas" id="idcatalogos.tecnicos.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.tecnicos.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.tecnicos.cambios" id="idcatalogos.tecnicos.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.tecnicos.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Servicios</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="menucatalogoservicios" id="idmenucatalogoservicios" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogoservicios">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.servicios.altas" id="idcatalogos.servicios.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.servicios.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.servicios.bajas" id="idcatalogos.servicios.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.servicios.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.servicios.cambios" id="idcatalogos.servicios.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.servicios.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Vines</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="menucatalogovines" id="idmenucatalogovines" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogovines">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.vines.altas" id="idcatalogos.vines.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.vines.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.vines.bajas" id="idcatalogos.vines.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.vines.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.vines.cambios" id="idcatalogos.vines.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.vines.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Crear Encuesta</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="menucatalogoencuentascrearencuesta" id="idmenucatalogoencuentascrearencuesta" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogoencuentascrearencuesta">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.encuestas.crear.encuentas.altas" id="idcatalogos.encuestas.crear.encuentas.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.encuestas.crear.encuentas.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.encuestas.crear.encuentas.bajas" id="idcatalogos.encuestas.crear.encuentas.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.encuestas.crear.encuentas.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.encuestas.crear.encuentas.cambios" id="idcatalogos.encuestas.crear.encuentas.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.encuestas.crear.encuentas.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Folios Factura</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="menucatalogofoliosfiscalesfoliosfacturas" id="idmenucatalogofoliosfiscalesfoliosfacturas" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idmenucatalogofoliosfiscalesfoliosfacturas">Acceso</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.folios.fiscales.folios.facturas.altas" id="idcatalogos.folios.fiscales.folios.facturas.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.folios.fiscales.folios.facturas.altas">Altas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.folios.fiscales.folios.facturas.bajas" id="idcatalogos.folios.fiscales.folios.facturas.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.folios.fiscales.folios.facturas.bajas">Bajas</label>'+
                                        '&nbsp;&nbsp;<input type="checkbox" name="catalogos.folios.fiscales.folios.facturas.cambios" id="idcatalogos.folios.fiscales.folios.facturas.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                        '<label for="idcatalogos.folios.fiscales.folios.facturas.cambios">Cambios</label>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-6">'+
                                    '<label>Acceso a todos los catálogos en menu</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="accesotodosloscatalogosenmenu" id="idaccesotodosloscatalogosenmenu" class="filled-in" value="1" onchange="marcaraccesosentodosloscatalogos()"/>'+
                                        '<label for="idaccesotodosloscatalogosenmenu">Marcar</label>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<label>Altas,Bajas,Cambios en todos los catálogos</label>'+
                                    '<div class="col-md-12 form-check">'+ 
                                        '<input type="checkbox" name="crudentodosloscatalogos" id="idcrudentodosloscatalogos" class="filled-in" value="1" onchange="marcarcrudentodosloscatalogos()"/>'+
                                        '<label for="idcrudentodosloscatalogos">Marcar</label>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                        '<div role="tabpanel" class="tab-pane fade" id="tabliberar">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<small><b style="color:#F44336 !important;">*</b> Liberar</small>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                        '<div role="tabpanel" class="tab-pane fade" id="tabsat">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<small><b style="color:#F44336 !important;">*</b> Sat</small>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                        '<div role="tabpanel" class="tab-pane fade" id="tabseries">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<small><b style="color:#F44336 !important;">*</b> Series</small>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                        '<div role="tabpanel" class="tab-pane fade" id="tabalmacenes">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<small><b style="color:#F44336 !important;">*</b> Almacenes</small>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                    '</div>';
        $("#tabsformpermisos").html(tabs);
        $("#ModalPermisos").modal('show');
        $('.page-loader-wrapper').css('display', 'none');
        $("#id_usuario_permisos").val(id);
        //accesos al menu
        $.each(data.array_submenus,function(key, registro) {
            if(registro[1] == "true"){
               $("input[name='"+registro[0]+"']").prop('checked', true);
            } 
        });
        //permisos crud
        $.each(data.array_permisos_crud,function(key, registro) {
            if(registro[1] == "true"){
               $("input[name='"+registro[0]+"']").prop('checked', true);
            } 
        });
    })
}
//marcar todos los aceesos de los registros
function marcaraccesosentodoslosregistros(){
    if( $('#idaccesotodoslosregistrosenmenu').prop('checked') ) {
        $(".accesoregistros").prop('checked', true);
    }else{
        $(".accesoregistros").prop('checked', false);
    }
    construirarraysubmenus();
    construirarraypermisoscrud();
}
//marcar todas las casillas de altas, bajas y modificaciones de los registros
function marcarcrudentodoslosregistros(){
    if( $('#idcrudentodoslosregistros').prop('checked') ) {
        $(".crudregistros").prop('checked', true);
    }else{
        $(".crudregistros").prop('checked', false);
    }
    construirarraysubmenus();
    construirarraypermisoscrud();
}
//marcador todos los accesos de los catalogos
function marcaraccesosentodosloscatalogos(){
    if( $('#idaccesotodosloscatalogosenmenu').prop('checked') ) {
        $(".accesocatalogos").prop('checked', true);
    }else{
        $(".accesocatalogos").prop('checked', false);
    }
    construirarraysubmenus();
    construirarraypermisoscrud();
}
//marcar todas las casillas de altas, bajas y modificaciones de los catalogos
function marcarcrudentodosloscatalogos(){
    if( $('#idcrudentodosloscatalogos').prop('checked') ) {
        $(".crudcatalogos").prop('checked', true);
    }else{
        $(".crudcatalogos").prop('checked', false);
    }
    construirarraysubmenus();
    construirarraypermisoscrud();
}
//permitir o reestringir acceso al menu
function construirarraysubmenus(){
    var string_submenus = "";
    var lista = document.getElementsByClassName("submenu");
    for (var i = 0; i < lista.length; i++) {
        if(lista[i].checked){
            string_submenus= string_submenus+"-"+lista[i].name+",true";
        }else{
            string_submenus= string_submenus+"-"+lista[i].name+",false";
        }
    }
    $("#string_submenus").val(string_submenus);
}
//asignar o quitar permisos en altas bajas y modificaciones
function construirarraypermisoscrud(){
    var string_permisos_crud = "";
    var lista = document.getElementsByClassName("permisoscrud");
    for (var i = 0; i < lista.length; i++) {

        if(lista[i].checked){
            string_permisos_crud= string_permisos_crud+"-"+lista[i].name+",true";
        }else{
            string_permisos_crud= string_permisos_crud+"-"+lista[i].name+",false";
        }
    }
    $("#string_permisos_crud").val(string_permisos_crud);
}
//guardar los permisos
$("#btnGuardarPermisos").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formpermisos")[0]);
    var form = $("#formpermisos");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:usuarios_guardar_permisos,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                msj_datosguardadoscorrectamente();
                limpiar();
                $("#ModalPermisos").modal('hide');
                limpiarmodales();

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