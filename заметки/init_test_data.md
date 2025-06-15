# Создание тестовых данных в базу

## Базовая структура скрипта

```php
<?php
try {
    // Подключение к БД
    $db = new PDO('sqlite:database.db');
    
    // Подготовка запроса
    $stmt = $db->prepare("INSERT INTO table_name (field1, field2) 
                         VALUES (:field1, :field2)");

    // Массив тестовых данных
    $test_data = [
        [
            'field1' => 'value1',
            'field2' => 'value2'
        ],
        [
            'field1' => 'value3',
            'field2' => 'value4'
        ]
    ];

    // Вставка данных
    foreach ($test_data as $data) {
        $stmt->execute($data);
    }

    echo "Тестовые данные успешно добавлены!";

} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
```

## Основные моменты:

1. Всегда используем параметризованные запросы
2. Данные храним в массиве массивов
3. Используем цикл foreach для вставки
4. Обрабатываем ошибки через try-catch

## Пример с несколькими таблицами

```php
// Подготовка запросов для разных таблиц
$stmt_users = $db->prepare("INSERT INTO users (name, email) VALUES (:name, :email)");
$stmt_orders = $db->prepare("INSERT INTO orders (user_id, product) VALUES (:user_id, :product)");

// Тестовые данные
$users = [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com']
];

$orders = [
    ['user_id' => 1, 'product' => 'Product 1'],
    ['user_id' => 2, 'product' => 'Product 2']
];

// Вставка данных
foreach ($users as $user) {
    $stmt_users->execute($user);
}

foreach ($orders as $order) {
    $stmt_orders->execute($order);
}
```

## Полезные советы:

1. Используйте `password_hash()` для хеширования паролей
2. Добавляйте реалистичные данные (имена, адреса, телефоны)
3. Создавайте связи между таблицами (foreign keys)
4. Добавляйте разные статусы и состояния для тестирования
5. Используйте разные роли пользователей (admin, user и т.д.)

## Работа с паролями

### Хеширование пароля
```php
// Создание хеша пароля
$password = 'password123';
$hash = password_hash($password, PASSWORD_DEFAULT);
// Результат: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

// Сохранение в базу
$stmt = $db->prepare("INSERT INTO users (login, password) VALUES (:login, :password)");
$stmt->execute([
    'login' => 'user1',
    'password' => $hash
]);
```

### Проверка пароля
```php
// Получение пользователя из базы
$stmt = $db->prepare("SELECT * FROM users WHERE login = :login");
$stmt->execute(['login' => 'user1']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Проверка пароля
$password = 'password123';
if (password_verify($password, $user['password'])) {
    echo "Пароль верный!";
} else {
    echo "Неверный пароль!";
}
```

### Важные моменты:
1. Всегда используйте `PASSWORD_DEFAULT` как алгоритм хеширования
2. Никогда не храните пароли в открытом виде
3. Хеш автоматически включает соль (salt)
4. Длина хеша всегда одинаковая, независимо от длины пароля
5. Нельзя расшифровать хеш обратно в пароль 