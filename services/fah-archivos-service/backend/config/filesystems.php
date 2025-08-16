<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar el disco del sistema de archivos predeterminado
    | que debe usar el framework. El disco "local", así como una variedad de
    | discos basados en la nube están disponibles para tu aplicación.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | A continuación puedes configurar tantos discos del sistema de archivos
    | como sea necesario, e incluso puedes configurar múltiples discos para
    | el mismo controlador. Ejemplos para la mayoría de controladores
    | soportados están configurados aquí como referencia.
    |
    | Controladores soportados: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        // CONFIGURACIÓN MINIO PARA FAH-ARCHIVOS-SERVICE
        'minio' => [
            'driver' => 's3',
            'key' => env('MINIO_KEY', 'fah_minio_admin'),
            'secret' => env('MINIO_SECRET', 'fah_minio_secret_2024'),
            'region' => env('MINIO_REGION', 'us-east-1'),
            'bucket' => env('MINIO_BUCKET', 'fah-archivos'),
            'endpoint' => env('MINIO_ENDPOINT', 'http://fah_minio:9000'),
            'use_path_style_endpoint' => true,
            'send_chunk_size' => 64 * 1024 * 1024, // 64MB chunks
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar los enlaces simbólicos que se crearán cuando se
    | ejecute el comando Artisan `storage:link`. Las claves del array deben
    | ser las ubicaciones de los enlaces y los valores deben ser sus destinos.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
