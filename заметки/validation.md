# Валидация и проверка уникальности данных

## Валидация данных (validation.php)

### validateRegistrationData()
```php
validateRegistrationData($data)
```
Функция проверяет все поля регистрации:
1. Имя, фамилия, отчество (русские буквы)
2. Телефон (формат +7(XXX)-XXX-XX-XX)
3. Email (корректный формат)
4. Логин (латинские буквы, цифры, подчеркивание)
5. Пароль (минимум 6 символов)
6. Подтверждение пароля

Примеры ошибок:
```php
$errors = [
    'first_name' => 'Имя должно содержать только русские буквы',
    'phone' => 'Телефон должен быть в формате +7(XXX)-XXX-XX-XX',
    'login' => 'Логин должен содержать минимум 6 символов'
];
```

## Проверка уникальности (check_unique.php)

### checkUniqueData()
```php
checkUniqueData($db, $data)
```
Функция проверяет уникальность:
1. Логина
2. Email (без учета регистра)
3. Телефона (с нормализацией формата)

Примеры ошибок:
```php
$errors = [
    'unique_login' => 'Пользователь с таким логином уже существует',
    'unique_email' => 'Пользователь с таким email уже существует',
    'unique_phone' => 'Пользователь с таким телефоном уже существует'
];
```

## Как использовать

1. **Валидация при регистрации:**
```php
<?php
require_once './scripts/validation.php';
require_once './scripts/check_unique.php';

$data = $_POST;
$errors = [];

// Проверяем валидность данных
$errors = validateRegistrationData($data);

// Если валидация прошла, проверяем уникальность
if (empty($errors)) {
    $errors = checkUniqueData($db, $data);
}

// Если есть ошибки, показываем их
if (!empty($errors)) {
    // Показываем ошибки пользователю
}
```

2. **Отображение ошибок в форме:**
```php
<!-- Форма регистрации -->
<form method="POST" action="register.php">
    <!-- Имя -->
    <div class="form-group">
        <label for="first_name">Имя</label>
        <input type="text" 
               name="first_name" 
               id="first_name" 
               value="<?= htmlspecialchars($data['first_name'] ?? '') ?>"
               class="<?= isset($errors['first_name']) ? 'is-invalid' : '' ?>">
        <?php if (isset($errors['first_name'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['first_name']) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Email -->
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" 
               name="email" 
               id="email" 
               value="<?= htmlspecialchars($data['email'] ?? '') ?>"
               class="<?= isset($errors['email']) || isset($errors['unique_email']) ? 'is-invalid' : '' ?>">
        <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['email']) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($errors['unique_email'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['unique_email']) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Остальные поля формы -->
</form>
```

3. **Стили для отображения ошибок:**
```css
.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
```

4. **Обработка в PHP:**
```php
<?php
// В начале файла
$data = [];
$errors = [];

// Если форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    
    // Валидация
    $errors = validateRegistrationData($data);
    
    // Проверка уникальности
    if (empty($errors)) {
        $errors = checkUniqueData($db, $data);
    }
    
    // Если нет ошибок, сохраняем данные
    if (empty($errors)) {
        // Сохранение в базу
        // Редирект на страницу успеха
    }
}
```

## Полный код утилит

