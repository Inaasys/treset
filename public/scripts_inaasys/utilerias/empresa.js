'use strict'
function limpiarmodalesutileriasempresa(){
  $("#tabsformutileriasempresa").empty();
}
//limpiar todos los inputs del formulario alta
function limpiarutileriasempresa(){
  $("#formutileriasempresa")[0].reset();
  //Resetear las validaciones del formulario alta
  form = $("#formutileriasempresa");
  form.parsley().reset();
}
//mostrar modal formulario
function mostrarmodalformularioutileriasempresa(){
    $("#ModalUtileriasEmpresa").modal('show');
}
//ocultar modal formulario
function ocultarmodalformularioutileriasempresa(){
    $("#ModalUtileriasEmpresa").modal('hide');
}
function datosutileriasempresa(){
    $('.page-loader-wrapper').css('display', 'block');
    //formulario modificacion
    var form =  '<div class="modal-header bg-red" >'+
                    '<h4 class="modal-title" id="largeModalLabel">Datos Empresa</h4>'+
                '</div>'+
                '<form id="formutileriasempresa" action="#" enctype="multipart/form-data">'+
                    '<div class="modal-body">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<label>Nombre Comercial</label>'+
                                '<input type="text" class="form-control" name="nombrecomercialempresa" id="nombrecomercialempresa" value="'+empresautileriasEmpresa+'" required onkeyup="tipoLetra(this);">'+
                            '</div>   '+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<label>Razón Social Fiscal: </label>'+
                                '<input type="text" class="form-control" name="razonsocialempresa" id="razonsocialempresa" value="'+empresautileriasNombre+'" required onkeyup="tipoLetra(this);">'+
                            '</div>'+
                        '</div>'+
                        '<div class="col-md-12">'+
                            '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                                '<li role="presentation" class="active">'+
                                    '<a href="#domiciliofiscalempresa" data-toggle="tab">Domicilio Fiscal</a>'+
                                '</li>'+
                                '<li role="presentation">'+
                                    '<a href="#lugardeexpedicion" data-toggle="tab">Lugar de Expedición</a>'+
                                '</li>'+
                                '<li role="presentation">'+
                                    '<a href="#impuestos" data-toggle="tab">Impuestos</a>'+
                                '</li>'+
                                '<li role="presentation">'+
                                    '<a href="#configurar" data-toggle="tab">Configurar</a>'+
                                '</li>'+
                            '</ul>'+
                            '<div class="tab-content">'+
                                '<div role="tabpanel" class="tab-pane fade in active" id="domiciliofiscalempresa">'+
                                    '<div class="row">'+
                                        '<div class="col-md-4">'+
                                            '<label>RFC <b style="color:#F44336 !important;">*</b></label>'+
                                            '<input type="text" class="form-control" name="rfcempresa" id="rfcempresa" value="'+empresautileriasRfc+'" required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Calle <b style="color:#F44336 !important;">*</b></label>'+
                                            '<input type="text" class="form-control" name="calleempresa" id="calleempresa" value="'+empresautileriasCalle+'" required onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>No. Exterior <b style="color:#F44336 !important;">*</b></label>'+
                                            '<input type="text" class="form-control" name="noexteriorempresa" id="noexteriorempresa" value="'+empresautileriasNoExterior+'" required onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="row">'+
                                        '<div class="col-md-4">'+
                                            '<label>No. Interior</label>'+
                                            '<input type="text" class="form-control" name="nointeriorempresa" id="nointeriorempresa" value="'+empresautileriasNoInterior+'" onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Colonia <b style="color:#F44336 !important;">*</b></label>'+
                                            '<input type="text" class="form-control" name="coloniaempresa" id="coloniaempresa" value="'+empresautileriasColonia+'" required onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Localidad <b style="color:#F44336 !important;">*</b></label>'+
                                            '<input type="text" class="form-control" name="localidadempresa" id="localidadempresa" value="'+empresautileriasLocalidad+'" required onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="row">'+
                                        '<div class="col-md-4">'+
                                            '<label>Referencia</label>'+
                                            '<input type="text" class="form-control" name="referenciaempresa" id="referenciaempresa" value="'+empresautileriasReferencia+'" onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>País<b style="color:#F44336 !important;">*</b></label>'+
                                            '<div class="row">'+
                                            '<div class="col-md-4">'+
                                                '<span class="input-group-btn">'+
                                                '<div id="buscarpaises" class="btn bg-blue waves-effect" onclick="obtenerpaises()">Seleccionar</div>'+
                                                '</span>'+
                                            '</div>'+  
                                            '<div class="col-md-8">'+  
                                                '<div class="form-line">'+
                                                '<input type="text" class="form-control" name="empresautileriasnombrepais" id="empresautileriasnombrepais" value="'+empresautileriasPais+'" required readonly onkeyup="tipoLetra(this)">'+
                                                '<input type="hidden" class="form-control" name="empresautileriasnumeropais" id="empresautileriasnumeropais" required readonly>'+
                                                '</div>'+
                                            '</div>'+     
                                            '</div>'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Estado<b style="color:#F44336 !important;">*</b></label>'+
                                            '<div class="row">'+
                                            '<div class="col-md-4">'+
                                                '<span class="input-group-btn">'+
                                                '<div id="buscarestados" class="btn bg-blue waves-effect" onclick="obtenerestados()">Seleccionar</div>'+
                                                '</span>'+
                                            '</div>'+  
                                            '<div class="col-md-8">'+  
                                                '<div class="form-line">'+
                                                '<input type="text" class="form-control" name="empresautileriasnombreestado" id="empresautileriasnombreestado" value="'+empresautileriasEstado+'" required readonly onkeyup="tipoLetra(this)">'+
                                                '<input type="hidden" class="form-control" name="empresautileriasnumeroestado" id="empresautileriasnumeroestado" required readonly>'+
                                                '</div>'+
                                            '</div>'+     
                                            '</div>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="row">'+
                                        '<div class="col-md-4">'+
                                            '<label>Municipio<b style="color:#F44336 !important;">*</b></label>'+
                                            '<div class="row">'+
                                            '<div class="col-md-4">'+
                                                '<span class="input-group-btn">'+
                                                '<div id="buscarmunicipios" class="btn bg-blue waves-effect" onclick="obtenermunicipios()">Seleccionar</div>'+
                                                '</span>'+
                                            '</div>'+  
                                            '<div class="col-md-8">'+  
                                                '<div class="form-line">'+
                                                '<input type="text" class="form-control" name="empresautileriasnombremunicipio" id="empresautileriasnombremunicipio" value="'+empresautileriasMunicipio+'" required readonly onkeyup="tipoLetra(this)">'+
                                                '</div>'+
                                            '</div>'+     
                                            '</div>'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Teléfonos</label>'+
                                            '<input type="text" id="telefonosempresa" class="form-control " name="telefonosempresa"  value="'+empresautileriasTelefonos+'" onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Email</label>'+
                                            '<input type="text" class="form-control" name="emailempresa" id="emailempresa" value="'+empresautileriasEmail+'" onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="row">'+
                                        '<div class="col-md-6">'+
                                            '<label>Cambiar Logotipo</label>'+
                                            '<input type="file" name="logo" id="logo"  class="dropify" data-max-file-size="2M" data-allowed-file-extensions="png jpg jpeg gif" />'+
                                        '</div>'+
                                        '<div class="col-md-6">'+
                                            '<label>Logotipo Actual</label><br>'+
                                            '<img src="logotipo_empresa/'+empresautileriasLogo+'" id="logoactual" width="50%" height="50%"></img>'+
                                        '</div>'+
                                    '</div>   '+
                                '</div>'+
                                '<div role="tabpanel" class="tab-pane fade" id="lugardeexpedicion">'+
                                    '<div class="row">'+
                                        '<div class="col-md-12"><label>Para el Emisor: </label></div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Lugar de Expedición<b style="color:#F44336 !important;">*</b></label>'+
                                            '<select id="lugardeexpedicionempresa" class="form-control select2" name="lugardeexpedicionempresa" style="width: 100% !important;" required>'+
                                            '</select>'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Régimen Fiscal<b style="color:#F44336 !important;">*</b></label>'+
                                            '<select id="regimenfiscalempresa" class="form-control select2" name="regimenfiscalempresa" style="width: 100% !important;" required>'+
                                            '</select>'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Moneda<b style="color:#F44336 !important;">*</b></label>'+
                                            '<select id="monedaempresa" class="form-control select2" name="monedaempresa" style="width: 100% !important;" required>'+
                                            '</select>'+
                                        '</div>'+
                                    '</div> <br>'+
                                    '<div class="row">'+
                                        '<div class="col-md-12"><label>Para el Receptor o Cliente: </label></div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Método de Pago</label>'+
                                            '<select id="metododepagoempresa" class="form-control select2" name="metododepagoempresa" style="width: 100% !important;" required>'+
                                            '</select>'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Uso CFDI</label>'+
                                            '<select id="usocfdiempresa" class="form-control select2" name="usocfdiempresa" style="width: 100% !important;" required>'+
                                            '</select>'+
                                        '</div>'+
                                    '</div>'+                                                   
                                '</div>'+ 
                                '<div role="tabpanel" class="tab-pane fade" id="impuestos">'+
                                    '<div class="row">'+
                                        '<div class="col-md-12"><label>Impuestos Federales: </label></div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Impuesto al valor agregado I.V.A.  %</label>'+
                                            '<select class="form-control select2" name="ivaempresa" id="ivaempresa" style="width: 100% !important;">'+
                                                '<option selected disabled hidden>Selecciona</option>'+
                                                '<option value="16.000000">16.000000</option>'+
                                                '<option value="00.000000">00.000000</option>'+
                                            '</select>'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Aplicar Traslados IEPS</label>'+
                                            '<div class="col-md-12 form-check">'+
                                                '<input type="radio" name="aplicartrasladosimpuestosfederalesempresa" id="aplicartrasladosimpuestosfederalesempresa" value="S">'+
                                                '<label for="aplicartrasladosimpuestosfederalesempresa">SI</label>'+
                                                '<input type="radio" name="aplicartrasladosimpuestosfederalesempresa" id="aplicartrasladosimpuestosfederalesempresa1" value="N">'+
                                                '<label for="aplicartrasladosimpuestosfederalesempresa1">NO</label>'+
                                            '</div>'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Aplicar Retenciones ISR,IVA y IEPS</label>'+
                                            '<div class="col-md-12 form-check">'+
                                                '<input type="radio" name="aplicarretencionesimpuestosfederalesempresa" id="aplicarretencionesimpuestosfederalesempresa" value="S">'+
                                                '<label for="aplicarretencionesimpuestosfederalesempresa">SI</label>'+
                                                '<input type="radio" name="aplicarretencionesimpuestosfederalesempresa" id="aplicarretencionesimpuestosfederalesempresa1" value="N">'+
                                                '<label for="aplicarretencionesimpuestosfederalesempresa1">NO</label>'+
                                            '</div>'+
                                        '</div>'+
                                    '</div> <br>'+
                                    '<div class="row">'+
                                        '<div class="col-md-12"><label>Impuestos Locales: </label></div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Aplicar Retenciones ISR,IVA y CED</label>'+
                                            '<div class="col-md-12 form-check">'+
                                                '<input type="radio" name="aplicarretencionesimpuestoslocalesempresa" id="aplicarretencionesimpuestoslocalesempresa" value="S">'+
                                                '<label for="aplicarretencionesimpuestoslocalesempresa">SI</label>'+
                                                '<input type="radio" name="aplicarretencionesimpuestoslocalesempresa" id="aplicarretencionesimpuestoslocalesempresa1" value="N">'+
                                                '<label for="aplicarretencionesimpuestoslocalesempresa1">NO</label>'+
                                            '</div>'+
                                        '</div>'+
                                        '<div class="col-md-4">'+
                                            '<label>Aplicar Traslados ISH</label>'+
                                            '<div class="col-md-12 form-check">'+
                                                '<input type="radio" name="aplicartrasladosimpuestoslocalesempresa" id="aplicartrasladosimpuestoslocalesempresa" value="S">'+
                                                '<label for="aplicartrasladosimpuestoslocalesempresa">SI</label>'+
                                                '<input type="radio" name="aplicartrasladosimpuestoslocalesempresa" id="aplicartrasladosimpuestoslocalesempresa1" value="N">'+
                                                '<label for="aplicartrasladosimpuestoslocalesempresa1">NO</label>'+
                                            '</div>'+
                                        '</div>'+
                                    '</div>'+
                                '</div> '+
                                '<div role="tabpanel" class="tab-pane fade" id="configurar">'+
                                    '<div class="row">'+
                                        '<div class="table-container col-md-12">'+
                                            '<table class="scroll">'+
                                                '<thead class="customercolor">'+
                                                    '<tr>'+
                                                        '<th class="col-md-1">#</th>'+
                                                        '<th class="col-md-2">Módulos</th>'+
                                                        '<th class="col-md-5">Configurar Sistema</th>'+
                                                        '<th class="col-md-4">Opción</th>'+
                                                    '</tr>'+
                                                '</thead>'+
                                                '<tbody>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">1</td>'+
                                                        '<td class="col-md-2">Sistema</td>'+
                                                        '<td class="col-md-5">¿ Definir Paquete Sistema a Utilizar ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<select class="form-control select2" name="tipopaquetesistema" id="tipopaquetesistema" style="width: 100% !important;">'+
                                                                '<option selected disabled hidden>Selecciona</option>'+
                                                                '<option value="Administración">Administración</option>'+
                                                                '<option value="Facturación">Facturación</option>'+
                                                                '<option value="Punto de Venta">Punto de Venta</option>'+
                                                                '<option value="Automotríz" >Automotríz</option>'+
                                                            '</select>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">2</td>'+
                                                        '<td class="col-md-2">Sistema</td>'+
                                                        '<td class="col-md-5">¿ Visualizar Logotipo en los Documentos ?</td>'+
                                                        '<td  class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="visualizarlogotipoendocumentos" id="visualizarlogotipoendocumentos" value="S">'+
                                                                '<label for="visualizarlogotipoendocumentos">SI</label>'+
                                                                '<input type="radio" name="visualizarlogotipoendocumentos" id="visualizarlogotipoendocumentos1" value="N">'+
                                                                '<label for="visualizarlogotipoendocumentos1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">3</td>'+
                                                        '<td class="col-md-2">Sistema</td>'+
                                                        '<td class="col-md-5">¿ Utilizar Mayúsculas en el Sistema ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="utilizarmayusculasistema" id="utilizarmayusculasistema" value="S">'+
                                                                '<label for="utilizarmayusculasistema">SI</label>'+
                                                                '<input type="radio" name="utilizarmayusculasistema" id="utilizarmayusculasistema1" value="N">'+
                                                                '<label for="utilizarmayusculasistema1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">4</td>'+
                                                        '<td class="col-md-2">Sistema</td>'+
                                                        '<td class="col-md-5">¿ Unidad de Medidad en el Sistema (PIEZA, METRO, KILO, LITRO, PAR, PARES, ACT, NA) etc ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<input type="text" class="form-control" name="unidaddemedidasistema" id="unidaddemedidasistema">'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">5</td>'+
                                                        '<td class="col-md-2">Sistema</td>'+
                                                        '<td class="col-md-5">¿ Búsqueda de Tipo de Cambio en Banamex y DOF (Diario Oficial de la Federación) del Valor Dolar y Conversión a Moneda Predeterminada ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="busquedavalordolar" id="busquedavalordolar" value="S">'+
                                                                '<label for="busquedavalordolar">SI</label>'+
                                                                '<input type="radio" name="busquedavalordolar" id="busquedavalordolar1" value="N">'+
                                                                '<label for="busquedavalordolar1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">6</td>'+
                                                        '<td class="col-md-2">Compras</td>'+
                                                        '<td class="col-md-5">¿ Modificar el Costo en Productos de Ultima Compra ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="modificarcostoproductoultimacompra" id="modificarcostoproductoultimacompra" value="S">'+
                                                                '<label for="modificarcostoproductoultimacompra">SI</label>'+
                                                                '<input type="radio" name="modificarcostoproductoultimacompra" id="modificarcostoproductoultimacompra1" value="N">'+
                                                                '<label for="modificarcostoproductoultimacompra1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">7</td>'+
                                                        '<td class="col-md-2">Compras</td>'+
                                                        '<td class="col-md-5">¿ Tipo de Costo de la Compra por Guardar en el Catálogo de Productos (Costo Más Alto o Ultimo Costo) ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<select class="form-control select2" name="tipodecostoaguardarencatalogoproductos" id="tipodecostoaguardarencatalogoproductos" style="width: 100% !important;">'+
                                                                '<option selected disabled hidden>Selecciona</option>    '+
                                                                '<option value="UltimoCosto">UltimoCosto</option>'+
                                                                '<option value="CostoMasAlto">CostoMasAlto</option>'+
                                                            '</select>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">8</td>'+
                                                        '<td class="col-md-2">Compras</td>'+
                                                        '<td class="col-md-5">¿ Obligar UUID en documentos de Compras y Notas de Proveedor ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="obligaruuidendocumentoscompras" id="obligaruuidendocumentoscompras" value="S">'+
                                                                '<label for="obligaruuidendocumentoscompras">SI</label>'+
                                                                '<input type="radio" name="obligaruuidendocumentoscompras" id="obligaruuidendocumentoscompras1" value="N">'+
                                                                '<label for="obligaruuidendocumentoscompras1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">9</td>'+
                                                        '<td class="col-md-2">Compras</td>'+
                                                        '<td class="col-md-5">¿ Obligar CONTRARECIBO en pagos ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="obligarcontrareciboenpagos" id="obligarcontrareciboenpagos" value="S">'+
                                                                '<label for="obligarcontrareciboenpagos">SI</label>'+
                                                                '<input type="radio" name="obligarcontrareciboenpagos" id="obligarcontrareciboenpagos1" value="N">'+
                                                                '<label for="obligarcontrareciboenpagos1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">10</td>'+
                                                        '<td class="col-md-2">Compras</td>'+
                                                        '<td class="col-md-5">¿ Obligar Ingresar Orden de Compra Antes de Ingresar una Compra ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="obligaringresarordencompraantesdeingresarunacompra" id="obligaringresarordencompraantesdeingresarunacompra" value="S">'+
                                                                '<label for="obligaringresarordencompraantesdeingresarunacompra">SI</label>'+
                                                                '<input type="radio" name="obligaringresarordencompraantesdeingresarunacompra" id="obligaringresarordencompraantesdeingresarunacompra1" value="N">'+
                                                                '<label for="obligaringresarordencompraantesdeingresarunacompra1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">11</td>'+
                                                        '<td class="col-md-2">Ventas</td>'+
                                                        '<td class="col-md-5">¿ Proteger la Utilidad de Venta Relacionado con Porcentajes de Marcas y NO Permitir Guardar Costo Cero en Documentos (remisiones, facturas, etc...) ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="protegerutilidadventa" id="protegerutilidadventa" value="S">'+
                                                                '<label for="protegerutilidadventa">SI</label>'+
                                                                '<input type="radio" name="protegerutilidadventa" id="protegerutilidadventa1" value="N">'+
                                                                '<label for="protegerutilidadventa1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">12</td>'+
                                                        '<td class="col-md-2">Ventas</td>'+
                                                        '<td class="col-md-5">¿ Tipo de Utilidad para Venta en Documentos (Financiera o Aritmética) ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<select class="form-control select2" name="tipoutilidadventa" id="tipoutilidadventa" style="width: 100% !important;">'+
                                                                '<option selected disabled hidden>Selecciona</option>'+    
                                                                '<option value="Financiera">Financiera</option>'+
                                                                '<option value="Aritmética">Aritmética</option>'+
                                                            '</select>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">13</td>'+
                                                        '<td class="col-md-2">Productos</td>'+
                                                        '<td class="col-md-5">¿ Utilizar Consecutivo de Códigos en Productos ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">14</td>'+
                                                        '<td class="col-md-2">Productos</td>'+
                                                        '<td class="col-md-5">¿ Guardar Documentos SIN Existencias ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="guardardocumentossinexistencias" id="guardardocumentossinexistencias" value="S">'+
                                                                '<label for="guardardocumentossinexistencias">SI</label>'+
                                                                '<input type="radio" name="guardardocumentossinexistencias" id="guardardocumentossinexistencias1" value="N">'+
                                                                '<label for="guardardocumentossinexistencias1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">15</td>'+
                                                        '<td class="col-md-2">Productos</td>'+
                                                        '<td class="col-md-5">¿ Mostrar Existencia de Productos en Ayudas ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="mostrarexistenciadeproductosenayudas" id="mostrarexistenciadeproductosenayudas" value="S">'+
                                                                '<label for="mostrarexistenciadeproductosenayudas">SI</label>'+
                                                                '<input type="radio" name="mostrarexistenciadeproductosenayudas" id="mostrarexistenciadeproductosenayudas1" value="N">'+
                                                                '<label for="mostrarexistenciadeproductosenayudas1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">16</td>'+
                                                        '<td class="col-md-2">Facturas</td>'+
                                                        '<td class="col-md-5">¿ Guardar al Timbrar Como Nombre de archivo del Comprobante uuid.xml, Caso Contrario se Guarda el Combrobante con el Nombre Folio-Serie.xml ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">17</td>'+
                                                        '<td class="col-md-2">Facturas</td>'+
                                                        '<td class="col-md-5">¿ Aplicar una Sola Nota de Crédito por Factura ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="aplicarsolounanotadecredito" id="aplicarsolounanotadecredito" value="S">'+
                                                                '<label for="aplicarsolounanotadecredito">SI</label>'+
                                                                '<input type="radio" name="aplicarsolounanotadecredito" id="aplicarsolounanotadecredito1" value="N">'+
                                                                '<label for="aplicarsolounanotadecredito1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">18</td>'+
                                                        '<td class="col-md-2">Facturas</td>'+
                                                        '<td class="col-md-5">¿ Mostrar Captura de Tallas en Facturas ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">19</td>'+
                                                        '<td class="col-md-2">Facturas</td>'+
                                                        '<td class="col-md-5">¿ Liberar Facturas Vencidas de Clientes ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="liberarfacturasvencidasdeclientes" id="liberarfacturasvencidasdeclientes" value="S">'+
                                                                '<label for="liberarfacturasvencidasdeclientes">SI</label>'+
                                                                '<input type="radio" name="liberarfacturasvencidasdeclientes" id="liberarfacturasvencidasdeclientes1" value="N">'+
                                                                '<label for="liberarfacturasvencidasdeclientes1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">20</td>'+
                                                        '<td class="col-md-2">Facturas</td>'+
                                                        '<td class="col-md-5">¿ Manejar Partes por Concepto en Facturas ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">21</td>'+
                                                        '<td class="col-md-2">Facturas</td>'+
                                                        '<td class="col-md-5">¿ Integrar Otra Base de Datos para Remisiones 0 = Normal, 1 = LS ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">22</td>'+
                                                        '<td class="col-md-2">Facturas</td>'+
                                                        '<td class="col-md-5">¿ Cadena de Conexión a Otra Base de Datos ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">23</td>'+
                                                        '<td class="col-md-2">Complementos</td>'+
                                                        '<td class="col-md-5">¿ Ingresar Complementos Comercio Exterior ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">24</td>'+
                                                        '<td class="col-md-2">Complementos</td>'+
                                                        '<td class="col-md-5">¿ Ingresar Complemento ledu ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">25</td>'+
                                                        '<td class="col-md-2">Complementos</td>'+
                                                        '<td class="col-md-5">¿ Ingresar Complemento Servicios Parciales de Construcción y Visualizar Cuenta Predial en Facturación ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">26</td>'+
                                                        '<td class="col-md-2">Punto de Venta</td>'+
                                                        '<td class="col-md-5">¿ Al ingresar una Compra de Modifican los Precios en Punto de Venta del Catálogo de Productos en Relacion a las Utilidades de Marcas ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">27</td>'+
                                                        '<td class="col-md-2">Punto de Venta</td>'+
                                                        '<td class="col-md-5">¿ Definir Precio Default en Punto de Venta (0 = No muestra precios para punto de venta en catálogo de productos) ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">28</td>'+
                                                        '<td class="col-md-2">Punto de Venta</td>'+
                                                        '<td class="col-md-5">¿ Solicitar Agente en Punto de Venta ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">29</td>'+
                                                        '<td class="col-md-2">Punto de Venta</td>'+
                                                        '<td class="col-md-5">¿ Solicitar Descuento % en Automático en Punto de Venta en Pantalla 1 ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">30</td>'+
                                                        '<td class="col-md-2">Contrarecibos</td>'+
                                                        '<td class="col-md-5">Para ContraRecibos: Días de Revisión</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">31</td>'+
                                                        '<td class="col-md-2">Contrarecibos</td>'+
                                                        '<td class="col-md-5">Para ContraRecibos: Días de Pago</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">32</td>'+
                                                        '<td class="col-md-2">Contrarecibos</td>'+
                                                        '<td class="col-md-5">Para las Facturas: Favor de Realizar Pago o Transferencia En</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">33</td>'+
                                                        '<td class="col-md-2">En Documentos</td>'+
                                                        '<td class="col-md-5">Cuando se Ingresa un Precio en Cotizaciones, Pedidos, Remisiones y Facturas, dicho Precio Desglosa el Iva, Ejemplo 100/1.16 = 86.2068, ¿ Utilizar Precios Netos ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">34</td>'+
                                                        '<td class="col-md-2">En Documentos</td>'+
                                                        '<td class="col-md-5">¿ Precios Libre (No con Utilidad de Marcas) ?</td>'+
                                                        '<td class="col-md-4"></td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td class="col-md-1">35</td>'+
                                                        '<td class="col-md-2">En Documentos</td>'+
                                                        '<td class="col-md-5">¿ Modificar Registro de Cualquier Fecha ?</td>'+
                                                        '<td class="col-md-4">'+
                                                            '<div class="col-md-12 form-check">'+
                                                                '<input type="radio" name="modificarregistrodecualquierfecha" id="modificarregistrodecualquierfecha" value="S">'+
                                                                '<label for="modificarregistrodecualquierfecha">SI</label>'+
                                                                '<input type="radio" name="modificarregistrodecualquierfecha" id="modificarregistrodecualquierfecha1" value="N">'+
                                                                '<label for="modificarregistrodecualquierfecha1">NO</label>'+
                                                            '</div>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                '</tbody>'+
                                            '</table>'+
                                        '</div>'+
                                    '</div>  '+                                                 
                                '</div> '+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="modal-footer">'+
                        '<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Salir</button>'+
                        '<div class="btn btn-success btn-sm"  onclick="guardar()">Guardar</div>'+
                    '</div>'+
                '</form>';

    $("#contenidoutileriasempresa").html(form);
    mostrarmodalformularioutileriasempresa();
    $('.page-loader-wrapper').css('display', 'none');
    $('.dropify').dropify({
        messages: {
            'default': 'Da click para seleccionar o arrastra el archivo',
            'replace': 'Arrastra y suelta o haz clic para reemplazar',
            'remove':  'Eliminar',
            'error':   'Vaya, sucedió algo mal'
        },
        error: {
            'fileSize': 'El tamaño maximo permitido es ({{ value }}).',
            'fileExtension': 'Extensiones permitidas ({{ value }}).'
        }
    });
}

function guardar(){
    var formData = new FormData($("#formutileriasempresa")[0]);
    var logotipo = $('#logo')[0].files[0];
    formData.append('logotipo', logotipo); 
    //var form = $("#formutileriasempresa");
    //if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:utilerias_empresa_guardar_modificacion,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                msj_datosguardadoscorrectamente();
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
    /*}else{
        form.parsley().validate();
    }*/
}

