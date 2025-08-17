<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оплата товара</title>
    <style>
        .personal-account-product-list { margin-bottom: 20px; }
        .personal-account-product-card { margin-bottom: 10px; }
        .payment-option { margin-bottom: 10px; }
        .payment-option input { margin-right: 10px; }
        .payment-method-form { display: none; }
        .payment-method-form.active { display: block; }
    </style>
</head>
<body>
    <form method="post" action="/backend/payment/monee/createPayment.php" id="paymentForm">
        <div class="personal-account-product">
            <div class="personal-account-product-list">
                <?php
                try {
                    $query = "SELECT productId, productName, productPrice, productOldPrice FROM Market"; 
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $stmt->store_result(); 
                    $stmt->bind_result($productId, $productName, $productPrice, $productOldPrice);

                    if ($stmt->num_rows > 0) {
                        while ($stmt->fetch()) { 
                            echo '<div class="personal-account-product-card">';
                            echo '<input class="personal-account-product-card-checkbox" name="selectedProductId" value="' . htmlspecialchars($productId) . '" id="' . htmlspecialchars($productId) . '" type="radio" required="">';
                            echo '<label class="personal-account-product-card-label" for="' . htmlspecialchars($productId) . '">';
                            echo '<strong class="personal-account-product-card-headline">' . htmlspecialchars($productName) . '</strong>';
                            echo '<span class="personal-account-product-card-price">';
                            echo '<span>' . htmlspecialchars($productPrice) . ' ₽</span>';
                            if ($productOldPrice) {
                                echo '<span class="personal-account-product-card-old-price">' . htmlspecialchars($productOldPrice) . ' ₽</span>';
                            }
                            echo '</span>';
                            echo '</label>';
                            echo '</div>';
                        }
                    } else {
                        echo "No products found in the database.";
                    }

                    $stmt->close();
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
                ?>
            </div>

            <input type="hidden" id="promo" name="promo">
            <input type="hidden" id="id" name="id">
            <input type="hidden" id="token" name="token" value="<?php echo isset($_SESSION['token']) ? htmlspecialchars($_SESSION['token']) : ''; ?>">

            <div class="payment-option" id="moneeOption">
                <input type="radio" id="monee" name="payment_system" value="monee" required onchange="updateSelectedPaymentSystem()">
                <label for="monee">Оплата через Monee (Россия, СНГ, Крипта)</label>
            </div>

            <button class="personal-account-product-payment" type="submit" id="submitBtn">Оплатить</button>
            <input id="promoCodeInput" class="personal-account-setting-field" style="width: 250px; margin-left: 10px;" type="text" placeholder="Введите промокод">
            <div style="font-size: 15px; font-weight: 600; margin-top: 10px;" id="promoResult"></div>
        </div>
    </form>

    <script>
        function updateSelectedPaymentSystem() {
            const selectedPayment = document.querySelector('input[name="payment_system"]:checked');
            if (selectedPayment) {
                document.getElementById('id').value = selectedPayment.value;
            }
        }

        document.getElementById('paymentForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const selectedProduct = document.querySelector('input[name="selectedProductId"]:checked');
            if (!selectedProduct) {
                alert('Выберите товар для оплаты!');
                return;
            }

            const paymentSystem = document.querySelector('input[name="payment_system"]:checked');
            if (!paymentSystem) {
                alert('Выберите способ оплаты!');
                return;
            }

            if (paymentSystem.value === 'monee') {
                const form = document.getElementById('paymentForm');
                const formData = new FormData(form);
                formData.set('id', selectedProduct.value); 

                const xhr = new XMLHttpRequest();
                xhr.open('POST', '/backend/payment/monee/createPayment.php', true);

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const result = JSON.parse(xhr.responseText);
                            if (result.redirectUrl) {
                                window.location.href = result.redirectUrl;
                            } else {
                                alert('Оплата успешно отправлена!');
                            }
                        } catch (e) {
                            alert('Ошибка при обработке ответа сервера.');
                        }
                    } else {
                        alert('Ошибка при отправке данных: ' + xhr.statusText);
                    }
                };

                xhr.onerror = function() {
                    alert('Произошла ошибка при отправке данных.');
                };

                xhr.send(formData);
            }
        });
    </script>
</body>
</html>
