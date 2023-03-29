<?php

return [


    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env( 'DB_GEONAMES_CONNECTION', 'geonames' ),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'geonames' => [
            'driver'      => env( 'DB_GEONAMES_DRIVER', 'pgsql' ),
            'host'        => env( 'DB_GEONAMES_HOST', 'db-pg' ),
            'port'        => env( 'DB_GEONAMES_PORT', 5432 ),
            'database'    => env( 'DB_GEONAMES_DATABASE', 'postgres' ),
            'username'    => env( 'DB_GEONAMES_USERNAME', 'postgres' ),
            'password'    => env( 'DB_GEONAMES_PASSWORD', 'password' ),
            'charset'     => 'utf8',
            'prefix'      => '',
            'strict'      => TRUE,
            'engine'      => NULL,
            //'options'     => [ \PDO::MYSQL_ATTR_LOCAL_INFILE => TRUE, ],
            //'options'     => extension_loaded( 'pdo_mysql' ) ? array_filter( [
                                                                            //      PDO::MYSQL_ATTR_SSL_CA       => env( 'MYSQL_ATTR_SSL_CA' ),
                                                                            //      PDO::MYSQL_ATTR_LOCAL_INFILE => TRUE,
                                                                            //  ] ) : [],
        ],
    ],

];