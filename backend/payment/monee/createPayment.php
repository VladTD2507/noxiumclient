<?php
session_start();
include_once("../../../config.php");
include_once("../../db.php");
include_once("../../session.php");

header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['token'])) {
    echo json_encode(['error' => true, 'message' => 'Не авторизован']);
    exit();
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => true, 'message' => 'Неверный метод запроса']);
    exit();
}

// Получаем данные из POST
$productId = $_POST['id'] ?? null;
$promoCode = trim($_POST['promo'] ?? '');
$token = $_POST['token'] ?? $_SESSION['token'] ?? null;

if (!$productId || !$token) {
    echo json_encode(['error' => true, 'message' => 'Отсутствуют обязательные параметры']);
    exit();
}

// Получаем данные товара
$stmt = $conn->prepare("SELECT productName, productPrice FROM Market WHERE productId = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$productResult = $stmt->get_result();
$product = $productResult->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode(['error' => true, 'message' => 'Товар не найден']);
    exit();
}

$price = (float)$product['productPrice'];
if ($price <= 0) {
    echo json_encode(['error' => true, 'message' => 'Некорректная цена товара']);
    exit();
}

// Получаем данные пользователя
$stmt = $conn->prepare("SELECT nickname FROM UserInfo WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$stmt->close();

$nickname = $user['nickname'] ?? 'неизвестный_никнейм';

// Инициализируем скидку
$discountPercent = 0;

// Обработка промокода, если указан
if ($promoCode !== '') {
    $stmt = $conn->prepare("SELECT promoPercent, promoValueOfActivated, promoMaxValueToActivate FROM Promocodes WHERE promoName = ?");
    $stmt->bind_param("s", $promoCode);
    $stmt->execute();
    $promoResult = $stmt->get_result();
    $promo = $promoResult->fetch_assoc();
    $stmt->close();

    if (!$promo) {
        echo json_encode(['error' => true, 'message' => 'Промокод не найден']);
        exit();
    }

    $activatedCount = (int)$promo['promoValueOfActivated'];
    $maxActivations = (int)$promo['promoMaxValueToActivate'];

    if ($maxActivations !== -1 && $activatedCount >= $maxActivations) {
        echo json_encode(['error' => true, 'message' => 'Промокод достиг максимума активаций']);
        exit();
    }

    $discountPercent = max(0, min(100, (float)$promo['promoPercent']));
    $discountAmount = $price * ($discountPercent / 100);
    $price = round($price - $discountAmount, 2);
    if ($price < 0) $price = 0;
}

$customFields = [
    'id' => $productId,
    'token' => $token,
    'nickname' => $nickname,
];

if ($promoCode !== '') {
    $customFields['promo'] = $promoCode;
}

$customFieldsStr = http_build_query($customFields, '', '|');

$paymentData = [
    'shop_to'      => getString("MarketSettings.moneeToken"),
    'sum'          => $price,
    'comment'      => 'Покупка товара: ' . $product['productName'] . ', цена: ' . $price . 'рублей для: ' . $nickname .
                      ($promoCode !== '' ? ", промокод: {$promoCode} (скидка {$discountPercent}%)" : ''),
    'expire'       => 1500,
    'success_url'  => 'https://' . getString("SiteSettings.domainUrl") . '/success',
    'subtract'     => 0,
    'custom_fields'=> $customFieldsStr,
];

$ch = curl_init('https://api.monee.pro/payment/create');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($paymentData),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode(['error' => true, 'message' => 'Ошибка CURL: ' . $curlError]);
    exit();
}

$responseData = json_decode($response, true);

if (isset($responseData['url'])) {
    echo json_encode(['redirectUrl' => $responseData['url']]);
    exit();
} else {
    echo json_encode(['error' => true, 'message' => 'Ошибка при создании платежа', 'response' => $responseData]);
    exit();
}
