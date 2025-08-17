<?php

// apt install composer
// cd /var/www/html/папка/assets
// composer require phpmailer/phpmailer
// composer install

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../assets/vendor/phpmailer/src/Exception.php';
require '../../assets/vendor/phpmailer/src/PHPMailer.php';
require '../../assets/vendor/phpmailer/src/SMTP.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once("../../backend/db.php");
include_once("../../config.php");

$secret = '6LdBXbsqAAAAAGK7N84mDs1MYP0AzHPAH1LaLBKX';

// Отладочная информация
error_log("POST data: " . print_r($_POST, true));

if (isset($_POST['g-recaptcha-response'])) {
    $curl = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, 'secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
    $out = curl_exec($curl);
    curl_close($curl);
    
    $out = json_decode($out);
    if (!$out->success) {
        $("#result").html('<p style="color: red; font-weight: 550">Ошибка. Пройдите капчу!</p>');
        exit();
    }
} else {
    $("#result").html('<p style="color: red; font-weight: 550">Ошибка. Пройдите капчу!</p>');
    exit();
}

$currentUnixTime = time();
$expiryTime = $currentUnixTime - 86400;
$deleteQuery = "DELETE FROM RecoveryInfo WHERE unix < ?";
$deleteStmt = $conn->prepare($deleteQuery);
$deleteStmt->bind_param('i', $expiryTime);
$deleteStmt->execute();
$deleteStmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = $_POST["nickname"];

    try {
        $query = "SELECT email FROM UserInfo WHERE nickname = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $nickname);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($email);
        if ($stmt->num_rows > 0 && $stmt->fetch()) {
            $checkQuery = "SELECT COUNT(*) FROM RecoveryInfo WHERE username = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param('s', $nickname);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();

            if ($count > 0) {
                $response = [
                    'status' => 'error',
                    'message' => 'Запрос на восстановление уже существует.'
                ];
                echo json_encode($response);
                exit;
            }

            $mail = new PHPMailer(true);
            try {
                $mail->CharSet = "UTF-8";
                $mail->isSMTP();
                $mail->Host = getString("smtp.host");
                $mail->SMTPAuth = true;
                $mail->Username = getString("smtp.username");
                $mail->Password = getString("smtp.password");
                $mail->Port = 465;

                $mail->setFrom(getString("smtp.username"), getString("SiteSettings.name"));
                $mail->addAddress($email);

                $recoveryCode = md5($nickname);

                $mail->isHTML(true);
                $mail->Subject = 'Письмо с восстановлением пароля';
                $mail->Body    = 'Привет, ' . $nickname . ', <b>вы подавали заявку на восстановление пароля</b>
                <br>Для восстановления пароля <a href="https://' . getString("SiteSettings.domainUrl") . '/recovery?code=' . $recoveryCode . '">кликните сюда</a>. ссылка активна 24 часа.';
                $mail->AltBody = 'Привет, ' . $nickname . ', вы подавали заявку на восстановление пароля. Для восстановления пароля перейдите по следующей ссылке (она активна 24 часа): https://' . getString("SiteSettings.domainUrl") .'/recovery?code=' . $recoveryCode;

                $mail->send();
                $("#result").html('<p style="color: green; font-weight: 550">Заявка отправлена!</p>');

                $query = "INSERT INTO RecoveryInfo (username, md5name, unix) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('ssi', $nickname, $recoveryCode, $currentUnixTime);
                $stmt->execute();
                $stmt->close();
            } catch (Exception $e) {
                $("#result").html('<p style="color: red; font-weight: 550">Ошибка. ' . $e->getMessage() . '</p>');
            }
        } else {
            $("#result").html('<p style="color: red; font-weight: 550">Ошибка. Не найден ' . $nickname . '</p>');
        }

        $stmt->close();
    } catch (Exception $e) {
        $("#result").html('<p style="color: red; font-weight: 550">Ошибка. ' . $e->getMessage() . '</p>');
    }
}
?>