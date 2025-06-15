<?php
session_start();

// Уничтожаем сессию
session_destroy();

// Перенаправляем на страницу входа
header('Location: ../pages/auth.php');
exit();
?> 