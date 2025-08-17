<?php
include_once "../../utils/webhook_utils.php";
include_once "../../db.php";

$shop_id = getString("MarketSettings.moneeToken");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Use POST method');
}

$content = json_decode(trim(file_get_contents("php://input")), true);
if (!is_array($content)) {
    sendWebhook('Ошибка: некорректный JSON');
    http_response_code(400);
    die('Incorrect JSON');
}

// Проверка IP
$validIps = json_decode(file_get_contents('https://api.monee.pro/ips'), true)['list'] ?? [];
if (!in_array($_SERVER['REMOTE_ADDR'], $validIps) && $_SERVER['REMOTE_ADDR'] !== '51.75.56.33') {
    sendWebhook('Ошибка: неизвестный IP ' . $_SERVER['REMOTE_ADDR']);
    http_response_code(403);
    die('Unknown IP');
}

// Проверка статуса платежа и обязательных полей
if (($content['status'] ?? '') !== 'success'
    || empty($content['invoice_id'])
    || empty($content['shop_id'])
    || empty($content['amount'])
) {
    http_response_code(400);
    die('Invalid payment data');
}

if ($content['shop_id'] !== $shop_id) {
    http_response_code(403);
    die('Invalid shop_id');
}

if (empty($content['custom_fields'])) {
    http_response_code(400);
    die('No custom fields');
}

// Разбор custom_fields
$custom_data = [];
foreach (explode('|', $content['custom_fields']) as $field) {
    [$key, $value] = array_pad(explode('=', $field, 2), 2, '');
    $custom_data[$key] = $value;
}

if (empty($custom_data['id']) || empty($custom_data['nickname'])) {
    http_response_code(400);
    die('Missing required custom fields');
}

$nickname = $custom_data['nickname'];
$productId = (int)$custom_data['id'];
$promoCode = $custom_data['promo'] ?? null;

// Проверяем промокод, если есть
if ($promoCode) {
    $stmt = $conn->prepare("SELECT promoValueOfActivated, promoMaxValueToActivate, promoWhoActivated, promoAuthor FROM Promocodes WHERE promoName = ?");
    $stmt->bind_param("s", $promoCode);
    $stmt->execute();
    $promo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$promo) {
        sendWebhook("Промокод '{$promoCode}' не найден");
        http_response_code(400);
        die('Invalid promo code');
    }

    $activatedCount = (int)$promo['promoValueOfActivated'];
    $maxActivations = (int)$promo['promoMaxValueToActivate'];
    $promoAuthor = $promo['promoAuthor'] ?? 'неизвестен';

    if ($maxActivations !== -1 && $activatedCount >= $maxActivations) {
        sendWebhook("Промокод '{$promoCode}' достиг максимума активаций");
        http_response_code(400);
        die('Promo code max activations reached');
    }

    // Обновляем активации и список активировавших
    $activatedCount++;
    $whoActivated = array_filter(array_map('trim', explode(',', $promo['promoWhoActivated'])));
    if (!in_array($nickname, $whoActivated, true)) {
        $whoActivated[] = $nickname;
    }
    $newWhoActivated = implode(',', $whoActivated);

    $stmt = $conn->prepare("UPDATE Promocodes SET promoValueOfActivated = ?, promoWhoActivated = ? WHERE promoName = ?");
    $stmt->bind_param("iss", $activatedCount, $newWhoActivated, $promoCode);
    $stmt->execute();
    $stmt->close();
}

// Получаем товар
$stmt = $conn->prepare("SELECT productName, commandForGive FROM Market WHERE productId = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    http_response_code(404);
    die('Product not found');
}

$command = str_replace('-nick', '"' . $nickname . '"', $product['commandForGive']);
$stmt = $conn->prepare($command);
$stmt->execute();
$stmt->close();

sendWebhook("Обработан платеж: ```" . json_encode($content, JSON_PRETTY_PRINT) . "```");
sendWebhook("```Выдан продукт '{$product['productName']}' пользователю '{$nickname}', заказ №{$content['invoice_id']}```");
if ($promoCode) {
sendWebhook("```Промокод '{$promoCode}' активирован пользователем '{$nickname}'. Активаций: {$activatedCount}. Активировали: {$newWhoActivated}. Владелец промокода: {$promoAuthor}```");
}

http_response_code(200);
die('OK');
