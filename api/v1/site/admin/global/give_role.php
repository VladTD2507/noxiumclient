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

$result = ['status' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = filter_input(INPUT_POST, 'nickname', FILTER_SANITIZE_STRING);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

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
                throw new Exception('User  not found');
            }

            $query = "UPDATE UserInfo SET role = ? WHERE nickname = ?";
            $stmt = $conn->prepare($query);

            if (!$stmt) {
                throw new Exception('Error preparing query: ' . $conn->error);
            }

            $stmt->bind_param('ss', $role, $nickname);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $result['status'] = true;
                $result['message'] = 'Роль ' . $role . ' выдана для ' . $nickname;

                sendWebhook('```Успешно выдана роль для: ' . $nickname . '```');
                sendWebhook('```' . getName() . ' выдал роль: ' . $role . '```');
            } else {
                $result['status'] = true;
                $result['message'] = 'Данная роль уже есть у пользователя!';
            }

            $stmt->close();

        } catch (Exception $e) {
            $result['status'] = false;
            $result['message'] = 'Error executing query: ' . $e->getMessage();
        }
} else {
    $result['message'] = 'Invalid request method';
}

echo json_encode($result);
exit(); 
?>