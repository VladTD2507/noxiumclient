<aside class="personal-account-aside">
	<ul class="personal-account-menu">
		<li class="personal-account-menu-list"> <a class="personal-account-menu-link active" data-target="account">Мой аккаунт</a> </li>
		<li class="personal-account-menu-list"> <a class="personal-account-menu-link" data-target="shop">Магазин</a> </li>
		<?php if ($till != 0) {
		echo '<li class="personal-account-menu-list"> <a class="personal-account-menu-link" data-target="download">Скачать клиент</a> </li>';
		} ?>
		<li class="personal-account-menu-list"> <a class="personal-account-menu-link" data-target="support">Поддержка</a> </li>
		<?php if ($role == "ADMIN" || $role == "DEV" || $role == "DEV+") {
		echo '<li class="personal-account-menu-list"> <a class="personal-account-menu-link" data-target="admin">Админка</a> </li>';
		} ?>
	</ul>
</aside>