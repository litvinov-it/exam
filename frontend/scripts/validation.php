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