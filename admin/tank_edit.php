<?php
include '../config.php';
include_admin();

$nations = $db->query('SELECT id, name FROM nations ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$types = $db->query('SELECT id, code, name FROM types ORDER BY id')->fetch_all(MYSQLI_ASSOC);

$tank = null;
$id = $_GET['id'] ?? '';
if ($id !== '') {
    if (ctype_digit($id)) {
        $stmt = $db->prepare('SELECT * FROM tanks WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $tank = $stmt->get_result()->fetch_assoc();
    }
    if (!$tank) {
        flash('Танк не найден', 'error');
        redirect('tanks.php');
    }
}

$numberFields = [
    'hp' => 'Прочность',
    'damage' => 'Разовый урон',
    'penetration' => 'Бронепробитие, мм',
    'speed' => 'Макс. скорость, км/ч',
    'armor_hull' => 'Броня корпуса (лоб), мм',
    'armor_turret' => 'Броня башни / рубки (лоб), мм',
    'view_range' => 'Обзор, м',
];

$form = $tank ?? [
    'name' => '',
    'nation_id' => '',
    'type_id' => '',
    'description' => '',
    'image' => null,
    'hp' => '',
    'damage' => '',
    'penetration' => '',
    'reload' => '',
    'speed' => '',
    'armor_hull' => '',
    'armor_turret' => '',
    'view_range' => '',
    'in_game' => 1,
    'is_available' => 1,
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['name'] = trim($_POST['name'] ?? '');
    $form['nation_id'] = $_POST['nation_id'] ?? '';
    $form['type_id'] = $_POST['type_id'] ?? '';
    $form['description'] = trim($_POST['description'] ?? '');
    $form['reload'] = str_replace(',', '.', trim($_POST['reload'] ?? ''));
    $form['in_game'] = isset($_POST['in_game']) ? 1 : 0;
    $form['is_available'] = isset($_POST['is_available']) ? 1 : 0;

    if ($form['name'] === '' || mb_strlen($form['name']) > 100) {
        $errors[] = 'Введите название танка (до 100 символов)';
    }
    if (!in_array($form['nation_id'], array_column($nations, 'id'))) {
        $errors[] = 'Выберите нацию';
    }
    if (!in_array($form['type_id'], array_column($types, 'id'))) {
        $errors[] = 'Выберите тип техники';
    }
    foreach ($numberFields as $field => $label) {
        $form[$field] = trim($_POST[$field] ?? '');
        if (!ctype_digit($form[$field]) || (int)$form[$field] > 100000) {
            $errors[] = $label . ': введите целое число от 0';
        }
    }
    if (!is_numeric($form['reload']) || $form['reload'] <= 0 || $form['reload'] > 999) {
        $errors[] = 'Перезарядка: введите число больше 0, например 12.5';
    }

    $image = $form['image'];
    if (!$errors && !empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Не удалось загрузить картинку';
        } elseif (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
            $errors[] = 'Картинка должна быть в формате PNG, JPG или WEBP';
        } elseif (getimagesize($_FILES['image']['tmp_name']) === false) {
            $errors[] = 'Файл не является картинкой';
        } else {
            $image = 'tank_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], '../img/tanks/' . $image);

            $oldImage = $form['image'];
            if ($oldImage && strpos($oldImage, 'tank_') === 0 && file_exists('../img/tanks/' . $oldImage)) {
                unlink('../img/tanks/' . $oldImage);
            }
        }
    }

    if (!$errors) {
        $name = $form['name'];
        $nationId = (int)$form['nation_id'];
        $typeId = (int)$form['type_id'];
        $description = $form['description'];
        $hp = (int)$form['hp'];
        $damage = (int)$form['damage'];
        $penetration = (int)$form['penetration'];
        $reload = (float)$form['reload'];
        $speed = (int)$form['speed'];
        $armorHull = (int)$form['armor_hull'];
        $armorTurret = (int)$form['armor_turret'];
        $viewRange = (int)$form['view_range'];
        $inGame = $form['in_game'];
        $isAvailable = $form['is_available'];

        if ($tank) {
            $stmt = $db->prepare('
                UPDATE tanks
                SET name = ?, nation_id = ?, type_id = ?, image = ?, description = ?,
                    hp = ?, damage = ?, penetration = ?, reload = ?, speed = ?,
                    armor_hull = ?, armor_turret = ?, view_range = ?, in_game = ?, is_available = ?
                WHERE id = ?
            ');
            $stmt->bind_param('siissiiidiiiiiii', $name, $nationId, $typeId, $image, $description,
                $hp, $damage, $penetration, $reload, $speed,
                $armorHull, $armorTurret, $viewRange, $inGame, $isAvailable, $tank['id']);
            $stmt->execute();
            flash('Танк «' . $name . '» сохранён');
        } else {
            $stmt = $db->prepare('
                INSERT INTO tanks (name, nation_id, type_id, image, description,
                                   hp, damage, penetration, reload, speed,
                                   armor_hull, armor_turret, view_range, in_game, is_available)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->bind_param('siissiiidiiiiii', $name, $nationId, $typeId, $image, $description,
                $hp, $damage, $penetration, $reload, $speed,
                $armorHull, $armorTurret, $viewRange, $inGame, $isAvailable);
            $stmt->execute();
            flash('Танк «' . $name . '» добавлен');
        }
        redirect('tanks.php');
    }
}

$title = $tank ? 'Редактирование: ' . $tank['name'] : 'Новый танк';
include '../header.php';
?>
<section class="section">
    <h1 class="page-title"><?= htmlspecialchars($title) ?></h1>

    <div class="admin-toolbar">
        <a class="back-link" href="tanks.php">← К списку танков</a>
    </div>

    <?php if ($errors): ?>
        <ul class="form-error">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form class="admin-form" method="post" enctype="multipart/form-data">
        <label class="field field--wide">
            <span class="field__label">Название</span>
            <input class="field__input" type="text" name="name" value="<?= htmlspecialchars($form['name']) ?>" maxlength="100" required>
        </label>

        <label class="field">
            <span class="field__label">Нация</span>
            <select class="field__input" name="nation_id" required>
                <option value="">Выберите нацию</option>
                <?php foreach ($nations as $nation): ?>
                    <option value="<?= (int)$nation['id'] ?>" <?= $form['nation_id'] == $nation['id'] ? 'selected' : '' ?>><?= htmlspecialchars($nation['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="field">
            <span class="field__label">Тип техники</span>
            <select class="field__input" name="type_id" required>
                <option value="">Выберите тип</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= (int)$type['id'] ?>" <?= $form['type_id'] == $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['name'] . ' (' . $type['code'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <?php foreach ($numberFields as $field => $label): ?>
            <label class="field">
                <span class="field__label"><?= $label ?></span>
                <input class="field__input" type="number" name="<?= $field ?>" value="<?= htmlspecialchars($form[$field]) ?>" min="0" max="100000" required>
            </label>
        <?php endforeach; ?>

        <label class="field">
            <span class="field__label">Перезарядка, с</span>
            <input class="field__input" type="text" name="reload" value="<?= htmlspecialchars($form['reload']) ?>" placeholder="например 12.5" required>
        </label>

        <label class="field field--wide">
            <span class="field__label">Описание</span>
            <textarea class="field__input" name="description" rows="4"><?= htmlspecialchars($form['description'] ?? '') ?></textarea>
        </label>

        <div class="field field--wide">
            <span class="field__label">Картинка (PNG, JPG или WEBP)</span>
            <?php if ($form['image']): ?>
                <img class="admin-form__preview" src="../img/tanks/<?= htmlspecialchars($form['image']) ?>" alt="">
            <?php endif; ?>
            <input class="field__input" type="file" name="image" accept=".png,.jpg,.jpeg,.webp">
        </div>

        <label class="checkbox">
            <input type="checkbox" name="in_game" value="1" <?= $form['in_game'] ? 'checked' : '' ?>>
            <span>Танк есть в игре</span>
        </label>
        <label class="checkbox">
            <input type="checkbox" name="is_available" value="1" <?= $form['is_available'] ? 'checked' : '' ?>>
            <span>Показывать на сайте</span>
        </label>

        <div class="field--wide">
            <button class="btn btn--big" type="submit"><?= $tank ? 'Сохранить изменения' : 'Добавить танк' ?></button>
        </div>
    </form>
</section>
<?php include '../footer.php'; ?>
