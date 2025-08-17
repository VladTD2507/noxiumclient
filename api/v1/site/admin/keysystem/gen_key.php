<?php
include_once("../../../../../config.php"); 
include_once("../../../../../backend/db.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin'])) {
    $result = array(
        'status' => false,
        'message' => 'Вы не админ.'
    );
    echo json_encode($result);
    exit();
}


include_once("../../../../../backend/session.php");
include_once("../../../../../backend/utils/webhook_utils.php");

$result = ['status' => false, 'message' => '', 'key' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plus_subday = filter_input(INPUT_POST, 'plus_subday', FILTER_VALIDATE_INT);
    $value_of_activate = filter_input(INPUT_POST, 'value_of_activate', FILTER_VALIDATE_INT);
    $delete_time = filter_input(INPUT_POST, 'delete_time', FILTER_SANITIZE_STRING);

    if ($plus_subday === false || $plus_subday === null) {
        $result['message'] = 'Invalid number of days';
    } elseif ($value_of_activate === false || $value_of_activate === null) {
        $result['message'] = 'Invalid number of activations';
    } elseif (!strtotime($delete_time)) {
        $result['message'] = 'Invalid delete time';
    } else {
        try {
            $token = $_SESSION['token']; 
            $ownerQuery = "SELECT nickname FROM UserInfo WHERE token = ?";
            $ownerStmt = $conn->prepare($ownerQuery);

            if (!$ownerStmt) {
                throw new Exception('Error preparing query to get nickname: ' . $conn->error);
            }

            $ownerStmt->bind_param('s', $token);
            $ownerStmt->execute();
            $ownerStmt->bind_result($owner);
            $ownerStmt->fetch();
            $ownerStmt->close();

            if (empty($owner)) {
                throw new Exception('User not found');
            }

            $generated_key = generateRandomKey();

            $formatted_delete_time = date('Y-m-d H:i:s', strtotime($delete_time));
            $timestamp = date('Y-m-d H:i:s');
            $users_of_activated = ''; 

            $query = "INSERT INTO KeyInfo (owner, plus_subday, key_column, value_of_activate, created_at, delete_time, users_of_activated) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);

            if (!$stmt) {
                throw new Exception('Error preparing query: ' . $conn->error);
            }

            $stmt->bind_param('sisssss', $owner, $plus_subday, $generated_key, $value_of_activate, $timestamp, $formatted_delete_time, $users_of_activated);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $result['status'] = true;
                $result['message'] = 'Key generated successfully';
                $result['key'] = $generated_key;

                sendWebhook('```Успешно создан ключ: ' . $generated_key . '```');
                sendWebhook('```' . getName() . ' создал ключ на: +' . $plus_subday . 'дня(-ей) до: ' . $formatted_delete_time . '```');
            } else {
                throw new Exception('Failed to insert record');
            }

            $stmt->close();

        } catch (Exception $e) {
            $result['status'] = false;
            $result['message'] = 'Error executing query: ' . $e->getMessage();
        }
    }
} else {
    $result['message'] = 'Invalid request method';
}

echo json_encode($result);
exit(); 

function generateRandomKey($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomKey = getString("AdminSettings.keyPrefix");

    for ($i = 0; $i < $length; $i++) {
        $randomKey .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomKey;
}
?>