### validation.php
```php
<?php

function validateRegistrationData($data) {
    $errors = [];

    // Валидация имени
    if (empty($data['first_name'])) {
        $errors['first_name'] = 'Имя обязательно для заполнения';
    } elseif (strlen($data['first_name']) < 2) {
        $errors['first_name'] = 'Имя должно содержать минимум 2 символа';
    } elseif (!preg_match('/^[а-яА-ЯёЁ\s-]+$/u', $data['first_name'])) {
        $errors['first_name'] = 'Имя должно содержать только русские буквы, пробелы и дефис';
    }

    // Валидация фамилии
    if (empty($data['last_name'])) {
        $errors['last_name'] = 'Фамилия обязательна для заполнения';
    } elseif (strlen($data['last_name']) < 2) {
        $errors['last_name'] = 'Фамилия должна содержать минимум 2 символа';
    } elseif (!preg_match('/^[а-яА-ЯёЁ\s-]+$/u', $data['last_name'])) {
        $errors['last_name'] = 'Фамилия должна содержать только русские буквы, пробелы и дефис';
    }

    // Валидация отчества
    if (!empty($data['middle_name']) && !preg_match('/^[а-яА-ЯёЁ\s-]+$/u', $data['middle_name'])) {
        $errors['middle_name'] = 'Отчество должно содержать только русские буквы, пробелы и дефис';
    }

    // Валидация телефона
    if (empty($data['phone'])) {
        $errors['phone'] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/', $data['phone'])) {
        $errors['phone'] = 'Телефон должен быть в формате +7(XXX)-XXX-XX-XX';
    }

    // Валидация email
    if (empty($data['email'])) {
        $errors['email'] = 'Email обязателен для заполнения';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный формат email';
    }

    // Валидация логина
    if (empty($data['login'])) {
        $errors['login'] = 'Логин обязателен для заполнения';
    } elseif (strlen($data['login']) < 6) {
        $errors['login'] = 'Логин должен содержать минимум 6 символов';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['login'])) {
        $errors['login'] = 'Логин может содержать только латинские буквы, цифры и знак подчеркивания';
    }

    // Валидация пароля
    if (empty($data['password'])) {
        $errors['password'] = 'Пароль обязателен для заполнения';
    } elseif (strlen($data['password']) < 6) {
        $errors['password'] = 'Пароль должен содержать минимум 6 символов';
    }

    // Проверка подтверждения пароля
    if (empty($data['password_confirm'])) {
        $errors['password_confirm'] = 'Подтверждение пароля обязательно';
    } elseif ($data['password'] !== $data['password_confirm']) {
        $errors['password_confirm'] = 'Пароли не совпадают';
    }

    return $errors;
}
```

### check_unique.php
```php
<?php

function checkUniqueData($db, $data) {
    $errors = [];

    // Проверяем уникальность логина
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE login = :login");
    $stmt->execute(['login' => $data['login']]);
    if ($stmt->fetchColumn() > 0) {
        $errors['unique_login'] = 'Пользователь с таким логином уже существует';
    }

    // Проверяем уникальность email
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE LOWER(email) = LOWER(:email)");
    $stmt->execute(['email' => $data['email']]);
    if ($stmt->fetchColumn() > 0) {
        $errors['unique_email'] = 'Пользователь с таким email уже существует';
    }

    // Проверяем уникальность телефона (нормализуем формат)
    $normalizedPhone = preg_replace('/[^0-9]/', '', $data['phone']);
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE REPLACE(REPLACE(REPLACE(REPLACE(phone, '(', ''), ')', ''), '-', ''), ' ', '') = :phone");
    $stmt->execute(['phone' => $normalizedPhone]);
    if ($stmt->fetchColumn() > 0) {
        $errors['unique_phone'] = 'Пользователь с таким телефоном уже существует';
    }

    return $errors;
}
```

## Используемые PHP-функции

### Работа со строками
- `strlen($string)` - возвращает длину строки
  ```php
  strlen("Hello") // вернет 5
  strlen("Привет") // вернет 12 (каждый кириллический символ = 2 байта)
  ```

- `preg_match($pattern, $string)` - проверяет строку на соответствие регулярному выражению
  ```php
  // Проверка на русские буквы
  preg_match('/^[а-яА-ЯёЁ\s-]+$/u', "Иван") // вернет 1 (true)
  preg_match('/^[а-яА-ЯёЁ\s-]+$/u', "John") // вернет 0 (false)
  ```

- `preg_replace($pattern, $replacement, $string)` - заменяет все совпадения в строке
  ```php
  // Оставить только цифры
  preg_replace('/[^0-9]/', '', "+7(999)-123-45-67") // вернет "79991234567"
  ```

### Работа с базой данных
- `fetchColumn()` - получает значение из первого столбца результата запроса
  ```php
  $stmt = $db->prepare("SELECT COUNT(*) FROM users");
  $stmt->execute();
  $count = $stmt->fetchColumn(); // вернет число записей
  ```

