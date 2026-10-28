<?php
/**
 * Лабораторная работа № 2. Задание 3/5.
 * Обработчик формы идентификации (входа) из шапки сайта.
 * Проверяет email и пароль (двойное хеширование MD5(SHA1(...))),
 * при успехе записывает данные пользователя в сессию.
 */

session_start();
require_once __DIR__ . '/connect.php';

// Выход из системы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    unset($_SESSION['user']);
    session_regenerate_id(true);
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$email    = trim(mb_strtolower($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

$T = (DB_DRIVER === 'mysql' ? '`' : '"') . usersTable() . (DB_DRIVER === 'mysql' ? '`' : '"');

$stmt = getDb()->prepare("SELECT id, email, password, name, phone, role, reg_date
                          FROM $T WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && $user['password'] === hashPassword($password)) {
    // Пароль совпал — идентифицируем пользователя
    unset($user['password']);                 // хеш в сессию не кладём
    $_SESSION['user'] = $user;
    session_regenerate_id(true);
    header('Location: index.php');
} else {
    $_SESSION['login_error'] = 'Неверный email или пароль.';
    header('Location: index.php');
}
exit;
