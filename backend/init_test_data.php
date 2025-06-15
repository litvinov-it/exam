<?php

try {
    // Подключение к БД через PDO
    $db = new PDO('sqlite:database.db');
    
    // Подготовка запроса для вставки пользователей
    $stmt = $db->prepare("INSERT INTO users (first_name, last_name, middle_name, phone, email, login, password, role) 
                         VALUES (:first_name, :last_name, :middle_name, :phone, :email, :login, :password, :role)");

    // Тестовые пользователи
    $users = [
        [
            'first_name' => 'Главный',
            'last_name' => 'Администратор',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'ivanov@example.com',
            'login' => 'adminka',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'admin'
        ],
        [
            'first_name' => 'Петр',
            'last_name' => 'Петров',
            'middle_name' => 'Петрович',
            'phone' => '+7 (999) 234-56-78',
            'email' => 'petrov@example.com',
            'login' => 'petrov',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'user'
        ],
        [
            'first_name' => 'Анна',
            'last_name' => 'Сидорова',
            'middle_name' => 'Александровна',
            'phone' => '+7 (999) 345-67-89',
            'email' => 'sidorova@example.com',
            'login' => 'sidorova',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'user'
        ]
    ];

    // Вставка пользователей
    foreach ($users as $user) {
        $stmt->execute($user);
    }

    // Подготовка запроса для вставки заявок
    $stmt = $db->prepare("INSERT INTO requests (user_id, address, contact_info, service_type, preferred_date, preferred_time, payment_type, status) 
                         VALUES (:user_id, :address, :contact_info, :service_type, :preferred_date, :preferred_time, :payment_type, :status)");

    // Тестовые заявки
    $requests = [
        [
            'user_id' => 2,
            'address' => 'ул. Ленина, 10, кв. 5',
            'contact_info' => '+7 (999) 123-45-67',
            'service_type' => 'Уборка квартиры',
            'preferred_date' => '2024-03-20',
            'preferred_time' => '14:00',
            'payment_type' => 'Карта',
            'status' => 'новая заявка'
        ],
        [
            'user_id' => 2,
            'address' => 'пр. Мира, 25, кв. 12',
            'contact_info' => '+7 (999) 234-56-78',
            'service_type' => 'Мытье окон',
            'preferred_date' => '2024-03-21',
            'preferred_time' => '10:00',
            'payment_type' => 'Наличные',
            'status' => 'в обработке'
        ],
        [
            'user_id' => 3,
            'address' => 'ул. Гагарина, 15, кв. 8',
            'contact_info' => '+7 (999) 345-67-89',
            'service_type' => 'Генеральная уборка',
            'preferred_date' => '2024-03-22',
            'preferred_time' => '16:00',
            'payment_type' => 'Карта',
            'status' => 'новая заявка'
        ]
    ];

    // Вставка заявок
    foreach ($requests as $request) {
        $stmt->execute($request);
    }

    echo "Тестовые данные успешно добавлены!";

} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}