<?php

namespace App\Core;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $host = 'localhost';
            $dbname = 'icsbinco_typhoncath_db';      // change this
            $username = 'icsbinco_typhoncath_db_user';   // change this
            $password = 'TyphonCath!23';      // change this

            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        return self::$connection;
    }
}