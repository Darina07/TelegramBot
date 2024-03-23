<?php

require_once __DIR__ . '/vendor/autoload.php';
$config = require 'config/config.php';

use TelegramBot\Command;
use TelegramBot\Database;
use TelegramBot\Message as CustomMessage;
use TelegramBot\TelegramBot;
use TelegramBot\User;

// Telegram API token
$token = $config['telegram_bot_token'];
// public URL
$publicUrl = $config['public_url'];

// Connect to the database
$db = new Database();
$db = $db->connect();

// Create a new instance of the TelegramBot class
$telegramBot = new TelegramBot($token, $publicUrl);
$telegramBotCommand = new Command($db);
$telegramBotMessage = new CustomMessage($db);
$users = new User($db);

// Set up the webhook
$telegramBot->setupWebhook();

// Get all messages from the database
$allMessages = $telegramBotMessage->getAllMassages();
var_dump(count($allMessages));

// Get all commands from the database
$result = $telegramBotCommand->getAllCommands();

// Create an array of commands
$messages = [];
foreach ($result as $item) {
    $messages[$item['command']] = $item['message'];
}

// Get incoming data from the webhook
$update = json_decode(file_get_contents('php://input'), true);

file_put_contents('./log_'.date("j.n.Y").'.log', $update, FILE_APPEND);
// Check if the update is valid
if ($update) {

    // Extract user information from the update
    $firstName = $update['message']['from']['first_name'];
    $lastName = $update['message']['from']['last_name'];
    $telegramId = $update['message']['from']['id'];

    // Check the message text
    $messageText = isset($update['message']['text']) ? $update['message']['text'] : '';

    // Handle different message scenarios
    switch ($messageText) {
        case '/start':
            // Check if the user already exists in the database
            $userExists = $users->checkUserExists($telegramId);
            if (!$userExists) {
                // Insert new user into the database who started the survey
                $users->addUser([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'telegram_id' => $telegramId,
                    'current_index' => 0,
                    'is_finish' => 0
                ]);
            }
            break;
        case '/finish':
            // Update the user's status to finish
            $users->updateFinishStatus($telegramId);
            break;
        case 'Understood':
            // Get the current index for the user
            $currentIndex = $users->getCurrentIndex($telegramId);

            if ($currentIndex == count($allMessages)) {
                $telegramBot->handleMessage($update, $messages, 100);
                break;
            }

            // Handle the message using the TelegramBot class
            $telegramBot->handleMessage($update, $allMessages, $currentIndex);
            // Update the current index for the user
            $users->updateCurrentIndex($telegramId, $currentIndex + 1);
            break;
        default:
            // Get the current index for the user
            $currentIndex = $users->getCurrentIndex($telegramId);
            // Handle the message using the TelegramBot class
            $telegramBot->handleMessage($update, $messages, $currentIndex);
    }


    $currentIndex = $users->getCurrentIndex($update['message']['from']['id']);
    $userExists = $users->checkUserExists($update['message']['from']['id']);
    $userFinished = $users->checkUserFinished($update['message']['from']['id']);
    if ($update && $update['message']['text'] === "/start" && $userExists && $userFinished) {
        $telegramBot->handleMessage($update, "You have already finished the survey.", 1);
    } else {
        $telegramBot->handleMessage($update, $messages, $currentIndex);
    }

} else {
    // Log error in a file
    file_put_contents('error.log', print_r($update, true));
}
