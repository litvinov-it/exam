<?php

/**
 * Проверяет авторизацию и роль пользователя, перенаправляет при необходимости
 * @param string $requiredRole Требуемая роль (admin/user)
 * @return bool true если доступ разрешен
 */
function checkAccess($requiredRole = null) {
    // Проверяем авторизацию
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../pages/auth.php');
        exit();
    }

    // Если указана требуемая роль, проверяем её
    if ($requiredRole !== null) {
        if ($_SESSION['role'] !== $requiredRole) {
            // Перенаправляем на соответствующую страницу в зависимости от роли
            if ($_SESSION['role'] === 'admin') {
                header('Location: ../pages/admin/requests.php');
            } else {
                header('Location: ../index.php');
            }
            exit();
        }
    }

    return true;
}

/**
 * Авторизует пользователя и перенаправляет на соответствующую страницу
 * @param int $userId ID пользователя
 * @param string $role Роль пользователя (admin/user)
 * @return void
 */
function authorizeAndRedirect($userId, $role) {
    // Устанавливаем сессию
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;
    
    // Перенаправляем в зависимости от роли
    if ($role === 'admin') {
        header('Location: ../pages/admin/requests.php');
    } else {
        header('Location: ../index.php');
    }
    exit();
} 