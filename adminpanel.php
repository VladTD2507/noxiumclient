<?php
include_once("backend/db.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once("backend/session.php");
include_once("config.php");

$sessionStatus = session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive';

function sendWebhook($message)
{
    $webhookUrl =
        getString("SiteSettings.discordWebhook");
    $data = [
        "content" => $message,
    ];
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    curl_close($ch);
}

if (!isset($_SESSION['token'])) {
    header("Location: auth");
    exit();
}

$query = "SELECT nickname, role FROM UserInfo WHERE token = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($nickname, $role);
    $stmt->fetch();

    if ($role !== "DEV+" && $role !== "ADMIN" && $role !== "DEV") {
        header("Location: cabinet");
        exit();
    }
	sendWebhook('```Вход в админ-панель: (' . date('Y-m-d H:i:s') . ')``` Username: ' . getName() . ' 
 IP: ||' . $_SERVER['REMOTE_ADDR'] . '|| User-agent: ||' . $_SERVER['HTTP_USER_AGENT'] . '||
 Session status: ||' . $sessionStatus . '||');
    $_SESSION['admin'] = true;
} else {
    header("Location: auth");
    exit();
}

$stmt->close();
$conn->close();

?>
	<!DOCTYPE html>
	<html lang="ru">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Админ панель</title>
		<link rel="stylesheet" href="assets/css/adminpanel.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	</head>
	<body>
		<div class="admin-panel">
			<h1>Админ панель</h1>
			<div class="tabs">
				<button class="tab-button active" onclick="openTab('users')">Пользователи</button>
				<button class="tab-button" onclick="openTab('other')">Другие функции</button>
			</div>
			<div id="users" class="tab-content">
				<h2>Пользователи</h2>
				<table id="usersTable">
					<thead>
						<tr>
							<th>Никнейм</th>
                            <th>Подписка</th>
                            <th>UID</th>
                            <th>Почта</th>
							<th>Роль</th>
						</tr>
					</thead>
					<tbody>
						<!-- гойда -->
					</tbody>
				</table>
			</div>
			<div id="other" class="tab-content" style="display:none;">
				<h2>Другие функции</h2>
				<button class="sub-button" onclick="openSubTab('keys')">Ключи</button>
				<button class="sub-button" onclick="openSubTab('sub-give')">Выдать подписку</button>
				<button class="sub-button" onclick="openSubTab('role-give')">Выдать роль</button>
				<button class="sub-button" onclick="openSubTab('hwid-res')">Сбросить HWID</button>
				<button class="sub-button" onclick="openSubTab('res-sub')">Сбросить подписку</button>
				<button class="sub-button" onclick="openSubTab('create-promo')">Создать промокод</button>
				<!-- ключи тут !-->
				<div id="keys" class="sub-tab-content">
					<h3>Создать ключ</h3>
					<form id="keyForm" onsubmit="generateKey(event)">
						<label for="plus_subday">Добавить дней:</label>
						<input type="number" id="plus_subday" name="plus_subday" required>
						<label for="value_of_activate">Максимальное кол-во активаций:</label>
						<input type="text" id="value_of_activate" name="value_of_activate" required placeholder="Введите -1 если бесконечно">
						<label for="delete_time">Время удаления:</label>
						<input type="datetime-local" id="delete_time" name="delete_time" required>
						<button type="submit">Создать</button>
					</form>
					<div id="keyResult"></div>
				</div>
				<div id="sub-give" class="sub-tab-content">
					<h3>Выдать подписку</h3>
					<form id="subForm" onsubmit="giveSub(event)">
						<label for="nickname_sub">Никнейм</label>
						<input type="text" id="nickname_sub" name="nickname" required>
						<label for="time">Выдать до:</label>
						<input type="datetime-local" id="time" name="time" required>
						<button type="submit">Выдать</button>
					</form>
					<div id="subGiveResult"></div>
				</div>
				<div id="hwid-res" class="sub-tab-content">
					<h3>Сбросить HWID</h3>
					<form id="hwidForm" onsubmit="hwidRes(event)">
						<label for="nickname">Никнейм</label>
						<input type="text" id="nickname" name="nickname" required>
						<button type="submit">Сбросить</button>
					</form>
					<div id="hwidResult"></div>
				</div>
				<div id="res-sub" class="sub-tab-content">
					<h3>Сбросить подписку</h3>
					<form id="resForm" onsubmit="resSub(event)">
						<label for="nickname">Никнейм</label>
						<input type="text" id="nickname" name="nickname" required>
						<button type="submit">Сбросить</button>
					</form>
					<div id="resSubResult"></div>
				</div>
				<div id="create-promo" class="sub-tab-content">
					<h3>Создать промокод</h3>
					<form id="cPromoForm" onsubmit="createPromo(event)">
						<label for="promoName">Имя промокода</label>
						<input type="text" id="promoName" name="promoName" required>
						<label for="promoAuthor">Автор промокода</label>
						<input type="text" id="promoAuthor" name="promoAuthor" required>
						<label for="promoDisc">Скидка в %</label>
						<input type="number" id="promoDisc" name="promoDisc" required>
						<label for="promoValue">Число активаций</label>
						<input type="number" id="promoValue" name="promoValue" required placeholder="-1 если бесконечно">
						<button type="submit">Создать</button>
					</form>
					<div id="CpromoResult"></div>
				</div>
				<div id="role-give" class="sub-tab-content">
					<h3>Выдать роль</h3>
					<form id="roleForm" onsubmit="roleGive(event)">
						<label for="nickname">Никнейм</label>
						<input type="text" id="nickname" name="nickname" required>
						<label for="role">Роль</label>
						<input type="text" id="role" name="role" required>
						<button type="submit">Выдать</button>
					</form>
					<div id="roleResult"></div>
				</div>
				<script src="assets/js/admin.js"></script>
	</body>

	</html>