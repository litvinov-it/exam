<?php
session_start();
require_once 'auth_utils.php';

try {
    // Подключение к БД
    $db = new PDO('sqlite:../../backend/database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем данные из формы
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    error_log("Login attempt - Login: " . $login);
    error_log("Login attempt - Password: " . $password);

    if (empty($login) || empty($password)) {
        header('Location: ../pages/auth.php?error=empty_fields');
        exit();
    }

    // Ищем пользователя в базе
    $stmt = $db->prepare("SELECT id, password, role FROM users WHERE login = :login");
    $stmt->execute(['login' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("User from DB: " . print_r($user, true));
    error_log("Stored password hash: " . ($user['password'] ?? 'not found'));
    error_log("Password verify result: " . (password_verify($password, $user['password'] ?? '') ? 'true' : 'false'));

    // Проверяем пароль
    if ($user && password_verify($password, $user['password'])) {
        // Авторизуем и перенаправляем
        authorizeAndRedirect($user['id'], $user['role']);
    } else {
        // Если данные неверные, возвращаем на страницу входа
        header('Location: ../pages/auth.php?error=invalid_credentials');
        exit();
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    // В случае ошибки с базой данных
    header('Location: ../pages/auth.php?error=db_error');
    exit();
}
