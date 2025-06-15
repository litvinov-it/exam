<?php
session_start();

// Подключаем утилиты
require_once '../scripts/utils.php';
require_once '../scripts/auth_utils.php';

// Проверяем доступ (только для обычных пользователей)
checkAccess('user');

try {
    // Подключение к БД
    $db = new PDO('sqlite:../../backend/database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем данные пользователя
    $stmt = $db->prepare("SELECT first_name, last_name, middle_name, phone, email, role FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Получаем заявки пользователя
    $stmt = $db->prepare("SELECT * FROM requests WHERE user_id = :user_id ORDER BY preferred_date DESC, preferred_time DESC");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Ошибка базы данных: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body class="home-page">
    <div class="home-container">
        <div class="home-header">
            <div class="user-info">
                <div class="user-name">
                    <?php echo htmlspecialchars($user['last_name'] . ' ' . $user['first_name'] . ' ' . $user['middle_name']); ?>
                </div>
            </div>
            <div class="header-actions">
                <a href="add_request.php" class="btn btn-primary">
                    Добавить заявку
                </a>
                <a href="../scripts/logout.php" class="back-link">
                    Выйти
                </a>
            </div>
        </div>
        <div class="content">
            <div class="welcome-section">
                <h1>Добро пожаловать!</h1>
                <p class="text-muted">Здесь вы можете управлять своими заявками на услуги</p>
            </div>
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php else: ?>
                <div class="requests-section">
                    <div class="section-header">
                        <h2>Мои заявки</h2>
                    </div>
                    <?php if (empty($requests)): ?>
                        <div class="empty-state">
                            <p>У вас пока нет заявок</p>
                        </div>
                    <?php else: ?>
                        <div class="requests-list">
                            <?php foreach ($requests as $request): ?>
                                <div class="request-card">
                                    <div class="request-header">
                                        <h3>Заявка #<?php echo htmlspecialchars($request['id']); ?></h3>
                                        <span class="status status-<?php echo strtolower($request['status']); ?>">
                                            <?php echo htmlspecialchars($request['status']); ?>
                                        </span>
                                    </div>
                                    <div class="request-body">
                                        <div class="request-info">
                                            <div class="info-item">
                                                <span><?php echo htmlspecialchars($request['service_type']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span><?php echo htmlspecialchars($request['address']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span><?php echo htmlspecialchars($request['preferred_date']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span><?php echo htmlspecialchars($request['preferred_time']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span><?php echo htmlspecialchars($request['payment_type']); ?></span>
                                            </div>
                                        </div>
                                        <?php if (!empty($request['reject_reason'])): ?>
                                            <div class="reject-reason">
                                                <span><?php echo htmlspecialchars($request['reject_reason']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
