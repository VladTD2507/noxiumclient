<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once("config.php");
include_once("backend/db.php");
include_once("backend/session.php");

if (!isset($_SESSION['token']))
{
    header("Location: auth");
    exit();
}


global $id, $till, $role, $formattedDate, $uid, $avatar, $hwid, $email, $ram;
$formattedDate = "Нет подписки"; // По умолчанию

try {
    $query = "SELECT nickname, avatar, id, till, role, uid, hwid, email, ram FROM UserInfo WHERE token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->store_result(); 
    $stmt->bind_result($nicknam, $avatar, $id, $till, $role, $uid, $hwid, $email, $ram);
    
    if ($stmt->num_rows > 0 && $stmt->fetch()) {
        if ($till !== null && $till < time()) {
            $updateQuery = "UPDATE UserInfo SET till = NULL WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('i', $id);
            $updateStmt->execute();
        } else {
            $formattedDate = date('d.m.Y', $till);
        }

        if ($email === "" || $email === null) {
            $email = "Почта не задана.";
        }
        if ($hwid === "" || $hwid === null || empty($hwid)) {
            $hwid = "Не привязан.";
        }
    } else {
        header("Location: auth");
        exit();
    }

    $stmt->close();
} catch (Exception $e) {
    // Обработка исключений
}

