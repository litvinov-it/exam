# Регистрация пользователей

## Описание

Файл `register.php` отвечает за обработку регистрации новых пользователей в системе. Он включает в себя валидацию данных, проверку уникальности и сохранение информации в базе данных.

## Основные функции

1. **Проверка авторизации**:

   - Если пользователь уже авторизован, перенаправляет на главную
   - Защита от повторной регистрации

2. **Обработка данных**:

   - Подготовка и очистка входящих данных
   - Валидация всех полей
   - Проверка уникальности логина, email и телефона

3. **Сохранение данных**:
   - Хеширование пароля
   - Сохранение в базу данных
   - Авторизация после успешной регистрации

## Полный код

```php
<?php
session_start();

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['user_id'])) {
    header('Location: ../pages/home.php');
    exit;
}

require_once 'validation.php';
require_once 'check_unique.php';
require_once 'utils.php';
require_once 'auth_utils.php';

$errors = [];
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

// Если есть ошибки или это GET запрос, возвращаем на страницу регистрации
if (!empty($errors) || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_data'] = $data ?? [];
    header('Location: ../pages/register.php');
    exit;
}
```

## Процесс регистрации

### 1. Подготовка данных

```php
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
```

### 2. Проверки

1. **Уникальность данных**:

   - Проверка существующего логина
   - Проверка существующего email
   - Проверка существующего телефона

2. **Валидация полей**:
   - Имя (только русские буквы)
   - Телефон (формат +7(XXX)-XXX-XX-XX)
   - Email (стандартный формат)
   - Логин (латинские буквы и цифры)
   - Пароль (минимум 6 символов)

### 3. Сохранение данных

```php
$stmt = $db->prepare("INSERT INTO users (...) VALUES (...)");
$stmt->execute([
    // ... данные пользователя ...
    'password' => password_hash($data['password'], PASSWORD_DEFAULT)
]);
```

## Обработка ошибок

### 1. Ошибки валидации

```php
$validationErrors = validateRegistrationData($data);
if (!empty($validationErrors)) {
    $errors = array_merge($errors, $validationErrors);
}
```

### 2. Ошибки уникальности

```php
$uniqueErrors = checkUniqueData($db, $data);
if (!empty($uniqueErrors)) {
    $errors = array_merge($errors, $uniqueErrors);
}
```

### 3. Ошибки базы данных

```php
try {
    // ... код работы с БД ...
} catch (PDOException $e) {
    $errors['db'] = 'Ошибка базы данных: ' . $e->getMessage();
}
```

## Перенаправления

### 1. Успешная регистрация

```php
$userId = $db->lastInsertId();
authorizeAndRedirect($userId, 'user');
```

### 2. Ошибки или GET запрос

```php
$_SESSION['register_errors'] = $errors;
$_SESSION['register_data'] = $data ?? [];
header('Location: ../pages/register.php');
exit;
```

## Важные моменты

1. **Безопасность**:

   - Хеширование паролей
   - Валидация всех входящих данных
   - Защита от SQL-инъекций через PDO

2. **Пользовательский опыт**:

   - Сохранение введенных данных при ошибках
   - Понятные сообщения об ошибках
   - Автоматическая авторизация после регистрации

3. **Практика**:
   - Использование подготовленных запросов
   - Обработка всех возможных ошибок
   - Очистка данных перед сохранением

### Настройка режима обработки ошибок PDO

```php
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

Эта строка настраивает, как PDO будет обрабатывать ошибки при работе с базой данных:

1. **PDO::ATTR_ERRMODE** - атрибут, определяющий режим обработки ошибок
2. **PDO::ERRMODE_EXCEPTION** - режим, при котором:
   - Все ошибки БД преобразуются в исключения
   - Можно использовать try-catch для обработки ошибок
   - Удобно для отладки и логирования

#### Другие режимы обработки ошибок:

- **PDO::ERRMODE_SILENT** (по умолчанию):

  ```php
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
  // Ошибки нужно проверять вручную
  if ($db->errorCode() !== '00000') {
      $error = $db->errorInfo();
  }
  ```

- **PDO::ERRMODE_WARNING**:
  ```php
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  // Ошибки вызывают PHP-предупреждения
  ```

#### Пример обработки ошибок с исключениями:

```php
try {
    $db = new PDO('sqlite:../../backend/database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Если произойдет ошибка, будет выброшено исключение
    $stmt = $db->prepare("SELECT * FROM non_existent_table");
    $stmt->execute();
} catch (PDOException $e) {
    // Обработка ошибки
    echo "Ошибка базы данных: " . $e->getMessage();
    // Можно записать в лог
    error_log($e->getMessage());
}
```

Вот так можно получить массив ошибок

```php
$errors = $_SESSION['register_errors'] ?? [];
```
