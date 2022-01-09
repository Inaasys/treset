'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
function retraso(){
    return new Promise(resolve => setTimeout(resolve, 500));
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
    //agregar inputs de busqueda por columna
    $('#tbllistado tfoot th').each( function () {
      var title = $(this).text();
      if(title != 'Operaciones'){
        $(this).html( '<input type="text" placeholder="Buscar en columna '+title+'" />' );
      }
    });
    tabla=$('#tbllistado').DataTable({
        "lengthMenu": [ 100, 250, 500, 1000 ],
        "pageLength": 1000,
        "sScrollX": "110%",
        "sScrollY": "350px",
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: usuarios_obtener,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'id', name: 'id', orderable: false, searchable: true },
            { data: 'name', name: 'name', orderable: false, searchable: true },
            { data: 'email', name: 'email', orderable: false, searchable: true },
            { data: 'user', name: 'user', orderable: false, searchable: true  },
            { data: 'role_id', name: 'role_id', orderable: false, searchable: true  }
        ],
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
        obtenerdatos(data.id);
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
                                '<input type="text" class="form-control" name="user" id="user" required onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
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
                                '<input type="text" class="form-control" name="user" id="user" required readonly onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
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
//permisos del usuario
function permisos(id){
    $('.page-loader-wrapper').css('display', 'block');
    $.get(usuarios_obtener_permisos,{id:id},function(data){
        //formulario modificacion
        $("#titulomodalpermisos").html("Permisos: "+data.name);
        var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                        '<li role="presentation" class="active">'+
                            '<a href="#tabregistros" data-toggle="tab">Registros</a>'+
                        '</li>'+
                        '<li role="presentation">'+
                            '<a href="#tabcatalogos" data-toggle="tab">Catálogos</a>'+
                        '</li>'+
                        '<li role="presentation">'+
                            '<a href="#tabreportes" data-toggle="tab">Reportes</a>'+
                        '</li>'+
                        '<li role="presentation">'+
                            '<a href="#tabtimbrar" data-toggle="tab">Timbrar</a>'+
                        '</li>'+
                    '</ul>'+
                    '<div class="tab-content">'+
                        '<div role="tabpanel" class="tab-pane fade in active" id="tabregistros" >'+
                            '<div style="height: 450px;overflow-y: scroll;padding: 0px 0px;">'+
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.ordenescompra.firmar" id="idregistros.ordenescompra.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.ordenescompra.firmar">Firmar</label>'+
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.compras.firmar" id="idregistros.compras.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.compras.firmar">Firmar</label>'+                                      
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.contrarecibos.firmar" id="idregistros.contrarecibos.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.contrarecibos.firmar">Firmar</label>'+   
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cotizaciones.firmar" id="idregistros.cotizaciones.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cotizaciones.firmar">Firmar</label>'+ 
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Lista Precios Volvo</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="menuregistroslistapreciosvolvo" id="idmenuregistroslistapreciosvolvo" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idmenuregistroslistapreciosvolvo">Acceso</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.listavolvo.cambios" id="idregistros.listavolvo.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.listavolvo.cambios">Cambios</label>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Lista Precios Cummins</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="menuregistroslistaprecioscummins" id="idmenuregistroslistaprecioscummins" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idmenuregistroslistaprecioscummins">Acceso</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.listacummins.cambios" id="idregistros.listacummins.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.listacummins.cambios">Cambios</label>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Cotizaciones Productos</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="menuregistroscotizacionesproductos" id="idmenuregistroscotizacionesproductos" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idmenuregistroscotizacionesproductos">Acceso</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cotizaciones.productos.altas" id="idregistros.cotizaciones.productos.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cotizaciones.productos.altas">Altas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cotizaciones.productos.bajas" id="idregistros.cotizaciones.productos.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cotizaciones.productos.bajas">Bajas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cotizaciones.productos.cambios" id="idregistros.cotizaciones.productos.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cotizaciones.productos.cambios">Cambios</label>'+ 
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cotizaciones.productos.firmar" id="idregistros.cotizaciones.productos.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cotizaciones.productos.firmar">Firmar</label>'+ 
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Cotizaciones Servicios</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="menuregistroscotizacionesservicios" id="idmenuregistroscotizacionesservicios" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idmenuregistroscotizacionesservicios">Acceso</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cotizaciones.servicios.altas" id="idregistros.cotizaciones.servicios.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cotizaciones.servicios.altas">Altas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cotizaciones.servicios.bajas" id="idregistros.cotizaciones.servicios.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cotizaciones.servicios.bajas">Bajas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cotizaciones.servicios.cambios" id="idregistros.cotizaciones.servicios.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cotizaciones.servicios.cambios">Cambios</label>'+ 
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cotizaciones.servicios.firmar" id="idregistros.cotizaciones.servicios.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cotizaciones.servicios.firmar">Firmar</label>'+ 
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.remisiones.firmar" id="idregistros.remisiones.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.remisiones.firmar">Firmar</label>'+ 
                                        '</div>'+
                                    '</div>'+
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.facturas.firmar" id="idregistros.facturas.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.facturas.firmar">Firmar</label>'+ 
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.produccion.firmar" id="idregistros.produccion.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.produccion.firmar">Firmar</label>'+ 
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.traspasos.firmar" id="idregistros.traspasos.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.traspasos.firmar">Firmar</label>'+ 
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.requisiciones.firmar" id="idregistros.requisiciones.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.requisiciones.firmar">Firmar</label>'+ 
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.ordenes.trabajo.terminar" id="idregistros.ordenes.trabajo.terminar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.ordenes.trabajo.terminar">Terminar</label>'+ 
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.ordenes.trabajo.firmar" id="idregistros.ordenes.trabajo.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.ordenes.trabajo.firmar">Firmar</label>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cuentas.x.cobrar.firmar" id="idregistros.cuentas.x.cobrar.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cuentas.x.cobrar.firmar">Firmar</label>'+ 
                                        '</div>'+
                                    '</div>'+
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cuentas.x.pagar.firmar" id="idregistros.cuentas.x.pagar.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cuentas.x.pagar.firmar">Firmar</label>'+ 
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.notas.credito.clientes.firmar" id="idregistros.notas.credito.clientes.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.notas.credito.clientes.firmar">Firmar</label>'+ 
                                        '</div>'+
                                    '</div>'+
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.notas.credito.proveedores.firmar" id="idregistros.notas.credito.proveedores.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.notas.credito.proveedores.firmar">Firmar</label>'+ 
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Carta Porte</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="menuregistroscartasporte" id="idmenuregistroscartasporte" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idmenuregistroscartasporte">Acceso</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cartasporte.altas" id="idregistros.cartasporte.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cartasporte.altas">Altas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cartasporte.bajas" id="idregistros.cartasporte.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cartasporte.bajas">Bajas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cartasporte.cambios" id="idregistros.cartasporte.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cartasporte.cambios">Cambios</label>'+ 
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.cartasporte.firmar" id="idregistros.cartasporte.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.cartasporte.firmar">Firmar</label>'+ 
                                        '</div>'+
                                    '</div>'+
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
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.ajustes.inventario.firmar" id="idregistros.ajustes.inventario.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.ajustes.inventario.firmar">Firmar</label>'+ 
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Asignación de Herramienta</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="menuregistrosasignacionherramienta" id="idmenuregistrosasignacionherramienta" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idmenuregistrosasignacionherramienta">Acceso</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.asignacion.herramienta.altas" id="idregistros.asignacion.herramienta.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.asignacion.herramienta.altas">Altas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.asignacion.herramienta.bajas" id="idregistros.asignacion.herramienta.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.asignacion.herramienta.bajas">Bajas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.asignacion.herramienta.cambios" id="idregistros.asignacion.herramienta.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.asignacion.herramienta.cambios">Cambios</label>'+ 
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.asignacion.herramienta.autorizar" id="idregistros.asignacion.herramienta.autorizar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.asignacion.herramienta.autorizar">Autorizar</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.asignacion.herramienta.auditoria.altas" id="idregistros.asignacion.herramienta.auditoria.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.asignacion.herramienta.auditoria.altas">Auditoria</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.asignacion.herramienta.firmar" id="idregistros.asignacion.herramienta.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.asignacion.herramienta.firmar">Firmar</label>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Prestamo de Herramienta</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="menuregistrosprestamoherramienta" id="idmenuregistrosprestamoherramienta" class="filled-in submenu accesoregistros" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idmenuregistrosprestamoherramienta">Acceso</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.prestamo.herramienta.altas" id="idregistros.prestamo.herramienta.altas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.prestamo.herramienta.altas">Altas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.prestamo.herramienta.bajas" id="idregistros.prestamo.herramienta.bajas" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.prestamo.herramienta.bajas">Bajas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.prestamo.herramienta.cambios" id="idregistros.prestamo.herramienta.cambios" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.prestamo.herramienta.cambios">Cambios</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.prestamo.herramienta.terminar" id="idregistros.prestamo.herramienta.terminar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.prestamo.herramienta.terminar">Terminar</label>'+ 
                                            '&nbsp;&nbsp;<input type="checkbox" name="registros.prestamo.herramienta.firmar" id="idregistros.prestamo.herramienta.firmar" class="filled-in crudregistros permisoscrud" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();" />'+
                                            '<label for="idregistros.prestamo.herramienta.firmar">Firmar</label>'+ 
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+ 
                            '<div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label class="col-red">Acceso a todos los registros en menu</label>'+
                                        '<div class="col-md-12 form-check">'+ 
                                            '<input type="checkbox" name="accesotodoslosregistrosenmenu" id="idaccesotodoslosregistrosenmenu" class="filled-in" value="1" onchange="marcaraccesosentodoslosregistros()"/>'+
                                            '<label for="idaccesotodoslosregistrosenmenu">Marcar</label>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label class="col-red">Altas,Bajas,Cambios en todos los registros</label>'+
                                        '<div class="col-md-12 form-check">'+ 
                                            '<input type="checkbox" name="crudentodoslosregistros" id="idcrudentodoslosregistros" class="filled-in" value="1" onchange="marcarcrudentodoslosregistros()"/>'+
                                            '<label for="idcrudentodoslosregistros">Marcar</label>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                        '<div role="tabpanel" class="tab-pane fade" id="tabcatalogos">'+
                            '<div style="height: 450px;overflow-y: scroll;padding: 0px 0px;">'+
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
                                    '<div class="col-md-6">'+
                                        '<label>Folios Notas</label>'+
                                        '<div class="col-md-12 form-check">'+ 
                                            '<input type="checkbox" name="menucatalogofoliosfiscalesfoliosnotas" id="idmenucatalogofoliosfiscalesfoliosnotas" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idmenucatalogofoliosfiscalesfoliosnotas">Acceso</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="catalogos.folios.fiscales.folios.notas.altas" id="idcatalogos.folios.fiscales.folios.notas.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idcatalogos.folios.fiscales.folios.notas.altas">Altas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="catalogos.folios.fiscales.folios.notas.bajas" id="idcatalogos.folios.fiscales.folios.notas.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idcatalogos.folios.fiscales.folios.notas.bajas">Bajas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="catalogos.folios.fiscales.folios.notas.cambios" id="idcatalogos.folios.fiscales.folios.notas.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idcatalogos.folios.fiscales.folios.notas.cambios">Cambios</label>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Folios Pagos</label>'+
                                        '<div class="col-md-12 form-check">'+ 
                                            '<input type="checkbox" name="menucatalogofoliosfiscalesfoliospagos" id="idmenucatalogofoliosfiscalesfoliospagos" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idmenucatalogofoliosfiscalesfoliospagos">Acceso</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="catalogos.folios.fiscales.folios.pagos.altas" id="idcatalogos.folios.fiscales.folios.pagos.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idcatalogos.folios.fiscales.folios.pagos.altas">Altas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="catalogos.folios.fiscales.folios.pagos.bajas" id="idcatalogos.folios.fiscales.folios.pagos.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idcatalogos.folios.fiscales.folios.pagos.bajas">Bajas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="catalogos.folios.fiscales.folios.pagos.cambios" id="idcatalogos.folios.fiscales.folios.pagos.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idcatalogos.folios.fiscales.folios.pagos.cambios">Cambios</label>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Folios Cartas Porte</label>'+
                                        '<div class="col-md-12 form-check">'+ 
                                            '<input type="checkbox" name="menucatalogofoliosfiscalesfolioscartasporte" id="idmenucatalogofoliosfiscalesfolioscartasporte" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idmenucatalogofoliosfiscalesfolioscartasporte">Acceso</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="catalogos.folios.fiscales.folios.cartasporte.altas" id="idcatalogos.folios.fiscales.folios.cartasporte.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idcatalogos.folios.fiscales.folios.cartasporte.altas">Altas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="catalogos.folios.fiscales.folios.cartasporte.bajas" id="idcatalogos.folios.fiscales.folios.cartasporte.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idcatalogos.folios.fiscales.folios.cartasporte.bajas">Bajas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="catalogos.folios.fiscales.folios.cartasporte.cambios" id="idcatalogos.folios.fiscales.folios.cartasporte.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idcatalogos.folios.fiscales.folios.cartasporte.cambios">Cambios</label>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Personal</label>'+
                                        '<div class="col-md-12 form-check">'+ 
                                            '<input type="checkbox" name="menucatalogopersonal" id="idmenucatalogopersonal" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idmenucatalogopersonal">Acceso</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="catalogos.personal.altas" id="idcatalogos.personal.altas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idcatalogos.personal.altas">Altas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="catalogos.personal.bajas" id="idcatalogos.personal.bajas" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idcatalogos.personal.bajas">Bajas</label>'+
                                            '&nbsp;&nbsp;<input type="checkbox" name="catalogos.personal.cambios" id="idcatalogos.personal.cambios" class="filled-in crudcatalogos permisoscrud" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idcatalogos.personal.cambios">Cambios</label>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Existencias</label>'+
                                        '<div class="col-md-12 form-check">'+ 
                                            '<input type="checkbox" name="menucatalogoexistencias" id="idmenucatalogoexistencias" class="filled-in submenu accesocatalogos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idmenucatalogoexistencias">Acceso</label>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label class="col-red">Acceso a todos los catálogos en menu</label>'+
                                        '<div class="col-md-12 form-check">'+ 
                                            '<input type="checkbox" name="accesotodosloscatalogosenmenu" id="idaccesotodosloscatalogosenmenu" class="filled-in" value="1" onchange="marcaraccesosentodosloscatalogos()"/>'+
                                            '<label for="idaccesotodosloscatalogosenmenu">Marcar</label>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label class="col-red">Altas,Bajas,Cambios en todos los catálogos</label>'+
                                        '<div class="col-md-12 form-check">'+ 
                                            '<input type="checkbox" name="crudentodosloscatalogos" id="idcrudentodosloscatalogos" class="filled-in" value="1" onchange="marcarcrudentodosloscatalogos()"/>'+
                                            '<label for="idcrudentodosloscatalogos">Marcar</label>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                        '<div role="tabpanel" class="tab-pane fade" id="tabreportes">'+
                            '<div style="height: 450px;overflow-y: scroll;padding: 0px 0px;">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Ordenes Compra</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureporterelacionordenescompra" id="idmenureporterelacionordenescompra" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacionordenescompra">Relación Ordenes Compra</label>'+
                                            '</div>'+
                                        '</div>'+
                                    '</div>'+ 
                                    '<div class="col-md-6">'+
                                        '<label>Compras</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureportescomprascajachica" id="idmenureportescomprascajachica" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureportescomprascajachica">Caja Chica</label>'+
                                            '</div>'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureporterelacioncompras" id="idmenureporterelacioncompras" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacioncompras">Relación Compras</label>'+
                                            '</div>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Contra Recibos</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureporterelacioncontrarecibos" id="idmenureporterelacioncontrarecibos" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacioncontrarecibos">Relación Contrarecibos</label>'+
                                            '</div>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Cotizaciones</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureporterelacioncotizaciones" id="idmenureporterelacioncotizaciones" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacioncotizaciones">Relación Cotizaciones</label>'+
                                            '</div>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Remisiones</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureporterelacionremisiones" id="idmenureporterelacionremisiones" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacionremisiones">Relación Remisiones</label>'+
                                            '</div>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-12">'+
                                        '<label>Facturas</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-3">'+
                                                '<input type="checkbox" name="menureporterelacionfacturasventasclientes" id="idmenureporterelacionfacturasventasclientes" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacionfacturasventasclientes">Relación Ventas Clientes</label>'+
                                            '</div>'+
                                            '<div class="col-md-3">'+
                                                '<input type="checkbox" name="menureporterelacionfacturasventasagentes" id="idmenureporterelacionfacturasventasagentes" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacionfacturasventasagentes">Relación Ventas Agentes</label>'+
                                            '</div>'+
                                            '<div class="col-md-3">'+
                                                '<input type="checkbox" name="menureporterelacionfacturasventasmarcas" id="idmenureporterelacionfacturasventasmarcas" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacionfacturasventasmarcas">Relación Ventas Marcas</label>'+
                                            '</div>'+
                                            '<div class="col-md-3">'+
                                                '<input type="checkbox" name="menureportefacturasantiguedadsaldos" id="idmenureportefacturasantiguedadsaldos" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureportefacturasantiguedadsaldos">Antiguedad Saldos</label>'+
                                            '</div>'+
                                            '<div class="col-md-3">'+
                                                '<input type="checkbox" name="menureportesfacturasventasdiarias" id="idmenureportesfacturasventasdiarias" class="filled-in submenu accesoreportes" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureportesfacturasventasdiarias">Ventas Diarias</label>'+    
                                            '</div>'+                                 
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Producción</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureporterelacionproduccion" id="idmenureporterelacionproduccion" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacionproduccion">Relación Producción</label>'+   
                                            '</div>'+  
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Comprobantes</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureporterelacioncomprobantes" id="idmenureporterelacioncomprobantes" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacioncomprobantes">Relación Comprobantes</label>'+
                                            '</div>'+  
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Requisiciones</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureporterelacionrequisiciones" id="idmenureporterelacionrequisiciones" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacionrequisiciones">Relación Requisiciones</label>'+
                                            '</div>'+  
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Ordenes Trabajo</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureportesordenestrabajohorastecnico" id="idmenureportesordenestrabajohorastecnico" class="filled-in submenu accesoreportes" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureportesordenestrabajohorastecnico">Horas Técnico</label>'+ 
                                            '</div>'+                                      
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Cuentas por Cobrar</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureporterelacioncxc" id="idmenureporterelacioncxc" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacioncxc">Relación Cuentas Por Cobrar</label>'+
                                            '</div>'+   
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Cuentas por Pagar</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureporterelacioncxp" id="idmenureporterelacioncxp" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacioncxp">Relación Cuentas Por Pagar</label>'+
                                            '</div>'+ 
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Notas Crédito Clientes</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureporterelacionnotasclientes" id="idmenureporterelacionnotasclientes" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacionnotasclientes">Relación Notas Clientes</label>'+
                                            '</div>'+ 
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Notas Credito Proveedores</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureporterelacionnotasproveedores" id="idmenureporterelacionnotasproveedores" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporterelacionnotasproveedores">Relación Notas Proveedores</label>'+
                                            '</div>'+ 
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-12">'+
                                        '<label>Iventario</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-4">'+
                                                '<input type="checkbox" name="menureportecostoinventario" id="idmenureportecostoinventario" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureportecostoinventario">Costo Inventario</label>'+
                                            '</div>'+ 
                                            '<div class="col-md-4">'+
                                                '<input type="checkbox" name="menureportecostoinventarioparametros" id="idmenureportecostoinventarioparametros" class="filled-in submenu accesoreportes" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureportecostoinventarioparametros">Costo Inventario (Ultimo Costo, Promedio y Más Alto)</label>'+ 
                                            '</div>'+ 
                                            '<div class="col-md-4">'+
                                                '<input type="checkbox" name="menureporteproductossincomprasyventas" id="idmenureporteproductossincomprasyventas" class="filled-in submenu accesoreportes" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporteproductossincomprasyventas">Productos sin Movimientos de Compras y Ventas</label>'+ 
                                            '</div>'+ 
                                            '<div class="col-md-4">'+
                                                '<input type="checkbox" name="menureportemovimientosalinventario" id="idmenureportemovimientosalinventario" class="filled-in submenu accesoreportes" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureportemovimientosalinventario">Movimientos al Inventario</label>'+ 
                                            '</div>'+ 
                                            '<div class="col-md-4">'+
                                                '<input type="checkbox" name="menureporteultimafechaycostosproductoscomprados" id="idmenureporteultimafechaycostosproductoscomprados" class="filled-in submenu accesoreportes" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporteultimafechaycostosproductoscomprados">Ultima Fecha y Costos de Productos Comprados</label>'+ 
                                            '</div>'+ 
                                            '<div class="col-md-4">'+
                                                '<input type="checkbox" name="menureporteultimafechaypreciosproductosfacturados" id="idmenureporteultimafechaypreciosproductosfacturados" class="filled-in submenu accesoreportes" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporteultimafechaypreciosproductosfacturados">Ultima Fecha y Precios de Productos Facturados</label>'+ 
                                            '</div>'+ 
                                            '<div class="col-md-4">'+
                                                '<input type="checkbox" name="menureporteinventariomaximosyminimos" id="idmenureporteinventariomaximosyminimos" class="filled-in submenu accesoreportes" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureporteinventariomaximosyminimos">Maximos y Minimos</label>'+ 
                                            '</div>'+ 
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Bitacoras</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureportebitacorasdocumentosyclientes" id="idmenureportebitacorasdocumentosyclientes" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureportebitacorasdocumentosyclientes">Bitacoras Documentos y Clientes</label>'+
                                            '</div>'+ 
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Usuarios</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<div class="col-md-6">'+
                                                '<input type="checkbox" name="menureportepermisosusuarios" id="idmenureportepermisosusuarios" class="filled-in submenu accesoreportes" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                                '<label for="idmenureportepermisosusuarios">Permisos Usuarios</label>'+
                                            '</div>'+ 
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label class="col-red">Acceso a todos los reportes en menu</label>'+
                                        '<div class="col-md-12 form-check">'+ 
                                            '<input type="checkbox" name="accesotodoslosreportesenmenu" id="idaccesotodoslosreportesenmenu" class="filled-in" value="1" onchange="marcaraccesosentodoslosreportes()"/>'+
                                            '<label for="idaccesotodoslosreportesenmenu">Marcar</label>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                        '<div role="tabtimbrar" class="tab-pane fade" id="tabtimbrar">'+
                            '<div style="height: 450px;overflow-y: scroll;padding: 0px 0px;">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Timbrar Facturas</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="registros.facturas.timbrar" id="idregistros.facturas.timbrar" class="filled-in permisoscrud timbrardocumentos" value="1"  onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idregistros.facturas.timbrar"></label>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Cancelar Timbres Facturas</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="registros.facturas.cancelartimbres" id="idregistros.facturas.cancelartimbres" class="filled-in permisoscrud cancelartimbresdocumentos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idregistros.facturas.cancelartimbres"></label>'+                                     
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Timbrar Notas</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="registros.notas.credito.clientes.timbrar" id="idregistros.notas.credito.clientes.timbrar" class="filled-in permisoscrud timbrardocumentos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idregistros.notas.credito.clientes.timbrar"></label>'+                                     
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Cancelar Timbres Notas</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="registros.notas.credito.clientes.cancelartimbres" id="idregistros.notas.credito.clientes.cancelartimbres" class="filled-in permisoscrud cancelartimbresdocumentos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idregistros.notas.credito.clientes.cancelartimbres"></label>'+                                     
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Timbrar Pagos</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="registros.cuentas.x.cobrar.timbrar" id="idregistros.cuentas.x.cobrar.timbrar" class="filled-in permisoscrud timbrardocumentos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idregistros.cuentas.x.cobrar.timbrar"></label>'+                                     
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Cancelar Timbres Pagos</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="registros.cuentas.x.cobrar.cancelartimbres" id="idregistros.cuentas.x.cobrar.cancelartimbres" class="filled-in permisoscrud cancelartimbresdocumentos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idregistros.cuentas.x.cobrar.cancelartimbres"></label>'+                                     
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Timbrar Cartas Porte</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="registros.cartasporte.timbrar" id="idregistros.cartasporte.timbrar" class="filled-in permisoscrud timbrardocumentos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idregistros.cartasporte.timbrar"></label>'+                                     
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Cancelar Timbres Cartas Porte</label>'+
                                        '<div class="col-md-12 form-check">'+
                                            '<input type="checkbox" name="registros.cartasporte.cancelartimbres" id="idregistros.cartasporte.cancelartimbres" class="filled-in permisoscrud cancelartimbresdocumentos" value="1" onchange="construirarraysubmenus();construirarraypermisoscrud();"/>'+
                                            '<label for="idregistros.cartasporte.cancelartimbres"></label>'+                                     
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label class="col-red">Timbrar todos los documentos (Facturas, Notas y Pagos)</label>'+
                                        '<div class="col-md-12 form-check">'+ 
                                            '<input type="checkbox" name="timbrartodoslosdocumentos" id="idtimbrartodoslosdocumentos" class="filled-in" value="1" onchange="marcartimbradoentodoslosdocumentos()"/>'+
                                            '<label for="idtimbrartodoslosdocumentos">Marcar</label>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label class="col-red">Cancelar timbres en todos los documentos (Facturas, Notas y Pagos)</label>'+
                                        '<div class="col-md-12 form-check">'+ 
                                            '<input type="checkbox" name="cancelartimbresentodoslosdocumentos" id="idcancelartimbresentodoslosdocumentos" class="filled-in" value="1" onchange="marcarcancelartimbresentodoslosdocumentos()"/>'+
                                            '<label for="idcancelartimbresentodoslosdocumentos">Marcar</label>'+
                                        '</div>'+
                                    '</div>'+
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
        construirarraysubmenus();
        construirarraypermisoscrud();
    }).fail( function() {
        msj_errorajax();
        $('.page-loader-wrapper').css('display', 'none');
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
//marcador todos los accesos de los reportes
function marcaraccesosentodoslosreportes(){
    if( $('#idaccesotodoslosreportesenmenu').prop('checked') ) {
        $(".accesoreportes").prop('checked', true);
    }else{
        $(".accesoreportes").prop('checked', false);
    }
    construirarraysubmenus();
    construirarraypermisoscrud();
}



//marcar timbrar en todos los documentos
function marcartimbradoentodoslosdocumentos(){
    if( $('#idtimbrartodoslosdocumentos').prop('checked') ) {
        $(".timbrardocumentos").prop('checked', true);
    }else{
        $(".timbrardocumentos").prop('checked', false);
    }
    construirarraysubmenus();
    construirarraypermisoscrud();
}
//marcr cancelar timbres en todos los documentos
function marcarcancelartimbresentodoslosdocumentos(){
    if( $('#idcancelartimbresentodoslosdocumentos').prop('checked') ) {
        $(".cancelartimbresdocumentos").prop('checked', true);
    }else{
        $(".cancelartimbresdocumentos").prop('checked', false);
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
//series documentos
function seriesusuariodocumentos(id,usuario){
    $("#titulomodalserie").html("Series Documentos: "+usuario);
    $("#ModalSeriesDocumentos").modal('show');
    $("#formularioserie").hide();
    $("#tablasmodalserie").show();
    var tablaseries =   '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<div class="row clearfix">'+
                                '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">'+
                                    '<h5>Series Documentos</h5>'+
                                '</div>'+
                                '<div class="col-lg-10 col-md-10 col-sm-10 col-xs-12 button-demo">'+
                                    '<div class="table-responsive">'+
                                        '<table>'+
                                            '<tr>'+
                                                '<td >'+
                                                    '<div class="btn bg-blue btn-xs waves-effect" onclick="altaserie(\''+usuario+'\')">'+
                                                        'Nueva Serie'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+
                                        '</table>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoseriedocumento" class="tbllistadoseriedocumento table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                '<th>Operaciones</th>'+
                                                '<th>Documento</th>'+
                                                '<th>Serie</th>'+
                                                '<th>Nombre</th>'+
                                                '<th>Usuario</th>'+
                                                '<th>Item</th>'+
                                                '</tr>'+
                                            '</thead>'+
                                            '<tbody></tbody>'+
                                        '</table>'+
                                    '</div>'+
                                '</div>'+  
                            '</div>'+
                        '</div>'+
                        '<div class="modal-footer">'+
                          '<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>'+
                        '</div>';
    $("#tablasmodalserie").html(tablaseries);
    $('#tbllistadoseriedocumento').DataTable({
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
            url: usuarios_obtener_series_documentos_usuario,
            data: function (d) {
                d.id = id;
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false  },
            { data: 'Documento', name: 'Documento' },
            { data: 'Serie', name: 'Serie', orderable: false, searchable: false  },
            { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false  },
            { data: 'Usuario', name: 'Usuario', orderable: false, searchable: false  },
            { data: 'Item', name: 'Item', orderable: false, searchable: false  }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoseriedocumento').DataTable().search( this.value ).draw();
                }
            });
        },
    });
}
//obtener tipos documentos
function obtenerdocumentos(){
    $.get(usuarios_obtener_tipos_documentos, function(select_tipos_documentos){
      $("#documento").html(select_tipos_documentos);
    })  
}
//alta de serie
function altaserie(usuario){
    $("#titulomodalserie").html('Alta Serie Documento');
    $("#formularioserie").show();
    $("#tablasmodalserie").hide();
    $("#btnGuardarSerie").show();
    $("#btnGuardarModificacionSerie").hide();
    //formulario alta
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                    '<a href="#datos" data-toggle="tab">Datos</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="datos">'+
                        '<div class="row">'+
                            '<div class="col-md-3">'+
                                '<label>Documento</label>'+
                                '<select class="form-control select2" name="documento" id="documento" required style="width::100% !important;" data-parsley-length="[1, 30]">'+
                                '</select>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Serie</label>'+
                                '<input type="text" class="form-control" name="serie" id="serie" required data-parsley-pattern="^[A-Z]+$" data-parsley-length="[1, 10]" onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Nombre</label>'+
                                '<input type="text" class="form-control" name="nombre" id="nombre" required onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]">'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Usuario</label>'+
                                '<input type="text" class="form-control" name="usuario" id="usuario" value="'+usuario+'" required readonly data-parsley-length="[1, 20]">'+
                            '</div>'+
                        '</div>'+
                    '</div>'+ 
                '</div>';
    $("#tabsformserie").html(tabs);  
    obtenerdocumentos();
    $("#documento").select2();
}
//mostrar lsitado series
function mostrarlistadoseriesdocumentos(){
    $("#ModalSeriesDocumentos").modal('show');
    $("#formularioserie").hide();
    $("#tablasmodalserie").show();
}
//guardar el registro
$("#btnGuardarSerie").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formparsleyserie")[0]);
    var form = $("#formparsleyserie");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:usuarios_guardar_serie_documento,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                if(data == 1){
                    msj_errorserieexistenteendocumento();
                }else{
                    msj_datosguardadoscorrectamente();
                    $("#formularioserie").hide();
                    $("#tablasmodalserie").show();
                    var tabla = $('#tbllistadoseriedocumento').DataTable();
                    tabla.ajax.reload();
                    $('.page-loader-wrapper').css('display', 'block');
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
//obtener serie documento
function obtenerdatosserie(documento, serie, usuario, nombre){
    $('.page-loader-wrapper').css('display', 'block');
    $("#titulomodalserie").html('Modificación Serie Documento');
    $("#formularioserie").show();
    $("#tablasmodalserie").hide();
    $("#btnGuardarSerie").hide();
    $("#btnGuardarModificacionSerie").show();
        //formulario modificacion
        var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                        '<li role="presentation" class="active">'+
                        '<a href="#datos" data-toggle="tab">Datos</a>'+
                        '</li>'+
                    '</ul>'+
                    '<div class="tab-content">'+
                        '<div role="tabpanel" class="tab-pane fade in active" id="datos">'+
                            '<div class="row">'+
                                '<div class="col-md-3">'+
                                    '<label>Documento</label>'+
                                    '<select class="form-control select2" name="documento" id="documento" required style="width::100% !important;" data-parsley-length="[1, 30]">'+
                                    '</select>'+
                                '</div>'+
                                '<div class="col-md-3">'+
                                    '<label>Serie</label>'+
                                    '<input type="text" class="form-control" name="serie" id="serie" required readonly data-parsley-pattern="^[A-Z]+$" data-parsley-length="[1, 10]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-3">'+
                                    '<label>Nombre</label>'+
                                    '<input type="text" class="form-control" name="nombre" id="nombre" required onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]">'+
                                '</div>'+
                                '<div class="col-md-3">'+
                                    '<label>Usuario</label>'+
                                    '<input type="text" class="form-control" name="usuario" id="usuario" value="'+usuario+'" required readonly data-parsley-length="[1, 20]">'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                    '</div>';
        $("#tabsformserie").html(tabs);  
        $("#serie").val(serie);
        $("#nombre").val(nombre);
        $("#usuario").val(usuario);
        obtenerdocumentos();
        seleccionartipodocumento(documento);
}
async function seleccionartipodocumento(documento){
    await retraso();
    $("#documento").val(documento).change();
    $("#documento").select2();
    $('.page-loader-wrapper').css('display', 'none');
}
//cambios
$("#btnGuardarModificacionSerie").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formparsleyserie")[0]);
    var form = $("#formparsleyserie");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:usuarios_guardar_modificacion_serie_documento,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                if(data == 1){
                    msj_errorserieexistenteendocumento();
                }else{
                    msj_datosguardadoscorrectamente();
                    $("#formularioserie").hide();
                    $("#tablasmodalserie").show();
                    var tabla = $('#tbllistadoseriedocumento').DataTable();
                    tabla.ajax.reload();
                    $('.page-loader-wrapper').css('display', 'block');
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