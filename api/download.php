<?php
$type = isset($_GET['type']) ? $_GET['type'] : 'public';

switch ($type) {
    case 'jar-beta':
        $file = 'files/output.jar';
        break;
    case 'jar-public':
    default:
        $file = 'files/output.jar';
        break;
}

header("Location: $file");
exit();
?>