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
    $nickname = filter_input(INPUT_POST, 'nickname', FILTER_SANITIZE_STRING);
    $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);

    if (!strtotime($time)) {
        $result['message'] = 'Invalid time';
    } else {
        try {
            $formatted_delete_time = date('Y-m-d H:i:s', strtotime($time));
            $timestamp = date('Y-m-d H:i:s');
            $formattedDateUnix = strtotime($formatted_delete_time);

            $query = "UPDATE UserInfo SET till = ? WHERE nickname = ?";
            $stmt = $conn->prepare($query);

            if (!$stmt) {
                throw new Exception('Error preparing query: ' . $conn->error);
            }

            $stmt->bind_param('is', $formattedDateUnix, $nickname);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $result['status'] = true;
                $result['message'] = 'Подписка для ' . $nickname . ' выдана на ' . $formatted_delete_time;

                sendWebhook('```Успешно выдана сабка для: ' . $nickname . '```');
                sendWebhook('```' . getName() . ' выдал сабку до: ' . $formatted_delete_time . '```');
            } else {
                $result['status'] = true;
                $result['message'] = 'Данная подписка уже есть у пользователя!';
            }

            $stmt->close();

        } catch (Exception $e) {
            $result['status'] = false;
            $result['message'] = 'Error executing query: ' . $e->getMessage();
        }
    }
} else {
    $result['message'] = 'Invalid request method';
}

echo json_encode($result);
exit(); 
?>