<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


include_once("backend/db.php");
include_once("config.php");
include_once("backend/session.php");

$sql_users = "SELECT COUNT(*) AS total_users FROM UserInfo";
$result_users = $conn->query($sql_users);

if ($result_users && $result_users->num_rows > 0) {
    $row_users = $result_users->fetch_assoc();
    $totalUsers = $row_users['total_users'];
} else {
    $totalUsers = "err users";
}
function daysSinceDate($specifiedDate) {
    $currentDate = new DateTime();
    $interval = $currentDate->diff($specifiedDate);
    $days = $interval->days;
    return $days;
}
$specifiedDate = DateTime::createFromFormat('d.m.Y', getString("SiteSettings.date"));
$days = daysSinceDate($specifiedDate);
?>

<html lang="en"><head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="keywords" content="atomclient, atom client, atom, arbuz, arbuz client, nursultan, nursultan nextgen, nurik, wexside, celestial, akrien, minecraft, minecraft 1.8, minecraft 1.12.2, minecraft 1.16.5, deadcode, нурсултан, nursultan client, нурсултан клиент, нурик, вексайд, целестиал, акриен, майнкрафт, чит, читы, читы для майнкрафт, читы для minecraft, чит на майнкрафт, чит на minecraft">
  <meta name="description" content="<?php echo getString("SiteSettings.name"); ?> - Лучший клиент для комфортной игры. Для майнкрафт 1.16.5 ">
  <title><?php echo getString("SiteSettings.name"); ?></title>
  <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/hamburgers/1.2.1/hamburgers.min.css">
  <link rel="stylesheet" href="assets/css/var.css">
  <link rel="stylesheet" href="assets/css/normalize.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/media.css">
</head>
<body data-aos-easing="ease" data-aos-duration="1000" data-aos-delay="0">

<div class="wrapper">


  <header><div>
  <?php include_once("resources/header.php"); ?>
