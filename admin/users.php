<?php
include '../config.php';
include_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $banned = ($_POST['action'] ?? '') === 'ban' ? 1 : 0;

    $stmt = $db->prepare('SELECT id, username, role FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $target = $stmt->get_result()->fetch_assoc();

    if (!$target) {
        flash('Пользователь не найден', 'error');
    } elseif ($target['role'] === 'admin') {
        flash('Нельзя заблокировать администратора', 'error');
    } else {
        $stmt = $db->prepare('UPDATE users SET is_banned = ? WHERE id = ?');
        $stmt->bind_param('ii', $banned, $userId);
        $stmt->execute();
        flash($banned ? 'Пользователь ' . $target['username'] . ' заблокирован' : 'Пользователь ' . $target['username'] . ' разблокирован');
    }
    redirect(back_url('users.php'));
}

$search = trim($_GET['q'] ?? '');
$onlyBanned = ($_GET['banned'] ?? '') === '1';

$sql = 'SELECT id, username, email, avatar, role, is_banned, created_at FROM users WHERE 1 = 1';
$params = [];
$bindTypes = '';
if ($search !== '') {
    $sql .= ' AND (username LIKE ? OR email LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $bindTypes .= 'ss';
}
if ($onlyBanned) {
    $sql .= ' AND is_banned = 1';
}
$sql .= ' ORDER BY created_at DESC LIMIT 100';

$stmt = $db->prepare($sql);
if ($params) {
    $stmt->bind_param($bindTypes, ...$params);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$back = 'users.php' . ($_GET ? '?' . http_build_query($_GET) : '');

$title = $onlyBanned ? 'Бан пользователей' : 'Поиск пользователя';
include '../header.php';
?>
<section class="section">
    <h1 class="page-title"><?= htmlspecialchars($title) ?></h1>

    <div class="admin-toolbar">
        <a class="back-link" href="index.php">← В админку</a>
    </div>

    <form class="filters__form filters__form--admin" method="get" action="users.php">
        <label class="field">
            <span class="field__label">Имя или почта</span>
            <input class="field__input" type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Например, tanker">
        </label>
        <label class="checkbox">
            <input type="checkbox" name="banned" value="1" <?= $onlyBanned ? 'checked' : '' ?>>
            <span>Только заблокированные</span>
        </label>
        <div class="filters__buttons">
            <button class="btn" type="submit">Найти</button>
            <a class="btn btn--outline" href="users.php">Сбросить</a>
        </div>
    </form>

    <?php if (empty($users)): ?>
        <p class="empty">Пользователи не найдены.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Имя</th>
                        <th>Почта</th>
                        <th>Роль</th>
                        <th>Регистрация</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $row): ?>
                        <tr>
                            <td><img class="avatar" src="../img/avatars/<?= htmlspecialchars($row['avatar']) ?>" alt=""></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= $row['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?></td>
                            <td><?= date('d.m.Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <?php if ($row['is_banned']): ?>
                                    <span class="badge badge--error">Заблокирован</span>
                                <?php else: ?>
                                    <span class="badge badge--success">Активен</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['role'] !== 'admin'): ?>
                                    <form method="post" action="users.php">
                                        <input type="hidden" name="user_id" value="<?= (int)$row['id'] ?>">
                                        <input type="hidden" name="back" value="<?= htmlspecialchars($back) ?>">
                                        <?php if ($row['is_banned']): ?>
                                            <input type="hidden" name="action" value="unban">
                                            <button class="btn btn--small btn--outline" type="submit">Разбанить</button>
                                        <?php else: ?>
                                            <input type="hidden" name="action" value="ban">
                                            <button class="btn btn--small btn--danger" type="submit">Забанить</button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php include '../footer.php'; ?>
