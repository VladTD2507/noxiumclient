<?php
include_once("../db.php");
include_once("../../config.php");
include_once("../utils/webhook_utils.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once("../session.php");

if (!isset($_SESSION['token'])) {
    header("Location: ../../../auth");
    exit();
}

$nickname = getName();
$type = $_POST['id'];

function getProduct($conn, $productId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM Market WHERE productId = ?");
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return mysqli_fetch_assoc($result);
}

function getPromocode($conn, $promocode) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM Promocodes WHERE promoName = ?");
    mysqli_stmt_bind_param($stmt, "s", $promocode);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return mysqli_fetch_assoc($result);
}

function createPaymentCispay($conn, $product, $nickname, $promocode = null) {
    $customFields = $nickname . ';' . $product['productId'];
    $productPrice = $product['productPrice'];

    if ($product['canUsePromo'] == 0) {
        $promocode = null;
    }
    if ($productPrice < 150.00) {
        echo 'Соси уебок)';
        exit();
    }
    if ($promocode) {
        $promocodeData = getPromocode($conn, $promocode);
        if ($promocodeData) {
            if ($promocodeData['promoMaxValueToActivate'] != -1) {
                if ($promocodeData['promoValueOfActivated'] >= $promocodeData['promoMaxValueToActivate']) {
                    echo "Promocode already activated";
                    exit();
                }
            }
            $discount = $productPrice * ($promocodeData['promoPercent'] / 100);
            $productPrice -= $discount;
            $customFields = $nickname . ';' . $product['productId'] . ';' . $promocode;
        } else {
            echo "Promocode not found";
            exit();
        }
    }

    $data = [
        "shop_to" => getString("MarketSettings.cispayKey"),
        "sum" => floatval($productPrice),
        "comment" => "Покупка товара " . $product['productName'] . " для " . $nickname,
        "expire" => 1500,
        "custom_fields" => $customFields,
        "subtract" => 1
    ];

    $ch = curl_init('https://api.cispay.pro/payment/create');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$httpcode, $response];
}

function createPaymentPally($conn, $amount, $description, $type, $shop_id, $order_id, $custom, $currency_in, $product, $promocode = null) {
    if (is_string($custom)) {
        $custom = json_decode($custom, true);
    }
    $nickname = $custom['username'];
    $productId = $custom['productId'];
    if ($promocode) {
        $promocodeData = getPromocode($conn, $promocode);
        if ($promocodeData) {
            if ($promocodeData['promoMaxValueToActivate'] != -1) {
                if ($promocodeData['promoValueOfActivated'] >= $promocodeData['promoMaxValueToActivate']) {
                    echo "Promocode already activated";
                    exit();
                }
            }
            $discount = $amount * ($promocodeData['promoPercent'] / 100);
            $amount -= $discount;

            $pidorArray = [
                'username' => $nickname,
                'amount' => $amount,
                'productId' => $productId,
                'promocode' => $promocode
            ];
            $custom = json_encode($pidorArray);
        }
    } else {
            $pidorArray = [
                'username' => $nickname,
                'amount' => $amount,
                'productId' => $productId,
                'promocode' => ""
            ];
            $custom = json_encode($pidorArray);
    }

    $url = 'https://pal24.pro/api/v1/bill/create';
    $token = getString("MarketSettings.pallyToken");
    $data = [
        'amount' => $amount,
        'order_id' => $order_id,
        'description' => $description,
        'type' => $type,
        'shop_id' => $shop_id,
        'custom' => $custom,
        'currency_in' => $currency_in,
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/x-www-form-urlencoded',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Ошибка cURL: ' . curl_error($ch);
    } else {
        $responseData = json_decode($response, true);
        if ($responseData['success'] === 'true') {
            header('Location: ' . $responseData['link_page_url']); 
            exit;
        } 
    }
    curl_close($ch);
}

function handlePaymentResponseCispay($httpcode, $response) {
    if ($httpcode == 200) {
        $result = json_decode($response, true);
        if ($result['status'] === 'success' && isset($result['url'])) {
            header("Location: " . $result['url']);
            exit();
        } else {
            echo $response;
        }
    } else {
        echo 'Error: ' . $httpcode . ' - ' . $response;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productId = $_POST["selectedProductId"];
    $promocode = $_POST["promo"];
    $type = 2;

    $product = getProduct($conn, $productId);
    if ($product) {
        if ($type == 1) {
            list($httpcode, $response) = createPaymentCispay($conn, $product, $nickname, $promocode);
            handlePaymentResponseCispay($httpcode, $response);
        } else if ($type == 2) {
            $amount = $product['productPrice'];
            $customArray = [
                'username' => $nickname,
                'amount' => $amount,
                'productId' => $productId,
                'promocode' => $promocode
            ];
            createPaymentPally($conn, $amount, 'NightWare ' . $product['productName'] . ' - для ' . $nickname, 'normal', getString("MarketSettings.pallyId"), uniqid('order_'), json_encode($customArray), 'RUB', $product, $promocode); // Передаем промокод
        }
    } else {
        echo "Product not found";
    }
} else {
    echo "Invalid request method";
}
?>