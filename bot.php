<?php
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
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $GEMINI_KEY;
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
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return $result['candidates'][0]['content']['parts'][0]['text'];
    }
    
    // إذا فشل، أرسل لنا تفاصيل الخطأ
    if (isset($result['error']['message'])) {
        return "خطأ من قوقل: " . $result['error']['message'];
    }
    return "خطأ غير معروف. كود الاستجابة: " . $http_code . "\nالرد الخام: " . substr($response, 0, 100);
}
$update = json_decode(file_get_contents('php://input'));
if (isset($update->message)) {
    $message = $update->message;
    $chat_id = $message->chat->id;
    $text    = $message->text;
    if ($text == '/start') {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "أنا جاهز! أرسل لي أي رسالة وسأحاول الرد باستخدام Gemini المستكشف للأخطاء."
        ]);
    } elseif (!empty($text)) {
        bot('sendChatAction', ['chat_id' => $chat_id, 'action' => 'typing']);
        $ai_response = askGemini($text);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => $ai_response]);
    }
}
