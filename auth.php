<?php
include_once("config.php");
?>
<html lang="en"><head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo getString("SiteSettings.name"); ?></title>
  <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/hamburgers/1.2.1/hamburgers.min.css">
  <link rel="stylesheet" href="assets/css/var.css">
  <link rel="stylesheet" href="assets/css/normalize.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/media.css">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<div class="wrapper">
  <header><div>
  <?php include_once("resources/header.php"); ?>
</div></header>

<?php
if (isset($_GET['do'])) {
    $do = $_GET['do'];
if ($do == "login") {
echo '
  <section class="authorization">
    <div class="container">
     <div class="form-group-authorization">
       <form class="form-authorization" id="authForm">
        <div class="form-authorization-logo">
          <span class="form-authorization-logo-brand-name">' . getString("SiteSettings.name") . '</span>
        </div>
         <h3 class="form-authorization-headline">Войдите с помощью учетной записи</h3>

         <div class="form-authorization-row">
           <input class="form-authorization-field" id="log-login" type="text" name="login" required="" placeholder="Никнейм">
         </div>
         <div class="form-authorization-row">
           <input class="form-authorization-field" id="log-password" name="password" type="password" required="" placeholder="Пароль">
         </div>
         <a href="?do=lost" class="form-authorization-recover">Забыли пароль?</a>

         <p id="result" style="font-size: 16px; font-weight: 600; color: red;"> </p>

         <button class="form-authorization-button" type="submit" style="margin-top: 80px;">Войти в аккаунт</button>
          <div class="form-authorization-register">
            <span class="form-authorization-register-text">Нет учетной записи?</span>
            <a href="?do=register" class="form-authorization-register-link">Зарегистрироваться</a>
          </div>
       </form>
     </div>
    </div>
  </section>';
  } elseif ($do == "register") {
    echo '<section class="authorization">
    <div class="container">
      <div class="form-group-authorization">
        <form class="form-authorization" id="registerForm">
          <div class="form-authorization-logo">
            <span class="form-authorization-logo-brand-name">' . getString("SiteSettings.name") .'</span>
          </div>
          <h3 class="form-authorization-headline">Зарегистрируйте учетную запись</h3>

          <div class="form-authorization-row">
            <input class="form-authorization-field" id="reg-mail" name="email" type="email" required="" placeholder="Введите адрес электронной почты">
          </div>
          <div class="form-authorization-row">
            <input class="form-authorization-field" id="reg-login" name="login" type="text" required="" placeholder="Придумайте никнейм">
          </div>
          <div class="form-authorization-row password">
            <input class="form-authorization-field" id="reg-password" name="pw_hash" type="password" required="" placeholder="Введите пароль">
          </div>

          <p id="registerResult" style="font-size: 16px; font-weight: 600; color: red;"> </p>


          <div style="margin-left: 23%; margin-top: 5px;">
            <div class="g-recaptcha" data-sitekey="' . getString("SiteSettings.recaptchaKey") .'">
          </div>
          <div style="width: 304px; height: 78px;"><div>


          <button class="form-authorization-button" style="margin-top: 40px;" type="submit">Зарегистрироваться</button>
          <div class="form-authorization-register">
            <span class="form-authorization-register-text">Есть учетная запись?</span>
            <a href="?do=login" class="form-authorization-register-link">Войти</a>
          </div>
        </form>
      </div>
    </div>
  </section>';
  } elseif ($do === "lost") {
  echo '<section class="authorization">
        <div class="container">
            <div class="form-group-authorization">
                <form class="form-authorization" id="authForm">
                    <div class="form-authorization-logo">
                        <span class="form-authorization-logo-brand-name">' . getString("SiteSettings.name") . '</span>
                    </div>
                    <h3 class="form-authorization-headline">Восстановление</h3>

                    <div class="form-authorization-row">
                        <input class="form-authorization-field" id="rec-mail" type="text" name="email" required="" placeholder="Адрес электронной почты">
                    </div>

                    <p id="result" style="font-size: 16px; font-weight: 600; color: green;"> </p>
            <div style="margin-left: 23%; margin-top: 5px;">
            <div class="g-recaptcha" data-sitekey="' . getString("SiteSettings.recaptchaKey") .'">
          </div>
          <div style="width: 304px; height: 78px;"><div>

                    <button class="form-authorization-button" type="submit" style="margin-top: 30px;">Восстановить аккаунт</button>
                    <div class="form-authorization-register">
                        <span class="form-authorization-register-text">Вспомнили?</span>
                        <a href="?do=login" class="form-authorization-register-link">Войти</a>
                    </div>
                </form>
            </div>
        </div>
    </section>';
  }
  } else {
    echo '
  <section class="authorization">
    <div class="container">
     <div class="form-group-authorization">
       <form class="form-authorization" id="authForm">
        <div class="form-authorization-logo">
          <span class="form-authorization-logo-brand-name">' . getString("SiteSettings.name"). '</span>
        </div>
         <h3 class="form-authorization-headline">Войдите с помощью учетной записи</h3>

         <div class="form-authorization-row">
           <input class="form-authorization-field" id="log-login" type="text" name="login" required="" placeholder="Никнейм">
         </div>
         <div class="form-authorization-row">
           <input class="form-authorization-field" id="log-password" name="password" type="password" required="" placeholder="Пароль">
         </div>
         <a href="?do=lost" class="form-authorization-recover">Забыли пароль?</a>

         <p id="result" style="font-size: 16px; font-weight: 600; color: red;"> </p>

         <button class="form-authorization-button" type="submit" style="margin-top: 80px;">Войти в аккаунт</button>
          <div class="form-authorization-register">
            <span class="form-authorization-register-text">Нет учетной записи?</span>
            <a href="?do=register" class="form-authorization-register-link">Зарегистрироваться</a>
          </div>
       </form>
     </div>
    </div>
  </section>';
  }
  ?>

  <footer><div>
  <?php include_once("resources/footer.php"); ?>
</div></footer>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="assets/js/style.js"></script>
<script src="assets/js/auth.js"></script>
</body></html>