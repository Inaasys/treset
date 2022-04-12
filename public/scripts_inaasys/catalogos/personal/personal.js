'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
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
      keys: true,
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
        ajax: personal_obtener,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'id', name: 'id', orderable: false, searchable: true },
            { data: 'nombre', name: 'nombre', orderable: false, searchable: true },
            { data: 'fecha_ingreso', name: 'fecha_ingreso', orderable: false, searchable: true },
            { data: 'tipo_personal', name: 'tipo_personal', orderable: false, searchable: true },
            { data: 'status', name: 'status', orderable: false, searchable: true }
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
                $(".inputbusquedageneral").val(""); 
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
//cargar usuarios y tecnicos del sistema
function cargarusuariosytecnicos(){
    $('.page-loader-wrapper').css('display', 'block');
    //formulario alta
    var tabs =  '<div class="row">'+
                    '<div class="col-md-12 table-responsive">'+
                        '<table id="tablausuariosytecnicos" class="table table-bordered tablausuariosytecnicos">'+
                            '<thead class="'+background_tables+'">'+
                                '<tr>'+
                                    '<th>Operaciones</th>'+
                                    '<th >Nombre</th>'+
                                    '<th >Fecha Ingreso</th>'+
                                    '<th class="customercolortheadth">Tipo Personal</th>'+
                                '</tr>'+
                            '</thead>'+
                            '<tbody>'+           
                            '</tbody>'+
                        '</table>'+
                    '</div>'+
                '</div>';
    $("#tablausuariosytecnicos").html(tabs);
    $("#ModalCargarUsuariosYTecnicos").modal('show');
    $.get(personal_obtener_usuarios_y_tecnicos, function(data){
        $("#tablausuariosytecnicos tbody").html(data);
        $('.page-loader-wrapper').css('display', 'none');
    })
}
//eliminar una fila en la tabla de personal
function eliminarfilapersonal(numerofila){
    $("#filapersonal"+numerofila).remove();
}
//dar de baja o alta registro
function desactivar(numeropersonal){
  $("#numeropersonal").val(numeropersonal);
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
      url:personal_alta_o_baja,
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
function obtenerdatos(numeropersonal){
  $("#titulomodal").html('Modificación Personal');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(personal_obtener_personal,{numeropersonal:numeropersonal },function(data){
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
                        '<label>Nombre</label>'+
                        '<input type="text" class="form-control" name="nombre" id="nombre" required onkeyup="tipoLetra(this);">'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-6">'+
                        '<label>Tipo Personal</label>'+
                        '<select name="tipopersonal" id="tipopersonal" class="form-control" style="width:100% !important;">'+
                        '</select>'+
                      '</div>'+
                    '</div>'+
                  '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //colocar autocomplette off  todo el formulario
    $(".form-control").attr('autocomplete','off');
    //boton formulario 
    $("#id").val(numeropersonal);
    $("#nombre").val(data.personal.nombre);
    $("#tipopersonal").html(data.tipopersonal);
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
      url:personal_guardar_modificacion,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        msj_datosguardadoscorrectamente();
        limpiar();
        ocultarmodalformulario();
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