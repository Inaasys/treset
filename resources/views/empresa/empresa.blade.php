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
                        <div class="profile-header {{$empresa->background_forms_and_modals}}" id="infoprofile"></div>
                        <div class="profile-body">
                            <div class="image-area text-center">
                                <img id="perfillogotipoempresa" src="logotipo_empresa/{{$empresa->Logo}}" alt="Perfil Empresa" height="100%" width="100%" class="text-center"/>
                            </div>
                            <div class="content-area text-center">
                                <h4>{{$empresa->Empresa}}</h4>
                            </div>
                        </div>
                        <div class="profile-footer text-center">
                            <h5>RFC:{{$empresa->Rfc}}</h5>
                        </div>
                    </div>
                    <div class="card card-about-me" hidden>
                        <div class="header text-center {{$empresa->background_forms_and_modals}}" id="infoprofile1">
                            <h5>INFORMACIÓN</h5>
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
                            <h5>USUARIO LOGUEADO</h5>
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
                                        <a href="#registro_facturapi" data-toggle="tab">Registro en Facturapi</a>
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
                                                        <input type="text" class="form-control" name="empresamoneda" id="empresamoneda" value="{{$empresa->Moneda}}" required readonly onkeyup="tipoLetra(this)">
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
                                                                <th class="col-md-2">Módulos</th>
                                                                <th class="col-md-5">Configurar Sistema</th>
                                                                <th class="col-md-4">Opción</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
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
                                                                <td class="col-md-2">Envio Documentos</td>
                                                                <td class="col-md-5">Correo 1 por default al que se enviaran los documentos</td>
                                                                <td class="col-md-4">
                                                                    <input type="text" class="form-control" name="correodefault1enviodocumentos" id="correodefault1enviodocumentos" data-parsley-type="email">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-2">Envio Documentos</td>
                                                                <td class="col-md-5">Correo 2 por default al que se enviaran los documentos</td>
                                                                <td class="col-md-4">
                                                                    <input type="text" class="form-control" name="correodefault2enviodocumentos" id="correodefault2enviodocumentos" data-parsley-type="email">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-2">Modificación Insumo</td>
                                                                <td class="col-md-5">Usuarios que pueden modificar Insumos</td>
                                                                <td class="col-md-4">
                                                                    <select  name="usuariosmodificacioninsumo[]" id="usuariosmodificacioninsumo" class="form-control select2" multiple="multiple" style="width:100% !important;" >
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-2">Remisiones</td>
                                                                <td class="col-md-5">Verificar si los las partidas de la remisión ya fueron cargadas en una O.T.</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="verificarpartidasremisionenot" id="verificarpartidasremisionenot" value="S">
                                                                        <label for="verificarpartidasremisionenot">SI</label>
                                                                        <input type="radio" name="verificarpartidasremisionenot" id="verificarpartidasremisionenot1" value="N">
                                                                        <label for="verificarpartidasremisionenot1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-2">Ordenes Compra</td>
                                                                <td class="col-md-5">Al enviar documento por email agregar en asunto la Referencia de la Orden de Compra</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="agregarreferenciaenasuntocorreo" id="agregarreferenciaenasuntocorreo" value="S">
                                                                        <label for="agregarreferenciaenasuntocorreo">SI</label>
                                                                        <input type="radio" name="agregarreferenciaenasuntocorreo" id="agregarreferenciaenasuntocorreo1" value="N">
                                                                        <label for="agregarreferenciaenasuntocorreo1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-2">Remisiones</td>
                                                                <td class="col-md-5">Controlar consecutivo de número de requisciones</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="controlarconsecutivonumrequisicion" id="controlarconsecutivonumrequisicion" value="S">
                                                                        <label for="controlarconsecutivonumrequisicion">SI</label>
                                                                        <input type="radio" name="controlarconsecutivonumrequisicion" id="controlarconsecutivonumrequisicion1" value="N">
                                                                        <label for="controlarconsecutivonumrequisicion1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-2">Facturas</td>
                                                                <td class="col-md-5">Colocar las observaciones de la remisión en la factura</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="colocarobservacionesremisionenfactura" id="colocarobservacionesremisionenfactura" value="S">
                                                                        <label for="colocarobservacionesremisionenfactura">SI</label>
                                                                        <input type="radio" name="colocarobservacionesremisionenfactura" id="colocarobservacionesremisionenfactura1" value="N">
                                                                        <label for="colocarobservacionesremisionenfactura1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-2">Facturas</td>
                                                                <td class="col-md-5">Pedir obligatoriamente una observación</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="pedirobligatoriamenteobservacionenfactura" id="pedirobligatoriamenteobservacionenfactura" value="S">
                                                                        <label for="pedirobligatoriamenteobservacionenfactura">SI</label>
                                                                        <input type="radio" name="pedirobligatoriamenteobservacionenfactura" id="pedirobligatoriamenteobservacionenfactura1" value="N">
                                                                        <label for="pedirobligatoriamenteobservacionenfactura1">NO</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="col-md-2">Remisiones</td>
                                                                <td class="col-md-5">Colocar en cero la cantidad al agregar partidas en remisiones</td>
                                                                <td class="col-md-4">
                                                                    <div class="col-md-12 form-check">
                                                                        <input type="radio" name="colocarencerocantidadenpartidasderemisiones" id="colocarencerocantidadenpartidasderemisiones" value="S">
                                                                        <label for="colocarencerocantidadenpartidasderemisiones">SI</label>
                                                                        <input type="radio" name="colocarencerocantidadenpartidasderemisiones" id="colocarencerocantidadenpartidasderemisiones1" value="N">
                                                                        <label for="colocarencerocantidadenpartidasderemisiones1">NO</label>
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
                                    <div role="tabpanel" class="tab-pane fade" id="registro_facturapi">
                                        <form id="formregistroempresafacturapi" action="#">
                                            <div class="row">  
                                                <div class="col-md-12">  
                                                    @if(Auth::user()->role_id == 1 && $empresa->IdFacturapi == "")
                                                        <h3>Da click en registrar en facturapi para poder timbrar las facturas de la empresa</h3>
                                                    @else
                                                        <h3>La empresa ya se encuentra registrada</h3>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row">    
                                                <div class="col-md-2 col-md-offset-10">
                                                    @if(Auth::user()->role_id == 1 && $empresa->IdFacturapi == "")
                                                        <button type="submit" class="btn bg-green btn-block waves-effect" id="btnguardarregistroempresafacturapi">Registrar Facturapi</button>
                                                    @endif
                                                </div>
                                            </div>
                                        </form>
                                    </div> 
                                    <div role="tabpanel" class="tab-pane fade" id="cambiar_logotipo">
                                        <form id="formlogotipo" action="#" enctype="multipart/form-data">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label>Cambiar Logotipo</label>
                                                    <input type="file" name="logo" id="logo"  class="dropify" data-max-file-size="1M" data-allowed-file-extensions="jpg" data-min-width="200" data-min-height="200"/>
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
                                                        <option value="bg-IndianRed" class="bg-IndianRed">bg-IndianRed</option>
                                                        <option value="bg-LightCoral" class="bg-LightCoral">bg-LightCoral</option>
                                                        <option value="bg-DarkSalmon" class="bg-DarkSalmon">bg-DarkSalmon</option>
                                                        <option value="bg-Crimson" class="bg-Crimson">bg-Crimson</option>
                                                        <option value="bg-FireBrick" class="bg-FireBrick">bg-FireBrick</option>
                                                        <option value="bg-DarkRed" class="bg-DarkRed">bg-DarkRed</option>
                                                        <option value="bg-LightPink" class="bg-LightPink">bg-LightPink</option>
                                                        <option value="bg-HotPink" class="bg-HotPink">bg-HotPink</option>
                                                        <option value="bg-DeepPink" class="bg-DeepPink">bg-DeepPink</option>
                                                        <option value="bg-MediumVioletRed" class="bg-MediumVioletRed">bg-MediumVioletRed</option>
                                                        <option value="bg-PaleVioletRed" class="bg-PaleVioletRed">bg-PaleVioletRed</option>
                                                        <option value="bg-Coral" class="bg-Coral">bg-Coral</option>
                                                        <option value="bg-Tomato" class="bg-Tomato">bg-Tomato</option>
                                                        <option value="bg-OrangeRed" class="bg-OrangeRed">bg-OrangeRed</option>
                                                        <option value="bg-DarkOrange" class="bg-DarkOrange">bg-DarkOrange</option>
                                                        <option value="bg-Gold" class="bg-Gold">bg-Gold</option>
                                                        <option value="bg-Moccasin" class="bg-Moccasin">bg-Moccasin</option>
                                                        <option value="bg-Khaki" class="bg-Khaki">bg-Khaki</option>
                                                        <option value="bg-DarkKhaki" class="bg-DarkKhaki">bg-DarkKhaki</option>
                                                        <option value="bg-Lavender" class="bg-Lavender">bg-Lavender</option>
                                                        <option value="bg-Thistle" class="bg-Thistle">bg-Thistle</option>
                                                        <option value="bg-Violet" class="bg-Violet">bg-Violet</option>
                                                        <option value="bg-Orchid" class="bg-Orchid">bg-Orchid</option>
                                                        <option value="bg-Magenta" class="bg-Magenta">bg-Magenta</option>
                                                        <option value="bg-MediumOrchid" class="bg-MediumOrchid">bg-MediumOrchid</option>
                                                        <option value="bg-MediumPurple" class="bg-MediumPurple">bg-MediumPurple</option>
                                                        <option value="bg-RebeccaPurple" class="bg-RebeccaPurple">bg-RebeccaPurple</option>
                                                        <option value="bg-DarkViolet" class="bg-DarkViolet">bg-DarkViolet</option>
                                                        <option value="bg-DarkMagenta" class="bg-DarkMagenta">bg-DarkMagenta</option>
                                                        <option value="bg-Purple" class="bg-Purple">bg-Purple</option>
                                                        <option value="bg-SlateBlue" class="bg-SlateBlue">bg-SlateBlue</option>
                                                        <option value="bg-DarkSlateBlue" class="bg-DarkSlateBlue">bg-DarkSlateBlue</option>
                                                        <option value="bg-GreenYellow" class="bg-GreenYellow">bg-GreenYellow</option>
                                                        <option value="bg-LawnGreen" class="bg-LawnGreen">bg-LawnGreen</option>
                                                        <option value="bg-Lime" class="bg-Lime">bg-Lime</option>
                                                        <option value="bg-LimeGreen" class="bg-LimeGreen">bg-LimeGreen</option>
                                                        <option value="bg-LightGreen" class="bg-LightGreen">bg-LightGreen</option>
                                                        <option value="bg-MediumSpringGreen" class="bg-MediumSpringGreen">bg-MediumSpringGreen</option>
                                                        <option value="bg-MediumSeaGreen" class="bg-MediumSeaGreen">bg-MediumSeaGreen</option>
                                                        <option value="bg-SeaGreen" class="bg-SeaGreen">bg-SeaGreen</option>
                                                        <option value="bg-ForestGreen" class="bg-ForestGreen">bg-ForestGreen</option>
                                                        <option value="bg-DarkGreen" class="bg-DarkGreen">bg-DarkGreen</option>
                                                        <option value="bg-Olive" class="bg-Olive">bg-Olive</option>
                                                        <option value="bg-DarkOliveGreen" class="bg-DarkOliveGreen">bg-DarkOliveGreen</option>
                                                        <option value="bg-MediumAquamarine" class="bg-MediumAquamarine">bg-MediumAquamarine</option>
                                                        <option value="bg-LightSeaGreen" class="bg-LightSeaGreen">bg-LightSeaGreen</option>
                                                        <option value="bg-DarkCyan" class="bg-DarkCyan">bg-DarkCyan</option>
                                                        <option value="bg-PaleTurquoise" class="bg-PaleTurquoise">bg-PaleTurquoise</option>
                                                        <option value="bg-Aquamarine" class="bg-Aquamarine">bg-Aquamarine</option>
                                                        <option value="bg-Turquoise" class="bg-Turquoise">bg-Turquoise</option>
                                                        <option value="bg-DarkTurquoise" class="bg-DarkTurquoise">bg-DarkTurquoise</option>
                                                        <option value="bg-CadetBlue" class="bg-CadetBlue">bg-CadetBlue</option>
                                                        <option value="bg-SteelBlue" class="bg-SteelBlue">bg-SteelBlue</option>
                                                        <option value="bg-LightSteelBlue" class="bg-LightSteelBlue">bg-LightSteelBlue</option>
                                                        <option value="bg-PowderBlue" class="bg-PowderBlue">bg-PowderBlue</option>
                                                        <option value="bg-SkyBlue" class="bg-SkyBlue">bg-SkyBlue</option>
                                                        <option value="bg-DeepSkyBlue" class="bg-DeepSkyBlue">bg-DeepSkyBlue</option>
                                                        <option value="bg-DodgerBlue" class="bg-DodgerBlue">bg-DodgerBlue</option>
                                                        <option value="bg-CornflowerBlue" class="bg-CornflowerBlue">bg-CornflowerBlue</option>
                                                        <option value="bg-RoyalBlue" class="bg-RoyalBlue">bg-RoyalBlue</option>
                                                        <option value="bg-DarkBlue" class="bg-DarkBlue">bg-DarkBlue</option>
                                                        <option value="bg-MidnightBlue" class="bg-MidnightBlue">bg-MidnightBlue</option>
                                                        <option value="bg-NavajoWhite" class="bg-NavajoWhite">bg-NavajoWhite</option>
                                                        <option value="bg-BurlyWood" class="bg-BurlyWood">bg-BurlyWood</option>
                                                        <option value="bg-Tan" class="bg-Tan">bg-Tan</option>
                                                        <option value="bg-RosyBrown" class="bg-RosyBrown">bg-RosyBrown</option>
                                                        <option value="bg-SandyBrown" class="bg-SandyBrown">bg-SandyBrown</option>
                                                        <option value="bg-Goldenrod" class="bg-Goldenrod">bg-Goldenrod</option>
                                                        <option value="bg-DarkGoldenrod" class="bg-DarkGoldenrod">bg-DarkGoldenrod</option>
                                                        <option value="bg-Chocolate" class="bg-Chocolate">bg-Chocolate</option>
                                                        <option value="bg-SaddleBrown" class="bg-SaddleBrown">bg-SaddleBrown</option>
                                                        <option value="bg-Brown" class="bg-Brown">bg-Brown</option>
                                                        <option value="bg-Maroon" class="bg-Maroon">bg-Maroon</option>
                                                        <option value="bg-Silver" class="bg-Silver">bg-Silver</option>
                                                        <option value="bg-DarkGray" class="bg-DarkGray">bg-DarkGray</option>
                                                        <option value="bg-Gray" class="bg-Gray">bg-Gray</option>
                                                        <option value="bg-DimGray" class="bg-DimGray">bg-DimGray</option>
                                                        <option value="bg-LightSlateGray" class="bg-LightSlateGray">bg-LightSlateGray</option>
                                                        <option value="bg-SlateGray" class="bg-SlateGray">bg-SlateGray</option>
                                                        <option value="bg-DarkSlateGray" class="bg-DarkSlateGray">bg-DarkSlateGray</option>
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
                                                        <option value="bg-IndianRed" class="bg-IndianRed">bg-IndianRed</option>
                                                        <option value="bg-LightCoral" class="bg-LightCoral">bg-LightCoral</option>
                                                        <option value="bg-DarkSalmon" class="bg-DarkSalmon">bg-DarkSalmon</option>
                                                        <option value="bg-Crimson" class="bg-Crimson">bg-Crimson</option>
                                                        <option value="bg-FireBrick" class="bg-FireBrick">bg-FireBrick</option>
                                                        <option value="bg-DarkRed" class="bg-DarkRed">bg-DarkRed</option>
                                                        <option value="bg-LightPink" class="bg-LightPink">bg-LightPink</option>
                                                        <option value="bg-HotPink" class="bg-HotPink">bg-HotPink</option>
                                                        <option value="bg-DeepPink" class="bg-DeepPink">bg-DeepPink</option>
                                                        <option value="bg-MediumVioletRed" class="bg-MediumVioletRed">bg-MediumVioletRed</option>
                                                        <option value="bg-PaleVioletRed" class="bg-PaleVioletRed">bg-PaleVioletRed</option>
                                                        <option value="bg-Coral" class="bg-Coral">bg-Coral</option>
                                                        <option value="bg-Tomato" class="bg-Tomato">bg-Tomato</option>
                                                        <option value="bg-OrangeRed" class="bg-OrangeRed">bg-OrangeRed</option>
                                                        <option value="bg-DarkOrange" class="bg-DarkOrange">bg-DarkOrange</option>
                                                        <option value="bg-Gold" class="bg-Gold">bg-Gold</option>
                                                        <option value="bg-Moccasin" class="bg-Moccasin">bg-Moccasin</option>
                                                        <option value="bg-Khaki" class="bg-Khaki">bg-Khaki</option>
                                                        <option value="bg-DarkKhaki" class="bg-DarkKhaki">bg-DarkKhaki</option>
                                                        <option value="bg-Lavender" class="bg-Lavender">bg-Lavender</option>
                                                        <option value="bg-Thistle" class="bg-Thistle">bg-Thistle</option>
                                                        <option value="bg-Violet" class="bg-Violet">bg-Violet</option>
                                                        <option value="bg-Orchid" class="bg-Orchid">bg-Orchid</option>
                                                        <option value="bg-Magenta" class="bg-Magenta">bg-Magenta</option>
                                                        <option value="bg-MediumOrchid" class="bg-MediumOrchid">bg-MediumOrchid</option>
                                                        <option value="bg-MediumPurple" class="bg-MediumPurple">bg-MediumPurple</option>
                                                        <option value="bg-RebeccaPurple" class="bg-RebeccaPurple">bg-RebeccaPurple</option>
                                                        <option value="bg-DarkViolet" class="bg-DarkViolet">bg-DarkViolet</option>
                                                        <option value="bg-DarkMagenta" class="bg-DarkMagenta">bg-DarkMagenta</option>
                                                        <option value="bg-Purple" class="bg-Purple">bg-Purple</option>
                                                        <option value="bg-SlateBlue" class="bg-SlateBlue">bg-SlateBlue</option>
                                                        <option value="bg-DarkSlateBlue" class="bg-DarkSlateBlue">bg-DarkSlateBlue</option>
                                                        <option value="bg-GreenYellow" class="bg-GreenYellow">bg-GreenYellow</option>
                                                        <option value="bg-LawnGreen" class="bg-LawnGreen">bg-LawnGreen</option>
                                                        <option value="bg-Lime" class="bg-Lime">bg-Lime</option>
                                                        <option value="bg-LimeGreen" class="bg-LimeGreen">bg-LimeGreen</option>
                                                        <option value="bg-LightGreen" class="bg-LightGreen">bg-LightGreen</option>
                                                        <option value="bg-MediumSpringGreen" class="bg-MediumSpringGreen">bg-MediumSpringGreen</option>
                                                        <option value="bg-MediumSeaGreen" class="bg-MediumSeaGreen">bg-MediumSeaGreen</option>
                                                        <option value="bg-SeaGreen" class="bg-SeaGreen">bg-SeaGreen</option>
                                                        <option value="bg-ForestGreen" class="bg-ForestGreen">bg-ForestGreen</option>
                                                        <option value="bg-DarkGreen" class="bg-DarkGreen">bg-DarkGreen</option>
                                                        <option value="bg-Olive" class="bg-Olive">bg-Olive</option>
                                                        <option value="bg-DarkOliveGreen" class="bg-DarkOliveGreen">bg-DarkOliveGreen</option>
                                                        <option value="bg-MediumAquamarine" class="bg-MediumAquamarine">bg-MediumAquamarine</option>
                                                        <option value="bg-LightSeaGreen" class="bg-LightSeaGreen">bg-LightSeaGreen</option>
                                                        <option value="bg-DarkCyan" class="bg-DarkCyan">bg-DarkCyan</option>
                                                        <option value="bg-PaleTurquoise" class="bg-PaleTurquoise">bg-PaleTurquoise</option>
                                                        <option value="bg-Aquamarine" class="bg-Aquamarine">bg-Aquamarine</option>
                                                        <option value="bg-Turquoise" class="bg-Turquoise">bg-Turquoise</option>
                                                        <option value="bg-DarkTurquoise" class="bg-DarkTurquoise">bg-DarkTurquoise</option>
                                                        <option value="bg-CadetBlue" class="bg-CadetBlue">bg-CadetBlue</option>
                                                        <option value="bg-SteelBlue" class="bg-SteelBlue">bg-SteelBlue</option>
                                                        <option value="bg-LightSteelBlue" class="bg-LightSteelBlue">bg-LightSteelBlue</option>
                                                        <option value="bg-PowderBlue" class="bg-PowderBlue">bg-PowderBlue</option>
                                                        <option value="bg-SkyBlue" class="bg-SkyBlue">bg-SkyBlue</option>
                                                        <option value="bg-DeepSkyBlue" class="bg-DeepSkyBlue">bg-DeepSkyBlue</option>
                                                        <option value="bg-DodgerBlue" class="bg-DodgerBlue">bg-DodgerBlue</option>
                                                        <option value="bg-CornflowerBlue" class="bg-CornflowerBlue">bg-CornflowerBlue</option>
                                                        <option value="bg-RoyalBlue" class="bg-RoyalBlue">bg-RoyalBlue</option>
                                                        <option value="bg-DarkBlue" class="bg-DarkBlue">bg-DarkBlue</option>
                                                        <option value="bg-MidnightBlue" class="bg-MidnightBlue">bg-MidnightBlue</option>
                                                        <option value="bg-NavajoWhite" class="bg-NavajoWhite">bg-NavajoWhite</option>
                                                        <option value="bg-BurlyWood" class="bg-BurlyWood">bg-BurlyWood</option>
                                                        <option value="bg-Tan" class="bg-Tan">bg-Tan</option>
                                                        <option value="bg-RosyBrown" class="bg-RosyBrown">bg-RosyBrown</option>
                                                        <option value="bg-SandyBrown" class="bg-SandyBrown">bg-SandyBrown</option>
                                                        <option value="bg-Goldenrod" class="bg-Goldenrod">bg-Goldenrod</option>
                                                        <option value="bg-DarkGoldenrod" class="bg-DarkGoldenrod">bg-DarkGoldenrod</option>
                                                        <option value="bg-Chocolate" class="bg-Chocolate">bg-Chocolate</option>
                                                        <option value="bg-SaddleBrown" class="bg-SaddleBrown">bg-SaddleBrown</option>
                                                        <option value="bg-Brown" class="bg-Brown">bg-Brown</option>
                                                        <option value="bg-Maroon" class="bg-Maroon">bg-Maroon</option>
                                                        <option value="bg-Silver" class="bg-Silver">bg-Silver</option>
                                                        <option value="bg-DarkGray" class="bg-DarkGray">bg-DarkGray</option>
                                                        <option value="bg-Gray" class="bg-Gray">bg-Gray</option>
                                                        <option value="bg-DimGray" class="bg-DimGray">bg-DimGray</option>
                                                        <option value="bg-LightSlateGray" class="bg-LightSlateGray">bg-LightSlateGray</option>
                                                        <option value="bg-SlateGray" class="bg-SlateGray">bg-SlateGray</option>
                                                        <option value="bg-DarkSlateGray" class="bg-DarkSlateGray">bg-DarkSlateGray</option>
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
                                                        <option value="bg-IndianRed" class="bg-IndianRed">bg-IndianRed</option>
                                                        <option value="bg-LightCoral" class="bg-LightCoral">bg-LightCoral</option>
                                                        <option value="bg-DarkSalmon" class="bg-DarkSalmon">bg-DarkSalmon</option>
                                                        <option value="bg-Crimson" class="bg-Crimson">bg-Crimson</option>
                                                        <option value="bg-FireBrick" class="bg-FireBrick">bg-FireBrick</option>
                                                        <option value="bg-DarkRed" class="bg-DarkRed">bg-DarkRed</option>
                                                        <option value="bg-LightPink" class="bg-LightPink">bg-LightPink</option>
                                                        <option value="bg-HotPink" class="bg-HotPink">bg-HotPink</option>
                                                        <option value="bg-DeepPink" class="bg-DeepPink">bg-DeepPink</option>
                                                        <option value="bg-MediumVioletRed" class="bg-MediumVioletRed">bg-MediumVioletRed</option>
                                                        <option value="bg-PaleVioletRed" class="bg-PaleVioletRed">bg-PaleVioletRed</option>
                                                        <option value="bg-Coral" class="bg-Coral">bg-Coral</option>
                                                        <option value="bg-Tomato" class="bg-Tomato">bg-Tomato</option>
                                                        <option value="bg-OrangeRed" class="bg-OrangeRed">bg-OrangeRed</option>
                                                        <option value="bg-DarkOrange" class="bg-DarkOrange">bg-DarkOrange</option>
                                                        <option value="bg-Gold" class="bg-Gold">bg-Gold</option>
                                                        <option value="bg-Moccasin" class="bg-Moccasin">bg-Moccasin</option>
                                                        <option value="bg-Khaki" class="bg-Khaki">bg-Khaki</option>
                                                        <option value="bg-DarkKhaki" class="bg-DarkKhaki">bg-DarkKhaki</option>
                                                        <option value="bg-Lavender" class="bg-Lavender">bg-Lavender</option>
                                                        <option value="bg-Thistle" class="bg-Thistle">bg-Thistle</option>
                                                        <option value="bg-Violet" class="bg-Violet">bg-Violet</option>
                                                        <option value="bg-Orchid" class="bg-Orchid">bg-Orchid</option>
                                                        <option value="bg-Magenta" class="bg-Magenta">bg-Magenta</option>
                                                        <option value="bg-MediumOrchid" class="bg-MediumOrchid">bg-MediumOrchid</option>
                                                        <option value="bg-MediumPurple" class="bg-MediumPurple">bg-MediumPurple</option>
                                                        <option value="bg-RebeccaPurple" class="bg-RebeccaPurple">bg-RebeccaPurple</option>
                                                        <option value="bg-DarkViolet" class="bg-DarkViolet">bg-DarkViolet</option>
                                                        <option value="bg-DarkMagenta" class="bg-DarkMagenta">bg-DarkMagenta</option>
                                                        <option value="bg-Purple" class="bg-Purple">bg-Purple</option>
                                                        <option value="bg-SlateBlue" class="bg-SlateBlue">bg-SlateBlue</option>
                                                        <option value="bg-DarkSlateBlue" class="bg-DarkSlateBlue">bg-DarkSlateBlue</option>
                                                        <option value="bg-GreenYellow" class="bg-GreenYellow">bg-GreenYellow</option>
                                                        <option value="bg-LawnGreen" class="bg-LawnGreen">bg-LawnGreen</option>
                                                        <option value="bg-Lime" class="bg-Lime">bg-Lime</option>
                                                        <option value="bg-LimeGreen" class="bg-LimeGreen">bg-LimeGreen</option>
                                                        <option value="bg-LightGreen" class="bg-LightGreen">bg-LightGreen</option>
                                                        <option value="bg-MediumSpringGreen" class="bg-MediumSpringGreen">bg-MediumSpringGreen</option>
                                                        <option value="bg-MediumSeaGreen" class="bg-MediumSeaGreen">bg-MediumSeaGreen</option>
                                                        <option value="bg-SeaGreen" class="bg-SeaGreen">bg-SeaGreen</option>
                                                        <option value="bg-ForestGreen" class="bg-ForestGreen">bg-ForestGreen</option>
                                                        <option value="bg-DarkGreen" class="bg-DarkGreen">bg-DarkGreen</option>
                                                        <option value="bg-Olive" class="bg-Olive">bg-Olive</option>
                                                        <option value="bg-DarkOliveGreen" class="bg-DarkOliveGreen">bg-DarkOliveGreen</option>
                                                        <option value="bg-MediumAquamarine" class="bg-MediumAquamarine">bg-MediumAquamarine</option>
                                                        <option value="bg-LightSeaGreen" class="bg-LightSeaGreen">bg-LightSeaGreen</option>
                                                        <option value="bg-DarkCyan" class="bg-DarkCyan">bg-DarkCyan</option>
                                                        <option value="bg-PaleTurquoise" class="bg-PaleTurquoise">bg-PaleTurquoise</option>
                                                        <option value="bg-Aquamarine" class="bg-Aquamarine">bg-Aquamarine</option>
                                                        <option value="bg-Turquoise" class="bg-Turquoise">bg-Turquoise</option>
                                                        <option value="bg-DarkTurquoise" class="bg-DarkTurquoise">bg-DarkTurquoise</option>
                                                        <option value="bg-CadetBlue" class="bg-CadetBlue">bg-CadetBlue</option>
                                                        <option value="bg-SteelBlue" class="bg-SteelBlue">bg-SteelBlue</option>
                                                        <option value="bg-LightSteelBlue" class="bg-LightSteelBlue">bg-LightSteelBlue</option>
                                                        <option value="bg-PowderBlue" class="bg-PowderBlue">bg-PowderBlue</option>
                                                        <option value="bg-SkyBlue" class="bg-SkyBlue">bg-SkyBlue</option>
                                                        <option value="bg-DeepSkyBlue" class="bg-DeepSkyBlue">bg-DeepSkyBlue</option>
                                                        <option value="bg-DodgerBlue" class="bg-DodgerBlue">bg-DodgerBlue</option>
                                                        <option value="bg-CornflowerBlue" class="bg-CornflowerBlue">bg-CornflowerBlue</option>
                                                        <option value="bg-RoyalBlue" class="bg-RoyalBlue">bg-RoyalBlue</option>
                                                        <option value="bg-DarkBlue" class="bg-DarkBlue">bg-DarkBlue</option>
                                                        <option value="bg-MidnightBlue" class="bg-MidnightBlue">bg-MidnightBlue</option>
                                                        <option value="bg-NavajoWhite" class="bg-NavajoWhite">bg-NavajoWhite</option>
                                                        <option value="bg-BurlyWood" class="bg-BurlyWood">bg-BurlyWood</option>
                                                        <option value="bg-Tan" class="bg-Tan">bg-Tan</option>
                                                        <option value="bg-RosyBrown" class="bg-RosyBrown">bg-RosyBrown</option>
                                                        <option value="bg-SandyBrown" class="bg-SandyBrown">bg-SandyBrown</option>
                                                        <option value="bg-Goldenrod" class="bg-Goldenrod">bg-Goldenrod</option>
                                                        <option value="bg-DarkGoldenrod" class="bg-DarkGoldenrod">bg-DarkGoldenrod</option>
                                                        <option value="bg-Chocolate" class="bg-Chocolate">bg-Chocolate</option>
                                                        <option value="bg-SaddleBrown" class="bg-SaddleBrown">bg-SaddleBrown</option>
                                                        <option value="bg-Brown" class="bg-Brown">bg-Brown</option>
                                                        <option value="bg-Maroon" class="bg-Maroon">bg-Maroon</option>
                                                        <option value="bg-Silver" class="bg-Silver">bg-Silver</option>
                                                        <option value="bg-DarkGray" class="bg-DarkGray">bg-DarkGray</option>
                                                        <option value="bg-Gray" class="bg-Gray">bg-Gray</option>
                                                        <option value="bg-DimGray" class="bg-DimGray">bg-DimGray</option>
                                                        <option value="bg-LightSlateGray" class="bg-LightSlateGray">bg-LightSlateGray</option>
                                                        <option value="bg-SlateGray" class="bg-SlateGray">bg-SlateGray</option>
                                                        <option value="bg-DarkSlateGray" class="bg-DarkSlateGray">bg-DarkSlateGray</option>
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
        var tipo_de_utilidad = '{{$empresa->Tipo_De_Utilidad}}';
        var correodefault1enviodocumentos = '{{$empresa->CorreoDefault1EnvioDocumentos}}';
        var correodefault2enviodocumentos = '{{$empresa->CorreoDefault2EnvioDocumentos}}';
        var usuariosamodificarinsumos = '{{$empresa->UsuariosModificarInsumo}}';
        var verificarinsumosremisionenot = '{{$empresa->VerificarPartidasRemisionEnOT}}';
        var empresa = '{{$empresa}}';
        var agregarreferenciaenasuntocorreo = '{{$empresa->AgregarReferenciaOrdenCompraEnAsuntoCorreo}}';
        var controlarconsecutivonumrequisicion = '{{$empresa->ControlarConsecutivoNumeroRequisicionEnRemisiones}}';
        var colocarobservacionesremisionenfactura = '{{$empresa->ColocarObservacionesDeRemisionEnFactura}}';
        var pedirobligatoriamenteobservacionenfactura = '{{$empresa->PedirObligatoriamenteObservacionEnFactura}}';
        var colocarencerocantidadenpartidasderemisiones = '{{$empresa->ColocarEnCeroCantidadEnPartidasDeRemisiones}}';
        var empresa_obtener_usuarios_a_modificar_insumos = '{!!URL::to('empresa_obtener_usuarios_a_modificar_insumos')!!}';
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
        var empresa_guardar_registro_empresa_facturapi = '{!!URL::to('empresa_guardar_registro_empresa_facturapi')!!}';
        var cambiar_contrasena = '{!!URL::to('cambiar_contrasena')!!}';
    </script>
    @include('secciones.libreriasregistrosycatalogos')
    <script src="scripts_inaasys/empresa/empresa.js"></script>

@endsection