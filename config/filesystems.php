<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'tmp' => [
            'driver' => 'local',
            'root'   => storage_path('app').'/tmp',
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => str_replace('/public', '', env('APP_URL') ? env('APP_URL') : '').'/public/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => '',
            'secret' => '',
            'region' => '',
            'bucket' => '',
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'visibility' => 'public',
        ],

        'wasabi' => [
            'driver' => 'wasabi',
            'key' => '',
            'secret' => '',
            'region' => '',
            'bucket' => '',
            'root' => '/',
            'visibility' => 'public',
        ],

        'do_spaces' => [
            'driver' => 's3',
            'key' => '',
            'secret' => '',
            'endpoint' => '',
            'region' => '',
            'bucket' => '',
            'visibility' => 'public',
        ],


        'minio' => [
            'driver' => 's3',
            'endpoint' => '',
            'url' => '',
            'key' => '',
            'secret' => '',
            'region' => '',
            'bucket' => '',
            'visibility' => 'public',
            'use_path_style_endpoint' => true,
            #'bucket_endpoint' => true,
        ],

    ],
    'defaultFilesystemDriver' => '',
];
