<?php

namespace TelegramBot;

use Exception;
use GuzzleHttp\Client;

class TelegramBot {
    private string $token;
    private string $publicUrl;

    public function __construct($token, $publicUrl)
    {
        $this->token = $token;
        $this->publicUrl = $publicUrl;
    }

    public function setupWebhook()
    {
        $webhookUrl = 'https://api.telegram.org/bot' . $this->token . '/setWebhook?url=' . $this->publicUrl;
        $client = new Client();

        try {
            $response = $client->post($webhookUrl);
            if ($response->getStatusCode() === 200) {
                $responseBody = json_decode($response->getBody(), true);
                var_dump($responseBody);
            } else {
                echo 'Failed to set up webhook. Status code: ' . $response->getStatusCode();
            }
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function handleMessage($update, $messages, $currentIndex)
    {
        $chatId = $update['message']['chat']['id'];
        $messageText = strtolower($update['message']['text']);

        if ($currentIndex == 100) {
            $this->sendMessage($chatId, "Please, click on /finish");
            return;
        }

        if ($messages === "You have already finished the survey.") {
            $this->sendMessage($chatId, $messages, 1);
            return;
        }

        if($messageText === '/finish'){
            $this->sendMessage($chatId, "Thank you for your time. Have a nice day!", null, 0, 1);
            return;
        }

        // Check if the message is the predefined reply button
        if ($messageText === "understood") {
            if ($currentIndex <= (count($messages) + 1)) {
                // If the message has an image, send it the image
                if($messages[$currentIndex]['image']){
                    $this->sendMessage($chatId, $messages[$currentIndex]['message'], $messages[$currentIndex]['image'], $messages[$currentIndex]['image_name']);
                    return;
                }
                // Send the message without an image
                $this->sendMessage($chatId, $messages[$currentIndex]['message']);
                return;
            }
           return;
        }

        // Check if the command exists in the messages array
        $commandFound = false;
        foreach ($messages as $command => $message) {
            if ($messageText === "/".$command) {
                $this->sendMessage($chatId, $message);
                $commandFound = true;
                break;
            }
        }

        // If the command was not found, send an "Unknown command" message
        if (!$commandFound) {
            $this->sendMessage($chatId, 'Unknown command. Please, read the text and click on "Understood"');
        }
    }


    private function sendMessage($chatId, $message, $imageURL = null, $imageName = 0, $isFinish = 0)
    {
        $url = 'https://api.telegram.org/bot' . $this->token . '/sendMessage';
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML', // or 'Markdown'
            'reply_markup' => json_encode([
                'force_reply' => true,
                'keyboard' => [
                    [['text' => 'Understood']]
                ],
                'resize_keyboard' => true
            ]),
            'disable_web_page_preview' => true // Prevent URLs from being parsed
        ];

        // If imageURL is provided, include it in the message
//        if ($imageURL) {
//            $data['text'] .= "\n" . '<a href="' . $imageURL . '">'. $imageName .'</a>';
//        }

        // If imageURL is provided, send it as a photo
        if ($imageURL) {
            // Use sendPhoto method instead of adding a link to the text
            $photoData = [
                'chat_id' => $chatId,
                'photo' => $imageURL
            ];
            file_get_contents('https://api.telegram.org/bot' . $this->token . '/sendPhoto?' . http_build_query($photoData));
        }

        if ($isFinish) {
            $data['reply_markup'] = json_encode([
                'force_reply' => true,
                'remove_keyboard' => true
            ]);
        }

        $data['text'] = str_replace('/day', '&#47; day', $data['text']);
        $data['text'] = str_replace('/month', '&#47; month', $data['text']);

        file_get_contents($url . '?' . http_build_query($data));
    }
}