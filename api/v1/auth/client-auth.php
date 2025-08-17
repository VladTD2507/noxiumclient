<?php

include_once("../../../backend/db.php");

$currentTime = time();

function encrypt($data, $key) {
    $keyLength = strlen($key);
    $output = '';

    for ($i = 0; $i < strlen($data); $i++) {
        $keyChar = $key[$i % $keyLength];
        $dataChar = $data[$i];
        
        $shift = (ord($keyChar) + $i) % 6; 

        $encryptedChar = chr((ord($dataChar) + $shift * 2) % 256); 
        $output .= $encryptedChar;
    }

    return $output;
}

function encodeResponse($data) {
    $output = '';

    for ($i = 0; $i < strlen($data); $i++) {
        $dataChar = $data[$i];
        
        $encryptedChar = chr(ord($dataChar) ^ 0x2);
        $output .= $encryptedChar;
    }

    return $output;
}

function encryptKey($data) {
    $key = $data . "eax";
    $keyLength = strlen($key);
    $output = '';

    for ($i = 0; $i < strlen($data); $i++) {
        $keyChar = $key[$i % $keyLength];
        $dataChar = $data[$i];
        
        $shift = (ord($keyChar) + $i) % 2; 

        $encryptedChar = chr((ord($dataChar) + $shift * 2) % 384); 
        $output .= $encryptedChar;
    }

    return $output;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['login'], $data['pass'], $data['hwid'], $data['token']) && strlen($data['token']) == 16) {
    $nickname = $data['login'];
    $passwordInput = $data['pass'];
    $hwid = $data['hwid'];
    $token = $data['token'];

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
            $response = ['key' => encryptKey(base64_encode($token)), 'data' => $encryptedString];
            echo encodeResponse(json_encode($response));
            $conn->close();
            exit;
        }

        if (is_null($user["till"]) || $user["till"] == 0 || $user["till"] < $currentTime) {
            $encryptedString = encrypt("sub", $token);
            $response = ['key' => encryptKey(base64_encode($token)), 'data' => $encryptedString, 'token' => ""];
            echo encodeResponse(json_encode($response));
            $conn->close();
            exit;
        }

        if (empty($storedHwid)) {
            $updateStmt = $conn->prepare("UPDATE UserInfo SET hwid = ? WHERE nickname = ?");
            $updateStmt->bind_param('ss', $hwid, $nickname);
            $updateStmt->execute();
            $storedHwid = $hwid;
            $combinedString = implode(':', ['InternalGuard', $user["nickname"], $user["uid"], $storedHwid, $user["till"], $user["role"]]);
            $encryptedString = encrypt($combinedString, $token);
            $response = ['key' => encryptKey(base64_encode($token)), 'data' => $encryptedString, 'token' => ""];
            echo encodeResponse(json_encode($response));
        } elseif ($storedHwid !== $hwid) {
            $encryptedString = encrypt("fuck", $token);
            $response = ['key' => encryptKey(base64_encode($token)), 'data' => $encryptedString];
            echo encodeResponse(json_encode($response));
        } else {
            $combinedString = implode(':', ['InternalGuard', $user["nickname"], $user["uid"], $storedHwid, $user["till"], $user["role"]]);
            $encryptedString = encrypt($combinedString, $token);
            $response = ['key' => encryptKey(base64_encode($token)), 'data' => $encryptedString, 'token' => ""];
            echo encodeResponse(json_encode($response));
        }
    } else {
        $encryptedString = encrypt("name", $token);
        $response = ['key' => encryptKey(base64_encode($token)), 'data' => $encryptedString, 'token' => ""];
        echo encodeResponse(json_encode($response));
    }
} else {
    if (isset($data['token'])) {
        $encryptedString = encrypt("fuck", $data['token']);
        $response = ['key' => encryptKey(base64_encode($data['token'])), 'data' => $encryptedString, 'token' => ""];
        echo encodeResponse(json_encode($response));
    } else {
        echo json_encode(['error' => 'Missing parameters']);
    }
}

$conn->close();
?>
