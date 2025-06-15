# Инициализация базы данных SQLite в PHP

## Базовая структура

```php
<?php
try {
    // Подключение к БД
    $db = new PDO('sqlite:database.db');
    
    // Создание таблицы
    $db->exec("CREATE TABLE IF NOT EXISTS table_name (
        id INTEGER PRIMARY KEY,
        field1 TEXT NOT NULL,
        field2 TEXT,
        field3 INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
```

## Основные моменты:

1. Используем PDO для работы с SQLite
2. Всегда оборачиваем в try-catch для обработки ошибок
3. Для создания таблиц используем CREATE TABLE IF NOT EXISTS

## Методы PDO для работы с запросами

### exec()
- Используется для выполнения SQL-запросов, которые не возвращают данные
- Подходит для: CREATE, DROP, ALTER, INSERT, UPDATE, DELETE
- Возвращает количество затронутых строк
```php
$db->exec("CREATE TABLE users (id INTEGER PRIMARY KEY)");
$affected = $db->exec("DELETE FROM users WHERE id = 1");
```

### prepare() + execute()
- Подготовка запроса с параметрами
- Выполнение подготовленного запроса
- Возвращает объект PDOStatement
```php
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => 1]);
$user = $stmt->fetch();
```

### query()
- Для простых запросов без параметров
- Возвращает объект PDOStatement
```php
$result = $db->query("SELECT * FROM users");
$users = $result->fetchAll();
```

## Методы получения данных

### fetch() и fetchAll()
- `fetch()` - получает одну строку результата
- `fetchAll()` - получает все строки результата

```php
// Получение одной записи
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => 1]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// Результат: ['id' => 1, 'name' => 'John', 'email' => 'john@example.com']

// Получение всех записей
$stmt = $db->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Результат: [
//     ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
//     ['id' => 2, 'name' => 'Jane', 'email' => 'jane@example.com']
// ]
```

### Как работает fetch()
При каждом вызове `fetch()` курсор перемещается на следующую строку результата. Когда строки заканчиваются, возвращается `false`.

```php
$stmt = $db->query("SELECT * FROM users");

// Первый вызов - первая строка
$first = $stmt->fetch(PDO::FETCH_ASSOC);
// ['id' => 1, 'name' => 'John']

// Второй вызов - вторая строка
$second = $stmt->fetch(PDO::FETCH_ASSOC);
// ['id' => 2, 'name' => 'Jane']

// Третий вызов - третья строка
$third = $stmt->fetch(PDO::FETCH_ASSOC);
// ['id' => 3, 'name' => 'Bob']

// Четвертый вызов - false, так как строк больше нет
$fourth = $stmt->fetch(PDO::FETCH_ASSOC);
// false
```

### Цикл по результатам
Часто используется в цикле while для обработки всех строк:

```php
$stmt = $db->query("SELECT * FROM users");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['name'] . "\n";
}
// Выведет:
// John
// Jane
// Bob
```

### Режимы выборки (fetch modes):
- `PDO::FETCH_ASSOC` - возвращает ассоциативный массив
- `PDO::FETCH_NUM` - возвращает индексированный массив
- `PDO::FETCH_BOTH` - возвращает оба типа индексов
- `PDO::FETCH_OBJ` - возвращает объект
- `PDO::FETCH_CLASS` - возвращает объект указанного класса

```php
// Примеры разных режимов выборки
$stmt = $db->query("SELECT * FROM users LIMIT 1");

// Ассоциативный массив
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// ['id' => 1, 'name' => 'John']

// Индексированный массив
$user = $stmt->fetch(PDO::FETCH_NUM);
// [0 => 1, 1 => 'John']

// Объект
$user = $stmt->fetch(PDO::FETCH_OBJ);
// stdClass Object ( [id] => 1 [name] => 'John' )
```

## Параметризованные запросы

Параметризованные запросы - это способ безопасной передачи данных в SQL-запросы. Вместо прямой подстановки значений в строку запроса, мы используем специальные маркеры (`:param` или `?`).

### Почему это важно:
- Защита от SQL-инъекций
- Автоматическое экранирование специальных символов
- Повышение производительности при повторном использовании запроса

### Примеры:

```php
// ❌ Плохо - прямая подстановка значений
$name = "John'; DROP TABLE users; --";
$db->exec("INSERT INTO users (name) VALUES ('$name')"); // Опасно!

// ✅ Хорошо - параметризованный запрос
$stmt = $db->prepare("INSERT INTO users (name) VALUES (:name)");
$stmt->execute([':name' => $name]); // Безопасно!

// Пример с несколькими параметрами
$stmt = $db->prepare("INSERT INTO users (name, email, age) VALUES (:name, :email, :age)");
$stmt->execute([
    ':name' => 'John',
    ':email' => 'john@example.com',
    ':age' => 25
]);
```

## Пример с параметризованным запросом:

```php
$stmt = $db->prepare("INSERT INTO table_name (field1, field2) VALUES (:field1, :field2)");
$stmt->execute([
    ':field1' => $value1,
    ':field2' => $value2
]);
``` 