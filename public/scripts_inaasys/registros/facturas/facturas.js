'use strict'
var tabla;
var form;
var contadorfilasfacturas = 0;
var tipoformatopdf;
//funcion que se ejecuta al inicio
function init(){
  campos_a_filtrar_en_busquedas();
  listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 1500));
}
function retrasoremisionesseleccionadas(){
  return new Promise(resolve => setTimeout(resolve, 2000));
}
function asignarfechaactual(){
  $.get(ordenes_compra_obtener_fecha_actual_datetimelocal, function(fechas){
    $("#fecha").val(fechas.fecha).attr('min', fechas.fechamin).attr('max', fechas.fechamax);
  })
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  var serie = $("#serie").val();
  $.get(facturas_obtener_ultimo_folio, {serie:serie}, function(folio){
    $("#folio").val(folio);
  })
}
//obtener tipos ordenes de compra
function obtenertiposordenescompra(defaultvalue){
  $.get(facturas_obtener_tipos, function(select_tipos){
    $("#tipo").html(select_tipos);
    if(defaultvalue != undefined){
      $("#tipo").val(defaultvalue).change();
      $("#tipo").select2();
    }
  })
}
//obtener tipos de unidades
function obtenertiposunidades(){
  $.get(facturas_obtener_tipos_unidades, function(select_tipos_unidades){
    $("#tipounidad").html(select_tipos_unidades);
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
//
function cerrarmodalnumeropedido(){
  $("#modalremisionesporpedido").modal('hide');
  $("#ModalFormulario").modal('show');
  $("#ModalFormulario").css('overflow', 'auto');
  ocultarformulario();
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
        url: facturas_obtener,
        data: function (d) {
            d.periodo = $("#periodo").val();
        }
    },
    "createdRow": function( row, data, dataIndex){
        if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
        else if( data.Status ==  `POR COBRAR`){ $(row).addClass('bg-red');}
    },
    columns: campos_tabla,
    "drawCallback": function( data ) {
        $("#sumaimportefiltrado").html(number_format(round(data.json.sumaimporte, numerodecimales), numerodecimales, '.', ','));
        $("#sumadescuentofiltrado").html(number_format(round(data.json.sumadescuento, numerodecimales), numerodecimales, '.', ','));
        $("#sumasubtotalfiltrado").html(number_format(round(data.json.sumasubtotal, numerodecimales), numerodecimales, '.', ','));
        $("#sumaivafiltrado").html(number_format(round(data.json.sumaiva, numerodecimales), numerodecimales, '.', ','));
        $("#sumatotalfiltrado").html(number_format(round(data.json.sumatotal, numerodecimales), numerodecimales, '.', ','));
        $("#sumaabonosfiltrado").html(number_format(round(data.json.sumaabonos, numerodecimales), numerodecimales, '.', ','));
        $("#sumadescuentosfiltrado").html(number_format(round(data.json.sumadescuentos, numerodecimales), numerodecimales, '.', ','));
        $("#sumasaldofiltrado").html(number_format(round(data.json.sumasaldo, numerodecimales), numerodecimales, '.', ','));
        $("#sumacostofiltrado").html(number_format(round(data.json.sumacosto, numerodecimales), numerodecimales, '.', ','));
        $("#sumacomisionfiltrado").html(number_format(round(data.json.sumacomision, numerodecimales), numerodecimales, '.', ','));
        $("#sumautilidadfiltrado").html(number_format(round(data.json.sumautilidad, numerodecimales), numerodecimales, '.', ','));
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
      //tooltip mensajes
      $('[data-toggle="tooltip"]').tooltip({
        container: 'body'
      });
    }
  });
  //modificacion al dar doble click
  $('#tbllistado tbody').on('dblclick', 'tr', function () {
    var data = tabla.row( this ).data();
    obtenerdatos(data.Factura);
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
  var numeroalmacen = 1;
  var form_data = new FormData();
  form_data.append('partidasexcel', partidasexcel);
  form_data.append('numeroalmacen', numeroalmacen);
  form_data.append('contadorproductos', contadorproductos);
  form_data.append('contadorfilas', contadorfilas);
  form_data.append('partida', partida);
  form_data.append('arraycodigospartidas', arraycodigospartidas);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:facturas_cargar_partidas_excel,
    data: form_data,
    type: 'POST',
    contentType: false,
    processData: false,
    success: function (data) {
      contadorfilas = data.contadorfilas;
      contadorproductos = data.contadorproductos;
      partida = data.numeropartida;
      $("#tabladetallesfactura tbody").append(data.filasdetallesfactura);
      comprobarfilas();
      calculartotal();
      $("#codigoabuscar").val("");
      $("#codigogastoabuscar").val("");
      //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
      $(".inputnextdet").keyup(function (e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        var index = $(this).index(".inputnextdet");
        switch(code){
          case 13:
            //$(".inputnextdet").eq(index + 1).focus().select();
            break;
          case 39:
            $(".inputnextdet").eq(index + 1).focus().select();
            break;
          case 37:
            $(".inputnextdet").eq(index - 1).focus().select();
            break;
        }
      });
    },
    error: function (data) {
      console.log(data);
    }
  });
});
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
            url: facturas_obtener_clientes,
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
        seleccionarcliente(data.Numero, data.Nombre, data.Plazo, data.Rfc, data.ClaveFormaPago, data.NombreFormaPago, data.ClaveMetodoPago, data.NombreMetodoPago, data.ClaveUsoCfdi, data.NombreUsoCfdi, "", "", data.Agente, data.Credito, data.Saldo, data.ClaveRegimenFiscal, data.RegimenFiscal);
    });
}
//seleccionar cliente
function seleccionarcliente(Numero, Nombre, Plazo, Rfc, claveformapago, formapago, clavemetodopago, metodopago, claveusocfdi, usocfdi, claveresidenciafiscal, residenciafiscal,NumeroAgente,CreditoCliente,SaldoCliente, claveregimenfiscalreceptor, regimenfiscalreceptor){
  var numeroclienteanterior = $("#numeroclienteanterior").val();
  var numerocliente = Numero;
  if(numeroclienteanterior != numerocliente){
    var numerofilas = $("#numerofilas").val()
    if(parseInt(numerofilas) > 0){
      var confirmacion = confirm("Esta seguro de cambiar el cliente, esto eliminara las partidas agregadas (Remisiones ó Servicios)?");
    }else{
      var confirmacion = true;
    }
    if (confirmacion == true) {
      //validar si el RFC del cliente es igual al de la empresa si es asi la seria de la factura debe ser con el depto INTERNA
      var emisorrfc = $("#emisorrfc").val();
      if(emisorrfc == Rfc){
        var confirmacionrfcigual = confirm("El cliente seleccionado tiene el mismo RFC que el emisor de la FACTURA por lo tanto la serie debe ser INTERNA esta seguro de seguir con los cambios?");
      }
      if(confirmacionrfcigual == true){
          $.get(facturas_obtener_serie_interna, function(datos){
            $("#tabladetallesfactura tbody").html("");
            $("#folio").val(datos.Folio);
            $("#serie").val(datos.Serie);
            $("#esquema").val(datos.Esquema);
            $("#depto").val(datos.Depto);
            $("#serietexto").html("Serie: "+datos.Serie);
            $("#esquematexto").html("Esquema: "+datos.Esquema);
            comprobartiposerie(datos.Depto);
            //comprobar numero de filas en la tabla
            comprobarfilas();
            //calcular totales compras nota proveedor
            calculartotal();
            //colocar strings vacios
            $("#stringremisionesseleccionadas").val("");
            $("#stringordenesseleccionadas").val("");
            contadorproductos = 0;
            contadorfilas = 0;
            partida = 1;
          })
      }
        $("#tabladetallesfactura tbody").html("");
        $("#numerocliente").val(Numero);
        $("#numeroclienteanterior").val(Numero);
        $("#cliente").val(Nombre);
        if(Nombre != null){
          $("#textonombrecliente").html(Nombre.substring(0, 40));
        }
        $("#rfccliente").val(Rfc);
        if(Rfc == 'XAXX010101000'){
          $("#divperiodicidad").show();
          $("#divmes").show();
          $("#claveperiodicidad").attr('required', 'required');
          $("#claveperiodicidadanterior").attr('required', 'required');
          $("#periodicidad").attr('required', 'required');
          $("#clavemes").attr('required', 'required');
          $("#clavemesanterior").attr('required', 'required');
          $("#mes").attr('required', 'required');
        }else{
          $("#divperiodicidad").hide();
          $("#divmes").hide();
          $("#claveperiodicidad").removeAttr('required');
          $("#claveperiodicidadanterior").removeAttr('required');
          $("#periodicidad").removeAttr('required');
          $("#clavemes").removeAttr('required');
          $("#clavemesanterior").removeAttr('required');
          $("#mes").removeAttr('required');
        }
        $("#plazo").val(Plazo);
        //credito y saldo
        $("#credito").val(number_format(round(CreditoCliente, numerodecimales), numerodecimales, '.', ''));
        $("#saldo").val(number_format(round(SaldoCliente, numerodecimales), numerodecimales, '.', ''));
        //datos pestaña receptor o cliente
        $("#receptorrfc").val(Rfc);
        $("#receptornombre").val(Nombre);
        $("#claveformapago").val(claveformapago);
        $("#claveformapagoanterior").val(claveformapago);
        $("#formapago").val(formapago);
        if(formapago != null){
          $("#textonombreformapago").html(formapago.substring(0, 40));
        }
        $("#clavemetodopago").val(clavemetodopago);
        $("#clavemetodopagoanterior").val(clavemetodopago);
        $("#metodopago").val(metodopago);
        if(metodopago != null){
          $("#textonombremetodopago").html(metodopago.substring(0, 40));
        }
        $("#claveusocfdi").val(claveusocfdi);
        $("#claveusocfdianterior").val(claveusocfdi);
        $("#usocfdi").val(usocfdi);
        if(usocfdi != null){
          $("#textonombreusocfdi").html(usocfdi.substring(0, 40));
        }
        $("#claveresidenciafiscal").val(claveresidenciafiscal);
        $("#claveresidenciafiscalanterior").val(claveresidenciafiscal);
        $("#residenciafiscal").val(residenciafiscal);
        if(residenciafiscal != null){
          $("#textonombreresidenciafiscal").html(residenciafiscal.substring(0, 40));
        }
        //regimen fiscal
        $("#claveregimenfiscalreceptor").val(claveregimenfiscalreceptor);
        $("#claveregimenfiscalreceptoranterior").val(claveregimenfiscalreceptor);
        $("#regimenfiscalreceptor").val(regimenfiscalreceptor);
        if(regimenfiscalreceptor != null){
            $("#textonombreregimenfiscalreceptor").html(regimenfiscalreceptor.substring(0, 40));
        }

        //Adenda
        if(Numero== 22 && Nombre == 'SEGUROS INBURSA, S.A., GRUPO FINANCIERO INBURSA'){
            $('#afectadoAdenda').attr("disabled", false);
            $('#emisorSiniestroAdenda').attr("disabled", false);
            $('#numeroSiniestroAdenda').attr("disabled", false);

            $('#afectadoAdenda').attr("required", true);
            $('#emisorSiniestroAdenda').attr("required", true);
            $('#numeroSiniestroAdenda').attr("required", true);
          }else{
            $('#afectadoAdenda').attr("disabled", true);
            $('#emisorSiniestroAdenda').attr("disabled", true);
            $('#numeroSiniestroAdenda').attr("disabled", true);

            $('#emisorSiniestroAdenda').attr("required", false);
            $('#numeroSiniestroAdenda').attr("required", false);
            $('#afectadoAdenda').attr("required", false);
          }
        //datos agente
        $.get(facturas_obtener_datos_agente, {NumeroAgente:NumeroAgente}, function(Agente){
          $("#numeroagente").val(Agente.Numero);
          $("#numeroagenteanterior").val(Agente.Numero);
          $("#rfcagente").val(Agente.Rfc);
          $("#agente").val(Agente.Nombre);
          if(Agente.Nombre != null){
            $("#textonombreagente").html(Agente.Nombre.substring(0, 40));
          }
        })
        //comprobar si mostrar botones
        var Depto = $("#depto").val();
        comprobartiposerie(Depto);
        //comprobar numero de filas en la tabla
        comprobarfilas();
        //calcular totales compras nota proveedor
        calculartotal();
        //colocar strings vacios
        $("#stringremisionesseleccionadas").val("");
        $("#stringordenesseleccionadas").val("");
        contadorproductos = 0;
        contadorfilas = 0;
        partida = 1;
        mostrarformulario();
    }
  }
}
//obtener registros de almacenes
function obteneragentes(){
    ocultarformulario();
    var tablaagentes= '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Agentes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoagente" class="tbllistadoagente table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Nombre</th>'+
                                                    '<th>Rfc</th>'+
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
              url: facturas_obtener_agentes,
          },
          columns: [
              { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
              { data: 'Numero', name: 'Numero' },
              { data: 'Nombre', name: 'Nombre' },
              { data: 'Rfc', name: 'Rfc' }
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
          seleccionaragente(data.Numero, data.Nombre, data.Rfc);
      });
}
//seleccionar almacen
function seleccionaragente(Numero, Nombre, Rfc){
  var numeroagenteanterior = $("#numeroagenteanterior").val();
  var numeroagente = Numero;
  if(numeroagenteanterior != numeroagente){
    $("#numeroagente").val(Numero);
    $("#numeroagenteanterior").val(Numero);
    $("#rfcagente").val(Rfc);
    $("#agente").val(Nombre);
    if(Nombre != null){
      $("#textonombreagente").html(Nombre.substring(0, 40));
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
        url: facturas_obtener_codigos_postales,
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
        $buscar.focus();
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
      seleccionarlugarexpedicion(data.Clave, data.Estado);
  });
}
//seleccionar lugar expedicion
function seleccionarlugarexpedicion(Clave, Estado){
  var lugarexpedicionanterior = $("#lugarexpedicionanterior").val();
  var lugarexpedicion = Clave;
  if(lugarexpedicionanterior != lugarexpedicion){
    $("#lugarexpedicion").val(Clave);
    $("#lugarexpedicionanterior").val(Clave);
    $("#textonombrelugarexpedicion").val(Estado);
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
        url: facturas_obtener_regimenes_fiscales
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
        $buscar.focus();
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
        url: facturas_obtener_tipos_relacion
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
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
        url: facturas_obtener_formas_pago
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
          { data: 'Descripcion', name: 'Descripcion', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
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
    $('#clavemetodopago').focus()
    if(Clave == '99'){
      seleccionarmetodopago('PPD','Pago en parcialidades o diferido');
    }else{
      seleccionarmetodopago('PUE','Pago en una sola exhibición');
    }
    mostrarformulario();
  }
}
//obtener metodos de pago
function obtenermetodospago(){
  ocultarformulario();
  var tablametodospago='<div class="modal-header '+background_forms_and_modals+'">'+
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
        url: facturas_obtener_metodos_pago
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
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
//seleccionar metodo pago
function seleccionarmetodopago(Clave, Nombre){
  var clavemetodopagoanterior = $("#clavemetodopagoanterior").val();
  var clavemetodopago = Clave;
  if(clavemetodopagoanterior != clavemetodopago){
    $("#clavemetodopago").val(Clave);
    $("#clavemetodopagoanterior").val(Clave);
    $("#metodopago").val(Nombre);
    if(Nombre != null){
      $("#textonombremetodopago").html(Nombre.substring(0, 40));
    }
    $('#clavemetodopago').focus()
    mostrarformulario();
  }else{
    $('#clavemetodopago').focus()
  }
}
//obtener usos cfdi
function obtenerusoscfdi(){
  ocultarformulario();
  var tablausoscfdi='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Usos CFDI</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadousocfdi" class="tbllistadousocfdi table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Clave</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>Fisica</th>'+
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
  $("#contenidomodaltablas").html(tablausoscfdi);
  var tusocfdi = $('#tbllistadousocfdi').DataTable({
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
        url: facturas_obtener_usos_cfdi
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
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadousocfdi').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadousocfdi tbody').on('dblclick', 'tr', function () {
      var data = tusocfdi.row( this ).data();
      seleccionarusocfdi(data.Clave, data.Nombre);
  });
}
//seleccionar uso cfdi
function seleccionarusocfdi(Clave, Nombre){
  var claveusocfdianterior = $("#claveusocfdianterior").val();
  var claveusocfdi = Clave;
  if(claveusocfdianterior != claveusocfdi){
    $('#seleccionarusocfdi').focus()
    $("#claveusocfdi").val(Clave);
    $("#claveusocfdianterior").val(Clave);
    $("#usocfdi").val(Nombre);
    if(Nombre != null){
      $("#textonombreusocfdi").html(Nombre.substring(0, 40));
    }
    mostrarformulario();
  }else{
    $('#seleccionarusocfdi').focus()
  }
}
//obtener residencias fiscales
function obtenerresidenciasfiscales(){
  ocultarformulario();
  var tablaresidenciasfiscales='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Residencias Fiscales</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoresidencialfiscal" class="tbllistadoresidencialfiscal table table-bordered table-striped table-hover" style="width:100% !important;">'+
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
  $("#contenidomodaltablas").html(tablaresidenciasfiscales);
  var tresfis = $('#tbllistadoresidencialfiscal').DataTable({
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
        url: facturas_obtener_residencias_fiscales
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Clave', name: 'Clave' },
          { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false},
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoresidencialfiscal').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoresidencialfiscal tbody').on('dblclick', 'tr', function () {
      var data = tresfis.row( this ).data();
      seleccionarresidenciafiscal(data.Clave, data.Nombre);
  });
}
//seleccionar residencia fiscal
function seleccionarresidenciafiscal(Clave, Nombre){
  var claveresidenciafiscalanterior = $("#claveresidenciafiscalanterior").val();
  var claveresidenciafiscal = Clave;
  if(claveresidenciafiscalanterior != claveresidenciafiscal){
    $("#claveresidenciafiscal").val(Clave);
    $("#claveresidenciafiscalanterior").val(Clave);
    $("#residenciafiscal").val(Nombre);
    if(Nombre != null){
      $("#textonombreresidenciafiscal").html(Nombre.substring(0, 40));
    }
    mostrarformulario();
  }
}
//obtener series facturas
function obtenerfoliosfacturas(){
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
                                                        '<th>Depto</th>'+
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
        url: facturas_obtener_folios_fiscales
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Serie', name: 'Serie' },
          { data: 'Esquema', name: 'Esquema', orderable: false, searchable: false},
          { data: 'Depto', name: 'Depto', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
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
      seleccionarfoliofiscal(data.Serie, data.Esquema, data.Depto);
  });
}
function seleccionarfoliofiscal(Serie, Esquema, Depto){
  var numerofilas = $("#numerofilas").val()
  if(parseInt(numerofilas) > 0){
    var confirmacion = confirm("Esta seguro de cambiar el folio fiscal, esto eliminara las partidas agregadas (Remisiones ó Servicios)?");
  }else{
    var confirmacion = true;
  }
  if (confirmacion == true) {
    $.get(facturas_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie,Esquema:Esquema,Depto:Depto}, function(folio){
      $("#tabladetallesfactura tbody").html("");
      $("#folio").val(folio);
      $("#serie").val(Serie);
      $("#esquema").val(Esquema);
      $("#depto").val(Depto);
      $("#serietexto").html("Serie: "+Serie);
      $("#esquematexto").html("Esquema: "+Esquema);
      comprobartiposerie(Depto);
      //comprobar numero de filas en la tabla
      comprobarfilas();
      //calcular totales compras nota proveedor
      calculartotal();
      //colocar strings vacios
      $("#stringremisionesseleccionadas").val("");
      $("#stringordenesseleccionadas").val("");
      contadorproductos = 0;
      contadorfilas = 0;
      partida = 1;
      mostrarformulario();
    })
  }
}
//comprobar el tipo de serie para la factura
function comprobartiposerie(Depto){
  //var cliente = $("#cliente").val();
  //if(cliente != ""){
    var esquema = $("#esquema").val();
    if(Depto == 'PRODUCTOS'){
      $("#divlistarremisiones").show();
      if(esquema == 'INTERNA'){
        $("#divbuscarcodigosgastos").show();
      }else{
        $("#divbuscarcodigosgastos").hide();
      }
      $("#divlistarservicios").hide();
      $("#divbuscarcodigosservicios").hide();
      $("#divbuscarcodigos").hide();
      $("#divimportarpartidas").hide();
    }else if(Depto == 'SERVICIO'){
      $("#divlistarremisiones").hide();
      $("#divbuscarcodigosgastos").hide();
      $("#divlistarservicios").show();
      $("#divbuscarcodigosservicios").show();
      $("#divbuscarcodigos").hide();
      $("#divimportarpartidas").hide();
    }else{
      $("#divlistarremisiones").hide();
      $("#divbuscarcodigosgastos").hide();
      $("#divlistarservicios").hide();
      $("#divbuscarcodigosservicios").hide();
      $("#divbuscarcodigos").show();
      $("#divimportarpartidas").show();
    }
  //}
}
//listar todas las facturas
function listarremisiones(){
  ocultarformulario();
  var tablaremisiones ='<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Facturar Remisiones</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                        '<div class="row">'+
                          '<div class="col-md-12">'+
                            '<div class="table-responsive">'+
                              '<table id="tbllistadoremision" class="tbllistadoremision table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                '<thead class="'+background_tables+'">'+
                                  '<tr>'+
                                    '<th>Remisión</th>'+
                                    '<th>Fecha</th>'+
                                    '<th>Cliente</th>'+
                                    '<th>Nombre</th>'+
                                    '<th>Facturar $</th>'+
                                    '<th>Plazo</th>'+
                                    '<th>Pedido</th>'+
                                    '<th>Selecciona</th>'+
                                  '</tr>'+
                                '</thead>'+
                                '<tbody></tbody>'+
                              '</table>'+
                            '</div>'+
                          '</div>'+
                        '</div>'+
                        '<div class="row">'+
                          '<div class="col-md-6 col-md-offset-4">'+
                          '</div>'+
                          '<div class="col-md-2">'+
                            '<label>Total $</label>'+
                            '<input type="text" class="form-control divorinputmodmd" name="totalafacturarAux" id="totalafacturarAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                            '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalafacturar" id="totalafacturar" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                          '</div>'+
                        '</div>'+
                      '</div>'+
                      '<div class="modal-footer">'+
                        '<div type="button" class="btn btn-info btn-sm" onclick="mostrarmodalnumeropedido();">Seleccionar remisiones por número de pedido</div>'+
                        '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                        '<button type="button" class="btn btn-success btn-sm" onclick="cargarremisionesseleccionadas();">Cargar remisiones</button>'+
                      '</div>';
    $("#contenidomodaltablas").html(tablaremisiones);
    $('#tbllistadoremision').DataTable({
        keys: true,
        "lengthMenu": [ 200 ,500, 1000 ],
        "pageLength": 200,
        //"searching": false,
        //"paging":   false,
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
          url: facturas_obtener_remisiones,
          data: function (d) {
              //d.numerocliente = $("#numerocliente").val();
              d.stringremisionesseleccionadas = $("#stringremisionesseleccionadas").val();
          }
        },
        columns: [
            { data: 'Remision', name: 'Remision' },
            { data: 'Fecha', name: 'Fecha' },
            { data: 'Cliente', name: 'Cliente', orderable: false, searchable: false },
            { data: 'NombreCliente', name: 'NombreCliente', orderable: false, searchable: false },
            { data: 'Facturar', name: 'Facturar', orderable: false, searchable: false },
            { data: 'Plazo', name: 'Plazo', orderable: false, searchable: false },
            { data: 'Pedido', name: 'Pedido', orderable: false, searchable: false },
            { data: 'Selecciona', name: 'Selecciona', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoremision').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 1000,
        "order": [[ 0, "desc" ]]
    });
}
//mostrar modal remisiones por numero de pedido
function mostrarmodalnumeropedido(){
  $("#modalremisionesporpedido").modal('show');
  $("#modalremisionesporpedido").css('overflow', 'auto');
  $("#ModalFormulario").modal('hide');
  $("#numeropedidoremisiones").val("");
  setTimeout(function(){$("#numeropedidoremisiones").focus();},500);
}
//seleccionar las remisiones por numero de pedido especificado
function seleccionarremisionesporpedido(){
  $("#tabladetallesfactura tbody").html("");
  partida=1;
  contadorfilas=0;
  contadorproductos=0;
  var form = $("#formremisionesporpedido");
  if (form.parsley().isValid()){
    var numeropedidoremisiones = $("#numeropedidoremisiones").val();
    var numerocliente = $("#numerocliente").val();
    var tipooperacion = $("#tipooperacion").val();
    var tablaremisiones ='<div class="modal-header '+background_forms_and_modals+'">'+
                              '<h4 class="modal-title">Facturar Remisiones</h4>'+
                          '</div>'+
                          '<div class="modal-body">'+
                            '<div class="row">'+
                              '<div class="col-md-12">'+
                                '<div class="table-responsive">'+
                                  '<table id="tbllistadoremision" class="tbllistadoremision table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                    '<thead class="'+background_tables+'">'+
                                      '<tr>'+
                                        '<th>Remisión</th>'+
                                        '<th>Fecha</th>'+
                                        '<th>Cliente</th>'+
                                        '<th>Nombre</th>'+
                                        '<th>Facturar $</th>'+
                                        '<th>Plazo</th>'+
                                        '<th>Pedido</th>'+
                                        '<th>Selecciona</th>'+
                                      '</tr>'+
                                    '</thead>'+
                                    '<tbody></tbody>'+
                                  '</table>'+
                                '</div>'+
                              '</div>'+
                            '</div>'+
                            '<div class="row">'+
                              '<div class="col-md-6 col-md-offset-4">'+
                              '</div>'+
                              '<div class="col-md-2">'+
                                '<label>Total $</label>'+
                                '<input type="text" class="form-control divorinputmodmd" name="totalafacturarAux" id="totalafacturarAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalafacturar" id="totalafacturar" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                              '</div>'+
                            '</div>'+
                          '</div>'+
                          '<div class="modal-footer">'+
                            '<div type="button" class="btn btn-info btn-sm" onclick="mostrarmodalnumeropedido();">Seleccionar remisiones por número de pedido</div>'+
                            '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                            '<button type="button" class="btn btn-success btn-sm" onclick="cargarremisionesseleccionadas();">Cargar remisiones</button>'+
                          '</div>';
                          $("#contenidomodaltablas").html(tablaremisiones);
                          $('#tbllistadoremision').DataTable({
                            keys: true,
                            //"searching": false,
                            //"paging":   false,
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
                              url: facturas_obtener_remisiones_por_pedido,
                              data: function (d) {
                                d.numerocliente = numerocliente;
                                d.numeropedidoremisiones = numeropedidoremisiones;
                              }
                            },
                            columns: [
                              { data: 'Remision', name: 'Remision' },
                              { data: 'Fecha', name: 'Fecha' },
                              { data: 'Cliente', name: 'Cliente', orderable: false, searchable: false },
                              { data: 'NombreCliente', name: 'NombreCliente', orderable: false, searchable: false },
                              { data: 'Facturar', name: 'Facturar', orderable: false, searchable: false },
                              { data: 'Plazo', name: 'Plazo', orderable: false, searchable: false },
                              { data: 'Pedido', name: 'Pedido', orderable: false, searchable: false },
                              { data: 'Selecciona', name: 'Selecciona', orderable: false, searchable: false }
                            ],
                            "initComplete": function() {
                              var $buscar = $('div.dataTables_filter input');
                              $buscar.focus();
                              $buscar.unbind();
                              $buscar.bind('keyup change', function(e) {
                                if(e.keyCode == 13 || this.value == "") {
                                  $('#tbllistadoremision').DataTable().search( this.value ).draw();
                                }
                              });
                              construirarrayremisionesseleccionadas();
                              arrayremisionesseleccionadas = [];
                              var lista = document.getElementsByClassName("remisionesseleccionadas");
                              for (var i = 0; i < lista.length; i++) {
                                if(lista[i].checked){
                                  arrayremisionesseleccionadas.push(lista[i].value);
                                }
                              }
                              $("#stringremisionesseleccionadas").val(arrayremisionesseleccionadas.sort());
                              calculartotalafacturar("REMISIONES");
                            },
                            "iDisplayLength": 1000,
                            "order": [[ 0, "desc" ]]
                          });
    $("#modalremisionesporpedido").modal('hide');
    $("#ModalFormulario").modal('show');
    $("#ModalFormulario").css('overflow', 'auto');
  }else{
    msjfaltandatosporcapturar();
  }
  //validar formulario
  form.parsley().validate();
}
//construir array de remisiones seleccionadas
var arrayremisionesseleccionadas = new Array();
function construirarrayremisionesseleccionadas(Remision){
  if( $("#idremisionesseleccionadas"+Remision).prop('checked') ) {
    var stringremisionesseleccionadas = $("#stringremisionesseleccionadas").val();
    if(stringremisionesseleccionadas != ""){
      arrayremisionesseleccionadas = stringremisionesseleccionadas.split(",");
    }
    var coincidencias = 0;
    if(arrayremisionesseleccionadas.length > 0){
        for(var i = 0; i < arrayremisionesseleccionadas.length; i++){
            if(Remision == arrayremisionesseleccionadas[i]){
                coincidencias++;
            }
        }
    }
    if(coincidencias == 0){
      arrayremisionesseleccionadas.push(Remision);
        $("#stringremisionesseleccionadas").val(arrayremisionesseleccionadas.sort());
    }
  }else{
    EliminarElementoArrayPorValor(arrayremisionesseleccionadas,Remision);
    $("#stringremisionesseleccionadas").val(arrayremisionesseleccionadas.sort());
  }
}
var arrayclientesremisionesseleccionadas = [];
//obtener todos los datos de la orden de compra seleccionada
function seleccionarremision(Folio,Remision,Cliente){
  if( $('#idremisionesseleccionadas'+Remision).prop('checked') ) {
    var cantidadclientesremisionesseleccionadas = arrayclientesremisionesseleccionadas.length;
    if(cantidadclientesremisionesseleccionadas == 0){
      arrayclientesremisionesseleccionadas.push(Cliente);
    }else{
      var existeclienteenarray = arrayclientesremisionesseleccionadas.indexOf(Cliente);
      if(existeclienteenarray == -1){
        alert("Solo se debe seleccionar un mismo cliente para facturación");
        $('#idremisionesseleccionadas'+Remision).removeAttr('checked');
      }else{
        arrayclientesremisionesseleccionadas.push(Cliente);
      }
    }
  }else{
    arrayclientesremisionesseleccionadas.pop();
  }
  construirarrayremisionesseleccionadas(Remision);
  calculartotalafacturar("REMISIONES");
}
//cargar remisiones
function cargarremisionesseleccionadas(){
  $('.page-loader-wrapper').css('display', 'block');
  //colocar strings vacios
  //$("#stringremisionesseleccionadas").val("");
  //$("#stringordenesseleccionadas").val("");
  contadorproductos = 0;
  contadorfilas = 0;
  partida = 1;
  $("#tabladetallesfactura tbody").html("");
  var tipooperacion = $("#tipooperacion").val();
  var stringremisionesseleccionadas = $("#stringremisionesseleccionadas").val();
  $.get(facturas_obtener_remision, {stringremisionesseleccionadas:stringremisionesseleccionadas, contadorfilas:contadorfilas, partida:partida, tipooperacion:tipooperacion, contadorproductos:contadorproductos}, function(data){
    //cargar datos cliente
    $("#numerocliente").val(data.cliente);
    $("#numeroclienteanterior").val("");
    obtenerclienteremisionesseleccionadas();
    seleccionarclienteremisionesseleccionadas(data);
  })
}
async function seleccionarclienteremisionesseleccionadas(data){
  await retrasoremisionesseleccionadas();
  $("#tabladetallesfactura tbody").append(data.filasremisiones);
  //array de remisiones seleccionadas
  construirarrayremisionesseleccionadas();
  //comprobar numero de filas en la tabla
  comprobarfilas();
  //calcular totales
  calculartotal();
  contadorfilas = data.contadorfilas;
  contadorproductos = data.contadorproductos;
  partida = data.partida;
  remisionagregadacorrectamente();
  mostrarformulario();
  //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
  $(".inputnextdet").keyup(function (e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    var index = $(this).index(".inputnextdet");
    switch(code){
      case 13:
        //$(".inputnextdet").eq(index + 1).focus().select();
        break;
      case 39:
        $(".inputnextdet").eq(index + 1).focus().select();
        break;
      case 37:
        $(".inputnextdet").eq(index - 1).focus().select();
        break;
    }
  });
  //colocar o no dataparsleyutilidad segun la configuracion de la empresa
  if(validarutilidadnegativa == 'S'){
      $(".utilidadpartida").removeAttr('data-parsley-utilidad');
      $("#utilidad").removeAttr('data-parsley-decimalesconfigurados');
  }else{
      $(".utilidadpartida").attr('data-parsley-utilidad', "0."+numerocerosconfiguradosinputnumberstep );
      $("#utilidad").attr('data-parsley-decimalesconfigurados', '/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/');
  }
  $('.page-loader-wrapper').css('display', 'none');
}
//listar todas las facturas
function listarordenes(){
  ocultarformulario();
  var tablaordenes ='<div class="modal-header '+background_forms_and_modals+'">'+
                          '<h4 class="modal-title">Facturar Servicios</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                        '<div class="row">'+
                          '<div class="col-md-12">'+
                            '<div class="table-responsive">'+
                              '<table id="tbllistadoorden" class="tbllistadoorden table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                '<thead class="'+background_tables+'">'+
                                  '<tr>'+
                                    '<th>Orden</th>'+
                                    '<th>Fecha</th>'+
                                    '<th>Cliente</th>'+
                                    '<th>Nombre</th>'+
                                    '<th>Por Facturar $</th>'+
                                    '<th>Total Orden</th>'+
                                    '<th>Tipo</th>'+
                                    '<th>Selecciona</th>'+
                                  '</tr>'+
                                '</thead>'+
                                '<tbody></tbody>'+
                              '</table>'+
                            '</div>'+
                          '</div>'+
                        '</div>'+
                        '<div class="row">'+
                          '<div class="col-md-6 col-md-offset-4">'+
                          '</div>'+
                          '<div class="col-md-2">'+
                            '<label>Total $</label>'+
                            '<input type="text" class="form-control divorinputmodmd" name="totalafacturarAux" id="totalafacturarAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                            '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalafacturar" id="totalafacturar" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                          '</div>'+
                        '</div>'+
                      '</div>'+
                      '<div class="modal-footer">'+
                        '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                        '<button type="button" class="btn btn-success btn-sm" onclick="cargarserviciosseleccionadas();">Cargar servicios</button>'+
                      '</div>';
    $("#contenidomodaltablas").html(tablaordenes);
    $('#tbllistadoorden').DataTable({
        keys: true,
        //"searching": false,
        //"paging":   false,
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
          url: facturas_obtener_ordenes,
          data: function (d) {
              d.numerocliente = $("#numerocliente").val();
              d.stringordenesseleccionadas = $("#stringordenesseleccionadas").val();
          }
        },
        columns: [
            { data: 'Orden', name: 'Orden' },
            { data: 'Fecha', name: 'Fecha' },
            { data: 'Cliente', name: 'Cliente', orderable: false, searchable: false },
            { data: 'NombreCliente', name: 'NombreCliente', orderable: false, searchable: false },
            { data: 'Facturar', name: 'Facturar', orderable: false, searchable: false },
            { data: 'Total', name: 'Total', orderable: false, searchable: false },
            { data: 'Tipo', name: 'Tipo', orderable: false, searchable: false },
            { data: 'Selecciona', name: 'Selecciona', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.focus();
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoorden').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 1000,
        "order": [[ 0, "desc" ]]
    });
}
//construir array ordenes seleccionadas
var arrayordenesseleccionadas = new Array();
function construirarrayordenesseleccionadas(Orden){
  if( $("#idordenesseleccionadas"+Orden).prop('checked') ) {
    var stringordenesseleccionadas = $("#stringordenesseleccionadas").val();
    if(stringordenesseleccionadas != ""){
      arrayordenesseleccionadas = stringordenesseleccionadas.split(",");
    }
    var coincidencias = 0;
    if(arrayordenesseleccionadas.length > 0){
        for(var i = 0; i < arrayordenesseleccionadas.length; i++){
            if(Orden == arrayordenesseleccionadas[i]){
                coincidencias++;
            }
        }
    }
    if(coincidencias == 0){
      arrayordenesseleccionadas.push(Orden);
      $("#stringordenesseleccionadas").val(arrayordenesseleccionadas.sort());
    }
  }else{
    EliminarElementoArrayPorValor(arrayordenesseleccionadas,Orden);
    $("#stringordenesseleccionadas").val(arrayordenesseleccionadas.sort());
  }
}
var arrayclientesordenesseleccionadas = [];
//obtener todos los datos de la orden de compra seleccionada
function seleccionarorden(Orden,Cliente){
  if( $('#idordenesseleccionadas'+Orden).prop('checked') ) {
    var cantidadclientesordenesseleccionadas = arrayclientesordenesseleccionadas.length;
    if(cantidadclientesordenesseleccionadas == 0){
      arrayclientesordenesseleccionadas.push(Cliente);
    }else{
      var existeclienteenarray = arrayclientesordenesseleccionadas.indexOf(Cliente);
      if(existeclienteenarray == -1){
        alert("Solo se debe seleccionar un mismo cliente para facturación");
        $('#idordenesseleccionadas'+Orden).removeAttr('checked');
      }else{
        arrayclientesordenesseleccionadas.push(Cliente);
      }
    }
  }else{
    arrayclientesordenesseleccionadas.pop();
  }
  construirarrayordenesseleccionadas(Orden);
  calculartotalafacturar("SERVICIOS");
}
//cargar servicios
function cargarserviciosseleccionadas(){
  contadorfilas = 0;
  partida = 1;
  contadorproductos = 0;
  $("#tabladetallesfactura tbody").html("");
  var tipooperacion = $("#tipooperacion").val();
  var stringordenesseleccionadas = $("#stringordenesseleccionadas").val();
  $.get(facturas_obtener_orden, {stringordenesseleccionadas:stringordenesseleccionadas, contadorfilas:contadorfilas, partida:partida, contadorproductos:contadorproductos, tipooperacion:tipooperacion}, function(data){
    $("#tabladetallesfactura tbody").append(data.filasordenes);
    //colocar pedido
    $("#pedido").val(data.pedido);
    //array de ordenes seleccionadas
    construirarrayordenesseleccionadas();
    //comprobar numero de filas en la tabla
    comprobarfilas();
    //calcular totales compras nota proveedor
    calculartotal();
    contadorfilas = data.contadorfilas;
    partida = data.partida;
    contadorproductos = data.contadorproductos;
    ordenagregadacorrectamente();
    mostrarformulario();
    //cargar datos cliente
    $("#numerocliente").val(data.cliente);
    $("#numeroclienteanterior").val("");
    obtenerclienteserviciosseleccionados();
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnextdet").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnextdet");
      switch(code){
        case 13:
          //$(".inputnextdet").eq(index + 1).focus().select();
          break;
        case 39:
          $(".inputnextdet").eq(index + 1).focus().select();
          break;
        case 37:
          $(".inputnextdet").eq(index - 1).focus().select();
          break;
      }
    });
    //colocar o no dataparsleyutilidad segun la configuracion de la empresa
    if(validarutilidadnegativa == 'S'){
        $(".utilidadpartida").removeAttr('data-parsley-utilidad');
        $("#utilidad").removeAttr('data-parsley-decimalesconfigurados');
    }else{
        $(".utilidadpartida").attr('data-parsley-utilidad', "0."+numerocerosconfiguradosinputnumberstep );
        $("#utilidad").attr('data-parsley-decimalesconfigurados', '/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/');
    }
    //ocultar input para agregar servicios
    $("#btnobtenerservicios").hide();
    $("#codigoservicioabuscar").hide();
  })
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
  $("#codigogastoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      listarproductosgastos();
    }
  });
  $("#codigoservicioabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      listarservicios();
    }
  });
});
//obtener por numero
function obtenerclientepornumero(){
  var numeroclienteanterior = $("#numeroclienteanterior").val();
  var numerocliente = $("#numerocliente").val();
  if(numeroclienteanterior != numerocliente){
    if($("#numerocliente").parsley().isValid()){
      $.get(facturas_obtener_cliente_por_numero, {numerocliente:numerocliente}, function(data){
          var confirmacion = true;
          if (confirmacion == true) {
              //validar si el RFC del cliente es igual al de la empresa si es asi la seria de la factura debe ser con el depto INTERNA
              var emisorrfc = $("#emisorrfc").val();
              if(emisorrfc == data.rfc){
                  $.get(facturas_obtener_serie_interna, function(datos){
                    $("#folio").val(datos.Folio);
                    $("#serie").val(datos.Serie);
                    $("#esquema").val(datos.Esquema);
                    $("#depto").val(datos.Depto);
                    $("#serietexto").html("Serie: "+datos.Serie);
                    $("#esquematexto").html("Esquema: "+datos.Esquema);
                  })
              }
              //Adenda
              if(data.numero== 22 && data.nombre == 'SEGUROS INBURSA, S.A., GRUPO FINANCIERO INBURSA'){
                $('#afectadoAdenda').attr("disabled", false);
                $('#emisorSiniestroAdenda').attr("disabled", false);
                $('#numeroSiniestroAdenda').attr("disabled", false);

                $('#afectadoAdenda').attr("required", true);
                $('#emisorSiniestroAdenda').attr("required", true);
                $('#numeroSiniestroAdenda').attr("required", true);
              }else{
                $('#afectadoAdenda').attr("disabled", true);
                $('#emisorSiniestroAdenda').attr("disabled", true);
                $('#numeroSiniestroAdenda').attr("disabled", true);

                $('#emisorSiniestroAdenda').attr("required", false);
                $('#numeroSiniestroAdenda').attr("required", false);
                $('#afectadoAdenda').attr("required", false);
              }
            $("#tabladetallesfactura tbody").html("");
            $("#numerocliente").val(data.numero);
            $("#numeroclienteanterior").val(data.numero);
            $("#cliente").val(data.nombre);
            if(data.nombre != null){
              $("#textonombrecliente").html(data.nombre.substring(0, 40));
            }
            $("#rfccliente").val(data.rfc);
            if(data.rfc == 'XAXX010101000'){
              $("#divperiodicidad").show();
              $("#divmes").show();
              $("#claveperiodicidad").attr('required', 'required');
              $("#claveperiodicidadanterior").attr('required', 'required');
              $("#periodicidad").attr('required', 'required');
              $("#clavemes").attr('required', 'required');
              $("#clavemesanterior").attr('required', 'required');
              $("#mes").attr('required', 'required');
            }else{
              $("#divperiodicidad").hide();
              $("#divmes").hide();
              $("#claveperiodicidad").removeAttr('required');
              $("#claveperiodicidadanterior").removeAttr('required');
              $("#periodicidad").removeAttr('required');
              $("#clavemes").removeAttr('required');
              $("#clavemesanterior").removeAttr('required');
              $("#mes").removeAttr('required');
            }
            $("#plazo").val(data.plazo);
            //credito y saldo
            $("#credito").val(number_format(round(data.credito, numerodecimales), numerodecimales, '.', ''));
            $("#saldo").val(number_format(round(data.saldo, numerodecimales), numerodecimales, '.', ''));

            $("#creditoAux").val(number_format(round(data.credito, numerodecimales), numerodecimales, '.', ','));
            $("#saldoAux").val(number_format(round(data.saldo, numerodecimales), numerodecimales, '.', ','));
            //datos pestaña receptor o cliente
            $("#receptorrfc").val(data.rfc);
            $("#receptornombre").val(data.nombre);
            $("#claveformapago").val(data.claveformapago);
            $("#claveformapagoanterior").val(data.claveformapago);
            $("#formapago").val(data.formapago);
            if(data.formapago != null){
              $("#textonombreformapago").html(data.formapago.substring(0, 40));
            }
            $("#clavemetodopago").val(data.clavemetodopago);
            $("#clavemetodopagoanterior").val(data.clavemetodopago);
            $("#metodopago").val(data.metodopago);
            if(data.metodopago != null){
              $("#textonombremetodopago").html(data.metodopago.substring(0, 40));
            }
            $("#claveusocfdi").val(data.claveusocfdi);
            $("#claveusocfdianterior").val(data.claveusocfdi);
            $("#usocfdi").val(data.usocfdi);
            if(data.usocfdi != null){
              $("#textonombreusocfdi").html(data.usocfdi.substring(0, 40));
            }
            $("#claveresidenciafiscal").val(data.claveresidenciafiscal);
            $("#claveresidenciafiscalanterior").val(data.claveresidenciafiscal);
            $("#residenciafiscal").val(data.residenciafiscal);
            if(data.residenciafiscal != null){
              $("#textonombreresidenciafiscal").html(data.residenciafiscal.substring(0, 40));
            }
            //regimen fiscal
            $("#claveregimenfiscalreceptor").val(data.claveregimenfiscalreceptor);
            $("#claveregimenfiscalreceptoranterior").val(data.claveregimenfiscalreceptor);
            $("#regimenfiscalreceptor").val(data.regimenfiscalreceptor);
            if(data.regimenfiscalreceptor != null){
                $("#textonombreregimenfiscalreceptor").html(data.regimenfiscalreceptor.substring(0, 40));
            }
            //datos agente
            $("#numeroagente").val(data.numeroagente);
            $("#numeroagenteanterior").val(data.numeroagente);
            $("#rfcagente").val(data.rfcagente);
            $("#agente").val(data.nombreagente);
            if(data.nombreagente != null){
              $("#textonombreagente").html(data.nombreagente.substring(0, 40));
            }
            //comprobar si mostrar botones
            var Depto = $("#depto").val();
            comprobartiposerie(Depto);
            //comprobar numero de filas en la tabla
            comprobarfilas();
            //calcular totales compras nota proveedor
            calculartotal();
            //colocar strings vacios
            $("#stringremisionesseleccionadas").val("");
            $("#stringordenesseleccionadas").val("");
            contadorproductos = 0;
            contadorfilas = 0;
            partida = 1;
            mostrarformulario();
          }
      })
    }
  }
}
//obtener cliente de las remisiones seleccionadas
function obtenerclienteremisionesseleccionadas(){
  var numeroclienteanterior = $("#numeroclienteanterior").val();
  var numerocliente = $("#numerocliente").val();
  if(numeroclienteanterior != numerocliente){
    if($("#numerocliente").parsley().isValid()){
      $.get(facturas_obtener_cliente_por_numero, {numerocliente:numerocliente}, function(data){
          var confirmacion = true;
          if (confirmacion == true) {
              //validar si el RFC del cliente es igual al de la empresa si es asi la seria de la factura debe ser con el depto INTERNA
              var emisorrfc = $("#emisorrfc").val();
              if(emisorrfc == data.rfc){

              }
            $("#numerocliente").val(data.numero);
            $("#numeroclienteanterior").val(data.numero);
            $("#cliente").val(data.nombre);
            if(data.nombre != null){
              $("#textonombrecliente").html(data.nombre.substring(0, 40));
            }
            //Adenda
            if(data.cliente.Numero== 22 && data.cliente.Nombre == 'SEGUROS INBURSA, S.A., GRUPO FINANCIERO INBURSA'){
                $('#afectadoAdenda').attr("disabled", false);
                $('#emisorSiniestroAdenda').attr("disabled", false);
                $('#numeroSiniestroAdenda').attr("disabled", false);

                $('#afectadoAdenda').attr("required", true);
                $('#emisorSiniestroAdenda').attr("required", true);
                $('#numeroSiniestroAdenda').attr("required", true);
              }else{
                $('#afectadoAdenda').attr("disabled", true);
                $('#emisorSiniestroAdenda').attr("disabled", true);
                $('#numeroSiniestroAdenda').attr("disabled", true);

                $('#emisorSiniestroAdenda').attr("required", false);
                $('#numeroSiniestroAdenda').attr("required", false);
                $('#afectadoAdenda').attr("required", false);
              }
            $("#rfccliente").val(data.rfc);
            $("#plazo").val(data.plazo);
            //credito y saldo
            $("#credito").val(number_format(round(data.credito, numerodecimales), numerodecimales, '.', ''));
            $("#saldo").val(number_format(round(data.saldo, numerodecimales), numerodecimales, '.', ''));
            //datos pestaña receptor o cliente
            $("#receptorrfc").val(data.rfc);
            $("#receptornombre").val(data.nombre);
            $("#claveformapago").val(data.claveformapago);
            $("#claveformapagoanterior").val(data.claveformapago);
            $("#formapago").val(data.formapago);
            if(data.formapago != null){
              $("#textonombreformapago").html(data.formapago.substring(0, 40));
            }
            $("#clavemetodopago").val(data.clavemetodopago);
            $("#clavemetodopagoanterior").val(data.clavemetodopago);
            $("#metodopago").val(data.metodopago);
            if(data.metodopago != null){
              $("#textonombremetodopago").html(data.metodopago.substring(0, 40));
            }
            $("#claveusocfdi").val(data.claveusocfdi);
            $("#claveusocfdianterior").val(data.claveusocfdi);
            $("#usocfdi").val(data.usocfdi);
            if(data.usocfdi != null){
              $("#textonombreusocfdi").html(data.usocfdi.substring(0, 40));
            }
            $("#claveresidenciafiscal").val(data.claveresidenciafiscal);
            $("#claveresidenciafiscalanterior").val(data.claveresidenciafiscal);
            $("#residenciafiscal").val(data.residenciafiscal);
            if(data.residenciafiscal != null){
              $("#textonombreresidenciafiscal").html(data.residenciafiscal.substring(0, 40));
            }
            //datos agente
            $("#numeroagente").val(data.numeroagente);
            $("#numeroagenteanterior").val(data.numeroagente);
            $("#rfcagente").val(data.rfcagente);
            $("#agente").val(data.nombreagente);
            if(data.nombreagente != null){
              $("#textonombreagente").html(data.nombreagente.substring(0, 40));
            }
            //comprobar si mostrar botones
            var Depto = $("#depto").val();
            comprobartiposerie(Depto);
            mostrarformulario();
          }
      })
    }
  }
}
//obtener cliente de los servicios seleccionados
function obtenerclienteserviciosseleccionados(){
  var numeroclienteanterior = $("#numeroclienteanterior").val();
  var numerocliente = $("#numerocliente").val();
  if(numeroclienteanterior != numerocliente){
    if($("#numerocliente").parsley().isValid()){
      $.get(facturas_obtener_cliente_por_numero, {numerocliente:numerocliente}, function(data){
          var confirmacion = true;
          if (confirmacion == true) {
              //validar si el RFC del cliente es igual al de la empresa si es asi la seria de la factura debe ser con el depto INTERNA
              var emisorrfc = $("#emisorrfc").val();
              if(emisorrfc == data.rfc){
                  $.get(facturas_obtener_serie_interna, function(datos){
                    $("#folio").val(datos.Folio);
                    $("#serie").val(datos.Serie);
                    $("#esquema").val(datos.Esquema);
                    $("#depto").val(datos.Depto);
                    $("#serietexto").html("Serie: "+datos.Serie);
                    $("#esquematexto").html("Esquema: "+datos.Esquema);
                  })
              }
            $("#numerocliente").val(data.numero);
            $("#numeroclienteanterior").val(data.numero);
            $("#cliente").val(data.nombre);
            if(data.nombre != null){
              $("#textonombrecliente").html(data.nombre.substring(0, 40));
            }
            //Adenda
            if(data.numero== 22 && data.nombre == 'SEGUROS INBURSA, S.A., GRUPO FINANCIERO INBURSA'){
                $('#afectadoAdenda').attr("disabled", false);
                $('#emisorSiniestroAdenda').attr("disabled", false);
                $('#numeroSiniestroAdenda').attr("disabled", false);

                $('#afectadoAdenda').attr("required", true);
                $('#emisorSiniestroAdenda').attr("required", true);
                $('#numeroSiniestroAdenda').attr("required", true);
              }else{
                $('#afectadoAdenda').attr("disabled", true);
                $('#emisorSiniestroAdenda').attr("disabled", true);
                $('#numeroSiniestroAdenda').attr("disabled", true);

                $('#emisorSiniestroAdenda').attr("required", false);
                $('#numeroSiniestroAdenda').attr("required", false);
                $('#afectadoAdenda').attr("required", false);
            }
            $("#rfccliente").val(data.rfc);
            $("#plazo").val(data.plazo);
            //credito y saldo
            $("#credito").val(number_format(round(data.credito, numerodecimales), numerodecimales, '.', ''));
            $("#saldo").val(number_format(round(data.saldo, numerodecimales), numerodecimales, '.', ''));
            //datos pestaña receptor o cliente
            $("#receptorrfc").val(data.rfc);
            $("#receptornombre").val(data.nombre);
            $("#claveformapago").val(data.claveformapago);
            $("#claveformapagoanterior").val(data.claveformapago);
            $("#formapago").val(data.formapago);
            if(data.formapago != null){
              $("#textonombreformapago").html(data.formapago.substring(0, 40));
            }
            $("#clavemetodopago").val(data.clavemetodopago);
            $("#clavemetodopagoanterior").val(data.clavemetodopago);
            $("#metodopago").val(data.metodopago);
            if(data.metodopago != null){
              $("#textonombremetodopago").html(data.metodopago.substring(0, 40));
            }
            $("#claveusocfdi").val(data.claveusocfdi);
            $("#claveusocfdianterior").val(data.claveusocfdi);
            $("#usocfdi").val(data.usocfdi);
            if(data.usocfdi != null){
              $("#textonombreusocfdi").html(data.usocfdi.substring(0, 40));
            }
            $("#claveresidenciafiscal").val(data.claveresidenciafiscal);
            $("#claveresidenciafiscalanterior").val(data.claveresidenciafiscal);
            $("#residenciafiscal").val(data.residenciafiscal);
            if(data.residenciafiscal != null){
              $("#textonombreresidenciafiscal").html(data.residenciafiscal.substring(0, 40));
            }
            //datos agente
            $("#numeroagente").val(data.numeroagente);
            $("#numeroagenteanterior").val(data.numeroagente);
            $("#rfcagente").val(data.rfcagente);
            $("#agente").val(data.nombreagente);
            if(data.nombreagente != null){
              $("#textonombreagente").html(data.nombreagente.substring(0, 40));
            }
            //comprobar si mostrar botones
            var Depto = $("#depto").val();
            comprobartiposerie(Depto);
            mostrarformulario();
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
      $.get(facturas_obtener_agente_por_numero, {numeroagente:numeroagente}, function(data){
          $("#numeroagente").val(data.numero);
          $("#numeroagenteanterior").val(data.numero);
          $("#rfcagente").val(data.rfc);
          $("#agente").val(data.nombre);
          if(data.nombre != null){
            $("#textonombreagente").html(data.nombre.substring(0, 40));
          }
          mostrarformulario();
      })
    }
  }
}
//regresar numero
function regresarnumeroagente(){
  var numeroagenteanterior = $("#numeroagenteanterior").val();
  $("#numeroagente").val(numeroagenteanterior);
}
//obtener por clave
function obtenerlugarexpedicionporclave(){
  var lugarexpedicionanterior = $("#lugarexpedicionanterior").val();
  var lugarexpedicion = $("#lugarexpedicion").val();
  if(lugarexpedicionanterior != lugarexpedicion){
    if($("#lugarexpedicion").parsley().isValid()){
      $.get(facturas_obtener_lugar_expedicion_por_clave, {lugarexpedicion:lugarexpedicion}, function(data){
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
      $.get(facturas_obtener_regimen_fiscal_por_clave, {claveregimenfiscal:claveregimenfiscal}, function(data){
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
      $.get(facturas_obtener_tipo_relacion_por_clave, {clavetiporelacion:clavetiporelacion}, function(data){
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
      $.get(facturas_obtener_forma_pago_por_clave, {claveformapago:claveformapago}, function(data){
        $("#claveformapago").val(data.clave);
        $("#claveformapagoanterior").val(data.clave);
        $("#formapago").val(data.nombre);
        if(data.nombre != null){
          $("#textonombreformapago").html(data.nombre.substring(0, 40));

        }
        if(data.clave == '99'){
          seleccionarmetodopago('PPD','Pago en parcialidades o diferido');
        }else{
          seleccionarmetodopago('PUE','Pago en una sola exhibición');
        }
        $('#clavemetodopago').focus()
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
//obtener por clave
function obtenermetodopagoporclave(){
  var clavemetodopagoanterior = $("#clavemetodopagoanterior").val();
  var clavemetodopago = $("#clavemetodopago").val();
  if(clavemetodopagoanterior != clavemetodopago){
    if($("#clavemetodopago").parsley().isValid()){
      $.get(facturas_obtener_metodo_pago_por_clave, {clavemetodopago:clavemetodopago}, function(data){
        $("#clavemetodopago").val(data.clave);
        $("#clavemetodopagoanterior").val(data.clave);
        $("#metodopago").val(data.nombre);
        if(data.nombre != null){
          $("#textonombremetodopago").html(data.nombre.substring(0, 40));
        }
        mostrarformulario();
      })
    }
    $('#claveusocfdi').focus()
  }
  $('#claveusocfdi').focus()
}
//regresar clave
function regresarclavemetodopago(){
  var clavemetodopagoanterior = $("#clavemetodopagoanterior").val();
  $("#clavemetodopago").val(clavemetodopagoanterior);
}
//obtener por clave
function obtenerusocfdiporclave(){
  var claveusocfdianterior = $("#claveusocfdianterior").val();
  var claveusocfdi = $("#claveusocfdi").val();
  if(claveusocfdianterior != claveusocfdi){
    if($("#claveusocfdi").parsley().isValid()){
      $.get(facturas_obtener_uso_cfdi_por_clave, {claveusocfdi:claveusocfdi}, function(data){
        $("#claveusocfdi").val(data.clave);
        $("#claveusocfdianterior").val(data.clave);
        $("#usocfdi").val(data.nombre);
        if(data.nombre != null){
          $("#textonombreusocfdi").html(data.nombre.substring(0, 40));
        }
        mostrarformulario();
        $('#claveregimenfiscalreceptor').focus()
      })
    }
  }else{
    $('#claveregimenfiscalreceptor').focus()
  }
}
//regresar clave
function regresarclaveusocfdi(){
  var claveusocfdianterior = $("#claveusocfdianterior").val();
  $("#claveusocfdi").val(claveusocfdianterior);
}
//obtener por clave
function obtenerresidenciafiscalporclave(){
  var claveresidenciafiscalanterior = $("#claveresidenciafiscalanterior").val();
  var claveresidenciafiscal = $("#claveresidenciafiscal").val();
  if(claveresidenciafiscalanterior != claveresidenciafiscal){
    if($("#claveresidenciafiscal").parsley().isValid()){
      $.get(facturas_obtener_residencia_fiscal_por_clave, {claveresidenciafiscal:claveresidenciafiscal}, function(data){
        $("#claveresidenciafiscal").val(data.clave);
        $("#claveresidenciafiscalanterior").val(data.clave);
        $("#residenciafiscal").val(data.nombre);
        if(data.nombre != null){
          $("#textonombreresidenciafiscal").html(data.nombre.substring(0, 40));
        }
        mostrarformulario();
      })
    }
  }
}
//regresar clave
function regresarclaveresidenciafiscal(){
  var claveresidenciafiscalanterior = $("#claveresidenciafiscalanterior").val();
  $("#claveresidenciafiscal").val(claveresidenciafiscalanterior);
}
//obtener regimenes fiscales
function obtenerregimenesfiscalesreceptor(){
  ocultarformulario();
  var tablaregimenesfiscales ='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Regimenes Fiscales</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoregimenfiscalreceptor" class="tbllistadoregimenfiscalreceptor table table-bordered table-striped table-hover" style="width:100% !important;">'+
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
  var tregfis = $('#tbllistadoregimenfiscalreceptor').DataTable({
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
        url: facturas_obtener_regimenes_fiscales_receptor
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
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoregimenfiscalreceptor').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoregimenfiscalreceptor tbody').on('dblclick', 'tr', function () {
      var data = tregfis.row( this ).data();
      seleccionarregimenfiscalreceptor(data.Clave, data.Nombre);
      $('#condicionesdepago').focus()
  });
}
//seleccionar lugar expedicion
function seleccionarregimenfiscalreceptor(Clave, Nombre){
  var claveregimenfiscalreceptoranterior = $("#claveregimenfiscalreceptoranterior").val();
  var claveregimenfiscalreceptor = Clave;
  if(claveregimenfiscalreceptoranterior != claveregimenfiscalreceptor){
    $('#condicionesdepago').focus()
    $("#claveregimenfiscalreceptor").val(Clave);
    $("#claveregimenfiscalreceptoranterior").val(Clave);
    $("#regimenfiscalreceptor").val(Nombre);
    if(Nombre != null){
      $("#textonombreregimenfiscalreceptor").html(Nombre.substring(0, 15));
    }
    $('#condicionesdepago').focus()
    mostrarformulario();
  }else{
    $('#condicionesdepago').focus()
  }
}
//obtener por clave
function obtenerregimenfiscalreceptorporclave(){
  var claveregimenfiscalreceptoranterior = $("#claveregimenfiscalreceptoranterior").val();
  var claveregimenfiscalreceptor = $("#claveregimenfiscalreceptor").val();
  if(claveregimenfiscalreceptoranterior != claveregimenfiscalreceptor){
    if($("#claveregimenfiscalreceptor").parsley().isValid()){
      $.get(facturas_obtener_regimenfiscalreceptor_por_clave, {claveregimenfiscalreceptor:claveregimenfiscalreceptor}, function(data){
        //$('#condicionesdepago').focus()
        $("#claveregimenfiscalreceptor").val(data.clave);
        $("#claveregimenfiscalreceptoranterior").val(data.clave);
        $("#regimenfiscalreceptor").val(data.nombre);
        if(data.nombre != null){
          $("#textonombreregimenfiscalreceptor").html(data.nombre.substring(0, 15));
          $('#condicionesdepago').focus()
        }
        mostrarformulario();
      })
    }else{
        //$('#condicionesdepago').focus()
    }
  }
}
//regresar clave
function regresarclaveregimenfiscalreceptor(){
  var claveregimenfiscalreceptoranterior = $("#claveregimenfiscalreceptoranterior").val();
  $("#claveregimenfiscalreceptor").val(claveregimenfiscalreceptoranterior);
}
//obtener peridicidades
function obtenerperiodicidades(){
  ocultarformulario();
  var tablaperidicidades ='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Periodicidades</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoperiodicidad" class="tbllistadoperiodicidad table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Numero</th>'+
                                                        '<th>Clave</th>'+
                                                        '<th>Descripcion</th>'+
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
  $("#contenidomodaltablas").html(tablaperidicidades);
  var listadotablaperiodicidades = $('#tbllistadoperiodicidad').DataTable({
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
        url: facturas_obtener_periodicidades
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Numero', name: 'Numero' },
          { data: 'Clave', name: 'Clave', orderable: false, searchable: false},
          { data: 'Descripcion', name: 'Descripcion', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadoperiodicidad').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoperiodicidad tbody').on('dblclick', 'tr', function () {
      var data = listadotablaperiodicidades.row( this ).data();
      seleccionarperiodicidad(data.Clave, data.Descripcion);
  });
}
//seleccionar periodicidad
function seleccionarperiodicidad(Clave, Descripcion){
  var claveperiodicidadanterior = $("#claveperiodicidadanterior").val();
  var claveperiodicidad = Clave;
  if(claveperiodicidadanterior != claveperiodicidad){
    $("#claveperiodicidad").val(Clave);
    $("#claveperiodicidadanterior").val(Clave);
    $("#periodicidad").val(Descripcion);
    if(Descripcion != null){
      $("#textonombreperiodicidad").html(Descripcion.substring(0, 15));
    }
    mostrarformulario();
  }
}
//obtener por clave
function obtenerperiodicidadporclave(){
  var claveperiodicidadanterior = $("#claveperiodicidadanterior").val();
  var claveperiodicidad = $("#claveperiodicidad").val();
  if(claveperiodicidadanterior != claveperiodicidad){
    if($("#claveperiodicidad").parsley().isValid()){
      $.get(facturas_obtener_periodicidad_por_clave, {claveperiodicidad:claveperiodicidad}, function(data){
        $("#claveperiodicidad").val(data.clave);
        $("#claveperiodicidadanterior").val(data.clave);
        $("#periodicidad").val(data.descripcion);
        if(data.descripcion != null){
          $("#textonombreperiodicidad").html(data.descripcion.substring(0, 15));
        }
        mostrarformulario();
      })
    }
  }
}
//regresar clave
function regresarclaveperiodicidad(){
  var claveperiodicidadanterior = $("#claveperiodicidadanterior").val();
  $("#claveperiodicidad").val(claveperiodicidadanterior);
}



//obtener meses
function obtenermeses(){
  ocultarformulario();
  var tablameses ='<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Meses</h4>'+
                              '</div>'+
                              '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadomes" class="tbllistadomes table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Numero</th>'+
                                                        '<th>Clave</th>'+
                                                        '<th>Descripcion</th>'+
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
  $("#contenidomodaltablas").html(tablameses);
  var listadotablameses = $('#tbllistadomes').DataTable({
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
        url: facturas_obtener_meses
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Numero', name: 'Numero' },
          { data: 'Clave', name: 'Clave', orderable: false, searchable: false},
          { data: 'Descripcion', name: 'Descripcion', orderable: false, searchable: false}
      ],
      "initComplete": function() {
        var $buscar = $('div.dataTables_filter input');
        $buscar.focus();
        $buscar.unbind();
        $buscar.bind('keyup change', function(e) {
            if(e.keyCode == 13 || this.value == "") {
              $('#tbllistadomes').DataTable().search( this.value ).draw();
            }
        });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadomes tbody').on('dblclick', 'tr', function () {
      var data = listadotablameses.row( this ).data();
      seleccionarmes(data.Clave, data.Descripcion);
  });
}
//seleccionar mes
function seleccionarmes(Clave, Descripcion){
  var clavemesanterior = $("#clavemesanterior").val();
  var clavemes = Clave;
  if(clavemesanterior != clavemes){
    $("#clavemes").val(Clave);
    $("#clavemesanterior").val(Clave);
    $("#mes").val(Descripcion);
    if(Descripcion != null){
      $("#textonombremes").html(Descripcion.substring(0, 15));
    }
    mostrarformulario();
  }
}
//obtener por clave
function obtenermesporclave(){
  var clavemesanterior = $("#clavemesanterior").val();
  var clavemes = $("#clavemes").val();
  if(clavemesanterior != clavemes){
    if($("#clavemes").parsley().isValid()){
      $.get(facturas_obtener_mes_por_clave, {clavemes:clavemes}, function(data){
        $("#clavemes").val(data.clave);
        $("#clavemesanterior").val(data.clave);
        $("#mes").val(data.descripcion);
        if(data.descripcion != null){
          $("#textonombremes").html(data.descripcion.substring(0, 15));
        }
        mostrarformulario();
      })
    }
  }
}
//regresar clave
function regresarclavemes(){
  var clavemesanterior = $("#clavemesanterior").val();
  $("#clavemes").val(clavemesanterior);
}




//listar productos para tab consumos
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
      url: facturas_obtener_productos,
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
      agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, number_format(round(data.Impuesto, numerodecimales), numerodecimales, '.', ''), data.SubTotal, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, data.NombreClaveProducto, data.NombreClaveUnidad, number_format(round(data.CostoDeLista, numerodecimales), numerodecimales, '.', ''));
  });
}
function obtenerproductoporcodigo(){
  var codigoabuscar = $("#codigoabuscar").val();
  var tipooperacion = $("#tipooperacion").val();
  $.get(facturas_obtener_producto_por_codigo,{codigoabuscar:codigoabuscar}, function(data){
    if(parseInt(data.contarproductos) > 0){
      agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Impuesto, data.SubTotal, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, data.NombreClaveProducto, data.NombreClaveUnidad, data.CostoDeLista);
    }else{
      msjnoseencontroningunproducto();
    }
  })
}
//listar productos para tab consumos
function listarproductosgastos(){
  ocultarformulario();
  var tablaproductosgastos ='<div class="modal-header '+background_forms_and_modals+'">'+
                        '<h4 class="modal-title">Productos Gastos</h4>'+
                      '</div>'+
                      '<div class="modal-body">'+
                        '<div class="row">'+
                          '<div class="col-md-12">'+
                            '<div class="table-responsive">'+
                              '<table id="tbllistadoproductogasto" class="tbllistadoproductogasto table table-bordered table-striped table-hover" style="width:100% !important">'+
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
  $("#contenidomodaltablas").html(tablaproductosgastos);
  var tprodgast = $('#tbllistadoproductogasto').DataTable({
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
      url: facturas_obtener_productos_gastos,
      data: function (d) {
        d.codigogastoabuscar = $("#codigogastoabuscar").val();
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
          $('#tbllistadoproductogasto').DataTable().search( this.value ).draw();
        }
      });
    },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoproductogasto tbody').on('dblclick', 'tr', function () {
      var data = tprodgast.row( this ).data();
      var tipooperacion = $("#tipooperacion").val();
      agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, number_format(round(data.Impuesto, numerodecimales), numerodecimales, '.', ''), data.SubTotal, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, data.NombreClaveProducto, data.NombreClaveUnidad, number_format(round(data.CostoDeLista, numerodecimales), numerodecimales, '.', ''));
  });
}
function obtenerproductogastoporcodigo(){
  var codigogastoabuscar = $("#codigogastoabuscar").val();
  var tipooperacion = $("#tipooperacion").val();
  $.get(facturas_obtener_producto_gasto_por_codigo,{codigogastoabuscar:codigogastoabuscar}, function(data){
    if(parseInt(data.contarproductos) > 0){
      agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, data.Impuesto, data.SubTotal, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, data.NombreClaveProducto, data.NombreClaveUnidad, data.CostoDeLista);
    }else{
      msjnoseencontroningunproducto();
    }
  })
}
//listarserv
function listarservicios(){
  ocultarformulario();
  var tablaservicios = '<div class="modal-header '+background_forms_and_modals+'">'+
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
                                      '<th>Cantidad</th>'+
                                      '<th>SubTotal</th>'+
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
  var tserv = $('#tbllistadoservicio').DataTable({
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
      url: facturas_obtener_servicios,
      data: function (d) {
        d.codigoservicioabuscar = $("#codigoservicioabuscar").val();
        d.tipooperacion = $("#tipooperacion").val();
      }
    },
    columns: [
      { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false  },
      { data: 'Codigo', name: 'Codigo' },
      { data: 'Servicio', name: 'Servicio'  },
      { data: 'Cantidad', name: 'Cantidad', orderable: false, searchable: false  },
      { data: 'Venta', name: 'Venta', orderable: false, searchable: false  }
    ],
    "initComplete": function() {
      var $buscar = $('div.dataTables_filter input');
      $buscar.focus();
      $buscar.unbind();
      $buscar.bind('keyup change', function(e) {
        if(e.keyCode == 13 || this.value == "") {
          $('#tbllistadoservicio').DataTable().search( this.value ).draw();
        }
      });
    },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoservicio tbody').on('dblclick', 'tr', function () {
      var data = tserv.row( this ).data();
      var tipooperacion = $("#tipooperacion").val();

      agregarfilaproducto(data.Codigo, data.Servicio, data.Unidad, number_format(round(data.Venta, numerodecimales), numerodecimales, '.', ''), number_format(round(16, numerodecimales), numerodecimales, '.', ''), number_format(round(data.Venta, numerodecimales), numerodecimales, '.', ''), tipooperacion, "", data.ClaveProducto, data.ClaveUnidad, data.NombreClaveProducto, data.NombreClaveUnidad, number_format(round(data.Venta, numerodecimales), numerodecimales, '.', ''), number_format(round(data.Cantidad, numerodecimales), numerodecimales, '.', ''));

      //agregarfilaservicio(data.Codigo, data.Servicio, data.Unidad, number_format(round(data.Costo, numerodecimales), numerodecimales, '.', ''), data.Venta, data.Cantidad, data.ClaveProducto, data.ClaveUnidad, tipooperacion);
  });
}
function obtenerservicioporcodigo(){
  var codigoservicioabuscar = $("#codigoservicioabuscar").val();
  var tipooperacion = $("#tipooperacion").val();
  $.get(facturas_obtener_servicio_por_codigo,{codigoservicioabuscar:codigoservicioabuscar}, function(data){
    if(parseInt(data.contarproductos) > 0){
      agregarfilaservicio(data.Codigo, data.Servicio, data.Unidad, data.Costo, data.Venta, data.Cantidad, data.ClaveProducto, data.ClaveUnidad, tipooperacion);
    }else{
      msjnoseencontroningunproducto();
    }
  })
}
//listar claves productos
function listarclavesproductos(fila){
  ocultarformulario();
  var tablaclavesproducto = '<div class="modal-header '+background_forms_and_modals+'">'+
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
  $("#contenidomodaltablas").html(tablaclavesproducto);
  var tclavprod = $('#tbllistadoclaveproducto').DataTable({
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
          url: facturas_obtener_claves_productos,
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
          $buscar.focus();
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoclaveproducto').DataTable().search( this.value ).draw();
              }
          });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoclaveproducto tbody').on('dblclick', 'tr', function () {
      var data = tclavprod.row( this ).data();
      seleccionarclaveproducto(data.Clave, data.Nombre, fila);
  });
}
//seleccion de clave producto
function seleccionarclaveproducto(clave, nombre, fila){
  $("#filaproducto"+fila+" .claveproductopartida").val(clave);
  $("#filaproducto"+fila+" .nombreclaveproductopartida").val(nombre);
  mostrarformulario();
}
//listar claves unidades
function listarclavesunidades(fila){
  ocultarformulario();
  var tablaclavesunidades = '<div class="modal-header '+background_forms_and_modals+'">'+
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
          url: facturas_obtener_claves_unidades,
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
          $buscar.focus();
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoclaveunidad').DataTable().search( this.value ).draw();
              }
          });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadoclaveunidad tbody').on('dblclick', 'tr', function () {
      var data = tclavuni.row( this ).data();
      seleccionarclaveunidad(data.Clave, data.Nombre, fila);
  });
}
//seleccion de clave unidad
function seleccionarclaveunidad(clave, nombre, fila){
  $("#filaproducto"+fila+" .claveunidadpartida").val(clave);
  $("#filaproducto"+fila+" .nombreclaveunidadpartida").val(nombre);
  mostrarformulario();
}
//listar facturas relacionadas
function listaruuidrelacionados(){
  ocultarformulario();
  var numerocliente = $("#numerocliente").val();
  var tablafacturasrel =    '<div class="modal-header '+background_forms_and_modals+'">'+
                                '<h4 class="modal-title">Comprobantes UUIDS</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadofacturarel" class="tbllistadofacturarel table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="'+background_tables+'">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>UUID</th>'+
                                                        '<th>Emisor</th>'+
                                                        '<th>Receptor</th>'+
                                                        '<th>Serie</th>'+
                                                        '<th>Folio</th>'+
                                                        '<th>Fecha</th>'+
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
  $("#contenidomodaltablas").html(tablafacturasrel);
  var tfacrel = $('#tbllistadofacturarel').DataTable({
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
          url: facturas_obtener_facturas_relacionadas,
          data: function (d) {
            d.numerocliente = numerocliente;
          }
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'UUID', name: 'UUID' },
          { data: 'EmisorRfc', name: 'EmisorRfc' },
          { data: 'ReceptorRfc', name: 'ReceptorRfc' },
          { data: 'Serie', name: 'Serie' },
          { data: 'Folio', name: 'Folio' },
          { data: 'Fecha', name: 'Fecha' },
          { data: 'Total', name: 'Total' }
      ],
      "initComplete": function() {
          var $buscar = $('div.dataTables_filter input');
          $buscar.focus();
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadofacturarel').DataTable().search( this.value ).draw();
              }
          });
      },
  });
  //seleccionar registro al dar doble click
  $('#tbllistadofacturarel tbody').on('dblclick', 'tr', function () {
      var data = tfacrel.row( this ).data();
      seleccionarfacturarel(data.UUID, data.Factura);
  });
}
function seleccionarfacturarel(UUID, Factura){
  var tipooperacion = $("#tipooperacion").val();
  var uuidrelacionado = '<tr class="filasuuid" id="filauuid0">'+
                                    '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminaruuid" onclick="eliminarfilauuid(0)">X</div><input type="hidden" class="form-control uuidagregadoen" name="uuidagregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm uuidrelacionado" name="uuidrelacionado[]" value="'+UUID+'" readonly>'+UUID+'</td>'+
                                '</tr>';
  $("#tablauuidrelacionados tbody").append(uuidrelacionado);
  renumerarfilasuuid();
  comprobarfilasuuid();
  mostrarformulario();
}
//función que evalua si la partida que quieren ingresar ya existe o no en el detalle de la orden de compra
function evaluarproductoexistente(Codigo){
  var sumaiguales=0;
  var sumadiferentes=0;
  var sumatotal=0;
  $("tr.filasproductos").each(function () {
      var codigoproducto = $('.codigopartida', this).val();
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
//agregar una fila en la tabla de precios productos codigo ó dppp
var contadorproductos=0;
var contadorfilas = 0;
var partida = 1;
function agregarfilaproducto(Codigo, Producto, Unidad, Costo, Impuesto, SubTotal, tipooperacion, Insumo, ClaveProducto, ClaveUnidad, NombreClaveProducto, NombreClaveUnidad, CostoDeLista, Cantidad){
    $('.page-loader-wrapper').css('display', 'block');
    var result = evaluarproductoexistente(Codigo);
    if(result == false){
        var multiplicacioncostoimpuesto =  new Decimal(SubTotal).times(Impuesto);
        var ivapesos = new Decimal(multiplicacioncostoimpuesto/100);
        var total = new Decimal(SubTotal).plus(ivapesos);
        var preciopartida = SubTotal;
        var utilidad = new Decimal(SubTotal).minus(Costo);
        var cantidad = '1.'+numerocerosconfigurados;
        var tipopartida = '';
        if(Cantidad != undefined){
          cantidad = Cantidad;
          var utilidad = preciopartida;
          tipopartida = 'SERV';
        }
        var tipo = "alta";
        var fila= '<tr class="filasproductos" id="filaproducto'+contadorfilas+'">'+
                    '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfilas" onclick="eliminarfila('+contadorproductos+')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                    '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'+Codigo+'</b></td>'+
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'+Producto+'" data-parsley-length="[1, 255]" autocomplete="off"></td>'+
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxs unidadpartida" name="unidadpartida[]" value="'+Unidad+'" data-parsley-length="[1, 5]" autocomplete="off"></td>'+
                    '<td class="tdmod">'+
                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'+cantidad+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');">'+
                        '<input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'+cantidad+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'+
                        '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'+
                        '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'+
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'+
                        '<input type="hidden" class="form-control tipopartida" name="tipopartida[]" value="'+tipopartida+'">'+
                    '</td>'+
                    '<td class="tdmod">'+
                        '<input type="text" class="form-control inputnextdet divorinputmodsm preciopartidaAux" name="preciopartidaAux[]" value="'+number_format(preciopartida, numerodecimales,'.',',')+'" onchange="formatocorrectoinputcantidadesComma(this);calculartotalesfilas('+contadorfilas+');">'+
                        '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'+preciopartida+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');">'+
                    '</td>'+
                    '<td class="tdmod">'+
                        '<input type="text" class="form-control divorinputmodsm importepartidaAux" name="importepartidaAux[]" value="'+number_format(preciopartida, numerodecimales,'.',',')+'" readonly>'+
                        '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly>'+
                    '</td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('+contadorfilas+');"></td>'+
                    '<td class="tdmod">'+
                        '<input type="text" class="form-control inputnextdet divorinputmodsm descuentopesospartidaAux" name="descuentopesospartidaAux[]" value="0.'+numerocerosconfigurados+'" onchange="formatocorrectoinputcantidadesComma(this);calculardescuentoporcentajepartida('+contadorfilas+');">'+
                        '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('+contadorfilas+');">'+
                    '</td>'+
                    '<td class="tdmod">'+
                        '<input type="text" class="form-control divorinputmodsm importedescuentopesospartidaAux" name="importedescuentopesospartidaAux[]" value="'+preciopartida+'" readonly>'+
                        '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'+
                    '</td>'+
                    '<td class="tdmod">'+
                        '<input type="text" class="form-control divorinputmodsm subtotalpartidaAux" name="subtotalpartidaAux[]" value="'+number_format(preciopartida,numerodecimales,'.',',W')+'" readonly>'+
                        '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'+preciopartida+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly>'+
                    '</td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'+Impuesto+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                    '<td class="tdmod">'+
                        '<input type="text" class="form-control divorinputmodsm trasladoivapesospartidaAux" name="trasladoivapesospartidaAux[]" value="'+number_format(round(ivapesos, numerodecimales), numerodecimales, '.', ',')+'" readonly>'+
                        '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'+number_format(round(ivapesos, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly>'+
                    '</td>'+
                    '<td class="tdmod">'+
                        '<input type="text" class="form-control divorinputmodsm totalpesospartidaAux" name="totalpesospartidaAux[]" value="'+number_format(round(total, numerodecimales), numerodecimales, '.', ',')+'" readonly>'+
                        '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'+number_format(round(total, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly>'+
                    '</td>'+
                    '<td class="tdmod">'+
                        '<input type="text" class="form-control divorinputmodsm costopartidaAux" name="costopartidaAux[]" value="'+number_format(Costo,numerodecimales,'.',',')+'" readonly>'+
                        '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly>'+
                    '</td>'+
                    '<td class="tdmod">'+
                        '<input type="text" class="form-control divorinputmodsm costototalpartidaAux" name="costototalpartidaAux[]" value="'+number_format(Costo,numerodecimales,'.',',')+'" readonly>'+
                        '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly>'+
                    '</td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('+contadorfilas+');" required></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'+
                    '<td class="tdmod">'+
                        '<input type="text" class="form-control divorinputmodsm utilidadpartidaAux" name="utilidadpartidaAux[]" value="'+number_format(round(utilidad, numerodecimales), numerodecimales, '.', ',')+'" readonly>'+
                        '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'+number_format(round(utilidad, numerodecimales), numerodecimales, '.', '')+'" readonly>'+
                    '</td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm remisionpartida" name="remisionpartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cartaportepartida" name="cartaportepartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm departamentopartida" name="departamentopartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cargopartida" name="cargopartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partida" name="partida[]" value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm tiendapartida" name="tiendapartida[]"  value="" autocomplete="off"></td>'+
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm pedidopartida" name="pedidopartida[]"  value="" autocomplete="off"></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm almacenpartida" name="almacenpartida[]"  value="" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm datosunidadpartida" name="datosunidadpartida[]"  value="" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm utilidadaritmeticapartida" name="utilidadaritmeticapartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm utilidadfinancieriapartida" name="utilidadfinancieriapartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'+CostoDeLista+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '<td class="tdmod">'+
                      '<div class="row divorinputmodxl">'+
                        '<div class="col-xs-2 col-sm-2 col-md-2">'+
                          '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('+contadorfilas+');" ><i class="material-icons">remove_red_eye</i></div>'+
                        '</div>'+
                        '<div class="col-xs-10 col-sm-10 col-md-10">'+
                          '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'+ClaveProducto+'" readonly required data-parsley-length="[1, 20]">'+
                        '</div>'+
                      '</div>'+
                    '</td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'+NombreClaveProducto+'" readonly></td>'+
                    '<td class="tdmod">'+
                      '<div class="row divorinputmodxl">'+
                        '<div class="col-xs-2 col-sm-2 col-md-2">'+
                          '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesunidades" data-toggle="tooltip" title="Ver Claves Unidades" onclick="listarclavesunidades('+contadorfilas+');" ><i class="material-icons">remove_red_eye</i></div>'+
                        '</div>'+
                        '<div class="col-xs-10 col-sm-10 col-md-10">'+
                          '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'+ClaveUnidad+'" readonly required data-parsley-length="[1, 5]">'+
                        '</div>'+
                      '</div>'+
                    '</td>'+
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'+NombreClaveUnidad+'" readonly></td>'+
                  '</tr>';
        $("#tabladetallesfactura").append(fila);
        calculartotalesfilas(contadorfilas);
        mostrarformulario();
        comprobarfilas();
        calculartotal();
        contadorproductos++;
        contadorfilas++;
        partida++;
        //colocar o no dataparsleyutilidad segun la configuracion de la empresa
        if(validarutilidadnegativa == 'S'){
            $(".utilidadpartida").removeAttr('data-parsley-utilidad');
            $("#utilidad").removeAttr('data-parsley-decimalesconfigurados');
        }else{
            $(".utilidadpartida").attr('data-parsley-utilidad', "0."+numerocerosconfiguradosinputnumberstep );
            $("#utilidad").attr('data-parsley-decimalesconfigurados', '/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/');
        }
        $("#codigoabuscar").val("");
        $("#codigogastoabuscar").val("");
        //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
        $(".inputnextdet").keyup(function (e) {
          //recomentable para mayor compatibilidad entre navegadores.
          var code = (e.keyCode ? e.keyCode : e.which);
          var index = $(this).index(".inputnextdet");
          switch(code){
            case 13:
              //$(".inputnextdet").eq(index + 1).focus().select();
              break;
            case 39:
              $(".inputnextdet").eq(index + 1).focus().select();
              break;
            case 37:
              $(".inputnextdet").eq(index - 1).focus().select();
              break;
          }
        });
        $('.page-loader-wrapper').css('display', 'none');
    }else{
        msj_errorproductoyaagregado();
        $('.page-loader-wrapper').css('display', 'none');
    }
}
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Factura');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs =        '<div class="row">'+
                      '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#facturatab" data-toggle="tab">Factura</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#emisortab" data-toggle="tab">Emisor</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#receptortab" data-toggle="tab">Receptor ó Cliente</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#otrostab" data-toggle="tab">Otros</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                          '<div role="tabpanel" class="tab-pane fade in active" id="facturatab">'+
                            '<div class="row">'+
                              '<div class="col-md-3">'+
                                '<label>Factura <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b> &nbsp;&nbsp;<b style="color:#F44336 !important;" id="esquematexto"> Esquema: '+esquema+'</b>  <div class="btn btn-xs bg-red waves-effect" id="btnobtenerfoliosfacturas" onclick="obtenerfoliosfacturas()">Cambiar</div></label>'+
                                '<input type="text" class="form-control inputnextdet" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                '<input type="hidden" class="form-control" name="stringremisionesseleccionadas" id="stringremisionesseleccionadas" readonly required>'+
                                '<input type="hidden" class="form-control" name="stringordenesseleccionadas" id="stringordenesseleccionadas" readonly required>'+
                                '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                                '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                                '<input type="hidden" class="form-control" name="esquema" id="esquema" value="'+esquema+'" readonly data-parsley-length="[1, 10]">'+
                                '<input type="hidden" class="form-control" name="depto" id="depto" value="'+depto+'" readonly data-parsley-length="[1, 20]">'+
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
                                      '<div class="btn bg-blue waves-effect" id="btnobteneragentes" onclick="obteneragentes()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="text" class="form-control inputnextdet" name="numeroagente" id="numeroagente" required data-parsley-type="integer" autocomplete="off">'+
                                        '<input type="hidden" class="form-control" name="numeroagenteanterior" id="numeroagenteanterior"  required data-parsley-type="integer">'+
                                        '<input type="hidden" class="form-control" name="rfcagente" id="rfcagente" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="hidden" class="form-control" name="agente" id="agente" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-1">'+
                                '<label>Plazo días</label>'+
                                '<input type="text" class="form-control inputnextdet" name="plazo" id="plazo" value="5" required  autocomplete="off">'+
                              '</div>'+
                              '<div class="col-md-2">'+
                                '<label>Fecha</label>'+
                                '<input type="datetime-local" class="form-control" name="fecha" id="fecha" required  data-parsley-excluded="true" onkeydown="return false">'+
                                '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                                '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                              '</div>'+
                            '</div>'+
                            '<div class="row">'+
                              '<div class="col-md-2">'+
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
                                      '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet" name="pesosmoneda" id="pesosmoneda" value="1.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</td>'+
                                  '</tr>'+
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-2">'+
                                '<label>Pedido</label>'+
                                '<input type="text" class="form-control inputnextdet" name="pedido" id="pedido" data-parsley-length="[1, 50]" autocomplete="off">'+
                              '</div>'+
                              '<div class="col-md-1">'+
                                '<label>Tipo</label>'+
                                '<select id="tipo" name="tipo" class="form-control select2" style="width:100%">'+
                                '</select>'+
                              '</div>'+
                              '<div class="col-md-1">'+
                                '<label>Unidad</label>'+
                                '<select id="tipounidad" name="tipounidad" class="form-control select2" style="width:100%">'+
                                '</select>'+
                              '</div>'+
                              '<div class="col-md-3" id="divbuscarcodigos" hidden>'+
                                '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver Productos</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off" onkeyup="tipoLetra(this);">'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-3" id="divlistarremisiones"  style="display:none">'+
                                '<label>Remisiones</label>'+
                                '<div class="btn btn-block bg-blue waves-effect" id="btnlistarremisiones" onclick="listarremisiones()">Agregar Remisiones</div>'+
                              '</div>'+
                              '<div class="col-md-3" id="divlistarservicios"  style="display:none">'+
                                '<label>Servicios</label>'+
                                '<div class="btn btn-block bg-blue waves-effect" id="btnlistarservicios" onclick="listarordenes()">Agregar Servicios</div>'+
                              '</div>'+
                              '<div class="col-md-3" id="divbuscarcodigosservicios" hidden>'+
                                '<label>Escribe el código del servicio y presiona ENTER</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" id="btnobtenerservicios" onclick="listarservicios()">Ver Servicios</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="text" class="form-control" name="codigoservicioabuscar" id="codigoservicioabuscar" placeholder="Escribe el código del servicio" autocomplete="off" onkeyup="tipoLetra(this);">'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-3" id="divbuscarcodigosgastos" hidden>'+
                                '<label>Escribe el código gastos a buscar y presiona ENTER</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" id="btnobtenerproductosgastos" onclick="listarproductosgastos()">Ver Gastos</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="text" class="form-control" name="codigogastoabuscar" id="codigogastoabuscar" placeholder="Escribe el código gasto" autocomplete="off" onkeyup="tipoLetra(this);">'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+
                                '</table>'+
                              '</div>'+
                            '</div>'+
                          '</div>'+
                          '<div role="tabpanel" class="tab-pane fade" id="emisortab">'+
                            '<div class="row">'+
                              '<div class="col-md-4">'+
                                '<label>R.F.C.</label>'+
                                '<input type="text" class="form-control inputnextdet" name="emisorrfc" id="emisorrfc" value="'+rfcempresa+'"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                              '</div>'+
                              '<div class="col-md-4">'+
                                '<label>Emisor Nombre</label>'+
                                '<input type="text" class="form-control inputnextdet" name="emisornombre" id="emisornombre" value="'+nombreempresa+'" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                              '</div>'+
                              '<div class="col-md-4">'+
                                '<label>Confirmación</label>'+
                                '<input type="text" class="form-control inputnextdet" name="confirmacion" id="confirmacion" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]" autocomplete="off">'+
                              '</div>'+
                            '</div>'+
                            '<div class="row">'+
                              '<div class="col-md-4">'+
                                '<label>Lugar Expedición <span class="label label-danger" id="textonombrelugarexpedicion"></span></label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenerlugaresexpedicion()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="text" class="form-control inputnextdet" name="lugarexpedicion" id="lugarexpedicion" value="'+lugarexpedicion+'" required autocomplete="off">'+
                                        '<input type="hidden" class="form-control" name="lugarexpedicionanterior" id="lugarexpedicionanterior" value="'+lugarexpedicion+'" required>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-4">'+
                                '<label>Régimen Fiscal <span class="label label-danger" id="textonombreregimenfiscal">'+regimenfiscal+'</span></label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscales()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="text" class="form-control inputnextdet" name="claveregimenfiscal" id="claveregimenfiscal" value="'+claveregimenfiscal+'" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                        '<input type="hidden" class="form-control" name="claveregimenfiscalanterior" id="claveregimenfiscalanterior" value="'+claveregimenfiscal+'" required onkeyup="tipoLetra(this)">'+
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
                                        '<input type="text" class="form-control inputnextdet" name="clavetiporelacion" id="clavetiporelacion" onkeyup="tipoLetra(this)" autocomplete="off">'+
                                        '<input type="hidden" class="form-control" name="clavetiporelacionanterior" id="clavetiporelacionanterior" onkeyup="tipoLetra(this)">'+
                                        '<input type="hidden" class="form-control" name="tiporelacion" id="tiporelacion"   readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+
                                '</table>'+
                              '</div>'+
                            '</div>'+
                            '<div class="row">'+
                              '<div class="col-md-8">'+
                                '<div class="row">'+
                                  '<div class="col-md-12 table-responsive cabecerafija" style="height: 125px;overflow-y: scroll;padding: 0px 0px;">'+
                                    '<input type="hidden" class="form-control" name="numerofilasuuid" id="numerofilasuuid" value="0" readonly>'+
                                    '<table id="tablauuidrelacionados" class="table table-bordered tablauuidrelacionados">'+
                                      '<thead class="'+background_tables+'">'+
                                        '<tr>'+
                                          '<th class="customercolortheadth">Comprobante o UUID Relacionado</th>'+
                                          '<th class="customercolortheadth">'+
                                              '<div class="col-md-6">'+
                                                '<label for="xml" class="btn btn-success">Selecciona el xml relacionado</label>'+
                                                '<input type="file" class="form-control" name="xml" id="xml" onchange="cambiodexml(this)" style="visibility:hidden;display:none;" onclick="this.value=null;" form="formxml">'+
                                              '</div>'+
                                              '<div class="col-md-6">'+
                                                '<label class="btn btn-success" onclick="listaruuidrelacionados();">Selecciona factura relacionada</label>'+
                                              '</div>'+
                                          '</th>'+
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
                          '<div role="tabpanel" class="tab-pane fade" id="receptortab">'+
                            '<div class="row">'+
                              '<div class="col-md-3">'+
                                '<label>R.F.C.</label>'+
                                '<input type="text" class="form-control inputnextdet" name="receptorrfc" id="receptorrfc"   required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Nombre</label>'+
                                '<input type="text" class="form-control inputnextdet" name="receptornombre" id="receptornombre"  required readonly onkeyup="tipoLetra(this);" data-parsley-length="[1, 150]">'+
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
                                        '<input type="text" class="form-control inputnextdet" name="claveformapago" id="claveformapago" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                        '<input type="hidden" class="form-control" name="claveformapagoanterior" id="claveformapagoanterior" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="hidden" class="form-control" name="formapago" id="formapago" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Método de Pago <span class="label label-danger" id="textonombremetodopago"></span></label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenermetodospago()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="text" class="form-control inputnextdet" name="clavemetodopago" id="clavemetodopago" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                        '<input type="hidden" class="form-control" name="clavemetodopagoanterior" id="clavemetodopagoanterior" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="hidden" class="form-control" name="metodopago" id="metodopago" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Uso CFDI <span class="label label-danger" id="textonombreusocfdi"></span></label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenerusoscfdi()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="text" class="form-control inputnextdet" name="claveusocfdi" id="claveusocfdi" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                        '<input type="hidden" class="form-control" name="claveusocfdianterior" id="claveusocfdianterior" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="hidden" class="form-control" name="usocfdi" id="usocfdi" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Residencia Fiscal <span class="label label-danger" id="textonombreresidenciafiscal"></span></label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenerresidenciasfiscales()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="text" class="form-control inputnextdet" name="claveresidenciafiscal" id="claveresidenciafiscal" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                        '<input type="hidden" class="form-control" name="claveresidenciafiscalanterior" id="claveresidenciafiscalanterior" required readonly onkeyup="tipoLetra(this)">'+
                                        '<input type="hidden" class="form-control" name="residenciafiscal" id="residenciafiscal" required readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-2">'+
                                  '<label>Régimen Fiscal <span class="label label-danger" id="textonombreregimenfiscalreceptor"></span></label>'+
                                  '<table class="col-md-12">'+
                                      '<tr>'+
                                      '<td>'+
                                          '<div class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscalesreceptor()">Seleccionar</div>'+
                                      '</td>'+
                                      '<td>'+
                                          '<div class="form-line">'+
                                          '<input type="text" class="form-control inputnextdet" name="claveregimenfiscalreceptor" id="claveregimenfiscalreceptor"  required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                          '<input type="hidden" class="form-control" name="claveregimenfiscalreceptoranterior" id="claveregimenfiscalreceptoranterior"  required onkeyup="tipoLetra(this)">'+
                                          '<input type="hidden" class="form-control" name="regimenfiscalreceptor" id="regimenfiscalreceptor"  required readonly>'+
                                          '</div>'+
                                      '</td>'+
                                      '</tr>'+
                                  '</table>'+
                              '</div>'+
                              '<div class="col-md-2">'+
                                '<label>Condiciones de Pago </label>'+
                                '<input type="text" class="form-control inputnextdet" name="condicionesdepago" id="condicionesdepago" value="CREDITO" required data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);enterCondiciones(event)" autocomplete="off">'+
                              '</div>'+
                              '<div class="col-md-2">'+
                                '<label>Num Reg Id Trib</label>'+
                                '<input type="text" class="form-control inputnextdet" name="numeroregidtrib" id="numeroregidtrib" data-parsley-length="[1, 40]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                              '</div>'+
                            '</div>'+
                          '</div>'+
                          '<div role="tabpanel" class="tab-pane fade" id="otrostab">'+
                            '<div class="row">'+
                              '<div class="col-md-3">'+
                                '<label>Tipo PA</label>'+
                                '<table class="col-md-12">'+
                                  '<tr>'+
                                    '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenertipospa()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                      '<div class="form-line">'+
                                        '<input type="hidden" class="form-control" name="numerotipopa" id="numerotipopa"  readonly onkeyup="tipoLetra(this)" autocomplete="off">'+
                                        '<input type="text" class="form-control inputnextdet" name="tipopa" id="tipopa"  readonly>'+
                                      '</div>'+
                                    '</td>'+
                                  '</tr>'+
                                '</table>'+
                              '</div>'+
                              '<div class="col-md-3">'+
                                '<label>Refactura</label>'+
                                '<input type="text" class="form-control inputnextdet" name="refactura" id="refactura"  readonly data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                              '</div>'+
                              '<div class="col-md-3" id="divperiodicidad">'+
                                    '<label>Periodicidad <span class="label label-danger" id="textonombreperiodicidad"></span></label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                        '<td>'+
                                            '<div class="btn bg-blue waves-effect" onclick="obtenerperiodicidades()">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                            '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnextdet" name="claveperiodicidad" id="claveperiodicidad"  onkeyup="tipoLetra(this)" autocomplete="off">'+
                                            '<input type="hidden" class="form-control" name="claveperiodicidadanterior" id="claveperiodicidadanterior"  onkeyup="tipoLetra(this)">'+
                                            '<input type="hidden" class="form-control" name="periodicidad" id="periodicidad" readonly>'+
                                            '</div>'+
                                        '</td>'+
                                        '</tr>'+
                                    '</table>'+
                              '</div>'+
                              '<div class="col-md-3" id="divmes">'+
                                    '<label>Meses <span class="label label-danger" id="textonombremes"></span></label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                        '<td>'+
                                            '<div class="btn bg-blue waves-effect" onclick="obtenermeses()">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                            '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnextdet" name="clavemes" id="clavemes"   onkeyup="tipoLetra(this)" autocomplete="off">'+
                                            '<input type="hidden" class="form-control" name="clavemesanterior" id="clavemesanterior"   onkeyup="tipoLetra(this)">'+
                                            '<input type="hidden" class="form-control" name="mes" id="mes"   readonly>'+
                                            '</div>'+
                                        '</td>'+
                                        '</tr>'+
                                    '</table>'+
                              '</div>'+
                              //Adenda
                              '<div class="col-md-9">'+
                                    '<div class="col-md-3">'+
                                        '<label>Afectado</label>'+
                                        '<input disabled type="text" class="form-control inputnextdet" name="afectadoAdenda" id="afectadoAdenda" onkeyup="tipoLetra(this)" autocomplete="off" required>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Emisor Siniestro</label>'+
                                        '<input disabled type="text" class="form-control inputnextdet" name="emisorSiniestroAdenda" id="emisorSiniestroAdenda" required onkeyup="tipoLetra(this)" data-parsley-maxlength="5" data-parsley-minlength="5" autocomplete="off">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Número Siniestro</label>'+
                                        '<input disabled type="text" class="form-control inputnextdet" name="numeroSiniestroAdenda" id="numeroSiniestroAdenda" required onkeyup="tipoLetra(this)" autocomplete="off" data-parsley-maxlength="7" data-parsley-minlength="7">'+
                                    '</div>'+
                              '</div>'+
                              //Fin adenda
                            '</div>'+
                          '</div>'+
                        '</div>'+
                      '</div>'+
                    '</div>'+
                    '<div class="row">'+
                      '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                          '<li role="presentation" class="active">'+
                            '<a href="#productostab" data-toggle="tab">Partidas</a>'+
                          '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                          '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                            '<div class="row">'+
                              '<div class="col-md-12 table-responsive cabecerafija" style="height: 300px;overflow-y: scroll;padding: 0px 0px;">'+
                                '<table id="tabladetallesfactura" class="table table-bordered tabladetallesfactura">'+
                                  '<thead class="'+background_tables+'">'+
                                    '<tr>'+
                                      '<th class="'+background_tables+'">#</th>'+
                                      '<th class="'+background_tables+'"><div style="width:100px !important;">Código</div></th>'+
                                      '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                                      '<th class="customercolortheadth">Uda</th>'+
                                      '<th class="customercolortheadth">Cantidad</th>'+
                                      '<th class="customercolortheadth">Precio $</th>'+
                                      '<th class="'+background_tables+'">Importe $</th>'+
                                      '<th class="customercolortheadth">Dcto %</th>'+
                                      '<th class="customercolortheadth">Dcto $</th>'+
                                      '<th class="'+background_tables+'">Importe Descuento $</th>'+
                                      '<th class="'+background_tables+'">SubTotal $</th>'+
                                      '<th class="customercolortheadth">Iva %</th>'+
                                      '<th class="'+background_tables+'">Traslado Iva $</th>'+
                                      '<th class="'+background_tables+'">Total $</th>'+
                                      '<th class="'+background_tables+'">Costo $</th>'+
                                      '<th class="'+background_tables+'">Costo Total $</th>'+
                                      '<th class="customercolortheadth">Comisión %</th>'+
                                      '<th class="'+background_tables+'">Comisión $</th>'+
                                      '<th class="bg-amber">Utilidad $</th>'+
                                      '<th class="'+background_tables+'">Remisión</th>'+
                                      '<th class="'+background_tables+'">Carta Porte</th>'+
                                      '<th class="'+background_tables+'">Orden</th>'+
                                      '<th class="'+background_tables+'">Departamento</th>'+
                                      '<th class="'+background_tables+'">Cargo</th>'+
                                      '<th class="'+background_tables+'">Partida</th>'+
                                      '<th class="customercolortheadth">Tienda</th>'+
                                      '<th class="customercolortheadth">Pedido</th>'+
                                      '<th class="'+background_tables+'">Almacén</th>'+
                                      '<th class="customercolortheadth">Datos de Unidad</th>'+
                                      '<th class="'+background_tables+'" hidden>Utilidad Aritmetica %</th>'+
                                      '<th class="'+background_tables+'" hidden>Utilidad Financiera %</th>'+
                                      '<th class="'+background_tables+'">Moneda</th>'+
                                      '<th class="'+background_tables+'">Costo de Lista</th>'+
                                      '<th class="'+background_tables+'">Tipo De Cambio</th>'+
                                      '<th class="customercolortheadth">ClaveProducto</th>'+
                                      '<th class="'+background_tables+'">Nombre ClaveProducto</th>'+
                                      '<th class="customercolortheadth">ClaveUnidad</th>'+
                                      '<th class="'+background_tables+'">Nombre ClaveUnidad</th>'+
                                      '<th class="'+background_tables+'" hidden>Traslado Ieps</th>'+
                                      '<th class="'+background_tables+'" hidden>Traslado Iva</th>'+
                                      '<th class="'+background_tables+'" hidden>Retención Iva</th>'+
                                      '<th class="'+background_tables+'" hidden>Retención Isr</th>'+
                                      '<th class="'+background_tables+'" hidden>Retención Ieps</th>'+
                                      '<th class="'+background_tables+'" hidden>Meses</th>'+
                                      '<th class="'+background_tables+'" hidden>Tasa Interes</th>'+
                                      '<th class="'+background_tables+'" hidden>Monto Interes</th>'+
                                    '</tr>'+
                                  '</thead>'+
                                  '<tbody>'+
                                  '</tbody>'+
                                '</table>'+
                              '</div>'+
                            '</div>'+
                          '</div>'+
                        '</div>'+
                        '<div class="row" id="divimportarpartidas">'+
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
                          '<div class="col-md-3">'+
                            '<label>Observaciones</label>'+
                            '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" rows="5" onkeyup="tipoLetra(this);" data-parsley-length="[1, 255]"></textarea>'+
                          '</div>'+
                          '<div class="col-md-3">'+
                            '<label>Comentarios</label>'+
                            '<textarea class="form-control inputnextdet" name="descripcion" id="descripcion" rows="5" onkeyup="tipoLetra(this);" data-parsley-length="[1, 255]"></textarea>'+
                          '</div>'+
                          '<div class="col-md-3">'+
                            '<table class="table table-striped table-hover">'+
                              '<tr>'+
                                '<td class="tdmod">Crédito</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="creditoAux" id="creditoAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="credito" id="credito" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                              '</tr>'+
                              '<tr>'+
                                '<td class="tdmod">Saldo</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="saldoAux" id="saldoAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="saldo" id="saldo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                              '</tr>'+
                              '<tr>'+
                                '<td class="tdmod">Utilidad</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="utilidadAux" id="utilidadAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="utilidad" id="utilidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                              '</tr>'+
                              '<tr>'+
                                '<td class="tdmod">Costo</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="costoAux" id="costoAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;"  step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                              '</tr>'+
                              '<tr hidden>'+
                                '<td class="tdmod">Comisión</td>'+
                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="comision" id="comision" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                              '</tr>'+
                            '</table>'+
                          '</div>'+
                          '<div class="col-md-3">'+
                            '<table class="table table-striped table-hover">'+
                              '<tr>'+
                                '<td class="tdmod">Importe</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="importeAux" id="importeAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                              '</tr>'+
                              '<tr>'+
                                '<td class="tdmod">Descuento</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="descuentoAux" id="descuentoAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                              '</tr>'+
                              '<tr>'+
                                '<td class="tdmod">SubTotal</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="subtotalAux" id="subtotalAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                              '</tr>'+
                              '<tr>'+
                                '<td class="tdmod">Iva</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="ivaAux" id="ivaAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                              '</tr>'+
                              '<tr>'+
                                '<td class="tdmod">Total</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="totalAux" id="totalAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
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
                    '</div>';
  $("#tabsform").html(tabs);
  //colocar autocomplette off  todo el formulario
  $(".form-control").attr('autocomplete','off');
  //colocar required en observaciones segun la configuracion de la empresa
  if(pedirobligatoriamenteobservacionenfactura == 'S'){
    $("#observaciones").attr('required', 'required');
  }else{
    $("#observaciones").removeAttr('required');
  }
  //mostrar mensaje de bajar plantilla
  $('[data-toggle="tooltip"]').tooltip({
    container: 'body'
  });
  obtenultimonumero();
  obtenertiposordenescompra('CLIENTE');
  obtenertiposunidades();
  asignarfechaactual();
  var Depto = $("#depto").val();
  comprobartiposerie(Depto);
  //asignar el tipo de operacion que se realizara
  $("#tipooperacion").val("alta");
  //se debe motrar el input para buscar los productos
  $("#divbuscarcodigoproducto").show();
  //activar los input select
  $("#moneda").select2();
  $("#tipo").select2();
  $("#tipounidad").select2();
  //reiniciar contadores
  contadorproductos=0;
  contadorfilas = 0;
  partida = 1;
  $("#numerofilas").val("0");
  //activar busqueda de codigos
  $("#codigoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerproductoporcodigo();
    }
  });
  $("#codigogastoabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerproductogastoporcodigo();
    }
  });
  $("#codigoservicioabuscar").keypress(function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerservicioporcodigo();
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
  //activar busqueda para metodo pago
  $('#clavemetodopago').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenermetodopagoporclave();
    }
  });
  //regresar clave
  $('#clavemetodopago').on('change', function(e) {
    regresarclavemetodopago();
  });
  //activar busqueda para uso cfdi
  $('#claveusocfdi').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerusocfdiporclave();
    }
  });
  //regresar clave
  $('#claveusocfdi').on('change', function(e) {
    regresarclaveusocfdi();
  });
  //activar busqueda para residencia fiscal
  $('#claveresidenciafiscal').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
    obtenerresidenciafiscalporclave();
    }
  });
  //regresar clave
  $('#claveresidenciafiscal').on('change', function(e) {
    regresarclaveresidenciafiscal();
  });
  //activar busqueda para forma pago
  $('#claveregimenfiscalreceptor').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenerregimenfiscalreceptorporclave();
      $('#condicionesdepago').focus()
      }
  });
  //regresar clave
  $('#claveregimenfiscalreceptor').on('change', function(e) {
      regresarclaveregimenfiscalreceptor();
  });
  //activar busqueda para periodicidad
  $('#claveperiodicidad').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenerperiodicidadporclave();
    }
  });
  //regresar clave
  $('#claveperiodicidad').on('change', function(e) {
      regresarclaveperiodicidad();
  });
  //activar busqueda para periodicidad
  $('#clavemes').on('keypress', function(e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code==13){
      obtenermesporclave();
    }
  });
  //regresar clave
  $('#clavemes').on('change', function(e) {
      regresarclavemes();
  });
  //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
  $(".inputnextdet").keyup(function (e) {
    //recomentable para mayor compatibilidad entre navegadores.
    var code = (e.keyCode ? e.keyCode : e.which);
    var index = $(this).index(".inputnextdet");
    switch(code){
      case 13:
        //$(".inputnextdet").eq(index + 1).focus().select();
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
  arrayclientesremisionesseleccionadas = [];
  arrayremisionesseleccionadas = new Array();
  $("#stringremisionesseleccionadas").val("");
  arrayclientesordenesseleccionadas = [];
  arrayordenesseleccionadas = new Array();
  $("#stringordenesseleccionadas").val("");
  $("#ModalAlta").modal('show');
}
//Cada que se elija un archivo
function cambiodexml(e) {
  var tipooperacion = $("#tipooperacion").val();
  var xml = $('#xml')[0].files[0];
  var form_data = new FormData();
  form_data.append('xml', xml);
  form_data.append('tipooperacion', tipooperacion);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:facturas_cargar_xml_uuid_relacionado,
    data: form_data,
    type: 'POST',
    contentType: false,
    processData: false,
    success: function (data) {
      var result = evaluaruuidexistente(data.uuid[0]);
      if(result == false){
        $("#tablauuidrelacionados tbody").append(data.uuidrelacionado);
        renumerarfilasuuid();
        comprobarfilasuuid();
      }
    },
    error: function (data) {
      console.log(data);
    }
  });
}
//comprobar numero filas de la tabla
function comprobarfilasuuid(){
  var numerofilasuuid = $("#tablauuidrelacionados tbody tr").length;
  $("#numerofilasuuid").val(numerofilasuuid);
}
//eliminar una fila en la tabla
function eliminarfilauuid(fila){
  var confirmacion = confirm("Esta seguro de eliminar la fila?");
  if (confirmacion == true) {
    $("#filauuid"+fila).remove();
    renumerarfilasuuid();
    comprobarfilasuuid();
  }
}
//renumerar las filas de la orden de compra
function renumerarfilasuuid(){
  var lista;
  //renumerar filas tr
  lista = document.getElementsByClassName("filasuuid");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("id", "filauuid"+i);
  }
  //renumerar btn eliminar fila
  lista = document.getElementsByClassName("btneliminaruuid");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onclick", "eliminarfilauuid("+i+')');
  }
}
function evaluaruuidexistente(uuid){
  var sumaiguales=0;
  var sumadiferentes=0;
  var sumatotal=0;
  $("tr.filasuuid").each(function () {
      var uuidrelacionado = $('.uuidrelacionado', this).val();
      if(uuid === uuidrelacionado){
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
      let preciopartidaAux = $('.preciopartidaAux', this).val()
      $('.preciopartida',this).val(number_format(preciopartidaAux,numerodecimales,'.',''))
      var preciopartida = $('.preciopartida', this).val();
      var importepartida = $('.importepartida', this).val();
      var descuentopesospartida = $('.descuentopesospartida', this).val();
      var importedescuentopesospartida = $('.importedescuentopesospartida', this).val();
      var subtotalpartida = $('.subtotalpartida', this).val();
      var ivaporcentajepartida = $('.ivaporcentajepartida', this).val();
      var trasladoivapesospartida = $('.trasladoivapesospartida', this).val();
      var totalpesospartida = $('.totalpesospartida', this).val();
      var utilidadpartida = $(".utilidadpartida", this).val();
      var costopartida = $(".costopartida", this).val();
      var costototalpartida = $(".costototalpartida ", this).val();
      var comisionporcentajepartida = $('.comisionporcentajepartida ', this).val();
      var comisionespesospartida = $('.comisionespesospartida ', this).val();
      var tipopartida = $(".tipopartida", this).val();
      //importe de la partida
      importepartida =  new Decimal(cantidadpartida).times(preciopartida);
      $('.importepartida', this).val(number_format(round(importepartida, numerodecimales), numerodecimales, '.', ''));
      $('.importepartidaAux', this).val(number_format($('.importepartida', this).val(), numerodecimales, '.', ','));
      //importe descuento
      importedescuentopesospartida =  new Decimal(importepartida).minus(descuentopesospartida);
      $('.importedescuentopesospartida', this).val(number_format(round(importedescuentopesospartida, numerodecimales), numerodecimales, '.', ''));
      $('.importedescuentopesospartidaAux', this).val(number_format($('.importedescuentopesospartida', this).val(), numerodecimales, '.', ','));

      //subtotal de la partida
      subtotalpartida =  new Decimal(importepartida).minus(descuentopesospartida);
      $('.subtotalpartida', this).val(number_format(round(subtotalpartida, numerodecimales), numerodecimales, '.', ''));
      $('.subtotalpartidaux', this).val(number_format($('.subtotalpartida', this).val(), numerodecimales, '.', ','));
      //iva en pesos de la partida
      var multiplicacionivapesospartida = new Decimal(subtotalpartida).times(ivaporcentajepartida);
      trasladoivapesospartida = new Decimal(multiplicacionivapesospartida/100);
      $('.trasladoivapesospartida', this).val(number_format(round(trasladoivapesospartida, numerodecimales), numerodecimales, '.', ''));
      $('.trasladoivapesospartidaAux', this).val(number_format($('.trasladoivapesospartida', this).val(), numerodecimales, '.', ','));
      //total en pesos de la partida
      totalpesospartida = new Decimal(subtotalpartida).plus(trasladoivapesospartida);
      $('.totalpesospartida', this).val(number_format(round(totalpesospartida, numerodecimales), numerodecimales, '.', ''));
      //costo total
      costototalpartida  = new Decimal(costopartida).times(cantidadpartida);
      $('.costototalpartida', this).val(number_format(round(costototalpartida, numerodecimales), numerodecimales, '.', ''));
      $('.costototalpartidaAux', this).val(number_format($('.costototalpartida', this).val(), numerodecimales, '.', ','));
      //comision de la partida
      var comisionporcentajepartida = new Decimal(subtotalpartida).times(comisionporcentajepartida);
      comisionespesospartida = new Decimal(comisionporcentajepartida/100);
      $('.comisionespesospartida', this).val(number_format(round(comisionespesospartida, numerodecimales), numerodecimales, '.', ''));
      //utilidad de la partida
      if(tipopartida == 'SERV'){
        utilidadpartida = subtotalpartida;
        $(".utilidadpartida", this).val(number_format(round(utilidadpartida, numerodecimales), numerodecimales, '.', ''));

      }else{
        utilidadpartida = new Decimal(subtotalpartida).minus(costototalpartida).minus(comisionespesospartida);
        $(".utilidadpartida", this).val(number_format(round(utilidadpartida, numerodecimales), numerodecimales, '.', ''));
      }
      $(".utilidadpartidaAux", this).val(number_format($(".utilidadpartida", this).val(), numerodecimales, '.', ','));
      calculartotal();
    }
    cuentaFilas++;
  });
}
//dejar en 0 los descuentos cuando el precio de la partida se cambie
function cambiodecantidadopreciopartida(fila){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
    if(fila === cuentaFilas){
      calculardescuentoporcentajepartida(fila);
      calculartotalesfilas(fila);
      calculartotal();
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
      let descuentoPesosAux = number_format($('.descuentopesospartidaAux', this).val(),numerodecimales,'.','')
      $('.descuentopesospartida', this).val(number_format(round(descuentoPesosAux,numerodecimales),numerodecimales,'.',''));
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
      $('.descuentopesospartidaAux', this).val(number_format(round(descuentopesospartida, numerodecimales), numerodecimales, '.', ','));
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
  var utilidad = 0;
  var costo = 0;
  var comision = 0;
  $("tr.filasproductos").each(function(){
    importe = new Decimal(importe).plus($(".importepartida", this).val());
    descuento = new Decimal(descuento).plus($(".descuentopesospartida", this).val());
    subtotal = new Decimal(subtotal).plus($(".subtotalpartida", this).val());
    iva = new Decimal(iva).plus($(".trasladoivapesospartida", this).val());
    total = new Decimal(total).plus($(".totalpesospartida", this).val());
    utilidad = new Decimal(utilidad).plus($(".utilidadpartida", this).val());
    costo = new Decimal(costo).plus($(".costototalpartida", this).val());
    comision = new Decimal(comision).plus($(".comisionpesospartida", this).val());
  });

  //IVA Y TOTAL
  let ivaAux = 0
  let totalRound = 0
  if(iva > 0){
    ivaAux = subtotal * 0.16;
  }
  totalRound = parseFloat(subtotal.toFixed(parseInt(2))) + ivaAux

  $("#importe").val(number_format(round(importe, 2), numerodecimales, '.', ''));
  $("#importeAux").val(number_format($("#importe").val(), numerodecimales, '.', ','));

  $("#descuento").val(number_format(round(descuento, 2), numerodecimales, '.', ''));
  $("#descuentoAux").val(number_format( $("#descuento").val(), numerodecimales, '.', ','));

  $("#subtotal").val(number_format(round(subtotal, 2), numerodecimales, '.', ''));
  $("#subtotalAux").val(number_format( $("#subtotal").val(), numerodecimales, '.', ','));

  $("#iva").val(number_format(round(ivaAux, 2), numerodecimales, '.', ''));
  $("#ivaAux").val(number_format($("#iva").val(), numerodecimales, '.', ','));

  $("#total").val(number_format(round(totalRound, 2), numerodecimales, '.', ''));
  $("#totalAux").val(number_format($("#total").val(), numerodecimales, '.', ','));

  $("#totalafacturar").val(number_format(round(totalRound, 2), numerodecimales, '.', ''));
  $("#totalafacturarAux").val(number_format($("#totalafacturar").val(), numerodecimales, '.', ','));
  $("#utilidad").val(number_format(round(utilidad, 2), numerodecimales, '.', ''));
  $("#utilidadAux").val(number_format($("#utilidad").val(), numerodecimales, '.', ','));

  $("#costo").val(number_format(round(costo, numerodecimales), numerodecimales, '.', ''));
  $("#costoAux").val(number_format($("#costo").val(), numerodecimales, '.', ','));
  $("#comision").val(number_format(round(comision, numerodecimales), numerodecimales, '.', ''));


  //nuevo saldo
  var numerocliente = $("#numerocliente").val();
  if(numerocliente != ""){
    $.get(facturas_obtener_nuevo_saldo_cliente,{numerocliente:numerocliente}, function(saldo){
        var nuevosaldo = new Decimal(saldo).plus(total);
        $("#saldo").val(number_format(round(nuevosaldo, numerodecimales), numerodecimales, '.', ''));
        $("#saldoAux").val(number_format(round(nuevosaldo, numerodecimales), numerodecimales, '.', ','));
        var saldo = $("#saldo").val();
        var credito = $("#credito").val();
        if(parseFloat(saldo) > parseFloat(credito)){
            $("#mensajecreditoexcedido").html("CRÉDITO DEL CLIENTE EXCEDIDO");
        }else{
            $("#mensajecreditoexcedido").html("");
        }
    })
  }
}
//calcular total a facturar
function calculartotalafacturar(tipo){
  var stringremisionesseleccionadas = $("#stringremisionesseleccionadas").val();
  var stringordenesseleccionadas = $("#stringordenesseleccionadas").val();
  $.get(facturas_obtener_total_a_facturar,{stringremisionesseleccionadas:stringremisionesseleccionadas,stringordenesseleccionadas:stringordenesseleccionadas,tipo:tipo}, function(totalafacturar){
    $("#totalafacturar").val(totalafacturar);
    $("#totalafacturarAux").val(number_format($("#totalafacturar").val(), numerodecimales, '.', ','));
  })
}
//eliminar una fila en la tabla de precios clientes
function eliminarfila(numerofila){
  var confirmacion = confirm("Esta seguro de eliminar la fila?");
  if (confirmacion == true) {
    $("#filaproducto"+numerofila).remove();
    contadorfilas--;
    contadorproductos--;
    partida--;
    comprobarfilas();
    renumerarfilas();//importante para todos los calculo en el modulo
    calculartotal();
  }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
  var numerofilas = $("#tabladetallesfactura tbody tr").length;
  $("#numerofilas").val(numerofilas);
}
//renumerar las filas de la orden de compra
function renumerarfilas(){
  var lista;
  //renumerar numero prtida
  lista = document.getElementsByClassName("numeropartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].innerHTML = parseInt(i)+1;
  }
  //renumerar filas tr
  lista = document.getElementsByClassName("filasproductos");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("id", "filaproducto"+i);
  }
  //renumerar btn eliminar filas
  lista = document.getElementsByClassName("btneliminarfilas");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onclick", "eliminarfila("+i+")");
  }
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("cantidadpartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar el precio de la partida
  lista = document.getElementsByClassName("preciopartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+') ');
  }
  lista = document.getElementsByClassName("preciopartidaAux");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidadesComma(this);calculartotalesfilas("+i+') ');
  }
  //renumerar descuento en pesos
  lista = document.getElementsByClassName("descuentoporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculardescuentopesospartida("+i+')');
  }
  //renumerar el decuetno en pesos de la partida
  lista = document.getElementsByClassName("descuentopesospartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida("+i+')');
  }
  lista = document.getElementsByClassName("descuentopesospartidaAux");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidadesComma(this);calculardescuentoporcentajepartida("+i+')');
  }
  //renumerar el iva en porcentaje de la partida
  lista = document.getElementsByClassName("ivaporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  // renumero porcentaje de comision
  lista = document.getElementsByClassName("comisionporcentajepartida");
  for (var i = 0; i < lista.length; i++){
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculardescuentopesospartida("+i+')');
  }
  //renumerar btnlistarclavesproductos
  lista = document.getElementsByClassName("btnlistarclavesproductos");
  for (var i = 0; i < lista.length; i++){
    lista[i].setAttribute("onclick", "listarclavesproductos("+i+')');
  }
  //renumerar btnlistarclavesunidades
  lista = document.getElementsByClassName("btnlistarclavesunidades");
  for (var i = 0; i < lista.length; i++){
    lista[i].setAttribute("onclick", "listarclavesunidades("+i+')');
  }
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
            url:facturas_guardar,
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
                arrayclientesremisionesseleccionadas = [];
                arrayremisionesseleccionadas = new Array();
                $("#stringremisionesseleccionadas").val("");
                arrayclientesordenesseleccionadas = [];
                arrayordenesseleccionadas = new Array();
                $("#stringordenesseleccionadas").val("");
                $('.page-loader-wrapper').css('display', 'none');
            },
            error:function(data){
              if(data.status == 403){
                msj_errorenpermisos();
              }else if(data.status == 404){
                msj_errorenfacturarelacion()
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
//modificacion
function obtenerdatos(facturamodificar){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(facturas_obtener_factura,{facturamodificar:facturamodificar },function(data){
    $("#titulomodal").html('Modificación Factura --- STATUS : ' + data.factura.Status);
    //formulario modificacion
    var tabs ='<div class="row">'+
                '<div class="col-md-12">'+
                  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                      '<li role="presentation" class="active">'+
                          '<a href="#facturatab" data-toggle="tab">Factura</a>'+
                      '</li>'+
                      '<li role="presentation">'+
                          '<a href="#emisortab" data-toggle="tab">Emisor</a>'+
                      '</li>'+
                      '<li role="presentation">'+
                          '<a href="#receptortab" data-toggle="tab">Receptor ó Cliente</a>'+
                      '</li>'+
                      '<li role="presentation">'+
                          '<a href="#otrostab" data-toggle="tab">Otros</a>'+
                      '</li>'+
                  '</ul>'+
                  '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="facturatab">'+
                      '<div class="row">'+
                        '<div class="col-md-3">'+
                          '<label>Factura <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp; <b style="color:#F44336 !important;" id="esquematexto"> Esquema: '+esquema+'</b>  <div class="btn btn-xs bg-red waves-effect" id="btnobtenerfoliosfacturas" onclick="obtenerfoliosfacturas()">Cambiar</div></label>'+
                          '<input type="text" class="form-control inputnextdet" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                          '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                          '<input type="hidden" class="form-control" name="stringremisionesseleccionadas" id="stringremisionesseleccionadas" readonly required>'+
                          '<input type="hidden" class="form-control" name="stringordenesseleccionadas" id="stringordenesseleccionadas" readonly required>'+
                          '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                          '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" readonly>'+
                          '<input type="hidden" class="form-control" name="esquema" id="esquema" value="'+esquema+'" readonly data-parsley-length="[1, 10]">'+
                          '<input type="hidden" class="form-control" name="depto" id="depto" value="'+depto+'" readonly >'+
                          '<input type="hidden" class="form-control" name="facturabd" id="facturabd" value="'+facturamodificar+'" readonly data-parsley-length="[1, 20]">'+
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
                                '<div class="btn bg-blue waves-effect" id="btnobteneragentes" onclick="obteneragentes()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="text" class="form-control inputnextdet" name="numeroagente" id="numeroagente" required data-parsley-type="integer" autocomplete="off">'+
                                  '<input type="hidden" class="form-control" name="numeroagenteanterior" id="numeroagenteanterior"  required data-parsley-type="integer">'+
                                  '<input type="hidden" class="form-control" name="rfcagente" id="rfcagente" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="hidden" class="form-control" name="agente" id="agente" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-1">'+
                          '<label>Plazo días</label>'+
                          '<input type="text" class="form-control inputnextdet" name="plazo" id="plazo" value="5" required autocomplete="off" >'+
                        '</div>'+
                        '<div class="col-md-2">'+
                          '<label>Fecha</label>'+
                          '<input type="datetime-local" class="form-control" name="fecha" id="fecha" required data-parsley-excluded="true" onkeydown="return false">'+
                          '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                          '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                        '</div>'+
                      '</div>'+
                      '<div class="row">'+
                        '<div class="col-md-2">'+
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
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet" name="pesosmoneda" id="pesosmoneda" value="1.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                              '</td>'+
                            '</tr>'+
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                          '<label>Pedido</label>'+
                          '<input type="text" class="form-control inputnextdet" name="pedido" id="pedido" data-parsley-length="[1, 50]" autocomplete="off">'+
                        '</div>'+
                        '<div class="col-md-1">'+
                          '<label>Tipo</label>'+
                          '<select id="tipo" name="tipo" class="form-control select2" style="width:100%">'+
                          '</select>'+
                        '</div>'+
                        '<div class="col-md-1">'+
                          '<label>Unidad</label>'+
                          '<select id="tipounidad" name="tipounidad" class="form-control select2" style="width:100%">'+
                          '</select>'+
                        '</div>'+
                        '<div class="col-md-3" id="divbuscarcodigos" hidden>'+
                          '<label>Escribe el código a buscar y presiona la tecla ENTER</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver Productos</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto" autocomplete="off"  onkeyup="tipoLetra(this);">'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-3" id="divlistarremisiones"  style="display:none">'+
                          '<label>Remisiones</label>'+
                          '<div class="btn btn-block bg-blue waves-effect" id="btnlistarremisiones" onclick="listarremisiones()">Agregar Remisiones</div>'+
                        '</div>'+
                        '<div class="col-md-3" id="divlistarservicios"  style="display:none">'+
                          '<label>Servicios</label>'+
                          '<div class="btn btn-block bg-blue waves-effect" id="btnlistarservicios" onclick="listarordenes()">Agregar Servicios</div>'+
                        '</div>'+
                        '<div class="col-md-3" id="divbuscarcodigosgastos" hidden>'+
                          '<label>Escribe el código gastos a buscar y presiona ENTER</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" id="btnobtenerproductosgastos" onclick="listarproductosgastos()">Ver Gastos</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="text" class="form-control" name="codigogastoabuscar" id="codigogastoabuscar" placeholder="Escribe el código gasto" autocomplete="off" onkeyup="tipoLetra(this);">'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+
                          '</table>'+
                        '</div>'+
                      '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="emisortab">'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<label>R.F.C.</label>'+
                          '<input type="text" class="form-control inputnextdet" name="emisorrfc" id="emisorrfc" value="'+rfcempresa+'"  required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Emisor Nombre</label>'+
                          '<input type="text" class="form-control inputnextdet" name="emisornombre" id="emisornombre" value="'+nombreempresa+'" required readonly data-parsley-length="[1, 150]" onkeyup="tipoLetra(this);">'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Confirmación</label>'+
                          '<input type="text" class="form-control inputnextdet" name="confirmacion" id="confirmacion" onkeyup="tipoLetra(this);" data-parsley-length="[1, 20]" autocomplete="off">'+
                        '</div>'+
                      '</div>'+
                      '<div class="row">'+
                        '<div class="col-md-4">'+
                          '<label>Lugar Expedición <span class="label label-danger" id="textonombrelugarexpedicion"></span></label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenerlugaresexpedicion()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="text" class="form-control inputnextdet" name="lugarexpedicion" id="lugarexpedicion" value="'+lugarexpedicion+'" required autocomplete="off">'+
                                  '<input type="hidden" class="form-control" name="lugarexpedicionanterior" id="lugarexpedicionanterior" value="'+lugarexpedicion+'" required>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                          '<label>Régimen Fiscal <span class="label label-danger" id="textonombreregimenfiscal">'+regimenfiscal+'</span></label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscales()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="text" class="form-control inputnextdet" name="claveregimenfiscal" id="claveregimenfiscal" value="'+claveregimenfiscal+'" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                  '<input type="hidden" class="form-control" name="claveregimenfiscalanterior" id="claveregimenfiscalanterior" value="'+claveregimenfiscal+'" required readonly onkeyup="tipoLetra(this)">'+
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
                                  '<input type="text" class="form-control inputnextdet" name="clavetiporelacion" id="clavetiporelacion" onkeyup="tipoLetra(this)" autocomplete="off">'+
                                  '<input type="hidden" class="form-control" name="clavetiporelacionanterior" id="clavetiporelacionanterior"  readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="hidden" class="form-control" name="tiporelacion" id="tiporelacion" readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+
                          '</table>'+
                        '</div>'+
                      '</div>'+
                      '<div class="row">'+
                        '<div class="col-md-6">'+
                          '<div class="row">'+
                            '<div class="col-md-12 table-responsive cabecerafija" style="height: 125px;overflow-y: scroll;padding: 0px 0px;">'+
                              '<input type="hidden" class="form-control" name="numerofilasuuid" id="numerofilasuuid" value="0" readonly>'+
                              '<table id="tablauuidrelacionados" class="table table-bordered tablauuidrelacionados">'+
                                '<thead class="'+background_tables+'">'+
                                  '<tr>'+
                                    '<th class="customercolortheadth">Comprobante o UUID Relacionado</th>'+
                                    '<th class="customercolortheadth">'+
                                        '<div class="col-md-12">'+
                                          '<label for="xml" class="btn btn-success">Selecciona el UUID relacionado</label>'+
                                          '<input type="file" class="form-control" name="xml" id="xml" onchange="cambiodexml(this)" style="visibility:hidden;display:none;" onclick="this.value=null;" form="formxml">'+
                                        '</div>'+
                                    '</th>'+
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
                    '<div role="tabpanel" class="tab-pane fade" id="receptortab">'+
                      '<div class="row">'+
                        '<div class="col-md-3">'+
                          '<label>R.F.C.</label>'+
                          '<input type="text" class="form-control inputnextdet" name="receptorrfc" id="receptorrfc"   required readonly data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);mayusculas(this);">'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Nombre</label>'+
                          '<input type="text" class="form-control inputnextdet" name="receptornombre" id="receptornombre"  required readonly onkeyup="tipoLetra(this);" data-parsley-length="[1, 150]">'+
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
                                  '<input type="text" class="form-control inputnextdet" name="claveformapago" id="claveformapago" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                  '<input type="hidden" class="form-control" name="claveformapagoanterior" id="claveformapagoanterior" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="hidden" class="form-control" name="formapago" id="formapago" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Método de Pago <span class="label label-danger" id="textonombremetodopago"></span></label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenermetodospago()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="text" class="form-control inputnextdet" name="clavemetodopago" id="clavemetodopago" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                  '<input type="hidden" class="form-control" name="clavemetodopagoanterior" id="clavemetodopagoanterior" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="hidden" class="form-control" name="metodopago" id="metodopago" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Uso CFDI <span class="label label-danger" id="textonombreusocfdi"></span></label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenerusoscfdi()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="text" class="form-control inputnextdet" name="claveusocfdi" id="claveusocfdi" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                  '<input type="hidden" class="form-control" name="claveusocfdianterior" id="claveusocfdianterior" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="hidden" class="form-control" name="usocfdi" id="usocfdi" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Residencia Fiscal <span class="label label-danger" id="textonombreresidenciafiscal"></span></label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenerresidenciasfiscales()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="text" class="form-control inputnextdet" name="claveresidenciafiscal" id="claveresidenciafiscal" required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                  '<input type="hidden" class="form-control" name="claveresidenciafiscalanterior" id="claveresidenciafiscalanterior" required readonly onkeyup="tipoLetra(this)">'+
                                  '<input type="hidden" class="form-control" name="residenciafiscal" id="residenciafiscal" required readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<label>Régimen Fiscal <span class="label label-danger" id="textonombreregimenfiscalreceptor"></span></label>'+
                            '<table class="col-md-12">'+
                                '<tr>'+
                                '<td>'+
                                    '<div class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscalesreceptor()">Seleccionar</div>'+
                                '</td>'+
                                '<td>'+
                                    '<div class="form-line">'+
                                    '<input type="text" class="form-control inputnextdet" name="claveregimenfiscalreceptor" id="claveregimenfiscalreceptor"  required onkeyup="tipoLetra(this)" autocomplete="off">'+
                                    '<input type="hidden" class="form-control" name="claveregimenfiscalreceptoranterior" id="claveregimenfiscalreceptoranterior"  required onkeyup="tipoLetra(this)">'+
                                    '<input type="hidden" class="form-control" name="regimenfiscalreceptor" id="regimenfiscalreceptor"  required readonly>'+
                                    '</div>'+
                                '</td>'+
                                '</tr>'+
                            '</table>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                          '<label>Condiciones de Pago</label>'+
                          '<input type="text" class="form-control inputnextdet" name="condicionesdepago" id="condicionesdepago" value="CREDITO" required data-parsley-length="[1, 50]" onkeyup="tipoLetra(this);enterCondiciones(event)" autocomplete="off">'+
                        '</div>'+
                        '<div class="col-md-2">'+
                          '<label>Num Reg Id Trib</label>'+
                          '<input type="text" class="form-control inputnextdet" name="numeroregidtrib" id="numeroregidtrib"  data-parsley-length="[1, 40]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                        '</div>'+
                      '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="otrostab">'+
                      '<div class="row">'+
                        '<div class="col-md-3">'+
                          '<label>Tipo PA</label>'+
                          '<table class="col-md-12">'+
                            '<tr>'+
                              '<td>'+
                                '<div class="btn bg-blue waves-effect" onclick="obtenertipospa()">Seleccionar</div>'+
                              '</td>'+
                              '<td>'+
                                '<div class="form-line">'+
                                  '<input type="hidden" class="form-control" name="numerotipopa" id="numerotipopa"  readonly onkeyup="tipoLetra(this)" autocomplete="off">'+
                                  '<input type="text" class="form-control inputnextdet" name="tipopa" id="tipopa"  readonly>'+
                                '</div>'+
                              '</td>'+
                            '</tr>'+
                          '</table>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                          '<label>Refactura</label>'+
                          '<input type="text" class="form-control inputnextdet" name="refactura" id="refactura"  readonly data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" autocomplete="off">'+
                        '</div>'+
                        '<div class="col-md-3" id="divperiodicidad">'+
                              '<label>Periodicidad <span class="label label-danger" id="textonombreperiodicidad"></span></label>'+
                              '<table class="col-md-12">'+
                                  '<tr>'+
                                  '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenerperiodicidades()">Seleccionar</div>'+
                                  '</td>'+
                                  '<td>'+
                                      '<div class="form-line">'+
                                      '<input type="text" class="form-control inputnextdet" name="claveperiodicidad" id="claveperiodicidad"  onkeyup="tipoLetra(this)" autocomplete="off">'+
                                      '<input type="hidden" class="form-control" name="claveperiodicidadanterior" id="claveperiodicidadanterior"  onkeyup="tipoLetra(this)">'+
                                      '<input type="hidden" class="form-control" name="periodicidad" id="periodicidad" readonly>'+
                                      '</div>'+
                                  '</td>'+
                                  '</tr>'+
                              '</table>'+
                        '</div>'+
                        '<div class="col-md-3" id="divmes">'+
                              '<label>Meses <span class="label label-danger" id="textonombremes"></span></label>'+
                              '<table class="col-md-12">'+
                                  '<tr>'+
                                  '<td>'+
                                      '<div class="btn bg-blue waves-effect" onclick="obtenermeses()">Seleccionar</div>'+
                                  '</td>'+
                                  '<td>'+
                                      '<div class="form-line">'+
                                      '<input type="text" class="form-control inputnextdet" name="clavemes" id="clavemes"   onkeyup="tipoLetra(this)" autocomplete="off">'+
                                      '<input type="hidden" class="form-control" name="clavemesanterior" id="clavemesanterior"   onkeyup="tipoLetra(this)">'+
                                      '<input type="hidden" class="form-control" name="mes" id="mes"   readonly>'+
                                      '</div>'+
                                  '</td>'+
                                  '</tr>'+
                              '</table>'+
                        '</div>'+
                        //Adenda
                        '<div class="col-md-9">'+
                            '<div class="col-md-3">'+
                                '<label>Afectado</label>'+
                                '<input disabled required type="text" class="form-control inputnextdet" name="afectadoAdenda" id="afectadoAdenda" onkeyup="tipoLetra(this)" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Emisor Siniestro</label>'+
                                '<input disabled required type="text" class="form-control inputnextdet" name="emisorSiniestroAdenda" id="emisorSiniestroAdenda" onkeyup="tipoLetra(this)" data-parsley-maxlength="5" data-parsley-minlength="5" autocomplete="off">'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Número Siniestro</label>'+
                                '<input disabled required type="text" class="form-control inputnextdet" name="numeroSiniestroAdenda" id="numeroSiniestroAdenda" onkeyup="tipoLetra(this)" autocomplete="off" data-parsley-maxlength="7" data-parsley-minlength="7">'+
                            '</div>'+
                        '</div>'+
                        //Fin adenda
                      '</div>'+
                    '</div>'+
                  '</div>'+
                '</div>'+
              '</div>'+
              '<div class="row">'+
                '<div class="col-md-12">'+
                  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                      '<a href="#productostab" data-toggle="tab">Partidas</a>'+
                    '</li>'+
                  '</ul>'+
                  '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                      '<div class="row">'+
                        '<div class="col-md-12 table-responsive cabecerafija" style="height: 300px;overflow-y: scroll;padding: 0px 0px;">'+
                          '<table id="tabladetallesfactura" class="table table-bordered tabladetallesfactura">'+
                            '<thead class="'+background_tables+'">'+
                              '<tr>'+
                                '<th class="'+background_tables+'">#</th>'+
                                '<th class="'+background_tables+'"><div style="width:100px !important;">Código</div></th>'+
                                '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                                '<th class="customercolortheadth">Uda</th>'+
                                '<th class="customercolortheadth">Cantidad</th>'+
                                '<th class="customercolortheadth">Precio $</th>'+
                                '<th class="'+background_tables+'">Importe $</th>'+
                                '<th class="customercolortheadth">Dcto %</th>'+
                                '<th class="customercolortheadth">Dcto $</th>'+
                                '<th class="'+background_tables+'">Importe Descuento $</th>'+
                                '<th class="'+background_tables+'">SubTotal $</th>'+
                                '<th class="customercolortheadth">Iva %</th>'+
                                '<th class="'+background_tables+'">Traslado Iva $</th>'+
                                '<th class="'+background_tables+'">Total $</th>'+
                                '<th class="'+background_tables+'">Costo $</th>'+
                                '<th class="'+background_tables+'">Costo Total $</th>'+
                                '<th class="customercolortheadth">Comisión %</th>'+
                                '<th class="'+background_tables+'">Comisión $</th>'+
                                '<th class="bg-amber">Utilidad $</th>'+
                                '<th class="'+background_tables+'">Remisión</th>'+
                                '<th class="'+background_tables+'">Carta Porte</th>'+
                                '<th class="'+background_tables+'">Orden</th>'+
                                '<th class="'+background_tables+'">Departamento</th>'+
                                '<th class="'+background_tables+'">Cargo</th>'+
                                '<th class="'+background_tables+'">Partida</th>'+
                                '<th class="customercolortheadth">Tienda</th>'+
                                '<th class="customercolortheadth">Pedido</th>'+
                                '<th class="'+background_tables+'">Almacén</th>'+
                                '<th class="customercolortheadth">Datos de Unidad</th>'+
                                '<th class="'+background_tables+'" hidden>Utilidad Aritmetica %</th>'+
                                '<th class="'+background_tables+'" hidden>Utilidad Financiera %</th>'+
                                '<th class="'+background_tables+'">Moneda</th>'+
                                '<th class="'+background_tables+'">Costo de Lista</th>'+
                                '<th class="'+background_tables+'">Tipo De Cambio</th>'+
                                '<th class="customercolortheadth">ClaveProducto</th>'+
                                '<th class="'+background_tables+'">Nombre ClaveProducto</th>'+
                                '<th class="customercolortheadth">ClaveUnidad</th>'+
                                '<th class="'+background_tables+'">Nombre ClaveUnidad</th>'+
                                '<th class="'+background_tables+'" hidden>Traslado Ieps</th>'+
                                '<th class="'+background_tables+'" hidden>Traslado Iva</th>'+
                                '<th class="'+background_tables+'" hidden>Retención Iva</th>'+
                                '<th class="'+background_tables+'" hidden>Retención Isr</th>'+
                                '<th class="'+background_tables+'" hidden>Retención Ieps</th>'+
                                '<th class="'+background_tables+'" hidden>Meses</th>'+
                                '<th class="'+background_tables+'" hidden>Tasa Interes</th>'+
                                '<th class="'+background_tables+'" hidden>Monto Interes</th>'+
                              '</tr>'+
                            '</thead>'+
                            '<tbody>'+
                            '</tbody>'+
                          '</table>'+
                        '</div>'+
                      '</div>'+
                    '</div>'+
                  '</div>'+
                  '<div class="row">'+
                    '<div class="col-md-3">'+
                      '<label>Observaciones</label>'+
                      '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" rows="5" onkeyup="tipoLetra(this);" data-parsley-length="[1, 255]"></textarea>'+
                    '</div>'+
                    '<div class="col-md-3">'+
                      '<label>Comentarios</label>'+
                      '<textarea class="form-control inputnextdet" name="descripcion" id="descripcion" rows="5" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
                    '</div>'+
                    '<div class="col-md-3">'+
                      '<table class="table table-striped table-hover">'+
                            '<tr>'+
                                '<td class="tdmod">Crédito</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="creditoAux" id="creditoAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="credito" id="credito" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                            '</tr>'+
                            '<tr>'+
                                '<td class="tdmod">Saldo</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="saldoAux" id="saldoAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="saldo" id="saldo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                            '</tr>'+
                            '<tr>'+
                                '<td class="tdmod">Utilidad</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="utilidadAux" id="utilidadAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="utilidad" id="utilidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                            '</tr>'+
                            '<tr>'+
                                '<td class="tdmod">Costo</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="costoAux" id="costoAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;"  step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                            '</tr>'+
                            '<tr hidden>'+
                                '<td class="tdmod">Comisión</td>'+
                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="comision" id="comision" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                            '</tr>'+
                      '</table>'+
                    '</div>'+
                    '<div class="col-md-3">'+
                        '<table class="table table-striped table-hover">'+
                            '<tr>'+
                                '<td class="tdmod">Importe</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="importeAux" id="importeAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                            '</tr>'+
                            '<tr>'+
                                '<td class="tdmod">Descuento</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="descuentoAux" id="descuentoAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                            '</tr>'+
                            '<tr>'+
                                '<td class="tdmod">SubTotal</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="subtotalAux" id="subtotalAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                            '</tr>'+
                            '<tr>'+
                                '<td class="tdmod">Iva</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="ivaAux" id="ivaAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
                            '</tr>'+
                            '<tr>'+
                                '<td class="tdmod">Total</td>'+
                                '<td class="tdmod">'+
                                    '<input type="text" class="form-control divorinputmodmd" name="totalAux" id="totalAux" value="0.'+numerocerosconfigurados+'" required readonly>'+
                                    '<input type="number" style="display:none;" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly>'+
                                '</td>'+
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
              '</div>';
    $("#tabsform").html(tabs);
    //colocar autocomplette off  todo el formulario
    $(".form-control").attr('autocomplete','off');
    //colocar required en observaciones segun la configuracion de la empresa
    if(pedirobligatoriamenteobservacionenfactura == 'S'){
      $("#observaciones").attr('required', 'required');
    }else{
      $("#observaciones").removeAttr('required');
    }
    obtenertiposordenescompra();
    obtenertiposunidades();
    //esconder el div del boton
    $("#btnobtenerclientes").hide();
    $("#btnobtenerfoliosfacturas").hide();
    $("#folio").val(data.factura.Folio);
    $("#serie").val(data.factura.Serie);
    $("#serietexto").html("Serie: "+data.factura.Serie);
    $("#esquematexto").html("Esquema: "+data.factura.Esquema);
    $("#esquema").val(data.factura.Esquema);
    $("#stringremisionesseleccionadas").val(data.arrayremisiones);
    $("#stringordenesseleccionadas").val(data.arrayordenes);
    $("#numerofilas").val(data.numerodetallesfactura);
    $("#depto").val(data.factura.Depto);
    $("#numerofilasuuid").val(data.numerodocumentosfactura);
    $("#fecha").val(data.fecha).attr('min', data.fechasdisponiblesenmodificacion.fechamin).attr('max', data.fechasdisponiblesenmodificacion.fechamax);
    $("#cliente").val(data.cliente.Nombre);
    if(data.cliente.Nombre != null){
      $("#textonombrecliente").html(data.cliente.Nombre.substring(0, 40));
    }
    $("#numerocliente").val(data.cliente.Numero);
    $("#numeroclienteanterior").val(data.cliente.Numero);
    //Adenda
    if(data.cliente.Numero== 22 && data.cliente.Nombre == 'SEGUROS INBURSA, S.A., GRUPO FINANCIERO INBURSA'){
      $('#afectadoAdenda').attr("disabled", false);
      $('#emisorSiniestroAdenda').attr("disabled", false);
      $('#numeroSiniestroAdenda').attr("disabled", false);

      $('#afectadoAdenda').attr("required", true);
      $('#emisorSiniestroAdenda').attr("required", true);
      $('#numeroSiniestroAdenda').attr("required", true);


      //Datos adenda
      $('#afectadoAdenda').val(data.Afectado);
      $('#emisorSiniestroAdenda').val(data.EmisorSiniestro);
      $('#numeroSiniestroAdenda').val(data.NumeroSiniestro);
    }else{
      $('#afectadoAdenda').attr("disabled", true);
      $('#emisorSiniestroAdenda').attr("disabled", true);
      $('#numeroSiniestroAdenda').attr("disabled", true);

      $('#emisorSiniestroAdenda').attr("required", false);
      $('#numeroSiniestroAdenda').attr("required", false);
      $('#afectadoAdenda').attr("required", false);
    }
    $("#rfccliente").val(data.cliente.Rfc);
    if(data.agente != null){
      $("#agente").val(data.agente.Nombre);
      if(data.agente.Nombre != null){
        $("#textonombreagente").html(data.agente.Nombre.substring(0, 40));
      }
      $("#numeroagente").val(data.agente.Numero);
      $("#numeroagenteanterior").val(data.agente.Numero);
      $("#rfcagente").val(data.agente.Rfc);
    }
    $("#plazo").val(data.factura.Plazo);
    $("#moneda").val(data.factura.Moneda).change();
    $("#pesosmoneda").val(data.tipocambio);
    $("#pedido").val(data.factura.Pedido);
    $("#observaciones").val(data.factura.Obs);
    $("#emisorrfc").val(data.factura.EmisorRfc);
    $("#emisornombre").val(data.factura.EmisorNombre);
    $("#confirmacion").val(data.factura.Confirmacion);
    $("#lugarexpedicion").val(data.factura.LugarExpedicion);
    $("#lugarexpedicionanterior").val(data.factura.LugarExpedicion);
    if(data.regimenfiscal != null){
      $("#regimenfiscal").val(data.regimenfiscal.Nombre);
      $("#textonombreregimenfiscal").html(data.regimenfiscal.Nombre.substring(0, 40));
      $("#claveregimenfiscal").val(data.regimenfiscal.Clave);
      $("#claveregimenfiscalanterior").val(data.regimenfiscal.Clave);
    }
    $("#tiporelacion").val(data.nombretiporelacion);
    if(data.nombretiporelacion != null){
      $("#textonombretiporelacion").html(data.nombretiporelacion.substring(0, 40));
    }
    $("#clavetiporelacion").val(data.clavetiporelacion);
    $("#clavetiporelacionanterior").val(data.clavetiporelacion);
    $("#receptorrfc").val(data.factura.ReceptorRfc);
    $("#receptornombre").val(data.factura.ReceptorNombre);
    if(data.formapago != null){
      $("#formapago").val(data.formapago.Nombre);
      $("#textonombreformapago").html(data.formapago.Nombre.substring(0, 40));
      $("#claveformapago").val(data.formapago.Clave);
      $("#claveformapagoanterior").val(data.formapago.Clave);
    }
    if(data.metodopago != null){
      $("#metodopago").val(data.metodopago.Nombre);
      $("#textonombremetodopago").html(data.metodopago.Nombre.substring(0, 40));
      $("#clavemetodopago").val(data.metodopago.Clave);
      $("#clavemetodopagoanterior").val(data.metodopago.Clave);
    }
    $("#condicionesdepago").val(data.factura.CondicionesDePago);
    if(data.usocfdi != null){
      $("#usocfdi").val(data.usocfdi.Nombre);
      $("#textonombreusocfdi").html(data.usocfdi.Nombre.substring(0, 40));
      $("#claveusocfdi").val(data.usocfdi.Clave);
      $("#claveusocfdianterior").val(data.usocfdi.Clave);
    }
    if(data.residenciafiscal != null){
      $("#residenciafiscal").val(data.residenciafiscal.Nombre);
      $("#textonombreresidenciafiscal").html(data.residenciafiscal.Nombre.substring(0, 40));
      $("#claveresidenciafiscal").val(data.residenciafiscal.Clave);
      $("#claveresidenciafiscalanterior").val(data.residenciafiscal.Clave);
    }
    if(data.regimenfiscalreceptor != null){
        $("#regimenfiscalreceptor").val(data.regimenfiscalreceptor.Nombre);
        if(data.regimenfiscalreceptor.Nombre != null){
            $("#textonombreregimenfiscalreceptor").html(data.regimenfiscalreceptor.Nombre.substring(0, 40));
        }
        $("#claveregimenfiscalreceptor").val(data.regimenfiscalreceptor.Clave);
        $("#claveregimenfiscalreceptoranterior").val(data.regimenfiscalreceptor.Clave);
    }
    if(data.cliente.Rfc == "XAXX010101000"){
      $("#divperiodicidad").show();
      $("#divmes").show();
      if(data.periodicidad != null){
          $("#periodicidad").val(data.periodicidad.Descripcion);
          if(data.periodicidad.Descripcion != null){
              $("#textonombreperiodicidad").html(data.periodicidad.Descripcion.substring(0, 40));
          }
          $("#claveperiodicidad").val(data.periodicidad.Clave);
          $("#claveperiodicidadanterior").val(data.periodicidad.Clave);
      }
      if(data.meses != null){
          $("#mes").val(data.meses.Descripcion);
          if(data.meses.Descripcion != null){
              $("#textonombremes").html(data.meses.Descripcion.substring(0, 40));
          }
          $("#clavemes").val(data.meses.Clave);
          $("#clavemesanterior").val(data.meses.Clave);
      }
    }else{
      $("#divperiodicidad").hide();
      $("#divmes").hide();
    }
    $("#numeroregidtrib").val(data.factura.NumRegIdTrib);
    $("#descripcion").val(data.factura.Descripcion);
    //cargar todos los detalles
    $("#tabladetallesfactura tbody").html(data.filasdetallesfactura);
    //totales
    $("#importe").val(data.importe);
    $("#importeAux").val(number_format(data.importe,numerodecimales,'.',','));
    $("#descuento").val(data.descuento);
    $("#descuentoAux").val(number_format(data.descuento,numerodecimales,'.',','));
    $("#subtotal").val(data.subtotal);
    $("#subtotalAux").val(number_format(data.subtotal,numerodecimales,'.',','));
    $("#iva").val(data.iva);
    $("#ivaAux").val(number_format(data.iva,numerodecimales,'.',','));
    $("#total").val(data.total);
    $("#totalAux").val(number_format(data.total,numerodecimales,'.',','));
    //cargar nota proveedor documentos
    $("#tablauuidrelacionados tbody").html(data.filasdocumentosfactura);
    //totales
    $("#credito").val(data.credito);
    $("#creditoAux").val(number_format(data.credito,numerodecimales,'.',','));
    $("#saldo").val(data.saldo);
    $("#saldoAux").val(number_format(data.saldo,numerodecimales,'.',','));
    $("#utilidad").val(data.utilidad);
    $("#utilidadAux").val(number_format(data.utilidad,numerodecimales,'.',','));
    $("#costo").val(data.costo)
    $("#costoAux").val(number_format(data.costo,numerodecimales,'.',','))
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //activar los input select
    $("#moneda").select2();
    //reiniciar contadores
    contadorproductos=data.contadorproductos;
    contadorfilas = data.contadorfilas;
    partida = data.partida;
    //activar busqueda de codigos
    $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenerproductoporcodigo();
      }
    });
    $("#codigogastoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenerproductogastoporcodigo();
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
    //activar busqueda para metodo pago
    $('#clavemetodopago').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenermetodopagoporclave();
      $('#claveusocfdi').focus()
      }
    });
    //regresar clave
    $('#clavemetodopago').on('change', function(e) {
      regresarclavemetodopago();
    });
    //activar busqueda para uso cfdi
    $('#claveusocfdi').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenerusocfdiporclave();
      }
    });
    //regresar clave
    $('#claveusocfdi').on('change', function(e) {
      regresarclaveusocfdi();
    });
    //activar busqueda para residencia fiscal
    $('#claveresidenciafiscal').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
      obtenerresidenciafiscalporclave();
      }
    });
    //regresar clave
    $('#claveresidenciafiscal').on('change', function(e) {
      regresarclaveresidenciafiscal();
    });
    //activar busqueda para forma pago
    $('#claveregimenfiscalreceptor').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerregimenfiscalreceptorporclave();
        $('#condicionesdepago').focus()
        }
    });
    //regresar clave
    $('#claveregimenfiscalreceptor').on('change', function(e) {
        regresarclaveregimenfiscalreceptor();
    });  //activar busqueda para periodicidad
    $('#claveperiodicidad').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenerperiodicidadporclave();
      }
    });
    //regresar clave
    $('#claveperiodicidad').on('change', function(e) {
        regresarclaveperiodicidad();
    });
    //activar busqueda para periodicidad
    $('#clavemes').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        obtenermesporclave();
      }
    });
    //regresar clave
    $('#clavemes').on('change', function(e) {
        regresarclavemes();
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnextdet").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnextdet");
      switch(code){
        case 13:
          //$(".inputnextdet").eq(index + 1).focus().select();
          break;
        case 39:
          $(".inputnextdet").eq(index + 1).focus().select();
          break;
        case 37:
          $(".inputnextdet").eq(index - 1).focus().select();
          break;
      }
    });
    //colocar o no dataparsleyutilidad segun la configuracion de la empresa
    if(validarutilidadnegativa == 'S'){
        $(".utilidadpartida").removeAttr('data-parsley-utilidad');
        $("#utilidad").removeAttr('data-parsley-decimalesconfigurados');
    }else{
        $(".utilidadpartida").attr('data-parsley-utilidad', "0."+numerocerosconfiguradosinputnumberstep );
        $("#utilidad").attr('data-parsley-decimalesconfigurados', '/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/');
    }
    renumerarfilasuuid();
    seleccionartipocliente(data);
  }).fail( function() {
    msj_errorajax();
    $('.page-loader-wrapper').css('display', 'none');
  })
}
async function seleccionartipocliente(data){
  await retraso();
  $("#tipo").val(data.factura.Tipo).change();
  $("#tipo").select2();
  $("#tipounidad").val(data.factura.Unidad).change();
  $("#tipounidad").select2();
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
          url:facturas_guardar_modificacion,
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
              }else if(data.status == 404){
                msj_errorenfacturarelacion()
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
//modificar datos generales
function modificardatosgeneralesdocumento(Factura){
  $.get(facturas_obtener_datos_generales,{Factura:Factura}, function(data){
    $("#modalmodificardatosgeneralesdocumento").modal('show');
    $("#facturadatosgenerales").val(Factura);
    $("#pedidodatosgenerales").val(data.Pedido);
    setTimeout(function(){$("#facturadatosgenerales").focus();},500);
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnextdet").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnextdet");
      switch(code){
        case 13:
          //$(".inputnextdet").eq(index + 1).focus().select();
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
      url:facturas_guardar_modificacion_datos_generales,
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
//detectar cuando en el input de buscar por codigo de producto el usuario presione la tecla enter, si es asi se realizara la busqueda con el codigo escrito
$(document).ready(function(){
  //activar busqueda de kardex por codigo
  $('#facturakardex').on('keypress', function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
          obtenerkardexporfactura();
      }
  });
});
//obtener kardex al dar click en detalle de la fila
function obtenerkardex(factura){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(facturas_obtener_kardex,{factura:factura},function(data){
      $("#titulomodalmovimientos").html("Kardex: " + factura);
      $("#facturakardex").val(factura);
      $("#filasmovimientos").html(data.filasmovimientos);
      $("#modalmovimientos").modal('show');
      $('.page-loader-wrapper').css('display', 'none');
  });
}
//obtener kardex al dar enter en el input del codigo
function obtenerkardexporfactura(){
  var facturakardex = $("#facturakardex").val();
  obtenerkardex(facturakardex);
}
//verificar si se puede dar de baja
function desactivar(facturadesactivar){
  $.get(facturas_verificar_si_continua_baja,{facturadesactivar:facturadesactivar}, function(data){
    if(data.Status == 'BAJA'){
      $("#facturadesactivar").val(0);
      $("#textomodaldesactivar").html('Error, esta Factura ya fue dado de baja');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{
      if(data.resultadofechas != ''){
        $("#facturadesactivar").val(0);
        $("#textomodaldesactivar").html('Error solo se pueden dar de baja las facturas del mes actual, fecha de la factura: ' + data.resultadofechas);
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        if(data.numerocuentasporcobrar > 0){
          $("#facturadesactivar").val(0);
          $("#textomodaldesactivar").html('Error esta factura tiene registros de cuentas por cobrar con el pago: ' + data.numerocuentaxcobrar);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else if(data.numeronotascliente > 0){
          $("#facturadesactivar").val(0);
          $("#textomodaldesactivar").html('Error esta factura tiene registros de notas crédito cliente con la nota: ' + data.numeronotacliente);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else{
          $("#facturadesactivar").val(facturadesactivar);
          $("#textomodaldesactivar").html('Estas seguro de dar de baja la factura? No'+ facturadesactivar);
          $("#motivobaja").val("");
          $("#divmotivobaja").show();
          $("#btnbaja").show();
          $('#estatusregistro').modal('show');
        }
      }
    }
  })
}
//bajas
$("#btnbaja").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formdesactivar")[0]);
  var form = $("#formdesactivar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:facturas_alta_o_baja,
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
//fin bajas
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento, tipo){
  tipoformatopdf = tipo;
  $.get(facturas_obtener_datos_envio_email,{documento:documento}, function(data){
    $("#textomodalenviarpdfemail").html("Enviar email Factura No." + documento);
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
    $("#emailasunto").val("FACTURA NO. " + documento +" DE "+ nombreempresa);
    $("#emailmensaje").val("FACTURA NO. " + documento +" DE "+ nombreempresa);
    if(data.factura.UUID != ""){
      $("#incluir_xml").removeAttr('onclick');
    }else{
      $("#incluir_xml").attr('onclick','javascript: return false;');
    }
    $("#divincluirxml").show();
    $(".dropify-clear").trigger("click");
    $("#divadjuntararchivo").show();
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
  if(tipoformatopdf == 1){
    var urlenviarpdf = facturas_enviar_pdfs_email;
  }else{
    var urlenviarpdf = facturas_enviar_pdfs_clientes_email;
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
//fin enviar documento pdf por email
//timbrar factura
function timbrarfactura(factura){
  $.get(facturas_verificar_si_continua_timbrado,{factura:factura}, function(data){
    if(data.Esquema == 'INTERNA' || data.Esquema == 'NOTA'){
      $("#facturatimbrado").val(0);
      $("#textomodaltimbrado").html('Aviso, las Facturas con Esquema INTERNA o NOTA no se pueden timbrar');
      $('#modaltimbrado').modal('show');
      $("#btntimbrarfactura").hide();
    }else if(data.Status == 'BAJA'){
      $("#facturatimbrado").val(0);
      $("#textomodaltimbrado").html('Aviso, esta Factura se encuentra dada de baja');
      $('#modaltimbrado').modal('show');
      $("#btntimbrarfactura").hide();
    }else{
      if(data.UUID != ""){
        $("#facturatimbrado").val(0);
        $("#textomodaltimbrado").html('Aviso, esta Factura ya se timbro');
        $('#modaltimbrado').modal('show');
        $("#btntimbrarfactura").hide();
      }else{
        $("#modaltimbrado").modal("show");
        $("#textomodaltimbrado").html("Esta seguro de timbrar la factura? No"+factura);
        $("#facturatimbrado").val(factura);
        $("#btntimbrarfactura").show();
      }
    }
  })
}
$("#btntimbrarfactura").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formtimbrado")[0]);
  var form = $("#formtimbrado");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:facturas_timbrar_factura,
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
// fin timbrar factura
//cancelar timbre
function cancelartimbre(facturabajatimbre){
  $.get(facturas_verificar_si_continua_baja_timbre,{facturabajatimbre:facturabajatimbre}, function(data){
    if(data.comprobante != ''){
      if(data.comprobante.IdFacturapi != null){
        if(data.obtener_factura.status == "canceled"){
          $("#facturabajatimbre").val(0);
          $("#iddocumentofacturapi").val(0);
          $("#textomodalbajatimbre").html('Aviso, el timbre de la factura No.' + facturabajatimbre +' ya esta cancelado');
          $("#divmotivobajatimbre").hide();
          $("#btnbajatimbre").hide();
          $('#modalbajatimbre').modal('show');
        }else if(data.obtener_factura.status == "valid"){
          if(data.obtener_factura.cancellation_status == "pending"){
            $("#facturabajatimbre").val(0);
            $("#iddocumentofacturapi").val(0);
            $("#textomodalbajatimbre").html('Aviso, el timbre de la factura No.' + facturabajatimbre +' ya se solicito su baja pero esta en espera de confirmación por parte del cliente');
            $("#divmotivobajatimbre").hide();
            $("#btnbajatimbre").hide();
            $('#modalbajatimbre').modal('show');
          }else if(data.obtener_factura.cancellation_status == "none"){
            $("#facturabajatimbre").val(facturabajatimbre);
            $("#iddocumentofacturapi").val(data.obtener_factura.id);
            $("#textomodalbajatimbre").html('Esta seguro de dar de baja el timbre de la factura No.'+ facturabajatimbre);
            $("#divmotivobajatimbre").show();
            $("#motivobajatimbre").html("<option value='01'>01 - Comprobante emitido con errores con relación</option><option value='02'>02 - Comprobante emitido con errores sin relación</option><option value='03'>03 - No se llevó a cabo la operación</option><option value='04'>04 - Operación nominativa relacionada en la factura global</option> ");
            $("#btnbajatimbre").show();
            $('#modalbajatimbre').modal('show');
          }
        }
      }else{
        $("#facturabajatimbre").val(0);
        $("#iddocumentofacturapi").val(0);
        $("#textomodalbajatimbre").html('Aviso, la factura No.'+ facturabajatimbre +' no esta timbrada en el nuevo sistema');
        $("#divmotivobajatimbre").hide();
        $("#btnbajatimbre").hide();
        $('#modalbajatimbre').modal('show');
      }
    }else{
      $("#facturabajatimbre").val(0);
      $("#iddocumentofacturapi").val(0);
      $("#textomodalbajatimbre").html('Aviso, la factura No.'+ facturabajatimbre +' no esta timbrada');
      $("#divmotivobajatimbre").hide();
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
      url:facturas_baja_timbre,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#modalbajatimbre').modal('hide');
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
          url: facturas_buscar_folio_string_like,
          data: function (d) {
            d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'Factura', name: 'Factura', orderable: false, searchable: true },
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
        agregararraypdf(data.Factura);
    });
}
//generar documento en iframe
function generardocumentoeniframe(Factura){
  var arraypdf = new Array();
  var folios = [Factura];
  arraypdf.push(folios);
  var form_data = new FormData();
  form_data.append('arraypdf', arraypdf);
  form_data.append('tipogeneracionpdf', 0);
  form_data.append('numerodecimalesdocumento', 2);
  form_data.append('imprimirdirectamente', 1);
  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url:facturas_generar_pdfs,
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
    if(campos[i] == 'Factura' || campos[i] == 'Status' || campos[i] == 'Periodo'){
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
function enterCondiciones(e) {
    if (e.keyCode === 13 || e.which === 13) {
        $('#observaciones').focus();
    }
}
init();

