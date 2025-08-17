<?php
include_once("../../../backend/db.php");

$apiKey = 'JNI_CREATEJAVAVM';

function generateKey() {
    $hexDigits = bin2hex(random_bytes(32));
    $randomLetters = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, mt_rand(10, 20));
    $key = $hexDigits . $randomLetters;
    $actualKey = substr($key, 0, 25);
    $base64Key = base64_encode($key);
    $hexKey = bin2hex($base64Key);
    return $key;
}

function saveKeyToStorage($conn, $key) {
    $timestamp = time();
    $sqlProverka = "SELECT id FROM KeyStorage WHERE id = 1";
    $stmt = $conn->prepare($sqlProverka);
    $stmt->execute();
    $resultCheck = $stmt->get_result();
    
    if ($resultCheck->num_rows > 0) {
        $sqlUpdate = "UPDATE KeyStorage SET key_value = ?, timestamp = ? WHERE id = 1";
        $stmt = $conn->prepare($sqlUpdate);
        $stmt->bind_param("si", $key, $timestamp);
        if (!$stmt->execute()) {
            die("Error updating key in storage: " . $stmt->error);
        }
    } else {
        $sqlInsert = "INSERT INTO KeyStorage (id, key_value, timestamp) VALUES (1, ?, ?)";
        $stmt = $conn->prepare($sqlInsert);
        $stmt->bind_param("si", $key, $timestamp);
        if (!$stmt->execute()) {
            die("Error saving key to storage: " . $stmt->error);
        }
    }
}

function getKeyFromStorage($conn) {
    $sql = "SELECT key_value, timestamp FROM KeyStorage WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $key = generateKey();
        saveKeyToStorage($conn, $key);
        return $key;
    } else {
        $row = $result->fetch_assoc();
        $timestamp = $row['timestamp'];
        $currentTimestamp = time();
        $expirationTime = 1;
        
        if ($currentTimestamp - $timestamp > $expirationTime) {
            $key = generateKey();
            saveKeyToStorage($conn, $key);
            return $key;
        } else {
            return $row['key_value'];
        }
    }
}

function encrypt($data, $key) {
    global $apiKey;
    $xorResult = '';
    $keyLength = strlen($key);
    for ($i = 0; $i < $keyLength; ++$i) {
        $xorResult .= chr(ord($key[$i]) ^ ord($apiKey[$i % strlen($apiKey)]));
    }
    $result = '';
    $dataLength = strlen($data);
    for ($i = 0; $i < $dataLength; ++$i) {
        $result .= chr(ord($data[$i]) ^ ord($xorResult[$i % $keyLength]));
    }
    return $result;
}

if (isset($_GET['login']) && isset($_GET['pass']) && isset($_GET['hwid'])) {
    $keyResponse = $_GET['login'];
    $passResponse = $_GET['pass'];
    $hwidResponse = $_GET['hwid'];
    
    $sqlVerify = "SELECT * FROM UserInfo WHERE nickname = ?";
    $stmt = $conn->prepare($sqlVerify);
    $stmt->bind_param("s", $keyResponse);
    $stmt->execute();
    $verifyResult = $stmt->get_result();
    
    if ($verifyResult === false) {
        die("Error executing verification query: " . $stmt->error);
    }

    if ($verifyResult->num_rows > 0) {
        $row = $verifyResult->fetch_assoc();
        $storedHwid = $row["hwid"];
        $storedPass = $row["password"];
        
        if (password_verify($passResponse, $storedPass)) {
            $key = getKeyFromStorage($conn);
            $combinedString = '!' . $row["ram"] . '!';
            $encryptedString = encrypt($combinedString, $key);
            $response = [
                'key' => $key,
                'data' => $encryptedString
            ];
            echo json_encode($response);
        } else {
            $response = [
                'key' => $key,
                'data' => "fuck"
            ];
            echo json_encode($response);
        }
    } else {
        $response = [
            'key' => $key,
            'data' => "fuck"
        ];
        echo json_encode($response);
    }
}

$conn->close();
?>
