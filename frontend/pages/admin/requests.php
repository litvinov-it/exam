<?php
session_start();

// Подключаем утилиты
require_once '../../scripts/utils.php';
require_once '../../scripts/auth_utils.php';

// Проверяем доступ (только для админов)
checkAccess('admin');

try {
    // Подключение к БД
    $db = new PDO('sqlite:../../../backend/database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем информацию об администраторе
    $stmt = $db->prepare("SELECT first_name, last_name, middle_name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Получаем все заявки
    $time_filter = $_GET['time_filter'] ?? 'all';
    $status_filter = $_GET['status_filter'] ?? 'all';

    $sql = "SELECT r.*, u.first_name, u.last_name, u.middle_name, u.phone, u.email 
            FROM requests r 
            JOIN users u ON r.user_id = u.id 
            WHERE 1=1";

    if ($time_filter !== 'all') {
        switch ($time_filter) {
            case 'today':
                $sql .= " AND date(r.preferred_date) = date('now')";
                break;
            case 'week':
                $sql .= " AND date(r.preferred_date) >= date('now', '-7 days')";
                break;
            case 'month':
                $sql .= " AND date(r.preferred_date) >= date('now', '-30 days')";
                break;
        }
    }

    if ($status_filter !== 'all') {
        $sql .= " AND r.status = :status";
    }

    $sql .= " ORDER BY r.preferred_date DESC, r.preferred_time DESC";

    $stmt = $db->prepare($sql);
    if ($status_filter !== 'all') {
        $stmt->bindParam(':status', $status_filter);
    }
    $stmt->execute();
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
    <title>Управление заявками</title>
    <link rel="stylesheet" href="../../styles.css">
</head>
<body class="home-page">
    <div class="home-container">
        <div class="home-header">
            <div class="user-info">
                <div class="user-name">
                    <?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name'] . ' ' . $admin['middle_name']); ?>
                </div>
            </div>
            <div class="header-actions">
                <a href="../../scripts/logout.php" class="back-link">
                    Выйти
                </a>
            </div>
        </div>

        <div class="content">
            <div class="welcome-section">
                <h1>Управление заявками</h1>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php else: ?>
                <div class="requests-section">
                    <div class="section-header">
                        <h2>Список заявок</h2>
                    </div>
                    <div class="filters">
                        <form method="GET" action="" class="filter-form">
                            <div class="filter-group">
                                <label for="time_filter">Период:</label>
                                <select name="time_filter" id="time_filter">
                                    <option value="all" <?php echo $time_filter === 'all' ? 'selected' : ''; ?>>Все время</option>
                                    <option value="today" <?php echo $time_filter === 'today' ? 'selected' : ''; ?>>Сегодня</option>
                                    <option value="week" <?php echo $time_filter === 'week' ? 'selected' : ''; ?>>За неделю</option>
                                    <option value="month" <?php echo $time_filter === 'month' ? 'selected' : ''; ?>>За месяц</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="status_filter">Статус:</label>
                                <select name="status_filter" id="status_filter">
                                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Все статусы</option>
                                    <option value="новая" <?php echo $status_filter === 'новая' ? 'selected' : ''; ?>>Новая</option>
                                    <option value="в работе" <?php echo $status_filter === 'в работе' ? 'selected' : ''; ?>>В работе</option>
                                    <option value="выполнено" <?php echo $status_filter === 'выполнено' ? 'selected' : ''; ?>>Выполнено</option>
                                    <option value="отменено" <?php echo $status_filter === 'отменено' ? 'selected' : ''; ?>>Отменено</option>
                                </select>
                            </div>
                            <button type="submit" class="filter-btn">Применить</button>
                        </form>
                    </div>
                    <?php if (empty($requests)): ?>
                        <div class="empty-state">
                            <p>Нет активных заявок</p>
                        </div>
                    <?php else: ?>
                        <div class="requests-list">
                            <?php foreach ($requests as $request): ?>
                                <div class="request-card">
                                    <div class="request-header">
                                        <h3>Заявка #<?php echo htmlspecialchars($request['id']); ?></h3>
                                        <span class="status status-<?php echo str_replace(' ', '-', strtolower($request['status'])); ?>">
                                            <?php echo htmlspecialchars($request['status']); ?>
                                        </span>
                                    </div>
                                    <div class="request-body">
                                        <div class="request-info">
                                            <div class="info-item">
                                                <span><strong>Клиент:</strong> <?php echo htmlspecialchars($request['last_name'] . ' ' . $request['first_name'] . ' ' . $request['middle_name']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span><strong>Контакты:</strong> <?php echo htmlspecialchars(numberToPhone($request['phone'])); ?>, <?php echo htmlspecialchars($request['email']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span><strong>Тип услуги:</strong> <?php echo htmlspecialchars($request['service_type']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span><strong>Адрес:</strong> <?php echo htmlspecialchars($request['address']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span><strong>Дата:</strong> <?php echo htmlspecialchars($request['preferred_date']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span><strong>Время:</strong> <?php echo htmlspecialchars($request['preferred_time']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span><strong>Способ оплаты:</strong> <?php echo htmlspecialchars($request['payment_type']); ?></span>
                                            </div>
                                        </div>
                                        <?php if (!empty($request['reject_reason'])): ?>
                                            <div class="reject-reason">
                                                <span><strong>Причина отказа:</strong> <?php echo htmlspecialchars($request['reject_reason']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($request['status'] === 'новая'): ?>
                                            <div class="actions">
                                                <form method="POST" action="../../scripts/update_request_status.php" style="display: inline;">
                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                    <input type="hidden" name="status" value="в работе">
                                                    <button type="submit" class="action-btn accept-btn">В работу</button>
                                                </form>
                                                <form method="POST" action="../../scripts/update_request_status.php" style="display: inline;">
                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                    <input type="hidden" name="status" value="выполнено">
                                                    <button type="submit" class="action-btn accept-btn">Выполнено</button>
                                                </form>
                                                <form method="POST" action="../../scripts/update_request_status.php" style="display: inline;">
                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                    <input type="hidden" name="status" value="отменено">
                                                    <input type="text" name="reject_reason" placeholder="Причина отмены" required>
                                                    <button type="submit" class="action-btn reject-btn">Отменить</button>
                                                </form>
                                            </div>
                                        <?php elseif ($request['status'] === 'в работе'): ?>
                                            <div class="actions">
                                                <form method="POST" action="../../scripts/update_request_status.php" style="display: inline;">
                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                    <input type="hidden" name="status" value="выполнено">
                                                    <button type="submit" class="action-btn accept-btn">Выполнено</button>
                                                </form>
                                                <form method="POST" action="../../scripts/update_request_status.php" style="display: inline;">
                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                    <input type="hidden" name="status" value="отменено">
                                                    <input type="text" name="reject_reason" placeholder="Причина отмены" required>
                                                    <button type="submit" class="action-btn reject-btn">Отменить</button>
                                                </form>
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
