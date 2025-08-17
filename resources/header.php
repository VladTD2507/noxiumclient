<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once("backend/session.php");
?>
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="/" class="brand-name"><?php echo getString("SiteSettings.name"); ?></a>
                <button id="hamburger" class="hamburger hamburger--collapse" type="button">
          <span class="hamburger-box">
            <span class="hamburger-inner"></span>
          </span>
                </button>

                <div class="header-main-nav-inner">
                    <nav class="header-main-nav">
                        <ul class="main-nav">
                            <li class="main-nav-list">
                                <a href="/#tariff" class="main-nav-link">Тарифы</a>
                            </li>
                            <li class="main-nav-list">
                                <a href="/#advantages" class="main-nav-link">Преимущества</a>
                            </li>
                            <li class="main-nav-list">
                                <a href="/#videoReview" class="main-nav-link">Видеообзор</a>
                            </li>
                            <li class="main-nav-list">
                            <?php
                            if (isset($_SESSION['token']) && validateToken()) {
                                echo '<a href="cabinet" class="main-nav-link">Купить</a>';
                            }
                            ?>
                            </li>
                        </ul>
                    </nav>


                    <?php
                    if (isset($_SESSION['token']) && validateToken()) {
                      echo '<a href="/cabinet" class="header-button-authme">Аккаунт</a>';
                    } else {
                      echo '<a href="/auth" class="header-button-authme">Авторизация</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </header>

    