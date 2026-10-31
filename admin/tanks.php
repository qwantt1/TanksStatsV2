<?php
include '../config.php';
include_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);

    $stmt = $db->prepare('SELECT name, image FROM tanks WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $tank = $stmt->get_result()->fetch_assoc();

    if (!$tank) {
        flash('Танк не найден', 'error');
    } else {
        $stmt = $db->prepare('DELETE FROM tanks WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        if ($tank['image'] && strpos($tank['image'], 'tank_') === 0 && file_exists('../img/tanks/' . $tank['image'])) {
            unlink('../img/tanks/' . $tank['image']);
        }
        $_SESSION['compare'] = array_values(array_diff($_SESSION['compare'] ?? [], [$id]));
        flash('Танк «' . $tank['name'] . '» удалён');
    }
    redirect('tanks.php');
}

$deleteTank = null;
$deleteId = $_GET['delete'] ?? '';
if (ctype_digit($deleteId)) {
    $stmt = $db->prepare('SELECT id, name FROM tanks WHERE id = ?');
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    $deleteTank = $stmt->get_result()->fetch_assoc();
}

$tanks = $db->query('
    SELECT t.id, t.name, t.image, t.hp, t.damage, t.in_game, t.is_available,
           n.name AS nation_name, ty.code AS type_code
    FROM tanks t
    LEFT JOIN nations n ON n.id = t.nation_id
    LEFT JOIN types ty ON ty.id = t.type_id
    ORDER BY t.type_id, t.name
')->fetch_all(MYSQLI_ASSOC);

$title = 'Управление танками';
include '../header.php';
?>
<section class="section">
    <h1 class="page-title">Управление танками</h1>

    <div class="admin-toolbar">
        <a class="back-link" href="index.php">← В админку</a>
        <a class="btn" href="tank_edit.php">+ Добавить танк</a>
    </div>

    <?php if ($deleteTank): ?>
        <div class="confirm">
            <p class="confirm__text">Удалить танк «<?= htmlspecialchars($deleteTank['name']) ?>»? Он пропадёт из каталога и из избранного у всех пользователей.</p>
            <form method="post" action="tanks.php">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$deleteTank['id'] ?>">
                <button class="btn btn--danger" type="submit">Да, удалить</button>
                <a class="btn btn--ghost" href="tanks.php">Отмена</a>
            </form>
        </div>
    <?php endif; ?>

    <?php if (empty($tanks)): ?>
        <p class="empty">Танков пока нет. Добавьте первый.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Название</th>
                        <th>Нация</th>
                        <th>Тип</th>
                        <th>Прочность</th>
                        <th>Урон</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tanks as $tank): ?>
                        <tr>
                            <td><img class="admin-table__thumb" src="<?= htmlspecialchars(tank_image($tank)) ?>" alt=""></td>
                            <td><a href="../tank.php?id=<?= (int)$tank['id'] ?>"><?= htmlspecialchars($tank['name']) ?></a></td>
                            <td><?= htmlspecialchars($tank['nation_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($tank['type_code'] ?? '-') ?></td>
                            <td><?= number_format($tank['hp'], 0, '.', ' ') ?></td>
                            <td><?= number_format($tank['damage'], 0, '.', ' ') ?></td>
                            <td>
                                <?php if ($tank['in_game']): ?>
                                    <span class="badge badge--success">В игре</span>
                                <?php else: ?>
                                    <span class="badge badge--error">Выведен</span>
                                <?php endif; ?>
                                <?php if (!$tank['is_available']): ?>
                                    <span class="badge badge--warning">Скрыт</span>
                                <?php endif; ?>
                            </td>
                            <td class="admin-table__actions">
                                <a class="btn btn--small" href="tank_edit.php?id=<?= (int)$tank['id'] ?>">Изменить</a>
                                <a class="btn btn--small btn--ghost" href="tanks.php?delete=<?= (int)$tank['id'] ?>">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php include '../footer.php'; ?>
