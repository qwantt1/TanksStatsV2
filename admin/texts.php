<?php
include '../config.php';
include_admin();

$fields = [
    'welcome_text' => ['Текст под заголовком на странице входа', 100],
    'catalog_intro' => ['Текст под заголовком каталога', 500],
    'maintenance_text' => ['Сообщение о технических работах', 500],
    'footer_text' => ['Текст в подвале сайта', 200],
];

$errors = [];
$values = [];
foreach ($fields as $name => $field) {
    $values[$name] = $settings[$name] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $name => [$label, $max]) {
        $values[$name] = trim($_POST[$name] ?? '');
        if ($values[$name] === '') {
            $errors[] = $label . ': поле не может быть пустым';
        } elseif (mb_strlen($values[$name]) > $max) {
            $errors[] = $label . ': не больше ' . $max . ' символов';
        }
    }

    if (!$errors) {
        $stmt = $db->prepare('UPDATE settings SET value = ? WHERE name = ?');
        foreach ($values as $name => $value) {
            $stmt->bind_param('ss', $value, $name);
            $stmt->execute();
        }
        flash('Тексты страниц сохранены');
        redirect('texts.php');
    }
}

$title = 'Редактирование информации страниц';
include '../header.php';
?>
<section class="section">
    <h1 class="page-title">Редактирование информации страниц</h1>

    <div class="admin-toolbar">
        <a class="back-link" href="index.php">← В админку</a>
    </div>

    <?php if ($errors): ?>
        <ul class="form-error">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form class="admin-form admin-form--single" method="post" action="texts.php">
        <?php foreach ($fields as $name => [$label, $max]): ?>
            <label class="field field--wide">
                <span class="field__label"><?= $label ?></span>
                <?php if ($max > 200): ?>
                    <textarea class="field__input" name="<?= $name ?>" rows="3" maxlength="<?= $max ?>"><?= htmlspecialchars($values[$name]) ?></textarea>
                <?php else: ?>
                    <input class="field__input" type="text" name="<?= $name ?>" value="<?= htmlspecialchars($values[$name]) ?>" maxlength="<?= $max ?>">
                <?php endif; ?>
            </label>
        <?php endforeach; ?>
        <div class="field--wide">
            <button class="btn btn--big" type="submit">Сохранить тексты</button>
        </div>
    </form>
</section>
<?php include '../footer.php'; ?>
