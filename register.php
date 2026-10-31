<?php
include 'config.php';

if ($user) {
    redirect('index.php');
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ0-9_]{3,30}$/u', $username)) {
        $errors[] = 'Имя: от 3 до 30 символов, только буквы, цифры и _';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
        $errors[] = 'Введите корректную почту';
    }
    if (mb_strlen($password) < 6) {
        $errors[] = 'Пароль должен быть не короче 6 символов';
    }
    if ($password !== $password2) {
        $errors[] = 'Пароли не совпадают';
    }
    if (!isset($_POST['agree'])) {
        $errors[] = 'Нужно согласие на обработку персональных данных';
    }

    if (!$errors) {
        $stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $errors[] = 'Это имя уже занято';
        }

        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $errors[] = 'Эта почта уже зарегистрирована';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $avatar = 'avatar' . random_int(1, 8) . '.svg';

        $stmt = $db->prepare('INSERT INTO users (username, email, password_hash, avatar) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $username, $email, $hash, $avatar);
        $stmt->execute();

        session_regenerate_id(true);
        $_SESSION['user_id'] = $db->insert_id;
        flash('Аккаунт создан. Аватарка назначена автоматически');
        redirect('index.php');
    }
}

$title = 'Регистрация';
include 'header.php';
?>
<section class="auth">
    <h1 class="auth__title">Регистрация</h1>
    <p class="auth__subtitle">Аватарка назначается автоматически после регистрации.</p>

    <div class="auth-card">
        <?php if ($errors): ?>
            <ul class="form-error">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form class="auth-card__form" method="post" action="register.php">
            <label class="field">
                <span class="field__label">Имя пользователя</span>
                <input class="field__input" type="text" name="username" value="<?= htmlspecialchars($username) ?>" autocomplete="username" required>
            </label>
            <label class="field">
                <span class="field__label">Почта</span>
                <input class="field__input" type="email" name="email" value="<?= htmlspecialchars($email) ?>" autocomplete="email" required>
            </label>
            <label class="field">
                <span class="field__label">Пароль, не короче 6 символов</span>
                <input class="field__input" type="password" name="password" autocomplete="new-password" required>
            </label>
            <label class="field">
                <span class="field__label">Повтор пароля</span>
                <input class="field__input" type="password" name="password2" autocomplete="new-password" required>
            </label>
            <label class="checkbox">
                <input type="checkbox" name="agree" value="1" <?= isset($_POST['agree']) ? 'checked' : '' ?>>
                <span>Согласен на обработку персональных данных</span>
            </label>
            <button class="btn btn--big" type="submit">Зарегистрироваться</button>
        </form>
    </div>

    <p class="auth__switch">Уже есть аккаунт? <a href="login.php">Войти</a></p>
</section>
<?php include 'footer.php'; ?>
