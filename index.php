<?php
include 'config.php';

$nationId = $_GET['nation'] ?? '';
$typeId = $_GET['type'] ?? '';
$search = trim($_GET['q'] ?? '');

$nations = $db->query('SELECT id, name FROM nations ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$types = $db->query('SELECT id, code, name FROM types ORDER BY id')->fetch_all(MYSQLI_ASSOC);

$sql = '
    SELECT t.id, t.name, t.image, t.hp, t.damage, t.penetration, t.reload, t.speed, t.in_game,
           n.name AS nation_name, ty.name AS type_name
    FROM tanks t
    LEFT JOIN nations n ON n.id = t.nation_id
    LEFT JOIN types ty ON ty.id = t.type_id
    WHERE t.is_available = 1
';
$params = [];
$bindTypes = '';

if (ctype_digit($nationId)) {
    $sql .= ' AND t.nation_id = ?';
    $params[] = (int)$nationId;
    $bindTypes .= 'i';
}
if (ctype_digit($typeId)) {
    $sql .= ' AND t.type_id = ?';
    $params[] = (int)$typeId;
    $bindTypes .= 'i';
}
if ($search !== '') {
    $sql .= ' AND t.name LIKE ?';
    $params[] = '%' . $search . '%';
    $bindTypes .= 's';
}
$sql .= ' ORDER BY t.type_id, t.name';

$stmt = $db->prepare($sql);
if ($params) {
    $stmt->bind_param($bindTypes, ...$params);
}
$stmt->execute();
$tanks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$favoriteIds = [];
if ($user) {
    $stmt = $db->prepare('SELECT tank_id FROM favorites WHERE user_id = ?');
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $favoriteIds = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'tank_id');
}
$compareIds = $_SESSION['compare'] ?? [];
$back = 'index.php' . ($_GET ? '?' . http_build_query($_GET) : '');

$title = 'Каталог танков';
include 'header.php';
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Каталог танков</h1>
        <p class="subtitle"><?= htmlspecialchars($settings['catalog_intro'] ?? '') ?></p>
    </div>
    <p class="page-count">Найдено танков: <?= count($tanks) ?></p>
</div>

<form class="filters__form" method="get" action="index.php">
    <label class="field">
        <span class="field__label">Нация</span>
        <select class="field__input" name="nation">
            <option value="">Все нации</option>
            <?php foreach ($nations as $nation): ?>
                <option value="<?= (int)$nation['id'] ?>" <?= $nationId === (string)$nation['id'] ? 'selected' : '' ?>><?= htmlspecialchars($nation['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="field">
        <span class="field__label">Тип техники</span>
        <select class="field__input" name="type">
            <option value="">Все типы</option>
            <?php foreach ($types as $type): ?>
                <option value="<?= (int)$type['id'] ?>" <?= $typeId === (string)$type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['name'] . ' (' . $type['code'] . ')') ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="field">
        <span class="field__label">Поиск</span>
        <input class="field__input" type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Название танка">
    </label>
    <div class="filters__buttons">
        <button class="btn" type="submit">Найти</button>
        <a class="btn btn--outline" href="index.php">Сбросить</a>
    </div>
</form>

<?php if (empty($tanks)): ?>
    <p class="empty">Ничего не найдено. Измените фильтры или сбросьте их.</p>
<?php else: ?>
    <div class="catalog">
        <?php foreach ($tanks as $tank): ?>
            <?php include 'tank_card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php include 'footer.php'; ?>
