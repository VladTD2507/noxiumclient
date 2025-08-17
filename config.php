<?php

// Конфигурация ниже
$config = [
    "SiteSettings" => [
        "name" => "Miraculos",
        "domainUrl" => "miraculos.tech",
        "discordUrl" => "https://discord.gg/x7Xj2g6Hba",
        "vkUrl" => "",
        "ytUrl" => "https://www.youtube.com/embed/fm2Pat-A6qo?si=u1Qcqtuvi5fSSLYh",
		"email" => "help@miraculos.tech",
        "telegramUrl" => "https://t.me/MiraculosClient",
		"discordWebhook" => "https://discordapp.com/api/webhooks/1359212269698813982/7AWDkejZJXz8sHIioX5MPhPmV6YN7SO5lw1N9H67LmXNls11JyuG0j0Scw3w0xVkoJ3M",
        "recaptchaKey" => "6LeaDzoqAAAAAETQOFh-JXi1f2qE8b7W8FTqdtR-", //https://www.google.com/recaptcha/admin/site/708448154/setup
		"date" => "18.01.2025" // d.m.y
    ],
    "MarketSettings" => [
     "moneeToken" => "bb8df1cc-b13e-4d42-9585-a8af032ad757",
	 "pallyToken" => "23351|bG5fldD6or4GydgcMDyeccseEKX741TgUG6uYZlD",
	 "pallyId" => "WZ753gdmJN"
    ],
	"AdminSettings" => [
	"keyPrefix" => "miracul_key-"
	],
    "LoaderSettings" => [
    "version" => "miraculos-1.0 beta"
    ],
    "smtp" => [
        "host" => "ssl://smtp.mail.ru",
        "username" => "site@internalguard.ru",
        "password" => "jRjWBczHkBNqTMmty6FM"
	]
];

function getString($path) {
    global $config;

    $keys = explode('.', $path); 
    $value = $config;

    foreach ($keys as $key) {
        if (isset($value[$key])) {
            $value = $value[$key];
        } else {
            return null;
        }
    }

    return $value;
}
?>