'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
    listar();
}
function retraso(){
    return new Promise(resolve => setTimeout(resolve, 1000));
}
//obtener fecha date time
function asignarfechaactual(){
    $.get(cuentas_por_cobrar_obtener_fecha_datetime, function(fecha){
        //$('input[type=datetime-local]').val(fecha);
        $("#fecha").val(fecha);
    }) 
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
    var serie = $("#serie").val();
    $.get(cuentas_por_cobrar_obtener_ultimo_folio, {serie:serie}, function(folio){
        $("#folio").val(folio);
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
function mostrarmodalformulario(tipo, modificacionpermitida){
    $("#ModalFormulario").modal('show');
    if(tipo == 'ALTA'){
        $("#btnGuardar").show();
        $("#btnGuardarModificacion").hide();
    }else if(tipo == 'MODIFICACION'){
        if(modificacionpermitida == 0){
            $("#btnGuardar").hide();
            $("#btnGuardarModificacion").hide();
        }else{
            $("#btnGuardar").hide();
            $("#btnGuardarModificacion").show();
        }
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
//cambiar url para exportar excel
function cambiarurlexportarexcel(){
    //colocar el periodo seleccionado como parametro para exportar a excel
    var periodo = $("#periodo").val();
    $("#btnGenerarFormatoExcel").attr("href", urlgenerarformatoexcel+'?periodo='+periodo);
}
function relistar(){
    cambiarurlexportarexcel();
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
}
//listar todos los registros de la tabla
function listar(){
    cambiarurlexportarexcel();
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
        "bScrollCollapse": true,
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
            url: cuentas_por_cobrar_obtener,
            data: function (d) {
                d.periodo = $("#periodo").val();
            },
        },
        "createdRow": function( row, data, dataIndex){
            if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
            else{ $(row).addClass(''); }
        },
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
//obtener registros de proveedores
function obtenerclientes(){
    ocultarformulario();
    var tablaclientes = '<div class="modal-header bg-red">'+
                        '<h4 class="modal-title">Clientes</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<div class="table-responsive">'+
                                    '<table id="tbllistadocliente" class="tbllistadocliente table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                        '<thead class="customercolor">'+
                                            '<tr>'+
                                                '<th>Operaciones</th>'+
                                                '<th>Numero</th>'+
                                                '<th>Nombre</th>'+
                                                '<th>R.F.C.</th>'+
                                                '<th>Municipio</th>'+
                                                '<th>Agente</th>'+
                                                '<th>Tipo</th>'+
                                                '<th>Saldo $</th>'+
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
                url: cuentas_por_cobrar_obtener_clientes,
            },
            columns: [
                { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
                { data: 'Numero', name: 'Numero' },
                { data: 'Nombre', name: 'Nombre' },
                { data: 'Rfc', name: 'Rfc', orderable: false, searchable: false },
                { data: 'Municipio', name: 'Municipio', orderable: false, searchable: false },
                { data: 'Agente', name: 'Agente', orderable: false, searchable: false },
                { data: 'Tipo', name: 'Tipo', orderable: false, searchable: false },
                { data: 'Saldo', name: 'Saldo', orderable: false, searchable: false }
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
            
    }); 
} 
//seleccionar cliente
function seleccionarcliente(Numero, Nombre, Plazo, Rfc, claveformapago, formapago, clavemetodopago, metodopago, claveusocfdi, usocfdi, claveresidenciafiscal, residenciafiscal){
    $("#numerocliente").val(Numero);
    $("#cliente").val(Nombre);
    $("#rfccliente").val(Rfc);
    //datos pestaña receptor o cliente
    $("#receptorrfc").val(Rfc);
    $("#receptornombre").val(Nombre);
    $("#claveformapago").val(claveformapago);
    $("#formapago").val(formapago);
    $("#btnlistarfacturas").show();
    mostrarformulario();
}
//obtener registros de almacenes
function obtenerbancos(){
    ocultarformulario();
    var tablabancos = '<div class="modal-header bg-red">'+
                            '<h4 class="modal-title">Bancos</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadobanco" class="tbllistadobanco table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="customercolor">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Banco</th>'+
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
      $("#contenidomodaltablas").html(tablabancos);
      $('#tbllistadobanco').DataTable({
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
              url: cuentas_por_cobrar_obtener_bancos,
          },
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
          
      }); 
} 
function seleccionarbanco(Numero, Nombre){
    $("#numerobanco").val(Numero);
    $("#banco").val(Nombre);
    mostrarformulario();
}
//obtener lugares expedicion
function obtenerlugaresexpedicion(){
    ocultarformulario();
    var tablacodigospostales =  '<div class="modal-header bg-red">'+
                                  '<h4 class="modal-title">Códigos Postales</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                  '<div class="row">'+
                                      '<div class="col-md-12">'+
                                          '<div class="table-responsive">'+
                                              '<table id="tbllistadocodigopostal" class="tbllistadocodigopostal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                  '<thead class="customercolor">'+
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
                                  '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                                '</div>';  
    $("#contenidomodaltablas").html(tablacodigospostales);
    $('#tbllistadocodigopostal').DataTable({
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
          url: cuentas_por_cobrar_obtener_codigos_postales,
          data: function (d) {
              //d.numeroestado = $("#estado").val();
          }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Clave', name: 'Clave' },
            { data: 'Estado', name: 'Estado', orderable: false, searchable: false},
            { data: 'Municipio', name: 'Municipio', orderable: false, searchable: false}
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
        
    });
  } 
  //seleccionar lugar expedicion
  function seleccionarlugarexpedicion(Clave){
  $("#lugarexpedicion").val(Clave);
  mostrarformulario();
  }
  //obtener regimenes fiscales
  function obtenerregimenesfiscales(){
    ocultarformulario();
    var tablaregimenesfiscales ='<div class="modal-header bg-red">'+
                                  '<h4 class="modal-title">Regimenes Fiscales</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                  '<div class="row">'+
                                      '<div class="col-md-12">'+
                                          '<div class="table-responsive">'+
                                              '<table id="tbllistadoregimenfiscal" class="tbllistadoregimenfiscal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                  '<thead class="customercolor">'+
                                                      '<tr>'+
                                                          '<th>Operaciones</th>'+
                                                          '<th>Clave</th>'+
                                                          '<th>Nombre</th>'+
                                                          '<th>Física</th>'+
                                                          '<th>Moral</th>'+
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
    $("#contenidomodaltablas").html(tablaregimenesfiscales);
    $('#tbllistadoregimenfiscal').DataTable({
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
          url: cuentas_por_cobrar_obtener_regimenes_fiscales
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Clave', name: 'Clave' },
            { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
            { data: 'Fisica', name: 'Fisica', orderable: false, searchable: false},
            { data: 'Moral', name: 'Moral', orderable: false, searchable: false}
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
        
    });
  } 
  //seleccionar lugar expedicion
  function seleccionarregimenfiscal(Clave, Nombre){
    $("#claveregimenfiscal").val(Clave);
    $("#regimenfiscal").val(Nombre);
    mostrarformulario();
  }
  //obtener tipos relacion
  function obtenertiposrelaciones(){
    ocultarformulario();
    var tablatiposrelaciones ='<div class="modal-header bg-red">'+
                                  '<h4 class="modal-title">Tipos Relación</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                  '<div class="row">'+
                                      '<div class="col-md-12">'+
                                          '<div class="table-responsive">'+
                                              '<table id="tbllistadotiporelacion" class="tbllistadotiporelacion table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                  '<thead class="customercolor">'+
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
    $("#contenidomodaltablas").html(tablatiposrelaciones);
    $('#tbllistadotiporelacion').DataTable({
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
          url: cuentas_por_cobrar_obtener_tipos_relacion
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Clave', name: 'Clave' },
            { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false}
        ],
        "initComplete": function() {
          var $buscar = $('div.dataTables_filter input');
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadotiporelacion').DataTable().search( this.value ).draw();
              }
          });
        },
        
    });
  } 
  //seleccionar lugar expedicion
  function seleccionartiporelacion(Clave, Nombre){
    $("#clavetiporelacion").val(Clave);
    $("#tiporelacion").val(Nombre);
    mostrarformulario();
  }
  //obtener formas de pago
  function obtenerformaspago(){
    ocultarformulario();
    var tablaformaspago ='<div class="modal-header bg-red">'+
                                  '<h4 class="modal-title">Formas Pago</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                  '<div class="row">'+
                                      '<div class="col-md-12">'+
                                          '<div class="table-responsive">'+
                                              '<table id="tbllistadoformapago" class="tbllistadoformapago table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                  '<thead class="customercolor">'+
                                                      '<tr>'+
                                                          '<th>Operaciones</th>'+
                                                          '<th>Clave</th>'+
                                                          '<th>Nombre</th>'+
                                                          '<th>Descripción</th>'+
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
    $("#contenidomodaltablas").html(tablaformaspago);
    $('#tbllistadoformapago').DataTable({
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
          url: cuentas_por_cobrar_obtener_formas_pago
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Clave', name: 'Clave' },
            { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
            { data: 'Descripcion', name: 'Descripcion', orderable: false, searchable: false}
        ],
        "initComplete": function() {
          var $buscar = $('div.dataTables_filter input');
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoformapago').DataTable().search( this.value ).draw();
              }
          });
        },
        
    });
  } 
  //seleccionar forma pago
  function seleccionarformapago(Clave, Nombre){
    $("#claveformapago").val(Clave);
    $("#formapago").val(Nombre);
    mostrarformulario();
  }
