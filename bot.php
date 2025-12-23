<?php

/**
 * بوت فحص النماذج المتاحة (Diagnostic Tool)
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

// دالة لجلب قائمة النماذج التي يسمح بها مفتاحك
function listAvailableModels() {
    global $GEMINI_KEY;
    // سنحاول عبر v1beta أولاً لأنها تظهر تفاصيل أكثر
    $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $GEMINI_KEY;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);
    
    if ($http_code == 200 && isset($result['models'])) {
        $model_names = [];
        foreach ($result['models'] as $m) {
            // نأخذ فقط النماذج التي تدعم توليد المحتوى
            if (in_array("generateContent", $m['supportedGenerationMethods'])) {
                $model_names[] = str_replace("models/", "", $m['name']);
            }
        }
        return "النماذج المتاحة لمفتاحك هي:\n✅ " . implode("\n✅ ", $model_names);
    }

    return "فشل في جلب القائمة. كود الخطأ: $http_code\nالرسالة: " . ($result['error']['message'] ?? 'غير معروفة');
}

$update = json_decode(file_get_contents('php://input'));

if (isset($update->message)) {
    $message = $update->message;
    $chat_id = $message->chat->id;
    $text    = $message->text;

    // بمجرد إرسال أي رسالة، سيرد البوت بقائمة النماذج
    $status = listAvailableModels();
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "تقرير الفحص التقني:\n\n" . $status . "\n\nأرسل لي اسم النموذج الذي تريد استخدامه من القائمة أعلاه."
    ]);
}
