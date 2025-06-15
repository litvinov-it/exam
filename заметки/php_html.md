# Взаимодействие PHP и HTML

## Основные принципы

### 1. Структура страницы
```php
<?php
// 1. Начало сессии
session_start();

// 2. Подключение утилит
require_once '../scripts/utils.php';
require_once '../scripts/auth_utils.php';

// 3. Проверка доступа
checkAccess('user');

// 4. Обработка данных
$errors = [];
$success = false;

// 5. Логика работы с БД
try {
    $db = new PDO('sqlite:../../backend/database.db');
    // ... работа с БД ...
} catch (PDOException $e) {
    $errors['db'] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Мета-теги и стили -->
</head>
<body>
    <!-- HTML-разметка -->
</body>
</html>
```

### 2. Вывод данных
```php
<!-- Простой вывод -->
<?php echo htmlspecialchars($user['name']); ?>

<!-- Условный вывод -->
<?php if ($success): ?>
    <div class="success">Успешно!</div>
<?php else: ?>
    <div class="error">Ошибка!</div>
<?php endif; ?>

<!-- Циклы -->
<?php foreach ($items as $item): ?>
    <div class="item">
        <?php echo htmlspecialchars($item['name']); ?>
    </div>
<?php endforeach; ?>
```

## Обработка форм

### 1. Отправка формы
```html
<form method="POST" action="script.php">
    <input type="text" name="field_name" value="<?php echo htmlspecialchars($data['field_name'] ?? ''); ?>">
    <button type="submit">Отправить</button>
</form>
```

### 2. Обработка данных
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'field_name' => trim($_POST['field_name'] ?? ''),
        // ... другие поля ...
    ];
    
    // Валидация
    if (empty($data['field_name'])) {
        $errors['field_name'] = 'Поле обязательно';
    }
    
    // Сохранение в БД
    if (empty($errors)) {
        // ... сохранение ...
    }
}
```

### 3. Отображение ошибок
```php
<div class="form-group">
    <input type="text" 
           name="field_name" 
           class="<?php echo !empty($errors['field_name']) ? 'is-invalid' : ''; ?>"
           value="<?php echo htmlspecialchars($data['field_name'] ?? ''); ?>">
    
    <?php if (!empty($errors['field_name'])): ?>
        <div class="error-text">
            <?php echo $errors['field_name']; ?>
        </div>
    <?php endif; ?>
</div>
```

## Работа с сессией

### 1. Сохранение данных
```php
// Сохранение ошибок
$_SESSION['errors'] = $errors;

// Сохранение введенных данных
$_SESSION['form_data'] = $data;
```

### 2. Получение данных
```php
// Получение ошибок
$errors = $_SESSION['errors'] ?? [];

// Получение данных формы
$data = $_SESSION['form_data'] ?? [];

// Очистка сессии
unset($_SESSION['errors']);
unset($_SESSION['form_data']);
```

## Безопасность

### 1. Защита от XSS
```php
// Всегда используйте htmlspecialchars при выводе
echo htmlspecialchars($user['name']);

// В формах
<input value="<?php echo htmlspecialchars($data['name'] ?? ''); ?>">
```

### 2. Защита от SQL-инъекций
```php
// Используйте подготовленные запросы
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
```

## Примеры из проекта

### 1. Форма регистрации
```php
<form method="POST" action="../scripts/register.php">
    <div class="form-group">
        <label for="login">Логин</label>
        <input type="text" 
               name="login" 
               value="<?php echo htmlspecialchars($data['login'] ?? ''); ?>"
               class="<?php echo !empty($errors['login']) ? 'is-invalid' : ''; ?>">
        
        <?php if (!empty($errors['login'])): ?>
            <div class="error-text"><?php echo $errors['login']; ?></div>
        <?php endif; ?>
    </div>
</form>
```

### 2. Список заявок
```php
<div class="requests-list">
    <?php foreach ($requests as $request): ?>
        <div class="request-card">
            <h3>Заявка #<?php echo htmlspecialchars($request['id']); ?></h3>
            <div class="status">
                <?php echo htmlspecialchars($request['status']); ?>
            </div>
            <?php if (!empty($request['reject_reason'])): ?>
                <div class="reject-reason">
                    <?php echo htmlspecialchars($request['reject_reason']); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
```

### 3. Фильтрация данных
```php
<div class="filters">
    <form method="GET" action="">
        <select name="status_filter">
            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>
                Все статусы
            </option>
            <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>
                Новые
            </option>
        </select>
        <button type="submit">Применить</button>
    </form>
