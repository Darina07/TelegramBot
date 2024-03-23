<?php

namespace TelegramBot;

class Database
{
    private ?DatabaseProvider $databaseProvider;
    public function __construct()
    {
        $this->databaseProvider = DatabaseProvider::getInstance();
    }

    function connect(){
        $config = require 'config/config.php';
        $this->databaseProvider::connectToDatabase($config['db_host'], $config['db_name'], $config['db_user'], $config['db_pass']);
        return $this->databaseProvider->getConnection();
    }
}