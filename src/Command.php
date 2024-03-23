<?php

namespace TelegramBot;

use PDO;

class Command
{
    private int $id;
    private string $command;
    private string $message;
    private $db;

    function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }
    public function getAllCommands(){
        $sql = <<<SQL
SELECT id, command, message FROM commandes;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMessageByID($commandID){
        $sql = <<<SQL
SELECT id, command, message FROM commandes WHERE id=:command_id;
SQL;
        $values = ['command_id'=>$commandID];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addCommand($command){
        $sql = <<<SQL
INSERT INTO commandes (command, message)
VALUES (:command, :message)
SQL;
        $values = [
            'command'=>$command['name'],
            'message'=>$command['message']
        ];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $this->db->lastInsertId();
    }

    public function editCommand($command){
        $sql = <<<SQL
UPDATE commandes
SET command=:command, message=:message
WHERE id=:message_id;
SQL;
        $values = [
            "message_id"=>$command['edit'],
            "command" => $command['name'][$command['edit']],
            "message" => $command['message'][$command['edit']]
        ];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }

    public function deleteCommand($commandID){
        $sql = <<<SQL
DELETE FROM commandes WHERE id=:command_id;
SQL;
        $values = ["command_id"=>$commandID];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }
}