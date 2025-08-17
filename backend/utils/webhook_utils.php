<?php

include_once("../../../config.php");

function sendWebhook($message)
{
    $webhookUrl =
        getString("SiteSettings.discordWebhook");
    $data = [
        "content" => $message,
    ];
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    curl_close($ch);
}

?>