//listar metodos pago
function listarmetodospago(fila){
    ocultarformulario();
    var tablametodospago =  '<div class="modal-header bg-red">'+
                                  '<h4 class="modal-title">Métodos Pago</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadometodopago" class="tbllistadometodopago table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="customercolor">'+
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
    $("#contenidomodaltablas").html(tablametodospago);
    $('#tbllistadometodopago').DataTable({
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
            url: cuentas_por_cobrar_obtener_metodos_pago,
            data: function (d) {
              d.fila = fila;
            }
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
                  $('#tbllistadometodopago').DataTable().search( this.value ).draw();
                }
            });
        },
        
    });
}
//seleccion de metodo de pago
function seleccionarmetodopago(Clave, Nombre, fila){
    $("#filafactura"+fila+" .metodopagodrfacturapartida").val(Clave);
    mostrarformulario();
}
//obtener folio serie nota
function obtenerfoliosnotas(){
    ocultarformulario();
    var tablafoliosfiscales='<div class="modal-header bg-red">'+
                                  '<h4 class="modal-title">Folios Fiscales</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                  '<div class="row">'+
                                      '<div class="col-md-12">'+
                                          '<div class="table-responsive">'+
                                              '<table id="tbllistadofoliofiscal" class="tbllistadofoliofiscal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                  '<thead class="customercolor">'+
                                                      '<tr>'+
                                                          '<th>Operaciones</th>'+
                                                          '<th>Serie</th>'+
                                                          '<th>Esquema</th>'+
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
    $("#contenidomodaltablas").html(tablafoliosfiscales);
    $('#tbllistadofoliofiscal').DataTable({
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
          url: cuentas_por_cobrar_obtener_folios_fiscales
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Serie', name: 'Serie' },
            { data: 'Esquema', name: 'Esquema', orderable: false, searchable: false},
        ],
        "initComplete": function() {
          var $buscar = $('div.dataTables_filter input');
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadofoliofiscal').DataTable().search( this.value ).draw();
              }
          });
        },
        
    });  
}
function seleccionarfoliofiscal(Serie, Esquema){
    var numerofilas = $("#numerofilas").val()
    if(parseInt(numerofilas) > 0){
        var confirmacion = confirm("Esta seguro de cambiar el folio fiscal?"); 
    }else{
        var confirmacion = true;
    }
    if (confirmacion == true) { 
      $.get(cuentas_por_cobrar_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie,Esquema:Esquema}, function(folio){
        $("#folio").val(folio);
        $("#serie").val(Serie);
        $("#esquema").val(Esquema);
        $("#serietexto").html("Serie: "+Serie);
        $("#esquematexto").html("Esquema: "+Esquema);
        mostrarformulario();
      }) 
    }
}
//listar todas las facturas
function listarfacturas (){
    ocultarformulario();
    var tablafacturas ='<div class="modal-header bg-red">'+
                            '<h4 class="modal-title">Facturas</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadofactura" class="tbllistadofactura table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="customercolor">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Factura</th>'+
                                                    '<th>Depto</th>'+
                                                    '<th>Fecha</th>'+
                                                    '<th>Plazo</th>'+
                                                    '<th>Items</th>'+
                                                    '<th>Total $</th>'+
                                                    '<th>Abonos $</th>'+
                                                    '<th>Descuentos $</th>'+
                                                    '<th>Saldo $</th>'+
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
      $("#contenidomodaltablas").html(tablafacturas);
      $('#tbllistadofactura').DataTable({
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
                url: cuentas_por_cobrar_obtener_facturas,
                data: function (d) {
                    d.numerocliente = $("#numerocliente").val();
                    d.stringfacturasseleccionadas = $("#stringfacturasseleccionadas").val();
                }
            },
            columns: [
                { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
                { data: 'Factura', name: 'Factura' },
                { data: 'Depto', name: 'Depto' },
                { data: 'Fecha', name: 'Fecha' },
                { data: 'Plazo', name: 'Plazo', orderable: false, searchable: false },
                { data: 'Items', name: 'Items', orderable: false, searchable: false },
                { data: 'Total', name: 'Total', orderable: false, searchable: false },
                { data: 'Abonos', name: 'Abonos', orderable: false, searchable: false },
                { data: 'Descuentos', name: 'Descuentos', orderable: false, searchable: false },
                { data: 'Saldo', name: 'Saldo', orderable: false, searchable: false }
            ],
            "initComplete": function() {
                var $buscar = $('div.dataTables_filter input');
                $buscar.unbind();
                $buscar.bind('keyup change', function(e) {
                    if(e.keyCode == 13 || this.value == "") {
                    $('#tbllistadofactura').DataTable().search( this.value ).draw();
                    }
                });
            },
          
      });  
  } 
  //obtener todos los datos de la orden de compra seleccionada
