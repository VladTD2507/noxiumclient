<?php 

include_once("../../../backend/db.php");

function getAvatarBase64($url) {
    $avatarData = file_get_contents($url);
    if ($avatarData === false) {
        return json_encode(['error' => "Ошибка при получении аватара."]);
    }
    return base64_encode($avatarData);
}

$login = $_GET['login'] ?? null;
$password = $_GET['password'] ?? null;

if (!$login || !$password) {
    echo "Логин и пароль не заданы.";
    exit;
}

if (strlen($login) < 3 || strlen($password) < 3) {
    echo "Логин и пароль должны быть больше 3 символов.";
    exit;
}

$defaultAvatarUrl = 'https://nightwareclient.ru/assets/images/avatar.png';

if ($login === 'dev' && $password === 'dev') {
    echo getAvatarBase64($defaultAvatarUrl);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM UserInfo WHERE nickname = ?");
$stmt->bind_param('s', $login);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    echo json_encode(['error' => "Ошибка выполнения запроса: " . $conn->error]);
    exit;
}

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $avatarPath = $user['avatar'];

    if (empty($avatarPath)) {
        echo getAvatarBase64($defaultAvatarUrl);
    } else {
        $avatarData = file_get_contents('../../../uploads/' . $avatarPath);
        if ($avatarData === false) {
            echo json_encode(['error' => "Ошибка при получении аватара."]);
        } else {
            echo base64_encode($avatarData);
        }
    }
} else {
    echo 'eblan'; 
}

?>