'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
    campos_a_filtrar_en_busquedas();
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
    //Campos ordenados a mostras
    var campos = columnas_ordenadas.split(",");
    var campos_busqueda = campos_busquedas.split(",");
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
        ajax: productos_obtener,
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
    $('#tbllistadoclaveproducto').DataTable({
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
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoclaveproducto').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
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
    $('#tbllistadoclaveunidad').DataTable({
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
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoclaveunidad').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
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
    $('#tbllistadomarca').DataTable({
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
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadomarca').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
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
    $('#tbllistadolinea').DataTable({
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
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadolinea').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
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
        ajax: productos_obtener_monedas,
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
    //var tabla = $('.tbllistadoutilidades').DataTable();
      //          tabla.ajax.reload();
}
//listar las existencias por almacen
function listarexistenciasalmacenes(){
    var codigo = $("#codigo").val();
    $.get(productos_obtener_existencias_almacenes,{codigo:codigo },function(filasexistenciasalmacen){
        $('#tbllistadoexistenciaalmacen').append(filasexistenciasalmacen);
    });
}
//detectar cuando en el input de buscar por numero de cliente el usuario presione la tecla enter, si es asi se realizara la busqueda con el numero escrito
function activarbusquedacliente(){
    var buscarnumerocliente = $('#numeroabuscar');
    buscarnumerocliente.unbind();
    buscarnumerocliente.bind('keyup change', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            listarclientes();
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
//detectar cuando en el input de buscar por codigo de producto el usuario presione la tecla enter, si es asi se realizara la busqueda con el codigo escrito
function activarbusquedaproducto(){
    var buscarcodigoproducto = $('#codigoabuscar');
    buscarcodigoproducto.unbind();
    buscarcodigoproducto.bind('keyup change', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          listarproductos();
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
            { data: 'Ubicacion', name: 'Ubicacion', orderable: false, searchable: false },
            { data: 'Ubicacion', name: 'Ubicacion', orderable: false, searchable: false },
            { data: 'Existencias', name: 'Existencias', orderable: false, searchable: false },
            { data: 'Costo', name: 'Costo', orderable: false, searchable: false },
            { data: 'SubTotal', name: 'SubTotal', orderable: false, searchable: false } 
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
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
function seleccionarclaveproducto(Clave, Nombre){
    $("#claveproducto").val(Clave);
    $("#producto").val(Nombre);
    $("#producto").keyup();
    mostrarformulario();
}
function seleccionarclaveunidad(Clave, Nombre){
    $("#claveunidad").val(Clave);
    $("#unidad").val(Nombre);
    $("#unidad").keyup();
    mostrarformulario();
}
function seleccionarmarca(Numero, Nombre){
    $("#marca").val(Numero);
    $("#nombremarca").val(Nombre);
    mostrarformulario();
    reiniciartablautilidades();
}
function seleccionarlinea(Numero, Nombre){
    $("#linea").val(Numero);
    $("#nombrelinea").val(Nombre);
    mostrarformulario();
}
function seleccionarmoneda(Clave){
    $("#moneda").val(Clave);
    mostrarformulario();
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
function agregarfilaconsumos(Codigo, Producto){
    var fila=   '<tr class="filasconsumos" id="filaconsumo'+contadorconsumos+'">'+
                    '<td><div class="btn btn-danger btn-xs" onclick="eliminarfilaconsumos('+contadorconsumos+')">X</div></td>'+
                    '<td><input type="hidden" name="codigoproductoconsumos[]" id="codigoproductoconsumos[]" value="'+Codigo+'" readonly>'+Codigo+'</td>'+
                    '<td><input type="hidden" name="productoconsumos[]" id="productoconsumos[]" value="'+Producto+'" readonly>'+Producto+'</td>'+
                    '<td><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" name="cantidadproductoconsumos[]" id="cantidadproductoconsumos[]" required value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);"></td>'+
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
                                '<label>Marca<b style="color:#F44336 !important;">*</b></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarmarcas" class="btn bg-blue waves-effect" onclick="listarmarcas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-8">'+  
                                        '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="marca" id="marca" required readonly>'+
                                            '<input type="text" class="form-control" name="nombremarca" id="nombremarca" required readonly>'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Linea<b style="color:#F44336 !important;">*</b></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarlineas" class="btn bg-blue waves-effect" onclick="listarlineas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-8">'+  
                                        '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="linea" id="linea" required readonly>'+
                                            '<input type="text" class="form-control" name="nombrelinea" id="nombrelinea" required readonly>'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Impuesto % <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control " name="impuesto" id="impuesto" required value="16.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                        '</div>'+   
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Costo (De última compra sin impuesto)</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control " name="costo" id="costo" value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Precio (Precio neto)</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control " name="precio" id="precio" value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Ubicación</label>'+
                                '<input type="text" class="form-control " name="ubicacion" id="ubicacion" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this)">'+
                            '</div>'+
                        '</div>'+ 
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="codigosalternostab">'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Código 1</label>'+
                                '<input type="text" class="form-control" name="codigo1" id="codigo1" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Código 2</label>'+
                                '<input type="text" class="form-control" name="codigo2" id="codigo2" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Código 3</label>'+
                                '<input type="text" class="form-control" name="codigo3" id="codigo3" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Código 4</label>'+
                                '<input type="text" class="form-control" name="codigo4" id="codigo4" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Código 5</label>'+
                                '<input type="text" class="form-control" name="codigo5" id="codigo5" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>';
  $("#tabsform").html(tabs);
  $("#codigo").removeAttr('readonly');
  seleccionarclaveunidad('H87','Pieza');
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
                    '<li role="presentation" style="display:none;">'+
                        '<a href="#codigosalternostab" data-toggle="tab">Código Alternos</a>'+
                    '</li>'+
                    '<li role="presentation" >'+
                        '<a href="#fechastab" data-toggle="tab">Fechas</a>'+
                    '</li>'+
                    '<li role="presentation" >'+
                        '<a href="#lpatab" data-toggle="tab">Lpa</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="productotab">'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Marca<b style="color:#F44336 !important;">*</b></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarmarcas" class="btn bg-blue waves-effect" onclick="listarmarcas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-8">'+  
                                        '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="marca" id="marca" required readonly>'+
                                            '<input type="text" class="form-control" name="nombremarca" id="nombremarca" required readonly>'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Linea<b style="color:#F44336 !important;">*</b></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarlineas" class="btn bg-blue waves-effect" onclick="listarlineas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-8">'+  
                                        '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="linea" id="linea" required readonly>'+
                                            '<input type="text" class="form-control" name="nombrelinea" id="nombrelinea" required readonly>'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Impuesto % <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control " name="impuesto" id="impuesto" required value="16.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onkeyup="tipoLetra(this);reiniciartablautilidades();" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                        '</div>'+   
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Costo (De última compra sin impuesto)</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control " name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onkeyup="tipoLetra(this);reiniciartablautilidades();" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Ubicación</label>'+
                                '<input type="text" class="form-control " name="ubicacion" id="ubicacion" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this)">'+
                            '</div>'+
                        '</div>'+ 
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Costo de Lista</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control " name="costodelista" id="costodelista" value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Moneda</label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarmonedas" class="btn bg-blue waves-effect" onclick="listarmonedas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-8">'+  
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control" name="moneda" id="moneda" readonly data-parsley-length="[1, 5]">'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-7">'+
                                '<label>UTILIDADES</label>'+   
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
                                '<div class="col-md-6">'+
                                    '<h5>PRECIOS A CLIENTES PARA ESTE PRODUCTO&nbsp;&nbsp;&nbsp;</h5>'+
                                '</div>'+    
                                '<div class="col-md-6">'+
                                    '<input type="text" class="form-control" name="numeroabuscar" id="numeroabuscar" placeholder="Escribe el número de cliente">'+
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
                                '<div class="col-md-6">'+
                                    '<h5>CONSUMOS DE PRODUCTO TERMINADO&nbsp;&nbsp;&nbsp;</h5>'+
                                '</div>'+
                                '<div class="col-md-6">'+    
                                    '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto">'+
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
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+           
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="codigosalternostab" style="display:none;">'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Código 1</label>'+
                                '<input type="text" class="form-control" name="codigo1" id="codigo1" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Código 2</label>'+
                                '<input type="text" class="form-control" name="codigo2" id="codigo2" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Código 3</label>'+
                                '<input type="text" class="form-control" name="codigo3" id="codigo3" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Código 4</label>'+
                                '<input type="text" class="form-control" name="codigo4" id="codigo4" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Código 5</label>'+
                                '<input type="text" class="form-control" name="codigo5" id="codigo5" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]">'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="fechastab">'+
                        '<div class="row">'+
                            '<div class="col-md-6">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Comisión %</label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="fechascomision" id="fechascomision" value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Descuento %</label>'+
                                        '<input type="text" class="form-control" name="fechasdescuento" id="fechasdescuento" value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Mínimos </label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="fechasminimos" id="fechasminimos" value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Máximos </label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="fechasmaximos" id="fechasmaximos" value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Costo Máximo </label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="fechascostomaximo" id="fechascostomaximo" value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Fecha Ultima Compra</label>'+
                                        '<input type="date" class="form-control" name="fechasfechaultimacompra" id="fechasfechaultimacompra" readonly>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Ultimo Costo $</label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="fechasultimocosto" id="fechasultimocosto" readonly value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Fecha Ultima Venta</label>'+
                                        '<input type="date" class="form-control" name="fechasfechaultimaventa" id="fechasfechaultimaventa" readonly>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Ultima Venta $</label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="fechasultimaventa" id="fechasultimaventa" readonly value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Zona de impresión </label>'+
                                        '<input type="text" class="form-control" name="fechaszonadeimpresion" id="fechaszonadeimpresion" value="ZONA00" data-parsley-length="[1, 8]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Producto PELIGROSO </label>'+
                                        '<input type="text" class="form-control" name="fechasproductopeligroso" id="fechasproductopeligroso" data-parsley-length="[1, 1]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Supercedido </label>'+
                                        '<input type="text" class="form-control" name="fechassupercedido" id="fechassupercedido" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" >'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Insumo </label>'+
                                        '<input type="text" class="form-control" name="fechasinsumo" id="fechasinsumo" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<label>Descripción </label>'+
                                        '<textarea class="form-control" name="fechasdescripcion" id="fechasdescripcion" rows="4" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="lpatab">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<small>Almacén REFACCIONES (UTPCAMIONES)</small>'+
                                '<div class="row">'+
                                    '<div class="col-md-4 form-check">'+
                                        '<label>Subir Código al LPA</label>'+
                                        '<input type="radio" name="lpasubircodigo" id="lpasubircodigo" value="S">'+
                                        '<label for="lpasubircodigo">SI</label>'+
                                        '<input type="radio" name="lpasubircodigo" id="lpasubircodigo1" value="N" checked>'+
                                        '<label for="lpasubircodigo1">NO</label>'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Precio de Compra  = Costo</label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="lpapreciocompra" id="lpapreciocompra" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Fecha Creación</label>'+
                                        '<input type="date" class="form-control" name="lpafechacreacion" id="lpafechacreacion" >'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+    
                                    '<div class="col-md-4">'+
                                        '<label>Fecha Ultima Venta</label>'+
                                        '<input type="date" class="form-control" name="lpafechaultimaventa" id="lpafechaultimaventa" >'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Fecha Ultima Compra</label>'+
                                        '<input type="date" class="form-control" name="lpafechaultimacompra" id="lpafechaultimacompra" >'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Identificación</label>'+
                                        '<input type="text" class="form-control" name="lpaidentificacion" id="lpaidentificacion" data-parsley-length="[1, 5]">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+    
                                    '<div class="col-md-4">'+
                                        '<label>Ubicación</label>'+
                                        '<input type="text" class="form-control" name="lpaubicacion" id="lpaubicacion" data-parsley-length="[1, 60]">'+
                                    '</div>'+
                                    '<div class="col-md-8">'+
                                        '<label>Código de Compra (0=surtir en automatico 6=bloqueado)</label>'+
                                        '<input type="text" class="form-control" name="lpacodigocompra" id="lpacodigocompra" >'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //datos principales
    $("#codigo").val(codigoproducto);
    $("#codigo").attr('readonly', 'readonly');
    $("#claveproducto").val(data.producto.ClaveProducto);
    $("#claveunidad").val(data.producto.ClaveUnidad);
    $("#producto").val(data.producto.Producto);
    $("#unidad").val(data.producto.Unidad);
    //datos tab producto
    $("#marca").val(data.producto.Marca);
    $("#nombremarca").val(data.marca.Nombre);
    $("#nombremarca").keyup();
    $("#linea").val(data.producto.Linea);
    $("#nombrelinea").val(data.linea.Nombre);
    $("#nombrelinea").keyup();
    $("#impuesto").val(data.impuesto);
    $("#costo").val(data.costo);
    $("#precio").val(data.precio);
    $("#ubicacion").val(data.producto.Ubicacion);
    $("#costodelista").val(data.costodelista);
    $("#moneda").val(data.producto.Moneda);
    //datos tab codigos alternos
    $("#codigo1").val(data.producto.Codigo1);
    $("#codigo2").val(data.producto.Codigo2);
    $("#codigo3").val(data.producto.Codigo3);
    $("#codigo4").val(data.producto.Codigo4);
    $("#codigo5").val(data.producto.Codigo5);
    //datos tab precios clientes
    $("#tablapreciosclientes").append(data.filaspreciosclientes);
    $("#numerofilasprecioscliente").val(data.numerofilasprecioscliente);
    //datos tab consumos
    if(data.producto.Pt == ""){
        $('input[name=consumosproductoterminado][value="N"]').attr('checked', 'checked');  
    }else{
        $('input[name=consumosproductoterminado][value='+data.producto.Pt+']').attr('checked', 'checked');
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
    $("#fechaszonadeimpresion").val(data.producto.Zona);
    $("#fechasproductopeligroso").val(data.producto.ProductoPeligroso);
    $("#fechassupercedido").val(data.producto.Supercedido);
    $("#fechasinsumo").val(data.producto.Insumo);
    $("#fechasdescripcion").val(data.producto.Descripcion);
    //tab lpa
    $("#lpapreciocompra").val(data.costo);
    if(data.producto.Lpa1Subir == ""){
        $('input[name=lpasubircodigo1][value="N"]').attr('checked', 'checked');  
    }else{
        $('input[name=lpasubircodigo1][value='+data.producto.Lpa1Subir+']').attr('checked', 'checked');
    }
    $("#lpafechacreacion").val(data.lpafechacreacion);
    $("#lpafechaultimaventa").val(data.lpafechaultimaventa);
    $("#lpafechaultimacompra").val(data.lpafechaultimacompra);
    $("#lpaidentificacion").val(data.producto.Lpa1Identificacion);
    $("#lpaubicacion").val(data.producto.Lpa1Ubicacion);
    $("#lpacodigocompra").val(data.producto.Lpa1CodigoCompra);
    listarutilidades();
    listarexistenciasalmacenes();
    mostrarmodalformulario('MODIFICACION');
    $('.page-loader-wrapper').css('display', 'none');
    activarbusquedaproducto();//importante activa la busqueda de productos por su codigo
    activarbusquedacliente();//importante activa la busqueda de clientes por su numero
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