var contadorproductos=0;
var contadorfilas = 0;
function seleccionarfactura(Folio, Factura){
    $('.page-loader-wrapper').css('display', 'block');
    var tipooperacion = $("#tipooperacion").val();
    $.get(cuentas_por_cobrar_obtener_factura, {Folio:Folio, Factura:Factura, contadorfilas:contadorfilas, tipooperacion:tipooperacion}, function(data){
        $("#tabladetallesfacturas tbody").append(data.filafactura);
        //array de compras seleccionar
        construirarrayfacturasseleccionadas();
        //comprobar numero de filas en la tabla
        comprobarfilasfactura();
        //calcular totales compras nota proveedor
        calculartotal();
        $('.page-loader-wrapper').css('display', 'none');
        contadorfilas++;
        contadorproductos++;
        mostrarformulario();

    })
}
//crear array de compras seleccionadas
function construirarrayfacturasseleccionadas(){
    var arrayfacturasseleccionadas = [];
    $("tr.filasfacturas").each(function () { 
        // obtener los datos de la fila
        var facturaaplicarpartida = $(".facturaaplicarpartida", this).val();
        arrayfacturasseleccionadas.push(facturaaplicarpartida);
    });
    $("#stringfacturasseleccionadas").val(arrayfacturasseleccionadas);
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilasfactura(){
    var numerofilas = $("#tabladetallesfacturas tbody tr").length;
    $("#numerofilas").val(numerofilas);
}
//calcular totales de la compra de la nota de proveedor
function calculartotal(){
    var total = 0;
    $("tr.filasfacturas").each(function(){
        total = new Decimal(total).plus($(".abonopesosfacturapartida", this).val());
    }); 
    $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
}
//eliminar una fila en la tabla
function eliminarfilafactura(fila){
    var confirmacion = confirm("Esta seguro de eliminar la fila?"); 
    if (confirmacion == true) { 
      $("#filafactura"+fila).remove();
      contadorfilas--; //importante para todos los calculos se debe restar al contador
      contadorproductos--;
      renumerarfilasfactura();//importante para todos los calculo en el modulo de orden de compra 
      comprobarfilasfactura();
      calculartotal();
      construirarrayfacturasseleccionadas();
    }
}
//renumerar las filas de la tabla
function renumerarfilasfactura(){
    var lista;
    //renumerar filas tr
    lista = document.getElementsByClassName("filasfacturas");
    for (var i = 0; i < lista.length; i++) {
      lista[i].setAttribute("id", "filafactura"+i);
    }
    //renumerar btneliminarfilacompra
    lista = document.getElementsByClassName("btneliminarfilafactura");
    for (var i = 0; i < lista.length; i++) {
      lista[i].setAttribute("onclick", "eliminarfilafactura("+i+')');
    }
    //renumerar descuentopesoscomprapartida
    lista = document.getElementsByClassName("abonopesosfacturapartida");
    for (var i = 0; i < lista.length; i++) {
      lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calcularnuevosaldo("+i+");calculartotalesfilastablafacturas("+i+')');
    }
}  
function calcularnuevosaldo(fila){
    var cuentaFilas = 0;
    $("tr.filasfacturas").each(function () {
        if(fila === cuentaFilas){   
            //abono en pesos de la partida
            var saldofacturapartidadb = $('.saldofacturapartidadb', this).val();
            var abonopesosfacturapartida = $('.abonopesosfacturapartida', this).val();
            if(parseFloat(abonopesosfacturapartida) > parseFloat(saldofacturapartidadb)){
                $('.abonopesosfacturapartida', this).val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
                calcularnuevosaldo(fila);
            }else{
                var nuevosaldo  =  new Decimal(saldofacturapartidadb).minus(abonopesosfacturapartida);
                $('.saldofacturapartida', this).val(number_format(round(nuevosaldo, numerodecimales), numerodecimales, '.', ''));
                $('.imppagadofacturapartida', this).val(number_format(round(abonopesosfacturapartida, numerodecimales), numerodecimales, '.', ''));
                $('.impsaldoinsolutofacturapartida', this).val(number_format(round(nuevosaldo, numerodecimales), numerodecimales, '.', ''));
                calculartotal();
            }
        }  
        cuentaFilas++;
    });
}
//alta
function alta(){
  $("#titulomodal").html('Alta Cuentas por Cobrar');
  mostrarmodalformulario('ALTA');
  mostrarformulario();
  //formulario alta
  var tabs ='<div class="row">'+
                '<div class="col-md-12">'+
                    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                        '<li role="presentation" class="active">'+
                            '<a href="#pagotab" data-toggle="tab">Pago</a>'+
                        '</li>'+
                        '<li role="presentation">'+
                            '<a href="#emisortab" data-toggle="tab">Emisor</a>'+
                        '</li>'+
                        '<li role="presentation">'+
                            '<a href="#receptortab" data-toggle="tab">Receptor ó Cliente</a>'+
                        '</li>'+
                    '</ul>'+
                    '<div class="tab-content">'+
                        '<div role="tabpanel" class="tab-pane fade in active" id="pagotab">'+
                            '<div class="row">'+
                                '<div class="col-md-3">'+
                                    '<label>Pago <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp;<b style="color:#F44336 !important;" id="esquematexto"> Esquema: '+esquema+'</b>  <div class="btn btn-xs bg-red waves-effect" id="btnobtenerfoliosnotas" onclick="obtenerfoliosnotas()">Cambiar</div></label>'+
                                    '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                    '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                    '<input type="hidden" class="form-control" name="stringfacturasseleccionadas" id="stringfacturasseleccionadas" readonly required>'+
                                    '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                                    '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                                    '<input type="hidden" class="form-control" name="esquema" id="esquema" value="'+esquema+'" readonly data-parsley-length="[1, 10]">'+
                                '</div>'+  
                                '<div class="col-md-3">'+
                                    '<label>Cliente</label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" id="btnobtenerclientes" onclick="obtenerclientes()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="hidden" class="form-control" name="numerocliente" id="numerocliente" required readonly onkeyup="tipoLetra(this)">'+
                                                    '<input type="text" class="form-control" name="cliente" id="cliente" required readonly>'+
                                                    '<input type="hidden" class="form-control" name="rfccliente" id="rfccliente" required readonly>'+
                                                '</div>'+
                                            '</td>'+    
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+ 
                                '<div class="col-md-3">'+
                                    '<label>Banco</label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" onclick="obtenerbancos()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="hidden" class="form-control" name="numerobanco" id="numerobanco" required readonly onkeyup="tipoLetra(this)">'+
                                                    '<input type="text" class="form-control" name="banco" id="banco" required readonly>'+
                                                '</div>'+
                                            '</td>'+    
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+ 
                                '<div class="col-md-3">'+
                                    '<label>Fecha</label>'+
                                    '<input type="datetime-local" class="form-control" name="fecha" id="fecha" required onchange="validasolomesactual();">'+
                                    '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                                    '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                                '</div>'+   
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-3">'+
                                    '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                        '<label>Moneda</label>'+
                                        '<select name="moneda" id="moneda" class="form-control select2" style="width:100% !important;" required>'+
                                            '<option value="MXN">MXN</option>'+
                                            '<option value="USD">USD</option>'+
                                            '<option value="EUR">EUR</option>'+
                                        '</select>'+
                                        '</td>'+
                                        '<td>'+
                                        '<label>Pesos</label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="pesosmoneda" id="pesosmoneda" value="1.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                        '</td>'+
                                    '</tr>'+
                                    '</table>'+
                                '</div>'+ 
                                '<div class="col-md-3">'+
                                    '<label>Fecha aplicación pagos</label>'+
                                    '<input type="datetime-local" class="form-control" name="fechaaplicacionpagos" id="fechaaplicacionpagos" required>'+
                                '</div>'+
                                '<div class="col-md-3">'+
                                        '<label>Cargar Facturas</label>'+
                                        '<div class="btn btn-block bg-blue waves-effect" id="btnlistarfacturas" onclick="listarfacturas()" style="display:none">Agregar Factura</div>'+
                                '</div>'+  
                            '</div>'+
                        '</div>'+   
                        '<div role="tabpanel" class="tab-pane fade" id="emisortab">'+
                            '<div class="row">'+
                                '<div class="col-md-4">'+
                                    '<label>R.F.C.</label>'+
                                    '<input type="text" class="form-control" name="emisorrfc" id="emisorrfc" value="'+rfcempresa+'"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                '</div>'+
                                '<div class="col-md-4">'+
                                    '<label>Nombre</label>'+
                                    '<input type="text" class="form-control" name="emisornombre" id="emisornombre" value="'+nombreempresa+'" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-4">'+
                                    '<label>Lugar Expedición</label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" onclick="obtenerlugaresexpedicion()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="text" class="form-control" name="lugarexpedicion" id="lugarexpedicion" value="'+lugarexpedicion+'" required readonly>'+
                                                '</div>'+
                                            '</td>'+
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-4">'+
                                    '<label>Régimen Fiscal</label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscales()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="hidden" class="form-control" name="claveregimenfiscal" id="claveregimenfiscal" value="'+claveregimenfiscal+'" required readonly onkeyup="tipoLetra(this)">'+
                                                    '<input type="text" class="form-control" name="regimenfiscal" id="regimenfiscal" value="'+regimenfiscal+'" required readonly>'+
                                                '</div>'+
                                            '</td>'+
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+
                                '<div class="col-md-4">'+
                                    '<label>Tipo Relación</label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" onclick="obtenertiposrelaciones()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="hidden" class="form-control" name="clavetiporelacion" id="clavetiporelacion" readonly onkeyup="tipoLetra(this)">'+
                                                    '<input type="text" class="form-control" name="tiporelacion" id="tiporelacion" readonly>'+
                                                '</div>'+
                                            '</td>'+
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                        '<div role="tabpanel" class="tab-pane fade" id="receptortab">'+
                            '<div class="row">'+
                                '<div class="col-md-3">'+
                                    '<label>R.F.C.</label>'+
                                    '<input type="text" class="form-control" name="receptorrfc" id="receptorrfc"   required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                '</div>'+
                                '<div class="col-md-3">'+
                                    '<label>Nombre</label>'+
                                    '<input type="text" class="form-control" name="receptornombre" id="receptornombre"  required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-3">'+
                                    '<label>Forma de Pago</label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" onclick="obtenerformaspago()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="hidden" class="form-control" name="claveformapago" id="claveformapago" required readonly onkeyup="tipoLetra(this)">'+
                                                    '<input type="text" class="form-control" name="formapago" id="formapago" required readonly>'+
                                                '</div>'+
                                            '</td>'+
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="row">'+
                '<div class="col-md-12">'+
                    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                        '<li role="presentation" class="active">'+
                            '<a href="#facturastab" data-toggle="tab">Facturas</a>'+
                        '</li>'+
                    '</ul>'+
                    '<div class="tab-content">'+
                        '<div role="tabpanel" class="tab-pane fade in active" id="facturastab">'+
                            '<div class="row">'+
                                '<div class="col-md-12 table-responsive cabecerafija" style="height: 250px;overflow-y: scroll;padding: 0px 0px;">'+
                                    '<table id="tabladetallesfacturas" class="table table-bordered tabladetallesfacturas">'+
                                        '<thead class="customercolor">'+
                                            '<tr>'+
                                            '<th class="customercolor">#</th>'+
                                            '<th class="customercolor">Factura</th>'+
                                            '<th class="customercolor"><div class="divorinputmodsm">Fecha</div></th>'+
                                            '<th class="customercolor">Plazo</th>'+
                                            '<th class="customercolor"><div class="divorinputmodsm">Vence</div></th>'+
                                            '<th class="customercolor">Total $</th>'+
                                            '<th class="customercolor">Abonos $</th>'+
                                            '<th class="customercolor">Notas Crédito $</th>'+
                                            '<th class="customercolortheadth">Abono $</th>'+
                                            '<th class="customercolor">Saldo $</th>'+
                                            '<th class="customercolor"><div class="divorinputmodxl">idDocumento</div></th>'+
                                            '<th class="customercolor">Serie</th>'+
                                            '<th class="customercolor">Folio</th>'+
                                            '<th class="customercolor">MonedaDR</th>'+
                                            '<th class="customercolor">TipoCambioDR</th>'+
                                            '<th class="customercolor">MetodoDePagoDR</th>'+
                                            '<th class="customercolor">NumParcialidad</th>'+
                                            '<th class="customercolor">ImpSaldoAnt</th>'+
                                            '<th class="customercolor">ImpPagado</th>'+
                                            '<th class="customercolor">ImpSaldoInsoluto</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+           
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="row">'+
                '<div class="col-md-6">'+   
                    '<label>Anotación</label>'+
                    '<textarea class="form-control" name="anotacion" id="anotacion" rows="2" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
                '</div>'+ 
                '<div class="col-md-3">'+
                '</div>'+
                '<div class="col-md-3">'+
                    '<table class="table table-striped table-hover">'+
                        '<tr>'+
                            '<td class="tdmod">Total</td>'+
                            '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '</tr>'+
                    '</table>'+
                '</div>'+
            '</div>';
    $("#tabsform").html(tabs);
    $("#btnobtenerclientes").show();
    obtenultimonumero();
    asignarfechaactual();
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("alta");
    //activar los input select
    $("#moneda").select2();
    //reiniciar contadores
    contadorproductos=0;
    contadorfilas = 0;
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
            url:cuentas_por_cobrar_guardar,
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
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(cxcdesactivar){
    $.get(cuentas_por_cobrar_comprobar_baja,{cxcdesactivar:cxcdesactivar}, function(data){
        if(data.Status == 'BAJA'){
            $("#cxcdesactivar").val(0);
            $("#textomodaldesactivar").html('Error, esta cuenta por cobrar ya fue dado de baja');
            $("#divmotivobaja").hide();
            $("#btnbaja").hide();
            $('#estatusregistro').modal('show');
        }else{ 
            if(data.resultadofechas != ''){
            $("#cxcdesactivar").val(0);
            $("#textomodaldesactivar").html('Error solo se pueden dar de baja las cuentas por cobrar del mes actual, fecha de la cuenta por cobrar: ' + data.resultadofechas);
            $("#divmotivobaja").hide();
            $("#btnbaja").hide();
            $('#estatusregistro').modal('show');
            }else{
                $("#cxcdesactivar").val(cxcdesactivar);
                $("#divmotivobaja").show();
                $("#textomodaldesactivar").html('Estas seguro de dar de baja el registro?');
                $('#estatusregistro').modal('show');
            }
        }
    })      
}
$("#btnbaja").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formdesactivar")[0]);
  var form = $("#formdesactivar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:cuentas_por_cobrar_baja,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#estatusregistro').modal('hide');
        msj_datosguardadoscorrectamente();
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
function obtenerdatos(cxcmodificar){
  $("#titulomodal").html('Modificación Cuenta por Cobrar');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(cuentas_por_cobrar_obtener_cuenta_por_cobrar,{cxcmodificar:cxcmodificar },function(data){
    //formulario modificacion
    var tabs =  '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#pagotab" data-toggle="tab">Pago</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#emisortab" data-toggle="tab">Emisor</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#receptortab" data-toggle="tab">Receptor ó Cliente</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="pagotab">'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Pago <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp;<b style="color:#F44336 !important;" id="esquematexto"> Esquema: '+esquema+'</b>  <div class="btn btn-xs bg-red waves-effect" id="btnobtenerfoliosnotas" onclick="obtenerfoliosnotas()">Cambiar</div></label>'+
                                        '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                        '<input type="hidden" class="form-control" name="stringfacturasseleccionadas" id="stringfacturasseleccionadas" readonly required>'+
                                        '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                                        '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                                        '<input type="hidden" class="form-control" name="esquema" id="esquema" value="'+esquema+'" readonly data-parsley-length="[1, 10]">'+
                                        '<input type="hidden" class="form-control" name="pago" id="pago" readonly>'+
                                    '</div>'+  
                                    '<div class="col-md-3">'+
                                        '<label>Cliente</label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" id="btnobtenerclientes" onclick="obtenerclientes()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="hidden" class="form-control" name="numerocliente" id="numerocliente" required readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="text" class="form-control" name="cliente" id="cliente" required readonly>'+
                                                        '<input type="hidden" class="form-control" name="rfccliente" id="rfccliente" required readonly>'+
                                                    '</div>'+
                                                '</td>'+    
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+ 
                                    '<div class="col-md-3">'+
                                        '<label>Banco</label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenerbancos()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="hidden" class="form-control" name="numerobanco" id="numerobanco" required readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="text" class="form-control" name="banco" id="banco" required readonly>'+
                                                    '</div>'+
                                                '</td>'+    
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+ 
                                    '<div class="col-md-3">'+
                                        '<label>Fecha</label>'+
                                        '<input type="datetime-local" class="form-control" name="fecha" id="fecha" required onchange="validasolomesactual();">'+
                                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                                        '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                                    '</div>'+   
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                            '<label>Moneda</label>'+
                                            '<select name="moneda" id="moneda" class="form-control select2" style="width:100% !important;" required>'+
                                                '<option value="MXN">MXN</option>'+
                                                '<option value="USD">USD</option>'+
                                                '<option value="EUR">EUR</option>'+
                                            '</select>'+
                                            '</td>'+
                                            '<td>'+
                                            '<label>Pesos</label>'+
                                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="pesosmoneda" id="pesosmoneda" value="1.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                            '</td>'+
                                        '</tr>'+
                                        '</table>'+
                                    '</div>'+ 
                                    '<div class="col-md-3">'+
                                        '<label>Fecha aplicación pagos</label>'+
                                        '<input type="datetime-local" class="form-control" name="fechaaplicacionpagos" id="fechaaplicacionpagos" required>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                            '<label>Cargar Facturas</label>'+
                                            '<div class="btn btn-block bg-blue waves-effect" id="btnlistarfacturas" onclick="listarfacturas()" style="display:none">Agregar Factura</div>'+
                                    '</div>'+  
                                '</div>'+
                            '</div>'+   
                            '<div role="tabpanel" class="tab-pane fade" id="emisortab">'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<label>R.F.C.</label>'+
                                        '<input type="text" class="form-control" name="emisorrfc" id="emisorrfc" value="'+rfcempresa+'"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Nombre</label>'+
                                        '<input type="text" class="form-control" name="emisornombre" id="emisornombre" value="'+nombreempresa+'" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Lugar Expedición</label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenerlugaresexpedicion()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control" name="lugarexpedicion" id="lugarexpedicion" value="'+lugarexpedicion+'" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<label>Régimen Fiscal</label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscales()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="hidden" class="form-control" name="claveregimenfiscal" id="claveregimenfiscal" value="'+claveregimenfiscal+'" required readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="text" class="form-control" name="regimenfiscal" id="regimenfiscal" value="'+regimenfiscal+'" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Tipo Relación</label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenertiposrelaciones()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="hidden" class="form-control" name="clavetiporelacion" id="clavetiporelacion" readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="text" class="form-control" name="tiporelacion" id="tiporelacion"  readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+ 
                            '<div role="tabpanel" class="tab-pane fade" id="receptortab">'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>R.F.C.</label>'+
                                        '<input type="text" class="form-control" name="receptorrfc" id="receptorrfc"   required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Nombre</label>'+
                                        '<input type="text" class="form-control" name="receptornombre" id="receptornombre"  required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Forma de Pago</label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenerformaspago()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="hidden" class="form-control" name="claveformapago" id="claveformapago" required readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="text" class="form-control" name="formapago" id="formapago" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#facturastab" data-toggle="tab">Facturas</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="facturastab">'+
                                '<div class="row">'+
                                    '<div class="col-md-12 table-responsive cabecerafija" style="height: 250px;overflow-y: scroll;padding: 0px 0px;">'+
                                        '<table id="tabladetallesfacturas" class="table table-bordered tabladetallesfacturas">'+
                                            '<thead class="customercolor">'+
                                                '<tr>'+
                                                '<th class="customercolor">#</th>'+
                                                '<th class="customercolor">Factura</th>'+
                                                '<th class="customercolor"><div class="divorinputmodsm">Fecha</div></th>'+
                                                '<th class="customercolor">Plazo</th>'+
                                                '<th class="customercolor"><div class="divorinputmodsm">Vence</div></th>'+
                                                '<th class="customercolor">Total $</th>'+
                                                '<th class="customercolor">Abonos $</th>'+
                                                '<th class="customercolor">Notas Crédito $</th>'+
                                                '<th class="customercolortheadth">Abono $</th>'+
                                                '<th class="customercolor">Saldo $</th>'+
                                                '<th class="customercolor"><div class="divorinputmodxl">idDocumento</div></th>'+
                                                '<th class="customercolor">Serie</th>'+
                                                '<th class="customercolor">Folio</th>'+
                                                '<th class="customercolor">MonedaDR</th>'+
                                                '<th class="customercolor">TipoCambioDR</th>'+
                                                '<th class="customercolor">MetodoDePagoDR</th>'+
                                                '<th class="customercolor">NumParcialidad</th>'+
                                                '<th class="customercolor">ImpSaldoAnt</th>'+
                                                '<th class="customercolor">ImpPagado</th>'+
                                                '<th class="customercolor">ImpSaldoInsoluto</th>'+
                                                '</tr>'+
                                            '</thead>'+
                                            '<tbody>'+           
                                            '</tbody>'+
                                        '</table>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+ 
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="row">'+
                    '<div class="col-md-6">'+   
                        '<label>Anotación</label>'+
                        '<textarea class="form-control" name="anotacion" id="anotacion" rows="2" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
                    '</div>'+ 
                    '<div class="col-md-3">'+
                    '</div>'+
                    '<div class="col-md-3">'+
                        '<table class="table table-striped table-hover">'+
                            '<tr>'+
                                '<td class="tdmod">Total</td>'+
                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                            '</tr>'+
                        '</table>'+
                    '</div>'+
                '</div>';
                $("#tabsform").html(tabs); 
    //esconder el div del boton listar ordenes
    $("#btnobtenerclientes").hide();
    $("#btnlistarfacturas").hide();
    $("#btnobtenerfoliosnotas").hide();
    $("#folio").val(data.cuentaxcobrar.Folio);
    $("#serie").val(data.cuentaxcobrar.Serie);
    $("#serietexto").html("Serie: "+data.cuentaxcobrar.Serie);
    $("#esquema").val(data.cuentaxcobrar.Esquema);
    $("#esquematexto").html("Esquema: "+data.cuentaxcobrar.Esquema);
    $("#pago").val(data.cuentaxcobrar.Pago);
    $("#stringfacturasseleccionadas").val(data.arrayfacturas);
    $("#numerofilas").val(data.numerocuentaxcobrardetalle);
    $("#fecha").val(data.fecha);
    $("#fechaaplicacionpagos").val(data.fechapago);
    $("#cliente").val(data.cliente.Nombre)
    $("#numerocliente").val(data.cliente.Numero);
    $("#rfccliente").val(data.cliente.Rfc);
    $("#banco").val(data.banco.Nombre);
    $("#numerobanco").val(data.banco.Numero);
    $("#moneda").val(data.cuentaxcobrar.Moneda).change();
    $("#pesosmoneda").val(data.tipocambio);
    $("#emisorrfc").val(data.cuentaxcobrar.EmisorRfc);
    $("#emisornombre").val(data.cuentaxcobrar.EmisorNombre);
    $("#lugarexpedicion").val(data.cuentaxcobrar.LugarExpedicion);
    $("#regimenfiscal").val(data.regimenfiscal.Nombre);
    $("#claveregimenfiscal").val(data.regimenfiscal.Clave);
    $("#tiporelacion").val(data.tiporelacion.Nombre);
    $("#clavetiporelacion").val(data.tiporelacion.Clave);
    $("#receptorrfc").val(data.cuentaxcobrar.ReceptorRfc);
    $("#receptornombre").val(data.cuentaxcobrar.ReceptorNombre);
    $("#formapago").val(data.formapago.Nombre);
    $("#claveformapago").val(data.formapago.Clave);
    $("#anotacion").val(data.cuentaxcobrar.Anotacion);
    //cargar todos los detalles
    $("#tabladetallesfacturas tbody").html(data.filasdetallecuentasporcobrar);
    //totales
    $("#total").val(data.abonototal);  
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //activar los input select
    $("#moneda").select2();
    //reiniciar contadores
    contadorproductos=data.contadorproductos;
    contadorfilas = data.contadorfilas;
    mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
    $('.page-loader-wrapper').css('display', 'none');
  }).fail( function() {
    msj_mantenimientoajax();    
    $('.page-loader-wrapper').css('display', 'none');
  })
}
//guardar modificación
$("#btnGuardarModificacion").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:ordenes_compra_guardar_modificacion,
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
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento){
    $.get(cuentas_por_cobrar_obtener_datos_envio_email,{documento:documento}, function(data){
      $("#textomodalenviarpdfemail").html("Enviar email Cuenta Por Cobrar No." + documento);
      $("#emaildocumento").val(documento);
      $("#emailde").val(data.emailde);
      $("#emailpara").val(data.emailpara);
      $("#emailasunto").val("CUENTA POR COBRAR NO. " + documento +" DE USADOS TRACTOCAMIONES Y PARTES REFACCIONARIAS SA DE CV");
      $("#modalenviarpdfemail").modal('show');
    })   
  }
  //enviar documento pdf por email
  $("#btnenviarpdfemail").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formenviarpdfemail")[0]);
    var form = $("#formenviarpdfemail");
    if (form.parsley().isValid()){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:cuentas_por_cobrar_enviar_pdfs_email,
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data){
          msj_documentoenviadoporemailcorrectamente();
          $("#modalenviarpdfemail").modal('hide');
          $('.page-loader-wrapper').css('display', 'none');
        },
        error:function(data){
          if(data.status == 403){
            msj_errorenpermisos();
          }else{
            msj_errorajax();
          }
          $("#modalenviarpdfemail").modal('hide');
          $('.page-loader-wrapper').css('display', 'none');
        }
      })
    }else{
      form.parsley().validate();
    }
  });
