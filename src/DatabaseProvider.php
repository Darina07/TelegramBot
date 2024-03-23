<?php

namespace TelegramBot;

use PDO;

final class DatabaseProvider
{
    private static ?DatabaseProvider $instance = null;
    private static PDO $conn;
    private function __construct(){}
    private function __wakeup(){}
    private function __clone(){}

    public static function getInstance(): DatabaseProvider{
        if(is_null(self::$instance)){
            self::$instance = new DatabaseProvider();
        }
        return self::$instance;
    }

    public static function connectToDatabase($host,$dbName,$user,$pass) {
        self::$conn = new PDO('mysql:host='.$host.';dbname='.$dbName, $user, $pass);
    }

    public function getConnection()
    {
        return self::$conn;
    }
}