</div></header>


  <main class="main-block-bg">
    <div class="container main-block-inner">
      <div class="main-block-info">
        <h1 class="main-block-headline">
          Лучший <br>клиент для <span class="main-block-headline-focus">комфортной</span> <br>игры в Minecraft
        </h1>
        <p class="main-block-desc">
          Мы предлагаем вам самый удобный игровой клиент, который обеспечит Вам максимальное удовольствие от игрового
          процесса.
        </p>
        <div class="main-block-button">
          <a href="/auth" class="main-block-authme">Авторизация</a>
          <a href="<?php echo getString("SiteSettings.discordUrl"); ?>" target="_blank" class="main-block-discord">
            <svg viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg" width="17">
              <path d="M14.9006 4.06779C13.7837 3.56177 12.6046 3.20279 11.3934 3C11.2277
              3.2932 11.0777 3.59486 10.9441 3.90372C9.65398 3.71134 8.34198 3.71134
              7.05182 3.90372C6.91819 3.59489 6.76822 3.29323 6.60255 3C5.39058 3.2045
              4.21068 3.56434 3.09264 4.07044C0.87304 7.32013 0.271342 10.4891 0.572191
               13.6131C1.87204 14.5635 3.32695 15.2862 4.87367 15.75C5.22194 15.2865 5.53012
               14.7947 5.79494 14.28C5.29196 14.0941 4.8065 13.8647 4.34417 13.5946C4.46585
               13.5072 4.58485 13.4173 4.69985 13.3299C6.04511 13.956 7.51339 14.2806 8.99999
               14.2806C10.4866 14.2806 11.9549 13.956 13.3001 13.3299C13.4165 13.4239 13.5355
               13.5139 13.6558 13.5946C13.1926 13.8652 12.7062 14.0949 12.2024 14.2813C12.4668
               14.7958 12.775 15.2871 13.1236 15.75C14.6717 15.2881 16.1277 14.5657 17.4278
               13.6144C17.7808 9.99159 16.8247 6.85173 14.9006 4.06779ZM6.17601 11.6919C5.33765
               11.6919 4.64502 10.939 4.64502 10.0128C4.64502 9.08655 5.31358 8.32705 6.17334
               8.32705C7.0331 8.32705 7.72037 9.08655 7.70567 10.0128C7.69096 10.939 7.03043
               11.6919 6.17601 11.6919ZM11.824 11.6919C10.9843 11.6919 10.2943 10.939 10.2943
               10.0128C10.2943 9.08655 10.9629 8.32705 11.824 8.32705C12.6851 8.32705 13.367
               9.08655 13.3523 10.0128C13.3376 10.939 12.6784 11.6919 11.824 11.6919Z" fill="#fff"></path>
            </svg>
            <span>Discord сервер</span>
          </a>
        </div>
      </div>
    </div>
  </main>

  <section class="about-tariff">
    <?php include_once("resources/tariff.php"); ?>
  </section>

  <section id="advantages" class="advantage-section">
    <div class="advantage-section-step-one">
      <div class="container">
        <div class="advantage-section-step-one-box">
          <h3 class="advantage-step-headline ic-diamond aos-init" data-aos="zoom-fade-right" data-aos-delay="100" data-aos-once="true">
            Эстетичный дизайн</h3>
          <p class="advantage-step-desc aos-init" data-aos="zoom-fade-left" data-aos-delay="300" data-aos-once="true">Мы предлагаем
            продукт с красивым и привлекательным интерфейсом, который делает взаимодействие с ним приятным и
            удовлетворительным</p>
        </div>
      </div>
    </div>
    <div class="advantage-section-step-two">
      <div class="container">
        <div class="advantage-section-step-two-box">
          <p class="advantage-step-desc aos-init" data-aos="zoom-fade-right" data-aos-delay="100" data-aos-once="true">Наш продукт
            обладает широкими возможностями для индивидуальной настройки, позволяя каждому пользователю адаптировать его
            под свои нужды и предпочтения.</p>
          <h3 class="advantage-step-headline keyboard aos-init" data-aos="zoom-fade-left" data-aos-delay="300" data-aos-once="true">
            Гибкость настроек</h3>
        </div>
      </div>
    </div>
    <div class="advantage-section-step-three">
      <div class="container">
        <div class="advantage-section-step-three-box">
          <h3 class="advantage-step-headline update aos-init" data-aos="zoom-fade-right" data-aos-delay="100" data-aos-once="true">
            Регулярные обновления</h3>
          <p class="advantage-step-desc update aos-init" data-aos="zoom-fade-left" data-aos-delay="300" data-aos-once="true">Мы активно
            работаем над улучшением и обновлением нашего продукта, чтобы обеспечить его соответствие последним тенденциям
            и требованиям рынка.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="about-brand">
    <div class="container">
      <div class="about-brand-card-list">
        <div class="about-brand-card update aos-init" data-aos="fade-up" data-aos-delay="100" data-aos-once="true">
          <h5 class="about-brand-card-headline">1</h5>
          <span class="about-brand-card-desc">Обновлений вышло</span>
        </div>
        <div class="about-brand-card market aos-init" data-aos="fade-up" data-aos-delay="300" data-aos-once="true">
          <h5 class="about-brand-card-headline"><?php echo htmlspecialchars($days); ?></h5>
          <span class="about-brand-card-desc">На рынке более дней</span>
        </div>
        <div class="about-brand-card person aos-init" data-aos="fade-up" data-aos-delay="500" data-aos-once="true">
          <h5 class="about-brand-card-headline"><?php echo htmlspecialchars($totalUsers); ?></h5>
          <span class="about-brand-card-desc">Клиентов на данный момент</span>
        </div>
      </div>
    </div>
  </section>

  <section id="videoReview" class="сlient-demo">
    <div class="container">
      <div class="сlient-demo-info">
        <h3 class="сlient-demo-info-headline">Демонстрация клиента</h3>
        <p class="сlient-demo-info-desc">Приглашаем Вас посмотреть короткий видеообзор нашего клиента.</p>
      </div>

      <div class="сlient-dem-video-inner">
        <iframe class="сlient-dem-video" src="<?php echo getString("SiteSettings.ytUrl"); ?>" style="border: none;" allowfullscreen="" width="500">
          </iframe>
      </div>
    </div>
  </section>

</div>
<footer><div>
<?php include_once("resources/footer.php"); ?>
</div></footer>

<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script src="assets/js/style.js"></script>

</body></html>