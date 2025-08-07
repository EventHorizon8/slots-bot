<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => env('DATABASE_URL', ''),
    'username' => env('DATABASE_USERNAME', ''),
    'password' => env('DATABASE_PASSWORD', ''),
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
