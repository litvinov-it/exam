<?php
session_start();

// Подключаем утилиты
require_once '../scripts/utils.php';
require_once '../scripts/auth_utils.php';

// Проверяем доступ (только для админов)
checkAccess('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $reject_reason = null;

    if ($status === 'отменено') {
        $reject_reason = $_POST['reject_reason'] ?? null;
        if (empty($reject_reason)) {
            die('Причина отмены обязательна');
        }
    }

    if (!$request_id || !$status) {
        die('Неверные данные');
    }

    try {
        $db = new PDO('sqlite:../../backend/database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("UPDATE requests SET status = :status, reject_reason = :reject_reason WHERE id = :request_id");
        $stmt->execute([
            'status' => $status,
            'reject_reason' => $reject_reason,
            'request_id' => $request_id
        ]);

        header("Location: ../pages/admin/requests.php");
        exit;
    } catch (PDOException $e) {
        die("Ошибка базы данных: " . $e->getMessage());
    }
} else {
    die('Метод не поддерживается');
} 