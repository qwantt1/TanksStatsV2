<?php
include 'config.php';
include_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tankId = (int)($_POST['tank_id'] ?? 0);

    $stmt = $db->prepare('SELECT name FROM tanks WHERE id = ?');
    $stmt->bind_param('i', $tankId);
    $stmt->execute();
    $tank = $stmt->get_result()->fetch_assoc();

    if (!$tank) {
        flash('Танк не найден', 'error');
    } else {
        $stmt = $db->prepare('DELETE FROM favorites WHERE user_id = ? AND tank_id = ?');
        $stmt->bind_param('ii', $user['id'], $tankId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            flash($tank['name'] . ' убран из избранного');
        } else {
            $stmt = $db->prepare('INSERT INTO favorites (user_id, tank_id) VALUES (?, ?)');
            $stmt->bind_param('ii', $user['id'], $tankId);
            $stmt->execute();
            flash($tank['name'] . ' добавлен в избранное');
        }
    }
    redirect(back_url('favorites.php'));
}

$stmt = $db->prepare('
    SELECT t.id, t.name, t.image, t.hp, t.damage, t.penetration, t.reload, t.speed, t.in_game,
           n.name AS nation_name, ty.name AS type_name,
           t.updated_at > f.created_at AS changed
    FROM favorites f
    JOIN tanks t ON t.id = f.tank_id
    LEFT JOIN nations n ON n.id = t.nation_id
    LEFT JOIN types ty ON ty.id = t.type_id
    WHERE f.user_id = ? AND t.is_available = 1
    ORDER BY f.created_at DESC, t.name
');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$tanks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$favoriteIds = array_column($tanks, 'id');
$compareIds = $_SESSION['compare'] ?? [];
$back = 'favorites.php';

$title = 'Избранные танки';
include 'header.php';
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Избранное</h1>
        <p class="subtitle">Если ТТХ танка изменятся после добавления, на карточке появится отметка «ТТХ изменились».</p>
    </div>
    <p class="page-count">Танков: <?= count($tanks) ?></p>
</div>

<?php if (empty($tanks)): ?>
    <p class="empty">
        Список пуст. Добавить танк можно кнопкой с сердцем на карточке в <a href="index.php">каталоге</a>.
    </p>
<?php else: ?>
    <div class="catalog">
        <?php foreach ($tanks as $tank): ?>
            <?php include 'tank_card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php include 'footer.php'; ?>
