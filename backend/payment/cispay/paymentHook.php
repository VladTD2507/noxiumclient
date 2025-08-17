<?php

include_once "../../utils/webhook_utils.php";
include_once "../../db.php";

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Invalid JSON input");
}

if (isset($data["status"]) && $data["status"] === "success") {

    $shop_id = $data["shop_id"];
    $invoice_id = $data["invoice_id"];

    $postData = json_encode([
        "shop_uuid" => $shop_id,
        "order_uuid" => $invoice_id,
    ]);

    $ch = curl_init("https://api.cispay.pro/payment/info");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $response = curl_exec($ch);
    if ($response === false) {
        die("Error during request: " . curl_error($ch));
    }

    $responseData = json_decode($response, true);

    sendWebhook('```Response: ' . $response . '```');

    curl_close($ch);

    if ($responseData["status"] === "success") {
        $customFields = $responseData['custom_fields'];
        $parts = explode(';', $customFields);
        $nick = trim($parts[0]);
        $productId = trim($parts[1]);
        $amount = $responseData['amount'];

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
            die();
        }
        $stmt->close();

        $promocode = isset($parts[2]) ? trim($parts[2]) : null;
        $discountedPrice = $productPrice;
        $promoDetails = 'Промокод не указан';

        if ($promocode) {
            $query = "SELECT promoIncome, promoAuthor, promoPercent FROM Promocodes WHERE promoName = ?";
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                sendWebhook('```Error preparing statement: ' . $conn->error . '```');
                die();
            }
            $stmt->bind_param("s", $promocode);
            $stmt->execute();
            $stmt->bind_result($promoIncome, $promoAuthor, $promoPercent);
            if ($stmt->fetch()) {
                $discountedPrice = $productPrice * (1 - $promoPercent / 100);

                $promoDetails = 'активирован промокод: ' . $promocode . ', пользователем: ' . $nick . ', использована скидка в: ' . $promoPercent . '%' . ', автор промокода: ' . $promoAuthor . ' получил всего: ' . $promoIncome . ' рублей';
            } else {
                sendWebhook('```Промо не найден: ' . $promocode . '```');
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
    sendWebhook('```Возможно подмена цены: ' . $nick . ', Product ID: ' . $productId . ', Expected: ' . $discountedPrice . ', Received: ' . $amount . '```');
    die();
}

    }
}
