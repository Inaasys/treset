<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Inaasys'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'America/Mexico_City',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'es',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',


    /*
    |--------------------------------------------------------------------------
    | VARIABLES DE CONFIGURACION DE LA APLICACION
    |--------------------------------------------------------------------------
    |
    | Variables utuilizadas para los diferentes modulos y catalogos
    |
    */
    //DOCUMENTACION API CURRENCY LAYER https://currencylayer.com/documentation
    'endpointapicurrencylayer' => 'live',
    'keyapicurrencylayer' => '6e2993559ed8ce04ef56d47f792e5a52',
    //API KEY FACTURAPI
    'keyfacturapi' => env('KEY_FACTURAPI'),
    'keygeneralfacturapi' => env('KEY_GENERAL_FACTURAPI'),
    'periodoincialmodulos' => '', //aqui se asigna el periodo inicial de los select de los modulos
    'numerodedecimales' => '', //aqui se asignan el numero de decimales que se utilizaran en todas las cantidades utilizadas en el sistema
    'numerodecimalesendocumentos' => '', //numero de decimales que se ocuparan en documentos PDF
    'mayusculas_sistema' => '', //aqui se aisgna si el sistema utilizara solo mayusculas o no
    'calleempresa' => '', //calle de la empresa
    'noexteriorempresa' => '', //no exterior de la empresa
    'nointeriorempresa' => '',//no interior de la empresa
    'coloniaempresa' => '', //colonia de la empresa
    'localidadempresa' => '',//localidad de la empresa
    'referenciaempresa' => '',//referencia de la empresa
    'cpempresa' => '', //cp de la empresa
    'municipioempresa' => '',//municipio de la empresa
    'estadoempresa' => '',//estado de la empresa
    'telefonosempresa' => '',//telefonos de la empresa
    'paisempresa' => '',//pais de la empresa
    'emailempresa' => '',//email de la empresa
    //para emisor documentos
    'lugarexpedicion' => '',//lugar expedicion
    'regimenfiscal' => '',//regimen fiscal
    //tipo de utilidad
    'tipodeutilidad' => '',
    //correo por default en envios de documentos
    'correodefault1enviodocumentos' => '',
    'correodefault2enviodocumentos' => '',
    //usuariis que puedes modificar insumos
    'usuariosamodificarinsumos' => '',
    'modificarcostosdeproductos' => '',
    'modificarcreditodeclientes' => '',
    'modificarcostoyventadeservicios' => '',
    // Para ligar una OT a una NP
    'ligarOTaCompra' => '',
    'suc2' => env('DB2_DATABASE'),
    'connsuc2' => 'sqlsrv2',
    'suc3' => env('DB3_DATABASE'),
    'connsuc3' => 'sqlsrv3',
    'suc4' => env('DB4_DATABASE'),
    'connsuc4' => 'sqlsrv4',



    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        Yajra\DataTables\DataTablesServiceProvider::class,
        //Barryvdh\DomPDF\ServiceProvider::class,
        Barryvdh\Snappy\ServiceProvider::class,
        Jenssegers\Date\DateServiceProvider::class,
        Maatwebsite\Excel\ExcelServiceProvider::class,
        SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class,
        LynX39\LaraPdfMerger\PdfMergerServiceProvider::class,
        Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider::class,
        Milon\Barcode\BarcodeServiceProvider::class,
        Jenssegers\Agent\AgentServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
        'DataTables' => Yajra\DataTables\Facades\DataTables::class,
        //'PDF' => Barryvdh\DomPDF\Facade::class,
        'PDF' => Barryvdh\Snappy\Facades\SnappyPdf::class,
        'SnappyImage' => Barryvdh\Snappy\Facades\SnappyImage::class,
        'Date' => Jenssegers\Date\Date::class,
        'Excel' => Maatwebsite\Excel\Facades\Excel::class,
        'QrCode' => SimpleSoftwareIO\QrCode\Facades\QrCode::class,
        'PdfMerger' => LynX39\LaraPdfMerger\Facades\PdfMerger::class,
        'DNS1D' => Milon\Barcode\Facades\DNS1DFacade::class,
        'DNS2D' => Milon\Barcode\Facades\DNS2DFacade::class,
        'Agent' => Jenssegers\Agent\Facades\Agent::class,

    ],

];
