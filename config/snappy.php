<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Snappy PDF / Image Configuration
    |--------------------------------------------------------------------------
    |
    | This option contains settings for PDF generation.
    |
    | Enabled:
    |    
    |    Whether to load PDF / Image generation.
    |
    | Binary:
    |    
    |    The file path of the wkhtmltopdf / wkhtmltoimage executable.
    |
    | Timout:
    |    
    |    The amount of time to wait (in seconds) before PDF / Image generation is stopped.
    |    Setting this to false disables the timeout (unlimited processing time).
    |
    | Options:
    |
    |    The wkhtmltopdf command options. These are passed directly to wkhtmltopdf.
    |    See https://wkhtmltopdf.org/usage/wkhtmltopdf.txt for all options.
        https://github.com/wemersonjanuario/wkhtmltopdf-windows
        https://github.com/barryvdh/laravel-snappy#wkhtmltopdf-installation
    |
    | Env:
    |
    |    The environment variables to set while running the wkhtmltopdf process.
        Instalar ejecutable para windows 32 / 64 bits https://wkhtmltopdf.org/downloads.html
        Despues instalar dependencia composer require wemersonjanuario/wkhtmltopdf-windows "0.12.2.3"
    |
    */
    
    'pdf' => [
        'enabled' => true,
        'binary' => base_path('vendor/wemersonjanuario/wkhtmltopdf-windows/bin/64bit/wkhtmltopdf.exe'),
        //'binary' => base_path('vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64'),
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],
    
    'image' => [
        'enabled' => true,
        'binary' => base_path('vendor/wemersonjanuario/wkhtmltopdf-windows/bin/64bit/wkhtmltoimage.exe'),
        //'binary' => base_path('vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64'),
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],

];
