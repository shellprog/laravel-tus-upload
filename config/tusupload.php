<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tus executable path
    |--------------------------------------------------------------------------
    |
    | This is the path of the tus server executable.
    |
    */
    
    'executable' => env('TUSUPLOAD_EXECUTABLE') ?: __DIR__ . '/../bin/tusd-',

    /*
    |--------------------------------------------------------------------------
    | Tus hooks path
    |--------------------------------------------------------------------------
    |
    | Where the hooks script can be found.
    |
    */

    'hooks' => env('TUSUPLOAD_HOOKS_DIRECTORY') ?: '',

    'hooks_url' => env('TUSUPLOAD_HOOKS_URL') ?: __DIR__ . 'http://127.0.0.1/api/tus-events',
    
    /*
    |--------------------------------------------------------------------------
    | Tus is behind a proxy
    |--------------------------------------------------------------------------
    |
    | Respect X-Forwarded-* and similar headers which may be set by proxies
    |
    */

    'behind_proxy' => env('TUSUPLOAD_USE_PROXY') ?: false,

    /*
    |--------------------------------------------------------------------------
    | Tus public URL
    |--------------------------------------------------------------------------
    |
    | The URL on which tus is exposed by the proxy.
    | Used only if behind_proxy is set to true.
    |
    */

    'public_url' => env('TUSUPLOAD_URL') ?: null,

    /*
    |--------------------------------------------------------------------------
    | Tus server host
    |--------------------------------------------------------------------------
    |
    | Host to bind HTTP server to
    |
    */
    
    'host' => env('TUSUPLOAD_HOST', '127.0.0.1'),

    /*
    |--------------------------------------------------------------------------
    | Tus server port
    |--------------------------------------------------------------------------
    |
    | Port to bind HTTP server to
    |
    */
    'port' => env('TUSUPLOAD_PORT', 1080),
    
    
    /*
    |--------------------------------------------------------------------------
    | Tus server Base path
    |--------------------------------------------------------------------------
    |
    | Basepath of the HTTP server
    |
    */

    'base_path' => env('TUSUPLOAD_HTTP_PATH', "/uploads/"),

    /*
    |--------------------------------------------------------------------------
    | Upload folder Configuration
    |--------------------------------------------------------------------------
    |
    | The folder used to store the files that are being uploaded via the Tus 
    | protocol.
    |
    */

    'storage' => env('TUSUPLOAD_STORAGE_PATH') ?: public_path('/uploads'),
    
    /*
    |--------------------------------------------------------------------------
    | Upload storage maximum usable 
    |--------------------------------------------------------------------------
    |
    | The maximum amount of space to use for storing the uploads
    |
    | Default null: unlimited
    |
    */

    'storage_size' => env('TUSUPLOAD_STORAGE_MAXIMUM_SIZE') ?: null,


    /*
    |--------------------------------------------------------------------------
    | Expose metrics about tusd usage
    |--------------------------------------------------------------------------
    |
    | Enables the Prometheus usage metric report. 
    | Metrics endpoint will be /metrics
    |
    */

    'expose_metrics' => false,

];
