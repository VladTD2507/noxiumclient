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

$secret = '6LeaDzoqAAAAALYy_pz85MYO4jztAGdsOUzCemX2';
$result = array();
$login = $_POST['login'];
$password = $_POST['password'];
$email = $_POST['mail'];

if (isset($_POST['g-recaptcha-response'])) {
    $curl = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, 'secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
    $out = curl_exec($curl);
    curl_close($curl);
    
    $out = json_decode($out);
    if (!$out->success) {
        $result['status'] = false;
        $result['Message'] = 'Ошибка в проверке reCAPTCHA.';
        echo json_encode($result);
        exit();
    }
} else {
    $result['status'] = false;
    $result['Message'] = 'Пожалуйста, заполните reCAPTCHA.';
    echo json_encode($result);
    exit();
}

if (!preg_match('/^[a-zA-Z0-9_.]+$/', $login)) {
    $result['status'] = false;
    $result['Message'] = "Логин может содержать только английские буквы, цифры, символы '_' и '.'.";
    echo json_encode($result);
    exit();
}

if (strlen($login) < 3 || strlen($password) < 3) {
    $result['status'] = false;
    $result['Message'] = "Минимальная длина ника и пароля 4 символа.";
    echo json_encode($result);
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    // Получаем количество записей
    $queryGetCount = "SELECT COUNT(*) AS count FROM UserInfo";
    $stmtGetCount = $conn->query($queryGetCount);
    $row = $stmtGetCount->fetch_assoc();
    $count = $row['count'];
    $uid = $count + 1;

    // Проверка на занятость логина
    $queryCheckLogin = "SELECT id FROM UserInfo WHERE nickname = ?";
    $stmtCheckLogin = $conn->prepare($queryCheckLogin);
    $stmtCheckLogin->bind_param('s', $login);
    $stmtCheckLogin->execute();
    $stmtCheckLogin->store_result();

    if ($stmtCheckLogin->num_rows > 0) {
        $result['status'] = false;
        $result['Message'] = "Логин уже занят.";
        echo json_encode($result);
        exit();
    }
    $stmtCheckLogin->close();

    // Проверка на занятость email
    $queryCheckEmail = "SELECT id FROM UserInfo WHERE email = ?";
    $stmtCheckEmail = $conn->prepare($queryCheckEmail);
    $stmtCheckEmail->bind_param('s', $email);
    $stmtCheckEmail->execute();
    $stmtCheckEmail->store_result();

    if ($stmtCheckEmail->num_rows > 0) {
        $result['status'] = false;
        $result['Message'] = "Email уже занят.";
        echo json_encode($result);
        exit();
    }
    $stmtCheckEmail->close();

    $token = generateToken($login);

    $query = "INSERT INTO UserInfo (`uid`, `nickname`, `password`, `email`, `role`, `token`) VALUES (?, ?, ?, ?, 'DEFAULT', ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('issss', $uid, $login, $hashedPassword, $email, $token);

    if ($stmt->execute()) {
        $result['status'] = true;
        $result['Message'] = "Регистрация прошла успешно.";
        if (!isset($_SESSION['admin'])){
            $_SESSION['token'] = $token;
            $_SESSION['user_email'] = $email;
        }

    } else {
        $result['status'] = false;
        $result['Message'] = "Ошибка при выполнении запроса: " . $stmt->error;
    }
} catch (Exception $e) {
    $result['status'] = false;
    $result['Message'] = "Ошибка при выполнении запроса.";
    $result['Exception'] = $e->getMessage();
}
echo json_encode($result);
exit();
?>
