<?php
if (!isset($db)) {
    include 'config.php';
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Технические работы | TanksStats</title>
    <link rel="stylesheet" href="<?= $base ?>style.css">
</head>
<body>
<header class="header">
    <div class="header__inner">
        <span class="logo">
            <img class="logo__mark" src="<?= $base ?>img/gear.svg" alt="">
            <span>Tanks<span class="logo__accent">Stats</span></span>
        </span>
    </div>
</header>
<main class="main">
    <section class="panel panel--narrow panel--center">
        <h1 class="panel__title">Ведутся технические работы</h1>
        <p class="subtitle"><?= htmlspecialchars($settings['maintenance_text'] ?? '') ?></p>
        <div class="panel__actions">
            <a class="btn btn--outline" href="<?= $base ?>login.php">Вход для администратора</a>
        </div>
    </section>
</main>
<footer class="footer">
    <div class="footer__inner">
        <p class="footer__text"><?= htmlspecialchars($settings['footer_text'] ?? '') ?></p>
    </div>
</footer>
</body>
</html>
