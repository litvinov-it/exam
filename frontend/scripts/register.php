<?php
session_start();

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['user_id'])) {
    header('Location: ../pages/home.php');
    exit;
}

require_once 'validation.php';
require_once 'check_unique.php';
require_once 'utils.php';
require_once 'auth_utils.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Инициализация подключения к базе данных
        $db = new PDO('sqlite:../../backend/database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Подготовка данных
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'middle_name' => trim($_POST['middle_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'login' => trim($_POST['login'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? ''
        ];

        // Проверка уникальности данных
        $uniqueErrors = checkUniqueData($db, $data);
        if (!empty($uniqueErrors)) {
            $errors = array_merge($errors, $uniqueErrors);
        }

        // Валидация данных
        $validationErrors = validateRegistrationData($data);
        if (!empty($validationErrors)) {
            $errors = array_merge($errors, $validationErrors);
        }

        // Если нет ошибок, регистрируем пользователя
        if (empty($errors)) {
            $stmt = $db->prepare("INSERT INTO users (first_name, last_name, middle_name, phone, email, login, password, role) 
                                VALUES (:first_name, :last_name, :middle_name, :phone, :email, :login, :password, 'user')");
            
            $stmt->execute([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'login' => $data['login'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ]);

            $userId = $db->lastInsertId();
            authorizeAndRedirect($userId, 'user');
        }
    } catch (PDOException $e) {
        $errors['db'] = 'Ошибка базы данных: ' . $e->getMessage();
    }
}

// Если есть ошибки или это GET запрос, возвращаем на страницу регистрации
if (!empty($errors) || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_data'] = $data ?? [];
    header('Location: ../pages/register.php');
    exit;
}
