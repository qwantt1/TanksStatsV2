<?php
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

$db = new mysqli('localhost', 'root', '', 'tanksstats');
$db->set_charset('utf8mb4');

$base = basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'admin' ? '../' : '';
$page = basename($_SERVER['SCRIPT_NAME']);

$settings = [];
$result = $db->query('SELECT name, value FROM settings');
while ($row = $result->fetch_assoc()) {
    $settings[$row['name']] = $row['value'];
}

$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT id, username, email, avatar, role, is_banned FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || $user['is_banned']) {
        unset($_SESSION['user_id']);
        $user = null;
    }
}
$isAdmin = $user && $user['role'] === 'admin';

if (($settings['maintenance'] ?? '0') === '1' && !$isAdmin && !in_array($page, ['login.php', 'logout.php'])) {
    http_response_code(503);
    include __DIR__ . '/maintenance.php';
    exit;
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function flash($text, $type = 'success')
{
    $_SESSION['flash'] = ['text' => $text, 'type' => $type];
}

function back_url($fallback = 'index.php')
{
    $back = $_POST['back'] ?? '';
    if (is_string($back) && preg_match('/^[a-z_]+\.php(\?[\w=&%.+-]*)?$/i', $back)) {
        return $back;
    }
    return $fallback;
}

function include_login()
{
    global $user, $base;
    if (!$user) {
        flash('Сначала войдите в аккаунт', 'error');
        redirect($base . 'login.php');
    }
}

function include_admin()
{
    global $isAdmin, $base;
    include_login();
    if (!$isAdmin) {
        flash('Этот раздел только для администраторов', 'error');
        redirect($base . 'index.php');
    }
}

function tank_image($tank)
{
    global $base;
    return $base . ($tank['image'] ? 'img/tanks/' . $tank['image'] : 'img/no-tank.svg');
}

function dpm($tank)
{
    return $tank['reload'] > 0 ? (int)round($tank['damage'] * 60 / $tank['reload']) : 0;
}
