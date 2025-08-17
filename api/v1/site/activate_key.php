<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once("../../../backend/db.php");
include_once("../../../config.php");
include_once("../../../backend/session.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['token'])) {
        header("Location: ../../../../auth");
        exit();
    }

    if (isset($_POST["key"])) {
        $nickname = getName();
        $key = $_POST["key"];

        $query = "SELECT key_column, value_of_activate, activated, owner, plus_subday, users_of_activated FROM KeyInfo WHERE key_column = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $value_of_activate = $row['value_of_activate'];
            $activated = $row['activated'];
            $owner = $row['owner'];
            $plus_subday = $row['plus_subday'];
            $users_of_activated = $row['users_of_activated'];

            if (strpos($users_of_activated, $nickname) !== false && $value_of_activate != -1) {
                $response = [
                    'status' => 'error',
                    'message' => 'Вы уже активировали этот ключ.'
                ];
                echo json_encode($response);
                $stmt->close();
                exit();
            }

            if ($value_of_activate != -1 && $activated >= $value_of_activate) {
                $response = [
                    'status' => 'error',
                    'message' => 'Ключ уже активирован другим пользователем.'
                ];
                echo json_encode($response);
                $stmt->close();
                exit();
            }

            // Check if the nickname already exists in the users_of_activated column
            $users_of_activated_array = explode("|", $users_of_activated);
            if (!in_array($nickname, $users_of_activated_array)) {
                $users_of_activated_array[] = $nickname;
                $users_of_activated = implode("|", $users_of_activated_array);
            }

            $user_info_query = "SELECT till FROM UserInfo WHERE nickname = ?";
            $user_info_stmt = $conn->prepare($user_info_query);
            $user_info_stmt->bind_param("s", $nickname);
            $user_info_stmt->execute();
            $user_info_result = $user_info_stmt->get_result();

            if ($user_info_result->num_rows > 0) {
                $user_info_row = $user_info_result->fetch_assoc();
                $till_unix = $user_info_row['till'];

                $till_date = date("Y-m-d H:i:s", $till_unix);

                $till_date_obj = new DateTime($till_date);
                $till_date_obj->modify("+$plus_subday days");
                $new_till_date = $till_date_obj->format("Y-m-d H:i:s");

                $new_till_unix = $till_date_obj->getTimestamp();
                
                $update_user_info_query = "UPDATE UserInfo SET till = ? WHERE nickname = ?";
                $update_user_info_stmt = $conn->prepare($update_user_info_query);
                $update_user_info_stmt->bind_param("is", $new_till_unix, $nickname);
                $update_user_info_stmt->execute();
            } else {
                $new_till_date = "Неизвестно";
            }

            if ($value_of_activate == -1 || $activated < $value_of_activate) {
                $new_activated = $activated + 1;

                $update_query = "UPDATE KeyInfo SET activated = ?, users_of_activated = ? WHERE key_column = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("iss", $new_activated, $users_of_activated, $key);
                $update_stmt->execute();

                $response = [
                    'status' => 'ok',
                    'message' => 'Ключ активирован.'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Превышен лимит активаций.'
                ];
            }
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Ключ не найден.'
            ];
            echo json_encode($response);
            $stmt->close();
            $conn->close(); 
            exit();
        }

        echo json_encode($response);
        $stmt->close(); 
        $user_info_stmt->close(); 
        $update_user_info_stmt->close(); 
        $update_stmt->close(); 
        $conn->close(); 
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Ключ не найден.'
        ];
        echo json_encode($response);
        $conn ->close(); 
    }
}
?>