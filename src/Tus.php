<?php

namespace OneOffTech\TusUpload;

use Illuminate\Support\Facades\Route;

class Tus
{

    /**
     * Binds the Tus Upload routes
     *
     * @return void
     */
    public static function routes()
    {
        Route::group([
            'prefix' => '',
            'namespace' => 'OneOffTech\TusUpload\Http\Controllers',
            'middleware' => 'auth:api',
        ], function ($router) {

            $router->resource(
                '/api/uploadjobs', 
                'TusUploadQueueController', 
                [
                    'only' => [
                        'index', 'store', 'destroy'
                    ],
                    'names' => [
                        'index' => 'tus.jobs.index',
                        'store' => 'tus.jobs.store',
                        'destroy' => 'tus.jobs.destroy',
                    ]
                ]
            );

            $router->post('/api/uploadjobs/events','TusUploadQueueController@handle');
            $router->post('/api/uploadjobs/cancel','TusUploadQueueController@cancel');

        });

    }
}