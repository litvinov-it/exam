<?php
session_start();

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

require_once '../scripts/validation.php';
require_once '../scripts/check_unique.php';
require_once '../scripts/utils.php';
require_once '../scripts/auth_utils.php';

// Получаем данные из сессии
$errors = $_SESSION['register_errors'] ?? [];
$data = $_SESSION['register_data'] ?? [];

// Очищаем данные из сессии
unset($_SESSION['register_errors']);
unset($_SESSION['register_data']);

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Инициализация подключения к базе данных
        $db = new PDO('sqlite:../../backend/database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Подготовка данных
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'middle_name' => trim($_POST['middle_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'login' => trim($_POST['login'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? ''
        ];

        // Проверка уникальности данных
        $uniqueErrors = checkUniqueData($db, $data);
        if (!empty($uniqueErrors)) {
            $errors = array_merge($errors, $uniqueErrors);
        }

        // Валидация данных
        $validationErrors = validateRegistrationData($data);
        if (!empty($validationErrors)) {
            $errors = array_merge($errors, $validationErrors);
        }

        // Если нет ошибок, регистрируем пользователя
        if (empty($errors)) {
            $stmt = $db->prepare("INSERT INTO users (first_name, last_name, middle_name, phone, email, login, password, role) 
                                VALUES (:first_name, :last_name, :middle_name, :phone, :email, :login, :password, 'user')");
            
            $stmt->execute([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'login' => $data['login'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ]);

            $userId = $db->lastInsertId();
            authorizeAndRedirect($userId, 'user');
        }
    } catch (PDOException $e) {
        $errors['db'] = 'Ошибка базы данных: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Регистрация</h1>
                <p class="auth-subtitle">Создайте новый аккаунт для доступа к системе</p>
            </div>

            <?php if ($success): ?>
                <div class="success-message">
                    <svg class="success-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    Регистрация успешно завершена! <a href="index.php">Перейти на главную</a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors['db'])): ?>
                    <div class="error-message">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <?php echo $errors['db']; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="../scripts/register.php">
                    <div class="form-group">
                        <label for="last_name">Фамилия</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($data['last_name'] ?? ''); ?>" <?php echo !empty($errors['last_name']) ? 'class="is-invalid"' : ''; ?>>
                        </div>
                        <?php if (!empty($errors['last_name'])): ?>
                            <div class="error-text"><?php echo $errors['last_name']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="first_name">Имя</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($data['first_name'] ?? ''); ?>" <?php echo !empty($errors['first_name']) ? 'class="is-invalid"' : ''; ?>>
                        </div>
                        <?php if (!empty($errors['first_name'])): ?>
                            <div class="error-text"><?php echo $errors['first_name']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="middle_name">Отчество</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <input type="text" name="middle_name" id="middle_name" value="<?php echo htmlspecialchars($data['middle_name'] ?? ''); ?>" <?php echo !empty($errors['middle_name']) ? 'class="is-invalid"' : ''; ?>>
                        </div>
                        <?php if (!empty($errors['middle_name'])): ?>
                            <div class="error-text"><?php echo $errors['middle_name']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="phone">Телефон</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <input type="text" name="phone" id="phone" placeholder="+7(XXX)-XXX-XX-XX" value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>" <?php echo (!empty($errors['phone']) || !empty($errors['unique_phone'])) ? 'class="is-invalid"' : ''; ?>>
                        </div>
                        <?php if (!empty($errors['phone'])): ?>
                            <div class="error-text"><?php echo $errors['phone']; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errors['unique_phone'])): ?>
                            <div class="error-text"><?php echo $errors['unique_phone']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" <?php echo (!empty($errors['email']) || !empty($errors['unique_email'])) ? 'class="is-invalid"' : ''; ?>>
                        </div>
                        <?php if (!empty($errors['email'])): ?>
                            <div class="error-text"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errors['unique_email'])): ?>
                            <div class="error-text"><?php echo $errors['unique_email']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="login">Логин</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <input type="text" name="login" id="login" value="<?php echo htmlspecialchars($data['login'] ?? ''); ?>" <?php echo (!empty($errors['login']) || !empty($errors['unique_login'])) ? 'class="is-invalid"' : ''; ?>>
                        </div>
                        <?php if (!empty($errors['login'])): ?>
                            <div class="error-text"><?php echo $errors['login']; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errors['unique_login'])): ?>
                            <div class="error-text"><?php echo $errors['unique_login']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input type="password" name="password" id="password" value="<?php echo htmlspecialchars($data['password'] ?? ''); ?>" <?php echo !empty($errors['password']) ? 'class="is-invalid"' : ''; ?>>
                        </div>
                        <?php if (!empty($errors['password'])): ?>
                            <div class="error-text"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Подтверждение пароля</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input type="password" name="password_confirm" id="password_confirm" value="<?php echo htmlspecialchars($data['password_confirm'] ?? ''); ?>" <?php echo !empty($errors['password_confirm']) ? 'class="is-invalid"' : ''; ?>>
                        </div>
                        <?php if (!empty($errors['password_confirm'])): ?>
                            <div class="error-text"><?php echo $errors['password_confirm']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="auth-footer">
                        <a href="auth.php" class="back-link">
                            <svg class="back-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            Назад
                        </a>
                        <button type="submit">
                            <span>Зарегистрироваться</span>
                            <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Маска для телефона
        document.getElementById('phone').addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
            e.target.value = !x[2] ? x[1] : '+7(' + x[2] + (x[3] ? ')-' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
        });
    </script>
</body>
</html>