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

$result = ['status' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    try {
        $query = "SELECT nickname, till, uid, email, role FROM UserInfo";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception('Ошибка подготовки запроса: ' . $conn->error);
        }

        $stmt->execute();
        $stmt->bind_result($nickname, $till, $uid, $email, $role);

        $users = [];
        while ($stmt->fetch()) {
            $users[] = [
                'nickname' => $nickname,
                'till' => $till,
                'uid' => $uid,
                'email' => $email,
                'role' => $role
            ];
        }
        $stmt->close();

        if (!empty($users)) {
            $result['status'] = true;
            $result['users'] = $users;
        } else {
            $result['status'] = false;
            $result['message'] = 'Пользователи не найдены.';
        }

    } catch (Exception $e) {
        $result['status'] = false;
        $result['message'] = 'Ошибка выполнения запроса: ' . $e->getMessage();
    }
} else {
    $result['message'] = 'Неверный метод запроса';
}

echo json_encode($result);
exit(); 
?>