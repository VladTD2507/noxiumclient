<?php

include_once("../../../backend/db.php");
include_once("../../../config.php");
$currentTime = time();

function encrypt($data, $key) {
    $keyLength = strlen($key);
    $output = '';

    for ($i = 0; $i < strlen($data); $i++) {
        $keyChar = $key[$i % $keyLength];
        $dataChar = $data[$i];
        $shift = ord($keyChar) % 5;

        $encryptedChar = chr((ord($dataChar) + $shift) % 256);
        $output .= $encryptedChar;
    }

    return $output;
}

$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

$nickname = isset($data['login']) ? $data['login'] : null;
$passwordInput = isset($data['pass']) ? $data['pass'] : null;
$hwid = isset($data['hwid']) ? $data['hwid'] : null;
$token = isset($data['token']) ? $data['token'] : null;
$message = isset($data['message']) ? $data['message'] : null;

if ($nickname && $passwordInput && $hwid && $token && isset($message) && strlen($token) == 16) {
    $stmt = $conn->prepare("SELECT * FROM UserInfo WHERE nickname = ?");
    $stmt->bind_param('s', $nickname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        die(json_encode(['error' => "Error executing verification query: " . $conn->error]));
    }

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $storedHwid = $user["hwid"];
        $storedPassword = $user["password"];

        if (!password_verify($passwordInput, $storedPassword)) {
            $encryptedString = encrypt("pass", $token);
            $response = ['key' => base64_encode($token), 'data' => $encryptedString];
            echo json_encode($response);
        sendWebhook("```Logged! - Invalid credits (password)```
name: " . $nickname . "
hwid: " . $hwid . "
||token: " . $token . "||");
            $conn->close();
            exit;
        }

        if (is_null($user["till"]) || $user["till"] == 0 || $user["till"] < $currentTime) {
            $encryptedString = encrypt("sub", $token);
            $response = ['key' => base64_encode($token), 'data' => $encryptedString];
        sendWebhook("```Logged! - Invalid credits (Subscribe is not active!)```
name: " . $nickname . "
hwid: " . $hwid . "
||token: " . $token . "||");
            echo json_encode($response);
            $conn->close();
            exit;
        }

        if (empty($storedHwid)) {
            $updateStmt = $conn->prepare("UPDATE UserInfo SET hwid = ? WHERE nickname = ?");
            $updateStmt->bind_param('ss', $hwid, $nickname);
            $updateStmt->execute();
            $storedHwid = $hwid;
            $encryptedString = encrypt("ok", $token);
            $response = ['key' => base64_encode($token), 'data' => $encryptedString];
            sendWebhook("```Logged! - " . $nickname . "```
" . encrypt($message, $token) . "
||token: " . $token . "||");
            echo json_encode($response);
        } elseif ($storedHwid !== $hwid) {
            $encryptedString = encrypt("fuck", $token);
            $response = ['key' => base64_encode($token), 'data' => $encryptedString];
        sendWebhook("```Logged! - Invalid credits (hwid is not simular!)```
name: " . $nickname . "
hwid: " . $hwid . "
||token: " . $token . "||");
            echo json_encode($response);
        } else {
            $encryptedString = encrypt("ok", $token);
            $response = ['key' => base64_encode($token), 'data' => $encryptedString];
                    sendWebhook("```Logged! - " . $nickname . "```
" . encrypt($message, $token) . "
||token: " . $token . "||");
            echo json_encode($response);
        }
    } else {
        $encryptedString = encrypt("name", $token);
        $response = ['key' => base64_encode($token), 'data' => $encryptedString];
        sendWebhook("```Logged! - Invalid credits (name)```
name: " . $nickname . "
hwid: " . $hwid . "
||token: " . $token . "||");
        echo json_encode($response);
    }
} else {
    if (isset($token)) {
        $encryptedString = encrypt("fuck", $token);
        $response = ['key' => base64_encode($token), 'data' => $encryptedString];
                sendWebhook("```Logged! - Invalid credits (token)```
name: " . $nickname . "
hwid: " . $hwid . "
||token: " . $token . "||");
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Missing parameters']);
                sendWebhook("```Logged! - Invalid credits (parameters)```
name: " . $nickname . "
hwid: " . $hwid . "
||token: " . $token . "||");
    }
}

$conn->close();

function sendWebhook($message)
{
    $webhookUrl =
        getString("SiteSettings.discordWebhook");
    $data = [
        "content" => $message,
    ];
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    curl_close($ch);
}
?>