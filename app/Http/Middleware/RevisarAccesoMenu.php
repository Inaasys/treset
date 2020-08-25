<?php

namespace App\Http\Middleware;

use Closure;

use App\Menu;

use App\User_Rel_Menu;

class RevisarAccesoMenu
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $menusactivos)
    {
        $explodemenusactivos = explode('|',$menusactivos);
        $contador_accede_a_url = 0;
        foreach($explodemenusactivos as $menuactivo) {
            $buscar_acceso_menu = Menu::where('name', $menuactivo)->first();//se obtiene el registro del menu
            //se comprueba si el usuario logueado tiene acceso a ese menu
            $contadormenu = User_Rel_Menu::where('user_id', auth()->user()->id)->where('menu_id', $buscar_acceso_menu->id)->where('status', 'true')->count();
            if($contadormenu > 0){
                    $contador_accede_a_url++;
                }       
        }
        
        if($contador_accede_a_url > 0){
            return $next($request);
        }else{
            //abort(403, 'No tienes permisos para ingresar a esa pagina!'); 
            return redirect('inicio')->with('error','No tienes permisos para ingresar a esa pagina, contacta al administrador del sistema');             
        }

        /*
        $menus = User_Rel_Menu::where('user_id', auth()->user()->id);
        $contador_accede_a_url = 0;
        foreach($menus as $menu){
            $menu = Menu::where('id', $menu->menu_id)->first();
            if($menu == $menusactivos){
                $contador_accede_a_url++;
            }
        }
        //dd($contador_accede_a_url);
        if($contador_accede_a_url > 0){
            return $next($request);
        }else{
            return redirect('inicio')->with('error','No tienes permisos para ingresar a esa pagina');
            //return redirect()->back()->with('error','No tienes permisos para ingresar a esa pagina');
            
        }
        */

    }
}
