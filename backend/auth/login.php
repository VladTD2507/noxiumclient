<?php
include_once("../db.php");
include_once("../../config.php");

function generateToken($login) {
    $randomString = bin2hex(random_bytes(16)); 
    $key = $login . (0x15 ** 2) / 2 . $randomString; 
    return hash('sha256', $key);
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_POST['login']) || !isset($_POST['password'])) {
    $result['status'] = false;
    $result['Message'] = "Invalid input data.";
    echo json_encode($result);
    exit();
}

$login = $_POST['login'];
$password = $_POST['password'];

if (strpos($login, "@")) {
    $result['status'] = false;
    $result['Message'] = "Вы указываете почту, а нужно никнейм!";
    echo json_encode($result);
    exit();  
}

try {

    $query = "SELECT token, password, email FROM UserInfo WHERE nickname = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($token, $storedPassword, $email);
        $stmt->fetch();

        if (password_verify($password, $storedPassword)) {
            $token = generateToken($login);
            
            $tokenQuery = "UPDATE UserInfo SET token = ? WHERE nickname = ?";
            $tokenStmt = $conn->prepare($tokenQuery);
            $tokenStmt->bind_param('ss', $token, $login);
            $tokenStmt->execute();
            $tokenStmt->close();

            $result['status'] = true;
            $result['Message'] = "Вход выполнен успешно.";
            $_SESSION['token'] = $token;
            $_SESSION['user_email'] = $email;
        } else {
            $result['status'] = false;
            $result['Message'] = "Неверный логин или пароль.";
        }
    } else {
        $result['status'] = false;
        $result['Message'] = "Пользователь с таким логином не найден.";
    }

    $stmt->close();
} catch (Exception $e) {
    $result['status'] = false;
    $result['Message'] = "Ошибка при выполнении запроса.";
    $result['Exception'] = $e->getMessage();
}

echo json_encode($result);
exit();