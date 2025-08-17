<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once("../../config.php");
include_once("../db.php");
include_once("../session.php");

$upload_dir = '../../uploads/';
$allowed_types = ['image/jpeg', 'image/png'];
$max_size = 5242880;
$disallowed_extensions = ['php', 'exe', 'sh', 'bat', 'js', 'html', 'css', 'pl', 'py', 'rb']; 
$allowed_extensions = ['png', 'jpg', 'jpeg'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die(json_encode(['error' => 'Upload failed']));
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $file_type = $finfo->file($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        die(json_encode(['error' => 'Invalid file type']));
    }
    
    if ($file['size'] > $max_size) {
        die(json_encode(['error' => 'File too large']));
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowed_extensions)) {
        die(json_encode(['error' => 'Disallowed file extension']));
    }

    $stmt = $conn->prepare("SELECT avatar FROM UserInfo WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($current_avatar);
    $stmt->fetch();
    $stmt->close();

    if ($current_avatar) {
        $old_avatar_path = $upload_dir . $current_avatar;
        if (file_exists($old_avatar_path)) {
            unlink($old_avatar_path);
        }
    }

    $new_filename = getName() . '_' . uniqid() . '.' . $extension;
    $upload_path = $upload_dir . $new_filename;
    
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        die(json_encode(['error' => 'Failed to save file']));
    }

    $stmt = $conn->prepare("UPDATE UserInfo SET avatar = ? WHERE token = ?");
    $stmt->bind_param("ss", $new_filename, $token);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'filename' => $new_filename]);
    } else {
        echo json_encode(['error' => 'Failed to update database']);
    }

    $stmt->close();
    $conn->close();
} else {
    die(json_encode(['error' => 'Invalid request']));
}
?>