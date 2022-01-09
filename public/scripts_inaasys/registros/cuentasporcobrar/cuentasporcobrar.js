'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
    campos_a_filtrar_en_busquedas();
    listar();
}
function retraso(){
    return new Promise(resolve => setTimeout(resolve, 1000));
}
//obtener fecha date time
function asignarfechaactual(){
    $.get(cuentas_por_cobrar_obtener_fecha_datetime, function(fechas){
        $("#fecha").val(fechas.fecha).attr('min', fechas.fechamin).attr('max', fechas.fechamax);
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
        "lengthMenu": [ 100, 250, 500, 1000 ],
        "pageLength": 100,
        "sScrollX": "110%",
        "sScrollY": "350px",
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
      obtenerdatos(data.Pago);
    });
}
//obtener registros de proveedores
function obtenerclientes(){
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
    var tcli = $('#tbllistadocliente').DataTable({
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
    //seleccionar registro al dar doble click
    $('#tbllistadocliente tbody').on('dblclick', 'tr', function () {
        var data = tcli.row( this ).data();
        seleccionarcliente(data.Numero, data.Nombre, data.Plazo, data.Rfc, data.ClaveFormaPago, data.NombreFormaPago, data.ClaveMetodoPago, data.NombreMetodoPago, data.ClaveUsoCfdi, data.NombreUsoCfdi, "", "");
    }); 
} 
//seleccionar cliente
function seleccionarcliente(Numero, Nombre, Plazo, Rfc, claveformapago, formapago, clavemetodopago, metodopago, claveusocfdi, usocfdi, claveresidenciafiscal, residenciafiscal){
    $('.page-loader-wrapper').css('display', 'block');
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    var numerocliente = Numero;
    if(numeroclienteanterior != numerocliente){ 
        $("#numerocliente").val(Numero);
        $("#numeroclienteanterior").val(Numero);
        $("#cliente").val(Nombre);
        if(Nombre != null){
            $("#textonombrecliente").html(Nombre.substring(0, 40));
        }
        $("#rfccliente").val(Rfc);
        //datos pestaña receptor o cliente
        $("#receptorrfc").val(Rfc);
        $("#receptornombre").val(Nombre);
        $("#claveformapago").val(claveformapago);
        $("#claveformapagoanterior").val(claveformapago);
        $("#formapago").val(formapago);
        if(formapago != null){
            $("#textonombreformapago").html(formapago.substring(0, 40));            
        }
        $("#btnlistarfacturas").show();
        var tipooperacion = $("#tipooperacion").val();
        var numerocliente = Numero;
        $.get(cuentas_por_cobrar_obtener_facturas_cliente, {numerocliente:numerocliente,tipooperacion:tipooperacion}, function(data){
            $("#tabladetallesfacturas tbody").html(data.filasfacturas);
            $('.page-loader-wrapper').css('display', 'none');
        });
        mostrarformulario();
    }
}
//obtener registros de almacenes
function obtenerbancos(){
    ocultarformulario();
    var tablabancos = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Bancos</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadobanco" class="tbllistadobanco table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
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
      var tban = $('#tbllistadobanco').DataTable({
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
                  $('#tbllistadobanco').DataTable().search( this.value ).draw();
                  }
              });
          },
      }); 
      //seleccionar registro al dar doble click
      $('#tbllistadobanco tbody').on('dblclick', 'tr', function () {
        var data = tban.row( this ).data();
        seleccionarbanco(data.Numero, data.Nombre);
      });
} 
function seleccionarbanco(Numero, Nombre){
    var numerobancoanterior = $("#numerobancoanterior").val();
    var numerobanco = Numero;
    if(numerobancoanterior != numerobanco){
        $("#numerobanco").val(Numero);
        $("#numerobancoanterior").val(Numero);
        $("#banco").val(Nombre);
        if(Nombre != null){
            $("#textonombrebanco").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
    }
}
//obtener lugares expedicion
function obtenerlugaresexpedicion(){
    ocultarformulario();
    var tablacodigospostales =  '<div class="modal-header '+background_forms_and_modals+'">'+
                                  '<h4 class="modal-title">Códigos Postales</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                  '<div class="row">'+
                                      '<div class="col-md-12">'+
                                          '<div class="table-responsive">'+
                                              '<table id="tbllistadocodigopostal" class="tbllistadocodigopostal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                  '<thead class="'+background_tables+'">'+
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
    var tcodpost = $('#tbllistadocodigopostal').DataTable({
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
    //seleccionar registro al dar doble click
    $('#tbllistadocodigopostal tbody').on('dblclick', 'tr', function () {
        var data = tcodpost.row( this ).data();
        seleccionarlugarexpedicion(data.Clave);
    }); 
} 
//seleccionar lugar expedicion
function seleccionarlugarexpedicion(Clave){
    var lugarexpedicionanterior = $("#lugarexpedicionanterior").val();
    var lugarexpedicion = Clave;
    if(lugarexpedicionanterior != lugarexpedicion){
        $("#lugarexpedicion").val(Clave);
        $("#lugarexpedicionanterior").val(Clave);
        if(Clave != null){
            $("#textonombrelugarexpedicion").html(Clave.substring(0, 40));
        }
        mostrarformulario();
    }
}
//obtener regimenes fiscales
function obtenerregimenesfiscales(){
    ocultarformulario();
    var tablaregimenesfiscales ='<div class="modal-header '+background_forms_and_modals+'">'+
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
    var tregfis = $('#tbllistadoregimenfiscal').DataTable({
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
    //seleccionar registro al dar doble click
    $('#tbllistadoregimenfiscal tbody').on('dblclick', 'tr', function () {
        var data = tregfis.row( this ).data();
        seleccionarregimenfiscal(data.Clave, data.Nombre);
    }); 
} 
//seleccionar lugar expedicion
function seleccionarregimenfiscal(Clave, Nombre){
    var claveregimenfiscalanterior = $("#claveregimenfiscalanterior").val();
    var claveregimenfiscal = Clave;
    if(claveregimenfiscalanterior != claveregimenfiscal){
        $("#claveregimenfiscal").val(Clave);
        $("#claveregimenfiscalanterior").val(Clave);
        $("#regimenfiscal").val(Nombre);
        if(Nombre != null){
            $("#textonombreregimenfiscal").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
    }
}
//obtener tipos relacion
function obtenertiposrelaciones(){
    ocultarformulario();
    var tablatiposrelaciones ='<div class="modal-header '+background_forms_and_modals+'">'+
                                  '<h4 class="modal-title">Tipos Relación</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                  '<div class="row">'+
                                      '<div class="col-md-12">'+
                                          '<div class="table-responsive">'+
                                              '<table id="tbllistadotiporelacion" class="tbllistadotiporelacion table table-bordered table-striped table-hover" style="width:100% !important;">'+
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
    $("#contenidomodaltablas").html(tablatiposrelaciones);
    var ttiprel = $('#tbllistadotiporelacion').DataTable({
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
    //seleccionar registro al dar doble click
    $('#tbllistadotiporelacion tbody').on('dblclick', 'tr', function () {
        var data = ttiprel.row( this ).data();
        seleccionartiporelacion(data.Clave, data.Nombre);
    }); 
} 
//seleccionar lugar expedicion
function seleccionartiporelacion(Clave, Nombre){
    var clavetiporelacionanterior = $("#clavetiporelacionanterior").val();
    var clavetiporelacion = Clave;
    if(clavetiporelacionanterior != clavetiporelacion){
        $("#clavetiporelacion").val(Clave);
        $("#clavetiporelacionanterior").val(Clave);
        $("#tiporelacion").val(Nombre);
        if(Nombre != null){
            $("#textonombretiporelacion").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
    }
}
//obtener formas de pago
function obtenerformaspago(){
    ocultarformulario();
    var tablaformaspago ='<div class="modal-header '+background_forms_and_modals+'">'+
                                  '<h4 class="modal-title">Formas Pago</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                  '<div class="row">'+
                                      '<div class="col-md-12">'+
                                          '<div class="table-responsive">'+
                                              '<table id="tbllistadoformapago" class="tbllistadoformapago table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                  '<thead class="'+background_tables+'">'+
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
    var tforpag = $('#tbllistadoformapago').DataTable({
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
    //seleccionar registro al dar doble click
    $('#tbllistadoformapago tbody').on('dblclick', 'tr', function () {
        var data = tforpag.row( this ).data();
        seleccionarformapago(data.Clave, data.Nombre);
    }); 
} 
//seleccionar forma pago
function seleccionarformapago(Clave, Nombre){
    var claveformapagoanterior = $("#claveformapagoanterior").val();
    var claveformapago = Clave;
    if(claveformapagoanterior != claveformapago){
        $("#claveformapago").val(Clave);
        $("#claveformapagoanterior").val(Clave);
        $("#formapago").val(Nombre);
        if(Nombre != null){
            $("#textonombreformapago").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
    }
}
//listar metodos pago
function listarmetodospago(fila){
    ocultarformulario();
    var tablametodospago =  '<div class="modal-header '+background_forms_and_modals+'">'+
                                  '<h4 class="modal-title">Métodos Pago</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadometodopago" class="tbllistadometodopago table table-bordered table-striped table-hover" style="width:100% !important;">'+
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
    $("#contenidomodaltablas").html(tablametodospago);
    var tmetpag = $('#tbllistadometodopago').DataTable({
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
    //seleccionar registro al dar doble click
    $('#tbllistadometodopago tbody').on('dblclick', 'tr', function () {
        var data = tmetpag.row( this ).data();
        seleccionarmetodopago(data.Clave, data.Nombre);
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
    var tablafoliosfiscales='<div class="modal-header '+background_forms_and_modals+'">'+
                                  '<h4 class="modal-title">Folios Fiscales</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                  '<div class="row">'+
                                      '<div class="col-md-12">'+
                                          '<div class="table-responsive">'+
                                              '<table id="tbllistadofoliofiscal" class="tbllistadofoliofiscal table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                  '<thead class="'+background_tables+'">'+
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
    var tfolfis = $('#tbllistadofoliofiscal').DataTable({
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
    //seleccionar registro al dar doble click
    $('#tbllistadofoliofiscal tbody').on('dblclick', 'tr', function () {
        var data = tfolfis.row( this ).data();
        seleccionarfoliofiscal(data.Serie, data.Esquema);
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
//obtener por numero
function obtenerclientepornumero(){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    var numerocliente = $("#numerocliente").val();
    if(numeroclienteanterior != numerocliente){
        if($("#numerocliente").parsley().isValid()){
            $('.page-loader-wrapper').css('display', 'block');
            var tipooperacion = $("#tipooperacion").val();
            $.get(cuentas_por_cobrar_obtener_cliente_por_numero, {numerocliente:numerocliente,tipooperacion:tipooperacion}, function(data){
                $("#numerocliente").val(data.numero);
                $("#numeroclienteanterior").val(data.numero);
                $("#cliente").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombrecliente").html(data.nombre.substring(0, 40));
                }
                $("#rfccliente").val(data.rfc);
                //datos pestaña receptor o cliente
                $("#receptorrfc").val(data.rfc);
                $("#receptornombre").val(data.nombre);
                $("#claveformapago").val(data.claveformapago);
                $("#claveformapagoanterior").val(data.claveformapago);
                $("#formapago").val(data.formapago);
                if(data.formapago != null){
                    $("#textonombreformapago").html(data.formapago.substring(0, 40));
                }
                $("#btnlistarfacturas").show();
                $("#tabladetallesfacturas tbody").html(data.filasfacturas);
                mostrarformulario(); 
                $('.page-loader-wrapper').css('display', 'none');
            }) 
        }
    }
  }
//regresar numero
function regresarnumerocliente(){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    $("#numerocliente").val(numeroclienteanterior);
}
//obtener por numero
function obtenerbancopornumero(){
    var numerobancoanterior = $("#numerobancoanterior").val();
    var numerobanco = $("#numerobanco").val();
    if(numerobancoanterior != numerobanco){
        if($("#numerobanco").parsley().isValid()){
            $.get(cuentas_por_cobrar_obtener_banco_por_numero, {numerobanco:numerobanco}, function(data){
                $("#numerobanco").val(data.numero);
                $("#numerobancoanterior").val(data.numero);
                $("#banco").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombrebanco").html(data.nombre.substring(0, 40));
                }
                mostrarformulario();
            }) 
        }
    }
}
//regresar numero
function regresarnumerobanco(){
    var numerobancoanterior = $("#numerobancoanterior").val();
    $("#numerobanco").val(numerobancoanterior);
}
//obtener por clave
function obtenerlugarexpedicionporclave(){
    var lugarexpedicionanterior = $("#lugarexpedicionanterior").val();
    var lugarexpedicion = $("#lugarexpedicion").val();
    if(lugarexpedicionanterior != lugarexpedicion){
        if($("#lugarexpedicion").parsley().isValid()){
            $.get(cuentas_por_cobrar_obtener_lugar_expedicion_por_clave, {lugarexpedicion:lugarexpedicion}, function(data){
            $("#lugarexpedicion").val(data.clave);
            $("#lugarexpedicionanterior").val(data.clave);
            if(data.estado != null){
                $("#textonombrelugarexpedicion").html(data.estado.substring(0, 40));
            }
            mostrarformulario();
            }) 
        }
    }
}
//regresar clave
function regresarclavelugarexpedicion(){
    var lugarexpedicionanterior = $("#lugarexpedicionanterior").val();
    $("#lugarexpedicion").val(lugarexpedicionanterior);
}
//obtener por clave
function obtenerregimenfiscalporclave(){
    var claveregimenfiscalanterior = $("#claveregimenfiscalanterior").val();
    var claveregimenfiscal = $("#claveregimenfiscal").val();
    if(claveregimenfiscalanterior != claveregimenfiscal){
        if($("#claveregimenfiscal").parsley().isValid()){
            $.get(cuentas_por_cobrar_obtener_regimen_fiscal_por_clave, {claveregimenfiscal:claveregimenfiscal}, function(data){
            $("#claveregimenfiscal").val(data.clave);
            $("#claveregimenfiscalanterior").val(data.clave);
            $("#regimenfiscal").val(data.nombre);
            if(data.nombre != null){
                $("#textonombreregimenfiscal").html(data.nombre.substring(0, 40));
            }
            mostrarformulario();
            }) 
        }
    }
}
//regresar clave
function regresarclaveregimenfiscal(){
    var claveregimenfiscalanterior = $("#claveregimenfiscalanterior").val();
    $("#claveregimenfiscal").val(claveregimenfiscalanterior);
}
//obtener por clave
function obtenertiporelacionporclave(){
    var clavetiporelacionanterior = $("#clavetiporelacionanterior").val();
    var clavetiporelacion = $("#clavetiporelacion").val();
    if(clavetiporelacionanterior != clavetiporelacion){
        if($("#clavetiporelacion").parsley().isValid()){
            $.get(cuentas_por_cobrar_obtener_tipo_relacion_por_clave, {clavetiporelacion:clavetiporelacion}, function(data){
            $("#clavetiporelacion").val(data.clave);
            $("#clavetiporelacionanterior").val(data.clave);  
            $("#tiporelacion").val(data.nombre);
            if(data.nombre != null){
                $("#textonombretiporelacion").html(data.nombre.substring(0, 40));
            }
            mostrarformulario();
            }) 
        }
    }
}
//regresar clave
function regresarclavetiporelacion(){
    var clavetiporelacionanterior = $("#clavetiporelacionanterior").val();
    $("#clavetiporelacion").val(clavetiporelacionanterior);
}
//obtener por clave
function obtenerformapagoporclave(){
    var claveformapagoanterior = $("#claveformapagoanterior").val();
    var claveformapago = $("#claveformapago").val();
    if(claveformapagoanterior != claveformapago){
        if($("#claveformapago").parsley().isValid()){
            $.get(cuentas_por_cobrar_obtener_forma_pago_por_clave, {claveformapago:claveformapago}, function(data){
            $("#claveformapago").val(data.clave);
            $("#claveformapagoanterior").val(data.clave);
            $("#formapago").val(data.nombre);
            if(data.nombre != null){
                $("#textonombreformapago").html(data.nombre.substring(0, 40));
            }
            mostrarformulario();
            }) 
        }
    }
}
//regresar clave
function regresarclaveformapago(){
    var claveformapagoanterior = $("#claveformapagoanterior").val();
    $("#claveformapago").val(claveformapagoanterior);
}
//listar todas las facturas
function listarfacturas (){
    ocultarformulario();
    var tablafacturas ='<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Facturas</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadofactura" class="tbllistadofactura table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
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
      var tfact = $('#tbllistadofactura').DataTable({
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
      //seleccionar registro al dar doble click
      $('#tbllistadofactura tbody').on('dblclick', 'tr', function () {
        var data = tfact.row( this ).data();
        seleccionarfactura(data.Folio, data.Factura);
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
//saldar factura
function saldarfactura(fila){
    var abonofactura= $("#filafactura"+fila+" .abonopesosfacturapartida").val();
    var saldofactura = $("#filafactura"+fila+" .saldofacturapartida").val();
    var nuevosaldo = new Decimal(abonofactura).plus(saldofactura);
    $("#filafactura"+fila+" .abonopesosfacturapartida").val(number_format(round(nuevosaldo, numerodecimales), numerodecimales, '.', ''));
    calcularnuevosaldo(fila);
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
                                    '<input type="text" class="form-control inputnext" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                    '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                    '<input type="hidden" class="form-control" name="stringfacturasseleccionadas" id="stringfacturasseleccionadas" readonly required>'+
                                    '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                                    '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                                    '<input type="hidden" class="form-control" name="esquema" id="esquema" value="'+esquema+'" readonly data-parsley-length="[1, 10]">'+
                                '</div>'+  
                                '<div class="col-md-3">'+
                                    '<label>Cliente <span class="label label-danger" id="textonombrecliente"></span></label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" id="btnobtenerclientes" onclick="obtenerclientes()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="text" class="form-control inputnext" name="numerocliente" id="numerocliente" required data-parsley-type="integer" autocomplete="off">'+
                                                    '<input type="hidden" class="form-control" name="numeroclienteanterior" id="numeroclienteanterior" required data-parsley-type="integer">'+
                                                    '<input type="hidden" class="form-control" name="cliente" id="cliente" required readonly>'+
                                                    '<input type="hidden" class="form-control" name="rfccliente" id="rfccliente" required readonly>'+
                                                '</div>'+
                                            '</td>'+    
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+ 
                                '<div class="col-md-3">'+
                                    '<label>Banco <span class="label label-danger" id="textonombrebanco"></span></label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" onclick="obtenerbancos()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="text" class="form-control inputnext" name="numerobanco" id="numerobanco" required data-parsley-type="integer" autocomplete="off">'+
                                                    '<input type="hidden" class="form-control" name="numerobancoanterior" id="numerobancoanterior" required data-parsley-type="integer">'+
                                                    '<input type="hidden" class="form-control" name="banco" id="banco" required readonly>'+
                                                '</div>'+
                                            '</td>'+    
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+ 
                                '<div class="col-md-3">'+
                                    '<label>Fecha</label>'+
                                    '<input type="datetime-local" class="form-control" name="fecha" id="fecha" required data-parsley-excluded="true" onkeydown="return false">'+
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
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="pesosmoneda" id="pesosmoneda" value="1.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                        '</td>'+
                                    '</tr>'+
                                    '</table>'+
                                '</div>'+ 
                                '<div class="col-md-3">'+
                                    '<label>Fecha aplicación pagos</label>'+
                                    '<input type="datetime-local" class="form-control" name="fechaaplicacionpagos" id="fechaaplicacionpagos"  data-parsley-excluded="true" onkeydown="return false" required>'+
                                '</div>'+
                                '<div class="col-md-3" hidden>'+
                                        '<label>Cargar Facturas</label>'+
                                        '<div class="btn btn-block bg-blue waves-effect" id="btnlistarfacturas" onclick="listarfacturas()" style="display:none">Agregar Factura</div>'+
                                '</div>'+  
                            '</div>'+
                        '</div>'+   
                        '<div role="tabpanel" class="tab-pane fade" id="emisortab">'+
                            '<div class="row">'+
                                '<div class="col-md-4">'+
                                    '<label>R.F.C.</label>'+
                                    '<input type="text" class="form-control inputnexttabem" name="emisorrfc" id="emisorrfc" value="'+rfcempresa+'"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                '</div>'+
                                '<div class="col-md-4">'+
                                    '<label>Nombre</label>'+
                                    '<input type="text" class="form-control inputnexttabem" name="emisornombre" id="emisornombre" value="'+nombreempresa+'" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-4">'+
                                    '<label>Lugar Expedición <span class="label label-danger" id="textonombrelugarexpedicion"></span></label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" onclick="obtenerlugaresexpedicion()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="text" class="form-control inputnexttabem" name="lugarexpedicion" id="lugarexpedicion" value="'+lugarexpedicion+'" required autocomplete="off">'+
                                                    '<input type="hidden" class="form-control" name="lugarexpedicionanterior" id="lugarexpedicionanterior" value="'+lugarexpedicion+'" required readonly>'+
                                                '</div>'+
                                            '</td>'+
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-4">'+
                                    '<label>Régimen Fiscal <span class="label label-danger" id="textonombreregimenfiscal">'+regimenfiscal+'</span></label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscales()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="text" class="form-control inputnexttabem" name="claveregimenfiscal" id="claveregimenfiscal" value="'+claveregimenfiscal+'" required autocomplete="off">'+
                                                    '<input type="hidden" class="form-control" name="claveregimenfiscalanterior" id="claveregimenfiscalanterior" value="'+claveregimenfiscal+'" required>'+
                                                    '<input type="hidden" class="form-control" name="regimenfiscal" id="regimenfiscal" value="'+regimenfiscal+'" required readonly>'+
                                                '</div>'+
                                            '</td>'+
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+
                                '<div class="col-md-4">'+
                                    '<label>Tipo Relación <span class="label label-danger" id="textonombretiporelacion"></span></label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" onclick="obtenertiposrelaciones()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="text" class="form-control inputnexttabem" name="clavetiporelacion" id="clavetiporelacion" autocomplete="off" >'+
                                                    '<input type="hidden" class="form-control" name="clavetiporelacionanterior" id="clavetiporelacionanterior" >'+
                                                    '<input type="hidden" class="form-control" name="tiporelacion" id="tiporelacion" readonly>'+
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
                                    '<input type="text" class="form-control inputnexttabre" name="receptorrfc" id="receptorrfc"   required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                '</div>'+
                                '<div class="col-md-3">'+
                                    '<label>Nombre</label>'+
                                    '<input type="text" class="form-control inputnexttabre" name="receptornombre" id="receptornombre"  required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                                '<div class="col-md-3">'+
                                    '<label>Forma de Pago <span class="label label-danger" id="textonombreformapago"></span></label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" onclick="obtenerformaspago()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="text" class="form-control inputnexttabre" name="claveformapago" id="claveformapago" required autocomplete="off">'+
                                                    '<input type="hidden" class="form-control" name="claveformapagoanterior" id="claveformapagoanterior" required>'+
                                                    '<input type="hidden" class="form-control" name="formapago" id="formapago" required readonly>'+
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
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                            '<th class="'+background_tables+'">#</th>'+
                                            '<th class="'+background_tables+'">Factura</th>'+
                                            '<th class="'+background_tables+'"><div class="divorinputmodsm">Fecha</div></th>'+
                                            '<th class="'+background_tables+'">Plazo</th>'+
                                            '<th class="'+background_tables+'"><div class="divorinputmodsm">Vence</div></th>'+
                                            '<th class="'+background_tables+'">Total $</th>'+
                                            '<th class="'+background_tables+'">Abonos $</th>'+
                                            '<th class="'+background_tables+'">Notas Crédito $</th>'+
                                            '<th class="customercolortheadth">Abono $</th>'+
                                            '<th class="'+background_tables+'">Saldo $ (DOBLE CLICK)</th>'+
                                            '<th class="'+background_tables+'"><div class="divorinputmodxl">idDocumento</div></th>'+
                                            '<th class="'+background_tables+'">Serie</th>'+
                                            '<th class="'+background_tables+'">Folio</th>'+
                                            '<th class="'+background_tables+'">MonedaDR</th>'+
                                            '<th class="'+background_tables+'">TipoCambioDR</th>'+
                                            '<th class="'+background_tables+'">MetodoDePagoDR</th>'+
                                            '<th class="'+background_tables+'">NumParcialidad</th>'+
                                            '<th class="'+background_tables+'">ImpSaldoAnt</th>'+
                                            '<th class="'+background_tables+'">ImpPagado</th>'+
                                            '<th class="'+background_tables+'">ImpSaldoInsoluto</th>'+
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
                    '<textarea class="form-control inputnext" name="anotacion" id="anotacion" rows="2" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
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
    $("#numerofilas").val("0");
    //activar busqueda para clientes
    $('#numerocliente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerclientepornumero();
        }
    });
    //regresar numero cliente
    $('#numerocliente').on('change', function(e) {
        regresarnumerocliente();
    });
    //activar busqueda para bancos
    $('#numerobanco').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerbancopornumero();
        }
    });
    //regresar numero banco
    $('#numerobanco').on('change', function(e) {
        regresarnumerobanco();
    });
    //activar busqueda para lugar expedicion
    $('#lugarexpedicion').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerlugarexpedicionporclave();
        }
    });
    //regresar clave
    $('#lugarexpedicion').on('change', function(e) {
        regresarclavelugarexpedicion();
    });
    //activar busqueda para regimen fiscal
    $('#claveregimenfiscal').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerregimenfiscalporclave();
        }
    });
    //regresar clave
    $('#claveregimenfiscal').on('change', function(e) {
        regresarclaveregimenfiscal();
    });
    //activar busqueda para tipo relacion
    $('#clavetiporelacion').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenertiporelacionporclave();
        }
    });
    //regresar clave
    $('#clavetiporelacion').on('change', function(e) {
        regresarclavetiporelacion();
    });
    //activar busqueda para forma pago
    $('#claveformapago').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerformapagoporclave();
        }
    });
    //regresar clave
    $('#claveformapago').on('change', function(e) {
        regresarclaveformapago();
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnext").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnext");          
        $(".inputnext").eq(index + 1).focus().select(); 
      }
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB EMISOR
    $(".inputnexttabem").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnexttabem");          
        $(".inputnexttabem").eq(index + 1).focus().select(); 
      }
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB RECEPTOR
    $(".inputnexttabre").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnexttabre");          
        $(".inputnexttabre").eq(index + 1).focus().select(); 
      }
    });
    setTimeout(function(){$("#folio").focus();},500);
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
    e.preventDefault();
    var form = $("#formparsley");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        enviarfilasutilizadas().then(resultado=>{
            var formData = new FormData($("#formparsley")[0]);
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
        })
    }else{
        msjfaltandatosporcapturar();
    }
    //validar formulario
    form.parsley().validate();
});
//funcion asincrona para buscar existencias de la partida
function enviarfilasutilizadas(){
    return new Promise((ejecuta)=>{
        setTimeout(function(){ 
            var cuentaFilas = 0;
            $("tr.filasfacturas").each(function () {
                var abonopesosfacturapartida = $(".abonopesosfacturapartida", this).val();
                if(parseFloat(abonopesosfacturapartida) == parseFloat(0) || parseFloat(abonopesosfacturapartida) < parseFloat(0)){
                    $("#filafactura"+cuentaFilas).remove();
                }
                cuentaFilas++;
            }); 
            var resultado = true;
            return ejecuta(resultado);
        },1000);
    })
}
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
                $("#btnbaja").show();
                $("#textomodaldesactivar").html('Estas seguro de dar de baja la cuenta por cobrar? No'+ cxcdesactivar);
                $("#motivobaja").val("");
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
  $('.page-loader-wrapper').css('display', 'block');
  $.get(cuentas_por_cobrar_obtener_cuenta_por_cobrar,{cxcmodificar:cxcmodificar },function(data){
    $("#titulomodal").html('Modificación Cuenta por Cobrar --- STATUS : ' + data.cuentaxcobrar.Status);
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
                                        '<input type="text" class="form-control inputnext" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                        '<input type="hidden" class="form-control" name="stringfacturasseleccionadas" id="stringfacturasseleccionadas" readonly required>'+
                                        '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                                        '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                                        '<input type="hidden" class="form-control" name="esquema" id="esquema" value="'+esquema+'" readonly data-parsley-length="[1, 10]">'+
                                        '<input type="hidden" class="form-control" name="pago" id="pago" readonly>'+
                                    '</div>'+  
                                    '<div class="col-md-3">'+
                                        '<label>Cliente <span class="label label-danger" id="textonombrecliente"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" id="btnobtenerclientes" onclick="obtenerclientes()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnext" name="numerocliente" id="numerocliente" required autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="numeroclienteanterior" id="numeroclienteanterior" required>'+
                                                        '<input type="hidden" class="form-control" name="cliente" id="cliente" required readonly>'+
                                                        '<input type="hidden" class="form-control" name="rfccliente" id="rfccliente" required readonly>'+
                                                    '</div>'+
                                                '</td>'+    
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+ 
                                    '<div class="col-md-3">'+
                                        '<label>Banco <span class="label label-danger" id="textonombrebanco"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenerbancos()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnext" name="numerobanco" id="numerobanco" required autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="numerobancoanterior" id="numerobancoanterior" required>'+
                                                        '<input type="hidden" class="form-control" name="banco" id="banco" required readonly>'+
                                                    '</div>'+
                                                '</td>'+    
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+ 
                                    '<div class="col-md-3">'+
                                        '<label>Fecha</label>'+
                                        '<input type="datetime-local" class="form-control" name="fecha" id="fecha" required data-parsley-excluded="true" onkeydown="return false">'+
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
                                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="pesosmoneda" id="pesosmoneda" value="1.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                            '</td>'+
                                        '</tr>'+
                                        '</table>'+
                                    '</div>'+ 
                                    '<div class="col-md-3">'+
                                        '<label>Fecha aplicación pagos</label>'+
                                        '<input type="datetime-local" class="form-control" name="fechaaplicacionpagos" id="fechaaplicacionpagos" required data-parsley-excluded="true" onkeydown="return false">'+
                                    '</div>'+
                                    '<div class="col-md-3" hidden>'+
                                            '<label>Cargar Facturas</label>'+
                                            '<div class="btn btn-block bg-blue waves-effect" id="btnlistarfacturas" onclick="listarfacturas()" style="display:none">Agregar Factura</div>'+
                                    '</div>'+  
                                '</div>'+
                            '</div>'+   
                            '<div role="tabpanel" class="tab-pane fade" id="emisortab">'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<label>R.F.C.</label>'+
                                        '<input type="text" class="form-control inputnexttabem" name="emisorrfc" id="emisorrfc" value="'+rfcempresa+'"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Nombre</label>'+
                                        '<input type="text" class="form-control inputnexttabem" name="emisornombre" id="emisornombre" value="'+nombreempresa+'" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Lugar Expedición <span class="label label-danger" id="textonombrelugarexpedicion"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenerlugaresexpedicion()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnexttabem" name="lugarexpedicion" id="lugarexpedicion" value="'+lugarexpedicion+'" required autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="lugarexpedicionanterior" id="lugarexpedicionanterior" value="'+lugarexpedicion+'" required>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<label>Régimen Fiscal <span class="label label-danger" id="textonombreregimenfiscal"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscales()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnexttabem" name="claveregimenfiscal" id="claveregimenfiscal" value="'+claveregimenfiscal+'" required autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="claveregimenfiscalanterior" id="claveregimenfiscalanterior" value="'+claveregimenfiscal+'" required>'+
                                                        '<input type="hidden" class="form-control" name="regimenfiscal" id="regimenfiscal" value="'+regimenfiscal+'" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Tipo Relación <span class="label label-danger" id="textonombretiporelacion"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenertiposrelaciones()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnexttabem" name="clavetiporelacion" id="clavetiporelacion" autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="clavetiporelacionanterior" id="clavetiporelacionanterior">'+
                                                        '<input type="hidden" class="form-control" name="tiporelacion" id="tiporelacion"  readonly>'+
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
                                        '<input type="text" class="form-control inputnexttabre" name="receptorrfc" id="receptorrfc"   required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Nombre</label>'+
                                        '<input type="text" class="form-control inputnexttabre" name="receptornombre" id="receptornombre"  required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Forma de Pago <span class="label label-danger" id="textonombreformapago"></span></label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obtenerformaspago()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="text" class="form-control inputnexttabre" name="claveformapago" id="claveformapago" required autocomplete="off">'+
                                                        '<input type="hidden" class="form-control" name="claveformapagoanterior" id="claveformapagoanterior" required>'+
                                                        '<input type="hidden" class="form-control" name="formapago" id="formapago" required readonly>'+
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
                                        '<table id="tabladetallesfacturas" class="table table-bordered tabladetallesfacturas tabladetallesmodulo">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                '<th class="'+background_tables+'">#</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'facturaaplicarpartida\',\'Compra\');">Factura</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'fechafacturapartida\',\'Fecha\');"><div class="divorinputmodsm">Fecha</div></th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'plazofacturapartida\',\'Plazo\');">Plazo</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'vencefacturapartida\',\'Vence\');"><div class="divorinputmodsm">Vence</div></th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'totalpesosfacturapartida\',\'Total $\');">Total $</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'abonosfacturapartida\',\'Abonos $\');">Abonos $</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'notascreditofacturapartida\',\'Notas Crédito $\');">Notas Crédito $</th>'+
                                                '<th class="customercolortheadth"   ondblclick="construirtabladinamicaporcolumna(\'abonopesosfacturapartida\',\'Abono $\');">Abono $</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'saldofacturapartida\',\'Saldo $\');">Saldo $ (DOBLE CLICK)</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'uuidfacturapartida\',\'IdDocumento\');"><div class="divorinputmodxl">idDocumento</div></th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'seriefacturapartida\',\'Serie\');">Serie</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'foliofacturapartida\',\'Folio\');">Folio</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'monedadrfacturapartida\',\'MonedaDR\');">MonedaDR</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'tipocambiodrfacturapartida\',\'TipoCambioDR\');">TipoCambioDR</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'metodopagodrfacturapartida\',\'MetodoDePagoDR\');">MetodoDePagoDR</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'numparcialidadfacturapartida\',\'NumParcialidad\');">NumParcialidad</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'impsaldoantfacturapartida\',\'ImpSaldoAnt\');">ImpSaldoAnt</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'imppagadofacturapartida\',\'ImpPagado\');">ImpPagado</th>'+
                                                '<th class="'+background_tables+'"  ondblclick="construirtabladinamicaporcolumna(\'impsaldoinsolutofacturapartida\',\'ImpSaldoInsoluto\');">ImpSaldoInsoluto</th>'+
                                                '</tr>'+
                                            '</thead>'+
                                            '<tbody>'+           
                                            '</tbody>'+
                                        '</table>'+
                                        '<table class="table table-bordered tabladinamicaacopiar" hidden>'+
                                            '<thead class="'+background_tables+'" id="theadtabladinamicaacopiar">'+
                                            '</thead>'+
                                            '<tbody id="tbodytabladinamicaacopiar">'+           
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
                        '<textarea class="form-control inputnext" name="anotacion" id="anotacion" rows="2" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
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
    $("#fecha").val(data.fecha).attr('min', data.fechasdisponiblesenmodificacion.fechamin).attr('max', data.fechasdisponiblesenmodificacion.fechamax);
    $("#fechaaplicacionpagos").val(data.fechapago);
    $("#cliente").val(data.cliente.Nombre);
    if(data.cliente.Nombre != null){
        $("#textonombrecliente").html(data.cliente.Nombre.substring(0, 40));
    }
    $("#numerocliente").val(data.cliente.Numero);
    $("#numeroclienteanterior").val(data.cliente.Numero);
    $("#rfccliente").val(data.cliente.Rfc);
    $("#banco").val(data.banco.Nombre);
    if(data.banco.Nombre != null){
        $("#textonombrebanco").html(data.banco.Nombre.substring(0, 40));
    }
    $("#numerobanco").val(data.banco.Numero);
    $("#numerobancoanterior").val(data.banco.Numero);
    $("#moneda").val(data.cuentaxcobrar.Moneda).change();
    $("#pesosmoneda").val(data.tipocambio);
    $("#emisorrfc").val(data.cuentaxcobrar.EmisorRfc);
    $("#emisornombre").val(data.cuentaxcobrar.EmisorNombre);
    $("#lugarexpedicion").val(data.cuentaxcobrar.LugarExpedicion);
    $("#lugarexpedicionanterior").val(data.cuentaxcobrar.LugarExpedicion);
    $("#regimenfiscal").val(data.regimenfiscal.Nombre);
    if(data.regimenfiscal.Nombre != null){
        $("#textonombreregimenfiscal").html(data.regimenfiscal.Nombre.substring(0, 40));
    }
    $("#claveregimenfiscal").val(data.regimenfiscal.Clave);
    $("#claveregimenfiscalanterior").val(data.regimenfiscal.Clave);
    $("#tiporelacion").val(data.tiporelacion.Nombre);
    if(data.tiporelacion.Nombre != null){
        $("#textonombretiporelacion").html(data.tiporelacion.Nombre.substring(0, 40));
    }
    $("#clavetiporelacion").val(data.tiporelacion.Clave);
    $("#clavetiporelacionanterior").val(data.tiporelacion.Clave);
    $("#receptorrfc").val(data.cuentaxcobrar.ReceptorRfc);
    $("#receptornombre").val(data.cuentaxcobrar.ReceptorNombre);
    $("#formapago").val(data.formapago.Nombre);
    if(data.formapago.Nombre != null){
        $("#textonombreformapago").html(data.formapago.Nombre.substring(0, 40));
    }
    $("#claveformapago").val(data.formapago.Clave);
    $("#claveformapagoanterior").val(data.formapago.Clave);
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
    //regresar numero cliente
    $('#numerocliente').on('change', function(e) {
        regresarnumerocliente();
    });
    //activar busqueda para bancos
    $('#numerobanco').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerbancopornumero();
        }
    });
    //regresar numero banco
    $('#numerobanco').on('change', function(e) {
        regresarnumerobanco();
    });
    //activar busqueda para lugar expedicion
    $('#lugarexpedicion').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerlugarexpedicionporclave();
        }
    });
    //regresar clave
    $('#lugarexpedicion').on('change', function(e) {
        regresarclavelugarexpedicion();
    });
    //activar busqueda para regimen fiscal
    $('#claveregimenfiscal').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerregimenfiscalporclave();
        }
    });
    //regresar clave
    $('#claveregimenfiscal').on('change', function(e) {
        regresarclaveregimenfiscal();
    });
    //activar busqueda para tipo relacion
    $('#clavetiporelacion').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenertiporelacionporclave();
        }
    });
    //regresar clave
    $('#clavetiporelacion').on('change', function(e) {
        regresarclavetiporelacion();
    });
    //activar busqueda para forma pago
    $('#claveformapago').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerformapagoporclave();
        }
    });
    //regresar clave
    $('#claveformapago').on('change', function(e) {
        regresarclaveformapago();
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnext").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnext");          
        $(".inputnext").eq(index + 1).focus().select(); 
      }
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB EMISOR
    $(".inputnexttabem").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnexttabem");          
        $(".inputnexttabem").eq(index + 1).focus().select(); 
      }
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB RECEPTOR
    $(".inputnexttabre").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnexttabre");          
        $(".inputnexttabre").eq(index + 1).focus().select(); 
      }
    });


    //copiar detalles tabla modulo
    const btnCopyTable = document.querySelector('table.tabladinamicaacopiar');
    const elTable = document.querySelector('table.tabladinamicaacopiar');
    const copyEl = (elToBeCopied) => {
       let range, sel;
       // Ensure that range and selection are supported by the browsers
       if (document.createRange && window.getSelection) {
           console.log(elToBeCopied);
           range = document.createRange();
           sel = window.getSelection();
           // unselect any element in the page
           sel.removeAllRanges();
           try {
               range.selectNodeContents(elToBeCopied);
               sel.addRange(range);
           } catch (e) {
               range.selectNode(elToBeCopied);
               sel.addRange(range);
           }
           document.execCommand('copy');
       }
       sel.removeAllRanges();
       msj_tablacopiadacorrectamente(); 
    };
    //btnCopyText.addEventListener('click', () => copyEl(elText));
    btnCopyTable.addEventListener('dblclick', () => copyEl(elTable));
    //fin copias tabla detalles modulo


    setTimeout(function(){$("#folio").focus();},500);
    mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
    $('.page-loader-wrapper').css('display', 'none');
  }).fail( function() {
    msj_errorajax();    
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
            url:cuentas_por_cobrar_guardar_modificacion,
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
        msjfaltandatosporcapturar();
    }
    //validar formulario
    form.parsley().validate();
});
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento,tipoformato){
    $.get(cuentas_por_cobrar_obtener_datos_envio_email,{documento:documento,tipoformato:tipoformato}, function(data){
        $("#textomodalenviarpdfemail").html("Enviar email Cuenta Por Cobrar No." + documento);
        $("#emaildocumento").val(documento);
        $("#emailde").val(data.emailde);
        $("#emailpara").val(data.emailpara);
        $("#email2cc").val(data.email2cc);
        $("#email3cc").val(data.email3cc);
        $("#emailasunto").val("CUENTA POR COBRAR NO. " + documento +" DE "+ nombreempresa);
        if(data.cuentaporcobrar.UUID != ""){
            $("#incluir_xml").removeAttr('onclick');
        }else{
            $("#incluir_xml").attr('onclick','javascript: return false;');
        }
        $("#divincluirxml").show();
        $(".dropify-clear").trigger("click");
        $("#divadjuntararchivo").hide();
        $("#modalenviarpdfemail").modal('show');
        if(tipoformato == 1){
            $("#tipoformato").val(tipoformato);
        }else{
            $("#tipoformato").val("N/A");
        }
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
//timbrar pago
function timbrarpago(pago){
    $.get(cuentas_por_cobrar_verificar_si_continua_timbrado,{pago:pago}, function(data){
        if(data.Esquema == 'PAGO'){
          $("#pagotimbrado").val(0);
          $("#textomodaltimbrado").html('Aviso, los pagos con Esquema PAGO no se pueden timbrar');
          $('#modaltimbrado').modal('show');
          $("#btntimbrarpago").hide();
        }else if(data.Status == 'BAJA'){
            $("#pagotimbrado").val(0);
            $("#textomodaltimbrado").html('Aviso, este Pago se encuentra dada de baja');
            $('#modaltimbrado').modal('show');
            $("#btntimbrarpago").hide();
        }else{ 
            if(data.UUID != ""){
                $("#pagotimbrado").val(0);
                $("#textomodaltimbrado").html('Aviso, este Pago ya se timbro');
                $('#modaltimbrado').modal('show');
                $("#btntimbrarpago").hide();
            }else{
                $("#modaltimbrado").modal("show");
                $("#textomodaltimbrado").html("Esta seguro de timbrar el Pago? No"+pago);
                $("#pagotimbrado").val(pago);
                $("#btntimbrarpago").show();
            }
        }
    }) 
}
$("#btntimbrarpago").on('click', function(e){
    e.preventDefault();
    var formData = new FormData($("#formtimbrado")[0]);
    var form = $("#formtimbrado");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:cuentas_por_cobrar_timbrar_pago,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                $('#modaltimbrado').modal('hide');
                var results = JSON.parse(data);
                msj_documentotimbradocorrectamente(results.mensaje, results.tipomensaje);
                $('.page-loader-wrapper').css('display', 'none');
            },
            error:function(data){
                if(data.status == 403){
                    msj_errorenpermisos();
                }else{
                    msj_errorajax();
                }
                $('#modaltimbrado').modal('hide');
                $('.page-loader-wrapper').css('display', 'none');
            }
        })
    }else{
        form.parsley().validate();
    }
});  
//cancelar timbre
function cancelartimbre(facturabajatimbre){
    $.get(cuentas_por_cobrar_verificar_si_continua_baja_timbre,{facturabajatimbre:facturabajatimbre}, function(data){
      if(data.comprobante != ''){
        if(data.comprobante.IdFacturapi != null){
            if(data.obtener_factura.cancellation_status == "none" || data.obtener_factura.cancellation_status == "pending"){
              $("#facturabajatimbre").val(facturabajatimbre);
              $("#iddocumentofacturapi").val(data.obtener_factura.id);
              $("#textomodalbajatimbre").html('Esta seguro de dar de baja el timbre de la factura No.'+ facturabajatimbre);
              $("#btnbajatimbre").show();
              $('#modalbajatimbre').modal('show');
            }else if(data.obtener_factura.cancellation_status == "accepted"){
              $("#facturabajatimbre").val(0);
              $("#iddocumentofacturapi").val(0);
              $("#textomodalbajatimbre").html('Aviso, el timbre de la factura No.' + facturabajatimbre +' ya esta cancelado');
              $("#btnbajatimbre").hide();
              $('#modalbajatimbre').modal('show');
            }
        }else{
          $("#facturabajatimbre").val(0);
          $("#iddocumentofacturapi").val(0);
          $("#textomodalbajatimbre").html('Aviso, el pago No.'+ facturabajatimbre +' no esta timbrada en el nuevo sistema');
          $("#btnbajatimbre").hide();
          $('#modalbajatimbre').modal('show');
        }
      }else{ 
        $("#facturabajatimbre").val(0);
        $("#iddocumentofacturapi").val(0);
        $("#textomodalbajatimbre").html('Aviso, el pago No.'+ facturabajatimbre +' no esta timbrada');
        $("#btnbajatimbre").hide();
        $('#modalbajatimbre').modal('show');
      }
    })   
}
$("#btnbajatimbre").on('click', function(e){
    e.preventDefault();
    var formData = new FormData($("#formbajatimbre")[0]);
    var form = $("#formbajatimbre");
    if (form.parsley().isValid()){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:cuentas_por_cobrar_baja_timbre,
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data){
          $('#modalbajatimbre').modal('hide');
          msj_timbrecanceladocorrectamente();
          $('.page-loader-wrapper').css('display', 'none');
        },
        error:function(data){
          if(data.status == 403){
            msj_errorenpermisos();
          }else{
            msj_errorajax();
          }
          $('#modalbajatimbre').modal('hide');
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
                                                '<th>Pago</th>'+
                                                '<th>Cliente</th>'+
                                                '<th>Abono</th>'+
                                                '<th>Status</th>'+
                                            '</tr>';
    $("#columnastablafoliosencontrados").html(columnastablafoliosencontrados);
    $("#columnasfootertablafoliosencontrados").html(columnastablafoliosencontrados);
    //agregar inputs de busqueda por columna
    $('#tablafoliosencontrados tfoot th').each( function () {
      var titulocolumnatfoot = $(this).text();
      $(this).html( '<input type="text" placeholder="Buscar en columna '+titulocolumnatfoot+'" />' );
    });
    var tablafolenc=$('#tablafoliosencontrados').DataTable({
        "paging":   false,
        "sScrollX": "100%",
        "sScrollY": "250px",
        processing: true,
        serverSide: true,
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        ajax: {
            url: cuentas_por_cobrar_buscar_folio_string_like,
            data: function (d) {
                d.string = $("#buscarfolio").val();
            },
        },
        columns: [
            { data: 'Pago', name: 'Pago', orderable: false, searchable: true },
            { data: 'NombreCliente', name: 'NombreCliente', orderable: false, searchable: true },
            { data: 'Abono', name: 'Abono', orderable: false, searchable: true  },
            { data: 'Status', name: 'Status', orderable: false, searchable: true  },
        ],
        initComplete: function () {
            // Aplicar busquedas por columna
            this.api().columns().every( function () {
                var that = this;
                $('input',this.footer()).on('keyup', function(){
                if(that.search() !== this.value){
                    that.search(this.value).draw();
                }
                });
            });
            $(".dataTables_filter").css('display', 'none');
        }
    });
    //modificacion al dar doble click
    $('#tablafoliosencontrados tbody').on('dblclick', 'tr', function () {
        tablafolenc = $("#tablafoliosencontrados").DataTable();
        var data = tablafolenc.row( this ).data();
        agregararraypdf(data.Pago);
    });
  }
//configurar tabla
function configurar_tabla(){
    var checkboxscolumnas = '';
    var optionsselectbusquedas = '';
    var campos = campos_activados.split(",");
    for (var i = 0; i < campos.length; i++) {
      var returncheckboxfalse = '';
      if(campos[i] == 'Pago' || campos[i] == 'Status' || campos[i] == 'Periodo'){
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
    if(rol_usuario_logueado == 1){
      $("#divorderbystabla").show();
    }
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