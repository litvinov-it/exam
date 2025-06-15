# Обновление статуса заявки

## Описание
Файл `update_request_status.php` отвечает за обновление статуса заявки администратором. Он включает проверку прав доступа, валидацию данных и обновление информации в базе данных.

## Основные функции
1. **Проверка доступа**:
   - Проверка авторизации администратора
   - Защита от несанкционированного доступа

2. **Обработка данных**:
   - Получение ID заявки и нового статуса
   - Проверка обязательных полей
   - Обработка причины отмены

3. **Обновление данных**:
   - Изменение статуса заявки
   - Сохранение причины отмены
   - Перенаправление после успешного обновления

## Полный код
```php
<?php
session_start();

// Подключаем утилиты
require_once '../scripts/utils.php';
require_once '../scripts/auth_utils.php';

// Проверяем доступ (только для админов)
checkAccess('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $reject_reason = null;

    if ($status === 'отменено') {
        $reject_reason = $_POST['reject_reason'] ?? null;
        if (empty($reject_reason)) {
            die('Причина отмены обязательна');
        }
    }

    if (!$request_id || !$status) {
        die('Неверные данные');
    }

    try {
        $db = new PDO('sqlite:../../backend/database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("UPDATE requests SET status = :status, reject_reason = :reject_reason WHERE id = :request_id");
        $stmt->execute([
            'status' => $status,
            'reject_reason' => $reject_reason,
            'request_id' => $request_id
        ]);

        header("Location: ../pages/admin/requests.php");
        exit;
    } catch (PDOException $e) {
        die("Ошибка базы данных: " . $e->getMessage());
    }
} else {
    die('Метод не поддерживается');
}
```

## Процесс обновления

### 1. Проверка доступа
```php
// Проверяем доступ (только для админов)
checkAccess('admin');
```
- Проверяет авторизацию пользователя
- Проверяет роль администратора
- Перенаправляет на страницу входа, если нет прав

### 2. Получение данных
```php
$request_id = $_POST['request_id'] ?? null;
$status = $_POST['status'] ?? null;
$reject_reason = null;

if ($status === 'отменено') {
    $reject_reason = $_POST['reject_reason'] ?? null;
    if (empty($reject_reason)) {
        die('Причина отмены обязательна');
    }
}
```
- Получаем ID заявки и новый статус
- Если статус "отменено", проверяем причину
- Проверяем обязательные поля

### 3. Обновление в базе данных
```php
$stmt = $db->prepare("UPDATE requests SET status = :status, reject_reason = :reject_reason WHERE id = :request_id");
$stmt->execute([
    'status' => $status,
    'reject_reason' => $reject_reason,
    'request_id' => $request_id
]);
```
- Используем подготовленный запрос
- Обновляем статус и причину отмены
- Привязываем параметры безопасно

## Обработка ошибок

### 1. Проверка метода
```php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Метод не поддерживается');
}
```

### 2. Проверка данных
```php
if (!$request_id || !$status) {
    die('Неверные данные');
}
```

### 3. Ошибки базы данных
```php
try {
    // ... код работы с БД ...
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
```

## Форма для обновления статуса
```html
<form action="scripts/update_request_status.php" method="post">
    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
    
    <select name="status" required>
        <option value="новое">Новое</option>
        <option value="в работе">В работе</option>
        <option value="выполнено">Выполнено</option>
        <option value="отменено">Отменено</option>
    </select>
    
    <div id="reject_reason_block" style="display: none;">
        <textarea name="reject_reason" placeholder="Причина отмены"></textarea>
    </div>
    
    <button type="submit">Обновить статус</button>
</form>

<script>
document.querySelector('select[name="status"]').addEventListener('change', function() {
    const rejectBlock = document.getElementById('reject_reason_block');
    rejectBlock.style.display = this.value === 'отменено' ? 'block' : 'none';
});
</script>
```

## Важные моменты
1. **Безопасность**:
   - Проверка прав доступа
   - Валидация входящих данных
   - Защита от SQL-инъекций

2. **Пользовательский опыт**:
   - Понятные сообщения об ошибках
   - Динамическое отображение полей
   - Перенаправление после успешного обновления

3. **Практика**:
   - Использование подготовленных запросов
   - Обработка всех возможных ошибок
   - Логирование важных действий 