### Безопасность
- `htmlspecialchars($string)` - преобразует специальные символы в HTML-сущности
  ```php
  htmlspecialchars("<script>alert('xss')</script>") 
  // вернет "&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;"
  ```

### Проверки
- `isset($var)` - проверяет, существует ли переменная и не равна ли она null
  ```php
  $errors = ['name' => 'Ошибка'];
  isset($errors['name']) // вернет true
  isset($errors['email']) // вернет false
  ```

### Оператор объединения с null (??)
- `$var ?? $default` - возвращает $var если она существует и не null, иначе $default
  ```php
  $data = ['name' => 'John'];
  $data['name'] ?? 'Гость' // вернет 'John'
  $data['email'] ?? 'Нет email' // вернет 'Нет email'
  ```

### Регулярные выражения в примерах
// Только русские буквы, пробелы и дефис
'/^[а-яА-ЯёЁ\s-]+$/u'

// Формат телефона +7(XXX)-XXX-XX-XX
'/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'

// Логин: латинские буквы, цифры, подчеркивание
'/^[a-zA-Z0-9_]+$/'
```

## Дополнительные примеры

### Работа со строками
```php
// strlen() - проверка длины
$name = "Иван";
if (strlen($name) < 2) {
    echo "Имя слишком короткое";
}

// preg_match() - проверка формата
$phone = "+7(999)-123-45-67";
if (!preg_match('/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/', $phone)) {
    echo "Неверный формат телефона";
}

// preg_replace() - очистка данных
$dirty = "  +7(999)-123-45-67  ";
$clean = preg_replace('/[^0-9]/', '', $dirty);
// $clean = "79991234567"
```

### Работа с массивами и проверки
```php
// isset() - проверка наличия данных
$user = [
    'name' => 'John',
    'email' => null
];

if (isset($user['name'])) {
    echo "Имя: " . $user['name'];
}

// Оператор ?? - значения по умолчанию
$name = $user['name'] ?? 'Гость';
$email = $user['email'] ?? 'Нет email';
$phone = $user['phone'] ?? 'Нет телефона';
```

### Безопасность
```php
// htmlspecialchars() - защита от XSS
$userInput = "<script>alert('hack')</script>";
$safe = htmlspecialchars($userInput);
// $safe = "&lt;script&gt;alert(&#039;hack&#039;)&lt;/script&gt;"

// В форме
<input type="text" value="<?= htmlspecialchars($data['name'] ?? '') ?>">
```

### Работа с базой данных
```php
// fetchColumn() - подсчет записей
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role = :role");
$stmt->execute(['role' => 'admin']);
$adminCount = $stmt->fetchColumn();

// Проверка уникальности
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
$stmt->execute(['email' => 'john@example.com']);
if ($stmt->fetchColumn() > 0) {
    echo "Email уже используется";
}
```

### Регулярные выражения
```php
// Проверка email
$email = "john.doe@example.com";
if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
    echo "Неверный формат email";
}

// Проверка пароля (минимум 6 символов, буквы и цифры)
$password = "Pass123";
if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $password)) {
    echo "Пароль должен содержать минимум 6 символов, буквы и цифры";
}

// Извлечение данных
$text = "Телефон: +7(999)-123-45-67, Email: john@example.com";
preg_match('/\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}/', $text, $matches);
$phone = $matches[0] ?? '';
```

### Комбинированные примеры
```php
// Валидация и очистка телефона
function validatePhone($phone) {
    // Очищаем от всего кроме цифр
    $clean = preg_replace('/[^0-9]/', '', $phone);
    
    // Проверяем длину
    if (strlen($clean) !== 11) {
        return false;
    }
    
    // Заменяем 8 на 7
    if ($clean[0] === '8') {
        $clean = '7' . substr($clean, 1);
    }
    
    // Форматируем
    return sprintf(
        '+7(%s)-%s-%s-%s',
        substr($clean, 1, 3),
        substr($clean, 4, 3),
        substr($clean, 7, 2),
        substr($clean, 9, 2)
    );
}

// Использование
$phone = " 8(999)-123-45-67 ";
$formatted = validatePhone($phone);
// $formatted = "+7(999)-123-45-67"
```