</div>
```

## JavaScript взаимодействие

### 1. Маска для телефона
```php
<input type="text" id="phone" name="phone">
<script>
document.getElementById('phone').addEventListener('input', function(e) {
    let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
    e.target.value = !x[2] ? x[1] : '+7(' + x[2] + (x[3] ? ')-' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
});
</script>
```

### 2. Динамическое отображение полей
```php
<div class="form-group">
    <input type="checkbox" id="other_service" name="other_service">
    <div id="other_service_description" style="display: none;">
        <textarea name="other_service_description"></textarea>
    </div>
</div>

<script>
document.getElementById('other_service').addEventListener('change', function() {
    const otherServiceDiv = document.getElementById('other_service_description');
    otherServiceDiv.style.display = this.checked ? 'block' : 'none';
});
</script>
```

## Важные моменты
1. **Безопасность**:
   - Всегда используйте `htmlspecialchars` при выводе данных
   - Используйте подготовленные запросы для работы с БД
   - Валидируйте все входящие данные

2. **Структура**:
   - Разделяйте логику и представление
   - Используйте утилиты для повторяющихся операций
   - Храните данные в сессии для сохранения состояния

3. **Пользовательский опыт**:
   - Сохраняйте введенные данные при ошибках
   - Показывайте понятные сообщения об ошибках
   - Используйте JavaScript для улучшения интерфейса 

## Логика обработки запросов

### 1. Структура обработки
```php
// 1. Начало обработки (например, в register.php)
session_start();
require_once '../scripts/validation.php';
require_once '../scripts/check_unique.php';

// 2. Получение данных из сессии
$errors = $_SESSION['register_errors'] ?? [];
$data = $_SESSION['register_data'] ?? [];

// 3. Очистка сессии
unset($_SESSION['register_errors']);
unset($_SESSION['register_data']);

// 4. Обработка POST-запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Подготовка данных
    $data = [
        'login' => trim($_POST['login'] ?? ''),
        'password' => $_POST['password'] ?? '',
        // ... другие поля
    ];

    // Валидация
    $validationErrors = validateRegistrationData($data);
    if (!empty($validationErrors)) {
        $errors = array_merge($errors, $validationErrors);
    }

    // Проверка уникальности
    $uniqueErrors = checkUniqueData($db, $data);
    if (!empty($uniqueErrors)) {
        $errors = array_merge($errors, $uniqueErrors);
    }

    // Если нет ошибок - сохраняем
    if (empty($errors)) {
        // Сохранение в БД
        $stmt = $db->prepare("INSERT INTO users ...");
        $stmt->execute([...]);
        
        // Перенаправление
        header('Location: success.php');
        exit;
    } else {
        // Сохраняем ошибки и данные в сессию
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_data'] = $data;
        
        // Возвращаем на форму
        header('Location: register.php');
        exit;
    }
}
```

### 2. Цепочка обработки запроса
1. **Пользователь отправляет форму**:
   ```html
   <form method="POST" action="../scripts/register.php">
       <input type="text" name="login">
       <button type="submit">Отправить</button>
   </form>
   ```

2. **Скрипт обработки (register.php)**:
   ```php
   // Получаем данные
   $login = $_POST['login'];
   
   // Валидируем
   if (empty($login)) {
       $errors['login'] = 'Логин обязателен';
   }
   
   // Если есть ошибки
   if (!empty($errors)) {
       $_SESSION['errors'] = $errors;
       header('Location: form.php');
       exit;
   }
   
   // Если всё хорошо
   // Сохраняем в БД
   // Перенаправляем на успех
   ```

3. **Возврат на форму с ошибками**:
   ```php
   // В form.php
   $errors = $_SESSION['errors'] ?? [];
   
   // В HTML
   <input class="<?php echo !empty($errors['login']) ? 'is-invalid' : ''; ?>">
   <?php if (!empty($errors['login'])): ?>
       <div class="error"><?php echo $errors['login']; ?></div>
   <?php endif; ?>
   ```

### 3. Примеры обработки в проекте

#### Регистрация
```php
// 1. Пользователь заполняет форму (register.php)
// 2. Данные отправляются в scripts/register.php
// 3. Скрипт проверяет данные:
   - Валидация (validation.php)
   - Уникальность (check_unique.php)
// 4. Если есть ошибки:
   - Сохраняются в сессию
   - Пользователь возвращается на форму
// 5. Если всё хорошо:
   - Данные сохраняются в БД
   - Пользователь авторизуется
   - Перенаправляется на главную
```

#### Создание заявки
```php
// 1. Пользователь заполняет форму (add_request.php)
// 2. Данные отправляются в scripts/add_request.php
// 3. Скрипт проверяет:
   - Доступ пользователя
   - Валидацию данных
// 4. Если есть ошибки:
   - Показываются на форме
   - Данные сохраняются
// 5. Если всё хорошо:
   - Заявка создается
   - Пользователь перенаправляется на список заявок
```

#### Обновление статуса
```php
// 1. Админ нажимает кнопку (requests.php)
// 2. Данные отправляются в scripts/update_request_status.php
// 3. Скрипт проверяет:
   - Права администратора
   - Валидность статуса
// 4. Если есть ошибки:
   - Показывается сообщение
// 5. Если всё хорошо:
   - Статус обновляется
   - Админ остается на странице
```

### 4. Важные моменты в обработке

1. **Безопасность**:
   - Всегда проверяйте права доступа
   - Валидируйте все входящие данные
   - Используйте подготовленные запросы

2. **Пользовательский опыт**:
   - Сохраняйте введенные данные при ошибках
   - Показывайте понятные сообщения
   - Перенаправляйте на нужные страницы

3. **Обработка ошибок**:
   - Логируйте ошибки
   - Показывайте пользователю только нужную информацию
   - Обрабатывайте все возможные случаи

4. **Сессии**:
   - Очищайте данные после использования
   - Не храните чувствительную информацию
   - Используйте для сохранения состояния формы 