<?php

namespace TelegramBot;

use PDO;

class User
{
    private int $id;
    private string $firstName;
    private string $lastName;
    private int $telegramId;
    private string $currentIndex;
    private $isFinish;
    private $db;

    function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }
    public function getAllUsers(){
        $sql = <<<SQL
SELECT id, first_name, last_name, telegram_id, current_index, is_finish FROM users;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function addUser($user){
        $sql = <<<SQL
INSERT INTO users (first_name, last_name, telegram_id, current_index, is_finish)
VALUES (:first_name, :last_name, :telegram_id, :current_index, :is_finish)
SQL;
        $values = [
            'first_name'=>$user['first_name'],
            'last_name'=>$user['last_name'],
            'telegram_id'=>$user['telegram_id'],
            'current_index'=>$user['current_index'],
            'is_finish'=>$user['is_finish']
        ];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $this->db->lastInsertId();
    }

    public function getCurrentIndex($telegramId){
        $sql = <<<SQL
SELECT current_index FROM users WHERE telegram_id=:telegram_id;
SQL;
        $values = ['telegram_id'=>$telegramId];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['current_index'];
    }

    public function updateCurrentIndex($telegramId, $currentIndex){
        $sql = <<<SQL
UPDATE users
SET current_index=:current_index
WHERE telegram_id=:telegram_id;
SQL;
        $values = [
            "current_index" => $currentIndex,
            "telegram_id" => $telegramId
        ];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }

    public function updateFinishStatus($telegramId){
        $sql = <<<SQL
UPDATE users
SET is_finish=1
WHERE telegram_id=:telegram_id;
SQL;
        $values = [
            "telegram_id" => $telegramId
        ];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }

    public function checkUserExists($telegramId): bool
    {
        $sql = <<<SQL
SELECT telegram_id FROM users WHERE telegram_id=:telegram_id;
SQL;
        $values = ['telegram_id'=>$telegramId];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['telegram_id']) {
            return true;
        }
        return false;
    }

    public function checkUserFinished($telegramId): bool
    {
        $sql = <<<SQL
SELECT is_finish FROM users WHERE telegram_id=:telegram_id;
SQL;
        $values = ['telegram_id'=>$telegramId];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['is_finish']) {
            return true;
        }
        return false;
    }



}