'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
    campos_a_filtrar_en_busquedas();
    listar();
}


//cambiar url para exportar excel
function cambiarurlexportarexcel(){
    //colocar la sucursal seleccionada como parametro para exportar a excel
    var sucursal = $("#sucursal").val();
    var datosconexionsucursal = sucursal.split(",");
    $("#titulonombreexistenciassucursal").html("EXISTENCIAS - "+datosconexionsucursal[1]);
    $("#btnGenerarFormatoExcel").attr("href", urlgenerarformatoexcel+'?sucursal='+sucursal);
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
        ajax: {
            url: existencias_suc1_obtener,
            data: function (d) {
                d.sucursal = $("#sucursal").val();
            }
        },
        columns: campos_tabla,
        "drawCallback": function( data ) {
            $("#sumacostofiltrado").html(number_format(round(data.json.sumacosto, numerodecimales), numerodecimales, '.', ''));
            $("#sumasubtotalfiltrado").html(number_format(round(data.json.sumasubtotal, numerodecimales), numerodecimales, '.', ''));
            $("#sumaivafiltrado").html(number_format(round(data.json.sumaiva, numerodecimales), numerodecimales, '.', ''));
            $("#sumatotalfiltrado").html(number_format(round(data.json.sumatotal, numerodecimales), numerodecimales, '.', ''));
            $("#sumatotalcostoinventariofiltrado").html(number_format(round(data.json.sumatotalcostoinventario, numerodecimales), numerodecimales, '.', ''));
            $("#sumacostolistafiltrado").html(number_format(round(data.json.sumacostolista, numerodecimales), numerodecimales, '.', ''));
            $("#sumacostoventafiltrado").html(number_format(round(data.json.sumacostoventa, numerodecimales), numerodecimales, '.', ''));
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
          $buscar.focus();
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistado').DataTable().search( this.value ).draw();
                $(".inputbusquedageneral").val(""); 
              }
          });
        }
    });
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