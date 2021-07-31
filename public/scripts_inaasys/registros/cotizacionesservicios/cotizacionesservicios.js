'use strict'
var tabla;
var form;
var tipoformatopdf;
//funcion que se ejecuta al inicio
function init(){
  campos_a_filtrar_en_busquedas();
  listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 1000));
}
function asignarfechaactual(){
    /*
    var fechahoy = new Date();
    var dia = ("0" + fechahoy.getDate()).slice(-2);
    var mes = ("0" + (fechahoy.getMonth() + 1)).slice(-2);
    var hoy = fechahoy.getFullYear()+"-"+(mes)+"-"+(dia) ;
    $('#fecha').val(hoy);
    $('input[type=datetime-local]').val(new Date().toJSON().slice(0,19));
    */
  $.get(cotizaciones_servicios_obtener_fecha_actual_datetimelocal, function(fechadatetimelocal){
    $("#fecha").val(fechadatetimelocal);
  }) 
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  var serie = $("#serie").val();
  $.get(cotizaciones_servicios_obtener_ultimo_folio,{serie:serie}, function(folio){
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
        url: cotizaciones_servicios_obtener,
        data: function (d) {
            d.periodo = $("#periodo").val();
        }
    },
    "createdRow": function( row, data, dataIndex){
        if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
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
//obtener tipos unidades
function obtenertiposunidades(){
  $.get(cotizaciones_servicios_obtener_tipos_unidades, function(select_tipos_unidades){
    $("#unidad").html(select_tipos_unidades);
  })  
}
//obtener series documento
function obtenerseriesdocumento(){
  ocultarformulario();
  var seriedefault = 'A';
  var tablaseriesdocumento= '<div class="modal-header '+background_forms_and_modals+'">'+
                              '<h4 class="modal-title">Series Documento &nbsp;&nbsp; <div class="btn bg-green btn-xs waves-effect" onclick="seleccionarseriedocumento(\''+seriedefault+'\')">Asignar Serie Default (A)</div></h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                              '<div class="row">'+
                                '<div class="col-md-12">'+
                                  '<div class="table-responsive">'+
                                    '<table id="tbllistadoseriedocumento" class="tbllistadoseriedocumento table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                      '<thead class="'+background_tables+'">'+
                                        '<tr>'+
                                          '<th>Operaciones</th>'+
                                          '<th>Serie</th>'+
                                          '<th>Documento</th>'+
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
  $("#contenidomodaltablas").html(tablaseriesdocumento);
  var t = $('#tbllistadoseriedocumento').DataTable({
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
        url: cotizaciones_servicios_obtener_series_documento
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Serie', name: 'Serie' },
          { data: 'Documento', name: 'Documento' },
          { data: 'Nombre', name: 'Nombre' }
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
function seleccionarseriedocumento(Serie){
    $.get(cotizaciones_servicios_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie}, function(folio){
      $("#folio").val(folio);
      $("#serie").val(Serie);
      $("#serietexto").html("Serie: "+Serie);
      mostrarformulario();
    }) 
}
//obtener registros de clientes
function obtenerclientes(){
    ocultarformulario();
    var tablaclientes = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Clientes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadocliente" class="tbllistadocliente table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
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
            url: cotizaciones_servicios_obtener_clientes,
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
                $('#tbllistadocliente').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
} 
//obtener registros de agentes
function obteneragentes(){
    ocultarformulario();
    var tablaagentes = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Agentes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive ">'+
                                        '<table id="tbllistadoagente" class="tbllistadoagente table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
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
    $("#contenidomodaltablas").html(tablaagentes);
    $('#tbllistadoagente').DataTable({
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
            url: cotizaciones_servicios_obtener_agentes,
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
                    $('#tbllistadoagente').DataTable().search( this.value ).draw();
                }
            });
        },  
    }); 
} 
function seleccionarcliente(Numero, Nombre, Credito, Saldo, NumeroAgente, Agente, Plazo){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    var numerocliente = Numero;
    if(numeroclienteanterior != numerocliente){
        $("#numerocliente").val(Numero);
        $("#numeroclienteanterior").val(Numero);
        $("#cliente").val(Nombre);
        if(Nombre != null){
            $("#textonombrecliente").html(Nombre.substring(0, 40));
        }
        $("#plazo").val(Plazo);
        //datos agente
        $("#numeroagente").val(NumeroAgente);
        $("#numeroagenteanterior").val(NumeroAgente);
        $("#agente").val(Agente);
        if(Agente != null){
            $("#textonombreagente").html(Agente.substring(0, 40));
        }
        mostrarformulario();
    }
}
function seleccionaragente(Numero, Nombre){
    var numeroagenteanterior = $("#numeroagenteanterior").val();
    var numeroagente = Numero;
    if(numeroagenteanterior != numeroagente){
        $("#numeroagente").val(Numero);
        $("#numeroagenteanterior").val(Numero);
        $("#agente").val(Nombre);
        if(Nombre != null){
            $("#textonombreagente").html(Nombre.substring(0, 60));
        }
        mostrarformulario();
    }
}
//obtener por numero
function obtenerclientepornumero(){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    var numerocliente = $("#numerocliente").val();
    if(numeroclienteanterior != numerocliente){
        if($("#numerocliente").parsley().isValid()){
            $.get(cotizaciones_servicios_obtener_cliente_por_numero, {numerocliente:numerocliente}, function(data){
                $("#numerocliente").val(data.numero);
                $("#numeroclienteanterior").val(data.numero);
                $("#cliente").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombrecliente").html(data.nombre.substring(0, 40));
                }
                $("#plazo").val(data.plazo);
                //datos agente
                $("#numeroagente").val(data.numeroagente);
                $("#numeroagenteanterior").val(data.numeroagente);
                $("#agente").val(data.nombreagente);
                if(data.nombreagente != null){
                    $("#textonombreagente").html(data.nombreagente.substring(0, 40));
                }
                mostrarformulario();
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
function obteneragentepornumero(){
    var numeroagenteanterior = $("#numeroagenteanterior").val();
    var numeroagente = $("#numeroagente").val();
    if(numeroagenteanterior != numeroagente){
        if($("#numeroagente").parsley().isValid()){
            $.get(cotizaciones_servicios_obtener_agente_por_numero, {numeroagente:numeroagente}, function(data){
                $("#numeroagente").val(data.numero);
                $("#numeroagenteanterior").val(data.numero);
                $("#agente").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombreagente").html(data.nombre.substring(0, 60));
                }
            })  
        }
    }
}
//regresar numero
function regresarnumeroagente(){
    var numeroagenteanterior = $("#numeroagenteanterior").val();
    $("#numeroagente").val(numeroagenteanterior);
}
//obtener registros de vines
function listarvines(){
    ocultarformulario();
    var tablavines = '<div class="modal-header '+background_forms_and_modals+'">'+
                                  '<h4 class="modal-title">Vines</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                  '<div class="row">'+
                                      '<div class="col-md-12">'+
                                          '<div class="table-responsive">'+
                                              '<table id="tbllistadovines" class="tbllistadovines table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                  '<thead class="'+background_tables+'">'+
                                                      '<tr>'+
                                                          '<th>Operaciones</th>'+
                                                          '<th>Economico</th>'+
                                                          '<th>Vin</th>'+
                                                          '<th>Placas</th>'+
                                                          '<th>Motor</th>'+
                                                          '<th>Marca</th>'+
                                                          '<th>Modelo</th>'+
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
    $("#contenidomodaltablas").html(tablavines);
    $('#tbllistadovines').DataTable({ 
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
            url: cotizaciones_servicios_obtener_vines,
            data: function (d) {
              d.numeroclientefacturaa = $("#numerocliente").val();
            }
          },
          columns: [
              { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
              { data: 'Economico', name: 'Economico' },
              { data: 'Vin', name: 'Vin' },
              { data: 'Placas', name: 'Placas', orderable: false, searchable: false },
              { data: 'Motor', name: 'Motor', orderable: false, searchable: false },
              { data: 'Marca', name: 'Marca', orderable: false, searchable: false },
              { data: 'Modelo', name: 'Modelo', orderable: false, searchable: false }
          ],
          "initComplete": function() {
              var $buscar = $('div.dataTables_filter input');
              $buscar.unbind();
              $buscar.bind('keyup change', function(e) {
                  if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadovines').DataTable().search( this.value ).draw();
                  }
              });
          },
    }); 
} 
function seleccionarvin(Cliente, Economico, Vin, Placas, Motor, Marca, Modelo, Año, Color){
    var vinanterior = $("#vinanterior").val();
    var vin = Vin;
    if(vinanterior != vin){
        $("#vin").val(Vin);
        $("#vinanterior").val(Vin);
        $("#textonombrevin").html(Vin);
        $("#motor").val(Motor);
        $("#marca").val(Marca);
        $("#modelo").val(Modelo);
        $("#ano").val(Año);
        $("#placas").val(Placas);
        $("#economico").val(Economico);
        $("#color").val(Color);
        mostrarformulario();
    }
}
//obtener por numero
function obtenervinpornumero(){
    var vinanterior = $("#vinanterior").val();
    var vin = $("#vin").val();
    if(vinanterior != vin){
        if($("#vin").parsley().isValid()){
            var vin = $("#vin").val();
            var numeroclientefacturaa = $("#numerocliente").val();
            $.get(cotizaciones_servicios_obtener_vin_por_numero, {vin:vin,numeroclientefacturaa:numeroclientefacturaa}, function(data){
                $("#vin").val(data.vin);
                $("#vinanterior").val(data.vin);
                $("#textonombrevin").html(data.vin);
                $("#motor").val(data.motor);
                $("#marca").val(data.marca);
                $("#modelo").val(data.modelo);
                $("#ano").val(data.año);
                $("#placas").val(data.placas);
                $("#economico").val(data.economico);
                $("#color").val(data.color);
                mostrarformulario();
            }) 
        }
    }
}
//listar productos
function listarproductos(){
    ocultarformulario();
    var tablaproductos ='<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Productos</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoproducto" class="tbllistadoproducto table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Código</th>'+
                                                    '<th>Marca</th>'+
                                                    '<th>Producto</th>'+
                                                    '<th>Ubicación</th>'+
                                                    '<th>Existencias</th>'+
                                                    '<th>Almacen</th>'+
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
    $("#contenidomodaltablas").html(tablaproductos);
    $('#tbllistadoproducto').DataTable({
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
            url: cotizaciones_servicios_obtener_productos,
            data: function (d) {
                d.codigoabuscar = $("#codigoabuscar").val();
                d.tipooperacion = $("#tipooperacion").val();
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false  },
            { data: 'Codigo', name: 'Codigo' },
            { data: 'Marca', name: 'Marca', orderable: false, searchable: false  },
            { data: 'Producto', name: 'Producto' },
            { data: 'Ubicacion', name: 'Ubicacion', orderable: false, searchable: false  },
            { data: 'Existencias', name: 'Existencias', orderable: false, searchable: false  },
            { data: 'Almacen', name: 'Almacen', orderable: false, searchable: false  },
            { data: 'Costo', name: 'Costo', orderable: false, searchable: false  },
            { data: 'SubTotal', name: 'SubTotal', orderable: false, searchable: false  } 
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                    $('#tbllistadoproducto').DataTable().search( this.value ).draw();
                }
            });
        },
    });
}
function obtenerproductoporcodigo(){
  var codigoabuscar = $("#codigoabuscar").val();
  var tipooperacion = $("#tipooperacion").val();
  $.get(cotizaciones_servicios_obtener_producto_por_codigo,{codigoabuscar:codigoabuscar}, function(data){
    if(parseInt(data.contarproductos) > 0){
      agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Impuesto, data.SubTotal, data.Existencias, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, data.CostoDeLista);
    }else{
      msjnoseencontroningunproducto();
    }
  }) 
}
//función que evalua si la partida que quieren ingresar ya existe o no en el detalle de la orden de compra
function evaluarproductoexistente(Codigo){
    var sumaiguales=0;
    var sumadiferentes=0;
    var sumatotal=0;
    $("tr.filasproductos").each(function () {
        var codigoproducto = $('.codigoproductopartida', this).val();
        if(Codigo === codigoproducto){
            sumaiguales++;
        }else{
            sumadiferentes++;
        }
        sumatotal++;
    });
    var resta = parseInt(sumadiferentes) - parseInt(sumaiguales);
    var total = sumatotal;
    if(resta != total){
        var result = true;
    }else{
        var result = false;
    }
    return result;
}
//calcular total de la orden de compra
function calculartotalesfilas(fila){
    // for each por cada fila:
    var cuentaFilas = 0;
    $("tr.filasproductos").each(function () { 
        if(fila === cuentaFilas){
            // obtener los datos de la fila:
            var cantidadpartida = $(".cantidadpartida", this).val();
            var preciopartida = $('.preciopartida', this).val();
            var importepartida = $('.importepartida', this).val();
            var descuentopesospartida = $('.descuentopesospartida', this).val();
            var subtotalpartida = $('.subtotalpartida', this).val();
            var ivaporcentajepartida = $('.ivaporcentajepartida', this).val();
            var ivapesospartida = $('.ivapesospartida', this).val();
            var totalpesospartida = $('.totalpesospartida', this).val(); 
            var utilidadpartida = $(".utilidadpartida", this).val();
            var costopartida = $(".costopartida", this).val();
            var costototalpartida = $(".costototalpartida ", this).val();
            var comisionporcentajepartida = $('.comisionporcentajepartida ', this).val();
            var comisionespesospartida = $('.comisionespesospartida ', this).val();
            //importe de la partida
            importepartida =  new Decimal(cantidadpartida).times(preciopartida);
            $('.importepartida', this).val(number_format(round(importepartida, numerodecimales), numerodecimales, '.', ''));
            //subtotal de la partida
            subtotalpartida =  new Decimal(importepartida).minus(descuentopesospartida);
            $('.subtotalpartida', this).val(number_format(round(subtotalpartida, numerodecimales), numerodecimales, '.', ''));
            //iva en pesos de la partida
            var multiplicacionivapesospartida = new Decimal(subtotalpartida).times(ivaporcentajepartida);
            ivapesospartida = new Decimal(multiplicacionivapesospartida/100);
            $('.ivapesospartida', this).val(number_format(round(ivapesospartida, numerodecimales), numerodecimales, '.', ''));
            //total en pesos de la partida
            totalpesospartida = new Decimal(subtotalpartida).plus(ivapesospartida);
            $('.totalpesospartida', this).val(number_format(round(totalpesospartida, numerodecimales), numerodecimales, '.', ''));
            //costo total
            costototalpartida  = new Decimal(costopartida).times(cantidadpartida);
            $('.costototalpartida', this).val(number_format(round(costototalpartida, numerodecimales), numerodecimales, '.', ''));
            //comision de la partida
            var comisionporcentajepartida = new Decimal(subtotalpartida).times(comisionporcentajepartida);
            comisionespesospartida = new Decimal(comisionporcentajepartida/100);
            $('.comisionespesospartida', this).val(number_format(round(comisionespesospartida, numerodecimales), numerodecimales, '.', ''));
            //utilidad de la partida
            utilidadpartida = new Decimal(subtotalpartida).minus(costototalpartida).minus(comisionespesospartida);
            $(".utilidadpartida", this).val(number_format(round(utilidadpartida, numerodecimales), numerodecimales, '.', ''));
            calculartotal();
        }  
        cuentaFilas++;
    });
}
//dejar en 0 los descuentos cuando el precio de la partida se cambie
function cambiodecantidadpartida(fila,tipo){
    var cuentaFilas = 0;
    $("tr.filasproductos").each(function () {
        if(fila === cuentaFilas){  
            //indice de surtimiento
            var cantidadpartida = $('.cantidadpartida', this).val();
            var existenciaactualpartida = $('.existenciaactualpartida', this).val();
            $(".cantidadsolicitadapartida", this).val(number_format(round(cantidadpartida, numerodecimales), numerodecimales, '.', ''));
            var multiplicacionexistenciaporindicesurtimiento = new Decimal(existenciaactualpartida).times(100);
            var indicesurtimientopartida = new Decimal(multiplicacionexistenciaporindicesurtimiento).dividedBy(cantidadpartida);
            $(".indicesurtimientopartida", this).val(number_format(round(indicesurtimientopartida, numerodecimales), numerodecimales, '.', ''));
            //descuentos en 0
            $('.descuentopesospartida', this).val('0.'+numerocerosconfigurados); 
            $('.descuentoporcentajepartida',this).val('0.'+numerocerosconfigurados);
            calculartotalesfilas(fila);
        }  
        cuentaFilas++;
    });   
}
//dejar en 0 los descuentos cuando el precio de la partida se cambie
function cambiodepreciopartida(fila,tipo){
    var cuentaFilas = 0;
    $("tr.filasproductos").each(function () {
        if(fila === cuentaFilas){  
            //validar si se capturara precio neto
            if( $('#idcapturaprecioneto').prop('checked') ) {
                var preciopartida = $('.preciopartida', this).val();
                var ivaporcentajepartida = $('.ivaporcentajepartida', this).val();
                var nuevoiva = new Decimal(ivaporcentajepartida).dividedBy(100).plus(1);
                var precioneto = new Decimal(preciopartida).dividedBy(nuevoiva);
                $(".preciopartida", this).val(number_format(round(precioneto, numerodecimales), numerodecimales, '.', ''));
            }
            //descuentos en 0
            $('.descuentopesospartida', this).val('0.'+numerocerosconfigurados); 
            $('.descuentoporcentajepartida',this).val('0.'+numerocerosconfigurados);
            calculartotalesfilas(fila);
        }  
        cuentaFilas++;
    });   
}
//calcular el porcentaje de descuento cuando el descuento en pesos se modifique
function calculardescuentoporcentajepartida(fila){
    var cuentaFilas = 0;
    $("tr.filasproductos").each(function () {
        if(fila === cuentaFilas){  
            //descuento en porcentaje de la partida
            var importepartida = $('.importepartida', this).val(); 
            var descuentopesospartida = $('.descuentopesospartida', this).val(); 
            var multiplicaciondescuentoporcentajepartida  =  new Decimal(descuentopesospartida).times(100);
            var descuentoporcentajepartida = new Decimal(multiplicaciondescuentoporcentajepartida/importepartida);
            $('.descuentoporcentajepartida', this).val(number_format(round(descuentoporcentajepartida, numerodecimales), numerodecimales, '.', ''));
            calculartotalesfilas(fila);
        }  
        cuentaFilas++;
    });    
}
//calcular el descuento en pesos cuando hay cambios en el porcentaje de descuento
function calculardescuentopesospartida(fila){
    var cuentaFilas = 0;
    $("tr.filasproductos").each(function () {
      if(fila === cuentaFilas){   
        //descuento en pesos de la partida
        var importepartida = $('.importepartida', this).val();
        var descuentoporcentajepartida = $('.descuentoporcentajepartida', this).val();
        var multiplicaciondescuentopesospartida  =  new Decimal(importepartida).times(descuentoporcentajepartida);
        var descuentopesospartida = new Decimal(multiplicaciondescuentopesospartida/100);
        $('.descuentopesospartida', this).val(number_format(round(descuentopesospartida, numerodecimales), numerodecimales, '.', ''));
        calculartotalesfilas(fila);
      }  
      cuentaFilas++;
    }); 
}      
//calcular totales de orden de compra
function calculartotal(){
    var importe = 0;
    var descuento = 0;
    var subtotal= 0;
    var iva = 0;
    var total = 0;
    var costo = 0;
    var utilidad = 0;
    var comision = 0;
    var importeservicio = 0;
    var descuentoservicio = 0;
    var subtotalservicio= 0;
    var ivaservicio = 0;
    var totalservicio = 0;
    var costoservicio = 0;
    var utilidadservicio = 0;
    var comisionservicio = 0;
    var importetotal = 0;
    var descuentototal = 0;
    var subtotaltotal = 0;
    var ivatotal = 0;
    var totaltotal = 0;
    var costototal = 0;
    var utilidadtotal = 0;
    var comisiontotal = 0;
    //productos
    $("tr.filasproductos").each(function(){
        importe= new Decimal(importe).plus($(".importepartida", this).val());
        descuento = new Decimal(descuento).plus($(".descuentopesospartida", this).val());
        subtotal= new Decimal(subtotal).plus($(".subtotalpartida", this).val());
        iva = new Decimal(iva).plus($(".ivapesospartida", this).val());
        total = new Decimal(total).plus($(".totalpesospartida", this).val());
        costo = new Decimal(costo).plus($(".costototalpartida ", this).val());
        utilidad = new Decimal(utilidad).plus($(".utilidadpartida", this).val());
        comision = new Decimal(comision).plus($(".comisionespesospartida", this).val());
    }); 
    $("#importe").val(number_format(round(importe, numerodecimales), numerodecimales, '.', ''));
    $("#descuento").val(number_format(round(descuento, numerodecimales), numerodecimales, '.', ''));
    $("#subtotal").val(number_format(round(subtotal, numerodecimales), numerodecimales, '.', ''));
    $("#iva").val(number_format(round(iva, numerodecimales), numerodecimales, '.', ''));
    $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
    $("#costo").val(number_format(round(costo, numerodecimales), numerodecimales, '.', ''));
    $("#utilidad").val(number_format(round(utilidad, numerodecimales), numerodecimales, '.', ''));
    $("#comision").val(number_format(round(comision, numerodecimales), numerodecimales, '.', ''));
    //servicios
    $("tr.filasservicios").each(function(){
        importeservicio = new Decimal(importeservicio).plus($(".importepartidaservicio", this).val());
        descuentoservicio = new Decimal(descuentoservicio).plus($(".descuentopesospartidaservicio", this).val());
        subtotalservicio = new Decimal(subtotalservicio).plus($(".subtotalpartidaservicio", this).val());
        ivaservicio = new Decimal(ivaservicio).plus($(".ivapesospartidaservicio", this).val());
        totalservicio = new Decimal(totalservicio).plus($(".totalpesospartidaservicio", this).val());
        costoservicio = new Decimal(costoservicio).plus($(".costototalpartidaservicio", this).val());
        utilidadservicio = new Decimal(utilidadservicio).plus($(".utilidadpartidaservicio", this).val());
        comisionservicio = new Decimal(comisionservicio).plus($(".comisionespesospartidaservicio", this).val());
    }); 
    $("#importeservicio").val(number_format(round(importeservicio, numerodecimales), numerodecimales, '.', ''));
    $("#descuentoservicio").val(number_format(round(descuentoservicio, numerodecimales), numerodecimales, '.', ''));
    $("#subtotalservicio").val(number_format(round(subtotalservicio, numerodecimales), numerodecimales, '.', ''));
    $("#ivaservicio").val(number_format(round(ivaservicio, numerodecimales), numerodecimales, '.', ''));
    $("#totalservicio").val(number_format(round(totalservicio, numerodecimales), numerodecimales, '.', ''));
    $("#costoservicio").val(number_format(round(costoservicio, numerodecimales), numerodecimales, '.', ''));
    $("#utilidadservicio").val(number_format(round(utilidadservicio, numerodecimales), numerodecimales, '.', ''));
    $("#comisionservicio").val(number_format(round(comisionservicio, numerodecimales), numerodecimales, '.', ''));
    //totales

    importetotal = new Decimal(importe).plus(importeservicio);
    descuentototal = new Decimal(descuento).plus(descuentoservicio);
    subtotaltotal = new Decimal(subtotal).plus(subtotalservicio);
    ivatotal = new Decimal(iva).plus(ivaservicio);
    totaltotal = new Decimal(total).plus(totalservicio);
    costototal = new Decimal(costo).plus(costoservicio);
    utilidadtotal = new Decimal(utilidad).plus(utilidadservicio);
    comisiontotal = new Decimal(comision).plus(comisionservicio); 
    $("#importetotal").val(number_format(round(importetotal, numerodecimales), numerodecimales, '.', ''));
    $("#descuentototal").val(number_format(round(descuentototal, numerodecimales), numerodecimales, '.', ''));
    $("#subtotaltotal").val(number_format(round(subtotaltotal, numerodecimales), numerodecimales, '.', ''));
    $("#ivatotal").val(number_format(round(ivatotal, numerodecimales), numerodecimales, '.', ''));
    $("#totaltotal").val(number_format(round(totaltotal, numerodecimales), numerodecimales, '.', ''));
    $("#costototal").val(number_format(round(costototal, numerodecimales), numerodecimales, '.', ''));
    $("#utilidadtotal").val(number_format(round(utilidadtotal, numerodecimales), numerodecimales, '.', ''));
    $("#comisiontotal").val(number_format(round(comisiontotal, numerodecimales), numerodecimales, '.', ''));
}
//agregar una fila en la tabla de precios productos
var contadorproductos=0;
var contadorfilas = 0;
function agregarfilaproducto(Codigo, Producto, Unidad, Costo, Impuesto, SubTotal, Existencias, tipooperacion, Insumo, ClaveProducto, ClaveUnidad, CostoDeLista){
    var result = evaluarproductoexistente(Codigo);
    if(result == false){
        $.get(cotizaciones_servicios_obtener_existencias_almacen_uno, {Codigo:Codigo}, function(exis){
            var indicesurtimientopartida = new Decimal(exis).times(100);
            var multiplicacioncostoimpuesto =  new Decimal(SubTotal).times(Impuesto);      
            var ivapesos = new Decimal(multiplicacioncostoimpuesto/100);
            var total = new Decimal(SubTotal).plus(ivapesos);
            var preciopartida = SubTotal;
            var comisionporcentaje = new Decimal(SubTotal).times(0);
            var comisionespesos= new Decimal(comisionporcentaje/100);
            var utilidad = new Decimal(SubTotal).minus(Costo).minus(comisionespesos);
            var tipo = "alta";
            var fila= '<tr class="filasproductos" id="filaproducto'+contadorproductos+'">'+
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('+contadorproductos+')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]">'+Codigo+'</td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'+Producto+'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'+Unidad+'" readonly data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+Unidad+'</td>'+
                        '<td class="tdmod">'+
                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');cambiodecantidadpartida('+contadorfilas+',\''+tipo +'\');">'+
                            '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'+                           
                        '</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');cambiodepreciopartida('+contadorfilas+',\''+tipo +'\');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'+Impuesto+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'+number_format(round(ivapesos, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'+number_format(round(total, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('+contadorfilas+');" required></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm comisionespesospartida" name="comisionespesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'+number_format(round(utilidad, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm existenciaactualpartida" name="existenciaactualpartida[]" value="'+exis+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodxl cantidadsolicitadapartida" name="cantidadsolicitadapartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm indicesurtimientopartida" name="indicesurtimientopartida[]" value="'+number_format(round(indicesurtimientopartida, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly data-parsley-length="[1, 3]" autocomplete="off"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costolistapartida" name="costolistapartida[]" value="'+CostoDeLista+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                        '</tr>';
            contadorproductos++;
            contadorfilas++;
            $("#tablaproductocotizacion").append(fila);
            mostrarformulario();      
            comprobarfilas();
            calculartotal();
            $("#codigoabuscar").val("");
        });
    }else{
        msj_errorproductoyaagregado();
    }  
}
//eliminar una fila en la tabla de precios clientes
function eliminarfila(numerofila){
    var confirmacion = confirm("Esta seguro de eliminar el producto?"); 
    if (confirmacion == true) { 
        $("#filaproducto"+numerofila).remove();
        contadorfilas--; //importante para todos los calculo en el modulo de orden de compra se debe restar al contadorfilas la fila que se acaba de eliminar
        comprobarfilas();
        renumerarfilas();//importante para todos los calculo en el modulo de orden de compra 
        calculartotal();
    }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
    var numerofilas = $("#tablaproductocotizacion tbody tr").length;
    $("#numerofilas").val(numerofilas);
}
//renumerar las filas de la orden de compra
function renumerarfilas(){
    var lista;
    var tipo = "alta";
    //renumerar la cantidad de la partida
    lista = document.getElementsByClassName("cantidadpartida");
    for (var i = 0; i < lista.length; i++) {
        lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+');cambiodecantidadpartida('+i+',\''+tipo +'\')');
    }
    //renumero el precio de la partida
    lista = document.getElementsByClassName("preciopartida");
    for (var i = 0; i < lista.length; i++) {
        lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+');cambiodepreciopartida('+i+',\''+tipo +'\')');
    }
    //renumerar descuento en pesos
    lista = document.getElementsByClassName("descuentoporcentajepartida");
    for (var i = 0; i < lista.length; i++) {
        lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculardescuentopesospartida("+i+')');
    }
    //renumerar porcentaje de descuento
    lista = document.getElementsByClassName("descuentopesospartida");
    for (var i = 0; i < lista.length; i++) {
        lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida("+i+')');
    }
    //renumerar porcentaje de iva
    lista = document.getElementsByClassName("ivaporcentajepartida");
    for (var i = 0; i < lista.length; i++) {
        lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
    }
}  
//listar servicios
function listarservicios(){
    ocultarformulario();
    var tablaservicios ='<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Servicios</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoservicio" class="tbllistadoservicio table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Código</th>'+
                                                    '<th>Servicio</th>'+
                                                    '<th>Unidad</th>'+
                                                    '<th>Venta</th>'+
                                                    '<th>Cantidad</th>'+
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
    $("#contenidomodaltablas").html(tablaservicios);
    $('#tbllistadoservicio').DataTable({
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
            url: cotizaciones_servicios_obtener_servicios,
            data: function (d) {
                d.codigoservicioabuscar = $("#codigoservicioabuscar").val();
                d.tipooperacion = $("#tipooperacion").val();
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false  },
            { data: 'Codigo', name: 'Codigo' },
            { data: 'Servicio', name: 'Servicio' },
            { data: 'Unidad', name: 'Unidad' },
            { data: 'Venta', name: 'Venta', orderable: false, searchable: false  },
            { data: 'Cantidad', name: 'Cantidad', orderable: false, searchable: false  }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                    $('#tbllistadoservicio').DataTable().search( this.value ).draw();
                }
            });
        },
    });
}
function obtenerservicioporcodigo(){
  var codigoservicioabuscar = $("#codigoservicioabuscar").val();
  var tipooperacion = $("#tipooperacion").val();
  $.get(cotizaciones_servicios_obtener_servicio_por_codigo,{codigoservicioabuscar:codigoservicioabuscar}, function(data){
    if(parseInt(data.contarservicios) > 0){
      agregarfilaservicio(data.Codigo, data.Servicio, data.Unidad, data.Familia, data.Costo, data.Venta, data.Cantidad, tipooperacion);
    }else{
      msjnoseencontroningunproducto();
    }
  }) 
}
//función que evalua si la partida que quieren ingresar ya existe o no en el detalle de la orden de compra
function evaluarservicioexistente(Codigo){
    var sumaiguales=0;
    var sumadiferentes=0;
    var sumatotal=0;
    $("tr.filasservicios").each(function () {
        var codigoservicio = $('.codigoserviciopartida', this).val();
        if(Codigo === codigoservicio){
            sumaiguales++;
        }else{
            sumadiferentes++;
        }
        sumatotal++;
    });
    var resta = parseInt(sumadiferentes) - parseInt(sumaiguales);
    var total = sumatotal;
    if(resta != total){
        var result = true;
    }else{
        var result = false;
    }
    return result;
}
//calcular total de la orden de compra
function calculartotalesfilasservicios(fila){
    // for each por cada fila:
    var cuentaFilas = 0;
    $("tr.filasservicios").each(function () { 
        if(fila === cuentaFilas){
            // obtener los datos de la fila:
            var cantidadpartidaservicio = $(".cantidadpartidaservicio", this).val();
            var preciopartidaservicio = $('.preciopartidaservicio', this).val();
            var importepartidaservicio = $('.importepartidaservicio', this).val();
            var descuentopesospartidaservicio = $('.descuentopesospartidaservicio', this).val();
            var subtotalpartidaservicio = $('.subtotalpartidaservicio', this).val();
            var ivaporcentajepartidaservicio = $('.ivaporcentajepartidaservicio', this).val();
            var ivapesospartidaservicio = $('.ivapesospartidaservicio', this).val();
            var totalpesospartidaservicio = $('.totalpesospartidaservicio', this).val(); 
            var utilidadpartidaservicio = $(".utilidadpartidaservicio", this).val();
            var costopartidaservicio = $(".costopartidaservicio", this).val();
            var costototalpartidaservicio = $(".costototalpartidaservicio ", this).val();
            var comisionporcentajepartidaservicio = $('.comisionporcentajepartidaservicio ', this).val();
            var comisionespesospartidaservicio = $('.comisionespesospartidaservicio ', this).val();
            //importe de la partida
            importepartidaservicio =  new Decimal(cantidadpartidaservicio).times(preciopartidaservicio);
            $('.importepartidaservicio', this).val(number_format(round(importepartidaservicio, numerodecimales), numerodecimales, '.', ''));
            //subtotal de la partida
            subtotalpartidaservicio =  new Decimal(importepartidaservicio).minus(descuentopesospartidaservicio);
            $('.subtotalpartidaservicio', this).val(number_format(round(subtotalpartidaservicio, numerodecimales), numerodecimales, '.', ''));
            //iva en pesos de la partida
            var multiplicacionivapesospartida = new Decimal(subtotalpartidaservicio).times(ivaporcentajepartidaservicio);
            ivapesospartidaservicio = new Decimal(multiplicacionivapesospartida/100);
            $('.ivapesospartidaservicio', this).val(number_format(round(ivapesospartidaservicio, numerodecimales), numerodecimales, '.', ''));
            //total en pesos de la partida
            totalpesospartidaservicio = new Decimal(subtotalpartidaservicio).plus(ivapesospartidaservicio);
            $('.totalpesospartidaservicio', this).val(number_format(round(totalpesospartidaservicio, numerodecimales), numerodecimales, '.', ''));
            //costo total
            costototalpartidaservicio  = new Decimal(costopartidaservicio).times(cantidadpartidaservicio);
            $('.costototalpartidaservicio', this).val(number_format(round(costototalpartidaservicio, numerodecimales), numerodecimales, '.', ''));
            //comision de la partida
            var comisionporcentajepartidaservicio = new Decimal(subtotalpartidaservicio).times(comisionporcentajepartidaservicio);
            comisionespesospartidaservicio = new Decimal(comisionporcentajepartidaservicio/100);
            $('.comisionespesospartidaservicio', this).val(number_format(round(comisionespesospartidaservicio, numerodecimales), numerodecimales, '.', ''));
            //utilidad de la partida
            utilidadpartidaservicio = new Decimal(subtotalpartidaservicio).minus(costototalpartidaservicio).minus(comisionespesospartidaservicio);
            $(".utilidadpartidaservicio", this).val(number_format(round(utilidadpartidaservicio, numerodecimales), numerodecimales, '.', ''));
            calculartotal();
        }  
        cuentaFilas++;
    });
}
//dejar en 0 los descuentos cuando el precio de la partida se cambie
function cambiodecantidadpartidaservicio(fila,tipo){
    var cuentaFilas = 0;
    $("tr.filasservicios").each(function () {
        if(fila === cuentaFilas){  
            //descuentos en 0
            $('.descuentopesospartidaservicio', this).val('0.'+numerocerosconfigurados); 
            $('.descuentoporcentajepartidaservicio',this).val('0.'+numerocerosconfigurados);
            calculartotalesfilasservicios(fila);
        }  
        cuentaFilas++;
    });   
}
//dejar en 0 los descuentos cuando el precio de la partida se cambie
function cambiodepreciopartidaservicio(fila,tipo){
    var cuentaFilas = 0;
    $("tr.filasservicios").each(function () {
        if(fila === cuentaFilas){  
            //validar si se capturara precio neto
            if( $('#idcapturaprecioneto').prop('checked') ) {
                var preciopartidaservicio = $('.preciopartidaservicio', this).val();
                var ivaporcentajepartidaservicio = $('.ivaporcentajepartidaservicio', this).val();
                var nuevoiva = new Decimal(ivaporcentajepartidaservicio).dividedBy(100).plus(1);
                var precioneto = new Decimal(preciopartidaservicio).dividedBy(nuevoiva);
                $(".preciopartidaservicio", this).val(number_format(round(precioneto, numerodecimales), numerodecimales, '.', ''));
            }
            //descuentos en 0
            $('.descuentopesospartidaservicio', this).val('0.'+numerocerosconfigurados); 
            $('.descuentoporcentajepartidaservicio',this).val('0.'+numerocerosconfigurados);
            calculartotalesfilasservicios(fila);
        }  
        cuentaFilas++;
    });   
}
//calcular el porcentaje de descuento cuando el descuento en pesos se modifique
function calculardescuentoporcentajepartidaservicio(fila){
    var cuentaFilas = 0;
    $("tr.filasservicios").each(function () {
        if(fila === cuentaFilas){  
            //descuento en porcentaje de la partida
            var importepartidaservicio = $('.importepartidaservicio', this).val(); 
            var descuentopesospartidaservicio = $('.descuentopesospartidaservicio', this).val(); 
            var multiplicaciondescuentoporcentajepartida  =  new Decimal(descuentopesospartidaservicio).times(100);
            var descuentoporcentajepartidaservicio = new Decimal(multiplicaciondescuentoporcentajepartida/importepartidaservicio);
            $('.descuentoporcentajepartidaservicio', this).val(number_format(round(descuentoporcentajepartidaservicio, numerodecimales), numerodecimales, '.', ''));
            calculartotalesfilasservicios(fila);
        }  
        cuentaFilas++;
    });    
}
//calcular el descuento en pesos cuando hay cambios en el porcentaje de descuento
function calculardescuentopesospartidaservicio(fila){
    var cuentaFilas = 0;
    $("tr.filasservicios").each(function () {
        if(fila === cuentaFilas){   
          //descuento en pesos de la partida
          var importepartidaservicio = $('.importepartidaservicio', this).val();
          var descuentoporcentajepartidaservicio = $('.descuentoporcentajepartidaservicio', this).val();
          var multiplicaciondescuentopesospartida  =  new Decimal(importepartidaservicio).times(descuentoporcentajepartidaservicio);
          var descuentopesospartidaservicio = new Decimal(multiplicaciondescuentopesospartida/100);
          $('.descuentopesospartidaservicio', this).val(number_format(round(descuentopesospartidaservicio, numerodecimales), numerodecimales, '.', ''));
          calculartotalesfilasservicios(fila);
        }  
        cuentaFilas++;
    }); 
}      
//agregar una fila en la tabla de precios productos
var contadorservicios=0;
var contadorfilasservicios = 0;
function agregarfilaservicio(Codigo, Servicio, Unidad, Familia, Costo, Venta, Cantidad, tipooperacion){
    var result = evaluarservicioexistente(Codigo);
    if(result == false){
        var tipo = "alta";
        var fila='<tr class="filasservicios" id="filaservicio'+contadorservicios+'">'+
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilaservicio('+contadorservicios+')">X</div><input type="hidden" class="form-control agregadoenservicio" name="agregadoenservicio[]" value="'+tipooperacion+'" readonly></td>'+
                    '<td class="tdmod"><input type="hidden" class="form-control codigoserviciopartida" name="codigoserviciopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]">'+Codigo+'</td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionserviciopartida" name="descripcionserviciopartida[]" value="'+Servicio+'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'+
                    '<td class="tdmod"><input type="hidden" class="form-control unidadserviciopartida" name="unidadserviciopartida[]" value="'+Unidad+'" readonly data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'+Unidad+'</td>'+
                    '<td class="tdmod">'+
                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartidaservicio" name="cantidadpartidaservicio[]" value="0.'+numerocerosconfigurados+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasservicios('+contadorfilasservicios+');cambiodecantidadpartidaservicio('+contadorfilasservicios+',\''+tipo +'\');">'+
                    '</td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciopartidaservicio" name="preciopartidaservicio[]" value="'+Venta+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasservicios('+contadorfilasservicios+');cambiodepreciopartidaservicio('+contadorfilasservicios+',\''+tipo +'\');"></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartidaservicio" name="importepartidaservicio[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentoporcentajepartidaservicio" name="descuentoporcentajepartidaservicio[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartidaservicio('+contadorfilasservicios+');"></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentopesospartidaservicio" name="descuentopesospartidaservicio[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartidaservicio('+contadorfilasservicios+');"></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartidaservicio" name="subtotalpartidaservicio[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivaporcentajepartidaservicio" name="ivaporcentajepartidaservicio[]" value="16.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasservicios('+contadorfilasservicios+');"></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivapesospartidaservicio" name="ivapesospartidaservicio[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartidaservicio" name="totalpesospartidaservicio[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costopartidaservicio" name="costopartidaservicio[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costototalpartidaservicio" name="costototalpartidaservicio[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm comisionporcentajepartidaservicio" name="comisionporcentajepartidaservicio[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartidaservicio('+contadorfilasservicios+');" required></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm comisionespesospartidaservicio" name="comisionespesospartidaservicio[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm utilidadpartidaservicio" name="utilidadpartidaservicio[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                  '</tr>';
        contadorservicios++;
        contadorfilasservicios++;
        $("#tablaserviciocotizacion").append(fila);
        mostrarformulario();      
        comprobarfilasservicios();
        calculartotal();
        $("#codigoservicioabuscar").val("");
    }else{
      msj_errorservicioyaagregado();
    }  
}
//eliminar una fila en la tabla de precios clientes
function eliminarfilaservicio(numerofila){
    var confirmacion = confirm("Esta seguro de eliminar el servicio?"); 
    if (confirmacion == true) { 
        $("#filaservicio"+numerofila).remove();
        contadorfilasservicios--; //importante para todos los calculo en el modulo de orden de compra se debe restar al contadorfilas la fila que se acaba de eliminar
        comprobarfilasservicios();
        renumerarfilasservicios();//importante para todos los calculo en el modulo de orden de compra 
        calculartotal();
    }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilasservicios(){
    var numerofilas = $("#tablaserviciocotizacion tbody tr").length;
    $("#numerofilasservicios").val(numerofilas);
}
//renumerar las filas de la orden de compra
function renumerarfilasservicios(){
    var lista;
    var tipo = "alta";
    //renumerar la cantidad de la partida
    lista = document.getElementsByClassName("cantidadpartidaservicio");
    for (var i = 0; i < lista.length; i++) {
        lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilasservicios("+i+');cambiodecantidadpartidaservicio('+i+',\''+tipo +'\')');
    }
    //renumero el precio de la partida
    lista = document.getElementsByClassName("preciopartidaservicio");
    for (var i = 0; i < lista.length; i++) {
        lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilasservicios("+i+');cambiodepreciopartidaservicio('+i+',\''+tipo +'\')');
    }
    //renumerar descuento en pesos
    lista = document.getElementsByClassName("descuentoporcentajepartidaservicio");
    for (var i = 0; i < lista.length; i++) {
        lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculardescuentopesospartidaservicio("+i+')');
    }
    //renumerar porcentaje de descuento
    lista = document.getElementsByClassName("descuentopesospartidaservicio");
    for (var i = 0; i < lista.length; i++) {
        lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculardescuentoporcentajepartidaservicio("+i+')');
    }
    //renumerar porcentaje de iva
    lista = document.getElementsByClassName("ivaporcentajepartidaservicio");
    for (var i = 0; i < lista.length; i++) {
        lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilasservicios("+i+')');
    }
}  
//alta clientes
function alta(){
    $("#titulomodal").html('Alta Cotización Servicio');
    mostrarmodalformulario('ALTA', 1);
    mostrarformulario();
    //formulario alta
    var tabs =  '<div class="col-md-12">'+
                    '<div class="row">'+
                        '<div class="col-md-3">'+
                            '<label>Cotización <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b> &nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                            '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                            '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" data-parsley-length="[0, 10]" required readonly>'+
                            '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" value="alta" readonly>'+
                            '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" value="0" readonly>'+
                            '<input type="hidden" class="form-control" name="numerofilasservicios" id="numerofilasservicios" value="0" readonly>'+
                        '</div>'+  
                        '<div class="col-md-2">'+
                            '<label>Plazo Días</label>'+
                            '<input type="text" class="form-control" name="plazo" id="plazo"  required autocomplete="off">'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<label>Referencia</label>'+
                            '<input type="text" class="form-control" name="referencia" id="referencia" data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<label>Unidad</label>'+
                            '<select name="unidad" id="unidad" class="form-control select2" style="width:100% !important;" required>'+ 
                            '</select>'+
                        '</div>'+
                        '<div class="col-xs-12 col-sm-12 col-md-3">'+
                            '<label>Fecha</label>'+
                            '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();" style="min-width:95%;">'+
                            '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                        '</div>'+
                    '</div>'+
                    '<div class="row">'+
                        '<div class="col-md-3">'+
                            '<label>Cliente <span class="label label-danger" id="textonombrecliente"></span></label>'+
                            '<table class="col-md-12">'+
                                '<tr>'+
                                    '<td>'+
                                        '<div class="btn bg-blue waves-effect" onclick="obtenerclientes()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control" name="numerocliente" id="numerocliente" required data-parsley-type="integer" autocomplete="off">'+
                                            '<input type="hidden" class="form-control" name="numeroclienteanterior" id="numeroclienteanterior" required data-parsley-type="integer">'+
                                            '<input type="hidden" class="form-control" name="cliente" id="cliente" required readonly onkeyup="tipoLetra(this)">'+
                                        '</div>'+
                                    '</td>'+
                                '</tr>'+
                            '</table>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                            '<label>Agente <span class="label label-danger" id="textonombreagente"></span></label>'+
                            '<table class="col-md-12">'+
                                '<tr>'+
                                    '<td>'+
                                        '<div class="btn bg-blue waves-effect" id="btnobteneragentes" onclick="obteneragentes()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+ 
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control" name="numeroagente" id="numeroagente" required data-parsley-type="integer" autocomplete="off">'+
                                            '<input type="hidden" class="form-control" name="numeroagenteanterior" id="numeroagenteanterior" required data-parsley-type="integer">'+
                                            '<input type="hidden" class="form-control" name="agente" id="agente" required readonly onkeyup="tipoLetra(this)">'+
                                        '</div>'+
                                    '</td>'+
                                '</tr>'+    
                            '</table>'+
                        '</div>'+
                        '<div class="col-md-3" id="divbuscarcodigoproducto">'+
                          '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver Productos</div>'+
                              '</td>'+
                              '<td>'+ 
                                '<div class="form-line">'+
                                  '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+    
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-3" id="divbuscarcodigoservicio">'+
                          '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" id="btnobtenerservicios" onclick="listarservicios()">Ver Servicios</div>'+
                              '</td>'+
                              '<td>'+ 
                                '<div class="form-line">'+
                                  '<input type="text" class="form-control" name="codigoservicioabuscar" id="codigoservicioabuscar" placeholder="Escribe el código del servicio" autocomplete="off">'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+    
                          '</table>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="col-md-12">'+
                    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                        '<li role="presentation" class="active">'+
                            '<a href="#productostab" data-toggle="tab">Refacciones</a>'+
                        '</li>'+
                        '<li role="presentation">'+
                            '<a href="#serviciostab" data-toggle="tab">Servicios</a>'+
                        '</li>'+
                        '<li role="presentation">'+
                            '<a href="#datosunidadtab" data-toggle="tab">Datos de la Unidad</a>'+
                        '</li>'+
                    '</ul>'+
                    '<div class="tab-content">'+
                        '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                            '<div class="row">'+
                                '<div class="col-md-12 table-responsive cabecerafija" style="height: 175px;overflow-y: scroll;padding: 0px 0px;">'+
                                    '<table id="tablaproductocotizacion" class="table table-bordered tablaproductocotizacion">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                                '<th class="'+background_tables+'">#</th>'+
                                                '<th class="customercolortheadth">Código</th>'+
                                                '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                                                '<th class="customercolortheadth">Unidad</th>'+
                                                '<th class="customercolortheadth">Cantidad</th>'+
                                                '<th class="customercolortheadth">Precio $</th>'+
                                                '<th class="'+background_tables+'">Importe $</th>'+
                                                '<th class="customercolortheadth">Dcto %</th>'+
                                                '<th class="customercolortheadth">Dcto $</th>'+
                                                '<th class="'+background_tables+'">SubTotal $</th>'+
                                                '<th class="customercolortheadth">Iva %</th>'+
                                                '<th class="'+background_tables+'">Iva $</th>'+
                                                '<th class="'+background_tables+'">Total $</th>'+
                                                '<th class="'+background_tables+'">Costo $</th>'+
                                                '<th class="'+background_tables+'">Costo Total $</th>'+
                                                '<th class="customercolortheadth">Comisión %</th>'+
                                                '<th class="'+background_tables+'">Comisión $</th>'+
                                                '<th class="bg-amber">Utilidad $</th>'+
                                                '<th class="'+background_tables+'">Existencia Actual</th>'+
                                                '<th class="'+background_tables+'"><div style="width:200px !important;">Cantidad Solicitada Por El Cliente</div></th>'+
                                                '<th class="'+background_tables+'">Indice Surtimiento %</th>'+
                                                '<th class="'+background_tables+'">Moneda</th>'+
                                                '<th class="'+background_tables+'">Costo de Lista</th>'+
                                                '<th class="'+background_tables+'">Tipo de Cambio</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+           
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+ 
                        '</div>'+ 
                        '<div role="tabpanel" class="tab-pane fade" id="serviciostab">'+
                            '<div class="row">'+
                                '<div class="col-md-12 table-responsive cabecerafija" style="height: 175px;overflow-y: scroll;padding: 0px 0px;">'+
                                    '<table id="tablaserviciocotizacion" class="table table-bordered tablaserviciocotizacion">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                                '<th class="'+background_tables+'">#</th>'+
                                                '<th class="customercolortheadth">Código</th>'+
                                                '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                                                '<th class="customercolortheadth">Unidad</th>'+
                                                '<th class="customercolortheadth">Cantidad</th>'+
                                                '<th class="customercolortheadth">Precio $</th>'+
                                                '<th class="'+background_tables+'">Importe $</th>'+
                                                '<th class="customercolortheadth">Dcto %</th>'+
                                                '<th class="customercolortheadth">Dcto $</th>'+
                                                '<th class="'+background_tables+'">SubTotal $</th>'+
                                                '<th class="customercolortheadth">Iva %</th>'+
                                                '<th class="'+background_tables+'">Iva $</th>'+
                                                '<th class="'+background_tables+'">Total $</th>'+
                                                '<th class="'+background_tables+'">Costo $</th>'+
                                                '<th class="'+background_tables+'">Costo Total $</th>'+
                                                '<th class="customercolortheadth">Comisión %</th>'+
                                                '<th class="'+background_tables+'">Comisión $</th>'+
                                                '<th class="bg-amber">Utilidad $</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+           
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+ 
                        '</div>'+ 
                        '<div role="tabpanel" class="tab-pane fade" id="datosunidadtab">'+
                            '<div class="row">'+
                                '<div class="col-md-12 table-responsive cabecerafija" style="height: 175px;overflow-y: scroll;padding: 0px 0px;">'+
                                    '<div class="row">'+
                                        '<div class="col-md-3">'+
                                            '<label>Vin / Serie <span class="label label-danger" id="textonombrevin"></span></label>'+
                                            '<table class="col-md-12">'+
                                                '<tr>'+
                                                    '<td>'+
                                                        '<div class="btn bg-blue waves-effect" onclick="listarvines()">Seleccionar</div>'+
                                                    '</td>'+
                                                    '<td>'+    
                                                        '<div class="form-line">'+
                                                            '<input type="text" class="form-control" name="vin" id="vin" data-parsley-length="[1, 30]" autocomplete="off">'+
                                                            '<input type="hidden" class="form-control" name="vinanterior" id="vinanterior" data-parsley-length="[1, 30]">'+
                                                        '</div>'+
                                                    '</td>'+    
                                                '</tr>'+    
                                            '</table>'+
                                        '</div>'+
                                        '<div class="col-md-2">'+
                                            '<label>Motor / Serie</label>'+
                                            '<input type="text" class="form-control" name="motor" id="motor"  onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]" autocomplete="off">'+
                                        '</div>'+
                                        '<div class="col-md-2">'+
                                            '<label>Marca</label>'+
                                            '<input type="text" class="form-control" name="marca" id="marca"  onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]" autocomplete="off">'+
                                        '</div>'+
                                        '<div class="col-md-2">'+
                                            '<label>Modelo</label>'+
                                            '<input type="text" class="form-control" name="modelo" id="modelo"  onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]" autocomplete="off">'+
                                        '</div>'+
                                        '<div class="col-md-1">'+
                                            '<label>Año</label>'+
                                            '<input type="text" class="form-control" name="ano" id="ano"  data-parsley-max="'+parseInt(periodohoy)+'" data-parsley-type="digits" data-parsley-length="[4,4]"  onkeyup="tipoLetra(this);" autocomplete="off">'+
                                        '</div>'+
                                        '<div class="col-md-2">'+
                                            '<label>Tipo Servicio</label>'+
                                            '<select name="tiposervicio" id="tiposervicio" class="form-control select2" style="width:100% !important;" required>'+
                                            '<option selected disabled hidden>Selecciona</option>'+
                                            '<option value="NORMAL">NORMAL</option>'+
                                            '<option value="CORRECTIVO" selected>CORRECTIVO</option>'+
                                            '<option value="PREVENTIVO">PREVENTIVO</option>'+
                                            '</select>'+
                                        '</div>'+                                    
                                    '</div>'+
                                    '<div class="row">'+
                                        '<div class="col-md-2">'+
                                            '<label>Kilómetros</label>'+
                                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="kilometros" id="kilometros" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)"  required >'+
                                        '</div>'+
                                        '<div class="col-md-2">'+
                                            '<label>Placas</label>'+
                                            '<input type="text" class="form-control" name="placas" id="placas" data-parsley-length="[1, 10]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                                        '</div>'+
                                        '<div class="col-md-2">'+
                                            '<label># Económico</label>'+
                                            '<input type="text" class="form-control" name="economico" id="economico"  data-parsley-length="[1, 30]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                                        '</div>'+
                                        '<div class="col-md-2">'+
                                            '<label>Color</label>'+
                                            '<input type="text" class="form-control" name="color" id="color" data-parsley-length="[3, 30]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+ 
                        '</div>'+ 
                    '</div>'+
                    '<div class="row">'+
                        '<div class="col-md-3">'+   
                            '<label>Observaciones</label>'+
                            '<textarea class="form-control" name="observaciones" id="observaciones" rows="5" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" required></textarea>'+
                        '</div>'+ 
                        '<div class="col-md-3">'+
                            '<table class="table table-striped table-hover">'+
                                '<tr>'+
                                    '<td style="padding:0px !important;" colspan="2">'+
                                        '<input type="checkbox" name="capturaprecioneto" id="idcapturaprecioneto" class="filled-in datotabla" value="1" />'+
                                        '<label for="idcapturaprecioneto">Capturar Precio Neto $</label>'+
                                    '</td>'+
                                '</tr>'+
                                '<tr>'+'</tr>'+
                                '<tr>'+'</tr>'+
                                '<tr>'+
                                    '<td style="padding:0px !important;">Utilidad</td>'+
                                    '<td style="padding:0px !important;"hidden><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="utilidad" id="utilidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td style="padding:0px !important;"hidden><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="utilidadservicio" id="utilidadservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="utilidadtotal" id="utilidadtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td style="padding:0px !important;">Costo</td>'+
                                    '<td style="padding:0px !important;"hidden><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td style="padding:0px !important;"hidden><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="costoservicio" id="costoservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="costototal" id="costototal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                '</tr>'+
                                '<tr hidden>'+
                                    '<td style="padding:0px !important;">Comisión</td>'+
                                    '<td style="padding:0px !important;"><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="comision" id="comision" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td style="padding:0px !important;"><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="comisionservicio" id="comisionservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="comisiontotal" id="comisiontotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                '</tr>'+
                            '</table>'+
                        '</div>'+
                        '<div class="col-md-6">'+
                            '<table class="table table-striped table-hover">'+
                                '<tr>'+
                                    '<td class="tdmod"></td>'+
                                    '<td class="tdmod"><b>Refacciones</b></td>'+
                                    '<td class="tdmod"><b>Servicio</b></td>'+
                                    '<td class="tdmod"><b>Total</b></td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td class="tdmod">Importe</td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importeservicio" id="importeservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importetotal" id="importetotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td class="tdmod">Descuento</td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentoservicio" id="descuentoservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentototal" id="descuentototal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td class="tdmod">SubTotal</td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotalservicio" id="subtotalservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotaltotal" id="subtotaltotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td class="tdmod">Iva</td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ivaservicio" id="ivaservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ivatotal" id="ivatotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td class="tdmod">Total</td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalservicio" id="totalservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totaltotal" id="totaltotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                '</tr>'+
                            '</table>'+
                        '</div>'+
                    '</div>'+ 
                '</div>';
    $("#tabsform").html(tabs);
    obtenultimonumero();
    obtenertiposunidades();
    asignarfechaactual();
    //reiniciar los contadores
    contadorproductos=0;
    contadorfilas = 0;
    //activar select2
    $("#unidad").select2();
    $("#tiposervicio").select2();
    //busquedas seleccion
    //activar busqueda de codigos
    $("#codigoabuscar").keypress(function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          obtenerproductoporcodigo();
        }
    });
    //activar busqueda de codigos servicios
    $("#codigoservicioabuscar").keypress(function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          obtenerservicioporcodigo();
        }
    });
    //activar busqueda
    $('#numerocliente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclientepornumero();
        }
    });
    //activar busqueda
    $('#numeroagente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obteneragentepornumero();
        }
    });
    //activar busqueda para vines
    $('#vin').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenervinpornumero();
        }
    });
    //regresar numero
    $('#numerocliente').on('change', function(e) {
        regresarnumerocliente();
    });
    //regresar numero
    $('#numeroagente').on('change', function(e) {
        regresarnumeroagente();
    });
    //regresar vin
    $('#vin').on('change', function(e) {
        regresarnumerovin();
    });
    $("#ModalAlta").modal('show');
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    var numerofilas = $("#numerofilas").val();
    var numerofilasservicios = $("#numerofilasservicios").val();
    if(parseInt(numerofilas) > 0 || parseInt(numerofilasservicios) > 0){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:cotizaciones_servicios_guardar,
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
      msj_erroralmenosunaentrada();
    }
  }else{
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});
//bajas
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(cotizaciondesactivar){
  $.get(cotizaciones_servicios_verificar_baja,{cotizaciondesactivar:cotizaciondesactivar}, function(data){
    if(data.Status == 'BAJA'){
      $("#cotizaciondesactivar").val(0);
      $("#textomodaldesactivar").html('Error, esta Cotización ya fue dado de baja');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{   
      if(data.resultadofechas != ''){
        $("#cotizaciondesactivar").val(0);
        $("#textomodaldesactivar").html('Error solo se pueden dar de baja las cotizaciones del mes actual, fecha de la cotización: ' + data.resultadofechas);
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        if(data.errores != ''){
          $("#cotizaciondesactivar").val(0);
          $("#textomodaldesactivar").html(data.errores);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else{
          $("#cotizaciondesactivar").val(cotizaciondesactivar);
          $("#textomodaldesactivar").html('Estas seguro de desactivar la cotización? No'+ cotizaciondesactivar);
          $("#divmotivobaja").show();
          $("#btnbaja").show();
          $('#estatusregistro').modal('show');
        }
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
      url:cotizaciones_servicios_bajas,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#estatusregistro').modal('hide');
        msj_datosguardadoscorrectamente();
        $("#motivobaja").val("");
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
function obtenerdatos(cotizacionmodificar){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(cotizaciones_servicios_obtener_cotizacion_servicio,{cotizacionmodificar:cotizacionmodificar },function(data){
    $("#titulomodal").html('Modificación Cotización Servicio --- STATUS : ' + data.cotizacion.Status);
    //formulario modificacion
    var tabs ='<div class="col-md-12">'+
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Cotización <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b> &nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                    '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                    '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" data-parsley-length="[0, 10]" required readonly>'+
                    '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" value="alta" readonly>'+
                    '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" value="0" readonly>'+
                    '<input type="hidden" class="form-control" name="numerofilasservicios" id="numerofilasservicios" value="0" readonly>'+
                  '</div>'+  
                  '<div class="col-md-2">'+
                    '<label>Plazo Días</label>'+
                    '<input type="text" class="form-control" name="plazo" id="plazo"  required autocomplete="off">'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Referencia</label>'+
                    '<input type="text" class="form-control" name="referencia" id="referencia" data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<label>Unidad</label>'+
                    '<select name="unidad" id="unidad" class="form-control select2" style="width:100% !important;" required>'+ 
                    '</select>'+
                  '</div>'+
                  '<div class="col-xs-12 col-sm-12 col-md-3">'+
                    '<label>Fecha</label>'+
                    '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();" style="min-width:95%;">'+
                    '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                  '</div>'+
                '</div>'+
                '<div class="row">'+
                  '<div class="col-md-3">'+
                    '<label>Cliente <span class="label label-danger" id="textonombrecliente"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" onclick="obtenerclientes()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control" name="numerocliente" id="numerocliente" required data-parsley-type="integer" autocomplete="off">'+
                            '<input type="hidden" class="form-control" name="numeroclienteanterior" id="numeroclienteanterior" required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="cliente" id="cliente" required readonly onkeyup="tipoLetra(this)">'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>Agente <span class="label label-danger" id="textonombreagente"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="btnobteneragentes" onclick="obteneragentes()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+ 
                          '<div class="form-line">'+
                            '<input type="text" class="form-control" name="numeroagente" id="numeroagente" required data-parsley-type="integer" autocomplete="off">'+
                            '<input type="hidden" class="form-control" name="numeroagenteanterior" id="numeroagenteanterior" required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="agente" id="agente" required readonly onkeyup="tipoLetra(this)">'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+    
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-3" id="divbuscarcodigoproducto">'+
                    '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver Productos</div>'+
                        '</td>'+
                        '<td>'+ 
                          '<div class="form-line">'+
                            '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+    
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-3" id="divbuscarcodigoservicio">'+
                    '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" id="btnobtenerservicios" onclick="listarservicios()">Ver Servicios</div>'+
                        '</td>'+
                        '<td>'+ 
                          '<div class="form-line">'+
                            '<input type="text" class="form-control" name="codigoservicioabuscar" id="codigoservicioabuscar" placeholder="Escribe el código del servicio" autocomplete="off">'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+    
                    '</table>'+
                  '</div>'+
                '</div>'+
              '</div>'+
              '<div class="col-md-12">'+
                '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                  '<li role="presentation" class="active">'+
                    '<a href="#productostab" data-toggle="tab">Refacciones</a>'+
                  '</li>'+
                  '<li role="presentation">'+
                    '<a href="#serviciostab" data-toggle="tab">Servicios</a>'+
                  '</li>'+
                  '<li role="presentation">'+
                    '<a href="#datosunidadtab" data-toggle="tab">Datos de la Unidad</a>'+
                  '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                  '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                    '<div class="row">'+
                      '<div class="col-md-12 table-responsive cabecerafija" style="height: 175px;overflow-y: scroll;padding: 0px 0px;">'+
                        '<table id="tablaproductocotizacion" class="table table-bordered tablaproductocotizacion">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="customercolortheadth">Código</th>'+
                              '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                              '<th class="customercolortheadth">Unidad</th>'+
                              '<th class="customercolortheadth">Cantidad</th>'+
                              '<th class="customercolortheadth">Precio $</th>'+
                              '<th class="'+background_tables+'">Importe $</th>'+
                              '<th class="customercolortheadth">Dcto %</th>'+
                              '<th class="customercolortheadth">Dcto $</th>'+
                              '<th class="'+background_tables+'">SubTotal $</th>'+
                              '<th class="customercolortheadth">Iva %</th>'+
                              '<th class="'+background_tables+'">Iva $</th>'+
                              '<th class="'+background_tables+'">Total $</th>'+
                              '<th class="'+background_tables+'">Costo $</th>'+
                              '<th class="'+background_tables+'">Costo Total $</th>'+
                              '<th class="customercolortheadth">Comisión %</th>'+
                              '<th class="'+background_tables+'">Comisión $</th>'+
                              '<th class="bg-amber">Utilidad $</th>'+
                              '<th class="'+background_tables+'">Existencia Actual</th>'+
                              '<th class="'+background_tables+'"><div style="width:200px !important;">Cantidad Solicitada Por El Cliente</div></th>'+
                              '<th class="'+background_tables+'">Indice Surtimiento %</th>'+
                              '<th class="'+background_tables+'">Moneda</th>'+
                              '<th class="'+background_tables+'">Costo de Lista</th>'+
                              '<th class="'+background_tables+'">Tipo de Cambio</th>'+
                            '</tr>'+
                          '</thead>'+
                          '<tbody>'+           
                          '</tbody>'+
                        '</table>'+
                      '</div>'+
                    '</div>'+ 
                  '</div>'+ 
                  '<div role="tabpanel" class="tab-pane fade" id="serviciostab">'+
                    '<div class="row">'+
                      '<div class="col-md-12 table-responsive cabecerafija" style="height: 175px;overflow-y: scroll;padding: 0px 0px;">'+
                        '<table id="tablaserviciocotizacion" class="table table-bordered tablaserviciocotizacion">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="customercolortheadth">Código</th>'+
                              '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                              '<th class="customercolortheadth">Unidad</th>'+
                              '<th class="customercolortheadth">Cantidad</th>'+
                              '<th class="customercolortheadth">Precio $</th>'+
                              '<th class="'+background_tables+'">Importe $</th>'+
                              '<th class="customercolortheadth">Dcto %</th>'+
                              '<th class="customercolortheadth">Dcto $</th>'+
                              '<th class="'+background_tables+'">SubTotal $</th>'+
                              '<th class="customercolortheadth">Iva %</th>'+
                              '<th class="'+background_tables+'">Iva $</th>'+
                              '<th class="'+background_tables+'">Total $</th>'+
                              '<th class="'+background_tables+'">Costo $</th>'+
                              '<th class="'+background_tables+'">Costo Total $</th>'+
                              '<th class="customercolortheadth">Comisión %</th>'+
                              '<th class="'+background_tables+'">Comisión $</th>'+
                              '<th class="bg-amber">Utilidad $</th>'+
                            '</tr>'+
                          '</thead>'+
                          '<tbody>'+           
                          '</tbody>'+
                        '</table>'+
                      '</div>'+
                    '</div>'+ 
                  '</div>'+ 
                  '<div role="tabpanel" class="tab-pane fade" id="datosunidadtab">'+
                    '<div class="row">'+
                      '<div class="col-md-12 table-responsive cabecerafija" style="height: 175px;overflow-y: scroll;padding: 0px 0px;">'+
                        '<div class="row">'+
                          '<div class="col-md-3">'+
                            '<label>Vin / Serie <span class="label label-danger" id="textonombrevin"></span></label>'+
                            '<table class="col-md-12">'+
                              '<tr>'+
                                '<td>'+
                                  '<div class="btn bg-blue waves-effect" onclick="listarvines()">Seleccionar</div>'+
                                '</td>'+
                                '<td>'+    
                                  '<div class="form-line">'+
                                    '<input type="text" class="form-control" name="vin" id="vin" data-parsley-length="[1, 30]" autocomplete="off">'+
                                    '<input type="hidden" class="form-control" name="vinanterior" id="vinanterior" data-parsley-length="[1, 30]">'+
                                  '</div>'+
                                '</td>'+    
                              '</tr>'+    
                            '</table>'+
                          '</div>'+
                          '<div class="col-md-2">'+
                            '<label>Motor / Serie</label>'+
                            '<input type="text" class="form-control" name="motor" id="motor"  onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]" autocomplete="off">'+
                          '</div>'+
                          '<div class="col-md-2">'+
                            '<label>Marca</label>'+
                            '<input type="text" class="form-control" name="marca" id="marca"  onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]" autocomplete="off">'+
                          '</div>'+
                          '<div class="col-md-2">'+
                            '<label>Modelo</label>'+
                            '<input type="text" class="form-control" name="modelo" id="modelo"  onkeyup="tipoLetra(this);" data-parsley-length="[1, 30]" autocomplete="off">'+
                          '</div>'+
                          '<div class="col-md-1">'+
                            '<label>Año</label>'+
                            '<input type="text" class="form-control" name="ano" id="ano"  data-parsley-max="'+parseInt(periodohoy)+'" data-parsley-type="digits" data-parsley-length="[4,4]"  onkeyup="tipoLetra(this);" autocomplete="off">'+
                          '</div>'+
                          '<div class="col-md-2">'+
                            '<label>Tipo Servicio</label>'+
                            '<select name="tiposervicio" id="tiposervicio" class="form-control select2" style="width:100% !important;" required>'+
                              '<option selected disabled hidden>Selecciona</option>'+
                              '<option value="NORMAL">NORMAL</option>'+
                              '<option value="CORRECTIVO" selected>CORRECTIVO</option>'+
                              '<option value="PREVENTIVO">PREVENTIVO</option>'+
                            '</select>'+
                          '</div>'+                                    
                        '</div>'+
                        '<div class="row">'+
                          '<div class="col-md-2">'+
                            '<label>Kilómetros</label>'+
                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="kilometros" id="kilometros" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this)"  required >'+
                          '</div>'+
                          '<div class="col-md-2">'+
                            '<label>Placas</label>'+
                            '<input type="text" class="form-control" name="placas" id="placas" data-parsley-length="[1, 10]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                          '</div>'+
                          '<div class="col-md-2">'+
                            '<label># Económico</label>'+
                            '<input type="text" class="form-control" name="economico" id="economico"  data-parsley-length="[1, 30]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                          '</div>'+
                          '<div class="col-md-2">'+
                            '<label>Color</label>'+
                            '<input type="text" class="form-control" name="color" id="color" data-parsley-length="[3, 30]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                          '</div>'+
                        '</div>'+
                      '</div>'+
                    '</div>'+ 
                  '</div>'+ 
                '</div>'+
                '<div class="row">'+
                  '<div class="col-md-3">'+   
                    '<label>Observaciones</label>'+
                    '<textarea class="form-control" name="observaciones" id="observaciones" rows="5" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);" required></textarea>'+
                  '</div>'+ 
                  '<div class="col-md-3">'+
                    '<table class="table table-striped table-hover">'+
                      '<tr>'+
                        '<td style="padding:0px !important;" colspan="2">'+
                          '<input type="checkbox" name="capturaprecioneto" id="idcapturaprecioneto" class="filled-in datotabla" value="1" />'+
                          '<label for="idcapturaprecioneto">Capturar Precio Neto $</label>'+
                        '</td>'+
                      '</tr>'+
                      '<tr>'+'</tr>'+
                      '<tr>'+'</tr>'+
                      '<tr>'+
                        '<td style="padding:0px !important;">Utilidad</td>'+
                        '<td style="padding:0px !important;"hidden><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="utilidad" id="utilidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td style="padding:0px !important;"hidden><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="utilidadservicio" id="utilidadservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="utilidadtotal" id="utilidadtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                      '</tr>'+
                      '<tr>'+
                        '<td style="padding:0px !important;">Costo</td>'+
                        '<td style="padding:0px !important;"hidden><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td style="padding:0px !important;"hidden><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="costoservicio" id="costoservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="costototal" id="costototal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                      '</tr>'+
                      '<tr hidden>'+
                        '<td style="padding:0px !important;">Comisión</td>'+
                        '<td style="padding:0px !important;"><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="comision" id="comision" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td style="padding:0px !important;"><input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="comisionservicio" id="comisionservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="comisiontotal" id="comisiontotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                      '</tr>'+
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-6">'+
                    '<table class="table table-striped table-hover">'+
                      '<tr>'+
                        '<td class="tdmod"></td>'+
                        '<td class="tdmod"><b>Refacciones</b></td>'+
                        '<td class="tdmod"><b>Servicio</b></td>'+
                        '<td class="tdmod"><b>Total</b></td>'+
                      '</tr>'+
                      '<tr>'+
                        '<td class="tdmod">Importe</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importeservicio" id="importeservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importetotal" id="importetotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                      '</tr>'+
                      '<tr>'+
                        '<td class="tdmod">Descuento</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentoservicio" id="descuentoservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentototal" id="descuentototal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                      '</tr>'+
                      '<tr>'+
                        '<td class="tdmod">SubTotal</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotalservicio" id="subtotalservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotaltotal" id="subtotaltotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                      '</tr>'+
                      '<tr>'+
                        '<td class="tdmod">Iva</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ivaservicio" id="ivaservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ivatotal" id="ivatotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                      '</tr>'+
                      '<tr>'+
                        '<td class="tdmod">Total</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalservicio" id="totalservicio" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totaltotal" id="totaltotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                      '</tr>'+
                    '</table>'+
                  '</div>'+
                '</div>'+ 
              '</div>';
    $("#tabsform").html(tabs);
    $("#periodohoy").val(data.cotizacion.Periodo);
    $("#folio").val(data.cotizacion.Folio);
    $("#serie").val(data.cotizacion.Serie);
    $("#serietexto").html("Serie: "+data.cotizacion.Serie);
    $("#plazo").val(data.cotizacion.Plazo);
    $("#referencia").val(data.cotizacion.Referencia);
    $("#fecha").val(data.fecha);
    $("#numerocliente").val(data.cliente.Numero);
    $("#numeroclienteanterior").val(data.cliente.Numero);
    $("#cliente").val(data.cliente.Nombre);
    if(data.cliente.Nombre != null){
      $("#textonombrecliente").html(data.cliente.Nombre.substring(0, 60));
    }
    $("#numeroagente").val(data.agente.Numero);
    $("#numeroagenteanterior").val(data.agente.Numero);
    $("#agente").val(data.agente.Nombre);
    if(data.agente.Nombre != null){
      $("#textonombreagente").html(data.agente.Nombre.substring(0, 60));
    }   
    $("#vin").val(data.cotizacion.Vin);
    $("#vinanterior").val(data.cotizacion.Vin);
    if(data.cotizacion.Vin != null){
      $("#textonombrevin").html(data.cotizacion.Vin);
    }
    $("#motor").val(data.cotizacion.Motor);
    $("#marca").val(data.cotizacion.Marca);
    $("#modelo").val(data.cotizacion.Modelo);
    $("#ano").val(data.cotizacion.Año);
    $("#kilometros").val(data.kilometros);
    $("#placas").val(data.cotizacion.Placas);
    $("#economico").val(data.cotizacion.Economico);
    $("#color").val(data.cotizacion.Color);
    $("#observaciones").val(data.cotizacion.Obs);
    $("#importe").val(data.importe);
    $("#descuento").val(data.descuento);
    $("#subtotal").val(data.subtotal);
    $("#iva").val(data.iva);
    $("#total").val(data.total);
    $("#utilidad").val(data.utilidad);
    $("#costo").val(data.costo);
    $("#comision").val(data.comision);
    $("#importeservicio").val(data.importeservicio);
    $("#descuentoservicio").val(data.descuentoservicio);
    $("#subtotalservicio").val(data.subtotalservicio);
    $("#ivaservicio").val(data.ivaservicio);
    $("#totalservicio").val(data.totalservicio);
    $("#utilidadservicio").val(data.utilidadservicio);
    $("#costoservicio").val(data.costoservicio);
    $("#comisionservicio").val(data.comisionservicio);
    $("#importetotal").val(data.importetotal);
    $("#descuentototal").val(data.descuentototal);
    $("#subtotaltotal").val(data.subtotaltotal);
    $("#ivatotal").val(data.ivatotal);
    $("#totaltotal").val(data.totaltotal);
    $("#utilidadtotal").val(data.utilidadtotal);
    $("#costototal").val(data.costototal);
    $("#comisiontotal").val(data.comisiontotal);
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //refacciones
    //tabs precios productos
    $("#tablaproductocotizacion").append(data.filasdetallescotizacion);
    $("#numerofilas").val(data.numerodetallescotizacion);
    //se deben asignar los valores a los contadores para que las sumas resulten correctas
    contadorproductos = data.contadorproductos;
    contadorfilas = data.contadorfilas;
    //servicios
    $("#tablaserviciocotizacion").append(data.filasdetallesservicioscotizacion);
    $("#numerofilasservicios").val(data.numerodetallesservicioscotizacion);
    //se deben asignar los valores a los contadores para que las sumas resulten correctas
    contadorservicios = data.contadorservicios;
    contadorfilasservicios = data.contadorfilasservicios;
    //busquedas seleccion//activar busqueda de codigos
    $("#codigoabuscar").keypress(function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          obtenerproductoporcodigo();
        }
    });
    //activar busqueda de codigos servicios
    $("#codigoservicioabuscar").keypress(function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          obtenerservicioporcodigo();
        }
    });
    //activar busqueda
    $('#numerocliente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclientepornumero();
        }
    });
    //activar busqueda
    $('#numeroagente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obteneragentepornumero();
        }
    });
    //activar busqueda para vines
    $('#vin').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenervinpornumero();
        }
    });
    //regresar numero
    $('#numerocliente').on('change', function(e) {
        regresarnumerocliente();
    });
    //regresar numero
    $('#numeroagente').on('change', function(e) {
        regresarnumeroagente();
    });
    //regresar vin
    $('#vin').on('change', function(e) {
        regresarnumerovin();
    });
    obtenertiposunidades();
    seleccionartipounidad(data);
  }).fail( function() {
    msj_errorajax();
    $('.page-loader-wrapper').css('display', 'none');
  }) 
}
async function seleccionartipounidad(data){
  await retraso();
  $("#unidad").val(data.cotizacion.Unidad).change();
  $("#unidad").select2();
  $("#tiposervicio").val(data.cotizacion.TipoServicio).change();
  $("#tiposervicio").select2();
  mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
  $('.page-loader-wrapper').css('display', 'none');
}
//guardar modificación
$("#btnGuardarModificacion").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formparsley")[0]);
  var form = $("#formparsley");
  if (form.parsley().isValid()){
    var numerofilas = $("#numerofilas").val();
    var numerofilasservicios = $("#numerofilasservicios").val();
    if(parseInt(numerofilas) > 0 || parseInt(numerofilasservicios) > 0){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:cotizaciones_servicios_guardar_modificacion,
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
      msj_erroralmenosunaentrada();
    }
  }else{
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
});
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento, tipo){
  tipoformatopdf = tipo;
  $.get(cotizaciones_servicios_obtener_datos_envio_email,{documento:documento}, function(data){
    $("#textomodalenviarpdfemail").html("Enviar email Cotización Servicio No." + documento);
    $("#emaildocumento").val(documento);
    $("#emailde").val(data.emailde);
    $("#emailpara").val(data.emailpara);
    $("#emailasunto").val("COTIZACIÓN SERVICIO NO. " + documento +" DE "+ nombreempresa);
    $("#modalenviarpdfemail").modal('show');
  })   
}
//enviar documento pdf por email
$("#btnenviarpdfemail").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formenviarpdfemail")[0]);
  var form = $("#formenviarpdfemail");
  if(tipoformatopdf == 1){
    var urlenviarpdf = cotizaciones_servicios_enviar_pdfs_email;
  }else{
    var urlenviarpdf = cotizaciones_servicios_enviar_pdfs_cliente_email;
  }
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:urlenviarpdf,
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
  var columnastablafoliosencontrados =  '<tr>'+
                                          '<th><div style="width:80px !important;">Generar Documento en PDF</div></th>'+
                                          '<th>Cotización</th>'+
                                          '<th>Cliente</th>'+
                                          '<th>Total</th>'+
                                          '<th>Status</th>'+
                                        '</tr>';
  $("#columnastablafoliosencontrados").html(columnastablafoliosencontrados);
  tabla=$('#tablafoliosencontrados').DataTable({
      "paging":   false,
      "ordering": false,
      "info":     false,
      "searching": false,
      order: [1, 'asc'],
      processing: true,
      serverSide: true,
      ajax: {
          url: cotizaciones_servicios_buscar_folio_string_like,
          data: function (d) {
              d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Cotizacion', name: 'Cotizacion' },
          { data: 'Cliente', name: 'Cliente', orderable: false, searchable: false },
          { data: 'Total', name: 'Total', orderable: false, searchable: false  },
          { data: 'Status', name: 'Status', orderable: false, searchable: false  },
      ],
  });
}
//configurar tabla
function configurar_tabla(){
  var checkboxscolumnas = '';
  var optionsselectbusquedas = '';
  var campos = campos_activados.split(",");
  for (var i = 0; i < campos.length; i++) {
    var returncheckboxfalse = '';
    if(campos[i] == 'Orden' || campos[i] == 'Status'){
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