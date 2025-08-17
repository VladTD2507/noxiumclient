<?php
include_once("../../../../../config.php"); 
include_once("../../../../../backend/db.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin'])) {
    $result = array(
        'status' => false,
        'message' => 'Вы не админ.'
    );
    echo json_encode($result);
    exit();
}

include_once("../../../../../backend/session.php");
include_once("../../../../../backend/utils/webhook_utils.php");

$result = ['status' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $promoName = filter_input(INPUT_POST, 'promoName', FILTER_SANITIZE_STRING);
    $promoAuthor = filter_input(INPUT_POST, 'promoAuthor', FILTER_SANITIZE_STRING);
    $promoDisc = filter_input(INPUT_POST, 'promoDisc', FILTER_SANITIZE_STRING);
    $promoValue = filter_input(INPUT_POST, 'promoValue', FILTER_SANITIZE_STRING);

    try {
        // Get the next promoId
        $maxIdQuery = "SELECT MAX(promoId) AS maxId FROM Promocodes";
        $maxIdResult = $conn->query($maxIdQuery);
        $maxIdRow = $maxIdResult->fetch_assoc();
        $promoId = $maxIdRow['maxId'] + 1; // Increment the maxId for the new promoId

        $insertQuery = "INSERT INTO Promocodes (promoId, promoName, promoPercent, promoIncome, promoAuthor, promoValueOfActivated, promoMaxValueToActivate, promoWhoActivated) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);

        if (!$insertStmt) {
            throw new Exception('Error preparing insert query: ' . $conn->error);
        }

        $promoIncome = 0.0; 
        $promoValueOfActivated = 0; 
        $promoMaxValueToActivate = 0; 
        $promoWhoActivated = ''; 

        $insertStmt->bind_param('isssssss', $promoId, $promoName, $promoDisc, $promoIncome, $promoAuthor, $promoValueOfActivated, $promoValue, $promoWhoActivated);
        $insertStmt->execute();

        if ($insertStmt->affected_rows > 0) {
            $result['status'] = true;
            $result['message'] = 'Сгенерирован промокод ' . $promoName . ' (' . $promoDisc . '%)';

            sendWebhook('```Успешно создан промокод: ' . $promoName . '```');
            sendWebhook('```' . getName() . ' создал промокод: ' . $promoDisc . '%, Автор промокода: ' . $promoAuthor . '```');
        } else {
            $result['status'] = false;
            $result['message'] = 'Не удалось создать промокод.';
        }

        $insertStmt->close();

    } catch (Exception $e) {
        $result['status'] = false;
        $result['message'] = 'Error executing query: ' . $e->getMessage();
    }
} else {
    $result['message'] = 'Invalid request method';
}

echo json_encode($result);
exit(); 
?>