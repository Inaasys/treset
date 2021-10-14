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
function asignarfechaactual(){
  /*
    var fechahoy = new Date();
    var dia = ("0" + fechahoy.getDate()).slice(-2);
    var mes = ("0" + (fechahoy.getMonth() + 1)).slice(-2);
    var hoy = fechahoy.getFullYear()+"-"+(mes)+"-"+(dia);
    $('#fecha').val(hoy);
    */
    $.get(ordenes_compra_obtener_fecha_actual_datetimelocal, function(fechadatetimelocal){
      $("#fecha").val(fechadatetimelocal);
    })
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  var serie = $("#serie").val();
  $.get(ajustesinventario_obtener_ultimo_id,{serie:serie}, function(folio){
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
        url: ajustesinventario_obtener,
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
  //modificacion al dar doble click
  $('#tbllistado tbody').on('dblclick', 'tr', function () {
    var data = tabla.row( this ).data();
    obtenerdatos(data.Ajuste);
  });
}


//realizar en reporte en excel
function descargar_plantilla(){
  $("#btnGenerarPlantilla").attr("href", urlgenerarplantilla);
  $("#btnGenerarPlantilla").click();
}
function seleccionarpartidasexcel(){
  $("#partidasexcel").click();
}
//Cada que se elija un archivo
function cargarpartidasexcel(e) {
  $("#btnenviarpartidasexcel").click();
}
//Agregar respuesta a la datatable
$("#btnenviarpartidasexcel").on('click', function(e){
  e.preventDefault();
  var partidasexcel = $('#partidasexcel')[0].files[0];
  var numeroalmacen = $("#numeroalmacen").val();
  var form_data = new FormData();
  form_data.append('partidasexcel', partidasexcel); 
  form_data.append('numeroalmacen', numeroalmacen);
  form_data.append('contadorproductos', contadorproductos);
  form_data.append('contadorfilas', contadorfilas);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:ajustesinventario_cargar_partidas_excel,
    data: form_data,
    type: 'POST',
    contentType: false,
    processData: false,
    success: function (data) {
      console.log(data);
      contadorfilas = data.contadorfilas;
      contadorproductos = data.contadorproductos;
      $("#tablaproductosajuste tbody").append(data.filasdetallesajuste);
      
      comprobarfilas();
      calculartotales();
    },
    error: function (data) {
      console.log(data);
    }
  });                      
});


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
        url: ajustesinventario_obtener_series_documento
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
  $.get(ajustesinventario_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie}, function(folio){
      $("#folio").val(folio);
      $("#serie").val(Serie);
      $("#serietexto").html("Serie: "+Serie);
      mostrarformulario();
  }) 
}
//obtener registros de remisiones
function obteneralmacenes(){
  ocultarformulario();
  var tablaalmacenes = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Almacenes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoalmacen" class="tbllistadoalmacen table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Almacen</th>'+
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
    $("#contenidomodaltablas").html(tablaalmacenes);
    $('#tbllistadoalmacen').DataTable({
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
            url: ajustesinventario_obtener_almacenes,
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
//obtener datos de remision seleccionada
function seleccionaralmacen(Numero, Nombre){
  var numeroalmacenanterior = $("#numeroalmacenanterior").val();
  var numeroalmacen = Numero;
  if(numeroalmacenanterior != numeroalmacen){
    $("#numeroalmacen").val(Numero);
    $("#numeroalmacenanterior").val(Numero);
    $("#almacen").val(Nombre);
    if(Nombre != null){
      $("#textonombrealmacen").html(Nombre.substring(0, 40));
    }
    mostrarformulario();
    var tipooperacion = $("#tipooperacion").val();
    if(tipooperacion == 'alta'){
      $("#divbuscarcodigoproducto").show();
    }
    //si se cambia el almacen cambiar datos en las filas
    var numerofilas = $("#numerofilas").val();
    if(parseInt(numerofilas) > parseInt(0)){
      cargarnuevosdatosenfilas();
    }
  }
}
function cargarnuevosdatosenfilas(){
  var numeroalmacen = $("#numeroalmacen").val();
  var serie = $("#serie").val();
  var folio = $("#folio").val();
  var fila = 0;
  $("tr.filasproductos").each(function () { 
      // obtener los datos de la fila:
      var codigopartida = $(".codigoproductopartida", this).val();
      var entradaspartida = $(".entradaspartida", this).val();
      var salidaspartida = $(".salidaspartida", this).val();
      obtenernuevosdatosfila(numeroalmacen, codigopartida, folio, serie, fila, entradaspartida, salidaspartida).then(nuevosdatosfila=>{
        $(".existenciaactualpartida", this).val(nuevosdatosfila.nuevaexistencia);
        if(parseFloat(entradaspartida) > 0 ){
          $(".entradaspartida", this).change();
        }
        if(parseFloat(salidaspartida) > 0){
          $(".salidaspartida", this).change();
        }
        //$(".existencianuevapartida", this).val(nuevosdatosfila.existencianuevapartida);
      })
      fila++;
  });  
}
//funcion asincrona para buscar existencias de la partida
function obtenernuevosdatosfila(numeroalmacen, codigopartida, folio, serie, fila, entradaspartida, salidaspartida){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $.get(ajustesinventario_obtener_nuevos_datos_fila,{'numeroalmacen':numeroalmacen,'codigopartida':codigopartida,'folio':folio,'serie':serie,'fila':fila,'entradaspartida':entradaspartida,'salidaspartida':salidaspartida},nuevosdatosfila=>{
        return ejecuta(nuevosdatosfila);
      })
    },500);
  })
}
//obtener por numero
function obteneralmacenpornumero(){
  var numeroalmacenanterior = $("#numeroalmacenanterior").val();
  var numeroalmacen = $("#numeroalmacen").val();
  if(numeroalmacenanterior != numeroalmacen){
    if($("#numeroalmacen").parsley().isValid()){
      var numeroalmacen = $("#numeroalmacen").val();
      $.get(ajustesinventario_obtener_almacen_por_numero, {numeroalmacen:numeroalmacen}, function(data){
        $("#numeroalmacen").val(data.numero);
        $("#numeroalmacenanterior").val(data.numero);
        $("#almacen").val(data.nombre);
        if(data.nombre != null){
          $("#textonombrealmacen").html(data.nombre.substring(0, 40));
        }
        mostrarformulario();
        var tipooperacion = $("#tipooperacion").val();
        if(tipooperacion == 'alta'){
          $("#divbuscarcodigoproducto").show();
        }
        //si se cambia el almacen cambiar datos en las filas
        var numerofilas = $("#numerofilas").val();
        if(parseInt(numerofilas) > parseInt(0)){
          cargarnuevosdatosenfilas();
        }
      }) 
    }
  }
}
//regresar numero
function regresarnumeroalmacen(){
  var numeroalmacenanterior = $("#numeroalmacenanterior").val();
  $("#numeroalmacen").val(numeroalmacenanterior);
}
//listar productos para tab consumos
function listarproductos(){
    ocultarformulario();
    var tablaproductos = '<div class="modal-header '+background_forms_and_modals+'">'+
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
        url: ajustesinventario_obtener_productos,
        data: function (d) {
          d.codigoabuscar = $("#codigoabuscar").val();
          d.tipooperacion = $("#tipooperacion").val();
          d.numeroalmacen = $("#numeroalmacen").val();
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
    var numeroalmacen = $("#numeroalmacen").val();
    var tipooperacion = $("#tipooperacion").val();
    $.get(ajustesinventario_obtener_producto_por_codigo,{codigoabuscar:codigoabuscar,numeroalmacen:numeroalmacen}, function(data){
      if(parseInt(data.contarproductos) > 0){
        agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Impuesto, tipooperacion);
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
//calcular total de cada fila
function calculartotalesfilas(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
    if(fila === cuentaFilas){
      // obtener los datos de la fila:
      var cantidadpartida = $(".cantidadpartida", this).val();
      var preciopartida = $('.preciopartida', this).val();
      var importepartida = $('.importepartida', this).val();
      //importe de la partida
      importepartida =  new Decimal(cantidadpartida).times(preciopartida);
      $('.importepartida', this).val(number_format(round(importepartida, numerodecimales), numerodecimales, '.', ''));
      calculartotales();
    }  
    cuentaFilas++;
  });
}
//calculo subtotal entradas
function calcularsubtotalentradas(fila){
    $("#filaproducto"+fila+" .salidaspartida").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
    $("#filaproducto"+fila+" .subtotalsalidaspartida").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
    $("#filaproducto"+fila+" .subtotalentradaspartida").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
    var subtotalentradaspartida = 0;
    var entradaspartida = $("#filaproducto"+fila+" .entradaspartida").val();
    var costopartida = $("#filaproducto"+fila+" .costopartida").val();
    subtotalentradaspartida= new Decimal(entradaspartida).times(costopartida);
    $("#filaproducto"+fila+" .subtotalentradaspartida").val(number_format(round(subtotalentradaspartida, numerodecimales), numerodecimales, '.', ''));
    calculartotales();
    $("#formparsley").parsley().validate();
}
//calculo subtotal salidas
function calcularsubtotalsalidas(fila){
    $("#filaproducto"+fila+" .entradaspartida").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
    $("#filaproducto"+fila+" .subtotalsalidaspartida").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
    $("#filaproducto"+fila+" .subtotalentradaspartida").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
    var subtotalsalidaspartida = 0;
    var salidaspartida = $("#filaproducto"+fila+" .salidaspartida").val();
    var costopartida = $("#filaproducto"+fila+" .costopartida").val();
    subtotalsalidaspartida= new Decimal(salidaspartida).times(costopartida);
    $("#filaproducto"+fila+" .subtotalsalidaspartida").val(number_format(round(subtotalsalidaspartida, numerodecimales), numerodecimales, '.', ''));
    calculartotales();
    $("#formparsley").parsley().validate();
}
//cambio en costo
function cambiocosto(fila){
  var entradaspartida = $("#filaproducto"+fila+" .entradaspartida").val();
  var salidaspartida = $("#filaproducto"+fila+" .salidaspartida").val();
  if(parseFloat(entradaspartida) > parseFloat(0)){
    calcularsubtotalentradas(fila);
  }
  if(parseFloat(salidaspartida) > parseFloat(0)){
    calcularsubtotalsalidas(fila);
  }
}
//calcular existencia nueva
function calcularexistencianueva(fila){
  var existencianuevapartida = 0;
  var existenciaactualpartida = $("#filaproducto"+fila+" .existenciaactualpartida").val();
  var entradaspartida = $("#filaproducto"+fila+" .entradaspartida").val();
  var salidaspartida = $("#filaproducto"+fila+" .salidaspartida").val();
  var entradaspartidadb = $("#filaproducto"+fila+" .entradaspartidadb").val();
  var salidaspartidadb = $("#filaproducto"+fila+" .salidaspartidadb").val();
  //existencianuevapartida = new Decimal(existenciaactualpartida).plus(entradaspartida).minus(salidaspartida).plus(salidaspartidadb).minus(entradaspartidadb);
  existencianuevapartida = new Decimal(existenciaactualpartida).plus(entradaspartida).minus(salidaspartida);
  $("#filaproducto"+fila+" .existencianuevapartida").val(number_format(round(existencianuevapartida, numerodecimales), numerodecimales, '.', ''));
}
//calcular totales
function calculartotales(){
    var subtotalsalidas= 0;
    var subtotalentradas = 0;
    var total = 0;
    $("tr.filasproductos").each(function(){
        subtotalsalidas= new Decimal(subtotalsalidas).plus($(".subtotalsalidaspartida", this).val());
        subtotalentradas= new Decimal(subtotalentradas).plus($(".subtotalentradaspartida", this).val());
    }); 
    //total
    total = new Decimal(subtotalentradas).minus(subtotalsalidas);
    $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
}
//agregar una fila en la tabla de precios productos
var contadorproductos=0;
var contadorfilas = 0;
function agregarfilaproducto(Codigo, Producto, Unidad, Costo, Impuesto, tipooperacion){
  $('.page-loader-wrapper').css('display', 'block');
  var result = evaluarproductoexistente(Codigo);
  var folio = $("#folio").val();
  var serie = $("#serie").val();
  var numeroalmacen = $("#numeroalmacen").val();
  if(result == false){
    $.get(ajustesinventario_obtener_existencias_partida, {almacen:numeroalmacen,codigopartida:Codigo,folio:folio,serie:serie}, function(Existencias){
      var tipo = "alta";
      var fila=   '<tr class="filasproductos" id="filaproducto'+contadorproductos+'">'+
                          '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('+contadorproductos+')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                          '<td class="tdmod"><input type="text" class="form-control divorinputmodmd codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]"></td>'+
                          '<td class="tdmod"><input type="text" class="form-control divorinputmodxl nombreproductopartida" name="nombreproductopartida[]" id="nombreproductopartida[]" value="'+Producto+'" readonly data-parsley-length="[1, 255]"></td>'+
                          '<td class="tdmod"><input type="text" class="form-control divorinputmodsm unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'+Unidad+'" readonly data-parsley-length="[1, 5]"></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm existenciaactualpartida" name="existenciaactualpartida[]" id="existenciaactualpartida[]" value="'+Existencias+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                          '<td class="tdmod">'+
                            '<input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm entradaspartidadb" name="entradaspartidadb[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly>'+
                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm entradaspartida" name="entradaspartida[]" id="entradaspartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" onchange="formatocorrectoinputcantidades(this);calcularsubtotalentradas('+contadorfilas+');calcularexistencianueva('+contadorfilas+');colocardataparsleyminentradas('+contadorfilas+');">'+
                          '</td>'+
                          '<td class="tdmod">'+
                            '<input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm salidaspartidadb" name="salidaspartidadb[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/"  readonly>'+
                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm salidaspartida" name="salidaspartida[]" id="salidaspartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-existencias="'+Existencias+'"	 onchange="formatocorrectoinputcantidades(this);calcularsubtotalsalidas('+contadorfilas+');calcularexistencianueva('+contadorfilas+');revisarexistenciasalmacen('+contadorfilas+');colocardataparsleyminsalidas('+contadorfilas+');">'+
                          '</td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm existencianuevapartida" name="existencianuevapartida[]" id="existencianuevapartida[]" value="'+Existencias+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costopartida" name="costopartida[]" id="costopartida[]" value="'+Costo+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);cambiocosto('+contadorfilas+');"></td>'+
                          '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalentradaspartida" name="subtotalentradaspartida[]" id="subtotalentradaspartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                          '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalsalidaspartida" name="subtotalsalidaspartida[]" id="subtotalsalidaspartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                      '</tr>';
      contadorproductos++;
      contadorfilas++;
      $("#tablaproductosajuste").append(fila);
      mostrarformulario();
      comprobarfilas();
      calculartotales();
      $('.page-loader-wrapper').css('display', 'none');
    }) 
  }else{
    msj_errorproductoyaagregado();
    $('.page-loader-wrapper').css('display', 'none');
  }  
}
//eliminar una fila en la tabla de precios clientes
function eliminarfila(numerofila){
  var confirmacion = confirm("Esta seguro de eliminar la fila?"); 
  if (confirmacion == true) { 
    $("#filaproducto"+numerofila).remove();
    contadorfilas--;
    contadorproductos--;
    comprobarfilas();
    renumerarfilas();//importante para todos los calculo en el modulo de orden de compra 
    calculartotales();
  }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
  var numerofilas = $("#tablaproductosajuste tbody tr").length;
  $("#numerofilas").val(numerofilas);
}
//renumerar las filas de la orden de compra
function renumerarfilas(){
  var lista;
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("entradaspartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calcularsubtotalentradas("+i+');calcularexistencianueva('+i+');colocardataparsleyminentradas('+i+')');
  }
  //renumero el precio de la partida
  lista = document.getElementsByClassName("salidaspartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calcularsubtotalsalidas("+i+');calcularexistencianueva('+i+');revisarexistenciasalmacen('+i+');colocardataparsleyminsalidas('+i+')');
  }
  //renumero el costo de la partida
  lista = document.getElementsByClassName("costopartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);cambiocosto("+i+')');
  }
}  
//revisar si hay existencias de la partida en el almacen
function revisarexistenciasalmacen(fila){
  var folio = $("#folio").val();
  var serie = $("#serie").val();
  var almacen = $("#numeroalmacen").val();
  var codigopartida = $("#filaproducto"+fila+" .codigoproductopartida").val();
  comprobarexistenciaspartida(almacen, codigopartida, folio, serie).then(nuevaexistencia=>{
    $("#filaproducto"+fila+" .salidaspartida").attr('data-parsley-existencias',nuevaexistencia);
    $("#filaproducto"+fila+" .salidaspartida").parsley().validate();
    $("#filaproducto"+fila+" .existencianuevapartida").parsley().validate();
  })
}
//funcion asincrona para buscar existencias de la partida
function comprobarexistenciaspartida(almacen, codigopartida, folio, serie){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $.get(ajustesinventario_obtener_existencias_partida,{'almacen':almacen,'codigopartida':codigopartida,'folio':folio,'serie':serie},nuevaexistencia=>{
        return ejecuta(nuevaexistencia);
      })
    },500);
  })
}
function colocardataparsleyminentradas(fila){
  $("#filaproducto"+fila+" .entradaspartida ").attr('data-parsley-min', '0.'+numerocerosconfiguradosinputnumberstep);
  $("#filaproducto"+fila+" .salidaspartida").removeAttr('data-parsley-min');
  $("#filaproducto"+fila+" .salidaspartida").parsley().validate();
}
//validar que se ingrese al menos una entrada o salida por fila
function colocardataparsleyminsalidas(fila){
  $("#filaproducto"+fila+" .salidaspartida ").attr('data-parsley-min', '0.'+numerocerosconfiguradosinputnumberstep);
  $("#filaproducto"+fila+" .entradaspartida").removeAttr('data-parsley-min');
  $("#filaproducto"+fila+" .entradaspartida").parsley().validate();
}
//alta
function alta(){
  $("#titulomodal").html('Alta Ajuste Inventario');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs ='<div class="col-md-12">'+  
              '<div class="row">'+
                '<div class="col-md-2">'+
                  '<label>Ajuste <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                  '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                  '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                  '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                  '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                '</div>'+ 
                '<div class="col-md-3">'+
                  '<label>Almacén <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                  '<table class="col-md-12">'+
                    '<tr>'+
                      '<td>'+
                        '<div class="btn bg-blue waves-effect" onclick="obteneralmacenes()">Seleccionar</div>'+
                      '</td>'+
                      '<td>'+
                        '<div class="form-line">'+
                          '<input type="text" class="form-control" name="numeroalmacen" id="numeroalmacen" required data-parsley-type="integer" autocomplete="off">'+
                          '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required data-parsley-type="integer">'+
                          '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly>'+
                          '<input type="hidden" class="form-control" name="almacendb" id="almacendb" required readonly>'+
                          '<input type="hidden" class="form-control" name="numeroalmacendb" id="numeroalmacendb" required readonly>'+
                        '</div>'+
                      '</td>'+
                    '</tr>'+   
                  '</table>'+
                '</div>'+
                '<div class="col-md-3">'+
                  '<label>Fecha </label>'+
                  '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();">'+
                  '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                '</div>'+
              '</div>'+
              '<div class="row">'+
                '<div class="col-md-4" id="divbuscarcodigoproducto" hidden>'+
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
              '</div>'+
            '</div>'+
            '<div class="col-md-12">'+  
              '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                '<li role="presentation" class="active">'+
                  '<a href="#productostab" data-toggle="tab">Productos</a>'+
                '</li>'+
              '</ul>'+
              '<div class="tab-content">'+
                '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                  '<div class="row">'+
                    '<div class="col-md-12 table-responsive cabecerafija" style="height: 300px;overflow-y: scroll;padding: 0px 0px;">'+
                      '<table id="tablaproductosajuste" class="table table-bordered tablaproductosajuste">'+
                        '<thead class="'+background_tables+'">'+
                          '<tr>'+
                            '<th class="'+background_tables+'">#</th>'+
                            '<th class="'+background_tables+'">Código</th>'+
                            '<th class="'+background_tables+'"><div style="width:200px !important;">Producto</div></th>'+
                            '<th class="'+background_tables+'">Unidad</th>'+
                            '<th class="'+background_tables+'">Existencia Actual</th>'+
                            '<th class="customercolortheadth">Entradas</th>'+
                            '<th class="customercolortheadth">Salidas</th>'+
                            '<th class="'+background_tables+'">Existencia Nueva</th>'+
                            '<th class="'+background_tables+'">Costo $</th>'+
                          '</tr>'+
                        '</thead>'+
                        '<tbody>'+           
                        '</tbody>'+
                      '</table>'+
                    '</div>'+
                  '</div>'+ 

                  '<div class="row">'+
                    '<div class="col-md-12">'+   
                      '<table>'+
                        '<tr>'+
                          '<td><div type="button" class="btn btn-success btn-sm" onclick="seleccionarpartidasexcel()">Subir partidas en excel</div></td>'+
                          '<td data-toggle="tooltip" data-placement="top" title data-original-title="Bajar plantilla"><a class="material-icons" onclick="descargar_plantilla()" id="btnGenerarPlantilla" target="_blank">get_app</a></td>'+
                        '</tr>'+
                      '</table>'+
                    '</div>'+ 
                  '</div>'+

                  '<div class="row">'+
                    '<div class="col-md-6">'+   
                      '<label>Observaciones</label>'+
                      '<textarea class="form-control" name="observaciones" id="observaciones" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
                    '</div>'+ 
                    '<div class="col-md-3 col-md-offset-3">'+
                      '<table class="table table-striped table-hover">'+
                        '<tr>'+
                          '<td class="tdmod">Total</td>'+
                          '<td class="tdmod"><input type="text" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" required readonly></td>'+
                        '</tr>'+
                      '</table>'+
                    '</div>'+
                  '</div>'+   
                '</div>'+ 
              '</div>'+ 
            '</div>';
  $("#tabsform").html(tabs);
  //mostrar mensaje de bajar plantilla
  $('[data-toggle="tooltip"]').tooltip({
    container: 'body'
  });
  //asignar alamcen 1 por default
  $("#numeroalmacen").val(1);
  obteneralmacenpornumero();
  $("#serie").val(serieusuario);
  $("#serietexto").html("Serie: "+serieusuario);
  obtenultimonumero();
  asignarfechaactual();
  //asignar el tipo de operacion que se realizara
  $("#tipooperacion").val("alta");
  //reiniciar contadores
  contadorproductos=0;
  contadorfilas = 0;
  $("#numerofilas").val("0");
  //busquedas selecciona
  //activar busqueda
  $("#codigoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerproductoporcodigo();
    }
  });
  //activar busqueda
  $('#numeroalmacen').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obteneralmacenpornumero();
    }
  });
  //regresar numero
  $('#numeroalmacen').on('change', function(e) {
    regresarnumeroalmacen();
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
    if(parseInt(numerofilas) > 0){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:ajustesinventario_guardar,
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
function desactivar(ajustedesactivar){
  $.get(ajustesinventario_verificar_baja,{ajustedesactivar:ajustedesactivar}, function(data){
    if(data.Status == 'BAJA'){
      $("#ajustedesactivar").val(0);
      $("#textomodaldesactivar").html('Error, este ajuste de inventario ya fue dado de baja');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{ 
      if(data.resultadofechas != ''){
        $("#ajustedesactivar").val(0);
        $("#textomodaldesactivar").html('Error solo se pueden dar de baja los ajustes del mes actual, fecha del ajuste: ' + data.resultadofechas);
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        if(data.errores != ''){
          $("#ajustedesactivar").val(0);
          $("#textomodaldesactivar").html(data.errores);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else{
          $("#ajustedesactivar").val(ajustedesactivar);
          $("#textomodaldesactivar").html('Estas seguro de dar de baja el ajuste de inventario? No'+ajustedesactivar);
          $("#motivobaja").val("");
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
      url:ajustesinventario_alta_o_baja,
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
//modificacion
function obtenerdatos(ajustemodificar){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(ajustesinventario_obtener_ajuste,{ajustemodificar:ajustemodificar },function(data){
    $("#titulomodal").html('Modificación Ajuste Inventario --- STATUS : ' + data.ajuste.Status);
    //formulario modificacion
    var tabs ='<div class="col-md-12">'+  
                '<div class="row">'+
                  '<div class="col-md-2">'+
                    '<label>Ajuste <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b></label>'+
                    '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                    '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                    '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                    '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                  '</div>'+ 
                  '<div class="col-md-3">'+
                    '<label>Almacén <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                    '<table class="col-md-12">'+
                      '<tr>'+
                        '<td>'+
                          '<div class="btn bg-blue waves-effect" onclick="obteneralmacenes()">Seleccionar</div>'+
                        '</td>'+
                        '<td>'+
                          '<div class="form-line">'+
                            '<input type="text" class="form-control" name="numeroalmacen" id="numeroalmacen" required data-parsley-type="integer" autocomplete="off">'+
                            '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required data-parsley-type="integer">'+
                            '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly>'+
                            '<input type="hidden" class="form-control" name="almacendb" id="almacendb" required readonly>'+
                            '<input type="hidden" class="form-control" name="numeroalmacendb" id="numeroalmacendb" required readonly>'+
                          '</div>'+
                        '</td>'+
                      '</tr>'+   
                    '</table>'+
                  '</div>'+
                  '<div class="col-md-3">'+
                    '<label>Fecha </label>'+
                    '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();">'+
                    '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                  '</div>'+
                '</div>'+
                '<div class="row">'+
                  '<div class="col-md-4" id="divbuscarcodigoproducto" hidden>'+
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
                '</div>'+
              '</div>'+
              '<div class="col-md-12">'+ 
                '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                  '<li role="presentation" class="active">'+
                      '<a href="#productostab" data-toggle="tab">Productos</a>'+
                  '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                  '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                    '<div class="row">'+
                      '<div class="col-md-12 table-responsive cabecerafija" style="height: 300px;overflow-y: scroll;padding: 0px 0px;">'+
                        '<table id="tablaproductosajuste" class="table table-bordered tablaproductosajuste">'+
                          '<thead class="'+background_tables+'">'+
                            '<tr>'+
                              '<th class="'+background_tables+'">#</th>'+
                              '<th class="'+background_tables+'">Código</th>'+
                              '<th class="'+background_tables+'"><div style="width:200px !important;">Producto</div></th>'+
                              '<th class="'+background_tables+'">Unidad</th>'+
                              '<th class="'+background_tables+'">Existencia Actual</th>'+
                              '<th class="customercolortheadth">Entradas</th>'+
                              '<th class="customercolortheadth">Salidas</th>'+
                              '<th class="'+background_tables+'">Existencia Nueva</th>'+
                              '<th class="'+background_tables+'">Costo $</th>'+
                            '</tr>'+
                          '</thead>'+
                          '<tbody>'+           
                          '</tbody>'+
                        '</table>'+
                      '</div>'+
                    '</div>'+ 
                    '<div class="row">'+
                      '<div class="col-md-6">'+   
                        '<label>Observaciones</label>'+
                        '<textarea class="form-control" name="observaciones" id="observaciones" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
                      '</div>'+ 
                      '<div class="col-md-3 col-md-offset-3">'+
                        '<table class="table table-striped table-hover">'+
                          '<tr>'+
                            '<td class="tdmod">Total</td>'+
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" required readonly></td>'+
                          '</tr>'+
                        '</table>'+
                      '</div>'+
                    '</div>'+   
                  '</div>'+ 
                '</div>'+ 
              '</div>';
    $("#tabsform").html(tabs);
    $("#periodohoy").val(data.ajuste.Periodo);
    $("#folio").val(data.ajuste.Folio);
    $("#serie").val(data.ajuste.Serie);
    $("#serietexto").html("Serie: "+data.ajuste.Serie);
    $("#fecha").val(data.fecha);
    $("#almacen").val(data.almacen.Nombre);
    if(data.almacen.Nombre != null){
      $("#textonombrealmacen").html(data.almacen.Nombre.substring(0, 40));
    }
    $("#numeroalmacen").val(data.almacen.Numero);
    $("#numeroalmacenanterior").val(data.almacen.Numero);
    $("#almacendb").val(data.almacen.Nombre);
    $("#numeroalmacendb").val(data.almacen.Numero);
    $("#observaciones").val(data.ajuste.Obs);
    $("#total").val(data.total);
    //detalles
    $("#tablaproductosajuste tbody").html(data.filasdetallesajuste);
    $("#numerofilas").val(data.numerodetallesajuste);
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //busquedas selecciona
    //activar busqueda
    $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenerproductoporcodigo();
      }
    });
    //activar busqueda
    $('#numeroalmacen').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obteneralmacenpornumero();
      }
    });
    //regresar numero
    $('#numeroalmacen').on('change', function(e) {
      regresarnumeroalmacen();
    });
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
    var numerofilas = $("#numerofilas").val();
    if(parseInt(numerofilas) > 0){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:ajustesinventario_guardar_modificacion,
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
function enviardocumentoemail(documento){
  $.get(ajustesinventario_obtener_datos_envio_email,{documento:documento}, function(data){
    $("#textomodalenviarpdfemail").html("Enviar email Ajuste de Inventario No." + documento);
    $("#emaildocumento").val(documento);
    $("#emailde").val(data.emailde);
    $("#emailpara").val(data.emailpara);
    $("#email2cc").val(data.email2cc);
    $("#email3cc").val(data.email3cc);
    $("#emailasunto").val("AJUSTE DE INVENTARIO NO. " + documento +" DE "+ nombreempresa);
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
      url:ajustesinventario_enviar_pdfs_email,
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
                                          '<th>Ajuste</th>'+
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
          url: ajustesinventario_buscar_folio_string_like,
          data: function (d) {
              d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Ajuste', name: 'Ajuste' },
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
    if(campos[i] == 'Ajuste' || campos[i] == 'Status' || campos[i] == 'Periodo'){
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