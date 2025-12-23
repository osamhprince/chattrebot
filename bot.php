<?php

/**
 * بوت تلجرام مطور - النسخة النهائية المستقرة
 */

$API_KEY = '8539850843:AAFuOcsI8meIsm9DLd6tSHn5DYxrj4mLT98'; 
$GEMINI_KEY = 'AIzaSyAryqUo-RBQUZCRMan697sirjNrwFvG83o'; 

function bot($method, $datas = []) {
    global $API_KEY;
    $url = "https://api.telegram.org/bot" . $API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    return json_decode($res);
}

function askGemini($prompt) {
    global $GEMINI_KEY;
    // تم التغيير إلى v1 لضمان الاستقرار
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=" . $GEMINI_KEY;

    $data = [
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? "عذرًا، واجهت مشكلة في التفكير.";
}

$update = json_decode(file_get_contents('php://input'));

if (isset($update->message)) {
    $message = $update->message;
    $chat_id = $message->chat->id;
    $text    = $message->text;
    $first_name = $message->from->first_name;

    if ($text == '/start') {
        $keyboard = [
            'inline_keyboard' => [[
                ['text' => "قناة المطور 📢", 'url' => "https://t.me/dev_osamh"],
                ['text' => "حساب المطور 👨‍💻", 'url' => "https://t.me/dev_osamh"]
            ]]
        ];

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "أهلاً بك يا $first_name! أنا الآن أعمل بشكل مستقر. جرب محادثتي الآن! 🤖",
            'reply_markup' => json_encode($keyboard)
        ]);
    } 
    elseif (!empty($text)) {
        bot('sendChatAction', ['chat_id' => $chat_id, 'action' => 'typing']);
        $ai_response = askGemini($text);
        bot('sendMessage', [
            'chat_id' => $chat_id, 
            'text' => $ai_response
        ]);
    }
}
