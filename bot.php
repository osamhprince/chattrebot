<?php
$telegram_token = "8539850843:AAFuOcsI8meIsm9DLd6tSHn5DYxrj4mLT98";
$gemini_api_key = "AIzaSyAryqUo-RBQUZCRMan697sirjNrwFvG83o";
$update = json_decode(file_get_contents('php://input'), true);
if (!$update) exit;
$message = $update['message'] ?? null;
$chat_id = $message['chat']['id'] ?? null;
$text = $message['text'] ?? '';
if ($chat_id && $text) {
    sendAction($chat_id, "typing");
    $ai_response = askGemini($text, $gemini_api_key);
    sendMessage($chat_id, $ai_response);
}
function askGemini($prompt, $api_key) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $api_key;
    $data = ["contents" => [["parts" => [["text" => $prompt]]]]];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? "عذرًا، واجهت مشكلة.";
}
function sendMessage($chat_id, $text) {
    global $telegram_token;
    $url = "https://api.telegram.org/bot" . $telegram_token . "/sendMessage";
    $data = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'Markdown'];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_exec($ch);
    curl_close($ch);
}
function sendAction($chat_id, $action) {
    global $telegram_token;
    $url = "https://api.telegram.org/bot" . $telegram_token . "/sendChatAction";
    $data = ['chat_id' => $chat_id, 'action' => $action];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_exec($ch);
    curl_close($ch);
}
