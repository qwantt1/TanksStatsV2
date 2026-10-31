<?php
include '../config.php';
include_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $value = ($settings['maintenance'] ?? '0') === '1' ? '0' : '1';

    $stmt = $db->prepare("UPDATE settings SET value = ? WHERE name = 'maintenance'");
    $stmt->bind_param('s', $value);
    $stmt->execute();

    flash($value === '1' ? 'Режим технических работ включён' : 'Сайт снова доступен всем');
    redirect('maintenance.php');
}

$enabled = ($settings['maintenance'] ?? '0') === '1';

$title = 'Шатдаун основных страниц';
include '../header.php';
?>
<section class="section">
    <h1 class="page-title">Шатдаун основных страниц</h1>

    <div class="admin-toolbar">
        <a class="back-link" href="index.php">← В админку</a>
    </div>

    <div class="panel panel--narrow">
        <p class="panel__status">
            Сейчас:
            <?php if ($enabled): ?>
                <span class="status status--on">техработы включены</span>
            <?php else: ?>
                <span class="status status--off">сайт работает</span>
            <?php endif; ?>
        </p>
        <p class="subtitle">
            Когда режим включён, все страницы сайта показывают сообщение о технических работах.
            Администраторы продолжают видеть сайт как обычно.
        </p>
        <p class="panel__quote">«<?= htmlspecialchars($settings['maintenance_text'] ?? '') ?>»</p>
        <p class="subtitle">Текст сообщения можно поменять в разделе <a href="texts.php">«Редактирование информации страниц»</a>.</p>

        <form method="post" action="maintenance.php">
            <?php if ($enabled): ?>
                <button class="btn btn--big btn--green" type="submit">Выключить техработы</button>
            <?php else: ?>
                <button class="btn btn--big btn--danger" type="submit">Включить техработы</button>
            <?php endif; ?>
        </form>
    </div>
</section>
<?php include '../footer.php'; ?>
