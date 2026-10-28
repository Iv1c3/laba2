<?php
/**
 * Лабораторная работа № 2. Задание 6.
 * Обработчик формы редактирования данных пользователя (UPDATE в БД).
 *
 * Правила:
 *  - email и дата регистрации НЕ изменяются;
 *  - обычный пользователь может менять только свои данные (name, phone, password);
 *  - роль меняет только администратор;
 *  - пароль при изменении хешируется так же — двойное хеширование MD5(SHA1(...)).
 */

session_start();
require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$current = $_SESSION['user'];
$isAdmin = ($current['role'] === 'admin');

$id       = (int)($_POST['id'] ?? 0);
$name     = trim($_POST['name'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$password = (string)($_POST['password'] ?? '');
$password2= (string)($_POST['password2'] ?? '');
$role     = $_POST['role'] ?? '';

// Администратор может редактировать любую запись, остальные — только свою
if (!$isAdmin && $id !== (int)$current['id']) {
    $_SESSION['edit_errors'] = ['Редактирование чужой записи запрещено.'];
    header('Location: edit.php');
    exit;
}

/* ---------- Валидация ------------------------------------------------------- */
$errors = [];

if ($name === '' || mb_strlen($name) < 3) {
    $errors[] = 'Имя должно содержать не менее 3 символов.';
}
if ($phone !== '' && !preg_match('/^[\d\s()+\-]{5,30}$/u', $phone)) {
    $errors[] = 'Телефон может содержать только цифры, пробелы и символы + ( ) -.';
}
if ($password !== '') {
    if (mb_strlen($password) < 4) {
        $errors[] = 'Новый пароль должен содержать не менее 4 символов.';
    }
    if ($password !== $password2) {
        $errors[] = 'Новый пароль и его повтор не совпадают.';
    }
}
if ($isAdmin && !in_array($role, ['user', 'admin'], true)) {
    $errors[] = 'Недопустимое значение роли.';
}

/* ---------- Формирование UPDATE --------------------------------------------- */
$T = (DB_DRIVER === 'mysql' ? '`' : '"') . usersTable() . (DB_DRIVER === 'mysql' ? '`' : '"');

if (empty($errors)) {
    $sets = ['name = ?', 'phone = ?'];           // email и reg_date не трогаем!
    $vals = [$name, $phone !== '' ? $phone : null];

    if ($password !== '') {
        $sets[] = 'password = ?';                // двойное хеширование
        $vals[] = hashPassword($password);
    }
    if ($isAdmin) {
        $sets[] = 'role = ?';
        $vals[] = $role;
    }

    $vals[] = $id;
    $stmt = getDb()->prepare("UPDATE $T SET " . implode(', ', $sets) . " WHERE id = ?");

    try {
        $stmt->execute($vals);
    } catch (PDOException $e) {
        $errors[] = 'Ошибка обновления записи: ' . $e->getMessage();
    }
}

/* ---------- Результат -------------------------------------------------------- */
if (empty($errors)) {
    // Если редактировали себя — обновляем данные в сессии
    if ($id === (int)$current['id']) {
        $selT = $T;
        $st = getDb()->prepare("SELECT id, email, name, phone, role, reg_date FROM $selT WHERE id = ?");
        $st->execute([$id]);
        if ($row = $st->fetch()) {
            $_SESSION['user'] = $row;
        }
    }
    header('Location: index.php?msg=updated');
    exit;
}

$_SESSION['edit_errors'] = $errors;
header("Location: edit.php?id=$id");
exit;
