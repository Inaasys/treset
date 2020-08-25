<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class HomeController extends ConfiguracionSistemaController
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    //ruta inicio sistema
    public function inicio(){
        return view('welcome');
    }
}
