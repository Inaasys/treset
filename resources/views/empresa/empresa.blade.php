@extends('plantilla_maestra')
@section('titulo')
  Perfil Empresa
@endsection
@section('additionals_css')
    @include('secciones.libreriascss')
@endsection
@section('content')
<section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-xs-12 col-sm-3">
                    <div class="card profile-card">
                        <div class="profile-header {{$empresa->background_forms_and_modals}}" id="infoprofile">&nbsp;</div>
                        <div class="profile-body">
                            <div class="image-area text-center">
                                <img id="perfillogotipoempresa" src="logotipo_empresa/{{$empresa->Logo}}" alt="Perfil Empresa" class="text-center"/>
                            </div>
                            <div class="content-area text-center">
                                <h4>{{$empresa->Empresa}}</h4>
                            </div>
                        </div>
                        <div class="profile-footer text-center">
                            <h5>RFC:{{$empresa->Rfc}}</h5>
                        </div>
                    </div>
                    <div class="card card-about-me">
                        <div class="header text-center {{$empresa->background_forms_and_modals}}" id="infoprofile1">
                            <h2>INFORMACIÓN</h2>
                        </div>
                        <div class="body">
                            <ul>
                                <li>
                                    <div class="title">
                                        <i class="material-icons">info</i>
                                        Dirección
                                    </div>
                                    <div class="content">
                                        {{$calleempresa}}, 
                                        {{$noexteriorempresa}}, 
                                        {{$coloniaempresa}}, 
                                        {{$cpempresa}}, 
                                        {{$municipioempresa}}, 
                                        {{$estadoempresa}} 
                                    </div>
                                </li>
                                <li>
                                    <div class="title">
                                        <i class="material-icons">phone</i>
                                        Teléfonos
                                        <div class="content">
                                            {{$telefonosempresa}}
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card card-about-me">
                        <div class="header text-center {{$empresa->background_forms_and_modals}}" id="infoprofile2">
                            <h2>USUARIO LOGUEADO</h2>
                        </div>
                        <div class="body">
                            <ul>
                                <li>
                                    <div class="title">
                                        <i class="material-icons">info</i>
                                        Usuario
                                    </div>
                                    <div class="content">
                                        Nombre: {{Auth::user()->name}} <br>
                                        Correo: {{Auth::user()->email}} <br>
                                        Usuario: {{Auth::user()->user}} <br>
                                        Rol: {{Auth::user()->role_id}} <br>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-9">
                    <div class="card">
                        <div class="body">
                            <div>
                                <ul class="nav nav-tabs tab-col-blue-grey" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#domiciliofiscal" data-toggle="tab">Domicilio Fiscal</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#lugardeexpedicion" data-toggle="tab">Lugar de Expedición</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#impuestos" data-toggle="tab">Impuestos</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#configurar" data-toggle="tab">Configurar</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#cambiar_contrasena" data-toggle="tab">Cambiar Contraseña</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#cambiar_logotipo" data-toggle="tab">Cambiar Logotipo</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane fade in active" id="domiciliofiscal">
                                        <form id="formdomiciliofiscal" action="#">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Nombre Comercial</label>
                                                    <input type="text" class="form-control" name="nombrecomercialempresa" id="nombrecomercialempresa" value="{{$empresa->Empresa}}" required onkeyup="tipoLetra(this);">
                                                </div>   
                                                <div class="col-md-6">
                                                    <label>Razón Social Fiscal: </label>
                                                    <input type="text" class="form-control" name="razonsocialempresa" id="razonsocialempresa" value="{{$empresa->Nombre}}" required onkeyup="tipoLetra(this);">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label>RFC <b style="color:#F44336 !important;">*</b></label>
                                                    <input type="text" class="form-control" name="rfcempresa" id="rfcempresa" value="{{$empresa->Rfc}}" required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" onkeyup="tipoLetra(this);mayusculas(this);">
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Calle <b style="color:#F44336 !important;">*</b></label>
                                                    <input type="text" class="form-control" name="calleempresa" id="calleempresa" value="{{$calleempresa}}" required onkeyup="tipoLetra(this);">
                                                </div>
                                                <div class="col-md-4">
                                                    <label>No. Exterior <b style="color:#F44336 !important;">*</b></label>
                                                    <input type="text" class="form-control" name="noexteriorempresa" id="noexteriorempresa" value="{{$noexteriorempresa}}" required onkeyup="tipoLetra(this);">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label>No. Interior</label>
                                                    <input type="text" class="form-control" name="nointeriorempresa" id="nointeriorempresa" value="{{$nointeriorempresa}}" onkeyup="tipoLetra(this);">
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Colonia <b style="color:#F44336 !important;">*</b></label>
                                                    <input type="text" class="form-control" name="coloniaempresa" id="coloniaempresa" value="{{$coloniaempresa}}" required onkeyup="tipoLetra(this);">
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Localidad <b style="color:#F44336 !important;">*</b></label>
                                                    <input type="text" class="form-control" name="localidadempresa" id="localidadempresa" value="{{$localidadempresa}}" required onkeyup="tipoLetra(this);">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label>Referencia</label>
                                                    <input type="text" class="form-control" name="referenciaempresa" id="referenciaempresa" value="{{$referenciaempresa}}" onkeyup="tipoLetra(this);">
                                                </div>
                                                <div class="col-md-4">
                                                    <label>País<b style="color:#F44336 !important;">*</b></label>
                                                    <div class="row">
                                                    <div class="col-md-4">
                                                        <span class="input-group-btn">
                                                        <div id="buscarpaises" class="btn bg-blue waves-effect" onclick="obtenerpaises()">Seleccionar</div>
                                                        </span>
                                                    </div>  
                                                    <div class="col-md-8">  
                                                        <div class="form-line">
                                                        <input type="text" class="form-control" name="empresanombrepais" id="empresanombrepais" value="{{$paisempresa}}" required readonly onkeyup="tipoLetra(this)">
                                                        <input type="hidden" class="form-control" name="empresanumeropais" id="empresanumeropais" required readonly>
                                                        </div>
                                                    </div>     
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Estado<b style="color:#F44336 !important;">*</b></label>
                                                    <div class="row">
                                                    <div class="col-md-4">
                                                        <span class="input-group-btn">
                                                        <div id="buscarestados" class="btn bg-blue waves-effect" onclick="obtenerestados()">Seleccionar</div>
                                                        </span>
                                                    </div>  
                                                    <div class="col-md-8">  
                                                        <div class="form-line">
                                                        <input type="text" class="form-control" name="empresanombreestado" id="empresanombreestado" value="{{$estadoempresa}}" required readonly onkeyup="tipoLetra(this)">
                                                        <input type="hidden" class="form-control" name="empresanumeroestado" id="empresanumeroestado" required readonly>
                                                        </div>
                                                    </div>     
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label>Municipio<b style="color:#F44336 !important;">*</b></label>
                                                    <div class="row">
                                                    <div class="col-md-4">
                                                        <span class="input-group-btn">
                                                        <div id="buscarmunicipios" class="btn bg-blue waves-effect" onclick="obtenermunicipios()">Seleccionar</div>
                                                        </span>
                                                    </div>  
                                                    <div class="col-md-8">  
                                                        <div class="form-line">
                                                        <input type="text" class="form-control" name="empresanombremunicipio" id="empresanombremunicipio" value="{{$municipioempresa}}" required readonly onkeyup="tipoLetra(this)">
                                                        </div>
                                                    </div>     
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Teléfonos</label>
                                                    <input type="text" id="telefonosempresa" class="form-control " name="telefonosempresa"  value="{{$telefonosempresa}}" onkeyup="tipoLetra(this);">
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Email</label>
                                                    <input type="text" class="form-control" name="emailempresa" id="emailempresa" value="{{$emailempresa}}" data-parsley-type="email" onkeyup="tipoLetra(this);">
                                                </div>
                                            </div>  
                                            <div class="row">    
                                                <div class="col-md-2 col-md-offset-10">
                                                    <button type="button" class="btn bg-green btn-block waves-effect" id="btnguardardomiciliofiscal">Guardar Cambios</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade" id="lugardeexpedicion">
                                        <form id="formlugarexpedicion" action="#">
                                            <div class="row">
                                                <div class="col-md-12"><label>Para el Emisor: </label></div>
                                                <div class="col-md-4">
                                                    <label>Lugar de Expedición<b style="color:#F44336 !important;">*</b></label>
                                                    <div class="row">
                                                    <div class="col-md-4">
                                                        <span class="input-group-btn">
                                                        <div id="buscarlugarexpedicion" class="btn bg-blue waves-effect" onclick="obtenerlugaresexpedicion()">Seleccionar</div>
                                                        </span>
                                                    </div>  
                                                    <div class="col-md-8">  
                                                        <div class="form-line">
                                                        <input type="text" class="form-control" name="empresalugarexpedicion" id="empresalugarexpedicion" value="{{$lugarexpedicion}}" required readonly onkeyup="tipoLetra(this)">
                                                        </div>
                                                    </div>     
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Régimen Fiscal<b style="color:#F44336 !important;">*</b></label>
                                                    <div class="row">
                                                    <div class="col-md-4">
                                                        <span class="input-group-btn">
                                                        <div id="buscarregimenesfiscales" class="btn bg-blue waves-effect" onclick="obtenerregimenesfiscales()">Seleccionar</div>
                                                        </span>
                                                    </div>  
                                                    <div class="col-md-8">  
                                                        <div class="form-line">
                                                        <input type="text" class="form-control" name="empresaregimenfiscal" id="empresaregimenfiscal" value="{{$regimenfiscal}}" required readonly onkeyup="tipoLetra(this)">
                                                        </div>
                                                    </div>     
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Moneda<b style="color:#F44336 !important;">*</b></label>
                                                    <div class="row">
                                                    <div class="col-md-4">
                                                        <span class="input-group-btn">
                                                        <div id="buscarmonedas" class="btn bg-blue waves-effect" onclick="obtenermonedas()">Seleccionar</div>
                                                        </span>
                                                    </div>  
                                                    <div class="col-md-8">  
                                                        <div class="form-line">
                                                        <input type="text" class="form-control" name="empresamoneda" id="empresamoneda" value="{{$municipioempresa}}" required readonly onkeyup="tipoLetra(this)">
                                                        </div>
                                                    </div>     
                                                    </div>
                                                </div>
                                            </div>    
                                            <div class="row">    
                                                <div class="col-md-2 col-md-offset-10">
                                                    <button type="button" class="btn bg-green btn-block waves-effect" id="btnguardarlugarexpedicion">Guardar Cambios</button>
                                                </div>
                                            </div>  
                                        </form>                                           
                                    </div> 
                                    <div role="tabpanel" class="tab-pane fade" id="impuestos">
                                        <div class="row">
                                            <div class="col-md-12"><label>Impuestos Federales: </label></div>
                                            <div class="col-md-4">
                                                <label>Impuesto al valor agregado I.V.A.  %</label>
                                                <select class="form-control select2" name="ivaempresa" id="ivaempresa" style="width: 100% !important;">
                                                    <option selected disabled hidden>Selecciona</option>
                                                    <option value="16.000000">16.000000</option>
                                                    <option value="00.000000">00.000000</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Aplicar Traslados IEPS</label>
                                                <div class="col-md-12 form-check">
                                                    <input type="radio" name="aplicartrasladosimpuestosfederalesempresa" id="aplicartrasladosimpuestosfederalesempresa" value="S">
                                                    <label for="aplicartrasladosimpuestosfederalesempresa">SI</label>
                                                    <input type="radio" name="aplicartrasladosimpuestosfederalesempresa" id="aplicartrasladosimpuestosfederalesempresa1" value="N">
                                                    <label for="aplicartrasladosimpuestosfederalesempresa1">NO</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Aplicar Retenciones ISR,IVA y IEPS</label>
                                                <div class="col-md-12 form-check">
                                                    <input type="radio" name="aplicarretencionesimpuestosfederalesempresa" id="aplicarretencionesimpuestosfederalesempresa" value="S">
                                                    <label for="aplicarretencionesimpuestosfederalesempresa">SI</label>
                                                    <input type="radio" name="aplicarretencionesimpuestosfederalesempresa" id="aplicarretencionesimpuestosfederalesempresa1" value="N">
                                                    <label for="aplicarretencionesimpuestosfederalesempresa1">NO</label>
                                                </div>
                                            </div>
                                        </div> <br>
                                        <div class="row">
                                            <div class="col-md-12"><label>Impuestos Locales: </label></div>
                                            <div class="col-md-4">
                                                <label>Aplicar Retenciones ISR,IVA y CED</label>
                                                <div class="col-md-12 form-check">
                                                    <input type="radio" name="aplicarretencionesimpuestoslocalesempresa" id="aplicarretencionesimpuestoslocalesempresa" value="S">
                                                    <label for="aplicarretencionesimpuestoslocalesempresa">SI</label>
                                                    <input type="radio" name="aplicarretencionesimpuestoslocalesempresa" id="aplicarretencionesimpuestoslocalesempresa1" value="N">
                                                    <label for="aplicarretencionesimpuestoslocalesempresa1">NO</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Aplicar Traslados ISH</label>
                                                <div class="col-md-12 form-check">
                                                    <input type="radio" name="aplicartrasladosimpuestoslocalesempresa" id="aplicartrasladosimpuestoslocalesempresa" value="S">
                                                    <label for="aplicartrasladosimpuestoslocalesempresa">SI</label>
                                                    <input type="radio" name="aplicartrasladosimpuestoslocalesempresa" id="aplicartrasladosimpuestoslocalesempresa1" value="N">
                                                    <label for="aplicartrasladosimpuestoslocalesempresa1">NO</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">    
                                            <div class="col-md-2 col-md-offset-10">
                                                <button type="submit" class="btn bg-green btn-block waves-effect" id="btnguardarimpuestos">Guardar Cambios</button>
                                            </div>
                                        </div>
                                    </div> 
                                    <div role="tabpanel" class="tab-pane fade" id="configurar">
                                        <form id="formconfigurar" action="#">
                                            <div class="row">
                                                <div class="table-container col-md-12">
                                                    <table class="scroll">
                                                        <thead class="{{$empresa->background_tables}}">
                                                            <tr>
                                                                <th class="col-md-1">#</th>
                                                                <th class="col-md-2">Módulos</th>
                                                                <th class="col-md-5">Configurar Sistema</th>
                                                                <th class="col-md-4">Opción</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="col-md-1">1</td>
                                                                <td class="col-md-2">Sistema</td>
                                                                <td class="col-md-5">¿ Definir Paquete Sistema a Utilizar ?</td>
                                                                <td class="col-md-4">
                                                                    <select class="form-control select2" name="tipopaquetesistema" id="tipopaquetesistema" style="width: 100% !important;">
                                                                        <option selected disabled hidden>Selecciona</option>
                                                                        <option value="Administración">Administración</option>
                                                                        <option value="Facturación">Facturación</option>
                                                                        <option value="Punto de Venta">Punto de Venta</option>
                                                                        <option value="Automotríz" >Automotríz</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">2</td>
                                                                <td class="col-md-2">Sistema</td>
                                                                <td class="col-md-5">¿ Visualizar Logotipo en los Documentos ?</td>
                                                                <td  class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="visualizarlogotipoendocumentos" id="visualizarlogotipoendocumentos" value="S">
                                                                        <label for="visualizarlogotipoendocumentos">SI</label>
                                                                        <input type="radio" name="visualizarlogotipoendocumentos" id="visualizarlogotipoendocumentos1" value="N">
                                                                        <label for="visualizarlogotipoendocumentos1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">3</td>
                                                                <td class="col-md-2">Sistema</td>
                                                                <td class="col-md-5">¿ Numero de Decimales ?</td>
                                                                <td class="col-md-4">
                                                                    <select class="form-control select2" name="numerodecimalessistema" id="numerodecimalessistema" style="width: 100% !important;">
                                                                        <option selected disabled hidden>Selecciona</option>
                                                                        <option value="1">1</option>
                                                                        <option value="2">2</option>
                                                                        <option value="3">3</option>
                                                                        <option value="4">4</option>
                                                                        <option value="5">5</option>
                                                                        <option value="6">6</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">4</td>
                                                                <td class="col-md-2">Sistema</td>
                                                                <td class="col-md-5">¿ Numero de Decimales en Documentos PDF ?</td>
                                                                <td class="col-md-4">
                                                                    <select class="form-control select2" name="numerodecilamesdocumentospdfsistema" id="numerodecilamesdocumentospdfsistema" style="width: 100% !important;">
                                                                        <option selected disabled hidden>Selecciona</option>
                                                                        <option value="1">1</option>
                                                                        <option value="2">2</option>
                                                                        <option value="3">3</option>
                                                                        <option value="4">4</option>
                                                                        <option value="5">5</option>
                                                                        <option value="6">6</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">5</td>
                                                                <td class="col-md-2">Sistema</td>
                                                                <td class="col-md-5">¿ Utilizar Mayúsculas en el Sistema ?</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="utilizarmayusculasistema" id="utilizarmayusculasistema" value="S">
                                                                        <label for="utilizarmayusculasistema">SI</label>
                                                                        <input type="radio" name="utilizarmayusculasistema" id="utilizarmayusculasistema1" value="N">
                                                                        <label for="utilizarmayusculasistema1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">6</td>
                                                                <td class="col-md-2">Sistema</td>
                                                                <td class="col-md-5">¿ Unidad de Medidad en el Sistema (PIEZA, METRO, KILO, LITRO, PAR, PARES, ACT, NA) etc ?</td>
                                                                <td class="col-md-4">
                                                                    <input type="text" class="form-control" name="unidaddemedidasistema" id="unidaddemedidasistema">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">7</td>
                                                                <td class="col-md-2">Sistema</td>
                                                                <td class="col-md-5">¿ Búsqueda de Tipo de Cambio en Banamex y DOF (Diario Oficial de la Federación) del Valor Dolar y Conversión a Moneda Predeterminada ?</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="busquedavalordolar" id="busquedavalordolar" value="S">
                                                                        <label for="busquedavalordolar">SI</label>
                                                                        <input type="radio" name="busquedavalordolar" id="busquedavalordolar1" value="N">
                                                                        <label for="busquedavalordolar1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">8</td>
                                                                <td class="col-md-2">Compras</td>
                                                                <td class="col-md-5">¿ Modificar el Costo en Productos de Ultima Compra ?</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="modificarcostoproductoultimacompra" id="modificarcostoproductoultimacompra" value="S">
                                                                        <label for="modificarcostoproductoultimacompra">SI</label>
                                                                        <input type="radio" name="modificarcostoproductoultimacompra" id="modificarcostoproductoultimacompra1" value="N">
                                                                        <label for="modificarcostoproductoultimacompra1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">9</td>
                                                                <td class="col-md-2">Compras</td>
                                                                <td class="col-md-5">¿ Tipo de Costo de la Compra por Guardar en el Catálogo de Productos (Costo Más Alto o Ultimo Costo) ?</td>
                                                                <td class="col-md-4">
                                                                    <select class="form-control select2" name="tipodecostoaguardarencatalogoproductos" id="tipodecostoaguardarencatalogoproductos" style="width: 100% !important;">
                                                                        <option selected disabled hidden>Selecciona</option>    
                                                                        <option value="UltimoCosto">UltimoCosto</option>
                                                                        <option value="CostoMasAlto">CostoMasAlto</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">10</td>
                                                                <td class="col-md-2">Compras</td>
                                                                <td class="col-md-5">¿ Obligar UUID en documentos de Compras y Notas de Proveedor ?</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="obligaruuidendocumentoscompras" id="obligaruuidendocumentoscompras" value="S">
                                                                        <label for="obligaruuidendocumentoscompras">SI</label>
                                                                        <input type="radio" name="obligaruuidendocumentoscompras" id="obligaruuidendocumentoscompras1" value="N">
                                                                        <label for="obligaruuidendocumentoscompras1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">11</td>
                                                                <td class="col-md-2">Compras</td>
                                                                <td class="col-md-5">¿ Obligar CONTRARECIBO en pagos ?</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="obligarcontrareciboenpagos" id="obligarcontrareciboenpagos" value="S">
                                                                        <label for="obligarcontrareciboenpagos">SI</label>
                                                                        <input type="radio" name="obligarcontrareciboenpagos" id="obligarcontrareciboenpagos1" value="N">
                                                                        <label for="obligarcontrareciboenpagos1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">12</td>
                                                                <td class="col-md-2">Ventas</td>
                                                                <td class="col-md-5">¿ Proteger la Utilidad de Venta Relacionado con Porcentajes de Marcas y NO Permitir Guardar Costo Cero en Documentos (remisiones, facturas, etc...) ?</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="protegerutilidadventa" id="protegerutilidadventa" value="S">
                                                                        <label for="protegerutilidadventa">SI</label>
                                                                        <input type="radio" name="protegerutilidadventa" id="protegerutilidadventa1" value="N">
                                                                        <label for="protegerutilidadventa1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">13</td>
                                                                <td class="col-md-2">Ventas</td>
                                                                <td class="col-md-5">¿ Tipo de Utilidad para Venta en Documentos (Financiera o Aritmética) ?</td>
                                                                <td class="col-md-4">
                                                                    <select class="form-control select2" name="tipoutilidadventa" id="tipoutilidadventa" style="width: 100% !important;">
                                                                        <option selected disabled hidden>Selecciona</option>    
                                                                        <option value="Financiera">Financiera</option>
                                                                        <option value="Aritmética">Aritmética</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">14</td>
                                                                <td class="col-md-2">Productos</td>
                                                                <td class="col-md-5">¿ Utilizar Consecutivo de Códigos en Productos ?</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="guardardocumentossinexistencias" id="guardardocumentossinexistencias" value="S">
                                                                        <label for="guardardocumentossinexistencias">SI</label>
                                                                        <input type="radio" name="guardardocumentossinexistencias" id="guardardocumentossinexistencias1" value="N">
                                                                        <label for="guardardocumentossinexistencias1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">15</td>
                                                                <td class="col-md-2">Productos</td>
                                                                <td class="col-md-5">¿ Guardar Documentos SIN Existencias ?</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="guardardocumentossinexistencias" id="guardardocumentossinexistencias" value="S">
                                                                        <label for="guardardocumentossinexistencias">SI</label>
                                                                        <input type="radio" name="guardardocumentossinexistencias" id="guardardocumentossinexistencias1" value="N">
                                                                        <label for="guardardocumentossinexistencias1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">16</td>
                                                                <td class="col-md-2">Facturas</td>
                                                                <td class="col-md-5">¿ Aplicar una Sola Nota de Crédito por Factura ?</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="aplicarsolounanotadecredito" id="aplicarsolounanotadecredito" value="S">
                                                                        <label for="aplicarsolounanotadecredito">SI</label>
                                                                        <input type="radio" name="aplicarsolounanotadecredito" id="aplicarsolounanotadecredito1" value="N">
                                                                        <label for="aplicarsolounanotadecredito1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">17</td>
                                                                <td class="col-md-2">Facturas</td>
                                                                <td class="col-md-5">¿ Liberar Facturas Vencidas de Clientes ?</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="liberarfacturasvencidasdeclientes" id="liberarfacturasvencidasdeclientes" value="S">
                                                                        <label for="liberarfacturasvencidasdeclientes">SI</label>
                                                                        <input type="radio" name="liberarfacturasvencidasdeclientes" id="liberarfacturasvencidasdeclientes1" value="N">
                                                                        <label for="liberarfacturasvencidasdeclientes1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">18</td>
                                                                <td class="col-md-2">Contrarecibos</td>
                                                                <td class="col-md-5">Para las Facturas: Favor de Realizar Pago o Transferencia En</td>
                                                                <td class="col-md-4"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">19</td>
                                                                <td class="col-md-2">En Documentos</td>
                                                                <td class="col-md-5">Cuando se Ingresa un Precio en Cotizaciones, Pedidos, Remisiones y Facturas, dicho Precio Desglosa el Iva, Ejemplo 100/1.16 = 86.2068, ¿ Utilizar Precios Netos ?</td>
                                                                <td class="col-md-4"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">20</td>
                                                                <td class="col-md-2">En Documentos</td>
                                                                <td class="col-md-5">¿ Precios Libre (No con Utilidad de Marcas) ?</td>
                                                                <td class="col-md-4"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-1">21</td>
                                                                <td class="col-md-2">En Documentos</td>
                                                                <td class="col-md-5">¿ Modificar Registro de Cualquier Fecha ?</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="modificarregistrodecualquierfecha" id="modificarregistrodecualquierfecha" value="S">
                                                                        <label for="modificarregistrodecualquierfecha">SI</label>
                                                                        <input type="radio" name="modificarregistrodecualquierfecha" id="modificarregistrodecualquierfecha1" value="N">
                                                                        <label for="modificarregistrodecualquierfecha1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>  
                                            <div class="row">    
                                                <div class="col-md-2 col-md-offset-10">
                                                    <button type="submit" class="btn bg-green btn-block waves-effect" id="btnguardarconfigurar">Guardar Cambios</button>
                                                </div>
                                            </div>     
                                        </form>                                            
                                    </div> 
                                    <div role="tabpanel" class="tab-pane fade" id="cambiar_contrasena">
                                        <form id="formcambiarcontrasena" action="#">
                                            <div class="row">
                                                <div class="col-md-6" >
                                                    <label>Nueva Contraseña Usuario<b style="color:#F44336 !important;">*</b></label>
                                                    <input type="text" class="form-control" name="email" id="email" value="{{Auth::user()->email}}" autocomplete="email" required data-parsley-type="email" style="display: none;">
                                                    <input type="password" class="form-control" name="pass" id="pass" required autocomplete="new-password" data-parsley-regexsafepassword="/^(?=.{8,}$)(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$/">
                                                </div>
                                                <div class="col-md-6" >
                                                    <label>Confirmar Nueva Contraseña Usuario<b style="color:#F44336 !important;">*</b></label>
                                                    <input type="password" class="form-control" name="confirmarpass" id="confirmarpass"  required autocomplete="new-password" data-parsley-equalto="#pass">
                                                </div>
                                            </div>
                                            <div class="row">    
                                                <div class="col-md-2 col-md-offset-10">
                                                    <button type="submit" class="btn bg-green btn-block waves-effect" id="btnguardarcontrasena">Guardar Cambios</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div> 
                                    <div role="tabpanel" class="tab-pane fade" id="cambiar_logotipo">
                                        <form id="formlogotipo" action="#" enctype="multipart/form-data">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label>Cambiar Logotipo</label>
                                                    <input type="file" name="logo" id="logo"  class="dropify" data-max-file-size="2M" data-allowed-file-extensions="png jpg jpeg gif"/>
                                                </div>
                                            </div> 
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label>Selecciona el color para el menu</label>
                                                    <select name="selectcolornavbar" id="selectcolornavbar" class="form-control" onchange="setcolornavbar()">
                                                        <option value="bg-red" class="bg-red">bg-red</option>
                                                        <option value="bg-pink" class="bg-pink">bg-pink</option>
                                                        <option value="bg-purple" class="bg-purple">bg-purple</option>
                                                        <option value="bg-deep-purple" class="bg-deep-purple">bg-deep-purple</option>
                                                        <option value="bg-indigo" class="bg-indigo">bg-indigo</option>
                                                        <option value="bg-blue" class="bg-blue">bg-blue</option>
                                                        <option value="bg-light-blue" class="bg-light-blue">bg-light-blue</option>
                                                        <option value="bg-cyan" class="bg-cyan">bg-cyan</option>
                                                        <option value="bg-teal" class="bg-teal">bg-teal</option>
                                                        <option value="bg-green" class="bg-green">bg-green</option>
                                                        <option value="bg-light-green" class="bg-light-green">bg-light-green</option>
                                                        <option value="bg-lime" class="bg-lime">bg-lime</option>
                                                        <option value="bg-yellow" class="bg-yellow">bg-yellow</option>
                                                        <option value="bg-amber" class="bg-amber">bg-amber</option>
                                                        <option value="bg-orange" class="bg-orange">bg-orange</option>
                                                        <option value="bg-deep-orange" class="bg-deep-orange">bg-deep-orange</option>
                                                        <option value="bg-brown" class="bg-brown">bg-brown</option>
                                                        <option value="bg-grey" class="bg-grey">bg-grey</option>
                                                        <option value="bg-blue-grey" class="bg-blue-grey">bg-blue-grey</option>
                                                        <option value="bg-black" class="bg-black">bg-black</option>
                                                    </select>
                                                </div>
                                            </div><br>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label>Selecciona el color para encabezados formularios y ventanas emergentes</label><br>
                                                    <select name="selectcolorformsandmodals" id="selectcolorformsandmodals" class="form-control" onchange="setcolorformsandmodals()">
                                                        <option value="bg-red" class="bg-red">bg-red</option>
                                                        <option value="bg-pink" class="bg-pink">bg-pink</option>
                                                        <option value="bg-purple" class="bg-purple">bg-purple</option>
                                                        <option value="bg-deep-purple" class="bg-deep-purple">bg-deep-purple</option>
                                                        <option value="bg-indigo" class="bg-indigo">bg-indigo</option>
                                                        <option value="bg-blue" class="bg-blue">bg-blue</option>
                                                        <option value="bg-light-blue" class="bg-light-blue">bg-light-blue</option>
                                                        <option value="bg-cyan" class="bg-cyan">bg-cyan</option>
                                                        <option value="bg-teal" class="bg-teal">bg-teal</option>
                                                        <option value="bg-green" class="bg-green">bg-green</option>
                                                        <option value="bg-light-green" class="bg-light-green">bg-light-green</option>
                                                        <option value="bg-lime" class="bg-lime">bg-lime</option>
                                                        <option value="bg-yellow" class="bg-yellow">bg-yellow</option>
                                                        <option value="bg-amber" class="bg-amber">bg-amber</option>
                                                        <option value="bg-orange" class="bg-orange">bg-orange</option>
                                                        <option value="bg-deep-orange" class="bg-deep-orange">bg-deep-orange</option>
                                                        <option value="bg-brown" class="bg-brown">bg-brown</option>
                                                        <option value="bg-grey" class="bg-grey">bg-grey</option>
                                                        <option value="bg-blue-grey" class="bg-blue-grey">bg-blue-grey</option>
                                                        <option value="bg-black" class="bg-black">bg-black</option>
                                                    </select>
                                                </div>
                                            </div><br>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label>Selecciona el color para encabezados tablas</label><br>
                                                    <select name="selectcolortables" id="selectcolortables" class="form-control">
                                                        <option value="bg-red" class="bg-red">bg-red</option>
                                                        <option value="bg-pink" class="bg-pink">bg-pink</option>
                                                        <option value="bg-purple" class="bg-purple">bg-purple</option>
                                                        <option value="bg-deep-purple" class="bg-deep-purple">bg-deep-purple</option>
                                                        <option value="bg-indigo" class="bg-indigo">bg-indigo</option>
                                                        <option value="bg-blue" class="bg-blue">bg-blue</option>
                                                        <option value="bg-light-blue" class="bg-light-blue">bg-light-blue</option>
                                                        <option value="bg-cyan" class="bg-cyan">bg-cyan</option>
                                                        <option value="bg-teal" class="bg-teal">bg-teal</option>
                                                        <option value="bg-green" class="bg-green">bg-green</option>
                                                        <option value="bg-light-green" class="bg-light-green">bg-light-green</option>
                                                        <option value="bg-lime" class="bg-lime">bg-lime</option>
                                                        <option value="bg-yellow" class="bg-yellow">bg-yellow</option>
                                                        <option value="bg-amber" class="bg-amber">bg-amber</option>
                                                        <option value="bg-orange" class="bg-orange">bg-orange</option>
                                                        <option value="bg-deep-orange" class="bg-deep-orange">bg-deep-orange</option>
                                                        <option value="bg-brown" class="bg-brown">bg-brown</option>
                                                        <option value="bg-grey" class="bg-grey">bg-grey</option>
                                                        <option value="bg-blue-grey" class="bg-blue-grey">bg-blue-grey</option>
                                                        <option value="bg-black" class="bg-black">bg-black</option>
                                                    </select>
                                                </div>
                                            </div><br>
                                            <div class="row">    
                                                <div class="col-md-2 col-md-offset-10">
                                                    <button type="button" class="btn bg-green btn-block waves-effect" id="btnguardarlogotipo">Guardar Cambios</button>
                                                </div>
                                            </div>    
                                        </div>                                          
                                    </div> 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Modal Tablas Seleccion-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="ModalTablas" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div id="contenidomodaltablas">
                <!-- aqui van las tablas de seleccion y se agregan automaticamente con jquery -->
            </div> 
        </div>
    </div>