//hacer busqueda de folio para exportacion en pdf
function relistarbuscarstringlike(){
    var tabla = $('#tablafoliosencontrados').DataTable();
    tabla.ajax.reload();
}
function buscarstringlike(){
    var columnastablafoliosencontrados =    '<tr>'+
                                                '<th><div style="width:80px !important;">Generar Documento en PDF</div></th>'+
                                                '<th>Pago</th>'+
                                                '<th>Cliente</th>'+
                                                '<th>Abono</th>'+
                                                '<th>Status</th>'+
                                            '</tr>';
    $("#columnastablafoliosencontrados").html(columnastablafoliosencontrados);
    tabla=$('#tablafoliosencontrados').DataTable({
        "paging":   false,
        "ordering": false,
        "info":     false,
        "searching": false,
        processing: true,
        serverSide: true,
        ajax: {
            url: cuentas_por_cobrar_buscar_folio_string_like,
            data: function (d) {
                d.string = $("#buscarfolio").val();
            },
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Pago', name: 'Pago' },
            { data: 'Cliente', name: 'Cliente', orderable: false, searchable: false },
            { data: 'Abono', name: 'Abono', orderable: false, searchable: false  },
            { data: 'Status', name: 'Status', orderable: false, searchable: false  },
        ],
    });
  }
