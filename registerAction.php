<?php
/**
 * Лабораторная работа № 2. Задание 5.
 * Обработчик регистрации: добавление новой записи о пользователе в БД.
 *
 * Особенности реализации:
 *  - проверка и очистка входных данных;
 *  - контроль уникальности email;
 *  - двойное хеширование пароля: MD5(SHA1(pass));
 *  - разделение ролей: новому пользователю автоматически выдаётся роль 'user',
 *    роль 'admin' может быть установлена только вручную через БД.
 */

session_start();
require_once __DIR__ . '/connect.php';

// Принимаем только POST-запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$email    = trim(mb_strtolower($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$password2= (string)($_POST['password2'] ?? '');
$name     = trim($_POST['name'] ?? '');
$phone    = trim($_POST['phone'] ?? '');

/* ---------- Валидация ------------------------------------------------------ */
$errors = [];

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Укажите корректный email.';
}
if (mb_strlen($password) < 4) {
    $errors[] = 'Пароль должен содержать не менее 4 символов.';
}
if ($password !== $password2) {
    $errors[] = 'Пароли в полях «Пароль» и «Повторите пароль» не совпадают.';
}
if ($name === '' || mb_strlen($name) < 3) {
    $errors[] = 'Укажите имя (не менее 3 символов).';
}
if ($phone !== '' && !preg_match('/^[\d\s()+\-]{5,30}$/u', $phone)) {
    $errors[] = 'Телефон может содержать только цифры, пробелы и символы + ( ) -.';
}

/* ---------- Проверка уникальности email ----------------------------------- */
if (empty($errors)) {
    $T = (DB_DRIVER === 'mysql' ? '`' : '"') . usersTable() . (DB_DRIVER === 'mysql' ? '`' : '"');

    $stmt = getDb()->prepare("SELECT id FROM $T WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Пользователь с таким email уже зарегистрирован.';
    }
}

/* ---------- Добавление записи в БД ----------------------------------------- */
if (empty($errors)) {
    $T = (DB_DRIVER === 'mysql' ? '`' : '"') . usersTable() . (DB_DRIVER === 'mysql' ? '`' : '"');

    $sql = "INSERT INTO $T (email, password, name, phone, role, reg_date)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = getDb()->prepare($sql);

    // Роль нового пользователя всегда 'user' (задание 5).
    // Повышение до 'admin' — только прямым UPDATE через БД (например, в phpMyAdmin).
    $role = 'user';

    try {
        $stmt->execute([
            $email,
            hashPassword($password),   // двойное хеширование MD5(SHA1(...))
            $name,
            $phone !== '' ? $phone : null,
            $role,
            date('Y-m-d H:i:s'),
        ]);
    } catch (PDOException $e) {
        $errors[] = 'Ошибка записи в базу данных: ' . $e->getMessage();
    }
}

/* ---------- Результат ------------------------------------------------------- */
if (empty($errors)) {
    header('Location: index.php?msg=registered');
    exit;
}

// Возврат на форму с сохранением введённых данных
$_SESSION['reg_errors'] = $errors;
$_SESSION['reg_old']    = ['email' => $email, 'name' => $name, 'phone' => $phone];
header('Location: register.php');
exit;