</div>

@endsection
@section('additionals_js')
    <script>
        /*urls y variables renderizadas con blade*/
        var mayusculas_sistema = '{{$mayusculas_sistema}}';
        var numerodecimales = '{{$numerodecimales}}';
        var numerodecimalesendocumentos = '{{$numerodecimalesendocumentos}}';
        var numerocerosconfigurados = '{{$numerocerosconfigurados}}';
        var numerocerosconfiguradosinputnumberstep = '{{$numerocerosconfiguradosinputnumberstep}}';
        var colornavbar = '{{$empresa->background_navbar}}';
        var colormodalsandforms = '{{$empresa->background_forms_and_modals}}';
        var colortables = '{{$empresa->background_tables}}';
        var urllogotipos = '{{asset("logotipo_empresa/")}}/';
        var background_navbar = '{{$empresa->background_navbar}}';
        var background_forms_and_modals = '{{$empresa->background_forms_and_modals}}';
        var background_tables = '{{$empresa->background_tables}}';
        var empresa_obtener_paises = '{!!URL::to('empresa_obtener_paises')!!}';
        var empresa_obtener_estados = '{!!URL::to('empresa_obtener_estados')!!}';
        var empresa_obtener_municipios = '{!!URL::to('empresa_obtener_municipios')!!}';
        var empresa_obtener_lugares_expedicion = '{!!URL::to('empresa_obtener_lugares_expedicion')!!}';
        var empresa_obtener_regimenes_fiscales = '{!!URL::to('empresa_obtener_regimenes_fiscales')!!}';
        var empresa_obtener_monedas = '{!!URL::to('empresa_obtener_monedas')!!}';
        var empresa_guardar_modificacion_domicilio_fiscal = '{!!URL::to('empresa_guardar_modificacion_domicilio_fiscal')!!}';
        var empresa_guardar_modificacion_lugar_expedicion = '{!!URL::to('empresa_guardar_modificacion_lugar_expedicion')!!}';
        var empresa_guardar_modificacion_configurar = '{!!URL::to('empresa_guardar_modificacion_configurar')!!}';
        var empresa_guardar_modificacion_logo_y_tema = '{!!URL::to('empresa_guardar_modificacion_logo_y_tema')!!}';
        var cambiar_contrasena = '{!!URL::to('cambiar_contrasena')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/empresa/empresa.js"></script>

@endsection