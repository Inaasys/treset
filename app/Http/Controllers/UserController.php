<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Helpers;
use DataTables;
use DB;
use App;
use App\User;
use App\Menu;
use App\Role;
use App\Permiso;
use App\User_Rel_Permiso;
use App\User_Rel_Menu;
use App\User_Rel_Almacen;
use App\User_Rel_Serie;
use App\Almacen;
use App\FolioComprobanteFactura;
use App\FolioComprobanteNota;
use App\FolioComprobantePago;
use Mail;
use App\Personal;
use App\Serie;
use App\Documento;

class UserController extends ConfiguracionSistemaController
{
    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function usuarios(){
        return view('catalogos.usuarios.usuarios');
    }
    //Listar registros
    public function usuarios_obtener(Request $request){
        if($request->ajax()){
            $data = User::query();
            return DataTables::of($data)
                    ->order(function ($query) {
                        $query->orderBy('role_id', 'ASC');
                    })
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos('.$data->id.')">Cambios</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar('.$data->id.')">Bajas</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="permisos('.$data->id.')">Ver Permisos</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="seriesusuariodocumentos('.$data->id.',\''.$data->user.'\')">Ver Series Documentos</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener ultimo id de tabla
    public function usuarios_obtener_ultimo_numero(){
        $id = Helpers::ultimoidregistrotabla('App\User');
        return response()->json($id);
    }
    //obtener roles 
    public function usuarios_obtener_roles(){
        $getroles = Role::orderBy("id", "DESC")->get();
        $roles = "";
        $contador = 1;
        foreach($getroles as $getrol){
            $roles = $roles.
            '<input type="radio" name="rol" id="rol'.$contador.'" value="'.$getrol->id.'" required>'.
                                '<label for="rol'.$contador.'">'.$getrol->name.'</label>';
            $contador++;
        }
        return response()->json($roles);
    }
    //insertar registro
    public function usuarios_guardar(Request $request){
        $email=$request->email;
	    $ExisteUsuario = User::where('email', $email)->first();
	    if($ExisteUsuario == true){
            $Usuario = 1;
	    }else{
            //insertar registro
            if (App::environment('local') || App::environment('production')) {
                DB::unprepared('SET IDENTITY_INSERT users ON');
            }
            $id = Helpers::ultimoidregistrotabla('App\User');
            $Usuario = new User;
		    $Usuario->id=$id;
		    $Usuario->name=$request->name;
            $Usuario->email=$request->email;
            $Usuario->password=Hash::make($request->pass);
            $Usuario->user=$request->user;
            $Usuario->role_id=$request->rol;
		    $Usuario->status="ALTA";
            $Usuario->save();
            if (App::environment('local') || App::environment('production')) {
                DB::unprepared('SET IDENTITY_INSERT users OFF');
            }
                //dar de alta en tabla personal
                DB::unprepared('SET IDENTITY_INSERT personal ON');
                $id = Helpers::ultimoidregistrotabla('App\Personal');
                $Personal = new Personal;
                $Personal->id=$id;
                $Personal->nombre = $request->name;
                $Personal->fecha_ingreso = Carbon::now()->toDateTimeString();
                $Personal->tipo_personal = "Administrativo";
                $Personal->status = 'ALTA';
                $Personal->save();
                DB::unprepared('SET IDENTITY_INSERT personal OFF');
            try{
                //enviar correo electrónico	
                $nombre = 'Receptor envio de correos';
                $receptor = 'osbaldo.anzaldo@utpcamiones.com.mx';
                $correos = ['osbaldo.anzaldo@utpcamiones.com.mx','marco.baltazar@utpcamiones.com.mx'];
                $name = "Receptor envio de correos";
                $body = "Se dio de alta un nuevo usuario";
                $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
                $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
                $nombre = $request->name;
                $correo = $request->email;
                $usuario = $request->user;
                $rol = $request->rol;
                Mail::send('correos.usuarios.nuevousuario', compact('nombre', 'correo', 'usuario', 'rol', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $correos) {
                    $message->to($receptor, $nombre)
                            ->cc($correos)
                            ->subject('Nuevo Usuario');
                });
            } catch(\Exception $e) {
                $receptor = 'osbaldo.anzaldo@utpcamiones.com.mx';
                $correos = ['osbaldo.anzaldo@utpcamiones.com.mx'];
                $msj = 'Error al enviar correo nuevo usuario'.$request->name.', '.$request->email;
                Mail::send('correos.errorenvio.error', compact('e','msj'), function($message) use ($receptor, $correos) {
                    $message->to($receptor)
                            ->cc($correos)
                            ->subject('Error al enviar correo nuevo usuario');
                });
            }

      	}
    	return response()->json($Usuario); 
    }
    //dar de baja registros
    public function usuarios_alta_o_baja(Request $request){
        $idusuario=$request->idusuario;
	    $Usuario = User::where('id', $idusuario )->first();
	    if($Usuario->status == 'ALTA'){
           $Usuario->status = 'BAJA';
	    }else{
	       $Usuario->status = 'ALTA';
        }
	    $Usuario->save();
	    return response()->json($Usuario);
    }
    //obtener datos registro seleccionado
    public function usuarios_obtener_usuario(Request $request){
        $usuario = User::where('id', $request->id)->first();
        $getroles = Role::orderBy("id", "DESC")->get();
        $roles = "";
        $contador = 1;
        foreach($getroles as $getrol){
            if($getrol->id == $usuario->role_id){
                $roles = $roles.
                '<input type="radio" name="rol" id="rol'.$contador.'" value="'.$getrol->id.'" required checked>'.
                                    '<label for="rol'.$contador.'">'.$getrol->name.'</label>';
            }else{
                $roles = $roles.
                '<input type="radio" name="rol" id="rol'.$contador.'" value="'.$getrol->id.'" required>'.
                                '<label for="rol'.$contador.'">'.$getrol->name.'</label>';
            }
            $contador++;
        }        
        $data = array(
            "usuario" => $usuario,
            "roles" => $roles
        );
        return response()->json($data);        
    }
    //guardar modificacion
    public function usuarios_guardar_modificacion(Request $request){
        $email=$request->email;
	    $ExisteUsuario = User::where('id','<>', $request->numero)->where('email', $email)->first();
	    if($ExisteUsuario == true){
            $Usuario = 1;
	    }else{
            //modificar registro
            $Usuario = User::where('id', $request->numero )->first();
            User::where('id', $request->numero)
            ->update([
                'name' => $request->name,
                'email' => $request->email,
                'user' => $request->user,
                'role_id' => $request->rol
            ]);
            /*
            $Usuario->name=$request->name;
            $Usuario->email=$request->email;
            //$Usuario->password=Hash::make($request->pass);
            $Usuario->user=$request->user;
            $Usuario->role_id=$request->rol;
            $Usuario->save();
            */
      	}
    	return response()->json($Usuario); 
    }
    //obtener todos los permisos y accesos al menu del usuario seleccionado
    public function usuarios_obtener_permisos(Request $request){
        $user = User::where('id', $request->id)->first();
        //accesos al menu
        $submenus_activos = User_Rel_Menu::where('user_id', $request->id)->get();
        $array_submenus = array();
        foreach($submenus_activos as $submenu_activo){
            $submenu = Menu::where('id', $submenu_activo->menu_id)->first();
            $array_submenus[]=array(
                "0"=>$submenu->name,
                "1"=>$submenu_activo->status,
              );

        }
        //permisos crud
        $permisos_crud_activos = User_Rel_Permiso::where('user_id', $request->id)->get();
        $array_permisos_crud = array();
        foreach($permisos_crud_activos as $permiso_crud_activo){
            $permiso = Permiso::where('id', $permiso_crud_activo->permiso_id)->first();
            $array_permisos_crud[]=array(
                "0"=>$permiso->name,
                "1"=>$permiso_crud_activo->status,
              );

        }
        //almacenes
        $almacenes = Almacen::where('Status', 'ALTA')->get();
        $select_almacenes_asignados = "<option disabled hidden>Selecciona...</option>";
        foreach($almacenes as $a){
            $tiene_almacen_asignado= User_Rel_Almacen::where('almacen_id', $a->Numero)->where('user_id', $request->id)->count();
            if ($tiene_almacen_asignado > 0) {
                $select_almacenes_asignados = $select_almacenes_asignados."<option value='".$a->Numero."' selected>".$a->Nombre."</option>"; 
            }else{
                $select_almacenes_asignados = $select_almacenes_asignados."<option value='".$a->Numero."'>".$a->Nombre."</option>";
            }
        }
        //series facturas
        $series_facturas = FolioComprobanteFactura::where('Status', 'ALTA')->get();
        $select_series_asignados_facturas = "<option disabled hidden>Selecciona...</option>";
        foreach($series_facturas as $sf){
            $tiene_serie_asignada = User_Rel_Serie::where('serie_id', $sf->Numero)->where('user_id', $request->id)->where('documento_serie', 'FACTURAS')->count();
            if ($tiene_serie_asignada > 0) {
                $select_series_asignados_facturas = $select_series_asignados_facturas."<option value='".$sf->Numero."' selected>".$sf->Serie."</option>"; 
            }else{
                $select_series_asignados_facturas = $select_series_asignados_facturas."<option value='".$sf->Numero."'>".$sf->Serie."</option>";
            }
        }
        //series notas credito
        $series_notas = FolioComprobanteNota::where('Status', 'ALTA')->get();
        $select_series_asignados_notas = "<option disabled hidden>Selecciona...</option>";
        foreach($series_notas as $sn){
            $tiene_serie_asignada = User_Rel_Serie::where('serie_id', $sn->Numero)->where('user_id', $request->id)->where('documento_serie', 'NOTAS')->count();
            if ($tiene_serie_asignada > 0) {
                $select_series_asignados_notas = $select_series_asignados_notas."<option value='".$sn->Numero."' selected>".$sn->Serie."</option>"; 
            }else{
                $select_series_asignados_notas = $select_series_asignados_notas."<option value='".$sn->Numero."'>".$sn->Serie."</option>";
            }
        }
        //series cuentas por cobrar
        $series_pagos = FolioComprobantePago::where('Status', 'ALTA')->get();
        $select_series_asignados_pagos = "<option disabled hidden>Selecciona...</option>";
        foreach($series_pagos as $sp){
            $tiene_serie_asignada = User_Rel_Serie::where('serie_id', $sp->Numero)->where('user_id', $request->id)->where('documento_serie', 'PAGOS')->count();
            if ($tiene_serie_asignada > 0) {
                $select_series_asignados_pagos = $select_series_asignados_pagos."<option value='".$sp->Numero."' selected>".$sp->Serie."</option>"; 
            }else{
                $select_series_asignados_pagos = $select_series_asignados_pagos."<option value='".$sp->Numero."'>".$sp->Serie."</option>";
            }
        }
        $data = array(
            "array_submenus" => $array_submenus,
            "array_permisos_crud" => $array_permisos_crud,
            "name" => $user->name,
            "select_almacenes_asignados" => $select_almacenes_asignados,
            "select_series_asignados_facturas" => $select_series_asignados_facturas,
            "select_series_asignados_notas" => $select_series_asignados_notas,
            "select_series_asignados_pagos" => $select_series_asignados_pagos
        );
        return response()->json($data);
    }
    //guardar acceso al menu y permisos del usuario seleccionado
    public function usuarios_guardar_permisos(Request $request){
        //acesos al menu
        $string_submenus = substr($request->string_submenus, 1); 
        $submenus = explode('-',$string_submenus);
        $eliminarsubmenus = User_Rel_Menu::where('user_id', $request->id_usuario_permisos)->forceDelete();
        foreach($submenus as $submenu){
            $submenuexplode = explode(',',$submenu);
            $menu = Menu::where('name', $submenuexplode[0])->first();
            $id = Helpers::ultimoidregistrotabla('App\User_Rel_Menu');
            if (App::environment('local') || App::environment('production')) {
                DB::unprepared('SET IDENTITY_INSERT user_rel_menus ON');
            }
            $User_Rel_Menu = new User_Rel_Menu;
            $User_Rel_Menu->id = $id;
            $User_Rel_Menu->menu_id = $menu->id;
            $User_Rel_Menu->user_id = $request->id_usuario_permisos;
            $User_Rel_Menu->status = $submenuexplode[1];
            $User_Rel_Menu->save();
            if (App::environment('local') || App::environment('production')) {
                DB::unprepared('SET IDENTITY_INSERT user_rel_menus OFF');
            }
        }
        //permisos crud
        $string_permisos_crud = substr($request->string_permisos_crud, 1); 
        $permisos_crud = explode('-',$string_permisos_crud);
        $eliminarpermisoscrud = User_Rel_Permiso::where('user_id', $request->id_usuario_permisos)->forceDelete();
        foreach($permisos_crud as $permiso_crud){
            $permiso_crudexplode = explode(',',$permiso_crud);
            $permiso = Permiso::where('name', $permiso_crudexplode[0])->first();
            $id = Helpers::ultimoidregistrotabla('App\User_Rel_Permiso');
            if (App::environment('local') || App::environment('production')) {
                DB::unprepared('SET IDENTITY_INSERT user_rel_permisos ON');
            }
            $User_Rel_Permiso = new User_Rel_Permiso;
            $User_Rel_Permiso->id = $id;
            $User_Rel_Permiso->user_id = $request->id_usuario_permisos;
            $User_Rel_Permiso->permiso_id = $permiso->id;
            $User_Rel_Permiso->status = $permiso_crudexplode[1];
            $User_Rel_Permiso->save();
            if (App::environment('local') || App::environment('production')) {
                DB::unprepared('SET IDENTITY_INSERT user_rel_permisos OFF');
            }
        }
        //almacenes asignados
        $eliminaralmacenesasignados = User_Rel_Almacen::where('user_id', $request->id_usuario_permisos)->forceDelete();
        if ($request->has("almacenesasignados")){
            foreach($request->almacenesasignados as $almacen_asignado){
                $id = Helpers::ultimoidregistrotabla('App\User_Rel_Almacen');
                if (App::environment('local') || App::environment('production')) {
                    DB::unprepared('SET IDENTITY_INSERT user_rel_almacenes ON');
                }
                $User_Rel_Almacen = new User_Rel_Almacen;
                $User_Rel_Almacen->id = $id;
                $User_Rel_Almacen->user_id = $request->id_usuario_permisos;
                $User_Rel_Almacen->almacen_id = $almacen_asignado;
                $User_Rel_Almacen->save();
                if (App::environment('local') || App::environment('production')) {
                    DB::unprepared('SET IDENTITY_INSERT user_rel_almacenes OFF');
                }
            }
        }
        //series asignadas facturas
        $eliminarseriesasignadasfacturas = User_Rel_Serie::where('user_id', $request->id_usuario_permisos)->where('documento_serie', 'FACTURAS')->forceDelete();
        if ($request->has("seriesfacturacionasignadas")){
            foreach($request->seriesfacturacionasignadas as $serie_factura_asignada){
                $id = Helpers::ultimoidregistrotabla('App\User_Rel_Serie');
                if (App::environment('local') || App::environment('production')) {
                    DB::unprepared('SET IDENTITY_INSERT user_rel_series ON');
                }
                $User_Rel_Serie = new User_Rel_Serie;
                $User_Rel_Serie->id = $id;
                $User_Rel_Serie->user_id = $request->id_usuario_permisos;
                $User_Rel_Serie->serie_id = $serie_factura_asignada;
                $User_Rel_Serie->documento_serie='FACTURAS';
                $User_Rel_Serie->save();
                if (App::environment('local') || App::environment('production')) {
                    DB::unprepared('SET IDENTITY_INSERT user_rel_series OFF');
                }
            }
        }
        //series asignadas notas
        $eliminarseriesasignadasnotas = User_Rel_Serie::where('user_id', $request->id_usuario_permisos)->where('documento_serie', 'NOTAS')->forceDelete();
        if ($request->has("seriesnotasasignadas")){
            foreach($request->seriesnotasasignadas as $serie_nota_asignada){
                $id = Helpers::ultimoidregistrotabla('App\User_Rel_Serie');
                if (App::environment('local') || App::environment('production')) {
                    DB::unprepared('SET IDENTITY_INSERT user_rel_series ON');
                }
                $User_Rel_Serie = new User_Rel_Serie;
                $User_Rel_Serie->id = $id;
                $User_Rel_Serie->user_id = $request->id_usuario_permisos;
                $User_Rel_Serie->serie_id = $serie_nota_asignada;
                $User_Rel_Serie->documento_serie='NOTAS';
                $User_Rel_Serie->save();
                if (App::environment('local') || App::environment('production')) {
                    DB::unprepared('SET IDENTITY_INSERT user_rel_series OFF');
                }
            }
        }
        //series asignadas pagos
        $eliminarseriesasignadaspagos = User_Rel_Serie::where('user_id', $request->id_usuario_permisos)->where('documento_serie', 'PAGOS')->forceDelete();
        if ($request->has("seriespagosasignadas")){
            foreach($request->seriespagosasignadas as $serie_pago_asignada){
                $id = Helpers::ultimoidregistrotabla('App\User_Rel_Serie');
                if (App::environment('local') || App::environment('production')) {
                    DB::unprepared('SET IDENTITY_INSERT user_rel_series ON');
                }
                $User_Rel_Serie = new User_Rel_Serie;
                $User_Rel_Serie->id = $id;
                $User_Rel_Serie->user_id = $request->id_usuario_permisos;
                $User_Rel_Serie->serie_id = $serie_pago_asignada;
                $User_Rel_Serie->documento_serie='PAGOS';
                $User_Rel_Serie->save();
                if (App::environment('local') || App::environment('production')) {
                    DB::unprepared('SET IDENTITY_INSERT user_rel_series OFF');
                }
            }
        }
        return response()->json($User_Rel_Menu);
    }

    //obtener series en documentos del usuario
    public function usuarios_obtener_series_documentos_usuario(Request $request){
        if($request->ajax()){
            $id = $request->id;
            $usuario = User::where('id', $id)->first();
            $data = Serie::where('Usuario', $usuario->user)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $operaciones =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatosserie(\''.$data->Documento.'\',\''.$data->Serie.'\',\''.$data->Usuario.'\',\''.$data->Nombre.'\')"><i class="material-icons">mode_edit</i></div> ';
                        return $operaciones;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }

    //obtener tipos documentos
    public function usuarios_obtener_tipos_documentos(){
        $tipos_documentos = Documento::where('status', 'ALTA')->get();
        $select_tipos_documentos = "<option selected disabled hidden>Selecciona...</option>";
        foreach($tipos_documentos as $tipo){
            $select_tipos_documentos = $select_tipos_documentos."<option value='".$tipo->documento."'>".$tipo->documento."</option>";
        }
        return response()->json($select_tipos_documentos);
    }

    //altas
    public function usuarios_guardar_serie_documento(Request $request){
        $documento=$request->documento;
        $serie = $request->serie;
        $usuario = $request->usuario;
	    $ExisteSerie = Serie::where('Documento', $documento)->where('Serie', $serie)->where('Usuario', $usuario)->first();
	    if($ExisteSerie == true){
            $Serie = 1;
	    }else{
            //insertar registro
            $Serie = new Serie;
		    $Serie->Documento=$documento;
		    $Serie->Serie=$serie;
            $Serie->Nombre=$request->nombre;
            $Serie->Usuario=$usuario;
            $Serie->Item=1;
            $Serie->save();
      	}
    	return response()->json($Serie); 
    }

    //cambios
    public function usuarios_guardar_modificacion_serie_documento(Request $request){
        $documento=$request->documento;
        $serie = $request->serie;
        $usuario = $request->usuario;
	    $Serie = Serie::where('Documento', $documento)->where('Serie', $serie)->where('Usuario', $usuario)->first();
        Serie::where('Documento', $documento)->where('Serie', $serie)->where('Usuario', $usuario)
                ->update([
                    'Nombre' => $request->nombre
                ]);
    	return response()->json($Serie); 
    }

    //obtener acceso al menu y permisos que tiene asignado el usuario logueado
    public function usuarios_obtener_submenus_activos(){
        //accesos al menu
        $submenus_activos = User_Rel_Menu::where('user_id', Auth::user()->id)->get();
        $array_submenus = array();
        foreach($submenus_activos as $submenu_activo){
            $submenu = Menu::where('id', $submenu_activo->menu_id)->first();
            $array_submenus[]=array(
                "0"=>$submenu->name,
                "1"=>$submenu_activo->status,
              );

        }
        //permisos crud
        $permisos_crud_activos = User_Rel_Permiso::where('user_id', Auth::user()->id)->get();
        $array_permisos_crud = array();
        foreach($permisos_crud_activos as $permiso_crud_activo){
            $permiso = Permiso::where('id', $permiso_crud_activo->permiso_id)->first();
            $array_permisos_crud[]=array(
                "0"=>$permiso->name,
                "1"=>$permiso_crud_activo->status,
              );

        }
        $data = array(
            "array_submenus" => $array_submenus,
            "array_permisos_crud" => $array_permisos_crud
        );
        return response()->json($data);
    }
    //cambiar la contraseña asignada por default del usuario al primer logueo del sistema
    public function cambiar_contrasena(Request $request){
        $User = User::where('id', Auth::user()->id)->first();
        User::where('id', Auth::user()->id)
        ->update([
            'password' => Hash::make($request->pass),
            'first_login' => 1
        ]);
        /*
        $User->password=Hash::make($request->pass);
        $User->first_login=1;
        $User->save();
        */
        return redirect('inicio')->with('success','Contraseña Cambiada Correctamente');
    }
}