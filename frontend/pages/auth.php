<?php
session_start();

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

$error = $_GET['error'] ?? '';
$errorMessage = '';

switch ($error) {
    case 'empty_fields':
        $errorMessage = 'Пожалуйста, заполните все поля';
        break;
    case 'invalid_credentials':
        $errorMessage = 'Неверный логин или пароль';
        break;
    case 'db_error':
        $errorMessage = 'Ошибка базы данных';
        break;
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Вход в систему</h1>
                <p class="auth-subtitle">Добро пожаловать! Пожалуйста, войдите в свой аккаунт</p>
            </div>

            <form action="../scripts/auth.php" method="post">
                <div class="form-group">
                    <label for="login">Логин</label>
                    <div class="input-group">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <input type="text" id="login" name="login" value="" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <div class="input-group">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" id="password" name="password" value="" required>
                    </div>
                </div>

                <?php if ($errorMessage): ?>
                    <div class="error-message">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>

                <button type="submit">
                    <span>Войти</span>
                    <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>

                <div class="auth-footer">
                    <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>