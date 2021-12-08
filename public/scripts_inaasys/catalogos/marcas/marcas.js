'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  $.get(marcas_obtener_ultimo_numero, function(numero){
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
        ajax: marcas_obtener,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero', orderable: false, searchable: true },
            { data: 'Nombre', name: 'Nombre', orderable: false, searchable: true },
            { data: 'Utilidad1', name: 'Utilidad1', orderable: false, searchable: true },
            { data: 'Utilidad2', name: 'Utilidad2', orderable: false, searchable: true },
            { data: 'Utilidad3', name: 'Utilidad3', orderable: false, searchable: true },
            { data: 'Utilidad4', name: 'Utilidad4', orderable: false, searchable: true },
            { data: 'Utilidad5', name: 'Utilidad5', orderable: false, searchable: true },
            { data: 'Status', name: 'Status', orderable: false, searchable: true }
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
      obtenerdatos(data.Numero);
    }); 
}
//validacion de utilidades
function validautilidad1(){
  var utilidad1 = $("#utilidad1").val();
  if(parseFloat(utilidad1) > 0){
    $("#utilidad2").removeAttr('data-parsley-max');  
    $("#utilidad2").attr('data-parsley-max', utilidad1);  
    $("#utilidad2").removeAttr('readonly');
  }
}
//validacion de utilidades
function validautilidad2(){
  var utilidad2 = $("#utilidad2").val();
  if(parseFloat(utilidad2) > 0){
    $("#utilidad3").removeAttr('data-parsley-max');  
    $("#utilidad3").attr('data-parsley-max', utilidad2);  
    $("#utilidad3").removeAttr('readonly');
  }
}
function validautilidad3(){
  var utilidad3 = $("#utilidad3").val();
  if(parseFloat(utilidad3) > 0){
    $("#utilidad4").removeAttr('data-parsley-max');  
    $("#utilidad4").attr('data-parsley-max', utilidad3);
    $("#utilidad4").removeAttr('readonly');
  }
    
}
function validautilidad4(){
  var utilidad4 = $("#utilidad4").val();
  if(parseFloat(utilidad4) > 0){
    $("#utilidad5").removeAttr('data-parsley-max');  
    $("#utilidad5").attr('data-parsley-max', utilidad4); 
    $("#utilidad5").removeAttr('readonly');
  } 
}
//alta clientes
function alta(){
    $("#titulomodal").html('Alta Marca');
    mostrarmodalformulario('ALTA');
    mostrarformulario();
    //formulario alta
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#utilidades" data-toggle="tab">Utilidades</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="utilidades">'+
                        '<div class="row">'+
                            '<div class="col-md-2">'+
                                '<label>Utilidad 1 %</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="utilidad1" id="utilidad1" value="0.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);validautilidad1();">'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Utilidad 2 %</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="utilidad2" id="utilidad2" value="0.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);validautilidad2();" readonly>'+
                                '<ul id="utilidad2labelerror" style="display:none">'+
                                    '<li>la utilidad2 no debe ser mayor a la utilidad1</li>'+
                                '</ul>'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Utilidad 3 %</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="utilidad3" id="utilidad3" value="0.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);validautilidad3();" readonly>'+
                                '<ul id="utilidad3labelerror" style="display:none">'+
                                    '<li>la utilidad3 no debe ser mayor a la utilidad2</li>'+
                                '</ul>'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Utilidad 4 %</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="utilidad4" id="utilidad4" value="0.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/"  onchange="formatocorrectoinputcantidades(this);validautilidad4();" readonly>'+
                                '<ul id="utilidad4labelerror" style="display:none">'+
                                    '<li>la utilidad4 no debe ser mayor a la utilidad3</li>'+
                                '</ul>'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Utilidad 5 %</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="utilidad5" id="utilidad5" value="0.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'+
                                '<ul id="utilidad5labelerror" style="display:none">'+
                                    '<li>la utilidad5 no debe ser mayor a la utilidad4</li>'+
                                '</ul>'+
                            '</div>'+
                        '</div>'+   
                    '</div>'+
                '</div>';
  $("#tabsform").html(tabs);
  obtenultimonumero();
  setTimeout(function(){$("#nombre").focus();},500);  
  //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
  $(".inputnext").keypress(function (e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    var index = $(this).index(".inputnext");          
        $(".inputnext").eq(index + 1).focus().select(); 
    }
  });  
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
      url:marcas_guardar,
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
//dar de baja o alta registro
function desactivar(numeromarca){
  $("#numeromarca").val(numeromarca);
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
      url:marcas_alta_o_baja,
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
function obtenerdatos(numeromarca){
  $("#titulomodal").html('Modificación Marca');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(marcas_obtener_marca,{numeromarca:numeromarca },function(data){
    //formulario modificacion
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#utilidades" data-toggle="tab">Utilidades</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="utilidades">'+
                        '<div class="row">'+
                            '<div class="col-md-2">'+
                                '<label>Utilidad 1 %</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="utilidad1" id="utilidad1" value="0.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);validautilidad1();">'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Utilidad 2 %</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="utilidad2" id="utilidad2" value="0.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);validautilidad2();" >'+
                                '<ul id="utilidad2labelerror" style="display:none">'+
                                    '<li>la utilidad2 no debe ser mayor a la utilidad1</li>'+
                                '</ul>'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Utilidad 3 %</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="utilidad3" id="utilidad3" value="0.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);validautilidad3();" >'+
                                '<ul id="utilidad3labelerror" style="display:none">'+
                                    '<li>la utilidad3 no debe ser mayor a la utilidad2</li>'+
                                '</ul>'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Utilidad 4 %</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="utilidad4" id="utilidad4" value="0.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);validautilidad4();" >'+
                                '<ul id="utilidad4labelerror" style="display:none">'+
                                    '<li>la utilidad4 no debe ser mayor a la utilidad3</li>'+
                                '</ul>'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Utilidad 5 %</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="utilidad5" id="utilidad5" value="0.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" >'+
                                '<ul id="utilidad5labelerror" style="display:none">'+
                                    '<li>la utilidad5 no debe ser mayor a la utilidad4</li>'+
                                '</ul>'+
                            '</div>'+
                        '</div>'+   
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //boton formulario 
    $("#numero").val(numeromarca);
    $("#nombre").val(data.marca.Nombre);
    $("#utilidad1").val(data.utilidad1);
    $("#utilidad2").val(data.utilidad2);
    $("#utilidad2").attr('data-parsley-max', data.utilidad1);
    $("#utilidad3").val(data.utilidad3);
    $("#utilidad3").attr('data-parsley-max', data.utilidad2);
    $("#utilidad4").val(data.utilidad4);
    $("#utilidad4").attr('data-parsley-max', data.utilidad3);
    $("#utilidad5").val(data.utilidad5);
    $("#utilidad5").attr('data-parsley-max', data.utilidad4);
    setTimeout(function(){$("#nombre").focus();},500);  
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnext").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      var index = $(this).index(".inputnext");          
          $(".inputnext").eq(index + 1).focus().select(); 
      }
    });  
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
      url:marcas_guardar_modificacion,
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