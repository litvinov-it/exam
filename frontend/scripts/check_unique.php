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