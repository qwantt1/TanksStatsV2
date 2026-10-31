<?php
include 'config.php';

if ($user) {
    redirect('index.php');
}

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = 'Введите имя или почту и пароль';
    } else {
        $stmt = $db->prepare('SELECT id, username, password_hash, is_banned FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $login, $login);
        $stmt->execute();
        $found = $stmt->get_result()->fetch_assoc();

        if (!$found || !password_verify($password, $found['password_hash'])) {
            $error = 'Неверное имя, почта или пароль';
        } elseif ($found['is_banned']) {
            $error = 'Ваш аккаунт заблокирован администратором';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $found['id'];
            flash('Вы вошли как ' . $found['username']);
            redirect('index.php');
        }
    }
}

$title = 'Вход';
include 'header.php';
?>
<section class="auth">
    <h1 class="auth__title">Вход</h1>
    <p class="auth__subtitle"><?= htmlspecialchars($settings['welcome_text'] ?? '') ?></p>

    <div class="auth-card">
        <?php if ($error): ?>
            <p class="form-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form class="auth-card__form" method="post" action="login.php">
            <label class="field">
                <span class="field__label">Имя или почта</span>
                <input class="field__input" type="text" name="login" value="<?= htmlspecialchars($login) ?>" autocomplete="username" required>
            </label>
            <label class="field">
                <span class="field__label">Пароль</span>
                <input class="field__input" type="password" name="password" autocomplete="current-password" required>
            </label>
            <button class="btn btn--big" type="submit">Войти</button>
        </form>
    </div>

    <p class="auth__switch">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
</section>
<?php include 'footer.php'; ?>
