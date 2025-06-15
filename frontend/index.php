<?php
session_start();

// Подключаем утилиты
require_once './scripts/utils.php';
require_once './scripts/auth_utils.php';

// Проверяем доступ (только для обычных пользователей)
checkAccess('user');

// Перенаправляем на главную страницу
header('Location: pages/home.php');
exit;