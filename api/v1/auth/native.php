<?php

include_once("../../../backend/db.php");
include_once("../../../backend/utils/webhook_utils.php");

$currentTime = time();
$userAgent = "InternalAntiLeak111";

if (!isset($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] !== $userAgent) {
    echo json_encode(['error' => 'Invalid user agent']);
    exit;
}

function encrypt($data, $key) {
    $keyLength = strlen($key);
    $output = '';

    for ($i = 0; $i < strlen($data); $i++) {
        $keyChar = $key[$i % $keyLength];
        $dataChar = $data[$i];
        
        $shift = (ord($keyChar) + $i) % 2; 

        $encryptedChar = chr((ord($dataChar) + $shift * 2) % 256); 
        $output .= $encryptedChar;
    }

    return $output;
}

function generateToken($login) {
    $randomString = bin2hex(random_bytes(16)); 
    $key = $login . (0x15 ** 2) / 2 . $randomString; 
    return hash('sha256', $key);
}

function decryptU($encryptedData, $key) {
    $keyLength = strlen($key);
    $dataLength = strlen($encryptedData);
    $result = '';

    for ($i = 0; $i < $dataLength; $i++) {
        $result .= $encryptedData[$i] ^ $key[$i % $keyLength];
    }

    return $result;
}

$nickname = isset($_GET['login']) ? $_GET['login'] : null;
$passwordInput = isset($_GET['pass']) ? $_GET['pass'] : null;
$hwid = isset($_GET['hwid']) ? $_GET['hwid'] : null;
$token = isset($_GET['token']) ? $_GET['token'] : null;
$u = isset($_GET['u']) ? $_GET['u'] : null;

if ($nickname && $passwordInput && $hwid && $token && strlen($token) == 16) {
    $stmt = $conn->prepare("SELECT * FROM UserInfo WHERE nickname = ?");
    $stmt->bind_param('s', $nickname);
    $stmt->execute();
    $result = $stmt->get_result();
	
	$decU = decryptU($u, $token);

    if ($result === false) {
        die(json_encode(['error' => "Error executing verification query: " . $conn->error]));
    }

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $storedHwid = $user["hwid"];
        $storedPassword = $user["password"];

        if (!password_verify($passwordInput, $storedPassword)) {
            $encryptedString = encrypt("pass", $token);
            echo json_encode(['key' => $token, 'data' => $encryptedString]);
            $conn->close();
            exit;
        }

        if (is_null($user["till"]) || $user["till"] == 0 || $user["till"] < $currentTime) {
            $encryptedString = encrypt("sub", $token);
            echo json_encode(['key' => $token, 'data' => $encryptedString]);
            $conn->close();
            exit;
        }

        $generatedToken = generateToken($nickname);

        if (empty($storedHwid)) {
            $updateStmt = $conn->prepare("UPDATE UserInfo SET hwid = ? WHERE nickname = ?");
            $updateStmt->bind_param('ss', $hwid, $nickname);
            $updateStmt->execute();
            $storedHwid = $hwid;
            $combinedString = implode('|', ['InternalGuard', $user["nickname"], $user["uid"], $storedHwid, $user["till"], $user["role"]]);
            $encryptedString = encrypt($combinedString, $token);
            $response = ['key' => $token, 'data' => $encryptedString];
            echo json_encode($response);
        } elseif ($storedHwid !== $hwid) {
            $encryptedString = encrypt("fuck", $token);
            $response = ['key' => $token, 'data' => $encryptedString];
            echo json_encode($response);
        } else {
            $combinedString = implode('|', ['InternalGuard', $user["nickname"], $user["uid"], $storedHwid, $user["till"], $user["role"]]);
            $encryptedString = encrypt($combinedString, $token);
            $response = ['key' => $token, 'data' => $encryptedString];
			sendWebhook("```Connected to native side auth: " . $user["nickname"] . " [UID: " . $user["uid"] . "] -> " . $user["role"] . " | IP: " . $_SERVER['REMOTE_ADDR'] . "```");
            echo json_encode($response);
        }
    } else {
        $encryptedString = encrypt("name", $token);
        echo json_encode(['key' => $token, 'data' => $encryptedString]);
    }
} else {
    if (isset($token)) {
        $encryptedString = encrypt("fuck", $token);
        echo json_encode(['key' => $token, 'data' => $encryptedString]);
    } else {
        echo json_encode(['error' => 'Missing parameters']);
    }
}

$conn->close();
?>