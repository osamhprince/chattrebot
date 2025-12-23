<?php

/**
 * بوت تلجرام - الإصدار الحديث (ديسمبر 2025)
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
    
    // استخدام أحدث طراز متوفر في نهاية 2025 (Gemini 2.0 Flash)
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=" . $GEMINI_KEY;

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

    // التحقق من النجاح
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return $result['candidates'][0]['content']['parts'][0]['text'];
    }

    // محاولة احتياطية مع الموديل الأقدم في حال لم يتفعل 2.0 في حسابك بعد
    if ($http_code != 200) {
        $url_v3 = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash-latest:generateContent?key=" . $GEMINI_KEY;
        $ch2 = curl_init($url_v3);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_POST, true);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        $resp2 = curl_exec($ch2);
        curl_close($ch2);
        $res2 = json_decode($resp2, true);
        if (isset($res2['candidates'][0]['content']['parts'][0]['text'])) {
            return $res2['candidates'][0]['content']['parts'][0]['text'];
        }
    }

    $msg = $result['error']['message'] ?? 'خطأ غير معروف';
    return "لا يزال هناك تعارض في الموديل لدى قوقل.\nالبيان: $msg\nكود الحالة: $http_code";
}

$update = json_decode(file_get_contents('php://input'));

if (isset($update->message)) {
    $message = $update->message;
    $chat_id = $message->chat->id;
    $text    = $message->text;

    if ($text == '/start') {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "تم الترقية إلى Gemini 2.0 الحديث 🚀\nجرب التحدث معي الآن بالذكاء الاصطناعي المتطور."
        ]);
    } 
    elseif (!empty($text)) {
        bot('sendChatAction', ['chat_id' => $chat_id, 'action' => 'typing']);
        $ai_response = askGemini($text);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => $ai_response]);
    }
}
