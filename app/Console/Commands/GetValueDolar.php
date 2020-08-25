<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;

class GetValueDolar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:valuedolar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Obtiene el valor del dolar actualizado cada 12 horas en la API: http://api.currencylayer.com/';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $endpoint = config('app.endpointapicurrencylayer');
        $access_key = config('app.keyapicurrencylayer');
        define('VT_URL', 'http://api.currencylayer.com/'.$endpoint.'?access_key='.$access_key.'&currencies=MXN');
        //crear cliente Guzzle HTTP
        $cliente = new Client();
        //respuesta de API
        $respuesta = $cliente->request('GET', VT_URL, []);
        $resultado = json_decode($respuesta->getBody());
        //obtener valor del dolar
        $valor_dolar = $resultado->quotes->USDMXN;
    }
}
