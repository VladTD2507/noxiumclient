<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once("../../../backend/db.php"); 
include_once("../../../config.php");
include_once("../../../backend/session.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['token'])) {
        header("Location: ../../../auth");
        exit();
    }

    if (isset($_POST["memory"])) {
        $nickname = getName();
        $ram = $_POST["memory"];

        $query = "UPDATE UserInfo SET ram = ? WHERE nickname = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $ram, $nickname);
        if ($stmt->execute()) {
            $response = [
                'status' => 'ok',
                'message' => 'Память установлена.'
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Память не установлена. (' . $stmt->error . ')'
            ];
        }

        $stmt->close();
        echo json_encode($response);
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Не указана память.'
        ];
        echo json_encode($response);
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Неверный метод запроса.'
    ];
    echo json_encode($response);
}
?>