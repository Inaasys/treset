<?php

namespace App\Http\Middleware;

use Closure;

use App\Permiso;

use App\User_Rel_Permiso;

class RevisarPermisos
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permisosactivos)
    {
        $explodepermisosactivos = explode('|',$permisosactivos);
        $contador_accede_a_url = 0;
        foreach($explodepermisosactivos as $permisoactivo) {
            $buscar_permiso_crud = Permiso::where('name', $permisoactivo)->first();//se obtiene el registro del menu
            //se comprueba si el usuario logueado tiene acceso a ese menu
            $contadorpermisocrud = User_Rel_Permiso::where('user_id', auth()->user()->id)->where('permiso_id', $buscar_permiso_crud->id)->where('status', 'true')->count();
            if($contadorpermisocrud > 0){
                    $contador_accede_a_url++;
            }       
        }
        
        if($contador_accede_a_url > 0){
            return $next($request);
        }else{
            //return redirect('inicio')->with('error','No tienes permisos para ingresar a esa pagina');  
            abort(403, 'No tienes permisos para ingresar a esa pagina!');      
        }
    }
}
