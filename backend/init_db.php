<?php

try {
    // Создание или подключение к БД через PDO
    $db = new PDO('sqlite:database.db');

    $db->exec("DROP TABLE IF EXISTS users");
    $db->exec("DROP TABLE IF EXISTS requests");

    // Создание таблиц  
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        middle_name TEXT,
        phone TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        login TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'user'
    )");

    // Создание таблицы для заявок на получение услуг
    $db->exec("CREATE TABLE IF NOT EXISTS requests (
        id INTEGER PRIMARY KEY,
        user_id INTEGER NOT NULL,
        address TEXT NOT NULL,
        contact_info TEXT NOT NULL,
        service_type TEXT NOT NULL,
        preferred_date TEXT NOT NULL,
        preferred_time TEXT NOT NULL,
        payment_type TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'новая заявка',
        reject_reason TEXT,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
