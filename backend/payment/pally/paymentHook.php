<?php
include_once("../../db.php");
include_once("../../../config.php");
include_once("../../utils/webhook_utils.php");

$postData = $_POST;

$requiredFields = ['Status', 'InvId', 'OutSum', 'CurrencyIn', 'SignatureValue'];
sendWebhook($postData);
foreach ($requiredFields as $field) {
    if (!isset($postData[$field])) {
        sendWebhook('```System error exception. (order is invalid ' . $postData['order_id'] . ') - Pally payment```');
        die("Обнаружено нарушение системы безопасности.");
    }
}

$status = $postData['Status'];
$invId = $postData['InvId'];
$outSum = $postData['OutSum'];
$currencyIn = $postData['CurrencyIn'];
$signatureValue = $postData['SignatureValue'];
$customData = json_decode($postData['custom'], true);

$productId = $customData['productId'];
$amountData = $customData['amount'];
$promoCodeData = $customData['promocode'];
$userNameData = $customData['username'];

//if ($outSum != $amountData) {
//    sendWebhook('```System error exception. (order is invalid (price changed) ' . $postData['InvId'] . ' nickname - '. $userNameData . ') - Pally payment```');
//    die("Ошибка: неверная сумма (Изменение).");
//}

switch ($status) {
    case 'SUCCESS':
        echo "Платеж успешен. ID заказа: $orderId, Сумма: $outSum $currencyIn";
        sendWebhook("```Успешно оплачен (Pally). Заказ: " . $order_id. "```");

        $nick = $customData['username'];
        $productId = $customData['productId'];
        $amount = $outSum; 

        $query = "SELECT productPrice, productName, commandForGive FROM Market WHERE productId = ?";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            sendWebhook('```Error preparing statement: ' . $conn->error . '```');
            die();
        }
        $stmt->bind_param("s", $productId);
        $stmt->execute();
        $stmt->bind_result($productPrice, $productName, $commandForGive);
        if (!$stmt->fetch()) {
            sendWebhook('```Product not found for productId: ' . $productId . '```');
            sendWebhook('``` ' . $customData . '```');
            die();
        }
        $stmt->close();

        $discountedPrice = $productPrice;
        $promoDetails = 'Промокод не указан';

        if (!empty($promoCodeData)) {
            $query = "SELECT promoIncome, promoAuthor, promoPercent FROM Promocodes WHERE promoName = ?";
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                sendWebhook('```Error preparing statement: ' . $conn->error . '```');
                die();
            }
            $stmt->bind_param("s", $promoCodeData);
            $stmt->execute();
            $stmt->bind_result($promoIncome, $promoAuthor, $promoPercent);
            if ($stmt->fetch()) {
                $discountedPrice = $productPrice * (1 - $promoPercent / 100);
                $promoDetails = 'активирован промокод: ' . $promocode . ', пользователем: ' . $nick . ', использована скидка в: ' . $promoPercent . '%' . ', автор промокода: ' . $promoAuthor . ' получил всего: ' . $promoIncome . ' рублей';
            } else {
                sendWebhook('```Промо не найден: ' . $promoCodeData . '```');
                die();
            }
            $stmt->close();
        }

        if (abs($amount - $discountedPrice) < 0.01) {
            if (isset($commandForGive)) {
                if (strpos($commandForGive, '-nick') !== false) {
                    $commandForGive = str_replace('-nick', '\'' . $nick . '\'', $commandForGive);
                }

                if ($conn->query($commandForGive) === TRUE) {
                    sendWebhook('```Покупка ' . $productName . ': ' . $nick . ', Product ID: ' . $productId . ', Сумма: ' . $amount . ', ' . $promoDetails . ', Запрос выполнен: ' . $commandForGive . ' ```');
                } else {
                    sendWebhook('```Error executing command: ' . $conn->error . '```');
                }
            }
        } else {
            sendWebhook('```Возможно подмена цены: ' . $nick . ', Product ID: ' . $productId . ', Ожидаемая: ' . $discountedPrice . ', Полученная: ' . $amount . '```');
            die();
        }
        break;

    case 'FAIL':
        $errorMessage = isset($postData['error_message']) ? $postData['error_message'] : 'Нет описания ошибки';
        sendWebhook('```Error oreder. ' . $orderId . ' Сообщение: ' . $errorMessage . '```');
        echo "Платеж не успешен. ID заказа: $orderId, Сообщение: $errorMessage";
        break;

    default:
        echo "Неизвестный статус платежа: $status";
        break;
}
?>