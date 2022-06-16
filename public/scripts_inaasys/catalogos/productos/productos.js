'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
    campos_a_filtrar_en_busquedas();
    listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 2000));
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
        $('#producto').prop('readOnly', false);
    }else if(tipo == 'MODIFICACION'){
        $("#btnGuardar").hide();
        $("#btnGuardarModificacion").show();
        $('#producto').prop('readOnly', true);
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
        keys: true,
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
        ajax: productos_obtener,
        columns: campos_tabla,
        "drawCallback": function( data ) {
            $("#sumacostofiltrado").html(number_format(round(data.json.sumacosto, numerodecimales), numerodecimales, '.', ''));
            $("#sumaultimocostofiltrado").html(number_format(round(data.json.sumaultimocosto, numerodecimales), numerodecimales, '.', ''));
            $("#sumaultimaventafiltrado").html(number_format(round(data.json.sumaultimaventa, numerodecimales), numerodecimales, '.', ''));
            $("#sumapreciofiltrado").html(number_format(round(data.json.sumaprecio, numerodecimales), numerodecimales, '.', ''));
            $("#sumaexistenciasfiltrado").html(number_format(round(data.json.sumaexistencias, numerodecimales), numerodecimales, '.', ''));
        },
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
//obtener tipos prod
function obtenertipos(){
    $.get(productos_obtener_tipos_prod, function(select_tipos){
      $("#tipo").html(select_tipos);
    })
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
        ajax: productos_obtener_claves_productos,
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
}
//obtener por clave
function obtenerclaveproductoporclave(){
    var claveproductoanterior = $("#claveproductoanterior").val();
    var claveproducto = $("#claveproducto").val();
    if(claveproductoanterior != claveproducto){
        if($("#claveproducto").parsley().isValid()){
            $.get(productos_obtener_clave_producto_por_clave, {claveproducto:claveproducto}, function(data){
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
        ajax: productos_obtener_claves_unidades,
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
            $.get(productos_obtener_clave_unidad_por_clave, {claveunidad:claveunidad}, function(data){
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
//listar marcas
function listarmarcas(){
    ocultarformulario();
    var tablamarcas =   '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Marcas</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadomarca" class="tbllistadomarca table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Número</th>'+
                                                    '<th>Nombre</th>'+
                                                    '<th>Util 1 %</th>'+
                                                    '<th>Util 2 %</th>'+
                                                    '<th>Util 3 %</th>'+
                                                    '<th>Util 4 %</th>'+
                                                    '<th>Util 5 %</th>'+
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
    $("#contenidomodaltablas").html(tablamarcas);
    var tmarc = $('#tbllistadomarca').DataTable({
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
        ajax: productos_obtener_marcas,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Utilidad1', name: 'Utilidad1', orderable: false, searchable: false  },
            { data: 'Utilidad2', name: 'Utilidad2', orderable: false, searchable: false  },
            { data: 'Utilidad3', name: 'Utilidad3', orderable: false, searchable: false  },
            { data: 'Utilidad4', name: 'Utilidad4', orderable: false, searchable: false  },
            { data: 'Utilidad5', name: 'Utilidad5', orderable: false, searchable: false  }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadomarca').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
    //seleccionar registro al dar doble click
    $('#tbllistadomarca tbody').on('dblclick', 'tr', function () {
        var data = tmarc.row( this ).data();
        seleccionarmarca(data.Numero, data.Nombre);
    });
}
function seleccionarmarca(Numero, Nombre){
    var marcaanterior = $("#marcaanterior").val();
    var marca = Numero;
    if(marcaanterior != marca){
        $("#marca").val(Numero);
        $("#marcaanterior").val(Numero);
        if(Nombre != null){
        $("#textonombremarca").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
        reiniciartablautilidades();
    }
}
//obtener por numero
function obtenermarcapornumero(){
    var marcaanterior = $("#marcaanterior").val();
    var marca = $("#marca").val();
    if(marcaanterior != marca){
        if($("#marca").parsley().isValid()){
            $.get(productos_obtener_marca_por_numero, {marca:marca}, function(data){
                $("#marca").val(data.numero);
                $("#marcaanterior").val(data.numero);
                if(data.nombre != null){
                    $("#textonombremarca").html(data.nombre.substring(0, 40));
                }
            })
        }
    }
}
//regresar numero
function regresarmarca(){
    var marcaanterior = $("#marcaanterior").val();
    $("#marca").val(marcaanterior);
}
//listar lineas
function listarlineas(){
    ocultarformulario();
    var tablalineas =   '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Lineas</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadolinea" class="tbllistadolinea table table-bordered table-striped table-hover" style="width:100% !important;">'+
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
    $("#contenidomodaltablas").html(tablalineas);
    var tlin = $('#tbllistadolinea').DataTable({
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
        ajax: productos_obtener_lineas,
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
                  $('#tbllistadolinea').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
    //seleccionar registro al dar doble click
    $('#tbllistadolinea tbody').on('dblclick', 'tr', function () {
        var data = tlin.row( this ).data();
        seleccionarlinea(data.Numero, data.Nombre);
    });
}
function seleccionarlinea(Numero, Nombre){
    var lineaanterior = $("#lineaanterior").val();
    var linea = Numero;
    if(lineaanterior != linea){
        $("#linea").val(Numero);
        $("#lineaanterior").val(Numero);
        if(Nombre != null){
            $("#textonombrelinea").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
    }
}
//obtener por numero
function obtenerlineapornumero(){
    var lineaanterior = $("#lineaanterior").val();
    var linea = $("#linea").val();
    if(lineaanterior != linea){
        if($("#linea").parsley().isValid()){
            $.get(productos_obtener_linea_por_numero, {linea:linea}, function(data){
                $("#linea").val(data.numero);
                $("#lineaanterior").val(data.numero);
                if(data.nombre != null){
                    $("#textonombrelinea").html(data.nombre.substring(0, 40));
                }
            })
        }
    }
}
//regresar numero
function regresarlinea(){
    var lineaanterior = $("#lineaanterior").val();
    $("#linea").val(lineaanterior);
}
//listar monedas
function listarmonedas(){
    ocultarformulario();
    var tablalineas =   '<div class="modal-header '+background_forms_and_modals+'">'+
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
                            '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                        '</div>';
    $("#contenidomodaltablas").html(tablalineas);
    var tmon = $('#tbllistadomoneda').DataTable({
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
        ajax: productos_obtener_monedas,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Clave', name: 'Clave' },
            { data: 'Nombre', name: 'Nombre' }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadomoneda').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
    //seleccionar registro al dar doble click
    $('#tbllistadomoneda tbody').on('dblclick', 'tr', function () {
        var data = tmon.row( this ).data();
        seleccionarmoneda(data.Clave, data.Nombre);
    });
}
function seleccionarmoneda(Clave, Nombre){
    var monedaanterior = $("#monedaanterior").val();
    var moneda = Clave;
    if(monedaanterior != moneda){
        $("#moneda").val(Clave);
        $("#monedaanterior").val(Clave);
        if(Nombre != null){
            $("#textonombremoneda").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
    }
}
//obtener por clave
function obtenermonedaporclave(){
    var monedaanterior = $("#monedaanterior").val();
    var moneda = $("#moneda").val();
    if(monedaanterior != moneda){
        if($("#moneda").parsley().isValid()){
            $.get(productos_obtener_moneda_por_clave, {moneda:moneda}, function(data){
                $("#moneda").val(data.clave);
                $("#monedaanterior").val(data.clave);
                if(data.nombre != null){
                    $("#textonombremoneda").html(data.nombre.substring(0, 40));
                }
            })
        }
    }
}
//regresar numero
function regresarmoneda(){
    var monedaanterior = $("#monedaanterior").val();
    $("#moneda").val(monedaanterior);
}
//detectar cuando en el input de buscar por numero de cliente el usuario presione la tecla enter, si es asi se realizara la busqueda con el numero escrito
function activarbusquedacliente(){
    var buscarnumerocliente = $('#numeroabuscar');
    buscarnumerocliente.unbind();
    buscarnumerocliente.bind('keyup change', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            buscardatosfilacliente();
        }
    });
}
//listar clientes para pestana precio clientes
function listarclientes(){
    ocultarformulario();
    var tablaclientes = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Clientes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadocliente" class="tbllistadocliente table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Nombre</th>'+
                                                    '<th>R.F.C.</th>'+
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
                            '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                        '</div>';
    $("#contenidomodaltablas").html(tablaclientes);
    $('#tbllistadocliente').DataTable({
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
        ajax: {
            url: productos_obtener_clientes,
            data: function (d) {
              d.numeroabuscar = $("#numeroabuscar").val();
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Rfc', name: 'Rfc', orderable: false, searchable: false },
            { data: 'Municipio', name: 'Municipio', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadocliente').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
}
function buscardatosfilacliente(){
    var numeroabuscar = $("#numeroabuscar").val();
    $.get(productos_obtener_datos_cliente_agregar_fila,{numeroabuscar:numeroabuscar}, function(data){
        if(data.existecliente > 0){
            agregarfilacliente(data.numero, data.nombre);
        }
        $("#numeroabuscar").val("");
    })
}
//detectar cuando en el input de buscar por codigo de producto el usuario presione la tecla enter, si es asi se realizara la busqueda con el codigo escrito
function activarbusquedaproducto(){
    var buscarcodigoproducto = $('#codigoabuscar');
    buscarcodigoproducto.unbind();
    buscarcodigoproducto.bind('keyup change', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            buscardatosfilaproducto();
        }
    });
  }
//listar productos para tab consumos
function listarproductos(){
    ocultarformulario();
    var tablaconsumos = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Productos</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoconsumoproducto" class="tbllistadoconsumoproducto table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Código</th>'+
                                                    '<th>Marca</th>'+
                                                    '<th>Producto</th>'+
                                                    '<th>Almacen</th>'+
                                                    '<th>Ubicación</th>'+
                                                    '<th>Existen</th>'+
                                                    '<th>Costo $</th>'+
                                                    '<th>Sub Total $</th>'+
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
    $("#contenidomodaltablas").html(tablaconsumos);
    $('#tbllistadoconsumoproducto').DataTable({
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
        ajax: {
            url: productos_obtener_productos_consumos,
            data: function (d) {
              d.codigoabuscar = $("#codigoabuscar").val();
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Codigo', name: 'Codigo' },
            { data: 'Marca', name: 'Marca', orderable: false, searchable: false  },
            { data: 'Producto', name: 'Producto', orderable: false, searchable: false },
            { data: 'Almacen', name: 'Almacen', orderable: false, searchable: false },
            { data: 'Ubicacion', name: 'Ubicacion', orderable: false, searchable: false },
            { data: 'Existencias', name: 'Existencias', orderable: false, searchable: false },
            { data: 'Costo', name: 'Costo', orderable: false, searchable: false },
            { data: 'SubTotal', name: 'SubTotal', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoconsumoproducto').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
}
function buscardatosfilaproducto(){
    var codigoabuscar = $("#codigoabuscar").val();
    $.get(productos_obtener_datos_producto_agregar_fila,{codigoabuscar:codigoabuscar}, function(data){
        if(data.existeproducto > 0){
            agregarfilaconsumos(data.codigo, data.nombreproducto, data.unidad, data.inventariable, data.costo, data.venta);
        }
        $("#codigoabuscar").val("");
    })

}
//listar utilidades
function listarutilidades(){
    var numeromarca = $("#marca").val();
    var costo = $("#costo").val();
    var impuesto = $("#impuesto").val();
    $.get(productos_obtener_utilidades,{numeromarca:numeromarca,costo:costo,impuesto:impuesto },function(filasutilidadesproducto){
        $('#tbllistadoutilidades tbody').html(filasutilidadesproducto);
    });
}
//reiniciar la tabla de utilidades
function reiniciartablautilidades(){
    listarutilidades();
}
//listar las existencias por almacen
function listarexistenciasalmacenes(){
    var codigo = $("#codigo").val();
    $.get(productos_obtener_existencias_almacenes,{codigo:codigo },function(filasexistenciasalmacen){
        $('#tbllistadoexistenciaalmacen').append(filasexistenciasalmacen);
    });
}
//agregar una fila en la tabla de precios clientes
var contadorpreciosclientes=0;
function agregarfilacliente(Numero, Nombre){
    var fila=   '<tr class="filaspreciosclientes" id="filapreciocliente'+contadorpreciosclientes+'">'+
                    '<td><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosclientes('+contadorpreciosclientes+')">X</div></td>'+
                    '<td><input type="hidden" name="numerocliente[]" id="numerocliente[]" value="'+Numero+'" readonly>'+Numero+'</td>'+
                    '<td><input type="hidden" name="nombrecliente[]" id="nombrecliente[]" value="'+Nombre+'" readonly>'+Nombre+'</td>'+
                    '<td><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" name="precioproductocliente[]" id="precioproductocliente[]" required value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);"></td>'+
                '</tr>';
    contadorpreciosclientes++;
    $("#tablapreciosclientes").append(fila);
    mostrarformulario();
    comprobarfilaspreciosclientes();
}
//eliminar una fila en la tabla de precios clientes
function eliminarfilapreciosclientes(numerofila){
    $("#filapreciocliente"+numerofila).remove();
    comprobarfilaspreciosclientes();
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilaspreciosclientes(){
    var numerofilas = $("#tablapreciosclientes tbody tr").length;
    $("#numerofilasprecioscliente").val(numerofilas);
}
//agregar una fila en la tabla de la tab consumos
var contadorconsumos=0;
function agregarfilaconsumos(Codigo, Producto, Unidad, Inventariable, Costo, Precio){
    var fila=   '<tr class="filasconsumos" id="filaconsumo'+contadorconsumos+'">'+
                    '<td><div class="btn btn-danger btn-xs" onclick="eliminarfilaconsumos('+contadorconsumos+')">X</div></td>'+
                    '<td><input type="hidden" name="codigoproductoconsumos[]" id="codigoproductoconsumos[]" value="'+Codigo+'" readonly>'+Codigo+'</td>'+
                    '<td><input type="hidden" name="productoconsumos[]" id="productoconsumos[]" value="'+Producto+'" readonly>'+Producto+'</td>'+
                    '<td><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" name="cantidadproductoconsumos[]" id="cantidadproductoconsumos[]" required value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);"></td>'+
                    '<td><input type="hidden" name="unidadconsumos[]" id="unidadconsumos[]" value="'+Unidad+'" readonly>'+Unidad+'</td>'+
                    '<td><input type="hidden" name="inventariableconsumos[]" id="inventariableconsumos[]" value="'+Inventariable+'" readonly>'+Inventariable+'</td>'+
                    '<td><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" name="costoconsumos[]" id="costoconsumos[]" required value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+Costo+'</td>'+
                    '<td><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" name="precionetoconsumos[]" id="precionetoconsumos[]" required value="'+Precio+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+Precio+'</td>'+
                '</tr>';
    contadorconsumos++;
    $("#tablaconsumosproductoterminado").append(fila);
    mostrarformulario();
    comprobarfilasconsumos();
}
//eliminar una fila en la tabla de consumos
function eliminarfilaconsumos(numerofila){
    $("#filaconsumo"+numerofila).remove();
    comprobarfilasconsumos();
}
//comprobar numero filas de la tabla de consumos
function comprobarfilasconsumos(){
    var numerofilas = $("#tablaconsumosproductoterminado tbody tr").length;
    $("#numerofilasconsumosproductoterminado").val(numerofilas);
}
//buscar si el codigo escrito ya esta en catalogo
function buscarcodigoencatalogo(){
    var codigo = $("#codigo").val();
    $.get(productos_buscar_codigo_en_tabla,{codigo:codigo },function(existecodigo){
        if(existecodigo > 0){
            msj_errorcodigoexistente();
        }
    });
}
//alta
function alta(){
    $("#titulomodal").html('Alta Producto');
    mostrarmodalformulario('ALTA');
    mostrarformulario();
    //formulario alta
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#productotab" data-toggle="tab">Producto</a>'+
                    '</li>'+
                    '<li role="presentation">'+
                        '<a href="#codigosalternostab" data-toggle="tab">Código Alternos</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="productotab">'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Marca<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombremarca"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarmarcas" class="btn bg-blue waves-effect" onclick="listarmarcas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+
                                    '<div class="col-md-8">'+
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="marca" id="marca" required>'+
                                            '<input type="hidden" class="form-control" name="marcaanterior" id="marcaanterior" required readonly>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Linea<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombrelinea"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarlineas" class="btn bg-blue waves-effect" onclick="listarlineas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+
                                    '<div class="col-md-8">'+
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="linea" id="linea" required>'+
                                            '<input type="hidden" class="form-control" name="lineaanterior" id="lineaanterior" required readonly>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Impuesto % <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="impuesto" id="impuesto" required value="16.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Costo (De última compra sin impuesto)</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Precio (Precio neto)</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="precio" id="precio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Ubicación</label>'+
                                '<input type="text" class="form-control inputnext" name="ubicacion" id="ubicacion" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this)" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Tipo Producto</label>'+
                                '<select name="tipo" id="tipo" class="form-control select2" style="width:100% !important;" required>'+
                                '</select>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="codigosalternostab">'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Código 1</label>'+
                                '<input type="text" class="form-control inputnext" name="codigo1" id="codigo1" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Código 2</label>'+
                                '<input type="text" class="form-control inputnext" name="codigo2" id="codigo2" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Código 3</label>'+
                                '<input type="text" class="form-control inputnext" name="codigo3" id="codigo3" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Código 4</label>'+
                                '<input type="text" class="form-control inputnext" name="codigo4" id="codigo4" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Código 5</label>'+
                                '<input type="text" class="form-control inputnext" name="codigo5" id="codigo5" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //colocar autocomplette off  todo el formulario
    $(".form-control").attr('autocomplete','off');
    obtenertipos();
    $("#codigo").removeAttr('readonly');
    setTimeout(function(){$("#codigo").focus();},500);
    seleccionarclaveunidad('H87','Pieza');
    //activar busqueda para clave producto
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
    //activar busqueda para clave unidad
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
    //activar busqueda para marca
    $('#marca').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenermarcapornumero();
        }
    });
    //regresar numero
    $('#marca').on('change', function(e) {
        regresarmarca();
    });
    //activar busqueda para linea
    $('#linea').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerlineapornumero();
        }
    });
    //regresar numero
    $('#linea').on('change', function(e) {
        regresarlinea();
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
            url:productos_guardar,
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
function desactivar(codigoproducto){
  $("#codigoproducto").val(codigoproducto);
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
            url:productos_alta_o_baja,
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
function obtenerdatos(codigoproducto){
    $("#titulomodal").html('Modificación Producto');
    $('.page-loader-wrapper').css('display', 'block');
    $.get(productos_obtener_producto,{codigoproducto:codigoproducto },function(data){
    //formulario modificacion
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#productotab" data-toggle="tab">Producto</a>'+
                    '</li>'+
                    '<li role="presentation" >'+
                        '<a href="#precioclientestab" data-toggle="tab">Precio Clientes</a>'+
                    '</li>'+
                    '<li role="presentation" >'+
                        '<a href="#consumostab" data-toggle="tab">Consumos</a>'+
                    '</li>'+
                    '<li role="presentation" >'+
                        '<a href="#fechastab" data-toggle="tab">Otros datos</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="productotab">'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Marca<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombremarca"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarmarcas" class="btn bg-blue waves-effect" onclick="listarmarcas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+
                                    '<div class="col-md-8">'+
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="marca" id="marca" required>'+
                                            '<input type="hidden" class="form-control" name="marcaanterior" id="marcaanterior" required readonly>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Linea<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombrelinea"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarlineas" class="btn bg-blue waves-effect" onclick="listarlineas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+
                                    '<div class="col-md-8">'+
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="linea" id="linea" required>'+
                                            '<input type="hidden" class="form-control" name="lineaanterior" id="lineaanterior" required readonly>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Impuesto % <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="impuesto" id="impuesto" required value="16.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onkeyup="reiniciartablautilidades();" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Costo (De última compra sin impuesto)</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onkeyup="reiniciartablautilidades();" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Ubicación</label>'+
                                '<input type="text" class="form-control inputnext" name="ubicacion" id="ubicacion" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this)" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Tipo Producto</label>'+
                                '<select name="tipo" id="tipo" class="form-control select2 " style="width:100% !important;" required>'+
                                '</select>'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-3">'+
                                '<label>Costo de Lista</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="costodelista" id="costodelista" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Moneda<span class="label label-danger" id="textonombremoneda"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarmonedas" class="btn bg-blue waves-effect" onclick="listarmonedas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+
                                    '<div class="col-md-8">'+
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="moneda" id="moneda" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this);">'+
                                            '<input type="hidden" class="form-control" name="monedaanterior" id="monedaanterior" readonly required data-parsley-length="[1, 5]">'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-2" id="divcodigodebarras">'+
                                data.barcode+
                            '</div>'+
                            '<div class="col-md-1">'+
                                '<label>Num. Impre.</label>'+
                                '<input type="number" class="form-control inputnext" name="numimpresiones" id="numimpresiones" value="1" >'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Tamaño Etiquetas</label>'+
                                '<select id="tamanoetiquetas" name="tamanoetiquetas" class="form-control">'+
                                    '<!--<option value="chica">chica</option>-->'+
                                    '<option value="grande">grande</option>'+
                                '</select>'+
                            '</div>'+
                            '<div class="col-md-1">'+
                                '<label>Imprimir</label>'+
                                '<div id="botoncambiardatosimprimircodigobarras" class="btn bg-blue btn-block waves-effect" onclick="imprimircodigosbarras();">Imprimir</div>'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-7">'+
                                '<label>UTILIDADES '+tipodeutilidad+'</label>'+
                                '<div class="table-container" style="height: 14em !important;">'+
                                    '<table id="tbllistadoutilidades" class="scroll tbllistadoutilidades">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                                '<th>Costo de Venta</th>'+
                                                '<th>Utilidad%</th>'+
                                                '<th>Utilidad$</th>'+
                                                '<th>Subtotal$</th>'+
                                                '<th>Iva$</th>'+
                                                '<th>Total$</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-5">'+
                                '<label>EXISTENCIAS</label>'+
                                '<div class="table-container" style="height: 14em !important;">'+
                                    '<table id="tbllistadoexistenciaalmacen" class="scroll tbllistadoexistenciaalmacen">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                                '<th>Almacén</th>'+
                                                '<th>Nombre</th>'+
                                                '<th>Existen</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="precioclientestab">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<div class="col-md-4">'+
                                    '<h5>PRECIOS A CLIENTES PARA ESTE PRODUCTO&nbsp;&nbsp;&nbsp;</h5>'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<div id="botonbuscarclientes" class="btn btn-block bg-blue waves-effect" onclick="listarclientes()">Ver Clientes</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<input type="text" class="form-control" name="numeroabuscar" id="numeroabuscar" placeholder="Escribe el número de cliente y presiona enter">'+
                                    '<input type="hidden" class="form-control" name="numerofilasprecioscliente" id="numerofilasprecioscliente">'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<div class="table-container">'+
                                    '<table id="tablapreciosclientes" class="scroll tablapreciosclientes">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                            '<th>Operaciones</th>'+
                                            '<th>Cliente</th>'+
                                            '<th>Nombre</th>'+
                                            '<th>Precio $</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="consumostab">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<div class="col-md-12 form-check">'+
                                    '<label>Producto Terminado (Pt)</label>'+
                                    '<input type="radio" name="consumosproductoterminado" id="consumosproductoterminado" value="S">'+
                                    '<label for="consumosproductoterminado">SI</label>'+
                                    '<input type="radio" name="consumosproductoterminado" id="consumosproductoterminado1" value="N" checked>'+
                                    '<label for="consumosproductoterminado1">NO</label>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-12">'+
                                '<div class="col-md-4">'+
                                    '<h5>CONSUMOS DE PRODUCTO TERMINADO&nbsp;&nbsp;&nbsp;</h5>'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<div id="botonbuscarproductos" class="btn btn-block bg-blue waves-effect" onclick="listarproductos()">Ver Productos</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto y presiona enter" onkeyup="tipoLetra(this)">'+
                                    '<input type="hidden" class="form-control" name="numerofilasconsumosproductoterminado" id="numerofilasconsumosproductoterminado">'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-12">'+
                                '<div class="table-container">'+
                                    '<table id="tablaconsumosproductoterminado" class="scroll tablaconsumosproductoterminado">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                                '<th>Operaciones</th>'+
                                                '<th>Código</th>'+
                                                '<th>Producto</th>'+
                                                '<th>Cantidad $</th>'+
                                                '<th>Unidad</th>'+
                                                '<th>Inventariable</th>'+
                                                '<th>Costo</th>'+
                                                '<th>Precio Neto $</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="fechastab">'+
                        '<div class="row">'+
                            '<div class="col-md-6">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Comisión %</label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="fechascomision" id="fechascomision" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Descuento %</label>'+
                                        '<input type="text" class="form-control inputnext" name="fechasdescuento" id="fechasdescuento" value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Mínimos </label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="fechasminimos" id="fechasminimos" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Máximos </label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="fechasmaximos" id="fechasmaximos" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Costo Máximo </label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="fechascostomaximo" id="fechascostomaximo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Fecha Ultima Compra</label>'+
                                        '<input type="date" class="form-control inputnext" name="fechasfechaultimacompra" id="fechasfechaultimacompra" readonly>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Ultimo Costo $</label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="fechasultimocosto" id="fechasultimocosto" readonly value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Fecha Ultima Venta</label>'+
                                        '<input type="date" class="form-control inputnext" name="fechasfechaultimaventa" id="fechasfechaultimaventa" readonly>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Ultima Venta $</label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="fechasultimaventa" id="fechasultimaventa" readonly value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Supercedido </label>'+
                                        '<input type="text" class="form-control inputnext" name="fechassupercedido" id="fechassupercedido" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" >'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Insumo </label>'+
                                        '<input type="text" class="form-control inputnext" name="fechasinsumo" id="fechasinsumo" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<label>Descripción </label>'+
                                        '<textarea class="form-control inputnext" name="fechasdescripcion" id="fechasdescripcion" rows="1" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Cambiar Imagen</label>'+
                                        '<input type="file" name="imagen" id="imagen"  class="dropify" data-max-file-size="1M" data-allowed-file-extensions="jpg jpeg png gif" data-min-width="200" data-min-height="200"/>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Imagen Actual</label>'+
                                        '<div data-toggle="modal" data-target="#modalimagenes"><img src="#" id="imagenactual" style="object-fit: contain;" width="100%" height="200px"></div>'+
                                    '</div>'+

                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //colocar readonly si no puede modificar insumos
    var arrayusuariosamodificarinsumosproductos = usuariosamodificarinsumos.split(",");
    if(arrayusuariosamodificarinsumosproductos.indexOf(usuariologueado) == '-1'){
        $("#fechasinsumo").attr('readonly', 'readonly');
    }else{
        $("#fechasinsumo").removeAttr('readonly');
    }
    //colocar readonly si no puede modificar costos
    var arrayusuariosamodificarcostosproductos = modificarcostosdeproductos.split(",");
    if(arrayusuariosamodificarcostosproductos.indexOf(usuariologueado) == '-1'){
        $("#costo").attr('readonly', 'readonly');
        $("#costodelista").attr('readonly', 'readonly');

    }else{
        $("#costo").removeAttr('readonly');
        $("#costodelista").removeAttr('readonly');
    }
    //colocar autocomplette off  todo el formulario
    $(".form-control").attr('autocomplete','off');
    $('.dropify').dropify();
    if(data.producto.Imagen != null){
        $('#imagenactual').attr("src", urlimagenesproductos+data.producto.Imagen);
        $('#imagenactualmodal').attr("src", urlimagenesproductos+data.producto.Imagen);
    }
    //obtener tipos
    obtenertipos();
    //datos principales
    $("#codigo").val(codigoproducto);
    $("#codigo").attr('readonly', 'readonly');
    $("#claveproducto").val(data.valores_producto.ClaveProducto);
    obtenerclaveproductoporclave();
    $("#claveunidad").val(data.valores_producto.ClaveUnidad);
    obtenerclaveunidadporclave();
    $("#producto").val(data.valores_producto.Producto);
    $("#unidad").val(data.valores_producto.Unidad);
    //datos tab producto
    $("#marca").val(data.valores_producto.Marca);
    obtenermarcapornumero();
    $("#linea").val(data.valores_producto.Linea);
    obtenerlineapornumero();
    $("#impuesto").val(data.impuesto);
    $("#costo").val(data.costo);
    $("#precio").val(data.precio);
    $("#ubicacion").val(data.valores_producto.Ubicacion);
    $("#costodelista").val(data.costodelista);
    $("#moneda").val(data.valores_producto.Moneda);
    obtenermonedaporclave();
    //datos tab precios clientes
    $("#tablapreciosclientes").append(data.filaspreciosclientes);
    $("#numerofilasprecioscliente").val(data.numerofilasprecioscliente);
    //datos tab consumos
    if(data.pt == ""){
        $('input[name=consumosproductoterminado][value="N"]').attr('checked', 'checked');
    }else{
        $('input[name=consumosproductoterminado][value='+data.pt+']').attr('checked', 'checked');
    }
    $("#tablaconsumosproductoterminado").append(data.filasconsumos);
    $("#numerofilasconsumosproductoterminado").val(data.numerofilasconsumos);
    //datos tab fechas
    $("#fechasfechaultimacompra").val(data.fechaultimacompra);
    $("#fechasultimocosto").val(data.ultimocosto);
    $("#fechasfechaultimaventa").val(data.fechaultimaventa);
    $("#fechasultimaventa").val(data.ultimaventa);
    $("#fechascomision").val(data.comision);
    $("#fechasdescuento").val(data.descuento);
    $("#fechasminimos").val(data.minimos);
    $("#fechasmaximos").val(data.maximos);
    $("#fechascostomaximo").val(data.costomaximo);
    $("#fechaszonadeimpresion").val(data.valores_producto.Zona);
    $("#fechasproductopeligroso").val(data.valores_producto.ProductoPeligroso);
    $("#fechassupercedido").val(data.valores_producto.Supercedido);
    $("#fechasinsumo").val(data.valores_producto.Insumo);
    $("#fechasdescripcion").val(data.valores_producto.Descripcion);
    listarutilidades();
    listarexistenciasalmacenes();
    activarbusquedaproducto();//importante activa la busqueda de productos por su codigo
    activarbusquedacliente();//importante activa la busqueda de clientes por su numero
    //activar busqueda para clave producto
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
    //activar busqueda para clave unidad
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
    //activar busqueda para marca
    $('#marca').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenermarcapornumero();
        }
    });
    //regresar numero
    $('#marca').on('change', function(e) {
        regresarmarca();
    });
    //activar busqueda para linea
    $('#linea').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerlineapornumero();
        }
    });
    //regresar numero
    $('#linea').on('change', function(e) {
        regresarlinea();
    });
    //activar busqueda para moneda
    $('#moneda').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenermonedaporclave();
        }
    });
    //regresar clave
    $('#moneda').on('change', function(e) {
        regresarmoneda();
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
    seleccionartipo(data);
  }).fail( function() {
    msj_errorajax();
    $('.page-loader-wrapper').css('display', 'none');
  })
}
async function seleccionartipo(data){
    await retraso();
    $("#tipo").val(data.valores_producto.TipoProd).change();
    $("#tipo").select2();
    setTimeout(function(){$("#codigo").focus();},500);
    mostrarmodalformulario('MODIFICACION');
    $('.page-loader-wrapper').css('display', 'none');
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
            url:productos_guardar_modificacion,
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
        msj_verificartodoslosdatos();
    }
});
//detectar cuando en el input de buscar por codigo de producto el usuario presione la tecla enter, si es asi se realizara la busqueda con el codigo escrito
$(document).ready(function(){
    //activar busqueda de kardex por codigo
    $('#codigokardex').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerkardexporcodigo();
        }
    });
});
//obtener kardex al dar click en detalle de la fila
function obtenerkardex(codigo,almacen){
    $('.page-loader-wrapper').css('display', 'block');
    $.get(productos_obtener_kardex,{codigo:codigo,almacen:almacen},function(data){
        $("#titulomodalmovimientos").html("Kardex: " + codigo);
        $("#infomovimientos").html("<div class='col-md-4'>Existencias actuales: " + data.existencias + "</div><div class='col-md-4'>Entradas: " + data.entradas + "</div><div class='col-md-4'>Salidas:" + data.salidas + "</div>");
        $("#codigokardex").val(codigo);
        $("#almacenkardex").html(data.selectalmacenes);
        $("#filasmovimientos").html(data.filasmovimientos);
        $("#modalmovimientos").modal('show');
        $('.page-loader-wrapper').css('display', 'none');
        //obtener documento
        $('#tablakardexproducto tr').dblclick(function(){
            var documento = $(this).find("td").eq(1).html();
            var numerodocumento = $(this).find("td").eq(2).html();
            obtenerdocumento(documento,numerodocumento);
        });
    });
}
//obtener kardex al dar enter en el input del codigo
function obtenerkardexporcodigo(){
    var codigokardex = $("#codigokardex").val();
    var almacenkardex = $("#almacenkardex").val();
    obtenerkardex(codigokardex,almacenkardex);
}
//obtener kardex al cambiar el almacen
$("#almacenkardex").on('change', function (e) {
    var codigokardex = $("#codigokardex").val();
    var almacenkardex = $("#almacenkardex").val();
    obtenerkardex(codigokardex,almacenkardex);
});
function generarcodigosbarras(){
    $("#ModalCodigosBarras").modal('show');
    setTimeout(function(){$("#valorgenerarcodigobarras").focus();},500);
    $("#divcodigosbarras").html("");
    $("#valorgenerarcodigobarras").val("");
    $("#arraycodigosparacodigosdebarras").val("");
    $("#tipoprodcodigosbarras").val("TODOS").change();
    $("#statuscodigosbarras").val("TODOS").change();
    $("#codigobarrasubicacion").select2({
        dropdownParent: $('#ModalCodigosBarras'),
        tags: true,
        width: '25.00em',
        tokenSeparators: [',', ' ']
    })
    //activar busqueda para metodo pago
    $('#valorgenerarcodigobarras').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            e.preventDefault();
            var valorgenerarcodigobarras = $("#valorgenerarcodigobarras").val();
            agregarcodigoenarraycodigosbarras(valorgenerarcodigobarras);
        }
    });
}
//agregar el codigo escrito en el array de generacion de codigos de barras
function agregarcodigoenarraycodigosbarras(valorgenerarcodigobarras){
    $.get(productos_validar_si_existe_codigo,{valorgenerarcodigobarras:valorgenerarcodigobarras}, function(data){
        if(data.resultado > 0){
            $("#divcodigosbarras").append('<div class="col-md-3 text-center">'+data.producto+'</div>');
            var arraycodigosparacodigosdebarras = $("#arraycodigosparacodigosdebarras").val();
            var newarraycodigosparacodigosdebarras = arraycodigosparacodigosdebarras + "," + valorgenerarcodigobarras;
            $("#arraycodigosparacodigosdebarras").val(newarraycodigosparacodigosdebarras);
        }
        $("#valorgenerarcodigobarras").val("");
    })
}
//funcion para mandar a imprimir el codigo de barras desde la modificacion
function imprimircodigosbarras(){
    var numimpresiones = $("#numimpresiones").val();
    var codigo = $("#codigo").val();
    var producto = $("#producto").val();
    var ubicacion = $("#ubicacion").val();
    var tamanoetiquetas = $("#tamanoetiquetas").val();
    window.open(
        productos_generar_pdf_codigo_barras+'?numimpresiones='+numimpresiones+'&codigo='+codigo+'&producto='+producto+'&ubicacion='+ubicacion+'&tamanoetiquetas='+tamanoetiquetas,
        '_blank' // <- This is what makes it open in a new window.
    );
}
//obtener documento
function obtenerdocumento(documento,numerodocumento){
    //alert("numerodocumento: "+numerodocumento+ "documento:"+documento);
    switch(documento){
        case 'Compras':
            //alert("Compras");
            //obtenerdatoscompra(numerodocumento);
            break;
        case 'Ajustes':
            //alert("Ajustes");
            break;
        case 'Remisiones':
            //alert("Remisiones");
            break;
        case 'Traspasos':
            //alert("Traspasos");
            break;
        case 'NC Cliente':
            //alert("Notas Clientes");
            break;
        case 'NC Proveedor':
            //alert("Notas Proveedores");
            break;
        case 'Asignacion Herramienta':
            //alert("Asignacion Herramienta");
            break;
        case 'Produccion':
            //alert("Produccion");
            break;
        case 'Consumo Produccion':
            //alert("Consumo Produccion");
            break;
        case 'Produccion Ter':
            //alert("Produccion Terminado");
            break;
        case 'Produccion Det':
            //alert("Produccion Detalles");
            break;
        case 'Destinar Ter':
            //alert("Destinar Terminado");
            break;
        case 'Destinar Det':
            //alert("Destinar Detalles");
            break;
    }
}

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
