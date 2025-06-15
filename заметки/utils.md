# Утилиты для работы с авторизацией и данными

## Авторизация (auth_utils.php)

### checkAccess()
```php
checkAccess($requiredRole = null)
```
Функция-охранник для защиты страниц:
1. Проверяет авторизацию (есть ли user_id в сессии)
2. Проверяет роль пользователя (если указана)
3. Перенаправляет на нужную страницу

Примеры:
```php
// Просто проверить авторизацию
checkAccess();

// Проверить роль админа
checkAccess('admin');

// Проверить роль пользователя
checkAccess('user');
```

### authorizeAndRedirect()
```php
authorizeAndRedirect($userId, $role)
```
Функция для входа пользователя:
1. Сохраняет ID и роль в сессии
2. Перенаправляет на нужную страницу

## Работа с данными (utils.php)

### Форматирование телефона

#### phoneToNumber()
```php
phoneToNumber($phone)
```
Преобразует телефон в числовой формат:
- Вход: `+7(999)-123-45-67`
- Выход: `79991234567`

#### numberToPhone()
```php
numberToPhone($number)
```
Преобразует число в формат телефона:
- Вход: `79991234567`
- Выход: `+7(999)-123-45-67`

Особенности:
- Заменяет 8 на 7 в начале
- Проверяет длину номера
- Возвращает исходный номер при ошибке

## Как использовать

1. **Защита страниц:**
```php
<?php
session_start();
require_once './scripts/auth_utils.php';

// Проверяем доступ
checkAccess('user');
// Если проверка пройдена - показываем страницу
```

2. **Авторизация:**
```php
<?php
// После проверки логина/пароля
authorizeAndRedirect($userId, $role);
```

3. **Работа с телефоном:**
```php
<?php
require_once './scripts/utils.php';

// Форматирование для базы
$phone = phoneToNumber('+7(999)-123-45-67');

// Форматирование для вывода
$formatted = numberToPhone('79991234567');
```

## Полный код утилит

### auth_utils.php
```php
<?php

/**
 * Проверяет авторизацию и роль пользователя, перенаправляет при необходимости
 * @param string $requiredRole Требуемая роль (admin/user)
 * @return bool true если доступ разрешен
 */
function checkAccess($requiredRole = null) {
    // Проверяем авторизацию
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../pages/auth.php');
        exit();
    }

    // Если указана требуемая роль, проверяем её
    if ($requiredRole !== null) {
        if ($_SESSION['role'] !== $requiredRole) {
            // Перенаправляем на соответствующую страницу в зависимости от роли
            if ($_SESSION['role'] === 'admin') {
                header('Location: ../pages/admin/requests.php');
            } else {
                header('Location: ../index.php');
            }
            exit();
        }
    }

    return true;
}

/**
 * Авторизует пользователя и перенаправляет на соответствующую страницу
 * @param int $userId ID пользователя
 * @param string $role Роль пользователя (admin/user)
 * @return void
 */
function authorizeAndRedirect($userId, $role) {
    // Устанавливаем сессию
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;
    
    // Перенаправляем в зависимости от роли
    if ($role === 'admin') {
        header('Location: ../admin/requests.php');
    } else {
        header('Location: ../index.php');
    }
    exit();
}
```

### utils.php
```php
<?php

/**
 * Преобразует отформатированный номер телефона в числовой формат
 * @param string $phone Номер телефона в формате +7(XXX)-XXX-XX-XX
 * @return string Номер телефона в формате 79991234567
 */
function phoneToNumber($phone) {
    return preg_replace('/[^0-9]/', '', $phone);
}

/**
 * Преобразует числовой формат номера телефона в отформатированный вид
 * @param string $number Номер телефона в формате 79991234567
 * @return string Номер телефона в формате +7(XXX)-XXX-XX-XX
 */
function numberToPhone($number) {
    // Убираем все нецифровые символы
    $number = preg_replace('/[^0-9]/', '', $number);
    
    // Если номер начинается с 8, заменяем на 7
    if (strlen($number) === 11 && $number[0] === '8') {
        $number = '7' . substr($number, 1);
    }
    
    // Форматируем номер
    if (strlen($number) === 11) {
        return sprintf(
            '+7(%s)-%s-%s-%s',
            substr($number, 1, 3),
            substr($number, 4, 3),
            substr($number, 7, 2),
            substr($number, 9, 2)
        );
    }
    
    return $number; // Возвращаем исходный номер, если формат не соответствует ожидаемому
}
```

## Выход из системы (logout.php)

### Описание
Файл `logout.php` отвечает за корректное завершение сессии пользователя и перенаправление на страницу авторизации.

### Основные функции
1. **Уничтожение сессии**:
   - Удаляет все данные сессии
   - Очищает куки сессии
   - Завершает текущую сессию

2. **Перенаправление**:
   - Отправляет пользователя на страницу авторизации
   - Предотвращает дальнейшее выполнение скрипта

### Полный код
```php
<?php
session_start();

// Уничтожаем сессию
session_destroy();

// Перенаправляем на страницу входа
header('Location: ../auth.php');
exit();
?>
```

### Как использовать
1. **Создание ссылки для выхода**:
```html
<a href="scripts/logout.php">Выйти</a>
```

2. **Кнопка выхода**:
```html
<form action="scripts/logout.php" method="post">
    <button type="submit">Выйти</button>
</form>
```

### Важные моменты
1. **Безопасность**:
   - Всегда используйте `exit()` после `header()`
   - Уничтожайте все данные сессии
   - Перенаправляйте на страницу авторизации

2. **Практика**:
   - Размещайте ссылку на выход в защищенных разделах
   - Добавляйте подтверждение выхода при необходимости
   - Очищайте все пользовательские данные

3. **Пример с подтверждением**:
```php
<?php
session_start();

if (isset($_POST['confirm_logout'])) {
    // Уничтожаем сессию
    session_destroy();
    
    // Перенаправляем на страницу входа
    header('Location: ../auth.php');
    exit();
}
?>
<form method="post">
    <p>Вы уверены, что хотите выйти?</p>
    <button type="submit" name="confirm_logout">Да, выйти</button>
    <a href="javascript:history.back()">Отмена</a>
</form>
``` 