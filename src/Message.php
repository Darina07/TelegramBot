<?php

namespace TelegramBot;

use PDO;

class Message
{
    private int $id;
    private string $message;

    private string $image;

    private string $imageName;
    private $db;

    function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }
    public function getAllMassages(){
        $sql = <<<SQL
SELECT id, message, image, image_name FROM messages;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMessageByID($messageID){
        $sql = <<<SQL
SELECT id, message FROM messages WHERE id=:message_id;
SQL;
        $values = ['message_id'=>$messageID];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addMessage($message){
        $sql = <<<SQL
INSERT INTO messages (message)
VALUES (:message)
SQL;
        $values = [
            'message'=>$message['message']
        ];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $this->db->lastInsertId();
    }

    public function editMessage($message){
        $sql = <<<SQL
UPDATE messages
SET message=:message
WHERE id=:message_id;
SQL;
        $values = [
            "message_id"=>$message['edit'],
            "message" => $message['message'][$message['edit']]
        ];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }

    public function deleteMessage($messageID){
        $sql = <<<SQL
DELETE FROM messages WHERE id=:message_id;
SQL;
        $values = ["message_id"=>$messageID];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }
}