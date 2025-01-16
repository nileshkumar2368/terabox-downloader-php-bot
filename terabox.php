<?php
// Telegram bot token
$botToken = "7433420155:AAEzE7D-IroJzerr-pdaBqe1VCnn5HHwyrk";
$apiUrl = "https://api.telegram.org/bot$botToken";

// Function to send a message
function sendMessage($chatId, $message) {
    global $apiUrl;
    $url = $apiUrl . "/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message
    ];
    file_get_contents($url . "?" . http_build_query($data));
}

// Function to process the TeraBox API
function processLink($url) {
    $apiEndpoint = "https://testterabox.vercel.app/api";
    $headers = [
        "Content-Type: application/json",
        "User-Agent: Postify/1.0.0"
    ];

    $body = json_encode(["url" => $url]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Error: " . curl_error($ch);
    }

    curl_close($ch);
    $data = json_decode($response, true);

    if (isset($data['direct_link'])) {
        $proxyUrl = "https://teraboxdownloader.online/proxy.php?url=";
        $shortLink = file_get_contents("https://tinyurl.com/api-create.php?url=" . urlencode($proxyUrl . urlencode($data['direct_link'])));
        return $shortLink ? $shortLink : "Error shortening the link.";
    }

    return "Failed to process the link. Please ensure the link is valid.";
}

// Process incoming webhook updates
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update || !isset($update["message"])) {
    exit;
}

$chatId = $update["message"]["chat"]["id"];
$text = $update["message"]["text"] ?? '';

if ($text === "/start") {
    sendMessage($chatId, "Welcome to the TeraBox Bot! Send me any link, and I'll try to process it for a direct download link.");
} else {
    $result = processLink($text);
    sendMessage($chatId, $result);
}
