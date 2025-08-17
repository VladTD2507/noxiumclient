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
    $resultCheck = $conn->query($sqlProverka);
    if ($resultCheck->num_rows > 0) {
        $sqlUpdate = $conn->prepare("UPDATE KeyStorage SET key_value = ?, timestamp = ? WHERE id = 1");
        $sqlUpdate->bind_param('si', $key, $timestamp);
        if ($sqlUpdate->execute() === false) {
            die("Error updating key in storage: " . $conn->error);
        }
    } else {
        $sqlInsert = $conn->prepare("INSERT INTO KeyStorage (id, key_value, timestamp) VALUES (1, ?, ?)");
        $sqlInsert->bind_param('si', $key, $timestamp);
        if ($sqlInsert->execute() === false) {
            die("Error saving key to storage: " . $conn->error);
        }
    }
}

function getKeyFromStorage($conn) {
    $sql = "SELECT key_value, timestamp FROM KeyStorage WHERE id = 1";
    $result = $conn->query($sql);
    if ($result === false || $result->num_rows === 0) {
        $key = generateKey();
        saveKeyToStorage($conn, $key);
        return $key;
    } else {
        $row = $result->fetch_assoc();
        $timestamp = $row['timestamp'];
        $currentTimestamp = time();
        $expirationTime = 1;
        if ($currentTimestamp - $timestamp) {
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
    $passReponse = $_GET['pass'];
    $hwidResponse = $_GET['hwid'];

    $sqlVerify = $conn->prepare("SELECT * FROM UserInfo WHERE nickname = ?");
    $sqlVerify->bind_param('s', $keyResponse);
    $sqlVerify->execute();
    $verifyresultat = $sqlVerify->get_result();

    if ($verifyresultat === false) {
        die("Error executing verification query: " . $conn->error);
    }

    if ($verifyresultat->num_rows > 0) {
        $row = $verifyresultat->fetch_assoc();
        $storedHwid = $row["hwid"];
        $storedPass = $row["password"];

        if (!password_verify($passReponse, $storedPass)) {
            $key = getKeyFromStorage($conn);
            $encryptedString = encrypt("pass", $key);
            $response = ['key' => $key, 'data' => $encryptedString];
            echo json_encode($response);
            $conn->close();
            exit;
        }

        if ($row["till"] === null || $row["till"] == 0) {
            $key = getKeyFromStorage($conn);
            $encryptedString = encrypt("sub", $key);
            $response = ['key' => $key, 'data' => $encryptedString];
            echo json_encode($response);
            $conn->close();
            exit;
        }

        $avatarBase64 = '';
        $avatarPath = '../' . $row["avatar"];
        if ($avatarPath && file_exists($avatarPath)) {
            $avatarBase64 = base64_encode(file_get_contents($avatarPath));
        }

        if (empty($storedHwid)) {
            $updateHwidSql = $conn->prepare("UPDATE UserInfo SET hwid = ? WHERE nickname = ?");
            $updateHwidSql->bind_param('ss', $hwidResponse, $keyResponse);
            $updateHwidSql->execute();
            $storedHwid = $hwidResponse;
            $combinedString = $row["nickname"] . ':' . $row["uid"] . ':' . $storedHwid . ':' . $row["till"] . ':' . $row["role"];
             $key = getKeyFromStorage($conn);
            $encryptedString = encrypt($combinedString, $key);
            $response = ['key' => $key, 'data' => $encryptedString];
            echo json_encode($response);
        } else if ($storedHwid != $hwidResponse) {
            $key = getKeyFromStorage($conn);
            $encryptedString = encrypt("hwid", $key);
            $response = ['key' => $key, 'data' => $encryptedString];
            echo json_encode($response);
        } else {
            $combinedString = $row["nickname"] . ':' . $row["uid"] . ':' . $storedHwid . ':' . $row["till"] . ':' . $row["role"];
            $key = getKeyFromStorage($conn);
            $encryptedString = encrypt($combinedString, $key);
            $response = ['key' => $key, 'data' => $encryptedString];
            echo json_encode($response);
        }
    } else {
        $key = getKeyFromStorage($conn);
        $encryptedString = encrypt("name", $key);
        $response = ['key' => $key, 'data' => $encryptedString];
        echo json_encode($response);
    }
} else {
    $key = getKeyFromStorage($conn);
    $encryptedString = encrypt("fuck", $key);
    $response = ['key' => $key, 'data' => $encryptedString];
    echo json_encode($response);
}

$conn->close();
?>