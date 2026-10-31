<?php
$inAdmin = $base === '../';
$compareCount = count($_SESSION['compare'] ?? []);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> | TanksStats</title>
    <link rel="stylesheet" href="<?= $base ?>style.css">
</head>
<body>
<header class="header">
    <div class="header__inner">
        <a class="logo" href="<?= $base ?>index.php">
            <img class="logo__mark" src="<?= $base ?>img/gear.svg" alt="">
            <span>Tanks<span class="logo__accent">Stats</span></span>
        </a>
        <nav class="nav">
            <a class="nav__link <?= !$inAdmin && in_array($page, ['index.php', 'tank.php']) ? 'nav__link--active' : '' ?>" href="<?= $base ?>index.php">Каталог</a>
            <a class="nav__link <?= $page === 'compare.php' ? 'nav__link--active' : '' ?>" href="<?= $base ?>compare.php">
                Сравнение<?php if ($compareCount > 0): ?> <span class="nav__count"><?= $compareCount ?>/2</span><?php endif; ?>
            </a>
            <?php if ($user): ?>
                <a class="nav__link <?= $page === 'favorites.php' ? 'nav__link--active' : '' ?>" href="<?= $base ?>favorites.php">Избранное</a>
            <?php endif; ?>
            <?php if ($isAdmin): ?>
                <a class="nav__link <?= $inAdmin ? 'nav__link--active' : '' ?>" href="<?= $base ?>admin/index.php">Админка</a>
            <?php endif; ?>
        </nav>
        <div class="header__user">
            <?php if ($user): ?>
                <span class="user-chip">
                    <img class="avatar" src="<?= $base ?>img/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="">
                    <span class="user-chip__name"><?= htmlspecialchars($user['username']) ?></span>
                </span>
                <a class="btn btn--outline btn--small" href="<?= $base ?>logout.php">Выйти</a>
            <?php else: ?>
                <a class="btn btn--ghost btn--small" href="<?= $base ?>login.php">Войти</a>
                <a class="btn btn--small" href="<?= $base ?>register.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="main">
    <?php if (isset($_SESSION['flash'])): ?>
        <p class="flash flash--<?= htmlspecialchars($_SESSION['flash']['type']) ?>"><?= htmlspecialchars($_SESSION['flash']['text']) ?></p>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
