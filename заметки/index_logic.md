# Логика работы точки входа (index.php)

## Базовая структура

```php
<?php
session_start();

// Подключение необходимых утилит
require_once './scripts/utils.php';
require_once './scripts/auth_utils.php';

// Проверка доступа (только для пользователей)
checkAccess('user');

// Перенаправление на основную страницу
header('Location: pages/home.php');
exit;
```

## Основные моменты:

1. **Старт сессии**
   - `session_start()` должен быть первым вызовом
   - Необходим для работы с сессиями и авторизацией

2. **Подключение утилит**
   - Подключаем все необходимые вспомогательные файлы
   - Обычно это файлы с функциями и классами

3. **Проверка доступа**
   - Проверяем авторизацию пользователя
   - Проверяем права доступа
   - Перенаправляем на страницу входа если нужно

4. **Перенаправление**
   - Используем `header('Location: ...')` для редиректа
   - Обязательно используем `exit` после редиректа

## Пример с разными ролями

```php
<?php
session_start();
require_once './scripts/utils.php';
require_once './scripts/auth_utils.php';

// Проверка роли пользователя
$role = getUserRole();

switch ($role) {
    case 'admin':
        header('Location: pages/admin/dashboard.php');
        break;
    case 'user':
        header('Location: pages/home.php');
        break;
    default:
        header('Location: pages/login.php');
}
exit;
```

## Важные моменты:

1. Всегда используйте `exit` после `header('Location: ...')`
2. Проверяйте авторизацию до любого редиректа
3. Храните логику маршрутизации в отдельном файле
4. Используйте константы для путей
5. Обрабатывайте все возможные роли пользователей 