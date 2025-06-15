<?php
session_start();

// Подключаем утилиты
require_once '../scripts/utils.php';
require_once '../scripts/auth_utils.php';

// Проверяем доступ (только для обычных пользователей)
checkAccess('user');

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Подключение к БД
        $db = new PDO('sqlite:../../backend/database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Получаем данные пользователя для контактной информации
        $stmt = $db->prepare("SELECT phone, email FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Подготовка данных
        $data = [
            'user_id' => $_SESSION['user_id'],
            'address' => trim($_POST['address'] ?? ''),
            'contact_info' => trim($_POST['contact_info'] ?? ''),
            'service_type' => isset($_POST['other_service']) ? trim($_POST['other_service_description'] ?? '') : trim($_POST['service_type'] ?? ''),
            'preferred_date' => trim($_POST['preferred_date'] ?? ''),
            'preferred_time' => trim($_POST['preferred_time'] ?? ''),
            'payment_type' => trim($_POST['payment_type'] ?? ''),
            'status' => 'новая заявка'
        ];

        // Валидация
        if (empty($data['address'])) {
            $errors['address'] = 'Укажите адрес';
        }

        if (empty($data['contact_info'])) {
            $errors['contact_info'] = 'Укажите контактный телефон';
        } elseif (!preg_match('/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/', $data['contact_info'])) {
            $errors['contact_info'] = 'Телефон должен быть в формате +7(XXX)-XXX-XX-XX';
        }

        if (empty($data['service_type'])) {
            $errors['service_type'] = 'Выберите тип услуги или укажите другую услугу';
        }

        if (empty($data['preferred_date'])) {
            $errors['preferred_date'] = 'Укажите предпочтительную дату';
        } else {
            $date = strtotime($data['preferred_date']);
            $today = strtotime(date('Y-m-d'));
            if ($date < $today) {
                $errors['preferred_date'] = 'Дата не может быть в прошлом';
            }
        }

        if (empty($data['preferred_time'])) {
            $errors['preferred_time'] = 'Укажите предпочтительное время';
        }

        if (empty($data['payment_type'])) {
            $errors['payment_type'] = 'Выберите способ оплаты';
        }

        // Если нет ошибок, добавляем заявку
        if (empty($errors)) {
            $stmt = $db->prepare("INSERT INTO requests (user_id, address, contact_info, service_type, preferred_date, preferred_time, payment_type, status) 
                                VALUES (:user_id, :address, :contact_info, :service_type, :preferred_date, :preferred_time, :payment_type, :status)");
            $stmt->execute($data);
            $success = true;
        }
    } catch (PDOException $e) {
        $errors['db'] = "Ошибка базы данных: " . $e->getMessage();
    }
}

if ($success) {
    header("Location: home.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новая заявка</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Новая заявка</h1>
                <p class="auth-subtitle">Заполните форму для создания новой заявки</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    Заявка успешно создана! <a href="home.php">Вернуться к списку заявок</a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors['db'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['db']; ?></div>
                <?php endif; ?>

                <form method="POST" action="" id="requestForm">
                    <div class="form-group">
                        <label for="address">Адрес:</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <input type="text" name="address" id="address" placeholder="Укажите полный адрес" class="<?php echo !empty($errors['address']) ? 'is-invalid' : ''; ?>">
                        </div>
                        <?php if (!empty($errors['address'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['address']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="contact_info">Контактный телефон:</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <input type="text" name="contact_info" id="contact_info" placeholder="+7(XXX)-XXX-XX-XX" class="<?php echo !empty($errors['contact_info']) ? 'is-invalid' : ''; ?>">
                        </div>
                        <?php if (!empty($errors['contact_info'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['contact_info']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Тип услуги:</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <select name="service_type" id="service_type" class="<?php echo !empty($errors['service_type']) ? 'is-invalid' : ''; ?>">
                                <option value="">Выберите тип услуги</option>
                                <option value="Общий клининг">Общий клининг</option>
                                <option value="Генеральная уборка">Генеральная уборка</option>
                                <option value="Послестроительная уборка">Послестроительная уборка</option>
                                <option value="Химчистка ковров и мебели">Химчистка ковров и мебели</option>
                            </select>
                        </div>
                        <?php if (!empty($errors['service_type'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['service_type']; ?></div>
                        <?php endif; ?>

                        <div class="form-group" style="display: flex; align-items: center; margin-top: 10px;">
                            <input type="checkbox" id="other_service" name="other_service" style="margin-right: 8px;">
                            <label for="other_service" style="margin: 0;">Иная услуга</label>
                        </div>

                        <div class="form-group" id="other_service_description" style="display: none; margin-top: 10px;">
                            <div class="input-group">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <textarea name="other_service_description" placeholder="Опишите, какая услуга вам необходима" style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; resize: vertical;"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="preferred_date">Предпочтительная дата:</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <input type="date" name="preferred_date" id="preferred_date" min="<?php echo date('Y-m-d'); ?>" class="<?php echo !empty($errors['preferred_date']) ? 'is-invalid' : ''; ?>">
                        </div>
                        <?php if (!empty($errors['preferred_date'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['preferred_date']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="preferred_time">Предпочтительное время:</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <input type="time" name="preferred_time" id="preferred_time" class="<?php echo !empty($errors['preferred_time']) ? 'is-invalid' : ''; ?>">
                        </div>
                        <?php if (!empty($errors['preferred_time'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['preferred_time']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="payment_type">Способ оплаты:</label>
                        <div class="input-group">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <line x1="2" y1="10" x2="22" y2="10"/>
                            </svg>
                            <select name="payment_type" id="payment_type" class="<?php echo !empty($errors['payment_type']) ? 'is-invalid' : ''; ?>">
                                <option value="">Выберите способ оплаты</option>
                                <option value="Наличные">Наличные</option>
                                <option value="Банковская карта">Банковская карта</option>
                            </select>
                        </div>
                        <?php if (!empty($errors['payment_type'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['payment_type']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="auth-footer">
                        <a href="home.php" class="back-link">
                            <svg class="back-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            Назад
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <span>Создать заявку</span>
                            <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>
                </form>

                <script>
                    document.getElementById('other_service').addEventListener('change', function() {
                        const otherServiceDiv = document.getElementById('other_service_description');
                        const serviceSelect = document.getElementById('service_type');
                        
                        if (this.checked) {
                            otherServiceDiv.style.display = 'block';
                            serviceSelect.disabled = true;
                            serviceSelect.value = '';
                        } else {
                            otherServiceDiv.style.display = 'none';
                            serviceSelect.disabled = false;
                        }
                    });

                    // Маска для телефона
                    document.getElementById('contact_info').addEventListener('input', function(e) {
                        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                        e.target.value = !x[2] ? x[1] : '+7(' + x[2] + (x[3] ? ')-' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
