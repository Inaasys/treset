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
  $.get(ordenes_compra_obtener_fecha_actual_datetimelocal, function(fechas){
    $("#fecha").val(fechas.fecha).attr('min', fechas.fechamin).attr('max', fechas.fechamax);
  }) 
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
    var serie = $("#serie").val();
    $.get(remisiones_obtener_ultimo_folio,{serie:serie}, function(folio){
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
//validar si se debe mostrar o no el buscador de productos
function mostrarbuscadorcodigoproducto(){
  var cliente = $("#cliente").val();
  var agente = $("#agente").val();
  var almacen = $("#almacen").val();
  if(cliente != "" && agente != "" && almacen != ""){
    $("#divbuscarcodigoproducto").show();
    $("#divlistarcotizaciones").show();
  }else{
    $("#divbuscarcodigoproducto").hide();
    $("#divlistarcotizaciones").hide();
  }
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
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
    cambiarurlexportarexcel();
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
        keys: true,
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
            url: remisiones_obtener,
            data: function (d) {
                d.periodo = $("#periodo").val();
            }
        },
        "createdRow": function( row, data, dataIndex){
            if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
            else if( data.Status ==  `POR FACTURAR`){ $(row).addClass('bg-red');}
        },
        columns: campos_tabla,
        "drawCallback": function( data ) {
            $("#sumaimportefiltrado").html(number_format(round(data.json.sumaimporte, numerodecimales), numerodecimales, '.', ''));
            $("#sumadescuentofiltrado").html(number_format(round(data.json.sumadescuento, numerodecimales), numerodecimales, '.', ''));
            $("#sumasubtotalfiltrado").html(number_format(round(data.json.sumasubtotal, numerodecimales), numerodecimales, '.', ''));
            $("#sumaivafiltrado").html(number_format(round(data.json.sumaiva, numerodecimales), numerodecimales, '.', ''));
            $("#sumatotalfiltrado").html(number_format(round(data.json.sumatotal, numerodecimales), numerodecimales, '.', ''));
            $("#sumacostofiltrado").html(number_format(round(data.json.sumacosto, numerodecimales), numerodecimales, '.', ''));
            $("#sumacomisionfiltrado").html(number_format(round(data.json.sumacomision, numerodecimales), numerodecimales, '.', ''));
            $("#sumautilidadfiltrado").html(number_format(round(data.json.sumautilidad, numerodecimales), numerodecimales, '.', '')); 
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
          //esconder opcion de generar formato tyt segun la sucursal
          if(generarformatorequisiciontyt == 'S'){
            $(".operaciongenerarformatoreqtyt").show();
          }
        }
    });
    //modificacion al dar doble click
    $('#tbllistado tbody').on('dblclick', 'tr', function () {
      var data = tabla.row( this ).data();
      obtenerdatos(data.Remision);
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
  var arraycodigospartidas = [];
  var lista = document.getElementsByClassName("codigoproductopartida");
  for (var i = 0; i < lista.length; i++) {
    arraycodigospartidas.push(lista[i].value);
  }
  var partidasexcel = $('#partidasexcel')[0].files[0];
  var numeroalmacen = $("#numeroalmacen").val();
  var numerocliente = $("#numerocliente").val();
  var form_data = new FormData();
  form_data.append('partidasexcel', partidasexcel);  
  form_data.append('numeroalmacen', numeroalmacen);
  form_data.append('contadorproductos', contadorproductos);
  form_data.append('contadorfilas', contadorfilas);
  form_data.append('arraycodigospartidas', arraycodigospartidas);
  form_data.append('numerocliente', numerocliente);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:remisiones_cargar_partidas_excel,
    data: form_data,
    type: 'POST',
    contentType: false,
    processData: false,
    success: function (data) {
        contadorfilas = data.contadorfilas;
        contadorproductos = data.contadorproductos;
        $("#tablaproductosremisiones tbody").append(data.filasdetallesremision);
        comprobarfilas();
        calculartotal();
        $("#codigoabuscar").val("");
        //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
        $(".inputnextdet").keyup(function (e) {
          //recomentable para mayor compatibilidad entre navegadores.
          var code = (e.keyCode ? e.keyCode : e.which);
          var index = $(this).index(".inputnextdet");          
          switch(code){
            case 13:
              $(".inputnextdet").eq(index + 1).focus().select(); 
              break;
            case 39:
              $(".inputnextdet").eq(index + 1).focus().select(); 
              break;
            case 37:
              $(".inputnextdet").eq(index - 1).focus().select(); 
              break;
          }
        });
        //mostrar o no insumos en partidas segun la configuracion de la empresa
        if(mostrarinsumoporpartidaenremisiones == 'S'){
            $(".tdinsumospartidas").show();
        }else{
            $(".tdinsumospartidas").hide();       
        }
        //colocar readonly si no puede modificar insumos
        var arrayusuariosamodificarinsumosproductos = usuariosamodificarinsumos.split(",");
        if(arrayusuariosamodificarinsumosproductos.indexOf(usuariologueado) == '-1'){
            $(".insumopartida").attr('readonly', 'readonly');
        }else{
            $(".insumopartida").removeAttr('readonly');
        }
        //colocar o no dataparsleyutilidad segun la configuracion de la empresa
        if(validarutilidadnegativa == 'S'){
            $(".utilidadpartida").removeAttr('data-parsley-utilidad');
            $("#utilidad").removeAttr('data-parsley-decimalesconfigurados');
        }else{
            $(".utilidadpartida").attr('data-parsley-utilidad', "0."+numerocerosconfiguradosinputnumberstep );
            $("#utilidad").attr('data-parsley-decimalesconfigurados', '/^[0-9]+[.]+[0-9]{4}$/');
        }
        //dar cambio en cantidad para colocar data parsley existencias de forma correcta
        $(".cantidadpartida").change();
        //ver si la opcion agregar iva al precio esta seleccionada
        if( $('#agregarivaalprecio').prop('checked') ) {
            //agregar al costo el iva
            colocarcostomasivaenpartidas();
        }
    },
    error: function (data) {
      console.log(data);
    }
  });                      
});
//obtener tipos cliente
function obtenertiposcliente(defaultvalue){
    $.get(remisiones_obtener_tipos_cliente, function(select_tipos_cliente){
        $("#tipo").html(select_tipos_cliente);
        if(defaultvalue != undefined){
            $("#tipo").val(defaultvalue).change();
            $("#tipo").select2();
        }
    })  
}
//obtener tipos unidad
function obtenertiposunidad(){
    $.get(remisiones_obtener_tipos_unidad, function(select_tipos_unidad){
        $("#unidad").html(select_tipos_unidad);
    }) 
}
//obtener series documento
function obtenerseriesdocumento(){
    ocultarformulario();
    var seriedefault = 'A';
    var tablaseriesdocumento=   '<div class="modal-header '+background_forms_and_modals+'">'+
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
    var tserdoc = $('#tbllistadoseriedocumento').DataTable({
        keys: true,
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
          url: remisiones_obtener_series_documento
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Serie', name: 'Serie' },
            { data: 'Documento', name: 'Documento' },
            { data: 'Nombre', name: 'Nombre' }
        ],
        "initComplete": function() {
          var $buscar = $('div.dataTables_filter input');
          $buscar.focus();
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoseriedocumento').DataTable().search( this.value ).draw();
              }
          });
        },
    });  
    //seleccionar registro al dar doble click
    $('#tbllistadoseriedocumento tbody').on('dblclick', 'tr', function () {
      var data = tserdoc.row( this ).data();
      seleccionarseriedocumento(data.Serie);
    }); 
}
function seleccionarseriedocumento(Serie){
    $.get(remisiones_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie}, function(folio){
        $("#folio").val(folio);
        $("#serie").val(Serie);
        $("#serietexto").html("Serie: "+Serie);
        mostrarformulario();
    }) 
}
//obtener clientes
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
        var tcli = $('#tbllistadocliente').DataTable({
            keys: true,
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
                url: remisiones_obtener_clientes
            },
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
                    $('#tbllistadocliente').DataTable().search( this.value ).draw();
                    }
                });
            }, 
        }); 
        //seleccionar registro al dar doble click
        $('#tbllistadocliente tbody').on('dblclick', 'tr', function () {
            var data = tcli.row( this ).data();
            seleccionarcliente(data.Numero, data.Nombre, data.Rfc, data.Credito, data.Saldo, data.NumeroAgente, data.NombreAgente);
        });  
} 
//obtener datos de remision seleccionada
function seleccionarcliente(Numero, Nombre, Rfc, Credito, Saldo, NumeroAgente, Agente){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    var numerocliente = Numero;
    if(numeroclienteanterior != numerocliente){
        $("#numerocliente").val(Numero);
        $("#numeroclienteanterior").val(Numero);
        $("#cliente").val(Nombre);
        $("#rfccliente").val(Rfc);
        if(Nombre != null){
            $("#textonombrecliente").html(Nombre.substring(0, 40));
        }
        $("#credito").val(number_format(round(Credito, numerodecimales), numerodecimales, '.', ''));
        $("#saldo").val(Saldo);
        //datos agente
        $("#numeroagente").val(NumeroAgente);
        $("#numeroagenteanterior").val(NumeroAgente);
        $("#agente").val(Agente);
        if(Agente != null){
            $("#textonombreagente").html(Agente.substring(0, 40));
        }
        mostrarformulario();
        mostrarbuscadorcodigoproducto();
        calculartotal();//para calcular nuevo saldo
        //ver si el cliente tiene el mismo rfc que la empresa
        var rfcempresa = $("#rfcempresa").val();
        var rfccliente = $("#rfccliente").val();
        if(rfcempresa == rfccliente){                    
            setTimeout(function(){$("#agregarivaalprecio").prop('checked', true);},500);
            colocarcostomasivaenpartidas();
        }else{
            setTimeout(function(){$("#agregarivaalprecio").prop('checked', false);},500);
            colocarcostosinivaenpartidas();
        }
    }
}
//obtener agentes
function obteneragentes(){
    ocultarformulario();
    var tablaagentes = '<div class="modal-header '+background_forms_and_modals+'">'+
                              '<h4 class="modal-title">Agentes</h4>'+
                          '</div>'+
                          '<div class="modal-body">'+
                              '<div class="row">'+
                                  '<div class="col-md-12">'+
                                      '<div class="table-responsive">'+
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
    var tagen = $('#tbllistadoagente').DataTable({
            keys: true,
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
              url: remisiones_obtener_agentes
          },
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
                  $('#tbllistadoagente').DataTable().search( this.value ).draw();
                  }
              });
          },
    }); 
    //seleccionar registro al dar doble click
    $('#tbllistadoagente tbody').on('dblclick', 'tr', function () {
        var data = tagen.row( this ).data();
        seleccionaragente(data.Numero, data.Nombre);
    }); 
} 
//obtener datos de remision seleccionada
function seleccionaragente(Numero, Nombre){
    var numeroagenteanterior = $("#numeroagenteanterior").val();
    var numeroagente = Numero;
    if(numeroagenteanterior != numeroagente){
        $("#numeroagente").val(Numero);
        $("#numeroagenteanterior").val(Numero);
        $("#agente").val(Nombre);
        if(Nombre != null){
            $("#textonombreagente").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
        mostrarbuscadorcodigoproducto();
    }
}
//obtener almacenes
function obteneralmacenes(){
    ocultarformulario();
    var tablaalmacenes ='<div class="modal-header '+background_forms_and_modals+'">'+
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
    var talm = $('#tbllistadoalmacen').DataTable({
        keys: true,
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
            url: remisiones_obtener_almacenes,
            data: function (d) {
                d.numeroalmacena = $("#numeroalmacena").val();
            }
        },
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
                $('#tbllistadoalmacen').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
    //seleccionar registro al dar doble click
    $('#tbllistadoalmacen tbody').on('dblclick', 'tr', function () {
        var data = talm.row( this ).data();
        seleccionaralmacen(data.Numero, data.Nombre);
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
        mostrarbuscadorcodigoproducto();
        //recargar existencias actuales del nuevo almacen seleccionado en data-parsley-existencias de las partidas
        $("tr.filasproductos").each(function () {
        $('.cantidadpartida', this).change();
        });
    }
}
//detectar cuando en el input de buscar por codigo de producto el usuario presione la tecla enter, si es asi se realizara la busqueda con el codigo escrito
$(document).ready(function(){
    $("#codigoabuscar").keypress(function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          listarproductos();
        }
    });
});
//obtener por numero
function obtenerclientepornumero(){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    var numerocliente = $("#numerocliente").val();
    if(numeroclienteanterior != numerocliente){
        if($("#numerocliente").parsley().isValid()){
            $.get(remisiones_obtener_cliente_por_numero, {numerocliente:numerocliente}, function(data){
                $("#numerocliente").val(data.numero);
                $("#numeroclienteanterior").val(data.numero);
                $("#cliente").val(data.nombre);
                $("#rfccliente").val(data.rfc);
                if(data.nombre != null){
                    $("#textonombrecliente").html(data.nombre.substring(0, 40));
                }
                $("#credito").val(number_format(round(data.credito, numerodecimales), numerodecimales, '.', ''));
                $("#saldo").val(data.saldo);
                //datos agente
                $("#numeroagente").val(data.numeroagente);
                $("#numeroagenteanterior").val(data.numeroagente);
                $("#agente").val(data.nombreagente);
                if(data.nombreagente != null){
                    $("#textonombreagente").html(data.nombreagente.substring(0, 40));
                }
                mostrarformulario();
                mostrarbuscadorcodigoproducto();
                calculartotal();//para obtener nuevo saldo
                //ver si el cliente tiene el mismo rfc que la empresa
                var rfcempresa = $("#rfcempresa").val();
                var rfccliente = $("#rfccliente").val();
                if(rfcempresa == rfccliente){                    
                    setTimeout(function(){$("#agregarivaalprecio").prop('checked', true);},500);
                    colocarcostomasivaenpartidas();
                    $("#idcapturaprecioneto").attr('disabled', 'disabled');
                    
                    $("#agregarivaalprecio").removeAttr('disabled');
                }else{
                    setTimeout(function(){$("#agregarivaalprecio").prop('checked', false);},500);
                    colocarcostosinivaenpartidas();
                    
                    $("#idcapturaprecioneto").removeAttr('disabled');
                    $("#agregarivaalprecio").attr('disabled', 'disabled');
                }
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
            $.get(remisiones_obtener_agente_por_numero, {numeroagente:numeroagente}, function(data){
                $("#numeroagente").val(data.numero);
                $("#numeroagenteanterior").val(data.numero);
                $("#agente").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombreagente").html(data.nombre.substring(0, 40));
                }
                mostrarformulario();
                mostrarbuscadorcodigoproducto();
            }) 
        }
    }
}
//regresar numero
function regresarnumeroagente(){
    var numeroagenteanterior = $("#numeroagenteanterior").val();
    $("#numeroagente").val(numeroagenteanterior);
}
//obtener por numero
function obteneralmacenpornumero(){
    var numeroalmacenanterior = $("#numeroalmacenanterior").val();
    var numeroalmacen = $("#numeroalmacen").val();
    if(numeroalmacenanterior != numeroalmacen){
        if($("#numeroalmacen").parsley().isValid()){
            $.get(remisiones_obtener_almacen_por_numero, {numeroalmacen:numeroalmacen}, function(data){
                $("#numeroalmacen").val(data.numero);
                $("#numeroalmacenanterior").val(data.numero);
                $("#almacen").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombrealmacen").html(data.nombre.substring(0, 40));
                }
                mostrarformulario();
                mostrarbuscadorcodigoproducto();
                //recargar existencias actuales del nuevo almacen seleccionado en data-parsley-existencias de las partidas
                $("tr.filasproductos").each(function () {
                $('.cantidadpartida', this).change();
                });
            }) 
        }
    }
}
//regresar numero
function regresarnumeroalmacen(){
    var numeroalmacenanterior = $("#numeroalmacenanterior").val();
    $("#numeroalmacen").val(numeroalmacenanterior);
}
//obtener por folio
function obtenerordenporfolio(){
    var ordenanterior = $("#ordenanterior").val();
    var orden = $("#orden").val();
    if(ordenanterior != orden){
        if($("#orden").parsley().isValid()){
            var codigos = new Array();
            $("tr.filasproductos").each(function () {
                codigos.push($('.codigoproductopartida', this).val());
            }); 
            var orden = $("#orden").val();
            $.get(remisiones_revisar_insumos_orden_trabajo_por_folio, {orden:orden,codigos:codigos}, function(data){
                if(data.numeroinsumosenorden > 0){
                    toastr.info( data.insumos, "Mensaje", {
                        "timeOut": "9000",
                        "progressBar": true,
                        "extendedTImeout": "9000"
                    }); 
                } 
            }) 
        }
    }
}
//regresar folio
function regresarfolioorden(){
    var ordenanterior = $("#ordenanterior").val();
    $("#orden").val(ordenanterior);
}
//activar busqueda
function obtenerserierqporserie(){
    var serierequisicionanterior = $("#serierequisicionanterior").val();
    var serierequisicion = $("#serierequisicion").val();
    if(serierequisicionanterior != serierequisicion){
        $.get(remisiones_obtener_serierq_por_serie, {serierequisicion:serierequisicion}, function(ultimonumero){
            $("#serierequisicion").val(serierequisicion);
            $("#requisicion").val(ultimonumero);
        }) 
    }
}
//listar todas las series de las requisiciones
function listarseriesrequisiciones(){
    ocultarformulario();
    var tablaseriesrequisiciones = '<div class="modal-header '+background_forms_and_modals+'">'+
                                        '<h4 class="modal-title">Series Requisiciones</h4>'+
                                    '</div>'+
                                    '<div class="modal-body">'+
                                        '<div class="row">'+
                                            '<div class="col-md-12">'+
                                                '<div class="table-responsive">'+
                                                    '<table id="tbllistadoserierequisicion" class="tbllistadoserierequisicion table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                        '<thead class="'+background_tables+'">'+
                                                            '<tr>'+
                                                                '<th>Operaciones</th>'+
                                                                '<th>Serie</th>'+
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
    $("#contenidomodaltablas").html(tablaseriesrequisiciones);
    var tserrq = $('#tbllistadoserierequisicion').DataTable({
        keys: true,
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
            url: remisiones_obtener_series_requisiciones
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'SerieRq', name: 'SerieRq' },
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                    $('#tbllistadoserierequisicion').DataTable().search( this.value ).draw();
                }
            });
        },
    });    
    //seleccionar registro al dar doble click
    $('#tbllistadoserierequisicion tbody').on('dblclick', 'tr', function () {
        var data = tserrq.row( this ).data();
        seleccionarserierq(data.SerieRq);
    }); 
}
//selecciones serie requisicion
function seleccionarserierq(SerieRq){
    $.get(remisiones_obtener_ultimo_numero_serierq_seleccionada, {SerieRq:SerieRq}, function(ultimonumero){
        $("#serierequisicion").val(SerieRq);
        $("#requisicion").val(ultimonumero);
        mostrarformulario();
    });
}
//listar todas las cotizaciones
function listarcotizaciones (){
    ocultarformulario();
    var tablacotizaciones = '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Cotizaciones</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadocotizacion" class="tbllistadocotizacion table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Cotización</th>'+
                                                        '<th>Fecha</th>'+
                                                        '<th>Cliente</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>Tipo</th>'+
                                                        '<th>Total</th>'+
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
    $("#contenidomodaltablas").html(tablacotizaciones);
    var tcots = $('#tbllistadocotizacion').DataTable({
        keys: true,
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
            url: remisiones_obtener_cotizaciones,
            data: function (d) {
                d.numerocliente = $("#numerocliente").val();
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Cotizacion', name: 'Cotizacion' },
            { data: 'Fecha', name: 'Fecha' },
            { data: 'Cliente', name: 'Cliente', orderable: false, searchable: false },
            { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false },
            { data: 'Tipo', name: 'Tipo', orderable: false, searchable: false },
            { data: 'Total', name: 'Total', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                    $('#tbllistadocotizacion').DataTable().search( this.value ).draw();
                }
            });
        },
    });    
    //seleccionar registro al dar doble click
    $('#tbllistadocotizacion tbody').on('dblclick', 'tr', function () {
        var data = tcots.row( this ).data();
        seleccionarcotizacion(data.Folio, data.Cotizacion);
    }); 
} 
//obtener todos los datos de la cotizacion seleccionada
function seleccionarcotizacion(Folio, Cotizacion){
    $('.page-loader-wrapper').css('display', 'block');
    $("#tablaproductosremisiones tbody").html("");
    var numeroalmacen = $("#numeroalmacen").val();
    $.get(remisiones_obtener_cotizacion, {Folio:Folio, Cotizacion:Cotizacion, numeroalmacen:numeroalmacen}, function(data){
        $("#observaciones").val(data.cotizacion.Obs);
        $("#plazo").val(data.cotizacion.Plazo);
        $("#tablaproductosremisiones tbody").html(data.filasdetallescotizacion);
        $("#importe").val(data.importe);
        $("#descuento").val(data.descuento);
        $("#subtotal").val(data.subtotal);
        $("#iva").val(data.iva);
        $("#total").val(data.total);
        //detalles
        $("#numerofilas").val(data.numerodetallescotizacion);
        //colocar valores a contadores
        contadorproductos = data.contadorproductos;
        contadorfilas = data.contadorfilas;
        seleccionartipocotizacion(data);
        //mostrar o no insumos en partidas segun la configuracion de la empresa
        if(mostrarinsumoporpartidaenremisiones == 'S'){
            $(".tdinsumospartidas").show();
        }else{
            $(".tdinsumospartidas").hide();     
        }
        //colocar readonly si no puede modificar insumos
        var arrayusuariosamodificarinsumosproductos = usuariosamodificarinsumos.split(",");
        if(arrayusuariosamodificarinsumosproductos.indexOf(usuariologueado) == '-1'){
            $(".insumopartida").attr('readonly', 'readonly');
        }else{
            $(".insumopartida").removeAttr('readonly');
        }
        //colocar o no dataparsleyutilidad segun la configuracion de la empresa
        if(validarutilidadnegativa == 'S'){
            $(".utilidadpartida").removeAttr('data-parsley-utilidad');
            $("#utilidad").removeAttr('data-parsley-decimalesconfigurados');
        }else{
            $(".utilidadpartida").attr('data-parsley-utilidad', "0."+numerocerosconfiguradosinputnumberstep );
            $("#utilidad").attr('data-parsley-decimalesconfigurados', '/^[0-9]+[.]+[0-9]{4}$/');
        }
        //dar cambio en cantidad para colocar data parsley existencias de forma correcta
        $(".cantidadpartida").change();
        //ver si la opcion agregar iva al precio esta seleccionada
        if( $('#agregarivaalprecio').prop('checked') ) {
            //agregar al costo el iva
            colocarcostomasivaenpartidas();
        }
    })  
}
async function seleccionartipocotizacion(data){
    await retraso();
    $("#tipo").val(data.cotizacion.Tipo).change();
    calculartotal();
    mostrarbuscadorcodigoproducto();
    mostrarformulario();
    $('.page-loader-wrapper').css('display', 'none');
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnextdet").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnextdet");          
      switch(code){
        case 13:
          $(".inputnextdet").eq(index + 1).focus().select(); 
          break;
        case 39:
          $(".inputnextdet").eq(index + 1).focus().select(); 
          break;
        case 37:
          $(".inputnextdet").eq(index - 1).focus().select(); 
          break;
      }
    });
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
    var tprod = $('#tbllistadoproducto').DataTable({
        keys: true,
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
            url: remisiones_obtener_productos,
            data: function (d) {
                d.codigoabuscar = $("#codigoabuscar").val();
                d.numeroalmacen = $("#numeroalmacen").val();
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
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoproducto').DataTable().search( this.value ).draw();
            }
            });
        },
    });   
    //seleccionar registro al dar doble click
    $('#tbllistadoproducto tbody').on('dblclick', 'tr', function () {
        var data = tprod.row( this ).data();
        var tipooperacion = $("#tipooperacion").val();
        //agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, number_format(round(data.Impuesto, numerodecimales), numerodecimales, '.', ''), data.SubTotal, data.Existencias, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, number_format(round(data.CostoDeLista, numerodecimales), numerodecimales, '.', ''));
        obtenerdatosagregarfilaproducto(data.Codigo);
    });
}
function obtenerproductoporcodigo(){
  var codigoabuscar = $("#codigoabuscar").val();
  var numeroalmacen = $("#numeroalmacen").val();
  var tipooperacion = $("#tipooperacion").val();
  $.get(remisiones_obtener_producto_por_codigo,{codigoabuscar:codigoabuscar,numeroalmacen:numeroalmacen}, function(data){
    if(parseInt(data.contarproductos) > 0){
      //agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Impuesto, data.SubTotal, data.Existencias, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, data.CostoDeLista);
      obtenerdatosagregarfilaproducto(data.Codigo);
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
        $('.descuentopesospartida', this).val('0.'+numerocerosconfigurados); 
        $('.descuentoporcentajepartida',this).val('0.'+numerocerosconfigurados);
        calculartotalesfilas(fila);
        //verificar si el almacen principal cuenta con las existencias requeridas
        var numeroalmacen = $("#numeroalmacen").val();
        var codigo = $(".codigoproductopartida", this).val();
        var cantidadpartida = $(".cantidadpartida", this).val();
        comprobarexistenciasenbd(fila, tipo, numeroalmacen, codigo).then(existencias=>{
            if(cantidadpartida > 0){
                if(tipo == "alta"){
                    var dataparsleymax = existencias;
                }else if(tipo == "modificacion"){
                    var dataparsleymax = new Decimal(existencias).plus($("#filaproducto"+fila+" .cantidadpartidadb").val());
                }
                $("#filaproducto"+fila+" .cantidadpartida").attr('data-parsley-existencias',dataparsleymax);
                $('.cantidadpartida', this).parsley().validate();
                $("#filaproducto"+fila+" .utilidadpartida").attr('data-parsley-utilidad', '0.'+numerocerosconfiguradosinputnumberstep);
            }else{
                $("#filaproducto"+fila+" .cantidadpartida").removeAttr('data-parsley-existencias');
                $('.cantidadpartida', this).parsley().validate();
                $("#filaproducto"+fila+" .utilidadpartida").removeAttr('data-parsley-utilidad');
            }
        })
    }  
    cuentaFilas++;
  });  
}
//funcion asincrona para buscar existencias de la partida
function comprobarexistenciasenbd(fila, tipo, numeroalmacen, codigo){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $.get(remisiones_obtener_existencias_almacen,{'numeroalmacen':numeroalmacen,'codigo':codigo},existencias=>{
        return ejecuta(existencias);
      })
    },500);
  })
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
    //nuevo saldo
    var numerocliente = $("#numerocliente").val();
    $.get(remisiones_obtener_nuevo_saldo_cliente,{numerocliente:numerocliente}, function(saldo){
        var nuevosaldo = new Decimal(saldo).plus(total);
        $("#saldo").val(number_format(round(nuevosaldo, numerodecimales), numerodecimales, '.', ''));
        var saldo = $("#saldo").val();
        var credito = $("#credito").val();
        if(parseFloat(saldo) > parseFloat(credito)){
            $("#mensajecreditoexcedido").html("CRÉDITO DEL CLIENTE EXCEDIDO");
        }else{
            $("#mensajecreditoexcedido").html("");           
        }
    })  
}
//obtener dato para agtegar fila producto
function obtenerdatosagregarfilaproducto(Codigo){
    var numeroalmacen = $("#numeroalmacen").val();
    var numerocliente = $("#numerocliente").val();
    var tipooperacion = $("#tipooperacion").val();
    $.get(remisiones_obtener_datos_agregar_fila_producto,{Codigo:Codigo,numeroalmacen:numeroalmacen,numerocliente:numerocliente,contadorproductos:contadorproductos,contadorfilas:contadorfilas,tipooperacion:tipooperacion}, function(data){
        agregarfilaproducto(data,Codigo);
    })
}
//agregar una fila en la tabla de precios productos
var contadorproductos=0;
var contadorfilas = 0;
function agregarfilaproducto(data,Codigo){
    $('.page-loader-wrapper').css('display', 'block');
    var result = evaluarproductoexistente(Codigo);
    if(result == false){
        contadorfilas = data.contadorfilas;
        contadorproductos = data.contadorproductos;
        $("#tablaproductosremisiones tbody").append(data.filasdetallesremision);
        comprobarfilas();
        calculartotal();
        mostrarformulario();      
        $("#codigoabuscar").val("");
        //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
        $(".inputnextdet").keyup(function (e) {
          //recomentable para mayor compatibilidad entre navegadores.
          var code = (e.keyCode ? e.keyCode : e.which);
          var index = $(this).index(".inputnextdet");          
          switch(code){
            case 13:
              $(".inputnextdet").eq(index + 1).focus().select(); 
              break;
            case 39:
              $(".inputnextdet").eq(index + 1).focus().select(); 
              break;
            case 37:
              $(".inputnextdet").eq(index - 1).focus().select(); 
              break;
          }
        });
        //mostrar o no insumos en partidas segun la configuracion de la empresa
        if(mostrarinsumoporpartidaenremisiones == 'S'){
            $(".tdinsumospartidas").show();
        }else{
            $(".tdinsumospartidas").hide();       
        }
        //colocar readonly si no puede modificar insumos
        var arrayusuariosamodificarinsumosproductos = usuariosamodificarinsumos.split(",");
        if(arrayusuariosamodificarinsumosproductos.indexOf(usuariologueado) == '-1'){
            $(".insumopartida").attr('readonly', 'readonly');
        }else{
            $(".insumopartida").removeAttr('readonly');
        }
        //colocar o no dataparsleyutilidad segun la configuracion de la empresa
        if(validarutilidadnegativa == 'S'){
            $(".utilidadpartida").removeAttr('data-parsley-utilidad');
            $("#utilidad").removeAttr('data-parsley-decimalesconfigurados');
        }else{
            $(".utilidadpartida").attr('data-parsley-utilidad', "0."+numerocerosconfiguradosinputnumberstep );
            $("#utilidad").attr('data-parsley-decimalesconfigurados', '/^[0-9]+[.]+[0-9]{4}$/');
        }
        //dar cambio en cantidad para colocar data parsley existencias de forma correcta
        $(".cantidadpartida").change();
        $('.page-loader-wrapper').css('display', 'none');
    }else{
        msj_errorproductoyaagregado();
        $('.page-loader-wrapper').css('display', 'none');
    }
    //ver si la opcion agregar iva al precio esta seleccionada
    if( $('#agregarivaalprecio').prop('checked') ) {
        //agregar al costo el iva
        colocarcostomasivaenpartidas();
    }
}
function colocarcostomasivaenpartidas(){
    $("tr.filasproductos").each(function () {
        var preciopartidaop = $('.preciopartidaop', this).val();
        var costomasiva = new Decimal(preciopartidaop).times(1.16);
        $('.ivaporcentajepartida', this).val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
        $('.preciopartida', this).val(number_format(round(costomasiva, numerodecimales), numerodecimales, '.', ''));
        $('.preciopartida', this).change();
    });
}
function colocarcostosinivaenpartidas(){
    $("tr.filasproductos").each(function () {
        var preciopartidaop = $('.preciopartidaop', this).val();
        $('.ivaporcentajepartida', this).val(number_format(round(16, numerodecimales), numerodecimales, '.', ''));
        $('.preciopartida', this).val(number_format(round(preciopartidaop, numerodecimales), numerodecimales, '.', ''));
        $('.preciopartida', this).change();
    });
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
    calculartotal();  
  }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
  var numerofilas = $("#tablaproductosremisiones tbody tr").length;
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
  // renumero porcentaje de comision
  lista = document.getElementsByClassName("comisionporcentajepartida");
  for (var i = 0; i < lista.length; i++){
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculardescuentopesospartida("+i+')');
  }
}  
//generar formato tyt
function actualizarurlexportarformatoreqtyt(){
    var arraycodigosformatoreqtyt = new Array();   
    $("tr.filasproductos").each(function () {
        var arraydatoscodigosformatoreqtyt = new Array();   
        var insumopartida = $('.insumopartida', this).val();
        var codigoproductopartida = $('.codigoproductopartida', this).val();
        var descripcionproductopartida = $('.descripcionproductopartida', this).val();
        var cantidadpartida = $('.cantidadpartida', this).val();
        arraydatoscodigosformatoreqtyt.push(insumopartida,codigoproductopartida,descripcionproductopartida,cantidadpartida);
        arraycodigosformatoreqtyt.push(arraydatoscodigosformatoreqtyt);
    });
    var referencia = $("#referencia").val();
    var ordenservicio = $("#ordenservicio").val();
    var equipo = $("#equipo").val();
    var fecha = $("#fecha").val();
    $.get(remisiones_generar_formato_req_tyt_en_modificacion_remision, {arraycodigosformatoreqtyt:arraycodigosformatoreqtyt, referencia:referencia, ordenservicio:ordenservicio, equipo:equipo, fecha:fecha}, function(data){
        $('#pdfiframe').attr("src", urlpdfsimpresionesrapidas+data);
        setTimeout(function(){imprimirdirecto();},500);  
    }) 
}
//alta
function alta(){
  $("#titulomodal").html('Alta Remisión');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs ='<div class="col-md-12">'+
                '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#remisiontab" data-toggle="tab">Remisión</a>'+
                    '</li>'+
                    '<li role="presentation">'+
                        '<a href="#pedidotab" data-toggle="tab">Pedido</a>'+
                    '</li>'+
                    '<li role="presentation" id="tabrevisioninsumosottab">'+
                        '<a href="#revisioninsumosottab" data-toggle="tab">Insumos carg. en OT</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="remisiontab">'+
                        '<div class="row">'+
                            '<div class="col-md-3">'+
                                '<label>Remisión <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                                '<input type="text" class="form-control inputnextdet" name="folio" id="folio" required onkeyup="tipoLetra(this);"  ondblclick="obtenultimonumero()">'+
                                '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" value="0" readonly>'+
                                '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" value="alta" readonly>'+
                                '<input type="hidden" class="form-control" name="rfcempresa" id="rfcempresa" value="'+rfcempresa+'" readonly>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Cliente <span class="label label-danger" id="textonombrecliente"></span></label>'+
                                '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="botonobtenerclientes" onclick="obtenerclientes()">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                            '<div class="form-line">'+
                                                '<input type="text" class="form-control inputnextdet" name="numerocliente" id="numerocliente" required data-parsley-type="integer" autocomplete="off">'+
                                                '<input type="hidden" class="form-control" name="numeroclienteanterior" id="numeroclienteanterior" required data-parsley-type="integer">'+
                                                '<input type="hidden" class="form-control" name="cliente" id="cliente" required readonly>'+
                                                '<input type="hidden" class="form-control" name="rfccliente" id="rfccliente" required readonly>'+
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
                                            '<div class="btn bg-blue waves-effect" id="botonobteneragentes" onclick="obteneragentes()">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                            '<div class="form-line">'+
                                                '<input type="text" class="form-control inputnextdet" name="numeroagente" id="numeroagente"  required data-parsley-type="integer" autocomplete="off">'+
                                                '<input type="hidden" class="form-control" name="numeroagenteanterior" id="numeroagenteanterior"  required data-parsley-type="integer">'+
                                                '<input type="hidden" class="form-control" name="agente" id="agente" required readonly>'+
                                            '</div>'+
                                        '</td>'+
                                    '</tr>'+    
                                '</table>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Fecha </label>'+
                                '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required  data-parsley-excluded="true" onkeydown="return false">'+
                                '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-2">'+
                                '<label>Almacén <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                                '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="botonobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                            '<div class="form-line">'+
                                                '<input type="text" class="form-control inputnextdet" name="numeroalmacen" id="numeroalmacen" required data-parsley-type="integer" autocomplete="off">'+
                                                '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required data-parsley-type="integer">'+
                                                '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly>'+
                                            '</div>'+
                                        '</td>'+
                                    '</tr>'+    
                                '</table>'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Tipo</label>'+
                                '<select name="tipo" id="tipo" class="form-control select2" style="width:100% !important;" required readonly></select>'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Unidad</label>'+
                                '<select name="unidad" id="unidad" class="form-control select2" style="width:100% !important;" required readonly></select>'+
                            '</div>'+
                            '<div class="col-md-1">'+
                                '<label>Plazo Días </label>'+
                                '<input type="text" class="form-control inputnextdet" name="plazo" id="plazo" value="5" onkeyup="tipoLetra(this);" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-3" id="divbuscarcodigoproducto" hidden>'+
                              '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                              '<table class="col-md-12">'+
                                '<tr>'+
                                  '<td>'+
                                    '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver Productos</div>'+
                                  '</td>'+
                                  '<td>'+ 
                                    '<div class="form-line">'+
                                      '<input type="text" class="form-control inputnextdet" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">'+
                                    '</div>'+
                                  '</td>'+
                                '</tr>'+    
                              '</table>'+
                            '</div>'+
                            '<div class="col-md-2" id="divlistarcotizaciones">'+
                              '<label>Cotizaciones</label>'+
                              '<div class="btn btn-block bg-blue waves-effect" id="btnlistarcotizaciones" onclick="listarcotizaciones()">Ver Cotizaciones</div>'+
                            '</div>'+ 
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="pedidotab">'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Pedido</label>'+
                                '<input type="text" class="form-control inputnextdet" name="pedido" id="pedido" onkeyup="tipoLetra(this);" data-parsley-length="[1, 50]" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Solicitado por</label>'+
                                '<input type="text" class="form-control inputnextdet" name="solicitadopor" id="solicitadopor" onkeyup="tipoLetra(this);" data-parsley-length="[1, 50]" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Destino del Pedido </label>'+
                                '<input type="text" class="form-control inputnextdet" name="destinodelpedido" id="destinodelpedido" onkeyup="tipoLetra(this);" data-parsley-length="[1, 255]" autocomplete="off">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-3">'+
                                '<label>Referencia</label>'+
                                '<input type="text" class="form-control inputnextdet" name="referencia" id="referencia" onkeyup="tipoLetra(this);"  data-parsley-length="[1, 50]" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Orden Servicio</label>'+
                                '<input type="text" class="form-control inputnextdet" name="ordenservicio" id="ordenservicio" onkeyup="tipoLetra(this);"  data-parsley-length="[1, 20]" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Equipo </label>'+
                                '<input type="text" class="form-control inputnextdet" name="equipo" id="equipo" onkeyup="tipoLetra(this);"  data-parsley-length="[1, 20]" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Requisición</label>'+
                                '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarseriesrequisiciones()">Series</div>'+
                                        '</td>'+
                                        '<td>'+ 
                                            '<div class="form-line">'+
                                                '<input type="text" class="form-control inputnextdet" name="serierequisicion" id="serierequisicion" placeholder="Serie" autocomplete="off" onkeyup="tipoLetra(this);">'+
                                                '<input type="hidden" class="form-control" name="serierequisicionanterior" id="serierequisicionanterior" placeholder="Serie" autocomplete="off" onkeyup="tipoLetra(this);">'+
                                            '</div>'+
                                        '</td>'+
                                        '<td>'+ 
                                            '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnextdet" name="requisicion" id="requisicion" placeholder="Número" autocomplete="off">'+
                                            '</div>'+
                                        '</td>'+
                                    '</tr>'+    
                                '</table>'+
                            '</div>'+
                            '<div class="col-md-2" id="divbtngenerarformatoreqtyt" hidden>'+
                                '<label>Generar Formato REQ TYT</label>'+
                                '<div class="btn bg-blue waves-effect" id="btngenerarformatoreqtyt" onclick="actualizarurlexportarformatoreqtyt()">Generar Formato REQ TYT</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="revisioninsumosottab" >'+
                        '<div class="row">'+
                            '<div class="col-md-3">'+
                                '<label>Número O.T. <span class="label label-danger" id="textonombreorden"></span></label>'+
                                '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<div class="form-line">'+
                                                '<input type="text" class="form-control" name="orden" id="orden" autocomplete="off" onkeyup="tipoLetra(this);">'+
                                                '<input type="hidden" class="form-control" name="ordenanterior" id="ordenanterior">'+
                                            '</div>'+
                                        '</td>'+
                                    '</tr>'+    
                                '</table>'+
                            '</div>'+
                        '</div>'+
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
                            '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                                '<table id="tablaproductosremisiones" class="table table-bordered tablaproductosremisiones">'+
                                    '<thead class="'+background_tables+'">'+
                                        '<tr>'+
                                          '<th class="'+background_tables+'">#</th>'+
                                          '<th class="customercolortheadth" id="thinsumospartidas">Insumo</th>'+
                                          '<th class="'+background_tables+'"><div style="width:100px !important;">Código</div></th>'+
                                          '<th class="customercolortheadth"><div style="width:400px !important;">Descripción</div></th>'+
                                          '<th class="'+background_tables+'">Unidad</th>'+
                                          '<th class="customercolortheadth">Por Remisionar</th>'+
                                          '<th class="customercolortheadth">Cantidad</th>'+
                                          '<th class="customercolortheadth">Precio $</th>'+
                                          '<th class="'+background_tables+'">Importe $</th>'+
                                          '<th class="customercolortheadth">Descuento %</th>'+
                                          '<th class="customercolortheadth">Descuento $</th>'+
                                          '<th class="'+background_tables+'">SubTotal $</th>'+
                                          '<th class="customercolortheadth">Iva %</th>'+
                                          '<th class="'+background_tables+'">Iva $</th>'+
                                          '<th class="'+background_tables+'">Total $</th>'+
                                          '<th class="'+background_tables+'">Costo $</th>'+
                                          '<th class="'+background_tables+'">Costo Total</th>'+
                                          '<th class="customercolortheadth">Comisión %</th>'+
                                          '<th class="'+background_tables+'">Comisión $</th>'+
                                          '<th class="bg-amber">Utilidad $</th>'+
                                          '<th class="'+background_tables+'">Moneda</th>'+
                                          '<th class="'+background_tables+'">Costo de Lista</th>'+
                                          '<th class="'+background_tables+'">Tipo de Cambio</th>'+
                                          '<th class="'+background_tables+'">Cotización</th>'+
                                          '<th class="customercolortheadth">ClaveProducto</th>'+
                                          '<th class="customercolortheadth">ClaveUnidad</th>'+
                                          '<th class="'+background_tables+'">Meses</th>'+
                                          '<th class="'+background_tables+'">TasaInteres</th>'+
                                          '<th class="'+background_tables+'">MontoInteres</th>'+
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
                                '<td><div type="button" class="btn btn-success btn-sm" onclick="seleccionarpartidasexcel()">Importar partidas en excel</div></td>'+
                                '<td data-toggle="tooltip" data-placement="top" title data-original-title="Bajar plantilla"><a class="material-icons" onclick="descargar_plantilla()" id="btnGenerarPlantilla" target="_blank">get_app</a></td>'+
                              '</tr>'+
                            '</table>'+
                          '</div>'+ 
                        '</div>'+
                        '<div class="row">'+
                          '<div class="col-md-6">'+   
                            '<label>Observaciones</label>'+
                            '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]" rows="3"></textarea>'+
                          '</div>'+ 
                            '<div class="col-md-3">'+
                                '<table class="table table-striped table-hover">'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;" colspan="2">'+
                                            '<input type="checkbox" name="tipoprecio" id="agregarivaalprecio" class="filled-in datotabla" value="1" />'+
                                            '<label for="agregarivaalprecio">Agregar Iva al Precio</label>'+
                                        '</td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;" colspan="2">'+
                                            '<input type="checkbox" name="capturaprecioneto" id="idcapturaprecioneto" class="filled-in datotabla" value="1" />'+
                                            '<label for="idcapturaprecioneto">Capturar Precio Neto $</label>'+
                                        '</td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Crédito</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="credito" id="credito" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Saldo</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="saldo" id="saldo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Utilidad</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="utilidad" id="utilidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Costo</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                '</table>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<table class="table table-striped table-hover">'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Importe</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Descuento</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">SubTotal</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Iva</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Total</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr hidden>'+
                                        '<td style="padding:0px !important;">Comisión</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="comision" id="comision" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                '</table>'+
                            '</div>'+
                        '</div>'+   
                        '<div class="row">'+
                            '<div class="col-md-12">'+   
                                '<h4 class="font-bold col-red" id="mensajecreditoexcedido"></h4>'+  
                            '</div>'+
                        '</div>'+
                    '</div>'+ 
                '</div>'+
            '</div>';
    $("#tabsform").html(tabs);
    //colocar autocomplette off  todo el formulario
    $(".form-control").attr('autocomplete','off');
    //colocar required en referencia segun la configuracion de la empresa
    if(pedirobligatoriamentereferenciarnremisiones == 'S'){
        $("#referencia").attr('required', 'required');
    }else{
        $("#referencia").removeAttr('required');
    }
    //colocar required en orden servicio segun la configuracion de la empresa
    if(pedirobligatoriamenteordenservicioenremisiones == 'S'){
        $("#ordenservicio").attr('required', 'required');
    }else{
        $("#ordenservicio").removeAttr('required');
    }
    //colocar required en equipo segun la configuracion de la empresa
    if(pedirobligatoriamenteequipoenremisiones == 'S'){
        $("#equipo").attr('required', 'required');
    }else{
        $("#equipo").removeAttr('required');
    }
    //permitir modificar consecutvio folio en remisiones
    if(modificarconsecutivofolioenremisiones == 'S'){
        $("#folio").removeAttr('readonly');
    }else{
        $("#folio").attr('readonly', 'readonly');
    }
    //mostrar o no insumos en partidas segun la configuracion de la empresa
    if(mostrarinsumoporpartidaenremisiones == 'S'){
        $("#thinsumospartidas").show();
    }else{
        $("#thinsumospartidas").hide();       
    }
    //colocar readonly o no a input de requisicion segun la configuracion de la empresa
    if(controlarconsecutivonumrequisicion == 'S'){
        $("#requisicion").attr('readonly', 'readonly');
    }else{
        $("#requisicion").removeAttr('readonly');
    }
    //ocultar tab de revision de insumos
    if(verificarinsumosremisionenot == 'N'){
        $("#revisioninsumosottab").hide();
        $("#tabrevisioninsumosottab").hide();
    }
    /*
    //ocultar o mostrar btoon para generar formato de req tyt
    if(generarformatorequisiciontyt == 'S'){
        $("#divbtngenerarformatoreqtyt").show();
    }else{
        $("#divbtngenerarformatoreqtyt").hide();
    }
    */
    //mostrar mensaje de bajar plantilla
    $('[data-toggle="tooltip"]').tooltip({
      container: 'body'
    });
    //asignar alamcen 1 por default
    $("#numeroalmacen").val(1);
    obteneralmacenpornumero();
    obtenultimonumero();
    obtenertiposcliente('CLIENTE');
    obtenertiposunidad();
    asignarfechaactual();
    //ocultar buscador de productos
    mostrarbuscadorcodigoproducto();
    //activar seelct2
    $("#tipo").select2();
    $("#unidad").select2();
    //reiniciar los contadores
    contadorproductos=0;
    contadorfilas = 0;
    //activar busqueda de codigos
    $("#codigoabuscar").keypress(function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerproductoporcodigo();
        }
    });
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
    //activar busqueda para agentes
    $('#numeroagente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obteneragentepornumero();
        }
    });
    //regresar numero agente
    $('#numeroagente').on('change', function(e) {
        regresarnumeroagente();
    });
    //activar busqueda para almacenes
    $('#numeroalmacen').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obteneralmacenpornumero();
        }
    });
    //regresar numero almacen
    $('#numeroalmacen').on('change', function(e) {
        regresarnumeroalmacen();
    });
    //activar busqueda para ordenes
    $('#orden').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenerordenporfolio();
      }
    });
    //regresar folio orden
    $('#orden').on('change', function(e) {
      regresarfolioorden();
    });
    //activar busqueda
    $('#serierequisicion').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          obtenerserierqporserie();
        }
    });
    $('#serierequisicion').on('change', function(e) {
        obtenerserierqporserie();
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnextdet").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnextdet");          
      switch(code){
        case 13:
          $(".inputnextdet").eq(index + 1).focus().select(); 
          break;
        case 39:
          $(".inputnextdet").eq(index + 1).focus().select(); 
          break;
        case 37:
          $(".inputnextdet").eq(index - 1).focus().select(); 
          break;
      }
    });
    setTimeout(function(){$("#folio").focus();},500);
    $("#ModalAlta").modal('show');
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
        var saldo = $("#saldo").val();
        var credito = $("#credito").val();
        if(parseFloat(saldo) <= parseFloat(credito)){
            var numerofilas = $("#numerofilas").val();
            if(parseInt(numerofilas) > 0  && parseInt(numerofilas) < 500){
                $('.page-loader-wrapper').css('display', 'block');
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url:remisiones_guardar,
                    type: "post",
                    dataType: "html",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success:function(data){
                        if(data == 1){
                            msj_errorremisionexistente();
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
                msj_erroralmenosunaentrada();
            }
        }else{
            msj_creditoexcedido();
        }
    }else{
        msjfaltandatosporcapturar();
    }
    //validar formulario
    form.parsley().validate();
});
//bajas
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(remisiondesactivar){
    $.get(remisiones_verificar_baja,{remisiondesactivar:remisiondesactivar}, function(data){
        if(data.Status == 'BAJA'){
            $("#remisiondesactivar").val(0);
            $("#textomodaldesactivar").html('Error, esta Remisión ya fue dado de baja');
            $("#divmotivobaja").hide();
            $("#btnbaja").hide();
            $('#estatusregistro').modal('show');
        }else{   
            if(data.resultadofechas != ''){
                $("#remisiondesactivar").val(0);
                $("#textomodaldesactivar").html('Error solo se pueden dar de baja las remisiones del mes actual, fecha de la remision: ' + data.resultadofechas);
                $("#divmotivobaja").hide();
                $("#btnbaja").hide();
                $('#estatusregistro').modal('show');
            }else{
                if(data.errores != ''){
                    $("#remisiondesactivar").val(0);
                    $("#textomodaldesactivar").html(data.errores);
                    $("#divmotivobaja").hide();
                    $("#btnbaja").hide();
                    $('#estatusregistro').modal('show');
                }else if(data.errorescotizacion != ''){
                    $("#remisiondesactivar").val(0);
                    $("#textomodaldesactivar").html(data.errorescotizacion);
                    $("#divmotivobaja").hide();
                    $("#btnbaja").hide();
                    $('#estatusregistro').modal('show');
                }else{
                    $("#remisiondesactivar").val(remisiondesactivar);
                    $("#textomodaldesactivar").html('Estas seguro de desactivar la remisión? No'+ remisiondesactivar);
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
      url:remisiones_alta_o_baja,
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
function obtenerdatos(remisionmodificar){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(remisiones_obtener_remision,{remisionmodificar:remisionmodificar },function(data){
    $("#titulomodal").html('Modificación Remisión --- STATUS : ' + data.remision.Status);
    //formulario modificacion
    var tabs ='<div class="col-md-12">'+
                '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#remisiontab" data-toggle="tab">Remisión</a>'+
                    '</li>'+
                    '<li role="presentation">'+
                        '<a href="#pedidotab" data-toggle="tab">Pedido</a>'+
                    '</li>'+
                    '<li role="presentation" id="tabrevisioninsumosottab">'+
                        '<a href="#revisioninsumosottab" data-toggle="tab">Insumos carg. en OT</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="remisiontab">'+
                        '<div class="row">'+
                            '<div class="col-md-3">'+
                                '<label>Remisión <b style="color:#F44336 !important;" id="serietexto"> Serie:</b></label>'+
                                '<input type="text" class="form-control inputnextdet" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);" ondblclick="obtenultimonumero()">'+
                                '<input type="hidden" class="form-control" name="serie" id="serie" required readonly data-parsley-length="[1, 10]">'+
                                '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                                '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                                '<input type="hidden" class="form-control" name="rfcempresa" id="rfcempresa" value="'+rfcempresa+'" readonly>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Cliente <span class="label label-danger" id="textonombrecliente"></span></label>'+
                                '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="botonobtenerclientes" onclick="obtenerclientes()">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                            '<div class="form-line">'+
                                                '<input type="text" class="form-control inputnextdet" name="numerocliente" id="numerocliente" required data-parsley-type="integer" autocomplete="off">'+
                                                '<input type="hidden" class="form-control" name="numeroclienteanterior" id="numeroclienteanterior" required data-parsley-type="integer">'+
                                                '<input type="hidden" class="form-control" name="cliente" id="cliente" required readonly>'+
                                                '<input type="hidden" class="form-control" name="rfccliente" id="rfccliente" required readonly>'+
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
                                            '<div class="btn bg-blue waves-effect" id="botonobteneragentes" onclick="obteneragentes()">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                            '<div class="form-line">'+
                                                '<input type="text" class="form-control inputnextdet" name="numeroagente" id="numeroagente"  required data-parsley-type="integer" autocomplete="off">'+
                                                '<input type="hidden" class="form-control" name="numeroagenteanterior" id="numeroagenteanterior"  required data-parsley-type="integer">'+
                                                '<input type="hidden" class="form-control" name="agente" id="agente"  readonly>'+
                                            '</div>'+
                                        '</td>'+
                                    '</tr>'+    
                                '</table>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Fecha </label>'+
                                '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required  data-parsley-excluded="true" onkeydown="return false">'+
                                '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-3">'+
                                '<label>Almacén <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                                '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="botonobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                            '<div class="form-line">'+
                                                '<input type="text" class="form-control inputnextdet" name="numeroalmacen" id="numeroalmacen" required readonly data-parsley-type="integer" autocomplete="off">'+
                                                '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required data-parsley-type="integer">'+
                                                '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly>'+
                                            '</div>'+
                                        '</td>'+
                                    '</tr>'+    
                                '</table>'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Tipo</label>'+
                                '<select name="tipo" id="tipo" class="form-control select2" style="width:100% !important;" required readonly></select>'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Unidad</label>'+
                                '<select name="unidad" id="unidad" class="form-control select2" style="width:100% !important;" required readonly></select>'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Plazo Días </label>'+
                                '<input type="text" class="form-control inputnextdet" name="plazo" id="plazo" onkeyup="tipoLetra(this);" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-3" id="divbuscarcodigoproducto" hidden>'+
                              '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                              '<table class="col-md-12">'+
                                '<tr>'+
                                  '<td>'+
                                    '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver Productos</div>'+
                                  '</td>'+
                                  '<td>'+ 
                                    '<div class="form-line">'+
                                      '<input type="text" class="form-control inputnextdet" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off">'+
                                    '</div>'+
                                  '</td>'+
                                '</tr>'+    
                              '</table>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="pedidotab">'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Pedido</label>'+
                                '<input type="text" class="form-control inputnextdet" name="pedido" id="pedido" onkeyup="tipoLetra(this);" data-parsley-length="[1, 50]" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Solicitado por</label>'+
                                '<input type="text" class="form-control inputnextdet" name="solicitadopor" id="solicitadopor" onkeyup="tipoLetra(this);" data-parsley-length="[1, 50]" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Destino del Pedido </label>'+
                                '<input type="text" class="form-control inputnextdet" name="destinodelpedido" id="destinodelpedido" onkeyup="tipoLetra(this);" data-parsley-length="[1, 255]" autocomplete="off">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-3">'+
                                '<label>Referencia</label>'+
                                '<input type="text" class="form-control inputnextdet" name="referencia" id="referencia" onkeyup="tipoLetra(this);" data-parsley-length="[1, 50]" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Orden Servicio</label>'+
                                '<input type="text" class="form-control inputnextdet" name="ordenservicio" id="ordenservicio" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Equipo </label>'+
                                '<input type="text" class="form-control inputnextdet" name="equipo" id="equipo" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Requisición</label>'+
                                '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarseriesrequisiciones()">Series</div>'+
                                        '</td>'+
                                        '<td>'+ 
                                            '<div class="form-line">'+
                                                '<input type="text" class="form-control inputnextdet" name="serierequisicion" id="serierequisicion" placeholder="Serie" autocomplete="off" onkeyup="tipoLetra(this);">'+
                                                '<input type="hidden" class="form-control" name="serierequisicionanterior" id="serierequisicionanterior" placeholder="Serie" autocomplete="off" onkeyup="tipoLetra(this);">'+
                                            '</div>'+
                                        '</td>'+
                                        '<td>'+ 
                                            '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnextdet" name="requisicion" id="requisicion" placeholder="Número" autocomplete="off">'+
                                            '</div>'+
                                        '</td>'+
                                    '</tr>'+    
                                '</table>'+
                            '</div>'+
                            '<div class="col-md-2" id="divbtngenerarformatoreqtyt" hidden>'+
                                '<label>Generar Formato REQ TYT</label>'+
                                '<div class="btn bg-blue waves-effect" id="btngenerarformatoreqtyt" onclick="actualizarurlexportarformatoreqtyt()">Generar Formato REQ TYT</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="revisioninsumosottab">'+
                        '<div class="row">'+
                            '<div class="col-md-3">'+
                                '<label>Número O.T. <span class="label label-danger" id="textonombreorden"></span></label>'+
                                '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<div class="form-line">'+
                                                '<input type="text" class="form-control" name="orden" id="orden" autocomplete="off" onkeyup="tipoLetra(this);">'+
                                                '<input type="hidden" class="form-control" name="ordenanterior" id="ordenanterior">'+
                                            '</div>'+
                                        '</td>'+
                                    '</tr>'+    
                                '</table>'+
                            '</div>'+
                        '</div>'+
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
                            '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                                '<table id="tablaproductosremisiones" class="table table-bordered tablaproductosremisiones">'+
                                    '<thead class="'+background_tables+'">'+
                                        '<tr>'+
                                          '<th class="'+background_tables+'">#</th>'+
                                          '<th class="customercolortheadth" id="thinsumospartidas">Insumo</th>'+
                                          '<th class="'+background_tables+'"><div style="width:100px !important;">Código</div></th>'+
                                          '<th class="customercolortheadth"><div style="width:400px !important;">Descripción</div></th>'+
                                          '<th class="'+background_tables+'">Unidad</th>'+
                                          '<th class="customercolortheadth">Por Remisionar</th>'+
                                          '<th class="customercolortheadth">Cantidad</th>'+
                                          '<th class="customercolortheadth">Precio $</th>'+
                                          '<th class="'+background_tables+'">Importe $</th>'+
                                          '<th class="customercolortheadth">Descuento %</th>'+
                                          '<th class="customercolortheadth">Descuento $</th>'+
                                          '<th class="'+background_tables+'">SubTotal $</th>'+
                                          '<th class="customercolortheadth">Iva %</th>'+
                                          '<th class="'+background_tables+'">Iva $</th>'+
                                          '<th class="'+background_tables+'">Total $</th>'+
                                          '<th class="'+background_tables+'">Costo $</th>'+
                                          '<th class="'+background_tables+'">Costo Total</th>'+
                                          '<th class="customercolortheadth">Comisión %</th>'+
                                          '<th class="'+background_tables+'">Comisión $</th>'+
                                          '<th class="bg-amber">Utilidad $</th>'+
                                          '<th class="'+background_tables+'">Moneda</th>'+
                                          '<th class="'+background_tables+'">Costo de Lista</th>'+
                                          '<th class="'+background_tables+'">Tipo de Cambio</th>'+
                                          '<th class="'+background_tables+'">Cotización</th>'+
                                          '<th class="customercolortheadth">ClaveProducto</th>'+
                                          '<th class="customercolortheadth">ClaveUnidad</th>'+
                                          '<th class="'+background_tables+'">Meses</th>'+
                                          '<th class="'+background_tables+'">TasaInteres</th>'+
                                          '<th class="'+background_tables+'">MontoInteres</th>'+
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
                            '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]" rows="3"></textarea>'+
                          '</div>'+ 
                            '<div class="col-md-3">'+
                                '<table class="table table-striped table-hover">'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;" colspan="2">'+
                                            '<input type="checkbox" name="tipoprecio" id="agregarivaalprecio" class="filled-in datotabla" value="1" />'+
                                            '<label for="agregarivaalprecio">Agregar Iva al Precio</label>'+
                                        '</td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;" colspan="2">'+
                                            '<input type="checkbox" name="capturaprecioneto" id="idcapturaprecioneto" class="filled-in datotabla" value="1" />'+
                                            '<label for="idcapturaprecioneto">Capturar Precio Neto $</label>'+
                                        '</td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Crédito</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="credito" id="credito" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Saldo</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="saldo" id="saldo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Utilidad</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="utilidad" id="utilidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Costo</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                '</table>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<table class="table table-striped table-hover">'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Importe</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Descuento</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">SubTotal</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Iva</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Total</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr hidden>'+
                                        '<td style="padding:0px !important;">Comisión</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="comision" id="comision" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                '</table>'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+
                            '<div class="col-md-12">'+   
                                '<h4 class="font-bold col-red" id="mensajecreditoexcedido"></h4>'+  
                            '</div>'+
                        '</div>'+
                    '</div>'+ 
                '</div>'+
            '</div>';
    $("#tabsform").html(tabs);
    //colocar autocomplette off  todo el formulario
    $(".form-control").attr('autocomplete','off');
    //colocar required en referencia segun la configuracion de la empresa
    if(pedirobligatoriamentereferenciarnremisiones == 'S'){
      $("#referencia").attr('required', 'required');
    }else{
        $("#referencia").removeAttr('required');
    }
    //colocar required en orden servicio segun la configuracion de la empresa
    if(pedirobligatoriamenteordenservicioenremisiones == 'S'){
      $("#ordenservicio").attr('required', 'required');
    }else{
        $("#ordenservicio").removeAttr('required');
    }
    //colocar required en equipo segun la configuracion de la empresa
    if(pedirobligatoriamenteequipoenremisiones == 'S'){
      $("#equipo").attr('required', 'required');
    }else{
        $("#equipo").removeAttr('required');
    }
    //colocar readonly o no a input de requisicion segun la configuracion de la empresa
    if(controlarconsecutivonumrequisicion == 'S'){
        $("#requisicion").attr('readonly', 'readonly');
    }else{
        $("#requisicion").removeAttr('readonly');
    }
    //ocultar tab de revision de insumos
    if(verificarinsumosremisionenot == 'N'){
        $("#revisioninsumosottab").hide();
        $("#tabrevisioninsumosottab").hide();
    }
    /*
    //ocultar o mostrar btoon para generar formato de req tyt
    if(generarformatorequisiciontyt == 'S'){
        $("#divbtngenerarformatoreqtyt").show();
    }else{
        $("#divbtngenerarformatoreqtyt").hide();
    }
    */
    $("#periodohoy").val(data.remision.Periodo);
    $("#folio").val(data.remision.Folio);
    $("#serie").val(data.remision.Serie);
    $("#serietexto").html("Serie: "+data.remision.Serie);
    $("#fecha").val(data.fecha).attr('min', data.fechasdisponiblesenmodificacion.fechamin).attr('max', data.fechasdisponiblesenmodificacion.fechamax);
    $("#cliente").val(data.cliente.Nombre);
    if(data.cliente.Nombre != null){
        $("#textonombrecliente").html(data.cliente.Nombre.substring(0, 40));
    }
    $("#numerocliente").val(data.cliente.Numero);
    $("#numeroclienteanterior").val(data.cliente.Numero);
    $("#agente").val(data.agente.Nombre);
    if(data.agente.Nombre != null){
        $("#textonombreagente").html(data.agente.Nombre.substring(0, 40));
    }
    $("#numeroagente").val(data.agente.Numero);
    $("#numeroagenteanterior").val(data.agente.Numero);
    $("#almacen").val(data.almacen.Nombre);
    if(data.almacen.Nombre != null){
        $("#textonombrealmacen").html(data.almacen.Nombre.substring(0, 40));
    }
    $("#numeroalmacen").val(data.almacen.Numero);
    $("#numeroalmacenanterior").val(data.almacen.Numero);
    $("#tipo").html(data.select_tipos_cliente);
    $("#unidad").html(data.select_tipos_unidad);
    $("#plazo").val(data.remision.Plazo);
    $("#pedido").val(data.remision.Pedido);
    $("#solicitadopor").val(data.remision.Solicita);
    $("#destinodelpedido").val(data.remision.Destino);
    $("#referencia").val(data.remision.Referencia);
    $("#ordenservicio").val(data.remision.Os);
    $("#equipo").val(data.remision.Eq);
    $("#serierequisicion").val(data.remision.SerieRq);
    $("#requisicion").val(data.remision.Rq);
    $("#observaciones").val(data.remision.Obs);
    $("#importe").val(data.importe);
    $("#descuento").val(data.descuento);
    $("#subtotal").val(data.subtotal);
    $("#iva").val(data.iva);
    $("#total").val(data.total);
    $("#costo").val(data.costo);
    $("#utilidad").val(data.utilidad);
    $("#comision").val(data.comision);
    $("#credito").val(number_format(round(data.credito, numerodecimales), numerodecimales, '.', '')); 
    $("#saldo").val(data.saldo);
    //detalles
    $("#tablaproductosremisiones tbody").html(data.filasdetallesremision);
    $("#numerofilas").val(data.numerodetallesremision);
    //colocar valores a contadores
    contadorproductos = data.contadorproductos;
    contadorfilas = data.contadorfilas;
    //ocultar botones de seleccion
    $("#botonobtenerclientes").show();
    $("#botonobteneragentes").show();
    $("#botonobteneralmacenes").hide();
    //mostrar el buscador de productos
    mostrarbuscadorcodigoproducto();
    //activar seelct2
    obtenertiposcliente();
    obtenertiposunidad();
    //activar busqueda de codigos
    $("#codigoabuscar").keypress(function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerproductoporcodigo();
        }
    });
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
    //activar busqueda para agentes
    $('#numeroagente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obteneragentepornumero();
        }
    });
    //regresar numero agente
    $('#numeroagente').on('change', function(e) {
        regresarnumeroagente();
    });
    //regresar numero almacen
    $('#numeroalmacen').on('change', function(e) {
        regresarnumeroalmacen();
    });
    //activar busqueda para ordenes
    $('#orden').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenerordenporfolio();
      }
    });
    //regresar folio orden
    $('#orden').on('change', function(e) {
      regresarfolioorden();
    });
    //activar busqueda
    $('#serierequisicion').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          obtenerserierqporserie();
        }
    });
    $('#serierequisicion').on('change', function(e) {
        obtenerserierqporserie();
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnextdet").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnextdet");          
      switch(code){
        case 13:
          $(".inputnextdet").eq(index + 1).focus().select(); 
          break;
        case 39:
          $(".inputnextdet").eq(index + 1).focus().select(); 
          break;
        case 37:
          $(".inputnextdet").eq(index - 1).focus().select(); 
          break;
      }
    });
    //mostrar o no insumos en partidas segun la configuracion de la empresa
    if(mostrarinsumoporpartidaenremisiones == 'S'){
        $("#thinsumospartidas").show();
        $(".tdinsumospartidas").show();
    }else{
        $("#thinsumospartidas").hide();  
        $(".tdinsumospartidas").hide();     
    }
    //colocar readonly si no puede modificar insumos
    var arrayusuariosamodificarinsumosproductos = usuariosamodificarinsumos.split(",");
    if(arrayusuariosamodificarinsumosproductos.indexOf(usuariologueado) == '-1'){
        $(".insumopartida").attr('readonly', 'readonly');
    }else{
        $(".insumopartida").removeAttr('readonly');
    }
    //colocar o no dataparsleyutilidad segun la configuracion de la empresa
    if(validarutilidadnegativa == 'S'){
        $(".utilidadpartida").removeAttr('data-parsley-utilidad');
        $("#utilidad").removeAttr('data-parsley-decimalesconfigurados');
    }else{
        $(".utilidadpartida").attr('data-parsley-utilidad', "0."+numerocerosconfiguradosinputnumberstep );
        $("#utilidad").attr('data-parsley-decimalesconfigurados', '/^[0-9]+[.]+[0-9]{4}$/');
    }
    //dar cambio en cantidad para colocar data parsley existencias de forma correcta
    $(".cantidadpartida").change();
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    seleccionartipocliente(data);
  }).fail( function() {
    msj_errorajax();
    $('.page-loader-wrapper').css('display', 'none');
  })
}
async function seleccionartipocliente(data){
    await retraso();
    $("#unidad").val(data.remision.Unidad).change();
    $("#unidad").select2();
    $("#tipo").val(data.remision.Tipo).change();
    $("#tipo").select2();
    setTimeout(function(){$("#folio").focus();},500);
    mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
    $('.page-loader-wrapper').css('display', 'none');
}
//guardar modificación
$("#btnGuardarModificacion").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
        var saldo = $("#saldo").val();
        var credito = $("#credito").val();
        if(parseFloat(saldo) <= parseFloat(credito)){
            var numerofilas = $("#numerofilas").val();
            if(parseInt(numerofilas) > 0  && parseInt(numerofilas) < 500){
                $('.page-loader-wrapper').css('display', 'block');
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url:remisiones_guardar_modificacion,
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
            msj_creditoexcedido();
        }
    }else{
        msjfaltandatosporcapturar();
    }
    //validar formulario
    form.parsley().validate();
});
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento){
    $.get(remisiones_obtener_datos_envio_email,{documento:documento}, function(data){
        $("#textomodalenviarpdfemail").html("Enviar email Remisión No." + documento);
        $("#emaildocumento").val(documento);
        $("#emailde").val(data.emailde);
        $("#emailpara").val(data.emailpara);
        $("#email2cc").val(data.correodefault1enviodocumentos);
        $("#email3cc").val(data.correodefault2enviodocumentos);
        if(data.email2cc != ""){
            $("#correosconcopia").append('<option value="'+data.email2cc+'" selected>'+data.email2cc+'</option>');
        }
        if(data.email3cc != ""){
            $("#correosconcopia").append('<option value="'+data.email3cc+'" selected>'+data.email3cc+'</option>');
        }
        $("#emailasunto").val("REMISIÓN NO. " + documento +" DE "+ nombreempresa);
        $("#emailmensaje").val("REMISIÓN NO. " + documento +" DE "+ nombreempresa);
        $(".dropify-clear").trigger("click");
        $("#divadjuntararchivo").hide();
        $("#modalenviarpdfemail").modal('show');
        $("#correosconcopia").select2({
            dropdownParent: $('#modalenviarpdfemail'),
            tags: true,
            width: '78.00em',
            tokenSeparators: [',', ' ']
        })
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
        url:remisiones_enviar_pdfs_email,
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data){
          msj_documentoenviadoporemailcorrectamente();
          $("#modalenviarpdfemail").modal('hide');
          $("#correosconcopia").html("");
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
//modificar datos generales
function modificardatosgeneralesdocumento(Remision){
  $.get(remisiones_obtener_datos_generales,{Remision:Remision}, function(data){
    $("#modalmodificardatosgeneralesdocumento").modal('show');
    $("#remisiondatosgenerales").val(Remision);
    $("#ordenserviciodatosgenerales").val(data.Os);
    $("#equipodatosgenerales").val(data.Eq);
    setTimeout(function(){$("#remisiondatosgenerales").focus();},500);
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnextdet").keyup(function (e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        var index = $(this).index(".inputnextdet");          
        switch(code){
            case 13:
                $(".inputnextdet").eq(index + 1).focus().select(); 
                break;
            case 39:
                $(".inputnextdet").eq(index + 1).focus().select(); 
                break;
            case 37:
                $(".inputnextdet").eq(index - 1).focus().select(); 
                break;
        }
    });
  });
}
//guardar cambios datos generales
$("#btnguardardatosgenerales").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formamodificardatosgenerales")[0]);
  var form = $("#formamodificardatosgenerales");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:remisiones_guardar_modificacion_datos_generales,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#modalmodificardatosgeneralesdocumento').modal('hide');
        msj_datosguardadoscorrectamente();
        $("#formamodificardatosgenerales")[0].reset();
        $('.page-loader-wrapper').css('display', 'none');
      },
      error:function(data){
        if(data.status == 403){
          msj_errorenpermisos();
        }else{
          msj_errorajax();
        }
        $('#modalmodificardatosgeneralesdocumento').modal('hide');
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
                                            '<th>Documento</th>'+
                                            '<th>Cliente</th>'+
                                            '<th>Total</th>'+
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
        keys: true,
      "pageLength": 100,
      'sDom': 't',
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
          url: remisiones_buscar_folio_string_like,
          data: function (d) {
              d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'Remision', name: 'Remision', orderable: false, searchable: true },
          { data: 'NombreCliente', name: 'NombreCliente', orderable: false, searchable: true },
          { data: 'Total', name: 'Total', orderable: false, searchable: true  },
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
        agregararraypdf(data.Remision);
    });
}
//generar documento en iframe
function generardocumentoeniframe(Remision){
  var arraypdf = new Array();
  var folios = [Remision];
  arraypdf.push(folios);
  var form_data = new FormData();
  form_data.append('arraypdf', arraypdf); 
  form_data.append('tipogeneracionpdf', 0);
  form_data.append('numerodecimalesdocumento', 2);
  form_data.append('imprimirdirectamente', 1);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:remisiones_generar_pdfs,
    data: form_data,
    type: 'POST',
    contentType: false,
    processData: false,
    success: function (data) {
      $('#pdfiframe').attr("src", urlpdfsimpresionesrapidas+data);
      setTimeout(function(){imprimirdirecto();},500);    
    },
    error: function (data) {
      console.log(data);
    }
  });
}
//imprimir documento pdf directamente
function imprimirdirecto(){
  var pdfFrame = window.frames["pdfiframe"];
  pdfFrame.focus();
  pdfFrame.print();
}
//configurar tabla
function configurar_tabla(){
    var checkboxscolumnas = '';
    var optionsselectbusquedas = '';
    var campos = campos_activados.split(",");
    for (var i = 0; i < campos.length; i++) {
      var returncheckboxfalse = '';
      if(campos[i] == 'Compra' || campos[i] == 'Status' || campos[i] == 'Periodo'){
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