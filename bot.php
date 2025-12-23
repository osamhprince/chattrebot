<?php

$API_KEY = '8539850843:AAFuOcsI8meIsm9DLd6tSHn5DYxrj4mLT98';

function bot($method, $datas = []) {
    global $API_KEY;
    $url = "https://api.telegram.org/bot" . $API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // لتجنب مشاكل الشهادة
    $res = curl_exec($ch);
    return json_decode($res);
}

$update = json_decode(file_get_contents('php://input'));

if (isset($update->message)) {
    $message = $update->message;
    $chat_id = $message->chat->id;
    $text    = $message->text;
    $first_name = $message->from->first_name;

    if ($text == '/start') {
        // تجهيز الأزرار بشكل منفصل لتجنب أخطاء السناتكس
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
            'text' => "أهلاً بك يا $first_name في البوت الخاص بي! 🤖",
            'reply_markup' => json_encode($keyboard)
        ]);
    }
}
