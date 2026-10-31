<?php
include '../config.php';
include_admin();

$tankCount = $db->query('SELECT COUNT(*) FROM tanks')->fetch_row()[0];
$userCount = $db->query('SELECT COUNT(*) FROM users')->fetch_row()[0];
$bannedCount = $db->query('SELECT COUNT(*) FROM users WHERE is_banned = 1')->fetch_row()[0];
$maintenance = ($settings['maintenance'] ?? '0') === '1';

$title = 'Администрирование';
include '../header.php';
?>
<section class="section">
    <h1 class="page-title">Администрирование</h1>

    <div class="admin-grid">
        <article class="admin-card">
            <h2 class="admin-card__title">Управление танками</h2>
            <p class="admin-card__text">Танков в базе: <?= (int)$tankCount ?></p>
            <a class="btn btn--small btn--outline admin-card__link" href="tanks.php">Открыть</a>
        </article>

        <article class="admin-card">
            <h2 class="admin-card__title">Шатдаун основных страниц</h2>
            <p class="admin-card__text">
                Сейчас:
                <?php if ($maintenance): ?>
                    <span class="status status--on">техработы включены</span>
                <?php else: ?>
                    <span class="status status--off">сайт работает</span>
                <?php endif; ?>
            </p>
            <a class="btn btn--small btn--outline admin-card__link" href="maintenance.php">Открыть</a>
        </article>

        <article class="admin-card">
            <h2 class="admin-card__title">Поиск пользователя</h2>
            <p class="admin-card__text">Пользователей: <?= (int)$userCount ?></p>
            <a class="btn btn--small btn--outline admin-card__link" href="users.php">Открыть</a>
        </article>

        <article class="admin-card">
            <h2 class="admin-card__title">Редактирование информации страниц</h2>
            <p class="admin-card__text">Приветствие, текст каталога, подвал, сообщение о техработах</p>
            <a class="btn btn--small btn--outline admin-card__link" href="texts.php">Открыть</a>
        </article>

        <article class="admin-card">
            <h2 class="admin-card__title">Бан пользователя</h2>
            <p class="admin-card__text">Заблокировано: <?= (int)$bannedCount ?></p>
            <a class="btn btn--small btn--outline admin-card__link" href="users.php?banned=1">Открыть</a>
        </article>
    </div>
</section>
<?php include '../footer.php'; ?>