try {
    $query = "SELECT productId, productName, productDir, productPrice FROM Market"; 
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stmt->store_result(); 
    $stmt->bind_result($productId, $productName, $productDir, $productPrice);
	
    if ($stmt->num_rows > 0) {
        while ($stmt->fetch()) { 
            if ($productDir == null || $productDir === "") {
                $productDir = "assets/images/shop/unknown.png";
            }
        }
    } else {
        echo "No products found in the database.";
    }

    $stmt->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

if ($role == "ADMIN") {
    $roleName = "Администратор";
} elseif ($role == "USER") {
    $roleName = "Покупатель";
} elseif ($role == "MEDIA") {
    $roleName = "Ютубер";
} elseif ($role == "FRIEND") {
    $roleName = "Друг";
} elseif ($role == "DEV") {
    $roleName = "Разработчик";
} elseif ($role == "DEV+") {
    $roleName = "Главный разработчик";
} else {
    $roleName = "Пользователь";
}

function getVersion() {
	return getString("LoaderSettings.version");
}

?>
	<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>
			<?php echo getString("SiteSettings.name"); ?>
		</title>
		<link rel="shortcut icon" href="assets/images/favicon.svg" type="image/svg">
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
		<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&amp;display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/hamburgers/1.2.1/hamburgers.min.css">
		<link rel="stylesheet" href="assets/css/var.css">
		<link rel="stylesheet" href="assets/css/normalize.css">
		<link rel="stylesheet" href="assets/css/style.css">
		<link rel="stylesheet" href="assets/css/media.css"> </head>

	<body>
		<style>
		.form-container {
			display: flex;
			padding: 20px;
		}
		
		.form-group {
			margin-bottom: 14px;
			width: 100%;
		}
		
		.form-group label {
			font-size: 16px;
			display: block;
			padding: 10px;
		}
		</style>
		<div class="wrapper">
			<header>
				<div>
					<?php include_once("resources/header.php"); ?>
				</div>
			</header>
			<div class="personal-account">
				<div class="container personal-account-inner">
					<div>
						<div>
							<div>
								<?php include_once("resources/cabinet/tabs.php"); ?>
							</div>
						</div>
					</div>
					<div id="account" class="content-section active personal-account-content">
						<div>
							<div>
								<h5 class="personal-account-content-headline">Мой аккаунт</h5>
								<div class="personal-account-content-inner">
									<div class="personal-account-content-avatar-box" style="background: linear-gradient(90deg, #000 0%, rgba(0, 0, 0, 0) 100%), url(assets/images/avatar.png) no-repeat left center / 100%;  border-radius: 16px;   padding: 16px;">
										<div class="personal-account-content-avatar-inner">
	                                        <div class="personal-account-content-avatar-inner">
											<div class="personal-account-content-avatar-block">
											 <form id="avatar-form" enctype="multipart/form-data">
											 <input type="file" id="file-input" name="avatar" accept="image/png, image/jpeg" style="display: none;">
											 <button type="button" class="personal-account-content-avatar-upload" onclick="$('#file-input').click()">
											       <svg class="personal-account-content-avatar-icon" viewBox="0 0 15 17" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.90192 2.47915L9.95443 4.60322C10.1266 4.78134 10.4056 4.78134 10.5777 4.60322C10.7499 4.42509 10.7499 4.1363 10.5777 3.95817L7.77283 1.05547C7.60071 0.877344 7.32164 0.877344 7.14952 1.05547L4.3446 3.95817C4.17248 4.1363 4.17248 4.42509 4.3446 4.60322C4.51673 4.78134 4.79579 4.78134 4.96792 4.60322L7.02042 2.47915L7.02042 10.5003H7.90192L7.90192 2.47915Z" fill="white"></path>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.39575 6.27709V5.36278H3.07742C1.6108 5.36278 0.421875 6.59083 0.421875 8.1057V13.5915C0.421875 15.1064 1.6108 16.3344 3.07742 16.3344H11.9292C13.3959 16.3344 14.5848 15.1064 14.5848 13.5915V8.1057C14.5848 6.59083 13.3959 5.36278 11.9292 5.36278H9.6952V6.27709H11.9292C12.907 6.27709 13.6996 7.09579 13.6996 8.1057V13.5915C13.6996 14.6014 12.907 15.4201 11.9292 15.4201H3.07742C2.09967 15.4201 1.30706 14.6014 1.30706 13.5915V8.1057C1.30706 7.09579 2.09967 6.27709 3.07742 6.27709H5.39575Z" fill="white"></path>
      </svg>
	  </button>
											 <?php
											 if (empty($avatar) || $avatar == null) {
											 echo '<img id="avatar" class="personal-account-content-avatar" src="assets/images/avatar.png" alt="User Avatar">';
											 } else {
											 echo '<img id="avatar" class="personal-account-content-avatar" src="uploads/' . $avatar . '" alt="User Avatar">';
											 }
											 ?>
											 </form>
											 </div>
											 </div>
											<div class="personal-account-content-avatar-info"> <span class="personal-account-content-avatar-role">
                                             <span><?php echo $roleName; ?></span> <span class="personal-account-content-avatar-role-id"><?php echo $uid; ?></span> </span> <span class="personal-account-content-avatar-user"><?php echo $nicknam ?></span> </div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div>
							<div>
								<div class="personal-account-setting personal-account-content-inner">
									<h5 class="personal-account-content-headline">Настройки аккаунта</h5>
									<form class="personal-account-setting-form" id="change_password_form">
										<input class="personal-account-setting-field" type="password" name="password" required="" placeholder="НВCI$I39(">
										<button class="personal-account-setting-button" type="submit">Изменить пароль</button>
										<p id="result" style="font-size: 15px; margin-top: 10px;"></p>
									</form>
								</div>
								<div class="personal-account-license personal-account-content-inner">
									<?php if ($till != 0) {
                                    echo '<div class="personal-account-license-ends">
									<h5 class="personal-account-content-headline license">Лицензия истекает</h5>
                                     <span class="personal-account-license-end-data">' . $formattedDate . '</span>
                                     <span class="personal-account-license-date-format">+3 GTM</span>
                                    </div>';
								} ?>
										<form class="personal-account-license-promo-code" id="activate-from">
											<label class="personal-account-license-promo-code-label" for="key"> Есть ключ?
												<input id="key" name="key" class="personal-account-license-promo-code-field" type="text" required="" placeholder="Введите ваш ключ"> </label>
											<button class="personal-account-license-promo-code-button" type="submit">Отправить</button>
											<p id="activate_resoult" style="font-size: 15px; font-weight: 550"></p>
										</form>
										<?php if ($till != 0) {
										echo '<form class="memory" id="memory-form">
											<label class="personal-account-license-promo-code-label" for="memory"> Оперативная память
												<input id="memory" name="memory" class="personal-account-license-promo-code-field" min="1024" max="32196" type="text" required="" placeholder="' . $ram . '"> </label>
											<button class="personal-account-license-promo-code-button" type="submit">Отправить</button>
											<p id="memory_result" style="font-size: 15px; font-weight: 550"></p>
										</form>'; } ?>
								</div>
								<div class="personal-account-button-exit"> <a href="/backend/auth/logout" class="personal-account-content-exit" type="button">Выйти</a> </div>
							</div>
						</div>
					</div>
					<div id="shop" class="content-section personal-account-content shop">
						<div>
							<div>
								<style>
								.payment-option {
									display: flex;
									align-items: center;
									margin-bottom: 15px;
									padding: 15px;
									background-color: rgba(0, 0, 0, 0.2);
									border-radius: 16px;
									cursor: pointer;
									transition: background-color 0.3s ease;
									font-weight: 500;
									font-size: 16px;
									width: 450px;
								}
								
								.payment-option-selected {
									outline: 1px solid #6A83F8;
								}
								
								.payment-option input[type="radio"] {
									display: none;
								}
								</style>
								<div class="personal-account-content-shop">
									<h5 class="personal-account-content-headline">Магазин</h5>
									<?php include_once("resources/cabinet/market.php"); ?>
							</div>
							<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
						</div>
					</div>
				</div>
				<div id="download" class="content-section personal-account-content">
					<div class="personal-account-content-download-client"> <a href="api/files/loader.exe" class="personal-account-content-download-client-button">Скачать клиент</a>
						<h5 class="personal-account-content-headline download-client">Информация о последнем обновлении</h5> <span class="personal-account-content-download-client-number">Текущая версия<span class="personal-account-content-download-client-count"><?php echo getVersion(); ?></span></span>
						<iframe class="personal-account-content-download-client-video" style="border: none;" src="<?php echo getString("SiteSettings.ytUrl"); ?>" allowfullscreen="" height="500;"> </iframe>
					</div>
				</div>
				<div id="support" class="content-section personal-account-content">
					<div class="personal-account-content-support">
						<h5 class="personal-account-content-headline">Поддержка</h5>
						<?php echo '<p class="personal-account-content-support-desc"> Возникли вопросы? Переходи в Discord-канал, где тебе помогут. Здесь ты найдёшь опытных участников, готовых ответить на твои вопросы и помочь решить возникшие проблемы. Не стесняйся задавать вопросы — вместе мы сможем найти решение! </p> <a href="' . getString("SiteSettings.discordUrl")  . '" target="_blank" class="personal-account-content-support-link">Discord канал</a> </div>'; ?>
					</div>
					<div id="admin" class="content-section personal-account-content">
					 <a href="adminpanel.php" class="personal-account-content-download-client-button">Перейти</a>
						<h5 class="personal-account-content-headline download-client">Здесь вы можете управлять сайтом и пользователями.</h5> </div>
				</div>
			</div>
			<footer>
				<div>
					<?php include_once("resources/footer.php"); ?>
				</div>
			</footer>
		</div>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<script src="assets/js/style.js"></script>
		<script src="assets/js/dashboard.js"></script>
<script>
$(document).ready(function() {
  $('#file-input').on('change', function() {
    $('#avatar-form').submit();
  });

  $('#avatar-form').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    
    $.ajax({
      url: 'backend/cabinet/upload_avatar.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(data) {
        if (data.success) {
          alert('Avatar uploaded successfully');
          $('#avatar').attr('src', '../../uploads/' + data.filename);
        } else {
          alert('Error: ' + data.error);
        }
      },
      error: function(xhr, status, error) {
        console.error('Error:', error);
      }
    });
  });
});
</script>
	</body>

	</html>