<?php

// ضع التوكن الخاص بك هنا
$API_KEY = '8539850843:AAFuOcsI8meIsm9DLd6tSHn5DYxrj4mLT98';

// وظيفة لإرسال الطلبات إلى تليجرام
function bot($method, $datas = []) {
    global $API_KEY;
    $url = "https://api.telegram.org/bot" . $API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        var_dump(curl_error($ch));
    } else {
        return json_decode($res);
    }
}

// استقبال البيانات القادمة من تليجرام
$update = json_decode(file_get_contents('php://input'));

if (isset($update->message)) {
    $message = $update->message;
    $chat_id = $message->chat->id;
    $text    = $message->text;
    $first_name = $message->from->first_name;

    // الرد على أمر البداية /start
    if ($text == '/start') {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "أهلاً بك يا $first_name في البوت الخاص بي! 🤖",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => "قناة المطور 📢", 'url' => "https://t.me/dev_osamh"],
                        ['text' => "حساب المطور 👨‍💻", 'url' => "https://t.me/dev_osamh"]"]
                    ]
                ]
            ])
        ]);
    }
}
