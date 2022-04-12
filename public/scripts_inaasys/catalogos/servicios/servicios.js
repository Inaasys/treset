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
        ajax: servicios_obtener,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Codigo', name: 'Codigo', orderable: false, searchable: true },
            { data: 'Servicio', name: 'Servicio', orderable: false, searchable: true },
            { data: 'Unidad', name: 'Unidad', orderable: false, searchable: true },
            { data: 'NumeroFamilia', name: 'NumeroFamilia', orderable: false, searchable: true },
            { data: 'Familia', name: 'Familia', orderable: false, searchable: true },
            { data: 'Costo', name: 'Costo', orderable: false, searchable: true },
            { data: 'Venta', name: 'Venta', orderable: false, searchable: true },
            { data: 'Cantidad', name: 'Cantidad', orderable: false, searchable: true },
            { data: 'ClaveProducto', name: 'ClaveProducto', orderable: false, searchable: true },
            { data: 'ClaveUnidad', name: 'ClaveUnidad', orderable: false, searchable: true },
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
                $(".inputbusquedageneral").val(""); 
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
function listarfamilias(){
    ocultarformulario();
    var tablafamilias = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Familias</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadofamilia" class="tbllistadofamilia table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Número</th>'+
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
    $("#contenidomodaltablas").html(tablafamilias);
    var tfam = $('#tbllistadofamilia').DataTable({
        keys: true,
        "pageLength": 250,
        "sScrollX": "110%",
        "sScrollY": "300px",
        "bScrollCollapse": true,  
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: servicios_obtener_familias,
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
                  $('#tbllistadofamilia').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
    //seleccionar registro al dar doble click
    $('#tbllistadofamilia tbody').on('dblclick', 'tr', function () {
        var data = tfam.row( this ).data();
        seleccionarfamilia(data.Numero, data.Nombre);
    });
}
function seleccionarfamilia(Numero, Nombre){
    var familiaanterior = $("#familiaanterior").val();
    var familia = Numero;
    if(familiaanterior != familia){
        $("#familia").val(Numero);
        $("#familiaanterior").val(Numero);
        if(Nombre != null){
        $("#textonombrefamilia").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
    }
}
//obtener por numero
function obtenerfamiliapornumero(){
    var familiaanterior = $("#familiaanterior").val();
    var familia = $("#familia").val();
    if(familiaanterior != familia){
        if($("#familia").parsley().isValid()){
            $.get(servicios_obtener_familia_por_numero, {familia:familia}, function(data){
                $("#familia").val(data.numero);
                $("#familiaanterior").val(data.numero);
                if(data.nombre != null){
                    $("#textonombrefamilia").html(data.nombre.substring(0, 40));
                }
            }) 
        }
    }
}
//regresar numero 
function regresarnumerofamilia(){
    var familiaanterior = $("#familiaanterior").val();
    $("#familia").val(familiaanterior);
}
//listar claves productos
function listarclavesproductos(){
    ocultarformulario();
    var tablaclavesproductos =  '<div class="modal-header '+background_forms_and_modals+'">'+
                                    '<h4 class="modal-title">Claves Productos</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row">'+
                                        '<div class="col-md-12">'+
                                            '<div class="table-responsive">'+
                                                '<table id="tbllistadoclaveproducto" class="tbllistadoclaveproducto table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                    '<thead class="'+background_tables+'">'+
                                                        '<tr>'+
                                                            '<th>Operaciones</th>'+
                                                            '<th>Clave</th>'+
                                                            '<th>Nombre</th>'+
                                                            '<th>Usual</th>'+
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
    $("#contenidomodaltablas").html(tablaclavesproductos);
    var tclavprod = $('#tbllistadoclaveproducto').DataTable({
        keys: true,
        "pageLength": 250,
        "sScrollX": "110%",
        "sScrollY": "300px",
        "bScrollCollapse": true,  
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: servicios_obtener_claves_productos,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Clave', name: 'Clave' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Usual', name: 'Usual', orderable: false, searchable: false  }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoclaveproducto').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
    //seleccionar registro al dar doble click
    $('#tbllistadoclaveproducto tbody').on('dblclick', 'tr', function () {
        var data = tclavprod.row( this ).data();
        seleccionarclaveproducto(data.Clave, data.Nombre);
    });
}
function seleccionarclaveproducto(Clave, Nombre){
    var claveproductoanterior = $("#claveproductoanterior").val();
    var claveproducto = Clave;
    if(claveproductoanterior != claveproducto){
        $("#claveproducto").val(Clave);
        $("#claveproductoanterior").val(Clave);
        if(Nombre != null){
        $("#textonombreclaveproducto").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
    }
    $("#claveproducto").val(Clave);
    $("#nombreclaveproducto").val(Nombre);
    $("#nombreclaveproducto").keyup();
    mostrarformulario();
}
//obtener por clave 
function obtenerclaveproductoporclave(){
    var claveproductoanterior = $("#claveproductoanterior").val();
    var claveproducto = $("#claveproducto").val();
    if(claveproductoanterior != claveproducto){
        if($("#claveproducto").parsley().isValid()){
            $.get(servicios_obtener_clave_producto_por_clave, {claveproducto:claveproducto}, function(data){
                $("#claveproducto").val(data.clave);
                $("#claveproductoanterior").val(data.clave);
                if(data.nombre != null){
                    $("#textonombreclaveproducto").html(data.nombre.substring(0, 40));
                }
            }) 
        }
    }
}
//regresar clave 
function regresarclaveproducto(){
    var claveproductoanterior = $("#claveproductoanterior").val();
    $("#claveproducto").val(claveproductoanterior);
}
//listar claves unidades
function listarclavesunidades(){
    ocultarformulario();
    var tablaclavesunidades =   '<div class="modal-header '+background_forms_and_modals+'">'+
                                    '<h4 class="modal-title">Claves Unidades</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row">'+
                                        '<div class="col-md-12">'+
                                            '<div class="table-responsive">'+
                                                '<table id="tbllistadoclaveunidad" class="tbllistadoclaveunidad table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                    '<thead class="'+background_tables+'">'+
                                                        '<tr>'+
                                                            '<th>Operaciones</th>'+
                                                            '<th>Clave</th>'+
                                                            '<th>Nombre</th>'+
                                                            '<th>Descripción</th>'+
                                                            '<th>Usual</th>'+
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
    $("#contenidomodaltablas").html(tablaclavesunidades);
    var tclavuni = $('#tbllistadoclaveunidad').DataTable({
        keys: true,
        "pageLength": 250,
        "sScrollX": "110%",
        "sScrollY": "300px",
        "bScrollCollapse": true,  
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: servicios_obtener_claves_unidades,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Clave', name: 'Clave' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Descripcion', name: 'Descripcion', orderable: false, searchable: false  },
            { data: 'Usual', name: 'Usual', orderable: false, searchable: false  }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoclaveunidad').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
    //seleccionar registro al dar doble click
    $('#tbllistadoclaveunidad tbody').on('dblclick', 'tr', function () {
        var data = tclavuni.row( this ).data();
        seleccionarclaveunidad(data.Clave, data.Nombre);
    });
}
function seleccionarclaveunidad(Clave, Nombre){
    var claveunidadanterior = $("#claveunidadanterior").val();
    var claveunidad = Clave;
    if(claveunidadanterior != claveunidad){
        $("#claveunidad").val(Clave);
        $("#claveunidadanterior").val(Clave);
        if(Nombre != null){
        $("#textonombreclaveunidad").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
    }
}
//obtener por clave 
function obtenerclaveunidadporclave(){
    var claveunidadanterior = $("#claveunidadanterior").val();
    var claveunidad = $("#claveunidad").val();
    if(claveunidadanterior != claveunidad){
        if($("#claveunidad").parsley().isValid()){
            $.get(servicios_obtener_clave_unidad_por_clave, {claveunidad:claveunidad}, function(data){
                $("#claveunidad").val(data.clave);
                $("#claveunidadanterior").val(data.clave);
                if(data.nombre != null){
                    $("#textonombreclaveunidad").html(data.nombre.substring(0, 40));
                }
            }) 
        }
    }
}
//regresar clave
function regresarclaveunidad(){
    var claveunidadanterior = $("#claveunidadanterior").val();
    $("#claveunidad").val(claveunidadanterior);
}

//alta
function alta(){
    $("#titulomodal").html('Alta Servicio');
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
                            '<div class="col-md-6">'+
                                '<label>Familia<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombrefamilia"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarfamilia" class="btn bg-blue waves-effect" onclick="listarfamilias()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+ 
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="familia" id="familia" required onkeyup="tipoLetra(this)">'+
                                            '<input type="hidden" class="form-control" name="nombrefamilia" id="nombrefamilia" readonly>'+
                                            '<input type="hidden" class="form-control" name="familiaanterior" id="familiaanterior" required>'+
                                        '</div>'+
                                    '</div>'+    
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<label>Clave Producto<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreclaveproducto"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarclavesproductos" class="btn bg-blue waves-effect" onclick="listarclavesproductos()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+  
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="claveproducto" id="claveproducto" required onkeyup="tipoLetra(this)">'+
                                            '<input type="hidden" class="form-control" name="claveproductoanterior" id="claveproductoanterior" required data-parsley-length="[1, 20]">'+
                                            '<input type="hidden" class="form-control" name="nombreclaveproducto" id="nombreclaveproducto" readonly>'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+ 
                            '<div class="col-md-6">'+
                                '<label>Clave Unidad<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreclaveunidad"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarclavesunidad" class="btn bg-blue waves-effect" onclick="listarclavesunidades()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+  
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="claveunidad" id="claveunidad" required onkeyup="tipoLetra(this)">'+
                                            '<input type="hidden" class="form-control" name="claveunidadanterior" id="claveunidadanterior" required data-parsley-length="[1, 5]" >'+
                                            '<input type="hidden" class="form-control" name="nombreclaveunidad" id="nombreclaveunidad" readonly>'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Servicio<b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control inputnext" name="servicio" id="servicio" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Unidad<b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control inputnext" name="unidad" id="unidad" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this);">'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+   
                            '<div class="col-md-4">'+
                                '<label>Cantidad</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="cantidad" id="cantidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Costo</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+  
                            '<div class="col-md-4">'+
                                '<label>Venta</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="venta" id="venta" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                        '</div>'+  
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //colocar autocomplette off  todo el formulario
    $(".form-control").attr('autocomplete','off');
    setTimeout(function(){$("#codigo").focus();},500);  
    $("#codigo").removeAttr('readonly');
    //activar busqueda
    $('#familia').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerfamiliapornumero();
        }
    });
    //regresar clave
    $('#familia').on('change', function(e) {
          regresarnumerofamilia();
    });
    //activar busqueda
    $('#claveproducto').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclaveproductoporclave();
        }
    });
    //regresar clave
    $('#claveproducto').on('change', function(e) {
          regresarclaveproducto();
    });
    //activar busqueda
    $('#claveunidad').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclaveunidadporclave();
        }
    });
    //regresar clave
    $('#claveunidad').on('change', function(e) {
          regresarclaveunidad();
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnext").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnext");          
      switch(code){
        case 13:
          $(".inputnext").eq(index + 1).focus().select(); 
          break;
        case 39:
          $(".inputnext").eq(index + 1).focus().select(); 
          break;
        case 37:
          $(".inputnext").eq(index - 1).focus().select(); 
          break;
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
            url:servicios_guardar,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                if(data == 1){
                    msj_errorcodigoexistente();
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
function desactivar(codigoservicio){
  $("#codigoservicio").val(codigoservicio);
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
            url:servicios_alta_o_baja,
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
function obtenerdatos(codigoservicio){
    $("#titulomodal").html('Modificación Servicio');
    $('.page-loader-wrapper').css('display', 'block');
    $.get(servicios_obtener_servicio,{codigoservicio:codigoservicio },function(data){
    //formulario modificacion
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#datosgenerales" data-toggle="tab">Datos Generales</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="datosgenerales">'+
                        '<div class="row">'+
                            '<div class="col-md-6">'+
                                '<label>Familia<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombrefamilia"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarfamilia" class="btn bg-blue waves-effect" onclick="listarfamilias()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+ 
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="familia" id="familia" required onkeyup="tipoLetra(this)">'+
                                            '<input type="hidden" class="form-control" name="nombrefamilia" id="nombrefamilia" readonly>'+
                                            '<input type="hidden" class="form-control" name="familiaanterior" id="familiaanterior" required readonly>'+
                                        '</div>'+
                                    '</div>'+    
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<label>Clave Producto<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreclaveproducto"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarclavesproductos" class="btn bg-blue waves-effect" onclick="listarclavesproductos()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+  
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="claveproducto" id="claveproducto" required onkeyup="tipoLetra(this);">'+
                                            '<input type="hidden" class="form-control" name="claveproductoanterior" id="claveproductoanterior" required data-parsley-length="[1, 20]">'+
                                            '<input type="hidden" class="form-control" name="nombreclaveproducto" id="nombreclaveproducto" readonly>'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+ 
                            '<div class="col-md-6">'+
                                '<label>Clave Unidad<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombreclaveunidad"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarclavesunidad" class="btn bg-blue waves-effect" onclick="listarclavesunidades()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+  
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="claveunidad" id="claveunidad" required onkeyup="tipoLetra(this)">'+
                                            '<input type="hidden" class="form-control" name="claveunidadanterior" id="claveunidadanterior" required data-parsley-length="[1, 5]">'+
                                            '<input type="hidden" class="form-control" name="nombreclaveunidad" id="nombreclaveunidad" readonly>'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Servicio<b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control inputnext" name="servicio" id="servicio" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Unidad<b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control inputnext" name="unidad" id="unidad" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this);">'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+   
                            '<div class="col-md-4">'+
                                '<label>Cantidad</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="cantidad" id="cantidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Costo</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+  
                            '<div class="col-md-4">'+
                                '<label>Venta</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="venta" id="venta" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                        '</div>'+  
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //colocar autocomplette off  todo el formulario
    $(".form-control").attr('autocomplete','off');
    //boton formulario 
    $("#codigo").val(codigoservicio);
    $("#servicio").val(data.servicio.Servicio);
    $("#unidad").val(data.servicio.Unidad);
    $("#cantidad").val(data.cantidad);
    $("#costo").val(data.costo);
    $("#venta").val(data.venta);
    if(data.familia != null){
        $("#familia").val(data.familia.Numero);
        $("#nombrefamilia").val(data.familia.Nombre);
        $("#textonombrealmacen").html(data.familia.Nombre.substring(0, 40));
    }
    if(data.claveproducto != null){
        $("#claveproducto").val(data.claveproducto.Clave);
        $("#nombreclaveproducto").val(data.claveproducto.Nombre);
        $("#textonombrealmacen").html(data.claveproducto.Nombre.substring(0, 40));
    }
    if(data.claveunidad != null){
        $("#claveunidad").val(data.claveunidad.Clave);
        $("#nombreclaveunidad").val(data.claveunidad.Nombre);
        $("#textonombrealmacen").html(data.claveunidad.Nombre.substring(0, 40));
    }
    setTimeout(function(){$("#codigo").focus();},500); 
    $("#codigo").attr('readonly', 'readonly') 
    //activar busqueda
    $('#familia').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerfamiliapornumero();
        }
    });
    //regresar clave
    $('#familia').on('change', function(e) {
          regresarnumerofamilia();
    });
    //activar busqueda
    $('#claveproducto').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclaveproductoporclave();
        }
    });
    //regresar clave
    $('#claveproducto').on('change', function(e) {
          regresarclaveproducto();
    });
    //activar busqueda
    $('#claveunidad').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclaveunidadporclave();
        }
    });
    //regresar clave
    $('#claveunidad').on('change', function(e) {
          regresarclaveunidad();
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnext").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnext");          
      switch(code){
        case 13:
          $(".inputnext").eq(index + 1).focus().select(); 
          break;
        case 39:
          $(".inputnext").eq(index + 1).focus().select(); 
          break;
        case 37:
          $(".inputnext").eq(index - 1).focus().select(); 
          break;
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
            url:servicios_guardar_modificacion,
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