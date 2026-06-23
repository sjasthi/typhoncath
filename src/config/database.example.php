<?php

return [
    'host'     => getenv('DB_HOST') ?: 'localhost',
    'port'     => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'your_database_name',
    'username' => getenv('DB_USER') ?: 'your_db_user',
    'password' => getenv('DB_PASS') ?: 'your_db_password',
];
