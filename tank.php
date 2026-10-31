<?php
include 'config.php';

$error = '';
$id = $_GET['id'] ?? null;

if (!$id || !ctype_digit($id) || (int)$id <= 0) {
    $error = 'Танк не найден';
    $tank = null;
} else {
    $stmt = $db->prepare('
        SELECT t.*, n.name AS nation_name, ty.name AS type_name
        FROM tanks t
        LEFT JOIN nations n ON n.id = t.nation_id
        LEFT JOIN types ty ON ty.id = t.type_id
        WHERE t.id = ? AND t.is_available = 1
    ');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $tank = $stmt->get_result()->fetch_assoc();

    if (!$tank) {
        $error = 'Танк не найден';
    }
}

$isFavorite = false;
if ($tank && $user) {
    $stmt = $db->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND tank_id = ?');
    $stmt->bind_param('ii', $user['id'], $tank['id']);
    $stmt->execute();
    $isFavorite = (bool)$stmt->get_result()->fetch_row();
}
$compareIds = $_SESSION['compare'] ?? [];

if ($error) {
    http_response_code(404);
}
$title = $tank ? $tank['name'] : 'Танк не найден';
include 'header.php';
?>
<?php if ($error): ?>
    <section class="panel panel--narrow panel--center">
        <h1 class="panel__title">Танк не найден</h1>
        <p class="subtitle">Проверьте правильность ссылки</p>
        <div class="panel__actions">
            <a class="btn btn--outline" href="index.php">← Вернуться в каталог</a>
        </div>
    </section>
<?php else: ?>
    <a class="back-link" href="index.php">← Вернуться в каталог</a>

    <section class="product-page<?= $tank['in_game'] ? '' : ' product-page--out-of-stock' ?>">
        <div class="product-page__info">
            <img class="product-page__image" src="<?= htmlspecialchars(tank_image($tank)) ?>" alt="<?= htmlspecialchars($tank['name']) ?>">
            <h1 class="product-page__title"><?= htmlspecialchars($tank['name']) ?></h1>
            <p class="product-page__meta">
                <span class="badge badge--neutral"><?= htmlspecialchars($tank['nation_name'] ?? 'Без нации') ?></span>
                <?php if ($tank['type_id']): ?>
                    <a class="badge badge--neutral product-page__type" href="index.php?type=<?= (int)$tank['type_id'] ?>"><?= htmlspecialchars($tank['type_name']) ?></a>
                <?php endif; ?>
                <span class="badge badge--neutral">X уровень</span>
                <?php if ($tank['in_game']): ?>
                    <span class="badge badge--success">Есть в игре</span>
                <?php else: ?>
                    <span class="badge badge--error">Выведен из игры</span>
                <?php endif; ?>
            </p>
            <p class="product-page__description"><?= nl2br(htmlspecialchars($tank['description'] ?? 'Описание отсутствует')) ?></p>
        </div>

        <div class="product-page__actions">
            <div class="product-page__price">
                <span class="product-page__price-label">Урон в минуту</span>
                <span class="product-page__price-value"><?= number_format(dpm($tank), 0, '.', ' ') ?></span>
            </div>

            <table class="ttx">
                <tr><th>Прочность</th><td><?= number_format($tank['hp'], 0, '.', ' ') ?></td></tr>
                <tr><th>Разовый урон</th><td><?= number_format($tank['damage'], 0, '.', ' ') ?></td></tr>
                <tr><th>Бронепробитие</th><td><?= (int)$tank['penetration'] ?> мм</td></tr>
                <tr><th>Перезарядка</th><td><?= number_format($tank['reload'], 1, ',', ' ') ?> с</td></tr>
                <tr><th>Макс. скорость</th><td><?= (int)$tank['speed'] ?> км/ч</td></tr>
                <tr><th>Броня корпуса (лоб)</th><td><?= (int)$tank['armor_hull'] ?> мм</td></tr>
                <tr><th>Броня башни / рубки (лоб)</th><td><?= (int)$tank['armor_turret'] ?> мм</td></tr>
                <tr><th>Обзор</th><td><?= (int)$tank['view_range'] ?> м</td></tr>
            </table>

            <?php if (in_array($tank['id'], $compareIds)): ?>
                <a class="btn btn--big" href="compare.php">Открыть сравнение</a>
            <?php else: ?>
                <form method="post" action="compare.php">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tank_id" value="<?= (int)$tank['id'] ?>">
                    <input type="hidden" name="back" value="tank.php?id=<?= (int)$tank['id'] ?>">
                    <button class="btn btn--big" type="submit">Добавить к сравнению</button>
                </form>
            <?php endif; ?>

            <form method="post" action="favorites.php">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="tank_id" value="<?= (int)$tank['id'] ?>">
                <input type="hidden" name="back" value="tank.php?id=<?= (int)$tank['id'] ?>">
                <?php if ($isFavorite): ?>
                    <button class="btn btn--outline btn--block btn--fav-on" type="submit">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.4l-1.4-1.3C5.6 14.6 2.5 11.8 2.5 8.3 2.5 5.5 4.7 3.3 7.5 3.3c1.6 0 3.2.8 4.5 2 1.3-1.2 2.9-2 4.5-2 2.8 0 5 2.2 5 5 0 3.5-3.1 6.3-8.1 10.8L12 20.4z"/></svg>
                        Убрать из избранного
                    </button>
                <?php else: ?>
                    <button class="btn btn--outline btn--block" type="submit">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.4l-1.4-1.3C5.6 14.6 2.5 11.8 2.5 8.3 2.5 5.5 4.7 3.3 7.5 3.3c1.6 0 3.2.8 4.5 2 1.3-1.2 2.9-2 4.5-2 2.8 0 5 2.2 5 5 0 3.5-3.1 6.3-8.1 10.8L12 20.4z"/></svg>
                        В избранное
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </section>
<?php endif; ?>
<?php include 'footer.php'; ?>
