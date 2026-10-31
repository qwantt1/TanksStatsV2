<?php
include 'config.php';

$compare = $_SESSION['compare'] ?? [];

$allTanks = [];
$result = $db->query('
    SELECT t.*, n.name AS nation_name, ty.name AS type_name
    FROM tanks t
    LEFT JOIN nations n ON n.id = t.nation_id
    LEFT JOIN types ty ON ty.id = t.type_id
    WHERE t.is_available = 1
    ORDER BY t.type_id, t.name
');
while ($row = $result->fetch_assoc()) {
    $row['dpm'] = dpm($row);
    $allTanks[$row['id']] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $tankId = (int)($_POST['tank_id'] ?? 0);

    if ($action === 'add') {
        if (!isset($allTanks[$tankId])) {
            flash('Танк не найден', 'error');
        } elseif (in_array($tankId, $compare)) {
            flash('Этот танк уже в сравнении', 'error');
        } elseif (count($compare) >= 2) {
            flash('Сравнить можно только 2 танка. Уберите один, чтобы добавить новый', 'error');
        } else {
            $compare[] = $tankId;
            flash($allTanks[$tankId]['name'] . ' добавлен в сравнение (' . count($compare) . ' из 2)');
        }
    } elseif ($action === 'remove') {
        $compare = array_values(array_diff($compare, [$tankId]));
    } elseif ($action === 'clear') {
        $compare = [];
    }

    $_SESSION['compare'] = $compare;

    if ($action === 'add' && count($compare) < 2) {
        redirect(back_url());
    }
    redirect('compare.php');
}

if (isset($_GET['tank1'], $_GET['tank2'])) {
    $compare = [];
    foreach ([$_GET['tank1'], $_GET['tank2']] as $value) {
        if (ctype_digit($value) && isset($allTanks[(int)$value]) && !in_array((int)$value, $compare)) {
            $compare[] = (int)$value;
        }
    }
    $_SESSION['compare'] = $compare;
}

$selected = [];
foreach ($compare as $tankId) {
    if (isset($allTanks[$tankId])) {
        $selected[] = $allTanks[$tankId];
    }
}

$groups = [];
foreach ($allTanks as $tank) {
    $groups[$tank['type_name'] ?? 'Без типа'][] = $tank;
}

$rows = [
    ['hp', 'Прочность', 'max', ''],
    ['damage', 'Разовый урон', 'max', ''],
    ['dpm', 'Урон в минуту', 'max', ''],
    ['penetration', 'Бронепробитие', 'max', ' мм'],
    ['reload', 'Перезарядка', 'min', ' с'],
    ['speed', 'Макс. скорость', 'max', ' км/ч'],
    ['armor_hull', 'Броня корпуса (лоб)', 'max', ' мм'],
    ['armor_turret', 'Броня башни / рубки (лоб)', 'max', ' мм'],
    ['view_range', 'Обзор', 'max', ' м'],
];

$wins = [0, 0];
if (count($selected) === 2) {
    foreach ($rows as $row) {
        $a = (float)$selected[0][$row[0]];
        $b = (float)$selected[1][$row[0]];
        if ($a != $b) {
            $firstBetter = $row[2] === 'max' ? $a > $b : $a < $b;
            $wins[$firstBetter ? 0 : 1]++;
        }
    }
}

$title = 'Сравнение танков';
include 'header.php';
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Сравнение техники</h1>
        <p class="subtitle">Выберите два танка. Лучшее значение в каждой строке выделено зелёным.</p>
    </div>
    <p class="page-count">Выбрано <?= count($selected) ?> из 2 танков</p>
</div>

<form class="filters__form filters__form--two" method="get" action="compare.php">
    <?php foreach (['tank1' => 'Танк 1', 'tank2' => 'Танк 2'] as $field => $label): ?>
        <?php $current = $selected[$field === 'tank1' ? 0 : 1]['id'] ?? 0; ?>
        <label class="field">
            <span class="field__label"><?= $label ?></span>
            <select class="field__input" name="<?= $field ?>">
                <option value="">Выберите танк</option>
                <?php foreach ($groups as $groupName => $groupTanks): ?>
                    <optgroup label="<?= htmlspecialchars($groupName) ?>">
                        <?php foreach ($groupTanks as $tank): ?>
                            <option value="<?= (int)$tank['id'] ?>" <?= $current == $tank['id'] ? 'selected' : '' ?>><?= htmlspecialchars($tank['name'] . ' (' . ($tank['nation_name'] ?? 'без нации') . ')') ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endforeach; ?>
    <div class="filters__buttons">
        <button class="btn" type="submit">Сравнить</button>
    </div>
</form>

<section class="compare">
    <?php if (count($selected) < 2): ?>
        <?php if ($selected): ?>
            <div class="compare__picked">
                <img class="compare__thumb" src="<?= htmlspecialchars(tank_image($selected[0])) ?>" alt="">
                <a class="compare__picked-name" href="tank.php?id=<?= (int)$selected[0]['id'] ?>"><?= htmlspecialchars($selected[0]['name']) ?></a>
                <form method="post" action="compare.php">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="tank_id" value="<?= (int)$selected[0]['id'] ?>">
                    <button class="btn btn--small btn--outline" type="submit">Убрать</button>
                </form>
            </div>
        <?php endif; ?>
        <p class="compare__hint">
            Нужно выбрать ещё <?= 2 - count($selected) ?> <?= count($selected) === 1 ? 'танк' : 'танка' ?>: в списке выше
            или кнопкой «Сравнить» в <a href="index.php">каталоге</a>.
        </p>
    <?php else: ?>
        <div class="compare__table-wrap">
            <table class="compare__table">
                <thead>
                    <tr>
                        <?php foreach ($selected as $index => $tank): ?>
                            <?php if ($index === 1): ?><th class="compare__vs">vs</th><?php endif; ?>
                            <th class="compare__head">
                                <img class="compare__thumb" src="<?= htmlspecialchars(tank_image($tank)) ?>" alt="">
                                <a class="compare__name" href="tank.php?id=<?= (int)$tank['id'] ?>"><?= htmlspecialchars($tank['name']) ?></a>
                                <span class="compare__sub"><?= htmlspecialchars(($tank['nation_name'] ?? 'без нации') . ', ' . mb_strtolower($tank['type_name'] ?? 'без типа')) ?></span>
                                <form method="post" action="compare.php">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="tank_id" value="<?= (int)$tank['id'] ?>">
                                    <button class="btn btn--small btn--outline" type="submit">Убрать</button>
                                </form>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as [$key, $label, $better, $unit]): ?>
                        <?php
                        $a = (float)$selected[0][$key];
                        $b = (float)$selected[1][$key];
                        $bestA = $better === 'max' ? $a > $b : $a < $b;
                        $bestB = $better === 'max' ? $b > $a : $b < $a;
                        $decimals = $key === 'reload' ? 1 : 0;
                        ?>
                        <tr>
                            <td class="compare__cell<?= $bestA ? ' compare__cell--best' : '' ?>"><?= number_format($a, $decimals, ',', ' ') . $unit ?></td>
                            <th class="compare__label"><?= $label ?></th>
                            <td class="compare__cell<?= $bestB ? ' compare__cell--best' : '' ?>"><?= number_format($b, $decimals, ',', ' ') . $unit ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="compare__total">
                        <td class="compare__cell<?= $wins[0] > $wins[1] ? ' compare__cell--best' : '' ?>"><?= $wins[0] ?></td>
                        <th class="compare__label">Лучше по характеристикам</th>
                        <td class="compare__cell<?= $wins[1] > $wins[0] ? ' compare__cell--best' : '' ?>"><?= $wins[1] ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if ($selected): ?>
        <div class="compare__footer">
            <?php if (count($selected) === 2): ?>
                <p class="compare__legend"><span class="compare__legend-mark"></span>лучшее значение в паре</p>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
            <form method="post" action="compare.php">
                <input type="hidden" name="action" value="clear">
                <button class="btn btn--small btn--ghost" type="submit">Очистить сравнение</button>
            </form>
        </div>
    <?php endif; ?>
</section>
<?php include 'footer.php'; ?>