//configurar tabla
function configurar_tabla(){
    //formulario configuracion tabla
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
                            '<div class="col-md-6">'+
                                '<div class="col-md-12 form-check">'+
                                    '<label>DATOS CUENTA POR COBRAR</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Pago" id="idPago" class="filled-in datotabla" value="Pago" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                    '<label for="idPago">Pago</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+    
                                    '<input type="checkbox" name="Serie" id="idSerie" class="filled-in datotabla" value="Serie" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idSerie">Serie</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Folio" id="idFolio" class="filled-in datotabla" value="Folio" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFolio">Folio</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Corte" id="idCorte" class="filled-in datotabla" value="Corte" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idCorte">Corte</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Fecha" id="idFecha" class="filled-in datotabla" value="Fecha" readonly onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFecha">Fecha</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="FechaPago" id="idFechaPago" class="filled-in datotabla" value="FechaPago" readonly onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFechaPago">FechaPago</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Cliente" id="idCliente" class="filled-in datotabla" value="Cliente" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idCliente">Cliente</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Banco" id="idBanco" class="filled-in datotabla" value="Banco" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idBanco">Banco</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Esquema" id="idEsquema" class="filled-in datotabla" value="Esquema" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idEsquema">Esquema</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Abono" id="idAbono" class="filled-in datotabla" value="Abono" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idAbono">Abono</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Anotacion" id="idAnotacion" class="filled-in datotabla" value="Anotacion" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idAnotacion">Anotacion</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="UUID" id="idUUID" class="filled-in datotabla" value="UUID" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idUUID">UUID</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Moneda" id="idMoneda" class="filled-in datotabla" value="Moneda" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idMoneda">Moneda</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="TipoCambio" id="idTipoCambio" class="filled-in datotabla" value="TipoCambio" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idTipoCambio">TipoCambio</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="EmisorRfc" id="idEmisorRfc" class="filled-in datotabla" value="EmisorRfc" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idEmisorRfc">EmisorRfc</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="EmisorNombre" id="idEmisorNombre" class="filled-in datotabla" value="EmisorNombre" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idEmisorNombre">EmisorNombre</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="LugarExpedicion" id="idLugarExpedicion" class="filled-in datotabla" value="LugarExpedicion" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idLugarExpedicion">LugarExpedicion</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="RegimenFiscal" id="idRegimenFiscal" class="filled-in datotabla" value="RegimenFiscal" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idRegimenFiscal">RegimenFiscal</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="ReceptorRfc" id="idReceptorRfc" class="filled-in datotabla" value="ReceptorRfc" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idReceptorRfc">ReceptorRfc</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="ReceptorNombre" id="idReceptorNombre" class="filled-in datotabla" value="ReceptorNombre" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idReceptorNombre">ReceptorNombre</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="FormaPago" id="idFormaPago" class="filled-in datotabla" value="FormaPago" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFormaPago">FormaPago</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="NumOperacion" id="idNumOperacion" class="filled-in datotabla" value="NumOperacion" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idNumOperacion">NumOperacion</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="RfcEmisorCtaOrd" id="idRfcEmisorCtaOrd" class="filled-in datotabla" value="RfcEmisorCtaOrd" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idRfcEmisorCtaOrd">RfcEmisorCtaOrd</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="NomBancoOrdExt" id="idNomBancoOrdExt" class="filled-in datotabla" value="NomBancoOrdExt" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idNomBancoOrdExt">NomBancoOrdExt</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="CtaOrdenante" id="idCtaOrdenante" class="filled-in datotabla" value="CtaOrdenante" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idCtaOrdenante">CtaOrdenante</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="RfcEmisorCtaBen" id="idRfcEmisorCtaBen" class="filled-in datotabla" value="RfcEmisorCtaBen" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idRfcEmisorCtaBen">RfcEmisorCtaBen</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="CtaBeneficiario" id="idCtaBeneficiario" class="filled-in datotabla" value="CtaBeneficiario" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idCtaBeneficiario">CtaBeneficiario</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="TipoCadPago" id="idTipoCadPago" class="filled-in datotabla" value="TipoCadPago" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idTipoCadPago">TipoCadPago</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="CertPago" id="idCertPago" class="filled-in datotabla" value="CertPago" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idCertPago">CertPago</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="CadPago" id="idCadPago" class="filled-in datotabla" value="CadPago" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idCadPago">CadPago</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="SelloPago" id="idSelloPago" class="filled-in datotabla" value="SelloPago" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idSelloPago">SelloPago</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Hora" id="idHora" class="filled-in datotabla" value="Hora" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idHora">Hora</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="TipoRelacion" id="idTipoRelacion" class="filled-in datotabla" value="TipoRelacion" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idTipoRelacion">TipoRelacion</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Status" id="idStatus" class="filled-in datotabla" value="Status" onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                    '<label for="idStatus">Status</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="MotivoBaja" id="idMotivoBaja" class="filled-in datotabla" value="MotivoBaja" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idMotivoBaja">MotivoBaja</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Equipo" id="idEquipo" class="filled-in datotabla" value="Equipo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idEquipo">Equipo</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Usuario" id="idUsuario" class="filled-in datotabla" value="Usuario" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idUsuario">Usuario</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Periodo" id="idPeriodo" class="filled-in datotabla" value="Periodo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idPeriodo">Periodo</label>'+
                                '</div>'+
                                '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                                '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<div class="col-md-12 form-check">'+
                                    '<label>DATOS CLIENTE</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="NumeroCliente" id="idNumeroCliente" class="filled-in datotabla" value="NumeroCliente"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idNumeroCliente">NumeroCliente</label>'+  
                                '</div>'+
                                '<div class="col-md-4 form-check">'+  
                                    '<input type="checkbox" name="NombreCliente" id="idNombreCliente" class="filled-in datotabla" value="NombreCliente"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idNombreCliente">NombreCliente</label>'+ 
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="RfcCliente" id="idRfcCliente" class="filled-in datotabla" value="RfcCliente"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idRfcCliente">RfcCliente</label>'+ 
                                '</div>'+
                                '<div class="col-md-12 form-check">'+
                                    '<label>DATOS FORMA PAGO</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="NumeroFormaPago" id="idNumeroFormaPago" class="filled-in datotabla" value="NumeroFormaPago"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idNumeroFormaPago">NumeroFormaPago</label>'+  
                                '</div>'+
                                '<div class="col-md-4 form-check">'+  
                                    '<input type="checkbox" name="ClaveFormaPago" id="idClaveFormaPago" class="filled-in datotabla" value="ClaveFormaPago"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idClaveFormaPago">ClaveFormaPago</label>'+ 
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="NombreFormaPago" id="idNombreFormaPago" class="filled-in datotabla" value="NombreFormaPago"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idNombreFormaPago">NombreFormaPago</label>'+ 
                                '</div>'+                             
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