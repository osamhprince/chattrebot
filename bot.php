<?php
/**
 * بوت تلجرام مطور - دمج الأزرار مع ذكاء Gemini الاصطناعي
 */
$API_KEY = '8539850843:AAFuOcsI8meIsm9DLd6tSHn5DYxrj4mLT98'; // توكن التلجرام
$GEMINI_KEY = 'AIzaSyAryqUo-RBQUZCRMan697sirjNrwFvG83o'; // مفتاح Gemini AI
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
/**
 * دالة التواصل مع Gemini AI
 */
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
    curl_setopt($ch, CURLOPT_HTTPHEADER: ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? "عذرًا، واجهت مشكلة في التفكير.";
}
// استقبال التحديثات
$update = json_decode(file_get_contents('php://input'));
if (isset($update->message)) {
    $message = $update->message;
    $chat_id = $message->chat->id;
    $text    = $message->text;
    $first_name = $message->from->first_name;
    // 1. التعامل مع أمر البداية /start
    if ($text == '/start') {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => "قناة المطور 📢", 'url' => "https://t.me/dev_osamh"],
                    ['text' => "حساب المطور 👨‍💻", 'url' => "https://t.me/dev_osamh"]
                ]
            ]
        ];
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "أهلاً بك يا $first_name! أنا الآن مدمج بذكاء اصطناعي (Gemini). أرسل لي أي شيء وسأرد عليك فوراً! 🤖",
            'reply_markup' => json_encode($keyboard)
        ]);
    } 
    // 2. التعامل مع أي رسالة نصية أخرى عبر Gemini
    elseif (!empty($text)) {
        // إظهار حالة "يكتب..." لتحسين التجربة
        bot('sendChatAction', [
            'chat_id' => $chat_id,
            'action' => 'typing'
        ]);
        $ai_response = askGemini($text);
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $ai_response,
            'parse_mode' => 'Markdown'
        ]);
    }
}
