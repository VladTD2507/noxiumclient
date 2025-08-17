<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once("../../../backend/db.php");
include_once("../../../config.php");
include_once("../../../backend/session.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['token'])) {
        header("Location: auth");
        exit();
    }

    if (isset($_POST["password"])) {
        $nickname = getName();
        $new_password = $_POST["password"];
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        try {
            $query = "UPDATE UserInfo SET password = ? WHERE nickname = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $hashed_password, $nickname);
            $stmt->execute();

            $response = [
                'status' => 'ok',
                'message' => 'Пароль сменен.'
            ];
            echo json_encode($response);
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'message' => 'Ошибка сервера.'
            ];
            echo json_encode($response);
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Пароль не указан.'
        ];
        echo json_encode($response);
    }
}
?>