<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=forum', // Имя вашей БД
    'username' => 'root', // Ваше имя пользователя MySQL
    'password' => '',     // Ваш пароль MySQL (пустой по умолчанию для XAMPP)
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];